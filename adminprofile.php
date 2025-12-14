<?php
session_start();
require __DIR__ . '/db.php';

/* =========================
   ADMIN GUARD
   ========================= */
$role = $_SESSION['role'] ?? '';
$adminId = (int)($_SESSION['admin_id'] ?? 0);
if ($adminId <= 0) $adminId = (int)($_SESSION['user_id'] ?? 0);

if ($adminId <= 0 || $role !== 'admin') {
  header("Location: login.php"); // عدّليها حسب اللوجين عندك
  exit;
}

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function firstLetter($s){
  $s = trim((string)$s);
  if ($s === '') return 'A';
  return strtoupper(mb_substr($s, 0, 1, 'UTF-8'));
}

/* =========================
   LOAD ADMIN
   ========================= */
$stmt = $pdo->prepare("SELECT id, first_name, last_name, display_name, email, avatar_url, is_super, is_active, created_at
                       FROM admins WHERE id=? LIMIT 1");
$stmt->execute([$adminId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
  session_destroy();
  header("Location: login.php");
  exit;
}

$fullName  = trim(($admin['first_name'] ?? '').' '.($admin['last_name'] ?? ''));
$shownName = trim((string)($admin['display_name'] ?? '')) !== '' ? (string)$admin['display_name'] : $fullName;
$letter    = firstLetter($shownName);
$avatar    = trim((string)($admin['avatar_url'] ?? ''));

$isSuper   = (int)($admin['is_super'] ?? 0);
$isActive  = (int)($admin['is_active'] ?? 0);
$createdAt = $admin['created_at'] ?? '';
$since     = $createdAt ? date('Y-m-d', strtotime($createdAt)) : date('Y-m-d');
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Travelo · Admin Profile</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
   <script src='https://cdn.jotfor.ms/agent/embedjs/019b189a507c7f0e98a0580ad136880f79ad/embed.js'>
</script>
  <!-- keep your dashboard.css (sidebar layout) -->
  <link rel="stylesheet" href="./assets/css/dashboard.css" />

<style>
  :root{
    /* Travelo */
    --accent:#872BFF;
    --accent2:#6C63FF;

    --bg:#F7F8FB;
    --ink:#0F172A;
    --muted:#64748B;

    --line:rgba(232,234,243,.95);
    --card:rgba(255,255,255,.92);

    --shadow:0 18px 55px rgba(15,23,42,.10);
    --shadow2:0 28px 90px rgba(15,23,42,.12);

    --r:22px;
    --r2:28px;
  }

  html.dark{
    --bg:#0b0e1a;
    --ink:#e9ecff;
    --muted:#aeb4d6;
    --line:rgba(36,41,73,.95);
    --card:rgba(15,18,34,.78);
    --shadow:0 20px 60px rgba(0,0,0,.35);
    --shadow2:0 34px 120px rgba(0,0,0,.55);
  }

  *{ box-sizing:border-box; }
  body{
    background:
      radial-gradient(circle at 10% 10%, rgba(135,43,255,.16), transparent 45%),
      radial-gradient(circle at 90% 15%, rgba(108,99,255,.14), transparent 45%),
      radial-gradient(circle at 60% 90%, rgba(135,43,255,.10), transparent 45%),
      var(--bg);
    color: var(--ink);
    font-family:"Plus Jakarta Sans",system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
  }

  /* =========================
     PROFILE LAYOUT (Scoped)
     ========================= */
  .profile-wrap{
    max-width: 1080px;
    margin: 0 auto;
    padding: 12px 18px 44px;
  }

  /* =========================
     HERO (like user profile)
     ========================= */
  .hero{
    border-radius: var(--r2);
    border: 1px solid var(--line);
    background: var(--card);
    box-shadow: var(--shadow2);
    overflow: hidden;
    backdrop-filter: blur(18px) saturate(170%);
  }

  /* softer cover + shapes (NOT too dark) */
  .cover{
    height: 200px;                 /* ✅ أكبر بس ناعم */
    position: relative;
    background:
      radial-gradient(circle at 20% 35%, rgba(135,43,255,.30), transparent 55%),
      radial-gradient(circle at 80% 30%, rgba(108,99,255,.26), transparent 55%),
      linear-gradient(135deg, rgba(135,43,255,.14) 0%, rgba(108,99,255,.12) 55%, rgba(255,255,255,.10) 100%);
  }
  .cover::before{
    content:"";
    position:absolute; inset:-80px -60px auto auto;
    width: 360px; height: 360px;
    background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.55), rgba(255,255,255,.12), transparent 70%);
    filter: blur(2px);
    transform: rotate(18deg);
    opacity:.75;
    pointer-events:none;
  }
  .cover::after{
    content:"";
    position:absolute; inset:0;
    background:
      linear-gradient(180deg, rgba(255,255,255,.25), transparent 45%, rgba(255,255,255,.30));
    pointer-events:none;
  }

  /* The header card that sits under the cover (fix overlap) */
  .identity{
    position: relative;
    z-index: 2;
    margin: -44px 18px 18px;       /* ✅ نفس user: avatar overlaps cover قليلاً */
    padding: 18px 18px;
    border-radius: calc(var(--r2) - 4px);
    border: 1px solid var(--line);
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(18px) saturate(170%);
    box-shadow: var(--shadow);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 18px;
    flex-wrap: wrap;
  }
  html.dark .identity{ background: rgba(15,18,34,.72); }

  .id-left{
    display:flex;
    align-items:center;
    gap: 14px;
    min-width: 0;
    flex: 1;
  }

  .avatar-big{
    width: 84px; height: 84px;
    border-radius: 999px;
    overflow:hidden;
    position:relative;
    flex: 0 0 auto;
    background: rgba(255,255,255,.95);
    border: 6px solid rgba(255,255,255,.95);
    box-shadow: 0 18px 46px rgba(15,23,42,.14);
  }
  html.dark .avatar-big{
    background: rgba(15,18,34,.92);
    border-color: rgba(15,18,34,.92);
  }
  .avatar-big img{ width:100%; height:100%; object-fit:cover; display:block; }
  .avatar-big .letter{
    width:100%; height:100%;
    display:grid; place-items:center;
    color:#fff;
    font-weight: 950;
    font-size: 26px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
  }

  /* camera button */
  .avatar-big .edit{
    position:absolute;
    right: 6px; bottom: 6px;
    width: 32px; height: 32px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.55);
    background: rgba(15,23,42,.40);
    color:#fff;
    display:grid; place-items:center;
    cursor:pointer;
    transition: .18s ease;
    backdrop-filter: blur(10px);
  }
  .avatar-big .edit:hover{
    transform: translateY(-1px);
    background: rgba(135,43,255,.55);
  }

  .idtext{
    min-width: 280px;
    flex: 1;
    min-width: 0;
  }

  .name{
    margin: 0;
    font-size: 22px;              /* ✅ واضح ومش مغطّى */
    font-weight: 950;
    letter-spacing: -.03em;
    line-height: 1.12;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--ink);
  }
  .email{
    margin: 6px 0 0;
    font-weight: 750;
    color: var(--muted);
    white-space: nowrap;
    overflow:hidden;
    text-overflow: ellipsis;
  }

  /* chips like user */
  .chips{
    display:flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 12px;
  }
  .chip{
    display:inline-flex;
    align-items:center;
    gap: 8px;
    padding: 9px 12px;
    border-radius: 999px;
    border: 1px solid var(--line);
    background: rgba(135,43,255,.06);
    color: var(--ink);
    font-weight: 850;
    font-size: 13px;
  }
  html.dark .chip{ background: rgba(135,43,255,.12); }
  .chip i{ color: var(--accent); }

  .chip--ok{
    background: rgba(22,163,74,.08);
    border-color: rgba(22,163,74,.22);
    color: #15803d;
  }
  .chip--ok i{ color:#16a34a; }

  .chip--off{
    background: rgba(239,68,68,.08);
    border-color: rgba(239,68,68,.22);
    color: #b91c1c;
  }
  .chip--off i{ color:#ef4444; }

  .actions{
    display:flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items:center;
  }

  /* buttons like user change buttons */
  .btn-soft{
    border: 1px solid var(--line);
    background: rgba(255,255,255,.92);
    color: var(--ink);
    border-radius: 999px;
    padding: 10px 14px;
    font-weight: 900;
    display:inline-flex;
    align-items:center;
    gap: 10px;
    cursor:pointer;
    transition: .18s ease;
    box-shadow: 0 10px 24px rgba(15,23,42,.06);
    white-space: nowrap;
  }
  html.dark .btn-soft{ background: rgba(15,18,34,.62); box-shadow: 0 14px 30px rgba(0,0,0,.28); }
  .btn-soft:hover{
    transform: translateY(-1px);
    border-color: rgba(135,43,255,.28);
    background: rgba(135,43,255,.06);
    color: var(--accent);
  }

  .btn-primaryx{
    border: 0;
    color:#fff;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    box-shadow: 0 16px 34px rgba(135,43,255,.22);
  }
  .btn-primaryx:hover{ filter: brightness(1.02); }

  /* =========================
     ACCOUNT PANEL (like user)
     ========================= */
  .panel{
    margin-top: 18px;
    border-radius: var(--r2);
    border: 1px solid var(--line);
    background: var(--card);
    box-shadow: var(--shadow2);
    overflow:hidden;
    backdrop-filter: blur(18px) saturate(170%);
    padding: 14px;
  }

  .rowline{
    display:flex;
    align-items:center;
    gap: 14px;
    padding: 16px 16px;
    border-radius: 18px;
    border: 1px solid rgba(232,234,243,.85);
    background:
      linear-gradient(90deg, rgba(135,43,255,.06), rgba(255,255,255,.72) 55%, rgba(255,255,255,.72));
    transition: .18s ease;
    margin-bottom: 12px;
  }
  html.dark .rowline{
    border-color: rgba(36,41,73,.95);
    background: linear-gradient(90deg, rgba(135,43,255,.14), rgba(15,18,34,.55));
  }
  .rowline:last-child{ margin-bottom: 0; }

  .rowline:hover{
    transform: translateY(-1px);
    border-color: rgba(135,43,255,.22);
    box-shadow: 0 14px 34px rgba(15,23,42,.08);
  }

  .meta{
    width: 160px;
    font-weight: 950;
    font-size: 12px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: color-mix(in srgb, var(--muted) 90%, transparent);
    flex: 0 0 auto;
  }

  .val{
    flex: 1;
    min-width: 0;
    font-weight: 950;
    font-size: 16px;
    color: var(--ink);
  }
  .sub{
    display:block;
    margin-top: 4px;
    font-size: 13px;
    font-weight: 750;
    color: var(--muted);
  }

  /* change button = same feel as user */
  .mini{
    border: 1px solid rgba(135,43,255,.22);
    background: rgba(135,43,255,.08);
    color: var(--accent);
    border-radius: 999px;
    padding: 10px 14px;
    font-weight: 950;
    display:inline-flex;
    align-items:center;
    gap: 10px;
    cursor:pointer;
    transition: .18s ease;
    white-space: nowrap;
    flex: 0 0 auto;
  }
  .mini:hover{
    transform: translateY(-1px);
    background: rgba(135,43,255,.12);
    border-color: rgba(135,43,255,.30);
  }

  .mini--danger{
    border-color: rgba(239,68,68,.22);
    background: rgba(239,68,68,.08);
    color: #ef4444;
  }
  .mini--danger:hover{
    background: rgba(239,68,68,.12);
    border-color: rgba(239,68,68,.30);
  }

  /* =========================
     MODALS (soft)
     ========================= */
  .modalx{ position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:9999; padding:18px; }
  .modalx.show{ display:flex; }
  .modalx .backdrop{ position:absolute; inset:0; background: rgba(15,23,42,.48); backdrop-filter: blur(12px); }
  .modalx .dialog{
    position:relative;
    width: min(560px, 100%);
    border-radius: 24px;
    border: 1px solid var(--line);
    background: rgba(255,255,255,.94);
    box-shadow: var(--shadow2);
    overflow:hidden;
    backdrop-filter: blur(18px) saturate(170%);
  }
  html.dark .modalx .dialog{ background: rgba(15,18,34,.82); }

  .modalx .head{
    padding: 16px 16px 12px;
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap: 12px;
    border-bottom: 1px solid var(--line);
  }
  .modalx .title{ margin:0; font-weight: 950; letter-spacing:-.02em; }
  .modalx .msub{ margin: 6px 0 0; color: var(--muted); font-weight: 750; font-size: 13px; }

  .iconbtn{
    width: 44px; height: 44px;
    border-radius: 16px;
    border: 1px solid var(--line);
    background: rgba(255,255,255,.65);
    display:grid; place-items:center;
    cursor:pointer;
    transition: .18s ease;
  }
  html.dark .iconbtn{ background: rgba(15,18,34,.55); }
  .iconbtn:hover{
    transform: translateY(-1px);
    border-color: rgba(135,43,255,.25);
    background: rgba(135,43,255,.10);
    color: var(--accent);
  }

  .modalx form{ padding: 14px 16px 16px; }
  .field{ margin-bottom: 12px; }
  .field label{
    display:block;
    font-weight: 950;
    font-size: 12px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 6px;
  }
  .field input{
    width:100%;
    padding: 12px 12px;
    border-radius: 16px;
    border: 1px solid var(--line);
    background: rgba(255,255,255,.70);
    color: var(--ink);
    font-weight: 850;
    outline:none;
    transition: .18s ease;
    backdrop-filter: blur(10px);
  }
  html.dark .field input{ background: rgba(15,18,34,.50); }
  .field input:focus{
    border-color: rgba(135,43,255,.35);
    box-shadow: 0 0 0 4px rgba(135,43,255,.14);
  }

  .foot{ display:flex; justify-content:flex-end; gap: 10px; padding-top: 6px; }

  /* toast */
  .toastx{
    position: fixed;
    left: 50%;
    bottom: 24px;
    transform: translateX(-50%);
    border-radius: 18px;
    border: 1px solid var(--line);
    background: rgba(255,255,255,.92);
    box-shadow: var(--shadow2);
    padding: 12px 14px;
    min-width: 300px;
    font-weight: 950;
    display:none;
    z-index: 10000;
    backdrop-filter: blur(18px) saturate(170%);
  }
  html.dark .toastx{ background: rgba(15,18,34,.82); }
  .toastx.show{ display:block; animation: pop .18s ease; }
  .toastx.ok{ border-color: rgba(22,163,74,.25); }
  .toastx.err{ border-color: rgba(239,68,68,.25); }

  @keyframes pop{
    from{ transform: translateX(-50%) translateY(10px); opacity:0; }
    to{ transform: translateX(-50%) translateY(0); opacity:1; }
  }

  /* responsive */
  @media (max-width: 860px){
    .cover{ height: 190px; }
    .identity{ margin: -40px 12px 12px; padding: 14px; }
    .idtext{ min-width: 0; }
    .actions{ width:100%; justify-content:flex-start; }
    .rowline{ flex-wrap: wrap; }
    .meta{ width: 100%; }
    .mini{ width: auto; }
  }
  /* =========================
   TOPBAR AVATAR (circle A)
   ========================= */

/* الزر نفسه */
.admin-badge{
  width: 44px;
  height: 44px;
  padding: 0;
  border: 0;
  border-radius: 999px;
  display: grid;
  place-items: center;
  cursor: pointer;
  background:
    radial-gradient(circle at 30% 25%, rgba(255,255,255,.55), transparent 55%),
    linear-gradient(135deg, var(--accent) 0%, var(--accent2) 55%, rgba(255,255,255,.2) 120%);
  box-shadow:
    0 14px 34px rgba(135,43,255,.24),
    0 0 0 1px rgba(135,43,255,.18) inset;
  transition: transform .18s ease, filter .18s ease, box-shadow .18s ease;
  position: relative;
  overflow: hidden;
}

.admin-badge::before{
  content:"";
  position:absolute;
  inset:-30%;
  background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.25), transparent 60%);
  transform: rotate(18deg);
  pointer-events:none;
}

