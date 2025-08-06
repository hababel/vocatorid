<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];

$api_url  = URL_PATH . 'get_codigo_reto.php?id_evento=' . $evento->id;
$token_data = @json_decode(@file_get_contents($api_url), true) ?: [];

// Preparar datos iniciales de la clave dinámica
$fruta = [
    'nombre' => $token_data['fruta'] ?? '',
    'url'    => $token_data['fruta_img'] ?? ''
];
$animal = [
    'nombre' => $token_data['animal'] ?? '',
    'url'    => $token_data['animal_img'] ?? ''
];
$color = $token_data['color_hex'] ?? '';

// Asegurar que las URLs sean absolutas y usen HTTPS
foreach (['fruta', 'animal'] as $tipo) {
    if (!empty(${$tipo}['url']) && stripos(${$tipo}['url'], 'https://') !== 0) {
        ${$tipo}['url'] = preg_replace('/^http:\/\//i', 'https://', ${$tipo}['url']);
        if (stripos(${$tipo}['url'], 'https://') !== 0) {
            ${$tipo}['url'] = URL_PATH . ltrim(${$tipo}['url'], '/');
        }
    }
}

$tiempo_restante = isset($token_data['tiempo_restante']) ? (int)$token_data['tiempo_restante'] : 40;
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
      <div class="item" id="fruta-container"></div>
      <div class="item" id="color-container"></div>
      <div class="item" id="animal-container"></div>
    </div>

    <div class="progreso">
      <div id="barra-progreso"></div>
      <span id="contador"></span>
    </div>
  </div>
</div>

<script>
const claveDinamica = {
  fruta: {
    nombre: "<?= htmlspecialchars($fruta['nombre'], ENT_QUOTES, 'UTF-8') ?>",
    url: "<?= htmlspecialchars($fruta['url'], ENT_QUOTES, 'UTF-8') ?>"
  },
  animal: {
    nombre: "<?= htmlspecialchars($animal['nombre'], ENT_QUOTES, 'UTF-8') ?>",
    url: "<?= htmlspecialchars($animal['url'], ENT_QUOTES, 'UTF-8') ?>"
  },
  color: "<?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?>",
  tiempo_restante: <?= $tiempo_restante ?>
};

function esURLValida(url) {
  return /^https?:\/\//i.test(url);
}

document.addEventListener('DOMContentLoaded', () => {
  const frutaC = document.getElementById('fruta-container');
  const colorC = document.getElementById('color-container');
  const animalC = document.getElementById('animal-container');
  const barra = document.getElementById('barra-progreso');
  const contador = document.getElementById('contador');
  const fondo = document.getElementById('fondo-dinamico');

  let tiempo = parseInt(claveDinamica.tiempo_restante, 10) || 40;
  const fondos = ["#1e3c72", "#2a5298", "#0f2027", "#4b6cb7", "#182848"];
  let indiceFondo = 0;

  function cambiarFondo() {
    fondo.style.background = fondos[indiceFondo];
    indiceFondo = (indiceFondo + 1) % fondos.length;
  }

  function actualizarVista() {
    contador.textContent = tiempo;
    barra.style.width = (tiempo / 40) * 100 + '%';
    if (tiempo <= 5) barra.style.background = '#dc3545';
    else if (tiempo <= 15) barra.style.background = '#ffc107';
    else barra.style.background = '#28a745';
    barra.classList.toggle('parpadeo', tiempo <= 10);
  }

  function renderClave(data) {
    frutaC.innerHTML = '';
    colorC.innerHTML = '';
    animalC.innerHTML = '';

    if (data.fruta && esURLValida(data.fruta.url)) {
      const imgFruta = document.createElement('img');
      imgFruta.src = data.fruta.url;
      imgFruta.alt = data.fruta.nombre;
      imgFruta.style.width = '180px';
      imgFruta.style.border = '2px solid #ccc';
      frutaC.appendChild(imgFruta);
    } else {
      frutaC.innerHTML = "<p style='color:red;'>Imagen de fruta no disponible</p>";
    }

    if (data.color) {
      const btnColor = document.createElement('div');
      btnColor.className = 'color-box';
      btnColor.style.background = data.color;
      btnColor.style.width = '180px';
      btnColor.style.height = '180px';
      btnColor.style.border = '3px solid #000';
      btnColor.style.borderRadius = '10px';
      colorC.appendChild(btnColor);
    } else {
      colorC.innerHTML = "<p style='color:red;'>Color no disponible</p>";
    }

    if (data.animal && esURLValida(data.animal.url)) {
      const imgAnimal = document.createElement('img');
      imgAnimal.src = data.animal.url;
      imgAnimal.alt = data.animal.nombre;
      imgAnimal.style.width = '180px';
      imgAnimal.style.border = '2px solid #ccc';
      animalC.appendChild(imgAnimal);
    } else {
      animalC.innerHTML = "<p style='color:red;'>Imagen de animal no disponible</p>";
    }
  }

  async function actualizarToken() {
    try {
      const response = await fetch('<?= URL_PATH; ?>get_codigo_reto.php?id_evento=<?= $evento->id; ?>');
      if (!response.ok) throw new Error('Error de red');
      const data = await response.json();
      if (data.estado === 'activo') {
        const nuevo = {
          fruta: { nombre: data.fruta, url: data.fruta_img },
          animal: { nombre: data.animal, url: data.animal_img },
          color: data.color_hex,
          tiempo_restante: data.tiempo_restante
        };
        renderClave(nuevo);
        tiempo = nuevo.tiempo_restante || 40;
        cambiarFondo();
        actualizarVista();
      }
    } catch (err) {
      console.error('Error al obtener el código:', err);
    }
  }

  renderClave(claveDinamica);
  cambiarFondo();
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
