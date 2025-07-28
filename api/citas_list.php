<?php
/**
 * GET /api/citas_list.php?id_usuario=…
 * ▸ Devuelve todas las citas del usuario (próximas y pasadas)
 *   {
 *     ok   : true,
 *     citas: [
 *        { id, proposito, fecha, hora, estado,
 *          responsable, url_foto, proyecto, manzana, villa }
 *     ]
 *   }
 */
require_once __DIR__.'/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$uid = (int)($_GET['id_usuario'] ?? 0);
if (!$uid) { http_response_code(400); exit(json_encode(['ok'=>false])); }

try {
    $db = DB::getDB();
    $sql = "
       SELECT  v.id,
        pa.proposito,                                 -- nombre del propósito (tabla nueva)
        v.fecha_reunion              AS fecha,
        TIME_FORMAT(v.hora_reunion,'%H:%i') AS hora,
        v.estado,

        r.nombre                     AS responsable,
        r.url_foto_perfil            AS url_foto,

        CONCAT('Proyecto ', u.nombre,
               ' - Mz ', pr.manzana,
               ', Villa ', pr.villa) AS proyecto,

        pr.manzana,
        pr.villa
FROM            agendamiento_visitas       v
JOIN proposito_agendamiento          pa ON pa.id = v.proposito_id
JOIN responsable                     r  ON r.id  = v.responsable_id
JOIN propiedad                       pr ON pr.id = v.id_propiedad
JOIN urbanizacion                    u  ON u.id  = pr.id_urbanizacion
WHERE  v.id_usuario = :uid
ORDER BY v.fecha_reunion ASC, v.hora_reunion ASC;

";

    $st = $db->prepare($sql);
    $st->execute([':uid'=>$uid]);

    echo json_encode(['ok'=>true,
                      'citas'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    error_log('citas_list: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false]);
}
