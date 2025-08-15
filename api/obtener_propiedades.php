<?php
/**
 *  obtener_propiedades.php  – Lista las propiedades de un cliente
 *
 *  GET /app/obtener_propiedades.php?id_usuario=7
 *
 *  Respuesta:
 *  200 OK
 *  {
 *    "ok": true,
 *    "propiedades": [
 *      {
 *        "id": 12,
 *        "tipo":      "Casa",
 *        "urbanizacion": "Basilea",
 *        "estado":    "En construcción",
 *        "etapa":     "Estructura",
 *        "fecha_compra":      "2025-02-01",
 *        "fecha_entrega":     "2026-06-13",
 *        "manzana":   "3351",
 *        "solar":     "7",
 *        "villa":     "7"
 *      },
 *      ...
 *    ]
 *  }
 */

 header('Content-Type: application/json; charset=utf-8');
 require_once __DIR__ . '/../config/db.php';
 
 // --- Lógica de Autenticación por Token ---
 $auth_id = null;
 $is_responsable = false;
 
 $headers = getallheaders();
 $authHeader = $headers['Authorization'] ?? '';
 if (strpos($authHeader, 'Bearer ') === 0) {
     $token = substr($authHeader, 7);
     try {
         $db = DB::getDB();
         
         // 1. Buscar en la tabla de usuarios
         $stmt = $db->prepare('SELECT id FROM usuario WHERE token = :token');
         $stmt->execute([':token' => $token]);
         $user = $stmt->fetch(PDO::FETCH_ASSOC);
 
         if ($user) {
             $auth_id = $user['id'];
             $is_responsable = false;
         } else {
             // 2. Si no se encuentra, buscar en la tabla de responsables
             $stmt_resp = $db->prepare('SELECT id FROM responsable WHERE token = :token');
             $stmt_resp->execute([':token' => $token]);
             $responsable = $stmt_resp->fetch(PDO::FETCH_ASSOC);
             if ($responsable) {
                 $auth_id = $responsable['id'];
                 $is_responsable = true;
             }
         }
     } catch (Exception $e) {
         error_log('Token validation error: ' . $e->getMessage());
     }
 }
 
 if ($auth_id === null) {
     http_response_code(401);
     echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
     exit();
 }
 // --- Fin Autenticación ---
 
 try {
     $db = DB::getDB();
 
     $sql = 'SELECT
               p.id,
               tp.nombre          AS tipo,
               u.nombre           AS urbanizacion,
               ep.nombre          AS estado,
               ec.nombre          AS etapa,
               p.fecha_compra,
               p.fecha_entrega,
               p.manzana,
               p.solar,
               p.villa
             FROM propiedad              p
             JOIN tipo_propiedad         tp ON tp.id  = p.tipo_id
             JOIN urbanizacion           u  ON u.id   = p.id_urbanizacion
             JOIN estado_propiedad       ep ON ep.id  = p.estado_id
             LEFT JOIN etapa_construccion ec ON ec.id = p.etapa_id';
 
     $params = [];
 
     if (!$is_responsable) {
         // Si no es responsable, filtrar por el ID del usuario autenticado
         $sql .= ' WHERE p.id_usuario = :uid';
         $params[':uid'] = $auth_id;
     }
 
     $sql .= ' ORDER BY p.fecha_compra DESC';
 
     $stmt = $db->prepare($sql);
     $stmt->execute($params);
 
     $props = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
     echo json_encode(['ok' => true, 'propiedades' => $props]);
 
 } catch (Throwable $e) {
     error_log('obtener_propiedades: '.$e->getMessage());
     http_response_code(500);
     echo json_encode(['ok' => false, 'mensaje' => 'Error interno']);
 }
 ?>