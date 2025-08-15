<?php
/**
 *  GET /api/pqr_list.php[&estado_id=…][&order_by=…]
 *  order_by: 'fecha' (default)'
 *  Respuesta: { ok:true, pqr:[ { id, numero, tipo, estado,
 *                               descripcion, fecha_ingreso, n_respuestas, manzana, villa } ] } //
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
                tp.nombre,
                p.numero_solicitud   AS numero,
                tp.nombre            AS tipo,
                ep.nombre            AS estado,
                p.descripcion,
                COALESCE(
                    (SELECT MAX(r.fecha_respuesta) FROM respuesta_pqr r WHERE r.pqr_id = p.id),
                    p.fecha_ingreso
                ) AS fecha_ingreso,
                p.url_problema,                    -- miniatura
                pr.manzana,                        -- Mz
                pr.villa,                          -- Villa
                ( SELECT COUNT(*) 
                    FROM respuesta_pqr r 
                   WHERE r.pqr_id = p.id )        AS n_respuestas
        FROM    pqr p
        JOIN    tipo_pqr     tp ON tp.id = p.tipo_id
        JOIN    estado_pqr   ep ON ep.id = p.estado_id
        JOIN    propiedad    pr ON pr.id = p.id_propiedad'; 

    $conditions = [];
    $params = [];

    if (!$is_admin_responsible_user) {
        if ($is_responsable) {
            // Responsable: Ver PQRs asignados a él
            $conditions[] = 'p.responsable_id = :responsable_id';
            $params[':responsable_id'] = $authenticated_user['id'];
        } else {
            // Usuario regular: Ver solo sus propios PQRs
            $conditions[] = 'p.id_usuario = :user_id';
            $params[':user_id'] = $authenticated_user['id'];
        }
    }

    // Axcc3xb1adir filtro por estado si existe
    if($estadoId){
        $conditions[] = 'p.estado_id = :eid';
        $params[':eid'] = $estadoId;
    }

    // Añadir filtro por pqr_id si existe
    $pqrId = isset($_GET['pqr_id']) ? (int)$_GET['pqr_id'] : 0;
    if($pqrId) {
        $conditions[] = 'p.id = :pqr_id';
        $params[':pqr_id'] = $pqrId;
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Lógica de Ordenación
    $sql .= ' ORDER BY ';
    if ($orderBy === 'urgencia') {
        // Necesitamos unir con la tabla de urgencia si no está ya unida
        // Asumiendo que p.urgencia_id ya está disponible o se puede unir
        $sql .= 'p.urgencia_id DESC, p.fecha_ingreso DESC';
    } else { // Por defecto o si es 'fecha'
        $sql .= 'p.fecha_ingreso DESC';
    }


    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['ok'=>true,'pqr'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);

}catch(Throwable $e){
    error_log('pqr_list: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'mensaje'=>'Error interno del servidor.']);
}
?>