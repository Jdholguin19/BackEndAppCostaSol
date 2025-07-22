<?php
/**
 *  GET /api/pqr_list.php?id_usuario=2[&estado_id=…]
 *  Respuesta: { ok:true, pqr:[ { id, numero, tipo, subtipo, estado,
 *                               descripcion, fecha_ingreso, n_respuestas } ] }
 */
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$idUser   = (int)($_GET['id_usuario'] ?? 0);
$estadoId = isset($_GET['estado_id']) ? (int)$_GET['estado_id'] : 0;
if(!$idUser){ http_response_code(400); exit(json_encode(['ok'=>false,'msg'=>'id_usuario requerido'])); }

try{
    $db = DB::getDB();

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
        JOIN    propiedad    pr ON pr.id = p.id_propiedad   -- NUEVA unión
        WHERE   p.id_usuario = :uid';

    $params = [':uid'=>$idUser];

    if($estadoId){
        $sql .= ' AND p.estado_id = :eid';
        $params[':eid'] = $estadoId;
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
