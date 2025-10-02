<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers/audit_helper.php';

header('Content-Type: application/json; charset=utf-8');

$db = DB::getDB();
$method = $_SERVER['REQUEST_METHOD'];

$authenticated_user_id = null;
$authenticated_user_type = null;

// --- Autenticación y Autorización --- //
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? '';

if (preg_match('/Bearer\s(.+)/', $auth_header, $matches)) {
    $token = $matches[1];

    // Buscar en tabla de responsables
    $stmt_responsable = $db->prepare("SELECT id FROM responsable WHERE token = :token");
    $stmt_responsable->execute([':token' => $token]);
    $responsable = $stmt_responsable->fetch(PDO::FETCH_ASSOC);

    if ($responsable) {
        $authenticated_user_id = $responsable['id'];
        $authenticated_user_type = 'responsable';
    }
}

// Solo responsables pueden ver reportes de usuarios
if (!$authenticated_user_id || $authenticated_user_type !== 'responsable') {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'mensaje' => 'Acceso denegado. Se requiere autenticación de responsable.']));
}

try {
    if ($method === 'GET') {
        $user_id = $_GET['user_id'] ?? null;
        
        if (!$user_id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'ID de usuario requerido']);
            exit;
        }

        // Obtener información básica del usuario
        $stmt_usuario = $db->prepare('
            SELECT u.id, u.nombres, u.apellidos, u.correo, u.cedula, u.telefono, 
                   u.url_foto_perfil, u.numero_propiedades, u.fecha_insertado,
                   r.nombre AS rol_nombre
            FROM usuario u 
            LEFT JOIN rol r ON u.rol_id = r.id 
            WHERE u.id = ?
        ');
        $stmt_usuario->execute([$user_id]);
        $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'mensaje' => 'Usuario no encontrado']);
            exit;
        }

        // === MÉTRICAS DE PROPIEDADES ===
        $stmt_propiedades = $db->prepare('
            SELECT COUNT(*) as total_propiedades,
                   COUNT(CASE WHEN estado_id = 1 THEN 1 END) as propiedades_activas,
                   COUNT(CASE WHEN estado_id = 2 THEN 1 END) as propiedades_entregadas,
                   COUNT(CASE WHEN estado_id = 3 THEN 1 END) as propiedades_en_construccion
            FROM propiedad 
            WHERE id_usuario = ?
        ');
        $stmt_propiedades->execute([$user_id]);
        $propiedades_stats = $stmt_propiedades->fetch(PDO::FETCH_ASSOC);

        // === MÉTRICAS DE CTG ===
        $stmt_ctg = $db->prepare('
            SELECT COUNT(*) as total_ctg,
                   COUNT(CASE WHEN estado_id = 1 THEN 1 END) as ctg_pendientes,
                   COUNT(CASE WHEN estado_id = 2 THEN 1 END) as ctg_en_proceso,
                   COUNT(CASE WHEN estado_id = 3 THEN 1 END) as ctg_resueltas,
                   COUNT(CASE WHEN urgencia_id = 1 THEN 1 END) as ctg_alta_urgencia,
                   COUNT(CASE WHEN urgencia_id = 2 THEN 1 END) as ctg_baja_urgencia
            FROM ctg 
            WHERE id_usuario = ?
        ');
        $stmt_ctg->execute([$user_id]);
        $ctg_stats = $stmt_ctg->fetch(PDO::FETCH_ASSOC);

        // === MÉTRICAS DE PQR ===
        $stmt_pqr = $db->prepare('
            SELECT COUNT(*) as total_pqr,
                   COUNT(CASE WHEN estado_id = 1 THEN 1 END) as pqr_pendientes,
                   COUNT(CASE WHEN estado_id = 2 THEN 1 END) as pqr_resueltas
            FROM pqr 
            WHERE id_usuario = ?
        ');
        $stmt_pqr->execute([$user_id]);
        $pqr_stats = $stmt_pqr->fetch(PDO::FETCH_ASSOC);

        // === MÉTRICAS DE CITAS ===
        $stmt_citas = $db->prepare('
            SELECT COUNT(*) as total_citas,
                   COUNT(CASE WHEN estado = "PROGRAMADO" THEN 1 END) as citas_programadas,
                   COUNT(CASE WHEN estado = "CONFIRMADO" THEN 1 END) as citas_confirmadas,
                   COUNT(CASE WHEN estado = "CANCELADO" THEN 1 END) as citas_canceladas
            FROM agendamiento_visitas 
            WHERE id_usuario = ?
        ');
        $stmt_citas->execute([$user_id]);
        $citas_stats = $stmt_citas->fetch(PDO::FETCH_ASSOC);

        // === MÉTRICAS DE MENSAJES ===
        $stmt_mensajes_ctg = $db->prepare('
            SELECT COUNT(*) as mensajes_ctg
            FROM respuesta_ctg rc
            JOIN ctg c ON rc.ctg_id = c.id
            WHERE c.id_usuario = ? AND rc.usuario_id = ?
        ');
        $stmt_mensajes_ctg->execute([$user_id, $user_id]);
        $mensajes_ctg = $stmt_mensajes_ctg->fetch(PDO::FETCH_ASSOC);

        $stmt_mensajes_pqr = $db->prepare('
            SELECT COUNT(*) as mensajes_pqr
            FROM respuesta_pqr rp
            JOIN pqr p ON rp.pqr_id = p.id
            WHERE p.id_usuario = ? AND rp.usuario_id = ?
        ');
        $stmt_mensajes_pqr->execute([$user_id, $user_id]);
        $mensajes_pqr = $stmt_mensajes_pqr->fetch(PDO::FETCH_ASSOC);

        // === HISTORIAL DE LOGIN ===
        $stmt_login = $db->prepare('
            SELECT COUNT(*) as total_logins,
                   COUNT(CASE WHEN estado_login = "EXITO" THEN 1 END) as logins_exitosos,
                   COUNT(CASE WHEN estado_login = "FALLIDO" THEN 1 END) as logins_fallidos,
                   MAX(fecha_ingreso) as ultimo_login
            FROM registro_login 
            WHERE id_usuario = ?
        ');
        $stmt_login->execute([$user_id]);
        $login_stats = $stmt_login->fetch(PDO::FETCH_ASSOC);

        // === ACTIVIDAD RECIENTE ===
        $stmt_actividad_reciente = $db->prepare('
            (SELECT "CTG" as tipo, id, fecha_ingreso as fecha, descripcion
             FROM ctg WHERE id_usuario = ? ORDER BY fecha_ingreso DESC LIMIT 5)
            UNION ALL
            (SELECT "PQR" as tipo, id, fecha_ingreso as fecha, descripcion
             FROM pqr WHERE id_usuario = ? ORDER BY fecha_ingreso DESC LIMIT 5)
            UNION ALL
            (SELECT "CITA" as tipo, id, fecha_ingreso as fecha, CONCAT("Cita programada para ", fecha_reunion) as descripcion
             FROM agendamiento_visitas WHERE id_usuario = ? ORDER BY fecha_ingreso DESC LIMIT 5)
            ORDER BY fecha DESC LIMIT 10
        ');
        $stmt_actividad_reciente->execute([$user_id, $user_id, $user_id]);
        $actividad_reciente = $stmt_actividad_reciente->fetchAll(PDO::FETCH_ASSOC);

        // === ESTADÍSTICAS DE TIEMPO ===
        $stmt_tiempo_promedio = $db->prepare('
            SELECT 
                AVG(CASE WHEN fecha_resolucion IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, fecha_ingreso, fecha_resolucion) 
                    END) as tiempo_promedio_resolucion_ctg_horas,
                AVG(CASE WHEN fecha_resolucion IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, fecha_ingreso, fecha_resolucion) 
                    END) as tiempo_promedio_resolucion_pqr_horas
            FROM (
                SELECT fecha_ingreso, fecha_resolucion FROM ctg WHERE id_usuario = ?
                UNION ALL
                SELECT fecha_ingreso, fecha_resolucion FROM pqr WHERE id_usuario = ?
            ) as combined
        ');
        $stmt_tiempo_promedio->execute([$user_id, $user_id]);
        $tiempo_stats = $stmt_tiempo_promedio->fetch(PDO::FETCH_ASSOC);

        // === COMPILAR RESULTADO ===
        $reporte = [
            'ok' => true,
            'usuario' => $usuario,
            'metricas' => [
                'propiedades' => $propiedades_stats,
                'ctg' => $ctg_stats,
                'pqr' => $pqr_stats,
                'citas' => $citas_stats,
                'mensajes' => [
                    'ctg' => $mensajes_ctg['mensajes_ctg'] ?? 0,
                    'pqr' => $mensajes_pqr['mensajes_pqr'] ?? 0,
                    'total' => ($mensajes_ctg['mensajes_ctg'] ?? 0) + ($mensajes_pqr['mensajes_pqr'] ?? 0)
                ],
                'login' => $login_stats,
                'tiempo_promedio' => $tiempo_stats
            ],
            'actividad_reciente' => $actividad_reciente,
            'fecha_generacion' => date('Y-m-d H:i:s')
        ];

        echo json_encode($reporte, JSON_UNESCAPED_UNICODE);

    } else {
        http_response_code(405);
        echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
    }

} catch (Exception $e) {
    error_log("Error en user_report_data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
?>
