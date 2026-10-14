<?php

namespace App\Services;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;

class ChatbotService
{
    private const MAX_STEPS = 5;

    private const SYSTEM = 'You are the assistant for BIG DACO, an inventory and sales system. '
        .'LANGUAGE RULE: detect the language of the user\'s latest message and reply strictly in that language. '
        .'If it is English, reply in English; if it is Spanish, reply in Spanish. Never mix languages. '
        .'If the user explicitly asks for both languages, give the answer in Spanish followed by its English version. '
        .'Be brief and clear. ALWAYS use the available functions to get real data from the database; never invent figures. '
        .'You only have read access. '
        .'You also know the "Declaración Persona Transhumana" reproduced below. If asked about it (its points, meaning, author, date), '
        .'answer from this text, translating faithfully into the user\'s language when needed, and do not add content that is not in it.';

    private function system(): string
    {
        $path = resource_path('chatbot/declaracion-persona-transhumana.txt');
        $doc = is_file($path) ? file_get_contents($path) : '';

        return self::SYSTEM."\n\n--- DOCUMENT ---\n".$doc;
    }

    /**
     * @param  list<array{role:string,text:string}>  $history
     */
    public function ask(string $message, array $history = []): string
    {
        $contents = [];
        foreach (array_slice($history, -10) as $turn) {
            $contents[] = [
                'role' => $turn['role'] === 'model' ? 'model' : 'user',
                'parts' => [['text' => (string) $turn['text']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        for ($i = 0; $i < self::MAX_STEPS; $i++) {
            $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->timeout(60)
                ->retry(3, 1500, fn ($e) => in_array($e->getCode(), [429, 500, 503]), throw: false)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/'
                    .config('services.gemini.model').':generateContent', [
                        'systemInstruction' => ['parts' => [['text' => $this->system()]]],
                        'contents' => $contents,
                        'tools' => [['functionDeclarations' => $this->declarations()]],
                    ]);

            if (! $response->successful()) {
                report(new \RuntimeException('Gemini error: '.$response->body()));

                return 'No pude contactar al asistente. Intenta de nuevo. / Could not reach the assistant. Please try again.';
            }

            $content = $response->json('candidates.0.content');
            $parts = $content['parts'] ?? [];
            $calls = array_values(array_filter($parts, fn ($p) => isset($p['functionCall'])));

            if (! $calls) {
                $text = trim(implode("\n", array_column($parts, 'text')));

                return $text !== '' ? $text : 'No tengo una respuesta. / I have no answer for that.';
            }

            $parts = array_map(function ($p) {
                if (isset($p['functionCall']) && empty($p['functionCall']['args'])) {
                    $p['functionCall']['args'] = new \stdClass;
                }

                return $p;
            }, $parts);

            $contents[] = ['role' => 'model', 'parts' => $parts];

            $results = [];
            foreach ($calls as $part) {
                $call = $part['functionCall'];
                $results[] = ['functionResponse' => [
                    'name' => $call['name'],
                    'response' => ['result' => $this->run($call['name'], $call['args'] ?? [])],
                ]];
            }
            $contents[] = ['role' => 'user', 'parts' => $results];
        }

        return 'Consulta demasiado compleja; sé más concreto. / Query too complex; please be more specific.';
    }

    private function run(string $name, array $args): array
    {
        return match ($name) {
            'buscar_productos' => $this->buscarProductos($args),
            'resumen_inventario' => $this->resumenInventario(),
            'resumen_ventas' => $this->resumenVentas($args),
            'productos_mas_vendidos' => $this->masVendidos($args),
            default => ['error' => 'Funcion desconocida'],
        };
    }

    private function buscarProductos(array $a): array
    {
        $q = Producto::query()->select('id', 'nombre', 'categoria', 'precio', 'stock');

        if (! empty($a['texto'])) {
            $q->where('nombre', 'like', '%'.$a['texto'].'%');
        }
        if (! empty($a['categoria'])) {
            $q->where('categoria', 'like', '%'.$a['categoria'].'%');
        }
        if (isset($a['stock_maximo'])) {
            $q->where('stock', '<=', (int) $a['stock_maximo']);
        }

        return $q->orderBy('nombre')->limit(25)->get()->toArray();
    }

    private function resumenInventario(): array
    {
        return [
            'total_productos' => Producto::count(),
            'unidades_en_stock' => (int) Producto::sum('stock'),
            'valor_inventario' => (float) Producto::selectRaw('COALESCE(SUM(precio * stock), 0) as v')->value('v'),
            'sin_stock' => Producto::where('stock', '<=', 0)->pluck('nombre')->take(25)->all(),
            'por_categoria' => Producto::selectRaw('categoria, COUNT(*) as productos, SUM(stock) as unidades')
                ->groupBy('categoria')->get()->toArray(),
        ];
    }

    private function resumenVentas(array $a): array
    {
        $q = Venta::query()->where('estado', 'completada');

        if (! empty($a['desde'])) {
            $q->whereDate('created_at', '>=', $a['desde']);
        }
        if (! empty($a['hasta'])) {
            $q->whereDate('created_at', '<=', $a['hasta']);
        }

        return [
            'ventas_completadas' => (clone $q)->count(),
            'total_vendido' => (float) (clone $q)->sum('total'),
        ];
    }

    private function masVendidos(array $a): array
    {
        $limit = min(max((int) ($a['limite'] ?? 5), 1), 20);

        return DetalleVenta::query()
            ->join('productos', 'productos.id', '=', 'detalle_ventas.producto_id')
            ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->where('ventas.estado', 'completada')
            ->groupBy('productos.id', 'productos.nombre')
            ->selectRaw('productos.nombre, SUM(detalle_ventas.cantidad) as unidades_vendidas')
            ->orderByDesc('unidades_vendidas')
            ->limit($limit)
            ->get()->toArray();
    }

    private function declarations(): array
    {
        return [
            [
                'name' => 'buscar_productos',
                'description' => 'Busca productos por nombre, categoria o stock maximo. Devuelve nombre, categoria, precio y stock.',
                'parameters' => ['type' => 'OBJECT', 'properties' => [
                    'texto' => ['type' => 'STRING', 'description' => 'Texto parcial del nombre'],
                    'categoria' => ['type' => 'STRING'],
                    'stock_maximo' => ['type' => 'INTEGER', 'description' => 'Solo productos con stock menor o igual (stock bajo)'],
                ]],
            ],
            [
                'name' => 'resumen_inventario',
                'description' => 'Resumen general del inventario: totales, valor, productos sin stock y conteo por categoria.',
            ],
            [
                'name' => 'resumen_ventas',
                'description' => 'Numero de ventas completadas y total vendido, opcionalmente en un rango de fechas.',
                'parameters' => ['type' => 'OBJECT', 'properties' => [
                    'desde' => ['type' => 'STRING', 'description' => 'Fecha inicial YYYY-MM-DD'],
                    'hasta' => ['type' => 'STRING', 'description' => 'Fecha final YYYY-MM-DD'],
                ]],
            ],
            [
                'name' => 'productos_mas_vendidos',
                'description' => 'Ranking de productos mas vendidos por unidades.',
                'parameters' => ['type' => 'OBJECT', 'properties' => [
                    'limite' => ['type' => 'INTEGER'],
                ]],
            ],
        ];
    }
}
