<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
?>

<style>
	body {
		background-color: #f0f9f5;
		/* Un fondo verde muy suave y agradable */
	}

	.confirmation-card {
		border: none;
		border-radius: 1rem;
		overflow: hidden;
		/* Para que la cabecera verde respete los bordes redondeados */
		box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
		margin-top: 2rem;
	}

	.card-header-success {
		background-color: #198754;
		/* Verde de éxito */
		color: white;
		padding: 2.5rem 1rem;
		position: relative;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
	}

	.success-icon {
		font-size: 4rem;
		line-height: 1;
	}

	/* Ilustración de fondo sutil */
	.card-header-success::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
	}

	.card-body-content {
		padding: 2rem;
		text-align: center;
	}
</style>

<div class="container container-main d-flex align-items-center">
	<div class="w-100" style="max-width: 500px; margin: auto;">

		<div class="card confirmation-card">
			<div class="card-header-success">
				<i class="bi bi-check-circle-fill success-icon"></i>
				<h1 class="h2 mt-3 mb-0">¡Todo listo!</h1>
			</div>
			<div class="card-body card-body-content">
				<h5 class="card-title">Tu asistencia ha sido confirmada.</h5>
				<p class="text-muted">Hemos registrado tu ingreso de forma exitosa. Ya no necesitas hacer nada más.</p>
				<hr class="my-4">
				<p class="mb-1"><strong>Evento:</strong><br><?php echo htmlspecialchars($evento->nombre_evento); ?></p>
				<p class="mb-0"><strong>Fecha:</strong><br><?php echo date('d \d\e F \d\e Y', strtotime($evento->fecha_evento)); ?></p>
				<div class="d-grid mt-4">
					<a href="#" class="btn btn-secondary disabled" aria-disabled="true">¡A disfrutar del evento!</a>
				</div>
			</div>
		</div>

	</div>
</div>
