<?php
declare(strict_types=1);
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/db.php';

// 1. Obtener el token del encabezado
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["ok" => false, "mensaje" => "Token no proporcionado"]);
    exit;
}

list(, $token) = explode(' ', $authHeader);

try {
    $conn = DB::getDB();

    // 2. Validar que el token pertenece a un responsable
    $stmt = $conn->prepare("SELECT id FROM responsable WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $responsable = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$responsable) {
        http_response_code(403);
        echo json_encode(["ok" => false, "mensaje" => "Acceso denegado. Se requiere rol de responsable."]);
        exit;
    }

    // 3. Si el token es válido y de un responsable, obtener la lista de usuarios
    $stmt_users = $conn->prepare("SELECT id AS id_usuario, nombres AS nombre, apellidos AS apellido FROM usuario WHERE rol_id IN (1, 2) ORDER BY nombres ASC");
    $stmt_users->execute();

    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["ok" => true, "data" => $users]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "mensaje" => "Error interno del servidor: " . $e->getMessage()]);
    exit;
}
?>
