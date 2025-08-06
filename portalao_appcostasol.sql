-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-08-2025 a las 15:47:42
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
  `fecha_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_ctg`
--

CREATE TABLE `estado_ctg` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_pqr`
--

CREATE TABLE `estado_pqr` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `usuario_actualizo` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `fecha_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

--
-- Volcado de datos para la tabla `progreso_construccion`
(1922, NULL, 4, NULL, NULL, 'https://constv-my.sharepoint.com/personal/aburgos_thaliavictoria_com_ec/_layouts/15/download.aspx?UniqueId=38c6ba0c-68ca-4170-9399-cc090c4d630e&Translate=false&tempauth=v1.eyJzaXRlaWQiOiI5MTBjOTliOC0yYzI4LTRmMDgtYjkwNS02YTZjOTRjNWEwODMiLCJhcHBfZGlzcGxheW5hbWUiOiJBcHBDb3N0YVNvbFNBQyIsIm5hbWVpZCI6IjY1MjhmMzcyLWVkMWItNDBlZi05NTBhLWE5NTAyMTljNWY5ZUBiOTYxOGFjNi0yNjQ4LTQxZWQtYmI0Zi0wM2JjZDk0YTc0OTMiLCJhdWQiOiIwMDAwMDAwMy0wMDAwLTBmZjEtY2UwMC0wMDAwMDAwMDAwMDAvY29uc3R2LW15LnNoYXJlcG9pbnQuY29tQGI5NjE4YWM2LTI2NDgtNDFlZC1iYjRmLTAzYmNkOTRhNzQ5MyIsImV4cCI6IjE3NTA3MTY0MjMifQ.CkAKDGVudHJhX2NsYWltcxIwQ01lQTU4SUdFQUFhRm5RMGFsOHpTVXBTVFZVMkxVSkdhbUpFU1daSlFVRXFBQT09CjIKCmFjdG9yYXBwaWQSJDAwMDAwMDAzLTAwMDAtMDAwMC1jMDAwLTAwMDAwMDAwMDAwMAoKCgRzbmlkEgI2NBILCIrrqPb9qJk-EAUaCzQwLjEyNi44LjQxKixXU1Z3QU5xVnN1aW1MMTBRclFraTBsQ052aTliOFBRMCthVFNYV2dkNlZzPTCfATgBQhChqwyoVqAAkD_n6oA-MVe8ShBoYXNoZWRwcm9vZnRva2VuegExugEbYWxsc2l0ZXMucmVhZCBhbGxmaWxlcy5yZWFkyAEB.-_4HPL2SMb03X3PzfJswN2no6BRqTh8bd_-05NIQ1GU&ApiVersion=2.0', 'https://constv-my.sharepoint.com/personal/aburgos_thaliavictoria_com_ec/Documents/-%20FEDATARIO/FOTOS%20DE%20INSPECCIONES/CATANIA/Visita%204-HABITABILIDAD/7119/7119-03/2.jpg', '017SGVTVYMXLDDRSTIOBAZHGOMBEGE2YYO', '2025-06-09 13:29:53', 'Alexander  Burgos', '2025-06-14 20:22:09', '7119', '03', 'Alexander  Burgos', 1, '- FEDATARIO/FOTOS DE INSPECCIONES/CATANIA/Visita 4-HABITABILIDAD/7119/7119-03/2.jpg', '2025-06-23 15:07:03');

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
(3, 7, 2, 4, 4, '2025-07-01', '2030-07-10', '2031-07-08', '2025-07-23 08:57:32', 3, '7119', '03', '033'),
(4, 7, 2, 3, 4, '2025-07-01', '2030-07-01', '2031-07-01', '2025-07-23 09:08:06', 3, '7117', '33', '33'),
(5, 8, 2, 4, 4, '2025-07-01', '2030-07-10', '2031-07-08', '2025-07-24 08:57:32', 3, '7119', '03', '033'),
(9, 9, 2, 4, 4, '2025-07-01', '2030-07-10', '2031-07-08', '2025-07-28 08:57:32', 3, '7119', '03', '033'),
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
  `fecha_respuesta` datetime NOT NULL DEFAULT current_timestamp()
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
  `fecha_respuesta` datetime NOT NULL DEFAULT current_timestamp()
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_menu`
--

CREATE TABLE `rol_menu` (
  `rol_id` tinyint(3) UNSIGNED NOT NULL,
  `menu_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_pqr`
--

CREATE TABLE `tipo_pqr` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_propiedad`
--

