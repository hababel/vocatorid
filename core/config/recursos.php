<?php
// core/config/recursos.php

/**
 * =================================================================
 * NUEVA FUNCIÓN: Generador de Contenido para Calendario (.ics)
 * =================================================================
 *
 * Genera el contenido de un archivo iCalendar (.ics) estandarizado.
 * Este contenido se puede adjuntar a un correo para que el receptor
 * pueda añadir el evento a su calendario fácilmente.
 *
 * @param string $fechaInicio Fecha y hora de inicio del evento (formato 'Y-m-d H:i:s').
 * @param string $fechaFin Fecha y hora de fin del evento (formato 'Y-m-d H:i:s').
 * @param string $titulo El nombre del evento.
 * @param string $descripcion Una breve descripción del evento.
 * @param string $ubicacion La ubicación del evento.
 * @return string El contenido del archivo .ics como un string.
 */


function calcularDistanciaHaversine($lat1, $lon1, $lat2, $lon2)
{
	$radioTierra = 6371000; // Radio de la Tierra en metros
	$dLat = deg2rad($lat2 - $lat1);
	$dLon = deg2rad($lon2 - $lon1);
	$a = sin($dLat / 2) * sin($dLat / 2) +
		cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
		sin($dLon / 2) * sin($dLon / 2);
	$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
	return $radioTierra * $c; // Distancia en metros
}

function generarContenidoIcs($fechaInicio, $fechaFin, $titulo, $descripcion, $ubicacion)
{
	// Formatear las fechas al formato UTC requerido por iCalendar (ej: 20250715T090000Z)
	$inicioUTC = gmdate('Ymd\THis\Z', strtotime($fechaInicio));
	$finUTC = gmdate('Ymd\THis\Z', strtotime($fechaFin));
	$ahoraUTC = gmdate('Ymd\THis\Z');

	// UID único para el evento.
	$uid = uniqid() . '@vocatorID.com';

	// Limpiar el texto para que sea compatible con el formato .ics
	$titulo = preg_replace('/([\,;])/', '\\\$1', $titulo);
	$descripcion = preg_replace('/([\,;])/', '\\\$1', $descripcion);
	$ubicacion = preg_replace('/([\,;])/', '\\\$1', $ubicacion);

	$icsContent = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//VOCATOR ID//App v1.0//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTAMP:{$ahoraUTC}
DTSTART:{$inicioUTC}
DTEND:{$finUTC}
UID:{$uid}
SUMMARY:{$titulo}
DESCRIPTION:{$descripcion}
LOCATION:{$ubicacion}
STATUS:CONFIRMED
SEQUENCE:0
END:VEVENT
END:VCALENDAR";

	return $icsContent;
}

/**
 * =================================================================
 * NUEVA FUNCIÓN: Repositorio de Recursos para la Clave Visual
 * =================================================================
 *
 * Devuelve un array con las categorías y los nombres de las imágenes
 * disponibles para el desafío de la "Clave Visual".
 *
 * @return array Un array asociativo de categorías y sus imágenes.
 */
function obtenerRecursosClaveVisual()
{
	return [
		'animales' => [
			'Gato.jpg',
			'Leon.jpg',
			'Oso.jpg',
			'Perro.jpg',
			'Caballo.jpg',
			'Tigre.jpg'
		],
		'frutas' => [
			'Manzana.jpg',
			'Banano.jpg',
			'Naranja.jpg',
			'Uvas.jpg',
			'Limon.jpg',
			'Pina.jpg'
		],
		'figuras' => [
			'Circulo.jpg',
			'Cuadrado.jpg',
			'Triangulo.jpg',
			'Estrella.jpg',
			'Corazon.jpg',
			'Hexagono.jpg'
		]
		// Se pueden añadir más categorías aquí en el futuro.
	];
}

/**
 * Genera un código visual aleatorio en el formato
 * [Fruta]-[Color]-[Animal] utilizando los recursos
 * disponibles en el sistema.
 *
 * @param array $colores Lista de colores a utilizar.
 * @return string Código generado.
 */
function generarCodigoFrutasColoresAnimales($colores)
{
        $recursos = obtenerRecursosClaveVisual();

        $frutaArchivo = $recursos['frutas'][array_rand($recursos['frutas'])];
        $animalArchivo = $recursos['animales'][array_rand($recursos['animales'])];

        $fruta = basename($frutaArchivo, '.jpg');
        $animal = basename($animalArchivo, '.jpg');

        // $colores es un array asociativo nombre => hex
        $colorNombre = array_rand($colores);
        $colorHex = $colores[$colorNombre];

        $codigo = $fruta . '-' . $colorNombre . '-' . $animal;

        return [
                'codigo' => $codigo,
                'fruta_img'  => URL_PATH . 'core/img/clave_visual/frutas/' . $frutaArchivo,
                'color_hex'  => $colorHex,
                'animal_img' => URL_PATH . 'core/img/clave_visual/animales/' . $animalArchivo,
                'fruta' => $fruta,
                'animal' => $animal,
                'color_nombre' => $colorNombre
        ];
}

/**
 * Devuelve las rutas de imágenes y el color HEX a partir del código
 * almacenado en la base de datos.
 */
function datosDesdeCodigoVisual($codigo, $colores)
{
        $partes = explode('-', $codigo);
        if (count($partes) !== 3) {
                return null;
        }

        list($fruta, $colorNombre, $animal) = $partes;

        return [
                'fruta_img'  => URL_PATH . 'core/img/clave_visual/frutas/' . $fruta . '.jpg',
                'color_hex'  => $colores[$colorNombre] ?? '#000000',
                'animal_img' => URL_PATH . 'core/img/clave_visual/animales/' . $animal . '.jpg',
                'fruta' => $fruta,
                'animal' => $animal,
                'color_nombre' => $colorNombre
        ];
}

/**
 * Genera un listado de opciones que incluye la respuesta correcta
 * y un número de distractores aleatorios.
 *
 * @param array $lista Lista total de elementos disponibles.
 * @param mixed $correcta Elemento que debe estar presente en el listado.
 * @param int $cantidad Cantidad total de opciones a generar (incluye la correcta). Por defecto 6.
 */
function generarOpcionesLista(array $lista, $correcta, $cantidad = 6)
{
        $opciones = [$correcta];
        $resto = array_values(array_diff($lista, [$correcta]));
        shuffle($resto);
        $opciones = array_merge($opciones, array_slice($resto, 0, max(0, $cantidad - 1)));
        shuffle($opciones);
        return $opciones;
}

