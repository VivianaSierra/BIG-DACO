<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('q', ''));
        $estadoStock = (string) $request->query('stock', 'todos');
        $estadosValidos = ['todos', 'critico', 'medio', 'alto'];

        if (! in_array($estadoStock, $estadosValidos, true)) {
            $estadoStock = 'todos';
        }

        $productos = Producto::query()
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($subquery) use ($busqueda) {
                    $subquery
                        ->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('categoria', 'like', "%{$busqueda}%");
                });
            })
            ->when($estadoStock === 'critico', function ($query) {
                $query->where('stock', '<', 30);
            })
            ->when($estadoStock === 'medio', function ($query) {
                $query->whereBetween('stock', [30, 49]);
            })
            ->when($estadoStock === 'alto', function ($query) {
                $query->where('stock', '>=', 50);
            })
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('productos.index', compact('productos', 'busqueda', 'estadoStock'));
    }

    public function create()
    {
        $categorias = $this->categoriasDisponibles();

        return view('productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $categorias = $this->categoriasDisponibles();

        $request->merge([
            'nombre' => $this->normalizarTexto($request->input('nombre')),
            'categoria' => trim((string) $request->input('categoria')),
            'precio' => $request->input('precio'),
            'stock' => $request->input('stock'),
        ]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'categoria' => ['required', 'string', Rule::in($categorias)],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'accion' => ['nullable', Rule::in(['guardar', 'continuar'])],
        ]);

        $existeProducto = Producto::query()
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($datos['nombre'])])
            ->where('categoria', $datos['categoria'])
            ->exists();

        if ($existeProducto) {
            return back()
                ->withErrors([
                    'nombre' => 'Ya existe un producto con el mismo nombre en esta categoría.',
                ])
                ->withInput();
        }

        $accion = $datos['accion'] ?? 'guardar';
        unset($datos['accion']);

        Producto::create($datos);

        if ($accion === 'continuar') {
            return redirect()
                ->route('productos.create')
                ->with('mensaje', 'Producto creado. Puedes seguir registrando.');
        }

        return redirect()
            ->route('productos.index')
            ->with('mensaje', 'Producto creado correctamente.');
    }

    public function show(Producto $producto)
    {
        //
    }

    public function edit(Producto $producto)
    {
        $categorias = $this->categoriasDisponibles();

        return view('productos.edit', compact('producto', 'categorias'));
    }

    public function update(Request $request, Producto $producto)
    {
        $categorias = $this->categoriasDisponibles();

        $request->merge([
            'nombre' => $this->normalizarTexto($request->input('nombre')),
            'categoria' => trim((string) $request->input('categoria')),
            'precio' => $request->input('precio'),
            'stock' => $request->input('stock'),
        ]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'categoria' => ['required', 'string', Rule::in($categorias)],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $existeProducto = Producto::query()
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($datos['nombre'])])
            ->where('categoria', $datos['categoria'])
            ->where('id', '!=', $producto->id)
            ->exists();

        if ($existeProducto) {
            return back()
                ->withErrors([
                    'nombre' => 'Ya existe otro producto con el mismo nombre en esta categoría.',
                ])
                ->withInput();
        }

        $producto->update($datos);

        return redirect()
            ->route('productos.index')
            ->with('mensaje', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()
            ->route('productos.index')
            ->with('mensaje', 'Producto eliminado correctamente.');
    }

    private function categoriasDisponibles(): array
    {
        return [
            'Granos',
            'Aceites',
            'Endulzantes',
            'Lácteos',
            'Panadería',
            'Bebidas',
            'Limpieza',
            'Aseo personal',
            'Enlatados',
            'Congelados',
        ];
    }

    private function normalizarTexto(?string $texto): string
    {
        $textoNormalizado = preg_replace('/\s+/', ' ', trim((string) $texto));

        return mb_convert_case($textoNormalizado, MB_CASE_TITLE, 'UTF-8');
    }
}
