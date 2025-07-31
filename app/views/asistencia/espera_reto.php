<?php
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
?>
<div class="container container-main d-flex align-items-center">
    <div class="w-100" style="max-width: 600px; margin:auto;">
        <div class="text-center mb-4">
            <h1 class="h2 mb-3">Verificación de Asistencia</h1>
            <p class="lead text-muted">Ingresa el código que ves en la pantalla del organizador.</p>
        </div>
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>
                <strong>Paso 1 – Completado</strong><br>
                <small>Sabemos que eres tú, porque accediste con tu enlace único de seguridad.</small>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body text-center" id="reto-container">
                <div class="mb-3">
                    <input type="text" id="respuesta" class="form-control text-center" placeholder="Código" maxlength="30">
                </div>
                <div class="d-grid gap-2">
                    <button id="btn-verificar" class="btn btn-primary">Verificar</button>
                </div>
                <div id="mensaje" class="mt-3"></div>
                <div id="contador" class="text-muted small"></div>
            </div>
        </div>
    </div>
</div>
<script>
const token = '<?php echo $invitacion->token_acceso; ?>';
let idReto = 0;
let proximoEn = 0;
let countdownInterval = null;

async function cargarReto() {
    const res = await fetch('<?php echo URL_PATH; ?>asistencia/obtenerRetoActivo/' + token);
    const data = await res.json();
    if (data.exito) {
        idReto = data.id_reto;
        document.getElementById('contador').textContent = '';
        if(countdownInterval) clearInterval(countdownInterval);
    } else if (data.proximo_en) {
        idReto = 0;
        proximoEn = data.proximo_en;
        iniciarContador();
    } else {
        document.getElementById('contador').textContent = '';
    }
}

async function verificar() {
    const codigo = document.getElementById('respuesta').value;
    const formData = new FormData();
    formData.append('token', token);
    formData.append('id_reto', idReto);
    formData.append('codigo', codigo);
    const res = await fetch('<?php echo URL_PATH; ?>asistencia/validarReto', { method:'POST', body: formData });
    const data = await res.json();
    if (data.exito) {
        document.getElementById('mensaje').innerHTML = '<span class="text-success">✅ Asistencia registrada</span>';
        document.getElementById('btn-verificar').disabled = true;
    } else {
        const msg = data.mensaje ? data.mensaje : 'Código incorrecto';
        document.getElementById('mensaje').innerHTML = '<span class="text-danger">' + msg + '</span>';
    }
    document.getElementById('respuesta').value = '';
}

function iniciarContador(){
    if(countdownInterval) clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
        if(proximoEn <= 0){
            clearInterval(countdownInterval);
            document.getElementById('contador').textContent = 'Activando nuevo reto...';
            cargarReto();
            return;
        }
        document.getElementById('contador').textContent = 'Próximo reto en ' + proximoEn + 's';
        proximoEn--;
    },1000);
}

document.getElementById('btn-verificar').addEventListener('click', verificar);
setInterval(cargarReto, 15000);
cargarReto();
</script>
