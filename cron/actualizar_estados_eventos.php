<?php
// =================================================================
// Cron Job para Actualizar Estados de Eventos Automáticamente
// =================================================================
// Este script está diseñado para ser ejecutado por una tarea programada
// en el servidor (ej. cada 5 o 10 minutos).

// Se simula la carga del entorno de la aplicación
require_once dirname(__DIR__) . '/core/config/config.php';
require_once APP_BASE_PHYSICAL_PATH . '/core/Model.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/model/EventoModel.php';

echo "--- Iniciando script de actualización de estados (" . date('Y-m-d H:i:s') . ") ---\n";

try {
	$eventoModel = new EventoModel();
	$ahora = new DateTime();
	$ahora_str = $ahora->format('Y-m-d H:i:s');

	// 1. ACTUALIZAR EVENTOS DE 'PUBLICADO' A 'EN CURSO'
	// Seleccionar todos los eventos publicados cuya fecha de inicio ya pasó.
	$eventos_a_iniciar = $eventoModel->db->query("SELECT * FROM eventos WHERE estado = 'Publicado' AND fecha_evento <= '{$ahora_str}'")->fetchAll(PDO::FETCH_OBJ);

	if ($eventos_a_iniciar) {
		echo count($eventos_a_iniciar) . " evento(s) para cambiar a 'En Curso'.\n";
		foreach ($eventos_a_iniciar as $evento) {
			// Se usa un método genérico para actualizar el estado.
			// Asumimos que no se necesita el id_organizador para una tarea del sistema.
			$stmt = $eventoModel->db->prepare("UPDATE eventos SET estado = 'En Curso' WHERE id = :id");
			$stmt->execute(['id' => $evento->id]);
			echo " - Evento ID {$evento->id} actualizado a 'En Curso'.\n";
		}
	} else {
		echo "No hay eventos para iniciar.\n";
	}


	// 2. ACTUALIZAR EVENTOS DE 'EN CURSO' A 'FINALIZADO'
	// Seleccionar todos los eventos en curso cuya fecha de finalización ya pasó.
	// La fecha de finalización se calcula sumando la duración a la fecha de inicio.
	$eventos_a_finalizar = $eventoModel->db->query("SELECT * FROM eventos WHERE estado = 'En Curso' AND '{$ahora_str}' >= DATE_ADD(fecha_evento, INTERVAL (duracion_horas * 60) MINUTE)")->fetchAll(PDO::FETCH_OBJ);

	if ($eventos_a_finalizar) {
		echo count($eventos_a_finalizar) . " evento(s) para cambiar a 'Finalizado'.\n";
		foreach ($eventos_a_finalizar as $evento) {
			$stmt = $eventoModel->db->prepare("UPDATE eventos SET estado = 'Finalizado' WHERE id = :id");
			$stmt->execute(['id' => $evento->id]);
			echo " - Evento ID {$evento->id} actualizado a 'Finalizado'.\n";
		}
	} else {
		echo "No hay eventos para finalizar.\n";
	}

	echo "--- Script finalizado exitosamente. ---\n";
} catch (PDOException $e) {
	// En un entorno real, esto se registraría en un archivo de log.
	die("ERROR DE BASE DE DATOS: " . $e->getMessage());
} catch (Exception $e) {
	die("ERROR GENERAL: " . $e->getMessage());
}
