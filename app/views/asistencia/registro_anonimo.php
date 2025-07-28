<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
?>

<div class="container container-main d-flex align-items-center">
	<div class="w-100" style="max-width: 500px; margin: auto;">

		<div class="text-center mb-4">
			<h1 class="h2 mb-3">Registro al Evento</h1>
			<p class="lead text-muted">Estás a punto de registrarte para:</p>
			<h2 class="h4"><?php echo htmlspecialchars($evento->nombre_evento); ?></h2>
		</div>

		<div class="card shadow-sm">
			<div class="card-body p-4" x-data="{ aceptado: false }">
				<p>Por favor, completa tus datos para recibir tu enlace de acceso personal.</p>
				<form action="<?php echo URL_PATH; ?>asistencia/procesarRegistroAnonimo" method="POST">

					<input type="hidden" name="id_evento" value="<?php echo $evento->id; ?>">

					<div class="form-floating mb-3">
						<input type="text" class="form-control" id="nombre" name="nombre" placeholder="Tu Nombre Completo" required>
						<label for="nombre">Nombre Completo</label>
					</div>

					<div class="form-floating mb-3">
						<input type="email" class="form-control" id="email" name="email" placeholder="tu@correo.com" required>
						<label for="email">Correo Electrónico</label>
					</div>

					<div class="form-floating mb-3">
						<input type="tel" class="form-control" id="telefono" name="telefono" placeholder="3001234567" required>
						<label for="telefono">Teléfono Móvil (Requerido)</label>
						<div class="form-text px-2">Necesario para verificar tu asistencia con nuestros sistemas de seguridad.</div>
					</div>
					<div class="form-check mb-3">
						<input class="form-check-input" type="checkbox" name="acepta_habeas_data" id="acepta_habeas_data" x-model="aceptado" required>
						<label class="form-check-label" for="acepta_habeas_data">
							Acepto la <a href="#" target="_blank">política de tratamiento de datos personales</a>.
						</label>
					</div>

					<div class="d-grid mt-4">
						<button class="btn btn-primary btn-lg" type="submit" :disabled="!aceptado">
							Registrarme y Recibir mi Acceso
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>