<?php
/**
 * GET ?proposito_id=…
 * Devuelve los próximos 14 días con cupo disponible
 */
require_once __DIR__.'/../../config/db.php';
 header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');


header('Content-Type: application/json; charset=utf-8');

$prop = (int)($_GET['proposito_id']??0);
if(!$prop){ http_response_code(400); exit(json_encode(['ok'=>false])); }

try{
$db = DB::getDB();
$sql = "
SELECT
  d.fecha,
  SUM(
    CEIL(
      TIMESTAMPDIFF(MINUTE, s.hora_inicio, s.hora_fin) / s.intervalo_minutos
    )
  ) - COUNT(v.id) AS libres
FROM (
  /* 1 → 14 = mañana hasta dentro de 14 días */
  SELECT CURDATE() + INTERVAL n DAY AS fecha
  FROM (
    SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
    UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
    UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
    UNION ALL SELECT 13 UNION ALL SELECT 14
  ) numeros
  WHERE WEEKDAY(CURDATE() + INTERVAL n DAY) BETWEEN 0 AND 4  -- lun-vie
) d
JOIN responsable r
       ON r.estado = 1
JOIN responsable_disponibilidad s
       ON s.responsable_id = r.id
      AND s.activo = 1
      AND d.fecha BETWEEN s.fecha_vigencia_desde
                      AND IFNULL(s.fecha_vigencia_hasta,'2999-12-31')
      AND s.dia_semana = WEEKDAY(d.fecha) + 1
LEFT JOIN agendamiento_visitas v
       ON v.responsable_id = r.id
      AND v.fecha_reunion = d.fecha
      AND v.estado <> 'CANCELADO'
GROUP BY d.fecha
HAVING libres > 0
ORDER BY d.fecha;

";
$rows=$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['ok'=>true,'items'=>$rows]);
}catch(Throwable $e){
 http_response_code(500); echo json_encode(['ok'=>false]);
}
