<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/profil.php';
Auth::requireRole('pelajar');

$db = Database::getConnection();
$pelajarId = Auth::getPelajarId();
$currentPage = 'jurnal';

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $action = getInput('post_action');
    if ($action === 'create') {
        $db->prepare('INSERT INTO jurnal_siswa (pelajar_id, judul, isi, mood, is_private) VALUES (?, ?, ?, ?, ?)')
           ->execute([$pelajarId, trim(getInput('judul')), trim(getInput('isi')), getInput('mood', 'netral'), getInput('is_private') ? 1 : 0]);
        redirect(APP_URL . '/pelajar/jurnal.php?msg=created');
    }
    if ($action === 'delete') {
        $db->prepare('DELETE FROM jurnal_siswa WHERE id = ? AND pelajar_id = ?')->execute([(int)getInput('jurnal_id'), $pelajarId]);
        redirect(APP_URL . '/pelajar/jurnal.php?msg=deleted');
    }
}

$jurnal = $db->prepare('SELECT * FROM jurnal_siswa WHERE pelajar_id = ? ORDER BY created_at DESC');
$jurnal->execute([$pelajarId]);
$list = $jurnal->fetchAll();

ob_start();
?>
<div class="page-actions">
    <p style="color:var(--color-text-muted)">Tulis refleksi harian Anda. Jurnal membantu memahami diri sendiri.</p>
    <button class="btn btn-primary" data-modal="modalJurnal">Tulis Jurnal</button>
</div>

<?php foreach ($list as $j): ?>
<div class="sharing-card">
    <div class="sharing-card-header">
        <strong><?= e($j['judul']) ?></strong>
        <span class="mood-indicator mood-<?= e($j['mood']) ?>"><?= e(moodLabel($j['mood'])) ?></span>
        <?php if ($j['is_private']): ?><span class="badge badge-secondary">Privat</span><?php endif; ?>
    </div>
    <div class="sharing-card-content"><?= nl2br(e($j['isi'])) ?></div>
    <div class="sharing-card-footer">
        <span><?= timeAgo($j['created_at']) ?></span>
        <form method="POST" style="display:inline" onsubmit="return confirm('Hapus jurnal?')">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="delete">
            <input type="hidden" name="jurnal_id" value="<?= $j['id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
<?php if (empty($list)): ?><div class="empty-state"><p>Belum ada jurnal. Mulai menulis refleksi Anda.</p></div><?php endif; ?>

<div class="modal-overlay" id="modalJurnal">
    <div class="modal">
        <div class="modal-header"><h2>Tulis Jurnal Refleksi</h2><button class="modal-close">&times;</button></div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="create">
            <div class="modal-body">
                <div class="form-group"><label>Judul</label><input type="text" name="judul" required></div>
                <div class="form-group">
                    <label>Mood Hari Ini</label>
                    <select name="mood">
                        <?php foreach (['senang','netral','sedih','cemas','semangat'] as $m): ?>
                        <option value="<?= $m ?>"><?= moodLabel($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Isi Jurnal</label><textarea name="isi" required rows="6" placeholder="Apa yang Anda rasakan hari ini?"></textarea></div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_private" value="1" checked> Jurnal privat (hanya Anda yang bisa melihat)</label>
                </div>
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
renderLayout('Jurnal Refleksi', $content, ['page' => $currentPage, 'sidebar' => pelajarSidebar($currentPage)]);
