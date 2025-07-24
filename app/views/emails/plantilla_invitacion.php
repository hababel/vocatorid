<!DOCTYPE html>
<html>

<head>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
			margin: 0;
			padding: 20px;
			background-color: #f2f2f7;
		}

		.email-container {
			max-width: 600px;
			margin: auto;
			background-color: #ffffff;
		}

		.header {
			padding: 40px 20px;
			text-align: center;
		}

		.header h1 {
			margin: 0;
			font-size: 28px;
			color: #1c1c1e;
		}

		.header .event-name {
			margin: 10px 0 0;
			color: #007aff;
			font-size: 20px;
			font-weight: 600;
		}

		.content {
			padding: 10px 20px 30px;
		}

		.action-card {
			margin-bottom: 20px;
			padding: 25px;
			border: 1px solid #e5e5e5;
			border-radius: 12px;
			text-align: center;
		}

		.icon {
			font-size: 40px;
			line-height: 1;
			margin-bottom: 15px;
		}

		.card-title {
			font-size: 22px;
			font-weight: 600;
			margin: 0 0 10px;
			color: #1c1c1e;
		}

		.card-text {
			font-size: 16px;
			color: #3c3c43;
			line-height: 1.6;
			margin: 0 0 20px;
		}

		.button {
			padding: 14px 28px;
			text-decoration: none;
			border-radius: 8px;
			display: inline-block;
			font-weight: 600;
			font-size: 16px;
			text-align: center;
			border: none;
		}

		.qr-code {
			margin-top: 20px;
		}

		.footer {
			padding: 30px;
			text-align: center;
			font-size: 12px;
			color: #8e8e93;
			background-color: #f8f8f8;
			border-top: 1px solid #e5e5e5;
		}

		.footer p {
			margin: 5px 0;
		}

		.footer a {
			color: #007aff;
			text-decoration: none;
		}
	</style>
</head>

<body>
	<div class="email-container">
		<div class="header">
			<h1>Tu Invitación</h1>
			<p class="event-name"><?php echo htmlspecialchars($nombre_evento); ?></p>
		</div>
		<div class="content">
			<p style="font-size: 18px; color: #1c1c1e; text-align: center; margin-bottom: 30px;">Hola <strong><?php echo htmlspecialchars($nombre_invitado); ?></strong>, ¡nos encantaría que nos acompañaras!</p>

			<div class="action-card" style="background-color: #eaf7ed;">
				<div class="icon">🤔</div>
				<h2 class="card-title">¿Puedes Asistir?</h2>
				<p class="card-text">Por favor, haz clic en uno de los botones para confirmar. Esto es muy importante para la organización.</p>
				<a href="<?php echo $enlace_confirmar; ?>" target="_blank" class="button" style="background-color: #34c759; color: white; margin-right: 10px;">✅ Sí, confirmo</a>
				<a href="<?php echo $enlace_rechazar; ?>" target="_blank" class="button" style="background-color: #ff3b30; color: white;">❌ No puedo</a>
			</div>

			<div class="action-card" style="background-color: #eaf2ff;">
				<div class="icon">🎟️</div>
				<h2 class="card-title">Tu Acceso al Evento</h2>
				<p class="card-text">Guarda este correo. El día del evento, úsalo para registrar tu asistencia de una de estas dos formas:</p>
				<p class="card-text" style="background-color: #ffffff; padding: 15px; border-radius: 8px;"><strong>Opción 1 (Presencial):</strong> Muestra este código QR en la entrada.<br>
					<img src="<?php echo $qr_code_url; ?>" alt="Tu Código QR Personal" style="max-width: 150px;" class="qr-code">
				</p>
				<p class="card-text" style="background-color: #ffffff; padding: 15px; border-radius: 8px;"><strong>Opción 2 (Virtual):</strong> Haz clic en el botón de abajo.<br><br>
					<a href="<?php echo $enlace_bienvenida; ?>" target="_blank" class="button" style="background-color: #007aff; color: white;" >➡️ Iniciar Registro Virtual</a>
				</p>
			</div>

			<div class="action-card" style="background-color: #fff9e6;">
				<div class="icon">ℹ️</div>
				<h2 class="card-title">¿Quieres Saber Más?</h2>
				<p class="card-text">Consulta la agenda, ubicación y otros detalles en el sitio web del evento.</p>
				<a href="<?php echo $enlace_micrositio; ?>" target="_blank" class="button" style="background-color: #e5e5e5; color: #1c1c1e;">Ver Detalles del Evento</a>
			</div>
		</div>

		<div class="footer">
			<p><strong><?php echo NOMBRE_SITIO; ?></strong></p>
			<p>Este correo fue enviado para el evento '<?php echo htmlspecialchars($nombre_evento); ?>'.</p>
			<p>Si tienes alguna pregunta, contacta al organizador o responde a <a href="mailto:<?php echo CORREO_CONTACTO_EMPRESA_APP; ?>"><?php echo CORREO_CONTACTO_EMPRESA_APP; ?></a>.</p>
			<p>&copy; <?php echo date('Y'); ?> <?php echo NOMBRE_EMPRESA_APP; ?>. Todos los derechos reservados.</p>
		</div>
	</div>
</body>

</html>