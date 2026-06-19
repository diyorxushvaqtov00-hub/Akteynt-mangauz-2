<?php
// admin/edit.php
// Manga yaratish / tahrirlash formasi

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$id    = (int) ($_GET['id'] ?? 0);
$manga = $id > 0 ? manga_get_by_id($id) : null;
$isNew = $manga === null || $manga === false;
$csrf  = csrf_token();

// POST — saqlash
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'slug'        => make_slug($_POST['title'] ?? ''),
        'title'       => trim($_POST['title']       ?? ''),
        'alt_title'   => trim($_POST['alt_title']   ?? '') ?: null,
        'cover'       => trim($_POST['cover']       ?? '') ?: null,
        'synopsis'    => trim($_POST['synopsis']    ?? '') ?: null,
        'status'      => $_POST['status']           ?? 'Ongoing',
        'author'      => trim($_POST['author']      ?? '') ?: null,
        'artist'      => trim($_POST['artist']      ?? '') ?: null,
        'genres'      => $_POST['genres']           ?? [],
        'rating'      => (float) ($_POST['rating']  ?? 0),
        'views'       => (int)   ($_POST['views']   ?? 0),
        'year'        => (int)   ($_POST['year']    ?? date('Y')),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
    ];

    // Validatsiya
    if ($data['title'] === '') {
        $errors[] = 'Sarlavha majburiy.';
    }
    if ($data['rating'] < 0 || $data['rating'] > 10) {
        $errors[] = 'Reyting 0–10 oralig\'ida bo\'lishi kerak.';
    }

    if (empty($errors)) {
        if ($isNew) {
            $newId = manga_create($data);
            if ($newId) {
                header('Location: /admin/dashboard.php?msg=created');
                exit;
            }
            $errors[] = 'Saqlashda xatolik.';
        } else {
            if (manga_update($id, $data)) {
                header('Location: /admin/dashboard.php?msg=updated');
                exit;
            }
            $errors[] = 'Yangilashda xatolik.';
        }
    }

    // Xatolik bo'lsa formni qayta to'ldiramiz
    $manga = $data;
}

