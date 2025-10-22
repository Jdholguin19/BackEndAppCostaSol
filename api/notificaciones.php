<?php
/*  GET /api/notificaciones.php
 *  Requiere token en header Authorization: Bearer <token>
 *  Muestra notificaciones de respuestas: Responsables asignados a CTGs/PQRs de Clientes (para Clientes),
 *  Clientes a CTGs/PQRs (para Responsables).
 *
 *  Respuesta:
 *      {
 *        ok:true,
 *        notificaciones: [
 *           { solicitud_id:1, tipo_solicitud:"CTG", mensaje:"...", usuario:"...", fecha_respuesta:"...", url_adjunto:"...", manzana: "...", villa: "..." },
 *           { solicitud_id:10, tipo_solicitud:"PQR", mensaje:"...", usuario:"...", fecha_respuesta:"...", url_adjunto:"...", manzana: "...", villa: "..." },
 *           ...
 *        ]
 *      }
 */

 require_once __DIR__.'/../config/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

$db = DB::getDB();

// --- Lógica de Autenticación --- //
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
list($tokenType, $token) = explode(' ', $authHeader, 2);

$authenticated_user = null;
$is_responsable = false;

if ($tokenType === 'Bearer' && $token) {
    // Buscar en tabla 'usuario'
    $sql_user = 'SELECT id, nombres, rol_id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($authenticated_user) {
        $is_responsable = false; // Usuarios regulares no son responsables
    } else {
        // Buscar en tabla 'responsable'
        $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
        $stmt_resp = $db->prepare($sql_resp);
        $stmt_resp->execute([':token' => $token]);
        $authenticated_user = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($authenticated_user) {
            $is_responsable = true; // Es un responsable
        }
    }
}

if (!$authenticated_user) {
    http_response_code(401); // No autorizado si no se autentica
    exit(json_encode(['ok' => false, 'mensaje' => 'No autenticado o token inválido']));
}

// --- Fin Lógica de Autenticación --- //


