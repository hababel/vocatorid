<?php
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
?>
<style>
    .reto-visual {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
    }
    .reto-img {
        width: 60px;
        height: 60px;
        object-fit: contain;
    }
    .color-btn {
        width: 40px;
        height: 40px;
        border: none;
    }
    .progress-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    #progress-bar {
        transition: background-color 0.5s ease, width 0.2s ease!important;
        border-radius: 8px;
    }
    .progress-bar-white { background-color: #f8f9fa; }
    .progress-bar-black { background-color: #000000; }
    @keyframes blink-animation { 50% { opacity: 0.2; } }
    .blinking-bar { animation: blink-animation 1s infinite; }
</style>
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
                <div class="reto-visual mb-3">
                    <img id="fruta" class="reto-img" src="" alt="fruta">
                    <button id="color-boton" class="color-btn"></button>
                    <img id="animal" class="reto-img" src="" alt="animal">
                </div>
                <div class="progress-container mb-3">
                    <div class="progress flex-grow-1">
                        <div id="progress-bar" class="progress-bar" role="progressbar" style="width:100%"></div>
                    </div>
                    <div id="contador" class="text-muted small">--:--</div>
                </div>
                <div class="mb-3">
                    <input type="text" id="respuesta" class="form-control text-center" placeholder="Código" maxlength="30">
                </div>
                <div class="d-grid gap-2">
                    <button id="btn-verificar" class="btn btn-primary">Verificar</button>
                </div>
                <div id="mensaje" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
<script>
const token = '<?php echo $invitacion->token_acceso; ?>';
let idReto = 0;
let proximoEn = 0;
let countdownInterval = null;
const frutaElem = document.getElementById('fruta');
const animalElem = document.getElementById('animal');
const colorBtn = document.getElementById('color-boton');
const progressBar = document.getElementById('progress-bar');
const contadorElem = document.getElementById('contador');

async function cargarReto() {
    const res = await fetch('<?php echo URL_PATH; ?>asistencia/obtenerRetoActivo/' + token);
    const data = await res.json();
    if (data.exito) {
        idReto = data.id_reto;
        frutaElem.src = data.fruta_img;
        animalElem.src = data.animal_img;
        colorBtn.style.backgroundColor = data.color_hex;
        iniciarBarra(data.tiempo_restante);
    } else if (data.proximo_en) {
        idReto = 0;
        proximoEn = data.proximo_en;
        iniciarContador();
    } else {
        contadorElem.textContent = '';
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

function iniciarBarra(duracion){
    clearInterval(countdownInterval);
    progressBar.classList.remove('blinking-bar','progress-bar-white','progress-bar-black');
    let segundosRestantes = duracion;

    function actualizar(){
        contadorElem.textContent = segundosRestantes + 's';
        const progreso = (segundosRestantes / duracion) * 100;
        progressBar.style.width = progreso + '%';
        const bloqueActual = Math.floor((duracion - segundosRestantes) / 5);
        if(bloqueActual % 2 === 0){
            progressBar.classList.remove('progress-bar-black');
            progressBar.classList.add('progress-bar-white');
        } else {
            progressBar.classList.remove('progress-bar-white');
            progressBar.classList.add('progress-bar-black');
        }
        if(segundosRestantes <= 15){
            progressBar.classList.add('blinking-bar');
        } else {
            progressBar.classList.remove('blinking-bar');
        }
    }

    actualizar();
    countdownInterval = setInterval(() => {
        segundosRestantes--;
        actualizar();
        if(segundosRestantes < 0){
            clearInterval(countdownInterval);
            cargarReto();
        }
    },1000);
}

function iniciarContador(){
    if(countdownInterval) clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
        if(proximoEn <= 0){
            clearInterval(countdownInterval);
            contadorElem.textContent = 'Activando nuevo reto...';
            progressBar.style.width = '0%';
            cargarReto();
            return;
        }
        contadorElem.textContent = 'Próximo reto en ' + proximoEn + 's';
        proximoEn--;
    },1000);
}

document.getElementById('btn-verificar').addEventListener('click', verificar);
setInterval(cargarReto, 15000);
cargarReto();
</script>
