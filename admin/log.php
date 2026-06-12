<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('admin');

$db = Database::getConnection();
$currentPage = 'log';

$logs = $db->query("SELECT la.*, u.nama_lengkap FROM log_aktivitas la JOIN users u ON la.admin_id = u.id ORDER BY la.created_at DESC LIMIT 100")->fetchAll();

ob_start();
?>
<div class="card">
    <div class="card-header"><h2>Riwayat Aktivitas Admin</h2></div>
    <div class="card-body table-wrapper">
        <table>
            <thead>
                <tr><th>Waktu</th><th>Admin</th><th>Aksi</th><th>Detail</th><th>IP</th></tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= formatWaktu($log['created_at']) ?></td>
                    <td><?= e($log['nama_lengkap']) ?></td>
                    <td><strong><?= e($log['aksi']) ?></strong></td>
                    <td><?= e($log['detail'] ?: '-') ?></td>
                    <td><?= e($log['ip_address'] ?: '-') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--color-text-muted)">Belum ada log</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
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
renderLayout('Log Aktivitas', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
