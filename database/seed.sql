-- Data contoh BimaGuru
-- Password semua akun: password

INSERT INTO users (email, password_hash, nama_lengkap, role, no_telepon) VALUES
('guru@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ibu Siti Rahayu, S.Pd', 'guru_wali', '081234567890');

SET @guru_user_id = LAST_INSERT_ID();

INSERT INTO guru_wali (user_id, nip, bidang_keahlian, bio, max_siswa) VALUES
(@guru_user_id, '198501152010012001', 'Bimbingan Konseling', 'Guru wali dengan pengalaman 15 tahun mendampingi siswa. Percaya setiap anak punya potensi unik yang perlu dirawat dengan penuh kasih.', 25);

SET @guru_wali_id = LAST_INSERT_ID();

INSERT INTO users (email, password_hash, nama_lengkap, role) VALUES
('siswa01@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Fauzi', 'pelajar'),
('siswa02@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'pelajar'),
('siswa03@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Citra Dewi', 'pelajar'),
('siswa04@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dian Permata', 'pelajar'),
('siswa05@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eka Putri', 'pelajar'),
('siswa06@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fajar Nugroho', 'pelajar'),
('siswa07@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gita Anindya', 'pelajar'),
('siswa08@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hadi Wijaya', 'pelajar'),
('siswa09@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Indah Lestari', 'pelajar'),
('siswa10@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Joko Susilo', 'pelajar'),
('siswa11@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kartika Sari', 'pelajar'),
('siswa12@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lukman Hakim', 'pelajar'),
('siswa13@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maya Anggraini', 'pelajar'),
('siswa14@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nanda Pratama', 'pelajar'),
('siswa15@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Oki Ramadhan', 'pelajar'),
('siswa16@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Putri Ayu', 'pelajar'),
('siswa17@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rizki Maulana', 'pelajar'),
('siswa18@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Salsa Bila', 'pelajar'),
('siswa19@bimaguru.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teguh Prasetyo', 'pelajar');

INSERT INTO pelajar (user_id, nis, kelas, jurusan) VALUES
((SELECT id FROM users WHERE email='siswa01@bimaguru.local'), '2024001', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa02@bimaguru.local'), '2024002', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa03@bimaguru.local'), '2024003', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa04@bimaguru.local'), '2024004', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa05@bimaguru.local'), '2024005', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa06@bimaguru.local'), '2024006', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa07@bimaguru.local'), '2024007', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa08@bimaguru.local'), '2024008', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa09@bimaguru.local'), '2024009', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa10@bimaguru.local'), '2024010', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa11@bimaguru.local'), '2024011', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa12@bimaguru.local'), '2024012', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa13@bimaguru.local'), '2024013', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa14@bimaguru.local'), '2024014', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa15@bimaguru.local'), '2024015', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa16@bimaguru.local'), '2024016', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa17@bimaguru.local'), '2024017', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa18@bimaguru.local'), '2024018', 'XII IPA 1', 'IPA'),
((SELECT id FROM users WHERE email='siswa19@bimaguru.local'), '2024019', 'XII IPA 1', 'IPA');

INSERT INTO penugasan (guru_wali_id, pelajar_id, tanggal_mulai)
SELECT @guru_wali_id, id, '2024-07-01' FROM pelajar;

UPDATE laporan_masalah SET guru_wali_id = @guru_wali_id WHERE 1=0;

INSERT INTO sharing (guru_wali_id, judul, konten, kategori) VALUES
(@guru_wali_id, 'Semangat Pagi, Anak-Anakku!', 'Setiap pagi adalah kesempatan baru untuk menjadi versi terbaik diri kita. Ingat, kegagalan bukan akhir — itu bagian dari perjalanan belajar. Kalau kemarin terasa berat, hari ini coba mulai dengan langkah kecil. Aku percaya kalian semua bisa.', 'motivasi'),
(@guru_wali_id, 'Pengalaman Menghadapi Ujian', 'Dulu waktu aku sekolah, aku juga pernah panik menjelang ujian. Yang membantu bukan begadang, tapi atur waktu dan istirahat cukup. Buatlah jadwal belajar, diskusi dengan teman, dan jangan lupa doa. Hasil ujian penting, tapi proses belajarnya lebih berharga.', 'pengalaman'),
(@guru_wali_id, 'Tips Mengelola Emosi', 'Ketika marah atau sedih, coba tarik napas 4 detik, tahan 4 detik, hembuskan 4 detik. Tuliskan perasaanmu di jurnal. Ceritakan pada orang yang kamu percaya. Emosi itu normal — yang penting bagaimana kita meresponsnya.', 'tips');

