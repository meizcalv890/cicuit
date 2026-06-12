<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/profil.php';
Auth::requireRole('pelajar');

$db = Database::getConnection();
$pelajarId = Auth::getPelajarId();
$currentPage = 'kemajuan';

$guruAssign = $db->prepare('SELECT guru_wali_id FROM penugasan WHERE pelajar_id = ? AND is_active = 1 LIMIT 1');
$guruAssign->execute([$pelajarId]);
$gRow = $guruAssign->fetch();

$kemajuan = [];
$latestByAspek = [];
if ($gRow) {
    $k = $db->prepare('SELECT * FROM kemajuan WHERE pelajar_id = ? AND guru_wali_id = ? ORDER BY created_at DESC');
    $k->execute([$pelajarId, $gRow['guru_wali_id']]);
    $kemajuan = $k->fetchAll();
    foreach ($kemajuan as $km) {
        if (!isset($latestByAspek[$km['aspek']])) $latestByAspek[$km['aspek']] = $km;
    }
}

ob_start();
?>
<div class="card">
    <div class="card-header"><h2>Kemajuan Perkembangan Saya</h2></div>
    <div class="card-body">
        <p style="color:var(--color-text-muted);margin-bottom:20px">Data kemajuan dicatat oleh guru wali Anda berdasarkan observasi bimbingan.</p>
        <div class="progress-grid">
            <?php foreach (['akademik','sosial','emosional','spiritual','karakter'] as $aspek): ?>
            <div class="progress-item">
                <label><?= e(kategoriLabel($aspek)) ?></label>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $latestByAspek[$aspek]['nilai'] ?? 0 ?>%"></div></div>
                <div class="progress-value"><?= $latestByAspek[$aspek]['nilai'] ?? 0 ?>%</div>
                <?php if (isset($latestByAspek[$aspek]['catatan']) && $latestByAspek[$aspek]['catatan']): ?>
                <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:6px"><?= e($latestByAspek[$aspek]['catatan']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (!empty($kemajuan)): ?>
<div class="card">
    <div class="card-header"><h2>Riwayat Kemajuan</h2></div>
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
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
renderLayout('Kemajuan Saya', $content, ['page' => $currentPage, 'sidebar' => pelajarSidebar($currentPage)]);
