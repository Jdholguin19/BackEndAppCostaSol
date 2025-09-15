<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers/audit_helper.php';

header('Content-Type: application/json; charset=utf-8');

$db = DB::getDB();
$method = $_SERVER['REQUEST_METHOD'];

$authenticated_user_id = null;
$authenticated_user_type = null;

// --- Autenticación y Autorización --- //
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? '';

if (preg_match('/Bearer\s(.+)/', $auth_header, $matches)) {
    $token = $matches[1];

    // Buscar en tabla de responsables
    $stmt_responsable = $db->prepare("SELECT id FROM responsable WHERE token = :token");
    $stmt_responsable->execute([':token' => $token]);
    $responsable = $stmt_responsable->fetch(PDO::FETCH_ASSOC);

    if ($responsable) {
        $authenticated_user_id = $responsable['id'];
        $authenticated_user_type = 'responsable';
    } else {
        // Buscar en tabla de usuarios (si un usuario normal pudiera usar esto, aunque user_crud es admin)
        $stmt_usuario = $db->prepare("SELECT id, rol_id FROM usuario WHERE token = :token");
        $stmt_usuario->execute([':token' => $token]);
        $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $authenticated_user_id = $usuario['id'];
            $authenticated_user_type = 'usuario';
            // Aquí podrías añadir una validación de rol_id si solo ciertos roles de usuario pueden usar user_crud
            // Por ejemplo, si rol_id 3 es admin: if ($usuario['rol_id'] !== 3) { ... return 403 ... }
        }
    }
}

// Si no hay usuario autenticado o no es un responsable (asumiendo que user_crud es solo para responsables/admins)
// Ajusta esta lógica de autorización según tus roles específicos para user_crud
if (!$authenticated_user_id || $authenticated_user_type !== 'responsable') {
    http_response_code(403); // Forbidden
    exit(json_encode(['ok'=>false,'mensaje'=>'Acceso denegado. Se requiere autenticación de responsable.']));
}
// --- Fin Autenticación y Autorización --- //

try {
    if ($method === 'POST') {               // alta
        $d = json_decode(file_get_contents('php://input'), true);
        $sql = 'INSERT INTO usuario (rol_id, nombres, apellidos, correo, contrasena_hash)
                VALUES (:rol, :nom, :ape, :cor, :hash)';
        $db->prepare($sql)->execute([
          ':rol'  => $d['rol_id'],
          ':nom'  => $d['nombres'],
          ':ape'  => $d['apellidos'],
          ':cor'  => $d['correo'],
          ':hash' => password_hash($d['contrasena'], PASSWORD_DEFAULT)
        ]);
        $new_user_id = $db->lastInsertId();
        log_audit_action($db, 'CREATE_USER', $authenticated_user_id, $authenticated_user_type, 'usuario', $new_user_id, ['nombres' => $d['nombres'], 'correo' => $d['correo']]);
        exit(json_encode(['ok'=>true]));

    } elseif ($method === 'PUT') {          // edición
        $d = json_decode(file_get_contents('php://input'), true);
        $user_id_to_update = $d['id'] ?? null;

        if (!$user_id_to_update) {
            http_response_code(400);
            exit(json_encode(['ok'=>false,'mensaje'=>'ID de usuario requerido para actualizar.']));
        }

        // Obtener datos antiguos para auditoría
        $stmt_old_data = $db->prepare("SELECT rol_id, nombres, apellidos, correo FROM usuario WHERE id = :id");
        $stmt_old_data->execute([':id' => $user_id_to_update]);
        $old_user_data = $stmt_old_data->fetch(PDO::FETCH_ASSOC);

        $set = 'rol_id=:rol, nombres=:nom, apellidos=:ape, correo=:cor';
        $params = [
          ':rol' => $d['rol_id'], ':nom'=>$d['nombres'],
          ':ape'=>$d['apellidos'], ':cor'=>$d['correo'], ':id'=>$d['id']
        ];
        if (!empty($d['contrasena'])) {     // cambiar contraseña opcional
            $set .= ', contrasena_hash=:hash';
            $params[':hash'] = password_hash($d['contrasena'], PASSWORD_DEFAULT);
        }
        $sql = "UPDATE usuario SET $set WHERE id=:id";
        $db->prepare($sql)->execute($params);

        // Obtener nuevos datos para auditoría (o usar $d si es completo)
        $new_user_data = $d; // Asumimos que $d contiene los datos actualizados
        log_audit_action($db, 'UPDATE_USER', $authenticated_user_id, $authenticated_user_type, 'usuario', $user_id_to_update, ['old_data' => $old_user_data, 'new_data' => $new_user_data]);
        exit(json_encode(['ok'=>true]));

    } elseif ($method === 'DELETE') {       // borrado
        $id = $_GET['id'] ?? 0;

        if (!$id) {
            http_response_code(400);
            exit(json_encode(['ok'=>false,'mensaje'=>'ID de usuario requerido para eliminar.']));
        }

        // Obtener datos del usuario a eliminar para auditoría
        $stmt_deleted_data = $db->prepare("SELECT rol_id, nombres, apellidos, correo FROM usuario WHERE id = :id");
        $stmt_deleted_data->execute([':id' => $id]);
        $deleted_user_data = $stmt_deleted_data->fetch(PDO::FETCH_ASSOC);

        $db->prepare('DELETE FROM usuario WHERE id = ?')->execute([$id]);
        log_audit_action($db, 'DELETE_USER', $authenticated_user_id, $authenticated_user_type, 'usuario', $id, ['deleted_user_data' => $deleted_user_data]);
        exit(json_encode(['ok'=>true]));
    }

    http_response_code(405);
    echo json_encode(['ok'=>false,'mensaje'=>'Método no permitido']);

} catch (Throwable $e) {
    error_log('user_crud: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'mensaje'=>'Error interno']);
}
