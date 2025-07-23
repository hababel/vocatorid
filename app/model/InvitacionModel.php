<?php

require_once APP_BASE_PHYSICAL_PATH . '/core/Model.php';

class InvitacionModel extends Model
{
	public function crear($id_evento, $id_contacto, $token_acceso)
	{
		$sql = "INSERT INTO invitaciones (id_evento, id_contacto, token_acceso) VALUES (:id_evento, :id_contacto, :token_acceso)";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_evento', $id_evento);
			$stmt->bindParam(':id_contacto', $id_contacto);
			$stmt->bindParam(':token_acceso', $token_acceso);
			if ($stmt->execute()) {
				return $this->db->lastInsertId();
			}
			return false;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function obtenerPorEventoYContacto($id_evento, $id_contacto)
	{
		$sql = "SELECT id FROM invitaciones WHERE id_evento = :id_evento AND id_contacto = :id_contacto LIMIT 1";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
			$stmt->bindParam(':id_contacto', $id_contacto, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return false;
		}
	}

	public function obtenerPorId($id_invitacion)
	{
		$sql = "SELECT i.*, c.nombre, c.email FROM invitaciones i JOIN contactos c ON i.id_contacto = c.id WHERE i.id = :id_invitacion";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_invitacion', $id_invitacion, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return false;
		}
	}

	public function obtenerPorToken($token_acceso)
	{
		$sql = "SELECT i.*, ra.fecha_checkin 
                FROM invitaciones i
                LEFT JOIN registros_asistencia ra ON i.id = ra.id_invitacion
                WHERE i.token_acceso = :token_acceso
                ORDER BY ra.fecha_checkin DESC LIMIT 1";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':token_acceso', $token_acceso);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return false;
		}
	}

	public function marcarAsistenciaVerificada($id_invitacion)
	{
		$sql = "UPDATE invitaciones SET asistencia_verificada = 1 WHERE id = :id_invitacion";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_invitacion', $id_invitacion);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	public function eliminar($id_invitacion, $id_organizador)
	{
		$sql = "DELETE i FROM invitaciones i JOIN eventos e ON i.id_evento = e.id WHERE i.id = :id_invitacion AND e.id_organizador = :id_organizador";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_invitacion', $id_invitacion, PDO::PARAM_INT);
			$stmt->bindParam(':id_organizador', $id_organizador, PDO::PARAM_INT);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	public function actualizarRsvp($token_acceso, $estado)
	{
		$sql = "UPDATE invitaciones SET estado_rsvp = :estado, fecha_confirmacion = NOW() WHERE token_acceso = :token_acceso AND estado_rsvp = 'Pendiente'";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':estado', $estado);
			$stmt->bindParam(':token_acceso', $token_acceso);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	public function actualizarFechaInvitacion($id_invitacion)
	{
		$sql = "UPDATE invitaciones SET fecha_invitacion = NOW() WHERE id = :id_invitacion";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_invitacion', $id_invitacion, PDO::PARAM_INT);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	public function obtenerInvitacionesPendientes($id_evento)
	{
		$sql = "SELECT i.*, c.nombre, c.email FROM invitaciones i JOIN contactos c ON i.id_contacto = c.id WHERE i.id_evento = :id_evento AND i.fecha_invitacion IS NULL";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	public function obtenerInvitadosPorEvento($id_evento, $id_organizador)
	{
		$sql = "SELECT 
                    c.id as id_contacto, c.nombre, c.email,
                    i.id as id_invitacion, i.estado_rsvp, i.fecha_invitacion, 
                    i.asistencia_verificada, i.clave_visual_tipo,
                    (SELECT ra.coordenadas_checkin FROM registros_asistencia ra WHERE ra.id_invitacion = i.id ORDER BY ra.fecha_checkin DESC LIMIT 1) as metodo_checkin
                FROM invitaciones i
                JOIN contactos c ON i.id_contacto = c.id
                WHERE i.id_evento = :id_evento AND c.id_organizador = :id_organizador
                ORDER BY c.nombre ASC";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
			$stmt->bindParam(':id_organizador', $id_organizador, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	public function obtenerContactosNoInvitados($id_evento, $id_organizador)
	{
		$sql = "SELECT c.id, c.nombre, c.email
                FROM contactos c
                LEFT JOIN invitaciones i ON c.id = i.id_contacto AND i.id_evento = :id_evento
                WHERE c.id_organizador = :id_organizador
                  AND c.archivado = 0
                  AND i.id IS NULL
                ORDER BY c.nombre ASC";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
			$stmt->bindParam(':id_organizador', $id_organizador, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	public function guardarClaveVisual($id_invitacion, $tipo, $valor_img, $valor_texto)
	{
		$sql = "UPDATE invitaciones SET clave_visual_tipo = :tipo, clave_visual_valor = :valor_img, clave_texto = :valor_texto WHERE id = :id";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':tipo', $tipo);
			$stmt->bindParam(':valor_img', $valor_img);
			$stmt->bindParam(':valor_texto', $valor_texto);
			$stmt->bindParam(':id', $id_invitacion, PDO::PARAM_INT);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}
}
