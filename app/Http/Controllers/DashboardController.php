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

        return view('inicio', [
            'totalProductos' => Producto::count(),
            'productosNuevosMes' => Producto::where('created_at', '>=', now()->startOfMonth())->count(),
            'ventasHoy' => $ventasHoy,
            'variacionVentas' => $ventasAyer > 0 ? round((($ventasHoy - $ventasAyer) / $ventasAyer) * 100, 1) : null,
            'stockCritico' => Producto::where('stock', '<', 30)->count(),
        ]);
    }
}
