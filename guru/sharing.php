<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('guru_wali');

$db = Database::getConnection();
$guruId = Auth::getGuruWaliId();
$currentPage = 'sharing';
$message = '';

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $action = getInput('post_action');

    if ($action === 'create') {
        $judul = trim(getInput('judul'));
        $konten = trim(getInput('konten'));
        $kategori = getInput('kategori', 'informasi');
        if ($judul && $konten) {
            $db->prepare('INSERT INTO sharing (guru_wali_id, judul, konten, kategori) VALUES (?, ?, ?, ?)')
               ->execute([$guruId, $judul, $konten, $kategori]);
            $newId = $db->lastInsertId();

            $siswaUsers = $db->prepare("SELECT u.id FROM penugasan pn JOIN pelajar p ON pn.pelajar_id = p.id JOIN users u ON p.user_id = u.id WHERE pn.guru_wali_id = ? AND pn.is_active = 1");
            $siswaUsers->execute([$guruId]);
            foreach ($siswaUsers->fetchAll() as $su) {
                $db->prepare('INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, ?, ?)')
                   ->execute([$su['id'], 'Sharing Baru', 'Guru wali Anda membagikan konten baru: ' . $judul, 'sharing', APP_URL . '/pelajar/sharing.php?id=' . $newId]);
            }
            redirect(APP_URL . '/guru/sharing.php?msg=created');
        } else {
            $message = 'Judul dan konten wajib diisi.';
        }
    }

    if ($action === 'edit') {
        $id = (int)getInput('sharing_id');
        $judul = trim(getInput('judul'));
        $konten = trim(getInput('konten'));
        $kategori = getInput('kategori', 'informasi');
        if ($id && $judul && $konten) {
            $db->prepare('UPDATE sharing SET judul = ?, konten = ?, kategori = ? WHERE id = ? AND guru_wali_id = ?')
               ->execute([$judul, $konten, $kategori, $id, $guruId]);
            redirect(APP_URL . '/guru/sharing.php?msg=edited');
        }
    }

    if ($action === 'delete') {
        $db->prepare('DELETE FROM sharing WHERE id = ? AND guru_wali_id = ?')->execute([(int)getInput('sharing_id'), $guruId]);
        redirect(APP_URL . '/guru/sharing.php?msg=deleted');
    }
}

// Data sharing milik guru
$sharing = $db->prepare("SELECT s.*, (SELECT COUNT(*) FROM komentar_sharing ks WHERE ks.sharing_id = s.id) as jumlah_komentar FROM sharing s WHERE s.guru_wali_id = ? ORDER BY s.created_at DESC");
$sharing->execute([$guruId]);
$list = $sharing->fetchAll();

// Sharing yang akan diedit
$editId = (int)getInput('edit_id', 0);
$editData = null;
if ($editId) {
    $es = $db->prepare('SELECT * FROM sharing WHERE id = ? AND guru_wali_id = ?');
    $es->execute([$editId, $guruId]);
    $editData = $es->fetch();
}

$msg = getInput('msg');
ob_start();
?>

<?php if ($msg === 'created'): ?><div class="alert alert-success">Sharing berhasil dipublikasikan ke siswa bimbingan Anda.</div><?php endif; ?>
<?php if ($msg === 'edited'): ?><div class="alert alert-success">Sharing berhasil diperbarui.</div><?php endif; ?>
<?php if ($msg === 'deleted'): ?><div class="alert alert-success">Sharing berhasil dihapus.</div><?php endif; ?>
<?php if ($message): ?><div class="alert alert-error"><?= e($message) ?></div><?php endif; ?>

<div class="page-actions">
    <p style="color:var(--color-text-muted)">Bagikan pengalaman, motivasi, dan tips untuk siswa bimbingan Anda</p>
    <button class="btn btn-primary" data-modal="modalSharing">+ Buat Sharing Baru</button>
</div>

<?php foreach ($list as $s): ?>
<div class="sharing-card">
    <div class="sharing-card-title"><?= e($s['judul']) ?></div>
    <div style="margin-bottom:8px">
        <span class="badge badge-primary"><?= e(kategoriLabel($s['kategori'])) ?></span>
    </div>
    <div class="sharing-card-content"><?= nl2br(e(mb_substr($s['konten'], 0, 300))) ?><?= mb_strlen($s['konten']) > 300 ? '...' : '' ?></div>
    <div class="sharing-card-footer">
        <span style="font-size:0.82rem;color:var(--color-text-muted)"><?= $s['jumlah_komentar'] ?> komentar &bull; <?= timeAgo($s['created_at']) ?></span>
        <div style="display:flex;gap:6px">
            <a href="sharing.php?edit_id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus sharing ini? Semua komentar juga akan terhapus.')">
                <?= csrfField() ?>
                <input type="hidden" name="post_action" value="delete">
                <input type="hidden" name="sharing_id" value="<?= $s['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php if (empty($list)): ?><div class="empty-state"><p>Belum ada konten sharing. Mulai bagikan inspirasi untuk siswa Anda!</p></div><?php endif; ?>

<!-- Modal Buat Sharing Baru -->
<div class="modal-overlay" id="modalSharing">
    <div class="modal" style="max-width:580px">
        <div class="modal-header"><h2>Buat Sharing Baru</h2><button class="modal-close">&times;</button></div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="create">
            <div class="modal-body">
                <div class="form-group">
                    <label>Judul <span style="color:var(--color-danger)">*</span></label>
                    <input type="text" name="judul" required placeholder="Contoh: Tips Belajar Efektif Menghadapi UN">
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                        <?php foreach (['motivasi' => 'Motivasi', 'pengalaman' => 'Pengalaman', 'tips' => 'Tips', 'informasi' => 'Informasi', 'refleksi' => 'Refleksi'] as $v => $l): ?>
                        <option value="<?= $v ?>"><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Konten <span style="color:var(--color-danger)">*</span></label>
                    <textarea name="konten" required rows="7" placeholder="Tuliskan sharing Anda... Cerita, motivasi, atau tips yang ingin Anda bagikan kepada siswa."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Publikasikan ke Siswa</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Sharing -->
<?php if ($editData): ?>
<div class="modal-overlay active" id="modalEditSharing">
    <div class="modal" style="max-width:580px">
        <div class="modal-header">
            <h2>Edit Sharing</h2>
            <a href="sharing.php" class="modal-close">&times;</a>
        </div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="edit">
            <input type="hidden" name="sharing_id" value="<?= $editData['id'] ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label>Judul <span style="color:var(--color-danger)">*</span></label>
                    <input type="text" name="judul" required value="<?= e($editData['judul']) ?>">
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                        <?php foreach (['motivasi' => 'Motivasi', 'pengalaman' => 'Pengalaman', 'tips' => 'Tips', 'informasi' => 'Informasi', 'refleksi' => 'Refleksi'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $editData['kategori'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Konten <span style="color:var(--color-danger)">*</span></label>
                    <textarea name="konten" required rows="7"><?= e($editData['konten']) ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <a href="sharing.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
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
renderLayout('Sharing', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
