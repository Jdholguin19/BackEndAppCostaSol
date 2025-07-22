<?php
/**
 *  api/login.php
 *  POST  { "correo": "...", "contrasena": "..." }
 *  OK    { ok:true, token:"...", usuario:{ id, nombres, apellidos, correo, url_foto_perfil, rol_id } }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok'=>false,'mensaje'=>'Método no permitido']));
}

/* ---------- 1. Entrada ---------- */
$input      = json_decode(file_get_contents('php://input'), true);
$correo     = $input['correo']     ?? null;
$contrasena = $input['contrasena'] ?? null;

if (!$correo || !$contrasena) {
    http_response_code(422);
    exit(json_encode(['ok'=>false,'mensaje'=>'Correo y contraseña requeridos']));
}

try {
    /* ---------- 2. Consulta ---------- */
    $db  = DB::getDB();
    $sql = 'SELECT id, nombres, apellidos, correo, url_foto_perfil,
                   rol_id, contrasena_hash
              FROM usuario
             WHERE correo = :correo
             LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->execute([':correo'=>$correo]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u || !password_verify($contrasena, $u['contrasena_hash'])) {
        http_response_code(401);
        exit(json_encode(['ok'=>false,'mensaje'=>'Credenciales incorrectas']));
    }

    /* ---------- 3. Generar token ---------- */
    $token = base64_encode(random_bytes(24));      // placeholder

    /* ---------- 4. Respuesta ---------- */
    unset($u['contrasena_hash']);
    echo json_encode(['ok'=>true,'token'=>$token,'usuario'=>$u]);

} catch (Throwable $e) {
    error_log('Login error: '.$e->getMessage());
    http_response_code(500);
    exit(json_encode(['ok'=>false,'mensaje'=>'Error interno']));
}

