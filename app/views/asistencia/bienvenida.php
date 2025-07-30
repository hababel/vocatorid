<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
?>

<style>
	.event-card {
		border-left: 5px solid #0d6efd;
	}

	.confirmation-panel {
		background-color: #e6f9f0;
		border-left: 5px solid #198754;
	}

	.details-section {
		background-color: #f8f9fa;
		border: 1px solid #dee2e6;
		border-radius: .5rem;
		margin-top: 1rem;
	}

	/* ====================================================== */
	/* AJUSTE FINAL DE TAMAÑOS SOLICITADO          */
	/* ====================================================== */
	.clave-visual-summary img {
		width: 63px;
		/* Aumento del 50% */
		height: 63px;
		border-radius: .3rem;
		border: 1px solid #ccc;
	}

	.clave-visual-summary .color-box {
		width: 39px;
		/* Aumento del 30% */
		height: 39px;
		border-radius: .3rem;
		border: 1px solid #ccc;
	}

	/* ====================================================== */
</style>

<div class="container container-main d-flex align-items-center">
	<div class="w-100" style="max-width: 600px; margin: auto;">

		<?php
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		if (isset($_SESSION['mensaje'])) {
			$tipo_alerta = 'alert-info';
			$icono = 'bi-info-circle-fill';
			if ($_SESSION['mensaje']['tipo'] === 'exito') {
				$tipo_alerta = 'alert-success';
				$icono = 'bi-check-circle-fill';
			}
			if ($_SESSION['mensaje']['tipo'] === 'error') {
				$tipo_alerta = 'alert-danger';
				$icono = 'bi-exclamation-triangle-fill';
			}
			echo '<div class="alert ' . $tipo_alerta . ' text-center d-flex align-items-center" role="alert">';
			echo '<i class="bi ' . $icono . ' me-2"></i>';
			echo '<div>' . htmlspecialchars($_SESSION['mensaje']['texto']) . '</div>';
			echo '</div>';
			unset($_SESSION['mensaje']);
		}
		?>

		<?php if ($invitacion->asistencia_verificada): ?>

			<div class="card shadow-sm confirmation-panel">
				<div class="card-body p-4 text-center">
					<i class="bi bi-patch-check-fill h1 text-success"></i>
					<h2 class="card-title mt-2">Tu asistencia ya está registrada</h2>
					<p class="text-muted">¡Gracias! El organizador ha recibido tu confirmación. No necesitas realizar ninguna otra acción.</p>

					<button class="btn btn-outline-secondary btn-sm" onclick="toggleDetails()">
						Ver detalles de mi registro
					</button>

					<div id="registration-details" class="details-section p-3 text-start" style="display: none;">
						<h6 class="mb-3">Detalles de tu Registro:</h6>
						<ul class="list-unstyled">
							<li><strong>Fecha y Hora:</strong> <?php echo date('d/m/Y h:i:s A', strtotime($invitacion->fecha_checkin)); ?></li>
							<li class="mt-2"><strong>Clave Visual utilizada:</strong>
								<div class="d-flex align-items-center mt-1 clave-visual-summary">
									<img src="<?php echo URL_PATH; ?>core/img/clave_visual/<?php echo $invitacion->clave_visual_tipo . '/' . $invitacion->clave_visual_valor; ?>" alt="Imagen Clave">
									<div class="color-box ms-2" style="background-color: <?php echo strtolower($invitacion->clave_texto); ?>"></div>
								</div>
							</li>
							<li class="mt-2"><strong>Token de Acceso:</strong> <small><code title="<?php echo $invitacion->token_acceso; ?>"><?php echo substr($invitacion->token_acceso, 0, 8) . '...' . substr($invitacion->token_acceso, -8); ?></code></small></li>
						</ul>
					</div>
				</div>
			</div>

		<?php else: ?>

			<div class="text-center mb-4">
				<h1 class="h2 mb-3">¡Bienvenido!</h1>
				<p class="lead text-muted">Estás a punto de registrar tu asistencia para el evento:</p>
			</div>

			<div class="card shadow-sm event-card mb-4">
				<div class="card-body p-4">
					<h3 class="card-title h4"><?php echo htmlspecialchars($evento->nombre_evento); ?></h3>
					<hr>
					<p class="card-text mb-2"><i class="bi bi-calendar-event me-2"></i><strong>Fecha:</strong> <?php echo date('d \d\e F \d\e Y', strtotime($evento->fecha_evento)); ?></p>
					<p class="card-text mb-2"><i class="bi bi-clock me-2"></i><strong>Hora:</strong> <?php echo date('h:i A', strtotime($evento->fecha_evento)); ?></p>
					<p class="card-text mb-0"><i class="bi bi-geo-alt me-2"></i><strong>Modalidad:</strong> <?php echo htmlspecialchars($evento->modo); ?></p>
				</div>
			</div>

                        <div class="text-center">
                                <p>Mantén esta página abierta. El organizador activará retos en momentos específicos para confirmar tu asistencia.</p>
                                <?php if ($evento->modo == 'Presencial'): ?>
                                        <p><strong>Asegúrate de estar en el lugar del evento y tener activado el GPS de tu dispositivo.</strong></p>
                                <?php elseif ($evento->modo == 'Hibrido'): ?>
                                        <p><strong>Si asistes de forma presencial, asegúrate de estar en el lugar del evento y tener activado el GPS de tu dispositivo.</strong></p>
                                <?php endif; ?>
                                <a href="<?php echo URL_PATH; ?>asistencia/inicio/<?php echo $invitacion->token_acceso; ?>" class="btn btn-primary btn-lg mt-3">
                                        <i class="bi bi-shield-check me-2"></i> Iniciar Registro de Asistencia
                                </a>
                        </div>

		<?php endif; ?>

	</div>
</div>

<script>
	function toggleDetails() {
		const detailsDiv = document.getElementById('registration-details');
		if (detailsDiv.style.display === 'none') {
			detailsDiv.style.display = 'block';
		} else {
			detailsDiv.style.display = 'none';
		}
	}
</script>