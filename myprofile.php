<?php
session_start();

/* ================== CONFIG ================== */
$host     = 'localhost';
$dbname   = 'travelo';
$username = 'root';
$password = '';

$loginUrl = 'login.php'; // عدّليها لو عندك login.php

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

  <link rel="stylesheet" href="./assets/css/home.css">
  <link rel="stylesheet" href="./assets/css/profile.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family:"Plus Jakarta Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: var(--bg);
      color: var(--ink);
    }
    a { text-decoration: none; color: inherit; }

    /* ===== Fix navbar links underline ===== */
    .nav-wrapper a,
    .nav-wrapper a:visited,
    .nav-links-ul li a{
      text-decoration: none !important;
      color: #000;
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

    .user-text{
      white-space: nowrap;
      color: #0f172a;
      font-weight: 700;
      font-size: 14px;
      letter-spacing: -0.01em;
      position: relative;
    }

    .user-caret{
      font-size: 12px;
      color: #94a3b8;
      transition: transform 0.25s ease;
      margin-left: 2px;
      flex: 0 0 auto;
    }
    .nav-user.open .user-caret{ transform: rotate(180deg); }

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

    .nav-user.open .user-toggle{
      box-shadow:
        0 12px 28px rgba(15, 23, 42, 0.14),
        0 0 0 1px rgba(124, 58, 237, 0.14) inset;
      background: rgba(255, 255, 255, 1);
    }

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
    #avatarImg{ display:none !important; }

    /* ================= Travelo Testimonial Submit (FRONT ONLY) ================= */
    .tvt-grid{
      display:grid;
      grid-template-columns: 1.05fr 1.2fr;
      gap: 16px;
      margin-top: 14px;
    }
    @media (max-width: 920px){
      .tvt-grid{ grid-template-columns: 1fr; }
    }

    .tvt-card{
      position:relative;
      background:#fff;
      border:1px solid var(--border);
      border-radius: 22px;
      box-shadow: var(--shadow);
      overflow:hidden;
    }

    .tvt-card--info{
      padding: 22px;
      background:
        radial-gradient(circle at 15% 15%, rgba(124,58,237,.14), transparent 60%),
        radial-gradient(circle at 85% 30%, rgba(108,99,255,.12), transparent 55%),
        #ffffff;
    }
    .tvt-glow{
      position:absolute; inset:-80px;
      background: radial-gradient(circle at 30% 20%, rgba(124,58,237,.22), transparent 55%);
      filter: blur(18px);
      pointer-events:none;
    }
    .tvt-badge{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding: 10px 12px;
      border-radius: 999px;
      font-weight: 800;
      font-size: 12px;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--accent);
      background: rgba(124,58,237,.10);
      border: 1px solid rgba(124,58,237,.16);
      position:relative;
      z-index:1;
    }
    .tvt-h3{
      position:relative;
      z-index:1;
      margin: 14px 0 8px;
      font-size: 22px;
      letter-spacing: -.02em;
    }
    .tvt-p{
      position:relative;
      z-index:1;
      margin: 0 0 16px;
      color: var(--muted);
      line-height: 1.7;
    }
    .tvt-points{
      position:relative;
      z-index:1;
      display:grid;
      gap: 10px;
    }
    .tvt-point{
      display:flex;
      align-items:center;
      gap: 10px;
      padding: 12px 12px;
      border-radius: 16px;
      background: rgba(255,255,255,.75);
      border: 1px solid rgba(232,234,243,.95);
      color: #334155;
      font-weight: 700;
    }
    .tvt-point i{ color: var(--accent); }

    .tvt-card--form{ padding: 18px; }
    .tvt-form{ display:grid; gap: 14px; }
    .tvt-row2{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }
    @media (max-width: 560px){
      .tvt-row2{ grid-template-columns: 1fr; }
    }

    .tvt-field label{
      display:block;
      font-weight: 800;
      font-size: 13px;
      color:#334155;
      margin: 0 0 6px;
    }
    .tvt-field input,
    .tvt-field textarea{
      width:100%;
      border-radius: 16px;
      border: 1px solid var(--border);
      background: #fff;
      padding: 12px 12px;
      font: inherit;
      outline: none;
      box-shadow: 0 0 0 0 rgba(124,58,237,0);
      transition: .18s ease;
    }
    .tvt-field textarea{ resize: vertical; min-height: 120px; }
    .tvt-field input:focus,
    .tvt-field textarea:focus{
      border-color: rgba(124,58,237,.35);
      box-shadow: 0 0 0 4px rgba(124,58,237,.10);
    }

    .tvt-stars{
      display:flex;
      align-items:center;
      gap: 8px;
      flex-wrap:wrap;
    }
    .tvt-star{
      width: 40px; height: 40px;
      border-radius: 14px;
      border: 1px solid rgba(232,234,243,.95);
      background: rgba(247,248,251,.9);
      cursor:pointer;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      transition: .18s ease;
    }
    .tvt-star i{ color: rgba(124,58,237,.35); font-size: 18px; }
    .tvt-star.is-on{
      background: rgba(124,58,237,.10);
      border-color: rgba(124,58,237,.22);
    }
    .tvt-star.is-on i{ color: var(--accent); }
    .tvt-star:hover{ transform: translateY(-1px); }

    .tvt-starsText{
      margin-left: 6px;
      font-weight: 800;
      color: #475569;
    }

    .tvt-help{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      margin-top: 8px;
    }
    .tvt-note{
      color: var(--muted);
      font-size: 13px;
      display:flex;
      align-items:center;
      gap: 8px;
    }
    .tvt-count{
      font-weight: 800;
      color:#94a3b8;
      font-size: 12px;
    }

    .tvt-actions{
      display:flex;
      justify-content:flex-end;
      gap: 10px;
      padding-top: 2px;
    }
    .tvt-btn{ border-radius: 999px; }

    .tvt-toast{
      position: fixed;
      left: 50%;
      bottom: 22px;
      transform: translateX(-50%) translateY(14px);
      opacity: 0;
      pointer-events: none;
      background: rgba(15,23,42,.92);
      color:#fff;
      padding: 12px 14px;
      border-radius: 999px;
      font-weight: 700;
      box-shadow: 0 18px 44px rgba(15,23,42,.28);
      transition: .22s ease;
      z-index: 9999;
      max-width: min(520px, calc(100% - 26px));
      text-align:center;
    }
    .tvt-toast.show{
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }
    #btnCover{
      opacity: 0;
    }
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

    <!-- ================= TESTIMONIAL SUBMIT (FRONT ONLY) ================= -->
    <section class="section tvt" id="tvt">
      <div class="section__head">
        <h2 class="section__title">Leave a review</h2>
        <p class="section__sub">Share your experience. It will be reviewed by admin before publishing.</p>
      </div>

      <div class="tvt-grid">
        <div class="tvt-card tvt-card--info">
          <div class="tvt-glow"></div>

          <div class="tvt-badge">
            <i class="bi bi-stars"></i>
            Travelo Testimonials
          </div>

          <h3 class="tvt-h3">Your words help others travel smarter ✈️</h3>
          <p class="tvt-p">
            Write a short message about your experience with Travelo. We’ll review it first, then it will appear in the testimonials section.
          </p>

          <div class="tvt-points">
            <div class="tvt-point"><i class="bi bi-shield-check"></i> Reviewed by admin</div>
            <div class="tvt-point"><i class="bi bi-lightning-charge"></i> Quick submission</div>
            <div class="tvt-point"><i class="bi bi-emoji-smile"></i> Be kind & honest</div>
          </div>
        </div>

        <div class="tvt-card tvt-card--form">
          <form class="tvt-form" id="tvtForm" autocomplete="off">
            <div class="tvt-row2">
              <div class="tvt-field">
                <label for="tvtName">Name</label>
                <input id="tvtName" name="name" type="text"
                       value="<?= h($fullName ?: ($user['username'] ?? 'Traveler')) ?>"
                       placeholder="Your name">
              </div>

              <div class="tvt-field">
                <label for="tvtTitle">Title</label>
                <input id="tvtTitle" name="title" type="text"
                       value="Travel Enthusiast"
                       placeholder="e.g. Adventure Seeker">
              </div>
            </div>

            <div class="tvt-field">
              <label>Rating</label>
              <div class="tvt-stars" role="radiogroup" aria-label="Rating">
                <button type="button" class="tvt-star" data-v="1" aria-label="1 star"><i class="bi bi-star-fill"></i></button>
                <button type="button" class="tvt-star" data-v="2" aria-label="2 stars"><i class="bi bi-star-fill"></i></button>
                <button type="button" class="tvt-star" data-v="3" aria-label="3 stars"><i class="bi bi-star-fill"></i></button>
                <button type="button" class="tvt-star" data-v="4" aria-label="4 stars"><i class="bi bi-star-fill"></i></button>
                <button type="button" class="tvt-star" data-v="5" aria-label="5 stars"><i class="bi bi-star-fill"></i></button>
                <input type="hidden" name="rating" id="tvtRating" value="5">
                <span class="tvt-starsText" id="tvtStarsText">Excellent</span>
              </div>
            </div>

            <div class="tvt-field">
              <label for="tvtMsg">Message</label>
              <textarea id="tvtMsg" name="message" rows="5"
                placeholder="Write your review... (max 300 chars)"></textarea>
              <div class="tvt-help">
                <span class="tvt-note"><i class="bi bi-info-circle"></i> It will be saved as <b>Pending</b> until admin approval.</span>
                <span class="tvt-count" id="tvtCount">0 / 300</span>
              </div>
            </div>

            <div class="tvt-actions">
              <button type="button" class="btn btn--ghost tvt-btn" id="tvtClear">
                <i class="bi bi-eraser"></i> Clear
              </button>
              <button type="submit" class="btn btn--primary tvt-btn">
                <i class="bi bi-send"></i> Submit review
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="tvt-toast" id="tvtToast" aria-live="polite" aria-atomic="true"></div>
    </section>
    <!-- ================= END TESTIMONIAL SUBMIT ================= -->

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

  <!-- ===== Testimonial front-only JS ===== -->
  <script>
