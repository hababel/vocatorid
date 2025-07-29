<?php
$invitacion = $datos['invitacion'];
?>
<div class="container container-main d-flex align-items-center">
    <div class="w-100" style="max-width:500px;margin:auto;">
        <h1 class="h2 text-center mb-4">Verificación de Asistencia</h1>
        <div class="text-center mb-3">
            <div id="codigo-reto" class="display-4 fw-bold">----</div>
        </div>
        <input type="text" id="respuesta" class="form-control text-center mb-3" placeholder="Ingresa el código">
        <button id="btn-verificar" class="btn btn-primary w-100 mb-2">Verificar</button>
        <button id="btn-nuevo" class="btn btn-outline-secondary w-100">Generar Nuevo Reto</button>
        <div id="mensaje" class="mt-3 text-center"></div>
    </div>
</div>
<script>
const token = '<?php echo $invitacion->token_acceso; ?>';
let idReto = null;
async function generarReto(){
    const resp = await fetch('<?php echo URL_PATH; ?>asistencia/obtenerReto/' + token);
    const data = await resp.json();
    if(data.exito){
        idReto = data.id_reto;
        document.getElementById('codigo-reto').textContent = data.codigo;
        setTimeout(generarReto, 15000);
    }else{
        document.getElementById('codigo-reto').textContent = '----';
    }
}
async function verificar(){
    const codigo = document.getElementById('respuesta').value;
    const formData = new FormData();
    formData.append('token', token);
    formData.append('id_reto', idReto);
    formData.append('codigo', codigo);
    const resp = await fetch('<?php echo URL_PATH; ?>asistencia/validarReto', {method:'POST', body:formData});
    const data = await resp.json();
    document.getElementById('mensaje').textContent = data.mensaje;
}
document.getElementById('btn-nuevo').addEventListener('click', generarReto);
document.getElementById('btn-verificar').addEventListener('click', verificar);
generarReto();
</script>
