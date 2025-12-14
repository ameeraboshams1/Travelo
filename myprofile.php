<?php
session_start();

/* ================== CONFIG ================== */
$host     = 'localhost';
$dbname   = 'travelo';
$username = 'root';
$password = '';

$loginUrl = 'login.html'; // عدّليها لو عندك login.php

if (!isset($_SESSION['user_id'])) {
  header("Location: $loginUrl");
  exit;
}

/* ================== DB CONNECTION (PDO) ================== */
try {
  $pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $username,
    $password,
    [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (PDOException $e) {
  die('DB error: ' . htmlspecialchars($e->getMessage()));
}

/* ================== FETCH USER ================== */
$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, first_name, last_name, username, email, birth_date, is_active, created_at
                       FROM users
                       WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
  session_destroy();
  header("Location: $loginUrl");
  exit;
}

if ((int)$user['is_active'] !== 1) {
  session_destroy();
  header("Location: $loginUrl");
  exit;
}

/* ================== HELPERS ================== */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
if ($fullName === '') $fullName = $user['username'] ?? 'Traveler';

$avatarBase = $user['first_name'] ?: ($user['username'] ?: 'U');
$avatarLetter = strtoupper(mb_substr($avatarBase, 0, 1));

$memberSince = $user['created_at'] ? substr((string)$user['created_at'], 0, 10) : '';
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Travelo · My Profile</title>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link rel="stylesheet" href="./assets/css/home.css">
  <link rel="stylesheet" href="./assets/css/profile.css">

  <!-- avatar letter small css (بدون صورة) -->
  <style>
    :root{
      --ink:#0f172a;
      --muted:#64748b;
      --border:#e8eaf3;
      --bg:#f7f8fb;
      --accent:#7c3aed;
      --accent2:#6c63ff;
      --shadow: 0 14px 36px rgba(15,23,42,.10);
      --radius: 16px;
    }

    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family:"Plus Jakarta Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: var(--bg);
      color: var(--ink);
    }

    /* ===== Demo Header layout ===== */
    .topbar{
      position: sticky;
      top: 0;
      z-index: 999;
      background: rgba(255,255,255,.86);
      backdrop-filter: blur(14px) saturate(160%);
      border-bottom: 1px solid rgba(232,234,243,.9);
    }
    .topbar .wrap{
      max-width: 1100px;
      margin: 0 auto;
      padding: 14px 18px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 14px;
    }
    .brand{
      display:flex;
      align-items:center;
      gap:10px;
      font-weight:800;
      letter-spacing:-.02em;
      color: var(--ink);
      text-decoration:none;
    }
    .brand .logo{
      width:34px;height:34px;border-radius:12px;
      background: radial-gradient(circle at 30% 20%, rgba(124,58,237,.35), transparent 55%),
                  linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      box-shadow: 0 10px 22px rgba(124,58,237,.18);
    }

    .nav-actions{
      display:flex;
      align-items:center;
      gap: 10px;
    }

    /* ===== Guest buttons ===== */
    .sign_in, .sign_up{
      border: 0;
      outline: none;
      cursor: pointer;
      padding: 10px 14px;
      border-radius: 999px;
      font-weight: 700;
      font-size: 14px;
      transition: .2s ease;
      font-family:"Plus Jakarta Sans", system-ui, sans-serif;
    }
    .sign_in{
      background: rgba(255,255,255,.95);
      box-shadow: 0 8px 22px rgba(15,23,42,.08), 0 0 0 1px rgba(232,234,243,.9) inset;
      color: var(--ink);
    }
    .sign_in:hover{ transform: translateY(-1px); box-shadow: 0 14px 30px rgba(15,23,42,.12); }
    .sign_up{
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent2) 100%);
      color: #fff;
      box-shadow: 0 14px 30px rgba(124,58,237,.22);
    }
    .sign_up:hover{ transform: translateY(-1px); filter: brightness(1.02); }

    /* ===== Travelo User Chip (Premium Design) ===== */
    .nav-user{
      position: relative;
      display: flex;
      align-items: center;
      gap: 8px;
      z-index: 100;
    }

    /* الزر الرئيسي */
    .nav-button .user-toggle{
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 5px 14px 5px 8px;
      border-radius: 999px;
      border: none;
      outline: none;
      background: rgba(255, 255, 255, 0.92);
      cursor: pointer;
      font-family: "Plus Jakarta Sans", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
      font-size: 14px;
      font-weight: 700;
      color: #0f172a;
      box-shadow:
        0 4px 12px rgba(15, 23, 42, 0.08),
        0 0 0 1px rgba(255, 255, 255, 0.3) inset;
      backdrop-filter: blur(12px) saturate(180%);
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      user-select: none;
    }

    /* خط لمعان خفيف */
    .nav-button .user-toggle::before{
      content:'';
      position:absolute;
      top:0; left:0; right:0;
      height:1px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,.65), transparent);
    }

    .nav-button .user-toggle:hover{
      transform: translateY(-1.5px);
      box-shadow:
        0 12px 28px rgba(15, 23, 42, 0.14),
        0 0 0 1px rgba(255, 255, 255, 0.4) inset;
      background: rgba(255, 255, 255, 0.98);
    }
    .nav-button .user-toggle:active{ transform: translateY(0); transition-duration:.1s; }

    /* الأفاتار */
    .user-avatar{
      width: 32px;
      height: 32px;
      border-radius: 999px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 14px;
      box-shadow: 0 3px 8px rgba(102, 126, 234, 0.3);
      position: relative;
      overflow: hidden;
      transition: transform 0.3s ease;
      flex: 0 0 auto;
    }

    .user-avatar::after{
      content:'';
      position:absolute;
      inset:0;
      background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.22), transparent);
      transform: translateX(-110%);
    }
    .nav-button .user-toggle:hover .user-avatar{ transform: scale(1.05) rotate(5deg); }
    .nav-button .user-toggle:hover .user-avatar::after{ animation: shimmer 1.35s infinite; }

    @keyframes shimmer { 100% { transform: translateX(110%); } }

    /* النص */
    .user-text{
      white-space: nowrap;
      color: #0f172a;
      font-weight: 700;
      font-size: 14px;
      letter-spacing: -0.01em;
      position: relative;
    }

    /* السهم (لازم يكون موجود بالـ HTML) */
    .user-caret{
      font-size: 12px;
      color: #94a3b8;
      transition: transform 0.25s ease;
      margin-left: 2px;
      flex: 0 0 auto;
    }
    .nav-user.open .user-caret{ transform: rotate(180deg); }

    /* القائمة المنسدلة */
    .user-menu{
      position: absolute;
      right: 0;
      top: calc(100% + 10px);
      min-width: 210px;
      background: rgba(255, 255, 255, 0.98);
      border-radius: 16px;
      padding: 8px 0;
      z-index: 1000;
      box-shadow:
        0 20px 60px rgba(15, 23, 42, 0.18),
        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
      backdrop-filter: blur(20px);
      overflow: hidden;

      /* مهم: نخفيها صح بدون ما نخرب الأنيميشن */
      opacity: 0;
      transform: translateY(-10px);
      pointer-events: none;
      transition: opacity .18s ease, transform .18s ease;
    }

    .user-menu::before{
      content:'';
      position:absolute;
      top:0; left:0; right:0;
      height:1px;
      background: linear-gradient(90deg, transparent, rgba(124,58,237,.22), transparent);
    }

    .user-menu.show{
      opacity: 1;
      transform: translateY(0);
      pointer-events: auto;
    }

    .user-menu a,
    .user-menu form button{
      display:flex;
      align-items:center;
      gap:10px;
      width:100%;
      text-align:left;
      padding: 10px 18px;
      font-size:14px;
      font-weight:600;
      font-family:"Plus Jakarta Sans", system-ui, sans-serif;
      background: transparent;
      border: none;
      cursor: pointer;
      color:#475569;
      transition: all .18s ease;
      position: relative;
      text-decoration: none;
    }

    .user-menu a i,
    .user-menu form button i{
      width: 18px;
      color:#94a3b8;
      font-size: 15px;
      transition: transform .18s ease, color .18s ease;
    }

    .user-menu a:hover,
    .user-menu form button:hover{
      background: linear-gradient(90deg, rgba(124, 58, 237, 0.10), transparent);
      color: #7c3aed;
      padding-left: 22px;
    }

    .user-menu a:hover i,
    .user-menu form button:hover i{
      color:#7c3aed;
      transform: scale(1.08);
    }

    .user-menu hr{
      border:none;
      height:1px;
      background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
      margin: 6px 16px;
    }

    /* شكل الزر وهو مفتوح */
    .nav-user.open .user-toggle{
      box-shadow:
        0 12px 28px rgba(15, 23, 42, 0.14),
        0 0 0 1px rgba(124, 58, 237, 0.14) inset;
      background: rgba(255, 255, 255, 1);
    }

    /* Demo content */
    .content{
      max-width:1100px;
      margin: 26px auto;
      padding: 0 18px;
    }
    .card{
      background:#fff;
      border:1px solid var(--border);
      border-radius: 18px;
      padding: 18px;
      box-shadow: var(--shadow);
    }
  
    .avatar-letter{
      width:100%; height:100%;
      display:flex; align-items:center; justify-content:center;
      color:#fff; font-weight:800; font-size:32px;
      letter-spacing:-.02em;
    }
    /* نخفي صورة الافاتار لو موجودة */
    #avatarImg{ display:none !important; }
  </style>

  <script>
    window.TRAVELO = window.TRAVELO || {};
    window.TRAVELO.isLoggedIn = true;
    window.TRAVELO.userId = <?= (int)$user['id'] ?>;
  </script>
