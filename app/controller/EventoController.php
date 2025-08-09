<?php

require_once APP_BASE_PHYSICAL_PATH . '/core/Controller.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/services/BrevoMailService.php';

class EventoController extends Controller
{

	private $eventoModel;
	private $invitacionModel;
        private $tokenAsistenciaModel;
        private $retoModel;
        private $registroRetoModel;
        private $mailService;

	public function __construct()
	{
		$this->eventoModel = $this->modelo('EventoModel');
		$this->invitacionModel = $this->modelo('InvitacionModel');
                $this->tokenAsistenciaModel = $this->modelo('TokenAsistenciaModel');
                $this->retoModel = $this->modelo('RetoModel');
                $this->registroRetoModel = $this->modelo('RegistroRetoModel');
                $this->mailService = new BrevoMailService();
	}

	private function verificarSesion()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		if (!isset($_SESSION['id_organizador'])) {
			$this->crearMensaje('error', 'Acceso denegado. Debes iniciar sesión.');
			$this->redireccionar('organizador/login');
			exit();
		}
	}

	public function crear()
	{
		$this->verificarSesion();
		$datos = [
			'titulo' => 'Crear Nuevo Evento',
			'nombre' => $_SESSION['nombre_organizador']
		];
		$this->vistaPanel('eventos/crear', $datos);
	}

	public function guardar()
	{
		$this->verificarSesion();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$datos = [
				'id_organizador' => $_SESSION['id_organizador'],
				'nombre_evento' => trim($_POST['nombre_evento']),
				'objetivo' => trim($_POST['objetivo']),
				'fecha_evento' => trim($_POST['fecha_evento']),
				'duracion_horas' => trim($_POST['duracion_horas']),
				'modo' => trim($_POST['modo']),
				'nombre_instructor' => trim($_POST['nombre_instructor']) ?? null,
				'lugar_nombre' => trim($_POST['lugar_nombre']) ?? null,
				'lugar_direccion' => trim($_POST['lugar_direccion']) ?? null,
				'latitud' => !empty($_POST['latitud']) ? trim($_POST['latitud']) : null,
				'longitud' => !empty($_POST['longitud']) ? trim($_POST['longitud']) : null,
				'estado' => 'Borrador'
			];

			if ($this->eventoModel->crear($datos)) {
				$this->crearMensaje('exito', '¡Evento creado exitosamente como borrador!');
				$this->redireccionar('organizador/panel');
			} else {
				$this->crearMensaje('error', 'Ocurrió un error al crear el evento. Inténtalo de nuevo.');
				$this->redireccionar('evento/crear');
			}
		} else {
			$this->redireccionar('organizador/panel');
		}
	}

	public function gestionar($id_evento)
	{
		$this->verificarSesion();
		$evento = $this->eventoModel->obtenerPorId($id_evento);
		if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
			$this->crearMensaje('error', 'El evento no existe o no tienes permiso para acceder a él.');
			$this->redireccionar('organizador/panel');
			return;
		}

		$invitados = $this->invitacionModel->obtenerInvitadosPorEvento($id_evento, $_SESSION['id_organizador']);
		$contactos_no_invitados = $this->invitacionModel->obtenerContactosNoInvitados($id_evento, $_SESSION['id_organizador']);

		$dias_faltantes = null;
		if ($evento->estado == 'Borrador' || $evento->estado == 'Publicado') {
			$ahora = new DateTime();
			$fechaEvento = new DateTime($evento->fecha_evento);
			if ($fechaEvento >= $ahora) {
				$diferencia = $ahora->diff($fechaEvento);
				$dias_faltantes = $diferencia->days;
			}
		}

		$datos = [
			'titulo' => 'Gestionar Evento: ' . $evento->nombre_evento,
			'nombre' => $_SESSION['nombre_organizador'],
			'evento' => $evento,
			'invitados' => $invitados,
			'contactos_no_invitados' => $contactos_no_invitados,
			'dias_faltantes' => $dias_faltantes
		];

		$this->vistaPanel('eventos/gestionar', $datos);
	}

	public function editar($id_evento)
	{
		$this->verificarSesion();
		$evento = $this->eventoModel->obtenerPorId($id_evento);
		if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
			$this->crearMensaje('error', 'No tienes permiso para editar este evento.');
			$this->redireccionar('organizador/panel');
			return;
		}
		$datos = [
			'titulo' => 'Editar Evento',
			'nombre' => $_SESSION['nombre_organizador'],
			'evento' => $evento
		];
		$this->vistaPanel('eventos/editar', $datos);
	}

	public function actualizar()
	{
		$this->verificarSesion();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$datos = [
				'id_evento' => $_POST['id_evento'],
				'id_organizador' => $_SESSION['id_organizador'],
				'nombre_evento' => trim($_POST['nombre_evento']),
				'objetivo' => trim($_POST['objetivo']),
				'fecha_evento' => trim($_POST['fecha_evento']),
				'duracion_horas' => trim($_POST['duracion_horas']),
				'modo' => trim($_POST['modo']),
				'nombre_instructor' => trim($_POST['nombre_instructor']) ?? null,
				'lugar_nombre' => trim($_POST['lugar_nombre']) ?? null,
				'lugar_direccion' => trim($_POST['lugar_direccion']) ?? null,
				'latitud' => !empty($_POST['latitud']) ? trim($_POST['latitud']) : null,
				'longitud' => !empty($_POST['longitud']) ? trim($_POST['longitud']) : null
			];

			if ($this->eventoModel->actualizar($datos)) {
				$this->crearMensaje('exito', '¡Evento actualizado exitosamente!');
				$this->redireccionar('evento/gestionar/' . $datos['id_evento']);
			} else {
				$this->crearMensaje('error', 'Ocurrió un error al actualizar el evento.');
				$this->redireccionar('evento/editar/' . $datos['id_evento']);
			}
		} else {
			$this->redireccionar('organizador/panel');
		}
	}

	public function eliminar()
	{
		$this->verificarSesion();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$id_evento = $_POST['id_evento_eliminar'];
			if ($this->eventoModel->eliminar($id_evento, $_SESSION['id_organizador'])) {
				$this->crearMensaje('exito', 'El evento ha sido eliminado exitosamente.');
			} else {
				$this->crearMensaje('error', 'No se pudo eliminar el evento o no tienes permiso para hacerlo.');
			}
			$this->redireccionar('organizador/panel');
		} else {
			$this->redireccionar('organizador/panel');
		}
	}

	public function cambiarEstado()
	{
		$this->verificarSesion();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$id_evento = $_POST['id_evento'];
			$nuevo_estado = $_POST['nuevo_estado'];
			$id_organizador = $_SESSION['id_organizador'];
			$estados_permitidos = ['Borrador', 'Publicado', 'En Curso', 'Finalizado', 'Cancelado'];
			if (in_array($nuevo_estado, $estados_permitidos)) {
				if ($this->eventoModel->cambiarEstado($id_evento, $id_organizador, $nuevo_estado)) {
					$this->crearMensaje('exito', 'El estado del evento ha sido cambiado a "' . $nuevo_estado . '".');
				} else {
					$this->crearMensaje('error', 'No se pudo cambiar el estado del evento.');
				}
			} else {
				$this->crearMensaje('error', 'Estado no válido.');
			}
			$this->redireccionar('evento/gestionar/' . $id_evento);
		} else {
			$this->redireccionar('organizador/panel');
		}
	}

	public function publicarYEnviar()
	{
		$this->verificarSesion();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$id_evento = $_POST['id_evento'];
			$id_organizador = $_SESSION['id_organizador'];

			$this->eventoModel->cambiarEstado($id_evento, $id_organizador, 'Publicado');

			$invitacionController = new InvitacionController();
			$resultado_envio = $invitacionController->enviarMasivoPostPublicacion($id_evento);

			$mensaje = "Evento publicado exitosamente. ";
			$mensaje .= "Se enviaron {$resultado_envio['iniciales']} invitaciones nuevas y ";
			$mensaje .= "se enviaron {$resultado_envio['recordatorios']} recordatorios.";
			$this->crearMensaje('exito', $mensaje);

			$this->redireccionar('evento/gestionar/' . $id_evento);
		} else {
			$this->redireccionar('organizador/panel');
		}
	}

        public function kiosco($id_evento)
        {
                $evento = $this->eventoModel->obtenerPorId($id_evento);
                if (!$evento) {
                        die('Este evento no existe.');
                }
                $datos = [
                        'titulo' => 'Kiosco - ' . $evento->nombre_evento,
                        'evento' => $evento
                ];
                extract($datos);
                require_once APP_BASE_PHYSICAL_PATH . '/app/views/eventos/kiosco.php';
        }

        /**
         * Pantalla de Kiosko Virtual que muestra el código visual
         * generado para el reto activo.
         */
        public function kioskoVirtual($id_evento)
        {
                $evento = $this->eventoModel->obtenerPorId($id_evento);
                if (!$evento) {
                        die('Este evento no existe.');
                }

                $mensaje = '';

                if ($evento->estado !== 'Publicado' || (property_exists($evento, 'activo') && !$evento->activo)) {
                        $mensaje = 'El evento debe estar publicado y activo.';
                } else {
                        $fin_evento = new DateTime($evento->fecha_evento);
                        $fin_evento->setTime(23, 59, 59);
                        $inicio_valido = (clone $fin_evento)->modify('-10 days');
                        $ahora = new DateTime();
                        if ($ahora < $inicio_valido || $ahora > $fin_evento) {
                                $mensaje = 'No es permitida la apertura del kiosko virtual porque no está en las fechas establecidas.';
                        }
                }

                $datos = [
                        'titulo' => 'Kiosko Virtual - ' . $evento->nombre_evento,
                        'evento' => $evento,
                        'mensaje_alerta' => $mensaje
                ];
                extract($datos);
                require_once APP_BASE_PHYSICAL_PATH . '/app/views/eventos/kiosko_virtual.php';
        }

        public function generarTokenKiosco($id_evento)
        {
                header('Content-Type: application/json');
		$token_dinamico = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
                // El token se actualiza periódicamente en el kiosco y antes
                // expiraba a los 15 segundos, lo que resultaba demasiado
                // estricto para algunos asistentes. Ahora otorgamos un margen
                // mayor de tiempo para que puedan escanearlo con calma.
                $expiracion_segundos = 120; // 2 minutos de validez
		$fecha_expiracion = (new DateTime())->add(new DateInterval("PT{$expiracion_segundos}S"))->format('Y-m-d H:i:s');

		if ($this->tokenAsistenciaModel->crear($id_evento, $token_dinamico, $fecha_expiracion)) {
			echo json_encode(['exito' => true, 'token' => $token_dinamico, 'expira_en' => $expiracion_segundos]);
		} else {
			echo json_encode(['exito' => false, 'mensaje' => 'No se pudo generar un nuevo token.']);
                }
        }

        public function dashboard($id_evento)
        {
                $this->verificarSesion();

                $evento = $this->eventoModel->obtenerPorId($id_evento);
                if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
                        die('Acceso denegado.');
                }

                $registros = $this->registroRetoModel->obtenerPorEvento($id_evento);

                $datos = [
                        'titulo' => 'Dashboard de Retos',
                        'evento' => $evento,
                        'registros' => $registros
                ];
                $this->vistaPanel('eventos/dashboard', $datos);
        }

        public function emitirRetoManual($id_evento)
        {
                header('Content-Type: application/json');

                $codigo = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
                $inicio = (new DateTime())->format('Y-m-d H:i:s');
                $fin = (new DateTime('+5 minutes'))->format('Y-m-d H:i:s');

                $id_reto = $this->retoModel->crear($id_evento, 'Reto manual', $inicio, $fin);
                if ($id_reto) {
                        $this->retoModel->actualizarCodigo($id_reto, $codigo);
                        echo json_encode(['exito' => true, 'id_reto' => $id_reto, 'codigo' => $codigo]);
                } else {
                        echo json_encode(['exito' => false]);
                }
        }

        public function obtenerRegistrosReto($id_evento)
        {
                header('Content-Type: application/json');

                $evento = $this->eventoModel->obtenerPorId($id_evento);
                if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
                        echo json_encode(['exito' => false]);
                        return;
                }

                $registros = $this->registroRetoModel->obtenerPorEvento($id_evento);
                $totalRetos = count($registros);
                $completados = 0;
                foreach ($registros as $r) {
                        if ($r->correcto) $completados++;
                }
                $porcentaje = $totalRetos > 0 ? round(($completados / $totalRetos) * 100, 2) : 0;

                echo json_encode(['exito' => true, 'porcentaje' => $porcentaje, 'registros' => $registros]);
        }

        public function crearReto($id_evento)
        {
                $this->verificarSesion();
                header('Content-Type: application/json');

                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                        echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
                        return;
                }

                $evento = $this->eventoModel->obtenerPorId($id_evento);
                if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
                        echo json_encode(['exito' => false, 'mensaje' => 'Evento no válido.']);
                        return;
                }

                if ($evento->estado !== 'Publicado' || (property_exists($evento, 'activo') && !$evento->activo)) {
                        echo json_encode(['exito' => false, 'mensaje' => 'El evento debe estar publicado y activo.']);
                        return;
                }

                $descripcion = trim($_POST['descripcion'] ?? '');
                $hora_inicio = trim($_POST['hora_inicio'] ?? '');
                $hora_fin = trim($_POST['hora_fin'] ?? '');

                try {
                        $inicio = new DateTime($hora_inicio);
                        $fin = new DateTime($hora_fin);
                } catch (Exception $e) {
                        echo json_encode(['exito' => false, 'mensaje' => 'Formato de fecha y hora inválido.']);
                        return;
                }

                $fin_evento = new DateTime($evento->fecha_evento);
                $fin_evento->setTime(23, 59, 59);
                $inicio_valido = (clone $fin_evento)->modify('-10 days');

                $ahora = new DateTime();
                if ($ahora < $inicio_valido || $ahora > $fin_evento) {
                        echo json_encode(['exito' => false, 'mensaje' => 'No es permitida la creación del nuevo reto porque no está en las fechas establecidas.']);
                        return;
                }

                if ($inicio < $inicio_valido || $fin > $fin_evento) {
                        echo json_encode(['exito' => false, 'mensaje' => 'No es permitida la creación del nuevo reto porque no está en las fechas establecidas.']);
                        return;
                }

                $id_reto = $this->retoModel->crear($id_evento, $descripcion, $inicio->format('Y-m-d H:i:s'), $fin->format('Y-m-d H:i:s'));
                if ($id_reto) {
                        echo json_encode(['exito' => true]);
                } else {
                        echo json_encode(['exito' => false]);
                }
        }

        public function activarReto($id_reto)
        {
                $this->verificarSesion();
                header('Content-Type: application/json');

                $reto = $this->retoModel->obtenerPorId($id_reto);
                if (!$reto) {
                        echo json_encode(['exito' => false]);
                        return;
                }

                $evento = $this->eventoModel->obtenerPorId($reto->id_evento);
                if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
                        echo json_encode(['exito' => false]);
                        return;
                }

                if ($this->retoModel->activarAhora($id_reto)) {
                        echo json_encode(['exito' => true]);
                } else {
                        echo json_encode(['exito' => false]);
                }
        }

        public function estadoRetos($id_evento)
        {
                $this->verificarSesion();
                header('Content-Type: application/json');

                $evento = $this->eventoModel->obtenerPorId($id_evento);
                if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
                        echo json_encode(['exito' => false]);
                        return;
                }

                $retos = $this->retoModel->obtenerPorEvento($id_evento);
                $ahora = new DateTime();
                $lista = [];
                foreach ($retos as $r) {
                        $estado = 'Pendiente';
                        if ($ahora >= new DateTime($r->hora_inicio) && $ahora <= new DateTime($r->hora_fin)) {
                                $estado = 'Activo';
                        } elseif ($ahora > new DateTime($r->hora_fin)) {
                                $estado = 'Finalizado';
                        }
                        $lista[] = [
                                'id' => $r->id,
                                'descripcion' => $r->descripcion,
                                'hora_inicio' => $r->hora_inicio,
                                'hora_fin' => $r->hora_fin,
                                'estado' => $estado,
                                'completados' => (int)$r->completados
                        ];
                }
                echo json_encode(['exito' => true, 'retos' => $lista]);
        }

        public function detalleReto($id_reto)
        {
                $this->verificarSesion();
                header('Content-Type: application/json');

                $reto = $this->retoModel->obtenerPorId($id_reto);
                if (!$reto) {
                        echo json_encode(['exito' => false]);
                        return;
                }

                $evento = $this->eventoModel->obtenerPorId($reto->id_evento);
                if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
                        echo json_encode(['exito' => false]);
                        return;
                }

                $registros = $this->registroRetoModel->obtenerPorReto($id_reto);
                echo json_encode(['exito' => true, 'registros' => $registros]);
        }

        public function exportarRetos($id_evento)
        {
                $this->verificarSesion();

                $evento = $this->eventoModel->obtenerPorId($id_evento);
                if (!$evento || $evento->id_organizador != $_SESSION['id_organizador']) {
                        die('Acceso denegado.');
                }

                $registros = $this->registroRetoModel->obtenerCompletadosPorEvento($id_evento);

                if (ob_get_level()) {
                        ob_end_clean();
                }

                $nombre_archivo = 'reporte_retos_evento_' . $id_evento . '.csv';
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
                $output = fopen('php://output', 'w');
                fputcsv($output, ['ID Reto', 'Descripción', 'Invitado', 'Email', 'Fecha'], ';');
                foreach ($registros as $r) {
                        fputcsv($output, [$r->id_reto, $r->descripcion, $r->nombre, $r->email, $r->fecha_registro], ';');
                }
                fclose($output);
                exit();
        }

	private function crearMensaje($tipo, $mensaje)
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		$_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $mensaje];
	}

	private function redireccionar($ruta)
	{
		header('Location: ' . URL_PATH . $ruta);
		exit();
	}
}
