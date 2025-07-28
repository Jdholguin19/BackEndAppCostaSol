<?php
/**
 * GET  ?proposito_id=…&fecha=YYYY-MM-DD
 * Devuelve array   [{responsable_id,hora}]
 */
require_once __DIR__.'/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$prop   = (int)($_GET['proposito_id']??0);
$fecha  = $_GET['fecha'] ?? '';
if(!$prop||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha)){
    http_response_code(400); exit(json_encode(['ok'=>false]));
}

try{
$db=DB::getDB();
$dia = (int)date('w', strtotime($fecha));   // 0 (domingo) - 6 (sábado)
$dia = $dia === 0 ? 1 : $dia + 1;           // 1 (domingo) - 7 (sábado) igual que DAYOFWEEK
$sql = "
SELECT s.responsable_id, TIME_FORMAT(s.hora,'%H:%i') hora
FROM (
  SELECT r.id AS responsable_id,
         ADDTIME(d.hora_inicio, SEC_TO_TIME(seq.n * d.intervalo_minutos * 60)) AS hora
  FROM   responsable r
  JOIN   responsable_disponibilidad d ON d.responsable_id = r.id
                                     AND d.activo = 1
                                     AND d.dia_semana = :dia
                                     AND :fecha BETWEEN d.fecha_vigencia_desde
                                                   AND IFNULL(d.fecha_vigencia_hasta, '2999-12-31')
  JOIN (
         SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
         UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7
         UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11
         UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
         UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
         UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23
         UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27
         UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30 UNION ALL SELECT 31
         UNION ALL SELECT 32 UNION ALL SELECT 33 UNION ALL SELECT 34 UNION ALL SELECT 35
         UNION ALL SELECT 36 UNION ALL SELECT 37 UNION ALL SELECT 38 UNION ALL SELECT 39
         UNION ALL SELECT 40 UNION ALL SELECT 41 UNION ALL SELECT 42 UNION ALL SELECT 43
         UNION ALL SELECT 44 UNION ALL SELECT 45 UNION ALL SELECT 46 UNION ALL SELECT 47
       ) seq
       ON seq.n < TIMESTAMPDIFF(MINUTE, d.hora_inicio, d.hora_fin) / d.intervalo_minutos
) s
LEFT JOIN agendamiento_visitas v
       ON v.responsable_id = s.responsable_id
      AND v.fecha_reunion = :fecha
      AND v.hora_reunion = s.hora
      AND v.estado <> 'CANCELADO'
WHERE v.id IS NULL
  AND TIMESTAMP(:fecha, s.hora) >= DATE_ADD(NOW(), INTERVAL 24 HOUR)
ORDER BY s.hora";
$st=$db->prepare($sql);
$st->execute([':dia'=>$dia,':fecha'=>$fecha]);
echo json_encode(['ok'=>true,'items'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
}catch(Throwable $e){
 http_response_code(500); echo json_encode(['ok'=>false]);
}
