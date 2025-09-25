-- Script para verificar las urbanizaciones y sus IDs
-- Ejecutar antes de correr el script PHP para confirmar los IDs

SELECT 
    id, 
    nombre, 
    estado,
    fecha_creada
FROM urbanizacion 
ORDER BY id;

-- Verificar que tenemos las 4 urbanizaciones esperadas
SELECT COUNT(*) as total_urbanizaciones FROM urbanizacion WHERE estado = 1;

-- Mostrar un resumen
SELECT 
    'Mapeo esperado en PHP:' as info,
    'ARIENZO = 1' as mapeo1,
    'BASILEA = 2' as mapeo2,
    'CATANIA = 3' as mapeo3,
    'DAVOS = 4' as mapeo4;
