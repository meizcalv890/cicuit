<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
Auth::requireRole('admin');

$db = Database::getConnection();
$currentPage = 'users';
$message = '';
$action = getInput('action', 'list');
$roleFilter = getInput('role', '');

function logAdminAction(PDO $db, string $aksi, string $detail = ''): void {
    $db->prepare('INSERT INTO log_aktivitas (admin_id, aksi, detail, ip_address) VALUES (?, ?, ?, ?)')
       ->execute([Auth::id(), $aksi, $detail, $_SERVER['REMOTE_ADDR'] ?? '']);
}

function createNotification(PDO $db, int $userId, string $judul, string $pesan, string $tipe = 'sistem', ?string $link = null): void {
    $db->prepare('INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, ?, ?)')
       ->execute([$userId, $judul, $pesan, $tipe, $link]);
}

if (isPost()) {
    if (!verifyCsrfToken(getInput('csrf_token'))) {
        $message = 'Token tidak valid.';
    } else {
        $postAction = getInput('post_action');

        if ($postAction === 'create') {
            $email = trim(getInput('email'));
            $nama = trim(getInput('nama_lengkap'));
            $password = getInput('password');
            $role = getInput('role');
            $no_telp = trim(getInput('no_telepon', ''));

            if (empty($email) || empty($nama) || empty($password) || empty($role)) {
                $message = 'Semua field wajib diisi.';
            } else {
                $check = $db->prepare('SELECT id FROM users WHERE email = ?');
                $check->execute([$email]);
                if ($check->fetch()) {
                    $message = 'Email sudah terdaftar.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $db->prepare('INSERT INTO users (email, password_hash, nama_lengkap, role, no_telepon) VALUES (?, ?, ?, ?, ?)')
                       ->execute([$email, $hash, $nama, $role, $no_telp ?: null]);
                    $userId = $db->lastInsertId();

                    if ($role === 'guru_wali') {
                        $db->prepare('INSERT INTO guru_wali (user_id, nip, bidang_keahlian, bio) VALUES (?, ?, ?, ?)')
                           ->execute([$userId, getInput('nip'), getInput('bidang_keahlian'), getInput('bio')]);
                    } elseif ($role === 'pelajar') {
                        $db->prepare('INSERT INTO pelajar (user_id, nis, kelas, jurusan) VALUES (?, ?, ?, ?)')
                           ->execute([$userId, getInput('nis'), getInput('kelas'), getInput('jurusan')]);
                    }

                    createNotification($db, $userId, 'Akun Dibuat', 'Akun Anda telah dibuat oleh administrator. Silakan login.');
                    logAdminAction($db, 'Buat pengguna', "$role: $nama ($email)");
                    redirect(APP_URL . '/admin/users.php?msg=created');
                }
            }
        }

        if ($postAction === 'update') {
            $id = (int)getInput('user_id');
            $nama = trim(getInput('nama_lengkap'));
            $no_telp = trim(getInput('no_telepon', ''));
            $is_active = getInput('is_active') ? 1 : 0;

            $db->prepare('UPDATE users SET nama_lengkap = ?, no_telepon = ?, is_active = ? WHERE id = ? AND role != ?')
               ->execute([$nama, $no_telp ?: null, $is_active, $id, 'admin']);

            $user = $db->prepare('SELECT role FROM users WHERE id = ?');
            $user->execute([$id]);
            $userData = $user->fetch();

            if ($userData['role'] === 'guru_wali') {
                $db->prepare('UPDATE guru_wali SET nip = ?, bidang_keahlian = ?, bio = ?, max_siswa = ? WHERE user_id = ?')
                   ->execute([getInput('nip'), getInput('bidang_keahlian'), getInput('bio'), (int)getInput('max_siswa', 25), $id]);
            } elseif ($userData['role'] === 'pelajar') {
                $db->prepare('UPDATE pelajar SET nis = ?, kelas = ?, jurusan = ?, alamat = ? WHERE user_id = ?')
                   ->execute([getInput('nis'), getInput('kelas'), getInput('jurusan'), getInput('alamat'), $id]);
            }

            logAdminAction($db, 'Update pengguna', "ID: $id - $nama");
            redirect(APP_URL . '/admin/users.php?msg=updated');
        }

        if ($postAction === 'delete') {
            $id = (int)getInput('user_id');
            $user = $db->prepare('SELECT nama_lengkap, role FROM users WHERE id = ? AND role != ?');
            $user->execute([$id, 'admin']);
            $u = $user->fetch();
            if ($u) {
                // Cegah hapus guru_wali jika ada siswa aktif yang masih dibimbing
                if ($u['role'] === 'guru_wali') {
                    $cek = $db->prepare("SELECT COUNT(*) FROM penugasan pn JOIN guru_wali gw ON pn.guru_wali_id = gw.id WHERE gw.user_id = ? AND pn.is_active = 1");
                    $cek->execute([$id]);
                    if ((int)$cek->fetchColumn() > 0) {
                        redirect(APP_URL . '/admin/users.php?msg=guru_has_students');
                    }
                }
                $db->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
                logAdminAction($db, 'Hapus pengguna', "{$u['role']}: {$u['nama_lengkap']}");
                redirect(APP_URL . '/admin/users.php?msg=deleted');
            }
        }
    }
}

