@extends('layout.app')

@section('titulo', 'BIG-DACO | Proveedores')

@section('contenido')

<a class="back-link" href="{{ url('/dashboard') }}">
	<i class="fa-solid fa-arrow-left"></i>
	Volver
</a>

<h2 class="page-title">Gestión de Proveedores</h2>
<p class="page-subtitle">Administra la información de tus proveedores de productos.</p>

<section class="toolbar" aria-label="Acciones de proveedores">
	<form method="GET" action="{{ route('proveedores.index') }}" class="search">
		<i class="fa-solid fa-magnifying-glass"></i>
		<input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar proveedores..." aria-label="Buscar proveedores">
	</form>

	<a href="{{ route('proveedores.create') }}" class="btn-app btn-primary">
		<i class="fa-solid fa-plus"></i>
		Registrar Proveedor
	</a>
</section>

<section class="app-card">
	<div class="table-wrap">
		<table>
			<thead>
				<tr>
					<th>ID</th>
					<th>Nombre</th>
					<th>Teléfono</th>
					<th>Email</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody>
				@forelse ($proveedores as $proveedor)
					<tr>
						<td>{{ $proveedor->id }}</td>
						<td>{{ $proveedor->nombre }}</td>
						<td>{{ $proveedor->telefono }}</td>
						<td>{{ $proveedor->email }}</td>
						<td>
							<a href="{{ route('proveedores.edit', $proveedor) }}" class="btn-app btn-warning"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
							<form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Deseas eliminar este proveedor?')">
								@csrf
								@method('DELETE')
								<button type="submit" class="btn-app btn-danger"><i class="fa-regular fa-trash-can"></i> Eliminar</button>
							</form>
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="5">No hay proveedores registrados.</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>

	<div class="card-footer">
		Mostrando {{ $proveedores->count() }} de {{ $proveedores->total() }} proveedores
		{{ $proveedores->links() }}
	</div>
</section>

@endsection
