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

// Solo responsables pueden ver reportes generales
if (!$authenticated_user_id || $authenticated_user_type !== 'responsable') {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'mensaje' => 'Acceso denegado. Se requiere autenticación de responsable.']));
}

try {
    if ($method === 'GET') {
        
        // === MÉTRICAS GENERALES DEL SISTEMA ===
        
        // Total de usuarios por rol
        $stmt_usuarios_rol = $db->prepare('
            SELECT r.nombre as rol, COUNT(u.id) as total
            FROM rol r
            LEFT JOIN usuario u ON r.id = u.rol_id
            GROUP BY r.id, r.nombre
            ORDER BY total DESC
        ');
        $stmt_usuarios_rol->execute();
        $usuarios_por_rol = $stmt_usuarios_rol->fetchAll(PDO::FETCH_ASSOC);

        // Total de propiedades por estado
        $stmt_propiedades_estado = $db->prepare('
            SELECT ep.nombre as estado, COUNT(p.id) as total
            FROM estado_propiedad ep
            LEFT JOIN propiedad p ON ep.id = p.estado_id
            GROUP BY ep.id, ep.nombre
            ORDER BY total DESC
        ');
        $stmt_propiedades_estado->execute();
        $propiedades_por_estado = $stmt_propiedades_estado->fetchAll(PDO::FETCH_ASSOC);

        // Total de propiedades por urbanización
        $stmt_propiedades_urbanizacion = $db->prepare('
            SELECT u.nombre as urbanizacion, COUNT(p.id) as total
            FROM urbanizacion u
            LEFT JOIN propiedad p ON u.id = p.id_urbanizacion
            WHERE u.estado = 1
            GROUP BY u.id, u.nombre
            ORDER BY total DESC
        ');
        $stmt_propiedades_urbanizacion->execute();
        $propiedades_por_urbanizacion = $stmt_propiedades_urbanizacion->fetchAll(PDO::FETCH_ASSOC);

        // === MÉTRICAS DE CTG ===
        $stmt_ctg_general = $db->prepare('
            SELECT 
                COUNT(*) as total_ctg,
                COUNT(CASE WHEN estado_id = 1 THEN 1 END) as pendientes,
                COUNT(CASE WHEN estado_id = 2 THEN 1 END) as en_proceso,
                COUNT(CASE WHEN estado_id = 3 THEN 1 END) as resueltas,
                COUNT(CASE WHEN urgencia_id = 1 THEN 1 END) as alta_urgencia,
                COUNT(CASE WHEN urgencia_id = 2 THEN 1 END) as baja_urgencia
            FROM ctg
        ');
        $stmt_ctg_general->execute();
        $ctg_general = $stmt_ctg_general->fetch(PDO::FETCH_ASSOC);

        // CTG por tipo
        $stmt_ctg_tipo = $db->prepare('
            SELECT tc.nombre as tipo, COUNT(c.id) as total
            FROM tipo_ctg tc
            LEFT JOIN ctg c ON tc.id = c.tipo_id
            GROUP BY tc.id, tc.nombre
            ORDER BY total DESC
        ');
        $stmt_ctg_tipo->execute();
        $ctg_por_tipo = $stmt_ctg_tipo->fetchAll(PDO::FETCH_ASSOC);

        // === MÉTRICAS DE PQR ===
        $stmt_pqr_general = $db->prepare('
            SELECT 
                COUNT(*) as total_pqr,
                COUNT(CASE WHEN estado_id = 1 THEN 1 END) as pendientes,
                COUNT(CASE WHEN estado_id = 2 THEN 1 END) as resueltas
            FROM pqr
        ');
        $stmt_pqr_general->execute();
        $pqr_general = $stmt_pqr_general->fetch(PDO::FETCH_ASSOC);

        // PQR por tipo
        $stmt_pqr_tipo = $db->prepare('
            SELECT tp.nombre as tipo, COUNT(p.id) as total
            FROM tipo_pqr tp
            LEFT JOIN pqr p ON tp.id = p.tipo_id
            GROUP BY tp.id, tp.nombre
            ORDER BY total DESC
        ');
        $stmt_pqr_tipo->execute();
        $pqr_por_tipo = $stmt_pqr_tipo->fetchAll(PDO::FETCH_ASSOC);

        // === MÉTRICAS DE CITAS ===
        $stmt_citas_general = $db->prepare('
            SELECT 
                COUNT(*) as total_citas,
                COUNT(CASE WHEN estado = "PROGRAMADO" THEN 1 END) as programadas,
                COUNT(CASE WHEN estado = "CONFIRMADO" THEN 1 END) as confirmadas,
                COUNT(CASE WHEN estado = "CANCELADO" THEN 1 END) as canceladas
            FROM agendamiento_visitas
        ');
        $stmt_citas_general->execute();
        $citas_general = $stmt_citas_general->fetch(PDO::FETCH_ASSOC);

        // === MÉTRICAS DE LOGIN ===
        $stmt_login_general = $db->prepare('
            SELECT 
                COUNT(*) as total_logins,
                COUNT(CASE WHEN estado_login = "EXITO" THEN 1 END) as exitosos,
                COUNT(CASE WHEN estado_login = "FALLIDO" THEN 1 END) as fallidos,
                COUNT(DISTINCT id_usuario) as usuarios_unicos,
                COUNT(DISTINCT id_responsable) as responsables_unicos
            FROM registro_login
            WHERE fecha_ingreso >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ');
        $stmt_login_general->execute();
        $login_general = $stmt_login_general->fetch(PDO::FETCH_ASSOC);

        // === ACTIVIDAD RECIENTE (Últimos 30 días) ===
        $stmt_actividad_reciente = $db->prepare('
            SELECT 
                action,
                COUNT(*) as total,
                DATE(timestamp) as fecha
            FROM audit_log 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY action, DATE(timestamp)
            ORDER BY fecha DESC, total DESC
            LIMIT 50
        ');
        $stmt_actividad_reciente->execute();
        $actividad_reciente = $stmt_actividad_reciente->fetchAll(PDO::FETCH_ASSOC);

        // === USUARIOS MÁS ACTIVOS ===
        $stmt_usuarios_activos = $db->prepare('
            SELECT 
                al.user_id,
                al.user_type,
                CASE 
                    WHEN al.user_type = "usuario" THEN u.nombres
                    WHEN al.user_type = "responsable" THEN r.nombre
                    ELSE "Sistema"
                END as nombre_usuario,
                COUNT(*) as total_acciones
            FROM audit_log al
            LEFT JOIN usuario u ON al.user_id = u.id AND al.user_type = "usuario"
            LEFT JOIN responsable r ON al.user_id = r.id AND al.user_type = "responsable"
            WHERE al.timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND al.user_id IS NOT NULL
            GROUP BY al.user_id, al.user_type
            ORDER BY total_acciones DESC
            LIMIT 10
        ');
        $stmt_usuarios_activos->execute();
        $usuarios_activos = $stmt_usuarios_activos->fetchAll(PDO::FETCH_ASSOC);

        // === MÓDULOS MÁS ACCEDIDOS ===
        $stmt_modulos_acceso = $db->prepare('
            SELECT 
                action,
                COUNT(*) as total_accesos,
                COUNT(DISTINCT user_id) as usuarios_unicos
            FROM audit_log 
            WHERE action LIKE "ACCESS_MODULE%"
            AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY action
            ORDER BY total_accesos DESC
            LIMIT 10
        ');
        $stmt_modulos_acceso->execute();
        $modulos_acceso = $stmt_modulos_acceso->fetchAll(PDO::FETCH_ASSOC);

        // === ESTADÍSTICAS TEMPORALES ===
        
        // Actividad por día (últimos 30 días)
        $stmt_actividad_diaria = $db->prepare('
            SELECT 
                DATE(timestamp) as fecha,
                COUNT(*) as total_acciones,
                COUNT(DISTINCT user_id) as usuarios_unicos
            FROM audit_log 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(timestamp)
            ORDER BY fecha DESC
        ');
        $stmt_actividad_diaria->execute();
        $actividad_diaria = $stmt_actividad_diaria->fetchAll(PDO::FETCH_ASSOC);

        // Acciones por hora del día
        $stmt_actividad_horaria = $db->prepare('
            SELECT 
                HOUR(timestamp) as hora,
                COUNT(*) as total_acciones
            FROM audit_log 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY HOUR(timestamp)
            ORDER BY hora
        ');
        $stmt_actividad_horaria->execute();
        $actividad_horaria = $stmt_actividad_horaria->fetchAll(PDO::FETCH_ASSOC);

        // === MÉTRICAS FINANCIERAS ===
        
        // Paquetes adicionales más populares
        $stmt_paquetes_populares = $db->prepare('
            SELECT 
                pa.nombre,
                pa.precio,
                COUNT(ppa.id) as total_selecciones,
                SUM(pa.precio) as ingresos_totales
            FROM paquetes_adicionales pa
            LEFT JOIN propiedad_paquetes_adicionales ppa ON pa.id = ppa.paquete_id
            WHERE pa.activo = 1
            GROUP BY pa.id, pa.nombre, pa.precio
            ORDER BY total_selecciones DESC
        ');
        $stmt_paquetes_populares->execute();
        $paquetes_populares = $stmt_paquetes_populares->fetchAll(PDO::FETCH_ASSOC);

        // Kits de acabados más seleccionados
        $stmt_kits_populares = $db->prepare('
            SELECT 
                ak.nombre,
                ak.costo,
                COUNT(p.id) as total_selecciones,
                SUM(ak.costo) as ingresos_totales
            FROM acabado_kit ak
            LEFT JOIN propiedad p ON ak.id = p.acabado_kit_seleccionado_id
            GROUP BY ak.id, ak.nombre, ak.costo
            ORDER BY total_selecciones DESC
        ');
        $stmt_kits_populares->execute();
        $kits_populares = $stmt_kits_populares->fetchAll(PDO::FETCH_ASSOC);

        // === TIEMPOS DE RESOLUCIÓN ===
        $stmt_tiempos_resolucion = $db->prepare('
            SELECT 
                "CTG" as tipo,
                AVG(TIMESTAMPDIFF(HOUR, fecha_ingreso, fecha_resolucion)) as tiempo_promedio_horas,
                MIN(TIMESTAMPDIFF(HOUR, fecha_ingreso, fecha_resolucion)) as tiempo_minimo_horas,
                MAX(TIMESTAMPDIFF(HOUR, fecha_ingreso, fecha_resolucion)) as tiempo_maximo_horas,
                COUNT(CASE WHEN fecha_resolucion IS NOT NULL THEN 1 END) as total_resueltas
            FROM ctg
            WHERE fecha_resolucion IS NOT NULL
            
            UNION ALL
            
            SELECT 
                "PQR" as tipo,
                AVG(TIMESTAMPDIFF(HOUR, fecha_ingreso, fecha_resolucion)) as tiempo_promedio_horas,
                MIN(TIMESTAMPDIFF(HOUR, fecha_ingreso, fecha_resolucion)) as tiempo_minimo_horas,
                MAX(TIMESTAMPDIFF(HOUR, fecha_ingreso, fecha_resolucion)) as tiempo_maximo_horas,
                COUNT(CASE WHEN fecha_resolucion IS NOT NULL THEN 1 END) as total_resueltas
            FROM pqr
            WHERE fecha_resolucion IS NOT NULL
        ');
        $stmt_tiempos_resolucion->execute();
        $tiempos_resolucion = $stmt_tiempos_resolucion->fetchAll(PDO::FETCH_ASSOC);

        // === COMPILAR RESULTADO ===
        $reporte_general = [
            'ok' => true,
            'metricas_generales' => [
                'usuarios_por_rol' => $usuarios_por_rol,
                'propiedades_por_estado' => $propiedades_por_estado,
                'propiedades_por_urbanizacion' => $propiedades_por_urbanizacion,
                'ctg' => $ctg_general,
                'ctg_por_tipo' => $ctg_por_tipo,
                'pqr' => $pqr_general,
                'pqr_por_tipo' => $pqr_por_tipo,
                'citas' => $citas_general,
                'login' => $login_general,
                'tiempos_resolucion' => $tiempos_resolucion
            ],
            'analisis_actividad' => [
                'actividad_reciente' => $actividad_reciente,
                'usuarios_mas_activos' => $usuarios_activos,
                'modulos_mas_accedidos' => $modulos_acceso,
                'actividad_diaria' => $actividad_diaria,
                'actividad_horaria' => $actividad_horaria
            ],
            'metricas_financieras' => [
                'paquetes_populares' => $paquetes_populares,
                'kits_populares' => $kits_populares
            ],
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'periodo_analisis' => 'Últimos 30 días'
        ];

        echo json_encode($reporte_general, JSON_UNESCAPED_UNICODE);

    } else {
        http_response_code(405);
        echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
    }

} catch (Exception $e) {
    error_log("Error en general_report_data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
?>
