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
  PRIMARY KEY (`id`),
  KEY `fk_detalle_kit` (`acabado_kit_id`),
  KEY `fk_detalle_componente` (`componente_id`),
  CONSTRAINT `fk_detalle_componente` FOREIGN KEY (`componente_id`) REFERENCES `componente` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_kit` FOREIGN KEY (`acabado_kit_id`) REFERENCES `acabado_kit` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acabado_detalle`
--

LOCK TABLES `acabado_detalle` WRITE;
/*!40000 ALTER TABLE `acabado_detalle` DISABLE KEYS */;
INSERT INTO `acabado_detalle` VALUES (1,2,1,'Claro','https://placehold.co/600x400/E1F5FE/37474F?text=Meson+Full+Claro'),(2,2,2,'Claro','https://placehold.co/600x400/E1F5FE/37474F?text=Anaquel+Sup.+Full+Claro'),(3,2,3,'Claro','https://placehold.co/600x400/E1F5FE/37474F?text=Anaquel+Inf.+Full+Claro'),(4,2,1,'Oscuro','https://placehold.co/600x400/37474F/E1F5FE?text=Meson+Full+Oscuro'),(5,2,2,'Oscuro','https://placehold.co/600x400/37474F/E1F5FE?text=Anaquel+Sup.+Full+Oscuro'),(6,2,3,'Oscuro','https://placehold.co/600x400/37474F/E1F5FE?text=Anaquel+Inf.+Full+Oscuro');
/*!40000 ALTER TABLE `acabado_detalle` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `acabado_kit`
--

LOCK TABLES `acabado_kit` WRITE;
/*!40000 ALTER TABLE `acabado_kit` DISABLE KEYS */;
INSERT INTO `acabado_kit` VALUES (1,'Cocina Standar','Acabados estándar para el modelo de cocina principal.','https://cedreo.com/wp-content/uploads/cloudinary/US_Kitchen_09_2D_554px_mg3dmt.jpg',0.00),(2,'Cocina Full','Acabados de lujo para el modelo de cocina full equipada.','https://content.elmueble.com/medio/2022/02/11/00541744-o_520915c4_2000x1327.jpg',3450.00);
/*!40000 ALTER TABLE `acabado_kit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agendamiento_visitas`
--

DROP TABLE IF EXISTS `agendamiento_visitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agendamiento_visitas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `responsable_id` bigint(20) unsigned NOT NULL,
  `proposito_id` tinyint(3) unsigned NOT NULL,
  `fecha_ingreso` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_reunion` date NOT NULL,
  `hora_reunion` time NOT NULL,
  `estado` enum('PROGRAMADO','CONFIRMADO','CANCELADO') COLLATE utf8_unicode_ci DEFAULT 'PROGRAMADO',
  `resultado` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `id_propiedad` bigint(20) unsigned DEFAULT NULL,
  `observaciones` text COLLATE utf8_unicode_ci COMMENT 'Objetivo o descripcion breve de la visita proporcionada por el usario',
  `duracion_minutos` smallint(6) DEFAULT NULL COMMENT 'Duración específica de la cita en minutos',
  `leido` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_responsable_hora` (`responsable_id`,`fecha_reunion`,`hora_reunion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `fk_agendamiento_propiedad` (`id_propiedad`),
  KEY `fk_visita_proposito` (`proposito_id`),
  CONSTRAINT `agendamiento_visitas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  CONSTRAINT `agendamiento_visitas_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `responsable` (`id`),
  CONSTRAINT `fk_agendamiento_propiedad` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedad` (`id`),
  CONSTRAINT `fk_visita_proposito` FOREIGN KEY (`proposito_id`) REFERENCES `proposito_agendamiento` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agendamiento_visitas`
--

LOCK TABLES `agendamiento_visitas` WRITE;
/*!40000 ALTER TABLE `agendamiento_visitas` DISABLE KEYS */;
INSERT INTO `agendamiento_visitas` VALUES (39,8,1,2,'2025-08-13 11:16:35','2025-08-15','10:00:00','PROGRAMADO',NULL,NULL,5,NULL,NULL,0),(41,8,2,4,'2025-08-13 11:58:47','2025-08-19','12:00:00','PROGRAMADO',NULL,NULL,5,NULL,NULL,0),(42,8,1,1,'2025-08-13 12:15:23','2025-08-27','15:00:00','PROGRAMADO',NULL,NULL,5,NULL,NULL,0),(43,8,2,1,'2025-08-14 11:50:40','2025-08-20','13:00:00','PROGRAMADO',NULL,NULL,5,NULL,NULL,0),(44,8,2,1,'2025-08-18 12:45:04','2025-08-19','15:00:00','PROGRAMADO',NULL,NULL,5,'Revisar la pared',NULL,0),(45,8,2,1,'2025-08-18 12:45:31','2025-08-26','12:00:00','PROGRAMADO',NULL,NULL,5,'',NULL,0),(46,8,2,1,'2025-08-18 12:45:59','2025-08-19','13:00:00','PROGRAMADO',NULL,NULL,5,'',NULL,0),(48,8,1,3,'2025-08-18 12:48:10','2025-08-22','10:00:00','PROGRAMADO',NULL,NULL,5,'',NULL,0),(49,8,2,1,'2025-08-18 12:48:17','2025-08-21','14:00:00','PROGRAMADO',NULL,NULL,5,'',NULL,0),(50,8,1,4,'2025-08-18 12:48:28','2025-08-20','15:00:00','PROGRAMADO',NULL,NULL,5,'',NULL,0),(55,8,1,1,'2025-08-22 15:22:50','2025-08-28','15:00:00','PROGRAMADO',NULL,NULL,5,'Recorrer mi terrenito',NULL,0),(58,9,2,2,'2025-08-25 12:43:38','2025-08-27','13:00:00','PROGRAMADO',NULL,NULL,14,'',NULL,0),(107,8,1,2,'2025-08-28 14:20:37','2025-09-10','09:00:00','PROGRAMADO',NULL,NULL,5,'',120,0),(108,8,2,2,'2025-08-28 14:20:46','2025-09-10','09:00:00','PROGRAMADO',NULL,NULL,5,'',120,0),(116,8,2,2,'2025-08-29 09:58:30','2025-09-15','13:00:00','PROGRAMADO',NULL,NULL,5,'',120,0);
/*!40000 ALTER TABLE `agendamiento_visitas` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `componente`
--

LOCK TABLES `componente` WRITE;
/*!40000 ALTER TABLE `componente` DISABLE KEYS */;
INSERT INTO `componente` VALUES (1,'Mesón'),(2,'Anaquel Superior'),(3,'Anaquel Inferior'),(4,'Piso'),(5,'Salpicadero');
/*!40000 ALTER TABLE `componente` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ctg`
--

LOCK TABLES `ctg` WRITE;
/*!40000 ALTER TABLE `ctg` DISABLE KEYS */;
INSERT INTO `ctg` VALUES (1,'SAC00001',2,2,6,17,1,'El toma corriente de las luces de la sala no esta energizado',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/logCostaSol.jpg',NULL,1,'2025-06-24 11:17:38','2025-06-26 12:09:44',NULL,NULL,NULL),(5,'SAC00004',2,1,4,9,2,'no cierran correctamentes las puertas del cuarto master',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/logCostaSol.jpg','',1,'2025-06-24 11:32:11','2025-06-26 12:28:18',NULL,NULL,NULL),(6,'SAC00006',2,2,1,2,1,'esto es un pqr de prueba',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/685c7603bc9c4-Alba_Bosque.jpg',NULL,1,'2025-06-25 16:19:47','2025-06-30 16:19:47',NULL,NULL,NULL),(7,'SAC00007',7,3,1,1,1,'Arreglar el sistema',2,NULL,NULL,NULL,2,'2025-07-23 09:15:36','2025-07-28 09:15:36',NULL,NULL,NULL),(17,'SAC00008',7,3,4,12,1,'HOLS',1,NULL,NULL,NULL,1,'2025-07-23 16:26:26','2025-07-28 16:26:26',NULL,NULL,NULL),(18,'SAC00018',8,5,1,1,1,'Necesito ayuda con esto',2,NULL,NULL,NULL,1,'2025-07-24 16:09:34','2025-07-29 16:09:34',NULL,NULL,NULL),(21,'SAC00021',8,5,8,28,2,'Que tal',1,NULL,NULL,NULL,2,'2025-07-30 12:01:19','2025-08-04 12:01:19',NULL,'2025-08-15 13:46:08','cambia muchacho'),(22,'SAC00022',8,5,6,20,1,'No está bien puesto',1,NULL,NULL,NULL,2,'2025-08-13 15:16:20','2025-08-18 15:16:20',NULL,'2025-08-13 16:44:30','juhjkhjn'),(23,'SAC00023',8,5,1,3,1,'no cubre',1,NULL,NULL,NULL,3,'2025-08-15 08:56:43','2025-08-20 08:56:43',NULL,NULL,NULL),(24,'SAC00024',9,14,5,15,1,'me ayudan',1,NULL,NULL,NULL,2,'2025-08-22 14:24:07','2025-08-27 14:24:07',NULL,NULL,NULL),(25,'SAC00025',9,14,6,21,1,'holaaaaa',1,NULL,NULL,NULL,2,'2025-08-22 14:26:32','2025-08-27 14:26:32',NULL,NULL,NULL),(26,'SAC00026',9,14,1,3,5,'holaaaaaa',1,NULL,NULL,NULL,2,'2025-08-22 14:28:31','2025-08-27 14:28:31',NULL,'2025-08-25 11:21:19',NULL),(27,'SAC00027',8,5,8,31,1,'Mucha fuga',2,NULL,NULL,NULL,1,'2025-08-22 15:22:10','2025-08-27 15:22:10',NULL,NULL,NULL),(28,'SAC00028',9,14,5,15,1,'holas',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/68ac832d677a1-BigmodelPoster.png',NULL,1,'2025-08-25 09:37:17','2025-08-30 09:37:17',NULL,NULL,NULL),(29,'SAC00029',9,14,5,13,1,'eeere',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/68ac86f23a51d-BigmodelPoster.png',NULL,1,'2025-08-25 09:53:22','2025-08-30 09:53:22',NULL,NULL,NULL),(30,'SAC00030',9,14,6,20,1,'sss',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/68ac887e92dee-BigmodelPoster.png',NULL,1,'2025-08-25 09:59:58','2025-08-30 09:59:58',NULL,NULL,NULL),(31,'SAC00031',9,14,1,1,1,'HHH',2,NULL,'https://app.costasol.com.ec/ImagenesCTG_problema/68ac8f222923a-BigmodelPoster.png',NULL,2,'2025-08-25 10:28:18','2025-08-30 10:28:18',NULL,NULL,NULL);
/*!40000 ALTER TABLE `ctg` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `estado_ctg`
--

LOCK TABLES `estado_ctg` WRITE;
/*!40000 ALTER TABLE `estado_ctg` DISABLE KEYS */;
INSERT INTO `estado_ctg` VALUES (2,'En progreso'),(1,'Ingresado'),(6,'Negado'),(5,'Resuelto');
/*!40000 ALTER TABLE `estado_ctg` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `estado_pqr`
--

LOCK TABLES `estado_pqr` WRITE;
/*!40000 ALTER TABLE `estado_pqr` DISABLE KEYS */;
INSERT INTO `estado_pqr` VALUES (2,'En concideración'),(1,'Recibido');
/*!40000 ALTER TABLE `estado_pqr` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `estado_propiedad`
--

LOCK TABLES `estado_propiedad` WRITE;
/*!40000 ALTER TABLE `estado_propiedad` DISABLE KEYS */;
INSERT INTO `estado_propiedad` VALUES (6,'BLOQUEADA'),(1,'DISPONIBLE'),(7,'EN PROCESO DE DESISTIMIENTO'),(4,'EN_CONSTRUCCIÓN'),(5,'ENTREGADA'),(2,'RESERVADA'),(3,'VENDIDA');
/*!40000 ALTER TABLE `estado_propiedad` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `etapa_construccion`
--

LOCK TABLES `etapa_construccion` WRITE;
/*!40000 ALTER TABLE `etapa_construccion` DISABLE KEYS */;
INSERT INTO `etapa_construccion` VALUES (1,'Cimentación',10,'Es la base de la vivienda. En esta etapa se prepara el terreno y se construyen los cimientos de concreto que darán estab',1,'2025-06-12 10:39:44'),(2,'Losa',20,'Consiste en la construcción de la plancha de concreto que se coloca sobre las paredes. Esta losa funciona como piso resi',1,'2025-06-12 10:39:44'),(3,'Cubierta terminada',45,'Corresponde a la instalación del techo definitivo de la vivienda, ya sea de losa, teja, zinc u otro material, lo que gar',1,'2025-06-12 10:45:32'),(4,'Habitabilidad',95,'En esta etapa la vivienda ya cuenta con puertas, ventanas, instalaciones eléctricas, agua y demás servicios básicos, que',1,'2025-06-12 10:45:32'),(5,'Entrega',100,'Es la fase final, en la cual la vivienda ha sido terminada, revisada y se entrega oficialmente al propietario lista para',1,'2025-06-12 10:47:04');
/*!40000 ALTER TABLE `etapa_construccion` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kit_color_opcion`
--

LOCK TABLES `kit_color_opcion` WRITE;
/*!40000 ALTER TABLE `kit_color_opcion` DISABLE KEYS */;
INSERT INTO `kit_color_opcion` VALUES (1,1,'Cocina Estándar Clara','Claro','https://placehold.co/600x400/F5F5DC/333333?text=Estandar+Clara'),(2,1,'Cocina Estándar Oscura','Oscuro','https://placehold.co/600x400/333333/F5F5DC?text=Estandar+Oscura'),(3,2,'Cocina Full Clara','Claro','https://placehold.co/600x400/E0F2F7/004D40?text=Full+Clara'),(4,2,'Cocina Full Oscura','Oscuro','https://placehold.co/600x400/004D40/E0F2F7?text=Full+Oscura');
/*!40000 ALTER TABLE `kit_color_opcion` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (1,'Selección Acabados','Personaliza tu propiedad ','https://app.costasol.com.ec/iconos/SeleccionAcabados.svg',1,1,'2025-06-13 08:28:02',NULL,NULL,0),(2,'CTG','Contingencias','https://app.costasol.com.ec/iconos/PQR.svg',1,14,'2025-06-13 08:33:54','2025-08-14 11:08:21',NULL,0),(3,'Agendar Visitas','Programa una visita a tu propiedad','https://app.costasol.com.ec/iconos/Agendamientos.svg',0,12,'2025-06-13 09:06:59','2025-08-14 11:06:59',NULL,1),(4,'Empresas Aliadas','Descuentos y promociones exclusivas','https://app.costasol.com.ec/iconos/EmpresaAliada.svg',0,6,'2025-06-13 09:17:59','2025-08-14 10:32:17',NULL,1),(5,'Crédito Hipotecario','Seguimiento y estado del proceso de tu crédito','https://app.costasol.com.ec/iconos/CreditoHipotecario.svg',1,5,'2025-06-13 09:20:28','2025-06-13 09:20:54',NULL,0),(6,'Garantias','Información sobre garantías','https://app.costasol.com.ec/iconos/Garantias.svg',1,15,'2025-06-13 09:36:51','2025-08-14 13:18:01',NULL,0),(7,'Calendario Responsable','Revisa tu agenda','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/4c040ff0348e783b0b48564151d05dc6beee1420/imagenes/calendario.svg',1,7,'2025-07-22 14:49:21','2025-08-14 11:13:27',NULL,0),(8,'Notificaciones','Mantente al día de todo','https://app.costasol.com.ec/iconos/Notificaciones.svg',0,8,'2025-07-23 14:58:06','2025-08-14 11:44:29',NULL,0),(9,'PQR','Petición, Queja y Recomendaciones','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/4c040ff0348e783b0b48564151d05dc6beee1420/imagenes/pqr.svg',1,9,'2025-07-23 14:58:06','2025-08-14 11:03:46',NULL,0),(10,'Admin User','Administra usuarios','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/7004b621cc1e4d4bd8c71adb35d0584007bdefe5/imagenes/admin.svg',1,10,'2025-08-05 12:40:06','2025-08-20 08:16:54',NULL,0),(11,'MCM','Manual de uso, Conservación Y\nMantenimiento de la vivienda','https://app.costasol.com.ec/iconos/mcm.svg',1,2,'2025-08-06 10:32:06','2025-08-22 07:15:40',NULL,0),(12,'Paleta Vegetal','Inspírate y ornamenta tu jardín ','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/67c46d5b804b0915b795839b6092fa44cbd49ec6/imagenes/tree.svg',1,3,'2025-08-06 10:32:06','2025-08-14 09:22:53',NULL,0),(13,'Admin Noticias','Crea nuevas notificas para los clientes','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/cd80644730b850cd6794d75e174a0df6e063f17d/imagenes/news.svg',1,13,'2025-08-06 10:32:06','2025-08-20 08:16:53',NULL,0),(14,'Admin Responsable','Administra tus responsables','https://cdn.prod.website-files.com/5f68a65d0932e3546d41cc61/5f9bb022fda3f6ccfb8e316a_1604038688273-admin%252B-best-shopify-apps.png',0,14,'2025-08-13 15:34:06','2025-08-20 08:16:53',NULL,0),(15,'Ver más','Explora todas las opciones','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/ab7c3531d7e648fa535cd942c4d9794737b65156/imagenes/vermas.svg',1,4,'2025-08-13 15:34:06','2025-08-14 13:18:03',NULL,0);
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `noticia`
--

LOCK TABLES `noticia` WRITE;
/*!40000 ALTER TABLE `noticia` DISABLE KEYS */;
INSERT INTO `noticia` VALUES (1,'Ciclovia Costasol','Cada ciclovía, cada sombra, cada camino libre de autos, es parte de un lugar que fue pensado para ti. ?','Cada ciclovía, cada sombra, cada camino libre de autos, es parte de un lugar que fue pensado para ti. ?','https://app.costasol.com.ec/ImagenesNoticias/noticia1.jpg','https://www.facebook.com/share/p/19N5JeV1xH/',1,1,'2025-06-16 11:31:01',NULL,2),(2,'CostaSol la ciudad que respira ','Entre áreas verdes, tranquilidad y diseño pensado para ti, así se vive cuando la ciudad se transforma en hogar ??','Entre áreas verdes, tranquilidad y diseño pensado para ti, así se vive cuando la ciudad se transforma en hogar ??','https://app.costasol.com.ec/ImagenesNoticias/noticia2.jpg','https://www.facebook.com/share/p/1AjfbqvBhE/',2,1,'2025-06-16 14:11:25','2025-06-16 14:56:35',2);
/*!40000 ALTER TABLE `noticia` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `paquetes_adicionales`
--

LOCK TABLES `paquetes_adicionales` WRITE;
/*!40000 ALTER TABLE `paquetes_adicionales` DISABLE KEYS */;
INSERT INTO `paquetes_adicionales` VALUES (1,'Encimera de Granito','Reemplaza la encimera estándar por una de granito de alta calidad, resistente a rayaduras y calor.',850.00,'[\"https://d35y5t5rad2lom.cloudfront.net/images/upload/112/card/5e7e7395a96415.53919462.jpg\", \"https://img.freepik.com/foto-gratis/primer-plano-fondo-textura-marmol_53876-17994.jpg\"]',1),(2,'Encimera de Granito','Reemplaza la encimera estándar por una de granito de alta calidad, resistente a rayaduras y calor.',850.00,'[\"https://d35y5t5rad2lom.cloudfront.net/images/upload/112/card/5e7e7395a96415.53919462.jpg\", \"https://img.freepik.com/foto-gratis/primer-plano-fondo-textura-marmol_53876-17994.jpg\"]',1);
/*!40000 ALTER TABLE `paquetes_adicionales` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pqr`
--

LOCK TABLES `pqr` WRITE;
/*!40000 ALTER TABLE `pqr` DISABLE KEYS */;
INSERT INTO `pqr` VALUES (2,'SAC00002',8,5,3,NULL,1,'Cambien el piso',NULL,NULL,NULL,NULL,1,'2025-08-07 08:39:25','2025-08-12 08:39:25',NULL,'2025-08-13 15:13:40','no responde nunca'),(3,'SAC00003',8,5,2,NULL,2,'Cambien la pared',NULL,NULL,NULL,NULL,2,'2025-08-07 08:42:31','2025-08-12 08:42:31',NULL,'2025-08-08 11:19:18','este si le sabe'),(4,'SAC00004',8,5,3,NULL,1,'No dej',NULL,NULL,NULL,NULL,2,'2025-08-13 15:15:39','2025-08-18 15:15:39',NULL,'2025-08-15 09:02:00','no es pila'),(5,'SAC00005',8,5,3,NULL,1,'Recomienden planta',NULL,NULL,NULL,NULL,1,'2025-08-22 14:35:02','2025-08-27 14:35:02',NULL,NULL,NULL),(6,'SAC00006',8,5,2,NULL,1,'Cómo me puedo quejar',NULL,NULL,NULL,NULL,1,'2025-08-22 15:21:44','2025-08-27 15:21:44',NULL,NULL,NULL),(7,'SAC00007',9,14,2,NULL,1,'hols',NULL,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/68ac846f720cb-BigmodelPoster.png',NULL,2,'2025-08-25 09:42:39','2025-08-30 09:42:39',NULL,NULL,NULL),(8,'SAC00008',9,14,2,NULL,1,'a',NULL,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/68ac951ba6aca-BigmodelPoster.png',NULL,2,'2025-08-25 10:53:47','2025-08-30 10:53:47',NULL,NULL,NULL),(9,'SAC00009',9,14,1,NULL,1,'ee',NULL,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/68ac96c26aa1e-BigmodelPoster.png',NULL,1,'2025-08-25 11:00:50','2025-08-30 11:00:50',NULL,NULL,NULL);
/*!40000 ALTER TABLE `pqr` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `propiedad`
--

LOCK TABLES `propiedad` WRITE;
/*!40000 ALTER TABLE `propiedad` DISABLE KEYS */;
INSERT INTO `propiedad` VALUES (1,2,2,4,4,'2025-01-01','2030-06-01','2031-06-02','2025-06-17 08:24:56',3,'7119','03','03',NULL,NULL),(2,2,2,3,4,'2025-02-01','2026-01-01','2026-06-13','2025-06-17 09:54:30',3,'7117','33','33',NULL,NULL),(3,7,2,4,4,'2025-07-01','2030-07-10','2031-07-08','2025-07-23 08:57:32',3,'7119','03','03',NULL,NULL),(4,7,2,3,4,'2025-07-01','2030-07-01','2031-07-01','2025-07-23 09:08:06',3,'7117','33','33',NULL,NULL),(5,8,2,4,4,'2025-07-01','2030-07-10','2031-07-01','2025-07-24 08:57:32',3,'9999','99','99',1,'Oscuro'),(12,11,2,3,4,'2025-07-01','2030-07-01','2031-07-01','2025-08-01 09:08:06',3,'7117','33','33',1,'Oscuro'),(14,9,2,4,4,'2025-07-01','2030-07-10','2031-07-01','2025-07-24 08:57:32',3,'9999','99','99',2,'Oscuro'),(15,9,2,3,4,'2025-07-01','2030-07-01','2031-07-01','2025-08-01 09:08:06',3,'7117','33','33',NULL,NULL);
/*!40000 ALTER TABLE `propiedad` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `propiedad_paquetes_adicionales`
--

LOCK TABLES `propiedad_paquetes_adicionales` WRITE;
/*!40000 ALTER TABLE `propiedad_paquetes_adicionales` DISABLE KEYS */;
INSERT INTO `propiedad_paquetes_adicionales` VALUES (3,5,1,'2025-09-01 18:28:00');
/*!40000 ALTER TABLE `propiedad_paquetes_adicionales` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proposito_agendamiento`
--

LOCK TABLES `proposito_agendamiento` WRITE;
/*!40000 ALTER TABLE `proposito_agendamiento` DISABLE KEYS */;
INSERT INTO `proposito_agendamiento` VALUES (1,'Recorrido de Obra','https://app.costasol.com.ec/iconos/LogoCostaSolVerde.svg',1,'2025-06-27 10:26:18'),(2,'Elección de acabados','https://app.costasol.com.ec/iconos/SeleccionAcabados.svg',1,'2025-06-27 10:26:18'),(3,'Consultas con servicio al cliente ','https://app.costasol.com.ec/iconos/Agendamientos.svg',1,'2025-06-27 10:26:43'),(4,'Consultas con crédito y cobranzas','https://app.costasol.com.ec/iconos/CreditoHipotecario.svg',1,'2025-06-27 10:26:43');
/*!40000 ALTER TABLE `proposito_agendamiento` ENABLE KEYS */;
UNLOCK TABLES;

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
  `onesignal_player_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_actualizacion_player_id` timestamp NULL DEFAULT NULL COMMENT 'Fecha de última actualización del OneSignal Player ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  KEY `idx_responsable_onesignal_player_id` (`onesignal_player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `responsable`
--

LOCK TABLES `responsable` WRITE;
/*!40000 ALTER TABLE `responsable` DISABLE KEYS */;
INSERT INTO `responsable` VALUES (1,'Ana María Felix','mixcrafttopyt@gmail.com','$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa','https://app.costasol.com.ec/ImagenesPQR_problema/logCostaSol.jpg','SAC',1,'2025-06-24 11:03:35','2025-09-02 10:26:09','dD5uaejWImRaJfhu7PezoKVtOfddMDKv','494528dd-7ee5-4f51-a7af-c4a4f01cb231','2025-09-02 16:26:09'),(2,'Carla Oquendo','jdholguin@tes.edu.ec','$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa','https://static.wixstatic.com/media/b80279_33a586f04740464cae96a3a6205d2c19~mv2.png','SAC',1,'2025-06-24 11:07:21','2025-09-01 11:45:25','o8iajKsqLQBwJiajGRTK74gnlEbTfw3v','c3925247-841e-41d7-be9a-30560a3b7a01','2025-09-01 17:45:25'),(3,'Admin','admin@thaliavictoria.com.ec','$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa','https://app.costasol.com.ec/ImagenesPerfil/68a79276ed803-profile-3.png','SAC',1,'2025-08-13 09:25:21','2025-08-26 07:11:27','CCAm6iWGVgItYukte6T52lBcLGzL1975','7d49b608-347b-4308-954e-1edc4e1b6dee','2025-08-26 13:11:27');
/*!40000 ALTER TABLE `responsable` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `responsable_disponibilidad`
--

LOCK TABLES `responsable_disponibilidad` WRITE;
/*!40000 ALTER TABLE `responsable_disponibilidad` DISABLE KEYS */;
INSERT INTO `responsable_disponibilidad` VALUES (1,1,1,'09:00:00','17:00:00','2025-06-01','2026-06-01',1,60),(2,1,2,'09:00:00','17:00:00','2025-06-01','2026-06-01',1,60),(3,1,3,'09:00:00','17:00:00','2025-06-01','2026-06-01',1,60),(4,1,4,'09:00:00','17:00:00','2025-06-01','2026-06-01',1,60),(5,1,5,'09:00:00','17:00:00','2025-06-01','2026-06-01',1,60),(6,2,1,'09:00:00','17:00:00','2025-06-01','2026-06-01',1,60),(7,2,2,'09:00:00','17:00:00','2025-06-01','2026-06-01',1,60),(8,2,3,'09:00:00','17:00:00','2025-06-01','2026-06-01',1,60),(9,2,4,'09:00:00','17:00:00','2025-06-01','2026-06-01',1,60),(10,2,5,'09:00:00','17:00:00','2025-06-01','2026-06-01',1,60);
/*!40000 ALTER TABLE `responsable_disponibilidad` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respuesta_ctg`
--

LOCK TABLES `respuesta_ctg` WRITE;
/*!40000 ALTER TABLE `respuesta_ctg` DISABLE KEYS */;
INSERT INTO `respuesta_ctg` VALUES (11,7,7,2,'hola',NULL,'2025-07-23 11:49:54',0),(12,7,7,2,'En que lo puedo ayudar',NULL,'2025-07-23 11:53:21',0),(13,7,7,2,'hola',NULL,'2025-07-23 12:10:41',0),(14,7,7,2,'si',NULL,'2025-07-23 14:28:21',0),(15,7,7,2,'Hola',NULL,'2025-07-23 14:38:19',0),(16,7,7,2,'Que tal',NULL,'2025-07-23 14:44:58',0),(17,7,7,2,'Si',NULL,'2025-07-23 15:41:59',0),(18,7,7,2,'m',NULL,'2025-07-23 15:55:54',0),(19,7,7,2,'si',NULL,'2025-07-23 16:20:01',0),(21,7,7,2,'HOLA',NULL,'2025-07-24 10:49:24',0),(22,17,7,1,'hola',NULL,'2025-07-24 14:49:16',1),(23,17,NULL,1,'hola',NULL,'2025-07-24 15:10:23',0),(24,17,7,NULL,'necesito ayuda',NULL,'2025-07-24 15:13:43',1),(25,17,NULL,1,'claro dime',NULL,'2025-07-24 15:14:45',0),(26,7,NULL,2,'hola',NULL,'2025-07-24 15:47:48',0),(27,18,8,NULL,'Hola',NULL,'2025-07-24 16:09:43',1),(30,17,NULL,1,'holA',NULL,'2025-07-28 08:29:29',0),(31,7,NULL,2,'digame',NULL,'2025-07-28 08:33:29',0),(32,18,8,NULL,'Hol',NULL,'2025-07-28 08:39:16',1),(64,21,NULL,2,'Buenas',NULL,'2025-07-30 12:26:54',1),(78,21,8,NULL,'hola',NULL,'2025-08-07 08:25:59',1),(79,21,NULL,2,'como le va',NULL,'2025-08-07 08:26:21',1),(80,21,8,NULL,'digame',NULL,'2025-08-07 08:26:36',1),(81,21,NULL,2,'hola',NULL,'2025-08-07 15:33:14',1),(82,21,NULL,2,'vale respecto a su opinion no opino que sea oportuno la respuesta anonadada',NULL,'2025-08-07 16:04:58',1),(83,21,NULL,2,'hola',NULL,'2025-08-07 16:48:25',1),(84,21,NULL,2,'si',NULL,'2025-08-07 16:50:48',1),(85,21,8,NULL,'no',NULL,'2025-08-07 16:57:39',1),(86,21,NULL,2,'buenas',NULL,'2025-08-08 08:45:27',1),(87,21,8,NULL,'digame',NULL,'2025-08-08 08:46:05',1),(88,21,NULL,2,'el que',NULL,'2025-08-08 08:47:02',1),(89,21,8,NULL,'que cosa de que',NULL,'2025-08-08 08:48:46',1),(90,21,NULL,2,'no se dime tu',NULL,'2025-08-08 08:48:59',1),(91,21,8,NULL,'hola',NULL,'2025-08-08 10:49:27',1),(92,21,8,NULL,'hola',NULL,'2025-08-12 09:11:37',1),(93,21,NULL,2,'si','https://app.costasol.com.ec/ImagenesPQR_respuestas/689b4bb2c72a9-email-signature.png','2025-08-12 09:12:02',1),(94,21,8,NULL,'hola',NULL,'2025-08-12 09:14:08',1),(95,21,NULL,2,'diga',NULL,'2025-08-12 09:14:24',1),(96,21,8,NULL,'holaaaa',NULL,'2025-08-12 11:14:14',1),(97,21,NULL,2,'Diga',NULL,'2025-08-12 11:14:37',1),(98,21,8,NULL,'hola\r\n}',NULL,'2025-08-13 16:52:30',1),(99,21,NULL,2,'hola',NULL,'2025-08-13 16:52:47',1),(100,21,NULL,2,'hola',NULL,'2025-08-13 16:53:12',1),(101,21,NULL,2,'ohola',NULL,'2025-08-13 16:53:31',1),(102,21,8,NULL,'hola',NULL,'2025-08-15 08:54:37',1),(103,21,NULL,2,'hola',NULL,'2025-08-15 08:54:48',1),(104,21,8,NULL,'Diga',NULL,'2025-08-15 13:47:43',1),(105,21,8,NULL,'No estoy',NULL,'2025-08-15 13:47:50',1),(106,21,8,NULL,'Hola, dígame estoy muy cansado',NULL,'2025-08-15 14:00:42',1),(107,21,8,NULL,'Hola',NULL,'2025-08-15 15:04:05',1),(108,21,8,NULL,'Hola',NULL,'2025-08-15 15:04:30',1),(109,21,8,NULL,'Hola',NULL,'2025-08-15 15:04:39',1),(110,21,NULL,2,'hola',NULL,'2025-08-15 15:05:25',1),(111,21,NULL,2,'hola',NULL,'2025-08-15 15:05:33',1),(112,21,NULL,2,'hola',NULL,'2025-08-15 15:06:13',1),(113,21,NULL,2,'hola',NULL,'2025-08-15 15:07:09',1),(114,21,NULL,2,'hola',NULL,'2025-08-15 15:08:58',1),(115,21,8,NULL,'No gracias',NULL,'2025-08-15 15:19:22',1),(116,21,NULL,2,'de o que',NULL,'2025-08-15 15:19:49',1),(117,21,NULL,2,'sw',NULL,'2025-08-15 15:19:59',1),(118,21,8,NULL,'Hola',NULL,'2025-08-15 16:50:24',1),(119,21,8,NULL,'Hila',NULL,'2025-08-15 17:02:56',1),(120,21,8,NULL,'Dígame ayúdeme pues',NULL,'2025-08-15 17:03:38',1),(121,21,8,NULL,'Hola',NULL,'2025-08-15 17:03:46',1),(122,21,8,NULL,'Hablé pues',NULL,'2025-08-18 08:16:47',1),(123,21,NULL,2,'el que o que',NULL,'2025-08-18 08:17:15',1),(124,21,NULL,2,'diga',NULL,'2025-08-18 08:18:11',1),(125,21,8,NULL,'no digo',NULL,'2025-08-18 08:19:53',1),(126,21,NULL,2,'si diga',NULL,'2025-08-18 08:45:04',1),(127,21,NULL,2,'el que',NULL,'2025-08-18 08:46:37',1),(128,18,NULL,1,'hola',NULL,'2025-08-20 17:09:35',0),(129,18,NULL,1,'hola',NULL,'2025-08-20 17:09:47',0),(130,18,NULL,1,'la',NULL,'2025-08-20 17:09:47',0),(131,18,NULL,1,'hola',NULL,'2025-08-20 17:18:51',0),(132,18,NULL,1,'aaaaaa',NULL,'2025-08-20 17:19:11',0),(133,26,9,NULL,'Si','https://app.costasol.com.ec/ImagenesPQR_respuestas/68ac81dc5d75b-BigmodelPoster.png','2025-08-25 09:31:40',1),(134,26,NULL,2,'hols','https://app.costasol.com.ec/ImagenesPQR_respuestas/68ac8294e5335-BigmodelPoster.png','2025-08-25 09:34:44',1),(135,26,NULL,2,'no sale','https://app.costasol.com.ec/ImagenesPQR_respuestas/68ac82bf6a686-BigmodelPoster.png','2025-08-25 09:35:27',1),(136,28,9,NULL,'si','https://app.costasol.com.ec/ImagenesCTG_respuestas/68ac8657d6faa-BigmodelPoster.png','2025-08-25 09:50:47',0),(137,29,9,NULL,'WWW','https://app.costasol.com.ec/ImagenesCTG_respuestas/68ac87cb077c3-BigmodelPoster.png','2025-08-25 09:56:59',0),(138,30,9,NULL,'<<<<<<<<<<','https://app.costasol.com.ec/ImagenesCTG_respuestas/68ac88c5f3994-BigmodelPoster.png','2025-08-25 10:01:09',1),(139,30,9,NULL,'<<<<<<<<<<','https://app.costasol.com.ec/ImagenesCTG_respuestas/68ac88c6409a5-BigmodelPoster.png','2025-08-25 10:01:10',1),(140,30,9,NULL,'apoco si',NULL,'2025-08-25 10:08:01',1),(141,26,9,NULL,'diga',NULL,'2025-08-25 10:09:21',1),(142,26,NULL,2,'mire','https://app.costasol.com.ec/ImagenesCTG_respuestas/68ac8abfbab71-BigmodelPoster.png','2025-08-25 10:09:35',1),(143,21,8,NULL,'Si o no','https://app.costasol.com.ec/ImagenesCTG_respuestas/68acceb979a8d-IMG-20250825-WA0002.jpg','2025-08-25 14:59:37',1),(144,26,9,NULL,'hola',NULL,'2025-08-26 07:39:02',1),(145,26,9,NULL,'hola',NULL,'2025-08-26 07:39:18',1),(146,26,9,NULL,'hla',NULL,'2025-08-26 07:39:34',1),(147,26,9,NULL,'hola',NULL,'2025-08-26 07:39:42',1),(148,21,NULL,2,'Hola',NULL,'2025-08-26 07:40:23',1),(149,21,NULL,2,'Hola',NULL,'2025-08-26 07:41:15',1),(150,21,NULL,2,'Hola',NULL,'2025-08-26 07:42:02',1),(151,21,NULL,2,'Hola',NULL,'2025-08-26 07:43:17',1),(152,21,NULL,2,'Hola',NULL,'2025-08-26 07:47:40',1),(153,26,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 08:43:53',1),(154,26,9,NULL,'eeeeeeeeeeee',NULL,'2025-08-26 08:45:10',1),(155,26,9,NULL,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 08:45:18',1),(156,26,9,NULL,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 09:53:17',1),(157,26,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 09:59:08',1),(158,26,9,NULL,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 10:12:00',1),(159,26,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 10:27:25',1),(160,26,9,NULL,'Aaa',NULL,'2025-08-26 10:37:56',1),(161,26,9,NULL,'Hola',NULL,'2025-08-26 11:02:39',1),(162,26,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 11:02:57',1),(163,26,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa','https://app.costasol.com.ec/ImagenesCTG_respuestas/68adf533e1add-BigmodelPoster.png','2025-08-26 11:56:03',1),(164,21,8,NULL,'si','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af41d14d7b3-PROYECTO ADMINISTRACIÓN CAPITULO 1.pdf','2025-08-27 11:35:13',0),(165,21,8,NULL,'audiio','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af44aa4e794-','2025-08-27 11:47:22',0),(166,21,8,NULL,'sql+','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af44dc6ffe3-','2025-08-27 11:48:12',0),(167,21,8,NULL,'aaaa','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af455d8e732-','2025-08-27 11:50:21',0),(168,21,8,NULL,'si','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af457a52197-','2025-08-27 11:50:50',0),(169,21,8,NULL,'eee','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af469cac603-','2025-08-27 11:55:40',0),(170,21,8,NULL,'sql+','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af46b07d35f-','2025-08-27 11:56:00',0),(171,21,8,NULL,'sql+','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af46b114015-','2025-08-27 11:56:01',0),(172,21,8,NULL,'sql','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af46f53d50f-','2025-08-27 11:57:09',0),(173,21,8,NULL,'hola','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af48021a506-PROYECTOADMINISTRACINCAPITULO1.pdf','2025-08-27 12:01:38',0),(174,21,8,NULL,'si','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af480d660b1-FORMATO_PROYECTOCONTABILIDADGENERAL.docx','2025-08-27 12:01:49',0),(175,21,8,NULL,'sqlk','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af482d344c1-portalao_appcostasol.sql','2025-08-27 12:02:21',0),(176,21,8,NULL,'img','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af484c66349-BigmodelPoster.png','2025-08-27 12:02:52',0),(177,21,8,NULL,'e','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af48598ca11-WhatsAppAudio2025-08-27at85151AM1.ogg','2025-08-27 12:03:05',0),(178,21,8,NULL,'hola','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af48bb17044-65562-515098354_small.mp4','2025-08-27 12:04:43',0),(179,21,8,NULL,'eee','https://app.costasol.com.ec/ImagenesCTG_respuestas/BigmodelPoster.png','2025-08-27 12:11:34',0),(180,21,8,NULL,'ee','https://app.costasol.com.ec/ImagenesCTG_respuestas/BigmodelPoster%281%29.png','2025-08-28 07:22:02',0),(181,21,8,NULL,'sql','https://app.costasol.com.ec/ImagenesCTG_respuestas/portalao_appcostasol.sql','2025-08-28 07:22:23',0),(182,21,8,NULL,'eeee','https://app.costasol.com.ec/ImagenesCTG_respuestas/65562-515098354_small.mp4','2025-08-28 07:24:00',0),(183,21,8,NULL,'lll','https://app.costasol.com.ec/ImagenesCTG_respuestas/WhatsAppAudio2025-08-27at85151AM1.ogg','2025-08-28 07:25:14',0),(184,21,8,NULL,'excel','https://app.costasol.com.ec/ImagenesCTG_respuestas/REP-SAC-03ReportedeAtencinaContingenciasajunio2025__.xlsx','2025-08-28 08:03:38',0),(185,21,8,NULL,'svg','https://app.costasol.com.ec/ImagenesCTG_respuestas/user-tie.svg','2025-08-28 08:04:20',0),(186,21,8,NULL,'php','https://app.costasol.com.ec/ImagenesCTG_respuestas/login_front.php','2025-08-28 08:04:56',0),(187,21,8,NULL,'exe','https://app.costasol.com.ec/ImagenesCTG_respuestas/VSCodeUserSetup-x64-11021.exe','2025-08-28 08:07:36',0),(188,21,8,NULL,'wasa','https://app.costasol.com.ec/ImagenesCTG_respuestas/WhatsAppAudio2025-08-27at85151AM.mp4','2025-08-28 08:20:38',0),(189,21,8,NULL,'mp3','https://app.costasol.com.ec/ImagenesCTG_respuestas/WhatsAppAudio2025-08-27at85151AM1.mp3','2025-08-28 08:23:14',0),(190,21,8,NULL,'ssss','https://app.costasol.com.ec/ImagenesCTG_respuestas/WhatsAppAudio2025-08-27at85151AM1%281%29.mp3','2025-08-28 10:29:49',0),(191,21,8,NULL,'ogg','https://app.costasol.com.ec/ImagenesCTG_respuestas/WhatsAppAudio2025-08-27at85151AM1%281%29.ogg','2025-08-28 10:30:00',0);
/*!40000 ALTER TABLE `respuesta_ctg` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respuesta_pqr`
--

LOCK TABLES `respuesta_pqr` WRITE;
/*!40000 ALTER TABLE `respuesta_pqr` DISABLE KEYS */;
INSERT INTO `respuesta_pqr` VALUES (7,3,NULL,2,'no',NULL,'2025-08-07 09:14:59',1),(8,3,NULL,2,'si o no',NULL,'2025-08-07 15:33:46',1),(9,3,NULL,2,'hol',NULL,'2025-08-07 15:34:12',1),(10,3,8,NULL,'HOLA',NULL,'2025-08-07 15:35:02',1),(11,3,NULL,2,'digame',NULL,'2025-08-07 15:57:10',1),(12,3,8,NULL,'el que',NULL,'2025-08-07 15:57:23',1),(13,3,NULL,2,'no se',NULL,'2025-08-07 16:01:49',1),(14,3,8,NULL,'diga',NULL,'2025-08-08 11:17:39',1),(15,3,NULL,2,'que te digo',NULL,'2025-08-08 11:17:52',1),(16,3,NULL,2,'que te digo',NULL,'2025-08-08 11:18:27',1),(17,3,8,NULL,'diga pues',NULL,'2025-08-08 11:18:43',1),(18,3,NULL,2,'el que pues',NULL,'2025-08-08 11:18:50',1),(19,2,NULL,1,'hable pues',NULL,'2025-08-13 15:13:29',1),(20,2,8,NULL,'que quiere',NULL,'2025-08-13 15:14:49',1),(21,4,8,NULL,'que no deja',NULL,'2025-08-15 09:01:25',1),(22,4,NULL,2,'que cosa',NULL,'2025-08-15 09:01:41',1),(23,4,8,NULL,'elp que de que',NULL,'2025-08-15 09:30:46',1),(24,4,NULL,2,'que no deja',NULL,'2025-08-15 09:36:38',1),(25,4,8,NULL,'responda pues',NULL,'2025-08-15 09:37:34',1),(26,2,8,NULL,'cambien el piso no lee',NULL,'2025-08-15 09:38:57',1),(27,2,NULL,1,'no sea sapo',NULL,'2025-08-15 09:40:00',1),(28,2,NULL,1,'hable',NULL,'2025-08-15 09:46:06',1),(29,2,8,NULL,'Que deseas',NULL,'2025-08-15 09:47:33',1),(30,2,NULL,1,'hable',NULL,'2025-08-15 09:52:01',1),(31,2,NULL,1,'hable',NULL,'2025-08-15 09:52:03',1),(32,2,NULL,1,'responda',NULL,'2025-08-15 11:25:59',1),(33,3,NULL,2,'hola',NULL,'2025-08-15 13:14:28',1),(34,3,NULL,2,'hola',NULL,'2025-08-18 10:26:49',1),(35,3,8,NULL,'Hola',NULL,'2025-08-20 16:35:24',1),(36,3,NULL,2,'hola',NULL,'2025-08-20 16:36:24',1),(37,3,NULL,2,'responda',NULL,'2025-08-20 16:37:24',1),(38,3,NULL,2,'hableee',NULL,'2025-08-20 16:37:40',1),(39,3,NULL,2,'hola',NULL,'2025-08-20 16:40:06',1),(40,2,NULL,1,'hable',NULL,'2025-08-20 16:41:32',1),(41,3,NULL,2,'que pues',NULL,'2025-08-20 16:46:51',1),(42,3,8,NULL,'Diga',NULL,'2025-08-20 16:47:19',1),(43,3,8,NULL,'Hola',NULL,'2025-08-20 16:47:49',1),(44,3,NULL,2,'diga',NULL,'2025-08-20 16:48:12',0),(45,3,NULL,2,'hola',NULL,'2025-08-20 16:48:25',0),(46,3,NULL,2,'hol',NULL,'2025-08-21 07:05:53',0),(47,2,NULL,1,'hola',NULL,'2025-08-21 07:38:06',1),(48,2,NULL,1,'hablehable',NULL,'2025-08-21 07:42:42',1),(49,2,NULL,1,'digaaaa',NULL,'2025-08-21 16:08:23',1),(50,2,NULL,1,'hola',NULL,'2025-08-21 16:08:37',1),(51,7,9,NULL,'hi',NULL,'2025-08-25 10:25:27',1),(52,7,9,NULL,'jelou','https://app.costasol.com.ec/ImagenesPQR_respuestas/68ac8e83a6d94-BigmodelPoster.png','2025-08-25 10:25:39',1),(53,7,9,NULL,'ddd','https://app.costasol.com.ec/ImagenesPQR_respuestas/68ac95eb6c360-BigmodelPoster.png','2025-08-25 10:57:15',1),(54,7,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 11:44:32',1),(55,2,8,NULL,'si','https://app.costasol.com.ec/ImagenesPQR_respuestas/WhatsAppAudio2025-08-27at85151AM1.mp3','2025-08-28 08:29:48',0),(56,2,8,NULL,'eexce','https://app.costasol.com.ec/ImagenesPQR_respuestas/REP-SAC-03ReportedeAtencinaContingenciasajunio2025__.xlsx','2025-08-28 08:30:06',0),(57,2,8,NULL,'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz',NULL,'2025-08-28 08:37:49',0),(58,2,8,NULL,'wwdwdwad',NULL,'2025-08-28 08:41:26',0),(59,9,NULL,1,'ssss',NULL,'2025-09-02 10:01:06',1),(60,9,9,NULL,'ssss',NULL,'2025-09-02 10:02:26',0);
/*!40000 ALTER TABLE `respuesta_pqr` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (1,'Cliente','Persona que ha adquirido una propiedad en Costasol pero aun no ha sido entregada.'),(2,'Residente','Persona a la que ya se le entrego una propiedad en Costasol'),(3,'SAC','Administrador');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `rol_menu`
--

LOCK TABLES `rol_menu` WRITE;
/*!40000 ALTER TABLE `rol_menu` DISABLE KEYS */;
INSERT INTO `rol_menu` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(3,7),(1,8),(1,9),(3,10),(2,11),(1,12),(3,13),(1,15);
/*!40000 ALTER TABLE `rol_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subtipo_ctg`
--

DROP TABLE IF EXISTS `subtipo_ctg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subtipo_ctg` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_id` tinyint(3) unsigned NOT NULL,
  `nombre` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `urgencia_id` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tipo_nombre` (`tipo_id`,`nombre`),
  KEY `fk_subtipo_urgencia` (`urgencia_id`),
  CONSTRAINT `fk_subtipo_tipo_ctg` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_ctg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_subtipo_urgencia_ctg` FOREIGN KEY (`urgencia_id`) REFERENCES `urgencia_ctg` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subtipo_ctg`
--

LOCK TABLES `subtipo_ctg` WRITE;
/*!40000 ALTER TABLE `subtipo_ctg` DISABLE KEYS */;
INSERT INTO `subtipo_ctg` VALUES (1,1,'Filtracion de agua por cubierta',2),(2,1,'Flashing desalineado',1),(3,1,'Abertura entre alero y viga de cubierta',1),(4,1,'Otros',1),(5,2,'Filtración de agua por falta de silicón en ventana',2),(6,2,'Seguro o rodachines dañados',1),(7,2,'Otros',1),(8,3,'Fisuras cerramiento lateral y/o posterior',1),(9,4,'Bisagra floja',1),(10,4,'Daño en tapa (descuadre, falta de perforación)',1),(11,4,'Defecto de instalación de cisterna',1),(12,4,'Otros',1),(13,5,'Tubería a/c: obstruidos',1),(14,5,'Tubería a/c: perforada',1),(15,5,'Tubería a/c: instalación incorrecta',1),(16,5,'Otros',1),(17,6,'Cajas de paso red/datos',1),(18,6,'Tubería red/datos obstruido',1),(19,6,'Tubería red/datos sin pasante',1),(20,6,'Tubería red/datos guías galvanizadas oxidadas',1),(21,6,'Otros',1),(22,7,'Interruptores o tomacorriente defectuosos',1),(23,7,'Cableado de puntos eléctricos',2),(24,7,'Falta de suministro de tomacorrientes o interrupto',1),(25,7,'Cortocircuito',2),(26,7,'Otros',1),(27,8,'Drenajes obstruidos y/o tapados',1),(28,8,'Cajas y conexiones de aa.ss',1),(29,8,'Silicón y/o empore en piezas sanitarias',1),(30,8,'Piezas sanitarias flojas y/o mal instaladas',1),(31,8,'Fuga de agua en accesorios, griferías y/o piezas s',2),(32,8,'Suministro de accesorios',1),(33,8,'Conexión calefón agua caliente o fria',1),(34,8,'Malos olores por sifones o piezas sanitarias',1),(35,8,'Daño de accesorios sanitarios',1),(36,8,'Fuga incontenible de agua',2),(37,8,'Otros',1),(38,9,'Maniguetas con falla',1),(39,9,'Bisagras o rieles oxidadas y/o con ruido',1),(40,9,'Material humedo / soplado',1),(41,9,'Olor a humedad del material',1),(42,9,'Otros',1),(43,10,'Fisuras en paredes interiores y/o exteriores',1),(44,10,'Enlucido fofo',1),(45,10,'Fisuras en boquetes de ventanas',1),(46,10,'Empaste o pintura soplada / desprendida',1),(47,10,'Albañileria enlucido',1),(48,10,'Otros',1),(49,11,'Anclajes deficientes (flojo)',1),(50,11,'Otros',1),(51,12,'Puerta metálica - descuadrada y/o desoldada',1),(52,12,'Cerraduras principal con fallas',1),(53,12,'Puerta metálica - cerradura dañada',1),(54,12,'Cerraduras/ pomo con fallas',1),(55,12,'Bisagras con falla',1),(56,12,'Otros',1),(57,13,'Piso:Placa fofa',1),(58,13,'Paredes: placa fofa',1),(59,13,'Acabado de empore',1),(60,13,'Otros',1),(61,14,'Defecto de instalación de planchas de gypsum',1),(62,14,'Tumbado humedo por filtracion',1),(63,14,'Fisuras en tumbado de gypsum tipo losa',1),(64,14,'Otros',1),(65,15,'Otros',1);
/*!40000 ALTER TABLE `subtipo_ctg` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_ctg`
--

LOCK TABLES `tipo_ctg` WRITE;
/*!40000 ALTER TABLE `tipo_ctg` DISABLE KEYS */;
INSERT INTO `tipo_ctg` VALUES (1,'Cubierta','1','1'),(2,'Aluminio y Vidrio','0.3','1'),(3,'Cerramiento','1','1'),(4,'Cisterna','0.3','1'),(5,'Instalaciones de Climatización','0.6','0.6'),(6,'Instalaciones de Voz y Datos','1','1'),(7,'Instalaciones Eléctricas','0.3','1'),(8,'Instalaciones Sanitarias','0.3','0.6'),(9,'Mobiliario cocina-baños-closets','0.3','0.6'),(10,'Paredes','1','1'),(11,'Pasamanos','0.3','0.3'),(12,'Puertas','0.3','0.3'),(13,'Recubrimientos','0.3','0.6'),(14,'Tumbado','1','1'),(15,'Otros','0.3','0.3');
/*!40000 ALTER TABLE `tipo_ctg` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `tipo_pqr`
--

LOCK TABLES `tipo_pqr` WRITE;
/*!40000 ALTER TABLE `tipo_pqr` DISABLE KEYS */;
INSERT INTO `tipo_pqr` VALUES (1,'Peticion'),(2,'Queja'),(3,'Recomendacion');
/*!40000 ALTER TABLE `tipo_pqr` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `tipo_propiedad`
--

LOCK TABLES `tipo_propiedad` WRITE;
/*!40000 ALTER TABLE `tipo_propiedad` DISABLE KEYS */;
INSERT INTO `tipo_propiedad` VALUES (2,'Casa'),(3,'Departamento'),(1,'Terreno');
/*!40000 ALTER TABLE `tipo_propiedad` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `urbanizacion`
--

LOCK TABLES `urbanizacion` WRITE;
/*!40000 ALTER TABLE `urbanizacion` DISABLE KEYS */;
INSERT INTO `urbanizacion` VALUES (1,'Arienzo',1,'2025-06-17 08:15:24'),(2,'Basilea',1,'2025-06-17 08:15:24'),(3,'Catania',1,'2025-06-17 08:15:24'),(4,'Davos',1,'2025-06-17 08:15:24'),(5,'Estanzza',1,'2025-06-17 08:15:24');
/*!40000 ALTER TABLE `urbanizacion` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `urgencia_ctg`
--

LOCK TABLES `urgencia_ctg` WRITE;
/*!40000 ALTER TABLE `urgencia_ctg` DISABLE KEYS */;
INSERT INTO `urgencia_ctg` VALUES (1,'BASICA'),(2,'URGENTE');
/*!40000 ALTER TABLE `urgencia_ctg` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (2,1,'Guillermo','Coello','0922797790','593982033045','gcoello@costasol.com.ec','$2y$10$RGAlN206FkvrivRqB86qE.pahU4Q7ThD3dWKgndwpJFbb75WN2t/.',1,'https://app.costasol.com.ec/FotoPerfil/FotoUsuarioGC.jpg','2025-06-10 10:42:58','BYFPee8yS1zM59X/7YPMSa1ifwJj3qA0',NULL,'2025-08-26 13:16:29'),(3,2,'Guillermo2','Coello2',NULL,NULL,'dr.gecb21@hotmail.com','$2y$10$MdNXTfLD2h3lbelU4QdN4eVssugUOrpDOrq5Yxx9U48ok8Finrh/u',0,NULL,'2025-06-10 11:05:44',NULL,NULL,NULL),(4,1,'Carlos','Pablo',NULL,NULL,'carlospablo@thaliavictoria.com.ec','$2y$10$S.HaX.E8Y0kc2hiGc1NJLus7h760yWOvh6ZqNcUSCx7T73iGRvVHi',0,NULL,'2025-07-03 13:41:37','HfiJ4S293HOsH/0XDUdnv6mq0c/0YtA3',NULL,NULL),(5,1,'Rafael','Romero',NULL,NULL,'rromero@thaliavictoria.com.ec','$2y$10$uqExlkQ7Xw6xfgzbQyqefOJmFOGrL4Qt.iMnUliWzb5iH9xVBZIou',0,NULL,'2025-07-14 16:11:24',NULL,NULL,NULL),(6,1,'Jonathan','Quijano',NULL,NULL,'jquijano@thaliavictoria.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-07-14 16:12:49',NULL,NULL,NULL),(7,1,'Joffre','Holguin',NULL,NULL,'joffreholguin19@gmail.com','$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa',1,'https://cdn-icons-png.flaticon.com/512/9187/9187532.png','2025-07-22 10:50:30','g9lrsOmr2BybWgrqIdB6k3dCmYOukdfX',NULL,'2025-08-22 20:30:44'),(8,1,'Daniel','Alarcon',NULL,NULL,'danielalarcon@gmail.com','$2y$10$KxKCaEiyWPjABgwuzODM.eWQlVCmLGgqISjp.5dkCDt5UYowRSGHC',1,'https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_640.png','2025-07-24 16:06:19','QZUftekO8Ol1AAx1ICCbyYLN9mpV2Ywb','e3c1007c-5a95-422a-adc4-9f7f05a0ccbc','2025-09-02 13:26:09'),(9,2,'Felipe','Pilligua',NULL,NULL,'fepilligua@gmail.com','$2y$10$reWiHb52f7EPZNo98Q3kz.H0Df6eFFB6FJrgll3nGywKhCmgl/hvC',1,'https://cdn-icons-png.flaticon.com/512/219/219983.png','2025-07-28 08:42:37','IUyo3O27MopmUVqVa+9P6Chj5BO/ptQc','c3925247-841e-41d7-be9a-30560a3b7a01','2025-09-02 16:22:19'),(10,1,'Jose','Tenesaca',NULL,NULL,'josesaca@gmail.com','$2y$10$8LEm..7rJ3ii/7QWAivJOuHJMjh3lQ4n/qEaqTI66Wu3YPKLcpble',0,NULL,'2025-08-05 13:25:22','iCHj2yX2GP5NG9lt7+NCkN/wPFr7tH2D',NULL,'2025-08-05 18:32:06'),(11,1,'Martin','Mera',NULL,NULL,'martin@gmial.com','$2y$10$UtKsBWDz.Hdf5DPHSlmV5etztt5mez967KwQccui431EB5woBL4au',0,'https://cdn-icons-png.flaticon.com/512/219/219983.png','2025-08-06 08:16:43','BrqmYT8yOa/1kGwyhEiSHb74+1d+X8nQ','494528dd-7ee5-4f51-a7af-c4a4f01cb231','2025-09-02 16:25:49'),(12,1,'prueba','1',NULL,NULL,'prueba@prueba.com','$2y$10$447WYbjzXhELxQuweQbcEuNgS4HiO.PxkyqBbm7zQ4XC.evK9FpoS',0,NULL,'2025-08-13 12:37:40','mc5PPSjS365g9XGa8+xrmQWPqdUg8t8s',NULL,'2025-08-13 17:37:52'),(13,1,'prueba','2',NULL,NULL,'prueba2@prueba.com','$2y$10$K2tKUN8/SS/o8Nhr7wnG3uTkzy5FdI3H8.kze3wAxd5w7a6tA0OZO',0,NULL,'2025-08-13 12:39:18',NULL,NULL,NULL),(14,1,'Geovanny','Herrera',NULL,NULL,'gherrera@costasol.com','$2y$10$J0CPBMknCZLdKYzSnjzPSe.lxh9ipybg8K1VWgliZxSj//u4tL8OK',0,NULL,'2025-07-23 07:56:38',NULL,NULL,NULL),(15,1,'Irma','Perez',NULL,NULL,'iperez@thaliavictoria.com.ec','$2y$10$MOGLPMPM2/JrPZt/KFh/oeoqV8Xg.U2S6RkLqPcya30gHGy43lXui',0,NULL,'2025-08-18 08:25:10',NULL,NULL,NULL),(16,2,'Guillermo','Coello',NULL,NULL,'gcoello@thaliavictoria.com.ec','$2y$10$BJWf37cacEKopZ2gAwO9.e55tu7Azw19MN7KzHSDJpHVHypylqLZm',0,NULL,'2025-08-26 07:25:17','ipAleDAF8NzM5N1lz3rZNDp4fLrK//kS',NULL,'2025-08-26 13:27:38');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-02 11:44:23
