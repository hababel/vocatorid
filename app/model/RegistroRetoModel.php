<?php
class RegistroRetoModel extends Model
{
    public function crear($id_reto, $id_invitacion, $codigo_ingresado, $ip, $correcto)
    {
        $sql = "INSERT INTO registros_retos (id_reto, id_invitacion, codigo_ingresado, ip, correcto, fecha_registro) VALUES (:id_reto, :id_invitacion, :codigo_ingresado, :ip, :correcto, NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_reto', $id_reto, PDO::PARAM_INT);
            $stmt->bindParam(':id_invitacion', $id_invitacion, PDO::PARAM_INT);
            $stmt->bindParam(':codigo_ingresado', $codigo_ingresado);
            $stmt->bindParam(':ip', $ip);
            $stmt->bindParam(':correcto', $correcto, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerPorReto($id_reto)
    {
        $sql = "SELECT * FROM registros_retos WHERE id_reto = :id_reto";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_reto', $id_reto, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorEvento($id_evento)
    {
        $sql = "SELECT rr.*, c.nombre FROM registros_retos rr
                JOIN invitaciones i ON rr.id_invitacion = i.id
                JOIN contactos c ON i.id_contacto = c.id
                WHERE i.id_evento = :id_evento";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function yaCompletado($id_reto, $id_invitacion)
    {
        $sql = "SELECT id FROM registros_retos WHERE id_reto = :id_reto AND id_invitacion = :id_invitacion AND correcto = 1 LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_reto', $id_reto, PDO::PARAM_INT);
            $stmt->bindParam(':id_invitacion', $id_invitacion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerCompletadosPorEvento($id_evento)
    {
        $sql = "SELECT rr.id_reto, rr.fecha_registro, c.nombre, c.email, r.descripcion
                FROM registros_retos rr
                JOIN invitaciones i ON rr.id_invitacion = i.id
                JOIN contactos c ON i.id_contacto = c.id
                JOIN retos r ON rr.id_reto = r.id
                WHERE i.id_evento = :id_evento AND rr.correcto = 1
                ORDER BY rr.id_reto, rr.fecha_registro";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
