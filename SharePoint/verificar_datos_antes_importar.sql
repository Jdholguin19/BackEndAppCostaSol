-- Script para verificar datos antes de importar el CSV
-- Ejecutar antes de correr el script PHP

-- 1. Verificar urbanizaciones disponibles
SELECT 'URBANIZACIONES DISPONIBLES' as info;
SELECT id, nombre, estado FROM urbanizacion WHERE estado = 1 ORDER BY id;

-- 2. Verificar roles disponibles
SELECT 'ROLES DISPONIBLES' as info;
SELECT id, nombre FROM rol WHERE estado = 1;

-- 3. Verificar tipos de propiedad disponibles
SELECT 'TIPOS DE PROPIEDAD DISPONIBLES' as info;
SELECT id, nombre FROM tipo_propiedad;

-- 4. Verificar etapas disponibles
SELECT 'ETAPAS DISPONIBLES' as info;
SELECT id, nombre, porcentaje FROM etapa_construccion WHERE estado = 1 ORDER BY id;

-- 5. Verificar estados de propiedad disponibles
SELECT 'ESTADOS DE PROPIEDAD DISPONIBLES' as info;
SELECT id, nombre FROM estado_propiedad;

-- 6. Verificar cuántos usuarios existen actualmente
SELECT 'USUARIOS EXISTENTES' as info;
SELECT COUNT(*) as total_usuarios FROM usuario;

-- 7. Verificar cuántas propiedades existen actualmente
SELECT 'PROPIEDADES EXISTENTES' as info;
SELECT COUNT(*) as total_propiedades FROM propiedad;

-- 8. Verificar usuarios duplicados por cédula
SELECT 'USUARIOS DUPLICADOS POR CÉDULA' as info;
SELECT cedula, COUNT(*) as duplicados 
FROM usuario 
WHERE cedula IS NOT NULL AND cedula != ''
GROUP BY cedula 
HAVING COUNT(*) > 1;

-- 9. Verificar usuarios duplicados por correo
SELECT 'USUARIOS DUPLICADOS POR CORREO' as info;
SELECT correo, COUNT(*) as duplicados 
FROM usuario 
WHERE correo IS NOT NULL AND correo != ''
GROUP BY correo 
HAVING COUNT(*) > 1;
