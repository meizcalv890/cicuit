<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('guru_wali');

$db = Database::getConnection();
$guruId = Auth::getGuruWaliId();
$currentPage = 'laporan';

if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
    $action = getInput('post_action');
    if ($action === 'tanggapi') {
        $laporanId = (int)getInput('laporan_id');
        $isi = trim(getInput('isi_tanggapan'));
        $saran = trim(getInput('saran', ''));

        $check = $db->prepare('SELECT lm.id, p.user_id FROM laporan_masalah lm JOIN pelajar p ON lm.pelajar_id = p.id WHERE lm.id = ? AND lm.guru_wali_id = ?');
        $check->execute([$laporanId, $guruId]);
        $lap = $check->fetch();

        if ($lap && $isi) {
            $db->prepare('INSERT INTO tanggapan_laporan (laporan_id, guru_wali_id, isi_tanggapan, saran) VALUES (?, ?, ?, ?)')
               ->execute([$laporanId, $guruId, $isi, $saran ?: null]);
            $db->prepare("UPDATE laporan_masalah SET status = 'ditanggapi' WHERE id = ?")->execute([$laporanId]);
            $db->prepare('INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, ?, ?)')
               ->execute([$lap['user_id'], 'Tanggapan Laporan', 'Guru wali Anda telah menanggapi laporan Anda', 'laporan', APP_URL.'/pelajar/laporan.php?id='.$laporanId]);
            redirect(APP_URL . '/guru/laporan.php?id='.$laporanId.'&msg=replied');
        }
    }
    if ($action === 'update_status') {
        $id = (int)getInput('laporan_id');
        $status = getInput('status');
        $db->prepare('UPDATE laporan_masalah SET status = ? WHERE id = ? AND guru_wali_id = ?')->execute([$status, $id, $guruId]);
        redirect(APP_URL . '/guru/laporan.php?msg=updated');
    }
}

$detailId = (int)getInput('id', 0);
if ($detailId) {
    $stmt = $db->prepare("SELECT lm.*, u.nama_lengkap FROM laporan_masalah lm
        JOIN pelajar p ON lm.pelajar_id = p.id JOIN users u ON p.user_id = u.id
        WHERE lm.id = ? AND lm.guru_wali_id = ?");
    $stmt->execute([$detailId, $guruId]);
    $detail = $stmt->fetch();

    $tanggapan = $db->prepare('SELECT * FROM tanggapan_laporan WHERE laporan_id = ? ORDER BY created_at ASC');
    $tanggapan->execute([$detailId]);
    $tanggapanList = $tanggapan->fetchAll();
}

$laporan = $db->prepare("SELECT lm.*, u.nama_lengkap FROM laporan_masalah lm
    JOIN pelajar p ON lm.pelajar_id = p.id JOIN users u ON p.user_id = u.id
    WHERE lm.guru_wali_id = ? ORDER BY FIELD(lm.status,'baru','diproses','ditanggapi','selesai','ditutup'), lm.created_at DESC");
$laporan->execute([$guruId]);
$laporanList = $laporan->fetchAll();

ob_start();
?>
<?php if (getInput('msg') === 'replied'): ?><div class="alert alert-success">Tanggapan berhasil dikirim.</div><?php endif; ?>

<?php if ($detailId && !empty($detail)): ?>
<div class="card">
    <div class="card-header">
        <h2><?= e($detail['judul']) ?></h2>
        <a href="laporan.php" class="btn btn-sm btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <div class="list-item-meta" style="margin-bottom:16px">
            <span>Dari: <?= $detail['is_anonim'] ? 'Anonim' : e($detail['nama_lengkap']) ?></span>
            <span class="badge badge-primary"><?= e(kategoriLabel($detail['kategori'])) ?></span>
            <span class="badge <?= statusBadge($detail['tingkat_urgensi']) ?>"><?= ucfirst($detail['tingkat_urgensi']) ?></span>
            <span class="badge <?= statusBadge($detail['status']) ?>"><?= ucfirst($detail['status']) ?></span>
        </div>
        <p style="margin-bottom:20px;line-height:1.8"><?= nl2br(e($detail['deskripsi'])) ?></p>

        <h3 style="font-size:0.95rem;margin-bottom:12px">Riwayat Tanggapan</h3>
        <?php foreach ($tanggapanList as $t): ?>
        <div class="sharing-card" style="margin-bottom:12px">
            <p><?= nl2br(e($t['isi_tanggapan'])) ?></p>
            <?php if ($t['saran']): ?><p style="margin-top:8px;color:var(--color-text-muted)"><strong>Saran:</strong> <?= nl2br(e($t['saran'])) ?></p><?php endif; ?>
            <div class="list-item-meta"><span><?= timeAgo($t['created_at']) ?></span></div>
        </div>
        <?php endforeach; ?>

        <form method="POST" style="margin-top:20px">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="tanggapi">
            <input type="hidden" name="laporan_id" value="<?= $detail['id'] ?>">
            <div class="form-group">
                <label>Tanggapan</label>
                <textarea name="isi_tanggapan" required placeholder="Tuliskan tanggapan dan bimbingan Anda..."></textarea>
            </div>
            <div class="form-group">
                <label>Saran (opsional)</label>
                <textarea name="saran" placeholder="Saran praktis untuk siswa..."></textarea>
            </div>
            <div style="display:flex;gap:10px">
                <button type="submit" class="btn btn-primary">Kirim Tanggapan</button>
                <select name="status" form="statusForm" style="padding:10px;border-radius:6px;border:1px solid var(--color-border)">
                    <?php foreach (['diproses','ditanggapi','selesai','ditutup'] as $s): ?>
                    <option value="<?= $s ?>" <?= $detail['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <form method="POST" id="statusForm" style="display:none">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="update_status">
            <input type="hidden" name="laporan_id" value="<?= $detail['id'] ?>">
        </form>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header"><h2>Laporan Masalah Siswa</h2></div>
    <div class="card-body">
        <?php foreach ($laporanList as $l): ?>
        <div class="list-item">
            <div class="list-item-content">
                <div class="list-item-title"><?= e($l['judul']) ?></div>
                <div class="list-item-meta">
                    <span><?= $l['is_anonim'] ? 'Anonim' : e($l['nama_lengkap']) ?></span>
                    <span class="badge badge-primary"><?= e(kategoriLabel($l['kategori'])) ?></span>
                    <span class="badge <?= statusBadge($l['status']) ?>"><?= ucfirst($l['status']) ?></span>
                    <span><?= timeAgo($l['created_at']) ?></span>
                </div>
            </div>
            <a href="laporan.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-primary">Tanggapi</a>
        </div>
        <?php endforeach; ?>
        <?php if (empty($laporanList)): ?>
        <div class="empty-state"><p>Belum ada laporan masuk</p></div>
        <?php endif; ?>
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
renderLayout('Laporan Masalah', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
