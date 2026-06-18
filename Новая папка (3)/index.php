<?php
// index.php
// Bosh sahifa — Featured (Saralangan) bloki yuqorida, oddiy mangalar pastda

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

session_safe_start();

$featured = manga_get_featured();  // is_featured = 1
$regular  = manga_get_regular();   // is_featured = 0
$csrf     = csrf_token();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= SITE_NAME ?></title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #0d1117;
      color: #e6edf3;
      font-family: system-ui, sans-serif;
      min-height: 100vh;
    }

    /* ── Header ── */
    header {
      background: #161b22;
      border-bottom: 1px solid #30363d;
      position: sticky; top: 0; z-index: 100;
    }
    .header-inner {
      max-width: 1200px; margin: 0 auto;
      display: flex; align-items: center; gap: 1rem;
      padding: .9rem 1.5rem;
    }
    .brand { font-weight: 800; font-size: 1.2rem; text-decoration: none; color: #e6edf3; }
    .brand span { color: #58a6ff; }
    .nav-links { display: flex; gap: .25rem; margin-left: .5rem; }
    .nav-links a {
      color: #8b949e; text-decoration: none; font-size: .875rem;
      padding: .4rem .75rem; border-radius: 6px; transition: all .15s;
    }
    .nav-links a:hover { background: #21262d; color: #e6edf3; }
    .spacer { flex: 1; }
    .admin-link {
      background: #238636; color: #fff; text-decoration: none;
      padding: .4rem .9rem; border-radius: 6px; font-size: .825rem; font-weight: 600;
      transition: background .15s;
    }
    .admin-link:hover { background: #2ea043; }

    /* ── Main ── */
    main { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; }

    /* ── Section header ── */
    .section-header {
      display: flex; align-items: center; gap: .6rem;
      margin-bottom: 1.2rem;
    }
    .section-header h2 { font-size: 1.15rem; font-weight: 700; }
    .section-badge {
      background: #3a2f00; color: #e3b341;
      font-size: .7rem; font-weight: 700; padding: .2rem .6rem;
      border-radius: 20px; letter-spacing: .04em;
    }
    .section-divider {
      border: none; border-top: 1px solid #30363d; margin: 2.5rem 0;
    }

    /* ── Manga grid ── */
    .manga-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
      gap: 1rem;
    }

    /* ── Manga card ── */
    .manga-card {
      background: #161b22;
      border: 1px solid #30363d;
      border-radius: 10px;
      overflow: hidden;
      transition: border-color .2s, transform .2s;
      position: relative;
    }
    .manga-card:hover { border-color: #58a6ff; transform: translateY(-2px); }

    .card-cover {
      position: relative;
      aspect-ratio: 2/3;
      overflow: hidden;
    }
    .card-cover img {
      width: 100%; height: 100%; object-fit: cover;
      transition: transform .4s;
    }
    .manga-card:hover .card-cover img { transform: scale(1.05); }
    .card-cover-placeholder {
      width: 100%; height: 100%;
      background: #21262d;
      display: flex; align-items: center; justify-content: center;
      color: #8b949e; font-size: 2rem;
    }

    .card-rating {
      position: absolute; top: .4rem; left: .4rem;
      background: rgba(13,17,23,.85); backdrop-filter: blur(4px);
      color: #e3b341; font-size: .7rem; font-weight: 700;
      padding: .2rem .5rem; border-radius: 5px;
    }
    .card-status {
      position: absolute; top: .4rem; right: .4rem;
      background: #238636; color: #fff; font-size: .65rem; font-weight: 700;
      padding: .15rem .45rem; border-radius: 4px; text-transform: uppercase;
    }
    .card-status.hiatus { background: #b91c1c; }
    .card-status.completed { background: #1d4ed8; }

    .featured-star {
      position: absolute; bottom: .4rem; left: .4rem;
      background: rgba(227,179,65,.15); border: 1px solid #e3b341;
      color: #e3b341; font-size: .65rem; font-weight: 700;
      padding: .15rem .45rem; border-radius: 4px;
    }

    .card-body { padding: .7rem; }
    .card-title {
      font-size: .825rem; font-weight: 700; line-height: 1.3;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
      overflow: hidden; margin-bottom: .4rem;
    }
    .card-meta {
      font-size: .72rem; color: #8b949e;
      display: flex; gap: .5rem; flex-wrap: wrap;
    }

    /* ── Admin overlay (faqat admin ko'radi) ── */
    .admin-overlay {
      position: absolute; inset: 0;
      background: rgba(13,17,23,.7);
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      gap: .4rem;
      opacity: 0;
      transition: opacity .2s;
      z-index: 10;
    }
    .manga-card:hover .admin-overlay { opacity: 1; }
    .adm-btn {
      width: 80%; padding: .4rem .6rem;
      border-radius: 6px; font-size: .775rem; font-weight: 600;
      border: none; cursor: pointer; transition: background .15s;
      text-align: center; text-decoration: none;
      display: block;
    }
    .adm-edit   { background: #1a2b4a; color: #58a6ff; }
    .adm-edit:hover { background: #1f3a6e; }
    .adm-delete { background: #3d1a1a; color: #f85149; }
    .adm-delete:hover { background: #5a2020; }
    .adm-pin    { background: #3a2f00; color: #e3b341; }
    .adm-pin:hover { background: #524200; }
    .adm-unpin  { background: #1a2b1a; color: #3fb950; }
    .adm-unpin:hover { background: #1e3a20; }

    /* ── Toast ── */
    #toast {
      position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
      background: #1f6feb; color: #fff; padding: .8rem 1.3rem;
      border-radius: 10px; font-size: .875rem; display: none;
      box-shadow: 0 8px 24px rgba(0,0,0,.4); max-width: 340px;
    }
    #toast.show { display: block; }
    #toast.error { background: #b91c1c; }

    /* ── Empty state ── */
    .empty { color: #8b949e; padding: 2rem; text-align: center; font-size: .9rem; }

    /* ── Footer ── */
    footer {
      text-align: center; padding: 2rem;
      color: #8b949e; font-size: .8rem;
      border-top: 1px solid #30363d; margin-top: 3rem;
    }

    @media (max-width: 600px) {
      .nav-links { display: none; }
      .manga-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
    }
  </style>
</head>
<body>
<div id="toast"></div>

<!-- Header -->
<header>
  <div class="header-inner">
    <a href="/" class="brand">Akteynt<span>MangaUz</span></a>
    <nav class="nav-links">
      <a href="#featured">Saralangan</a>
      <a href="#all">Barcha mangalar</a>
    </nav>
    <div class="spacer"></div>
    <?php if (is_admin()): ?>
      <a href="/admin/dashboard.php" class="admin-link">⚙️ Admin Panel</a>
    <?php endif; ?>
  </div>
</header>

<main>

  <!-- ══ SARALANGAN (FEATURED) BLOKI ══ -->
  <?php if (!empty($featured)): ?>
  <section id="featured">
    <div class="section-header">
      <h2>⭐ Saralangan Mangalar</h2>
      <span class="section-badge">FEATURED</span>
    </div>
    <div class="manga-grid">
      <?php foreach ($featured as $m): ?>
        <?php render_manga_card($m, is_admin(), $csrf); ?>
      <?php endforeach; ?>
    </div>
  </section>
  <hr class="section-divider">
  <?php endif; ?>

  <!-- ══ BARCHA MANGALAR ══ -->
  <section id="all">
    <div class="section-header">
      <h2>🔥 Barcha Mangalar</h2>
    </div>
    <?php if (!empty($regular)): ?>
    <div class="manga-grid">
      <?php foreach ($regular as $m): ?>
        <?php render_manga_card($m, is_admin(), $csrf); ?>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p class="empty">Hali manga qo'shilmagan.</p>
    <?php endif; ?>
  </section>

</main>

<footer>
  &copy; <?= date('Y') ?> <?= SITE_NAME ?> — Barcha huquqlar himoyalangan.
</footer>

<?php if (is_admin()): ?>
<script>
const CSRF = <?= json_encode($csrf) ?>;

function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show' + (isError ? ' error' : '');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.className = '', 3500);
}

async function adminAction(action, id) {
  const fd = new FormData();
  fd.append('action', action);
  fd.append('manga_id', id);
  fd.append('csrf_token', CSRF);
  const res = await fetch('/admin/admin_actions.php', { method: 'POST', body: fd });
  return res.json();
}

async function deleteManga(id, title) {
  if (!confirm(`"${title}" ni o'chirishni tasdiqlaysizmi?\n\nBu amalni qaytarib bo'lmaydi!`)) return;
  try {
    const data = await adminAction('delete', id);
    if (data.success) {
      document.getElementById('card-' + id)?.remove();
      showToast(data.message);
    } else showToast(data.message, true);
  } catch { showToast('Server xatosi.', true); }
}

async function toggleFeatured(id) {
  try {
    const data = await adminAction('toggle_featured', id);
    if (data.success) {
      showToast(data.message);
      // Sahifani qayta yuklash — Featured blokni yangilash uchun
      setTimeout(() => location.reload(), 900);
    } else showToast(data.message, true);
  } catch { showToast('Server xatosi.', true); }
}
</script>
<?php endif; ?>
</body>
</html>

<?php
// ──────────────────────────────────────────────
//  Manga kartasini chiqarish funksiyasi
// ──────────────────────────────────────────────
function render_manga_card(array $m, bool $admin, string $csrf): void
{
    $statusClass = match($m['status']) {
        'Completed' => 'completed',
        'Hiatus'    => 'hiatus',
        default     => '',
    };
    $pinLabel = $m['is_featured'] ? 'Unpin' : '⭐ Pin';
    $pinClass = $m['is_featured'] ? 'adm-unpin' : 'adm-pin';
    $id       = (int) $m['id'];
    $title    = htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8');
?>
<div class="manga-card" id="card-<?= $id ?>">
  <div class="card-cover">
    <?php if (!empty($m['cover'])): ?>
      <img src="<?= e($m['cover']) ?>" alt="<?= $title ?>">
    <?php else: ?>
      <div class="card-cover-placeholder">📖</div>
    <?php endif; ?>

    <span class="card-rating">⭐ <?= number_format((float)$m['rating'], 1) ?></span>
    <span class="card-status <?= $statusClass ?>"><?= e($m['status']) ?></span>
    <?php if ($m['is_featured']): ?>
      <span class="featured-star">⭐ Featured</span>
    <?php endif; ?>

    <?php if ($admin): ?>
    <div class="admin-overlay">
      <a href="/admin/edit.php?id=<?= $id ?>" class="adm-btn adm-edit">✏️ Tahrirlash</a>
      <button class="adm-btn adm-delete"
        onclick="deleteManga(<?= $id ?>, '<?= addslashes($title) ?>')">
        🗑 O'chirish
      </button>
      <button class="adm-btn <?= $pinClass ?>"
        onclick="toggleFeatured(<?= $id ?>)">
        <?= $pinLabel ?>
      </button>
    </div>
    <?php endif; ?>
  </div>

  <div class="card-body">
    <div class="card-title"><?= $title ?></div>
    <div class="card-meta">
      <span><?= (int)$m['year'] ?></span>
      <span>👁 <?= format_views((int)$m['views']) ?></span>
    </div>
  </div>
</div>
<?php
}
?>
