<?php

require_once APP_BASE_PHYSICAL_PATH . '/core/Controller.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/services/BrevoMailService.php';

class AsistenciaController extends Controller
{

	private $invitacionModel;
	private $eventoModel;
	private $registroAsistenciaModel;
	private $tokenAsistenciaModel;
	private $contactoModel;
	private $mailService;

	public function __construct()
	{
		$this->invitacionModel = $this->modelo('InvitacionModel');
		$this->eventoModel = $this->modelo('EventoModel');
		$this->registroAsistenciaModel = $this->modelo('RegistroAsistenciaModel');
		$this->tokenAsistenciaModel = $this->modelo('TokenAsistenciaModel');
		$this->contactoModel = $this->modelo('ContactoModel');
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

	public function iniciarVerificacion($token_acceso = '')
	{
		if (empty($token_acceso)) {
			die('Acceso denegado: Token no proporcionado.');
		}
		$invitacion = $this->invitacionModel->obtenerPorToken($token_acceso);
		if (!$invitacion) {
			die('Enlace de invitación no válido o caducado.');
		}
		if ($this->registroAsistenciaModel->yaRegistrado($invitacion->id)) {
			$this->crearMensaje('exito', 'Tu asistencia para este evento ya ha sido registrada.');
			$this->redireccionar('asistencia/bienvenida/' . $token_acceso);
			return;
		}
		$evento = $this->eventoModel->obtenerPorId($invitacion->id_evento);
		if (!$evento) {
			die('El evento asociado a esta invitación no fue encontrado.');
		}
		$datos = ['titulo' => 'Verificar Asistencia', 'evento' => $evento, 'invitacion' => $invitacion];
		$this->vista('asistencia/verificacion', $datos);
	}

	public function procesarVerificacion()
	{
		header('Content-Type: application/json');
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
			return;
		}
               $token_acceso = $_POST['token_acceso'] ?? '';
               $token_dinamico = $_POST['token_dinamico'] ?? '';
               $token_dinamico = strtoupper(trim($token_dinamico));
		$latitud_asistente = $_POST['latitud'] ?? null;
		$longitud_asistente = $_POST['longitud'] ?? null;
		$invitacion = $this->invitacionModel->obtenerPorToken($token_acceso);

		if (!$invitacion) {
			echo json_encode(['exito' => false, 'mensaje' => 'Tu sesión de invitación no es válida.']);
			return;
		}
		if ($this->registroAsistenciaModel->yaRegistrado($invitacion->id)) {
			echo json_encode(['exito' => true, 'mensaje' => 'Tu asistencia ya había sido registrada.', 'completado' => true]);
			return;
		}
		$evento = $this->eventoModel->obtenerPorId($invitacion->id_evento);
		if (!$evento) {
			echo json_encode(['exito' => false, 'mensaje' => 'El evento asociado no se pudo encontrar.']);
			return;
		}

		// --- INICIO DE LA SECCIÓN DE DEPURACIÓN ---
		$resultado_validacion = $this->tokenAsistenciaModel->validarToken($evento->id, $token_dinamico);
		$hora_actual_php = (new DateTime())->format('Y-m-d H:i:s');

		if (!$resultado_validacion['valido']) {
			$mensaje = 'El código QR ya no es válido o no corresponde al evento. '
				. 'Obtén un nuevo código desde el kiosco y vuelve a intentarlo.';
$mensaje.=
					'validacion_fallida'.true."<br>".
					'hora_actual_servidor_php' .$hora_actual_php."<br>".
					'hora_expiracion_del_token' .$resultado_validacion['fecha_expiracion_token'] ?? 'No encontrado'."<br>".
					'hora_actual_base_de_datos' . $resultado_validacion['hora_actual_db'] ?? 'No disponible'."<br>".
					'motivo_del_fallo' . $resultado_validacion['motivo'] ?? 'La hora de expiración es anterior a la hora de la BD.'
				;

			// Enviamos toda la información de depuración
			echo json_encode([
				'exito' => false,
				'mensaje' => $mensaje,
				'debug_info' => [
					'validacion_fallida' => true,
					'hora_actual_servidor_php' => $hora_actual_php,
					'hora_expiracion_del_token' => $resultado_validacion['fecha_expiracion_token'] ?? 'No encontrado',
					'hora_actual_base_de_datos' => $resultado_validacion['hora_actual_db'] ?? 'No disponible',
					'motivo_del_fallo' => $resultado_validacion['motivo'] ?? 'La hora de expiración es anterior a la hora de la BD.'
				]
			]);
			return;
		}
		// --- FIN DE LA SECCIÓN DE DEPURACIÓN ---

		if ($evento->modo !== 'Virtual') {
			if (is_null($latitud_asistente) || is_null($longitud_asistente)) {
				echo json_encode(['exito' => false, 'mensaje' => 'No se pudo obtener tu ubicación GPS.']);
				return;
			}
			$distancia = calcularDistanciaHaversine($evento->latitud, $evento->longitud, $latitud_asistente, $longitud_asistente);
			if ($distancia > 200) {
				echo json_encode(['exito' => false, 'mensaje' => 'Verificación fallida. No te encuentras en la ubicación del evento.']);
				return;
			}
		}

		$recursos = obtenerRecursosClaveVisual();
		$categoria_aleatoria = array_rand($recursos);
		$imagen_aleatoria = $recursos[$categoria_aleatoria][array_rand($recursos[$categoria_aleatoria])];

		$colores = ['Azul', 'Verde', 'Rojo', 'Amarillo', 'Naranja', 'Morado'];
		$color_aleatorio = $colores[array_rand($colores)];

		// **CORRECCIÓN:** Se verifica si la clave visual se guardó correctamente
		if (!$this->invitacionModel->guardarClaveVisual($invitacion->id, $categoria_aleatoria, $imagen_aleatoria, $color_aleatorio)) {
			echo json_encode(['exito' => false, 'mensaje' => 'Error al preparar el desafío de verificación. Inténtalo de nuevo.']);
			return;
		}

		$contacto = $this->contactoModel->obtenerPorId($invitacion->id_contacto);

		// **CORRECCIÓN:** Se verifica si el correo con la clave visual fue enviado
		if (!$this->_enviarCorreoClaveVisual($contacto->email, $contacto->nombre, $imagen_aleatoria, $color_aleatorio)) {
			echo json_encode(['exito' => false, 'mensaje' => 'No pudimos enviar la clave visual a tu correo. Verifica que tu email sea correcto e inténtalo de nuevo.']);
			return;
		}

		// Si todo fue exitoso, se envía la respuesta para redirigir al usuario
		echo json_encode([
			'exito' => true,
			'mensaje' => 'Verificación inicial correcta. Revisa tu correo para obtener tu clave visual y completar el registro.',
			'siguiente_paso' => URL_PATH . 'asistencia/mostrarDesafio/' . $token_acceso
		]);
	}

