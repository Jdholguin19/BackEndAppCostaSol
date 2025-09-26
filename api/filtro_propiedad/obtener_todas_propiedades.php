<?php
/**
 * obtener_todas_propiedades.php - API para obtener todas las propiedades para responsables
 * 
 * GET /api/filtro_propiedad/obtener_todas_propiedades.php
 * 
 * Respuesta:
 * {
 *   "ok": true,
 *   "propiedades": [...],
 *   "total": 350,
 *   "mostrar_filtro": true
 * }
 */

require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    http_response_code(401);
    exit(json_encode(['ok' => false, 'mensaje' => 'Token de autenticación requerido']));
}

try {
    $db = DB::getDB();
    
    // Verificar token y obtener usuario (primero en tabla usuario, luego en responsable)
    $stmt_token = $db->prepare('SELECT u.id, u.nombres, u.apellidos, "usuario" as tipo_usuario 
                               FROM usuario u 
                               WHERE u.token = :token');
    $stmt_token->execute([':token' => $token]);
    $usuario = $stmt_token->fetch(PDO::FETCH_ASSOC);
    
    // Si no se encuentra en usuario, buscar en responsable
    if (!$usuario) {
        $stmt_responsable = $db->prepare('SELECT r.id, r.nombre as nombres, "" as apellidos, "responsable" as tipo_usuario 
                                         FROM responsable r 
                                         WHERE r.token = :token AND r.estado = 1');
        $stmt_responsable->execute([':token' => $token]);
        $usuario = $stmt_responsable->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$usuario) {
        http_response_code(401);
        exit(json_encode(['ok' => false, 'mensaje' => 'Token inválido']));
    }
    
    // Obtener todas las propiedades (responsables ven todas las propiedades)
    $sql = 'SELECT p.id, p.manzana, p.villa, p.solar,
                   u.nombres as cliente_nombre, u.apellidos as cliente_apellidos,
                   CONCAT(u.nombres, " ", u.apellidos) as cliente_completo,
                   t.nombre as tipo, urb.nombre as urbanizacion
            FROM propiedad p
            LEFT JOIN usuario u ON u.id = p.id_usuario
            JOIN tipo_propiedad t ON t.id = p.tipo_id
            JOIN urbanizacion urb ON urb.id = p.id_urbanizacion
            ORDER BY urb.nombre, p.manzana, p.villa';
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear respuesta
    $propiedades_formateadas = array_map(function($p) {
        return [
            'id' => (int)$p['id'],
            'tipo' => $p['tipo'],
            'urbanizacion' => $p['urbanizacion'],
            'manzana' => $p['manzana'],
            'villa' => $p['villa'],
            'solar' => $p['solar'],
            'cliente_nombre' => $p['cliente_nombre'] ?? 'Sin asignar',
            'cliente_apellidos' => $p['cliente_apellidos'] ?? '',
            'cliente_completo' => $p['cliente_completo'] ?? 'Sin asignar',
            'display_name' => $p['tipo'] . ' ' . $p['urbanizacion'] . ' MZ ' . $p['manzana'] . ' V ' . $p['villa']
        ];
    }, $propiedades);
    
    // Determinar si mostrar filtro (solo para responsables con más de 20 propiedades)
    $is_responsable = ($usuario['tipo_usuario'] === 'responsable');
    $mostrar_filtro = $is_responsable && count($propiedades_formateadas) > 20;
    
    echo json_encode([
        'ok' => true,
        'propiedades' => $propiedades_formateadas,
        'total' => count($propiedades_formateadas),
        'mostrar_filtro' => $mostrar_filtro,
        'is_responsable' => $is_responsable,
        'tipo_usuario' => $usuario['tipo_usuario']
    ]);
    
} catch (Throwable $e) {
    error_log('obtener_todas_propiedades: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
?>
