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
-- Table structure for table `gantt_cross_project_links`
--

DROP TABLE IF EXISTS `gantt_cross_project_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gantt_cross_project_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_task_id` int(11) NOT NULL,
  `source_project_id` int(11) NOT NULL,
  `target_task_id` int(11) NOT NULL,
  `target_project_id` int(11) NOT NULL,
  `type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cross_link_source_task` (`source_task_id`),
  KEY `fk_cross_link_target_task` (`target_task_id`),
  KEY `fk_cross_link_source_project` (`source_project_id`),
  KEY `fk_cross_link_target_project` (`target_project_id`),
  CONSTRAINT `fk_cross_link_source_project` FOREIGN KEY (`source_project_id`) REFERENCES `gantt_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cross_link_source_task` FOREIGN KEY (`source_task_id`) REFERENCES `gantt_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cross_link_target_project` FOREIGN KEY (`target_project_id`) REFERENCES `gantt_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cross_link_target_task` FOREIGN KEY (`target_task_id`) REFERENCES `gantt_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gantt_cross_project_links`
--

LOCK TABLES `gantt_cross_project_links` WRITE;
/*!40000 ALTER TABLE `gantt_cross_project_links` DISABLE KEYS */;
INSERT INTO `gantt_cross_project_links` VALUES (2,8,2,6,1,'3');
/*!40000 ALTER TABLE `gantt_cross_project_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gantt_links`
--

DROP TABLE IF EXISTS `gantt_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gantt_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` int(11) NOT NULL,
  `target` int(11) NOT NULL,
  `type` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_gantt_links_source` (`source`),
  KEY `fk_gantt_links_target` (`target`),
  CONSTRAINT `fk_gantt_links_source` FOREIGN KEY (`source`) REFERENCES `gantt_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_gantt_links_target` FOREIGN KEY (`target`) REFERENCES `gantt_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gantt_links`
--

LOCK TABLES `gantt_links` WRITE;
/*!40000 ALTER TABLE `gantt_links` DISABLE KEYS */;
INSERT INTO `gantt_links` VALUES (3,10,8,'0'),(6,10,12,'1'),(7,9,11,'1'),(8,11,14,'0'),(9,13,12,'1'),(10,13,12,'3');
/*!40000 ALTER TABLE `gantt_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gantt_projects`
--

DROP TABLE IF EXISTS `gantt_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gantt_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gantt_projects`
--

LOCK TABLES `gantt_projects` WRITE;
/*!40000 ALTER TABLE `gantt_projects` DISABLE KEYS */;
INSERT INTO `gantt_projects` VALUES (1,'Proyecto de Ejemplo'),(2,'Proyecto CostaSOl'),(3,'Prueba'),(4,'Proyectos Joffre');
/*!40000 ALTER TABLE `gantt_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gantt_tasks`
--

DROP TABLE IF EXISTS `gantt_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gantt_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL DEFAULT '0',
  `text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `duration` int(11) NOT NULL,
  `progress` float NOT NULL,
  `parent` int(11) NOT NULL,
  `sortorder` int(11) NOT NULL,
  `open` tinyint(1) NOT NULL DEFAULT '1',
  `owners` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3498db',
  PRIMARY KEY (`id`),
  KEY `idx_project_id` (`project_id`),
  CONSTRAINT `fk_gantt_tasks_project_id` FOREIGN KEY (`project_id`) REFERENCES `gantt_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gantt_tasks`
--

LOCK TABLES `gantt_tasks` WRITE;
/*!40000 ALTER TABLE `gantt_tasks` DISABLE KEYS */;
INSERT INTO `gantt_tasks` VALUES (4,1,'Fase 2: Desarrollo','1901-11-30',11,0,0,40,1,'','#3498db'),(6,1,'Implementar Módulo B','1901-11-30',11,0,4,60,1,'','#3498db'),(8,2,'Pruba','1901-12-04',3,0,0,10,1,'2','#76db33'),(9,2,'New task','1901-12-03',3,0.6,0,10,1,'','#3498db'),(10,2,'Nueva Tarea','1901-12-02',1,0,8,10,1,'4','#c81919'),(11,2,'aaaa','1901-12-02',1,0.571429,10,10,1,'8','#3498db'),(12,2,'Nueva Tarea','1901-11-29',1,0,11,10,1,'','#3498db'),(13,2,'Nueva Tarea','1901-11-29',1,0,0,10,1,'','#3498db'),(14,2,'1111','1901-12-06',10,0.4,10,0,0,'3','#6fc38c'),(18,3,'Nueva Tarea','2025-09-16',7,0.21039,0,0,0,'','#3498db'),(19,3,'Nueva Tarea','2025-09-18',14,0,18,10,1,'','#3498db'),(20,3,'Nueva Tarea','2025-09-17',13,0,18,0,0,'','#3498db'),(21,3,'Nueva Tarea','2025-09-24',10,0,0,10,1,'8','#3498db'),(22,3,'Nueva Tarea','2025-09-24',11,0,21,0,0,'','#3498db'),(23,4,'App CostaSol','2025-09-24',91,0,0,10,1,'7','#1de010'),(24,4,'Gantt','2025-09-24',91,0,0,10,1,'7','#3498db'),(25,4,'Kiss Flow','2025-09-24',91,0,0,10,1,'7','#9a12d9'),(26,4,'BDU - KF, H, P, PA','2025-09-24',91,0,0,0,0,'7','#e68414'),(29,4,'Diseño','2025-09-24',91,0,24,10,1,'','#3498db'),(30,4,'Funciones','2025-09-24',91,0,24,0,0,'','#3498db'),(31,4,'Nuevas Funciones','2025-09-24',1,0,30,10,1,'','#3498db'),(32,4,'Fix','2025-09-24',91,0,30,10,1,'','#3498db'),(33,4,'Fix al grid timeline - scorll','2025-09-24',1,1,32,10,1,'','#3498db'),(34,4,'New Desing ( scale_unit  ) -- Project','2025-09-24',1,0,29,10,1,'','#3498db'),(35,4,'Nueva función: Agragamos la visualizacion del gantt scale por semanas y días','2025-09-24',1,1,31,10,1,'','#3498db');
/*!40000 ALTER TABLE `gantt_tasks` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=354 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (2,1,'Guillermo','Coello','0922797790','593982033045','gcoellooo@costasol.com.ec','$2y$10$RGAlN206FkvrivRqB86qE.pahU4Q7ThD3dWKgndwpJFbb75WN2t/.',1,'https://app.costasol.com.ec/FotoPerfil/FotoUsuarioGC.jpg','2025-06-10 10:42:58','BYFPee8yS1zM59X/7YPMSa1ifwJj3qA0',NULL,'2025-08-26 13:16:29'),(3,2,'Guillermo2','Coello2',NULL,NULL,'dr.gecb21@hotmail.com','$2y$10$MdNXTfLD2h3lbelU4QdN4eVssugUOrpDOrq5Yxx9U48ok8Finrh/u',0,NULL,'2025-06-10 11:05:44',NULL,NULL,NULL),(4,1,'Carlos','Pablo',NULL,NULL,'carlospablo@thaliavictoria.com.ec','$2y$10$S.HaX.E8Y0kc2hiGc1NJLus7h760yWOvh6ZqNcUSCx7T73iGRvVHi',0,NULL,'2025-07-03 13:41:37','HfiJ4S293HOsH/0XDUdnv6mq0c/0YtA3',NULL,NULL),(5,1,'Rafael','Romero',NULL,NULL,'rromero@thaliavictoria.com.ec','$2y$10$uqExlkQ7Xw6xfgzbQyqefOJmFOGrL4Qt.iMnUliWzb5iH9xVBZIou',0,NULL,'2025-07-14 16:11:24',NULL,NULL,NULL),(6,1,'Jonathan','Quijano',NULL,NULL,'jquijano@thaliavictoria.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-07-14 16:12:49',NULL,NULL,NULL),(7,1,'Joffre','Holguin',NULL,NULL,'joffreholguin19@gmail.com','$2y$10$buAboItJmIjG1j8Zn5y/Ou4LDVy5xrVYm4P1.6KebUpCsXCteoJXa',1,'https://cdn-icons-png.flaticon.com/512/9187/9187532.png','2025-07-22 10:50:30','g9lrsOmr2BybWgrqIdB6k3dCmYOukdfX',NULL,'2025-08-22 20:30:44'),(8,1,'Prueba','CTG','0955538400','0986940124','danielalarcon@gmail.com','$2y$10$KxKCaEiyWPjABgwuzODM.eWQlVCmLGgqISjp.5dkCDt5UYowRSGHC',1,'https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_640.png','2025-07-24 16:06:19',NULL,NULL,'2025-09-17 20:46:35'),(9,1,'Prueba','Acabado','0912345678',NULL,'fepilligua@gmail.com','$2y$10$reWiHb52f7EPZNo98Q3kz.H0Df6eFFB6FJrgll3nGywKhCmgl/hvC',1,'https://cdn-icons-png.flaticon.com/512/219/219983.png','2025-07-28 08:42:37',NULL,'494528dd-7ee5-4f51-a7af-c4a4f01cb231','2025-09-18 15:51:15'),(10,1,'Jose','Tenesaca',NULL,NULL,'josesaca@gmail.com','$2y$10$8LEm..7rJ3ii/7QWAivJOuHJMjh3lQ4n/qEaqTI66Wu3YPKLcpble',0,NULL,'2025-08-05 13:25:22','iCHj2yX2GP5NG9lt7+NCkN/wPFr7tH2D',NULL,'2025-08-05 18:32:06'),(11,1,'Martin','Mera',NULL,NULL,'martin@gmial.com','$2y$10$UtKsBWDz.Hdf5DPHSlmV5etztt5mez967KwQccui431EB5woBL4au',0,'https://cdn-icons-png.flaticon.com/512/219/219983.png','2025-08-06 08:16:43','BrqmYT8yOa/1kGwyhEiSHb74+1d+X8nQ',NULL,'2025-09-02 16:25:49'),(12,1,'prueba','1',NULL,NULL,'prueba@prueba.com','$2y$10$447WYbjzXhELxQuweQbcEuNgS4HiO.PxkyqBbm7zQ4XC.evK9FpoS',0,NULL,'2025-08-13 12:37:40','mc5PPSjS365g9XGa8+xrmQWPqdUg8t8s',NULL,'2025-08-13 17:37:52'),(13,1,'prueba','2',NULL,NULL,'prueba2@prueba.com','$2y$10$K2tKUN8/SS/o8Nhr7wnG3uTkzy5FdI3H8.kze3wAxd5w7a6tA0OZO',0,NULL,'2025-08-13 12:39:18',NULL,NULL,NULL),(14,1,'Geovanny','Herrera',NULL,NULL,'gherrera@costasol.com','$2y$10$J0CPBMknCZLdKYzSnjzPSe.lxh9ipybg8K1VWgliZxSj//u4tL8OK',0,NULL,'2025-07-23 07:56:38',NULL,NULL,NULL),(15,1,'Irma','Perez',NULL,NULL,'iperez@thaliavictoria.com.ec','$2y$10$MOGLPMPM2/JrPZt/KFh/oeoqV8Xg.U2S6RkLqPcya30gHGy43lXui',0,NULL,'2025-08-18 08:25:10',NULL,NULL,NULL),(16,2,'Guillermo','Coello',NULL,NULL,'gcoello1@thaliavictoria.com.ec','$2y$10$BJWf37cacEKopZ2gAwO9.e55tu7Azw19MN7KzHSDJpHVHypylqLZm',0,NULL,'2025-08-26 07:25:17','Q29fti9M9+ZdNlJhwrTOvN+BBv/6KkQf','c3925247-841e-41d7-be9a-30560a3b7a01','2025-09-03 15:27:34'),(17,1,'Demo','Costasol',NULL,NULL,'demo@costasol.com.ec','$2y$10$p0tLwdW04EN1VKNmMvRz9.TlBmNaxIf0jkTzV7kvSBmEM0xOE4zfu',0,NULL,'2025-09-05 07:56:15',NULL,NULL,NULL),(18,1,'Alejandra','Ramos',NULL,NULL,'jramos@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:13:14',NULL,NULL,NULL),(22,1,'Jonathan Granda','',NULL,NULL,'jgranda@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:18:48',NULL,NULL,NULL),(23,1,'Maggy Latorre','',NULL,NULL,'mlatorre@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:18:48',NULL,NULL,NULL),(24,1,'Miguel Loor','',NULL,NULL,'mloor@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:20:01',NULL,NULL,NULL),(25,1,'Raúl Tacle','',NULL,NULL,'rtacle@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:20:01',NULL,NULL,NULL),(26,1,' Ricardo Verdesoto','',NULL,NULL,'rverdesoto@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:21:28',NULL,NULL,NULL),(27,1,'Anthony Garcia','',NULL,NULL,'agarciat@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:21:28',NULL,NULL,NULL),(28,1,'Cecilia Robalino','',NULL,NULL,'crobalino@costasol.com.ec','$2y$10$AEHyeOLBMhOPt0PhJdpy8e8WWyMM7jEbKg6MQiwfYjCH7ZHnp/at2',0,NULL,'2025-09-09 16:21:49',NULL,NULL,NULL),(29,1,'SAC','SAC',NULL,NULL,'servicioalcliente@thaliavictoria.com.ec','$2y$10$NWp3MITyecCEPZ/./Y5SrO80qSgGKNTi2cts.qDArzHQuQmWzXlt6',0,NULL,'2025-09-12 12:54:49',NULL,NULL,NULL),(31,1,'prueba','228',NULL,NULL,'p@gmail.com','$2y$10$JVG23uW0P9xsuHh.UyX92Oa/1qq2vpx29wM/ip2IpMGFVWWBMCtga',0,NULL,'2025-09-15 09:49:28',NULL,NULL,NULL),(353,1,'prueba','visita',NULL,NULL,'pvisita@gmail.com','$2y$10$pDXYa7LTUoyfP0FZwauClufmFIrLVPOxwhcymgeXAVGgYnaBohRTS',0,NULL,'2025-09-23 14:59:54',NULL,NULL,NULL);
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

-- Dump completed on 2025-09-24 11:52:49
