<?php
/**
 * SCRIPT DE SINCRONIZACIÓN MASIVA UNIDIRECCIONAL (KISS FLOW -> APP)
 *
 * Propósito: Este script realiza una sincronización completa desde el dataset 'DS_Documentos_Cliente'
 * de Kiss Flow hacia la base de datos local de la aplicación (tablas `usuario` y `propiedad`).
 *
 * Funcionamiento:
 * 1. Se conecta a la API de Kiss Flow para obtener todos los registros del dataset.
 * 2. Itera sobre cada registro y, usando la cédula como clave, busca si el usuario ya existe localmente.
 * 3. Si el usuario no existe, lo crea junto con su propiedad.
 * 4. Si el usuario ya existe, actualiza sus datos y los de su propiedad.
 * 5. Maneja inconsistencias de datos (campos nulos, formatos inesperados, duplicados) para evitar errores fatales.
 *
 * Ejecución: Este script está diseñado para ser ejecutado manualmente desde un navegador o terminal
 * para realizar una carga inicial o una resincronización completa.
 */
declare(strict_types=1);

// --- CONFIGURACIÓN Y SETUP INICIAL ---

// Aumentar límites de tiempo y memoria para procesos que pueden ser largos y consumir recursos.
set_time_limit(0);
ini_set('memory_limit', '512M');

// Configura la salida para que sea texto plano y se muestre en tiempo real en el navegador.
header('Content-Type: text/plain; charset=utf-8');
echo '<pre>';
ob_implicit_flush(true);
ob_start();

// --- INCLUSIÓN DE DEPENDENCIAS ---

// Carga la configuración de la base de datos.
require_once __DIR__ . '/../../config/db.php';
// Carga las credenciales y la URL de la API de Kiss Flow.
require_once __DIR__ . '/../../kiss_flow/config.php';

// --- FUNCIONES AUXILIARES ---

/**
 * Imprime un mensaje de log con la fecha y hora actual.
 * @param string $message El mensaje a mostrar.
 */
function log_message(string $message): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    ob_flush();
    flush();
}

/**
 * Realiza una llamada genérica a la API de Kiss Flow usando cURL.
 * @param string $url La URL completa del endpoint de la API.
 * @return ?array La respuesta JSON decodificada como un array asociativo, o null si hay un error.
 */
