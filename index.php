<?php


session_start();
require_once("core/config/config.php");
require_once("core/config/router.php");
require_once("core/config/conn.php");
require_once("core/config/recursos.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$router = new router();
$controllerName = $router->getController();
$methodName = $router->getMethod();
$params = $router->getParams();

// --- LISTA BLANCA ACTUALIZADA ---
$rutas_publicas = [
	// Controlador 'organizador'
	'organizador/index',
	'organizador/login',
	'organizador/registro',
	'organizador/crear',
	'organizador/procesarLogin',
	'organizador/recuperarPassword',
	'organizador/solicitarRecuperacion',
	'organizador/resetPassword',
	'organizador/actualizarPassword',
	'organizador/noAutorizado',

	// Controlador 'asistencia'
	'asistencia/bienvenida',
	'asistencia/registroAnonimo',
	'asistencia/procesarRegistroAnonimo',
	'asistencia/procesarVerificacion', // API para kiosco virtual
	'asistencia/procesarQrPersonal', // API para kiosco físico

	// Controlador 'invitacion'
	'invitacion/responder'
];

$ruta_actual = $controllerName . '/' . $methodName;

// Se añade una excepción para el Kiosco Físico, que es privado pero no sigue el flujo de la lista blanca
if ($ruta_actual === 'kioscoFisico/scanner' && !isset($_SESSION['id_organizador'])) {
	header('Location: ' . URL_PATH . 'organizador/login');
	exit();
}

if (!in_array($ruta_actual, $rutas_publicas) && !isset($_SESSION['id_organizador'])) {
	header('Location: ' . URL_PATH . 'organizador/login');
	exit();
}

// CORRECCIÓN DEFINITIVA: Se usa ucfirst() para manejar correctamente los nombres en camelCase.
$controllerClassName = ucfirst($controllerName) . 'Controller';
$path_controller = "app/controller/" . $controllerClassName . ".php";

if (file_exists($path_controller)) {
	require_once $path_controller;

	$controller = new $controllerClassName;

	if (method_exists($controller, $methodName)) {
		call_user_func_array([$controller, $methodName], $params);
	} else {
		header("Location:" . URL_PATH . "error/metodoNoEncontrado", true, 301);
		die();
	}
} else {
	header("Location:" . URL_PATH . "error/controladorNoEncontrado", true, 301);
	die();
}
