<?php
// C:\xampp\htdocs\BackEndAppCostaSol\Front\initiate_outlook_auth.php

declare(strict_types=1);

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir el archivo de configuración de Outlook
require_once __DIR__ . '/../config/config_outlook.php';

// --- Lógica para obtener el ID del responsable actual de forma segura ---
$currentResponsableId = 0;

// Asumiendo que la información del usuario logueado está en la sesión
if (isset($_SESSION['cs_usuario'])) {
    $loggedInUser = json_decode($_SESSION['cs_usuario'], true);
    if (isset($loggedInUser['id']) && isset($loggedInUser['is_responsable']) && $loggedInUser['is_responsable']) {
        $currentResponsableId = (int)$loggedInUser['id'];
    }
}
// --- FIN Lógica para obtener el ID del responsable actual ---

if ($currentResponsableId === 0) {
    // Si no se pudo identificar al responsable, redirigir con un error o mostrar un mensaje
    header('Location: perfil.php?error=no_responsable_id');
    exit();
}

// Generar un token CSRF y almacenarlo en la sesión
$oauthState = bin2hex(random_bytes(16));
$stateParam = $currentResponsableId . '_' . $oauthState; // Este es el estado completo que se enviará a Microsoft
$_SESSION['oauth_state'] = $stateParam; // ¡Guardar el estado completo en la sesión!

file_put_contents(__DIR__ . '/../config/csrf_debug.log', "initiate_outlook_auth.php: Session ID: " . session_id() . ", oauth_state set: " . $_SESSION['oauth_state'] . "\n", FILE_APPEND);

// Forzar el guardado y cierre de la sesión antes de la redirección
session_write_close();


// Genera la URL de autorización
$authorizationUrl = "https://login.microsoftonline.com/" . OUTLOOK_TENANT_ID . "/oauth2/v2.0/authorize?" .
                    "client_id=" . urlencode(OUTLOOK_CLIENT_ID) .
                    "&response_type=code" .
                    "&redirect_uri=" . urlencode(OUTLOOK_REDIRECT_URI) .
                    "&response_mode=query" .
                    "&scope=" . urlencode(OUTLOOK_SCOPES) .
                    "&state=" . urlencode($stateParam); // Pasa el ID del responsable y el token CSRF como 'state'

// Redirigir al usuario a la URL de autorización de Microsoft
header('Location: ' . $authorizationUrl);
exit();

?>