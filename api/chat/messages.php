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

    $actor = null; // { id, type: 'user'|'responsable' }

    // Buscar usuario
    $sql_user = 'SELECT id, nombres, rol_id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $usr = $stmt_user->fetch(PDO::FETCH_ASSOC);
    if ($usr) {
        $actor = ['id' => (int)$usr['id'], 'type' => 'user'];
    } else {
        // Buscar responsable
        $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
        $stmt_resp = $db->prepare($sql_resp);
        $stmt_resp->execute([':token' => $token]);
        $resp = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($resp) {
            $actor = ['id' => (int)$resp['id'], 'type' => 'responsable'];
        }
    }

    if (!$actor) {
        echo json_encode(['ok' => false, 'error' => 'Token inválido']);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $thread_id = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $since_id = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;

        if ($thread_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'thread_id requerido']);
            exit;
        }

        $params = [':tid' => $thread_id];
        $cond = 'WHERE m.thread_id = :tid';
        if ($since_id > 0) {
            $cond .= ' AND m.id > :sid';
            $params[':sid'] = $since_id;
        }

        $sql = "SELECT m.id, m.sender_type, m.sender_id, m.content, m.created_at, m.read_at
                FROM chat_message m $cond
                ORDER BY m.id ASC
                LIMIT " . max(1, min($limit, 200));
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['ok' => true, 'messages' => $messages]);
        exit;
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $thread_id = (int)($data['thread_id'] ?? 0);
        $content   = trim((string)($data['content'] ?? ''));

        if ($thread_id <= 0 || $content === '') {
            echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos']);
            exit;
        }

        $sql_ins = 'INSERT INTO chat_message (thread_id, sender_type, sender_id, content) VALUES (:tid, :stype, :sid, :content)';
        $stmt = $db->prepare($sql_ins);
        $stmt->execute([
            ':tid' => $thread_id,
            ':stype' => $actor['type'],
            ':sid' => $actor['id'],
            ':content' => $content
        ]);

        echo json_encode(['ok' => true, 'message_id' => (int)$db->lastInsertId()]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Método no soportado']);

} catch (Exception $e) {
    error_log('chat/messages.php error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
}