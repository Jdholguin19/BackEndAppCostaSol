<?php
require_once __DIR__.'/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try{
    $db   = DB::getDB();
    $rows = $db->query("SELECT id,proposito,url_icono
                        FROM proposito_agendamiento
                        WHERE estado = 1
                        ORDER BY id")
               ->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok'=>true,'items'=>$rows]);
}catch(Throwable $e){
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}

