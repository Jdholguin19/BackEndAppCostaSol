-- Script simple para agregar la columna id_urbanizacion
-- Ejecutar paso a paso:

-- Paso 1: Agregar la columna (ejecutar solo si no existe)
ALTER TABLE progreso_construccion 
ADD COLUMN id_urbanizacion TINYINT(3) UNSIGNED NULL 
COMMENT 'ID de la urbanización (FK a tabla urbanizacion)';

-- Paso 2: Agregar la clave foránea (ejecutar solo si no existe)
ALTER TABLE progreso_construccion 
ADD CONSTRAINT fk_progreso_urbanizacion 
FOREIGN KEY (id_urbanizacion) REFERENCES urbanizacion(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Paso 3: Agregar índice (ejecutar solo si no existe)
ALTER TABLE progreso_construccion 
ADD INDEX idx_progreso_urbanizacion (id_urbanizacion);

-- Paso 4: Limpiar registros
DELETE FROM progreso_construccion;

-- Paso 5: Resetear AUTO_INCREMENT
ALTER TABLE progreso_construccion AUTO_INCREMENT = 1;

-- Paso 6: Verificar
SELECT COUNT(*) as registros_restantes FROM progreso_construccion;
DESCRIBE progreso_construccion;
SELECT id, nombre FROM urbanizacion WHERE estado = 1;
