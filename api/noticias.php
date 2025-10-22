<?php
/**
 *  api/noticias.php  – Listado de noticias
 *
 *  Métodos:
 *    GET  /api/noticias.php
 *    POST /api/noticias.php        - Crear noticia con subida de imagen
 *    DELETE /api/noticias.php?id=X - Borrar noticia
 *
 *  Parámetros opcionales (GET):
 *      id               → devuelve solo esa noticia
 *      limit  (int)     → cantidad de registros (def. 12)
 *      offset (int)     → para paginar (def. 0)
 *      estado (0|1)     → filtrar por activo/inactivo (def. 1)
 *
 *  Cuerpo de la solicitud (POST) - multipart/form-data:
 *      titulo (string)      - requerido
 *      resumen (string)     - requerido
 *      imagen (file)        - requerido, formatos: JPG, PNG, GIF, WEBP (máx 2MB)
 *      link_noticia (string, opcional)
 *
 *  Parámetros de URL (DELETE):
 *      id (int, requerido) → ID de la noticia a borrar
 *
 *  Respuesta:
 *      200 OK  { ok:true, ... }
 *      400 Bad Request { ok: false, error: "..." }
 *      404 Not Found { ok: false, error: "..." }
 *      500 Internal Server Error { ok: false, error: "Error interno" }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * Envía una notificación a usuarios basado en criterios de rol
 * @param PDO $db Base de datos
 * @param string $titulo Título de la notificación
 * @param string $mensaje Mensaje de la notificación
 * @param string $tipo Tipo de notificación (noticia, ctg, pqr, etc)
 * @param int $tipo_id ID relacionado (noticia_id, ctg_id, etc)
 * @param array|null $destinatarios Array con 'todos' (bool), 'clientes' (bool), 'residentes' (bool)
 *                                    Si es null, se envía a todos
 */
function sendNotificationByRoles($db, $titulo, $mensaje, $tipo, $tipo_id, $destinatarios = null) {
    try {
        // Si no hay destinatarios especificados, enviar a todos
        if ($destinatarios === null || (isset($destinatarios['todos']) && $destinatarios['todos'])) {
            $sql_usuarios = "SELECT id FROM usuario";
            $params = [];
        } else {
            // Construir array de roles a incluir
            $roles = [];
            if (isset($destinatarios['clientes']) && $destinatarios['clientes']) {
                $roles[] = 1; // Cliente
            }
            if (isset($destinatarios['residentes']) && $destinatarios['residentes']) {
                $roles[] = 2; // Residente
            }

            // Si no hay roles seleccionados, no enviar notificaciones
            if (empty($roles)) {
                return true; // No es error, solo que no hay destinatarios
            }

            // Crear placeholders para consulta
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $sql_usuarios = "SELECT id FROM usuario WHERE rol_id IN ($placeholders)";
            $params = $roles;
        }

        // Obtener usuarios
        $stmt_usuarios = $db->prepare($sql_usuarios);
        $stmt_usuarios->execute($params);
        $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

        if (empty($usuarios)) {
            return false;
        }

        // Insertar notificación para cada usuario
        $sql_notif = "INSERT INTO notificacion (usuario_id, titulo, mensaje, tipo, tipo_id, leido, fecha_creacion)
                      VALUES (:usuario_id, :titulo, :mensaje, :tipo, :tipo_id, 0, NOW())";
        $stmt_notif = $db->prepare($sql_notif);

        foreach ($usuarios as $usuario) {
            $stmt_notif->execute([
                ':usuario_id' => $usuario['id'],
                ':titulo' => $titulo,
                ':mensaje' => $mensaje,
                ':tipo' => $tipo,
                ':tipo_id' => $tipo_id
            ]);
        }

        return true;
    } catch (Throwable $e) {
        error_log('Error enviando notificaciones: ' . $e->getMessage());
        return false;
    }
}

/**
 * Envía una notificación a todos los usuarios (legacy)
 * @param PDO $db Base de datos
 * @param string $titulo Título de la notificación
 * @param string $mensaje Mensaje de la notificación
 * @param string $tipo Tipo de notificación (noticia, ctg, pqr, etc)
 * @param int $tipo_id ID relacionado (noticia_id, ctg_id, etc)
 */
