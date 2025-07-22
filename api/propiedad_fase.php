<?php
/**
 *  propiedad_fase.php  –  Devuelve la etapa y porcentaje de una propiedad
 *
 *  GET /api/propiedad_fase.php?id_propiedad=12
 *
 *  Respuesta OK:
 *  {
 *    "ok": true,
 *    "propiedad_id": 12,
 *    "fase": {
 *       "etapa_id": 3,
 *       "etapa": "Estructura",
 *       "descripcion": "Columnas y losa terminadas",
 *       "porcentaje": 45
 *    }
 *  }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

/* ---------- 1. Validar parámetro ---------- */
$idProp = $_GET['id_propiedad'] ?? null;
if (!ctype_digit((string)$idProp)) {
    http_response_code(400);
    exit(json_encode(['ok'=>false,'mensaje'=>'Parámetro id_propiedad requerido']));
}

try {
    $db = DB::getDB();

    /* ---------- 2. Consultar propiedad + etapa ---------- */
    $sql = 'SELECT
              p.etapa_id,
              ec.nombre       AS etapa,
              ec.descripcion,
              ec.porcentaje
            FROM propiedad            p
            LEFT JOIN etapa_construccion ec ON ec.id = p.etapa_id
            WHERE p.id = :pid
            LIMIT 1';

    $stmt = $db->prepare($sql);
    $stmt->execute([':pid' => $idProp]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {                      // ID de propiedad inexistente
        http_response_code(404);
        exit(json_encode(['ok'=>false,'mensaje'=>'Propiedad no encontrada']));
    }

    if (!$row['etapa_id']) {          // Propiedad aún sin etapa asignada
        exit(json_encode([
            'ok'           => true,
            'propiedad_id' => (int)$idProp,
            'fase' => [
                'etapa_id'   => null,
                'etapa'      => 'Sin etapa asignada',
                'descripcion'=> '',
                'porcentaje' => 0
            ]
        ]));
    }

    echo json_encode([
        'ok'           => true,
        'propiedad_id' => (int)$idProp,
        'fase'         => $row
    ]);

} catch (Throwable $e) {
    error_log('propiedad_fase: '.$e->getMessage());
    http_response_code(500);
    exit(json_encode(['ok'=>false,'mensaje'=>'Error interno']));
}
