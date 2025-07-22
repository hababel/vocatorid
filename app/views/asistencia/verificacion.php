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

	#qr-reader {
		width: 100%;
		position: relative;
		background-color: #000;
		border-radius: 0.5rem;
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
			<div id="scanner-view" class="mb-3">
				<div id="qr-reader-container">
					<div id="qr-reader">
						<div id="qr-reader-placeholder">
							<div class="text-center">
								<div class="spinner-border spinner-border-sm mb-2" role="status"></div>
								<div>Iniciando cámara...</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="processing-view" style="display: none;">
				<div class="alert alert-info text-center p-4">
					<div class="spinner-border mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
					<h4 class="alert-heading">Verificando...</h4>
					<p class="mb-0">Estamos validando tu asistencia. Por favor, espera un momento.</p>
				</div>
			</div>

			<div id="error-view" style="display: none;">
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
		const scannerView = document.getElementById('scanner-view');
		const processingView = document.getElementById('processing-view');
		const errorView = document.getElementById('error-view');
		const errorMessageElem = document.getElementById('error-message');
		const retryButton = document.getElementById('retry-button');

		let html5QrCode;
		let isProcessing = false;

		function setView(viewName) {
			scannerView.style.display = 'none';
			processingView.style.display = 'none';
			errorView.style.display = 'none';
			document.getElementById(viewName).style.display = 'block';
		}

		function handleError(message) {
			setView('error-view');
			errorMessageElem.textContent = message;
			if (html5QrCode && html5QrCode.isScanning) {
				html5QrCode.stop();
			}
		}

		async function onScanSuccess(decodedText, decodedResult) {
			if (isProcessing) return;
			isProcessing = true;

			if (html5QrCode.isScanning) {
				await html5QrCode.stop();
			}

			setView('processing-view');

			const formData = new FormData();
			formData.append('token_acceso', '<?php echo $invitacion->token_acceso; ?>');
			formData.append('token_dinamico', decodedText);

			try {
				const response = await fetch('<?php echo URL_PATH; ?>asistencia/procesarVerificacion', {
					method: 'POST',
					body: formData
				});
				const data = await response.json();

				if (data.exito) {
					if (data.siguiente_paso) window.location.href = data.siguiente_paso;
					else if (data.completado) window.location.reload();
				} else {
					handleError(data.mensaje);
				}
			} catch (error) {
				handleError('Error de conexión. Revisa tu conexión a internet.');
			} finally {
				isProcessing = false;
			}
		}

		function startScanner() {
			setView('scanner-view');
			html5QrCode = new Html5Qrcode("qr-reader");
			const config = {
				fps: 10,
				qrbox: {
					width: 250,
					height: 250
				},
				supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
			};

			html5QrCode.start({
					facingMode: "environment"
				}, config, onScanSuccess)
				.catch(err => {
					handleError("No se pudo iniciar la cámara. Por favor, otorga los permisos necesarios y recarga la página.");
				});
		}

		retryButton.addEventListener('click', () => {
			window.location.reload();
		});

		startScanner();
	});
</script>