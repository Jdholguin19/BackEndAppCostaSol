-- Fase 1: Modificaciones a la Base de Datos

-- 1. Modificar la tabla `acabado_kit` para añadir la columna `costo`.
ALTER TABLE `acabado_kit` ADD `costo` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Costo adicional del kit';

-- 2. Actualizar los costos de los kits existentes.
UPDATE `acabado_kit` SET `costo` = 0.00 WHERE `id` = 1; -- Cocina Standar
UPDATE `acabado_kit` SET `costo` = 3450.00 WHERE `id` = 2; -- Cocina Full

-- 3. Crear la nueva tabla `paquetes_adicionales`.
CREATE TABLE `paquetes_adicionales` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `precio` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `fotos` JSON NULL COMMENT 'Un array de URLs de imágenes. Ej: ["url1.jpg", "url2.jpg"]',
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 4. Crear la tabla intermedia `propiedad_paquetes_adicionales`.
CREATE TABLE `propiedad_paquetes_adicionales` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `propiedad_id` BIGINT UNSIGNED NOT NULL,
  `paquete_id` INT UNSIGNED NOT NULL,
  `fecha_agregado` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_propiedad_paquete` (`propiedad_id`, `paquete_id`),
  INDEX `idx_propiedad` (`propiedad_id`),
  INDEX `idx_paquete` (`paquete_id`),
  CONSTRAINT `fk_prop_paq_propiedad` FOREIGN KEY (`propiedad_id`) REFERENCES `propiedad` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prop_paq_paquete` FOREIGN KEY (`paquete_id`) REFERENCES `paquetes_adicionales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
