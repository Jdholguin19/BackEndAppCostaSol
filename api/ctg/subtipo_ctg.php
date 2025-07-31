<?php
require_once __DIR__.'/../../config/db.php';

$id = (int)($_GET['tipo_id'] ?? 0);
$db = DB::getDB();

try {
    $stmt = $db->prepare(
        'SELECT id, nombre 
           FROM subtipo_ctg 
          WHERE tipo_id = ? 
       ORDER BY nombre'
    );
    $stmt->execute([$id]);

    echo json_encode([
        'ok'       => true,
        'subtipos' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Throwable $e) {
    error_log('subtipo_ctg: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false]);
}

