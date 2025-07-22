<?php

$evento = $datos['evento'];
?>

<style>
	.confirmation-container {
		text-align: center;
		padding: 3rem 1rem;
	}

	.success-icon {
		font-size: 6rem;
		color: #198754;
		/* Verde de éxito */
	}

	.event-card-summary {
		background-color: #f8f9fa;
		border: 1px solid #dee2e6;
		border-radius: 0.75rem;
		text-align: left;
		margin-top: 2rem;
	}
</style>

<div class="container container-main d-flex align-items-center">
	<div class="w-100" style="max-width: 600px; margin: auto;">

		<div class="confirmation-container">
			<i class="bi bi-check-circle-fill success-icon"></i>
			<h1 class="display-5 mt-3">¡Asistencia Confirmada!</h1>
			<p class="lead text-muted">Tu registro para el evento ha sido procesado exitosamente.</p>
			<p>Ya puedes cerrar esta ventana. ¡Disfruta del evento!</p>

			<div class="card event-card-summary">
				<div class="card-body p-4">
					<h5 class="card-title mb-3">Resumen del Evento:</h5>
					<p class="card-text mb-2">
						<strong>Evento:</strong> <?php echo htmlspecialchars($evento->nombre_evento); ?>
					</p>
					<p class="card-text mb-0">
						<strong>Fecha:</strong> <?php echo date('d \d\e F \d\e Y', strtotime($evento->fecha_evento)); ?>
					</p>
				</div>
			</div>
		</div>

	</div>
</div>
