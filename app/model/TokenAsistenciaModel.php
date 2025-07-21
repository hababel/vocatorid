<?php

class TokenAsistenciaModel extends Model
{

	/**
	 * Crea un nuevo token dinámico para un evento.
	 */
	public function crear($id_evento, $token_dinamico, $fecha_expiracion)
	{
		$sql = "INSERT INTO tokens_asistencia_dinamicos (id_evento, token_dinamico, fecha_expiracion) VALUES (:id_evento, :token_dinamico, :fecha_expiracion)";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_evento', $id_evento);
			$stmt->bindParam(':token_dinamico', $token_dinamico);
			$stmt->bindParam(':fecha_expiracion', $fecha_expiracion);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Valida un token dinámico, comprobando que exista y no haya expirado.
	 */
       public function validarToken($id_evento, $token_dinamico)
       {
					$token_dinamico = strtoupper(trim($token_dinamico));

					// Consulta para obtener la hora de expiración y la hora actual de la BD
					$sql = "SELECT 
													(SELECT fecha_expiracion FROM tokens_asistencia_dinamicos 
													WHERE id_evento = :id_evento AND token_dinamico = :token_dinamico 
													ORDER BY id DESC LIMIT 1) as fecha_expiracion_token,
													NOW() as hora_actual_db";
					try {
						$stmt = $this->db->prepare($sql);
						$stmt->bindParam(':id_evento', $id_evento);
						$stmt->bindParam(':token_dinamico', $token_dinamico);
						$stmt->execute();
						$tiempos = $stmt->fetch(PDO::FETCH_OBJ);

						if (!$tiempos || !$tiempos->fecha_expiracion_token) {
							return [
								'valido' => false,
								'motivo' => 'Token no encontrado en la base de datos.'
							];
						}

						$fecha_exp = new DateTime($tiempos->fecha_expiracion_token);
						$fecha_db = new DateTime($tiempos->hora_actual_db);

						return [
							'valido' => $fecha_exp > $fecha_db, // La validación real
							'fecha_expiracion_token' => $tiempos->fecha_expiracion_token,
							'hora_actual_db' => $tiempos->hora_actual_db
						];
					} catch (PDOException $e) {
						return ['valido' => false, 'motivo' => $e->getMessage()];
					}
       }
}
