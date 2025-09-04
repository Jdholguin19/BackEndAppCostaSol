<?php
/* Calendar feed para FullCalendar */
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$id    = (int)($_GET['responsable_id'] ?? 0);
$start = $_GET['start'] ?? '';
$end   = $_GET['end']   ?? '';

if (!$id || !strtotime($start) || !strtotime($end)) {
    http_response_code(400);
    exit(json_encode(['ok' => false]));
}

try {
    $db  = DB::getDB();
    $sql = "
      SELECT v.id,
             CONCAT(pa.proposito,' — ', COALESCE(u.nombres, 'Evento Externo'),' ', COALESCE(u.apellidos, '')) AS title,
             CONCAT(v.fecha_reunion,'T',v.hora_reunion)           AS start,
             ADDTIME(CONCAT(v.fecha_reunion,' ',v.hora_reunion),
                     SEC_TO_TIME(COALESCE(v.duracion_minutos, 45) * 60)) AS end,
             CASE v.estado
                  WHEN 'PROGRAMADO' THEN '#ffc107'
                  WHEN 'REALIZADO'  THEN '#198754'
                  WHEN 'CANCELADO'  THEN '#dc3545'
                  ELSE '#6c757d' END                             AS color
      FROM  agendamiento_visitas      v
      JOIN  proposito_agendamiento    pa ON pa.id = v.proposito_id
      LEFT JOIN  usuario              u  ON u.id  = v.id_usuario -- Cambiado a LEFT JOIN
      WHERE v.responsable_id = :id
        AND v.fecha_reunion BETWEEN :f0 AND :f1
      ORDER BY start";

    $st = $db->prepare($sql);
    $st->execute([
        ':id' => $id,
        ':f0' => $start,
        ':f1' => $end
    ]);

    echo json_encode(['ok' => true, 'items' => $st->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false]);
}

