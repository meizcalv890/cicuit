<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/profil.php';
Auth::requireRole('guru_wali');

$currentPage = 'profil';
$extraFields = function($db, $userId) {
    $stmt = $db->prepare('SELECT * FROM guru_wali WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch();
};

$updateExtra = function($db, $userId) {
    $db->prepare('UPDATE guru_wali SET nip = ?, bidang_keahlian = ?, bio = ? WHERE user_id = ?')
       ->execute([getInput('nip'), getInput('bidang_keahlian'), getInput('bio'), $userId]);
};

renderProfilPage('guru', $currentPage, $extraFields, $updateExtra, function($extra) {
    ob_start();
    ?>
    <div class="form-row">
        <div class="form-group"><label>NIP</label><input type="text" name="nip" value="<?= e($extra['nip'] ?? '') ?>"></div>
        <div class="form-group"><label>Bidang Keahlian</label><input type="text" name="bidang_keahlian" value="<?= e($extra['bidang_keahlian'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>Bio</label><textarea name="bio" rows="3"><?= e($extra['bio'] ?? '') ?></textarea></div>
    <?php
    return ob_get_clean();
});
