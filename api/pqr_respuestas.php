<?php
/**
 *  GET /api/pqr_respuestas.php?pqr_id=12
 *  → { ok:true, respuestas:[
 *        { id, mensaje, url_adjunto, fecha_respuesta,
 *          usuario_id, nombre, url_foto }
 *     ] }
 */
require_once __DIR__.'/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$pqrId = (int)($_GET['pqr_id'] ?? 0);
if(!$pqrId){
    http_response_code(400);
    exit(json_encode(['ok'=>false,'msg'=>'pqr_id requerido']));
}

try{
    $db = DB::getDB();
     $sql = 'SELECT  r.id,
                r.mensaje,
                r.url_adjunto,
                r.fecha_respuesta,
                r.usuario_id,                -- 👈  devuelve tal cual
                r.responsable_id,            -- 👈  idem
                COALESCE(u.nombres , resp.nombre)          AS nombre,
                COALESCE(u.url_foto_perfil , resp.url_foto_perfil) AS url_foto
        FROM    respuesta_pqr r
        LEFT JOIN usuario     u    ON u.id    = r.usuario_id
        LEFT JOIN responsable resp ON resp.id = r.responsable_id
        WHERE   r.pqr_id = :pid
        ORDER BY r.fecha_respuesta ASC';


    $st  = $db->prepare($sql);
    $st->execute([':pid'=>$pqrId]);
    echo json_encode(['ok'=>true,'respuestas'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
}catch(Throwable $e){
    error_log('pqr_respuestas: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
