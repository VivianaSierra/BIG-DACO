@extends('layout.app')

@section('titulo', 'BIG-DACO | Dashboard')

@section('contenido')
	<style>
		.kpi-grid {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 14px;
			margin-bottom: 22px;
		}

		.kpi-card {
			background: var(--surface);
			border: 1px solid var(--border);
			border-radius: 12px;
			padding: 16px;
			box-shadow: var(--shadow);
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			min-height: 122px;
		}

		.kpi-title {
			font-size: 16px;
			color: #365173;
			font-weight: 500;
			margin: 0 0 6px;
		}

		.kpi-value {
			font-size: 30px;
			line-height: 1;
			margin: 0;
			font-weight: 700;
			letter-spacing: -0.6px;
		}

		.kpi-meta {
			margin-top: 8px;
			font-size: 14px;
			font-weight: 600;
		}

		.kpi-meta.ok {
			color: #22b36b;
		}

		.kpi-meta.warn {
			color: #ef3a4a;
		}

		.kpi-icon {
			width: 46px;
			height: 46px;
			border-radius: 12px;
			display: grid;
			place-items: center;
			font-size: 22px;
			flex-shrink: 0;
		}

		.kpi-icon.blue {
			background: #dbeafe;
			color: #2563eb;
		}

		.kpi-icon.green {
			background: #dcfce9;
			color: #22b36b;
		}

		.kpi-icon.red {
			background: #ffe7ea;
			color: #ef3a4a;
		}

		h2.section-title {
			margin: 0 0 14px;
			font-size: 22px;
			font-weight: 800;
		}

		.module-grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 14px;
		}

		.module-card {
			background: var(--surface);
			border: 1px solid var(--border);
			border-radius: 12px;
			padding: 16px;
			box-shadow: var(--shadow);
		}

		.module-icon {
			width: 52px;
			height: 52px;
			border-radius: 14px;
			display: grid;
			place-items: center;
			color: #fff;
			font-size: 24px;
			margin-bottom: 10px;
		}

		.module-icon.blue { background: linear-gradient(135deg, #3b82f6, #1e40af); }
		.module-icon.violet { background: linear-gradient(135deg, #a855f7, #7e22ce); }
		.module-icon.green { background: linear-gradient(135deg, #22c55e, #15803d); }
		.module-icon.orange { background: linear-gradient(135deg, #fb923c, #ea580c); }

		.module-card h3 {
			margin: 0;
			font-size: 16px;
			font-weight: 800;
		}

		.module-card p {
			margin: 6px 0 10px;
			color: var(--muted);
			font-size: 14px;
		}

		.module-link {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			font-size: 14px;
			font-weight: 800;
		}

		.module-link.blue { color: #2563eb; }
		.module-link.violet { color: #9333ea; }
		.module-link.green { color: #16a34a; }
		.module-link.orange { color: #ea580c; }

		.footer-note {
			text-align: center;
			color: #8ba0b8;
			margin-top: 20px;
			font-size: 14px;
			font-weight: 500;
		}

		@media (max-width: 992px) {
			.kpi-grid { grid-template-columns: 1fr; }
			.module-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
		}

		@media (max-width: 640px) {
			.module-grid { grid-template-columns: 1fr; }
		}
	</style>

	<section class="kpi-grid" aria-label="Resumen de indicadores">
		<article class="kpi-card">
			<div>
				<p class="kpi-title">Total productos</p>
				<p class="kpi-value">{{ number_format($totalProductos) }}</p>
				<p class="kpi-meta ok"><i class="fa-solid fa-arrow-trend-up"></i> +{{ $productosNuevosMes }} este mes</p>
			</div>
			<div class="kpi-icon blue"><i class="fa-solid fa-cube"></i></div>
		</article>

		<article class="kpi-card">
			<div>
				<p class="kpi-title">Ventas hoy</p>
				<p class="kpi-value">${{ number_format($ventasHoy, 0, '.', ',') }}</p>
				@if ($variacionVentas === null)
					<p class="kpi-meta">Sin ventas ayer para comparar</p>
				@else
					<p class="kpi-meta {{ $variacionVentas >= 0 ? 'ok' : 'warn' }}">
						<i class="fa-solid fa-arrow-trend-{{ $variacionVentas >= 0 ? 'up' : 'down' }}"></i>
						{{ $variacionVentas >= 0 ? '+' : '' }}{{ $variacionVentas }}% vs ayer
					</p>
				@endif
			</div>
			<div class="kpi-icon green"><i class="fa-solid fa-dollar-sign"></i></div>
		</article>

		<article class="kpi-card">
			<div>
				<p class="kpi-title">Stock crítico</p>
				<p class="kpi-value">{{ $stockCritico }}</p>
				@if ($stockCritico > 0)
					<a class="kpi-meta warn" href="{{ url('/inventario?estado=critico') }}">Requiere atención</a>
				@else
					<p class="kpi-meta ok">Todo en orden</p>
				@endif
			</div>
			<div class="kpi-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
		</article>
	</section>

	<section aria-label="Módulos principales">
		<h2 class="section-title">Módulos principales</h2>
		<div class="module-grid">
			<article class="module-card">
				<div class="module-icon blue"><i class="fa-solid fa-cube"></i></div>
				<h3>Productos</h3>
				<p>Gestión de inventario</p>
				<a class="module-link blue" href="{{ url('/productos') }}">Entrar <i class="fa-solid fa-angle-right"></i></a>
			</article>

			<article class="module-card">
				<div class="module-icon violet"><i class="fa-solid fa-chart-column"></i></div>
				<h3>Inventario</h3>
				<p>Control de stock</p>
				<a class="module-link violet" href="{{ url('/inventario') }}">Entrar <i class="fa-solid fa-angle-right"></i></a>
			</article>

			<article class="module-card">
				<div class="module-icon green"><i class="fa-solid fa-arrow-trend-up"></i></div>
				<h3>Ventas</h3>
				<p>Registro de ventas</p>
				<a class="module-link green" href="{{ url('/ventas') }}">Entrar <i class="fa-solid fa-angle-right"></i></a>
			</article>

			<article class="module-card">
				<div class="module-icon orange"><i class="fa-regular fa-file-lines"></i></div>
				<h3>Reportes</h3>
				<p>Análisis y reportes</p>
				<a class="module-link orange" href="{{ url('/reportes/ventas') }}">Entrar <i class="fa-solid fa-angle-right"></i></a>
			</article>
		</div>
	</section>

	<p class="footer-note">Sistema de gestión integrado · Actualizado en tiempo real</p>
@endsection