<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/profil.php';
Auth::requireRole('pelajar');

$db = Database::getConnection();
$pelajarId = Auth::getPelajarId();
$currentPage = 'materi';

$guruId = $db->prepare('SELECT guru_wali_id FROM penugasan WHERE pelajar_id = ? AND is_active = 1 LIMIT 1');
$guruId->execute([$pelajarId]);
$gRow = $guruId->fetch();

$list = [];
if ($gRow) {
    $materi = $db->prepare("SELECT m.*, u.nama_lengkap FROM materi_bimbingan m JOIN guru_wali gw ON m.guru_wali_id = gw.id JOIN users u ON gw.user_id = u.id WHERE m.guru_wali_id = ? AND m.is_published = 1 ORDER BY m.created_at DESC");
    $materi->execute([$gRow['guru_wali_id']]);
    $list = $materi->fetchAll();
}

$detailId = (int)getInput('id', 0);
$detail = null;
if ($detailId) {
    foreach ($list as $m) { if ((int)$m['id'] === $detailId) { $detail = $m; break; } }
}

ob_start();
?>
<?php if ($detail): ?>
<div class="card">
    <div class="card-header"><h2><?= e($detail['judul']) ?></h2><a href="materi.php" class="btn btn-sm btn-secondary">Kembali</a></div>
    <div class="card-body">
        <div class="list-item-meta" style="margin-bottom:16px">
            <span>Oleh: <?= e($detail['nama_lengkap']) ?></span>
            <span class="badge badge-primary"><?= e(kategoriLabel($detail['kategori'])) ?></span>
        </div>
        <?php if ($detail['deskripsi']): ?><p style="color:var(--color-text-muted);margin-bottom:16px"><?= e($detail['deskripsi']) ?></p><?php endif; ?>
        <div style="line-height:1.8"><?= nl2br(e($detail['konten'])) ?></div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <?php foreach ($list as $m): ?>
        <div class="list-item">
            <div class="list-item-content">
                <div class="list-item-title"><?= e($m['judul']) ?></div>
                <div class="list-item-meta">
                    <span><?= e($m['nama_lengkap']) ?></span>
                    <span class="badge badge-primary"><?= e(kategoriLabel($m['kategori'])) ?></span>
                    <span><?= timeAgo($m['created_at']) ?></span>
                </div>
            </div>
            <a href="materi.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-primary">Baca</a>
        </div>
        <?php endforeach; ?>
        <?php if (empty($list)): ?><div class="empty-state"><p>Belum ada materi bimbingan</p></div><?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
renderLayout('Materi Bimbingan', $content, ['page' => $currentPage, 'sidebar' => pelajarSidebar($currentPage)]);
