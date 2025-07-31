<?php
// El header_panel.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
$invitados = $datos['invitados'];
$contactos_no_invitados = $datos['contactos_no_invitados'] ?? [];
$dias_faltantes = $datos['dias_faltantes'] ?? null;
$is_publicado = ($evento->estado == 'Publicado');

// Lógica para el Checklist y Puntuación de Salud
$checklist = [
	'publicado' => $is_publicado,
	'invitados_agregados' => false,
	'invitaciones_enviadas' => false,
];
$puntuacion = 0;
if ($checklist['publicado']) $puntuacion += 40;

$total_invitados = 0;
$total_enviados = 0;
$total_confirmados = 0;
$total_rechazados = 0;
$total_pendientes_rsvp = 0;
$total_asistentes = 0;
$invitaciones_pendientes_de_envio = 0;

if (!empty($invitados)) {
	$total_invitados = count($invitados);
	foreach ($invitados as $invitado) {
		if ($invitado->fecha_invitacion) {
			$total_enviados++;
		} else {
			$invitaciones_pendientes_de_envio++;
		}
		if ($invitado->estado_rsvp == 'Confirmado') $total_confirmados++;
		elseif ($invitado->estado_rsvp == 'Rechazado') $total_rechazados++;
		else $total_pendientes_rsvp++;
		if ($invitado->asistencia_verificada) $total_asistentes++;
	}
}
if ($total_invitados > 0) $checklist['invitados_agregados'] = true;
if ($checklist['invitados_agregados']) $puntuacion += 30;
if ($total_enviados > 0 && $invitaciones_pendientes_de_envio == 0) {
	$checklist['invitaciones_enviadas'] = true;
	if ($checklist['invitaciones_enviadas']) $puntuacion += 30;
}

// LÓGICA PARA EL GRÁFICO DE ASISTENCIA Y PORCENTAJES
$asistencia_completa_virtual = 0;
$asistencia_completa_fisico = 0;
$asistencia_iniciada = 0;

if (!empty($invitados)) {
	foreach ($invitados as $invitado) {
		if ($invitado->asistencia_verificada) {
			if ($invitado->metodo_checkin == 'Verificado por 3-FAV') {
				$asistencia_completa_virtual++;
			} else { // Asumimos que cualquier otro método es físico/manual
				$asistencia_completa_fisico++;
			}
		} elseif (!empty($invitado->clave_visual_tipo)) {
			$asistencia_iniciada++;
		}
	}
}
$total_registrados = $asistencia_completa_virtual + $asistencia_completa_fisico;
$asistencia_pendiente = $total_invitados - ($total_registrados + $asistencia_iniciada);
$porcentaje_eficiencia = ($total_invitados > 0) ? ($total_registrados / $total_invitados) * 100 : 0;
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
	.icon-list {
		list-style: none;
		padding-left: 0;
	}

	.icon-list li {
		display: flex;
		align-items: flex-start;
		margin-bottom: 0.75rem;
		font-size: 0.95rem;
	}

	.icon-list .icon {
		font-size: 1.1rem;
		margin-right: 0.75rem;
		color: #0d6efd;
		width: 20px;
		text-align: center;
		padding-top: 2px;
	}

	.chart-container {
		position: relative;
		height: 250px;
		width: 100%;
	}

	.asistencia-chart-container {
		position: relative;
		height: 250px;
		width: 100%;
	}

	#map-gestionar {
		height: 100%;
		min-height: 260px;
		width: 100%;
		border-radius: 0.5rem;
		z-index: 0;
	}

	.nav-tabs .nav-link {
		color: #6c757d;
	}

	.nav-tabs .nav-link.active {
		color: #0d6efd;
		border-color: #dee2e6 #dee2e6 #fff;
	}

	.summary-card {
		border-left: 4px solid;
	}
</style>

