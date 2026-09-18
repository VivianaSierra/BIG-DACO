@extends('layout.app')

@section('titulo', 'BIG-DACO | Editar Producto')

@section('contenido')

<a class="back-link" href="{{ route('productos.index') }}">
	<i class="fa-solid fa-arrow-left"></i>
	Volver
</a>

<h2 class="page-title">Editar Producto</h2>
<p class="page-subtitle">Actualiza la información del producto seleccionado.</p>

<section class="app-card" style="padding: 18px;">
	<form class="form-grid" method="POST" action="{{ route('productos.update', $producto) }}">
		@csrf
		@method('PUT')

		@if ($errors->any())
			<div class="app-alert" style="margin-bottom: 0; border-color: #ffd0d6; background: #fff1f3; color: #b42334;">
				Corrige los campos marcados antes de continuar.
			</div>
		@endif

		<div>
			<label class="form-label">Nombre</label>
			<input type="text" class="form-control" name="nombre" value="{{ old('nombre', $producto->nombre) }}" placeholder="Nombre del producto" required>
			@error('nombre')
				<small style="display:block; margin-top:6px; color:#b42334; font-weight:600;">{{ $message }}</small>
			@enderror
		</div>

		<div>
			<label class="form-label">Categoría</label>
			<select class="form-control" name="categoria" required>
				<option value="" disabled>Selecciona una categoría</option>
				@foreach ($categorias as $categoria)
					<option value="{{ $categoria }}" {{ old('categoria', $producto->categoria) === $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
				@endforeach
			</select>
			@error('categoria')
				<small style="display:block; margin-top:6px; color:#b42334; font-weight:600;">{{ $message }}</small>
			@enderror
		</div>

		<div>
			<label class="form-label">Precio</label>
			<input type="number" class="form-control" name="precio" value="{{ old('precio', $producto->precio) }}" placeholder="0" min="0" step="0.01" required>
			@error('precio')
				<small style="display:block; margin-top:6px; color:#b42334; font-weight:600;">{{ $message }}</small>
			@enderror
		</div>

		<div>
			<label class="form-label">Stock</label>
			<input type="number" class="form-control" name="stock" value="{{ old('stock', $producto->stock) }}" placeholder="0" min="0" required>
			@error('stock')
				<small style="display:block; margin-top:6px; color:#b42334; font-weight:600;">{{ $message }}</small>
			@enderror
		</div>

		<div class="form-actions">
			<button class="btn-app btn-primary" type="submit">
				<i class="fa-solid fa-floppy-disk"></i>
				Actualizar
			</button>
			<a href="{{ route('productos.index') }}" class="btn-app btn-muted">
				<i class="fa-solid fa-xmark"></i>
				Cancelar
			</a>
		</div>
	</form>
</section>
@endsection
