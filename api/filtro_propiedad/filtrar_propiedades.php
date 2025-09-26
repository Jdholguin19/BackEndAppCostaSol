<?php
/**
 * filtrar_propiedades.php - API para filtrar propiedades por responsables
 * 
 * POST /api/filtro_propiedad/filtrar_propiedades.php
 * 
 * Body:
 * {
 *   "filtros": {
 *     "manzana": "7100",
 *     "villa": "14", 
 *     "nombre_cliente": "Ana"
 *   }
 * }
 * 
 * Respuesta:
 * {
 *   "ok": true,
 *   "propiedades": [
 *     {
 *       "id": 123,
 *       "tipo": "Casa",
 *       "urbanizacion": "Catania",
 *       "manzana": "7100",
 *       "villa": "14",
 *       "cliente_nombre": "Ana María Felix",
 *       "cliente_apellidos": "García López"
 *     }
 *   ],
 *   "total": 1
 * }
 */

require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'mensaje' => 'Método no permitido']));
}

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
    
    // Verificar que sea responsable
    if ($usuario['tipo_usuario'] !== 'responsable') {
        http_response_code(403);
        exit(json_encode(['ok' => false, 'mensaje' => 'Acceso denegado. Solo responsables pueden filtrar propiedades']));
    }
    
    // Obtener filtros del body
    $input = json_decode(file_get_contents('php://input'), true);
    $filtros = $input['filtros'] ?? [];
    
    // Construir consulta SQL (responsables pueden filtrar todas las propiedades)
    $sql = 'SELECT p.id, p.manzana, p.villa, p.solar,
                   u.nombres as cliente_nombre, u.apellidos as cliente_apellidos,
                   CONCAT(u.nombres, " ", u.apellidos) as cliente_completo,
                   t.nombre as tipo, urb.nombre as urbanizacion
            FROM propiedad p
            LEFT JOIN usuario u ON u.id = p.id_usuario
            JOIN tipo_propiedad t ON t.id = p.tipo_id
            JOIN urbanizacion urb ON urb.id = p.id_urbanizacion
            WHERE 1=1';
    
    $params = [];
    $conditions = [];
    
    // Filtro por manzana
    if (!empty($filtros['manzana'])) {
        $conditions[] = 'p.manzana LIKE :manzana';
        $params[':manzana'] = '%' . $filtros['manzana'] . '%';
    }
    
    // Filtro por villa
    if (!empty($filtros['villa'])) {
        $conditions[] = 'p.villa LIKE :villa';
        $params[':villa'] = '%' . $filtros['villa'] . '%';
    }
    
    // Filtro por nombre del cliente
    if (!empty($filtros['nombre_cliente'])) {
        $conditions[] = '(u.nombres LIKE :nombre_cliente OR u.apellidos LIKE :nombre_cliente OR CONCAT(u.nombres, " ", u.apellidos) LIKE :nombre_cliente_completo)';
        $params[':nombre_cliente'] = '%' . $filtros['nombre_cliente'] . '%';
        $params[':nombre_cliente_completo'] = '%' . $filtros['nombre_cliente'] . '%';
    }
    
    // Agregar condiciones a la consulta
    if (!empty($conditions)) {
        $sql .= ' AND ' . implode(' AND ', $conditions);
    }
    
    // Ordenar por urbanización, manzana y villa
    $sql .= ' ORDER BY urb.nombre, p.manzana, p.villa LIMIT 100';
    
    // Ejecutar consulta
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
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
    
    echo json_encode([
        'ok' => true,
        'propiedades' => $propiedades_formateadas,
        'total' => count($propiedades_formateadas),
        'filtros_aplicados' => $filtros,
        'debug' => [
            'sql' => $sql,
            'params' => $params,
            'usuario_id' => $usuario['id']
        ]
    ]);
    
} catch (Throwable $e) {
    error_log('filtrar_propiedades: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
?>
