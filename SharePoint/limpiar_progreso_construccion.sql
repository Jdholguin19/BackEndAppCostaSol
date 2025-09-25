-- Script para limpiar la tabla progreso_construccion
-- Ejecutar antes de volver a extraer datos de SharePoint

-- Opción 1: Eliminar todos los registros (recomendado para regenerar todo)
DELETE FROM progreso_construccion;

-- Opción 2: Si quieres mantener algunos registros, puedes usar WHERE
-- DELETE FROM progreso_construccion WHERE fecha_registro < '2024-01-01';

-- Verificar que la tabla esté vacía
SELECT COUNT(*) as registros_restantes FROM progreso_construccion;

-- Opcional: Resetear el AUTO_INCREMENT si la tabla tiene ID autoincremental
-- ALTER TABLE progreso_construccion AUTO_INCREMENT = 1;
