<?php
/*  POST /api/ctg_update_estado.php
 *  Body (JSON):
 *      ctg_id     int     ID del CTG a actualizar
 *      estado_id  int     Nuevo ID del estado
 *  Requires token in header Authorization: Bearer <token>
 *
 *  Respuesta:
 *      { ok: true, mensaje: "Estado actualizado correctamente" }
 *      { ok: false, mensaje: "..." }
 */
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// --- Lógica de Autenticación (Verificar token y si es responsable) --- //
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = null;

if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
}

$authenticated_user = null;
$is_responsable = false;

if ($token) {
    $db = DB::getDB();
    // Buscar en tabla 'responsable' (solo responsables pueden actualizar el estado)
    $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
    $stmt_resp = $db->prepare($sql_resp);
    $stmt_resp->execute([':token' => $token]);
    $authenticated_user = $stmt_resp->fetch(PDO::FETCH_ASSOC);
    if ($authenticated_user) {
        $is_responsable = true;
    }
    // No necesitamos buscar en la tabla 'usuario' aquí, ya que solo los responsables pueden realizar esta acción.
}

// Si no se autenticó como responsable, devolver error 403
if (!$authenticated_user || !$is_responsable) {
    http_response_code(403); // Prohibido
    exit(json_encode(['ok' => false, 'mensaje' => 'No autorizado para realizar esta acción. Solo responsables.']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'mensaje' => 'Método no permitido. Use POST o PUT.']));
}

/* ---------- 1. Entrada ---------- */
$input = json_decode(file_get_contents('php://input'), true);
$ctgId = (int)($input['ctg_id'] ?? 0);
$estadoId = (int)($input['estado_id'] ?? 0);

// Validar entrada
if (!$ctgId || !$estadoId) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'mensaje' => 'ctg_id y estado_id son requeridos']));
}

try {
    // Reutilizar la conexión de la autenticación
    $db = DB::getDB();

    // Opcional: Verificar si el CTG con ese ID existe y está asignado a este responsable (medida de seguridad adicional)
    $sql_check_ctg = 'SELECT id FROM ctg WHERE id = :ctg_id AND responsable_id = :responsable_id LIMIT 1';
    $stmt_check_ctg = $db->prepare($sql_check_ctg);
    $stmt_check_ctg->execute([
        ':ctg_id' => $ctgId,
        ':responsable_id' => $authenticated_user['id']
    ]);

    if (!$stmt_check_ctg->fetch()) {
        http_response_code(403); // Prohibido si el CTG no existe o no está asignado a este responsable
        exit(json_encode(['ok' => false, 'mensaje' => 'CTG no encontrado o no asignado a este responsable.']));
    }


    /* ---------- 2. Actualizar estado del CTG ---------- */
    $sql_update = 'UPDATE ctg SET estado_id = :estado_id, fecha_actualizacion = NOW() WHERE id = :ctg_id';
    $stmt_update = $db->prepare($sql_update);
    $stmt_update->execute([
        ':estado_id' => $estadoId,
        ':ctg_id' => $ctgId
    ]);

    // Verificar si se afectó alguna fila (si el CTG existía y se pudo actualizar)
    if ($stmt_update->rowCount() > 0) {
         // Opcional: Si el nuevo estado es "Resuelto" o "Cerrado", podrías añadir lógica adicional aquí (ej: enviar una última notificación al cliente).

        echo json_encode(['ok' => true, 'mensaje' => 'Estado actualizado correctamente']);
    } else {
        http_response_code(500); 
        echo json_encode(['ok' => false, 'mensaje' => 'No se pudo actualizar el estado del CTG.']);
    }


} catch (Throwable $e) {
    error_log('ctg_update_estado error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor.']);
}
?>