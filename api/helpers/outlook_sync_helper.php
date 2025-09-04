<?php
declare(strict_types=1);

// api/helpers/outlook_sync_helper.php (Versión 2 - con cURL)

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config_outlook.php';
require_once __DIR__ . '/outlook_auth_helper.php';

/**
 * Escribe un registro en la tabla de log de sincronización.
 */
function log_sync(?int $id_cita, ?int $id_responsable, string $direccion, string $accion, string $estado, ?string $mensaje = null, ?array $payload = null, ?array $respuesta = null): void {
    try {
        $db = DB::getDB();
        $sql = "INSERT INTO log_sincronizacion_outlook (id_cita, id_responsable, direccion, accion, estado, mensaje, payload_enviado, respuesta_recibida) VALUES (:id_cita, :id_responsable, :direccion, :accion, :estado, :mensaje, :payload, :respuesta)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id_cita' => $id_cita,
            ':id_responsable' => $id_responsable,
            ':direccion' => $direccion,
            ':accion' => $accion,
            ':estado' => $estado,
            ':mensaje' => $mensaje,
            ':payload' => $payload ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
            ':respuesta' => $respuesta ? json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null
        ]);
    } catch (Throwable $e) {
        error_log("Error al escribir en log_sincronizacion_outlook: " . $e->getMessage());
    }
}



/**
 * Obtiene un access token válido para un responsable, refrescándolo si es necesario.
 */
function getOutlookAccessToken(int $responsableId): ?string {
    try {
        $db = DB::getDB();
        $stmt = $db->prepare("SELECT outlook_access_token, outlook_refresh_token, outlook_token_expires_at FROM responsable WHERE id = :id");
        $stmt->execute([':id' => $responsableId]);
        $tokens = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tokens || !$tokens['outlook_access_token'] || !$tokens['outlook_refresh_token']) {
            return null; // No hay tokens
        }

        if (strtotime($tokens['outlook_token_expires_at']) < time() + 60) { // Expira pronto
            return refreshOutlookAccessToken($responsableId, $tokens['outlook_refresh_token']);
        }

        return $tokens['outlook_access_token'];

    } catch (Throwable $e) {
        log_sync(null, $responsableId, 'App -> Outlook', 'GET_TOKEN', 'Error', "Error al obtener token: " . $e->getMessage());
        return null;
    }
}

/**
 * Crea un evento en el calendario de Outlook a partir de una cita.
 */
