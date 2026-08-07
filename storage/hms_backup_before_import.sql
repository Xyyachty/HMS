
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

/*!40000 DROP DATABASE IF EXISTS `hms`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `hms` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `hms`;
DROP TABLE IF EXISTS `faculties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faculties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `block` varchar(5) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculties_block_unique` (`block`),
  KEY `faculties_user_id_foreign` (`user_id`),
  CONSTRAINT `faculties_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `faculties` WRITE;
/*!40000 ALTER TABLE `faculties` DISABLE KEYS */;
INSERT INTO `faculties` VALUES (2,5,'923654745','active',NULL,'2026-06-12 08:33:34','2026-06-12 08:33:34'),(3,10,'','active',NULL,'2026-06-12 11:09:32','2026-06-12 11:09:32'),(4,15,'9266511423','active',NULL,'2026-07-02 00:17:39','2026-07-02 00:17:39');
/*!40000 ALTER TABLE `faculties` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `faculty_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faculty_classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `faculty_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `letter` varchar(8) NOT NULL,
  `capacity` smallint(5) unsigned NOT NULL DEFAULT 40,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculty_classes_faculty_id_letter_unique` (`faculty_id`,`letter`),
  CONSTRAINT `faculty_classes_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `faculty_classes` WRITE;
/*!40000 ALTER TABLE `faculty_classes` DISABLE KEYS */;
/*!40000 ALTER TABLE `faculty_classes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `front_desk_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_desk_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `canvas_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `front_desk_activities_user_id_action_index` (`user_id`,`action`),
  KEY `front_desk_activities_canvas_id_index` (`canvas_id`),
  CONSTRAINT `front_desk_activities_canvas_id_foreign` FOREIGN KEY (`canvas_id`) REFERENCES `front_desk_canvases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `front_desk_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `front_desk_activities` WRITE;
/*!40000 ALTER TABLE `front_desk_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `front_desk_activities` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `front_desk_canvases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_desk_canvases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `faculty_id` bigint(20) unsigned DEFAULT NULL,
  `student_group_id` bigint(20) unsigned DEFAULT NULL,
  `canvas_mode` enum('custom','default') NOT NULL DEFAULT 'custom',
  `widgets` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`widgets`)),
  `default_html` longtext DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `front_desk_canvases_faculty_id_foreign` (`faculty_id`),
  KEY `front_desk_canvases_student_group_id_foreign` (`student_group_id`),
  KEY `front_desk_canvases_user_id_faculty_id_index` (`user_id`,`faculty_id`),
  CONSTRAINT `front_desk_canvases_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `front_desk_canvases_student_group_id_foreign` FOREIGN KEY (`student_group_id`) REFERENCES `student_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `front_desk_canvases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `front_desk_canvases` WRITE;
/*!40000 ALTER TABLE `front_desk_canvases` DISABLE KEYS */;
/*!40000 ALTER TABLE `front_desk_canvases` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `group_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `faculty_id` bigint(20) unsigned NOT NULL,
  `selected_template` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_settings_group_name_faculty_id_unique` (`group_name`,`faculty_id`),
  KEY `group_settings_faculty_id_foreign` (`faculty_id`),
  CONSTRAINT `group_settings_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `group_settings` WRITE;
/*!40000 ALTER TABLE `group_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `hotel_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hotel_customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `faculty_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hotel_customers_team_email_unique` (`group_name`,`faculty_id`,`email`),
  KEY `hotel_customers_group_name_faculty_id_index` (`group_name`,`faculty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `hotel_customers` WRITE;
/*!40000 ALTER TABLE `hotel_customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `hotel_customers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_05_24_000001_add_role_to_users_table',1),(6,'2026_05_24_000002_create_faculties_table',1),(7,'2026_05_24_000003_add_name_parts_to_users_table',1),(8,'2026_05_24_000004_add_status_and_phone_to_users_table',1),(9,'2026_05_24_000005_create_students_table',1),(10,'2026_05_25_000001_create_student_groups_table',1),(11,'2026_06_03_000001_add_faculty_id_to_student_groups_table',1),(12,'2026_06_08_000001_create_tasks_table',1),(13,'2026_06_10_000001_add_assigned_to_to_tasks_table',1),(14,'2026_06_13_000001_create_front_desk_canvases_table',2),(15,'2026_06_13_000002_create_front_desk_activities_table',2),(16,'2026_06_26_062221_add_student_id_to_tasks_table',3),(17,'2024_01_01_000001_create_front_desk_canvases_table',4),(18,'2024_01_01_000002_create_front_desk_activities_table',4),(19,'2026_07_04_000001_add_housekeeping_role_to_tasks_table',5),(20,'2026_07_04_000002_create_student_group_roles_table',6),(21,'2026_07_04_000003_migrate_existing_roles_to_pivot_table',6),(22,'2026_07_06_000001_create_group_settings_table',7),(23,'2026_07_13_000001_fix_nulls_email_verified_and_task_student_links',8),(24,'2026_07_13_000002_replace_null_optional_fields_with_empty_string',8),(25,'2026_07_14_064325_add_customizations_to_group_settings_table',8),(26,'2026_07_14_200000_create_faculty_classes_and_link_students',8),(27,'2026_07_14_210000_make_student_groups_role_nullable',8),(28,'2026_07_14_220000_backfill_and_require_user_tokens',8),(29,'2026_07_14_230000_add_avatar_to_users_table',8),(30,'2026_07_15_004500_add_last_seen_at_to_users_table',8),(31,'2026_07_15_010000_create_hotel_template_builder_tables',8),(32,'2026_07_20_010000_create_hotel_customers_table',8),(33,'2026_07_23_000001_rename_suspended_status_to_inactive',8),(34,'2026_07_23_000002_add_block_to_faculties_table',8),(35,'2026_07_25_200000_normalize_template_customizations',8),(36,'2026_07_25_214500_drop_unused_laravel_tables',8),(37,'2026_07_25_215100_prune_redundant_template_version_rows',9),(38,'2026_07_25_220000_widen_template_styles_to_elements',9),(39,'2026_07_25_221200_version_id_zero_for_live',9);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `student_group_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_group_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_group_id` bigint(20) unsigned NOT NULL,
  `role` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_group_roles_student_group_id_role_unique` (`student_group_id`,`role`),
  CONSTRAINT `student_group_roles_student_group_id_foreign` FOREIGN KEY (`student_group_id`) REFERENCES `student_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `student_group_roles` WRITE;
/*!40000 ALTER TABLE `student_group_roles` DISABLE KEYS */;
INSERT INTO `student_group_roles` VALUES (1,1,'front_desk','2026-07-03 10:40:44','2026-07-03 10:40:44'),(2,2,'restaurant_management','2026-07-03 10:40:44','2026-07-03 10:40:44'),(3,3,'room_management','2026-07-03 10:40:44','2026-07-03 10:40:44'),(4,4,'maintenance','2026-07-03 10:40:44','2026-07-03 10:40:44'),(5,5,'front_desk','2026-07-03 10:40:44','2026-07-03 10:40:44'),(6,6,'restaurant_management','2026-07-03 10:40:44','2026-07-03 10:40:44'),(7,7,'room_management','2026-07-03 10:40:44','2026-07-03 10:40:44'),(8,8,'maintenance','2026-07-03 10:40:44','2026-07-03 10:40:44'),(9,9,'front_desk','2026-07-03 10:40:44','2026-07-03 10:40:44'),(10,10,'restaurant_management','2026-07-03 10:40:44','2026-07-03 10:40:44');
/*!40000 ALTER TABLE `student_group_roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `student_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `faculty_id` bigint(20) unsigned DEFAULT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_groups_group_name_student_id_unique` (`group_name`,`student_id`),
  KEY `student_groups_student_id_foreign` (`student_id`),
  KEY `student_groups_faculty_id_foreign` (`faculty_id`),
  CONSTRAINT `student_groups_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_groups_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `student_groups` WRITE;
/*!40000 ALTER TABLE `student_groups` DISABLE KEYS */;
INSERT INTO `student_groups` VALUES (1,'Luxe_Hotel',2,4,'front_desk','2026-06-12 08:58:20','2026-06-12 08:58:20'),(2,'Luxe_Hotel',2,5,'restaurant_management','2026-06-12 08:58:20','2026-06-12 08:58:20'),(3,'Luxe_Hotel',2,6,'room_management','2026-06-12 08:58:20','2026-06-12 08:58:20'),(4,'Luxe_Hotel',2,3,'maintenance','2026-06-12 08:58:20','2026-06-12 08:58:20'),(5,'Group Alpha',3,7,'front_desk','2026-06-12 11:09:32','2026-06-12 11:09:32'),(6,'Group Alpha',3,8,'restaurant_management','2026-06-12 11:09:32','2026-06-12 11:09:32'),(7,'Group Alpha',3,9,'room_management','2026-06-12 11:09:32','2026-06-12 11:09:32'),(8,'Group Alpha',3,10,'maintenance','2026-06-12 11:09:32','2026-06-12 11:09:32'),(9,'Tibaghak',4,12,'front_desk','2026-07-02 00:24:19','2026-07-02 00:24:19'),(10,'Tibaghak',4,11,'restaurant_management','2026-07-02 00:24:19','2026-07-02 00:24:19');
/*!40000 ALTER TABLE `student_groups` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `faculty_id` bigint(20) unsigned DEFAULT NULL,
  `faculty_class_id` bigint(20) unsigned DEFAULT NULL,
  `student_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_student_id_unique` (`student_id`),
  KEY `students_user_id_foreign` (`user_id`),
  KEY `students_faculty_id_foreign` (`faculty_id`),
  KEY `students_faculty_class_id_foreign` (`faculty_class_id`),
  CONSTRAINT `students_faculty_class_id_foreign` FOREIGN KEY (`faculty_class_id`) REFERENCES `faculty_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (3,6,NULL,NULL,'12345','2026-06-12 08:44:06','2026-06-12 08:44:06'),(4,7,NULL,NULL,'12346','2026-06-12 08:44:07','2026-06-12 08:44:07'),(5,8,NULL,NULL,'12347','2026-06-12 08:44:07','2026-06-12 08:44:07'),(6,9,NULL,NULL,'12348','2026-06-12 08:44:07','2026-06-12 08:44:07'),(7,11,NULL,NULL,'STU001','2026-06-12 11:09:32','2026-06-12 11:09:32'),(8,12,NULL,NULL,'STU002','2026-06-12 11:09:32','2026-06-12 11:09:32'),(9,13,NULL,NULL,'STU003','2026-06-12 11:09:32','2026-06-12 11:09:32'),(10,14,NULL,NULL,'STU004','2026-06-12 11:09:32','2026-06-12 11:09:32'),(11,16,NULL,NULL,'2024-001','2026-07-02 00:21:20','2026-07-02 00:21:20'),(12,17,NULL,NULL,'2024-002','2026-07-02 00:21:23','2026-07-02 00:21:23'),(13,18,NULL,NULL,'2300354','2026-07-08 01:09:27','2026-07-08 01:09:27');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `faculty_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `role` enum('front_desk','restaurant_management','room_management','maintenance','housekeeping') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_faculty_id_foreign` (`faculty_id`),
  KEY `tasks_assigned_to_foreign` (`assigned_to`),
  KEY `tasks_student_id_foreign` (`student_id`),
  CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,3,7,11,'front_desk','Sample Task for Front desk','This is a description of the sample task for front_desk','2026-06-14','high','active','2026-06-12 11:09:32','2026-06-12 11:09:32'),(2,3,8,12,'restaurant_management','Sample Task for Restaurant management','This is a description of the sample task for restaurant_management','2026-06-14','high','active','2026-06-12 11:09:32','2026-06-12 11:09:32'),(3,3,9,13,'room_management','Sample Task for Room management','This is a description of the sample task for room_management','2026-06-14','high','active','2026-06-12 11:09:32','2026-06-12 11:09:32'),(4,3,10,14,'maintenance','Sample Task for Maintenance','This is a description of the sample task for maintenance','2026-06-14','high','active','2026-06-12 11:09:32','2026-06-12 11:09:32'),(5,2,NULL,NULL,'front_desk','Review check-in procedures','Review and practice standard check-in procedures for guests','2026-06-27','high','active','2026-06-25 21:38:16','2026-06-25 21:38:16'),(6,4,NULL,NULL,'front_desk','Review check-in procedures','Review and practice standard check-in procedures for guests','2026-07-03','high','active','2026-07-02 00:26:56','2026-07-02 00:26:56'),(7,4,NULL,NULL,'front_desk','Update guest records system','Update and maintain accurate guest information in the system','2026-07-03','medium','active','2026-07-02 00:26:56','2026-07-02 00:26:56'),(8,4,NULL,NULL,'front_desk','Handle guest complaints','Learn proper procedures for handling guest complaints professionally','2026-07-03','high','active','2026-07-02 00:26:56','2026-07-02 00:26:56'),(9,4,NULL,NULL,'front_desk','Manage room reservations','Process and confirm room reservations accurately','2026-07-03','medium','active','2026-07-02 00:26:56','2026-07-02 00:26:56'),(10,4,NULL,NULL,'front_desk','Process check-out procedures','Complete guest check-out and billing procedures','2026-07-03','medium','active','2026-07-02 00:26:56','2026-07-02 00:26:56'),(11,4,NULL,NULL,'front_desk','Phone etiquette training','Practice professional phone communication with guests','2026-07-03','low','active','2026-07-02 00:26:56','2026-07-02 00:26:56');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `team_role_template_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_role_template_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_role_template_id` bigint(20) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `selected_template` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `label` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_role_template_versions_unique` (`team_role_template_id`,`version`),
  KEY `team_role_template_versions_created_by_foreign` (`created_by`),
  CONSTRAINT `team_role_template_versions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `team_role_template_versions_team_role_template_id_foreign` FOREIGN KEY (`team_role_template_id`) REFERENCES `team_role_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `team_role_template_versions` WRITE;
/*!40000 ALTER TABLE `team_role_template_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_role_template_versions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `team_role_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_role_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `faculty_id` bigint(20) unsigned NOT NULL,
  `role` varchar(64) NOT NULL,
  `selected_template` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_role_templates_unique` (`group_name`,`faculty_id`,`role`),
  KEY `team_role_templates_updated_by_foreign` (`updated_by`),
  KEY `team_role_templates_faculty_id_group_name_index` (`faculty_id`,`group_name`),
  CONSTRAINT `team_role_templates_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_role_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `team_role_templates` WRITE;
/*!40000 ALTER TABLE `team_role_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_role_templates` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `team_template_edit_grants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_template_edit_grants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `faculty_id` bigint(20) unsigned NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `role` varchar(64) NOT NULL,
  `granted_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_template_edit_grants_unique` (`faculty_id`,`group_name`,`student_id`,`role`),
  KEY `team_template_edit_grants_student_id_foreign` (`student_id`),
  KEY `team_template_edit_grants_granted_by_foreign` (`granted_by`),
  CONSTRAINT `team_template_edit_grants_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_template_edit_grants_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `team_template_edit_grants_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `team_template_edit_grants` WRITE;
/*!40000 ALTER TABLE `team_template_edit_grants` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_template_edit_grants` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `template_content_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_content_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `content_item_id` bigint(20) unsigned NOT NULL,
  `field_name` varchar(120) NOT NULL,
  `field_value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tpl_content_fields_idx` (`content_item_id`,`field_name`),
  CONSTRAINT `template_content_fields_content_item_id_foreign` FOREIGN KEY (`content_item_id`) REFERENCES `template_content_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `template_content_fields` WRITE;
/*!40000 ALTER TABLE `template_content_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `template_content_fields` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `template_content_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_content_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_role_template_id` bigint(20) unsigned NOT NULL,
  `version_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `collection` varchar(64) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `item_ref` varchar(120) DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_content_items_parent_id_foreign` (`parent_id`),
  KEY `tpl_content_collection_idx` (`team_role_template_id`,`version_id`,`collection`),
  KEY `template_content_items_version_id_index` (`version_id`),
  CONSTRAINT `template_content_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `template_content_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `template_content_items_team_role_template_id_foreign` FOREIGN KEY (`team_role_template_id`) REFERENCES `team_role_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `template_content_items` WRITE;
/*!40000 ALTER TABLE `template_content_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `template_content_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `template_elements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_elements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_role_template_id` bigint(20) unsigned NOT NULL,
  `version_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `element_key` text NOT NULL,
  `hms_id` varchar(120) DEFAULT NULL,
  `page` varchar(64) DEFAULT NULL,
  `free_position` tinyint(1) NOT NULL DEFAULT 0,
  `move_mode` varchar(40) DEFAULT NULL,
  `keep_fixed` tinyint(1) NOT NULL DEFAULT 0,
  `text_content` text DEFAULT NULL,
  `icon_class` varchar(255) DEFAULT NULL,
  `display_value` varchar(255) DEFAULT NULL,
  `image_src` varchar(500) DEFAULT NULL,
  `image_background` varchar(500) DEFAULT NULL,
  `color` varchar(120) DEFAULT NULL,
  `background_color` varchar(120) DEFAULT NULL,
  `font_family` varchar(255) DEFAULT NULL,
  `font_weight` varchar(40) DEFAULT NULL,
  `font_style` varchar(40) DEFAULT NULL,
  `text_decoration` varchar(80) DEFAULT NULL,
  `font_size` varchar(40) DEFAULT NULL,
  `text_align` varchar(40) DEFAULT NULL,
  `line_height` varchar(40) DEFAULT NULL,
  `letter_spacing` varchar(40) DEFAULT NULL,
  `background_size` varchar(80) DEFAULT NULL,
  `background_position` varchar(80) DEFAULT NULL,
  `background_repeat` varchar(40) DEFAULT NULL,
  `padding` varchar(80) DEFAULT NULL,
  `padding_top` varchar(40) DEFAULT NULL,
  `padding_right` varchar(40) DEFAULT NULL,
  `padding_bottom` varchar(40) DEFAULT NULL,
  `padding_left` varchar(40) DEFAULT NULL,
  `margin` varchar(80) DEFAULT NULL,
  `margin_top` varchar(40) DEFAULT NULL,
  `margin_right` varchar(40) DEFAULT NULL,
  `margin_bottom` varchar(40) DEFAULT NULL,
  `margin_left` varchar(40) DEFAULT NULL,
  `border` varchar(120) DEFAULT NULL,
  `border_radius` varchar(80) DEFAULT NULL,
  `box_shadow` varchar(255) DEFAULT NULL,
  `opacity` varchar(20) DEFAULT NULL,
  `position` varchar(40) DEFAULT NULL,
  `top` varchar(40) DEFAULT NULL,
  `left_pos` varchar(40) DEFAULT NULL,
  `right_pos` varchar(40) DEFAULT NULL,
  `bottom_pos` varchar(40) DEFAULT NULL,
  `width` varchar(40) DEFAULT NULL,
  `height` varchar(40) DEFAULT NULL,
  `max_width` varchar(40) DEFAULT NULL,
  `min_width` varchar(40) DEFAULT NULL,
  `min_height` varchar(40) DEFAULT NULL,
  `z_index` varchar(20) DEFAULT NULL,
  `transform` varchar(255) DEFAULT NULL,
  `display` varchar(40) DEFAULT NULL,
  `overflow` varchar(40) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tpl_elements_template_version_idx` (`team_role_template_id`,`version_id`),
  KEY `template_elements_version_id_index` (`version_id`),
  CONSTRAINT `template_elements_team_role_template_id_foreign` FOREIGN KEY (`team_role_template_id`) REFERENCES `team_role_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `template_elements` WRITE;
/*!40000 ALTER TABLE `template_elements` DISABLE KEYS */;
/*!40000 ALTER TABLE `template_elements` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `template_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_role_template_id` bigint(20) unsigned NOT NULL,
  `version_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `element_key` text NOT NULL,
  `kind` varchar(40) NOT NULL DEFAULT 'src',
  `image_path` varchar(500) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tpl_images_template_version_idx` (`team_role_template_id`,`version_id`),
  KEY `template_images_version_id_index` (`version_id`),
  CONSTRAINT `template_images_team_role_template_id_foreign` FOREIGN KEY (`team_role_template_id`) REFERENCES `team_role_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `template_images` WRITE;
/*!40000 ALTER TABLE `template_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `template_images` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `template_layouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_layouts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_role_template_id` bigint(20) unsigned NOT NULL,
  `version_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `section_id` varchar(120) NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tpl_layouts_template_version_idx` (`team_role_template_id`,`version_id`),
  KEY `template_layouts_version_id_index` (`version_id`),
  CONSTRAINT `template_layouts_team_role_template_id_foreign` FOREIGN KEY (`team_role_template_id`) REFERENCES `team_role_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `template_layouts` WRITE;
/*!40000 ALTER TABLE `template_layouts` DISABLE KEYS */;
/*!40000 ALTER TABLE `template_layouts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'student',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,'Dean','System','','Dean','dean@hms.edu','',NULL,'2026-07-27 07:06:12','$2y$10$96Wi2P3IwbPcuCJTC0DUMegpOCHLOZLedWtkMog0kPR8b18nLl/SW','dean','active','1N0I0xzQWlDZG8LLOmzdWnZ3lqz9bfNNMkssrYfcw6kRMnRn6hMODjECahn1','2026-06-12 08:30:26','2026-07-03 08:55:53',NULL),(5,'Faculty T. Test','Faculty','T.','Test','facultytest1@hms.edu','923654745',NULL,'2026-07-27 07:06:12','$2y$10$5X5xmF/GJXUFQ6EPN0Y2OOIda3PC/EhVgqJPHmQ/e1fbxbTiGTS4.','faculty','active','ssuaGslaZAIuUYWJhcVhDJWKEOn28RyJZJ5Lbv7aAaIsjWHnvn2cEcHdo96l','2026-06-12 08:33:34','2026-06-12 08:33:34',NULL),(6,'test G. student','test','G.','student','teststudent@gmail.com','926191478',NULL,'2026-07-27 07:06:12','$2y$10$Dawfma8X42BmF/Y1xLrSDuKSOf4lkGeUE0PAJ/QsPJ30JEInECQ6y','student','active','2teulKQYnEoZtVxQBEgnFamq6IlazVJgKe7Hcf7YkRw9qMANIjmtyrBGHaYG','2026-06-12 08:44:06','2026-06-12 08:44:06',NULL),(7,'test T. student1','test','T.','student1','teststudent1@gmail.com','9261254365',NULL,'2026-07-27 07:06:12','$2y$10$.4ZtVmZXDoVx7fbScnLqYOV7WzcFG5Xi18Y2Sd3w10GH2n0z6.aQW','student','active','6ceAInXmTMSyxg0q2CVxQcpqGHwuxz2K2PyLF2wpmclna4ss2yedqPMpH12k','2026-06-12 08:44:07','2026-06-12 08:44:07',NULL),(8,'test T. student2','test','T.','student2','teststudent2@gmail.com','9261254366',NULL,'2026-07-27 07:06:12','$2y$10$VZfEaX/bxjvHQA9emZ6d6ODskcB2mVXziqJwnLg0vAmltuU3cblLi','student','active','qwnlCZIgSfrcsbi6g104V85MHsCeAzDCsluVzfAhuMem0E25ZDLcBvlqGRuK','2026-06-12 08:44:07','2026-06-12 08:44:07',NULL),(9,'test T. student3','test','T.','student3','teststudent3@gmail.com','9261254367',NULL,'2026-07-27 07:06:12','$2y$10$2Dkiszn/Fst5c7OHCLIqae2bTwTLzrr3OzVaGJOBzUUR3fuNm5j8K','student','active','OlucrWbqf9hNkQcNTyU8oLgs4mlm6ZPKhTrIHrOBtMJDFrUn1wpMDOkgkUGM','2026-06-12 08:44:07','2026-06-12 08:44:07',NULL),(10,'Faculty Teacher','','','','faculty@hms.edu','',NULL,'2026-07-27 07:06:12','$2y$10$PlKuu7DP.p0Nuxk8Mn.JIO26W59NnfTWXWNjw610jyPXQD0NHT31m','faculty','active','VNLcIhcP0XgXfv0fsnxOkPSr8omQLWehI8XgLAnBIh4Mn49lYcYysZWcqN8o','2026-06-12 11:09:32','2026-06-12 11:09:32',NULL),(11,'Student Front desk','','','','student_front_desk@hms.edu','',NULL,'2026-07-27 07:06:12','$2y$10$4vR1KZhWvd32EjJ0GlZTq.VnvXkVkdBRzdtIHWLM7YltK/HSDex62','student','active','HHJCbTCYywYaB65RTDL0BM2xZCYmQctc3TZJCMMJaPfcl7U3neWikhH40zna','2026-06-12 11:09:32','2026-06-25 09:59:55',NULL),(12,'Student Restaurant management','','','','student_restaurant_management@hms.edu','',NULL,'2026-07-27 07:06:12','$2y$10$4vGfT8cafhQXKpkhmM2fuOo4DvhqphCzZw9moJfClQRV9n0yggfU2','student','active','YF9MMPeKnREAjEHGeqcQebmA8G7UR2RimnoxE9mNi5VD3E1IZZooClibBT7U','2026-06-12 11:09:32','2026-06-14 06:33:54',NULL),(13,'Student Room management','','','','student_room_management@hms.edu','',NULL,'2026-07-27 07:06:12','$2y$10$KRJYHfoGySotCh9eDiQufeFqw/9GyCSo.u.7T3MbWG0wooLQ.KC9S','student','active','waE6UmaRekRKp7lcDr7NLKbOpjbR4AcIKPcg0Eyn4DGVaE5BPrtDL090D3jh','2026-06-12 11:09:32','2026-06-14 06:37:27',NULL),(14,'Student Maintenance','','','','student_maintenance@hms.edu','',NULL,'2026-07-27 07:06:12','$2y$10$0f/Tnnj5ccavl9SvTmCtuehV8IRM6y1EWVfNjjBYmLszKq9qk5DBu','student','active','TQ3jgtJB8eIOWkz5C3h2oZV3LLiCbdNjgd8eYUuY3F30olIpMEPKU5s8KvLX','2026-06-12 11:09:32','2026-06-26 03:47:40',NULL),(15,'Carolina Saraos','Carolina','','Saraos','saraos@hms.edu','9266511423',NULL,'2026-07-27 07:06:12','$2y$10$8eKYxEYzQeXV1J7/abPVieS7iZgRR1J/snABkaqq44cxzG5duKpgK','faculty','active','pRlbiKh4cqstmX5Jm7sx6vIEXdhFpLwCLzQhlOdA6D4b6fAkhK2DEMZPDPll','2026-07-02 00:17:39','2026-07-02 00:17:39',NULL),(16,'Juan M. Dela Cruz','Juan','M.','Dela Cruz','jdelacruz@hms.edu','09171234567',NULL,'2026-07-27 07:06:12','$2y$10$fd8miDqVynQVeEeQ79WcLeEjjggxcZ/mbcjpgZUz.s/RE760/bCZm','student','active','13fWShWAA7FBN3dFxgYWXzKkTv9hSv09phFCe1NWz91NCbnNXEDHiqs7jqjA','2026-07-02 00:21:20','2026-07-05 10:12:43',NULL),(17,'Maria Santos','Maria','','Santos','msantos@hms.edu','',NULL,'2026-07-27 07:06:12','$2y$10$7hvV6gnJoIV8XW75fpfy/.Yu3soVZr.TftT7NWieM8yFYZb5nkKZG','student','active','2gagK7RkYZnNLG3n0kU1wFn9xg5vAPO9iDMXYlwny8fPSLo2VgN9aMJQoNh6','2026-07-02 00:21:23','2026-07-02 00:29:12',NULL),(18,'Jun A. Orao','Jun','A.','Orao','jun@hms.edu','926191345',NULL,'2026-07-27 15:17:59','$2y$10$Qx4cCySXQNcfgeDbO.bwQ.xBIbm12ilO4cwddYZXxpIA6OZGMOjf.','student','active','ytRuxjDqZs3lbcbjH45maewsoUsbOdgQvRz4sxrCcaFHD6heWUk4LZCaE3Qn','2026-07-08 01:09:27','2026-07-27 07:17:59','2026-07-27 07:17:59');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

