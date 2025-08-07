<?php

require_once APP_BASE_PHYSICAL_PATH . '/core/Controller.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/services/BrevoMailService.php';

class AsistenciaController extends Controller
{
        /**
         * Paleta de colores disponibles para la generación del código
         * visual. Esta lista es utilizada por otros componentes de la
         * aplicación mediante la propiedad estática.
         */
        /**
         * Mapa de colores utilizados en la Clave Visual. La clave es el nombre
         * que debe escribir el usuario y el valor el código HEX que se
         * presentará en pantalla.
         */
        public static $colores = [
                'Rojo'     => '#FF0000',
                'Verde'    => '#28a745',
                'Azul'     => '#007bff',
                'Amarillo' => '#ffc107',
                'Negro'    => '#000000',
                'Naranja'  => '#fd7e14',
                'Morado'   => '#6f42c1'
        ];

	private $invitacionModel;
	private $eventoModel;
	private $registroAsistenciaModel;
        private $tokenAsistenciaModel;
        private $contactoModel;
        private $retoModel;
        private $registroRetoModel;
        private $mailService;

	public function __construct()
	{
		$this->invitacionModel = $this->modelo('InvitacionModel');
		$this->eventoModel = $this->modelo('EventoModel');
		$this->registroAsistenciaModel = $this->modelo('RegistroAsistenciaModel');
		$this->tokenAsistenciaModel = $this->modelo('TokenAsistenciaModel');
                $this->contactoModel = $this->modelo('ContactoModel');
                $this->retoModel = $this->modelo('RetoModel');
                $this->registroRetoModel = $this->modelo('RegistroRetoModel');
                $this->mailService = new BrevoMailService();
       }

	public function bienvenida($token_acceso = '')
	{
		if (empty($token_acceso)) {
			die('Acceso denegado: Token no proporcionado.');
		}
		$invitacion = $this->invitacionModel->obtenerPorToken($token_acceso);
		if (!$invitacion) {
			die('Enlace de invitación no válido o caducado.');
		}
		$evento = $this->eventoModel->obtenerPorId($invitacion->id_evento);
		if (!$evento) {
			die('El evento asociado a esta invitación no fue encontrado.');
		}
		$datos = ['titulo' => 'Bienvenido a ' . $evento->nombre_evento, 'evento' => $evento, 'invitacion' => $invitacion];
		$this->vista('asistencia/bienvenida', $datos);
	}


        public function procesarQrPersonal()
        {
                header('Content-Type: application/json');
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                        echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
                        return;
                }

		$token_acceso = $_POST['token_acceso'] ?? '';
		$kiosko_lat = $_POST['kiosko_lat'] ?? null;
		$kiosko_lng = $_POST['kiosko_lng'] ?? null;

		$invitacion = $this->invitacionModel->obtenerPorToken($token_acceso);
		if (!$invitacion) {
			echo json_encode(['exito' => false, 'mensaje' => 'Credencial no válida o no encontrada.']);
			return;
		}

		if ($this->registroAsistenciaModel->yaRegistrado($invitacion->id)) {
			$contacto = $this->contactoModel->obtenerPorId($invitacion->id_contacto);
			$nombre = $contacto ? $contacto->nombre : 'Invitado';
			echo json_encode(['exito' => true, 'mensaje' => 'La asistencia de ' . $nombre . ' ya había sido registrada.']);
			return;
		}

		$evento = $this->eventoModel->obtenerPorId($invitacion->id_evento);
		if (!$evento) {
			echo json_encode(['exito' => false, 'mensaje' => 'El evento asociado no se pudo encontrar.']);
			return;
		}

		if ($evento->modo !== 'Virtual') {
			if (is_null($kiosko_lat) || is_null($kiosko_lng)) {
				echo json_encode(['exito' => false, 'mensaje' => 'No se pudo obtener la ubicación GPS del kiosco.']);
				return;
			}
			$distancia = calcularDistanciaHaversine($evento->latitud, $evento->longitud, $kiosko_lat, $kiosko_lng);
			if ($distancia > 200) {
				echo json_encode(['exito' => false, 'mensaje' => 'Verificación fallida. El kiosco no se encuentra en la ubicación del evento.']);
				return;
			}
		}

