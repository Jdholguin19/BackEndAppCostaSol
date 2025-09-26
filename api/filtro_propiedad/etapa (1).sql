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
) ENGINE=InnoDB AUTO_INCREMENT=808 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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

-- Dump completed on 2025-09-25 16:15:30
