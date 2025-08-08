<?php
/**
 *  api/noticias.php  – Listado de noticias
 *
 *  Métodos:
 *    GET  /api/noticias.php
 *    POST /api/noticias.php        - Crear noticia
 *    DELETE /api/noticias.php?id=X - Borrar noticia
 *
 *  Parámetros opcionales (GET):
 *      id               → devuelve solo esa noticia
 *      limit  (int)     → cantidad de registros (def. 12)
 *      offset (int)     → para paginar (def. 0)
 *      estado (0|1)     → filtrar por activo/inactivo (def. 1)
 *
 *  Cuerpo de la solicitud (POST):
 *      titulo (string)
 *      resumen (string)
 *      url_imagen (string, opcional)
 *      link_noticia (string, opcional)
 *      autor_id (int, opcional - si se autentica al responsable)
 *
 *  Parámetros de URL (DELETE):
 *      id (int, requerido) → ID de la noticia a borrar
 *
 *  Respuesta:
 *      200 OK  { ok:true, ... }
 *      400 Bad Request { ok: false, mensaje: "..." }
 *      404 Not Found { ok: false, mensaje: "..." }
 *      500 Internal Server Error { ok: false, mensaje: "Error interno" }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = DB::getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            /* ---------- Lógica existente para listar/obtener noticia por ID ---------- */
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
                $n   = $stmt->fetch(PDO::FETCH_ASSOC); // Usar fetch(PDO::FETCH_ASSOC) para consistencia
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
            $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC); // Usar fetchAll(PDO::FETCH_ASSOC)

            echo json_encode([
                'ok'       => true,
                'total'    => $total,
                'noticias' => $noticias
            ]);

            break;

        case 'POST':
            /* ---------- Crear nueva noticia ---------- */
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar datos mínimos
            if (!isset($data['titulo']) || empty($data['titulo']) || !isset($data['resumen']) || empty($data['resumen'])) {
                http_response_code(400);
                exit(json_encode(['ok' => false, 'mensaje' => 'Faltan campos obligatorios (titulo, resumen)']));
            }

            $titulo = $data['titulo'];
            $resumen = $data['resumen'];
            $url_imagen = $data['url_imagen'] ?? null; // Opcional
            $link_noticia = $data['link_noticia'] ?? null; // Opcional
            //autor_id - si manejas autenticación, obtén el ID del usuario responsable aquí
            $autor_id = null; // Por ahora, dejándolo como NULL según tu indicación

            // Determinar el siguiente valor para 'orden' (simple: max + 1)
            $stmt_orden = $db->query("SELECT MAX(orden) FROM noticia");
            $next_orden = (int)$stmt_orden->fetchColumn() + 1;

            $sql_insert = 'INSERT INTO noticia (titulo, resumen, url_imagen, link_noticia, estado, orden, fecha_publicacion, autor_id)
                           VALUES (:titulo, :resumen, :url_imagen, :link_noticia, :estado, :orden, NOW(), :autor_id)';

            $stmt_insert = $db->prepare($sql_insert);

            $result = $stmt_insert->execute([
                ':titulo' => $titulo,
                ':resumen' => $resumen,
                ':url_imagen' => $url_imagen,
                ':link_noticia' => $link_noticia,
                ':estado' => 1, // Estado inicial 1
                ':orden' => $next_orden,
                ':autor_id' => $autor_id
            ]);

            if ($result) {
                echo json_encode(['ok' => true, 'mensaje' => 'Noticia creada con éxito', 'id' => $db->lastInsertId()]);
            } else {
                http_response_code(500); // Error interno del servidor
                echo json_encode(['ok' => false, 'mensaje' => 'Error al crear la noticia']);
            }

            break;

        case 'DELETE':
            /* ---------- Borrar noticia ---------- */
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            if ($id <= 0) {
                http_response_code(400); // Bad Request
                exit(json_encode(['ok' => false, 'mensaje' => 'ID de noticia inválido']));
            }

            // Puedes añadir una verificación aquí para asegurarte de que el usuario autenticado tiene permiso para borrar esta noticia

            $sql_delete = 'DELETE FROM noticia WHERE id = :id';
            $stmt_delete = $db->prepare($sql_delete);

            $result = $stmt_delete->execute([':id' => $id]);

            if ($result) {
                if ($stmt_delete->rowCount() > 0) {
                    echo json_encode(['ok' => true, 'mensaje' => 'Noticia eliminada con éxito']);
                } else {
                    http_response_code(404); // Not Found - Si el ID no existía
                    echo json_encode(['ok' => false, 'mensaje' => 'Noticia no encontrada para eliminar']);
                }
            } else {
                http_response_code(500); // Error interno del servidor
                echo json_encode(['ok' => false, 'mensaje' => 'Error al eliminar la noticia']);
            }

            break;

        default:
            // Método no permitido
            http_response_code(405); // Method Not Allowed
            header('Allow: GET, POST, DELETE'); // Informar los métodos permitidos
            echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
            break;
    }

} catch (Throwable $e) {
    error_log('noticias.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}