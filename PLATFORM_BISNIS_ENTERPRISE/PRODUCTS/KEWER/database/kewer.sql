-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: kewer
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
-- Table structure for table `ahli_waris`
--

DROP TABLE IF EXISTS `ahli_waris`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ahli_waris` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `hubungan` enum('suami','istri','anak','orang_tua','saudara','lainnya') NOT NULL DEFAULT 'lainnya',
  `ktp` varchar(16) DEFAULT NULL,
  `telp` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `adalah_penjamin` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Apakah menjamin pinjaman',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ahli_waris_nasabah` (`nasabah_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Ahli waris nasabah untuk kasus meninggal dunia';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ahli_waris`
--

LOCK TABLES `ahli_waris` WRITE;
/*!40000 ALTER TABLE `ahli_waris` DISABLE KEYS */;
/*!40000 ALTER TABLE `ahli_waris` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_advice`
--

DROP TABLE IF EXISTS `ai_advice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ai_advice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bos_user_id` int(11) DEFAULT NULL COMMENT 'NULL = saran umum',
  `kategori` enum('pertumbuhan','risiko','efisiensi','produk','umum') NOT NULL DEFAULT 'umum',
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `prioritas` enum('rendah','sedang','tinggi','kritis') DEFAULT 'sedang',
  `data_pendukung` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'data metrics yang mendukung saran' CHECK (json_valid(`data_pendukung`)),
  `status` enum('baru','dibaca','diterapkan','diabaikan') DEFAULT 'baru',
  `dibaca_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bos` (`bos_user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_advice`
--

LOCK TABLES `ai_advice` WRITE;
/*!40000 ALTER TABLE `ai_advice` DISABLE KEYS */;
INSERT INTO `ai_advice` VALUES (1,1,'umum','[Koperasi Kewer Pangururan] Kondisi operasional baik','Koperasi Koperasi Kewer Pangururan berjalan dengan baik. Tidak ada masalah kritis yang terdeteksi saat ini. Terus pantau:\n\n1. Pertumbuhan nasabah\n2. Tingkat keterlambatan pembayaran\n3. Efisiensi operasional staf\n4. Diversifikasi produk pinjaman','rendah','{\"nasabah\":2,\"pinjaman\":0,\"staf\":8}','baru',NULL,'2026-05-02 15:14:04','2026-05-02 15:14:04'),(2,26,'pertumbuhan','[Koperasi Test Mandiri] Belum memiliki nasabah — mulai akuisisi segera','Koperasi Koperasi Test Mandiri belum memiliki nasabah terdaftar. Disarankan untuk segera melakukan:\n\n1. Sosialisasi ke masyarakat sekitar\n2. Kerjasama dengan kelurahan/desa setempat\n3. Tawarkan program pinjaman ringan sebagai daya tarik awal\n4. Gunakan fitur petugas lapangan untuk kunjungan door-to-door','kritis','{\"nasabah_total\":0}','baru',NULL,'2026-05-02 15:14:04','2026-05-02 15:14:04'),(3,27,'pertumbuhan','[Koperasi Flow Test] Belum memiliki nasabah — mulai akuisisi segera','Koperasi Koperasi Flow Test belum memiliki nasabah terdaftar. Disarankan untuk segera melakukan:\n\n1. Sosialisasi ke masyarakat sekitar\n2. Kerjasama dengan kelurahan/desa setempat\n3. Tawarkan program pinjaman ringan sebagai daya tarik awal\n4. Gunakan fitur petugas lapangan untuk kunjungan door-to-door','kritis','{\"nasabah_total\":0}','baru',NULL,'2026-05-02 15:14:04','2026-05-02 15:14:04');
/*!40000 ALTER TABLE `ai_advice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akun`
--

DROP TABLE IF EXISTS `akun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akun` (
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tipe` enum('aset','kewajiban','ekuitas','pendapatan','beban') NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `saldo_normal` enum('debit','kredit') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`kode`),
  KEY `idx_tipe` (`tipe`),
  KEY `idx_kategori` (`kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akun`
--

LOCK TABLES `akun` WRITE;
/*!40000 ALTER TABLE `akun` DISABLE KEYS */;
/*!40000 ALTER TABLE `akun` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `angsuran`
--

DROP TABLE IF EXISTS `angsuran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `angsuran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frekuensi_id` int(11) DEFAULT NULL,
  `cabang_id` int(11) DEFAULT 1,
  `pinjaman_id` int(11) NOT NULL,
  `no_angsuran` int(11) NOT NULL,
  `jatuh_tempo` date NOT NULL,
  `pokok` decimal(12,2) NOT NULL,
  `bunga` decimal(12,2) NOT NULL,
  `total_angsuran` decimal(12,2) NOT NULL,
  `denda` decimal(12,2) DEFAULT 0.00,
  `total_bayar` decimal(12,2) DEFAULT 0.00,
  `status` enum('belum','lunas','telat','dibatalkan') NOT NULL DEFAULT 'belum',
  `tanggal_bayar` date DEFAULT NULL,
  `cara_bayar` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `denda_persentase` decimal(8,4) DEFAULT NULL COMMENT 'Persentase denda yang diterapkan',
  `denda_terhitung` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai denda yang dihitung otomatis',
  `denda_dibebaskan` decimal(12,2) DEFAULT NULL COMMENT 'Nilai denda yang di-waive',
  `denda_alasan` varchar(255) DEFAULT NULL COMMENT 'Alasan pembebasan denda',
  `denda_waived_by` int(11) DEFAULT NULL COMMENT 'User ID yang melakukan waive',
  `denda_waived_at` datetime DEFAULT NULL,
  `hari_telat_saat_bayar` int(11) DEFAULT NULL COMMENT 'Jumlah hari telat saat pembayaran dilakukan',
  `total_bayar_akhir` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total bayar = total_angsuran + denda_terhitung - denda_dibebaskan',
  `petugas_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_angsuran_pinjaman` (`pinjaman_id`),
  KEY `idx_angsuran_jatuh_tempo` (`jatuh_tempo`),
  KEY `idx_angsuran_pinjaman_status` (`pinjaman_id`,`status`),
  KEY `idx_angsuran_cabang_status` (`status`),
  KEY `idx_angsuran_jatuh_tempo_status` (`jatuh_tempo`,`status`),
  KEY `fk_angsuran_frekuensi` (`frekuensi_id`),
  KEY `idx_angsuran_status` (`status`),
  KEY `idx_angsuran_status_jatuh_tempo` (`status`,`jatuh_tempo`),
  CONSTRAINT `angsuran_ibfk_2` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`),
  CONSTRAINT `fk_angsuran_frekuensi` FOREIGN KEY (`frekuensi_id`) REFERENCES `ref_frekuensi_angsuran` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_angsuran_pinjaman` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `angsuran`
--

LOCK TABLES `angsuran` WRITE;
/*!40000 ALTER TABLE `angsuran` DISABLE KEYS */;
INSERT INTO `angsuran` VALUES (1,3,1,1,1,'2026-06-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-07 17:37:13',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL),(2,3,1,1,2,'2026-07-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-07 17:37:13',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL),(3,3,1,1,3,'2026-08-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-07 17:37:13',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL),(4,3,1,1,4,'2026-09-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-07 17:37:13',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL),(5,3,1,1,5,'2026-10-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-07 17:37:13',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL),(6,3,1,1,6,'2026-11-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-07 17:37:13',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL);
/*!40000 ALTER TABLE `angsuran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'User who owns the API key',
  `partner_name` varchar(255) DEFAULT NULL COMMENT 'Partner organization name',
  `key_name` varchar(100) NOT NULL COMMENT 'Name/description of the API key',
  `api_key` varchar(255) NOT NULL COMMENT 'The actual API key',
  `scopes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Allowed scopes/permissions' CHECK (json_valid(`scopes`)),
  `is_active` tinyint(1) DEFAULT 1,
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_api_key` (`api_key`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='API key management for partner integrations';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_log_user` (`user_id`),
  KEY `idx_audit_log_action` (`action`),
  KEY `idx_audit_log_table` (`table_name`),
  KEY `idx_audit_log_created` (`created_at`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,1,'CREATE','cabang',1,NULL,'{\"kode_cabang\":\"HQ001\",\"nama_cabang\":\"Kantor Pusat Pangururan\",\"is_headquarters\":true}','::1','curl/8.5.0','2026-05-02 14:17:25'),(2,1,'CREATE','cabang',2,'null','{\"kode_cabang\":\"CB001\",\"nama_cabang\":\"Cabang Balige\",\"alamat\":\"Jl. SM Raja No.5, Balige\"}',NULL,NULL,'2026-05-02 14:17:42');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auto_confirm_settings`
--

DROP TABLE IF EXISTS `auto_confirm_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_confirm_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `plafon_threshold` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tenor_limit` int(11) NOT NULL DEFAULT 0,
  `max_risk_score` int(11) NOT NULL DEFAULT 10,
  `require_nasabah_history` tinyint(1) NOT NULL DEFAULT 1,
  `min_nasabah_history_months` int(11) NOT NULL DEFAULT 3,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_enabled` (`enabled`),
  CONSTRAINT `auto_confirm_settings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auto_confirm_settings_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_confirm_settings`
--

LOCK TABLES `auto_confirm_settings` WRITE;
/*!40000 ALTER TABLE `auto_confirm_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `auto_confirm_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_plans`
--

DROP TABLE IF EXISTS `billing_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tipe` enum('fixed','percentage','usage') NOT NULL DEFAULT 'fixed',
  `harga_bulanan` decimal(15,2) DEFAULT 0.00,
  `persentase_keuntungan` decimal(5,2) DEFAULT 0.00,
  `harga_per_api_call` decimal(10,4) DEFAULT 0.0000,
  `harga_per_render` decimal(10,4) DEFAULT 0.0000,
  `api_call_gratis` int(11) DEFAULT 0,
  `render_gratis` int(11) DEFAULT 0,
  `max_users` int(11) DEFAULT NULL,
  `max_cabang` int(11) DEFAULT NULL,
  `max_nasabah` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_plans`
--

LOCK TABLES `billing_plans` WRITE;
/*!40000 ALTER TABLE `billing_plans` DISABLE KEYS */;
INSERT INTO `billing_plans` VALUES (1,'STARTER','Starter','fixed',250000.00,0.00,0.0000,0.0000,0,0,5,1,100,'Paket starter untuk koperasi kecil. Max 5 user, 1 cabang, 100 nasabah.',1,'2026-05-02 15:03:48','2026-05-02 15:03:48'),(2,'GROWTH','Growth','fixed',750000.00,0.00,0.0000,0.0000,0,0,20,5,500,'Paket growth untuk koperasi berkembang. Max 20 user, 5 cabang, 500 nasabah.',1,'2026-05-02 15:03:48','2026-05-02 15:03:48'),(3,'PRO','Professional','fixed',1500000.00,0.00,0.0000,0.0000,0,0,NULL,NULL,NULL,'Paket pro untuk koperasi besar. Unlimited user & cabang.',1,'2026-05-02 15:03:48','2026-05-02 15:03:48'),(4,'REVENUE_SHARE','Revenue Share','percentage',0.00,2.50,0.0000,0.0000,0,0,NULL,NULL,NULL,'Bayar 2.5% dari keuntungan bulanan koperasi. Cocok untuk koperasi yang baru mulai.',1,'2026-05-02 15:03:48','2026-05-02 15:03:48'),(5,'PAY_AS_YOU_GO','Pay As You Go','usage',0.00,0.00,10.0000,5.0000,10000,50000,NULL,NULL,NULL,'Bayar sesuai pemakaian. 10.000 API call & 50.000 render gratis/bulan.',1,'2026-05-02 15:03:48','2026-05-02 15:03:48');
/*!40000 ALTER TABLE `billing_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blacklist_log`
--

DROP TABLE IF EXISTS `blacklist_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blacklist_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `aksi` enum('blacklist','unblacklist') NOT NULL,
  `scope` enum('koperasi','platform') NOT NULL DEFAULT 'koperasi' COMMENT 'koperasi=hanya di koperasi ini, platform=seluruh platform',
  `owner_bos_id` int(10) unsigned DEFAULT NULL COMMENT 'Bos yang mem-blacklist (null = platform level oleh appOwner)',
  `alasan` text DEFAULT NULL,
  `dilakukan_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nasabah_id` (`nasabah_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blacklist_log`
--

LOCK TABLES `blacklist_log` WRITE;
/*!40000 ALTER TABLE `blacklist_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `blacklist_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bos_registrations`
--

DROP TABLE IF EXISTS `bos_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bos_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `nama_usaha` varchar(100) DEFAULT NULL,
  `alamat_usaha` text DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL,
  `regency_id` int(10) unsigned DEFAULT NULL,
  `district_id` int(10) unsigned DEFAULT NULL,
  `village_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bos_registrations`
--

LOCK TABLES `bos_registrations` WRITE;
/*!40000 ALTER TABLE `bos_registrations` DISABLE KEYS */;
INSERT INTO `bos_registrations` VALUES (1,'patri','$2y$10$zucy/tAcsiUBX5OqgemSgud7V8Kyd5kuiryiABLy1.Rux68v5JC0.','Patri Sihaloho','patri@kewer.co.id','081234567890','Koperasi Kewer Pangururan','Jl. Sisingamangaraja No.1, Pangururan, Samosir',NULL,NULL,NULL,NULL,'approved',NULL,'2026-05-02 14:17:07',NULL,'2026-05-02 14:17:07','2026-05-02 14:17:07'),(2,'bos_test','$2y$10$O8sF3tlZnxzYmuAEFbdWRemzJP6ugwOYgfJWCNr8qt6c/CCNeYmWa','Test Bos Koperasi','test@kewer.co.id','081299990000','Koperasi Test Mandiri','Jl. Test No.1',NULL,NULL,NULL,NULL,'approved',25,'2026-05-02 14:51:52',NULL,'2026-05-02 14:51:52','2026-05-02 14:51:52'),(3,'bos_flow_test','$2y$10$aVKCp3ektX/ptO64NUiu.uHb0Ny.DAS1oqEtuxh4C8u271c8wjSpi','Flow Test Bos','flow@test.co.id','081288880001','Koperasi Flow Test','Jl. Flow No.1',NULL,NULL,NULL,NULL,'approved',25,'2026-05-02 14:54:15',NULL,'2026-05-02 14:54:15','2026-05-02 14:54:15');
/*!40000 ALTER TABLE `bos_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cabang`
--

DROP TABLE IF EXISTS `cabang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cabang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(10) unsigned DEFAULT NULL,
  `kode_cabang` varchar(10) NOT NULL,
  `nama_cabang` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL,
  `provinsi` varchar(50) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL,
  `regency_id` int(10) unsigned DEFAULT NULL,
  `district_id` int(10) unsigned DEFAULT NULL,
  `village_id` int(10) unsigned DEFAULT NULL,
  `db_orang_person_id` int(11) DEFAULT NULL COMMENT 'person record for branch address',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `is_headquarters` tinyint(1) DEFAULT 0,
  `headquarters_id` int(10) unsigned DEFAULT NULL,
  `owner_bos_id` int(10) unsigned DEFAULT NULL,
  `created_by_user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'GPS latitude for geofencing',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'GPS longitude for geofencing',
  `geofence_radius` int(11) DEFAULT 5000 COMMENT 'Geofence radius in meters (default 5km)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_cabang` (`kode_cabang`),
  KEY `idx_owner_bos_id` (`owner_bos_id`),
  KEY `idx_created_by_user_id` (`created_by_user_id`),
  KEY `idx_cooperative_id` (`cooperative_id`),
  KEY `idx_headquarters_id` (`headquarters_id`),
  KEY `idx_db_orang_person` (`db_orang_person_id`),
  KEY `idx_cabang_owner_bos` (`owner_bos_id`),
  KEY `idx_cabang_id` (`id`),
  KEY `idx_cabang_gps` (`latitude`,`longitude`),
  CONSTRAINT `fk_cabang_db_orang_person` FOREIGN KEY (`db_orang_person_id`) REFERENCES `db_orang`.`people` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cabang`
--

LOCK TABLES `cabang` WRITE;
/*!40000 ALTER TABLE `cabang` DISABLE KEYS */;
INSERT INTO `cabang` VALUES (1,NULL,'HQ001','Kantor Pusat Pangururan','Jl. Sisingamangaraja No.1, Pangururan','062163212345','pusat@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,16,'aktif',1,NULL,1,1,'2026-05-02 14:17:25','2026-05-02 15:34:24',NULL,NULL,5000),(2,NULL,'CB001','Cabang Balige','Jl. SM Raja No.5, Balige','062163254321','balige@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,17,'aktif',0,NULL,1,1,'2026-05-02 14:17:42','2026-05-02 15:34:24',NULL,NULL,5000);
/*!40000 ALTER TABLE `cabang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consolidated_reports`
--

DROP TABLE IF EXISTS `consolidated_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consolidated_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `report_type` enum('daily','weekly','monthly','quarterly','yearly') NOT NULL,
  `report_date` date NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_cabang` int(11) NOT NULL DEFAULT 0,
  `total_nasabah` int(11) NOT NULL DEFAULT 0,
  `total_pinjaman_aktif` int(11) NOT NULL DEFAULT 0,
  `total_pinjaman_lunas` int(11) NOT NULL DEFAULT 0,
  `total_plafon_aktif` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_angsuran_dibayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_penunggakan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_kas` decimal(15,2) NOT NULL DEFAULT 0.00,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `generated_by` (`generated_by`),
  KEY `idx_report_type_date` (`report_type`,`report_date`),
  KEY `idx_period` (`period_start`,`period_end`),
  CONSTRAINT `consolidated_reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consolidated_reports`
--

LOCK TABLES `consolidated_reports` WRITE;
/*!40000 ALTER TABLE `consolidated_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `consolidated_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_scoring_logs`
--

DROP TABLE IF EXISTS `credit_scoring_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_scoring_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `risk_level` varchar(50) NOT NULL,
  `breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Score breakdown details' CHECK (json_valid(`breakdown`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nasabah_id` (`nasabah_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `credit_scoring_logs_ibfk_1` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Credit scoring audit trail';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_scoring_logs`
--

LOCK TABLES `credit_scoring_logs` WRITE;
/*!40000 ALTER TABLE `credit_scoring_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `credit_scoring_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_cash_reconciliation`
--

DROP TABLE IF EXISTS `daily_cash_reconciliation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_cash_reconciliation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `tanggal` date NOT NULL,
  `kas_awal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_penerimaan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_pengeluaran` decimal(12,2) NOT NULL DEFAULT 0.00,
  `kas_akhir` decimal(12,2) NOT NULL DEFAULT 0.00,
  `kas_fisik` decimal(12,2) NOT NULL DEFAULT 0.00,
  `selisih` decimal(12,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
  `prepared_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `prepared_by` (`prepared_by`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_status` (`status`),
  CONSTRAINT `daily_cash_reconciliation_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_cash_reconciliation`
--

LOCK TABLES `daily_cash_reconciliation` WRITE;
/*!40000 ALTER TABLE `daily_cash_reconciliation` DISABLE KEYS */;
/*!40000 ALTER TABLE `daily_cash_reconciliation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delegated_permissions`
--

DROP TABLE IF EXISTS `delegated_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delegated_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delegator_id` int(11) NOT NULL COMMENT 'User who delegates',
  `delegatee_id` int(11) NOT NULL COMMENT 'User who receives delegation',
  `permission_scope` varchar(100) NOT NULL COMMENT 'Scope of delegation: all_operations, employee_crud, branch_crud, branch_employee_crud',
  `scope_limitation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON with additional limitations' CHECK (json_valid(`scope_limitation`)),
  `reason` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `delegated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `delegator_id` (`delegator_id`),
  KEY `delegatee_id` (`delegatee_id`),
  CONSTRAINT `delegated_permissions_ibfk_1` FOREIGN KEY (`delegator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delegated_permissions_ibfk_2` FOREIGN KEY (`delegatee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delegated_permissions`
--

LOCK TABLES `delegated_permissions` WRITE;
/*!40000 ALTER TABLE `delegated_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `delegated_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `denda_settings`
--

DROP TABLE IF EXISTS `denda_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `denda_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT NULL,
  `frekuensi` enum('harian','mingguan','bulanan') NOT NULL DEFAULT 'bulanan',
  `tipe_denda` enum('persentase','nominal') NOT NULL DEFAULT 'nominal',
  `nilai_denda` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Persentase dari angsuran ATAU nominal per hari',
  `grace_period` int(11) NOT NULL DEFAULT 0 COMMENT 'Hari toleransi sebelum denda dihitung',
  `denda_maksimal` decimal(12,2) DEFAULT NULL COMMENT 'Batas maksimal denda (null = tanpa batas)',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_denda_frekuensi` (`frekuensi`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `denda_settings`
--

LOCK TABLES `denda_settings` WRITE;
/*!40000 ALTER TABLE `denda_settings` DISABLE KEYS */;
INSERT INTO `denda_settings` VALUES (1,NULL,'harian','nominal',1000.00,1,50000.00,'aktif','2026-04-16 17:03:42','2026-04-16 17:03:42'),(2,NULL,'mingguan','nominal',5000.00,2,100000.00,'aktif','2026-04-16 17:03:42','2026-04-16 17:03:42'),(3,NULL,'bulanan','persentase',2.00,3,500000.00,'aktif','2026-04-16 17:03:42','2026-04-16 17:03:42');
/*!40000 ALTER TABLE `denda_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `external_api_logs`
--

DROP TABLE IF EXISTS `external_api_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `external_api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_name` varchar(100) NOT NULL COMMENT 'Name of external API (e.g., SLIK OJK, Payment Gateway)',
  `endpoint` varchar(255) NOT NULL COMMENT 'API endpoint called',
  `method` varchar(10) NOT NULL COMMENT 'GET, POST, PUT, DELETE',
  `request_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Request payload' CHECK (json_valid(`request_body`)),
  `response_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Response payload' CHECK (json_valid(`response_body`)),
  `status_code` int(11) DEFAULT NULL COMMENT 'HTTP status code',
  `status` varchar(20) NOT NULL COMMENT 'success, error, timeout',
  `error_message` text DEFAULT NULL,
  `duration_ms` int(11) DEFAULT NULL COMMENT 'Request duration in milliseconds',
  `user_id` int(11) DEFAULT NULL COMMENT 'User who initiated the request',
  `cabang_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cabang_id` (`cabang_id`),
  KEY `idx_api_name` (`api_name`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `external_api_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `external_api_logs_ibfk_2` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='External API call logs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `external_api_logs`
--

LOCK TABLES `external_api_logs` WRITE;
/*!40000 ALTER TABLE `external_api_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `external_api_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_risk`
--

DROP TABLE IF EXISTS `family_risk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_risk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `nama_kepala_keluarga` varchar(100) NOT NULL,
  `alamat_keluarga` text NOT NULL,
  `tingkat_risiko` enum('rendah','sedang','tinggi','sangat_tinggi') NOT NULL DEFAULT 'rendah',
  `total_pinjaman_gagal` int(11) DEFAULT 0,
  `total_nasabah_bermasalah` int(11) DEFAULT 0,
  `tanggal_ditandai` date NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_family_risk_risiko` (`tingkat_risiko`),
  KEY `idx_family_risk_alamat` (`alamat_keluarga`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_risk`
--

LOCK TABLES `family_risk` WRITE;
/*!40000 ALTER TABLE `family_risk` DISABLE KEYS */;
/*!40000 ALTER TABLE `family_risk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_officer_activities`
--

DROP TABLE IF EXISTS `field_officer_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `field_officer_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `petugas_id` int(11) NOT NULL,
  `cabang_id` int(11) DEFAULT 1,
  `activity_type` enum('survey_nasabah','input_pinjaman','kutip_angsuran','follow_up','promosi','edukasi','lainnya') NOT NULL,
  `nasabah_id` int(11) DEFAULT NULL,
  `pinjaman_id` int(11) DEFAULT NULL,
  `angsuran_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `daerah_province_id` int(10) unsigned DEFAULT NULL,
  `daerah_regency_id` int(10) unsigned DEFAULT NULL,
  `daerah_district_id` int(10) unsigned DEFAULT NULL,
  `daerah_village_id` bigint(20) unsigned DEFAULT NULL,
  `activity_date` date NOT NULL,
  `activity_time` time NOT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nasabah_id` (`nasabah_id`),
  KEY `pinjaman_id` (`pinjaman_id`),
  KEY `angsuran_id` (`angsuran_id`),
  KEY `idx_petugas_cabang` (`petugas_id`),
  KEY `idx_activity_date` (`activity_date`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_status` (`status`),
  KEY `idx_daerah` (`daerah_district_id`,`daerah_village_id`),
  CONSTRAINT `field_officer_activities_ibfk_1` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_officer_activities_ibfk_3` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE SET NULL,
  CONSTRAINT `field_officer_activities_ibfk_4` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`) ON DELETE SET NULL,
  CONSTRAINT `field_officer_activities_ibfk_5` FOREIGN KEY (`angsuran_id`) REFERENCES `angsuran` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `field_officer_activities`
--

LOCK TABLES `field_officer_activities` WRITE;
/*!40000 ALTER TABLE `field_officer_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `field_officer_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal`
--

DROP TABLE IF EXISTS `jurnal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jurnal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `nomor_jurnal` varchar(50) NOT NULL,
  `tanggal_jurnal` date NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_jurnal` (`nomor_jurnal`),
  KEY `idx_nomor_jurnal` (`nomor_jurnal`),
  KEY `idx_tanggal` (`tanggal_jurnal`),
  KEY `idx_jurnal_tanggal` (`tanggal_jurnal`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal`
--

LOCK TABLES `jurnal` WRITE;
/*!40000 ALTER TABLE `jurnal` DISABLE KEYS */;
INSERT INTO `jurnal` VALUES (1,1,'JRNL-20260502-001-0001','2026-05-02','2026-05-02','Pencairan pinjaman PNJ001 untuk nasabah',1,'2026-05-02 14:20:40','2026-05-02 14:20:40'),(2,1,'JRNL-20260508-001-0001','2026-05-08','2026-05-08','Pencairan pinjaman PNJ002 untuk nasabah',1,'2026-05-08 13:16:02','2026-05-08 13:16:02'),(3,1,'JRNL-20260508-001-0002','2026-05-08','2026-05-08','Pencairan pinjaman PNJ002 untuk nasabah',1,'2026-05-08 13:16:37','2026-05-08 13:16:37');
/*!40000 ALTER TABLE `jurnal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal_detail`
--

DROP TABLE IF EXISTS `jurnal_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jurnal_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jurnal_id` int(11) NOT NULL,
  `akun_kode` varchar(20) NOT NULL,
  `akun_nama` varchar(100) NOT NULL,
  `debit` decimal(20,2) DEFAULT 0.00,
  `kredit` decimal(20,2) DEFAULT 0.00,
  `referensi_tipe` varchar(50) DEFAULT NULL COMMENT 'pinjaman, angsuran, pembayaran, pengeluaran, kas, dll',
  `referensi_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_jurnal_id` (`jurnal_id`),
  KEY `idx_akun` (`akun_kode`),
  KEY `idx_referensi` (`referensi_tipe`,`referensi_id`),
  CONSTRAINT `jurnal_detail_ibfk_1` FOREIGN KEY (`jurnal_id`) REFERENCES `jurnal` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal_detail`
--

LOCK TABLES `jurnal_detail` WRITE;
/*!40000 ALTER TABLE `jurnal_detail` DISABLE KEYS */;
INSERT INTO `jurnal_detail` VALUES (1,1,'1-2001','Piutang Pinjaman',5000000.00,0.00,'pinjaman',1,'2026-05-02 14:20:40'),(2,1,'1-1002','Kas Cabang',0.00,5000000.00,'pinjaman',1,'2026-05-02 14:20:40'),(3,2,'1-2001','Piutang Pinjaman',5000000.00,0.00,'pinjaman',2,'2026-05-08 13:16:02'),(4,2,'1-1002','Kas Cabang',0.00,5000000.00,'pinjaman',2,'2026-05-08 13:16:02'),(5,3,'1-2001','Piutang Pinjaman',5000000.00,0.00,'pinjaman',3,'2026-05-08 13:16:37'),(6,3,'1-1002','Kas Cabang',0.00,5000000.00,'pinjaman',3,'2026-05-08 13:16:37');
/*!40000 ALTER TABLE `jurnal_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal_kas`
--

DROP TABLE IF EXISTS `jurnal_kas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jurnal_kas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `tipe` enum('masuk','keluar') NOT NULL,
  `kategori` enum('angsuran','pencairan','biaya_operasional','gaji','kas_bon','denda','selisih_kas','lainnya') NOT NULL,
  `referensi_tabel` varchar(50) DEFAULT NULL COMMENT 'Nama tabel sumber: pembayaran, pengeluaran, kas_bon, dll',
  `referensi_id` int(11) DEFAULT NULL COMMENT 'ID record di tabel referensi',
  `jumlah` decimal(12,0) NOT NULL,
  `saldo_sebelum` decimal(12,0) NOT NULL DEFAULT 0,
  `saldo_sesudah` decimal(12,0) NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_jurnal_cabang_tgl` (`cabang_id`,`tanggal`),
  KEY `idx_jurnal_ref` (`referensi_tabel`,`referensi_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Jurnal kas harian otomatis dari semua transaksi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal_kas`
--

LOCK TABLES `jurnal_kas` WRITE;
/*!40000 ALTER TABLE `jurnal_kas` DISABLE KEYS */;
INSERT INTO `jurnal_kas` VALUES (1,1,'2026-05-08','keluar','pencairan','pinjaman',2,5000000,0,-5000000,'Pencairan pinjaman PNJ002',1,'2026-05-08 13:16:02'),(2,1,'2026-05-08','keluar','pencairan','pinjaman',3,5000000,-5000000,-10000000,'Pencairan pinjaman PNJ002',1,'2026-05-08 13:16:37');
/*!40000 ALTER TABLE `jurnal_kas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_bon`
--

DROP TABLE IF EXISTS `kas_bon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas_bon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `karyawan_id` int(11) NOT NULL,
  `kode_kasbon` varchar(20) NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `tanggal_pemberian` date DEFAULT NULL,
  `tanggal_potong` date DEFAULT NULL,
  `jumlah` decimal(12,0) NOT NULL,
  `tenor_bulan` int(11) DEFAULT 1,
  `potongan_per_bulan` decimal(12,0) DEFAULT 0,
  `potongan_ke` int(11) DEFAULT 0,
  `sisa_bon` decimal(12,0) DEFAULT 0,
  `tujuan` text DEFAULT NULL,
  `status` enum('pengajuan','disetujui','diberikan','dipotong','selesai','ditolak') DEFAULT 'pengajuan',
  `catatan` text DEFAULT NULL,
  `disetujui_oleh` int(11) DEFAULT NULL,
  `tanggal_disetujui` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_kasbon` (`kode_kasbon`),
  KEY `disetujui_oleh` (`disetujui_oleh`),
  KEY `idx_kasbon_karyawan` (`karyawan_id`),
  KEY `idx_kasbon_status` (`status`),
  KEY `idx_kasbon_tanggal` (`tanggal_pengajuan`),
  KEY `idx_kasbon_tanggal_pengajuan` (`tanggal_pengajuan`),
  CONSTRAINT `kas_bon_ibfk_2` FOREIGN KEY (`karyawan_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_bon_ibfk_3` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_bon`
--

LOCK TABLES `kas_bon` WRITE;
/*!40000 ALTER TABLE `kas_bon` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_bon` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_kasbon_before_insert` BEFORE INSERT ON `kas_bon` FOR EACH ROW BEGIN
    SET NEW.potongan_ke = 0;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kas_bon_potongan`
--

DROP TABLE IF EXISTS `kas_bon_potongan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas_bon_potongan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kas_bon_id` int(11) NOT NULL,
  `bulan_potong` varchar(7) NOT NULL,
  `jumlah_potong` decimal(12,0) NOT NULL,
  `tanggal_potong` date NOT NULL,
  `potong_oleh` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_potongan` (`kas_bon_id`,`bulan_potong`),
  KEY `potong_oleh` (`potong_oleh`),
  KEY `idx_potongan_kasbon` (`kas_bon_id`),
  KEY `idx_potongan_bulan` (`bulan_potong`),
  CONSTRAINT `kas_bon_potongan_ibfk_1` FOREIGN KEY (`kas_bon_id`) REFERENCES `kas_bon` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_bon_potongan_ibfk_2` FOREIGN KEY (`potong_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_bon_potongan`
--

LOCK TABLES `kas_bon_potongan` WRITE;
/*!40000 ALTER TABLE `kas_bon_potongan` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_bon_potongan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_petugas`
--

DROP TABLE IF EXISTS `kas_petugas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas_petugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `petugas_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `saldo_awal` decimal(12,0) DEFAULT 0,
  `total_terima` decimal(12,0) DEFAULT 0,
  `total_disetor` decimal(12,0) DEFAULT 0,
  `saldo_akhir` decimal(12,0) DEFAULT 0,
  `selisih` decimal(12,0) DEFAULT NULL COMMENT 'Positif=lebih, Negatif=kurang',
  `selisih_keterangan` text DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL COMMENT 'Admin/manager yang verifikasi kas',
  `verified_at` datetime DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Kas yang sudah diverifikasi tidak bisa diedit',
  `status` enum('lengkap','kurang','lebih') DEFAULT 'lengkap',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kas_petugas_petugas` (`petugas_id`),
  KEY `idx_kas_petugas_tanggal` (`tanggal`),
  KEY `idx_kas_petugas_petugas_tanggal` (`petugas_id`,`tanggal`),
  CONSTRAINT `kas_petugas_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_petugas`
--

LOCK TABLES `kas_petugas` WRITE;
/*!40000 ALTER TABLE `kas_petugas` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_petugas` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_kas_petugas_before_insert` BEFORE INSERT ON `kas_petugas` FOR EACH ROW BEGIN
    SET NEW.saldo_akhir = NEW.saldo_awal + NEW.total_terima - NEW.total_disetor;
    
    
    IF NEW.total_disetor = 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir > 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir < 0 THEN
        SET NEW.status = 'lebih';
    ELSE
        SET NEW.status = 'lengkap';
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_kas_petugas_before_update` BEFORE UPDATE ON `kas_petugas` FOR EACH ROW BEGIN
    SET NEW.saldo_akhir = NEW.saldo_awal + NEW.total_terima - NEW.total_disetor;
    
    
    IF NEW.total_disetor = 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir > 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir < 0 THEN
        SET NEW.status = 'lebih';
    ELSE
        SET NEW.status = 'lengkap';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kas_petugas_setoran`
--

DROP TABLE IF EXISTS `kas_petugas_setoran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas_petugas_setoran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `petugas_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `total_kas_petugas` decimal(12,2) NOT NULL,
  `total_setoran` decimal(12,2) NOT NULL,
  `selisih` decimal(12,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_petugas_tanggal` (`petugas_id`,`tanggal`),
  KEY `idx_cabang_tanggal` (`tanggal`),
  KEY `idx_status` (`status`),
  CONSTRAINT `kas_petugas_setoran_ibfk_1` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_petugas_setoran_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_petugas_setoran`
--

LOCK TABLES `kas_petugas_setoran` WRITE;
/*!40000 ALTER TABLE `kas_petugas_setoran` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_petugas_setoran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kelebihan_bayar`
--

DROP TABLE IF EXISTS `kelebihan_bayar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kelebihan_bayar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `pinjaman_id` int(11) DEFAULT NULL COMMENT 'Pinjaman yang menghasilkan kelebihan',
  `pembayaran_id` int(11) DEFAULT NULL COMMENT 'Pembayaran sumber kelebihan',
  `jumlah` decimal(12,2) NOT NULL,
  `status` enum('pending','dikembalikan','dikompensasi') NOT NULL DEFAULT 'pending' COMMENT 'dikembalikan=refund cash, dikompensasi=offset ke pinjaman berikutnya',
  `diproses_oleh` int(11) DEFAULT NULL,
  `tanggal_proses` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kelebihan_nasabah` (`nasabah_id`),
  KEY `idx_kelebihan_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Kelebihan bayar nasabah — refund atau kompensasi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kelebihan_bayar`
--

LOCK TABLES `kelebihan_bayar` WRITE;
/*!40000 ALTER TABLE `kelebihan_bayar` DISABLE KEYS */;
/*!40000 ALTER TABLE `kelebihan_bayar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_activities`
--

DROP TABLE IF EXISTS `koperasi_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `koperasi_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bos_user_id` int(11) NOT NULL COMMENT 'FK users.id (bos)',
  `cabang_id` int(11) DEFAULT NULL COMMENT 'FK cabang.id',
  `user_id` int(11) DEFAULT NULL COMMENT 'user yang melakukan',
  `kategori` enum('nasabah_baru','nasabah_blacklist','pinjaman_baru','pinjaman_disetujui','pinjaman_lunas','pinjaman_gagal','pembayaran','angsuran_telat','cabang_baru','petugas_baru','petugas_keluar','kas_masuk','kas_keluar','login','setting_change','lainnya') NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'metadata tambahan' CHECK (json_valid(`data_json`)),
  `ref_table` varchar(50) DEFAULT NULL COMMENT 'tabel terkait (nasabah/pinjaman/etc)',
  `ref_id` int(11) DEFAULT NULL COMMENT 'ID record terkait',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bos` (`bos_user_id`),
  KEY `idx_cabang` (`cabang_id`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_created` (`created_at`),
  KEY `idx_ref` (`ref_table`,`ref_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log semua aktivitas penting koperasi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_activities`
--

LOCK TABLES `koperasi_activities` WRITE;
/*!40000 ALTER TABLE `koperasi_activities` DISABLE KEYS */;
INSERT INTO `koperasi_activities` VALUES (1,1,1,NULL,'nasabah_baru','Nasabah baru: Budi Siregar','Nasabah NSB001 (Budi Siregar) terdaftar',NULL,'nasabah',1,NULL,'2026-05-02 14:19:56'),(2,1,1,NULL,'nasabah_baru','Nasabah baru: Maria Tampubolon','Nasabah NSB002 (Maria Tampubolon) terdaftar',NULL,'nasabah',2,NULL,'2026-05-02 14:19:56'),(4,1,1,NULL,'nasabah_baru','Nasabah baru: Budi Siregar','Nasabah NSB001 (Budi Siregar) terdaftar',NULL,'nasabah',1,NULL,'2026-05-02 14:19:56'),(5,1,1,NULL,'nasabah_baru','Nasabah baru: Maria Tampubolon','Nasabah NSB002 (Maria Tampubolon) terdaftar',NULL,'nasabah',2,NULL,'2026-05-02 14:19:56'),(7,1,1,19,'pinjaman_lunas','Pinjaman Rp 5,000,000 untuk nasabah #1','Pinjaman plafon Rp 5,000,000, bunga 2.00%, tenor 6 bulan','{\"plafon\": 5000000.00, \"bunga_per_bulan\": 2.00, \"tenor\": 6}','pinjaman',1,NULL,'2026-05-02 14:20:20'),(8,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #1 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',1,NULL,'2026-05-01 17:00:00'),(9,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #2 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',2,NULL,'2026-05-01 17:00:00'),(10,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #3 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',3,NULL,'2026-05-01 17:00:00'),(11,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #4 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',4,NULL,'2026-05-01 17:00:00'),(12,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #5 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',5,NULL,'2026-05-01 17:00:00'),(13,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #6 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',6,NULL,'2026-05-01 17:00:00');
/*!40000 ALTER TABLE `koperasi_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_billing`
--

DROP TABLE IF EXISTS `koperasi_billing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `koperasi_billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bos_user_id` int(11) NOT NULL COMMENT 'user.id of bos',
  `billing_plan_id` int(11) NOT NULL,
  `status` enum('aktif','suspended','cancelled') DEFAULT 'aktif',
  `tanggal_mulai` date NOT NULL,
  `tanggal_berakhir` date DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `billing_plan_id` (`billing_plan_id`),
  KEY `idx_bos` (`bos_user_id`),
  CONSTRAINT `koperasi_billing_ibfk_1` FOREIGN KEY (`billing_plan_id`) REFERENCES `billing_plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_billing`
--

LOCK TABLES `koperasi_billing` WRITE;
/*!40000 ALTER TABLE `koperasi_billing` DISABLE KEYS */;
INSERT INTO `koperasi_billing` VALUES (1,1,1,'aktif','2026-05-02',NULL,NULL,25,'2026-05-02 15:13:20','2026-05-02 15:13:20');
/*!40000 ALTER TABLE `koperasi_billing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_invoices`
--

DROP TABLE IF EXISTS `koperasi_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `koperasi_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_billing_id` int(11) NOT NULL,
  `bos_user_id` int(11) NOT NULL,
  `kode_invoice` varchar(30) NOT NULL,
  `periode_bulan` int(2) NOT NULL,
  `periode_tahun` int(4) NOT NULL,
  `biaya_fixed` decimal(15,2) DEFAULT 0.00,
  `biaya_persentase` decimal(15,2) DEFAULT 0.00,
  `keuntungan_koperasi` decimal(15,2) DEFAULT 0.00 COMMENT 'basis perhitungan persentase',
  `biaya_usage` decimal(15,2) DEFAULT 0.00,
  `total_api_calls` int(11) DEFAULT 0,
  `total_renders` int(11) DEFAULT 0,
  `subtotal` decimal(15,2) DEFAULT 0.00,
  `diskon` decimal(15,2) DEFAULT 0.00,
  `total` decimal(15,2) DEFAULT 0.00,
  `status` enum('draft','terbit','dibayar','overdue','cancelled') DEFAULT 'draft',
  `tanggal_terbit` date DEFAULT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `metode_bayar` varchar(50) DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_invoice` (`kode_invoice`),
  UNIQUE KEY `uk_billing_periode` (`koperasi_billing_id`,`periode_tahun`,`periode_bulan`),
  KEY `idx_bos` (`bos_user_id`),
  KEY `idx_periode` (`periode_tahun`,`periode_bulan`),
  CONSTRAINT `koperasi_invoices_ibfk_1` FOREIGN KEY (`koperasi_billing_id`) REFERENCES `koperasi_billing` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_invoices`
--

LOCK TABLES `koperasi_invoices` WRITE;
/*!40000 ALTER TABLE `koperasi_invoices` DISABLE KEYS */;
INSERT INTO `koperasi_invoices` VALUES (1,1,1,'INV-202605-0001',5,2026,250000.00,0.00,0.00,0.00,0,0,250000.00,0.00,250000.00,'dibayar','2026-05-02','2026-05-16','2026-05-02','transfer',NULL,NULL,'2026-05-02 15:13:20','2026-05-02 15:13:20');
/*!40000 ALTER TABLE `koperasi_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `labarugi`
--

DROP TABLE IF EXISTS `labarugi`;
/*!50001 DROP VIEW IF EXISTS `labarugi`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `labarugi` AS SELECT
 1 AS `kategori`,
  1 AS `kode`,
  1 AS `nama`,
  1 AS `saldo_akhir` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `loan_risk_log`
--

DROP TABLE IF EXISTS `loan_risk_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loan_risk_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `nasabah_id` int(11) NOT NULL,
  `pinjaman_id` int(11) NOT NULL,
  `jenis_risiko` enum('gagal_bayar','macet','keluarga_bermasalah','blacklist_keluarga','lainnya') NOT NULL,
  `tingkat_risiko` enum('rendah','sedang','tinggi','sangat_tinggi') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tindakan_diambil` text DEFAULT NULL,
  `tanggal_kejadian` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pinjaman_id` (`pinjaman_id`),
  KEY `idx_loan_risk_nasabah` (`nasabah_id`),
  KEY `idx_loan_risk_jenis` (`jenis_risiko`),
  KEY `idx_loan_risk_tanggal` (`tanggal_kejadian`),
  CONSTRAINT `loan_risk_log_ibfk_3` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_risk_log`
--

LOCK TABLES `loan_risk_log` WRITE;
/*!40000 ALTER TABLE `loan_risk_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_risk_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile_devices`
--

DROP TABLE IF EXISTS `mobile_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobile_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT 'android' COMMENT 'android, ios, web',
  `app_version` varchar(50) DEFAULT NULL,
  `os_version` varchar(50) DEFAULT NULL,
  `push_token` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_seen` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_device_id` (`device_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `mobile_devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registered mobile devices';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile_devices`
--

LOCK TABLES `mobile_devices` WRITE;
/*!40000 ALTER TABLE `mobile_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `mobile_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nasabah`
--

DROP TABLE IF EXISTS `nasabah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nasabah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `owner_bos_id` int(10) unsigned DEFAULT NULL COMMENT 'Bos pemilik data nasabah ini',
  `kode_nasabah` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `alamat_rumah` text DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL,
  `regency_id` int(10) unsigned DEFAULT NULL,
  `district_id` int(10) unsigned DEFAULT NULL,
  `village_id` int(10) unsigned DEFAULT NULL,
  `hubungan_keluarga` text DEFAULT NULL,
  `ktp` varchar(16) NOT NULL,
  `telp` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jenis_usaha` varchar(50) DEFAULT NULL,
  `lokasi_pasar` varchar(100) DEFAULT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `foto_selfie` varchar(255) DEFAULT NULL,
  `referensi_nasabah_id` int(11) DEFAULT NULL,
  `db_orang_user_id` int(11) DEFAULT NULL,
  `db_orang_address_id` int(11) DEFAULT NULL,
  `status` enum('aktif','nonaktif','blacklist') NOT NULL DEFAULT 'aktif',
  `blacklist_reason` text DEFAULT NULL,
  `skor_risiko_keluarga` int(11) DEFAULT 0,
  `catatan_risiko` text DEFAULT NULL,
  `skor_kredit` int(11) NOT NULL DEFAULT 100 COMMENT 'Skor kredit internal 0-100. Turun jika telat, naik jika tepat waktu',
  `platform_blacklist` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Blacklist platform-wide oleh appOwner (lintas koperasi)',
  `total_pinjaman_aktif` int(11) NOT NULL DEFAULT 0 COMMENT 'Cache: jumlah pinjaman aktif (di koperasi ini)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tanggal_meninggal` date DEFAULT NULL COMMENT 'Jika nasabah meninggal dunia',
  `penjamin_id` int(11) DEFAULT NULL COMMENT 'ID ahli waris yang menjadi penjamin pinjaman',
  `credit_score` decimal(5,2) DEFAULT NULL COMMENT 'Credit score (0-100)',
  `risk_level` varchar(50) DEFAULT NULL COMMENT 'Risk level: Sangat Rendah, Rendah, Sedang, Tinggi, Sangat Tinggi',
  `score_updated_at` datetime DEFAULT NULL COMMENT 'Last credit score update',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_nasabah` (`kode_nasabah`),
  UNIQUE KEY `uk_nasabah_ktp_koperasi` (`ktp`,`owner_bos_id`),
  KEY `idx_nasabah_ktp` (`ktp`),
  KEY `idx_referensi_nasabah` (`referensi_nasabah_id`),
  KEY `idx_nasabah_cabang_status` (`status`),
  KEY `idx_province_id` (`province_id`),
  KEY `idx_regency_id` (`regency_id`),
  KEY `idx_district_id` (`district_id`),
  KEY `idx_village_id` (`village_id`),
  KEY `idx_db_orang_user` (`db_orang_user_id`),
  KEY `idx_db_orang_address` (`db_orang_address_id`),
  KEY `idx_nasabah_cabang` (`cabang_id`),
  KEY `idx_nasabah_owner_bos` (`owner_bos_id`),
  KEY `idx_nasabah_status` (`status`),
  KEY `idx_nasabah_platform_blacklist` (`platform_blacklist`),
  KEY `idx_nasabah_credit_score` (`credit_score`),
  KEY `idx_nasabah_risk_level` (`risk_level`),
  CONSTRAINT `fk_nasabah_cabang` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_nasabah_db_orang_address` FOREIGN KEY (`db_orang_address_id`) REFERENCES `db_orang`.`addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_nasabah_db_orang_user` FOREIGN KEY (`db_orang_user_id`) REFERENCES `db_orang`.`people` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_nasabah_referensi` FOREIGN KEY (`referensi_nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nasabah`
--

LOCK TABLES `nasabah` WRITE;
/*!40000 ALTER TABLE `nasabah` DISABLE KEYS */;
INSERT INTO `nasabah` VALUES (1,1,1,'NSB001','Budi Siregar',NULL,NULL,'Pangururan',NULL,NULL,NULL,NULL,NULL,NULL,'1201010101010001','081234500001',NULL,'Warung','',NULL,NULL,NULL,14,NULL,'aktif',NULL,0,NULL,100,0,0,'2026-05-02 14:19:56','2026-05-03 17:54:53',NULL,NULL,NULL,NULL,NULL),(2,1,1,'NSB002','Maria Tampubolon',NULL,NULL,'Balige',NULL,NULL,NULL,NULL,NULL,NULL,'1201010101010002','081234500002',NULL,'Toko Kelontong','',NULL,NULL,NULL,15,NULL,'aktif',NULL,0,NULL,100,0,0,'2026-05-02 14:19:56','2026-05-03 17:54:53',NULL,NULL,NULL,NULL,NULL),(4,1,1,'NSB003','Duplicate',NULL,NULL,'Test',NULL,NULL,NULL,NULL,NULL,NULL,'1234567890768090','081234567890','','','',NULL,NULL,NULL,NULL,NULL,'aktif',NULL,0,NULL,100,0,0,'2026-05-08 13:12:57','2026-05-08 13:12:57',NULL,NULL,NULL,NULL,NULL),(10,1,1,'NSB004','Duplicate',NULL,NULL,'Test',NULL,NULL,NULL,NULL,NULL,NULL,'1234567890180701','081234567890','','','',NULL,NULL,NULL,NULL,NULL,'aktif',NULL,0,NULL,100,0,0,'2026-05-08 13:16:02','2026-05-08 13:16:02',NULL,NULL,NULL,NULL,NULL),(13,1,1,'NSB005','Duplicate',NULL,NULL,'Test',NULL,NULL,NULL,NULL,NULL,NULL,'1234567890765744','081234567890','','','',NULL,NULL,NULL,NULL,NULL,'aktif',NULL,0,NULL,100,0,0,'2026-05-08 13:16:37','2026-05-08 13:16:37',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `nasabah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nasabah_family_link`
--

DROP TABLE IF EXISTS `nasabah_family_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nasabah_family_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `jenis_hubungan` enum('ayah','ibu','suami','istri','anak','saudara','kerabat','lainnya') NOT NULL,
  `nama_keluarga` varchar(100) NOT NULL,
  `ktp_keluarga` varchar(16) DEFAULT NULL,
  `alamat_keluarga` text DEFAULT NULL,
  `telp_keluarga` varchar(15) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_family_link_nasabah` (`nasabah_id`),
  KEY `idx_family_link_ktp` (`ktp_keluarga`),
  CONSTRAINT `nasabah_family_link_ibfk_1` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nasabah_family_link`
--

LOCK TABLES `nasabah_family_link` WRITE;
/*!40000 ALTER TABLE `nasabah_family_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `nasabah_family_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nasabah_orang_mapping`
--

DROP TABLE IF EXISTS `nasabah_orang_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nasabah_orang_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `db_orang_user_id` int(11) DEFAULT NULL,
  `db_orang_address_id` int(11) DEFAULT NULL,
  `mapped_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mapped_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nasabah_id` (`nasabah_id`),
  KEY `db_orang_user_id` (`db_orang_user_id`),
  KEY `db_orang_address_id` (`db_orang_address_id`),
  KEY `mapped_by` (`mapped_by`),
  CONSTRAINT `nasabah_orang_mapping_ibfk_1` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nasabah_orang_mapping_ibfk_2` FOREIGN KEY (`mapped_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nasabah_orang_mapping`
--

LOCK TABLES `nasabah_orang_mapping` WRITE;
/*!40000 ALTER TABLE `nasabah_orang_mapping` DISABLE KEYS */;
/*!40000 ALTER TABLE `nasabah_orang_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `neraca`
--

DROP TABLE IF EXISTS `neraca`;
/*!50001 DROP VIEW IF EXISTS `neraca`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `neraca` AS SELECT
 1 AS `kategori`,
  1 AS `kode`,
  1 AS `nama`,
  1 AS `saldo_akhir` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `neraca_saldo`
--

DROP TABLE IF EXISTS `neraca_saldo`;
/*!50001 DROP VIEW IF EXISTS `neraca_saldo`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `neraca_saldo` AS SELECT
 1 AS `kode`,
  1 AS `nama`,
  1 AS `tipe`,
  1 AS `kategori`,
  1 AS `saldo_normal`,
  1 AS `total_debit`,
  1 AS `total_kredit`,
  1 AS `saldo_akhir` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `notification_queue`
--

DROP TABLE IF EXISTS `notification_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) DEFAULT NULL COMMENT 'ID nasabah (opsional)',
  `petugas_id` int(11) DEFAULT NULL COMMENT 'ID petugas yang mengirim (opsional)',
  `tipe` enum('jatuh_tempo','konfirmasi_bayar','blacklist','approval_pinjaman','tagihan','lainnya') NOT NULL DEFAULT 'lainnya',
  `nomor_wa` varchar(20) NOT NULL COMMENT 'Nomor WhatsApp tujuan',
  `pesan` text NOT NULL COMMENT 'Isi pesan',
  `priority` tinyint(1) NOT NULL DEFAULT 5 COMMENT '1=highest, 10=lowest',
  `status` enum('pending','processing','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
  `provider` varchar(20) NOT NULL DEFAULT 'fonnte' COMMENT 'Fonnte, Twilio, dll',
  `retry_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Jumlah retry gagal',
  `max_retry` int(11) NOT NULL DEFAULT 3 COMMENT 'Max retry sebelumµö¥Õ╝â',
  `scheduled_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu terjadwal (jika delayed)',
  `sent_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu terkirim',
  `response_code` int(11) DEFAULT NULL COMMENT 'HTTP response code',
  `response_body` text DEFAULT NULL COMMENT 'Response body dari provider',
  `error_message` text DEFAULT NULL COMMENT 'Error message jika gagal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_scheduled` (`scheduled_at`),
  KEY `idx_tipe` (`tipe`),
  KEY `idx_status_priority` (`status`,`priority`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Queue untuk notifikasi WA dengan rate limiting';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_queue`
--

LOCK TABLES `notification_queue` WRITE;
/*!40000 ALTER TABLE `notification_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifikasi`
--

DROP TABLE IF EXISTS `notifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'Null = broadcast ke semua role tertentu',
  `target_role` varchar(50) DEFAULT NULL COMMENT 'Kirim ke semua user dengan role ini di cabang',
  `tipe` enum('jatuh_tempo','macet','kas_selisih','pinjaman_baru','restrukturisasi','write_off','info','peringatan') NOT NULL,
  `judul` varchar(200) NOT NULL,
  `pesan` text NOT NULL,
  `referensi_tabel` varchar(50) DEFAULT NULL,
  `referensi_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`,`is_read`),
  KEY `idx_notif_cabang` (`cabang_id`,`tipe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Notifikasi internal sistem';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifikasi`
--

LOCK TABLES `notifikasi` WRITE;
/*!40000 ALTER TABLE `notifikasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `pinjaman_id` int(11) NOT NULL,
  `angsuran_id` int(11) NOT NULL,
  `kode_pembayaran` varchar(20) NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `denda` decimal(12,2) DEFAULT 0.00,
  `total_bayar` decimal(12,2) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `cara_bayar` enum('tunai','transfer','digital') NOT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `petugas_id` int(11) NOT NULL,
  `petugas_pengganti_id` int(11) DEFAULT NULL COMMENT 'Jika pembayaran dikutip oleh petugas pengganti',
  `is_offline` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Pembayaran diinput offline (belakangan)',
  `offline_reason` varchar(255) DEFAULT NULL,
  `tanggal_kutip` date DEFAULT NULL COMMENT 'Tanggal petugas mengutip (bisa berbeda dari tanggal_bayar)',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `denda_dibayar` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jumlah denda yang dibayar',
  `denda_waived` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jumlah denda yang di-waive',
  `total_pembayaran` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total = pokok + bunga + denda_dibayar',
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `akurasi_gps` smallint(6) DEFAULT NULL COMMENT 'meters',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'GPS latitude',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'GPS longitude',
  `gps_accuracy` decimal(8,2) DEFAULT NULL COMMENT 'GPS accuracy in meters',
  `captured_at` datetime DEFAULT NULL COMMENT 'GPS capture timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_pembayaran` (`kode_pembayaran`),
  KEY `angsuran_id` (`angsuran_id`),
  KEY `petugas_id` (`petugas_id`),
  KEY `idx_pembayaran_pinjaman` (`pinjaman_id`),
  KEY `idx_pembayaran_pinjaman_tanggal` (`pinjaman_id`,`tanggal_bayar`),
  KEY `idx_pembayaran_angsuran` (`angsuran_id`),
  KEY `idx_pembayaran_tanggal` (`tanggal_bayar`),
  KEY `idx_pembayaran_petugas` (`petugas_id`),
  KEY `idx_gps` (`latitude`,`longitude`),
  CONSTRAINT `fk_pembayaran_pinjaman` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`),
  CONSTRAINT `pembayaran_ibfk_3` FOREIGN KEY (`angsuran_id`) REFERENCES `angsuran` (`id`),
  CONSTRAINT `pembayaran_ibfk_4` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayaran`
--

LOCK TABLES `pembayaran` WRITE;
/*!40000 ALTER TABLE `pembayaran` DISABLE KEYS */;
INSERT INTO `pembayaran` VALUES (1,1,1,1,'BYR001',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,NULL,0,NULL,NULL,'','2026-05-02 14:34:59','2026-05-02 14:34:59',0.00,0.00,933333.33,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,1,1,2,'BYR002',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,NULL,0,NULL,NULL,'','2026-05-02 14:35:10','2026-05-02 14:35:10',0.00,0.00,933333.33,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,1,1,3,'BYR003',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,NULL,0,NULL,NULL,'','2026-05-02 14:35:10','2026-05-02 14:35:10',0.00,0.00,933333.33,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,1,1,4,'BYR004',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,NULL,0,NULL,NULL,'','2026-05-02 14:35:11','2026-05-02 14:35:11',0.00,0.00,933333.33,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,1,1,5,'BYR005',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,NULL,0,NULL,NULL,'','2026-05-02 14:35:11','2026-05-02 14:35:11',0.00,0.00,933333.33,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,1,1,6,'BYR006',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,NULL,0,NULL,NULL,'','2026-05-02 14:35:11','2026-05-02 14:35:11',0.00,0.00,933333.33,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `pembayaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembayaran_offline_queue`
--

DROP TABLE IF EXISTS `pembayaran_offline_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembayaran_offline_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `petugas_id` int(11) NOT NULL,
  `nasabah_id` int(11) NOT NULL,
  `pinjaman_id` int(11) NOT NULL,
  `angsuran_id` int(11) NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `tanggal_kutip` date NOT NULL,
  `cara_bayar` enum('tunai','transfer','digital') NOT NULL DEFAULT 'tunai',
  `keterangan` text DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL COMMENT 'Identifikasi perangkat petugas',
  `status` enum('pending','processed','failed','duplicate') NOT NULL DEFAULT 'pending',
  `processed_at` datetime DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `pembayaran_id` int(11) DEFAULT NULL COMMENT 'ID pembayaran yang dibuat setelah sync',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_offline_petugas` (`petugas_id`,`tanggal_kutip`),
  KEY `idx_offline_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Antrian pembayaran offline untuk sync saat online';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayaran_offline_queue`
--

LOCK TABLES `pembayaran_offline_queue` WRITE;
/*!40000 ALTER TABLE `pembayaran_offline_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `pembayaran_offline_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penagihan`
--

DROP TABLE IF EXISTS `penagihan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penagihan` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pinjaman_id` int(11) NOT NULL,
  `angsuran_id` int(11) DEFAULT NULL,
  `jenis` enum('jatuh_tempo','telat','macet','follow_up') NOT NULL DEFAULT 'jatuh_tempo',
  `jenis_penagihan_id` int(11) DEFAULT NULL,
  `status` enum('pending','dalam_proses','berhasil','gagal','diabaikan') DEFAULT 'pending',
  `tanggal_jatuh_tempo` date NOT NULL,
  `tanggal_penagihan` date DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `hasil` text DEFAULT NULL,
  `tindakan` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `angsuran_id` (`angsuran_id`),
  KEY `idx_pinjaman_status` (`pinjaman_id`,`status`),
  KEY `idx_petugas_tanggal` (`petugas_id`,`tanggal_penagihan`),
  KEY `idx_jenis_status` (`jenis`,`status`),
  KEY `idx_tanggal_jatuh_tempo` (`tanggal_jatuh_tempo`),
  KEY `fk_penagihan_jenis` (`jenis_penagihan_id`),
  CONSTRAINT `fk_penagihan_jenis` FOREIGN KEY (`jenis_penagihan_id`) REFERENCES `ref_jenis_penagihan` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `penagihan_ibfk_1` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `penagihan_ibfk_2` FOREIGN KEY (`angsuran_id`) REFERENCES `angsuran` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `penagihan_ibfk_3` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penagihan`
--

LOCK TABLES `penagihan` WRITE;
/*!40000 ALTER TABLE `penagihan` DISABLE KEYS */;
/*!40000 ALTER TABLE `penagihan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penagihan_log`
--

DROP TABLE IF EXISTS `penagihan_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penagihan_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `penagihan_id` bigint(20) NOT NULL,
  `aksi` varchar(100) NOT NULL,
  `hasil` text DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_penagihan_id` (`penagihan_id`),
  KEY `idx_petugas_tanggal` (`petugas_id`,`tanggal`),
  CONSTRAINT `penagihan_log_ibfk_1` FOREIGN KEY (`penagihan_id`) REFERENCES `penagihan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `penagihan_log_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penagihan_log`
--

LOCK TABLES `penagihan_log` WRITE;
/*!40000 ALTER TABLE `penagihan_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `penagihan_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengeluaran`
--

DROP TABLE IF EXISTS `pengeluaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `kategori` enum('gaji','lembur','bonus','operasional','belanja','lainnya') NOT NULL,
  `sub_kategori` varchar(50) DEFAULT NULL,
  `jumlah` decimal(12,0) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `petugas_id` (`petugas_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_pengeluaran_kategori` (`kategori`),
  KEY `idx_pengeluaran_tanggal` (`tanggal`),
  KEY `idx_pengeluaran_status` (`status`),
  KEY `idx_pengeluaran_cabang_status` (`status`),
  KEY `idx_pengeluaran_kategori_tanggal` (`kategori`,`tanggal`),
  CONSTRAINT `pengeluaran_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pengeluaran_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengeluaran`
--

LOCK TABLES `pengeluaran` WRITE;
/*!40000 ALTER TABLE `pengeluaran` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengeluaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengganti_petugas`
--

DROP TABLE IF EXISTS `pengganti_petugas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengganti_petugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL COMMENT 'alias petugas_asli_id',
  `pengganti_id` int(11) DEFAULT NULL COMMENT 'alias petugas_pengganti_id',
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `alasan_ketidakhadiran` varchar(50) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `petugas_asli_id` int(11) NOT NULL COMMENT 'Petugas yang tidak hadir',
  `petugas_pengganti_id` int(11) NOT NULL COMMENT 'Petugas yang menggantikan',
  `tanggal` date NOT NULL,
  `alasan` enum('sakit','izin','cuti','lainnya') NOT NULL DEFAULT 'lainnya',
  `keterangan` text DEFAULT NULL,
  `disetujui_oleh` int(11) DEFAULT NULL,
  `status` enum('pending','aktif','selesai','batal') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pengganti_asli` (`petugas_asli_id`),
  KEY `idx_pengganti_tgl` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Penugasan petugas pengganti saat petugas asli berhalangan';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengganti_petugas`
--

LOCK TABLES `pengganti_petugas` WRITE;
/*!40000 ALTER TABLE `pengganti_petugas` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengganti_petugas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_audit_log`
--

DROP TABLE IF EXISTS `permission_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `target_user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `permission_id` int(11) DEFAULT NULL,
  `old_value` tinyint(1) DEFAULT NULL,
  `new_value` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_target_user_id` (`target_user_id`),
  KEY `idx_permission_id` (`permission_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `permission_audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_audit_log_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_audit_log`
--

LOCK TABLES `permission_audit_log` WRITE;
/*!40000 ALTER TABLE `permission_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `permission_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(100) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori` varchar(100) NOT NULL DEFAULT 'general',
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `idx_kode` (`kode`),
  KEY `idx_kategori` (`kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.read','View Dashboard','general','View dashboard statistics','2026-04-28 05:26:12'),(2,'nasabah.read','View Nasabah','nasabah','View nasabah list','2026-04-28 05:26:12'),(3,'manage_nasabah','Manage Nasabah','nasabah','Create, edit, delete nasabah','2026-04-28 05:26:12'),(4,'pinjaman.read','View Pinjaman','pinjaman','View pinjaman list','2026-04-28 05:26:12'),(5,'manage_pinjaman','Manage Pinjaman','pinjaman','Create, edit, delete pinjaman','2026-04-28 05:26:12'),(6,'pinjaman.approve','Approve Pinjaman','pinjaman','Approve pinjaman applications','2026-04-28 05:26:12'),(7,'angsuran.read','View Angsuran','angsuran','View angsuran list','2026-04-28 05:26:12'),(8,'manage_pembayaran','Manage Pembayaran','angsuran','Record payments','2026-04-28 05:26:12'),(9,'angsuran.create','Catat Aktivitas Lapangan','aktivitas','Akses untuk mencatat aktivitas lapangan','2026-04-28 05:26:12'),(10,'kas_petugas.read','View Kas Petugas','kas','View kas petugas','2026-04-28 05:26:12'),(11,'kas_petugas.update','Update Kas Petugas','kas','Approve setoran','2026-04-28 05:26:12'),(12,'kas.read','View Cash','kas','View cash reconciliation','2026-04-28 05:26:12'),(13,'kas.update','Update Cash','kas','Approve reconciliation','2026-04-28 05:26:12'),(14,'pinjaman.auto_confirm','Auto Confirm Settings','settings','Manage auto-confirm','2026-04-28 05:26:12'),(15,'users.create','Create Users','users','Create new users','2026-04-28 05:26:12'),(16,'users.read','View Users','users','View users list','2026-04-28 05:26:12'),(17,'manage_users','Manage Users','users','Edit, delete users','2026-04-28 05:26:12'),(18,'cabang.read','View Cabang','cabang','View branch list','2026-04-28 05:26:12'),(19,'manage_cabang','Manage Cabang','cabang','Create, edit, delete branches','2026-04-28 05:26:12'),(20,'view_laporan','View Laporan','laporan','View reports','2026-04-28 05:26:12'),(21,'manage_pengeluaran','Manage Pengeluaran','pengeluaran','Create, edit, delete pengeluaran','2026-04-28 05:26:12'),(22,'view_pengeluaran','View Pengeluaran','pengeluaran','View pengeluaran list','2026-04-28 05:26:12'),(23,'manage_kas_bon','Manage Kas Bon','kas_bon','Create, edit, delete kas bon','2026-04-28 05:26:12'),(24,'view_kas_bon','View Kas Bon','kas_bon','View kas bon list','2026-04-28 05:26:12'),(25,'manage_bunga','Manage Bunga','settings','Edit bunga settings','2026-04-28 05:26:12'),(26,'view_settings','View Settings','settings','View settings','2026-04-28 05:26:12'),(27,'manage_petugas','Manage Petugas','users','Create, edit, delete petugas','2026-04-28 05:26:12'),(28,'view_petugas','View Petugas','users','View petugas list','2026-04-28 05:26:12'),(29,'assign_permissions','Assign Permissions','admin','Assign permissions to users','2026-04-28 05:26:12'),(58,'nasabah.create','Nasabah create','general','Permission for nasabah.create','2026-04-29 17:41:31'),(59,'nasabah.edit','Nasabah edit','general','Permission for nasabah.edit','2026-04-29 17:41:31'),(60,'nasabah.delete','Nasabah delete','general','Permission for nasabah.delete','2026-04-29 17:41:31'),(61,'pinjaman.create','Pinjaman create','general','Permission for pinjaman.create','2026-04-29 17:41:31'),(62,'pinjaman.edit','Pinjaman edit','general','Permission for pinjaman.edit','2026-04-29 17:41:31'),(63,'pinjaman.delete','Pinjaman delete','general','Permission for pinjaman.delete','2026-04-29 17:41:31'),(64,'angsuran.edit','Angsuran edit','general','Permission for angsuran.edit','2026-04-29 17:41:31'),(65,'angsuran.delete','Angsuran delete','general','Permission for angsuran.delete','2026-04-29 17:41:31'),(66,'users.edit','Users edit','general','Permission for users.edit','2026-04-29 17:41:31'),(67,'users.delete','Users delete','general','Permission for users.delete','2026-04-29 17:41:31'),(68,'cabang.create','Cabang create','general','Permission for cabang.create','2026-04-29 17:41:31'),(69,'cabang.edit','Cabang edit','general','Permission for cabang.edit','2026-04-29 17:41:31'),(70,'cabang.delete','Cabang delete','general','Permission for cabang.delete','2026-04-29 17:41:31'),(71,'rute_harian.read','Rute_harian read','general','Permission for rute_harian.read','2026-04-29 17:41:31'),(72,'manage_app','Kelola Aplikasi','app','Akses pengelolaan level aplikasi','2026-05-02 14:44:02'),(73,'approve_bos','Approve Bos','app','Menyetujui pendaftaran Bos koperasi baru','2026-05-02 14:44:02'),(74,'view_koperasi','Lihat Koperasi','app','Melihat daftar semua koperasi terdaftar','2026-05-02 14:44:02'),(75,'suspend_koperasi','Suspend Koperasi','app','Menangguhkan koperasi','2026-05-02 14:44:02'),(76,'dashboard_analytics_view','View Dashboard Analytics','dashboard','View advanced dashboard analytics','2026-05-08 12:42:02'),(77,'dashboard_analytics_export','Export Dashboard Analytics','dashboard','Export dashboard analytics data','2026-05-08 12:42:02'),(78,'credit_scoring_view','View Credit Scoring','credit_scoring','View credit scoring information','2026-05-08 12:42:02'),(79,'credit_scoring_calculate','Calculate Credit Scores','credit_scoring','Calculate credit scores','2026-05-08 12:42:02'),(80,'credit_scoring_auto_approve','Auto-approve Pinjaman','credit_scoring','Auto-approve pinjaman based on credit score','2026-05-08 12:42:02'),(81,'gps_tracking_view','View GPS Tracking','gps','View GPS tracking data','2026-05-08 12:42:02'),(82,'gps_tracking_use','Use GPS Tracking','gps','Use GPS tracking for pembayaran','2026-05-08 12:42:02'),(83,'visits_view','View Petugas Visits','visits','View petugas visits','2026-05-08 12:42:02'),(84,'visits_create','Create Petugas Visits','visits','Create petugas visits','2026-05-08 12:42:02'),(85,'audit_log_view','View Audit Log','audit','View audit log','2026-05-08 12:42:02'),(86,'audit_log_export','Export Audit Log','audit','Export audit log','2026-05-08 12:42:02'),(87,'geographic_analysis_view','View Geographic Analysis','geographic','View geographic analysis','2026-05-08 12:42:02'),(88,'geographic_analysis_search','Geographic Radius Search','geographic','Use geographic radius search','2026-05-08 12:42:02'),(89,'sync_view','View Sync Status','sync','View sync status','2026-05-08 12:42:02'),(90,'sync_execute','Execute Data Sync','sync','Execute data sync','2026-05-08 12:42:02'),(91,'webhook_manage','Manage Webhooks','webhook','Manage webhooks','2026-05-08 12:42:02'),(92,'webhook_view','View Webhooks','webhook','View webhooks','2026-05-08 12:42:02'),(93,'external_api_view','View External API Logs','api','View external API logs','2026-05-08 12:42:02');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `petugas_daerah_tugas`
--

DROP TABLE IF EXISTS `petugas_daerah_tugas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `petugas_daerah_tugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `petugas_id` int(11) NOT NULL COMMENT 'FK users.id (petugas)',
  `cabang_id` int(11) DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL,
  `regency_id` int(10) unsigned DEFAULT NULL,
  `district_id` int(10) unsigned DEFAULT NULL,
  `village_id` bigint(20) unsigned DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL COMMENT 'nama area tugas',
  `is_active` tinyint(1) DEFAULT 1,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL COMMENT 'FK users.id (manager/bos)',
  PRIMARY KEY (`id`),
  KEY `idx_petugas` (`petugas_id`),
  KEY `idx_district` (`district_id`),
  KEY `idx_village` (`village_id`),
  KEY `idx_active` (`petugas_id`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Daerah tugas petugas lapangan';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `petugas_daerah_tugas`
--

LOCK TABLES `petugas_daerah_tugas` WRITE;
/*!40000 ALTER TABLE `petugas_daerah_tugas` DISABLE KEYS */;
INSERT INTO `petugas_daerah_tugas` VALUES (1,21,1,NULL,NULL,590,NULL,'Kecamatan Pangururan',1,'2026-05-02 15:36:24',1),(2,22,2,NULL,NULL,372,NULL,'Kecamatan Balige',1,'2026-05-02 15:36:24',1);
/*!40000 ALTER TABLE `petugas_daerah_tugas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pinjaman`
--

DROP TABLE IF EXISTS `pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `frekuensi_id` int(11) DEFAULT NULL,
  `produk_pinjaman_id` int(11) DEFAULT NULL,
  `kode_pinjaman` varchar(20) NOT NULL,
  `nasabah_id` int(11) NOT NULL,
  `plafon` decimal(12,2) NOT NULL,
  `tenor` int(11) NOT NULL COMMENT 'Tenor: harian (max 100), mingguan (max 52), bulanan (max 12)',
  `bunga_per_bulan` decimal(5,2) NOT NULL,
  `total_bunga` decimal(12,2) NOT NULL,
  `total_pembayaran` decimal(12,2) NOT NULL,
  `angsuran_pokok` decimal(12,2) NOT NULL,
  `angsuran_bunga` decimal(12,2) NOT NULL,
  `angsuran_total` decimal(12,2) NOT NULL,
  `tanggal_akad` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `tujuan_pinjaman` text DEFAULT NULL,
  `jaminan` text DEFAULT NULL,
  `jaminan_tipe` enum('tanpa','bpkb','shm','ajb','tabungan') DEFAULT 'tanpa',
  `jaminan_nilai` decimal(12,0) DEFAULT NULL,
  `jaminan_dokumen` varchar(255) DEFAULT NULL,
  `jaminan_status` enum('aktif','dilepas','terjual','hilang') DEFAULT 'aktif',
  `status` enum('pengajuan','disetujui','aktif','lunas','ditolak','macet') NOT NULL DEFAULT 'pengajuan',
  `pinjaman_induk_id` int(11) DEFAULT NULL COMMENT 'ID pinjaman lama (jika ini adalah restrukturisasi/refinancing)',
  `is_restrukturisasi` tinyint(1) NOT NULL DEFAULT 0,
  `sisa_pokok_berjalan` decimal(12,2) DEFAULT NULL COMMENT 'Sisa pokok real-time (diupdate saat bayar)',
  `total_denda_terhutang` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tanggal_lunas_awal` date DEFAULT NULL COMMENT 'Target lunas original sebelum reschedule',
  `approved_by` int(11) DEFAULT NULL COMMENT 'User yang menyetujui pinjaman',
  `approved_at` datetime DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `override_pinjaman_aktif` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Jika 1: bos/manager override larangan pinjaman ganda',
  `override_oleh` int(11) DEFAULT NULL,
  `override_alasan` text DEFAULT NULL,
  `tanggal_lunas` date DEFAULT NULL,
  `auto_confirmed` tinyint(1) DEFAULT 0,
  `auto_confirmed_at` timestamp NULL DEFAULT NULL,
  `auto_confirmed_by` int(11) DEFAULT NULL,
  `petugas_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `kolektibilitas` tinyint(1) DEFAULT 1 COMMENT '1=Lancar,2=DPK,3=KurangLancar,4=Diragukan,5=Macet',
  `hari_tunggakan` int(11) DEFAULT 0,
  `auto_approved` tinyint(1) DEFAULT 0 COMMENT 'Auto-approved flag',
  `approval_reason` text DEFAULT NULL COMMENT 'Reason for approval/rejection',
  `credit_score_at_approval` decimal(5,2) DEFAULT NULL COMMENT 'Credit score at time of approval',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_pinjaman` (`kode_pinjaman`),
  KEY `petugas_id` (`petugas_id`),
  KEY `idx_pinjaman_nasabah` (`nasabah_id`),
  KEY `idx_pinjaman_cabang_status` (`status`),
  KEY `idx_pinjaman_nasabah_status` (`nasabah_id`,`status`),
  KEY `auto_confirmed_by` (`auto_confirmed_by`),
  KEY `idx_pinjaman_frekuensi_status` (`status`),
  KEY `idx_pinjaman_status` (`status`),
  KEY `idx_pinjaman_petugas` (`petugas_id`),
  KEY `idx_pinjaman_tanggal_jatuh_tempo` (`tanggal_jatuh_tempo`),
  KEY `idx_pinjaman_status_jatuh_tempo` (`status`,`tanggal_jatuh_tempo`),
  KEY `fk_pinjaman_produk` (`produk_pinjaman_id`),
  KEY `fk_pinjaman_frekuensi` (`frekuensi_id`),
  CONSTRAINT `fk_pinjaman_frekuensi` FOREIGN KEY (`frekuensi_id`) REFERENCES `ref_frekuensi_angsuran` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_pinjaman_nasabah` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_pinjaman_produk` FOREIGN KEY (`produk_pinjaman_id`) REFERENCES `ref_produk_pinjaman` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `pinjaman_ibfk_2` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`),
  CONSTRAINT `pinjaman_ibfk_3` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`),
  CONSTRAINT `pinjaman_ibfk_4` FOREIGN KEY (`auto_confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pinjaman`
--

LOCK TABLES `pinjaman` WRITE;
/*!40000 ALTER TABLE `pinjaman` DISABLE KEYS */;
INSERT INTO `pinjaman` VALUES (1,1,3,5,'PNJ001',1,5000000.00,6,2.00,600000.00,5600000.00,833333.33,100000.00,933333.33,'2026-05-02','2026-11-02','Modal usaha warung','BPKB Motor','tanpa',NULL,NULL,'aktif','lunas',NULL,0,NULL,0.00,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,'2026-05-02',0,NULL,NULL,19,'2026-05-02 14:20:20','2026-05-07 17:16:07',1,0,0,NULL,NULL);
/*!40000 ALTER TABLE `pinjaman` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_auto_update_family_risk` AFTER UPDATE ON `pinjaman` FOR EACH ROW BEGIN
    DECLARE v_nasabah_id INT;
    DECLARE v_alamat TEXT;
    DECLARE v_cabang_id INT;
    DECLARE v_family_risk_id INT;
    DECLARE v_nama_nasabah VARCHAR(100);
    
    
    IF NEW.status = 'macet' AND OLD.status != 'macet' THEN
        
        SELECT nasabah_id, cabang_id INTO v_nasabah_id, v_cabang_id
        FROM pinjaman WHERE id = NEW.id;
        
        SELECT alamat, nama INTO v_alamat, v_nama_nasabah
        FROM nasabah WHERE id = v_nasabah_id;
        
        
        INSERT INTO loan_risk_log (cabang_id, nasabah_id, pinjaman_id, jenis_risiko, tingkat_risiko, deskripsi, tindakan_diambil, tanggal_kejadian)
        VALUES (v_cabang_id, v_nasabah_id, NEW.id, 'gagal_bayar', 'tinggi', 'Pinjaman gagal bayar', 'Auto-tagged as family risk', CURDATE());
        
        
        UPDATE nasabah 
        SET skor_risiko_keluarga = skor_risiko_keluarga + 10
        WHERE id = v_nasabah_id;
        
        
        SELECT id INTO v_family_risk_id FROM family_risk WHERE alamat_keluarga LIKE CONCAT('%', SUBSTRING_INDEX(v_alamat, ' ', 3), '%') AND cabang_id = v_cabang_id LIMIT 1;
        
        IF v_family_risk_id IS NULL THEN
            
            INSERT INTO family_risk (cabang_id, nama_kepala_keluarga, alamat_keluarga, tingkat_risiko, total_pinjaman_gagal, total_nasabah_bermasalah, tanggal_ditandai, alasan)
            VALUES (v_cabang_id, v_nama_nasabah, v_alamat, 'tinggi', 1, 1, CURDATE(), 'Pinjaman gagal bayar');
        ELSE
            
            UPDATE family_risk 
            SET total_pinjaman_gagal = total_pinjaman_gagal + 1,
                total_nasabah_bermasalah = total_nasabah_bermasalah + 1,
                tingkat_risiko = CASE 
                    WHEN total_pinjaman_gagal + 1 >= 3 THEN 'sangat_tinggi'
                    WHEN total_pinjaman_gagal + 1 = 2 THEN 'tinggi'
                    ELSE 'sedang'
                END,
                updated_at = NOW()
            WHERE id = v_family_risk_id;
        END IF;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pinjaman_jaminan`
--

DROP TABLE IF EXISTS `pinjaman_jaminan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pinjaman_jaminan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinjaman_id` int(11) NOT NULL,
  `jaminan_tipe_id` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `nilai_taksiran` decimal(12,0) DEFAULT 0,
  `nomor_dokumen` varchar(50) DEFAULT NULL,
  `file_dokumen` varchar(255) DEFAULT NULL,
  `status` enum('aktif','dilepas','terjual','hilang') DEFAULT 'aktif',
  `tanggal_dilepas` date DEFAULT NULL,
  `dilepas_oleh` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `dilepas_oleh` (`dilepas_oleh`),
  KEY `idx_pinjaman_id` (`pinjaman_id`),
  KEY `idx_jaminan_tipe_id` (`jaminan_tipe_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `pinjaman_jaminan_ibfk_1` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pinjaman_jaminan_ibfk_2` FOREIGN KEY (`jaminan_tipe_id`) REFERENCES `ref_jaminan_tipe` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `pinjaman_jaminan_ibfk_3` FOREIGN KEY (`dilepas_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pinjaman_jaminan`
--

LOCK TABLES `pinjaman_jaminan` WRITE;
/*!40000 ALTER TABLE `pinjaman_jaminan` DISABLE KEYS */;
/*!40000 ALTER TABLE `pinjaman_jaminan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platform_bank_accounts`
--

DROP TABLE IF EXISTS `platform_bank_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platform_bank_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_name` varchar(100) NOT NULL COMMENT 'Nama bank',
  `account_number` varchar(50) NOT NULL COMMENT 'Nomor rekening',
  `account_name` varchar(100) NOT NULL COMMENT 'Nama pemilik rekening',
  `tipe_pembayaran` enum('bank','mobile_banking','ewallet','qris','virtual_account') DEFAULT 'bank' COMMENT 'Tipe pembayaran',
  `nomor_rekening` varchar(50) DEFAULT NULL COMMENT 'Nomor rekening (untuk bank, mobile_banking, virtual_account)',
  `nomor_hp` varchar(20) DEFAULT NULL COMMENT 'Nomor HP (untuk ewallet)',
  `nama_pemilik` varchar(100) DEFAULT NULL COMMENT 'Nama pemilik rekening',
  `cabang` varchar(100) DEFAULT NULL COMMENT 'Nama cabang bank',
  `bank_code` varchar(10) DEFAULT NULL COMMENT 'Kode bank (misal: 014 untuk BCA)',
  `branch` varchar(100) DEFAULT NULL COMMENT 'Nama cabang bank (deprecated, use cabang)',
  `is_primary` tinyint(1) DEFAULT 0 COMMENT 'Apakah rekening utama',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Tanggal dibuat',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Tanggal diupdate',
  PRIMARY KEY (`id`),
  KEY `idx_primary_active` (`is_primary`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rekening bank platform untuk pembayaran';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platform_bank_accounts`
--

LOCK TABLES `platform_bank_accounts` WRITE;
/*!40000 ALTER TABLE `platform_bank_accounts` DISABLE KEYS */;
INSERT INTO `platform_bank_accounts` VALUES (1,'BCA','1234567890','Kewer Platform','bank','1234567890',NULL,'Kewer Platform','Jakarta Pusat','014','Jakarta Pusat',1,1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(2,'BCA','1234567890','Kewer Platform','bank','1234567890',NULL,'Kewer Platform','Jakarta Pusat','014','Jakarta Pusat',1,1,'2026-05-10 04:24:18','2026-05-10 04:24:18');
/*!40000 ALTER TABLE `platform_bank_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platform_features`
--

DROP TABLE IF EXISTS `platform_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platform_features` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feature_key` varchar(64) NOT NULL COMMENT 'identifier unik fitur',
  `label` varchar(128) NOT NULL COMMENT 'nama tampilan',
  `description` text DEFAULT NULL,
  `category` varchar(32) NOT NULL DEFAULT 'general' COMMENT 'wa|auth|pwa|laporan|lapangan|system',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `changed_by` int(10) unsigned DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `feature_key` (`feature_key`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platform_features`
--

LOCK TABLES `platform_features` WRITE;
/*!40000 ALTER TABLE `platform_features` DISABLE KEYS */;
INSERT INTO `platform_features` VALUES (1,'wa_notifikasi','In-App Notifikasi','Kirim notifikasi in-app via notification queue. Tidak memerlukan layanan eksternal.','wa',1,25,'2026-05-05 22:18:34','2026-05-04 01:35:33'),(2,'wa_pengingat_auto','Pengingat Otomatis','Cron harian kirim notifikasi pengingat H-3, H-1 dan H-0 jatuh tempo ke nasabah.','wa',1,NULL,NULL,'2026-05-04 01:35:33'),(3,'two_factor_auth','2FA Login (TOTP)','Autentikasi dua faktor untuk role bos/manager menggunakan Google Authenticator.','auth',0,25,'2026-05-05 22:20:48','2026-05-04 01:35:33'),(4,'pwa','PWA (Progressive Web App)','Service worker, manifest, install-to-homescreen, offline fallback.','pwa',0,25,'2026-05-05 22:21:15','2026-05-04 01:35:33'),(5,'gps_pembayaran','GPS pada Pembayaran','Rekam koordinat GPS saat petugas mencatat pembayaran di lapangan.','lapangan',0,NULL,NULL,'2026-05-04 01:35:33'),(6,'export_laporan','Export Laporan (CSV/PDF)','Tombol export di halaman laporan. PDF butuh library dompdf.','laporan',1,25,'2026-05-05 22:19:22','2026-05-04 01:35:33'),(7,'target_petugas','Target Kinerja Petugas','Set target bulanan kutipan/nasabah per petugas, tampil progress bar di kinerja.','lapangan',1,25,'2026-05-05 23:18:43','2026-05-04 01:35:33'),(8,'slip_harian','Slip Harian Petugas','Petugas bisa cetak rekap kutipan harian mereka sendiri.','lapangan',1,25,'2026-05-05 23:18:37','2026-05-04 01:35:33'),(9,'kolektibilitas','Kolektibilitas OJK (1-5)','Badge dan update otomatis level kolektibilitas pinjaman per standar OJK.','lapangan',0,NULL,NULL,'2026-05-04 01:35:33'),(10,'cron_harian','Cron Job Harian','Jalankan autoTandaiMacet, hitungDenda, dan notifikasi jatuh tempo tiap pagi.','system',1,25,'2026-05-05 23:18:49','2026-05-04 01:35:33'),(11,'simulasi_pinjaman','Simulasi Pinjaman Real-time','Preview angsuran dan jadwal amortisasi saat input pinjaman baru.','lapangan',1,NULL,NULL,'2026-05-04 01:35:33'),(23,'cross_koperasi_check','Cross Koperasi Check','Cek nasabah lintas koperasi (pending schema koperasi_master)','koperasi',0,NULL,NULL,'2026-05-10 10:00:28'),(24,'pembayaran_elektronik','Pembayaran Elektronik','QRIS/E-wallet/VA (pending schema koperasi_rekening)','pembayaran',0,NULL,NULL,'2026-05-10 10:00:28'),(25,'wa_notifikasi_queue','Notification Queue','Queue system untuk notifikasi in-app. Proses batch via cron.','wa',0,NULL,NULL,'2026-05-10 10:06:50');
/*!40000 ALTER TABLE `platform_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provinsi_activation`
--

DROP TABLE IF EXISTS `provinsi_activation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provinsi_activation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `province_id` varchar(20) NOT NULL COMMENT 'ID provinsi (mengacu ke db_alamat.province)',
  `province_name` varchar(100) NOT NULL COMMENT 'Nama provinsi',
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = aktif untuk pendaftaran, 0 = non-aktif',
  `activated_by` int(11) DEFAULT NULL COMMENT 'User ID yang mengaktifkan (FK ke users.id)',
  `activated_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu diaktifkan',
  `deactivated_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu dinonaktifkan terakhir',
  `notes` text DEFAULT NULL COMMENT 'Catatan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `province_id` (`province_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_activated_by` (`activated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Status aktivasi provinsi untuk pendaftaran koperasi (dikelola appOwner)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provinsi_activation`
--

LOCK TABLES `provinsi_activation` WRITE;
/*!40000 ALTER TABLE `provinsi_activation` DISABLE KEYS */;
INSERT INTO `provinsi_activation` VALUES (1,'12','SUMATERA UTARA',1,NULL,'2026-05-10 02:59:51',NULL,'Default aktif','2026-05-10 02:59:51','2026-05-10 02:59:51');
/*!40000 ALTER TABLE `provinsi_activation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_frekuensi_angsuran`
--

DROP TABLE IF EXISTS `ref_frekuensi_angsuran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_frekuensi_angsuran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `hari_per_periode` int(11) NOT NULL COMMENT 'Jumlah hari dalam satu periode',
  `tenor_default` int(11) NOT NULL COMMENT 'Tenor default untuk frekuensi ini',
  `tenor_min` int(11) NOT NULL COMMENT 'Tenor minimum',
  `tenor_max` int(11) NOT NULL COMMENT 'Tenor maximum',
  `urutan_tampil` int(11) DEFAULT 0,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `idx_status` (`status`),
  KEY `idx_urutan` (`urutan_tampil`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_frekuensi_angsuran`
--

LOCK TABLES `ref_frekuensi_angsuran` WRITE;
/*!40000 ALTER TABLE `ref_frekuensi_angsuran` DISABLE KEYS */;
INSERT INTO `ref_frekuensi_angsuran` VALUES (1,'HARIAN','Harian',1,30,1,100,1,'aktif','2026-05-07 17:09:03','2026-05-07 17:09:03'),(2,'MINGGUAN','Mingguan',7,12,1,52,2,'aktif','2026-05-07 17:09:03','2026-05-07 17:09:03'),(3,'BULANAN','Bulanan',30,12,1,36,3,'aktif','2026-05-07 17:09:03','2026-05-07 17:09:03');
/*!40000 ALTER TABLE `ref_frekuensi_angsuran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jaminan_tipe`
--

DROP TABLE IF EXISTS `ref_jaminan_tipe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jaminan_tipe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipe_kode` varchar(20) NOT NULL,
  `tipe_nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipe_kode` (`tipe_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jaminan_tipe`
--

LOCK TABLES `ref_jaminan_tipe` WRITE;
/*!40000 ALTER TABLE `ref_jaminan_tipe` DISABLE KEYS */;
INSERT INTO `ref_jaminan_tipe` VALUES (6,'JAM001','Tanpa Jaminan','Pinjaman tanpa jaminan','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(7,'JAM002','BPKB Kendaraan','Jaminan BPKB kendaraan bermotor','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(8,'JAM003','SHM Tanah/Rumah','Sertifikat Hak Milik tanah atau rumah','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(9,'JAM004','AJB','Akta Jual Beli','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(10,'JAM005','Tabungan','Jaminan tabungan','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(11,'JAM006','SK Kerja','Surat Keterangan Kerja','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(12,'JAM007','Lainnya','Jaminan lainnya','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42');
/*!40000 ALTER TABLE `ref_jaminan_tipe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_penagihan`
--

DROP TABLE IF EXISTS `ref_jenis_penagihan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jenis_penagihan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `urutan_tampil` int(11) DEFAULT 0,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `idx_status` (`status`),
  KEY `idx_urutan` (`urutan_tampil`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_penagihan`
--

LOCK TABLES `ref_jenis_penagihan` WRITE;
/*!40000 ALTER TABLE `ref_jenis_penagihan` DISABLE KEYS */;
INSERT INTO `ref_jenis_penagihan` VALUES (1,'JATUH_TEMPO','Jatuh Tempo','Penagihan rutin saat jatuh tempo',1,'aktif','2026-05-07 17:11:04','2026-05-07 17:11:04'),(2,'TELAT_1_7','Telat 1-7 Hari','Penagihan untuk keterlambatan 1-7 hari',2,'aktif','2026-05-07 17:11:04','2026-05-07 17:11:04'),(3,'TELAT_8_14','Telat 8-14 Hari','Penagihan untuk keterlambatan 8-14 hari',3,'aktif','2026-05-07 17:11:04','2026-05-07 17:11:04'),(4,'TELAT_15_30','Telat 15-30 Hari','Penagihan untuk keterlambatan 15-30 hari',4,'aktif','2026-05-07 17:11:04','2026-05-07 17:11:04'),(5,'TELAT_30_PLUS','Telat 30+ Hari','Penagihan untuk keterlambatan lebih dari 30 hari',5,'aktif','2026-05-07 17:11:04','2026-05-07 17:11:04'),(6,'MACET','Macet','Penagihan untuk pinjaman yang macet',6,'aktif','2026-05-07 17:11:04','2026-05-07 17:11:04'),(7,'FOLLOW_UP','Follow Up','Follow up setelah penagihan',7,'aktif','2026-05-07 17:11:04','2026-05-07 17:11:04');
/*!40000 ALTER TABLE `ref_jenis_penagihan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_usaha`
--

DROP TABLE IF EXISTS `ref_jenis_usaha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_jenis_usaha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jenis_kode` varchar(20) NOT NULL,
  `jenis_nama` varchar(50) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_kode` (`jenis_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_usaha`
--

LOCK TABLES `ref_jenis_usaha` WRITE;
/*!40000 ALTER TABLE `ref_jenis_usaha` DISABLE KEYS */;
INSERT INTO `ref_jenis_usaha` VALUES (7,'J001','Warung','Retail','Warung kelontong atau minimarket','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(8,'J002','Toko Sembako','Retail','Toko bahan pokok','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(9,'J003','Restoran','F&B','Restoran atau rumah makan','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(10,'J004','Kafe','F&B','Kafe atau kedai kopi','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(11,'J005','Pangkas Rambut','Jasa','Usaha pangkas rambut','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(12,'J006','Laundry','Jasa','Usaha laundry','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(13,'J007','Bengkel','Jasa','Bengkel motor atau mobil','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(14,'J008','Toko Elektronik','Retail','Toko elektronik','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(15,'J009','Toko Pakaian','Retail','Toko pakaian atau butik','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(16,'J010','Pedagang Kaki Lima','Retail','Pedagang kaki lima','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(17,'J011','Produksi Makanan','Produksi','Usaha produksi makanan','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(18,'J012','Lainnya','Lainnya','Jenis usaha lainnya','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41');
/*!40000 ALTER TABLE `ref_jenis_usaha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_kategori_pengeluaran`
--

DROP TABLE IF EXISTS `ref_kategori_pengeluaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_kategori_pengeluaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kategori_kode` varchar(20) NOT NULL,
  `kategori_nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kategori_kode` (`kategori_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_kategori_pengeluaran`
--

LOCK TABLES `ref_kategori_pengeluaran` WRITE;
/*!40000 ALTER TABLE `ref_kategori_pengeluaran` DISABLE KEYS */;
INSERT INTO `ref_kategori_pengeluaran` VALUES (7,'KAT001','Operasional','Pengeluaran operasional harian','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(8,'KAT002','Gaji','Penggajian karyawan','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(9,'KAT003','Sewa','Sewa tempat usaha','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(10,'KAT004','Listrik & Air','Tagihan listrik dan air','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(11,'KAT005','Transportasi','Pengeluaran transportasi','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(12,'KAT006','Peralatan','Pembelian peralatan','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(13,'KAT007','Marketing','Biaya marketing dan promosi','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(14,'KAT008','Lainnya','Pengeluaran lainnya','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43');
/*!40000 ALTER TABLE `ref_kategori_pengeluaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_metode_pembayaran`
--

DROP TABLE IF EXISTS `ref_metode_pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_metode_pembayaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metode_kode` varchar(20) NOT NULL,
  `metode_nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `metode_kode` (`metode_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_metode_pembayaran`
--

LOCK TABLES `ref_metode_pembayaran` WRITE;
/*!40000 ALTER TABLE `ref_metode_pembayaran` DISABLE KEYS */;
INSERT INTO `ref_metode_pembayaran` VALUES (4,'MET001','Tunai','Pembayaran tunai','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(5,'MET002','Transfer Bank','Transfer melalui bank','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(6,'MET003','QRIS','Pembayaran QRIS','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(7,'MET004','Debit','Kartu debit','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(8,'MET005','Kredit','Kartu kredit','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(9,'MET006','E-Wallet','Dompet elektronik','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(10,'MET007','Cek','Pembayaran dengan cek','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(11,'MET008','Lainnya','Metode pembayaran lainnya','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44');
/*!40000 ALTER TABLE `ref_metode_pembayaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_produk_pinjaman`
--

DROP TABLE IF EXISTS `ref_produk_pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_produk_pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `frekuensi_id` int(11) NOT NULL COMMENT 'Foreign key ke ref_frekuensi_angsuran',
  `tenor_min` int(11) NOT NULL COMMENT 'Tenor minimum dalam periode',
  `tenor_max` int(11) NOT NULL COMMENT 'Tenor maximum dalam periode',
  `jumlah_min` decimal(15,2) NOT NULL COMMENT 'Jumlah pinjaman minimum',
  `jumlah_max` decimal(15,2) NOT NULL COMMENT 'Jumlah pinjaman maximum',
  `bunga_default` decimal(5,2) NOT NULL COMMENT 'Bunga default dalam persen',
  `bunga_min` decimal(5,2) DEFAULT NULL COMMENT 'Bunga minimum dalam persen',
  `bunga_max` decimal(5,2) DEFAULT NULL COMMENT 'Bunga maximum dalam persen',
  `biaya_admin` decimal(15,2) DEFAULT 0.00 COMMENT 'Biaya administrasi nominal',
  `biaya_provisi` decimal(5,2) DEFAULT 0.00 COMMENT 'Biaya provisi dalam persen',
  `asuransi_wajib` tinyint(1) DEFAULT 0 COMMENT 'Apakah asuransi wajib',
  `jaminan_wajib` tinyint(1) DEFAULT 0 COMMENT 'Apakah jaminan wajib',
  `jaminan_tipe_id` int(11) DEFAULT NULL COMMENT 'Tipe jaminan yang wajib',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `idx_frekuensi_id` (`frekuensi_id`),
  KEY `idx_status` (`status`),
  KEY `idx_jaminan_tipe_id` (`jaminan_tipe_id`),
  CONSTRAINT `ref_produk_pinjaman_ibfk_1` FOREIGN KEY (`frekuensi_id`) REFERENCES `ref_frekuensi_angsuran` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ref_produk_pinjaman_ibfk_2` FOREIGN KEY (`jaminan_tipe_id`) REFERENCES `ref_jaminan_tipe` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_produk_pinjaman`
--

LOCK TABLES `ref_produk_pinjaman` WRITE;
/*!40000 ALTER TABLE `ref_produk_pinjaman` DISABLE KEYS */;
INSERT INTO `ref_produk_pinjaman` VALUES (3,'PIN_HARIAN','Pinjaman Harian','Pinjaman dengan angsuran harian untuk pedagang pasar',1,1,100,500000.00,5000000.00,1.50,1.00,2.50,5000.00,0.00,0,0,NULL,'aktif','2026-05-07 17:11:01','2026-05-07 17:11:01'),(4,'PIN_HARIAN_JAMINAN','Pinjaman Harian dengan Jaminan','Pinjaman harian dengan jaminan untuk plafon lebih tinggi',1,1,100,500000.00,10000000.00,1.20,0.80,2.00,10000.00,0.00,0,1,NULL,'aktif','2026-05-07 17:11:01','2026-05-07 17:11:01'),(5,'PIN_MINGGUAN','Pinjaman Mingguan','Pinjaman dengan angsuran mingguan',2,1,52,300000.00,10000000.00,1.00,0.50,1.50,10000.00,0.00,0,0,NULL,'aktif','2026-05-07 17:11:01','2026-05-07 17:11:01'),(6,'PIN_MINGGUAN_KEMAS','Pinjaman Mingguan Kemas','Pinjaman mingguan tenor 11 minggu (Koperasi Sentra Dana style)',2,11,11,300000.00,3500000.00,1.00,0.50,1.50,10000.00,0.00,0,0,NULL,'aktif','2026-05-07 17:11:01','2026-05-07 17:11:01'),(7,'PIN_MINGGUAN_ASA','Pinjaman Mingguan ASA','Pinjaman mingguan tenor 8 minggu (Koperasi ASA style)',2,8,8,300000.00,3500000.00,1.00,0.50,1.50,10000.00,0.00,0,0,NULL,'aktif','2026-05-07 17:11:01','2026-05-07 17:11:01'),(8,'PIN_BULANAN','Pinjaman Bulanan','Pinjaman dengan angsuran bulanan',3,1,36,500000.00,50000000.00,0.50,0.30,1.00,25000.00,1.00,1,1,NULL,'aktif','2026-05-07 17:11:01','2026-05-07 17:11:01');
/*!40000 ALTER TABLE `ref_produk_pinjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_roles`
--

DROP TABLE IF EXISTS `ref_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_kode` varchar(20) NOT NULL,
  `role_nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `urutan_tampil` int(11) DEFAULT 0,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_kode` (`role_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_roles`
--

LOCK TABLES `ref_roles` WRITE;
/*!40000 ALTER TABLE `ref_roles` DISABLE KEYS */;
INSERT INTO `ref_roles` VALUES (1,'bos','Bos','Pemilik usaha dengan akses penuh untuk pengawasan operasional dan keuangan',NULL,1,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(2,'manager_pusat','Manager Pusat','Manager di kantor pusat dengan akses manajemen operasional pusat',NULL,2,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(3,'manager_cabang','Manager Cabang','Manager cabang dengan akses manajemen operasional cabang',NULL,3,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(4,'admin_pusat','Admin Pusat','Admin di kantor pusat dengan akses administratif pusat',NULL,4,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(5,'admin_cabang','Admin Cabang','Admin cabang dengan akses administratif cabang',NULL,5,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(6,'petugas_pusat','Petugas Pusat','Petugas lapangan pusat untuk kunjungan nasabah dan penagihan',NULL,6,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(7,'petugas_cabang','Petugas Cabang','Petugas lapangan cabang untuk kunjungan nasabah dan penagihan',NULL,7,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(8,'teller','Teller','Teller dengan akses view-only data dan update kas reconciliation',NULL,8,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(9,'appOwner','App Owner','Pemilik aplikasi yang mengelola pendaftaran koperasi dan persetujuan Bos',NULL,0,'aktif','2026-05-02 14:44:02','2026-05-02 14:44:02');
/*!40000 ALTER TABLE `ref_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_status_pinjaman`
--

DROP TABLE IF EXISTS `ref_status_pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_status_pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_kode` varchar(20) NOT NULL,
  `status_nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `urutan_tampil` int(11) DEFAULT 0,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_kode` (`status_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_status_pinjaman`
--

LOCK TABLES `ref_status_pinjaman` WRITE;
/*!40000 ALTER TABLE `ref_status_pinjaman` DISABLE KEYS */;
INSERT INTO `ref_status_pinjaman` VALUES (7,'STS001','Pending','Pinjaman menunggu persetujuan',1,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45'),(8,'STS002','Disetujui','Pinjaman disetujui dan aktif',2,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45'),(9,'STS003','Ditolak','Pinjaman ditolak',3,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45'),(10,'STS004','Lunas','Pinjaman telah lunas',4,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45'),(11,'STS005','Blacklist','Pinjaman masuk blacklist',5,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45'),(12,'STS006','Batal','Pinjaman dibatalkan',6,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45');
/*!40000 ALTER TABLE `ref_status_pinjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restrukturisasi`
--

DROP TABLE IF EXISTS `restrukturisasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restrukturisasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinjaman_id` int(11) NOT NULL COMMENT 'Pinjaman yang direstrukturisasi',
  `pinjaman_baru_id` int(11) DEFAULT NULL COMMENT 'Pinjaman pengganti (jika tipe=refinancing)',
  `tipe` enum('reschedule','reconditioning','restructuring','refinancing') NOT NULL COMMENT 'reschedule=perpanjang tenor, reconditioning=ubah bunga, restructuring=kombinasi, refinancing=pinjaman baru',
  `alasan` enum('kesulitan_keuangan','bencana_alam','sakit','usaha_merugi','covid','lainnya') NOT NULL DEFAULT 'lainnya',
  `alasan_detail` text DEFAULT NULL,
  `sisa_pokok` decimal(12,2) NOT NULL COMMENT 'Sisa pokok saat restrukturisasi',
  `tenor_baru` int(11) DEFAULT NULL,
  `bunga_baru` decimal(5,2) DEFAULT NULL,
  `angsuran_baru` decimal(12,2) DEFAULT NULL,
  `tanggal_efektif` date NOT NULL,
  `denda_dibebaskan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `disetujui_oleh` int(11) NOT NULL,
  `status` enum('draft','disetujui','aktif','batal') NOT NULL DEFAULT 'draft',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_restruk_pinjaman` (`pinjaman_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Restrukturisasi pinjaman bermasalah';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restrukturisasi`
--

LOCK TABLES `restrukturisasi` WRITE;
/*!40000 ALTER TABLE `restrukturisasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `restrukturisasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riwayat_skor_kredit`
--

DROP TABLE IF EXISTS `riwayat_skor_kredit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riwayat_skor_kredit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `owner_bos_id` int(10) unsigned NOT NULL,
  `skor_sebelum` int(11) NOT NULL,
  `skor_sesudah` int(11) NOT NULL,
  `delta` int(11) NOT NULL COMMENT 'Positif=naik, Negatif=turun',
  `alasan` enum('bayar_tepat_waktu','bayar_telat','restrukturisasi','writeoff','blacklist','unblacklist','manual') NOT NULL,
  `referensi_id` int(11) DEFAULT NULL COMMENT 'ID pembayaran/pinjaman terkait',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_skor_nasabah` (`nasabah_id`),
  KEY `idx_skor_tanggal` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Riwayat perubahan skor kredit nasabah';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riwayat_skor_kredit`
--

LOCK TABLES `riwayat_skor_kredit` WRITE;
/*!40000 ALTER TABLE `riwayat_skor_kredit` DISABLE KEYS */;
/*!40000 ALTER TABLE `riwayat_skor_kredit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `permission_code` varchar(100) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_role` (`role`),
  KEY `idx_permission_code` (`permission_code`)
) ENGINE=InnoDB AUTO_INCREMENT=450 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (32,'bos','angsuran.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(33,'bos','angsuran.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(35,'bos','cabang.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(36,'bos','dashboard.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(37,'bos','kas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(38,'bos','kas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(39,'bos','kas_petugas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(40,'bos','kas_petugas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(41,'bos','manage_bunga',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(42,'bos','manage_cabang',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(43,'bos','manage_kas_bon',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(44,'bos','manage_nasabah',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(45,'bos','manage_pembayaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(46,'bos','manage_pengeluaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(47,'bos','manage_petugas',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(48,'bos','manage_pinjaman',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(49,'bos','manage_users',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(50,'bos','nasabah.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(51,'bos','pinjaman.approve',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(52,'bos','pinjaman.auto_confirm',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(53,'bos','pinjaman.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(54,'bos','users.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(55,'bos','users.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(56,'bos','view_kas_bon',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(57,'bos','view_laporan',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(58,'bos','view_pengeluaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(59,'bos','view_petugas',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(60,'bos','view_settings',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(63,'petugas_cabang','angsuran.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(64,'petugas_cabang','angsuran.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(65,'petugas_cabang','assign_permissions',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(66,'petugas_cabang','cabang.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(67,'petugas_cabang','dashboard.read',1,'2026-04-28 05:26:12','2026-05-02 13:29:23'),(68,'petugas_cabang','kas.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(69,'petugas_cabang','kas.update',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(70,'petugas_cabang','kas_petugas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(71,'petugas_cabang','kas_petugas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(72,'petugas_cabang','manage_bunga',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(73,'petugas_cabang','manage_cabang',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(74,'petugas_cabang','manage_kas_bon',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(75,'petugas_cabang','manage_nasabah',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(76,'petugas_cabang','manage_pembayaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(77,'petugas_cabang','manage_pengeluaran',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(78,'petugas_cabang','manage_petugas',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(79,'petugas_cabang','manage_pinjaman',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(80,'petugas_cabang','manage_users',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(82,'petugas_cabang','pinjaman.approve',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(83,'petugas_cabang','pinjaman.auto_confirm',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(85,'petugas_cabang','users.create',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(86,'petugas_cabang','users.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(87,'petugas_cabang','view_kas_bon',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(89,'petugas_cabang','view_pengeluaran',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(90,'petugas_cabang','view_petugas',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(91,'petugas_cabang','view_settings',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(108,'bos','nasabah.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(109,'bos','nasabah.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(110,'bos','nasabah.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(111,'bos','pinjaman.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(112,'bos','pinjaman.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(113,'bos','pinjaman.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(114,'bos','angsuran.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(115,'bos','angsuran.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(116,'bos','users.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(117,'bos','users.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(118,'bos','cabang.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(119,'bos','cabang.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(120,'bos','cabang.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(121,'bos','rute_harian.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(131,'manager_pusat','nasabah.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(132,'manager_pusat','nasabah.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(133,'manager_pusat','nasabah.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(134,'manager_pusat','nasabah.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(135,'manager_pusat','pinjaman.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(136,'manager_pusat','pinjaman.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(137,'manager_pusat','pinjaman.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(138,'manager_pusat','pinjaman.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(139,'manager_pusat','pinjaman.auto_confirm',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(140,'manager_pusat','angsuran.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(141,'manager_pusat','angsuran.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(142,'manager_pusat','angsuran.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(143,'manager_pusat','angsuran.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(144,'manager_pusat','users.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(145,'manager_pusat','users.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(146,'manager_pusat','users.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(147,'manager_pusat','users.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(148,'manager_pusat','cabang.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(149,'manager_pusat','cabang.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(150,'manager_pusat','cabang.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(151,'manager_pusat','cabang.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(152,'manager_pusat','manage_bunga',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(153,'manager_pusat','view_settings',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(154,'manager_pusat','manage_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(155,'manager_pusat','view_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(156,'manager_pusat','manage_kas_bon',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(157,'manager_pusat','view_kas_bon',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(158,'manager_pusat','view_laporan',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(159,'manager_pusat','manage_petugas',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(160,'manager_pusat','view_petugas',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(161,'manager_pusat','rute_harian.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(162,'manager_pusat','kas_petugas.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(163,'manager_pusat','kas.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(164,'manager_pusat','assign_permissions',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(165,'admin_pusat','nasabah.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(166,'admin_pusat','pinjaman.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(167,'admin_pusat','angsuran.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(168,'admin_pusat','cabang.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(169,'admin_pusat','view_laporan',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(170,'admin_pusat','view_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(171,'admin_pusat','view_kas_bon',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(172,'admin_pusat','manage_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(173,'admin_cabang','manage_nasabah',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(174,'admin_cabang','nasabah.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(175,'admin_cabang','nasabah.create',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(176,'admin_cabang','manage_pinjaman',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(177,'admin_cabang','pinjaman.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(178,'admin_cabang','pinjaman.create',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(179,'admin_cabang','angsuran.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(180,'admin_cabang','manage_pembayaran',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(181,'admin_cabang','kas.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(182,'admin_cabang','kas.update',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(183,'admin_cabang','view_pengeluaran',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(187,'teller','kas.read',1,'2026-04-30 15:10:58','2026-05-08 13:15:40'),(188,'teller','kas.update',1,'2026-04-30 15:10:58','2026-05-08 13:15:40'),(189,'teller','view_pengeluaran',1,'2026-04-30 15:10:58','2026-05-08 13:15:40'),(190,'manager_cabang','manage_nasabah',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(191,'manager_cabang','nasabah.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(192,'manager_cabang','manage_pinjaman',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(193,'manager_cabang','pinjaman.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(194,'manager_cabang','pinjaman.approve',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(195,'manager_cabang','angsuran.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(196,'manager_cabang','manage_pembayaran',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(197,'manager_cabang','kas_petugas.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(198,'manager_cabang','kas_petugas.update',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(199,'manager_cabang','kas.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(200,'manager_cabang','kas.update',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(202,'manager_cabang','view_laporan',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(203,'admin_pusat','manage_nasabah',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(204,'admin_pusat','nasabah.create',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(205,'admin_pusat','manage_pinjaman',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(206,'admin_pusat','pinjaman.create',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(207,'admin_pusat','pinjaman.approve',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(208,'admin_pusat','manage_pembayaran',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(209,'admin_pusat','kas.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(210,'admin_pusat','kas.update',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(213,'petugas_pusat','nasabah.read',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(214,'petugas_pusat','nasabah.create',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(215,'petugas_pusat','pinjaman.read',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(216,'petugas_pusat','pinjaman.create',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(217,'petugas_pusat','angsuran.read',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(218,'petugas_pusat','angsuran.create',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(219,'petugas_pusat','kas_petugas.read',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(220,'petugas_pusat','rute_harian.read',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(221,'petugas_pusat','view_laporan',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(222,'petugas_pusat','kas_petugas.create',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(223,'petugas_pusat','kas_petugas.update',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(228,'manager_pusat','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(229,'manager_cabang','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(230,'admin_pusat','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(231,'admin_cabang','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(232,'petugas_pusat','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(235,'petugas_cabang','nasabah.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(236,'petugas_cabang','nasabah.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(237,'petugas_cabang','pinjaman.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(238,'petugas_cabang','pinjaman.create',0,'2026-05-02 13:29:23','2026-05-03 17:11:43'),(239,'petugas_cabang','rute_harian.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(240,'petugas_cabang','view_laporan',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(241,'manager_cabang','nasabah.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(242,'manager_cabang','nasabah.edit',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(243,'manager_cabang','nasabah.delete',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(244,'manager_cabang','pinjaman.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(245,'manager_cabang','pinjaman.edit',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(246,'manager_cabang','angsuran.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(247,'manager_cabang','angsuran.edit',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(248,'manager_cabang','users.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(249,'manager_cabang','cabang.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(250,'manager_cabang','view_pengeluaran',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(251,'manager_cabang','view_kas_bon',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(252,'manager_cabang','manage_kas_bon',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(253,'manager_cabang','view_petugas',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(254,'manager_cabang','manage_petugas',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(255,'manager_cabang','rute_harian.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(256,'manager_cabang','view_settings',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(258,'admin_cabang','nasabah.edit',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(259,'admin_cabang','nasabah.delete',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(260,'admin_cabang','angsuran.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(261,'admin_cabang','cabang.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(262,'admin_cabang','view_kas_bon',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(263,'admin_cabang','view_petugas',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(265,'admin_cabang','rute_harian.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(267,'bos','assign_permissions',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(268,'petugas_pusat','manage_pembayaran',1,'2026-05-02 14:27:07','2026-05-02 14:27:07'),(269,'manager_pusat','manage_pembayaran',1,'2026-05-02 14:27:07','2026-05-02 14:27:07'),(270,'appOwner','manage_app',1,'2026-05-02 14:44:02','2026-05-02 14:44:02'),(271,'appOwner','approve_bos',1,'2026-05-02 14:44:02','2026-05-02 14:44:02'),(272,'appOwner','view_koperasi',1,'2026-05-02 14:44:02','2026-05-02 14:44:02'),(273,'appOwner','suspend_koperasi',1,'2026-05-02 14:44:02','2026-05-02 14:44:02'),(274,'manager_pusat','pinjaman.approve',1,'2026-05-03 17:11:43','2026-05-03 17:11:43'),(275,'bos','dashboard_analytics_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(276,'bos','dashboard_analytics_export',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(277,'bos','credit_scoring_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(278,'bos','credit_scoring_calculate',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(279,'bos','credit_scoring_auto_approve',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(280,'bos','gps_tracking_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(281,'bos','gps_tracking_use',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(282,'bos','visits_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(283,'bos','visits_create',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(284,'bos','audit_log_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(285,'bos','audit_log_export',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(286,'bos','geographic_analysis_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(287,'bos','geographic_analysis_search',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(288,'bos','sync_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(289,'bos','sync_execute',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(290,'bos','webhook_manage',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(291,'bos','webhook_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(292,'bos','external_api_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(293,'manager_pusat','dashboard_analytics_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(294,'manager_pusat','dashboard_analytics_export',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(295,'manager_pusat','credit_scoring_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(296,'manager_pusat','credit_scoring_calculate',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(297,'manager_pusat','gps_tracking_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(298,'manager_pusat','visits_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(299,'manager_pusat','audit_log_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(300,'manager_pusat','audit_log_export',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(301,'manager_pusat','geographic_analysis_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(302,'manager_pusat','geographic_analysis_search',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(303,'manager_pusat','sync_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(304,'manager_pusat','webhook_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(305,'manager_pusat','external_api_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(306,'manager_cabang','dashboard_analytics_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(307,'manager_cabang','credit_scoring_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(308,'manager_cabang','gps_tracking_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(309,'manager_cabang','visits_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(310,'manager_cabang','audit_log_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(311,'manager_cabang','geographic_analysis_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(312,'admin_pusat','dashboard_analytics_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(313,'admin_pusat','credit_scoring_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(314,'admin_pusat','gps_tracking_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(315,'admin_pusat','visits_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(316,'admin_pusat','audit_log_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(317,'admin_pusat','audit_log_export',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(318,'admin_pusat','external_api_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(319,'admin_cabang','dashboard_analytics_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(320,'admin_cabang','audit_log_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(321,'petugas_pusat','gps_tracking_use',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(322,'petugas_pusat','visits_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(323,'petugas_pusat','visits_create',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(324,'petugas_cabang','gps_tracking_use',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(325,'petugas_cabang','visits_view',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(326,'petugas_cabang','visits_create',1,'2026-05-08 12:42:28','2026-05-08 12:42:28'),(327,'teller','dashboard_analytics_view',1,'2026-05-08 12:42:28','2026-05-08 13:15:40'),(335,'teller','dashboard.read',1,'2026-05-08 13:15:05','2026-05-08 13:15:05'),(336,'teller','nasabah.read',1,'2026-05-08 13:15:05','2026-05-08 13:15:05'),(337,'teller','pinjaman.read',1,'2026-05-08 13:15:05','2026-05-08 13:15:05'),(338,'teller','angsuran.read',1,'2026-05-08 13:15:05','2026-05-08 13:15:05'),(339,'teller','angsuran.edit',1,'2026-05-08 13:15:05','2026-05-08 13:15:05'),(340,'teller','pembayaran.read',1,'2026-05-08 13:15:05','2026-05-08 13:15:05'),(341,'teller','pembayaran.create',1,'2026-05-08 13:15:05','2026-05-08 13:15:05'),(342,'teller','kas_petugas.read',1,'2026-05-08 13:15:05','2026-05-08 13:15:05'),(343,'teller','kas_petugas.update',1,'2026-05-08 13:15:05','2026-05-08 13:15:05'),(344,'bos','dashboard_analytics_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(345,'bos','dashboard_analytics_export',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(346,'bos','credit_scoring_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(347,'bos','credit_scoring_calculate',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(348,'bos','credit_scoring_auto_approve',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(349,'bos','gps_tracking_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(350,'bos','gps_tracking_use',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(351,'bos','visits_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(352,'bos','visits_create',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(353,'bos','audit_log_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(354,'bos','audit_log_export',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(355,'bos','geographic_analysis_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(356,'bos','geographic_analysis_search',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(357,'bos','sync_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(358,'bos','sync_execute',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(359,'bos','webhook_manage',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(360,'bos','webhook_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(361,'bos','external_api_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(362,'manager_pusat','dashboard_analytics_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(363,'manager_pusat','dashboard_analytics_export',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(364,'manager_pusat','credit_scoring_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(365,'manager_pusat','credit_scoring_calculate',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(366,'manager_pusat','gps_tracking_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(367,'manager_pusat','visits_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(368,'manager_pusat','audit_log_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(369,'manager_pusat','audit_log_export',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(370,'manager_pusat','geographic_analysis_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(371,'manager_pusat','geographic_analysis_search',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(372,'manager_pusat','sync_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(373,'manager_pusat','webhook_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(374,'manager_pusat','external_api_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(375,'manager_cabang','dashboard_analytics_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(376,'manager_cabang','credit_scoring_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(377,'manager_cabang','gps_tracking_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(378,'manager_cabang','visits_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(379,'manager_cabang','audit_log_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(380,'manager_cabang','geographic_analysis_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(381,'admin_pusat','dashboard_analytics_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(382,'admin_pusat','credit_scoring_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(383,'admin_pusat','gps_tracking_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(384,'admin_pusat','visits_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(385,'admin_pusat','audit_log_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(386,'admin_pusat','audit_log_export',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(387,'admin_pusat','external_api_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(388,'admin_cabang','dashboard_analytics_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(389,'admin_cabang','audit_log_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(390,'petugas_pusat','gps_tracking_use',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(391,'petugas_pusat','visits_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(392,'petugas_pusat','visits_create',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(393,'petugas_cabang','gps_tracking_use',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(394,'petugas_cabang','visits_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(395,'petugas_cabang','visits_create',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(396,'karyawan','dashboard_analytics_view',1,'2026-05-10 02:50:41','2026-05-10 02:50:41'),(397,'bos','dashboard_analytics_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(398,'bos','dashboard_analytics_export',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(399,'bos','credit_scoring_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(400,'bos','credit_scoring_calculate',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(401,'bos','credit_scoring_auto_approve',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(402,'bos','gps_tracking_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(403,'bos','gps_tracking_use',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(404,'bos','visits_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(405,'bos','visits_create',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(406,'bos','audit_log_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(407,'bos','audit_log_export',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(408,'bos','geographic_analysis_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(409,'bos','geographic_analysis_search',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(410,'bos','sync_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(411,'bos','sync_execute',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(412,'bos','webhook_manage',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(413,'bos','webhook_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(414,'bos','external_api_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(415,'manager_pusat','dashboard_analytics_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(416,'manager_pusat','dashboard_analytics_export',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(417,'manager_pusat','credit_scoring_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(418,'manager_pusat','credit_scoring_calculate',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(419,'manager_pusat','gps_tracking_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(420,'manager_pusat','visits_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(421,'manager_pusat','audit_log_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(422,'manager_pusat','audit_log_export',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(423,'manager_pusat','geographic_analysis_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(424,'manager_pusat','geographic_analysis_search',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(425,'manager_pusat','sync_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(426,'manager_pusat','webhook_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(427,'manager_pusat','external_api_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(428,'manager_cabang','dashboard_analytics_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(429,'manager_cabang','credit_scoring_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(430,'manager_cabang','gps_tracking_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(431,'manager_cabang','visits_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(432,'manager_cabang','audit_log_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(433,'manager_cabang','geographic_analysis_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(434,'admin_pusat','dashboard_analytics_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(435,'admin_pusat','credit_scoring_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(436,'admin_pusat','gps_tracking_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(437,'admin_pusat','visits_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(438,'admin_pusat','audit_log_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(439,'admin_pusat','audit_log_export',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(440,'admin_pusat','external_api_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(441,'admin_cabang','dashboard_analytics_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(442,'admin_cabang','audit_log_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(443,'petugas_pusat','gps_tracking_use',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(444,'petugas_pusat','visits_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(445,'petugas_pusat','visits_create',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(446,'petugas_cabang','gps_tracking_use',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(447,'petugas_cabang','visits_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(448,'petugas_cabang','visits_create',1,'2026-05-10 04:24:14','2026-05-10 04:24:14'),(449,'karyawan','dashboard_analytics_view',1,'2026-05-10 04:24:14','2026-05-10 04:24:14');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_bunga`
--

DROP TABLE IF EXISTS `setting_bunga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting_bunga` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frekuensi_id` int(11) DEFAULT NULL,
  `cabang_id` int(11) DEFAULT NULL,
  `jenis_pinjaman` varchar(50) NOT NULL,
  `tenor_min` int(11) NOT NULL DEFAULT 1,
  `tenor_max` int(11) NOT NULL DEFAULT 24,
  `bunga_default` decimal(5,2) NOT NULL,
  `bunga_min` decimal(5,2) NOT NULL,
  `bunga_max` decimal(5,2) NOT NULL,
  `faktor_risiko` decimal(5,2) DEFAULT 0.00,
  `jaminan_adjustment` decimal(5,2) DEFAULT 0.00,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_setting_bunga_frekuensi` (`frekuensi_id`),
  CONSTRAINT `fk_setting_bunga_frekuensi` FOREIGN KEY (`frekuensi_id`) REFERENCES `ref_frekuensi_angsuran` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_bunga`
--

LOCK TABLES `setting_bunga` WRITE;
/*!40000 ALTER TABLE `setting_bunga` DISABLE KEYS */;
INSERT INTO `setting_bunga` VALUES (1,1,NULL,'umum',1,100,0.50,0.30,1.00,0.00,0.00,'aktif','2026-05-07 17:41:38','2026-05-07 17:41:38'),(2,2,NULL,'umum',1,52,1.00,0.50,2.00,0.00,0.00,'aktif','2026-05-07 17:41:38','2026-05-07 17:41:38'),(3,3,NULL,'umum',1,36,2.00,1.00,3.00,0.00,0.00,'aktif','2026-05-07 17:41:38','2026-05-07 17:41:38');
/*!40000 ALTER TABLE `setting_bunga` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_denda`
--

DROP TABLE IF EXISTS `setting_denda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting_denda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frekuensi_id` int(11) DEFAULT NULL,
  `cabang_id` int(11) DEFAULT NULL,
  `tipe_denda` enum('persentase','nominal_tetap') NOT NULL DEFAULT 'persentase',
  `nilai_denda` decimal(8,4) NOT NULL DEFAULT 0.5000 COMMENT 'Persentase per hari (%) atau nominal tetap',
  `denda_maksimal` decimal(12,2) DEFAULT NULL COMMENT 'Batas maksimal denda (NULL = tidak ada batas)',
  `grace_period` int(11) NOT NULL DEFAULT 0 COMMENT 'Masa toleransi dalam hari',
  `bisa_waive` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Apakah denda bisa di-waive oleh manager/owner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `fk_setting_denda_frekuensi` (`frekuensi_id`),
  CONSTRAINT `fk_setting_denda_frekuensi` FOREIGN KEY (`frekuensi_id`) REFERENCES `ref_frekuensi_angsuran` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_denda`
--

LOCK TABLES `setting_denda` WRITE;
/*!40000 ALTER TABLE `setting_denda` DISABLE KEYS */;
INSERT INTO `setting_denda` VALUES (1,1,NULL,'persentase',0.1000,100000.00,0,1,'2026-05-07 17:41:38','2026-05-07 17:41:38',NULL,NULL,1),(2,2,NULL,'persentase',0.0500,200000.00,3,1,'2026-05-07 17:41:38','2026-05-07 17:41:38',NULL,NULL,1),(3,3,NULL,'persentase',0.0300,500000.00,7,1,'2026-05-07 17:41:38','2026-05-07 17:41:38',NULL,NULL,1);
/*!40000 ALTER TABLE `setting_denda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (20,'denda_harian_percent','0.5','Denda harian dalam persen','2026-04-30 14:39:53','2026-04-30 14:39:53'),(21,'denda_mingguan_percent','1.0','Denda mingguan dalam persen','2026-04-30 14:39:53','2026-04-30 14:39:53'),(22,'denda_bulanan_percent','2.0','Denda bulanan dalam persen','2026-04-30 14:39:53','2026-04-30 14:39:53'),(23,'denda_grace_period_days','3','Masa toleransi keterlambatan dalam hari','2026-04-30 14:39:53','2026-04-30 14:39:53');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sync_conflicts`
--

DROP TABLE IF EXISTS `sync_conflicts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_conflicts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) NOT NULL,
  `conflict_type` varchar(50) NOT NULL COMMENT 'version_mismatch, data_mismatch, delete_conflict',
  `local_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Local version of data' CHECK (json_valid(`local_data`)),
  `remote_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Remote version of data' CHECK (json_valid(`remote_data`)),
  `resolved` tinyint(1) DEFAULT 0 COMMENT 'Whether conflict is resolved',
  `resolution` varchar(50) DEFAULT NULL COMMENT 'keep_local, keep_remote, merge',
  `resolved_by` int(11) DEFAULT NULL COMMENT 'User ID who resolved',
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `resolved_by` (`resolved_by`),
  KEY `idx_cabang_id` (`cabang_id`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_resolved` (`resolved`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `sync_conflicts_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sync_conflicts_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sync conflict tracking';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_conflicts`
--

LOCK TABLES `sync_conflicts` WRITE;
/*!40000 ALTER TABLE `sync_conflicts` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_conflicts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sync_logs`
--

DROP TABLE IF EXISTS `sync_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `direction` varchar(20) NOT NULL COMMENT 'to_central, from_central',
  `sync_type` varchar(20) NOT NULL COMMENT 'full, incremental',
  `status` varchar(20) NOT NULL COMMENT 'started, completed, failed',
  `table_name` varchar(100) DEFAULT NULL COMMENT 'Table being synced (if single table sync)',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Sync details including counts, conflicts, etc.' CHECK (json_valid(`details`)),
  `started_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cabang_id` (`cabang_id`),
  KEY `idx_status` (`status`),
  KEY `idx_started_at` (`started_at`),
  CONSTRAINT `sync_logs_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Data synchronization logs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_logs`
--

LOCK TABLES `sync_logs` WRITE;
/*!40000 ALTER TABLE `sync_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `target_petugas`
--

DROP TABLE IF EXISTS `target_petugas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `target_petugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `petugas_id` int(11) NOT NULL,
  `bulan` varchar(7) NOT NULL COMMENT 'Format: YYYY-MM',
  `target_kutipan` decimal(15,2) DEFAULT 0.00,
  `target_nasabah_baru` int(11) DEFAULT 0,
  `target_pinjaman_baru` int(11) DEFAULT 0,
  `target_collection_rate` decimal(5,2) DEFAULT 90.00,
  `catatan` text DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_target` (`petugas_id`,`bulan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `target_petugas`
--

LOCK TABLES `target_petugas` WRITE;
/*!40000 ALTER TABLE `target_petugas` DISABLE KEYS */;
/*!40000 ALTER TABLE `target_petugas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi_log`
--

DROP TABLE IF EXISTS `transaksi_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaksi_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `nomor_transaksi` varchar(50) NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `tipe_transaksi` enum('pinjaman','angsuran','pembayaran','pengeluaran','kas_masuk','kas_keluar','kas_bon','kas_setoran','rekonsiliasi') NOT NULL,
  `jumlah` decimal(20,2) NOT NULL,
  `nasabah_id` int(11) DEFAULT NULL,
  `pinjaman_id` int(11) DEFAULT NULL,
  `angsuran_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('pending','posted','void') DEFAULT 'pending',
  `jurnal_id` int(11) DEFAULT NULL COMMENT 'Reference to journal entry',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_transaksi` (`nomor_transaksi`),
  KEY `idx_nomor_transaksi` (`nomor_transaksi`),
  KEY `idx_tanggal` (`tanggal_transaksi`),
  KEY `idx_tipe` (`tipe_transaksi`),
  KEY `idx_nasabah` (`nasabah_id`),
  KEY `idx_pinjaman` (`pinjaman_id`),
  KEY `jurnal_id` (`jurnal_id`),
  CONSTRAINT `transaksi_log_ibfk_1` FOREIGN KEY (`jurnal_id`) REFERENCES `jurnal` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_log`
--

LOCK TABLES `transaksi_log` WRITE;
/*!40000 ALTER TABLE `transaksi_log` DISABLE KEYS */;
INSERT INTO `transaksi_log` VALUES (1,1,'PINJ-20260502-001-0001','2026-05-02','pinjaman',5000000.00,1,1,NULL,1,'Pencairan pinjaman PNJ001','posted',1,'2026-05-02 14:20:40','2026-05-02 14:20:40'),(2,1,'PINJ-20260508-001-0001','2026-05-08','pinjaman',5000000.00,11,2,NULL,1,'Pencairan pinjaman PNJ002','posted',2,'2026-05-08 13:16:02','2026-05-08 13:16:02'),(3,1,'PINJ-20260508-001-0002','2026-05-08','pinjaman',5000000.00,14,3,NULL,1,'Pencairan pinjaman PNJ002','posted',3,'2026-05-08 13:16:37','2026-05-08 13:16:37');
/*!40000 ALTER TABLE `transaksi_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usage_daily_summary`
--

DROP TABLE IF EXISTS `usage_daily_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_daily_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bos_user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `total_api_calls` int(11) DEFAULT 0,
  `total_renders` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bos_tanggal` (`bos_user_id`,`tanggal`)
) ENGINE=InnoDB AUTO_INCREMENT=4032 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_daily_summary`
--

LOCK TABLES `usage_daily_summary` WRITE;
/*!40000 ALTER TABLE `usage_daily_summary` DISABLE KEYS */;
INSERT INTO `usage_daily_summary` VALUES (1,1,'2026-05-02',22,96,'2026-05-02 15:13:20','2026-05-02 15:37:06'),(33,26,'2026-05-02',0,5,'2026-05-02 15:14:41','2026-05-02 15:14:41'),(124,1,'2026-05-03',17,214,'2026-05-03 18:46:03','2026-05-03 19:02:19'),(355,1,'2026-05-05',128,1192,'2026-05-05 15:17:30','2026-05-05 17:01:34'),(808,27,'2026-05-05',0,43,'2026-05-05 16:19:10','2026-05-05 16:24:46'),(1718,1,'2026-05-08',85,320,'2026-05-08 12:47:07','2026-05-08 13:16:41'),(2123,1,'2026-05-10',0,1909,'2026-05-10 02:48:00','2026-05-10 03:01:22');
/*!40000 ALTER TABLE `usage_daily_summary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usage_log`
--

DROP TABLE IF EXISTS `usage_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bos_user_id` int(11) NOT NULL COMMENT 'owner bos of the user',
  `user_id` int(11) NOT NULL,
  `tipe` enum('api_call','page_render') NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) DEFAULT 'GET',
  `response_code` int(11) DEFAULT 200,
  `tanggal` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bos_tanggal` (`bos_user_id`,`tanggal`),
  KEY `idx_tanggal` (`tanggal`)
) ENGINE=InnoDB AUTO_INCREMENT=4032 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_log`
--

LOCK TABLES `usage_log` WRITE;
/*!40000 ALTER TABLE `usage_log` DISABLE KEYS */;
INSERT INTO `usage_log` VALUES (1,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:13:20'),(2,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:13:20'),(3,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:13:20'),(4,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-02','2026-05-02 15:13:20'),(5,1,1,'api_call','/kewer/api/bos_registration.php','POST',200,'2026-05-02','2026-05-02 15:14:39'),(6,1,1,'api_call','/kewer/api/bos_registration.php','POST',200,'2026-05-02','2026-05-02 15:14:39'),(7,1,1,'api_call','/kewer/api/bos_registration.php','POST',200,'2026-05-02','2026-05-02 15:14:39'),(8,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(9,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(10,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(11,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(12,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(13,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(14,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(15,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(16,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(17,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(18,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(19,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(20,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(21,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(22,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(23,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(24,1,21,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(25,1,21,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(26,1,21,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(27,1,21,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(28,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(29,1,23,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(30,1,23,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(31,1,23,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(32,1,23,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(33,26,26,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(34,26,26,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(35,26,26,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(36,26,26,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(37,26,26,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(38,1,1,'api_call','/kewer/api/bos_registration.php','POST',200,'2026-05-02','2026-05-02 15:14:56'),(39,1,1,'api_call','/kewer/api/bos_registration.php','POST',200,'2026-05-02','2026-05-02 15:14:56'),(40,1,1,'api_call','/kewer/api/bos_registration.php','GET',200,'2026-05-02','2026-05-02 15:14:56'),(41,1,1,'api_call','/kewer/api/bos_registration.php','GET',200,'2026-05-02','2026-05-02 15:14:56'),(42,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(43,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(44,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(45,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(46,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(47,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(48,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(49,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(50,1,1,'api_call','/kewer/api/alamat.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(51,1,1,'api_call','/kewer/api/alamat.php','GET',200,'2026-05-02','2026-05-02 15:30:13'),(52,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(53,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(54,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(55,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(56,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(57,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(58,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(59,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(60,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(61,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(62,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(63,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(64,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(65,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(66,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(67,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(68,1,2,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(69,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(70,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(71,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(72,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(73,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(74,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(75,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(76,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(77,1,19,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(78,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(79,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(80,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(81,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(82,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(83,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(84,1,1,'api_call','/kewer/api/kas_bon.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(85,1,1,'api_call','/kewer/api/pengeluaran.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(86,1,1,'api_call','/kewer/api/accounting.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(87,1,1,'api_call','/kewer/api/family_risk.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(88,1,1,'api_call','/kewer/api/kas_petugas.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(89,1,1,'api_call','/kewer/api/setting_bunga.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(90,1,1,'api_call','/kewer/api/nasabah_blacklist.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(91,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(92,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(93,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(94,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(95,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(96,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(97,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(98,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(99,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(100,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(101,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(102,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(103,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(104,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(105,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(106,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(107,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(108,1,2,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(109,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(110,1,2,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(111,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(112,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(113,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(114,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(115,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(116,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(117,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(118,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(119,1,19,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(120,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:37:06'),(121,1,19,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:06'),(122,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:06'),(123,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-02','2026-05-02 15:37:06'),(124,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:46:03'),(125,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:46:04'),(126,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:46:11'),(127,1,2,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:46:12'),(128,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:46:20'),(129,1,18,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:46:21'),(130,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:46:28'),(131,1,19,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:46:29'),(132,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:46:36'),(133,1,20,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:46:37'),(134,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:46:44'),(135,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:46:45'),(136,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:46:52'),(137,1,22,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:46:53'),(138,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:47:00'),(139,1,23,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:47:01'),(140,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:47:20'),(141,1,1,'api_call','/kewer/api/feature_flags.php','POST',200,'2026-05-03','2026-05-03 18:47:20'),(142,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:47:21'),(143,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:47:34'),(144,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-03','2026-05-03 18:47:35'),(145,1,1,'api_call','/kewer/api/wa_notifikasi.php','GET',200,'2026-05-03','2026-05-03 18:47:35'),(146,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:47:35'),(147,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:47:48'),(148,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-03','2026-05-03 18:47:49'),(149,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-03','2026-05-03 18:47:51'),(150,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:47:51'),(151,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:48:04'),(152,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-03','2026-05-03 18:48:05'),(153,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:48:07'),(154,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:48:21'),(155,1,21,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-03','2026-05-03 18:48:22'),(156,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:48:24'),(157,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:48:38'),(158,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 18:48:39'),(159,1,1,'api_call','/kewer/api/target_petugas.php','GET',200,'2026-05-03','2026-05-03 18:48:41'),(160,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:48:41'),(161,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:48:54'),(162,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-03','2026-05-03 18:48:55'),(163,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:48:56'),(164,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:09'),(165,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-03','2026-05-03 18:49:10'),(166,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:10'),(167,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:49:11'),(168,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:25'),(169,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:25'),(170,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:25'),(171,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-03','2026-05-03 18:49:25'),(172,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:26'),(173,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:26'),(174,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:49:27'),(175,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:39'),(176,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:39'),(177,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:49:41'),(178,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:46'),(179,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-03','2026-05-03 18:49:47'),(180,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:47'),(181,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:47'),(182,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-03','2026-05-03 18:49:49'),(183,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:49:51'),(184,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:57'),(185,1,23,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 18:49:58'),(186,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:49:58'),(187,1,23,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:49:59'),(188,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:50:06'),(189,1,21,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 18:50:07'),(190,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:50:09'),(191,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:50:16'),(192,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 18:50:16'),(193,1,2,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:50:18'),(194,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:52:01'),(195,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:52:02'),(196,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:52:09'),(197,1,2,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:52:10'),(198,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:52:17'),(199,1,18,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:52:18'),(200,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:52:25'),(201,1,19,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:52:26'),(202,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:52:33'),(203,1,20,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:52:34'),(204,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:52:42'),(205,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:52:43'),(206,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:52:50'),(207,1,22,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:52:51'),(208,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:52:58'),(209,1,23,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:52:59'),(210,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:53:20'),(211,1,1,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-03','2026-05-03 18:53:20'),(212,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:53:20'),(213,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:53:21'),(214,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:53:28'),(215,1,1,'api_call','/kewer/api/feature_flags.php','POST',200,'2026-05-03','2026-05-03 18:53:29'),(216,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:53:29'),(217,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:53:42'),(218,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-03','2026-05-03 18:53:43'),(219,1,1,'api_call','/kewer/api/wa_notifikasi.php','POST',200,'2026-05-03','2026-05-03 18:53:43'),(220,1,1,'api_call','/kewer/api/auth_2fa.php','GET',200,'2026-05-03','2026-05-03 18:53:43'),(221,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:53:43'),(222,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:53:57'),(223,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-03','2026-05-03 18:53:58'),(224,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-03','2026-05-03 18:53:59'),(225,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:54:00'),(226,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:54:11'),(227,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-03','2026-05-03 18:54:12'),(228,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:54:14'),(229,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:54:28'),(230,1,21,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-03','2026-05-03 18:54:28'),(231,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:54:30'),(232,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:54:44'),(233,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 18:54:45'),(234,1,1,'api_call','/kewer/api/target_petugas.php','GET',200,'2026-05-03','2026-05-03 18:54:47'),(235,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:54:47'),(236,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:54:59'),(237,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 18:55:00'),(238,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:55:01'),(239,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:55:15'),(240,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-03','2026-05-03 18:55:16'),(241,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:55:18'),(242,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:55:30'),(243,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-03','2026-05-03 18:55:31'),(244,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:55:31'),(245,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:55:32'),(246,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:55:44'),(247,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:55:44'),(248,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-03','2026-05-03 18:55:44'),(249,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:55:44'),(250,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:55:45'),(251,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:55:45'),(252,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:55:46'),(253,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:55:58'),(254,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:55:59'),(255,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:56:00'),(256,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:56:05'),(257,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-03','2026-05-03 18:56:06'),(258,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:56:06'),(259,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:56:06'),(260,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-03','2026-05-03 18:56:08'),(261,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:56:10'),(262,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:56:17'),(263,1,23,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 18:56:17'),(264,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:56:17'),(265,1,23,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:56:18'),(266,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:56:25'),(267,1,21,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 18:56:26'),(268,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:56:28'),(269,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:56:35'),(270,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 18:56:36'),(271,1,2,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:56:37'),(272,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:57:27'),(273,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:57:28'),(274,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:57:35'),(275,1,2,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:57:36'),(276,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:57:43'),(277,1,18,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:57:44'),(278,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:57:51'),(279,1,19,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:57:52'),(280,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:58:00'),(281,1,20,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:58:01'),(282,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:58:08'),(283,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:58:09'),(284,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:58:16'),(285,1,22,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:58:17'),(286,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:58:24'),(287,1,23,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:58:25'),(288,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:58:47'),(289,1,1,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-03','2026-05-03 18:58:48'),(290,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:58:48'),(291,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:58:49'),(292,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:58:56'),(293,1,1,'api_call','/kewer/api/feature_flags.php','POST',200,'2026-05-03','2026-05-03 18:58:56'),(294,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:58:57'),(295,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:59:10'),(296,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-03','2026-05-03 18:59:11'),(297,1,1,'api_call','/kewer/api/wa_notifikasi.php','POST',200,'2026-05-03','2026-05-03 18:59:11'),(298,1,1,'api_call','/kewer/api/auth_2fa.php','GET',200,'2026-05-03','2026-05-03 18:59:11'),(299,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:59:11'),(300,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:59:24'),(301,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-03','2026-05-03 18:59:25'),(302,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-03','2026-05-03 18:59:27'),(303,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:59:27'),(304,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:59:39'),(305,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-03','2026-05-03 18:59:40'),(306,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:59:42'),(307,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:59:55'),(308,1,21,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-03','2026-05-03 18:59:56'),(309,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 18:59:57'),(310,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 18:59:59'),(311,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:00:11'),(312,1,21,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-03','2026-05-03 19:00:12'),(313,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:00:12'),(314,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:00:13'),(315,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:00:25'),(316,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 19:00:26'),(317,1,1,'api_call','/kewer/api/target_petugas.php','GET',200,'2026-05-03','2026-05-03 19:00:28'),(318,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:00:28'),(319,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:00:40'),(320,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 19:00:41'),(321,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:00:43'),(322,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:00:56'),(323,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-03','2026-05-03 19:00:57'),(324,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:00:58'),(325,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:11'),(326,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-03','2026-05-03 19:01:11'),(327,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:12'),(328,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:01:12'),(329,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:25'),(330,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:25'),(331,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:25'),(332,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-03','2026-05-03 19:01:25'),(333,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:26'),(334,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:26'),(335,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:01:27'),(336,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:39'),(337,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:40'),(338,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:01:41'),(339,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:47'),(340,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-03','2026-05-03 19:01:48'),(341,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:48'),(342,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:48'),(343,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-03','2026-05-03 19:01:50'),(344,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:01:53'),(345,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:01:59'),(346,1,23,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 19:02:00'),(347,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:02:00'),(348,1,23,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:02:01'),(349,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:02:08'),(350,1,21,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 19:02:09'),(351,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:02:11'),(352,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-03','2026-05-03 19:02:17'),(353,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-03','2026-05-03 19:02:18'),(354,1,2,'page_render','/kewer/logout.php','GET',200,'2026-05-03','2026-05-03 19:02:19'),(355,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:17:30'),(356,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:17:31'),(357,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:17:37'),(358,1,2,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:17:38'),(359,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:17:44'),(360,1,18,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:17:45'),(361,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:17:51'),(362,1,19,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:17:52'),(363,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:17:58'),(364,1,20,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:17:59'),(365,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:18:05'),(366,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:18:06'),(367,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:18:13'),(368,1,22,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:18:14'),(369,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:18:20'),(370,1,23,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:18:21'),(371,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:18:40'),(372,1,1,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-05','2026-05-05 15:18:41'),(373,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:18:41'),(374,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:18:42'),(375,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:18:47'),(376,1,1,'api_call','/kewer/api/feature_flags.php','POST',200,'2026-05-05','2026-05-05 15:18:47'),(377,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:18:47'),(378,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:19:00'),(379,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-05','2026-05-05 15:19:00'),(380,1,1,'api_call','/kewer/api/wa_notifikasi.php','POST',200,'2026-05-05','2026-05-05 15:19:00'),(381,1,1,'api_call','/kewer/api/auth_2fa.php','GET',200,'2026-05-05','2026-05-05 15:19:01'),(382,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:19:01'),(383,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:19:13'),(384,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 15:19:14'),(385,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-05','2026-05-05 15:19:15'),(386,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:19:15'),(387,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:19:27'),(388,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 15:19:28'),(389,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:19:29'),(390,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:19:42'),(391,1,21,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-05','2026-05-05 15:19:43'),(392,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:19:44'),(393,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:19:45'),(394,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:19:58'),(395,1,21,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-05','2026-05-05 15:19:59'),(396,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:19:59'),(397,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:20:00'),(398,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:20:12'),(399,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 15:20:13'),(400,1,1,'api_call','/kewer/api/target_petugas.php','GET',200,'2026-05-05','2026-05-05 15:20:14'),(401,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:20:14'),(402,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:20:25'),(403,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 15:20:26'),(404,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:20:27'),(405,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:20:39'),(406,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-05','2026-05-05 15:20:40'),(407,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:20:42'),(408,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:20:53'),(409,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-05','2026-05-05 15:20:54'),(410,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:20:54'),(411,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:20:55'),(412,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:07'),(413,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:07'),(414,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:07'),(415,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:21:07'),(416,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:08'),(417,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:08'),(418,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:21:09'),(419,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:20'),(420,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:21'),(421,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:21:22'),(422,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:28'),(423,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:21:29'),(424,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:29'),(425,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:29'),(426,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 15:21:30'),(427,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:21:33'),(428,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:38'),(429,1,23,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 15:21:39'),(430,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:39'),(431,1,23,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:21:40'),(432,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:45'),(433,1,21,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 15:21:46'),(434,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:21:48'),(435,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:21:53'),(436,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 15:21:54'),(437,1,2,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:21:56'),(438,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:27:12'),(439,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:27:16'),(440,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:27:19'),(441,1,2,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:27:22'),(442,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:27:26'),(443,1,18,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:27:29'),(444,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:27:32'),(445,1,19,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:27:35'),(446,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:27:38'),(447,1,20,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:27:42'),(448,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:27:45'),(449,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:27:48'),(450,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:27:52'),(451,1,22,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:27:55'),(452,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:27:58'),(453,1,23,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:28:01'),(454,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:04'),(455,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:29:07'),(456,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:07'),(457,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:07'),(458,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:07'),(459,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 15:29:12'),(460,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:12'),(461,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:12'),(462,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:12'),(463,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 15:29:17'),(464,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:17'),(465,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:17'),(466,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:29:22'),(467,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:22'),(468,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:29:22'),(469,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:30:35'),(470,1,21,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:30:37'),(471,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:30:37'),(472,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:30:37'),(473,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:30:37'),(474,1,21,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:30:42'),(475,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:30:43'),(476,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:30:43'),(477,1,21,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 15:30:47'),(478,1,21,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 15:30:49'),(479,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:31:27'),(480,1,21,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:31:29'),(481,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:31:30'),(482,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:31:30'),(483,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:31:30'),(484,1,21,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:31:35'),(485,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:31:35'),(486,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:31:35'),(487,1,21,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 15:31:39'),(488,1,21,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 15:31:42'),(489,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:31:54'),(490,1,21,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:31:56'),(491,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:31:56'),(492,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:31:56'),(493,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:31:57'),(494,1,21,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:32:02'),(495,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:32:02'),(496,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:32:02'),(497,1,21,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 15:32:08'),(498,1,21,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 15:32:10'),(499,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:33'),(500,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:33:36'),(501,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:36'),(502,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:36'),(503,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:36'),(504,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:33:42'),(505,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:42'),(506,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:42'),(507,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:33:48'),(508,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 15:33:50'),(509,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:33:50'),(510,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 15:33:52'),(511,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:53'),(512,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:53'),(513,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:53'),(514,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 15:33:59'),(515,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:59'),(516,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:33:59'),(517,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:37:29'),(518,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:37:33'),(519,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:37:35'),(520,1,21,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:37:38'),(521,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:37:41'),(522,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:37:43'),(523,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:37:47'),(524,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:37:47'),(525,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:37:47'),(526,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:37:47'),(527,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:37:53'),(528,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:37:53'),(529,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:37:53'),(530,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:37:59'),(531,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:39:07'),(532,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:39:11'),(533,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:39:13'),(534,1,21,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:39:16'),(535,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:39:20'),(536,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:39:22'),(537,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:39:25'),(538,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:39:25'),(539,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:39:25'),(540,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:39:25'),(541,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:39:31'),(542,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:39:31'),(543,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:39:31'),(544,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:39:37'),(545,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:40:54'),(546,1,21,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:40:57'),(547,1,21,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 15:41:01'),(548,1,21,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:41:01'),(549,1,21,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:41:04'),(550,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:46:50'),(551,1,20,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:46:53'),(552,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:46:55'),(553,1,22,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:46:58'),(554,1,22,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:47:01'),(555,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:47:04'),(556,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 15:47:07'),(557,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:47:07'),(558,1,2,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:47:10'),(559,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:47:12'),(560,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 15:47:15'),(561,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:47:15'),(562,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:47:15'),(563,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:47:15'),(564,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:47:19'),(565,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 15:47:22'),(566,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:47:24'),(567,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:47:27'),(568,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:47:30'),(569,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:54:06'),(570,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:54:09'),(571,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:54:12'),(572,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:54:12'),(573,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:54:12'),(574,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:54:16'),(575,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 15:54:19'),(576,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:54:19'),(577,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:54:19'),(578,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:54:19'),(579,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 15:54:22'),(580,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:54:25'),(581,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:55:05'),(582,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:55:08'),(583,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:55:11'),(584,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:55:11'),(585,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:55:11'),(586,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:55:15'),(587,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 15:55:18'),(588,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:55:18'),(589,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:55:18'),(590,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:55:18'),(591,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 15:55:21'),(592,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:55:24'),(593,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:55:48'),(594,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:55:51'),(595,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:55:54'),(596,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:55:54'),(597,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:55:54'),(598,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:55:57'),(599,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 15:56:01'),(600,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:56:01'),(601,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:56:01'),(602,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:56:01'),(603,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 15:56:04'),(604,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:56:07'),(605,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:09'),(606,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:09'),(607,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:09'),(608,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:09'),(609,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:09'),(610,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:09'),(611,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:09'),(612,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:09'),(613,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:12'),(614,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:12'),(615,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:12'),(616,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:12'),(617,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:12'),(618,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:12'),(619,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:12'),(620,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:12'),(621,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:15'),(622,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:15'),(623,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:16'),(624,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:16'),(625,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:16'),(626,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:16'),(627,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:16'),(628,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:16'),(629,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(630,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(631,1,22,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(632,1,23,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(633,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(634,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(635,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(636,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(637,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(638,1,21,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(639,1,20,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 15:58:19'),(640,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:58:22'),(641,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:22'),(642,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:22'),(643,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:58:22'),(644,1,22,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:58:22'),(645,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:22'),(646,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:22'),(647,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:22'),(648,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:22'),(649,1,23,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:58:22'),(650,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(651,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(652,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(653,1,21,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(654,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(655,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(656,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(657,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(658,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(659,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(660,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:23'),(661,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 15:58:24'),(662,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:24'),(663,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:24'),(664,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:58:25'),(665,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 15:58:26'),(666,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:26'),(667,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:26'),(668,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:26'),(669,1,22,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:58:26'),(670,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:58:27'),(671,1,21,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:58:27'),(672,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 15:58:27'),(673,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:27'),(674,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 15:58:29'),(675,1,22,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 15:58:29'),(676,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 15:58:30'),(677,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 15:58:30'),(678,1,21,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 15:58:30'),(679,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 15:58:30'),(680,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 15:58:53'),(681,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:15'),(682,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:15'),(683,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:16'),(684,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:16'),(685,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:16'),(686,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:16'),(687,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:16'),(688,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:16'),(689,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:19'),(690,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:19'),(691,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:19'),(692,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:19'),(693,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:19'),(694,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:19'),(695,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:19'),(696,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:19'),(697,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:22'),(698,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:22'),(699,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:22'),(700,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:22'),(701,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:22'),(702,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:22'),(703,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:23'),(704,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:23'),(705,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:05:25'),(706,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:25'),(707,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:25'),(708,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:05:25'),(709,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:25'),(710,1,22,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:05:25'),(711,1,23,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:05:25'),(712,1,21,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:05:26'),(713,1,20,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:05:26'),(714,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:05:26'),(715,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:05:26'),(716,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:05:28'),(717,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(718,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(719,1,22,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(720,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(721,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(722,1,22,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(723,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(724,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(725,1,23,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(726,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(727,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(728,1,21,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(729,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(730,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(731,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:29'),(732,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:05:30'),(733,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:30'),(734,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:30'),(735,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:30'),(736,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:05:30'),(737,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:30'),(738,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:30'),(739,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:30'),(740,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:05:32'),(741,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:32'),(742,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:32'),(743,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:05:32'),(744,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:32'),(745,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:32'),(746,1,22,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:05:32'),(747,1,21,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:05:33'),(748,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:05:33'),(749,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:05:33'),(750,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:05:34'),(751,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:05:35'),(752,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:05:35'),(753,1,22,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:05:36'),(754,1,21,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:05:36'),(755,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:05:37'),(756,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:05:37'),(757,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:42'),(758,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:42'),(759,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:42'),(760,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:42'),(761,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:42'),(762,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:45'),(763,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:45'),(764,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:45'),(765,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:45'),(766,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:45'),(767,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:48'),(768,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:48'),(769,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:49'),(770,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:49'),(771,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:49'),(772,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:07:51'),(773,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:07:52'),(774,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:07:52'),(775,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:52'),(776,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:52'),(777,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:52'),(778,1,20,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:07:52'),(779,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:07:52'),(780,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:07:55'),(781,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:55'),(782,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:55'),(783,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:07:55'),(784,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:55'),(785,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:55'),(786,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:07:55'),(787,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:55'),(788,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:55'),(789,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:07:55'),(790,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:56'),(791,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:56'),(792,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:07:56'),(793,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:56'),(794,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:56'),(795,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:07:58'),(796,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:07:58'),(797,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:58'),(798,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:58'),(799,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:58'),(800,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:07:59'),(801,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:07:59'),(802,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:07:59'),(803,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:07:59'),(804,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:08:02'),(805,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:08:02'),(806,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:08:02'),(807,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:08:02'),(808,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:19:10'),(809,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:19:46'),(810,27,27,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:19:51'),(811,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:19:51'),(812,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:19:51'),(813,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:19:51'),(814,27,27,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:19:55'),(815,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:19:56'),(816,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:19:56'),(817,27,27,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 16:20:02'),(818,27,27,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:20:20'),(819,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:20:20'),(820,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:20:20'),(821,27,27,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:20:22'),(822,27,27,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:20:33'),(823,27,27,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:20:45'),(824,27,27,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 16:20:47'),(825,27,27,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:20:47'),(826,27,27,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:20:50'),(827,27,27,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:21:00'),(828,27,27,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:21:06'),(829,27,27,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 16:21:08'),(830,27,27,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:21:11'),(831,27,27,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-05','2026-05-05 16:21:29'),(832,27,27,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-05','2026-05-05 16:23:30'),(833,27,27,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:23:45'),(834,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:23:45'),(835,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:23:45'),(836,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:23:45'),(837,27,27,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 16:23:48'),(838,27,27,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:23:51'),(839,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:23:51'),(840,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:23:51'),(841,27,27,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:24:06'),(842,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:24:06'),(843,27,27,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:24:06'),(844,27,27,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:24:08'),(845,27,27,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:24:12'),(846,27,27,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:24:18'),(847,27,27,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-05','2026-05-05 16:24:23'),(848,27,27,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:24:25'),(849,27,27,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-05','2026-05-05 16:24:31'),(850,27,27,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-05','2026-05-05 16:24:46'),(851,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:25:07'),(852,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:25:18'),(853,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:25:19'),(854,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:26:03'),(855,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:26:04'),(856,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:26:04'),(857,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:26:04'),(858,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:26:07'),(859,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:26:07'),(860,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:26:07'),(861,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:26:09'),(862,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:28:03'),(863,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:28:05'),(864,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:28:05'),(865,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:28:05'),(866,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:28:06'),(867,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:28:07'),(868,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:28:09'),(869,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:28:11'),(870,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 16:28:12'),(871,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:28:13'),(872,1,1,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-05','2026-05-05 16:28:15'),(873,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:28:24'),(874,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:28:24'),(875,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:28:24'),(876,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:28:24'),(877,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 16:28:25'),(878,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:28:27'),(879,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:28:27'),(880,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:28:27'),(881,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:28:28'),(882,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:28:28'),(883,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:28:28'),(884,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:28:29'),(885,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:28:31'),(886,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:28:32'),(887,1,1,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-05','2026-05-05 16:28:33'),(888,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:28:35'),(889,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-05','2026-05-05 16:28:36'),(890,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 16:28:38'),(891,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:28:40'),(892,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:28:40'),(893,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-05','2026-05-05 16:28:52'),(894,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:29:08'),(895,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:35:41'),(896,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:35:52'),(897,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:35:52'),(898,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:35:52'),(899,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:35:52'),(900,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:18'),(901,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:39:18'),(902,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:18'),(903,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(904,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(905,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(906,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(907,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(908,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(909,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(910,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(911,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(912,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(913,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(914,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(915,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(916,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(917,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(918,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(919,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(920,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(921,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(922,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(923,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(924,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(925,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(926,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(927,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:39:19'),(928,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(929,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(930,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(931,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(932,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(933,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(934,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(935,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(936,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(937,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(938,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(939,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(940,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(941,1,19,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:39:20'),(942,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(943,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(944,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(945,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(946,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(947,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(948,1,18,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(949,1,18,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(950,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(951,1,18,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(952,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(953,1,18,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(954,1,18,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(955,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(956,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(957,1,20,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(958,1,20,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(959,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(960,1,20,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(961,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(962,1,20,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:21'),(963,1,20,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:39:22'),(964,1,20,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:22'),(965,1,20,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:39:22'),(966,1,20,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:39:22'),(967,1,20,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:22'),(968,1,20,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:22'),(969,1,20,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:39:22'),(970,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:25'),(971,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:35'),(972,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:39:35'),(973,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:35'),(974,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:39:35'),(975,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:35'),(976,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:35'),(977,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:36'),(978,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:39:36'),(979,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:36'),(980,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:39:36'),(981,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:39:36'),(982,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:36'),(983,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:36'),(984,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:39:36'),(985,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(986,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(987,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(988,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(989,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(990,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(991,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(992,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(993,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(994,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(995,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(996,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(997,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(998,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(999,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(1000,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(1001,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(1002,1,2,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(1003,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(1004,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(1005,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(1006,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:48'),(1007,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1008,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1009,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1010,1,18,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1011,1,18,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1012,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1013,1,18,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1014,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1015,1,18,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1016,1,18,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1017,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1018,1,18,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1019,1,18,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1020,1,18,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1021,1,18,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1022,1,18,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1023,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1024,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1025,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1026,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:39:49'),(1027,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1028,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1029,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1030,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1031,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1032,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1033,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1034,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1035,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1036,1,19,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1037,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1038,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1039,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1040,1,19,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1041,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1042,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1043,1,20,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1044,1,20,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1045,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1046,1,20,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1047,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1048,1,20,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:50'),(1049,1,20,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1050,1,20,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1051,1,20,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1052,1,20,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1053,1,20,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1054,1,20,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1055,1,20,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1056,1,20,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1057,1,20,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1058,1,20,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1059,1,20,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1060,1,20,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:39:51'),(1061,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:40:31'),(1062,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-05','2026-05-05 16:40:31'),(1063,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-05','2026-05-05 16:40:31'),(1064,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-05','2026-05-05 16:40:31'),(1065,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-05','2026-05-05 16:40:32'),(1066,1,1,'api_call','/kewer/api/alamat.php','GET',200,'2026-05-05','2026-05-05 16:40:32'),(1067,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-05','2026-05-05 16:40:32'),(1068,1,1,'api_call','/kewer/api/feature_flags.php','GET',200,'2026-05-05','2026-05-05 16:40:32'),(1069,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:40:45'),(1070,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-05','2026-05-05 16:40:46'),(1071,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-05','2026-05-05 16:40:46'),(1072,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-05','2026-05-05 16:40:46'),(1073,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-05','2026-05-05 16:40:46'),(1074,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-05','2026-05-05 16:40:46'),(1075,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:40:59'),(1076,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-05','2026-05-05 16:40:59'),(1077,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-05','2026-05-05 16:40:59'),(1078,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-05','2026-05-05 16:40:59'),(1079,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-05','2026-05-05 16:40:59'),(1080,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-05','2026-05-05 16:40:59'),(1081,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:41:08'),(1082,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-05','2026-05-05 16:41:08'),(1083,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-05','2026-05-05 16:41:09'),(1084,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1085,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1086,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1087,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1088,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1089,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1090,1,1,'api_call','/kewer/api/roles.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1091,1,1,'api_call','/kewer/api/kas_petugas.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1092,1,1,'api_call','/kewer/api/pengeluaran.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1093,1,1,'api_call','/kewer/api/setting_bunga.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1094,1,1,'api_call','/kewer/api/field_officer_activities.php','GET',200,'2026-05-05','2026-05-05 16:41:25'),(1095,1,1,'api_call','/kewer/api/daily_cash_reconciliation.php','GET',200,'2026-05-05','2026-05-05 16:41:26'),(1096,1,1,'api_call','/kewer/api/family_risk.php','GET',200,'2026-05-05','2026-05-05 16:41:26'),(1097,1,1,'api_call','/kewer/api/kas_bon.php','GET',200,'2026-05-05','2026-05-05 16:41:26'),(1098,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:41:34'),(1099,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:41:42'),(1100,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:42:54'),(1101,1,1,'api_call','/kewer/api/auto_confirm_settings.php','GET',200,'2026-05-05','2026-05-05 16:42:54'),(1102,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:43:02'),(1103,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1104,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1105,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1106,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1107,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1108,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1109,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1110,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1111,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1112,1,1,'page_render','/kewer/pages/bos/index.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1113,1,1,'page_render','/kewer/pages/superadmin/index.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1114,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1115,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-05','2026-05-05 16:43:16'),(1116,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-05','2026-05-05 16:43:49'),(1117,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-05','2026-05-05 16:43:58'),(1118,1,1,'api_call','/kewer/api/roles.php','GET',200,'2026-05-05','2026-05-05 16:44:16'),(1119,1,1,'api_call','/kewer/api/kas_bon.php','GET',200,'2026-05-05','2026-05-05 16:44:16'),(1120,1,1,'api_call','/kewer/api/kas_petugas.php','GET',200,'2026-05-05','2026-05-05 16:44:16'),(1121,1,1,'api_call','/kewer/api/kas_petugas_setoran.php','GET',200,'2026-05-05','2026-05-05 16:44:16'),(1122,1,1,'api_call','/kewer/api/pengeluaran.php','GET',200,'2026-05-05','2026-05-05 16:44:16'),(1123,1,1,'api_call','/kewer/api/setting_bunga.php','GET',200,'2026-05-05','2026-05-05 16:44:17'),(1124,1,1,'api_call','/kewer/api/field_officer_activities.php','GET',200,'2026-05-05','2026-05-05 16:44:17'),(1125,1,1,'api_call','/kewer/api/daily_cash_reconciliation.php','GET',200,'2026-05-05','2026-05-05 16:44:17'),(1126,1,1,'api_call','/kewer/api/family_risk.php','GET',200,'2026-05-05','2026-05-05 16:44:17'),(1127,1,1,'api_call','/kewer/api/nasabah_blacklist.php','GET',200,'2026-05-05','2026-05-05 16:44:17'),(1128,1,1,'api_call','/kewer/api/accounting.php','GET',200,'2026-05-05','2026-05-05 16:44:17'),(1129,1,1,'api_call','/kewer/api/delegated_permissions.php','GET',200,'2026-05-05','2026-05-05 16:44:17'),(1130,1,1,'api_call','/kewer/api/branch_managers.php','GET',200,'2026-05-05','2026-05-05 16:44:17'),(1131,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 16:44:39'),(1132,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 16:44:39'),(1133,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 16:44:39'),(1134,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 16:44:39'),(1135,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1136,1,1,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1137,1,1,'page_render','/kewer/pages/bos/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1138,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1139,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1140,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1141,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1142,1,1,'page_render','/kewer/pages/superadmin/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1143,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1144,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1145,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1146,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1147,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1148,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:44:40'),(1149,1,1,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-05','2026-05-05 16:45:21'),(1150,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:46:26'),(1151,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:46:26'),(1152,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:46:26'),(1153,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1154,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1155,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1156,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1157,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1158,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1159,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1160,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1161,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1162,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1163,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1164,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1165,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1166,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1167,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1168,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1169,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1170,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1171,1,1,'page_render','/kewer/pages/bos/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1172,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1173,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1174,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1175,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1176,1,1,'page_render','/kewer/pages/superadmin/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1177,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1178,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1179,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1180,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1181,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1182,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1183,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1184,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1185,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1186,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1187,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1188,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1189,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1190,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1191,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:46:27'),(1192,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1193,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1194,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1195,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1196,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1197,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1198,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1199,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1200,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1201,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1202,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1203,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1204,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1205,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1206,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1207,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1208,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1209,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1210,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1211,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1212,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1213,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1214,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1215,1,20,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1216,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1217,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:28'),(1218,1,20,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:29'),(1219,1,20,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:46:29'),(1220,1,20,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:29'),(1221,1,20,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:46:29'),(1222,1,20,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:46:29'),(1223,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:46:50'),(1224,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:46:50'),(1225,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:46:50'),(1226,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-05','2026-05-05 16:46:50'),(1227,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1228,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1229,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1230,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1231,1,1,'api_call','/kewer/api/roles.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1232,1,1,'api_call','/kewer/api/kas_petugas.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1233,1,1,'api_call','/kewer/api/pengeluaran.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1234,1,1,'api_call','/kewer/api/setting_bunga.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1235,1,1,'api_call','/kewer/api/field_officer_activities.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1236,1,1,'api_call','/kewer/api/daily_cash_reconciliation.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1237,1,1,'api_call','/kewer/api/auto_confirm_settings.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1238,1,1,'api_call','/kewer/api/family_risk.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1239,1,1,'api_call','/kewer/api/kas_bon.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1240,1,1,'api_call','/kewer/api/nasabah_blacklist.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1241,1,1,'api_call','/kewer/api/accounting.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1242,1,1,'api_call','/kewer/api/delegated_permissions.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1243,1,1,'api_call','/kewer/api/branch_managers.php','GET',200,'2026-05-05','2026-05-05 16:46:51'),(1244,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1245,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1246,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1247,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1248,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1249,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1250,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1251,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1252,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1253,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1254,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1255,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1256,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1257,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1258,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:33'),(1259,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1260,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1261,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1262,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1263,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1264,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1265,1,1,'page_render','/kewer/pages/bos/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1266,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1267,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1268,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1269,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1270,1,1,'page_render','/kewer/pages/superadmin/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1271,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1272,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1273,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1274,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1275,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1276,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1277,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1278,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1279,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1280,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1281,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1282,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1283,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1284,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1285,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1286,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1287,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1288,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1289,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1290,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1291,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1292,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1293,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:34'),(1294,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1295,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1296,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1297,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1298,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1299,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1300,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1301,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1302,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1303,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1304,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1305,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1306,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1307,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1308,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1309,1,20,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1310,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1311,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1312,1,20,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1313,1,20,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1314,1,20,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1315,1,20,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1316,1,20,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:35'),(1317,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:47:44'),(1318,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:47:44'),(1319,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:47:44'),(1320,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:44'),(1321,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:44'),(1322,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:44'),(1323,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1324,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1325,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1326,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1327,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1328,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1329,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1330,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1331,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1332,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1333,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1334,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1335,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1336,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1337,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1338,1,1,'page_render','/kewer/pages/bos/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1339,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1340,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1341,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1342,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1343,1,1,'page_render','/kewer/pages/superadmin/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1344,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1345,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1346,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1347,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1348,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1349,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1350,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1351,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1352,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1353,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1354,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1355,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1356,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1357,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1358,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1359,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:47:45'),(1360,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1361,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1362,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1363,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1364,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1365,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1366,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1367,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1368,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1369,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1370,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1371,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1372,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1373,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1374,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1375,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1376,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1377,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1378,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1379,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1380,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1381,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1382,1,20,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1383,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1384,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1385,1,20,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1386,1,20,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1387,1,20,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1388,1,20,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1389,1,20,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:47:46'),(1390,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:48:30'),(1391,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-05','2026-05-05 16:48:49'),(1392,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-05','2026-05-05 16:49:09'),(1393,1,1,'api_call','/kewer/api/wa_notifikasi.php','GET',200,'2026-05-05','2026-05-05 16:49:09'),(1394,1,1,'api_call','/kewer/api/target_petugas.php','GET',200,'2026-05-05','2026-05-05 16:49:09'),(1395,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-05','2026-05-05 16:49:19'),(1396,1,1,'api_call','/kewer/api/wa_notifikasi.php','GET',200,'2026-05-05','2026-05-05 16:49:19'),(1397,1,1,'api_call','/kewer/api/export.php','GET',200,'2026-05-05','2026-05-05 16:49:30'),(1398,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:50:03'),(1399,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:50:03'),(1400,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:50:03'),(1401,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:50:03'),(1402,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:50:03'),(1403,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:50:03'),(1404,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:50:03'),(1405,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:50:03'),(1406,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:50:03'),(1407,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1408,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1409,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1410,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1411,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1412,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1413,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1414,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1415,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1416,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1417,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1418,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1419,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1420,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1421,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1422,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1423,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1424,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1425,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1426,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1427,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1428,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1429,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1430,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1431,1,1,'page_render','/kewer/pages/bos/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1432,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1433,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1434,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1435,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1436,1,1,'page_render','/kewer/pages/superadmin/index.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1437,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1438,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1439,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 16:50:04'),(1440,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1441,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1442,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1443,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1444,1,1,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1445,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1446,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1447,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1448,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1449,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1450,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1451,1,1,'api_call','/kewer/api/roles.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1452,1,1,'api_call','/kewer/api/kas_petugas.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1453,1,1,'api_call','/kewer/api/pengeluaran.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1454,1,1,'api_call','/kewer/api/setting_bunga.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1455,1,1,'api_call','/kewer/api/field_officer_activities.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1456,1,1,'api_call','/kewer/api/daily_cash_reconciliation.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1457,1,1,'api_call','/kewer/api/auto_confirm_settings.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1458,1,1,'api_call','/kewer/api/family_risk.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1459,1,1,'api_call','/kewer/api/kas_bon.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1460,1,1,'api_call','/kewer/api/nasabah_blacklist.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1461,1,1,'api_call','/kewer/api/accounting.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1462,1,1,'api_call','/kewer/api/delegated_permissions.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1463,1,1,'api_call','/kewer/api/branch_managers.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1464,1,1,'api_call','/kewer/api/alamat.php','GET',200,'2026-05-05','2026-05-05 16:50:05'),(1465,1,1,'api_call','/kewer/api/target_petugas.php','GET',200,'2026-05-05','2026-05-05 16:50:06'),(1466,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:08'),(1467,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 16:59:08'),(1468,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 16:59:08'),(1469,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1470,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1471,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1472,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1473,1,1,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1474,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1475,1,1,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1476,1,1,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1477,1,1,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1478,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1479,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1480,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1481,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1482,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1483,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1484,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-05','2026-05-05 16:59:09'),(1485,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1486,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1487,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1488,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1489,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1490,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1491,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1492,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1493,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1494,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1495,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1496,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1497,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1498,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1499,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1500,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1501,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1502,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1503,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1504,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1505,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1506,1,1,'page_render','/kewer/pages/bos/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1507,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1508,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1509,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1510,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1511,1,1,'page_render','/kewer/pages/superadmin/index.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1512,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1513,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1514,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 16:59:58'),(1515,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1516,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1517,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1518,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1519,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1520,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1521,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1522,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1523,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1524,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1525,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1526,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1527,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1528,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1529,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1530,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1531,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1532,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1533,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1534,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1535,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1536,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1537,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1538,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1539,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1540,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1541,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1542,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1543,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1544,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 16:59:59'),(1545,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1546,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1547,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1548,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1549,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1550,1,20,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1551,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1552,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1553,1,20,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1554,1,20,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1555,1,20,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1556,1,20,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1557,1,20,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:00'),(1558,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1559,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1560,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1561,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1562,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1563,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1564,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1565,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1566,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1567,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1568,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1569,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1570,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1571,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1572,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1573,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 17:00:09'),(1574,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1575,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1576,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1577,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1578,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1579,1,1,'page_render','/kewer/pages/bos/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1580,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1581,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1582,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1583,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1584,1,1,'page_render','/kewer/pages/superadmin/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1585,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1586,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1587,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1588,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1589,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1590,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1591,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1592,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1593,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1594,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1595,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1596,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1597,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1598,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1599,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1600,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1601,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1602,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1603,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1604,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1605,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1606,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1607,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:10'),(1608,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1609,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1610,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1611,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1612,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1613,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1614,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1615,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1616,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1617,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1618,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1619,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1620,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1621,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1622,1,20,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1623,1,20,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1624,1,20,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1625,1,20,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1626,1,20,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1627,1,20,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1628,1,20,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1629,1,20,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1630,1,20,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:11'),(1631,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1632,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1633,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1634,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1635,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1636,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1637,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1638,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1639,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1640,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1641,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:17'),(1642,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1643,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1644,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1645,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1646,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1647,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1648,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1649,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1650,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1651,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1652,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1653,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1654,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1655,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1656,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1657,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1658,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1659,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1660,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1661,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1662,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1663,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1664,1,1,'page_render','/kewer/pages/bos/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1665,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1666,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1667,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1668,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1669,1,1,'page_render','/kewer/pages/superadmin/index.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1670,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1671,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1672,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 17:00:18'),(1673,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1674,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1675,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1676,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1677,1,1,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1678,1,1,'api_call','/kewer/api/dashboard.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1679,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1680,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1681,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1682,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1683,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1684,1,1,'api_call','/kewer/api/roles.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1685,1,1,'api_call','/kewer/api/kas_petugas.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1686,1,1,'api_call','/kewer/api/pengeluaran.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1687,1,1,'api_call','/kewer/api/setting_bunga.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1688,1,1,'api_call','/kewer/api/field_officer_activities.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1689,1,1,'api_call','/kewer/api/daily_cash_reconciliation.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1690,1,1,'api_call','/kewer/api/auto_confirm_settings.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1691,1,1,'api_call','/kewer/api/family_risk.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1692,1,1,'api_call','/kewer/api/kas_bon.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1693,1,1,'api_call','/kewer/api/nasabah_blacklist.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1694,1,1,'api_call','/kewer/api/accounting.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1695,1,1,'api_call','/kewer/api/delegated_permissions.php','GET',200,'2026-05-05','2026-05-05 17:00:19'),(1696,1,1,'api_call','/kewer/api/branch_managers.php','GET',200,'2026-05-05','2026-05-05 17:00:20'),(1697,1,1,'api_call','/kewer/api/alamat.php','GET',200,'2026-05-05','2026-05-05 17:00:20'),(1698,1,1,'api_call','/kewer/api/target_petugas.php','GET',200,'2026-05-05','2026-05-05 17:00:20'),(1699,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1700,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1701,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1702,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1703,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1704,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1705,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1706,1,1,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1707,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1708,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1709,1,1,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1710,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1711,1,1,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1712,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1713,1,1,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1714,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-05','2026-05-05 17:01:33'),(1715,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-05','2026-05-05 17:01:34'),(1716,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-05','2026-05-05 17:01:34'),(1717,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-05','2026-05-05 17:01:34'),(1718,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:47:07'),(1719,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:47:46'),(1720,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:47:47'),(1721,1,1,'page_render','/kewer/login.php','GET',200,'2026-05-08','2026-05-08 12:47:49'),(1722,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:47:49'),(1723,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:48:44'),(1724,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:48:45'),(1725,1,1,'page_render','/kewer/login.php','GET',200,'2026-05-08','2026-05-08 12:48:47'),(1726,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:48:47'),(1727,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:50:21'),(1728,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:50:22'),(1729,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-08','2026-05-08 12:50:24'),(1730,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:50:29'),(1731,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 12:50:30'),(1732,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:50:30'),(1733,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:50:30'),(1734,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:50:30'),(1735,1,1,'page_render','/kewer/logout.php','GET',200,'2026-05-08','2026-05-08 12:50:32'),(1736,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:52:24'),(1737,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:52:26'),(1738,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 12:52:27'),(1739,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:52:27'),(1740,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:52:28'),(1741,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:52:28'),(1742,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:52:50'),(1743,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:52:51'),(1744,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 12:52:53'),(1745,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:52:53'),(1746,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:52:53'),(1747,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:52:53'),(1748,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:53:17'),(1749,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:53:18'),(1750,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 12:53:20'),(1751,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:53:20'),(1752,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:53:20'),(1753,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:53:20'),(1754,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-08','2026-05-08 12:53:21'),(1755,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:54:16'),(1756,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:54:17'),(1757,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 12:54:19'),(1758,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:54:19'),(1759,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:54:19'),(1760,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:54:19'),(1761,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-08','2026-05-08 12:54:26'),(1762,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 12:54:27'),(1763,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:54:27'),(1764,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:54:27'),(1765,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 12:54:27'),(1766,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:12:55'),(1767,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1768,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1769,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1770,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1771,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1772,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1773,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1774,1,1,'api_call','/kewer/api/roles.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1775,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1776,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1777,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1778,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1779,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1780,1,1,'api_call','/kewer/api/field_officer_activities.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1781,1,1,'api_call','/kewer/api/kas_petugas_setoran.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1782,1,1,'api_call','/kewer/api/daily_cash_reconciliation.php','GET',200,'2026-05-08','2026-05-08 13:12:56'),(1783,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:12:56'),(1784,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1785,1,1,'api_call','/kewer/api/nasabah.php','PUT',200,'2026-05-08','2026-05-08 13:12:57'),(1786,1,1,'api_call','/kewer/api/nasabah.php','DELETE',200,'2026-05-08','2026-05-08 13:12:57'),(1787,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:12:57'),(1788,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:12:57'),(1789,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:12:57'),(1790,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:12:57'),(1791,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1792,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1793,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1794,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1795,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1796,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1797,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1798,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1799,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1800,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1801,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1802,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1803,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1804,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1805,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1806,1,1,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1807,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1808,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1809,1,1,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1810,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:12:57'),(1811,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1812,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1813,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1814,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1815,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1816,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1817,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1818,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1819,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1820,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1821,1,2,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1822,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1823,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1824,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1825,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1826,1,2,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1827,1,2,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1828,1,2,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1829,1,2,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1830,1,2,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:12:58'),(1831,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1832,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1833,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1834,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1835,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1836,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1837,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1838,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1839,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1840,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1841,1,19,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1842,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1843,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1844,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1845,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1846,1,19,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1847,1,19,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1848,1,19,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1849,1,19,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1850,1,19,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:12:59'),(1851,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:13:00'),(1852,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:13:00'),(1853,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:15:10'),(1854,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1855,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1856,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1857,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1858,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1859,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1860,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1861,1,1,'api_call','/kewer/api/roles.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1862,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1863,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1864,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1865,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1866,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1867,1,1,'api_call','/kewer/api/field_officer_activities.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1868,1,1,'api_call','/kewer/api/kas_petugas_setoran.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1869,1,1,'api_call','/kewer/api/daily_cash_reconciliation.php','GET',200,'2026-05-08','2026-05-08 13:15:11'),(1870,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:15:11'),(1871,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:15:12'),(1872,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:15:12'),(1873,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:15:12'),(1874,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:15:12'),(1875,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1876,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1877,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1878,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1879,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1880,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1881,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1882,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1883,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1884,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1885,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1886,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1887,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1888,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1889,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1890,1,1,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1891,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1892,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1893,1,1,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1894,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1895,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1896,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:15:12'),(1897,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1898,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1899,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1900,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1901,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1902,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1903,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1904,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1905,1,2,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1906,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1907,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1908,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1909,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1910,1,2,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1911,1,2,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1912,1,2,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1913,1,2,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1914,1,2,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1915,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1916,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:15:13'),(1917,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1918,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1919,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1920,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1921,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1922,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1923,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1924,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1925,1,19,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1926,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1927,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1928,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1929,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1930,1,19,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1931,1,19,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1932,1,19,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1933,1,19,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1934,1,19,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:15:14'),(1935,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:15:15'),(1936,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:15:15'),(1937,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1938,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1939,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1940,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1941,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1942,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1943,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1944,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1945,1,1,'api_call','/kewer/api/roles.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1946,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1947,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1948,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1949,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1950,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1951,1,1,'api_call','/kewer/api/field_officer_activities.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1952,1,1,'api_call','/kewer/api/kas_petugas_setoran.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1953,1,1,'api_call','/kewer/api/daily_cash_reconciliation.php','GET',200,'2026-05-08','2026-05-08 13:16:01'),(1954,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:16:02'),(1955,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1956,1,1,'api_call','/kewer/api/nasabah.php','PUT',200,'2026-05-08','2026-05-08 13:16:02'),(1957,1,1,'api_call','/kewer/api/nasabah.php','DELETE',200,'2026-05-08','2026-05-08 13:16:02'),(1958,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:16:02'),(1959,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:16:02'),(1960,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:16:02'),(1961,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:16:02'),(1962,1,1,'api_call','/kewer/api/pinjaman.php','POST',200,'2026-05-08','2026-05-08 13:16:02'),(1963,1,1,'api_call','/kewer/api/pinjaman.php','PUT',200,'2026-05-08','2026-05-08 13:16:02'),(1964,1,1,'api_call','/kewer/api/pinjaman.php','PUT',200,'2026-05-08','2026-05-08 13:16:02'),(1965,1,1,'api_call','/kewer/api/pinjaman.php','POST',200,'2026-05-08','2026-05-08 13:16:02'),(1966,1,1,'api_call','/kewer/api/pinjaman.php','POST',200,'2026-05-08','2026-05-08 13:16:02'),(1967,1,1,'api_call','/kewer/api/pinjaman.php','POST',200,'2026-05-08','2026-05-08 13:16:02'),(1968,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1969,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1970,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1971,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1972,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1973,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1974,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1975,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1976,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1977,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:02'),(1978,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1979,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1980,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1981,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1982,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1983,1,1,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1984,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1985,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1986,1,1,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1987,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1988,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1989,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1990,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1991,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1992,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1993,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1994,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1995,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1996,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1997,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1998,1,2,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(1999,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(2000,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(2001,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(2002,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:16:03'),(2003,1,2,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2004,1,2,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2005,1,2,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2006,1,2,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2007,1,2,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2008,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2009,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2010,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2011,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2012,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2013,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2014,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2015,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2016,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2017,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2018,1,19,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2019,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2020,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2021,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2022,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2023,1,19,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2024,1,19,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2025,1,19,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2026,1,19,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2027,1,19,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:16:04'),(2028,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:16:06'),(2029,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:16:06'),(2030,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:16:35'),(2031,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:16:35'),(2032,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:16:35'),(2033,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:16:35'),(2034,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2035,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2036,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2037,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2038,1,1,'api_call','/kewer/api/roles.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2039,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2040,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2041,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2042,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2043,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2044,1,1,'api_call','/kewer/api/field_officer_activities.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2045,1,1,'api_call','/kewer/api/kas_petugas_setoran.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2046,1,1,'api_call','/kewer/api/daily_cash_reconciliation.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2047,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:16:36'),(2048,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:16:36'),(2049,1,1,'api_call','/kewer/api/nasabah.php','PUT',200,'2026-05-08','2026-05-08 13:16:36'),(2050,1,1,'api_call','/kewer/api/nasabah.php','DELETE',200,'2026-05-08','2026-05-08 13:16:37'),(2051,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:16:37'),(2052,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:16:37'),(2053,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:16:37'),(2054,1,1,'api_call','/kewer/api/nasabah.php','POST',200,'2026-05-08','2026-05-08 13:16:37'),(2055,1,1,'api_call','/kewer/api/pinjaman.php','POST',200,'2026-05-08','2026-05-08 13:16:37'),(2056,1,1,'api_call','/kewer/api/pinjaman.php','PUT',200,'2026-05-08','2026-05-08 13:16:37'),(2057,1,1,'api_call','/kewer/api/pinjaman.php','PUT',200,'2026-05-08','2026-05-08 13:16:37'),(2058,1,1,'api_call','/kewer/api/pinjaman.php','POST',200,'2026-05-08','2026-05-08 13:16:37'),(2059,1,1,'api_call','/kewer/api/pinjaman.php','POST',200,'2026-05-08','2026-05-08 13:16:37'),(2060,1,1,'api_call','/kewer/api/pinjaman.php','POST',200,'2026-05-08','2026-05-08 13:16:37'),(2061,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2062,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2063,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2064,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2065,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2066,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2067,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2068,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2069,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2070,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2071,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:16:37'),(2072,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2073,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2074,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2075,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2076,1,1,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2077,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2078,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2079,1,1,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2080,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2081,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2082,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2083,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2084,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2085,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2086,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2087,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2088,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2089,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2090,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2091,1,2,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2092,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2093,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:16:38'),(2094,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2095,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2096,1,2,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2097,1,2,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2098,1,2,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2099,1,2,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2100,1,2,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2101,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2102,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2103,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2104,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2105,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2106,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2107,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2108,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2109,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2110,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2111,1,19,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2112,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2113,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2114,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2115,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2116,1,19,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2117,1,19,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-08','2026-05-08 13:16:39'),(2118,1,19,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-08','2026-05-08 13:16:40'),(2119,1,19,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-08','2026-05-08 13:16:40'),(2120,1,19,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-08','2026-05-08 13:16:40'),(2121,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:16:41'),(2122,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-08','2026-05-08 13:16:41'),(2123,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:00'),(2124,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:33'),(2125,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:48:33'),(2126,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:33'),(2127,1,1,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:48:33'),(2128,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:33'),(2129,1,1,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:48:33'),(2130,1,1,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:48:33'),(2131,1,1,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:48:33'),(2132,1,1,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2133,1,1,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2134,1,1,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2135,1,1,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2136,1,1,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2137,1,1,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2138,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2139,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2140,1,1,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2141,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2142,1,1,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2143,1,1,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2144,1,1,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2145,1,1,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:34'),(2146,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2147,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2148,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2149,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2150,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2151,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2152,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2153,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2154,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2155,1,1,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2156,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2157,1,1,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:48:35'),(2158,1,1,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2159,1,1,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2160,1,1,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2161,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2162,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2163,1,1,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2164,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2165,1,1,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2166,1,1,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2167,1,1,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2168,1,1,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2169,1,1,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2170,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2171,1,1,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2172,1,1,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:36'),(2173,1,1,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2174,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2175,1,1,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2176,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2177,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2178,1,1,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2179,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2180,1,1,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2181,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2182,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2183,1,1,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2184,1,1,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2185,1,1,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2186,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:48:37'),(2187,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2188,1,1,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2189,1,1,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2190,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2191,1,1,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2192,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2193,1,1,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2194,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2195,1,1,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2196,1,1,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2197,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2198,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2199,1,1,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2200,1,1,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:48:38'),(2201,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2202,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2203,1,2,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2204,1,2,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2205,1,2,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2206,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2207,1,2,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2208,1,2,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2209,1,2,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2210,1,2,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2211,1,2,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2212,1,2,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2213,1,2,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:48:39'),(2214,1,2,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2215,1,2,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2216,1,2,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2217,1,2,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2218,1,2,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2219,1,2,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2220,1,2,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2221,1,2,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2222,1,2,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2223,1,2,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2224,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2225,1,2,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2226,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2227,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:48:40'),(2228,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:48:41'),(2229,1,2,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:48:41'),(2230,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:48:41'),(2231,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:48:41'),(2232,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:48:41'),(2233,1,2,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:48:41'),(2234,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:48:41'),(2235,1,2,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2236,1,2,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2237,1,2,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2238,1,2,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2239,1,2,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2240,1,2,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2241,1,2,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2242,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2243,1,2,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2244,1,2,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2245,1,2,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:48:42'),(2246,1,2,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:48:43'),(2247,1,2,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:48:43'),(2248,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:43'),(2249,1,2,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:48:43'),(2250,1,2,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:43'),(2251,1,2,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:43'),(2252,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:43'),(2253,1,2,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:43'),(2254,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:43'),(2255,1,2,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:48:44'),(2256,1,2,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:44'),(2257,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:48:44'),(2258,1,2,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:48:44'),(2259,1,2,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:48:44'),(2260,1,2,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:44'),(2261,1,2,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:48:44'),(2262,1,2,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:48:44'),(2263,1,2,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:48:44'),(2264,1,2,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2265,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2266,1,2,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2267,1,2,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2268,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2269,1,2,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2270,1,2,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2271,1,2,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2272,1,2,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2273,1,2,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2274,1,2,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:45'),(2275,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2276,1,2,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2277,1,2,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2278,1,2,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2279,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2280,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2281,1,18,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2282,1,18,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2283,1,18,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2284,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2285,1,18,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:48:46'),(2286,1,18,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2287,1,18,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2288,1,18,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2289,1,18,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2290,1,18,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2291,1,18,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2292,1,18,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2293,1,18,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2294,1,18,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2295,1,18,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2296,1,18,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2297,1,18,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2298,1,18,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:48:47'),(2299,1,18,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2300,1,18,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2301,1,18,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2302,1,18,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2303,1,18,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2304,1,18,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2305,1,18,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2306,1,18,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2307,1,18,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2308,1,18,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2309,1,18,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:48:48'),(2310,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2311,1,18,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2312,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2313,1,18,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2314,1,18,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2315,1,18,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2316,1,18,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2317,1,18,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2318,1,18,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2319,1,18,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2320,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:48:49'),(2321,1,18,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2322,1,18,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2323,1,18,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2324,1,18,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2325,1,18,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2326,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2327,1,18,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2328,1,18,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2329,1,18,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2330,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2331,1,18,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2332,1,18,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2333,1,18,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:48:50'),(2334,1,18,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2335,1,18,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2336,1,18,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2337,1,18,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2338,1,18,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2339,1,18,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2340,1,18,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2341,1,18,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2342,1,18,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2343,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2344,1,18,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2345,1,18,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2346,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:51'),(2347,1,18,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2348,1,18,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2349,1,18,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2350,1,18,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2351,1,18,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2352,1,18,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2353,1,18,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2354,1,18,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2355,1,18,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2356,1,18,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2357,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2358,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:52'),(2359,1,19,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2360,1,19,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2361,1,19,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2362,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2363,1,19,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2364,1,19,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2365,1,19,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2366,1,19,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2367,1,19,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2368,1,19,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2369,1,19,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:48:53'),(2370,1,19,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2371,1,19,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2372,1,19,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2373,1,19,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2374,1,19,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2375,1,19,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2376,1,19,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2377,1,19,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2378,1,19,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2379,1,19,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2380,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2381,1,19,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2382,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2383,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:48:54'),(2384,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2385,1,19,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2386,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2387,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2388,1,19,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2389,1,19,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2390,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2391,1,19,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2392,1,19,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2393,1,19,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2394,1,19,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:48:55'),(2395,1,19,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2396,1,19,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2397,1,19,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2398,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2399,1,19,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2400,1,19,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2401,1,19,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2402,1,19,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2403,1,19,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2404,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2405,1,19,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2406,1,19,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2407,1,19,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2408,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:56'),(2409,1,19,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2410,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2411,1,19,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2412,1,19,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2413,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2414,1,19,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2415,1,19,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2416,1,19,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2417,1,19,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2418,1,19,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2419,1,19,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:48:57'),(2420,1,19,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2421,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2422,1,19,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2423,1,19,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2424,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2425,1,19,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2426,1,19,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2427,1,19,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2428,1,19,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2429,1,19,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2430,1,19,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2431,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2432,1,19,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2433,1,19,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2434,1,19,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:48:58'),(2435,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:48:59'),(2436,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:02'),(2437,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:53:02'),(2438,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:02'),(2439,1,1,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2440,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2441,1,1,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2442,1,1,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2443,1,1,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2444,1,1,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2445,1,1,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2446,1,1,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2447,1,1,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2448,1,1,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2449,1,1,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2450,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2451,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:53:03'),(2452,1,1,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2453,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2454,1,1,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2455,1,1,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2456,1,1,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2457,1,1,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2458,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2459,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2460,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2461,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2462,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2463,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2464,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2465,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:53:04'),(2466,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2467,1,1,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2468,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2469,1,1,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2470,1,1,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2471,1,1,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2472,1,1,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2473,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2474,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2475,1,1,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2476,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2477,1,1,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2478,1,1,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2479,1,1,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2480,1,1,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:53:05'),(2481,1,1,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2482,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2483,1,1,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2484,1,1,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2485,1,1,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2486,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2487,1,1,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2488,1,1,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2489,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2490,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2491,1,1,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2492,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2493,1,1,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2494,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2495,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:06'),(2496,1,1,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2497,1,1,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2498,1,1,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2499,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2500,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2501,1,1,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2502,1,1,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2503,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2504,1,1,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2505,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2506,1,1,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2507,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2508,1,1,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2509,1,1,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2510,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2511,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:53:07'),(2512,1,1,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2513,1,1,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2514,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2515,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2516,1,2,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2517,1,2,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2518,1,2,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2519,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2520,1,2,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2521,1,2,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2522,1,2,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2523,1,2,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2524,1,2,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:53:08'),(2525,1,2,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2526,1,2,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2527,1,2,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2528,1,2,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2529,1,2,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2530,1,2,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2531,1,2,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2532,1,2,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2533,1,2,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2534,1,2,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2535,1,2,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2536,1,2,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2537,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2538,1,2,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2539,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:53:09'),(2540,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2541,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2542,1,2,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2543,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2544,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2545,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2546,1,2,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2547,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2548,1,2,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2549,1,2,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2550,1,2,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2551,1,2,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2552,1,2,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2553,1,2,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:10'),(2554,1,2,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2555,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2556,1,2,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2557,1,2,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2558,1,2,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2559,1,2,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2560,1,2,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2561,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2562,1,2,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2563,1,2,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2564,1,2,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2565,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2566,1,2,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2567,1,2,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2568,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:11'),(2569,1,2,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2570,1,2,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2571,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2572,1,2,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2573,1,2,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2574,1,2,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2575,1,2,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2576,1,2,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2577,1,2,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2578,1,2,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2579,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2580,1,2,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2581,1,2,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2582,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2583,1,2,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:53:12'),(2584,1,2,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2585,1,2,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2586,1,2,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2587,1,2,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2588,1,2,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2589,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2590,1,2,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2591,1,2,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2592,1,2,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2593,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2594,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2595,1,18,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:53:13'),(2596,1,18,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2597,1,18,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2598,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2599,1,18,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2600,1,18,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2601,1,18,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2602,1,18,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2603,1,18,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2604,1,18,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2605,1,18,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2606,1,18,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:53:14'),(2607,1,18,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2608,1,18,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2609,1,18,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2610,1,18,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2611,1,18,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2612,1,18,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2613,1,18,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2614,1,18,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2615,1,18,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2616,1,18,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2617,1,18,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2618,1,18,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2619,1,18,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:53:15'),(2620,1,18,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2621,1,18,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2622,1,18,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2623,1,18,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2624,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2625,1,18,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2626,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2627,1,18,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2628,1,18,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2629,1,18,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2630,1,18,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:53:16'),(2631,1,18,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2632,1,18,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2633,1,18,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2634,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2635,1,18,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2636,1,18,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2637,1,18,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2638,1,18,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2639,1,18,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2640,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2641,1,18,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2642,1,18,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:17'),(2643,1,18,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2644,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2645,1,18,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2646,1,18,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2647,1,18,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2648,1,18,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2649,1,18,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2650,1,18,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2651,1,18,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2652,1,18,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2653,1,18,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:18'),(2654,1,18,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:53:19'),(2655,1,18,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:53:19'),(2656,1,18,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:53:19'),(2657,1,18,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:53:19'),(2658,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:53:19'),(2659,1,18,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:19'),(2660,1,18,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:53:19'),(2661,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:19'),(2662,1,18,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:53:19'),(2663,1,18,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:53:19'),(2664,1,18,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:53:20'),(2665,1,18,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:53:20'),(2666,1,18,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:20'),(2667,1,18,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:20'),(2668,1,18,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:53:20'),(2669,1,18,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:53:20'),(2670,1,18,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:20'),(2671,1,18,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:53:20'),(2672,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:20'),(2673,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2674,1,19,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2675,1,19,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2676,1,19,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2677,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2678,1,19,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2679,1,19,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2680,1,19,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2681,1,19,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2682,1,19,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2683,1,19,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2684,1,19,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:53:21'),(2685,1,19,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2686,1,19,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2687,1,19,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2688,1,19,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2689,1,19,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2690,1,19,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2691,1,19,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2692,1,19,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2693,1,19,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2694,1,19,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2695,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2696,1,19,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2697,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:53:22'),(2698,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2699,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2700,1,19,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2701,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2702,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2703,1,19,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2704,1,19,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2705,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2706,1,19,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2707,1,19,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2708,1,19,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2709,1,19,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2710,1,19,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:53:23'),(2711,1,19,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2712,1,19,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2713,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2714,1,19,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2715,1,19,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2716,1,19,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2717,1,19,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2718,1,19,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2719,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2720,1,19,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2721,1,19,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2722,1,19,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2723,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2724,1,19,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:24'),(2725,1,19,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2726,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2727,1,19,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2728,1,19,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2729,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2730,1,19,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2731,1,19,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2732,1,19,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2733,1,19,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2734,1,19,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2735,1,19,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2736,1,19,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2737,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:53:25'),(2738,1,19,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2739,1,19,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2740,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2741,1,19,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2742,1,19,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2743,1,19,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2744,1,19,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2745,1,19,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2746,1,19,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2747,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2748,1,19,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2749,1,19,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2750,1,19,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2751,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:53:26'),(2752,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:45'),(2753,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2754,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2755,1,1,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2756,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2757,1,1,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2758,1,1,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2759,1,1,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2760,1,1,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2761,1,1,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2762,1,1,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2763,1,1,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2764,1,1,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2765,1,1,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2766,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2767,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:54:46'),(2768,1,1,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2769,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2770,1,1,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2771,1,1,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2772,1,1,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2773,1,1,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2774,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2775,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2776,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2777,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2778,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2779,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2780,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2781,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:54:47'),(2782,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2783,1,1,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2784,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2785,1,1,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2786,1,1,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2787,1,1,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2788,1,1,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2789,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2790,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2791,1,1,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2792,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2793,1,1,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2794,1,1,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2795,1,1,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2796,1,1,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2797,1,1,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:54:48'),(2798,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2799,1,1,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2800,1,1,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2801,1,1,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2802,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2803,1,1,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2804,1,1,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2805,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2806,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2807,1,1,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2808,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2809,1,1,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2810,1,1,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2811,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:54:49'),(2812,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2813,1,1,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2814,1,1,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2815,1,1,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2816,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2817,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2818,1,1,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2819,1,1,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2820,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2821,1,1,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2822,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2823,1,1,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2824,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2825,1,1,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2826,1,1,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2827,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2828,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:54:50'),(2829,1,1,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2830,1,1,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2831,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2832,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2833,1,2,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2834,1,2,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2835,1,2,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2836,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2837,1,2,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2838,1,2,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2839,1,2,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2840,1,2,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2841,1,2,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2842,1,2,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2843,1,2,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:54:51'),(2844,1,2,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2845,1,2,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2846,1,2,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2847,1,2,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2848,1,2,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2849,1,2,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2850,1,2,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2851,1,2,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2852,1,2,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2853,1,2,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2854,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2855,1,2,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2856,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2857,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2858,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:54:52'),(2859,1,2,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2860,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2861,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2862,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2863,1,2,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2864,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2865,1,2,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2866,1,2,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2867,1,2,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2868,1,2,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2869,1,2,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2870,1,2,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:53'),(2871,1,2,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2872,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2873,1,2,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2874,1,2,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2875,1,2,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2876,1,2,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2877,1,2,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2878,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2879,1,2,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2880,1,2,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2881,1,2,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2882,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2883,1,2,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2884,1,2,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2885,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:54:54'),(2886,1,2,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2887,1,2,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2888,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2889,1,2,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2890,1,2,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2891,1,2,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2892,1,2,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2893,1,2,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2894,1,2,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2895,1,2,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2896,1,2,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2897,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2898,1,2,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2899,1,2,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2900,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2901,1,2,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:54:55'),(2902,1,2,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2903,1,2,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2904,1,2,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2905,1,2,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2906,1,2,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2907,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2908,1,2,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2909,1,2,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2910,1,2,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2911,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2912,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2913,1,18,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2914,1,18,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:54:56'),(2915,1,18,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2916,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2917,1,18,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2918,1,18,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2919,1,18,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2920,1,18,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2921,1,18,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2922,1,18,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2923,1,18,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2924,1,18,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2925,1,18,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2926,1,18,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2927,1,18,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2928,1,18,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2929,1,18,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2930,1,18,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:54:57'),(2931,1,18,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2932,1,18,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2933,1,18,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2934,1,18,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2935,1,18,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2936,1,18,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2937,1,18,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2938,1,18,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2939,1,18,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2940,1,18,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2941,1,18,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2942,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2943,1,18,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:54:58'),(2944,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2945,1,18,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2946,1,18,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2947,1,18,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2948,1,18,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2949,1,18,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2950,1,18,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2951,1,18,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2952,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2953,1,18,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2954,1,18,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2955,1,18,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2956,1,18,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2957,1,18,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2958,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:54:59'),(2959,1,18,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2960,1,18,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2961,1,18,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2962,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2963,1,18,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2964,1,18,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2965,1,18,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2966,1,18,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2967,1,18,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2968,1,18,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2969,1,18,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2970,1,18,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2971,1,18,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:00'),(2972,1,18,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2973,1,18,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2974,1,18,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2975,1,18,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2976,1,18,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2977,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2978,1,18,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2979,1,18,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2980,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2981,1,18,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2982,1,18,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2983,1,18,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2984,1,18,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2985,1,18,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:01'),(2986,1,18,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2987,1,18,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2988,1,18,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2989,1,18,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2990,1,18,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2991,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2992,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2993,1,19,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2994,1,19,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2995,1,19,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2996,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2997,1,19,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2998,1,19,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(2999,1,19,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:55:02'),(3000,1,19,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3001,1,19,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3002,1,19,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3003,1,19,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3004,1,19,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3005,1,19,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3006,1,19,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3007,1,19,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3008,1,19,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3009,1,19,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3010,1,19,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3011,1,19,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3012,1,19,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3013,1,19,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3014,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:55:03'),(3015,1,19,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3016,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3017,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3018,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3019,1,19,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3020,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3021,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3022,1,19,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3023,1,19,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3024,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3025,1,19,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3026,1,19,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:04'),(3027,1,19,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3028,1,19,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3029,1,19,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3030,1,19,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3031,1,19,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3032,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3033,1,19,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3034,1,19,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3035,1,19,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3036,1,19,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3037,1,19,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3038,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3039,1,19,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3040,1,19,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3041,1,19,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3042,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:05'),(3043,1,19,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3044,1,19,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3045,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3046,1,19,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3047,1,19,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3048,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3049,1,19,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3050,1,19,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3051,1,19,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3052,1,19,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3053,1,19,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3054,1,19,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3055,1,19,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:06'),(3056,1,19,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3057,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3058,1,19,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3059,1,19,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3060,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3061,1,19,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3062,1,19,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3063,1,19,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3064,1,19,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3065,1,19,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3066,1,19,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3067,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3068,1,19,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3069,1,19,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3070,1,19,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3071,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:07'),(3072,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:33'),(3073,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:55:33'),(3074,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:33'),(3075,1,1,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3076,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3077,1,1,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3078,1,1,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3079,1,1,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3080,1,1,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3081,1,1,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3082,1,1,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3083,1,1,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3084,1,1,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3085,1,1,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3086,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3087,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:55:34'),(3088,1,1,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3089,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3090,1,1,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3091,1,1,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3092,1,1,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3093,1,1,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3094,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3095,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3096,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3097,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3098,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3099,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3100,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3101,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:35'),(3102,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3103,1,1,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3104,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3105,1,1,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3106,1,1,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3107,1,1,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3108,1,1,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3109,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3110,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3111,1,1,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3112,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3113,1,1,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3114,1,1,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3115,1,1,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3116,1,1,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:55:36'),(3117,1,1,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3118,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3119,1,1,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3120,1,1,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3121,1,1,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3122,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3123,1,1,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3124,1,1,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3125,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3126,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3127,1,1,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3128,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3129,1,1,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3130,1,1,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:37'),(3131,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3132,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3133,1,1,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3134,1,1,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3135,1,1,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3136,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3137,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3138,1,1,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3139,1,1,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3140,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3141,1,1,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3142,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3143,1,1,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3144,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:38'),(3145,1,1,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3146,1,1,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3147,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3148,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3149,1,1,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3150,1,1,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3151,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3152,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3153,1,2,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3154,1,2,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3155,1,2,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3156,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3157,1,2,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:55:39'),(3158,1,2,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3159,1,2,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3160,1,2,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3161,1,2,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3162,1,2,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3163,1,2,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3164,1,2,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3165,1,2,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3166,1,2,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3167,1,2,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3168,1,2,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3169,1,2,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3170,1,2,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3171,1,2,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3172,1,2,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3173,1,2,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:40'),(3174,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3175,1,2,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3176,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3177,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3178,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3179,1,2,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3180,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3181,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3182,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3183,1,2,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3184,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:41'),(3185,1,2,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3186,1,2,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3187,1,2,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3188,1,2,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3189,1,2,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3190,1,2,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3191,1,2,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3192,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3193,1,2,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3194,1,2,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3195,1,2,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3196,1,2,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3197,1,2,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3198,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3199,1,2,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3200,1,2,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:42'),(3201,1,2,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3202,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3203,1,2,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3204,1,2,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3205,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3206,1,2,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3207,1,2,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3208,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3209,1,2,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3210,1,2,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3211,1,2,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3212,1,2,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3213,1,2,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3214,1,2,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:55:43'),(3215,1,2,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3216,1,2,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3217,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3218,1,2,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3219,1,2,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3220,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3221,1,2,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3222,1,2,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3223,1,2,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3224,1,2,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3225,1,2,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3226,1,2,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3227,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3228,1,2,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:55:44'),(3229,1,2,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3230,1,2,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3231,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3232,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3233,1,18,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3234,1,18,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3235,1,18,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3236,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3237,1,18,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3238,1,18,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3239,1,18,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3240,1,18,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3241,1,18,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:55:45'),(3242,1,18,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3243,1,18,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3244,1,18,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3245,1,18,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3246,1,18,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3247,1,18,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3248,1,18,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3249,1,18,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3250,1,18,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3251,1,18,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3252,1,18,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3253,1,18,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3254,1,18,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3255,1,18,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3256,1,18,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:55:46'),(3257,1,18,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3258,1,18,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3259,1,18,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3260,1,18,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3261,1,18,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3262,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3263,1,18,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3264,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3265,1,18,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3266,1,18,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3267,1,18,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3268,1,18,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:55:47'),(3269,1,18,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3270,1,18,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3271,1,18,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3272,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3273,1,18,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3274,1,18,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3275,1,18,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3276,1,18,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3277,1,18,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3278,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3279,1,18,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3280,1,18,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3281,1,18,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3282,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3283,1,18,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:48'),(3284,1,18,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3285,1,18,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3286,1,18,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3287,1,18,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3288,1,18,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3289,1,18,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3290,1,18,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3291,1,18,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3292,1,18,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3293,1,18,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3294,1,18,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3295,1,18,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3296,1,18,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3297,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:55:49'),(3298,1,18,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3299,1,18,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3300,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3301,1,18,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3302,1,18,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3303,1,18,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3304,1,18,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3305,1,18,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3306,1,18,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3307,1,18,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3308,1,18,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3309,1,18,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3310,1,18,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3311,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3312,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:50'),(3313,1,19,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3314,1,19,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3315,1,19,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3316,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3317,1,19,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3318,1,19,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3319,1,19,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3320,1,19,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3321,1,19,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3322,1,19,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3323,1,19,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3324,1,19,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3325,1,19,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3326,1,19,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:55:51'),(3327,1,19,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3328,1,19,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3329,1,19,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3330,1,19,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3331,1,19,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3332,1,19,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3333,1,19,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3334,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3335,1,19,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3336,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3337,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3338,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:55:52'),(3339,1,19,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3340,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3341,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3342,1,19,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3343,1,19,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3344,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3345,1,19,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3346,1,19,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3347,1,19,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3348,1,19,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3349,1,19,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3350,1,19,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3351,1,19,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3352,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:55:53'),(3353,1,19,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3354,1,19,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3355,1,19,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3356,1,19,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3357,1,19,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3358,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3359,1,19,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3360,1,19,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3361,1,19,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3362,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3363,1,19,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3364,1,19,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3365,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3366,1,19,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3367,1,19,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:54'),(3368,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3369,1,19,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3370,1,19,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3371,1,19,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3372,1,19,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3373,1,19,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3374,1,19,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3375,1,19,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3376,1,19,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3377,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3378,1,19,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3379,1,19,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3380,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3381,1,19,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:55:55'),(3382,1,19,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:55:56'),(3383,1,19,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:55:56'),(3384,1,19,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:55:56'),(3385,1,19,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:55:56'),(3386,1,19,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:55:56'),(3387,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:55:56'),(3388,1,19,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:55:56'),(3389,1,19,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:55:56'),(3390,1,19,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:55:56'),(3391,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:55:56'),(3392,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:36'),(3393,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:56:36'),(3394,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:36'),(3395,1,1,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:56:36'),(3396,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:36'),(3397,1,1,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:56:36'),(3398,1,1,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:56:36'),(3399,1,1,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:56:36'),(3400,1,1,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3401,1,1,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3402,1,1,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3403,1,1,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3404,1,1,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3405,1,1,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3406,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3407,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3408,1,1,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3409,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3410,1,1,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3411,1,1,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3412,1,1,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3413,1,1,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3414,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:56:37'),(3415,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3416,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3417,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3418,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3419,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3420,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3421,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3422,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3423,1,1,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3424,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3425,1,1,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3426,1,1,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3427,1,1,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3428,1,1,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:56:38'),(3429,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3430,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3431,1,1,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3432,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3433,1,1,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3434,1,1,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3435,1,1,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3436,1,1,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3437,1,1,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3438,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3439,1,1,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3440,1,1,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3441,1,1,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3442,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3443,1,1,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3444,1,1,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3445,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:39'),(3446,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3447,1,1,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3448,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3449,1,1,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3450,1,1,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3451,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3452,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3453,1,1,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3454,1,1,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3455,1,1,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3456,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3457,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3458,1,1,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3459,1,1,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3460,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:40'),(3461,1,1,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3462,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3463,1,1,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3464,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3465,1,1,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3466,1,1,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3467,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3468,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3469,1,1,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3470,1,1,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3471,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3472,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3473,1,2,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3474,1,2,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:41'),(3475,1,2,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3476,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3477,1,2,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3478,1,2,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3479,1,2,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3480,1,2,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3481,1,2,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3482,1,2,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3483,1,2,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3484,1,2,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3485,1,2,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3486,1,2,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3487,1,2,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:56:42'),(3488,1,2,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3489,1,2,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3490,1,2,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3491,1,2,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3492,1,2,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3493,1,2,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3494,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3495,1,2,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3496,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3497,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3498,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3499,1,2,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3500,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3501,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:56:43'),(3502,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3503,1,2,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3504,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3505,1,2,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3506,1,2,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3507,1,2,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3508,1,2,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3509,1,2,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3510,1,2,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3511,1,2,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3512,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3513,1,2,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3514,1,2,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3515,1,2,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:56:44'),(3516,1,2,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3517,1,2,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3518,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3519,1,2,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3520,1,2,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3521,1,2,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3522,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3523,1,2,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3524,1,2,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3525,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3526,1,2,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3527,1,2,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3528,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3529,1,2,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3530,1,2,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3531,1,2,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:56:45'),(3532,1,2,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3533,1,2,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3534,1,2,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3535,1,2,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3536,1,2,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3537,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3538,1,2,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3539,1,2,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3540,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3541,1,2,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3542,1,2,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3543,1,2,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3544,1,2,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3545,1,2,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3546,1,2,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3547,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:56:46'),(3548,1,2,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3549,1,2,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3550,1,2,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3551,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3552,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3553,1,18,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3554,1,18,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3555,1,18,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3556,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3557,1,18,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3558,1,18,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3559,1,18,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:56:47'),(3560,1,18,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3561,1,18,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3562,1,18,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3563,1,18,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3564,1,18,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3565,1,18,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3566,1,18,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3567,1,18,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3568,1,18,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3569,1,18,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3570,1,18,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3571,1,18,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3572,1,18,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3573,1,18,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3574,1,18,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:56:48'),(3575,1,18,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3576,1,18,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3577,1,18,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3578,1,18,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3579,1,18,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3580,1,18,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3581,1,18,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3582,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3583,1,18,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3584,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3585,1,18,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3586,1,18,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:49'),(3587,1,18,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3588,1,18,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3589,1,18,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3590,1,18,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3591,1,18,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3592,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3593,1,18,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3594,1,18,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3595,1,18,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3596,1,18,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3597,1,18,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3598,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3599,1,18,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3600,1,18,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3601,1,18,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3602,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:50'),(3603,1,18,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3604,1,18,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3605,1,18,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3606,1,18,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3607,1,18,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3608,1,18,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3609,1,18,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3610,1,18,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3611,1,18,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3612,1,18,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3613,1,18,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3614,1,18,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3615,1,18,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3616,1,18,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:56:51'),(3617,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3618,1,18,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3619,1,18,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3620,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3621,1,18,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3622,1,18,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3623,1,18,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3624,1,18,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3625,1,18,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3626,1,18,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3627,1,18,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3628,1,18,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3629,1,18,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3630,1,18,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3631,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:52'),(3632,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3633,1,19,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3634,1,19,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3635,1,19,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3636,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3637,1,19,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3638,1,19,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3639,1,19,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3640,1,19,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3641,1,19,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3642,1,19,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3643,1,19,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 02:56:53'),(3644,1,19,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3645,1,19,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3646,1,19,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3647,1,19,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3648,1,19,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3649,1,19,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3650,1,19,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3651,1,19,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3652,1,19,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3653,1,19,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3654,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3655,1,19,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3656,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 02:56:54'),(3657,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3658,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3659,1,19,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3660,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3661,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3662,1,19,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3663,1,19,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3664,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3665,1,19,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3666,1,19,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3667,1,19,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3668,1,19,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3669,1,19,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 02:56:55'),(3670,1,19,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3671,1,19,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3672,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3673,1,19,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3674,1,19,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3675,1,19,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3676,1,19,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3677,1,19,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3678,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3679,1,19,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3680,1,19,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3681,1,19,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3682,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3683,1,19,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:56'),(3684,1,19,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3685,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3686,1,19,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3687,1,19,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3688,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3689,1,19,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3690,1,19,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3691,1,19,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3692,1,19,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3693,1,19,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3694,1,19,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3695,1,19,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3696,1,19,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3697,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 02:56:57'),(3698,1,19,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3699,1,19,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3700,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3701,1,19,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3702,1,19,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3703,1,19,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3704,1,19,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3705,1,19,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3706,1,19,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3707,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3708,1,19,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3709,1,19,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3710,1,19,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3711,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 02:56:58'),(3712,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:00:59'),(3713,1,1,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 03:00:59'),(3714,1,1,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 03:00:59'),(3715,1,1,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 03:00:59'),(3716,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 03:00:59'),(3717,1,1,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 03:00:59'),(3718,1,1,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 03:00:59'),(3719,1,1,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3720,1,1,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3721,1,1,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3722,1,1,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3723,1,1,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3724,1,1,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3725,1,1,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3726,1,1,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3727,1,1,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3728,1,1,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3729,1,1,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3730,1,1,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3731,1,1,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3732,1,1,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:00'),(3733,1,1,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3734,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3735,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3736,1,1,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3737,1,1,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3738,1,1,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3739,1,1,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3740,1,1,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3741,1,1,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3742,1,1,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3743,1,1,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3744,1,1,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:01'),(3745,1,1,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3746,1,1,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3747,1,1,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3748,1,1,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3749,1,1,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3750,1,1,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3751,1,1,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3752,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3753,1,1,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3754,1,1,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3755,1,1,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3756,1,1,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3757,1,1,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3758,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3759,1,1,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3760,1,1,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3761,1,1,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:02'),(3762,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3763,1,1,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3764,1,1,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3765,1,1,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3766,1,1,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3767,1,1,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3768,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3769,1,1,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3770,1,1,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3771,1,1,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3772,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3773,1,1,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3774,1,1,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3775,1,1,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3776,1,1,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3777,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 03:01:03'),(3778,1,1,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3779,1,1,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3780,1,1,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3781,1,1,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3782,1,1,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3783,1,1,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3784,1,1,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3785,1,1,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3786,1,1,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3787,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3788,1,1,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3789,1,1,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3790,1,1,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3791,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:04'),(3792,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3793,1,2,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3794,1,2,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3795,1,2,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3796,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3797,1,2,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3798,1,2,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3799,1,2,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3800,1,2,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3801,1,2,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3802,1,2,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3803,1,2,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3804,1,2,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3805,1,2,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 03:01:05'),(3806,1,2,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3807,1,2,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3808,1,2,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3809,1,2,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3810,1,2,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3811,1,2,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3812,1,2,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3813,1,2,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3814,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3815,1,2,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3816,1,2,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3817,1,2,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3818,1,2,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3819,1,2,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:06'),(3820,1,2,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3821,1,2,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3822,1,2,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3823,1,2,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3824,1,2,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3825,1,2,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3826,1,2,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3827,1,2,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3828,1,2,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3829,1,2,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3830,1,2,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3831,1,2,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3832,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 03:01:07'),(3833,1,2,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3834,1,2,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3835,1,2,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3836,1,2,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3837,1,2,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3838,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3839,1,2,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3840,1,2,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3841,1,2,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3842,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3843,1,2,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3844,1,2,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3845,1,2,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3846,1,2,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3847,1,2,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:08'),(3848,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3849,1,2,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3850,1,2,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3851,1,2,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3852,1,2,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3853,1,2,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3854,1,2,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3855,1,2,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3856,1,2,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3857,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3858,1,2,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3859,1,2,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3860,1,2,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3861,1,2,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3862,1,2,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 03:01:09'),(3863,1,2,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3864,1,2,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3865,1,2,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3866,1,2,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3867,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3868,1,2,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3869,1,2,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3870,1,2,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3871,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3872,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3873,1,18,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3874,1,18,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3875,1,18,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3876,1,18,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:10'),(3877,1,18,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3878,1,18,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3879,1,18,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3880,1,18,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3881,1,18,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3882,1,18,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3883,1,18,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3884,1,18,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3885,1,18,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3886,1,18,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3887,1,18,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3888,1,18,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3889,1,18,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3890,1,18,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 03:01:11'),(3891,1,18,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3892,1,18,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3893,1,18,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3894,1,18,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3895,1,18,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3896,1,18,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3897,1,18,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3898,1,18,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3899,1,18,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3900,1,18,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3901,1,18,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 03:01:12'),(3902,1,18,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3903,1,18,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3904,1,18,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3905,1,18,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3906,1,18,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3907,1,18,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3908,1,18,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3909,1,18,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3910,1,18,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3911,1,18,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3912,1,18,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3913,1,18,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3914,1,18,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3915,1,18,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3916,1,18,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 03:01:13'),(3917,1,18,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3918,1,18,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3919,1,18,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3920,1,18,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3921,1,18,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3922,1,18,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3923,1,18,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3924,1,18,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3925,1,18,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3926,1,18,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3927,1,18,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3928,1,18,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3929,1,18,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3930,1,18,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3931,1,18,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 03:01:14'),(3932,1,18,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3933,1,18,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3934,1,18,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3935,1,18,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3936,1,18,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3937,1,18,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3938,1,18,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3939,1,18,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3940,1,18,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3941,1,18,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3942,1,18,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3943,1,18,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3944,1,18,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 03:01:15'),(3945,1,18,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3946,1,18,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3947,1,18,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3948,1,18,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3949,1,18,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3950,1,18,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3951,1,18,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3952,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3953,1,19,'page_render','/kewer/pages/angsuran/bayar.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3954,1,19,'page_render','/kewer/pages/angsuran/bayar_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3955,1,19,'page_render','/kewer/pages/angsuran/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3956,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3957,1,19,'page_render','/kewer/pages/app_owner/ai_advisor.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3958,1,19,'page_render','/kewer/pages/app_owner/approvals.php','GET',200,'2026-05-10','2026-05-10 03:01:16'),(3959,1,19,'page_render','/kewer/pages/app_owner/billing.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3960,1,19,'page_render','/kewer/pages/app_owner/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3961,1,19,'page_render','/kewer/pages/app_owner/features.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3962,1,19,'page_render','/kewer/pages/app_owner/koperasi.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3963,1,19,'page_render','/kewer/pages/app_owner/provinsi_activation.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3964,1,19,'page_render','/kewer/pages/app_owner/settings.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3965,1,19,'page_render','/kewer/pages/app_owner/usage.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3966,1,19,'page_render','/kewer/pages/audit/index.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3967,1,19,'page_render','/kewer/pages/auto_confirm/index.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3968,1,19,'page_render','/kewer/pages/bos/billing.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3969,1,19,'page_render','/kewer/pages/bos/delegated_permissions.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3970,1,19,'page_render','/kewer/pages/bos/register.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3971,1,19,'page_render','/kewer/pages/bos/setup_headquarters.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3972,1,19,'page_render','/kewer/pages/cabang/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3973,1,19,'page_render','/kewer/pages/cabang/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3974,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-10','2026-05-10 03:01:17'),(3975,1,19,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3976,1,19,'page_render','/kewer/pages/cash_reconciliation/index.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3977,1,19,'page_render','/kewer/pages/family_risk/index.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3978,1,19,'page_render','/kewer/pages/field_activities/index.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3979,1,19,'page_render','/kewer/pages/jaminan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3980,1,19,'page_render','/kewer/pages/kas_bon/index.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3981,1,19,'page_render','/kewer/pages/kas_petugas/index.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3982,1,19,'page_render','/kewer/pages/kinerja/index.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3983,1,19,'page_render','/kewer/pages/laporan/gabungan.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3984,1,19,'page_render','/kewer/pages/laporan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3985,1,19,'page_render','/kewer/pages/nasabah/angsuran.php','GET',200,'2026-05-10','2026-05-10 03:01:18'),(3986,1,19,'page_render','/kewer/pages/nasabah/blacklist_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3987,1,19,'page_render','/kewer/pages/nasabah/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3988,1,19,'page_render','/kewer/pages/nasabah/data_keluarga.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3989,1,19,'page_render','/kewer/pages/nasabah/detail.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3990,1,19,'page_render','/kewer/pages/nasabah/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3991,1,19,'page_render','/kewer/pages/nasabah/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3992,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3993,1,19,'page_render','/kewer/pages/nasabah/pembayaran.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3994,1,19,'page_render','/kewer/pages/nasabah/pengajuan_pinjaman.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3995,1,19,'page_render','/kewer/pages/nasabah/pengajuan_simpanan.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3996,1,19,'page_render','/kewer/pages/nasabah/pinjaman.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3997,1,19,'page_render','/kewer/pages/nasabah/profil.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3998,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(3999,1,19,'page_render','/kewer/pages/notifikasi/index.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(4000,1,19,'page_render','/kewer/pages/pembayaran/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:19'),(4001,1,19,'page_render','/kewer/pages/pembayaran/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4002,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4003,1,19,'page_render','/kewer/pages/pembayaran/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4004,1,19,'page_render','/kewer/pages/penagihan/index.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4005,1,19,'page_render','/kewer/pages/pengeluaran/index.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4006,1,19,'page_render','/kewer/pages/permissions/index.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4007,1,19,'page_render','/kewer/pages/petugas/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4008,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4009,1,19,'page_render','/kewer/pages/petugas/kunjungan.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4010,1,19,'page_render','/kewer/pages/petugas/riwayat_harian.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4011,1,19,'page_render','/kewer/pages/petugas/slip_harian.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4012,1,19,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4013,1,19,'page_render','/kewer/pages/petugas/transaksi.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4014,1,19,'page_render','/kewer/pages/pinjaman/cetak_kartu.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4015,1,19,'page_render','/kewer/pages/pinjaman/cetak_kwitansi.php','GET',200,'2026-05-10','2026-05-10 03:01:20'),(4016,1,19,'page_render','/kewer/pages/pinjaman/detail.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4017,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4018,1,19,'page_render','/kewer/pages/pinjaman/index_compact.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4019,1,19,'page_render','/kewer/pages/pinjaman/proses.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4020,1,19,'page_render','/kewer/pages/pinjaman/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4021,1,19,'page_render','/kewer/pages/rute_harian/index.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4022,1,19,'page_render','/kewer/pages/setting_bunga/index.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4023,1,19,'page_render','/kewer/pages/settings/webhooks.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4024,1,19,'page_render','/kewer/pages/superadmin/bos_approvals.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4025,1,19,'page_render','/kewer/pages/users/edit.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4026,1,19,'page_render','/kewer/pages/users/hapus.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4027,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4028,1,19,'page_render','/kewer/pages/users/settings_2fa.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4029,1,19,'page_render','/kewer/pages/users/tambah.php','GET',200,'2026-05-10','2026-05-10 03:01:21'),(4030,1,19,'page_render','/kewer/pages/users/transfer.php','GET',200,'2026-05-10','2026-05-10 03:01:22'),(4031,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-10','2026-05-10 03:01:22');
/*!40000 ALTER TABLE `usage_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_permissions`
--

LOCK TABLES `user_permissions` WRITE;
/*!40000 ALTER TABLE `user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `ktp` varchar(16) DEFAULT NULL COMMENT 'NIK KTP - identitas global via db_orang',
  `email` varchar(100) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'teller',
  `owner_bos_id` int(10) unsigned DEFAULT NULL,
  `cabang_id` int(11) DEFAULT NULL,
  `derived_permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`derived_permissions`)),
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `limit_kasbon` decimal(12,0) DEFAULT 0,
  `gaji` decimal(12,0) DEFAULT 0,
  `tanggal_lahir` date DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `db_orang_person_id` int(11) DEFAULT NULL,
  `totp_secret` varchar(64) DEFAULT NULL,
  `totp_enabled` tinyint(1) DEFAULT 0,
  `totp_verified_at` timestamp NULL DEFAULT NULL,
  `phone_2fa` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_owner_bos_id` (`owner_bos_id`),
  KEY `idx_db_orang_person` (`db_orang_person_id`),
  KEY `idx_users_ktp` (`ktp`),
  KEY `idx_users_cabang` (`cabang_id`),
  KEY `idx_users_owner_bos` (`owner_bos_id`),
  KEY `idx_users_role_status` (`role`,`status`),
  KEY `idx_users_id` (`id`),
  CONSTRAINT `fk_users_cabang` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_db_orang_person` FOREIGN KEY (`db_orang_person_id`) REFERENCES `db_orang`.`people` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'patri','$2y$10$zucy/tAcsiUBX5OqgemSgud7V8Kyd5kuiryiABLy1.Rux68v5JC0.','Patri Sihaloho',NULL,'patri@kewer.co.id','081234567890','bos',NULL,1,NULL,'aktif','2026-05-02 14:17:07','2026-05-02 15:34:24',0,0,NULL,NULL,3,NULL,0,NULL,NULL),(2,'mgr_pusat','$2y$10$uGH5./H5aYqFVBMWUB.p/Oh3EQhw4ejNLM6Pz9ALxZpfuHTViZ4qO','Sondang Silaban',NULL,'','','manager_pusat',1,1,NULL,'aktif','2026-05-02 14:18:26','2026-05-02 15:34:24',0,0,NULL,NULL,4,NULL,0,NULL,NULL),(18,'mgr_balige','$2y$10$M3T60PI35ooIbe7AJTFG.eEUvuuqtgSoayMu2yvpsN6yR6b6zTHFu','Roswita Nainggolan',NULL,'mgr_balige@kewer.co.id',NULL,'manager_cabang',1,2,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,5,NULL,0,NULL,NULL),(19,'adm_pusat','$2y$10$0NOdONMqf3C2krjFnXaDVujJj6jybgKQ.HF5mbnnuHijJQ2CdaWyi','Melvina Hutabarat',NULL,'adm_pusat@kewer.co.id',NULL,'admin_pusat',1,1,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,6,NULL,0,NULL,NULL),(20,'adm_balige','$2y$10$kvbrWAiSVSdYfgfdcJGpK.qGYjopxzgL8HZd/vuJT3Vz1SI6i5UCC','Ruli Sirait',NULL,'adm_balige@kewer.co.id',NULL,'admin_cabang',1,2,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,7,NULL,0,NULL,NULL),(21,'ptr_pusat','$2y$10$MLr90EoeRX1o2pD7qS4pwODQ2rX/WI9Nwm2dKfjvIga5khYdQ9tO6','Darwin Sinaga',NULL,'ptr_pusat@kewer.co.id',NULL,'petugas_pusat',1,1,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,8,NULL,0,NULL,NULL),(22,'ptr_balige','$2y$10$RoBvhk/8HBof0rZmYYXxmuEtnpS2aXZ2/1OxohVXGIFHtKAjAOv16','Markus Situmorang',NULL,'ptr_balige@kewer.co.id',NULL,'petugas_cabang',1,2,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,9,NULL,0,NULL,NULL),(23,'krw_pusat','$2y$10$JOIggChOhqoxkU1capAwG./eELKhgkl0HTpy4Qgx90ejPuolVFDAm','Susi Aritonang',NULL,'krw_pusat@kewer.co.id',NULL,'teller',1,1,NULL,'aktif','2026-05-02 14:19:20','2026-05-08 13:13:19',0,0,NULL,NULL,10,NULL,0,NULL,NULL),(24,'krw_balige','$2y$10$LhWlwITO.UVOYFmpymybTe5CxDIzRZyfgr0kyzXOBPRmgXfynNzBu','Petrus Hutagalung',NULL,'krw_balige@kewer.co.id',NULL,'teller',1,2,NULL,'aktif','2026-05-02 14:19:20','2026-05-08 13:13:19',0,0,NULL,NULL,11,NULL,0,NULL,NULL),(25,'appowner','$2y$10$CtXCJToI4qyhfCTWl7yPjOxF9fLr1rJwrT6LjD1dFvcFWJoHs2GhG','App Owner',NULL,'admin@kewer.app',NULL,'appOwner',NULL,NULL,NULL,'aktif','2026-05-02 14:44:02','2026-05-02 15:13:20',0,0,NULL,NULL,NULL,NULL,0,NULL,NULL),(26,'bos_test','$2y$10$O8sF3tlZnxzYmuAEFbdWRemzJP6ugwOYgfJWCNr8qt6c/CCNeYmWa','Test Bos Koperasi',NULL,'test@kewer.co.id','081299990000','bos',NULL,NULL,NULL,'aktif','2026-05-02 14:51:52','2026-05-02 15:34:24',0,0,NULL,NULL,12,NULL,0,NULL,NULL),(27,'bos_flow_test','$2y$10$aVKCp3ektX/ptO64NUiu.uHb0Ny.DAS1oqEtuxh4C8u271c8wjSpi','Flow Test Bos',NULL,'flow@test.co.id','081288880001','bos',NULL,NULL,NULL,'aktif','2026-05-02 14:54:15','2026-05-02 15:34:24',0,0,NULL,NULL,13,NULL,0,NULL,NULL),(28,'mgr_pangururan','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Manager Pangururan',NULL,NULL,NULL,'manager_cabang',NULL,1,NULL,'aktif','2026-05-08 13:13:25','2026-05-08 13:13:25',0,0,NULL,NULL,NULL,NULL,0,NULL,NULL),(29,'adm_pangururan','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Admin Pangururan',NULL,NULL,NULL,'admin_cabang',NULL,1,NULL,'aktif','2026-05-08 13:13:25','2026-05-08 13:13:25',0,0,NULL,NULL,NULL,NULL,0,NULL,NULL),(30,'ptr_pngr1','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Petugas Pusat 1',NULL,NULL,NULL,'petugas_pusat',NULL,1,NULL,'aktif','2026-05-08 13:13:25','2026-05-08 13:13:25',0,0,NULL,NULL,NULL,NULL,0,NULL,NULL),(31,'ptr_pngr2','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Petugas Pusat 2',NULL,NULL,NULL,'petugas_cabang',NULL,1,NULL,'aktif','2026-05-08 13:13:25','2026-05-08 13:13:25',0,0,NULL,NULL,NULL,NULL,0,NULL,NULL),(32,'ptr_blg1','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Petugas Balige 1',NULL,NULL,NULL,'petugas_cabang',NULL,2,NULL,'aktif','2026-05-08 13:13:25','2026-05-08 13:13:25',0,0,NULL,NULL,NULL,NULL,0,NULL,NULL),(33,'krw_pngr','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Karyawan Pangururan',NULL,NULL,NULL,'teller',NULL,1,NULL,'aktif','2026-05-08 13:13:25','2026-05-08 13:13:25',0,0,NULL,NULL,NULL,NULL,0,NULL,NULL),(34,'krw_blg','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Karyawan Balige',NULL,NULL,NULL,'teller',NULL,2,NULL,'aktif','2026-05-08 13:13:25','2026-05-08 13:13:25',0,0,NULL,NULL,NULL,NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_penagihan_hari_ini`
--

DROP TABLE IF EXISTS `v_penagihan_hari_ini`;
/*!50001 DROP VIEW IF EXISTS `v_penagihan_hari_ini`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_penagihan_hari_ini` AS SELECT
 1 AS `id`,
  1 AS `pinjaman_id`,
  1 AS `kode_nasabah`,
  1 AS `nama_nasabah`,
  1 AS `telp`,
  1 AS `alamat`,
  1 AS `province_id`,
  1 AS `regency_id`,
  1 AS `district_id`,
  1 AS `village_id`,
  1 AS `no_angsuran`,
  1 AS `jatuh_tempo`,
  1 AS `total_angsuran`,
  1 AS `total_bayar`,
  1 AS `status_angsuran`,
  1 AS `jenis`,
  1 AS `status_penagihan`,
  1 AS `petugas_id`,
  1 AS `nama_petugas`,
  1 AS `catatan` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `visits`
--

DROP TABLE IF EXISTS `visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `petugas_id` int(11) NOT NULL,
  `nasabah_id` int(11) NOT NULL,
  `cabang_id` int(11) NOT NULL,
  `visit_type` varchar(50) DEFAULT 'pembayaran' COMMENT 'Visit type: pembayaran, follow_up, survey',
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `gps_accuracy` decimal(8,2) DEFAULT NULL,
  `geofence_valid` tinyint(1) DEFAULT 1 COMMENT 'Whether location was within geofence',
  `distance_from_cabang` decimal(10,2) DEFAULT NULL COMMENT 'Distance from cabang in meters',
  `visit_date` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_petugas_id` (`petugas_id`),
  KEY `idx_nasabah_id` (`nasabah_id`),
  KEY `idx_cabang_id` (`cabang_id`),
  KEY `idx_visit_date` (`visit_date`),
  KEY `idx_gps` (`latitude`,`longitude`),
  CONSTRAINT `visits_ibfk_1` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `visits_ibfk_2` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `visits_ibfk_3` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Field officer visit logs with GPS tracking';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visits`
--

LOCK TABLES `visits` WRITE;
/*!40000 ALTER TABLE `visits` DISABLE KEYS */;
/*!40000 ALTER TABLE `visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wa_log`
--

DROP TABLE IF EXISTS `wa_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wa_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `tipe` enum('tagihan','konfirmasi_bayar','jatuh_tempo','macet','pengingat') NOT NULL,
  `nomor_wa` varchar(20) NOT NULL,
  `pesan` text NOT NULL,
  `status` enum('pending','sent','failed','read') DEFAULT 'pending',
  `provider` varchar(30) DEFAULT 'fonnte',
  `response_code` varchar(10) DEFAULT NULL,
  `response_body` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wa_log`
--

LOCK TABLES `wa_log` WRITE;
/*!40000 ALTER TABLE `wa_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `wa_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhook_deliveries`
--

DROP TABLE IF EXISTS `webhook_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webhook_deliveries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `webhook_id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `resource_type` varchar(100) NOT NULL COMMENT 'Type of resource (pinjaman, pembayaran, etc.)',
  `resource_id` int(11) NOT NULL COMMENT 'ID of the resource',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `status` varchar(20) NOT NULL COMMENT 'pending, sent, failed',
  `sent_at` datetime DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `last_error` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_webhook_id` (`webhook_id`),
  KEY `idx_resource` (`resource_type`,`resource_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `webhook_deliveries_ibfk_1` FOREIGN KEY (`webhook_id`) REFERENCES `webhooks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Webhook delivery queue';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhook_deliveries`
--

LOCK TABLES `webhook_deliveries` WRITE;
/*!40000 ALTER TABLE `webhook_deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhook_deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhook_logs`
--

DROP TABLE IF EXISTS `webhook_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webhook_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `webhook_id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Event payload' CHECK (json_valid(`payload`)),
  `response_code` int(11) DEFAULT NULL,
  `response_body` text DEFAULT NULL,
  `status` varchar(20) NOT NULL COMMENT 'success, failed, retrying',
  `attempt_number` int(11) DEFAULT 1 COMMENT 'Current attempt number',
  `error_message` text DEFAULT NULL,
  `triggered_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_webhook_id` (`webhook_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_status` (`status`),
  KEY `idx_triggered_at` (`triggered_at`),
  CONSTRAINT `webhook_logs_ibfk_1` FOREIGN KEY (`webhook_id`) REFERENCES `webhooks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Webhook delivery logs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhook_logs`
--

LOCK TABLES `webhook_logs` WRITE;
/*!40000 ALTER TABLE `webhook_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhook_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhooks`
--

DROP TABLE IF EXISTS `webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webhooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'User who created the webhook',
  `event_type` varchar(100) NOT NULL COMMENT 'Event type (e.g., pinjaman.approved, pembayaran.received)',
  `target_url` varchar(255) NOT NULL COMMENT 'Webhook endpoint URL',
  `secret_key` varchar(255) DEFAULT NULL COMMENT 'Secret for HMAC signature',
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional headers to send' CHECK (json_valid(`headers`)),
  `is_active` tinyint(1) DEFAULT 1,
  `retry_count` int(11) DEFAULT 3 COMMENT 'Number of retry attempts',
  `retry_interval` int(11) DEFAULT 300 COMMENT 'Retry interval in seconds',
  `last_triggered_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `webhooks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Webhook configuration for event notifications';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhooks`
--

LOCK TABLES `webhooks` WRITE;
/*!40000 ALTER TABLE `webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `write_off`
--

DROP TABLE IF EXISTS `write_off`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `write_off` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinjaman_id` int(11) NOT NULL,
  `nasabah_id` int(11) NOT NULL,
  `sisa_pokok` decimal(12,2) NOT NULL,
  `total_denda` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_kerugian` decimal(12,2) NOT NULL,
  `alasan` enum('meninggal','bangkrut','kabur','tidak_ditemukan','force_majeure','lainnya') NOT NULL,
  `alasan_detail` text DEFAULT NULL,
  `upaya_penagihan` text DEFAULT NULL COMMENT 'Dokumentasi upaya penagihan sebelum write-off',
  `disetujui_oleh` int(11) NOT NULL,
  `tanggal_writeoff` date NOT NULL,
  `status_aset` enum('tidak_ada','jaminan_ada','sudah_disita','sedang_diproses') NOT NULL DEFAULT 'tidak_ada',
  `dokumen` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_writeoff_pinjaman` (`pinjaman_id`),
  KEY `idx_writeoff_pinjaman` (`pinjaman_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Hapus buku pinjaman macet dengan audit trail';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `write_off`
--

LOCK TABLES `write_off` WRITE;
/*!40000 ALTER TABLE `write_off` DISABLE KEYS */;
/*!40000 ALTER TABLE `write_off` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `labarugi`
--

/*!50001 DROP VIEW IF EXISTS `labarugi`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `labarugi` AS select 'Pendapatan' AS `kategori`,`neraca_saldo`.`kode` AS `kode`,`neraca_saldo`.`nama` AS `nama`,`neraca_saldo`.`saldo_akhir` AS `saldo_akhir` from `neraca_saldo` where `neraca_saldo`.`tipe` = 'pendapatan' and `neraca_saldo`.`saldo_akhir` <> 0 union all select 'Beban' AS `kategori`,`neraca_saldo`.`kode` AS `kode`,`neraca_saldo`.`nama` AS `nama`,`neraca_saldo`.`saldo_akhir` AS `saldo_akhir` from `neraca_saldo` where `neraca_saldo`.`tipe` = 'beban' and `neraca_saldo`.`saldo_akhir` <> 0 order by case when `kategori` = 'Pendapatan' then 1 else 2 end,`kategori`,`kode` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `neraca`
--

/*!50001 DROP VIEW IF EXISTS `neraca`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `neraca` AS select 'Aset' AS `kategori`,`neraca_saldo`.`kode` AS `kode`,`neraca_saldo`.`nama` AS `nama`,`neraca_saldo`.`saldo_akhir` AS `saldo_akhir` from `neraca_saldo` where `neraca_saldo`.`tipe` = 'aset' and `neraca_saldo`.`saldo_akhir` <> 0 union all select 'Kewajiban' AS `kategori`,`neraca_saldo`.`kode` AS `kode`,`neraca_saldo`.`nama` AS `nama`,`neraca_saldo`.`saldo_akhir` AS `saldo_akhir` from `neraca_saldo` where `neraca_saldo`.`tipe` = 'kewajiban' and `neraca_saldo`.`saldo_akhir` <> 0 union all select 'Ekuitas' AS `kategori`,`neraca_saldo`.`kode` AS `kode`,`neraca_saldo`.`nama` AS `nama`,`neraca_saldo`.`saldo_akhir` AS `saldo_akhir` from `neraca_saldo` where `neraca_saldo`.`tipe` = 'ekuitas' and `neraca_saldo`.`saldo_akhir` <> 0 order by `kategori`,`kode` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `neraca_saldo`
--

/*!50001 DROP VIEW IF EXISTS `neraca_saldo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `neraca_saldo` AS select `a`.`kode` AS `kode`,`a`.`nama` AS `nama`,`a`.`tipe` AS `tipe`,`a`.`kategori` AS `kategori`,`a`.`saldo_normal` AS `saldo_normal`,coalesce(sum(case when `jd`.`debit` > 0 then `jd`.`debit` else 0 end),0) AS `total_debit`,coalesce(sum(case when `jd`.`kredit` > 0 then `jd`.`kredit` else 0 end),0) AS `total_kredit`,case when `a`.`saldo_normal` = 'debit' then coalesce(sum(case when `jd`.`debit` > 0 then `jd`.`debit` else 0 end),0) - coalesce(sum(case when `jd`.`kredit` > 0 then `jd`.`kredit` else 0 end),0) else coalesce(sum(case when `jd`.`kredit` > 0 then `jd`.`kredit` else 0 end),0) - coalesce(sum(case when `jd`.`debit` > 0 then `jd`.`debit` else 0 end),0) end AS `saldo_akhir` from (`akun` `a` left join `jurnal_detail` `jd` on(`a`.`kode` = `jd`.`akun_kode`)) group by `a`.`kode`,`a`.`nama`,`a`.`tipe`,`a`.`kategori`,`a`.`saldo_normal` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_penagihan_hari_ini`
--

/*!50001 DROP VIEW IF EXISTS `v_penagihan_hari_ini`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_penagihan_hari_ini` AS select `p`.`id` AS `id`,`p`.`pinjaman_id` AS `pinjaman_id`,`n`.`kode_nasabah` AS `kode_nasabah`,`n`.`nama` AS `nama_nasabah`,`n`.`telp` AS `telp`,`n`.`alamat` AS `alamat`,`n`.`province_id` AS `province_id`,`n`.`regency_id` AS `regency_id`,`n`.`district_id` AS `district_id`,`n`.`village_id` AS `village_id`,`a`.`no_angsuran` AS `no_angsuran`,`a`.`jatuh_tempo` AS `jatuh_tempo`,`a`.`total_angsuran` AS `total_angsuran`,`a`.`total_bayar` AS `total_bayar`,`a`.`status` AS `status_angsuran`,`p`.`jenis` AS `jenis`,`p`.`status` AS `status_penagihan`,`p`.`petugas_id` AS `petugas_id`,`u`.`nama` AS `nama_petugas`,`p`.`catatan` AS `catatan` from ((((`penagihan` `p` join `pinjaman` `pin` on(`p`.`pinjaman_id` = `pin`.`id`)) join `nasabah` `n` on(`pin`.`nasabah_id` = `n`.`id`)) left join `angsuran` `a` on(`p`.`angsuran_id` = `a`.`id`)) left join `users` `u` on(`p`.`petugas_id` = `u`.`id`)) where `p`.`status` in ('pending','dalam_proses') order by `p`.`tanggal_jatuh_tempo` */;
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

-- Dump completed on 2026-05-10 11:27:14
