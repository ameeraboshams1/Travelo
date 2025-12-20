<?php
// ================== DB CONNECTION (PDO) ==================
$host     = 'localhost';
$dbname   = 'travelo';
$username = 'root';
$password = '';
session_start();

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
  die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

// ================== HELPERS ==================
function mapBadgeToCategory($badge)
{
  $b = strtolower((string)$badge);

  if (strpos($b, 'adventure') !== false) return 'adventure';
  if (strpos($b, 'beach') !== false || strpos($b, 'sea') !== false) return 'beach';
  if (strpos($b, 'city') !== false) return 'city';
  if (strpos($b, 'hike') !== false) return 'hiking';
  if (strpos($b, 'museum') !== false) return 'museum';
  if (strpos($b, 'culture') !== false) return 'cultural';
  if (strpos($b, 'luxury') !== false || strpos($b, 'relax') !== false) return 'relax';

  return 'adventure';
}

function fakeRating($id)
{
  $base = 4.3 + ($id % 6) * 0.1;
  if ($base > 4.9) $base = 4.9;
  return $base;
}

function fakeReviews($id)
{
  return 80 + (($id * 17) % 250);
}

function hasTable(PDO $pdo, $table)
{
  $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
  $st = $pdo->prepare($sql);
  $st->execute([$table]);
  return (int)$st->fetchColumn() > 0;
}

function hasColumn(PDO $pdo, $table, $col)
{
  $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
  $st = $pdo->prepare($sql);
  $st->execute([$table, $col]);
  return (int)$st->fetchColumn() > 0;
}

// ================== DETECT OPTIONAL STRUCTURE ==================
$hasPackages = hasTable($pdo, 'packages');
if (!$hasPackages) die("Table 'packages' not found.");

$packagesHasHotelId  = hasColumn($pdo, 'packages', 'hotel_id');
$packagesHasFlightId = hasColumn($pdo, 'packages', 'flight_id');

$hasHotels  = hasTable($pdo, 'hotels');
$hasFlights = hasTable($pdo, 'flights');

// flights columns (try to detect)
$flightCols = [
  'airline_name' => ['airline_name','airline','carrier','company','airline_company'],
  'flight_no'    => ['flight_no','flight_number','flight_num','number','flightcode'],
  'from_city'    => ['from_city','origin_city','from','origin','departure_city','source_city'],
  'to_city'      => ['to_city','destination_city','to','destination','arrival_city','dest_city'],
  'depart_at'    => ['departure_datetime','depart_datetime','departure_time','depart_time','depart_at','departure_at'],
  'arrive_at'    => ['arrival_datetime','arrive_datetime','arrival_time','arrive_time','arrive_at','arrival_at'],
];

$detectedFlight = [
  'airline_name' => null,
  'flight_no'    => null,
  'from_city'    => null,
  'to_city'      => null,
  'depart_at'    => null,
  'arrive_at'    => null,
];

if ($hasFlights && $packagesHasFlightId) {
  foreach ($flightCols as $key => $cands) {
    foreach ($cands as $c) {
      if (hasColumn($pdo, 'flights', $c)) { $detectedFlight[$key] = $c; break; }
    }
  }
}

// hotels columns (based on your screenshot)
$hotelCols = [
  'name'               => 'name',
  'rating'             => 'rating',
  'reviews_count'      => 'reviews_count',
  'price_per_night'    => 'price_per_night',
  'currency'           => 'currency',
  'has_parking'        => 'has_parking',
  'has_attached_bathroom' => 'has_attached_bathroom',
  'has_cctv'           => 'has_cctv',
  'has_wifi'           => 'has_wifi',
  'has_sea_view'       => 'has_sea_view',
  'has_city_view'      => 'has_city_view',
  'has_free_breakfast' => 'has_free_breakfast',
  'pay_at_hotel'       => 'pay_at_hotel',
  'couple_friendly'    => 'couple_friendly',
  'pet_friendly'       => 'pet_friendly',
  'airport_shuttle'    => 'airport_shuttle',
];

$hotelDetected = [];
if ($hasHotels && $packagesHasHotelId) {
  foreach ($hotelCols as $alias => $col) {
    $hotelDetected[$alias] = hasColumn($pdo, 'hotels', $col) ? $col : null;
  }
}

