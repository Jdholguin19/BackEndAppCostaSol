<?php
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// Verificar token
$token = null;
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $auth_header = $headers['Authorization'];
    if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    http_response_code(401);
    exit(json_encode(['ok' => false, 'mensaje' => 'Token requerido']));
}

// Verificar que sea responsable
try {
    $db = DB::getDB();
    
    // Verificar token en tabla responsable
    $stmt_responsable = $db->prepare('SELECT id, nombre FROM responsable WHERE token = :token AND estado = 1');
    $stmt_responsable->execute([':token' => $token]);
    $responsable = $stmt_responsable->fetch(PDO::FETCH_ASSOC);
    
    if (!$responsable) {
        http_response_code(403);
        exit(json_encode(['ok' => false, 'mensaje' => 'Acceso denegado. Solo responsables pueden obtener propiedades de clientes.']));
    }
} catch (Exception $e) {
    http_response_code(500);
    exit(json_encode(['ok' => false, 'mensaje' => 'Error de autenticación']));
}

$cliente_id = $_GET['cliente_id'] ?? null;

if (!$cliente_id || !ctype_digit($cliente_id)) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'mensaje' => 'ID de cliente requerido.']));
}

try {
    $db = DB::getDB();

    // Obtener propiedades del cliente
    $sql = 'SELECT p.id, p.manzana, p.villa, p.solar,
                   CONCAT(p.manzana, " - ", p.villa) as ubicacion_completa,
                   u.nombre as urbanizacion,
                   tp.nombre as tipo,
                   CONCAT(us.nombres, " ", us.apellidos) as cliente_completo
            FROM propiedad p
            JOIN urbanizacion u ON u.id = p.id_urbanizacion
            JOIN tipo_propiedad tp ON tp.id = p.tipo_id
            JOIN usuario us ON us.id = p.id_usuario
            WHERE p.id_usuario = :cliente_id
            ORDER BY u.nombre, p.manzana, p.villa';

    $stmt = $db->prepare($sql);
    $stmt->execute([':cliente_id' => $cliente_id]);
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear respuesta
    $propiedades_formateadas = array_map(function($propiedad) {
        return [
            'id' => (int)$propiedad['id'],
            'ubicacion_completa' => $propiedad['ubicacion_completa'],
            'manzana' => $propiedad['manzana'],
            'villa' => $propiedad['villa'],
            'solar' => $propiedad['solar'],
            'urbanizacion' => $propiedad['urbanizacion'],
            'tipo' => $propiedad['tipo'],
            'cliente_completo' => $propiedad['cliente_completo'],
            'display_name' => $propiedad['tipo'] . ' ' . $propiedad['urbanizacion'] . ' MZ ' . $propiedad['manzana'] . ' V ' . $propiedad['villa']
        ];
    }, $propiedades);

    echo json_encode([
        'ok' => true,
        'propiedades' => $propiedades_formateadas,
        'total' => count($propiedades_formateadas)
    ]);

} catch (Throwable $e) {
    error_log('obtener_propiedades_cliente.php: ' . $e->getMessage());
    http_response_code(500);
    exit(json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor.']));
}
?>
