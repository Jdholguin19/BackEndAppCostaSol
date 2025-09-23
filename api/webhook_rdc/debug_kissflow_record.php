<?php
declare(strict_types=1);

// --- CONFIGURACIÓN Y SETUP INICIAL ---
set_time_limit(0);
ini_set('memory_limit', '1024M');
header('Content-Type: text/plain; charset=utf-8');
echo '<pre>';
ob_implicit_flush(true);
ob_start();

// --- INCLUSIÓN DE DEPENDENCIAS ---
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../../kiss_flow/config.php';

/**
 * Realiza una llamada a la API de Kiss Flow para obtener registros
 */
function call_kissflow_api(string $url): ?array {
    $ch = curl_init($url);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Access-Key-Id: ' . KISSFLOW_ACCESS_KEY_ID,
        'X-Access-Key-Secret: ' . KISSFLOW_ACCESS_KEY_SECRET
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "ERROR cURL: $error\n";
        return null;
    }
    
    if ($http_code >= 300) {
        echo "ERROR API. Código: $http_code. Respuesta: " . substr($response, 0, 500) . "...\n";
        return null;
    }
    
    return json_decode($response, true);
}

echo "=== DEBUG: CONSULTANDO REGISTRO EN KISS FLOW ===\n";

// Obtener todos los registros y buscar manualmente
$search_url = KISSFLOW_API_HOST . "/dataset/2/AcNcc9rydX9F/DS_Documentos_Cliente/list?page_number=1&page_size=1000";
echo "URL de búsqueda: $search_url\n\n";

$response = call_kissflow_api($search_url);

if ($response && isset($response['Data'])) {
    $records = $response['Data'];
    echo "=== BÚSQUEDA MANUAL EN " . count($records) . " REGISTROS ===\n\n";
    
    $found_records = [];
    
    // Buscar manualmente por cédula 09864124
    foreach ($records as $record) {
        if (isset($record['Identificacion']) && $record['Identificacion'] === '09864124') {
            $found_records[] = $record;
        }
    }
    
    echo "=== REGISTROS ENCONTRADOS CON CÉDULA 09864124 ===\n";
    echo "Se encontraron " . count($found_records) . " registros:\n\n";
    
    foreach ($found_records as $index => $record) {
        echo "--- REGISTRO " . ($index + 1) . " ---\n";
        echo "_id: " . ($record['_id'] ?? 'NO ENCONTRADO') . "\n";
        echo "Name (Key): " . ($record['Name'] ?? 'NO ENCONTRADO') . "\n";
        echo "Identificacion: " . ($record['Identificacion'] ?? 'NO ENCONTRADO') . "\n";
        echo "Convenio: " . ($record['Convenio'] ?? 'NO ENCONTRADO') . "\n";
        echo "Nombre_Cliente: " . ($record['Nombre_Cliente'] ?? 'NO ENCONTRADO') . "\n";
        echo "\n";
    }
    
    // Buscar por convenio ANV-2888
    $found_convenio = [];
    foreach ($records as $record) {
        if (isset($record['Convenio']) && $record['Convenio'] === 'ANV-2888') {
            $found_convenio[] = $record;
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
    echo "=== REGISTROS ENCONTRADOS CON CONVENIO ANV-2888 ===\n";
    echo "Se encontraron " . count($found_convenio) . " registros:\n\n";
    
    foreach ($found_convenio as $index => $record) {
        echo "--- REGISTRO " . ($index + 1) . " ---\n";
        echo "_id: " . ($record['_id'] ?? 'NO ENCONTRADO') . "\n";
        echo "Name (Key): " . ($record['Name'] ?? 'NO ENCONTRADO') . "\n";
        echo "Identificacion: " . ($record['Identificacion'] ?? 'NO ENCONTRADO') . "\n";
        echo "Convenio: " . ($record['Convenio'] ?? 'NO ENCONTRADO') . "\n";
        echo "Nombre_Cliente: " . ($record['Nombre_Cliente'] ?? 'NO ENCONTRADO') . "\n";
        echo "\n";
    }
    
} else {
    echo "ERROR: No se pudo obtener los registros\n";
    echo "Respuesta: " . json_encode($response) . "\n";
}

echo "=== FIN DEL DEBUG ===\n";
echo '</pre>';
