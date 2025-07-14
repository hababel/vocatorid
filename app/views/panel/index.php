<?php
// El header_panel.php se carga automáticamente desde el controlador.
$eventosActivos = $datos['eventos_activos'];
$eventosFinalizados = $datos['eventos_finalizados'];
?>

<!-- Librería para los gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Contenedor principal -->
<div class="container-fluid px-md-4 py-4">

	<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
		<div>
			<h1 class="h2">Panel de Control</h1>
			<p class="text-muted mb-0">Gestiona tus eventos activos y consulta tu historial.</p>
		</div>
		<a href="<?php echo URL_PATH; ?>evento/crear" class="btn btn-primary">
			<i class="bi bi-plus-circle me-1"></i> Crear Nuevo Evento
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

	<!-- SECCIÓN DE EVENTOS ACTIVOS -->
	<h4 class="mb-3">Eventos Activos</h4>
	<div class="row g-4">
		<?php if (empty($eventosActivos)): ?>
			<div class="col-12">
				<div class="text-center py-5 bg-light rounded">
					<i class="bi bi-calendar-x" style="font-size: 3rem; color: #6c757d;"></i>
					<h5 class="mt-3">No tienes eventos activos</h5>
					<p class="text-muted">¡Crea un nuevo evento para empezar!</p>
				</div>
			</div>
		<?php else: ?>
			<?php foreach ($eventosActivos as $evento): ?>
				<div class="col-md-6 col-lg-4">
					<div class="card h-100 shadow-sm">
						<div class="card-body d-flex flex-column">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<h5 class="card-title mb-0"><?php echo htmlspecialchars($evento->nombre_evento); ?></h5>
								<!-- NUEVO: Menú de acciones para cada evento -->
								<div class="dropdown">
									<button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
										<i class="bi bi-three-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end">
										<li><a class="dropdown-item" href="<?php echo URL_PATH; ?>evento/editar/<?php echo $evento->id; ?>"><i class="bi bi-pencil-fill me-2"></i>Editar</a></li>
										<li><a class="dropdown-item" href="<?php echo URL_PATH; ?>evento/clonar/<?php echo $evento->id; ?>"><i class="bi bi-copy me-2"></i>Clonar</a></li>
										<li>
											<hr class="dropdown-divider">
										</li>
										<li><button class="dropdown-item text-danger" onclick="confirmarEliminacion(<?php echo $evento->id; ?>, '<?php echo htmlspecialchars($evento->nombre_evento, ENT_QUOTES); ?>')"><i class="bi bi-trash-fill me-2"></i>Eliminar</button></li>
									</ul>
								</div>
							</div>
							<p class="card-subtitle text-muted small"><i class="bi bi-clock me-1"></i><?php echo date('d/m/Y h:i A', strtotime($evento->fecha_evento)); ?></p>
							<hr>
							<div class="d-flex justify-content-around text-center my-2">
								<div>
									<h6 class="mb-0">Estado</h6>
									<?php
									$estado_clase = 'bg-secondary';
									if ($evento->estado == 'Publicado') $estado_clase = 'bg-success';
									if ($evento->estado == 'En Curso') $estado_clase = 'bg-info';
									?>
									<span class="badge <?php echo $estado_clase; ?>"><?php echo htmlspecialchars($evento->estado); ?></span>
								</div>
								<div>
									<h6 class="mb-0">Invitados</h6>
									<span class="fw-bold fs-5"><?php echo $evento->total_invitados ?? 0; ?></span>
								</div>
								<div>
									<h6 class="mb-0">Confirmados</h6>
									<span class="fw-bold fs-5 text-success"><?php echo $evento->total_confirmados ?? 0; ?></span>
								</div>
							</div>
							<div class="mt-auto pt-3 border-top">
								<a href="<?php echo URL_PATH; ?>evento/gestionar/<?php echo $evento->id; ?>" class="btn btn-primary w-100">Gestionar Evento</a>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>


	<!-- SECCIÓN DE HISTORIAL DE EVENTOS -->
	<div class="mt-5">
		<div class="accordion" id="accordionHistorial">
			<div class="accordion-item">
				<h2 class="accordion-header" id="headingOne">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHistorial" aria-expanded="false" aria-controls="collapseHistorial">
						<i class="bi bi-archive-fill me-2"></i>Historial de Eventos Finalizados y Cancelados
					</button>
				</h2>
				<div id="collapseHistorial" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionHistorial">
					<div class="accordion-body p-0">
						<?php if (empty($eventosFinalizados)): ?>
							<p class="text-center text-muted p-4">No tienes eventos en tu historial.</p>
						<?php else: ?>
							<div class="table-responsive">
								<table class="table table-sm table-hover align-middle mb-0">
									<thead class="table-light">
										<tr>
											<th scope="col" class="ps-3">Nombre del Evento</th>
											<th scope="col">Fecha</th>
											<th scope="col">Estado</th>
											<th scope="col" class="text-end pe-3">Acciones</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($eventosFinalizados as $evento): ?>
											<tr>
												<td class="ps-3"><?php echo htmlspecialchars($evento->nombre_evento); ?></td>
												<td><?php echo date('d/m/Y', strtotime($evento->fecha_evento)); ?></td>
												<td>
													<span class="badge <?php echo ($evento->estado == 'Finalizado') ? 'bg-dark' : 'bg-danger'; ?>">
														<?php echo htmlspecialchars($evento->estado); ?>
													</span>
												</td>
												<td class="text-end pe-3">
													<a href="#" class="btn btn-sm btn-secondary" title="Ver Reporte"><i class="bi bi-bar-chart-fill"></i></a>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalEliminarLabel">Confirmar Eliminación</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>¿Estás seguro de que deseas eliminar el evento "<strong id="nombreEventoEliminar"></strong>"?</p>
				<p class="text-danger"><strong>Advertencia:</strong> Esta acción es irreversible y se eliminarán todas las invitaciones y registros de asistencia asociados.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
				<form id="formEliminar" action="<?php echo URL_PATH; ?>evento/eliminar" method="POST">
					<input type="hidden" name="id_evento_eliminar" id="idEventoEliminar">
					<button type="submit" class="btn btn-danger">Sí, Eliminar Evento</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
	function confirmarEliminacion(id, nombre) {
		document.getElementById('nombreEventoEliminar').textContent = nombre;
		document.getElementById('idEventoEliminar').value = id;
		var modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
		modal.show();
	}
</script>

<?php
// El footer_panel.php se carga automáticamente desde el controlador.
?>