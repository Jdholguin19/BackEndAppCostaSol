<?php
/**
 * validate_responsable.php - Endpoint para validar si un token pertenece a un responsable
 * POST: { "token": "..." }
 * Response: { "ok": true/false, "responsable": {...} }
 */

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok'=>false,'mensaje'=>'Método no permitido']));
}

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? null;

if (!$token) {
    http_response_code(422);
    exit(json_encode(['ok'=>false,'mensaje'=>'Token requerido']));
}

try {
    $db = DB::getDB();
    
    // Verificar si el token pertenece a un responsable activo
    $stmt = $db->prepare('SELECT id, nombre, correo, area FROM responsable WHERE token = ? AND estado = 1');
    $stmt->execute([$token]);
    $responsable = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($responsable) {
        echo json_encode([
            'ok' => true,
            'responsable' => $responsable
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'mensaje' => 'Token inválido o responsable no encontrado'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Error en validate_responsable: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'mensaje'=>'Error interno']);
}
?> 