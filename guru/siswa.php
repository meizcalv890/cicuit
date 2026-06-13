<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('guru_wali');

$db = Database::getConnection();
$guruId = Auth::getGuruWaliId();
$currentPage = 'siswa';
$message = '';

// Handle tambah siswa
if (isPost()) {
    if (!verifyCsrfToken(getInput('csrf_token'))) {
        $message = 'error:Token tidak valid.';
    } else {
        $postAction = getInput('post_action');

        if ($postAction === 'tambah_siswa') {
            $pelajarIds = $_POST['pelajar_ids'] ?? [];
            $pelajarIds = array_map('intval', array_filter($pelajarIds));

            if (empty($pelajarIds)) {
                $message = 'error:Pilih minimal satu siswa.';
            } else {
                // Cek kapasitas guru
                $capStmt = $db->prepare("SELECT max_siswa FROM guru_wali WHERE id = ?");
                $capStmt->execute([$guruId]);
                $cap = $capStmt->fetchColumn() ?: 25;

                $currentCount = $db->prepare("SELECT COUNT(*) FROM penugasan WHERE guru_wali_id = ? AND is_active = 1");
                $currentCount->execute([$guruId]);
                $jumlahSaatIni = (int)$currentCount->fetchColumn();

                $sisa = $cap - $jumlahSaatIni;
                if (count($pelajarIds) > $sisa) {
                    $message = "error:Kapasitas tidak cukup. Anda hanya dapat menambah $sisa siswa lagi (maks $cap).";
                } else {
                    $added = 0;
                    foreach ($pelajarIds as $pid) {
                        // Pastikan pelajar belum ditugaskan ke guru ini
                        $cek = $db->prepare("SELECT id FROM penugasan WHERE guru_wali_id = ? AND pelajar_id = ? AND is_active = 1");
                        $cek->execute([$guruId, $pid]);
                        if (!$cek->fetch()) {
                            $db->prepare("INSERT INTO penugasan (guru_wali_id, pelajar_id, tanggal_mulai, is_active) VALUES (?, ?, CURDATE(), 1)")
                               ->execute([$guruId, $pid]);
                            // Notifikasi ke siswa
                            $pInfo = $db->prepare("SELECT u.id, u.nama_lengkap FROM pelajar p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
                            $pInfo->execute([$pid]);
                            $pData = $pInfo->fetch();
                            if ($pData) {
                                $guruName = $_SESSION['user_name'] ?? 'Guru Wali';
                                $db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, 'info', ?)")
                                   ->execute([$pData['id'], 'Ditugaskan ke Guru Wali', "Anda telah ditugaskan ke $guruName sebagai guru wali Anda.", APP_URL . '/pelajar/index.php']);
                            }
                            $added++;
                        }
                    }
                    redirect(APP_URL . '/guru/siswa.php?msg=added&count=' . $added);
                }
            }
        }

        if ($postAction === 'lepas_siswa') {
            $pelajarId = (int)getInput('pelajar_id');
            $db->prepare("UPDATE penugasan SET is_active = 0, tanggal_selesai = CURDATE() WHERE guru_wali_id = ? AND pelajar_id = ? AND is_active = 1")
               ->execute([$guruId, $pelajarId]);
            redirect(APP_URL . '/guru/siswa.php?msg=removed');
        }
    }
}

