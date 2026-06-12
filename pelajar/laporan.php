<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/profil.php';
Auth::requireRole('pelajar');

$db = Database::getConnection();
$pelajarId = Auth::getPelajarId();
$currentPage = 'laporan';

$guruAssign = $db->prepare('SELECT guru_wali_id FROM penugasan WHERE pelajar_id = ? AND is_active = 1 LIMIT 1');
$guruAssign->execute([$pelajarId]);
$guruRow = $guruAssign->fetch();
$guruWaliId = $guruRow ? $guruRow['guru_wali_id'] : null;

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    if (getInput('post_action') === 'create') {
        $db->prepare('INSERT INTO laporan_masalah (pelajar_id, guru_wali_id, judul, kategori, deskripsi, tingkat_urgensi, is_anonim) VALUES (?, ?, ?, ?, ?, ?, ?)')
           ->execute([
               $pelajarId, $guruWaliId, trim(getInput('judul')), getInput('kategori'),
               trim(getInput('deskripsi')), getInput('tingkat_urgensi', 'sedang'),
               getInput('is_anonim') ? 1 : 0
           ]);
        if ($guruWaliId) {
            $guruUser = $db->prepare('SELECT u.id FROM guru_wali gw JOIN users u ON gw.user_id = u.id WHERE gw.id = ?');
            $guruUser->execute([$guruWaliId]);
            $gu = $guruUser->fetch();
            if ($gu) {
                $db->prepare('INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, ?, ?, ?)')
                   ->execute([$gu['id'], 'Laporan Baru', 'Siswa mengirim laporan masalah baru', 'laporan']);
            }
        }
        redirect(APP_URL . '/pelajar/laporan.php?msg=created');
    }
}

$detailId = (int)getInput('id', 0);
if ($detailId) {
    $detail = $db->prepare('SELECT * FROM laporan_masalah WHERE id = ? AND pelajar_id = ?');
    $detail->execute([$detailId, $pelajarId]);
    $detailData = $detail->fetch();
    if ($detailData) {
        $tanggapan = $db->prepare('SELECT tl.*, u.nama_lengkap FROM tanggapan_laporan tl JOIN guru_wali gw ON tl.guru_wali_id = gw.id JOIN users u ON gw.user_id = u.id WHERE tl.laporan_id = ? ORDER BY tl.created_at ASC');
        $tanggapan->execute([$detailId]);
        $tanggapanList = $tanggapan->fetchAll();
    }
}

$laporan = $db->prepare('SELECT * FROM laporan_masalah WHERE pelajar_id = ? ORDER BY created_at DESC');
$laporan->execute([$pelajarId]);
$laporanList = $laporan->fetchAll();

ob_start();
?>
<?php if (getInput('msg') === 'created'): ?><div class="alert alert-success">Laporan berhasil dikirim.</div><?php endif; ?>

<?php if ($detailId && !empty($detailData)): ?>
<div class="card">
    <div class="card-header"><h2><?= e($detailData['judul']) ?></h2><a href="laporan.php" class="btn btn-sm btn-secondary">Kembali</a></div>
    <div class="card-body">
        <div class="list-item-meta" style="margin-bottom:16px">
            <span class="badge badge-primary"><?= e(kategoriLabel($detailData['kategori'])) ?></span>
            <span class="badge <?= statusBadge($detailData['status']) ?>"><?= ucfirst($detailData['status']) ?></span>
            <span><?= formatTanggal($detailData['created_at']) ?></span>
        </div>
        <p style="margin-bottom:20px;line-height:1.8"><?= nl2br(e($detailData['deskripsi'])) ?></p>
        <h3 style="font-size:0.95rem;margin-bottom:12px">Tanggapan Guru Wali</h3>
        <?php if (empty($tanggapanList)): ?>
        <p style="color:var(--color-text-muted)">Belum ada tanggapan. Guru wali Anda akan segera merespons.</p>
        <?php else: foreach ($tanggapanList as $t): ?>
        <div class="sharing-card">
            <p><strong><?= e($t['nama_lengkap']) ?></strong></p>
            <p style="margin-top:8px"><?= nl2br(e($t['isi_tanggapan'])) ?></p>
            <?php if ($t['saran']): ?><p style="margin-top:8px;color:var(--color-text-muted)"><strong>Saran:</strong> <?= nl2br(e($t['saran'])) ?></p><?php endif; ?>
            <div class="list-item-meta"><span><?= timeAgo($t['created_at']) ?></span></div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<?php else: ?>

<div class="page-actions">
    <p style="color:var(--color-text-muted)">Ceritakan permasalahan Anda. Guru wali siap mendengarkan dan membantu.</p>
    <button class="btn btn-primary" data-modal="modalLaporan">Buat Laporan</button>
</div>

<div class="card">
    <div class="card-body">
        <?php foreach ($laporanList as $l): ?>
        <div class="list-item">
            <div class="list-item-content">
                <div class="list-item-title"><?= e($l['judul']) ?></div>
                <div class="list-item-meta">
                    <span class="badge badge-primary"><?= e(kategoriLabel($l['kategori'])) ?></span>
                    <span class="badge <?= statusBadge($l['status']) ?>"><?= ucfirst($l['status']) ?></span>
                    <span><?= timeAgo($l['created_at']) ?></span>
                </div>
            </div>
            <a href="laporan.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-secondary">Detail</a>
        </div>
        <?php endforeach; ?>
        <?php if (empty($laporanList)): ?><div class="empty-state"><p>Belum ada laporan. Jangan ragu untuk berbagi.</p></div><?php endif; ?>
    </div>
</div>

<div class="modal-overlay" id="modalLaporan">
    <div class="modal">
        <div class="modal-header"><h2>Buat Laporan Masalah</h2><button class="modal-close">&times;</button></div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="create">
            <div class="modal-body">
                <div class="form-group"><label>Judul</label><input type="text" name="judul" required placeholder="Ringkas permasalahan Anda"></div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori">
                            <?php foreach (['psikologis','pendidikan','sosial','materi','keluarga','lainnya'] as $k): ?>
                            <option value="<?= $k ?>"><?= kategoriLabel($k) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tingkat Urgensi</label>
                        <select name="tingkat_urgensi">
                            <option value="rendah">Rendah</option>
                            <option value="sedang" selected>Sedang</option>
                            <option value="tinggi">Tinggi</option>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" required rows="5" placeholder="Ceritakan permasalahan Anda dengan detail..."></textarea></div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_anonim" value="1"> Kirim sebagai anonim</label>
                    <p class="form-hint">Identitas Anda disembunyikan dari guru wali jika dicentang.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Kirim Laporan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
renderLayout('Laporan Masalah', $content, ['page' => $currentPage, 'sidebar' => pelajarSidebar($currentPage)]);
