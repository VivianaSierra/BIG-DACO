<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $ventasHoy = (float) Venta::where('estado', 'completada')->whereDate('created_at', today())->sum('total');
        $ventasAyer = (float) Venta::where('estado', 'completada')->whereDate('created_at', today()->subDay())->sum('total');

        $porDia = Venta::where('estado', 'completada')
            ->where('created_at', '>=', today()->subDays(6))
            ->get(['total', 'created_at'])
            ->groupBy(fn ($v) => $v->created_at->toDateString())
            ->map(fn ($g) => (float) $g->sum('total'));

        $serie = collect(range(6, 0))->map(fn ($i) => $porDia[today()->subDays($i)->toDateString()] ?? 0.0)->all();

        return view('inicio', [
            'totalProductos' => Producto::count(),
            'productosNuevosMes' => Producto::where('created_at', '>=', now()->startOfMonth())->count(),
            'ventasHoy' => $ventasHoy,
            'ventasHoyCantidad' => Venta::where('estado', 'completada')->whereDate('created_at', today())->count(),
            'serieVentas' => $serie,
            'variacionVentas' => $ventasAyer > 0 ? round((($ventasHoy - $ventasAyer) / $ventasAyer) * 100, 1) : null,
            'stockCritico' => Producto::where('stock', '<', 30)->count(),
        ]);
    }
}
