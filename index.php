<?php
$subStatus = $_GET['sub'] ?? null;

require __DIR__ . '/db.php';
session_start();

// top destinations (is_top = 1)
$stmtTop = $pdo->prepare("
  SELECT id, name, city, country, image_url, short_desc, base_price, category
  FROM destinations
  WHERE is_active = 1 AND is_top = 1
  ORDER BY created_at DESC
");
$stmtTop->execute();
$topDestinations = $stmtTop->fetchAll();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Travelo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
    rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <link href="./assets/css/main.css" rel="stylesheet">
  <link href="./assets/css/home.css" rel="stylesheet">
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
 /* =====================================
   BLOGS (Admin Cards) - Bigger + Modern
   ===================================== */

#blogs .blogs-admin-list{
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* ✅ أكبر + مرن */
  gap: 18px;
  align-items: stretch;
}

#blogs .blog-card{
  position: relative;
  border-radius: 22px;
  border: 1px solid rgba(124,58,237,.12);
  background: var(--tbl-bg);
  overflow: hidden;

  display: flex;
  flex-direction: column;

  min-height: 430px;        /* ✅ كارد أطول وأفخم */
  box-shadow:
    0 24px 70px rgba(15,23,42,.10),
    0 1px 0 rgba(255,255,255,.65) inset;

  transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease, filter .22s ease;
  animation: blogPop .35s ease both;
}

@keyframes blogPop{
  from{ opacity:0; transform: translateY(10px) scale(.985); }
  to{ opacity:1; transform: translateY(0) scale(1); }
}

#blogs .blog-card:hover{
  transform: translateY(-5px);
  border-color: rgba(124,58,237,.24);
  box-shadow:
    0 32px 90px rgba(15,23,42,.14),
    0 1px 0 rgba(255,255,255,.7) inset;
  filter: saturate(1.03);
}

/* Cover */
#blogs .blog-cover-wrap{
  position: relative;
  height: 200px; /* ✅ صورة أكبر */
  background: var(--tbl-head-bg);
  overflow: hidden;
}

#blogs .blog-cover{
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scale(1.04);
  transition: transform .55s ease;
}
#blogs .blog-card:hover .blog-cover{ transform: scale(1.10); }

#blogs .blog-cover-wrap::after{
  content:"";
  position:absolute; inset:0;
  background:
    radial-gradient(circle at 30% 15%, rgba(124,58,237,.18), transparent 55%),
    linear-gradient(180deg, rgba(0,0,0,0) 35%, rgba(0,0,0,.38) 100%);
  pointer-events:none;
}

/* Badges */
#blogs .blog-badges{
  position:absolute;
  left: 14px;
  bottom: 14px;
  display:flex;
  gap: 8px;
  flex-wrap: wrap;
  z-index: 2;
}

#blogs .badge-pill{
  display:inline-flex;
  align-items:center;
  gap: 6px;
  padding: .34rem .68rem;
  border-radius: 999px;
  font-weight: 900;
  font-size: 12.2px;
  color:#fff;
  background: rgba(15,23,42,.55);
  border: 1px solid rgba(255,255,255,.18);
  backdrop-filter: blur(10px);
}

#blogs .badge-status{
  background: linear-gradient(135deg, rgba(124,58,237,.98), rgba(108,99,255,.95));
  border-color: rgba(255,255,255,.22);
}

/* Body */
#blogs .blog-body{
  padding: 16px 16px 12px; /* ✅ مساحة أريح */
  flex: 1;
  display:flex;
  flex-direction: column;
  gap: 10px;
  min-width: 0;
}

#blogs .blog-title{
  margin: 0;
  font-weight: 1000;
  font-size: 16px; /* ✅ أكبر */
  color: var(--tbl-head-text);
  letter-spacing: -0.015em;

  display:-webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow:hidden;
  min-height: calc(1.35em * 2);
  line-height: 1.35;
}

#blogs .blog-excerpt{
  margin: 0;
  color: var(--p-muted);
  font-weight: 650;
  font-size: 13.8px; /* ✅ أكبر وأنعم */
  line-height: 1.8;

  display:-webkit-box;
  -webkit-line-clamp: 4;  /* ✅ مساحة نص أكثر لأن الكارد أكبر */
  -webkit-box-orient: vertical;
  overflow:hidden;
  min-height: calc(1.8em * 4);
}

