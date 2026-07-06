-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: ebp_restaurant_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `accounting_periods`
--

DROP TABLE IF EXISTS `accounting_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounting_periods` (
  `period_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `fiscal_year` int(11) NOT NULL,
  `period_number` int(11) NOT NULL,
  `period_name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('OPEN','CLOSED','LOCKED') DEFAULT 'OPEN',
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`period_id`),
  UNIQUE KEY `uk_period` (`tenant_id`,`branch_id`,`fiscal_year`,`period_number`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_status` (`status`),
  KEY `idx_period_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_period_fiscal_year` (`fiscal_year`),
  KEY `idx_period_status` (`status`),
  KEY `idx_period_dates` (`start_date`,`end_date`),
  CONSTRAINT `accounting_periods_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  CONSTRAINT `chk_period_date_order` CHECK (`end_date` >= `start_date`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_periods`
--

LOCK TABLES `accounting_periods` WRITE;
/*!40000 ALTER TABLE `accounting_periods` DISABLE KEYS */;
INSERT INTO `accounting_periods` VALUES (7,1,2,2026,7,'July 2026','2026-07-01','2026-07-31','OPEN',NULL,NULL,NULL,'2026-07-06 18:57:24','2026-07-06 18:57:24',NULL);
/*!40000 ALTER TABLE `accounting_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts_payable`
--

DROP TABLE IF EXISTS `accounts_payable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts_payable` (
  `ap_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `bill_number` varchar(50) NOT NULL,
  `bill_date` date NOT NULL,
  `due_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `balance_amount` decimal(15,2) NOT NULL,
  `status` enum('DRAFT','PENDING','PARTIAL','PAID','OVERDUE','CANCELLED') DEFAULT 'PENDING',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ap_id`),
  UNIQUE KEY `uk_bill_number` (`tenant_id`,`bill_number`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_supplier` (`supplier_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_ap_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_ap_supplier` (`supplier_id`),
  KEY `idx_ap_status` (`status`),
  KEY `idx_ap_due_date` (`due_date`),
  KEY `idx_ap_bill_number` (`tenant_id`,`bill_number`),
  CONSTRAINT `accounts_payable_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  CONSTRAINT `accounts_payable_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  CONSTRAINT `chk_ap_balance_not_negative` CHECK (`balance_amount` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_payable`
--

LOCK TABLES `accounts_payable` WRITE;
/*!40000 ALTER TABLE `accounts_payable` DISABLE KEYS */;
INSERT INTO `accounts_payable` VALUES (1,1,2,1,'BILL-20260706-8975','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:50:21','2026-07-06 18:50:21',NULL),(2,1,2,1,'BILL-20260706-7609','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:51:14','2026-07-06 18:51:14',NULL),(3,1,2,1,'BILL-20260706-5605','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:52:05','2026-07-06 18:52:05',NULL),(4,1,2,1,'BILL-20260706-7845','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:52:28','2026-07-06 18:52:28',NULL),(5,1,2,1,'BILL-20260706-0540','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:52:55','2026-07-06 18:52:55',NULL),(6,1,2,1,'BILL-20260706-6186','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:53:46','2026-07-06 18:53:46',NULL),(7,1,2,1,'BILL-20260706-3546','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:54:26','2026-07-06 18:54:26',NULL),(8,1,2,1,'BILL-20260706-3952','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:55:02','2026-07-06 18:55:02',NULL),(9,1,2,1,'BILL-20260706-3345','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:56:09','2026-07-06 18:56:09',NULL),(10,1,2,1,'BILL-20260706-6606','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:57:10','2026-07-06 18:57:10',NULL),(11,1,2,1,'BILL-20260706-7064','2026-07-07','2026-08-05',3000.00,0.00,3000.00,'PENDING','Test bill','2026-07-06 18:57:24','2026-07-06 18:57:24',NULL);
/*!40000 ALTER TABLE `accounts_payable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts_receivable`
--

DROP TABLE IF EXISTS `accounts_receivable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts_receivable` (
  `ar_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `balance_amount` decimal(15,2) NOT NULL,
  `status` enum('DRAFT','PENDING','PARTIAL','PAID','OVERDUE','CANCELLED') DEFAULT 'PENDING',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ar_id`),
  UNIQUE KEY `uk_invoice_number` (`tenant_id`,`invoice_number`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_ar_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_ar_customer` (`customer_id`),
  KEY `idx_ar_status` (`status`),
  KEY `idx_ar_due_date` (`due_date`),
  KEY `idx_ar_invoice_number` (`tenant_id`,`invoice_number`),
  CONSTRAINT `accounts_receivable_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  CONSTRAINT `accounts_receivable_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  CONSTRAINT `chk_ar_balance_not_negative` CHECK (`balance_amount` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_receivable`
--

LOCK TABLES `accounts_receivable` WRITE;
/*!40000 ALTER TABLE `accounts_receivable` DISABLE KEYS */;
INSERT INTO `accounts_receivable` VALUES (11,1,2,1,'INV-20260706-4221','2026-07-07','2026-08-05',5000.00,4000.00,1000.00,'PARTIAL','Test invoice','2026-07-06 18:57:09','2026-07-06 18:57:24',NULL),(12,1,2,1,'INV-20260706-9020','2026-07-07','2026-08-05',5000.00,0.00,5000.00,'PENDING','Test invoice','2026-07-06 18:57:23','2026-07-06 18:57:23',NULL);
/*!40000 ALTER TABLE `accounts_receivable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_analytics`
--

DROP TABLE IF EXISTS `ad_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_analytics` (
  `analytics_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `analytics_date` date NOT NULL,
  `impressions` int(11) DEFAULT 0,
  `clicks` int(11) DEFAULT 0,
  `conversions` int(11) DEFAULT 0,
  `ctr` decimal(5,4) DEFAULT NULL,
  `conversion_rate` decimal(5,4) DEFAULT NULL,
  `cost_per_impression` decimal(10,4) DEFAULT NULL,
  `cost_per_click` decimal(10,4) DEFAULT NULL,
  `cost_per_conversion` decimal(10,4) DEFAULT NULL,
  `total_spend` decimal(15,2) DEFAULT NULL,
  `revenue_generated` decimal(15,2) DEFAULT NULL,
  `roi` decimal(5,4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`analytics_id`),
  UNIQUE KEY `uk_campaign_date` (`campaign_id`,`analytics_date`),
  KEY `idx_analytics_campaign` (`campaign_id`),
  KEY `idx_analytics_date` (`analytics_date`),
  CONSTRAINT `fk_analytics_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `ad_campaigns` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_analytics`
--

LOCK TABLES `ad_analytics` WRITE;
/*!40000 ALTER TABLE `ad_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_campaigns`
--

DROP TABLE IF EXISTS `ad_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_campaigns` (
  `campaign_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `campaign_code` varchar(50) NOT NULL,
  `campaign_name` varchar(150) NOT NULL,
  `campaign_type` enum('BANNER','SPONSORED_PRODUCT','FEATURED_SUPPLIER','PROMOTIONAL') NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `targeting_audience` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`targeting_audience`)),
  `targeting_location` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`targeting_location`)),
  `targeting_cuisine_type` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`targeting_cuisine_type`)),
  `status` enum('DRAFT','PENDING_APPROVAL','ACTIVE','PAUSED','COMPLETED','CANCELLED') DEFAULT 'DRAFT',
  `approval_status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`campaign_id`),
  UNIQUE KEY `campaign_code` (`campaign_code`),
  KEY `idx_ad_tenant` (`tenant_id`),
  KEY `idx_ad_type` (`campaign_type`),
  KEY `idx_ad_status` (`status`),
  KEY `idx_ad_dates` (`start_date`,`end_date`),
  CONSTRAINT `fk_ad_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_campaigns`
--

LOCK TABLES `ad_campaigns` WRITE;
/*!40000 ALTER TABLE `ad_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_clicks`
--

DROP TABLE IF EXISTS `ad_clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_clicks` (
  `click_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `impression_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `click_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`click_id`),
  KEY `fk_click_impression` (`impression_id`),
  KEY `idx_click_campaign` (`campaign_id`),
  KEY `idx_click_time` (`click_time`),
  CONSTRAINT `fk_click_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `ad_campaigns` (`campaign_id`),
  CONSTRAINT `fk_click_impression` FOREIGN KEY (`impression_id`) REFERENCES `ad_impressions` (`impression_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_clicks`
--

LOCK TABLES `ad_clicks` WRITE;
/*!40000 ALTER TABLE `ad_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_conversions`
--

DROP TABLE IF EXISTS `ad_conversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_conversions` (
  `conversion_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `click_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `conversion_type` varchar(50) DEFAULT NULL,
  `conversion_value` decimal(10,2) DEFAULT NULL,
  `conversion_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`conversion_id`),
  KEY `fk_conversion_click` (`click_id`),
  KEY `idx_conversion_campaign` (`campaign_id`),
  KEY `idx_conversion_time` (`conversion_time`),
  CONSTRAINT `fk_conversion_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `ad_campaigns` (`campaign_id`),
  CONSTRAINT `fk_conversion_click` FOREIGN KEY (`click_id`) REFERENCES `ad_clicks` (`click_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_conversions`
--

LOCK TABLES `ad_conversions` WRITE;
/*!40000 ALTER TABLE `ad_conversions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_conversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_impressions`
--

DROP TABLE IF EXISTS `ad_impressions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_impressions` (
  `impression_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `impression_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`impression_id`),
  KEY `idx_impression_campaign` (`campaign_id`),
  KEY `idx_impression_time` (`impression_time`),
  CONSTRAINT `fk_impression_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `ad_campaigns` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_impressions`
--

LOCK TABLES `ad_impressions` WRITE;
/*!40000 ALTER TABLE `ad_impressions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_impressions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `add_on_services`
--

DROP TABLE IF EXISTS `add_on_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `add_on_services` (
  `add_on_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `add_on_code` varchar(50) NOT NULL,
  `add_on_name` varchar(150) NOT NULL,
  `add_on_category` enum('AI_FEATURES','ADVANCED_ANALYTICS','PRIORITY_SUPPORT','CUSTOM_INTEGRATIONS','ADDITIONAL_STORAGE','WHITE_LABEL') NOT NULL,
  `description` text DEFAULT NULL,
  `pricing_model` enum('MONTHLY','QUARTERLY','ANNUAL','ONE_TIME') DEFAULT 'MONTHLY',
  `base_price` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`add_on_id`),
  UNIQUE KEY `add_on_code` (`add_on_code`),
  KEY `idx_add_on_category` (`add_on_category`),
  KEY `idx_add_on_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `add_on_services`
--

LOCK TABLES `add_on_services` WRITE;
/*!40000 ALTER TABLE `add_on_services` DISABLE KEYS */;
INSERT INTO `add_on_services` VALUES (1,'AI_ANALYTICS_BASIC','AI Analytics Basic','AI_FEATURES','Basic AI-powered analytics and insights','MONTHLY',50.00,'IDR',1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(2,'AI_ANALYTICS_ADVANCED','AI Analytics Advanced','AI_FEATURES','Advanced AI-powered analytics with predictive capabilities','MONTHLY',200.00,'IDR',1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(3,'ADVANCED_ANALYTICS','Advanced Analytics','ADVANCED_ANALYTICS','Advanced business intelligence and reporting','MONTHLY',30.00,'IDR',1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(4,'PRIORITY_SUPPORT_STANDARD','Priority Support Standard','PRIORITY_SUPPORT','Standard priority support with 24h response time','MONTHLY',50.00,'IDR',1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(5,'PRIORITY_SUPPORT_PREMIUM','Priority Support Premium','PRIORITY_SUPPORT','Premium priority support with 4h response time','MONTHLY',150.00,'IDR',1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(6,'CUSTOM_INTEGRATION','Custom Integration','CUSTOM_INTEGRATIONS','Custom third-party system integration','ONE_TIME',500.00,'IDR',1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(7,'ADDITIONAL_STORAGE','Additional Storage','ADDITIONAL_STORAGE','Additional 100GB storage space','MONTHLY',20.00,'IDR',1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(8,'WHITE_LABEL','White Label','WHITE_LABEL','White-label customization option','MONTHLY',100.00,'IDR',1,'2026-07-05 03:54:19','2026-07-05 03:54:19');
/*!40000 ALTER TABLE `add_on_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_autonomy_levels`
--

DROP TABLE IF EXISTS `ai_autonomy_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_autonomy_levels` (
  `autonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_id` bigint(20) unsigned NOT NULL,
  `autonomy_level` enum('RECOMMENDATION','AUTO_APPROVE_BOUNDS','FULL_AUTONOMY') NOT NULL,
  `bounds_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bounds_config`)),
  `approval_threshold` decimal(5,4) DEFAULT NULL,
  `human_review_required` tinyint(1) DEFAULT 1,
  `auto_approve_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`auto_approve_conditions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`autonomy_id`),
  KEY `idx_autonomy_model` (`model_id`),
  KEY `idx_autonomy_level` (`autonomy_level`),
  CONSTRAINT `fk_autonomy_model` FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_autonomy_levels`
--

LOCK TABLES `ai_autonomy_levels` WRITE;
/*!40000 ALTER TABLE `ai_autonomy_levels` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_autonomy_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_decision_logs`
--

DROP TABLE IF EXISTS `ai_decision_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_decision_logs` (
  `decision_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `prediction_id` bigint(20) unsigned DEFAULT NULL,
  `decision_type` varchar(50) NOT NULL,
  `decision_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`decision_data`)),
  `human_override` tinyint(1) DEFAULT 0,
  `override_reason` text DEFAULT NULL,
  `override_user_id` bigint(20) unsigned DEFAULT NULL,
  `decision_outcome` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`decision_outcome`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`decision_id`),
  KEY `fk_decision_prediction` (`prediction_id`),
  KEY `idx_decision_tenant` (`tenant_id`),
  KEY `idx_decision_model` (`model_id`),
  KEY `idx_decision_override` (`human_override`),
  KEY `idx_decision_created` (`created_at`),
  CONSTRAINT `fk_decision_model` FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`model_id`),
  CONSTRAINT `fk_decision_prediction` FOREIGN KEY (`prediction_id`) REFERENCES `ai_predictions` (`prediction_id`),
  CONSTRAINT `fk_decision_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_decision_logs`
--

LOCK TABLES `ai_decision_logs` WRITE;
/*!40000 ALTER TABLE `ai_decision_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_decision_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_governance_logs`
--

DROP TABLE IF EXISTS `ai_governance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_governance_logs` (
  `governance_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_id` bigint(20) unsigned NOT NULL,
  `log_type` enum('ETHICS_REVIEW','COMPLIANCE_CHECK','RISK_ASSESSMENT','AUDIT','MODEL_UPDATE','PERFORMANCE_REVIEW') NOT NULL,
  `log_description` text DEFAULT NULL,
  `reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `review_status` enum('PENDING','APPROVED','REJECTED','REQUIRES_CHANGES') DEFAULT 'PENDING',
  `risk_level` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'MEDIUM',
  `findings` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `action_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`action_items`)),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`governance_id`),
  KEY `idx_governance_model` (`model_id`),
  KEY `idx_governance_type` (`log_type`),
  KEY `idx_governance_status` (`review_status`),
  KEY `idx_governance_risk` (`risk_level`),
  CONSTRAINT `fk_governance_model` FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_governance_logs`
--

LOCK TABLES `ai_governance_logs` WRITE;
/*!40000 ALTER TABLE `ai_governance_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_governance_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_model_feedback`
--

DROP TABLE IF EXISTS `ai_model_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_model_feedback` (
  `feedback_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prediction_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `feedback_type` enum('POSITIVE','NEGATIVE','NEUTRAL') NOT NULL,
  `feedback_comment` text DEFAULT NULL,
  `actual_outcome` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`actual_outcome`)),
  `accuracy_rating` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`feedback_id`),
  KEY `idx_feedback_prediction` (`prediction_id`),
  KEY `idx_feedback_type` (`feedback_type`),
  CONSTRAINT `fk_feedback_prediction` FOREIGN KEY (`prediction_id`) REFERENCES `ai_predictions` (`prediction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_model_feedback`
--

LOCK TABLES `ai_model_feedback` WRITE;
/*!40000 ALTER TABLE `ai_model_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_model_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_models`
--

DROP TABLE IF EXISTS `ai_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_models` (
  `model_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_code` varchar(50) NOT NULL,
  `model_name` varchar(150) NOT NULL,
  `model_category` enum('PREDICTIVE','DECISION_SUPPORT','OPERATIONAL','CUSTOMER_EXPERIENCE','FINANCIAL') NOT NULL,
  `model_type` enum('DEMAND_FORECASTING','INVENTORY_OPTIMIZATION','STAFF_SCHEDULING','MENU_ENGINEERING','DYNAMIC_PRICING','SUPPLIER_SELECTION','KITCHEN_OPERATIONS','TABLE_MANAGEMENT','DELIVERY_OPTIMIZATION','PERSONALIZATION','SENTIMENT_ANALYSIS','CHURN_PREDICTION','REVENUE_FORECASTING','COST_OPTIMIZATION','FRAUD_DETECTION') NOT NULL,
  `model_version` varchar(20) NOT NULL,
  `model_description` text DEFAULT NULL,
  `autonomy_level` enum('RECOMMENDATION','AUTO_APPROVE_BOUNDS','FULL_AUTONOMY') DEFAULT 'RECOMMENDATION',
  `training_data_source` text DEFAULT NULL,
  `model_accuracy` decimal(5,4) DEFAULT NULL,
  `last_trained_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`model_id`),
  UNIQUE KEY `model_code` (`model_code`),
  KEY `idx_model_category` (`model_category`),
  KEY `idx_model_type` (`model_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_models`
--

LOCK TABLES `ai_models` WRITE;
/*!40000 ALTER TABLE `ai_models` DISABLE KEYS */;
INSERT INTO `ai_models` VALUES (1,'DEMAND_FORECAST','Demand Forecasting Model','PREDICTIVE','DEMAND_FORECASTING','1.0.0','Predicts daily and weekly demand for menu items','RECOMMENDATION',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(2,'INVENTORY_OPT','Inventory Optimization Model','PREDICTIVE','INVENTORY_OPTIMIZATION','1.0.0','Optimizes stock levels and reorder points','AUTO_APPROVE_BOUNDS',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(3,'STAFF_SCHED','Staff Scheduling Model','PREDICTIVE','STAFF_SCHEDULING','1.0.0','Optimizes staff schedules based on demand','RECOMMENDATION',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(4,'MENU_ENG','Menu Engineering Model','DECISION_SUPPORT','MENU_ENGINEERING','1.0.0','Recommends price adjustments and menu changes','RECOMMENDATION',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(5,'DYNAMIC_PRICE','Dynamic Pricing Model','DECISION_SUPPORT','DYNAMIC_PRICING','1.0.0','Suggests real-time price adjustments','AUTO_APPROVE_BOUNDS',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(6,'SUPPLIER_SEL','Supplier Selection Model','DECISION_SUPPORT','SUPPLIER_SELECTION','1.0.0','Recommends optimal suppliers','RECOMMENDATION',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(7,'KITCHEN_OPS','Kitchen Operations Model','OPERATIONAL','KITCHEN_OPERATIONS','1.0.0','Optimizes kitchen workflow','AUTO_APPROVE_BOUNDS',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(8,'TABLE_MGMT','Table Management Model','OPERATIONAL','TABLE_MANAGEMENT','1.0.0','Optimizes table assignment and turnover','AUTO_APPROVE_BOUNDS',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(9,'DELIVERY_OPT','Delivery Optimization Model','OPERATIONAL','DELIVERY_OPTIMIZATION','1.0.0','Optimizes delivery routes and timing','AUTO_APPROVE_BOUNDS',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(10,'PERSONALIZE','Personalization Model','CUSTOMER_EXPERIENCE','PERSONALIZATION','1.0.0','Personalizes recommendations for customers','RECOMMENDATION',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(11,'SENTIMENT','Sentiment Analysis Model','CUSTOMER_EXPERIENCE','SENTIMENT_ANALYSIS','1.0.0','Analyzes customer reviews and feedback','RECOMMENDATION',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(12,'CHURN_PRED','Churn Prediction Model','CUSTOMER_EXPERIENCE','CHURN_PREDICTION','1.0.0','Predicts customer churn risk','RECOMMENDATION',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(13,'REVENUE_FORE','Revenue Forecasting Model','FINANCIAL','REVENUE_FORECASTING','1.0.0','Forecasts revenue and financial metrics','RECOMMENDATION',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(14,'COST_OPT','Cost Optimization Model','FINANCIAL','COST_OPTIMIZATION','1.0.0','Identifies cost reduction opportunities','RECOMMENDATION',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24'),(15,'FRAUD_DET','Fraud Detection Model','FINANCIAL','FRAUD_DETECTION','1.0.0','Detects fraudulent transactions','FULL_AUTONOMY',NULL,NULL,NULL,1,'2026-07-05 03:53:24','2026-07-05 03:53:24');
/*!40000 ALTER TABLE `ai_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_predictions`
--

DROP TABLE IF EXISTS `ai_predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_predictions` (
  `prediction_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `prediction_type` varchar(50) NOT NULL,
  `input_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`input_data`)),
  `prediction_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prediction_result`)),
  `confidence_score` decimal(5,4) DEFAULT NULL,
  `prediction_metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prediction_metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`prediction_id`),
  KEY `idx_prediction_tenant` (`tenant_id`),
  KEY `idx_prediction_model` (`model_id`),
  KEY `idx_prediction_type` (`prediction_type`),
  KEY `idx_prediction_created` (`created_at`),
  CONSTRAINT `fk_prediction_model` FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`model_id`),
  CONSTRAINT `fk_prediction_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_predictions`
--

LOCK TABLES `ai_predictions` WRITE;
/*!40000 ALTER TABLE `ai_predictions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_predictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_payments`
--

DROP TABLE IF EXISTS `ap_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ap_payments` (
  `ap_payment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `ap_id` bigint(20) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ap_payment_id`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_ap` (`ap_id`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_app_ap` (`ap_id`),
  KEY `idx_app_date` (`payment_date`),
  KEY `idx_app_tenant_branch` (`tenant_id`,`branch_id`),
  CONSTRAINT `ap_payments_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  CONSTRAINT `ap_payments_ibfk_2` FOREIGN KEY (`ap_id`) REFERENCES `accounts_payable` (`ap_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_payments`
--

LOCK TABLES `ap_payments` WRITE;
/*!40000 ALTER TABLE `ap_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ap_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ar_payments`
--

DROP TABLE IF EXISTS `ar_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ar_payments` (
  `ar_payment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `ar_id` bigint(20) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ar_payment_id`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_ar` (`ar_id`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_arp_ar` (`ar_id`),
  KEY `idx_arp_date` (`payment_date`),
  KEY `idx_arp_tenant_branch` (`tenant_id`,`branch_id`),
  CONSTRAINT `ar_payments_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  CONSTRAINT `ar_payments_ibfk_2` FOREIGN KEY (`ar_id`) REFERENCES `accounts_receivable` (`ar_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ar_payments`
--

LOCK TABLES `ar_payments` WRITE;
/*!40000 ALTER TABLE `ar_payments` DISABLE KEYS */;
INSERT INTO `ar_payments` VALUES (12,1,2,11,'2026-07-07',2000.00,'CASH',NULL,'Test payment','2026-07-06 18:57:09','2026-07-06 18:57:09',NULL),(13,1,2,11,'2026-07-07',2000.00,'CASH',NULL,'Test payment','2026-07-06 18:57:23','2026-07-06 18:57:23',NULL);
/*!40000 ALTER TABLE `ar_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_depreciation`
--

DROP TABLE IF EXISTS `asset_depreciation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_depreciation` (
  `depreciation_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint(20) unsigned NOT NULL,
  `fiscal_year` int(11) NOT NULL,
  `fiscal_month` int(11) NOT NULL,
  `depreciation_amount` decimal(15,2) NOT NULL,
  `accumulated_depreciation` decimal(15,2) NOT NULL,
  `book_value` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`depreciation_id`),
  UNIQUE KEY `uk_asset_period` (`asset_id`,`fiscal_year`,`fiscal_month`),
  KEY `idx_asset` (`asset_id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  CONSTRAINT `asset_depreciation_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `fixed_assets` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_depreciation`
--

LOCK TABLES `asset_depreciation` WRITE;
/*!40000 ALTER TABLE `asset_depreciation` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_depreciation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `attendance_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` time NOT NULL,
  `check_out_time` time DEFAULT NULL,
  `break_start_time` time DEFAULT NULL,
  `break_end_time` time DEFAULT NULL,
  `work_hours` decimal(5,2) DEFAULT 0.00,
  `overtime_hours` decimal(5,2) DEFAULT 0.00,
  `status` enum('PRESENT','ABSENT','LATE','EARLY_LEAVE','HALF_DAY','ON_LEAVE') DEFAULT 'PRESENT',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `unique_employee_date` (`employee_id`,`attendance_date`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_branch_id` (`branch_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_attendance_date` (`attendance_date`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,1,1,1,'2026-07-07','08:00:00','17:00:00',NULL,NULL,9.00,0.00,'PRESENT',NULL,'2026-07-06 19:27:12','2026-07-06 19:27:12'),(2,3,1,5,'2026-07-07','08:30:00','17:30:00',NULL,NULL,9.00,0.00,'PRESENT',NULL,'2026-07-06 19:27:12','2026-07-06 19:27:12'),(3,3,1,6,'2026-07-07','09:00:00','18:00:00',NULL,NULL,9.00,0.00,'PRESENT',NULL,'2026-07-06 19:27:12','2026-07-06 19:27:12');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `audit_log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `record_id` bigint(20) unsigned DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`audit_log_id`),
  KEY `idx_audit_logs_tenant_id` (`tenant_id`),
  KEY `idx_audit_logs_user_id` (`user_id`),
  KEY `idx_audit_logs_module` (`module`),
  KEY `idx_audit_logs_action` (`action`),
  KEY `idx_audit_logs_created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_logs`
--

DROP TABLE IF EXISTS `backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_logs` (
  `backup_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `backup_type` enum('FULL','INCREMENTAL','DIFFERENTIAL') NOT NULL,
  `backup_source` enum('DATABASE','FILES','SYSTEM') NOT NULL,
  `backup_location` varchar(255) DEFAULT NULL,
  `backup_size_mb` decimal(10,2) DEFAULT NULL,
  `backup_status` enum('STARTED','COMPLETED','FAILED','VERIFIED') DEFAULT 'STARTED',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `verification_status` enum('PENDING','SUCCESS','FAILED') DEFAULT 'PENDING',
  `verification_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `retention_days` int(11) DEFAULT 30,
  `created_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`backup_id`),
  KEY `idx_backup_tenant` (`tenant_id`),
  KEY `idx_backup_type` (`backup_type`),
  KEY `idx_backup_status` (`backup_status`),
  KEY `idx_backup_started` (`started_at`),
  CONSTRAINT `fk_backup_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_logs`
--

LOCK TABLES `backup_logs` WRITE;
/*!40000 ALTER TABLE `backup_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_accounts`
--

DROP TABLE IF EXISTS `bank_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_accounts` (
  `bank_account_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_type` enum('CHECKING','SAVINGS','CREDIT_CARD','CASH') DEFAULT 'CHECKING',
  `currency` varchar(3) DEFAULT 'IDR',
  `balance` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`bank_account_id`),
  UNIQUE KEY `uk_account_number` (`tenant_id`,`account_number`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `bank_accounts_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_accounts`
--

LOCK TABLES `bank_accounts` WRITE;
/*!40000 ALTER TABLE `bank_accounts` DISABLE KEYS */;
INSERT INTO `bank_accounts` VALUES (1,1,2,'Main Operating Account','1234567890','BCA','CHECKING','IDR',1000000.00,1,'2026-07-06 18:40:04','2026-07-06 18:40:04',NULL),(2,1,2,'Petty Cash Account','0987654321','Mandiri','SAVINGS','IDR',500000.00,1,'2026-07-06 18:40:04','2026-07-06 18:40:04',NULL);
/*!40000 ALTER TABLE `bank_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_reconciliation_items`
--

DROP TABLE IF EXISTS `bank_reconciliation_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_reconciliation_items` (
  `reconciliation_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reconciliation_id` bigint(20) unsigned NOT NULL,
  `item_type` enum('DEPOSIT','WITHDRAWAL','ADJUSTMENT') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reconciliation_item_id`),
  KEY `idx_reconciliation` (`reconciliation_id`),
  KEY `idx_item_type` (`item_type`),
  CONSTRAINT `bank_reconciliation_items_ibfk_1` FOREIGN KEY (`reconciliation_id`) REFERENCES `bank_reconciliations` (`reconciliation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_reconciliation_items`
--

LOCK TABLES `bank_reconciliation_items` WRITE;
/*!40000 ALTER TABLE `bank_reconciliation_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_reconciliation_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_reconciliations`
--

DROP TABLE IF EXISTS `bank_reconciliations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_reconciliations` (
  `reconciliation_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `bank_account_id` bigint(20) unsigned NOT NULL,
  `reconciliation_date` date NOT NULL,
  `statement_balance` decimal(15,2) NOT NULL,
  `book_balance` decimal(15,2) NOT NULL,
  `difference` decimal(15,2) NOT NULL,
  `status` enum('DRAFT','RECONCILED','UNRECONCILED') DEFAULT 'DRAFT',
  `reconciled_by` bigint(20) unsigned DEFAULT NULL,
  `reconciled_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`reconciliation_id`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_bank_account` (`bank_account_id`),
  KEY `idx_reconciliation_date` (`reconciliation_date`),
  KEY `idx_status` (`status`),
  KEY `idx_br_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_br_bank_account` (`bank_account_id`),
  KEY `idx_br_status` (`status`),
  KEY `idx_br_date` (`reconciliation_date`),
  CONSTRAINT `bank_reconciliations_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  CONSTRAINT `bank_reconciliations_ibfk_2` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`bank_account_id`),
  CONSTRAINT `chk_br_balances_not_negative` CHECK (`statement_balance` >= 0 and `book_balance` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_reconciliations`
--

LOCK TABLES `bank_reconciliations` WRITE;
/*!40000 ALTER TABLE `bank_reconciliations` DISABLE KEYS */;
INSERT INTO `bank_reconciliations` VALUES (6,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:41:51','2026-07-06 18:41:51',NULL),(7,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:42:50','2026-07-06 18:42:50',NULL),(8,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:43:57','2026-07-06 18:43:57',NULL),(9,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:44:37','2026-07-06 18:44:37',NULL),(10,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:46:29','2026-07-06 18:46:29',NULL),(11,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:47:10','2026-07-06 18:47:10',NULL),(12,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:48:40','2026-07-06 18:48:40',NULL),(13,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:49:31','2026-07-06 18:49:31',NULL),(14,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:50:22','2026-07-06 18:50:22',NULL),(15,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:51:14','2026-07-06 18:51:14',NULL),(16,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:52:05','2026-07-06 18:52:05',NULL),(17,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:52:28','2026-07-06 18:52:28',NULL),(18,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:52:56','2026-07-06 18:52:56',NULL),(19,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:53:46','2026-07-06 18:53:46',NULL),(20,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:54:26','2026-07-06 18:54:26',NULL),(21,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:55:02','2026-07-06 18:55:02',NULL),(22,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:56:09','2026-07-06 18:56:09',NULL),(23,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:57:10','2026-07-06 18:57:10',NULL),(24,1,2,1,'2026-07-07',10000.00,1000000.00,-990000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:57:24','2026-07-06 18:57:24',NULL);
/*!40000 ALTER TABLE `bank_reconciliations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beta_feedback`
--

DROP TABLE IF EXISTS `beta_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `beta_feedback` (
  `feedback_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `participant_id` bigint(20) unsigned NOT NULL,
  `feedback_category` enum('FEATURE_REQUEST','BUG_REPORT','UX_IMPROVEMENT','PERFORMANCE','GENERAL') NOT NULL,
  `feedback_subject` varchar(200) DEFAULT NULL,
  `feedback_description` text DEFAULT NULL,
  `severity` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'MEDIUM',
  `status` enum('NEW','REVIEWING','IMPLEMENTED','DECLINED','DEFERRED') DEFAULT 'NEW',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `priority` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`feedback_id`),
  KEY `idx_feedback_participant` (`participant_id`),
  KEY `idx_feedback_category` (`feedback_category`),
  KEY `idx_feedback_status` (`status`),
  KEY `idx_feedback_severity` (`severity`),
  CONSTRAINT `fk_feedback_participant` FOREIGN KEY (`participant_id`) REFERENCES `beta_program_participants` (`participant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beta_feedback`
--

LOCK TABLES `beta_feedback` WRITE;
/*!40000 ALTER TABLE `beta_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `beta_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beta_program_participants`
--

DROP TABLE IF EXISTS `beta_program_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `beta_program_participants` (
  `participant_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `participant_type` enum('EARLY_ADOPTER','INDUSTRY_EXPERT','PARTNER','EMPLOYEE') NOT NULL,
  `participant_name` varchar(150) DEFAULT NULL,
  `participant_email` varchar(100) DEFAULT NULL,
  `participant_phone` varchar(50) DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `business_type` varchar(50) DEFAULT NULL,
  `status` enum('INVITED','ACCEPTED','ACTIVE','COMPLETED','WITHDRAWN') DEFAULT 'INVITED',
  `invited_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `feedback_score` int(11) DEFAULT NULL,
  `feedback_count` int(11) DEFAULT 0,
  `incentive_value` decimal(10,2) DEFAULT NULL,
  `incentive_status` enum('PENDING','EARNED','PAID','FORFEITED') DEFAULT 'PENDING',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`participant_id`),
  KEY `idx_beta_tenant` (`tenant_id`),
  KEY `idx_beta_type` (`participant_type`),
  KEY `idx_beta_status` (`status`),
  CONSTRAINT `fk_beta_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beta_program_participants`
--

LOCK TABLES `beta_program_participants` WRITE;
/*!40000 ALTER TABLE `beta_program_participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `beta_program_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `branch_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `branch_code` varchar(50) NOT NULL,
  `branch_name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `delivery_radius_km` decimal(5,2) DEFAULT 5.00,
  `is_main` tinyint(1) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`branch_id`),
  UNIQUE KEY `idx_branches_tenant_code` (`tenant_id`,`branch_code`),
  KEY `idx_branches_tenant_id` (`tenant_id`),
  KEY `idx_branches_company_id` (`company_id`),
  KEY `idx_branches_status` (`status`),
  KEY `idx_branches_location` (`latitude`,`longitude`),
  CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `branches_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (2,1,2,'MAIN','Main Branch','123 Main Street',NULL,NULL,NULL,NULL,5.00,0,'ACTIVE','2026-07-02 07:41:03','2026-07-02 07:41:03',NULL);
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_items`
--

DROP TABLE IF EXISTS `budget_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_items` (
  `budget_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `budget_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `budgeted_amount` decimal(15,2) NOT NULL,
  `actual_amount` decimal(15,2) DEFAULT 0.00,
  `variance` decimal(15,2) DEFAULT 0.00,
  `period_type` enum('MONTHLY','QUARTERLY','YEARLY') DEFAULT 'MONTHLY',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`budget_item_id`),
  KEY `idx_budget` (`budget_id`),
  KEY `idx_account` (`account_id`),
  KEY `idx_bi_budget` (`budget_id`),
  KEY `idx_bi_account` (`account_id`),
  CONSTRAINT `budget_items_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`budget_id`),
  CONSTRAINT `budget_items_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`account_id`),
  CONSTRAINT `chk_bi_budgeted_not_negative` CHECK (`budgeted_amount` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_items`
--

LOCK TABLES `budget_items` WRITE;
/*!40000 ALTER TABLE `budget_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budgets`
--

DROP TABLE IF EXISTS `budgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budgets` (
  `budget_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `budget_name` varchar(100) NOT NULL,
  `fiscal_year` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_budget` decimal(15,2) NOT NULL,
  `status` enum('DRAFT','APPROVED','ACTIVE','CLOSED') DEFAULT 'DRAFT',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`budget_id`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_status` (`status`),
  KEY `idx_budget_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_budget_fiscal_year` (`fiscal_year`),
  KEY `idx_budget_status` (`status`),
  CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  CONSTRAINT `chk_budget_total_positive` CHECK (`total_budget` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budgets`
--

LOCK TABLES `budgets` WRITE;
/*!40000 ALTER TABLE `budgets` DISABLE KEYS */;
INSERT INTO `budgets` VALUES (1,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:26:16','2026-07-06 18:26:16',NULL),(2,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:28:28','2026-07-06 18:28:28',NULL),(3,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:30:17','2026-07-06 18:30:17',NULL),(4,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:32:07','2026-07-06 18:32:07',NULL),(5,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:37:22','2026-07-06 18:37:22',NULL),(6,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:41:51','2026-07-06 18:41:51',NULL),(7,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:42:51','2026-07-06 18:42:51',NULL),(8,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:43:57','2026-07-06 18:43:57',NULL),(9,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:44:37','2026-07-06 18:44:37',NULL),(10,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:46:30','2026-07-06 18:46:30',NULL),(11,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:47:10','2026-07-06 18:47:10',NULL),(12,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:48:40','2026-07-06 18:48:40',NULL),(13,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:49:31','2026-07-06 18:49:31',NULL),(14,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:50:22','2026-07-06 18:50:22',NULL),(15,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:51:14','2026-07-06 18:51:14',NULL),(16,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:52:06','2026-07-06 18:52:06',NULL),(17,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:52:28','2026-07-06 18:52:28',NULL),(18,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:52:56','2026-07-06 18:52:56',NULL),(19,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:53:46','2026-07-06 18:53:46',NULL),(20,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:54:26','2026-07-06 18:54:26',NULL),(21,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:55:02','2026-07-06 18:55:02',NULL),(22,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:56:10','2026-07-06 18:56:10',NULL),(23,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:57:10','2026-07-06 18:57:10',NULL),(24,1,2,'Test Budget 2026',2026,'2026-01-01','2026-12-31',1000000.00,'DRAFT',NULL,NULL,NULL,'2026-07-06 18:57:24','2026-07-06 18:57:24',NULL);
/*!40000 ALTER TABLE `budgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_hours`
--

DROP TABLE IF EXISTS `business_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_hours` (
  `business_hour_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `day_of_week` tinyint(4) NOT NULL COMMENT '1=Monday, 7=Sunday',
  `open_time` time NOT NULL,
  `close_time` time NOT NULL,
  `is_closed` tinyint(1) DEFAULT 0,
  `break_start_time` time DEFAULT NULL,
  `break_end_time` time DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`business_hour_id`),
  UNIQUE KEY `unique_tenant_branch_day` (`tenant_id`,`branch_id`,`day_of_week`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_branch_id` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_hours`
--

LOCK TABLES `business_hours` WRITE;
/*!40000 ALTER TABLE `business_hours` DISABLE KEYS */;
INSERT INTO `business_hours` VALUES (1,1,1,1,'08:00:00','20:00:00',0,NULL,NULL,1,'2026-07-06 19:26:18','2026-07-06 19:26:18'),(2,1,1,2,'08:00:00','20:00:00',0,NULL,NULL,1,'2026-07-06 19:26:18','2026-07-06 19:26:18'),(3,1,1,3,'08:00:00','20:00:00',0,NULL,NULL,1,'2026-07-06 19:26:18','2026-07-06 19:26:18'),(4,1,1,4,'08:00:00','20:00:00',0,NULL,NULL,1,'2026-07-06 19:26:18','2026-07-06 19:26:18'),(5,1,1,5,'08:00:00','22:00:00',0,NULL,NULL,1,'2026-07-06 19:26:18','2026-07-06 19:26:18'),(6,1,1,6,'09:00:00','23:00:00',0,NULL,NULL,1,'2026-07-06 19:26:18','2026-07-06 19:26:18'),(7,1,1,7,'10:00:00','18:00:00',0,NULL,NULL,1,'2026-07-06 19:26:18','2026-07-06 19:26:18');
/*!40000 ALTER TABLE `business_hours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_flow_items`
--

DROP TABLE IF EXISTS `cash_flow_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_flow_items` (
  `cash_flow_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `transaction_date` date NOT NULL,
  `cash_flow_type` enum('OPERATING','INVESTING','FINANCING') NOT NULL,
  `sub_type` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`cash_flow_id`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_cash_flow_type` (`cash_flow_type`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_cf_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_cf_date` (`transaction_date`),
  KEY `idx_cf_type` (`cash_flow_type`),
  KEY `idx_cf_reference` (`reference_type`,`reference_id`),
  CONSTRAINT `cash_flow_items_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_flow_items`
--

LOCK TABLES `cash_flow_items` WRITE;
/*!40000 ALTER TABLE `cash_flow_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cash_flow_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `category_code` varchar(50) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `idx_categories_tenant_code` (`tenant_id`,`category_code`),
  KEY `idx_categories_tenant_id` (`tenant_id`),
  KEY `idx_categories_parent_id` (`parent_id`),
  KEY `idx_categories_status` (`status`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,1,'MAIN','Main Course',NULL,NULL,0,'ACTIVE','2026-07-02 11:53:10','2026-07-02 11:53:10',NULL,NULL),(2,1,'BEV','Beverages',NULL,NULL,0,'ACTIVE','2026-07-02 11:53:10','2026-07-02 11:53:10',NULL,NULL),(3,1,'APP','Appetizers',NULL,NULL,0,'ACTIVE','2026-07-02 11:53:10','2026-07-02 11:53:10',NULL,NULL),(4,1,'HOME_FOOD','Home Food','Home-cooked food items',NULL,0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL,NULL),(5,3,'MAIN_COURSE','Main Course','Main course dishes',NULL,0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL,NULL),(6,3,'BEVERAGES','Beverages','Drinks and beverages',NULL,0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL,NULL),(7,3,'DESSERTS','Desserts','Sweet items',NULL,0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL,NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chart_of_accounts`
--

DROP TABLE IF EXISTS `chart_of_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chart_of_accounts` (
  `account_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `account_code` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` enum('ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE') NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `idx_chart_of_accounts_tenant_code` (`tenant_id`,`account_code`),
  KEY `idx_chart_of_accounts_tenant_id` (`tenant_id`),
  KEY `idx_chart_of_accounts_type` (`account_type`),
  KEY `idx_chart_of_accounts_parent_id` (`parent_id`),
  KEY `idx_coa_tenant_type` (`tenant_id`,`account_type`),
  KEY `idx_coa_tenant_code` (`tenant_id`,`account_code`),
  KEY `idx_coa_active` (`is_active`),
  CONSTRAINT `chart_of_accounts_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chart_of_accounts_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `chart_of_accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_of_accounts`
--

LOCK TABLES `chart_of_accounts` WRITE;
/*!40000 ALTER TABLE `chart_of_accounts` DISABLE KEYS */;
INSERT INTO `chart_of_accounts` VALUES (1,1,'1000','Cash','ASSET',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(2,1,'1100','Accounts Receivable','ASSET',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(3,1,'1200','Inventory','ASSET',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(4,1,'1300','Prepaid Expenses','ASSET',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(5,1,'1500','Fixed Assets','ASSET',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(6,1,'1600','Accumulated Depreciation','ASSET',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(7,1,'2000','Accounts Payable','LIABILITY',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(8,1,'2100','Salaries Payable','LIABILITY',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(9,1,'2200','Taxes Payable','LIABILITY',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(10,1,'2300','Loans Payable','LIABILITY',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(11,1,'3000','Owner Equity','EQUITY',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(12,1,'3100','Retained Earnings','EQUITY',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(13,1,'4000','Sales Revenue','REVENUE',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(14,1,'4100','Service Revenue','REVENUE',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(15,1,'4200','Other Revenue','REVENUE',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(16,1,'6000','Cost of Goods Sold','EXPENSE',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(17,1,'6100','Salary Expense','EXPENSE',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(18,1,'6200','Rent Expense','EXPENSE',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(19,1,'6300','Utility Expense','EXPENSE',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(20,1,'6400','Marketing Expense','EXPENSE',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL),(21,1,'6500','Other Expenses','EXPENSE',NULL,1,'2026-07-06 18:31:51','2026-07-06 18:31:51',NULL);
/*!40000 ALTER TABLE `chart_of_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `company_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `company_code` varchar(50) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`company_id`),
  UNIQUE KEY `idx_companies_tenant_code` (`tenant_id`,`company_code`),
  KEY `idx_companies_tenant_id` (`tenant_id`),
  KEY `idx_companies_status` (`status`),
  CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (2,1,'DEFAULT','Default Restaurant',NULL,NULL,NULL,NULL,'ACTIVE','2026-07-02 07:41:03','2026-07-02 07:41:03',NULL);
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `currencies` (
  `currency_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `currency_code` varchar(3) NOT NULL,
  `currency_name` varchar(100) NOT NULL,
  `currency_symbol` varchar(10) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`currency_id`),
  UNIQUE KEY `currency_code` (`currency_code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES (1,'IDR','Indonesian Rupiah','Rp',1,'2026-07-06 18:22:46'),(2,'USD','United States Dollar','$',1,'2026-07-06 18:22:46'),(3,'EUR','Euro','â‚¬',1,'2026-07-06 18:22:46'),(4,'SGD','Singapore Dollar','S$',1,'2026-07-06 18:22:46'),(5,'MYR','Malaysian Ringgit','RM',1,'2026-07-06 18:22:46'),(6,'THB','Thai Baht','à¸¿',1,'2026-07-06 18:22:46'),(7,'JPY','Japanese Yen','Â¥',1,'2026-07-06 18:22:46'),(8,'CNY','Chinese Yuan','Â¥',1,'2026-07-06 18:22:46'),(9,'GBP','British Pound','Â£',1,'2026-07-06 18:22:46'),(10,'AUD','Australian Dollar','A$',1,'2026-07-06 18:22:46');
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_modules`
--

DROP TABLE IF EXISTS `custom_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_modules` (
  `custom_module_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `module_code` varchar(50) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `module_category` varchar(50) DEFAULT 'CUSTOM',
  `description` text DEFAULT NULL,
  `module_type` enum('CORE','CUSTOM','INTEGRATION') DEFAULT 'CUSTOM',
  `is_enabled` tinyint(1) DEFAULT 1,
  `is_premium` tinyint(1) DEFAULT 0,
  `pricing_tier` text DEFAULT NULL,
  `custom_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_config`)),
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`custom_module_id`),
  UNIQUE KEY `unique_tenant_module` (`tenant_id`,`module_code`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_module_code` (`module_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_modules`
--

LOCK TABLES `custom_modules` WRITE;
/*!40000 ALTER TABLE `custom_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_permissions`
--

DROP TABLE IF EXISTS `custom_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_permissions` (
  `custom_permission_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `permission_code` varchar(100) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module_code` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`custom_permission_id`),
  UNIQUE KEY `unique_tenant_permission` (`tenant_id`,`permission_code`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_module_code` (`module_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_permissions`
--

LOCK TABLES `custom_permissions` WRITE;
/*!40000 ALTER TABLE `custom_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `customer_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL DEFAULT 1,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `loyalty_points` int(11) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,1,'John Doe','john@example.com','08123456789','Jl. Sudirman No. 1',100,'ACTIVE','2026-07-05 17:25:18','2026-07-05 17:25:18',NULL),(2,1,'Jane Smith','jane@example.com','08198765432','Jl. Thamrin No. 2',50,'ACTIVE','2026-07-05 17:25:18','2026-07-05 17:25:18',NULL),(3,1,'Bob Johnson','bob@example.com','08111222333','Jl. Gatot Subroto No. 3',25,'ACTIVE','2026-07-05 17:25:18','2026-07-05 17:25:18',NULL),(4,1,'Test Customer 1','customer1@test.com','08123456789','Jakarta',0,'ACTIVE','2026-07-06 18:39:30','2026-07-06 18:39:30',NULL),(5,1,'Test Customer 2','customer2@test.com','08123456790','Bandung',0,'ACTIVE','2026-07-06 18:39:30','2026-07-06 18:39:30',NULL),(6,1,'Test Customer 1','customer1@test.com','08123456789','Jakarta',0,'ACTIVE','2026-07-06 18:39:39','2026-07-06 18:39:39',NULL),(7,1,'Test Customer 2','customer2@test.com','08123456790','Bandung',0,'ACTIVE','2026-07-06 18:39:39','2026-07-06 18:39:39',NULL),(8,1,'Test Customer 1','customer1@test.com','08123456789','Jakarta',0,'ACTIVE','2026-07-06 18:39:52','2026-07-06 18:39:52',NULL),(9,1,'Test Customer 2','customer2@test.com','08123456790','Bandung',0,'ACTIVE','2026-07-06 18:39:52','2026-07-06 18:39:52',NULL),(10,1,'Test Customer 1','customer1@test.com','08123456789','Jakarta',0,'ACTIVE','2026-07-06 18:40:04','2026-07-06 18:40:04',NULL),(11,1,'Test Customer 2','customer2@test.com','08123456790','Bandung',0,'ACTIVE','2026-07-06 18:40:04','2026-07-06 18:40:04',NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_product_subscriptions`
--

DROP TABLE IF EXISTS `data_product_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_product_subscriptions` (
  `subscription_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `subscription_start_date` date NOT NULL,
  `subscription_end_date` date DEFAULT NULL,
  `status` enum('ACTIVE','SUSPENDED','CANCELLED','EXPIRED') DEFAULT 'ACTIVE',
  `price_paid` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`subscription_id`),
  KEY `idx_data_sub_tenant` (`tenant_id`),
  KEY `idx_data_sub_product` (`product_id`),
  KEY `idx_data_sub_status` (`status`),
  CONSTRAINT `fk_data_sub_product` FOREIGN KEY (`product_id`) REFERENCES `data_products` (`product_id`),
  CONSTRAINT `fk_data_sub_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_product_subscriptions`
--

LOCK TABLES `data_product_subscriptions` WRITE;
/*!40000 ALTER TABLE `data_product_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_product_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_products`
--

DROP TABLE IF EXISTS `data_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_products` (
  `product_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `product_type` enum('INDUSTRY_INSIGHTS','SUPPLIER_LEADS','MARKET_TRENDS','CUSTOM_ANALYTICS') NOT NULL,
  `description` text DEFAULT NULL,
  `data_source` text DEFAULT NULL,
  `update_frequency` enum('DAILY','WEEKLY','MONTHLY','QUARTERLY') DEFAULT 'MONTHLY',
  `pricing_model` enum('SUBSCRIPTION','PER_LEAD','CUSTOM') NOT NULL,
  `base_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `product_code` (`product_code`),
  KEY `idx_data_product_type` (`product_type`),
  KEY `idx_data_product_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_products`
--

LOCK TABLES `data_products` WRITE;
/*!40000 ALTER TABLE `data_products` DISABLE KEYS */;
INSERT INTO `data_products` VALUES (1,'IND_INSIGHTS','Restaurant Industry Insights','INDUSTRY_INSIGHTS','Comprehensive industry insights and market analysis',NULL,'MONTHLY','SUBSCRIPTION',99.00,'IDR',1,'2026-07-05 03:53:58','2026-07-05 03:53:58'),(2,'SUP_LEADS','Supplier Lead Generation','SUPPLIER_LEADS','Qualified leads for suppliers',NULL,'WEEKLY','PER_LEAD',5.00,'IDR',1,'2026-07-05 03:53:58','2026-07-05 03:53:58'),(3,'MKT_TRENDS','Market Trend Reports','MARKET_TRENDS','Detailed market trend analysis and forecasts',NULL,'MONTHLY','SUBSCRIPTION',199.00,'IDR',1,'2026-07-05 03:53:58','2026-07-05 03:53:58'),(4,'CUST_ANALYTICS','Custom Analytics Dashboard','CUSTOM_ANALYTICS','Custom-built analytics dashboard for your business',NULL,'DAILY','SUBSCRIPTION',299.00,'IDR',1,'2026-07-05 03:53:58','2026-07-05 03:53:58');
/*!40000 ALTER TABLE `data_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deliveries` (
  `delivery_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL DEFAULT 1,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `delivery_address` text NOT NULL,
  `phone` varchar(50) NOT NULL,
  `delivery_time` timestamp NULL DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `driver_name` varchar(100) DEFAULT NULL,
  `status` enum('PENDING','IN_TRANSIT','DELIVERED','CANCELLED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`delivery_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_order` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
INSERT INTO `deliveries` VALUES (1,1,NULL,1,'John Doe','Jl. Sudirman No. 1','08123456789','2026-07-05 18:25:18',15000.00,'Driver A','PENDING','2026-07-05 17:25:18','2026-07-05 17:25:18',NULL),(2,1,NULL,2,'Jane Smith','Jl. Thamrin No. 2','08198765432','2026-07-05 19:25:18',20000.00,'Driver B','IN_TRANSIT','2026-07-05 17:25:18','2026-07-05 17:25:18',NULL);
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disaster_recovery_plans`
--

DROP TABLE IF EXISTS `disaster_recovery_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disaster_recovery_plans` (
  `drp_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `drp_code` varchar(50) NOT NULL,
  `drp_name` varchar(150) NOT NULL,
  `drp_type` enum('DATA_RECOVERY','SYSTEM_RECOVERY','FACILITY_RECOVERY','FULL_RECOVERY') NOT NULL,
  `recovery_objectives` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recovery_objectives`)),
  `recovery_procedures` text DEFAULT NULL,
  `contact_persons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contact_persons`)),
  `backup_locations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`backup_locations`)),
  `testing_schedule` varchar(100) DEFAULT NULL,
  `last_tested_at` timestamp NULL DEFAULT NULL,
  `last_test_result` text DEFAULT NULL,
  `status` enum('DRAFT','ACTIVE','REVIEW','OUTDATED') DEFAULT 'DRAFT',
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`drp_id`),
  UNIQUE KEY `drp_code` (`drp_code`),
  KEY `idx_drp_tenant` (`tenant_id`),
  KEY `idx_drp_type` (`drp_type`),
  KEY `idx_drp_status` (`status`),
  CONSTRAINT `fk_drp_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disaster_recovery_plans`
--

LOCK TABLES `disaster_recovery_plans` WRITE;
/*!40000 ALTER TABLE `disaster_recovery_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `disaster_recovery_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emergency_closures`
--

DROP TABLE IF EXISTS `emergency_closures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emergency_closures` (
  `closure_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `closure_type` enum('NATURAL_DISASTER','POWER_OUTAGE','WATER_ISSUE','HEALTH_EMERGENCY','SECURITY_THREAT','EQUIPMENT_FAILURE','OTHER') NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `severity` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'MEDIUM',
  `description` text DEFAULT NULL,
  `impact_assessment` text DEFAULT NULL,
  `recovery_plan` text DEFAULT NULL,
  `notified_employees` tinyint(1) DEFAULT 0,
  `notified_customers` tinyint(1) DEFAULT 0,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`closure_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_branch_id` (`branch_id`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emergency_closures`
--

LOCK TABLES `emergency_closures` WRITE;
/*!40000 ALTER TABLE `emergency_closures` DISABLE KEYS */;
/*!40000 ALTER TABLE `emergency_closures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_shifts`
--

DROP TABLE IF EXISTS `employee_shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_shifts` (
  `employee_shift_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `shift_id` bigint(20) unsigned NOT NULL,
  `effective_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`employee_shift_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_shift_id` (`shift_id`),
  KEY `idx_effective_date` (`effective_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_shifts`
--

LOCK TABLES `employee_shifts` WRITE;
/*!40000 ALTER TABLE `employee_shifts` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `employee_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL DEFAULT 1,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `employee_name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,1,NULL,'Alice Manager','Restaurant Manager','081234567890','alice@restaurant.com',5000000.00,'2026-01-01','ACTIVE','2026-07-05 17:25:18','2026-07-05 17:25:18',NULL),(2,1,NULL,'Bob Chef','Head Chef','081987654321','bob@restaurant.com',4500000.00,'2026-01-15','ACTIVE','2026-07-05 17:25:18','2026-07-05 17:25:18',NULL),(3,1,NULL,'Charlie Waiter','Waiter','081112223334','charlie@restaurant.com',2500000.00,'2026-02-01','ACTIVE','2026-07-05 17:25:18','2026-07-05 17:25:18',NULL),(4,1,NULL,'Diana Cashier','Cashier','081556667778','diana@restaurant.com',2800000.00,'2026-02-15','ACTIVE','2026-07-05 17:25:18','2026-07-05 17:25:18',NULL);
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exchange_rates`
--

DROP TABLE IF EXISTS `exchange_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exchange_rates` (
  `exchange_rate_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `from_currency` varchar(3) NOT NULL,
  `to_currency` varchar(3) NOT NULL,
  `rate` decimal(15,8) NOT NULL,
  `effective_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`exchange_rate_id`),
  UNIQUE KEY `uk_tenant_from_to_date` (`tenant_id`,`from_currency`,`to_currency`,`effective_date`),
  KEY `idx_tenant_currency` (`tenant_id`,`from_currency`,`to_currency`),
  KEY `idx_effective_date` (`effective_date`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exchange_rates`
--

LOCK TABLES `exchange_rates` WRITE;
/*!40000 ALTER TABLE `exchange_rates` DISABLE KEYS */;
INSERT INTO `exchange_rates` VALUES (1,1,'USD','IDR',15000.00000000,'2026-07-07','2026-07-06 18:44:00',2);
/*!40000 ALTER TABLE `exchange_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `favorite_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `restaurant_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`favorite_id`),
  UNIQUE KEY `unique_user_restaurant` (`user_id`,`restaurant_id`,`deleted_at`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feature_modules`
--

DROP TABLE IF EXISTS `feature_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feature_modules` (
  `module_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_code` varchar(50) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `module_category` enum('CORE','CUSTOMER','OPERATIONS','ENTERPRISE','ADVANCED') NOT NULL,
  `description` text DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `is_premium` tinyint(1) DEFAULT 0,
  `pricing_tier` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pricing_tier`)),
  `dependencies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dependencies`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `module_code` (`module_code`),
  KEY `idx_module_category` (`module_category`),
  KEY `idx_is_enabled` (`is_enabled`),
  KEY `idx_is_premium` (`is_premium`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feature_modules`
--

LOCK TABLES `feature_modules` WRITE;
/*!40000 ALTER TABLE `feature_modules` DISABLE KEYS */;
INSERT INTO `feature_modules` VALUES (1,'pos','Point of Sale','CORE','Core POS functionality for order management',1,0,NULL,NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(2,'inventory','Inventory Management','CORE','Stock tracking and inventory management',1,0,NULL,NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(3,'menu','Menu Management','CORE','Menu and product management',1,0,NULL,NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(4,'staff','Staff Management','CORE','Employee and staff management',1,0,NULL,NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(5,'reservations','Reservation Management','CUSTOMER','Table reservation system',1,0,NULL,NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(6,'loyalty','Loyalty Program','CUSTOMER','Customer loyalty and rewards',1,1,'{\"basic\": 30, \"premium\": 50}',NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(7,'delivery','Delivery Management','OPERATIONS','Delivery order management',1,0,NULL,NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(8,'kitchen_display','Kitchen Display System','OPERATIONS','Digital kitchen display',1,0,NULL,NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(9,'table_management','Table Management','OPERATIONS','Table and floor management',1,0,NULL,NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(10,'procurement','Procurement','OPERATIONS','Purchase order and supplier management',1,0,NULL,NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(11,'multi_location','Multi-Location','ENTERPRISE','Multi-location management',1,1,'{\"basic\": 100, \"premium\": 200}',NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(12,'api_access','API Access','ENTERPRISE','API access for integrations',1,1,'{\"basic\": 50, \"premium\": 100}',NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(13,'franchise','Franchise Management','ENTERPRISE','Franchise operations',1,1,'{\"basic\": 150, \"premium\": 300}',NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(14,'international','International','ENTERPRISE','Multi-currency and multi-language',1,1,'{\"basic\": 200, \"premium\": 400}',NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(15,'ai_analytics','AI Analytics','ADVANCED','AI-powered analytics and insights',1,1,'{\"basic\": 100, \"premium\": 200}',NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46'),(16,'automation','Automation','ADVANCED','Workflow automation',1,1,'{\"basic\": 80, \"premium\": 150}',NULL,'2026-07-05 03:52:46','2026-07-05 03:52:46');
/*!40000 ALTER TABLE `feature_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `featured_restaurant_requests`
--

DROP TABLE IF EXISTS `featured_restaurant_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `featured_restaurant_requests` (
  `request_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `request_type` enum('FEATURED_LISTING','SPONSORED_SEARCH','PROMOTIONAL_BANNER') NOT NULL,
  `description` text DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('PENDING','UNDER_REVIEW','APPROVED','REJECTED','ACTIVE','COMPLETED','CANCELLED') DEFAULT 'PENDING',
  `approval_status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`request_id`),
  KEY `idx_featured_tenant` (`tenant_id`),
  KEY `idx_featured_type` (`request_type`),
  KEY `idx_featured_status` (`status`),
  CONSTRAINT `fk_featured_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `featured_restaurant_requests`
--

LOCK TABLES `featured_restaurant_requests` WRITE;
/*!40000 ALTER TABLE `featured_restaurant_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `featured_restaurant_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fixed_assets`
--

DROP TABLE IF EXISTS `fixed_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fixed_assets` (
  `asset_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `asset_code` varchar(50) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `asset_category` varchar(50) DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `purchase_cost` decimal(15,2) NOT NULL,
  `salvage_value` decimal(15,2) DEFAULT 0.00,
  `useful_life` int(11) NOT NULL,
  `depreciation_method` enum('STRAIGHT_LINE','DECLINING_BALANCE','UNITS_OF_PRODUCTION') DEFAULT 'STRAIGHT_LINE',
  `current_value` decimal(15,2) DEFAULT NULL,
  `accumulated_depreciation` decimal(15,2) DEFAULT 0.00,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('ACTIVE','DISPOSED','SOLD','WRITTEN_OFF') DEFAULT 'ACTIVE',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`asset_id`),
  UNIQUE KEY `uk_asset_code` (`tenant_id`,`asset_code`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_asset_code` (`asset_code`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`asset_category`),
  KEY `idx_fa_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_fa_status` (`status`),
  KEY `idx_fa_category` (`asset_category`),
  KEY `idx_fa_code` (`tenant_id`,`asset_code`),
  CONSTRAINT `fixed_assets_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  CONSTRAINT `chk_fa_useful_life_positive` CHECK (`useful_life` > 0),
  CONSTRAINT `chk_fa_purchase_cost_positive` CHECK (`purchase_cost` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fixed_assets`
--

LOCK TABLES `fixed_assets` WRITE;
/*!40000 ALTER TABLE `fixed_assets` DISABLE KEYS */;
INSERT INTO `fixed_assets` VALUES (1,1,2,'AST-20260706-8680','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:26:13','2026-07-06 18:26:13',NULL),(2,1,2,'AST-20260706-6998','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:28:26','2026-07-06 18:28:26',NULL),(3,1,2,'AST-20260706-0973','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:30:17','2026-07-06 18:30:17',NULL),(4,1,2,'AST-20260706-1430','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:32:07','2026-07-06 18:32:07',NULL),(5,1,2,'AST-20260706-9398','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:37:22','2026-07-06 18:37:22',NULL),(6,1,2,'AST-20260706-3121','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:41:51','2026-07-06 18:41:51',NULL),(7,1,2,'AST-20260706-1667','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:42:51','2026-07-06 18:42:51',NULL),(8,1,2,'AST-20260706-8996','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:43:57','2026-07-06 18:43:57',NULL),(9,1,2,'AST-20260706-7860','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:44:37','2026-07-06 18:44:37',NULL),(10,1,2,'AST-20260706-8061','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:46:30','2026-07-06 18:46:30',NULL),(11,1,2,'AST-20260706-9716','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:47:10','2026-07-06 18:47:10',NULL),(12,1,2,'AST-20260706-8935','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:48:40','2026-07-06 18:48:40',NULL),(13,1,2,'AST-20260706-9369','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:49:31','2026-07-06 18:49:31',NULL),(14,1,2,'AST-20260706-4911','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:50:22','2026-07-06 18:50:22',NULL),(15,1,2,'AST-20260706-5526','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:51:14','2026-07-06 18:51:14',NULL),(16,1,2,'AST-20260706-8837','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:52:06','2026-07-06 18:52:06',NULL),(17,1,2,'AST-20260706-6077','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:52:28','2026-07-06 18:52:28',NULL),(18,1,2,'AST-20260706-2637','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:52:56','2026-07-06 18:52:56',NULL),(19,1,2,'AST-20260706-4572','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:53:46','2026-07-06 18:53:46',NULL),(20,1,2,'AST-20260706-2317','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:54:26','2026-07-06 18:54:26',NULL),(21,1,2,'AST-20260706-4588','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:55:02','2026-07-06 18:55:02',NULL),(22,1,2,'AST-20260706-2882','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:56:09','2026-07-06 18:56:09',NULL),(23,1,2,'AST-20260706-6843','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:57:10','2026-07-06 18:57:10',NULL),(24,1,2,'AST-20260706-0562','Test Equipment','EQUIPMENT','2026-07-07',50000.00,0.00,5,'STRAIGHT_LINE',50000.00,0.00,'Main Office','ACTIVE',NULL,'2026-07-06 18:57:24','2026-07-06 18:57:24',NULL);
/*!40000 ALTER TABLE `fixed_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `food_waste`
--

DROP TABLE IF EXISTS `food_waste`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `food_waste` (
  `waste_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `waste_date` date NOT NULL,
  `waste_type` varchar(50) NOT NULL COMMENT 'spoilage, preparation_error, overproduction, expired, customer_return, etc',
  `inventory_item_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `cost_per_unit` decimal(15,2) DEFAULT 0.00,
  `total_cost` decimal(15,2) DEFAULT 0.00,
  `recorded_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`waste_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_date` (`waste_date`),
  KEY `idx_type` (`waste_type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `food_waste`
--

LOCK TABLES `food_waste` WRITE;
/*!40000 ALTER TABLE `food_waste` DISABLE KEYS */;
INSERT INTO `food_waste` VALUES (1,1,NULL,'2026-07-07','spoilage',NULL,1.500,'kg','Test waste record',10.00,15.00,NULL,NULL,'2026-07-06 20:00:39'),(2,1,NULL,'2026-07-07','spoilage',NULL,1.500,'kg','Test waste record',10.00,15.00,NULL,NULL,'2026-07-06 20:01:59'),(3,1,NULL,'2026-07-07','spoilage',NULL,1.500,'kg','Test waste record',10.00,15.00,NULL,NULL,'2026-07-06 20:02:20'),(4,1,NULL,'2026-07-07','spoilage',NULL,1.500,'kg','Test waste record',10.00,15.00,NULL,NULL,'2026-07-06 20:02:36'),(5,1,NULL,'2026-07-07','spoilage',NULL,1.500,'kg','Test waste record',10.00,15.00,NULL,NULL,'2026-07-06 20:02:52');
/*!40000 ALTER TABLE `food_waste` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `general_ledger`
--

DROP TABLE IF EXISTS `general_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `general_ledger` (
  `ledger_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `journal_entry_id` bigint(20) unsigned NOT NULL,
  `journal_line_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `transaction_date` date NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `debit_amount` decimal(15,2) DEFAULT 0.00,
  `credit_amount` decimal(15,2) DEFAULT 0.00,
  `balance` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ledger_id`),
  KEY `journal_entry_id` (`journal_entry_id`),
  KEY `idx_tenant_branch` (`tenant_id`,`branch_id`),
  KEY `idx_account` (`account_id`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_gl_tenant_branch_date` (`tenant_id`,`branch_id`,`transaction_date`),
  KEY `idx_gl_account_date` (`account_id`,`transaction_date`),
  KEY `idx_gl_reference` (`reference_type`,`reference_id`),
  KEY `idx_gl_tenant_account` (`tenant_id`,`account_id`),
  CONSTRAINT `general_ledger_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  CONSTRAINT `general_ledger_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`account_id`),
  CONSTRAINT `general_ledger_ibfk_3` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`journal_entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `general_ledger`
--

LOCK TABLES `general_ledger` WRITE;
/*!40000 ALTER TABLE `general_ledger` DISABLE KEYS */;
/*!40000 ALTER TABLE `general_ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geographic_expansions`
--

DROP TABLE IF EXISTS `geographic_expansions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geographic_expansions` (
  `expansion_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `target_country` varchar(50) NOT NULL,
  `target_city` varchar(100) DEFAULT NULL,
  `target_region` varchar(100) DEFAULT NULL,
  `expansion_stage` enum('RESEARCH','PLANNING','PREPARATION','LAUNCH','GROWTH','MATURE') DEFAULT 'RESEARCH',
  `target_customers` int(11) DEFAULT NULL,
  `actual_customers` int(11) DEFAULT 0,
  `target_revenue` decimal(15,2) DEFAULT NULL,
  `actual_revenue` decimal(15,2) DEFAULT 0.00,
  `roi_percentage` decimal(5,2) DEFAULT NULL,
  `launch_date` date DEFAULT NULL,
  `lessons_learned` text DEFAULT NULL,
  `status` enum('PLANNED','ACTIVE','PAUSED','COMPLETED','CANCELLED') DEFAULT 'PLANNED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`expansion_id`),
  KEY `idx_expansion_country` (`target_country`),
  KEY `idx_expansion_stage` (`expansion_stage`),
  KEY `idx_expansion_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geographic_expansions`
--

LOCK TABLES `geographic_expansions` WRITE;
/*!40000 ALTER TABLE `geographic_expansions` DISABLE KEYS */;
/*!40000 ALTER TABLE `geographic_expansions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geographic_pricing_adjustments`
--

DROP TABLE IF EXISTS `geographic_pricing_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geographic_pricing_adjustments` (
  `adjustment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country` varchar(50) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `adjustment_percentage` decimal(5,2) NOT NULL,
  `adjustment_type` enum('INCREASE','DECREASE') NOT NULL,
  `effective_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`adjustment_id`),
  KEY `idx_geo_country` (`country`),
  KEY `idx_geo_region` (`region`),
  KEY `idx_geo_active` (`is_active`),
  KEY `idx_geo_dates` (`effective_date`,`expiry_date`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geographic_pricing_adjustments`
--

LOCK TABLES `geographic_pricing_adjustments` WRITE;
/*!40000 ALTER TABLE `geographic_pricing_adjustments` DISABLE KEYS */;
INSERT INTO `geographic_pricing_adjustments` VALUES (1,'Indonesia',NULL,0.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(2,'Singapore',NULL,20.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(3,'Malaysia',NULL,20.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(4,'Thailand',NULL,20.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(5,'Vietnam',NULL,20.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(6,'Philippines',NULL,20.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(7,'Australia',NULL,30.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(8,'Japan',NULL,30.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(9,'South Korea',NULL,30.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(10,'United Kingdom',NULL,50.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(11,'Germany',NULL,50.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(12,'France',NULL,50.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(13,'United States',NULL,60.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(14,'Canada',NULL,60.00,'INCREASE','2026-07-05',NULL,1,NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19');
/*!40000 ALTER TABLE `geographic_pricing_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `growth_metrics`
--

DROP TABLE IF EXISTS `growth_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `growth_metrics` (
  `metric_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `metric_date` date NOT NULL,
  `metric_type` enum('ACQUISITION','ACTIVATION','ENGAGEMENT','RETENTION','REVENUE','REFERRAL') NOT NULL,
  `metric_value` decimal(15,2) DEFAULT NULL,
  `metric_metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metric_metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`metric_id`),
  KEY `idx_metric_date` (`metric_date`),
  KEY `idx_metric_type` (`metric_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `growth_metrics`
--

LOCK TABLES `growth_metrics` WRITE;
/*!40000 ALTER TABLE `growth_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `growth_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `holidays` (
  `holiday_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `holiday_name` varchar(100) NOT NULL,
  `holiday_date` date NOT NULL,
  `holiday_type` enum('PUBLIC','COMPANY','RELIGIOUS','SPECIAL') DEFAULT 'PUBLIC',
  `is_recurring` tinyint(1) DEFAULT 0,
  `recurring_month` int(11) DEFAULT NULL,
  `recurring_day` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`holiday_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_branch_id` (`branch_id`),
  KEY `idx_holiday_date` (`holiday_date`),
  KEY `idx_holiday_type` (`holiday_type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (1,1,NULL,'Independence Day','2026-08-17','PUBLIC',1,NULL,NULL,NULL,1,'2026-07-06 19:27:12','2026-07-06 19:27:12'),(2,1,NULL,'New Year','2026-01-01','PUBLIC',1,NULL,NULL,NULL,1,'2026-07-06 19:27:12','2026-07-06 19:27:12'),(3,3,NULL,'Christmas','2026-12-25','PUBLIC',1,NULL,NULL,NULL,1,'2026-07-06 19:27:12','2026-07-06 19:27:12'),(4,3,NULL,'Eid al-Fitr','2026-04-10','RELIGIOUS',0,NULL,NULL,NULL,1,'2026-07-06 19:27:12','2026-07-06 19:27:12');
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory` (
  `inventory_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(20) DEFAULT 'unit',
  `minimum_stock` decimal(10,2) DEFAULT 0.00,
  `maximum_stock` decimal(10,2) DEFAULT 0.00,
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `sourcing_type` enum('self_produced','outsourced','supplier_sourced','mixed') DEFAULT 'supplier_sourced',
  `allergen_info` text DEFAULT NULL,
  `storage_location` varchar(50) DEFAULT NULL,
  `storage_temperature` varchar(20) DEFAULT NULL,
  `quality_grade` enum('A','B','C','STANDARD') DEFAULT 'STANDARD',
  `is_perishable` tinyint(1) DEFAULT 1,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`inventory_id`),
  UNIQUE KEY `idx_inventory_branch_product` (`branch_id`,`product_id`),
  KEY `idx_inventory_tenant_id` (`tenant_id`),
  KEY `idx_inventory_branch_id` (`branch_id`),
  KEY `idx_inventory_product_id` (`product_id`),
  KEY `idx_batch_number` (`batch_number`),
  KEY `idx_expiry_date` (`expiry_date`),
  KEY `idx_supplier` (`supplier_id`),
  KEY `idx_sourcing_type` (`sourcing_type`),
  KEY `idx_perishable` (`is_perishable`),
  CONSTRAINT `fk_inventory_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES (1,1,2,1,100.00,'kg',10.00,200.00,NULL,NULL,NULL,NULL,'supplier_sourced',NULL,NULL,NULL,'STANDARD',1,'ACTIVE','2026-07-02 12:14:26','2026-07-02 12:14:26',NULL),(2,1,2,2,50.00,'liter',5.00,100.00,NULL,NULL,NULL,NULL,'supplier_sourced',NULL,NULL,NULL,'STANDARD',1,'ACTIVE','2026-07-02 12:14:26','2026-07-02 12:14:26',NULL),(3,1,2,3,30.00,'kg',5.00,50.00,NULL,NULL,NULL,NULL,'supplier_sourced',NULL,NULL,NULL,'STANDARD',1,'ACTIVE','2026-07-02 12:14:26','2026-07-02 12:14:26',NULL),(4,1,2,4,20.00,'kg',5.00,40.00,NULL,NULL,NULL,NULL,'supplier_sourced',NULL,NULL,NULL,'STANDARD',1,'ACTIVE','2026-07-02 12:14:26','2026-07-02 12:14:26',NULL),(5,1,2,5,15.00,'kg',3.00,30.00,NULL,NULL,NULL,NULL,'supplier_sourced',NULL,NULL,NULL,'STANDARD',1,'ACTIVE','2026-07-02 12:14:26','2026-07-02 12:14:26',NULL);
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_items` (
  `inventory_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `item_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT 'unit',
  `cost_per_unit` decimal(15,2) DEFAULT 0.00,
  `current_stock` decimal(10,3) DEFAULT 0.000,
  `min_stock_level` decimal(10,3) DEFAULT 0.000,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`inventory_item_id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_entries` (
  `journal_entry_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `journal_number` varchar(50) NOT NULL,
  `journal_date` date NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('DRAFT','POSTED','CANCELLED') DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`journal_entry_id`),
  UNIQUE KEY `idx_journal_entries_tenant_number` (`tenant_id`,`journal_number`),
  KEY `idx_journal_entries_tenant_id` (`tenant_id`),
  KEY `idx_journal_entries_branch_id` (`branch_id`),
  KEY `idx_journal_entries_date` (`journal_date`),
  KEY `idx_journal_entries_status` (`status`),
  KEY `idx_je_tenant_date` (`tenant_id`,`journal_date`),
  KEY `idx_je_tenant_branch_date` (`tenant_id`,`branch_id`,`journal_date`),
  KEY `idx_je_reference` (`reference_type`,`reference_id`),
  KEY `idx_je_status` (`status`),
  CONSTRAINT `journal_entries_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `journal_entries_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entries`
--

LOCK TABLES `journal_entries` WRITE;
/*!40000 ALTER TABLE `journal_entries` DISABLE KEYS */;
INSERT INTO `journal_entries` VALUES (1,1,2,'JE-20260706-3383','2026-07-07',NULL,NULL,'Test','POSTED','2026-07-06 18:34:07','2026-07-06 18:34:07',NULL),(2,1,2,'JE-20260706-0735','2026-07-07',NULL,NULL,'Test','POSTED','2026-07-06 18:34:46','2026-07-06 18:34:46',NULL),(3,1,2,'JE-20260706-7549','2026-07-07',NULL,NULL,'Test','POSTED','2026-07-06 18:35:13','2026-07-06 18:35:13',NULL),(4,1,2,'JE-20260706-5598','2026-07-07',NULL,NULL,'Test','POSTED','2026-07-06 18:35:45','2026-07-06 18:35:45',NULL),(5,1,2,'JE-20260706-8057','2026-07-07',NULL,NULL,'Test','POSTED','2026-07-06 18:36:02','2026-07-06 18:36:02',NULL),(6,1,2,'JE-20260706-5991','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:37:15','2026-07-06 18:37:15',NULL),(7,1,2,'JE-20260706-7595','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:41:44','2026-07-06 18:41:44',NULL),(8,1,2,'JE-20260706-5077','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:42:44','2026-07-06 18:42:44',NULL),(9,1,2,'JE-20260706-0250','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:43:50','2026-07-06 18:43:50',NULL),(10,1,2,'JE-20260706-2177','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:44:30','2026-07-06 18:44:30',NULL),(11,1,2,'JE-20260706-1751','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:46:23','2026-07-06 18:46:23',NULL),(12,1,2,'JE-20260706-4217','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:47:03','2026-07-06 18:47:03',NULL),(13,1,2,'JE-20260706-6180','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:48:33','2026-07-06 18:48:33',NULL),(14,1,2,'JE-20260706-0527','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:49:25','2026-07-06 18:49:25',NULL),(15,1,2,'JE-20260706-5988','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:50:18','2026-07-06 18:50:18',NULL),(16,1,2,'JE-20260706-9787','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:51:12','2026-07-06 18:51:12',NULL),(17,1,2,'JE-20260706-2324','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:52:02','2026-07-06 18:52:02',NULL),(18,1,2,'JE-20260706-9663','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:52:26','2026-07-06 18:52:26',NULL),(19,1,2,'JE-20260706-2529','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:52:54','2026-07-06 18:52:54',NULL),(20,1,2,'JE-20260706-5891','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:53:44','2026-07-06 18:53:44',NULL),(21,1,2,'JE-20260706-8730','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:54:24','2026-07-06 18:54:24',NULL),(22,1,2,'JE-20260706-7459','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:55:00','2026-07-06 18:55:00',NULL),(23,1,2,'JE-20260706-9349','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:56:08','2026-07-06 18:56:08',NULL),(24,1,2,'JE-20260706-2762','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:57:09','2026-07-06 18:57:09',NULL),(25,1,2,'JE-20260706-4002','2026-07-07',NULL,NULL,'Test journal entry','POSTED','2026-07-06 18:57:23','2026-07-06 18:57:23',NULL);
/*!40000 ALTER TABLE `journal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_lines`
--

DROP TABLE IF EXISTS `journal_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_lines` (
  `journal_line_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`journal_line_id`),
  KEY `idx_journal_lines_journal_entry_id` (`journal_entry_id`),
  KEY `idx_journal_lines_account_id` (`account_id`),
  KEY `idx_jl_journal_entry` (`journal_entry_id`),
  KEY `idx_jl_account` (`account_id`),
  CONSTRAINT `journal_lines_ibfk_1` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`journal_entry_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `journal_lines_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_debit_credit` CHECK (`debit` > 0 and `credit` = 0 or `credit` > 0 and `debit` = 0 or `debit` = 0 and `credit` = 0)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_lines`
--

LOCK TABLES `journal_lines` WRITE;
/*!40000 ALTER TABLE `journal_lines` DISABLE KEYS */;
INSERT INTO `journal_lines` VALUES (1,1,1,1000.00,0.00,NULL,'2026-07-06 18:34:07'),(2,1,2,0.00,1000.00,NULL,'2026-07-06 18:34:07'),(3,2,1,1000.00,0.00,NULL,'2026-07-06 18:34:46'),(4,2,2,0.00,1000.00,NULL,'2026-07-06 18:34:46'),(5,3,1,1000.00,0.00,NULL,'2026-07-06 18:35:13'),(6,3,2,0.00,1000.00,NULL,'2026-07-06 18:35:13'),(7,4,1,1000.00,0.00,NULL,'2026-07-06 18:35:45'),(8,4,2,0.00,1000.00,NULL,'2026-07-06 18:35:45'),(9,5,1,1000.00,0.00,NULL,'2026-07-06 18:36:02'),(10,5,2,0.00,1000.00,NULL,'2026-07-06 18:36:02'),(11,6,1,1000.00,0.00,'Debit entry','2026-07-06 18:37:15'),(12,6,2,0.00,1000.00,'Credit entry','2026-07-06 18:37:15'),(13,7,1,1000.00,0.00,'Debit entry','2026-07-06 18:41:44'),(14,7,2,0.00,1000.00,'Credit entry','2026-07-06 18:41:44'),(15,8,1,1000.00,0.00,'Debit entry','2026-07-06 18:42:44'),(16,8,2,0.00,1000.00,'Credit entry','2026-07-06 18:42:44'),(17,9,1,1000.00,0.00,'Debit entry','2026-07-06 18:43:50'),(18,9,2,0.00,1000.00,'Credit entry','2026-07-06 18:43:50'),(19,10,1,1000.00,0.00,'Debit entry','2026-07-06 18:44:30'),(20,10,2,0.00,1000.00,'Credit entry','2026-07-06 18:44:30'),(21,11,1,1000.00,0.00,'Debit entry','2026-07-06 18:46:23'),(22,11,2,0.00,1000.00,'Credit entry','2026-07-06 18:46:23'),(23,12,1,1000.00,0.00,'Debit entry','2026-07-06 18:47:03'),(24,12,2,0.00,1000.00,'Credit entry','2026-07-06 18:47:03'),(25,13,1,1000.00,0.00,'Debit entry','2026-07-06 18:48:33'),(26,13,2,0.00,1000.00,'Credit entry','2026-07-06 18:48:33'),(27,14,1,1000.00,0.00,'Debit entry','2026-07-06 18:49:25'),(28,14,2,0.00,1000.00,'Credit entry','2026-07-06 18:49:25'),(29,15,1,1000.00,0.00,'Debit entry','2026-07-06 18:50:18'),(30,15,2,0.00,1000.00,'Credit entry','2026-07-06 18:50:18'),(31,16,1,1000.00,0.00,'Debit entry','2026-07-06 18:51:12'),(32,16,2,0.00,1000.00,'Credit entry','2026-07-06 18:51:12'),(33,17,1,1000.00,0.00,'Debit entry','2026-07-06 18:52:02'),(34,17,2,0.00,1000.00,'Credit entry','2026-07-06 18:52:02'),(35,18,1,1000.00,0.00,'Debit entry','2026-07-06 18:52:26'),(36,18,2,0.00,1000.00,'Credit entry','2026-07-06 18:52:26'),(37,19,1,1000.00,0.00,'Debit entry','2026-07-06 18:52:54'),(38,19,2,0.00,1000.00,'Credit entry','2026-07-06 18:52:54'),(39,20,1,1000.00,0.00,'Debit entry','2026-07-06 18:53:44'),(40,20,2,0.00,1000.00,'Credit entry','2026-07-06 18:53:44'),(41,21,1,1000.00,0.00,'Debit entry','2026-07-06 18:54:24'),(42,21,2,0.00,1000.00,'Credit entry','2026-07-06 18:54:24'),(43,22,1,1000.00,0.00,'Debit entry','2026-07-06 18:55:00'),(44,22,2,0.00,1000.00,'Credit entry','2026-07-06 18:55:00'),(45,23,1,1000.00,0.00,'Debit entry','2026-07-06 18:56:08'),(46,23,2,0.00,1000.00,'Credit entry','2026-07-06 18:56:08'),(47,24,1,1000.00,0.00,'Debit entry','2026-07-06 18:57:09'),(48,24,2,0.00,1000.00,'Credit entry','2026-07-06 18:57:09'),(49,25,1,1000.00,0.00,'Debit entry','2026-07-06 18:57:23'),(50,25,2,0.00,1000.00,'Credit entry','2026-07-06 18:57:23');
/*!40000 ALTER TABLE `journal_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kitchen_order_items`
--

DROP TABLE IF EXISTS `kitchen_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kitchen_order_items` (
  `kitchen_order_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kitchen_order_id` bigint(20) unsigned NOT NULL,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('PENDING','PREPARING','READY','SERVED','CANCELLED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`kitchen_order_item_id`),
  KEY `idx_kitchen_order_items_kitchen_order_id` (`kitchen_order_id`),
  KEY `idx_kitchen_order_items_order_item_id` (`order_item_id`),
  KEY `idx_kitchen_order_items_product_id` (`product_id`),
  KEY `idx_kitchen_order_items_status` (`status`),
  CONSTRAINT `kitchen_order_items_ibfk_1` FOREIGN KEY (`kitchen_order_id`) REFERENCES `kitchen_orders` (`kitchen_order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kitchen_order_items_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kitchen_order_items_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kitchen_order_items`
--

LOCK TABLES `kitchen_order_items` WRITE;
/*!40000 ALTER TABLE `kitchen_order_items` DISABLE KEYS */;
INSERT INTO `kitchen_order_items` VALUES (1,3,5,2,1,NULL,'PENDING','2026-07-02 12:28:29','2026-07-02 12:28:29'),(2,4,7,1,2,NULL,'PENDING','2026-07-02 12:33:35','2026-07-02 12:33:35');
/*!40000 ALTER TABLE `kitchen_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kitchen_orders`
--

DROP TABLE IF EXISTS `kitchen_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kitchen_orders` (
  `kitchen_order_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `kitchen_order_number` varchar(50) NOT NULL,
  `status` enum('PENDING','IN_PROGRESS','READY','SERVED','CANCELLED') DEFAULT 'PENDING',
  `priority` enum('LOW','NORMAL','HIGH','URGENT') DEFAULT 'NORMAL',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`kitchen_order_id`),
  UNIQUE KEY `idx_kitchen_orders_tenant_number` (`tenant_id`,`kitchen_order_number`),
  KEY `idx_kitchen_orders_tenant_id` (`tenant_id`),
  KEY `idx_kitchen_orders_branch_id` (`branch_id`),
  KEY `idx_kitchen_orders_order_id` (`order_id`),
  KEY `idx_kitchen_orders_status` (`status`),
  CONSTRAINT `kitchen_orders_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kitchen_orders_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kitchen_orders_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kitchen_orders`
--

LOCK TABLES `kitchen_orders` WRITE;
/*!40000 ALTER TABLE `kitchen_orders` DISABLE KEYS */;
INSERT INTO `kitchen_orders` VALUES (1,1,2,4,'KIT-20260702-0001','PENDING','NORMAL',NULL,NULL,'2026-07-02 12:27:39','2026-07-02 12:27:39'),(2,1,2,5,'KIT-20260702-0002','PENDING','NORMAL',NULL,NULL,'2026-07-02 12:28:00','2026-07-02 12:28:00'),(3,1,2,6,'KIT-20260702-0003','PENDING','NORMAL',NULL,NULL,'2026-07-02 12:28:29','2026-07-02 12:28:29'),(4,1,2,8,'KIT-20260702-0004','PENDING','NORMAL',NULL,NULL,'2026-07-02 12:33:35','2026-07-02 12:33:35');
/*!40000 ALTER TABLE `kitchen_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_requests` (
  `leave_request_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `leave_type` enum('ANNUAL','SICK','MATERNITY','PATERNITY','UNPAID','COMPASSIONATE','OTHER') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` decimal(5,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED','CANCELLED') DEFAULT 'PENDING',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`leave_request_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_branch_id` (`branch_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_status` (`status`),
  KEY `idx_date_range` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_requests`
--

LOCK TABLES `leave_requests` WRITE;
/*!40000 ALTER TABLE `leave_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_points`
--

DROP TABLE IF EXISTS `loyalty_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_points` (
  `loyalty_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `points_balance` int(11) DEFAULT 0,
  `points_earned` int(11) DEFAULT 0,
  `points_redeemed` int(11) DEFAULT 0,
  `tier` varchar(50) DEFAULT 'Bronze',
  `next_tier` varchar(50) DEFAULT 'Silver',
  `points_to_next_tier` int(11) DEFAULT 1000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`loyalty_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_points`
--

LOCK TABLES `loyalty_points` WRITE;
/*!40000 ALTER TABLE `loyalty_points` DISABLE KEYS */;
INSERT INTO `loyalty_points` VALUES (1,10,1,1000,5000,4000,'Gold','Platinum',2000,'2026-07-05 21:37:09','2026-07-05 21:37:09',NULL);
/*!40000 ALTER TABLE `loyalty_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketplace_fees`
--

DROP TABLE IF EXISTS `marketplace_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_fees` (
  `marketplace_fee_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_type` enum('SUPPLIER_PURCHASE','STAFF_BOOKING','SERVICE_FEE') NOT NULL,
  `fee_percentage` decimal(5,4) DEFAULT NULL,
  `fixed_fee` decimal(10,2) DEFAULT NULL,
  `total_fee` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `fee_status` enum('PENDING','COLLECTED','WAIVED','REFUNDED') DEFAULT 'PENDING',
  `collected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`marketplace_fee_id`),
  KEY `idx_mf_tenant` (`tenant_id`),
  KEY `idx_mf_transaction` (`transaction_id`),
  KEY `idx_mf_type` (`transaction_type`),
  KEY `idx_mf_status` (`fee_status`),
  CONSTRAINT `idx_mf_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketplace_fees`
--

LOCK TABLES `marketplace_fees` WRITE;
/*!40000 ALTER TABLE `marketplace_fees` DISABLE KEYS */;
/*!40000 ALTER TABLE `marketplace_fees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item_modifiers`
--

DROP TABLE IF EXISTS `order_item_modifiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_item_modifiers` (
  `order_item_modifier_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `modifier_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_item_modifier_id`),
  KEY `idx_order_item_modifiers_order_item_id` (`order_item_id`),
  KEY `idx_order_item_modifiers_modifier_id` (`modifier_id`),
  CONSTRAINT `order_item_modifiers_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item_modifiers`
--

LOCK TABLES `order_item_modifiers` WRITE;
/*!40000 ALTER TABLE `order_item_modifiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_item_modifiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `order_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variant_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('PENDING','PREPARING','READY','SERVED','CANCELLED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`order_item_id`),
  KEY `idx_order_items_order_id` (`order_id`),
  KEY `idx_order_items_product_id` (`product_id`),
  KEY `idx_order_items_status` (`status`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (2,3,1,NULL,2,25000.00,50000.00,NULL,'PENDING','2026-07-02 12:12:43','2026-07-02 12:12:43'),(3,4,2,NULL,1,22000.00,22000.00,NULL,'PENDING','2026-07-02 12:27:39','2026-07-02 12:27:39'),(4,5,2,NULL,1,22000.00,22000.00,NULL,'PENDING','2026-07-02 12:28:00','2026-07-02 12:28:00'),(5,6,2,NULL,1,22000.00,22000.00,NULL,'PENDING','2026-07-02 12:28:29','2026-07-02 12:28:29'),(6,7,1,NULL,2,25000.00,50000.00,NULL,'PENDING','2026-07-02 12:33:10','2026-07-02 12:33:10'),(7,8,1,NULL,2,25000.00,50000.00,NULL,'PENDING','2026-07-02 12:33:35','2026-07-02 12:33:35'),(8,10,1,NULL,2,25000.00,50000.00,NULL,'PENDING','2026-07-05 21:39:18','2026-07-05 21:39:18');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `table_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('PENDING','CONFIRMED','PREPARING','READY','SERVED','COMPLETED','CANCELLED') DEFAULT 'PENDING',
  `order_type` enum('DINE_IN','TAKE_AWAY','DELIVERY','PRE_ORDER') DEFAULT 'DINE_IN',
  `is_open_order` tinyint(1) DEFAULT 1,
  `is_priority` tinyint(1) DEFAULT 0,
  `is_held` tinyint(1) DEFAULT 0,
  `hold_reason` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `delivery_time` datetime DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `service_charge` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('UNPAID','PARTIAL','PAID','REFUNDED') DEFAULT 'UNPAID',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `idx_orders_tenant_number` (`tenant_id`,`order_number`),
  KEY `idx_orders_tenant_id` (`tenant_id`),
  KEY `idx_orders_branch_id` (`branch_id`),
  KEY `idx_orders_user_id` (`user_id`),
  KEY `idx_orders_table_id` (`table_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_payment_status` (`payment_status`),
  KEY `idx_orders_created_at` (`created_at`),
  KEY `idx_orders_order_type` (`order_type`),
  KEY `idx_orders_is_open_order` (`is_open_order`),
  KEY `idx_orders_is_priority` (`is_priority`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (2,1,2,'ORD-20260702-0001',3,1,NULL,'PENDING','DINE_IN',1,0,0,NULL,NULL,NULL,NULL,0.00,NULL,50000.00,0.00,0.00,0.00,50000.00,0.00,NULL,'UNPAID',NULL,'2026-07-02 12:12:26','2026-07-02 12:12:26',NULL,NULL),(3,1,2,'ORD-20260702-0002',3,1,NULL,'PENDING','DINE_IN',1,0,0,NULL,NULL,NULL,NULL,0.00,NULL,50000.00,0.00,0.00,0.00,50000.00,0.00,NULL,'UNPAID',NULL,'2026-07-02 12:12:43','2026-07-02 12:12:43',NULL,NULL),(4,1,2,'ORD-20260702-0003',3,2,NULL,'PENDING','DINE_IN',1,0,0,NULL,NULL,NULL,NULL,0.00,NULL,22000.00,0.00,0.00,0.00,22000.00,0.00,NULL,'UNPAID',NULL,'2026-07-02 12:27:39','2026-07-02 12:27:39',NULL,NULL),(5,1,2,'ORD-20260702-0004',3,2,NULL,'PENDING','DINE_IN',1,0,0,NULL,NULL,NULL,NULL,0.00,NULL,22000.00,0.00,0.00,0.00,22000.00,0.00,NULL,'UNPAID',NULL,'2026-07-02 12:28:00','2026-07-02 12:28:00',NULL,NULL),(6,1,2,'ORD-20260702-0005',3,2,NULL,'PENDING','DINE_IN',1,0,0,NULL,NULL,NULL,NULL,0.00,NULL,22000.00,0.00,0.00,0.00,22000.00,0.00,NULL,'UNPAID',NULL,'2026-07-02 12:28:29','2026-07-02 12:28:29',NULL,NULL),(7,1,2,'ORD-20260702-0006',3,3,NULL,'PENDING','DINE_IN',1,0,0,NULL,NULL,NULL,NULL,0.00,NULL,50000.00,0.00,0.00,0.00,50000.00,0.00,NULL,'UNPAID',NULL,'2026-07-02 12:33:10','2026-07-02 12:33:10',NULL,NULL),(8,1,2,'ORD-20260702-0007',3,3,NULL,'PENDING','DINE_IN',1,0,0,NULL,NULL,NULL,NULL,0.00,NULL,50000.00,0.00,0.00,0.00,50000.00,0.00,NULL,'UNPAID',NULL,'2026-07-02 12:33:35','2026-07-02 12:33:35',NULL,NULL),(10,1,2,'ORD-20260705-9146',10,NULL,NULL,'PENDING','DINE_IN',1,0,0,NULL,NULL,NULL,NULL,0.00,NULL,50000.00,0.00,0.00,0.00,50000.00,0.00,NULL,'UNPAID',NULL,'2026-07-05 21:39:18','2026-07-05 21:39:18',NULL,NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `payment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `split_bill_id` bigint(20) unsigned DEFAULT NULL,
  `payment_method` enum('CASH','QRIS','DEBIT','CREDIT','E_WALLET','TRANSFER','VOUCHER') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('PENDING','COMPLETED','FAILED','REFUNDED') DEFAULT 'COMPLETED',
  `reference_number` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `idx_payments_order_id` (`order_id`),
  KEY `idx_payments_split_bill_id` (`split_bill_id`),
  KEY `idx_payments_payment_method` (`payment_method`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`split_bill_id`) REFERENCES `split_bills` (`split_bill_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,3,NULL,'CASH',50000.00,'COMPLETED','CASH-001','2026-07-02 12:21:19',NULL),(2,2,NULL,'CASH',50000.00,'COMPLETED','CASH-003','2026-07-02 12:23:27',NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `permission_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permission_code` varchar(100) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `permission_code` (`permission_code`),
  KEY `idx_permissions_module` (`module`),
  KEY `idx_permissions_action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'MENU_MANAGE','MENU_MANAGE',NULL,NULL,'MENU MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(2,'TABLE_MANAGE','TABLE_MANAGE',NULL,NULL,'TABLE MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(3,'RESERVATION_MANAGE','RESERVATION_MANAGE',NULL,NULL,'RESERVATION MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(4,'INVENTORY_MANAGE','INVENTORY_MANAGE',NULL,NULL,'INVENTORY MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(5,'KITCHEN_VIEW','View Kitchen Orders',NULL,NULL,'View kitchen display system','2026-07-02 07:41:03','2026-07-05 19:50:26'),(6,'USER_MANAGE','USER_MANAGE',NULL,NULL,'USER MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(7,'SETTINGS_MANAGE','Manage Settings',NULL,NULL,'Manage system settings','2026-07-02 07:41:03','2026-07-05 19:50:26'),(8,'REPORT_VIEW','REPORT_VIEW',NULL,NULL,'REPORT VIEW','2026-07-02 07:41:03','2026-07-02 07:41:03'),(9,'SALES_MANAGE','SALES_MANAGE',NULL,NULL,'SALES MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(10,'ORDER_CREATE','Create Orders',NULL,NULL,'Create new orders','2026-07-02 12:05:55','2026-07-05 19:50:26'),(11,'ORDER_UPDATE','Order Update',NULL,NULL,'Update existing orders','2026-07-02 12:05:55','2026-07-02 12:05:55'),(12,'ORDER_DELETE','Delete Orders',NULL,NULL,'Delete orders','2026-07-02 12:05:55','2026-07-05 19:50:26'),(13,'ORDER_VIEW','View Orders',NULL,NULL,'View orders','2026-07-02 12:05:55','2026-07-05 19:50:26'),(14,'PAYMENT_PROCESS','Payment Process',NULL,NULL,'Process payments','2026-07-02 12:05:55','2026-07-02 12:05:55'),(15,'KITCHEN_UPDATE','Kitchen Update',NULL,NULL,'Update kitchen order status','2026-07-02 12:05:55','2026-07-02 12:05:55'),(16,'TABLE_ASSIGN','Table Assign',NULL,NULL,'Assign tables to orders','2026-07-02 12:05:55','2026-07-02 12:05:55'),(17,'RESERVATION_CREATE','Create Reservations',NULL,NULL,'Create new reservations','2026-07-02 12:05:55','2026-07-05 19:50:26'),(18,'RESERVATION_UPDATE','Reservation Update',NULL,NULL,'Update reservations','2026-07-02 12:05:55','2026-07-02 12:05:55'),(19,'STOCK_ADJUST','Stock Adjust',NULL,NULL,'Adjust inventory stock','2026-07-02 12:05:55','2026-07-02 12:05:55'),(20,'PRODUCT_VIEW','Product View',NULL,NULL,'View menu products','2026-07-02 12:05:55','2026-07-02 12:05:55'),(21,'CATEGORY_VIEW','Category View',NULL,NULL,'View menu categories','2026-07-02 12:05:55','2026-07-02 12:05:55'),(22,'ACCOUNTING_MANAGE','Accounting Management',NULL,NULL,'Manage accounting and financial transactions','2026-07-05 17:20:27','2026-07-05 17:20:27'),(23,'CRM_MANAGE','CRM Management',NULL,NULL,'Manage customer relationships and loyalty programs','2026-07-05 17:20:27','2026-07-05 17:20:27'),(24,'HR_MANAGE','HR Management',NULL,NULL,'Manage human resources and employees','2026-07-05 17:20:27','2026-07-05 17:20:27'),(25,'DELIVERY_MANAGE','Delivery Management',NULL,NULL,'Manage delivery orders and drivers','2026-07-05 17:20:27','2026-07-05 17:20:27'),(26,'AI_VIEW','AI Features',NULL,NULL,'View AI-powered insights and recommendations','2026-07-05 17:20:27','2026-07-05 17:20:27'),(27,'INTEGRATION_MANAGE','Integration Management',NULL,NULL,'Manage third-party integrations','2026-07-05 17:20:27','2026-07-05 17:20:27'),(28,'QUALITY_MANAGE','Manage Quality',NULL,NULL,'Manage quality control','2026-07-05 17:20:27','2026-07-05 19:50:26'),(29,'SUPPLYCHAIN_MANAGE','Supply Chain Management',NULL,NULL,'Manage suppliers and procurement','2026-07-05 17:20:27','2026-07-05 17:20:27'),(30,'SUSTAINABILITY_VIEW','Sustainability',NULL,NULL,'View sustainability metrics and reports','2026-07-05 17:20:27','2026-07-05 17:20:27'),(31,'LOCATION_MANAGE','Location Management',NULL,NULL,'Manage multiple restaurant locations','2026-07-05 17:20:27','2026-07-05 17:20:27'),(32,'MAINTENANCE_MANAGE','Maintenance Management',NULL,NULL,'Manage equipment maintenance schedules','2026-07-05 17:20:27','2026-07-05 17:20:27'),(33,'ENTERPRISE_VIEW','Enterprise Features',NULL,NULL,'View enterprise-wide analytics','2026-07-05 17:20:27','2026-07-05 17:20:27'),(34,'TENANT_MANAGE','Tenant Management',NULL,NULL,'Manage multi-tenant configurations','2026-07-05 17:20:27','2026-07-05 17:20:27'),(35,'WHATSAPP_MANAGE','WhatsApp Integration',NULL,NULL,'Manage WhatsApp ordering and notifications','2026-07-05 17:20:27','2026-07-05 17:20:27'),(36,'MENU_CREATE','Create Menu Items',NULL,NULL,'Create new menu categories and products','2026-07-05 19:50:26','2026-07-05 19:50:26'),(37,'MENU_EDIT','Edit Menu Items',NULL,NULL,'Edit existing menu categories and products','2026-07-05 19:50:26','2026-07-05 19:50:26'),(38,'MENU_DELETE','Delete Menu Items',NULL,NULL,'Delete menu categories and products','2026-07-05 19:50:26','2026-07-05 19:50:26'),(39,'MENU_VIEW','View Menu Items',NULL,NULL,'View menu categories and products','2026-07-05 19:50:26','2026-07-05 19:50:26'),(40,'MENU_EDIT_PRICE','Edit Menu Prices',NULL,NULL,'Edit menu item prices','2026-07-05 19:50:26','2026-07-05 19:50:26'),(41,'MENU_MANAGE_MODIFIERS','Manage Menu Modifiers',NULL,NULL,'Manage product modifiers and options','2026-07-05 19:50:26','2026-07-05 19:50:26'),(42,'MENU_VIEW_RECIPE','View Recipes',NULL,NULL,'View recipe details','2026-07-05 19:50:26','2026-07-05 19:50:26'),(43,'ORDER_EDIT','Edit Orders',NULL,NULL,'Edit existing orders','2026-07-05 19:50:26','2026-07-05 19:50:26'),(44,'ORDER_PAYMENT','Process Payments',NULL,NULL,'Process order payments','2026-07-05 19:50:26','2026-07-05 19:50:26'),(45,'ORDER_DISCOUNT','Apply Discounts',NULL,NULL,'Apply discounts to orders','2026-07-05 19:50:26','2026-07-05 19:50:26'),(46,'ORDER_SPLIT_BILL','Split Bills',NULL,NULL,'Split order bills','2026-07-05 19:50:26','2026-07-05 19:50:26'),(47,'ORDER_MERGE','Merge Orders',NULL,NULL,'Merge multiple orders','2026-07-05 19:50:26','2026-07-05 19:50:26'),(48,'ORDER_VOID','Void Orders',NULL,NULL,'Void orders','2026-07-05 19:50:26','2026-07-05 19:50:26'),(49,'ORDER_REFUND','Refund Orders',NULL,NULL,'Process refunds','2026-07-05 19:50:26','2026-07-05 19:50:26'),(50,'ORDER_KITCHEN_STATUS','Update Kitchen Status',NULL,NULL,'Update order kitchen status','2026-07-05 19:50:26','2026-07-05 19:50:26'),(51,'ORDER_TAB_OPEN','Open Tabs',NULL,NULL,'Open customer tabs','2026-07-05 19:50:26','2026-07-05 19:50:26'),(52,'ORDER_TAB_CLOSE','Close Tabs',NULL,NULL,'Close customer tabs','2026-07-05 19:50:26','2026-07-05 19:50:26'),(56,'TABLE_CREATE','Create Tables',NULL,NULL,'Create new tables','2026-07-05 19:50:26','2026-07-05 19:50:26'),(57,'TABLE_EDIT','Edit Tables',NULL,NULL,'Edit table details','2026-07-05 19:50:26','2026-07-05 19:50:26'),(58,'TABLE_DELETE','Delete Tables',NULL,NULL,'Delete tables','2026-07-05 19:50:26','2026-07-05 19:50:26'),(59,'TABLE_VIEW','View Tables',NULL,NULL,'View tables','2026-07-05 19:50:26','2026-07-05 19:50:26'),(60,'TABLE_UPDATE_STATUS','Update Table Status',NULL,NULL,'Update table status','2026-07-05 19:50:26','2026-07-05 19:50:26'),(61,'TABLE_ASSIGN_ORDER','Assign Order to Table',NULL,NULL,'Assign orders to tables','2026-07-05 19:50:26','2026-07-05 19:50:26'),(62,'TABLE_MERGE','Merge Tables',NULL,NULL,'Merge tables','2026-07-05 19:50:26','2026-07-05 19:50:26'),(63,'TABLE_SPLIT','Split Tables',NULL,NULL,'Split tables','2026-07-05 19:50:26','2026-07-05 19:50:26'),(64,'INVENTORY_CREATE','Create Inventory Items',NULL,NULL,'Create new inventory items','2026-07-05 19:50:26','2026-07-05 19:50:26'),(65,'INVENTORY_EDIT','Edit Inventory Items',NULL,NULL,'Edit inventory items','2026-07-05 19:50:26','2026-07-05 19:50:26'),(66,'INVENTORY_DELETE','Delete Inventory Items',NULL,NULL,'Delete inventory items','2026-07-05 19:50:26','2026-07-05 19:50:26'),(67,'INVENTORY_VIEW','View Inventory',NULL,NULL,'View inventory','2026-07-05 19:50:26','2026-07-05 19:50:26'),(68,'INVENTORY_ADJUST','Adjust Stock',NULL,NULL,'Adjust inventory stock levels','2026-07-05 19:50:26','2026-07-05 19:50:26'),(69,'INVENTORY_STOCK_OPNAME','Stock Opname',NULL,NULL,'Perform stock opname','2026-07-05 19:50:26','2026-07-05 19:50:26'),(70,'INVENTORY_CREATE_PO','Create Purchase Orders',NULL,NULL,'Create purchase orders','2026-07-05 19:50:26','2026-07-05 19:50:26'),(71,'INVENTORY_RECEIVE_PO','Receive Purchase Orders',NULL,NULL,'Receive purchase orders','2026-07-05 19:50:26','2026-07-05 19:50:26'),(72,'INVENTORY_VIEW_LOW_STOCK','View Low Stock',NULL,NULL,'View low stock alerts','2026-07-05 19:50:26','2026-07-05 19:50:26'),(73,'INVENTORY_VIEW_EXPIRING','View Expiring',NULL,NULL,'View expiring items','2026-07-05 19:50:26','2026-07-05 19:50:26'),(74,'KITCHEN_UPDATE_STATUS','Update Kitchen Status',NULL,NULL,'Update kitchen order status','2026-07-05 19:50:26','2026-07-05 19:50:26'),(75,'KITCHEN_FIRE_COURSE','Fire Course',NULL,NULL,'Fire course to kitchen','2026-07-05 19:50:26','2026-07-05 19:50:26'),(76,'KITCHEN_CANCEL_ITEM','Cancel Kitchen Item',NULL,NULL,'Cancel kitchen items','2026-07-05 19:50:26','2026-07-05 19:50:26'),(78,'RESERVATION_EDIT','Edit Reservations',NULL,NULL,'Edit existing reservations','2026-07-05 19:50:26','2026-07-05 19:50:26'),(79,'RESERVATION_DELETE','Delete Reservations',NULL,NULL,'Delete reservations','2026-07-05 19:50:26','2026-07-05 19:50:26'),(80,'RESERVATION_VIEW','View Reservations',NULL,NULL,'View reservations','2026-07-05 19:50:26','2026-07-05 19:50:26'),(81,'RESERVATION_CONFIRM','Confirm Reservations',NULL,NULL,'Confirm and seat reservations','2026-07-05 19:50:26','2026-07-05 19:50:26'),(82,'RESERVATION_WAITLIST','Manage Waitlist',NULL,NULL,'Manage reservation waitlist','2026-07-05 19:50:26','2026-07-05 19:50:26'),(83,'RESERVATION_VIEW_GUEST_NOTES','View Guest Notes',NULL,NULL,'View guest notes and preferences','2026-07-05 19:50:26','2026-07-05 19:50:26'),(85,'ACCOUNTING_VIEW_REVENUE','View Revenue',NULL,NULL,'View revenue reports','2026-07-05 19:50:26','2026-07-05 19:50:26'),(86,'ACCOUNTING_VIEW_EXPENSES','View Expenses',NULL,NULL,'View expense reports','2026-07-05 19:50:26','2026-07-05 19:50:26'),(87,'ACCOUNTING_VIEW_PROFIT','View Profit',NULL,NULL,'View profit reports','2026-07-05 19:50:26','2026-07-05 19:50:26'),(88,'ACCOUNTING_VIEW_TRANSACTIONS','View Transactions',NULL,NULL,'View financial transactions','2026-07-05 19:50:26','2026-07-05 19:50:26'),(89,'ACCOUNTING_CREATE_JOURNAL','Create Journal Entries',NULL,NULL,'Create accounting journal entries','2026-07-05 19:50:26','2026-07-05 19:50:26'),(90,'ACCOUNTING_VIEW_TAX','View Tax Reports',NULL,NULL,'View tax reports','2026-07-05 19:50:26','2026-07-05 19:50:26'),(91,'ACCOUNTING_MANAGE_PAYABLES','Manage Payables',NULL,NULL,'Manage accounts payable','2026-07-05 19:50:26','2026-07-05 19:50:26'),(92,'ACCOUNTING_MANAGE_RECEIVABLES','Manage Receivables',NULL,NULL,'Manage accounts receivable','2026-07-05 19:50:26','2026-07-05 19:50:26'),(93,'CRM_VIEW_CUSTOMERS','View Customers',NULL,NULL,'View customer list','2026-07-05 19:50:26','2026-07-05 19:50:26'),(94,'CRM_VIEW_CUSTOMER_DETAIL','View Customer Details',NULL,NULL,'View customer details','2026-07-05 19:50:26','2026-07-05 19:50:26'),(95,'CRM_ADD_CUSTOMER','Add Customers',NULL,NULL,'Add new customers','2026-07-05 19:50:26','2026-07-05 19:50:26'),(96,'CRM_EDIT_CUSTOMER','Edit Customers',NULL,NULL,'Edit customer information','2026-07-05 19:50:26','2026-07-05 19:50:26'),(97,'CRM_MANAGE_LOYALTY','Manage Loyalty Points',NULL,NULL,'Manage customer loyalty points','2026-07-05 19:50:26','2026-07-05 19:50:26'),(98,'CRM_VIEW_HISTORY','View Purchase History',NULL,NULL,'View customer purchase history','2026-07-05 19:50:26','2026-07-05 19:50:26'),(99,'CRM_VIEW_PREFERENCES','View Customer Preferences',NULL,NULL,'View customer preferences','2026-07-05 19:50:26','2026-07-05 19:50:26'),(100,'CRM_MARKETING','Marketing Campaigns',NULL,NULL,'Create and manage marketing campaigns','2026-07-05 19:50:26','2026-07-05 19:50:26'),(101,'REPORT_SALES','View Sales Reports',NULL,NULL,'View sales reports','2026-07-05 19:50:26','2026-07-05 19:50:26'),(102,'REPORT_INVENTORY','View Inventory Reports',NULL,NULL,'View inventory reports','2026-07-05 19:50:26','2026-07-05 19:50:26'),(103,'REPORT_STAFF','View Staff Performance',NULL,NULL,'View staff performance reports','2026-07-05 19:50:26','2026-07-05 19:50:26'),(104,'REPORT_FINANCIAL','View Financial Reports',NULL,NULL,'View financial reports','2026-07-05 19:50:26','2026-07-05 19:50:26'),(105,'REPORT_CUSTOM','Custom Reports',NULL,NULL,'Create custom reports','2026-07-05 19:50:26','2026-07-05 19:50:26'),(106,'REPORT_EXPORT','Export Data',NULL,NULL,'Export report data','2026-07-05 19:50:26','2026-07-05 19:50:26'),(107,'REPORT_SCHEDULE','Schedule Reports',NULL,NULL,'Schedule automated reports','2026-07-05 19:50:26','2026-07-05 19:50:26'),(108,'HR_VIEW_EMPLOYEES','View Employees',NULL,NULL,'View employee list','2026-07-05 19:50:26','2026-07-05 19:50:26'),(109,'HR_ADD_EMPLOYEE','Add Employees',NULL,NULL,'Add new employees','2026-07-05 19:50:26','2026-07-05 19:50:26'),(110,'HR_EDIT_EMPLOYEE','Edit Employees',NULL,NULL,'Edit employee information','2026-07-05 19:50:26','2026-07-05 19:50:26'),(111,'HR_DELETE_EMPLOYEE','Delete Employees',NULL,NULL,'Delete employees','2026-07-05 19:50:26','2026-07-05 19:50:26'),(112,'HR_VIEW_PAYROLL','View Payroll',NULL,NULL,'View payroll information','2026-07-05 19:50:26','2026-07-05 19:50:26'),(113,'HR_MANAGE_PAYROLL','Manage Payroll',NULL,NULL,'Manage payroll processing','2026-07-05 19:50:26','2026-07-05 19:50:26'),(114,'HR_VIEW_SCHEDULE','View Schedule',NULL,NULL,'View employee schedules','2026-07-05 19:50:26','2026-07-05 19:50:26'),(115,'HR_CREATE_SCHEDULE','Create Schedule',NULL,NULL,'Create employee schedules','2026-07-05 19:50:26','2026-07-05 19:50:26'),(116,'HR_PERFORMANCE','Performance Review',NULL,NULL,'Conduct performance reviews','2026-07-05 19:50:26','2026-07-05 19:50:26'),(117,'HR_VIEW_OWN_PROFILE','View Own Profile',NULL,NULL,'View own employee profile','2026-07-05 19:50:26','2026-07-05 19:50:26'),(118,'HR_VIEW_OWN_SCHEDULE','View Own Schedule',NULL,NULL,'View own schedule','2026-07-05 19:50:26','2026-07-05 19:50:26'),(119,'DELIVERY_VIEW','View Deliveries',NULL,NULL,'View delivery list','2026-07-05 19:50:26','2026-07-05 19:50:26'),(120,'DELIVERY_CREATE','Create Delivery',NULL,NULL,'Create new delivery','2026-07-05 19:50:26','2026-07-05 19:50:26'),(121,'DELIVERY_EDIT','Edit Delivery',NULL,NULL,'Edit delivery details','2026-07-05 19:50:26','2026-07-05 19:50:26'),(122,'DELIVERY_ASSIGN_DRIVER','Assign Driver',NULL,NULL,'Assign driver to delivery','2026-07-05 19:50:26','2026-07-05 19:50:26'),(123,'DELIVERY_UPDATE_STATUS','Update Status',NULL,NULL,'Update delivery status','2026-07-05 19:50:26','2026-07-05 19:50:26'),(124,'DELIVERY_TRACK','Track Delivery',NULL,NULL,'Track delivery status','2026-07-05 19:50:26','2026-07-05 19:50:26'),(125,'SUPPLYCHAIN_VIEW','View Supply Chain',NULL,NULL,'View supply chain information','2026-07-05 19:50:26','2026-07-05 19:50:26'),(126,'SUPPLYCHAIN_MANAGE_SUPPLIERS','Manage Suppliers',NULL,NULL,'Manage supplier information','2026-07-05 19:50:26','2026-07-05 19:50:26'),(127,'SUPPLYCHAIN_PURCHASE_PLANNING','Purchase Planning',NULL,NULL,'Plan purchases','2026-07-05 19:50:26','2026-07-05 19:50:26'),(128,'SUPPLYCHAIN_QUALITY_CONTROL','Quality Control',NULL,NULL,'Manage quality control','2026-07-05 19:50:26','2026-07-05 19:50:26'),(129,'QUALITY_VIEW','View Quality',NULL,NULL,'View quality information','2026-07-05 19:50:26','2026-07-05 19:50:26'),(130,'QUALITY_CREATE_CHECK','Create Quality Check',NULL,NULL,'Create quality check records','2026-07-05 19:50:26','2026-07-05 19:50:26'),(132,'LOYALTY_VIEW','View Loyalty',NULL,NULL,'View loyalty program','2026-07-05 19:50:26','2026-07-05 19:50:26'),(133,'LOYALTY_MANAGE','Manage Loyalty',NULL,NULL,'Manage loyalty program','2026-07-05 19:50:26','2026-07-05 19:50:26'),(134,'LOYALTY_REDEEM','Redeem Points',NULL,NULL,'Redeem loyalty points','2026-07-05 19:50:26','2026-07-05 19:50:26'),(135,'SETTINGS_VIEW','View Settings',NULL,NULL,'View system settings','2026-07-05 19:50:26','2026-07-05 19:50:26'),(136,'SETTINGS_TAX_CONFIG','Configure Tax',NULL,NULL,'Configure tax settings','2026-07-05 19:50:26','2026-07-05 19:50:26'),(137,'SETTINGS_PAYMENT_CONFIG','Configure Payment',NULL,NULL,'Configure payment methods','2026-07-05 19:50:26','2026-07-05 19:50:26'),(139,'USER_VIEW','View Users',NULL,NULL,'View user list','2026-07-05 19:50:26','2026-07-05 19:50:26'),(140,'USER_CREATE','Create Users',NULL,NULL,'Create new users','2026-07-05 19:50:26','2026-07-05 19:50:26'),(141,'USER_EDIT','Edit Users',NULL,NULL,'Edit user information','2026-07-05 19:50:26','2026-07-05 19:50:26'),(142,'USER_DELETE','Delete Users',NULL,NULL,'Delete users','2026-07-05 19:50:26','2026-07-05 19:50:26'),(143,'USER_ASSIGN_ROLE','Assign Roles',NULL,NULL,'Assign roles to users','2026-07-05 19:50:26','2026-07-05 19:50:26'),(144,'ACCOUNTING_VIEW','View Accounting Data','Accounting','view','View accounting data and reports','2026-07-06 17:57:07','2026-07-06 17:57:07'),(145,'ACCOUNTING_CREATE','Create Journal Entries','Accounting','create','Create journal entries','2026-07-06 17:57:07','2026-07-06 17:57:07'),(146,'ACCOUNTING_EDIT','Edit Journal Entries','Accounting','edit','Edit journal entries','2026-07-06 17:57:07','2026-07-06 17:57:07'),(147,'ACCOUNTING_DELETE','Delete Journal Entries','Accounting','delete','Delete journal entries','2026-07-06 17:57:07','2026-07-06 17:57:07'),(148,'ACCOUNTING_APPROVE','Approve Journal Entries','Accounting','approve','Approve journal entries','2026-07-06 17:57:07','2026-07-06 17:57:07'),(149,'ACCOUNTING_CLOSE_PERIOD','Close Accounting Period','Accounting','close','Close accounting period','2026-07-06 17:57:07','2026-07-06 17:57:07');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_modifier_assignments`
--

DROP TABLE IF EXISTS `product_modifier_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_modifier_assignments` (
  `assignment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `modifier_group_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `idx_product_modifier_assignments_product_group` (`product_id`,`modifier_group_id`),
  KEY `idx_product_modifier_assignments_product_id` (`product_id`),
  KEY `idx_product_modifier_assignments_modifier_group_id` (`modifier_group_id`),
  CONSTRAINT `product_modifier_assignments_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_modifier_assignments_ibfk_2` FOREIGN KEY (`modifier_group_id`) REFERENCES `product_modifier_groups` (`modifier_group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_modifier_assignments`
--

LOCK TABLES `product_modifier_assignments` WRITE;
/*!40000 ALTER TABLE `product_modifier_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_modifier_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_modifier_groups`
--

DROP TABLE IF EXISTS `product_modifier_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_modifier_groups` (
  `modifier_group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `group_code` varchar(50) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `min_selections` int(11) DEFAULT 0,
  `max_selections` int(11) DEFAULT 1,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`modifier_group_id`),
  UNIQUE KEY `idx_modifier_groups_tenant_code` (`tenant_id`,`group_code`),
  KEY `idx_modifier_groups_tenant_id` (`tenant_id`),
  CONSTRAINT `product_modifier_groups_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_modifier_groups`
--

LOCK TABLES `product_modifier_groups` WRITE;
/*!40000 ALTER TABLE `product_modifier_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_modifier_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_modifiers`
--

DROP TABLE IF EXISTS `product_modifiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_modifiers` (
  `modifier_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `modifier_group_id` bigint(20) unsigned NOT NULL,
  `modifier_code` varchar(50) NOT NULL,
  `modifier_name` varchar(100) NOT NULL,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `is_available` tinyint(1) DEFAULT 1,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`modifier_id`),
  UNIQUE KEY `idx_modifiers_group_code` (`modifier_group_id`,`modifier_code`),
  KEY `idx_modifiers_modifier_group_id` (`modifier_group_id`),
  CONSTRAINT `product_modifiers_ibfk_1` FOREIGN KEY (`modifier_group_id`) REFERENCES `product_modifier_groups` (`modifier_group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_modifiers`
--

LOCK TABLES `product_modifiers` WRITE;
/*!40000 ALTER TABLE `product_modifiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_modifiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `variant_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `variant_code` varchar(50) NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `is_default` tinyint(1) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`variant_id`),
  UNIQUE KEY `idx_product_variants_product_code` (`product_id`,`variant_code`),
  KEY `idx_product_variants_product_id` (`product_id`),
  CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `preparation_time` int(11) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `idx_products_tenant_code` (`tenant_id`,`product_code`),
  KEY `idx_products_tenant_id` (`tenant_id`),
  KEY `idx_products_category_id` (`category_id`),
  KEY `idx_products_name` (`product_name`),
  KEY `idx_products_status` (`status`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,1,'PROD001','Nasi Goreng',NULL,25000.00,NULL,NULL,1,0,'ACTIVE','2026-07-02 12:12:36','2026-07-02 12:12:36',NULL,NULL),(2,1,1,'PROD002','Mie Goreng',NULL,22000.00,NULL,NULL,1,0,'ACTIVE','2026-07-02 12:12:36','2026-07-02 12:12:36',NULL,NULL),(3,1,2,'PROD003','Es Teh Manis',NULL,5000.00,NULL,NULL,1,0,'ACTIVE','2026-07-02 12:12:36','2026-07-02 12:12:36',NULL,NULL),(4,1,2,'PROD004','Jus Jeruk',NULL,10000.00,NULL,NULL,1,0,'ACTIVE','2026-07-02 12:12:36','2026-07-02 12:12:36',NULL,NULL),(5,1,3,'PROD005','Gado-Gado',NULL,20000.00,NULL,NULL,1,0,'ACTIVE','2026-07-02 12:12:36','2026-07-02 12:12:36',NULL,NULL),(6,1,1,'HF001','Nasi Box','Complete meal box',25000.00,NULL,NULL,1,0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL,NULL),(7,3,2,'MC001','Nasi Goreng','Fried rice',35000.00,NULL,NULL,1,0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL,NULL),(8,3,2,'MC002','Mie Goreng','Fried noodles',30000.00,NULL,NULL,1,0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL,NULL),(9,3,3,'BV001','Es Teh','Iced tea',10000.00,NULL,NULL,1,0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL,NULL),(10,3,3,'BV002','Kopi','Coffee',15000.00,NULL,NULL,1,0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL,NULL),(11,3,4,'DS001','Puding','Pudding',12000.00,NULL,NULL,1,0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL,NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_allergens`
--

DROP TABLE IF EXISTS `recipe_allergens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipe_allergens` (
  `recipe_allergen_id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_id` int(11) NOT NULL,
  `allergen_name` varchar(100) NOT NULL,
  `allergen_type` varchar(50) DEFAULT NULL COMMENT 'gluten, dairy, nuts, soy, eggs, fish, shellfish, etc',
  `severity` varchar(20) DEFAULT 'medium' COMMENT 'low, medium, high',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recipe_allergen_id`),
  KEY `idx_recipe` (`recipe_id`),
  KEY `idx_allergen` (`allergen_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_allergens`
--

LOCK TABLES `recipe_allergens` WRITE;
/*!40000 ALTER TABLE `recipe_allergens` DISABLE KEYS */;
/*!40000 ALTER TABLE `recipe_allergens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_ingredients`
--

DROP TABLE IF EXISTS `recipe_ingredients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipe_ingredients` (
  `recipe_ingredient_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `recipe_id` bigint(20) unsigned NOT NULL,
  `ingredient_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `allergen_info` text DEFAULT NULL,
  `is_critical` tinyint(1) DEFAULT 0,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recipe_ingredient_id`),
  KEY `idx_recipe_ingredients_recipe_id` (`recipe_id`),
  KEY `idx_recipe_ingredients_ingredient_id` (`ingredient_id`),
  KEY `idx_supplier` (`supplier_id`),
  CONSTRAINT `fk_ri_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_ingredients`
--

LOCK TABLES `recipe_ingredients` WRITE;
/*!40000 ALTER TABLE `recipe_ingredients` DISABLE KEYS */;
INSERT INTO `recipe_ingredients` VALUES (1,1,1,0.20,'kg',NULL,0,NULL,'2026-07-02 12:33:06');
/*!40000 ALTER TABLE `recipe_ingredients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_nutrition`
--

DROP TABLE IF EXISTS `recipe_nutrition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipe_nutrition` (
  `nutrition_id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_id` int(11) NOT NULL,
  `serving_size` decimal(10,2) DEFAULT 1.00,
  `calories` decimal(10,2) DEFAULT NULL,
  `protein` decimal(10,2) DEFAULT NULL,
  `carbohydrates` decimal(10,2) DEFAULT NULL,
  `fat` decimal(10,2) DEFAULT NULL,
  `saturated_fat` decimal(10,2) DEFAULT NULL,
  `trans_fat` decimal(10,2) DEFAULT NULL,
  `cholesterol` decimal(10,2) DEFAULT NULL,
  `sodium` decimal(10,2) DEFAULT NULL,
  `fiber` decimal(10,2) DEFAULT NULL,
  `sugar` decimal(10,2) DEFAULT NULL,
  `vitamin_a` decimal(10,2) DEFAULT NULL,
  `vitamin_c` decimal(10,2) DEFAULT NULL,
  `calcium` decimal(10,2) DEFAULT NULL,
  `iron` decimal(10,2) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`nutrition_id`),
  KEY `idx_recipe` (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_nutrition`
--

LOCK TABLES `recipe_nutrition` WRITE;
/*!40000 ALTER TABLE `recipe_nutrition` DISABLE KEYS */;
/*!40000 ALTER TABLE `recipe_nutrition` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_preparation_steps`
--

DROP TABLE IF EXISTS `recipe_preparation_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipe_preparation_steps` (
  `step_id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `step_description` text NOT NULL,
  `estimated_time` int(11) DEFAULT NULL COMMENT 'Estimated time in minutes',
  `temperature` varchar(50) DEFAULT NULL COMMENT 'Temperature if applicable',
  `equipment_needed` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`step_id`),
  KEY `idx_recipe` (`recipe_id`),
  KEY `idx_step` (`recipe_id`,`step_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_preparation_steps`
--

LOCK TABLES `recipe_preparation_steps` WRITE;
/*!40000 ALTER TABLE `recipe_preparation_steps` DISABLE KEYS */;
/*!40000 ALTER TABLE `recipe_preparation_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_versions`
--

DROP TABLE IF EXISTS `recipe_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipe_versions` (
  `version_id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_id` int(11) NOT NULL,
  `version_number` int(11) NOT NULL,
  `version_name` varchar(100) DEFAULT NULL,
  `changes_description` text DEFAULT NULL,
  `total_cost` decimal(15,2) DEFAULT 0.00,
  `cost_per_portion` decimal(15,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`version_id`),
  KEY `idx_recipe` (`recipe_id`),
  KEY `idx_version` (`recipe_id`,`version_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_versions`
--

LOCK TABLES `recipe_versions` WRITE;
/*!40000 ALTER TABLE `recipe_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `recipe_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipes`
--

DROP TABLE IF EXISTS `recipes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipes` (
  `recipe_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `recipe_code` varchar(50) NOT NULL,
  `recipe_name` varchar(100) NOT NULL,
  `instructions` text DEFAULT NULL,
  `yield_quantity` decimal(10,2) DEFAULT 1.00,
  `yield_unit` varchar(20) DEFAULT 'portion',
  `sourcing_type` enum('self_produced','outsourced','supplier_sourced','mixed') DEFAULT 'supplier_sourced',
  `production_cost_labor` decimal(10,2) DEFAULT 0.00,
  `production_cost_equipment` decimal(10,2) DEFAULT 0.00,
  `production_cost_overhead` decimal(10,2) DEFAULT 0.00,
  `halal_certified` tinyint(1) DEFAULT 0,
  `halal_certification_id` varchar(100) DEFAULT NULL,
  `preparation_time_minutes` int(11) DEFAULT 0,
  `difficulty_level` enum('EASY','MEDIUM','HARD') DEFAULT 'MEDIUM',
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`recipe_id`),
  UNIQUE KEY `idx_recipes_tenant_code` (`tenant_id`,`recipe_code`),
  KEY `idx_recipes_tenant_id` (`tenant_id`),
  KEY `idx_recipes_product_id` (`product_id`),
  KEY `idx_recipes_status` (`status`),
  KEY `idx_sourcing_type` (`sourcing_type`),
  KEY `idx_halal_certified` (`halal_certified`),
  CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipes`
--

LOCK TABLES `recipes` WRITE;
/*!40000 ALTER TABLE `recipes` DISABLE KEYS */;
INSERT INTO `recipes` VALUES (1,1,1,'REC001','Nasi Goreng Recipe','Fry rice with vegetables and egg',1.00,'portion','supplier_sourced',0.00,0.00,0.00,0,NULL,0,'MEDIUM','ACTIVE','2026-07-02 12:32:46','2026-07-02 12:32:46',NULL,NULL),(2,1,1,'TEST_001','Test Recipe','Test instructions',1.00,'portion','supplier_sourced',10.00,5.00,3.00,0,NULL,30,'EASY','ACTIVE','2026-07-06 20:01:59','2026-07-06 20:01:59',NULL,NULL),(4,1,1,'TEST_1783368155','Test Recipe','Test instructions',1.00,'portion','supplier_sourced',10.00,5.00,3.00,0,NULL,30,'EASY','ACTIVE','2026-07-06 20:02:35','2026-07-06 20:02:35',NULL,NULL),(5,1,1,'TEST_1783368171','Test Recipe','Test instructions',1.00,'portion','supplier_sourced',10.00,5.00,3.00,0,NULL,30,'EASY','ACTIVE','2026-07-06 20:02:52','2026-07-06 20:02:52',NULL,NULL);
/*!40000 ALTER TABLE `recipes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referral_programs`
--

DROP TABLE IF EXISTS `referral_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referral_programs` (
  `program_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `program_code` varchar(50) NOT NULL,
  `program_name` varchar(150) NOT NULL,
  `program_type` enum('RESTAURANT_REFERRAL','CONSUMER_REFERRAL','PARTNER_REFERRAL') NOT NULL,
  `description` text DEFAULT NULL,
  `referrer_reward_type` enum('CREDIT','DISCOUNT','CASH','POINTS') NOT NULL,
  `referrer_reward_value` decimal(10,2) NOT NULL,
  `referee_reward_type` enum('CREDIT','DISCOUNT','CASH','POINTS') NOT NULL,
  `referee_reward_value` decimal(10,2) NOT NULL,
  `max_rewards_per_referrer` int(11) DEFAULT NULL,
  `min_referee_purchase` decimal(10,2) DEFAULT NULL,
  `program_start_date` date NOT NULL,
  `program_end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `terms_conditions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`program_id`),
  UNIQUE KEY `program_code` (`program_code`),
  KEY `idx_program_type` (`program_type`),
  KEY `idx_program_active` (`is_active`),
  KEY `idx_program_dates` (`program_start_date`,`program_end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referral_programs`
--

LOCK TABLES `referral_programs` WRITE;
/*!40000 ALTER TABLE `referral_programs` DISABLE KEYS */;
INSERT INTO `referral_programs` VALUES (1,'REST_REF_2024','Restaurant Referral Program 2024','RESTAURANT_REFERRAL','Refer other restaurants to earn $500 credit for both parties','CREDIT',500.00,'CREDIT',500.00,10,100.00,'2026-07-05',NULL,1,NULL,'2026-07-05 03:53:40','2026-07-05 03:53:40'),(2,'CONS_REF_2024','Consumer Referral Program 2024','CONSUMER_REFERRAL','Refer friends to earn $10 discount for referrer, $5 for referee','DISCOUNT',10.00,'DISCOUNT',5.00,50,20.00,'2026-07-05',NULL,1,NULL,'2026-07-05 03:53:40','2026-07-05 03:53:40');
/*!40000 ALTER TABLE `referral_programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referral_transactions`
--

DROP TABLE IF EXISTS `referral_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referral_transactions` (
  `transaction_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `program_id` bigint(20) unsigned NOT NULL,
  `referrer_id` bigint(20) unsigned NOT NULL,
  `referee_id` bigint(20) unsigned DEFAULT NULL,
  `referral_code` varchar(50) NOT NULL,
  `referral_status` enum('PENDING','CONVERTED','COMPLETED','EXPIRED','CANCELLED') DEFAULT 'PENDING',
  `conversion_date` timestamp NULL DEFAULT NULL,
  `purchase_amount` decimal(10,2) DEFAULT NULL,
  `referrer_reward_earned` decimal(10,2) DEFAULT NULL,
  `referee_reward_earned` decimal(10,2) DEFAULT NULL,
  `referrer_reward_status` enum('PENDING','EARNED','PAID','FORFEITED') DEFAULT 'PENDING',
  `referee_reward_status` enum('PENDING','EARNED','PAID','FORFEITED') DEFAULT 'PENDING',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `idx_referral_program` (`program_id`),
  KEY `idx_referral_referrer` (`referrer_id`),
  KEY `idx_referral_referee` (`referee_id`),
  KEY `idx_referral_code` (`referral_code`),
  KEY `idx_referral_status` (`referral_status`),
  CONSTRAINT `fk_referral_program` FOREIGN KEY (`program_id`) REFERENCES `referral_programs` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referral_transactions`
--

LOCK TABLES `referral_transactions` WRITE;
/*!40000 ALTER TABLE `referral_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `referral_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_jobs`
--

DROP TABLE IF EXISTS `report_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_jobs` (
  `report_job_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `parameters` text DEFAULT NULL,
  `status` enum('QUEUED','PROCESSING','COMPLETED','FAILED') DEFAULT 'QUEUED',
  `file_path` varchar(500) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`report_job_id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_jobs`
--

LOCK TABLES `report_jobs` WRITE;
/*!40000 ALTER TABLE `report_jobs` DISABLE KEYS */;
INSERT INTO `report_jobs` VALUES (1,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:26:27',NULL,NULL),(2,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:28:40',NULL,NULL),(3,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:30:24',NULL,NULL),(4,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:32:14',NULL,NULL),(5,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:37:30',NULL,NULL),(6,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:41:56',NULL,NULL),(7,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:42:56',NULL,NULL),(8,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:44:00',NULL,NULL),(9,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:44:40',NULL,NULL),(10,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:46:33',NULL,NULL),(11,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:47:12',NULL,NULL),(12,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:48:42',NULL,NULL),(13,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:49:32',NULL,NULL),(14,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:50:24',NULL,NULL),(15,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:51:17',NULL,NULL),(16,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:52:06',NULL,NULL),(17,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:52:30',NULL,NULL),(18,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:52:56',NULL,NULL),(19,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:53:48',NULL,NULL),(20,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:54:27',NULL,NULL),(21,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:55:04',NULL,NULL),(22,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:56:10',NULL,NULL),(23,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:57:11',NULL,NULL),(24,1,2,2,'TRIAL_BALANCE','Trial Balance Report','{\"as_of_date\":\"2026-07-07\"}','QUEUED',NULL,NULL,'2026-07-06 18:57:24',NULL,NULL);
/*!40000 ALTER TABLE `report_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_queue`
--

DROP TABLE IF EXISTS `report_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_queue` (
  `queue_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_job_id` bigint(20) unsigned NOT NULL,
  `priority` int(11) DEFAULT 0,
  `queued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`queue_id`),
  KEY `idx_priority` (`priority`),
  KEY `idx_queued_at` (`queued_at`),
  KEY `report_job_id` (`report_job_id`),
  CONSTRAINT `report_queue_ibfk_1` FOREIGN KEY (`report_job_id`) REFERENCES `report_jobs` (`report_job_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_queue`
--

LOCK TABLES `report_queue` WRITE;
/*!40000 ALTER TABLE `report_queue` DISABLE KEYS */;
INSERT INTO `report_queue` VALUES (1,1,0,'2026-07-06 18:26:27'),(2,2,0,'2026-07-06 18:28:40'),(3,3,0,'2026-07-06 18:30:24'),(4,4,0,'2026-07-06 18:32:14'),(5,5,0,'2026-07-06 18:37:30'),(6,6,0,'2026-07-06 18:41:56'),(7,7,0,'2026-07-06 18:42:56'),(8,8,0,'2026-07-06 18:44:00'),(9,9,0,'2026-07-06 18:44:40'),(10,10,0,'2026-07-06 18:46:33'),(11,11,0,'2026-07-06 18:47:12'),(12,12,0,'2026-07-06 18:48:42'),(13,13,0,'2026-07-06 18:49:32'),(14,14,0,'2026-07-06 18:50:24'),(15,15,0,'2026-07-06 18:51:17'),(16,16,0,'2026-07-06 18:52:06'),(17,17,0,'2026-07-06 18:52:30'),(18,18,0,'2026-07-06 18:52:56'),(19,19,0,'2026-07-06 18:53:48'),(20,20,0,'2026-07-06 18:54:27'),(21,21,0,'2026-07-06 18:55:04'),(22,22,0,'2026-07-06 18:56:10'),(23,23,0,'2026-07-06 18:57:11'),(24,24,0,'2026-07-06 18:57:24');
/*!40000 ALTER TABLE `report_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `reservation_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `reservation_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `table_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `party_size` int(11) NOT NULL,
  `status` enum('PENDING','CONFIRMED','SEATED','COMPLETED','CANCELLED','NO_SHOW') DEFAULT 'PENDING',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`reservation_id`),
  UNIQUE KEY `idx_reservations_tenant_number` (`tenant_id`,`reservation_number`),
  KEY `idx_reservations_tenant_id` (`tenant_id`),
  KEY `idx_reservations_branch_id` (`branch_id`),
  KEY `idx_reservations_table_id` (`table_id`),
  KEY `idx_reservations_date` (`reservation_date`),
  KEY `idx_reservations_status` (`status`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,1,2,'RES-20260702-001','John Doe','08123456789',NULL,1,'2026-07-03','19:00:00',4,'CONFIRMED',NULL,'2026-07-02 12:17:42','2026-07-02 12:17:42',NULL),(2,1,2,'RES-20260702-002','Jane Smith','08123456788',NULL,2,'2026-07-03','20:00:00',2,'CONFIRMED',NULL,'2026-07-02 12:17:42','2026-07-02 12:17:42',NULL),(4,1,2,'RES-20260705-5151','Budi Santoso','+6281234567890',NULL,NULL,'2026-07-10','19:00:00',4,'PENDING','Near window','2026-07-05 21:39:01','2026-07-05 21:39:01',NULL),(5,1,2,'RES-20260705-7836','Budi Santoso','+6281234567890',NULL,NULL,'2026-07-10','19:00:00',4,'PENDING','Near window','2026-07-05 21:39:18','2026-07-05 21:39:18',NULL);
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `review_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `restaurant_id` bigint(20) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`review_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,10,1,2,5,'Great food!','PENDING','2026-07-05 21:39:01','2026-07-05 21:39:01',NULL),(2,10,1,2,5,'Great food!','PENDING','2026-07-05 21:39:18','2026-07-05 21:39:18',NULL);
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_assessments`
--

DROP TABLE IF EXISTS `risk_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_assessments` (
  `risk_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `risk_code` varchar(50) NOT NULL,
  `risk_name` varchar(150) NOT NULL,
  `risk_category` enum('TECHNICAL','BUSINESS','OPERATIONAL','EXTERNAL','FINANCIAL','REGULATORY') NOT NULL,
  `risk_description` text DEFAULT NULL,
  `probability` enum('VERY_LOW','LOW','MEDIUM','HIGH','VERY_HIGH') DEFAULT 'MEDIUM',
  `impact` enum('VERY_LOW','LOW','MEDIUM','HIGH','VERY_HIGH') DEFAULT 'MEDIUM',
  `risk_score` int(11) GENERATED ALWAYS AS (case `probability` when 'VERY_LOW' then 1 when 'LOW' then 2 when 'MEDIUM' then 3 when 'HIGH' then 4 when 'VERY_HIGH' then 5 end * case `impact` when 'VERY_LOW' then 1 when 'LOW' then 2 when 'MEDIUM' then 3 when 'HIGH' then 4 when 'VERY_HIGH' then 5 end) STORED,
  `mitigation_strategy` text DEFAULT NULL,
  `owner` varchar(100) DEFAULT NULL,
  `status` enum('OPEN','MITIGATING','MONITORING','CLOSED') DEFAULT 'OPEN',
  `last_reviewed_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`risk_id`),
  KEY `idx_risk_tenant` (`tenant_id`),
  KEY `idx_risk_category` (`risk_category`),
  KEY `idx_risk_score` (`risk_score`),
  KEY `idx_risk_status` (`status`),
  CONSTRAINT `fk_risk_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_assessments`
--

LOCK TABLES `risk_assessments` WRITE;
/*!40000 ALTER TABLE `risk_assessments` DISABLE KEYS */;
/*!40000 ALTER TABLE `risk_assessments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_incidents`
--

DROP TABLE IF EXISTS `risk_incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_incidents` (
  `incident_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `risk_id` bigint(20) unsigned DEFAULT NULL,
  `incident_code` varchar(50) NOT NULL,
  `incident_title` varchar(200) NOT NULL,
  `incident_description` text DEFAULT NULL,
  `severity` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'MEDIUM',
  `impact_description` text DEFAULT NULL,
  `affected_systems` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`affected_systems`)),
  `root_cause` text DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  `status` enum('OPEN','INVESTIGATING','RESOLVING','RESOLVED','CLOSED') DEFAULT 'OPEN',
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `reported_by` bigint(20) DEFAULT NULL,
  `resolved_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`incident_id`),
  KEY `idx_incident_tenant` (`tenant_id`),
  KEY `idx_incident_risk` (`risk_id`),
  KEY `idx_incident_severity` (`severity`),
  KEY `idx_incident_status` (`status`),
  KEY `idx_incident_occurred` (`occurred_at`),
  CONSTRAINT `fk_incident_risk` FOREIGN KEY (`risk_id`) REFERENCES `risk_assessments` (`risk_id`),
  CONSTRAINT `fk_incident_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_incidents`
--

LOCK TABLES `risk_incidents` WRITE;
/*!40000 ALTER TABLE `risk_incidents` DISABLE KEYS */;
/*!40000 ALTER TABLE `risk_incidents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_fallbacks`
--

DROP TABLE IF EXISTS `role_fallbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_fallbacks` (
  `fallback_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `missing_role_code` varchar(50) NOT NULL COMMENT 'Role that is missing',
  `fallback_role_code` varchar(50) NOT NULL COMMENT 'Role to use as fallback',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`fallback_id`),
  UNIQUE KEY `unique_tenant_missing_role` (`tenant_id`,`missing_role_code`),
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_fallbacks`
--

LOCK TABLES `role_fallbacks` WRITE;
/*!40000 ALTER TABLE `role_fallbacks` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_fallbacks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_feature_modules`
--

DROP TABLE IF EXISTS `role_feature_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_feature_modules` (
  `role_feature_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `enabled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`role_feature_id`),
  UNIQUE KEY `unique_role_module` (`role_id`,`module_id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_module_id` (`module_id`),
  KEY `idx_is_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_feature_modules`
--

LOCK TABLES `role_feature_modules` WRITE;
/*!40000 ALTER TABLE `role_feature_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_feature_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_menus`
--

DROP TABLE IF EXISTS `role_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_menus` (
  `role_menu_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `menu_code` varchar(50) NOT NULL,
  `menu_name` varchar(100) NOT NULL,
  `menu_path` varchar(255) DEFAULT NULL,
  `parent_menu_code` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `icon` varchar(50) DEFAULT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`role_menu_id`),
  UNIQUE KEY `unique_role_menu` (`role_id`,`menu_code`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_menu_code` (`menu_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_menus`
--

LOCK TABLES `role_menus` WRITE;
/*!40000 ALTER TABLE `role_menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_permission_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_permission_id`),
  UNIQUE KEY `idx_role_permissions_role_permission` (`role_id`,`permission_id`),
  KEY `idx_role_permissions_role_id` (`role_id`),
  KEY `idx_role_permissions_permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=356 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,2,1,'2026-07-02 07:41:03'),(2,2,2,'2026-07-02 07:41:03'),(3,2,3,'2026-07-02 07:41:03'),(4,2,4,'2026-07-02 07:41:03'),(5,2,5,'2026-07-02 07:41:03'),(6,2,6,'2026-07-02 07:41:03'),(7,2,7,'2026-07-02 07:41:03'),(8,2,8,'2026-07-02 07:41:03'),(9,2,9,'2026-07-02 07:41:03'),(10,3,1,'2026-07-02 12:07:00'),(11,3,2,'2026-07-02 12:07:00'),(12,3,3,'2026-07-02 12:07:00'),(13,3,4,'2026-07-02 12:07:00'),(14,3,5,'2026-07-02 12:07:00'),(15,3,8,'2026-07-02 12:07:00'),(16,3,9,'2026-07-02 12:07:00'),(17,3,10,'2026-07-02 12:07:00'),(18,3,11,'2026-07-02 12:07:00'),(19,3,12,'2026-07-02 12:07:00'),(20,3,13,'2026-07-02 12:07:00'),(21,3,14,'2026-07-02 12:07:00'),(22,3,15,'2026-07-02 12:07:00'),(23,3,16,'2026-07-02 12:07:00'),(24,3,17,'2026-07-02 12:07:00'),(25,3,18,'2026-07-02 12:07:00'),(26,3,19,'2026-07-02 12:07:00'),(27,3,20,'2026-07-02 12:07:00'),(28,3,21,'2026-07-02 12:07:00'),(29,3,7,'2026-07-02 12:07:00'),(30,4,10,'2026-07-02 12:07:00'),(31,4,11,'2026-07-02 12:07:00'),(32,4,13,'2026-07-02 12:07:00'),(33,4,16,'2026-07-02 12:07:00'),(34,4,17,'2026-07-02 12:07:00'),(35,4,18,'2026-07-02 12:07:00'),(36,4,20,'2026-07-02 12:07:00'),(37,4,21,'2026-07-02 12:07:00'),(38,5,5,'2026-07-02 12:07:00'),(39,5,13,'2026-07-02 12:07:00'),(40,5,15,'2026-07-02 12:07:00'),(41,5,20,'2026-07-02 12:07:00'),(42,5,21,'2026-07-02 12:07:00'),(43,6,10,'2026-07-02 12:07:00'),(44,6,11,'2026-07-02 12:07:00'),(45,6,13,'2026-07-02 12:07:00'),(46,6,14,'2026-07-02 12:07:00'),(47,6,20,'2026-07-02 12:07:00'),(48,6,21,'2026-07-02 12:07:00'),(49,7,4,'2026-07-02 12:07:00'),(50,7,19,'2026-07-02 12:07:00'),(51,7,20,'2026-07-02 12:07:00'),(52,7,21,'2026-07-02 12:07:00'),(53,8,2,'2026-07-02 12:07:00'),(54,8,3,'2026-07-02 12:07:00'),(55,8,16,'2026-07-02 12:07:00'),(56,8,17,'2026-07-02 12:07:00'),(57,8,18,'2026-07-02 12:07:00'),(58,2,22,'2026-07-05 17:20:40'),(59,2,23,'2026-07-05 17:20:40'),(60,2,24,'2026-07-05 17:20:40'),(61,2,25,'2026-07-05 17:20:40'),(62,2,26,'2026-07-05 17:20:40'),(63,2,27,'2026-07-05 17:20:40'),(64,2,28,'2026-07-05 17:20:40'),(65,2,29,'2026-07-05 17:20:40'),(66,2,30,'2026-07-05 17:20:40'),(67,2,31,'2026-07-05 17:20:40'),(68,2,32,'2026-07-05 17:20:40'),(69,2,33,'2026-07-05 17:20:40'),(70,2,34,'2026-07-05 17:20:40'),(71,2,35,'2026-07-05 17:20:40'),(72,2,36,'2026-07-05 19:50:30'),(73,2,37,'2026-07-05 19:50:30'),(74,2,38,'2026-07-05 19:50:30'),(75,2,39,'2026-07-05 19:50:30'),(76,2,40,'2026-07-05 19:50:30'),(77,2,41,'2026-07-05 19:50:30'),(78,2,42,'2026-07-05 19:50:30'),(79,2,10,'2026-07-05 19:50:30'),(80,2,43,'2026-07-05 19:50:30'),(81,2,12,'2026-07-05 19:50:30'),(82,2,13,'2026-07-05 19:50:30'),(83,2,44,'2026-07-05 19:50:30'),(84,2,45,'2026-07-05 19:50:30'),(85,2,46,'2026-07-05 19:50:30'),(86,2,47,'2026-07-05 19:50:30'),(87,2,48,'2026-07-05 19:50:30'),(88,2,49,'2026-07-05 19:50:30'),(89,2,50,'2026-07-05 19:50:30'),(90,2,51,'2026-07-05 19:50:30'),(91,2,52,'2026-07-05 19:50:30'),(92,2,56,'2026-07-05 19:50:30'),(93,2,57,'2026-07-05 19:50:30'),(94,2,58,'2026-07-05 19:50:30'),(95,2,59,'2026-07-05 19:50:30'),(96,2,60,'2026-07-05 19:50:30'),(97,2,61,'2026-07-05 19:50:30'),(98,2,62,'2026-07-05 19:50:30'),(99,2,63,'2026-07-05 19:50:30'),(100,2,64,'2026-07-05 19:50:30'),(101,2,65,'2026-07-05 19:50:30'),(102,2,66,'2026-07-05 19:50:30'),(103,2,67,'2026-07-05 19:50:30'),(104,2,68,'2026-07-05 19:50:30'),(105,2,69,'2026-07-05 19:50:30'),(106,2,70,'2026-07-05 19:50:30'),(107,2,71,'2026-07-05 19:50:30'),(108,2,72,'2026-07-05 19:50:30'),(109,2,73,'2026-07-05 19:50:30'),(110,2,74,'2026-07-05 19:50:30'),(111,2,75,'2026-07-05 19:50:30'),(112,2,76,'2026-07-05 19:50:30'),(113,2,17,'2026-07-05 19:50:30'),(114,2,78,'2026-07-05 19:50:30'),(115,2,79,'2026-07-05 19:50:30'),(116,2,80,'2026-07-05 19:50:30'),(117,2,81,'2026-07-05 19:50:30'),(118,2,82,'2026-07-05 19:50:30'),(119,2,83,'2026-07-05 19:50:30'),(120,2,85,'2026-07-05 19:50:30'),(121,2,86,'2026-07-05 19:50:30'),(122,2,87,'2026-07-05 19:50:30'),(123,2,88,'2026-07-05 19:50:30'),(124,2,89,'2026-07-05 19:50:30'),(125,2,90,'2026-07-05 19:50:30'),(126,2,91,'2026-07-05 19:50:30'),(127,2,92,'2026-07-05 19:50:30'),(128,2,93,'2026-07-05 19:50:30'),(129,2,94,'2026-07-05 19:50:30'),(130,2,95,'2026-07-05 19:50:30'),(131,2,96,'2026-07-05 19:50:30'),(132,2,97,'2026-07-05 19:50:30'),(133,2,98,'2026-07-05 19:50:30'),(134,2,99,'2026-07-05 19:50:30'),(135,2,100,'2026-07-05 19:50:30'),(136,2,101,'2026-07-05 19:50:30'),(137,2,102,'2026-07-05 19:50:30'),(138,2,103,'2026-07-05 19:50:30'),(139,2,104,'2026-07-05 19:50:30'),(140,2,105,'2026-07-05 19:50:30'),(141,2,106,'2026-07-05 19:50:30'),(142,2,107,'2026-07-05 19:50:30'),(143,2,108,'2026-07-05 19:50:30'),(144,2,109,'2026-07-05 19:50:30'),(145,2,110,'2026-07-05 19:50:30'),(146,2,111,'2026-07-05 19:50:30'),(147,2,112,'2026-07-05 19:50:30'),(148,2,113,'2026-07-05 19:50:30'),(149,2,114,'2026-07-05 19:50:30'),(150,2,115,'2026-07-05 19:50:30'),(151,2,116,'2026-07-05 19:50:30'),(152,2,119,'2026-07-05 19:50:30'),(153,2,120,'2026-07-05 19:50:30'),(154,2,121,'2026-07-05 19:50:30'),(155,2,122,'2026-07-05 19:50:30'),(156,2,123,'2026-07-05 19:50:30'),(157,2,124,'2026-07-05 19:50:30'),(158,2,125,'2026-07-05 19:50:30'),(159,2,126,'2026-07-05 19:50:30'),(160,2,127,'2026-07-05 19:50:30'),(161,2,128,'2026-07-05 19:50:30'),(162,2,129,'2026-07-05 19:50:30'),(163,2,130,'2026-07-05 19:50:30'),(164,2,132,'2026-07-05 19:50:30'),(165,2,133,'2026-07-05 19:50:30'),(166,2,134,'2026-07-05 19:50:30'),(167,2,135,'2026-07-05 19:50:30'),(168,2,136,'2026-07-05 19:50:30'),(169,2,137,'2026-07-05 19:50:30'),(170,2,139,'2026-07-05 19:50:30'),(171,2,140,'2026-07-05 19:50:30'),(172,2,141,'2026-07-05 19:50:30'),(173,2,142,'2026-07-05 19:50:30'),(174,2,143,'2026-07-05 19:50:30'),(175,9,13,'2026-07-05 19:50:30'),(176,9,44,'2026-07-05 19:50:30'),(177,9,45,'2026-07-05 19:50:30'),(178,9,46,'2026-07-05 19:50:30'),(179,9,48,'2026-07-05 19:50:30'),(180,9,49,'2026-07-05 19:50:30'),(181,9,59,'2026-07-05 19:50:30'),(182,9,60,'2026-07-05 19:50:30'),(183,9,61,'2026-07-05 19:50:30'),(184,9,39,'2026-07-05 19:50:30'),(185,9,85,'2026-07-05 19:50:30'),(186,9,88,'2026-07-05 19:50:30'),(187,9,90,'2026-07-05 19:50:30'),(188,9,101,'2026-07-05 19:50:30'),(189,9,104,'2026-07-05 19:50:30'),(190,9,95,'2026-07-05 19:50:30'),(191,10,5,'2026-07-05 19:50:30'),(192,10,74,'2026-07-05 19:50:30'),(193,10,75,'2026-07-05 19:50:30'),(194,10,13,'2026-07-05 19:50:30'),(195,10,67,'2026-07-05 19:50:30'),(196,10,72,'2026-07-05 19:50:30'),(197,10,73,'2026-07-05 19:50:30'),(198,10,39,'2026-07-05 19:50:30'),(199,10,42,'2026-07-05 19:50:30'),(200,4,59,'2026-07-05 19:50:30'),(201,4,60,'2026-07-05 19:50:30'),(202,4,61,'2026-07-05 19:50:30'),(203,4,78,'2026-07-05 19:50:30'),(204,4,80,'2026-07-05 19:50:30'),(205,4,81,'2026-07-05 19:50:30'),(206,4,43,'2026-07-05 19:50:30'),(207,4,39,'2026-07-05 19:50:30'),(208,4,95,'2026-07-05 19:50:30'),(209,4,93,'2026-07-05 19:50:30'),(210,4,94,'2026-07-05 19:50:30'),(211,3,39,'2026-07-05 19:50:30'),(212,3,40,'2026-07-05 19:50:30'),(213,3,43,'2026-07-05 19:50:30'),(214,3,44,'2026-07-05 19:50:30'),(215,3,45,'2026-07-05 19:50:30'),(216,3,46,'2026-07-05 19:50:30'),(217,3,48,'2026-07-05 19:50:30'),(218,3,49,'2026-07-05 19:50:30'),(219,3,50,'2026-07-05 19:50:30'),(220,3,59,'2026-07-05 19:50:30'),(221,3,60,'2026-07-05 19:50:30'),(222,3,61,'2026-07-05 19:50:30'),(223,3,62,'2026-07-05 19:50:30'),(224,3,63,'2026-07-05 19:50:30'),(225,3,67,'2026-07-05 19:50:30'),(226,3,65,'2026-07-05 19:50:30'),(227,3,68,'2026-07-05 19:50:30'),(228,3,72,'2026-07-05 19:50:30'),(229,3,73,'2026-07-05 19:50:30'),(230,3,70,'2026-07-05 19:50:30'),(231,3,71,'2026-07-05 19:50:30'),(232,3,74,'2026-07-05 19:50:30'),(233,3,75,'2026-07-05 19:50:30'),(234,3,76,'2026-07-05 19:50:30'),(235,3,78,'2026-07-05 19:50:30'),(236,3,80,'2026-07-05 19:50:30'),(237,3,81,'2026-07-05 19:50:30'),(238,3,82,'2026-07-05 19:50:30'),(239,3,85,'2026-07-05 19:50:30'),(240,3,86,'2026-07-05 19:50:30'),(241,3,87,'2026-07-05 19:50:30'),(242,3,88,'2026-07-05 19:50:30'),(243,3,90,'2026-07-05 19:50:30'),(244,3,93,'2026-07-05 19:50:30'),(245,3,94,'2026-07-05 19:50:30'),(246,3,95,'2026-07-05 19:50:30'),(247,3,96,'2026-07-05 19:50:30'),(248,3,98,'2026-07-05 19:50:30'),(249,3,99,'2026-07-05 19:50:30'),(250,3,100,'2026-07-05 19:50:30'),(251,3,101,'2026-07-05 19:50:30'),(252,3,102,'2026-07-05 19:50:30'),(253,3,103,'2026-07-05 19:50:30'),(254,3,104,'2026-07-05 19:50:30'),(255,3,105,'2026-07-05 19:50:30'),(256,3,106,'2026-07-05 19:50:30'),(257,3,108,'2026-07-05 19:50:30'),(258,3,110,'2026-07-05 19:50:30'),(259,3,112,'2026-07-05 19:50:30'),(260,3,113,'2026-07-05 19:50:30'),(261,3,114,'2026-07-05 19:50:30'),(262,3,115,'2026-07-05 19:50:30'),(263,3,116,'2026-07-05 19:50:30'),(264,3,119,'2026-07-05 19:50:30'),(265,3,120,'2026-07-05 19:50:30'),(266,3,121,'2026-07-05 19:50:30'),(267,3,122,'2026-07-05 19:50:30'),(268,3,123,'2026-07-05 19:50:30'),(269,3,124,'2026-07-05 19:50:30'),(270,3,125,'2026-07-05 19:50:30'),(271,3,126,'2026-07-05 19:50:30'),(272,3,127,'2026-07-05 19:50:30'),(273,3,129,'2026-07-05 19:50:30'),(274,3,28,'2026-07-05 19:50:30'),(275,3,130,'2026-07-05 19:50:30'),(276,3,139,'2026-07-05 19:50:30'),(277,11,64,'2026-07-05 19:50:30'),(278,11,65,'2026-07-05 19:50:30'),(279,11,66,'2026-07-05 19:50:30'),(280,11,67,'2026-07-05 19:50:30'),(281,11,68,'2026-07-05 19:50:30'),(282,11,69,'2026-07-05 19:50:30'),(283,11,70,'2026-07-05 19:50:30'),(284,11,71,'2026-07-05 19:50:30'),(285,11,72,'2026-07-05 19:50:30'),(286,11,73,'2026-07-05 19:50:30'),(287,11,125,'2026-07-05 19:50:30'),(288,11,126,'2026-07-05 19:50:30'),(289,11,127,'2026-07-05 19:50:30'),(290,11,128,'2026-07-05 19:50:30'),(291,11,129,'2026-07-05 19:50:30'),(292,11,28,'2026-07-05 19:50:30'),(293,11,130,'2026-07-05 19:50:30'),(294,11,13,'2026-07-05 19:50:30'),(295,11,102,'2026-07-05 19:50:30'),(296,11,39,'2026-07-05 19:50:30'),(297,11,42,'2026-07-05 19:50:30'),(298,12,13,'2026-07-05 19:50:30'),(299,12,10,'2026-07-05 19:50:30'),(300,12,51,'2026-07-05 19:50:30'),(301,12,52,'2026-07-05 19:50:30'),(302,12,59,'2026-07-05 19:50:30'),(303,12,60,'2026-07-05 19:50:30'),(304,12,61,'2026-07-05 19:50:30'),(305,12,67,'2026-07-05 19:50:30'),(306,12,72,'2026-07-05 19:50:30'),(307,12,39,'2026-07-05 19:50:30'),(308,13,13,'2026-07-05 19:50:30'),(309,13,10,'2026-07-05 19:50:30'),(310,13,67,'2026-07-05 19:50:30'),(311,13,72,'2026-07-05 19:50:30'),(312,13,39,'2026-07-05 19:50:30'),(313,13,132,'2026-07-05 19:50:30'),(314,13,134,'2026-07-05 19:50:30'),(315,14,13,'2026-07-05 19:50:30'),(316,14,39,'2026-07-05 19:50:30'),(317,14,67,'2026-07-05 19:50:30'),(318,14,93,'2026-07-05 19:50:30'),(319,14,94,'2026-07-05 19:50:30'),(320,14,95,'2026-07-05 19:50:30'),(321,14,96,'2026-07-05 19:50:30'),(322,14,98,'2026-07-05 19:50:30'),(323,14,99,'2026-07-05 19:50:30'),(324,14,83,'2026-07-05 19:50:30'),(325,8,59,'2026-07-05 19:50:30'),(326,8,60,'2026-07-05 19:50:30'),(327,8,61,'2026-07-05 19:50:30'),(328,8,78,'2026-07-05 19:50:30'),(329,8,80,'2026-07-05 19:50:30'),(330,8,81,'2026-07-05 19:50:30'),(331,8,82,'2026-07-05 19:50:30'),(332,8,83,'2026-07-05 19:50:30'),(333,8,13,'2026-07-05 19:50:30'),(334,8,39,'2026-07-05 19:50:30'),(335,8,95,'2026-07-05 19:50:30'),(336,8,93,'2026-07-05 19:50:30'),(337,8,94,'2026-07-05 19:50:30'),(338,16,22,'2026-07-06 17:57:25'),(339,16,104,'2026-07-06 17:57:25'),(340,16,145,'2026-07-06 17:57:25'),(341,16,146,'2026-07-06 17:57:25'),(342,16,147,'2026-07-06 17:57:25'),(343,16,148,'2026-07-06 17:57:25'),(344,16,149,'2026-07-06 17:57:25'),(345,16,144,'2026-07-06 17:57:25'),(346,3,145,'2026-07-06 17:57:32'),(347,3,146,'2026-07-06 17:57:32'),(348,3,147,'2026-07-06 17:57:32'),(349,3,149,'2026-07-06 17:57:32'),(350,2,145,'2026-07-06 17:57:32'),(351,2,146,'2026-07-06 17:57:32'),(352,2,147,'2026-07-06 17:57:32'),(353,2,148,'2026-07-06 17:57:32'),(354,2,149,'2026-07-06 17:57:32'),(355,2,144,'2026-07-06 17:57:32');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_template_modules`
--

DROP TABLE IF EXISTS `role_template_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_template_modules` (
  `template_module_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint(20) unsigned NOT NULL,
  `module_code` varchar(50) NOT NULL,
  `access_level` enum('FULL','READ','WRITE','NONE') DEFAULT 'FULL',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`template_module_id`),
  UNIQUE KEY `unique_template_module` (`template_id`,`module_code`),
  KEY `idx_template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_template_modules`
--

LOCK TABLES `role_template_modules` WRITE;
/*!40000 ALTER TABLE `role_template_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_template_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_template_permissions`
--

DROP TABLE IF EXISTS `role_template_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_template_permissions` (
  `template_permission_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint(20) unsigned NOT NULL,
  `permission_code` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`template_permission_id`),
  UNIQUE KEY `unique_template_permission` (`template_id`,`permission_code`),
  KEY `idx_template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_template_permissions`
--

LOCK TABLES `role_template_permissions` WRITE;
/*!40000 ALTER TABLE `role_template_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_template_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_templates`
--

DROP TABLE IF EXISTS `role_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_templates` (
  `template_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `template_code` varchar(50) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`template_id`),
  UNIQUE KEY `unique_template_code` (`template_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_templates`
--

LOCK TABLES `role_templates` WRITE;
/*!40000 ALTER TABLE `role_templates` DISABLE KEYS */;
INSERT INTO `role_templates` VALUES (1,'OWNER','Owner','Full access to all features',1,1,'2026-07-06 19:19:39','2026-07-06 19:19:39'),(2,'MANAGER','Manager','Manage operations and staff',1,1,'2026-07-06 19:19:39','2026-07-06 19:19:39'),(3,'STAFF','Staff','Basic operational access',1,1,'2026-07-06 19:19:39','2026-07-06 19:19:39'),(4,'VIEWER','Viewer','Read-only access',1,1,'2026-07-06 19:19:39','2026-07-06 19:19:39');
/*!40000 ALTER TABLE `role_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `role_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `role_code` varchar(50) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `idx_roles_tenant_code` (`tenant_id`,`role_code`),
  KEY `idx_roles_tenant_id` (`tenant_id`),
  KEY `idx_roles_status` (`status`),
  CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (2,1,'ADMIN','Administrator','Full system access',0,'ACTIVE','2026-07-02 07:41:03','2026-07-02 07:41:03',NULL),(3,1,'MANAGER','Restaurant Manager','Manages restaurant operations',0,'ACTIVE','2026-07-02 12:05:48','2026-07-02 12:05:48',NULL),(4,1,'WAITER','Waiter','Takes orders and serves customers',0,'ACTIVE','2026-07-02 12:05:48','2026-07-02 12:05:48',NULL),(5,1,'KITCHEN','Kitchen Staff','Prepares food orders',0,'ACTIVE','2026-07-02 12:05:48','2026-07-02 12:05:48',NULL),(6,1,'CASHIER','Cashier','Handles payments and billing',0,'ACTIVE','2026-07-02 12:05:48','2026-07-02 12:05:48',NULL),(7,1,'INVENTORY','Inventory Manager','Manages stock and inventory',0,'ACTIVE','2026-07-02 12:05:48','2026-07-02 12:05:48',NULL),(8,1,'HOST','Host/Hostess','Manages reservations and seating',0,'ACTIVE','2026-07-02 12:05:48','2026-07-02 12:05:48',NULL),(9,1,'KASIR','Kasir','Cashier - handle sales and payments',0,'ACTIVE','2026-07-05 19:50:30','2026-07-05 19:50:30',NULL),(10,1,'KOKI','Koki','Chef - kitchen operations',0,'ACTIVE','2026-07-05 19:50:30','2026-07-05 19:50:30',NULL),(11,1,'STOK','Stok','Inventory Manager',0,'ACTIVE','2026-07-05 19:50:30','2026-07-05 19:50:30',NULL),(12,1,'BARTENDER','Bartender','Bartender - bar service',0,'ACTIVE','2026-07-05 19:50:30','2026-07-05 19:50:30',NULL),(13,1,'BARISTA','Barista','Barista - coffee preparation',0,'ACTIVE','2026-07-05 19:50:30','2026-07-05 19:50:30',NULL),(14,1,'SOMMELIER','Sommelier','Sommelier - wine service (fine dining)',0,'ACTIVE','2026-07-05 19:50:30','2026-07-05 19:50:30',NULL),(15,1,'CONSUMER','Consumer','Restaurant customer/consumer',0,'ACTIVE','2026-07-05 21:23:02','2026-07-05 21:23:02',NULL),(16,1,'ACCOUNTANT','Accountant/Finance Manager','Manages accounting and financial operations',0,'ACTIVE','2026-07-06 17:56:55','2026-07-06 17:56:55',NULL),(17,1,'HOME_OWNER','Home Owner','Owner of home-based business',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(18,1,'HOME_STAFF','Home Staff','Staff for home business',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(19,2,'KITCHEN_OWNER','Kitchen Owner','Owner of kitchen delivery',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(20,2,'KITCHEN_STAFF','Kitchen Staff','Kitchen staff',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(21,3,'RESTO_OWNER','Restaurant Owner','Restaurant owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(22,3,'RESTO_MANAGER','Restaurant Manager','Restaurant manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(23,3,'RESTO_WAITER','Waiter','Waiter staff',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(24,3,'RESTO_KITCHEN','Kitchen Staff','Kitchen staff',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(25,3,'RESTO_CASHIER','Cashier','Cashier',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(26,4,'CAFE_OWNER','Cafe Owner','Cafe owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(27,4,'CAFE_BARISTA','Barista','Barista',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(28,5,'WARUNG_OWNER','Warung Owner','Warung owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(29,5,'WARUNG_HELPER','Warung Helper','Warung helper',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(30,3,'CHAIN_OWNER','Chain Owner','Chain business owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(31,3,'AREA_MANAGER','Area Manager','Area manager for multiple branches',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(32,3,'BRANCH_MANAGER','Branch Manager','Branch manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(33,3,'CHAIN_STAFF','Chain Staff','Regular staff',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(34,5,'CORP_CEO','CEO','Chief Executive Officer',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(35,5,'CORP_COO','COO','Chief Operating Officer',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(36,5,'CORP_CFO','CFO','Chief Financial Officer',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(37,5,'REGIONAL_DIRECTOR','Regional Director','Regional director',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(38,5,'BRANCH_MANAGER','Branch Manager','Branch manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(39,5,'CORP_STAFF','Corporate Staff','Corporate staff',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(40,7,'GLOBAL_CEO','Global CEO','Global Chief Executive',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(41,7,'GLOBAL_COO','Global COO','Global Chief Operating',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(42,7,'COUNTRY_MANAGER','Country Manager','Country manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(43,7,'REGIONAL_MANAGER','Regional Manager','Regional manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(44,7,'INTL_STAFF','International Staff','International staff',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(45,11,'TRUCK_OWNER','Truck Owner','Food truck owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(46,11,'TRUCK_DRIVER','Truck Driver','Truck driver',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(47,11,'TRUCK_COOK','Truck Cook','Truck cook',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(48,12,'VENDOR_OWNER','Vendor Owner','Street vendor owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(49,12,'VENDOR_HELPER','Vendor Helper','Vendor helper',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(50,13,'STALL_OWNER','Stall Owner','Stall owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(51,13,'STALL_ATTENDANT','Stall Attendant','Stall attendant',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(52,14,'KIOSK_OWNER','Kiosk Owner','Kiosk owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(53,14,'KIOSK_STAFF','Kiosk Staff','Kiosk staff',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(54,15,'CAFE_MANAGER','Cafe Manager','Cafe manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(55,15,'CAFE_BARISTA','Barista','Barista',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(56,15,'CAFE_SERVER','Server','Server',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(57,16,'COFFEE_OWNER','Coffee Owner','Coffee house owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(58,16,'COFFEE_BARISTA','Coffee Barista','Coffee barista',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(59,17,'FINE_OWNER','Fine Dining Owner','Fine dining owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(60,17,'FINE_MANAGER','Fine Dining Manager','Fine dining manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(61,17,'FINE_WAITER','Fine Dining Waiter','Fine dining waiter',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(62,17,'FINE_CHEF','Executive Chef','Executive chef',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(63,18,'CASUAL_OWNER','Casual Owner','Casual dining owner',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(64,18,'CASUAL_MANAGER','Casual Manager','Casual dining manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(65,19,'HOTEL_GM','General Manager','Hotel general manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(66,19,'HOTEL_F&B','F&B Manager','Food and beverage manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(67,19,'HOTEL_STAFF','Hotel Staff','Hotel staff',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(68,20,'RESORT_GM','Resort Manager','Resort manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(69,20,'RESORT_F&B','Resort F&B','Resort F&B manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(70,21,'AIRPORT_MGR','Airport Manager','Airport food manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(71,21,'AIRPORT_STAFF','Airport Staff','Airport staff',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(72,22,'MALL_MGR','Mall Manager','Mall food manager',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(73,22,'MALL_STAFF','Mall Staff','Mall staff',0,'ACTIVE','2026-07-06 19:26:18','2026-07-06 19:26:18',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `status` varchar(20) DEFAULT 'scheduled' COMMENT 'scheduled, completed, absent, late',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`schedule_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_date` (`schedule_date`),
  KEY `idx_shift` (`shift_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_audit_logs`
--

DROP TABLE IF EXISTS `security_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_audit_logs` (
  `audit_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_category` enum('AUTHENTICATION','AUTHORIZATION','DATA_ACCESS','DATA_MODIFICATION','SYSTEM','NETWORK') NOT NULL,
  `event_description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `request_url` text DEFAULT NULL,
  `request_params` text DEFAULT NULL,
  `response_status` int(11) DEFAULT NULL,
  `risk_level` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'LOW',
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`audit_id`),
  KEY `idx_audit_tenant` (`tenant_id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_event_type` (`event_type`),
  KEY `idx_audit_category` (`event_category`),
  KEY `idx_audit_risk` (`risk_level`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_audit_logs`
--

LOCK TABLES `security_audit_logs` WRITE;
/*!40000 ALTER TABLE `security_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `setting_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('STRING','NUMBER','BOOLEAN','JSON') DEFAULT 'STRING',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `idx_settings_tenant_key` (`tenant_id`,`setting_key`),
  KEY `idx_settings_tenant_id` (`tenant_id`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shifts`
--

DROP TABLE IF EXISTS `shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shifts` (
  `shift_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `shift_name` varchar(100) NOT NULL,
  `shift_code` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_duration_minutes` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `break_duration` int(11) DEFAULT 0,
  PRIMARY KEY (`shift_id`),
  UNIQUE KEY `unique_tenant_branch_code` (`tenant_id`,`branch_id`,`shift_code`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_branch_id` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shifts`
--

LOCK TABLES `shifts` WRITE;
/*!40000 ALTER TABLE `shifts` DISABLE KEYS */;
INSERT INTO `shifts` VALUES (1,1,2,'Morning Shift','MORNING','08:00:00','16:00:00',30,1,NULL,'2026-07-06 20:02:20','2026-07-06 20:02:20',0),(3,1,2,'Morning Shift','SHIFT_1783368172','08:00:00','16:00:00',30,1,NULL,'2026-07-06 20:02:52','2026-07-06 20:02:52',0);
/*!40000 ALTER TABLE `shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sla_monitoring`
--

DROP TABLE IF EXISTS `sla_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sla_monitoring` (
  `sla_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `target_value` decimal(10,2) DEFAULT NULL,
  `current_value` decimal(10,2) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `measurement_period` enum('HOURLY','DAILY','WEEKLY','MONTHLY') DEFAULT 'DAILY',
  `status` enum('COMPLIANT','WARNING','BREACH') DEFAULT 'COMPLIANT',
  `last_measured_at` timestamp NULL DEFAULT NULL,
  `breach_count` int(11) DEFAULT 0,
  `breach_threshold` int(11) DEFAULT 3,
  `alert_recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`alert_recipients`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`sla_id`),
  KEY `idx_sla_tenant` (`tenant_id`),
  KEY `idx_sla_service` (`service_name`),
  KEY `idx_sla_status` (`status`),
  CONSTRAINT `fk_sla_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sla_monitoring`
--

LOCK TABLES `sla_monitoring` WRITE;
/*!40000 ALTER TABLE `sla_monitoring` DISABLE KEYS */;
/*!40000 ALTER TABLE `sla_monitoring` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `special_schedules`
--

DROP TABLE IF EXISTS `special_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `special_schedules` (
  `schedule_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `schedule_date` date NOT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `is_closed` tinyint(1) DEFAULT 0,
  `reason` varchar(255) DEFAULT NULL,
  `schedule_type` enum('HOLIDAY','EVENT','MAINTENANCE','SPECIAL') DEFAULT 'SPECIAL',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`schedule_id`),
  UNIQUE KEY `unique_tenant_branch_date` (`tenant_id`,`branch_id`,`schedule_date`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_branch_id` (`branch_id`),
  KEY `idx_schedule_date` (`schedule_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `special_schedules`
--

LOCK TABLES `special_schedules` WRITE;
/*!40000 ALTER TABLE `special_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `special_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `split_bill_items`
--

DROP TABLE IF EXISTS `split_bill_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `split_bill_items` (
  `split_bill_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `split_bill_id` bigint(20) unsigned NOT NULL,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`split_bill_item_id`),
  KEY `idx_split_bill_items_split_bill_id` (`split_bill_id`),
  KEY `idx_split_bill_items_order_item_id` (`order_item_id`),
  CONSTRAINT `split_bill_items_ibfk_1` FOREIGN KEY (`split_bill_id`) REFERENCES `split_bills` (`split_bill_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `split_bill_items_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `split_bill_items`
--

LOCK TABLES `split_bill_items` WRITE;
/*!40000 ALTER TABLE `split_bill_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `split_bill_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `split_bills`
--

DROP TABLE IF EXISTS `split_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `split_bills` (
  `split_bill_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `split_type` enum('PER_PERSON','PER_ITEM','CUSTOM') NOT NULL,
  `total_splits` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`split_bill_id`),
  KEY `idx_split_bills_order_id` (`order_id`),
  CONSTRAINT `split_bills_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `split_bills`
--

LOCK TABLES `split_bills` WRITE;
/*!40000 ALTER TABLE `split_bills` DISABLE KEYS */;
/*!40000 ALTER TABLE `split_bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_balances`
--

DROP TABLE IF EXISTS `stock_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_balances` (
  `stock_balance_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `inventory_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_transaction_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`stock_balance_id`),
  UNIQUE KEY `uk_stock_balances_branch_inventory` (`branch_id`,`inventory_id`),
  KEY `idx_stock_balances_tenant` (`tenant_id`),
  KEY `idx_stock_balances_branch` (`branch_id`),
  KEY `idx_stock_balances_inventory` (`inventory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_balances`
--

LOCK TABLES `stock_balances` WRITE;
/*!40000 ALTER TABLE `stock_balances` DISABLE KEYS */;
INSERT INTO `stock_balances` VALUES (1,1,2,1,-0.80,'2026-07-02 12:33:35','2026-07-02 12:33:10','2026-07-02 12:33:35');
/*!40000 ALTER TABLE `stock_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transactions`
--

DROP TABLE IF EXISTS `stock_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_transactions` (
  `stock_transaction_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `transaction_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`stock_transaction_id`),
  KEY `idx_stock_transactions_tenant_id` (`tenant_id`),
  KEY `idx_stock_transactions_branch_id` (`branch_id`),
  KEY `idx_stock_transactions_product_id` (`product_id`),
  KEY `idx_stock_transactions_type` (`transaction_type`),
  KEY `idx_stock_transactions_created_at` (`created_at`),
  CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stock_transactions_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stock_transactions_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transactions`
--

LOCK TABLES `stock_transactions` WRITE;
/*!40000 ALTER TABLE `stock_transactions` DISABLE KEYS */;
INSERT INTO `stock_transactions` VALUES (1,1,2,1,'OUT',0.40,'kg','ORDER',8,NULL,'2026-07-02 12:33:35');
/*!40000 ALTER TABLE `stock_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_payments`
--

DROP TABLE IF EXISTS `subscription_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_payments` (
  `payment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint(20) unsigned NOT NULL,
  `payment_code` varchar(50) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `payment_method` enum('CREDIT_CARD','BANK_TRANSFER','E_WALLET','QRIS','CRYPTO') NOT NULL,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `payment_status` enum('PENDING','PROCESSING','COMPLETED','FAILED','REFUNDED','PARTIALLY_REFUNDED') DEFAULT 'PENDING',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `billing_period_start` date DEFAULT NULL,
  `billing_period_end` date DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `gateway_response` text DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `refunded_amount` decimal(10,2) DEFAULT 0.00,
  `refund_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `payment_code` (`payment_code`),
  KEY `idx_payment_subscription` (`subscription_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_payment_date` (`payment_date`),
  CONSTRAINT `fk_payment_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `tenant_subscriptions` (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_payments`
--

LOCK TABLES `subscription_payments` WRITE;
/*!40000 ALTER TABLE `subscription_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscription_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_plans`
--

DROP TABLE IF EXISTS `subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_plans` (
  `plan_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plan_code` varchar(50) NOT NULL,
  `plan_name` varchar(150) NOT NULL,
  `plan_tier` enum('HOME_BASED','SMALL_RESTAURANT','REGIONAL_CHAIN','NATIONAL_CORPORATION','INTERNATIONAL_CORPORATION') NOT NULL,
  `business_type` enum('home_based','small_restaurant','regional_chain','national_corporation','international_corporation') NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `billing_cycle` enum('MONTHLY','QUARTERLY','ANNUAL') DEFAULT 'MONTHLY',
  `annual_discount_percentage` decimal(5,2) DEFAULT 0.00,
  `max_locations` int(11) DEFAULT NULL,
  `max_users` int(11) DEFAULT NULL,
  `max_products` int(11) DEFAULT NULL,
  `max_orders_per_month` int(11) DEFAULT NULL,
  `included_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`included_features`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`plan_id`),
  UNIQUE KEY `plan_code` (`plan_code`),
  KEY `idx_plan_tier` (`plan_tier`),
  KEY `idx_plan_business_type` (`business_type`),
  KEY `idx_plan_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_plans`
--

LOCK TABLES `subscription_plans` WRITE;
/*!40000 ALTER TABLE `subscription_plans` DISABLE KEYS */;
INSERT INTO `subscription_plans` VALUES (1,'HB_FREE','Home Based Free','HOME_BASED','home_based','Free plan for home-based businesses',0.00,'IDR','MONTHLY',0.00,1,1,10,100,NULL,1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(2,'HB_BASIC','Home Based Basic','HOME_BASED','home_based','Basic plan for home-based businesses',29.00,'IDR','MONTHLY',0.00,1,2,50,500,NULL,1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(3,'SR_STARTER','Small Restaurant Starter','SMALL_RESTAURANT','small_restaurant','Starter plan for small restaurants',49.00,'IDR','MONTHLY',0.00,1,3,100,1000,NULL,1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(4,'SR_STANDARD','Small Restaurant Standard','SMALL_RESTAURANT','small_restaurant','Standard plan for small restaurants',99.00,'IDR','MONTHLY',0.00,1,5,200,5000,NULL,1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(5,'SR_PROFESSIONAL','Small Restaurant Professional','SMALL_RESTAURANT','small_restaurant','Professional plan for small restaurants',249.00,'IDR','MONTHLY',0.00,2,10,500,10000,NULL,1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(6,'RC_GROWTH','Regional Chain Growth','REGIONAL_CHAIN','regional_chain','Growth plan for regional chains',149.00,'IDR','MONTHLY',0.00,5,20,1000,50000,NULL,1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(7,'RC_SCALE','Regional Chain Scale','REGIONAL_CHAIN','regional_chain','Scale plan for regional chains',349.00,'IDR','MONTHLY',0.00,10,50,2000,100000,NULL,1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(8,'NC_ENTERPRISE','National Corporation Enterprise','NATIONAL_CORPORATION','national_corporation','Enterprise plan for national corporations',499.00,'IDR','MONTHLY',0.00,25,100,5000,250000,NULL,1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(9,'NC_PREMIUM','National Corporation Premium','NATIONAL_CORPORATION','national_corporation','Premium plan for national corporations',999.00,'IDR','MONTHLY',0.00,50,200,10000,500000,NULL,1,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(10,'IC_GLOBAL','International Corporation Global','INTERNATIONAL_CORPORATION','international_corporation','Global plan for international corporations',1499.00,'IDR','MONTHLY',0.00,100,500,20000,1000000,NULL,1,'2026-07-05 03:54:19','2026-07-05 03:54:19');
/*!40000 ALTER TABLE `subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_ad_placements`
--

DROP TABLE IF EXISTS `supplier_ad_placements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_ad_placements` (
  `placement_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `placement_type` enum('BANNER','SPONSORED_PRODUCT','FEATURED_SUPPLIER') NOT NULL,
  `placement_name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `status` enum('DRAFT','PENDING_APPROVAL','ACTIVE','PAUSED','EXPIRED','REJECTED') DEFAULT 'DRAFT',
  `approval_status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `actual_spend` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`placement_id`),
  KEY `fk_placement_campaign` (`campaign_id`),
  KEY `idx_placement_supplier` (`supplier_id`),
  KEY `idx_placement_type` (`placement_type`),
  KEY `idx_placement_status` (`status`),
  CONSTRAINT `fk_placement_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `ad_campaigns` (`campaign_id`),
  CONSTRAINT `fk_placement_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_ad_placements`
--

LOCK TABLES `supplier_ad_placements` WRITE;
/*!40000 ALTER TABLE `supplier_ad_placements` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_ad_placements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_contracts`
--

DROP TABLE IF EXISTS `supplier_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_contracts` (
  `contract_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `contract_number` varchar(50) NOT NULL,
  `contract_type` enum('PURCHASE','SERVICE','PARTNERSHIP') DEFAULT 'PURCHASE',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 0,
  `payment_terms` varchar(50) DEFAULT NULL,
  `delivery_terms` text DEFAULT NULL,
  `quality_standards` text DEFAULT NULL,
  `penalty_clauses` text DEFAULT NULL,
  `contract_value` decimal(15,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `status` enum('DRAFT','ACTIVE','EXPIRED','TERMINATED','SUSPENDED') DEFAULT 'DRAFT',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`contract_id`),
  UNIQUE KEY `contract_number` (`contract_number`),
  KEY `idx_contract_tenant` (`tenant_id`),
  KEY `idx_contract_supplier` (`supplier_id`),
  KEY `idx_contract_status` (`status`),
  KEY `idx_contract_dates` (`start_date`,`end_date`),
  CONSTRAINT `fk_contract_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  CONSTRAINT `fk_contract_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_contracts`
--

LOCK TABLES `supplier_contracts` WRITE;
/*!40000 ALTER TABLE `supplier_contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_products`
--

DROP TABLE IF EXISTS `supplier_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_products` (
  `supplier_product_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `supplier_sku` varchar(50) DEFAULT NULL,
  `supplier_price` decimal(10,2) DEFAULT NULL,
  `supplier_unit` varchar(20) DEFAULT NULL,
  `minimum_order_quantity` decimal(10,2) DEFAULT NULL,
  `lead_time_days` int(11) DEFAULT 7,
  `is_preferred` tinyint(1) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`supplier_product_id`),
  UNIQUE KEY `uk_supplier_product` (`supplier_id`,`product_id`),
  KEY `idx_sp_tenant` (`tenant_id`),
  KEY `idx_sp_supplier` (`supplier_id`),
  KEY `idx_sp_product` (`product_id`),
  CONSTRAINT `fk_sp_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `fk_sp_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  CONSTRAINT `fk_sp_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_products`
--

LOCK TABLES `supplier_products` WRITE;
/*!40000 ALTER TABLE `supplier_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `supplier_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `supplier_code` varchar(50) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'Indonesia',
  `tax_number` varchar(100) DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT NULL,
  `lead_time_days` int(11) DEFAULT 7,
  `minimum_order_quantity` decimal(10,2) DEFAULT 0.00,
  `rating` decimal(3,2) DEFAULT 0.00,
  `status` enum('ACTIVE','INACTIVE','BLOCKED') DEFAULT 'ACTIVE',
  `halal_certified` tinyint(1) DEFAULT 0,
  `halal_certification_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`supplier_id`),
  UNIQUE KEY `supplier_code` (`supplier_code`),
  KEY `idx_supplier_tenant` (`tenant_id`),
  KEY `idx_supplier_status` (`status`),
  KEY `idx_supplier_halal` (`halal_certified`),
  CONSTRAINT `fk_supplier_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,1,'SUPP001','Test Supplier 1',NULL,'08123456791','supplier1@test.com','Surabaya',NULL,NULL,'Indonesia',NULL,NULL,7,0.00,0.00,'ACTIVE',0,NULL,NULL,NULL,'2026-07-06 18:39:39',NULL,'2026-07-06 18:39:39',NULL),(2,1,'SUPP002','Test Supplier 2',NULL,'08123456792','supplier2@test.com','Medan',NULL,NULL,'Indonesia',NULL,NULL,7,0.00,0.00,'ACTIVE',0,NULL,NULL,NULL,'2026-07-06 18:39:39',NULL,'2026-07-06 18:39:39',NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_health_checks`
--

DROP TABLE IF EXISTS `system_health_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_health_checks` (
  `check_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `check_name` varchar(100) NOT NULL,
  `check_type` enum('DATABASE','API','STORAGE','SECURITY','PERFORMANCE','BACKUP','NETWORK','MEMORY','CPU','DISK','SSL','SERVICE') NOT NULL,
  `check_endpoint` varchar(255) DEFAULT NULL,
  `expected_result` text DEFAULT NULL,
  `check_interval_minutes` int(11) DEFAULT 5,
  `status` enum('HEALTHY','WARNING','CRITICAL','UNKNOWN') DEFAULT 'UNKNOWN',
  `last_check_at` timestamp NULL DEFAULT NULL,
  `last_check_result` text DEFAULT NULL,
  `last_check_duration_ms` int(11) DEFAULT NULL,
  `failure_count` int(11) DEFAULT 0,
  `alert_threshold` int(11) DEFAULT 3,
  `is_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`check_id`),
  KEY `idx_check_type` (`check_type`),
  KEY `idx_check_status` (`status`),
  KEY `idx_check_enabled` (`is_enabled`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_health_checks`
--

LOCK TABLES `system_health_checks` WRITE;
/*!40000 ALTER TABLE `system_health_checks` DISABLE KEYS */;
INSERT INTO `system_health_checks` VALUES (1,'Database Connectivity','DATABASE','SELECT 1','1',5,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(2,'Database Response Time','DATABASE','SELECT 1','< 100ms',5,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(3,'API Response Time','API','/api/health','200 OK',5,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(4,'Storage Available','STORAGE','/api/storage/health','> 10GB',10,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(5,'SSL Certificate','SSL','api.example.com','Valid',60,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(6,'Memory Usage','MEMORY','/api/system/memory','< 80%',5,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(7,'CPU Usage','CPU','/api/system/cpu','< 80%',5,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(8,'Disk Usage','DISK','/api/system/disk','< 80%',10,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(9,'Network Latency','NETWORK','api.example.com','< 50ms',5,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(10,'Backup Status','BACKUP','/api/backup/status','SUCCESS',60,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(11,'Service Uptime','SERVICE','/api/service/status','RUNNING',5,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06'),(12,'Security Headers','SECURITY','/api/security/headers','Present',60,'UNKNOWN',NULL,NULL,NULL,0,3,1,'2026-07-05 03:53:06','2026-07-05 03:53:06');
/*!40000 ALTER TABLE `system_health_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tables`
--

DROP TABLE IF EXISTS `tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tables` (
  `table_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `table_number` varchar(20) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `capacity` int(11) DEFAULT 4,
  `area` varchar(50) DEFAULT NULL,
  `status` enum('AVAILABLE','OCCUPIED','RESERVED','CLEANING') DEFAULT 'AVAILABLE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`table_id`),
  UNIQUE KEY `idx_tables_branch_number` (`branch_id`,`table_number`),
  KEY `idx_tables_tenant_id` (`tenant_id`),
  KEY `idx_tables_branch_id` (`branch_id`),
  KEY `idx_tables_status` (`status`),
  CONSTRAINT `tables_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tables_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables`
--

LOCK TABLES `tables` WRITE;
/*!40000 ALTER TABLE `tables` DISABLE KEYS */;
INSERT INTO `tables` VALUES (1,1,2,'T1',NULL,4,NULL,'AVAILABLE','2026-07-02 12:12:19','2026-07-02 12:12:19',NULL),(2,1,2,'T2',NULL,4,NULL,'AVAILABLE','2026-07-02 12:12:19','2026-07-02 12:12:19',NULL),(3,1,2,'T3',NULL,6,NULL,'AVAILABLE','2026-07-02 12:12:19','2026-07-02 12:12:19',NULL),(4,1,2,'T4',NULL,2,NULL,'AVAILABLE','2026-07-02 12:12:19','2026-07-02 12:12:19',NULL),(5,1,2,'T5',NULL,8,NULL,'AVAILABLE','2026-07-02 12:12:19','2026-07-02 12:12:19',NULL);
/*!40000 ALTER TABLE `tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_add_ons`
--

DROP TABLE IF EXISTS `tenant_add_ons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_add_ons` (
  `tenant_add_on_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `add_on_id` bigint(20) unsigned NOT NULL,
  `subscription_start_date` date NOT NULL,
  `subscription_end_date` date DEFAULT NULL,
  `status` enum('ACTIVE','SUSPENDED','CANCELLED','EXPIRED') DEFAULT 'ACTIVE',
  `auto_renew` tinyint(1) DEFAULT 1,
  `price_paid` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`tenant_add_on_id`),
  KEY `idx_tao_tenant` (`tenant_id`),
  KEY `idx_tao_add_on` (`add_on_id`),
  KEY `idx_tao_status` (`status`),
  CONSTRAINT `fk_tao_add_on` FOREIGN KEY (`add_on_id`) REFERENCES `add_on_services` (`add_on_id`),
  CONSTRAINT `fk_tao_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_add_ons`
--

LOCK TABLES `tenant_add_ons` WRITE;
/*!40000 ALTER TABLE `tenant_add_ons` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_add_ons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_configurations`
--

DROP TABLE IF EXISTS `tenant_configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_configurations` (
  `config_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `business_type` enum('home_based','small_restaurant','regional_chain','national_corporation','international_corporation') DEFAULT 'small_restaurant',
  `physical_presence` enum('no_building','home_kitchen','food_truck','stall','cafe','restaurant','hotel','international_facility') DEFAULT 'restaurant',
  `cuisine_type` enum('traditional','international','fusion') DEFAULT 'traditional',
  `halal_type` enum('halal_only','non_halal','mixed') DEFAULT 'halal_only',
  `target_market` enum('mass_market','niche','premium','luxury') DEFAULT 'mass_market',
  `menu_complexity` enum('single_item','limited','moderate','extensive') DEFAULT 'moderate',
  `product_mix` enum('food_only','beverage_only','food_beverage','food_non_food') DEFAULT 'food_beverage',
  `operating_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`operating_hours`)),
  `delivery_zones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`delivery_zones`)),
  `service_areas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_areas`)),
  `onboarding_template` varchar(50) DEFAULT NULL,
  `onboarding_completed_at` timestamp NULL DEFAULT NULL,
  `enabled_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`enabled_features`)),
  `disabled_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`disabled_features`)),
  `custom_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_settings`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `tenant_id` (`tenant_id`),
  KEY `idx_business_type` (`business_type`),
  KEY `idx_physical_presence` (`physical_presence`),
  KEY `idx_cuisine_type` (`cuisine_type`),
  KEY `idx_halal_type` (`halal_type`),
  CONSTRAINT `fk_config_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_configurations`
--

LOCK TABLES `tenant_configurations` WRITE;
/*!40000 ALTER TABLE `tenant_configurations` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_configurations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_feature_modules`
--

DROP TABLE IF EXISTS `tenant_feature_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_feature_modules` (
  `tenant_module_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `enabled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tenant_module_id`),
  UNIQUE KEY `uk_tenant_module` (`tenant_id`,`module_id`),
  KEY `idx_tfm_tenant` (`tenant_id`),
  KEY `idx_tfm_module` (`module_id`),
  CONSTRAINT `fk_tfm_module` FOREIGN KEY (`module_id`) REFERENCES `feature_modules` (`module_id`),
  CONSTRAINT `fk_tfm_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_feature_modules`
--

LOCK TABLES `tenant_feature_modules` WRITE;
/*!40000 ALTER TABLE `tenant_feature_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_feature_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_subscriptions`
--

DROP TABLE IF EXISTS `tenant_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_subscriptions` (
  `subscription_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `plan_id` bigint(20) unsigned NOT NULL,
  `subscription_code` varchar(50) NOT NULL,
  `subscription_start_date` date NOT NULL,
  `subscription_end_date` date DEFAULT NULL,
  `billing_cycle` enum('MONTHLY','QUARTERLY','ANNUAL') DEFAULT 'MONTHLY',
  `status` enum('TRIAL','ACTIVE','SUSPENDED','CANCELLED','EXPIRED') DEFAULT 'TRIAL',
  `trial_end_date` date DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `current_locations` int(11) DEFAULT 1,
  `current_users` int(11) DEFAULT 1,
  `current_products` int(11) DEFAULT 0,
  `current_orders_per_month` int(11) DEFAULT 0,
  `base_price` decimal(10,2) DEFAULT NULL,
  `applied_discount` decimal(10,2) DEFAULT 0.00,
  `final_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `next_billing_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`subscription_id`),
  UNIQUE KEY `subscription_code` (`subscription_code`),
  KEY `idx_sub_tenant` (`tenant_id`),
  KEY `idx_sub_plan` (`plan_id`),
  KEY `idx_sub_status` (`status`),
  KEY `idx_sub_next_billing` (`next_billing_date`),
  CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`plan_id`),
  CONSTRAINT `fk_sub_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_subscriptions`
--

LOCK TABLES `tenant_subscriptions` WRITE;
/*!40000 ALTER TABLE `tenant_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `tenant_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_code` varchar(50) NOT NULL,
  `tenant_name` varchar(150) NOT NULL,
  `business_type` varchar(50) DEFAULT 'RESTAURANT',
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_single_member` tinyint(1) DEFAULT 0 COMMENT 'Tenant has only one member (owner)',
  PRIMARY KEY (`tenant_id`),
  UNIQUE KEY `tenant_code` (`tenant_code`),
  KEY `idx_tenants_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
INSERT INTO `tenants` VALUES (1,'DEFAULT','Default Tenant','RESTAURANT','ACTIVE','2026-07-02 07:41:03','2026-07-02 07:41:03',NULL,0),(2,'HOME_CAFE','Home Cafe Business','home_based','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(3,'HOME_KITCHEN','Home Kitchen Delivery','home_based','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(4,'SMALL_RESTO','Small Family Restaurant','small_restaurant','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(5,'CAFE_SHOP','Coffee Shop','small_restaurant','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(6,'WARUNG_MAKAN','Warung Makan','small_restaurant','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(7,'REGIONAL_CHAIN','Regional Restaurant Chain','regional_chain','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(8,'CITY_FOOD','City Food Chain','regional_chain','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(9,'NATIONAL_CORP','National Restaurant Corp','national_corporation','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(10,'FAST_FOOD_NAT','National Fast Food Chain','national_corporation','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(11,'INTL_CORP','International Restaurant Corp','international_corporation','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(12,'GLOBAL_CHAIN','Global Restaurant Chain','international_corporation','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(13,'FOOD_TRUCK','Mobile Food Truck','food_truck','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(14,'STREET_FOOD','Street Food Vendor','food_truck','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(15,'FOOD_STALL','Food Stall','stall','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(16,'KIOSK_FOOD','Food Kiosk','stall','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(17,'URBAN_CAFE','Urban Cafe','cafe','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(18,'COFFEE_HOUSE','Coffee House','cafe','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(19,'FINE_DINING','Fine Dining Restaurant','restaurant','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(20,'CASUAL_DINING','Casual Dining Restaurant','restaurant','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(21,'HOTEL_RESTO','Hotel Restaurant','hotel','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(22,'RESORT_DINING','Resort Dining','hotel','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(23,'AIRPORT_FOOD','Airport Food Court','international_facility','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0),(24,'MALL_FOOD','Mall Food Court','international_facility','ACTIVE','2026-07-06 19:23:39','2026-07-06 19:23:39',NULL,0);
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tips`
--

DROP TABLE IF EXISTS `tips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tips` (
  `tip_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `tip_date` date NOT NULL,
  `tip_amount` decimal(15,2) NOT NULL,
  `tip_type` varchar(20) DEFAULT 'cash' COMMENT 'cash, card, digital',
  `payment_method` varchar(50) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tip_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_date` (`tip_date`),
  KEY `idx_order` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tips`
--

LOCK TABLES `tips` WRITE;
/*!40000 ALTER TABLE `tips` DISABLE KEYS */;
INSERT INTO `tips` VALUES (1,1,NULL,2,NULL,'2026-07-07',50.00,'cash','cash',NULL,NULL,'2026-07-06 20:00:39'),(2,1,NULL,2,NULL,'2026-07-07',50.00,'cash','cash',NULL,NULL,'2026-07-06 20:01:59'),(3,1,NULL,2,NULL,'2026-07-07',50.00,'cash','cash',NULL,NULL,'2026-07-06 20:02:20'),(4,1,NULL,2,NULL,'2026-07-07',50.00,'cash','cash',NULL,NULL,'2026-07-06 20:02:36'),(5,1,NULL,2,NULL,'2026-07-07',50.00,'cash','cash',NULL,NULL,'2026-07-06 20:02:52');
/*!40000 ALTER TABLE `tips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_fees`
--

DROP TABLE IF EXISTS `transaction_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_fees` (
  `fee_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fee_code` varchar(50) NOT NULL,
  `fee_name` varchar(150) NOT NULL,
  `fee_type` enum('PAYMENT_PROCESSING','MARKETPLACE','DELIVERY','ADDITIONAL') NOT NULL,
  `fee_percentage` decimal(5,4) DEFAULT NULL,
  `fixed_fee` decimal(10,2) DEFAULT NULL,
  `fee_description` text DEFAULT NULL,
  `applies_to` enum('ALL','SPECIFIC_TIER','SPECIFIC_REGION') DEFAULT 'ALL',
  `tier_applicability` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tier_applicability`)),
  `region_applicability` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`region_applicability`)),
  `is_active` tinyint(1) DEFAULT 1,
  `effective_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`fee_id`),
  UNIQUE KEY `fee_code` (`fee_code`),
  KEY `idx_fee_type` (`fee_type`),
  KEY `idx_fee_active` (`is_active`),
  KEY `idx_fee_dates` (`effective_date`,`expiry_date`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_fees`
--

LOCK TABLES `transaction_fees` WRITE;
/*!40000 ALTER TABLE `transaction_fees` DISABLE KEYS */;
INSERT INTO `transaction_fees` VALUES (1,'PAYMENT_PROCESSING','Payment Processing Fee','PAYMENT_PROCESSING',0.0150,0.00,'1.5% payment processing fee for all transactions','ALL',NULL,NULL,1,'2026-07-05',NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(2,'MARKETPLACE_SUPPLIER','Marketplace Supplier Fee','MARKETPLACE',0.0300,0.00,'3% marketplace fee for supplier transactions','ALL',NULL,NULL,1,'2026-07-05',NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(3,'MARKETPLACE_STAFF','Marketplace Staff Fee','MARKETPLACE',0.0500,0.00,'5% marketplace fee for staff bookings','ALL',NULL,NULL,1,'2026-07-05',NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19'),(4,'DELIVERY_PLATFORM','Delivery Platform Fee','DELIVERY',0.1000,0.00,'10% delivery platform fee (revenue share)','ALL',NULL,NULL,1,'2026-07-05',NULL,'2026-07-05 03:54:19','2026-07-05 03:54:19');
/*!40000 ALTER TABLE `transaction_fees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_ad_preferences`
--

DROP TABLE IF EXISTS `user_ad_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_ad_preferences` (
  `preference_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `allow_personalized_ads` tinyint(1) DEFAULT 1,
  `allow_targeted_ads` tinyint(1) DEFAULT 1,
  `ad_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ad_categories`)),
  `opted_out_campaigns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`opted_out_campaigns`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`preference_id`),
  KEY `idx_ad_pref_user` (`user_id`),
  CONSTRAINT `fk_ad_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_ad_preferences`
--

LOCK TABLES `user_ad_preferences` WRITE;
/*!40000 ALTER TABLE `user_ad_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_ad_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_feature_modules`
--

DROP TABLE IF EXISTS `user_feature_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_feature_modules` (
  `user_feature_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `enabled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_feature_id`),
  UNIQUE KEY `unique_user_module` (`user_id`,`module_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_module_id` (`module_id`),
  KEY `idx_is_enabled` (`is_enabled`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_feature_modules`
--

LOCK TABLES `user_feature_modules` WRITE;
/*!40000 ALTER TABLE `user_feature_modules` DISABLE KEYS */;
INSERT INTO `user_feature_modules` VALUES (1,2,15,1,'2026-07-06 19:09:16',NULL,'2026-07-06 19:09:16','2026-07-06 19:10:45');
/*!40000 ALTER TABLE `user_feature_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_role_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_role_id`),
  UNIQUE KEY `idx_user_roles_user_role` (`user_id`,`role_id`),
  KEY `idx_user_roles_user_id` (`user_id`),
  KEY `idx_user_roles_role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (2,2,2,'2026-07-02 07:41:04'),(3,3,3,'2026-07-02 12:06:50'),(4,4,4,'2026-07-02 12:06:50'),(5,5,10,'2026-07-02 12:06:50'),(6,6,6,'2026-07-02 12:06:50'),(7,7,7,'2026-07-02 12:06:50'),(8,8,8,'2026-07-02 12:06:50'),(9,9,2,'2026-07-05 17:37:27'),(10,2,8,'2026-07-05 19:50:30'),(11,10,15,'2026-07-05 21:23:18'),(12,11,15,'2026-07-05 21:23:18'),(13,12,17,'2026-07-06 19:26:18'),(14,13,17,'2026-07-06 19:26:18'),(15,12,18,'2026-07-06 19:26:18'),(16,13,18,'2026-07-06 19:26:18'),(20,14,19,'2026-07-06 19:26:18'),(21,15,19,'2026-07-06 19:26:18'),(22,14,20,'2026-07-06 19:26:18'),(23,15,20,'2026-07-06 19:26:18'),(27,20,25,'2026-07-06 19:26:18'),(28,19,25,'2026-07-06 19:26:18'),(29,17,25,'2026-07-06 19:26:18'),(30,16,25,'2026-07-06 19:26:18'),(31,18,25,'2026-07-06 19:26:18'),(32,20,24,'2026-07-06 19:26:18'),(33,19,24,'2026-07-06 19:26:18'),(34,17,24,'2026-07-06 19:26:18'),(35,16,24,'2026-07-06 19:26:18'),(36,18,24,'2026-07-06 19:26:18'),(37,20,22,'2026-07-06 19:26:18'),(38,19,22,'2026-07-06 19:26:18'),(39,17,22,'2026-07-06 19:26:18'),(40,16,22,'2026-07-06 19:26:18'),(41,18,22,'2026-07-06 19:26:18'),(42,20,21,'2026-07-06 19:26:18'),(43,19,21,'2026-07-06 19:26:18'),(44,17,21,'2026-07-06 19:26:18'),(45,16,21,'2026-07-06 19:26:18'),(46,18,21,'2026-07-06 19:26:18'),(47,20,23,'2026-07-06 19:26:18'),(48,19,23,'2026-07-06 19:26:18'),(49,17,23,'2026-07-06 19:26:18'),(50,16,23,'2026-07-06 19:26:18'),(51,18,23,'2026-07-06 19:26:18');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE','SUSPENDED') DEFAULT 'ACTIVE',
  `is_platform_owner` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `idx_users_tenant_username` (`tenant_id`,`username`),
  UNIQUE KEY `idx_users_tenant_email` (`tenant_id`,`email`),
  KEY `idx_users_tenant_id` (`tenant_id`),
  KEY `idx_users_branch_id` (`branch_id`),
  KEY `idx_users_status` (`status`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,1,2,'admin','admin@restaurant.com','$2y$10$kQgQqm06RpPVrBqACkLNneJkQVRdb1SOMgKz.1pnwnFHi/6yErbHm','System Administrator',NULL,'ACTIVE',0,'2026-07-02 07:41:03','2026-07-02 07:41:03',NULL),(3,1,2,'manager','manager@restaurant.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'ACTIVE',0,'2026-07-02 12:06:36','2026-07-02 12:06:36',NULL),(4,1,2,'waiter','waiter@restaurant.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'ACTIVE',0,'2026-07-02 12:06:36','2026-07-02 12:06:36',NULL),(5,1,2,'kitchen','kitchen@restaurant.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'ACTIVE',0,'2026-07-02 12:06:36','2026-07-02 12:06:36',NULL),(6,1,2,'cashier','cashier@restaurant.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'ACTIVE',0,'2026-07-02 12:06:36','2026-07-02 12:06:36',NULL),(7,1,2,'inventory','inventory@restaurant.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'ACTIVE',0,'2026-07-02 12:06:36','2026-07-02 12:06:36',NULL),(8,1,2,'host','host@restaurant.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'ACTIVE',0,'2026-07-02 12:06:36','2026-07-02 12:06:36',NULL),(9,1,NULL,'platform','platform@ebp.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Platform Owner',NULL,'ACTIVE',1,'2026-07-05 17:37:27','2026-07-05 17:37:27',NULL),(10,1,NULL,'consumer1','consumer1@example.com','$2y$10$Nfsq19zHsYXBfcP/ql6v5.gAZUTEk1d.1SkrOJvfWLQLr2z81Zeu.','Budi Santoso','+6281234567890','ACTIVE',0,'2026-07-05 21:21:29','2026-07-05 21:23:44',NULL),(11,1,NULL,'consumer2','consumer2@example.com','$2y$10$Nfsq19zHsYXBfcP/ql6v5.gAZUTEk1d.1SkrOJvfWLQLr2z81Zeu.','Siti Rahayu','+6289876543210','ACTIVE',0,'2026-07-05 21:21:29','2026-07-05 21:23:44',NULL),(12,1,NULL,'home_owner','home_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Home Owner','081234567890','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(13,1,NULL,'home_staff','home_staff@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Home Staff','081234567891','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(14,2,NULL,'kitchen_owner','kitchen_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Kitchen Owner','081234567892','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(15,2,NULL,'kitchen_staff','kitchen_staff@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Kitchen Staff','081234567893','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(16,3,NULL,'resto_owner','resto_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Restaurant Owner','081234567894','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(17,3,NULL,'resto_manager','resto_manager@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Restaurant Manager','081234567895','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(18,3,NULL,'resto_waiter','resto_waiter@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Waiter Staff','081234567896','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(19,3,NULL,'resto_kitchen','resto_kitchen@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Kitchen Staff','081234567897','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(20,3,NULL,'resto_cashier','resto_cashier@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Cashier','081234567898','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(21,4,NULL,'cafe_owner','cafe_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Cafe Owner','081234567899','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(22,4,NULL,'cafe_barista','cafe_barista@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Barista','081234567900','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(23,5,NULL,'warung_owner','warung_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Warung Owner','081234567901','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(24,5,NULL,'warung_helper','warung_helper@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Warung Helper','081234567902','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(25,3,NULL,'chain_owner','chain_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Chain Owner','081234567903','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(26,3,NULL,'area_manager','area_manager@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Area Manager','081234567904','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(27,3,NULL,'branch_manager','branch_manager@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Branch Manager','081234567905','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(28,3,NULL,'chain_staff','chain_staff@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Chain Staff','081234567906','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(29,5,NULL,'corp_ceo','ceo@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','CEO','081234567907','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(30,5,NULL,'corp_coo','coo@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','COO','081234567908','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(31,5,NULL,'corp_cfo','cfo@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','CFO','081234567909','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(32,5,NULL,'regional_director','regional_director@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Regional Director','081234567910','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(33,5,NULL,'nat_branch_manager','nat_branch_manager@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Branch Manager','081234567911','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(34,5,NULL,'corp_staff','corp_staff@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Corporate Staff','081234567912','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(35,7,NULL,'global_ceo','global_ceo@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Global CEO','081234567913','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(36,7,NULL,'global_coo','global_coo@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Global COO','081234567914','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(37,7,NULL,'country_manager','country_manager@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Country Manager','081234567915','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(38,7,NULL,'intl_regional_manager','intl_regional_manager@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Regional Manager','081234567916','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(39,7,NULL,'intl_staff','intl_staff@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','International Staff','081234567917','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(40,11,NULL,'truck_owner','truck_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Truck Owner','081234567918','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(41,11,NULL,'truck_driver','truck_driver@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Truck Driver','081234567919','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(42,11,NULL,'truck_cook','truck_cook@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Truck Cook','081234567920','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(43,12,NULL,'vendor_owner','vendor_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Vendor Owner','081234567921','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(44,12,NULL,'vendor_helper','vendor_helper@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Vendor Helper','081234567922','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(45,13,NULL,'stall_owner','stall_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Stall Owner','081234567923','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(46,13,NULL,'stall_attendant','stall_attendant@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Stall Attendant','081234567924','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(47,14,NULL,'kiosk_owner','kiosk_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Kiosk Owner','081234567925','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(48,14,NULL,'kiosk_staff','kiosk_staff@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Kiosk Staff','081234567926','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(49,15,NULL,'cafe_manager','cafe_manager@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Cafe Manager','081234567927','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(50,15,NULL,'cafe_barista','cafe_barista@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Barista','081234567928','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(51,15,NULL,'cafe_server','cafe_server@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Server','081234567929','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(52,16,NULL,'coffee_owner','coffee_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Coffee Owner','081234567930','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(53,16,NULL,'coffee_barista','coffee_barista@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Coffee Barista','081234567931','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(54,17,NULL,'fine_owner','fine_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Fine Dining Owner','081234567932','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(55,17,NULL,'fine_manager','fine_manager@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Fine Dining Manager','081234567933','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(56,17,NULL,'fine_waiter','fine_waiter@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Fine Dining Waiter','081234567934','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(57,17,NULL,'fine_chef','fine_chef@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Executive Chef','081234567935','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(58,18,NULL,'casual_owner','casual_owner@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Casual Dining Owner','081234567936','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(59,18,NULL,'casual_manager','casual_manager@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Casual Dining Manager','081234567937','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(60,19,NULL,'hotel_gm','hotel_gm@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Hotel General Manager','081234567938','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(61,19,NULL,'hotel_fb','hotel_fb@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','F&B Manager','081234567939','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(62,19,NULL,'hotel_staff','hotel_staff@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Hotel Staff','081234567940','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(63,20,NULL,'resort_gm','resort_gm@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Resort Manager','081234567941','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(64,20,NULL,'resort_fb','resort_fb@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Resort F&B Manager','081234567942','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(65,21,NULL,'airport_mgr','airport_mgr@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Airport Manager','081234567943','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(66,21,NULL,'airport_staff','airport_staff@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Airport Staff','081234567944','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(67,22,NULL,'mall_mgr','mall_mgr@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Mall Manager','081234567945','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL),(68,22,NULL,'mall_staff','mall_staff@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Mall Staff','081234567946','ACTIVE',0,'2026-07-06 19:26:18','2026-07-06 19:26:18',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `viral_campaigns`
--

DROP TABLE IF EXISTS `viral_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `viral_campaigns` (
  `campaign_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_code` varchar(50) NOT NULL,
  `campaign_name` varchar(150) NOT NULL,
  `campaign_type` enum('SOCIAL_SHARE','CHALLENGE','CONTEST','GIVEAWAY','REFERRAL_BOOST') NOT NULL,
  `description` text DEFAULT NULL,
  `campaign_start_date` date NOT NULL,
  `campaign_end_date` date DEFAULT NULL,
  `target_audience` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_audience`)),
  `rewards` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rewards`)),
  `rules` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`campaign_id`),
  UNIQUE KEY `campaign_code` (`campaign_code`),
  KEY `idx_campaign_type` (`campaign_type`),
  KEY `idx_campaign_active` (`is_active`),
  KEY `idx_campaign_dates` (`campaign_start_date`,`campaign_end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `viral_campaigns`
--

LOCK TABLES `viral_campaigns` WRITE;
/*!40000 ALTER TABLE `viral_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `viral_campaigns` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-07  3:07:45
