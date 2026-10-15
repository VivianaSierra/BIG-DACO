@extends('layout.app')

@section('titulo', 'BIG-DACO | Reporte de Ventas')

@section('contenido')

<a class="back-link" href="{{ url('/dashboard') }}">
	<i class="fa-solid fa-arrow-left"></i>
	Volver
</a>

<h2 class="page-title">Reporte de Ventas</h2>
<p class="page-subtitle">Visualiza indicadores clave y el comportamiento de ventas de los últimos 7 días.</p>

<div class="app-card" style="padding: 18px; margin-bottom: 16px;">
	<p>Total de ventas del día: <strong>${{ number_format($ventasHoy, 0, ',', '.') }}</strong></p>
	<p style="margin-bottom: 0;">Producto más vendido:
		<strong>{{ $productoTop ? $productoTop->nombre.' ('.(int) $productoTop->unidades.' uds.)' : 'Sin ventas registradas' }}</strong>
	</p>
</div>

<div class="app-card" style="padding: 18px;">
	<canvas id="ventasChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('ventasChart'), {
	type: 'bar',
	data: {
		labels: @json($etiquetas),
		datasets: [{
			label: 'Ventas',
			data: @json($valores),
			backgroundColor: '#2f81f7',
			borderRadius: 8
		}]
	}
});
</script>

@endsection
