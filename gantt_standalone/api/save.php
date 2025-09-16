<?php
// save.php - Maneja las operaciones CRUD (crear, actualizar, eliminar) para DHTMLX Gantt

require_once '../config/db.php'; // Incluye la configuración de la base de datos

// --- Debugging ---
error_log("--- save.php received request ---");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST Data: " . print_r($_POST, true));
error_log("Raw Input: " . file_get_contents('php://input'));
error_log("--- End save.php debug ---");
// --- End Debugging ---

header('Content-Type: application/json');

// Get the temporary ID from the 'ids' parameter
$temp_id = isset($_POST['ids']) ? $_POST['ids'] : '';

// Construct the actual keys based on the temporary ID
$action_key = $temp_id . '_' . '!nativeeditor_status';
$id_key = $temp_id . '_' . 'id';

// Retrieve action and ID using the constructed keys
$action = isset($_POST[$action_key]) ? $_POST[$action_key] : '';
$id = isset($_POST[$id_key]) ? $_POST[$id_key] : '';

$response = ['action' => $action, 'sid' => $temp_id]; // sid is the temporary ID sent by the client

// Initialize $stmt to null to prevent "Undefined variable" warning if no case matches
$stmt = null;

try {
    switch ($action) {
        case 'inserted':
            // All other task properties are also prefixed
            $project_id = isset($_POST[$temp_id . '_project_id']) ? (int)$_POST[$temp_id . '_project_id'] : (isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0);
            if ($project_id === 0) {
                throw new Exception("project_id no proporcionado para la nueva tarea.");
            }

            $text = htmlspecialchars($_POST[$temp_id . '_text']);
            $start_date = htmlspecialchars($_POST[$temp_id . '_start_date']);
            $duration = htmlspecialchars($_POST[$temp_id . '_duration']);
            $progress = htmlspecialchars($_POST[$temp_id . '_progress']);
            $parent = htmlspecialchars($_POST[$temp_id . '_parent']);
            // Provide default values if not present in POST
            $sortorder = isset($_POST[$temp_id . '_sortorder']) ? htmlspecialchars($_POST[$temp_id . '_sortorder']) : 10; // Default sortorder
            $open = isset($_POST[$temp_id . '_open']) ? htmlspecialchars($_POST[$temp_id . '_open']) : 1; // Default open (1 for true)
            $owners = isset($_POST[$temp_id . '_owners']) ? htmlspecialchars($_POST[$temp_id . '_owners']) : NULL;
            $color = isset($_POST[$temp_id . '_color']) ? htmlspecialchars($_POST[$temp_id . '_color']) : '#3498db'; // Default color

            $stmt = $conn->prepare("INSERT INTO gantt_tasks (project_id, text, start_date, duration, progress, parent, sortorder, open, owners, color) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdiisiss", $project_id, $text, $start_date, $duration, $progress, $parent, $sortorder, $open, $owners, $color);
            $stmt->execute();
            $response['tid'] = $stmt->insert_id; // tid is the real ID from the database
            break;

        case 'updated':
            // Sanitize and cast all inputs to their correct types
            $text = htmlspecialchars($_POST[$temp_id . '_text']);
            $start_date = htmlspecialchars($_POST[$temp_id . '_start_date']);
            $duration = (float)$_POST[$temp_id . '_duration'];
            $progress = (float)$_POST[$temp_id . '_progress'];
            $parent = (int)$_POST[$temp_id . '_parent'];
            $sortorder = (int)$_POST[$temp_id . '_sortorder'];
            $open = (int)$_POST[$temp_id . '_open'];
            $owners = isset($_POST[$temp_id . '_owners']) ? htmlspecialchars($_POST[$temp_id . '_owners']) : NULL;
            $color = isset($_POST[$temp_id . '_color']) ? htmlspecialchars($_POST[$temp_id . '_color']) : '#3498db'; // Default color
            $id = (int)$id;

            $stmt = $conn->prepare("UPDATE gantt_tasks SET text=?, start_date=?, duration=?, progress=?, parent=?, sortorder=?, open=?, owners=?, color=? WHERE id=?");
            $stmt->bind_param("ssddiiissi", $text, $start_date, $duration, $progress, $parent, $sortorder, $open, $owners, $color, $id);
            $stmt->execute();
            break;

        case 'deleted':
            $stmt = $conn->prepare("DELETE FROM gantt_tasks WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            break;

        case 'inserted_link':
            $source = htmlspecialchars($_POST[$temp_id . '_source']);
            $target = htmlspecialchars($_POST[$temp_id . '_target']);
            $type = htmlspecialchars($_POST[$temp_id . '_type']);

            $stmt = $conn->prepare("INSERT INTO gantt_links (source, target, type) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $source, $target, $type);
            $stmt->execute();
            $response['tid'] = $stmt->insert_id;
            break;

        case 'updated_link':
            $source = htmlspecialchars($_POST[$temp_id . '_source']);
            $target = htmlspecialchars($_POST[$temp_id . '_target']);
            $type = htmlspecialchars($_POST[$temp_id . '_type']);

            $stmt = $conn->prepare("UPDATE gantt_links SET source=?, target=?, type=? WHERE id=?");
            $stmt->bind_param("iisi", $source, $target, $type, $id);
            $stmt->execute();
            break;

        case 'deleted_link':
            $stmt = $conn->prepare("DELETE FROM gantt_links WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            break;
        default:
            $response['action'] = 'error';
            $response['message'] = 'Acción no reconocida o vacía.';
            error_log("Acción no reconocida o vacía: " . $action);
            break;
    }

    if ($stmt && $stmt->error) {
        throw new Exception($stmt->error);
    }
    if ($stmt) {
        $stmt->close();
    }
    $response['action'] = $action;
} catch (Exception $e) {
    $response['action'] = 'error';
    $response['message'] = $e->getMessage();
    error_log("Error en save.php: " . $e->getMessage());
}

echo json_encode($response);

$conn->close();
?>