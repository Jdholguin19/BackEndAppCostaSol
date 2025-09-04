<?php
declare(strict_types=1);

// api/helpers/outlook_sync_helper.php (Versión 3 - Verificación de duplicados mejorada)

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
 * Importa todos los eventos de un calendario de Outlook a la base de datos local.
 * Se asegura de no crear duplicados si un evento ya fue importado o si el horario ya está ocupado.
 */
function importarEventosDeOutlook(int $responsableId, string $accessToken): void {
    $db = DB::getDB();
    // Traemos más campos para tener la información completa
    $graphUrl = "https://graph.microsoft.com/v1.0/me/events?%24select=id,subject,body,start,end,location&%24top=100";

    $importedCount = 0;
    $skippedCount = 0;

    while ($graphUrl) {
        $ch = curl_init($graphUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_sync(null, $responsableId, 'Outlook -> App', 'IMPORTAR', 'Error', "No se pudieron obtener eventos de Outlook. HTTP: $httpCode");
            return;
        }

        $data = json_decode($response, true);
        $events = $data['value'] ?? [];

        foreach ($events as $event) {
            $outlookEventId = $event['id'];

            // --- Verificación de Duplicados Robusta ---
            // 1. Chequear por ID de Outlook (si este evento específico ya fue importado)
            $stmtCheckId = $db->prepare("SELECT id FROM agendamiento_visitas WHERE outlook_event_id = :outlook_id");
            $stmtCheckId->execute([ ':outlook_id' => $outlookEventId ]);
            if ($stmtCheckId->fetch()) {
                $skippedCount++;
                continue;
            }

            // Convertir fechas para la segunda verificación
            $zonaHorariaUTC = new DateTimeZone('UTC');
            $zonaHorariaLocal = new DateTimeZone('America/Guayaquil');
            $startUTC = new DateTime($event['start']['dateTime'], $zonaHorariaUTC);
            $startLocal = (clone $startUTC)->setTimezone($zonaHorariaLocal);
            $fechaLocal = $startLocal->format('Y-m-d');
            $horaLocal = $startLocal->format('H:i:s');

            // 2. Chequear por la restricción de unicidad (responsable, fecha, hora) para evitar el error 1062
            $stmtCheckUnique = $db->prepare("SELECT id FROM agendamiento_visitas WHERE responsable_id = :resp_id AND fecha_reunion = :fecha AND hora_reunion = :hora");
            $stmtCheckUnique->execute([
                ':resp_id' => $responsableId,
                ':fecha'   => $fechaLocal,
                ':hora'    => $horaLocal
            ]);
            if ($stmtCheckUnique->fetch()) {
                $skippedCount++;
                continue;
            }
            // --- Fin Verificación de Duplicados ---

            // Preparar datos para la inserción
            $endUTC = new DateTime($event['end']['dateTime'], $zonaHorariaUTC);
            $duracion = ($endUTC->getTimestamp() - $startUTC->getTimestamp()) / 60;
            $observaciones = $event['subject'] ?? 'Evento de Outlook';

            $stmtInsert = $db->prepare("
                INSERT INTO agendamiento_visitas
                (id_usuario, id_propiedad, responsable_id, proposito_id, fecha_reunion, hora_reunion, estado, observaciones, duracion_minutos, outlook_event_id)
                VALUES (NULL, NULL, :responsable_id, 5, :fecha, :hora, 'PROGRAMADO', :obs, :duracion, :outlook_id)
            ");
            
            $stmtInsert->execute([
                ':responsable_id' => $responsableId,
                ':fecha' => $fechaLocal,
                ':hora' => $horaLocal,
                ':obs' => $observaciones,
                ':duracion' => $duracion,
                ':outlook_id' => $outlookEventId
            ]);
            $importedCount++;
        }

        // Manejar paginación
        $graphUrl = $data['@odata.nextLink'] ?? null;
    }

    log_sync(null, $responsableId, 'Outlook -> App', 'IMPORTAR', 'Exito', "Importación completada. Importados: $importedCount, Omitidos: $skippedCount.");
}

/**
 * Procesa una notificación de webhook individual recibida de Microsoft Graph.
 */
function procesarNotificacionWebhook(array $notification): void {
    $db = DB::getDB();

    // 1. Encontrar al responsable basado en el ID de la suscripción
    $subscriptionId = $notification['subscriptionId'] ?? null;
    if (!$subscriptionId) return;

    $stmtResp = $db->prepare("SELECT id, outlook_client_state FROM responsable WHERE outlook_subscription_id = :sub_id");
    $stmtResp->execute([':sub_id' => $subscriptionId]);
    $responsable = $stmtResp->fetch(PDO::FETCH_ASSOC);

    if (!$responsable) return; // No se encontró responsable para esta suscripción

    $responsableId = (int)$responsable['id'];

    // 2. Validar el clientState para seguridad
    $clientState = $notification['clientState'] ?? null;
    if ($clientState !== $responsable['outlook_client_state']) {
        log_sync(null, $responsableId, 'Outlook -> App', 'WEBHOOK', 'Error', 'ClientState no coincide. Posible intento de suplantación.');
        return;
    }

    $changeType = $notification['changeType'] ?? '';
    $resourceUrl = $notification['resource'] ?? '';
    preg_match('/events\(\'(.*?)\'\)/i', $resourceUrl, $matches);
    $outlookEventId = $matches[1] ?? null;

    if (!$outlookEventId) return;

    // 3. Manejar evento eliminado
    if ($changeType === 'deleted') {
        $stmt = $db->prepare("UPDATE agendamiento_visitas SET estado = 'CANCELADO' WHERE outlook_event_id = :outlook_id");
        $stmt->execute([':outlook_id' => $outlookEventId]);
        log_sync($stmt->rowCount() ? null : null, $responsableId, 'Outlook -> App', 'ELIMINAR', 'Exito', "Evento eliminado en Outlook fue marcado como CANCELADO en la app.");
        return;
    }

    // 4. Para eventos creados o actualizados, obtener los detalles del evento de Graph
    $accessToken = getOutlookAccessToken($responsableId);
    if (!$accessToken) return;

    $graphUrl = "https://graph.microsoft.com/v1.0/{$resourceUrl}";
    $ch = curl_init($graphUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
    $response = curl_exec($ch);
    curl_close($ch);
    $eventData = json_decode($response, true);

    if (empty($eventData) || isset($eventData['error'])) return;

    // 5. Buscar si la cita ya existe en la DB local
    $stmtFind = $db->prepare("SELECT id FROM agendamiento_visitas WHERE outlook_event_id = :outlook_id");
    $stmtFind->execute([':outlook_id' => $outlookEventId]);
    $citaLocal = $stmtFind->fetch();

    // Preparar datos comunes
    $zonaHorariaUTC = new DateTimeZone('UTC');
    $zonaHorariaLocal = new DateTimeZone('America/Guayaquil');
    $startUTC = new DateTime($eventData['start']['dateTime'], $zonaHorariaUTC);
    $endUTC = new DateTime($eventData['end']['dateTime'], $zonaHorariaUTC);
    $startLocal = (clone $startUTC)->setTimezone($zonaHorariaLocal);
    $duracion = ($endUTC->getTimestamp() - $startUTC->getTimestamp()) / 60;
    $observaciones = $eventData['subject'] ?? 'Evento de Outlook';

    if ($citaLocal) {
        // --- Lógica de ACTUALIZACIÓN ---
        $citaId = $citaLocal['id'];
        $stmtUpdate = $db->prepare("UPDATE agendamiento_visitas SET fecha_reunion = :fecha, hora_reunion = :hora, observaciones = :obs, duracion_minutos = :duracion WHERE id = :id");
        $stmtUpdate->execute([
            ':fecha' => $startLocal->format('Y-m-d'),
            ':hora' => $startLocal->format('H:i:s'),
            ':obs' => $observaciones,
            ':duracion' => $duracion,
            ':id' => $citaId
        ]);
        log_sync($citaId, $responsableId, 'Outlook -> App', 'ACTUALIZAR', 'Exito', 'Cita local actualizada desde Outlook.');
    } else {
        // --- Lógica de CREACIÓN ---
        $stmtInsert = $db->prepare("INSERT INTO agendamiento_visitas (id_usuario, id_propiedad, responsable_id, proposito_id, fecha_reunion, hora_reunion, estado, observaciones, duracion_minutos, outlook_event_id) VALUES (NULL, NULL, :responsable_id, 5, :fecha, :hora, 'PROGRAMADO', :obs, :duracion, :outlook_id)");
        $stmtInsert->execute([
            ':responsable_id' => $responsableId,
            ':fecha' => $startLocal->format('Y-m-d'),
            ':hora' => $startLocal->format('H:i:s'),
            ':obs' => $observaciones,
            ':duracion' => $duracion,
            ':outlook_id' => $outlookEventId
        ]);
        log_sync($db->lastInsertId(), $responsableId, 'Outlook -> App', 'CREAR', 'Exito', 'Nueva cita creada desde Outlook.');
    }
}

?>