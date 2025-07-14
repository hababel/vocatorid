<?php

class RecordatorioModel extends Model
{

	/**
	 * Obtiene los recordatorios que están listos para ser enviados.
	 * Esta función es utilizada por el script del Cron Job.
	 */
	public function obtenerRecordatoriosParaEnviar()
	{
		// La consulta busca recordatorios no enviados cuya fecha de envío calculada ya pasó.
		$sql = "SELECT r.*, e.nombre_evento, e.fecha_evento FROM recordatorios r
                JOIN eventos e ON r.id_evento = e.id
                WHERE r.fue_enviado = 0 
                AND NOW() >= (e.fecha_evento - INTERVAL r.tiempo_antes_valor r.tiempo_antes_unidad)";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return [];
		}
	}

	/**
	 * Marca un recordatorio como enviado para evitar duplicados.
	 */
	public function marcarRecordatorioComoEnviado($id_recordatorio)
	{
		$sql = "UPDATE recordatorios SET fue_enviado = 1 WHERE id = :id_recordatorio";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_recordatorio', $id_recordatorio);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}
}
