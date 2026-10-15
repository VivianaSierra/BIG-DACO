<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q', ''));

        $proveedores = Proveedor::query()
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($subquery) use ($busqueda) {
                    $subquery
                        ->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('telefono', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('proveedores.index', compact('proveedores', 'busqueda'));
    }

    public function create(): View
    {
        return view('proveedores.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Proveedor::create($this->validated($request));

        return redirect()->route('proveedores.index')->with('mensaje', 'Proveedor registrado correctamente.');
    }

    public function edit(Proveedor $proveedor): View
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update($this->validated($request));

        return redirect()->route('proveedores.index')->with('mensaje', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $proveedor->delete();

        return redirect()->route('proveedores.index')->with('mensaje', 'Proveedor eliminado correctamente.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
        ]);
    }
}
