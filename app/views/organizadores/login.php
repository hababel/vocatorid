<?php
// El header se carga automáticamente desde el controlador.
// Solo nos enfocamos en el contenido específico de esta página.
?>

<div class="container container-main d-flex align-items-center">
	<div class="w-100" style="max-width: 450px; margin: auto;">

		<div class="text-center mb-4">
			<h1 class="h3 mb-3 fw-normal"><?php echo NOMBRE_SITIO; ?></h1>
			<p class="text-muted">Inicia sesión en tu cuenta</p>
		</div>

		<?php
		// Inicia la sesión si no está iniciada para poder leer las variables de sesión.
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		// --- BLOQUE PARA MOSTRAR MENSAJES DE NOTIFICACIÓN ---
		if (isset($_SESSION['mensaje'])) {
			// Determinar el color de la alerta según el tipo de mensaje
			$tipo_alerta = $_SESSION['mensaje']['tipo'] === 'exito' ? 'alert-success' : 'alert-danger';

			// Mostrar la alerta
			echo '<div class="alert ' . $tipo_alerta . ' text-center" role="alert">';
			echo htmlspecialchars($_SESSION['mensaje']['texto']);
			echo '</div>';

			// Eliminar el mensaje de la sesión para que no se muestre de nuevo
			unset($_SESSION['mensaje']);
		}
		?>

		<div class="card p-4">
			<form action="<?php echo URL_PATH; ?>organizador/procesarLogin" method="POST">

				<div class="form-floating mb-3">
					<input type="email" class="form-control" id="email" name="email" placeholder="nombre@ejemplo.com" required>
					<label for="email">Correo Electrónico</label>
				</div>

				<div class="form-floating mb-3">
					<input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
					<label for="password">Contraseña</label>
				</div>

				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="rememberMe">
						<label class="form-check-label" for="rememberMe">
							Recordarme
						</label>
					</div>
					<a href="#">¿Olvidaste tu contraseña?</a>
				</div>

				<button class="w-100 btn btn-lg btn-primary" type="submit">
					Iniciar Sesión
				</button>
			</form>
		</div>

		<p class="mt-4 text-center text-muted">
			¿No tienes una cuenta? <a href="<?php echo URL_PATH; ?>organizador/registro">Regístrate</a>
		</p>

	</div>
</div>

<?php
// El footer se carga automáticamente desde el controlador.
?>