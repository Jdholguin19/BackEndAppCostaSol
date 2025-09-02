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
            $auth_id = 1; // Placeholder, solo para confirmar que está autenticado
        }
    } catch (Exception $e) {}
}
if ($auth_id === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit();
}
// --- Fin Autenticación ---

$propiedad_id = filter_input(INPUT_GET, 'propiedad_id', FILTER_VALIDATE_INT);
if (!$propiedad_id) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'No se ha especificado una propiedad.']);
    exit;
}

try {
    $conn = DB::getDB();
    // En el futuro, aquí se podría añadir lógica para filtrar kits por propiedad_id
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, url_imagen_principal, costo FROM acabado_kit ORDER BY id ASC");
    $stmt->execute();

    $kits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'kits' => $kits]);

} catch (Throwable $e) {
    error_log('acabados_kits_disponibles: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
?>