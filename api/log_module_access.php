<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers/audit_helper.php';

header('Content-Type: application/json; charset=utf-8');

// --- Lógica de Autenticación por Token ---
$authenticated_user_id = null;
$authenticated_user_type = null;

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (preg_match('/Bearer\s(.+)/', $authHeader, $matches)) {
    $token = $matches[1];
    try {
        $db = DB::getDB();
        // Buscar en tabla 'usuario'
        $stmt_user = $db->prepare("SELECT id, rol_id FROM usuario WHERE token = :token LIMIT 1");
        $stmt_user->execute([':token' => $token]);
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $authenticated_user_id = (int)$user['id'];
            $authenticated_user_type = 'usuario';
        } else {
            // Buscar en tabla 'responsable'
            $stmt_resp = $db->prepare("SELECT id FROM responsable WHERE token = :token LIMIT 1");
            $stmt_resp->execute([':token' => $token]);
            $responsable = $stmt_resp->fetch(PDO::FETCH_ASSOC);
            if ($responsable) {
                $authenticated_user_id = (int)$responsable['id'];
                $authenticated_user_type = 'responsable';
            }
        }
    } catch (Throwable $e) {
        error_log("Error de autenticación en log_module_access: " . $e->getMessage());
    }
}

if (!$authenticated_user_id) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autenticado']);
    exit();
}
// --- Fin Autenticación ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok'=>false,'mensaje'=>'Método no permitido']));
}

$input = json_decode(file_get_contents('php://input'), true);

$menu_id = filter_var($input['menu_id'] ?? null, FILTER_VALIDATE_INT);
$menu_name = filter_var($input['menu_name'] ?? null, FILTER_SANITIZE_STRING);

if (!$menu_id || !$menu_name) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'ID y nombre del menú son requeridos.']);
    exit;
}

try {
    $db = DB::getDB();
    log_audit_action($db, 'ACCESS_MODULE', $authenticated_user_id, $authenticated_user_type, 'menu', $menu_id, ['menu_name' => $menu_name]);
    echo json_encode(['ok' => true, 'mensaje' => 'Acceso al módulo registrado.']);
} catch (Throwable $e) {
    error_log('Error al registrar acceso a módulo: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor.']);
}

?>