<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- El título ahora es dinámico. El controlador pasará el título en el array $datos. -->
	<title><?php echo isset($datos['titulo']) ? $datos['titulo'] . ' - ' . NOMBRE_SITIO : NOMBRE_SITIO; ?></title>

	<!-- Dependencias CSS y JS comunes a todo el sitio -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

	<!-- Estilos base que se aplicarán a todas las páginas -->
	<style>
		body {
			background-color: #f8f9fa;
			display: flex;
			flex-direction: column;
			min-height: 100vh;
		}

		.container-main {
			flex: 1;
		}

		.card {
			border: none;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
			border-radius: 0.75rem;
		}
	</style>
</head>

<body>