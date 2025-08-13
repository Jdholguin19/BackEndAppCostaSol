<?php
require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$uid = (int)($_POST['id_usuario']??0);
$propiedad = (int)($_POST['id_propiedad']??0);
$proposito = (int)($_POST['proposito_id']??0);
$fecha = $_POST['fecha']??'';
$hora  = $_POST['hora']??'';
if(!$uid||!$propiedad||!$proposito||
   !preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha)||
   !preg_match('/^\d{2}:\d{2}$/',$hora))
{ http_response_code(400); exit(json_encode(['ok'=>false])); }

try{
$db=DB::getDB(); $db->beginTransaction();

$dia = (int)date('w', strtotime($fecha));   // 0 (domingo) - 6 (sábado)
if ($dia == 0) { // Sunday
    $dia = 7; // Map Sunday to 7, assuming no availability for Sunday in DB
}

/* responsable disponible con menos carga ese día */
$resp=$db->prepare("
 SELECT r.id,COUNT(v.id) n
 FROM   responsable r
 JOIN   responsable_disponibilidad d ON d.responsable_id=r.id
       AND d.activo=1 AND d.dia_semana= :dia_calculated
       AND :f BETWEEN d.fecha_vigencia_desde
                 AND IFNULL(d.fecha_vigencia_hasta,'2999-12-31')
       AND TIME(:h) BETWEEN d.hora_inicio AND d.hora_fin
 LEFT   JOIN agendamiento_visitas v ON v.responsable_id=r.id
       AND v.fecha_reunion=:f AND v.hora_reunion = TIME(:h) AND v.estado<>'CANCELADO'
 GROUP  BY r.id
 ORDER  BY n ASC, RAND() ASC
 LIMIT 1");
$resp->execute([':f'=>$fecha,':h'=>$hora, ':dia_calculated'=>$dia]);
$respId=$resp->fetchColumn();
if(!$respId) throw new Exception('Sin responsable');

/* inserta */
$ins=$db->prepare("
 INSERT INTO agendamiento_visitas
   (id_usuario,responsable_id,proposito_id,id_propiedad,
    fecha_reunion,hora_reunion,estado)
 VALUES(:u,:r,:p,:prop,:f,:h,'PROGRAMADO')");
$ins->execute([
 ':u'=>$uid,':r'=>$respId,':p'=>$proposito,':prop'=>$propiedad,
 ':f'=>$fecha,':h'=>$hora]);
$db->commit();
echo json_encode(['ok'=>true]);
}catch(PDOException $e){
 $db->rollBack();
 if($e->errorInfo[1]==1062)
      echo json_encode(['ok'=>false,'msg'=>'Horario ya reservado']);
 else{ http_response_code(500); echo json_encode(['ok'=>false]); }
}
