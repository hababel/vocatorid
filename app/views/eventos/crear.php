<?php
// El header_panel.php se carga automáticamente desde el controlador.
?>

<!-- Dependencias de Leaflet.js (Mapa interactivo) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- MEJORA: Dependencias de Flatpickr (Calendario amigable) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>


<style>
	/* Estilo para el contenedor del mapa */
	#map {
		height: 400px;
		width: 100%;
		border-radius: 0.5rem;
		border: 1px solid #dee2e6;
		background-color: #e9ecef;
		/* Color de fondo mientras carga */
	}
</style>

<div class="container-fluid px-md-4 py-4">
	<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
		<div>
			<h1 class="h2">Crear Nuevo Evento</h1>
			<p class="text-muted mb-0">Completa los detalles para tu próximo evento.</p>
		</div>
		<a href="<?php echo URL_PATH; ?>organizador/panel" class="btn btn-outline-secondary">
			<i class="bi bi-arrow-left me-1"></i> Volver al Panel
		</a>
	</div>

	<?php
	// --- BLOQUE PARA MOSTRAR MENSAJES DE NOTIFICACIÓN ---
	if (isset($_SESSION['mensaje'])) {
		$tipo_alerta = $_SESSION['mensaje']['tipo'] === 'exito' ? 'alert-success' : 'alert-danger';
		echo '<div class="alert ' . $tipo_alerta . ' text-center" role="alert">' . htmlspecialchars($_SESSION['mensaje']['texto']) . '</div>';
		unset($_SESSION['mensaje']);
	}
	?>

	<div class="card shadow-sm">
		<div class="card-body p-4">
			<form action="<?php echo URL_PATH; ?>evento/guardar" method="POST">
				<div class="row g-4">
					<!-- Columna Izquierda: Detalles del Evento -->
					<div class="col-md-6">
						<h5>Detalles Principales</h5>
						<hr class="mt-2">
						<div class="mb-3">
							<label for="nombre_evento" class="form-label">Nombre del Evento <span class="text-danger">*</span></label>
							<input type="text" class="form-control" id="nombre_evento" name="nombre_evento" required>
						</div>
						<div class="mb-3">
							<label for="objetivo" class="form-label">Objetivo / Descripción</label>
							<textarea class="form-control" id="objetivo" name="objetivo" rows="3"></textarea>
						</div>

						<!-- MEJORA: Campo para el nombre del facilitador -->
						<div class="mb-3">
							<label for="nombre_instructor" class="form-label">Nombre del Facilitador / Instructor</label>
							<input type="text" class="form-control" id="nombre_instructor" name="nombre_instructor">
						</div>

						<div class="row">
							<div class="col-sm-6 mb-3">
								<label for="fecha_evento" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
								<!-- Se mantiene el input, pero será mejorado por Flatpickr -->
								<input type="text" class="form-control" id="fecha_evento" name="fecha_evento" required placeholder="Selecciona una fecha...">
							</div>
							<div class="col-sm-6 mb-3">
								<label for="duracion_horas" class="form-label">Duración (horas) <span class="text-danger">*</span></label>
								<input type="number" class="form-control" id="duracion_horas" name="duracion_horas" step="0.5" min="0.5" value="1" required>
							</div>
						</div>
						<div class="mb-3">
							<label for="modo" class="form-label">Modo del Evento <span class="text-danger">*</span></label>
							<select class="form-select" id="modo" name="modo" required>
								<option value="Presencial">Presencial</option>
								<option value="Virtual">Virtual</option>
								<option value="Hibrido">Híbrido</option>
							</select>
						</div>
					</div>

					<!-- Columna Derecha: Ubicación del Evento -->
					<div class="col-md-6">
						<h5>Ubicación (Para eventos presenciales o híbridos)</h5>
						<hr class="mt-2">
						<div class="mb-3">
							<label for="lugar_nombre" class="form-label">Nombre del Lugar (Ej: Auditorio Principal)</label>
							<input type="text" class="form-control" id="lugar_nombre" name="lugar_nombre">
						</div>
						<div class="mb-3">
							<label for="lugar_direccion" class="form-label">Dirección</label>
							<input type="text" class="form-control" id="lugar_direccion" name="lugar_direccion">
						</div>

						<div id="map"></div>

						<div class="alert alert-info mt-2 p-2 d-flex align-items-center">
							<i class="bi bi-info-circle-fill me-2"></i>
							<div>
								Haz clic en el mapa para colocar el marcador azul. Ese punto será la ubicación oficial del evento.
							</div>
						</div>

						<input type="hidden" id="latitud" name="latitud">
						<input type="hidden" id="longitud" name="longitud">
					</div>
				</div>

				<div class="text-end mt-4">
					<button type="submit" class="btn btn-primary btn-lg">Guardar Evento</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	// --- LÓGICA DEL MAPA INTERACTIVO CON LEAFLET.JS ---
	const latitudInput = document.getElementById('latitud');
	const longitudInput = document.getElementById('longitud');
	let marker;
	let map;

	function inicializarMapa(lat, lng, zoom) {
		if (map) return;
		map = L.map('map').setView([lat, lng], zoom);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);
		marker = L.marker([lat, lng]).addTo(map)
			.bindTooltip('<b>Ubicación del Evento</b>', {
				permanent: true,
				direction: 'top'
			})
			.openTooltip();
		latitudInput.value = lat.toFixed(8);
		longitudInput.value = lng.toFixed(8);
		map.on('click', function(e) {
			const clickedLat = e.latlng.lat;
			const clickedLng = e.latlng.lng;
			latitudInput.value = clickedLat.toFixed(8);
			longitudInput.value = clickedLng.toFixed(8);
			marker.setLatLng([clickedLat, clickedLng]);
			map.panTo([clickedLat, clickedLng]);
		});
	}

	if (navigator.geolocation) {
		document.getElementById('map').innerHTML = '<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border" role="status"><span class="visually-hidden">Obteniendo ubicación...</span></div></div>';
		navigator.geolocation.getCurrentPosition(
			function(position) {
				inicializarMapa(position.coords.latitude, position.coords.longitude, 13);
			},
			function() {
				inicializarMapa(4.5709, -74.2973, 5);
			}
		);
	} else {
		inicializarMapa(4.5709, -74.2973, 5);
	}

	// --- MEJORA: INICIALIZAR FLATPICKR ---
	flatpickr("#fecha_evento", {
		enableTime: true,
		dateFormat: "Y-m-d H:i",
		time_24hr: true,
		locale: "es", // Usar el idioma español
		minDate: "today" // No permitir seleccionar fechas pasadas
	});
</script>

<?php
// El footer_panel.php se carga automáticamente desde el controlador.
?>