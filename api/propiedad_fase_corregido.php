<?php
/**
 *  propiedad_fase_corregido.php  –  Devuelve la etapa y porcentaje de una propiedad
 *  basado en la ÚLTIMA etapa que tiene fotos, no la primera que encuentra
 *
 *  GET /api/propiedad_fase_corregido.php?id_propiedad=12
 *
 *  Respuesta OK:
 *  {
 *    "ok": true,
 *    "propiedad_id": 12,
 *    "fase": {
 *       "etapa_id": 4,
 *       "etapa": "Habitabilidad",
 *       "descripcion": "En esta etapa la vivienda ya cuenta con puerta...",
 *       "porcentaje": 95
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

    /* ---------- 2. Obtener información de la propiedad ---------- */
    $sql_propiedad = 'SELECT p.id, p.manzana, p.villa, p.etapa_id, p.id_urbanizacion
                      FROM propiedad p
                      WHERE p.id = :pid
                      LIMIT 1';

    $stmt_propiedad = $db->prepare($sql_propiedad);
    $stmt_propiedad->execute([':pid' => $idProp]);
    $propiedad = $stmt_propiedad->fetch(PDO::FETCH_ASSOC);

    if (!$propiedad) {
        http_response_code(404);
        exit(json_encode(['ok'=>false,'mensaje'=>'Propiedad no encontrada']));
    }

    $manzana = $propiedad['manzana'];
    $villa = $propiedad['villa'];
    $id_urbanizacion = $propiedad['id_urbanizacion'];

    /* ---------- 3. Buscar la ÚLTIMA etapa que tiene fotos ---------- */
    $sql_ultima_etapa = 'SELECT pc.id_etapa, pc.porcentaje, ec.nombre, ec.descripcion, ec.porcentaje as porcentaje_etapa
                         FROM progreso_construccion pc
                         JOIN etapa_construccion ec ON ec.id = pc.id_etapa
                         WHERE pc.mz = :manzana 
                           AND pc.villa = :villa 
                           AND pc.id_urbanizacion = :id_urbanizacion
                           AND pc.estado = 1
                           AND pc.drive_item_id IS NOT NULL
                           AND pc.drive_item_id != ""
                         ORDER BY pc.id_etapa DESC
                         LIMIT 1';

    $stmt_ultima = $db->prepare($sql_ultima_etapa);
    $stmt_ultima->execute([
        ':manzana' => $manzana,
        ':villa' => $villa,
        ':id_urbanizacion' => $id_urbanizacion
    ]);
    $ultima_etapa_con_foto = $stmt_ultima->fetch(PDO::FETCH_ASSOC);

    /* ---------- 4. Si no hay etapas con fotos, usar la etapa asignada en la propiedad ---------- */
    if (!$ultima_etapa_con_foto) {
        $sql_etapa_propiedad = 'SELECT ec.id as etapa_id, ec.nombre as etapa, ec.descripcion, ec.porcentaje
                               FROM etapa_construccion ec
                               WHERE ec.id = :etapa_id';

        $stmt_etapa = $db->prepare($sql_etapa_propiedad);
        $stmt_etapa->execute([':etapa_id' => $propiedad['etapa_id']]);
        $fase = $stmt_etapa->fetch(PDO::FETCH_ASSOC);

        if (!$fase) {
            $fase = [
                'etapa_id' => null,
                'etapa' => 'Sin etapa asignada',
                'descripcion' => '',
                'porcentaje' => 0
            ];
        }
    } else {
        /* ---------- 5. Usar la última etapa con foto ---------- */
        $fase = [
            'etapa_id' => (int)$ultima_etapa_con_foto['id_etapa'],
            'etapa' => $ultima_etapa_con_foto['nombre'],
            'descripcion' => $ultima_etapa_con_foto['descripcion'],
            'porcentaje' => (int)$ultima_etapa_con_foto['porcentaje_etapa'] // Usar el porcentaje de la tabla etapa_construccion
        ];
    }

    /* ---------- 6. Debug: información adicional ---------- */
    $debug_info = [
        'manzana' => $manzana,
        'villa' => $villa,
        'id_urbanizacion' => $id_urbanizacion,
        'etapa_propiedad' => $propiedad['etapa_id'],
        'ultima_etapa_con_foto' => $ultima_etapa_con_foto ? [
            'id_etapa' => $ultima_etapa_con_foto['id_etapa'],
            'nombre' => $ultima_etapa_con_foto['nombre'],
            'porcentaje' => $ultima_etapa_con_foto['porcentaje_etapa']
        ] : null
    ];

    echo json_encode([
        'ok' => true,
        'propiedad_id' => (int)$idProp,
        'fase' => $fase,
        'debug' => $debug_info
    ]);

} catch (Throwable $e) {
    error_log('propiedad_fase_corregido: '.$e->getMessage());
    http_response_code(500);
    exit(json_encode(['ok'=>false,'mensaje'=>'Error interno']));
}
?>
