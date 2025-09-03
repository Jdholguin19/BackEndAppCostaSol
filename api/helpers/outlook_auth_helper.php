<?php
// C:\xampp\htdocs\BackEndAppCostaSol\api\helpers\outlook_auth_helper.php

declare(strict_types=1);

ini_set('error_log', __DIR__ . '/../../config/error_log');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config_outlook.php';

/**
 * Refreshes the Outlook access token using the refresh token.
 * Updates the database with the new tokens and expiration time.
 *
 * @param int $responsableId The ID of the responsible user.
 * @param string $refreshToken The refresh token.
 * @return string|null The new access token on success, or null on failure.
 */
function refreshOutlookAccessToken(int $responsableId, string $refreshToken): ?string {
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
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        error_log("Error al refrescar token para responsable $responsableId: " . ($response ?: curl_error($ch)));
        return null;
    }

    $tokenData = json_decode($response, true);
    if (!isset($tokenData['access_token'])) {
        error_log("No access_token en respuesta de refresco para responsable $responsableId: " . json_encode($tokenData));
        return null;
    }

    // Actualizar tokens en la DB
    try {
        $db = DB::getDB();
        $expiresAt = date('Y-m-d H:i:s', time() + ($tokenData['expires_in'] ?? 3600));
        $stmt = $db->prepare("
            UPDATE responsable
            SET
                outlook_access_token = :access_token,
                outlook_refresh_token = :refresh_token,
                outlook_token_expires_at = :expires_at
            WHERE id = :responsable_id
        ");
        $stmt->execute([
            ':access_token' => $tokenData['access_token'],
            ':refresh_token' => $tokenData['refresh_token'] ?? $refreshToken, // Usar el nuevo refresh token si se proporciona
            ':expires_at' => $expiresAt,
            ':responsable_id' => $responsableId
        ]);
        return $tokenData['access_token'];
    } catch (PDOException $e) {
        error_log("Error DB al guardar token refrescado para responsable $responsableId: " . $e->getMessage());
        return null;
    }
}

/**
 * Creates a webhook subscription for Outlook calendar events.
 *
 * @param int $responsableId The ID of the responsible user.
 * @param string $accessToken The access token for the responsible.
 * @param string $webhookUrl The URL of your webhook endpoint.
 * @return array|null An array containing 'id' and 'clientState' of the subscription on success, or null on failure.
 */
function createOutlookWebhookSubscription(int $responsableId, string $accessToken, string $webhookUrl): ?array {
    $subscriptionUrl = "https://graph.microsoft.com/v1.0/subscriptions";

    // Generar un clientState único y secreto para esta suscripción
    // Este valor debe ser almacenado en tu DB junto con el ID de la suscripción
    // para verificar la autenticidad de las notificaciones.
    $clientState = bin2hex(random_bytes(16)); // Genera un string aleatorio de 32 caracteres

    // Calculate expirationDateTime outside the array definition
    $formattedExpirationDateTime = gmdate('Y-m-d\TH:i:sZ', strtotime('+1 hour')); // Using gmdate and no milliseconds

    $subscriptionData = [
        "changeType" => "created,updated,deleted",
        "notificationUrl" => "https://webhook.site/6111fe3c-045d-44e5-8b30-c93522521667", // This is now webhook.site
        "resource" => "me/calendar/events",
        "expirationDateTime" => $formattedExpirationDateTime, // Use the pre-calculated string here
        "clientState" => $clientState
    ];

    $ch = curl_init($subscriptionUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($subscriptionData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $responseData = json_decode($response, true);

    if ($httpCode === 201) { // 201 Created
        return [
            'id' => $responseData['id'] ?? null,
            'clientState' => $responseData['clientState'] ?? null
        ];
    } else {
        error_log("Error al crear la suscripción a webhook para responsable $responsableId (HTTP $httpCode): " . json_encode($responseData, JSON_PRETTY_PRINT));
        return null;
    }
}

?>