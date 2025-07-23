<?php

require_once APP_BASE_PHYSICAL_PATH . '/core/Controller.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/services/BrevoMailService.php';

class InvitacionController extends Controller
{

	private $invitacionModel;
	private $eventoModel;
	private $contactoModel;
	private $mailService;

	public function __construct()
	{
		$this->invitacionModel = $this->modelo('InvitacionModel');
		$this->eventoModel = $this->modelo('EventoModel');
		$this->contactoModel = $this->modelo('ContactoModel');
		$this->mailService = new BrevoMailService();
	}

	public function agregar()
	{
		$this->verificarSesion();

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->redireccionar('organizador/panel');
			return;
		}

		$id_evento = $_POST['id_evento'] ?? 0;
		$modo = $_POST['modo_agregar'] ?? '';
		$enviar_ahora = isset($_POST['enviar_invitacion_ahora']);
		$id_organizador = $_SESSION['id_organizador'];

		$evento = $this->eventoModel->obtenerPorId($id_evento);
		if (!$evento || $evento->id_organizador != $id_organizador) {
			$this->crearMensaje('error', 'Evento no válido o no tienes permiso.');
			$this->redireccionar('organizador/panel');
			return;
		}

		$nuevas_invitaciones = [];
		$invitados_ya_existentes = 0;
		$contactos_creados = 0;

