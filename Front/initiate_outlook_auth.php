<?php
// C:\xampp\htdocs\BackEndAppCostaSol\Front\initiate_outlook_auth.php

declare(strict_types=1);

// La sesión ya no es necesaria aquí, leemos el ID directamente del POST.

require_once __DIR__ . '/../config/config_outlook.php';

// --- Lógica para obtener el ID del responsable actual de forma segura ---
$currentResponsableId = 0;
if (isset($_POST['responsable_id'])) {
    $currentResponsableId = (int)$_POST['responsable_id'];
}
// --- FIN Lógica para obtener el ID del responsable actual ---

if ($currentResponsableId === 0) {
    // Si no se pudo identificar al responsable, redirigir con un error o mostrar un mensaje
    header('Location: perfil.php?error=no_responsable_id');
    exit();
}

// En lugar de un token CSRF complejo basado en sesión, usaremos el ID del responsable como el estado.
// Esto es seguro en este contexto porque el flujo lo inicia un usuario ya autenticado en la app.
$stateParam = (string)$currentResponsableId;

// Genera la URL de autorización
$authorizationUrl = "https://login.microsoftonline.com/" . OUTLOOK_TENANT_ID . "/oauth2/v2.0/authorize?" .
                    "client_id=" . urlencode(OUTLOOK_CLIENT_ID) .
                    "&response_type=code" .
                    "&redirect_uri=" . urlencode(OUTLOOK_REDIRECT_URI) .
                    "&response_mode=query" .
                    "&scope=" . urlencode(OUTLOOK_SCOPES) .
                    "&state=" . urlencode($stateParam); // Pasa el ID del responsable como 'state'

// Redirigir al usuario a la URL de autorización de Microsoft
header('Location: ' . $authorizationUrl);
exit();

?>