                if ($this->registroAsistenciaModel->crear($invitacion->id, 'Kiosco Físico')) {
                        $this->invitacionModel->marcarAsistenciaVerificada($invitacion->id);
                        $contacto = $this->contactoModel->obtenerPorId($invitacion->id_contacto);
                        $nombre = $contacto ? $contacto->nombre : 'Invitado';
                        echo json_encode(['exito' => true, 'mensaje' => '¡Asistencia registrada para ' . $nombre . '!', 'nombre_invitado' => $nombre]);
                } else {
                        echo json_encode(['exito' => false, 'mensaje' => 'Ocurrió un error al guardar la asistencia.']);
                }
        }

        // === NUEVAS FUNCIONES PARA RETOS DINÁMICOS ===

        public function inicio($token_acceso = '')
        {
                if (empty($token_acceso)) {
                        die('Acceso denegado: Token no proporcionado.');
                }
                $invitacion = $this->invitacionModel->obtenerPorToken($token_acceso);
                if (!$invitacion) {
                        die('Enlace no válido o caducado.');
                }
                $evento = $this->eventoModel->obtenerPorId($invitacion->id_evento);
                if (!$evento) {
                        die('Evento no encontrado.');
                }

                $datos = [
                        'titulo' => 'Registro de Asistencia',
                        'evento' => $evento,
                        'invitacion' => $invitacion
                ];
                $this->vista('asistencia/espera_reto', $datos);
        }

        public function obtenerRetoActivo($token_acceso)
        {
                header('Content-Type: application/json');
                $invitacion = $this->invitacionModel->obtenerPorToken($token_acceso);
                if (!$invitacion) {
                        echo json_encode(['exito' => false]);
                        return;
                }

                $reto = $this->retoModel->obtenerActivoPorEvento($invitacion->id_evento);
                if ($reto) {
                        $timestamp = isset($reto->codigo_actual_timestamp) ? strtotime($reto->codigo_actual_timestamp) : time();
                        $ahora = time();

                        if (empty($reto->codigo_actual) || ($ahora - $timestamp) >= 40) {
                                $datos = generarCodigoFrutasColoresAnimales(self::$colores);
                                $codigo = $datos['codigo'];
                                unset($datos['codigo']);
                                if (method_exists($this->retoModel, 'actualizarCodigoYFecha')) {
                                        $actualizado = $this->retoModel->actualizarCodigoYFecha($reto->id, $codigo);
                                        if (!$actualizado) {
                                                $this->retoModel->actualizarCodigo($reto->id, $codigo);
                                        }
                                } else {
                                        $this->retoModel->actualizarCodigo($reto->id, $codigo);
                                }
                                $reto->codigo_actual = $codigo;
                                $timestamp = $ahora;
                        } else {
                                $datos = datosDesdeCodigoVisual($reto->codigo_actual, self::$colores);
                        }

                        $tiempo_restante = 40 - ($ahora - $timestamp);
                        if ($tiempo_restante < 0) $tiempo_restante = 0;

                        $recursos = obtenerRecursosClaveVisual();
                        $listaFrutas = array_map(fn($f) => basename($f, '.jpg'), $recursos['frutas']);
                        $listaAnimales = array_map(fn($a) => basename($a, '.jpg'), $recursos['animales']);
                        $listaColores = array_values(self::$colores);

                        $opciones_frutas = array_map(
                                fn($n) => URL_PATH . 'core/img/clave_visual/frutas/' . $n . '.jpg',
                                generarOpcionesLista($listaFrutas, $datos['fruta'], 6)
                        );
                        $opciones_animales = array_map(
                                fn($n) => URL_PATH . 'core/img/clave_visual/animales/' . $n . '.jpg',
                                generarOpcionesLista($listaAnimales, $datos['animal'], 6)
                        );
                        $opciones_colores = generarOpcionesLista($listaColores, $datos['color_hex'], 6);

                        echo json_encode([
                                'exito' => true,
                                'id_reto' => $reto->id,
                                'estado' => 'activo',
                                'tiempo_restante' => $tiempo_restante,
                                'opciones_frutas' => $opciones_frutas,
                                'opciones_animales' => $opciones_animales,
                                'opciones_colores' => $opciones_colores
                        ]);
                        return;
                }

                $proximo = $this->retoModel->obtenerProximoPorEvento($invitacion->id_evento);
                if ($proximo) {
                        $inicio = new DateTime($proximo->hora_inicio);
                        $segundos = $inicio->getTimestamp() - time();
                        echo json_encode(['exito' => false, 'proximo_en' => $segundos]);
                } else {
                        echo json_encode(['exito' => false]);
                }
        }

        public function validarReto()
        {
                header('Content-Type: application/json');
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                        echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
                        return;
                }

                $token_acceso = $_POST['token'] ?? '';
                $id_reto = $_POST['id_reto'] ?? 0;
                $fruta = trim($_POST['fruta'] ?? '');
                $color = trim($_POST['color'] ?? '');
                $animal = trim($_POST['animal'] ?? '');

                $invitacion = $this->invitacionModel->obtenerPorToken($token_acceso);
                if (!$invitacion) {
                        echo json_encode(['exito' => false, 'mensaje' => 'Token inválido.']);
                        return;
                }

                $reto = $this->retoModel->obtenerActivoPorEvento($invitacion->id_evento);
                if (!$reto || $reto->id != $id_reto) {
                        echo json_encode(['exito' => false, 'mensaje' => 'Reto fuera de tiempo.']);
                        return;
                }

                if ($this->registroRetoModel->yaCompletado($reto->id, $invitacion->id)) {
                        echo json_encode(['exito' => false, 'mensaje' => 'Reto ya completado.']);
                        return;
                }

                $partes = explode('-', $reto->codigo_actual);
                if (count($partes) === 3) {
                        list($frutaCor, $colorCor, $animalCor) = array_map('trim', $partes);
                } else {
                        $frutaCor = $colorCor = $animalCor = '';
                }

                $codigoIngresado = $fruta . '-' . $color . '-' . $animal;

                echo "Combinación seleccionada por el usuario: $codigoIngresado\n";
                echo "Clave correcta: " . $reto->codigo_actual . "\n";

                $correcto = (strcasecmp($frutaCor, $fruta) === 0 && strcasecmp($colorCor, $color) === 0 && strcasecmp($animalCor, $animal) === 0) ? 1 : 0;
                $this->registroRetoModel->crear($reto->id, $invitacion->id, $codigoIngresado, $_SERVER['REMOTE_ADDR'] ?? '', $correcto);

                if ($correcto == 1 && !$this->registroAsistenciaModel->yaRegistrado($invitacion->id)) {
                        $this->registroAsistenciaModel->crear($invitacion->id, 'Reto ' . $reto->id);
                        $this->invitacionModel->marcarAsistenciaVerificada($invitacion->id);
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
        }

	public function registroAnonimo($id_evento = 0)
	{
		$evento = $this->eventoModel->obtenerPorId($id_evento);
		if (!$evento) {
			die('Este evento no existe o no está disponible para registro.');
		}
		$datos = ['titulo' => 'Registro para ' . $evento->nombre_evento, 'evento' => $evento];
		$this->vista('asistencia/registro_anonimo', $datos);
	}

	public function procesarRegistroAnonimo()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$id_evento = $_POST['id_evento'];
			$nombre = trim($_POST['nombre']);
			$email = trim($_POST['email']);
			$telefono = trim($_POST['telefono']);
			$acepta_habeas_data = isset($_POST['acepta_habeas_data']) ? 1 : 0;

			// Validación del teléfono
			if (empty($telefono) || !is_numeric($telefono) || strlen($telefono) < 7 || strlen($telefono) > 15) {
				die('Error: El número de teléfono es obligatorio y debe ser válido.');
			}

			if ($acepta_habeas_data == 0) {
				die('Error: Debe aceptar la política de tratamiento de datos para continuar.');
			}

			$evento = $this->eventoModel->obtenerPorId($id_evento);
			if (!$evento) {
				die('Evento no válido.');
			}

			$datos_contacto = [
				'id_organizador' => $evento->id_organizador,
				'nombre' => $nombre,
				'email' => $email,
				'telefono' => $telefono,
				'acepta_habeas_data' => $acepta_habeas_data,
				'fuente_registro' => 'Micrositio',
				'lote_importacion' => null,
				'id_evento_origen' => $id_evento
			];

			$id_contacto = $this->contactoModel->crear($datos_contacto);

			if ($id_contacto) {
				$token_acceso = bin2hex(random_bytes(32));
				$this->invitacionModel->crear($id_evento, $id_contacto, $token_acceso);

				$asunto = "Tu acceso para el evento: " . $evento->nombre_evento;
				$enlace = URL_PATH . 'asistencia/bienvenida/' . $token_acceso;
				$cuerpoHtml = "<h1>Hola " . htmlspecialchars($nombre) . ",</h1><p>Gracias por registrarte al evento '" . htmlspecialchars($evento->nombre_evento) . "'.</p><p>Para registrar tu ingreso el día del evento, por favor usa el siguiente enlace:</p><p><a href='" . $enlace . "'>Acceder al Evento</a></p><p>¡Te esperamos!</p>";

				$this->mailService->enviarEmail($email, $nombre, $asunto, $cuerpoHtml);

				$this->vista('asistencia/registro_exitoso');
			} else {
				die('Hubo un error al registrar el contacto. Es posible que el correo ya esté en uso.');
			}
		} else {
			$this->redireccionar('');
		}
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
