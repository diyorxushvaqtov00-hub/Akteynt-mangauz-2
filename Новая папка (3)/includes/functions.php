<?php
// includes/functions.php
// Manga CRUD va Featured tizimi uchun barcha funksiyalar

declare(strict_types=1);

require_once __DIR__ . '/db.php';

// ──────────────────────────────────────────────
//  O'QISH (READ)
// ──────────────────────────────────────────────

/**
 * Barcha mangalarni olish (sahifalash bilan)
 */
function manga_get_all(int $page = 1, int $limit = ITEMS_PER_PAGE): array
{
    $offset = ($page - 1) * $limit;
    $stmt = db()->prepare(
        'SELECT * FROM mangas ORDER BY is_featured DESC, views DESC LIMIT ? OFFSET ?'
    );
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

/**
 * Jami manga soni
 */
function manga_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM mangas')->fetchColumn();
}

/**
 * Featured mangalarni olish (saralangan blok uchun)
 */
function manga_get_featured(): array
{
    $stmt = db()->prepare(
        'SELECT * FROM mangas WHERE is_featured = 1 ORDER BY rating DESC'
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Oddiy (featured emas) mangalarni olish
 */
function manga_get_regular(): array
{
    $stmt = db()->prepare(
        'SELECT * FROM mangas WHERE is_featured = 0 ORDER BY views DESC'
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Bitta mangani ID bo'yicha olish
 */
function manga_get_by_id(int $id): array|false
{
    $stmt = db()->prepare('SELECT * FROM mangas WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Bitta mangani slug bo'yicha olish
 */
function manga_get_by_slug(string $slug): array|false
{
    $stmt = db()->prepare('SELECT * FROM mangas WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// ──────────────────────────────────────────────
//  YARATISH (CREATE)
// ──────────────────────────────────────────────

/**
 * Yangi manga qo'shish
 * @return int|false Yangi manga ID si yoki false
 */
function manga_create(array $data): int|false
{
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO mangas
            (slug, title, alt_title, cover, synopsis, status, author, artist, genres, rating, views, year, is_featured)
         VALUES
            (:slug, :title, :alt_title, :cover, :synopsis, :status, :author, :artist, :genres, :rating, :views, :year, :is_featured)'
    );

    $ok = $stmt->execute([
        ':slug'        => $data['slug'],
        ':title'       => $data['title'],
        ':alt_title'   => $data['alt_title']   ?? null,
        ':cover'       => $data['cover']        ?? null,
        ':synopsis'    => $data['synopsis']     ?? null,
        ':status'      => $data['status']       ?? 'Ongoing',
        ':author'      => $data['author']       ?? null,
        ':artist'      => $data['artist']       ?? null,
        ':genres'      => json_encode($data['genres'] ?? []),
        ':rating'      => (float) ($data['rating']  ?? 0),
        ':views'       => (int)   ($data['views']   ?? 0),
        ':year'        => (int)   ($data['year']    ?? date('Y')),
        ':is_featured' => (int)   ($data['is_featured'] ?? 0),
    ]);

    return $ok ? (int) $pdo->lastInsertId() : false;
}

// ──────────────────────────────────────────────
//  TAHRIRLASH (UPDATE)
// ──────────────────────────────────────────────

/**
 * Mangani tahrirlash
 */
function manga_update(int $id, array $data): bool
{
    $stmt = db()->prepare(
        'UPDATE mangas SET
            title       = :title,
            alt_title   = :alt_title,
            cover       = :cover,
            synopsis    = :synopsis,
            status      = :status,
            author      = :author,
            artist      = :artist,
            genres      = :genres,
            rating      = :rating,
            year        = :year,
            is_featured = :is_featured
         WHERE id = :id'
    );

    return $stmt->execute([
        ':title'       => $data['title'],
        ':alt_title'   => $data['alt_title']   ?? null,
        ':cover'       => $data['cover']        ?? null,
        ':synopsis'    => $data['synopsis']     ?? null,
        ':status'      => $data['status']       ?? 'Ongoing',
        ':author'      => $data['author']       ?? null,
        ':artist'      => $data['artist']       ?? null,
        ':genres'      => json_encode($data['genres'] ?? []),
        ':rating'      => (float) ($data['rating']  ?? 0),
        ':year'        => (int)   ($data['year']    ?? date('Y')),
        ':is_featured' => (int)   ($data['is_featured'] ?? 0),
        ':id'          => $id,
    ]);
}

// ──────────────────────────────────────────────
//  O'CHIRISH (DELETE)
// ──────────────────────────────────────────────

/**
 * Mangani o'chirish
 */
function manga_delete(int $id): bool
{
    $stmt = db()->prepare('DELETE FROM mangas WHERE id = ?');
    return $stmt->execute([$id]);
}

// ──────────────────────────────────────────────
//  FEATURED (PIN/UNPIN)
// ──────────────────────────────────────────────

/**
 * Featured holatini almashtirish (toggle)
 * @return int Yangi is_featured qiymati (0 yoki 1)
 */
function manga_toggle_featured(int $id): int
{
    // Avval hozirgi qiymatni olamiz
    $stmt = db()->prepare('SELECT is_featured FROM mangas WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $current = (int) $stmt->fetchColumn();

    $new_value = $current === 1 ? 0 : 1;

    $upd = db()->prepare('UPDATE mangas SET is_featured = ? WHERE id = ?');
    $upd->execute([$new_value, $id]);

    return $new_value;
}

// ──────────────────────────────────────────────
//  YORDAMCHI FUNKSIYALAR
// ──────────────────────────────────────────────

/**
 * Ko'rishlar sonini formatlash: 1200000 → 1.2M
 */
function format_views(int $views): string
{
    if ($views >= 1_000_000) return number_format($views / 1_000_000, 1) . 'M';
    if ($views >= 1_000)     return number_format($views / 1_000, 0) . 'K';
    return (string) $views;
}

/**
 * Xavfsiz chiqarish uchun HTML escape
 */
function e(mixed $val): string
{
    return htmlspecialchars((string) $val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Slug yaratish (sarlavhadan)
 */
function make_slug(string $title): string
{
    $slug = mb_strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9\s-]/u', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}
