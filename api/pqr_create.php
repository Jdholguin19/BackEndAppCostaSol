<?php
/*  POST /api/pqr_create.php
 *  Body  (multipart/form-data)
 *      id_usuario       int
 *      id_propiedad     int
 *      tipo_id          int
 *      subtipo_id       int
 *      descripcion      string
 *      archivo          (file | optional)
 *
 *  Respuesta:
 *      { ok:true, id:123, numero:'SAC00007' }
 */
require_once __DIR__.'/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$db = DB::getDB();
try{
    /* ---------- 1. validar ---------- */
    $uid   = (int)($_POST['id_usuario']   ?? 0);
    $pid   = (int)($_POST['id_propiedad'] ?? 0);
    $tipo  = (int)($_POST['tipo_id']      ?? 0);
    $sub   = (int)($_POST['subtipo_id']   ?? 0);
    $desc  = trim($_POST['descripcion']   ?? '');

    if(!$uid||!$pid||!$tipo||!$sub||$desc===''){
        http_response_code(400);
        exit(json_encode(['ok'=>false,'msg'=>'Datos incompletos']));
    }

    /* ---------- 2. gestionar adjunto ---------- */
    $urlProblema = null;
    if(!empty($_FILES['archivo']['tmp_name'])){
        $name = uniqid().'-'.basename($_FILES['archivo']['name']);
        $dest = __DIR__.'/../ImagenesPQR_problema/'.$name;
        if(move_uploaded_file($_FILES['archivo']['tmp_name'],$dest)){
            $urlProblema = "https://app.costasol.com.ec/ImagenesPQR_problema/$name";
        }
    }

    /* ---------- 3. obtener responsable aleatorio ---------- */
    $respId = $db->query("SELECT id FROM responsable WHERE estado=1 ORDER BY RAND() LIMIT 1")
                 ->fetchColumn();

    /* ---------- 4. crear numero_solicitud ---------- */
    $num = $db->query("SELECT LPAD(IFNULL(MAX(id),0)+1,5,'0') FROM pqr")->fetchColumn();
    $numero = 'SAC'.$num;                               // ej: SAC00007

    /* ---------- 5. insertar ---------- */
    $sql = 'INSERT INTO pqr
            (numero_solicitud,id_usuario,id_propiedad,tipo_id,subtipo_id,estado_id,
             descripcion,urgencia_id,url_problema,responsable_id,fecha_compromiso)
            VALUES
            (:num,:uid,:pid,:tipo,:sub,1,          /* 1 = Ingresado */
             :des,2,:url,:resp,DATE_ADD(NOW(),INTERVAL 5 DAY))';
    $db->prepare($sql)->execute([
        ':num'=>$numero, ':uid'=>$uid, ':pid'=>$pid,
        ':tipo'=>$tipo, ':sub'=>$sub, ':des'=>$desc,
        ':url'=>$urlProblema, ':resp'=>$respId
    ]);

    echo json_encode(['ok'=>true,'id'=>$db->lastInsertId(),'numero'=>$numero]);

}catch(Throwable $e){
    error_log('pqr_create: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
