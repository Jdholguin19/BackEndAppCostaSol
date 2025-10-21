<?php
/**
 * POST /api/cita/cita_registrar_asistencia.php
 * 
 * Registra si el cliente asistió o no a la cita
 * 
 * Body (JSON):
 * {
 *   "cita_id": 123,
 *   "asistencia": "ASISTIO" | "NO_ASISTIO"
 * }
 * 
 * Requiere token en header Authorization: Bearer <token>
 * 
 * Respuesta:
 * { ok: true }
 * { ok: false, mensaje: "..." }
 */

require_once __DIR__.'/../../config/db.php';
require_once __DIR__ . '/../helpers/audit_helper.php';
header('Content-Type: application/json; charset=utf-8');

// --- Validación de Token ---
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
list($tokenType, $token) = explode(' ', $authHeader . ' ', 2);

$authenticated_user = null;
$is_responsable = false;

if ($tokenType === 'Bearer' && $token) {
    $db = DB::getDB();
    
    // Buscar en tabla 'responsable' primero
    $sql_resp = 'SELECT id FROM responsable WHERE token = :token LIMIT 1';
    $stmt_resp = $db->prepare($sql_resp);
    $stmt_resp->execute([':token' => $token]);
    $responsable = $stmt_resp->fetch(PDO::FETCH_ASSOC);
    
    if ($responsable) {
        $authenticated_user = $responsable;
        $is_responsable = true;
    } else {
        // Buscar en tabla 'usuario'
        $sql_user = 'SELECT id FROM usuario WHERE token = :token LIMIT 1';
        $stmt_user = $db->prepare($sql_user);
        $stmt_user->execute([':token' => $token]);
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $authenticated_user = $user;
            $is_responsable = false;
        }
    }
}

if (!$authenticated_user) {
    http_response_code(401);
    exit(json_encode(['ok' => false, 'mensaje' => 'Token inválido']));
}

// Solo responsables pueden registrar asistencia
if (!$is_responsable) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'mensaje' => 'Solo responsables pueden registrar asistencia']));
}

try {
    $db = DB::getDB();
    
    // Leer el JSON del body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $cita_id = (int)($data['cita_id'] ?? 0);
    $asistencia = trim($data['asistencia'] ?? '');
    
    if (!$cita_id || !in_array($asistencia, ['ASISTIO', 'NO_ASISTIO'])) {
        http_response_code(400);
        exit(json_encode(['ok' => false, 'mensaje' => 'Datos incompletos o inválidos']));
    }
    
    // Verificar que la cita existe y está asignada al responsable actual
    $sql_check = 'SELECT id, responsable_id FROM agendamiento_visitas WHERE id = :cita_id LIMIT 1';
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->execute([':cita_id' => $cita_id]);
    $cita = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$cita) {
        http_response_code(404);
        exit(json_encode(['ok' => false, 'mensaje' => 'Cita no encontrada']));
    }
    
    if ($cita['responsable_id'] != $authenticated_user['id']) {
        http_response_code(403);
        exit(json_encode(['ok' => false, 'mensaje' => 'No tienes permiso para esta cita']));
    }
    
    // Actualizar la asistencia
    $sql_update = 'UPDATE agendamiento_visitas SET asistencia = :asistencia WHERE id = :cita_id';
    $stmt_update = $db->prepare($sql_update);
    $stmt_update->execute([
        ':asistencia' => $asistencia,
        ':cita_id' => $cita_id
    ]);
    
    // Registrar en auditoría
    log_audit_action(
        $db, 
        'REGISTRAR_ASISTENCIA_CITA', 
        $authenticated_user['id'], 
        'responsable', 
        'agendamiento_visitas', 
        $cita_id, 
        ['asistencia' => $asistencia]
    );
    
    echo json_encode(['ok' => true, 'mensaje' => 'Asistencia registrada correctamente']);
    
} catch (Throwable $e) {
    error_log('cita_registrar_asistencia: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno: ' . $e->getMessage()]);
}
?>