	public function mostrarDesafio($token_acceso)
	{
		$invitacion = $this->invitacionModel->obtenerPorToken($token_acceso);
		if (!$invitacion || empty($invitacion->clave_visual_tipo)) {
			die('Enlace no válido o desafío no iniciado.');
		}

		$recursos = obtenerRecursosClaveVisual();
		$opciones_imagenes = $recursos[$invitacion->clave_visual_tipo];
		shuffle($opciones_imagenes);

		$opciones_colores = ['Azul', 'Verde', 'Rojo', 'Amarillo', 'Naranja', 'Morado'];
		shuffle($opciones_colores);

		$datos = [
			'titulo' => 'Desafío de Verificación',
			'invitacion' => $invitacion,
			'opciones_imagenes' => $opciones_imagenes,
			'opciones_colores' => $opciones_colores
		];
		$this->vista('asistencia/verificacion_clave_visual', $datos);
	}

	public function procesarClaveVisual()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->redireccionar('');
		}

		$token_acceso = $_POST['token_acceso'];
		$imagen_seleccionada = $_POST['clave_imagen'];
		$color_seleccionado = $_POST['clave_color'];

		$invitacion = $this->invitacionModel->obtenerPorToken($token_acceso);

		if ($invitacion && $invitacion->clave_visual_valor === $imagen_seleccionada && $invitacion->clave_texto === $color_seleccionado) {

			if ($this->registroAsistenciaModel->crear($invitacion->id, 'Verificado por 3-FAV')) {
				$this->invitacionModel->marcarAsistenciaVerificada($invitacion->id);
				$this->crearMensaje('exito', '¡Asistencia registrada exitosamente!');
			} else {
				$this->crearMensaje('info', 'Tu asistencia ya había sido registrada.');
			}
		} else {
			$this->crearMensaje('error', 'Clave visual incorrecta. Por favor, inténtalo de nuevo.');
		}

		$this->redireccionar('asistencia/bienvenida/' . $token_acceso);
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
			$acepta_habeas_data = isset($_POST['acepta_habeas_data']) ? 1 : 0;

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
				'telefono' => '',
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

				$this . vista('asistencia/registro_exitoso');
			} else {
				die('Hubo un error al registrar el contacto. Es posible que el correo ya esté en uso.');
			}
		} else {
			$this . redireccionar('');
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

	private function _enviarCorreoClaveVisual($email, $nombre, $imagen, $color)
	{
		$asunto = "Tu Clave Visual para el Evento";
		$cuerpoHtml = "<p>Hola {$nombre},</p><p>Para completar tu registro de asistencia, selecciona la imagen de un <strong>" . substr($imagen, 0, -4) . "</strong> y el color <strong>{$color}</strong>.</p><p>Este código es de un solo uso.</p>";
		// **CORRECCIÓN:** Devolver el resultado del envío del correo
		return $this->mailService->enviarEmail($email, $nombre, $asunto, $cuerpoHtml);
	}
}
