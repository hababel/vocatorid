<?php
// app/views/eventos/agregar_invitados.php
?>

<div x-data="{ 
    modo: 'seleccionar', 
    contactosSeleccionados: [],
    enviarInvitacion: true,
    toggleSelectAll(event) {
        let checkboxes = document.querySelectorAll('.contacto-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = event.target.checked;
        });
    }
}">
	<div class="card shadow-sm">
		<div class="card-header bg-light">
			<div class="btn-group w-100" role="group">
				<button type="button" class="btn" :class="modo === 'seleccionar' ? 'btn-primary' : 'btn-outline-primary'" @click="modo = 'seleccionar'"><i class="bi bi-list-check me-2"></i>Seleccionar de Contactos</button>
				<button type="button" class="btn" :class="modo === 'manual' ? 'btn-primary' : 'btn-outline-primary'" @click="modo = 'manual'"><i class="bi bi-person-plus me-2"></i>Añadir Manualmente</button>
				<button type="button" class="btn" :class="modo === 'csv' ? 'btn-primary' : 'btn-outline-primary'" @click="modo = 'csv'"><i class="bi bi-file-earmark-arrow-up me-2"></i>Importar desde CSV</button>
			</div>
		</div>

		<div class="card-body p-4">
			<form action="<?php echo URL_PATH; ?>invitacion/agregar" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="id_evento" value="<?php echo $evento->id; ?>">
				<input type="hidden" name="modo_agregar" :value="modo">

				<div x-show="modo === 'seleccionar'" x-transition>
					<h5>Selecciona los contactos a invitar</h5>
					<p class="text-muted small">Elige uno o más contactos de tu libreta de direcciones que aún no han sido invitados a este evento.</p>
					<?php if (empty($contactos_no_invitados)): ?>
						<div class="alert alert-info">Todos tus contactos ya han sido invitados a este evento.</div>
					<?php else: ?>
						<div class="form-check border-bottom pb-2 mb-2">
							<input class="form-check-input" type="checkbox" id="seleccionarTodosContactos" @click="toggleSelectAll($event)">
							<label class="form-check-label" for="seleccionarTodosContactos">
								<strong>Seleccionar Todos / Ninguno</strong>
							</label>
						</div>
						<div class="mb-3" style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; padding: 1rem; border-radius: 0.5rem;">
							<?php foreach ($contactos_no_invitados as $contacto): ?>
								<div class="form-check">
									<input class="form-check-input contacto-checkbox" type="checkbox" name="contactos_ids[]" value="<?php echo $contacto->id; ?>" id="contacto_<?php echo $contacto->id; ?>">
									<label class="form-check-label" for="contacto_<?php echo $contacto->id; ?>">
										<?php echo htmlspecialchars($contacto->nombre); ?> <small class="text-muted">(<?php echo htmlspecialchars($contacto->email); ?>)</small>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<div x-show="modo === 'manual'" x-transition>
					<h5>Añadir un nuevo invitado manualmente</h5>
					<p class="text-muted small">El contacto se guardará en tu libreta de direcciones y se invitará al evento.</p>
					<div class="row g-3">
						<div class="col-md-6"><label for="nombre_manual" class="form-label">Nombre Completo <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre_manual" id="nombre_manual" required></div>
						<div class="col-md-6"><label for="email_manual" class="form-label">Correo Electrónico <span class="text-danger">*</span></label><input type="email" class="form-control" name="email_manual" id="email_manual" required></div>
						<div class="col-md-12"><label for="telefono_manual" class="form-label">Teléfono Móvil <span class="text-danger">*</span></label><input type="tel" class="form-control" name="telefono_manual" id="telefono_manual" required></div>
					</div>
				</div>

				<div x-show="modo === 'csv'" x-transition>
					<h5>Importar invitados desde un archivo CSV</h5>
					<div class="alert alert-info d-flex justify-content-between align-items-center">
						<div>
							<strong>Formato:</strong>
							<ul class="mb-0 small">
								<li>Columna 1: <strong>Nombre Completo</strong>, Columna 2: <strong>Email</strong>, Columna 3: <strong>Teléfono (Obligatorio)</strong></li>
								<li>La primera fila se ignora (cabecera).</li>
							</ul>
						</div>
						<a href="<?php echo URL_PATH; ?>contacto/descargarPlantilla" class="btn btn-outline-primary btn-sm"><i class="bi bi-download me-2"></i>Descargar Plantilla</a>
					</div>
					<div class="mb-3"><label for="archivo_csv" class="form-label">Selecciona tu archivo .csv <span class="text-danger">*</span></label><input class="form-control" type="file" name="archivo_csv" id="archivo_csv" accept=".csv" required></div>
				</div>

				<hr class="my-4">

				<div class="d-flex justify-content-between align-items-center">
					<div class="form-check form-switch">
						<input class="form-check-input" type="checkbox" role="switch" name="enviar_invitacion_ahora" id="enviarInvitacion" value="1" x-model="enviarInvitacion" checked>
						<label class="form-check-label" for="enviarInvitacion">Enviar invitación por correo electrónico inmediatamente</label>
					</div>
					<button type="submit" class="btn btn-success"><i class="bi bi-person-check-fill me-2"></i>Añadir Invitados</button>
				</div>
			</form>
		</div>
	</div>
</div>