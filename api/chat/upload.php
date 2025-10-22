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

    $actor = null; // { id, type }

    // Buscar usuario
    $stmt_user = $db->prepare('SELECT id, nombres FROM usuario WHERE token = :token LIMIT 1');
    $stmt_user->execute([':token' => $token]);
    $usr = $stmt_user->fetch(PDO::FETCH_ASSOC);
    if ($usr) {
        $actor = ['id' => (int)$usr['id'], 'type' => 'user'];
    } else {
        // Buscar responsable
        $stmt_resp = $db->prepare('SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1');
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

    // Validar archivo
    if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        echo json_encode(['ok' => false, 'error' => 'Archivo no enviado']);
        exit;
    }

    $file = $_FILES['file'];
    $size = (int)$file['size'];
    if ($size <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Archivo vacío']);
        exit;
    }
    // Limitar tamaño: 20MB
    if ($size > 20 * 1024 * 1024) {
        echo json_encode(['ok' => false, 'error' => 'Archivo demasiado grande (máx 20MB)']);
        exit;
    }

    // Tipos permitidos
    $allowed = [
        'image/png','image/jpeg','image/jpg','image/gif',
        'application/pdf','text/plain','application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'audio/webm','audio/mpeg','audio/mp3','audio/wav','audio/ogg'
    ];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) {
        // Permitir algunos por extensión si mime falla
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['png','jpg','jpeg','gif','pdf','txt','doc','docx','webm','mp3','wav','ogg'];
        if (!in_array($ext, $allowedExt)) {
            echo json_encode(['ok' => false, 'error' => 'Tipo de archivo no permitido']);
            exit;
        }
    }

    // Carpeta destino
    $baseDir = __DIR__ . '/../../uploads/chat';
    if (!is_dir($baseDir)) {
        if (!mkdir($baseDir, 0777, true)) {
            echo json_encode(['ok' => false, 'error' => 'No se pudo crear carpeta de subida']);
            exit;
        }
    }

    // Nombre único
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext === '') {
        // Derivar por mime
        $map = [
            'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif',
            'application/pdf' => 'pdf', 'text/plain' => 'txt',
            'audio/webm' => 'webm', 'audio/mpeg' => 'mp3', 'audio/wav' => 'wav', 'audio/ogg' => 'ogg'
        ];
        $ext = $map[$mime] ?? 'bin';
    }
    $prefix = $actor['type'] === 'user' ? 'u' : 'r';
    $fname = $prefix . $actor['id'] . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $baseDir . DIRECTORY_SEPARATOR . $fname;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el archivo']);
        exit;
    }

    // URL pública
    $url = '/uploads/chat/' . $fname;
    echo json_encode(['ok' => true, 'url' => $url]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno', 'detail' => $e->getMessage()]);
}