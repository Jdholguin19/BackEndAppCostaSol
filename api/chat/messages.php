<?php
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = DB::getDB();

function getBearerToken(): string {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (strpos($auth, 'Bearer ') === 0) {
        return substr($auth, 7);
    }
    return '';
}

function identifyActor(PDO $pdo, string $token): ?array {
    if (!$token) return null;
    // Usuario
    $stmt = $pdo->prepare('SELECT id FROM usuario WHERE token = :t LIMIT 1');
    $stmt->execute([':t' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['id'])) return ['type' => 'user', 'id' => (int)$row['id']];
    // Responsable
    $stmt = $pdo->prepare('SELECT id FROM responsable WHERE token = :t LIMIT 1');
    $stmt->execute([':t' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['id'])) return ['type' => 'responsable', 'id' => (int)$row['id']];
    return null;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $threadId = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;
    $sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;
    $limit   = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
    if ($threadId <= 0) { echo json_encode(['ok'=>false,'error'=>'Invalid thread_id']); exit; }
    $limit = max(1, min($limit, 500));

    $sql = "SELECT m.id, m.thread_id, m.sender_type, m.content, m.created_at, m.reply_to_id,
                   rt.sender_type AS reply_to_sender_type, rt.content AS reply_to_content
            FROM chat_message m
            LEFT JOIN chat_message rt ON rt.id = m.reply_to_id
            WHERE m.thread_id = :tid AND m.id > :sid
            ORDER BY m.id ASC
            LIMIT $limit"; // safe: $limit clamped integer
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':tid'=>$threadId, ':sid'=>$sinceId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $messages = array_map(function($row){
        $msg = [
            'id' => (int)$row['id'],
            'thread_id' => (int)$row['thread_id'],
            'sender_type' => $row['sender_type'],
            'content' => $row['content'],
            'created_at' => $row['created_at'],
        ];
        if (!empty($row['reply_to_id'])) {
            $msg['reply_to'] = [
                'id' => (int)$row['reply_to_id'],
                'sender_type' => $row['reply_to_sender_type'] ?? null,
                'content' => $row['reply_to_content'] ?? null,
            ];
        }
        return $msg;
    }, $rows);

    echo json_encode(['ok' => true, 'messages' => $messages]);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $threadId = isset($data['thread_id']) ? (int)$data['thread_id'] : 0;
    $content  = isset($data['content']) ? trim((string)$data['content']) : '';
    $replyToId = isset($data['reply_to_id']) ? (int)$data['reply_to_id'] : null;

    $token = getBearerToken();
    $actor = identifyActor($pdo, $token);

    // Permitir que el frontend envíe sender_type explícito; si no, usar el del token
    $providedType = isset($data['sender_type']) ? (string)$data['sender_type'] : '';
    $senderType = in_array($providedType, ['user','responsable'], true) ? $providedType : ($actor['type'] ?? null);
    $senderId   = $actor['id'] ?? null;

    if ($threadId <= 0 || $senderType === null || $senderId === null || $content === '') {
        echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
        exit;
    }

    // Validar que el hilo exista y opcionalmente pertenezca al actor
    $st = $pdo->prepare('SELECT id, user_id, responsable_id FROM chat_thread WHERE id = :id LIMIT 1');
    $st->execute([':id' => $threadId]);
    $thread = $st->fetch(PDO::FETCH_ASSOC);
    if (!$thread) { echo json_encode(['ok'=>false,'error'=>'Thread not found']); exit; }
    // Reglas de pertenencia mínimas (suaves para no romper flujos existentes)
    if ($senderType === 'user' && (int)$thread['user_id'] !== (int)$senderId) {
        echo json_encode(['ok'=>false,'error'=>'Thread does not belong to user']);
        exit;
    }
    // Para responsable no bloqueamos si no coincide para compatibilidad; puedes endurecer si es necesario

    $stmt = $pdo->prepare('INSERT INTO chat_message (thread_id, sender_type, sender_id, content, reply_to_id) VALUES (:tid, :st, :sid, :ct, :rid)');
    $stmt->execute([':tid'=>$threadId, ':st'=>$senderType, ':sid'=>$senderId, ':ct'=>$content, ':rid'=>$replyToId]);
    $id = (int)$pdo->lastInsertId();

    echo json_encode(['ok' => true, 'id' => $id]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Method not allowed']);