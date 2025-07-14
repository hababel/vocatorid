<?php

require_once APP_BASE_PHYSICAL_PATH . '/core/Controller.php';

class KioscoFisicoController extends Controller
{
	private $eventoModel;

	public function __construct()
	{
		$this->verificarSesion();
		$this->eventoModel = $this->modelo('EventoModel');
	}

	/**
	 * Muestra la interfaz del escáner QR del kiosco físico.
	 */
	public function scanner($id_evento = 0)
	{
		if (empty($id_evento)) {
			die('Error: ID de evento no especificado para el kiosco.');
		}

		$evento = $this->eventoModel->obtenerPorId($id_evento);

		if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
			die('Error: Evento no encontrado o no tienes permiso para acceder a este kiosco.');
		}

		$datos = [
			'titulo' => 'Kiosco Físico - ' . $evento->nombre_evento,
			'nombre' => $_SESSION['nombre_organizador'],
			'evento' => $evento
		];
		$this->vistaPanel('kiosco_fisico/scanner', $datos);
	}

	private function verificarSesion()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		if (!isset($_SESSION['id_organizador'])) {
			header('Location: ' . URL_PATH . 'organizador/login');
			exit();
		}
	}
}
