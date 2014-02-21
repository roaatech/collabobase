-- CREATE DATABASE  IF NOT EXISTS `t4f` /*!40100 DEFAULT CHARACTER SET utf8 */;
-- USE `t4f`;
-- MySQL dump 10.13  Distrib 5.6.13, for Win32 (x86)
--
-- Host: 127.0.0.1    Database: t4f
-- ------------------------------------------------------
-- Server version	5.6.12-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Temporary table structure for view `category`
--

DROP TABLE IF EXISTS `category`;
/*!50001 DROP VIEW IF EXISTS `category`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `category` (
  `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `parent` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `category_states`
--

DROP TABLE IF EXISTS `category_states`;
/*!50001 DROP VIEW IF EXISTS `category_states`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `category_states` (
  `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `parent` tinyint NOT NULL,
  `posts` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `last_update` timestamp NULL DEFAULT NULL,
  `last_chat_message_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_chat_user1_idx` (`user_id`),
  KEY `idx_time` (`time`),
  KEY `idx_last_update` (`last_update`),
  CONSTRAINT `fk_chat_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat`
--
-- ORDER BY:  `id`

LOCK TABLES `chat` WRITE;
/*!40000 ALTER TABLE `chat` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_message`
--

DROP TABLE IF EXISTS `chat_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `script` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `chat_id` int(11) NOT NULL,
  `chat_participant_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_chat_message_chat_id` (`chat_id`),
  KEY `idx_chat_message_chat_participant_id` (`chat_participant_id`),
  KEY `idx_chat_message_time` (`time`),
  CONSTRAINT `fk_chat_message_chat_id` FOREIGN KEY (`chat_id`) REFERENCES `chat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_chat_message_chat_participant_id` FOREIGN KEY (`chat_participant_id`) REFERENCES `chat_participant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_message`
--
-- ORDER BY:  `id`

LOCK TABLES `chat_message` WRITE;
/*!40000 ALTER TABLE `chat_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_participant`
--

DROP TABLE IF EXISTS `chat_participant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_participant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role` set('viewer','user','admin','deleted') NOT NULL DEFAULT 'user',
  `chat_id` int(11) NOT NULL,
  `last_check` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chat_participant_user_chat` (`user_id`,`chat_id`),
  KEY `fk_chat_participant_user1_idx` (`user_id`),
  KEY `fk_chat_participant_chat1_idx` (`chat_id`),
  CONSTRAINT `fk_chat_participant_chat1` FOREIGN KEY (`chat_id`) REFERENCES `chat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_chat_participant_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_participant`
--
-- ORDER BY:  `id`

LOCK TABLES `chat_participant` WRITE;
/*!40000 ALTER TABLE `chat_participant` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_participant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file`
--

DROP TABLE IF EXISTS `file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `version` float(4,2) NOT NULL DEFAULT '0.00',
  `last_version_id` int(11) DEFAULT NULL,
  `status` enum('active','deleted') NOT NULL DEFAULT 'active',
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_file_user1_idx` (`user_id`),
  KEY `fk_file_file_version_last_version_id_idx` (`last_version_id`),
  KEY `idx_file_status` (`status`),
  KEY `idx_time` (`time`,`update_time`),
  CONSTRAINT `fk_file_file_version_last_version_id` FOREIGN KEY (`last_version_id`) REFERENCES `file_version` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_file_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file`
--
-- ORDER BY:  `id`

LOCK TABLES `file` WRITE;
/*!40000 ALTER TABLE `file` DISABLE KEYS */;
/*!40000 ALTER TABLE `file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `file_category`
--

DROP TABLE IF EXISTS `file_category`;
/*!50001 DROP VIEW IF EXISTS `file_category`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `file_category` (
  `file_id` tinyint NOT NULL,
  `category_id` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `file_version`
--

DROP TABLE IF EXISTS `file_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_type` varchar(45) NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT '0',
  `original_name` varchar(45) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `version` float(4,2) NOT NULL DEFAULT '1.00',
  `status` enum('active','deleted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `fk_file_version_user1_idx` (`user_id`),
  KEY `fk_file_version_file1_idx` (`file_id`),
  KEY `idx_version_time` (`time`),
  KEY `fk_file_version_post_id_idx` (`post_id`),
  KEY `idx_file_version_status` (`status`),
  CONSTRAINT `fk_file_version_file1` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_file_version_post_id` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_file_version_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_version`
--
-- ORDER BY:  `id`

LOCK TABLES `file_version` WRITE;
/*!40000 ALTER TABLE `file_version` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group`
--

DROP TABLE IF EXISTS `group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group`
--
-- ORDER BY:  `id`

LOCK TABLES `group` WRITE;
/*!40000 ALTER TABLE `group` DISABLE KEYS */;
INSERT  IGNORE INTO `group` (`id`, `name`, `description`) VALUES (1,'all',NULL);
/*!40000 ALTER TABLE `group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_user`
--

DROP TABLE IF EXISTS `group_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_group_user_user_idx` (`user_id`),
  KEY `fk_group_user_group1_idx` (`group_id`),
  CONSTRAINT `fk_group_user_group1` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_group_user_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_user`
--
-- ORDER BY:  `id`

LOCK TABLES `group_user` WRITE;
/*!40000 ALTER TABLE `group_user` DISABLE KEYS */;
INSERT  IGNORE INTO `group_user` (`id`, `user_id`, `group_id`) VALUES (1,1,1);
/*!40000 ALTER TABLE `group_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notorm`
--

DROP TABLE IF EXISTS `notorm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notorm` (
  `id` varchar(255) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notorm`
--
-- ORDER BY:  `id`

LOCK TABLES `notorm` WRITE;
/*!40000 ALTER TABLE `notorm` DISABLE KEYS */;
/*!40000 ALTER TABLE `notorm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `root_post_id` int(11) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('draft','pending','active','removed','closed') NOT NULL DEFAULT 'draft',
  `last_update_user_id` int(11) DEFAULT NULL,
  `last_update_time` timestamp NULL DEFAULT NULL,
  `last_reply_time` timestamp NULL DEFAULT NULL,
  `last_reply_user_id` int(11) DEFAULT NULL,
  `last_reply_post_id` int(11) DEFAULT NULL,
  `total_replies` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_post_user1_idx` (`user_id`),
  KEY `fk_post_file1_idx` (`file_id`),
  KEY `fk_post_post1_idx` (`post_id`),
  KEY `idx_post_time` (`time`),
  KEY `idx_post_status` (`status`),
  KEY `fk_post_last_updater_user_id_idx` (`last_update_user_id`),
  KEY `fk_post_root_post_id_idx` (`root_post_id`),
  KEY `fk_post_last_reply_post_id_idx` (`last_reply_post_id`),
  KEY `fk_post_last_reply_user_id_idx` (`last_reply_user_id`),
  KEY `idx_post_total_replies` (`total_replies`),
  CONSTRAINT `fk_post_file_id` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_post_last_reply_post_id` FOREIGN KEY (`last_reply_post_id`) REFERENCES `post` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_post_last_reply_user_id` FOREIGN KEY (`last_reply_user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_post_last_update_user_id` FOREIGN KEY (`last_update_user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_post_post_id` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_post_root_post_id` FOREIGN KEY (`root_post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_post_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post`
--
-- ORDER BY:  `id`

LOCK TABLES `post` WRITE;
/*!40000 ALTER TABLE `post` DISABLE KEYS */;
/*!40000 ALTER TABLE `post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `post_category`
--

DROP TABLE IF EXISTS `post_category`;
/*!50001 DROP VIEW IF EXISTS `post_category`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `post_category` (
  `post_id` tinyint NOT NULL,
  `category_id` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `post_tag`
--

DROP TABLE IF EXISTS `post_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_post_tag_post1_idx` (`post_id`),
  KEY `fk_post_tag_tag1_idx` (`tag_id`),
  CONSTRAINT `fk_post_tag_post1` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_post_tag_tag1` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_tag`
--
-- ORDER BY:  `id`

LOCK TABLES `post_tag` WRITE;
/*!40000 ALTER TABLE `post_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_sessions`
--

DROP TABLE IF EXISTS `sys_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_sessions`
--
-- ORDER BY:  `session_id`

LOCK TABLES `sys_sessions` WRITE;
/*!40000 ALTER TABLE `sys_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `tag_type_id` int(11) DEFAULT NULL,
  `data_type` set('number','boolean','string') NOT NULL DEFAULT 'string',
  `tag_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tag_name_type_parent` (`name`,`tag_type_id`,`tag_id`),
  KEY `fk_tag_tag_type1_idx` (`tag_type_id`),
  KEY `fk_tag_tag1_idx` (`tag_id`),
  CONSTRAINT `fk_tag_tag1` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tag_tag_type1` FOREIGN KEY (`tag_type_id`) REFERENCES `tag_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag`
--
-- ORDER BY:  `id`

LOCK TABLES `tag` WRITE;
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `tag_posts`
--

DROP TABLE IF EXISTS `tag_posts`;
/*!50001 DROP VIEW IF EXISTS `tag_posts`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `tag_posts` (
  `tag_id` tinyint NOT NULL,
  `count` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tag_type`
--

DROP TABLE IF EXISTS `tag_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_type`
--
-- ORDER BY:  `id`

LOCK TABLES `tag_type` WRITE;
/*!40000 ALTER TABLE `tag_type` DISABLE KEYS */;
INSERT  IGNORE INTO `tag_type` (`id`, `name`) VALUES (1,'category');
/*!40000 ALTER TABLE `tag_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `role` set('admin','supervisor','user') NOT NULL DEFAULT 'user',
  `first_name` varchar(45) DEFAULT NULL,
  `second_name` varchar(45) DEFAULT NULL,
  `last_name` varchar(45) DEFAULT NULL,
  `birth_date` datetime DEFAULT NULL,
  `note` text,
  `deleted` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_deleted` (`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--
-- ORDER BY:  `id`

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT  IGNORE INTO `user` (`id`, `username`, `password`, `role`, `first_name`, `second_name`, `last_name`, `birth_date`, `note`, `deleted`) VALUES (1,'admin','21232f297a57a5a743894a0e4a801fc3','admin','','','','1970-03-11 00:00:00',NULL,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_address`
--

DROP TABLE IF EXISTS `user_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_address` (
  `user_id` int(11) NOT NULL,
  `street` varchar(45) DEFAULT NULL,
  `city` varchar(45) DEFAULT NULL,
  `state` varchar(45) DEFAULT NULL,
  `zip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_profile_address_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_address`
--
-- ORDER BY:  `user_id`

LOCK TABLES `user_address` WRITE;
/*!40000 ALTER TABLE `user_address` DISABLE KEYS */;
INSERT  IGNORE INTO `user_address` (`user_id`, `street`, `city`, `state`, `zip`) VALUES (1,'','','','');
/*!40000 ALTER TABLE `user_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_contact`
--

DROP TABLE IF EXISTS `user_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_contact` (
  `user_id` int(11) NOT NULL,
  `main_phone` varchar(45) DEFAULT NULL,
  `alternative_phone` varchar(45) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `skype_id` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  CONSTRAINT `fk_user_contact_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_contact`
--
-- ORDER BY:  `user_id`

LOCK TABLES `user_contact` WRITE;
/*!40000 ALTER TABLE `user_contact` DISABLE KEYS */;
INSERT  IGNORE INTO `user_contact` (`user_id`, `main_phone`, `alternative_phone`, `email`, `skype_id`) VALUES (1,'','','','');
/*!40000 ALTER TABLE `user_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_emergency_contact`
--

DROP TABLE IF EXISTS `user_emergency_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_emergency_contact` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `primary_phone` varchar(45) DEFAULT NULL,
  `alternative_phone` varchar(45) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `skype_id` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `full_name_UNIQUE` (`full_name`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  CONSTRAINT `fk_user_emergency_contact_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_emergency_contact`
--
-- ORDER BY:  `user_id`

LOCK TABLES `user_emergency_contact` WRITE;
/*!40000 ALTER TABLE `user_emergency_contact` DISABLE KEYS */;
INSERT  IGNORE INTO `user_emergency_contact` (`user_id`, `full_name`, `primary_phone`, `alternative_phone`, `email`, `skype_id`) VALUES (1,'','','','','');
/*!40000 ALTER TABLE `user_emergency_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_file`
--

DROP TABLE IF EXISTS `user_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `file_type` varchar(45) NOT NULL,
  `file_name` varchar(45) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `local_name` varchar(100) NOT NULL,
  `tags` set('profile','active') DEFAULT NULL,
  `others` text,
  PRIMARY KEY (`id`),
  KEY `fk_user_files_user1_idx` (`user_id`),
  CONSTRAINT `fk_user_files_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_file`
--
-- ORDER BY:  `id`

LOCK TABLES `user_file` WRITE;
/*!40000 ALTER TABLE `user_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_information`
--

DROP TABLE IF EXISTS `user_information`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_information` (
  `user_id` int(11) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` set('active','paused') NOT NULL DEFAULT 'active',
  `last_access` timestamp NULL DEFAULT NULL,
  `last_discussions_check` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_user_information_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_information`
--
-- ORDER BY:  `user_id`

LOCK TABLES `user_information` WRITE;
/*!40000 ALTER TABLE `user_information` DISABLE KEYS */;
INSERT  IGNORE INTO `user_information` (`user_id`, `creation_date`, `status`, `last_access`, `last_discussions_check`) VALUES (1,'2013-12-19 20:29:29','active',NULL,NULL);
/*!40000 ALTER TABLE `user_information` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_mission`
--

DROP TABLE IF EXISTS `user_mission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_mission` (
  `user_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `department` varchar(45) DEFAULT NULL,
  `supervisor` varchar(45) DEFAULT NULL,
  `employee_id` varchar(45) DEFAULT NULL,
  `work_location` varchar(45) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_mission_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_mission`
--
-- ORDER BY:  `user_id`

LOCK TABLES `user_mission` WRITE;
/*!40000 ALTER TABLE `user_mission` DISABLE KEYS */;
INSERT  IGNORE INTO `user_mission` (`user_id`, `title`, `department`, `supervisor`, `employee_id`, `work_location`, `start_date`) VALUES (1,'','','','','',NULL);
/*!40000 ALTER TABLE `user_mission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `category`
--
-- ALGORITHM=UNDEFINED
-- /*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */

