<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../../config/db.php';

try {
    // Verificar autenticación
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token de autorización requerido']);
        exit;
    }
    
    $token = $matches[1];
    
    // Validar token usando el patrón del proyecto
    $pdo = DB::getDB();
    $authenticated_user = null;
    $is_responsable = false;
    
    // Buscar en tabla 'responsable'
    $stmt_resp = $pdo->prepare('SELECT id, nombre FROM responsable WHERE token = :token AND token IS NOT NULL');
    $stmt_resp->execute([':token' => $token]);
    $responsable = $stmt_resp->fetch(PDO::FETCH_ASSOC);
    
    if ($responsable) {
        $authenticated_user = $responsable;
        $is_responsable = true;
    } else {
        // Buscar en tabla 'usuario' como fallback
        $stmt_user = $pdo->prepare('SELECT id, nombres, rol_id FROM usuario WHERE token = :token AND token IS NOT NULL');
        $stmt_user->execute([':token' => $token]);
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $authenticated_user = $user;
            $is_responsable = false;
        }
    }
    
    if (!$authenticated_user) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token inválido']);
        exit;
    }
    
    // Verificar que sea responsable
    if (!$is_responsable) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Solo responsables pueden ver detalles']);
        exit;
    }
    
    $user_id = $_GET['user_id'] ?? null;
    $tipo_metrica = $_GET['tipo'] ?? null;
    
    if (!$user_id || !$tipo_metrica) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'user_id y tipo son requeridos']);
        exit;
    }
    
    switch ($tipo_metrica) {
        case 'propiedades':
            $stmt = $pdo->prepare("
                SELECT 
                    p.id,
                    p.manzana,
                    p.solar,
                    p.villa,
                    p.fecha_compra,
                    p.fecha_entrega,
                    tp.nombre as tipo_propiedad,
                    ec.nombre as etapa_construccion,
                    ec.porcentaje as porcentaje_construccion,
                    ep.nombre as estado_propiedad,
                    u.nombre as urbanizacion,
                    ak.nombre as acabado_kit,
                    p.acabado_color_seleccionado
                FROM propiedad p
                LEFT JOIN tipo_propiedad tp ON p.tipo_id = tp.id
                LEFT JOIN etapa_construccion ec ON p.etapa_id = ec.id
                LEFT JOIN estado_propiedad ep ON p.estado_id = ep.id
                LEFT JOIN urbanizacion u ON p.id_urbanizacion = u.id
                LEFT JOIN acabado_kit ak ON p.acabado_kit_seleccionado_id = ak.id
                WHERE p.id_usuario = ?
                ORDER BY p.fecha_insertado DESC
            ");
            $stmt->execute([$user_id]);
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'ctg':
            $stmt = $pdo->prepare("
                SELECT 
                    c.numero_solicitud,
                    c.fecha_ingreso,
                    c.descripcion,
                    ec.nombre as estado,
                    c.fecha_resolucion,
                    c.observaciones,
                    tc.nombre as tipo_ctg
                FROM ctg c
                LEFT JOIN estado_ctg ec ON c.estado_id = ec.id
                LEFT JOIN tipo_ctg tc ON c.tipo_id = tc.id
                WHERE c.id_usuario = ?
                ORDER BY c.fecha_ingreso DESC
                LIMIT 10
            ");
            $stmt->execute([$user_id]);
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'pqr':
            $stmt = $pdo->prepare("
                SELECT 
                    p.numero_solicitud,
                    p.fecha_ingreso,
                    p.descripcion,
                    ep.nombre as estado,
                    p.fecha_resolucion,
                    p.observaciones,
                    tp.nombre as tipo_pqr
                FROM pqr p
                LEFT JOIN estado_pqr ep ON p.estado_id = ep.id
                LEFT JOIN tipo_pqr tp ON p.tipo_id = tp.id
                WHERE p.id_usuario = ?
                ORDER BY p.fecha_ingreso DESC
                LIMIT 10
            ");
            $stmt->execute([$user_id]);
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'citas':
            $stmt = $pdo->prepare("
                SELECT 
                    av.id,
                    av.fecha_reunion,
                    av.hora_reunion,
                    av.estado,
                    av.asistencia,
                    av.resultado,
                    av.observaciones,
                    pa.proposito as proposito,
                    CONCAT(r.nombre) as responsable_nombre,
                    p.manzana,
                    p.solar,
                    p.villa
                FROM agendamiento_visitas av
                LEFT JOIN proposito_agendamiento pa ON av.proposito_id = pa.id
                LEFT JOIN responsable r ON av.responsable_id = r.id
                LEFT JOIN propiedad p ON av.id_propiedad = p.id
                WHERE av.id_usuario = ?
                ORDER BY av.fecha_reunion DESC, av.hora_reunion DESC
                LIMIT 10
            ");
            $stmt->execute([$user_id]);
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Tipo de métrica no válido']);
            exit;
    }
    
    echo json_encode([
        'ok' => true,
        'tipo' => $tipo_metrica,
        'detalles' => $detalles
    ]);
    
} catch (Exception $e) {
    error_log("Error en detalles_metricas.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
}
?>