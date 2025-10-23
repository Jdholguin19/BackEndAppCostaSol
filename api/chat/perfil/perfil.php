<?php
require_once __DIR__ . '/../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = DB::getDB();

    // Auth via Bearer token (solo responsables pueden ver perfiles)
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = null;
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
    }
    if (!$token) {
        echo json_encode(['ok' => false, 'error' => 'Token requerido']);
        exit;
    }

    $stmt_resp = $db->prepare('SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1');
    $stmt_resp->execute([':token' => $token]);
    $resp = $stmt_resp->fetch(PDO::FETCH_ASSOC);
    if (!$resp) {
        echo json_encode(['ok' => false, 'error' => 'Solo responsables pueden ver perfiles']);
        exit;
    }

    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    if ($user_id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'user_id requerido']);
        exit;
    }

    // Obtener información básica del usuario
    $stmt_user = $db->prepare('
        SELECT u.id, u.nombres, u.apellidos, u.correo, u.cedula, u.telefono, 
               u.fecha_insertado, u.url_foto_perfil, r.nombre as rol_nombre
        FROM usuario u
        LEFT JOIN rol r ON r.id = u.rol_id
        WHERE u.id = :user_id LIMIT 1
    ');
    $stmt_user->execute([':user_id' => $user_id]);
    $usuario = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado']);
        exit;
    }

    // Obtener propiedades del usuario
    $stmt_props = $db->prepare('
        SELECT COUNT(*) as total_propiedades,
               GROUP_CONCAT(DISTINCT CONCAT(manzana, "-", villa) SEPARATOR ", ") as propiedades_list
        FROM propiedad 
        WHERE id_usuario = :user_id
    ');
    $stmt_props->execute([':user_id' => $user_id]);
    $propiedades = $stmt_props->fetch(PDO::FETCH_ASSOC);

    // Obtener estadísticas de CTG
    $stmt_ctg = $db->prepare('
        SELECT COUNT(*) as total_ctg,
               SUM(CASE WHEN estado_id = 3 THEN 1 ELSE 0 END) as ctg_resueltas,
               AVG(CASE WHEN estado_id = 3 AND fecha_resolucion IS NOT NULL 
                   THEN TIMESTAMPDIFF(HOUR, fecha_ingreso, fecha_resolucion) 
                   ELSE NULL END) as tiempo_promedio_resolucion_horas
        FROM ctg 
        WHERE id_usuario = :user_id
    ');
    $stmt_ctg->execute([':user_id' => $user_id]);
    $ctg_stats = $stmt_ctg->fetch(PDO::FETCH_ASSOC);

    // Obtener estadísticas de PQR
    $stmt_pqr = $db->prepare('
        SELECT COUNT(*) as total_pqr,
               SUM(CASE WHEN estado_id = 3 THEN 1 ELSE 0 END) as pqr_resueltas,
               AVG(CASE WHEN estado_id = 3 AND fecha_resolucion IS NOT NULL 
                   THEN TIMESTAMPDIFF(HOUR, fecha_ingreso, fecha_resolucion) 
                   ELSE NULL END) as tiempo_promedio_resolucion_horas
        FROM pqr 
        WHERE id_usuario = :user_id
    ');
    $stmt_pqr->execute([':user_id' => $user_id]);
    $pqr_stats = $stmt_pqr->fetch(PDO::FETCH_ASSOC);

    // Obtener estadísticas de citas
    $stmt_citas = $db->prepare('
        SELECT COUNT(*) as total_citas,
               SUM(CASE WHEN estado = "CONFIRMADO" THEN 1 ELSE 0 END) as citas_confirmadas,
               SUM(CASE WHEN estado = "CANCELADO" THEN 1 ELSE 0 END) as citas_canceladas
        FROM agendamiento_visitas 
        WHERE id_usuario = :user_id
    ');
    $stmt_citas->execute([':user_id' => $user_id]);
    $citas_stats = $stmt_citas->fetch(PDO::FETCH_ASSOC);

    // Obtener estadísticas de mensajes de chat
    $stmt_msgs = $db->prepare('
        SELECT COUNT(*) as total_mensajes
        FROM chat_message cm
        JOIN chat_thread ct ON ct.id = cm.thread_id
        WHERE ct.user_id = :user_id AND cm.sender_type = "user"
    ');
    $stmt_msgs->execute([':user_id' => $user_id]);
    $mensajes_stats = $stmt_msgs->fetch(PDO::FETCH_ASSOC);

    // Obtener actividad reciente (últimas 10 acciones)
    $actividad_reciente = [];
    
    // CTG recientes
    $stmt_ctg_recent = $db->prepare('
        SELECT "CTG" as tipo, CONCAT("CTG #", numero_solicitud) as descripcion, 
               fecha_ingreso as fecha_creacion, estado_id, 
               (SELECT nombre FROM estado_ctg WHERE id = ctg.estado_id) as estado
        FROM ctg 
        WHERE id_usuario = :user_id 
        ORDER BY fecha_ingreso DESC 
        LIMIT 5
    ');
    $stmt_ctg_recent->execute([':user_id' => $user_id]);
    $ctg_recientes = $stmt_ctg_recent->fetchAll(PDO::FETCH_ASSOC);
    
    // PQR recientes
    $stmt_pqr_recent = $db->prepare('
        SELECT "PQR" as tipo, CONCAT("PQR #", numero_solicitud) as descripcion, 
               fecha_ingreso as fecha_creacion, estado_id,
               (SELECT nombre FROM estado_pqr WHERE id = pqr.estado_id) as estado
        FROM pqr 
        WHERE id_usuario = :user_id 
        ORDER BY fecha_ingreso DESC 
        LIMIT 5
    ');
    $stmt_pqr_recent->execute([':user_id' => $user_id]);
    $pqr_recientes = $stmt_pqr_recent->fetchAll(PDO::FETCH_ASSOC);

    // Combinar y ordenar actividad reciente
    $actividad_reciente = array_merge($ctg_recientes, $pqr_recientes);
    usort($actividad_reciente, function($a, $b) {
        return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
    });
    $actividad_reciente = array_slice($actividad_reciente, 0, 10);

    // Preparar respuesta
    $response = [
        'ok' => true,
        'usuario' => $usuario,
        'estadisticas' => [
            'propiedades' => [
                'total_propiedades' => (int)$propiedades['total_propiedades'],
                'propiedades_list' => $propiedades['propiedades_list'] ?? ''
            ],
            'ctg' => [
                'total_ctg' => (int)$ctg_stats['total_ctg'],
                'ctg_resueltas' => (int)$ctg_stats['ctg_resueltas'],
                'tiempo_promedio_resolucion_horas' => $ctg_stats['tiempo_promedio_resolucion_horas'] ? 
                    round((float)$ctg_stats['tiempo_promedio_resolucion_horas'], 1) : null
            ],
            'pqr' => [
                'total_pqr' => (int)$pqr_stats['total_pqr'],
                'pqr_resueltas' => (int)$pqr_stats['pqr_resueltas'],
                'tiempo_promedio_resolucion_horas' => $pqr_stats['tiempo_promedio_resolucion_horas'] ? 
                    round((float)$pqr_stats['tiempo_promedio_resolucion_horas'], 1) : null
            ],
            'citas' => [
                'total_citas' => (int)$citas_stats['total_citas'],
                'citas_confirmadas' => (int)$citas_stats['citas_confirmadas'],
                'citas_canceladas' => (int)$citas_stats['citas_canceladas']
            ],
            'mensajes' => [
                'total_mensajes' => (int)$mensajes_stats['total_mensajes']
            ]
        ],
        'actividad_reciente' => [
            'ctg' => array_filter($actividad_reciente, function($item) { return $item['tipo'] === 'CTG'; }),
            'pqr' => array_filter($actividad_reciente, function($item) { return $item['tipo'] === 'PQR'; })
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log('chat/perfil/perfil.php error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
}