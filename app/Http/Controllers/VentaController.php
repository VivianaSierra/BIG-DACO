<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('q', ''));
        $desde = (string) $request->query('desde', '');
        $hasta = (string) $request->query('hasta', '');
        $estado = (string) $request->query('estado', 'todos');

        if (! in_array($estado, ['todos', 'activas', 'anuladas'], true)) {
            $estado = 'todos';
        }

        $ventas = Venta::query()
            ->with(['detalleVentas.producto'])
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($subquery) use ($busqueda) {
                    $subquery
                        ->where('id', 'like', "%{$busqueda}%")
                        ->orWhereHas('detalleVentas.producto', function ($productoQuery) use ($busqueda) {
                            $productoQuery->where('nombre', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->when($desde !== '', function ($query) use ($desde) {
                $query->whereDate('created_at', '>=', $desde);
            })
            ->when($hasta !== '', function ($query) use ($hasta) {
                $query->whereDate('created_at', '<=', $hasta);
            })
            ->when($estado === 'activas', function ($query) {
                $query->where('estado', 'completada');
            })
            ->when($estado === 'anuladas', function ($query) {
                $query->where('estado', 'anulada');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $metricas = [
            'total_ventas' => Venta::count(),
            'activas' => Venta::where('estado', 'completada')->count(),
            'anuladas' => Venta::where('estado', 'anulada')->count(),
            'ingreso_total' => (float) Venta::where('estado', 'completada')->sum('total'),
            'ventas_hoy' => Venta::whereDate('created_at', now()->toDateString())->where('estado', 'completada')->count(),
            'ingreso_hoy' => (float) Venta::whereDate('created_at', now()->toDateString())->where('estado', 'completada')->sum('total'),
        ];

        return view('ventas.index', compact('ventas', 'busqueda', 'desde', 'hasta', 'estado', 'metricas'));
    }

    public function create()
    {
        $productos = $this->obtenerProductosParaVenta();

        return view('ventas.create', compact('productos'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'producto_id' => ['required', 'array', 'min:1'],
            'producto_id.*' => ['required', 'integer', 'exists:productos,id'],
            'cantidad' => ['required', 'array', 'min:1'],
            'cantidad.*' => ['required', 'integer', 'min:1'],
            'fecha' => ['nullable', 'date'],
        ]);

        if (count($datos['producto_id']) !== count($datos['cantidad'])) {
            throw ValidationException::withMessages([
                'cantidad' => 'Las líneas de productos no son válidas.',
            ]);
        }

        $lineas = collect($datos['producto_id'])
            ->values()
            ->map(function ($productoId, $index) use ($datos) {
                return [
                    'producto_id' => (int) $productoId,
                    'cantidad' => (int) ($datos['cantidad'][$index] ?? 0),
                ];
            })
            ->filter(fn (array $linea) => $linea['cantidad'] > 0)
            ->values();

        if ($lineas->isEmpty()) {
            throw ValidationException::withMessages([
                'cantidad' => 'Agrega al menos un producto con cantidad válida.',
            ]);
        }

        $hayDuplicados = $lineas
            ->pluck('producto_id')
            ->duplicates()
            ->isNotEmpty();

        if ($hayDuplicados) {
            throw ValidationException::withMessages([
                'producto_id' => 'No repitas productos en la misma venta.',
            ]);
        }

        $venta = DB::transaction(function () use ($datos, $lineas) {
            $productos = Producto::query()
                ->whereIn('id', $lineas->pluck('producto_id')->unique()->all())
                ->lockForUpdate()
                ->get(['id', 'nombre', 'precio', 'stock'])
                ->keyBy('id');

            $cantidadesPorProducto = $lineas
                ->groupBy('producto_id')
                ->map(fn (Collection $items) => (int) $items->sum('cantidad'));

            foreach ($cantidadesPorProducto as $productoId => $cantidadTotal) {
                $producto = $productos->get((int) $productoId);

                if (! $producto || $producto->stock < $cantidadTotal) {
                    $nombreProducto = $producto?->nombre ?? 'Producto';

                    throw ValidationException::withMessages([
                        'cantidad' => 'No hay stock suficiente para ' . $nombreProducto . '.',
                    ]);
                }
            }

            $totalVenta = $lineas->sum(function (array $linea) use ($productos) {
                $producto = $productos->get($linea['producto_id']);

                return ((float) $producto->precio) * $linea['cantidad'];
            });

            $fechaVenta = isset($datos['fecha']) && $datos['fecha'] !== ''
                ? Carbon::parse($datos['fecha'])->setTime(12, 0)
                : now();

            $venta = new Venta();
            $venta->total = $totalVenta;
            $venta->estado = 'completada';
            $venta->anulada_at = null;
            $venta->created_at = $fechaVenta;
            $venta->updated_at = $fechaVenta;
            $venta->save();

            foreach ($lineas as $linea) {
                $producto = $productos->get($linea['producto_id']);

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $linea['cantidad'],
                    'precio' => (float) $producto->precio,
                ]);
            }

            foreach ($cantidadesPorProducto as $productoId => $cantidadTotal) {
                $producto = $productos->get((int) $productoId);
                $producto->decrement('stock', $cantidadTotal);
            }

            return $venta;
        });

        return redirect()
            ->route('ventas.ticket', $venta)
            ->with('mensaje', 'Venta registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        if ($venta->estado === 'anulada') {
            return redirect()
                ->route('ventas.index')
                ->with('mensaje', 'La venta ya estaba anulada.');
        }

        DB::transaction(function () use ($venta) {
            $venta->load('detalleVentas.producto');

            foreach ($venta->detalleVentas as $detalle) {
                if ($detalle->producto) {
                    $detalle->producto->increment('stock', $detalle->cantidad);
                }
            }

            $venta->update([
                'estado' => 'anulada',
                'anulada_at' => now(),
            ]);
        });

        return redirect()
            ->route('ventas.index')
            ->with('mensaje', 'Venta anulada y stock restaurado correctamente.');
    }

    public function ticket(Venta $venta)
    {
        $venta->load('detalleVentas.producto');

        return view('ventas.ticket', compact('venta'));
    }

    private function obtenerProductosParaVenta()
    {
        return Producto::query()
            ->where('stock', '>', 0)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'precio', 'stock']);
    }
}
