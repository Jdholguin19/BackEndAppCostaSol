
1 <?php
     // test_graph_sdk.php - VERSIÓN DE DEPURACIÓN
     
     // Forzar la visualización de errores
     ini_set('display_errors', 1);
     ini_set('display_startup_errors', 1);
     error_reporting(E_ALL);
     
     // 1. Verificar si el autoload de Composer existe
    $autoload_path = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoload_path)) {
        echo "Error: No se encuentra el archivo 'vendor/autoload.php'. Asegúrate de haber ejecutado 'composer require microsoft/microsoft-graph' en la raíz del proyecto en tu servidor.";
        exit;
    }
    
    // 2. Incluir el autoloader
    require_once $autoload_path;
    
    // 3. Intentar usar una clase del SDK
    try {
        $graph = new \Microsoft\Graph\Graph();
        echo "¡Éxito! La librería Microsoft Graph SDK se ha cargado correctamente.";
        // La clase se instanció sin errores, lo que significa que el SDK está disponible.
    } catch (Throwable $e) {
        echo "Ocurrió un error al intentar usar el SDK: " . $e->getMessage();
   }
   
    ?>