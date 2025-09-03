<?php
// C:\xampp\htdocs\BackEndAppCostaSol\api\outlook_webhook.php

// No es necesario iniciar sesión aquí, ya que es un endpoint público para Microsoft Graph.
// require_once __DIR__ . '/../config/db.php'; // Solo si necesitas DB para validación o procesamiento inmediato
// require_once __DIR__ . '/../config/config_outlook.php'; // Solo si necesitas credenciales para validación o procesamiento inmediato

header('Content-Type: text/plain'); // Microsoft Graph espera una respuesta de texto plano para la validación

// Manejar la solicitud de validación de suscripción
if (isset($_GET['validationToken'])) {
    // Microsoft Graph envía un validationToken en la URL para verificar el endpoint.
    // Debes devolver este token como respuesta de texto plano.
    echo $_GET['validationToken'];
    http_response_code(200); // Código de estado HTTP 200 OK
    exit();
}

// Si no es una solicitud de validación, es una notificación de cambio (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí es donde recibirás las notificaciones de cambios del calendario.
    // El cuerpo de la solicitud será JSON.
    $input = file_get_contents('php://input');
    $notification = json_decode($input, true);

    // --- Lógica de Procesamiento de Notificaciones ---
    // Por ahora, solo registraremos la notificación para verificar que funciona.
    // En un entorno de producción, aquí procesarías los cambios en el calendario.
    error_log("Webhook Notification Received: " . json_encode($notification, JSON_PRETTY_PRINT));

    // Microsoft Graph espera un código de estado HTTP 202 Accepted para confirmar que recibiste la notificación.
    http_response_code(202);
    exit();
}

// Si la solicitud no es GET con validationToken ni POST, es una solicitud no válida.
http_response_code(400); // Bad Request
echo "Solicitud no válida.";
exit();

?>