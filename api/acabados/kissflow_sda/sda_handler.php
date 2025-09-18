<?php
declare(strict_types=1);

// Establecer encabezado de respuesta como JSON
header('Content-Type: application/json');

// Incluir el archivo de configuración de Kiss Flow
require_once __DIR__ . '/../../../kiss_flow/config.php';

// --- Funciones Auxiliares para llamadas a la API ---

/**
 * Realiza una llamada a la API de Kiss Flow.
 * @param string $url La URL del endpoint.
 * @param string $method El método HTTP (GET, POST).
 * @param ?array $payload El cuerpo de la petición para POST.
 * @return ?array La respuesta decodificada de la API o null si hay error.
 */
function call_kissflow_api(string $url, string $method = 'GET', ?array $payload = null): ?array {
    $ch = curl_init($url);

    $headers = [
        'Content-Type: application/json',
        'X-Access-Key-ID: ' . KISSFLOW_ACCESS_KEY_ID,
        'X-Access-Key-Secret: ' . KISSFLOW_ACCESS_KEY_SECRET
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($payload) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("Error en cURL para $url: $error");
        return null;
    }

    if ($http_code >= 300) {
        error_log("Error de API para $url. Código: $http_code. Respuesta: $response");
        return null;
    }

    return json_decode($response, true);
}

// --- Lógica Principal del Handler ---

$response = ['ok' => false, 'mensaje' => 'Ocurrió un error inesperado.'];

try {
    // 1. Leer y validar los datos de entrada
    $input_data = json_decode(file_get_contents('php://input'), true);
    if (!$input_data) {
        throw new Exception('No se recibieron datos de entrada o el formato es incorrecto.');
    }

    $cedula = $input_data['cedula'] ?? null;
    if (!$cedula) {
        throw new Exception('El número de cédula es obligatorio para continuar.');
    }

    // 2. PASO 1: Buscar al cliente en el Dataset de Kiss Flow
    $encoded_cedula = urlencode($cedula);
    $search_url = KISSFLOW_API_HOST . "/dataset/2/AcNcc9rydX9F/DS_Documentos_Cliente/list?q={$encoded_cedula}&search_field=Identificacion";
    $cliente_response = call_kissflow_api($search_url);
    $cliente_data = $cliente_response['Data'][0] ?? null;

    if (!$cliente_data || !isset($cliente_data['_id'])) {
        throw new Exception("Cliente con cédula {$cedula} no fue encontrado en Kiss Flow. No se puede continuar.");
    }
    
    $kissflow_cliente_id = $cliente_data['_id'];
    $convenio_from_kissflow = $cliente_data['Convenio'] ?? 'N/A';

    // 3. PASO 2: Iniciar el proceso en Kiss Flow con los datos iniciales
    $process_name = 'Copia_de_Eleccio_n_de_Acabados_y_Adicion';
    $init_url = KISSFLOW_API_HOST . "/process/2/AcNcc9rydX9F/{$process_name}";
    
    // Se utilizan los datos obtenidos del Dataset de Kiss Flow para asegurar consistencia.
    $initial_payload = [
        // El campo 'Ubicacion' es el lookup al dataset DS_Documentos_Cliente
        'Ubicacion' => ['_id' => $kissflow_cliente_id],
        
        // El resto de los campos se rellenan con los datos del dataset encontrado
        'Identificacion' => $cliente_data['Identificacion'] ?? 'N/A',
        'Nombre_del_Cliente' => $cliente_data['Nombre_Cliente'] ?? 'N/A', // Mapeado desde el dataset
        'Convenio' => $convenio_from_kissflow,
        'Mz_Solar' => $cliente_data['Mz_Solar'] ?? 'N/A',
        'Etapa' => $cliente_data['Etapa'] ?? 'N/A',
        'Modelo_1' => $cliente_data['Modelo'] ?? 'N/A', // Mapeado desde el dataset
        'Fecha' => date('Y-m-d'),
    ];

    $init_response = call_kissflow_api($init_url, 'POST', $initial_payload); 

    if (empty($init_response) || !isset($init_response['_id']) || !isset($init_response['_activity_instance_id'])) {
        throw new Exception('No se pudo iniciar el proceso en Kiss Flow o la respuesta no contiene los IDs necesarios.');
    }
    
    $item_id = $init_response['_id'];
    $activity_id = $init_response['_activity_instance_id'];

    // 4. PASO 3: Enviar (Submit) el borrador para moverlo a la siguiente etapa
    $submit_url = KISSFLOW_API_HOST . "/process/2/AcNcc9rydX9F/{$process_name}/{$item_id}/{$activity_id}/submit";
    $submit_response = call_kissflow_api($submit_url, 'POST', []); 

    if ($submit_response === null) {
        throw new Exception('Falló el envío final del ticket en Kiss Flow. El borrador podría haber quedado guardado.');
    }

    // 5. Respuesta de éxito
    $response = [
        'ok' => true, 
        'mensaje' => 'Proceso de Selección de Acabados registrado y enviado en Kiss Flow.',
        'kissflow_item_id' => $item_id
    ];

} catch (Exception $e) {
    http_response_code(400);
    $response['mensaje'] = $e->getMessage();
    error_log('Error en sda_handler.php: ' . $e->getMessage());
}

// Devolver la respuesta final
echo json_encode($response);
