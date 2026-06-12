<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('admin');

$db = Database::getConnection();
$currentPage = 'sharing';

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $action = getInput('post_action');
    if ($action === 'toggle') {
        $id = (int)getInput('sharing_id');
        $db->prepare('UPDATE sharing SET is_published = NOT is_published WHERE id = ?')->execute([$id]);
        redirect(APP_URL . '/admin/sharing.php?msg=updated');
    }
    if ($action === 'delete') {
        $id = (int)getInput('sharing_id');
        $db->prepare('DELETE FROM sharing WHERE id = ?')->execute([$id]);
        redirect(APP_URL . '/admin/sharing.php?msg=deleted');
    }
}

$sharing = $db->query("SELECT s.*, u.nama_lengkap as nama_guru,
    (SELECT COUNT(*) FROM komentar_sharing ks WHERE ks.sharing_id = s.id) as jumlah_komentar
    FROM sharing s JOIN guru_wali gw ON s.guru_wali_id = gw.id JOIN users u ON gw.user_id = u.id
    ORDER BY s.created_at DESC")->fetchAll();

ob_start();
?>
<div class="card">
    <div class="card-header"><h2>Semua Konten Sharing</h2></div>
    <div class="card-body">
        <?php foreach ($sharing as $s): ?>
        <div class="sharing-card">
            <div class="sharing-card-header">
                <div class="avatar"><?= e(getInitials($s['nama_guru'])) ?></div>
                <div>
                    <strong><?= e($s['nama_guru']) ?></strong>
                    <div class="list-item-meta"><span><?= timeAgo($s['created_at']) ?></span></div>
                </div>
                <span class="badge badge-primary" style="margin-left:auto"><?= e(kategoriLabel($s['kategori'])) ?></span>
            </div>
            <div class="sharing-card-title"><?= e($s['judul']) ?></div>
            <div class="sharing-card-content"><?= nl2br(e(mb_substr($s['konten'], 0, 300))) ?><?= mb_strlen($s['konten']) > 300 ? '...' : '' ?></div>
            <div class="sharing-card-footer">
                <span><?= $s['jumlah_komentar'] ?> komentar</span>
                <div style="display:flex;gap:8px">
                    <span class="badge <?= $s['is_published'] ? 'badge-success' : 'badge-secondary' ?>"><?= $s['is_published'] ? 'Published' : 'Draft' ?></span>
                    <form method="POST" style="display:inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="post_action" value="toggle">
                        <input type="hidden" name="sharing_id" value="<?= $s['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-secondary"><?= $s['is_published'] ? 'Unpublish' : 'Publish' ?></button>
                    </form>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Hapus sharing ini?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="post_action" value="delete">
                        <input type="hidden" name="sharing_id" value="<?= $s['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($sharing)): ?>
        <div class="empty-state"><p>Belum ada konten sharing</p></div>
        <?php endif; ?>
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
renderLayout('Kelola Sharing', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
