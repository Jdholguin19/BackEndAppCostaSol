CREATE DATABASE  IF NOT EXISTS `portalao_appCostaSol` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `portalao_appCostaSol`;
-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: box5500.bluehost.com    Database: portalao_appCostaSol
-- ------------------------------------------------------
-- Server version	5.7.44-48

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
-- Table structure for table `agendamiento_visitas`
--

DROP TABLE IF EXISTS `agendamiento_visitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agendamiento_visitas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned DEFAULT NULL,
  `responsable_id` bigint(20) unsigned NOT NULL,
  `proposito_id` tinyint(3) unsigned NOT NULL,
  `fecha_ingreso` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_reunion` date NOT NULL,
  `hora_reunion` time NOT NULL,
  `estado` enum('PROGRAMADO','CONFIRMADO','CANCELADO') COLLATE utf8_unicode_ci DEFAULT 'PROGRAMADO',
  `resultado` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `asistencia` enum('NO_REGISTRADO','ASISTIO','NO_ASISTIO') COLLATE utf8_unicode_ci DEFAULT 'NO_REGISTRADO' COMMENT 'Registro de asistencia del cliente a la cita',
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `id_propiedad` bigint(20) unsigned DEFAULT NULL,
  `observaciones` text COLLATE utf8_unicode_ci COMMENT 'Objetivo o descripcion breve de la visita proporcionada por el usario',
  `duracion_minutos` smallint(6) DEFAULT NULL COMMENT 'Duración específica de la cita en minutos',
  `leido` tinyint(1) NOT NULL DEFAULT '0',
  `outlook_event_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ID único del evento en Microsoft Outlook',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_responsable_hora` (`responsable_id`,`fecha_reunion`,`hora_reunion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `fk_agendamiento_propiedad` (`id_propiedad`),
  KEY `fk_visita_proposito` (`proposito_id`),
  KEY `idx_asistencia` (`asistencia`),
  CONSTRAINT `agendamiento_visitas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  CONSTRAINT `agendamiento_visitas_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`),
  CONSTRAINT `fk_agendamiento_propiedad` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedad` (`id`),
  CONSTRAINT `fk_visita_proposito` FOREIGN KEY (`proposito_id`) REFERENCES `proposito_agendamiento` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1334 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_type` enum('usuario','responsable','sistema') COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Ej: LOGIN_SUCCESS, CREATE_CITA, UPDATE_CTG_STATUS',
  `target_resource` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ej: cita, ctg, pqr, usuario',
  `target_id` bigint(20) unsigned DEFAULT NULL COMMENT 'El ID del recurso afectado',
  `details` json DEFAULT NULL COMMENT 'Un objeto JSON con detalles adicionales, como valores antiguos y nuevos',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`,`user_type`),
  KEY `idx_action` (`action`),
  KEY `idx_resource` (`target_resource`,`target_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1035 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
-- Table structure for table `ctg`
--

DROP TABLE IF EXISTS `ctg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ctg` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero_solicitud` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_propiedad` bigint(20) unsigned DEFAULT NULL,
  `tipo_id` tinyint(3) unsigned DEFAULT NULL,
  `subtipo_id` tinyint(3) unsigned DEFAULT NULL,
  `estado_id` tinyint(3) unsigned NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `urgencia_id` tinyint(3) unsigned DEFAULT NULL,
  `resolucion` text COLLATE utf8_unicode_ci,
  `url_problema` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_solucion` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `responsable_id` bigint(20) unsigned DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_compromiso` datetime DEFAULT NULL,
  `fecha_resolucion` datetime DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  `observaciones` varchar(700) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pqr_numero` (`numero_solicitud`),
  KEY `id_usuario` (`id_usuario`),
  KEY `responsable_id` (`responsable_id`),
  KEY `estado_id` (`estado_id`),
  KEY `urgencia_id` (`urgencia_id`),
  KEY `fk_pqr_propiedad` (`id_propiedad`),
  KEY `idx_pqr_tipo` (`tipo_id`),
  KEY `idx_pqr_subtipo` (`subtipo_id`),
  CONSTRAINT `ctg_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  CONSTRAINT `ctg_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`),
  CONSTRAINT `ctg_ibfk_5` FOREIGN KEY (`estado_id`) REFERENCES `estado_ctg` (`id`),
  CONSTRAINT `ctg_ibfk_6` FOREIGN KEY (`urgencia_id`) REFERENCES `urgencia_ctg` (`id`),
  CONSTRAINT `fk_ctg_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado_ctg` (`id`),
  CONSTRAINT `fk_ctg_subtipo` FOREIGN KEY (`subtipo_id`) REFERENCES `subtipo_ctg` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ctg_tipo` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_ctg` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ctg_urgencia` FOREIGN KEY (`urgencia_id`) REFERENCES `urgencia_ctg` (`id`),
  CONSTRAINT `fk_pqr_propiedad` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedad` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `estado_ctg`
