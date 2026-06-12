<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/layout.php';

function renderProfilPage(string $rolePrefix, string $currentPage, callable $getExtra, callable $updateExtra, callable $renderExtraFields): void {
    $db = Database::getConnection();
    $user = Auth::user();
    $message = '';

    if (isPost() && verifyCsrfToken(getInput('csrf_token'))) {
        $action = getInput('post_action');

        if ($action === 'update_profile') {
            $db->prepare('UPDATE users SET nama_lengkap = ?, no_telepon = ? WHERE id = ?')
               ->execute([trim(getInput('nama_lengkap')), trim(getInput('no_telepon', '')) ?: null, Auth::id()]);
            $updateExtra($db, Auth::id());
            $_SESSION['user_name'] = trim(getInput('nama_lengkap'));
            $message = 'Profil berhasil diperbarui.';
            $user = Auth::user();
        }

        if ($action === 'change_password') {
            $current = getInput('current_password');
            $newPass = getInput('new_password');
            $confirm = getInput('confirm_password');

            if (!password_verify($current, $user['password_hash'])) {
                $message = 'Kata sandi saat ini salah.';
            } elseif (strlen($newPass) < 6) {
                $message = 'Kata sandi baru minimal 6 karakter.';
            } elseif ($newPass !== $confirm) {
                $message = 'Konfirmasi kata sandi tidak cocok.';
            } else {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, Auth::id()]);
                $message = 'Kata sandi berhasil diubah.';
            }
        }
    }

    $extra = $getExtra($db, Auth::id());

    ob_start();
    ?>
    <?php if ($message): ?>
    <div class="alert <?= str_contains($message, 'berhasil') ? 'alert-success' : 'alert-error' ?>"><?= e($message) ?></div>
    <?php endif; ?>

    <div class="grid-2">
        <div class="card">
            <div class="card-header"><h2>Informasi Profil</h2></div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="post_action" value="update_profile">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= e($user['email']) ?>" disabled>
                        <p class="form-hint">Email tidak dapat diubah.</p>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= e($user['nama_lengkap']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="no_telepon" value="<?= e($user['no_telepon'] ?? '') ?>">
                    </div>
                    <?= $renderExtraFields($extra) ?>
                    <button type="submit" class="btn btn-primary">Simpan Profil</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>Ubah Kata Sandi</h2></div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="post_action" value="change_password">
                    <div class="form-group">
                        <label>Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>Kata Sandi Baru</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="confirm_password" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary">Ubah Kata Sandi</button>
                </form>
            </div>
        </div>
    </div>
    <?php
    $content = ob_get_clean();

    $sidebarFn = $rolePrefix === 'guru' ? 'guruSidebar' : 'pelajarSidebar';
    renderLayout('Profil Saya', $content, ['page' => $currentPage, 'sidebar' => $sidebarFn($currentPage)]);
}

function guruSidebar(string $currentPage): string {
    return navItem(APP_URL.'/guru/index.php','Dashboard',iconDashboard(),$currentPage,'dashboard')
        . navItem(APP_URL.'/guru/siswa.php','Siswa Saya',iconUsers(),$currentPage,'siswa')
        . navItem(APP_URL.'/guru/laporan.php','Laporan Masalah',iconReport(),$currentPage,'laporan')
        . navItem(APP_URL.'/guru/sesi.php','Sesi Bimbingan',iconCalendar(),$currentPage,'sesi')
        . navItem(APP_URL.'/guru/sharing.php','Sharing',iconShare(),$currentPage,'sharing')
        . navItem(APP_URL.'/guru/materi.php','Materi Bimbingan',iconBook(),$currentPage,'materi')
        . navItem(APP_URL.'/guru/catatan.php','Catatan Siswa',iconNote(),$currentPage,'catatan')
        . navItem(APP_URL.'/guru/kemajuan.php','Kemajuan Siswa',iconChart(),$currentPage,'kemajuan')
        . navItem(APP_URL.'/guru/profil.php','Profil',iconProfile(),$currentPage,'profil');
}

function pelajarSidebar(string $currentPage): string {
    return navItem(APP_URL.'/pelajar/index.php','Dashboard',iconDashboard(),$currentPage,'dashboard')
        . navItem(APP_URL.'/pelajar/laporan.php','Laporan Masalah',iconReport(),$currentPage,'laporan')
        . navItem(APP_URL.'/pelajar/jurnal.php','Jurnal Refleksi',iconJournal(),$currentPage,'jurnal')
        . navItem(APP_URL.'/pelajar/sharing.php','Sharing',iconShare(),$currentPage,'sharing')
        . navItem(APP_URL.'/pelajar/sesi.php','Sesi Bimbingan',iconCalendar(),$currentPage,'sesi')
        . navItem(APP_URL.'/pelajar/materi.php','Materi Bimbingan',iconBook(),$currentPage,'materi')
        . navItem(APP_URL.'/pelajar/kemajuan.php','Kemajuan Saya',iconChart(),$currentPage,'kemajuan')
        . navItem(APP_URL.'/pelajar/profil.php','Profil',iconProfile(),$currentPage,'profil');
}
