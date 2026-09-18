@extends('layout.app')

@section('titulo', 'BIG-DACO | Inventario')

@section('contenido')
	<style>
		.inventory-kpis {
			display: grid;
			grid-template-columns: repeat(5, minmax(0, 1fr));
			gap: 10px;
			margin-bottom: 14px;
		}

		.inventory-kpi {
			background: var(--surface);
			border: 1px solid var(--border);
			border-radius: 12px;
			padding: 10px 12px;
			box-shadow: var(--shadow);
		}

		.inventory-kpi p {
			margin: 0;
		}

		.inventory-kpi .label {
			font-size: 12px;
			font-weight: 700;
			color: #64748b;
		}

		.inventory-kpi .value {
			margin-top: 4px;
			font-size: 24px;
			font-weight: 800;
			line-height: 1;
		}

		.inventory-kpi .value.ok {
			color: #0e8f42;
		}

		.inventory-kpi .value.mid {
			color: #b98000;
		}

		.inventory-kpi .value.low {
			color: #d92639;
		}

		.inventory-status {
			font-weight: 700;
		}

		.inventory-status.ok {
			color: #0e8f42;
		}

		.inventory-status.mid {
			color: #b98000;
		}

		.inventory-status.low {
			color: #d92639;
		}

		@media (max-width: 1100px) {
			.inventory-kpis {
				grid-template-columns: repeat(3, minmax(0, 1fr));
			}
		}

		@media (max-width: 700px) {
			.inventory-kpis {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
		}
	</style>

<a class="back-link" href="{{ url('/dashboard') }}">
	<i class="fa-solid fa-arrow-left"></i>
	Volver
</a>

<h2 class="page-title">Inventario</h2>
<p class="page-subtitle">Controla la disponibilidad y estado del stock en tiempo real.</p>

<section class="inventory-kpis" aria-label="Resumen del inventario">
	<article class="inventory-kpi">
		<p class="label">Total productos</p>
		<p class="value">{{ $metricas['total'] }}</p>
	</article>
	<article class="inventory-kpi">
		<p class="label">Disponible</p>
		<p class="value ok">{{ $metricas['disponible'] }}</p>
	</article>
	<article class="inventory-kpi">
		<p class="label">Stock medio</p>
		<p class="value mid">{{ $metricas['medio'] }}</p>
	</article>
	<article class="inventory-kpi">
		<p class="label">Stock crítico</p>
		<p class="value low">{{ $metricas['critico'] }}</p>
	</article>
	<article class="inventory-kpi">
		<p class="label">Agotado</p>
		<p class="value low">{{ $metricas['agotado'] }}</p>
	</article>
</section>

<section class="toolbar" aria-label="Filtros de inventario">
	<form method="GET" action="{{ route('inventario.index') }}" style="display:flex; flex:1 1 620px; gap:10px; flex-wrap:wrap; align-items:center;">
		<div class="search" style="max-width: 360px;">
			<i class="fa-solid fa-magnifying-glass"></i>
			<input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por nombre o categoría">
		</div>

		<select class="form-control" name="estado" style="max-width: 220px;">
			<option value="todos" {{ $estado === 'todos' ? 'selected' : '' }}>Todos los estados</option>
			<option value="disponible" {{ $estado === 'disponible' ? 'selected' : '' }}>Disponible</option>
			<option value="medio" {{ $estado === 'medio' ? 'selected' : '' }}>Stock medio</option>
			<option value="critico" {{ $estado === 'critico' ? 'selected' : '' }}>Stock crítico</option>
			<option value="agotado" {{ $estado === 'agotado' ? 'selected' : '' }}>Agotado</option>
		</select>

		<button class="btn-app btn-muted" type="submit">
			<i class="fa-solid fa-filter"></i>
			Filtrar
		</button>

		@if ($busqueda !== '' || $estado !== 'todos')
			<a class="btn-app" style="background:#e2e8f0; color:#334155;" href="{{ route('inventario.index') }}">
				<i class="fa-solid fa-rotate-left"></i>
				Limpiar
			</a>
		@endif
	</form>
</section>

<section class="app-card">
	<div class="table-wrap">
		<table>
			<thead>
				<tr>
					<th>ID</th>
					<th>Producto</th>
					<th>Categoría</th>
					<th>Stock</th>
					<th>Estado</th>
					<th>Acción</th>
				</tr>
			</thead>
			<tbody>
				@forelse ($inventario as $producto)
					@php
						$stockClass = $producto->stock >= 50 ? 'stock-ok' : ($producto->stock >= 30 ? 'stock-mid' : ($producto->stock > 0 ? 'stock-low' : 'stock-low'));
						$estadoLabel = $producto->stock >= 50 ? 'Disponible' : ($producto->stock >= 30 ? 'Stock medio' : ($producto->stock > 0 ? 'Stock crítico' : 'Agotado'));
						$estadoClass = $producto->stock >= 50 ? 'ok' : ($producto->stock >= 30 ? 'mid' : 'low');
					@endphp
					<tr>
						<td>{{ $producto->id }}</td>
						<td>{{ $producto->nombre }}</td>
						<td>{{ $producto->categoria }}</td>
						<td><span class="stock-pill {{ $stockClass }}">{{ $producto->stock }}</span></td>
						<td><span class="inventory-status {{ $estadoClass }}">{{ $estadoLabel }}</span></td>
						<td>
							<a href="{{ route('productos.edit', $producto) }}" class="btn-app btn-warning"><i class="fa-regular fa-pen-to-square"></i> Ajustar</a>
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="6" style="text-align:center; color:#6f859f; font-weight:600;">No hay productos para los filtros aplicados.</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>

	<div class="card-footer">Mostrando {{ $inventario->count() }} de {{ $inventario->total() }} productos en inventario</div>

	@if ($inventario->hasPages())
		<div style="display:flex; justify-content:space-between; align-items:center; gap:10px; padding: 12px 16px; border-top:1px solid #e7edf4; background:#fff; flex-wrap:wrap;">
			<a
				href="{{ $inventario->previousPageUrl() ?: '#' }}"
				class="btn-app btn-muted"
				style="{{ $inventario->onFirstPage() ? 'pointer-events:none; opacity:0.55;' : '' }}"
			>
				<i class="fa-solid fa-angle-left"></i>
				Anterior
			</a>

			<span style="font-size:14px; font-weight:700; color:#526b87;">
				Página {{ $inventario->currentPage() }} de {{ $inventario->lastPage() }}
			</span>

			<a
				href="{{ $inventario->nextPageUrl() ?: '#' }}"
				class="btn-app btn-muted"
				style="{{ $inventario->hasMorePages() ? '' : 'pointer-events:none; opacity:0.55;' }}"
			>
				Siguiente
				<i class="fa-solid fa-angle-right"></i>
			</a>
		</div>
	@endif
</section>

@endsection