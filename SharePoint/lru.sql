CREATE DATABASE  IF NOT EXISTS `portalao_appCostaSol` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `portalao_appCostaSol`;
-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: box5500.bluehost.com    Database: portalao_appCostaSol
-- ------------------------------------------------------
-- Server version	5.7.23-23

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acabado_detalle`
--

DROP TABLE IF EXISTS `acabado_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acabado_detalle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acabado_kit_id` int(10) unsigned NOT NULL,
  `componente_id` int(10) unsigned NOT NULL,
  `color` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Ej: Claro, Oscuro',
  `url_imagen` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_detalle_kit` (`acabado_kit_id`),
  KEY `fk_detalle_componente` (`componente_id`),
  CONSTRAINT `fk_detalle_componente` FOREIGN KEY (`componente_id`) REFERENCES `componente` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_kit` FOREIGN KEY (`acabado_kit_id`) REFERENCES `acabado_kit` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acabado_kit`
--

DROP TABLE IF EXISTS `acabado_kit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acabado_kit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Ej: Cocina Standar, Baño Principal',
  `descripcion` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_imagen_principal` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'URL de la imagen para el Paso 1',
  `costo` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Costo adicional del kit',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `componente`
--

DROP TABLE IF EXISTS `componente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `componente` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Ej: Mesón, Piso, Anaquel Superior',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `estado_propiedad`
--

DROP TABLE IF EXISTS `estado_propiedad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estado_propiedad` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etapa_construccion`
--

DROP TABLE IF EXISTS `etapa_construccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etapa_construccion` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `porcentaje` tinyint(4) NOT NULL,
  `descripcion` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `fecha_creada` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_icono` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `orden` tinyint(3) unsigned DEFAULT '0',
  `fecha_creado` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizado` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `usuario_actualizo` bigint(20) unsigned DEFAULT NULL,
  `menu_bar` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `usuario_actualizo` (`usuario_actualizo`),
  CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`usuario_actualizo`) REFERENCES `usuario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paquetes_adicionales`
--

DROP TABLE IF EXISTS `paquetes_adicionales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paquetes_adicionales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `precio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fotos` json DEFAULT NULL COMMENT 'Un array de URLs de imágenes. Ej: ["url1.jpg", "url2.jpg"]',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `progreso_construccion`
--

DROP TABLE IF EXISTS `progreso_construccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `progreso_construccion` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_propiedad` bigint(20) unsigned DEFAULT NULL,
  `id_etapa` smallint(5) unsigned NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `porcentaje` tinyint(4) DEFAULT NULL,
  `ruta_descarga_sharepoint` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ruta_visualizacion_sharepoint` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `drive_item_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_creado_sharepoint` datetime DEFAULT NULL,
  `usuario_creador` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_modificado_sharepoint` datetime DEFAULT NULL,
  `mz` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `villa` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usuario_modificado_sharepoint` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = activo, 0 = inactivo',
  `url_imagen` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_urbanizacion` tinyint(3) unsigned DEFAULT NULL COMMENT 'ID de la urbanización (FK a tabla urbanizacion)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `drive_item_id` (`drive_item_id`),
  KEY `id_propiedad` (`id_propiedad`),
  KEY `id_etapa` (`id_etapa`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_sharepoint` (`fecha_creado_sharepoint`),
  KEY `idx_progreso_urbanizacion` (`id_urbanizacion`),
  CONSTRAINT `fk_progreso_urbanizacion` FOREIGN KEY (`id_urbanizacion`) REFERENCES `urbanizacion` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `progreso_construccion_ibfk_1` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedad` (`id`),
  CONSTRAINT `progreso_construccion_ibfk_2` FOREIGN KEY (`id_etapa`) REFERENCES `etapa_construccion` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2233 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `propiedad`
--

DROP TABLE IF EXISTS `propiedad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `propiedad` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `tipo_id` tinyint(3) unsigned NOT NULL,
  `etapa_id` smallint(5) unsigned NOT NULL,
  `estado_id` tinyint(3) unsigned NOT NULL,
  `fecha_compra` date DEFAULT NULL,
  `fecha_hipotecario` date DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `fecha_insertado` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_urbanizacion` tinyint(3) unsigned NOT NULL,
  `manzana` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `solar` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `villa` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `acabado_kit_seleccionado_id` int(10) unsigned DEFAULT NULL,
  `acabado_color_seleccionado` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `tipo_id` (`tipo_id`),
  KEY `etapa_id` (`etapa_id`),
  KEY `estado_id` (`estado_id`),
  KEY `fk_propiedad_urbanizacion` (`id_urbanizacion`),
  KEY `fk_propiedad_kit_seleccionado` (`acabado_kit_seleccionado_id`),
  CONSTRAINT `fk_propiedad_kit_seleccionado` FOREIGN KEY (`acabado_kit_seleccionado_id`) REFERENCES `acabado_kit` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_propiedad_urbanizacion` FOREIGN KEY (`id_urbanizacion`) REFERENCES `urbanizacion` (`id`),
  CONSTRAINT `propiedad_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  CONSTRAINT `propiedad_ibfk_2` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_propiedad` (`id`),
  CONSTRAINT `propiedad_ibfk_3` FOREIGN KEY (`etapa_id`) REFERENCES `etapa_construccion` (`id`),
  CONSTRAINT `propiedad_ibfk_4` FOREIGN KEY (`estado_id`) REFERENCES `estado_propiedad` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `propiedad_paquetes_adicionales`
--

DROP TABLE IF EXISTS `propiedad_paquetes_adicionales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `propiedad_paquetes_adicionales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `propiedad_id` bigint(20) unsigned NOT NULL,
  `paquete_id` int(10) unsigned NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_propiedad_paquete` (`propiedad_id`,`paquete_id`),
  KEY `idx_propiedad` (`propiedad_id`),
  KEY `idx_paquete` (`paquete_id`),
  CONSTRAINT `fk_prop_paq_paquete` FOREIGN KEY (`paquete_id`) REFERENCES `paquetes_adicionales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prop_paq_propiedad` FOREIGN KEY (`propiedad_id`) REFERENCES `propiedad` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipo_propiedad`
--

DROP TABLE IF EXISTS `tipo_propiedad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_propiedad` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `urbanizacion`
--

DROP TABLE IF EXISTS `urbanizacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `urbanizacion` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `fecha_creada` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rol_id` tinyint(3) unsigned NOT NULL,
  `nombres` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `apellidos` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `cedula` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `contrasena_hash` char(60) COLLATE utf8_unicode_ci NOT NULL,
  `numero_propiedades` smallint(5) unsigned DEFAULT '0',
  `url_foto_perfil` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_insertado` datetime DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `onesignal_player_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_actualizacion_player_id` timestamp NULL DEFAULT NULL COMMENT 'Fecha de última actualización del OneSignal Player ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `uk_usuario_cedula` (`cedula`),
  KEY `rol_id` (`rol_id`),
  KEY `idx_usuario_onesignal_player_id` (`onesignal_player_id`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=354 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'portalao_appCostaSol'
--

--
-- Dumping routines for database 'portalao_appCostaSol'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-25  9:59:44
