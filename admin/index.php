<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('admin');

$db = Database::getConnection();

$stats = [
    'guru' => $db->query("SELECT COUNT(*) as c FROM guru_wali gw JOIN users u ON gw.user_id = u.id WHERE u.is_active = 1")->fetch()['c'],
    'pelajar' => $db->query("SELECT COUNT(*) as c FROM pelajar p JOIN users u ON p.user_id = u.id WHERE u.is_active = 1")->fetch()['c'],
    'laporan' => $db->query("SELECT COUNT(*) as c FROM laporan_masalah WHERE status IN ('baru','diproses')")->fetch()['c'],
    'sesi' => $db->query("SELECT COUNT(*) as c FROM sesi_bimbingan WHERE status = 'dijadwalkan' AND tanggal_sesi >= NOW()")->fetch()['c'],
];

$recentLogs = $db->query("SELECT la.*, u.nama_lengkap FROM log_aktivitas la JOIN users u ON la.admin_id = u.id ORDER BY la.created_at DESC LIMIT 8")->fetchAll();

$currentPage = 'dashboard';
ob_start();
?>
<div class="welcome-banner">
    <h2>Selamat datang, <?= e($_SESSION['user_name']) ?></h2>
    <p>Kelola sistem bimbingan guru wali secara komprehensif dari panel admin ini.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><?= iconUsers() ?></div>
        <div class="stat-info"><h3><?= $stats['guru'] ?></h3><p>Guru Wali Aktif</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><?= iconProfile() ?></div>
        <div class="stat-info"><h3><?= $stats['pelajar'] ?></h3><p>Pelajar Terdaftar</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><?= iconReport() ?></div>
        <div class="stat-info"><h3><?= $stats['laporan'] ?></h3><p>Laporan Aktif</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><?= iconCalendar() ?></div>
        <div class="stat-info"><h3><?= $stats['sesi'] ?></h3><p>Sesi Terjadwal</p></div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h2>Aksi Cepat</h2></div>
        <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:10px">
                <a href="users.php?action=create&role=guru_wali" class="btn btn-secondary">Tambah Guru Wali Baru</a>
                <a href="users.php?action=create&role=pelajar" class="btn btn-secondary">Tambah Pelajar Baru</a>
                <a href="penugasan.php" class="btn btn-secondary">Atur Penugasan Guru-Siswa</a>
                <a href="pengaturan.php" class="btn btn-secondary">Pengaturan Sistem</a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h2>Log Aktivitas Terbaru</h2></div>
        <div class="card-body">
            <?php if (empty($recentLogs)): ?>
            <div class="empty-state"><p>Belum ada aktivitas</p></div>
            <?php else: ?>
            <?php foreach ($recentLogs as $log): ?>
            <div class="list-item">
                <div class="list-item-content">
                    <div class="list-item-title"><?= e($log['aksi']) ?></div>
                    <div class="list-item-meta">
                        <span><?= e($log['nama_lengkap']) ?></span>
                        <span><?= timeAgo($log['created_at']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$sidebar = navItem(APP_URL.'/admin/index.php','Dashboard',iconDashboard(),$currentPage,'dashboard')
    . navItem(APP_URL.'/admin/users.php','Pengguna',iconUsers(),$currentPage,'users')
    . navItem(APP_URL.'/admin/penugasan.php','Penugasan',iconAssign(),$currentPage,'penugasan')
    . navItem(APP_URL.'/admin/laporan.php','Laporan',iconReport(),$currentPage,'laporan')
    . navItem(APP_URL.'/admin/sharing.php','Sharing',iconShare(),$currentPage,'sharing')
    . navItem(APP_URL.'/admin/pengaturan.php','Pengaturan',iconSettings(),$currentPage,'pengaturan')
    . navItem(APP_URL.'/admin/log.php','Log Aktivitas',iconLog(),$currentPage,'log');
renderLayout('Dashboard Admin', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