// ================== FETCH PACKAGES (with joins) ==================
try {
  $select = "
    SELECT
      p.id,
      p.title,
      p.destination_id,
      p.location,
      p.from_city,
      p.duration_days,
      p.price_usd,
      p.badge_type,
      p.image_url
  ";

  $join = "";

  // ---- Hotel join ----
  if ($packagesHasHotelId && $hasHotels) {
    $join .= " LEFT JOIN hotels h ON h.id = p.hotel_id ";

    // safe selects
    $select .= ", " . ($hotelDetected['name'] ? "h.`{$hotelDetected['name']}`" : "''") . " AS hotel_name";
    $select .= ", " . ($hotelDetected['rating'] ? "h.`{$hotelDetected['rating']}`" : "NULL") . " AS hotel_rating";
    $select .= ", " . ($hotelDetected['reviews_count'] ? "h.`{$hotelDetected['reviews_count']}`" : "NULL") . " AS hotel_reviews";
    $select .= ", " . ($hotelDetected['price_per_night'] ? "h.`{$hotelDetected['price_per_night']}`" : "NULL") . " AS hotel_price_per_night";
    $select .= ", " . ($hotelDetected['currency'] ? "h.`{$hotelDetected['currency']}`" : "'USD'") . " AS hotel_currency";

    foreach (['has_parking','has_attached_bathroom','has_cctv','has_wifi','has_sea_view','has_city_view','has_free_breakfast','pay_at_hotel','couple_friendly','pet_friendly','airport_shuttle'] as $flag) {
      $col = $hotelDetected[$flag] ?? null;
      $select .= ", " . ($col ? "h.`$col`" : "0") . " AS hotel_$flag";
    }
  } else {
    // fallback no hotel
    $select .= ", '' AS hotel_name, NULL AS hotel_rating, NULL AS hotel_reviews, NULL AS hotel_price_per_night, 'USD' AS hotel_currency";
    foreach (['has_parking','has_attached_bathroom','has_cctv','has_wifi','has_sea_view','has_city_view','has_free_breakfast','pay_at_hotel','couple_friendly','pet_friendly','airport_shuttle'] as $flag) {
      $select .= ", 0 AS hotel_$flag";
    }
  }

  // ---- Flight join ----
  if ($packagesHasFlightId && $hasFlights) {
    $join .= " LEFT JOIN flights f ON f.id = p.flight_id ";

    $select .= ", " . ($detectedFlight['airline_name'] ? "f.`{$detectedFlight['airline_name']}`" : "''") . " AS flight_airline";
    $select .= ", " . ($detectedFlight['flight_no'] ? "f.`{$detectedFlight['flight_no']}`" : "''") . " AS flight_no";
    $select .= ", " . ($detectedFlight['from_city'] ? "f.`{$detectedFlight['from_city']}`" : "''") . " AS flight_from";
    $select .= ", " . ($detectedFlight['to_city'] ? "f.`{$detectedFlight['to_city']}`" : "''") . " AS flight_to";
    $select .= ", " . ($detectedFlight['depart_at'] ? "f.`{$detectedFlight['depart_at']}`" : "NULL") . " AS flight_depart_at";
    $select .= ", " . ($detectedFlight['arrive_at'] ? "f.`{$detectedFlight['arrive_at']}`" : "NULL") . " AS flight_arrive_at";
  } else {
    $select .= ", '' AS flight_airline, '' AS flight_no, '' AS flight_from, '' AS flight_to, NULL AS flight_depart_at, NULL AS flight_arrive_at";
  }

  $sql = $select . "
    FROM packages p
    $join
    ORDER BY p.id ASC
  ";

  $stmt = $pdo->query($sql);
  $packages = $stmt->fetchAll();
} catch (PDOException $e) {
  die('Query error: ' . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Tour Package</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <link rel="stylesheet" href="./assets/css/home.css" />
  <link rel="stylesheet" href="./assets/css/packages.css" />
  
</script>
<style>
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
</style>
  <!-- TRAVELO user info للـ JS -->
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
  <!-- ============ NAVBAR ============ -->
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
            <li><a href="./packages.php" class="active">Packages</a></li>
            <li><a href="./destination.php">Destinations</a></li>
          </ul>
        </div>

        <div class="nav-button">
             <button id="darkModeToggle" class="dark-mode-toggle" type="button" aria-label="Toggle dark mode">
    <i class="bi bi-moon-fill" id="darkModeIcon"></i>
  </button>
          <?php if (isset($_SESSION['user_id'])): ?>
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

  <div class="spinner-overlay" id="spinner">
    <div class="spinner"></div>
  </div>

  <div class="page">
    <!-- ============ HERO ============ -->
    <section class="hero">
      <div class="hero-banner" data-animate>
        <div class="hero-overlay"></div>
        <div class="hero-text">
          <h1>Tour Package</h1>
          <p class="hero-breadcrumb">Home <span>/ Tour Package</span></p>
        </div>
      </div>

      <div class="container">
        <form class="hero-search-box" id="heroSearch" data-animate>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Destination</label>
              <div class="input-with-icon">
                <input type="text" class="form-input hero-input" name="destination" placeholder="Where to go?" />
                <i class="fa-solid fa-location-dot"></i>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Type</label>
              <div class="select-with-icon">
                <select class="form-select hero-select" name="type">
                  <option selected disabled>Activity</option>
                  <option value="adventure">Adventure</option>
                  <option value="relax">Relax</option>
                  <option value="city">City Tour</option>
                  <option value="cultural">Cultural</option>
                </select>
                <i class="fa-solid fa-briefcase"></i>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">When</label>
              <div class="input-with-icon">
                <input type="date" class="form-input hero-input" name="date" />
                <i class="fa-solid fa-calendar"></i>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Guests</label>
              <div class="select-with-icon">
                <select class="form-select hero-select" name="guests">
                  <option>0</option><option>1</option><option>2</option><option>3</option><option>4+</option>
                </select>
                <i class="fa-solid fa-user"></i>
              </div>
            </div>

            <button class="form-button hero-button" type="submit">
              Search <span class="btn-ripple"></span>
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- ============ MAIN ============ -->
    <main class="main">
      <div class="container main-layout">
        <!-- ============ SIDEBAR ============ -->
        <aside class="sidebar" data-animate>
          <div class="sidebar-section">
            <div class="sidebar-title"><i class="fa-solid fa-magnifying-glass"></i> Search</div>
            <div class="search-box">
              <input type="text" id="sidebarSearch" placeholder="Type anything..." />
              <i class="fa-solid fa-magnifying-glass"></i>
            </div>
          </div>

          <div class="sidebar-section">
            <div class="sidebar-title"><i class="fa-solid fa-sliders"></i> Filter By Price</div>
            <div class="sidebar-range">
              <span id="priceRangeText">Selected range: $0 - $0</span>
            </div>
            <input type="range" id="priceSlider" min="0" max="0" value="0" />
            <button class="btn-apply" id="applyPrice" type="button">Submit</button>
          </div>

          <div class="sidebar-section" id="categoriesList">
            <div class="sidebar-title"><i class="fa-solid fa-list"></i> Categories</div>
            <ul class="sidebar-list">
              <li><label><span><input type="checkbox" data-categories="all" checked /> All Tours</span><span class="count">0</span></label></li>
              <li><label><span><input type="checkbox" data-categories="adventure" /> Adventure</span><span class="count">0</span></label></li>
              <li><label><span><input type="checkbox" data-categories="beach" /> Beaches</span><span class="count">0</span></label></li>
              <li><label><span><input type="checkbox" data-categories="city" /> City Tours</span><span class="count">0</span></label></li>
              <li><label><span><input type="checkbox" data-categories="hiking" /> Hiking</span><span class="count">0</span></label></li>
              <li><label><span><input type="checkbox" data-categories="museum" /> Museum Tours</span><span class="count">0</span></label></li>
            </ul>
          </div>

          <div class="sidebar-section" id="durationList">
            <div class="sidebar-title"><i class="fa-regular fa-clock"></i> Duration</div>
            <ul class="sidebar-list">
              <li><label><span><input type="checkbox" data-duration="day" /> 1 Day</span><span class="count">0</span></label></li>
              <li><label><span><input type="checkbox" data-duration="weekend" /> 2–3 Days</span><span class="count">0</span></label></li>
              <li><label><span><input type="checkbox" data-duration="week" /> 4–7 Days</span><span class="count">0</span></label></li>
              <li><label><span><input type="checkbox" data-duration="extended" /> 8+ Days</span><span class="count">0</span></label></li>
            </ul>
          </div>

          <div class="sidebar-section" id="ratingList">
            <div class="sidebar-title"><i class="fa-solid fa-star"></i> Reviews</div>
            <ul class="sidebar-list">
              <li><label><span><input type="checkbox" data-rating="5" /> <i class="fa-solid fa-star"></i> 5 Stars &amp; Up</span><span class="count">0</span></label></li>
              <li><label><span><input type="checkbox" data-rating="4" /> <i class="fa-solid fa-star"></i> 4 &amp; Up</span><span class="count">0</span></label></li>
              <li><label><span><input type="checkbox" data-rating="3" /> <i class="fa-solid fa-star"></i> 3 &amp; Up</span><span class="count">0</span></label></li>
            </ul>
          </div>
        </aside>

        <!-- ============ LIST ============ -->
        <section class="tour-section">
          <div class="list-top-bar" data-animate>
            <h2><i class="fa-solid fa-suitcase-rolling"></i> <span id="tourCount"><?php echo count($packages); ?></span> Tours</h2>
            <div class="list-top-meta">
              <span>Sort by</span>
              <select id="sortSelect">
                <option value="featured">Featured</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
                <option value="rating">Rating</option>
                <option value="popular">Popular</option>
              </select>
            </div>
          </div>

          <div class="tour-grid" id="tourGrid">
            <?php if (!empty($packages)): ?>
              <?php foreach ($packages as $pkg): ?>
                <?php
                  $id        = (int)($pkg['id'] ?? 0);
                  $title     = (string)($pkg['title'] ?? '');
                  $location  = (string)($pkg['location'] ?? '');
                  $fromCity  = (string)($pkg['from_city'] ?? '');
                  $duration  = (int)($pkg['duration_days'] ?? 0);
                  $price     = (float)($pkg['price_usd'] ?? 0);
                  $badge     = (string)($pkg['badge_type'] ?? '');
                  $imageUrl  = (string)($pkg['image_url'] ?? '');
                  $destId    = (int)($pkg['destination_id'] ?? 0);

                  $category  = mapBadgeToCategory($badge);
                  $rating    = fakeRating($id);
                  $reviews   = fakeReviews($id);

                  $nights    = max(1, $duration);
                  $comboText = $badge ?: 'Flight + Hotel';
                  $currency  = 'USD';

                  $hotelName = (string)($pkg['hotel_name'] ?? '');
                  $hotelRating = $pkg['hotel_rating'];
                  $hotelReviews = $pkg['hotel_reviews'];
                  $hotelPriceNight = $pkg['hotel_price_per_night'];
                  $hotelCur = (string)($pkg['hotel_currency'] ?? 'USD');

                  $flightAirline = (string)($pkg['flight_airline'] ?? '');
                  $flightNo      = (string)($pkg['flight_no'] ?? '');
                  $flightFrom    = (string)($pkg['flight_from'] ?? '');
                  $flightTo      = (string)($pkg['flight_to'] ?? '');
                  $flightDepart  = (string)($pkg['flight_depart_at'] ?? '');
                  $flightArrive  = (string)($pkg['flight_arrive_at'] ?? '');

                  if (!$imageUrl) {
                    $imageUrl = "https://images.pexels.com/photos/248797/pexels-photo-248797.jpeg?auto=compress&cs=tinysrgb&w=1600";
                  }

                  $hotelFlags = [
                    'has_wifi','has_free_breakfast','has_parking','has_city_view','has_sea_view','airport_shuttle',
                    'has_attached_bathroom','has_cctv','pay_at_hotel','couple_friendly','pet_friendly'
                  ];
                ?>
                <article class="tour-card" data-animate
                  data-price="<?= htmlspecialchars((string)$price, ENT_QUOTES) ?>"
                  data-rating="<?= htmlspecialchars((string)$rating, ENT_QUOTES) ?>"
                  data-duration="<?= htmlspecialchars((string)$duration, ENT_QUOTES) ?>"
                  data-category="<?= htmlspecialchars((string)$category, ENT_QUOTES) ?>"
                  data-destination-id="<?= (int)$destId ?>"

                  data-package-id="<?= (int)$id ?>"
                  data-title="<?= htmlspecialchars($title, ENT_QUOTES) ?>"
                  data-city="<?= htmlspecialchars($location, ENT_QUOTES) ?>"
                  data-nights="<?= (int)$nights ?>"
                  data-combo="<?= htmlspecialchars($comboText, ENT_QUOTES) ?>"
                  data-currency="<?= htmlspecialchars($currency, ENT_QUOTES) ?>"

                  data-hotel-name="<?= htmlspecialchars($hotelName, ENT_QUOTES) ?>"
                  data-hotel-rating="<?= htmlspecialchars((string)($hotelRating ?? ''), ENT_QUOTES) ?>"
                  data-hotel-reviews="<?= htmlspecialchars((string)($hotelReviews ?? ''), ENT_QUOTES) ?>"
                  data-hotel-price-night="<?= htmlspecialchars((string)($hotelPriceNight ?? ''), ENT_QUOTES) ?>"
                  data-hotel-currency="<?= htmlspecialchars($hotelCur, ENT_QUOTES) ?>"

                  <?php foreach ($hotelFlags as $f): ?>
                    data-<?= str_replace('_','-',$f) ?>="<?= (int)($pkg["hotel_$f"] ?? 0) ?>"
                  <?php endforeach; ?>

                  data-flight-airline="<?= htmlspecialchars($flightAirline, ENT_QUOTES) ?>"
                  data-flight-no="<?= htmlspecialchars($flightNo, ENT_QUOTES) ?>"
                  data-flight-from="<?= htmlspecialchars($flightFrom, ENT_QUOTES) ?>"
                  data-flight-to="<?= htmlspecialchars($flightTo, ENT_QUOTES) ?>"
                  data-flight-depart="<?= htmlspecialchars($flightDepart, ENT_QUOTES) ?>"
                  data-flight-arrive="<?= htmlspecialchars($flightArrive, ENT_QUOTES) ?>"
                >
                  <div class="tour-card-image">
                    <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($title, ENT_QUOTES) ?>">
                    <div class="tour-badge"><?= htmlspecialchars($badge) ?></div>
                    <div class="tour-heart"><i class="fa-regular fa-heart"></i></div>
                  </div>

                  <div class="tour-card-body">
                    <div class="tour-location"><?= htmlspecialchars($location) ?></div>
                    <div class="tour-title"><?= htmlspecialchars($title) ?></div>

                    <?php if (!empty($hotelName)): ?>
                      <div class="tour-hotel-line" style="display:flex;align-items:center;gap:8px;font-size:12px;color:#6b7280;">
                        <i class="fa-solid fa-hotel" style="color:#b049f1;"></i>
                        <span><?= htmlspecialchars($hotelName) ?></span>
                      </div>
                    <?php endif; ?>

                    <div class="tour-meta">
                      <div class="tour-meta-left">
                        <span><i class="fa-solid fa-star"></i><?= number_format((float)$rating, 1) ?> (<?= (int)$reviews ?>)</span>
                        <span><i class="fa-regular fa-clock"></i><?= $duration > 0 ? ((int)$duration . ' Days') : 'Flexible' ?></span>
                      </div>
                      <div class="tour-price">$<?= number_format((float)$price, 2) ?></div>
                    </div>
                  </div>

                  <div class="tour-footer">
                    <span>From <?= htmlspecialchars($fromCity) ?></span>
                    <div class="tour-footer-actions">
                      <button type="button" class="view-details">View Details</button>
                      <button type="button" class="book-package-btn">BOOK NOW</button>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No tour packages found.</p>
            <?php endif; ?>
          </div>

          <div class="pagination" data-animate>
            <div class="page-link active" data-type="page" data-page="1">1</div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <div id="toast" class="toast"></div>

  <script src="./assets/js/home.js"></script>
  <script src="./assets/js/packages.js"></script>

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
