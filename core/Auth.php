<?php
class Auth {
    public static function login(string $username, string $password): bool {
        $db  = Database::getInstance();
        $sql = 'SELECT id, password, full_name, role FROM users WHERE username = ? AND is_active = 1 LIMIT 1';
        $st  = $db->prepare($sql);
        $st->execute([$username]);
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $username;
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            return true;
        }
        return false;
    }

    public static function logout(): void {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }

    public static function requireAdmin(): void {
        self::check();
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            include __DIR__ . '/../includes/403.php';
            exit;
        }
    }

    public static function isAdmin(): bool {
        return ($_SESSION['role'] ?? '') === 'admin';
    }

    public static function id(): int {
        return (int)($_SESSION['user_id'] ?? 0);
    }
}