try {
    $conditions_ctg = [];
    $conditions_pqr = [];
    $conditions_citas = [];
    $params = [];

    if ($is_responsable) {
        $responsable_id = $authenticated_user['id'];
        // Notificaciones de CTG: respuestas de clientes a mis CTGs
        $conditions_ctg[] = 'rp.usuario_id IS NOT NULL';
        $conditions_ctg[] = 'p.responsable_id = :responsable_id';
        $params[':responsable_id'] = $responsable_id;

        // Notificaciones de PQR: respuestas de clientes a mis PQRs
        $conditions_pqr[] = 'rpq.usuario_id IS NOT NULL';
        $conditions_pqr[] = 'pq.responsable_id = :responsable_id_pqr';
        $params[':responsable_id_pqr'] = $responsable_id;

        // Notificaciones de Citas: nuevas citas asignadas a mí que no he leído
        $conditions_citas[] = 'a.responsable_id = :responsable_id_cita';
        $conditions_citas[] = 'a.leido = 0';
        $params[':responsable_id_cita'] = $responsable_id;

    } else {
        $user_id = $authenticated_user['id'];
        // Notificaciones de CTG: respuestas de responsables a mis CTGs
        $conditions_ctg[] = 'rp.responsable_id IS NOT NULL';
        $conditions_ctg[] = 'p.id_usuario = :user_id';
        $params[':user_id'] = $user_id;

        // Notificaciones de PQR: respuestas de responsables a mis PQRs
        $conditions_pqr[] = 'rpq.responsable_id IS NOT NULL';
        $conditions_pqr[] = 'pq.id_usuario = :user_id_pqr';
        $params[':user_id_pqr'] = $user_id;
        
        // Los clientes no reciben notificaciones de citas en esta vista
        $conditions_citas[] = '1=0'; 
    }

    // --- Consulta para CTG ---
    $sql_ctg = "SELECT rp.id AS id, rp.ctg_id AS solicitud_id, CONVERT('CTG' USING utf8mb4) AS tipo_solicitud, CONVERT(rp.mensaje USING utf8mb4) AS mensaje, CONVERT(COALESCE(u.nombres , resp.nombre) USING utf8mb4) AS usuario, rp.fecha_respuesta, CONVERT(rp.url_adjunto USING utf8mb4) AS url_adjunto, CONVERT(pr.manzana USING utf8mb4) AS manzana, CONVERT(pr.villa USING utf8mb4) AS villa, rp.leido FROM respuesta_ctg rp LEFT JOIN usuario u ON rp.usuario_id = u.id LEFT JOIN responsable resp ON rp.responsable_id = resp.id JOIN ctg p ON rp.ctg_id = p.id JOIN propiedad pr ON p.id_propiedad = pr.id";
    if (!empty($conditions_ctg)) {
        $sql_ctg .= ' WHERE ' . implode(' AND ', $conditions_ctg);
    }

    // --- Consulta para PQR ---
    $sql_pqr = "SELECT rpq.id AS id, rpq.pqr_id AS solicitud_id, CONVERT('PQR' USING utf8mb4) AS tipo_solicitud, CONVERT(rpq.mensaje USING utf8mb4) AS mensaje, CONVERT(COALESCE(us.nombres , respn.nombre) USING utf8mb4) AS usuario, rpq.fecha_respuesta, CONVERT(rpq.url_adjunto USING utf8mb4) AS url_adjunto, CONVERT(prp.manzana USING utf8mb4) AS manzana, CONVERT(prp.villa USING utf8mb4) AS villa, rpq.leido FROM respuesta_pqr rpq LEFT JOIN usuario us ON rpq.usuario_id = us.id LEFT JOIN responsable respn ON rpq.responsable_id = respn.id JOIN pqr pq ON rpq.pqr_id = pq.id JOIN propiedad prp ON pq.id_propiedad = prp.id";
    if (!empty($conditions_pqr)) {
        $sql_pqr .= ' WHERE ' . implode(' AND ', $conditions_pqr);
    }

    // --- Consulta para Citas ---
    $sql_citas = "SELECT a.id AS id, a.id AS solicitud_id, CONVERT('Cita' USING utf8mb4) AS tipo_solicitud, CONVERT(pa.proposito USING utf8mb4) AS mensaje, CONVERT(u.nombres USING utf8mb4) AS usuario, a.fecha_ingreso AS fecha_respuesta, NULL AS url_adjunto, CONVERT(pr.manzana USING utf8mb4) AS manzana, CONVERT(pr.villa USING utf8mb4) AS villa, a.leido FROM agendamiento_visitas a JOIN usuario u ON a.id_usuario = u.id JOIN propiedad pr ON a.id_propiedad = pr.id JOIN proposito_agendamiento pa ON a.proposito_id = pa.id";
    if (!empty($conditions_citas)) {
        $sql_citas .= ' WHERE ' . implode(' AND ', $conditions_citas);
    }

    // --- Consulta para Noticias ---
    $user_id = $authenticated_user['id'] ?? null;
    $sql_noticias = "SELECT notif.id, n.id AS solicitud_id, CONVERT('Noticia' USING utf8mb4) AS tipo_solicitud, CONVERT(n.titulo USING utf8mb4) AS mensaje, CONVERT('Sistema' USING utf8mb4) AS usuario, n.fecha_publicacion AS fecha_respuesta, CONVERT(n.url_imagen USING utf8mb4) AS url_adjunto, NULL AS manzana, NULL AS villa, notif.leido FROM noticia n";
    
    if ($user_id) {
        // Solo mostrar noticias que el usuario ha recibido (existen en la tabla notificacion para él)
        $sql_noticias .= " JOIN notificacion notif ON n.id = notif.tipo_id AND notif.tipo = 'Noticia' WHERE notif.usuario_id = :user_id_noticia";
        $params[':user_id_noticia'] = $user_id;
    } else {
        // Si es responsable, mostrar todas las noticias publicadas
        $sql_noticias .= " LEFT JOIN notificacion notif ON n.id = notif.tipo_id AND notif.tipo = 'Noticia' WHERE n.estado = 1";
    }

    // --- Combinar las 4 consultas ---
    $sql = "($sql_ctg) UNION ALL ($sql_pqr) UNION ALL ($sql_citas) UNION ALL ($sql_noticias) ORDER BY fecha_respuesta DESC LIMIT 20";

    $stmt = $db->prepare($sql);

    // Ejecutar con los parámetros
    $stmt->execute($params);

    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'notificaciones' => $notificaciones]);

} catch (Throwable $e) {
    error_log('notificaciones.php: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error interno']);
}