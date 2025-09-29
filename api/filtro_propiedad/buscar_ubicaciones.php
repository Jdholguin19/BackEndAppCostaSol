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
        exit(json_encode(['ok' => false, 'mensaje' => 'Acceso denegado. Solo responsables pueden buscar ubicaciones.']));
    }
} catch (Exception $e) {
    http_response_code(500);
    exit(json_encode(['ok' => false, 'mensaje' => 'Error de autenticación']));
}

$query = $_GET['q'] ?? '';
$limit = min((int)($_GET['limit'] ?? 15), 25); // Máximo 25 resultados

if (strlen($query) < 1) {
    echo json_encode(['ok' => true, 'ubicaciones' => []]);
    exit;
}

try {
    $db = DB::getDB();

    // Si la consulta contiene un guión, buscar villas específicas
    if (strpos($query, '-') !== false) {
        // Formato: "7100-" o "7100-01"
        $parts = explode('-', $query);
        $manzana = trim($parts[0]);
        $villa_partial = isset($parts[1]) ? trim($parts[1]) : '';
        
        if (empty($manzana)) {
            echo json_encode(['ok' => true, 'ubicaciones' => []]);
            exit;
        }

        $sql = 'SELECT DISTINCT p.manzana, p.villa,
                       CONCAT(p.manzana, " - ", p.villa) as ubicacion_completa,
                       u.nombre as urbanizacion,
                       CONCAT(us.nombres, " ", us.apellidos) as cliente_completo,
                       us.id as cliente_id
                FROM propiedad p
                JOIN urbanizacion u ON u.id = p.id_urbanizacion
                LEFT JOIN usuario us ON us.id = p.id_usuario
                WHERE p.manzana = :manzana';
        
        $params = [':manzana' => $manzana];
        
        if (!empty($villa_partial)) {
            $sql .= ' AND p.villa LIKE :villa';
            $params[':villa'] = $villa_partial . '%';
        }
        
        $sql .= ' ORDER BY p.villa LIMIT :limit';
        $params[':limit'] = $limit;
        
    } else {
        // Buscar manzanas que empiecen con la consulta
        $sql = 'SELECT DISTINCT p.manzana,
                       CONCAT(p.manzana, " - ") as ubicacion_completa,
                       u.nombre as urbanizacion,
                       COUNT(DISTINCT p.villa) as total_villas
                FROM propiedad p
                JOIN urbanizacion u ON u.id = p.id_urbanizacion
                WHERE p.manzana LIKE :query
                GROUP BY p.manzana, u.nombre
                ORDER BY p.manzana
                LIMIT :limit';
        
        $params = [
            ':query' => $query . '%',
            ':limit' => $limit
        ];
    }

    $stmt = $db->prepare($sql);
    
    // Bind parameters with correct types
    foreach ($params as $key => $value) {
        if ($key === ':limit') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
    
    $stmt->execute();
    $ubicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear respuesta
    $ubicaciones_formateadas = array_map(function($ubicacion) {
        return [
            'ubicacion_completa' => $ubicacion['ubicacion_completa'],
            'manzana' => $ubicacion['manzana'],
            'villa' => $ubicacion['villa'] ?? null,
            'urbanizacion' => $ubicacion['urbanizacion'],
            'cliente_completo' => $ubicacion['cliente_completo'] ?? null,
            'cliente_id' => isset($ubicacion['cliente_id']) ? (int)$ubicacion['cliente_id'] : null,
            'total_villas' => isset($ubicacion['total_villas']) ? (int)$ubicacion['total_villas'] : null
        ];
    }, $ubicaciones);

    echo json_encode([
        'ok' => true,
        'ubicaciones' => $ubicaciones_formateadas,
        'query' => $query,
        'total' => count($ubicaciones_formateadas)
    ]);

} catch (Throwable $e) {
    error_log('buscar_ubicaciones.php: ' . $e->getMessage());
    http_response_code(500);
    exit(json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor.']));
}
?>
