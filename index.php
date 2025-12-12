<?php
$subStatus = $_GET['sub'] ?? null;
session_start();

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
  <link href="./assets/css/main.css" rel="stylesheet">
  <link href="./assets/css/home.css" rel="stylesheet">
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
  </style>

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
        <i class="fa-solid fa-chevron-down"></i>
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
        <button class="category-btn active" data-category="all">City</button>
        <button class="category-btn" data-category="mountain">Mountain</button>
        <button class="category-btn" data-category="forest">Forest</button>
        <button class="category-btn" data-category="island">Island</button>
        <a href="#" class="see-all-link">see all</a>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-12 col-md-6 col-lg-4 destination-col" data-category="city">
        <div class="destination-card">
          <div class="image-container">
            <div class="image-blur-effect"></div>
            <img src="Rectangle 1434.svg" alt="Tokyo" class="destination-image" />
            <div class="rating-badge">
              <span class="star">★</span>
              <span>5.0</span>
            </div>
          </div>

          <div class="card-content">
            <div class="destination-city">Tokyo</div>
            <div class="destination-desc">
              Lorem Ipsum is simply dummy text of the printing and…see more
            </div>

            <div class="destination-bottom">
              <div class="destination-footer-top">
                <span class="location-city">Tokyo, Japan</span>
              </div>
              <div class="destination-footer-bottom">
                <div class="destination-price">
                  $360 <span>+12 interest free</span>
                </div>
                <button class="btn-gradient view-btn" data-city="Tokyo">See More</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-4 destination-col" data-category="city">
        <div class="destination-card">
          <div class="image-container">
            <div class="image-blur-effect"></div>
            <img src="https://images.pexels.com/photos/532263/pexels-photo-532263.jpeg" alt="Rome"
              class="destination-image" />
            <div class="rating-badge">
              <span class="star">★</span>
              <span>5.0</span>
            </div>
          </div>

          <div class="card-content">
            <div class="destination-city">Rome</div>
            <div class="destination-desc">
              Lorem Ipsum is simply dummy text of the printing and…see more
            </div>

            <div class="destination-bottom">
              <div class="destination-footer-top">
                <span class="location-city">Rome, Italy</span>
              </div>
              <div class="destination-footer-bottom">
                <div class="destination-price">
                  $370 <span>+12 interest free</span>
                </div>
                <button class="btn-gradient view-btn" data-city="Rome">See More</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-4 destination-col" data-category="city">
        <div class="destination-card">
          <div class="image-container">
            <div class="image-blur-effect"></div>
            <img src="https://images.pexels.com/photos/586052/pexels-photo-586052.jpeg" alt="Barcelona"
              class="destination-image" />
            <div class="rating-badge">
              <span class="star">★</span>
              <span>5.0</span>
            </div>
          </div>

          <div class="card-content">
            <div class="destination-city">Barcelona</div>
            <div class="destination-desc">
              Lorem Ipsum is simply dummy text of the printing and…see more
            </div>

            <div class="destination-bottom">
              <div class="destination-footer-top">
                <span class="location-city">Barcelona, Spain</span>
              </div>
              <div class="destination-footer-bottom">
                <div class="destination-price">
                  $400 <span>+12 interest free</span>
                </div>
                <button class="btn-gradient view-btn" data-city="Barcelona">See More</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-4 destination-col" data-category="city">
        <div class="destination-card">
          <div class="image-container">
            <div class="image-blur-effect"></div>
            <img src="https://images.pexels.com/photos/373290/pexels-photo-373290.jpeg" alt="Bangkok"
              class="destination-image" />
            <div class="rating-badge">
              <span class="star">★</span>
              <span>5.0</span>
            </div>
          </div>

          <div class="card-content">
            <div class="destination-city">Bangkok</div>
            <div class="destination-desc">
              Lorem Ipsum is simply dummy text of the printing and…see more
            </div>

            <div class="destination-bottom">
              <div class="destination-footer-top">
                <span class="location-city">Bangkok, Thailand</span>
              </div>
              <div class="destination-footer-bottom">
                <div class="destination-price">
                  $300 <span>+12 interest free</span>
                </div>
                <button class="btn-gradient view-btn" data-city="Bangkok">See More</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-4 destination-col" data-category="island">
        <div class="destination-card">
          <div class="image-container">
            <div class="image-blur-effect"></div>
            <img src="https://images.pexels.com/photos/2193300/pexels-photo-2193300.jpeg" alt="Sydney"
              class="destination-image" />
            <div class="rating-badge">
              <span class="star">★</span>
              <span>5.0</span>
            </div>
          </div>

          <div class="card-content">
            <div class="destination-city">Sydney</div>
            <div class="destination-desc">
              Lorem Ipsum is simply dummy text of the printing and…see more
            </div>

            <div class="destination-bottom">
              <div class="destination-footer-top">
                <span class="location-city">Sydney, Australia</span>
              </div>
              <div class="destination-footer-bottom">
                <div class="destination-price">
                  $300 <span>+12 interest free</span>
                </div>
                <button class="btn-gradient view-btn" data-city="Sydney">See More</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-4 destination-col" data-category="forest">
        <div class="destination-card">
          <div class="image-container">
            <div class="image-blur-effect"></div>
            <img src="https://images.pexels.com/photos/2946729/pexels-photo-2946729.jpeg" alt="Toronto"
              class="destination-image" />
            <div class="rating-badge">
              <span class="star">★</span>
              <span>5.0</span>
            </div>
          </div>

          <div class="card-content">
            <div class="destination-city">Toronto</div>
            <div class="destination-desc">
              Lorem Ipsum is simply dummy text of the printing and…see more
            </div>

            <div class="destination-bottom">
              <div class="destination-footer-top">
                <span class="location-city">Toronto, Canada</span>
              </div>
              <div class="destination-footer-bottom">
                <div class="destination-price">
                  $370 <span>+12 interest free</span>
                </div>
                <button class="btn-gradient view-btn" data-city="Toronto">See More</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
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
            <h2 id="modalDestinationTitle">Tokyo</h2>
            <p id="modalDestinationLocation" class="modal-location">Tokyo, Japan</p>
          </div>
          <div class="modal-rating">
            <span>★ 5.0</span>
          </div>
        </div>

        <p id="modalDestinationDesc" class="modal-description">
          Explore the vibrant streets, neon lights, and traditional temples in one of the most
          exciting cities in the world.
        </p>

        <div class="destination-modal-stats">
          <div class="stat-card">
            <span class="stat-label">Avg. visitors</span>
            <span class="stat-value" id="modalVisitors">12M / year</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">Best season</span>
            <span class="stat-value" id="modalSeason">Mar – May</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">Starting from</span>
            <span class="stat-value" id="modalPrice">$360</span>
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


  <section class="testimonials-section">
    <div class="container-testimonials">
      <div class="section-header">
        <p class="subtitle">TESTIMONIALS</p>
        <h2>Trust our clients</h2>
      </div>

      <div class="testimonial-slider">
        <div class="slider-wrapper">
          <div class="testimonial-slide active-slide">
            <img src="./assets/images/TESTIMONIALS/avatar.png" alt="Irfan Rahmat" class="client-avatar">
            <h3>Irfan Rahmat</h3>
            <p class="client-title">Travel Enthusiast</p>
            <div class="rating"></div>
            <p class="testimonial-text">
              I love Travelo, this is the best place to buy ticket and help you find your dream holiday.
            </p>
          </div>

          <div class="testimonial-slide">
            <img src="./assets/images/TESTIMONIALS/avatar2.png" alt="Jane Doe" class="client-avatar">
            <h3>Jane Doe</h3>
            <p class="client-title">Adventure Seeker</p>
            <div class="rating"></div>
            <p class="testimonial-text">
              An amazing experience from start to finish. The support team was incredibly helpful. Highly
              recommended!
            </p>
          </div>

          <div class="testimonial-slide">
            <img src="./assets/images/TESTIMONIALS/avatar1.png" alt="John Smith" class="client-avatar">
            <h3>John Smith</h3>
            <p class="client-title">Family Vacationer</p>
            <div class="rating"></div>
            <p class="testimonial-text">
              Our family trip was perfectly organized. Everything was seamless. We will definitely be back
              for more.
            </p>
          </div>
        </div>

        <button class="slider-btn prev-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <button class="slider-btn next-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>

        <div class="slider-pagination">
        </div>
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
      <form class="nl-form" action="subscribe.php" method="post">
    <span class="nl-icon" aria-hidden="true">
         <svg viewBox="0 0 24 24">
                                    <path
                                        d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5Z" />
                                </svg>
    </span>
    <input type="email" name="email" class="nl-input" placeholder="Your email" required>
    <button type="submit" class="nl-btn">Subscribe</button>
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






  <img class="effect1" src="./assets/images/hero-img/Ellipse 23.png">
  <img class="effect2" src="assets/images/hero-img/Ellipse 24.jpg">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
  <script src="./assets/js/home.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('userMenuToggle');
    const menu   = document.getElementById('userMenu');

    if (toggle && menu) {
      // افتح/سكر المنيو لما أكبس عالأفاتار
      toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('show');
      });

      // سكّر المنيو لو كبست برا
      document.addEventListener('click', () => {
        menu.classList.remove('show');
      });
    }
  });
</script>




</body>

</html>