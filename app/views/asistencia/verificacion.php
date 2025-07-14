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
		padding-top: 75%;
		position: relative;
		background-color: #000;
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

		<div x-data="verificacionHandler()">
			<div id="qr-reader-container" class="mb-3" x-show="estado === 'inicial' || estado === 'escaneando'">
				<div id="qr-reader">
					<div id="qr-reader-placeholder" x-show="estado === 'inicial'">
						<div class="text-center">
							<div class="spinner-border spinner-border-sm mb-2" role="status"></div>
							<div>Iniciando cámara...</div>
						</div>
					</div>
				</div>
			</div>

			<div x-show="estado === 'procesando'">
				<div class="alert alert-info text-center p-4">
					<div class="spinner-border mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
					<h4 class="alert-heading">Verificando...</h4>
					<p class="mb-0">Estamos validando tu asistencia. Por favor, espera un momento.</p>
				</div>
			</div>

			<div x-show="estado === 'error'">
				<div class="alert alert-danger text-center p-4">
					<i class="bi bi-x-circle-fill status-icon"></i>
					<h4 class="alert-heading mt-2">Error en la Verificación</h4>
					<p class="mb-0" x-text="mensaje"></p>
					<button @click="reiniciar()" class="btn btn-danger mt-3">Intentar de Nuevo</button>
				</div>
			</div>
		</div>

	</div>
</div>

<script>
	function verificacionHandler() {
		return {
			estado: 'inicial', // inicial, escaneando, procesando, error
			mensaje: '',
			coordenadas: null,
			html5QrCode: null,

			init() {
				if ('<?php echo $evento->modo; ?>' === 'Virtual') {
					this.iniciarScanner();
				} else {
					this.obtenerGPS();
				}
			},

			obtenerGPS() {
				// ... (sin cambios)
			},

			iniciarScanner() {
				const qrPlaceholder = document.getElementById('qr-reader-placeholder');
				this.estado = 'escaneando'; // Actualiza el estado

				this.html5QrCode = new Html5Qrcode("qr-reader");
				// ... (el resto de la función sin cambios)
			},

			async procesarVerificacion(tokenDinamico) {
				this.estado = 'procesando'; // **AQUÍ SE MUESTRA EL MENSAJE DE "PROCESANDO"**
				// ... (el resto de la función sin cambios)
			},

			mostrarError(msg) {
				this.estado = 'error';
				this.mensaje = msg;
				if (this.html5QrCode && this.html5QrCode.isScanning) {
					this.html5QrCode.stop().catch(err => console.error("Error al detener el escáner.", err));
				}
			},

			reiniciar() {
				window.location.reload();
			}
		}
	}

	// Re-estructuramos el script para que sea más legible
	document.addEventListener('alpine:init', () => {
		Alpine.data('verificacionHandler', verificacionHandler);
	});
</script>