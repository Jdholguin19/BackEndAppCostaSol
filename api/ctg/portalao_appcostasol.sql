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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ctg`
--

LOCK TABLES `ctg` WRITE;
/*!40000 ALTER TABLE `ctg` DISABLE KEYS */;
INSERT INTO `ctg` VALUES (1,'SAC00001',2,2,6,17,1,'El toma corriente de las luces de la sala no esta energizado',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/logCostaSol.jpg',NULL,1,'2025-06-24 11:17:38','2025-06-26 12:09:44',NULL,NULL,NULL),(5,'SAC00004',2,1,4,9,2,'no cierran correctamentes las puertas del cuarto master',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/logCostaSol.jpg','',1,'2025-06-24 11:32:11','2025-06-26 12:28:18',NULL,NULL,NULL),(6,'SAC00006',2,2,1,2,1,'esto es un pqr de prueba',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/685c7603bc9c4-Alba_Bosque.jpg',NULL,1,'2025-06-25 16:19:47','2025-06-30 16:19:47',NULL,NULL,NULL),(7,'SAC00007',7,3,1,1,1,'Arreglar el sistema',2,NULL,NULL,NULL,2,'2025-07-23 09:15:36','2025-07-28 09:15:36',NULL,NULL,NULL),(17,'SAC00008',7,3,4,12,1,'HOLS',1,NULL,NULL,NULL,1,'2025-07-23 16:26:26','2025-07-28 16:26:26',NULL,NULL,NULL),(18,'SAC00018',8,5,1,1,1,'Necesito ayuda con esto',2,NULL,NULL,NULL,1,'2025-07-24 16:09:34','2025-07-29 16:09:34',NULL,NULL,NULL),(21,'SAC00021',8,5,8,28,2,'Que tal',1,NULL,NULL,NULL,2,'2025-07-30 12:01:19','2025-08-04 12:01:19',NULL,'2025-08-15 13:46:08','cambia muchacho'),(22,'SAC00022',8,5,6,20,1,'No está bien puesto',1,NULL,NULL,NULL,2,'2025-08-13 15:16:20','2025-08-18 15:16:20',NULL,'2025-08-13 16:44:30','juhjkhjn'),(23,'SAC00023',8,5,1,3,1,'no cubre',1,NULL,NULL,NULL,3,'2025-08-15 08:56:43','2025-08-20 08:56:43',NULL,NULL,NULL),(24,'SAC00024',9,14,5,15,1,'me ayudan',1,NULL,NULL,NULL,2,'2025-08-22 14:24:07','2025-08-27 14:24:07',NULL,NULL,NULL),(25,'SAC00025',9,14,6,21,1,'holaaaaa',1,NULL,NULL,NULL,2,'2025-08-22 14:26:32','2025-08-27 14:26:32',NULL,NULL,NULL),(26,'SAC00026',9,14,1,3,5,'holaaaaaa',1,NULL,NULL,NULL,2,'2025-08-22 14:28:31','2025-08-27 14:28:31',NULL,'2025-08-25 11:21:19',NULL),(27,'SAC00027',8,5,8,31,1,'Mucha fuga',2,NULL,NULL,NULL,1,'2025-08-22 15:22:10','2025-08-27 15:22:10',NULL,NULL,NULL),(28,'SAC00028',9,14,5,15,1,'holas',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/68ac832d677a1-BigmodelPoster.png',NULL,1,'2025-08-25 09:37:17','2025-08-30 09:37:17',NULL,NULL,NULL),(29,'SAC00029',9,14,5,13,1,'eeere',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/68ac86f23a51d-BigmodelPoster.png',NULL,1,'2025-08-25 09:53:22','2025-08-30 09:53:22',NULL,NULL,NULL),(30,'SAC00030',9,14,6,20,1,'sss',1,NULL,'https://app.costasol.com.ec/ImagenesPQR_problema/68ac887e92dee-BigmodelPoster.png',NULL,1,'2025-08-25 09:59:58','2025-08-30 09:59:58',NULL,NULL,NULL),(31,'SAC00031',9,14,1,1,1,'HHH',2,NULL,'https://app.costasol.com.ec/ImagenesCTG_problema/68ac8f222923a-BigmodelPoster.png',NULL,2,'2025-08-25 10:28:18','2025-08-30 10:28:18',NULL,NULL,NULL),(32,'SAC00032',8,5,6,17,1,'Hahaha',1,NULL,NULL,NULL,1,'2025-09-15 10:28:01','2025-09-20 10:28:01',NULL,NULL,NULL),(33,'SAC00033',8,5,3,8,1,'ESTOS ES UNA PRUEBA',1,NULL,NULL,NULL,1,'2025-09-17 09:39:35','2025-09-22 09:39:35',NULL,NULL,NULL),(34,'SAC00034',8,5,9,39,1,'ESTO ES UNA PRUEBA PRUEBA',1,NULL,NULL,NULL,1,'2025-09-17 09:44:21','2025-09-22 09:44:21',NULL,NULL,NULL),(35,'SAC00035',8,5,9,40,1,'ESTO ES UNA PRUEBA PRUEBA',1,NULL,NULL,NULL,1,'2025-09-17 09:49:02','2025-09-22 09:49:02',NULL,NULL,NULL);
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
INSERT INTO `menu` VALUES (1,'Selección Acabados','Personaliza tu propiedad ','https://app.costasol.com.ec/iconos/SeleccionAcabados.svg',1,1,'2025-06-13 08:28:02',NULL,NULL,0),(2,'CTG','Contingencias','https://app.costasol.com.ec/iconos/PQR.svg',1,14,'2025-06-13 08:33:54','2025-08-14 11:08:21',NULL,0),(3,'Agendar Visitas','Programa una visita a tu propiedad','https://app.costasol.com.ec/iconos/Agendamientos.svg',0,12,'2025-06-13 09:06:59','2025-08-14 11:06:59',NULL,1),(4,'Empresas Aliadas','Descuentos y promociones exclusivas','https://app.costasol.com.ec/iconos/EmpresaAliada.svg',0,6,'2025-06-13 09:17:59','2025-08-14 10:32:17',NULL,1),(5,'Crédito Hipotecario','Seguimiento y estado del proceso de tu crédito','https://app.costasol.com.ec/iconos/CreditoHipotecario.svg',1,5,'2025-06-13 09:20:28','2025-06-13 09:20:54',NULL,0),(6,'Garantias','Información sobre garantías','https://app.costasol.com.ec/iconos/Garantias.svg',1,15,'2025-06-13 09:36:51','2025-08-14 13:18:01',NULL,0),(7,'Calendario Responsable','Revisa tu agenda','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/4c040ff0348e783b0b48564151d05dc6beee1420/imagenes/calendario.svg',1,7,'2025-07-22 14:49:21','2025-08-14 11:13:27',NULL,0),(8,'Notificaciones','Mantente al día de todo','https://app.costasol.com.ec/iconos/Notificaciones.svg',0,8,'2025-07-23 14:58:06','2025-08-14 11:44:29',NULL,0),(9,'PQR','Petición, Queja y Recomendaciones','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/4c040ff0348e783b0b48564151d05dc6beee1420/imagenes/pqr.svg',1,9,'2025-07-23 14:58:06','2025-08-14 11:03:46',NULL,0),(10,'Admin User','Administra usuarios','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/7004b621cc1e4d4bd8c71adb35d0584007bdefe5/imagenes/admin.svg',1,10,'2025-08-05 12:40:06','2025-08-20 08:16:54',NULL,0),(11,'MCM','Manual de uso, Conservación Y\nMantenimiento de la vivienda','https://app.costasol.com.ec/iconos/mcm.svg',1,2,'2025-08-06 10:32:06','2025-08-22 07:15:40',NULL,0),(12,'Paleta Vegetal','Inspírate y ornamenta tu jardín ','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/67c46d5b804b0915b795839b6092fa44cbd49ec6/imagenes/tree.svg',1,3,'2025-08-06 10:32:06','2025-08-14 09:22:53',NULL,0),(13,'Admin Noticias','Crea nuevas notificas para los clientes','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/cd80644730b850cd6794d75e174a0df6e063f17d/imagenes/news.svg',1,13,'2025-08-06 10:32:06','2025-08-20 08:16:53',NULL,0),(14,'Auditoria','Auditoria','https://cdn.prod.website-files.com/5f68a65d0932e3546d41cc61/5f9bb022fda3f6ccfb8e316a_1604038688273-admin%252B-best-shopify-apps.png',1,14,'2025-08-13 15:34:06','2025-09-15 13:55:03',NULL,0),(15,'Ver más','Explora todas las opciones','https://raw.githubusercontent.com/Jdholguin19/BackEndAppCostaSol/ab7c3531d7e648fa535cd942c4d9794737b65156/imagenes/vermas.svg',1,4,'2025-08-13 15:34:06','2025-08-14 13:18:03',NULL,0);
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
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
INSERT INTO `propiedad` VALUES (1,2,2,4,4,'2025-01-01','2030-06-01','2031-06-02','2025-06-17 08:24:56',3,'7119','03','03',NULL,NULL),(2,2,2,3,4,'2025-02-01','2026-01-01','2026-06-13','2025-06-17 09:54:30',3,'7117','33','33',NULL,NULL),(3,7,2,4,4,'2025-07-01','2030-07-10','2031-07-08','2025-07-23 08:57:32',3,'7119','03','03',NULL,NULL),(4,7,2,3,4,'2025-07-01','2030-07-01','2031-07-01','2025-07-23 09:08:06',3,'7117','33','33',NULL,NULL),(5,8,2,4,4,'2025-07-01','2030-07-10','2031-07-01','2025-07-24 08:57:32',3,'9999','99','99',1,'Oscuro'),(12,11,2,3,4,'2025-07-01','2030-07-01','2031-07-01','2025-08-01 09:08:06',3,'7117','33','33',1,'Oscuro'),(14,9,2,4,4,'2025-07-01','2030-07-10','2031-07-01','2025-07-24 08:57:32',3,'9999','99','99',2,'Oscuro'),(15,9,2,3,4,'2025-07-01','2030-07-01','2031-07-01','2025-08-01 09:08:06',3,'7117','33','33',1,'Oscuro');
/*!40000 ALTER TABLE `propiedad` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `responsable`
--

LOCK TABLES `responsable` WRITE;
/*!40000 ALTER TABLE `responsable` DISABLE KEYS */;
INSERT INTO `responsable` VALUES (1,'Ana María Felix','sistemas@thaliavictoria.com.ec','$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa','https://app.costasol.com.ec/ImagenesPQR_problema/logCostaSol.jpg','SAC',1,'2025-06-24 11:03:35','2025-09-17 09:40:33','7ldydu6o/Nn6Ll7vLiLc2l0sAqmnYS/2',NULL,NULL,NULL,NULL,NULL,NULL,'494528dd-7ee5-4f51-a7af-c4a4f01cb231','2025-09-17 15:38:47'),(2,'Andra Gonzales','gcoello@thaliavictoria.com.ec','$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa','https://static.wixstatic.com/media/b80279_33a586f04740464cae96a3a6205d2c19~mv2.png','SAC',1,'2025-06-24 11:07:21','2025-09-17 09:39:56','SOpdiQfrx5Av6P3q+QXFNes8VITuHN3/','eyJ0eXAiOiJKV1QiLCJub25jZSI6IkZCaGVXNnR4SzR4RktRZjI0NXlaTmJYT0xPem9UcVZraVhENDRSV1RyWTgiLCJhbGciOiJSUzI1NiIsIng1dCI6IkpZaEFjVFBNWl9MWDZEQmxPV1E3SG4wTmVYRSIsImtpZCI6IkpZaEFjVFBNWl9MWDZEQmxPV1E3SG4wTmVYRSJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC9iOTYxOGFjNi0yNjQ4LTQxZWQtYmI0Zi0wM2JjZDk0YTc0OTMvIiwiaWF0IjoxNzU3OTcxOTMyLCJuYmYiOjE3NTc5NzE5MzIsImV4cCI6MTc1Nzk3NzA0NywiYWNjdCI6MCwiYWNyIjoiMSIsImFjcnMiOlsicDEiXSwiYWlvIjoiQVpRQWEvOFpBQUFBS0hMRWl3Um9makJYcWhYTHEvUnZjZkpWSCtoTTZCMDVFdWhVYzlzSWVIeXdZZ2kzekVnbWxRK2srcVU2Zm56V1J4eU9BMEpYTXpTSG5tdVlvK2ZNTjBRS3ZGcmJoaGdldkJYbnVzL1lOcG9BclJnelB6UTRxU0w3dkR4T1NCbzdiSWdFMzlIZytqcXJ1S3BqTGx5ejkzS2NpM29NbjI5MDNuT3ZtUWk0M0VISHVZZ0JCd210ZHI4aER6czQ2bC9yIiwiYW1yIjpbInB3ZCIsIm1mYSJdLCJhcHBfZGlzcGxheW5hbWUiOiJBcHBDb3N0YVNvbC1PdXRsb29rLVN5bmMiLCJhcHBpZCI6IjFjMzYyYWQ1LWZjMDMtNGE0MS1iMjg1LWNhNjI1ZGNmY2E4MSIsImFwcGlkYWNyIjoiMSIsImZhbWlseV9uYW1lIjoiQ29lbGxvIEJlbHRyw6FuIiwiZ2l2ZW5fbmFtZSI6Ikd1aWxsZXJtbyIsImlkdHlwIjoidXNlciIsImlwYWRkciI6IjE1Ny4xMDAuMTA4LjExIiwibmFtZSI6Ikd1aWxsZXJtbyBFLiBDb2VsbG8iLCJvaWQiOiI3NzFiY2ZmNi02ZjdkLTQ3NmItYTNhNS0yZjcwZjg4OGExYjUiLCJwbGF0ZiI6IjMiLCJwdWlkIjoiMTAwMzIwMDM3RDdGOEZBMiIsInJoIjoiMS5BVkFBeG9waHVVZ203VUc3VHdPODJVcDBrd01BQUFBQUFBQUF3QUFBQUFBQUFBQlFBQVJRQUEuIiwic2NwIjoiQ2FsZW5kYXJzLlJlYWRXcml0ZSBvcGVuaWQgcHJvZmlsZSBVc2VyLlJlYWQgZW1haWwiLCJzaWQiOiIwMDg5ZDE1OS0yOGVmLWYxZTAtNzk5OS0wNzhiNTUzZGZlMjYiLCJzaWduaW5fc3RhdGUiOlsia21zaSJdLCJzdWIiOiJydkhiNU5BRVJ5ZmxMSlFHQ3BIbDZSOURDTlJsZFRERXdQXzlPZjBqbHRnIiwidGVuYW50X3JlZ2lvbl9zY29wZSI6IlNBIiwidGlkIjoiYjk2MThhYzYtMjY0OC00MWVkLWJiNGYtMDNiY2Q5NGE3NDkzIiwidW5pcXVlX25hbWUiOiJnY29lbGxvQHRoYWxpYXZpY3RvcmlhLmNvbS5lYyIsInVwbiI6Imdjb2VsbG9AdGhhbGlhdmljdG9yaWEuY29tLmVjIiwidXRpIjoibjR4ckpzQUFYa0stdExqaVFRTUVBQSIsInZlciI6IjEuMCIsIndpZHMiOlsiYjc5ZmJmNGQtM2VmOS00Njg5LTgxNDMtNzZiMTk0ZTg1NTA5Il0sInhtc19mdGQiOiJyMk5ycmJmMS1CcGxieklnN1lvYzlmU3BQemk3VTlIeXh0cWlDUUdMRDhnQmRYTnpiM1YwYUMxa2MyMXoiLCJ4bXNfaWRyZWwiOiIxNCAxIiwieG1zX3N0Ijp7InN1YiI6IkYwZDRLREF0eHJOQnlnWmZoOENpVFZTcTdTYWlHTXJBcnRWV1JKMXdVbEUifSwieG1zX3RjZHQiOjE2Mjc2ODIzMTN9.m4a3U5B9j6CaD0rjBFBKQ1NlQFetdyzqqI3ZCVgPSF7oPNyojFL_nH5ulQJs6hydkdhyaZiSmiIgeFHgdT8sECJh5880ISTR7gyQYv6BWOlFEl3FXv2uRw5hNJEcyztSb-JRmuGPDOpeaJYoFuNmleZh75AVXeigcTrhvIYHqdZKUOSje5WR3hspE703S3Ac9yiDAEQcPZQd8FBOeDH_28Z7w_bsA-hdDgmLgOp_RugiGACu6gVmAU6YEswIQeQ5xBcz42dc6FzDgaEmZGYsS5AWnUXLYwAfdYrIE6AZ-fehXIYHadXqzpRAVsbHCUE9RxdHoec9ad2ALaa6ltNtfQ','1.AVAAxophuUgm7UG7TwO82Up0k9UqNhwD_EFKsoXKYl3PyoFQAARQAA.AgABAwEAAABVrSpeuWamRam2jAF1XRQEAwDs_wUA9P8kbc7_og9tBEQdWsk0G18Z5iOK7Tx2fRAJ86nZHM4UumMOkuD_cTr91_Bmy3RJ16MGo8ca7xlas9ntAdEGCtKUlNZiB7RNQ8EZdDv4YFVvxgLxz85qcxVaX2NyBiMNH6InRilDczihD1yMezvd4UVmocIVzspowR1WecHFRX8NBNt_-lqHb6EnOp1W4YrvHYfzaX7pS6H_lJOJBEOzo5p7hOo1CmihNVah-_AurwYlK9jX4fZ9b59FW0ySS8Dw5W3LHFiOoM2u9u4rvj2ZrPd4-6B6yGIc3mm24RD5FMPWPCsHTAPlkfCfyF-_FBoBl1HXFxete5GD60UWM_xAC9LYZGmWxMHY4oj9PMYMc7n-37OxGahvs_CNllcMW8r-FDsDpKuyNJ31eJsEd2qrg6LXPYleDyxGvXLVEWCru4caJEWnjZh-muQYsa2znqXnlagxYtqxfRY7xucrNPBT5jzMkmrfsV9havA4BZlyKBZBhybj7-PPz4BBdaweJxB45AdYKvia1Qb8YoJHdr8oHAqD_1WiFX9EUJhs4WbRr3bYXRD0zYDpsFv9jBFrLXilUZAPoJu6krUINsam_xP6W3Rgy0-IAcZx-OmttypKO-lIsmBLZ5tSh4nXGw_uukmiMJobVWVX4-NZ5q7EpKEDWT5h--sHV4AOT0jYYwLmQCcrezom2Abx5W72qCt3z5LApGLWmViaf4KnmTj94Pgrt6yXutudnLTsC6hzioGP8FyVJsHfDCBiWzG2a9Cx0lgSdtAqPyCp4OWAbJvhSQ2kJwcng2WFd8qwViILDeUWdpAwFwHtZCbdlGsS-QVmt8RwMLeVuZwy7T9BaP-iswScJO4EdhrAsuBrQaz5n4XwKZ0zs1u5GcKBWF3w3Wf9_ZV5cC6do29An5AU_DoQFVnd9KQ9YLY','2025-09-15 16:57:27','973971b9-3889-44f9-b496-ce1e53e99675','26bc628cdb369d7e7a259b8d9d447370','AQMkADAyZjVkMWU2LWY0ZTMtNDA0Yi1hYQExLWUwYWQ4MjRkNDNiNgBGAAADM6SdagXDXka9dwjpfWMPMAcAxbep3HYmykSNf77yTUHxwQAAAgEGAAAAxbep3HYmykSNf77yTUHxwQAAAoxEAAAA','c3925247-841e-41d7-be9a-30560a3b7a01','2025-09-17 15:39:56'),(3,'Admin','admin@thaliavictoria.com.ec','$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa','https://app.costasol.com.ec/ImagenesPerfil/68a79276ed803-profile-3.png','SAC',1,'2025-08-13 09:25:21','2025-09-05 15:23:34','CCAm6iWGVgItYukte6T52lBcLGzL1975',NULL,NULL,NULL,NULL,NULL,NULL,'7d49b608-347b-4308-954e-1edc4e1b6dee','2025-09-05 21:23:34');
/*!40000 ALTER TABLE `responsable` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respuesta_ctg`
--

