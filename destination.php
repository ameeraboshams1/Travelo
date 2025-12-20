<?php
require __DIR__ . '/db.php';
session_start();

/* ===== Pagination settings ===== */
$perPage = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

/* ===== Read category filter from URL ===== */
$category = $_GET['category'] ?? 'all';

/* عدّلي القائمة حسب القيم الحقيقية بعمود category عندك */
$allowed = ['all', 'city', 'mountain', 'forest', 'island'];
if (!in_array($category, $allowed, true)) $category = 'all';

/* ===== Build WHERE + params ===== */
$where = "WHERE is_active = 1";
$params = [];

if ($category !== 'all') {
  $where .= " AND category = :cat";
  $params[':cat'] = $category;
}

/* ===== Count total AFTER filter ===== */
$countSql = "SELECT COUNT(*) FROM destinations $where";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();

$totalPages = (int)ceil($total / $perPage);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;

/* ===== Fetch only page results AFTER filter ===== */
$sql = "SELECT id, name, city, country, category, image_url, short_desc, base_price
        FROM destinations
        $where
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$destinations = $stmt->fetchAll();

/* ===== Helper: keep query params while changing page ===== */
function pageUrl(int $p): string {
  $params = $_GET;
  $params['page'] = $p;
  return '?' . http_build_query($params);
}

function filterUrl(string $cat, int $p = 1): string {
  $params = $_GET;
  $params['category'] = $cat;
  $params['page'] = $p;
  return '?' . http_build_query($params);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Destinations – Travelo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />

  <link rel="stylesheet" href="./assets/css/home.css">
  <link rel="stylesheet" href="./assets/css/destination.css">
  
    <style>
    /* ===== Travelo User Chip (Premium Design) ===== */
.nav-user {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
  z-index: 100;
}

/* الزر الرئيسي - نسخة أكثر أناقة */
.nav-button .user-toggle {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 5px 16px 5px 8px;
  border-radius: 999px;
  border: none;
  outline: none;
  background: rgba(255, 255, 255, 0.92);
  cursor: pointer;
  font-family: "Plus Jakarta Sans", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #0f172a;
  box-shadow: 
    0 4px 12px rgba(15, 23, 42, 0.08),
    0 0 0 1px rgba(255, 255, 255, 0.3) inset;
  backdrop-filter: blur(12px) saturate(180%);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

/* تأثير توهج خفيف عند التحويم */
.nav-button .user-toggle::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
}

.nav-button .user-toggle:hover {
  transform: translateY(-1.5px);
  box-shadow: 
    0 12px 28px rgba(15, 23, 42, 0.14),
    0 0 0 1px rgba(255, 255, 255, 0.4) inset;
  background: rgba(255, 255, 255, 0.98);
}

.nav-button .user-toggle:active {
  transform: translateY(0);
  transition-duration: 0.1s;
}

/* الأفاتار الدائري - تصميم متطور */
.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 999px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  box-shadow: 0 3px 8px rgba(102, 126, 234, 0.3);
  position: relative;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.user-avatar::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transform: translateX(-100%);
}

.nav-button .user-toggle:hover .user-avatar {
  transform: scale(1.05) rotate(5deg);
}

.nav-button .user-toggle:hover .user-avatar::after {
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}

/* النص */
.user-text {
  white-space: nowrap;
  color: #0f172a;
  font-weight: 600;
  font-size: 14px;
  letter-spacing: -0.01em;
  position: relative;
}

/* السهم */
.user-toggle i {
  font-size: 12px;
  color: #94a3b8;
  transition: transform 0.3s ease;
  margin-left: 2px;
}

.user-toggle.show-menu i {
  transform: rotate(180deg);
}

/* القائمة المنسدلة - نسخة أكثر تطوراً */
.user-menu {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  min-width: 200px;
  background: rgba(255, 255, 255, 0.98);
  border-radius: 16px;
  padding: 8px 0;
  display: none;
  z-index: 1000;
  box-shadow: 
    0 20px 60px rgba(15, 23, 42, 0.18),
    0 0 0 1px rgba(255, 255, 255, 0.1) inset;
  backdrop-filter: blur(20px);
  opacity: 0;
  transform: translateY(-10px);
  animation: menuFadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
  overflow: hidden;
}

@keyframes menuFadeIn {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* حدود ناعمة للقائمة */
.user-menu::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(124, 58, 237, 0.2), transparent);
}

.user-menu a,
.user-menu form button {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  text-align: left;
  padding: 10px 18px;
  font-size: 14px;
  font-weight: 500;
  font-family: "Plus Jakarta Sans", system-ui, sans-serif;
  background: transparent;
  border: none;
  cursor: pointer;
  color: #475569;
  transition: all 0.2s ease;
  position: relative;
}