function crearEventoEnOutlook(int $citaId): ?string {
    $db = DB::getDB();
    $stmt = $db->prepare(" 
        SELECT 
            c.*, 
            r.outlook_calendar_id,
            u.nombres AS cliente_nombres, 
            u.apellidos AS cliente_apellidos,
            p.proposito AS proposito_nombre,
            prop.manzana,
            prop.villa
        FROM agendamiento_visitas c
        JOIN responsable r ON c.responsable_id = r.id
        JOIN usuario u ON c.id_usuario = u.id
        JOIN proposito_agendamiento p ON c.proposito_id = p.id
        JOIN propiedad prop ON c.id_propiedad = prop.id
        WHERE c.id = :id
    ");
    $stmt->execute([':id' => $citaId]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cita) {
        log_sync($citaId, null, 'App -> Outlook', 'CREAR', 'Error', 'Cita no encontrada en la DB local.');
        return null;
    }

    $responsableId = (int)$cita['responsable_id'];
    $calendarId = $cita['outlook_calendar_id'];

    if (!$calendarId) {
        log_sync($citaId, $responsableId, 'App -> Outlook', 'CREAR', 'Error', 'El responsable no tiene un ID de calendario de Outlook configurado.');
        return null;
    }

    $accessToken = getOutlookAccessToken($responsableId);
    if (!$accessToken) {
        log_sync($citaId, $responsableId, 'App -> Outlook', 'CREAR', 'Error', 'No se pudo obtener un token de acceso de Outlook válido.');
        return null;
    }

    // --- Conversión de Zona Horaria (CRÍTICO) ---
    $zonaHorariaLocal = new DateTimeZone('America/Guayaquil');
    $inicioLocal = new DateTime($cita['fecha_reunion'] . ' ' . $cita['hora_reunion'], $zonaHorariaLocal);
    $duracion = $cita['duracion_minutos'] ?? 60;
    $finLocal = (clone $inicioLocal)->add(new DateInterval("PT{$duracion}M"));

    $payload = [
        'subject' => "Cita: " . $cita['proposito_nombre'] . " - " . $cita['cliente_nombres'] . " " . $cita['cliente_apellidos'],
        'body' => [
            'contentType' => 'HTML',
            'content' => "<b>Cliente:</b> " . htmlspecialchars($cita['cliente_nombres'] . " " . $cita['cliente_apellidos']) . "<br>" .
                         "<b>Propiedad:</b> M" . htmlspecialchars($cita['manzana']) . " V" . htmlspecialchars($cita['villa']) . "<br>" .
                         "<b>Motivo:</b> " . htmlspecialchars($cita['proposito_nombre']) . "<br>" .
                         "<b>Observaciones:</b> " . nl2br(htmlspecialchars($cita['observaciones'] ?? '')) . "<br><br>" .
                         "<i>Evento generado automáticamente por AppCostaSol.</i>"
        ],
        'start' => [
            'dateTime' => $inicioLocal->format('Y-m-d\TH:i:s'),
            'timeZone' => 'America/Guayaquil'
        ],
        'end' => [
            'dateTime' => $finLocal->format('Y-m-d\TH:i:s'),
            'timeZone' => 'America/Guayaquil'
        ],
        'location' => [
            'displayName' => "Propiedad M" . $cita['manzana'] . " V" . $cita['villa']
        ]
    ];

    $graphUrl = "https://graph.microsoft.com/v1.0/me/calendars/{$calendarId}/events";
    $ch = curl_init($graphUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $responseData = json_decode($response, true);

    if ($httpCode === 201) { // 201 Created
        $outlookEventId = $responseData['id'] ?? null;
        log_sync($citaId, $responsableId, 'App -> Outlook', 'CREAR', 'Exito', 'Evento creado en Outlook.', $payload, $responseData);
        return $outlookEventId;
    } else {
        log_sync($citaId, $responsableId, 'App -> Outlook', 'CREAR', 'Error', "HTTP $httpCode: " . $response, $payload, $responseData);
        return null;
    }
}

/**
 * Elimina un evento en el calendario de Outlook.
 *
 * @param string $outlookEventId El ID del evento a eliminar.
 * @param int $responsableId El ID del responsable dueño del calendario.
 * @param int $citaId El ID de la cita local para los logs.
 * @return bool True si se eliminó con éxito, false en caso contrario.
 */
function eliminarEventoEnOutlook(string $outlookEventId, int $responsableId, int $citaId): bool {
    $accessToken = getOutlookAccessToken($responsableId);
    if (!$accessToken) {
        log_sync($citaId, $responsableId, 'App -> Outlook', 'ELIMINAR', 'Error', 'No se pudo obtener un token de acceso de Outlook válido.');
        return false;
    }

    $graphUrl = "https://graph.microsoft.com/v1.0/me/events/{$outlookEventId}";

    $ch = curl_init($graphUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 204) { // 204 No Content es la respuesta exitosa para DELETE
        log_sync($citaId, $responsableId, 'App -> Outlook', 'ELIMINAR', 'Exito', 'Evento eliminado en Outlook.');
        return true;
    } else {
        log_sync($citaId, $responsableId, 'App -> Outlook', 'ELIMINAR', 'Error', "HTTP $httpCode");
        return false;
    }
}

/**
 * Importa eventos existentes del calendario de Outlook a la base de datos local.
 *
 * @param int $responsableId El ID del responsable.
 * @param string $accessToken El token de acceso de Outlook.
 * @return void
 */
function importarEventosDeOutlook(int $responsableId, string $accessToken): void
{
    $db = DB::getDB();
    $calendarId = null;

    // Obtener el calendar_id del responsable
    $stmt = $db->prepare("SELECT outlook_calendar_id FROM responsable WHERE id = :id");
    $stmt->execute([':id' => $responsableId]);
    $respData = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($respData && $respData['outlook_calendar_id']) {
        $calendarId = $respData['outlook_calendar_id'];
    } else {
        log_sync(null, $responsableId, 'Outlook -> App', 'IMPORTAR', 'Error', 'No se encontró ID de calendario para el responsable.');
        return;
    }

    $graphUrl = "https://graph.microsoft.com/v1.0/me/calendars/{$calendarId}/events";

    $ch = curl_init($graphUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $responseData = json_decode($response, true);

    if ($httpCode === 200 && isset($responseData['value'])) {
        foreach ($responseData['value'] as $event) {
            // Verificar si el evento ya existe en la DB local
            $stmtCheck = $db->prepare("SELECT id FROM agendamiento_visitas WHERE outlook_event_id = :outlook_event_id");
            $stmtCheck->execute([':outlook_event_id' => $event['id']]);
            if ($stmtCheck->fetch()) {
                // Evento ya existe, saltar
                continue;
            }

            // Convertir fechas y horas a formato local (America/Guayaquil)
            $zonaHorariaLocal = new DateTimeZone('America/Guayaquil');

            $startDateTime = new DateTime($event['start']['dateTime'], new DateTimeZone($event['start']['timeZone']));
            $startDateTime->setTimezone($zonaHorariaLocal);

            $endDateTime = new DateTime($event['end']['dateTime'], new DateTimeZone($event['end']['timeZone']));
            $endDateTime->setTimezone($zonaHorariaLocal);

            $fechaReunion = $startDateTime->format('Y-m-d');
            $horaReunion = $startDateTime->format('H:i:s');
            $duracionMinutos = ($endDateTime->getTimestamp() - $startDateTime->getTimestamp()) / 60;
            if ($duracionMinutos < 1) $duracionMinutos = 30; // Mínimo 30 minutos

            // Extraer propósito y propiedad de la descripción o asunto si es posible
            $propositoId = 5; // Default to 'Evento de Calendario'
            $idPropiedad = null;
            $observaciones = $event['body']['content'] ?? $event['subject'];

            // Intenta parsear el propósito del asunto si sigue el patrón "Cita: [Proposito] - [Cliente]"
            if (preg_match('/^Cita: (.+?) - /', $event['subject'], $matches)) {
                $propositoNombre = trim($matches[1]);
                $stmtProposito = $db->prepare("SELECT id FROM proposito_agendamiento WHERE proposito = :proposito");
                $stmtProposito->execute([':proposito' => $propositoNombre]);
                $foundProposito = $stmtProposito->fetch(PDO::FETCH_ASSOC);
                if ($foundProposito) {
                    $propositoId = (int)$foundProposito['id'];
                }
            }

            // Intenta parsear la propiedad del location o asunto si sigue el patrón "Propiedad M[manzana] V[villa]"
            $locationName = $event['location']['displayName'] ?? '';
            if (preg_match('/Propiedad M(\w+) V(\w+)/', $locationName, $matches)) {
                $manzana = $matches[1];
                $villa = $matches[2];
                $stmtPropiedad = $db->prepare("SELECT id FROM propiedad WHERE manzana = :manzana AND villa = :villa");
                $stmtPropiedad->execute([':manzana' => $manzana, ':villa' => $villa]);
                $foundPropiedad = $stmtPropiedad->fetch(PDO::FETCH_ASSOC);
                if ($foundPropiedad) {
                    $idPropiedad = (int)$foundPropiedad['id'];
                }
            }


            // Insertar en agendamiento_visitas
            $stmtInsert = $db->prepare(" 
                INSERT INTO agendamiento_visitas (
                    responsable_id, proposito_id, fecha_reunion, hora_reunion,
                    estado, observaciones, duracion_minutos, outlook_event_id, id_propiedad
                ) VALUES (
                    :responsable_id, :proposito_id, :fecha_reunion, :hora_reunion,
                    'PROGRAMADO', :observaciones, :duracion_minutos, :outlook_event_id, :id_propiedad
                )");
            $stmtInsert->execute([
                ':responsable_id' => $responsableId,
                ':proposito_id' => $propositoId,
                ':fecha_reunion' => $fechaReunion,
                ':hora_reunion' => $horaReunion,
                ':observaciones' => $observaciones,
                ':duracion_minutos' => $duracionMinutos,
                ':outlook_event_id' => $event['id'],
                ':id_propiedad' => $idPropiedad
            ]);
            log_sync($db->lastInsertId(), $responsableId, 'Outlook -> App', 'IMPORTAR', 'Exito', 'Evento importado desde Outlook.');
        }
    } else {
        log_sync(null, $responsableId, 'Outlook -> App', 'IMPORTAR', 'Error', 'Error al obtener eventos de Outlook: HTTP ' . $httpCode . ' - ' . json_encode($responseData));
    }
}


/**
 * Procesa una notificación de webhook de Outlook.
 *
 * @param array $notification La notificación recibida del webhook.
 * @return void
 */
function procesarNotificacionWebhook(array $notification): void
{
    $db = DB::getDB();

    // Validar clientState (seguridad)
    $clientState = $notification['clientState'] ?? null;
    if (!$clientState) {
        log_sync(null, null, 'Outlook -> App', 'WEBHOOK', 'Error', 'Notificación sin clientState.');
        return;
    }

    // Buscar responsable por clientState
    $stmtResp = $db->prepare("SELECT id, outlook_access_token, outlook_refresh_token, outlook_token_expires_at, outlook_calendar_id FROM responsable WHERE outlook_client_state = :client_state");
    $stmtResp->execute([':client_state' => $clientState]);
    $responsable = $stmtResp->fetch(PDO::FETCH_ASSOC);

    if (!$responsable) {
        log_sync(null, null, 'Outlook -> App', 'WEBHOOK', 'Error', 'Responsable no encontrado para el clientState: ' . $clientState);
        return;
    }

    $responsableId = (int)$responsable['id'];
    $accessToken = getOutlookAccessToken($responsableId); // Asegura un token válido
    if (!$accessToken) {
        log_sync(null, $responsableId, 'Outlook -> App', 'WEBHOOK', 'Error', 'No se pudo obtener token de acceso para procesar webhook.');
        return;
    }

    $changeType = $notification['changeType'] ?? null;
    $outlookEventId = $notification['resourceData']['id'] ?? null; // ID del evento en Outlook

    if (!$changeType || !$outlookEventId) {
        log_sync(null, $responsableId, 'Outlook -> App', 'WEBHOOK', 'Error', 'Notificación incompleta (changeType o outlookEventId faltante).', $notification);
        return;
    }

    log_sync(null, $responsableId, 'Outlook -> App', 'WEBHOOK', 'Info', "Procesando: $changeType para evento $outlookEventId");

    switch ($changeType) {
        case 'Created':
        case 'Updated':
            // Obtener detalles completos del evento desde Graph API
            $graphUrl = "https://graph.microsoft.com/v1.0/me/events/{$outlookEventId}";
            $ch = curl_init($graphUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $eventData = json_decode($response, true);

            if ($httpCode === 200) {
                // Convertir fechas y horas a formato local (America/Guayaquil)
                $zonaHorariaLocal = new DateTimeZone('America/Guayaquil');

                $startDateTime = new DateTime($eventData['start']['dateTime'], new DateTimeZone($eventData['start']['timeZone']));
                $startDateTime->setTimezone($zonaHorariaLocal);

                $endDateTime = new DateTime($eventData['end']['dateTime'], new DateTimeZone($eventData['end']['timeZone']));
                $endDateTime->setTimezone($zonaHorariaLocal);

                $fechaReunion = $startDateTime->format('Y-m-d');
                $horaReunion = $startDateTime->format('H:i:s');
                $duracionMinutos = ($endDateTime->getTimestamp() - $startDateTime->getTimestamp()) / 60;
                if ($duracionMinutos < 1) $duracionMinutos = 30; // Mínimo 30 minutos

                $observaciones = $eventData['body']['content'] ?? $eventData['subject'];
                $propositoId = 5; // Default to 'Evento de Calendario' (ID 5)
                $idPropiedad = null;

                // Intenta parsear el propósito del asunto si sigue el patrón "Cita: [Proposito] - [Cliente]"
                if (preg_match('/^Cita: (.+?) - /', $eventData['subject'], $matches)) {
                    $propositoNombre = trim($matches[1]);
                    $stmtProposito = $db->prepare("SELECT id FROM proposito_agendamiento WHERE proposito = :proposito");
                    $stmtProposito->execute([':proposito' => $propositoNombre]);
                    $foundProposito = $stmtProposito->fetch(PDO::FETCH_ASSOC);
                    if ($foundProposito) {
                        $propositoId = (int)$foundProposito['id'];
                    }
                }

                // Intenta parsear la propiedad del location o asunto si sigue el patrón "Propiedad M[manzana] V[villa]"
                $locationName = $eventData['location']['displayName'] ?? '';
                if (preg_match('/Propiedad M(\w+) V(\w+)/', $locationName, $matches)) {
                    $manzana = $matches[1];
                    $villa = $matches[2];
                    $stmtPropiedad = $db->prepare("SELECT id FROM propiedad WHERE manzana = :manzana AND villa = :villa");
                    $stmtPropiedad->execute([':manzana' => $manzana, ':villa' => $villa]);
                    $foundPropiedad = $stmtPropiedad->fetch(PDO::FETCH_ASSOC);
                    if ($foundPropiedad) {
                        $idPropiedad = (int)$foundPropiedad['id'];
                    }
                }

                // Verificar si ya existe en la DB local
                $stmtCheck = $db->prepare("SELECT id FROM agendamiento_visitas WHERE outlook_event_id = :outlook_event_id");
                $stmtCheck->execute([':outlook_event_id' => $outlookEventId]);
                $localCita = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($localCita) {
                    // Actualizar cita existente
                    $stmtUpdate = $db->prepare(" 
                        UPDATE agendamiento_visitas SET
                            responsable_id = :responsable_id,
                            proposito_id = :proposito_id,
                            fecha_reunion = :fecha_reunion,
                            hora_reunion = :hora_reunion,
                            observaciones = :observaciones,
                            duracion_minutos = :duracion_minutos,
                            id_propiedad = :id_propiedad
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
                        ':responsable_id' => $responsableId,
                        ':proposito_id' => $propositoId,
                        ':fecha_reunion' => $fechaReunion,
                        ':hora_reunion' => $horaReunion,
                        ':observaciones' => $observaciones,
                        ':duracion_minutos' => $duracionMinutos,
                        ':id_propiedad' => $idPropiedad,
                        ':id' => $localCita['id']
                    ]);
                    log_sync((int)$localCita['id'], $responsableId, 'Outlook -> App', 'ACTUALIZAR', 'Exito', 'Cita actualizada desde Outlook.');
                } else {
                    // Insertar nueva cita
                    $stmtInsert = $db->prepare(" 
                        INSERT INTO agendamiento_visitas (
                            responsable_id, proposito_id, fecha_reunion, hora_reunion,
                            estado, observaciones, duracion_minutos, outlook_event_id, id_propiedad
                        ) VALUES (
                            :responsable_id, :proposito_id, :fecha_reunion, :hora_reunion,
                            'PROGRAMADO', :observaciones, :duracion_minutos, :outlook_event_id, :id_propiedad
                        )
                    ");
                    $stmtInsert->execute([
                        ':responsable_id' => $responsableId,
                        ':proposito_id' => $propositoId,
                        ':fecha_reunion' => $fechaReunion,
                        ':hora_reunion' => $horaReunion,
                        ':observaciones' => $observaciones,
                        ':duracion_minutos' => $duracionMinutos,
                        ':outlook_event_id' => $outlookEventId,
                        ':id_propiedad' => $idPropiedad
                    ]);
                    log_sync((int)$db->lastInsertId(), $responsableId, 'Outlook -> App', 'CREAR', 'Exito', 'Cita creada desde Outlook.');
                }
            } else {
                log_sync(null, $responsableId, 'Outlook -> App', 'WEBHOOK', 'Error', 'Error al obtener detalles del evento: HTTP ' . $httpCode . ' - ' . json_encode($eventData));
            }
            break;

        case 'Deleted':
            // Marcar cita local como CANCELADO o eliminarla
            $stmtDelete = $db->prepare("UPDATE agendamiento_visitas SET estado = 'CANCELADO' WHERE outlook_event_id = :outlook_event_id");
            $stmtDelete->execute([':outlook_event_id' => $outlookEventId]);
            if ($stmtDelete->rowCount() > 0) {
                log_sync(null, $responsableId, 'Outlook -> App', 'ELIMINAR', 'Exito', 'Cita cancelada/eliminada localmente desde Outlook.');
            } else {
                log_sync(null, $responsableId, 'Outlook -> App', 'ELIMINAR', 'Error', 'Cita no encontrada localmente para eliminar desde Outlook.');
            }
            break;

        default:
            log_sync(null, $responsableId, 'Outlook -> App', 'WEBHOOK', 'Advertencia', 'Tipo de cambio de webhook no soportado: ' . $changeType);
            break;
    }
}