/* Meta */
#blogs .blog-meta{
  margin-top:auto;
  display:flex;
  align-items:center;
  justify-content: space-between;
  gap: 10px;
  padding-top: 14px;
  border-top: 1px solid rgba(124,58,237,.10);

  font-size: 13px;     /* ✅ أكبر */
  font-weight: 800;
  color: var(--p-muted);
}

#blogs .blog-meta .m{
  display:flex;
  align-items:center;
  gap: 7px;
  white-space: nowrap;
}

/* Actions */
#blogs .blog-actions{
  display:flex;
  gap: 12px;
  padding: 14px 16px 16px;

  border-top: 1px solid rgba(124,58,237,.08);
  background: linear-gradient(180deg, rgba(124,58,237,.05), transparent 65%);
}

/* Buttons (modern + bigger) */
#blogs .blog-actions .btn{
  border-radius: 999px !important;
  font-weight: 900 !important;
  padding: .64rem 1.05rem !important; /* ✅ أكبر */
  flex: 1;
  box-shadow: none !important;
  transition: transform .18s ease, filter .18s ease, background .18s ease, border-color .18s ease, color .18s ease;
}
#blogs .blog-actions .btn:hover{ transform: translateY(-1px); }

/* View Button (premium) */
#blogs .btn-viewdetails{
  border: 0 !important;
  color: #fff !important;
  background: linear-gradient(135deg, rgba(124,58,237,1), rgba(108,99,255,1)) !important;
  box-shadow: 0 16px 34px rgba(124,58,237,.20) !important;
}
#blogs .btn-viewdetails:hover{
  filter: brightness(1.05);
  box-shadow: 0 20px 46px rgba(124,58,237,.26) !important;
}

/* Delete Button (white bg + red hover) */
#blogs .btn-del,
#blogs .btn-danger{
  background: #fff !important;
  color: #0f172a !important;
  border: 1px solid rgba(15,23,42,.14) !important;
}
#blogs .btn-del:hover,
#blogs .btn-danger:hover{
  background: rgba(239,68,68,.10) !important;
  border-color: rgba(239,68,68,.50) !important;
  color: #ef4444 !important;
}

/* Focus rings */
#blogs .blog-actions .btn:focus{
  box-shadow: 0 0 0 4px rgba(124,58,237,.16) !important;
}
#blogs .btn-del:focus,
#blogs .btn-danger:focus{
  box-shadow: 0 0 0 4px rgba(239,68,68,.16) !important;
}

/* Dark mode tweaks */
html.dark #blogs .blog-card{
  border-color: rgba(124,58,237,.18);
  box-shadow: 0 22px 70px rgba(0,0,0,.42);
}
html.dark #blogs .blog-actions{
  border-top-color: rgba(233,236,255,.10);
  background: linear-gradient(180deg, rgba(124,58,237,.07), transparent 65%);
}
html.dark #blogs .btn-del,
html.dark #blogs .btn-danger{
  background: rgba(15,18,34,.55) !important;
  color: var(--p-ink) !important;
  border-color: rgba(233,236,255,.16) !important;
}
/* ===== BLOGS OVERRIDE (PASTE LAST) ===== */
#blogs .blogs-admin-list{
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
  gap: 18px !important;
}

#blogs .blog-card{
  min-height: 430px !important;
  border-radius: 22px !important;
  box-shadow: 0 24px 70px rgba(15,23,42,.10) !important;
  border: 1px solid rgba(124,58,237,.12) !important;
  transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease, filter .22s ease !important;
}
#blogs .blog-card:hover{
  transform: translateY(-5px) !important;
  box-shadow: 0 32px 90px rgba(15,23,42,.14) !important;
  border-color: rgba(124,58,237,.24) !important;
}

#blogs .blog-cover-wrap{ height: 200px !important; }
#blogs .blog-cover{ transform: scale(1.04) !important; transition: transform .55s ease !important; }
#blogs .blog-card:hover .blog-cover{ transform: scale(1.10) !important; }

