<?php
declare(strict_types=1);

// --- CONFIGURACIÓN Y SETUP INICIAL ---
set_time_limit(0);
ini_set('memory_limit', '1024M');
header('Content-Type: text/plain; charset=utf-8');
echo '<pre>';
ob_implicit_flush(true);
ob_start();

// --- GESTIÓN DE LOGS ---
define('LOG_FILE', __DIR__ . '/sync_db_to_kf_log.txt');
file_put_contents(LOG_FILE, ''); // Limpia el archivo de log al inicio

// --- INCLUSIÓN DE DEPENDENCIAS ---
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../../kiss_flow/config.php'; // Para KISSFLOW_API_HOST, KISSFLOW_ACCESS_KEY_ID, KISSFLOW_ACCESS_KEY_SECRET

// --- FUNCIONES AUXILIARES ---

/**
 * Imprime un mensaje de log en pantalla y lo guarda en un archivo.
 * @param string $message El mensaje a registrar.
 */
function log_message(string $message): void {
    $log_entry = '[' . date('Y-m-d H:i:s') . '] ' . $message . "
";
    echo $log_entry;
    ob_flush();
    flush();
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Realiza una llamada a la API de Kiss Flow para upsert de registros de dataset en bulk.
 * @param string $dataset_id El ID del dataset de Kiss Flow.
 * @param array $records_data Un array de registros a upsert. Cada registro debe incluir '_id' si es una actualización.
 * @return ?array La respuesta JSON decodificada, o null si hay un error.
 */
function call_kissflow_upsert_api(string $dataset_id, array $records_data): ?array {
    // Endpoint para upsert en bulk. Se asume que es un POST a /records/bulk.
    $url = KISSFLOW_API_HOST . "/dataset/2/AcNcc9rydX9F/{$dataset_id}/records/bulk";
    
    $ch = curl_init($url);
    $headers = [
        'Content-Type: application/json',
        'X-Access-Key-ID: ' . KISSFLOW_ACCESS_KEY_ID,
        'X-Access-Key-Secret: ' . KISSFLOW_ACCESS_KEY_SECRET
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($records_data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        log_message("ERROR cURL para upsert en Kiss Flow: $error");
        return null;
    }
    if ($http_code >= 300) {
        log_message("ERROR API para upsert en Kiss Flow. Código: $http_code. Respuesta: $response");
        return null;
    }
    return json_decode($response, true);
}

// --- LÓGICA PRINCIPAL DE SINCRONIZACIÓN ---

log_message("INICIO DE LA SINCRONIZACIÓN DE DB LOCAL A KISS FLOW.");

$conn = DB::getDB();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Archivo para almacenar el timestamp de la última sincronización
define('LAST_SYNC_TIMESTAMP_FILE', __DIR__ . '/last_sync_timestamp.txt');
$last_sync_timestamp = '2000-01-01 00:00:00'; // Valor por defecto para la primera ejecución

if (file_exists(LAST_SYNC_TIMESTAMP_FILE)) {
    $last_sync_timestamp = trim(file_get_contents(LAST_SYNC_TIMESTAMP_FILE));
}

$current_timestamp = date('Y-m-d H:i:s');

// 1. Obtener registros modificados de la base de datos local
// Se unen las tablas usuario y propiedad para obtener todos los datos necesarios.
// Se filtran por last_modified en ambas tablas.
$stmt = $conn->prepare("
    SELECT
        u.cedula AS Identificacion,
        u.nombres,
        u.apellidos,
        u.correo AS Comentario_Gerencia,
        u.telefono AS Tipo_de_Fachada,
        u.kissflow_convenio AS Convenio,
        p.kissflow_ds_id AS _id,
        p.kissflow_proyecto AS Proyecto,
        p.manzana,
        p.villa,
        p.fecha_entrega,
        etapa.nombre AS Etapa,
        tipo.nombre AS Modelo
    FROM
        usuario u
    JOIN
        propiedad p ON u.id = p.id_usuario
    LEFT JOIN
        etapa_construccion etapa ON p.etapa_id = etapa.id
    LEFT JOIN
        tipo_propiedad tipo ON p.tipo_id = tipo.id
    WHERE
        u.last_modified > :last_sync_timestamp
        OR p.last_modified > :last_sync_timestamp
");
$stmt->execute([':last_sync_timestamp' => $last_sync_timestamp]);
$modified_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

log_message("Se encontraron " . count($modified_records) . " registros modificados desde la última sincronización.");

$dataset_id = 'DS_Documentos_Cliente';
$upserted_count = 0;
$errors_count = 0;

foreach ($modified_records as $record) {
    try {
        // Construir el payload para Kiss Flow
        $kissflow_payload = [
            '_id' => $record['_id'], // ID del registro en Kiss Flow para la actualización
            'Identificacion' => $record['Identificacion'],
            'Nombre_Cliente' => trim($record['nombres'] . ' ' . $record['apellidos']),
            'Comentario_Gerencia' => $record['Comentario_Gerencia'],
            'Tipo_de_Fachada' => $record['Tipo_de_Fachada'],
            'Convenio' => $record['Convenio'],
            'Etapa' => $record['Etapa'],
            'Modelo' => $record['Modelo'],
            'Mz_Solar' => trim($record['manzana'] . ' / ' . $record['villa']),
            'Proyecto' => $record['Proyecto'],
            'Fecha_de_Entrega' => $record['fecha_entrega'],
        ];

        // Filtrar campos nulos o vacíos para no sobrescribir datos en Kiss Flow si no hay cambios locales
        $kissflow_payload = array_filter($kissflow_payload, fn($value) => !is_null($value) && $value !== '');

        // Llamar a la API de Kiss Flow para upsert (enviando un array con un solo registro)
        $response = call_kissflow_upsert_api($dataset_id, [$kissflow_payload]);

        // Asumiendo que la API de bulk upsert devuelve un array de resultados, y cada resultado tiene un 'status'
        // O que el 'status' general indica el éxito de la operación.
        // Esto puede necesitar ajuste si la respuesta real de Kiss Flow es diferente.
        if ($response && !empty($response['Records ingested'])) { // Asumiendo que 'Records ingested' indica éxito
            log_message("Registro con _id '{$record['_id']}' (Cédula: {$record['Identificacion']}) upserted exitosamente en Kiss Flow.");
            $upserted_count++;
        } else {
            log_message("ERROR al upsertar registro con _id '{$record['_id']}' (Cédula: {$record['Identificacion']}) en Kiss Flow. Respuesta: " . json_encode($response));
            $errors_count++;
        }

    } catch (Exception $e) {
        log_message("ERROR CRÍTICO al procesar registro con _id '{$record['_id']}' (Cédula: {$record['Identificacion']}): " . $e->getMessage());
        $errors_count++;
    }
}

// Guardar el timestamp de la sincronización actual para la próxima ejecución
file_put_contents(LAST_SYNC_TIMESTAMP_FILE, $current_timestamp);

log_message("
--- REPORTE FINAL ---");
log_message("Registros procesados: " . count($modified_records));
log_message("Registros upserted en Kiss Flow: $upserted_count");
log_message("Errores: $errors_count");
log_message("SINCRONIZACIÓN DE DB LOCAL A KISS FLOW COMPLETADA.");

echo '</pre>';
