<?php
/**
 *  GET /api/ctg_list.php[&estado_id=…][&order_by=…]
 *  order_by: 'fecha' (default) or 'urgencia'
 *  Respuesta: { ok:true, ctg:[ { id, numero, tipo, subtipo, estado,
 *                               descripcion, fecha_ingreso, n_respuestas, manzana, villa,
 *                               urgencia_id, urgencia } ] } // Axcc3xb1adimos urgencia_id y urgencia
 */
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$estadoId = isset($_GET['estado_id']) ? (int)$_GET['estado_id'] : 0;
$orderBy = $_GET['order_by'] ?? 'fecha'; // Nuevo parxc3xa1metro de ordenacixc3xb3n, por defecto 'fecha'

// --- Lxcc3xb3gica de Autenticacixc3xb3n ---
// Asegxc3xbbrate de que esta lxcc3xb3gica es la misma que tienes actualmente
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
list($tokenType, $token) = explode(' ', $authHeader, 2);

$authenticated_user = null;
$is_responsable = false;

if ($tokenType === 'Bearer' && $token) {
    $db = DB::getDB(); // Usar la conexixc3xb3n para autenticar
    // Buscar en tabla 'usuario'
    $sql_user = 'SELECT id, nombres, rol_id FROM usuario WHERE token = :token LIMIT 1';
    $stmt_user = $db->prepare($sql_user);
    $stmt_user->execute([':token' => $token]);
    $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($authenticated_user) {
        $is_responsable = false;
    } else {
        // Buscar en tabla 'responsable'
        $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
        $stmt_resp = $db->prepare($sql_resp);
        $stmt_resp->execute([':token' => $token]);
        $authenticated_user = $stmt_resp->fetch(PDO::FETCH_ASSOC);
        if ($authenticated_user) {
            $is_responsable = true;
        }
    }
}

$is_admin_responsible_user = false;
if ($authenticated_user && $is_responsable && $authenticated_user['id'] == 3) {
    $is_admin_responsible_user = true;
}

if (!$authenticated_user) {
    http_response_code(401); // No autorizado
    exit(json_encode(['ok' => false, 'mensaje' => 'No autenticado o token invxc3xa1lido']));
}
// --- Fin Lxcc3xb3gica de Autenticacixc3xb3n ---


try{
    $db = DB::getDB(); // Reutilizar la conexixc3xb3n de la autenticacixc3xb3n

   $sql = 'SELECT  p.id,
                p.numero_solicitud   AS numero,
                tp.nombre            AS tipo,
                sp.nombre            AS subtipo,
                ep.nombre            AS estado,
                p.descripcion,
                COALESCE(
                    (SELECT MAX(r.fecha_respuesta) FROM respuesta_ctg r WHERE r.ctg_id = p.id),
                    p.fecha_ingreso
                ) AS fecha_ingreso,
                p.url_problema,                    -- miniatura
                pr.manzana,                        -- Mz
                pr.villa,                          -- Villa
                p.urgencia_id,                       -- Axcc3xb1adimos ID de urgencia
                up.nombre            AS urgencia,   -- Axcc3xb1adimos nombre de urgencia
                ( SELECT COUNT(*) 
                    FROM respuesta_ctg r 
                   WHERE r.ctg_id = p.id )        AS n_respuestas
        FROM    ctg p
        JOIN    tipo_ctg     tp ON tp.id = p.tipo_id
        LEFT JOIN    subtipo_ctg  sp ON sp.id = p.subtipo_id
        JOIN    estado_ctg   ep ON ep.id = p.estado_id
        LEFT JOIN propiedad    pr ON pr.id = p.id_propiedad
        JOIN    urgencia_ctg up ON up.id = p.urgencia_id'; // <-- JOIN a la tabla de urgencia

    $conditions = [];
    $params = [];

    if (!$is_admin_responsible_user) {
        if ($is_responsable) {
            // Responsable: Ver CTGs asignados a él
            $conditions[] = 'p.responsable_id = :responsable_id';
            $params[':responsable_id'] = $authenticated_user['id'];
        } else {
            // Usuario regular: Ver solo sus propios CTGs
            $conditions[] = 'p.id_usuario = :user_id';
            $params[':user_id'] = $authenticated_user['id'];
        }
    }

    // Axcc3xb1adir filtro por estado si existe
    if($estadoId){
        $conditions[] = 'p.estado_id = :eid';
        $params[':eid'] = $estadoId;
    }

    // Añadir filtro por ctg_id si existe
    $ctgId = isset($_GET['ctg_id']) ? (int)$_GET['ctg_id'] : 0;
    if($ctgId) {
        $conditions[] = 'p.id = :ctg_id';
        $params[':ctg_id'] = $ctgId;
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // --- Lxcc3xb3gica de Ordenacixc3xb3n ---
    $sql .= ' ORDER BY ';
    if ($orderBy === 'urgencia') {
        // Ordenar por urgencia (ID: 3 Alta, 2 Media, 1 Baja) y luego por fecha
        $sql .= 'p.urgencia_id DESC, p.fecha_ingreso DESC';
    } else { // Por defecto o si es 'fecha'
        $sql .= 'p.fecha_ingreso DESC';
    }
    // --- Fin Lxcc3xb3gica de Ordenacixc3xb3n ---


    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['ok'=>true,'ctg'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);

}catch(Throwable $e){
    error_log('ctg_list: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'mensaje'=>'Error interno del servidor.']);
}
?>