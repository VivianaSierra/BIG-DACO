@extends('layout.app')

@section('titulo', 'BIG-DACO | Registrar Proveedor')

@section('contenido')

<a class="back-link" href="{{ route('proveedores.index') }}">
	<i class="fa-solid fa-arrow-left"></i>
	Volver
</a>

<h2 class="page-title">Registrar Proveedor</h2>
<p class="page-subtitle">Ingresa los datos del proveedor para incorporarlo al sistema.</p>

<section class="app-card" style="padding: 18px;">
	<form class="form-grid" method="POST" action="{{ route('proveedores.store') }}">
		@csrf
		@include('proveedores.form')
	</form>
</section>
@endsection
