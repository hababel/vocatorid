<?php
// El header_panel.php se carga automáticamente.
$evento = $datos['evento'];
?>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tone/14.7.77/Tone.js"></script>

<style>
	html,
	body {
		height: 100%;
		overflow: hidden;
		background-color: #f0f2f5;
	}

	.main-container {
		height: calc(100vh - 56px);
		display: flex;
		flex-direction: column;
		padding: 1.5rem;
	}

	.scanner-content-wrapper {
		flex-grow: 1;
		display: flex;
		justify-content: center;
		align-items: center;
		width: 100%;
		min-height: 0;
	}

	.scanner-content {
		width: 100%;
		max-width: 80%;
		display: flex;
		gap: 1.5rem;
		height: 95%;
		max-height: 700px;
	}

	.column-scanner,
	.column-status {
		background-color: #ffffff;
		border-radius: 1rem;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
		display: flex;
		flex-direction: column;
		padding: 1.5rem;
	}

	.column-scanner {
		flex: 3;
	}

	.column-status {
		flex: 2;
	}

	#qr-reader-container {
		width: 100%;
		flex-grow: 1;
		position: relative;
		border-radius: 0.5rem;
		overflow: hidden;
		border: 1px solid #dee2e6;
		min-height: 250px;
		display: flex;
		justify-content: center;
		align-items: center;
		background-color: #000;
	}

	#qr-reader {
		width: 100%;
		height: 100%;
	}

	.instructions {
		background-color: #e9ecef;
		border-radius: .5rem;
		padding: 0.75rem 1rem;
		text-align: left;
		font-size: 0.8rem;
		flex-shrink: 0;
		margin-bottom: 1rem;
	}

	.instructions ol {
		padding-left: 1.1rem;
		margin-bottom: 0;
	}

	.status-panel {
		flex-grow: 1;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		text-align: center;
	}

	.alert-container {
		width: 100%;
		position: relative;
		overflow: hidden;
	}

	.alert-container .row {
		align-items: center;
	}

	.alert-container .icon-col {
		flex: 0 0 40%;
		max-width: 40%;
		font-size: 4rem;
	}

	.alert-container .text-col {
		flex: 0 0 60%;
		max-width: 60%;
		text-align: left;
	}

	.alert-container h4 {
		margin-bottom: 0.25rem;
		font-size: 1.25rem;
	}

	.alert-container p {
		margin-bottom: 0;
		font-size: 1rem;
	}

	.alert-progress {
		position: absolute;
		bottom: 0;
		left: 0;
		height: 5px;
		background-color: rgba(0, 0, 0, 0.2);
		width: 100%;
		animation: progress-countdown 6s linear forwards;
	}

	@keyframes progress-countdown {
		from {
			width: 100%;
		}

		to {
			width: 0%;
		}
	}

	#ultimos-registros-container {
		border-top: 1px solid #dee2e6;
		padding-top: 1rem;
		margin-top: 1.5rem;
		flex-shrink: 0;
	}

	.location-status-card {
		border-left: 5px solid;
	}
</style>

