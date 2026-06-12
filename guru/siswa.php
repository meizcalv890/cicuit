<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('guru_wali');

$db = Database::getConnection();
$guruId = Auth::getGuruWaliId();
$currentPage = 'siswa';

$siswa = $db->prepare("SELECT p.*, u.nama_lengkap, u.email, u.no_telepon, pn.tanggal_mulai,
    (SELECT COUNT(*) FROM laporan_masalah lm WHERE lm.pelajar_id = p.id AND lm.status IN ('baru','diproses')) as laporan_aktif,
    (SELECT COUNT(*) FROM sesi_bimbingan sb WHERE sb.pelajar_id = p.id AND sb.status = 'dijadwalkan') as sesi_aktif
    FROM penugasan pn
    JOIN pelajar p ON pn.pelajar_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE pn.guru_wali_id = ? AND pn.is_active = 1
    ORDER BY u.nama_lengkap");
$siswa->execute([$guruId]);
$siswaList = $siswa->fetchAll();

$detailId = (int)getInput('id', 0);
$detailSiswa = null;
$kemajuanSiswa = [];
if ($detailId) {
    $d = $db->prepare("SELECT p.*, u.nama_lengkap, u.email FROM pelajar p JOIN users u ON p.user_id = u.id
        JOIN penugasan pn ON pn.pelajar_id = p.id WHERE p.id = ? AND pn.guru_wali_id = ? AND pn.is_active = 1");
    $d->execute([$detailId, $guruId]);
    $detailSiswa = $d->fetch();
    if ($detailSiswa) {
        $k = $db->prepare("SELECT * FROM kemajuan WHERE pelajar_id = ? AND guru_wali_id = ? ORDER BY created_at DESC LIMIT 5");
        $k->execute([$detailId, $guruId]);
        $kemajuanSiswa = $k->fetchAll();
    }
}

ob_start();
?>
<div class="card">
    <div class="card-header"><h2>Siswa yang Dibimbing (<?= count($siswaList) ?>)</h2></div>
    <div class="card-body table-wrapper">
        <table>
            <thead>
                <tr><th>Nama</th><th>NIS</th><th>Kelas</th><th>Dibimbing Sejak</th><th>Laporan Aktif</th><th>Sesi Aktif</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($siswaList as $s): ?>
                <tr>
                    <td><strong><?= e($s['nama_lengkap']) ?></strong></td>
                    <td><?= e($s['nis'] ?: '-') ?></td>
                    <td><?= e($s['kelas'] ?: '-') ?></td>
                    <td><?= formatTanggal($s['tanggal_mulai']) ?></td>
                    <td><?= $s['laporan_aktif'] ?></td>
                    <td><?= $s['sesi_aktif'] ?></td>
                    <td>
                        <a href="siswa.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Detail</a>
                        <a href="catatan.php?pelajar_id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Catatan</a>
                        <a href="kemajuan.php?pelajar_id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Kemajuan</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($siswaList)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--color-text-muted)">Belum ada siswa ditugaskan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($detailSiswa): ?>
<div class="card">
    <div class="card-header"><h2>Detail: <?= e($detailSiswa['nama_lengkap']) ?></h2></div>
    <div class="card-body">
        <div class="grid-2">
            <div>
                <p><strong>Email:</strong> <?= e($detailSiswa['email']) ?></p>
                <p><strong>NIS:</strong> <?= e($detailSiswa['nis'] ?: '-') ?></p>
                <p><strong>Kelas:</strong> <?= e($detailSiswa['kelas'] ?: '-') ?></p>
                <p><strong>Jurusan:</strong> <?= e($detailSiswa['jurusan'] ?: '-') ?></p>
            </div>
            <div>
                <h3 style="margin-bottom:12px;font-size:0.95rem">Kemajuan Terbaru</h3>
                <?php if (empty($kemajuanSiswa)): ?>
                <p style="color:var(--color-text-muted)">Belum ada data kemajuan</p>
                <?php else: ?>
                <div class="progress-grid">
                    <?php foreach ($kemajuanSiswa as $km): ?>
                    <div class="progress-item">
                        <label><?= e(kategoriLabel($km['aspek'])) ?></label>
                        <div class="progress-bar"><div class="progress-fill" style="width:<?= $km['nilai'] ?>%"></div></div>
                        <div class="progress-value"><?= $km['nilai'] ?>%</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
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
renderLayout('Siswa Saya', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
