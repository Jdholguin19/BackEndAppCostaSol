<?php
declare(strict_types=1);

header('Content-Type: application/json');

// Incluir archivos de configuración
require_once __DIR__ . '/config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';

// Convertir la consulta a minúsculas para una comparación insensible a mayúsculas y minúsculas
$lower_query = strtolower(trim($query));

try {
    // --- Lógica para obtener el resumen de estados ---
    if ($lower_query === 'resumen estados' || $lower_query === 'estados' || $lower_query === 'status') {
        $counts = [];
        $statuses = ['Completed', 'InProgress', 'Rejected', 'Withdrawn']; // Incluir Withdrawn también

        foreach ($statuses as $status) {
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM kissflow_emision_pagos WHERE _status = ?");
            if ($stmt === false) {
                throw new Exception("Error al preparar la consulta de conteo para $status: " . $conn->error);
            }
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $counts[$status] = $row['count'];
            $stmt->close();
        }

        echo json_encode(['ok' => true, 'type' => 'status_summary', 'data' => $counts]);
        $conn->close();
        exit;
    }

    // --- Lógica existente para buscar por número de factura, proveedor o motivo ---
    if (empty($query)) {
        echo json_encode(['ok' => false, 'mensaje' => 'Por favor, escribe algo.']);
        exit;
    }

    $response_message = 'Lo siento, no encontré resultados para "' . htmlspecialchars($query) . '".';

    // Intentar buscar por número de factura
    if (is_numeric($query)) {
        $stmt = $conn->prepare("SELECT * FROM kissflow_emision_pagos WHERE request_number = ? LIMIT 1");
        $stmt->bind_param("i", $query);
    } else {
        // Intentar buscar por proveedor o motivo
        $search_term = '%' . $query . '%';
        $stmt = $conn->prepare("SELECT * FROM kissflow_emision_pagos WHERE Proveedor LIKE ? OR Motivo LIKE ? LIMIT 1");
        $stmt->bind_param("ss", $search_term, $search_term);
    }

    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response_message = "Encontré un registro:\n";
        $response_message .= "  ID: " . ($row['kissflow_item_id'] ?? 'N/A') . "\n";
        $response_message .= "  Proveedor: " . ($row['Proveedor'] ?? 'N/A') . "\n";
        $response_message .= "  Monto: $" . number_format((float)($row['Monto'] ?? 0.0), 2) . "\n";
        $response_message .= "  Motivo: " . ($row['Motivo'] ?? 'N/A') . "\n";
        $response_message .= "  Fecha de Pago: " . ($row['Fecha_de_Pago'] ?? 'N/A') . "\n";
        $response_message .= "  Estado: " . ($row['_status'] ?? 'N/A') . "\n"; // Añadir el estado
        // Puedes añadir más campos aquí
    } else {
        $response_message = 'No encontré ningún registro que coincida con "' . htmlspecialchars($query) . '".';
    }

    $stmt->close();
    echo json_encode(['ok' => true, 'mensaje' => $response_message]);

} catch (Exception $e) {
    error_log("Error en chatbot_backend.php: " . $e->getMessage());
    echo json_encode(['ok' => false, 'mensaje' => 'Ocurrió un error interno. Por favor, inténtalo de nuevo más tarde.', 'error' => $e->getMessage()]);
}

$conn->close();