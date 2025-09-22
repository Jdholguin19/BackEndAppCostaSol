<?php
declare(strict_types=1);

/**
 * Este archivo centraliza toda la lógica para procesar un único registro de Kiss Flow.
 * Puede ser llamado tanto por el script de sincronización masiva como por el manejador de webhooks.
 */

// --- FUNCIONES AUXILIARES DE LÓGICA ---

/**
 * Imprime un mensaje de log en pantalla y lo guarda en un archivo.
 * @param string $message El mensaje a registrar.
 */
function log_message(string $message): void {
    $log_entry = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    
    // Solo imprimir en pantalla si estamos en el contexto de sincronización masiva (BULK).
    // En el contexto de WEBHOOK, no se debe imprimir nada para no corromper la respuesta JSON.
    if (defined('SYNC_CONTEXT') && SYNC_CONTEXT === 'BULK') {
        echo $log_entry;
        ob_flush();
        flush();
    }

    // Guardar siempre en el archivo de log.
    // El flag LOCK_EX previene escrituras concurrentes si el script se ejecutara múltiples veces.
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Parsea una cadena de Mz/Solar (ej: "Mz A / Villa B") en un array asociativo.
 * Maneja formatos no esperados truncando el valor para que quepa en la columna `manzana`.
 * @param string $mz_solar_string La cadena a parsear.
 * @return array Array con las claves 'manzana' y 'villa'.
 */
function parse_mz_solar(string $mz_solar_string): array {
    $mz_solar_string = trim($mz_solar_string);
    $result = ['manzana' => null, 'villa' => null];

    // Caso 1: "Mz A / Villa B"
    if (preg_match('/Mz\s*([^\/]+)\s*\/\s*Villa\s*(.+)/i', $mz_solar_string, $matches)) {
        $result['manzana'] = mb_substr(trim($matches[1]), 0, 10);
        $result['villa'] = mb_substr(trim($matches[2]), 0, 10);
    // Caso 2: "7146-01" o "TORRE D-3A"
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
 * Obtiene el ID de un registro en una tabla maestra (ej. `etapa_construccion`).
 * Si el valor no existe en la tabla, lo inserta y devuelve el nuevo ID generado.
 * @param PDO $conn La conexión a la base de datos.
 * @param string $table_name El nombre de la tabla maestra (ej. 'etapa_construccion').
 * @param string $column_name El nombre de la columna a buscar (ej. 'nombre').
 * @param ?string $value El valor de texto a buscar o crear (ej. 'Catania').
 * @return ?int El ID del registro correspondiente. Null si el valor de entrada es vacío.
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
        // Caso especial para la tabla `etapa_construccion` que tiene una columna `porcentaje` no nula.
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
 * @param ?string $phone_number El número de teléfono a limpiar.
 * @return ?string El número de teléfono formateado, o null si la entrada es vacía.
 */
function clean_phone_number(?string $phone_number): ?string {
    if (empty($phone_number)) {
        return null;
    }
    $cleaned_number = trim($phone_number);
    
    if (strpos($cleaned_number, '+593') === 0) {
        $local_part = substr($cleaned_number, 4);
        return '0' . preg_replace('/[^0-9]/', '', $local_part);
    }
    
    return preg_replace('/[^0-9]/', '', $cleaned_number);
}


// --- FUNCIÓN PRINCIPAL DE PROCESAMIENTO ---

/**
 * Procesa un único registro de Kiss Flow, creando o actualizando el usuario y la propiedad correspondientes.
 * 
 * @param array $record El array de datos del registro de Kiss Flow.
 * @param PDO $conn La conexión a la base de datos.
 * @return array Un array con el resultado de la operación (ej. ['user_created' => true, 'error' => null]).
 */
function process_kissflow_record(array $record, PDO $conn): array {
    $result = [
        'user_created' => false,
        'user_updated' => false,
        'property_created' => false,
        'property_updated' => false,
        'error' => null,
        'skipped' => false
    ];

    $kissflow_ds_id = $record['_id'] ?? null;
    $cedula = preg_replace('/[^0-9]/', '', trim($record['Identificacion'] ?? ''));

    if (empty($cedula)) {
        log_message("Registro de KF con ID '{$kissflow_ds_id}' omitido: Identificacion (cédula) vacía o inválida.");
        $result['skipped'] = true;
        $result['error'] = 'Cédula vacía o inválida';
        return $result;
    }

    try {
        $conn->beginTransaction();

        // 1. BUSCAR O CREAR USUARIO
        $stmt_user = $conn->prepare("SELECT id FROM usuario WHERE cedula = :cedula LIMIT 1");
        $stmt_user->execute([':cedula' => $cedula]);
        $user_id = $stmt_user->fetchColumn();

        if (!$user_id) {
            $result['user_created'] = true;
            $new_user_data = [];
            $new_user_data['cedula'] = $cedula;
            $new_user_data['rol_id'] = 1;
            $new_user_data['contrasena_hash'] = password_hash('1234', PASSWORD_DEFAULT);

            if (!empty($record['Nombre_Cliente'])) {
                $parts = explode(' ', trim($record['Nombre_Cliente']), 2);
                $new_user_data['nombres'] = mb_substr($parts[0] ?? '', 0, 60);
                $new_user_data['apellidos'] = mb_substr($parts[1] ?? '', 0, 60);
            }
            
            $email = trim($record['Comentario_Gerencia'] ?? '');
            if (empty($email)) {
                $new_user_data['correo'] = $cedula . '@placeholder.costasol.com.ec';
            } else {
                $stmt_email = $conn->prepare("SELECT id FROM usuario WHERE correo = :correo LIMIT 1");
                $stmt_email->execute([':correo' => $email]);
                if ($stmt_email->fetchColumn()) {
                    log_message("ADVERTENCIA: El correo {$email} ya existe. Usando placeholder para nueva cédula {$cedula}.");
                    $new_user_data['correo'] = $cedula . '@placeholder.costasol.com.ec';
                } else {
                    $new_user_data['correo'] = $email;
                }
            }

            if (!empty($record['Tipo_de_Fachada'])) $new_user_data['telefono'] = clean_phone_number($record['Tipo_de_Fachada']);
            if (!empty($record['RUC'][0]['Content'])) $new_user_data['kissflow_ruc_url'] = $record['RUC'][0]['Content'];
            if (!empty($record['Rev_Gerencia'])) $new_user_data['kissflow_rev_gerencia'] = trim($record['Rev_Gerencia']);
            if (!empty($record['Convenio'])) $new_user_data['kissflow_convenio'] = trim($record['Convenio']);

            $columns = implode(', ', array_map(fn($c) => "`$c`", array_keys($new_user_data)));
            $placeholders = ':' . implode(', :', array_keys($new_user_data));
            $stmt_insert_user = $conn->prepare("INSERT INTO usuario ($columns) VALUES ($placeholders)");
            $stmt_insert_user->execute($new_user_data);
            $user_id = (int)$conn->lastInsertId();

        } else {
            $update_fields = [];
            if (!empty($record['Nombre_Cliente'])) {
                $parts = explode(' ', trim($record['Nombre_Cliente']), 2);
                $update_fields['nombres'] = mb_substr($parts[0] ?? '', 0, 60);
                $update_fields['apellidos'] = mb_substr($parts[1] ?? '', 0, 60);
            }
            if (!empty($record['Tipo_de_Fachada'])) $update_fields['telefono'] = clean_phone_number($record['Tipo_de_Fachada']);
            if (!empty($record['RUC'][0]['Content'])) $update_fields['kissflow_ruc_url'] = $record['RUC'][0]['Content'];
            if (!empty($record['Rev_Gerencia'])) $update_fields['kissflow_rev_gerencia'] = trim($record['Rev_Gerencia']);
            if (!empty($record['Convenio'])) $update_fields['kissflow_convenio'] = trim($record['Convenio']);

            if (!empty($record['Comentario_Gerencia'])) {
                $email = trim($record['Comentario_Gerencia']);
                $stmt_email = $conn->prepare("SELECT id, cedula FROM usuario WHERE correo = :correo AND id != :current_user_id LIMIT 1");
                $stmt_email->execute([':correo' => $email, ':current_user_id' => $user_id]);
                if ($other_user = $stmt_email->fetch(PDO::FETCH_ASSOC)) {
                    log_message("ADVERTENCIA: El correo {$email} ya pertenece a otro usuario (Cédula: {$other_user['cedula']}). No se actualizará para la cédula actual ({$cedula}).");
                } else {
                    $update_fields['correo'] = $email;
                }
            }

            if (!empty($update_fields)) {
                $result['user_updated'] = true;
                $set_parts = [];
                foreach ($update_fields as $key => $value) {
                    $set_parts[] = "`$key` = :$key";
                }
                $set_sql = implode(', ', $set_parts);
                
                $stmt_update_user = $conn->prepare("UPDATE usuario SET $set_sql WHERE id = :id");
                $update_fields['id'] = $user_id;
                $stmt_update_user->execute($update_fields);
            }
        }

        // 2. BUSCAR O CREAR PROPIEDAD
        $stmt_prop = $conn->prepare("SELECT id FROM propiedad WHERE kissflow_ds_id = :ds_id LIMIT 1");
        $stmt_prop->execute([':ds_id' => $kissflow_ds_id]);
        $prop_id = $stmt_prop->fetchColumn();

        $prop_data_to_update = [];
        $prop_data_to_update['kissflow_ds_id'] = $kissflow_ds_id;
        $prop_data_to_update['etapa_id'] = get_or_create_master_id($conn, 'etapa_construccion', 'nombre', $record['Etapa'] ?? null);
        $prop_data_to_update['tipo_id'] = get_or_create_master_id($conn, 'tipo_propiedad', 'nombre', $record['Modelo'] ?? null);
        if (!empty($record['Mz_Solar'])) {
            $mz_villa = parse_mz_solar($record['Mz_Solar']);
            $prop_data_to_update['manzana'] = $mz_villa['manzana'];
            $prop_data_to_update['villa'] = $mz_villa['villa'];
        }
        if (!empty($record['Proyecto'])) $prop_data_to_update['kissflow_proyecto'] = trim($record['Proyecto']);
        if (!empty($record['Fecha_de_Entrega'])) $prop_data_to_update['fecha_entrega'] = $record['Fecha_de_Entrega'];
        
        $prop_data_to_update = array_filter($prop_data_to_update, fn($value) => !is_null($value) && $value !== '');

        if (!$prop_id) {
            $result['property_created'] = true;
            $prop_data_to_update['id_usuario'] = $user_id;
            $prop_data_to_update['estado_id'] = 1; // Default: DISPONIBLE
            $prop_data_to_update['id_urbanizacion'] = 1; // Default: Arienzo
            if (empty($prop_data_to_update['etapa_id'])) $prop_data_to_update['etapa_id'] = 1; // Default
            if (empty($prop_data_to_update['tipo_id'])) $prop_data_to_update['tipo_id'] = 1; // Default

            $columns = implode(', ', array_map(fn($c) => "`$c`", array_keys($prop_data_to_update)));
            $placeholders = ':' . implode(', :', array_keys($prop_data_to_update));
            $stmt_insert_prop = $conn->prepare("INSERT INTO propiedad ($columns) VALUES ($placeholders)");
            $stmt_insert_prop->execute($prop_data_to_update);
        } else {
            if (!empty($prop_data_to_update)) {
                $result['property_updated'] = true;
                $set_parts = [];
                foreach ($prop_data_to_update as $key => $value) $set_parts[] = "`$key` = :$key";
                $set_sql = implode(', ', $set_parts);
                $stmt_update_prop = $conn->prepare("UPDATE propiedad SET $set_sql WHERE id = :id");
                $prop_data_to_update['id'] = $prop_id;
                $stmt_update_prop->execute($prop_data_to_update);
            }
        }

        $conn->commit();

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        log_message("ERROR al procesar registro de KF con ID '{$kissflow_ds_id}' (Cédula: {$cedula}): " . $e->getMessage());
        log_message("DATOS DEL REGISTRO FALLIDO: " . json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $result['error'] = $e->getMessage();
    }

    return $result;
}