/* أيقونات داخل القائمة */
.user-menu a i,
.user-menu form button i {
  width: 18px;
  color: #94a3b8;
  font-size: 15px;
}

.user-menu a:hover,
.user-menu form button:hover {
  background: linear-gradient(90deg, rgba(124, 58, 237, 0.08), transparent);
  color: #7c3aed;
  padding-left: 22px;
}

.user-menu a:hover i,
.user-menu form button:hover i {
  color: #7c3aed;
  transform: scale(1.1);
}

/* فاصل أنيق بين العناصر */
.user-menu hr {
  border: none;
  height: 1px;
  background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
  margin: 6px 16px;
}

.user-menu.show {
  display: block;
}

/* تأثير عند فتح القائمة */
.user-menu.show ~ .nav-button .user-toggle {
  box-shadow: 
    0 8px 24px rgba(15, 23, 42, 0.12),
    0 0 0 1px rgba(124, 58, 237, 0.1) inset;
  background: rgba(255, 255, 255, 1);
}




/* =========================
   Travelo Premium Pagination
   ========================= */
.travelo-pagination{
  margin-top: 26px !important;
}

.travelo-pagination .pagination{
  gap: 14px;
  align-items: center;
}

/* كل زر */
.travelo-pagination .page-link{
  width: 62px;
  height: 56px;
  display: grid;
  place-items: center;

  border-radius: 16px;
  border: 1px solid rgba(124, 58, 237, .14);
  background: rgba(255,255,255,.92);

  color: #4c1d95;
  font-weight: 800;
  font-size: 18px;
  letter-spacing: -0.02em;

  box-shadow:
    0 16px 36px rgba(15,23,42,.10),
    0 0 0 1px rgba(255,255,255,.40) inset;

  backdrop-filter: blur(14px) saturate(160%);
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
}

/* إزالة حواف bootstrap الافتراضية */
.travelo-pagination .page-item:first-child .page-link,
.travelo-pagination .page-item:last-child .page-link{
  border-top-left-radius: 16px;
  border-bottom-left-radius: 16px;
  border-top-right-radius: 16px;
  border-bottom-right-radius: 16px;
}

/* Hover فخم */
.travelo-pagination .page-link:hover{
  transform: translateY(-2px);
  border-color: rgba(124, 58, 237, .28);
  background: rgba(255,255,255,.98);
  box-shadow:
    0 22px 52px rgba(15,23,42,.14),
    0 0 0 1px rgba(124, 58, 237, .10) inset;
}

