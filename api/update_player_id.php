<?php
/**
 *  api/update_player_id.php
 *  POST  { "onesignal_player_id": "..." }
 *  Requires token in header Authorization: Bearer <token>
 *  OK    { "ok": true, "mensaje": "Player ID actualizado" }
 *  Error { "ok": false, "mensaje": "..." }
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers/audit_helper.php'; // Incluir el helper de auditoría
header('Content-Type: application/json; charset=utf-8');

// --- Lógica de Autenticación (Verificar token) --- //
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = null;

if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
}

$authenticated_user_id = null;
$is_responsable = false;

if ($token) {
    $db = DB::getDB();
    // Buscar en tabla 'usuario' (clientes)
    $sql_user = 'SELECT id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $authenticated_user_id = $user['id'];
        $is_responsable = false;
        $authenticated_user_type = 'usuario'; // Set user type
    } else {
        // Si no es un usuario, verificar si es un responsable
        $sql_resp = 'SELECT id FROM responsable WHERE token = :token LIMIT 1';
        $stmt_resp = $db->prepare($sql_resp);
        $stmt_resp->execute([':token' => $token]);
        $resp = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($resp) {
            $authenticated_user_id = $resp['id'];
            $is_responsable = true;
            $authenticated_user_type = 'responsable'; // Set user type
        }
    }
}

// Si no se autenticó ningún usuario o responsable, devolver error 401
if (!$authenticated_user_id) {
    http_response_code(401);
    exit(json_encode(['ok' => false, 'mensaje' => 'No autenticado o token inválido']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok'=>false,'mensaje'=>'Método no permitido']));
}

/* ---------- 1. Entrada ---------- */
$input = json_decode(file_get_contents('php://input'), true);
$oneSignalPlayerId = $input['onesignal_player_id'] ?? null;

// Validar entrada - permitir null para desuscripción
if (!isset($input['onesignal_player_id'])) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'mensaje' => 'onesignal_player_id es requerido']));
}

// Usar el ID del usuario autenticado directamente
    // Usar el ID del usuario autenticado directamente
    $userId = $authenticated_user_id;

    // Determinar en qué tabla actualizar (usuario o responsable)
    $table_to_update = $is_responsable ? 'responsable' : 'usuario';

    // Obtener el Player ID actual para el log de auditoría
    $sql_get_old_player_id = "SELECT onesignal_player_id FROM $table_to_update WHERE id = :user_id LIMIT 1";
    $stmt_get_old_player_id = $db->prepare($sql_get_old_player_id);
    $stmt_get_old_player_id->execute([':user_id' => $userId]);
    $old_player_id = $stmt_get_old_player_id->fetchColumn();

    try {

    // Si el Player ID es null o cadena vacía, solo limpiar el campo (desuscripción)
    if ($oneSignalPlayerId === null || $oneSignalPlayerId === "") {
        $sql = "UPDATE $table_to_update SET onesignal_player_id = NULL, fecha_actualizacion_player_id = NOW() WHERE id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['ok' => true, 'mensaje' => 'Player ID eliminado exitosamente (desuscripción)']);
            log_audit_action($db, 'UPDATE_ONESIGNAL_PLAYER_ID', $authenticated_user_id, $authenticated_user_type, $table_to_update, $authenticated_user_id, ['old_player_id' => $old_player_id, 'new_player_id' => null, 'status_change' => 'Unsubscribed']);
        } else {
            echo json_encode(['ok' => true, 'mensaje' => 'Player ID ya estaba eliminado']);
            log_audit_action($db, 'UPDATE_ONESIGNAL_PLAYER_ID', $authenticated_user_id, $authenticated_user_type, $table_to_update, $authenticated_user_id, ['old_player_id' => $old_player_id, 'new_player_id' => null, 'status_change' => 'No change (already unsubscribed)']);
        }
        exit;
    }

    /* ---------- 2. Verificar si el Player ID ya existe para otro usuario ---------- */
    $sql_check = "SELECT id FROM $table_to_update WHERE onesignal_player_id = :player_id AND id != :user_id LIMIT 1";
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->execute([
        ':player_id' => $oneSignalPlayerId,
        ':user_id' => $userId
    ]);
    
    if ($stmt_check->fetch()) {
        // El Player ID ya existe para otro usuario, limpiar el anterior
        $sql_clear = "UPDATE $table_to_update SET onesignal_player_id = NULL WHERE onesignal_player_id = :player_id AND id != :user_id";
        $stmt_clear = $db->prepare($sql_clear);
        $stmt_clear->execute([
            ':player_id' => $oneSignalPlayerId,
            ':user_id' => $userId
        ]);
        error_log("Player ID duplicado limpiado para permitir nueva asignación");
    }

    /* ---------- 3. Actualizar onesignal_player_id ---------- */
    $sql = "UPDATE $table_to_update SET onesignal_player_id = :player_id, fecha_actualizacion_player_id = NOW() WHERE id = :user_id";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':player_id' => $oneSignalPlayerId,
        ':user_id' => $userId
    ]);

    // Verificar si se afectó alguna fila
    if ($stmt->rowCount() > 0) {
         error_log("Player ID actualizado exitosamente para usuario $userId: $oneSignalPlayerId");
         echo json_encode(['ok' => true, 'mensaje' => 'Player ID actualizado exitosamente']);
         log_audit_action($db, 'UPDATE_ONESIGNAL_PLAYER_ID', $authenticated_user_id, $authenticated_user_type, $table_to_update, $authenticated_user_id, ['old_player_id' => $old_player_id, 'new_player_id' => $oneSignalPlayerId, 'status_change' => 'Subscribed / Player ID updated']);
    } else {
        // Verificar si el usuario existe
        $sql_exists = "SELECT id FROM $table_to_update WHERE id = :user_id";
        $stmt_exists = $db->prepare($sql_exists);
        $stmt_exists->execute([':user_id' => $userId]);
        
        if ($stmt_exists->fetch()) {
            // El usuario existe, probablemente el Player ID ya era el mismo
            echo json_encode(['ok' => true, 'mensaje' => 'Player ID sin cambios (ya era el mismo)']);
            log_audit_action($db, 'UPDATE_ONESIGNAL_PLAYER_ID', $authenticated_user_id, $authenticated_user_type, $table_to_update, $authenticated_user_id, ['old_player_id' => $old_player_id, 'new_player_id' => $oneSignalPlayerId, 'status_change' => 'No change (already subscribed)']);
        } else {
            http_response_code(404);
            echo json_encode(['ok' => false, 'mensaje' => 'Usuario no encontrado']);
        }
    }

} catch (Throwable $e) {
    error_log('update_player_id error: '.$e->getMessage());
    error_log('update_player_id stack trace: '.$e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno al actualizar Player ID']);
}
?>