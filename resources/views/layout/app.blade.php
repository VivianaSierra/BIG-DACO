<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>@yield('titulo', 'BIG-DACO')</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<style>
		:root {
			--bg: #f3f5f7;
			--surface: #ffffff;
			--border: #d8dee8;
			--text: #0f274a;
			--muted: #6f859f;
			--green: #0ea84d;
			--blue: #2563eb;
			--yellow: #f4b400;
			--red: #ff2f3f;
			--shadow: 0 6px 16px rgba(15, 39, 74, 0.07);
		}

		* {
			box-sizing: border-box;
		}

		body {
			margin: 0;
			font-family: 'Plus Jakarta Sans', sans-serif;
			background: var(--bg);
			color: var(--text);
		}

		a {
			text-decoration: none;
		}

		.topbar {
			background: #fff;
			border-bottom: 1px solid var(--border);
		}

		.topbar-inner {
			max-width: 1180px;
			margin: 0 auto;
			padding: 10px 20px;
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 16px;
			flex-wrap: wrap;
		}

		.brand h1 {
			margin: 0;
			font-size: 16px;
			font-weight: 800;
			color: var(--text);
		}

		.brand p {
			margin: 6px 0 0;
			font-size: 12px;
			color: var(--muted);
			font-weight: 500;
		}

		.top-actions {
			display: flex;
			gap: 12px;
			flex-wrap: wrap;
		}

		.top-btn {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 8px 14px;
			border-radius: 10px;
			border: 1px solid var(--border);
			background: #f1f5f9;
			color: #29435f;
			font-size: 13px;
			font-weight: 700;
			transition: transform 0.15s ease, box-shadow 0.15s ease;
		}

		.top-btn:hover {
			transform: translateY(-1px);
			box-shadow: 0 8px 18px rgba(49, 80, 112, 0.12);
		}

		.main-nav {
			max-width: 1180px;
			margin: 0 auto;
			padding: 0 20px 10px;
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
		}

		.main-nav a {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 7px 10px;
			border-radius: 10px;
			font-size: 13px;
			font-weight: 700;
			color: #3b5674;
			background: #edf2f7;
			border: 1px solid #e0e8f1;
		}

		.page {
			max-width: 1180px;
			margin: 0 auto;
			padding: 18px 20px;
		}

		.page-title {
			margin: 0;
			font-size: 36px;
			font-weight: 800;
			letter-spacing: -0.3px;
			color: var(--text);
		}

		.page-subtitle {
			margin: 8px 0 18px;
			color: var(--muted);
			font-size: 18px;
			font-weight: 500;
		}

		.back-link {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			color: #415e7c;
			font-size: 15px;
			font-weight: 700;
			margin-bottom: 12px;
		}

		.app-card {
			background: var(--surface);
			border: 1px solid var(--border);
			border-radius: 14px;
			box-shadow: var(--shadow);
			overflow: hidden;
		}

		.toolbar {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 16px;
			margin-bottom: 16px;
			flex-wrap: wrap;
		}

		.btn-app {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			border: 0;
			border-radius: 10px;
			padding: 10px 16px;
			font-size: 14px;
			font-weight: 800;
			color: #fff;
			cursor: pointer;
		}

		.btn-primary {
			background: var(--green);
		}

		.btn-warning {
			background: var(--yellow);
		}

		.btn-danger {
			background: var(--red);
		}

		.btn-muted {
			background: #64748b;
		}

		.search {
			flex: 1 1 320px;
			max-width: 460px;
			position: relative;
		}

		.search i {
			position: absolute;
			left: 12px;
			top: 50%;
			transform: translateY(-50%);
			color: #8da1b8;
			font-size: 16px;
		}

		.search input,
		.form-control {
			width: 100%;
			border: 1px solid var(--border);
			border-radius: 10px;
			background: #fff;
			height: 44px;
			padding: 0 12px;
			font-family: inherit;
			font-size: 15px;
			color: #2a4764;
			outline: none;
		}

		.search input {
			padding-left: 36px;
		}

		.form-grid {
			display: grid;
			gap: 14px;
		}

		.form-label {
			display: block;
			margin-bottom: 6px;
			font-size: 14px;
			font-weight: 700;
			color: #2f4b68;
		}

		.form-actions {
			display: flex;
			gap: 10px;
			margin-top: 4px;
			flex-wrap: wrap;
		}

		.table-wrap {
			width: 100%;
			overflow-x: auto;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			min-width: 720px;
		}

		th,
		td {
			text-align: left;
			padding: 14px 16px;
			border-bottom: 1px solid #e7edf4;
			font-size: 15px;
			color: #2f4b68;
		}

		th {
			color: #243f5e;
			font-weight: 800;
			background: #fff;
		}

		.stock-pill {
			display: inline-block;
			min-width: 36px;
			text-align: center;
			border-radius: 999px;
			padding: 6px 10px;
			font-size: 13px;
			font-weight: 700;
		}

		.stock-ok {
			background: #dff8e7;
			color: #0e8f42;
		}

		.stock-mid {
			background: #fff5d6;
			color: #b98000;
		}

		.stock-low {
			background: #ffe5e8;
			color: #d92639;
		}

		.text-success {
			color: #0e8f42;
			font-weight: 700;
		}

		.text-danger {
			color: #d92639;
			font-weight: 700;
		}

		.card-footer {
			padding: 12px 16px;
			font-size: 14px;
			color: #5a7491;
			background: #fbfcfe;
		}

		.app-alert {
			margin-bottom: 16px;
			padding: 12px 14px;
			border: 1px solid #b4f1c9;
			background: #e8fff0;
			color: #157347;
			border-radius: 10px;
			font-size: 14px;
			font-weight: 700;
		}

		@media (max-width: 768px) {
			.topbar-inner,
			.main-nav,
			.page {
				padding-left: 16px;
				padding-right: 16px;
			}

			.page-title {
				font-size: 28px;
			}

			.page-subtitle {
				font-size: 16px;
			}

			.top-actions {
				width: 100%;
			}

			.top-btn {
				flex: 1;
				justify-content: center;
			}
		}
	</style>
