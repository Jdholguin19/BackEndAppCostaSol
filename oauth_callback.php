<?php
// C:\xampp\htdocs\BackEndAppCostaSol\oauth_callback.php

declare(strict_types=1);

ini_set('error_log', __DIR__ . '/config/error_log'); // Added this line

session_start(); // Iniciar sesión para la protección CSRF

file_put_contents(__DIR__ . '/config/csrf_debug.log', "oauth_callback.php: Session ID: " . session_id() . ", oauth_state read: " . ($_SESSION['oauth_state'] ?? 'NOT SET') . "\n", FILE_APPEND);

require_once __DIR__ . '/config/db.php'; // Tu archivo de conexión a la base de datos
require_once __DIR__ . '/config/config_outlook.php'; // Archivo de configuración de Outlook
require_once __DIR__ . '/api/helpers/outlook_auth_helper.php'; // Nuevo archivo de ayuda


header('Content-Type: application/json; charset=utf-8');

// Función para manejar errores y salir
function handleError(string $message, int $httpCode = 500): void {
    http_response_code($httpCode);
    echo json_encode(['ok' => false, 'message' => $message]);
    exit();
}

// 1. Verificar si se recibió un código de autorización y validar el estado CSRF
if (!isset($_GET['code'])) {
    handleError('No se recibió el código de autorización de Microsoft.', 400);
}

$authCode = $_GET['code'];
$state = $_GET['state'] ?? '';

// --- DEBUGGING CSRF STATE ---
$debug_log_file = __DIR__ . '/config/csrf_debug.log';
file_put_contents($debug_log_file, "--- New Attempt ---\n", FILE_APPEND);
file_put_contents($debug_log_file, "GET State: " . $state . "\n", FILE_APPEND);
file_put_contents($debug_log_file, "Session State: " . ($_SESSION['oauth_state'] ?? 'NOT SET IN SESSION') . "\n", FILE_APPEND);
file_put_contents($debug_log_file, "Comparison Result: " . (($_SESSION['oauth_state'] ?? '') === $state ? 'MATCH' : 'NO MATCH') . "\n", FILE_APPEND);
// --- END DEBUGGING CSRF STATE ---

// Validar el estado CSRF

if (empty($state) || !isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
    // Limpiar el estado de la sesión para evitar reintentos con un estado inválido
    unset($_SESSION['oauth_state']);
    handleError('Error de seguridad: Estado CSRF inválido o faltante.', 403);
}

// Una vez validado, eliminar el estado de la sesión
unset($_SESSION['oauth_state']);

// El ID del responsable ahora se obtiene del 'state' validado
$responsableId = (int)$state; // Convertir el estado a ID de responsable

if ($responsableId === 0) {
    handleError('No se pudo identificar al responsable para guardar los tokens.', 400);
}

// 2. Intercambiar el código de autorización por tokens
$tokenUrl = "https://login.microsoftonline.com/" . OUTLOOK_TENANT_ID . "/oauth2/v2.0/token";

$postData = [
    'client_id'     => OUTLOOK_CLIENT_ID,
    'scope'         => OUTLOOK_SCOPES,
    'code'          => $authCode,
    'redirect_uri'  => OUTLOOK_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
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

if ($response === false) {
    handleError('Error de conexión al solicitar tokens: ' . curl_error($ch));
}

$tokenData = json_decode($response, true);

if ($httpCode !== 200 || !isset($tokenData['access_token'])) {
    handleError('Error al obtener tokens de Microsoft: ' . ($tokenData['error_description'] ?? 'Desconocido'), $httpCode);
}

$accessToken = $tokenData['access_token'];
$refreshToken = $tokenData['refresh_token'] ?? null; // El refresh token puede no estar siempre presente si el scope offline_access no se concedió
$expiresIn = $tokenData['expires_in']; // Tiempo en segundos hasta que expire el access token

// Calcular la fecha de expiración
$expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);

// 3. Obtener el ID del usuario de Outlook para vincularlo con el responsable
// Esto es crucial para saber a qué responsable pertenecen estos tokens
// Puedes obtenerlo del id_token si lo solicitaste, o haciendo una llamada a /me
// Por simplicidad, asumiremos que el 'state' contiene el ID del responsable
// En un sistema real, usarías un 'state' generado aleatoriamente y almacenado en sesión
// para prevenir ataques CSRF, y luego vincularías el token al responsable logueado.
// Para este ejemplo, vamos a asumir que el 'state' es el ID del responsable.
// Si no usas 'state', necesitarás otra forma de identificar al responsable.
// Por ejemplo, si el responsable ya está logueado en tu app, puedes obtener su ID de sesión.

// Para este ejemplo, vamos a asumir que el ID del responsable se pasa como 'state'
// En un entorno de producción, el 'state' debe ser un valor aleatorio generado por tu app
// y verificado para prevenir CSRF. El ID del responsable se obtendría de la sesión.
$responsableId = (int)$state; // ¡ADVERTENCIA: Esto es solo para fines de demostración!

if ($responsableId === 0) {
    // Si no se pudo obtener el ID del responsable del 'state', intenta obtenerlo de la sesión
    // o de alguna otra forma segura si el usuario ya está autenticado en tu app.
    // Por ahora, si no hay ID, es un error.
    handleError('No se pudo identificar al responsable para guardar los tokens.', 400);
}

// 4. Guardar los tokens en la base de datos
try {
    $db = DB::getDB();
    $stmt = $db->prepare("
        UPDATE responsable
        SET
            outlook_access_token = :access_token,
            outlook_refresh_token = :refresh_token,
            outlook_token_expires_at = :expires_at
        WHERE id = :responsable_id
    ");
    $stmt->execute([
        ':access_token' => $accessToken,
        ':refresh_token' => $refreshToken,
        ':expires_at' => $expiresAt,
        ':responsable_id' => $responsableId
    ]);

    if ($stmt->rowCount() > 0) {
        // Tokens guardados exitosamente, ahora crear la suscripción al webhook
        $webhookUrl = "https://app.costasol.com.ec/api/outlook_webhook.php"; // URL CORRECTA de tu webhook
        $subscription = createOutlookWebhookSubscription($responsableId, $accessToken, $webhookUrl);

        if ($subscription && isset($subscription['id']) && isset($subscription['clientState'])) {
            // Guardar el ID de suscripción y clientState en la DB
            $stmtSub = $db->prepare("
                UPDATE responsable
                SET
                    outlook_subscription_id = :sub_id,
                    outlook_client_state = :client_state
                WHERE id = :responsable_id
            ");
            $stmtSub->execute([
                ':sub_id' => $subscription['id'],
                ':client_state' => $subscription['clientState'],
                ':responsable_id' => $responsableId
            ]);
            echo json_encode(['ok' => true, 'message' => 'Tokens y suscripción de Outlook guardados exitosamente.']);
        } else {
            // Si la suscripción falla, aún así los tokens se guardaron
            echo json_encode(['ok' => true, 'message' => 'Tokens de Outlook guardados, pero la suscripción al webhook falló.']);
            error_log("Error: Suscripción a webhook fallida para responsable $responsableId.");
        }

        // Opcional: Redirigir al responsable a una página de confirmación o a su panel
        // header('Location: /Front/panel_calendario.php?outlook_connected=true');
        // exit();
    } else {
        handleError('No se pudo actualizar la base de datos para el responsable ' . $responsableId . '.');
    }

} catch (PDOException $e) {
    error_log('Error al guardar tokens en DB: ' . $e->getMessage());
    handleError('Error interno al guardar los tokens.');
}

?>