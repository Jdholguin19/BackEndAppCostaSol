<?php
/**
 *  api/noticias.php  – Listado de noticias
 *
 *  Métodos:
 *    GET  /api/noticias.php
 *
 *  Parámetros opcionales:
 *      id               → devuelve solo esa noticia
 *      limit  (int)     → cantidad de registros (def. 12)
 *      offset (int)     → para paginar (def. 0)
 *      estado (0|1)     → filtrar por activo/inactivo (def. 1)
 *
 *  Respuesta:
 *      200 OK  { ok:true, total: X, noticias:[{ id, titulo, resumen, url_imagen,
 *                                              link_noticia, fecha_publicacion }] }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = DB::getDB();

    /* ---------- Parámetros ---------- */
    $id      = isset($_GET['id'])      ? (int)$_GET['id']      : 0;
    $limit   = isset($_GET['limit'])   ? max(1, (int)$_GET['limit'])   : 12;
    $offset  = isset($_GET['offset'])  ? max(0, (int)$_GET['offset'])  : 0;
    $estado  = isset($_GET['estado'])  ? (int)$_GET['estado']  : 1;  // 1 = activos

    /* ---------- Si piden una sola noticia ---------- */
    if ($id > 0) {
        $sql  = 'SELECT id, titulo, resumen, contenido, url_imagen, link_noticia,
                        fecha_publicacion, fecha_actualizacion
                   FROM noticia
                  WHERE id = :id';
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $n   = $stmt->fetch();
        if (!$n) {
            http_response_code(404);
            exit(json_encode(['ok' => false, 'mensaje' => 'Noticia no encontrada']));
        }
        exit(json_encode(['ok' => true, 'noticia' => $n]));
    }

    /* ---------- Listado paginado ---------- */
    $where  = 'WHERE 1';
    $params = [];
    if ($estado === 0 || $estado === 1) {
        $where .= ' AND estado = :estado';
        $params[':estado'] = $estado;
    }

    // total para paginación
    $total = $db->prepare("SELECT COUNT(*) FROM noticia $where");
    $total->execute($params);
    $total = (int)$total->fetchColumn();

    // listado
    $sql = "SELECT id, titulo, resumen, url_imagen, link_noticia,
                   fecha_publicacion
              FROM noticia
              $where
             ORDER BY fecha_publicacion DESC, orden ASC
             LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $noticias = $stmt->fetchAll();

    echo json_encode([
        'ok'       => true,
        'total'    => $total,
        'noticias' => $noticias
    ]);

} catch (Throwable $e) {
    error_log('noticias.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno']);
}
