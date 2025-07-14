<?php

require_once APP_BASE_PHYSICAL_PATH . '/core/Controller.php';

class PublicoController extends Controller
{

	private $eventoModel;

	public function __construct()
	{
		// Este controlador es público, por lo que no necesita verificación de sesión.
		$this->eventoModel = $this->modelo('EventoModel');
	}

	/**
	 * Muestra la página pública (micrositio) de un evento.
	 * @param int $id_evento
	 */
	public function evento($id_evento = 0)
	{
		if (empty($id_evento)) {
			die('Evento no especificado.');
		}

		$evento = $this->eventoModel->obtenerPorId($id_evento);

		// Validar que el evento exista.
		if (!$evento) {
			die('Este evento no existe o no está disponible actualmente.');
		}

		// CORRECCIÓN: Se elimina el bloqueo y en su lugar se pasa el estado a la vista.
		$datos = [
			'titulo' => $evento->nombre_evento,
			'evento' => $evento
		];

		// Usamos la plantilla pública (header.php, footer.php)
		$this->vista('publico/evento', $datos);
	}
}
