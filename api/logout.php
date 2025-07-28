<?php
/**
 * api/logout.php
 * POST { "token": "..." }
 * OK   { "ok": true, "mensaje": "Sesión cerrada correctamente" }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok'=>false,'mensaje'=>'Método no permitido']));
}

/* ---------- 1. Entrada ---------- */
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? null;

if (!$token) {
    http_response_code(422);
    exit(json_encode(['ok'=>false,'mensaje'=>'Token requerido']));
}

try {
    /* ---------- 2. Validar token (opcional) ---------- */
    // Si tienes una tabla de tokens activos, aquí podrías invalidarlo
    // Por ahora, simplemente validamos que el token tenga el formato correcto
    $decoded = base64_decode($token);
    if (!$decoded || strlen($decoded) < 20) {
        http_response_code(401);
        exit(json_encode(['ok'=>false,'mensaje'=>'Token inválido']));
    }

    /* ---------- 3. Invalidar token en BD (opcional) ---------- */
    // Si manejas tokens en base de datos:
    // $db = DB::getDB();
    // $sql = 'UPDATE tokens_activos SET activo = 0 WHERE token = :token';
    // $stmt = $db->prepare($sql);
    // $stmt->execute([':token' => $token]);

    /* ---------- 4. Respuesta exitosa ---------- */
    echo json_encode([
        'ok' => true,
        'mensaje' => 'Sesión cerrada correctamente'
    ]);

} catch (Throwable $e) {
    error_log('Logout error: '.$e->getMessage());
    http_response_code(500);
    exit(json_encode(['ok'=>false,'mensaje'=>'Error interno']));
}
?>