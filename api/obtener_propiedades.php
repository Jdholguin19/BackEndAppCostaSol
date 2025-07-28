<?php
/**
 *  obtener_propiedades.php  – Lista las propiedades de un cliente
 *
 *  GET /app/obtener_propiedades.php?id_usuario=7
 *
 *  Respuesta:
 *  200 OK
 *  {
 *    "ok": true,
 *    "propiedades": [
 *      {
 *        "id": 12,
 *        "tipo":      "Casa",
 *        "urbanizacion": "Basilea",
 *        "estado":    "En construcción",
 *        "etapa":     "Estructura",
 *        "fecha_compra":      "2025-02-01",
 *        "fecha_entrega":     "2026-06-13",
 *        "manzana":   "3351",
 *        "solar":     "7",
 *        "villa":     "7"
 *      },
 *      ...
 *    ]
 *  }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

/* ---------- 1. Validar parámetros ---------- */
$idUsuario = $_GET['id_usuario'] ?? null;

if (!ctype_digit((string)$idUsuario)) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'mensaje' => 'Parámetro id_usuario requerido']));
}

try {
    $db = DB::getDB();

    /* ---------- 2. Query con joins para nombres descriptivos ---------- */
    $sql = 'SELECT
              p.id,
              tp.nombre          AS tipo,
              u.nombre           AS urbanizacion,
              ep.nombre          AS estado,
              ec.nombre          AS etapa,
              p.fecha_compra,
              p.fecha_entrega,
              p.manzana,
              p.solar,
              p.villa
            FROM propiedad              p
            JOIN tipo_propiedad         tp ON tp.id  = p.tipo_id
            JOIN urbanizacion           u  ON u.id   = p.id_urbanizacion
            JOIN estado_propiedad       ep ON ep.id  = p.estado_id
            LEFT JOIN etapa_construccion ec ON ec.id = p.etapa_id
            WHERE p.id_usuario = :uid
            ORDER BY p.fecha_compra DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute([':uid' => $idUsuario]);

    $props = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'propiedades' => $props]);

} catch (Throwable $e) {
    error_log('obtener_propiedades: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno']);
}
