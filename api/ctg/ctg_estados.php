<?php
// Devuelve todos los estados (id, nombre) para las pestañas
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $estados = DB::getDB()
        ->query('SELECT id, nombre FROM estado_ctg ORDER BY id')
        ->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok'=>true, 'estados'=>$estados]);
} catch(Throwable $e){
    error_log('ctg_estados: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