<div class="container-fluid px-md-4 py-4" x-data="{ searchTerm: '' }">

	<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
		<div>
			<h1 class="h2">Gestionar Evento</h1>
		</div>
		<a href="<?php echo URL_PATH; ?>organizador/panel" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver al Panel</a>
	</div>

	<?php
	if (isset($_SESSION['mensaje'])) {
		$tipo_alerta = 'alert-success';
		$icono = 'bi-check-circle-fill';
		if ($_SESSION['mensaje']['tipo'] === 'error') {
			$tipo_alerta = 'alert-danger';
			$icono = 'bi-exclamation-triangle-fill';
		}
		if ($_SESSION['mensaje']['tipo'] === 'info') {
			$tipo_alerta = 'alert-warning';
			$icono = 'bi-info-circle-fill';
		}
		echo '<div id="autoCloseAlert" class="alert ' . $tipo_alerta . ' alert-dismissible fade show text-center d-flex align-items-center" role="alert">';
		echo '<i class="bi ' . $icono . ' me-2"></i>';
		echo '<div>' . htmlspecialchars($_SESSION['mensaje']['texto']) . '</div>';
		echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
		echo '</div>';
		unset($_SESSION['mensaje']);
	}
	?>

	<div class="card shadow-sm mb-4">
		<div class="card-header bg-light py-3">
			<h4 class="mb-1"><?php echo htmlspecialchars($evento->nombre_evento); ?></h4>
			<p class="text-muted mb-0 small"><?php echo htmlspecialchars($evento->objetivo); ?></p>
		</div>
		<div class="card-body p-4">
			<div class="row g-4">
				<div class="col-lg-7">
					<h6 class="mb-3 text-primary">Detalles Clave</h6>
					<ul class="icon-list">
						<li><i class="bi bi-person-video3 icon"></i>
							<div><strong>Facilitador:</strong> <?php echo htmlspecialchars($evento->nombre_instructor ?? 'Por confirmar'); ?></div>
						</li>
						<li><i class="bi bi-calendar-event icon"></i>
							<div><strong>Fecha y Hora:</strong> <?php echo date('l, d \d\e F \d\e Y - h:i A', strtotime($evento->fecha_evento)); ?></div>
						</li>
						<li><i class="bi bi-geo-alt icon"></i>
							<div><strong>Lugar:</strong> <?php echo htmlspecialchars($evento->lugar_nombre ?? 'No especificado'); ?></div>
						</li>
						<?php if (isset($dias_faltantes)): ?>
							<li>
								<i class="bi bi-hourglass-split icon text-warning"></i>
								<div>
									<strong>Tiempo restante:</strong>
									<?php
									if ($dias_faltantes === 0) {
										echo '<span class="text-danger fw-bold"> ¡Es hoy!</span>';
									} elseif ($dias_faltantes === 1) {
										echo ' Falta 1 día';
									} else {
										echo ' Faltan ' . $dias_faltantes . ' días';
									}
									?>
								</div>
							</li>
						<?php endif; ?>
					</ul>
					<div class="p-3 mt-4 bg-light rounded border">
						<h6 class="text-muted mb-2 small">ESTADO Y ACCIONES</h6>
						<div class="d-flex justify-content-between align-items-center">
							<div>
								<?php
								if ($evento->estado == 'Borrador') {
									echo '<div class="dropdown">
                                            <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-unlock-fill me-1"></i> Estado: Borrador
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form action="' . URL_PATH . 'evento/cambiarEstado" method="POST" class="d-inline">
                                                        <input type="hidden" name="id_evento" value="' . $evento->id . '">
                                                        <input type="hidden" name="nuevo_estado" value="Publicado">
                                                        <button type="submit" class="dropdown-item"><i class="bi bi-rocket-launch-fill me-2"></i>Solo Publicar</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="' . URL_PATH . 'evento/publicarYEnviar" method="POST" class="d-inline">
                                                        <input type="hidden" name="id_evento" value="' . $evento->id . '">
                                                        <button type="submit" class="dropdown-item"><i class="bi bi-envelope-arrow-up-fill me-2"></i>Publicar y Enviar Invitaciones</button>
                                                    </form>
                                                </li>
                                            </ul>
                                          </div>';
								} else {
									$estado_clase = 'bg-secondary';
									if ($evento->estado == 'Publicado') $estado_clase = 'bg-success';
									if ($evento->estado == 'En Curso') $estado_clase = 'bg-info';
									if ($evento->estado == 'Finalizado') $estado_clase = 'bg-dark';
									if ($evento->estado == 'Cancelado') $estado_clase = 'bg-danger';
									echo '<span class="badge ' . $estado_clase . ' fs-6">' . htmlspecialchars($evento->estado) . '</span>';
								}
								?>
							</div>
							<div class="btn-group" role="group">
								<?php if ($evento->estado != 'Borrador'): ?>
									<?php if ($evento->modo == 'Virtual' || $evento->modo == 'Hibrido'): ?>
                                                                       <a href="<?php echo URL_PATH; ?>evento/kioskoVirtual/<?php echo $evento->id; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Abrir Kiosco Virtual"><i class="bi bi-display-fill me-1"></i>Kiosco Virtual</a>
									<?php endif; ?>
									<?php if ($evento->modo == 'Presencial' || $evento->modo == 'Hibrido'): ?>
										<a href="<?php echo URL_PATH; ?>kioscoFisico/scanner/<?php echo $evento->id; ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Abrir Kiosco Físico"><i class="bi bi-qr-code-scan me-1"></i>Kiosco Físico</a>
									<?php endif; ?>
									<a href="<?php echo URL_PATH; ?>publico/evento/<?php echo $evento->id; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver Micrositio"><i class="bi bi-box-arrow-up-right me-1"></i>Micrositio</a>
								<?php endif; ?>
								<a href="<?php echo URL_PATH; ?>evento/editar/<?php echo $evento->id; ?>" class="btn btn-sm btn-outline-warning" title="Editar Detalles"><i class="bi bi-pencil-fill me-1"></i>Editar</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-5">
					<?php if ($evento->modo != 'Virtual' && !empty($evento->latitud)): ?>
						<div id="map-gestionar"></div>
					<?php else: ?>
						<div class="text-center bg-light p-4 rounded d-flex align-items-center justify-content-center h-100">
							<p class="text-muted mb-0"><i class="bi bi-camera-video-fill me-2"></i>Este es un evento virtual.<br>No se requiere ubicación física.</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<ul class="nav nav-tabs mb-4" id="eventoTab" role="tablist">
		<li class="nav-item" role="presentation"><button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="true"><i class="bi bi-speedometer2 me-2"></i>Dashboard</button></li>
		<li class="nav-item" role="presentation"><button class="nav-link" id="invitados-tab" data-bs-toggle="tab" data-bs-target="#invitados" type="button" role="tab" aria-controls="invitados" aria-selected="false"><i class="bi bi-people-fill me-2"></i>Gestionar Invitados (<?php echo $total_invitados; ?>)</button></li>
               <li class="nav-item" role="presentation"><button class="nav-link" id="agregar-invitados-tab" data-bs-toggle="tab" data-bs-target="#agregar-invitados" type="button" role="tab" aria-controls="agregar-invitados" aria-selected="false"><i class="bi bi-person-plus-fill me-2"></i>Agregar Invitados</button></li>
               <li class="nav-item" role="presentation"><button class="nav-link" id="retos-tab" data-bs-toggle="tab" data-bs-target="#retos" type="button" role="tab" aria-controls="retos" aria-selected="false"><i class="bi bi-shield-check me-2"></i>Gestión de Retos de Asistencia</button></li>
       </ul>
	<div class="tab-content" id="eventoTabContent">
		<div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
			<div class="row g-4">
				<div class="col-lg-8">
					<div class="card shadow-sm h-100">
						<div class="card-body">
							<h5 class="card-title"><i class="bi bi-pie-chart-fill me-2"></i>Resumen de Estado (RSVP)</h5>
							<div class="row align-items-center">
								<div class="col-md-7">
									<div class="chart-container">
										<canvas id="dynamicChart"></canvas>
									</div>
								</div>
								<div class="col-md-5">
									<div class="summary-card border-primary p-2 mb-2">
										<div class="d-flex justify-content-between align-items-center">
											<small class="text-muted">Total Invitados</small>
											<span class="fw-bold fs-5"><?php echo $total_invitados; ?></span>
										</div>
									</div>
									<div class="summary-card border-info p-2 mb-2">
										<div class="d-flex justify-content-between align-items-center">
											<small class="text-muted">Invitaciones Enviadas</small>
											<span class="fw-bold fs-5"><?php echo $total_enviados; ?></span>
										</div>
									</div>
									<div class="summary-card border-success p-2 mb-2">
										<div class="d-flex justify-content-between align-items-center">
											<small class="text-muted">RSVP Confirmados</small>
											<span class="fw-bold fs-5 text-success"><?php echo $total_confirmados; ?></span>
										</div>
									</div>
									<div class="summary-card border-danger p-2">
										<div class="d-flex justify-content-between align-items-center">
											<small class="text-muted">RSVP Rechazados</small>
											<span class="fw-bold fs-5 text-danger"><?php echo $total_rechazados; ?></span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="card shadow-sm h-100">
						<div class="card-body">
							<h5 class="card-title"><i class="bi bi-check2-circle me-2"></i>Checklist del Evento</h5>
							<ul class="list-group list-group-flush">
								<li class="list-group-item <?php echo $checklist['publicado'] ? 'text-decoration-line-through text-muted' : ''; ?>"><i class="bi <?php echo $checklist['publicado'] ? 'bi-check-circle-fill text-success' : 'bi-circle'; ?> me-2"></i>Publicar el evento</li>
								<li class="list-group-item <?php echo $checklist['invitados_agregados'] ? 'text-decoration-line-through text-muted' : ''; ?>"><i class="bi <?php echo $checklist['invitados_agregados'] ? 'bi-check-circle-fill text-success' : 'bi-circle'; ?> me-2"></i>Añadir invitados a la lista</li>
								<li class="list-group-item <?php echo $checklist['invitaciones_enviadas'] ? 'text-decoration-line-through text-muted' : ''; ?>"><i class="bi <?php echo $checklist['invitaciones_enviadas'] ? 'bi-check-circle-fill text-success' : 'bi-circle'; ?> me-2"></i>Enviar todas las invitaciones</li>
							</ul>
							<h5 class="mt-4">Puntuación de Salud del Evento</h5>
							<div class="progress" style="height: 20px;">
								<div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $puntuacion; ?>%;" aria-valuenow="<?php echo $puntuacion; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $puntuacion; ?>%</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-12">
					<div class="card shadow-sm">
						<div class="card-body">
							<h5 class="card-title"><i class="bi bi-person-check-fill me-2"></i>Estado del Registro de Asistencia</h5>
							<?php if ($total_registrados == $total_invitados && $total_invitados > 0): ?>
								<div class="alert alert-success d-flex align-items-center" role="alert">
									<i class="bi bi-star-fill me-2"></i>
									<div>
										<strong>¡Excelente!</strong> Todos los invitados han registrado su asistencia.
									</div>
								</div>
							<?php endif; ?>
							<div class="row align-items-center">
								<div class="col-md-8">
									<div id="asistenciaChartContainer" class="asistencia-chart-container">
										<canvas id="asistenciaChart"></canvas>
									</div>
								</div>
								<div class="col-md-4">
									<?php if ($evento->modo == 'Hibrido'): ?>
										<div class="summary-card p-2 mb-2" style="border-color: #0d6efd;">
											<div class="d-flex justify-content-between align-items-center">
												<small class="text-muted">Virtual (3-FAV)</small>
												<span class="fw-bold fs-5" style="color: #0d6efd;"><?php echo $asistencia_completa_virtual; ?></span>
											</div>
										</div>
										<div class="summary-card p-2 mb-2" style="border-color: #6f42c1;">
											<div class="d-flex justify-content-between align-items-center">
												<small class="text-muted">Presencial (Kiosco)</small>
												<span class="fw-bold fs-5" style="color: #6f42c1;"><?php echo $asistencia_completa_fisico; ?></span>
											</div>
										</div>
									<?php else: ?>
										<div class="summary-card border-success p-2 mb-2">
											<div class="d-flex justify-content-between align-items-center">
												<small class="text-muted">Asistencia Registrada</small>
												<span class="fw-bold fs-5 text-success"><?php echo $total_registrados; ?></span>
											</div>
										</div>
									<?php endif; ?>
									<div class="summary-card border-warning p-2 mb-2">
										<div class="d-flex justify-content-between align-items-center">
											<small class="text-muted">Proceso Iniciado</small>
											<span class="fw-bold fs-5 text-warning"><?php echo $asistencia_iniciada; ?></span>
										</div>
									</div>
									<div class="summary-card border-secondary p-2">
										<div class="d-flex justify-content-between align-items-center">
											<small class="text-muted">Pendientes por Registrar</small>
											<span class="fw-bold fs-5"><?php echo $asistencia_pendiente; ?></span>
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer bg-light text-center p-3 mt-3">
								<p class="mb-0">
									<span class="badge bg-primary"><?php echo $total_invitados; ?> Invitados (100%)</span>
									<i class="bi bi-arrow-right-short"></i>
									<span class="badge bg-success"><?php echo $total_registrados; ?> Registrados</span>
									<i class="bi bi-arrow-right-short"></i>
									<span class="badge bg-info text-dark">Eficiencia de Registro: <?php echo number_format($porcentaje_eficiencia, 1); ?>%</span>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tab-pane fade" id="invitados" role="tabpanel" aria-labelledby="invitados-tab">
			<div class="card shadow-sm">
				<div class="card-header bg-white d-flex justify-content-between align-items-center">
					<h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Panel de Invitados y Asistencia</h5>
					<?php if ($invitaciones_pendientes_de_envio > 0): ?>
						<form action="<?php echo URL_PATH; ?>invitacion/enviar" method="POST">
							<input type="hidden" name="id_evento" value="<?php echo $evento->id; ?>">
							<button type="submit" name="accion" value="enviar_pendientes" class="btn btn-info">
								<i class="bi bi-envelope-arrow-up-fill me-2"></i>Enviar a <?php echo $invitaciones_pendientes_de_envio; ?> Pendientes
							</button>
						</form>
					<?php endif; ?>
				</div>
				<div class="card-body p-0">
					<div class="p-3 border-bottom d-flex justify-content-between align-items-center">
						<div class="fw-bold text-muted">Total de Invitados: <?php echo $total_invitados; ?></div>
						<input type="text" id="searchInput" class="form-control" style="max-width: 400px;" placeholder="Buscar invitado por nombre o email...">
					</div>
					<div class="table-responsive">
						<table class="table table-hover align-middle mb-0">
							<thead class="table-light">
								<tr>
									<th class="ps-3">Nombre</th>
									<th>Email</th>
									<th>Estado de la Invitación</th>
									<th class="text-center">Acciones</th>
								</tr>
							</thead>
							<tbody id="invitados-tbody">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

               <div class="tab-pane fade" id="agregar-invitados" role="tabpanel" aria-labelledby="agregar-invitados-tab">
                       <?php require_once 'agregar_invitados.php'; ?>
               </div>

               <div class="tab-pane fade" id="retos" role="tabpanel" aria-labelledby="retos-tab">
                       <div class="card shadow-sm mb-4">
                               <div class="card-body">
                                       <form id="form-reto" class="row g-2 mb-3">
                                               <div class="col-md-4">
                                                       <input type="text" name="descripcion" class="form-control" placeholder="Descripción del Reto" required>
                                               </div>
                                               <div class="col-md-3">
                                                       <input type="datetime-local" name="hora_inicio" class="form-control" required>
                                               </div>
                                               <div class="col-md-3">
                                                       <input type="datetime-local" name="hora_fin" class="form-control" required>
                                               </div>
                                               <div class="col-md-2 d-grid">
                                                       <button class="btn btn-success">Crear Reto</button>
                                               </div>
                                       </form>
                                       <div class="mb-2 text-end">
                                               <a href="<?php echo URL_PATH; ?>evento/exportarRetos/<?php echo $evento->id; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-2"></i>Exportar Reporte</a>
                                       </div>
                                       <div class="table-responsive">
                                               <table class="table table-sm">
                                                       <thead>
                                                               <tr>
                                                                       <th>Descripción</th>
                                                                       <th>Inicio</th>
                                                                       <th>Fin</th>
                                                                       <th>Estado</th>
                                                                       <th>Completados</th>
                                                                       <th>Acciones</th>
                                                               </tr>
                                                       </thead>
                                                       <tbody id="lista-retos"></tbody>
                                               </table>
                                       </div>
                               </div>
                       </div>
                       <div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
                               <div class="modal-dialog modal-lg">
                                       <div class="modal-content">
                                               <div class="modal-header">
                                                       <h5 class="modal-title">Detalle de Reto</h5>
                                                       <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                               </div>
                                               <div class="modal-body">
                                                       <div class="table-responsive">
                                                               <table class="table table-sm">
                                                                       <thead><tr><th>Invitado</th><th>Código</th><th>Fecha</th></tr></thead>
                                                                       <tbody id="detalle-body"></tbody>
                                                               </table>
                                                       </div>
                                               </div>
                                       </div>
                               </div>
                       </div>
               </div>
       </div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const ctx = document.getElementById('dynamicChart').getContext('2d');
		const estadoEvento = '<?php echo $evento->estado; ?>';
		let chartData = (estadoEvento === 'En Curso' || estadoEvento === 'Finalizado') ? {
			labels: ['Asistentes', 'Ausentes'],
			datasets: [{
				data: [<?php echo $total_asistentes; ?>, <?php echo $total_invitados - $total_asistentes; ?>],
				backgroundColor: ['rgba(13, 110, 253, 0.7)', 'rgba(108, 117, 125, 0.7)'],
				borderColor: ['#0d6efd', '#6c757d']
			}]
		} : {
			labels: ['Confirmados', 'Rechazados', 'Pendientes de RSVP'],
			datasets: [{
				data: [<?php echo $total_confirmados; ?>, <?php echo $total_rechazados; ?>, <?php echo $total_pendientes_rsvp; ?>],
				backgroundColor: ['rgba(25, 135, 84, 0.7)', 'rgba(220, 53, 69, 0.7)', 'rgba(255, 193, 7, 0.7)'],
				borderColor: ['#198754', '#dc3545', '#ffc107']
			}]
		};
		new Chart(ctx, {
			type: 'doughnut',
			data: chartData,
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					}
				}
			}
		});

		const ctxAsistencia = document.getElementById('asistenciaChart').getContext('2d');
		const modoEvento = '<?php echo $evento->modo; ?>';
		let asistenciaLabels = [];
		let asistenciaData = [];
		let asistenciaColors = [];
		const totalRegistrados = <?php echo $total_registrados; ?>;
		const totalVirtual = <?php echo $asistencia_completa_virtual; ?>;
		const totalFisico = <?php echo $asistencia_completa_fisico; ?>;
		const totalIniciado = <?php echo $asistencia_iniciada; ?>;
		const totalPendiente = <?php echo $asistencia_pendiente; ?>;
		if (modoEvento === 'Hibrido') {
			asistenciaLabels = ['Virtual (3-FAV)', 'Presencial (Kiosco)', 'Proceso Iniciado', 'Pendientes por Registrar'];
			asistenciaData = [totalVirtual, totalFisico, totalIniciado, totalPendiente];
			asistenciaColors = ['#0d6efd', '#6f42c1', '#ffc107', '#dee2e6'];
		} else {
			asistenciaLabels = ['Asistencia Registrada', 'Proceso Iniciado', 'Pendientes por Registrar'];
			asistenciaData = [totalRegistrados, totalIniciado, totalPendiente];
			asistenciaColors = ['#198754', '#ffc107', '#dee2e6'];
		}
		const totalAsistenciaData = asistenciaData.reduce((a, b) => a + b, 0);
		if (totalAsistenciaData === 0) {
			document.getElementById('asistenciaChartContainer').innerHTML = '<div class="text-center text-muted p-4 d-flex align-items-center justify-content-center h-100"><div><i class="bi bi-bar-chart-line fs-1"></i><p class="mt-2">Los datos de asistencia aparecerán aquí cuando los invitados comiencen el proceso de registro.</p></div></div>';
		} else {
			new Chart(ctxAsistencia, {
				type: 'doughnut',
				data: {
					labels: asistenciaLabels,
					datasets: [{
						data: asistenciaData,
						backgroundColor: asistenciaColors,
						borderColor: '#ffffff',
						borderWidth: 2
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: false
						}
					}
				}
			});
		}

		<?php if ($evento->modo != 'Virtual' && !empty($evento->latitud)): ?>
			const lat = <?php echo $evento->latitud; ?>;
			const lng = <?php echo $evento->longitud; ?>;
			const map = L.map('map-gestionar', {
				scrollWheelZoom: false
			}).setView([lat, lng], 15);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
			L.marker([lat, lng]).addTo(map).bindPopup('<b><?php echo htmlspecialchars($evento->nombre_evento); ?></b>').openPopup();
		<?php endif; ?>

		const alertElement = document.getElementById('autoCloseAlert');
		if (alertElement) {
			setTimeout(function() {
				const bsAlert = new bootstrap.Alert(alertElement);
				bsAlert.close();
			}, 8000);
		}

		const invitadosData = <?php echo json_encode($invitados); ?> || [];
		const tbody = document.getElementById('invitados-tbody');
		const searchInput = document.getElementById('searchInput');

		const getStatusBadge = (invitado) => {
			if (invitado.asistencia_verificada) {
				return `<span class="badge bg-primary"><i class="bi bi-check-circle-fill me-1"></i> Asistió</span>`;
			}
			if (invitado.estado_rsvp == 'Confirmado') {
				return `<span class="badge bg-success"><i class="bi bi-hand-thumbs-up-fill me-1"></i> Confirmado</span>`;
			}
			if (invitado.estado_rsvp == 'Rechazado') {
				return `<span class="badge bg-danger"><i class="bi bi-hand-thumbs-down-fill me-1"></i> Rechazado</span>`;
			}
			if (invitado.estado_rsvp == 'Pendiente' && invitado.fecha_invitacion) {
				return `<span class="badge bg-info"><i class="bi bi-send-check-fill me-1"></i> Enviada</span>`;
			}
			if (invitado.estado_rsvp == 'Pendiente' && !invitado.fecha_invitacion) {
				return `<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> Pendiente de Envío</span>`;
			}
			return '';
		};

		const renderTable = (filter) => {
			tbody.innerHTML = '';

			if (!invitadosData || invitadosData.length === 0) {
				tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Aún no has agregado invitados a este evento.</td></tr>`;
				return;
			}

			const searchTerm = filter.toLowerCase();
			const filteredData = invitadosData.filter(invitado => {
				const nombre = (invitado.nombre || '').toLowerCase();
				const email = (invitado.email || '').toLowerCase();
				return nombre.includes(searchTerm) || email.includes(searchTerm);
			});

			if (filteredData.length === 0) {
				tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">No se encontraron invitados con ese término de búsqueda.</td></tr>`;
				return;
			}

			filteredData.forEach(invitado => {
				const tr = document.createElement('tr');

				const nombre = invitado.nombre || 'Nombre no asignado';
				const urlReenviar = `<?php echo URL_PATH; ?>invitacion/reenviar/${invitado.id_invitacion}`;
				const urlDesinvitar = `<?php echo URL_PATH; ?>invitacion/desinvitar/<?php echo $evento->id; ?>/${invitado.id_invitacion}`;

				tr.innerHTML = `
					<td class="ps-3"><strong>${nombre}</strong></td>
					<td>${invitado.email}</td>
					<td>${getStatusBadge(invitado)}</td>
					<td class="text-center">
						<a href="${urlReenviar}" class="btn btn-sm btn-outline-primary" title="Reenviar Invitación"><i class="bi bi-send-arrow-up-fill"></i></a>
						<a href="${urlDesinvitar}" class="btn btn-sm btn-outline-danger ms-1" title="Eliminar Invitación"><i class="bi bi-x-lg"></i></a>
					</td>
				`;

				const deleteButton = tr.querySelector('a[title="Eliminar Invitación"]');
				deleteButton.addEventListener('click', (event) => {
					event.preventDefault();
					if (confirm(`¿Estás seguro de que deseas eliminar la invitación para ${nombre}?`)) {
						window.location.href = deleteButton.href;
					}
				});

				tbody.appendChild(tr);
			});
		};

		searchInput.addEventListener('input', (event) => {
			renderTable(event.target.value);
		});

		renderTable('');
	});
</script>

<script>
	const URL_PATH = '<?php echo URL_PATH; ?>';
</script>
<script src="<?php echo URL_PATH; ?>core/customassets/js/country-codes.js"></script>
<script>
const URL_BASE = '<?php echo URL_PATH; ?>';
const ID_EVENTO = <?php echo (int)$evento->id; ?>;
</script>
<script src="<?php echo URL_PATH; ?>core/customassets/js/retos_admin.js"></script>