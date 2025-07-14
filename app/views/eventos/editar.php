<?php
// El header_panel.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
?>

<!-- Dependencias de Leaflet.js y Flatpickr -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

<style>
	#map {
		height: 400px;
		width: 100%;
		border-radius: 0.5rem;
		border: 1px solid #dee2e6;
	}
</style>

<div class="container-fluid px-md-4 py-4">
	<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
		<div>
			<h1 class="h2">Editar Evento</h1>
			<p class="text-muted mb-0">Modificando el evento: <strong><?php echo htmlspecialchars($evento->nombre_evento); ?></strong></p>
		</div>
		<a href="<?php echo URL_PATH; ?>organizador/panel" class="btn btn-outline-secondary">
			<i class="bi bi-arrow-left me-1"></i> Volver al Panel
		</a>
	</div>

	<?php
	if (isset($_SESSION['mensaje'])) {
		$tipo_alerta = $_SESSION['mensaje']['tipo'] === 'exito' ? 'alert-success' : 'alert-danger';
		echo '<div class="alert ' . $tipo_alerta . ' text-center" role="alert">' . htmlspecialchars($_SESSION['mensaje']['texto']) . '</div>';
		unset($_SESSION['mensaje']);
	}
	?>

	<div class="card shadow-sm">
		<div class="card-body p-4">
			<form action="<?php echo URL_PATH; ?>evento/actualizar" method="POST">
				<!-- Campo oculto para enviar el ID del evento que se está editando -->
				<input type="hidden" name="id_evento" value="<?php echo $evento->id; ?>">

				<div class="row g-4">
					<!-- Columna Izquierda: Detalles del Evento -->
					<div class="col-md-6">
						<h5>Detalles Principales</h5>
						<hr class="mt-2">
						<div class="mb-3">
							<label for="nombre_evento" class="form-label">Nombre del Evento <span class="text-danger">*</span></label>
							<input type="text" class="form-control" id="nombre_evento" name="nombre_evento" value="<?php echo htmlspecialchars($evento->nombre_evento); ?>" required>
						</div>
						<div class="mb-3">
							<label for="objetivo" class="form-label">Objetivo / Descripción</label>
							<textarea class="form-control" id="objetivo" name="objetivo" rows="3"><?php echo htmlspecialchars($evento->objetivo); ?></textarea>
						</div>
						<div class="mb-3">
							<label for="nombre_instructor" class="form-label">Nombre del Facilitador / Instructor</label>
							<input type="text" class="form-control" id="nombre_instructor" name="nombre_instructor" value="<?php echo htmlspecialchars($evento->nombre_instructor ?? ''); ?>">
						</div>
						<div class="row">
							<div class="col-sm-6 mb-3">
								<label for="fecha_evento" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
								<input type="text" class="form-control" id="fecha_evento" name="fecha_evento" value="<?php echo htmlspecialchars($evento->fecha_evento); ?>" required>
							</div>
							<div class="col-sm-6 mb-3">
								<label for="duracion_horas" class="form-label">Duración (horas) <span class="text-danger">*</span></label>
								<input type="number" class="form-control" id="duracion_horas" name="duracion_horas" step="0.5" min="0.5" value="<?php echo htmlspecialchars($evento->duracion_horas); ?>" required>
							</div>
						</div>
						<div class="mb-3">
							<label for="modo" class="form-label">Modo del Evento <span class="text-danger">*</span></label>
							<select class="form-select" id="modo" name="modo" required>
								<option value="Presencial" <?php echo ($evento->modo == 'Presencial') ? 'selected' : ''; ?>>Presencial</option>
								<option value="Virtual" <?php echo ($evento->modo == 'Virtual') ? 'selected' : ''; ?>>Virtual</option>
								<option value="Hibrido" <?php echo ($evento->modo == 'Hibrido') ? 'selected' : ''; ?>>Híbrido</option>
							</select>
						</div>
					</div>

					<!-- Columna Derecha: Ubicación del Evento -->
					<div class="col-md-6">
						<h5>Ubicación</h5>
						<hr class="mt-2">
						<div class="mb-3">
							<label for="lugar_nombre" class="form-label">Nombre del Lugar</label>
							<input type="text" class="form-control" id="lugar_nombre" name="lugar_nombre" value="<?php echo htmlspecialchars($evento->lugar_nombre); ?>">
						</div>
						<div class="mb-3">
							<label for="lugar_direccion" class="form-label">Dirección</label>
							<input type="text" class="form-control" id="lugar_direccion" name="lugar_direccion" value="<?php echo htmlspecialchars($evento->lugar_direccion); ?>">
						</div>
						<div id="map"></div>
						<input type="hidden" id="latitud" name="latitud" value="<?php echo htmlspecialchars($evento->latitud); ?>">
						<input type="hidden" id="longitud" name="longitud" value="<?php echo htmlspecialchars($evento->longitud); ?>">
					</div>
				</div>

				<div class="text-end mt-4">
					<button type="submit" class="btn btn-primary btn-lg">Actualizar Evento</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const latitudInput = document.getElementById('latitud');
		const longitudInput = document.getElementById('longitud');
		let marker;
		let map;

		const initialLat = parseFloat(latitudInput.value) || 4.5709;
		const initialLng = parseFloat(longitudInput.value) || -74.2973;
		const initialZoom = (latitudInput.value) ? 15 : 5;

		map = L.map('map').setView([initialLat, initialLng], initialZoom);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

		marker = L.marker([initialLat, initialLng]).addTo(map);

		map.on('click', function(e) {
			const clickedLat = e.latlng.lat;
			const clickedLng = e.latlng.lng;
			latitudInput.value = clickedLat.toFixed(8);
			longitudInput.value = clickedLng.toFixed(8);
			marker.setLatLng([clickedLat, clickedLng]);
			map.panTo([clickedLat, clickedLng]);
		});

		flatpickr("#fecha_evento", {
			enableTime: true,
			dateFormat: "Y-m-d H:i",
			time_24hr: true,
			locale: "es"
		});
	});
</script>

<?php
// El footer_panel.php se carga automáticamente desde el controlador.
?>