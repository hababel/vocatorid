<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
$mensaje_principal = $datos['mensaje_principal'];
$mensaje_secundario = $datos['mensaje_secundario'];
$es_exito = $datos['es_exito'];
?>

<style>
	.status-icon {
		font-size: 5rem;
	}

	.event-details {
		background-color: #f8f9fa;
		border: 1px solid #dee2e6;
		border-radius: 0.5rem;
	}
</style>

<div class="container container-main d-flex align-items-center">
	<div class="w-100 text-center" style="max-width: 600px; margin: auto;">

		<?php if ($es_exito): ?>
			<i class="bi bi-check-circle-fill text-success status-icon"></i>
		<?php else: ?>
			<i class="bi bi-info-circle-fill text-warning status-icon"></i>
		<?php endif; ?>

		<h1 class="h2 mt-3"><?php echo htmlspecialchars($mensaje_principal); ?></h1>
		<p class="lead text-muted"><?php echo htmlspecialchars($mensaje_secundario); ?></p>

		<hr class="my-4">

		<div class="card shadow-sm">
			<div class="card-header">
				Detalles del Evento
			</div>
			<div class="card-body text-start">
				<h5 class="card-title"><?php echo htmlspecialchars($evento->nombre_evento); ?></h5>
				<p class="card-text mb-2">
					<i class="bi bi-calendar-event me-2"></i>
					<strong>Fecha:</strong> <?php echo date('d \d\e F \d\e Y, h:i A', strtotime($evento->fecha_evento)); ?>
				</p>
				<p class="card-text mb-0">
					<i class="bi bi-geo-alt me-2"></i>
					<strong>Modalidad:</strong> <?php echo htmlspecialchars($evento->modo); ?>
				</p>
			</div>
		</div>

	</div>
</div>

<?php
// El footer.php se carga automáticamente desde el controlador.
?>