<?php
// El header_panel.php se carga automáticamente desde el controlador.
$contactos = $datos['contactos'];
?>

<style>
	.actions-bar {
		transition: all 0.3s ease-in-out;
	}
</style>

<!-- Contenedor principal -->
<div class="container-fluid px-md-4 py-4"
	x-data="{ 
        seleccionados: [],
        toggleAll: false,
        accion: '',
        seleccionarTodos() {
            this.toggleAll = !this.toggleAll;
            let checkboxes = document.querySelectorAll('input[name=\'contactos[]\']');
            checkboxes.forEach(cb => { cb.checked = this.toggleAll; });
            this.actualizarSeleccionados();
        },
        actualizarSeleccionados() {
            this.seleccionados = Array.from(document.querySelectorAll('input[name=\'contactos[]\']:checked')).map(cb => cb.value);
        },
        prepararAccion(tipo, id = null) {
            this.accion = tipo;
            let idsParaAccion = id ? [id] : this.seleccionados;
            document.getElementById('contactosSeleccionadosJson').value = JSON.stringify(idsParaAccion);
        }
     }">

	<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
		<div>
			<h1 class="h2">Contactos Archivados</h1>
			<p class="text-muted mb-0">Gestiona los contactos que has archivado.</p>
		</div>
		<a href="<?php echo URL_PATH; ?>contacto/index" class="btn btn-outline-primary">
			<i class="bi bi-arrow-left me-1"></i> Volver a Mis Contactos
		</a>
	</div>

	<?php
	if (isset($_SESSION['mensaje'])) {
		$tipo_alerta = 'alert-info';
		if ($_SESSION['mensaje']['tipo'] === 'exito') $tipo_alerta = 'alert-success';
		if ($_SESSION['mensaje']['tipo'] === 'error') $tipo_alerta = 'alert-danger';

		echo '<div class="alert ' . $tipo_alerta . ' text-center" role="alert">' . htmlspecialchars($_SESSION['mensaje']['texto']) . '</div>';
		unset($_SESSION['mensaje']);
	}
	?>

	<div class="card shadow-sm">
		<div class="card-header bg-white d-flex justify-content-between align-items-center">
			<h5 class="mb-0"><i class="bi bi-archive-fill me-2"></i>Archivo</h5>
			<div class="actions-bar" :class="{ 'd-none': seleccionados.length === 0 }">
				<span class="me-3" x-text="seleccionados.length + ' seleccionado(s)'"></span>
				<button class="btn btn-outline-success btn-sm" @click="prepararAccion('desarchivar')" data-bs-toggle="modal" data-bs-target="#modalAccionArchivados">
					<i class="bi bi-box-arrow-up"></i> Restaurar
				</button>
				<button class="btn btn-outline-danger btn-sm ms-2" @click="prepararAccion('eliminar')" data-bs-toggle="modal" data-bs-target="#modalAccionArchivados">
					<i class="bi bi-trash-fill"></i> Eliminar Permanentemente
				</button>
			</div>
		</div>
		<div class="card-body p-0">
			<?php if (empty($contactos)): ?>
				<div class="text-center py-5">
					<i class="bi bi-archive" style="font-size: 4rem; color: #6c757d;"></i>
					<h4 class="mt-3">No tienes contactos archivados</h4>
				</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th class="text-center ps-3" style="width: 50px;"><input class="form-check-input" type="checkbox" @click="seleccionarTodos()"></th>
								<th scope="col">Nombre</th>
								<th scope="col">Email</th>
								<th scope="col">Teléfono</th>
								<th scope="col" class="text-end pe-3">Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($contactos as $contacto): ?>
								<tr>
									<td class="text-center ps-3"><input class="form-check-input" type="checkbox" name="contactos[]" value="<?php echo $contacto->id; ?>" @change="actualizarSeleccionados()"></td>
									<td><strong><?php echo htmlspecialchars($contacto->nombre); ?></strong></td>
									<td><?php echo htmlspecialchars($contacto->email); ?></td>
									<td><?php echo htmlspecialchars($contacto->telefono ?? 'N/A'); ?></td>
									<td class="text-end pe-3">
										<button class="btn btn-sm btn-success" title="Restaurar"
											@click="prepararAccion('desarchivar', <?php echo $contacto->id; ?>)"
											data-bs-toggle="modal" data-bs-target="#modalAccionArchivados">
											<i class="bi bi-box-arrow-up"></i>
										</button>
										<button class="btn btn-sm btn-danger" title="Eliminar Permanentemente"
											@click="prepararAccion('eliminar', <?php echo $contacto->id; ?>)"
											data-bs-toggle="modal" data-bs-target="#modalAccionArchivados">
											<i class="bi bi-trash-fill"></i>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Modal de Confirmación para Acciones de Archivados -->
	<div class="modal fade" id="modalAccionArchivados" tabindex="-1" aria-labelledby="modalAccionLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modalAccionLabel">
						<span x-show="accion === 'desarchivar'">Confirmar Restauración</span>
						<span x-show="accion === 'eliminar'">Confirmar Eliminación Permanente</span>
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div x-show="accion === 'desarchivar'">
						<p>¿Estás seguro de que deseas restaurar los contactos seleccionados? Volverán a tu libreta de direcciones principal.</p>
					</div>
					<div x-show="accion === 'eliminar'">
						<p>¿Estás seguro de que deseas eliminar permanentemente los contactos seleccionados?</p>
						<p class="text-danger"><strong>Advertencia:</strong> Esta acción es irreversible.</p>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
					<form id="formAccionMasivaArchivados" action="<?php echo URL_PATH; ?>contacto/procesarAccionArchivados" method="POST">
						<input type="hidden" name="contactos_seleccionados_json" id="contactosSeleccionadosJson">
						<input type="hidden" name="accion_masiva" :value="accion">
						<button type="submit" class="btn" :class="{ 'btn-success': accion === 'desarchivar', 'btn-danger': accion === 'eliminar' }">
							<span x-show="accion === 'desarchivar'">Sí, Restaurar</span>
							<span x-show="accion === 'eliminar'">Sí, Eliminar</span>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