function call_kissflow_api(string $url): ?array {
    $ch = curl_init($url);
    $headers = [
        'Content-Type: application/json',
        'X-Access-Key-ID: ' . KISSFLOW_ACCESS_KEY_ID,
        'X-Access-Key-Secret: ' . KISSFLOW_ACCESS_KEY_SECRET
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        log_message("ERROR cURL para $url: $error");
        return null;
    }
    if ($http_code >= 300) {
        log_message("ERROR API para $url. Código: $http_code. Respuesta: $response");
        return null;
    }
    return json_decode($response, true);
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
    if (preg_match('/Mz\s*([^\/]+)\s*\/\s*Villa\s*(.+)/i', $mz_solar_string, $matches)) {
        $result['manzana'] = mb_substr(trim($matches[1]), 0, 10);
        $result['villa'] = mb_substr(trim($matches[2]), 0, 10);
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

// --- LÓGICA PRINCIPAL DE SINCRONIZACIÓN ---

log_message("INICIO DE LA SINCRONIZACIÓN MASIVA.");

$conn = DB::getDB();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Contadores para el reporte final.
$processed_count = 0;
$created_users = 0;
$updated_users = 0;
$created_properties = 0;
$updated_properties = 0;
$errors = 0;

$page_number = 1;
$page_size = 50; // Se procesan 50 registros por cada llamada a la API para no sobrecargarla.

// Bucle principal para manejar la paginación de la API de Kiss Flow.
do {
    log_message("Procesando página $page_number...");
    $dataset_id = 'DS_Documentos_Cliente';
    $list_url = KISSFLOW_API_HOST . "/dataset/2/AcNcc9rydX9F/{$dataset_id}/list?page_number={$page_number}&page_size={$page_size}";
    
    $response = call_kissflow_api($list_url);
    $records = $response['Data'] ?? [];

    // Si la API no devuelve más registros, se termina el bucle.
    if (empty($records)) {
        log_message("No se encontraron más registros. Fin del proceso.");
        break;
    }

    // Itera sobre cada registro de Kiss Flow obtenido en la página actual.
    foreach ($records as $record) {
        $processed_count++;
        $kissflow_ds_id = $record['_id'] ?? null;
        $cedula = trim($record['Identificacion'] ?? '');

        // Si un registro no tiene cédula, se omite ya que es nuestra clave principal de enlace.
        if (empty($cedula)) {
            log_message("Registro de KF con ID '{$kissflow_ds_id}' omitido: no tiene Identificacion (cédula).");
            $errors++;
            continue;
        }

        // Flags para llevar la cuenta de las acciones realizadas en esta iteración.
        $was_user_created = false;
        $was_property_created = false;
        $was_user_updated = false;
        $was_property_updated = false;

        try {
            // Inicia una transacción. Todos los cambios para este registro se confirman o se revierten juntos.
            $conn->beginTransaction();

            // 1. BUSCAR O CREAR USUARIO
            $stmt_user = $conn->prepare("SELECT id FROM usuario WHERE cedula = :cedula LIMIT 1");
            $stmt_user->execute([':cedula' => $cedula]);
            $user_id = $stmt_user->fetchColumn();

            if (!$user_id) {
                // --- LÓGICA DE CREACIÓN DE USUARIO ---
                $was_user_created = true;
                $new_user_data = [];
                $new_user_data['cedula'] = $cedula;
                $new_user_data['rol_id'] = 1; // Rol "Cliente" por defecto.
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

                if (!empty($record['Tipo_de_Fachada'])) $new_user_data['telefono'] = trim($record['Tipo_de_Fachada']);
                if (!empty($record['RUC'][0]['Content'])) $new_user_data['kissflow_ruc_url'] = $record['RUC'][0]['Content'];
                if (!empty($record['Rev_Gerencia'])) $new_user_data['kissflow_rev_gerencia'] = trim($record['Rev_Gerencia']);
                if (!empty($record['Convenio'])) $new_user_data['kissflow_convenio'] = trim($record['Convenio']);

                $columns = implode(', ', array_map(fn($c) => "`$c`", array_keys($new_user_data)));
                $placeholders = ':' . implode(', :', array_keys($new_user_data));
                $stmt_insert_user = $conn->prepare("INSERT INTO usuario ($columns) VALUES ($placeholders)");
                $stmt_insert_user->execute($new_user_data);
                $user_id = (int)$conn->lastInsertId();

            } else {
                // --- LÓGICA DE ACTUALIZACIÓN DE USUARIO ---
                $update_fields = [];
                if (!empty($record['Nombre_Cliente'])) {
                    $parts = explode(' ', trim($record['Nombre_Cliente']), 2);
                    $update_fields['nombres'] = mb_substr($parts[0] ?? '', 0, 60);
                    $update_fields['apellidos'] = mb_substr($parts[1] ?? '', 0, 60);
                }
                if (!empty($record['Tipo_de_Fachada'])) $update_fields['telefono'] = trim($record['Tipo_de_Fachada']);
                if (!empty($record['RUC'][0]['Content'])) $update_fields['kissflow_ruc_url'] = $record['RUC'][0]['Content'];
                if (!empty($record['Rev_Gerencia'])) $update_fields['kissflow_rev_gerencia'] = trim($record['Rev_Gerencia']);
                if (!empty($record['Convenio'])) $update_fields['kissflow_convenio'] = trim($record['Convenio']);

                if (!empty($record['Comentario_Gerencia'])) {
                    $email = trim($record['Comentario_Gerencia']);
                    $stmt_email = $conn->prepare("SELECT id FROM usuario WHERE correo = :correo AND id != :current_user_id LIMIT 1");
                    $stmt_email->execute([':correo' => $email, ':current_user_id' => $user_id]);
                    if ($stmt_email->fetchColumn()) {
                        log_message("ADVERTENCIA: El correo {$email} ya pertenece a otro usuario. No se actualizará para la cédula {$cedula}.");
                    } else {
                        $update_fields['correo'] = $email;
                    }
                }

                if (!empty($update_fields)) {
                    $was_user_updated = true;
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
                $was_property_created = true;
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
                    $was_property_updated = true;
                    $set_parts = [];
                    foreach ($prop_data_to_update as $key => $value) $set_parts[] = "`$key` = :$key";
                    $set_sql = implode(', ', $set_parts);
                    $stmt_update_prop = $conn->prepare("UPDATE propiedad SET $set_sql WHERE id = :id");
                    $prop_data_to_update['id'] = $prop_id;
                    $stmt_update_prop->execute($prop_data_to_update);
                }
            }

            // Si todo fue exitoso, se confirman los cambios en la base de datos.
            $conn->commit();

            // Se incrementan los contadores solo después de que el commit fue exitoso.
            if ($was_user_created) $created_users++;
            if ($was_user_updated) $updated_users++;
            if ($was_property_created) $created_properties++;
            if ($was_property_updated) $updated_properties++;

        } catch (Exception $e) {
            // Si ocurre cualquier error, se revierten todos los cambios de este registro.
            if ($conn->inTransaction()) $conn->rollBack();
            log_message("ERROR al procesar registro de KF con ID '{$kissflow_ds_id}' (Cédula: {$cedula}): " . $e->getMessage());
            log_message("DATOS DEL REGISTRO FALLIDO: " . json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $errors++;
        }
    }

    $page_number++;

} while (true);

log_message("\n--- REPORTE FINAL ---");
log_message("Registros de Kiss Flow procesados: $processed_count");
log_message("Usuarios nuevos creados: $created_users");
log_message("Usuarios existentes actualizados: $updated_users");
log_message("Propiedades nuevas creadas: $created_properties");
log_message("Propiedades existentes actualizadas: $updated_properties");
log_message("Errores: $errors");
log_message("SINCRONIZACIÓN COMPLETADA.");

echo '</pre>';