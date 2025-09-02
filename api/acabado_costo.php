<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../config/db.php';

// --- Lógica de Autenticación por Token ---
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
        // Silencio en caso de error
    }
}

if ($auth_id === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit();
}
// --- Fin Autenticación ---

$kit_id = filter_input(INPUT_GET, 'kit_id', FILTER_VALIDATE_INT);

if (!$kit_id) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Se requiere un ID de kit.']);
    exit;
}

try {
    $conn = DB::getDB();
    $stmt = $conn->prepare("SELECT costo FROM acabado_kit WHERE id = :kit_id");
    $stmt->execute([':kit_id' => $kit_id]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Kit no encontrado.']);
        exit;
    }

    echo json_encode(['ok' => true, 'costo' => (float)$result['costo']]);

} catch (Throwable $e) {
    error_log('acabado_costo: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
?>