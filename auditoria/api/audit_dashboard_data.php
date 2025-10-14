<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../api/helpers/audit_helper.php';

$db = DB::getDB();

// Función para validar autenticación de responsable
function validateResponsableAuth($db) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return ['valid' => false, 'message' => 'Token de autorización requerido'];
    }
    
    $token = $matches[1];
    
    try {
        $stmt = $db->prepare('SELECT id, nombre FROM responsable WHERE token = ? AND estado = 1');
        $stmt->execute([$token]);
        $responsable = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$responsable) {
            return ['valid' => false, 'message' => 'Token inválido o responsable inactivo'];
        }
        
        return ['valid' => true, 'responsable' => $responsable];
    } catch (Exception $e) {
        return ['valid' => false, 'message' => 'Error de autenticación: ' . $e->getMessage()];
    }
}

// Función para obtener datos de módulos (optimizada)
function getModulesData($db) {
    $modules = [
        'autenticacion' => [
            'name' => 'Autenticación',
            'description' => 'Inicios de sesión y cierres de sesión',
            'actions' => ['LOGIN_SUCCESS', 'LOGIN_FAILURE', 'LOGOUT']
        ],
        'usuario' => [
            'name' => 'Usuarios',
            'description' => 'Gestión de usuarios del sistema',
            'actions' => ['CREATE_USER', 'UPDATE_USER', 'DELETE_USER']
        ],
        'cita' => [
            'name' => 'Citas',
            'description' => 'Agendamiento y gestión de citas',
            'actions' => ['CREATE_CITA', 'CANCEL_CITA', 'UPDATE_CITA_STATUS', 'DELETE_CITA']
        ],
        'ctg' => [
            'name' => 'CTG',
            'description' => 'Gestión de contingencias',
            'actions' => ['CREATE_CTG', 'UPDATE_CTG_STATUS', 'ADD_CTG_RESPONSE', 'UPDATE_CTG_OBSERVATION']
        ],
        'pqr' => [
            'name' => 'PQR',
            'description' => 'Peticiones, quejas y recomendaciones',
            'actions' => ['CREATE_PQR', 'UPDATE_PQR_STATUS', 'ADD_PQR_RESPONSE', 'UPDATE_PQR_OBSERVATION']
        ],
        'acabados' => [
            'name' => 'Acabados',
            'description' => 'Selección de acabados de propiedades',
            'actions' => ['SAVE_ACABADOS']
        ],
        'perfil' => [
            'name' => 'Perfil',
            'description' => 'Gestión de perfil de usuario',
            'actions' => ['UPDATE_PROFILE_PICTURE']
        ],
        'notificaciones' => [
            'name' => 'Notificaciones',
            'description' => 'Gestión de notificaciones push',
            'actions' => ['UPDATE_ONESIGNAL_PLAYER_ID']
        ],
        'acceso_modulo' => [
            'name' => 'Acceso a Módulos',
            'description' => 'Acceso a diferentes módulos del sistema',
            'actions' => ['ACCESS_MODULE']
        ]
    ];
    
    $result = [];
    
    // Optimización: Una sola consulta para obtener todos los conteos
    $allActions = [];
    foreach ($modules as $module) {
        $allActions = array_merge($allActions, $module['actions']);
    }
    $allActions = array_unique($allActions);
    
    // Consulta optimizada con CASE para contar por módulo
    $placeholders = str_repeat('?,', count($allActions) - 1) . '?';
    $sql = "SELECT 
                CASE 
                    WHEN target_resource = 'autenticacion' OR action IN ('LOGIN_SUCCESS', 'LOGIN_FAILURE', 'LOGOUT') THEN 'autenticacion'
                    WHEN target_resource = 'usuario' OR action IN ('CREATE_USER', 'UPDATE_USER', 'DELETE_USER') THEN 'usuario'
                    WHEN target_resource = 'cita' OR action IN ('CREATE_CITA', 'CANCEL_CITA', 'UPDATE_CITA_STATUS', 'DELETE_CITA') THEN 'cita'
                    WHEN target_resource = 'ctg' OR action IN ('CREATE_CTG', 'UPDATE_CTG_STATUS', 'ADD_CTG_RESPONSE', 'UPDATE_CTG_OBSERVATION') THEN 'ctg'
                    WHEN target_resource = 'pqr' OR action IN ('CREATE_PQR', 'UPDATE_PQR_STATUS', 'ADD_PQR_RESPONSE', 'UPDATE_PQR_OBSERVATION') THEN 'pqr'
                    WHEN target_resource = 'acabados' OR action = 'SAVE_ACABADOS' THEN 'acabados'
                    WHEN target_resource = 'perfil' OR action = 'UPDATE_PROFILE_PICTURE' THEN 'perfil'
                    WHEN target_resource = 'notificaciones' OR action = 'UPDATE_ONESIGNAL_PLAYER_ID' THEN 'notificaciones'
                    WHEN target_resource = 'acceso_modulo' OR action = 'ACCESS_MODULE' THEN 'acceso_modulo'
                END as module_resource,
                COUNT(*) as count
            FROM audit_log 
            WHERE (target_resource IN ('autenticacion', 'usuario', 'cita', 'ctg', 'pqr', 'acabados', 'perfil', 'notificaciones', 'acceso_modulo')
               OR action IN ($placeholders))
               AND NOT (target_resource = 'menu' AND target_id = 15)
            GROUP BY module_resource";
    
    $params = $allActions;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Crear array asociativo para acceso rápido
    $countMap = [];
    foreach ($counts as $count) {
        if ($count['module_resource']) {
            $countMap[$count['module_resource']] = $count['count'];
        }
    }
    
    // Construir resultado final
    foreach ($modules as $resource => $module) {
        $result[] = [
            'resource' => $resource,
            'name' => $module['name'],
            'description' => $module['description'],
            'count' => $countMap[$resource] ?? 0
        ];
    }
    
    return $result;
}

