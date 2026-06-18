<?php
// admin/login.php
// Admin kirish sahifasi

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

session_safe_start();

// Allaqachon tizimga kirgan bo'lsa, dashboard ga o'tkazish
if (is_admin()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Foydalanuvchi nomi va parolni kiriting.';
    } elseif (admin_login($username, $password)) {
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        // Brute-force ni sekinlashtirish
        sleep(1);
        $error = 'Foydalanuvchi nomi yoki parol noto\'g\'ri.';
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Kirish — Akteynt MangaUz</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #0d1117;
      font-family: system-ui, sans-serif;
      color: #e6edf3;
    }
    .card {
      background: #161b22;
      border: 1px solid #30363d;
      border-radius: 12px;
      padding: 2.5rem;
      width: 100%;
      max-width: 380px;
    }
    .logo { font-size: 1.4rem; font-weight: 800; text-align: center; margin-bottom: 1.5rem; }
    .logo span { color: #58a6ff; }
    .error {
      background: #3d1a1a;
      border: 1px solid #f85149;
      color: #f85149;
      border-radius: 8px;
      padding: .75rem 1rem;
      margin-bottom: 1rem;
      font-size: .875rem;
    }
    label { display: block; font-size: .85rem; color: #8b949e; margin-bottom: .4rem; }
    input[type=text], input[type=password] {
      width: 100%;
      background: #0d1117;
      border: 1px solid #30363d;
      border-radius: 8px;
      padding: .65rem .9rem;
      color: #e6edf3;
      font-size: .95rem;
      outline: none;
      transition: border-color .2s;
      margin-bottom: 1rem;
    }
    input:focus { border-color: #58a6ff; }
    button[type=submit] {
      width: 100%;
      background: #238636;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: .75rem;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: background .2s;
      margin-top: .25rem;
    }
    button[type=submit]:hover { background: #2ea043; }
  </style>
</head>
<body>
<div class="card">
  <div class="logo">Akteynt<span>MangaUz</span> <br><small style="font-size:.8rem;color:#8b949e">Admin Panel</small></div>

  <?php if ($error !== ''): ?>
    <div class="error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <label for="username">Foydalanuvchi nomi</label>
    <input
      type="text"
      id="username"
      name="username"
      autocomplete="username"
      value="<?= e($_POST['username'] ?? '') ?>"
      required
    >

    <label for="password">Parol</label>
    <input
      type="password"
      id="password"
      name="password"
      autocomplete="current-password"
      required
    >

    <button type="submit">Kirish</button>
  </form>
</div>
</body>
</html>
