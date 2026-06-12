<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('pelajar');

$db = Database::getConnection();
$pelajarId = Auth::getPelajarId();
$currentPage = 'dashboard';

$guruWali = $db->prepare("SELECT u.nama_lengkap, u.email, gw.bio, gw.bidang_keahlian FROM penugasan pn
    JOIN guru_wali gw ON pn.guru_wali_id = gw.id JOIN users u ON gw.user_id = u.id
    WHERE pn.pelajar_id = ? AND pn.is_active = 1 LIMIT 1");
$guruWali->execute([$pelajarId]);
$guru = $guruWali->fetch();

$stats = [
    'laporan' => $db->prepare("SELECT COUNT(*) as c FROM laporan_masalah WHERE pelajar_id = ? AND status IN ('baru','diproses','ditanggapi')"),
    'sesi' => $db->prepare("SELECT COUNT(*) as c FROM sesi_bimbingan WHERE pelajar_id = ? AND status = 'dijadwalkan' AND tanggal_sesi >= NOW()"),
    'sharing' => $db->query("SELECT COUNT(*) as c FROM sharing WHERE is_published = 1")->fetch()['c'],
    'jurnal' => $db->prepare('SELECT COUNT(*) as c FROM jurnal_siswa WHERE pelajar_id = ?'),
];
$stats['laporan']->execute([$pelajarId]); $stats['laporan'] = $stats['laporan']->fetch()['c'];
$stats['sesi']->execute([$pelajarId]); $stats['sesi'] = $stats['sesi']->fetch()['c'];
$stats['jurnal']->execute([$pelajarId]); $stats['jurnal'] = $stats['jurnal']->fetch()['c'];

$sharingTerbaru = $db->query("SELECT s.*, u.nama_lengkap FROM sharing s JOIN guru_wali gw ON s.guru_wali_id = gw.id JOIN users u ON gw.user_id = u.id WHERE s.is_published = 1 ORDER BY s.created_at DESC LIMIT 3")->fetchAll();

ob_start();
?>
<div class="welcome-banner">
    <h2>Halo, <?= e($_SESSION['user_name']) ?></h2>
    <p>Ruang aman untuk berbagi, belajar, dan berkembang bersama guru wali Anda.</p>
</div>

<?php if ($guru): ?>
<div class="card" style="margin-bottom:24px">
    <div class="card-body" style="display:flex;align-items:center;gap:16px">
        <div class="avatar" style="width:56px;height:56px;font-size:1.1rem"><?= e(getInitials($guru['nama_lengkap'])) ?></div>
        <div>
            <p style="font-size:0.85rem;color:var(--color-text-muted)">Guru Wali Anda</p>
            <h3 style="font-size:1.1rem"><?= e($guru['nama_lengkap']) ?></h3>
            <?php if ($guru['bidang_keahlian']): ?><p style="color:var(--color-text-muted);font-size:0.9rem"><?= e($guru['bidang_keahlian']) ?></p><?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon warning"><?= iconReport() ?></div>
        <div class="stat-info"><h3><?= $stats['laporan'] ?></h3><p>Laporan Aktif</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><?= iconCalendar() ?></div>
        <div class="stat-info"><h3><?= $stats['sesi'] ?></h3><p>Sesi Mendatang</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><?= iconJournal() ?></div>
        <div class="stat-info"><h3><?= $stats['jurnal'] ?></h3><p>Entri Jurnal</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary"><?= iconShare() ?></div>
        <div class="stat-info"><h3><?= $stats['sharing'] ?></h3><p>Konten Sharing</p></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Sharing Terbaru dari Guru Wali</h2><a href="sharing.php" class="btn btn-sm btn-secondary">Lihat Semua</a></div>
    <div class="card-body">
        <?php foreach ($sharingTerbaru as $s): ?>
        <div class="list-item">
            <div class="list-item-content">
                <div class="list-item-title"><?= e($s['judul']) ?></div>
                <div class="list-item-meta">
                    <span><?= e($s['nama_lengkap']) ?></span>
                    <span class="badge badge-primary"><?= e(kategoriLabel($s['kategori'])) ?></span>
                    <span><?= timeAgo($s['created_at']) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($sharingTerbaru)): ?><div class="empty-state"><p>Belum ada sharing</p></div><?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/profil.php';
renderLayout('Dashboard', $content, ['page' => $currentPage, 'sidebar' => pelajarSidebar($currentPage)]);
