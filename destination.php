<?php
// destination.php

require __DIR__ . '/db.php';   // يجلب $pdo من db.php

// جلب الديستناشنز الفعّالة فقط
$sql = "SELECT id, name, city, country, category, image_url, short_desc, base_price
        FROM destinations
        WHERE is_active = 1
        ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$destinations = $stmt->fetchAll();   // مصفوفة تحتوي على كل الوجهات
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Destinations – Travelo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >

  <link rel="stylesheet" href="./assets/css/home.css">
  <link rel="stylesheet" href="./assets/css/destination.css">
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
            <li><a href="./flights.php">Flights</a></li>
            <li><a href="./hotel.php">Hotels</a></li>
            <li><a href="./packages.php">Packages</a></li>
            <li><a href="destination.php" class="active-nav">Destinations</a></li>
          </ul>
        </div>

        <div class="nav-button">
          <button id="btnLogin" type="button" class="sign_in">Login</button>
          <button id="btnLogin1" class="sign_up">Sign up</button>
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
          <button class="category-btn active" data-category="all">City</button>
          <button class="category-btn" data-category="mountain">Mountain</button>
          <button class="category-btn" data-category="forest">Forest</button>
          <button class="category-btn" data-category="island">Island</button>
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
                              data-city="<?= htmlspecialchars($dest['name']) ?>">
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
            <button class="modal-btn primary">
              <span class="icon">✈️</span>
              <div class="text">
                <span class="title">Book Trip</span>
                <span class="subtitle">Flights & activities</span>
              </div>
            </button>

            <button class="modal-btn outline">
              <span class="icon">🎁</span>
              <div class="text">
                <span class="title">Book Package</span>
                <span class="subtitle">Flight + hotel + tour</span>
              </div>
            </button>

            <button class="modal-btn ghost">
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
  <script src="./assets/js/destination.js"></script>
</body>
</html>