</head>
<body>
	@hasSection('encabezado')
		@yield('encabezado')
	@else
	<header class="topbar">
		<div class="topbar-inner">
			<div class="brand">
				<h1>BIG-DACO</h1>
				<p>Plataforma de gestión de inventario y análisis de datos para supermercados</p>
			</div>

			<div class="top-actions">
				<a class="top-btn" href="{{ url('/proveedores') }}">
					<i class="fa-solid fa-users"></i>
					Proveedores
				</a>
				<a class="top-btn" href="{{ url('/ventas/create') }}">
					<i class="fa-solid fa-cart-shopping"></i>
					Nueva venta
				</a>
			</div>
		</div>

		<nav class="main-nav" aria-label="Navegación principal">
			<a href="{{ url('/dashboard') }}"><i class="fa-solid fa-house"></i> Inicio</a>
			<a href="{{ url('/productos') }}"><i class="fa-solid fa-box"></i> Productos</a>
			<a href="{{ url('/proveedores') }}"><i class="fa-solid fa-truck"></i> Proveedores</a>
			<a href="{{ url('/ventas') }}"><i class="fa-solid fa-cart-shopping"></i> Ventas</a>
			<a href="{{ url('/inventario') }}"><i class="fa-solid fa-warehouse"></i> Inventario</a>
			<a href="{{ url('/reportes/ventas') }}"><i class="fa-solid fa-chart-column"></i> Reportes</a>
		</nav>
	</header>
	@endif

	@hasSection('sin_contenedor')
		@yield('contenido')
	@else
	<main class="page">
		@if(session('mensaje'))
			<div class="app-alert">{{ session('mensaje') }}</div>
		@endif

		@yield('contenido')
	</main>
	@endif

	@auth
		<x-chatbot />
	@endauth
</body>
</html>