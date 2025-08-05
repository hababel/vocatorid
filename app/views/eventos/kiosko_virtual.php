<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];

$base_url = URL_PATH . 'core/img/clave_visual/';
$api_url  = URL_PATH . 'get_codigo_reto.php?id_evento=' . $evento->id;
$token_data = @json_decode(@file_get_contents($api_url), true) ?: [];

$fruta = ['url' => '', 'nombre' => ''];
$animal = ['url' => '', 'nombre' => ''];
$color = '#ffffff';
$tiempo_restante = 40;

if (($token_data['estado'] ?? '') === 'activo') {
    $fruta['url']  = $token_data['fruta_img'];
    $animal['url'] = $token_data['animal_img'];
    $color         = $token_data['color_hex'];
    $tiempo_restante = $token_data['tiempo_restante'];

    if (!filter_var($fruta['url'], FILTER_VALIDATE_URL)) {
        $fruta['url'] = $base_url . 'frutas/' . $fruta['url'];
    }
    if (!filter_var($animal['url'], FILTER_VALIDATE_URL)) {
        $animal['url'] = $base_url . 'animales/' . $animal['url'];
    }

    $fruta['nombre']  = ucfirst(pathinfo($fruta['url'], PATHINFO_FILENAME));
    $animal['nombre'] = ucfirst(pathinfo($animal['url'], PATHINFO_FILENAME));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kiosko Virtual</title>
    <style>
body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

#fondo-dinamico {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: background 1s ease;
}

.kiosko-card {
  background: #fff;
  border-radius: 15px;
  padding: 30px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  text-align: center;
  max-width: 800px;
  width: 90%;
}

.titulo-kiosko {
  font-size: 28px;
  color: #222;
  margin-bottom: 20px;
}

.clave-visual {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 40px;
  margin: 25px 0;
}

.item img {
  width: 180px;
  height: auto;
  object-fit: contain;
}

.color-box {
  width: 180px;
  height: 180px;
  border: 3px solid #000;
  border-radius: 12px;
}

.progreso {
  margin-top: 20px;
  width: 100%;
}

#barra-progreso {
  width: 100%;
  height: 25px;
  background: #28a745;
  border-radius: 5px;
  transition: width 1s linear;
}

#barra-progreso.parpadeo {
  animation: blink 1s infinite;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

#contador {
  display: block;
  font-size: 24px;
  margin-top: 8px;
  color: #444;
}
    </style>
</head>
<body>
<div class="kiosko-wrapper" id="fondo-dinamico">
  <div class="kiosko-card">
    <h1 class="titulo-kiosko">🔹 Clave Dinámica del Reto Actual 🔹</h1>

    <div class="clave-visual">
      <div class="item"><img id="fruta" src="<?= htmlspecialchars($fruta['url']) ?>" alt="<?= htmlspecialchars($fruta['nombre']) ?>"></div>
      <div class="item"><button id="color-boton" class="color-box" style="background:<?= htmlspecialchars($color) ?>;"></button></div>
      <div class="item"><img id="animal" src="<?= htmlspecialchars($animal['url']) ?>" alt="<?= htmlspecialchars($animal['nombre']) ?>"></div>
    </div>

    <div class="progreso">
      <div id="barra-progreso"></div>
      <span id="contador"><?= (int)$tiempo_restante ?></span>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  let tiempo = parseInt(document.getElementById('contador').textContent, 10) || 40;
  const barra = document.getElementById('barra-progreso');
  const contador = document.getElementById('contador');
  const frutaElem = document.getElementById('fruta');
  const animalElem = document.getElementById('animal');
  const colorBtn = document.getElementById('color-boton');
  const fondo = document.getElementById('fondo-dinamico');

  const fondos = ["#1e3c72", "#2a5298", "#0f2027", "#4b6cb7", "#182848"];
  let indiceFondo = 0;

  function cambiarFondo() {
    fondo.style.background = fondos[indiceFondo];
    indiceFondo = (indiceFondo + 1) % fondos.length;
  }
  cambiarFondo();

  function actualizarVista() {
    contador.textContent = tiempo;
    barra.style.width = (tiempo / 40) * 100 + '%';
    if (tiempo <= 5) barra.style.background = '#dc3545';
    else if (tiempo <= 15) barra.style.background = '#ffc107';
    else barra.style.background = '#28a745';
    barra.classList.toggle('parpadeo', tiempo <= 10);
  }

  async function actualizarToken() {
    try {
      const response = await fetch('<?= URL_PATH; ?>get_codigo_reto.php?id_evento=<?= $evento->id; ?>');
      if (!response.ok) throw new Error('Error de red');
      const data = await response.json();
      if (data.estado === 'activo') {
        let frutaUrl = data.fruta_img;
        let animalUrl = data.animal_img;
        if (!/^https?:\/\//i.test(frutaUrl)) {
          frutaUrl = '<?= $base_url ?>frutas/' + frutaUrl;
        }
        if (!/^https?:\/\//i.test(animalUrl)) {
          animalUrl = '<?= $base_url ?>animales/' + animalUrl;
        }
        frutaElem.src = frutaUrl;
        frutaElem.alt = frutaUrl.split('/').pop().split('.')[0];
        animalElem.src = animalUrl;
        animalElem.alt = animalUrl.split('/').pop().split('.')[0];
        colorBtn.style.background = data.color_hex;
        tiempo = data.tiempo_restante || 40;
        cambiarFondo();
        actualizarVista();
      }
    } catch (err) {
      console.error('Error al obtener el código:', err);
    }
  }

  actualizarVista();
  setInterval(() => {
    tiempo--;
    if (tiempo <= 0) {
      actualizarToken();
      tiempo = 40;
    }
    actualizarVista();
  }, 1000);
});
</script>
</body>
</html>
