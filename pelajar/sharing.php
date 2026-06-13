<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/profil.php';
Auth::requireRole('pelajar');

$db = Database::getConnection();
$currentPage = 'sharing';
$userId = Auth::id();
$pelajarId = Auth::getPelajarId();

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $action = getInput('post_action', 'komentar');

    if ($action === 'create') {
        $judul  = trim(getInput('judul'));
        $konten = trim(getInput('konten'));
        $kat    = getInput('kategori', 'informasi');
        if ($judul && $konten) {
            $db->prepare('INSERT INTO sharing (pelajar_id, judul, konten, kategori) VALUES (?, ?, ?, ?)')
               ->execute([$pelajarId, $judul, $konten, $kat]);
            redirect(APP_URL . '/pelajar/sharing.php?msg=created');
        }
    }

    if ($action === 'delete') {
        $sid = (int)getInput('sharing_id');
        $db->prepare('DELETE FROM sharing WHERE id = ? AND pelajar_id = ?')
           ->execute([$sid, $pelajarId]);
        redirect(APP_URL . '/pelajar/sharing.php?msg=deleted');
    }

    if ($action === 'edit') {
        $sid    = (int)getInput('sharing_id');
        $judul  = trim(getInput('judul'));
        $konten = trim(getInput('konten'));
        $kat    = getInput('kategori', 'informasi');
        if ($sid && $judul && $konten) {
            $db->prepare('UPDATE sharing SET judul=?, konten=?, kategori=? WHERE id=? AND pelajar_id=?')
               ->execute([$judul, $konten, $kat, $sid, $pelajarId]);
            redirect(APP_URL . '/pelajar/sharing.php?msg=edited');
        }
    }

    if ($action === 'komentar') {
        $sid = (int)getInput('sharing_id');
        $isi = trim(getInput('isi'));
        if ($sid && $isi) {
            $db->prepare('INSERT INTO komentar_sharing (sharing_id, user_id, isi) VALUES (?, ?, ?)')
               ->execute([$sid, $userId, $isi]);
        }
        redirect(APP_URL . '/pelajar/sharing.php?id=' . $sid . '&msg=commented');
    }
}

// ─── Query helper: gabungkan author guru & pelajar ───────────────────────────
$authorSelect = "
    COALESCE(gu.nama_lengkap, pu.nama_lengkap) AS nama_lengkap,
    CASE WHEN s.guru_wali_id IS NOT NULL THEN 'guru_wali' ELSE 'pelajar' END AS author_role,
    CASE WHEN s.pelajar_id = ? THEN 1 ELSE 0 END AS is_mine";

$authorJoin = "
    LEFT JOIN guru_wali gw ON s.guru_wali_id = gw.id
    LEFT JOIN users gu ON gw.user_id = gu.id
    LEFT JOIN pelajar p ON s.pelajar_id = p.id
    LEFT JOIN users pu ON p.user_id = pu.id";

