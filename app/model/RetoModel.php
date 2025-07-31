<?php
class RetoModel extends Model
{
    public function crear($id_evento, $descripcion, $hora_inicio, $hora_fin)
    {
        $sql = "INSERT INTO retos (id_evento, descripcion, hora_inicio, hora_fin, fecha_creacion) VALUES (:id_evento, :descripcion, :hora_inicio, :hora_fin, NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':hora_inicio', $hora_inicio);
            $stmt->bindParam(':hora_fin', $hora_fin);
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarCodigo($id_reto, $codigo_actual)
    {
        $sql = "UPDATE retos SET codigo_actual = :codigo_actual WHERE id = :id_reto";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':codigo_actual', $codigo_actual);
            $stmt->bindParam(':id_reto', $id_reto, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza el código y registra la fecha de actualización
     * en la columna codigo_actual_timestamp si existe.
     */
    public function actualizarCodigoYFecha($id_reto, $codigo_actual)
    {
        $sql = "UPDATE retos SET codigo_actual = :codigo_actual, codigo_actual_timestamp = NOW() WHERE id = :id_reto";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':codigo_actual', $codigo_actual);
            $stmt->bindParam(':id_reto', $id_reto, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza únicamente la fecha de la última generación
     * del código visual.
     */
    public function actualizarFechaCodigo($id_reto)
    {
        $sql = "UPDATE retos SET codigo_actual_timestamp = NOW() WHERE id = :id_reto";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_reto', $id_reto, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerActivoPorEvento($id_evento)
    {
        $sql = "SELECT * FROM retos WHERE id_evento = :id_evento AND hora_inicio <= NOW() AND hora_fin >= NOW() ORDER BY id DESC LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerProximoPorEvento($id_evento)
    {
        $sql = "SELECT * FROM retos WHERE id_evento = :id_evento AND hora_inicio > NOW() ORDER BY hora_inicio ASC LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerPorEvento($id_evento)
    {
        $sql = "SELECT r.*, (SELECT COUNT(*) FROM registros_asistencia ra WHERE ra.coordenadas_checkin = CONCAT('Reto ', r.id)) AS completados FROM retos r WHERE r.id_evento = :id_evento ORDER BY r.hora_inicio ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId($id_reto)
    {
        $sql = "SELECT * FROM retos WHERE id = :id_reto";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_reto', $id_reto, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function activarAhora($id_reto)
    {
        $reto = $this->obtenerPorId($id_reto);
        if (!$reto) {
            return false;
        }
        $inicio_original = new DateTime($reto->hora_inicio);
        $fin_original = new DateTime($reto->hora_fin);
        $diff = $fin_original->getTimestamp() - $inicio_original->getTimestamp();
        $nuevo_inicio = date('Y-m-d H:i:s');
        $nuevo_fin = date('Y-m-d H:i:s', time() + $diff);

        $sql = "UPDATE retos SET hora_inicio = :inicio, hora_fin = :fin WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':inicio', $nuevo_inicio);
            $stmt->bindParam(':fin', $nuevo_fin);
            $stmt->bindParam(':id', $id_reto, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
