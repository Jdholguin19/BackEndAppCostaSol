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
    // PASO 1: BUSCAR AL CLIENTE EN EL DATASET (REUTILIZADO DE CTG)
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
    // PASO 2: INICIAR PROCESO Y CREAR BORRADOR (DRAFT) PARA SELECCIÓN DE ACABADOS
    // -------------------------------------------------------------------
    $process_name = 'Copia_de_Eleccio_n_de_Acabados_y_Adicion';
    $init_url = KISSFLOW_API_HOST . "/process/2/AcNcc9rydX9F/{$process_name}";
    
    // Payload inicial vacío, los datos se enviarán en el paso de actualización
    $init_response = call_kissflow_api($init_url, 'POST', []); 

    if (empty($init_response) || !isset($init_response['_id']) || !isset($init_response['_activity_instance_id'])) {
        throw new Exception('No se pudo iniciar el proceso en Kiss Flow o la respuesta no contiene los IDs necesarios.');
    }
    $item_id = $init_response['_id'];
    $activity_id = $init_response['_activity_instance_id'];

    // -------------------------------------------------------------------
    // PASO 1: BUSCAR AL CLIENTE EN EL DATASET (REUTILIZADO DE CTG)
    // -------------------------------------------------------------------
    $encoded_cedula = urlencode($cedula);
    $search_url = KISSFLOW_API_HOST . "/dataset/2/AcNcc9rydX9F/DS_Documentos_Cliente/list?q={$encoded_cedula}&search_field=Identificacion";
    $cliente_response = call_kissflow_api($search_url);
    $cliente_data = $cliente_response['Data'] ?? null;

    if (empty($cliente_data) || !isset($cliente_data[0]['_id'])) {
        throw new Exception("Cliente con cédula {$cedula} no fue encontrado en Kiss Flow. No se puede continuar.");
    }
    $kissflow_cliente_id = $cliente_data[0]['_id'];

    if (empty($cliente_data) || !isset($cliente_data[0]['_id'])) {
        throw new Exception("Cliente con cédula {$cedula} no fue encontrado en Kiss Flow. No se puede continuar.");
    }
    $kissflow_cliente_id = $cliente_data[0]['_id'];
    $convenio_from_kissflow = $cliente_data[0]['Convenio'] ?? 'N/A'; // Extraer Convenio del cliente de Kiss Flow

    // -------------------------------------------------------------------
    // PASO 2: INICIAR PROCESO Y CREAR BORRADOR CON DATOS INICIALES
    // -------------------------------------------------------------------
    $process_name = 'Copia_de_Eleccio_n_de_Acabados_y_Adicion';
    $init_url = KISSFLOW_API_HOST . "/process/2/AcNcc9rydX9F/{$process_name}";
    
    // Payload inicial con datos del cliente y propiedad para que se rellenen al crear el borrador
    $initial_payload = [
        'Cliente' => ['_id' => $kissflow_cliente_id],
        'Ubicacion' => ['_id' => $kissflow_cliente_id],
        'Identificacion' => $input_data['cedula'] ?? 'N/A',
        'Nombre_del_Cliente' => $input_data['nombre_cliente'] ?? 'N/A',
        'Mz_Solar' => $input_data['propiedad_manzana_solar'] ?? 'N/A',
        'Etapa' => $input_data['propiedad_etapa'] ?? 'N/A',
        'Modelo_1' => $input_data['propiedad_modelo'] ?? 'N/A',
        'Convenio' => $convenio_from_kissflow, // Usamos el Convenio obtenido de Kiss Flow
        'Fecha' => date('Y-m-d'), // Fecha de hoy
        // TODO: Añadir campos específicos de selección de acabados aquí si se pueden enviar en la creación inicial
        // Por ejemplo: 'Kit_Seleccionado' => $input_data['kit_seleccionado']
    ];

    $init_response = call_kissflow_api($init_url, 'POST', $initial_payload); 

    if (empty($init_response) || !isset($init_response['_id']) || !isset($init_response['_activity_instance_id'])) {
        throw new Exception('No se pudo iniciar el proceso en Kiss Flow o la respuesta no contiene los IDs necesarios.');
    }
    $item_id = $init_response['_id'];
    $activity_id = $init_response['_activity_instance_id'];

    // --- PASO DE ACTUALIZACIÓN DE DATOS ESPECÍFICOS ELIMINADO (se envían en la creación inicial) ---

    // -------------------------------------------------------------------
    // PASO 3: ENVIAR EL BORRADOR (SUBMIT)
    // -------------------------------------------------------------------
    $submit_url = KISSFLOW_API_HOST . "/process/2/AcNcc9rydX9F/{$process_name}/{$item_id}/{$activity_id}/submit";
    $submit_response = call_kissflow_api($submit_url, 'POST', []); 

    if ($submit_response === null) {
        throw new Exception('Falló el envío final del ticket en Kiss Flow.');
    }

    $response = [
        'ok' => true, 
        'mensaje' => 'Proceso de Selección de Acabados registrado en Kiss Flow.',
        'kissflow_item_id' => $item_id
    ];

    // -------------------------------------------------------------------
    // PASO 4: ENVIAR EL BORRADOR (SUBMIT) - Opcional, según el flujo deseado
    // -------------------------------------------------------------------
    $submit_url = $update_url . '/submit';
    $submit_response = call_kissflow_api($submit_url, 'POST', []); 

    if ($submit_response === null) {
        throw new Exception('Falló el envío final del ticket en Kiss Flow.');
    }

    $response = [
        'ok' => true, 
        'mensaje' => 'Proceso de Selección de Acabados registrado en Kiss Flow.',
        'kissflow_item_id' => $item_id
    ];

} catch (Exception $e) {
    http_response_code(400);
    $response['mensaje'] = $e->getMessage();
    error_log('Error en sda_handler.php: ' . $e->getMessage());
}

// Devolver la respuesta final
echo json_encode($response);
