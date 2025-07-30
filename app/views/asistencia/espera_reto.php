<?php
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
?>
<div class="container container-main d-flex align-items-center">
    <div class="w-100" style="max-width: 600px; margin:auto;">
        <div class="text-center mb-4">
            <h1 class="h2 mb-3">Verificación de Asistencia</h1>
            <p class="lead text-muted">Ingresa el código que se muestra en pantalla.</p>
        </div>
        <div class="card shadow-sm">
            <div class="card-body text-center" id="reto-container">
                <h3 id="codigo-reto" class="display-4">---</h3>
                <div class="mb-3">
                    <input type="text" id="respuesta" class="form-control text-center" placeholder="Código" maxlength="6">
                </div>
                <div class="d-grid gap-2">
                    <button id="btn-verificar" class="btn btn-primary">Verificar</button>
                    <button id="btn-nuevo" class="btn btn-outline-secondary">Generar Nuevo Reto</button>
                </div>
                <div id="mensaje" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
<script>
const token = '<?php echo $invitacion->token_acceso; ?>';
let idReto = 0;

async function cargarReto() {
    const res = await fetch('<?php echo URL_PATH; ?>asistencia/obtenerRetoActivo/' + token);
    const data = await res.json();
    if (data.exito) {
        idReto = data.id_reto;
        document.getElementById('codigo-reto').textContent = data.codigo;
    } else {
        document.getElementById('codigo-reto').textContent = '---';
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
        document.getElementById('mensaje').innerHTML = '<span class="text-success">Código correcto</span>';
    } else {
        document.getElementById('mensaje').innerHTML = '<span class="text-danger">Código incorrecto</span>';
    }
    document.getElementById('respuesta').value = '';
}

document.getElementById('btn-verificar').addEventListener('click', verificar);
document.getElementById('btn-nuevo').addEventListener('click', cargarReto);
setInterval(cargarReto, 15000);
cargarReto();
</script>
