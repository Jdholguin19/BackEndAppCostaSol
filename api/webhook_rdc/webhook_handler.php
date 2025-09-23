<?php
/**
 * ENDPOINT DE WEBHOOK PARA SINCRONIZACIÓN DESDE KISS FLOW (RDC)
 *
 * Propósito: Este script recibe notificaciones (payloads) desde un webhook de Kiss Flow
 * cada vez que un registro en el dataset 'DS_Documentos_Cliente' es creado o modificado.
 *
 * Funcionamiento:
 * 1. Recibe una solicitud HTTP POST con un payload JSON que contiene los datos de un único registro.
 * 2. Valida que el payload sea correcto y contenga la información necesaria (ej. cédula).
 * 3. Usando la cédula como clave, busca si el usuario ya existe en la base de datos local.
 * 4. Si el usuario no existe, lo crea. Si ya existe, actualiza sus datos.
 * 5. Realiza la misma operación para la propiedad asociada, creándola o actualizándola.
 * 6. Utiliza transacciones para garantizar la atomicidad de las operaciones en la base de datos.
 * 7. Registra la operación y los posibles errores en un archivo de log.
 * 8. Devuelve una respuesta JSON con un estado (success/error) y un mensaje.
 */
declare(strict_types=1);

// --- CONFIGURACIÓN Y SETUP INICIAL ---
header('Content-Type: application/json; charset=utf-8');

// --- GESTIÓN DE LOGS ---
define('LOG_FILE', __DIR__ . '/sync_log.txt');

// --- INCLUSIÓN DE DEPENDENCIAS ---
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../../kiss_flow/config.php'; // Para KISSFLOW_API_HOST, KISSFLOW_ACCESS_KEY_ID, KISSFLOW_ACCESS_KEY_SECRET

// --- FUNCIONES AUXILIARES ---

/**
 * Registra un mensaje en el archivo de log.
 * @param string $message El mensaje a registrar.
 * @param array $context Datos adicionales para incluir en el log.
 */
function log_message(string $message, array $context = []): void {
    $log_entry = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if (!empty($context)) {
        $log_entry .= " | Context: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    $log_entry .= "\n";
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Obtiene el Key (Name) de un registro en Kiss Flow usando su _id
 * @param string $record_id El _id del registro en Kiss Flow
 * @return ?string El Key (Name) del registro, o null si no se encuentra
 */
function get_kissflow_key_by_id(string $record_id): ?string {
    if (empty($record_id)) {
        return null;
    }
    
    // Obtener el registro específico por ID
    $url = KISSFLOW_API_HOST . "/dataset/2/AcNcc9rydX9F/DS_Documentos_Cliente/record/{$record_id}";
    
    $ch = curl_init($url);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Access-Key-Id: ' . KISSFLOW_ACCESS_KEY_ID,
        'X-Access-Key-Secret: ' . KISSFLOW_ACCESS_KEY_SECRET
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || $http_code >= 300) {
        log_message("ERROR al obtener registro de Kiss Flow por ID: $error", ['record_id' => $record_id, 'http_code' => $http_code]);
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['Data']['Name'])) {
        return $data['Data']['Name'];
    }
    
    return null;
}

/**
 * Parsea una cadena de Mz/Solar (ej: "Mz A / Villa B") en un array asociativo.
 */
function parse_mz_solar(string $mz_solar_string): array {
    $mz_solar_string = trim($mz_solar_string);
    $result = ['manzana' => null, 'villa' => null];

    if (preg_match('/Mz\s*([^\/]+)\s*\/\s*Villa\s*(.+)/i', $mz_solar_string, $matches)) {
        $result['manzana'] = mb_substr(trim($matches[1]), 0, 10);
        $result['villa'] = mb_substr(trim($matches[2]), 0, 10);
    } elseif (strpos($mz_solar_string, '-') !== false) {
        $parts = explode('-', $mz_solar_string, 2);
        $result['manzana'] = mb_substr(trim($parts[0]), 0, 10);
        $result['villa'] = mb_substr(trim($parts[1]), 0, 10);
    } else {
        log_message("ADVERTENCIA: Formato Mz_Solar no esperado: '{$mz_solar_string}'. Se usará como manzana (truncado).");
        $result['manzana'] = mb_substr($mz_solar_string, 0, 10);
    }
    return $result;
}

/**
 * Obtiene o crea el ID de un registro en una tabla maestra.
 */
