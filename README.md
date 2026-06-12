# BimaGuru

Sistem Bimbingan Guru Wali Digital — aplikasi web berbasis PHP dan JavaScript untuk mendukung hubungan bimbingan antara guru wali dan pelajar.

## Fitur Utama

### Admin
- Kelola pengguna (guru wali & pelajar)
- Atur penugasan guru wali ke pelajar (fleksibel)
- Monitor laporan masalah & konten sharing
- Pengaturan sistem & log aktivitas
- Tidak dapat mengubah kata sandi pengguna (privasi login)

### Guru Wali
- Dashboard siswa bimbingan (~19 siswa per guru)
- Tanggapi laporan masalah siswa (psikologis, pendidikan, sosial, dll)
- Jadwalkan sesi bimbingan
- Sharing pengalaman, motivasi, tips
- Materi bimbingan & catatan privat siswa
- Pantau kemajuan perkembangan siswa

### Pelajar
- Kirim laporan masalah (bisa anonim)
- Jurnal refleksi harian dengan mood tracker
- Baca sharing & berkomentar
- Lihat sesi bimbingan & materi
- Pantau kemajuan perkembangan

## Instalasi

1. Pastikan XAMPP (Apache + MySQL) berjalan
2. Clone/copy proyek ke `htdocs/pjwd`
3. Buka `http://localhost/pjwd/install.php`
4. Klik **Install Sekarang**
5. Login dengan akun demo:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@bimaguru.local | password |
| Guru Wali | guru@bimaguru.local | password |
| Pelajar | siswa01@bimaguru.local | password |

## Struktur Proyek

```
pjwd/
├── admin/          # Panel administrator
├── guru/           # Panel guru wali
├── pelajar/        # Panel pelajar
├── api/            # REST API endpoints
├── assets/         # CSS, JS
├── config/         # Konfigurasi database
├── database/       # Schema & seed SQL
├── includes/       # Auth, helpers, layout
├── uploads/        # File uploads
├── install.php     # Installer
└── login.php       # Halaman login
```

## Teknologi

- **Backend:** PHP 8+ (PDO, session auth)
- **Frontend:** Vanilla JavaScript, CSS custom
- **Database:** MySQL/MariaDB
- **UI:** Tema hangat kekuningan, SVG icons, clean design

## Konfigurasi

Edit `config/config.php` jika perlu:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'bimaguru');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_URL', 'http://localhost/pjwd');
```
