<?php
/**
 * Configuración para la importación de datos CSV
 * Ajusta estos valores según tu base de datos
 */

return [
    // IDs por defecto - AJUSTAR SEGÚN TU BASE DE DATOS
    'rol_cliente_default' => 1,        // ID del rol "Cliente" en la tabla rol
    'tipo_propiedad_default' => 1,     // ID del tipo de propiedad por defecto
    'etapa_default' => 1,              // ID de la etapa inicial (ej: "Cimentación")
    'estado_default' => 4,             // ID del estado inicial (ej: "Activo")
    
    // Configuración de contraseña temporal
    'contrasena_temporal' => 'temp123', // Contraseña temporal para nuevos usuarios
    
    // Configuración de archivo CSV
    'csv_file' => __DIR__ . '/Libro2.csv',
    
    // Mapeo de columnas CSV (ajustar si cambias el CSV)
    'columnas_csv' => [
        'urbanizacion' => 'urbanizacion',  // ID de urbanización
        'mz' => 'mz',                      // Manzana
        'villa' => 'villa',                // Villa
        'cedula' => 'cedula',              // Cédula
        'cliente' => 'cliente',            // Nombre completo del cliente
        'correo' => 'correo',              // Email
        'telefono' => 'telefono'           // Teléfono
    ],
    
    // Configuración de procesamiento
    'procesamiento' => [
        'saltar_fila_encabezados' => true,  // Si el CSV tiene encabezados
        'separar_nombres_apellidos' => true, // Separar automáticamente nombres y apellidos
        'formato_nombres' => 'apellidos_primero', // 'apellidos_primero' o 'nombres_primero'
        'apellidos_por_defecto' => ['Sin', 'Apellido'], // Si no se pueden separar
        'validar_duplicados' => true,       // Validar duplicados antes de insertar
        'mostrar_debug' => true             // Mostrar información de debug
    ]
];
?>
