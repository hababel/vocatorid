<?php
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
require_once APP_BASE_PHYSICAL_PATH . '/app/controller/AsistenciaController.php';
require_once APP_BASE_PHYSICAL_PATH . '/core/config/recursos.php';

// Obtener y barajar recursos de la clave visual
$recursos = obtenerRecursosClaveVisual();

$frutas = array_map(function ($archivo) {
    return [
        'nombre' => pathinfo($archivo, PATHINFO_FILENAME),
        'url' => URL_PATH . 'core/img/clave_visual/frutas/' . $archivo
    ];
}, $recursos['frutas']);

$animales = array_map(function ($archivo) {
    return [
        'nombre' => pathinfo($archivo, PATHINFO_FILENAME),
        'url' => URL_PATH . 'core/img/clave_visual/animales/' . $archivo
    ];
}, $recursos['animales']);

$colores = [];
foreach (AsistenciaController::$colores as $nombre => $hex) {
    $colores[] = ['nombre' => $nombre, 'hex' => $hex];
}

shuffle($frutas);
shuffle($animales);
shuffle($colores);

// Obtener ID del reto activo
ob_start();
$ctrl = new AsistenciaController();
$ctrl->obtenerRetoActivo($invitacion->token_acceso);
$retoData = json_decode(ob_get_clean(), true);
$idReto = $retoData['id_reto'] ?? 0;
header('Content-Type: text/html; charset=utf-8');
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
}

.opciones label {
  cursor: pointer;
  display: inline-block;
  text-align: center;
}

.opciones img, .opciones button {
  width: 80px;
  height: 80px;
  border: 2px solid transparent;
  border-radius: 5px;
}

.opciones input[type="radio"] {
  display: none;
}

.opciones input[type="radio"]:checked + img,
.opciones input[type="radio"]:checked + button {
  border: 3px solid #2ecc71;
}

.boton-verificar {
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

.boton-verificar:hover {
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

    <form method="POST" action="<?php echo URL_PATH; ?>validar_reto.php">
      <input type="hidden" name="token" value="<?php echo $invitacion->token_acceso; ?>">
      <input type="hidden" name="id_reto" value="<?php echo $idReto; ?>">

      <div class="wizard-section">
        <h3>1️⃣ Selecciona la fruta que viste:</h3>
        <div class="opciones">
          <?php foreach($frutas as $fruta): ?>
            <label>
              <input type="radio" name="fruta" value="<?php echo $fruta['nombre']; ?>" required>
              <img src="<?php echo $fruta['url']; ?>" alt="<?php echo $fruta['nombre']; ?>">
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="wizard-section">
        <h3>2️⃣ Selecciona el color que viste:</h3>
        <div class="opciones">
          <?php foreach($colores as $color): ?>
            <label>
              <input type="radio" name="color" value="<?php echo $color['nombre']; ?>" required>
              <button style="background:<?php echo $color['hex']; ?>;"></button>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="wizard-section">
        <h3>3️⃣ Selecciona el animal que viste:</h3>
        <div class="opciones">
          <?php foreach($animales as $animal): ?>
            <label>
              <input type="radio" name="animal" value="<?php echo $animal['nombre']; ?>" required>
              <img src="<?php echo $animal['url']; ?>" alt="<?php echo $animal['nombre']; ?>">
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <button type="submit" class="boton-verificar">✅ Confirmar Asistencia</button>
    </form>
    <div id="mensaje" class="mt-3"></div>
  </div>
</div>
