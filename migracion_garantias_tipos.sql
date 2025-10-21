-- Migración para agregar nuevo campo 'valida_hasta_entrega' a la tabla garantias
-- Fecha: 2025-10-21

ALTER TABLE `garantias` ADD COLUMN `valida_hasta_entrega` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Válida hasta la entrega, 0 = Válida por tiempo de garantía (meses)' AFTER `tiempo_garantia_meses`;

-- Crear índice para optimizar búsquedas
CREATE INDEX `idx_valida_hasta_entrega` ON `garantias`(`valida_hasta_entrega`);
