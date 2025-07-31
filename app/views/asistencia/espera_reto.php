<?php
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
require_once APP_BASE_PHYSICAL_PATH . '/core/config/recursos.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/controller/AsistenciaController.php';
$recursos = obtenerRecursosClaveVisual();
$listaFrutas = array_map(fn($f) => basename($f, '.jpg'), $recursos['frutas']);
$listaAnimales = array_map(fn($a) => basename($a, '.jpg'), $recursos['animales']);
$listaColores = array_keys(AsistenciaController::$colores);
?>
<style>
    #card-kiosco {
        background: #fff !important;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
    }
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
    .opciones-list {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        margin-bottom: 1rem;
    }
    .opcion-img {
        width: 60px;
        height: 60px;
        object-fit: contain;
        cursor: pointer;
        border: 3px solid transparent;
        border-radius: 8px;
    }
    .opcion-img.selected {
        border-color: #0d6efd;
    }
    .color-option {
        width: 40px;
        height: 40px;
        border: none;
        cursor: pointer;
        border: 3px solid transparent;
        border-radius: 4px;
    }
    .color-option.selected {
        border-color: #0d6efd;
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
            <p class="lead text-muted">Selecciona los elementos que ves en pantalla.</p>
        </div>
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>
                <strong>Paso 1 – Completado</strong><br>
                <small>Sabemos que eres tú, porque accediste con tu enlace único de seguridad.</small>
            </div>
        </div>
        <div id="card-kiosco">
            <div id="reto-container">
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
                <div class="opciones-list" id="opciones-fruta"></div>
                <div class="opciones-list" id="opciones-color"></div>
                <div class="opciones-list" id="opciones-animal"></div>
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
const opcionesFrutas = <?php echo json_encode($listaFrutas); ?>;
const opcionesColores = <?php echo json_encode($listaColores); ?>;
const opcionesAnimales = <?php echo json_encode($listaAnimales); ?>;
const mapaColores = <?php echo json_encode(AsistenciaController::$colores); ?>;
const baseUrl = '<?php echo URL_PATH; ?>';
let idReto = 0;
let proximoEn = 0;
let countdownInterval = null;
const frutaElem = document.getElementById('fruta');
const animalElem = document.getElementById('animal');
const colorBtn = document.getElementById('color-boton');
const progressBar = document.getElementById('progress-bar');
const contadorElem = document.getElementById('contador');

function generarOpciones(lista, correcta) {
    const opciones = [correcta];
    while (opciones.length < 5) {
        const op = lista[Math.floor(Math.random() * lista.length)];
        if (!opciones.includes(op)) opciones.push(op);
    }
    for (let i = opciones.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [opciones[i], opciones[j]] = [opciones[j], opciones[i]];
    }
    return opciones;
}


let frutaSeleccionada = '';
let animalSeleccionado = '';
let colorSeleccionado = '';

function renderFrutas(opciones) {
    const cont = document.getElementById('opciones-fruta');
    cont.innerHTML = '';
    frutaSeleccionada = '';
    opciones.forEach(nombre => {
        const img = document.createElement('img');
        img.src = baseUrl + 'core/img/clave_visual/frutas/' + nombre + '.jpg';
        img.className = 'opcion-img';
        img.addEventListener('click', () => {
            frutaSeleccionada = nombre;
            cont.querySelectorAll('img').forEach(i => i.classList.remove('selected'));
            img.classList.add('selected');
        });
        cont.appendChild(img);
    });
}

function renderAnimales(opciones) {
    const cont = document.getElementById('opciones-animal');
    cont.innerHTML = '';
    animalSeleccionado = '';
    opciones.forEach(nombre => {
        const img = document.createElement('img');
        img.src = baseUrl + 'core/img/clave_visual/animales/' + nombre + '.jpg';
        img.className = 'opcion-img';
        img.addEventListener('click', () => {
            animalSeleccionado = nombre;
            cont.querySelectorAll('img').forEach(i => i.classList.remove('selected'));
            img.classList.add('selected');
        });
        cont.appendChild(img);
    });
}

function renderColores(opciones) {
    const cont = document.getElementById('opciones-color');
    cont.innerHTML = '';
    colorSeleccionado = '';
    opciones.forEach(nombre => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'color-option';
        btn.style.backgroundColor = mapaColores[nombre];
        btn.addEventListener('click', () => {
            colorSeleccionado = nombre;
            cont.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
        });
        cont.appendChild(btn);
    });
}

async function cargarReto() {
    const res = await fetch('<?php echo URL_PATH; ?>asistencia/obtenerRetoActivo/' + token);
    const data = await res.json();
    if (data.exito) {
        idReto = data.id_reto;
        frutaElem.src = data.fruta_img;
        animalElem.src = data.animal_img;
        colorBtn.style.backgroundColor = data.color_hex;
        const frutaNombre = data.fruta_img.split('/').pop().replace('.jpg','');
        const animalNombre = data.animal_img.split('/').pop().replace('.jpg','');
        const colorNombre = Object.keys(mapaColores).find(n => mapaColores[n] === data.color_hex);
        renderFrutas(generarOpciones(opcionesFrutas, frutaNombre));
        renderColores(generarOpciones(opcionesColores, colorNombre));
        renderAnimales(generarOpciones(opcionesAnimales, animalNombre));
        iniciarBarra(data.tiempo_restante);
    } else if (data.proximo_en) {
        idReto = 0;
        proximoEn = data.proximo_en;
        iniciarContador();
        renderFrutas([]);
        renderColores([]);
        renderAnimales([]);
    } else {
        contadorElem.textContent = '';
        renderFrutas([]);
        renderColores([]);
        renderAnimales([]);
    }
}

async function verificar() {
    const frutaSel = frutaSeleccionada;
    const colorSel = colorSeleccionado;
    const animalSel = animalSeleccionado;
    const formData = new FormData();
    formData.append('token', token);
    formData.append('id_reto', idReto);
    formData.append('fruta', frutaSel);
    formData.append('color', colorSel);
    formData.append('animal', animalSel);
    const res = await fetch('<?php echo URL_PATH; ?>asistencia/validarReto', { method:'POST', body: formData });
    const data = await res.json();
    if (data.exito) {
        document.getElementById('mensaje').innerHTML = '<span class="text-success">✅ Asistencia registrada</span>';
        document.getElementById('btn-verificar').disabled = true;
    } else {
        const msg = data.mensaje ? data.mensaje : 'Código incorrecto';
        document.getElementById('mensaje').innerHTML = '<span class="text-danger">' + msg + '</span>';
    }
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
