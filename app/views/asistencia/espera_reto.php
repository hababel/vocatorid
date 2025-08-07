<?php
$evento = $datos['evento'];
$invitacion = $datos['invitacion'];
require_once APP_BASE_PHYSICAL_PATH . '/app/controller/AsistenciaController.php';
require_once APP_BASE_PHYSICAL_PATH . '/core/config/recursos.php';

// Obtener opciones del reto activo para este invitado
ob_start();
$ctrl = new AsistenciaController();
$ctrl->obtenerRetoActivo($invitacion->token_acceso);
$retoData = json_decode(ob_get_clean(), true) ?: [];

$frutas = $animales = $colores = [];
$idReto = 0;

if (($retoData['exito'] ?? false) && ($retoData['estado'] ?? '') === 'activo') {
    $idReto = $retoData['id_reto'] ?? 0;

    foreach ($retoData['opciones_frutas'] as $url) {
        $frutas[] = [
            'nombre' => pathinfo($url, PATHINFO_FILENAME),
            'url' => $url
        ];
    }

    foreach ($retoData['opciones_animales'] as $url) {
        $animales[] = [
            'nombre' => pathinfo($url, PATHINFO_FILENAME),
            'url' => $url
        ];
    }

    $hexToNombre = array_flip(AsistenciaController::$colores);
    foreach ($retoData['opciones_colores'] as $hex) {
        $colores[] = [
            'nombre' => $hexToNombre[$hex] ?? $hex,
            'hex' => $hex
        ];
    }
} else {
    // Fallback: mostrar todos los recursos disponibles
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

    foreach (AsistenciaController::$colores as $nombre => $hex) {
        $colores[] = ['nombre' => $nombre, 'hex' => $hex];
    }
}

shuffle($frutas);
shuffle($animales);
shuffle($colores);

$frutas  = array_slice($frutas, 0, 6);
$animales = array_slice($animales, 0, 6);
$colores = array_slice($colores, 0, 6);

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
  gap: 12px;
}

.opciones label {
  cursor: pointer;
  display: inline-block;
  text-align: center;
  transition: transform 0.2s ease;
}

.opciones img, .opciones .color-opcion {
  width: 80px;
  height: 80px;
  border: 2px solid #ccc;
  border-radius: 8px;
  background: #fff;
  transition: all 0.2s ease;
}

.opciones input[type="radio"] {
  display: none;
}

.opciones input[type="radio"]:checked + img,
.opciones input[type="radio"]:checked + .color-opcion {
  border: 4px solid #28a745;
  transform: scale(1.15);
  box-shadow: 0 0 10px rgba(40, 167, 69, 0.7);
  z-index: 2;
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

#mensaje .error {
  background: #f8d7da;
  color: #842029;
  padding: 12px;
  border-radius: 5px;
}

#mensaje .exito {
  background: #d1e7dd;
  color: #0f5132;
  padding: 12px;
  border-radius: 5px;
}
</style>

<div class="container container-main d-flex align-items-center">
  <div class="w-100" style="max-width:600px;margin:auto;">
    <div class="mensaje">
      ✅ Paso 1 Completado: Sabemos que eres tú porque accediste con tu enlace único.
    </div>

    <div class="wizard-header">
      <span class="wizard-step">Paso 2 de 2</span>
      <h2>Verificación de Asistencia</h2>
      <p>Selecciona los elementos que viste en la pantalla compartida del evento.</p>
    </div>

    <form method="POST" action="<?= URL_PATH; ?>validar_reto.php">
      <input type="hidden" name="token" value="<?= $invitacion->token_acceso; ?>">
      <input type="hidden" name="id_reto" value="<?= $idReto; ?>">

      <div class="wizard-section">
        <h3>1️⃣ Selecciona la fruta que viste:</h3>
        <div class="opciones">
          <?php foreach($frutas as $fruta): ?>
            <label>
              <input type="radio" name="fruta" value="<?= $fruta['nombre'] ?>" required>
              <img src="<?= $fruta['url'] ?>" alt="<?= $fruta['nombre'] ?>">
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="wizard-section">
        <h3>2️⃣ Selecciona el color que viste:</h3>
        <div class="opciones">
          <?php foreach($colores as $color): ?>
            <label>
              <input type="radio" name="color" value="<?= $color['nombre'] ?>" required>
              <div class="color-opcion" style="background:<?= $color['hex'] ?>;"></div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="wizard-section">
        <h3>3️⃣ Selecciona el animal que viste:</h3>
        <div class="opciones">
          <?php foreach($animales as $animal): ?>
            <label>
              <input type="radio" name="animal" value="<?= $animal['nombre'] ?>" required>
              <img src="<?= $animal['url'] ?>" alt="<?= $animal['nombre'] ?>">
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <button type="submit" class="boton-verificar">✅ Confirmar Asistencia</button>
    </form>
    <div id="mensaje" class="mt-3"></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  const mensajeDiv = document.getElementById('mensaje');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    mensajeDiv.innerHTML = '';
    const formData = new FormData(form);
    try {
      const respuesta = await fetch(form.action, {
        method: 'POST',
        body: formData
      });
      const datos = await respuesta.json();
      if (datos.exito) {
        mensajeDiv.innerHTML = '<div class="exito">✅ ' + (datos.mensaje || 'Asistencia registrada correctamente.') + '</div>';
        form.reset();
      } else {
        mensajeDiv.innerHTML = '<div class="error">❌ ' + (datos.mensaje || 'La combinación seleccionada no es correcta.') + '</div>';
      }
    } catch (err) {
      mensajeDiv.innerHTML = '<div class="error">❌ Error al procesar la solicitud.</div>';
    }
  });
});
</script>
