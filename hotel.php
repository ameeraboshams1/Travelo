<?php
require_once __DIR__ . '/db.php';
session_start();

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // نجيب كل الفنادق مع معلومات الديستنيشن + صورة أساسية + كل الصور (pipe separated)
    $stmt = $pdo->query("
        SELECT
            h.*,
            d.city AS destination_city,
            d.country AS destination_country,
            COALESCE(
                (
                    SELECT hi.image_url
                    FROM hotel_images hi
                    WHERE hi.hotel_id = h.id
                      AND hi.is_primary = 1
                    ORDER BY hi.sort_order ASC, hi.id ASC
                    LIMIT 1
                ),
                d.image_url
            ) AS primary_image,
            (
                SELECT GROUP_CONCAT(hi.image_url ORDER BY hi.is_primary DESC, hi.sort_order ASC, hi.id ASC SEPARATOR '|')
                FROM hotel_images hi
                WHERE hi.hotel_id = h.id
            ) AS images_list
        FROM hotels h
        JOIN destinations d ON d.id = h.destination_id
        WHERE h.is_active = 1
        ORDER BY h.created_at DESC
    ");
    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalHotels = count($hotels);

    if ($totalHotels > 0) {
        $prices   = array_map(fn($h) => (float)$h['price_per_night'], $hotels);
        $minPrice = min($prices);
        $maxPrice = max($prices);
    } else {
        $minPrice = 0;
        $maxPrice = 0;
    }
} catch (PDOException $e) {
    $dbError     = $e->getMessage();
    $hotels      = [];
    $totalHotels = 0;
    $minPrice    = 0;
    $maxPrice    = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Hotels List – Travelo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="./assets/css/home.css" />
  <link rel="stylesheet" href="./assets/css/hotel.css" />
  <style>
    /* ===== Travelo User Chip (Premium Design) ===== */
    .nav-user {
      position: relative;
      display: flex;
      align-items: center;
      gap: 8px;
      z-index: 100;
    }

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
      100% { transform: translateX(100%); }
    }

    .user-text {
      white-space: nowrap;
      color: #0f172a;
      font-weight: 600;
      font-size: 14px;
      letter-spacing: -0.01em;
      position: relative;
    }

    .user-toggle i {
      font-size: 12px;
      color: #94a3b8;
      transition: transform 0.3s ease;
      margin-left: 2px;
    }

    .user-toggle.show-menu i {
      transform: rotate(180deg);
    }

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
      to { opacity: 1; transform: translateY(0); }
    }

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

    .user-menu hr {
      border: none;
      height: 1px;
      background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
      margin: 6px 16px;
    }

    .user-menu.show { display: block; }
  </style>

  <script>
    // نرسل معلومات اليوزر للـ JS عشان نبعتهم لـ booking.php
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
    <!-- NAV -->
    <section class="nav-wrapper">
        <div class="container">
            <nav class="nav">
                <div class="logo">
                    <img class="img-logo" src="./assets/images/logo.svg" alt="Travelo Logo">
                    <a href="./index.php">Travelo</a>
                </div>

                <div class="nav-links">
                    <ul class="nav-links-ul">
                        <li><a href="./index.php">Home</a></li>
                        <li><a href="./fligths.php">Flights</a></li>
                        <li><a href="./hotel.php" class="active">Hotels</a></li>
                        <li><a href="./packages.php">Packages</a></li>
                        <li><a href="./destination.php">Destinations</a></li>
                    </ul>
                </div>

                <div class="nav-button">
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
                      </button>

                      <div class="user-menu" id="userMenu">
                        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                          <a href="admin-dashboard.php">Admin dashboard</a>
                        <?php else: ?>
                          <a href="my-bookings.php">My bookings</a>
                        <?php endif; ?>

                        <form action="logout.php" method="post">
                          <button type="submit">Log out</button>
                        </form>
                      </div>
                    </div>
                  <?php else: ?>
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

    <!-- HERO -->
    <div class="hero-banner">
        <div class="banner-overlay"></div>

        <div class="stars">
          <div class="star"></div><div class="star"></div><div class="star"></div><div class="star"></div>
          <div class="star"></div><div class="star"></div><div class="star"></div><div class="star"></div>
        </div>

        <div class="floating-dots">
          <div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div>
        </div>

        <div class="container">
          <div class="hero-content">
            <h1 class="hero-title">
              <span class="title-text">
                <span class="letter">H</span>
                <span class="letter">o</span>
                <span class="letter">t</span>
                <span class="letter">e</span>
                <span class="letter">l</span>
              </span>
              <span class="sparkle">✨</span>
            </h1>

            <p class="hero-subtitle">
              <span class="subtitle-text">Where Luxury Meets Comfort</span>
            </p>

            <div class="scroll-indicator">
              <div class="mouse"><div class="wheel"></div></div>
              <span class="scroll-text">Scroll to discover</span>
            </div>
          </div>
        </div>

        <div class="wave"></div>
    </div>

    <!-- HOTELS SECTION -->
    <section class="hotels-section">
      <div class="container">
        <!-- toolbar -->
        <div class="hotels-toolbar">
          <div class="toolbar-left">
            <span class="toolbar-title">Hotels</span>
            <span class="toolbar-count">
              | Total <a href="#"><?= htmlspecialchars((string)$totalHotels) ?> results</a>
            </span>
          </div>

          <div class="toolbar-tabs">
            <button class="toolbar-tab active" data-sort="popular">Popular</button>
            <button class="toolbar-tab" data-sort="rating">Guest Ratings</button>
            <button class="toolbar-tab" data-sort="price">
              Price <span class="icon-placeholder small"></span>
            </button>
          </div>
        </div>

        <div class="hotels-grid">
          <!-- HOTELS LIST -->
          <div class="hotels-list">
            <?php if (isset($dbError)): ?>
              <div class="db-error"><p>Could not load hotels right now.</p></div>
            <?php elseif (!$hotels): ?>
              <p>No hotels available at the moment.</p>
            <?php else: ?>
              <?php
              $index = 0;
              foreach ($hotels as $hotel):
                  $index++;

                  // ====== BUILD IMAGES LIST (from hotel_images) ======
                  $imagesRaw = $hotel['images_list'] ?? '';
                  $imagesArr = array_values(array_filter(array_map('trim', explode('|', $imagesRaw))));

                  if (!$imagesArr && !empty($hotel['primary_image'])) {
                      $imagesArr = [trim($hotel['primary_image'])];
                  }

                  // فلترة قيم غلط (زي أرقام)
                  $imagesArr = array_values(array_filter($imagesArr, function($u){
                      if ($u === '' || $u === null) return false;
                      $uu = trim((string)$u);
                      if ($uu === '' || strtolower($uu) === 'null' || strtolower($uu) === 'none') return false;
                      if (ctype_digit($uu)) return false;
                      return true;
                  }));

                  // إزالة التكرار
                  $imagesArr = array_values(array_unique($imagesArr));

                  $primaryImage = $imagesArr[0] ?? '';
                  if ($primaryImage === '') {
                      $primaryImage = "https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=80";
                      $imagesArr = [$primaryImage];
                  }

                  $imagesAttr = implode('|', $imagesArr);

                  $amenities = [];
                  if ($hotel['has_parking'])            $amenities[] = 'Parking';
                  if ($hotel['has_attached_bathroom'])  $amenities[] = 'Attached Bathroom';
                  if ($hotel['has_cctv'])               $amenities[] = 'CCTV Cameras';
                  if ($hotel['has_wifi'])               $amenities[] = 'Wifi';
                  if ($hotel['has_sea_view'])           $amenities[] = 'Sea View';
                  if ($hotel['has_city_view'])          $amenities[] = 'City View';
                  if ($hotel['has_free_breakfast'])     $amenities[] = 'Free Breakfast';
                  if ($hotel['pay_at_hotel'])           $amenities[] = 'Pay Hotel Available';
                  if ($hotel['couple_friendly'])        $amenities[] = 'Couple Friendly';
                  if ($hotel['pet_friendly'])           $amenities[] = 'Pet Friendly';
                  if ($hotel['airport_shuttle'])        $amenities[] = 'Airport Shuttle';

                  $displayAmenities = array_slice($amenities, 0, 4);
                  $hasMoreAmenities = count($amenities) > 4;

                  $price      = (float)$hotel['price_per_night'];
                  $rating     = (float)$hotel['rating'];
                  $discount   = (int)$hotel['discount_percent'];
                  $reviews    = (int)$hotel['reviews_count'];

                  $cityCountry = trim($hotel['destination_city'] . ', ' . $hotel['destination_country']);
              ?>
              <article
                class="hotel-card"
                data-hotel-id="<?= (int)$hotel['id'] ?>"
                data-index="<?= $index ?>"
                data-destination-id="<?= (int)$hotel['destination_id'] ?>"
                data-price="<?= htmlspecialchars((string)$price, ENT_QUOTES) ?>"
                data-rating="<?= htmlspecialchars(number_format($rating, 1), ENT_QUOTES) ?>"
                data-reviews="<?= $reviews ?>"
                data-discount="<?= $discount ?>"
                data-location="<?= htmlspecialchars($hotel['location_text'], ENT_QUOTES) ?>"
                data-city-country="<?= htmlspecialchars($cityCountry, ENT_QUOTES) ?>"
                data-description="<?= htmlspecialchars($hotel['description'], ENT_QUOTES) ?>"
                data-amenities="<?= htmlspecialchars(implode('|', $amenities), ENT_QUOTES) ?>"
                data-images="<?= htmlspecialchars($imagesAttr, ENT_QUOTES) ?>"
                data-currency="<?= htmlspecialchars($hotel['currency'], ENT_QUOTES) ?>"
              >
                <div class="hotel-image-col">
                  <div class="hotel-image-wrapper">
                    <img
                      src="<?= htmlspecialchars($primaryImage) ?>"
                      alt="<?= htmlspecialchars($hotel['name']) ?>"
                    />
                    <div class="hotel-avatars"></div>
                  </div>
                </div>

                <div class="hotel-main-col">
                  <div class="hotel-top-row">
                    <div class="hotel-text-block">
                      <h3 class="hotel-name"><?= htmlspecialchars($hotel['name']) ?></h3>
                      <p class="hotel-location"><?= htmlspecialchars($hotel['location_text']) ?></p>

                      <div class="hotel-amenities">
                        <?php foreach ($displayAmenities as $amenity): ?>
                          <span class="amenity">
                            <span class="icon-placeholder"></span>
                            <?= htmlspecialchars($amenity) ?>
                          </span>
                        <?php endforeach; ?>

                        <?php if ($hasMoreAmenities): ?>
                          <span class="amenity more">More+</span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="hotel-side-top">
                      <button class="map-view">MAP VIEW</button>
                      <div class="rating-chip">
                        <span class="rating-score"><?= htmlspecialchars(number_format($rating, 1)) ?></span>
                        <span class="rating-star">★</span>
                      </div>
                    </div>
                  </div>

                  <div class="hotel-bottom-row">
                    <div class="hotel-price-block">
                      <?php if ($discount > 0): ?>
                        <span class="price-off"><?= $discount ?>% off</span>
                      <?php endif; ?>
                      <span class="price-value">$<?= number_format($price, 2) ?></span>
                    </div>

                    <div class="hotel-actions">
                      <button class="btn-light">View Details</button>
                      <button class="btn-primary">BOOK NOW</button>
                    </div>
                  </div>
                </div>
              </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- FILTERS PANEL -->
          <aside class="filters-panel">
            <div class="filters-header"><h3>Filters</h3></div>

            <section class="filter-block">
              <div class="filter-row">
                <span class="filter-label">Price</span>
                <span class="filter-value" id="priceSummary">
                  <?= $maxPrice > 0 ? 'Up to $' . number_format($maxPrice, 2) : '' ?>
                </span>
              </div>

              <div class="price-chart"><div class="chart-wave"></div></div>

              <div class="price-slider">
                <div class="slider-track">
                  <div class="slider-fill"></div>
                  <div class="slider-thumb left"></div>
                  <div class="slider-thumb right"></div>
                </div>
                <div class="slider-values">
                  <span><?= $minPrice > 0 ? '$' . number_format($minPrice, 2) : '$0.00' ?></span>
                  <span><?= $maxPrice > 0 ? '$' . number_format($maxPrice, 2) : '$0.00' ?></span>
                </div>
              </div>
            </section>

            <section class="filter-block">
              <div class="filter-row">
                <span class="filter-label">Looking for</span>
                <span class="filter-dot">2</span>
              </div>
              <div class="filter-select">
                <div class="select-display">
                  <span>Package Deals</span>
                  <span class="icon-placeholder small"></span>
                </div>
              </div>
            </section>

            <section class="filter-block">
              <div class="filter-row"><span class="filter-label">Category</span></div>
              <div class="filter-select">
                <div class="select-display">
                  <span>5 Star</span>
                  <span class="icon-placeholder small"></span>
                </div>
              </div>
            </section>

            <section class="filter-block">
              <div class="filter-row"><span class="filter-label">Popular Filters</span></div>

              <div class="filters-checkboxes">
                <label class="filter-checkbox">
                  <input type="checkbox" value="Pay Hotel Available" />
                  <span>Pay Hotel Available</span>
                </label>
                <label class="filter-checkbox">
                  <input type="checkbox" value="Couple Friendly" />
                  <span>Couple Friendly</span>
                </label>
                <label class="filter-checkbox">
                  <input type="checkbox" value="Free Breakfast" />
                  <span>Free Breakfast</span>
                </label>
              </div>

              <button class="more-filters" id="moreFiltersBtn">
                More <span class="icon-placeholder tiny"></span>
              </button>

              <div class="filters-extra" id="filtersExtra">
                <label class="filter-checkbox">
                  <input type="checkbox" value="Pet Friendly" />
                  <span>Pet Friendly</span>
                </label>
                <label class="filter-checkbox">
                  <input type="checkbox" value="Airport Shuttle" />
                  <span>Airport Shuttle</span>
                </label>
              </div>
            </section>
          </aside>
        </div>
      </div>
    </section>

    <!-- HOTEL MODAL مع عدّاد الليالي -->
    <div class="hotel-modal-overlay" id="hotelModal">
      <div class="hotel-modal">
        <button class="hotel-modal-close" id="hotelModalClose">&times;</button>

        <div class="hotel-modal-grid">
          <div class="hotel-modal-left">
            <div class="hotel-modal-main">
              <button class="modal-image-nav prev" id="modalPrevImg">‹</button>
              <img id="modalMainImage" src="" alt="Hotel main image" />
              <button class="modal-image-nav next" id="modalNextImg">›</button>

              <div class="modal-tag" id="modalOfferTag">BEST DEAL</div>
            </div>

            <div class="hotel-modal-thumbs" id="modalThumbs"></div>

            <div class="modal-price-under">
              <span id="modalHotelPrice" class="modal-price">$0.00</span>
              <div class="modal-price-under-meta">
                <span class="modal-per">USD · 1 night</span>
                <button class="modal-pay-under" id="modalPayUnderBtn">Pay now</button>
              </div>
            </div>
          </div>

          <div class="hotel-modal-right">
            <header class="modal-header-top">
              <div>
                <h2 id="modalHotelName">Hotel name</h2>
                <p class="modal-location" id="modalHotelLocation">Location text</p>
              </div>
            </header>

            <div class="modal-rating-row">
              <div class="modal-rating-chip">
                <span class="score" id="modalHotelRating">4.5</span>
                <span class="label">Excellent</span>
              </div>
              <span class="modal-rating-reviews" id="modalHotelReviews">0 reviews</span>
            </div>

            <div class="modal-score-bars">
              <div class="score-row">
                <span>Location</span>
                <div class="score-bar"><span class="fill" id="scoreLocation"></span></div>
              </div>
              <div class="score-row">
                <span>Service</span>
                <div class="score-bar"><span class="fill" id="scoreService"></span></div>
              </div>
              <div class="score-row">
                <span>Value</span>
                <div class="score-bar"><span class="fill" id="scoreValue"></span></div>
              </div>
            </div>

            <section class="modal-about-block">
              <h4>About</h4>
              <p id="modalHotelAbout">Description will appear here.</p>
            </section>

            <section class="modal-services-block">
              <h4>Popular Services</h4>
              <ul id="modalHotelServices"></ul>
            </section>

            <div class="modal-safety-card" id="modalSafetyCard">
              <div class="safety-icon"></div>
              <div class="safety-text">
                <h5 id="safetyTitle">Travel safe during your stay</h5>
                <p id="safetyDesc">
                  This property follows extra safety and cleaning measures to help keep you protected.
                </p>
              </div>
            </div>

            <!-- Nights selector -->
            <div class="modal-nights-row" style="
                  margin-top: 14px;
                  padding: 10px 12px;
                  border-radius: 12px;
                  border: 1px solid #e5e7eb;
                  background: #f9fafb;
                  display:flex;
                  align-items:center;
                  justify-content:space-between;
                  gap:12px;">
              <div style="display:flex;flex-direction:column;gap:2px;">
                <span style="font-size:13px;font-weight:600;color:#111827;">Nights</span>
                <span style="font-size:11px;color:#6b7280;">Choose how many nights you want to stay</span>
              </div>

              <div style="display:flex;align-items:center;gap:8px;">
                <button type="button" id="modalNightsMinus" style="
                    width:30px;height:30px;
                    border-radius:999px;
                    border:1px solid #e5e7eb;
                    background:#ffffff;
                    cursor:pointer;
                    font-size:18px;
                    line-height:1;">-</button>

                <span id="modalNightsValue" style="min-width:24px;text-align:center;font-weight:600;font-size:14px;">1</span>

                <button type="button" id="modalNightsPlus" style="
                    width:30px;height:30px;
                    border-radius:999px;
                    border:1px solid #e5e7eb;
                    background:#ffffff;
                    cursor:pointer;
                    font-size:18px;
                    line-height:1;">+</button>
              </div>
            </div>

            <div class="modal-actions">
              <button class="modal-book-btn" id="modalBookBtn">Book Now</button>
              <button class="modal-secondary-btn" id="modalCloseSecondary">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>

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
                        <a href="#">+970 599 000 111</a>
                        <a href="#">info@travelo.com</a>
                    </div>
                </div>

                <div class="footer-links">
                    <div class="footer-link">
                        <h3>Products</h3>
                        <a href="#">Flights</a>
                        <a href="#">Hotels</a>
                        <a href="#">Car Rentals</a>
                        <a href="#">Travel Packages</a>
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
    <script src="./assets/js/hotel.js"></script>
</body>
</html>
