<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/profil.php';
Auth::requireRole('pelajar');

$db = Database::getConnection();
$pelajarId = Auth::getPelajarId();
$currentPage = 'sesi';

$sesi = $db->prepare("SELECT sb.*, u.nama_lengkap as nama_guru FROM sesi_bimbingan sb
    JOIN guru_wali gw ON sb.guru_wali_id = gw.id JOIN users u ON gw.user_id = u.id
    WHERE sb.pelajar_id = ? ORDER BY sb.tanggal_sesi DESC");
$sesi->execute([$pelajarId]);
$list = $sesi->fetchAll();

ob_start();
?>
<div class="card">
    <div class="card-header"><h2>Sesi Bimbingan Saya</h2></div>
    <div class="card-body table-wrapper">
        <table>
            <thead><tr><th>Judul</th><th>Guru Wali</th><th>Tanggal</th><th>Durasi</th><th>Lokasi</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($list as $s): ?>
                <tr>
                    <td><strong><?= e($s['judul']) ?></strong><?= $s['topik'] ? '<br><small>'.e($s['topik']).'</small>' : '' ?></td>
                    <td><?= e($s['nama_guru']) ?></td>
                    <td><?= formatWaktu($s['tanggal_sesi']) ?></td>
                    <td><?= $s['durasi_menit'] ?> menit</td>
                    <td><?= e($s['lokasi']) ?></td>
                    <td><span class="badge <?= statusBadge($s['status']) ?>"><?= ucfirst($s['status']) ?></span></td>
                </tr>
                <?php if ($s['ringkasan']): ?>
                <tr><td colspan="6" style="background:var(--color-primary-soft);font-size:0.9rem"><strong>Ringkasan:</strong> <?= e($s['ringkasan']) ?></td></tr>
                <?php endif; ?>
                <?php endforeach; ?>
                <?php if (empty($list)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--color-text-muted)">Belum ada sesi bimbingan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Sesi Bimbingan', $content, ['page' => $currentPage, 'sidebar' => pelajarSidebar($currentPage)]);