		switch ($modo) {
			case 'seleccionar':
				$ids_contactos = $_POST['contactos_ids'] ?? [];
				if (empty($ids_contactos)) {
					$this->crearMensaje('error', 'No seleccionaste ningún contacto.');
					$this->redireccionar('evento/gestionar/' . $id_evento);
					return;
				}
				foreach ($ids_contactos as $id_contacto) {
					if ($this->invitacionModel->obtenerPorEventoYContacto($id_evento, $id_contacto)) {
						$invitados_ya_existentes++;
						continue;
					}
					$token = bin2hex(random_bytes(32));
					$id_invitacion = $this->invitacionModel->crear($id_evento, $id_contacto, $token);
					if ($id_invitacion) {
						$nuevas_invitaciones[] = $this->invitacionModel->obtenerPorId($id_invitacion);
					}
				}
				break;

			case 'manual':
				$nombre = trim($_POST['nombre_manual']);
				$email = trim($_POST['email_manual']);
				if (empty($nombre) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$this->crearMensaje('error', 'El nombre y el correo electrónico son obligatorios y deben ser válidos.');
					$this->redireccionar('evento/gestionar/' . $id_evento);
					return;
				}

				$id_contacto = null;
				$contacto_existente = $this->contactoModel->obtenerPorEmailYOrganizador($email, $id_organizador);

				if ($contacto_existente) {
					$id_contacto = $contacto_existente->id;
					$this->crearMensaje('info', "El contacto '{$email}' ya existía en tu libreta y ha sido invitado al evento.");
				} else {
					$datos_contacto = [
						'id_organizador' => $id_organizador,
						'nombre' => $nombre,
						'email' => $email,
						'telefono' => trim($_POST['telefono_manual'] ?? ''),
						'acepta_habeas_data' => 0,
						'fuente_registro' => 'Manual',
						'lote_importacion' => null,
						'id_evento_origen' => $id_evento
					];
					$id_contacto = $this->contactoModel->crear($datos_contacto);
					if ($id_contacto) $contactos_creados++;
				}

				if ($id_contacto) {
					if ($this->invitacionModel->obtenerPorEventoYContacto($id_evento, $id_contacto)) {
						$invitados_ya_existentes++;
					} else {
						$token = bin2hex(random_bytes(32));
						$id_invitacion = $this->invitacionModel->crear($id_evento, $id_contacto, $token);
						if ($id_invitacion) {
							$nuevas_invitaciones[] = $this->invitacionModel->obtenerPorId($id_invitacion);
						}
					}
				}
				break;

			case 'csv':
				if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] == UPLOAD_ERR_OK) {
					$archivo = $_FILES['archivo_csv']['tmp_name'];
					$lote_id = uniqid('csv_evento_');

					if (($gestor = fopen($archivo, "r")) !== FALSE) {
						fgetcsv($gestor, 1000, ";");
						while (($datos_fila = fgetcsv($gestor, 1000, ";")) !== FALSE) {
							if (count($datos_fila) >= 2 && !empty($datos_fila[0]) && filter_var($datos_fila[1], FILTER_VALIDATE_EMAIL)) {
								$email_csv = trim($datos_fila[1]);
								$nombre_csv = trim($datos_fila[0]);

								$id_contacto_csv = null;
								$contacto_existente_csv = $this->contactoModel->obtenerPorEmailYOrganizador($email_csv, $id_organizador);

								if ($contacto_existente_csv) {
									$id_contacto_csv = $contacto_existente_csv->id;
								} else {
									$datos_contacto_csv = [
										'id_organizador' => $id_organizador,
										'nombre' => $nombre_csv,
										'email' => $email_csv,
										'telefono' => isset($datos_fila[2]) ? trim($datos_fila[2]) : '',
										'acepta_habeas_data' => 0,
										'fuente_registro' => 'Importacion_CSV',
										'lote_importacion' => $lote_id,
										'id_evento_origen' => $id_evento
									];
									$id_contacto_csv = $this->contactoModel->crear($datos_contacto_csv);
									if ($id_contacto_csv) $contactos_creados++;
								}

								if ($id_contacto_csv) {
									if ($this->invitacionModel->obtenerPorEventoYContacto($id_evento, $id_contacto_csv)) {
										$invitados_ya_existentes++;
									} else {
										$token = bin2hex(random_bytes(32));
										$id_invitacion = $this->invitacionModel->crear($id_evento, $id_contacto_csv, $token);
										if ($id_invitacion) {
											$nuevas_invitaciones[] = $this->invitacionModel->obtenerPorId($id_invitacion);
										}
									}
								}
							}
						}
						fclose($gestor);
					}
				} else {
					$this->crearMensaje('error', 'Error al subir el archivo CSV.');
					$this->redireccionar('evento/gestionar/' . $id_evento);
					return;
				}
				break;
		}

		$envios_exitosos = 0;
		if ($enviar_ahora && !empty($nuevas_invitaciones)) {
			foreach ($nuevas_invitaciones as $invitacion) {
				if ($this->_enviarCorreoInvitacion($invitacion, $evento, $invitacion->token_acceso)) {
					$this->invitacionModel->actualizarFechaInvitacion($invitacion->id);
					$envios_exitosos++;
				}
			}
		}

		if (!isset($_SESSION['mensaje'])) {
			$mensaje_final = count($nuevas_invitaciones) . " invitado(s) agregado(s) al evento. ";
			if ($contactos_creados > 0) {
				$mensaje_final .= "Se añadieron " . $contactos_creados . " contacto(s) nuevo(s) a tu libreta. ";
			}
			if ($invitados_ya_existentes > 0) {
				$mensaje_final .= $invitados_ya_existentes . " contacto(s) ya estaba(n) en la lista. ";
			}
			if ($enviar_ahora) {
				$mensaje_final .= "Se enviaron " . $envios_exitosos . " invitaciones por correo.";
			}
			$this->crearMensaje('exito', $mensaje_final);
		}

		$this->redireccionar('evento/gestionar/' . $id_evento);
	}

	public function invitarIndividual($id_evento, $id_contacto)
	{
		$this->verificarSesion();
		$token_acceso = bin2hex(random_bytes(32));
		if ($this->invitacionModel->crear($id_evento, $id_contacto, $token_acceso)) {
			$this->crearMensaje('exito', 'Contacto añadido a la lista de invitados.');
		} else {
			$this->crearMensaje('error', 'No se pudo invitar al contacto.');
		}
		$this->redireccionar('evento/gestionar/' . $id_evento);
	}

	public function desinvitar($id_evento, $id_invitacion)
	{
		$this->verificarSesion();
		if ($this->invitacionModel->eliminar($id_invitacion, $_SESSION['id_organizador'])) {
			$this->crearMensaje('exito', 'El invitado ha sido eliminado de la lista.');
		} else {
			$this->crearMensaje('error', 'No se pudo eliminar la invitación.');
		}
		$this->redireccionar('evento/gestionar/' . $id_evento);
	}

	public function enviar()
	{
		$this->verificarSesion();
		header('Content-Type: application/json');

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$id_evento = $_POST['id_evento'];
			$accion = $_POST['accion'];

			$evento = $this->eventoModel->obtenerPorId($id_evento);
			if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
				echo json_encode(['exito' => false, 'mensaje' => 'No tienes permiso para este evento.']);
				exit();
			}

			$invitaciones_a_enviar = [];
			$envios_exitosos = 0;

			if ($accion == 'enviar_pendientes') {
				$invitaciones_a_enviar = $this->invitacionModel->obtenerInvitacionesPendientes($id_evento);
			}

			foreach ($invitaciones_a_enviar as $invitacion) {
				if ($this->_enviarCorreoInvitacion($invitacion, $evento, $invitacion->token_acceso)) {
					$this->invitacionModel->actualizarFechaInvitacion($invitacion->id);
					$envios_exitosos++;
				}
			}

			echo json_encode([
				'exito' => true,
				'enviados' => $envios_exitosos,
				'total' => count($invitaciones_a_enviar)
			]);
			exit();
		}

		echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
		exit();
	}

	public function enviarMasivoPostPublicacion($id_evento)
	{
		$this->verificarSesion();
		$evento = $this->eventoModel->obtenerPorId($id_evento);
		if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
			return ['iniciales' => 0, 'recordatorios' => 0];
		}

		$invitados = $this->invitacionModel->obtenerInvitadosPorEvento($id_evento, $_SESSION['id_organizador']);
		$contador_iniciales = 0;
		$contador_recordatorios = 0;

		foreach ($invitados as $invitado) {
			if (is_null($invitado->fecha_invitacion)) {
				if ($this->_enviarCorreoInvitacion($invitado, $evento, $invitado->token_acceso)) {
					$this->invitacionModel->actualizarFechaInvitacion($invitado->id_invitacion);
					$contador_iniciales++;
				}
			} elseif ($invitado->estado_rsvp == 'Pendiente') {
				if ($this->_enviarCorreoRecordatorioPublicacion($invitado, $evento)) {
					$contador_recordatorios++;
				}
			}
		}
		return ['iniciales' => $contador_iniciales, 'recordatorios' => $contador_recordatorios];
	}

	public function responder($token_acceso = '', $respuesta = '')
	{
		$datos = [];
		if (empty($token_acceso) || !in_array($respuesta, ['confirmar', 'rechazar'])) {
			die('Solicitud no válida.');
		}
		$invitacion = $this->invitacionModel->obtenerPorToken($token_acceso);
		if (!$invitacion) {
			die('Enlace de invitación no válido.');
		}
		$evento = $this->eventoModel->obtenerPorId($invitacion->id_evento);
		if (!$evento) {
			die('Evento no encontrado.');
		}
		$estado = ($respuesta == 'confirmar') ? 'Confirmado' : 'Rechazado';
		if ($this->invitacionModel->actualizarRsvp($token_acceso, $estado)) {
			$datos['es_exito'] = true;
			$datos['mensaje_principal'] = ($estado == 'Confirmado') ? '¡Gracias por confirmar!' : 'Respuesta registrada';
			$datos['mensaje_secundario'] = ($estado == 'Confirmado') ? 'Tu asistencia ha sido confirmada. ¡Te esperamos!' : 'Lamentamos que no puedas asistir. Gracias por notificarnos.';
		} else {
			$datos['es_exito'] = false;
			$datos['mensaje_principal'] = 'Respuesta no procesada';
			$datos['mensaje_secundario'] = 'Es posible que ya hayas respondido a esta invitación anteriormente.';
		}
		$datos['titulo'] = 'Respuesta de Invitación';
		$datos['evento'] = $evento;
		$this->vista('asistencia/respuesta_rsvp', $datos);
	}

	public function reenviar($id_invitacion = 0)
	{
		$this->verificarSesion();
		$invitacion = $this->invitacionModel->obtenerPorId($id_invitacion);
		if (!$invitacion) {
			$this->crearMensaje('error', 'La invitación no existe.');
			$this->redireccionar('organizador/panel');
			return;
		}
		$evento = $this->eventoModel->obtenerPorId($invitacion->id_evento);
		if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
			$this->crearMensaje('error', 'No tienes permiso para reenviar esta invitación.');
			$this->redireccionar('organizador/panel');
			return;
		}

		if ($this->_enviarCorreoInvitacion($invitacion, $evento, $invitacion->token_acceso)) {
			$this->invitacionModel->actualizarFechaInvitacion($id_invitacion);
			$this->crearMensaje('exito', 'Invitación reenviada exitosamente a ' . $invitacion->email);
		} else {
			$this->crearMensaje('error', 'No se pudo reenviar la invitación.');
		}
		$this->redireccionar('evento/gestionar/' . $invitacion->id_evento);
	}

	private function _enviarCorreoInvitacion($contacto_o_invitacion, $evento, $token_acceso)
	{
		$asunto = "Tu Credencial para: " . $evento->nombre_evento;

		// 1. Definir todas las variables que la plantilla necesita
		$nombre_invitado = $contacto_o_invitacion->nombre;
		$nombre_evento = $evento->nombre_evento;
		$enlace_micrositio = URL_PATH . 'publico/evento/' . $evento->id;
		$enlace_bienvenida = URL_PATH . 'asistencia/bienvenida/' . $token_acceso;
		$enlace_confirmar = URL_PATH . 'invitacion/responder/' . $token_acceso . '/confirmar';
		$enlace_rechazar = URL_PATH . 'invitacion/responder/' . $token_acceso . '/rechazar';
		$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($token_acceso);

		// 2. Usar el búfer de salida de PHP para "renderizar" la plantilla en una variable
		ob_start();
		require APP_BASE_PHYSICAL_PATH . '/app/views/emails/plantilla_invitacion.php';
		$cuerpoHtml = ob_get_clean();

		// 3. Enviar el correo con el HTML generado
		return $this->mailService->enviarEmail($contacto_o_invitacion->email, $contacto_o_invitacion->nombre, $asunto, $cuerpoHtml);
	}

	private function _enviarCorreoRecordatorioPublicacion($invitacion, $evento)
	{
		$asunto = "Recordatorio: El evento '{$evento->nombre_evento}' ha sido publicado";
		$enlace_micrositio = URL_PATH . 'publico/evento/' . $evento->id;
		$enlace_confirmar = URL_PATH . 'invitacion/responder/' . $invitacion->token_acceso . '/confirmar';
		$enlace_rechazar = URL_PATH . 'invitacion/responder/' . $invitacion->token_acceso . '/rechazar';

		$cuerpoHtml = '<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;"><div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 10px; padding: 30px;"><h2 style="color: #333;">Recordatorio de Invitación</h2><p>Hola <strong>' . htmlspecialchars($invitacion->nombre) . '</strong>,</p><p>Te recordamos que estás invitado al evento <strong>"' . htmlspecialchars($evento->nombre_evento) . '"</strong>.</p><p>El evento ya ha sido publicado oficialmente. Te agradecemos que confirmes tu asistencia lo antes posible.</p><div style="text-align:center; margin: 25px 0;"><a href="' . $enlace_confirmar . '" style="background-color: #198754; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Sí, Asistiré</a><a href="' . $enlace_rechazar . '" style="background-color: #dc3545; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;">No Puedo Asistir</a></div><p>Para más detalles, puedes visitar el <a href="' . $enlace_micrositio . '">micrositio del evento</a>.</p></div></body>';

		return $this->mailService->enviarEmail($invitacion->email, $invitacion->nombre, $asunto, $cuerpoHtml);
	}

	private function verificarSesion()
	{
		if (session_status() === PHP_SESSION_NONE) session_start();
		if (!isset($_SESSION['id_organizador'])) {
			$this->crearMensaje('error', 'Acceso denegado. Debes iniciar sesión.');
			$this->redireccionar('organizador/login');
			exit();
		}
	}

	private function crearMensaje($tipo, $mensaje)
	{
		if (session_status() === PHP_SESSION_NONE) session_start();
		$_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $mensaje];
	}

	private function redireccionar($ruta)
	{
		header('Location: ' . URL_PATH . $ruta);
		exit();
	}
}
