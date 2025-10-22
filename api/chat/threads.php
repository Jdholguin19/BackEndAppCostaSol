<?php
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = DB::getDB();

    // Auth via Bearer token (solo responsables pueden listar hilos)
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
        echo json_encode(['ok' => false, 'error' => 'Solo responsables pueden listar hilos']);
        exit;
    }

    $responsable_id = (int)$resp['id'];
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = max(1, min($limit, 200));
    $offset = max(0, ($page - 1) * $limit);

    $params = [':rid' => $responsable_id];
    $where = 'WHERE t.responsable_id = :rid';
    if ($q !== '') {
        $where .= ' AND (u.nombres LIKE :q OR CAST(u.id AS CHAR) = :qid)';
        $params[':q'] = '%' . $q . '%';
        $params[':qid'] = $q;
    }

    // Seleccionar último mensaje por hilo
    $sql = "SELECT t.id, t.user_id, u.nombres AS user_name, t.estado, t.created_at,
                   m.id AS last_message_id, m.content AS last_message, m.created_at AS last_created_at
            FROM chat_thread t
            JOIN usuario u ON u.id = t.user_id
            LEFT JOIN chat_message m ON m.id = (
                SELECT mm.id FROM chat_message mm WHERE mm.thread_id = t.id ORDER BY mm.id DESC LIMIT 1
            )
            $where
            ORDER BY COALESCE(m.created_at, t.created_at) DESC
            LIMIT $limit OFFSET $offset";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'threads' => $threads]);

} catch (Exception $e) {
    error_log('chat/threads.php error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
}