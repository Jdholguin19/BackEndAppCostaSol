<?php
declare(strict_types=1);

// Definimos el contexto para que los logs no se impriman en la respuesta JSON.
define('SYNC_CONTEXT', 'WEBHOOK');

/**
 * ENDPOINT DE WEBHOOK PARA SINCRONIZACIÓN EN TIEMPO REAL DESDE KISS FLOW
 *
 * Este script escucha los eventos de creación y actualización de registros en el
 * dataset 'DS_Documentos_Cliente' de Kiss Flow y actualiza la base de datos local al instante.
 */

// --- CONFIGURACIÓN Y SETUP INICIAL ---

// Responder siempre con código 200 OK a Kiss Flow, a menos que haya un error de autenticación.
// Esto es crucial para que Kiss Flow sepa que el evento fue recibido y no intente enviarlo de nuevo.
http_response_code(200);
header('Content-Type: application/json');

// --- INCLUSIÓN DE DEPENDENCIAS ---

// Se necesita la configuración de la BD y las credenciales de Kiss Flow.
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../kiss_flow/config.php'; 

// Carga la lógica de procesamiento de registros y la función de log.
// Es importante definir LOG_FILE aquí también para que log_message funcione.
define('LOG_FILE', __DIR__ . '/sync_log.txt');
require_once __DIR__ . '/sync_logic.php';

// --- LÓGICA DEL WEBHOOK ---

// !! IMPORTANTE !!
// Secreto compartido para verificar que las peticiones del Webhook vienen de Kiss Flow.
$KISSFLOW_WEBHOOK_SECRET = 'MiAppCostaSol_SyncWebApp_2025!';

// 1. Verificación de Seguridad (Bearer Token)
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$expected_header = 'Bearer ' . $KISSFLOW_WEBHOOK_SECRET;

if (!hash_equals($expected_header, $auth_header)) {
    http_response_code(401);
    log_message("ERROR DE WEBHOOK: Token de autorización inválido o ausente. Acceso no autorizado.");
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

// 2. Decodificar el Payload
$payload = file_get_contents('php://input');

// Logueamos el payload crudo para poder inspeccionar qué está enviando Kiss Flow exactamente.
log_message("WEBHOOK: Payload crudo recibido: '" . $payload . "'", false);

// Si el payload está vacío o solo contiene espacios en blanco, no es un registro válido.
// Respondemos inmediatamente a Kiss Flow y terminamos la ejecución.
if (trim($payload) === '') {
    log_message("WEBHOOK: Payload vacío o con espacios en blanco detectado. Ejecución terminada.", false);
    // Respondemos 200 OK para que Kiss Flow no siga reintentando.
    echo json_encode(['status' => 'success', 'message' => 'Empty payload received, ignored.']);
    exit;
}

$data = json_decode($payload, true);

// Se espera que el cuerpo de la petición sea directamente el objeto JSON del registro.
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    log_message("ERROR DE WEBHOOK: Payload JSON inválido.");
    echo json_encode(['status' => 'error', 'message' => 'Payload inválido']);
    exit;
}

// 3. Procesar el registro
log_message("Webhook recibido. Procesando registro...");

// El payload completo ahora es el registro.
$record = $data;
$conn = DB::getDB();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$result = process_kissflow_record($record, $conn);

if ($result['error']) {
    log_message("WEBHOOK: Error al procesar el registro ID: " . ($record['_id'] ?? 'N/A') . ". Error: " . $result['error']);
} else {
    log_message("WEBHOOK: Registro ID: " . ($record['_id'] ?? 'N/A') . " procesado exitosamente.");
}

// 4. Responder a Kiss Flow
// El código 200 ya se estableció al inicio. Simplemente confirmamos el éxito.
echo json_encode(['status' => 'success']);
