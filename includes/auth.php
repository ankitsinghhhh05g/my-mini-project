<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    if (empty($_SESSION['user_id'])) {
        return $user = null;
    }
    $stmt = db()->prepare('SELECT id, full_name, email, role, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $user = ($stmt->fetch() ?: null);
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (current_user()['role'] !== 'admin') {
        flash('error', 'Administrator access is required.');
        redirect('dashboard.php');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Invalid form token. Please go back and try again.');
    }
}

function login_user(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
}
