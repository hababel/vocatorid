<?php
// El header.php se carga automáticamente desde el controlador.
$fruta = $datos['fruta'];
$animal = $datos['animal'];
$color = $datos['color'];
?>

<style>
body {
  background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
  color: white;
  font-family: 'Segoe UI', sans-serif;
  text-align: center;
  padding: 20px;
}

.kiosko-container {
  max-width: 900px;
  margin: auto;
  padding: 20px;
}

h1 {
  font-size: 30px;
  margin-bottom: 20px;
  color: #fff;
}

.clave-panel {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 40px;
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

.item {
  text-align: center;
}

.item img {
  width: 150px;
  height: auto;
}

.color-box {
  width: 150px;
  height: 150px;
  border: 3px solid #000;
  border-radius: 10px;
}

.progreso-container {
  margin-top: 30px;
  width: 80%;
  margin-left: auto;
  margin-right: auto;
  position: relative;
}

.progreso-barra {
  width: 100%;
  height: 25px;
  background: #28a745;
  border-radius: 5px;
  transition: width 1s linear;
}

#contador {
  font-size: 28px;
  font-weight: bold;
  display: block;
  margin-top: 10px;
}
</style>
</head>
<body>
<div class="kiosko-container">
  <h1>🔹 Clave Dinámica del Reto Actual 🔹</h1>

  <div class="clave-panel">
    <div class="item">
      <img src="<?= $fruta['url'] ?>" alt="<?= $fruta['nombre'] ?>">
      <p><?= ucfirst($fruta['nombre']) ?></p>
    </div>
    <div class="item">
      <button class="color-box" style="background:<?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?>;"></button>
      <p>Color</p>
    </div>
    <div class="item">
      <img src="<?= $animal['url'] ?>" alt="<?= $animal['nombre'] ?>">
      <p><?= ucfirst($animal['nombre']) ?></p>
    </div>
  </div>

  <div class="progreso-container">
    <div class="progreso-barra" id="progreso"></div>
    <span id="contador">40</span>
  </div>
</div>

<script>
let tiempo = 40;
const barra = document.getElementById('progreso');
const contador = document.getElementById('contador');

const intervalo = setInterval(() => {
  tiempo--;
  contador.textContent = tiempo;

  let ancho = (tiempo / 40) * 100;
  barra.style.width = ancho + '%';

  if (tiempo <= 15 && tiempo > 5) barra.style.background = '#ffc107';
  if (tiempo <= 5) barra.style.background = '#dc3545';

  if (tiempo <= 0) clearInterval(intervalo);
}, 1000);
</script>
</body>
</html>
