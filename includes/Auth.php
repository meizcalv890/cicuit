<?php
require_once __DIR__ . '/../config/Database.php';

class Auth {
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(string $email, string $password): array {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Email atau kata sandi salah.'];
        }

        $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['nama_lengkap'];
        $_SESSION['user_email'] = $user['email'];

        return ['success' => true, 'role' => $user['role']];
    }

    public static function logout(): void {
        session_destroy();
    }

    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array {
        if (!self::check()) return null;
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string {
        return $_SESSION['user_role'] ?? null;
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            redirect(APP_URL . '/login.php');
        }
    }

    public static function requireRole(string ...$roles): void {
        self::requireLogin();
        if (!in_array(self::role(), $roles)) {
            redirect(APP_URL . '/index.php');
        }
    }

    public static function getGuruWaliId(): ?int {
        if (self::role() !== 'guru_wali') return null;
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM guru_wali WHERE user_id = ?');
        $stmt->execute([self::id()]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }

    public static function getPelajarId(): ?int {
        if (self::role() !== 'pelajar') return null;
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM pelajar WHERE user_id = ?');
        $stmt->execute([self::id()]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }

    public static function dashboardUrl(): string {
        $role = self::role();
        return match ($role) {
            'admin' => APP_URL . '/admin/index.php',
            'guru_wali' => APP_URL . '/guru/index.php',
            'pelajar' => APP_URL . '/pelajar/index.php',
            default => APP_URL . '/login.php',
        };
    }
}