#blogs .blog-actions{ gap: 12px !important; padding: 14px 16px 16px !important; }
#blogs .blog-actions .btn{
  padding: .64rem 1.05rem !important;
  border-radius: 999px !important;
  font-weight: 900 !important;
}

#blogs .btn-del,
#blogs .btn-danger{
  background: #fff !important;
  color: #0f172a !important;
  border: 1px solid rgba(15,23,42,.14) !important;
}
#blogs .btn-del:hover,
#blogs .btn-danger:hover{
  background: rgba(239,68,68,.10) !important;
  border-color: rgba(239,68,68,.50) !important;
  color: #ef4444 !important;
}


  </style>

  <!-- TRAVELO user info للـ JS (زي hotel.php) -->
  <script>
    window.TRAVELO = window.TRAVELO || {};
    window.TRAVELO.isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    <?php if (isset($_SESSION['user_id'])): ?>
      window.TRAVELO.userId    = <?= (int) $_SESSION['user_id'] ?>;
      window.TRAVELO.userName  = <?= json_encode($_SESSION['user_name']  ?? '') ?>;
      window.TRAVELO.userEmail = <?= json_encode($_SESSION['user_email'] ?? '') ?>;
    <?php endif; ?>
  </script>

</head>

<body>
  
  <img class="effect1" src="./assets/images/hero-img/Ellipse 23.png">
  <img class="effect2" src="./assets/images/hero-img/Ellipse 24.jpg">
  <section class="nav-wrapper">
    <div class="container">
      <nav class="nav">
        <div class="logo">
          <img class="img-logo" src="./assets/images/logo.svg" alt="Travelo Logo">
          <a href="index.php">Travelo</a>
        </div>

        <div class="nav-links">
          <ul class="nav-links-ul">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="./fligths.php">Flights</a></li>
            <li><a href="./hotel.php">Hotels</a></li>
            <li><a href="./packages.php">Packages</a></li>
            <li><a href="./destination.php">Destinations</a></li>

          </ul>
        </div>

<div class="nav-button">
   <button id="darkModeToggle" class="dark-mode-toggle" type="button" aria-label="Toggle dark mode">
    <i class="bi bi-moon-fill" id="darkModeIcon"></i>
  </button>
  <?php if (isset($_SESSION['user_id'])): ?>
    <!-- ====== Logged-in state ====== -->
    <div class="nav-user">
      <button type="button" class="user-toggle" id="userMenuToggle" aria-haspopup="true" aria-expanded="false">
        <span class="user-avatar">
          <?php
            $name = $_SESSION['user_name'] ?? 'U';
            echo strtoupper(mb_substr($name, 0, 1));
          ?>
        </span>

        <span class="user-text">
          Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Traveler') ?>
        </span>

        <!-- السهم -->
        <i class="bi bi-chevron-down user-caret" aria-hidden="true"></i>
      </button>

      <div class="user-menu" id="userMenu">
        <a href="./myprofile.php"><i class="bi bi-person"></i> My profile</a>

        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <a href="admin-dashboard.php"><i class="bi bi-speedometer2"></i> Admin dashboard</a>
        <?php else: ?>
          <a href="./myBooking.php"><i class="bi bi-ticket-perforated"></i> My bookings</a>
        <?php endif; ?>

        <hr>

        <form action="logout.php" method="post">
          <button type="submit"><i class="bi bi-box-arrow-right"></i> Log out</button>
        </form>
      </div>
    </div>
  <?php else: ?>
    <!-- ====== Guest state ====== -->
    <button id="btnLogin" type="button" class="sign_in">Login</button>
    <button id="btnLogin1" type="button" class="sign_up">Sign up</button>
  <?php endif; ?>
