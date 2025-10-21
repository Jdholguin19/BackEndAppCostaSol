<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Cargar la configuración de la base de datos
require_once '../config/db.php';

try {
    $db = DB::getDB();
    
    // 1. VERIFICAR TOKEN
    $headers = getallheaders();
    $token = null;
    $auth_id = null;
    $is_responsable = false;

    if (isset($headers['Authorization'])) {
        $auth_header = $headers['Authorization'];
        if (strpos($auth_header, 'Bearer ') === 0) {
            $token = substr($auth_header, 7);
        }
    }
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token no proporcionado']);
        exit;
    }
    
    $stmt_user = $db->prepare('SELECT id FROM usuario WHERE token = :token AND token IS NOT NULL');
    $stmt_user->execute([':token' => $token]);
    $user = $stmt_user->fetch();

    if ($user) {
        $auth_id = $user['id'];
    } else {
        $stmt_resp = $db->prepare('SELECT id FROM responsable WHERE token = :token AND token IS NOT NULL');
        $stmt_resp->execute([':token' => $token]);
        $responsable = $stmt_resp->fetch();
        if ($responsable) {
            $auth_id = $responsable['id'];
            $is_responsable = true;
        }
    }
    
    if (!$auth_id) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token inválido']);
        exit;
    }

    // 2. OBTENER TIPO DE PROPIEDAD DEL USUARIO (si es usuario normal)
    $tipo_propiedad_id = null;
    $fecha_entrega_str = null; // Inicializar variable
    if (!$is_responsable) {
        $sql_propiedad = "SELECT tp.id as tipo_propiedad_id, p.fecha_entrega
                         FROM propiedad p
                         JOIN tipo_propiedad tp ON p.tipo_id = tp.id
                         WHERE p.id_usuario = :id_usuario AND p.fecha_entrega IS NOT NULL
                         ORDER BY p.fecha_entrega DESC LIMIT 1";
        $stmt_propiedad = $db->prepare($sql_propiedad);
        $stmt_propiedad->execute([':id_usuario' => $auth_id]);
        $propiedad = $stmt_propiedad->fetch(PDO::FETCH_ASSOC);
        if ($propiedad) {
            $tipo_propiedad_id = $propiedad['tipo_propiedad_id'];
            $fecha_entrega_str = $propiedad['fecha_entrega'];
        }
    }

    // 3. OBTENER LISTA DE GARANTÍAS ACTIVAS
    if ($is_responsable) {
        // Responsables ven todas las garantías
        $sql = 'SELECT id, nombre, descripcion, tiempo_garantia_meses, valida_hasta_entrega, tipo_propiedad_id
                FROM garantias
                WHERE estado = 1
                ORDER BY orden ASC, nombre ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute();
    } else {
        // Usuarios normales ven garantías generales + específicas de su tipo
        $sql = 'SELECT id, nombre, descripcion, tiempo_garantia_meses, valida_hasta_entrega, tipo_propiedad_id
                FROM garantias
                WHERE estado = 1
                AND (tipo_propiedad_id IS NULL OR tipo_propiedad_id = :tipo_propiedad_id)
                ORDER BY orden ASC, nombre ASC';
        $stmt = $db->prepare($sql);
        $params = [];
        if ($tipo_propiedad_id !== null) {
            $params['tipo_propiedad_id'] = $tipo_propiedad_id;
        } else {
            // Si no hay tipo_propiedad_id, solo mostrar garantías generales
            $sql = 'SELECT id, nombre, descripcion, tiempo_garantia_meses, valida_hasta_entrega, tipo_propiedad_id
                    FROM garantias
                    WHERE estado = 1 AND tipo_propiedad_id IS NULL
                    ORDER BY orden ASC, nombre ASC';
            $stmt = $db->prepare($sql);
        }
        $stmt->execute($params);
    }

    $garantias = $stmt->fetchAll();
    
    // 4. PROCESAR GARANTÍAS Y CALCULAR VIGENCIA
    $garantias_procesadas = [];
    foreach ($garantias as $garantia) {
        // Formatear duración
        $tiempo_meses = intval($garantia['tiempo_garantia_meses']);
        $valida_hasta_entrega = (int)$garantia['valida_hasta_entrega'];
        
        $duracion_texto = '';
        if ($valida_hasta_entrega) {
            $duracion_texto = 'Válida hasta la entrega';
        } else {
            if ($tiempo_meses >= 12) {
                $anios = floor($tiempo_meses / 12);
                $meses_restantes = $tiempo_meses % 12;
                $duracion_texto = $anios == 1 ? '1 año' : $anios . ' años';
                if ($meses_restantes > 0) {
                    $duracion_texto .= ' y ' . ($meses_restantes == 1 ? '1 mes' : $meses_restantes . ' meses');
                }
            } else {
                $duracion_texto = ($tiempo_meses == 1) ? '1 mes' : $tiempo_meses . ' meses';
            }
        }

        // Calcular vigencia y estado
        $vigencia_texto = 'No aplica';
        $activa = false; // Por defecto, no está activa si no hay fecha de entrega
        $tipo_garantia = 'tiempo'; // 'tiempo' o 'entrega'

        if ($fecha_entrega_str) {
            try {
                if ($valida_hasta_entrega) {
                    // Garantía válida hasta la entrega
                    $fecha_vencimiento = new DateTime($fecha_entrega_str);
                    $vigencia_texto = $fecha_vencimiento->format('d/m/Y');
                    $tipo_garantia = 'entrega';
                    
                    // Comparar con la fecha actual
                    $hoy = new DateTime();
                    $activa = ($fecha_vencimiento >= $hoy->setTime(0, 0, 0));
                } else {
                    // Garantía por tiempo (meses)
                    $fecha_vencimiento = new DateTime($fecha_entrega_str);

                    // Sumar el tiempo de garantía en meses
                    if ($tiempo_meses > 0) {
                        $fecha_vencimiento->add(new DateInterval("P{$tiempo_meses}M"));
                    }

                    $vigencia_texto = $fecha_vencimiento->format('d/m/Y');
                    $tipo_garantia = 'tiempo';

                    // Comparar con la fecha actual para determinar si está activa
                    $hoy = new DateTime();
                    $activa = ($fecha_vencimiento >= $hoy->setTime(0, 0, 0));
                }

            } catch (Exception $e) {
                $vigencia_texto = 'Error';
                $activa = false;
            }
        }

        $garantias_procesadas[] = [
            'id' => $garantia['id'],
            'categoria' => $garantia['nombre'],
            'elemento' => $garantia['nombre'],
            'descripcion' => $garantia['descripcion'] ?: $garantia['nombre'],
            'duracion' => $duracion_texto,
            'vigencia' => $vigencia_texto,
            'activa' => $activa,
            'tipo_garantia' => $tipo_garantia,
            'responsable' => 'Thalia Victoria Constructora'
        ];
    }
    
    echo json_encode(['ok' => true, 'garantias' => $garantias_procesadas]);
    
} catch (Exception $e) {
    error_log("Garantías API - Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al obtener garantías: ' . $e->getMessage()]);
}
?> 