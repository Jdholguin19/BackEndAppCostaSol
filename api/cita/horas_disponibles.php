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
$duracion_especial = (int)($_GET['duracion'] ?? 0); // Nueva duración opcional

if (!$proposito_id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_str)) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'mensaje' => 'Parámetros inválidos.']));
}

try {
    $db = DB::getDB();
    
    $fecha_dt = new DateTime($fecha_str);
    $dia_semana = (int)$fecha_dt->format('N');

    // 3. Obtener citas ya agendadas para esa fecha con su duración
    $stmt_booked = $db->prepare("
        SELECT 
            v.responsable_id, -- Necesitamos saber quién está ocupado
            TIME_FORMAT(v.hora_reunion, '%H:%i') as hora_inicio,
            COALESCE(v.duracion_minutos, 60) as duracion_minutos 
        FROM agendamiento_visitas v
        WHERE v.fecha_reunion = :fecha AND v.estado <> 'CANCELADO'
    ");
    $stmt_booked->execute([':fecha' => $fecha_str]);
    $booked_slots_info = $stmt_booked->fetchAll(PDO::FETCH_ASSOC);

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
        // Crear DateTime con fecha y hora correctas (incluyendo segundos)
        $start_time = DateTime::createFromFormat('Y-m-d H:i:s', $fecha_str . ' ' . $rule['hora_inicio']);
        $end_time = DateTime::createFromFormat('Y-m-d H:i:s', $fecha_str . ' ' . $rule['hora_fin']);
        
        // Validar que se crearon correctamente
        if (!$start_time || !$end_time) {
            error_log("ERROR: No se pudo crear DateTime para regla: " . json_encode($rule));
            continue;
        }
        
        $interval = ($duracion_especial > 0) ? $duracion_especial : (int)$rule['intervalo_minutos'];

        if ($interval <= 0) continue;

        $current_slot_dt = clone $start_time;

        while ($current_slot_dt < $end_time) {
            $slot_start_dt = clone $current_slot_dt;
            $slot_end_dt = (clone $slot_start_dt)->add(new DateInterval("PT{$interval}M"));

            if ($slot_end_dt > $end_time) break;

            $slot_str = $slot_start_dt->format('H:i');
            
            // Crear DateTime con formato específico para evitar problemas de interpretación
            $full_slot_dt = DateTime::createFromFormat('Y-m-d H:i', $fecha_str . ' ' . $slot_str);
            if (!$full_slot_dt || $full_slot_dt < $now_plus_buffer) {
                $current_slot_dt->add(new DateInterval("PT{$interval}M"));
                continue;
            }

            // Verificar si ALGÚN responsable está disponible en este horario
            $any_responsable_available = false;
            
            foreach ($availability_rules as $check_rule) {
                $this_responsable_available = true;
                
                // Verificar si este responsable específico tiene colisiones
                foreach ($booked_slots_info as $booked) {
                    if ($booked['responsable_id'] == $check_rule['responsable_id']) {
                        $booked_start = DateTime::createFromFormat('Y-m-d H:i', $fecha_str . ' ' . $booked['hora_inicio']);
                        
                        // Validar que se creó correctamente
                        if (!$booked_start) {
                            error_log("ERROR: No se pudo crear DateTime para cita existente: " . json_encode($booked));
                            continue;
                        }
                        
                        $booked_end = (clone $booked_start)->add(new DateInterval("PT{$booked['duracion_minutos']}M"));

                        // Verificar solapamiento: si hay intersección entre los horarios
                        $hay_solapamiento = false;
                        
                        // Caso 1: El slot empieza durante la cita existente
                        if ($slot_start_dt->getTimestamp() >= $booked_start->getTimestamp() && $slot_start_dt->getTimestamp() < $booked_end->getTimestamp()) {
                            $hay_solapamiento = true;
                        }
                        // Caso 2: El slot termina durante la cita existente
                        elseif ($slot_end_dt->getTimestamp() > $booked_start->getTimestamp() && $slot_end_dt->getTimestamp() <= $booked_end->getTimestamp()) {
                            $hay_solapamiento = true;
                        }
                        // Caso 3: El slot contiene completamente la cita existente
                        elseif ($slot_start_dt->getTimestamp() <= $booked_start->getTimestamp() && $slot_end_dt->getTimestamp() >= $booked_end->getTimestamp()) {
                            $hay_solapamiento = true;
                        }
                        
                        if ($hay_solapamiento) {
                            $this_responsable_available = false;
                            break;
                        }
                    }
                }
                
                // Si este responsable está disponible, marcar el horario como disponible
                if ($this_responsable_available) {
                    $any_responsable_available = true;
                    break;
                }
            }
            
            // Solo marcar como disponible si hay al menos un responsable libre
            $is_collision = !$any_responsable_available;

            if (!$is_collision) {
                if (!isset($available_slots[$slot_str])) {
                    $available_slots[$slot_str] = $rule['responsable_id'];
                }
            }
            
            $current_slot_dt->add(new DateInterval("PT{$interval}M"));
        }
    }

    $final_slots = [];
    foreach ($available_slots as $hora => $responsable_id) {
        $final_slots[] = [
            'hora' => $hora,
            'responsable_id' => $responsable_id
        ];
    }

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