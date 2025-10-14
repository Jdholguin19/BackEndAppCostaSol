-- Crear tabla garantias
CREATE TABLE IF NOT EXISTS `garantias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `tiempo_garantia_meses` smallint(5) unsigned NOT NULL COMMENT 'Tiempo de garantía en meses',
  `tipo_propiedad_id` tinyint(3) unsigned DEFAULT NULL COMMENT 'FK a tipo_propiedad.id, NULL = aplica a todos los tipos',
  `estado` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = inactivo, 1 = activo',
  `orden` smallint(5) unsigned DEFAULT '0' COMMENT 'Orden de visualización',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tipo_propiedad` (`tipo_propiedad_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_orden` (`orden`),
  CONSTRAINT `fk_garantias_tipo_propiedad` FOREIGN KEY (`tipo_propiedad_id`) REFERENCES `tipo_propiedad` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Insertar datos de ejemplo basados en los tipos_ctg actuales
INSERT INTO `garantias` (`nombre`, `descripcion`, `tiempo_garantia_meses`, `tipo_propiedad_id`, `estado`, `orden`) VALUES
('Estructura', 'Garantía de estructura de la propiedad', 60, NULL, 1, 1),
('Instalaciones Eléctricas', 'Garantía de instalaciones eléctricas', 24, NULL, 1, 2),
('Instalaciones Sanitarias', 'Garantía de instalaciones sanitarias', 24, NULL, 1, 3),
('Acabados', 'Garantía de acabados y pintura', 12, NULL, 1, 4),
('Puertas y Ventanas', 'Garantía de puertas y ventanas', 12, NULL, 1, 5),
('Techo', 'Garantía de techo e impermeabilización', 36, NULL, 1, 6),
('Jardinería', 'Garantía de jardinería y áreas verdes', 12, NULL, 1, 7);
('Techo', 'Garantía de techo e impermeabilización', 3.00, NULL, 1, 6),
('Jardinería', 'Garantía de jardinería y áreas verdes', 1.00, NULL, 1, 7);