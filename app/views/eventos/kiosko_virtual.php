<?php
// El header.php se carga automáticamente desde el controlador.
$evento = $datos['evento'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clave Dinámica</title>
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            text-align: center;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        .kiosko-container {
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .clave-panel {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 40px;
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .clave-panel img {
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
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .progreso-barra {
            flex-grow: 1;
            height: 25px;
            background: #28a745;
            border-radius: 5px;
            transition: width 1s linear, background 0.5s ease;
        }

        #contador {
            font-size: 26px;
            font-weight: bold;
            min-width: 50px;
        }

        .blink {
            animation: blink 1s steps(2, start) infinite;
        }

        @keyframes blink {
            to {
                visibility: hidden;
            }
        }
    </style>
</head>
<body>
    <div class="kiosko-container">
        <h1>🔹 Clave Dinámica del Reto Actual 🔹</h1>

        <div class="clave-panel">
            <div class="item"><img id="fruta" src="" alt="Fruta"></div>
            <div class="item"><div id="color-box" class="color-box"></div></div>
            <div class="item"><img id="animal" src="" alt="Animal"></div>
        </div>

        <div class="progreso-container">
            <div class="progreso-barra" id="progreso"></div>
            <span id="contador">40</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fruta = document.getElementById('fruta');
            const animal = document.getElementById('animal');
            const colorBox = document.getElementById('color-box');
            const barra = document.getElementById('progreso');
            const contador = document.getElementById('contador');
            let tiempo = 40;
            let intervalo = null;

            function actualizarBarra() {
                contador.textContent = tiempo;
                const ancho = (tiempo / 40) * 100;
                barra.style.width = ancho + '%';

                barra.classList.remove('blink');
                if (tiempo <= 5) {
                    barra.style.background = '#dc3545';
                    barra.classList.add('blink');
                } else if (tiempo <= 15) {
                    barra.style.background = '#ffc107';
                } else {
                    barra.style.background = '#28a745';
                }
            }

            function iniciar(tRestante) {
                clearInterval(intervalo);
                tiempo = tRestante;
                actualizarBarra();
                intervalo = setInterval(() => {
                    tiempo--;
                    if (tiempo < 0) {
                        clearInterval(intervalo);
                        actualizarToken();
                    } else {
                        actualizarBarra();
                    }
                }, 1000);
            }

            async function actualizarToken() {
                try {
                    const resp = await fetch('<?= URL_PATH; ?>get_codigo_reto.php?id_evento=<?= $evento->id; ?>');
                    if (!resp.ok) throw new Error('Error de red');
                    const data = await resp.json();
                    if (data.estado === 'activo') {
                        fruta.src = data.fruta_img;
                        animal.src = data.animal_img;
                        colorBox.style.background = data.color_hex;
                        iniciar(data.tiempo_restante);
                    }
                } catch (err) {
                    console.error('Error al obtener el código:', err);
                }
            }

            actualizarToken();
        });
    </script>
</body>
</html>

