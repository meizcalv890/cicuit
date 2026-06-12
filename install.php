<?php
/**
 * BimaGuru - Installer
 * Jalankan sekali via browser: http://localhost/pjwd/install.php
 */
require_once __DIR__ . '/config/config.php';

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        $statements = array_filter(array_map('trim', explode(';', $schema)));

        foreach ($statements as $stmt) {
            if (!empty($stmt) && !preg_match('/^(CREATE DATABASE|USE)/i', $stmt)) {
                try {
                    if (preg_match('/^CREATE DATABASE/i', $stmt)) {
                        $pdo->exec($stmt);
                    }
                } catch (Exception $e) {}
            }
        }

        $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . DB_NAME . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE ' . DB_NAME);

        foreach ($statements as $stmt) {
            if (!empty($stmt) && !preg_match('/^(CREATE DATABASE|USE)/i', $stmt)) {
                $pdo->exec($stmt);
            }
        }

        $seed = file_get_contents(__DIR__ . '/database/seed.sql');
        foreach (array_filter(array_map('trim', explode(';', $seed))) as $stmt) {
            if (!empty($stmt)) $pdo->exec($stmt);
        }

        if (!is_dir(__DIR__ . '/uploads')) {
            mkdir(__DIR__ . '/uploads', 0755, true);
        }

        file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));
        $messages[] = 'Instalasi berhasil! Database dan data contoh telah dibuat.';
    } catch (Exception $e) {
        $errors[] = 'Error: ' . $e->getMessage();
    }
}

$installed = file_exists(__DIR__ . '/.installed');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi - BimaGuru</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container" style="max-width:520px">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Instalasi BimaGuru</h1>
                <p>Sistem Bimbingan Guru Wali Digital</p>
            </div>

            <?php foreach ($errors as $err): ?>
            <div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>

            <?php foreach ($messages as $msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach; ?>

            <?php if ($installed && empty($messages)): ?>
            <div class="alert alert-info">Sistem sudah terinstal.</div>
            <a href="<?= APP_URL ?>/login.php" class="btn btn-primary btn-block" style="margin-top:16px">Ke Halaman Login</a>
            <?php elseif (!$installed || !empty($messages)): ?>
            <?php if (empty($messages)): ?>
            <p style="margin-bottom:20px;color:var(--color-text-muted)">Klik tombol di bawah untuk membuat database dan data contoh.</p>
            <form method="POST">
                <button type="submit" class="btn btn-primary btn-block">Install Sekarang</button>
            </form>
            <?php else: ?>
            <div style="margin-top:20px">
                <h3 style="font-size:0.95rem;margin-bottom:12px">Akun Demo:</h3>
                <table style="width:100%;font-size:0.9rem">
                    <tr><td style="padding:6px 0"><strong>Admin</strong></td><td>admin@bimaguru.local</td><td>password</td></tr>
                    <tr><td style="padding:6px 0"><strong>Guru Wali</strong></td><td>guru@bimaguru.local</td><td>password</td></tr>
                    <tr><td style="padding:6px 0"><strong>Pelajar</strong></td><td>siswa01@bimaguru.local</td><td>password</td></tr>
                </table>
                <a href="<?= APP_URL ?>/login.php" class="btn btn-primary btn-block" style="margin-top:20px">Ke Halaman Login</a>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
