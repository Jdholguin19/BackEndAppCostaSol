<?php
/**
 *  api/login.php  – Endpoint de autenticación (JSON)
 *  Requiere: PHP ≥ 8.1, config/db.php, tabla usuario (password_hash bcrypt/argon2)
 *
 *  POST /api/login.php
 *  Body JSON: { "correo": "juan@dominio.com", "contrasena": "Secreta123" }
 *  Respuesta: 200 OK  { "ok": true, "token": "...", "usuario": { ... } }
 */

require_once __DIR__ . '/../config/db.php';            // ajusta el path si cambia
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'mensaje' => 'Método no permitido']));
}

/* ---------- 1. Leer y validar la entrada ---------- */
$input = json_decode(file_get_contents('php://input'), true);

$correo      = $input['correo']      ?? null;
$contrasena  = $input['contrasena']  ?? null;

if (!$correo || !$contrasena) {
    http_response_code(422);
    exit(json_encode(['ok' => false, 'mensaje' => 'Correo y contraseña requeridos']));
}

/* ---------- 2. Consultar al usuario ---------- */
try {
    $db   = DB::getDB();                                        // singleton PDO
    $sql  = 'SELECT id, nombres, apellidos, rol_id, contrasena_hash 
             FROM usuario WHERE correo = :correo LIMIT 1';

    $stmt = $db->prepare($sql);
    $stmt->execute([':correo' => $correo]);
    $u = $stmt->fetch();

    if (!$u || !password_verify($contrasena, $u['contrasena_hash'])) {
        http_response_code(401);
        exit(json_encode(['ok' => false, 'mensaje' => 'Credenciales incorrectas']));
    }

    /* ---------- 3. Generar un token sencillo (JWT opcional) ---------- */
    $payload = base64_encode(random_bytes(24));                 // placeholder
    // ► En producción genera un JWT/HMAC y guarda blacklist si vas a invalidar.

    /* ---------- 4. Responder ---------- */
    unset($u['contrasena_hash']);                               // nunca envíes el hash

    echo json_encode([
        'ok'      => true,
        'token'   => $payload,
        'usuario' => $u
    ]);

} catch (Throwable $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    exit(json_encode(['ok' => false, 'mensaje' => 'Error interno']));
}
