<?php

// Se utiliza la constante para una ruta absoluta y robusta.
require_once APP_BASE_PHYSICAL_PATH . '/core/Model.php';

class ContactoModel extends Model
{

	/**
	 * Obtiene todos los contactos ACTIVOS de un organizador específico.
	 */
	public function obtenerPorOrganizador($id_organizador)
	{
		$sql = "SELECT c.*, e.nombre_evento as evento_origen_nombre 
                FROM contactos c
                LEFT JOIN eventos e ON c.id_evento_origen = e.id
                WHERE c.id_organizador = :id_organizador AND c.archivado = 0 
                ORDER BY c.nombre ASC";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_organizador', $id_organizador);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	/**
	 * Obtiene un contacto por su email para un organizador específico.
	 */
	public function obtenerPorEmailYOrganizador($email, $id_organizador)
	{
		$sql = "SELECT * FROM contactos WHERE email = :email AND id_organizador = :id_organizador LIMIT 1";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':email', $email);
			$stmt->bindParam(':id_organizador', $id_organizador, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Obtiene todos los contactos ARCHIVADOS de un organizador específico.
	 */
	public function obtenerArchivadosPorOrganizador($id_organizador)
	{
		$sql = "SELECT * FROM contactos WHERE id_organizador = :id_organizador AND archivado = 1 ORDER BY nombre ASC";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_organizador', $id_organizador);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	/**
	 * Crea un nuevo contacto para un organizador.
	 */
	public function crear($datos)
	{
		$sql = "INSERT INTO contactos (id_organizador, email, nombre, telefono, acepta_habeas_data, fuente_registro, lote_importacion, id_evento_origen) 
                VALUES (:id_organizador, :email, :nombre, :telefono, :acepta_habeas_data, :fuente_registro, :lote_importacion, :id_evento_origen)";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_organizador', $datos['id_organizador']);
			$stmt->bindParam(':email', $datos['email']);
			$stmt->bindParam(':nombre', $datos['nombre']);
			$stmt->bindParam(':telefono', $datos['telefono']);
			$stmt->bindParam(':acepta_habeas_data', $datos['acepta_habeas_data'], PDO::PARAM_INT);
			$stmt->bindParam(':fuente_registro', $datos['fuente_registro']);
			$stmt->bindParam(':lote_importacion', $datos['lote_importacion']);
			$stmt->bindParam(':id_evento_origen', $datos['id_evento_origen']);

			if ($stmt->execute()) {
				return $this->db->lastInsertId();
			} else {
				return false;
			}
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Obtiene los detalles de un contacto específico por su ID.
	 */
	public function obtenerPorId($id_contacto)
	{
		$sql = "SELECT * FROM contactos WHERE id = :id_contacto";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_contacto', $id_contacto, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Actualiza los datos de un contacto existente.
	 */
	public function actualizar($datos)
	{
		$sql = "UPDATE contactos SET nombre = :nombre, email = :email, telefono = :telefono 
                WHERE id = :id_contacto AND id_organizador = :id_organizador";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':nombre', $datos['nombre']);
			$stmt->bindParam(':email', $datos['email']);
			$stmt->bindParam(':telefono', $datos['telefono']);
			$stmt->bindParam(':id_contacto', $datos['id_contacto'], PDO::PARAM_INT);
			$stmt->bindParam(':id_organizador', $datos['id_organizador'], PDO::PARAM_INT);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Archiva una lista de contactos.
	 */
	public function archivar($ids, $id_organizador)
	{
		if (empty($ids)) return true;
		$placeholders = implode(',', array_fill(0, count($ids), '?'));
		$sql = "UPDATE contactos SET archivado = 1 WHERE id_organizador = ? AND id IN ($placeholders)";
		try {
			$stmt = $this->db->prepare($sql);
			$params = array_merge([$id_organizador], $ids);
			return $stmt->execute($params);
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Desarchiva (restaura) una lista de contactos.
	 */
	public function desarchivar($ids, $id_organizador)
	{
		if (empty($ids)) return true;
		$placeholders = implode(',', array_fill(0, count($ids), '?'));
		$sql = "UPDATE contactos SET archivado = 0 WHERE id_organizador = ? AND id IN ($placeholders)";
		try {
			$stmt = $this->db->prepare($sql);
			$params = array_merge([$id_organizador], $ids);
			return $stmt->execute($params);
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Elimina permanentemente una lista de contactos.
	 */
	public function eliminar($ids, $id_organizador)
	{
		if (empty($ids)) return true;
		$placeholders = implode(',', array_fill(0, count($ids), '?'));
		$sql = "DELETE FROM contactos WHERE id_organizador = ? AND id IN ($placeholders)";
		try {
			$stmt = $this->db->prepare($sql);
			$params = array_merge([$id_organizador], $ids);
			return $stmt->execute($params);
		} catch (PDOException $e) {
			return false;
		}
	}
}
