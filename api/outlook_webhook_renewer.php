<?php
declare(strict_types=1);

// api/outlook_webhook_renewer.php
// Este script está diseñado para ser ejecutado como un cron job.
// Renueva las suscripciones de webhook de Outlook que están a punto de expirar.

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers/outlook_sync_helper.php'; // Contiene log_sync y getOutlookAccessToken

// Definir un umbral de tiempo para la renovación (ej. renovar si expira en menos de 24 horas)
const RENEWAL_THRESHOLD_SECONDS = 24 * 3600; // 24 horas

// Definir la duración de la renovación (ej. extender por 2 días más)
const RENEWAL_DURATION_DAYS = 2;

function renewOutlookSubscription(int $responsableId, string $subscriptionId, string $accessToken): bool {
    $db = DB::getDB();
    $newExpirationDateTime = date('Y-m-d\TH:i:s.000Z', strtotime('+' . RENEWAL_DURATION_DAYS . ' days'));

    $renewalUrl = "https://graph.microsoft.com/v1.0/subscriptions/{$subscriptionId}";
    $payload = [
        "expirationDateTime" => $newExpirationDateTime
    ];

    $ch = curl_init($renewalUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); // Usar PATCH para renovar
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $responseData = json_decode($response, true);

    if ($httpCode === 200) { // 200 OK para renovación exitosa
        // Actualizar la fecha de expiración en la DB
        $stmt = $db->prepare("UPDATE responsable SET outlook_token_expires_at = :expires_at WHERE id = :id");
        $stmt->execute([
            ':expires_at' => date('Y-m-d H:i:s', strtotime($newExpirationDateTime)),
            ':id' => $responsableId
        ]);
        log_sync(null, $responsableId, 'Outlook -> App', 'RENOVAR_WEBHOOK', 'Exito', 'Suscripción de webhook renovada.', $payload, $responseData);
        return true;
    } else {
        log_sync(null, $responsableId, 'Outlook -> App', 'RENOVAR_WEBHOOK', 'Error', "Error al renovar suscripción (HTTP $httpCode): " . json_encode($responseData), $payload, $responseData);
        return false;
    }
}

try {
    $db = DB::getDB();

    // 1. Obtener todos los responsables con suscripciones de webhook activas
    $stmt = $db->prepare("SELECT id, outlook_access_token, outlook_refresh_token, outlook_token_expires_at, outlook_subscription_id FROM responsable WHERE outlook_subscription_id IS NOT NULL AND outlook_subscription_id != ''");
    $stmt->execute();
    $responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    log_sync(null, null, 'Outlook -> App', 'RENOVAR_WEBHOOK_CRON', 'Info', 'Iniciando proceso de renovación de webhooks. Responsables a revisar: ' . count($responsables));

    foreach ($responsables as $responsable) {
        $responsableId = (int)$responsable['id'];
        $subscriptionId = $responsable['outlook_subscription_id'];
        $currentExpiration = strtotime($responsable['outlook_token_expires_at']);

        // Verificar si la suscripción está a punto de expirar
        if ($currentExpiration - time() < RENEWAL_THRESHOLD_SECONDS) {
            log_sync(null, $responsableId, 'Outlook -> App', 'RENOVAR_WEBHOOK_CRON', 'Info', 'Suscripción a punto de expirar. Intentando renovar.');
            
            // Obtener un token de acceso válido (refrescar si es necesario)
            $accessToken = getOutlookAccessToken($responsableId);

            if ($accessToken) {
                renewOutlookSubscription($responsableId, $subscriptionId, $accessToken);
            } else {
                log_sync(null, $responsableId, 'Outlook -> App', 'RENOVAR_WEBHOOK_CRON', 'Error', 'No se pudo obtener token de acceso para renovar suscripción.');
            }
        } else {
            log_sync(null, $responsableId, 'Outlook -> App', 'RENOVAR_WEBHOOK_CRON', 'Info', 'Suscripción aún válida. No requiere renovación.');
        }
    }

    log_sync(null, null, 'Outlook -> App', 'RENOVAR_WEBHOOK_CRON', 'Info', 'Proceso de renovación de webhooks finalizado.');

} catch (Throwable $e) {
    error_log("Error fatal en outlook_webhook_renewer.php: " . $e->getMessage());
    log_sync(null, null, 'Outlook -> App', 'RENOVAR_WEBHOOK_CRON', 'Error', 'Error fatal: ' . $e->getMessage());
}

?>