.admin-badge:hover{
  transform: translateY(-1px);
  filter: brightness(1.02);
  box-shadow:
    0 18px 46px rgba(135,43,255,.28),
    0 0 0 1px rgba(135,43,255,.22) inset;
}

.admin-badge:focus{
  outline: none;
  box-shadow:
    0 0 0 4px rgba(135,43,255,.16),
    0 18px 46px rgba(135,43,255,.28);
}

/* إلغاء سهم bootstrap */
.admin-badge.dropdown-toggle::after{ display:none; }

/* الحرف جوّا الدائرة */
.admin-badge__letter{
  width: 36px;
  height: 36px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  color: #fff;
  font-weight: 950;
  font-size: 14px;
  letter-spacing: -.02em;
  background:
    radial-gradient(circle at 35% 30%, rgba(255,255,255,.22), transparent 55%),
    rgba(15,23,42,.18);
  box-shadow:
    0 10px 22px rgba(15,23,42,.14),
    0 0 0 1px rgba(255,255,255,.18) inset;
}

/* القائمة المنسدلة تبعتها */
.admin-menu{
  border-radius: 18px;
  border: 1px solid var(--line);
  background: rgba(255,255,255,.94);
  box-shadow: 0 26px 90px rgba(15,23,42,.18);
  padding: 8px;
  min-width: 220px;
  backdrop-filter: blur(18px) saturate(170%);
  overflow: hidden;
}
html.dark .admin-menu{ background: rgba(15,18,34,.82); }