function get_or_create_master_id(PDO $conn, string $table_name, string $column_name, ?string $value): ?int {
    if (empty($value)) return null;
    $value = trim($value);
    $stmt = $conn->prepare("SELECT id FROM `$table_name` WHERE `$column_name` = :value LIMIT 1");
    $stmt->execute([':value' => $value]);
    $id = $stmt->fetchColumn();
    if ($id) {
        return (int)$id;
    } else {
        log_message("Creando nuevo registro en '$table_name' para el valor '$value'...");
        if ($table_name === 'etapa_construccion') {
            $stmt_insert = $conn->prepare("INSERT INTO `etapa_construccion` (`nombre`, `porcentaje`) VALUES (:value, 0)");
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO `$table_name` (`$column_name`) VALUES (:value)");
        }
        if ($stmt_insert->execute([':value' => $value])) {
            return (int)$conn->lastInsertId();
        }
        return null;
    }
}

/**
 * Limpia y formatea un número de teléfono.
 */
function clean_phone_number(?string $phone_number): ?string {
    if (empty($phone_number)) return null;
    $cleaned_number = trim($phone_number);
    if (strpos($cleaned_number, '+593') === 0) {
        $local_part = substr($cleaned_number, 4);
        return '0' . preg_replace('/[^0-9]/', '', $local_part);
    }
    return preg_replace('/[^0-9]/', '', $cleaned_number);
}

/**
 * Envía una respuesta JSON y termina la ejecución del script.
 * @param string $status 'success' o 'error'.
 * @param string $message Mensaje descriptivo.
 * @param int $http_code Código de estado HTTP.
 */
function send_response(string $status, string $message, int $http_code = 200): void {
    http_response_code($http_code);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// --- LÓGICA PRINCIPAL DEL WEBHOOK ---

log_message("Webhook invocado.");

// 1. LEER Y VALIDAR EL PAYLOAD
$json_payload = file_get_contents('php://input');
$record = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($record)) {
    log_message("Error: Payload JSON inválido o no es un array.", ['payload' => $json_payload]);
    send_response('error', 'Payload JSON inválido.', 400);
}

log_message("Payload recibido y decodificado.", ['record_id' => $record['_id'] ?? 'N/A']);

$kissflow_ds_id = $record['_id'] ?? null;
$cedula = preg_replace('/[^0-9]/', '', trim($record['Identificacion'] ?? ''));

if (empty($cedula)) {
    log_message("Error: El campo 'Identificacion' (cédula) está vacío o es inválido.", ['record_id' => $kissflow_ds_id]);
    send_response('error', 'El campo Identificacion es obligatorio.', 400);
}

