<?php
// includes/auth.php
// Admin autentifikatsiyasi funksiyalari

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Sessionni xavfsiz boshlash
 */
function session_safe_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => false, // HTTPS bo'lsa true qiling
            'httponly' => true,  // JavaScript kirolmasin
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

/**
 * Admin tizimga kirganligi tekshiradi
 */
function is_admin(): bool
{
    session_safe_start();
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_logged_in']);
}

/**
 * Admin emas bo'lsa sahifadan qaytaradi
 */
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Admin login — foydalanuvchi nomi va parol tekshiradi
 */
function admin_login(string $username, string $password): bool
{
    $stmt = db()->prepare(
        'SELECT id, password FROM admins WHERE username = ? LIMIT 1'
    );
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if ($row && password_verify($password, $row['password'])) {
        session_safe_start();
        session_regenerate_id(true); // Session fixation himoyasi

        $_SESSION['admin_id']        = (int) $row['id'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        return true;
    }

    return false;
}

/**
 * Admin tizimdan chiqarish
 */
function admin_logout(): void
{
    session_safe_start();
    $_SESSION = [];
    session_destroy();
}

/**
 * CSRF token yaratish
 */
function csrf_token(): string
{
    session_safe_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF tokenni tekshirish
 */
function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('Xavfsizlik xatosi: CSRF token noto\'g\'ri.');
    }
}