// Daftar siswa yang sudah dibimbing
$siswa = $db->prepare("SELECT p.*, u.nama_lengkap, u.email, u.no_telepon, pn.tanggal_mulai, pn.id as penugasan_id,
    (SELECT COUNT(*) FROM laporan_masalah lm WHERE lm.pelajar_id = p.id AND lm.status IN ('baru','diproses')) as laporan_aktif,
    (SELECT COUNT(*) FROM sesi_bimbingan sb WHERE sb.pelajar_id = p.id AND sb.status = 'dijadwalkan') as sesi_aktif
    FROM penugasan pn
    JOIN pelajar p ON pn.pelajar_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE pn.guru_wali_id = ? AND pn.is_active = 1
    ORDER BY u.nama_lengkap");
$siswa->execute([$guruId]);
$siswaList = $siswa->fetchAll();

// Daftar siswa yang BELUM ditugaskan ke guru ini (untuk modal tambah)
$available = $db->prepare("SELECT p.id, p.nis, p.kelas, p.jurusan, u.nama_lengkap, u.email
    FROM pelajar p
    JOIN users u ON p.user_id = u.id
    WHERE u.is_active = 1
    AND p.id NOT IN (
        SELECT pelajar_id FROM penugasan WHERE guru_wali_id = ? AND is_active = 1
    )
    ORDER BY u.nama_lengkap");
$available->execute([$guruId]);
$availableList = $available->fetchAll();

// Info kapasitas guru
$capInfo = $db->prepare("SELECT max_siswa FROM guru_wali WHERE id = ?");
$capInfo->execute([$guruId]);
$maxSiswa = (int)($capInfo->fetchColumn() ?: 25);
$sisaKapasitas = $maxSiswa - count($siswaList);

$detailId = (int)getInput('id', 0);
$detailSiswa = null;
$kemajuanSiswa = [];
if ($detailId) {
    $d = $db->prepare("SELECT p.*, u.nama_lengkap, u.email FROM pelajar p JOIN users u ON p.user_id = u.id
        JOIN penugasan pn ON pn.pelajar_id = p.id WHERE p.id = ? AND pn.guru_wali_id = ? AND pn.is_active = 1");
    $d->execute([$detailId, $guruId]);
    $detailSiswa = $d->fetch();
    if ($detailSiswa) {
        $k = $db->prepare("SELECT * FROM kemajuan WHERE pelajar_id = ? AND guru_wali_id = ? ORDER BY created_at DESC LIMIT 5");
        $k->execute([$detailId, $guruId]);
        $kemajuanSiswa = $k->fetchAll();
    }
}

$msg = getInput('msg');
ob_start();
?>

<?php if ($msg === 'added'): ?>
<div class="alert alert-success"><?= (int)getInput('count') ?> siswa berhasil ditambahkan.</div>
<?php endif; ?>
<?php if ($msg === 'removed'): ?>
<div class="alert alert-success">Siswa berhasil dilepas dari bimbingan.</div>
<?php endif; ?>
<?php if ($message): ?>
<div class="alert alert-<?= strpos($message, 'error:') === 0 ? 'error' : 'success' ?>"><?= e(str_replace('error:', '', $message)) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h2>Siswa yang Dibimbing (<?= count($siswaList) ?>/<?= $maxSiswa ?>)</h2>
        <?php if ($sisaKapasitas > 0 && !empty($availableList)): ?>
        <button class="btn btn-primary" data-modal="modalTambahSiswa">+ Tambah Siswa</button>
        <?php elseif ($sisaKapasitas <= 0): ?>
        <span style="font-size:0.85rem;color:var(--color-text-muted)">Kapasitas penuh</span>
        <?php endif; ?>
    </div>
    <div class="card-body table-wrapper">
        <table>
            <thead>
                <tr><th>Nama</th><th>NIS</th><th>Kelas</th><th>Dibimbing Sejak</th><th>Laporan Aktif</th><th>Sesi Aktif</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($siswaList as $s): ?>
                <tr>
                    <td><strong><?= e($s['nama_lengkap']) ?></strong></td>
                    <td><?= e($s['nis'] ?: '-') ?></td>
                    <td><?= e($s['kelas'] ?: '-') ?></td>
                    <td><?= formatTanggal($s['tanggal_mulai']) ?></td>
                    <td><?= $s['laporan_aktif'] ?></td>
                    <td><?= $s['sesi_aktif'] ?></td>
                    <td style="display:flex;gap:4px;flex-wrap:wrap">
                        <a href="siswa.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Detail</a>
                        <a href="catatan.php?pelajar_id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Catatan</a>
                        <a href="kemajuan.php?pelajar_id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Kemajuan</a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Lepas siswa ini dari bimbingan Anda?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="post_action" value="lepas_siswa">
                            <input type="hidden" name="pelajar_id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Lepas</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($siswaList)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--color-text-muted)">Belum ada siswa ditugaskan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($detailSiswa): ?>
<div class="card">
    <div class="card-header"><h2>Detail: <?= e($detailSiswa['nama_lengkap']) ?></h2></div>
    <div class="card-body">
        <div class="grid-2">
            <div>
                <p><strong>Email:</strong> <?= e($detailSiswa['email']) ?></p>
                <p><strong>NIS:</strong> <?= e($detailSiswa['nis'] ?: '-') ?></p>
                <p><strong>Kelas:</strong> <?= e($detailSiswa['kelas'] ?: '-') ?></p>
                <p><strong>Jurusan:</strong> <?= e($detailSiswa['jurusan'] ?: '-') ?></p>
            </div>
            <div>
                <h3 style="margin-bottom:12px;font-size:0.95rem">Kemajuan Terbaru</h3>
                <?php if (empty($kemajuanSiswa)): ?>
                <p style="color:var(--color-text-muted)">Belum ada data kemajuan</p>
                <?php else: ?>
                <div class="progress-grid">
                    <?php foreach ($kemajuanSiswa as $km): ?>
                    <div class="progress-item">
                        <label><?= e(kategoriLabel($km['aspek'])) ?></label>
                        <div class="progress-bar"><div class="progress-fill" style="width:<?= $km['nilai'] ?>%"></div></div>
                        <div class="progress-value"><?= $km['nilai'] ?>%</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Tambah Siswa -->
<?php if (!empty($availableList)): ?>
<div class="modal-overlay" id="modalTambahSiswa">
    <div class="modal" style="max-width:640px">
        <div class="modal-header">
            <h2>Tambah Siswa ke Bimbingan</h2>
            <button class="modal-close">&times;</button>
        </div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="tambah_siswa">
            <div class="modal-body">
                <p style="margin-bottom:12px;font-size:0.9rem;color:var(--color-text-muted)">
                    Pilih siswa yang ingin Anda bimbing. Kapasitas tersisa: <strong><?= $sisaKapasitas ?></strong> dari <?= $maxSiswa ?> slot.
                </p>
                <div style="margin-bottom:10px">
                    <input type="text" id="searchSiswaModal" placeholder="Cari nama atau NIS..." 
                           style="width:100%;padding:8px 12px;border:1px solid var(--color-border);border-radius:6px;font-size:0.9rem">
                </div>
                <div style="max-height:340px;overflow-y:auto;border:1px solid var(--color-border);border-radius:6px">
                    <table style="width:100%;border-collapse:collapse">
                        <thead>
                            <tr style="background:var(--color-bg-secondary);position:sticky;top:0">
                                <th style="padding:8px 12px;text-align:left;font-size:0.82rem;font-weight:600;width:36px">
                                    <input type="checkbox" id="selectAllSiswa" title="Pilih Semua">
                                </th>
                                <th style="padding:8px 12px;text-align:left;font-size:0.82rem;font-weight:600">Nama</th>
                                <th style="padding:8px 12px;text-align:left;font-size:0.82rem;font-weight:600">NIS</th>
                                <th style="padding:8px 12px;text-align:left;font-size:0.82rem;font-weight:600">Kelas</th>
                            </tr>
                        </thead>
                        <tbody id="siswaModalBody">
                            <?php foreach ($availableList as $av): ?>
                            <tr class="siswa-row" style="border-top:1px solid var(--color-border)" 
                                data-nama="<?= strtolower(e($av['nama_lengkap'])) ?>" 
                                data-nis="<?= strtolower(e($av['nis'] ?? '')) ?>">
                                <td style="padding:8px 12px">
                                    <input type="checkbox" name="pelajar_ids[]" value="<?= $av['id'] ?>" class="siswa-check">
                                </td>
                                <td style="padding:8px 12px;font-size:0.9rem"><strong><?= e($av['nama_lengkap']) ?></strong></td>
                                <td style="padding:8px 12px;font-size:0.9rem;color:var(--color-text-muted)"><?= e($av['nis'] ?: '-') ?></td>
                                <td style="padding:8px 12px;font-size:0.9rem;color:var(--color-text-muted)"><?= e($av['kelas'] ?: '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p id="selectedCount" style="margin-top:8px;font-size:0.85rem;color:var(--color-text-muted)">0 siswa dipilih</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnTambahSubmit" disabled>Tambah Siswa Terpilih</button>
            </div>
        </form>
    </div>
</div>
<script>
(function() {
    const checks = document.querySelectorAll('.siswa-check');
    const selectAll = document.getElementById('selectAllSiswa');
    const countEl = document.getElementById('selectedCount');
    const submitBtn = document.getElementById('btnTambahSubmit');
    const searchEl = document.getElementById('searchSiswaModal');
    const maxAdd = <?= $sisaKapasitas ?>;

    function updateCount() {
        const checked = document.querySelectorAll('.siswa-check:checked').length;
        countEl.textContent = checked + ' siswa dipilih';
        submitBtn.disabled = checked === 0;
        if (checked > maxAdd) {
            countEl.style.color = 'var(--color-danger, #dc3545)';
            countEl.textContent = checked + ' dipilih — melebihi kapasitas (' + maxAdd + ' tersisa)';
            submitBtn.disabled = true;
        } else {
            countEl.style.color = 'var(--color-text-muted)';
        }
    }

    checks.forEach(c => c.addEventListener('change', updateCount));

    selectAll?.addEventListener('change', function() {
        document.querySelectorAll('.siswa-row:not([style*="display:none"]) .siswa-check').forEach(c => {
            c.checked = this.checked;
        });
        updateCount();
    });

    searchEl?.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.siswa-row').forEach(row => {
            const nama = row.dataset.nama || '';
            const nis = row.dataset.nis || '';
            row.style.display = (!q || nama.includes(q) || nis.includes(q)) ? '' : 'none';
        });
        // uncheck hidden ones
        document.querySelectorAll('.siswa-row[style*="display:none"] .siswa-check').forEach(c => { c.checked = false; });
        updateCount();
    });
})();
</script>
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
renderLayout('Siswa Saya', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
