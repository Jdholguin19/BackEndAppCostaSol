<?php
declare(strict_types=1);

/**
 * Envía una notificación push a un único destinatario usando OneSignal.
 *
 * @param string $title El título de la notificación.
 * @param string $body El cuerpo del mensaje de la notificación.
 * @param string $playerId El OneSignal Player ID del destinatario.
 * @param array|null $data Datos adicionales para enviar en la notificación (e.g., ['pqr_id' => 123]).
 * @return bool Retorna true si la notificación fue aceptada por la API de OneSignal (HTTP 200), false en caso contrario.
 */
function send_one_signal_notification(string $title, string $body, string $playerId, ?array $data = null): bool
{
    $oneSignalAppId = 'e77613c2-51f8-431d-9892-8b2463ecc817';
    $oneSignalApiKey = 'os_v2_app_453bhqsr7bbr3gesrmsgh3gic66q3hsf24becvfqkh44mrzwgvmwtm3k4p47sydyynham5mmlkc4qyigv27jxoage7n3omod5plhxmi';

    $payload = [
        'app_id' => $oneSignalAppId,
        'include_player_ids' => [$playerId],
        'headings' => ['en' => $title, 'es' => $title],
        'contents' => ['en' => $body, 'es' => $body],
        'priority' => 10,
        'ttl' => 86400,      // Time to live: 24 horas
        'expire_in' => 86400 // Se borra 24 horas después de leída
    ];

    if ($data) {
        $payload['data'] = $data;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ' . $oneSignalApiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Considerar TRUE en producción con el bundle de CA correcto

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['id'])) {
            error_log("Notificación enviada a {$playerId}. OneSignal ID: " . $responseData['id']);
            return true;
        }
    }
    
    error_log("Error enviando notificación a {$playerId}. HTTP Code: {$httpCode}. Response: {$response}");
    return false;
}
