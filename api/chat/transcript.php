<?php
require_once __DIR__ . '/../../config/db.php';

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
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Token requerido']);
        exit;
    }

    $actor = null; // { id, type: 'user'|'responsable' }

    // Buscar usuario
    $stmt_user = $db->prepare('SELECT id, nombres FROM usuario WHERE token = :token LIMIT 1');
    $stmt_user->execute([':token' => $token]);
    $usr = $stmt_user->fetch(PDO::FETCH_ASSOC);
    if ($usr) {
        $actor = ['id' => (int)$usr['id'], 'type' => 'user', 'name' => $usr['nombres']];
    } else {
        // Buscar responsable
        $stmt_resp = $db->prepare('SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1');
        $stmt_resp->execute([':token' => $token]);
        $resp = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($resp) {
            $actor = ['id' => (int)$resp['id'], 'type' => 'responsable', 'name' => $resp['nombre']];
        }
    }

    if (!$actor) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Token inválido']);
        exit;
    }

    $thread_id = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;
    if ($thread_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'thread_id requerido']);
        exit;
    }

    // Verificar pertenencia al hilo
    $stmt_t = $db->prepare('SELECT t.id, t.user_id, u.nombres AS user_name, t.responsable_id, r.nombre AS responsable_name, t.created_at
                            FROM chat_thread t
                            JOIN usuario u ON u.id = t.user_id
                            JOIN responsable r ON r.id = t.responsable_id
                            WHERE t.id = :tid LIMIT 1');
    $stmt_t->execute([':tid' => $thread_id]);
    $t = $stmt_t->fetch(PDO::FETCH_ASSOC);
    if (!$t) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Hilo no encontrado']);
        exit;
    }

    if ($actor['type'] === 'user' && (int)$t['user_id'] !== $actor['id']) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Sin permisos para este hilo']);
        exit;
    }
    if ($actor['type'] === 'responsable' && (int)$t['responsable_id'] !== $actor['id']) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Sin permisos para este hilo']);
        exit;
    }

    // Obtener mensajes
    $stmt_m = $db->prepare('SELECT id, sender_type, sender_id, content, created_at
                            FROM chat_message WHERE thread_id = :tid ORDER BY id ASC');
    $stmt_m->execute([':tid' => $thread_id]);
    $messages = $stmt_m->fetchAll(PDO::FETCH_ASSOC);

    $lines = [];
    $lines[] = 'Transcripción de chat';
    $lines[] = 'Hilo #' . $t['id'] . ' | Usuario: ' . $t['user_name'] . ' | Responsable: ' . $t['responsable_name'];
    $lines[] = 'Creado: ' . $t['created_at'];
    $lines[] = str_repeat('-', 80);

    foreach ($messages as $m) {
        $who = $m['sender_type'] === 'responsable' ? 'Responsable' : 'Usuario';
        $ts = $m['created_at'];
        $content = preg_replace('/\r?\n/', ' ', (string)$m['content']);
        $lines[] = "[$ts] $who: $content";
    }

    $text = implode("\r\n", $lines) . "\r\n";

    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="chat_transcript_' . $thread_id . '.txt"');
    header('Content-Length: ' . strlen($text));
    echo $text;

} catch (Exception $e) {
    error_log('chat/transcript.php error: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
}