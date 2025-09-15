<?php
/**
 * api/logout.php
 * POST { "token": "..." }
 * OK   { "ok": true, "mensaje": "Sesión cerrada correctamente" }
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers/audit_helper.php'; // Incluir el helper de auditoría

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok'=>false,'mensaje'=>'Método no permitido']));
}

/* ---------- 1. Entrada ---------- */
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? null;

if (!$token) {
    http_response_code(422);
    exit(json_encode(['ok'=>false,'mensaje'=>'Token requerido']));
}

try {
    $db = DB::getDB();
    $user_id = null;
    $user_type = null;

    // Buscar el token en la tabla de usuarios
    $stmt_user = $db->prepare("SELECT id, rol_id FROM usuario WHERE token = :token");
    $stmt_user->execute([':token' => $token]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = $user['id'];
        $user_type = 'usuario';
        // Invalidar token
        $update_sql = 'UPDATE usuario SET token = NULL WHERE id = :id';
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->execute([':id' => $user_id]);
    } else {
        // Si no es un usuario, buscar en la tabla de responsables
        $stmt_responsable = $db->prepare("SELECT id FROM responsable WHERE token = :token");
        $stmt_responsable->execute([':token' => $token]);
        $responsable = $stmt_responsable->fetch(PDO::FETCH_ASSOC);

        if ($responsable) {
            $user_id = $responsable['id'];
            $user_type = 'responsable';
            // Invalidar token
            $update_sql = 'UPDATE responsable SET token = NULL WHERE id = :id';
            $update_stmt = $db->prepare($update_sql);
            $update_stmt->execute([':id' => $user_id]);
        }
    }

    if ($user_id && $user_type) {
        log_audit_action($db, 'LOGOUT', $user_id, $user_type); // Log de auditoría
        echo json_encode([
            'ok' => true,
            'mensaje' => 'Sesión cerrada correctamente'
        ]);
    } else {
        // Token no encontrado o inválido
        log_audit_action($db, 'LOGOUT_FAILURE', null, 'sistema', null, null, ['token_intentado' => $token]); // Log de auditoría
        http_response_code(401);
        exit(json_encode(['ok'=>false,'mensaje'=>'Token inválido o sesión ya cerrada']));
    }

} catch (Throwable $e) {
    error_log('Logout error: '.$e->getMessage());
    http_response_code(500);
    exit(json_encode(['ok'=>false,'mensaje'=>'Error interno']));
}
?>