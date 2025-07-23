<?php
/*  POST /api/pqr_insert_form.php
 *  Body  (multipart/form-data)
 *      pqr_id         int
 *      usuario_id     int
 *      mensaje        string
 *      archivo        (file | optional)
 *
 *  Respuesta:
 *      { ok:true }
 */
require_once __DIR__.'/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$db = DB::getDB();
try{
    /* ---------- 1. validar ---------- */
    $pqrId     = (int)($_POST['pqr_id']     ?? 0);
    $usuarioId = (int)($_POST['usuario_id'] ?? 0);
    $mensaje   = trim($_POST['mensaje']   ?? '');

    if(!$pqrId || !$usuarioId || $mensaje === ''){
        http_response_code(400);
        exit(json_encode(['ok'=>false,'msg'=>'Datos incompletos']));
    }

    /* ---------- 2. gestionar adjunto ---------- */
    $urlAdjunto = null;
    if(!empty($_FILES['archivo']['tmp_name'])){
        $name = uniqid().'-'.basename($_FILES['archivo']['name']);
        $dest = __DIR__.'/../ImagenesPQR_respuestas/'.$name;
        if(move_uploaded_uploaded_file($_FILES['archivo']['tmp_name'],$dest)){
            $urlAdjunto = "https://app.costasol.com.ec/ImagenesPQR_respuestas/$name";
        }
    }

    /* ---------- 3. obtener responsable_id (si existe) ---------- */
    // Assuming responsable_id might be needed, fetch it from the pqr table for the given pqr_id
    $responsableId = $db->query("SELECT responsable_id FROM pqr WHERE id = $pqrId")->fetchColumn();

    /* ---------- 4. insertar respuesta ---------- */
    $sql = 'INSERT INTO respuesta_pqr
            (pqr_id, usuario_id, responsable_id, mensaje, url_adjunto, fecha_respuesta)
            VALUES
            (:pqr_id, :usuario_id, :responsable_id, :mensaje, :url_adjunto, NOW())';
    $db->prepare($sql)->execute([
        ':pqr_id'=> $pqrId,
        ':usuario_id'=> $usuarioId,
        ':responsable_id'=> $responsableId, // Use the fetched responsable_id
        ':mensaje'=> $mensaje,
        ':url_adjunto'=> $urlAdjunto
    ]);

    echo json_encode(['ok'=>true]);

}catch(Throwable $e){
    error_log('pqr_insert_form: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error interno']);
}
?>