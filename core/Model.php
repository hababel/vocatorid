<?php
/*
 * Modelo base.
 * Proporciona la conexión a la base de datos a todos los modelos hijos.
 */
class Model
{

	protected $db; // Propiedad protegida para almacenar el objeto de conexión PDO.

	public function __construct()
	{
		// Utilizamos el método estático 'Conectar' de la clase 'Database'
		// para obtener la instancia de la conexión a la base de datos.
		//
		// Esta clase asume que el archivo /core/config/conn.php, que contiene
		// la clase 'Database', ya ha sido cargado por el punto de entrada
		// de la aplicación (index.php).
		$this->db = Database::Conectar();
	}
}
