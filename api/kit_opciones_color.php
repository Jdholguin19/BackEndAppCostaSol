<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../config/db.php';

// --- Lógica de Autenticación --- 
$auth_id = null;
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    try {
        $conn_auth = DB::getDB();
        $stmt = $conn_auth->prepare('SELECT id FROM usuario WHERE token = :token UNION SELECT id FROM responsable WHERE token = :token');
        $stmt->execute([':token' => $token]);
        if ($stmt->fetch()) {
            $auth_id = 1;
        }
    } catch (Exception $e) {}
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
    echo json_encode(['ok' => false, 'mensaje' => 'No se ha especificado un kit de acabados.']);
    exit;
}

try {
    $conn = DB::getDB();
    $stmt = $conn->prepare(
        "SELECT id, nombre_opcion, color_nombre, url_imagen_opcion 
         FROM kit_color_opcion 
         WHERE acabado_kit_id = :kit_id 
         ORDER BY id ASC"
    );
    $stmt->execute([':kit_id' => $kit_id]);

    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'opciones' => $opciones]);

} catch (Throwable $e) {
    error_log('kit_opciones_color: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
?>
