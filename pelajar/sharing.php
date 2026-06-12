<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/profil.php';
Auth::requireRole('pelajar');

$db = Database::getConnection();
$currentPage = 'sharing';
$userId = Auth::id();

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $db->prepare('INSERT INTO komentar_sharing (sharing_id, user_id, isi) VALUES (?, ?, ?)')
       ->execute([(int)getInput('sharing_id'), $userId, trim(getInput('isi'))]);
    redirect(APP_URL . '/pelajar/sharing.php?id='.(int)getInput('sharing_id').'&msg=commented');
}

$detailId = (int)getInput('id', 0);
if ($detailId) {
    $detail = $db->prepare("SELECT s.*, u.nama_lengkap FROM sharing s JOIN guru_wali gw ON s.guru_wali_id = gw.id JOIN users u ON gw.user_id = u.id WHERE s.id = ? AND s.is_published = 1");
    $detail->execute([$detailId]);
    $sharingData = $detail->fetch();
    if ($sharingData) {
        $komentar = $db->prepare('SELECT ks.*, u.nama_lengkap, u.role FROM komentar_sharing ks JOIN users u ON ks.user_id = u.id WHERE ks.sharing_id = ? ORDER BY ks.created_at ASC');
        $komentar->execute([$detailId]);
        $komentarList = $komentar->fetchAll();
    }
}

$list = $db->query("SELECT s.*, u.nama_lengkap, (SELECT COUNT(*) FROM komentar_sharing ks WHERE ks.sharing_id = s.id) as jumlah_komentar
    FROM sharing s JOIN guru_wali gw ON s.guru_wali_id = gw.id JOIN users u ON gw.user_id = u.id
    WHERE s.is_published = 1 ORDER BY s.created_at DESC")->fetchAll();

ob_start();
?>
<?php if ($detailId && !empty($sharingData)): ?>
<div class="sharing-card">
    <div class="sharing-card-header">
        <div class="avatar"><?= e(getInitials($sharingData['nama_lengkap'])) ?></div>
        <div>
            <strong><?= e($sharingData['nama_lengkap']) ?></strong>
            <div class="list-item-meta"><span><?= timeAgo($sharingData['created_at']) ?></span></div>
        </div>
        <span class="badge badge-primary" style="margin-left:auto"><?= e(kategoriLabel($sharingData['kategori'])) ?></span>
    </div>
    <div class="sharing-card-title"><?= e($sharingData['judul']) ?></div>
    <div class="sharing-card-content"><?= nl2br(e($sharingData['konten'])) ?></div>
</div>

<div class="card">
    <div class="card-header"><h2>Komentar (<?= count($komentarList) ?>)</h2><a href="sharing.php" class="btn btn-sm btn-secondary">Kembali</a></div>
    <div class="card-body">
        <?php foreach ($komentarList as $k): ?>
        <div class="list-item">
            <div class="avatar"><?= e(getInitials($k['nama_lengkap'])) ?></div>
            <div class="list-item-content">
                <strong><?= e($k['nama_lengkap']) ?></strong>
                <span class="badge badge-secondary" style="margin-left:6px"><?= e(roleLabel($k['role'])) ?></span>
                <p style="margin-top:4px"><?= nl2br(e($k['isi'])) ?></p>
                <div class="list-item-meta"><span><?= timeAgo($k['created_at']) ?></span></div>
            </div>
        </div>
        <?php endforeach; ?>

        <form method="POST" style="margin-top:16px">
            <?= csrfField() ?>
            <input type="hidden" name="sharing_id" value="<?= $sharingData['id'] ?>">
            <div class="form-group">
                <label>Tulis Komentar</label>
                <textarea name="isi" required rows="3" placeholder="Bagikan pendapat atau pengalaman Anda..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Komentar</button>
        </form>
    </div>
</div>
<?php else: ?>
<?php foreach ($list as $s): ?>
<div class="sharing-card">
    <div class="sharing-card-header">
        <div class="avatar"><?= e(getInitials($s['nama_lengkap'])) ?></div>
        <div>
            <strong><?= e($s['nama_lengkap']) ?></strong>
            <div class="list-item-meta"><span><?= timeAgo($s['created_at']) ?></span></div>
        </div>
        <span class="badge badge-primary" style="margin-left:auto"><?= e(kategoriLabel($s['kategori'])) ?></span>
    </div>
    <div class="sharing-card-title"><?= e($s['judul']) ?></div>
    <div class="sharing-card-content"><?= nl2br(e(mb_substr($s['konten'], 0, 250))) ?><?= mb_strlen($s['konten']) > 250 ? '...' : '' ?></div>
    <div class="sharing-card-footer">
        <span><?= $s['jumlah_komentar'] ?> komentar</span>
        <a href="sharing.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Baca & Komentari</a>
    </div>
</div>
<?php endforeach; ?>
<?php if (empty($list)): ?><div class="empty-state"><p>Belum ada konten sharing</p></div><?php endif; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
renderLayout('Sharing', $content, ['page' => $currentPage, 'sidebar' => pelajarSidebar($currentPage)]);
