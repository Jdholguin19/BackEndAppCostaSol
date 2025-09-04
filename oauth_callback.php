<?php
// C:\xampp\htdocs\BackEndAppCostaSol\oauth_callback.php

declare(strict_types=1);

// --- MODO DE DEPURACIÓN --- 
// Forzar la visualización de todos los errores para encontrar la causa del error 500.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN MODO DE DEPURACIÓN ---

// Forzar la limpieza de la caché de OPcache, que puede causar errores de "archivo no encontrado" en el servidor.
if (function_exists('opcache_reset')) {
    opcache_reset();
}

ini_set('error_log', __DIR__ . '/config/error_log');

// La sesión ya no es necesaria para la validación CSRF en este flujo modificado.

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/config_outlook.php';
require_once __DIR__ . '/api/helpers/outlook_auth_helper.php';

header('Content-Type: application/json; charset=utf-8');

// Función para manejar errores y salir
function handleError(string $message, int $httpCode = 500): void {
    http_response_code($httpCode);
    echo json_encode(['ok' => false, 'message' => $message]);
    exit();
}

// 1. Verificar si se recibió un código de autorización y el estado
if (!isset($_GET['code']) || !isset($_GET['state'])) {
    handleError('No se recibió el código de autorización o el estado de Microsoft.', 400);
}

$authCode = $_GET['code'];
$state = $_GET['state'];

// En nuestro flujo modificado, el 'state' es directamente el ID del responsable.
$responsableId = (int)$state;

if ($responsableId === 0) {
    handleError('El estado recibido no es un ID de responsable válido.', 400);
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
        // --- INICIO: Obtener y guardar el ID del calendario principal (con cURL para evadir problemas de autoloader) ---
        $calendarId = null;
        try {
            $graphUrl = "https://graph.microsoft.com/v1.0/me/calendars";
            
            $ch = curl_init($graphUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $calendarsData = json_decode($response, true);
                $calendars = $calendarsData['value'] ?? [];

                // Buscar el calendario por defecto
                foreach ($calendars as $calendar) {
                    if (!empty($calendar['isDefaultCalendar'])) {
                        $calendarId = $calendar['id'];
                        break;
                    }
                }
                // Fallback por si no hay un calendario por defecto, buscar uno llamado 'Calendar' o 'Calendario'
                if (!$calendarId) {
                    foreach ($calendars as $calendar) {
                        if (isset($calendar['name']) && in_array(strtolower($calendar['name']), ['calendar', 'calendario'])) {
                            $calendarId = $calendar['id'];
                            break;
                        }
                    }
                }
                // Fallback final: tomar el primer calendario de la lista
                if (!$calendarId && !empty($calendars)) {
                    $calendarId = $calendars[0]['id'];
                }

                if ($calendarId) {
                    $stmtCal = $db->prepare("UPDATE responsable SET outlook_calendar_id = :calendar_id WHERE id = :id");
                    $stmtCal->execute([':calendar_id' => $calendarId, ':id' => $responsableId]);
                }
            } else {
                throw new Exception("Error al llamar a Graph API para obtener calendarios. HTTP Code: $httpCode. Response: $response");
            }

        } catch (\Throwable $e) {
            // Si falla la obtención del calendario, redirigimos con un error específico.
            $errorMessage = "Conexión exitosa, pero no se pudo obtener el ID del calendario. Error: " . $e->getMessage();
            // Limpiamos el mensaje para que sea seguro en una URL
            $errorParam = urlencode($errorMessage);
            header("Location: /Front/perfil.php?outlook_status=error_calendar_id&error_message={$errorParam}");
            exit();
        }
        // --- FIN: Obtener y guardar el ID del calendario principal ---

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
            // Redirigir a una página de éxito en el frontend
            header('Location: /Front/perfil.php?outlook_status=success');
            exit();
        } else {
            // Si la suscripción falla, aún así los tokens se guardaron
            error_log("Error: Suscripción a webhook fallida para responsable $responsableId.");
            header('Location: /Front/perfil.php?outlook_status=error_subscription');
            exit();
        }
    } else {
        handleError('No se pudo actualizar la base de datos para el responsable ' . $responsableId . '.');
    }

} catch (PDOException $e) {
    error_log('Error al guardar tokens en DB: ' . $e->getMessage());
    handleError('Error interno al guardar los tokens.');
}

?>