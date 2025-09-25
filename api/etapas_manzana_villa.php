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
 * Falta implementar:
 * el sistema de porcentajes de las etapas reales, ya que ahora se muentran solo con validacion de si hay fotos o no, ya que esto esta de la mano con la base de datos, que no hay datos de los proncetajes 
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
    $stmt = $db->query('SELECT id, nombre, descripcion FROM etapa_construccion ORDER BY id');
    $etapas = [];
    foreach ($stmt as $row) {
        $etapas[$row['id']] = [
            'id_etapa'   => (int)$row['id'],
            'etapa'      => $row['nombre'],
            'porcentaje' => 0,
            'estado'     => 'Planificado',
            'descripcion'=> $row['descripcion'] ?? '',
            'fotos'      => []
        ];
    }

    /* 2. Avances para la manzana + villa */
    $sql = 'SELECT pc.id, pc.id_etapa, pc.porcentaje, ec.descripcion,
                   pc.drive_item_id, pc.fecha_registro, pc.mz, pc.villa,
                   ec.nombre
            FROM   progreso_construccion pc
            JOIN   etapa_construccion    ec ON ec.id = pc.id_etapa
            WHERE  pc.mz    = :mz
              AND  pc.villa = :vl
              AND  pc.estado = 1
            ORDER BY pc.id_etapa, pc.fecha_registro DESC';

    $q = $db->prepare($sql);
    $q->execute([':mz'=>$mz, ':vl'=>$vl]);

    // Debug: ver qué datos estamos obteniendo
    $debug_data = [];
    $processed_etapas = [];
    
    foreach ($q as $r) {
        $debug_data[] = $r;
        $idEtapa = $r['id_etapa'];
        
        // Solo actualizar si es la primera vez que vemos esta etapa (último registro)
        if (!isset($processed_etapas[$idEtapa])) {
            $e = &$etapas[$idEtapa];
            // Manejar porcentajes null - convertirlos a 0
            $porcentaje = $r['porcentaje'] === null ? 0 : (int)$r['porcentaje'];
            $e['porcentaje'] = $porcentaje;
            
            // Si hay fotos, considerar como "Proceso" aunque el porcentaje sea 0
            $tieneFotos = false;
            foreach ($q as $r2) {
                if ($r2['id_etapa'] == $idEtapa && $r2['drive_item_id']) {
                    $tieneFotos = true;
                    break;
                }
            }
            
            if ($tieneFotos) {
                $e['estado'] = 'Proceso';
            } else {
                $e['estado'] = $porcentaje >= 100 ? 'Hecho' 
                              : ($porcentaje > 0 ? 'Proceso' : 'Planificado');
            }
            
            $processed_etapas[$idEtapa] = true;
        }
        
        // Agregar fotos si existen
        if ($r['drive_item_id']) {
            $etapas[$idEtapa]['fotos'][] = "../SharePoint/mostrar_imagen.php?id=" . urlencode($r['id']);
        }
    }

    /* ---------- 3. Calcular el porcentaje general basado en la última etapa con foto ---------- */
    $sql_ultima_etapa_con_foto = 'SELECT pc.id_etapa, ec.porcentaje as porcentaje_etapa, ec.nombre
                                  FROM progreso_construccion pc
                                  JOIN etapa_construccion ec ON ec.id = pc.id_etapa
                                  WHERE pc.mz = :mz 
                                    AND pc.villa = :vl 
                                    AND pc.estado = 1
                                    AND pc.drive_item_id IS NOT NULL
                                    AND pc.drive_item_id != ""
                                  ORDER BY pc.id_etapa DESC
                                  LIMIT 1';

    $stmt_ultima = $db->prepare($sql_ultima_etapa_con_foto);
    $stmt_ultima->execute([':mz' => $mz, ':vl' => $vl]);
    $ultima_etapa_con_foto = $stmt_ultima->fetch(PDO::FETCH_ASSOC);

    $porcentaje_general = 0;
    $etapa_actual = 'Sin progreso';
    
    if ($ultima_etapa_con_foto) {
        $porcentaje_general = (int)$ultima_etapa_con_foto['porcentaje_etapa'];
        $etapa_actual = $ultima_etapa_con_foto['nombre'];
    }

    // Debug: incluir información de debug en la respuesta
    $response = [
        'ok'      => true,
        'manzana' => $mz,
        'villa'   => $vl,
        'etapas'  => array_values($etapas),
        'progreso_general' => [
            'porcentaje' => $porcentaje_general,
            'etapa_actual' => $etapa_actual
        ],
        'debug'   => [
            'sql_params' => ['mz' => $mz, 'vl' => $vl],
            'found_records' => count($debug_data),
            'sample_data' => array_slice($debug_data, 0, 3),
            'processed_etapas' => array_keys($processed_etapas),
            'ultima_etapa_con_foto' => $ultima_etapa_con_foto,
            'sql_query' => $sql
        ]
    ];

    echo json_encode($response);

} catch (Throwable $e) {
    error_log('etapas_manzana_villa: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'mensaje'=>'Error interno']);
}
