<?php
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = DB::getDB();

    // Auth via Bearer token (usuario o responsable)
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

    $authenticated_user = null;
    $is_responsable = false;

    // Buscar usuario
    $sql_user = 'SELECT id, nombres, rol_id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$authenticated_user) {
        // Buscar responsable
        $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
        $stmt_resp = $db->prepare($sql_resp);
        $stmt_resp->execute([':token' => $token]);
        $authenticated_user = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($authenticated_user) {
            $is_responsable = true;
        }
    }

    if (!$authenticated_user) {
        echo json_encode(['ok' => false, 'error' => 'Token inválido']);
        exit;
    }

    // Responsable destino fijo id=2
    $DEST_RESPONSABLE_ID = 2;

    if ($is_responsable) {
        // Si quien consulta es el responsable, puede pedir hilo por user_id
        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        if ($user_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'user_id requerido para responsable']);
            exit;
        }
        $responsable_id = (int)$authenticated_user['id'];
    } else {
        // Usuario: su propio hilo con responsable id=2
        $user_id = (int)$authenticated_user['id'];
        $responsable_id = $DEST_RESPONSABLE_ID;
    }

    // Buscar hilo existente
    $sql_thread = 'SELECT id, user_id, responsable_id, estado, created_at
                     FROM chat_thread WHERE user_id = :uid AND responsable_id = :rid LIMIT 1';
    $stmt_thread = $db->prepare($sql_thread);
    $stmt_thread->execute([':uid' => $user_id, ':rid' => $responsable_id]);
    $thread = $stmt_thread->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        // Crear hilo
        $sql_create = 'INSERT INTO chat_thread (user_id, responsable_id, estado) VALUES (:uid, :rid, 1)';
        $stmt_create = $db->prepare($sql_create);
        $stmt_create->execute([':uid' => $user_id, ':rid' => $responsable_id]);
        $thread_id = (int)$db->lastInsertId();
        $thread = [
            'id' => $thread_id,
            'user_id' => $user_id,
            'responsable_id' => $responsable_id,
            'estado' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    echo json_encode(['ok' => true, 'thread' => $thread]);

} catch (Exception $e) {
    error_log('chat/thread.php error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
}