LOCK TABLES `respuesta_ctg` WRITE;
/*!40000 ALTER TABLE `respuesta_ctg` DISABLE KEYS */;
INSERT INTO `respuesta_ctg` VALUES (11,7,7,2,'hola',NULL,'2025-07-23 11:49:54',0),(12,7,7,2,'En que lo puedo ayudar',NULL,'2025-07-23 11:53:21',0),(13,7,7,2,'hola',NULL,'2025-07-23 12:10:41',0),(14,7,7,2,'si',NULL,'2025-07-23 14:28:21',0),(15,7,7,2,'Hola',NULL,'2025-07-23 14:38:19',0),(16,7,7,2,'Que tal',NULL,'2025-07-23 14:44:58',0),(17,7,7,2,'Si',NULL,'2025-07-23 15:41:59',0),(18,7,7,2,'m',NULL,'2025-07-23 15:55:54',0),(19,7,7,2,'si',NULL,'2025-07-23 16:20:01',0),(21,7,7,2,'HOLA',NULL,'2025-07-24 10:49:24',0),(22,17,7,1,'hola',NULL,'2025-07-24 14:49:16',1),(23,17,NULL,1,'hola',NULL,'2025-07-24 15:10:23',0),(24,17,7,NULL,'necesito ayuda',NULL,'2025-07-24 15:13:43',1),(25,17,NULL,1,'claro dime',NULL,'2025-07-24 15:14:45',0),(26,7,NULL,2,'hola',NULL,'2025-07-24 15:47:48',0),(27,18,8,NULL,'Hola',NULL,'2025-07-24 16:09:43',1),(30,17,NULL,1,'holA',NULL,'2025-07-28 08:29:29',0),(31,7,NULL,2,'digame',NULL,'2025-07-28 08:33:29',0),(32,18,8,NULL,'Hol',NULL,'2025-07-28 08:39:16',1),(64,21,NULL,2,'Buenas',NULL,'2025-07-30 12:26:54',1),(78,21,8,NULL,'hola',NULL,'2025-08-07 08:25:59',1),(79,21,NULL,2,'como le va',NULL,'2025-08-07 08:26:21',1),(80,21,8,NULL,'digame',NULL,'2025-08-07 08:26:36',1),(81,21,NULL,2,'hola',NULL,'2025-08-07 15:33:14',1),(82,21,NULL,2,'vale respecto a su opinion no opino que sea oportuno la respuesta anonadada',NULL,'2025-08-07 16:04:58',1),(83,21,NULL,2,'hola',NULL,'2025-08-07 16:48:25',1),(84,21,NULL,2,'si',NULL,'2025-08-07 16:50:48',1),(85,21,8,NULL,'no',NULL,'2025-08-07 16:57:39',1),(86,21,NULL,2,'buenas',NULL,'2025-08-08 08:45:27',1),(87,21,8,NULL,'digame',NULL,'2025-08-08 08:46:05',1),(88,21,NULL,2,'el que',NULL,'2025-08-08 08:47:02',1),(89,21,8,NULL,'que cosa de que',NULL,'2025-08-08 08:48:46',1),(90,21,NULL,2,'no se dime tu',NULL,'2025-08-08 08:48:59',1),(91,21,8,NULL,'hola',NULL,'2025-08-08 10:49:27',1),(92,21,8,NULL,'hola',NULL,'2025-08-12 09:11:37',1),(93,21,NULL,2,'si','https://app.costasol.com.ec/ImagenesPQR_respuestas/689b4bb2c72a9-email-signature.png','2025-08-12 09:12:02',1),(94,21,8,NULL,'hola',NULL,'2025-08-12 09:14:08',1),(95,21,NULL,2,'diga',NULL,'2025-08-12 09:14:24',1),(96,21,8,NULL,'holaaaa',NULL,'2025-08-12 11:14:14',1),(97,21,NULL,2,'Diga',NULL,'2025-08-12 11:14:37',1),(98,21,8,NULL,'hola\r\n}',NULL,'2025-08-13 16:52:30',1),(99,21,NULL,2,'hola',NULL,'2025-08-13 16:52:47',1),(100,21,NULL,2,'hola',NULL,'2025-08-13 16:53:12',1),(101,21,NULL,2,'ohola',NULL,'2025-08-13 16:53:31',1),(102,21,8,NULL,'hola',NULL,'2025-08-15 08:54:37',1),(103,21,NULL,2,'hola',NULL,'2025-08-15 08:54:48',1),(104,21,8,NULL,'Diga',NULL,'2025-08-15 13:47:43',1),(105,21,8,NULL,'No estoy',NULL,'2025-08-15 13:47:50',1),(106,21,8,NULL,'Hola, dígame estoy muy cansado',NULL,'2025-08-15 14:00:42',1),(107,21,8,NULL,'Hola',NULL,'2025-08-15 15:04:05',1),(108,21,8,NULL,'Hola',NULL,'2025-08-15 15:04:30',1),(109,21,8,NULL,'Hola',NULL,'2025-08-15 15:04:39',1),(110,21,NULL,2,'hola',NULL,'2025-08-15 15:05:25',1),(111,21,NULL,2,'hola',NULL,'2025-08-15 15:05:33',1),(112,21,NULL,2,'hola',NULL,'2025-08-15 15:06:13',1),(113,21,NULL,2,'hola',NULL,'2025-08-15 15:07:09',1),(114,21,NULL,2,'hola',NULL,'2025-08-15 15:08:58',1),(115,21,8,NULL,'No gracias',NULL,'2025-08-15 15:19:22',1),(116,21,NULL,2,'de o que',NULL,'2025-08-15 15:19:49',1),(117,21,NULL,2,'sw',NULL,'2025-08-15 15:19:59',1),(118,21,8,NULL,'Hola',NULL,'2025-08-15 16:50:24',1),(119,21,8,NULL,'Hila',NULL,'2025-08-15 17:02:56',1),(120,21,8,NULL,'Dígame ayúdeme pues',NULL,'2025-08-15 17:03:38',1),(121,21,8,NULL,'Hola',NULL,'2025-08-15 17:03:46',1),(122,21,8,NULL,'Hablé pues',NULL,'2025-08-18 08:16:47',1),(123,21,NULL,2,'el que o que',NULL,'2025-08-18 08:17:15',1),(124,21,NULL,2,'diga',NULL,'2025-08-18 08:18:11',1),(125,21,8,NULL,'no digo',NULL,'2025-08-18 08:19:53',1),(126,21,NULL,2,'si diga',NULL,'2025-08-18 08:45:04',1),(127,21,NULL,2,'el que',NULL,'2025-08-18 08:46:37',1),(128,18,NULL,1,'hola',NULL,'2025-08-20 17:09:35',0),(129,18,NULL,1,'hola',NULL,'2025-08-20 17:09:47',0),(130,18,NULL,1,'la',NULL,'2025-08-20 17:09:47',0),(131,18,NULL,1,'hola',NULL,'2025-08-20 17:18:51',0),(132,18,NULL,1,'aaaaaa',NULL,'2025-08-20 17:19:11',0),(133,26,9,NULL,'Si','https://app.costasol.com.ec/ImagenesPQR_respuestas/68ac81dc5d75b-BigmodelPoster.png','2025-08-25 09:31:40',1),(134,26,NULL,2,'hols','https://app.costasol.com.ec/ImagenesPQR_respuestas/68ac8294e5335-BigmodelPoster.png','2025-08-25 09:34:44',1),(135,26,NULL,2,'no sale','https://app.costasol.com.ec/ImagenesPQR_respuestas/68ac82bf6a686-BigmodelPoster.png','2025-08-25 09:35:27',1),(136,28,9,NULL,'si','https://app.costasol.com.ec/ImagenesCTG_respuestas/68ac8657d6faa-BigmodelPoster.png','2025-08-25 09:50:47',0),(137,29,9,NULL,'WWW','https://app.costasol.com.ec/ImagenesCTG_respuestas/68ac87cb077c3-BigmodelPoster.png','2025-08-25 09:56:59',0),(138,30,9,NULL,'<<<<<<<<<<','https://app.costasol.com.ec/ImagenesCTG_respuestas/68ac88c5f3994-BigmodelPoster.png','2025-08-25 10:01:09',1),(139,30,9,NULL,'<<<<<<<<<<','https://app.costasol.com.ec/ImagenesCTG_respuestas/68ac88c6409a5-BigmodelPoster.png','2025-08-25 10:01:10',1),(140,30,9,NULL,'apoco si',NULL,'2025-08-25 10:08:01',1),(141,26,9,NULL,'diga',NULL,'2025-08-25 10:09:21',1),(142,26,NULL,2,'mire','https://app.costasol.com.ec/ImagenesCTG_respuestas/68ac8abfbab71-BigmodelPoster.png','2025-08-25 10:09:35',1),(143,21,8,NULL,'Si o no','https://app.costasol.com.ec/ImagenesCTG_respuestas/68acceb979a8d-IMG-20250825-WA0002.jpg','2025-08-25 14:59:37',1),(144,26,9,NULL,'hola',NULL,'2025-08-26 07:39:02',1),(145,26,9,NULL,'hola',NULL,'2025-08-26 07:39:18',1),(146,26,9,NULL,'hla',NULL,'2025-08-26 07:39:34',1),(147,26,9,NULL,'hola',NULL,'2025-08-26 07:39:42',1),(148,21,NULL,2,'Hola',NULL,'2025-08-26 07:40:23',1),(149,21,NULL,2,'Hola',NULL,'2025-08-26 07:41:15',1),(150,21,NULL,2,'Hola',NULL,'2025-08-26 07:42:02',1),(151,21,NULL,2,'Hola',NULL,'2025-08-26 07:43:17',1),(152,21,NULL,2,'Hola',NULL,'2025-08-26 07:47:40',1),(153,26,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 08:43:53',1),(154,26,9,NULL,'eeeeeeeeeeee',NULL,'2025-08-26 08:45:10',1),(155,26,9,NULL,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 08:45:18',1),(156,26,9,NULL,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 09:53:17',1),(157,26,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 09:59:08',1),(158,26,9,NULL,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 10:12:00',1),(159,26,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 10:27:25',1),(160,26,9,NULL,'Aaa',NULL,'2025-08-26 10:37:56',1),(161,26,9,NULL,'Hola',NULL,'2025-08-26 11:02:39',1),(162,26,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',NULL,'2025-08-26 11:02:57',1),(163,26,NULL,2,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa','https://app.costasol.com.ec/ImagenesCTG_respuestas/68adf533e1add-BigmodelPoster.png','2025-08-26 11:56:03',1),(164,21,8,NULL,'si','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af41d14d7b3-PROYECTO ADMINISTRACIÓN CAPITULO 1.pdf','2025-08-27 11:35:13',1),(165,21,8,NULL,'audiio','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af44aa4e794-','2025-08-27 11:47:22',1),(166,21,8,NULL,'sql+','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af44dc6ffe3-','2025-08-27 11:48:12',1),(167,21,8,NULL,'aaaa','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af455d8e732-','2025-08-27 11:50:21',1),(168,21,8,NULL,'si','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af457a52197-','2025-08-27 11:50:50',1),(169,21,8,NULL,'eee','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af469cac603-','2025-08-27 11:55:40',1),(170,21,8,NULL,'sql+','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af46b07d35f-','2025-08-27 11:56:00',1),(171,21,8,NULL,'sql+','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af46b114015-','2025-08-27 11:56:01',1),(172,21,8,NULL,'sql','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af46f53d50f-','2025-08-27 11:57:09',1),(173,21,8,NULL,'hola','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af48021a506-PROYECTOADMINISTRACINCAPITULO1.pdf','2025-08-27 12:01:38',1),(174,21,8,NULL,'si','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af480d660b1-FORMATO_PROYECTOCONTABILIDADGENERAL.docx','2025-08-27 12:01:49',1),(175,21,8,NULL,'sqlk','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af482d344c1-portalao_appcostasol.sql','2025-08-27 12:02:21',1),(176,21,8,NULL,'img','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af484c66349-BigmodelPoster.png','2025-08-27 12:02:52',1),(177,21,8,NULL,'e','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af48598ca11-WhatsAppAudio2025-08-27at85151AM1.ogg','2025-08-27 12:03:05',1),(178,21,8,NULL,'hola','https://app.costasol.com.ec/ImagenesCTG_respuestas/68af48bb17044-65562-515098354_small.mp4','2025-08-27 12:04:43',1),(179,21,8,NULL,'eee','https://app.costasol.com.ec/ImagenesCTG_respuestas/BigmodelPoster.png','2025-08-27 12:11:34',1),(180,21,8,NULL,'ee','https://app.costasol.com.ec/ImagenesCTG_respuestas/BigmodelPoster%281%29.png','2025-08-28 07:22:02',1),(181,21,8,NULL,'sql','https://app.costasol.com.ec/ImagenesCTG_respuestas/portalao_appcostasol.sql','2025-08-28 07:22:23',1),(182,21,8,NULL,'eeee','https://app.costasol.com.ec/ImagenesCTG_respuestas/65562-515098354_small.mp4','2025-08-28 07:24:00',1),(183,21,8,NULL,'lll','https://app.costasol.com.ec/ImagenesCTG_respuestas/WhatsAppAudio2025-08-27at85151AM1.ogg','2025-08-28 07:25:14',1),(184,21,8,NULL,'excel','https://app.costasol.com.ec/ImagenesCTG_respuestas/REP-SAC-03ReportedeAtencinaContingenciasajunio2025__.xlsx','2025-08-28 08:03:38',1),(185,21,8,NULL,'svg','https://app.costasol.com.ec/ImagenesCTG_respuestas/user-tie.svg','2025-08-28 08:04:20',1),(186,21,8,NULL,'php','https://app.costasol.com.ec/ImagenesCTG_respuestas/login_front.php','2025-08-28 08:04:56',1),(187,21,8,NULL,'exe','https://app.costasol.com.ec/ImagenesCTG_respuestas/VSCodeUserSetup-x64-11021.exe','2025-08-28 08:07:36',1),(188,21,8,NULL,'wasa','https://app.costasol.com.ec/ImagenesCTG_respuestas/WhatsAppAudio2025-08-27at85151AM.mp4','2025-08-28 08:20:38',1),(189,21,8,NULL,'mp3','https://app.costasol.com.ec/ImagenesCTG_respuestas/WhatsAppAudio2025-08-27at85151AM1.mp3','2025-08-28 08:23:14',1),(190,21,8,NULL,'ssss','https://app.costasol.com.ec/ImagenesCTG_respuestas/WhatsAppAudio2025-08-27at85151AM1%281%29.mp3','2025-08-28 10:29:49',1),(191,21,8,NULL,'ogg','https://app.costasol.com.ec/ImagenesCTG_respuestas/WhatsAppAudio2025-08-27at85151AM1%281%29.ogg','2025-08-28 10:30:00',1),(192,32,8,NULL,'Hhh',NULL,'2025-09-15 10:28:36',0);
/*!40000 ALTER TABLE `respuesta_ctg` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (2,1,'Guillermo','Coello','0922797790','593982033045','gcoellooo@costasol.com.ec','$2y$10$RGAlN206FkvrivRqB86qE.pahU4Q7ThD3dWKgndwpJFbb75WN2t/.',1,'https://app.costasol.com.ec/FotoPerfil/FotoUsuarioGC.jpg','2025-06-10 10:42:58','BYFPee8yS1zM59X/7YPMSa1ifwJj3qA0',NULL,'2025-08-26 13:16:29'),(3,2,'Guillermo2','Coello2',NULL,NULL,'dr.gecb21@hotmail.com','$2y$10$MdNXTfLD2h3lbelU4QdN4eVssugUOrpDOrq5Yxx9U48ok8Finrh/u',0,NULL,'2025-06-10 11:05:44',NULL,NULL,NULL),(4,1,'Carlos','Pablo',NULL,NULL,'carlospablo@thaliavictoria.com.ec','$2y$10$S.HaX.E8Y0kc2hiGc1NJLus7h760yWOvh6ZqNcUSCx7T73iGRvVHi',0,NULL,'2025-07-03 13:41:37','HfiJ4S293HOsH/0XDUdnv6mq0c/0YtA3',NULL,NULL),(5,1,'Rafael','Romero',NULL,NULL,'rromero@thaliavictoria.com.ec','$2y$10$uqExlkQ7Xw6xfgzbQyqefOJmFOGrL4Qt.iMnUliWzb5iH9xVBZIou',0,NULL,'2025-07-14 16:11:24',NULL,NULL,NULL),(6,1,'Jonathan','Quijano',NULL,NULL,'jquijano@thaliavictoria.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-07-14 16:12:49',NULL,NULL,NULL),(7,1,'Joffre','Holguin',NULL,NULL,'joffreholguin19@gmail.com','$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa',1,'https://cdn-icons-png.flaticon.com/512/9187/9187532.png','2025-07-22 10:50:30','g9lrsOmr2BybWgrqIdB6k3dCmYOukdfX',NULL,'2025-08-22 20:30:44'),(8,1,'Prueba','CTG','0912345678','0986940124','danielalarcon@gmail.com','$2y$10$KxKCaEiyWPjABgwuzODM.eWQlVCmLGgqISjp.5dkCDt5UYowRSGHC',1,'https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_640.png','2025-07-24 16:06:19','Ew7Y2X4pvv/ZeJOLm9PByh5T5ztOdpq0','494528dd-7ee5-4f51-a7af-c4a4f01cb231','2025-09-17 15:38:58'),(9,1,'Felipe','Pilligua',NULL,NULL,'fepilligua@gmail.com','$2y$10$reWiHb52f7EPZNo98Q3kz.H0Df6eFFB6FJrgll3nGywKhCmgl/hvC',1,'https://cdn-icons-png.flaticon.com/512/219/219983.png','2025-07-28 08:42:37',NULL,NULL,'2025-09-15 20:58:00'),(10,1,'Jose','Tenesaca',NULL,NULL,'josesaca@gmail.com','$2y$10$8LEm..7rJ3ii/7QWAivJOuHJMjh3lQ4n/qEaqTI66Wu3YPKLcpble',0,NULL,'2025-08-05 13:25:22','iCHj2yX2GP5NG9lt7+NCkN/wPFr7tH2D',NULL,'2025-08-05 18:32:06'),(11,1,'Martin','Mera',NULL,NULL,'martin@gmial.com','$2y$10$UtKsBWDz.Hdf5DPHSlmV5etztt5mez967KwQccui431EB5woBL4au',0,'https://cdn-icons-png.flaticon.com/512/219/219983.png','2025-08-06 08:16:43','BrqmYT8yOa/1kGwyhEiSHb74+1d+X8nQ',NULL,'2025-09-02 16:25:49'),(12,1,'prueba','1',NULL,NULL,'prueba@prueba.com','$2y$10$447WYbjzXhELxQuweQbcEuNgS4HiO.PxkyqBbm7zQ4XC.evK9FpoS',0,NULL,'2025-08-13 12:37:40','mc5PPSjS365g9XGa8+xrmQWPqdUg8t8s',NULL,'2025-08-13 17:37:52'),(13,1,'prueba','2',NULL,NULL,'prueba2@prueba.com','$2y$10$K2tKUN8/SS/o8Nhr7wnG3uTkzy5FdI3H8.kze3wAxd5w7a6tA0OZO',0,NULL,'2025-08-13 12:39:18',NULL,NULL,NULL),(14,1,'Geovanny','Herrera',NULL,NULL,'gherrera@costasol.com','$2y$10$J0CPBMknCZLdKYzSnjzPSe.lxh9ipybg8K1VWgliZxSj//u4tL8OK',0,NULL,'2025-07-23 07:56:38',NULL,NULL,NULL),(15,1,'Irma','Perez',NULL,NULL,'iperez@thaliavictoria.com.ec','$2y$10$MOGLPMPM2/JrPZt/KFh/oeoqV8Xg.U2S6RkLqPcya30gHGy43lXui',0,NULL,'2025-08-18 08:25:10',NULL,NULL,NULL),(16,2,'Guillermo','Coello',NULL,NULL,'gcoello1@thaliavictoria.com.ec','$2y$10$BJWf37cacEKopZ2gAwO9.e55tu7Azw19MN7KzHSDJpHVHypylqLZm',0,NULL,'2025-08-26 07:25:17','Q29fti9M9+ZdNlJhwrTOvN+BBv/6KkQf','c3925247-841e-41d7-be9a-30560a3b7a01','2025-09-03 15:27:34'),(17,1,'Demo','Costasol',NULL,NULL,'demo@costasol.com.ec','$2y$10$p0tLwdW04EN1VKNmMvRz9.TlBmNaxIf0jkTzV7kvSBmEM0xOE4zfu',0,NULL,'2025-09-05 07:56:15',NULL,NULL,NULL),(18,1,'Alejandra','Ramos',NULL,NULL,'jramos@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:13:14',NULL,NULL,NULL),(22,1,'Jonathan Granda','',NULL,NULL,'jgranda@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:18:48',NULL,NULL,NULL),(23,1,'Maggy Latorre','',NULL,NULL,'mlatorre@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:18:48',NULL,NULL,NULL),(24,1,'Miguel Loor','',NULL,NULL,'mloor@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:20:01',NULL,NULL,NULL),(25,1,'Raúl Tacle','',NULL,NULL,'rtacle@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:20:01',NULL,NULL,NULL),(26,1,' Ricardo Verdesoto','',NULL,NULL,'rverdesoto@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:21:28',NULL,NULL,NULL),(27,1,'Anthony Garcia','',NULL,NULL,'agarciat@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:21:28',NULL,NULL,NULL),(28,1,'Cecilia Robalino','',NULL,NULL,'crobalino@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:21:49',NULL,NULL,NULL),(29,1,'SAC','SAC',NULL,NULL,'servicioalcliente@thaliavictoria.com.ec','$2y$10$NWp3MITyecCEPZ/./Y5SrO80qSgGKNTi2cts.qDArzHQuQmWzXlt6',0,NULL,'2025-09-12 12:54:49',NULL,NULL,NULL),(31,1,'prueba','228',NULL,NULL,'p@gmail.com','$2y$10$JVG23uW0P9xsuHh.UyX92Oa/1qq2vpx29wM/ip2IpMGFVWWBMCtga',0,NULL,'2025-09-15 09:49:28',NULL,NULL,NULL);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

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

-- Dump completed on 2025-09-17 11:22:19
