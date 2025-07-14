<?php

class RegistroAsistenciaModel extends Model
{

	/**
	 * Crea el registro final de asistencia para una invitación.
	 */
	public function crear($id_invitacion, $coordenadas_checkin = null)
	{
		$sql = "INSERT INTO registros_asistencia (id_invitacion, coordenadas_checkin) VALUES (:id_invitacion, :coordenadas_checkin)";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_invitacion', $id_invitacion);
			$stmt->bindParam(':coordenadas_checkin', $coordenadas_checkin);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Verifica si ya existe un registro de asistencia para una invitación.
	 */
	public function yaRegistrado($id_invitacion)
	{
		$sql = "SELECT id FROM registros_asistencia WHERE id_invitacion = :id_invitacion";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_invitacion', $id_invitacion);
			$stmt->execute();
			return $stmt->rowCount() > 0;
		} catch (PDOException $e) {
			return false;
		}
	}
}
