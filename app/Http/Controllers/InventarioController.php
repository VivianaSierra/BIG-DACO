<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('q', ''));
        $estado = (string) $request->query('estado', 'todos');
        $estadosValidos = ['todos', 'disponible', 'medio', 'critico', 'agotado'];

        if (! in_array($estado, $estadosValidos, true)) {
            $estado = 'todos';
        }

        $inventario = Producto::query()
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($subquery) use ($busqueda) {
                    $subquery
                        ->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('categoria', 'like', "%{$busqueda}%");
                });
            })
            ->when($estado === 'disponible', function ($query) {
                $query->where('stock', '>=', 50);
            })
            ->when($estado === 'medio', function ($query) {
                $query->whereBetween('stock', [30, 49]);
            })
            ->when($estado === 'critico', function ($query) {
                $query->whereBetween('stock', [1, 29]);
            })
            ->when($estado === 'agotado', function ($query) {
                $query->where('stock', '=', 0);
            })
            ->orderBy('stock')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        $metricas = [
            'total' => Producto::count(),
            'disponible' => Producto::where('stock', '>=', 50)->count(),
            'medio' => Producto::whereBetween('stock', [30, 49])->count(),
            'critico' => Producto::whereBetween('stock', [1, 29])->count(),
            'agotado' => Producto::where('stock', 0)->count(),
        ];

        return view('inventario.index', compact('inventario', 'metricas', 'busqueda', 'estado'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
