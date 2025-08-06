-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-08-2025 a las 19:09:43
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
(2, 'Aluminio y Vidrio', '0,3', '1'),
(3, 'Cerramiento', '1', '1'),
(4, 'Cisterna', '0,3', '1'),
(5, 'Instalaciones de Climatización', '0,6', '0,6'),
(6, 'Instalaciones de Voz y Datos', '1', '1'),
(7, 'Instalaciones Eléctricas', '0,3', '1'),
(8, 'Instalaciones Sanitarias', '0,3', '0,6'),
(9, 'Mobiliario cocina-baños-closets', '0,3', '0,6'),
(10, 'Paredes', '1', '1'),
(11, 'Pasamanos', '0,3', '0,3'),
(12, 'Puertas', '0,3', '0,3'),
(13, 'Recubrimientos', '0,3', '0,6'),
(14, 'Tumbado', '1', '1'),
(15, 'Otros', '0,3', '0,3');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tipo_ctg`
--
ALTER TABLE `tipo_ctg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tipo_ctg`
--
ALTER TABLE `tipo_ctg`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
