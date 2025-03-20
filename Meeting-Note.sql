/*!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.18-MariaDB, for linux-systemd (x86_64)
--
-- Host: localhost    Database: admin_meeting-notes
-- ------------------------------------------------------
-- Server version	10.6.18-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `meetings`
--

DROP TABLE IF EXISTS `meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `meeting_no` varchar(50) NOT NULL,
  `details` text NOT NULL,
  `recorded_at` datetime NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `department` varchar(50) DEFAULT NULL,
  `meeting_date` date NOT NULL,
  `meeting_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `committee_present` text DEFAULT NULL,
  `committee_absent` text DEFAULT NULL,
  `attendees` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_date` (`date`),
  CONSTRAINT `meetings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meetings`
--

LOCK TABLES `meetings` WRITE;
/*!40000 ALTER TABLE `meetings` DISABLE KEYS */;
INSERT INTO `meetings` VALUES (13,6,'ประชุมประจำเดือน','2','หกดหกดหกดหกด','2025-03-20 13:36:55','','0000-00-00','2025-03-20 06:32:31','2025-03-20 06:36:55','IT','2025-03-20','15:00:00','ห้องประชุมชั้น 4','[\"\\u0e2b\\u0e01\\u0e14\\u0e2b\\u0e14\\u0e2b\\u0e01\\u0e14\\u0e01\\u0e2b\\u0e14\",\"\\u0e2b\\u0e01\\u0e14\\u0e2b\\u0e01\\u0e14\",\"\\u0e2b\\u0e01\\u0e14\\u0e2b\\u0e01\\u0e14\"]','[\"\\u0e2b\\u0e01\\u0e14\\u0e2b\\u0e01\\u0e14\",\"\\u0e2b\\u0e01\\u0e14\\u0e2b\\u0e01\\u0e14\",\"\\u0e2b\\u0e01\\u0e14\\u0e2b\\u0e01\\u0e14\"]','[\"\\u0e2b\\u0e01\\u0e14\\u0e2b\\u0e01\\u0e14\",\"\\u0e2b\\u0e01\\u0e14\\u0e2b\\u0e01\\u0e14\",\"\\u0e2b\\u0e01\\u0e14\\u0e2b\\u0e01\\u0e14\"]');
/*!40000 ALTER TABLE `meetings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `division` varchar(50) NOT NULL,
  `role` enum('user','admin','superadmin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (6,'devmeeting','ymd','devit','it@youngmeedee.co.th','$2y$10$4Zlr3ucMK9Q6cQaLbhyHE.CrDItMUL0JPF8hLy4WBH0Deat.dy8GO','IT','IT','IT','superadmin','2025-03-20 04:51:45','2025-03-20 04:51:45'),(7,'จักรสิน','เติมสมบัติเจริญ','itmanager','jaksin.t@youngmeedee.co.th','$2y$10$b11B85agE6RUkgdyM8Z4COGAVfVq9XF4k7zkWjaBNnrOn.qd2OvR6','IT MANAGER','IT','IT','user','2025-03-20 04:59:42','2025-03-20 04:59:42'),(8,'test1','ymd','test1','pdpacenter@youngmeedee.co.th','$2y$10$pRJLh4UPmvu.EjLMwmVhGOBSTnkjZYAphDdy..wOOBsYvtmdL16yO','HR','HR','HR','user','2025-03-20 05:02:17','2025-03-20 05:02:17');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'admin_meeting-notes'
--

--
-- Dumping routines for database 'admin_meeting-notes'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-20 14:02:25
