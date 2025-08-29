<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';

// --- Lógica de Autenticación por Token (simplificada) ---
// Se asegura de que solo un usuario autenticado pueda ver las opciones.
$auth_id = null;
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    try {
        $conn_auth = DB::getDB();
        $stmt = $conn_auth->prepare('SELECT id FROM usuario WHERE token = :token UNION SELECT id FROM responsable WHERE token = :token');
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $auth_id = $user['id'];
        }
    } catch (Exception $e) {
        // No hacer nada, el auth_id seguirá siendo null
    }
}

if ($auth_id === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit();
}
// --- Fin Autenticación ---

try {
    $conn = DB::getDB();
    $stmt = $conn->prepare("SELECT id, nombre, url_plano, descripcion FROM plano ORDER BY id ASC");
    $stmt->execute();

    $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'planos' => $planos]);

} catch (Throwable $e) {
    error_log('planos_disponibles: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
?>
