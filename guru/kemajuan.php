<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('guru_wali');

$db = Database::getConnection();
$guruId = Auth::getGuruWaliId();
$currentPage = 'kemajuan';

$siswaList = $db->prepare("SELECT p.id, u.nama_lengkap FROM penugasan pn JOIN pelajar p ON pn.pelajar_id = p.id JOIN users u ON p.user_id = u.id WHERE pn.guru_wali_id = ? AND pn.is_active = 1");
$siswaList->execute([$guruId]);
$siswaOptions = $siswaList->fetchAll();

$filterPelajar = (int)getInput('pelajar_id', $siswaOptions[0]['id'] ?? 0);

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $db->prepare('INSERT INTO kemajuan (pelajar_id, guru_wali_id, aspek, nilai, catatan, periode) VALUES (?, ?, ?, ?, ?, ?)')
       ->execute([
           (int)getInput('pelajar_id'), $guruId, getInput('aspek'),
           min(100, max(0, (int)getInput('nilai'))), trim(getInput('catatan', '')),
           getInput('periode', date('Y-m'))
       ]);
    redirect(APP_URL . '/guru/kemajuan.php?pelajar_id='.(int)getInput('pelajar_id').'&msg=created');
}

$kemajuan = [];
if ($filterPelajar) {
    $k = $db->prepare('SELECT * FROM kemajuan WHERE pelajar_id = ? AND guru_wali_id = ? ORDER BY created_at DESC');
    $k->execute([$filterPelajar, $guruId]);
    $kemajuan = $k->fetchAll();
}

$latestByAspek = [];
foreach ($kemajuan as $km) {
    if (!isset($latestByAspek[$km['aspek']])) {
        $latestByAspek[$km['aspek']] = $km;
    }
}

ob_start();
?>
<div class="page-actions">
    <div class="filter-group">
        <select onchange="location.href='kemajuan.php?pelajar_id='+this.value">
            <?php foreach ($siswaOptions as $so): ?>
            <option value="<?= $so['id'] ?>" <?= $filterPelajar === (int)$so['id'] ? 'selected' : '' ?>><?= e($so['nama_lengkap']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-primary" data-modal="modalKemajuan">Catat Kemajuan</button>
</div>

<?php if ($filterPelajar): ?>
<div class="card">
    <div class="card-header"><h2>Ringkasan Kemajuan</h2></div>
    <div class="card-body">
        <div class="progress-grid">
            <?php foreach (['akademik','sosial','emosional','spiritual','karakter'] as $aspek): ?>
            <div class="progress-item">
                <label><?= e(kategoriLabel($aspek)) ?></label>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $latestByAspek[$aspek]['nilai'] ?? 0 ?>%"></div></div>
                <div class="progress-value"><?= $latestByAspek[$aspek]['nilai'] ?? 0 ?>%</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Riwayat Pencatatan</h2></div>
    <div class="card-body table-wrapper">
        <table>
            <thead><tr><th>Aspek</th><th>Nilai</th><th>Periode</th><th>Catatan</th><th>Tanggal</th></tr></thead>
            <tbody>
                <?php foreach ($kemajuan as $km): ?>
                <tr>
                    <td><?= e(kategoriLabel($km['aspek'])) ?></td>
                    <td><strong><?= $km['nilai'] ?>%</strong></td>
                    <td><?= e($km['periode'] ?: '-') ?></td>
                    <td><?= e($km['catatan'] ?: '-') ?></td>
                    <td><?= formatTanggal($km['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($kemajuan)): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--color-text-muted)">Belum ada data</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="modal-overlay" id="modalKemajuan">
    <div class="modal">
        <div class="modal-header"><h2>Catat Kemajuan Siswa</h2><button class="modal-close">&times;</button></div>
        <form method="POST">
            <?= csrfField() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Siswa</label>
                    <select name="pelajar_id" required>
                        <?php foreach ($siswaOptions as $so): ?>
                        <option value="<?= $so['id'] ?>" <?= $filterPelajar === (int)$so['id'] ? 'selected' : '' ?>><?= e($so['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Aspek</label>
                        <select name="aspek">
                            <?php foreach (['akademik','sosial','emosional','spiritual','karakter'] as $a): ?>
                            <option value="<?= $a ?>"><?= kategoriLabel($a) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nilai (0-100)</label>
                        <input type="number" name="nilai" min="0" max="100" required>
                    </div>
                </div>
                <div class="form-group"><label>Periode</label><input type="month" name="periode" value="<?= date('Y-m') ?>"></div>
                <div class="form-group"><label>Catatan</label><textarea name="catatan" rows="3"></textarea></div>
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
renderLayout('Kemajuan Siswa', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
