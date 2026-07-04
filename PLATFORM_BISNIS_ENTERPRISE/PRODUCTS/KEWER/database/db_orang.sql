-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_orang
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL COMMENT 'FK to people.id',
  `label` varchar(50) DEFAULT 'rumah' COMMENT 'rumah/kantor/usaha',
  `jenis_alamat_id` int(11) DEFAULT NULL,
  `jenis_properti_id` int(11) DEFAULT NULL,
  `street_address` text DEFAULT NULL COMMENT 'Jalan, nomor rumah',
  `nama_gedung` varchar(100) DEFAULT NULL,
  `house_number` varchar(20) DEFAULT NULL,
  `nomor_unit` varchar(20) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to provinces.id',
  `regency_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to regencies.id',
  `district_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to districts.id',
  `village_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to villages.id',
  `postal_code` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 1,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_person` (`person_id`),
  KEY `idx_province` (`province_id`),
  KEY `idx_regency` (`regency_id`),
  KEY `idx_district` (`district_id`),
  KEY `idx_village` (`village_id`),
  KEY `idx_primary` (`person_id`,`is_primary`),
  KEY `idx_jenis_alamat_id` (`jenis_alamat_id`),
  KEY `idx_jenis_properti_id` (`jenis_properti_id`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `idx_person_village` (`person_id`,`village_id`),
  KEY `idx_is_primary` (`is_primary`),
  CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`jenis_alamat_id`) REFERENCES `ref_jenis_alamat` (`id`) ON DELETE SET NULL,
  CONSTRAINT `addresses_ibfk_2` FOREIGN KEY (`jenis_properti_id`) REFERENCES `ref_jenis_properti` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_addr_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Alamat orang, referensi lokasi ke db_orang.provinces/regencies/districts/villages';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (3,14,'rumah',1,NULL,'Pangururan',NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,1,NULL,'2026-05-02 15:34:24','2026-05-07 16:50:31',NULL),(4,15,'rumah',1,NULL,'Balige',NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,1,NULL,'2026-05-02 15:34:24','2026-05-07 16:50:31',NULL),(5,16,'kantor',2,NULL,'Jl. Sisingamangaraja No.1, Pangururan',NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,1,NULL,'2026-05-02 15:34:24','2026-05-07 16:50:31',NULL),(6,17,'kantor',2,NULL,'Jl. SM Raja No.5, Balige',NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,1,NULL,'2026-05-02 15:34:24','2026-05-07 16:50:31',NULL);
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_relations`
--

DROP TABLE IF EXISTS `family_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `relative_person_id` int(11) NOT NULL,
  `relationship_type_id` int(11) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `relationship_type_id` (`relationship_type_id`),
  KEY `idx_person_id` (`person_id`),
  KEY `idx_relative_person_id` (`relative_person_id`),
  KEY `idx_person_relasi` (`person_id`,`relationship_type_id`),
  CONSTRAINT `family_relations_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `family_relations_ibfk_2` FOREIGN KEY (`relative_person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `family_relations_ibfk_3` FOREIGN KEY (`relationship_type_id`) REFERENCES `ref_jenis_relasi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relasi keluarga per orang';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_relations`
--

