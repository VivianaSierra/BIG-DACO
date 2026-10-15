@extends('layout.app')

@section('titulo', 'BIG-DACO | Editar Proveedor')

@section('contenido')

<a class="back-link" href="{{ route('proveedores.index') }}">
	<i class="fa-solid fa-arrow-left"></i>
	Volver
</a>

<h2 class="page-title">Editar Proveedor</h2>
<p class="page-subtitle">Actualiza los datos del proveedor.</p>

<section class="app-card" style="padding: 18px;">
	<form class="form-grid" method="POST" action="{{ route('proveedores.update', $proveedor) }}">
		@csrf
		@method('PUT')
		@include('proveedores.form')
	</form>
</section>
@endsection
