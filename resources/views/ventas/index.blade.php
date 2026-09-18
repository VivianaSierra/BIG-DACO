@extends('layout.app')

@section('titulo', 'BIG-DACO | Ventas')

@section('contenido')
	<style>
		.sales-kpis {
			display: grid;
			grid-template-columns: repeat(6, minmax(0, 1fr));
			gap: 10px;
			margin-bottom: 14px;
		}

		.sales-kpi {
			background: var(--surface);
			border: 1px solid var(--border);
			border-radius: 12px;
			padding: 10px 12px;
			box-shadow: var(--shadow);
		}

		.sales-kpi p {
			margin: 0;
		}

		.sales-kpi .label {
			font-size: 12px;
			font-weight: 700;
			color: #64748b;
		}

		.sales-kpi .value {
			margin-top: 4px;
			font-size: 20px;
			font-weight: 800;
			line-height: 1;
		}

		.badge-state {
			display: inline-block;
			padding: 5px 10px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 800;
		}

		.badge-state.ok {
			background: #dff8e7;
			color: #0e8f42;
		}

		.badge-state.off {
			background: #ffe5e8;
			color: #d92639;
		}

		@media (max-width: 1200px) {
			.sales-kpis {
				grid-template-columns: repeat(3, minmax(0, 1fr));
			}
		}

		@media (max-width: 700px) {
			.sales-kpis {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
		}
	</style>

	<a class="back-link" href="{{ url('/dashboard') }}">
		<i class="fa-solid fa-arrow-left"></i>
		Volver
	</a>

	<h2 class="page-title">Registro de Ventas</h2>
	<p class="page-subtitle">Consulta, imprime y anula ventas registradas sin perder historial.</p>

	<section class="sales-kpis" aria-label="Resumen de ventas">
		<article class="sales-kpi">
			<p class="label">Ventas registradas</p>
			<p class="value">{{ $metricas['total_ventas'] }}</p>
		</article>
		<article class="sales-kpi">
			<p class="label">Ventas activas</p>
			<p class="value">{{ $metricas['activas'] }}</p>
		</article>
		<article class="sales-kpi">
			<p class="label">Ventas anuladas</p>
			<p class="value">{{ $metricas['anuladas'] }}</p>
		</article>
		<article class="sales-kpi">
			<p class="label">Ingreso total</p>
			<p class="value">${{ number_format($metricas['ingreso_total'], 0, ',', '.') }}</p>
		</article>
		<article class="sales-kpi">
			<p class="label">Ventas hoy</p>
			<p class="value">{{ $metricas['ventas_hoy'] }}</p>
		</article>
		<article class="sales-kpi">
			<p class="label">Ingreso hoy</p>
			<p class="value">${{ number_format($metricas['ingreso_hoy'], 0, ',', '.') }}</p>
		</article>
	</section>

	<section class="toolbar" aria-label="Acciones de ventas">
		<form method="GET" action="{{ route('ventas.index') }}" style="display:flex; flex:1 1 780px; gap:10px; flex-wrap:wrap; align-items:center;">
			<div class="search" style="max-width: 280px;">
				<i class="fa-solid fa-magnifying-glass"></i>
				<input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por ID o producto..." aria-label="Buscar ventas">
			</div>

			<input type="date" class="form-control" name="desde" value="{{ $desde }}" style="max-width: 170px;">
			<input type="date" class="form-control" name="hasta" value="{{ $hasta }}" style="max-width: 170px;">

			<select class="form-control" name="estado" style="max-width: 160px;">
				<option value="todos" {{ $estado === 'todos' ? 'selected' : '' }}>Todos</option>
				<option value="activas" {{ $estado === 'activas' ? 'selected' : '' }}>Activas</option>
				<option value="anuladas" {{ $estado === 'anuladas' ? 'selected' : '' }}>Anuladas</option>
			</select>

			<button class="btn-app btn-muted" type="submit">
				<i class="fa-solid fa-filter"></i>
				Filtrar
			</button>

			@if ($busqueda !== '' || $desde !== '' || $hasta !== '' || $estado !== 'todos')
				<a class="btn-app" style="background:#e2e8f0; color:#334155;" href="{{ route('ventas.index') }}">
					<i class="fa-solid fa-rotate-left"></i>
					Limpiar
				</a>
			@endif
		</form>

		<a href="{{ route('ventas.create') }}" class="btn-app btn-primary">
			<i class="fa-solid fa-plus"></i>
			Nueva Venta
		</a>
	</section>

	<section class="app-card">
		<div class="table-wrap">
			<table>
				<thead>
					<tr>
						<th>ID</th>
						<th>Fecha</th>
						<th>Ítems</th>
						<th>Total</th>
						<th>Estado</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					@forelse ($ventas as $venta)
						@php
							$items = $venta->detalleVentas;
							$resumenItems = $items->map(function ($detalle) {
								$nombre = $detalle->producto?->nombre ?? 'N/A';
								return $nombre . ' x' . $detalle->cantidad;
							})->implode(', ');
						@endphp
						<tr>
							<td>{{ $venta->id }}</td>
							<td>{{ $venta->created_at?->format('Y-m-d') }}</td>
							<td>{{ $resumenItems !== '' ? $resumenItems : 'Sin detalle' }}</td>
							<td>${{ number_format((float) $venta->total, 0, ',', '.') }}</td>
							<td>
								@if ($venta->estado === 'anulada')
									<span class="badge-state off">Anulada</span>
								@else
									<span class="badge-state ok">Activa</span>
								@endif
							</td>
							<td style="display:flex; gap:8px; flex-wrap:wrap;">
								<a href="{{ route('ventas.ticket', $venta) }}" class="btn-app btn-muted"><i class="fa-solid fa-receipt"></i> Ticket</a>
								@if ($venta->estado !== 'anulada')
									<form action="{{ route('ventas.destroy', $venta) }}" method="POST" onsubmit="return confirm('¿Deseas anular esta venta? El stock se restaurará automáticamente.')">
										@csrf
										@method('DELETE')
										<button type="submit" class="btn-app btn-danger"><i class="fa-regular fa-circle-xmark"></i> Anular</button>
									</form>
								@else
									<span style="font-size:12px; color:#8a9bb1; font-weight:700; align-self:center;">{{ $venta->anulada_at?->format('Y-m-d H:i') }}</span>
								@endif
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="6" style="text-align:center; color:#6f859f; font-weight:600;">No se encontraron ventas para los filtros aplicados.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="card-footer">Mostrando {{ $ventas->count() }} de {{ $ventas->total() }} ventas</div>

		@if ($ventas->hasPages())
			<div style="display:flex; justify-content:space-between; align-items:center; gap:10px; padding: 12px 16px; border-top:1px solid #e7edf4; background:#fff; flex-wrap:wrap;">
				<a
					href="{{ $ventas->previousPageUrl() ?: '#' }}"
					class="btn-app btn-muted"
					style="{{ $ventas->onFirstPage() ? 'pointer-events:none; opacity:0.55;' : '' }}"
				>
					<i class="fa-solid fa-angle-left"></i>
					Anterior
				</a>

				<span style="font-size:14px; font-weight:700; color:#526b87;">
					Página {{ $ventas->currentPage() }} de {{ $ventas->lastPage() }}
				</span>

				<a
					href="{{ $ventas->nextPageUrl() ?: '#' }}"
					class="btn-app btn-muted"
					style="{{ $ventas->hasMorePages() ? '' : 'pointer-events:none; opacity:0.55;' }}"
				>
					Siguiente
					<i class="fa-solid fa-angle-right"></i>
				</a>
			</div>
		@endif
	</section>
@endsection
