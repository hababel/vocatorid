<?php

require_once APP_BASE_PHYSICAL_PATH . '/core/Controller.php';

class OrganizadorController extends Controller
{

	private $organizadorModel;
	private $eventoModel;

	public function __construct()
	{
		$this->organizadorModel = $this->modelo('OrganizadorModel');
		$this->eventoModel = $this->modelo('EventoModel');
	}

	public function index()
	{
		if ($this->sesionIniciada()) {
			$this->redireccionar('organizador/panel');
		} else {
			$this->redireccionar('organizador/login');
		}
	}

	public function registro()
	{
		$this->vista('organizadores/registro');
	}

	public function crear()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$datos = [
				'nombre_completo' => trim($_POST['nombre_completo']),
				'email' => trim($_POST['email']),
				'password' => trim($_POST['password']),
				'confirmar_password' => trim($_POST['confirmar_password'])
			];

			if (empty($datos['nombre_completo']) || empty($datos['email']) || empty($datos['password']) || strlen($datos['password']) < 8 || $datos['password'] !== $datos['confirmar_password'] || $this->organizadorModel->emailExiste($datos['email'])) {
				die('Error en la validación de los datos de registro.');
			}

			$datos['password_hash'] = password_hash($datos['password'], PASSWORD_DEFAULT);

			if ($this->organizadorModel->registrar($datos)) {
				echo "¡Registro exitoso! Ahora puedes iniciar sesión.";
			} else {
				die('Algo salió mal durante el registro.');
			}
		} else {
			echo "Método no permitido.";
		}
	}

	public function login()
	{
		$this->vista('organizadores/login');
	}

	public function procesarLogin()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$email = trim($_POST['email']);
			$password = trim($_POST['password']);

			$organizadorLogueado = $this->organizadorModel->login($email, $password);

			if ($organizadorLogueado) {
				$this->crearSesionDeUsuario($organizadorLogueado);
				$this->redireccionar('organizador/panel');
			} else {
				$this->crearMensaje('error', 'Email o contraseña incorrectos.');
				$this->redireccionar('organizador/login');
			}
		} else {
			$this->redireccionar('organizador/login');
		}
	}

	/**
	 * Página principal del panel de control con lógica mejorada.
	 */
	public function panel()
	{
		if (!$this->sesionIniciada()) {
			$this->crearMensaje('error', 'Acceso denegado. Debes iniciar sesión.');
			$this->redireccionar('organizador/login');
			return;
		}

		$todosLosEventos = $this->eventoModel->obtenerPorOrganizador($_SESSION['id_organizador']);

		$eventosActivos = [];
		$eventosFinalizados = [];
		$ahora = new DateTime();

		foreach ($todosLosEventos as $evento) {
			if ($evento->estado == 'Finalizado' || $evento->estado == 'Cancelado') {
				$eventosFinalizados[] = $evento;
			} else {
				// CORRECCIÓN: Se calcula los días que faltan para el evento
				$fechaEvento = new DateTime($evento->fecha_evento);
				if ($fechaEvento >= $ahora) {
					$diferencia = $ahora->diff($fechaEvento);
					$evento->dias_faltantes = $diferencia->days;
				} else {
					$evento->dias_faltantes = null; // El evento ya pasó pero no está finalizado
				}
				$eventosActivos[] = $evento;
			}
		}

		$datos = [
			'titulo' => 'Panel de Control',
			'nombre' => $_SESSION['nombre_organizador'],
			'eventos_activos' => $eventosActivos,
			'eventos_finalizados' => $eventosFinalizados
		];

		$this->vistaPanel('panel/index', $datos);
	}

	public function logout()
	{
		session_start();
		session_unset();
		session_destroy();
		$this->crearMensaje('exito', 'Has cerrado sesión exitosamente.');
		$this->redireccionar('organizador/login');
	}

	private function crearSesionDeUsuario($usuario)
	{
		session_start();
		$_SESSION['id_organizador'] = $usuario->id;
		$_SESSION['nombre_organizador'] = $usuario->nombre_completo;
		$_SESSION['email_organizador'] = $usuario->email;
	}

	private function sesionIniciada()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		return isset($_SESSION['id_organizador']);
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
