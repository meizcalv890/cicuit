<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('guru_wali');

$db = Database::getConnection();
$guruId = Auth::getGuruWaliId();
$currentPage = 'catatan';

$siswaList = $db->prepare("SELECT p.id, u.nama_lengkap FROM penugasan pn JOIN pelajar p ON pn.pelajar_id = p.id JOIN users u ON p.user_id = u.id WHERE pn.guru_wali_id = ? AND pn.is_active = 1");
$siswaList->execute([$guruId]);
$siswaOptions = $siswaList->fetchAll();

$filterPelajar = (int)getInput('pelajar_id', 0);

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $action = getInput('post_action');
    if ($action === 'create') {
        $db->prepare('INSERT INTO catatan_guru (guru_wali_id, pelajar_id, judul, isi, kategori) VALUES (?, ?, ?, ?, ?)')
           ->execute([$guruId, (int)getInput('pelajar_id'), trim(getInput('judul')), trim(getInput('isi')), getInput('kategori')]);
        redirect(APP_URL . '/guru/catatan.php?pelajar_id='.(int)getInput('pelajar_id').'&msg=created');
    }
    if ($action === 'delete') {
        $db->prepare('DELETE FROM catatan_guru WHERE id = ? AND guru_wali_id = ?')->execute([(int)getInput('catatan_id'), $guruId]);
        redirect(APP_URL . '/guru/catatan.php?msg=deleted');
    }
}

$where = 'cg.guru_wali_id = ?';
$params = [$guruId];
if ($filterPelajar) {
    $where .= ' AND cg.pelajar_id = ?';
    $params[] = $filterPelajar;
}

$catatan = $db->prepare("SELECT cg.*, u.nama_lengkap FROM catatan_guru cg JOIN pelajar p ON cg.pelajar_id = p.id JOIN users u ON p.user_id = u.id WHERE $where ORDER BY cg.created_at DESC");
$catatan->execute($params);
$list = $catatan->fetchAll();

ob_start();
?>
<div class="page-actions">
    <div class="filter-group">
        <select onchange="location.href='catatan.php?pelajar_id='+this.value">
            <option value="0">Semua Siswa</option>
            <?php foreach ($siswaOptions as $so): ?>
            <option value="<?= $so['id'] ?>" <?= $filterPelajar === (int)$so['id'] ? 'selected' : '' ?>><?= e($so['nama_lengkap']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-primary" data-modal="modalCatatan">Tambah Catatan</button>
</div>

<div class="card">
    <div class="card-header"><h2>Catatan Privat Siswa</h2></div>
    <div class="card-body">
        <?php foreach ($list as $c): ?>
        <div class="sharing-card">
            <div class="sharing-card-header">
                <strong><?= e($c['judul']) ?></strong>
                <span class="badge badge-primary"><?= e(kategoriLabel($c['kategori'])) ?></span>
            </div>
            <p style="color:var(--color-text-muted);margin:8px 0"><?= nl2br(e($c['isi'])) ?></p>
            <div class="sharing-card-footer">
                <span>Siswa: <?= e($c['nama_lengkap']) ?></span>
                <span><?= timeAgo($c['created_at']) ?></span>
                <form method="POST" style="display:inline" onsubmit="return confirm('Hapus catatan?')">
                    <?= csrfField() ?>
                    <input type="hidden" name="post_action" value="delete">
                    <input type="hidden" name="catatan_id" value="<?= $c['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($list)): ?><div class="empty-state"><p>Belum ada catatan</p></div><?php endif; ?>
    </div>
</div>

<div class="modal-overlay" id="modalCatatan">
    <div class="modal">
        <div class="modal-header"><h2>Tambah Catatan</h2><button class="modal-close">&times;</button></div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="create">
            <div class="modal-body">
                <div class="form-group">
                    <label>Siswa</label>
                    <select name="pelajar_id" required>
                        <?php foreach ($siswaOptions as $so): ?>
                        <option value="<?= $so['id'] ?>" <?= $filterPelajar === (int)$so['id'] ? 'selected' : '' ?>><?= e($so['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Judul</label><input type="text" name="judul" required></div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                        <?php foreach (['perkembangan','perhatian','prestasi','perilaku','lainnya'] as $k): ?>
                        <option value="<?= $k ?>"><?= ucfirst($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Isi Catatan</label><textarea name="isi" required rows="5"></textarea></div>
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
renderLayout('Catatan Siswa', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
