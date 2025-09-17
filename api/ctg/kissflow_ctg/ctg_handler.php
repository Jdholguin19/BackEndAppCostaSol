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
    // Leer los datos de la petición POST
    $input_data = json_decode(file_get_contents('php://input'), true);

    if (!$input_data) {
        throw new Exception('No se recibieron datos de entrada o el formato es incorrecto.');
    }

    // Extraer la cédula (ajustar el nombre del campo si es necesario)
    $cedula = $input_data['cedula'] ?? null;
    if (!$cedula) {
        throw new Exception('El número de cédula es obligatorio para continuar.');
    }

    // -------------------------------------------------------------------
    // PASO 1: BÚSQUEDA DEL CLIENTE EN KISS FLOW
    // -------------------------------------------------------------------
    $encoded_cedula = urlencode($cedula);
    // URL confirmada gracias a la captura de la petición de red
    $search_url = KISSFLOW_API_HOST . "/dataset/2/AcNcc9rydX9F/DS_Documentos_Cliente/list?q={$encoded_cedula}&search_field=Identificacion";

    $cliente_response = call_kissflow_api($search_url);

    // La respuesta de la API anida los resultados bajo la clave 'Data'
    $cliente_data = $cliente_response['Data'] ?? null;

    if (empty($cliente_data) || !isset($cliente_data[0]['_id'])) {
        throw new Exception("Cliente con cédula {$cedula} no fue encontrado en Kiss Flow. No se puede continuar.");
    }
    
    // Asumimos que el primer resultado es el correcto
    $kissflow_cliente_id = $cliente_data[0]['_id'];


    // -------------------------------------------------------------------
    // PASO 2: CREACIÓN DEL "WARRANTY CLAIM"
    // -------------------------------------------------------------------

    // Mapear los campos recibidos al payload que espera Kiss Flow
    $warranty_payload = [
        'Requestor_Name' => $input_data['nombre_cliente'] ?? 'N/A',
        'Email_1' => $input_data['email'] ?? 'N/A',
        'Phone' => $input_data['telefono'] ?? 'N/A',
        'Request_Date' => date('Y-m-d'),
        'Descripcion_del_Dano' => $input_data['descripcion_dano'] ?? 'Sin descripción.',
        'Contingencia' => $input_data['contingencia_nombre'] ?? 'OTROS', // <-- Usamos el nombre de la contingencia
        'Cliente' => [
            '_id' => $kissflow_cliente_id
        ],
        'Ubicacion' => [
            '_id' => $kissflow_cliente_id
        ]
    ];

    $create_url = KISSFLOW_API_HOST . '/api/v2/processes/' . rawurlencode('Tickets De Atención de Contingencia') . '/items';

    $creation_response = call_kissflow_api($create_url, 'POST', $warranty_payload);

    if (!$creation_response) {
        throw new Exception('La creación del Warranty Claim en Kiss Flow falló. La API no respondió o devolvió un error.');
    }

    $response = [
        'ok' => true, 
        'mensaje' => 'El CTG ha sido registrado en Kiss Flow exitosamente.',
        'kissflow_cliente_id' => $kissflow_cliente_id,
        'respuesta_kissflow' => $creation_response
    ];

} catch (Exception $e) {
    http_response_code(400);
    $response['mensaje'] = $e->getMessage();
    error_log('Error en ctg_handler.php: ' . $e->getMessage());
}

// Devolver la respuesta final
echo json_encode($response);
