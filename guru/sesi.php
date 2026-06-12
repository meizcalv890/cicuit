<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('guru_wali');

$db = Database::getConnection();
$guruId = Auth::getGuruWaliId();
$currentPage = 'sesi';

$siswaList = $db->prepare("SELECT p.id, u.nama_lengkap FROM penugasan pn JOIN pelajar p ON pn.pelajar_id = p.id JOIN users u ON p.user_id = u.id WHERE pn.guru_wali_id = ? AND pn.is_active = 1");
$siswaList->execute([$guruId]);
$siswaOptions = $siswaList->fetchAll();

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $action = getInput('post_action');
    if ($action === 'create') {
        $db->prepare('INSERT INTO sesi_bimbingan (guru_wali_id, pelajar_id, judul, topik, tanggal_sesi, durasi_menit, lokasi, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
           ->execute([
               $guruId, (int)getInput('pelajar_id'), trim(getInput('judul')), trim(getInput('topik', '')),
               getInput('tanggal_sesi'), (int)getInput('durasi_menit', 30), trim(getInput('lokasi', 'Online')), trim(getInput('catatan', ''))
           ]);
        $pelajarUser = $db->prepare('SELECT u.id FROM pelajar p JOIN users u ON p.user_id = u.id WHERE p.id = ?');
        $pelajarUser->execute([(int)getInput('pelajar_id')]);
        $pu = $pelajarUser->fetch();
        if ($pu) {
            $db->prepare('INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, ?, ?, ?)')
               ->execute([$pu['id'], 'Sesi Bimbingan Baru', 'Guru wali Anda menjadwalkan sesi bimbingan', 'sesi']);
        }
        redirect(APP_URL . '/guru/sesi.php?msg=created');
    }
    if ($action === 'update_status') {
        $id = (int)getInput('sesi_id');
        $status = getInput('status');
        $ringkasan = trim(getInput('ringkasan', ''));
        $db->prepare('UPDATE sesi_bimbingan SET status = ?, ringkasan = ? WHERE id = ? AND guru_wali_id = ?')
           ->execute([$status, $ringkasan ?: null, $id, $guruId]);
        redirect(APP_URL . '/guru/sesi.php?msg=updated');
    }
}

$sesi = $db->prepare("SELECT sb.*, u.nama_lengkap FROM sesi_bimbingan sb
    JOIN pelajar p ON sb.pelajar_id = p.id JOIN users u ON p.user_id = u.id
    WHERE sb.guru_wali_id = ? ORDER BY sb.tanggal_sesi DESC");
$sesi->execute([$guruId]);
$sesiList = $sesi->fetchAll();

ob_start();
?>
<?php if (getInput('msg') === 'created'): ?><div class="alert alert-success">Sesi berhasil dijadwalkan.</div><?php endif; ?>

<div class="page-actions">
    <p style="color:var(--color-text-muted)">Jadwalkan dan kelola sesi bimbingan dengan siswa</p>
    <button class="btn btn-primary" data-modal="modalSesi">Jadwalkan Sesi</button>
</div>

<div class="card">
    <div class="card-body table-wrapper">
        <table>
            <thead>
                <tr><th>Judul</th><th>Siswa</th><th>Tanggal</th><th>Durasi</th><th>Lokasi</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($sesiList as $s): ?>
                <tr>
                    <td><strong><?= e($s['judul']) ?></strong><?= $s['topik'] ? '<br><small>'.e($s['topik']).'</small>' : '' ?></td>
                    <td><?= e($s['nama_lengkap']) ?></td>
                    <td><?= formatWaktu($s['tanggal_sesi']) ?></td>
                    <td><?= $s['durasi_menit'] ?> menit</td>
                    <td><?= e($s['lokasi']) ?></td>
                    <td><span class="badge <?= statusBadge($s['status']) ?>"><?= ucfirst($s['status']) ?></span></td>
                    <td>
                        <?php if ($s['status'] !== 'selesai' && $s['status'] !== 'dibatalkan'): ?>
                        <form method="POST" style="display:inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="post_action" value="update_status">
                            <input type="hidden" name="sesi_id" value="<?= $s['id'] ?>">
                            <input type="hidden" name="status" value="selesai">
                            <button type="submit" class="btn btn-sm btn-success btn-secondary">Selesai</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($sesiList)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--color-text-muted)">Belum ada sesi</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalSesi">
    <div class="modal">
        <div class="modal-header"><h2>Jadwalkan Sesi Bimbingan</h2><button class="modal-close">&times;</button></div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="create">
            <div class="modal-body">
                <div class="form-group">
                    <label>Siswa</label>
                    <select name="pelajar_id" required>
                        <?php foreach ($siswaOptions as $so): ?>
                        <option value="<?= $so['id'] ?>"><?= e($so['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Judul Sesi</label><input type="text" name="judul" required></div>
                <div class="form-group"><label>Topik</label><input type="text" name="topik"></div>
                <div class="form-row">
                    <div class="form-group"><label>Tanggal & Waktu</label><input type="datetime-local" name="tanggal_sesi" required></div>
                    <div class="form-group"><label>Durasi (menit)</label><input type="number" name="durasi_menit" value="30" min="15" max="120"></div>
                </div>
                <div class="form-group"><label>Lokasi</label><input type="text" name="lokasi" value="Online"></div>
                <div class="form-group"><label>Catatan</label><textarea name="catatan"></textarea></div>
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
renderLayout('Sesi Bimbingan', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
