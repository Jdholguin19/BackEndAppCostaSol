-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-09-2025 a las 17:10:17
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
-- Estructura de tabla para la tabla `gantt_cross_project_links`
--

CREATE TABLE `gantt_cross_project_links` (
  `id` int(11) NOT NULL,
  `source_task_id` int(11) NOT NULL,
  `source_project_id` int(11) NOT NULL,
  `target_task_id` int(11) NOT NULL,
  `target_project_id` int(11) NOT NULL,
  `type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gantt_cross_project_links`
--

INSERT INTO `gantt_cross_project_links` (`id`, `source_task_id`, `source_project_id`, `target_task_id`, `target_project_id`, `type`) VALUES
(2, 8, 2, 6, 1, '3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gantt_links`
--

CREATE TABLE `gantt_links` (
  `id` int(11) NOT NULL,
  `source` int(11) NOT NULL,
  `target` int(11) NOT NULL,
  `type` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gantt_projects`
--

CREATE TABLE `gantt_projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gantt_projects`
--

INSERT INTO `gantt_projects` (`id`, `name`) VALUES
(1, 'Proyecto de Ejemplo'),
(2, 'Proyecto CostaSOl');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gantt_tasks`
--

CREATE TABLE `gantt_tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL DEFAULT 0,
  `text` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `duration` int(11) NOT NULL,
  `progress` float NOT NULL,
  `parent` int(11) NOT NULL,
  `sortorder` int(11) NOT NULL,
  `open` tinyint(1) NOT NULL DEFAULT 1,
  `owners` text DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#3498db'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gantt_tasks`
--

INSERT INTO `gantt_tasks` (`id`, `project_id`, `text`, `start_date`, `duration`, `progress`, `parent`, `sortorder`, `open`, `owners`, `color`) VALUES
(4, 1, 'Fase 2: Desarrollo', '1901-11-30', 11, 0, 0, 40, 1, '', '#3498db'),
(6, 1, 'Implementar Módulo B', '1901-11-30', 11, 0, 4, 60, 1, '', '#3498db'),
(8, 2, 'New taskq', '1901-11-30', 1, 0, 0, 10, 1, '2', '#76db33'),
(9, 2, 'New task', '0000-00-00', 1, 0, 0, 10, 1, '', '#3498db'),
(10, 2, 'Nueva Tarea', '0000-00-00', 1, 0, 8, 10, 1, '8', '#3498db'),
(11, 2, 'aaaa', '1901-11-30', 1, 0, 10, 10, 1, '8', '#3498db'),
(12, 2, 'Nueva Tarea', '0000-00-00', 1, 0, 11, 10, 1, '', '#3498db'),
(13, 2, 'Nueva Tarea', '0000-00-00', 1, 0, 0, 10, 1, '', '#3498db'),
(14, 2, '1111', '1901-12-02', 3, 0.471698, 10, 0, 0, '3', '#6fc38c');

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
(8, 2, 'Daniel', 'Alarcon', NULL, NULL, 'danielalarcon@gmail.com', '$2y$10$KxKCaEiyWPjABgwuzODM.eWQlVCmLGgqISjp.5dkCDt5UYowRSGHC', 1, 'https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_640.png', '2025-07-24 16:06:19', 'oecawXknyGFgZ1j3bpgYVEvmxlodq2EM', NULL, '2025-08-18 21:36:27'),
(9, 1, 'Felipe', 'Pilligua', NULL, NULL, 'fepilligua@gmail.com', '$2y$10$reWiHb52f7EPZNo98Q3kz.H0Df6eFFB6FJrgll3nGywKhCmgl/hvC', 1, 'https://cdn-icons-png.flaticon.com/512/219/219983.png', '2025-07-28 08:42:37', '72MCZVJgqKtpDn4N249/pPMBLTVQjz9I', '4b2ad379-5372-40fe-9532-b46bb026fa7a', '2025-08-19 15:10:50'),
(10, 1, 'Jose', 'Tenesaca', NULL, NULL, 'josesaca@gmail.com', '$2y$10$8LEm..7rJ3ii/7QWAivJOuHJMjh3lQ4n/qEaqTI66Wu3YPKLcpble', 0, NULL, '2025-08-05 13:25:22', 'iCHj2yX2GP5NG9lt7+NCkN/wPFr7tH2D', NULL, '2025-08-05 18:32:06'),
(11, 1, 'Martin', 'Mera', NULL, NULL, 'martin@gmial.com', '$2y$10$UtKsBWDz.Hdf5DPHSlmV5etztt5mez967KwQccui431EB5woBL4au', 0, 'https://cdn-icons-png.flaticon.com/512/219/219983.png', '2025-08-06 08:16:43', 'tDe030ZBD99SVoDhzEecbw4h/pEyY0v1', NULL, '2025-08-06 14:35:50'),
(12, 1, 'prueba', '1', NULL, NULL, 'prueba@prueba.com', '$2y$10$447WYbjzXhELxQuweQbcEuNgS4HiO.PxkyqBbm7zQ4XC.evK9FpoS', 0, NULL, '2025-08-13 12:37:40', 'mc5PPSjS365g9XGa8+xrmQWPqdUg8t8s', NULL, '2025-08-13 17:37:52'),
(13, 1, 'prueba', '2', NULL, NULL, 'prueba2@prueba.com', '$2y$10$K2tKUN8/SS/o8Nhr7wnG3uTkzy5FdI3H8.kze3wAxd5w7a6tA0OZO', 0, NULL, '2025-08-13 12:39:18', NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `gantt_cross_project_links`
--
ALTER TABLE `gantt_cross_project_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cross_link_source_task` (`source_task_id`),
  ADD KEY `fk_cross_link_target_task` (`target_task_id`),
  ADD KEY `fk_cross_link_source_project` (`source_project_id`),
  ADD KEY `fk_cross_link_target_project` (`target_project_id`);

--
-- Indices de la tabla `gantt_links`
--
ALTER TABLE `gantt_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gantt_links_source` (`source`),
  ADD KEY `fk_gantt_links_target` (`target`);

--
-- Indices de la tabla `gantt_projects`
--
ALTER TABLE `gantt_projects`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `gantt_tasks`
--
ALTER TABLE `gantt_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_id` (`project_id`);

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
-- AUTO_INCREMENT de la tabla `gantt_cross_project_links`
--
ALTER TABLE `gantt_cross_project_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `gantt_links`
--
ALTER TABLE `gantt_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `gantt_projects`
--
ALTER TABLE `gantt_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `gantt_tasks`
--
ALTER TABLE `gantt_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `gantt_cross_project_links`
--
ALTER TABLE `gantt_cross_project_links`
  ADD CONSTRAINT `fk_cross_link_source_project` FOREIGN KEY (`source_project_id`) REFERENCES `gantt_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cross_link_source_task` FOREIGN KEY (`source_task_id`) REFERENCES `gantt_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cross_link_target_project` FOREIGN KEY (`target_project_id`) REFERENCES `gantt_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cross_link_target_task` FOREIGN KEY (`target_task_id`) REFERENCES `gantt_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `gantt_links`
--
ALTER TABLE `gantt_links`
  ADD CONSTRAINT `fk_gantt_links_source` FOREIGN KEY (`source`) REFERENCES `gantt_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_gantt_links_target` FOREIGN KEY (`target`) REFERENCES `gantt_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `gantt_tasks`
--
ALTER TABLE `gantt_tasks`
  ADD CONSTRAINT `fk_gantt_tasks_project_id` FOREIGN KEY (`project_id`) REFERENCES `gantt_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
