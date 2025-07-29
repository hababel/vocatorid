<?php

class RetoModel extends Model
{
    public function crear($datos)
    {
        $sql = "INSERT INTO retos (id_evento, descripcion, hora_inicio, hora_fin, codigo_actual, fecha_creacion)
                VALUES (:id_evento, :descripcion, :hora_inicio, :hora_fin, :codigo_actual, NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_evento', $datos['id_evento']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            $stmt->bindParam(':hora_inicio', $datos['hora_inicio']);
            $stmt->bindParam(':hora_fin', $datos['hora_fin']);
            $stmt->bindParam(':codigo_actual', $datos['codigo_actual']);
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerActivoPorEvento($id_evento)
    {
        $sql = "SELECT * FROM retos WHERE id_evento = :id_evento AND hora_inicio <= NOW() AND hora_fin >= NOW() ORDER BY hora_inicio DESC LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarCodigo($id_reto, $codigo)
    {
        $sql = "UPDATE retos SET codigo_actual = :codigo WHERE id = :id_reto";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':id_reto', $id_reto);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerPorId($id_reto)
    {
        $sql = "SELECT * FROM retos WHERE id = :id_reto";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_reto', $id_reto);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function validarCodigo($id_reto, $codigo)
    {
        $sql = "SELECT id FROM retos WHERE id = :id_reto AND codigo_actual = :codigo AND hora_inicio <= NOW() AND hora_fin >= NOW()";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_reto', $id_reto);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
