<?php
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
require_once APP_BASE_PHYSICAL_PATH . '/app/controller/AsistenciaController.php';
?>
<style>
    .titulo-seccion{margin-top:1rem;font-weight:bold;}
    .opciones{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:1rem;}
    .opciones img,.opciones button{width:80px;height:80px;object-fit:contain;border:2px solid transparent;border-radius:5px;cursor:pointer;}
    .seleccionada{border-color:#28a745!important;}
    .mensaje{background:#d4edda;padding:.75rem;border-radius:5px;margin-bottom:1rem;font-size:.9rem;}
</style>
<div class="container container-main d-flex align-items-center">
    <div class="w-100" style="max-width:600px;margin:auto;">
        <h2>Verificación de Asistencia</h2>
        <p>Selecciona los elementos que viste en la pantalla compartida del evento.</p>
        <div class="mensaje">
            ✅ Paso 1 Completado: Sabemos que eres tú porque accediste con tu enlace único.
        </div>
        <div class="titulo-seccion">Selecciona la fruta que viste:</div>
        <div class="opciones" id="frutas"></div>
        <div class="titulo-seccion">Selecciona el color que viste:</div>
        <div class="opciones" id="colores"></div>
        <div class="titulo-seccion">Selecciona el animal que viste:</div>
        <div class="opciones" id="animales"></div>
        <button id="verificar" class="btn btn-primary w-100">Confirmar</button>
        <div id="mensaje" class="mt-3"></div>
    </div>
</div>
<script>
const token = '<?php echo $invitacion->token_acceso; ?>';
const mapaColores = <?php echo json_encode(AsistenciaController::$colores); ?>;
let idReto = 0;
let frutaSel = '';
let animalSel = '';
let colorSel = '';

function renderOpciones(cont, opciones, tipo){
    cont.innerHTML='';
    opciones.forEach(opt=>{
        const nombre = tipo==='color' ? Object.keys(mapaColores).find(k=>mapaColores[k]===opt) : opt.split('/').pop().replace(/\.[^.]+$/, '');
        const el = tipo==='color'?document.createElement('button'):document.createElement('img');
        if(tipo==='color') el.style.backgroundColor=opt; else el.src=opt;
        el.addEventListener('click',()=>{
            if(tipo==='fruta'){frutaSel=nombre; cont.querySelectorAll('img').forEach(i=>i.classList.remove('seleccionada'));}
            if(tipo==='animal'){animalSel=nombre; cont.querySelectorAll('img').forEach(i=>i.classList.remove('seleccionada'));}
            if(tipo==='color'){colorSel=nombre; cont.querySelectorAll('button').forEach(i=>i.classList.remove('seleccionada'));}
            el.classList.add('seleccionada');
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
        document.getElementById('verificar').disabled=true;
    }else{
        const msg=data.mensaje?data.mensaje:'Código incorrecto';
        document.getElementById('mensaje').textContent=msg;
    }
}

document.getElementById('verificar').addEventListener('click',verificar);
setInterval(cargarReto,15000);
cargarReto();
</script>
