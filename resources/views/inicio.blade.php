@extends('layout.app')

@section('titulo', 'BIG-DACO | Inicio')
@section('sin_contenedor', '1')

@php
	$hora = (int) now()->format('G');
	$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
	$maxSerie = max($serieVentas) ?: 1;
@endphp

@section('encabezado')
	<style>
		body { background: linear-gradient(135deg, #f6f8fb 0%, #eef2f9 100%) fixed; }

		.hd { background: rgba(255,255,255,.92); border-bottom: 1px solid #e6ebf2; position: sticky; top: 0; z-index: 20; backdrop-filter: blur(8px); }
		.hd-in { max-width: 1210px; margin: 0 auto; padding: 12px 20px; display: flex; align-items: center; gap: 28px; }
		.hd-brand { display: flex; align-items: center; gap: 10px; }
		.hd-logo { width: 38px; height: 38px; border-radius: 11px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; display: grid; place-items: center; font-size: 17px; box-shadow: 0 6px 14px rgba(37,99,235,.35); }
		.hd-brand strong { display: block; font-size: 15px; line-height: 1.1; }
		.hd-brand small { font-size: 11px; color: var(--muted); }
		.hd-nav { display: flex; gap: 4px; flex: 1; }
		.hd-nav a { display: inline-flex; align-items: center; gap: 7px; padding: 9px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #3b4f6b; }
		.hd-nav a:hover { background: #f0f4fa; }
		.hd-nav a.act { background: #e3edff; color: #2563eb; }
		.hd-act { display: flex; align-items: center; gap: 10px; }
		.ic-btn { position: relative; width: 38px; height: 38px; border-radius: 11px; border: 1px solid #e1e7f0; background: #fff; display: grid; place-items: center; color: #3b4f6b; font-size: 14px; }
		.ic-btn:hover { background: #f4f7fb; }
		.ic-btn .dot { position: absolute; top: 8px; right: 9px; width: 7px; height: 7px; border-radius: 50%; background: #ef3a4a; }
		.btn-nv { display: inline-flex; align-items: center; gap: 8px; padding: 11px 18px; border-radius: 11px; background: #2563eb; color: #fff; font-size: 13px; font-weight: 700; box-shadow: 0 8px 18px rgba(37,99,235,.35); }
		.btn-nv:hover { background: #1d4ed8; }
		@media (max-width: 900px) { .hd-in { flex-wrap: wrap; gap: 12px; } .hd-nav { order: 3; flex-basis: 100%; overflow-x: auto; } }
	</style>

	<header class="hd">
		<div class="hd-in">
			<div class="hd-brand">
				<div class="hd-logo"><i class="fa-solid fa-warehouse"></i></div>
				<div><strong>BIG-DACO</strong><small>Gestión inteligente</small></div>
			</div>

			<nav class="hd-nav" aria-label="Navegación principal">
				<a class="act" href="{{ url('/dashboard') }}"><i class="fa-solid fa-house"></i> Inicio</a>
				<a href="{{ url('/productos') }}"><i class="fa-solid fa-cube"></i> Productos</a>
				<a href="{{ url('/proveedores') }}"><i class="fa-solid fa-truck"></i> Proveedores</a>
				<a href="{{ url('/ventas') }}"><i class="fa-solid fa-cart-shopping"></i> Ventas</a>
				<a href="{{ url('/inventario') }}"><i class="fa-solid fa-warehouse"></i> Inventario</a>
				<a href="{{ url('/reportes/ventas') }}"><i class="fa-solid fa-chart-column"></i> Reportes</a>
			</nav>

			<div class="hd-act">
				<a class="ic-btn" href="{{ url('/productos') }}" title="Buscar productos"><i class="fa-solid fa-magnifying-glass"></i></a>
				<a class="ic-btn" href="{{ url('/inventario?estado=critico') }}" title="Alertas de stock">
					<i class="fa-regular fa-bell"></i>
					@if ($stockCritico > 0)<span class="dot"></span>@endif
				</a>
				<a class="btn-nv" href="{{ url('/ventas/create') }}"><i class="fa-solid fa-cart-shopping"></i> Nueva venta</a>
			</div>
		</div>
	</header>
@endsection

@section('contenido')
	<style>
		.home { max-width: 1210px; margin: 0 auto; padding: 26px 20px 20px; }
		.eyebrow { font-size: 11px; font-weight: 800; letter-spacing: 1.2px; color: #2563eb; text-transform: uppercase; margin: 0 0 8px; }
		.home-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 22px; }
		.home-head h1 { margin: 0; font-size: 36px; font-weight: 600; letter-spacing: -1px; }
		.home-head p { margin: 6px 0 0; color: var(--muted); font-size: 14px; }
		.live { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; background: #fff; border: 1px solid #e6ebf2; border-radius: 999px; font-size: 12px; color: #52667f; box-shadow: 0 2px 8px rgba(15,39,74,.05); white-space: nowrap; }
		.live::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #16a34a; }

		.stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; margin-bottom: 34px; }
		.stat { background: #fff; border: 1px solid #edf1f7; border-radius: 18px; padding: 20px 22px; box-shadow: 0 10px 28px rgba(15,39,74,.06); min-height: 180px; display: flex; flex-direction: column; }
		.stat-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
		.chip-ic { width: 38px; height: 38px; border-radius: 11px; display: grid; place-items: center; font-size: 15px; }
		.chip-ic.blue { background: #e6efff; color: #2563eb; }
		.chip-ic.green { background: #dcf6e8; color: #16a34a; }
		.chip-ic.red { background: #ffe6ea; color: #ef3a4a; }
		.tag { font-size: 10px; font-weight: 800; letter-spacing: .8px; padding: 5px 10px; border-radius: 7px; text-transform: uppercase; }
		.tag.gray { background: #eef1f5; color: #52667f; }
		.tag.green { background: #dcf6e8; color: #15803d; }
		.tag.red { background: #ffe6ea; color: #e11d48; }
		.stat-label { font-size: 13px; color: #52667f; margin: 0 0 6px; }
		.stat-value { font-size: 34px; font-weight: 800; letter-spacing: -1px; margin: 0; line-height: 1.1; }
		.stat-foot { margin-top: auto; padding-top: 14px; display: flex; justify-content: space-between; align-items: flex-end; font-size: 12px; font-weight: 600; }
		.ok { color: #16a34a; } .bad { color: #ef3a4a; } .soft { color: #6f859f; font-weight: 500; }
		.bars { display: flex; align-items: flex-end; gap: 3px; height: 24px; }
		.bars i { display: block; width: 7px; border-radius: 2px; background: linear-gradient(180deg, #34d399, #16a34a); }

		.sec-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px; }
		.sec-head h2 { margin: 0; font-size: 26px; font-weight: 600; letter-spacing: -.5px; }
		.btn-ghost { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border: 1px solid #e1e7f0; background: #fff; border-radius: 11px; font-size: 12px; font-weight: 700; color: #3b4f6b; }
		.btn-ghost:hover { background: #f4f7fb; }

		.mods { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
		.mod { display: flex; flex-direction: column; background: #fff; border: 1px solid #edf1f7; border-radius: 18px; padding: 18px 18px 16px; min-height: 208px; color: inherit; transition: transform .15s, box-shadow .15s; }
		.mod:hover { transform: translateY(-3px); box-shadow: 0 14px 30px rgba(15,39,74,.10); }
		.mod-ic { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; font-size: 17px; margin-bottom: 26px; }
		.mod-ic.blue { background: #e6efff; color: #2563eb; }
		.mod-ic.violet { background: #efe7ff; color: #7c3aed; }
		.mod-ic.green { background: #dcf6e8; color: #16a34a; }
		.mod-ic.orange { background: #ffeedd; color: #ea580c; }
		.mod h3 { margin: 0 0 8px; font-size: 18px; font-weight: 600; }
		.mod p { margin: 0; color: #6f859f; font-size: 13px; line-height: 1.5; }
		.mod-foot { margin-top: auto; padding-top: 18px; display: flex; justify-content: space-between; align-items: center; font-size: 11px; font-weight: 700; color: #52667f; }
		.go { width: 28px; height: 28px; border-radius: 50%; background: #eef1f5; display: grid; place-items: center; font-size: 11px; color: #52667f; }

		.foot { margin-top: 36px; padding-top: 18px; border-top: 1px solid #e3e9f1; display: flex; justify-content: space-between; font-size: 12px; color: #6f859f; }
		.foot b { color: #3b4f6b; display: inline-flex; align-items: center; gap: 8px; }
		.foot b::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #16a34a; }

		@media (max-width: 992px) { .stats { grid-template-columns: 1fr; } .mods { grid-template-columns: repeat(2, minmax(0, 1fr)); } .home-head { flex-direction: column; align-items: flex-start; } }
		@media (max-width: 640px) { .mods { grid-template-columns: 1fr; } .foot { flex-direction: column; gap: 6px; } .home-head h1 { font-size: 28px; } }
	</style>

	<div class="home">
		@if(session('mensaje'))
			<div class="app-alert">{{ session('mensaje') }}</div>
		@endif

		<div class="home-head">
			<div>
				<p class="eyebrow">Resumen general</p>
				<h1>{{ $saludo }}, equipo</h1>
				<p>Esto es lo que está pasando hoy en tu supermercado.</p>
			</div>
			<span class="live">Actualizado hace un momento</span>
		</div>

		<section class="stats" aria-label="Resumen de indicadores">
			<article class="stat">
				<div class="stat-top">
					<div class="chip-ic blue"><i class="fa-solid fa-cube"></i></div>
					<span class="tag gray">Catálogo</span>
				</div>
				<p class="stat-label">Total productos</p>
				<p class="stat-value">{{ number_format($totalProductos) }}</p>
				<div class="stat-foot">
					<span class="ok">
						<i class="fa-solid fa-arrow-trend-up"></i>
						{{ $productosNuevosMes > 0 ? '+'.$productosNuevosMes.' este mes' : 'Sin cambios este mes' }}
					</span>
				</div>
			</article>

			<article class="stat">
				<div class="stat-top">
					<div class="chip-ic green"><i class="fa-solid fa-dollar-sign"></i></div>
					<span class="tag green">Hoy</span>
				</div>
				<p class="stat-label">Ventas totales</p>
				<p class="stat-value">${{ number_format($ventasHoy, 0, '.', ',') }}</p>
				<div class="stat-foot">
					<span class="soft">
						{{ $ventasHoyCantidad }} {{ $ventasHoyCantidad === 1 ? 'venta registrada' : 'ventas registradas' }}
						@if ($variacionVentas !== null)
							· <span class="{{ $variacionVentas >= 0 ? 'ok' : 'bad' }}">{{ $variacionVentas >= 0 ? '+' : '' }}{{ $variacionVentas }}% vs ayer</span>
						@endif
					</span>
					<span class="bars" title="Ventas de los últimos 7 días">
						@foreach ($serieVentas as $v)
							<i style="height: {{ max(4, round(($v / $maxSerie) * 24)) }}px"></i>
						@endforeach
					</span>
				</div>
			</article>

			<article class="stat">
				<div class="stat-top">
					<div class="chip-ic red"><i class="fa-solid fa-triangle-exclamation"></i></div>
					<span class="tag red">Atención</span>
				</div>
				<p class="stat-label">Stock crítico</p>
				<p class="stat-value">{{ $stockCritico }}</p>
				<div class="stat-foot">
					@if ($stockCritico > 0)
						<a class="bad" href="{{ url('/inventario?estado=critico') }}">Revisar inventario <i class="fa-solid fa-arrow-right"></i></a>
					@else
						<span class="ok">Todo en orden</span>
					@endif
				</div>
			</article>
		</section>

		<div class="sec-head">
			<div>
				<p class="eyebrow">Accesos rápidos</p>
				<h2>Módulos principales</h2>
			</div>
			<a class="btn-ghost" href="{{ url('/proveedores') }}"><i class="fa-solid fa-user-group"></i> Gestionar proveedores</a>
		</div>

		<section class="mods" aria-label="Módulos principales">
			<a class="mod" href="{{ url('/productos') }}">
				<div class="mod-ic blue"><i class="fa-solid fa-cube"></i></div>
				<h3>Productos</h3>
				<p>Gestiona tu catálogo, precios y categorías.</p>
				<div class="mod-foot"><span>{{ $totalProductos }} {{ $totalProductos === 1 ? 'producto activo' : 'productos activos' }}</span><span class="go"><i class="fa-solid fa-chevron-right"></i></span></div>
			</a>

			<a class="mod" href="{{ url('/inventario') }}">
				<div class="mod-ic violet"><i class="fa-solid fa-warehouse"></i></div>
				<h3>Inventario</h3>
				<p>Controla existencias y movimientos de stock.</p>
				<div class="mod-foot"><span>{{ $stockCritico }} {{ $stockCritico === 1 ? 'alerta pendiente' : 'alertas pendientes' }}</span><span class="go"><i class="fa-solid fa-chevron-right"></i></span></div>
			</a>

			<a class="mod" href="{{ url('/ventas') }}">
				<div class="mod-ic green"><i class="fa-solid fa-arrow-trend-up"></i></div>
				<h3>Ventas</h3>
				<p>Registra ventas y consulta tu historial.</p>
				<div class="mod-foot"><span>${{ number_format($ventasHoy, 0, '.', ',') }} vendidos hoy</span><span class="go"><i class="fa-solid fa-chevron-right"></i></span></div>
			</a>

			<a class="mod" href="{{ url('/reportes/ventas') }}">
				<div class="mod-ic orange"><i class="fa-regular fa-file-lines"></i></div>
				<h3>Reportes</h3>
				<p>Analiza el rendimiento de tu negocio.</p>
				<div class="mod-foot"><span>Datos en tiempo real</span><span class="go"><i class="fa-solid fa-chevron-right"></i></span></div>
			</a>
		</section>

		<footer class="foot">
			<b>Sistema operativo</b>
			<span>BIG-DACO · Gestión integrada de inventario</span>
		</footer>
	</div>
@endsection
