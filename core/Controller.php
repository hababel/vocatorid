<?php
/*
 * Controlador base.
 * Carga los modelos y las vistas.
 */
class Controller
{

	/**
	 * Carga el modelo correspondiente.
	 * @param string $modelo - El nombre del archivo del modelo (sin .php).
	 * @return object - Una instancia del modelo.
	 */
	public function modelo($modelo)
	{
		$archivoModelo = APP_BASE_PHYSICAL_PATH . '/app/model/' . $modelo . '.php';

		if (file_exists($archivoModelo)) {
			require_once $archivoModelo;
			return new $modelo();
		} else {
			die('Error Crítico: El archivo del modelo no existe en la ruta: ' . $archivoModelo);
		}
	}

	/**
	 * Carga la vista PÚBLICA (login, registro, etc.) con su plantilla.
	 * @param string $vista - El nombre del archivo de la vista.
	 * @param array $datos - Datos para pasar a la vista.
	 */
	public function vista($vista, $datos = [])
	{
		$archivoVista = APP_BASE_PHYSICAL_PATH . '/app/views/' . $vista . '.php';

		if (file_exists($archivoVista)) {
			extract($datos);
			require_once APP_BASE_PHYSICAL_PATH . '/app/views/includes/header.php';
			require_once $archivoVista;
			require_once APP_BASE_PHYSICAL_PATH . '/app/views/includes/footer.php';
		} else {
			die('Error Crítico: El archivo de la vista no existe en la ruta: ' . $archivoVista);
		}
	}

	/**
	 * Carga una vista del PANEL DE CONTROL con su plantilla específica.
	 * @param string $vista - El nombre del archivo de la vista.
	 * @param array $datos - Datos para pasar a la vista.
	 */
	public function vistaPanel($vista, $datos = [])
	{
		$archivoVista = APP_BASE_PHYSICAL_PATH . '/app/views/' . $vista . '.php';

		if (file_exists($archivoVista)) {
			extract($datos);
			require_once APP_BASE_PHYSICAL_PATH . '/app/views/includes/header_panel.php';
			require_once $archivoVista;
			require_once APP_BASE_PHYSICAL_PATH . '/app/views/includes/footer_panel.php';
		} else {
			die('Error Crítico: El archivo de la vista del panel no existe en la ruta: ' . $archivoVista);
		}
	}
}
