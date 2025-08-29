<?php
/**
 * GET /api/cita/citas_list.php?id_usuario=…
 * ▸ Devuelve todas las citas del usuario (próximas y pasadas)
 *   {
 *     ok   : true,
 *     citas: [
 *        { id, proposito, fecha, hora, estado,
 *          responsable, url_foto, proyecto, manzana, villa }
 *     ]
 *   }
 */
require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');


$rol = $_GET['rol'] ?? '';
$whereClause = '';
$params = [];

switch ($rol) {
    case 'admin_responsable':
        // No WHERE clause needed for admin
        break;
    case 'responsable':
        $idResponsable = (int)($_GET['id_responsable'] ?? 0);
        
        if (!$idResponsable) {
            http_response_code(400);
            exit(json_encode(['ok' => false, 'message' => 'ID de responsable no proporcionado.']));
        }
        $whereClause = 'WHERE v.responsable_id = :id_responsable';
        $params[':id_responsable'] = $idResponsable;
        break;
    case 'usuario':
        $idUsuario = (int)($_GET['id_usuario'] ?? 0);
        
        if (!$idUsuario) {
            http_response_code(400);
            exit(json_encode(['ok' => false, 'message' => 'ID de usuario no proporcionado.']));
        }
        $whereClause = 'WHERE v.id_usuario = :id_usuario';
        $params[':id_usuario'] = $idUsuario;
        break;
    default:
        http_response_code(400);
        exit(json_encode(['ok' => false, 'message' => 'Rol no válido o no proporcionado.']));
}


try {
    $db = DB::getDB();
    $sql = "
       SELECT
            v.id,
            v.responsable_id,
            v.observaciones,
            pa.proposito,
            v.fecha_reunion              AS fecha,
            TIME_FORMAT(v.hora_reunion, '%H:%i') AS hora,
            -- Usar la duración específica si existe, si no, la del responsable, con un fallback de 30
            COALESCE(v.duracion_minutos, rd.intervalo_minutos, 30) AS intervalo_minutos,
            v.estado,
            r.nombre                     AS responsable,
            r.url_foto_perfil            AS url_foto,
            CONCAT('Proyecto ', u.nombre,
                   ' - Mz ', pr.manzana,
                   ', Villa ', pr.villa) AS proyecto,
            pr.manzana,
            pr.villa
        FROM
            agendamiento_visitas       v
        JOIN
            proposito_agendamiento          pa ON pa.id = v.proposito_id
        JOIN
            responsable                     r  ON r.id  = v.responsable_id
        JOIN
            propiedad                       pr ON pr.id = v.id_propiedad
        JOIN
            urbanizacion                    u  ON u.id  = pr.id_urbanizacion
        LEFT JOIN 
            responsable_disponibilidad rd ON rd.responsable_id = v.responsable_id
            AND (WEEKDAY(v.fecha_reunion) + 1) = rd.dia_semana
            AND v.fecha_reunion BETWEEN rd.fecha_vigencia_desde AND IFNULL(rd.fecha_vigencia_hasta, '2999-12-31')

";

    
    $sql .= $whereClause . ' ORDER BY v.fecha_ingreso DESC;';
    $st = $db->prepare($sql);
    $st->execute($params);

    echo json_encode(['ok'=>true,
                      'citas'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    error_log('citas_list: Error: '.$e->getMessage() . '\nStack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['ok'=>false]);
}
