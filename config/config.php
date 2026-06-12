<?php
// ============================================================
//  AUTO-DETECT ENVIRONMENT (Windows XAMPP / Linux Apache2)
//  Tidak perlu ubah apapun — config ini bekerja di kedua OS
// ============================================================

$isWindows = (PHP_OS_FAMILY === 'Windows');

// --- Database ---
define('DB_HOST',    'localhost');
define('DB_NAME',    'bimaguru');
define('DB_USER',    'root');
// Windows XAMPP: password root biasanya kosong ''
// Linux Apache2: password root '12345'
define('DB_PASS',    $isWindows ? '' : '12345');
define('DB_CHARSET', 'utf8mb4');

// --- Aplikasi ---
define('APP_NAME', 'BimaGuru');
// URL sama di kedua OS karena folder project sama (pjwd/cicuit)
define('APP_URL',  'http://localhost/pjwd/cicuit');
define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_PATH', APP_ROOT . '/uploads');
define('SESSION_LIFETIME', 7200);

date_default_timezone_set('Asia/Jakarta');
