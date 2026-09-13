-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: scb_school
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.22.04.2

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
-- Table structure for table `admission_enquiries`
--

DROP TABLE IF EXISTS `admission_enquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admission_enquiries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guardian_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intended_level` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intended_term` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intended_year` smallint unsigned DEFAULT NULL,
  `preferred_contact_method` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `consent_confirmed` tinyint(1) NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `first_viewed_at` timestamp NULL DEFAULT NULL,
  `viewed_by` bigint unsigned DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `source_ip_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admission_enquiries_reference_code_unique` (`reference_code`),
  KEY `admission_enquiries_status_created_at_index` (`status`,`created_at`),
  KEY `admission_enquiries_assigned_to_status_created_at_index` (`assigned_to`,`status`,`created_at`),
  KEY `admission_enquiries_email_index` (`email`),
  KEY `admission_enquiries_telephone_index` (`telephone`),
  KEY `admission_enquiries_intended_year_index` (`intended_year`),
  KEY `admission_enquiries_viewed_by_foreign` (`viewed_by`),
  KEY `admission_enquiries_first_viewed_at_index` (`first_viewed_at`),
  CONSTRAINT `admission_enquiries_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admission_enquiries_viewed_by_foreign` FOREIGN KEY (`viewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admission_enquiries`
--

LOCK TABLES `admission_enquiries` WRITE;
/*!40000 ALTER TABLE `admission_enquiries` DISABLE KEYS */;
INSERT INTO `admission_enquiries` VALUES (1,'ADM-LOCAL-0001','Admissions Enquiry','guardian@example.test','+27 00 000 0001','Primary',NULL,2027,'email','Please share the next admissions steps.',1,'new','2026-08-21 19:20:37',1,1,'12ca17b49af2289436f303e0166030a21e525d266e209267433801a8fd4071a0','0a96d96da99c0ca483c66d2d366494a428f37b496cce7a8b55f7e7568d409e2c',NULL,NULL,'2026-08-21 18:47:22','2026-08-21 19:20:37',NULL);
/*!40000 ALTER TABLE `admission_enquiries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admission_enquiry_notes`
--

DROP TABLE IF EXISTS `admission_enquiry_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admission_enquiry_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admission_enquiry_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sensitive` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admission_enquiry_notes_user_id_foreign` (`user_id`),
  KEY `admission_enquiry_notes_admission_enquiry_id_created_at_index` (`admission_enquiry_id`,`created_at`),
  CONSTRAINT `admission_enquiry_notes_admission_enquiry_id_foreign` FOREIGN KEY (`admission_enquiry_id`) REFERENCES `admission_enquiries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admission_enquiry_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admission_enquiry_notes`
--

LOCK TABLES `admission_enquiry_notes` WRITE;
/*!40000 ALTER TABLE `admission_enquiry_notes` DISABLE KEYS */;
INSERT INTO `admission_enquiry_notes` VALUES (1,1,1,'Local-only internal note for workflow testing.',1,'2026-08-21 18:47:22','2026-08-21 18:47:22');
/*!40000 ALTER TABLE `admission_enquiry_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `link_label` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_dismissible` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_created_by_foreign` (`created_by`),
  KEY `announcements_updated_by_foreign` (`updated_by`),
  KEY `announcements_status_starts_at_ends_at_index` (`status`,`starts_at`,`ends_at`),
  KEY `announcements_severity_status_index` (`severity`,`status`),
  CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `announcements_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'Admissions Notice','Use the admissions page to send an enquiry and receive guidance from the school team.','info','draft','2026-08-20 22:31:25','2026-09-21 22:31:25',NULL,NULL,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:25',NULL);
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` bigint unsigned DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `request_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_actor_id_created_at_index` (`actor_id`,`created_at`),
  KEY `audit_logs_subject_lookup` (`subject_type`,`subject_id`,`created_at`),
  KEY `audit_logs_action_created_at_index` (`action`,`created_at`),
  KEY `audit_logs_request_id_index` (`request_id`),
  CONSTRAINT `audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'database.seeded','database',NULL,'Local MySQL seed data installed.',NULL,'{\"environment\": \"local\"}',NULL,NULL,'8035f9e3-5de3-459e-9eaf-cefccf5a018b','2026-08-21 18:47:22'),(2,1,'admission_enquiry.first_viewed','App\\Models\\AdmissionEnquiry',1,'Admission enquiry opened: ADM-LOCAL-0001','{\"viewed_by\": null, \"first_viewed_at\": null}','{\"viewed_by\": 1, \"first_viewed_at\": \"2026-08-21T21:20:37.688312Z\"}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','d8ae2876-1bed-4a10-bbba-118af4dc3e6f','2026-08-21 19:20:37'),(3,1,'user.created','App\\Models\\User',3,'User created from visible admin users screen: content.test@example.com',NULL,'{\"name\": \"content test\", \"email\": \"content.test@example.com\", \"roles\": [\"content-editor\"], \"is_active\": true}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','8421a0f5-cfad-4779-abe8-42daacbf512e','2026-08-21 19:42:16'),(4,1,'database.seeded','database',NULL,'Local MySQL seed data installed.',NULL,'{\"environment\": \"local\"}',NULL,NULL,'98803643-bf38-40ab-b754-a24688ff76ff','2026-08-21 20:11:33'),(5,1,'user.roles_updated','App\\Models\\User',3,'User roles updated: content.test@example.com','{\"roles\": [\"content-editor\"]}','{\"roles\": [\"school-administrator\"]}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','9a1ae046-97df-45de-b3cb-34d68157c1ce','2026-08-21 20:14:33'),(6,1,'user.profile_updated','App\\Models\\User',3,'User profile updated: content.test@example.com','{\"name\": \"content test\", \"email\": \"content.test@example.com\"}','{\"name\": \"content test\", \"email\": \"content.test@example.com\"}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','1fdaa273-a1e3-43fd-bc26-661a8f5147ec','2026-08-21 20:14:46'),(7,1,'user.status_updated','App\\Models\\User',3,'User status updated: content.test@example.com to Disabled','{\"is_active\": true}','{\"is_active\": false}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','eebd5258-5532-4525-a24d-0f8b9d7b5ceb','2026-08-21 20:14:50'),(8,1,'user.status_updated','App\\Models\\User',3,'User status updated: content.test@example.com to Active','{\"is_active\": false}','{\"is_active\": true}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','dda614b3-7279-49eb-a2ba-29cf4581999f','2026-08-21 20:15:02'),(9,1,'database.seeded','database',NULL,'Local MySQL seed data installed.',NULL,'{\"environment\": \"local\"}',NULL,NULL,'f77a7edc-68f6-4bc2-80c6-d32f3b3c0343','2026-08-21 21:10:28'),(10,1,'contact_message.first_viewed','App\\Models\\ContactMessage',1,'Contact message opened: CON-LOCAL-0001','{\"viewed_by\": null, \"first_viewed_at\": null}','{\"viewed_by\": 1, \"first_viewed_at\": \"2026-08-21T23:35:41.469259Z\"}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','aecf2574-c772-437e-8ce1-0ff485e9b1ed','2026-08-21 21:35:41'),(11,1,'settings.updated','App\\Models\\SiteSetting',NULL,'Site settings updated from visible admin settings screen.','{\"identity.motto\": \"Learning with purpose\", \"contact.address\": \"\", \"analytics.enabled\": \"\", \"analytics.site_id\": \"\", \"contact.telephone\": \"\", \"seo.default_title\": \"St. Charles Borromeo Pre & Primary School\", \"analytics.provider\": \"none\", \"identity.school_name\": \"St. Charles Borromeo Pre & Primary School\", \"contact.primary_email\": \"\", \"seo.default_description\": \"Discover learning, school life and admissions at St. Charles Borromeo Pre & Primary School.\"}','{\"identity.motto\": \"Learning with purpose\", \"contact.address\": \"\", \"analytics.enabled\": \"1\", \"analytics.site_id\": \"scb.ac.tz\", \"contact.telephone\": \"\", \"seo.default_title\": \"St. Charles Borromeo Pre & Primary School\", \"analytics.provider\": \"plausible\", \"identity.school_name\": \"St. Charles Borromeo Pre & Primary School\", \"contact.primary_email\": \"\", \"seo.default_description\": \"Discover learning, school life and admissions at St. Charles Borromeo Pre & Primary School.\"}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','19e576c2-7ee4-4422-9093-f82cec18c796','2026-08-21 22:07:55'),(12,1,'database.seeded','database',NULL,'Local MySQL seed data installed.',NULL,'{\"environment\": \"local\"}',NULL,NULL,'0de157ef-19ee-44ea-ba8a-d34b07d3637b','2026-08-21 22:31:25'),(13,1,'post.created','App\\Models\\Post',5,'News story draft created: dgjsfjxfjxfhjm',NULL,'{\"body\": \"xfHJzdtgjxc \\r\\nxjhv mhk\", \"slug\": \"dgjsfjxfjxfhjm\", \"title\": \"dgjsfjxfjxfhjm\", \"status\": \"draft\", \"excerpt\": \"fXHzbnxfgm vmmhvmhv\", \"author_id\": 1, \"created_by\": 1, \"updated_by\": 1}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','b2a47166-7cd9-4ac5-b145-85f891be5cfb','2026-08-23 11:51:34'),(14,1,'post.updated','App\\Models\\Post',5,'News story updated from visible admin editor: dgjsfjxfjxfhjm','{\"body\": \"xfHJzdtgjxc \\r\\nxjhv mhk\", \"tags\": [], \"title\": \"dgjsfjxfjxfhjm\", \"status\": \"draft\", \"excerpt\": \"fXHzbnxfgm vmmhvmhv\", \"is_featured\": false, \"published_at\": null, \"scheduled_at\": null, \"post_category_id\": null}','{\"body\": \"xfHJzdtgjxc \\r\\nxjhv mhk\", \"tags\": [], \"title\": \"dgjsfjxfjxfhjm\", \"status\": \"published\", \"excerpt\": \"fXHzbnxfgm vmmhvmhv\", \"is_featured\": false, \"published_at\": \"2026-08-17T09:07:00.000000Z\", \"scheduled_at\": null, \"post_category_id\": \"3\"}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','9fdf0b36-77ee-496a-831c-eb10c8186d16','2026-08-23 11:52:40'),(15,1,'event.created','App\\Models\\Event',4,'Event draft created: Official website launch',NULL,'{\"slug\": \"official-website-launch\", \"title\": \"Official website launch\", \"timezone\": \"UTC\", \"starts_at\": \"2026-08-25T00:03:00.000000Z\", \"created_by\": 1, \"updated_by\": 1, \"venue_name\": \"St. Charles Borromeo hall\", \"event_state\": \"scheduled\", \"publication_status\": \"draft\"}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','da6119f3-5ac3-42ae-93d8-ba5527183556','2026-08-23 11:54:52'),(16,1,'event.updated','App\\Models\\Event',4,'Event updated from visible admin editor: Official website launch','{\"body\": null, \"slug\": \"official-website-launch\", \"title\": \"Official website launch\", \"ends_at\": null, \"summary\": null, \"starts_at\": \"2026-08-25T00:03:00.000000Z\", \"venue_name\": \"St. Charles Borromeo hall\", \"published_at\": null, \"event_category_id\": null, \"publication_status\": \"draft\"}','{\"body\": \"cgjcghvmcjb,\", \"slug\": \"official-website-launch\", \"title\": \"Official website launch\", \"ends_at\": \"2026-08-25T08:05:00.000000Z\", \"summary\": null, \"starts_at\": \"2026-08-25T00:03:00.000000Z\", \"venue_name\": \"St. Charles Borromeo hall\", \"published_at\": null, \"event_category_id\": null, \"publication_status\": \"draft\"}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','bdc5f568-7313-40bd-a866-2fd3bba22a26','2026-08-23 11:56:56'),(17,1,'event.updated','App\\Models\\Event',4,'Event updated from visible admin editor: Official website launch','{\"body\": \"cgjcghvmcjb,\", \"slug\": \"official-website-launch\", \"title\": \"Official website launch\", \"ends_at\": \"2026-08-25T08:05:00.000000Z\", \"summary\": null, \"starts_at\": \"2026-08-25T00:03:00.000000Z\", \"venue_name\": \"St. Charles Borromeo hall\", \"published_at\": null, \"event_category_id\": null, \"publication_status\": \"draft\"}','{\"body\": \"cgjcghvmcjb,\", \"slug\": \"official-website-launch\", \"title\": \"Official website launch\", \"ends_at\": \"2026-08-25T08:05:00.000000Z\", \"summary\": null, \"starts_at\": \"2026-08-25T00:03:00.000000Z\", \"venue_name\": \"St. Charles Borromeo hall\", \"published_at\": \"2026-08-23T13:57:47.000000Z\", \"event_category_id\": null, \"publication_status\": \"published\"}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','15a40226-0261-43b9-9788-3bf6036db90c','2026-08-23 11:57:47'),(18,1,'post.created','App\\Models\\Post',6,'News story draft created: More Than a School: How the Spirit of St. Charles Borromeo Shapes Every Child We Educate',NULL,'{\"body\": \"At St. Charles Borromeo Pre & Primary School, Mbeya, education goes beyond academic achievement. Our school is deeply inspired by the life and philosophy of St. Charles Borromeo, whose legacy emphasized faith, discipline, education, service and strong moral character. These principles continue to shape the way we teach, guide and nurture every child.\\r\\n\\r\\nOur philosophy is reflected in the values of Faith, Discipline and Excellence. We believe that a good education should develop both the mind and character of a child. Pupils are encouraged to become respectful, responsible, confident and hardworking while building a strong academic foundation for their future.\\r\\n\\r\\nDiscipline at St. Charles Borromeo is not simply about following rules. It is about helping children develop positive habits such as punctuality, responsibility, respect, perseverance and self-control. These qualities prepare our pupils not only for examinations, but also for secondary education and life beyond the classroom.\\r\\n\\r\\nAcademic excellence remains an important part of our mission. Our teachers work to create an environment where pupils are encouraged to learn, ask questions, discover their talents and develop confidence in their abilities. We believe every child has potential that can grow when supported by dedicated teachers, strong values and an encouraging school community.\\r\\n\\r\\nFaith also remains at the heart of our school culture. Through prayer, respect, compassion and service to others, pupils learn that education should be accompanied by integrity and responsibility. We want our children to understand that true success is not measured only by what they achieve, but also by the kind of people they become.\\r\\n\\r\\nFor parents, choosing a school is about much more than classrooms and examinations. It is about choosing an environment that will influence a child\'s future. At St. Charles Borromeo Pre & Primary School, we are committed to providing that strong foundation—where children can grow academically, spiritually and socially.\", \"slug\": \"more-than-a-school-how-the-spirit-of-st-charles-borromeo-shapes-every-child-we-educate\", \"title\": \"More Than a School: How the Spirit of St. Charles Borromeo Shapes Every Child We Educate\", \"status\": \"draft\", \"excerpt\": \"At St. Charles Borromeo Pre & Primary School, Mbeya, education goes beyond academic achievement. Our school is deeply inspired by the life and philosophy of St. Charles Borromeo, whose legacy emphasized faith, discipline, education, service and strong moral character. These principles continue to shape the way we teach, guide and nurture every child.\", \"author_id\": 1, \"created_by\": 1, \"updated_by\": 1}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','618ed5ad-547b-4fdd-9db4-171b45241967','2026-08-24 05:38:19'),(19,1,'page_block.updated','App\\Models\\PageBlock',8,'Page block updated: About our school / identity','{\"body\": \"The About experience balances the institution’s Catholic heritage with a modern, confident story about learning and care.\", \"heading\": \"Tradition gives the school roots. Learning gives every child wings.\", \"page_id\": 2, \"media_id\": 4, \"settings\": {\"quote\": \"Education should form capable minds, generous hearts and responsible lives.\", \"source\": \"managed_content\", \"eyebrow\": \"Our identity\", \"body_secondary\": \"The page layout gives administrators dedicated areas for school history, leadership messages, mission, vision, values, facilities and governance.\"}, \"block_type\": \"identity\", \"is_enabled\": true, \"sort_order\": 8, \"subheading\": null, \"visible_from\": null, \"visible_until\": null}','{\"body\": \"St. Charles Borromeo Pre & Primary School is a values-driven learning institution in Mbeya, established in 2017 with a commitment to providing children with quality education grounded in faith, discipline and academic excellence. Inspired by the philosophy and legacy of St. Charles Borromeo, the school focuses on developing the whole child by combining strong academic foundations with character formation, responsibility, confidence, respect and service to others. Since its establishment, the school has continued to create a nurturing and purposeful learning environment where pupils are encouraged to discover their potential, develop positive lifelong habits and grow into knowledgeable, disciplined and responsible members of society.\", \"heading\": null, \"page_id\": 2, \"media_id\": \"10\", \"settings\": {\"quote\": \"Education should form capable minds, generous hearts and responsible lives.\", \"source\": \"managed_content\", \"eyebrow\": \"Our identity\", \"body_secondary\": \"The page layout gives administrators dedicated areas for school history, leadership messages, mission, vision, values, facilities and governance.\"}, \"block_type\": \"identity\", \"is_enabled\": true, \"sort_order\": 8, \"subheading\": null, \"visible_from\": null, \"visible_until\": null}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','a3d34101-eed7-4ebc-8b8e-cbe640c0f7d4','2026-08-24 05:46:41'),(20,1,'post.updated','App\\Models\\Post',5,'News story updated from visible admin editor: dgjsfjxfjxfhjm','{\"body\": \"xfHJzdtgjxc \\r\\nxjhv mhk\", \"tags\": [], \"title\": \"dgjsfjxfjxfhjm\", \"status\": \"published\", \"excerpt\": \"fXHzbnxfgm vmmhvmhv\", \"is_featured\": false, \"published_at\": \"2026-08-17T09:07:00.000000Z\", \"scheduled_at\": null, \"post_category_id\": 3, \"featured_media_id\": null}','{\"body\": \"xfHJzdtgjxc \\r\\nxjhv mhk\", \"tags\": [], \"title\": \"dgjsfjxfjxfhjm\", \"status\": \"archived\", \"excerpt\": \"fXHzbnxfgm vmmhvmhv\", \"is_featured\": false, \"published_at\": null, \"scheduled_at\": null, \"post_category_id\": \"3\", \"featured_media_id\": null}','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','2d704b6c-5a17-488d-8ebe-b960d1bd7208','2026-08-24 11:12:53');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('st-charles-borromeo-cache-5210a5ff37c1adf5db1b3c6d03bff28b','i:1;',1787355269),('st-charles-borromeo-cache-5210a5ff37c1adf5db1b3c6d03bff28b:timer','i:1787355269;',1787355269),('st-charles-borromeo-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1787356090),('st-charles-borromeo-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1787356090;',1787356090),('st-charles-borromeo-cache-6563e28cc11c7711636a183e9e0057f6','i:1;',1787556507),('st-charles-borromeo-cache-6563e28cc11c7711636a183e9e0057f6:timer','i:1787556507;',1787556507),('st-charles-borromeo-cache-72e1fe3c671e39e657b53ccbc4a2e3b1','i:1;',1787577162),('st-charles-borromeo-cache-72e1fe3c671e39e657b53ccbc4a2e3b1:timer','i:1787577162;',1787577162),('st-charles-borromeo-cache-8d07b2775a8a2f0d74e42697d73a25e4','i:2;',1787556513),('st-charles-borromeo-cache-8d07b2775a8a2f0d74e42697d73a25e4:timer','i:1787556513;',1787556513),('st-charles-borromeo-cache-b5603f364728dc4430e9748faacc0975','i:1;',1787577162),('st-charles-borromeo-cache-b5603f364728dc4430e9748faacc0975:timer','i:1787577162;',1787577162);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_message_notes`
--

DROP TABLE IF EXISTS `contact_message_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_message_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contact_message_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sensitive` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_message_notes_user_id_foreign` (`user_id`),
  KEY `contact_message_notes_contact_message_id_created_at_index` (`contact_message_id`,`created_at`),
  CONSTRAINT `contact_message_notes_contact_message_id_foreign` FOREIGN KEY (`contact_message_id`) REFERENCES `contact_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contact_message_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_message_notes`
--

LOCK TABLES `contact_message_notes` WRITE;
/*!40000 ALTER TABLE `contact_message_notes` DISABLE KEYS */;
INSERT INTO `contact_message_notes` VALUES (1,1,1,'Local-only contact workflow note.',1,'2026-08-21 18:47:22','2026-08-21 18:47:22');
/*!40000 ALTER TABLE `contact_message_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `consent_confirmed` tinyint(1) NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `first_viewed_at` timestamp NULL DEFAULT NULL,
  `viewed_by` bigint unsigned DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `source_ip_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_messages_reference_code_unique` (`reference_code`),
  KEY `contact_messages_status_created_at_index` (`status`,`created_at`),
  KEY `contact_messages_assigned_to_status_created_at_index` (`assigned_to`,`status`,`created_at`),
  KEY `contact_messages_email_index` (`email`),
  KEY `contact_messages_viewed_by_foreign` (`viewed_by`),
  KEY `contact_messages_first_viewed_at_index` (`first_viewed_at`),
  CONSTRAINT `contact_messages_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contact_messages_viewed_by_foreign` FOREIGN KEY (`viewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (1,'CON-LOCAL-0001','Website Visitor','contact@example.test','+27 00 000 0002','General enquiry','Please share more information about the school.',1,'new','2026-08-21 21:35:41',1,1,'12ca17b49af2289436f303e0166030a21e525d266e209267433801a8fd4071a0','0a96d96da99c0ca483c66d2d366494a428f37b496cce7a8b55f7e7568d409e2c',NULL,NULL,'2026-08-21 18:47:22','2026-08-21 21:35:41',NULL);
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content_revisions`
--

DROP TABLE IF EXISTS `content_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_revisions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `revisionable_type` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revisionable_id` bigint unsigned NOT NULL,
  `revision_number` int unsigned NOT NULL,
  `snapshot` json NOT NULL,
  `change_summary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_rev_unique` (`revisionable_type`,`revisionable_id`,`revision_number`),
  KEY `content_revisions_created_by_foreign` (`created_by`),
  KEY `content_rev_lookup` (`revisionable_type`,`revisionable_id`,`created_at`),
  CONSTRAINT `content_revisions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_revisions`
--

LOCK TABLES `content_revisions` WRITE;
/*!40000 ALTER TABLE `content_revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `content_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Primary School','primary-school','Primary learning and pastoral support.',NULL,NULL,0,1,'2026-08-21 18:47:22','2026-08-21 18:47:22',NULL);
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `download_categories`
--

DROP TABLE IF EXISTS `download_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `download_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `download_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `download_categories`
--

LOCK TABLES `download_categories` WRITE;
/*!40000 ALTER TABLE `download_categories` DISABLE KEYS */;
INSERT INTO `download_categories` VALUES (1,'Admissions','admissions','Approved admissions documents.',0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(2,'Calendar','calendar','Published school calendar documents.',0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(3,'Policies','policies','Approved school policies and notices.',0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL);
/*!40000 ALTER TABLE `download_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `downloads`
--

DROP TABLE IF EXISTS `downloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `downloads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `download_category_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `media_id` bigint unsigned NOT NULL,
  `version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publication_date` date DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `download_count` bigint unsigned NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `downloads_slug_unique` (`slug`),
  KEY `downloads_media_id_foreign` (`media_id`),
  KEY `downloads_created_by_foreign` (`created_by`),
  KEY `downloads_updated_by_foreign` (`updated_by`),
  KEY `downloads_status_published_at_index` (`status`,`published_at`),
  KEY `downloads_download_category_id_status_publication_date_index` (`download_category_id`,`status`,`publication_date`),
  KEY `downloads_download_count_index` (`download_count`),
  FULLTEXT KEY `downloads_title_description_fulltext` (`title`,`description`),
  CONSTRAINT `downloads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `downloads_download_category_id_foreign` FOREIGN KEY (`download_category_id`) REFERENCES `download_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `downloads_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `downloads_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `downloads`
--

LOCK TABLES `downloads` WRITE;
/*!40000 ALTER TABLE `downloads` DISABLE KEYS */;
INSERT INTO `downloads` VALUES (1,1,'Admissions Information Pack','admissions-information-pack','Admissions guidance document.',15,NULL,'2026-08-22','draft',NULL,0,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:25',NULL),(2,2,'Academic Calendar','academic-calendar-placeholder','School calendar document.',14,NULL,'2026-08-17','draft',NULL,0,1,1,'2026-08-21 18:47:22','2026-08-21 18:47:22',NULL),(3,3,'Safeguarding Statement','safeguarding-statement-placeholder','School safeguarding document.',14,NULL,'2026-08-13','draft',NULL,0,1,1,'2026-08-21 18:47:22','2026-08-21 18:47:22',NULL),(4,2,'Academic Calendar','academic-calendar','School calendar document.',15,NULL,'2026-08-18','draft',NULL,0,1,1,'2026-08-21 20:11:33','2026-08-21 22:31:25',NULL),(5,3,'Safeguarding Statement','safeguarding-statement','School safeguarding document.',15,NULL,'2026-08-14','draft',NULL,0,1,1,'2026-08-21 20:11:33','2026-08-21 22:31:25',NULL);
/*!40000 ALTER TABLE `downloads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_replies`
--

DROP TABLE IF EXISTS `email_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_replies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `replyable_type` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `replyable_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `queued_at` timestamp NOT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `failure_type` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_replies_user_id_foreign` (`user_id`),
  KEY `email_replies_subject_lookup` (`replyable_type`,`replyable_id`,`created_at`),
  KEY `email_replies_status_queued_at_index` (`status`,`queued_at`),
  CONSTRAINT `email_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_replies`
--

LOCK TABLES `email_replies` WRITE;
/*!40000 ALTER TABLE `email_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_categories`
--

DROP TABLE IF EXISTS `event_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_categories`
--

LOCK TABLES `event_categories` WRITE;
/*!40000 ALTER TABLE `event_categories` DISABLE KEYS */;
INSERT INTO `event_categories` VALUES (1,'School Calendar','school-calendar','Dates published by the school.',1,'2026-08-21 18:47:22','2026-08-21 18:47:22',NULL),(2,'Faith','faith','Faith and community events.',1,'2026-08-21 18:47:22','2026-08-21 18:47:22',NULL),(3,'Sport','sport','Sport and participation events.',1,'2026-08-21 18:47:22','2026-08-21 18:47:22',NULL);
/*!40000 ALTER TABLE `event_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_category_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `featured_media_id` bigint unsigned DEFAULT NULL,
  `starts_at` timestamp NOT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `timezone` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `venue_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue_address` text COLLATE utf8mb4_unicode_ci,
  `map_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `programme_download_id` bigint unsigned DEFAULT NULL,
  `event_state` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `publication_status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `seo_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonical_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_media_id` bigint unsigned DEFAULT NULL,
  `robots_index` tinyint(1) NOT NULL DEFAULT '1',
  `robots_follow` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `events_slug_unique` (`slug`),
  KEY `events_featured_media_id_foreign` (`featured_media_id`),
  KEY `events_programme_download_id_foreign` (`programme_download_id`),
  KEY `events_og_media_id_foreign` (`og_media_id`),
  KEY `events_created_by_foreign` (`created_by`),
  KEY `events_updated_by_foreign` (`updated_by`),
  KEY `events_publication_status_starts_at_index` (`publication_status`,`starts_at`),
  KEY `events_event_category_id_publication_status_starts_at_index` (`event_category_id`,`publication_status`,`starts_at`),
  KEY `events_event_state_starts_at_index` (`event_state`,`starts_at`),
  KEY `events_starts_at_ends_at_index` (`starts_at`,`ends_at`),
  FULLTEXT KEY `events_title_summary_body_fulltext` (`title`,`summary`,`body`),
  CONSTRAINT `events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_event_category_id_foreign` FOREIGN KEY (`event_category_id`) REFERENCES `event_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_featured_media_id_foreign` FOREIGN KEY (`featured_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_og_media_id_foreign` FOREIGN KEY (`og_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_programme_download_id_foreign` FOREIGN KEY (`programme_download_id`) REFERENCES `downloads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,1,'Parent Orientation','parent-orientation','Orientation information for families.','Full event details will be published after the school calendar is confirmed.',2,'2026-09-05 07:00:00','2026-09-05 09:00:00','UTC',NULL,NULL,NULL,NULL,1,'scheduled','published','2026-08-20 22:31:25',NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:22','2026-08-21 22:31:25',NULL),(2,2,'Community Mass','community-mass','A gathering for prayer and community.','Full event details will be published after the school calendar is confirmed.',4,'2026-09-19 07:00:00','2026-09-19 09:00:00','UTC',NULL,NULL,NULL,NULL,1,'scheduled','published','2026-08-20 22:31:25',NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:22','2026-08-21 22:31:25',NULL),(3,3,'Inter-house Sports Day','inter-house-sports-day','A school sport and participation event.','Full event details will be published after the school calendar is confirmed.',6,'2026-10-03 07:00:00','2026-10-03 09:00:00','UTC',NULL,NULL,NULL,NULL,1,'scheduled','published','2026-08-20 22:31:25',NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:22','2026-08-21 22:31:25',NULL),(4,NULL,'Official website launch','official-website-launch',NULL,'cgjcghvmcjb,',NULL,'2026-08-24 22:03:00','2026-08-25 06:05:00','UTC','St. Charles Borromeo hall',NULL,NULL,NULL,NULL,'scheduled','published','2026-08-23 11:57:47',NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-23 11:54:52','2026-08-23 11:57:47',NULL);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faq_categories`
--

DROP TABLE IF EXISTS `faq_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faq_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faq_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faq_categories`
--

LOCK TABLES `faq_categories` WRITE;
/*!40000 ALTER TABLE `faq_categories` DISABLE KEYS */;
INSERT INTO `faq_categories` VALUES (1,'General','general',0,1,'2026-08-21 18:47:22','2026-08-21 18:47:22');
/*!40000 ALTER TABLE `faq_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `faq_category_id` bigint unsigned DEFAULT NULL,
  `question` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `verification_notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faqs_created_by_foreign` (`created_by`),
  KEY `faqs_updated_by_foreign` (`updated_by`),
  KEY `faqs_faq_category_id_status_sort_order_index` (`faq_category_id`,`status`,`sort_order`),
  KEY `faqs_status_published_at_index` (`status`,`published_at`),
  KEY `faqs_verified_by_foreign` (`verified_by`),
  KEY `faqs_verified_at_index` (`verified_at`),
  FULLTEXT KEY `faqs_question_answer_fulltext` (`question`,`answer`),
  CONSTRAINT `faqs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `faqs_faq_category_id_foreign` FOREIGN KEY (`faq_category_id`) REFERENCES `faq_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `faqs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `faqs_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (1,1,'Where should official admissions information be confirmed?','Use the admissions enquiry form to request current guidance directly from the school team.',1,'published','2026-08-20 22:31:25','2026-08-20 22:31:25',1,'Website process confirmed.',1,1,'2026-08-21 18:47:22','2026-08-21 22:31:25',NULL),(2,1,'How do I begin an admission enquiry?','Families can send an enquiry through the Admissions page or contact the school office. An authorised staff member will confirm availability, required documents and the next step.',2,'published','2026-08-20 22:31:25','2026-08-20 22:31:25',1,'Website process confirmed.',1,1,'2026-08-21 18:47:22','2026-08-21 22:31:25',NULL),(3,1,'Can I visit the school before applying?','Yes. The admissions page provides a clear school-visit action so families can arrange an appropriate time.',3,'published','2026-08-20 18:47:22',NULL,NULL,NULL,1,1,'2026-08-21 18:47:22','2026-08-21 18:47:22',NULL),(4,1,'Where can I find term dates and forms?','Published calendars, admissions documents, policies and school resources are available on the Downloads page.',4,'published','2026-08-20 22:31:25','2026-08-20 22:31:25',1,'Website process confirmed.',1,1,'2026-08-21 18:47:22','2026-08-21 22:31:25',NULL),(5,1,'How are school photographs managed?','Public photographs pass a safeguarding review. Images requiring consent remain private until authorised staff confirm the relevant consent record.',5,'published','2026-08-20 22:31:25','2026-08-20 22:31:25',1,'Website process confirmed.',1,1,'2026-08-21 18:47:22','2026-08-21 22:31:25',NULL),(6,1,'How can I arrange a school visit?','Send an admission enquiry with your preferred contact details. The school office will respond with available arrangements.',3,'published','2026-08-20 22:31:25','2026-08-20 22:31:25',1,'Website process confirmed.',1,1,'2026-08-21 20:11:33','2026-08-21 22:31:25',NULL);
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `galleries`
--

DROP TABLE IF EXISTS `galleries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `galleries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `gallery_category_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cover_media_id` bigint unsigned DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `galleries_slug_unique` (`slug`),
  KEY `galleries_cover_media_id_foreign` (`cover_media_id`),
  KEY `galleries_created_by_foreign` (`created_by`),
  KEY `galleries_updated_by_foreign` (`updated_by`),
  KEY `galleries_status_published_at_index` (`status`,`published_at`),
  KEY `galleries_gallery_category_id_status_index` (`gallery_category_id`,`status`),
  CONSTRAINT `galleries_cover_media_id_foreign` FOREIGN KEY (`cover_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `galleries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `galleries_gallery_category_id_foreign` FOREIGN KEY (`gallery_category_id`) REFERENCES `gallery_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `galleries_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `galleries`
--

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
INSERT INTO `galleries` VALUES (1,1,'School Life Gallery','school-life-gallery','Learning, community, achievement and celebration at St. Charles Borromeo.',10,NULL,'published','2026-08-20 22:31:25',1,1,1,'2026-08-21 18:47:22','2026-08-21 22:31:25',NULL),(2,1,'Graduation 2026','graduation-2026','Selected moments from the graduation celebration held at St. Charles Borromeo Hall on 15 August 2026.',18,'2026-08-15','published','2026-08-21 20:31:25',0,1,1,'2026-08-21 21:10:28','2026-08-21 22:31:25',NULL);
/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery_categories`
--

DROP TABLE IF EXISTS `gallery_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gallery_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gallery_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery_categories`
--

LOCK TABLES `gallery_categories` WRITE;
/*!40000 ALTER TABLE `gallery_categories` DISABLE KEYS */;
INSERT INTO `gallery_categories` VALUES (1,'School Life','school-life',0,1,'2026-08-21 18:47:22','2026-08-21 18:47:22');
/*!40000 ALTER TABLE `gallery_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery_items`
--

DROP TABLE IF EXISTS `gallery_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gallery_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `gallery_id` bigint unsigned NOT NULL,
  `media_id` bigint unsigned NOT NULL,
  `caption` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gallery_items_gallery_id_media_id_unique` (`gallery_id`,`media_id`),
  KEY `gallery_items_media_id_foreign` (`media_id`),
  KEY `gallery_items_gallery_id_sort_order_index` (`gallery_id`,`sort_order`),
  CONSTRAINT `gallery_items_gallery_id_foreign` FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gallery_items_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery_items`
--

LOCK TABLES `gallery_items` WRITE;
/*!40000 ALTER TABLE `gallery_items` DISABLE KEYS */;
INSERT INTO `gallery_items` VALUES (1,1,10,'Aerial view of the St. Charles Borromeo school campus.',1,1,'2026-08-21 18:47:22','2026-08-21 18:47:22'),(2,1,11,'Pupils and staff taking part in a school tree-planting activity.',2,0,'2026-08-21 18:47:22','2026-08-21 18:47:22'),(3,1,12,'A pupil receiving a certificate during a school recognition ceremony.',3,0,'2026-08-21 18:47:22','2026-08-21 18:47:22'),(4,1,13,'Pupils presenting a cultural performance at a school celebration.',4,0,'2026-08-21 18:47:22','2026-08-21 18:47:22'),(5,1,6,'Students playing football.',5,0,'2026-08-21 18:47:22','2026-08-21 18:47:22'),(6,1,3,'Learners reading in the school library.',6,0,'2026-08-21 18:47:22','2026-08-21 18:47:22'),(7,2,18,'Graduates welcome guests to the celebration.',1,1,'2026-08-21 21:10:28','2026-08-21 21:10:28'),(8,2,17,'St. Charles Borromeo Hall prepared for the graduation ceremony.',2,0,'2026-08-21 21:10:28','2026-08-21 21:10:28'),(9,2,16,'Guests arrive for the school graduation celebration.',3,0,'2026-08-21 21:10:28','2026-08-21 21:10:28'),(10,2,19,'A graduate marks the completion of an important school milestone.',4,0,'2026-08-21 21:10:28','2026-08-21 21:10:28'),(11,2,20,'A joyful moment after the graduation ceremony.',5,0,'2026-08-21 21:10:28','2026-08-21 21:10:28'),(12,2,21,'A graduate photographed during the school celebration.',6,0,'2026-08-21 21:10:28','2026-08-21 21:10:28');
/*!40000 ALTER TABLE `gallery_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disk` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `directory` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` bigint unsigned NOT NULL,
  `width` int unsigned DEFAULT NULL,
  `height` int unsigned DEFAULT NULL,
  `checksum_sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption` text COLLATE utf8mb4_unicode_ci,
  `credit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `focal_x` decimal(5,2) DEFAULT NULL,
  `focal_y` decimal(5,2) DEFAULT NULL,
  `visibility` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'private',
  `consent_required` tinyint(1) NOT NULL DEFAULT '0',
  `consent_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `consent_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publication_restricted` tinyint(1) NOT NULL DEFAULT '0',
  `restriction_reason` text COLLATE utf8mb4_unicode_ci,
  `is_protected_asset` tinyint(1) NOT NULL DEFAULT '0',
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_disk_directory_stored_name_unique` (`disk`,`directory`,`stored_name`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_uploaded_by_foreign` (`uploaded_by`),
  KEY `media_visibility_publication_restricted_deleted_at_index` (`visibility`,`publication_restricted`,`deleted_at`),
  KEY `media_consent_required_consent_confirmed_index` (`consent_required`,`consent_confirmed`),
  CONSTRAINT `media_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES (1,'46af59cc-3185-4909-815d-ec97ac7c8cf9','public','assets/images/brand','scb-logo-original.jpg','scb-logo-original.jpg','image/jpeg','jpg',41164,447,447,'2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b','The exact St. Charles Borromeo school logo.','Protected St. Charles Borromeo school identity asset.','St. Charles Borromeo supplied asset',NULL,NULL,'public',0,0,NULL,0,NULL,1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(2,'87122017-badd-4901-bf81-d266c0627924','local','seed/school','01-religious-community-ceremony.jpg','01-religious-community-ceremony.jpg','image/jpeg','jpg',618804,2048,1536,'08a1e43fbd1bb1fd4ee60eab3993f09c22eabaf9c744c8746a9231199004ec66','School community ceremony photograph.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(3,'959a2f79-d79f-4209-a9f3-71481b898810','local','seed/school','02-school-library-reading.jpg','02-school-library-reading.jpg','image/jpeg','jpg',591251,2048,969,'1a2cb028ab12041be1ef20d08851ed09b92c7155698cdb051a3911eda24f65b7','Learners reading in the school library.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(4,'05601586-d2ec-4b3c-b67b-eb60a0eb435b','local','seed/school','03-sisters-community-group.jpg','03-sisters-community-group.jpg','image/jpeg','jpg',439136,1920,1122,'0d1a709fcdc4b0b696363f308c8d128ce767abb90ba955fc170be9de36e19909','School sisters and community members.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(5,'31f4b627-8255-4f2b-9734-98b56c6d3572','local','seed/school','05-classroom-learning.jpg','05-classroom-learning.jpg','image/jpeg','jpg',653346,2048,1203,'7de61b42cdfb621e1a11f447521af15e419a112c768d2383c2a62cc1eae9beb2','Classroom learning photograph.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(6,'00fe0bf3-d586-43c7-91b0-2cadda6399ce','local','seed/school','06-students-playing-football.jpg','06-students-playing-football.jpg','image/jpeg','jpg',951103,2048,996,'eb61c1ab5ef075e3776f00aa6b8b6aa6dcfdb002425e06c411f2aa1726a2e5b0','Students playing football.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(7,'5855549d-c660-411f-acec-093b03307189','local','seed/school','07-students-school-assembly.jpg','07-students-school-assembly.jpg','image/jpeg','jpg',766478,2048,1298,'cd204ca7e73f7f83341142a7a2582cd37c9c268c3978fb2fb575c6f306728e8d','Students gathered for school assembly.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(8,'a85d5f71-6368-447f-ac06-e5a60cf9f101','local','seed/school','11-school-playground.jpg','11-school-playground.jpg','image/jpeg','jpg',1508419,2048,1578,'541041538ede88d7dbac0922562ec0fd7594682b5548ac58ea25f5eabf34631f','School playground area.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(9,'d5e7cde0-e02a-409a-b20e-163196166ba3','local','seed/school','14-school-administration-staff.jpg','14-school-administration-staff.jpg','image/jpeg','jpg',1485123,2560,1233,'d6795521d112adfe104afe31dad212dac64464c984b3ef255d102cbdde491b0c','Administration staff group photograph.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',0,0,NULL,0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(10,'507d7005-9aa8-488e-8f8b-2c945679f465','local','seed/school/2026','campus-aerial.webp','campus-aerial.webp','image/webp','webp',500530,1920,1080,'d32a93d6b80a4980dbaf056a28cd07317d4bfd8ed30868e573bbb5e6a77b3b93','Aerial view of the St. Charles Borromeo school campus.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',0,0,NULL,0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(11,'99331059-42bf-424e-a12b-89c5a2e57e3a','local','seed/school/2026','tree-planting-community.webp','tree-planting-community.webp','image/webp','webp',144260,1080,720,'e0c53620adab1fd22a54df826afb812c8802c98fa90ecb9b7f4228e543e66f38','Pupils and staff taking part in a school tree-planting activity.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(12,'9290d35e-0272-4352-8a09-a5dd60bd4e2d','local','seed/school/2026','pupil-recognition.webp','pupil-recognition.webp','image/webp','webp',142916,1099,1127,'12a63bef59f9ec1b6422c5e2f625b24324cf9b20dda02c25bc092eb3545c3440','A pupil receiving a certificate during a school recognition ceremony.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(13,'f4c63342-0e8f-4b04-805c-c548f1522931','local','seed/school/2026','cultural-performance.webp','cultural-performance.webp','image/webp','webp',82806,897,373,'95b52e5accb9b39d9b7db6f80e0bf6f116e5e6d3f8dceececa085f6c612ae8fe','Pupils presenting a cultural performance at a school celebration.','St. Charles Borromeo school life.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(14,'3cbc31a4-a460-4703-a5ec-3d9bcc11705b','public','assets/documents','admissions-pack-placeholder.pdf','admissions-pack-placeholder.pdf','application/pdf','pdf',0,NULL,NULL,'633904e94467f9f656fd7a1577109f734ac51b712c560fedf86b87c633748ac8','Placeholder admissions pack document metadata.','Document awaiting approved publication copy and file.',NULL,NULL,NULL,'private',0,0,NULL,1,'No approved public file has been supplied.',0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(15,'818ac4ee-3ca0-41cc-95ce-594b9df23c95','public','assets/documents','admissions-document-awaiting-file.pdf','admissions-document-awaiting-file.pdf','application/pdf','pdf',0,NULL,NULL,'4063786a020f4852daf09793e1d23e67824eb63d4b0f9f6219ca4938edd77d29',NULL,'Admissions document record.',NULL,NULL,NULL,'private',0,0,NULL,1,'No approved public file has been supplied.',0,1,'2026-08-21 20:11:33','2026-08-21 22:31:24',NULL),(16,'f194ee74-c142-472f-9731-5a92ad5c5750','local','seed/school/2026/graduation','graduation-guest-arrival-2026.webp','graduation-guest-arrival-2026.webp','image/webp','webp',207890,1707,2560,'9cafcf76ae9c41e74ea7c763fab4f72dc4d31e5e874693fe70834929bf2ac5aa','Guests arriving for the 2026 graduation celebration at St. Charles Borromeo Hall.','Guests arrive for the school graduation celebration.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 21:10:27','2026-08-21 21:10:27',NULL),(17,'89f94b4e-d6d9-4e17-829d-9d94ffeafc17','local','seed/school/2026/graduation','graduation-hall-2026.webp','graduation-hall-2026.webp','image/webp','webp',369676,2560,1707,'b8dd792571aa9f40d8ede5098e1c2e99df358267e2861cc06183bfc697e18d57','The decorated stage inside St. Charles Borromeo Hall for the 2026 graduation.','St. Charles Borromeo Hall prepared for the graduation ceremony.','St. Charles Borromeo supplied asset',NULL,NULL,'public',0,0,NULL,0,NULL,0,1,'2026-08-21 21:10:27','2026-08-21 21:10:27',NULL),(18,'804ffec1-66f4-411a-85ab-83eed9218752','local','seed/school/2026/graduation','graduation-ceremonial-welcome-2026.webp','graduation-ceremonial-welcome-2026.webp','image/webp','webp',391852,2560,1707,'4744a220cf7ce8d90e9ae6c05440ff1aaae621dddf6a4558f4243f6ddc3ec5bc','Graduates forming a ceremonial welcome line during the 2026 school graduation.','Graduates welcome guests to the celebration.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 21:10:27','2026-08-21 21:10:27',NULL),(19,'fab4337d-f446-4873-948e-7377237b83b4','local','seed/school/2026/graduation','graduation-graduate-portrait-01-2026.webp','graduation-graduate-portrait-01-2026.webp','image/webp','webp',354320,1707,2560,'114de6dc9f6a713bd6536c5f9e3c558a90d94b5ba1ffc34fdfa28d3e637c7472','A graduate holding flowers after the 2026 school ceremony.','A graduate marks the completion of an important school milestone.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 21:10:27','2026-08-21 21:10:27',NULL),(20,'e6bb3456-6885-4d8f-a76b-a861da56484a','local','seed/school/2026/graduation','graduation-graduate-portrait-02-2026.webp','graduation-graduate-portrait-02-2026.webp','image/webp','webp',327208,1707,2560,'a07e034b0981b728384f6f5f86931885f70d87c8221ca668e3322e6e81938e78','A smiling graduate holding flowers at the 2026 school celebration.','A joyful moment after the graduation ceremony.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 21:10:27','2026-08-21 21:10:27',NULL),(21,'72203480-95c9-431c-8acc-238830d7c882','local','seed/school/2026/graduation','graduation-graduate-portrait-03-2026.webp','graduation-graduate-portrait-03-2026.webp','image/webp','webp',288484,1707,2560,'55d58d6e798af39612ad3913a2952345d7d68eed7a85b75d040fdb9446d039e1','A graduate wearing a commemorative sash and holding flowers.','A graduate photographed during the school celebration.','St. Charles Borromeo supplied asset',NULL,NULL,'public',1,1,'owner-supplied-confirmed',0,NULL,0,1,'2026-08-21 21:10:27','2026-08-21 21:10:27',NULL);
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_usages`
--

DROP TABLE IF EXISTS `media_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_usages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `media_id` bigint unsigned NOT NULL,
  `usable_type` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usable_id` bigint unsigned NOT NULL,
  `field_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_usages_media_id_usable_type_usable_id_field_name_unique` (`media_id`,`usable_type`,`usable_id`,`field_name`),
  KEY `media_usages_usable_type_usable_id_index` (`usable_type`,`usable_id`),
  CONSTRAINT `media_usages_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_usages`
--

LOCK TABLES `media_usages` WRITE;
/*!40000 ALTER TABLE `media_usages` DISABLE KEYS */;
INSERT INTO `media_usages` VALUES (1,10,'App\\Models\\PageBlock',1,'settings.carousel_media_ids','2026-08-21 18:47:21'),(2,11,'App\\Models\\PageBlock',1,'settings.carousel_media_ids','2026-08-21 18:47:21'),(3,13,'App\\Models\\PageBlock',1,'settings.carousel_media_ids','2026-08-21 18:47:21'),(4,10,'App\\Models\\GalleryItem',1,'media_id','2026-08-21 18:47:22'),(5,11,'App\\Models\\GalleryItem',2,'media_id','2026-08-21 18:47:22'),(6,12,'App\\Models\\GalleryItem',3,'media_id','2026-08-21 18:47:22'),(7,13,'App\\Models\\GalleryItem',4,'media_id','2026-08-21 18:47:22'),(8,6,'App\\Models\\GalleryItem',5,'media_id','2026-08-21 18:47:22'),(9,3,'App\\Models\\GalleryItem',6,'media_id','2026-08-21 18:47:22'),(10,18,'App\\Models\\PageBlock',1,'settings.carousel_media_ids','2026-08-21 21:10:27'),(11,17,'App\\Models\\PageBlock',1,'settings.carousel_media_ids','2026-08-21 21:10:27'),(17,18,'App\\Models\\GalleryItem',7,'media_id','2026-08-21 21:10:28'),(18,17,'App\\Models\\GalleryItem',8,'media_id','2026-08-21 21:10:28'),(19,16,'App\\Models\\GalleryItem',9,'media_id','2026-08-21 21:10:28'),(20,19,'App\\Models\\GalleryItem',10,'media_id','2026-08-21 21:10:28'),(21,20,'App\\Models\\GalleryItem',11,'media_id','2026-08-21 21:10:28'),(22,21,'App\\Models\\GalleryItem',12,'media_id','2026-08-21 21:10:28'),(23,17,'App\\Models\\Post',4,'article_gallery:01','2026-08-21 22:31:25'),(24,16,'App\\Models\\Post',4,'article_gallery:02','2026-08-21 22:31:25'),(25,19,'App\\Models\\Post',4,'article_gallery:03','2026-08-21 22:31:25'),(26,20,'App\\Models\\Post',4,'article_gallery:04','2026-08-21 22:31:25'),(27,21,'App\\Models\\Post',4,'article_gallery:05','2026-08-21 22:31:25');
/*!40000 ALTER TABLE `media_usages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_variants`
--

DROP TABLE IF EXISTS `media_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `media_id` bigint unsigned NOT NULL,
  `variant_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disk` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `width` int unsigned DEFAULT NULL,
  `height` int unsigned DEFAULT NULL,
  `size_bytes` bigint unsigned NOT NULL,
  `checksum_sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_variants_media_id_variant_key_unique` (`media_id`,`variant_key`),
  CONSTRAINT `media_variants_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_variants`
--

LOCK TABLES `media_variants` WRITE;
/*!40000 ALTER TABLE `media_variants` DISABLE KEYS */;
INSERT INTO `media_variants` VALUES (1,16,'responsive-480','local','seed/school/2026/graduation/variants/graduation-guest-arrival-2026-480.webp','image/webp',480,720,31622,'9f7b6b0bc2dd3b055fa3808e8fff7de50958fa636013a7a9d4ee254ad2a45bf3','2026-08-21 21:10:27','2026-08-21 21:10:27'),(2,16,'responsive-960','local','seed/school/2026/graduation/variants/graduation-guest-arrival-2026-960.webp','image/webp',960,1440,76842,'c519282cfd37e5043421c02eef8de345477e21f7404118fbdf9d40abcc527107','2026-08-21 21:10:27','2026-08-21 21:10:27'),(3,16,'responsive-1600','local','seed/school/2026/graduation/variants/graduation-guest-arrival-2026-1600.webp','image/webp',1600,2400,146486,'484980ba490745abca544a3eb7d4f92daaeaaee1617b3a8c1b1389528a0ba1da','2026-08-21 21:10:27','2026-08-21 21:10:27'),(4,17,'responsive-480','local','seed/school/2026/graduation/variants/graduation-hall-2026-480.webp','image/webp',480,320,22464,'113603f25cebac57643eca343b9e4ca8f4e8d22bae8ea6b7d7a099fc634c0f22','2026-08-21 21:10:27','2026-08-21 21:10:27'),(5,17,'responsive-960','local','seed/school/2026/graduation/variants/graduation-hall-2026-960.webp','image/webp',960,640,67476,'ad14615d14f66dff505806caee82e1d916b59f7bde9aed1541287759972d4340','2026-08-21 21:10:27','2026-08-21 21:10:27'),(6,17,'responsive-1600','local','seed/school/2026/graduation/variants/graduation-hall-2026-1600.webp','image/webp',1600,1067,141342,'f5c67a8866ed7e2177282e4f88bdb427724f22842da052560cad8327883cf9a6','2026-08-21 21:10:27','2026-08-21 21:10:27'),(7,18,'responsive-480','local','seed/school/2026/graduation/variants/graduation-ceremonial-welcome-2026-480.webp','image/webp',480,320,34018,'382d1bcff65c37263bd97ef653540f0dd979effbcb0eb640bbd2c0d9a7437f2b','2026-08-21 21:10:27','2026-08-21 21:10:27'),(8,18,'responsive-960','local','seed/school/2026/graduation/variants/graduation-ceremonial-welcome-2026-960.webp','image/webp',960,640,87608,'650fed0808ab9c3a3f358dee78f346c440c8bd7b2831c19c88eac794dec0db93','2026-08-21 21:10:27','2026-08-21 21:10:27'),(9,18,'responsive-1600','local','seed/school/2026/graduation/variants/graduation-ceremonial-welcome-2026-1600.webp','image/webp',1600,1067,173426,'ba71290a4640193444c22623312bb2a1b24c7840b735a6c323de63eaab24792e','2026-08-21 21:10:27','2026-08-21 21:10:27'),(10,19,'responsive-480','local','seed/school/2026/graduation/variants/graduation-graduate-portrait-01-2026-480.webp','image/webp',480,720,40678,'1137739adbe6942259eb9b301ba507751efbcc7ab2b6a705458e587f18defc23','2026-08-21 21:10:27','2026-08-21 21:10:27'),(11,19,'responsive-960','local','seed/school/2026/graduation/variants/graduation-graduate-portrait-01-2026-960.webp','image/webp',960,1440,105846,'eefdd129c239f83c606c42ca131acce857f9ea6b335e75b1f2469d794812f491','2026-08-21 21:10:27','2026-08-21 21:10:27'),(12,19,'responsive-1600','local','seed/school/2026/graduation/variants/graduation-graduate-portrait-01-2026-1600.webp','image/webp',1600,2400,225162,'b55027c844bf0bfd4c67bb5524eaadc9d3c5f63ad029f6fa3752872bb87893b9','2026-08-21 21:10:27','2026-08-21 21:10:27'),(13,20,'responsive-480','local','seed/school/2026/graduation/variants/graduation-graduate-portrait-02-2026-480.webp','image/webp',480,720,38916,'6734bade4ff01af217fc0a7f636fc109a24568f6b99ff575177f68dc62bb9fd6','2026-08-21 21:10:27','2026-08-21 21:10:27'),(14,20,'responsive-960','local','seed/school/2026/graduation/variants/graduation-graduate-portrait-02-2026-960.webp','image/webp',960,1440,103414,'42e5cd64cc6e323dc6e454ba8bbd69fd814e84ac39c1ec11eaf751077d67feaa','2026-08-21 21:10:27','2026-08-21 21:10:27'),(15,20,'responsive-1600','local','seed/school/2026/graduation/variants/graduation-graduate-portrait-02-2026-1600.webp','image/webp',1600,2400,212696,'b70cb3a56592169d31aa9bed2e54f377714984fda997b3adc779cdf995d63ad2','2026-08-21 21:10:27','2026-08-21 21:10:27'),(16,21,'responsive-480','local','seed/school/2026/graduation/variants/graduation-graduate-portrait-03-2026-480.webp','image/webp',480,720,38708,'45bdfbbf50d97ff82e571e7ffdc011761f7e93200c86129154b570ca6821691f','2026-08-21 21:10:27','2026-08-21 21:10:27'),(17,21,'responsive-960','local','seed/school/2026/graduation/variants/graduation-graduate-portrait-03-2026-960.webp','image/webp',960,1440,95780,'956532af9a42620eb5a0210d5ed343ffd2f8537d9f3364dd4f0530e989a5792e','2026-08-21 21:10:27','2026-08-21 21:10:27'),(18,21,'responsive-1600','local','seed/school/2026/graduation/variants/graduation-graduate-portrait-03-2026-1600.webp','image/webp',1600,2400,192486,'e4cc8bb66ceb95af722cc7867ed874539f532484a03730e13a2cb2164006018c','2026-08-21 21:10:27','2026-08-21 21:10:27'),(19,2,'responsive-480','local','seed/school/variants/01-religious-community-ceremony-480.webp','image/webp',480,360,22382,'d48d55d77efbcae9651c865645111943d08d8f0633840b59b84548174a07b2a0','2026-08-21 22:31:24','2026-08-21 22:31:24'),(20,2,'responsive-960','local','seed/school/variants/01-religious-community-ceremony-960.webp','image/webp',960,720,53052,'9381dca512427f2958be6f7729393bf3816556dcdfb4ebb71b69825d48b2eff3','2026-08-21 22:31:24','2026-08-21 22:31:24'),(21,2,'responsive-1600','local','seed/school/variants/01-religious-community-ceremony-1600.webp','image/webp',1600,1200,96274,'1d60470a878443937926d03fc019f60bb78574f279eec5661c62a564ec666233','2026-08-21 22:31:24','2026-08-21 22:31:24'),(22,3,'responsive-480','local','seed/school/variants/02-school-library-reading-480.webp','image/webp',480,227,23074,'9bc4b374750205cb5b690e28aac1b5646ef904fba65d62c260d636237f244306','2026-08-21 22:31:24','2026-08-21 22:31:24'),(23,3,'responsive-960','local','seed/school/variants/02-school-library-reading-960.webp','image/webp',960,454,59118,'81196876b6c34c898ad7d4642e78b5a153fd6e96ba3805e8cd30e33e81ff9e60','2026-08-21 22:31:24','2026-08-21 22:31:24'),(24,3,'responsive-1600','local','seed/school/variants/02-school-library-reading-1600.webp','image/webp',1600,757,107442,'c506c6d2b67cc46565f757c9c9b37132e3e9f5b60e008ee65d58950b7171f0b2','2026-08-21 22:31:24','2026-08-21 22:31:24'),(25,4,'responsive-480','local','seed/school/variants/03-sisters-community-group-480.webp','image/webp',480,281,21276,'793273cfc6ab373226dd9166a1f911d073f943aa8e2c1a57407e62371874bf61','2026-08-21 22:31:24','2026-08-21 22:31:24'),(26,4,'responsive-960','local','seed/school/variants/03-sisters-community-group-960.webp','image/webp',960,561,48048,'1b69e8ec1f79bbf1cb93c9a89098a64b0961e7e9e374a181242d6b0bce5bd26a','2026-08-21 22:31:24','2026-08-21 22:31:24'),(27,4,'responsive-1600','local','seed/school/variants/03-sisters-community-group-1600.webp','image/webp',1600,935,83000,'fe04e78d71ea641cb90a4f6af42f34bdbe0bedd3101e933c35255ffe5e12863c','2026-08-21 22:31:24','2026-08-21 22:31:24'),(28,5,'responsive-480','local','seed/school/variants/05-classroom-learning-480.webp','image/webp',480,282,26070,'8925be286b5375ee37b52918610fd331389815c06603724595ea456329402318','2026-08-21 22:31:24','2026-08-21 22:31:24'),(29,5,'responsive-960','local','seed/school/variants/05-classroom-learning-960.webp','image/webp',960,564,65414,'f156e0e81425ac443ad36a56ea47d065598bee8a040d0bd5fa78000f0537cea6','2026-08-21 22:31:24','2026-08-21 22:31:24'),(30,5,'responsive-1600','local','seed/school/variants/05-classroom-learning-1600.webp','image/webp',1600,940,113164,'12aa997999d7e6e18e573cb4a364eafd32bb810c2a9e6c8904375c2ecbe09cf7','2026-08-21 22:31:24','2026-08-21 22:31:24'),(31,6,'responsive-480','local','seed/school/variants/06-students-playing-football-480.webp','image/webp',480,233,34750,'690f516baf7b692e8f7788502668854d5aef20561189062a1ac4ed23fea1932d','2026-08-21 22:31:24','2026-08-21 22:31:24'),(32,6,'responsive-960','local','seed/school/variants/06-students-playing-football-960.webp','image/webp',960,467,134504,'23b975b4a31741023917b1cc4f395c78be2e932ba6d4c5c502484c08cffe43df','2026-08-21 22:31:24','2026-08-21 22:31:24'),(33,6,'responsive-1600','local','seed/school/variants/06-students-playing-football-1600.webp','image/webp',1600,778,277012,'ef401df4a3f54b1d293517612d13a5b2aa7a2c07ef0a792e44debe4e4dba71a6','2026-08-21 22:31:24','2026-08-21 22:31:24'),(34,7,'responsive-480','local','seed/school/variants/07-students-school-assembly-480.webp','image/webp',480,304,31246,'c3218dd167a29b8a745f07a90fcfd24f3debb7b292c10f5f6e563e31c6c9a999','2026-08-21 22:31:24','2026-08-21 22:31:24'),(35,7,'responsive-960','local','seed/school/variants/07-students-school-assembly-960.webp','image/webp',960,608,82084,'05a9f706cccd03db576eac4fd85dfb2e1a5bc992f8297188a19c358e3296cd6a','2026-08-21 22:31:24','2026-08-21 22:31:24'),(36,7,'responsive-1600','local','seed/school/variants/07-students-school-assembly-1600.webp','image/webp',1600,1014,150170,'38d0d2c171f5d2e6ae87f7d265ed93067b0e301eec9fe83afe1f11fb63adf9cc','2026-08-21 22:31:24','2026-08-21 22:31:24'),(37,8,'responsive-480','local','seed/school/variants/11-school-playground-480.webp','image/webp',480,370,67070,'e62f6746e46f57c1cb35cbd32f6cb89b96d0444e689c7175dcb737dcc7fa6a86','2026-08-21 22:31:24','2026-08-21 22:31:24'),(38,8,'responsive-960','local','seed/school/variants/11-school-playground-960.webp','image/webp',960,740,202624,'49f4ea8b0dd15fda4ff114e2d90a41e8a199f07aff415cda9af29855d2f0a705','2026-08-21 22:31:24','2026-08-21 22:31:24'),(39,8,'responsive-1600','local','seed/school/variants/11-school-playground-1600.webp','image/webp',1600,1233,381944,'2986b13e142815b6b27b5d1a7880e80d28dff54de4f6de762c919a1f3aa0f4dc','2026-08-21 22:31:24','2026-08-21 22:31:24'),(40,9,'responsive-480','local','seed/school/variants/14-school-administration-staff-480.webp','image/webp',480,231,25796,'45a6a8c11311355438f7fe5bd001125188804610aa21288f30665cd36eb5c290','2026-08-21 22:31:24','2026-08-21 22:31:24'),(41,9,'responsive-960','local','seed/school/variants/14-school-administration-staff-960.webp','image/webp',960,462,86774,'8f0644cc0176487b4cfa931db7e5b80cc2c225470d5251a2ceefb39e68b23a44','2026-08-21 22:31:24','2026-08-21 22:31:24'),(42,9,'responsive-1600','local','seed/school/variants/14-school-administration-staff-1600.webp','image/webp',1600,771,204408,'e95911833a37ea579b1d65931f9b7b77d69aa81957700b95f945b4bafc95fbdd','2026-08-21 22:31:24','2026-08-21 22:31:24'),(43,10,'responsive-480','local','seed/school/2026/variants/campus-aerial-480.webp','image/webp',480,270,36336,'b0bc49a95b54d4e9b6adfb40736dca10e28119b0ff5f3d9b9a76c42a9caf6a9a','2026-08-21 22:31:24','2026-08-21 22:31:24'),(44,10,'responsive-960','local','seed/school/2026/variants/campus-aerial-960.webp','image/webp',960,540,132250,'838d7dfdedfcc2cca4292777194969fd0566ca1f25ce2bee4d535bb8f71a8d68','2026-08-21 22:31:24','2026-08-21 22:31:24'),(45,10,'responsive-1600','local','seed/school/2026/variants/campus-aerial-1600.webp','image/webp',1600,900,278928,'6ffae5a116fb992643a83b6f9be079991ca2301acf9a31dda2c2357ec4e90d88','2026-08-21 22:31:24','2026-08-21 22:31:24'),(46,11,'responsive-480','local','seed/school/2026/variants/tree-planting-community-480.webp','image/webp',480,320,38198,'33e97b25c323aba3f8567f7e6dd22b0051bf3759c1030e756f1e72c9d4e958b2','2026-08-21 22:31:24','2026-08-21 22:31:24'),(47,11,'responsive-960','local','seed/school/2026/variants/tree-planting-community-960.webp','image/webp',960,640,91832,'b4fad4fd428fbfce87397cc42956e5e10d49d75ef830d67e884395ea05e61243','2026-08-21 22:31:24','2026-08-21 22:31:24'),(48,12,'responsive-480','local','seed/school/2026/variants/pupil-recognition-480.webp','image/webp',480,492,33652,'8e95f7ec7fc5f0f0fd2a3fc9d7ef956a5c65370f5adefd1d9dd2fb21e192b002','2026-08-21 22:31:24','2026-08-21 22:31:24'),(49,12,'responsive-960','local','seed/school/2026/variants/pupil-recognition-960.webp','image/webp',960,984,80800,'b667754a984f4357b9301252035cbed13e2f837a9bdfdddc01af44f9c604ae02','2026-08-21 22:31:24','2026-08-21 22:31:24'),(50,13,'responsive-480','local','seed/school/2026/variants/cultural-performance-480.webp','image/webp',480,200,23324,'b860bbfa002e1c627e60aa4da95e0295f1695b9eafc9532905119c5591667c21','2026-08-21 22:31:24','2026-08-21 22:31:24');
/*!40000 ALTER TABLE `media_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `page_id` bigint unsigned DEFAULT NULL,
  `label` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_name` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_self',
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_parent_id_foreign` (`parent_id`),
  KEY `menu_items_page_id_foreign` (`page_id`),
  KEY `menu_items_tree_order` (`menu_id`,`parent_id`,`sort_order`),
  KEY `menu_items_active` (`menu_id`,`is_active`),
  CONSTRAINT `menu_items_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_items_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `menu_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
INSERT INTO `menu_items` VALUES (1,1,NULL,1,'Home','route',NULL,'home','_self',NULL,1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(2,1,NULL,2,'About','route',NULL,'about','_self',NULL,2,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(3,1,NULL,3,'Academics','route',NULL,'academics','_self',NULL,3,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(4,1,NULL,4,'Admissions','route',NULL,'admissions','_self',NULL,4,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(5,1,NULL,5,'News','route',NULL,'news.index','_self',NULL,5,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(6,1,NULL,6,'Events','route',NULL,'events.index','_self',NULL,6,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(7,1,NULL,7,'Gallery','route',NULL,'gallery','_self',NULL,7,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(8,1,NULL,9,'Contact','route',NULL,'contact','_self',NULL,8,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(9,2,NULL,2,'About the school','route',NULL,'about','_self',NULL,1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(10,2,NULL,3,'Academics','route',NULL,'academics','_self',NULL,2,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(11,2,NULL,4,'Admissions','route',NULL,'admissions','_self',NULL,3,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(12,2,NULL,5,'News and notices','route',NULL,'news.index','_self',NULL,4,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(13,3,NULL,6,'School calendar','route',NULL,'events.index','_self',NULL,1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(14,3,NULL,7,'Gallery','route',NULL,'gallery','_self',NULL,2,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(15,3,NULL,8,'Downloads','route',NULL,'downloads','_self',NULL,3,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(16,3,NULL,10,'Frequently asked questions','route',NULL,'faq','_self',NULL,4,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(17,4,NULL,11,'Privacy and safeguarding','route',NULL,'privacy','_self',NULL,1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(18,4,NULL,9,'Contact','route',NULL,'contact','_self',NULL,2,1,'2026-08-21 18:47:21','2026-08-21 18:47:21');
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menus_location_unique` (`location`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (1,'Primary Navigation','primary','active','2026-08-21 18:47:21','2026-08-21 18:47:21'),(2,'Footer School','footer-school','active','2026-08-21 18:47:21','2026-08-21 18:47:21'),(3,'Footer Resources','footer-resources','active','2026-08-21 18:47:21','2026-08-21 18:47:21'),(4,'Footer Legal','footer-legal','active','2026-08-21 18:47:21','2026-08-21 18:47:21');
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_03_000001_create_access_tables',1),(5,'2026_08_03_000002_create_media_tables',1),(6,'2026_08_03_000003_create_site_page_navigation_tables',1),(7,'2026_08_03_000004_create_editorial_and_event_tables',1),(8,'2026_08_03_000005_create_school_gallery_faq_tables',1),(9,'2026_08_03_000006_create_enquiry_redirect_audit_tables',1),(10,'2026_08_05_000007_add_view_state_to_public_enquiries',1),(11,'2026_08_21_000008_add_faq_verification_fields',2),(12,'2026_08_21_000009_create_email_replies_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_blocks`
--

DROP TABLE IF EXISTS `page_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_blocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint unsigned NOT NULL,
  `block_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `heading` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subheading` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `media_id` bigint unsigned DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `visible_from` timestamp NULL DEFAULT NULL,
  `visible_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_blocks_media_id_foreign` (`media_id`),
  KEY `page_blocks_page_id_is_enabled_sort_order_index` (`page_id`,`is_enabled`,`sort_order`),
  KEY `page_blocks_visible_from_visible_until_index` (`visible_from`,`visible_until`),
  CONSTRAINT `page_blocks_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `page_blocks_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_blocks`
--

LOCK TABLES `page_blocks` WRITE;
/*!40000 ALTER TABLE `page_blocks` DISABLE KEYS */;
INSERT INTO `page_blocks` VALUES (1,1,'hero','Growing minds. Forming character.',NULL,'A classic, faith-centred school experience where every child is known, challenged and prepared to serve.',10,'{\"badge\": \"St. Charles Borromeo Pre & Primary School\", \"stats\": [{\"label\": \"Faith\", \"description\": \"Values for life\"}, {\"label\": \"Care\", \"description\": \"Every child matters\"}, {\"label\": \"Excellence\", \"description\": \"Confident learners\"}, {\"label\": \"Service\", \"description\": \"Responsible citizens\"}], \"source\": \"managed_content\", \"primary_action\": {\"label\": \"Begin admission\", \"route\": \"admissions\"}, \"secondary_action\": {\"label\": \"Discover our school\", \"route\": \"about\"}, \"carousel_media_ids\": [10, 18, 17, 11]}',1,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(2,1,'welcome','A school that feels rooted, calm and ambitious.',NULL,'Our learning environment brings together strong classroom practice, Catholic identity, creative expression and a caring community.',12,'{\"action\": {\"label\": \"Our story\", \"route\": \"about\"}, \"source\": \"managed_content\", \"eyebrow\": \"Welcome\", \"body_secondary\": \"From the first years of pre-primary to the final stage of primary education, children are guided to become thoughtful learners, kind friends and courageous contributors.\"}',2,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(3,1,'programme_highlights','Learning shaped around what children need to thrive.',NULL,'A balanced school experience brings learning, wellbeing, character and participation together.',NULL,'{\"cards\": [{\"icon\": \"✦\", \"title\": \"Pre-primary\", \"description\": \"Playful foundations in language, numeracy, movement and social confidence.\"}, {\"icon\": \"▤\", \"title\": \"Primary learning\", \"description\": \"Structured, engaging teaching that builds understanding and independence.\"}, {\"icon\": \"♡\", \"title\": \"Pastoral care\", \"description\": \"A watchful community that supports wellbeing, dignity and belonging.\"}, {\"icon\": \"⚑\", \"title\": \"Clubs and sport\", \"description\": \"Opportunities to discover talent, teamwork, leadership and joy.\"}], \"action\": {\"label\": \"Explore academics\", \"route\": \"academics\"}, \"source\": \"managed_content\", \"eyebrow\": \"The whole child\"}',3,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(4,1,'academic_life','Clear teaching. Active minds. Purposeful progress.',NULL,'Learning is strengthened through participation, practice, feedback and the confidence to keep improving.',13,'{\"action\": {\"label\": \"View programmes\", \"route\": \"academics\"}, \"source\": \"managed_content\", \"eyebrow\": \"Academic life\"}',4,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(5,1,'latest_news','Latest News',NULL,'Recent school stories and upcoming moments.',NULL,'{\"action\": {\"label\": \"All news\", \"route\": \"news.index\"}, \"source\": \"managed_content\", \"eyebrow\": \"School life\"}',5,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(6,1,'upcoming_events','Upcoming Events',NULL,NULL,NULL,'{\"action\": {\"label\": \"All events\", \"route\": \"events.index\"}, \"source\": \"managed_content\"}',6,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(7,1,'call_to_action','Come and see the school in action.',NULL,'The admissions journey is presented clearly: enquire, visit, apply and receive support from the school office.',NULL,'{\"source\": \"managed_content\", \"eyebrow\": \"Admissions\", \"primary_action\": {\"label\": \"Admission guide\", \"route\": \"admissions\"}, \"secondary_action\": {\"label\": \"Book a visit\", \"route\": \"contact\"}}',7,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(8,2,'identity',NULL,NULL,'St. Charles Borromeo Pre & Primary School is a values-driven learning institution in Mbeya, established in 2017 with a commitment to providing children with quality education grounded in faith, discipline and academic excellence. Inspired by the philosophy and legacy of St. Charles Borromeo, the school focuses on developing the whole child by combining strong academic foundations with character formation, responsibility, confidence, respect and service to others. Since its establishment, the school has continued to create a nurturing and purposeful learning environment where pupils are encouraged to discover their potential, develop positive lifelong habits and grow into knowledgeable, disciplined and responsible members of society.',10,'{\"quote\": \"Education should form capable minds, generous hearts and responsible lives.\", \"source\": \"managed_content\", \"eyebrow\": \"Our identity\", \"body_secondary\": \"The page layout gives administrators dedicated areas for school history, leadership messages, mission, vision, values, facilities and governance.\"}',8,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-24 05:46:41'),(9,2,'values','Mission, vision and values',NULL,'The school presents its mission, vision and values as clear commitments for daily life.',NULL,'{\"items\": [{\"icon\": \"✦\", \"label\": \"Mission\", \"description\": \"Purposeful education rooted in faith and care.\"}, {\"icon\": \"◎\", \"label\": \"Vision\", \"description\": \"Confident learners prepared to serve society.\"}, {\"icon\": \"◆\", \"label\": \"Values\", \"description\": \"Integrity, excellence, discipline and compassion.\"}, {\"icon\": \"♜\", \"label\": \"Leadership\", \"description\": \"Responsible stewardship and open communication.\"}], \"source\": \"managed_content\"}',9,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(10,2,'catholic_character','Faith is expressed through welcome, dignity and service.',NULL,'This content area is managed as a reusable page block, with an image, heading, body copy and call to action.',2,'{\"action\": {\"label\": \"Meet the community\", \"route\": \"contact\"}, \"source\": \"managed_content\", \"eyebrow\": \"Catholic character\", \"body_secondary\": \"The visual treatment is formal without being cold: deep plum from the crest, warm gold accents, generous cream surfaces and restrained crimson highlights.\"}',10,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(11,2,'leadership_staff','People who guide learning with clarity and care.',NULL,'Meet the people who support the school community.',NULL,'{\"source\": \"managed_content\", \"eyebrow\": \"Leadership and staff\"}',11,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(12,3,'learning_journey','Programmes designed for growth, mastery and curiosity.',NULL,'The UX separates programmes clearly while preserving a single visual language across the school.',NULL,'{\"cards\": [{\"title\": \"Pre-primary\", \"number\": \"1\", \"target\": \"preprimary\", \"description\": \"Language, number, movement, creative play and routines that build confidence.\"}, {\"title\": \"Lower primary\", \"number\": \"2\", \"target\": \"primary\", \"description\": \"Strong foundations in literacy, numeracy, discovery and personal responsibility.\"}, {\"title\": \"Upper primary\", \"number\": \"3\", \"target\": \"primary\", \"description\": \"Deeper subject understanding, independence, leadership and preparation for transition.\"}], \"source\": \"managed_content\", \"eyebrow\": \"Learning journey\"}',12,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(13,3,'pre_primary','A joyful beginning with clear purpose.',NULL,'The page combines emotional reassurance for parents with practical curriculum information.',8,'{\"items\": [{\"label\": \"Communication\", \"description\": \"Listening, speaking, vocabulary and early literacy.\"}, {\"label\": \"Discovery\", \"description\": \"Early number, patterns, observation and problem solving.\"}, {\"label\": \"Movement\", \"description\": \"Coordination, healthy routines and active play.\"}, {\"label\": \"Belonging\", \"description\": \"Friendship, confidence, faith and self-management.\"}], \"source\": \"managed_content\", \"eyebrow\": \"Pre-primary\"}',13,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(14,3,'primary_school','Knowledge that becomes skill and good judgement.',NULL,'Subject information is presented in scannable groups with downloadable curriculum documents.',3,'{\"items\": [{\"label\": \"Core subjects\", \"description\": \"Languages, mathematics, science and social studies.\"}, {\"label\": \"Formation\", \"description\": \"Religious education, life skills and citizenship.\"}, {\"label\": \"Creative life\", \"description\": \"Art, music, performance and practical projects.\"}, {\"label\": \"Wellbeing\", \"description\": \"Physical education, sport and personal development.\"}], \"source\": \"managed_content\", \"eyebrow\": \"Primary school\"}',14,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(15,3,'beyond_lessons','Talent grows through participation.',NULL,'Clubs, sports, worship, leadership and educational visits are given equal visual weight as essential parts of school life.',NULL,'{\"items\": [{\"label\": \"Sport\", \"description\": \"Teamwork, fitness and fair play.\"}, {\"label\": \"Creative arts\", \"description\": \"Expression, courage and celebration.\"}], \"source\": \"managed_content\", \"eyebrow\": \"Beyond lessons\"}',15,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(16,3,'programme_list','Learning Programmes',NULL,'Explore the programmes currently published by the school.',5,'{\"source\": \"managed_content\"}',16,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(17,4,'journey','A clear four-step admissions journey.',NULL,'Parents should always know what to do next. Each step has a strong action, expected response and responsible school contact.',NULL,'{\"steps\": [{\"label\": \"Make an enquiry\", \"description\": \"Tell us the child’s intended level and preferred admission period.\"}, {\"label\": \"Visit the school\", \"description\": \"Meet the admissions team and experience the learning environment.\"}, {\"label\": \"Submit application\", \"description\": \"Provide the requested documents through the approved process.\"}, {\"label\": \"Receive guidance\", \"description\": \"The school communicates assessment, placement and next steps.\"}], \"source\": \"managed_content\", \"eyebrow\": \"Join the school\"}',17,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(18,4,'prepare','Helpful information before applying.',NULL,'Review the key information before making an enquiry.',NULL,'{\"cards\": [{\"icon\": \"✓\", \"title\": \"Entry information\", \"description\": \"Age guidance, available levels and placement arrangements.\"}, {\"icon\": \"▣\", \"title\": \"Documents\", \"description\": \"A simple list of approved documents, without collecting sensitive data too early.\"}, {\"icon\": \"↓\", \"route\": \"downloads\", \"title\": \"Downloads\", \"description\": \"Application guide, calendars and school resources.\"}], \"source\": \"managed_content\", \"eyebrow\": \"What to prepare\"}',18,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(19,4,'visit','See the places where children learn, play and belong.',NULL,'A campus visit is a high-value conversion action and remains visible throughout the admissions experience.',8,'{\"action\": {\"label\": \"Book a school visit\", \"route\": \"contact\"}, \"source\": \"managed_content\", \"eyebrow\": \"Visit the campus\"}',19,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24'),(20,4,'downloads_list','Admissions Information',NULL,'Approved admissions documents will be listed here when available.',NULL,'{\"source\": \"managed_content\"}',20,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(21,9,'contact_details','Contact The School',NULL,'Use the form to send your enquiry to the school team.',NULL,'{\"source\": \"managed_content\"}',21,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(22,10,'faq_accordion','Frequently Asked Questions',NULL,'Browse practical guidance for families and visitors.',NULL,'{\"source\": \"managed_content\"}',22,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(23,11,'privacy_statement','Respectful data use and safeguarding.',NULL,'This page explains the website approach to privacy, child safeguarding, cookies and responsible access.',4,'{\"links\": [\"Privacy notice\", \"Safeguarding statement\", \"Cookie notice\", \"Website terms\"], \"source\": \"managed_content\", \"sections\": [{\"body\": \"Public forms should collect only what the school needs to respond. Sensitive records must use secure, purpose-built processes rather than general contact forms.\", \"heading\": \"Data minimisation\"}, {\"body\": \"The school records consent, avoids unnecessary identifying details and provides a clear removal-request process.\", \"heading\": \"Children’s photographs\"}, {\"body\": \"Every administrator should use an individual account, least-privilege permissions, secure authentication and auditable activity.\", \"heading\": \"Administrator access\"}]}',23,1,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24');
/*!40000 ALTER TABLE `page_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `template_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `featured_media_id` bigint unsigned DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `seo_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonical_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_media_id` bigint unsigned DEFAULT NULL,
  `robots_index` tinyint(1) NOT NULL DEFAULT '1',
  `robots_follow` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`),
  KEY `pages_featured_media_id_foreign` (`featured_media_id`),
  KEY `pages_og_media_id_foreign` (`og_media_id`),
  KEY `pages_created_by_foreign` (`created_by`),
  KEY `pages_updated_by_foreign` (`updated_by`),
  KEY `pages_status_published_at_index` (`status`,`published_at`),
  KEY `pages_page_type_status_index` (`page_type`,`status`),
  KEY `pages_robots_index_status_index` (`robots_index`,`status`),
  CONSTRAINT `pages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pages_featured_media_id_foreign` FOREIGN KEY (`featured_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pages_og_media_id_foreign` FOREIGN KEY (`og_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pages_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'Home','home','home','home','published','Faith, care, learning and community.',10,'2026-08-20 22:31:24',NULL,'Home | St. Charles Borromeo','Faith, care, learning and community.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(2,'About our school','about','standard','standard','published','A community shaped by faith, thoughtful teaching and respectful relationships.',4,'2026-08-20 22:31:24',NULL,'About our school | St. Charles Borromeo','A community shaped by faith, thoughtful teaching and respectful relationships.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(3,'Academics','academics','standard','standard','published','A coherent learning journey from early foundations to confident primary achievement.',5,'2026-08-20 22:31:24',NULL,'Academics | St. Charles Borromeo','A coherent learning journey from early foundations to confident primary achievement.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(4,'Admissions','admissions','standard','standard','published','A clear route from first enquiry to the next admissions step.',8,'2026-08-20 22:31:24',NULL,'Admissions | St. Charles Borromeo','A clear route from first enquiry to the next admissions step.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(5,'News','news','standard','listing','published','Stories and updates from school life.',11,'2026-08-20 22:31:24',NULL,'News | St. Charles Borromeo','Stories and updates from school life.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(6,'Events','events','standard','listing','published','Published dates and moments from the school calendar.',13,'2026-08-20 22:31:24',NULL,'Events | St. Charles Borromeo','Published dates and moments from the school calendar.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(7,'Gallery','gallery','standard','listing','published','A visual record of learning, community and celebration.',12,'2026-08-20 22:31:24',NULL,'Gallery | St. Charles Borromeo','A visual record of learning, community and celebration.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(8,'Downloads','downloads','standard','listing','published','Approved school documents and resources.',3,'2026-08-20 22:31:24',NULL,'Downloads | St. Charles Borromeo','Approved school documents and resources.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(9,'Contact','contact','standard','form','published','Send a message to the school team.',10,'2026-08-20 22:31:24',NULL,'Contact | St. Charles Borromeo','Send a message to the school team.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(10,'FAQ','faq','standard','listing','published','Helpful answers for families and visitors.',3,'2026-08-20 22:31:24',NULL,'FAQ | St. Charles Borromeo','Helpful answers for families and visitors.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(11,'Privacy','privacy','standard','standard','published','How personal information and safeguarding responsibilities are handled.',NULL,'2026-08-20 22:31:24',NULL,'Privacy | St. Charles Borromeo','How personal information and safeguarding responsibilities are handled.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(12,'Search','search','standard','listing','published','Find published information across the school website.',NULL,'2026-08-20 22:31:24',NULL,'Search | St. Charles Borromeo','Find published information across the school website.',NULL,NULL,NULL,NULL,0,1,1,1,'2026-08-21 20:11:33','2026-08-21 22:31:24',NULL);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES ('local.admin@example.test','$2y$12$EBSYqz.haDezb1djxrCbEeAnKmsfUH8G5ZsxrWDL5Go0Fdf6lVwj2','2026-08-21 21:47:10');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_role` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1);
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'Pages View','pages.view','pages','Allows authorised users to pages view.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(2,'Pages Create','pages.create','pages','Allows authorised users to pages create.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(3,'Pages Update','pages.update','pages','Allows authorised users to pages update.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(4,'Pages Publish','pages.publish','pages','Allows authorised users to pages publish.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(5,'Media View','media.view','media','Allows authorised users to media view.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(6,'Media Approve','media.approve','media','Allows authorised users to media approve.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(7,'News Manage','news.manage','news','Allows authorised users to news manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(8,'Events Manage','events.manage','events','Allows authorised users to events manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(9,'Galleries Manage','galleries.manage','galleries','Allows authorised users to galleries manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(10,'Downloads Manage','downloads.manage','downloads','Allows authorised users to downloads manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(11,'Admissions View','admissions.view','admissions','Allows authorised users to admissions view.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(12,'Admissions Manage','admissions.manage','admissions','Allows authorised users to admissions manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(13,'Admissions Export','admissions.export','admissions','Allows authorised users to admissions export.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(14,'Contacts View','contacts.view','contacts','Allows authorised users to contacts view.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(15,'Contacts Manage','contacts.manage','contacts','Allows authorised users to contacts manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(16,'Contacts Export','contacts.export','contacts','Allows authorised users to contacts export.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(17,'Staff Manage','staff.manage','staff','Allows authorised users to staff manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(18,'Programmes Manage','programmes.manage','programmes','Allows authorised users to programmes manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(19,'Settings Manage','settings.manage','settings','Allows authorised users to settings manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(20,'Users Manage','users.manage','users','Allows authorised users to users manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(21,'Roles Manage','roles.manage','roles','Allows authorised users to roles manage.','2026-08-21 18:47:21','2026-08-21 18:47:21'),(22,'Audit View','audit.view','audit','Allows authorised users to audit view.','2026-08-21 18:47:21','2026-08-21 18:47:21');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_categories`
--

DROP TABLE IF EXISTS `post_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_categories`
--

LOCK TABLES `post_categories` WRITE;
/*!40000 ALTER TABLE `post_categories` DISABLE KEYS */;
INSERT INTO `post_categories` VALUES (1,'School News','school-news','News from across the school community.',0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(2,'Learning','learning','Learning experiences and achievements.',0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL),(3,'Sport','sport','Sport, teamwork and participation.',0,1,'2026-08-21 18:47:21','2026-08-21 18:47:21',NULL);
/*!40000 ALTER TABLE `post_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_tag`
--

DROP TABLE IF EXISTS `post_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_tag` (
  `post_id` bigint unsigned NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  UNIQUE KEY `post_tag_post_id_tag_id_unique` (`post_id`,`tag_id`),
  KEY `post_tag_tag_id_post_id_index` (`tag_id`,`post_id`),
  CONSTRAINT `post_tag_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_tag`
--

LOCK TABLES `post_tag` WRITE;
/*!40000 ALTER TABLE `post_tag` DISABLE KEYS */;
INSERT INTO `post_tag` VALUES (1,1),(2,1),(3,1),(4,1),(4,2);
/*!40000 ALTER TABLE `post_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_category_id` bigint unsigned DEFAULT NULL,
  `author_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `featured_media_id` bigint unsigned DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `seo_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonical_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_media_id` bigint unsigned DEFAULT NULL,
  `robots_index` tinyint(1) NOT NULL DEFAULT '1',
  `robots_follow` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_slug_unique` (`slug`),
  KEY `posts_author_id_foreign` (`author_id`),
  KEY `posts_featured_media_id_foreign` (`featured_media_id`),
  KEY `posts_og_media_id_foreign` (`og_media_id`),
  KEY `posts_created_by_foreign` (`created_by`),
  KEY `posts_updated_by_foreign` (`updated_by`),
  KEY `posts_status_published_at_index` (`status`,`published_at`),
  KEY `posts_post_category_id_status_published_at_index` (`post_category_id`,`status`,`published_at`),
  KEY `posts_is_featured_status_published_at_index` (`is_featured`,`status`,`published_at`),
  FULLTEXT KEY `posts_title_excerpt_body_fulltext` (`title`,`excerpt`,`body`),
  CONSTRAINT `posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `posts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `posts_featured_media_id_foreign` FOREIGN KEY (`featured_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `posts_og_media_id_foreign` FOREIGN KEY (`og_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `posts_post_category_id_foreign` FOREIGN KEY (`post_category_id`) REFERENCES `post_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `posts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,1,1,'Growing Together Through Tree Planting','young-learners-shine','Pupils and staff share a practical moment of care for the school environment.','The school community came together for a hands-on tree-planting activity, giving pupils an opportunity to learn through participation and shared responsibility.',11,'published',1,'2026-08-19 22:31:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:25',NULL),(2,2,1,'Celebrating Effort And Progress','reading-together-in-the-library','Recognition moments encourage pupils to value steady effort and personal progress.','A school recognition ceremony celebrates a pupil\'s work and marks an encouraging step in the learning journey.',12,'published',0,'2026-08-14 22:31:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:25',NULL),(3,3,1,'Culture In Motion','teamwork-takes-the-field','Pupils bring colour, rhythm and confidence to a school celebration.','A lively cultural performance gives pupils space to practise teamwork, expression and confidence before the school community.',13,'published',0,'2026-08-09 22:31:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:21','2026-08-21 22:31:25',NULL),(4,1,1,'St. Charles Borromeo Celebrates the 2026 Graduation','st-charles-borromeo-2026-graduation','The school community gathered at St. Charles Borromeo Hall on 15 August 2026 for a graduation celebration with guest of honour Solomon Itunda, Mbeya District Commissioner.','St. Charles Borromeo Pre & Primary School held its 2026 graduation ceremony on 15 August at St. Charles Borromeo Hall. Graduates, families, staff and invited guests came together to mark an important milestone in the pupils\' school journey.\n\nThe school welcomed Solomon Itunda, Mbeya District Commissioner, as guest of honour. The occasion recognised the graduates\' progress and the support provided by their families, teachers and the wider school community.\n\nThe ceremony reflected the school\'s commitment to learning, character and service while giving the graduating pupils a joyful and dignified send-off.',18,'published',1,'2026-08-21 20:31:25',NULL,'2026 Graduation Celebration | St. Charles Borromeo','Highlights from the St. Charles Borromeo graduation held on 15 August 2026 with guest of honour Solomon Itunda, Mbeya District Commissioner.',NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 21:10:27','2026-08-21 22:31:25',NULL),(5,3,1,'dgjsfjxfjxfhjm','dgjsfjxfjxfhjm','fXHzbnxfgm vmmhvmhv','xfHJzdtgjxc \r\nxjhv mhk',NULL,'archived',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-23 11:51:34','2026-08-24 11:12:53',NULL),(6,NULL,1,'More Than a School: How the Spirit of St. Charles Borromeo Shapes Every Child We Educate','more-than-a-school-how-the-spirit-of-st-charles-borromeo-shapes-every-child-we-educate','At St. Charles Borromeo Pre & Primary School, Mbeya, education goes beyond academic achievement. Our school is deeply inspired by the life and philosophy of St. Charles Borromeo, whose legacy emphasized faith, discipline, education, service and strong moral character. These principles continue to shape the way we teach, guide and nurture every child.','At St. Charles Borromeo Pre & Primary School, Mbeya, education goes beyond academic achievement. Our school is deeply inspired by the life and philosophy of St. Charles Borromeo, whose legacy emphasized faith, discipline, education, service and strong moral character. These principles continue to shape the way we teach, guide and nurture every child.\r\n\r\nOur philosophy is reflected in the values of Faith, Discipline and Excellence. We believe that a good education should develop both the mind and character of a child. Pupils are encouraged to become respectful, responsible, confident and hardworking while building a strong academic foundation for their future.\r\n\r\nDiscipline at St. Charles Borromeo is not simply about following rules. It is about helping children develop positive habits such as punctuality, responsibility, respect, perseverance and self-control. These qualities prepare our pupils not only for examinations, but also for secondary education and life beyond the classroom.\r\n\r\nAcademic excellence remains an important part of our mission. Our teachers work to create an environment where pupils are encouraged to learn, ask questions, discover their talents and develop confidence in their abilities. We believe every child has potential that can grow when supported by dedicated teachers, strong values and an encouraging school community.\r\n\r\nFaith also remains at the heart of our school culture. Through prayer, respect, compassion and service to others, pupils learn that education should be accompanied by integrity and responsibility. We want our children to understand that true success is not measured only by what they achieve, but also by the kind of people they become.\r\n\r\nFor parents, choosing a school is about much more than classrooms and examinations. It is about choosing an environment that will influence a child\'s future. At St. Charles Borromeo Pre & Primary School, we are committed to providing that strong foundation—where children can grow academically, spiritually and socially.',NULL,'draft',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-24 05:38:19','2026-08-24 05:38:19',NULL);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programmes`
--

DROP TABLE IF EXISTS `programmes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programmes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `programme_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `featured_media_id` bigint unsigned DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `seo_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonical_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_media_id` bigint unsigned DEFAULT NULL,
  `robots_index` tinyint(1) NOT NULL DEFAULT '1',
  `robots_follow` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `programmes_slug_unique` (`slug`),
  KEY `programmes_featured_media_id_foreign` (`featured_media_id`),
  KEY `programmes_og_media_id_foreign` (`og_media_id`),
  KEY `programmes_created_by_foreign` (`created_by`),
  KEY `programmes_updated_by_foreign` (`updated_by`),
  KEY `programmes_status_published_at_index` (`status`,`published_at`),
  KEY `programmes_department_id_status_sort_order_index` (`department_id`,`status`,`sort_order`),
  KEY `programmes_programme_type_status_index` (`programme_type`,`status`),
  FULLTEXT KEY `programmes_name_summary_body_fulltext` (`name`,`summary`,`body`),
  CONSTRAINT `programmes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `programmes_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `programmes_featured_media_id_foreign` FOREIGN KEY (`featured_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `programmes_og_media_id_foreign` FOREIGN KEY (`og_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `programmes_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programmes`
--

LOCK TABLES `programmes` WRITE;
/*!40000 ALTER TABLE `programmes` DISABLE KEYS */;
INSERT INTO `programmes` VALUES (1,1,'Primary Learning Programme','primary-learning-programme','primary','Primary','A structured programme supporting knowledge, skill, character and confidence.','Programme information is organised to help families understand the learning journey and the support available to pupils.',5,1,'published','2026-08-20 22:31:25',NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,'2026-08-21 18:47:22','2026-08-21 22:31:25',NULL);
/*!40000 ALTER TABLE `programmes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redirects`
--

DROP TABLE IF EXISTS `redirects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redirects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source_path` varchar(700) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_url` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `http_status` smallint unsigned NOT NULL DEFAULT '301',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `hit_count` bigint unsigned NOT NULL DEFAULT '0',
  `last_hit_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `redirects_source_path_unique` (`source_path`),
  KEY `redirects_created_by_foreign` (`created_by`),
  KEY `redirects_active_source` (`is_active`,`source_path`),
  CONSTRAINT `redirects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `redirects`
--

LOCK TABLES `redirects` WRITE;
/*!40000 ALTER TABLE `redirects` DISABLE KEYS */;
/*!40000 ALTER TABLE `redirects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_user` (
  `role_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `assigned_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `role_user_role_id_user_id_unique` (`role_id`,`user_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  KEY `role_user_assigned_by_foreign` (`assigned_by`),
  CONSTRAINT `role_user_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (1,1,1,'2026-08-21 22:31:24'),(2,3,1,'2026-08-21 20:14:33'),(5,2,1,'2026-08-21 22:31:24');
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Super Administrator','super-administrator','Super Administrator access profile.',1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(2,'School Administrator','school-administrator','School Administrator access profile.',1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(3,'Content Editor','content-editor','Content Editor access profile.',1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(4,'Admissions Officer','admissions-officer','Admissions Officer access profile.',1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(5,'Teacher Contributor','teacher-contributor','Teacher Contributor access profile.',1,'2026-08-21 18:47:21','2026-08-21 18:47:21');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('1T0t5S7jFvbOwsFt5VRjdMRzLTobVY8cyNakg1J3',1,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','eyJfdG9rZW4iOiJ4eXpYOEJIU0RYSUFEakQ5TE50WlA2QVFGMXd0OTk3dkFmTElsdHRZIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hYm91dCIsInJvdXRlIjoiYWJvdXQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=',1787557614),('pCVDB8tBEGVBwKOnaSHFs0BhsAGRgdOP0CcShYjG',1,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','eyJfdG9rZW4iOiJtR0J2THI4anJHMnE0UW1Ma1dBUWFtMmM5OUs3QXVyZGZTcTJEaThkIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9uZXdzIiwicm91dGUiOiJuZXdzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjF9',1787577193),('Qg3neMrZ91kaOVnVndvAx23LRyxFqGKERmIyDU9X',1,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','eyJfdG9rZW4iOiJJYW9WYjBLdDk4TmJsbndnMUx2cWNTUXVJSWVCbjNCc2I0MWdNOFNPIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvbWVkaWFcLzJcL3ByZXZpZXciLCJyb3V0ZSI6ImFkbWluLm1lZGlhLnByZXZpZXcifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=',1787501163);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_key` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value_json` json NOT NULL,
  `value_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_settings_group_name_setting_key_unique` (`group_name`,`setting_key`),
  KEY `site_settings_updated_by_foreign` (`updated_by`),
  CONSTRAINT `site_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (1,'identity','school_name','{\"value\": \"St. Charles Borromeo Pre & Primary School\"}','string',1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(2,'identity','motto','{\"value\": \"Learning with purpose\"}','string',1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(3,'contact','primary_email','{\"value\": \"\"}','string',1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(4,'contact','telephone','{\"value\": \"\"}','string',1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(5,'contact','address','{\"value\": \"\"}','string',1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(6,'seo','default_title','{\"value\": \"St. Charles Borromeo Pre & Primary School\"}','string',1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(7,'seo','default_description','{\"value\": \"Discover learning, school life and admissions at St. Charles Borromeo Pre & Primary School.\"}','string',1,1,'2026-08-21 18:47:21','2026-08-21 18:47:21'),(8,'analytics','enabled','{\"value\": false}','boolean',1,1,'2026-08-21 20:11:33','2026-08-21 22:31:25'),(9,'analytics','provider','{\"value\": \"none\"}','string',1,1,'2026-08-21 20:11:33','2026-08-21 22:31:25'),(10,'analytics','site_id','{\"value\": \"\"}','string',1,1,'2026-08-21 20:11:33','2026-08-21 22:31:25');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_members`
--

DROP TABLE IF EXISTS `staff_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_title` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `staff_type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_biography` text COLLATE utf8mb4_unicode_ci,
  `photo_media_id` bigint unsigned DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_leadership` tinyint(1) NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_members_slug_unique` (`slug`),
  KEY `staff_members_photo_media_id_foreign` (`photo_media_id`),
  KEY `staff_members_department_id_is_active_sort_order_index` (`department_id`,`is_active`,`sort_order`),
  KEY `staff_members_is_leadership_is_public_sort_order_index` (`is_leadership`,`is_public`,`sort_order`),
  CONSTRAINT `staff_members_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `staff_members_photo_media_id_foreign` FOREIGN KEY (`photo_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_members`
--

LOCK TABLES `staff_members` WRITE;
/*!40000 ALTER TABLE `staff_members` DISABLE KEYS */;
INSERT INTO `staff_members` VALUES (1,1,'School Administration','local-seed-administrator','Administration','administration','The administration team supports school operations and communication with families.',9,NULL,NULL,1,1,1,1,'2026-08-21 18:47:22','2026-08-21 18:47:22',NULL),(2,1,'Teaching Community','local-seed-teaching-community','Teaching Team','teaching','The teaching community guides learning, participation and pupil progress.',5,NULL,NULL,2,0,1,1,'2026-08-21 18:47:22','2026-08-21 18:47:22',NULL),(3,1,'Pastoral Support','local-seed-pastoral-support','Pastoral Support','pastoral','Pastoral support helps sustain wellbeing, dignity and belonging across school life.',4,NULL,NULL,3,0,1,1,'2026-08-21 18:47:22','2026-08-21 18:47:22',NULL);
/*!40000 ALTER TABLE `staff_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (1,'Community','community','2026-08-21 18:47:21','2026-08-21 18:47:21'),(2,'Graduation','graduation','2026-08-21 21:10:27','2026-08-21 21:10:27');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_is_active_deleted_at_index` (`is_active`,`deleted_at`),
  KEY `users_last_login_at_index` (`last_login_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System Administrator','local.admin@example.test','2026-08-21 22:31:24','$2y$12$oB9l2KXN4A4rodNzNFcr6uj0UmEfxuN7bmpq/FOh01t60SZJIAxD.',1,'2026-08-24 11:11:42','127.0.0.1',NULL,'2026-08-21 18:47:21','2026-08-24 11:11:42',NULL),(2,'Staff Contributor','local.teacher@example.test','2026-08-21 22:31:24','$2y$12$RaBVr9KSq7Hk4Ndwy.v8pOCOA7TZNspgF0h.m5Ybo2Y1Jn.dXsYEi',1,NULL,NULL,NULL,'2026-08-21 18:47:21','2026-08-21 22:31:24',NULL),(3,'content test','content.test@example.com',NULL,'$2y$12$zUkCx0lIIfPPfvWtoHbKpujwVaHXnKB5gMGMqFDQabbJGkbtU//c2',1,'2026-08-21 19:42:57','127.0.0.1',NULL,'2026-08-21 19:42:16','2026-08-21 20:15:02',NULL);
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

-- Dump completed on 2026-08-24 15:49:13
