-- Script completo para limpiar y preparar la base de datos
-- Ejecutar en este orden:

-- 1. Verificar si la columna id_urbanizacion existe, si no, agregarla
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'progreso_construccion' 
     AND COLUMN_NAME = 'id_urbanizacion') = 0,
    'ALTER TABLE progreso_construccion ADD COLUMN id_urbanizacion TINYINT(3) UNSIGNED NULL COMMENT ''ID de la urbanización (FK a tabla urbanizacion)''',
    'SELECT ''Columna id_urbanizacion ya existe'' as mensaje'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Verificar si la clave foránea existe, si no, agregarla
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'progreso_construccion' 
     AND CONSTRAINT_NAME = 'fk_progreso_urbanizacion') = 0,
    'ALTER TABLE progreso_construccion ADD CONSTRAINT fk_progreso_urbanizacion FOREIGN KEY (id_urbanizacion) REFERENCES urbanizacion(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT ''Clave foránea fk_progreso_urbanizacion ya existe'' as mensaje'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Verificar si el índice existe, si no, agregarlo
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'progreso_construccion' 
     AND INDEX_NAME = 'idx_progreso_urbanizacion') = 0,
    'ALTER TABLE progreso_construccion ADD INDEX idx_progreso_urbanizacion (id_urbanizacion)',
    'SELECT ''Índice idx_progreso_urbanizacion ya existe'' as mensaje'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Limpiar todos los registros existentes
DELETE FROM progreso_construccion;

-- 5. Resetear el AUTO_INCREMENT
ALTER TABLE progreso_construccion AUTO_INCREMENT = 1;

-- 6. Verificar que la tabla esté limpia
SELECT COUNT(*) as registros_restantes FROM progreso_construccion;

-- 7. Verificar la estructura de la tabla
DESCRIBE progreso_construccion;

-- 8. Mostrar las urbanizaciones disponibles
SELECT id, nombre FROM urbanizacion WHERE estado = 1;
