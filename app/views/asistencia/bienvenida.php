<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
?>

<style>
	.event-card {
		border-left: 5px solid #0d6efd;
	}
</style>

<div class="container container-main d-flex align-items-center">
	<div class="w-100" style="max-width: 600px; margin: auto;">

		<div class="text-center mb-4">
			<h1 class="h2 mb-3">¡Bienvenido!</h1>
			<p class="lead text-muted">Estás a punto de registrar tu asistencia para el evento:</p>
		</div>

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
		<div class="card shadow-sm event-card mb-4">
			<div class="card-body p-4">
				<h3 class="card-title h4"><?php echo htmlspecialchars($evento->nombre_evento); ?></h3>
				<hr>
				<p class="card-text mb-2">
					<i class="bi bi-calendar-event me-2"></i>
					<strong>Fecha:</strong> <?php echo date('d \d\e F \d\e Y', strtotime($evento->fecha_evento)); ?>
				</p>
				<p class="card-text mb-2">
					<i class="bi bi-clock me-2"></i>
					<strong>Hora:</strong> <?php echo date('h:i A', strtotime($evento->fecha_evento)); ?>
				</p>
				<p class="card-text mb-0">
					<i class="bi bi-geo-alt me-2"></i>
					<strong>Modalidad:</strong> <?php echo htmlspecialchars($evento->modo); ?>
				</p>
			</div>
		</div>

		<?php if ($invitacion->asistencia_verificada): ?>
			<div class="alert alert-success text-center p-4">
				<i class="bi bi-check-circle-fill h1"></i>
				<h4 class="alert-heading mt-2">¡Todo listo!</h4>
				<p class="mb-0">Tu asistencia para este evento ya ha sido registrada exitosamente.</p>
			</div>
		<?php else: ?>
			<div class="text-center">
				<p>Para completar el proceso, necesitarás escanear el código QR que se mostrará en la pantalla del evento.</p>
				<p><strong>Asegúrate de estar en el lugar del evento y tener activado el GPS de tu dispositivo.</strong></p>

				<a href="<?php echo URL_PATH; ?>asistencia/iniciarVerificacion/<?php echo $invitacion->token_acceso; ?>" class="btn btn-primary btn-lg mt-3">
					<i class="bi bi-qr-code-scan me-2"></i> Iniciar Registro de Asistencia
				</a>
			</div>
		<?php endif; ?>

	</div>
</div>