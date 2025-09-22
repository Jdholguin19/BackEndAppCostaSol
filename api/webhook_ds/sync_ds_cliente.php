<?php
/**
 * SCRIPT DE SINCRONIZACIÓN MASIVA UNIDIRECCIONAL (KISS FLOW -> APP)
 *
 * Propósito: Este script realiza una sincronización completa desde el dataset 'DS_Documentos_Cliente'
 * de Kiss Flow hacia la base de datos local de la aplicación (tablas `usuario` y `propiedad`).
 *
 * Funcionamiento:
 * 1. Se conecta a la API de Kiss Flow para obtener todos los registros del dataset.
 * 2. Itera sobre cada registro y, usando la cédula como clave, busca si el usuario ya existe localmente.
 * 3. Si el usuario no existe, lo crea junto con su propiedad.
 * 4. Si el usuario ya existe, actualiza sus datos y los de su propiedad.
 * 5. Maneja inconsistencias de datos (campos nulos, formatos inesperados, duplicados) para evitar errores fatales.
 *
 * Ejecución: Este script está diseñado para ser ejecutado manualmente desde un navegador o terminal
 * para realizar una carga inicial o una resincronización completa.
 */
declare(strict_types=1);

// Definimos el contexto para que los logs se impriman en pantalla durante la ejecución manual.
define('SYNC_CONTEXT', 'BULK');

// --- CONFIGURACIÓN Y SETUP INICIAL ---

// Aumentar límites de tiempo y memoria para procesos que pueden ser largos y consumir recursos.
set_time_limit(0);
ini_set('memory_limit', '1024M');

// Configura la salida para que sea texto plano y se muestre en tiempo real en el navegador.
header('Content-Type: text/plain; charset=utf-8');
echo '<pre>';
ob_implicit_flush(true);
ob_start();

// --- GESTIÓN DE LOGS ---
define('LOG_FILE', __DIR__ . '/sync_log.txt');
// Limpia el archivo de log al inicio de cada ejecución.
file_put_contents(LOG_FILE, ''); 

// --- INCLUSIÓN DE DEPENDENCIAS ---

// Carga la configuración de la base de datos.
require_once __DIR__ . '/../../config/db.php';
// Carga las credenciales y la URL de la API de Kiss Flow.
require_once __DIR__ . '/../../kiss_flow/config.php';
// Carga la lógica de procesamiento de registros (que también contiene la función log_message).
require_once __DIR__ . '/sync_logic.php';

// --- FUNCIONES ESPECÍFICAS DE ESTE SCRIPT ---

/**
 * Realiza una llamada genérica a la API de Kiss Flow usando cURL.
 * @param string $url La URL completa del endpoint de la API.
 * @return ?array La respuesta JSON decodificada como un array asociativo, o null si hay un error.
 */
function call_kissflow_api(string $url): ?array {
    $ch = curl_init($url);
    $headers = [
        'Content-Type: application/json',
        'X-Access-Key-ID: ' . KISSFLOW_ACCESS_KEY_ID,
        'X-Access-Key-Secret: ' . KISSFLOW_ACCESS_KEY_SECRET
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        log_message("ERROR cURL para $url: $error");
        return null;
    }
    if ($http_code >= 300) {
        log_message("ERROR API para $url. Código: $http_code. Respuesta: $response");
        return null;
    }
    return json_decode($response, true);
}

// --- LÓGICA PRINCIPAL DE SINCRONIZACIÓN ---

log_message("INICIO DE LA SINCRONIZACIÓN MASIVA.");

$conn = DB::getDB();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Contadores para el reporte final.
$processed_count = 0;
$created_users = 0;
$updated_users = 0;
$created_properties = 0;
$updated_properties = 0;
$errors = 0;

$page_number = 1;
$page_size = 50; // Se procesan 50 registros por cada llamada a la API para no sobrecargarla.

// Bucle principal para manejar la paginación de la API de Kiss Flow.
do {
    log_message("Procesando página $page_number...");
    $dataset_id = 'DS_Documentos_Cliente';
    $list_url = KISSFLOW_API_HOST . "/dataset/2/AcNcc9rydX9F/{$dataset_id}/list?page_number={$page_number}&page_size={$page_size}";
    
    $response = call_kissflow_api($list_url);
    $records = $response['Data'] ?? [];

    // Si la API no devuelve más registros, se termina el bucle.
    if (empty($records)) {
        log_message("No se encontraron más registros. Fin del proceso.");
        break;
    }

    // Itera sobre cada registro de Kiss Flow obtenido en la página actual.
    foreach ($records as $record) {
        $processed_count++;
        
        $result = process_kissflow_record($record, $conn);

        // Se incrementan los contadores según el resultado de la función de procesamiento.
        if ($result['error']) {
            $errors++;
        } elseif ($result['skipped']) {
            $errors++; // Contar cédulas vacías como errores en el reporte final.
        } else {
            if ($result['user_created']) $created_users++;
            if ($result['user_updated']) $updated_users++;
            if ($result['property_created']) $created_properties++;
            if ($result['property_updated']) $updated_properties++;
        }
    }

    $page_number++;

} while (true);

log_message("\n--- REPORTE FINAL ---");
log_message("Registros de Kiss Flow procesados: $processed_count");
log_message("Usuarios nuevos creados: $created_users");
log_message("Usuarios existentes actualizados: $updated_users");
log_message("Propiedades nuevas creadas: $created_properties");
log_message("Propiedades existentes actualizadas: $updated_properties");
log_message("Errores: $errors");
log_message("SINCRONIZACIÓN COMPLETADA.");

echo '</pre>';