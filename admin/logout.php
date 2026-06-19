<?php
// admin/logout.php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
session_safe_start();
csrf_verify();   // Faqat POST orqali chiqish mumkin
admin_logout();
header('Location: /admin/login.php');
exit;
