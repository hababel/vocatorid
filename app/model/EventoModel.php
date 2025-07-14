<?php

// Se utiliza la constante para una ruta absoluta y robusta.
require_once APP_BASE_PHYSICAL_PATH . '/core/Model.php';

class EventoModel extends Model
{

	/**
	 * Crea un nuevo evento para un organizador.
	 */
	public function crear($datos)
	{
		$sql = "INSERT INTO eventos (id_organizador, nombre_evento, objetivo, fecha_evento, duracion_horas, estado, modo, nombre_instructor, lugar_nombre, lugar_direccion, latitud, longitud) 
                VALUES (:id_organizador, :nombre_evento, :objetivo, :fecha_evento, :duracion_horas, :estado, :modo, :nombre_instructor, :lugar_nombre, :lugar_direccion, :latitud, :longitud)";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_organizador', $datos['id_organizador']);
			$stmt->bindParam(':nombre_evento', $datos['nombre_evento']);
			$stmt->bindParam(':objetivo', $datos['objetivo']);
			$stmt->bindParam(':fecha_evento', $datos['fecha_evento']);
			$stmt->bindParam(':duracion_horas', $datos['duracion_horas']);
			$stmt->bindParam(':estado', $datos['estado']);
			$stmt->bindParam(':modo', $datos['modo']);
			$stmt->bindParam(':nombre_instructor', $datos['nombre_instructor']);
			$stmt->bindParam(':lugar_nombre', $datos['lugar_nombre']);
			$stmt->bindParam(':lugar_direccion', $datos['lugar_direccion']);
			$stmt->bindParam(':latitud', $datos['latitud']);
			$stmt->bindParam(':longitud', $datos['longitud']);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Obtiene todos los eventos de un organizador, incluyendo el conteo de invitados y confirmados.
	 */
	public function obtenerPorOrganizador($id_organizador)
	{
		$sql = "SELECT
                    e.*,
                    COUNT(i.id) AS total_invitados,
                    SUM(CASE WHEN i.estado_rsvp = 'Confirmado' THEN 1 ELSE 0 END) AS total_confirmados
                FROM
                    eventos e
                LEFT JOIN
                    invitaciones i ON e.id = i.id_evento
                WHERE
                    e.id_organizador = :id_organizador
                GROUP BY
                    e.id
                ORDER BY
                    e.fecha_evento DESC";
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
	 * Obtiene los detalles de un evento específico.
	 */
	public function obtenerPorId($id_evento)
	{
		$sql = "SELECT * FROM eventos WHERE id = :id_evento";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Actualiza los datos de un evento existente.
	 */
	public function actualizar($datos)
	{
		$sql = "UPDATE eventos SET 
                    nombre_evento = :nombre_evento,
                    objetivo = :objetivo,
                    fecha_evento = :fecha_evento,
                    duracion_horas = :duracion_horas,
                    modo = :modo,
                    nombre_instructor = :nombre_instructor,
                    lugar_nombre = :lugar_nombre,
                    lugar_direccion = :lugar_direccion,
                    latitud = :latitud,
                    longitud = :longitud
                WHERE id = :id_evento AND id_organizador = :id_organizador";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':nombre_evento', $datos['nombre_evento']);
			$stmt->bindParam(':objetivo', $datos['objetivo']);
			$stmt->bindParam(':fecha_evento', $datos['fecha_evento']);
			$stmt->bindParam(':duracion_horas', $datos['duracion_horas']);
			$stmt->bindParam(':modo', $datos['modo']);
			$stmt->bindParam(':nombre_instructor', $datos['nombre_instructor']);
			$stmt->bindParam(':lugar_nombre', $datos['lugar_nombre']);
			$stmt->bindParam(':lugar_direccion', $datos['lugar_direccion']);
			$stmt->bindParam(':latitud', $datos['latitud']);
			$stmt->bindParam(':longitud', $datos['longitud']);
			$stmt->bindParam(':id_evento', $datos['id_evento'], PDO::PARAM_INT);
			$stmt->bindParam(':id_organizador', $datos['id_organizador'], PDO::PARAM_INT);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Elimina un evento de la base de datos.
	 */
	public function eliminar($id_evento, $id_organizador)
	{
		$sql = "DELETE FROM eventos WHERE id = :id_evento AND id_organizador = :id_organizador";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
			$stmt->bindParam(':id_organizador', $id_organizador, PDO::PARAM_INT);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Cambia el estado de un evento.
	 */
	public function cambiarEstado($id_evento, $id_organizador, $nuevo_estado)
	{
		$sql = "UPDATE eventos SET estado = :nuevo_estado WHERE id = :id_evento AND id_organizador = :id_organizador";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':nuevo_estado', $nuevo_estado);
			$stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
			$stmt->bindParam(':id_organizador', $id_organizador, PDO::PARAM_INT);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * NUEVO: Clona un evento y sus invitaciones.
	 * @param int $id_evento_original
	 * @param int $id_organizador
	 * @return int|false El ID del nuevo evento clonado o false si falla.
	 */
	public function clonar($id_evento_original, $id_organizador)
	{
		$this->db->beginTransaction();
		try {
			// 1. Obtener datos del evento original
			$evento_original = $this->obtenerPorId($id_evento_original);
			if (!$evento_original || $evento_original->id_organizador != $id_organizador) {
				$this->db->rollBack();
				return false;
			}

			// 2. Crear el nuevo evento clonado
			$sql_clon_evento = "INSERT INTO eventos (id_organizador, nombre_evento, objetivo, fecha_evento, duracion_horas, estado, modo, nombre_instructor, lugar_nombre, lugar_direccion, latitud, longitud)
                                VALUES (?, ?, ?, ?, ?, 'Borrador', ?, ?, ?, ?, ?, ?)";
			$stmt_clon = $this->db->prepare($sql_clon_evento);
			$stmt_clon->execute([
				$id_organizador,
				"Copia de " . $evento_original->nombre_evento,
				$evento_original->objetivo,
				date('Y-m-d H:i:s'), // Fecha actual por defecto
				$evento_original->duracion_horas,
				$evento_original->modo,
				$evento_original->nombre_instructor,
				$evento_original->lugar_nombre,
				$evento_original->lugar_direccion,
				$evento_original->latitud,
				$evento_original->longitud
			]);
			$id_nuevo_evento = $this->db->lastInsertId();

			// 3. Obtener todas las invitaciones del evento original
			$invitaciones_originales = $this->db->prepare("SELECT id_contacto FROM invitaciones WHERE id_evento = ?");
			$invitaciones_originales->execute([$id_evento_original]);
			$contactos_a_invitar = $invitaciones_originales->fetchAll(PDO::FETCH_COLUMN);

			// 4. Crear las nuevas invitaciones para el evento clonado
			if (!empty($contactos_a_invitar)) {
				$sql_clon_invitaciones = "INSERT INTO invitaciones (id_evento, id_contacto, token_acceso) VALUES (?, ?, ?)";
				$stmt_invitacion = $this->db->prepare($sql_clon_invitaciones);
				foreach ($contactos_a_invitar as $id_contacto) {
					$token_acceso = bin2hex(random_bytes(32));
					$stmt_invitacion->execute([$id_nuevo_evento, $id_contacto, $token_acceso]);
				}
			}

			$this->db->commit();
			return $id_nuevo_evento;
		} catch (Exception $e) {
			$this->db->rollBack();
			return false;
		}
	}
}
