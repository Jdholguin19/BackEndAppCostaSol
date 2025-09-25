-- Script para corregir el tipo de dato de la columna villa
-- Ejecutar antes de volver a importar datos

-- 1. Verificar el tipo actual de la columna villa
DESCRIBE propiedad;

-- 2. Cambiar el tipo de dato de villa a VARCHAR para preservar ceros a la izquierda
ALTER TABLE propiedad MODIFY COLUMN villa VARCHAR(10) COLLATE utf8_unicode_ci DEFAULT NULL;

-- 3. También cambiar solar si es necesario
ALTER TABLE propiedad MODIFY COLUMN solar VARCHAR(10) COLLATE utf8_unicode_ci DEFAULT NULL;

-- 4. También cambiar manzana para preservar ceros a la izquierda
ALTER TABLE propiedad MODIFY COLUMN manzana VARCHAR(10) COLLATE utf8_unicode_ci DEFAULT NULL;

-- 5. Verificar el cambio
DESCRIBE propiedad;

-- 6. Verificar algunos datos existentes
SELECT id, manzana, villa, solar FROM propiedad LIMIT 10;
