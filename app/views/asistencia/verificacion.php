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

	/* NUEVO: Estilos para el panel de depuración */
	#debug-panel {
		text-align: left;
		font-size: 0.8rem;
		border: 1px solid #ccc;
		background-color: #f8f9fa;
	}

	#debug-panel h5 {
		font-size: 1rem;
	}
</style>

<div class="container container-main d-flex align-items-center" x-data="verificacionHandler()" x-init="init()">
	<div class="w-100" style="max-width: 600px; margin: auto;">

		<div class="text-center mb-4">
			<h1 class="h2 mb-3">Registrar Asistencia</h1>
			<p class="lead text-muted">Apunta la cámara al código QR que se muestra en la pantalla del evento.</p>
		</div>

		<div>
			<div id="qr-reader-container" class="mb-3" x-show="estado === 'escaneando'">
				<div id="qr-reader">
					<div id="qr-reader-placeholder">
						<div class="text-center">
							<div class="spinner-border spinner-border-sm mb-2" role="status"></div>
							<div>Iniciando cámara...</div>
						</div>
					</div>
				</div>
			</div>

			<div id="processing-state" x-show="estado === 'procesando'">
				<div class="alert alert-info text-center p-4">
					<div class="spinner-border mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
					<h4 class="alert-heading">Verificando...</h4>
					<p class="mb-0">Estamos validando tu asistencia. Por favor, espera un momento.</p>
				</div>
			</div>

			<div id="error-state" x-show="estado === 'error'">
				<div class="alert alert-danger text-center p-4">
					<i class="bi bi-x-circle-fill status-icon"></i>
					<h4 class="alert-heading mt-2">Error en la Verificación</h4>
					<p class="mb-0" x-text="mensaje"></p>
					<button @click="reiniciar()" class="btn btn-danger mt-3">Intentar de Nuevo</button>
				</div>

				<div id="debug-panel" class="p-3 rounded mt-3" style="display: none;">
					<h5><i class="bi bi-bug-fill"></i> Información de Depuración</h5>
					<ul class="list-unstyled mb-0">
						<li><strong>Hora Servidor (PHP):</strong> <code id="debug-php-time"></code></li>
						<li><strong>Hora Base de Datos (DB):</strong> <code id="debug-db-time"></code></li>
						<li><strong>Hora Expiración Token:</strong> <code id="debug-token-time"></code></li>
						<li class="mt-2"><strong>Resultado Comparación:</strong> <span id="debug-resultado"></span></li>
					</ul>
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
				this.estado = 'escaneando';
				this.iniciarScanner();
			},

			iniciarScanner() {
				const qrPlaceholder = document.getElementById('qr-reader-placeholder');

				this.html5QrCode = new Html5Qrcode("qr-reader");
				const config = {
					fps: 10,
					qrbox: {
						width: 250,
						height: 250
					}
				};

				this.html5QrCode.start({
						facingMode: "environment"
					}, config,
					(decodedText, decodedResult) => {
						// Evita escanear el mismo código múltiples veces seguidas
						if (this.estado === 'procesando') return;
						this.html5QrCode.pause();
						this.procesarVerificacion(decodedText);
					},
					(errorMessage) => {
						/* ignora errores de "QR no encontrado" */ }
				).catch(err => {
					this.mostrarError("No se pudo iniciar la cámara. Asegúrate de dar los permisos necesarios.");
				});
			},

			async procesarVerificacion(tokenDinamico) {
				this.estado = 'procesando';

				const formData = new FormData();
				formData.append('token_acceso', '<?php echo $invitacion->token_acceso; ?>');
				formData.append('token_dinamico', tokenDinamico);

				try {
					const response = await fetch('<?php echo URL_PATH; ?>asistencia/procesarVerificacion', {
						method: 'POST',
						body: formData
					});

					const data = await response.json();

					if (data.exito) {
						if (data.siguiente_paso) {
							window.location.href = data.siguiente_paso;
						} else if (data.completado) {
							window.location.reload();
						}
					} else {
						this.mostrarError(data.mensaje, data.debug_info || null);
					}

				} catch (error) {
					this.mostrarError('Error de conexión con el servidor.');
				}
			},

			mostrarError(msg, debugInfo = null) {
				this.estado = 'error';
				this.mensaje = msg;

				if (this.html5QrCode && this.html5QrCode.isScanning) {
					this.html5QrCode.stop().catch(err => console.error("Error al detener el escáner.", err));
				}

				if (debugInfo) {
					const panel = document.getElementById('debug-panel');
					document.getElementById('debug-php-time').textContent = debugInfo.hora_actual_servidor_php;
					document.getElementById('debug-db-time').textContent = debugInfo.hora_actual_base_de_datos;
					document.getElementById('debug-token-time').textContent = debugInfo.hora_expiracion_del_token;

					const expDate = new Date(debugInfo.hora_expiracion_del_token);
					const dbDate = new Date(debugInfo.hora_actual_base_de_datos);

					if (isNaN(expDate.getTime()) || isNaN(dbDate.getTime())) {
						document.getElementById('debug-resultado').innerHTML = '<span class="badge bg-warning">No se pudo comparar</span>';
					} else if (expDate > dbDate) {
						document.getElementById('debug-resultado').innerHTML = '<span class="badge bg-success">VÁLIDO LÓGICAMENTE</span>';
					} else {
						document.getElementById('debug-resultado').innerHTML = '<span class="badge bg-danger">INVÁLIDO</span> (Expiración <= Hora DB)';
					}

					panel.style.display = 'block';
				}
			},

			reiniciar() {
				window.location.reload();
			}
		}
	}
</script>