</div>


        <button class="menu-toggle" aria-label="Open menu"><span></span></button>
      </nav>
    </div>
  </section>
  <div class="spinner-overlay" id="spinner">
    <div class="spinner"></div>
  </div>


  <section class="hero">
    <div class="container">
      <div class="row">
        <div class="hero-section">
          <div class="hero1">
            <div class="explore-button">
              <button>Explore the world!<span> <img src="./assets/images/explore.svg"> </span></button>
            </div>
            <div class="hero-heading">
              <h1> <span class="from-heading">From</span> the Middle East <span class="to-world">to the
                  World.</span> </h1>
            </div>
            <div class="hero-discription">
              <p>Stay updated with travel tips, recommendations, and latest promos.</p>
            </div>
            <div class="hero-button">
              <button class="get-started">Get Started</button>
              <button class="watch-demo">Watch demo<span> <img src="./assets/images/whatch-demo.svg">
                </span></button>
            </div>
          </div>
          <div class="hero2">
            <img class="line1" src="./assets/images/line1.svg">
            <img class="line2" src="./assets/images/line2.svg">
            <img class="air1" src="./assets/images/hero-img/Vector.png">
            <img class="air2" src="./assets/images/hero-img/Vector (1).png">
            <img class="plane" src="./assets/images/hero-img/Plane.png">
            <img class="map" src="./assets/images/hero-img/Maps.png">
            <div class="h-card1">
              <img src="./assets/images/hero-img/Rectangle 4.jpg" alt="img1" class="h-img">
            </div>
            <div class="h-card2">
              <img src="./assets/images/hero-img/Rectangle 5.jpg" alt="img2" class="h-img">
            </div>
            <div class="h-card3">
              <img src="./assets/images/hero-img/Rectangle 3.jpg" alt="img3" class="h-img">
            </div>
            <div class="h-card4">
              <img src="./assets/images/hero-img/Rectangle 2.png" alt="img4" class="h-img">

            </div>


          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="brand-strip py-4">
    <div class="container">
      <div class="ticker">
        <ul class="ticker-track list-unstyled d-flex align-items-center m-0">

          <li class="logo-item "><img src="./assets/images/brands/Traveloka.png" alt="Traveloka"></li>
          <li class="logo-item"><img src="./assets/images/brands/tiket.com.png" alt="tiket.com"></li>
          <li class="logo-item"><img src="./assets/images/brands/Booking.png" alt="Booking.com"></li>
          <li class="logo-item"><img src="./assets/images/brands/Tripadvisor.png" alt="Tripadvisor"></li>
          <li class="logo-item"><img src="./assets/images/brands/Airbnb.png" alt="Airbnb"></li>

        </ul>
      </div>
    </div>
  </section>

  <section class="services">
    <div class="container">
      <h2 class="section-eyebrow">services</h2>
      <h2 class="service-header">Why book using Travelo</h2>
      <div class="row gap-30">
        <div class="serv">
          <div class="service-images">
            <img src="./assets/images/services/Group 2.png">
          </div>

          <div class="services-description">
            <h3>All You Needs</h3>
            <h4>Everything in one place—flights, stays, and attractions curated for you. Compare, combine,
              and book in minutes.</h4>
          </div>
        </div>
        <div class="serv">
          <div class="service-images">
            <img src="./assets/images/services/Group 3.png">
          </div>
          <div class="services-description">
            <h3>Flexible Booking</h3>
            <h4>Plans change. Pick flexible rates with free cancellation or date changes on select
              options—clearly</h4>
          </div>
        </div>
        <div class="serv">
          <div class="service-images">
            <img src="./assets/images/services/Group 4.png">
          </div>
          <div class="services-description">
            <h3>Secure Payment</h3>
            <h4>Encrypted checkout with trusted providers and multiple payment methods. Your data stays
              protected—always.</h4>
          </div>
        </div>
      </div>
    </div>
  </section>

