<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json');

// Incluir archivos de configuración
require_once __DIR__ . '/config/db.php';

class Chatbot
{
    private $conn;
    private const STATUS_COMMANDS = ['resumen estados', 'estados', 'status'];
    private const AVAILABLE_STATUSES = ['Completed', 'InProgress', 'Rejected', 'Withdrawn'];

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function handleQuery(string $query): array
    {
        $lower_query = strtolower(trim($query));

        if (in_array($lower_query, self::STATUS_COMMANDS)) {
            return $this->getStatusSummary();
        }

        // Check for advanced search pattern: "buscar campo:"valor" AND campo:"valor""
        // Or range search patterns: "buscar monto_mayor_que:X", "buscar fecha_despues_de:YYYY-MM-DD"
        if (str_starts_with($lower_query, 'buscar ')) {
            return $this->advancedSearch(substr($query, strlen('buscar ')));
        }

        if (empty($query)) {
            return ['ok' => false, 'mensaje' => 'Por favor, escribe algo.'];
        }

        return $this->searchRecords($query);
    }

    private function getStatusSummary(): array
    {
        $counts = [];
        foreach (self::AVAILABLE_STATUSES as $status) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS count FROM kissflow_emision_pagos WHERE _status = ?");
            if ($stmt === false) {
                error_log("Error al preparar la consulta de conteo para $status: " . $this->conn->error);
                return ['ok' => false, 'mensaje' => 'Ocurrió un error al obtener el resumen de estados.'];
            }
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $counts[$status] = $row['count'];
            $stmt->close();
        }
        return ['ok' => true, 'type' => 'status_summary', 'data' => $counts];
    }

    private function searchRecords(string $query): array
    {
        // Seleccionar solo las columnas necesarias para la respuesta
        $select_columns = 'kissflow_item_id, Name, Proveedor, Monto, Motivo, Fecha_de_Pago, _status';

        // Intentar buscar por número de factura
        if (is_numeric($query)) {
            $stmt = $this->conn->prepare("SELECT {$select_columns} FROM kissflow_emision_pagos WHERE request_number = ? LIMIT 1");
            if ($stmt === false) {
                error_log("Error al preparar la consulta de búsqueda por número: " . $this->conn->error);
                return ['ok' => false, 'mensaje' => 'Ocurrió un error interno al buscar por número.'];
            }
            $stmt->bind_param("i", $query);
        } else {
            // Intentar buscar por proveedor o motivo
            $search_term = '%' . $query . '%';
            $stmt = $this->conn->prepare("SELECT {$select_columns} FROM kissflow_emision_pagos WHERE Proveedor LIKE ? OR Motivo LIKE ? LIMIT 1");
            if ($stmt === false) {
                error_log("Error al preparar la consulta de búsqueda por texto: " . $this->conn->error);
                return ['ok' => false, 'mensaje' => 'Ocurrió un error interno al buscar por texto.'];
            }
            $stmt->bind_param("ss", $search_term, $search_term);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response_message = "Encontré un registro:\n";
            $response_message .= "  ID: " . ($row['kissflow_item_id'] ?? 'N/A') . "\n";
            $response_message .= "  Nombre: " . ($row['Name'] ?? 'N/A') . "\n";
            $response_message .= "  Proveedor: " . ($row['Proveedor'] ?? 'N/A') . "\n";
            $response_message .= "  Monto: $" . number_format((float)($row['Monto'] ?? 0.0), 2) . "\n";
            $response_message .= "  Motivo: " . ($row['Motivo'] ?? 'N/A') . "\n";
            $response_message .= "  Fecha de Pago: " . ($row['Fecha_de_Pago'] ?? 'N/A') . "\n";
            $response_message .= "  Estado: " . ($row['_status'] ?? 'N/A') . "\n";
            return ['ok' => true, 'mensaje' => $response_message];
        } else {
            return ['ok' => true, 'mensaje' => 'No encontré ningún registro que coincida con "' . htmlspecialchars($query) . '".'];
        }
    }

    private function advancedSearch(string $query_string): array
    {
        $select_columns = 'kissflow_item_id, Name, Proveedor, Monto, Fecha_de_Factura, Fecha_de_Pago, _status, Motivo';
        $where_clauses = [];
        $bind_types = '';
        $bind_params = [];
        $limit = 10; // Default limit for advanced searches

        // Parse query string for field:"value" pairs and range queries
        // Example: 'proveedor:"Romance Eventos" AND monto_mayor_que:500 AND estado:"InProgress"'
        preg_match_all('/(?:(\w+):"([^\"]+)"|(\w+):([\w\d\-\.,]+))(?:\s*(?:AND|$))/i', $query_string, $matches, PREG_SET_ORDER);

        $parsed_criteria = [];
        foreach ($matches as $match) {
            $field = $match[1] ?? $match[3]; // field name
            $value = $match[2] ?? $match[4]; // value
            $parsed_criteria[strtolower($field)] = $value;
        }

        // Map user-friendly fields to DB columns and build WHERE clauses
        $field_map = [
            'proveedor' => 'Proveedor',
            'estado' => '_status',
            'monto' => 'Monto',
            'fecha_de_pago' => 'Fecha_de_Pago',
            'fecha_de_factura' => 'Fecha_de_Factura',
            'request_number' => 'request_number',
            'motivo' => 'Motivo'
        ];

        foreach ($parsed_criteria as $key => $value) {
            // Handle range queries first
            if (str_starts_with($key, 'monto_mayor_que')) {
                $where_clauses[] = "Monto > ?";
                $bind_types .= 'd';
                $bind_params[] = (float)$value;
            } elseif (str_starts_with($key, 'monto_menor_que')) {
                $where_clauses[] = "Monto < ?";
                $bind_types .= 'd';
                $bind_params[] = (float)$value;
            } elseif (str_starts_with($key, 'monto_entre')) {
                $parts = explode(',', $value);
                if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                    $where_clauses[] = "Monto BETWEEN ? AND ?";
                    $bind_types .= 'dd';
                    $bind_params[] = (float)$parts[0];
                    $bind_params[] = (float)$parts[1];
                } else {
                    return ['ok' => false, 'mensaje' => 'Formato de monto_entre incorrecto. Usa: monto_entre:X,Y'];
                }
            } elseif (str_starts_with($key, 'fecha_despues_de')) {
                $where_clauses[] = "Fecha_de_Pago >= ?";
                $bind_types .= 's';
                $bind_params[] = $value;
            } elseif (str_starts_with($key, 'fecha_antes_de')) {
                $where_clauses[] = "Fecha_de_Pago <= ?";
                $bind_types .= 's';
                $bind_params[] = $value;
            }
            // Handle exact/like matches
            elseif (isset($field_map[$key])) {
                $db_field = $field_map[$key];
                if ($db_field === 'request_number' && !is_numeric($value)) {
                    return ['ok' => false, 'mensaje' => "El valor para 'request_number' debe ser numérico."];
                }
                if ($db_field === 'Monto' && !is_numeric($value)) {
                    return ['ok' => false, 'mensaje' => "El valor para 'Monto' debe ser numérico."];
                }

                // Use LIKE for text fields, exact for others
                if (in_array($db_field, ['Proveedor', 'Motivo', '_status'])) {
                    $where_clauses[] = "{$db_field} LIKE ?";
                    $bind_types .= 's';
                    $bind_params[] = '%' . $value . '%';
                } else {
                    $where_clauses[] = "{$db_field} = ?";
                    $bind_types .= 's';
                    $bind_params[] = $value;
                }
            } else {
                return ['ok' => false, 'mensaje' => "Campo de búsqueda avanzado no reconocido: {$key}."];
            }
        }

        if (empty($where_clauses)) {
            return ['ok' => false, 'mensaje' => 'No se encontraron criterios de búsqueda válidos para la búsqueda avanzada.'];
        }

        $sql = "SELECT {$select_columns} FROM kissflow_emision_pagos WHERE " . implode(' AND ', $where_clauses) . " LIMIT {$limit}";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            error_log("Error al preparar la consulta avanzada: " . $this->conn->error);
            return ['ok' => false, 'mensaje' => 'Ocurrió un error interno al preparar la búsqueda avanzada.'];
        }

        // Dynamically bind parameters
        if (!empty($bind_params)) {
            $stmt->bind_param($bind_types, ...$bind_params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $found_records = [];
        while ($row = $result->fetch_assoc()) {
            $found_records[] = $row;
        }
        $stmt->close();

        if (empty($found_records)) {
            return ['ok' => true, 'mensaje' => 'No encontré ningún registro que coincida con tu búsqueda avanzada.'];
        } else {
            $response_message = "Encontré " . count($found_records) . " registro(s):\n";
            foreach ($found_records as $record) {
                $response_message .= "--------------------
";
                $response_message .= "  ID: " . ($record['kissflow_item_id'] ?? 'N/A') . "\n";
                $response_message .= "  Nombre: " . ($record['Name'] ?? 'N/A') . "\n";
                $response_message .= "  Proveedor: " . ($record['Proveedor'] ?? 'N/A') . "\n";
                $response_message .= "  Monto: $" . number_format((float)($record['Monto'] ?? 0.0), 2) . "\n";
                $response_message .= "  Motivo: " . ($record['Motivo'] ?? 'N/A') . "\n";
                $response_message .= "  Fecha Pago: " . ($record['Fecha_de_Pago'] ?? 'N/A') . "\n";
                $response_message .= "  Estado: " . ($record['_status'] ?? 'N/A') . "\n";
            }
            if (count($found_records) >= $limit) {
                $response_message .= "--------------------
";
                $response_message .= "Mostrando los primeros {$limit} resultados. Por favor, sé más específico si necesitas más.";
            }
            return ['ok' => true, 'mensaje' => $response_message];
        }
    }
}

// Uso
$input = json_decode(file_get_contents('php://input'), true);
$query = (string)($input['query'] ?? '');

try {
    $chatbot = new Chatbot($conn);
    $response = $chatbot->handleQuery($query);
    echo json_encode($response);
} catch (Exception $e) {
    error_log("Error fatal en chatbot_backend.php: " . $e->getMessage());
    echo json_encode(['ok' => false, 'mensaje' => 'Ocurrió un error interno inesperado.', 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
