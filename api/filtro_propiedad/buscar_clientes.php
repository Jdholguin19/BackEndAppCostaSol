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
        exit(json_encode(['ok' => false, 'mensaje' => 'Acceso denegado. Solo responsables pueden buscar clientes.']));
    }
} catch (Exception $e) {
    http_response_code(500);
    exit(json_encode(['ok' => false, 'mensaje' => 'Error de autenticación']));
}

$query = $_GET['q'] ?? '';
$limit = min((int)($_GET['limit'] ?? 10), 20); // Máximo 20 resultados

if (strlen($query) < 1) {
    echo json_encode(['ok' => true, 'clientes' => []]);
    exit;
}

try {
    $db = DB::getDB();

    // Buscar clientes por nombre o apellido que empiecen con la consulta
    $sql = 'SELECT DISTINCT u.id, u.nombres, u.apellidos, 
                   CONCAT(u.nombres, " ", u.apellidos) as nombre_completo,
                   COUNT(p.id) as total_propiedades
            FROM usuario u
            LEFT JOIN propiedad p ON p.id_usuario = u.id
            WHERE (u.nombres LIKE :query OR u.apellidos LIKE :query OR CONCAT(u.nombres, " ", u.apellidos) LIKE :query)
            GROUP BY u.id, u.nombres, u.apellidos
            ORDER BY 
                CASE 
                    WHEN u.nombres LIKE :query_exact THEN 1
                    WHEN u.nombres LIKE :query_start THEN 2
                    WHEN u.apellidos LIKE :query_start THEN 3
                    ELSE 4
                END,
                u.nombres, u.apellidos
            LIMIT :limit';

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':query', $query . '%', PDO::PARAM_STR);
    $stmt->bindValue(':query_exact', $query, PDO::PARAM_STR);
    $stmt->bindValue(':query_start', $query . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear respuesta
    $clientes_formateados = array_map(function($cliente) {
        return [
            'id' => (int)$cliente['id'],
            'nombre_completo' => $cliente['nombre_completo'],
            'nombres' => $cliente['nombres'],
            'apellidos' => $cliente['apellidos'],
            'total_propiedades' => (int)$cliente['total_propiedades']
        ];
    }, $clientes);

    echo json_encode([
        'ok' => true,
        'clientes' => $clientes_formateados,
        'query' => $query,
        'total' => count($clientes_formateados)
    ]);

} catch (Throwable $e) {
    error_log('buscar_clientes.php: ' . $e->getMessage());
    http_response_code(500);
    exit(json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor.']));
}
?>
