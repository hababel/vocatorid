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
        $actualizado = $retoModel->actualizarCodigoYFecha($reto->id, $codigo);
        if (!$actualizado) {
            $retoModel->actualizarCodigo($reto->id, $codigo);
        }
    } else {
        $retoModel->actualizarCodigo($reto->id, $codigo);
    }
    $reto->codigo_actual = $codigo;
    $timestamp = $ahora;
} else {
    $datos = datosDesdeCodigoVisual($reto->codigo_actual, AsistenciaController::$colores);
}

$tiempo_restante = 40 - ($ahora - $timestamp);
if ($tiempo_restante < 0) {
    $tiempo_restante = 0;
}

echo json_encode([
    'estado' => 'activo',
    'fruta_img' => $datos['fruta_img'],
    'animal_img' => $datos['animal_img'],
    'color_hex' => $datos['color_hex'],
    'fruta' => $datos['fruta'],
    'animal' => $datos['animal'],
    'color_nombre' => $datos['color_nombre'],
    'tiempo_restante' => $tiempo_restante
]);
