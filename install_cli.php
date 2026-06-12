<?php
require_once __DIR__ . '/config/config.php';

echo "Installing BimaGuru...\n";

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . DB_NAME . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE ' . DB_NAME);

    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    $schema = preg_replace('/CREATE DATABASE.*?;/is', '', $schema);
    $schema = preg_replace('/USE bimaguru;/i', '', $schema);

    foreach (array_filter(array_map('trim', explode(';', $schema))) as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }

    $seed = file_get_contents(__DIR__ . '/database/seed.sql');
    foreach (array_filter(array_map('trim', explode(';', $seed))) as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }

    if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
    file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));

    echo "SUCCESS! Installation complete.\n";
    echo "Login: admin@bimaguru.local / password\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
