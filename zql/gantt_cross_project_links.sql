-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-09-2025 a las 22:24:44
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
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `gantt_cross_project_links`
--
ALTER TABLE `gantt_cross_project_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
