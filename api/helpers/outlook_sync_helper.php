<?php
declare(strict_types=1);

// api/helpers/outlook_sync_helper.php (Versión 2 - con cURL)

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config_outlook.php';

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
 * Refresca un access_token de Outlook usando un refresh_token.
 */
function refreshOutlookAccessToken(int $responsableId, string $refreshToken): ?string {
    // ... (La función de refresco sigue siendo la misma, con cURL)
    $tokenUrl = "https://login.microsoftonline.com/" . OUTLOOK_TENANT_ID . "/oauth2/v2.0/token";
    $postData = [
        'client_id'     => OUTLOOK_CLIENT_ID,
        'scope'         => OUTLOOK_SCOPES,
        'refresh_token' => $refreshToken,
        'redirect_uri'  => OUTLOOK_REDIRECT_URI,
        'grant_type'    => 'refresh_token',
        'client_secret' => OUTLOOK_CLIENT_SECRET,
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        log_sync(null, $responsableId, 'App -> Outlook', 'REFRESH_TOKEN', 'Error', "HTTP $httpCode: " . $response);
        return null;
    }

    $tokenData = json_decode($response, true);
    if (!isset($tokenData['access_token'])) {
        return null;
    }

    try {
        $db = DB::getDB();
        $expiresAt = date('Y-m-d H:i:s', time() + ($tokenData['expires_in'] ?? 3600));
        $stmt = $db->prepare(
            "UPDATE responsable SET outlook_access_token = :access_token, outlook_refresh_token = :refresh_token, outlook_token_expires_at = :expires_at WHERE id = :id"
        );
        $stmt->execute([
            ':access_token' => $tokenData['access_token'],
            ':refresh_token' => $tokenData['refresh_token'] ?? $refreshToken,
            ':expires_at' => $expiresAt,
            ':id' => $responsableId
        ]);
        return $tokenData['access_token'];
    } catch (Throwable $e) {
        log_sync(null, $responsableId, 'App -> Outlook', 'REFRESH_TOKEN', 'Error', "Error DB: " . $e->getMessage());
        return null;
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

?>