<div class="container-fluid main-container">
	<div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-3" style="max-width: 80%; margin: 0 auto;">
		<h1 class="h4 mb-0">Kiosco Físico: <span class="text-primary"><?php echo htmlspecialchars($evento->nombre_evento); ?></span></h1>
		<a href="<?php echo URL_PATH; ?>organizador/panel" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
	</div>

	<div class="scanner-content-wrapper">
		<div class="scanner-content">
			<div class="column-scanner">
				<div id="qr-reader-container">
					<div id="qr-reader" class="d-flex align-items-center justify-content-center bg-dark text-white-50">
						<div id="scanner-placeholder">
							<div class="text-center">
								<i class="bi bi-geo-alt-fill fs-1"></i>
								<p>Verificando ubicación del kiosco...</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="column-status">
				<div id="location-status-panel" class="card location-status-card mb-3" style="display:none;">
					<div class="card-body d-flex align-items-center p-3">
						<i id="location-icon" class="bi fs-2 me-3"></i>
						<div>
							<h6 id="location-title" class="card-title mb-0"></h6>
							<p id="location-text" class="card-text small mb-0"></p>
						</div>
					</div>
				</div>

				<div class="instructions">
					<p class="mb-1"><strong><i class="bi bi-info-circle-fill me-2"></i>Instrucciones:</strong></p>
					<ol>
						<li>Apunta la cámara al código QR de la credencial del invitado.</li>
						<li>El sistema registrará la asistencia automáticamente.</li>
						<li>El resultado del escaneo aparecerá en el panel de abajo.</li>
					</ol>
				</div>
				<div id="status-panel">
					<div id="initial-state" class="text-center">
						<i class="bi bi-upc-scan text-muted" style="font-size: 6rem;"></i>
						<h3 class="mt-3 text-muted">Esperando Escaneo</h3>
						<p id="qr-reader-status" class="text-muted small">El escáner se activará al verificar la ubicación.</p>
					</div>
					<div id="success-message" class="alert alert-success alert-container" style="display: none;">
						<div class="row">
							<div class="col-5 text-center icon-col"><i class="bi bi-check-circle-fill"></i></div>
							<div class="col-7 text-col">
								<h4 class="alert-heading">¡Éxito!</h4>
								<p></p>
							</div>
						</div>
						<div class="alert-progress"></div>
					</div>
					<div id="info-message" class="alert alert-warning alert-container" style="display: none;">
						<div class="row">
							<div class="col-5 text-center icon-col"><i class="bi bi-exclamation-triangle-fill"></i></div>
							<div class="col-7 text-col">
								<h4 class="alert-heading">Atención</h4>
								<p></p>
							</div>
						</div>
						<div class="alert-progress"></div>
					</div>
					<div id="error-message" class="alert alert-danger alert-container" style="display: none;">
						<div class="row">
							<div class="col-5 text-center icon-col"><i class="bi bi-x-circle-fill"></i></div>
							<div class="col-7 text-col">
								<h4 class="alert-heading">Error</h4>
								<p></p>
							</div>
						</div>
						<div class="alert-progress"></div>
					</div>
				</div>
				<div id="ultimos-registros-container" class="mt-auto w-100" style="display: none;">
					<h5 class="text-center">Últimos Registros</h5>
					<ul id="ultimos-registros" class="list-group"></ul>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const initialStateDiv = document.getElementById('initial-state');
		const successMessageDiv = document.getElementById('success-message');
		const infoMessageDiv = document.getElementById('info-message');
		const errorMessageDiv = document.getElementById('error-message');
		const statusDiv = document.getElementById('qr-reader-status');
		const ultimosRegistrosContainer = document.getElementById('ultimos-registros-container');
		const ultimosRegistrosList = document.getElementById('ultimos-registros');
		const locationStatusPanel = document.getElementById('location-status-panel');
		const locationIcon = document.getElementById('location-icon');
		const locationTitle = document.getElementById('location-title');
		const locationText = document.getElementById('location-text');
		const scannerPlaceholder = document.getElementById('scanner-placeholder');

		let ultimoScan = null;
		let kioskoCoords = null;
		let ultimosRegistros = [];
		const synth = new Tone.Synth().toDestination();
		let messageTimer = null;

		// **NUEVO:** Coordenadas del evento desde PHP
		const eventoLat = <?php echo json_encode($evento->latitud); ?>;
		const eventoLng = <?php echo json_encode($evento->longitud); ?>;

		// **NUEVO:** Función para calcular distancia (Haversine)
		function calcularDistancia(lat1, lon1, lat2, lon2) {
			const R = 6371e3; // Radio de la Tierra en metros
			const phi1 = lat1 * Math.PI / 180;
			const phi2 = lat2 * Math.PI / 180;
			const deltaPhi = (lat2 - lat1) * Math.PI / 180;
			const deltaLambda = (lon2 - lon1) * Math.PI / 180;

			const a = Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
				Math.cos(phi1) * Math.cos(phi2) *
				Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
			const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
			return R * c; // en metros
		}

		function showMessage(type, text) {
			/* ... (Sin cambios) ... */ }

		function agregarARegistros(nombre) {
			/* ... (Sin cambios) ... */ }
		async function procesarAsistencia(tokenAcceso) {
			/* ... (Sin cambios) ... */ }

		const qrCodeSuccessCallback = (decodedText, decodedResult) => {
			if (decodedText === ultimoScan) return;
			ultimoScan = decodedText;
			procesarAsistencia(decodedText);
			setTimeout(() => {
				ultimoScan = null;
			}, 4000);
		};

		function iniciarScanner() {
			scannerPlaceholder.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>';
			statusDiv.innerText = 'Solicitando permiso para la cámara...';
			const html5QrCode = new Html5Qrcode("qr-reader");
			const config = {
				fps: 10,
				qrbox: (w, h) => {
					let s = Math.min(w, h);
					return {
						width: Math.floor(s * 0.7),
						height: Math.floor(s * 0.7)
					};
				}
			};
			html5QrCode.start({
					facingMode: "environment"
				}, config, qrCodeSuccessCallback)
				.then(() => {
					scannerPlaceholder.style.display = 'none';
					statusDiv.innerText = 'Cámara iniciada. Escaneando...';
				})
				.catch(err => {
					scannerPlaceholder.innerHTML = '<p class="text-danger">Error al iniciar la cámara.</p>';
					statusDiv.innerText = '';
					showMessage('error', 'No se pudo iniciar la cámara.');
				});
		}

		function setLocationStatus(status, title, text) {
			locationStatusPanel.style.display = 'flex';
			locationStatusPanel.className = 'card location-status-card mb-3'; // Reset
			if (status === 'success') {
				locationStatusPanel.classList.add('border-success');
				locationIcon.className = 'bi bi-check-circle-fill text-success fs-2 me-3';
			} else {
				locationStatusPanel.classList.add('border-danger');
				locationIcon.className = 'bi bi-exclamation-triangle-fill text-danger fs-2 me-3';
			}
			locationTitle.textContent = title;
			locationText.textContent = text;
		}

		statusDiv.innerText = 'Obteniendo ubicación del kiosco...';
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(
				(position) => {
					kioskoCoords = {
						lat: position.coords.latitude,
						lng: position.coords.longitude
					};

					if (!eventoLat || !eventoLng) {
						setLocationStatus('warning', 'Ubicación no Verificable', 'El evento no tiene coordenadas guardadas. No se puede validar la distancia.');
						iniciarScanner(); // Iniciar de todas formas, pero con advertencia
						return;
					}

					const distancia = calcularDistancia(eventoLat, eventoLng, kioskoCoords.lat, kioskoCoords.lng);
					if (distancia <= 200) { // Radio de 200 metros
						setLocationStatus('success', 'Ubicación Verificada', `El kiosco está a ${distancia.toFixed(0)} metros del lugar del evento.`);
						iniciarScanner();
					} else {
						setLocationStatus('error', '¡Alerta de Ubicación!', `El kiosco está a más de ${distancia.toFixed(0)} metros del lugar del evento. No se activará el escáner.`);
						scannerPlaceholder.innerHTML = '<p class="text-danger p-3">El escáner está desactivado por encontrarse fuera de la ubicación del evento.</p>';
					}
				},
				() => {
					statusDiv.innerText = '';
					setLocationStatus('error', 'Error de GPS', 'No se pudo obtener la ubicación. Revise los permisos.');
					scannerPlaceholder.innerHTML = '<p class="text-danger p-3">El escáner está desactivado porque no se pudo obtener la ubicación GPS.</p>';
				}
			);
		} else {
			statusDiv.innerText = '';
			setLocationStatus('error', 'GPS no Soportado', 'Este navegador no soporta geolocalización.');
			scannerPlaceholder.innerHTML = '<p class="text-danger p-3">El escáner está desactivado porque el navegador no soporta GPS.</p>';
		}
	});
</script>