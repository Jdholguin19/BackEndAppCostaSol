<?php
/**
 * SCRIPT PARA SINCRONIZAR LOS KEYS REALES DE KISS FLOW
 * 
 * Este script obtiene todos los registros de Kiss Flow y actualiza
 * el campo kissflow_ds_id en la DB local con el Key real.
 */
declare(strict_types=1);

set_time_limit(0);
ini_set('memory_limit', '1024M');
header('Content-Type: text/plain; charset=utf-8');
echo '<pre>';
ob_implicit_flush(true);
ob_start();

define('LOG_FILE', __DIR__ . '/sync_keys_log.txt');
file_put_contents(LOG_FILE, '');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../../kiss_flow/config.php';

function log_message(string $message): void {
    $log_entry = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    echo $log_entry;
    ob_flush();
    flush();
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

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
        log_message("ERROR cURL: $error");
        return null;
    }
    if ($http_code >= 300) {
        log_message("ERROR API. Código: $http_code. Respuesta: $response");
        return null;
    }
    return json_decode($response, true);
}

log_message("INICIO DE SINCRONIZACIÓN DE KEYS DESDE KISS FLOW.");

$conn = DB::getDB();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Archivo para almacenar el timestamp de la última sincronización de keys
define('LAST_KEYS_SYNC_TIMESTAMP_FILE', __DIR__ . '/last_keys_sync_timestamp.txt');
$last_sync_timestamp = '2000-01-01 00:00:00'; // Valor por defecto para la primera ejecución

if (file_exists(LAST_KEYS_SYNC_TIMESTAMP_FILE)) {
    $last_sync_timestamp = trim(file_get_contents(LAST_KEYS_SYNC_TIMESTAMP_FILE));
}

$current_timestamp = date('Y-m-d H:i:s');
log_message("Sincronizando keys modificados desde: $last_sync_timestamp");

$updated_count = 0;
$page_number = 1;
$page_size = 50;

do {
    log_message("Procesando página $page_number...");
    // Agregar filtro de fecha para solo obtener registros modificados
    $list_url = KISSFLOW_API_HOST . "/dataset/2/AcNcc9rydX9F/DS_Documentos_Cliente/list?page_number={$page_number}&page_size={$page_size}&modified_after=" . urlencode($last_sync_timestamp);
    
    $response = call_kissflow_api($list_url);
    $records = $response['Data'] ?? [];

    if (empty($records)) {
        log_message("No se encontraron más registros modificados.");
        break;
    }

    foreach ($records as $kf_record) {
        $kf_key = $kf_record['Name'] ?? null; // Cambiar de 'Key' a 'Name'
        $kf_identificacion = $kf_record['Identificacion'] ?? null;
        
        if (!$kf_key || !$kf_identificacion) {
            log_message("Registro sin Name o Identificación: " . json_encode($kf_record));
            continue;
        }

        log_message("Procesando cédula de KF: $kf_identificacion, Name: $kf_key");

        // Buscar en la DB local por cédula
        $stmt = $conn->prepare("SELECT id, kissflow_key FROM usuario WHERE cedula = :cedula LIMIT 1");
        $stmt->execute([':cedula' => $kf_identificacion]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $user_id = $user_data['id'];
            $existing_key = $user_data['kissflow_key'];
            
            // Solo actualizar si no tiene key o si el key es diferente
            if (empty($existing_key) || $existing_key !== $kf_key) {
                log_message("Actualizando key para cédula $kf_identificacion: $existing_key -> $kf_key");
                $stmt_update = $conn->prepare("UPDATE usuario SET kissflow_key = :kf_key WHERE id = :user_id");
                if ($stmt_update->execute([':kf_key' => $kf_key, ':user_id' => $user_id])) {
                    log_message("Actualizado kissflow_key para cédula $kf_identificacion: $kf_key");
                    $updated_count++;
                }
            } else {
                log_message("Key ya existe y es correcto para cédula $kf_identificacion: $kf_key");
            }
        } else {
            log_message("NO encontrado usuario local para cédula: $kf_identificacion");
        }
    }

    $page_number++;
    usleep(500000); // Delay para evitar rate limiting

} while (true);

// Guardar el timestamp de la sincronización de keys
file_put_contents(LAST_KEYS_SYNC_TIMESTAMP_FILE, $current_timestamp);

log_message("--- REPORTE FINAL ---");
log_message("Keys actualizados: $updated_count");
log_message("SINCRONIZACIÓN DE KEYS COMPLETADA.");

echo '</pre>';
