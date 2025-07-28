<?php

require_once APP_BASE_PHYSICAL_PATH . '/core/Controller.php';

class ContactoController extends Controller
{

	private $contactoModel;

	public function __construct()
	{
		$this->verificarSesion();
		$this->contactoModel = $this->modelo('ContactoModel');
	}

	public function index()
	{
		$contactos = $this->contactoModel->obtenerPorOrganizador($_SESSION['id_organizador']);
		$datos = [
			'titulo' => 'Mis Contactos',
			'nombre' => $_SESSION['nombre_organizador'],
			'contactos' => $contactos
		];
		$this->vistaPanel('contactos/index', $datos);
	}

	public function archivados()
	{
		$contactos = $this->contactoModel->obtenerArchivadosPorOrganizador($_SESSION['id_organizador']);
		$datos = [
			'titulo' => 'Contactos Archivados',
			'nombre' => $_SESSION['nombre_organizador'],
			'contactos' => $contactos
		];
		$this->vistaPanel('contactos/archivados', $datos);
	}

	public function crear()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// --- INICIO DE CAMBIOS ---
			$telefono = trim($_POST['telefono'] ?? '');
			if (empty($telefono) || !is_numeric($telefono) || strlen($telefono) < 7 || strlen($telefono) > 15) {
				$this->crearMensaje('error', 'El número de teléfono es obligatorio y debe ser un número válido.');
				$this->redireccionar('contacto/index');
				return;
			}
			// --- FIN DE CAMBIOS ---

