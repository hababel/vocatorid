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
}
?>
