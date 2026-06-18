<?php
// includes/db.php
// PDO ulanishini bir marta yaratadi va qayta ishlatadi (Singleton pattern)

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Haqiqiy prepared statements
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Xatolikni foydalanuvchidan yashirish (xavfsizlik)
            error_log('DB ulanish xatosi: ' . $e->getMessage());
            die(json_encode(['error' => 'Serverda xatolik yuz berdi.']));
        }
    }

    return $pdo;
}
