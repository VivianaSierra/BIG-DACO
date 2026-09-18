@extends('layout.app')

@section('titulo', 'BIG-DACO | Nueva Venta')

@section('contenido')
	<a class="back-link" href="{{ route('ventas.index') }}">
		<i class="fa-solid fa-arrow-left"></i>
		Volver
	</a>

	<h2 class="page-title">Nueva Venta</h2>
	<p class="page-subtitle">Registra una venta con varios productos y descuenta stock automáticamente.</p>

	<section class="app-card" style="padding: 18px;">
		<form class="form-grid" method="POST" action="{{ route('ventas.store') }}" id="form-venta">
			@csrf

			@if ($errors->any())
				<div class="app-alert" style="margin-bottom: 0; border-color: #ffd0d6; background: #fff1f3; color: #b42334;">
					Corrige los campos marcados antes de continuar.
				</div>
			@endif

			<div>
				<label class="form-label">Fecha de venta</label>
				<input type="date" class="form-control" name="fecha" value="{{ old('fecha', now()->toDateString()) }}">
			</div>

			<div id="lineas-venta" class="form-grid" style="gap:10px;">
				@php
					$lineasPrevias = old('producto_id', [null]);
				@endphp
				@foreach ($lineasPrevias as $indice => $productoSeleccionado)
					<div class="linea-venta" style="display:grid; grid-template-columns: 1fr 140px auto; gap:10px; align-items:end;">
						<div>
							<label class="form-label">Buscar producto</label>
							<input type="text" class="form-control producto-search" placeholder="Escribe para buscar..." style="margin-bottom:8px;">

							<label class="form-label">Producto</label>
							<select class="form-control producto-select" name="producto_id[]" required>
								<option value="" disabled {{ $productoSeleccionado ? '' : 'selected' }}>Selecciona un producto</option>
								@foreach ($productos as $producto)
									<option
										value="{{ $producto->id }}"
										data-precio="{{ $producto->precio }}"
										data-stock="{{ $producto->stock }}"
										{{ (string) $productoSeleccionado === (string) $producto->id ? 'selected' : '' }}
									>
										{{ $producto->nombre }} · Stock: {{ $producto->stock }} · ${{ number_format((float) $producto->precio, 0, ',', '.') }}
									</option>
								@endforeach
							</select>
						</div>
						<div>
							<label class="form-label">Cantidad</label>
							<input type="number" class="form-control cantidad-input" name="cantidad[]" value="{{ old('cantidad.' . $indice, 1) }}" min="1" required>
						</div>
						<button type="button" class="btn-app btn-danger btn-eliminar-linea" style="height:44px;">
							<i class="fa-solid fa-trash"></i>
							Quitar
						</button>
					</div>
				@endforeach
			</div>

			<div class="form-actions" style="margin-top: 2px;">
				<button class="btn-app btn-muted" type="button" id="agregar-linea">
					<i class="fa-solid fa-plus"></i>
					Agregar producto
				</button>
			</div>

			<div>
				<label class="form-label">Total estimado</label>
				<input type="text" class="form-control" id="total_estimado" value="$0" readonly>
				<small id="stock_alerta" style="display:block; margin-top:6px; color:#6f859f; font-weight:600;">Verifica que las cantidades no superen el stock.</small>
			</div>

			<div class="form-actions">
				<button class="btn-app btn-primary" type="submit">
					<i class="fa-solid fa-floppy-disk"></i>
					Guardar Venta
				</button>
				<a href="{{ route('ventas.index') }}" class="btn-app btn-muted">
					<i class="fa-solid fa-xmark"></i>
					Cancelar
				</a>
			</div>
		</form>
	</section>

	<script>
		(function () {
			const lineasContainer = document.getElementById('lineas-venta');
			const agregarLineaBtn = document.getElementById('agregar-linea');
			const totalInput = document.getElementById('total_estimado');
			const alertaStock = document.getElementById('stock_alerta');

			function obtenerConteoSeleccionados() {
				const conteo = {};

				lineasContainer.querySelectorAll('.producto-select').forEach((select) => {
					if (!select.value) {
						return;
					}

					conteo[select.value] = (conteo[select.value] || 0) + 1;
				});

				return conteo;
			}

			function actualizarOpcionesDeLinea(linea) {
				const buscador = linea.querySelector('.producto-search');
				const productoSelect = linea.querySelector('.producto-select');
				const termino = String(buscador.value || '').trim().toLowerCase();
				const seleccionActual = productoSelect.value;
				const conteoSeleccionados = obtenerConteoSeleccionados();
				let visibles = 0;
				let ultimoVisible = null;

				for (let i = 1; i < productoSelect.options.length; i++) {
					const option = productoSelect.options[i];
					const coincideBusqueda = termino === '' || option.textContent.toLowerCase().includes(termino);
					const tomadoEnOtraLinea = option.value !== seleccionActual && (conteoSeleccionados[option.value] || 0) > 0;
					const mostrar = coincideBusqueda && !tomadoEnOtraLinea;

					option.hidden = !mostrar;
					option.disabled = tomadoEnOtraLinea;

					if (mostrar) {
						visibles += 1;
						ultimoVisible = option;
					}
				}

				const seleccionada = productoSelect.options[productoSelect.selectedIndex];
				if (seleccionada && (seleccionada.hidden || seleccionada.disabled)) {
					productoSelect.selectedIndex = 0;
				}

				if (!productoSelect.value && termino !== '' && visibles === 1 && ultimoVisible) {
					productoSelect.value = ultimoVisible.value;
					buscador.value = ultimoVisible.textContent.split(' · ')[0].trim();
				}
			}

			function actualizarOpcionesTodasLasLineas() {
				lineasContainer.querySelectorAll('.linea-venta').forEach((linea) => {
					actualizarOpcionesDeLinea(linea);
				});
			}

			function formatearMoneda(valor) {
				return '$' + new Intl.NumberFormat('es-CO').format(valor);
			}

			function actualizarResumen() {
				let total = 0;
				let advertencia = '';
				const seleccionados = {};
				let hayDuplicados = false;

				lineasContainer.querySelectorAll('.linea-venta').forEach((linea) => {
					const productoSelect = linea.querySelector('.producto-select');
					const cantidadInput = linea.querySelector('.cantidad-input');
					const opcion = productoSelect.options[productoSelect.selectedIndex];
					const precio = Number(opcion?.dataset?.precio || 0);
					const stock = Number(opcion?.dataset?.stock || 0);
					const cantidad = Number(cantidadInput.value || 0);

					total += (precio * cantidad);

					if (productoSelect.value) {
						seleccionados[productoSelect.value] = (seleccionados[productoSelect.value] || 0) + 1;
						if (seleccionados[productoSelect.value] > 1) {
							hayDuplicados = true;
						}
					}

					if (productoSelect.value && cantidad > stock) {
						const nombre = opcion.textContent.split(' · ')[0] || 'Producto';
						advertencia = 'La cantidad de ' + nombre + ' supera su stock.';
					}
				});

				if (hayDuplicados) {
					advertencia = 'No repitas productos en la misma venta.';
				}

				totalInput.value = formatearMoneda(total);
				alertaStock.textContent = advertencia !== '' ? advertencia : 'Verifica que las cantidades no superen el stock.';
				alertaStock.style.color = advertencia !== '' ? '#b42334' : '#6f859f';
			}

			function crearLinea() {
				const primeraLinea = lineasContainer.querySelector('.linea-venta');
				const nuevaLinea = primeraLinea.cloneNode(true);
				nuevaLinea.querySelector('.producto-search').value = '';
				nuevaLinea.querySelector('.producto-select').selectedIndex = 0;
				nuevaLinea.querySelector('.cantidad-input').value = 1;

				nuevaLinea.querySelectorAll('.producto-select option').forEach((option, index) => {
					if (index > 0) {
						option.hidden = false;
					}
				});

				lineasContainer.appendChild(nuevaLinea);
				actualizarOpcionesTodasLasLineas();
				actualizarResumen();
			}

			lineasContainer.addEventListener('click', (event) => {
				if (!event.target.closest('.btn-eliminar-linea')) {
					return;
				}

				const lineas = lineasContainer.querySelectorAll('.linea-venta');
				if (lineas.length === 1) {
					return;
				}

				event.target.closest('.linea-venta').remove();
				actualizarOpcionesTodasLasLineas();
				actualizarResumen();
			});

			lineasContainer.addEventListener('change', (event) => {
				if (event.target.classList.contains('producto-select')) {
					const linea = event.target.closest('.linea-venta');
					const buscador = linea.querySelector('.producto-search');
					const opcion = event.target.options[event.target.selectedIndex];

					if (opcion && opcion.value) {
						buscador.value = opcion.textContent.split(' · ')[0].trim();
					}

					actualizarOpcionesTodasLasLineas();
					actualizarResumen();
				}
			});

			lineasContainer.addEventListener('input', (event) => {
				if (event.target.classList.contains('cantidad-input')) {
					actualizarResumen();
				}

				if (event.target.classList.contains('producto-search')) {
					const linea = event.target.closest('.linea-venta');
					actualizarOpcionesDeLinea(linea);
					actualizarOpcionesTodasLasLineas();
					actualizarResumen();
				}
			});

			agregarLineaBtn.addEventListener('click', crearLinea);

			lineasContainer.querySelectorAll('.linea-venta').forEach((linea) => {
				const select = linea.querySelector('.producto-select');
				const buscador = linea.querySelector('.producto-search');
				const opcion = select.options[select.selectedIndex];

				if (opcion && opcion.value) {
					buscador.value = opcion.textContent.split(' · ')[0].trim();
				}
			});

			actualizarOpcionesTodasLasLineas();
			actualizarResumen();
		})();
	</script>
@endsection