<div class="top-wrapper">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
    <div>
      <div class="section-label">TOP DESTINATION</div>
      <h1 class="section-title mb-0">Explore top destination</h1>
    </div>

    <div class="category-tabs">
      <button class="category-btn" data-category="city">City</button>
      <button class="category-btn" data-category="mountain">Mountain</button>
      <button class="category-btn" data-category="forest">Forest</button>
      <button class="category-btn" data-category="island">Island</button>
      <a href="./destination.php" class="see-all-link">see all</a>
    </div>
  </div>

  <!-- ✅ Stage: الأسهم على الطرفين وبالنص -->
  <div class="td-stage">
    <button class="td-nav-btn prev" type="button" aria-label="Previous">
      <i class="bi bi-chevron-left"></i>
    </button>

    <div class="row g-4">
      <?php if (!empty($topDestinations)): ?>
        <?php foreach ($topDestinations as $dest): ?>
          <div class="col-12 col-md-6 col-lg-4 destination-col"
               data-category="<?= htmlspecialchars($dest['category']) ?>"
               data-id="<?= (int)$dest['id'] ?>">
            <div class="destination-card">
              <div class="image-container">
                <div class="image-blur-effect"></div>

                <img
                  src="<?= htmlspecialchars($dest['image_url']) ?>"
                  alt="<?= htmlspecialchars($dest['name']) ?>"
                  class="destination-image"
                />

                <div class="rating-badge">
                  <span class="star">★</span>
                  <span>5.0</span>
                </div>
              </div>

              <div class="card-content">
                <div class="destination-city"><?= htmlspecialchars($dest['name']) ?></div>
                <div class="destination-desc"><?= htmlspecialchars($dest['short_desc']) ?></div>

                <div class="destination-bottom">
                  <div class="destination-footer-top">
                    <span class="location-city">
                      <?= htmlspecialchars($dest['city'] . ', ' . $dest['country']) ?>
                    </span>
                  </div>

                  <div class="destination-footer-bottom">
                    <div class="destination-price">
                      $<?= htmlspecialchars($dest['base_price']) ?>
                      <span>per person</span>
                    </div>

                    <button class="btn-gradient view-btn"
                      data-id="<?= (int)$dest['id'] ?>"
                      data-name="<?= htmlspecialchars($dest['name'], ENT_QUOTES) ?>"
                      data-location="<?= htmlspecialchars($dest['city'] . ', ' . $dest['country'], ENT_QUOTES) ?>"
                      data-image="<?= htmlspecialchars($dest['image_url'], ENT_QUOTES) ?>"
                      data-desc="<?= htmlspecialchars($dest['short_desc'], ENT_QUOTES) ?>"
                      data-price="$<?= htmlspecialchars($dest['base_price'], ENT_QUOTES) ?>"
                      data-rating="5.0">
                      See More
                    </button>
                  </div>
                </div>

              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12"><p>No destinations available right now.</p></div>
      <?php endif; ?>
    </div>

    <button class="td-nav-btn next" type="button" aria-label="Next">
      <i class="bi bi-chevron-right"></i>
    </button>
  </div>

  <!-- ✅ dots تحت -->
  <div class="td-dots" id="tdDots" aria-label="Top destinations pages"></div>
</div>



    <!-- MODAL -->
    <div class="destination-modal-overlay" id="destinationModal">
      <div class="destination-modal">
        <button class="destination-modal-close" id="destinationModalClose">&times;</button>

        <div class="destination-modal-image-wrapper">
          <img src="" alt="" id="modalDestinationImage" class="destination-modal-image" />
          <div class="destination-modal-chip">Top destination</div>
          <div class="destination-modal-gradient"></div>
        </div>

        <div class="destination-modal-body">
          <div class="destination-modal-header">
            <div>
              <h2 id="modalDestinationTitle">Title</h2>
              <p id="modalDestinationLocation" class="modal-location">City, Country</p>
            </div>
            <div class="modal-rating">
              <span>★ 5.0</span>
            </div>
          </div>

          <p id="modalDestinationDesc" class="modal-description">
            Description...
          </p>

          <div class="destination-modal-stats">
            <div class="stat-card">
              <span class="stat-label">Avg. visitors</span>
              <span class="stat-value" id="modalVisitors">—</span>
            </div>
            <div class="stat-card">
              <span class="stat-label">Best season</span>
              <span class="stat-value" id="modalSeason">—</span>
            </div>
            <div class="stat-card">
              <span class="stat-label">Starting from</span>
              <span class="stat-value" id="modalPrice">$0</span>
            </div>
          </div>

          <div class="destination-modal-actions">
            <button class="modal-btn primary" type="button" id="modalBookFlightBtn">
  <span class="icon">✈️</span>
  <div class="text">
    <span class="title">Book Trip</span>
    <span class="subtitle">Flights & activities</span>
  </div>
</button>


            <button class="modal-btn outline" type="button" id="modalBookPackageBtn">
  <span class="icon">🎁</span>
  <div class="text">
    <span class="title">Book Package</span>
    <span class="subtitle">Flight + hotel + tour</span>
  </div>
