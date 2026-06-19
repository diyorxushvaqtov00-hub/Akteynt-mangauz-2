<?php
// admin/admin_actions.php
// Barcha admin POST so'rovlarini qayta ishlaydi (Delete, Toggle Featured)
// AJAX va oddiy form submit ikkalasini ham qabul qiladi

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Faqat POST so'rovlari
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Admin tekshiruvi
require_admin();

// CSRF tekshiruvi
csrf_verify();

$action = $_POST['action'] ?? '';
$id     = (int) ($_POST['manga_id'] ?? 0);

if ($id <= 0) {
    respond_error('Noto\'g\'ri manga ID.');
}

// JSON chiqarish uchun
header('Content-Type: application/json; charset=utf-8');

match ($action) {
    'delete'          => handle_delete($id),
    'toggle_featured' => handle_toggle_featured($id),
    default           => respond_error('Noma\'lum amal: ' . e($action)),
};

// ──────────────────────────────────────────────
//  Handlerlar
// ──────────────────────────────────────────────

function handle_delete(int $id): void
{
    // Mavjudligini tekshir
    $manga = manga_get_by_id($id);
    if (!$manga) {
        respond_error('Manga topilmadi.');
    }

    if (manga_delete($id)) {
        respond_success([
            'message' => '"' . e($manga['title']) . '" o\'chirildi.',
            'id'      => $id,
        ]);
    } else {
        respond_error('O\'chirishda xatolik yuz berdi.');
    }
}

function handle_toggle_featured(int $id): void
{
    $manga = manga_get_by_id($id);
    if (!$manga) {
        respond_error('Manga topilmadi.');
    }

    $new_val = manga_toggle_featured($id);
    $label   = $new_val === 1 ? 'Saralanganlarga qo\'shildi ⭐' : 'Saralanganganlardan olib tashlandi';

    respond_success([
        'message'     => '"' . e($manga['title']) . '": ' . $label,
        'is_featured' => $new_val,
        'id'          => $id,
    ]);
}

// ──────────────────────────────────────────────
//  JSON javoblar
// ──────────────────────────────────────────────

function respond_success(array $data): never
{
    echo json_encode(['success' => true, ...$data]);
    exit;
}

function respond_error(string $message, int $code = 400): never
{
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}
