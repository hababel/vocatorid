<?php
$invitacion = $datos['invitacion'];
$opciones_frutas = $datos['opciones_frutas'];
$opciones_colores = $datos['opciones_colores'];
$opciones_animales = $datos['opciones_animales'];
?>
<style>
.opciones {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin: 10px 0 20px;
}
.opciones img,
.opciones button {
  width: 80px;
  height: 80px;
  cursor: pointer;
  border: 2px solid transparent;
  border-radius: 5px;
}
.seleccionado {
  border: 3px solid #2ecc71 !important;
}
</style>
<div class="container container-main d-flex align-items-center">
  <div class="w-100" style="max-width: 700px; margin: auto;">
    <div class="text-center mb-4">
      <h1 class="h2 mb-3">Verificación Final</h1>
      <p class="lead text-muted">Para completar tu registro, selecciona la combinación correcta.</p>
    </div>
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <form action="<?php echo URL_PATH; ?>asistencia/procesarClaveVisual" method="POST" id="formClave">
          <input type="hidden" name="token_acceso" value="<?php echo $invitacion->token_acceso; ?>">
          <input type="hidden" name="clave_fruta" id="clave_fruta">
          <input type="hidden" name="clave_color" id="clave_color">
          <input type="hidden" name="clave_animal" id="clave_animal">

          <div class="titulo-seccion">Selecciona la fruta que viste:</div>
          <div class="opciones" id="frutas">
<?php foreach ($opciones_frutas as $img): ?>
            <img src="<?php echo URL_PATH; ?>core/img/clave_visual/frutas/<?php echo $img; ?>" data-valor="<?php echo $img; ?>" onclick="seleccionar(this, 'fruta')">
<?php endforeach; ?>
          </div>

          <div class="titulo-seccion">Selecciona el color que viste:</div>
          <div class="opciones" id="colores">
<?php foreach ($opciones_colores as $color): ?>
            <button type="button" style="background:<?php echo strtolower($color); ?>;" data-valor="<?php echo $color; ?>" onclick="seleccionar(this, 'color')"></button>
<?php endforeach; ?>
          </div>

          <div class="titulo-seccion">Selecciona el animal que viste:</div>
          <div class="opciones" id="animales">
<?php foreach ($opciones_animales as $img): ?>
            <img src="<?php echo URL_PATH; ?>core/img/clave_visual/animales/<?php echo $img; ?>" data-valor="<?php echo $img; ?>" onclick="seleccionar(this, 'animal')">
<?php endforeach; ?>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg" id="confirmar" disabled>Confirmar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
function seleccionar(el, grupo) {
  const cont = document.getElementById(grupo + 's');
  Array.from(cont.children).forEach(c => c.classList.remove('seleccionado'));
  el.classList.add('seleccionado');
  document.getElementById('clave_' + grupo).value = el.dataset.valor;
  validarSeleccion();
}
function validarSeleccion() {
  const f = document.getElementById('clave_fruta').value;
  const c = document.getElementById('clave_color').value;
  const a = document.getElementById('clave_animal').value;
  document.getElementById('confirmar').disabled = !(f && c && a);
}
</script>