</button>


           <button class="modal-btn ghost" type="button" id="modalBookHotelBtn">
  <span class="icon">🏨</span>
  <div class="text">
    <span class="title">Book Hotel</span>
    <span class="subtitle">Hand-picked stays</span>
  </div>
</button>

        </div>
      </div>
    </div>
  </div>

  <section class="hero-section">
    <div class="container1">
      <div class="hero-image">
        <img src="./assets/images/POINT/woman.png" alt="A smiling woman with a phone and decorative shapes"
          class="main-image">
      </div>
      <div class="hero-content">
        <img src="./assets/images/POINT/ticket.png" alt="Ticket icon" class="floating-icon">
        <p class="subtitle">TRAVEL POINT</p>
        <h1>We help you find your dream destination</h1>
        <p class="description">
          Hay! Travelo there to help you find your dream holiday.<br> Easy you just find where you want to go
          and<br> buy the ticket.
        </p>
        <div class="stats-grid">
          <div class="stat-item">
            <p class="stat-number">200+</p>
            <p class="stat-label">Holiday Package</p>
          </div>
          <div class="stat-item">
            <p class="stat-number">450</p>
            <p class="stat-label">RedDoorz</p>
          </div>
          <div class="stat-item">
            <p class="stat-number">10</p>
            <p class="stat-label">Premium Airlines</p>
          </div>
          <div class="stat-item">
            <p class="stat-number">12k+</p>
            <p class="stat-label">Happy Customer</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="features-section">
    <div class="container1">
      <div class="features-content">
        <p class="subtitle">KEY FEATURES</p>
        <h2>We offer best services</h2>
        <p class="description">
          Hay! Travelo there to help you find your dream holiday. Easy you just find where you want to go and
          buy the ticket.
        </p>

        <ul class="features-list">
          <li class="feature-item">
            <img src="./assets/images/FEATURES/location.png" alt="Location Icon" class="feature-icon">
            <div class="feature-text">
              <h3>Select many location</h3>
              <p>Chooce your favorite location</p>
            </div>
          </li>
          <li class="feature-item active-feature">
            <img src="./assets/images/FEATURES/Schedule.png" alt="Calendar Icon" class="feature-icon">
            <div class="feature-text">
              <h3>Schedule your trip</h3>
              <p>Set the date you want</p>
            </div>
          </li>
          <li class="feature-item">
            <img src="./assets/images/FEATURES/discount.png" alt="Discount Icon" class="feature-icon">
            <div class="feature-text">
              <h3>Big discount</h3>
              <p>Get discount for every services</p>
            </div>
          </li>
        </ul>
      </div>
      <div class="features-images">
        <img src="./assets/images/FEATURES/photo2.png" alt="Colosseum in Rome" class="feature-image image-1">
        <img src="./assets/images/FEATURES/photo1.png" alt="Dubai Cityscape" class="feature-image image-2">
        <img src="./assets/images/FEATURES/BACKGROUND.png" alt="Decorative Dots" class="deco-dots">
      </div>

    </div>
  </section>

<?php
// =================== TESTIMONIALS BACK (PUT THIS ABOVE THE SECTION) ===================
require_once __DIR__ . '/db.php';

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function name_style_bucket(string $name): string {
  $n = mb_strtolower(trim($name));

  $femaleHints = ['aya','sara','sarah','maria','maryam','fatima','noor','nour','lama','leen','lina','dana','hala','ruba','eman','iman','huda','heba','reema','rima','rana','jana','janna','tala','shahd','yasmin','yasmeen','yara'];
  $maleHints   = ['ahmad','mohammad','muhammad','ali','omar','amr','hassan','hussein','yousef','yusuf','khaled','karem','ibrahim','adam','ammar','ameer','sameer','maher','tariq','zaid','zayd'];

  foreach ($femaleHints as $w) if (str_contains($n, $w)) return 'female';
  foreach ($maleHints as $w)   if (str_contains($n, $w)) return 'male';

  $last = mb_substr($n, -1);
  if (in_array($last, ['ة','ه','ى','ا'], true)) return 'female';

  return 'neutral';
}

