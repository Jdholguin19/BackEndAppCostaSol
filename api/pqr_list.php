<?php
/**
 *  GET /api/pqr_list.php[&estado_id=…]
 *  // Ya no se usa id_usuario en el GET para seguridad
 *  Respuesta: { ok:true, pqr:[ { id, numero, tipo, subtipo, estado,
 *                               descripcion, fecha_ingreso, n_respuestas, manzana, villa } ] }
 */
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$estadoId = isset($_GET['estado_id']) ? (int)$_GET['estado_id'] : 0;

// --- Lógica de Autenticación --- //
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
list($tokenType, $token) = explode(' ', $authHeader, 2);

$authenticated_user = null;
$is_responsable = false;

if ($tokenType === 'Bearer' && $token) {
    $db = DB::getDB(); // Usar la conexión para autenticar
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

if (!$authenticated_user) {
    http_response_code(401); // No autorizado
    exit(json_encode(['ok' => false, 'mensaje' => 'No autenticado o token inválido']));
}

// --- Fin Lógica de Autenticación --- //

try{
    $db = DB::getDB(); // Reutilizar la conexión de la autenticación

   $sql = 'SELECT  p.id,
                p.numero_solicitud   AS numero,
                tp.nombre            AS tipo,
                sp.nombre            AS subtipo,
                ep.nombre            AS estado,
                p.descripcion,
                p.fecha_ingreso,
                p.url_problema,                    -- miniatura
                pr.manzana,                        -- Mz
                pr.villa,                          -- Villa
                ( SELECT COUNT(*) 
                    FROM respuesta_pqr r 
                   WHERE r.pqr_id = p.id )        AS n_respuestas
        FROM    pqr p
        JOIN    tipo_pqr     tp ON tp.id = p.tipo_id
        JOIN    subtipo_pqr  sp ON sp.id = p.subtipo_id
        JOIN    estado_pqr   ep ON ep.id = p.estado_id
        JOIN    propiedad    pr ON pr.id = p.id_propiedad';

    $conditions = [];
    $params = [];

    if ($is_responsable) {
        // Responsable: Ver PQRs asignados a él
        // Asumo que la tabla pqr tiene una columna responsable_id para el responsable asignado
        // Si el nombre de la columna es diferente, ajústalo aquí.
        $conditions[] = 'p.responsable_id = :responsable_id'; // <--- Ajusta 'p.responsable_id' si el nombre es diferente
        $params[':responsable_id'] = $authenticated_user['id'];
    } else {
        // Usuario regular: Ver solo sus propios PQRs
        $conditions[] = 'p.id_usuario = :user_id';
        $params[':user_id'] = $authenticated_user['id'];
    }

    // Añadir filtro por estado si existe
    if($estadoId){
        $conditions[] = 'p.estado_id = :eid';
        $params[':eid'] = $estadoId;
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY p.fecha_ingreso DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['ok'=>true,'pqr'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);

}catch(Throwable $e){
    error_log('pqr_list: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
