<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('admin');

$db = Database::getConnection();
$currentPage = 'penugasan';

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $postAction = getInput('post_action');

    if ($postAction === 'assign') {
        $guruId = (int)getInput('guru_wali_id');
        $pelajarId = (int)getInput('pelajar_id');
        $catatan = trim(getInput('catatan', ''));

        $check = $db->prepare('SELECT id FROM penugasan WHERE pelajar_id = ? AND is_active = 1');
        $check->execute([$pelajarId]);
        if ($check->fetch()) {
            $db->prepare('UPDATE penugasan SET is_active = 0, tanggal_selesai = CURDATE() WHERE pelajar_id = ? AND is_active = 1')
               ->execute([$pelajarId]);
        }

        $db->prepare('INSERT INTO penugasan (guru_wali_id, pelajar_id, tanggal_mulai, catatan) VALUES (?, ?, CURDATE(), ?)')
           ->execute([$guruId, $pelajarId, $catatan ?: null]);

        $pelajarUser = $db->prepare('SELECT u.id, u.nama_lengkap FROM pelajar p JOIN users u ON p.user_id = u.id WHERE p.id = ?');
        $pelajarUser->execute([$pelajarId]);
        $pUser = $pelajarUser->fetch();

        $guruUser = $db->prepare('SELECT u.id, u.nama_lengkap FROM guru_wali gw JOIN users u ON gw.user_id = u.id WHERE gw.id = ?');
        $guruUser->execute([$guruId]);
        $gUser = $guruUser->fetch();

        if ($pUser) {
            $db->prepare('INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, ?, ?, ?)')
               ->execute([$pUser['id'], 'Penugasan Guru Wali', "Anda dibimbing oleh {$gUser['nama_lengkap']}", 'info']);
        }
        if ($gUser) {
            $db->prepare('INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, ?, ?, ?)')
               ->execute([$gUser['id'], 'Siswa Baru', "{$pUser['nama_lengkap']} ditugaskan kepada Anda", 'info']);
        }

        $db->prepare('INSERT INTO log_aktivitas (admin_id, aksi, detail) VALUES (?, ?, ?)')
           ->execute([Auth::id(), 'Penugasan baru', "Guru: {$gUser['nama_lengkap']} - Siswa: {$pUser['nama_lengkap']}"]);

        redirect(APP_URL . '/admin/penugasan.php?msg=assigned');
    }

    if ($postAction === 'unassign') {
        $id = (int)getInput('penugasan_id');
        $db->prepare('UPDATE penugasan SET is_active = 0, tanggal_selesai = CURDATE() WHERE id = ?')->execute([$id]);
        redirect(APP_URL . '/admin/penugasan.php?msg=unassigned');
    }
}

$guruList = $db->query("SELECT gw.id, u.nama_lengkap, gw.max_siswa,
    (SELECT COUNT(*) FROM penugasan pn WHERE pn.guru_wali_id = gw.id AND pn.is_active = 1) as jumlah_siswa
    FROM guru_wali gw JOIN users u ON gw.user_id = u.id WHERE u.is_active = 1")->fetchAll();

$pelajarList = $db->query("SELECT p.id, u.nama_lengkap, p.kelas, p.nis FROM pelajar p JOIN users u ON p.user_id = u.id WHERE u.is_active = 1")->fetchAll();

$penugasan = $db->query("SELECT pn.*, ug.nama_lengkap as nama_guru, up.nama_lengkap as nama_pelajar, p.kelas
    FROM penugasan pn
    JOIN guru_wali gw ON pn.guru_wali_id = gw.id
    JOIN users ug ON gw.user_id = ug.id
    JOIN pelajar p ON pn.pelajar_id = p.id
    JOIN users up ON p.user_id = up.id
    WHERE pn.is_active = 1
    ORDER BY ug.nama_lengkap, up.nama_lengkap")->fetchAll();

$msg = getInput('msg');
ob_start();
?>
<?php if ($msg === 'assigned'): ?><div class="alert alert-success">Penugasan berhasil dibuat.</div><?php endif; ?>
<?php if ($msg === 'unassigned'): ?><div class="alert alert-success">Penugasan berhasil diakhiri.</div><?php endif; ?>

<div class="page-actions">
    <p style="color:var(--color-text-muted)">Atur hubungan guru wali dengan pelajar. Setiap pelajar hanya memiliki satu guru wali aktif.</p>
    <button class="btn btn-primary" data-modal="modalAssign">Buat Penugasan</button>
</div>

<div class="stats-grid">
    <?php foreach ($guruList as $g): ?>
    <div class="stat-card">
        <div class="stat-icon primary"><div class="avatar"><?= e(getInitials($g['nama_lengkap'])) ?></div></div>
        <div class="stat-info">
            <h3><?= $g['jumlah_siswa'] ?>/<?= $g['max_siswa'] ?></h3>
            <p><?= e($g['nama_lengkap']) ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header"><h2>Penugasan Aktif</h2></div>
    <div class="card-body table-wrapper">
        <table>
            <thead>
                <tr><th>Guru Wali</th><th>Pelajar</th><th>Kelas</th><th>Mulai</th><th>Catatan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($penugasan as $pn): ?>
                <tr>
                    <td><?= e($pn['nama_guru']) ?></td>
                    <td><?= e($pn['nama_pelajar']) ?></td>
                    <td><?= e($pn['kelas'] ?: '-') ?></td>
                    <td><?= formatTanggal($pn['tanggal_mulai']) ?></td>
                    <td><?= e($pn['catatan'] ?: '-') ?></td>
                    <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Akhiri penugasan ini?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="post_action" value="unassign">
                            <input type="hidden" name="penugasan_id" value="<?= $pn['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Akhiri</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($penugasan)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--color-text-muted)">Belum ada penugasan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalAssign">
    <div class="modal">
        <div class="modal-header">
            <h2>Buat Penugasan Baru</h2>
            <button class="modal-close">&times;</button>
        </div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="assign">
            <div class="modal-body">
                <div class="form-group">
                    <label>Guru Wali</label>
                    <select name="guru_wali_id" required>
                        <?php foreach ($guruList as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= e($g['nama_lengkap']) ?> (<?= $g['jumlah_siswa'] ?>/<?= $g['max_siswa'] ?> siswa)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pelajar</label>
                    <select name="pelajar_id" required>
                        <?php foreach ($pelajarList as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['nama_lengkap']) ?> - <?= e($p['kelas'] ?: 'Tanpa kelas') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="catatan" placeholder="Catatan penugasan (opsional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Penugasan</button>
            </div>
        </form>
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
renderLayout('Penugasan Guru-Siswa', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
