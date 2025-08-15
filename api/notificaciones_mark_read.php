<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// --- Lógica de Autenticación por Token ---
$user = null;
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    try {
        $db = DB::getDB();
        
        // 1. Buscar en la tabla de usuarios
        $stmt = $db->prepare('SELECT id, rol_id FROM usuario WHERE token = :token');
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Si no se encuentra, buscar en la tabla de responsables
        if (!$user) {
            $stmt_resp = $db->prepare('SELECT id FROM responsable WHERE token = :token');
            $stmt_resp->execute([':token' => $token]);
            $responsable = $stmt_resp->fetch(PDO::FETCH_ASSOC);
            if ($responsable) {
                // Si es un responsable, construir el array de usuario manualmente
                $user = [
                    'id' => $responsable['id'],
                    'rol_id' => 2 // El rol de responsable es 2
                ];
            }
        }
    } catch (Exception $e) {
        error_log('Token validation error: ' . $e->getMessage());
    }
}

if (!$user) {
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

$id_usuario_actual = $user['id'];
$id_rol_actual = $user['rol_id'];
$type = $data['type'];
$id = (int)$data['id'];

$sql = '';
$params = [];

$table_respuesta = ($type == 'ctg') ? 'respuesta_ctg' : 'respuesta_pqr';
$table_main = $type;
$id_column_main = ($type == 'ctg') ? 'ctg_id' : 'pqr_id';
$id_column_ticket_owner = 'id_usuario';

try {
    $db = DB::getDB();

    if ($id_rol_actual == 2) { // Es un responsable: marcar respuestas de usuario como leídas en sus tickets asignados
        $sql = "UPDATE {$table_respuesta} r JOIN {$table_main} m ON r.{$id_column_main} = m.id 
                SET r.leido = 1 
                WHERE r.{$id_column_main} = ? AND m.responsable_id = ? AND r.usuario_id IS NOT NULL AND r.leido = 0";
        $params = [$id, $id_usuario_actual];
    } else { // Es un usuario normal: marcar respuestas de responsables como leídas en su propio ticket
        $sql = "UPDATE {$table_respuesta} r JOIN {$table_main} m ON r.{$id_column_main} = m.id 
                SET r.leido = 1 
                WHERE r.{$id_column_main} = ? AND m.{$id_column_ticket_owner} = ? AND r.responsable_id IS NOT NULL AND r.leido = 0";
        $params = [$id, $id_usuario_actual];
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