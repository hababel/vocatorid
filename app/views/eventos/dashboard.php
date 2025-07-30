<?php
// Vista sencilla de dashboard de retos
$evento = $datos['evento'];
$registros = $datos['registros'];
?>
<div class="container-fluid px-md-4 py-4">
    <h1 class="h3 mb-4">Dashboard de Retos - <?php echo htmlspecialchars($evento->nombre_evento); ?></h1>
    <div class="mb-3 d-flex justify-content-between">
        <button id="btn-manual" class="btn btn-primary">Emitir Reto Manual</button>
        <div id="porcentaje" class="fw-bold"></div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr><th>Invitado</th><th>Reto</th><th>Estado</th></tr>
            </thead>
            <tbody id="tabla-registros">
                <?php foreach ($registros as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r->nombre); ?></td>
                    <td><?php echo $r->id_reto; ?></td>
                    <td><?php echo $r->correcto ? '✅' : '❌'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
const btn = document.getElementById('btn-manual');
btn.addEventListener('click', async () => {
    const res = await fetch('<?php echo URL_PATH; ?>evento/emitirRetoManual/<?php echo $evento->id; ?>');
    const data = await res.json();
    if(data.exito){
        alert('Reto emitido');
        cargarRegistros();
    }
});

async function cargarRegistros(){
    const res = await fetch('<?php echo URL_PATH; ?>evento/obtenerRegistrosReto/<?php echo $evento->id; ?>');
    const data = await res.json();
    if(!data.exito) return;
    const tbody = document.getElementById('tabla-registros');
    tbody.innerHTML = '';
    data.registros.forEach(r => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${r.nombre}</td><td>${r.id_reto}</td><td>${r.correcto ? '✅' : '❌'}</td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('porcentaje').textContent = 'Retos completados: ' + data.porcentaje + '%';
}

setInterval(cargarRegistros, 10000);
window.addEventListener('load', cargarRegistros);
</script>
