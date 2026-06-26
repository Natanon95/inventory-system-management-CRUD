<?php
function e(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(mixed $val): string {
    return '฿' . number_format((float)$val, 2);
}

function redirect(string $path): void {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function flash(string $key, string $msg): void {
    $_SESSION['flash'][$key] = $msg;
}

function getFlash(string $key): string {
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfVerify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token mismatch.');
    }
}

function paginate(int $total, int $perPage, int $page): array {
    $pages  = (int)ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    return ['pages' => max(1, $pages), 'offset' => max(0, $offset)];
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    return match(true) {
        $diff < 60     => 'just now',
        $diff < 3600   => floor($diff / 60)    . ' min ago',
        $diff < 86400  => floor($diff / 3600)  . ' hr ago',
        $diff < 604800 => floor($diff / 86400) . ' days ago',
        default        => date('d M Y', strtotime($datetime)),
    };
}
