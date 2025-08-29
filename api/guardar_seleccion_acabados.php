<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';

// --- Lógica de Autenticación por Token ---
$auth_user_id = null;
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    try {
        $conn_auth = DB::getDB();
        // Solo los usuarios clientes/residentes pueden guardar acabados.
        $stmt = $conn_auth->prepare('SELECT id FROM usuario WHERE token = :token AND rol_id IN (1, 2)');
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $auth_user_id = (int)$user['id'];
        }
    } catch (Exception $e) {
        // Silencio en caso de error
    }
}

if ($auth_user_id === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado para realizar esta acción']);
    exit();
}
// --- Fin Autenticación ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok'=>false,'mensaje'=>'Método no permitido']));
}

$input = json_decode(file_get_contents('php://input'), true);

$propiedad_id = filter_var($input['propiedad_id'] ?? null, FILTER_VALIDATE_INT);
$kit_id = filter_var($input['kit_id'] ?? null, FILTER_VALIDATE_INT);
$color = filter_var($input['color'] ?? null, FILTER_SANITIZE_STRING);

if (!$propiedad_id || !$kit_id || !$color) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Datos incompletos. Se requiere propiedad, kit y color.']);
    exit;
}

try {
    $conn = DB::getDB();

    // Verificación: Asegurarse de que la propiedad pertenece al usuario autenticado.
    $stmt_verify = $conn->prepare("SELECT id FROM propiedad WHERE id = :propiedad_id AND id_usuario = :user_id");
    $stmt_verify->execute([':propiedad_id' => $propiedad_id, ':user_id' => $auth_user_id]);
    if ($stmt_verify->fetch() === false) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'mensaje' => 'No tiene permiso para modificar esta propiedad.']);
        exit;
    }

    // Actualizar la propiedad con la selección
    $stmt_update = $conn->prepare(
        "UPDATE propiedad 
         SET acabado_kit_seleccionado_id = :kit_id, acabado_color_seleccionado = :color 
         WHERE id = :propiedad_id AND id_usuario = :user_id"
    );

    $stmt_update->execute([
        ':kit_id' => $kit_id,
        ':color' => $color,
        ':propiedad_id' => $propiedad_id,
        ':user_id' => $auth_user_id
    ]);

    if ($stmt_update->rowCount() > 0) {
        echo json_encode(['ok' => true, 'mensaje' => 'Selección guardada correctamente.']);
    } else {
        echo json_encode(['ok' => true, 'mensaje' => 'No se realizaron cambios, es posible que la selección ya estuviera guardada.']);
    }

} catch (Throwable $e) {
    error_log('guardar_seleccion_acabados: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor al guardar la selección.']);
}
?>