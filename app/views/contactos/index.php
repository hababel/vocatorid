<?php
// El header_panel.php se carga automáticamente desde el controlador.
$contactos = $datos['contactos'];
?>

<style>
	.actions-bar {
		transition: all 0.3s ease-in-out;
	}
</style>

<div class="container-fluid px-md-4 py-4"
	x-data="{ 
        seleccionados: [],
        toggleAll: false,
        accion: '',
        contactoEditar: {},
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
        },
        prepararEdicion(contacto) {
            if (!contacto) return;
            this.contactoEditar = contacto;
            document.getElementById('id_contacto_editar').value = contacto.id;
            document.getElementById('nombre_editar').value = contacto.nombre;
            document.getElementById('email_editar').value = contacto.email;
            
            const phoneInput = document.getElementById('telefono_editar_visible');
            if(phoneInput && contacto.telefono) {
                phoneInput.value = contacto.telefono.substring(3);
            }
        }
     }">

	<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
		<div>
			<h1 class="h2">Mis Contactos</h1>
			<p class="text-muted mb-0">Gestiona tu libreta de direcciones para invitar a tus eventos.</p>
		</div>
		<div>
			<a href="<?php echo URL_PATH; ?>contacto/archivados" class="btn btn-outline-secondary">
				<i class="bi bi-archive"></i> Ver Archivados
			</a>
			<button class="btn btn-success ms-2" type="button" data-bs-toggle="modal" data-bs-target="#modalImportarCSV">
				<i class="bi bi-file-earmark-arrow-up-fill"></i> Importar
			</button>
			<button class="btn btn-primary ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNuevoContacto" aria-controls="offcanvasNuevoContacto">
				<i class="bi bi-plus-circle"></i> Añadir Contacto
			</button>
		</div>
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
			<h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Libreta de Direcciones</h5>
			<div class="actions-bar" :class="{ 'd-none': seleccionados.length === 0 }">
				<span class="me-3" x-text="seleccionados.length + ' seleccionado(s)'"></span>
				<button class="btn btn-outline-secondary btn-sm" @click="prepararAccion('archivar')" data-bs-toggle="modal" data-bs-target="#modalAccionContacto">
					<i class="bi bi-archive-fill"></i> Archivar
				</button>
				<button class="btn btn-outline-danger btn-sm ms-2" @click="prepararAccion('eliminar')" data-bs-toggle="modal" data-bs-target="#modalAccionContacto">
					<i class="bi bi-trash-fill"></i> Eliminar
				</button>
			</div>
		</div>
		<div class="card-body p-0">
			<?php if (empty($contactos)): ?>
				<div class="text-center py-5">
					<i class="bi bi-journal-x" style="font-size: 4rem; color: #6c757d;"></i>
					<h4 class="mt-3">Tu libreta de direcciones está vacía</h4>
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
								<th scope="col">Fuente de Registro</th>
								<th scope="col">Evento de Origen</th>
								<th scope="col">Habeas Data</th>
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
									<td>
										<?php
										switch ($contacto->fuente_registro) {
											case 'Micrositio':
												echo '<span class="badge bg-primary"><i class="bi bi-globe me-1"></i> Micrositio</span>';
												break;
											case 'Importacion_CSV':
												echo '<span class="badge bg-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Importado</span>';
												break;
											default:
												echo '<span class="badge bg-secondary"><i class="bi bi-pencil-square me-1"></i> Manual</span>';
												break;
										}
										?>
									</td>
									<td>
										<?php if (!empty($contacto->evento_origen_nombre)): ?>
											<a href="<?php echo URL_PATH . 'evento/gestionar/' . $contacto->id_evento_origen; ?>">
												<?php echo htmlspecialchars($contacto->evento_origen_nombre); ?>
											</a>
										<?php else: ?>
											<span class="text-muted">N/A</span>
										<?php endif; ?>
									</td>
									<td class="text-center">
										<?php if ($contacto->acepta_habeas_data): ?>
											<span class="badge bg-success" title="Aceptado"><i class="bi bi-check-circle-fill"></i></span>
										<?php else: ?>
											<span class="badge bg-danger" title="No Aceptado"><i class="bi bi-x-circle-fill"></i></span>
										<?php endif; ?>
									</td>
									<td class="text-end pe-3">
										<button class="btn btn-sm btn-warning" title="Editar"
											@click="prepararEdicion(<?php echo htmlspecialchars(json_encode($contacto)); ?>)"
											data-bs-toggle="modal" data-bs-target="#modalEditarContacto">
											<i class="bi bi-pencil-fill"></i>
										</button>
										<button class="btn btn-sm btn-secondary" title="Archivar"
											@click="prepararAccion('archivar', <?php echo $contacto->id; ?>)"
											data-bs-toggle="modal" data-bs-target="#modalAccionContacto">
											<i class="bi bi-archive-fill"></i>
										</button>
										<button class="btn btn-sm btn-danger" title="Eliminar"
											@click="prepararAccion('eliminar', <?php echo $contacto->id; ?>)"
											data-bs-toggle="modal" data-bs-target="#modalAccionContacto">
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

	<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNuevoContacto" aria-labelledby="offcanvasNuevoContactoLabel">
		<div class="offcanvas-header">
			<h5 id="offcanvasNuevoContactoLabel">Añadir Nuevo Contacto</h5>
			<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body">
			<p>Completa los datos del nuevo contacto.</p>
			<hr>
			<form id="formNuevoContacto" action="<?php echo URL_PATH; ?>contacto/crear" method="POST">
				<div class="mb-3">
					<label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
					<input type="text" class="form-control" id="nombre" name="nombre" required>
				</div>
				<div class="mb-3">
					<label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
					<input type="email" class="form-control" id="email" name="email" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Teléfono Móvil <span class="text-danger">*</span></label>
					<input type="tel" class="form-control" id="telefono_nuevo">
				</div>
				<div class="d-grid mt-4">
					<button type="submit" class="btn btn-primary">Guardar Contacto</button>
				</div>
			</form>
		</div>
	</div>

	<div class="modal fade" id="modalImportarCSV" tabindex="-1" aria-labelledby="modalImportarLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
			</div>
		</div>
	</div>

	<div class="modal fade" id="modalEditarContacto" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modalEditarLabel">Editar Contacto</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form id="formEditarContacto" action="<?php echo URL_PATH; ?>contacto/actualizar" method="POST">
					<div class="modal-body">
						<input type="hidden" name="id_contacto_editar" id="id_contacto_editar">
						<div class="mb-3">
							<label for="nombre_editar" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
							<input type="text" class="form-control" id="nombre_editar" name="nombre_editar" required>
						</div>
						<div class="mb-3">
							<label for="email_editar" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
							<input type="email" class="form-control" id="email_editar" name="email_editar" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Teléfono Móvil <span class="text-danger">*</span></label>
							<input type="tel" class="form-control" id="telefono_editar_visible">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
						<button type="submit" class="btn btn-primary">Guardar Cambios</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modalAccionContacto" tabindex="-1" aria-labelledby="modalAccionLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
			</div>
		</div>
	</div>
</div>

<script>
	const URL_PATH = '<?php echo URL_PATH; ?>';
</script>
<script src="<?php echo URL_PATH; ?>core/customassets/js/country-codes.js"></script>

<?php
// El footer_panel.php se carga automáticamente desde el controlador.
?>