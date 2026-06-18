<?php
// includes/config.php
// Ma'lumotlar bazasi sozlamalari — .env yoki server muhitidan oling

define('DB_HOST', 'localhost');
define('DB_NAME', 'akteynt_manga');
define('DB_USER', 'root');          // O'zgartiring!
define('DB_PASS', 'your_password'); // O'zgartiring!
define('DB_CHARSET', 'utf8mb4');

// Admin sessiya kaliti (tasodifiy uzun qator)
define('SESSION_SECRET', 'AkteyNt$3cr3t!2026xY9');

// Sayt sozlamalari
define('SITE_NAME', 'Akteynt MangaUz');
define('ITEMS_PER_PAGE', 12);