(() => {
  const form = document.getElementById('tvtForm');
  if (!form) return;

  const stars = Array.from(document.querySelectorAll('.tvt-star'));
  const ratingInput = document.getElementById('tvtRating');
  const starsText = document.getElementById('tvtStarsText');
  const msg = document.getElementById('tvtMsg');
  const count = document.getElementById('tvtCount');
  const clearBtn = document.getElementById('tvtClear');
  const toast = document.getElementById('tvtToast');

  const nameInp = document.getElementById('tvtName');
  const titleInp = document.getElementById('tvtTitle');
  const submitBtn = form.querySelector('button[type="submit"]');

  const labels = {1:'Poor',2:'Fair',3:'Good',4:'Very good',5:'Excellent'};
  const MAX = 300;

  function paint(v){
    stars.forEach(btn => btn.classList.toggle('is-on', (+btn.dataset.v) <= v));
    ratingInput.value = String(v);
    starsText.textContent = labels[v] || 'Excellent';
  }

  function showToast(text){
    toast.textContent = text;
    toast.classList.add('show');
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => toast.classList.remove('show'), 2400);
  }

  function updateCount(){
    const v = msg.value || '';
    if (v.length > MAX) msg.value = v.slice(0, MAX);
    count.textContent = `${msg.value.length} / ${MAX}`;
  }

  paint(+ratingInput.value || 5);
  updateCount();

  msg.addEventListener('input', updateCount);
  stars.forEach(btn => btn.addEventListener('click', () => paint(+btn.dataset.v || 5)));

  clearBtn.addEventListener('click', () => {
    titleInp.value = 'Travel Enthusiast';
    msg.value = '';
    paint(5);
    updateCount();
    showToast('Cleared ✅');
  });

  async function postJson(url, payload){
    const res = await fetch(url, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) {
      throw new Error(data.message || 'Request failed');
    }
    return data;
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const name = (nameInp.value || '').trim();
    const title = (titleInp.value || '').trim();
    const message = (msg.value || '').trim();
    const rating = parseInt(ratingInput.value || '5', 10) || 5;

    if (!name || !message) return showToast('Please write your name and message.');

    submitBtn?.setAttribute('disabled', 'disabled');

    try{
      await postJson('./testimonial_submit.php', { name, title, message, rating });
      showToast('Sent! Pending admin review 💜');

      msg.value = '';
      paint(5);
      updateCount();
    }catch(err){
      showToast(err.message || 'Something went wrong');
    }finally{
      submitBtn?.removeAttribute('disabled');
    }
  });
})();
</script>

  <!-- زر Ask AI -->
  <button id="askAiBtn" class="ask-ai-btn" type="button" aria-label="Ask AI">
    <i class="bi bi-airplane-fill" aria-hidden="true"></i>
    <span class="ask-ai-bubble">Ask AI</span>
  </button>

  <script>
  (() => {
    const AGENT_ID = "019b189a507c7f0e98a0580ad136880f79ad";
    const SRC = `https://cdn.jotfor.ms/agent/embedjs/${AGENT_ID}/embed.js`;

    function loadWidget(){
      return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${SRC}"]`)) return resolve();
        const s = document.createElement("script");
        s.src = SRC;
        s.async = true;
        s.onload = resolve;
        s.onerror = () => reject(new Error("Failed to load widget"));
        document.body.appendChild(s);
      });
    }

    function openLauncherWhenReady(timeoutMs = 8000){
      return new Promise((resolve) => {
        const start = Date.now();

        const tryOpen = () => {
          const launcher =
            document.querySelector('button[aria-label*="Ask AI" i]') ||
            document.querySelector('button[aria-label*="Chat" i]') ||
            document.querySelector('[data-testid*="launcher" i]') ||
            document.querySelector('.jotform-ai-launcher, .agent-launcher, .chat-launcher');

          if (launcher) { launcher.click(); resolve(true); return true; }
          if (Date.now() - start > timeoutMs) { resolve(false); return true; }
          return false;
        };

        if (tryOpen()) return;

        const obs = new MutationObserver(() => {
          if (tryOpen()) obs.disconnect();
        });
        obs.observe(document.documentElement, { childList:true, subtree:true });
      });
    }

    document.addEventListener("DOMContentLoaded", () => {
      const btn = document.getElementById("askAiBtn");
      if (!btn) return;

      btn.addEventListener('click', async () => {
        btn.style.display = "none";
        try{
          await loadWidget();
          await openLauncherWhenReady();
        }catch(e){
          console.error(e);
          btn.style.display = "";
          alert("AI widget failed to load.");
        }
      }, { once:true });
    });
  })();
  </script>

</body>
</html>
