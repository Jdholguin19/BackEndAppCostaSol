-- Script para alterar las tablas y prepararlas para la sincronización con Kiss Flow.
-- Se añaden nuevas columnas sin eliminar datos existentes.

--
-- 1. Añadir columnas a la tabla `usuario`
--

-- Columna para almacenar la URL del archivo RUC (PDF) de Kiss Flow
ALTER TABLE `usuario` ADD COLUMN `kissflow_ruc_url` VARCHAR(2048) NULL DEFAULT NULL COMMENT 'URL al archivo RUC en Kiss Flow';

-- Columna para almacenar el campo de texto Rev_Gerencia
ALTER TABLE `usuario` ADD COLUMN `kissflow_rev_gerencia` TEXT NULL DEFAULT NULL COMMENT 'Campo Rev Gerencia de Kiss Flow';

-- Columna para almacenar el campo Convenio
ALTER TABLE `usuario` ADD COLUMN `kissflow_convenio` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Campo Convenio de Kiss Flow';


--
-- 2. Añadir columnas a la tabla `propiedad`
--

-- Columna para almacenar el ID único del registro del dataset de Kiss Flow
-- Se hace ÚNICO para prevenir que un mismo registro de Kiss Flow se sincronice con más de una propiedad local.
ALTER TABLE `propiedad` ADD COLUMN `kissflow_ds_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'ID del registro en el Dataset de Kiss Flow';
ALTER TABLE `propiedad` ADD UNIQUE INDEX `idx_unique_kissflow_ds_id` (`kissflow_ds_id`);

-- Columna para almacenar el campo Proyecto
ALTER TABLE `propiedad` ADD COLUMN `kissflow_proyecto` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Campo Proyecto de Kiss Flow';


-- Mensaje de finalización
SELECT 'Las tablas `usuario` y `propiedad` han sido alteradas exitosamente para la sincronización con Kiss Flow.' AS `status`;
