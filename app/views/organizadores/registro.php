<?php
// El header se carga automáticamente desde el controlador.
// Solo nos enfocamos en el contenido específico de esta página.
?>

<div class="container container-main d-flex align-items-center">
	<div class="w-100" style="max-width: 500px; margin: auto;">

		<div class="text-center mb-4">
			<h1 class="h3 mb-3 fw-normal"><?php echo NOMBRE_SITIO; ?></h1>
			<p class="text-muted">Crea tu cuenta de Organizador</p>
		</div>

		<?php
		// Inicia la sesión si no está iniciada para poder leer las variables de sesión.
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		// --- BLOQUE PARA MOSTRAR MENSAJES DE NOTIFICACIÓN ---
		if (isset($_SESSION['mensaje'])) {
			$tipo_alerta = $_SESSION['mensaje']['tipo'] === 'exito' ? 'alert-success' : 'alert-danger';

			echo '<div class="alert ' . $tipo_alerta . ' text-center" role="alert">';
			echo htmlspecialchars($_SESSION['mensaje']['texto']);
			echo '</div>';

			unset($_SESSION['mensaje']);
		}
		?>

		<div class="card p-4">
			<!-- 
                Inicializamos Alpine.js con x-data.
                Definimos las variables para los campos del formulario y la lógica de validación.
            -->
			<div x-data="{
                nombre_completo: '',
                email: '',
                password: '',
                confirmar_password: '',
                fortaleza: { puntuacion: 0, texto: '', color: '' },
                contrasenasCoinciden() {
                    if (!this.password || !this.confirmar_password) return true;
                    return this.password === this.confirmar_password;
                },
                calcularFortaleza() {
                    let p = 0;
                    if (this.password.length >= 8) p++;
                    if (/[A-Z]/.test(this.password)) p++;
                    if (/[a-z]/.test(this.password)) p++;
                    if (/[0-9]/.test(this.password)) p++;
                    if (/[^A-Za-z0-9]/.test(this.password)) p++;
                    this.fortaleza.puntuacion = p;
                    switch (p) {
                        case 1: case 2: this.fortaleza.texto = 'Débil'; this.fortaleza.color = 'bg-danger'; break;
                        case 3: this.fortaleza.texto = 'Moderada'; this.fortaleza.color = 'bg-warning'; break;
                        case 4: case 5: this.fortaleza.texto = 'Fuerte'; this.fortaleza.color = 'bg-success'; break;
                        default: this.fortaleza.texto = ''; this.fortaleza.color = '';
                    }
                }
            }">
				<form action="<?php echo URL_PATH; ?>organizador/crear" method="POST">

					<div class="form-floating mb-3">
						<input type="text" class="form-control" id="nombre" name="nombre_completo" placeholder="Juan Pérez" x-model="nombre_completo" required>
						<label for="nombre">Nombre Completo</label>
					</div>

					<div class="form-floating mb-3">
						<input type="email" class="form-control" id="email" name="email" placeholder="nombre@ejemplo.com" x-model="email" required>
						<label for="email">Correo Electrónico</label>
					</div>

					<div class="form-floating mb-3">
						<input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" x-model="password" @input="calcularFortaleza()" required>
						<label for="password">Contraseña</label>
					</div>

					<!-- Barra de Fortaleza de Contraseña -->
					<div class="mb-3" x-show="password.length > 0">
						<div class="progress" style="height: 5px;">
							<div class="progress-bar" role="progressbar" :class="fortaleza.color" :style="`width: ${(fortaleza.puntuacion / 5) * 100}%`"></div>
						</div>
						<small class="form-text text-muted" x-text="fortaleza.texto"></small>
					</div>

					<div class="form-floating mb-3">
						<input type="password" class="form-control" id="confirmar_password" name="confirmar_password" placeholder="Confirmar Contraseña" x-model="confirmar_password" required>
						<label for="confirmar_password">Confirmar Contraseña</label>
					</div>

					<!-- Mensaje de Error si no coinciden -->
					<template x-if="!contrasenasCoinciden()">
						<div class="alert alert-danger p-2 text-center" role="alert">
							Las contraseñas no coinciden.
						</div>
					</template>

					<!-- El botón se deshabilita si las contraseñas no coinciden o si la fortaleza es menor a 3 -->
					<button class="w-100 btn btn-lg btn-primary" type="submit" :disabled="!contrasenasCoinciden() || fortaleza.puntuacion < 3">
						Crear Cuenta
					</button>
				</form>
			</div>
		</div>

		<p class="mt-4 text-center text-muted">
			¿Ya tienes una cuenta? <a href="<?php echo URL_PATH; ?>organizador/login">Inicia Sesión</a>
		</p>

	</div>
</div>

<?php
// El footer se carga automáticamente desde el controlador.
?>