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
        .'Be brief and clear. ALWAYS use the available functions to get real data from the database; never invent figures. '
        .'You only have read access. '
        .'You can also answer questions about the following user-provided text, independently of database queries. '
        .'It is the "Declaración Persona Transhumana", attributed in the provided text to Adriano Muñoz Barrera, '
        .'Rector UCundinamarca S21, Fusagasugá, August 26, 2022. Its twelve principles are: '
        .'(1) organize and direct one\'s own life; (2) understand and defend life as a major 21st-century commitment; '
        .'(3) build a personal way of life for happiness, love, well-being, and personal development; '
        .'(4) determine one\'s life, learn from it, and adopt good practices that support individual development; '
        .'(5) value and care for physical, mental, emotional, affective, and spiritual health; '
        .'(6) go beyond oneself, improve and evolve, consider others, living beings, and nature; '
        .'(7) pursue self-improvement through technology and science when appropriate, happiness, a life plan, '
        .'and positive professional and social transformation without disregarding others; '
        .'(8) live and contribute to community peace and harmony; '
        .'(9) be free, autonomous, and responsible through dialogue and construction, self-direction, and self-governance; '
        .'(10) shape personality, character, identity, autonomy, and responsibility; '
        .'(11) make transformative choices that improve life without harming oneself, nature, society, coexistence, or democracy; '
        .'(12) explore and cultivate positive feelings, emotions, and behaviors to promote happiness. '
        .'When asked about it, explain or summarize these principles accurately and give the attribution and date above. '
        .'Do not claim the text is in the database or that its attribution was independently verified.';

    /**
     * @param  list<array{role:string,text:string}>  $history
     */
    public function ask(string $message, array $history = []): string
    {
        if (blank(config('services.gemini.key'))) {
            report(new \RuntimeException('GEMINI_API_KEY no esta configurada en el entorno.'));

            return 'El asistente no esta configurado (falta GEMINI_API_KEY). / The assistant is not configured (missing GEMINI_API_KEY).';
        }

        $databaseQuestion = $this->requiresDatabaseLookup($message);
        $contents = [];
        foreach (array_slice($history, -10) as $turn) {
            $contents[] = [
                'role' => $turn['role'] === 'model' ? 'model' : 'user',
                'parts' => [['text' => (string) $turn['text']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        for ($i = 0; $i < self::MAX_STEPS; $i++) {
            $payload = [
                'systemInstruction' => ['parts' => [['text' => self::SYSTEM]]],
                'contents' => $contents,
                'tools' => [['functionDeclarations' => $this->declarations()]],
            ];

            if ($databaseQuestion && $i === 0) {
                $payload['toolConfig'] = [
                    'functionCallingConfig' => ['mode' => 'ANY'],
                ];
            }

            $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->timeout(60)
                ->retry(3, 1500, fn ($e) => in_array($e->getCode(), [429, 500, 503]), throw: false)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/'
                    .config('services.gemini.model').':generateContent', $payload);

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

    private function requiresDatabaseLookup(string $message): bool
    {
        $hasDatabaseTopic = (bool) preg_match(
            '/\b(product\w*|producto\w*|catalog\w*|cat[aá]log\w*|categor\w*|price\w*|precio\w*|stock|'
            .'inventory|inventar\w*|existenc\w*|sale\w*|venta\w*|sold|vendid\w*|revenue|'
            .'income|ingreso\w*|supplier\w*|proveedor\w*|best.?sell\w*|m[aá]s vendido\w*)\b/iu',
            $message
        );

        return $hasDatabaseTopic
            && ! preg_match('/\b(transhuman\w*|declaraci[oó]n persona|declaration)\b/iu', $message);
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
