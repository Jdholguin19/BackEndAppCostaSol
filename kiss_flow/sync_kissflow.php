<?php
declare(strict_types=1);

// Incluir archivos de configuración
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config.php';

echo "Iniciando sincronización con Kiss Flow...\n";

// --- FUNCIÓN AUXILIAR PARA LLAMADAS cURL ---
function call_kissflow_api(string $url): ?array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Access-Key-ID: ' . KISSFLOW_ACCESS_KEY_ID,
        'X-Access-Key-Secret: ' . KISSFLOW_ACCESS_KEY_SECRET
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "Error en cURL para $url: $error\n";
        return null;
    }

    if ($http_code !== 200) {
        echo "Error de API para $url. Código: $http_code. Respuesta: $response\n";
        return null;
    }
    return json_decode($response, true);
}

// 1. Obtener la fecha del último registro modificado para traer solo los más nuevos
$last_sync_date = null;
$result = $conn->query("SELECT MAX(_modified_at) AS last_date FROM kissflow_emision_pagos");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['last_date'] !== null) {
        $last_sync_date = $row['last_date'];
        echo "Última modificación detectada: $last_sync_date\n";
    }
}

// 2. Preparar la consulta de inserción/actualización
$sql = "INSERT INTO kissflow_emision_pagos (
            kissflow_item_id, kissflow_activity_id, Name, _created_at, _completed_at, _status, _created_by_id, _created_by_name, _modified_by_id, _modified_by_name, request_number, Proveedor, Monto, Fecha_de_Factura, Fecha_de_Pago, Factura_files, Numero_de_Factura, Orden_de_Pago_files, Numero_de_ChequeTransaccion, BancoCuenta, Motivo, Por_que_se_necesita, Cheque_ya_firmado, Notifico_al_proveedor, Valor_de_Pago, Desea_notificacion_automatica, Valor_Neto, Tipo_de_Pago, Cheque_o_Transferencia, Documentos_Relevantes_1_files, Documentos_Relevantes_2_files, Solicita, Aprueba, Necesita_Factura, viene_de, proceso_actual, Ordenes_de_Transferencias_cargadas, UDN_id, UDN_Name, UDN_Descripcion, ETAPA_id, ETAPA_Name, ETAPA_Descripcion, AUXILIAR_id, AUXILIAR_Name, AUXILIAR_Descripcion, CRF_id, CRF_Name, CRF_Descripcion, Proyecto_id, Proyecto_Name, Proyecto_Descripcion, RazonSocial_NombreComercial, RazonSocial_RazonSocial, RazonSocial_NumeroCuenta, RazonSocial_RUC, RazonSocial_Banco, RazonSocial_TipoCuenta, _modified_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        ) ON DUPLICATE KEY UPDATE
        kissflow_activity_id=VALUES(kissflow_activity_id), Name=VALUES(Name), _completed_at=VALUES(_completed_at), _status=VALUES(_status), _modified_by_id=VALUES(_modified_by_id), _modified_by_name=VALUES(_modified_by_name), Proveedor=VALUES(Proveedor), Monto=VALUES(Monto), Fecha_de_Factura=VALUES(Fecha_de_Factura), Fecha_de_Pago=VALUES(Fecha_de_Pago), Factura_files=VALUES(Factura_files), Numero_de_Factura=VALUES(Numero_de_Factura), Orden_de_Pago_files=VALUES(Orden_de_Pago_files), Numero_de_ChequeTransaccion=VALUES(Numero_de_ChequeTransaccion), BancoCuenta=VALUES(BancoCuenta), Motivo=VALUES(Motivo), Por_que_se_necesita=VALUES(Por_que_se_necesita), Cheque_ya_firmado=VALUES(Cheque_ya_firmado), Notifico_al_proveedor=VALUES(Notifico_al_proveedor), Valor_de_Pago=VALUES(Valor_de_Pago), Desea_notificacion_automatica=VALUES(Desea_notificacion_automatica), Valor_Neto=VALUES(Valor_Neto), Tipo_de_Pago=VALUES(Tipo_de_Pago), Cheque_o_Transferencia=VALUES(Cheque_o_Transferencia), Documentos_Relevantes_1_files=VALUES(Documentos_Relevantes_1_files), Documentos_Relevantes_2_files=VALUES(Documentos_Relevantes_2_files), Solicita=VALUES(Solicita), Aprueba=VALUES(Aprueba), Necesita_Factura=VALUES(Necesita_Factura), viene_de=VALUES(viene_de), proceso_actual=VALUES(proceso_actual), Ordenes_de_Transferencias_cargadas=VALUES(Ordenes_de_Transferencias_cargadas), UDN_id=VALUES(UDN_id), UDN_Name=VALUES(UDN_Name), UDN_Descripcion=VALUES(UDN_Descripcion), ETAPA_id=VALUES(ETAPA_id), ETAPA_Name=VALUES(ETAPA_Name), ETAPA_Descripcion=VALUES(ETAPA_Descripcion), AUXILIAR_id=VALUES(AUXILIAR_id), AUXILIAR_Name=VALUES(AUXILIAR_Name), AUXILIAR_Descripcion=VALUES(AUXILIAR_Descripcion), CRF_id=VALUES(CRF_id), CRF_Name=VALUES(CRF_Name), CRF_Descripcion=VALUES(CRF_Descripcion), Proyecto_id=VALUES(Proyecto_id), Proyecto_Name=VALUES(Proyecto_Name), Proyecto_Descripcion=VALUES(Proyecto_Descripcion), RazonSocial_NombreComercial=VALUES(RazonSocial_NombreComercial), RazonSocial_RazonSocial=VALUES(RazonSocial_RazonSocial), RazonSocial_NumeroCuenta=VALUES(RazonSocial_NumeroCuenta), RazonSocial_RUC=VALUES(RazonSocial_RUC), RazonSocial_Banco=VALUES(RazonSocial_Banco), RazonSocial_TipoCuenta=VALUES(RazonSocial_TipoCuenta), _modified_at=VALUES(_modified_at), fecha_sincronizacion=NOW()";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error);
}

