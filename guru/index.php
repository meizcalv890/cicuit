<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('guru_wali');

$db = Database::getConnection();
$guruId = Auth::getGuruWaliId();
$currentPage = 'dashboard';

$stats = [
    'siswa' => $db->prepare('SELECT COUNT(*) as c FROM penugasan WHERE guru_wali_id = ? AND is_active = 1'),
    'laporan_baru' => $db->prepare("SELECT COUNT(*) as c FROM laporan_masalah WHERE guru_wali_id = ? AND status IN ('baru','diproses')"),
    'sesi' => $db->prepare("SELECT COUNT(*) as c FROM sesi_bimbingan WHERE guru_wali_id = ? AND status = 'dijadwalkan' AND tanggal_sesi >= NOW()"),
    'sharing' => $db->prepare('SELECT COUNT(*) as c FROM sharing WHERE guru_wali_id = ? AND is_published = 1'),
];
foreach ($stats as $key => $stmt) { $stmt->execute([$guruId]); $stats[$key] = $stmt->fetch()['c']; }

$laporanTerbaru = $db->prepare("SELECT lm.*, u.nama_lengkap FROM laporan_masalah lm
    JOIN pelajar p ON lm.pelajar_id = p.id JOIN users u ON p.user_id = u.id
    WHERE lm.guru_wali_id = ? ORDER BY lm.created_at DESC LIMIT 5");
$laporanTerbaru->execute([$guruId]);

$sesiMendatang = $db->prepare("SELECT sb.*, u.nama_lengkap FROM sesi_bimbingan sb
    JOIN pelajar p ON sb.pelajar_id = p.id JOIN users u ON p.user_id = u.id
    WHERE sb.guru_wali_id = ? AND sb.status = 'dijadwalkan' AND sb.tanggal_sesi >= NOW()
    ORDER BY sb.tanggal_sesi ASC LIMIT 5");
$sesiMendatang->execute([$guruId]);

ob_start();
?>
<div class="welcome-banner">
    <h2>Halo, <?= e($_SESSION['user_name']) ?></h2>
    <p>Bimbingi siswa dengan penuh kasih sayang. Setiap interaksi adalah langkah menuju perkembangan mereka.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><?= iconUsers() ?></div>
        <div class="stat-info"><h3><?= $stats['siswa'] ?></h3><p>Siswa Dibimbing</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><?= iconReport() ?></div>
        <div class="stat-info"><h3><?= $stats['laporan_baru'] ?></h3><p>Laporan Perlu Ditangani</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><?= iconCalendar() ?></div>
        <div class="stat-info"><h3><?= $stats['sesi'] ?></h3><p>Sesi Mendatang</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><?= iconShare() ?></div>
        <div class="stat-info"><h3><?= $stats['sharing'] ?></h3><p>Konten Sharing</p></div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h2>Laporan Terbaru</h2><a href="laporan.php" class="btn btn-sm btn-secondary">Lihat Semua</a></div>
        <div class="card-body">
            <?php $items = $laporanTerbaru->fetchAll(); ?>
            <?php if (empty($items)): ?>
            <div class="empty-state"><p>Belum ada laporan</p></div>
            <?php else: foreach ($items as $l): ?>
            <div class="list-item">
                <div class="list-item-content">
                    <div class="list-item-title"><?= e($l['judul']) ?></div>
                    <div class="list-item-meta">
                        <span><?= e($l['nama_lengkap']) ?></span>
                        <span class="badge <?= statusBadge($l['status']) ?>"><?= ucfirst($l['status']) ?></span>
                        <span><?= timeAgo($l['created_at']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h2>Sesi Mendatang</h2><a href="sesi.php" class="btn btn-sm btn-secondary">Kelola Sesi</a></div>
        <div class="card-body">
            <?php $sesi = $sesiMendatang->fetchAll(); ?>
            <?php if (empty($sesi)): ?>
            <div class="empty-state"><p>Tidak ada sesi terjadwal</p></div>
            <?php else: foreach ($sesi as $s): ?>
            <div class="list-item">
                <div class="list-item-content">
                    <div class="list-item-title"><?= e($s['judul']) ?></div>
                    <div class="list-item-meta">
                        <span><?= e($s['nama_lengkap']) ?></span>
                        <span><?= formatWaktu($s['tanggal_sesi']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$sidebar = navItem(APP_URL.'/guru/index.php','Dashboard',iconDashboard(),$currentPage,'dashboard')
    . navItem(APP_URL.'/guru/siswa.php','Siswa Saya',iconUsers(),$currentPage,'siswa')
    . navItem(APP_URL.'/guru/laporan.php','Laporan Masalah',iconReport(),$currentPage,'laporan')
    . navItem(APP_URL.'/guru/sesi.php','Sesi Bimbingan',iconCalendar(),$currentPage,'sesi')
    . navItem(APP_URL.'/guru/sharing.php','Sharing',iconShare(),$currentPage,'sharing')
    . navItem(APP_URL.'/guru/materi.php','Materi Bimbingan',iconBook(),$currentPage,'materi')
    . navItem(APP_URL.'/guru/catatan.php','Catatan Siswa',iconNote(),$currentPage,'catatan')
    . navItem(APP_URL.'/guru/kemajuan.php','Kemajuan Siswa',iconChart(),$currentPage,'kemajuan')
    . navItem(APP_URL.'/guru/profil.php','Profil',iconProfile(),$currentPage,'profil');
renderLayout('Dashboard Guru Wali', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
