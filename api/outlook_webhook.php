<?php
// api/outlook_webhook.php

require_once __DIR__ . '/../helpers/outlook_sync_helper.php';

// 1. Microsoft Graph envía un validationToken para verificar el endpoint la primera vez.
if (isset($_GET['validationToken'])) {
    header('Content-Type: text/plain');
    http_response_code(200);
    echo $_GET['validationToken'];
    exit();
}

// 2. Si no es una validación, es una notificación de cambio (POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Respondemos inmediatamente a Microsoft para que no reintente la notificación.
    http_response_code(202); // 202 Accepted
    flush();

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (isset($data['value']) && is_array($data['value'])) {
        foreach ($data['value'] as $notification) {
            try {
                procesarNotificacionWebhook($notification);
            } catch (\Throwable $e) {
                // Registrar cualquier error inesperado durante el procesamiento.
                log_sync(null, null, 'Outlook -> App', 'WEBHOOK', 'Error', 'Error fatal en webhook: ' . $e->getMessage());
            }
        }
    }
    exit();
}

// 3. Si no es GET de validación ni POST, es una solicitud no válida.
http_response_code(400); // Bad Request
exit();

?>