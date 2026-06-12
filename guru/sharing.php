<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('guru_wali');

$db = Database::getConnection();
$guruId = Auth::getGuruWaliId();
$currentPage = 'sharing';

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $action = getInput('post_action');
    if ($action === 'create') {
        $db->prepare('INSERT INTO sharing (guru_wali_id, judul, konten, kategori) VALUES (?, ?, ?, ?)')
           ->execute([$guruId, trim(getInput('judul')), trim(getInput('konten')), getInput('kategori')]);

        $siswaUsers = $db->prepare("SELECT u.id FROM penugasan pn JOIN pelajar p ON pn.pelajar_id = p.id JOIN users u ON p.user_id = u.id WHERE pn.guru_wali_id = ? AND pn.is_active = 1");
        $siswaUsers->execute([$guruId]);
        foreach ($siswaUsers->fetchAll() as $su) {
            $db->prepare('INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, ?, ?)')
               ->execute([$su['id'], 'Sharing Baru', 'Guru wali Anda membagikan konten baru', 'sharing', APP_URL.'/pelajar/sharing.php']);
        }
        redirect(APP_URL . '/guru/sharing.php?msg=created');
    }
    if ($action === 'delete') {
        $db->prepare('DELETE FROM sharing WHERE id = ? AND guru_wali_id = ?')->execute([(int)getInput('sharing_id'), $guruId]);
        redirect(APP_URL . '/guru/sharing.php?msg=deleted');
    }
}

$sharing = $db->prepare("SELECT s.*, (SELECT COUNT(*) FROM komentar_sharing ks WHERE ks.sharing_id = s.id) as jumlah_komentar FROM sharing s WHERE s.guru_wali_id = ? ORDER BY s.created_at DESC");
$sharing->execute([$guruId]);
$list = $sharing->fetchAll();

ob_start();
?>
<?php if (getInput('msg') === 'created'): ?><div class="alert alert-success">Sharing berhasil dipublikasikan.</div><?php endif; ?>

<div class="page-actions">
    <p style="color:var(--color-text-muted)">Bagikan pengalaman, motivasi, dan tips untuk siswa bimbingan Anda</p>
    <button class="btn btn-primary" data-modal="modalSharing">Buat Sharing</button>
</div>

<?php foreach ($list as $s): ?>
<div class="sharing-card">
    <div class="sharing-card-title"><?= e($s['judul']) ?></div>
    <div class="sharing-card-content"><?= nl2br(e($s['konten'])) ?></div>
    <div class="sharing-card-footer">
        <span class="badge badge-primary"><?= e(kategoriLabel($s['kategori'])) ?></span>
        <span><?= $s['jumlah_komentar'] ?> komentar</span>
        <span><?= timeAgo($s['created_at']) ?></span>
        <form method="POST" style="display:inline" onsubmit="return confirm('Hapus sharing ini?')">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="delete">
            <input type="hidden" name="sharing_id" value="<?= $s['id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
<?php if (empty($list)): ?><div class="empty-state"><p>Belum ada konten sharing</p></div><?php endif; ?>

<div class="modal-overlay" id="modalSharing">
    <div class="modal">
        <div class="modal-header"><h2>Buat Sharing Baru</h2><button class="modal-close">&times;</button></div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="create">
            <div class="modal-body">
                <div class="form-group"><label>Judul</label><input type="text" name="judul" required></div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                        <?php foreach (['motivasi','pengalaman','tips','informasi','refleksi'] as $k): ?>
                        <option value="<?= $k ?>"><?= kategoriLabel($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Konten</label><textarea name="konten" required rows="6" placeholder="Tuliskan sharing Anda..."></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Publikasikan</button>
            </div>
        </form>
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
renderLayout('Sharing', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