--

DROP TABLE IF EXISTS `estado_ctg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estado_ctg` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `estado_pqr`
--

DROP TABLE IF EXISTS `estado_pqr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estado_pqr` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
-- Table structure for table `garantias`
--

DROP TABLE IF EXISTS `garantias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `garantias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `tiempo_garantia_meses` smallint(5) unsigned NOT NULL COMMENT 'Tiempo de garantía en meses',
  `tipo_propiedad_id` tinyint(3) unsigned DEFAULT NULL COMMENT 'FK a tipo_propiedad.id, NULL = aplica a todos los tipos',
  `estado` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = inactivo, 1 = activo',
  `orden` smallint(5) unsigned DEFAULT '0' COMMENT 'Orden de visualización',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tipo_propiedad` (`tipo_propiedad_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_orden` (`orden`),
  CONSTRAINT `fk_garantias_tipo_propiedad` FOREIGN KEY (`tipo_propiedad_id`) REFERENCES `tipo_propiedad` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kit_color_opcion`
--

DROP TABLE IF EXISTS `kit_color_opcion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kit_color_opcion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acabado_kit_id` int(10) unsigned NOT NULL,
  `nombre_opcion` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Ej: Cocina Estándar Clara',
  `color_nombre` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Ej: Claro, Oscuro',
  `url_imagen_opcion` varchar(2000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'URL de la imagen para el Paso 2',
  PRIMARY KEY (`id`),
  KEY `fk_opcion_kit_color` (`acabado_kit_id`),
  CONSTRAINT `fk_opcion_kit_color` FOREIGN KEY (`acabado_kit_id`) REFERENCES `acabado_kit` (`id`) ON DELETE CASCADE
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `noticia`
--

DROP TABLE IF EXISTS `noticia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `noticia` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(160) COLLATE utf8_unicode_ci NOT NULL,
  `resumen` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contenido` text COLLATE utf8_unicode_ci,
  `url_imagen` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_noticia` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `orden` smallint(5) unsigned DEFAULT '0',
  `estado` tinyint(1) DEFAULT '1',
  `fecha_publicacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `autor_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `autor_id` (`autor_id`),
  CONSTRAINT `noticia_ibfk_1` FOREIGN KEY (`autor_id`) REFERENCES `usuario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
-- Table structure for table `pqr`
--

DROP TABLE IF EXISTS `pqr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pqr` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero_solicitud` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_propiedad` bigint(20) unsigned DEFAULT NULL,
  `tipo_id` tinyint(3) unsigned DEFAULT NULL,
  `subtipo_id` tinyint(3) unsigned DEFAULT NULL,
  `estado_id` tinyint(3) unsigned NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `urgencia_id` tinyint(3) unsigned DEFAULT NULL,
  `resolucion` text COLLATE utf8_unicode_ci,
  `url_problema` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_solucion` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `responsable_id` bigint(20) unsigned DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_compromiso` datetime DEFAULT NULL,
  `fecha_resolucion` datetime DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  `observaciones` varchar(700) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pqr_numero` (`numero_solicitud`),
  KEY `id_usuario` (`id_usuario`),
  KEY `responsable_id` (`responsable_id`),
  KEY `estado_id` (`estado_id`),
  KEY `fk_pqr_propiedad` (`id_propiedad`),
  KEY `idx_pqr_tipo` (`tipo_id`),
  KEY `fk_pqr_subtipo` (`subtipo_id`),
  KEY `fk_pqr_urgencia` (`urgencia_id`),
  CONSTRAINT `fk_pqr_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado_pqr` (`id`),
  CONSTRAINT `fk_pqr_propiedad_ref` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedad` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pqr_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`),
  CONSTRAINT `fk_pqr_subtipo` FOREIGN KEY (`subtipo_id`) REFERENCES `subtipo_ctg` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pqr_tipo` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_pqr` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pqr_urgencia` FOREIGN KEY (`urgencia_id`) REFERENCES `urgencia_ctg` (`id`),
  CONSTRAINT `fk_pqr_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=497 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `proposito_agendamiento`
--

DROP TABLE IF EXISTS `proposito_agendamiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proposito_agendamiento` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `proposito` varchar(100) NOT NULL,
  `url_icono` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_ingreso` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registro_login`
--

DROP TABLE IF EXISTS `registro_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registro_login` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned DEFAULT NULL,
  `id_responsable` bigint(20) unsigned DEFAULT NULL,
  `estado_login` enum('EXITO','FALLIDO') COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_responsable` (`id_responsable`),
  CONSTRAINT `registro_login_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=971 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registro_recuperacion_contrasena`
--

DROP TABLE IF EXISTS `registro_recuperacion_contrasena`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registro_recuperacion_contrasena` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `codigo` char(6) COLLATE utf8_unicode_ci NOT NULL,
  `estado_solicitud` enum('PENDIENTE','USADO','VENCIDO') COLLATE utf8_unicode_ci DEFAULT 'PENDIENTE',
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_expira` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `registro_recuperacion_contrasena_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `responsable`
--

DROP TABLE IF EXISTS `responsable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `responsable` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `correo` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `contrasena_hash` char(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_foto_perfil` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `fecha_ingreso` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `outlook_access_token` text COLLATE utf8_unicode_ci,
  `outlook_refresh_token` text COLLATE utf8_unicode_ci,
  `outlook_token_expires_at` datetime DEFAULT NULL,
  `outlook_subscription_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `outlook_client_state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `outlook_calendar_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `onesignal_player_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_actualizacion_player_id` timestamp NULL DEFAULT NULL COMMENT 'Fecha de última actualización del OneSignal Player ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  KEY `idx_responsable_onesignal_player_id` (`onesignal_player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `responsable_disponibilidad`
--

DROP TABLE IF EXISTS `responsable_disponibilidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `responsable_disponibilidad` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `responsable_id` bigint(20) unsigned NOT NULL,
  `dia_semana` tinyint(3) unsigned NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `fecha_vigencia_desde` date DEFAULT NULL,
  `fecha_vigencia_hasta` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `intervalo_minutos` smallint(6) NOT NULL DEFAULT '30',
  PRIMARY KEY (`id`),
  KEY `idx_disponibilidad_lookup` (`responsable_id`,`dia_semana`,`fecha_vigencia_desde`,`fecha_vigencia_hasta`,`activo`),
  CONSTRAINT `responsable_disponibilidad_ibfk_1` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `respuesta_ctg`
--

DROP TABLE IF EXISTS `respuesta_ctg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `respuesta_ctg` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ctg_id` bigint(20) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `responsable_id` bigint(20) unsigned DEFAULT NULL,
  `mensaje` text COLLATE utf8_unicode_ci NOT NULL,
  `url_adjunto` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_respuesta` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leido` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pqr_id` (`ctg_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fk_resp_resp` (`responsable_id`),
  CONSTRAINT `fk_resp_ctg` FOREIGN KEY (`ctg_id`) REFERENCES `ctg` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_resp_resp` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_resp_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`),
  CONSTRAINT `fk_resp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `respuesta_pqr`
--

DROP TABLE IF EXISTS `respuesta_pqr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `respuesta_pqr` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pqr_id` bigint(20) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `responsable_id` bigint(20) unsigned DEFAULT NULL,
  `mensaje` text COLLATE utf8_unicode_ci NOT NULL,
  `url_adjunto` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_respuesta` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leido` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pqr_id` (`pqr_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fk_resp_resp` (`responsable_id`),
  CONSTRAINT `fk_respuesta_pqr` FOREIGN KEY (`pqr_id`) REFERENCES `pqr` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_respuesta_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_respuesta_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rol_menu`
--

DROP TABLE IF EXISTS `rol_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol_menu` (
  `rol_id` tinyint(3) unsigned NOT NULL,
  `menu_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`rol_id`,`menu_id`),
  KEY `menu_id` (`menu_id`),
  CONSTRAINT `rol_menu_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`),
  CONSTRAINT `rol_menu_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipo_ctg`
--

DROP TABLE IF EXISTS `tipo_ctg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_ctg` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `tiempo_garantia_min` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tiempo_garantia_max` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipo_pqr`
--

DROP TABLE IF EXISTS `tipo_pqr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_pqr` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
-- Table structure for table `urgencia_ctg`
--

DROP TABLE IF EXISTS `urgencia_ctg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `urgencia_ctg` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=809 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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

-- Dump completed on 2025-10-21 12:05:50
