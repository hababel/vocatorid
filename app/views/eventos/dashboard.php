<?php
// Panel avanzado de gestión de retos
$evento = $datos['evento'];
?>
<div class="container-fluid px-md-4 py-4">
    <h1 class="h3 mb-4">Gestión de Retos de Asistencia - <?php echo htmlspecialchars($evento->nombre_evento); ?></h1>

    <form id="form-reto" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="descripcion" class="form-control" placeholder="Descripción del Reto" required>
        </div>
        <div class="col-md-3">
            <input type="datetime-local" name="hora_inicio" class="form-control" required>
        </div>
        <div class="col-md-3">
            <input type="datetime-local" name="hora_fin" class="form-control" required>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-success">Crear Reto</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estado</th>
                    <th>Completados</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="lista-retos"></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de Reto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Invitado</th><th>Código</th><th>Fecha</th></tr></thead>
                <tbody id="detalle-body"></tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const URL_BASE = '<?php echo URL_PATH; ?>';
const ID_EVENTO = <?php echo (int)$evento->id; ?>;
</script>
<script src="<?php echo URL_PATH; ?>core/customassets/js/retos_admin.js"></script>
