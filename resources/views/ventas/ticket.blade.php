@extends('layout.app')

@section('titulo', 'BIG-DACO | Ticket Venta #' . $venta->id)

@section('contenido')
	<a class="back-link" href="{{ route('ventas.index') }}">
		<i class="fa-solid fa-arrow-left"></i>
		Volver a ventas
	</a>

	<h2 class="page-title">Ticket de Venta #{{ $venta->id }}</h2>
	<p class="page-subtitle">Fecha: {{ $venta->created_at?->format('Y-m-d H:i') }} · Estado: {{ $venta->estado === 'anulada' ? 'Anulada' : 'Activa' }}</p>

	<section class="toolbar" aria-label="Acciones de ticket">
		<div></div>
		<div class="form-actions" style="margin-top: 0;">
			<button class="btn-app btn-primary" onclick="window.print()">
				<i class="fa-solid fa-print"></i>
				Imprimir ticket
			</button>
		</div>
	</section>

	<section class="app-card" id="ticket-venta">
		<div class="table-wrap">
			<table>
				<thead>
					<tr>
						<th>Producto</th>
						<th>Cantidad</th>
						<th>Precio unitario</th>
						<th>Subtotal</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($venta->detalleVentas as $detalle)
						<tr>
							<td>{{ $detalle->producto?->nombre ?? 'N/A' }}</td>
							<td>{{ $detalle->cantidad }}</td>
							<td>${{ number_format((float) $detalle->precio, 0, ',', '.') }}</td>
							<td>${{ number_format((float) $detalle->precio * (int) $detalle->cantidad, 0, ',', '.') }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<div class="card-footer" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
			<span style="font-weight:700; color:#334155;">Total de ítems: {{ $venta->detalleVentas->sum('cantidad') }}</span>
			<span style="font-weight:800; color:#0f274a;">TOTAL: ${{ number_format((float) $venta->total, 0, ',', '.') }}</span>
		</div>
	</section>
@endsection