// 2. PROCESAR EL REGISTRO
$conn = DB::getDB();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $conn->beginTransaction();

    // Buscar o crear usuario
    $stmt_user = $conn->prepare("SELECT id FROM usuario WHERE cedula = :cedula LIMIT 1");
    $stmt_user->execute([':cedula' => $cedula]);
    $user_id = $stmt_user->fetchColumn();

    $user_created = false; // Variable para trackear si se creó un usuario nuevo
    
    if (!$user_id) {
        // Crear usuario
        $user_created = true; // Marcar que se está creando un usuario nuevo
        $new_user_data = [
            'cedula' => $cedula,
            'rol_id' => 1,
            'contrasena_hash' => password_hash('1234', PASSWORD_DEFAULT),
            'nombres' => mb_substr(explode(' ', trim($record['Nombre_de_Cliente'] ?? ''), 2)[0] ?? '', 0, 60),
            'apellidos' => mb_substr(explode(' ', trim($record['Nombre_de_Cliente'] ?? ''), 2)[1] ?? '', 0, 60),
            'correo' => $cedula . '@placeholder.costasol.com.ec', // Email se traerá de otra DB
            'telefono' => null, // Teléfono se traerá de otra DB
            'kissflow_ruc_url' => null, // RUC se traerá de otra DB
            'kissflow_convenio' => trim($record['Numero_de_Convenio'] ?? '')
        ];

        $columns = implode(', ', array_map(fn($c) => "`$c`", array_keys($new_user_data)));
        $placeholders = ':' . implode(', :', array_keys($new_user_data));
        $stmt_insert_user = $conn->prepare("INSERT INTO usuario ($columns) VALUES ($placeholders)");
        $stmt_insert_user->execute($new_user_data);
        $user_id = (int)$conn->lastInsertId();
        log_message("Usuario CREADO con ID: {$user_id}", ['cedula' => $cedula]);

    } else {
        // Actualizar usuario
        $update_fields = [
            'nombres' => mb_substr(explode(' ', trim($record['Nombre_de_Cliente'] ?? ''), 2)[0] ?? '', 0, 60),
            'apellidos' => mb_substr(explode(' ', trim($record['Nombre_de_Cliente'] ?? ''), 2)[1] ?? '', 0, 60),
            'kissflow_convenio' => trim($record['Numero_de_Convenio'] ?? '')
        ];
        // Los campos telefono, correo y ruc_url se gestionarán desde otra base de datos,
        // por lo que no se actualizan desde este webhook para no sobrescribir datos.

        $update_fields = array_filter($update_fields, fn($v) => !is_null($v) && $v !== '');
        if (!empty($update_fields)) {
            $set_parts = [];
            foreach ($update_fields as $key => $value) $set_parts[] = "`$key` = :$key";
            $set_sql = implode(', ', $set_parts);
            
            $stmt_update_user = $conn->prepare("UPDATE usuario SET $set_sql WHERE id = :id");
            $update_fields['id'] = $user_id;
            $stmt_update_user->execute($update_fields);
            log_message("Usuario ACTUALIZADO con ID: {$user_id}", ['cedula' => $cedula]);
        }
    }

    // Buscar o crear propiedad
    $stmt_prop = $conn->prepare("SELECT id FROM propiedad WHERE kissflow_ds_id = :ds_id LIMIT 1");
    $stmt_prop->execute([':ds_id' => $kissflow_ds_id]);
    $prop_id = $stmt_prop->fetchColumn();

    $prop_data = [
        'kissflow_ds_id' => $kissflow_ds_id,
        'etapa_id' => get_or_create_master_id($conn, 'etapa_construccion', 'nombre', $record['nometapa'] ?? null),
        'tipo_id' => get_or_create_master_id($conn, 'tipo_propiedad', 'nombre', $record['Modelo'] ?? null),
        'kissflow_proyecto' => trim($record['Proyecto'] ?? ''),
        'fecha_entrega' => null // Fecha de entrega se traerá de otra DB
    ];
    if (!empty($record['Ubicacion'])) {
        $mz_villa = parse_mz_solar($record['Ubicacion']);
        $prop_data['manzana'] = $mz_villa['manzana'];
        $prop_data['villa'] = $mz_villa['villa'];
    }
    
    $prop_data = array_filter($prop_data, fn($v) => !is_null($v) && $v !== '');

    if (!$prop_id) {
        $prop_data['id_usuario'] = $user_id;
        $prop_data['estado_id'] = 1;
        $prop_data['id_urbanizacion'] = 1;
        if (empty($prop_data['etapa_id'])) $prop_data['etapa_id'] = 1;
        if (empty($prop_data['tipo_id'])) $prop_data['tipo_id'] = 1;

        $columns = implode(', ', array_map(fn($c) => "`$c`", array_keys($prop_data)));
        $placeholders = ':' . implode(', :', array_keys($prop_data));
        $stmt_insert_prop = $conn->prepare("INSERT INTO propiedad ($columns) VALUES ($placeholders)");
        $stmt_insert_prop->execute($prop_data);
        log_message("Propiedad CREADA para usuario ID: {$user_id}", ['kissflow_ds_id' => $kissflow_ds_id]);
        
        // Para usuarios nuevos, obtener el Key de Kiss Flow y actualizarlo
        if ($user_created) {
            $kissflow_key = get_kissflow_key_by_id($kissflow_ds_id);
            if ($kissflow_key) {
                $stmt_update_key = $conn->prepare("UPDATE usuario SET kissflow_key = :kissflow_key WHERE id = :user_id");
                $stmt_update_key->execute([':kissflow_key' => $kissflow_key, ':user_id' => $user_id]);
                log_message("Key de Kiss Flow obtenido y actualizado", ['user_id' => $user_id, 'kissflow_key' => $kissflow_key]);
            } else {
                log_message("No se pudo obtener el Key de Kiss Flow para el usuario nuevo", ['user_id' => $user_id, 'kissflow_ds_id' => $kissflow_ds_id]);
            }
        }
    } else {
        if (!empty($prop_data)) {
            $set_parts = [];
            foreach ($prop_data as $key => $value) $set_parts[] = "`$key` = :$key";
            $set_sql = implode(', ', $set_parts);
            $stmt_update_prop = $conn->prepare("UPDATE propiedad SET $set_sql WHERE id = :id");
            $prop_data['id'] = $prop_id;
            $stmt_update_prop->execute($prop_data);
            log_message("Propiedad ACTUALIZADA con ID: {$prop_id}", ['kissflow_ds_id' => $kissflow_ds_id]);
        }
    }

    $conn->commit();
    send_response('success', "Registro procesado correctamente para la cédula {$cedula}.");

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    log_message("ERROR CRÍTICO al procesar registro: " . $e->getMessage(), ['record_id' => $kissflow_ds_id, 'cedula' => $cedula]);
    send_response('error', "Error interno del servidor al procesar el registro.", 500);
}
