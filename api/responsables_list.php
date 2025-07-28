<?php
/**
 * GET /api/responsables_list.php
 * → { ok:true, items:[ {id,nombre,url_foto} … ] }
 */
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = DB::getDB();
    $sql = "
      SELECT  r.id,
              r.nombre,
              COALESCE(r.url_foto_perfil,'') AS url_foto
      FROM    responsable r
      JOIN    responsable_disponibilidad d
                ON d.responsable_id = r.id
               AND d.activo = 1
               AND ( CURDATE() BETWEEN d.fecha_vigencia_desde
                                   AND IFNULL(d.fecha_vigencia_hasta,'2999-12-31') )
      WHERE   r.estado = 1
      GROUP BY r.id, r.nombre, r.url_foto_perfil
      ORDER BY r.nombre
    ";
    $items = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'items' => $items]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false]);
}
