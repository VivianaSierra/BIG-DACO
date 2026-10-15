@if ($errors->any())
	<div class="app-alert" style="margin-bottom: 0; border-color: #ffd0d6; background: #fff1f3; color: #b42334;">
		Revisa los datos ingresados.
	</div>
@endif

<div>
	<label class="form-label">Nombre del proveedor</label>
	<input type="text" name="nombre" class="form-control" placeholder="Nombre o razón social" value="{{ old('nombre', $proveedor->nombre ?? '') }}" required>
	@error('nombre') <small style="color:#b42334">{{ $message }}</small> @enderror
</div>

<div>
	<label class="form-label">Teléfono</label>
	<input type="text" name="telefono" class="form-control" placeholder="Número de contacto" value="{{ old('telefono', $proveedor->telefono ?? '') }}" required>
	@error('telefono') <small style="color:#b42334">{{ $message }}</small> @enderror
</div>

<div>
	<label class="form-label">Email</label>
	<input type="email" name="email" class="form-control" placeholder="correo@proveedor.com" value="{{ old('email', $proveedor->email ?? '') }}" required>
	@error('email') <small style="color:#b42334">{{ $message }}</small> @enderror
</div>

<div class="form-actions">
	<button class="btn-app btn-primary" type="submit">
		<i class="fa-solid fa-floppy-disk"></i>
		Guardar
	</button>
	<a href="{{ route('proveedores.index') }}" class="btn-app btn-muted">
		<i class="fa-solid fa-xmark"></i>
		Cancelar
	</a>
</div>
