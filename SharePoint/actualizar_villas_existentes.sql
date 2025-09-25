-- Script para actualizar las villas existentes con el formato correcto
-- Ejecutar DESPUÉS de cambiar el tipo de dato a VARCHAR

-- 1. Primero cambiar el tipo de dato (si no lo has hecho)
ALTER TABLE propiedad MODIFY COLUMN villa VARCHAR(10) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE propiedad MODIFY COLUMN solar VARCHAR(10) COLLATE utf8_unicode_ci DEFAULT NULL;

-- 2. Verificar algunos datos actuales
SELECT id, manzana, villa, solar FROM propiedad WHERE villa REGEXP '^[0-9]+$' LIMIT 10;

-- 3. Actualizar villas que son números de 1 dígito para agregar el 0 a la izquierda
UPDATE propiedad 
SET villa = LPAD(villa, 2, '0')
WHERE villa REGEXP '^[0-9]$' AND LENGTH(villa) = 1;

-- 4. Actualizar solar también (si es necesario)
UPDATE propiedad 
SET solar = LPAD(solar, 2, '0')
WHERE solar REGEXP '^[0-9]$' AND LENGTH(solar) = 1;

-- 5. Actualizar manzanas que son números de menos de 4 dígitos (agregar ceros a la derecha)
UPDATE propiedad 
SET manzana = RPAD(manzana, 4, '0')
WHERE manzana REGEXP '^[0-9]+$' AND LENGTH(manzana) < 4;

-- 6. Verificar los cambios en villas
SELECT id, manzana, villa, solar FROM propiedad WHERE villa REGEXP '^0[0-9]$' LIMIT 5;

-- 7. Verificar los cambios en manzanas
SELECT id, manzana, villa, solar FROM propiedad WHERE manzana REGEXP '^[0-9]{2}00$' LIMIT 5;

-- 8. Contar cuántos registros se actualizaron
SELECT 
    'Villas con formato 0X' as descripcion,
    COUNT(*) as cantidad
FROM propiedad 
WHERE villa REGEXP '^0[0-9]$'
UNION ALL
SELECT 
    'Manzanas con formato XX00' as descripcion,
    COUNT(*) as cantidad
FROM propiedad 
WHERE manzana REGEXP '^[0-9]{2}00$'
UNION ALL
SELECT 
    'Villas de 1 dígito restantes' as descripcion,
    COUNT(*) as cantidad
FROM propiedad 
WHERE villa REGEXP '^[0-9]$' AND LENGTH(villa) = 1
UNION ALL
SELECT 
    'Manzanas de menos de 4 dígitos restantes' as descripcion,
    COUNT(*) as cantidad
FROM propiedad 
WHERE manzana REGEXP '^[0-9]+$' AND LENGTH(manzana) < 4;
