@extends('layout.app')

@section('titulo', 'BIG-DACO | Productos')

@section('contenido')
<a class="back-link" href="{{ url('/dashboard') }}">
	<i class="fa-solid fa-arrow-left"></i>
	Volver
</a>

<h2 class="page-title">Gestión de Productos</h2>
<p class="page-subtitle">Administra el catálogo de productos del supermercado</p>

<section class="toolbar" aria-label="Acciones de productos">
	<form method="GET" action="{{ route('productos.index') }}" style="display:flex; flex:1 1 620px; gap:10px; flex-wrap:wrap; align-items:center;">
		<div class="search" style="max-width: 360px;">
			<i class="fa-solid fa-magnifying-glass"></i>
			<input
				type="text"
				name="q"
				value="{{ $busqueda }}"
				placeholder="Buscar productos..."
				aria-label="Buscar productos"
			>
		</div>

		<select class="form-control" name="stock" style="max-width: 220px;">
			<option value="todos" {{ $estadoStock === 'todos' ? 'selected' : '' }}>Todos los niveles</option>
			<option value="critico" {{ $estadoStock === 'critico' ? 'selected' : '' }}>Stock crítico (&lt; 30)</option>
			<option value="medio" {{ $estadoStock === 'medio' ? 'selected' : '' }}>Stock medio (30 - 49)</option>
			<option value="alto" {{ $estadoStock === 'alto' ? 'selected' : '' }}>Stock alto (&gt;= 50)</option>
		</select>

		<button class="btn-app btn-muted" type="submit">
			<i class="fa-solid fa-filter"></i>
			Filtrar
		</button>

		@if ($busqueda !== '' || $estadoStock !== 'todos')
			<a class="btn-app" style="background:#e2e8f0; color:#334155;" href="{{ route('productos.index') }}">
				<i class="fa-solid fa-rotate-left"></i>
				Limpiar
			</a>
		@endif
	</form>

	<a class="btn-app btn-primary" href="{{ route('productos.create') }}">
		<i class="fa-solid fa-plus"></i>
		Añadir Producto
	</a>
</section>

<section class="app-card" aria-label="Listado de productos">
	<div class="table-wrap">
		<table>
			<thead>
				<tr>
					<th>ID</th>
					<th>Nombre</th>
					<th>Categoría</th>
					<th>Precio</th>
					<th>Stock</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody>
				@forelse ($productos as $producto)
					<tr>
						<td>{{ $producto->id }}</td>
						<td>{{ $producto->nombre }}</td>
						<td>{{ $producto->categoria }}</td>
						<td>${{ number_format((float) $producto->precio, 0, ',', '.') }}</td>
						<td>
							@php
								$stockClass = $producto->stock >= 50 ? 'stock-ok' : ($producto->stock >= 30 ? 'stock-mid' : 'stock-low');
							@endphp
							<span class="stock-pill {{ $stockClass }}">{{ $producto->stock }}</span>
						</td>
						<td style="display: flex; gap: 8px; flex-wrap: wrap;">
							<a href="{{ route('productos.edit', $producto) }}" class="btn-app btn-warning"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
							<form action="{{ route('productos.destroy', $producto) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este producto?')">
								@csrf
								@method('DELETE')
								<button type="submit" class="btn-app btn-danger"><i class="fa-regular fa-trash-can"></i> Eliminar</button>
							</form>
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="6" style="text-align:center; color:#6f859f; font-weight:600;">No se encontraron productos.</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>

	<div class="card-footer">
		Mostrando {{ $productos->count() }} de {{ $productos->total() }} productos
	</div>

	@if ($productos->hasPages())
		<div style="display:flex; justify-content:space-between; align-items:center; gap:10px; padding: 12px 16px; border-top:1px solid #e7edf4; background:#fff; flex-wrap:wrap;">
			<a
				href="{{ $productos->previousPageUrl() ?: '#' }}"
				class="btn-app btn-muted"
				style="{{ $productos->onFirstPage() ? 'pointer-events:none; opacity:0.55;' : '' }}"
			>
				<i class="fa-solid fa-angle-left"></i>
				Anterior
			</a>

			<span style="font-size:14px; font-weight:700; color:#526b87;">
				Página {{ $productos->currentPage() }} de {{ $productos->lastPage() }}
			</span>

			<a
				href="{{ $productos->nextPageUrl() ?: '#' }}"
				class="btn-app btn-muted"
				style="{{ $productos->hasMorePages() ? '' : 'pointer-events:none; opacity:0.55;' }}"
			>
				Siguiente
				<i class="fa-solid fa-angle-right"></i>
			</a>
		</div>
	@endif
</section>
@endsection