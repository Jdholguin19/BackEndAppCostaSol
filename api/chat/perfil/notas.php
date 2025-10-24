<?php
require_once __DIR__ . '/../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = DB::getDB();

    // Auth via Bearer token (solo responsables pueden ver/editar notas)
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = null;
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
    }
    if (!$token) {
        echo json_encode(['ok' => false, 'error' => 'Token requerido']);
        exit;
    }

    $stmt_resp = $db->prepare('SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1');
    $stmt_resp->execute([':token' => $token]);
    $resp = $stmt_resp->fetch(PDO::FETCH_ASSOC);
    if (!$resp) {
        echo json_encode(['ok' => false, 'error' => 'Solo responsables pueden acceder a las notas']);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // Obtener notas del cliente
        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        if ($user_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'user_id requerido']);
            exit;
        }

        $stmt = $db->prepare('SELECT notas FROM usuario WHERE id = :user_id LIMIT 1');
        $stmt->execute([':user_id' => $user_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado']);
            exit;
        }

        echo json_encode([
            'ok' => true,
            'notas' => $usuario['notas']
        ]);

    } elseif ($method === 'POST') {
        // Guardar notas del cliente
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['ok' => false, 'error' => 'Datos JSON requeridos']);
            exit;
        }

        $user_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;
        $notas = isset($input['notas']) ? trim($input['notas']) : '';

        if ($user_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'user_id requerido']);
            exit;
        }

        // Verificar que el usuario existe
        $stmt_check = $db->prepare('SELECT id FROM usuario WHERE id = :user_id LIMIT 1');
        $stmt_check->execute([':user_id' => $user_id]);
        if (!$stmt_check->fetch()) {
            echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado']);
            exit;
        }

        // Actualizar notas
        $stmt_update = $db->prepare('UPDATE usuario SET notas = :notas WHERE id = :user_id');
        $stmt_update->execute([
            ':notas' => $notas === '' ? null : $notas,
            ':user_id' => $user_id
        ]);

        echo json_encode([
            'ok' => true,
            'message' => 'Notas guardadas correctamente'
        ]);

    } else {
        echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    }

} catch (Exception $e) {
    error_log('chat/perfil/notas.php error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
}
?>