// Función para obtener auditorías recientes
function getRecentAudits($db, $limit = 10) {
    $sql = "SELECT al.*, 
                   CASE 
                       WHEN al.user_type = 'usuario' THEN CONCAT(u.nombres, ' ', u.apellidos)
                       WHEN al.user_type = 'responsable' THEN r.nombre
                       ELSE 'Sistema'
                   END as user_name,
                   CASE 
                       WHEN al.user_type = 'usuario' THEN CONCAT(u.nombres, ' ', u.apellidos)
                       WHEN al.user_type = 'responsable' THEN r.nombre
                       ELSE 'Sistema'
                   END as user_display_name
            FROM audit_log al
            LEFT JOIN usuario u ON al.user_type = 'usuario' AND al.user_id = u.id
            LEFT JOIN responsable r ON al.user_type = 'responsable' AND al.user_id = r.id
            ORDER BY al.timestamp DESC
            LIMIT " . intval($limit);
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $audits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar detalles para mejorar la visualización
    foreach ($audits as &$audit) {
        $audit['formatted_details'] = formatAuditDetails($audit['details'], $db, $audit);
    }
    
    return $audits;
}

function getModuleAudits($db, $resource, $filters = [], $offset = 0, $limit = 20) {
    // Construir la consulta base
    $sql = "SELECT al.*, 
                   CASE 
                       WHEN al.user_type = 'usuario' THEN CONCAT(u.nombres, ' ', u.apellidos)
                       WHEN al.user_type = 'responsable' THEN r.nombre
                       ELSE 'Sistema'
                   END as user_name,
                   CASE 
                       WHEN al.user_type = 'usuario' THEN CONCAT(u.nombres, ' ', u.apellidos)
                       WHEN al.user_type = 'responsable' THEN r.nombre
                       ELSE 'Sistema'
                   END as user_display_name
            FROM audit_log al
            LEFT JOIN usuario u ON al.user_type = 'usuario' AND al.user_id = u.id
            LEFT JOIN responsable r ON al.user_type = 'responsable' AND al.user_id = r.id
            WHERE 1=1";
    
    $params = [];
    
    // Filtro por recurso
    if ($resource) {
        $sql .= " AND (al.target_resource = ?";
        $params[] = $resource;
        
        // También incluir acciones específicas del módulo
        $moduleActions = getModuleActions($resource);
        if (!empty($moduleActions)) {
            $placeholders = str_repeat('?,', count($moduleActions) - 1) . '?';
            $sql .= " OR al.action IN ($placeholders)";
            $params = array_merge($params, $moduleActions);
        }
        $sql .= ")";
    }
    
    // Excluir ID Objetivo 15 para acceso a módulos
    if ($resource === 'acceso_modulo') {
        $sql .= " AND NOT (al.target_resource = 'menu' AND al.target_id = 15)";
    }
    
    // Aplicar filtros adicionales
    if (!empty($filters['date_from'])) {
        $sql .= " AND al.timestamp >= ?";
        $params[] = $filters['date_from'] . ' 00:00:00';
    }
    
    if (!empty($filters['date_to'])) {
        $sql .= " AND al.timestamp <= ?";
        $params[] = $filters['date_to'] . ' 23:59:59';
    }
    
    if (!empty($filters['user_type'])) {
        $sql .= " AND al.user_type = ?";
        $params[] = $filters['user_type'];
    }
    
    if (!empty($filters['action'])) {
        $sql .= " AND al.action LIKE ?";
        $params[] = '%' . $filters['action'] . '%';
    }
    
    if (!empty($filters['details'])) {
        // Para acabados, buscar por palabra clave en lugar de coincidencia exacta
        if ($resource === 'acabados') {
            $sql .= " AND al.details LIKE ?";
            $detailsFilter = '%' . $filters['details'] . '%';
            $params[] = $detailsFilter;
            error_log("Filtro de detalles para acabados: " . $detailsFilter);
        } else {
            $sql .= " AND al.details LIKE ?";
            $params[] = '%' . $filters['details'] . '%';
        }
    }
    
    if (!empty($filters['tipo_ctg'])) {
        // Para CTG, filtrar por tipo_id en el JSON de detalles usando JSON_UNQUOTE y JSON_EXTRACT
        $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(al.details, '$.tipo_id')) = ?";
        $params[] = $filters['tipo_ctg'];
    }

    if (!empty($filters['tipo_pqr'])) {
        // Para PQR, filtrar por tipo_id en el JSON de detalles usando JSON_UNQUOTE y JSON_EXTRACT
        $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(al.details, '$.tipo_id')) = ?";
        $params[] = $filters['tipo_pqr'];
    }
    
    if (!empty($filters['target_id'])) {
        $sql .= " AND al.target_id = ?";
        $params[] = $filters['target_id'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (al.details LIKE ? OR al.action LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Contar total de resultados (usar la misma lógica de filtros)
    $countSql = "SELECT COUNT(*) as total FROM audit_log al WHERE 1=1";
    $countParams = [];
    
    if ($resource) {
        $countSql .= " AND (al.target_resource = ?";
        $countParams[] = $resource;
        
        $moduleActions = getModuleActions($resource);
        if (!empty($moduleActions)) {
            $placeholders = str_repeat('?,', count($moduleActions) - 1) . '?';
            $countSql .= " OR al.action IN ($placeholders)";
            $countParams = array_merge($countParams, $moduleActions);
        }
        $countSql .= ")";
    }
    
    // Excluir ID Objetivo 15 para acceso a módulos
    if ($resource === 'acceso_modulo') {
        $countSql .= " AND NOT (al.target_resource = 'menu' AND al.target_id = 15)";
    }
    
    // Aplicar los mismos filtros al count
    if (!empty($filters['date_from'])) {
        $countSql .= " AND al.timestamp >= ?";
        $countParams[] = $filters['date_from'] . ' 00:00:00';
    }
    
    if (!empty($filters['date_to'])) {
        $countSql .= " AND al.timestamp <= ?";
        $countParams[] = $filters['date_to'] . ' 23:59:59';
    }
    
    if (!empty($filters['user_type'])) {
        $countSql .= " AND al.user_type = ?";
        $countParams[] = $filters['user_type'];
    }
    
    if (!empty($filters['action'])) {
        $countSql .= " AND al.action LIKE ?";
        $countParams[] = '%' . $filters['action'] . '%';
    }

    if (!empty($filters['tipo_ctg'])) {
        // Para CTG, filtrar por tipo_id en el JSON de detalles usando JSON_UNQUOTE y JSON_EXTRACT
        $countSql .= " AND JSON_UNQUOTE(JSON_EXTRACT(al.details, '$.tipo_id')) = ?";
        $countParams[] = $filters['tipo_ctg'];
    }

    if (!empty($filters['tipo_pqr'])) {
        // Para PQR, filtrar por tipo_id en el JSON de detalles usando JSON_UNQUOTE y JSON_EXTRACT
        $countSql .= " AND JSON_UNQUOTE(JSON_EXTRACT(al.details, '$.tipo_id')) = ?";
        $countParams[] = $filters['tipo_pqr'];
    }
    
    if (!empty($filters['target_id'])) {
        $countSql .= " AND al.target_id = ?";
        $countParams[] = $filters['target_id'];
    }
    
    if (!empty($filters['search'])) {
        $countSql .= " AND (al.details LIKE ? OR al.action LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
    }
    
    // Ejecutar count
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($countParams);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ordenar y paginar
    $sql .= " ORDER BY al.timestamp DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);
    
    error_log("SQL Query final: " . $sql);
    error_log("SQL Params final: " . json_encode($params));
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $audits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Resultados encontrados: " . count($audits));
    
    // Procesar detalles para mejorar la visualización
    foreach ($audits as &$audit) {
        $audit['formatted_details'] = formatAuditDetails($audit['details'], $db, $audit);
    }
    
    return ['audits' => $audits, 'total' => $total];
}

// Función para obtener todos los datos de auditoría para el gráfico (sin paginación)
function getModuleAuditsForChart($db, $resource, $filters = []) {
    // Construir la consulta base para el gráfico
    $sql = "SELECT al.action, al.details
            FROM audit_log al
            WHERE 1=1";
    
    $params = [];
    
    // Replicar la lógica de filtros de getModuleAudits
    if ($resource) {
        $sql .= " AND (al.target_resource = ?";
        $params[] = $resource;
        
        $moduleActions = getModuleActions($resource);
        if (!empty($moduleActions)) {
            $placeholders = str_repeat('?,', count($moduleActions) - 1) . '?';
            $sql .= " OR al.action IN ($placeholders)";
            $params = array_merge($params, $moduleActions);
        }
        $sql .= ")";
    }
    
    // Excluir ID Objetivo 15 para acceso a módulos
    if ($resource === 'acceso_modulo') {
        $sql .= " AND NOT (al.target_resource = 'menu' AND al.target_id = 15)";
    }
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND al.timestamp >= ?";
        $params[] = $filters['date_from'] . ' 00:00:00';
    }
    
    if (!empty($filters['date_to'])) {
        $sql .= " AND al.timestamp <= ?";
        $params[] = $filters['date_to'] . ' 23:59:59';
    }
    
    if (!empty($filters['user_type'])) {
        $sql .= " AND al.user_type = ?";
        $params[] = $filters['user_type'];
    }
    
    if (!empty($filters['action'])) {
        $sql .= " AND al.action LIKE ?";
        $params[] = '%' . $filters['action'] . '%';
    }
    
    if (!empty($filters['details'])) {
        // Para acabados, buscar por palabra clave en lugar de coincidencia exacta
        if ($resource === 'acabados') {
            $sql .= " AND al.details LIKE ?";
            $detailsFilter = '%' . $filters['details'] . '%';
            $params[] = $detailsFilter;
            error_log("Filtro de detalles para acabados: " . $detailsFilter);
        } else {
            $sql .= " AND al.details LIKE ?";
            $params[] = '%' . $filters['details'] . '%';
        }
    }
    
    if (!empty($filters['tipo_ctg'])) {
        // Para CTG, filtrar por tipo_id en el JSON de detalles usando JSON_UNQUOTE y JSON_EXTRACT
        $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(al.details, '$.tipo_id')) = ?";
        $params[] = $filters['tipo_ctg'];
    }

    if (!empty($filters['tipo_pqr'])) {
        // Para PQR, filtrar por tipo_id en el JSON de detalles usando JSON_UNQUOTE y JSON_EXTRACT
        $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(al.details, '$.tipo_id')) = ?";
        $params[] = $filters['tipo_pqr'];
    }
    
    if (!empty($filters['target_id'])) {
        $sql .= " AND al.target_id = ?";
        $params[] = $filters['target_id'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (al.details LIKE ? OR al.action LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $audits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar detalles para que el gráfico funcione correctamente
    foreach ($audits as &$audit) {
        $audit['formatted_details'] = formatAuditDetails($audit['details'], $db, $audit);
    }
    
    return $audits;
}

// Función para formatear los detalles de auditoría
function formatAuditDetails($details, $db = null, $audit = null) {
    if (!$details) return '-';
    
    try {
        $parsed = is_string($details) ? json_decode($details, true) : $details;
        
        if (!$parsed || !is_array($parsed)) {
            return $details;
        }

        $action = $audit['action'] ?? null;

        // Formato para ADD_PQR_RESPONSE y ADD_CTG_RESPONSE
        if ($action === 'ADD_PQR_RESPONSE' || $action === 'ADD_CTG_RESPONSE') {
            $result = '';
            if (isset($parsed['mensaje'])) {
                $result .= 'Mensaje: ' . htmlspecialchars($parsed['mensaje']);
            }
            if (!empty($parsed['url_adjunto'])) {
                if ($result) $result .= ', ';
                $url = htmlspecialchars($parsed['url_adjunto']);
                $result .= 'Url: <a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>';
            }
            if ($result) return $result;
        }

        // Formato para UPDATE_PQR_STATUS y UPDATE_CTG_STATUS
        if (($action === 'UPDATE_PQR_STATUS' || $action === 'UPDATE_CTG_STATUS') && isset($parsed['new_estado_id'])) {
            try {
                $result = '';
                $table = ($action === 'UPDATE_PQR_STATUS') ? 'estado_pqr' : 'estado_ctg';
                
                // Get new status name
                $stmt_new = $db->prepare("SELECT nombre FROM $table WHERE id = ?");
                $stmt_new->execute([$parsed['new_estado_id']]);
                $new_status_name = $stmt_new->fetchColumn();
                if ($new_status_name) {
                    $result .= 'Estado Actualizado: ' . $new_status_name;
                }

                // Get old status name
                if (isset($parsed['old_estado_id'])) {
                    $stmt_old = $db->prepare("SELECT nombre FROM $table WHERE id = ?");
                    $stmt_old->execute([$parsed['old_estado_id']]);
                    $old_status_name = $stmt_old->fetchColumn();
                    if ($old_status_name) {
                        if ($result) $result .= ', ';
                        $result .= 'Estado Anterior: ' . $old_status_name;
                    }
                }
                if ($result) return $result;
            } catch (Exception $e) {
                error_log("Error formatting status update: " . $e->getMessage());
            }
        }

        // Formato para UPDATE_PQR_OBSERVATION y UPDATE_CTG_OBSERVATION
        if ($action === 'UPDATE_PQR_OBSERVATION' || $action === 'UPDATE_CTG_OBSERVATION') {
            $result = '';
            if (isset($parsed['new_observaciones'])) {
                $result .= 'Nueva Observacion: ' . htmlspecialchars($parsed['new_observaciones']);
            }
            if (!empty($parsed['old_observaciones'])) {
                if ($result) $result .= ', ';
                $result .= 'Antigua Observacion: ' . htmlspecialchars($parsed['old_observaciones']);
            }
            if ($result) return $result;
        }
        
        // Casos especiales para mejorar la visualización
        if (isset($parsed['menu_name'])) {
            return $parsed['menu_name'];
        }
        
        if (isset($parsed['kit_name'])) {
            return $parsed['kit_name'];
        }
        
        // Si tenemos tipo_id, obtener información completa según el recurso
        if (isset($parsed['tipo_id']) && $db && $audit) {
            $resource = $audit['target_resource'] ?? null;
            $action = $audit['action'] ?? null;
            $isCtg = ($resource === 'ctg' || strpos($action, 'CTG') !== false);
            $isPqr = ($resource === 'pqr' || strpos($action, 'PQR') !== false);

            if ($isCtg || $isPqr) {
                try {
                    $result = '';
                    $tableName = $isCtg ? 'tipo_ctg' : 'tipo_pqr';
                    
                    // Obtener nombre del tipo
                    $stmt = $db->prepare("SELECT nombre FROM $tableName WHERE id = ?");
                    $stmt->execute([$parsed['tipo_id']]);
                    $tipo_name = $stmt->fetchColumn();
                    
                    if ($tipo_name) {
                        $result .= "Tipo: " . $tipo_name;
                    }
                    
                    // Agregar descripción si existe
                    if (isset($parsed['descripcion'])) {
                        if ($result) $result .= ", ";
                        $result .= "Descripción: " . $parsed['descripcion'];
                    }
                    
                    // Agregar número de solicitud si existe
                    if (isset($parsed['numero_solicitud'])) {
                        if ($result) $result .= ", ";
                        $result .= "N°: " . $parsed['numero_solicitud'];
                    }
                    
                    if ($result) {
                        return $result;
                    }
                } catch (Exception $e) {
                    // Si hay error, continuar con el procesamiento normal
                    error_log("Error formatting details for tipo_id: " . $e->getMessage());
                }
            }
        }
        
        // Si tenemos kit_id (para acabados), obtener información completa
        if (isset($parsed['kit_id']) && $db) {
            try {
                // Obtener nombre del kit
                $stmt = $db->prepare("SELECT nombre FROM acabado_kit WHERE id = ?");
                $stmt->execute([$parsed['kit_id']]);
                $kit_name = $stmt->fetchColumn();
                
                if ($kit_name) {
                    $result = $kit_name;
                    
                    // Agregar color si existe
                    if (isset($parsed['color_nombre'])) {
                        $result .= " " . $parsed['color_nombre'];
                    }
                    
                    // Agregar paquetes adicionales si existen
                    if (isset($parsed['paquetes_adicionales_ids']) && is_array($parsed['paquetes_adicionales_ids']) && !empty($parsed['paquetes_adicionales_ids'])) {
                        try {
                            $placeholders = implode(',', array_fill(0, count($parsed['paquetes_adicionales_ids']), '?'));
                            $stmt_packages = $db->prepare("SELECT nombre FROM paquetes_adicionales WHERE id IN ($placeholders)");
                            $stmt_packages->execute($parsed['paquetes_adicionales_ids']);
                            $package_names = $stmt_packages->fetchAll(PDO::FETCH_COLUMN);
                            
                            if (!empty($package_names)) {
                                $result .= " + " . implode(", ", $package_names);
                            }
                        } catch (Exception $e) {
                            // Si hay error obteniendo paquetes, continuar sin ellos
                        }
                    }
                    
                    return $result;
                }
            }
            catch (Exception $e) {
                // Si hay error, continuar con el procesamiento normal
            }
        }
        
        // Si tenemos proposito_id (para citas), obtener información completa
        if (isset($parsed['proposito_id'])) {
            $proposito_name = null;
            
            // Intentar obtener el nombre desde la base de datos
            if ($db) {
                try {
                    $stmt = $db->prepare("SELECT proposito FROM proposito_agendamiento WHERE id = ?");
                    $stmt->execute([$parsed['proposito_id']]);
                    $proposito_name = $stmt->fetchColumn();
                } catch (Exception $e) {
                    // Si hay error, usar fallback hardcodeado
                }
            }
            
            // Fallback hardcodeado si no se pudo obtener de la DB
            if (!$proposito_name) {
                $proposito_names = [
                    1 => 'Recorrido de Obra',
                    2 => 'Entrega de Llaves',
                    3 => 'Inspección Técnica',
                    4 => 'Reunión Comercial',
                    5 => 'Visita Técnica',
                    6 => 'Entrega de Documentos'
                ];
                $proposito_name = $proposito_names[$parsed['proposito_id']] ?? 'Propósito ID ' . $parsed['proposito_id'];
            }
            
            $result = $proposito_name;
            
            // Agregar hora si existe
            if (isset($parsed['hora_reunion'])) {
                $result .= ", Hora " . $parsed['hora_reunion'];
            }
            
            // Agregar ID de propiedad si existe
            if (isset($parsed['id_propiedad'])) {
                $result .= ", id_propiedad " . $parsed['id_propiedad'];
            }
            
            // Agregar fecha de reunión si existe
            if (isset($parsed['fecha_reunion'])) {
                $result .= ", fecha de reunión " . $parsed['fecha_reunion'];
            }
            
            // Agregar nombre del responsable si existe
            if (isset($parsed['responsable_id']) && $db) {
                try {
                    $stmt_resp = $db->prepare("SELECT nombre FROM responsable WHERE id = ?");
                    $stmt_resp->execute([$parsed['responsable_id']]);
                    $responsable_name = $stmt_resp->fetchColumn();
                    if ($responsable_name) {
                        $result .= ", (" . $responsable_name . ")";
                    }
                } catch (Exception $e) {
                    // Si hay error obteniendo responsable, continuar sin él
                }
            }
            
            // Agregar duración si existe
            if (isset($parsed['duracion_minutos'])) {
                $result .= ", Tiempo " . $parsed['duracion_minutos'] . " minutos";
            }
            
            return $result;
        }
        
        // Si tenemos deleted_cita_data (para citas eliminadas), obtener información del propósito
        if (isset($parsed['deleted_cita_data']) && is_array($parsed['deleted_cita_data']) && isset($parsed['deleted_cita_data']['proposito_id'])) {
            $proposito_name = null;
            
            // Intentar obtener el nombre desde la base de datos
            if ($db) {
                try {
                    $stmt = $db->prepare("SELECT proposito FROM proposito_agendamiento WHERE id = ?");
                    $stmt->execute([$parsed['deleted_cita_data']['proposito_id']]);
                    $proposito_name = $stmt->fetchColumn();
                } catch (Exception $e) {
                    // Si hay error, usar fallback hardcodeado
                }
            }
            
            // Fallback hardcodeado si no se pudo obtener de la DB
            if (!$proposito_name) {
                $proposito_names = [
                    1 => 'Recorrido de Obra',
                    2 => 'Entrega de Llaves',
                    3 => 'Inspección Técnica',
                    4 => 'Reunión Comercial',
                    5 => 'Visita Técnica',
                    6 => 'Entrega de Documentos'
                ];
                $proposito_name = $proposito_names[$parsed['deleted_cita_data']['proposito_id']] ?? 'Propósito ID ' . $parsed['deleted_cita_data']['proposito_id'];
            }
            
            $result = $proposito_name . " (Eliminada)";
            
            // Agregar hora si existe
            if (isset($parsed['deleted_cita_data']['hora_reunion'])) {
                $result .= ", Hora " . $parsed['deleted_cita_data']['hora_reunion'];
            }
            
            // Agregar fecha de reunión si existe
            if (isset($parsed['deleted_cita_data']['fecha_reunion'])) {
                $result .= ", fecha de reunión " . $parsed['deleted_cita_data']['fecha_reunion'];
            }
            
            // Agregar nombre del responsable si existe
            if (isset($parsed['deleted_cita_data']['responsable_id']) && $db) {
                try {
                    $stmt_resp = $db->prepare("SELECT nombre FROM responsable WHERE id = ?");
                    $stmt_resp->execute([$parsed['deleted_cita_data']['responsable_id']]);
                    $responsable_name = $stmt_resp->fetchColumn();
                    if ($responsable_name) {
                        $result .= ", (" . $responsable_name . ")";
                    }
                } catch (Exception $e) {
                    // Si hay error obteniendo responsable, continuar sin él
                }
            }
            
            // Agregar duración si existe
            if (isset($parsed['deleted_cita_data']['duracion_minutos'])) {
                $result .= ", Tiempo " . $parsed['deleted_cita_data']['duracion_minutos'] . " minutos";
            }
            
            return $result;
        }
        
        // Si tenemos reason (para citas canceladas), mostrar el motivo
        if (isset($parsed['reason'])) {
            return $parsed['reason'];
        }
        
        if (isset($parsed['responsable_name'])) {
            return 'Responsable: ' . $parsed['responsable_name'];
        }
        
        if (isset($parsed['dashboard'])) {
            return 'Dashboard: ' . $parsed['dashboard'];
        }
        
        // Para otros casos, mostrar los valores más importantes
        $importantKeys = ['old_status', 'new_status', 'old_value', 'new_value', 'property_name', 'kit_name', 'color_name'];
        $formatted = [];
        
        foreach ($parsed as $key => $value) {
            if (in_array($key, $importantKeys)) {
                $formatted[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
            }
        }
        
        return empty($formatted) ? json_encode($parsed) : implode(', ', $formatted);
        
    } catch (Exception $e) {
        return $details;
    }
}

// Función para obtener acciones específicas de un módulo
function getModuleActions($resource) {
    $moduleActions = [
        'autenticacion' => ['LOGIN_SUCCESS', 'LOGIN_FAILURE', 'LOGOUT'],
        'usuario' => ['CREATE_USER', 'UPDATE_USER', 'DELETE_USER'],
        'cita' => ['CREATE_CITA', 'CANCEL_CITA', 'UPDATE_CITA_STATUS', 'DELETE_CITA'],
        'ctg' => ['CREATE_CTG', 'UPDATE_CTG_STATUS', 'ADD_CTG_RESPONSE', 'UPDATE_CTG_OBSERVATION'],
        'pqr' => ['CREATE_PQR', 'UPDATE_PQR_STATUS', 'ADD_PQR_RESPONSE', 'UPDATE_PQR_OBSERVATION'],
        'acabados' => ['SAVE_ACABADOS'],
        'perfil' => ['UPDATE_PROFILE_PICTURE'],
        'notificaciones' => ['UPDATE_ONESIGNAL_PLAYER_ID'],
        'acceso_modulo' => ['ACCESS_MODULE']
    ];
    
    return $moduleActions[$resource] ?? [];
}

// Función para obtener los tipos de PQR desde la base de datos
function getPQRTipos($db) {
    try {
        $sql = "SELECT id, nombre FROM tipo_pqr ORDER BY nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $tipos;
    } catch (Exception $e) {
        error_log("Error al obtener tipos de PQR: " . $e->getMessage());
        return [];
    }
}

// Función para obtener los tipos de CTG desde la base de datos
function getCTGTipos($db) {
    try {
        $sql = "SELECT id, nombre FROM tipo_ctg ORDER BY nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $tipos;
    }
    catch (Exception $e) {
        error_log("Error al obtener tipos de CTG: " . $e->getMessage());
        return [];
    }
}


// Función para obtener las palabras clave de acabados desde los detalles de auditorías
function getAcabadosKits($db) {
    try {
        // Obtener todos los detalles de auditorías de acabados (SAVE_ACABADOS)
        $sql = "SELECT DISTINCT details FROM audit_log 
                WHERE action = 'SAVE_ACABADOS' 
                AND details IS NOT NULL 
                AND details != ''
                ORDER BY details";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $details = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        error_log("Detalles encontrados para SAVE_ACABADOS: " . count($details));
        error_log("Primeros detalles: " . json_encode(array_slice($details, 0, 3)));
        
        $keywords = [];
        
        foreach ($details as $detail) {
            // Decodificar JSON si es necesario
            $decoded = json_decode($detail, true);
            if ($decoded && isset($decoded['kit_name'])) {
                $kitName = $decoded['kit_name'];
            } elseif ($decoded && isset($decoded['kit_id'])) {
                // Si no tiene kit_name pero tiene kit_id, construir el nombre
                $kitId = $decoded['kit_id'];
                $colorNombre = $decoded['color_nombre'] ?? '';
                
                // Mapear kit_id a nombre (basado en los datos del audit.sql)
                if ($kitId == 1) {
                    $kitName = 'Cocina Standar' . ($colorNombre ? ' ' . $colorNombre : '');
                } elseif ($kitId == 2) {
                    $kitName = 'Cocina Full' . ($colorNombre ? ' ' . $colorNombre : '');
                } else {
                    $kitName = 'Kit ' . $kitId . ($colorNombre ? ' ' . $colorNombre : '');
                }
            } else {
                $kitName = $detail;
            }
            
            // Extraer palabras clave principales (case insensitive)
            $kitNameLower = strtolower($kitName);
            error_log("Procesando kit: '$kitName' -> '$kitNameLower'");
            
            if (strpos($kitNameLower, 'full') !== false) {
                $keywords['Full'] = 'Full';
                error_log("Agregado keyword: Full");
            }
            if (strpos($kitNameLower, 'standar') !== false || strpos($kitNameLower, 'estandar') !== false) {
                $keywords['Standar'] = 'Standar';
                error_log("Agregado keyword: Standar");
            }
            if (strpos($kitNameLower, 'premium') !== false) {
                $keywords['Premium'] = 'Premium';
                error_log("Agregado keyword: Premium");
            }
            if (strpos($kitNameLower, 'básico') !== false || strpos($kitNameLower, 'basico') !== false) {
                $keywords['Básico'] = 'Básico';
                error_log("Agregado keyword: Básico");
            }
            if (strpos($kitNameLower, 'deluxe') !== false) {
                $keywords['Deluxe'] = 'Deluxe';
                error_log("Agregado keyword: Deluxe");
            }
        }
        
        // Si no se encontraron palabras clave, usar los detalles completos como opciones
        if (empty($keywords)) {
            foreach ($details as $detail) {
                $decoded = json_decode($detail, true);
                if ($decoded && isset($decoded['kit_name'])) {
                    $kitName = $decoded['kit_name'];
                } else {
                    $kitName = $detail;
                }
                
                // Limitar la longitud del nombre para el combo box
                $shortName = strlen($kitName) > 30 ? substr($kitName, 0, 30) . '...' : $kitName;
                $keywords[$kitName] = $shortName;
            }
        }
        
        // Convertir a formato esperado por el frontend
        $result = [];
        foreach ($keywords as $key => $value) {
            $result[] = ['nombre' => $value];
        }
        
        error_log("Keywords generadas: " . json_encode($result));
        
        return $result;
    } catch (Exception $e) {
        error_log("Error al obtener palabras clave de acabados: " . $e->getMessage());
        return [];
    }
}

// Procesar la solicitud
try {
    // Validar autenticación
    $auth = validateResponsableAuth($db);
    if (!$auth['valid']) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'mensaje' => $auth['message']]);
        exit;
    }
    
    // Obtener datos de la solicitud
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    
    switch ($action) {
        case 'get_modules_data':
            $modules = getModulesData($db);
            echo json_encode([
                'ok' => true,
                'modules' => $modules
            ]);
            break;
            
        case 'get_recent_audits':
            $audits = getRecentAudits($db, 10);
            echo json_encode([
                'ok' => true,
                'audits' => $audits
            ]);
            break;
            
        case 'get_module_audits':
            $resource = $input['resource'] ?? '';
            $filters = [
                'date_from' => $input['date_from'] ?? '',
                'date_to' => $input['date_to'] ?? '',
                'user_type' => $input['user_type'] ?? '',
                'action' => $input['action_filter'] ?? '', // Usar action_filter en lugar de action
                'details' => $input['details_filter'] ?? '', // Nuevo filtro para detalles
                'tipo_ctg' => $input['tipo_ctg_filter'] ?? '',
                'tipo_pqr' => $input['tipo_pqr_filter'] ?? '',
                'target_id' => $input['target_id'] ?? '',
                'search' => $input['search'] ?? ''
            ];
            $offset = intval($input['offset'] ?? 0);
            $limit = intval($input['limit'] ?? 20);
            
            $result = getModuleAudits($db, $resource, $filters, $offset, $limit);
            $chartData = getModuleAuditsForChart($db, $resource, $filters);

            echo json_encode([
                'ok' => true,
                'audits' => $result['audits'],
                'total' => $result['total'],
                'chart_data' => $chartData
            ]);
            break;
            
        case 'get_acabados_kits':
            $kits = getAcabadosKits($db);
            echo json_encode([
                'ok' => true,
                'kits' => $kits,
                'debug' => [
                    'total_kits' => count($kits),
                    'kits_found' => $kits
                ]
            ]);
            break;
        
        case 'get_ctg_tipos':
            $tipos = getCTGTipos($db);
            echo json_encode([
                'ok' => true,
                'tipos' => $tipos
            ]);
            break;

        case 'get_pqr_tipos':
            $tipos = getPQRTipos($db);
            echo json_encode([
                'ok' => true,
                'tipos' => $tipos
            ]);
            break;
            
            
        default:
            http_response_code(400);
            echo json_encode([
                'ok' => false, 
                'mensaje' => 'Acción no válida: ' . $action,
                'received_action' => $action,
                'available_actions' => ['get_modules_data', 'get_recent_audits', 'get_module_audits', 'get_comparison_data']
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>
