<?php
// admin/dashboard.php
// Admin boshqaruv paneli — barcha mangalar ro'yxati, CRUD tugmalari

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(); // Admin emas bo'lsa login ga yo'naltiradi

$page   = max(1, (int) ($_GET['page'] ?? 1));
$mangas = manga_get_all($page);
$total  = manga_count();
$pages  = (int) ceil($total / ITEMS_PER_PAGE);

$message = $_GET['msg'] ?? '';
$csrf    = csrf_token();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard — Akteynt MangaUz</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background:#0d1117; color:#e6edf3; font-family:system-ui,sans-serif; min-height:100vh; }

    /* Navbar */
    nav {
      background:#161b22; border-bottom:1px solid #30363d;
      display:flex; align-items:center; gap:1rem;
      padding:.85rem 1.5rem;
    }
    nav .brand { font-weight:800; font-size:1.1rem; }
    nav .brand span { color:#58a6ff; }
    nav .spacer { flex:1; }
    nav a, nav form button {
      color:#8b949e; text-decoration:none; font-size:.875rem;
      background:none; border:none; cursor:pointer; padding:.4rem .8rem;
      border-radius:6px; transition:background .15s, color .15s;
    }
    nav a:hover, nav form button:hover { background:#21262d; color:#e6edf3; }
    nav .btn-add {
      background:#238636; color:#fff; font-weight:600;
      padding:.45rem 1rem; border-radius:6px; text-decoration:none;
      transition:background .15s;
    }
    nav .btn-add:hover { background:#2ea043; }

    /* Toast */
    #toast {
      position:fixed; top:1.2rem; right:1.2rem; z-index:9999;
      background:#1f6feb; color:#fff; padding:.8rem 1.3rem;
      border-radius:10px; font-size:.875rem; display:none;
      box-shadow:0 8px 24px rgba(0,0,0,.4);
      max-width:360px;
    }
    #toast.show { display:block; }
    #toast.error { background:#b91c1c; }

    /* Main layout */
    main { max-width:1100px; margin:0 auto; padding:1.5rem 1rem; }
    h1 { font-size:1.3rem; font-weight:700; margin-bottom:1rem; }

    /* Stats bar */
    .stats {
      display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap;
    }
    .stat-card {
      background:#161b22; border:1px solid #30363d; border-radius:10px;
      padding:.9rem 1.4rem; flex:1; min-width:130px;
    }
    .stat-card small { color:#8b949e; font-size:.75rem; display:block; margin-bottom:.25rem; }
    .stat-card strong { font-size:1.5rem; font-weight:800; color:#58a6ff; }

    /* Table */
    .table-wrap { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; font-size:.875rem; }
    th {
      text-align:left; padding:.7rem .9rem;
      color:#8b949e; font-weight:600; border-bottom:1px solid #30363d;
      white-space:nowrap;
    }
    td { padding:.7rem .9rem; border-bottom:1px solid #21262d; vertical-align:middle; }
    tr:hover td { background:#161b22; }

    .cover-img {
      width:38px; height:54px; object-fit:cover;
      border-radius:5px; border:1px solid #30363d;
    }
    .badge {
      display:inline-block; padding:.2rem .55rem;
      border-radius:20px; font-size:.7rem; font-weight:700;
    }
    .badge-ongoing  { background:#0d3b20; color:#3fb950; }
    .badge-completed{ background:#1a2b4a; color:#58a6ff; }
    .badge-hiatus   { background:#3a2020; color:#f85149; }
    .badge-featured { background:#3a2f00; color:#e3b341; }

    /* Action buttons */
    .actions { display:flex; gap:.4rem; flex-wrap:wrap; }
    .btn {
      padding:.32rem .75rem; border-radius:6px; font-size:.78rem;
      font-weight:600; cursor:pointer; border:1px solid transparent;
      text-decoration:none; transition:all .15s;
      white-space:nowrap;
    }
    .btn-edit   { background:#1a2b4a; color:#58a6ff; border-color:#1f4080; }
    .btn-edit:hover { background:#1f3a6e; }
    .btn-delete { background:#3d1a1a; color:#f85149; border-color:#6e2020; }
    .btn-delete:hover { background:#5a2020; }
    .btn-pin    { background:#3a2f00; color:#e3b341; border-color:#6e5500; }
    .btn-pin:hover { background:#524200; }
    .btn-unpin  { background:#1a2b1a; color:#3fb950; border-color:#1f6020; }
    .btn-unpin:hover { background:#1e3a20; }

    /* Pagination */
    .pagination { display:flex; gap:.4rem; margin-top:1.5rem; flex-wrap:wrap; }
    .pagination a, .pagination span {
      padding:.4rem .85rem; border-radius:6px; font-size:.85rem;
      background:#161b22; border:1px solid #30363d; text-decoration:none; color:#e6edf3;
    }
    .pagination .active { background:#1f6feb; border-color:#1f6feb; color:#fff; }
    .pagination a:hover  { background:#21262d; }

    /* Responsive */
    @media (max-width:640px) {
      .stat-card { min-width:100px; }
      td, th { padding:.5rem .6rem; }
    }
  </style>
</head>
<body>

<div id="toast"></div>

<!-- Navbar -->
<nav>
  <div class="brand">Akteynt<span>MangaUz</span></div>
  <div class="spacer"></div>
  <a href="/">Saytga o'tish</a>
  <a href="/admin/edit.php">+ Yangi manga</a>
  <form method="POST" action="/admin/logout.php" style="display:inline">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <button type="submit">Chiqish</button>
  </form>
</nav>

<main>
  <h1>Manga boshqaruvi</h1>

  <!-- Statistika -->
  <div class="stats">
    <div class="stat-card">
      <small>Jami manga</small>
      <strong><?= $total ?></strong>
    </div>
    <div class="stat-card">
      <small>Saralangan</small>
      <strong><?= count(manga_get_featured()) ?></strong>
    </div>
    <div class="stat-card">
      <small>Admin</small>
      <strong style="font-size:1rem;color:#e6edf3"><?= e($_SESSION['admin_username'] ?? '') ?></strong>
    </div>
  </div>

  <!-- Jadval -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Muqova</th>
          <th>Sarlavha</th>
          <th>Holati</th>
          <th>Reyting</th>
          <th>Ko'rishlar</th>
          <th>Featured</th>
          <th>Amallar</th>
        </tr>
      </thead>
      <tbody id="manga-tbody">
      <?php foreach ($mangas as $i => $m): ?>
        <?php
          $statusClass = match($m['status']) {
            'Ongoing'   => 'badge-ongoing',
            'Completed' => 'badge-completed',
            default     => 'badge-hiatus',
          };
          $pinLabel  = $m['is_featured'] ? 'Unpin' : 'Pin ⭐';
          $pinClass  = $m['is_featured'] ? 'btn-unpin' : 'btn-pin';
        ?>
        <tr id="row-<?= $m['id'] ?>">
          <td><?= ($page - 1) * ITEMS_PER_PAGE + $i + 1 ?></td>
          <td>
            <?php if ($m['cover']): ?>
              <img src="<?= e($m['cover']) ?>" alt="" class="cover-img">
            <?php else: ?>
              <div class="cover-img" style="background:#21262d;display:flex;align-items:center;justify-content:center;color:#8b949e;font-size:.6rem;">N/A</div>
            <?php endif; ?>
          </td>
          <td>
            <strong><?= e($m['title']) ?></strong>
            <?php if ($m['alt_title']): ?>
              <br><small style="color:#8b949e"><?= e($m['alt_title']) ?></small>
            <?php endif; ?>
          </td>
          <td><span class="badge <?= $statusClass ?>"><?= e($m['status']) ?></span></td>
          <td><?= number_format((float)$m['rating'], 1) ?></td>
          <td><?= format_views((int)$m['views']) ?></td>
          <td>
            <?php if ($m['is_featured']): ?>
              <span class="badge badge-featured">⭐ Saralangan</span>
            <?php else: ?>
              <span style="color:#8b949e;font-size:.8rem;">—</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="actions">
              <a href="/admin/edit.php?id=<?= $m['id'] ?>" class="btn btn-edit">✏️ Tahrirlash</a>

              <button
                class="btn btn-delete"
                onclick="deleteManga(<?= $m['id'] ?>, '<?= e(addslashes($m['title'])) ?>')"
              >🗑 O'chirish</button>

              <button
                class="btn <?= $pinClass ?>"
                id="pin-btn-<?= $m['id'] ?>"
                onclick="toggleFeatured(<?= $m['id'] ?>)"
              ><?= $pinLabel ?></button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Sahifalash -->
  <?php if ($pages > 1): ?>
  <div class="pagination">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <?php if ($p === $page): ?>
        <span class="active"><?= $p ?></span>
      <?php else: ?>
        <a href="?page=<?= $p ?>"><?= $p ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</main>

<script>
const CSRF = <?= json_encode($csrf) ?>;

// ── Toast xabari ──────────────────────────────
function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show' + (isError ? ' error' : '');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.className = '', 3500);
}

// ── AJAX yordamchi ────────────────────────────
async function adminAction(action, mangaId) {
  const fd = new FormData();
  fd.append('action',     action);
  fd.append('manga_id',  mangaId);
  fd.append('csrf_token', CSRF);

  const res  = await fetch('/admin/admin_actions.php', { method: 'POST', body: fd });
  const data = await res.json();
  return data;
}

// ── O'chirish ─────────────────────────────────
async function deleteManga(id, title) {
  // JavaScript confirm() — o'chirishdan oldin tasdiqlash
  const confirmed = confirm(`⚠️ "${title}" mangasini o'chirishni tasdiqlaysizmi?\n\nBu amalni qaytarib bo'lmaydi!`);
  if (!confirmed) return;

  try {
    const data = await adminAction('delete', id);
    if (data.success) {
      document.getElementById('row-' + id)?.remove();
      showToast(data.message);
    } else {
      showToast(data.message, true);
    }
  } catch (err) {
    showToast('Server bilan muammo yuz berdi.', true);
  }
}

// ── Pin / Unpin ───────────────────────────────
async function toggleFeatured(id) {
  try {
    const data = await adminAction('toggle_featured', id);
    if (data.success) {
      const btn = document.getElementById('pin-btn-' + id);
      const row = document.getElementById('row-' + id);

      // Tugmani yangilash
      if (data.is_featured === 1) {
        btn.textContent = 'Unpin';
        btn.className = 'btn btn-unpin';
        // Featured badge ni qo'shish
        row.cells[6].innerHTML = '<span class="badge badge-featured">⭐ Saralangan</span>';
      } else {
        btn.textContent = 'Pin ⭐';
        btn.className = 'btn btn-pin';
        row.cells[6].innerHTML = '<span style="color:#8b949e;font-size:.8rem;">—</span>';
      }

      showToast(data.message);
    } else {
      showToast(data.message, true);
    }
  } catch (err) {
    showToast('Server bilan muammo yuz berdi.', true);
  }
}
</script>
</body>
</html>
