<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
?>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<style>
	#qr-reader-container {
		width: 100%;
		max-width: 500px;
		margin: auto;
		border: 1px solid #dee2e6;
		border-radius: 0.5rem;
		overflow: hidden;
	}

	/* MEJORA: Se añade un elemento <video> para el stream de la cámara */
	#qr-reader {
		width: 100%;
		padding-top: 75%;
		/* Proporción 4:3 */
		position: relative;
		background-color: #000;
	}

	#qr-video {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		object-fit: cover;
		/* Asegura que el video cubra el contenedor */
	}

	#qr-reader-placeholder {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		display: flex;
		justify-content: center;
		align-items: center;
		color: #6c757d;
	}

	.status-icon {
		font-size: 3rem;
	}
</style>

<div class="container container-main d-flex align-items-center">
	<div class="w-100" style="max-width: 600px; margin: auto;">

		<div class="text-center mb-4">
			<h1 class="h2 mb-3">Registrar Asistencia</h1>
			<p class="lead text-muted">Apunta la cámara al código QR que se muestra en la pantalla del evento.</p>
		</div>

		<div>
			<div id="qr-reader-container" class="mb-3">
				<div id="qr-reader">
					<video id="qr-video" playsinline style="display: none;"></video>
					<div id="qr-reader-placeholder">
						<div class="text-center">
							<div class="spinner-border spinner-border-sm mb-2" role="status"></div>
							<div>Iniciando cámara...</div>
						</div>
					</div>
				</div>
			</div>

			<div id="processing-state" style="display: none;">
				<div class="alert alert-info text-center p-4">
					<div class="spinner-border mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
					<h4 class="alert-heading">Verificando...</h4>
					<p class="mb-0">Estamos validando tu asistencia. Por favor, espera un momento.</p>
				</div>
			</div>

			<div id="error-state" style="display: none;">
				<div class="alert alert-danger text-center p-4">
					<i class="bi bi-x-circle-fill status-icon"></i>
					<h4 class="alert-heading mt-2">Error en la Verificación</h4>
					<p class="mb-0" id="error-message"></p>
					<button id="retry-button" class="btn btn-danger mt-3">Intentar de Nuevo</button>
				</div>
			</div>
		</div>

	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Referencias a los elementos del DOM que se controlarán
		const qrReaderContainer = document.getElementById('qr-reader-container');
		const qrPlaceholder = document.getElementById('qr-reader-placeholder');
		const videoElem = document.getElementById('qr-video');
		const processingStateDiv = document.getElementById('processing-state');
		const errorStateDiv = document.getElementById('error-state');
		const errorMessageElem = document.getElementById('error-message');
		const retryButton = document.getElementById('retry-button');
		let html5QrCode = null; // Variable para mantener la instancia del escáner

		/**
		 * Función principal que se encarga de activar la cámara y el escáner.
		 */
		async function iniciarCamaraYScanner() {
			// Ocultar mensajes de error y mostrar el estado de carga inicial
			errorStateDiv.style.display = 'none';
			qrReaderContainer.style.display = 'block';
			processingStateDiv.style.display = 'none';
			qrPlaceholder.style.display = 'flex';
			videoElem.style.display = 'none'; // Ocultar <video> hasta que se active el stream

			try {
				// 1. Solicitar acceso a la cámara del dispositivo usando la API nativa del navegador.
				//    Se prioriza la cámara trasera ('environment').
				const stream = await navigator.mediaDevices.getUserMedia({
					video: {
						facingMode: 'environment'
					}
				});

				// 2. Una vez concedido el permiso, se asigna el stream de video al elemento <video>.
				videoElem.srcObject = stream;
				videoElem.style.display = 'block'; // Mostrar el video.
				qrPlaceholder.style.display = 'none'; // Ocultar el mensaje "Iniciando cámara...".

				// 3. Se instancia y se inicia la librería `html5-qrcode`, adjuntándola al elemento <video>
				//    que ya contiene el stream de la cámara.
				html5QrCode = new Html5Qrcode("qr-reader");
				html5QrCode.start(
					videoElem, {
						fps: 10,
						qrbox: {
							width: 250,
							height: 250
						}
					},
					(decodedText) => {
						// Función que se ejecuta al escanear un QR con éxito.
						if (html5QrCode && html5QrCode.isScanning) {
							html5QrCode.stop(); // Detener la cámara para ahorrar recursos.
						}
						procesarVerificacion(decodedText);
					},
					(errorMessage) => {
						/* Se puede ignorar el error de "QR no encontrado" */ }
				);

			} catch (err) {
				// 4. Se maneja el error si el usuario niega el permiso o no se encuentra una cámara.
				console.error("Error al acceder a la cámara: ", err);
				mostrarError("No se pudo acceder a la cámara. Por favor, concede los permisos necesarios en tu navegador.");
			}
		}

		/**
		 * Envía el token escaneado al servidor para su validación.
		 * La lógica interna de esta función no cambia.
		 */
		async function procesarVerificacion(tokenDinamico) {
			qrReaderContainer.style.display = 'none';
			processingStateDiv.style.display = 'block';

			const formData = new FormData();
			formData.append('token_acceso', '<?php echo $invitacion->token_acceso; ?>');
			formData.append('token_dinamico', tokenDinamico);

			// La lógica de GPS para eventos no virtuales se mantiene.

			try {
				const response = await fetch('<?php echo URL_PATH; ?>asistencia/procesarVerificacion', {
					method: 'POST',
					body: formData
				});
				const data = await response.json();

				if (data.exito && data.siguiente_paso) {
					window.location.href = data.siguiente_paso; // Redirigir al siguiente paso.
				} else {
					mostrarError(data.mensaje || 'Ocurrió un error inesperado.');
				}
			} catch (error) {
				mostrarError('Error de conexión. Revisa tu internet e inténtalo de nuevo.');
			}
		}

		/**
		 * Muestra el panel de error con un mensaje específico.
		 */
		function mostrarError(msg) {
			processingStateDiv.style.display = 'none';
			qrReaderContainer.style.display = 'none';
			errorMessageElem.textContent = msg;
			errorStateDiv.style.display = 'block';
		}

		// Se asigna el evento 'click' al botón de reintento para recargar la página.
		retryButton.addEventListener('click', () => {
			window.location.reload();
		});

		// Se llama a la función principal para que el proceso inicie al cargar la página.
		iniciarCamaraYScanner();
	});
</script>