<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Venta;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function ventas(): View
    {
        $ventasHoy = (float) Venta::where('estado', 'completada')->whereDate('created_at', today())->sum('total');

        $productoTop = DetalleVenta::query()
            ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->join('productos', 'productos.id', '=', 'detalle_ventas.producto_id')
            ->where('ventas.estado', 'completada')
            ->selectRaw('productos.nombre as nombre, SUM(detalle_ventas.cantidad) as unidades')
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('unidades')
            ->first();

        $porDia = Venta::where('estado', 'completada')
            ->where('created_at', '>=', today()->subDays(6))
            ->get(['total', 'created_at'])
            ->groupBy(fn ($venta) => $venta->created_at->toDateString())
            ->map(fn ($grupo) => (float) $grupo->sum('total'));

        $dias = collect(range(6, 0))->map(fn ($i) => today()->subDays($i));

        return view('reportes.ventas', [
            'ventasHoy' => $ventasHoy,
            'productoTop' => $productoTop,
            'etiquetas' => $dias->map(fn ($dia) => $dia->format('d/m'))->all(),
            'valores' => $dias->map(fn ($dia) => $porDia[$dia->toDateString()] ?? 0.0)->all(),
        ]);
    }
}
