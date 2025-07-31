<?php
require_once __DIR__.'/../config/db.php';
$db = DB::getDB();
echo json_encode(['ok'=>true,
    'tipos'=>$db->query('SELECT id,nombre FROM tipo_ctg ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC)
]);
