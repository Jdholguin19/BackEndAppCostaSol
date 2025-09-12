-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-09-2025 a las 22:25:15
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
  `owners` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gantt_tasks`
--

INSERT INTO `gantt_tasks` (`id`, `project_id`, `text`, `start_date`, `duration`, `progress`, `parent`, `sortorder`, `open`, `owners`) VALUES
(4, 1, 'Fase 2: Desarrollo', '0000-00-00', 2567, 0, 0, 40, 1, ''),
(6, 1, 'Implementar Módulo B', '0000-00-00', 3659, 0, 4, 60, 1, ''),
(8, 2, 'New taskq', '0000-00-00', 1, 0, 0, 10, 1, '2'),
(9, 2, 'New task', '0000-00-00', 1, 0, 0, 10, 1, ''),
(10, 2, 'Nueva Tarea', '0000-00-00', 1, 0, 8, 10, 1, '8'),
(11, 2, 'Nueva Tarea', '0000-00-00', 1, 0, 10, 10, 1, '4'),
(12, 2, 'Nueva Tarea', '0000-00-00', 1, 0, 11, 10, 1, ''),
(13, 2, 'Nueva Tarea', '0000-00-00', 1, 0, 0, 10, 1, '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `gantt_tasks`
--
ALTER TABLE `gantt_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `gantt_tasks`
--
ALTER TABLE `gantt_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `gantt_tasks`
--
ALTER TABLE `gantt_tasks`
  ADD CONSTRAINT `fk_gantt_tasks_project_id` FOREIGN KEY (`project_id`) REFERENCES `gantt_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
