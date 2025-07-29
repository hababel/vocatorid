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
         * Registra la respuesta de un reto de verificación.
         */
        public function registrarReto($id_invitacion, $id_reto, $ip_origen, $codigo_utilizado)
        {
                $sql = "INSERT INTO registros_asistencia (id_invitacion, id_reto, ip_origen, token_utilizado) VALUES (:id_invitacion, :id_reto, :ip_origen, :token_utilizado)";
                try {
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(':id_invitacion', $id_invitacion);
                        $stmt->bindParam(':id_reto', $id_reto);
                        $stmt->bindParam(':ip_origen', $ip_origen);
                        $stmt->bindParam(':token_utilizado', $codigo_utilizado);
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