$where = "WHERE u.role != 'admin'";
$params = [];
if ($roleFilter) {
    $where .= ' AND u.role = ?';
    $params[] = $roleFilter;
}

$search = trim(getInput('search', ''));
if ($search) {
    $where .= ' AND (u.nama_lengkap LIKE ? OR u.email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $db->prepare("SELECT u.*, gw.nip, p.nis, p.kelas FROM users u
    LEFT JOIN guru_wali gw ON u.id = gw.user_id
    LEFT JOIN pelajar p ON u.id = p.user_id
    $where ORDER BY u.created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

$msg = getInput('msg');
ob_start();
?>
<?php if ($msg === 'created'): ?><div class="alert alert-success">Pengguna berhasil dibuat.</div><?php endif; ?>
<?php if ($msg === 'updated'): ?><div class="alert alert-success">Data pengguna berhasil diperbarui.</div><?php endif; ?>
<?php if ($msg === 'deleted'): ?><div class="alert alert-success">Pengguna berhasil dihapus.</div><?php endif; ?>
<?php if ($msg === 'guru_has_students'): ?><div class="alert alert-error">Guru Wali tidak dapat dihapus karena masih memiliki siswa aktif. Pindahkan atau lepas siswa terlebih dahulu.</div><?php endif; ?>
<?php if ($message): ?><div class="alert alert-error"><?= e($message) ?></div><?php endif; ?>

<div class="page-actions">
    <div class="filter-group">
        <form method="GET" class="search-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" placeholder="Cari nama atau email..." value="<?= e($search) ?>">
            <?php if ($roleFilter): ?><input type="hidden" name="role" value="<?= e($roleFilter) ?>"><?php endif; ?>
        </form>
        <select onchange="location.href='users.php?role='+this.value+'&search=<?= e(urlencode($search)) ?>'">
            <option value="">Semua Role</option>
            <option value="guru_wali" <?= $roleFilter === 'guru_wali' ? 'selected' : '' ?>>Guru Wali</option>
            <option value="pelajar" <?= $roleFilter === 'pelajar' ? 'selected' : '' ?>>Pelajar</option>
        </select>
    </div>
    <button class="btn btn-primary" data-modal="modalCreate">Tambah Pengguna</button>
</div>

<div class="card">
    <div class="card-body table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Info</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= e($u['nama_lengkap']) ?></strong></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge badge-primary"><?= e(roleLabel($u['role'])) ?></span></td>
                    <td>
                        <?php if ($u['role'] === 'guru_wali'): ?>
                            NIP: <?= e($u['nip'] ?: '-') ?>
                        <?php elseif ($u['role'] === 'pelajar'): ?>
                            <?= e($u['nis'] ?: '-') ?> / <?= e($u['kelas'] ?: '-') ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $u['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                            <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td>
                        <a href="users.php?action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <?php
                        $confirmMsg = $u['role'] === 'guru_wali'
                            ? 'PERINGATAN: Menghapus Guru Wali akan menghapus semua data bimbingan! Yakin hapus ' . e($u['nama_lengkap']) . '?'
                            : 'Yakin hapus pengguna ' . e($u['nama_lengkap']) . '?';
                        ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('<?= $confirmMsg ?>')">
                            <?= csrfField() ?>
                            <input type="hidden" name="post_action" value="delete">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--color-text-muted)">Tidak ada data</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($action === 'edit' && ($editId = (int)getInput('id'))): ?>
<?php
$editStmt = $db->prepare("SELECT
    u.id, u.email, u.nama_lengkap, u.role, u.no_telepon, u.is_active,
    gw.nip, gw.bidang_keahlian, gw.bio, gw.max_siswa,
    p.nis, p.kelas, p.jurusan, p.alamat
    FROM users u
    LEFT JOIN guru_wali gw ON u.id = gw.user_id
    LEFT JOIN pelajar p ON u.id = p.user_id WHERE u.id = ?");
$editStmt->execute([$editId]);
$editUser = $editStmt->fetch();
if ($editUser):
?>
<div class="modal-overlay active" id="modalEdit">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Pengguna</h2>
            <a href="users.php" class="modal-close">&times;</a>
        </div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="update">
            <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?= e($editUser['email']) ?>" disabled>
                    <p class="form-hint">Email tidak dapat diubah (data privasi login).</p>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?= e($editUser['nama_lengkap']) ?>" required>
                </div>
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="no_telepon" value="<?= e($editUser['no_telepon'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_active" value="1" <?= $editUser['is_active'] ? 'checked' : '' ?>> Aktif</label>
                </div>
                <?php if ($editUser['role'] === 'guru_wali'): ?>
                <div class="form-row">
                    <div class="form-group"><label>NIP</label><input type="text" name="nip" value="<?= e($editUser['nip'] ?? '') ?>"></div>
                    <div class="form-group"><label>Max Siswa</label><input type="number" name="max_siswa" value="<?= e($editUser['max_siswa'] ?? 25) ?>"></div>
                </div>
                <div class="form-group"><label>Bidang Keahlian</label><input type="text" name="bidang_keahlian" value="<?= e($editUser['bidang_keahlian'] ?? '') ?>"></div>
                <div class="form-group"><label>Bio</label><textarea name="bio"><?= e($editUser['bio'] ?? '') ?></textarea></div>
                <?php elseif ($editUser['role'] === 'pelajar'): ?>
                <div class="form-row">
                    <div class="form-group"><label>NIS</label><input type="text" name="nis" value="<?= e($editUser['nis'] ?? '') ?>"></div>
                    <div class="form-group"><label>Kelas</label><input type="text" name="kelas" value="<?= e($editUser['kelas'] ?? '') ?>"></div>
                </div>
                <div class="form-group"><label>Jurusan</label><input type="text" name="jurusan" value="<?= e($editUser['jurusan'] ?? '') ?>"></div>
                <div class="form-group"><label>Alamat</label><textarea name="alamat"><?= e($editUser['alamat'] ?? '') ?></textarea></div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a href="users.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; endif; ?>

<div class="modal-overlay" id="modalCreate">
    <div class="modal">
        <div class="modal-header">
            <h2>Tambah Pengguna Baru</h2>
            <button class="modal-close">&times;</button>
        </div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="post_action" value="create">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="createRole" required>
                            <option value="guru_wali">Guru Wali</option>
                            <option value="pelajar">Pelajar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kata Sandi</label>
                        <input type="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="no_telepon">
                    </div>
                </div>
                <div id="guruFields">
                    <div class="form-row">
                        <div class="form-group"><label>NIP</label><input type="text" name="nip"></div>
                        <div class="form-group"><label>Bidang Keahlian</label><input type="text" name="bidang_keahlian"></div>
                    </div>
                    <div class="form-group"><label>Bio</label><textarea name="bio"></textarea></div>
                </div>
                <div id="pelajarFields" style="display:none">
                    <div class="form-row">
                        <div class="form-group"><label>NIS</label><input type="text" name="nis"></div>
                        <div class="form-group"><label>Kelas</label><input type="text" name="kelas"></div>
                    </div>
                    <div class="form-group"><label>Jurusan</label><input type="text" name="jurusan"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Buat Pengguna</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('createRole')?.addEventListener('change', function() {
    document.getElementById('guruFields').style.display = this.value === 'guru_wali' ? 'block' : 'none';
    document.getElementById('pelajarFields').style.display = this.value === 'pelajar' ? 'block' : 'none';
});
<?php if ($action === 'create'): ?>document.getElementById('modalCreate')?.classList.add('active');<?php endif; ?>
</script>
<?php
$content = ob_get_clean();
$sidebar = navItem(APP_URL.'/admin/index.php','Dashboard',iconDashboard(),$currentPage,'dashboard')
    . navItem(APP_URL.'/admin/users.php','Pengguna',iconUsers(),$currentPage,'users')
    . navItem(APP_URL.'/admin/penugasan.php','Penugasan',iconAssign(),$currentPage,'penugasan')
    . navItem(APP_URL.'/admin/laporan.php','Laporan',iconReport(),$currentPage,'laporan')
    . navItem(APP_URL.'/admin/sharing.php','Sharing',iconShare(),$currentPage,'sharing')
    . navItem(APP_URL.'/admin/pengaturan.php','Pengaturan',iconSettings(),$currentPage,'pengaturan')
    . navItem(APP_URL.'/admin/log.php','Log Aktivitas',iconLog(),$currentPage,'log');
renderLayout('Kelola Pengguna', $content, ['page' => $currentPage, 'sidebar' => $sidebar]);
