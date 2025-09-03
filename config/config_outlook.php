<?php
// C:\xampp\htdocs\BackEndAppCostaSol\config\config_outlook.php

// Credenciales de tu aplicación registrada en Azure AD para la sincronización de calendarios
define('OUTLOOK_CLIENT_ID', '1c362ad5-fc03-4a41-b285-ca625dcfca81'); // ID de aplicación (cliente)
define('OUTLOOK_CLIENT_SECRET', 'RVT8Q~coGVsmTLnBzu3qnY8OX5mH_PiebGOlvdl8'); // Valor del secreto de cliente
define('OUTLOOK_REDIRECT_URI', 'https://app.costasol.com.ec/oauth_callback.php'); // URI de redirección configurada en Azure AD (ej. http://localhost/BackEndAppCostaSol/oauth_callback.php)
define('OUTLOOK_TENANT_ID', 'common'); // O tu ID de inquilino específico si solo permites usuarios de tu organización
define('OUTLOOK_SCOPES', 'openid profile offline_access Calendars.ReadWrite'); // Permisos solicitados
?>