<?php
// Vista sencilla de dashboard de retos
$evento = $datos['evento'];
$registros = $datos['registros'];
?>
<div class="container-fluid px-md-4 py-4">
    <h1 class="h3 mb-4">Dashboard de Retos - <?php echo htmlspecialchars($evento->nombre_evento); ?></h1>
    <div class="mb-3">
        <button id="btn-manual" class="btn btn-primary">Emitir Reto Manual</button>
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
    }
});
</script>
