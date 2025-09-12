-- Script para migrar cronograma.json a tablas SQL

--
-- Estructura de tabla para `cronograma_fases`
--
CREATE TABLE `cronograma_fases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para `cronograma_tareas`
--
CREATE TABLE `cronograma_tareas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fase_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fase_id` (`fase_id`),
  CONSTRAINT `cronograma_tareas_ibfk_1` FOREIGN KEY (`fase_id`) REFERENCES `cronograma_fases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cronograma_fases`
--
INSERT INTO `cronograma_fases` (`id`, `titulo`, `fecha_inicio`, `fecha_fin`) VALUES
(1, 'Cimentación y Funcionalidades Base', '2025-07-21', '2025-07-22'),
(2, 'Desarrollo del Nuevo Sistema de Agendamiento (Backend)', '2025-07-23', '2025-08-01'),
(3, 'Funcionalidades para Responsables y Rediseño de Tickets', '2025-08-04', '2025-08-08');

--
-- Volcado de datos para la tabla `cronograma_tareas`
--
-- Tareas para la Fase 1
INSERT INTO `cronograma_tareas` (`fase_id`, `titulo`, `descripcion`) VALUES
(1, 'Diseño y Expansión de la Base de Datos', 'Análisis de requerimientos y modificación de la estructura de la base de datos para soportar nuevas funcionalidades.');

-- Tareas para la Fase 2
INSERT INTO `cronograma_tareas` (`fase_id`, `titulo`, `descripcion`) VALUES
(2, 'Modernización de la Interfaz de Agendamiento', 'Se modernizó la interfaz de Front/cita_nueva.php con un calendario interactivo y un selector de hora tipo "rueda".'),
(2, 'Desarrollo del Sistema de Notificaciones Push (OneSignal)', 'Implementación de notificaciones push con una ventana de suscripción personalizada y un switch de control en el perfil.'),
(2, 'Implementación de Agendamiento por Responsables', 'Creación de Front/cita_responsable.php para que el personal pueda agendar citas para los clientes.');

-- Tareas para la Fase 3
INSERT INTO `cronograma_tareas` (`fase_id`, `titulo`, `descripcion`) VALUES
(3, 'Creación del Sistema de Perfil de Usuario', 'Desarrollo de Front/perfil.php y la API para la gestión de la cuenta del cliente.'),
(3, 'Rediseño de la Interfaz de Hilo de Chat para CTG y PQR', 'Las páginas de detalle de CTG y PQR fueron rediseñadas para mostrar las respuestas como un chat.'),
(3, 'Implementación del Sistema de Envío de Correos Transaccionales', 'Creación de un script centralizado para notificar a los responsables sobre nuevas asignaciones.');

