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

               $sql = "SELECT id FROM tokens_asistencia_dinamicos
               WHERE id_evento = :id_evento
               AND token_dinamico = :token_dinamico
               AND fecha_expiracion > NOW()";
               try {
                       $stmt = $this->db->prepare($sql);
                       $stmt->bindParam(':id_evento', $id_evento);
                       $stmt->bindParam(':token_dinamico', $token_dinamico);
                       $stmt->execute();
                       return $stmt->rowCount() > 0;
               } catch (PDOException $e) {
                       return false;
               }
       }
}
