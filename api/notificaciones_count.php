<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// --- Lógica de Autenticación por Token ---
$auth_id = null;
$is_responsable = false;

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    try {
        $db = DB::getDB();
        
        // 1. Buscar en la tabla de usuarios
        $stmt = $db->prepare('SELECT id FROM usuario WHERE token = :token');
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $auth_id = $user['id'];
            $is_responsable = false;
        } else {
            // 2. Si no se encuentra, buscar en la tabla de responsables
            $stmt_resp = $db->prepare('SELECT id FROM responsable WHERE token = :token');
            $stmt_resp->execute([':token' => $token]);
            $responsable = $stmt_resp->fetch(PDO::FETCH_ASSOC);
            if ($responsable) {
                $auth_id = $responsable['id'];
                $is_responsable = true;
            }
        }
    } catch (Exception $e) {
        error_log('Token validation error: ' . $e->getMessage());
    }
}

if ($auth_id === null) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado', 'count' => 0]);
    exit();
}
// --- Fin Autenticación ---

$total_notificaciones = 0;
$sql = '';
$params = [];

try {
    $db = DB::getDB();

    if ($is_responsable) { // Es un responsable: contar respuestas de usuarios no leídas en sus tickets asignados
        $sql = "SELECT SUM(unread_count) as total_unread FROM (
                    SELECT COUNT(rc.id) as unread_count 
                    FROM respuesta_ctg rc
                    JOIN ctg c ON rc.ctg_id = c.id
                    WHERE rc.usuario_id IS NOT NULL AND rc.leido = 0 AND c.responsable_id = ?
                    UNION ALL
                    SELECT COUNT(rp.id) as unread_count
                    FROM respuesta_pqr rp
                    JOIN pqr p ON rp.pqr_id = p.id
                    WHERE rp.usuario_id IS NOT NULL AND rp.leido = 0 AND p.responsable_id = ?
                ) as combined_counts";
        $params = [$auth_id, $auth_id];
    } else { // Es un usuario normal: contar respuestas de responsables no leídas + notificaciones generales
        $sql = "SELECT SUM(unread_count) as total_unread FROM (
                    SELECT COUNT(rc.id) as unread_count 
                    FROM respuesta_ctg rc
                    JOIN ctg c ON rc.ctg_id = c.id
                    WHERE rc.responsable_id IS NOT NULL AND rc.leido = 0 AND c.id_usuario = ?
                    UNION ALL
                    SELECT COUNT(rp.id) as unread_count
                    FROM respuesta_pqr rp
                    JOIN pqr p ON rp.pqr_id = p.id
                    WHERE rp.responsable_id IS NOT NULL AND rp.leido = 0 AND p.id_usuario = ?
                    UNION ALL
                    SELECT COUNT(n.id) as unread_count
                    FROM notificacion n
                    WHERE n.usuario_id = ? AND n.leido = 0 AND n.estado = 1
                ) as combined_counts";
        $params = [$auth_id, $auth_id, $auth_id];
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fila && $fila['total_unread'] !== null) {
        $total_notificaciones = (int)$fila['total_unread'];
    }

    echo json_encode(['status' => 'success', 'count' => $total_notificaciones]);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error en notificaciones_count: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor.', 'count' => 0]);
}
?>