.admin-menu .dropdown-item{
  border-radius: 14px;
  padding: 10px 12px;
  display:flex;
  align-items:center;
  gap:10px;
  font-weight: 900;
  color: var(--ink);
  transition: .16s ease;
}
.admin-menu .dropdown-item:hover{
  background: linear-gradient(90deg, rgba(135,43,255,.12), transparent);
  color: var(--accent);
  transform: translateX(2px);
}
.admin-menu .dropdown-item i{
  color: var(--muted);
  transition: .16s ease;
}
.admin-menu .dropdown-item:hover i{
  color: var(--accent);
  transform: scale(1.06);
}
.admin-menu .dropdown-divider{
  margin: 8px 6px;
  opacity: .6;
}

</style>


  <script>
    window.ADMIN = {
      id: <?= (int)$admin['id'] ?>,
      name: <?= json_encode($shownName) ?>,
      email: <?= json_encode($admin['email']) ?>
    };
  </script>
</head>
<body>

<div class="app">
  <!-- SIDEBAR (same as dashboard) -->
  <aside id="sidebar" class="sidebar">
    <div class="brand">
      <img class="logo" src="./assets/images/logo.svg" alt="Travelo logo" />
      Travelo Admin
    </div>

    <div class="mb-3">
      <a class="btn btn-outline-secondary d-lg-none w-100" href="#" onclick="toggleSidebar(event)">
        <i class="bi bi-layout-sidebar-inset"></i> Menu
      </a>
    </div>

    <nav class="nav flex-column gap-1" id="mainNav">
      <a class="nav-link" href="./dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      <a class="nav-link active" href="./adminprofile.php"><i class="bi bi-person me-2"></i>My Profile</a>
    </nav>

    <hr />
    <form action="./logout.php" method="post" class="px-2">
      <button class="btn btn-outline-secondary w-100" type="submit">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
      </button>
    </form>

    <div class="sub mt-2">© 2025 Travelo</div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <!-- TOPBAR -->
    <div class="topbar container-fluid rounded-4 p-3 mb-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary d-lg-none" onclick="toggleSidebar(event)">
          <i class="bi bi-list"></i>
        </button>
        <div class="page-title" id="pageTitle">Admin Profile</div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="input-group" style="max-width:360px;">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input id="globalSearch" class="form-control border-start-0" placeholder="Search (Ctrl + K)" />
        </div>

        <button id="themeToggle" class="btn btn-outline-secondary" title="Toggle theme">
          <i class="bi bi-moon-stars" id="themeIcon"></i>
        </button>

        <!-- Admin badge dropdown -->
        <div class="dropdown">
          <button class="admin-badge dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="<?= h($shownName) ?>">
            <span class="admin-badge__letter" id="topBadgeLetter"><?= h($letter) ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end admin-menu">
            <li>
              <a class="dropdown-item" href="./adminprofile.php">
                <i class="bi bi-person"></i> My profile
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form action="./logout.php" method="post" class="m-0">
                <button class="dropdown-item" type="submit">
                  <i class="bi bi-box-arrow-right"></i> Logout
                </button>
              </form>
            </li>
          </ul>
        </div>

      </div>
    </div>

    <!-- CONTENT -->
    <div class="profile-wrap">
      <section class="hero">
        <div class="cover"></div>

        <div class="identity">
          <div class="id-left">
            <div class="avatar-big" id="avatarBox">
              <?php if ($avatar !== ''): ?>
                <img id="avatarImg" src="<?= h($avatar) ?>" alt="Admin photo">
              <?php else: ?>
                <div class="letter" id="avatarLetter"><?= h($letter) ?></div>
              <?php endif; ?>
              <button class="edit" type="button" data-open="modalAvatar"><i class="bi bi-camera"></i></button>
            </div>

            <div class="idtext">
              <h1 class="name" id="displayName"><?= h($shownName) ?></h1>
              <p class="email" id="displayEmail"><?= h($admin['email']) ?></p>

              <div class="chips">
                <span class="chip"><i class="bi bi-shield-check"></i><?= $isSuper ? 'Super Admin' : 'Admin' ?></span>
                <span class="chip"><i class="bi bi-clock"></i>Member since <?= h($since) ?></span>
                <span class="chip <?= $isActive ? 'chip--ok' : 'chip--off' ?>" id="statusChip">
                  <i class="bi bi-activity"></i><?= $isActive ? 'Active' : 'Inactive' ?>
                </span>
              </div>
            </div>
          </div>

          <div class="actions">
            <button class="btn-soft btn-primaryx" type="button" data-open="modalEdit"><i class="bi bi-pencil-square"></i>Edit profile</button>
            <button class="btn-soft" type="button" data-open="modalPass"><i class="bi bi-key"></i>Change password</button>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="rowline">
          <div class="meta">First name</div>
          <div class="val" id="vFirst"><?= h($admin['first_name']) ?></div>
          <button class="mini" type="button" data-open="modalEdit"><i class="bi bi-pencil"></i> Change</button>
        </div>

        <div class="rowline">
          <div class="meta">Last name</div>
          <div class="val" id="vLast"><?= h($admin['last_name']) ?></div>
          <button class="mini" type="button" data-open="modalEdit"><i class="bi bi-pencil"></i> Change</button>
        </div>

        <div class="rowline">
          <div class="meta">Display name</div>
          <div class="val" id="vDisp"><?= h($admin['display_name'] ?? '') ?></div>
          <button class="mini" type="button" data-open="modalEdit"><i class="bi bi-pencil"></i> Change</button>
        </div>

        <div class="rowline">
          <div class="meta">Email</div>
          <div class="val" id="vEmail"><?= h($admin['email']) ?></div>
          <button class="mini" type="button" data-open="modalEdit"><i class="bi bi-pencil"></i> Change</button>
        </div>

        <div class="rowline">
          <div class="meta">Status</div>
          <div class="val">
            <span class="val" id="statusText"><?= $isActive ? 'Active' : 'Inactive' ?></span>
            <span class="sub">Admin can be deactivated</span>
          </div>
          <button class="mini mini--danger" type="button" id="btnToggleActive">
            <i class="bi bi-slash-circle"></i> Toggle
          </button>
        </div>
      </section>
    </div>
  </main>
