<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers/audit_helper.php'; // Incluir el helper de auditoría
require_once __DIR__ . '/../correos/EnviarCorreoNotificacionResponsable.php';

// --- Lógica de Autenticación por Token ---
$auth_user_id = null;
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    try {
        $conn_auth = DB::getDB();
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
$color_nombre = filter_var($input['color'] ?? null, FILTER_SANITIZE_STRING);
$paquetes_adicionales_ids = $input['paquetes_adicionales'] ?? [];

if (!$propiedad_id || !$kit_id || !$color_nombre) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Datos incompletos. Se requiere propiedad, kit y color.']);
    exit;
}

if (!is_array($paquetes_adicionales_ids)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'El formato de los paquetes adicionales es incorrecto.']);
    exit;
}

$conn = DB::getDB();

try {
    $stmt_verify = $conn->prepare("SELECT id FROM propiedad WHERE id = :propiedad_id AND id_usuario = :user_id");
    $stmt_verify->execute([':propiedad_id' => $propiedad_id, ':user_id' => $auth_user_id]);
    if ($stmt_verify->fetch() === false) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'mensaje' => 'No tiene permiso para modificar esta propiedad.']);
        exit;
    }

    $conn->beginTransaction();

    $stmt_update = $conn->prepare(
        "UPDATE propiedad SET acabado_kit_seleccionado_id = :kit_id, acabado_color_seleccionado = :color WHERE id = :propiedad_id AND id_usuario = :user_id"
    );
    $stmt_update->execute([
        ':kit_id' => $kit_id,
        ':color' => $color_nombre,
        ':propiedad_id' => $propiedad_id,
        ':user_id' => $auth_user_id
    ]);

    $stmt_delete_paquetes = $conn->prepare("DELETE FROM propiedad_paquetes_adicionales WHERE propiedad_id = :propiedad_id");
    $stmt_delete_paquetes->execute([':propiedad_id' => $propiedad_id]);

    if (!empty($paquetes_adicionales_ids)) {
        $stmt_insert_paquete = $conn->prepare(
            "INSERT INTO propiedad_paquetes_adicionales (propiedad_id, paquete_id) VALUES (:propiedad_id, :paquete_id)"
        );
        foreach ($paquetes_adicionales_ids as $paquete_id) {
            $paquete_id_sanitized = filter_var($paquete_id, FILTER_VALIDATE_INT);
            if ($paquete_id_sanitized) {
                $stmt_insert_paquete->execute([
                    ':propiedad_id' => $propiedad_id,
                    ':paquete_id' => $paquete_id_sanitized
                ]);
            }
        }
    }

    $conn->commit();

    log_audit_action($conn, 'SAVE_ACABADOS', $auth_user_id, 'usuario', 'propiedad', $propiedad_id, ['kit_id' => $kit_id, 'color_nombre' => $color_nombre, 'paquetes_adicionales_ids' => $paquetes_adicionales_ids]); // Log de auditoría

    // Enviar correo después de confirmar la transacción
    try {
        // 1. Obtener datos para el correo
        $stmt_user = $conn->prepare("SELECT nombres, apellidos FROM usuario WHERE id = :user_id");
        $stmt_user->execute([':user_id' => $auth_user_id]);
        $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

        $stmt_prop = $conn->prepare("SELECT manzana, villa FROM propiedad WHERE id = :propiedad_id");
        $stmt_prop->execute([':propiedad_id' => $propiedad_id]);
        $prop_data = $stmt_prop->fetch(PDO::FETCH_ASSOC);

        $stmt_kit = $conn->prepare("SELECT nombre, costo FROM acabado_kit WHERE id = :kit_id");
        $stmt_kit->execute([':kit_id' => $kit_id]);
        $kit_data = $stmt_kit->fetch(PDO::FETCH_ASSOC);

        $stmt_color = $conn->prepare("SELECT nombre_opcion FROM kit_color_opcion WHERE acabado_kit_id = :kit_id AND color_nombre = :color_nombre");
        $stmt_color->execute([':kit_id' => $kit_id, ':color_nombre' => $color_nombre]);
        $color_data = $stmt_color->fetch(PDO::FETCH_ASSOC);

        $paquetes_data = [];
        if (!empty($paquetes_adicionales_ids)) {
            $placeholders = implode(',', array_fill(0, count($paquetes_adicionales_ids), '?'));
            $stmt_paquetes = $conn->prepare("SELECT nombre, precio FROM paquetes_adicionales WHERE id IN ($placeholders)");
            $stmt_paquetes->execute($paquetes_adicionales_ids);
            $paquetes_data = $stmt_paquetes->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt_resp = $conn->prepare("SELECT correo FROM responsable WHERE id = 1");
        $stmt_resp->execute();
        $responsable_data = $stmt_resp->fetch(PDO::FETCH_ASSOC);

        if ($responsable_data) {
            $datosParaCorreo = [
                'nombreCliente' => $user_data['nombres'] . ' ' . $user_data['apellidos'],
                'nombrePropiedad' => 'Manzana: ' . $prop_data['manzana'] . ', Villa: ' . $prop_data['villa'],
                'kit' => ['nombre' => $kit_data['nombre'], 'costo' => (float)$kit_data['costo']],
                'color' => ['nombre' => $color_data['nombre_opcion']],
                'paquetes' => $paquetes_data
            ];
            enviarNotificacionAcabados($responsable_data['correo'], $datosParaCorreo);
        }

    } catch (Throwable $e) {
        error_log('Error al intentar enviar correo de notificación de acabados: ' . $e->getMessage());
    }

    echo json_encode(['ok' => true, 'mensaje' => 'Selección guardada correctamente.']);

} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('guardar_seleccion_acabados: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor al guardar la selección.']);
}
?>