-- BimaGuru Database Dump
-- Generated: 2026-06-14 00:53

CREATE DATABASE IF NOT EXISTS bimaguru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bimaguru;

/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.13-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: bimaguru
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB

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
-- Table structure for table `catatan_guru`
--

DROP TABLE IF EXISTS `catatan_guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `catatan_guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_wali_id` int(11) NOT NULL,
  `pelajar_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `kategori` enum('perkembangan','perhatian','prestasi','perilaku','lainnya') DEFAULT 'perkembangan',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guru_wali_id` (`guru_wali_id`),
  KEY `pelajar_id` (`pelajar_id`),
  CONSTRAINT `catatan_guru_ibfk_1` FOREIGN KEY (`guru_wali_id`) REFERENCES `guru_wali` (`id`) ON DELETE CASCADE,
  CONSTRAINT `catatan_guru_ibfk_2` FOREIGN KEY (`pelajar_id`) REFERENCES `pelajar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catatan_guru`
--

LOCK TABLES `catatan_guru` WRITE;
/*!40000 ALTER TABLE `catatan_guru` DISABLE KEYS */;
INSERT INTO `catatan_guru` VALUES
(1,1,1,'Observasi Januari','Ahmad menunjukkan kemajuan signifikan dalam partisipasi kelas. Perlu dorongan untuk public speaking.','perkembangan','2026-06-14 00:45:23','2026-06-14 00:45:23');
/*!40000 ALTER TABLE `catatan_guru` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guru_wali`
--

DROP TABLE IF EXISTS `guru_wali`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guru_wali` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `bidang_keahlian` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `max_siswa` int(11) DEFAULT 25,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `guru_wali_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guru_wali`
--

LOCK TABLES `guru_wali` WRITE;
/*!40000 ALTER TABLE `guru_wali` DISABLE KEYS */;
INSERT INTO `guru_wali` VALUES
(1,2,'198501152010012001','Bimbingan Konseling','Guru wali dengan pengalaman 15 tahun mendampingi siswa. Percaya setiap anak punya potensi unik yang perlu dirawat dengan penuh kasih.',25,'2026-06-14 00:45:23');
/*!40000 ALTER TABLE `guru_wali` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal_siswa`
--

DROP TABLE IF EXISTS `jurnal_siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurnal_siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pelajar_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `mood` enum('senang','netral','sedih','cemas','semangat') DEFAULT 'netral',
  `is_private` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pelajar_id` (`pelajar_id`),
  CONSTRAINT `jurnal_siswa_ibfk_1` FOREIGN KEY (`pelajar_id`) REFERENCES `pelajar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal_siswa`
--

