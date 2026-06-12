<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('admin');

$db = Database::getConnection();
$currentPage = 'pengaturan';

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $settings = getInput('settings', []);
    if (is_array($settings)) {
        foreach ($settings as $key => $value) {
            $db->prepare('UPDATE pengaturan SET nilai = ? WHERE kunci = ?')->execute([trim($value), $key]);
        }
    }
    $db->prepare('INSERT INTO log_aktivitas (admin_id, aksi, detail) VALUES (?, ?, ?)')
       ->execute([Auth::id(), 'Update pengaturan', 'Pengaturan sistem diperbarui']);
    redirect(APP_URL . '/admin/pengaturan.php?msg=updated');
}

$pengaturan = $db->query('SELECT * FROM pengaturan ORDER BY id')->fetchAll();

ob_start();
?>
<?php if (getInput('msg') === 'updated'): ?><div class="alert alert-success">Pengaturan berhasil disimpan.</div><?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Pengaturan Sistem</h2></div>
    <div class="card-body">
        <form method="POST">
            <?= csrfField() ?>
            <?php foreach ($pengaturan as $p): ?>
            <div class="form-group">
                <label><?= e($p['keterangan'] ?: $p['kunci']) ?></label>
                <input type="text" name="settings[<?= e($p['kunci']) ?>]" value="<?= e($p['nilai']) ?>">
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Informasi Sistem</h2></div>
    <div class="card-body">
        <table>
            <tr><td>Versi</td><td>1.0.0</td></tr>
            <tr><td>PHP</td><td><?= phpversion() ?></td></tr>
            <tr><td>Database</td><td><?= DB_NAME ?></td></tr>
            <tr><td>Catatan Privasi</td><td>Admin tidak dapat mengubah kata sandi pengguna. Hanya pengguna sendiri yang dapat mengganti sandi melalui profil.</td></tr>
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
renderLayout('Pengaturan', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
