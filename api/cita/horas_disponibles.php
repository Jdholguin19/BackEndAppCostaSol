<?php
/**
 * GET  ?proposito_id=…&fecha=YYYY-MM-DD
 * Devuelve array   [{responsable_id,hora}]
 */
require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// 1. Validar parámetros de entrada
$proposito_id = (int)($_GET['proposito_id'] ?? 0);
$fecha_str = $_GET['fecha'] ?? '';

if (!$proposito_id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_str)) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'mensaje' => 'Parámetros inválidos.']));
}

try {
    $db = DB::getDB();
    
    // 2. Obtener día de la semana (1=Lunes, 7=Domingo)
    $fecha_dt = new DateTime($fecha_str);
    $dia_semana = (int)$fecha_dt->format('N');

    // 3. Obtener citas ya agendadas para esa fecha
    $stmt_booked = $db->prepare("
        SELECT TIME_FORMAT(hora_reunion, '%H:%i') as hora 
        FROM agendamiento_visitas 
        WHERE fecha_reunion = :fecha AND estado <> 'CANCELADO'
    ");
    $stmt_booked->execute([':fecha' => $fecha_str]);
    $booked_slots = $stmt_booked->fetchAll(PDO::FETCH_COLUMN, 0);

    // 4. Obtener reglas de disponibilidad aplicables
    $stmt_rules = $db->prepare("
        SELECT responsable_id, hora_inicio, hora_fin, intervalo_minutos 
        FROM responsable_disponibilidad 
        WHERE activo = 1 
          AND dia_semana = :dia_semana
          AND :fecha BETWEEN fecha_vigencia_desde AND IFNULL(fecha_vigencia_hasta, '2999-12-31')
    ");
    $stmt_rules->execute([':dia_semana' => $dia_semana, ':fecha' => $fecha_str]);
    $availability_rules = $stmt_rules->fetchAll(PDO::FETCH_ASSOC);

    // 5. Generar y filtrar horarios
    $available_slots = [];
    $now_plus_buffer = new DateTime();
    $now_plus_buffer->add(new DateInterval('PT24H')); // Búfer de 24 horas

    foreach ($availability_rules as $rule) {
        $start_time = new DateTime($rule['hora_inicio']);
        $end_time = new DateTime($rule['hora_fin']);
        $interval = (int)$rule['intervalo_minutos'];

        if ($interval <= 0) continue;

        $current_slot_dt = clone $start_time;

        while ($current_slot_dt < $end_time) {
            $slot_str = $current_slot_dt->format('H:i');
            
            // Combinar fecha y hora para la comparación de tiempo
            $full_slot_dt = new DateTime($fecha_str . ' ' . $slot_str);

            // Comprobar si el slot está disponible y fuera del búfer de 24h
            if (!in_array($slot_str, $booked_slots) && $full_slot_dt >= $now_plus_buffer) {
                // Usar el horario como clave para evitar duplicados y asignar un responsable
                if (!isset($available_slots[$slot_str])) {
                    $available_slots[$slot_str] = $rule['responsable_id'];
                }
            }
            
            $current_slot_dt->add(new DateInterval("PT{$interval}M"));
        }
    }

    // 6. Formatear y ordenar la salida
    $final_slots = [];
    foreach ($available_slots as $hora => $responsable_id) {
        $final_slots[] = [
            'hora' => $hora,
            'responsable_id' => $responsable_id
        ];
    }

    // Ordenar por hora
    usort($final_slots, function($a, $b) {
        return strcmp($a['hora'], $b['hora']);
    });

    echo json_encode(['ok' => true, 'items' => $final_slots]);

} catch (Throwable $e) {
    http_response_code(500);
    error_log("horas_disponibles.php: " . $e->getMessage());
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor.']);
}
?>