-- Script para agregar el campo urbanizacion a la tabla progreso_construccion
-- Ejecutar antes de volver a extraer datos de SharePoint

-- Agregar la columna urbanizacion
ALTER TABLE progreso_construccion 
ADD COLUMN urbanizacion VARCHAR(50) NULL 
COMMENT 'Nombre de la urbanización (ARIENZO, BASILEA, CATANIA, DAVOS)';

-- Verificar que la columna se agregó correctamente
DESCRIBE progreso_construccion;
