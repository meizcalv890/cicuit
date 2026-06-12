<?php

function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function isPost(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function isGet(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

function getInput(string $key, $default = null) {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function getJsonInput(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function formatTanggal(?string $date, string $format = 'd M Y'): string {
    if (!$date) return '-';
    $dt = new DateTime($date);
    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    if ($format === 'd M Y') {
        return $dt->format('d') . ' ' . $bulan[(int)$dt->format('n') - 1] . ' ' . $dt->format('Y');
    }
    return $dt->format($format);
}

function formatWaktu(?string $datetime): string {
    if (!$datetime) return '-';
    return formatTanggal($datetime, 'd M Y') . ' ' . date('H:i', strtotime($datetime));
}

function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    return formatTanggal($datetime);
}

function statusBadge(string $status): string {
    $map = [
        'baru' => 'badge-info',
        'diproses' => 'badge-warning',
        'ditanggapi' => 'badge-primary',
        'selesai' => 'badge-success',
        'ditutup' => 'badge-secondary',
        'dijadwalkan' => 'badge-info',
        'berlangsung' => 'badge-warning',
        'dibatalkan' => 'badge-danger',
        'rendah' => 'badge-success',
        'sedang' => 'badge-warning',
        'tinggi' => 'badge-danger',
    ];
    return $map[$status] ?? 'badge-secondary';
}

function kategoriLabel(string $kategori): string {
    $map = [
        'psikologis' => 'Psikologis',
        'pendidikan' => 'Pendidikan',
        'sosial' => 'Sosial',
        'materi' => 'Materi',
        'keluarga' => 'Keluarga',
        'lainnya' => 'Lainnya',
        'motivasi' => 'Motivasi',
        'pengalaman' => 'Pengalaman',
        'tips' => 'Tips',
        'informasi' => 'Informasi',
        'refleksi' => 'Refleksi',
        'akademik' => 'Akademik',
        'emosional' => 'Emosional',
        'spiritual' => 'Spiritual',
        'karakter' => 'Karakter',
    ];
    return $map[$kategori] ?? ucfirst($kategori);
}

function moodLabel(string $mood): string {
    $map = [
        'senang' => 'Senang',
        'netral' => 'Netral',
        'sedih' => 'Sedih',
        'cemas' => 'Cemas',
        'semangat' => 'Semangat',
    ];
    return $map[$mood] ?? ucfirst($mood);
}

function roleLabel(string $role): string {
    $map = [
        'admin' => 'Administrator',
        'guru_wali' => 'Guru Wali',
        'pelajar' => 'Pelajar',
    ];
    return $map[$role] ?? ucfirst($role);
}

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(generateCsrfToken()) . '">';
}

function getInitials(string $name): string {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $initials ?: '?';
}

function paginate(int $total, int $page, int $perPage = 10): array {
    $totalPages = max(1, ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    return [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
    ];
}

function getSetting(string $key, $default = null) {
    static $cache = [];
    if (!isset($cache[$key])) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT nilai FROM pengaturan WHERE kunci = ?');
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            $cache[$key] = $row ? $row['nilai'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
    return $cache[$key];
}