</div>

<!-- ===== Edit Modal ===== -->
<div class="modalx" id="modalEdit" aria-hidden="true">
  <div class="backdrop" data-close="modalEdit"></div>
  <div class="dialog">
    <div class="head">
      <div>
        <h3 class="title m-0">Edit admin profile</h3>
        <p class="msub">Update your name and email.</p>
      </div>
      <button class="iconbtn" type="button" data-close="modalEdit"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="formEdit">
      <div class="field">
        <label>First name</label>
        <input id="inFirst" type="text" value="<?= h($admin['first_name']) ?>">
      </div>
      <div class="field">
        <label>Last name</label>
        <input id="inLast" type="text" value="<?= h($admin['last_name']) ?>">
      </div>
      <div class="field">
        <label>Display name</label>
        <input id="inDisp" type="text" value="<?= h($admin['display_name'] ?? '') ?>">
      </div>
      <div class="field">
        <label>Email</label>
        <input id="inEmail" type="email" value="<?= h($admin['email']) ?>">
      </div>

      <div class="foot">
        <button class="btn-soft" type="button" data-close="modalEdit">Cancel</button>
        <button class="btn-soft btn-primaryx" type="submit"><i class="bi bi-check2"></i> Save</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== Password Modal ===== -->
<div class="modalx" id="modalPass" aria-hidden="true">
  <div class="backdrop" data-close="modalPass"></div>
  <div class="dialog">
    <div class="head">
      <div>
        <h3 class="title m-0">Change password</h3>
        <p class="msub">Minimum 8 characters.</p>
      </div>
      <button class="iconbtn" type="button" data-close="modalPass"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="formPass">
      <div class="field">
        <label>Current password</label>
        <input id="oldPass" type="password" placeholder="••••••••">
      </div>
      <div class="field">
        <label>New password</label>
        <input id="newPass" type="password" placeholder="At least 8 characters">
      </div>
      <div class="field">
        <label>Confirm new password</label>
        <input id="newPass2" type="password" placeholder="Repeat password">
      </div>

      <div class="foot">
        <button class="btn-soft" type="button" data-close="modalPass">Cancel</button>
        <button class="btn-soft btn-primaryx" type="submit"><i class="bi bi-check2"></i> Update</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== Avatar Modal ===== -->
