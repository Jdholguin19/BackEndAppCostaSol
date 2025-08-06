-- Crear tabla de configuración para el sistema
CREATE TABLE IF NOT EXISTS `configuracion_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_creado` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizado` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuración inicial
INSERT INTO `configuracion_sistema` (`clave`, `valor`, `descripcion`) VALUES
('garantias_porcentaje_minimo', '50', 'Porcentaje mínimo de progreso de construcción para mostrar menú Garantías'),
('garantias_habilitado', '1', '1=Habilitado, 0=Deshabilitado - Controla si se muestra el menú Garantías');

-- Comentario: Esta tabla permite ajustar el umbral sin cambiar código 