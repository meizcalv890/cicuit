<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/profil.php';
Auth::requireRole('pelajar');

$currentPage = 'profil';
renderProfilPage('pelajar', $currentPage,
    function($db, $userId) {
        $stmt = $db->prepare('SELECT * FROM pelajar WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    },
    function($db, $userId) {
        $db->prepare('UPDATE pelajar SET nis = ?, kelas = ?, jurusan = ?, alamat = ? WHERE user_id = ?')
           ->execute([getInput('nis'), getInput('kelas'), getInput('jurusan'), getInput('alamat'), $userId]);
    },
    function($extra) {
        ob_start();
        ?>
        <div class="form-row">
            <div class="form-group"><label>NIS</label><input type="text" name="nis" value="<?= e($extra['nis'] ?? '') ?>"></div>
            <div class="form-group"><label>Kelas</label><input type="text" name="kelas" value="<?= e($extra['kelas'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label>Jurusan</label><input type="text" name="jurusan" value="<?= e($extra['jurusan'] ?? '') ?>"></div>
        <div class="form-group"><label>Alamat</label><textarea name="alamat" rows="2"><?= e($extra['alamat'] ?? '') ?></textarea></div>
        <?php
        return ob_get_clean();
    }
);