LOCK TABLES `family_relations` WRITE;
/*!40000 ALTER TABLE `family_relations` DISABLE KEYS */;
/*!40000 ALTER TABLE `family_relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kewer_user_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to kewer.users.id',
  `kewer_nasabah_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to kewer.nasabah.id',
  `nama` varchar(100) NOT NULL,
  `gelar_id` int(11) DEFAULT NULL,
  `nama_depan` varchar(100) DEFAULT NULL,
  `nama_tengah` varchar(100) DEFAULT NULL,
  `nama_belakang` varchar(100) DEFAULT NULL,
  `nama_lengkap` varchar(255) DEFAULT NULL,
  `ktp` varchar(16) DEFAULT NULL,
  `jenis_identitas_id` int(11) DEFAULT NULL,
  `nomor_identitas` varchar(50) DEFAULT NULL,
  `telp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `jenis_kelamin_id` int(11) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `agama` varchar(20) DEFAULT NULL,
  `agama_id` int(11) DEFAULT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `golongan_darah_id` int(11) DEFAULT NULL,
  `suku_id` int(11) DEFAULT NULL,
  `status_perkawinan_id` int(11) DEFAULT NULL,
  `pekerjaan_id` int(11) DEFAULT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `foto_selfie` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_unique_identitas` (`jenis_identitas_id`,`nomor_identitas`),
  KEY `idx_kewer_user` (`kewer_user_id`),
  KEY `idx_kewer_nasabah` (`kewer_nasabah_id`),
  KEY `idx_ktp` (`ktp`),
  KEY `idx_nama` (`nama`),
  KEY `idx_agama_id` (`agama_id`),
  KEY `idx_jenis_kelamin_id` (`jenis_kelamin_id`),
  KEY `idx_golongan_darah_id` (`golongan_darah_id`),
  KEY `idx_suku_id` (`suku_id`),
  KEY `idx_status_perkawinan_id` (`status_perkawinan_id`),
  KEY `idx_pekerjaan_id` (`pekerjaan_id`),
  KEY `idx_jenis_identitas_id` (`jenis_identitas_id`),
  KEY `idx_nomor_identitas` (`nomor_identitas`),
  KEY `idx_gelar_id` (`gelar_id`),
  KEY `idx_nama_depan` (`nama_depan`),
  KEY `idx_nama_belakang` (`nama_belakang`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `idx_nama_tanggal_lahir` (`nama_depan`,`tanggal_lahir`),
  KEY `idx_fulltext_nama` (`nama`,`nama_depan`,`nama_belakang`),
  CONSTRAINT `people_ibfk_1` FOREIGN KEY (`agama_id`) REFERENCES `ref_agama` (`id`) ON DELETE SET NULL,
  CONSTRAINT `people_ibfk_2` FOREIGN KEY (`jenis_kelamin_id`) REFERENCES `ref_jenis_kelamin` (`id`) ON DELETE SET NULL,
  CONSTRAINT `people_ibfk_3` FOREIGN KEY (`golongan_darah_id`) REFERENCES `ref_golongan_darah` (`id`) ON DELETE SET NULL,
  CONSTRAINT `people_ibfk_4` FOREIGN KEY (`suku_id`) REFERENCES `ref_suku` (`id`) ON DELETE SET NULL,
  CONSTRAINT `people_ibfk_5` FOREIGN KEY (`status_perkawinan_id`) REFERENCES `ref_status_perkawinan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `people_ibfk_6` FOREIGN KEY (`pekerjaan_id`) REFERENCES `ref_pekerjaan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `people_ibfk_7` FOREIGN KEY (`jenis_identitas_id`) REFERENCES `ref_jenis_identitas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `people_ibfk_8` FOREIGN KEY (`gelar_id`) REFERENCES `ref_jenis_gelar` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_tanggal_lahir` CHECK (`tanggal_lahir` is null or `tanggal_lahir` between '1900-01-01' and '2100-12-31'),
  CONSTRAINT `chk_nomor_identitas` CHECK (`nomor_identitas` is null or `nomor_identitas` regexp '^[0-9]{16}$')
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Data orang terhubung ke kewer';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `people`
--

LOCK TABLES `people` WRITE;
/*!40000 ALTER TABLE `people` DISABLE KEYS */;
INSERT INTO `people` VALUES (3,1,NULL,'Patri Sihaloho',NULL,'Patri',NULL,NULL,'Patri Sihaloho',NULL,NULL,NULL,'081234567890','patri@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(4,2,NULL,'Sondang Silaban',NULL,'Sondang',NULL,NULL,'Sondang Silaban',NULL,NULL,NULL,'','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(5,18,NULL,'Roswita Nainggolan',NULL,'Roswita',NULL,NULL,'Roswita Nainggolan',NULL,NULL,NULL,NULL,'mgr_balige@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(6,19,NULL,'Melvina Hutabarat',NULL,'Melvina',NULL,NULL,'Melvina Hutabarat',NULL,NULL,NULL,NULL,'adm_pusat@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(7,20,NULL,'Ruli Sirait',NULL,'Ruli',NULL,NULL,'Ruli Sirait',NULL,NULL,NULL,NULL,'adm_balige@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(8,21,NULL,'Darwin Sinaga',NULL,'Darwin',NULL,NULL,'Darwin Sinaga',NULL,NULL,NULL,NULL,'ptr_pusat@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(9,22,NULL,'Markus Situmorang',NULL,'Markus',NULL,NULL,'Markus Situmorang',NULL,NULL,NULL,NULL,'ptr_balige@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(10,23,NULL,'Susi Aritonang',NULL,'Susi',NULL,NULL,'Susi Aritonang',NULL,NULL,NULL,NULL,'krw_pusat@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(11,24,NULL,'Petrus Hutagalung',NULL,'Petrus',NULL,NULL,'Petrus Hutagalung',NULL,NULL,NULL,NULL,'krw_balige@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(12,26,NULL,'Test Bos Koperasi',NULL,'Test',NULL,'Koperasi','Test Bos Koperasi',NULL,NULL,NULL,'081299990000','test@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(13,27,NULL,'Flow Test Bos',NULL,'Flow',NULL,'Bos','Flow Test Bos',NULL,NULL,NULL,'081288880001','flow@test.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(14,NULL,1,'Budi Siregar',NULL,'Budi',NULL,NULL,'Budi Siregar','1201010101010001',1,'1201010101010001','081234500001',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(15,NULL,2,'Maria Tampubolon',NULL,'Maria',NULL,NULL,'Maria Tampubolon','1201010101010002',1,'1201010101010002','081234500002',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(16,NULL,NULL,'Cabang: Kantor Pusat Pangururan',NULL,'Cabang:',NULL,'Pangururan','Cabang: Kantor Pusat Pangururan',NULL,NULL,NULL,'062163212345','pusat@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(17,NULL,NULL,'Cabang: Cabang Balige',NULL,'Cabang:',NULL,'Balige','Cabang: Cabang Balige',NULL,NULL,NULL,'062163254321','balige@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif','2026-05-02 15:34:24','2026-05-07 16:54:35',NULL),(18,NULL,NULL,'Test User',NULL,'Test','Middle','User','Test User','1234567890123456',1,'1234567890123456',NULL,NULL,'L',1,'1990-01-01','Jakarta','Islam',1,'Programmer',1,1,1,NULL,NULL,NULL,'Test person','aktif','2026-05-07 17:03:11','2026-05-07 17:03:11',NULL);
/*!40000 ALTER TABLE `people` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_people_nama_update
BEFORE INSERT ON people
FOR EACH ROW
BEGIN
    IF NEW.nama_lengkap IS NULL THEN
        SET NEW.nama_lengkap = CONCAT(
            COALESCE((SELECT nama FROM ref_jenis_gelar WHERE id = NEW.gelar_id), ''), 
            ' ', 
            COALESCE(NEW.nama_depan, ''), 
            ' ', 
            COALESCE(NEW.nama_tengah, ''), 
            ' ', 
            COALESCE(NEW.nama_belakang, '')
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_people_nama_update_before
BEFORE UPDATE ON people
FOR EACH ROW
BEGIN
    IF NEW.nama_depan <> OLD.nama_depan OR NEW.nama_tengah <> OLD.nama_tengah OR NEW.nama_belakang <> OLD.nama_belakang OR NEW.gelar_id <> OLD.gelar_id THEN
        SET NEW.nama_lengkap = CONCAT(
            COALESCE((SELECT nama FROM ref_jenis_gelar WHERE id = NEW.gelar_id), ''), 
            ' ', 
            COALESCE(NEW.nama_depan, ''), 
            ' ', 
            COALESCE(NEW.nama_tengah, ''), 
            ' ', 
            COALESCE(NEW.nama_belakang, '')
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `people_audit_log`
--

DROP TABLE IF EXISTS `people_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people_audit_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `action` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `changed_by` (`changed_by`),
  KEY `idx_person_id` (`person_id`),
  KEY `idx_action` (`action`),
  KEY `idx_changed_at` (`changed_at`),
  CONSTRAINT `people_audit_log_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `people_audit_log_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `people` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail perubahan data orang';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `people_audit_log`
--

LOCK TABLES `people_audit_log` WRITE;
/*!40000 ALTER TABLE `people_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `people_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `people_documents`
--

DROP TABLE IF EXISTS `people_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `jenis_identitas_id` int(11) NOT NULL,
  `nomor_dokumen` varchar(50) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `tanggal_ekspedisi` date DEFAULT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `person_id` (`person_id`,`jenis_identitas_id`,`nomor_dokumen`),
  KEY `jenis_identitas_id` (`jenis_identitas_id`),
  KEY `verified_by` (`verified_by`),
  KEY `idx_person_id` (`person_id`),
  KEY `idx_nomor_dokumen` (`nomor_dokumen`),
  KEY `idx_person_jenis` (`person_id`,`jenis_identitas_id`),
  CONSTRAINT `people_documents_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `people_documents_ibfk_2` FOREIGN KEY (`jenis_identitas_id`) REFERENCES `ref_jenis_identitas` (`id`),
  CONSTRAINT `people_documents_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `people` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dokumen identitas per orang';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `people_documents`
--

LOCK TABLES `people_documents` WRITE;
/*!40000 ALTER TABLE `people_documents` DISABLE KEYS */;
INSERT INTO `people_documents` VALUES (1,14,1,'1201010101010001',NULL,NULL,NULL,1,NULL,NULL,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(2,15,1,'1201010101010002',NULL,NULL,NULL,1,NULL,NULL,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(3,18,1,'1234567890123456',NULL,NULL,NULL,1,NULL,NULL,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11');
/*!40000 ALTER TABLE `people_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `people_emails`
--

DROP TABLE IF EXISTS `people_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `jenis_email_id` int(11) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `person_id` (`person_id`,`email`),
  KEY `jenis_email_id` (`jenis_email_id`),
  KEY `idx_person_id` (`person_id`),
  KEY `idx_email` (`email`),
  CONSTRAINT `people_emails_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `people_emails_ibfk_2` FOREIGN KEY (`jenis_email_id`) REFERENCES `ref_jenis_email` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Email per orang';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `people_emails`
--

LOCK TABLES `people_emails` WRITE;
/*!40000 ALTER TABLE `people_emails` DISABLE KEYS */;
INSERT INTO `people_emails` VALUES (1,3,'patri@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(2,4,'',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(3,5,'mgr_balige@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(4,6,'adm_pusat@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(5,7,'adm_balige@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(6,8,'ptr_pusat@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(7,9,'ptr_balige@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(8,10,'krw_pusat@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(9,11,'krw_balige@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(10,12,'test@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(11,13,'flow@test.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(12,16,'pusat@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(13,17,'balige@kewer.co.id',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11');
/*!40000 ALTER TABLE `people_emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `people_phones`
--

DROP TABLE IF EXISTS `people_phones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people_phones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `jenis_telepon_id` int(11) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `person_id` (`person_id`,`phone_number`),
  KEY `jenis_telepon_id` (`jenis_telepon_id`),
  KEY `idx_person_id` (`person_id`),
  KEY `idx_phone_number` (`phone_number`),
  CONSTRAINT `people_phones_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `people_phones_ibfk_2` FOREIGN KEY (`jenis_telepon_id`) REFERENCES `ref_jenis_telepon` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nomor telepon per orang';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `people_phones`
--

LOCK TABLES `people_phones` WRITE;
/*!40000 ALTER TABLE `people_phones` DISABLE KEYS */;
INSERT INTO `people_phones` VALUES (1,3,'081234567890',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(2,4,'',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(3,12,'081299990000',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(4,13,'081288880001',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(5,14,'081234500001',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(6,15,'081234500002',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(7,16,'062163212345',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(8,17,'062163254321',1,1,0,NULL,'2026-05-10 04:23:11','2026-05-10 04:23:11');
/*!40000 ALTER TABLE `people_phones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_agama`
--

DROP TABLE IF EXISTS `ref_agama`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_agama` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi agama resmi di Indonesia';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_agama`
--

LOCK TABLES `ref_agama` WRITE;
/*!40000 ALTER TABLE `ref_agama` DISABLE KEYS */;
INSERT INTO `ref_agama` VALUES (1,'Islam','ISLAM',1,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(2,'Kristen Protestan','KRISTEN_PROTESTAN',2,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(3,'Katolik','KATOLIK',3,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(4,'Hindu','HINDU',4,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(5,'Buddha','BUDDHA',5,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(6,'Konghucu','KONGHUCU',6,1,'2026-05-07 16:48:01','2026-05-07 16:48:01');
/*!40000 ALTER TABLE `ref_agama` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_golongan_darah`
--

DROP TABLE IF EXISTS `ref_golongan_darah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_golongan_darah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(10) NOT NULL,
  `kode` varchar(5) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi golongan darah';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_golongan_darah`
--

LOCK TABLES `ref_golongan_darah` WRITE;
/*!40000 ALTER TABLE `ref_golongan_darah` DISABLE KEYS */;
INSERT INTO `ref_golongan_darah` VALUES (1,'A','A',1,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(2,'B','B',2,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(3,'AB','AB',3,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(4,'O','O',4,1,'2026-05-07 16:48:01','2026-05-07 16:48:01');
/*!40000 ALTER TABLE `ref_golongan_darah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_alamat`
--

DROP TABLE IF EXISTS `ref_jenis_alamat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jenis_alamat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis alamat';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_alamat`
--

LOCK TABLES `ref_jenis_alamat` WRITE;
/*!40000 ALTER TABLE `ref_jenis_alamat` DISABLE KEYS */;
INSERT INTO `ref_jenis_alamat` VALUES (1,'Rumah','RUMAH',1,1,'2026-05-07 16:50:30','2026-05-07 16:50:30'),(2,'Kantor','KANTOR',2,1,'2026-05-07 16:50:30','2026-05-07 16:50:30'),(3,'Kos','KOS',3,1,'2026-05-07 16:50:30','2026-05-07 16:50:30'),(4,'Apartemen','APARTEMEN',4,1,'2026-05-07 16:50:30','2026-05-07 16:50:30'),(5,'Kontrakan','KONTRAKAN',5,1,'2026-05-07 16:50:30','2026-05-07 16:50:30'),(6,'Mess','MESS',6,1,'2026-05-07 16:50:30','2026-05-07 16:50:30'),(7,'Toko','TOKO',7,1,'2026-05-07 16:50:30','2026-05-07 16:50:30'),(8,'Gudang','GUDANG',8,1,'2026-05-07 16:50:30','2026-05-07 16:50:30'),(9,'Ruko','RUKO',9,1,'2026-05-07 16:50:30','2026-05-07 16:50:30'),(10,'Lainnya','LAINNYA',99,1,'2026-05-07 16:50:30','2026-05-07 16:50:30');
/*!40000 ALTER TABLE `ref_jenis_alamat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_email`
--

DROP TABLE IF EXISTS `ref_jenis_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jenis_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis email';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_email`
--

LOCK TABLES `ref_jenis_email` WRITE;
/*!40000 ALTER TABLE `ref_jenis_email` DISABLE KEYS */;
INSERT INTO `ref_jenis_email` VALUES (1,'Personal','PERSONAL',1,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(2,'Kantor','WORK',2,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(3,'Lainnya','OTHER',99,1,'2026-05-10 04:23:11','2026-05-10 04:23:11');
/*!40000 ALTER TABLE `ref_jenis_email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_gelar`
--

DROP TABLE IF EXISTS `ref_jenis_gelar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jenis_gelar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `posisi` enum('depan','belakang') NOT NULL DEFAULT 'depan',
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi gelar';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_gelar`
--

LOCK TABLES `ref_jenis_gelar` WRITE;
/*!40000 ALTER TABLE `ref_jenis_gelar` DISABLE KEYS */;
INSERT INTO `ref_jenis_gelar` VALUES (1,'Dr.','DR','depan',1,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(2,'Ir.','IR','depan',2,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(3,'H.','H','depan',3,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(4,'Drs.','DRS','depan',4,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(5,'S.Kom','SKOM','belakang',5,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(6,'S.E.','SE','belakang',6,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(7,'M.M.','MM','belakang',7,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(8,'M.Si.','MSI','belakang',8,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(9,'Lainnya','OTHER','depan',99,1,'2026-05-10 04:23:11','2026-05-10 04:23:11');
/*!40000 ALTER TABLE `ref_jenis_gelar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_identitas`
--

DROP TABLE IF EXISTS `ref_jenis_identitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jenis_identitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `panjang_nomor` int(11) DEFAULT NULL COMMENT 'Panjang standar nomor identitas',
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis dokumen identitas';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_identitas`
--

LOCK TABLES `ref_jenis_identitas` WRITE;
/*!40000 ALTER TABLE `ref_jenis_identitas` DISABLE KEYS */;
INSERT INTO `ref_jenis_identitas` VALUES (1,'KTP','KTP',16,1,1,'2026-05-07 16:50:31','2026-05-07 16:50:31'),(2,'SIM','SIM',12,2,1,'2026-05-07 16:50:31','2026-05-07 16:50:31'),(3,'Paspor','PASPOR',8,3,1,'2026-05-07 16:50:31','2026-05-07 16:50:31'),(4,'KK','KK',16,4,1,'2026-05-07 16:50:31','2026-05-07 16:50:31'),(5,'NPWP','NPWP',15,5,1,'2026-05-07 16:50:31','2026-05-07 16:50:31'),(6,'KITAS','KITAS',NULL,6,1,'2026-05-07 16:50:31','2026-05-07 16:50:31'),(7,'KITAP','KITAP',NULL,7,1,'2026-05-07 16:50:31','2026-05-07 16:50:31'),(8,'Lainnya','LAINNYA',NULL,99,1,'2026-05-07 16:50:31','2026-05-07 16:50:31');
/*!40000 ALTER TABLE `ref_jenis_identitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_kelamin`
--

DROP TABLE IF EXISTS `ref_jenis_kelamin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jenis_kelamin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(20) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis kelamin';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_kelamin`
--

LOCK TABLES `ref_jenis_kelamin` WRITE;
/*!40000 ALTER TABLE `ref_jenis_kelamin` DISABLE KEYS */;
INSERT INTO `ref_jenis_kelamin` VALUES (1,'Laki-laki','L',1,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(2,'Perempuan','P',2,1,'2026-05-07 16:48:01','2026-05-07 16:48:01');
/*!40000 ALTER TABLE `ref_jenis_kelamin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_properti`
--

DROP TABLE IF EXISTS `ref_jenis_properti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jenis_properti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis properti';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_properti`
--

LOCK TABLES `ref_jenis_properti` WRITE;
/*!40000 ALTER TABLE `ref_jenis_properti` DISABLE KEYS */;
INSERT INTO `ref_jenis_properti` VALUES (1,'Rumah Tinggal','RUMAH_TINGGAL',1,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(2,'Ruko','RUKO',2,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(3,'Apartemen','APARTEMEN',3,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(4,'Tanah','TANAH',4,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(5,'Kos','KOS',5,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(6,'Kontrakan','KONTRAKAN',6,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(7,'Gudang','GUDANG',7,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(8,'Toko','TOKO',8,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(9,'Kantor','KANTOR',9,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(10,'Lainnya','OTHER',99,1,'2026-05-10 04:23:11','2026-05-10 04:23:11');
/*!40000 ALTER TABLE `ref_jenis_properti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_relasi`
--

DROP TABLE IF EXISTS `ref_jenis_relasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jenis_relasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis relasi keluarga';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_relasi`
--

LOCK TABLES `ref_jenis_relasi` WRITE;
/*!40000 ALTER TABLE `ref_jenis_relasi` DISABLE KEYS */;
INSERT INTO `ref_jenis_relasi` VALUES (1,'Ayah','FATHER',1,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(2,'Ibu','MOTHER',2,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(3,'Suami','HUSBAND',3,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(4,'Istri','WIFE',4,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(5,'Anak','CHILD',5,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(6,'Saudara Kandung','SIBLING',6,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(7,'Kakek','GRANDFATHER',7,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(8,'Nenek','GRANDMOTHER',8,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(9,'Paman','UNCLE',9,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(10,'Bibi','AUNT',10,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(11,'Keponakan','NEPHEW',11,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(12,'Cucu','GRANDCHILD',12,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(13,'Lainnya','OTHER',99,1,'2026-05-10 04:23:11','2026-05-10 04:23:11');
/*!40000 ALTER TABLE `ref_jenis_relasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_telepon`
--

DROP TABLE IF EXISTS `ref_jenis_telepon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jenis_telepon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi jenis telepon';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_telepon`
--

LOCK TABLES `ref_jenis_telepon` WRITE;
/*!40000 ALTER TABLE `ref_jenis_telepon` DISABLE KEYS */;
INSERT INTO `ref_jenis_telepon` VALUES (1,'Mobile','MOBILE',1,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(2,'Rumah','HOME',2,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(3,'Kantor','OFFICE',3,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(4,'WhatsApp','WHATSAPP',4,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(5,'Fax','FAX',5,1,'2026-05-10 04:23:11','2026-05-10 04:23:11'),(6,'Lainnya','OTHER',99,1,'2026-05-10 04:23:11','2026-05-10 04:23:11');
/*!40000 ALTER TABLE `ref_jenis_telepon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_pekerjaan`
--

DROP TABLE IF EXISTS `ref_pekerjaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_pekerjaan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi pekerjaan';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_pekerjaan`
--

LOCK TABLES `ref_pekerjaan` WRITE;
/*!40000 ALTER TABLE `ref_pekerjaan` DISABLE KEYS */;
INSERT INTO `ref_pekerjaan` VALUES (1,'Pedagang','Wiraswasta',1,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(2,'Petani','Pertanian',2,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(3,'Nelayan','Perikanan',3,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(4,'Buruh','Buruh',4,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(5,'PNS','Pemerintahan',5,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(6,'Wiraswasta','Wiraswasta',6,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(7,'Karyawan Swasta','Swasta',7,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(8,'Ibu Rumah Tangga','Rumah Tangga',8,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(9,'Pelajar/Mahasiswa','Pendidikan',9,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(10,'Lainnya',NULL,99,1,'2026-05-07 16:48:01','2026-05-07 16:48:01');
/*!40000 ALTER TABLE `ref_pekerjaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_status_perkawinan`
--

DROP TABLE IF EXISTS `ref_status_perkawinan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_status_perkawinan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi status perkawinan sesuai standar KTP';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_status_perkawinan`
--

LOCK TABLES `ref_status_perkawinan` WRITE;
/*!40000 ALTER TABLE `ref_status_perkawinan` DISABLE KEYS */;
INSERT INTO `ref_status_perkawinan` VALUES (1,'Belum Kawin','BELUM_KAWIN',1,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(2,'Kawin','KAWIN',2,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(3,'Cerai Hidup','CERAI_HIDUP',3,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(4,'Cerai Mati','CERAI_MATI',4,1,'2026-05-07 16:48:01','2026-05-07 16:48:01');
/*!40000 ALTER TABLE `ref_status_perkawinan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_suku`
--

DROP TABLE IF EXISTS `ref_suku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_suku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `provinsi` varchar(50) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`,`provinsi`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Referensi suku bangsa di Indonesia';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_suku`
--

LOCK TABLES `ref_suku` WRITE;
/*!40000 ALTER TABLE `ref_suku` DISABLE KEYS */;
INSERT INTO `ref_suku` VALUES (1,'Jawa','Jawa Tengah',1,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(2,'Sunda','Jawa Barat',2,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(3,'Batak','Sumatera Utara',3,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(4,'Minangkabau','Sumatera Barat',4,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(5,'Bugis','Sulawesi Selatan',5,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(6,'Madura','Jawa Timur',6,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(7,'Betawi','DKI Jakarta',7,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(8,'Bali','Bali',8,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(9,'Banjar','Kalimantan Selatan',9,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(10,'Aceh','Aceh',10,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(11,'Dayak','Kalimantan Tengah',11,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(12,'Sasak','Nusa Tenggara Barat',12,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(13,'Makassar','Sulawesi Selatan',13,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(14,'Papua','Papua',14,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(15,'Tionghoa',NULL,15,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(16,'Arab',NULL,16,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(17,'Minahasa','Sulawesi Utara',17,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(18,'Gorontalo','Gorontalo',18,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(19,'Nias','Sumatera Utara',19,1,'2026-05-07 16:48:01','2026-05-07 16:48:01'),(20,'Melayu','Riau',20,1,'2026-05-07 16:48:01','2026-05-07 16:48:01');
/*!40000 ALTER TABLE `ref_suku` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-10 11:24:48
