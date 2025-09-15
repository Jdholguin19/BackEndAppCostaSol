<?php
/**
 *  POST /api/ctg/ctg_update_observaciones.php
 *  Requiere token en header Authorization: Bearer <token>
 *  Solo el responsable asignado puede actualizar las observaciones
 *  Parámetros POST: ctg_id, observaciones
 *  → { ok:true, mensaje: "Observaciones actualizadas correctamente" }
 */
require_once __DIR__.'/../../config/db.php';
require_once __DIR__ . '/../helpers/audit_helper.php'; // Incluir el helper de auditoría
header('Content-Type: application/json; charset=utf-8');

// --- Lógica de Autenticación --- //
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
list($tokenType, $token) = explode(' ', $authHeader, 2);

$authenticated_user = null;
$is_responsable = false;

if ($tokenType === 'Bearer' && $token) {
    $db = DB::getDB(); // Usar la conexión para autenticar
    // Buscar en tabla 'usuario'
    $sql_user = 'SELECT id, nombres, rol_id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($authenticated_user) {
        $is_responsable = false;
    } else {
        // Buscar en tabla 'responsable'
        $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
        $stmt_resp = $db->prepare($sql_resp);
        $stmt_resp->execute([':token' => $token]);
        $authenticated_user = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($authenticated_user) {
            $is_responsable = true;
        }
    }
}

if (!$authenticated_user) {
    http_response_code(401); // No autorizado
    exit(json_encode(['ok' => false, 'mensaje' => 'No autenticado o token inválido']));
}

// --- Fin Lógica de Autenticación --- //

// Verificar que es método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit(json_encode(['ok' => false, 'mensaje' => 'Método no permitido']));
}

$ctgId = (int)($_POST['ctg_id'] ?? 0);
$observaciones = trim($_POST['observaciones'] ?? '');

if(!$ctgId){
    http_response_code(400);
    exit(json_encode(['ok'=>false,'mensaje'=>'ctg_id requerido']));
}

// Validar longitud de observaciones (máximo 700 caracteres según la BD)
if (strlen($observaciones) > 700) {
    http_response_code(400);
    exit(json_encode(['ok'=>false,'mensaje'=>'Las observaciones no pueden exceder 700 caracteres']));
}

try{
    $db = DB::getDB(); // Reutilizar la conexión de la autenticación

    // Obtener las observaciones actuales del CTG para el log de auditoría
    $sql_old_obs = 'SELECT observaciones FROM ctg WHERE id = :ctg_id LIMIT 1';
    $stmt_old_obs = $db->prepare($sql_old_obs);
    $stmt_old_obs->execute([':ctg_id' => $ctgId]);
    $old_observaciones = $stmt_old_obs->fetchColumn();
    
    // Verificar que el usuario es responsable y está asignado a este CTG
    if (!$is_responsable) {
        http_response_code(403); // Forbidden
        exit(json_encode(['ok' => false, 'mensaje' => 'Solo los responsables pueden actualizar las observaciones']));
    }
    
    // Verificar que el responsable está asignado a este CTG
    $sql_check = 'SELECT id, responsable_id FROM ctg WHERE id = :ctg_id AND responsable_id = :responsable_id LIMIT 1';
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->execute([
        ':ctg_id' => $ctgId,
        ':responsable_id' => $authenticated_user['id']
    ]);
    
    $ctg = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$ctg) {
        http_response_code(403); // Forbidden
        exit(json_encode(['ok' => false, 'mensaje' => 'No tienes permisos para actualizar las observaciones de este CTG']));
    }
    
    // Actualizar las observaciones
    $sql_update = 'UPDATE ctg SET observaciones = :observaciones, fecha_actualizacion = NOW() WHERE id = :ctg_id AND responsable_id = :responsable_id';
    $stmt_update = $db->prepare($sql_update);
    $result = $stmt_update->execute([
        ':observaciones' => $observaciones,
        ':ctg_id' => $ctgId,
        ':responsable_id' => $authenticated_user['id']
    ]);
    
    if ($result) {
        log_audit_action($db, 'UPDATE_CTG_OBSERVATION', $authenticated_user['id'], 'responsable', 'ctg', $ctgId, ['old_observaciones' => $old_observaciones, 'new_observaciones' => $observaciones]); // Log de auditoría
        echo json_encode([
            'ok' => true, 
            'mensaje' => 'Observaciones actualizadas correctamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error al actualizar las observaciones']);
    }
    
}catch(Throwable $e){
    error_log('ctg_update_observaciones: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'mensaje'=>'Error interno']);
}
