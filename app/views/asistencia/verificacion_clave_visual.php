<?php
$invitacion = $datos['invitacion'];
$opciones_imagenes = $datos['opciones_imagenes'];
$opciones_colores = $datos['opciones_colores'];
?>
<style>
	.clave-option-img {
		border: 4px solid transparent;
		border-radius: 0.75rem;
		transition: all 0.2s ease-in-out;
		cursor: pointer;
	}

	.clave-option-img:hover {
		transform: scale(1.05);
		border-color: #0d6efd;
	}

	.clave-option-img.selected {
		border-color: #0d6efd;
		box-shadow: 0 0 15px rgba(13, 110, 253, 0.5);
	}

	.color-btn {
		width: 60px;
		height: 60px;
		border: 4px solid transparent;
		transition: all 0.2s ease-in-out;
	}

	.color-btn.selected {
		border-color: #212529;
		transform: scale(1.1);
	}
</style>
<div class="container container-main d-flex align-items-center">
	<div class="w-100" style="max-width: 700px; margin: auto;">
		<div class="text-center mb-4">
			<h1 class="h2 mb-3">Verificación Final</h1>
			<p class="lead text-muted">Para completar tu registro, selecciona la clave visual que hemos enviado a tu correo.</p>
		</div>
		<div class="card shadow-sm">
			<div class="card-body p-4" x-data="{ selectedImage: '', selectedColor: '' }">
				<form action="<?php echo URL_PATH; ?>asistencia/procesarClaveVisual" method="POST">
					<input type="hidden" name="token_acceso" value="<?php echo $invitacion->token_acceso; ?>">
					<input type="hidden" name="clave_imagen" x-model="selectedImage">
					<input type="hidden" name="clave_color" x-model="selectedColor">

					<h5 class="text-center">1. Selecciona la Imagen Correcta</h5>
					<div class="row g-3 justify-content-center mb-4">
						<?php foreach ($opciones_imagenes as $img): ?>
							<div class="col-4 col-md-3">
								<img src="<?php echo URL_PATH; ?>public/img/clave_visual/<?php echo $invitacion->clave_visual_tipo . '/' . $img; ?>"
									class="img-fluid clave-option-img"
									:class="{ 'selected': selectedImage === '<?php echo $img; ?>' }"
									@click="selectedImage = '<?php echo $img; ?>'">
							</div>
						<?php endforeach; ?>
					</div>

					<h5 class="text-center">2. Selecciona el Color Correcto</h5>
					<div class="d-flex justify-content-center gap-3 mb-4">
						<?php foreach ($opciones_colores as $color): ?>
							<button type="button" class="btn color-btn rounded-circle"
								style="background-color: <?php echo strtolower($color); ?>"
								:class="{ 'selected': selectedColor === '<?php echo $color; ?>' }"
								@click="selectedColor = '<?php echo $color; ?>'"></button>
						<?php endforeach; ?>
					</div>

					<div class="d-grid">
						<button type="submit" class="btn btn-primary btn-lg" :disabled="!selectedImage || !selectedColor">
							<i class="bi bi-check-circle-fill me-2"></i>Verificar Asistencia
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>