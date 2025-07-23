<?php
/*  GET /api/notificaciones.php
 *
 *  Respuesta:
 *      {
 *        ok:true,
 *        notificaciones: [ { pqr_id:1, mensaje:"...", usuario:"...", fecha_respuesta:"...", url_adjunto:"..." }, ... ]
 *      }
 */

 require_once __DIR__.'/../config/db.php';
 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

$db = DB::getDB();


try {
    // Obtener las últimas respuestas de PQR (puedes ajustar el límite o agregar filtros si es necesario)
    $sql = "SELECT
                rp.pqr_id,
                rp.mensaje,
                u.nombres AS usuario,
                rp.fecha_respuesta,
                rp.url_adjunto
            FROM respuesta_pqr rp
            JOIN usuario u ON rp.usuario_id = u.id
            ORDER BY rp.fecha_respuesta DESC
            LIMIT 20"; // Limita a las últimas 20 notificaciones

    $stmt = $db->query($sql);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'notificaciones' => $notificaciones]);

} catch (Throwable $e) {
    error_log('notificaciones.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error interno']);
}
?>