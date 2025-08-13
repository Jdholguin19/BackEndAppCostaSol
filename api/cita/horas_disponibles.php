<?php
/**
 * GET  ?proposito_id=…&fecha=YYYY-MM-DD
 * Devuelve array   [{responsable_id,hora}]
 */
require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$prop   = (int)($_GET['proposito_id']??0);
$fecha  = $_GET['fecha'] ?? '';
if(!$prop||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha)){
    http_response_code(400); exit(json_encode(['ok'=>false]));
}

try{
$db=DB::getDB();
$dia = (int)date('w', strtotime($fecha));   // 0 (domingo) - 6 (sábado)
if ($dia == 0) { // Sunday
    $dia = 7; // Map Sunday to 7, assuming no availability for Sunday in DB
}
$sql = "
SELECT TIME_FORMAT(s_hours.hora,'%H:%i') hora, MIN(r.id) as responsable_id
FROM (
  SELECT
    ADDTIME('09:00:00', SEC_TO_TIME(seq.n * 60 * 60)) AS hora
  FROM (
         SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
         UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7
       ) seq
  WHERE ADDTIME('09:00:00', SEC_TO_TIME(seq.n * 60 * 60)) BETWEEN '09:00:00' AND '16:00:00'
) AS s_hours
JOIN responsable r
LEFT JOIN responsable_disponibilidad d ON d.responsable_id = r.id
                                     AND d.activo = 1
                                     AND d.dia_semana = :dia
                                     AND :fecha BETWEEN d.fecha_vigencia_desde
                                                   AND IFNULL(d.fecha_vigencia_hasta, '2999-12-31')
LEFT JOIN agendamiento_visitas v
       ON v.responsable_id = r.id
      AND v.fecha_reunion = :fecha
      AND v.hora_reunion = s_hours.hora
      AND v.estado <> 'CANCELADO'
WHERE v.id IS NULL
  AND d.responsable_id IS NOT NULL
  AND TIMESTAMP(:fecha, s_hours.hora) >= DATE_ADD(NOW(), INTERVAL 24 HOUR)
GROUP BY s_hours.hora
ORDER BY s_hours.hora";
$st=$db->prepare($sql);
$st->execute([':dia'=>$dia,':fecha'=>$fecha]);
echo json_encode(['ok'=>true,'items'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
}catch(Throwable $e){
 http_response_code(500); echo json_encode(['ok'=>false]);
}