			$datos = [
				'id_organizador' => $_SESSION['id_organizador'],
				'nombre' => trim($_POST['nombre']),
				'email' => trim($_POST['email']),
				'telefono' => $telefono,
				'acepta_habeas_data' => 0,
				'fuente_registro' => 'Manual',
				'lote_importacion' => null,
				'id_evento_origen' => null
			];
			if (empty($datos['nombre']) || empty($datos['email'])) {
				$this->crearMensaje('error', 'El nombre y el email son obligatorios.');
				$this->redireccionar('contacto/index');
				return;
			}
			if ($this->contactoModel->crear($datos)) {
				$this->crearMensaje('exito', '¡Contacto añadido exitosamente!');
			} else {
				$this->crearMensaje('error', 'Ocurrió un error al añadir el contacto. Es posible que el email ya exista en tu lista.');
			}
			$this->redireccionar('contacto/index');
		} else {
			$this->redireccionar('contacto/index');
		}
	}

	public function actualizar()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// --- INICIO DE CAMBIOS ---
			$telefono = trim($_POST['telefono_editar'] ?? '');
			if (empty($telefono) || !is_numeric($telefono) || strlen($telefono) < 7 || strlen($telefono) > 15) {
				$this->crearMensaje('error', 'El número de teléfono es obligatorio y debe ser un número válido.');
				$this->redireccionar('contacto/index');
				return;
			}
			// --- FIN DE CAMBIOS ---

			$datos = [
				'id_contacto' => $_POST['id_contacto_editar'],
				'id_organizador' => $_SESSION['id_organizador'],
				'nombre' => trim($_POST['nombre_editar']),
				'email' => trim($_POST['email_editar']),
				'telefono' => $telefono
			];

			if (empty($datos['nombre']) || empty($datos['email'])) {
				$this->crearMensaje('error', 'El nombre y el email son obligatorios.');
				$this->redireccionar('contacto/index');
				return;
			}

			if ($this->contactoModel->actualizar($datos)) {
				$this->crearMensaje('exito', '¡Contacto actualizado exitosamente!');
			} else {
				$this->crearMensaje('error', 'Ocurrió un error al actualizar el contacto.');
			}
			$this->redireccionar('contacto/index');
		} else {
			$this->redireccionar('contacto/index');
		}
	}

	public function importar()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] == UPLOAD_ERR_OK) {
				$archivo = $_FILES['archivo_csv']['tmp_name'];
				$nombreArchivo = $_FILES['archivo_csv']['name'];
				$extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);

				if (strtolower($extension) != 'csv') {
					$this->crearMensaje('error', 'Formato de archivo no válido. Por favor, sube un archivo .csv');
					$this->redireccionar('contacto/index');
					return;
				}

				$importados = 0;
				$errores = 0;
				$lote_id = uniqid('csv_');

				if (($gestor = fopen($archivo, "r")) !== FALSE) {
					fgetcsv($gestor, 1000, ";");
					while (($datos_fila = fgetcsv($gestor, 1000, ";")) !== FALSE) {
						// --- INICIO DE CAMBIOS ---
						// Ahora se esperan 3 columnas como mínimo (nombre, email, teléfono)
						if (count($datos_fila) >= 3) {
							$telefono_csv = isset($datos_fila[2]) ? trim($datos_fila[2]) : '';
							$datos_contacto = [
								'id_organizador' => $_SESSION['id_organizador'],
								'nombre' => trim($datos_fila[0]),
								'email' => trim($datos_fila[1]),
								'telefono' => $telefono_csv,
								'acepta_habeas_data' => 0,
								'fuente_registro' => 'Importacion_CSV',
								'lote_importacion' => $lote_id,
								'id_evento_origen' => null
							];

							// Validación estricta para cada fila
							if (
								!empty($datos_contacto['nombre']) &&
								!empty($datos_contacto['email']) &&
								filter_var($datos_contacto['email'], FILTER_VALIDATE_EMAIL) &&
								!empty($telefono_csv) && is_numeric($telefono_csv) && strlen($telefono_csv) >= 7 && strlen($telefono_csv) <= 15
							) {
								if ($this->contactoModel->crear($datos_contacto)) {
									$importados++;
								} else {
									$errores++;
								}
							} else {
								$errores++;
							}
							// --- FIN DE CAMBIOS ---
						}
					}
					fclose($gestor);
					$this->crearMensaje('exito', "$importados contactos importados. $errores filas no se pudieron importar (datos duplicados, incompletos o inválidos).");
				} else {
					$this->crearMensaje('error', 'No se pudo abrir el archivo CSV.');
				}
			} else {
				$this->crearMensaje('error', 'No se subió ningún archivo o hubo un error en la subida.');
			}
			$this->redireccionar('contacto/index');
		} else {
			$this->redireccionar('contacto/index');
		}
	}

	public function procesarAccion()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$accion = $_POST['accion_masiva'] ?? '';
			$ids_json = $_POST['contactos_seleccionados_json'] ?? '[]';
			$ids = json_decode($ids_json, true);
			$id_organizador = $_SESSION['id_organizador'];

			if (empty($ids) || !is_array($ids)) {
				$this->crearMensaje('error', 'No se seleccionó ningún contacto.');
				$this->redireccionar('contacto/index');
				return;
			}

			if ($accion == 'archivar') {
				if ($this->contactoModel->archivar($ids, $id_organizador)) {
					$this->crearMensaje('exito', count($ids) . ' contacto(s) han sido archivados.');
				} else {
					$this->crearMensaje('error', 'Ocurrió un error al archivar los contactos.');
				}
			} elseif ($accion == 'eliminar') {
				if ($this->contactoModel->eliminar($ids, $id_organizador)) {
					$this->crearMensaje('exito', count($ids) . ' contacto(s) han sido eliminados permanentemente.');
				} else {
					$this->crearMensaje('error', 'Ocurrió un error al eliminar los contactos.');
				}
			} else {
				$this->crearMensaje('error', 'Acción no válida.');
			}
			$this->redireccionar('contacto/index');
		} else {
			$this->redireccionar('contacto/index');
		}
	}

	public function procesarAccionArchivados()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$accion = $_POST['accion_masiva'] ?? '';
			$ids_json = $_POST['contactos_seleccionados_json'] ?? '[]';
			$ids = json_decode($ids_json, true);
			$id_organizador = $_SESSION['id_organizador'];

			if (empty($ids) || !is_array($ids)) {
				$this->crearMensaje('error', 'No se seleccionó ningún contacto.');
				$this->redireccionar('contacto/archivados');
				return;
			}

			if ($accion == 'desarchivar') {
				if ($this->contactoModel->desarchivar($ids, $id_organizador)) {
					$this->crearMensaje('exito', count($ids) . ' contacto(s) han sido restaurados.');
				} else {
					$this->crearMensaje('error', 'Ocurrió un error al restaurar los contactos.');
				}
			} elseif ($accion == 'eliminar') {
				if ($this->contactoModel->eliminar($ids, $id_organizador)) {
					$this->crearMensaje('exito', count($ids) . ' contacto(s) han sido eliminados permanentemente.');
				} else {
					$this->crearMensaje('error', 'Ocurrió un error al eliminar los contactos.');
				}
			} else {
				$this->crearMensaje('error', 'Acción no válida.');
			}
			$this->redireccionar('contacto/archivados');
		} else {
			$this->redireccionar('contacto/archivados');
		}
	}

	public function descargarPlantilla()
	{
		if (ob_get_level()) {
			ob_end_clean();
		}
		$nombre_archivo = "vocatorID_plantilla_importacion.csv";
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
		$output = fopen('php://output', 'w');
		// --- INICIO DE CAMBIOS ---
		fputcsv($output, ['Nombre Completo', 'Email', 'Telefono (Obligatorio)'], ';');
		fputcsv($output, ['Juan Perez', 'juan.perez@ejemplo.com', '3001234567'], ';');
		// --- FIN DE CAMBIOS ---
		fclose($output);
		exit();
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
