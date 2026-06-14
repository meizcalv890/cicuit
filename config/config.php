<?php
// ============================================================
//  AUTO-DETECT ENVIRONMENT (Replit / Windows XAMPP / Linux)
// ============================================================

// --- Database ---
define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    '3306');
define('DB_NAME',    'bimaguru');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');
define('DB_SOCKET',  '/home/runner/mysql-run/mysql.sock');

// --- Aplikasi ---
define('APP_NAME', 'BimaGuru');

// Auto-detect APP_URL for Replit or local dev (termasuk XAMPP subfolder)
if (!empty($_SERVER['HTTP_HOST'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    }
    $host = $_SERVER['HTTP_HOST'];

    // Deteksi subfolder: bandingkan DOCUMENT_ROOT dengan direktori app
    $appRoot  = realpath(__DIR__ . '/..');
    $docRoot  = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $basePath = '';
    if ($docRoot && $appRoot && strpos($appRoot, $docRoot) === 0) {
        // XAMPP: app ada di subfolder htdocs
        $basePath = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
    }
    define('APP_URL', rtrim($scheme . '://' . $host . $basePath, '/'));
} else {
    define('APP_URL', 'http://localhost:5000');
}

define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_PATH', APP_ROOT . '/uploads');
define('SESSION_LIFETIME', 7200);

date_default_timezone_set('Asia/Jakarta');
