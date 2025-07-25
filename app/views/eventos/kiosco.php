<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<style>
	html,
	body {
		height: 100%;
		margin: 0;
		padding: 0;
		overflow: hidden;
	}

	body {
		background: #1c1c1c;
		color: #f8f9fa;
		display: flex;
		justify-content: center;
		align-items: center;
		text-align: center;
		font-family: 'Poppins', sans-serif;
	}

	.background-gradient {
		position: absolute;
		width: 100%;
		height: 100%;
		background: linear-gradient(45deg, #0d6efd, #6f42c1, #d63384);
		background-size: 400% 400%;
		animation: gradientAnimation 15s ease infinite;
		filter: blur(150px);
		z-index: -1;
	}

	@keyframes gradientAnimation {
		0% {
			background-position: 0% 50%;
		}

		50% {
			background-position: 100% 50%;
		}

		100% {
			background-position: 0% 50%;
		}
	}

	.kiosco-container {
		width: 100%;
		max-width: 500px;
		padding: 2rem;
	}

	.card-kiosco {
		background-color: rgba(33, 37, 41, 0.75);
		border-radius: 1.5rem;
		padding: 2.5rem;
		border: 1px solid rgba(255, 255, 255, 0.1);
		box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
		backdrop-filter: blur(15px);
		-webkit-backdrop-filter: blur(15px);
	}

	#qrcode {
		width: 250px;
		height: 250px;
		margin: 1.5rem auto;
		background-color: white;
		border-radius: 1rem;
		padding: 1rem;
		box-sizing: border-box;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	#qrcode>* {
		max-width: 100%;
		height: auto;
	}

	.codigo-texto {
		font-family: 'Share Tech Mono', monospace;
		font-size: 4.5rem;
		font-weight: 400;
		letter-spacing: 0.5rem;
		color: #fff;
		line-height: 1;
		margin-top: 1rem;
		text-shadow: 0 0 10px rgba(13, 202, 240, 0.5);
	}

	.progress-container {
		display: flex;
		align-items: center;
		gap: 15px;
		margin-top: 2rem;
	}

	.progress {
		flex-grow: 1;
		height: 8px;
		background-color: rgba(255, 255, 255, 0.1);
		border-radius: 8px;
		overflow: hidden;
	}

	/* Esta clase ahora se aplica al div interior con id="progress-bar" */
	#progress-bar {
		transition: background-color 0.5s ease, width 0.2s ease !important;
		border-radius: 8px;
	}

	.progress-bar-white {
		background-color: #f8f9fa;
	}

	.progress-bar-yellow {
		background-color: #ffc107;
	}


	#minutos-restantes {
		font-family: 'Share Tech Mono', monospace;
		font-size: 1.2rem;
		color: #adb5bd;
		flex-shrink: 0;
	}

	@keyframes blink-animation {
		50% {
			opacity: 0.2;
		}
	}

	.blinking-bar {
		animation: blink-animation 1s infinite;
	}

	.event-title {
		font-weight: 700;
		color: #ffffff;
	}

	.event-details {
		color: #e9ecef;
		font-weight: 300;
		border-top: 1px solid rgba(255, 255, 255, 0.1);
		padding-top: 1rem;
		margin-top: 1rem;
	}

	.text-muted {
		color: #adb5bd !important;
	}
</style>
</head>

<body>

	<div class="background-gradient"></div>

	<div class="kiosco-container">
		<div class="card-kiosco">
			<h1 class="event-title h2"><?php echo htmlspecialchars($evento->nombre_evento); ?></h1>

			<div id="qrcode"></div>

			<p class="text-muted mt-4">O ingresa el código:</p>
			<div id="codigo-texto" class="codigo-texto">CARGANDO...</div>

			<div class="progress-container">
				<div class="progress">
					<div id="progress-bar" class="progress" role="progressbar" style="width: 100%"></div>
				</div>
				<div id="minutos-restantes">--:--</div>
			</div>

			<div class="event-details mt-3">
				<small><i class="bi bi-calendar-event me-2"></i><?php echo date('d/m/Y', strtotime($evento->fecha_evento)); ?></small>
				<?php if (!empty($evento->nombre_instructor)): ?>
					<small><i class="bi bi-person-video3 me-2"></i><?php echo htmlspecialchars($evento->nombre_instructor); ?></small>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const qrContainer = document.getElementById('qrcode');
			const codigoTextoElem = document.getElementById('codigo-texto');
			const progressBar = document.getElementById('progress-bar');
			const minutosRestantesElem = document.getElementById('minutos-restantes');
			let countdownInterval = null;

			function generarQrCode(texto) {
				if (qrContainer) {
					qrContainer.innerHTML = '';
					new QRCode(qrContainer, {
						text: texto,
						width: 220,
						height: 220,
						colorDark: '#000000',
						colorLight: '#ffffff',
						correctLevel: QRCode.CorrectLevel.H
					});
				}
			}

			function iniciarContador(duracion) {
				clearInterval(countdownInterval);
				progressBar.classList.remove('blinking-bar', 'progress-bar-white', 'progress-bar-yellow');

				let segundosRestantes = duracion;

				function actualizarVista() {
					const minutos = Math.floor(segundosRestantes / 60);
					const segundos = segundosRestantes % 60;
					minutosRestantesElem.textContent = `${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;

					const progreso = (segundosRestantes / duracion) * 100;
					progressBar.style.width = progreso + '%';

					const bloqueActual = Math.floor((duracion - segundosRestantes) / 5);
					if (bloqueActual % 2 === 0) {
						progressBar.classList.remove('progress-bar-yellow');
						progressBar.classList.add('progress-bar-white');
					} else {
						progressBar.classList.remove('progress-bar-white');
						progressBar.classList.add('progress-bar-yellow');
					}

					// CORRECCIÓN: Titilar a los 10 segundos
					if (segundosRestantes <= 10) {
						progressBar.classList.add('blinking-bar');
					} else {
						progressBar.classList.remove('blinking-bar');
					}
				}

				actualizarVista();

				countdownInterval = setInterval(() => {
					segundosRestantes--;
					actualizarVista();

					if (segundosRestantes < 0) {
						clearInterval(countdownInterval);
						actualizarToken();
					}
				}, 1000);
			}

			async function actualizarToken() {
				try {
					const response = await fetch('<?php echo URL_PATH; ?>evento/generarTokenKiosco/<?php echo $evento->id; ?>');
					if (!response.ok) throw new Error('Error de red');

					const data = await response.json();

					if (data.exito && data.token) {
						codigoTextoElem.textContent = data.token;
						generarQrCode(data.token);
						iniciarContador(data.expira_en);
					} else {
						codigoTextoElem.textContent = 'ERROR';
						qrContainer.innerHTML = '';
					}
				} catch (error) {
					console.error('Error al obtener el token:', error);
					codigoTextoElem.textContent = 'ERROR';
					qrContainer.innerHTML = '';
				}
			}

			actualizarToken();
		});
	</script>

</body>

</html>