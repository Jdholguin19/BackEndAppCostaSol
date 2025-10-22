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
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit();
}
// --- Fin Autenticación ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['type']) || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parámetros incompletos']);
    exit();
}

$type = $data['type'];
$id = (int)$data['id'];

$sql = '';
$params = [];

try {
    $db = DB::getDB();

    // Si el tipo es 'notificacion', marcar notificación general como leída
    if ($type == 'notificacion') {
        $sql = "UPDATE notificacion 
                SET leido = 1, fecha_lectura = NOW()
                WHERE id = ? AND usuario_id = ? AND leido = 0";
        $params = [$id, $auth_id];
    } else {
        // Tipos existentes: ctg, pqr
        $table_respuesta = ($type == 'ctg') ? 'respuesta_ctg' : 'respuesta_pqr';
        $table_main = $type;
        $id_column_main = ($type == 'ctg') ? 'ctg_id' : 'pqr_id';

        if ($is_responsable) { // Es un responsable: marcar respuestas de usuario como leídas en sus tickets asignados
            $sql = "UPDATE {$table_respuesta} r JOIN {$table_main} m ON r.{$id_column_main} = m.id 
                    SET r.leido = 1 
                    WHERE r.{$id_column_main} = ? AND m.responsable_id = ? AND r.usuario_id IS NOT NULL AND r.leido = 0";
            $params = [$id, $auth_id];
        } else { // Es un usuario normal: marcar respuestas de responsables como leídas en su propio ticket
            $sql = "UPDATE {$table_respuesta} r JOIN {$table_main} m ON r.{$id_column_main} = m.id 
                    SET r.leido = 1 
                    WHERE r.{$id_column_main} = ? AND m.id_usuario = ? AND r.responsable_id IS NOT NULL AND r.leido = 0";
            $params = [$id, $auth_id];
        }
    }

    $stmt = $db->prepare($sql);
    
    if ($stmt->execute($params)) {
        echo json_encode(['status' => 'success', 'message' => 'Notificaciones marcadas como leídas']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado de las notificaciones.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error en notificaciones_mark_read: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor.']);
}
?>