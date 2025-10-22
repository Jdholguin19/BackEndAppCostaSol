<?php
/**
 *  api/menu.php  – Devuelve los ítems de menú activos.
 *
 *  GET /api/menu.php
 *  Parámetros opcionales:
 *      role_id   → filtra los menús asignados a ese rol (tabla rol_menu).
 *
 *  Respuesta:
 *      200 OK  { "ok": true, "menus": [ { id, nombre, descripcion, url_icono, orden } ] }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = DB::getDB();
    
    // --- Lógica de Autenticación --- //
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = null;
    
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
    }
    
    $authenticated_user = null;
    $is_responsable = false;
    
    if ($token) {
        // Buscar en tabla 'usuario'
        $sql_user = 'SELECT id, nombres, rol_id FROM usuario WHERE token = :token LIMIT 1';
        $stmt_user = $db->prepare($sql_user);
        $stmt_user->execute([':token' => $token]);
        $authenticated_user = $stmt_user->fetch(PDO::FETCH_ASSOC);
        
        if ($authenticated_user) {
            $is_responsable = false;
        } else {
            // Buscar en tabla 'responsable'
            $sql_resp = 'SELECT id, nombre FROM responsable WHERE token = :token LIMIT 1';
            $stmt_resp = $db->prepare($sql_resp);
            $stmt_resp->execute([':token' => $token]);
            $authenticated_user = $stmt_resp->fetch(PDO::FETCH_ASSOC);
            if ($authenticated_user) {
                $is_responsable = true;
            }
        }
    }
    
    $is_admin_responsible_user = false;
    if ($authenticated_user && $is_responsable) {
        $is_admin_responsible_user = true;
    }

    /* --------- Verificar si el usuario es responsable --------- */
    if ($authenticated_user && $is_responsable) {
        // Si es responsable, siempre mostrar garantías
        $mostrar_garantias = true;
    } else {
        // Para usuarios regulares, verificar progreso de construcción
        $mostrar_garantias = false;
        
        // Obtener propiedades del usuario
        if ($authenticated_user && !$is_responsable) { // Solo para usuarios no responsables
            $sql_propiedades = 'SELECT id, manzana, villa FROM propiedad WHERE id_usuario = :user_id';
            $stmt_propiedades = $db->prepare($sql_propiedades);
            $stmt_propiedades->execute([':user_id' => $authenticated_user['id']]);
            $propiedades = $stmt_propiedades->fetchAll();
            
            foreach ($propiedades as $propiedad) {
                $manzana = $propiedad['manzana'];
                $villa = $propiedad['villa'];
                
                // Obtener etapas con progreso para esta propiedad
                $sql_etapas = 'SELECT pc.id_etapa, pc.porcentaje, pc.drive_item_id, ec.nombre, ec.porcentaje as porcentaje_objetivo
                               FROM progreso_construccion pc
                               JOIN etapa_construccion ec ON ec.id = pc.id_etapa
                               WHERE pc.mz = :manzana AND pc.villa = :villa AND pc.estado = 1
                               GROUP BY pc.id_etapa
                               ORDER BY pc.id_etapa';
                
                $stmt_etapas = $db->prepare($sql_etapas);
                $stmt_etapas->execute([':manzana' => $manzana, ':villa' => $villa]);
                $etapas = $stmt_etapas->fetchAll();
                
                // Si no hay datos de progreso, verificar si la propiedad tiene al menos 2 etapas con fotos
                if (count($etapas) == 0) {
                    // Buscar propiedades con fotos pero sin porcentajes
                    $sql_fotos = 'SELECT COUNT(DISTINCT pc.id_etapa) as etapas_con_fotos
                                  FROM progreso_construccion pc
                                  WHERE pc.mz = :manzana AND pc.villa = :villa 
                                  AND pc.estado = 1 AND pc.drive_item_id IS NOT NULL';
                    $stmt_fotos = $db->prepare($sql_fotos);
                    $stmt_fotos->execute([':manzana' => $manzana, ':villa' => $villa]);
                    $etapas_con_fotos = $stmt_fotos->fetch()['etapas_con_fotos'];
                    
                    if ($etapas_con_fotos >= 2) {
                        $mostrar_garantias = true;
                        break;
                    }
                } else {
                    // Obtener todas las etapas disponibles para calcular el total objetivo
                    $sql_todas_etapas = 'SELECT id, nombre, porcentaje FROM etapa_construccion WHERE estado = 1 ORDER BY id';
                    $stmt_todas = $db->prepare($sql_todas_etapas);
                    $stmt_todas->execute();
                    $todas_etapas = $stmt_todas->fetchAll();
                    
                    // Crear un mapa de etapas con progreso real
                    $etapas_con_progreso_real = [];
                    foreach ($etapas as $etapa) {
                        $etapas_con_progreso_real[$etapa['id_etapa']] = $etapa;
                    }
                    
                    // Calcular progreso total
                    $total_progreso_objetivo = 0;
                    $total_progreso_real = 0;
                    
                    foreach ($todas_etapas as $etapa_objetivo) {
                        $porcentaje_objetivo = $etapa_objetivo['porcentaje'];
                        $total_progreso_objetivo += $porcentaje_objetivo;
                        
                        // Verificar si esta etapa tiene progreso real
                        if (isset($etapas_con_progreso_real[$etapa_objetivo['id']])) {
                            $etapa_real = $etapas_con_progreso_real[$etapa_objetivo['id']];
                            $porcentaje_real = $etapa_real['porcentaje'] ?? 0;
                            
                            // Si no hay porcentaje real pero hay fotos, considerar como "en proceso" (50% del objetivo)
                            if ($porcentaje_real == 0 && $etapa_real['drive_item_id']) {
                                $porcentaje_real = $porcentaje_objetivo * 0.5; // 50% del objetivo de la etapa
                            }
                            
                            $total_progreso_real += $porcentaje_real;
                        }
                    }
                    
                    // Calcular porcentaje de progreso general
                    if ($total_progreso_objetivo > 0) {
                        $porcentaje_promedio = ($total_progreso_real / $total_progreso_objetivo) * 100;
                    } else {
                        $porcentaje_promedio = 0;
                    }
                    
                    // Umbral configurable (por defecto 50%)
                    $umbral_garantias = 50; // Se puede hacer configurable desde base de datos
                    
                    if ($porcentaje_promedio >= $umbral_garantias) {
                        $mostrar_garantias = true;
                        break; // Si una propiedad cumple, mostrar garantías
                    }
                }
            }
        }
    }
    
    $role_id = isset($_GET['role_id']) ? (int)$_GET['role_id'] : 0;

    /* --------- Query base --------- */
    $sql = 'SELECT m.id, m.nombre, m.descripcion, m.url_icono, m.orden
              FROM menu m
             WHERE m.estado = 1';

    $params = [];

    /* --------- Filtrar por rol si llega role_id (solo si no es admin responsable) --------- */
    if ($role_id > 0 && !$is_admin_responsible_user) {
        $sql .= ' AND EXISTS (
                     SELECT 1
                       FROM rol_menu rm
                      WHERE rm.menu_id = m.id
                        AND (rm.rol_id = :role_id';
        // Si el rol es Residente (2), incluir también menús de Cliente (1)
        if ($role_id == 2) {
            $sql .= ' OR rm.rol_id = 1';
        }
        $sql .= ')
                  )';
        $params[':role_id'] = $role_id;
    }

    /* --------- Excepción: MCM (id 11) solo para Residentes (rol_id = 2) --------- */
    // Ocultar MCM para responsables y para cualquier rol distinto de 2
    $should_hide_mcm = $is_admin_responsible_user || ($role_id !== 2);
    if ($should_hide_mcm) {
        $sql .= ' AND m.id != 11';
    }
    
    /* --------- Filtrar garantías según progreso de construcción --------- */
    if (!$mostrar_garantias && !$is_admin_responsible_user) {
        $sql .= ' AND m.nombre != "Garantías"';
    }

    $sql .= ' ORDER BY m.orden, m.nombre';

    /* --------- Ejecutar --------- */
    $stmt  = $db->prepare($sql);
    $stmt->execute($params);
    $menus = $stmt->fetchAll();
    
    echo json_encode(['ok' => true, 'menus' => $menus]);
    
} catch (Exception $e) {
    error_log("Menu API - Error: " . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error al obtener menús']);
}