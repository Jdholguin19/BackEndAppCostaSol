-- Script para corregir manzanas que fueron modificadas incorrectamente
-- Por ejemplo: 0071 → 7100, 0711 → 7110

-- 1. Verificar manzanas incorrectas (que empiezan con 0 y tienen 4 dígitos)
SELECT id, manzana, villa FROM propiedad WHERE manzana REGEXP '^0[0-9]{3}$' LIMIT 10;

-- 2. Mostrar cómo quedarían después de la corrección
SELECT 
    id, 
    manzana as manzana_actual,
    CONCAT(SUBSTRING(manzana, 2), '0') as manzana_corregida,
    villa
FROM propiedad 
WHERE manzana REGEXP '^0[0-9]{3}$' 
LIMIT 10;

-- 3. Contar cuántas manzanas necesitan corrección
SELECT 
    'Manzanas que necesitan corrección' as descripcion,
    COUNT(*) as cantidad
FROM propiedad 
WHERE manzana REGEXP '^0[0-9]{3}$';

-- 4. Corregir las manzanas incorrectas
-- Convertir 0071 → 7100, 0711 → 7110, etc.
UPDATE propiedad 
SET manzana = CONCAT(SUBSTRING(manzana, 2), '0')
WHERE manzana REGEXP '^0[0-9]{3}$';

-- 5. Verificar que no queden manzanas incorrectas
SELECT 
    'Manzanas incorrectas restantes' as descripcion,
    COUNT(*) as cantidad
FROM propiedad 
WHERE manzana REGEXP '^0[0-9]{3}$';

-- 6. Mostrar ejemplos de manzanas corregidas
SELECT id, manzana, villa FROM propiedad WHERE manzana REGEXP '^[0-9]{2}00$' LIMIT 5;

-- 7. Contar manzanas con formato correcto
SELECT 
    'Manzanas con formato correcto (XX00)' as descripcion,
    COUNT(*) as cantidad
FROM propiedad 
WHERE manzana REGEXP '^[0-9]{2}00$';
