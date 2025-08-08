-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-08-2025 a las 18:10:48
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
(1, 'SAC00001', 9, NULL, 2, NULL, 2, 'Faltan mas arboles', NULL, NULL, NULL, NULL, 2, '2025-08-01 11:11:00', '2025-08-06 11:11:00', NULL, '2025-08-01 12:14:47', NULL),
(2, 'SAC00002', 8, 5, 3, NULL, 1, 'Cambien el piso', NULL, NULL, NULL, NULL, 1, '2025-08-07 08:39:25', '2025-08-12 08:39:25', NULL, NULL, NULL),
(3, 'SAC00003', 8, 5, 2, NULL, 2, 'Cambien la pared', NULL, NULL, NULL, NULL, 2, '2025-08-07 08:42:31', '2025-08-12 08:42:31', NULL, '2025-08-07 16:39:38', NULL);

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
(1, 'Ana María Felix', 'coordinadorsac@thaliavictoria.com.ec', '$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa', 'https://app.costasol.com.ec/ImagenesPQR_problema/logCostaSol.jpg', 'SAC', 1, '2025-06-24 11:03:35', '2025-08-07 15:19:00', 'WjZToP+fFhe09kUBPHql1J+bPLVAEYqG', NULL, '2025-08-07 20:14:22'),
(2, 'Carla Oquendo', 'servicioalcliente@thaliavictoria.com.ec', '$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa', 'https://static.wixstatic.com/media/b80279_33a586f04740464cae96a3a6205d2c19~mv2.png', 'SAC', 1, '2025-06-24 11:07:21', '2025-08-08 10:49:36', 'UTgWAPp8NU9DQjPlCeQJiJNzmL7JS7kV', '4b2ad379-5372-40fe-9532-b46bb026fa7a', '2025-08-08 15:49:36');

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
(8, 2, 'Daniel', 'Alarcon', NULL, NULL, 'danielalarcon@gmail.com', '$2y$10$KxKCaEiyWPjABgwuzODM.eWQlVCmLGgqISjp.5dkCDt5UYowRSGHC', 1, 'https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_640.png', '2025-07-24 16:06:19', 'yqnU6HSGMzo6mC9YnhfjzseIej5lQq7o', 'b7c880b7-10a3-4d48-a66e-749b20ffbf92', '2025-08-08 15:49:20'),
(9, 1, 'Felipe', 'Pilligua', NULL, NULL, 'fepilligua@gmail.com', '$2y$10$reWiHb52f7EPZNo98Q3kz.H0Df6eFFB6FJrgll3nGywKhCmgl/hvC', 1, 'https://cdn-icons-png.flaticon.com/512/219/219983.png', '2025-07-28 08:42:37', 'aEIXljEwXRKUMVhprWqKgNt+78FKcQfX', '4b2ad379-5372-40fe-9532-b46bb026fa7a', '2025-08-07 13:15:08'),
(10, 1, 'Jose', 'Tenesaca', NULL, NULL, 'josesaca@gmail.com', '$2y$10$8LEm..7rJ3ii/7QWAivJOuHJMjh3lQ4n/qEaqTI66Wu3YPKLcpble', 0, NULL, '2025-08-05 13:25:22', 'iCHj2yX2GP5NG9lt7+NCkN/wPFr7tH2D', NULL, '2025-08-05 18:32:06'),
(11, 1, 'Martin', 'Mera', NULL, NULL, 'martin@gmial.com', '$2y$10$UtKsBWDz.Hdf5DPHSlmV5etztt5mez967KwQccui431EB5woBL4au', 0, 'https://cdn-icons-png.flaticon.com/512/219/219983.png', '2025-08-06 08:16:43', 'J8c0MuHCcDeG7rNQmhTVLyL4Lf6XZJb4', NULL, '2025-08-06 14:35:50');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `estado_pqr`
--
ALTER TABLE `estado_pqr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

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
  ADD KEY `idx_pqr_tipo` (`tipo_id`);

--
-- Indices de la tabla `responsable`
--
ALTER TABLE `responsable`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idx_responsable_onesignal_player_id` (`onesignal_player_id`);

--
-- Indices de la tabla `respuesta_pqr`
--
ALTER TABLE `respuesta_pqr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pqr_id` (`pqr_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_resp_resp` (`responsable_id`);

--
-- Indices de la tabla `tipo_pqr`
--
ALTER TABLE `tipo_pqr`
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
-- AUTO_INCREMENT de la tabla `estado_pqr`
--
ALTER TABLE `estado_pqr`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pqr`
--
ALTER TABLE `pqr`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `responsable`
--
ALTER TABLE `responsable`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `respuesta_pqr`
--
ALTER TABLE `respuesta_pqr`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_pqr`
--
ALTER TABLE `tipo_pqr`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

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
-- Filtros para la tabla `respuesta_pqr`
--
ALTER TABLE `respuesta_pqr`
  ADD CONSTRAINT `fk_respuesta_pqr` FOREIGN KEY (`pqr_id`) REFERENCES `pqr` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_respuesta_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_respuesta_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