INSERT INTO materi_bimbingan (guru_wali_id, judul, deskripsi, konten, kategori) VALUES
(@guru_wali_id, 'Membangun Kepercayaan Diri', 'Panduan praktis meningkatkan rasa percaya diri', 'Kepercayaan diri dibangun dari pengakuan akan kelebihan dan penerimaan akan kekurangan. Latihan public speaking, menetapkan target kecil, dan merayakan pencapaian kecil bisa membantu.', 'psikologis'),
(@guru_wali_id, 'Strategi Belajar Efektif', 'Metode belajar yang terbukti efektif', 'Gunakan teknik Pomodoro (25 menit belajar, 5 menit istirahat), buat mind map, ajarkan kembali materi ke teman, dan ulang materi sebelum tidur.', 'pendidikan');

INSERT INTO sesi_bimbingan (guru_wali_id, pelajar_id, judul, topik, tanggal_sesi, durasi_menit, lokasi, status) VALUES
(@guru_wali_id, (SELECT id FROM pelajar WHERE nis='2024001'), 'Bimbingan Akademik', 'Strategi belajar matematika', DATE_ADD(NOW(), INTERVAL 2 DAY), 30, 'Ruang BK', 'dijadwalkan'),
(@guru_wali_id, (SELECT id FROM pelajar WHERE nis='2024003'), 'Konsultasi Pribadi', 'Mengelola stres ujian', DATE_ADD(NOW(), INTERVAL 3 DAY), 45, 'Online', 'dijadwalkan');

INSERT INTO laporan_masalah (pelajar_id, guru_wali_id, judul, kategori, deskripsi, tingkat_urgensi, status) VALUES
((SELECT id FROM pelajar WHERE nis='2024005'), @guru_wali_id, 'Kesulitan Fokus Belajar', 'pendidikan', 'Akhir-akhir ini sulit fokus saat belajar di rumah karena lingkungan bising. Butuh saran cara mengatur waktu belajar.', 'sedang', 'baru'),
((SELECT id FROM pelajar WHERE nis='2024010'), @guru_wali_id, 'Merasa Cemas Sosial', 'psikologis', 'Sering merasa gugup saat presentasi di depan kelas. Ingin belajar mengatasi rasa cemas ini.', 'sedang', 'baru');

INSERT INTO kemajuan (pelajar_id, guru_wali_id, aspek, nilai, catatan, periode) VALUES
((SELECT id FROM pelajar WHERE nis='2024001'), @guru_wali_id, 'akademik', 85, 'Menunjukkan peningkatan di matematika', '2024-12'),
((SELECT id FROM pelajar WHERE nis='2024001'), @guru_wali_id, 'sosial', 78, 'Aktif dalam diskusi kelompok', '2024-12'),
((SELECT id FROM pelajar WHERE nis='2024001'), @guru_wali_id, 'emosional', 72, 'Perlu pendampingan saat stres ujian', '2024-12'),
((SELECT id FROM pelajar WHERE nis='2024001'), @guru_wali_id, 'spiritual', 80, 'Konsisten dalam ibadah', '2024-12'),
((SELECT id FROM pelajar WHERE nis='2024001'), @guru_wali_id, 'karakter', 88, 'Jujur dan bertanggung jawab', '2024-12');

INSERT INTO jurnal_siswa (pelajar_id, judul, isi, mood) VALUES
((SELECT id FROM pelajar WHERE nis='2024001'), 'Hari yang Produktif', 'Hari ini berhasil menyelesaikan tugas matematika dan presentasi kelompok. Merasa lega dan bangga.', 'senang');

INSERT INTO catatan_guru (guru_wali_id, pelajar_id, judul, isi, kategori) VALUES
(@guru_wali_id, (SELECT id FROM pelajar WHERE nis='2024001'), 'Observasi Januari', 'Ahmad menunjukkan kemajuan signifikan dalam partisipasi kelas. Perlu dorongan untuk public speaking.', 'perkembangan');
