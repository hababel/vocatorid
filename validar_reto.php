<?php
require_once __DIR__ . '/core/config/config.php';
require_once APP_BASE_PHYSICAL_PATH . '/core/config/conn.php';
require_once APP_BASE_PHYSICAL_PATH . '/core/Model.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/model/InvitacionModel.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/model/RetoModel.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/model/RegistroAsistenciaModel.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/model/RegistroRetoModel.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

$token = $_POST['token'] ?? '';
$id_reto = (int)($_POST['id_reto'] ?? 0);
$fruta = trim($_POST['fruta'] ?? '');
$color = trim($_POST['color'] ?? '');
$animal = trim($_POST['animal'] ?? '');

$invitacionModel = new InvitacionModel();
$retoModel = new RetoModel();
$registroRetoModel = new RegistroRetoModel();
$registroAsistenciaModel = new RegistroAsistenciaModel();

$invitacion = $invitacionModel->obtenerPorToken($token);
if (!$invitacion) {
    echo json_encode(['exito' => false, 'mensaje' => 'Token inválido.']);
    exit;
}

$reto = $retoModel->obtenerActivoPorEvento($invitacion->id_evento);
if (!$reto || $reto->id != $id_reto) {
    echo json_encode(['exito' => false, 'mensaje' => 'Reto fuera de tiempo.']);
    exit;
}

if ($registroRetoModel->yaCompletado($reto->id, $invitacion->id)) {
    echo json_encode(['exito' => false, 'mensaje' => 'Reto ya completado.']);
    exit;
}

$partes = explode('-', $reto->codigo_actual);
if (count($partes) === 3) {
    list($frutaCor, $colorCor, $animalCor) = $partes;
} else {
    $frutaCor = $colorCor = $animalCor = '';
}

$codigoIngresado = $fruta . '-' . $color . '-' . $animal;
$correcto = (strcasecmp($frutaCor, $fruta) === 0 &&
             strcasecmp($colorCor, $color) === 0 &&
             strcasecmp($animalCor, $animal) === 0) ? 1 : 0;
$registroRetoModel->crear($reto->id, $invitacion->id, $codigoIngresado, $_SERVER['REMOTE_ADDR'] ?? '', $correcto);

if ($correcto == 1 && !$registroAsistenciaModel->yaRegistrado($invitacion->id)) {
    $registroAsistenciaModel->crear($invitacion->id, 'Reto ' . $reto->id);
    $invitacionModel->marcarAsistenciaVerificada($invitacion->id);
}

if ($correcto == 1) {
    echo json_encode([
        'exito'   => true,
        'mensaje' => '¡Asistencia registrada con éxito!'
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'exito'   => false,
        'mensaje' => 'La combinación seleccionada no coincide con la clave dinámica.'
    ], JSON_UNESCAPED_UNICODE);
}
