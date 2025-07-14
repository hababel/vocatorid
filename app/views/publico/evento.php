<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
$is_publicado = ($evento->estado == 'Publicado');
?>

<!-- Dependencias de Leaflet.js para el mapa -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
	.event-banner {
		background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://placehold.co/1200x400/0d6efd/white?text=Imagen+del+Evento') no-repeat center center;
		background-size: cover;
		padding: 5rem 1rem;
		color: white;
		text-align: center;
		border-bottom: 5px solid #0d6efd;
	}

	.event-title {
		font-weight: 700;
	}

	.event-details-card {
		border: none;
		border-radius: 0.75rem;
	}

	#map {
		height: 400px;
		width: 100%;
		border-radius: 0.5rem;
		z-index: 0;
	}

	.icon-list {
		list-style: none;
		padding-left: 0;
	}

	.icon-list li {
		display: flex;
		align-items: center;
		margin-bottom: 1rem;
		font-size: 1.1rem;
	}

	.icon-list .icon {
		font-size: 1.5rem;
		margin-right: 1rem;
		color: #0d6efd;
		width: 30px;
		text-align: center;
	}
</style>

<div class="event-banner">
	<div class="container">
		<h1 class="display-4 event-title"><?php echo htmlspecialchars($evento->nombre_evento); ?></h1>
		<p class="lead"><?php echo htmlspecialchars($evento->objetivo); ?></p>
	</div>
</div>

<div class="container my-5">
	<div class="row g-5">
		<!-- Columna de Información -->
		<div class="col-lg-8">
			<div class="card shadow-sm event-details-card">
				<div class="card-body p-4">
					<h3 class="card-title mb-4">Detalles del Evento</h3>
					<ul class="icon-list">
						<li>
							<i class="bi bi-person-video3 icon"></i>
							<div><strong>Facilitador:</strong> <?php echo htmlspecialchars($evento->nombre_instructor ?? 'Por confirmar'); ?></div>
						</li>
						<li>
							<i class="bi bi-calendar-event icon"></i>
							<div><strong>Fecha y Hora:</strong> <?php echo date('l, d \d\e F \d\e Y - h:i A', strtotime($evento->fecha_evento)); ?></div>
						</li>
						<li>
							<i class="bi bi-clock icon"></i>
							<div><strong>Duración:</strong> <?php echo htmlspecialchars($evento->duracion_horas); ?> horas</div>
						</li>
						<li>
							<i class="bi bi-display icon"></i>
							<div><strong>Modalidad:</strong> <?php echo htmlspecialchars($evento->modo); ?></div>
						</li>
					</ul>

					<?php if ($evento->modo != 'Virtual'): ?>
						<hr class="my-4">
						<h4 class="h5">Ubicación</h4>
						<ul class="icon-list">
							<li>
								<i class="bi bi-building icon"></i>
								<div><strong>Lugar:</strong> <?php echo htmlspecialchars($evento->lugar_nombre ?? 'No especificado'); ?></div>
							</li>
							<li>
								<i class="bi bi-geo-alt icon"></i>
								<div><strong>Dirección:</strong> <?php echo htmlspecialchars($evento->lugar_direccion ?? 'No especificada'); ?></div>
							</li>
						</ul>
						<div id="map" class="mt-3"></div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Columna de Registro -->
		<div class="col-lg-4">
			<div class="card shadow-sm event-details-card position-sticky" style="top: 2rem;">
				<div class="card-body p-4 text-center">
					<h3 class="card-title">¡No te lo pierdas!</h3>
					<p>Regístrate ahora para asegurar tu lugar en este evento y recibir tu credencial de acceso.</p>
					<div class="d-grid">
						<!-- CORRECCIÓN: Lógica para deshabilitar el botón y mostrar tooltip -->
						<?php if ($is_publicado): ?>
							<a href="<?php echo URL_PATH; ?>asistencia/registroAnonimo/<?php echo $evento->id; ?>" class="btn btn-primary btn-lg">
								<i class="bi bi-check-circle-fill me-2"></i>Registrarme Ahora
							</a>
						<?php else: ?>
							<span class="d-grid" data-bs-toggle="tooltip" data-bs-placement="top" title="El registro se habilitará cuando el evento sea publicado por el organizador.">
								<button type="button" class="btn btn-primary btn-lg" disabled>
									<i class="bi bi-check-circle-fill me-2"></i>Registro Próximamente
								</button>
							</span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if ($evento->modo != 'Virtual' && !empty($evento->latitud)): ?>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const lat = <?php echo $evento->latitud; ?>;
			const lng = <?php echo $evento->longitud; ?>;

			const map = L.map('map').setView([lat, lng], 15);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
			}).addTo(map);
			L.marker([lat, lng]).addTo(map)
				.bindPopup('<b><?php echo htmlspecialchars($evento->nombre_evento); ?></b><br><?php echo htmlspecialchars($evento->lugar_nombre); ?>').openPopup();
		});
	</script>
<?php endif; ?>

<script>
	// Script para inicializar los tooltips de Bootstrap
	var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
	var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
		return new bootstrap.Tooltip(tooltipTriggerEl)
	})
</script>

<?php
// El footer.php se carga automáticamente.
?>