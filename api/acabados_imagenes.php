<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';

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

$kit_id = filter_input(INPUT_GET, 'acabado_kit_id', FILTER_VALIDATE_INT);
$color = filter_input(INPUT_GET, 'color', FILTER_SANITIZE_STRING);

if (!$kit_id || !$color) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Se requiere un kit y un color.']);
    exit;
}

try {
    $conn = DB::getDB();
    $stmt = $conn->prepare(
        "SELECT 
            c.nombre AS componente, 
            ad.url_imagen,
            ad.descripcion
         FROM 
            acabado_detalle ad
         JOIN 
            componente c ON ad.componente_id = c.id
         WHERE 
            ad.acabado_kit_id = :kit_id AND ad.color = :color
         ORDER BY 
            c.id ASC"
    );
    
    $stmt->execute([
        ':kit_id' => $kit_id,
        ':color' => $color
    ]);

    $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'imagenes' => $imagenes]);

} catch (Throwable $e) {
    error_log('acabados_imagenes: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
?>
