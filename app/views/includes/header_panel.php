<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo isset($datos['titulo']) ? $datos['titulo'] . ' - ' . NOMBRE_SITIO : NOMBRE_SITIO; ?></title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

	<style>
		body {
			background-color: #f8f9fa;
		}
	</style>
</head>

<body>

	<!-- ====================================================== -->
	<!-- AQUÍ EMPIEZA EL MENÚ SUPERIOR DE NAVEGACIÓN -->
	<!-- ====================================================== -->
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
		<div class="container-fluid px-md-4">
			<a class="navbar-brand" href="<?php echo URL_PATH; ?>organizador/panel"><?php echo NOMBRE_SITIO; ?></a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNavDropdown">
				<ul class="navbar-nav me-auto mb-2 mb-lg-0">
					<li class="nav-item">
						<a class="nav-link active" aria-current="page" href="<?php echo URL_PATH; ?>organizador/panel">
							<i class="bi bi-calendar-event me-1"></i>Mis Eventos
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo URL_PATH; ?>contacto">
							<i class="bi bi-person-lines-fill me-1"></i>Mis Contactos
						</a>
					</li>
				</ul>
				<ul class="navbar-nav">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-person-circle me-1"></i> Bienvenido, <?php echo htmlspecialchars($datos['nombre']); ?>
						</a>
						<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
							<li><a class="dropdown-item" href="#">Mi Perfil</a></li>
							<li>
								<hr class="dropdown-divider">
							</li>
							<li>
								<a class="dropdown-item text-danger" href="<?php echo URL_PATH; ?>organizador/logout">
									<i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
								</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<!-- ====================================================== -->
	<!-- AQUÍ TERMINA EL MENÚ SUPERIOR DE NAVEGACIÓN -->
	<!-- ====================================================== -->

	<!-- El contenido principal (panel/index.php) se insertará aquí -->
	<main class="container-fluid">