/*!50001 DROP TABLE IF EXISTS `category`*/;
/*!50001 DROP VIEW IF EXISTS `category`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `category` AS select `tag`.`id` AS `id`,`tag`.`name` AS `name`,`tag`.`tag_id` AS `parent` from `tag` where `tag`.`tag_type_id` in (select `tag_type`.`id` from `tag_type` where (`tag_type`.`name` = 'category')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `category_states`
--

/*!50001 DROP TABLE IF EXISTS `category_states`*/;
/*!50001 DROP VIEW IF EXISTS `category_states`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `category_states` AS select `category`.`id` AS `id`,`category`.`name` AS `name`,`category`.`parent` AS `parent`,ifnull(`posts`.`count`,0) AS `posts` from (`category` left join `tag_posts` `posts` on((`category`.`id` = `posts`.`tag_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `file_category`
--

/*!50001 DROP TABLE IF EXISTS `file_category`*/;
/*!50001 DROP VIEW IF EXISTS `file_category`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `file_category` AS select `post`.`file_id` AS `file_id`,ifnull(`pt`.`tag_id`,NULL) AS `category_id` from (`post` left join `post_tag` `pt` on((`pt`.`post_id` = `post`.`id`))) where ((`pt`.`tag_id` in (select `category`.`id` from `category`) or isnull(`pt`.`tag_id`)) and (`post`.`file_id` is not null)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `post_category`
--

/*!50001 DROP TABLE IF EXISTS `post_category`*/;
/*!50001 DROP VIEW IF EXISTS `post_category`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `post_category` AS select `post`.`id` AS `post_id`,ifnull(`pt`.`tag_id`,NULL) AS `category_id` from (`post` left join `post_tag` `pt` on((`pt`.`post_id` = `post`.`id`))) where ((`pt`.`tag_id` in (select `category`.`id` from `category`) or isnull(`pt`.`tag_id`)) and isnull(`post`.`file_id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tag_posts`
--

/*!50001 DROP TABLE IF EXISTS `tag_posts`*/;
/*!50001 DROP VIEW IF EXISTS `tag_posts`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE VIEW `tag_posts` AS select `post_tag`.`tag_id` AS `tag_id`,count(0) AS `count` from `post_tag` group by `post_tag`.`tag_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
