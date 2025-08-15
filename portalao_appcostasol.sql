-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-08-2025 a las 19:49:18
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `portalao_appcostasol`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agendamiento_visitas`
--

CREATE TABLE `agendamiento_visitas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `responsable_id` bigint(20) UNSIGNED NOT NULL,
  `proposito_id` tinyint(3) UNSIGNED NOT NULL,
  `fecha_ingreso` datetime DEFAULT current_timestamp(),
  `fecha_reunion` date NOT NULL,
  `hora_reunion` time NOT NULL,
  `estado` enum('PROGRAMADO','REALIZADO','CANCELADO') DEFAULT 'PROGRAMADO',
  `resultado` varchar(150) DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `id_propiedad` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `agendamiento_visitas`
--

INSERT INTO `agendamiento_visitas` (`id`, `id_usuario`, `responsable_id`, `proposito_id`, `fecha_ingreso`, `fecha_reunion`, `hora_reunion`, `estado`, `resultado`, `fecha_actualizacion`, `id_propiedad`) VALUES
(38, 9, 2, 1, '2025-08-13 11:15:58', '2025-08-14', '13:00:00', 'PROGRAMADO', NULL, NULL, 10),
(39, 8, 1, 2, '2025-08-13 11:16:35', '2025-08-15', '10:00:00', 'PROGRAMADO', NULL, NULL, 5),
(40, 9, 1, 3, '2025-08-13 11:16:59', '2025-08-25', '15:00:00', 'PROGRAMADO', NULL, NULL, 10),
(41, 8, 2, 4, '2025-08-13 11:58:47', '2025-08-19', '12:00:00', 'PROGRAMADO', NULL, NULL, 5),
(42, 8, 1, 1, '2025-08-13 12:15:23', '2025-08-27', '15:00:00', 'PROGRAMADO', NULL, NULL, 5),
(43, 8, 2, 1, '2025-08-14 11:50:40', '2025-08-20', '13:00:00', 'PROGRAMADO', NULL, NULL, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ctg`
--

CREATE TABLE `ctg` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `numero_solicitud` varchar(25) NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `id_propiedad` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `subtipo_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `estado_id` tinyint(3) UNSIGNED NOT NULL,
  `descripcion` text DEFAULT NULL,
  `urgencia_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `resolucion` text DEFAULT NULL,
  `url_problema` varchar(2000) DEFAULT NULL,
  `url_solucion` varchar(2000) DEFAULT NULL,
  `responsable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT current_timestamp(),
  `fecha_compromiso` datetime DEFAULT NULL,
  `fecha_resolucion` datetime DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  `observaciones` varchar(700) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `ctg`
--

INSERT INTO `ctg` (`id`, `numero_solicitud`, `id_usuario`, `id_propiedad`, `tipo_id`, `subtipo_id`, `estado_id`, `descripcion`, `urgencia_id`, `resolucion`, `url_problema`, `url_solucion`, `responsable_id`, `fecha_ingreso`, `fecha_compromiso`, `fecha_resolucion`, `fecha_actualizacion`, `observaciones`) VALUES
(1, 'SAC00001', 2, 2, 6, 17, 1, 'El toma corriente de las luces de la sala no esta energizado', 1, NULL, 'https://app.costasol.com.ec/ImagenesPQR_problema/logCostaSol.jpg', NULL, 1, '2025-06-24 11:17:38', '2025-06-26 12:09:44', NULL, NULL, NULL),
(5, 'SAC00004', 2, 1, 4, 9, 2, 'no cierran correctamentes las puertas del cuarto master', 1, NULL, 'https://app.costasol.com.ec/ImagenesPQR_problema/logCostaSol.jpg', '', 1, '2025-06-24 11:32:11', '2025-06-26 12:28:18', NULL, NULL, NULL),
(6, 'SAC00006', 2, 2, 1, 2, 1, 'esto es un pqr de prueba', 1, NULL, 'https://app.costasol.com.ec/ImagenesPQR_problema/685c7603bc9c4-Alba_Bosque.jpg', NULL, 1, '2025-06-25 16:19:47', '2025-06-30 16:19:47', NULL, NULL, NULL),
(7, 'SAC00007', 7, 3, 1, 1, 1, 'Arreglar el sistema', 2, NULL, NULL, NULL, 2, '2025-07-23 09:15:36', '2025-07-28 09:15:36', NULL, NULL, NULL),
(17, 'SAC00008', 7, 3, 4, 12, 1, 'HOLS', 1, NULL, NULL, NULL, 1, '2025-07-23 16:26:26', '2025-07-28 16:26:26', NULL, NULL, NULL),
(18, 'SAC00018', 8, 5, 1, 1, 1, 'Necesito ayuda con esto', 2, NULL, NULL, NULL, 1, '2025-07-24 16:09:34', '2025-07-29 16:09:34', NULL, NULL, NULL),
(21, 'SAC00021', 8, 5, 8, 28, 2, 'Que tal', 1, NULL, NULL, NULL, 2, '2025-07-30 12:01:19', '2025-08-04 12:01:19', NULL, '2025-08-08 11:04:05', 'clientes es menso**'),
(22, 'SAC00022', 8, 5, 6, 20, 1, 'No está bien puesto', 1, NULL, NULL, NULL, 2, '2025-08-13 15:16:20', '2025-08-18 15:16:20', NULL, '2025-08-13 16:44:30', 'juhjkhjn'),
(23, 'SAC00023', 8, 5, 1, 3, 1, 'no cubre', 1, NULL, NULL, NULL, 3, '2025-08-15 08:56:43', '2025-08-20 08:56:43', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_ctg`
--

CREATE TABLE `estado_ctg` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `estado_ctg`
--

INSERT INTO `estado_ctg` (`id`, `nombre`) VALUES
(2, 'En progreso'),
(1, 'Ingresado'),
(6, 'Negado'),
(5, 'Resuelto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_pqr`
--

CREATE TABLE `estado_pqr` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `estado_pqr`
--

INSERT INTO `estado_pqr` (`id`, `nombre`) VALUES
(2, 'En concideración'),
(1, 'Recibido');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_propiedad`
--

CREATE TABLE `estado_propiedad` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `estado_propiedad`
--

INSERT INTO `estado_propiedad` (`id`, `nombre`) VALUES
(6, 'BLOQUEADA'),
(1, 'DISPONIBLE'),
(7, 'EN PROCESO DE DESISTIMIENTO'),
(4, 'EN_CONSTRUCCIÓN'),
(5, 'ENTREGADA'),
(2, 'RESERVADA'),
(3, 'VENDIDA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etapa_construccion`
--

CREATE TABLE `etapa_construccion` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `nombre` varchar(40) NOT NULL,
  `porcentaje` tinyint(4) NOT NULL,
  `descripcion` varchar(120) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `etapa_construccion`
--

INSERT INTO `etapa_construccion` (`id`, `nombre`, `porcentaje`, `descripcion`, `estado`, `fecha_creada`) VALUES
(1, 'Cimentación', 10, 'La cimentación es la base de la casa. Aquí se prepara el suelo y se echa el cemento que sostiene toda la estructura.', 1, '2025-06-12 10:39:44'),
(2, 'Losa', 20, 'La etapa de losa es cuando se arma y se vacía el piso de concreto que va encima de las paredes. Es como poner el “techo”', 1, '2025-06-12 10:39:44'),
(3, 'Cubierta terminada', 45, 'La etapa de cubierta terminada es cuando ya se coloca el techo final de la casa. Puede ser de losa, teja, zinc, etc.', 1, '2025-06-12 10:45:32'),
(4, 'Habitabilidad', 95, 'La etapa de habitabilidad es cuando la casa ya está lista para vivir. Ya tiene puertas, ventanas, servicios básicos, etc', 1, '2025-06-12 10:45:32'),
(5, 'Entrega', 100, 'La etapa de entrega es cuando te entregan oficialmente la casa. Ya está terminada, revisada y lista para habitarla.', 1, '2025-06-12 10:47:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu`
--

CREATE TABLE `menu` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `nombre` varchar(40) NOT NULL,
  `descripcion` varchar(120) DEFAULT NULL,
  `url_icono` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `orden` tinyint(3) UNSIGNED DEFAULT 0,
  `fecha_creado` datetime DEFAULT current_timestamp(),
  `fecha_actualizado` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `usuario_actualizo` bigint(20) UNSIGNED DEFAULT NULL,
  `menu_bar` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `menu`
--

INSERT INTO `menu` (`id`, `nombre`, `descripcion`, `url_icono`, `estado`, `orden`, `fecha_creado`, `fecha_actualizado`, `usuario_actualizo`, `menu_bar`) VALUES
(1, 'Selección Acabados', 'Personaliza tu propiedad ', 'https://app.costasol.com.ec/iconos/SeleccionAcabados.svg', 1, 1, '2025-06-13 08:28:02', NULL, NULL, 0),
(2, 'CTG', 'Contingencias', 'https://app.costasol.com.ec/iconos/PQR.svg', 1, 14, '2025-06-13 08:33:54', '2025-08-14 11:08:21', NULL, 0),
(3, 'Agendar Visitas', 'Programa una visita a tu propiedad', 'https://app.costasol.com.ec/iconos/Agendamientos.svg', 0, 12, '2025-06-13 09:06:59', '2025-08-14 11:06:59', NULL, 1),
(4, 'Empresas Aliadas', 'Descuentos y promociones exclusivas', 'https://app.costasol.com.ec/iconos/EmpresaAliada.svg', 0, 6, '2025-06-13 09:17:59', '2025-08-14 10:32:17', NULL, 1),
(5, 'Crédito Hipotecario', 'Seguimiento y estado del proceso de tu crédito', 'https://app.costasol.com.ec/iconos/CreditoHipotecario.svg', 1, 5, '2025-06-13 09:20:28', '2025-06-13 09:20:54', NULL, 0),
(6, 'Garantias', 'Información sobre garantías', 'https://app.costasol.com.ec/iconos/Garantias.svg', 1, 15, '2025-06-13 09:36:51', '2025-08-14 13:18:01', NULL, 0),
(7, 'Calendario Responsable', 'Revisa tu agenda', 'https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/4c040ff0348e783b0b48564151d05dc6beee1420/imagenes/calendario.svg', 1, 7, '2025-07-22 14:49:21', '2025-08-14 11:13:27', NULL, 0),
(8, 'Notificaciones', 'Mantente al día de todo', 'https://app.costasol.com.ec/iconos/Notificaciones.svg', 0, 8, '2025-07-23 14:58:06', '2025-08-14 11:44:29', NULL, 0),
(9, 'PQR', 'Petición, Queja y Recomendaciones', 'https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/4c040ff0348e783b0b48564151d05dc6beee1420/imagenes/pqr.svg', 1, 9, '2025-07-23 14:58:06', '2025-08-14 11:03:46', NULL, 0),
(10, 'Admin-User', 'Administra usuarios', 'https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/7004b621cc1e4d4bd8c71adb35d0584007bdefe5/imagenes/admin.svg', 1, 10, '2025-08-05 12:40:06', '2025-08-05 12:47:33', NULL, 0),
(11, 'MCM', 'Manual de uso, Conservación Y\nMantenimiento de la vivienda', 'https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/15d4cbb5e8fad6336a89f04567a6ca0ee0d7b5c2/imagenes/mcm.svg', 1, 2, '2025-08-06 10:32:06', '2025-08-14 09:22:10', NULL, 0),
(12, 'Paleta Vegetal', 'Inspírate y ornamenta tu jardín ', 'https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/67c46d5b804b0915b795839b6092fa44cbd49ec6/imagenes/tree.svg', 1, 3, '2025-08-06 10:32:06', '2025-08-14 09:22:53', NULL, 0),
(13, 'Admin-Noticias', 'Crea nuevas notificas para los clientes', 'https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/cd80644730b850cd6794d75e174a0df6e063f17d/imagenes/news.svg', 1, 13, '2025-08-06 10:32:06', '2025-08-06 14:46:47', NULL, 0),
(14, 'Admin-Responsable', 'Administra tus responsables', 'https://cdn.prod.website-files.com/5f68a65d0932e3546d41cc61/5f9bb022fda3f6ccfb8e316a_1604038688273-admin%252B-best-shopify-apps.png', 1, 14, '2025-08-13 15:34:06', '2025-08-13 16:38:38', NULL, 0),
(15, 'Ver más', 'Explora todas las opciones', 'https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/ab7c3531d7e648fa535cd942c4d9794737b65156/imagenes/vermas.svg', 1, 4, '2025-08-13 15:34:06', '2025-08-14 13:18:03', NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticia`
--

CREATE TABLE `noticia` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(160) NOT NULL,
  `resumen` varchar(255) NOT NULL,
  `contenido` text DEFAULT NULL,
  `url_imagen` varchar(255) DEFAULT NULL,
  `link_noticia` varchar(255) DEFAULT NULL,
  `orden` smallint(5) UNSIGNED DEFAULT 0,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_publicacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `autor_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `noticia`
--

INSERT INTO `noticia` (`id`, `titulo`, `resumen`, `contenido`, `url_imagen`, `link_noticia`, `orden`, `estado`, `fecha_publicacion`, `fecha_actualizacion`, `autor_id`) VALUES
(1, 'Ciclovia Costasol', 'Cada ciclovía, cada sombra, cada camino libre de autos, es parte de un lugar que fue pensado para ti. ?', 'Cada ciclovía, cada sombra, cada camino libre de autos, es parte de un lugar que fue pensado para ti. ?', 'https://app.costasol.com.ec/ImagenesNoticias/noticia1.jpg', 'https://www.facebook.com/share/p/19N5JeV1xH/', 1, 1, '2025-06-16 11:31:01', NULL, 2),
(2, 'CostaSol la ciudad que respira ', 'Entre áreas verdes, tranquilidad y diseño pensado para ti, así se vive cuando la ciudad se transforma en hogar ??', 'Entre áreas verdes, tranquilidad y diseño pensado para ti, así se vive cuando la ciudad se transforma en hogar ??', 'https://app.costasol.com.ec/ImagenesNoticias/noticia2.jpg', 'https://www.facebook.com/share/p/1AjfbqvBhE/', 2, 1, '2025-06-16 14:11:25', '2025-06-16 14:56:35', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pqr`
--

CREATE TABLE `pqr` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `numero_solicitud` varchar(25) NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `id_propiedad` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `subtipo_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `estado_id` tinyint(3) UNSIGNED NOT NULL,
  `descripcion` text DEFAULT NULL,
  `urgencia_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `resolucion` text DEFAULT NULL,
  `url_problema` varchar(2000) DEFAULT NULL,
  `url_solucion` varchar(2000) DEFAULT NULL,
  `responsable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT current_timestamp(),
  `fecha_compromiso` datetime DEFAULT NULL,
  `fecha_resolucion` datetime DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  `observaciones` varchar(700) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pqr`
--

INSERT INTO `pqr` (`id`, `numero_solicitud`, `id_usuario`, `id_propiedad`, `tipo_id`, `subtipo_id`, `estado_id`, `descripcion`, `urgencia_id`, `resolucion`, `url_problema`, `url_solucion`, `responsable_id`, `fecha_ingreso`, `fecha_compromiso`, `fecha_resolucion`, `fecha_actualizacion`, `observaciones`) VALUES
(2, 'SAC00002', 8, 5, 3, NULL, 1, 'Cambien el piso', NULL, NULL, NULL, NULL, 1, '2025-08-07 08:39:25', '2025-08-12 08:39:25', NULL, '2025-08-13 15:13:40', 'no responde nunca'),
(3, 'SAC00003', 8, 5, 2, NULL, 2, 'Cambien la pared', NULL, NULL, NULL, NULL, 2, '2025-08-07 08:42:31', '2025-08-12 08:42:31', NULL, '2025-08-08 11:19:18', 'este si le sabe'),
(4, 'SAC00004', 8, 5, 3, NULL, 1, 'No dej', NULL, NULL, NULL, NULL, 2, '2025-08-13 15:15:39', '2025-08-18 15:15:39', NULL, '2025-08-15 09:02:00', 'no es pila');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progreso_construccion`
--

CREATE TABLE `progreso_construccion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_propiedad` bigint(20) UNSIGNED DEFAULT NULL,
  `id_etapa` smallint(5) UNSIGNED NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `porcentaje` tinyint(4) DEFAULT NULL,
  `ruta_descarga_sharepoint` varchar(2000) DEFAULT NULL,
  `ruta_visualizacion_sharepoint` varchar(2000) DEFAULT NULL,
  `drive_item_id` varchar(255) DEFAULT NULL,
  `fecha_creado_sharepoint` datetime DEFAULT NULL,
  `usuario_creador` varchar(100) DEFAULT NULL,
  `fecha_modificado_sharepoint` datetime DEFAULT NULL,
  `mz` varchar(10) DEFAULT NULL,
  `villa` varchar(1000) DEFAULT NULL,
  `usuario_modificado_sharepoint` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = activo, 0 = inactivo',
  `url_imagen` varchar(2000) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedad`
--

CREATE TABLE `propiedad` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `tipo_id` tinyint(3) UNSIGNED NOT NULL,
  `etapa_id` smallint(5) UNSIGNED NOT NULL,
  `estado_id` tinyint(3) UNSIGNED NOT NULL,
  `fecha_compra` date DEFAULT NULL,
  `fecha_hipotecario` date DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `fecha_insertado` datetime DEFAULT current_timestamp(),
  `id_urbanizacion` tinyint(3) UNSIGNED NOT NULL,
  `manzana` varchar(10) DEFAULT NULL,
  `solar` varchar(10) DEFAULT NULL,
  `villa` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `propiedad`
--

INSERT INTO `propiedad` (`id`, `id_usuario`, `tipo_id`, `etapa_id`, `estado_id`, `fecha_compra`, `fecha_hipotecario`, `fecha_entrega`, `fecha_insertado`, `id_urbanizacion`, `manzana`, `solar`, `villa`) VALUES
(1, 2, 2, 4, 4, '2025-01-01', '2030-06-01', '2031-06-02', '2025-06-17 08:24:56', 3, '7119', '03', '03'),
(2, 2, 2, 3, 4, '2025-02-01', '2026-01-01', '2026-06-13', '2025-06-17 09:54:30', 3, '7117', '33', '33'),
(3, 7, 2, 4, 4, '2025-07-01', '2030-07-10', '2031-07-08', '2025-07-23 08:57:32', 3, '7119', '03', '03'),
(4, 7, 2, 3, 4, '2025-07-01', '2030-07-01', '2031-07-01', '2025-07-23 09:08:06', 3, '7117', '33', '33'),
(5, 8, 2, 4, 4, '2025-07-01', '2030-07-10', '2031-07-08', '2025-07-24 08:57:32', 3, '9999', '99', '99'),
(10, 9, 2, 3, 4, '2025-07-01', '2030-07-01', '2031-07-01', '2025-07-28 09:08:06', 3, '7117', '33', '33'),
(12, 11, 2, 3, 4, '2025-07-01', '2030-07-01', '2031-07-01', '2025-08-01 09:08:06', 3, '7117', '33', '33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proposito_agendamiento`
--

CREATE TABLE `proposito_agendamiento` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `proposito` varchar(100) NOT NULL,
  `url_icono` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_ingreso` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `proposito_agendamiento`
--

INSERT INTO `proposito_agendamiento` (`id`, `proposito`, `url_icono`, `estado`, `fecha_ingreso`) VALUES
(1, 'Recorrido de Obra', 'https://app.costasol.com.ec/iconos/LogoCostaSolVerde.svg', 1, '2025-06-27 10:26:18'),
(2, 'Elección de acabados', 'https://app.costasol.com.ec/iconos/SeleccionAcabados.svg', 1, '2025-06-27 10:26:18'),
(3, 'Consultas con servicio al cliente ', 'https://app.costasol.com.ec/iconos/Agendamientos.svg', 1, '2025-06-27 10:26:43'),
(4, 'Consultas con crédito y cobranzas', 'https://app.costasol.com.ec/iconos/CreditoHipotecario.svg', 1, '2025-06-27 10:26:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_login`
--

CREATE TABLE `registro_login` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED DEFAULT NULL,
  `id_responsable` bigint(20) UNSIGNED DEFAULT NULL,
  `estado_login` enum('EXITO','FALLIDO') NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_recuperacion_contrasena`
--

CREATE TABLE `registro_recuperacion_contrasena` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `codigo` char(6) NOT NULL,
  `estado_solicitud` enum('PENDIENTE','USADO','VENCIDO') DEFAULT 'PENDIENTE',
  `fecha_solicitud` datetime DEFAULT current_timestamp(),
  `fecha_expira` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `responsable`
--

CREATE TABLE `responsable` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `correo` varchar(120) NOT NULL,
  `contrasena_hash` char(60) DEFAULT NULL,
  `url_foto_perfil` varchar(255) DEFAULT NULL,
  `area` varchar(40) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_ingreso` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `token` varchar(255) DEFAULT NULL,
  `onesignal_player_id` varchar(255) DEFAULT NULL,
  `fecha_actualizacion_player_id` timestamp NULL DEFAULT NULL COMMENT 'Fecha de última actualización del OneSignal Player ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `responsable`
--

INSERT INTO `responsable` (`id`, `nombre`, `correo`, `contrasena_hash`, `url_foto_perfil`, `area`, `estado`, `fecha_ingreso`, `fecha_actualizacion`, `token`, `onesignal_player_id`, `fecha_actualizacion_player_id`) VALUES
(1, 'Ana María Felix', 'coordinadorsac@thaliavictoria.com.ec', '$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa', 'https://app.costasol.com.ec/ImagenesPQR_problema/logCostaSol.jpg', 'SAC', 1, '2025-06-24 11:03:35', '2025-08-15 10:36:00', 'ukK/t1H21xMZny7KrSEMu1ij7wgvbtKf', '4b2ad379-5372-40fe-9532-b46bb026fa7a', '2025-08-15 15:36:00'),
(2, 'Carla Oquendo', 'servicioalcliente@thaliavictoria.com.ec', '$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa', 'https://static.wixstatic.com/media/b80279_33a586f04740464cae96a3a6205d2c19~mv2.png', 'SAC', 1, '2025-06-24 11:07:21', '2025-08-15 12:41:48', '8rfmBYNhoin05F6sFzzhdVLovqcNvZP9', 'b7c880b7-10a3-4d48-a66e-749b20ffbf92', '2025-08-15 17:41:48'),
(3, 'Admin', 'admin@thaliavictoria.com.ec', '$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa', 'https://cdn.prod.website-files.com/5f68a65d0932e3546d41cc61/5f9bb022fda3f6ccfb8e316a_1604038688273-admin%252B-best-shopify-apps.png', 'SAC', 1, '2025-08-13 09:25:21', '2025-08-15 11:50:56', '6zRhS/CR69hEuKfAu+Ej1ZVXbAnW3T7X', 'ac37e2ab-53af-40d3-bd53-6547df90b2a8', '2025-08-15 16:50:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `responsable_disponibilidad`
--

CREATE TABLE `responsable_disponibilidad` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `responsable_id` bigint(20) UNSIGNED NOT NULL,
  `dia_semana` tinyint(3) UNSIGNED NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `fecha_vigencia_desde` date DEFAULT NULL,
  `fecha_vigencia_hasta` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `intervalo_minutos` smallint(6) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `responsable_disponibilidad`
--

INSERT INTO `responsable_disponibilidad` (`id`, `responsable_id`, `dia_semana`, `hora_inicio`, `hora_fin`, `fecha_vigencia_desde`, `fecha_vigencia_hasta`, `activo`, `intervalo_minutos`) VALUES
(1, 1, 1, '09:00:00', '17:00:00', '2025-06-01', '2026-06-01', 1, 45),
(2, 1, 2, '09:00:00', '17:00:00', '2025-06-01', '2026-06-01', 1, 45),
(3, 1, 3, '09:00:00', '17:00:00', '2025-06-01', '2026-06-01', 1, 45),
(4, 1, 4, '09:00:00', '17:00:00', '2025-06-01', '2026-06-01', 1, 45),
(5, 1, 5, '09:00:00', '17:00:00', '2025-06-01', '2026-06-01', 1, 45),
(6, 2, 1, '08:00:00', '16:00:00', '2025-06-01', '2026-06-01', 1, 45),
(7, 2, 2, '08:00:00', '16:00:00', '2025-06-01', '2026-06-01', 1, 45),
(8, 2, 3, '08:00:00', '16:00:00', '2025-06-01', '2026-06-01', 1, 45),
(9, 2, 4, '08:00:00', '16:00:00', '2025-06-01', '2026-06-01', 1, 45),
(10, 2, 5, '08:00:00', '16:00:00', '2025-06-01', '2026-06-01', 1, 45);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuesta_ctg`
--

CREATE TABLE `respuesta_ctg` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ctg_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `responsable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mensaje` text NOT NULL,
  `url_adjunto` varchar(2000) DEFAULT NULL,
  `fecha_respuesta` datetime NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuesta_pqr`
--

CREATE TABLE `respuesta_pqr` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pqr_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `responsable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mensaje` text NOT NULL,
  `url_adjunto` varchar(2000) DEFAULT NULL,
  `fecha_respuesta` datetime NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Cliente', 'Persona que ha adquirido una propiedad en Costasol pero aun no ha sido entregada.'),
(2, 'Residente', 'Persona a la que ya se le entrego una propiedad en Costasol'),
(3, 'SAC', 'Administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_menu`
--

CREATE TABLE `rol_menu` (
  `rol_id` tinyint(3) UNSIGNED NOT NULL,
  `menu_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `rol_menu`
--

INSERT INTO `rol_menu` (`rol_id`, `menu_id`) VALUES
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 8),
(1, 9),
(1, 12),
(1, 15),
(2, 1),
(2, 2),
(2, 11),
(3, 7),
(3, 10),
(3, 13);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subtipo_ctg`
--

CREATE TABLE `subtipo_ctg` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `tipo_id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `urgencia_id` tinyint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `subtipo_ctg`
--

INSERT INTO `subtipo_ctg` (`id`, `tipo_id`, `nombre`, `urgencia_id`) VALUES
(1, 1, 'Filtracion de agua por cubierta', 2),
(2, 1, 'Flashing desalineado', 1),
(3, 1, 'Abertura entre alero y viga de cubierta', 1),
(4, 1, 'Otros', 1),
(5, 2, 'Filtración de agua por falta de silicón en ventana', 2),
(6, 2, 'Seguro o rodachines dañados', 1),
(7, 2, 'Otros', 1),
(8, 3, 'Fisuras cerramiento lateral y/o posterior', 1),
(9, 4, 'Bisagra floja', 1),
(10, 4, 'Daño en tapa (descuadre, falta de perforación)', 1),
(11, 4, 'Defecto de instalación de cisterna', 1),
(12, 4, 'Otros', 1),
(13, 5, 'Tubería a/c: obstruidos', 1),
(14, 5, 'Tubería a/c: perforada', 1),
(15, 5, 'Tubería a/c: instalación incorrecta', 1),
(16, 5, 'Otros', 1),
(17, 6, 'Cajas de paso red/datos', 1),
(18, 6, 'Tubería red/datos obstruido', 1),
(19, 6, 'Tubería red/datos sin pasante', 1),
(20, 6, 'Tubería red/datos guías galvanizadas oxidadas', 1),
(21, 6, 'Otros', 1),
(22, 7, 'Interruptores o tomacorriente defectuosos', 1),
(23, 7, 'Cableado de puntos eléctricos', 2),
(24, 7, 'Falta de suministro de tomacorrientes o interrupto', 1),
(25, 7, 'Cortocircuito', 2),
(26, 7, 'Otros', 1),
(27, 8, 'Drenajes obstruidos y/o tapados', 1),
(28, 8, 'Cajas y conexiones de aa.ss', 1),
(29, 8, 'Silicón y/o empore en piezas sanitarias', 1),
(30, 8, 'Piezas sanitarias flojas y/o mal instaladas', 1),
(31, 8, 'Fuga de agua en accesorios, griferías y/o piezas s', 2),
(32, 8, 'Suministro de accesorios', 1),
(33, 8, 'Conexión calefón agua caliente o fria', 1),
(34, 8, 'Malos olores por sifones o piezas sanitarias', 1),
(35, 8, 'Daño de accesorios sanitarios', 1),
(36, 8, 'Fuga incontenible de agua', 2),
(37, 8, 'Otros', 1),
(38, 9, 'Maniguetas con falla', 1),
(39, 9, 'Bisagras o rieles oxidadas y/o con ruido', 1),
(40, 9, 'Material humedo / soplado', 1),
(41, 9, 'Olor a humedad del material', 1),
(42, 9, 'Otros', 1),
(43, 10, 'Fisuras en paredes interiores y/o exteriores', 1),
(44, 10, 'Enlucido fofo', 1),
(45, 10, 'Fisuras en boquetes de ventanas', 1),
(46, 10, 'Empaste o pintura soplada / desprendida', 1),
(47, 10, 'Albañileria enlucido', 1),
(48, 10, 'Otros', 1),
(49, 11, 'Anclajes deficientes (flojo)', 1),
(50, 11, 'Otros', 1),
(51, 12, 'Puerta metálica - descuadrada y/o desoldada', 1),
(52, 12, 'Cerraduras principal con fallas', 1),
(53, 12, 'Puerta metálica - cerradura dañada', 1),
(54, 12, 'Cerraduras/ pomo con fallas', 1),
(55, 12, 'Bisagras con falla', 1),
(56, 12, 'Otros', 1),
(57, 13, 'Piso:Placa fofa', 1),
(58, 13, 'Paredes: placa fofa', 1),
(59, 13, 'Acabado de empore', 1),
(60, 13, 'Otros', 1),
(61, 14, 'Defecto de instalación de planchas de gypsum', 1),
(62, 14, 'Tumbado humedo por filtracion', 1),
(63, 14, 'Fisuras en tumbado de gypsum tipo losa', 1),
(64, 14, 'Otros', 1),
(65, 15, 'Otros', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_ctg`
--

CREATE TABLE `tipo_ctg` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `tiempo_garantia_min` varchar(60) DEFAULT NULL,
  `tiempo_garantia_max` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_ctg`
--

INSERT INTO `tipo_ctg` (`id`, `nombre`, `tiempo_garantia_min`, `tiempo_garantia_max`) VALUES
(1, 'Cubierta', '1', '1'),
(2, 'Aluminio y Vidrio', '0.3', '1'),
(3, 'Cerramiento', '1', '1'),
(4, 'Cisterna', '0.3', '1'),
(5, 'Instalaciones de Climatización', '0.6', '0.6'),
(6, 'Instalaciones de Voz y Datos', '1', '1'),
(7, 'Instalaciones Eléctricas', '0.3', '1'),
(8, 'Instalaciones Sanitarias', '0.3', '0.6'),
(9, 'Mobiliario cocina-baños-closets', '0.3', '0.6'),
(10, 'Paredes', '1', '1'),
(11, 'Pasamanos', '0.3', '0.3'),
(12, 'Puertas', '0.3', '0.3'),
(13, 'Recubrimientos', '0.3', '0.6'),
(14, 'Tumbado', '1', '1'),
(15, 'Otros', '0.3', '0.3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_pqr`
--

CREATE TABLE `tipo_pqr` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_pqr`
--

INSERT INTO `tipo_pqr` (`id`, `nombre`) VALUES
(1, 'Peticion'),
(2, 'Queja'),
(3, 'Recomendacion');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_propiedad`
--

CREATE TABLE `tipo_propiedad` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_propiedad`
--

INSERT INTO `tipo_propiedad` (`id`, `nombre`) VALUES
(2, 'Casa'),
(3, 'Departamento'),
(1, 'Terreno');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `urbanizacion`
--

CREATE TABLE `urbanizacion` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `urbanizacion`
--

INSERT INTO `urbanizacion` (`id`, `nombre`, `estado`, `fecha_creada`) VALUES
(1, 'Arienzo', 1, '2025-06-17 08:15:24'),
(2, 'Basilea', 1, '2025-06-17 08:15:24'),
(3, 'Catania', 1, '2025-06-17 08:15:24'),
(4, 'Davos', 1, '2025-06-17 08:15:24'),
(5, 'Estanzza', 1, '2025-06-17 08:15:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `urgencia_ctg`
--

CREATE TABLE `urgencia_ctg` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `urgencia_ctg`
--

INSERT INTO `urgencia_ctg` (`id`, `nombre`) VALUES
(1, 'BASICA'),
(2, 'URGENTE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rol_id` tinyint(3) UNSIGNED NOT NULL,
  `nombres` varchar(60) NOT NULL,
  `apellidos` varchar(60) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `correo` varchar(120) NOT NULL,
  `contrasena_hash` char(60) NOT NULL,
  `numero_propiedades` smallint(5) UNSIGNED DEFAULT 0,
  `url_foto_perfil` varchar(255) DEFAULT NULL,
  `fecha_insertado` datetime DEFAULT current_timestamp(),
  `token` varchar(255) DEFAULT NULL,
  `onesignal_player_id` varchar(255) DEFAULT NULL,
  `fecha_actualizacion_player_id` timestamp NULL DEFAULT NULL COMMENT 'Fecha de última actualización del OneSignal Player ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `rol_id`, `nombres`, `apellidos`, `cedula`, `telefono`, `correo`, `contrasena_hash`, `numero_propiedades`, `url_foto_perfil`, `fecha_insertado`, `token`, `onesignal_player_id`, `fecha_actualizacion_player_id`) VALUES
(2, 1, 'Guillermo', 'Coello', '0922797790', '593982033045', 'gcoello@costasol.com.ec', '$2y$10$RGAlN206FkvrivRqB86qE.pahU4Q7ThD3dWKgndwpJFbb75WN2t/.', 1, 'https://app.costasol.com.ec/FotoPerfil/FotoUsuarioGC.jpg', '2025-06-10 10:42:58', NULL, NULL, NULL),
(3, 2, 'Guillermo2', 'Coello2', NULL, NULL, 'dr.gecb21@hotmail.com', '$2y$10$MdNXTfLD2h3lbelU4QdN4eVssugUOrpDOrq5Yxx9U48ok8Finrh/u', 0, NULL, '2025-06-10 11:05:44', NULL, NULL, NULL),
(4, 1, 'Carlos', 'Pablo', NULL, NULL, 'carlospablo@thaliavictoria.com.ec', '$2y$10$S.HaX.E8Y0kc2hiGc1NJLus7h760yWOvh6ZqNcUSCx7T73iGRvVHi', 0, NULL, '2025-07-03 13:41:37', NULL, NULL, NULL),
(5, 1, 'Rafael', 'Romero', NULL, NULL, 'rromero@thaliavictoria.com.ec', '$2y$10$uqExlkQ7Xw6xfgzbQyqefOJmFOGrL4Qt.iMnUliWzb5iH9xVBZIou', 0, NULL, '2025-07-14 16:11:24', NULL, NULL, NULL),
(6, 1, 'Jonathan', 'Quijano', NULL, NULL, 'jquijano@thaliavictoria.com.ec', '$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2', 0, NULL, '2025-07-14 16:12:49', NULL, NULL, NULL),
(7, 1, 'Joffre', 'Holguin', NULL, NULL, 'joffreholguin19@gmail.com', '$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa', 1, 'https://cdn-icons-png.flaticon.com/512/9187/9187532.png', '2025-07-22 10:50:30', 'JEYSzRChFPbwcMEJGJK/+6RZxRrktqnP', NULL, NULL),
(8, 2, 'Daniel', 'Alarcon', NULL, NULL, 'danielalarcon@gmail.com', '$2y$10$KxKCaEiyWPjABgwuzODM.eWQlVCmLGgqISjp.5dkCDt5UYowRSGHC', 1, 'https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_640.png', '2025-07-24 16:06:19', 'JPpKlQBk0BhYeite5i+rsT9mQ7iDYDMZ', 'b7c880b7-10a3-4d48-a66e-749b20ffbf92', '2025-08-15 15:35:35'),
(9, 1, 'Felipe', 'Pilligua', NULL, NULL, 'fepilligua@gmail.com', '$2y$10$reWiHb52f7EPZNo98Q3kz.H0Df6eFFB6FJrgll3nGywKhCmgl/hvC', 1, 'https://cdn-icons-png.flaticon.com/512/219/219983.png', '2025-07-28 08:42:37', '65oP60rZ0yYLCKdp22WUzpI07dl/KViI', NULL, '2025-08-14 14:24:51'),
(10, 1, 'Jose', 'Tenesaca', NULL, NULL, 'josesaca@gmail.com', '$2y$10$8LEm..7rJ3ii/7QWAivJOuHJMjh3lQ4n/qEaqTI66Wu3YPKLcpble', 0, NULL, '2025-08-05 13:25:22', 'iCHj2yX2GP5NG9lt7+NCkN/wPFr7tH2D', NULL, '2025-08-05 18:32:06'),
(11, 1, 'Martin', 'Mera', NULL, NULL, 'martin@gmial.com', '$2y$10$UtKsBWDz.Hdf5DPHSlmV5etztt5mez967KwQccui431EB5woBL4au', 0, 'https://cdn-icons-png.flaticon.com/512/219/219983.png', '2025-08-06 08:16:43', 'tDe030ZBD99SVoDhzEecbw4h/pEyY0v1', NULL, '2025-08-06 14:35:50'),
(12, 1, 'prueba', '1', NULL, NULL, 'prueba@prueba.com', '$2y$10$447WYbjzXhELxQuweQbcEuNgS4HiO.PxkyqBbm7zQ4XC.evK9FpoS', 0, NULL, '2025-08-13 12:37:40', 'mc5PPSjS365g9XGa8+xrmQWPqdUg8t8s', NULL, '2025-08-13 17:37:52'),
(13, 1, 'prueba', '2', NULL, NULL, 'prueba2@prueba.com', '$2y$10$K2tKUN8/SS/o8Nhr7wnG3uTkzy5FdI3H8.kze3wAxd5w7a6tA0OZO', 0, NULL, '2025-08-13 12:39:18', NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agendamiento_visitas`
--
ALTER TABLE `agendamiento_visitas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_responsable_hora` (`responsable_id`,`fecha_reunion`,`hora_reunion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_agendamiento_propiedad` (`id_propiedad`),
  ADD KEY `fk_visita_proposito` (`proposito_id`);

--
-- Indices de la tabla `ctg`
--
ALTER TABLE `ctg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_pqr_numero` (`numero_solicitud`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `responsable_id` (`responsable_id`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `urgencia_id` (`urgencia_id`),
  ADD KEY `fk_pqr_propiedad` (`id_propiedad`),
  ADD KEY `idx_pqr_tipo` (`tipo_id`),
  ADD KEY `idx_pqr_subtipo` (`subtipo_id`);

--
-- Indices de la tabla `estado_ctg`
--
ALTER TABLE `estado_ctg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `estado_pqr`
--
ALTER TABLE `estado_pqr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `estado_propiedad`
--
ALTER TABLE `estado_propiedad`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `etapa_construccion`
--
ALTER TABLE `etapa_construccion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_actualizo` (`usuario_actualizo`);

--
-- Indices de la tabla `noticia`
--
ALTER TABLE `noticia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autor_id` (`autor_id`);

--
-- Indices de la tabla `pqr`
--
ALTER TABLE `pqr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_pqr_numero` (`numero_solicitud`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `responsable_id` (`responsable_id`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `fk_pqr_propiedad` (`id_propiedad`),
  ADD KEY `idx_pqr_tipo` (`tipo_id`),
  ADD KEY `fk_pqr_subtipo` (`subtipo_id`),
  ADD KEY `fk_pqr_urgencia` (`urgencia_id`);

--
-- Indices de la tabla `progreso_construccion`
--
ALTER TABLE `progreso_construccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_propiedad` (`id_propiedad`),
  ADD KEY `id_etapa` (`id_etapa`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_sharepoint` (`fecha_creado_sharepoint`);

--
-- Indices de la tabla `propiedad`
--
ALTER TABLE `propiedad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `tipo_id` (`tipo_id`),
  ADD KEY `etapa_id` (`etapa_id`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `fk_propiedad_urbanizacion` (`id_urbanizacion`);

--
-- Indices de la tabla `proposito_agendamiento`
--
ALTER TABLE `proposito_agendamiento`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `registro_login`
--
ALTER TABLE `registro_login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_responsable` (`id_responsable`);

--
-- Indices de la tabla `registro_recuperacion_contrasena`
--
ALTER TABLE `registro_recuperacion_contrasena`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `responsable`
--
ALTER TABLE `responsable`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idx_responsable_onesignal_player_id` (`onesignal_player_id`);

--
-- Indices de la tabla `responsable_disponibilidad`
--
ALTER TABLE `responsable_disponibilidad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_disponibilidad_lookup` (`responsable_id`,`dia_semana`,`fecha_vigencia_desde`,`fecha_vigencia_hasta`,`activo`);

--
-- Indices de la tabla `respuesta_ctg`
--
ALTER TABLE `respuesta_ctg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pqr_id` (`ctg_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_resp_resp` (`responsable_id`);

--
-- Indices de la tabla `respuesta_pqr`
--
ALTER TABLE `respuesta_pqr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pqr_id` (`pqr_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_resp_resp` (`responsable_id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `rol_menu`
--
ALTER TABLE `rol_menu`
  ADD PRIMARY KEY (`rol_id`,`menu_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indices de la tabla `subtipo_ctg`
--
ALTER TABLE `subtipo_ctg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tipo_nombre` (`tipo_id`,`nombre`),
  ADD KEY `fk_subtipo_urgencia` (`urgencia_id`);

--
-- Indices de la tabla `tipo_ctg`
--
ALTER TABLE `tipo_ctg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tipo_pqr`
--
ALTER TABLE `tipo_pqr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tipo_propiedad`
--
ALTER TABLE `tipo_propiedad`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `urbanizacion`
--
ALTER TABLE `urbanizacion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `urgencia_ctg`
--
ALTER TABLE `urgencia_ctg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `uk_usuario_cedula` (`cedula`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `idx_usuario_onesignal_player_id` (`onesignal_player_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `agendamiento_visitas`
--
ALTER TABLE `agendamiento_visitas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `ctg`
--
ALTER TABLE `ctg`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `estado_ctg`
--
ALTER TABLE `estado_ctg`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `estado_pqr`
--
ALTER TABLE `estado_pqr`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estado_propiedad`
--
ALTER TABLE `estado_propiedad`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `etapa_construccion`
--
ALTER TABLE `etapa_construccion`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `menu`
--
ALTER TABLE `menu`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `noticia`
--
ALTER TABLE `noticia`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pqr`
--
ALTER TABLE `pqr`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `progreso_construccion`
--
ALTER TABLE `progreso_construccion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `propiedad`
--
ALTER TABLE `propiedad`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `proposito_agendamiento`
--
ALTER TABLE `proposito_agendamiento`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `registro_login`
--
ALTER TABLE `registro_login`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registro_recuperacion_contrasena`
--
ALTER TABLE `registro_recuperacion_contrasena`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `responsable`
--
ALTER TABLE `responsable`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `responsable_disponibilidad`
--
ALTER TABLE `responsable_disponibilidad`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `respuesta_ctg`
--
ALTER TABLE `respuesta_ctg`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuesta_pqr`
--
ALTER TABLE `respuesta_pqr`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `subtipo_ctg`
--
ALTER TABLE `subtipo_ctg`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de la tabla `tipo_ctg`
--
ALTER TABLE `tipo_ctg`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tipo_pqr`
--
ALTER TABLE `tipo_pqr`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipo_propiedad`
--
ALTER TABLE `tipo_propiedad`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `urbanizacion`
--
ALTER TABLE `urbanizacion`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `urgencia_ctg`
--
ALTER TABLE `urgencia_ctg`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agendamiento_visitas`
--
ALTER TABLE `agendamiento_visitas`
  ADD CONSTRAINT `agendamiento_visitas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `agendamiento_visitas_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`),
  ADD CONSTRAINT `fk_agendamiento_propiedad` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedad` (`id`),
  ADD CONSTRAINT `fk_visita_proposito` FOREIGN KEY (`proposito_id`) REFERENCES `proposito_agendamiento` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `ctg`
--
ALTER TABLE `ctg`
  ADD CONSTRAINT `ctg_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `ctg_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`),
  ADD CONSTRAINT `ctg_ibfk_5` FOREIGN KEY (`estado_id`) REFERENCES `estado_ctg` (`id`),
  ADD CONSTRAINT `ctg_ibfk_6` FOREIGN KEY (`urgencia_id`) REFERENCES `urgencia_ctg` (`id`),
  ADD CONSTRAINT `fk_ctg_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado_ctg` (`id`),
  ADD CONSTRAINT `fk_ctg_subtipo` FOREIGN KEY (`subtipo_id`) REFERENCES `subtipo_ctg` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ctg_tipo` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_ctg` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ctg_urgencia` FOREIGN KEY (`urgencia_id`) REFERENCES `urgencia_ctg` (`id`),
  ADD CONSTRAINT `fk_pqr_propiedad` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedad` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`usuario_actualizo`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `noticia`
--
ALTER TABLE `noticia`
  ADD CONSTRAINT `noticia_ibfk_1` FOREIGN KEY (`autor_id`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `pqr`
--
ALTER TABLE `pqr`
  ADD CONSTRAINT `fk_pqr_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado_pqr` (`id`),
  ADD CONSTRAINT `fk_pqr_propiedad_ref` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedad` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pqr_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`),
  ADD CONSTRAINT `fk_pqr_subtipo` FOREIGN KEY (`subtipo_id`) REFERENCES `subtipo_ctg` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pqr_tipo` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_pqr` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pqr_urgencia` FOREIGN KEY (`urgencia_id`) REFERENCES `urgencia_ctg` (`id`),
  ADD CONSTRAINT `fk_pqr_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `progreso_construccion`
--
ALTER TABLE `progreso_construccion`
  ADD CONSTRAINT `progreso_construccion_ibfk_1` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedad` (`id`),
  ADD CONSTRAINT `progreso_construccion_ibfk_2` FOREIGN KEY (`id_etapa`) REFERENCES `etapa_construccion` (`id`);

--
-- Filtros para la tabla `propiedad`
--
ALTER TABLE `propiedad`
  ADD CONSTRAINT `fk_propiedad_urbanizacion` FOREIGN KEY (`id_urbanizacion`) REFERENCES `urbanizacion` (`id`),
  ADD CONSTRAINT `propiedad_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `propiedad_ibfk_2` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_propiedad` (`id`),
  ADD CONSTRAINT `propiedad_ibfk_3` FOREIGN KEY (`etapa_id`) REFERENCES `etapa_construccion` (`id`),
  ADD CONSTRAINT `propiedad_ibfk_4` FOREIGN KEY (`estado_id`) REFERENCES `estado_propiedad` (`id`);

--
-- Filtros para la tabla `registro_login`
--
ALTER TABLE `registro_login`
  ADD CONSTRAINT `registro_login_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `registro_recuperacion_contrasena`
--
ALTER TABLE `registro_recuperacion_contrasena`
  ADD CONSTRAINT `registro_recuperacion_contrasena_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `responsable_disponibilidad`
--
ALTER TABLE `responsable_disponibilidad`
  ADD CONSTRAINT `responsable_disponibilidad_ibfk_1` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`);

--
-- Filtros para la tabla `respuesta_ctg`
--
ALTER TABLE `respuesta_ctg`
  ADD CONSTRAINT `fk_resp_ctg` FOREIGN KEY (`ctg_id`) REFERENCES `ctg` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_resp_resp` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_resp_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`),
  ADD CONSTRAINT `fk_resp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuesta_pqr`
--
ALTER TABLE `respuesta_pqr`
  ADD CONSTRAINT `fk_respuesta_pqr` FOREIGN KEY (`pqr_id`) REFERENCES `pqr` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_respuesta_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_respuesta_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_menu`
--
ALTER TABLE `rol_menu`
  ADD CONSTRAINT `rol_menu_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`),
  ADD CONSTRAINT `rol_menu_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);

--
-- Filtros para la tabla `subtipo_ctg`
--
ALTER TABLE `subtipo_ctg`
  ADD CONSTRAINT `fk_subtipo_tipo_ctg` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_ctg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_subtipo_urgencia_ctg` FOREIGN KEY (`urgencia_id`) REFERENCES `urgencia_ctg` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
