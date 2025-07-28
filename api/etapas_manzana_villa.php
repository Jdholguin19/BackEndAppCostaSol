<?php
/**
 *  etapas_manzana_villa.php – Listado de etapas y fotos para una manzana + villa
 *
 *  GET /api/etapas_manzana_villa.php?manzana=3350&villa=6
 *
 *  Respuesta:
 *  {
 *    "ok": true,
 *    "manzana": "3350",
 *    "villa": "6",
 *    "etapas": [
 *       {
 *         "id_etapa": 1,
 *         "etapa": "Cimentación",
 *         "porcentaje": 100,
 *         "estado": "Hecho",
 *         "descripcion": "Cimentación lista",
 *         "fotos": ["https://.../thumb1.jpg","https://..."]
 *       },
 *       ...
 *    ]
 *  }
 */

require_once __DIR__.'/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

/* ---------- parámetros ---------- */
$mz   = $_GET['manzana'] ?? '';
$vl   = $_GET['villa']   ?? '';

if ($mz==='' || $vl==='') {
    http_response_code(400);
    exit(json_encode(['ok'=>false,'mensaje'=>'Parámetros manzana y villa requeridos']));
}

try {
    $db = DB::getDB();

    /* 1. Todas las etapas (para mostrar las que aún no tienen avance) */
    $stmt = $db->query('SELECT id, nombre FROM etapa_construccion ORDER BY id');
    $etapas = [];
    foreach ($stmt as $row) {
        $etapas[$row['id']] = [
            'id_etapa'   => (int)$row['id'],
            'etapa'      => $row['nombre'],
            'porcentaje' => 0,
            'estado'     => 'Planificado',
            'descripcion'=> '',
            'fotos'      => []
        ];
    }

    /* 2. Avances para la manzana + villa */
    $sql = 'SELECT pc.id, pc.id_etapa, pc.porcentaje, pc.descripcion,
                   pc.drive_item_id,
                   ec.nombre
            FROM   progreso_construccion pc
            JOIN   etapa_construccion    ec ON ec.id = pc.id_etapa
            WHERE  pc.mz    = :mz
              AND  pc.villa = :vl
              AND  pc.estado = 1
            ORDER BY pc.id_etapa, pc.fecha_registro DESC';

    $q = $db->prepare($sql);
    $q->execute([':mz'=>$mz, ':vl'=>$vl]);

    foreach ($q as $r) {
        $e = &$etapas[$r['id_etapa']];                 // referencia
        // primera fila de esta etapa = última captura → usa sus datos
        if ($e['porcentaje'] === 0) {
            $e['porcentaje']  = (int)$r['porcentaje'];
            $e['descripcion'] = $r['descripcion'] ?? '';
            $e['estado']      = $r['porcentaje'] >= 100 ? 'Hecho'
                               : ($r['porcentaje'] > 0 ? 'Proceso' : 'Planificado');
        }
        if ($r['drive_item_id']) {
        $e['fotos'][] = "../SharePoint/mostrar_imagen.php?id=" . urlencode($r['id']);
    }
    }
    // quitar índice numérico
    $etapasList = array_values($etapas);

    echo json_encode([
        'ok'      => true,
        'manzana' => $mz,
        'villa'   => $vl,
        'etapas'  => $etapasList
    ]);

} catch (Throwable $e) {
    error_log('etapas_manzana_villa: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'mensaje'=>'Error interno']);
}
