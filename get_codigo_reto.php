<?php
require_once __DIR__ . '/core/config/config.php';
require_once APP_BASE_PHYSICAL_PATH . '/core/config/conn.php';
require_once APP_BASE_PHYSICAL_PATH . '/core/Model.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/model/RetoModel.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/controller/AsistenciaController.php';
require_once APP_BASE_PHYSICAL_PATH . '/core/config/recursos.php';

header('Content-Type: application/json');

$id_evento = isset($_GET['id_evento']) ? (int)$_GET['id_evento'] : 0;

$retoModel = new RetoModel();
$reto = $retoModel->obtenerActivoPorEvento($id_evento);

if (!$reto) {
    echo json_encode(['estado' => 'inactivo']);
    exit;
}

$timestamp = isset($reto->codigo_actual_timestamp) ? strtotime($reto->codigo_actual_timestamp) : time();
$ahora = time();

if (empty($reto->codigo_actual) || ($ahora - $timestamp) >= 40) {
    $datos = generarCodigoFrutasColoresAnimales(AsistenciaController::$colores);
    $codigo = $datos['codigo'];
    unset($datos['codigo']);
    if (method_exists($retoModel, 'actualizarCodigoYFecha')) {
        $retoModel->actualizarCodigoYFecha($reto->id, $codigo);
    } else {
        $retoModel->actualizarCodigo($reto->id, $codigo);
    }
    $reto->codigo_actual = $codigo;
    $timestamp = $ahora;
} else {
    $datos = datosDesdeCodigoVisual($reto->codigo_actual, AsistenciaController::$colores);
}

$recursos = obtenerRecursosClaveVisual();
$listaFrutas = array_map(fn($f) => basename($f, '.jpg'), $recursos['frutas']);
$listaAnimales = array_map(fn($a) => basename($a, '.jpg'), $recursos['animales']);
$listaColores = array_values(AsistenciaController::$colores);

$opciones_frutas = array_map(
    fn($n) => URL_PATH . 'core/img/clave_visual/frutas/' . $n . '.jpg',
    generarOpcionesLista($listaFrutas, $datos['fruta'])
);
$opciones_animales = array_map(
    fn($n) => URL_PATH . 'core/img/clave_visual/animales/' . $n . '.jpg',
    generarOpcionesLista($listaAnimales, $datos['animal'])
);
$opciones_colores = generarOpcionesLista($listaColores, $datos['color_hex']);

$tiempo_restante = 40 - ($ahora - $timestamp);
if ($tiempo_restante < 0) {
    $tiempo_restante = 0;
}

echo json_encode(array_merge($datos, [
    'tiempo_restante' => $tiempo_restante,
    'estado' => 'activo',
    'opciones_frutas' => $opciones_frutas,
    'opciones_animales' => $opciones_animales,
    'opciones_colores' => $opciones_colores
]));