$genres_list = [
    'Action','Adventure','Fantasy','Romance','Drama',
    'Sci-Fi','Horror','Comedy','Mystery','Supernatural',
    'Slice of Life','Martial Arts',
];
$selected_genres = is_array($manga['genres'] ?? null)
    ? $manga['genres']
    : json_decode($manga['genres'] ?? '[]', true);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $isNew ? 'Yangi manga' : 'Tahrirlash' ?> — Admin</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background:#0d1117; color:#e6edf3; font-family:system-ui,sans-serif; min-height:100vh; }
    nav {
      background:#161b22; border-bottom:1px solid #30363d;
      display:flex; align-items:center; gap:1rem; padding:.85rem 1.5rem;
    }
    nav .brand { font-weight:800; font-size:1.1rem; }
    nav .brand span { color:#58a6ff; }
    nav a { color:#8b949e; text-decoration:none; font-size:.875rem;
      padding:.4rem .8rem; border-radius:6px; transition:background .15s; }
    nav a:hover { background:#21262d; color:#e6edf3; }

    main { max-width:700px; margin:0 auto; padding:2rem 1rem; }
    h1 { font-size:1.3rem; font-weight:700; margin-bottom:1.5rem; }

    .error-box {
      background:#3d1a1a; border:1px solid #f85149; border-radius:8px;
      padding:.9rem 1.2rem; margin-bottom:1.2rem;
    }
    .error-box li { color:#f85149; font-size:.875rem; margin-left:1rem; }

    .form-group { margin-bottom:1.2rem; }
    label { display:block; font-size:.85rem; color:#8b949e; margin-bottom:.4rem; }
    input[type=text], input[type=number], input[type=url],
    select, textarea {
      width:100%; background:#0d1117; border:1px solid #30363d;
      border-radius:8px; padding:.65rem .9rem; color:#e6edf3;
      font-size:.95rem; outline:none; transition:border-color .2s;
    }
    input:focus, select:focus, textarea:focus { border-color:#58a6ff; }
    textarea { resize:vertical; min-height:100px; font-family:inherit; }

    .genres-grid {
      display:flex; flex-wrap:wrap; gap:.5rem; margin-top:.3rem;
    }
    .genre-label {
      display:flex; align-items:center; gap:.3rem;
      background:#21262d; border:1px solid #30363d; border-radius:20px;
      padding:.3rem .8rem; cursor:pointer; font-size:.8rem;
      transition:all .15s;
    }
    .genre-label:has(input:checked) {
      background:#1f4080; border-color:#58a6ff; color:#58a6ff;
    }
    .genre-label input { display:none; }

    .featured-toggle {
      display:flex; align-items:center; gap:.7rem; margin-top:.4rem;
    }
    .switch { position:relative; width:44px; height:24px; }
    .switch input { opacity:0; width:0; height:0; }
    .slider {
      position:absolute; cursor:pointer; inset:0;
      background:#30363d; border-radius:24px; transition:.2s;
    }
    .slider:before {
      content:''; position:absolute; height:18px; width:18px;
      left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s;
    }
    .switch input:checked + .slider { background:#e3b341; }
    .switch input:checked + .slider:before { transform:translateX(20px); }

    .row2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    @media(max-width:520px){ .row2{ grid-template-columns:1fr; } }

    .btn-bar { display:flex; gap:.75rem; margin-top:1.5rem; flex-wrap:wrap; }
    .btn-save {
      background:#238636; color:#fff; border:none; border-radius:8px;
      padding:.7rem 1.5rem; font-size:1rem; font-weight:700; cursor:pointer;
      transition:background .15s;
    }
    .btn-save:hover { background:#2ea043; }
    .btn-cancel {
      background:#21262d; color:#e6edf3; border:1px solid #30363d;
      border-radius:8px; padding:.7rem 1.2rem; font-size:1rem; cursor:pointer;
      text-decoration:none; display:inline-flex; align-items:center;
    }
    .btn-cancel:hover { background:#30363d; }
  </style>
</head>
<body>
<nav>
  <div class="brand">Akteynt<span>MangaUz</span></div>
  <a href="/admin/dashboard.php">← Dashboard</a>
</nav>

<main>
  <h1><?= $isNew ? '➕ Yangi manga qo\'shish' : '✏️ Manga tahrirlash' ?></h1>

  <?php if (!empty($errors)): ?>
    <div class="error-box">
      <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <div class="form-group">
      <label for="title">Sarlavha *</label>
      <input type="text" id="title" name="title" value="<?= e($manga['title'] ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label for="alt_title">Muqobil sarlavha (ixtiyoriy)</label>
      <input type="text" id="alt_title" name="alt_title" value="<?= e($manga['alt_title'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="synopsis">Qisqacha mazmun</label>
      <textarea id="synopsis" name="synopsis"><?= e($manga['synopsis'] ?? '') ?></textarea>
    </div>

    <div class="row2">
      <div class="form-group">
        <label for="author">Muallif</label>
        <input type="text" id="author" name="author" value="<?= e($manga['author'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="artist">Artist</label>
        <input type="text" id="artist" name="artist" value="<?= e($manga['artist'] ?? '') ?>">
      </div>
    </div>

    <div class="row2">
      <div class="form-group">
        <label for="rating">Reyting (0–10)</label>
        <input type="number" id="rating" name="rating" min="0" max="10" step="0.1"
               value="<?= e($manga['rating'] ?? '0') ?>">
      </div>
      <div class="form-group">
        <label for="year">Yil</label>
        <input type="number" id="year" name="year" min="1990" max="2030"
               value="<?= e($manga['year'] ?? date('Y')) ?>">
      </div>
    </div>

    <div class="row2">
      <div class="form-group">
        <label for="status">Holati</label>
        <select id="status" name="status">
          <?php foreach (['Ongoing','Completed','Hiatus'] as $s): ?>
            <option value="<?= $s ?>" <?= ($manga['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="views">Ko'rishlar soni</label>
        <input type="number" id="views" name="views" min="0"
               value="<?= e($manga['views'] ?? '0') ?>">
      </div>
    </div>

    <div class="form-group">
      <label for="cover">Muqova URL</label>
      <input type="text" id="cover" name="cover" placeholder="/covers/manga-slug.png"
             value="<?= e($manga['cover'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Janrlar</label>
      <div class="genres-grid">
        <?php foreach ($genres_list as $genre): ?>
          <label class="genre-label">
            <input type="checkbox" name="genres[]" value="<?= e($genre) ?>"
              <?= in_array($genre, $selected_genres ?? [], true) ? 'checked' : '' ?>>
            <?= e($genre) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-group">
      <label>Saralangan (Featured)</label>
      <div class="featured-toggle">
        <label class="switch">
          <input type="checkbox" name="is_featured" value="1"
            <?= !empty($manga['is_featured']) ? 'checked' : '' ?>>
          <span class="slider"></span>
        </label>
        <span style="font-size:.875rem;color:#8b949e">Bosh sahifada "Saralangan" blokida ko'rsatish</span>
      </div>
    </div>

    <div class="btn-bar">
      <button type="submit" class="btn-save">💾 Saqlash</button>
      <a href="/admin/dashboard.php" class="btn-cancel">Bekor qilish</a>
    </div>
  </form>
</main>
</body>
</html>
