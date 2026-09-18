@extends('layout.app')

@section('titulo', 'BIG-DACO | Registrar Proveedor')

@section('contenido')

<a class="back-link" href="{{ url('/proveedores') }}">
	<i class="fa-solid fa-arrow-left"></i>
	Volver
</a>

<h2 class="page-title">Registrar Proveedor</h2>
<p class="page-subtitle">Ingresa los datos del proveedor para incorporarlo al sistema.</p>

<section class="app-card" style="padding: 18px;">
	<form class="form-grid">
		<div>
			<label class="form-label">Nombre del proveedor</label>
			<input type="text" class="form-control" placeholder="Nombre o razón social">
		</div>

		<div>
			<label class="form-label">Teléfono</label>
			<input type="text" class="form-control" placeholder="Número de contacto">
		</div>

		<div>
			<label class="form-label">Email</label>
			<input type="email" class="form-control" placeholder="correo@proveedor.com">
		</div>

		<div class="form-actions">
			<button class="btn-app btn-primary" type="submit">
				<i class="fa-solid fa-floppy-disk"></i>
				Guardar
			</button>
			<a href="{{ url('/proveedores') }}" class="btn-app btn-muted">
				<i class="fa-solid fa-xmark"></i>
				Cancelar
			</a>
		</div>
	</form>
</section>
@endsection