LOCK TABLES `jurnal_siswa` WRITE;
/*!40000 ALTER TABLE `jurnal_siswa` DISABLE KEYS */;
INSERT INTO `jurnal_siswa` VALUES
(1,1,'Hari yang Produktif','Hari ini berhasil menyelesaikan tugas matematika dan presentasi kelompok. Merasa lega dan bangga.','senang',1,'2026-06-14 00:45:23','2026-06-14 00:45:23');
/*!40000 ALTER TABLE `jurnal_siswa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kemajuan`
--

DROP TABLE IF EXISTS `kemajuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kemajuan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pelajar_id` int(11) NOT NULL,
  `guru_wali_id` int(11) NOT NULL,
  `aspek` enum('akademik','sosial','emosional','spiritual','karakter') NOT NULL,
  `nilai` int(11) DEFAULT 0 CHECK (`nilai` >= 0 and `nilai` <= 100),
  `catatan` text DEFAULT NULL,
  `periode` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pelajar_id` (`pelajar_id`),
  KEY `guru_wali_id` (`guru_wali_id`),
  KEY `idx_periode` (`periode`),
  CONSTRAINT `kemajuan_ibfk_1` FOREIGN KEY (`pelajar_id`) REFERENCES `pelajar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kemajuan_ibfk_2` FOREIGN KEY (`guru_wali_id`) REFERENCES `guru_wali` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kemajuan`
--

LOCK TABLES `kemajuan` WRITE;
/*!40000 ALTER TABLE `kemajuan` DISABLE KEYS */;
INSERT INTO `kemajuan` VALUES
(1,1,1,'akademik',85,'Menunjukkan peningkatan di matematika','2024-12','2026-06-14 00:45:23'),
(2,1,1,'sosial',78,'Aktif dalam diskusi kelompok','2024-12','2026-06-14 00:45:23'),
(3,1,1,'emosional',72,'Perlu pendampingan saat stres ujian','2024-12','2026-06-14 00:45:23'),
(4,1,1,'spiritual',80,'Konsisten dalam ibadah','2024-12','2026-06-14 00:45:23'),
(5,1,1,'karakter',88,'Jujur dan bertanggung jawab','2024-12','2026-06-14 00:45:23');
/*!40000 ALTER TABLE `kemajuan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `komentar_sharing`
--

DROP TABLE IF EXISTS `komentar_sharing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `komentar_sharing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sharing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `isi` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sharing_id` (`sharing_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `komentar_sharing_ibfk_1` FOREIGN KEY (`sharing_id`) REFERENCES `sharing` (`id`) ON DELETE CASCADE,
  CONSTRAINT `komentar_sharing_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `komentar_sharing`
--

LOCK TABLES `komentar_sharing` WRITE;
/*!40000 ALTER TABLE `komentar_sharing` DISABLE KEYS */;
/*!40000 ALTER TABLE `komentar_sharing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `laporan_masalah`
--

DROP TABLE IF EXISTS `laporan_masalah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `laporan_masalah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pelajar_id` int(11) NOT NULL,
  `guru_wali_id` int(11) DEFAULT NULL,
  `judul` varchar(200) NOT NULL,
  `kategori` enum('psikologis','pendidikan','sosial','materi','keluarga','lainnya') NOT NULL,
  `deskripsi` text NOT NULL,
  `tingkat_urgensi` enum('rendah','sedang','tinggi') DEFAULT 'sedang',
  `status` enum('baru','diproses','ditanggapi','selesai','ditutup') DEFAULT 'baru',
  `is_anonim` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pelajar_id` (`pelajar_id`),
  KEY `guru_wali_id` (`guru_wali_id`),
  KEY `idx_status` (`status`),
  KEY `idx_kategori` (`kategori`),
  CONSTRAINT `laporan_masalah_ibfk_1` FOREIGN KEY (`pelajar_id`) REFERENCES `pelajar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laporan_masalah_ibfk_2` FOREIGN KEY (`guru_wali_id`) REFERENCES `guru_wali` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laporan_masalah`
--

LOCK TABLES `laporan_masalah` WRITE;
/*!40000 ALTER TABLE `laporan_masalah` DISABLE KEYS */;
INSERT INTO `laporan_masalah` VALUES
(1,5,1,'Kesulitan Fokus Belajar','pendidikan','Akhir-akhir ini sulit fokus saat belajar di rumah karena lingkungan bising. Butuh saran cara mengatur waktu belajar.','sedang','baru',0,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(2,10,1,'Merasa Cemas Sosial','psikologis','Sering merasa gugup saat presentasi di depan kelas. Ingin belajar mengatasi rasa cemas ini.','sedang','baru',0,'2026-06-14 00:45:23','2026-06-14 00:45:23');
/*!40000 ALTER TABLE `laporan_masalah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_aktivitas`
--

DROP TABLE IF EXISTS `log_aktivitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `aksi` varchar(100) NOT NULL,
  `detail` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_aktivitas`
--

LOCK TABLES `log_aktivitas` WRITE;
/*!40000 ALTER TABLE `log_aktivitas` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_aktivitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materi_bimbingan`
--

DROP TABLE IF EXISTS `materi_bimbingan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `materi_bimbingan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_wali_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `konten` text NOT NULL,
  `kategori` enum('psikologis','pendidikan','sosial','karakter','umum') DEFAULT 'umum',
  `file_path` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guru_wali_id` (`guru_wali_id`),
  CONSTRAINT `materi_bimbingan_ibfk_1` FOREIGN KEY (`guru_wali_id`) REFERENCES `guru_wali` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materi_bimbingan`
--

LOCK TABLES `materi_bimbingan` WRITE;
/*!40000 ALTER TABLE `materi_bimbingan` DISABLE KEYS */;
INSERT INTO `materi_bimbingan` VALUES
(1,1,'Membangun Kepercayaan Diri','Panduan praktis meningkatkan rasa percaya diri','Kepercayaan diri dibangun dari pengakuan akan kelebihan dan penerimaan akan kekurangan. Latihan public speaking, menetapkan target kecil, dan merayakan pencapaian kecil bisa membantu.','psikologis',NULL,1,'2026-06-14 00:45:23'),
(2,1,'Strategi Belajar Efektif','Metode belajar yang terbukti efektif','Gunakan teknik Pomodoro (25 menit belajar, 5 menit istirahat), buat mind map, ajarkan kembali materi ke teman, dan ulang materi sebelum tidur.','pendidikan',NULL,1,'2026-06-14 00:45:23');
/*!40000 ALTER TABLE `materi_bimbingan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifikasi`
--

DROP TABLE IF EXISTS `notifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `pesan` text NOT NULL,
  `tipe` enum('info','laporan','sesi','sharing','sistem') DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`,`is_read`),
  CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifikasi`
--

LOCK TABLES `notifikasi` WRITE;
/*!40000 ALTER TABLE `notifikasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pelajar`
--

DROP TABLE IF EXISTS `pelajar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pelajar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nis` varchar(30) DEFAULT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `pelajar_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pelajar`
--

LOCK TABLES `pelajar` WRITE;
/*!40000 ALTER TABLE `pelajar` DISABLE KEYS */;
INSERT INTO `pelajar` VALUES
(1,3,'2024001','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(2,4,'2024002','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(3,5,'2024003','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(4,6,'2024004','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(5,7,'2024005','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(6,8,'2024006','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(7,9,'2024007','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(8,10,'2024008','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(9,11,'2024009','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(10,12,'2024010','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(11,13,'2024011','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(12,14,'2024012','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(13,15,'2024013','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(14,16,'2024014','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(15,17,'2024015','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(16,18,'2024016','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(17,19,'2024017','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(18,20,'2024018','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23'),
(19,21,'2024019','XII IPA 1','IPA',NULL,NULL,'2026-06-14 00:45:23');
/*!40000 ALTER TABLE `pelajar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan`
--

DROP TABLE IF EXISTS `pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kunci` varchar(100) NOT NULL,
  `nilai` text DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kunci` (`kunci`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan`
--

LOCK TABLES `pengaturan` WRITE;
/*!40000 ALTER TABLE `pengaturan` DISABLE KEYS */;
INSERT INTO `pengaturan` VALUES
(1,'nama_aplikasi','BimaGuru','Nama aplikasi','2026-06-14 00:45:23'),
(2,'tagline','Bimbingan Guru Wali Digital','Tagline aplikasi','2026-06-14 00:45:23'),
(3,'max_siswa_per_guru','25','Maksimal siswa per guru wali','2026-06-14 00:45:23'),
(4,'warna_utama','#D4A017','Warna tema utama','2026-06-14 00:45:23');
/*!40000 ALTER TABLE `pengaturan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penugasan`
--

DROP TABLE IF EXISTS `penugasan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `penugasan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_wali_id` int(11) NOT NULL,
  `pelajar_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `catatan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_guru` (`guru_wali_id`),
  KEY `idx_pelajar` (`pelajar_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `penugasan_ibfk_1` FOREIGN KEY (`guru_wali_id`) REFERENCES `guru_wali` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penugasan_ibfk_2` FOREIGN KEY (`pelajar_id`) REFERENCES `pelajar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penugasan`
--

LOCK TABLES `penugasan` WRITE;
/*!40000 ALTER TABLE `penugasan` DISABLE KEYS */;
INSERT INTO `penugasan` VALUES
(1,1,1,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(2,1,2,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(3,1,3,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(4,1,4,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(5,1,5,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(6,1,6,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(7,1,7,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(8,1,8,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(9,1,9,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(10,1,10,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(11,1,11,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(12,1,12,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(13,1,13,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(14,1,14,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(15,1,15,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(16,1,16,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(17,1,17,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(18,1,18,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23'),
(19,1,19,'2024-07-01',NULL,1,NULL,'2026-06-14 00:45:23');
/*!40000 ALTER TABLE `penugasan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesi_bimbingan`
--

DROP TABLE IF EXISTS `sesi_bimbingan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesi_bimbingan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_wali_id` int(11) NOT NULL,
  `pelajar_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `topik` varchar(150) DEFAULT NULL,
  `tanggal_sesi` datetime NOT NULL,
  `durasi_menit` int(11) DEFAULT 30,
  `lokasi` varchar(100) DEFAULT 'Online',
  `catatan` text DEFAULT NULL,
  `status` enum('dijadwalkan','berlangsung','selesai','dibatalkan') DEFAULT 'dijadwalkan',
  `ringkasan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guru_wali_id` (`guru_wali_id`),
  KEY `pelajar_id` (`pelajar_id`),
  KEY `idx_tanggal` (`tanggal_sesi`),
  CONSTRAINT `sesi_bimbingan_ibfk_1` FOREIGN KEY (`guru_wali_id`) REFERENCES `guru_wali` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sesi_bimbingan_ibfk_2` FOREIGN KEY (`pelajar_id`) REFERENCES `pelajar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesi_bimbingan`
--

LOCK TABLES `sesi_bimbingan` WRITE;
/*!40000 ALTER TABLE `sesi_bimbingan` DISABLE KEYS */;
INSERT INTO `sesi_bimbingan` VALUES
(1,1,1,'Bimbingan Akademik','Strategi belajar matematika','2026-06-16 00:45:23',30,'Ruang BK',NULL,'dijadwalkan',NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(2,1,3,'Konsultasi Pribadi','Mengelola stres ujian','2026-06-17 00:45:23',45,'Online',NULL,'dijadwalkan',NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23');
/*!40000 ALTER TABLE `sesi_bimbingan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sharing`
--

DROP TABLE IF EXISTS `sharing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sharing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_wali_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `konten` text NOT NULL,
  `kategori` enum('motivasi','pengalaman','tips','informasi','refleksi') DEFAULT 'informasi',
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guru_wali_id` (`guru_wali_id`),
  KEY `idx_published` (`is_published`),
  CONSTRAINT `sharing_ibfk_1` FOREIGN KEY (`guru_wali_id`) REFERENCES `guru_wali` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sharing`
--

LOCK TABLES `sharing` WRITE;
/*!40000 ALTER TABLE `sharing` DISABLE KEYS */;
INSERT INTO `sharing` VALUES
(1,1,'Semangat Pagi, Anak-Anakku!','Setiap pagi adalah kesempatan baru untuk menjadi versi terbaik diri kita. Ingat, kegagalan bukan akhir — itu bagian dari perjalanan belajar. Kalau kemarin terasa berat, hari ini coba mulai dengan langkah kecil. Aku percaya kalian semua bisa.','motivasi',1,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(2,1,'Pengalaman Menghadapi Ujian','Dulu waktu aku sekolah, aku juga pernah panik menjelang ujian. Yang membantu bukan begadang, tapi atur waktu dan istirahat cukup. Buatlah jadwal belajar, diskusi dengan teman, dan jangan lupa doa. Hasil ujian penting, tapi proses belajarnya lebih berharga.','pengalaman',1,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(3,1,'Tips Mengelola Emosi','Ketika marah atau sedih, coba tarik napas 4 detik, tahan 4 detik, hembuskan 4 detik. Tuliskan perasaanmu di jurnal. Ceritakan pada orang yang kamu percaya. Emosi itu normal — yang penting bagaimana kita meresponsnya.','tips',1,'2026-06-14 00:45:23','2026-06-14 00:45:23');
/*!40000 ALTER TABLE `sharing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tanggapan_laporan`
--

DROP TABLE IF EXISTS `tanggapan_laporan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tanggapan_laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `laporan_id` int(11) NOT NULL,
  `guru_wali_id` int(11) NOT NULL,
  `isi_tanggapan` text NOT NULL,
  `saran` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `laporan_id` (`laporan_id`),
  KEY `guru_wali_id` (`guru_wali_id`),
  CONSTRAINT `tanggapan_laporan_ibfk_1` FOREIGN KEY (`laporan_id`) REFERENCES `laporan_masalah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tanggapan_laporan_ibfk_2` FOREIGN KEY (`guru_wali_id`) REFERENCES `guru_wali` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tanggapan_laporan`
--

LOCK TABLES `tanggapan_laporan` WRITE;
/*!40000 ALTER TABLE `tanggapan_laporan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tanggapan_laporan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `role` enum('admin','guru_wali','pelajar') NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Administrator Sistem','admin',NULL,NULL,1,'2026-06-14 00:48:57','2026-06-14 00:45:23','2026-06-14 00:48:57'),
(2,'guru@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Ibu Siti Rahayu, S.Pd','guru_wali',NULL,'081234567890',1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(3,'siswa01@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Ahmad Fauzi','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(4,'siswa02@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Budi Santoso','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(5,'siswa03@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Citra Dewi','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(6,'siswa04@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Dian Permata','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(7,'siswa05@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Eka Putri','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(8,'siswa06@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Fajar Nugroho','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(9,'siswa07@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Gita Anindya','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(10,'siswa08@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Hadi Wijaya','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(11,'siswa09@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Indah Lestari','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(12,'siswa10@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Joko Susilo','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(13,'siswa11@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Kartika Sari','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(14,'siswa12@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Lukman Hakim','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(15,'siswa13@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Maya Anggraini','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(16,'siswa14@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Nanda Pratama','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(17,'siswa15@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Oki Ramadhan','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(18,'siswa16@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Putri Ayu','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(19,'siswa17@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Rizki Maulana','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(20,'siswa18@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Salsa Bila','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23'),
(21,'siswa19@bimaguru.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Teguh Prasetyo','pelajar',NULL,NULL,1,NULL,'2026-06-14 00:45:23','2026-06-14 00:45:23');
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

-- Dump completed on 2026-06-14  0:53:23