function pick_avatar(array $row): string {
  $dbUrl = trim((string)($row['avatar_url'] ?? ''));
  if ($dbUrl !== '') return $dbUrl;

  // استخدم الصور الموجودة عندك
  $male    = ['./assets/images/TESTIMONIALS/avatar1.png'];
  $female  = ['./assets/images/TESTIMONIALS/avatar2.png'];
  $neutral = ['./assets/images/TESTIMONIALS/avatar.png'];

  $bucket = name_style_bucket((string)($row['name'] ?? ''));
  $pool = $neutral;
  if ($bucket === 'male') $pool = $male;
  if ($bucket === 'female') $pool = $female;

  // راندوم ثابت لكل شخص
  $seed = crc32(strtolower(($row['name'] ?? '') . '|' . ($row['id'] ?? '0')));
  $idx = $seed % max(1, count($pool));

  return $pool[$idx];
}

// جلب approved فقط
$stmt = $pdo->prepare("
  SELECT id, name, title, message, rating, avatar_url, reviewed_at
  FROM testimonials
  WHERE status='approved'
  ORDER BY reviewed_at DESC, id DESC
  LIMIT 12
");
$stmt->execute();
$testimonials = $stmt->fetchAll();
?>

<!-- =================== TESTIMONIALS SECTION (DYNAMIC) =================== -->
<section class="testimonials-section">
  <div class="container-testimonials">
    <div class="section-header">
      <p class="subtitle">TESTIMONIALS</p>
      <h2>Trust our clients</h2>
    </div>

    <div class="testimonial-slider">
      <div class="slider-wrapper">

        <?php if (!$testimonials): ?>
          <div class="testimonial-slide active-slide">
            <img src="./assets/images/TESTIMONIALS/avatar.png" alt="User" class="client-avatar">
            <h3>No testimonials yet</h3>
            <p class="client-title">—</p>

            <!-- ✅ لا نطبع نجوم هنا (عشان ما تتكرر) -->
            <div class="rating" data-rating="5"></div>

            <p class="testimonial-text">Be the first to leave a review 💜</p>
          </div>

        <?php else: ?>
          <?php $first = true; ?>
          <?php foreach ($testimonials as $t): ?>
            <?php
              $avatar = pick_avatar($t);
              $rating = (int)($t['rating'] ?? 5);
              if ($rating < 1) $rating = 1;
              if ($rating > 5) $rating = 5;
            ?>
            <div class="testimonial-slide <?= $first ? 'active-slide' : '' ?>">
              <img src="<?= h($avatar) ?>" alt="<?= h($t['name'] ?? 'User') ?>" class="client-avatar">
              <h3><?= h($t['name'] ?? 'User') ?></h3>
              <p class="client-title"><?= h($t['title'] ?? '') ?></p>

              <!-- ✅ أهم سطر: نخليها فاضية + data-rating -->
              <div class="rating" data-rating="<?= $rating ?>"></div>

              <p class="testimonial-text"><?= h($t['message'] ?? '') ?></p>
            </div>
            <?php $first = false; ?>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>

      <button class="slider-btn prev-btn" type="button" aria-label="Previous">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </button>

      <button class="slider-btn next-btn" type="button" aria-label="Next">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </button>

      <div class="slider-pagination"></div>
    </div>

    <img src="./assets/images/TESTIMONIALS/Graphic_Elements.png" alt="Decorative Dots" class="deco-dots-testimonials">
  </div>
</section>

<?php if ($subStatus === 'ok'): ?>
  <div class="alert alert-success ">
    Thank you for subscribing to Travelo newsletter ✈️💜
  </div>
<?php elseif ($subStatus === 'exists'): ?>
  <div class="alert alert-warning">
    This email is already subscribed.
  </div>
<?php elseif ($subStatus === 'invalid'): ?>
  <div class="alert alert-danger">
    Please enter a valid email address.
  </div>
<?php endif; ?>

  <section class="newsletter">
    <div class="container">
      <div class="row">
        <div class="nl-content">
          <div class="nk-content">
            <p class="nl-eyebrow">Subscribe to our newsletter</p>
            <h2 class="nl-title">
              Prepare you self and let’s explore the<br>
              beautiful of the world
            </h2>
          </div>
          <div class="nk1-content">
      <form class="nl-form" id="newsletterForm" action="subscribe.php" method="post">
    <span class="nl-icon" aria-hidden="true">
         <svg viewBox="0 0 24 24">
                                    <path
                                        d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5Z" />
                                </svg>
    </span>
    <input type="email" name="email" class="nl-input" placeholder="Your email" required>
    <button type="submit" id="subscribeBtn" class="nl-btn">Subscribe</button>
</form>
          </div>

        </div>
      </div>
    </div>
  </section>
  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="footer1">
          <h3 class="brand-title">
            <img class="brand-logo" src="./assets/images/logo.svg" alt="Travelo" />
            Travelo
          </h3>
          <p class="brand-desc">
            Travelo makes travel easy and enjoyable. Find flights, hotels, and bookings all in one place.
          </p>

          <div class="linksfoot">
            <a href="#">Westbank, PS</a>
            <a href="#">+972 59-260-2379</a>
            <a href="#">traveloa9@gmail.com</a>
          </div>
        </div>

        <div class="footer-links">
          <div class="footer-link">
            <h3>Products</h3>
            <a href="./fligths.php">Flights</a>
            <a href="./hotel.php">Hotels</a>
            <a href="#">Car Rentals</a>
            <a href="./packages.php">Travel Packages</a>
          </div>

          <div class="footer-link">
            <h3>Useful Links</h3>
            <a href="./TravelAdvisories.html">Travel-Advisories</a>
            <a href="./support.html">Support</a>
            <a href="./privacy.html">Privacy Policy</a>
            <a href="./terms.html">Terms &amp; Conditions</a>
          </div>

          <div class="footer-link1">
            <h3>Other</h3>
            <a href="./about.html">About Travelo</a>
            <a href="./stores.html">Traveler Stories</a>
            <a href="./blogs.php">Blog</a>
            <a href="./faqs.html">FAQ</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <div class="endfoot">
    <div class="container">
      <div class="footer-end">
        <h3 class="copy">© 2025 Travelo. All Rights Reserved — Developed by Ameer & Zeina.</h3>
        <div class="footicon">
          <a href="#"><img src="./assets/images/Group.svg" alt="twitter"></a>
          <a href="#"><img src="./assets/images/Group 7.svg" alt="facebook"></a>
          <a href="#"><img src="./assets/images/Frame 86.svg" alt="instagram"></a>
        </div>
      </div>
    </div>
  </div>
  <img class="effect1" src="./assets/images/hero-img/Ellipse 23.png">
  <img class="effect2" src="assets/images/hero-img/Ellipse 24.jpg">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
 
</script>
   <script src="./assets/js/home.js"></script>
<script src="./assets/js/destination.js"></script>
  <!-- Toast -->
<div id="toast" class="toast"></div>
<style>
.toast {
  position: fixed;
  bottom: 30px;
  left: 50%;              
  transform: translateX(-50%) translateY(20px); 
  min-width: 260px;
  max-width: 360px;
  padding: 14px 18px;
  background: #16a34a;
  color: #fff;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 10px 25px rgba(0,0,0,.15);
  opacity: 0;
  transition: all .3s ease;
  z-index: 9999;
}


.toast.show {
  opacity: 1;
  transform: translateY(0);
}

.toast.error {
  background: #ef4444;
}

</style>
<script>
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');

  toast.textContent = message;
  toast.className = 'toast show';

  if (type === 'error') toast.classList.add('error');
  if (type === 'warn') toast.classList.add('warn');

  setTimeout(() => {
    toast.classList.remove('show');
  }, 2200);
}
</script>
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
      // لو محمّل قبل لا تعيديه
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
        // لانشر Jotform (جربي عدة سلكترات)
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

    btn.addEventListener("click", async () => {
      // ✅ اخفي زرّك فورًا بعد أول كبسة
      btn.style.display = "none";

      try{
        await loadWidget();           // ✅ حمّلي الشات بوت الآن (كان مخفي قبل)
        await openLauncherWhenReady(); // ✅ افتحيه تلقائيًا
      }catch(e){
        console.error(e);
        // لو صار خطأ، رجّعي الزر حتى ما يختفي على الفاضي
        btn.style.display = "";
        alert("AI widget failed to load.");
      }
    }, { once:true }); // ✅ يمنع تعدد الكبس/تكرار
  });
})();
</script>


</body>
</html>