/* Active (الصفحة الحالية) */
.travelo-pagination .page-item.active .page-link{
  background: linear-gradient(135deg, #7c3aed, #6c63ff);
  border-color: transparent;
  color: #fff;

  box-shadow:
    0 24px 60px rgba(124, 58, 237, .30),
    0 10px 30px rgba(108, 99, 255, .18);
  transform: translateY(-1px);
}

/* Prev/Next */
.travelo-pagination .page-item .page-link[aria-label="Previous"],
.travelo-pagination .page-item .page-link[aria-label="Next"]{
  font-size: 20px;
  font-weight: 900;
}

/* Disabled */
.travelo-pagination .page-item.disabled .page-link{
  opacity: .45;
  cursor: not-allowed;
  transform: none;
  box-shadow:
    0 10px 22px rgba(15,23,42,.08),
    0 0 0 1px rgba(255,255,255,.35) inset;
}

/* نقاط ... */
.travelo-pagination .page-item.disabled .page-link{
  background: rgba(255,255,255,.78);
}

/* موبايل */
@media (max-width: 576px){
  .travelo-pagination .pagination{ gap: 10px; }
  .travelo-pagination .page-link{
    width: 52px; height: 48px;
    border-radius: 14px;
    font-size: 16px;
  }
}
/* النسخة المصقولة جداً */
.travelo-pagination {
    margin: 24px 0 !important;
}

.travelo-pagination .pagination {
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.travelo-pagination .page-link {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    color: #475569;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: border-color 0.15s ease;
}

.travelo-pagination .page-link:hover {
    border-color: #94a3b8;
}

.travelo-pagination .page-item.active .page-link {
    background: #4f46e5;
    border-color: #4f46e5;
    color: #ffffff;
}

.travelo-pagination .page-item.disabled .page-link {
    opacity: 0.4;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .travelo-pagination .pagination {
        padding: 8px;
        gap: 6px;
    }
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
<body class="destination-page">
  <div class="spinner-overlay" id="spinner">
    <div class="spinner"></div>
  </div>

  <!-- NAVBAR -->
  <section class="nav-wrapper">
    <div class="container">
      <nav class="nav">
        <div class="logo">
          <img class="img-logo" src="./assets/images/logo.svg" alt="Travelo Logo">
          <a href="index.php">Travelo</a>
        </div>

        <div class="nav-links">
          <ul class="nav-links-ul">
            <li><a href="./index.php">Home</a></li>
            <li><a href="./fligths.php">Flights</a></li>
            <li><a href="./hotel.php">Hotels</a></li>
            <li><a href="./packages.php">Packages</a></li>
            <li><a href="./destination.php" class="active">Destinations</a></li>
          </ul>
        </div>

        <div class="nav-button">
          <?php if (isset($_SESSION['user_id'])): ?>
            <!-- ====== Logged-in state ====== -->
            <div class="nav-user">
              <button type="button" class="user-toggle" id="userMenuToggle">
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


  <!-- HERO / BANNER -->
  <section class="destination-banner"
           style="background-image: url('./assets/images/ameer.png');
                  background-size: cover;
                  background-position: center;
                  background-repeat: no-repeat;">
    <div class="destination-banner-overlay">
      <div class="destination-banner-content">
        <p class="banner-label">TOP DESTINATIONS</p>
        <h1 class="banner-title">Discover your next trip</h1>
        <p class="banner-subtitle">Browse all destinations picked specially for you</p>
      </div>
    </div>
  </section>

  <main class="destination-main">
    <div class="top-wrapper">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
        <div>
          <h1 class="section-title mb-0">Explore top destination</h1>
        </div>

        <!-- الفلاتر حسب الكاتيجوري -->
        <div class="category-tabs">
  <a class="category-btn <?= $category==='all'?'active':'' ?>" href="<?= htmlspecialchars(filterUrl('all', 1)) ?>">All</a>
  <a class="category-btn <?= $category==='city'?'active':'' ?>" href="<?= htmlspecialchars(filterUrl('city', 1)) ?>">City</a>
  <a class="category-btn <?= $category==='mountain'?'active':'' ?>" href="<?= htmlspecialchars(filterUrl('mountain', 1)) ?>">Mountain</a>
  <a class="category-btn <?= $category==='forest'?'active':'' ?>" href="<?= htmlspecialchars(filterUrl('forest', 1)) ?>">Forest</a>
  <a class="category-btn <?= $category==='island'?'active':'' ?>" href="<?= htmlspecialchars(filterUrl('island', 1)) ?>">Island</a>
</div>

      </div>

      <!-- CARDS من الداتا بيس -->
      <div class="row g-4">
        <?php if (!empty($destinations)): ?>
          <?php foreach ($destinations as $dest): ?>
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
                    <!-- حالياً ثابت، لاحقاً بنربطه مع ريتينغ من الداشبورد -->
                    <span>5.0</span>
                  </div>
                </div>

                <div class="card-content">
                  <div class="destination-city">
                    <?= htmlspecialchars($dest['name']) ?>
                  </div>

                  <div class="destination-desc">
                    <?= htmlspecialchars($dest['short_desc']) ?>
                  </div>

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
                        data-rating="5.0"
                        >
                        See More
                      </button>

                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <p>No destinations available right now.</p>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($totalPages > 1): ?>
  <nav class="travelo-pagination mt-4 d-flex justify-content-center" aria-label="Destinations pagination">
    <ul class="pagination pagination-lg">

      <!-- Prev -->
      <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= ($page <= 1) ? '#' : htmlspecialchars(pageUrl($page - 1)) ?>" aria-label="Previous">
          &laquo;
        </a>
      </li>

      <?php
        // عرض صفحات بشكل مرتب مع ...
        $window = 2; // كم صفحة حول الصفحة الحالية
        $start = max(1, $page - $window);
        $end   = min($totalPages, $page + $window);

        // أول صفحة + نقاط إذا بعيد
        if ($start > 1) {
          echo '<li class="page-item"><a class="page-link" href="'.htmlspecialchars(pageUrl(1)).'">1</a></li>';
          if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }

        // صفحات الوسط
        for ($i = $start; $i <= $end; $i++) {
          $active = ($i === $page) ? 'active' : '';
          echo '<li class="page-item '.$active.'">';
          echo '<a class="page-link" href="'.htmlspecialchars(pageUrl($i)).'">'.$i.'</a>';
          echo '</li>';
        }

        // آخر صفحة + نقاط إذا بعيد
        if ($end < $totalPages) {
          if ($end < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
          echo '<li class="page-item"><a class="page-link" href="'.htmlspecialchars(pageUrl($totalPages)).'">'.$totalPages.'</a></li>';
        }
      ?>

      <!-- Next -->
      <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= ($page >= $totalPages) ? '#' : htmlspecialchars(pageUrl($page + 1)) ?>" aria-label="Next">
          &raquo;
        </a>
      </li>

    </ul>
  </nav>
<?php endif; ?>

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
  </main>

  <!-- FOOTER -->
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
            <a href="#">Tulkarm, PS</a>
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
            <a href="./blogs.html">Blog</a>
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

  <script src="./assets/js/home.js"></script>
  <script src="./assets/js/destination.js"></script>

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