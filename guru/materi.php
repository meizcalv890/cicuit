<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('guru_wali');

$db = Database::getConnection();
$guruId = Auth::getGuruWaliId();
$currentPage = 'materi';

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $action = getInput('post_action');
    if ($action === 'create') {
        $db->prepare('INSERT INTO materi_bimbingan (guru_wali_id, judul, deskripsi, konten, kategori) VALUES (?, ?, ?, ?, ?)')
           ->execute([$guruId, trim(getInput('judul')), trim(getInput('deskripsi', '')), trim(getInput('konten')), getInput('kategori')]);
        redirect(APP_URL . '/guru/materi.php?msg=created');
    }
    if ($action === 'delete') {
        $db->prepare('DELETE FROM materi_bimbingan WHERE id = ? AND guru_wali_id = ?')->execute([(int)getInput('materi_id'), $guruId]);
        redirect(APP_URL . '/guru/materi.php?msg=deleted');
    }
}

$materi = $db->prepare('SELECT * FROM materi_bimbingan WHERE guru_wali_id = ? ORDER BY created_at DESC');
$materi->execute([$guruId]);
$list = $materi->fetchAll();

ob_start();
?>
<div class="page-actions">
    <p style="color:var(--color-text-muted)">Buat materi bimbingan untuk membantu siswa</p>
    <button class="btn btn-primary" data-modal="modalMateri">Tambah Materi</button>
</div>

<div class="card">
    <div class="card-body">
        <?php foreach ($list as $m): ?>
        <div class="list-item">
            <div class="list-item-content">
                <div class="list-item-title"><?= e($m['judul']) ?></div>
                <div class="list-item-meta">
                    <span class="badge badge-primary"><?= e(kategoriLabel($m['kategori'])) ?></span>
                    <span><?= timeAgo($m['created_at']) ?></span>
                </div>
                <?php if ($m['deskripsi']): ?><p style="margin-top:6px;color:var(--color-text-muted);font-size:0.9rem"><?= e($m['deskripsi']) ?></p><?php endif; ?>
            </div>
            <form method="POST" onsubmit="return confirm('Hapus materi?')">
                <?= csrfField() ?>
                <input type="hidden" name="post_action" value="delete">
                <input type="hidden" name="materi_id" value="<?= $m['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
        </div>
        <?php endforeach; ?>
        <?php if (empty($list)): ?><div class="empty-state"><p>Belum ada materi</p></div><?php endif; ?>
    </div>
</div>

<div class="modal-overlay" id="modalMateri">
    <div class="modal">
        <div class="modal-header"><h2>Tambah Materi Bimbingan</h2><button class="modal-close">&times;</button></div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="create">
            <div class="modal-body">
                <div class="form-group"><label>Judul</label><input type="text" name="judul" required></div>
                <div class="form-group"><label>Deskripsi Singkat</label><input type="text" name="deskripsi"></div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                        <?php foreach (['psikologis','pendidikan','sosial','karakter','umum'] as $k): ?>
                        <option value="<?= $k ?>"><?= kategoriLabel($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Konten Materi</label><textarea name="konten" required rows="8"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
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
renderLayout('Materi Bimbingan', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