<div class="modalx" id="modalAvatar" aria-hidden="true">
  <div class="backdrop" data-close="modalAvatar"></div>
  <div class="dialog">
    <div class="head">
      <div>
        <h3 class="title m-0">Update profile photo</h3>
        <p class="msub">JPG/PNG/WEBP up to 2MB.</p>
      </div>
      <button class="iconbtn" type="button" data-close="modalAvatar"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="formAvatar">
      <div class="field">
        <label>Choose image</label>
        <input id="avatarFile" type="file" accept="image/*">
      </div>

      <div class="foot">
        <button class="btn-soft" type="button" data-close="modalAvatar">Cancel</button>
        <button class="btn-soft btn-primaryx" type="submit"><i class="bi bi-upload"></i> Upload</button>
      </div>
    </form>
  </div>
</div>

<div class="toastx" id="toast"></div>

<!-- scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  /* ==========================
     API endpoints (مطابقة لملفات API اللي اتفقنا عليهم)
     ========================== */
  const API = {
    updateProfile: "./API/admin_profile_update.php",
    changePassword: "./API/admin_password_change.php",
    uploadAvatar: "./API/admin_avatar_upload.php",
    toggleActive: "./API/admin_toggle_active.php"
  };

  /* ==========================
     UI Helpers
     ========================== */
  const $ = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));

  function firstLetterJS(s){
    s = String(s || "").trim();
    return s ? s[0].toUpperCase() : "A";
  }

  function toast(type, msg){
    const t = $("#toast");
    t.className = "toastx " + (type || "");
    t.textContent = msg;
    t.classList.add("show");
    clearTimeout(window.__toastTimer);
    window.__toastTimer = setTimeout(()=> t.classList.remove("show"), 2600);
  }

  function openModal(id){ $("#"+id).classList.add("show"); $("#"+id).setAttribute("aria-hidden","false"); }
  function closeModal(id){ $("#"+id).classList.remove("show"); $("#"+id).setAttribute("aria-hidden","true"); }

  $$("[data-open]").forEach(btn=>{
    btn.addEventListener("click", ()=> openModal(btn.getAttribute("data-open")));
  });
  $$("[data-close]").forEach(btn=>{
    btn.addEventListener("click", ()=> closeModal(btn.getAttribute("data-close")));
  });

  document.addEventListener("keydown", (e)=>{
    if (e.key === "Escape"){
      $$(".modalx.show").forEach(m => closeModal(m.id));
    }
  });

  /* ==========================
     Theme (same idea as dashboard)
     ========================== */
  (function initTheme(){
    const saved = localStorage.getItem("travelo-theme");
    if(saved === "dark") document.documentElement.classList.add("dark");
    updateThemeIcon();
  })();

  function updateThemeIcon(){
    const icon = $("#themeIcon");
    if(!icon) return;
    if(document.documentElement.classList.contains("dark")){
      icon.classList.remove("bi-moon-stars");
      icon.classList.add("bi-sun");
    }else{
      icon.classList.add("bi-moon-stars");
      icon.classList.remove("bi-sun");
    }
  }

  $("#themeToggle")?.addEventListener("click", ()=>{
    document.documentElement.classList.toggle("dark");
    localStorage.setItem("travelo-theme",
      document.documentElement.classList.contains("dark") ? "dark" : "light"
    );
    updateThemeIcon();
  });

  /* ==========================
     Fetch helpers
     ========================== */
  async function postJSON(url, data){
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data || {})
    });
    const j = await res.json().catch(()=> ({}));
    if(!res.ok || j.success === false) throw new Error(j.message || "Request failed");
    return j;
  }

  /* ==========================
     Update profile
     ========================== */
  $("#formEdit")?.addEventListener("submit", async (e)=>{
    e.preventDefault();
    const payload = {
      first_name: $("#inFirst").value.trim(),
      last_name: $("#inLast").value.trim(),
      display_name: $("#inDisp").value.trim(),
      email: $("#inEmail").value.trim()
    };

    try{
      const out = await postJSON(API.updateProfile, payload);

      $("#vFirst").textContent = payload.first_name;
      $("#vLast").textContent  = payload.last_name;
      $("#vDisp").textContent  = payload.display_name;
      $("#vEmail").textContent = payload.email;

      const newShown = payload.display_name || (payload.first_name + " " + payload.last_name);
      $("#displayName").textContent  = newShown;
      $("#displayEmail").textContent = payload.email;

      // Update top badge letter + avatar letter if no image
      const L = firstLetterJS(newShown);
      $("#topBadgeLetter").textContent = L;

      const avatarImg = $("#avatarImg");
      const avatarLetter = $("#avatarLetter");
      if(!avatarImg && avatarLetter){
        avatarLetter.textContent = L;
      }

      closeModal("modalEdit");
      toast("ok", out.message || "Profile updated");
    }catch(err){
      toast("err", err.message);
    }
  });

  /* ==========================
     Change password
     ========================== */
  $("#formPass")?.addEventListener("submit", async (e)=>{
    e.preventDefault();
    const oldp = $("#oldPass").value;
    const newp = $("#newPass").value;
    const newp2= $("#newPass2").value;

    if(newp.length < 8) return toast("err", "New password must be at least 8 characters");
    if(newp !== newp2) return toast("err", "Passwords do not match");

    try{
      const out = await postJSON(API.changePassword, { old_password: oldp, new_password: newp });
      $("#oldPass").value = $("#newPass").value = $("#newPass2").value = "";
      closeModal("modalPass");
      toast("ok", out.message || "Password updated");
    }catch(err){
      toast("err", err.message);
    }
  });

  /* ==========================
     Upload avatar
     ========================== */
  $("#formAvatar")?.addEventListener("submit", async (e)=>{
    e.preventDefault();
    const file = $("#avatarFile").files?.[0];
    if(!file) return toast("err", "Choose an image first");

    const fd = new FormData();
    fd.append("avatar", file);

    try{
      const res = await fetch(API.uploadAvatar, { method:"POST", body: fd });
      const out = await res.json().catch(()=> ({}));
      if(!res.ok || out.success === false) throw new Error(out.message || "Upload failed");

      const url = out.avatar_url || out.url;
      if(url){
        const box = $("#avatarBox");
        box.querySelector("#avatarLetter")?.remove();

        let img = box.querySelector("#avatarImg");
        if(!img){
          img = document.createElement("img");
          img.id = "avatarImg";
          img.alt = "Admin photo";
          box.insertBefore(img, box.firstChild);
        }
        img.src = url + (url.includes("?") ? "&" : "?") + "t=" + Date.now();
      }

      $("#avatarFile").value = "";
      closeModal("modalAvatar");
      toast("ok", out.message || "Photo updated");
    }catch(err){
      toast("err", err.message);
    }
  });

  /* ==========================
     Toggle active
     ========================== */
  $("#btnToggleActive")?.addEventListener("click", async ()=>{
    try{
      const out = await postJSON(API.toggleActive, {});
      const active = !!out.is_active;

      $("#statusText").textContent = active ? "Active" : "Inactive";

      const chip = $("#statusChip");
      chip.classList.toggle("chip--ok", active);
      chip.classList.toggle("chip--off", !active);
      chip.innerHTML = `<i class="bi bi-activity"></i>${active ? "Active" : "Inactive"}`;

      toast("ok", out.message || "Status updated");
    }catch(err){
      toast("err", err.message);
    }
  });

  /* Sidebar toggler (fallback) */
  function toggleSidebar(e){
    e && e.preventDefault();
    document.getElementById("sidebar")?.classList.toggle("open");
  }
  window.toggleSidebar = toggleSidebar;
</script>

</body>
</html>
