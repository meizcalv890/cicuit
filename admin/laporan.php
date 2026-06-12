<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('admin');

$db = Database::getConnection();
$currentPage = 'laporan';

$statusFilter = getInput('status', '');
$where = '1=1';
$params = [];
if ($statusFilter) {
    $where .= ' AND lm.status = ?';
    $params[] = $statusFilter;
}

$stmt = $db->prepare("SELECT lm.*, up.nama_lengkap as nama_pelajar, ug.nama_lengkap as nama_guru, p.kelas
    FROM laporan_masalah lm
    JOIN pelajar p ON lm.pelajar_id = p.id
    JOIN users up ON p.user_id = up.id
    LEFT JOIN guru_wali gw ON lm.guru_wali_id = gw.id
    LEFT JOIN users ug ON gw.user_id = ug.id
    WHERE $where ORDER BY lm.created_at DESC");
$stmt->execute($params);
$laporan = $stmt->fetchAll();

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $id = (int)getInput('laporan_id');
    $status = getInput('status');
    $db->prepare('UPDATE laporan_masalah SET status = ? WHERE id = ?')->execute([$status, $id]);
    $db->prepare('INSERT INTO log_aktivitas (admin_id, aksi, detail) VALUES (?, ?, ?)')
       ->execute([Auth::id(), 'Update status laporan', "ID: $id -> $status"]);
    redirect(APP_URL . '/admin/laporan.php?msg=updated');
}

ob_start();
?>
<div class="page-actions">
    <div class="filter-group">
        <select onchange="location.href='laporan.php?status='+this.value">
            <option value="">Semua Status</option>
            <?php foreach (['baru','diproses','ditanggapi','selesai','ditutup'] as $s): ?>
            <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Semua Laporan Masalah</h2></div>
    <div class="card-body table-wrapper">
        <table>
            <thead>
                <tr><th>Judul</th><th>Pelajar</th><th>Kategori</th><th>Urgensi</th><th>Guru Wali</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($laporan as $l): ?>
                <tr>
                    <td><strong><?= e($l['judul']) ?></strong><?= $l['is_anonim'] ? ' <span class="badge badge-secondary">Anonim</span>' : '' ?></td>
                    <td><?= e($l['nama_pelajar']) ?></td>
                    <td><?= e(kategoriLabel($l['kategori'])) ?></td>
                    <td><span class="badge <?= statusBadge($l['tingkat_urgensi']) ?>"><?= ucfirst($l['tingkat_urgensi']) ?></span></td>
                    <td><?= e($l['nama_guru'] ?: '-') ?></td>
                    <td><span class="badge <?= statusBadge($l['status']) ?>"><?= ucfirst($l['status']) ?></span></td>
                    <td><?= formatTanggal($l['created_at']) ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="laporan_id" value="<?= $l['id'] ?>">
                            <select name="status" onchange="this.form.submit()" style="padding:4px 8px;font-size:0.8rem">
                                <?php foreach (['baru','diproses','ditanggapi','selesai','ditutup'] as $s): ?>
                                <option value="<?= $s ?>" <?= $l['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($laporan)): ?>
                <tr><td colspan="8" style="text-align:center;color:var(--color-text-muted)">Belum ada laporan</td></tr>
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
renderLayout('Kelola Laporan', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
