-- MySQL dump 10.13  Distrib 8.0.29, for Linux (x86_64)
--
-- Host: localhost    Database: db_virtuelle
-- ------------------------------------------------------
-- Server version	8.0.29

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account_commission_operations`
--

DROP TABLE IF EXISTS `account_commission_operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_commission_operations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `solde_avant` int DEFAULT NULL,
  `montant` int DEFAULT NULL,
  `solde_apres` int DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_commission_operations`
--

LOCK TABLES `account_commission_operations` WRITE;
/*!40000 ALTER TABLE `account_commission_operations` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_commission_operations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_commissions`
--

DROP TABLE IF EXISTS `account_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_commissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `solde` int DEFAULT NULL,
  `partenaire_id` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_commissions`
--

LOCK TABLES `account_commissions` WRITE;
/*!40000 ALTER TABLE `account_commissions` DISABLE KEYS */;
INSERT INTO `account_commissions` VALUES (1,0,1,0,'2023-01-20 17:12:35','2023-01-20 17:12:35');
/*!40000 ALTER TABLE `account_commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_distribution_operations`
--

DROP TABLE IF EXISTS `account_distribution_operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_distribution_operations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `solde_avant` int DEFAULT NULL,
  `montant` int DEFAULT NULL,
  `solde_apres` int DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_distribution_operations`
--

LOCK TABLES `account_distribution_operations` WRITE;
/*!40000 ALTER TABLE `account_distribution_operations` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_distribution_operations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_distributions`
--

DROP TABLE IF EXISTS `account_distributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_distributions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `partenaire_id` int DEFAULT NULL,
  `solde` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_distributions`
--

LOCK TABLES `account_distributions` WRITE;
/*!40000 ALTER TABLE `account_distributions` DISABLE KEYS */;
INSERT INTO `account_distributions` VALUES (1,1,0,0,'2023-01-20 17:12:35','2023-01-20 17:12:35');
/*!40000 ALTER TABLE `account_distributions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carte_physiques`
--

DROP TABLE IF EXISTS `carte_physiques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carte_physiques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `serie` int DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `gamme_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carte_physiques`
--

LOCK TABLES `carte_physiques` WRITE;
/*!40000 ALTER TABLE `carte_physiques` DISABLE KEYS */;
INSERT INTO `carte_physiques` VALUES (1,1,'0012345678',1,1,0,0,'2022-11-10 10:58:28','2022-11-10 14:06:30'),(2,2,'0012345679',1,1,1,0,'2022-11-10 10:58:28','2022-11-10 10:58:28'),(3,3,'0012345680',1,1,1,0,'2022-11-10 10:58:28','2022-11-10 10:58:28'),(4,4,'0012345681',1,1,1,0,'2022-11-10 10:58:28','2022-11-10 10:58:28'),(5,5,'0012345682',1,1,1,0,'2022-11-10 10:58:28','2022-11-10 10:58:28'),(6,6,'0012345683',1,1,1,0,'2022-11-10 10:58:28','2022-11-10 10:58:28'),(7,7,'0012345684',1,1,1,0,'2022-11-10 10:58:28','2022-11-10 10:58:28'),(8,8,'0012345685',1,1,1,0,'2022-11-10 10:58:28','2022-11-10 10:58:28'),(9,9,'0012345686',1,1,1,0,'2022-11-10 10:58:28','2022-11-10 10:58:28'),(10,10,'0012345687',1,1,1,0,'2022-11-10 10:58:29','2022-11-10 10:58:29'),(11,11,'0012345688',1,1,1,0,'2022-11-10 10:58:29','2022-11-10 10:58:29'),(12,12,'0012345689',1,1,1,0,'2022-11-10 10:58:29','2022-11-10 10:58:29'),(13,13,'0012345690',1,1,1,0,'2022-11-10 10:58:29','2022-11-10 10:58:29'),(14,14,'0012345691',1,1,1,0,'2022-11-10 10:58:29','2022-11-10 10:58:29'),(15,15,'0012345692',1,1,1,0,'2022-11-10 10:58:29','2022-11-10 10:58:29'),(16,16,'0012345693',1,1,1,0,'2022-11-10 10:59:29','2022-11-10 10:59:29');
/*!40000 ALTER TABLE `carte_physiques` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carte_virtuelles`
--

DROP TABLE IF EXISTS `carte_virtuelles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carte_virtuelles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `last` varchar(50) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carte_virtuelles`
--

LOCK TABLES `carte_virtuelles` WRITE;
/*!40000 ALTER TABLE `carte_virtuelles` DISABLE KEYS */;
/*!40000 ALTER TABLE `carte_virtuelles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commissions`
--

DROP TABLE IF EXISTS `commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_operation` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `value` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commissions`
--

LOCK TABLES `commissions` WRITE;
/*!40000 ALTER TABLE `commissions` DISABLE KEYS */;
INSERT INTO `commissions` VALUES (1,'retrait','Taux pourcentage',40,0,'2023-01-20 16:36:08','2023-01-20 16:43:54'),(2,'depot','Taux fixe',50,0,'2023-01-20 16:41:17','2023-01-20 16:41:17');
/*!40000 ALTER TABLE `commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departements`
--

DROP TABLE IF EXISTS `departements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) DEFAULT NULL,
  `code` char(5) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departements`
--

LOCK TABLES `departements` WRITE;
/*!40000 ALTER TABLE `departements` DISABLE KEYS */;
INSERT INTO `departements` VALUES (1,'Alibori','AL',0,'2022-11-23 10:24:46','2022-11-23 10:24:47'),(2,'Atacora','AK',0,'2022-11-23 10:28:05','2022-11-23 10:28:06'),(3,'Atlantique','AQ',0,'2022-11-23 10:28:03','2022-11-23 10:28:07'),(4,'Borgou','BO',0,'2022-11-23 10:28:03','2022-11-23 10:28:07'),(5,'Collines','CO',0,'2022-11-23 10:28:02','2022-11-23 10:28:08'),(6,'Couffo','KO',0,'2022-11-23 10:28:01','2022-11-23 10:28:09'),(7,'Donga','DO',0,'2022-11-23 10:28:01','2022-11-23 10:28:09'),(8,'Littoral','LI',0,'2022-11-23 10:28:00','2022-11-23 10:28:10'),(9,'Mono','MO',0,'2022-11-23 10:28:00','2022-11-23 10:28:10'),(10,'Ouémé','OU',0,'2022-11-23 10:27:58','2022-11-23 10:28:11'),(11,'Plateau','PL',0,'2022-11-23 10:27:57','2022-11-23 10:28:11'),(12,'Zou','ZO',0,'2022-11-23 10:27:57','2022-11-23 10:28:12');
/*!40000 ALTER TABLE `departements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `depots`
--

DROP TABLE IF EXISTS `depots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `depots` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_client_id` int DEFAULT NULL,
  `user_partenaire_id` int DEFAULT NULL,
  `partenaire_id` int DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `solde_avant` int DEFAULT NULL,
  `montant` int DEFAULT NULL,
  `frais` int DEFAULT NULL,
  `solde_apres` int DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `motif_rejet` text,
  `validate` tinyint DEFAULT NULL,
  `validateur_id` int DEFAULT NULL,
  `rejet_id` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `depots`
--

LOCK TABLES `depots` WRITE;
/*!40000 ALTER TABLE `depots` DISABLE KEYS */;
/*!40000 ALTER TABLE `depots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `frais`
--

DROP TABLE IF EXISTS `frais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `frais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_operation` varchar(50) DEFAULT NULL,
  `start` int DEFAULT NULL,
  `end` int DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `value` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `frais`
--

LOCK TABLES `frais` WRITE;
/*!40000 ALTER TABLE `frais` DISABLE KEYS */;
INSERT INTO `frais` VALUES (1,'retrait',1,5000,'Taux fixe',100,0,'2023-01-20 16:17:15','2023-01-20 16:17:15'),(2,'retrait',5001,20000,'Taux fixe',400,0,'2023-01-20 16:18:39','2023-01-20 16:18:39'),(3,'retrait',200001,100000,'Taux fixe',1000,0,'2023-01-20 16:19:16','2023-01-20 16:19:16'),(4,'retrait',100001,2000000,'Taux pourcentage',1,0,'2023-01-20 16:20:02','2023-01-20 16:20:02');
/*!40000 ALTER TABLE `frais` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gammes`
--

DROP TABLE IF EXISTS `gammes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gammes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) DEFAULT NULL,
  `prix` int DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gammes`
--

LOCK TABLES `gammes` WRITE;
/*!40000 ALTER TABLE `gammes` DISABLE KEYS */;
INSERT INTO `gammes` VALUES (1,'Agbonnon',5000,1,0,'2022-11-09 17:53:04','2022-11-10 09:16:11','physique','text de descriotion de la gamme 1'),(2,'Virtuelle',2000,1,0,'2022-12-29 08:26:42','2022-12-29 08:26:42','virtuelle','text de descriotion de la gamme 2'),(3,'Ewilzo',3000,1,0,'2023-01-03 11:50:45','2023-01-03 11:51:06','physique','text de descriotion de la gamme 3');
/*!40000 ALTER TABLE `gammes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gtp_requests`
--

DROP TABLE IF EXISTS `gtp_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gtp_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6825 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gtp_requests`
--

LOCK TABLES `gtp_requests` WRITE;
/*!40000 ALTER TABLE `gtp_requests` DISABLE KEYS */;
INSERT INTO `gtp_requests` VALUES (6792,'2023-01-20 16:33:58','2023-01-20 16:33:59'),(6793,'2023-01-20 16:56:12','2023-01-20 16:56:12'),(6794,'2023-01-20 16:56:17','2023-01-20 16:56:17'),(6795,'2023-01-20 16:56:17','2023-01-20 16:56:17'),(6796,'2023-01-20 16:56:17','2023-01-20 16:56:17'),(6797,'2023-01-20 16:56:18','2023-01-20 16:56:18'),(6798,'2023-01-20 16:56:18','2023-01-20 16:56:18'),(6799,'2023-01-20 16:56:18','2023-01-20 16:56:18'),(6800,'2023-01-20 16:56:56','2023-01-20 16:56:56'),(6801,'2023-01-20 16:56:57','2023-01-20 16:56:57'),(6802,'2023-01-20 16:57:01','2023-01-20 16:57:01'),(6803,'2023-01-20 16:57:02','2023-01-20 16:57:02'),(6804,'2023-01-20 19:34:28','2023-01-20 19:34:28'),(6805,'2023-01-21 12:27:29','2023-01-21 12:27:29'),(6806,'2023-01-21 12:30:29','2023-01-21 12:30:29'),(6807,'2023-01-21 12:31:34','2023-01-21 12:31:34'),(6808,'2023-01-21 12:31:56','2023-01-21 12:31:56'),(6809,'2023-01-23 10:48:15','2023-01-23 10:48:15'),(6810,'2023-01-23 10:49:37','2023-01-23 10:49:37'),(6811,'2023-01-23 10:52:41','2023-01-23 10:52:41'),(6812,'2023-01-23 11:03:12','2023-01-23 11:03:12'),(6813,'2023-01-23 11:14:06','2023-01-23 11:14:06'),(6814,'2023-01-23 11:17:26','2023-01-23 11:17:26'),(6815,'2023-01-23 11:18:45','2023-01-23 11:18:45'),(6816,'2023-01-23 11:18:57','2023-01-23 11:18:57'),(6817,'2023-01-23 11:29:49','2023-01-23 11:29:49'),(6818,'2023-01-23 11:30:13','2023-01-23 11:30:13'),(6819,'2023-01-23 11:52:50','2023-01-23 11:52:50'),(6820,'2023-01-23 11:52:51','2023-01-23 11:52:51'),(6821,'2023-01-23 11:52:52','2023-01-23 11:52:52'),(6822,'2023-01-23 12:04:11','2023-01-23 12:04:11'),(6823,'2023-01-23 12:04:12','2023-01-23 12:04:12'),(6824,'2023-01-23 12:04:12','2023-01-23 12:04:12');
/*!40000 ALTER TABLE `gtp_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kyc_clients`
--

DROP TABLE IF EXISTS `kyc_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kyc_clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `birthday` varchar(15) DEFAULT NULL,
  `departement` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profession` varchar(50) DEFAULT NULL,
  `revenu` varchar(50) DEFAULT NULL,
  `piece_type` int DEFAULT NULL,
  `piece_id` varchar(50) DEFAULT NULL,
  `piece_file` varchar(255) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `user_with_piece` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kyc_clients`
--

LOCK TABLES `kyc_clients` WRITE;
/*!40000 ALTER TABLE `kyc_clients` DISABLE KEYS */;
INSERT INTO `kyc_clients` VALUES (1,'GBEVE','Aurens','noreply-bcv@bestcash.me','229 68080278','22-JAN-2023','LI','Cotonou','BJ','Houeyiho',NULL,NULL,1,'123456','/storage/pieces/piece_file/L8SrL50oiARORpAwVu2iHTVpxConOa1miFjOUKck.png',0,'2023-01-22 10:36:11','2023-01-22 14:14:18','/storage/pieces/user_with_piece/637nOllZWXdc4yDFzcnYtCH3C68vLHRVPiy1ro9a.png');
/*!40000 ALTER TABLE `kyc_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `limits`
--

DROP TABLE IF EXISTS `limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `limits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_partenaire_id` int NOT NULL DEFAULT '0',
  `type_operation` varchar(50) DEFAULT NULL,
  `partenaire_id` int DEFAULT NULL,
  `montant` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `limits`
--

LOCK TABLES `limits` WRITE;
/*!40000 ALTER TABLE `limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mouchard_partenaires`
--

DROP TABLE IF EXISTS `mouchard_partenaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mouchard_partenaires` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) DEFAULT NULL,
  `user_partenaire_id` int DEFAULT NULL,
  `deleted` tinyint DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mouchard_partenaires`
--

LOCK TABLES `mouchard_partenaires` WRITE;
/*!40000 ALTER TABLE `mouchard_partenaires` DISABLE KEYS */;
/*!40000 ALTER TABLE `mouchard_partenaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mouchards`
--

DROP TABLE IF EXISTS `mouchards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mouchards` (
  `id` int NOT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `deleted` tinyint DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mouchards`
--

LOCK TABLES `mouchards` WRITE;
/*!40000 ALTER TABLE `mouchards` DISABLE KEYS */;
/*!40000 ALTER TABLE `mouchards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partenaires`
--

DROP TABLE IF EXISTS `partenaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `partenaires` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `last` varchar(50) DEFAULT NULL,
  `rccm` varchar(255) DEFAULT NULL,
  `ifu` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partenaires`
--

LOCK TABLES `partenaires` WRITE;
/*!40000 ALTER TABLE `partenaires` DISABLE KEYS */;
INSERT INTO `partenaires` VALUES (1,'Elite','12705536','7172','storage/partenaire/rccm/1674234755.jpg','storage/partenaire/ifu/1674234755.pdf','noreply-bcv@bestcash.me','22967717596',0,'2023-01-20 17:12:35','2023-01-20 17:12:35');
/*!40000 ALTER TABLE `partenaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `route` varchar(50) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (2,'admin','Voir la liste des clients','clients',0,'2022-11-17 18:15:59','2022-11-17 18:16:02'),(3,'admin','Modifier les clients','client.edit',0,'2022-11-17 18:16:01','2022-11-17 18:16:03'),(4,'admin','Supprimer des comptes clients','client.delete',0,'2022-11-17 18:15:59','2022-11-17 18:16:04'),(5,'admin','Reinitialiser le mot de passe des clients','client.reset.password',0,'2022-11-17 18:16:07','2022-11-17 18:16:08'),(6,'admin','Activer des comptes clients','client.activation',0,'2022-11-17 18:16:09','2022-11-17 18:16:09'),(7,'admin','Désactiver des comptes clients','client.desactivation',0,'2022-11-17 18:16:10','2022-11-17 18:16:11'),(8,'admin','Voir les details des comptes clients','client.details',0,'2022-11-17 18:16:11','2022-11-17 18:16:12'),(9,'admin','Editer les KYC clients','kyc.edit',0,'2022-11-17 18:16:13','2022-11-17 18:16:14'),(10,'admin','Voir la liste des partenaires','partenaires',0,'2022-11-17 18:16:14','2022-11-17 18:16:15'),(11,'admin','Ajouter un partenaire','partenaire.add',0,'2022-11-17 18:16:16','2022-11-17 18:16:16'),(12,'admin','Modifier les partenaires','partenaire.edit',0,'2022-11-17 18:16:17','2022-11-17 18:16:17'),(13,'admin','Supprimer les partenaires','partenaire.delete',0,'2022-11-17 18:16:18','2022-11-17 18:16:19'),(14,'admin','Voir les details des partenaires','partenaire.details',0,'2022-11-17 18:16:19','2022-11-17 18:16:20'),(15,'admin','Annuler les retraits d\'un partenaire','partenaire.cancel.retrait',0,'2022-11-16 18:16:21','2022-11-17 18:16:22'),(16,'admin','Annuler les depots d\'un partenaire','partenaire.cancel.depot',0,'2022-11-17 18:16:23','2022-11-17 18:16:24'),(17,'admin','Voir la liste des utilisateurs','users',0,'2022-11-17 18:16:27','2022-11-17 18:16:26'),(18,'admin','Ajouter un utilisateur','user.add',0,'2022-11-17 18:16:25','2022-11-17 18:16:27'),(19,'admin','Modifier un utilisateur','user.edit',0,'2022-11-17 18:16:28','2022-11-17 18:16:29'),(20,'admin','Supprimer un utilisateur','user.delete',0,'2022-11-17 18:16:29','2022-11-17 18:16:30'),(21,'admin','Activer un utilisateur','user.activation',0,'2022-11-17 18:16:31','2022-11-17 18:16:32'),(22,'admin','Desactiver un utilisateur','user.desactivation',0,'2022-11-17 18:16:31','2022-11-17 18:16:31'),(23,'admin','Voir les details des utilisateurs','user.details',0,'2022-11-17 18:16:33','2022-11-17 18:16:34'),(24,'admin','Reinitialiser le mot de passe des utilisateurs','user.reset.password',0,'2022-11-17 18:16:34','2022-11-17 18:16:35'),(25,'admin','Voir la liste des gammes de cartes','gammes',0,'2022-11-17 18:16:35','2022-11-17 18:16:36'),(26,'admin','Ajouter une gamme de carte','gamme.add',0,'2022-11-17 18:16:37','2022-11-17 18:16:37'),(27,'admin','Modifier une gamme de carte','gamme.edit',0,'2022-11-17 18:16:38','2022-11-17 18:16:38'),(28,'admin','Supprimer une gamme de carte','gamme.delete',0,'2022-11-17 18:16:44','2022-11-17 18:16:45'),(29,'admin','Activer une gamme de carte','gamme.activation',0,'2022-11-17 18:16:45','2022-11-17 18:16:46'),(30,'admin','Désactiver une gamme de carte','gamme.desactivation',0,'2022-11-17 18:16:46','2022-11-17 18:16:47'),(31,'admin','Voir la liste des frais de retraits','frais',0,'2022-11-17 18:16:47','2022-11-17 18:16:48'),(32,'admin','Ajouter un frais','frais.add',0,'2022-11-17 18:16:48','2022-11-17 18:16:49'),(33,'admin','Modifier un frais','frais.edit',0,'2022-11-17 18:16:49','2022-11-17 18:16:50'),(34,'admin','Supprimer un frais','frais.delete',0,'2022-11-17 18:16:50','2022-11-17 18:16:51'),(35,'admin','Voir la liste des cartes physiques disponibles','carte.physiques',0,'2022-11-17 18:16:54','2022-11-17 18:16:54'),(36,'admin','Stocker des cartes physiques','carte.physiques.add',0,'2022-11-17 18:16:53','2022-11-17 18:16:53'),(37,'admin','Voir la liste des ventes de carte physiques en attentes','vente.physiques.attentes',0,'2022-11-17 18:16:52','2022-11-17 18:16:52'),(38,'admin','Supprimer les ventes de carte physiques','vente.physiques.delete',0,'2022-11-17 18:16:55','2022-11-17 18:16:55'),(39,'admin','Valider les ventes de carte physiques en attente','vente.physiques.attentes.validation',0,'2022-11-17 18:16:56','2022-11-17 18:16:56'),(40,'admin','Rejeter les ventes de carte physiques en attente','vente.physiques.attentes.rejet',0,'2022-11-17 18:16:57','2022-11-17 18:16:58'),(41,'admin','Voir la liste des ventes de carte physiques finalies','vente.physiques.finalises',0,'2022-11-17 18:16:59','2022-11-17 18:16:59'),(42,'admin','Voir la liste des ventes de carte physiques en rejete','vente.physiques.rejetes',0,'2022-11-17 18:16:58','2022-11-17 18:17:00'),(43,'admin','Voir la liste des ventes de carte virtuelle en attentes','vente.virtuelles.attentes',0,'2022-11-17 18:17:05','2022-11-17 18:17:00'),(44,'admin','Supprimer les ventes de carte virtuelles','vente.virtuelles.delete',0,'2022-11-17 18:17:02','2022-11-17 18:17:01'),(45,'admin','Valider les ventes de carte virtuelles en attente','vente.virtuelles.attentes.validation',0,'2022-11-17 18:17:02','2022-11-17 18:17:01'),(46,'admin','Rejeter les ventes de carte virtuelles en attente','vente.virtuelles.attentes.rejet',0,'2022-11-17 18:17:07','2022-11-17 18:17:07'),(47,'admin','Voir la liste des ventes de carte virtuelles finalises','vente.virtuelles.finalises',0,'2022-11-17 18:17:09','2022-11-17 18:17:09'),(48,'admin','Voir la liste des ventes de carte virtuelles en rejete','vente.virtuelles.rejetes',0,'2022-11-17 18:17:11','2022-11-17 18:17:08'),(49,'admin','Voir la liste des recharges en attentes','rechargements.attente',0,'2022-11-17 18:17:10','2022-11-17 18:17:14'),(50,'admin','Supprimer les recharges en attentes','rechargement.attentes.delete',0,'2022-11-17 18:17:12','2022-11-17 18:17:14'),(51,'admin','Valider les recharges en attentes','rechargement.attentes.validation',0,'2022-11-15 18:17:12','2022-11-17 18:17:13'),(52,'admin','Rejeter les recharges en attentes','rechargement.attentes.rejet',0,'2022-11-17 18:17:34','2022-11-17 18:17:36'),(53,'admin','Voir la liste des rechargements finalises','rechargement.finalises',0,'2022-11-17 18:17:35','2022-11-17 18:17:35'),(54,'admin','Voir la liste des rechargements rejetes','rechargement.rejetes',0,'2022-11-17 18:46:31','2022-11-17 18:17:36'),(78,'partenaire','Voir les retaits en attente de validation client','retraits',0,'2022-11-17 18:53:07','2022-11-17 18:53:06'),(79,'partenaire','Voir les retraits finalisés','retraits.finalises',0,'2022-11-17 18:53:04','2022-11-17 18:53:05'),(80,'partenaire','Faire une operation de retrait','retrait.create',0,'2022-11-17 18:53:24','2022-11-17 18:53:27'),(81,'partenaire','Annuler une operation de retrait','retrait.cancel',0,'2022-11-17 18:53:28','2022-11-17 18:53:27'),(82,'partenaire','Voir les depots en attentes de validation client','depots',0,'2022-11-17 18:53:28','2022-11-17 18:53:31'),(83,'partenaire','Voir les depots finalisés','depots.finalises',0,'2022-11-17 18:53:29','2022-11-17 18:53:29'),(84,'partenaire','Faire une operation de depot','depot.create',0,'2022-11-17 18:53:33','2022-11-17 18:53:31'),(89,'partenaire','Voir la liste des utilisateurs','users',0,'2022-11-17 18:53:38','2022-11-17 18:53:39'),(90,'partenaire','Details des utilisateurs partenaires','user.details',0,'2022-11-17 18:53:40','2022-11-17 18:53:40'),(91,'partenaire','Ajouter un utilisateur partenaire','user.add',0,'2022-11-17 18:53:41','2022-11-17 18:53:42'),(92,'partenaire','Modifier un utilisateur partenaire','user.edit',0,'2022-11-17 18:53:42','2022-11-17 18:53:43'),(93,'partenaire','Supprimer un utilisateur partenaire','user.delete',0,'2022-11-17 18:53:43','2022-11-17 18:53:44'),(94,'partenaire','Reinitialiser le mot de passe d\'un utilisateur partenaire','user.reset.password',0,'2022-11-17 18:53:45','2022-11-17 18:53:46'),(95,'partenaire','Activer un utilisateur partenaire','user.activation',0,'2022-11-17 18:53:47','2022-11-17 18:53:48'),(96,'partenaire','Desactiver un utilisateur partenaire','user.desactivation',0,'2022-11-17 18:53:49','2022-11-17 18:53:49'),(97,'admin','Ajouter un compte client','client.add',0,'2022-11-18 10:19:04','2022-11-18 10:19:11'),(98,'admin','Voir la liste des roles','roles',0,'2022-11-18 16:04:24','2022-11-18 16:04:25'),(99,'admin','Ajouter un role','roles.add',0,'2022-11-18 16:04:23','2022-11-18 16:04:26'),(100,'admin','Modifier un role','roles.edit',0,'2022-11-18 16:04:22','2022-11-18 16:04:26'),(101,'admin','Supprimer un role','roles.delete',0,'2022-11-18 16:04:22','2022-11-18 16:04:26'),(102,'admin','Editer le kyc','edit.kyc',0,'2022-11-21 17:40:41','2022-11-21 17:40:42'),(103,'partenaire','Voir la liste des retraits en attente de validation partenaire','retraits.unvalidate',0,'2022-12-22 16:07:14','2022-12-22 16:07:16'),(104,'partenaire','Validation de retrait','retrait.validate',0,'2022-12-22 16:07:25','2022-12-22 16:07:26'),(105,'partenaire','Telechargement du retrait','retrait.download',0,'2022-12-22 16:07:33','2022-12-22 16:07:34'),(106,'partenaire','Voir la liste des depots rejetes','depots.rejetes',0,'2022-12-22 16:07:42','2022-12-22 16:07:43'),(107,'partenaire','Voir la liste des depots en attente de validation partenaire','depots.unvalidate',0,'2022-12-22 16:07:51','2022-12-22 16:07:52'),(108,'partenaire','Annulation de depot','depot.cancel',0,'2022-12-22 16:08:01','2022-12-22 16:08:03'),(109,'partenaire','Validation du depot','depot.validate',0,'2022-12-22 16:11:26','2022-12-22 16:11:27'),(110,'partenaire','Telechargement du depot','depot.download',0,'2022-12-22 16:17:04','2022-12-22 16:17:05'),(111,'partenaire','Voir la liste des restrictions','restrictions',0,'2022-12-22 16:17:30','2022-12-22 16:18:54'),(112,'partenaire','Ajout de restrictions','restriction.add',0,'2022-12-22 16:17:36','2022-12-22 16:18:54'),(113,'partenaire','Modifier les restrictions','restriction.edit',0,'2022-12-22 16:17:41','2022-12-22 16:18:55'),(114,'partenaire','Supprimer les restrictions','restriction.delete',0,'2022-12-22 16:17:46','2022-12-22 16:18:56'),(115,'partenaire','Activer une restriction','restriction.activation',0,'2022-12-22 16:18:02','2022-12-22 16:18:57'),(116,'partenaire','Desactiver une restriction','restriction.desactivation',0,'2022-12-22 16:18:09','2022-12-22 16:18:57'),(117,'partenaire','Voir la liste des limites d\'operation','limits',0,'2022-12-22 16:18:13','2022-12-22 16:18:58'),(118,'partenaire','Ajouter une limite d\'operation','limit.add',0,'2022-12-22 16:18:17','2022-12-22 16:19:00'),(119,'partenaire','Modifier une limite d\'operaion','limit.edit',0,'2022-12-22 16:18:20','2022-12-22 16:18:59'),(120,'partenaire','Supprimer une limite d\'operaion','limit.delete',0,'2022-12-22 16:18:24','2022-12-22 16:19:00'),(121,'partenaire','Voir la liste des roles','roles',0,'2022-12-22 16:18:27','2022-12-22 16:19:01'),(122,'partenaire','Ajouter les roles','roles.add',0,'2022-12-22 16:18:41','2022-12-22 16:19:01'),(123,'partenaire','Modifier les roles','roles.edit',0,'2022-12-22 16:18:46','2022-12-22 16:19:02'),(124,'partenaire','Suprimer les roles','roles.delete',0,'2022-12-22 16:18:50','2022-12-22 16:19:03'),(125,'partenaire','Voir la liste des retraits rejetes','retraits.rejetes',0,'2022-12-26 15:22:25','2022-12-26 15:22:26'),(126,'partenaire','Ajouter un depot','depots.new',0,'2022-12-26 16:20:17','2022-12-26 16:20:18'),(127,'partenaire','Ajouter un retrait','retraits.new',0,'2022-12-26 16:20:17','2022-12-26 16:20:19');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recharges`
--

DROP TABLE IF EXISTS `recharges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recharges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_client_id` int DEFAULT NULL,
  `montant` int DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `motif_rejet` text,
  `rejeteur_id` int DEFAULT NULL,
  `validateur_id` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recharges`
--

LOCK TABLES `recharges` WRITE;
/*!40000 ALTER TABLE `recharges` DISABLE KEYS */;
/*!40000 ALTER TABLE `recharges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restriction_agences`
--

DROP TABLE IF EXISTS `restriction_agences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `restriction_agences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `partenaire_id` int DEFAULT NULL,
  `user_partenaire_id` int DEFAULT NULL,
  `createur_id` int DEFAULT NULL,
  `type_operation` varchar(50) DEFAULT NULL,
  `type_restriction` varchar(50) DEFAULT NULL,
  `valeur` int DEFAULT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `etat` tinyint DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restriction_agences`
--

LOCK TABLES `restriction_agences` WRITE;
/*!40000 ALTER TABLE `restriction_agences` DISABLE KEYS */;
/*!40000 ALTER TABLE `restriction_agences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restrictions`
--

DROP TABLE IF EXISTS `restrictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `restrictions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_operation` varchar(50) DEFAULT NULL,
  `type_restriction` varchar(50) DEFAULT NULL,
  `type_acteur` varchar(50) DEFAULT NULL,
  `valeur` int DEFAULT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `etat` tinyint DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restrictions`
--

LOCK TABLES `restrictions` WRITE;
/*!40000 ALTER TABLE `restrictions` DISABLE KEYS */;
/*!40000 ALTER TABLE `restrictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `retraits`
--

DROP TABLE IF EXISTS `retraits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retraits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_client_id` int DEFAULT NULL,
  `user_partenaire_id` int DEFAULT NULL,
  `partenaire_id` int DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `solde_avant` int DEFAULT NULL,
  `montant` int DEFAULT NULL,
  `solde_apres` int DEFAULT NULL,
  `frais` int DEFAULT NULL,
  `code` int DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `motif_rejet` text,
  `validate` tinyint DEFAULT NULL,
  `validateur_id` int DEFAULT NULL,
  `rejet_id` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `retraits`
--

LOCK TABLES `retraits` WRITE;
/*!40000 ALTER TABLE `retraits` DISABLE KEYS */;
/*!40000 ALTER TABLE `retraits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_partenaire_permissions`
--

DROP TABLE IF EXISTS `role_partenaire_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_partenaire_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_partenaire_id` int DEFAULT NULL,
  `permission_id` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_partenaire_permissions`
--

LOCK TABLES `role_partenaire_permissions` WRITE;
/*!40000 ALTER TABLE `role_partenaire_permissions` DISABLE KEYS */;
INSERT INTO `role_partenaire_permissions` VALUES (1,1,78,0,'2022-12-16 18:20:03','2022-12-16 18:20:04'),(5,3,78,1,'2022-12-16 17:37:51','2022-12-16 17:39:41'),(6,3,79,1,'2022-12-16 17:37:51','2022-12-16 17:39:41'),(7,3,80,1,'2022-12-16 17:37:51','2022-12-16 17:39:41'),(8,3,81,1,'2022-12-16 17:37:51','2022-12-16 17:39:41'),(9,3,82,1,'2022-12-16 17:37:51','2022-12-16 17:39:05'),(10,3,83,1,'2022-12-16 17:37:51','2022-12-16 17:39:05'),(11,1,79,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(12,1,80,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(13,1,81,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(14,1,82,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(15,1,83,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(16,1,84,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(17,1,89,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(18,1,90,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(19,1,91,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(20,1,92,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(21,1,93,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(22,1,94,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(23,1,95,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(24,1,96,0,'2022-12-22 14:51:04','2022-12-22 14:51:04'),(25,4,78,0,'2022-12-22 15:25:19','2022-12-26 13:22:52'),(26,4,79,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(27,4,80,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(28,4,81,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(29,4,82,0,'2022-12-22 15:25:19','2022-12-22 15:49:05'),(30,4,83,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(31,4,84,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(32,4,89,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(33,4,90,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(34,4,91,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(35,4,92,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(36,4,93,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(37,4,94,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(38,4,95,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(39,4,96,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(40,4,103,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(41,4,104,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(42,4,105,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(43,4,106,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(44,4,107,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(45,4,108,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(46,4,109,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(47,4,110,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(48,4,111,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(49,4,112,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(50,4,113,0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(51,4,114,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(52,4,115,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(53,4,116,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(54,4,117,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(55,4,118,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(56,4,119,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(57,4,120,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(58,4,121,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(59,4,122,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(60,4,123,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(61,4,124,0,'2022-12-22 15:25:20','2022-12-22 15:25:20'),(62,5,78,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(63,5,79,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(64,5,80,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(65,5,81,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(66,5,82,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(67,5,83,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(68,5,84,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(69,5,89,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(70,5,90,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(71,5,91,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(72,5,92,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(73,5,93,0,'2022-12-26 13:55:22','2022-12-26 13:55:22'),(74,4,125,0,'2022-12-26 14:24:13','2022-12-26 14:24:13'),(75,4,126,0,'2022-12-26 15:22:56','2022-12-26 15:22:56'),(76,4,127,0,'2022-12-26 15:22:56','2022-12-26 15:22:56');
/*!40000 ALTER TABLE `role_partenaire_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_partenaires`
--

DROP TABLE IF EXISTS `role_partenaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_partenaires` (
  `id` int NOT NULL AUTO_INCREMENT,
  `partenaire_id` int DEFAULT NULL,
  `libelle` varchar(150) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_partenaires`
--

LOCK TABLES `role_partenaires` WRITE;
/*!40000 ALTER TABLE `role_partenaires` DISABLE KEYS */;
INSERT INTO `role_partenaires` VALUES (1,1,'test',0,'2022-12-16 18:19:48','2022-12-16 18:19:49'),(3,1,'Agent',1,'2022-12-16 17:37:51','2022-12-26 13:25:54'),(4,1,'Administrateur',0,'2022-12-22 15:25:19','2022-12-22 15:25:19'),(5,1,'Ministre',0,'2022-12-26 13:55:22','2022-12-26 13:55:22');
/*!40000 ALTER TABLE `role_partenaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int DEFAULT NULL,
  `permission_id` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1,78,0,'2022-11-18 11:58:23','2022-11-30 17:38:44'),(2,1,79,0,'2022-11-18 11:58:23','2022-11-30 17:38:44'),(3,1,80,0,'2022-11-18 11:58:23','2022-11-30 17:38:44'),(16,1,81,0,'2022-11-18 14:42:04','2022-11-30 17:38:44'),(18,1,82,0,'2022-11-18 13:49:05','2022-11-30 17:38:44'),(19,1,83,0,'2022-11-18 13:49:05','2022-11-30 17:38:44'),(20,1,84,0,'2022-11-18 13:49:05','2022-11-30 17:38:44'),(21,1,89,0,'2022-11-18 13:49:05','2022-11-30 17:38:44'),(26,1,94,0,'2022-11-18 13:49:32','2022-11-30 17:38:44'),(27,1,95,0,'2022-11-18 13:49:32','2022-11-30 17:38:44'),(28,1,90,0,'2022-11-18 14:29:39','2022-11-30 17:38:44'),(29,1,91,0,'2022-11-18 14:29:39','2022-11-30 17:38:44'),(30,1,92,0,'2022-11-18 14:29:39','2022-11-30 17:38:44'),(31,1,93,0,'2022-11-18 14:29:39','2022-11-30 17:38:44'),(32,1,96,0,'2022-11-18 14:29:39','2022-11-30 17:38:44'),(33,2,2,0,'2022-11-18 14:33:45','2022-11-18 16:46:21'),(34,2,3,0,'2022-11-18 14:33:45','2022-11-18 16:59:11'),(35,2,4,0,'2022-11-18 14:33:45','2022-11-18 16:59:11'),(36,2,5,0,'2022-11-18 14:33:45','2022-11-18 16:47:13'),(37,2,6,0,'2022-11-18 14:33:45','2022-11-18 14:33:45'),(38,2,7,0,'2022-11-18 14:33:45','2022-11-18 14:33:45'),(39,2,8,0,'2022-11-18 14:33:45','2022-11-18 16:47:40'),(40,2,9,0,'2022-11-18 14:33:45','2022-11-18 14:33:45'),(41,2,10,0,'2022-11-18 14:33:45','2022-11-18 17:00:59'),(42,2,11,0,'2022-11-18 14:33:45','2022-11-18 17:06:33'),(43,2,12,0,'2022-11-18 14:33:45','2022-11-18 17:06:33'),(44,2,13,0,'2022-11-18 14:33:45','2022-11-18 14:33:45'),(45,2,14,0,'2022-11-18 14:33:45','2022-11-18 14:33:45'),(46,2,15,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(47,2,16,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(48,2,17,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(49,2,18,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(50,2,19,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(51,2,20,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(52,2,21,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(53,2,22,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(54,2,23,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(55,2,24,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(56,2,25,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(57,2,26,0,'2022-11-18 14:33:46','2022-11-18 16:18:23'),(58,2,27,0,'2022-11-18 14:33:46','2022-11-18 16:18:23'),(59,2,28,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(60,2,29,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(61,2,30,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(62,2,31,0,'2022-11-18 14:33:46','2022-11-18 16:05:48'),(63,2,32,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(64,2,33,0,'2022-11-18 14:33:46','2022-11-18 16:07:16'),(65,2,34,0,'2022-11-18 14:33:46','2022-11-18 16:12:37'),(66,2,35,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(67,2,36,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(68,2,37,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(69,2,38,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(70,2,39,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(71,2,40,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(72,2,41,0,'2022-11-18 14:33:46','2022-11-18 14:33:46'),(73,2,42,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(74,2,43,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(75,2,44,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(76,2,45,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(77,2,46,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(78,2,47,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(79,2,48,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(80,2,49,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(81,2,50,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(82,2,51,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(83,2,52,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(84,2,53,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(85,2,54,0,'2022-11-18 14:33:47','2022-11-18 14:33:47'),(89,2,97,0,'2022-11-18 14:33:47','2022-11-18 16:47:13'),(90,3,2,0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(91,3,3,0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(92,3,4,0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(93,3,5,0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(94,3,6,0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(95,3,7,0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(96,3,8,0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(97,3,9,0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(98,3,10,0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(99,3,11,0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(100,3,12,0,'2022-11-18 14:38:04','2022-11-18 14:38:04'),(101,3,13,0,'2022-11-18 14:38:04','2022-11-18 14:38:04'),(102,3,14,0,'2022-11-18 14:38:04','2022-11-18 14:38:04'),(103,3,15,0,'2022-11-18 14:38:04','2022-11-18 14:38:04'),(104,2,98,0,'2022-11-18 15:05:20','2022-11-18 15:05:20'),(105,2,99,0,'2022-11-18 15:05:20','2022-11-18 15:14:09'),(106,2,100,0,'2022-11-18 15:05:20','2022-11-18 15:42:22'),(107,2,101,0,'2022-11-18 15:05:20','2022-12-16 17:12:29'),(108,2,102,0,'2022-11-21 16:42:59','2022-11-21 16:42:59'),(109,4,2,0,'2022-12-16 17:14:05','2022-12-16 17:14:05'),(110,4,4,0,'2022-12-16 17:14:06','2022-12-16 17:14:06');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (2,'Administrateur',0,'2022-11-18 14:33:45','2022-11-18 14:37:34'),(3,'test',0,'2022-11-18 14:38:03','2022-11-18 14:38:03'),(4,'Agent',0,'2022-12-16 17:14:05','2022-12-16 17:14:05');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seuils`
--

DROP TABLE IF EXISTS `seuils`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seuils` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_operation` varchar(50) DEFAULT NULL,
  `type_restriction` varchar(50) DEFAULT NULL,
  `valeur` int DEFAULT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `etat` tinyint DEFAULT NULL,
  `partenaire_id` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seuils`
--

LOCK TABLES `seuils` WRITE;
/*!40000 ALTER TABLE `seuils` DISABLE KEYS */;
/*!40000 ALTER TABLE `seuils` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transferts`
--

DROP TABLE IF EXISTS `transferts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transferts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_client_id` int DEFAULT NULL,
  `receveur_id` int DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `solde_avant_receveur` int DEFAULT NULL,
  `solde_apres_receveur` int DEFAULT NULL,
  `solde_avant_envoyeur` int DEFAULT NULL,
  `solde_apres_envoyeur` int DEFAULT NULL,
  `montant` int DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transferts`
--

LOCK TABLES `transferts` WRITE;
/*!40000 ALTER TABLE `transferts` DISABLE KEYS */;
/*!40000 ALTER TABLE `transferts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_clients`
--

DROP TABLE IF EXISTS `user_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lastname` varchar(100) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  `last` varchar(10) DEFAULT NULL,
  `username` varchar(15) DEFAULT NULL,
  `password` text,
  `status` tinyint DEFAULT NULL,
  `double_authentification` tinyint DEFAULT NULL,
  `sms` tinyint DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `lastconnexion` varchar(50) DEFAULT NULL,
  `verification` tinyint DEFAULT '0',
  `verification_step_one` tinyint DEFAULT '0',
  `verification_step_two` tinyint DEFAULT '0',
  `verification_step_three` tinyint DEFAULT '0',
  `kyc_client_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_clients`
--

LOCK TABLES `user_clients` WRITE;
/*!40000 ALTER TABLE `user_clients` DISABLE KEYS */;
INSERT INTO `user_clients` VALUES (1,'Enock','GBEVE','12705536','7172','22967717596','$2y$10$8PlCSsAz8zxc27bokC2Uu.19TcQvAC4/xd26SQm1WN/qFPQyPj/aC',0,0,0,0,'2023-01-20 16:57:12','2023-01-20 16:57:12',NULL,0,0,0,0,NULL),(2,'Aurens','GBEVE','12706189','5553','22968080278','$2y$10$Q6xZ9t1DBFned1xG./PVzuxKyhuqpdLC565EFMMWkPDmztG2u37U6',1,0,0,0,'2023-01-20 17:30:35','2023-01-23 11:30:15',NULL,1,1,1,1,1),(3,'Evans','GBEVE',NULL,NULL,'22962617848','$2y$10$GAliK4Ays3DCTZ3aqMkGduWQSO7dCvdLNw8gdsi3nkJ8EFj/nLDY.',0,0,0,0,'2023-01-20 17:36:02','2023-01-20 17:36:02',NULL,0,0,0,0,NULL),(4,'Erica','HOUNKPONOU',NULL,NULL,'22961710410','$2y$10$DB6L17o6lHXzd7Ln5Huai.4SMezhdnTak.zcvRce/fJGChkenMaF6',0,0,0,0,'2023-01-20 18:05:40','2023-01-20 18:05:40',NULL,0,0,0,0,NULL),(5,'Béatrice','LATONKPODE',NULL,NULL,'22991221518','$2y$10$uk6ogeE14HKph3D/oZzmdeZNhvqDm.YzHgUWPV9viPqNF7U1EBiw2',0,0,0,0,'2023-01-20 18:06:32','2023-01-20 18:06:32',NULL,0,0,0,0,NULL);
/*!40000 ALTER TABLE `user_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_partenaires`
--

DROP TABLE IF EXISTS `user_partenaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_partenaires` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lastname` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `partenaire_id` int DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `status` bit(1) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role_partenaire_id` int DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `lastconnexion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_partenaires`
--

LOCK TABLES `user_partenaires` WRITE;
/*!40000 ALTER TABLE `user_partenaires` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_partenaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lastname` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(250) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `role_id` int DEFAULT NULL,
  `lastconnexion` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Aurens','GBEVE','gaurens','$2y$10$XVdPNQ2C0TBPN7m72iqxguL/ubhBEL2BypeYr3nMvtXCo25BY9Oky',0,1,2,NULL,'2022-11-09 15:38:43','2022-12-02 13:49:13'),(2,'Evans','GBEVE','gevans','$2y$10$fYsVcVk19n/.EP7dSUHduOGPT6CfSOkyUHuJwAFNoS56zYnvxSe6m',0,1,3,NULL,'2022-11-11 12:40:43','2022-11-11 12:40:43'),(3,'Jean','SINGBO','sjean','$2y$10$d0V9ELzhYEAD0wpY4wLuT.n/uXjVYmAWegV/D8et5eqr2osQAw95u',0,1,2,NULL,'2022-12-02 13:30:16','2022-12-02 13:30:16');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vente_physiques`
--

DROP TABLE IF EXISTS `vente_physiques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vente_physiques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kyc_client_id` int DEFAULT NULL,
  `montant` int DEFAULT NULL,
  `gamme_id` int DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `carte_physique_id` int DEFAULT NULL,
  `rejeteur_id` int DEFAULT NULL,
  `validateur_id` int DEFAULT NULL,
  `motif_rejet` varchar(50) DEFAULT NULL,
  `etat` tinyint DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vente_physiques`
--

LOCK TABLES `vente_physiques` WRITE;
/*!40000 ALTER TABLE `vente_physiques` DISABLE KEYS */;
/*!40000 ALTER TABLE `vente_physiques` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vente_virtuelles`
--

DROP TABLE IF EXISTS `vente_virtuelles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vente_virtuelles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kyc_client_id` int DEFAULT NULL,
  `carte_virtuelle_id` int DEFAULT NULL,
  `gamme_id` int DEFAULT NULL,
  `montant` int DEFAULT NULL,
  `etat` tinyint DEFAULT NULL,
  `motif_rejet` varchar(255) DEFAULT NULL,
  `rejeteur_id` varchar(255) DEFAULT NULL,
  `validateur_id` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `deleted` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vente_virtuelles`
--

LOCK TABLES `vente_virtuelles` WRITE;
/*!40000 ALTER TABLE `vente_virtuelles` DISABLE KEYS */;
/*!40000 ALTER TABLE `vente_virtuelles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-01-23 14:30:20