// 3. Bucle de paginación para la lista superficial
$page_number = 1;
$page_size = 50;
$total_nuevos_registros = 0;
$total_actualizados = 0;
$accountId = 'AcNcc9rydX9F';
$processName = 'Emisio_n_de_Pagos_';
$list_base_url = KISSFLOW_API_HOST . "/process/2/{$accountId}/{$processName}/myitems/completed";

while (true) {
    $params = ['page_number' => $page_number, 'page_size' => $page_size];
    $list_url = $list_base_url . '?' . http_build_query($params);
    
    echo "Consultando lista de items, página $page_number...\n";
    $summary_response = call_kissflow_api($list_url);

    if ($summary_response === null || empty($summary_response['Data'])) {
        echo "No se encontraron más items en la lista. Terminando.\n";
        break;
    }

    // 4. Para cada item de la lista, obtener su detalle
    foreach ($summary_response['Data'] as $summary_item) {
        $item_id = $summary_item['_id'] ?? null;
        $activity_instance_id = $summary_item['_activity_instance_id'] ?? null;

        if (!$item_id || !$activity_instance_id) {
            echo "Item en la lista no tiene ID, saltando...\n";
            continue;
        }

        // Solo procesar si el item fue modificado después de nuestra última sincronización
        $item_modified_at = $summary_item['_modified_at'] ?? '1970-01-01T00:00:00Z';
        if ($last_sync_date && strtotime($item_modified_at) < strtotime($last_sync_date)) {
            continue; // Saltar item porque no es nuevo
        }

        echo "Obteniendo detalle para item $item_id...\n";
        $detail_url = KISSFLOW_API_HOST . "/process/2/{$accountId}/{$processName}/{$item_id}/{$activity_instance_id}";
        $item = call_kissflow_api($detail_url);

        if ($item === null) {
            echo "No se pudo obtener el detalle para el item $item_id, saltando...\n";
            continue;
        }

        // 5. Mapear y guardar en la base de datos
        $get_file_names = fn($arr) => isset($arr) && is_array($arr) ? implode(', ', array_column($arr, 'name')) : null;

                $params_to_bind = [
            $item['_id'] ?? null, $item['_activity_instance_id'] ?? null, $item['Name'] ?? null, $item['_created_at'] ?? null, $item['_completed_at'] ?? null, $item['_status'] ?? null,
            $item['_created_by']['_id'] ?? null, $item['_created_by']['Name'] ?? null, $item['_modified_by']['_id'] ?? null, $item['_modified_by']['Name'] ?? null,
            $item['_request_number'] ?? null, $item['Proveedor'] ?? null, $item['Monto'] ?? null, $item['Fecha_de_Factura'] ?? null, $item['Fecha_de_Pago'] ?? null,
            $get_file_names($item['Factura'] ?? null), $item['Numero_de_Factura'] ?? null, $get_file_names($item['Orden_de_Pago'] ?? null), $item['Numero_de_ChequeTransaccion'] ?? null,
            $item['BancoCuenta'] ?? null, $item['Motivo'] ?? null, $item['Por_que_se_necesita'] ?? null, $item['Cheque_ya_firmado'] ?? null, $item['Notifico_al_proveedor_que_el_cheque_esta_listo'] ?? null,
            $item['Valor_de_Pago'] ?? null, $item['Desea_que_Kissflow_notifique_automaticamente_al_proveedor_que_el_cheque_ya_esta_listo'] ?? null, $item['Valor_Neto'] ?? null,
            is_array($item['Tipo_de_Pago']) ? implode(', ', $item['Tipo_de_Pago']) : null, is_array($item['Cheque_o_Transferencia_1']) ? implode(', ', $item['Cheque_o_Transferencia_1']) : null,
            $get_file_names($item['Documentos_Relevantes_1'] ?? null), $get_file_names($item['Documentos_Relevantes_2'] ?? null),
            $item['Solicita'] ?? null, $item['Aprueba'] ?? null, $item['Necesita_Factura'] ?? null, $item['viene_de'] ?? null, $item['proceso_actual'] ?? null, $item['Ordenes_de_Transferencias_cargadas'] ?? null,
            $item['UDN']['_id'] ?? null, $item['UDN']['Name'] ?? null, $item['UDN']['UDNDescripcion'] ?? null,
            $item['ETAPA']['_id'] ?? null, $item['ETAPA']['Name'] ?? null, $item['ETAPA']['ETPDescripcion'] ?? null,
            $item['AUXILIAR']['_id'] ?? null, $item['AUXILIAR']['Name'] ?? null, $item['AUXILIAR']['AUXDescripcion'] ?? null,
            $item['CRF']['_id'] ?? null, $item['CRF']['Name'] ?? null, $item['CRF']['CRFDescripcion'] ?? null,
            $item['Proyecto']['_id'] ?? null, $item['Proyecto']['Name'] ?? null, $item['Proyecto']['PYTDescripcion'] ?? null,
            $item['Razon_Social']['NombreComercial'] ?? null, $item['Razon_Social']['RazonSocial'] ?? null, $item['Razon_Social']['NumeroCuenta'] ?? null, $item['Razon_Social']['RUC'] ?? null, $item['Razon_Social']['Banco'] ?? null, $item['Razon_Social']['TipoCuenta'] ?? null,
            $item['_modified_at'] ?? null
        ];
        
        $types = 'ssssssssssisssssssssssbididsssssssssbbsssssssssssssssssssssssss';
        $stmt->bind_param($types, ...$params_to_bind);
        
        $types = 'ssssssssssisssssssssssbididsssssssssbbsssssssssssssssssssss';
        echo "DEBUG: Contando variables...\n";
        echo "COUNT(params_to_bind): " . count($params_to_bind) . "\n";
        echo "STRLEN(types): " . strlen($types) . "\n";
        exit; // Detener el script aquí para depurar

        $stmt->bind_param($types, ...$params_to_bind);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 1) { // 1 for INSERT, 2 for UPDATE
                $total_actualizados++;
            } elseif ($stmt->affected_rows > 0) {
                $total_nuevos_registros++;
            }
        } else {
            echo "Error al ejecutar la consulta para item $item_id: " . $stmt->error . "\n";
        }
    }
    $page_number++;
}

$stmt->close();
$conn->close();

echo "\nSincronización completa. Se añadieron $total_nuevos_registros nuevos registros y se actualizaron $total_actualizados.\n";

?>