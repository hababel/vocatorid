<?php
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
require_once APP_BASE_PHYSICAL_PATH . '/app/controller/AsistenciaController.php';
?>
<style>
body {
  font-family: 'Segoe UI', Arial, sans-serif;
  background: #f9f9f9;
  padding: 20px;
  text-align: center;
}

.wizard-header {
  margin-bottom: 20px;
}

.wizard-step {
  display: inline-block;
  background: #007bff;
  color: #fff;
  font-weight: bold;
  padding: 5px 12px;
  border-radius: 15px;
  font-size: 14px;
}

.wizard-header h2 {
  margin: 10px 0;
  font-size: 22px;
}

.mensaje {
  background: #d1e7dd;
  color: #0f5132;
  border-radius: 5px;
  padding: 12px;
  margin-bottom: 20px;
}

.wizard-section {
  margin-bottom: 25px;
}

.opciones {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}

.opciones img, .opciones button {
  width: 70px;
  height: 70px;
  object-fit: contain;
  cursor: pointer;
  border: 2px solid transparent;
  border-radius: 5px;
  background: #fff;
}

.opciones button {
  border: 2px solid #000;
}

.seleccionado {
  border: 3px solid #2ecc71 !important;
}

#confirmar {
  width: 90%;
  max-width: 250px;
  background: #007bff;
  color: #fff;
  border: none;
  padding: 12px;
  font-size: 18px;
  border-radius: 5px;
  cursor: pointer;
}

#confirmar:hover {
  background: #0056b3;
}
</style>

<div class="container container-main d-flex align-items-center">
  <div class="w-100" style="max-width:600px;margin:auto;">
    <div class="wizard-header">
      <span class="wizard-step">Paso 2 de 2</span>
      <h2>Verificación de Asistencia</h2>
      <p>Selecciona los elementos que viste en la pantalla compartida del evento.</p>
    </div>

    <div class="mensaje">
      ✅ Paso 1 Completado: Sabemos que eres tú porque accediste con tu enlace único.
    </div>

    <div class="wizard-section">
      <h3>1️⃣ Selecciona la fruta que viste:</h3>
      <div class="opciones" id="frutas"></div>
    </div>

    <div class="wizard-section">
      <h3>2️⃣ Selecciona el color que viste:</h3>
      <div class="opciones" id="colores"></div>
    </div>

    <div class="wizard-section">
      <h3>3️⃣ Selecciona el animal que viste:</h3>
      <div class="opciones" id="animales"></div>
    </div>

    <button id="confirmar">✅ Confirmar Asistencia</button>
    <div id="mensaje" class="mt-3"></div>
  </div>
</div>
<script>
const URL_PATH = '<?php echo URL_PATH; ?>';
const token = '<?php echo $invitacion->token_acceso; ?>';
const mapaColores = <?php echo json_encode(AsistenciaController::$colores); ?>;
let idReto = 0;
let frutaSel = '';
let animalSel = '';
let colorSel = '';

function seleccionar(elemento, grupo) {
  document.querySelectorAll(`#${grupo}s .seleccionado`).forEach(el => el.classList.remove('seleccionado'));
  elemento.classList.add('seleccionado');
}

function renderOpciones(cont, opciones, tipo) {
  cont.innerHTML = '';
  opciones.forEach(opt => {
    const nombre = tipo === 'color'
      ? Object.keys(mapaColores).find(k => mapaColores[k] === opt)
      : opt.split('/').pop().replace(/\.[^.]+$/, '');
    const el = tipo === 'color' ? document.createElement('button') : document.createElement('img');
    if (tipo === 'color') {
      el.style.backgroundColor = opt;
    } else {
      el.src = URL_PATH + opt;
      el.alt = nombre;
    }
    el.addEventListener('click', () => {
      if (tipo === 'fruta') frutaSel = nombre;
      if (tipo === 'animal') animalSel = nombre;
      if (tipo === 'color') colorSel = nombre;
      seleccionar(el, tipo);
    });
    cont.appendChild(el);
  });
}

async function cargarReto(){
    const res = await fetch('<?php echo URL_PATH; ?>asistencia/obtenerRetoActivo/'+token);
    const data = await res.json();
    if(data.exito){
        idReto = data.id_reto;
        renderOpciones(document.getElementById('frutas'), data.opciones_frutas, 'fruta');
        renderOpciones(document.getElementById('animales'), data.opciones_animales, 'animal');
        renderOpciones(document.getElementById('colores'), data.opciones_colores, 'color');
    }
}

async function verificar(){
    if(!frutaSel || !colorSel || !animalSel){
        document.getElementById('mensaje').textContent='Debes seleccionar los tres elementos.';
        return;
    }
    const fd=new FormData();
    fd.append('token',token);
    fd.append('id_reto',idReto);
    fd.append('fruta',frutaSel);
    fd.append('color',colorSel);
    fd.append('animal',animalSel);
    const res=await fetch('<?php echo URL_PATH; ?>validar_reto.php',{method:'POST',body:fd});
    const data=await res.json();
    if(data.exito){
        document.getElementById('mensaje').textContent='✅ Asistencia registrada';
        document.getElementById('confirmar').disabled=true;
    }else{
        const msg=data.mensaje?data.mensaje:'Código incorrecto';
        document.getElementById('mensaje').textContent=msg;
    }
}

document.getElementById('confirmar').addEventListener('click', verificar);
setInterval(cargarReto,15000);
cargarReto();
</script>