function sendNotificationToAll($db, $titulo, $mensaje, $tipo, $tipo_id) {
    return sendNotificationByRoles($db, $titulo, $mensaje, $tipo, $tipo_id, ['todos' => true]);
}

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
            $filter_by_user = isset($_GET['filter_by_user']) && $_GET['filter_by_user'] === '1';

            // Obtener usuario_id del token si se requiere filtrado
            $usuario_id = null;
            $is_responsable = false;
            if ($filter_by_user) {
                $headers = getallheaders();
                $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
                
                if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                    $token = $matches[1];
                    
                    // Validar token en tabla usuario
                    $stmt = $db->prepare("SELECT id FROM usuario WHERE token = :token");
                    $stmt->execute([':token' => $token]);
                    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($userData) {
                        $usuario_id = $userData['id'];
                        $is_responsable = false;
                    } else {
                        // Si no está en usuario, buscar en responsable
                        $stmt = $db->prepare("SELECT id FROM responsable WHERE token = :token");
                        $stmt->execute([':token' => $token]);
                        $respData = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($respData) {
                            // Los responsables ven todas las noticias, no filtrar
                            $is_responsable = true;
                            $filter_by_user = false; // Desactivar filtrado para responsables
                        }
                    }
                }
            }

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
            $where  = 'WHERE n.estado = :estado';
            $params = [':estado' => $estado];
            
            // Log para debug
            error_log("noticias.php: filter_by_user=$filter_by_user, usuario_id=$usuario_id, is_responsable=$is_responsable");
            
            // Si hay filtrado por usuario, hacer JOIN con notificacion
            if ($filter_by_user && $usuario_id) {
                $sql = "SELECT DISTINCT n.id, n.titulo, n.resumen, n.url_imagen, n.link_noticia,
                               n.fecha_publicacion
                          FROM noticia n
                          INNER JOIN notificacion notif ON notif.tipo_id = n.id 
                                                        AND notif.tipo = 'Noticia'
                                                        AND notif.usuario_id = :usuario_id
                          $where
                         ORDER BY n.fecha_publicacion DESC, n.orden ASC
                         LIMIT :limit OFFSET :offset";
                $params[':usuario_id'] = $usuario_id;
                
                error_log("noticias.php: Filtrando noticias para usuario_id=$usuario_id");
            } else {
                // Sin filtrado, devolver todas las noticias (para admin)
                $sql = "SELECT n.id, n.titulo, n.resumen, n.url_imagen, n.link_noticia,
                               n.fecha_publicacion
                          FROM noticia n
                          $where
                         ORDER BY n.fecha_publicacion DESC, n.orden ASC
                         LIMIT :limit OFFSET :offset";
            }

            // Contar total
            if ($filter_by_user && $usuario_id) {
                $countSql = "SELECT COUNT(DISTINCT n.id) FROM noticia n
                             INNER JOIN notificacion notif ON notif.tipo_id = n.id 
                                                           AND notif.tipo = 'Noticia'
                                                           AND notif.usuario_id = :usuario_id
                             $where";
            } else {
                $countSql = "SELECT COUNT(*) FROM noticia n $where";
            }
            
            $totalStmt = $db->prepare($countSql);
            foreach ($params as $k => $v) {
                if ($k !== ':limit' && $k !== ':offset') {
                    $totalStmt->bindValue($k, $v);
                }
            }
            $totalStmt->execute();
            $total = (int)$totalStmt->fetchColumn();

            // Ejecutar consulta principal
            $stmt = $db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'ok'       => true,
                'total'    => $total,
                'noticias' => $noticias
            ]);

            break;

        case 'POST':
            /* ---------- Crear nueva noticia con subida de imagen ---------- */
            
            // Validar datos mínimos
            if (!isset($_POST['titulo']) || empty($_POST['titulo']) || !isset($_POST['resumen']) || empty($_POST['resumen'])) {
                http_response_code(400);
                exit(json_encode(['ok' => false, 'error' => 'Faltan campos obligatorios (titulo, resumen)']));
            }

            $titulo = $_POST['titulo'];
            $resumen = $_POST['resumen'];
            $link_noticia = $_POST['link_noticia'] ?? null;
            $url_imagen = null;

            // Manejar la subida de imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['imagen'];
                
                // Validar tipo de archivo
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($file['type'], $allowed_types)) {
                    http_response_code(400);
                    exit(json_encode(['ok' => false, 'error' => 'Tipo de archivo no permitido. Use JPG, PNG, GIF o WEBP']));
                }

                // Validar tamaño (máx 2MB)
                if ($file['size'] > 2 * 1024 * 1024) {
                    http_response_code(400);
                    exit(json_encode(['ok' => false, 'error' => 'Archivo demasiado grande (máx 2MB)']));
                }

                // Crear carpeta si no existe
                $upload_dir = __DIR__ . '/../ImagenesNoticias/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Generar nombre único
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'noticia_' . time() . '_' . uniqid() . '.' . $ext;
                $filepath = $upload_dir . $filename;

                // Mover archivo
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $url_imagen = '../ImagenesNoticias/' . $filename;
                } else {
                    http_response_code(500);
                    exit(json_encode(['ok' => false, 'error' => 'Error al subir la imagen']));
                }
            } else {
                http_response_code(400);
                exit(json_encode(['ok' => false, 'error' => 'Imagen es requerida']));
            }

            // Determinar el siguiente valor para 'orden'
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
                ':estado' => 1,
                ':orden' => $next_orden,
                ':autor_id' => null
            ]);

            if ($result) {
                $noticia_id = $db->lastInsertId();

                // Procesar destinatarios
                $destinatarios = null;
                if (isset($_POST['destinatarios'])) {
                    $destinatarios = json_decode($_POST['destinatarios'], true);
                    if (!is_array($destinatarios)) {
                        $destinatarios = null;
                    }
                }

                // Enviar notificación con criterio de roles
                $titulo_notif = 'Nueva Noticia: ' . substr($titulo, 0, 50);
                $mensaje_notif = $resumen;
                sendNotificationByRoles($db, $titulo_notif, $mensaje_notif, 'noticia', $noticia_id, $destinatarios);

                echo json_encode(['ok' => true, 'mensaje' => 'Noticia creada con éxito', 'id' => $noticia_id]);
            } else {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'Error al insertar en base de datos']);
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