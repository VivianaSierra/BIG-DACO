@extends('layout.app')

@section('titulo', 'BIG-DACO | Reporte de Ventas')

@section('contenido')

<a class="back-link" href="{{ url('/dashboard') }}">
	<i class="fa-solid fa-arrow-left"></i>
	Volver
</a>

<h2 class="page-title">Reporte de Ventas</h2>
<p class="page-subtitle">Visualiza indicadores clave y el comportamiento de ventas semanales.</p>

<div class="app-card" style="padding: 18px; margin-bottom: 16px;">
	<p>Total de ventas del día: <strong>$250.000</strong></p>
	<p style="margin-bottom: 0;">Producto más vendido: <strong>Arroz</strong></p>
</div>

<div class="app-card" style="padding: 18px;">
	<canvas id="ventasChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx = document.getElementById('ventasChart');

new Chart(ctx, {
type: 'bar',
data: {
labels: ['Lunes','Martes','Miércoles','Jueves','Viernes'],
datasets: [{
label: 'Ventas',
data: [120000,190000,300000,250000,400000],
backgroundColor: '#2f81f7',
borderRadius: 8
}]
}
});

</script>

@endsection