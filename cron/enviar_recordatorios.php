<?php
// cron/enviar_recordatorios.php

/**
 * =================================================================
 * Script de Cron Job para Envío de Recordatorios
 * =================================================================
 *
 * Propósito:
 * Este script está diseñado para ser ejecutado automáticamente por el servidor
 * a intervalos regulares (ej. cada 5 minutos). Su función es:
 * 1. Buscar en la base de datos recordatorios que han alcanzado su fecha de envío.
 * 2. Obtener la lista de invitados para cada evento correspondiente.
 * 3. Personalizar y enviar el correo de recordatorio a cada invitado.
 * 4. Marcar el recordatorio como "enviado" para que no se procese de nuevo.
 *
 * Este script NUNCA debe ser accesible desde la web.
 */

// --- BOOTSTRAP DE LA APLICACIÓN ---
// Necesitamos cargar el entorno de la aplicación para tener acceso a las
// constantes, la conexión a la base de datos y los modelos.
// La ruta puede necesitar ajuste dependiendo de dónde guardes este script.
require_once dirname(__DIR__) . '/core/config/config.php';
require_once APP_BASE_PHYSICAL_PATH . '/core/config/conn.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/model/Model.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/model/RecordatorioModel.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/model/ParticipanteModel.php';
require_once APP_BASE_PHYSICAL_PATH . '/app/services/BrevoMailService.php';

// --- INICIO DEL PROCESO ---

echo "=============================================\n";
echo "Iniciando script de envío de recordatorios a las " . date('Y-m-d H:i:s') . "\n";

// Instanciar los modelos y servicios necesarios
$recordatorioModel = new RecordatorioModel();
$participanteModel = new ParticipanteModel();
$mailService = new BrevoMailService();

// 1. Obtener los recordatorios que están listos para ser enviados
$recordatorios_a_enviar = $recordatorioModel->obtenerRecordatoriosParaEnviar();

if (empty($recordatorios_a_enviar)) {
	echo "No hay recordatorios para enviar en este momento.\n";
	echo "=============================================\n";
	exit;
}

echo "Se encontraron " . count($recordatorios_a_enviar) . " reglas de recordatorio para procesar.\n";

// 2. Procesar cada regla de recordatorio
foreach ($recordatorios_a_enviar as $recordatorio) {
	echo "\nProcesando recordatorio ID #" . $recordatorio->id_recordatorio . " para el evento '" . $recordatorio->nombre_evento . "'...\n";

	// 3. Obtener la lista de todos los participantes invitados a este evento
	$invitados = $participanteModel->obtenerParticipantesPorCecrc($recordatorio->id_cecrc);

	if (empty($invitados)) {
		echo " -> No se encontraron invitados para este evento. Marcando recordatorio como enviado para no reintentar.\n";
	} else {
		echo " -> Se enviarán " . count($invitados) . " correos.\n";
		$envios_exitosos = 0;

		// 4. Enviar un correo personalizado a cada invitado
		foreach ($invitados as $invitado) {
			// Personalizar el cuerpo del correo reemplazando las etiquetas
			$cuerpo_personalizado = str_replace('[Nombre del Participante]', htmlspecialchars($invitado->nombre), $recordatorio->cuerpo_correo);
			$cuerpo_personalizado = str_replace('[Fecha del Evento]', date('d/m/Y H:i', strtotime($recordatorio->fecha_evento)), $cuerpo_personalizado);

			if ($mailService->enviarEmail(
				$invitado->email,
				$invitado->nombre,
				$recordatorio->asunto_correo,
				$cuerpo_personalizado
			)) {
				$envios_exitosos++;
			}
		}
		echo " -> Envíos exitosos: " . $envios_exitosos . "/" . count($invitados) . "\n";
	}

	// 5. Marcar el recordatorio como enviado para que no se procese de nuevo
	$recordatorioModel->marcarRecordatorioComoEnviado($recordatorio->id_recordatorio);
	echo " -> Recordatorio ID #" . $recordatorio->id_recordatorio . " marcado como enviado.\n";
}

echo "\nProceso de recordatorios finalizado.\n";
echo "=============================================\n";