CREATE TABLE `tipo_propiedad` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `urgencia_ctg`
--

CREATE TABLE `urgencia_ctg` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
(8, 1, 'Daniel', 'Alarcon', NULL, NULL, 'danielalarcon@gmail.com', '$2y$10$KxKCaEiyWPjABgwuzODM.eWQlVCmLGgqISjp.5dkCDt5UYowRSGHC', 1, 'https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_640.png', '2025-07-24 16:06:19', 'syf6vSnOHmdEH2vQpm62mXx3LkSk6Y5t', 'b7c880b7-10a3-4d48-a66e-749b20ffbf92', '2025-08-06 13:46:11'),
(9, 1, 'Felipe', 'Pilligua', NULL, NULL, 'fepilligua@gmail.com', '$2y$10$reWiHb52f7EPZNo98Q3kz.H0Df6eFFB6FJrgll3nGywKhCmgl/hvC', 1, 'https://cdn-icons-png.flaticon.com/512/219/219983.png', '2025-07-28 08:42:37', 'usS/ZLM+PnC0w2UOtgK4kCPskmwnSmqF', NULL, '2025-08-05 17:08:45'),
(10, 3, 'Jose', 'Tenesaca', NULL, NULL, 'josesaca@gmail.com', '$2y$10$PDi0FrSYYTkVDklx9bsAhOrY3BvFxJmqlzrNPP392Yj86w5k.aXha', 0, NULL, '2025-08-05 13:25:22', 'iCHj2yX2GP5NG9lt7+NCkN/wPFr7tH2D', NULL, '2025-08-05 18:32:06'),
(11, 1, 'Martin', 'Mera', NULL, NULL, 'martin@gmial.com', '$2y$10$UtKsBWDz.Hdf5DPHSlmV5etztt5mez967KwQccui431EB5woBL4au', 0, 'https://cdn-icons-png.flaticon.com/512/219/219983.png', '2025-08-06 08:16:43', 'VjNzggmwNLyOVhlKi1HTjJSXyVziQkun', '4b2ad379-5372-40fe-9532-b46bb026fa7a', '2025-08-06 13:45:57');

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
  ADD KEY `urgencia_id` (`urgencia_id`),
  ADD KEY `fk_pqr_propiedad` (`id_propiedad`),
  ADD KEY `idx_pqr_tipo` (`tipo_id`),
  ADD KEY `idx_pqr_subtipo` (`subtipo_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ctg`
--
ALTER TABLE `ctg`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_ctg`
--
ALTER TABLE `estado_ctg`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_pqr`
--
ALTER TABLE `estado_pqr`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `noticia`
--
ALTER TABLE `noticia`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pqr`
--
ALTER TABLE `pqr`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `progreso_construccion`
--
ALTER TABLE `progreso_construccion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1923;

--
-- AUTO_INCREMENT de la tabla `propiedad`
--
ALTER TABLE `propiedad`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `proposito_agendamiento`
--
ALTER TABLE `proposito_agendamiento`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `responsable_disponibilidad`
--
ALTER TABLE `responsable_disponibilidad`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subtipo_ctg`
--
ALTER TABLE `subtipo_ctg`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_ctg`
--
ALTER TABLE `tipo_ctg`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_pqr`
--
ALTER TABLE `tipo_pqr`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_propiedad`
--
ALTER TABLE `tipo_propiedad`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `urbanizacion`
--
ALTER TABLE `urbanizacion`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `urgencia_ctg`
--
ALTER TABLE `urgencia_ctg`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
