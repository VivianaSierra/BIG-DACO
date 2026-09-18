@extends('layout.app')

@section('titulo', 'BIG-DACO | Proveedores')

@section('contenido')

<a class="back-link" href="{{ url('/dashboard') }}">
	<i class="fa-solid fa-arrow-left"></i>
	Volver
</a>

<h2 class="page-title">Gestión de Proveedores</h2>
<p class="page-subtitle">Administra la información de tus proveedores de productos.</p>

<section class="toolbar" aria-label="Acciones de proveedores">
	<div class="search">
		<i class="fa-solid fa-magnifying-glass"></i>
		<input type="text" placeholder="Buscar proveedores..." aria-label="Buscar proveedores">
	</div>

	<a href="{{ url('/proveedores/create') }}" class="btn-app btn-primary">
		<i class="fa-solid fa-plus"></i>
		Registrar Proveedor
	</a>
</section>

<section class="app-card">
	<div class="table-wrap">
		<table>
			<thead>
				<tr>
					<th>ID</th>
					<th>Nombre</th>
					<th>Teléfono</th>
					<th>Email</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>1</td>
					<td>Distribuidora Central</td>
					<td>3104567890</td>
					<td>proveedor@email.com</td>
					<td>
						<a href="#" class="btn-app btn-warning"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
						<a href="#" class="btn-app btn-danger"><i class="fa-regular fa-trash-can"></i> Eliminar</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="card-footer">Mostrando 1 de 1 proveedores</div>
</section>

@endsection