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

try {
    $conn = DB::getDB();
    // Seleccionamos solo los paquetes activos
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, precio, fotos FROM paquetes_adicionales WHERE activo = 1 ORDER BY id ASC");
    $stmt->execute();

    $paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // La columna 'fotos' es JSON, así que la decodificamos para el frontend.
    foreach ($paquetes as &$paquete) {
        if (!empty($paquete['fotos'])) {
            $paquete['fotos'] = json_decode($paquete['fotos'], true);
        } else {
            $paquete['fotos'] = [];
        }
    }

    echo json_encode(['ok' => true, 'paquetes' => $paquetes]);

} catch (Throwable $e) {
    error_log('paquetes_adicionales: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
?>