// ─── Detail view ─────────────────────────────────────────────────────────────
$detailId = (int)getInput('id', 0);
$sharingData = null;
$komentarList = [];
if ($detailId) {
    $stmt = $db->prepare("SELECT s.*, $authorSelect
        FROM sharing s $authorJoin
        WHERE s.id = ? AND s.is_published = 1");
    $stmt->execute([$pelajarId, $detailId]);
    $sharingData = $stmt->fetch();
    if ($sharingData) {
        $k = $db->prepare('SELECT ks.*, u.nama_lengkap, u.role FROM komentar_sharing ks JOIN users u ON ks.user_id = u.id WHERE ks.sharing_id = ? ORDER BY ks.created_at ASC');
        $k->execute([$detailId]);
        $komentarList = $k->fetchAll();
    }
}

// ─── Edit data (untuk modal edit) ────────────────────────────────────────────
$editId = (int)getInput('edit_id', 0);
$editData = null;
if ($editId) {
    $e = $db->prepare('SELECT * FROM sharing WHERE id = ? AND pelajar_id = ?');
    $e->execute([$editId, $pelajarId]);
    $editData = $e->fetch();
}

// ─── Daftar semua sharing ─────────────────────────────────────────────────────
$stmt2 = $db->prepare("SELECT s.*,
    (SELECT COUNT(*) FROM komentar_sharing ks WHERE ks.sharing_id = s.id) AS jumlah_komentar,
    $authorSelect
    FROM sharing s $authorJoin
    WHERE s.is_published = 1 ORDER BY s.created_at DESC");
$stmt2->execute([$pelajarId]);
$list = $stmt2->fetchAll();

$msg = getInput('msg');
ob_start();
?>

<?php if ($msg === 'created'): ?><div class="alert alert-success">Sharing Anda berhasil dipublikasikan!</div><?php endif; ?>
<?php if ($msg === 'edited'): ?><div class="alert alert-success">Sharing berhasil diperbarui.</div><?php endif; ?>
<?php if ($msg === 'deleted'): ?><div class="alert alert-success">Sharing berhasil dihapus.</div><?php endif; ?>
<?php if ($msg === 'commented'): ?><div class="alert alert-success">Komentar berhasil dikirim.</div><?php endif; ?>

<?php if ($detailId && $sharingData): ?>
<?php // ─── HALAMAN DETAIL ──────────────────────────────────────────────────── ?>
<div class="sharing-card">
    <div class="sharing-card-header">
        <div class="avatar"><?= e(getInitials($sharingData['nama_lengkap'])) ?></div>
        <div>
            <strong><?= e($sharingData['nama_lengkap']) ?></strong>
            <span class="badge badge-secondary" style="margin-left:6px;font-size:0.75rem">
                <?= $sharingData['author_role'] === 'guru_wali' ? 'Guru Wali' : 'Pelajar' ?>
            </span>
            <div class="list-item-meta"><span><?= timeAgo($sharingData['created_at']) ?></span></div>
        </div>
        <span class="badge badge-primary" style="margin-left:auto"><?= e(kategoriLabel($sharingData['kategori'])) ?></span>
    </div>
    <div class="sharing-card-title"><?= e($sharingData['judul']) ?></div>
    <div class="sharing-card-content"><?= nl2br(e($sharingData['konten'])) ?></div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Komentar (<?= count($komentarList) ?>)</h2>
        <a href="sharing.php" class="btn btn-sm btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <?php if (empty($komentarList)): ?>
        <p style="color:var(--color-text-muted);margin-bottom:16px">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
        <?php endif; ?>
        <?php foreach ($komentarList as $k): ?>
        <div class="list-item" style="align-items:flex-start;gap:12px">
            <div class="avatar" style="flex-shrink:0"><?= e(getInitials($k['nama_lengkap'])) ?></div>
            <div class="list-item-content">
                <strong><?= e($k['nama_lengkap']) ?></strong>
                <span class="badge badge-secondary" style="margin-left:6px;font-size:0.72rem"><?= e(roleLabel($k['role'])) ?></span>
                <p style="margin-top:6px;line-height:1.6"><?= nl2br(e($k['isi'])) ?></p>
                <div class="list-item-meta"><span><?= timeAgo($k['created_at']) ?></span></div>
            </div>
        </div>
        <?php endforeach; ?>

        <form method="POST" style="margin-top:20px;border-top:1px solid var(--color-border);padding-top:16px">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="komentar">
            <input type="hidden" name="sharing_id" value="<?= $sharingData['id'] ?>">
            <div class="form-group">
                <label>Tulis Komentar</label>
                <textarea name="isi" required rows="3" placeholder="Bagikan pendapat atau pengalamanmu..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Komentar</button>
        </form>
    </div>
</div>

<?php else: ?>
<?php // ─── HALAMAN DAFTAR ──────────────────────────────────────────────────── ?>

<div class="page-actions">
    <p style="color:var(--color-text-muted)">Baca sharing dari guru wali dan sesama pelajar, atau bagikan pengalamanmu sendiri!</p>
    <button class="btn btn-primary" data-modal="modalSharingPelajar">+ Buat Sharing</button>
</div>

<?php foreach ($list as $s): ?>
<div class="sharing-card">
    <div class="sharing-card-header">
        <div class="avatar"><?= e(getInitials($s['nama_lengkap'])) ?></div>
        <div>
            <strong><?= e($s['nama_lengkap']) ?></strong>
            <span class="badge badge-<?= $s['author_role'] === 'guru_wali' ? 'primary' : 'secondary' ?>" style="margin-left:6px;font-size:0.72rem">
                <?= $s['author_role'] === 'guru_wali' ? 'Guru Wali' : 'Pelajar' ?>
            </span>
            <div class="list-item-meta"><span><?= timeAgo($s['created_at']) ?></span></div>
        </div>
        <span class="badge badge-primary" style="margin-left:auto"><?= e(kategoriLabel($s['kategori'])) ?></span>
    </div>
    <div class="sharing-card-title"><?= e($s['judul']) ?></div>
    <div class="sharing-card-content"><?= nl2br(e(mb_substr($s['konten'], 0, 250))) ?><?= mb_strlen($s['konten']) > 250 ? '...' : '' ?></div>
    <div class="sharing-card-footer">
        <span style="font-size:0.82rem;color:var(--color-text-muted)"><?= $s['jumlah_komentar'] ?> komentar</span>
        <div style="display:flex;gap:6px;align-items:center">
            <?php if ($s['is_mine']): ?>
            <a href="sharing.php?edit_id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus sharing ini?')">
                <?= csrfField() ?>
                <input type="hidden" name="post_action" value="delete">
                <input type="hidden" name="sharing_id" value="<?= $s['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
            <?php endif; ?>
            <a href="sharing.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Baca &amp; Komentari</a>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php if (empty($list)): ?>
<div class="empty-state"><p>Belum ada sharing. Jadilah yang pertama berbagi!</p></div>
<?php endif; ?>

<!-- Modal Buat Sharing -->
<div class="modal-overlay" id="modalSharingPelajar">
    <div class="modal" style="max-width:560px">
        <div class="modal-header"><h2>Buat Sharing</h2><button class="modal-close">&times;</button></div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="create">
            <div class="modal-body">
                <div class="form-group">
                    <label>Judul <span style="color:var(--color-danger)">*</span></label>
                    <input type="text" name="judul" required placeholder="Contoh: Pengalaman saya belajar matematika">
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
                    <label>Ceritakan <span style="color:var(--color-danger)">*</span></label>
                    <textarea name="konten" required rows="6" placeholder="Bagikan pengalamanmu, tips belajar, atau cerita inspiratif untuk teman-teman..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Publikasikan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Sharing (milik siswa sendiri) -->
<?php if ($editData): ?>
<div class="modal-overlay active" id="modalEditSharingPelajar">
    <div class="modal" style="max-width:560px">
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
                    <label>Judul</label>
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
                    <label>Konten</label>
                    <textarea name="konten" required rows="6"><?= e($editData['konten']) ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <a href="sharing.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>
<?php
$content = ob_get_clean();
renderLayout('Sharing', $content, ['page' => $currentPage, 'sidebar' => pelajarSidebar($currentPage)]);
