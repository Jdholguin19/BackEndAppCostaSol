-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-09-2025 a las 21:14:19
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
-- Base de datos: `portalao_bdu_kissflow`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kissflow_emision_pagos`
--

CREATE TABLE `kissflow_emision_pagos` (
  `id` int(11) NOT NULL,
  `kissflow_item_id` varchar(50) NOT NULL,
  `kissflow_activity_id` varchar(50) DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `_created_at` datetime DEFAULT NULL,
  `_completed_at` datetime DEFAULT NULL,
  `_status` varchar(100) DEFAULT NULL,
  `_created_by_id` varchar(50) DEFAULT NULL,
  `_created_by_name` varchar(255) DEFAULT NULL,
  `_modified_by_id` varchar(50) DEFAULT NULL,
  `_modified_by_name` varchar(255) DEFAULT NULL,
  `request_number` int(11) DEFAULT NULL,
  `Proveedor` varchar(255) DEFAULT NULL,
  `Monto` decimal(12,2) DEFAULT NULL,
  `Fecha_de_Factura` date DEFAULT NULL,
  `Fecha_de_Pago` date DEFAULT NULL,
  `Factura_files` text DEFAULT NULL,
  `Numero_de_Factura` varchar(255) DEFAULT NULL,
  `Orden_de_Pago_files` text DEFAULT NULL,
  `Numero_de_ChequeTransaccion` varchar(255) DEFAULT NULL,
  `BancoCuenta` varchar(255) DEFAULT NULL,
  `Motivo` text DEFAULT NULL,
  `Por_que_se_necesita` text DEFAULT NULL,
  `Cheque_ya_firmado` tinyint(1) DEFAULT NULL,
  `Notifico_al_proveedor` tinyint(1) DEFAULT NULL,
  `Valor_de_Pago` decimal(12,2) DEFAULT NULL,
  `Desea_notificacion_automatica` tinyint(1) DEFAULT NULL,
  `Valor_Neto` decimal(12,2) DEFAULT NULL,
  `Tipo_de_Pago` varchar(255) DEFAULT NULL,
  `Cheque_o_Transferencia` varchar(255) DEFAULT NULL,
  `Documentos_Relevantes_1_files` text DEFAULT NULL,
  `Documentos_Relevantes_2_files` text DEFAULT NULL,
  `Solicita` varchar(255) DEFAULT NULL,
  `Aprueba` varchar(255) DEFAULT NULL,
  `Necesita_Factura` tinyint(1) DEFAULT NULL,
  `viene_de` varchar(255) DEFAULT NULL,
  `proceso_actual` varchar(255) DEFAULT NULL,
  `Ordenes_de_Transferencias_cargadas` tinyint(1) DEFAULT NULL,
  `UDN_id` varchar(50) DEFAULT NULL,
  `UDN_Name` varchar(255) DEFAULT NULL,
  `UDN_Descripcion` varchar(255) DEFAULT NULL,
  `ETAPA_id` varchar(50) DEFAULT NULL,
  `ETAPA_Name` varchar(255) DEFAULT NULL,
  `ETAPA_Descripcion` varchar(255) DEFAULT NULL,
  `AUXILIAR_id` varchar(50) DEFAULT NULL,
  `AUXILIAR_Name` varchar(255) DEFAULT NULL,
  `AUXILIAR_Descripcion` varchar(255) DEFAULT NULL,
  `CRF_id` varchar(50) DEFAULT NULL,
  `CRF_Name` varchar(255) DEFAULT NULL,
  `CRF_Descripcion` varchar(255) DEFAULT NULL,
  `Proyecto_id` varchar(50) DEFAULT NULL,
  `Proyecto_Name` varchar(255) DEFAULT NULL,
  `Proyecto_Descripcion` varchar(255) DEFAULT NULL,
  `RazonSocial_NombreComercial` varchar(255) DEFAULT NULL,
  `RazonSocial_RazonSocial` varchar(255) DEFAULT NULL,
  `RazonSocial_NumeroCuenta` varchar(100) DEFAULT NULL,
  `RazonSocial_RUC` varchar(20) DEFAULT NULL,
  `RazonSocial_Banco` varchar(255) DEFAULT NULL,
  `RazonSocial_TipoCuenta` varchar(100) DEFAULT NULL,
  `_modified_at` datetime DEFAULT NULL,
  `fecha_sincronizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `kissflow_emision_pagos`
--
ALTER TABLE `kissflow_emision_pagos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kissflow_item_id` (`kissflow_item_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `kissflow_emision_pagos`
--
ALTER TABLE `kissflow_emision_pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