</head>

<body>

  <!-- ================= NAV ================= -->
  <section class="nav-wrapper">
    <div class="container">
      <nav class="nav">
        <div class="logo">
          <img class="img-logo" src="./assets/images/logo.svg" alt="Travelo Logo">
          <a href="index.php">Travelo</a>
        </div>

        <div class="nav-links">
          <ul class="nav-links-ul">
            <li><a href="index.php">Home</a></li>
            <li><a href="./fligths.php">Flights</a></li>
            <li><a href="./hotel.php">Hotels</a></li>
            <li><a href="./packages.php">Packages</a></li>
            <li><a href="./destination.php">Destinations</a></li>
          </ul>
        </div>

        <div class="nav-button">
          <div class="nav-user" id="navUser">
            <button type="button" class="user-toggle" id="userMenuToggle" aria-haspopup="true" aria-expanded="false">
              <span class="user-avatar"><?= h($avatarLetter) ?></span>

              <span class="user-text">
                Welcome back, <?= h($user['username'] ?? 'Traveler') ?>
              </span>

              <i class="bi bi-chevron-down user-caret" aria-hidden="true"></i>
            </button>

            <div class="user-menu" id="userMenu">
              <a href="./profile.php"><i class="bi bi-person"></i> My profile</a>
              <a href="./myBooking.php"><i class="bi bi-ticket-perforated"></i> My bookings</a>
              <hr>
              <form action="logout.php" method="post">
                <button type="submit"><i class="bi bi-box-arrow-right"></i> Log out</button>
              </form>
            </div>
          </div>
        </div>

      </nav>
    </div>
  </section>
  <!-- ================= END NAV ================= -->


  <!-- ================= PROFILE PAGE ================= -->
  <main class="page">

    <header class="hero">
      <div class="cover">
        <div class="cover__actions">
          <button class="btn btn--ghost" type="button" id="btnCover" disabled>
            <i class="bi bi-upload"></i>
            Change cover
          </button>
        </div>
      </div>

      <div class="identity">
        <div class="avatar">
          <!-- no image -->
          <div class="avatar-letter" id="avatarLetter"><?= h($avatarLetter) ?></div>
          <img id="avatarImg" src="" alt="Profile photo">
          <button class="avatar__edit" type="button" id="btnAvatar" aria-label="Change profile photo" disabled>
            <i class="bi bi-pencil-fill"></i>
          </button>
        </div>

        <div class="idtext">
          <h1 class="name" id="displayName"><?= h($fullName) ?></h1>
          <p class="email" id="displayEmail"><?= h($user['email'] ?? '') ?></p>

          <div class="chips">
            <span class="chip" id="sinceChip">
              <i class="bi bi-clock"></i>
              Member since <?= h($memberSince ?: '—') ?>
            </span>
          </div>
        </div>

        <div class="hero__right">
          <button class="btn btn--primary" type="button" data-open="modalEdit">
            <i class="bi bi-pencil-square"></i>
            Edit profile
          </button>
        </div>
      </div>
    </header>

    <section class="section">
      <div class="section__head">
        <h2 class="section__title">Account</h2>
        <p class="section__sub">Everything in one clean place.</p>
      </div>

      <div class="panel">
        <div class="row">
          <div class="meta">First name</div>
          <div class="val"><strong id="vFirst"><?= h($user['first_name'] ?? '') ?></strong></div>
          <button class="mini" type="button" data-open="modalEdit"><i class="bi bi-pencil"></i> Change</button>
        </div>

        <div class="row">
          <div class="meta">Last name</div>
          <div class="val"><strong id="vLast"><?= h($user['last_name'] ?? '') ?></strong></div>
          <button class="mini" type="button" data-open="modalEdit"><i class="bi bi-pencil"></i> Change</button>
        </div>

        <div class="row">
          <div class="meta">Username</div>
          <div class="val"><strong id="vUser"><?= h($user['username'] ?? '') ?></strong></div>
          <button class="mini" type="button" data-open="modalEdit"><i class="bi bi-pencil"></i> Change</button>
        </div>

        <div class="row">
          <div class="meta">Email</div>
          <div class="val"><strong id="vEmail"><?= h($user['email'] ?? '') ?></strong></div>
          <button class="mini" type="button" data-open="modalEdit"><i class="bi bi-pencil"></i> Change</button>
        </div>

        <div class="row">
          <div class="meta">Birth date</div>
          <div class="val"><strong id="vBirth"><?= h($user['birth_date'] ?? '') ?></strong></div>
          <button class="mini" type="button" data-open="modalEdit"><i class="bi bi-pencil"></i> Change</button>
        </div>

        <div class="divider"></div>

        <div class="row">
          <div class="meta">Password</div>
          <div class="val">
            <strong>••••••••••••</strong>
            <span class="muted">Change it regularly</span>
          </div>
          <button class="mini" type="button" data-open="modalPass"><i class="bi bi-key"></i> Change</button>
        </div>

        <div class="row">
          <div class="meta">Status</div>
          <div class="val">
            <strong id="statusText">Active</strong>
            <span class="muted">You can deactivate anytime</span>
          </div>
          <button class="mini mini--danger" type="button" id="btnDeactivate">
            <i class="bi bi-slash-circle"></i> Deactivate
          </button>
        </div>
      </div>
    </section>

  </main>


  <!-- ================= MODALS ================= -->
  <div class="modal" id="modalEdit" aria-hidden="true">
    <div class="modal__backdrop" data-close="modalEdit"></div>
    <div class="modal__dialog" role="dialog" aria-modal="true">
      <div class="modal__head">
        <div>
          <h3 class="modal__title">Edit profile</h3>
          <p class="modal__sub">Update your info.</p>
        </div>
        <button class="iconbtn" type="button" data-close="modalEdit" aria-label="Close">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <form class="form" id="formEdit">
        <div class="row2">
          <div class="field">
            <label>First name</label>
            <input id="inFirst" type="text" placeholder="First name" value="<?= h($user['first_name'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Last name</label>
            <input id="inLast" type="text" placeholder="Last name" value="<?= h($user['last_name'] ?? '') ?>">
          </div>
        </div>

        <div class="field">
          <label>Username</label>
          <input id="inUser" type="text" placeholder="username" value="<?= h($user['username'] ?? '') ?>">
        </div>

        <div class="field">
          <label>Email</label>
          <input id="inEmail" type="email" placeholder="you@email.com" value="<?= h($user['email'] ?? '') ?>">
        </div>

        <div class="field">
          <label>Birth date</label>
          <input id="inBirth" type="date" value="<?= h($user['birth_date'] ?? '') ?>">
        </div>

        <div class="modal__foot">
          <button class="btn btn--ghost" type="button" data-close="modalEdit">Cancel</button>
          <button class="btn btn--primary" type="submit">
            <i class="bi bi-check2"></i> Save
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal" id="modalPass" aria-hidden="true">
    <div class="modal__backdrop" data-close="modalPass"></div>
    <div class="modal__dialog" role="dialog" aria-modal="true">
      <div class="modal__head">
        <div>
          <h3 class="modal__title">Change password</h3>
          <p class="modal__sub">Secure your account.</p>
        </div>
        <button class="iconbtn" type="button" data-close="modalPass" aria-label="Close">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <form class="form" id="formPass">
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

        <div class="modal__foot">
          <button class="btn btn--ghost" type="button" data-close="modalPass">Cancel</button>
          <button class="btn btn--primary" type="submit">
            <i class="bi bi-check2"></i> Update
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="toast" id="toast" aria-live="polite" aria-atomic="true"></div>

  <script src="./assets/js/home.js"></script>
  <script src="./assets/js/profile.js"></script>
</body>
</html>
