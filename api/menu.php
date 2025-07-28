<?php
/**
 *  api/menu.php  – Devuelve los ítems de menú activos.
 *
 *  GET /api/menu.php
 *  Parámetros opcionales:
 *      role_id   → filtra los menús asignados a ese rol (tabla rol_menu).
 *
 *  Respuesta:
 *      200 OK  { "ok": true, "menus": [ { id, nombre, descripcion, url_icono, orden } ] }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db      = DB::getDB();
    $role_id = isset($_GET['role_id']) ? (int)$_GET['role_id'] : 0;

    /* --------- Query base --------- */
    $sql = 'SELECT m.id, m.nombre, m.descripcion, m.url_icono, m.orden
              FROM menu m
             WHERE m.estado = 1';

    $params = [];

    /* --------- Filtrar por rol si llega role_id --------- */
    if ($role_id > 0) {
        $sql .= ' AND EXISTS (
                     SELECT 1
                       FROM rol_menu rm
                      WHERE rm.menu_id = m.id
                        AND rm.rol_id  = :role_id
                  )';
        $params[':role_id'] = $role_id;
    }

    $sql .= ' ORDER BY m.orden, m.nombre';

    /* --------- Ejecutar --------- */
    $stmt  = $db->prepare($sql);
    $stmt->execute($params);
    $menus = $stmt->fetchAll();

    echo json_encode(['ok' => true, 'menus' => $menus]);

} catch (Throwable $e) {
    error_log('menu.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno']);
}
