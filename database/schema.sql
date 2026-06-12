-- BimaGuru: Sistem Bimbingan Guru Wali
-- Database Schema

CREATE DATABASE IF NOT EXISTS bimaguru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bimaguru;

-- Pengguna sistem
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(150) NOT NULL,
    role ENUM('admin', 'guru_wali', 'pelajar') NOT NULL,
    foto_profil VARCHAR(255) DEFAULT NULL,
    no_telepon VARCHAR(20) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Profil guru wali
CREATE TABLE guru_wali (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    nip VARCHAR(30) DEFAULT NULL,
    bidang_keahlian VARCHAR(100) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    max_siswa INT DEFAULT 25,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Profil pelajar
CREATE TABLE pelajar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    nis VARCHAR(30) DEFAULT NULL,
    kelas VARCHAR(20) DEFAULT NULL,
    jurusan VARCHAR(50) DEFAULT NULL,
    tanggal_lahir DATE DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Penugasan guru wali ke pelajar (fleksibel)
CREATE TABLE penugasan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_wali_id INT NOT NULL,
    pelajar_id INT NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    catatan TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_wali_id) REFERENCES guru_wali(id) ON DELETE CASCADE,
    FOREIGN KEY (pelajar_id) REFERENCES pelajar(id) ON DELETE CASCADE,
    INDEX idx_guru (guru_wali_id),
    INDEX idx_pelajar (pelajar_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Laporan permasalahan siswa
CREATE TABLE laporan_masalah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelajar_id INT NOT NULL,
    guru_wali_id INT DEFAULT NULL,
    judul VARCHAR(200) NOT NULL,
    kategori ENUM('psikologis', 'pendidikan', 'sosial', 'materi', 'keluarga', 'lainnya') NOT NULL,
    deskripsi TEXT NOT NULL,
    tingkat_urgensi ENUM('rendah', 'sedang', 'tinggi') DEFAULT 'sedang',
    status ENUM('baru', 'diproses', 'ditanggapi', 'selesai', 'ditutup') DEFAULT 'baru',
    is_anonim TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pelajar_id) REFERENCES pelajar(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_wali_id) REFERENCES guru_wali(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_kategori (kategori)
) ENGINE=InnoDB;

-- Tanggapan guru wali terhadap laporan
CREATE TABLE tanggapan_laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laporan_id INT NOT NULL,
    guru_wali_id INT NOT NULL,
    isi_tanggapan TEXT NOT NULL,
    saran TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (laporan_id) REFERENCES laporan_masalah(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_wali_id) REFERENCES guru_wali(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sesi bimbingan
CREATE TABLE sesi_bimbingan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_wali_id INT NOT NULL,
    pelajar_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    topik VARCHAR(150) DEFAULT NULL,
    tanggal_sesi DATETIME NOT NULL,
    durasi_menit INT DEFAULT 30,
    lokasi VARCHAR(100) DEFAULT 'Online',
    catatan TEXT DEFAULT NULL,
    status ENUM('dijadwalkan', 'berlangsung', 'selesai', 'dibatalkan') DEFAULT 'dijadwalkan',
    ringkasan TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_wali_id) REFERENCES guru_wali(id) ON DELETE CASCADE,
    FOREIGN KEY (pelajar_id) REFERENCES pelajar(id) ON DELETE CASCADE,
    INDEX idx_tanggal (tanggal_sesi)
) ENGINE=InnoDB;

-- Sharing / posting dari guru wali
CREATE TABLE sharing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_wali_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    konten TEXT NOT NULL,
    kategori ENUM('motivasi', 'pengalaman', 'tips', 'informasi', 'refleksi') DEFAULT 'informasi',
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_wali_id) REFERENCES guru_wali(id) ON DELETE CASCADE,
    INDEX idx_published (is_published)
) ENGINE=InnoDB;

-- Komentar pada sharing
CREATE TABLE komentar_sharing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sharing_id INT NOT NULL,
    user_id INT NOT NULL,
    isi TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sharing_id) REFERENCES sharing(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Jurnal refleksi siswa
CREATE TABLE jurnal_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelajar_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    mood ENUM('senang', 'netral', 'sedih', 'cemas', 'semangat') DEFAULT 'netral',
    is_private TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pelajar_id) REFERENCES pelajar(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Catatan guru wali (privat, untuk siswa)
CREATE TABLE catatan_guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_wali_id INT NOT NULL,
    pelajar_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    kategori ENUM('perkembangan', 'perhatian', 'prestasi', 'perilaku', 'lainnya') DEFAULT 'perkembangan',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_wali_id) REFERENCES guru_wali(id) ON DELETE CASCADE,
    FOREIGN KEY (pelajar_id) REFERENCES pelajar(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Kemajuan / progress tracking siswa
CREATE TABLE kemajuan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelajar_id INT NOT NULL,
    guru_wali_id INT NOT NULL,
    aspek ENUM('akademik', 'sosial', 'emosional', 'spiritual', 'karakter') NOT NULL,
    nilai INT DEFAULT 0 CHECK (nilai >= 0 AND nilai <= 100),
    catatan TEXT DEFAULT NULL,
    periode VARCHAR(20) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pelajar_id) REFERENCES pelajar(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_wali_id) REFERENCES guru_wali(id) ON DELETE CASCADE,
    INDEX idx_periode (periode)
) ENGINE=InnoDB;

-- Materi bimbingan
CREATE TABLE materi_bimbingan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_wali_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    konten TEXT NOT NULL,
    kategori ENUM('psikologis', 'pendidikan', 'sosial', 'karakter', 'umum') DEFAULT 'umum',
    file_path VARCHAR(255) DEFAULT NULL,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_wali_id) REFERENCES guru_wali(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Notifikasi
CREATE TABLE notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    pesan TEXT NOT NULL,
    tipe ENUM('info', 'laporan', 'sesi', 'sharing', 'sistem') DEFAULT 'info',
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB;

-- Pengaturan sistem
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kunci VARCHAR(100) NOT NULL UNIQUE,
    nilai TEXT DEFAULT NULL,
    keterangan VARCHAR(255) DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Log aktivitas admin
CREATE TABLE log_aktivitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    aksi VARCHAR(100) NOT NULL,
    detail TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Data awal
INSERT INTO users (email, password_hash, nama_lengkap, role) VALUES
('admin@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Sistem', 'admin');

INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES
('nama_aplikasi', 'BimaGuru', 'Nama aplikasi'),
('tagline', 'Bimbingan Guru Wali Digital', 'Tagline aplikasi'),
('max_siswa_per_guru', '25', 'Maksimal siswa per guru wali'),
('warna_utama', '#D4A017', 'Warna tema utama');
