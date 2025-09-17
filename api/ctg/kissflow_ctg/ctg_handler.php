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
    // PASO 1: BUSCAR AL CLIENTE EN EL DATASET
    // -------------------------------------------------------------------
    $encoded_cedula = urlencode($cedula);
    $search_url = KISSFLOW_API_HOST . "/dataset/2/AcNcc9rydX9F/DS_Documentos_Cliente/list?q={$encoded_cedula}&search_field=Identificacion";
    $cliente_response = call_kissflow_api($search_url);
    $cliente_data = $cliente_response['Data'] ?? null;

    if (empty($cliente_data) || !isset($cliente_data[0]['_id'])) {
        throw new Exception("Cliente con cédula {$cedula} no fue encontrado en Kiss Flow. No se puede continuar.");
    }
    $kissflow_cliente_id = $cliente_data[0]['_id'];

    // -------------------------------------------------------------------
    // PASO 2: INICIAR PROCESO Y CREAR BORRADOR CON DATOS
    // -------------------------------------------------------------------
    // Unimos el ID del cliente con el resto de los datos del formulario en un solo payload inicial.
    $initial_payload = [
        'Cliente' => ['_id' => $kissflow_cliente_id],
        'Ubicacion' => ['_id' => $kissflow_cliente_id],
        'Email_1' => $input_data['email'] ?? 'N/A',
        'Phone' => $input_data['telefono'] ?? 'N/A',
        'Descripcion_del_Dano' => $input_data['descripcion_dano'] ?? 'Sin descripción.',
        'Contingencia' => $input_data['contingencia_nombre'] ?? 'OTROS',
        'Requiere_agendamiento_de_inspeccion' => true // <-- Condición para pausar el proceso
    ];

    $init_url = KISSFLOW_API_HOST . '/process/2/AcNcc9rydX9F/Warranty_Claim';
    $init_response = call_kissflow_api($init_url, 'POST', $initial_payload);

    if (empty($init_response) || !isset($init_response['_id']) || !isset($init_response['_activity_instance_id'])) {
        throw new Exception('No se pudo iniciar el proceso en Kiss Flow con los datos iniciales. Verifique los logs de la API.');
    }
    $item_id = $init_response['_id'];
    $activity_id = $init_response['_activity_instance_id'];

    // -- PASO INTERMEDIO DE ACTUALIZACIÓN ELIMINADO --

    // -------------------------------------------------------------------
    // PASO 3: ENVIAR EL BORRADOR (SUBMIT)
    // -------------------------------------------------------------------
    $submit_url = KISSFLOW_API_HOST . "/process/2/AcNcc9rydX9F/Warranty_Claim/{$item_id}/{$activity_id}/submit";
    $submit_response = call_kissflow_api($submit_url, 'POST', []); // Payload vacío

    if ($submit_response === null) {
        throw new Exception('Falló el envío final del ticket en Kiss Flow.');
    }

    $response = [
        'ok' => true, 
        'mensaje' => 'El CTG ha sido registrado y enviado en Kiss Flow exitosamente.',
        'kissflow_item_id' => $item_id
    ];

} catch (Exception $e) {
    http_response_code(400);
    $response['mensaje'] = $e->getMessage();
    error_log('Error en ctg_handler.php: ' . $e->getMessage());
}

// Devolver la respuesta final
echo json_encode($response);
