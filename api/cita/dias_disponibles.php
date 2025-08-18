<?php
require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Aunque proposito_id no se usa en esta lógica, se mantiene por compatibilidad futura
$proposito_id = (int)($_GET['proposito_id'] ?? 0);
$year = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? date('m'));

if (!$proposito_id || !$year || !$month) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'mensaje' => 'Parámetros inválidos.']));
}

try {
    $db = DB::getDB();
    
    // 1. Obtener todas las reglas de disponibilidad activas
    $stmt_rules = $db->query("SELECT * FROM responsable_disponibilidad WHERE activo = 1");
    $availability_rules = $stmt_rules->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener todas las citas agendadas para el mes y año dados
    $stmt_appts = $db->prepare("
        SELECT 
            DATE(fecha_reunion) as fecha, 
            responsable_id, 
            TIME_FORMAT(hora_reunion, '%H:%i') as hora 
        FROM agendamiento_visitas 
        WHERE YEAR(fecha_reunion) = :year 
          AND MONTH(fecha_reunion) = :month
          AND estado <> 'CANCELADO'
    ");
    $stmt_appts->execute([':year' => $year, ':month' => $month]);
    $appointments = $stmt_appts->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar citas por fecha y responsable para una búsqueda rápida
    $booked_slots = [];
    foreach ($appointments as $appt) {
        $booked_slots[$appt['fecha']][$appt['responsable_id']][] = $appt['hora'];
    }

    $available_dates = [];
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $today = date('Y-m-d');

    // 3. Iterar sobre cada día del mes
    for ($day = 1; $day <= $days_in_month; $day++) {
        $current_date_str = sprintf('%d-%02d-%02d', $year, $month, $day);
        
        // Omitir fechas pasadas
        if ($current_date_str < $today) {
            continue;
        }

        $current_dt = new DateTime($current_date_str);
        $day_of_week = $current_dt->format('N'); // 1 (Lunes) a 7 (Domingo)

        // 4. Verificar si el día tiene alguna disponibilidad
        $is_day_available = false;
        foreach ($availability_rules as $rule) {
            // Comprobar si la regla se aplica a este día de la semana y rango de fechas
            $vigencia_desde = $rule['fecha_vigencia_desde'];
            $vigencia_hasta = $rule['fecha_vigencia_hasta'] ?? '2999-12-31';

            if ($rule['dia_semana'] == $day_of_week && $current_date_str >= $vigencia_desde && $current_date_str <= $vigencia_hasta) {
                
                // Calcular el total de cupos posibles para esta regla
                $start_time = new DateTime($rule['hora_inicio']);
                $end_time = new DateTime($rule['hora_fin']);
                $interval = (int)$rule['intervalo_minutos'];
                if ($interval == 0) continue; // Evitar división por cero

                $total_seconds = $end_time->getTimestamp() - $start_time->getTimestamp();
                $total_slots_for_rule = floor($total_seconds / ($interval * 60));

                // Obtener los cupos ya reservados para este responsable en este día
                $booked_count = 0;
                if (isset($booked_slots[$current_date_str][$rule['responsable_id']])) {
                    $booked_count = count($booked_slots[$current_date_str][$rule['responsable_id']]);
                }

                if ($total_slots_for_rule > $booked_count) {
                    $is_day_available = true;
                    break; // Se encontró un cupo, el día está disponible. No es necesario seguir buscando.
                }
            }
        }

        if ($is_day_available) {
            $available_dates[] = $current_date_str;
        }
    }

    echo json_encode(['ok' => true, 'items' => $available_dates]);

} catch (Throwable $e) {
    http_response_code(500);
    error_log("dias_disponibles.php: " . $e->getMessage());
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor.']);
}
?>