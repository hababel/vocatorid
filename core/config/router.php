<?php
// core/config/router.php

class router
{

	public $uri;
	public $controller;
	public $method;
	public $params = []; // Un array para almacenar todos los parámetros

	public function __construct()
	{
		$this->setUri();
		$this->setController();
		$this->setMethod();
		$this->setParams();
	}

        public function setUri()
        {
                // Obtenemos solo la ruta, sin la cadena de consulta
                $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                // Eliminamos la ruta base del proyecto si existe
                $base_path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
                $this->uri = str_replace($base_path, '', $path);
                $this->uri = trim($this->uri, '/');
        }

       public function setController()
       {
               // El controlador es el primer segmento de la URI. Si está vacío, por defecto es 'organizador'.
                $uri_parts = !empty($this->uri) ? explode('/', $this->uri) : ['organizador'];
                $controller = $uri_parts[0] ?: 'organizador';
                // Si viene con la extensión .php, la eliminamos
                $controller = preg_replace('/\.php$/', '', $controller);
                $this->controller = $controller;
       }

       public function setMethod()
       {
               // El método es el segundo segmento. Si no existe, por defecto es 'index'.
                $uri_parts = !empty($this->uri) ? explode('/', $this->uri) : [];
                $method = isset($uri_parts[1]) && !empty($uri_parts[1]) ? $uri_parts[1] : 'index';
                // También quitamos la extensión .php si existe
                $method = preg_replace('/\.php$/', '', $method);
                $this->method = $method;
       }

	public function setParams()
	{
		$uri_parts = !empty($this->uri) ? explode('/', $this->uri) : [];

		// Si hay más de dos segmentos (controlador y método), el resto son parámetros.
		if (count($uri_parts) > 2) {
			$this->params = array_slice($uri_parts, 2);
		}

		// También añadimos los parámetros de POST para los formularios.
		// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// 	$this->params['post'] = $_POST;
		// }
	}

	public function getController()
	{
		return $this->controller;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getParams()
	{
		return $this->params;
	}
}
