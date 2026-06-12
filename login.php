<?php
require_once __DIR__ . '/bootstrap.php';

if (Auth::check()) {
    redirect(Auth::dashboardUrl());
}

$error = '';
if (isPost()) {
    $email = trim(getInput('email', ''));
    $password = getInput('password', '');

    if (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        $result = Auth::login($email, $password);
        if ($result['success']) {
            redirect(Auth::dashboardUrl());
        }
        $error = $result['message'];
    }
}

$pageTitle = 'Masuk';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                        <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
                        <path d="M24 12v12l8 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="24" cy="24" r="3" fill="currentColor"/>
                    </svg>
                </div>
                <h1><?= e(getSetting('nama_aplikasi', APP_NAME)) ?></h1>
                <p><?= e(getSetting('tagline', 'Bimbingan Guru Wali Digital')) ?></p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="nama@email.com" value="<?= e(getInput('email', '')) ?>">
                </div>
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" required placeholder="Masukkan kata sandi">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </form>

            <div class="auth-footer">
                <p>Sistem Bimbingan Guru Wali</p>
            </div>
        </div>
    </div>
</body>
</html>
