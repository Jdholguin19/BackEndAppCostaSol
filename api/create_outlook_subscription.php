<?php
// Ejemplo: C:\xampp\htdocs\BackEndAppCostaSol\api\create_outlook_subscription.php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config_outlook.php';

// --- Función para refrescar el token de acceso (necesitarás esta función) ---
// Esta función debería ser similar a la lógica que ya tienes en oauth_callback.php
// para intercambiar el refresh_token por un nuevo access_token.
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
        $stmt = $db->prepare(
            "UPDATE responsable
            SET
                outlook_access_token = :access_token,
                outlook_refresh_token = :refresh_token,
                outlook_token_expires_at = :expires_at
            WHERE id = :responsable_id"
        );
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
// --- FIN Función para refrescar el token de acceso ---


// --- Lógica para crear la suscripción ---
// Asume que tienes el ID del responsable y sus tokens de la DB
$responsableId = 2; // Reemplaza con el ID del responsable real
try {
    $db = DB::getDB();
    $stmt = $db->prepare("SELECT outlook_access_token, outlook_refresh_token, outlook_token_expires_at FROM responsable WHERE id = :id");
    $stmt->execute([':id' => $responsableId]);
    $respData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$respData || !$respData['outlook_access_token']) {
        die("No se encontraron tokens de Outlook para el responsable $responsableId.");
    }

    $accessToken = $respData['outlook_access_token'];
    $refreshToken = $respData['outlook_refresh_token'];
    $expiresAt = strtotime($respData['outlook_token_expires_at']);

    // Refrescar token si está expirado o a punto de expirar (ej. en los próximos 5 minutos)
    if (time() >= $expiresAt - 300) { // 300 segundos = 5 minutos
        $accessToken = refreshOutlookAccessToken($responsableId, $refreshToken);
        if (!$accessToken) {
            die("No se pudo refrescar el token de acceso para el responsable $responsableId.");
        }
    }

    // Generar un clientState único y secreto para esta suscripción
    // Este valor debe ser almacenado en tu DB junto con el ID de la suscripción
    // para verificar la autenticidad de las notificaciones.
    $clientState = bin2hex(random_bytes(16)); // Genera un string aleatorio de 32 caracteres

    $subscriptionUrl = "https://graph.microsoft.com/v1.0/subscriptions";
    $webhookUrl = "https://app.costasol.com.ec/api/outlook_webhook.php"; // ¡Asegúrate de que esta URL sea pública!

    $subscriptionData = [
        "changeType" => "created,updated,deleted",
        "notificationUrl" => $webhookUrl,
        "resource" => "me/calendar/events",
        "expirationDateTime" => date('Y-m-d\TH:i:s.000Z', strtotime('+2 days')), // Expira en 2 días
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
        echo "Suscripción a webhook creada exitosamente:\n";
        echo "ID de Suscripción: " . ($responseData['id'] ?? 'N/A') . "\n";
        echo "clientState: " . ($responseData['clientState'] ?? 'N/A') . "\n";
        echo "expirationDateTime: " . ($responseData['expirationDateTime'] ?? 'N/A') . "\n";

        // ¡IMPORTANTE! Guarda el ID de suscripción y el clientState en tu base de datos
        // para el responsable correspondiente. Los necesitarás para renovar la suscripción
        // y para verificar la autenticidad de las notificaciones.
        $stmt = $db->prepare("UPDATE responsable SET outlook_subscription_id = :sub_id, outlook_client_state = :client_state WHERE id = :id");
        $stmt->execute([
            ':sub_id' => $responseData['id'],
            ':client_state' => $clientState,
            ':id' => $responsableId
        ]);
        echo "ID de suscripción y clientState guardados en la DB.\n";

    } else {
        echo "Error al crear la suscripción a webhook (HTTP $httpCode):\n";
        echo "Respuesta: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }

} catch (Throwable $e) {
    error_log("Error al crear suscripción Outlook: " . $e->getMessage());
    echo "Error interno: " . $e->getMessage() . "\n";
}

?>