<?php
// test_graph_sdk.php - VERSIÓN FINAL CORREGIDA

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

// Importar las clases necesarias
use Microsoft\Kiota\Authentication\Oauth\AuthorizationCodeContext;
use Microsoft\Graph\GraphServiceClient;

try {
    // Para este test, solo necesitamos demostrar que podemos crear los objetos
    // sin que falle, usando las clases y constructores correctos.
    // Usamos valores ficticios.
    $tenantId = 'tu_tenant_id';
    $clientId = 'tu_client_id';
    $clientSecret = 'tu_client_secret';
    $authorizationCode = 'dummy_auth_code';
    $redirectUri = 'https://localhost/callback';

    // 1. Creamos el contexto de autenticación (los datos para el login)
    $tokenRequestContext = new AuthorizationCodeContext(
        $tenantId,
        $clientId,
        $clientSecret,
        $authorizationCode,
        $redirectUri
    );

    // 2. Pasamos el CONTEXTO directamente al cliente principal, como indica el error.
    // La librería se encarga de crear el adaptador y el proveedor por debajo.
    $graphServiceClient = new GraphServiceClient($tokenRequestContext);

    echo "<h1>¡Éxito Definitivo!</h1>";
    echo "<p>La librería <strong>Microsoft Graph SDK v2</strong> y sus componentes de autenticación se han cargado correctamente.</p>";
    echo "<p>Se ha instanciado <code>GraphServiceClient</code> pasándole el contexto de autenticación, tal como requiere esta versión del SDK.</p>";
    echo "<p><b>El entorno está listo.</b> Ya podemos proceder con el plan de acción para sincronizar las citas.</p>";

} catch (Throwable $e) {
    echo "<h1>Ocurrió un error al intentar usar el SDK v2</h1>";
    echo "<p>Este error es inesperado. Por favor, revisa los detalles:</p>";
    echo "<b>Mensaje:</b> " . $e->getMessage() . "<br>";
    echo "<b>Archivo:</b> " . $e->getFile() . "<br>";
    echo "<b>Línea:</b> " . $e->getLine() . "<br>";
    echo "<pre>Trace: " . $e->getTraceAsString() . "</pre>";
}
?>