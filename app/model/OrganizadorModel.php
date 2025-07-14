<?php

// Se utiliza la constante para una ruta absoluta y robusta.
require_once APP_BASE_PHYSICAL_PATH . '/core/Model.php';

class OrganizadorModel extends Model
{

	/**
	 * Inserta un nuevo organizador en la base de datos.
	 * @param array $datos
	 * @return bool
	 */
	public function registrar($datos)
	{
		$sql = "INSERT INTO organizadores (nombre_completo, email, password_hash) VALUES (:nombre, :email, :password_hash)";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':nombre', $datos['nombre_completo']);
			$stmt->bindParam(':email', $datos['email']);
			$stmt->bindParam(':password_hash', $datos['password_hash']);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Verifica si un correo electrónico ya existe.
	 * @param string $email
	 * @return bool
	 */
	public function emailExiste($email)
	{
		$sql = "SELECT id FROM organizadores WHERE email = :email";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':email', $email);
			$stmt->execute();
			return $stmt->rowCount() > 0;
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Verifica las credenciales de un organizador para el login.
	 * @param string $email
	 * @param string $password
	 * @return object|false
	 */
	public function login($email, $password)
	{
		$sql = "SELECT * FROM organizadores WHERE email = :email";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':email', $email);
			$stmt->execute();
			$organizador = $stmt->fetch(PDO::FETCH_OBJ);

			if ($organizador && password_verify($password, $organizador->password_hash)) {
				return $organizador;
			} else {
				return false;
			}
		} catch (PDOException $e) {
			return false;
		}
	}
}
