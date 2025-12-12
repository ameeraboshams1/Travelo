<?php
// ================== DB CONNECTION (PDO) ==================
$host     = 'localhost';
$dbname   = 'travelo';   // <-- عدّلي اسم قاعدة البيانات لو مختلف
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

// ================== FETCH PACKAGES ==================
try {
    $stmt = $pdo->query("
        SELECT 
            id,
            title,
            location,
            from_city,
            duration_days,
            price_usd,
            badge_type,
            image_url
        FROM packages
        ORDER BY id ASC
    ");
    $packages = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Query error: ' . htmlspecialchars($e->getMessage()));
}

// ================== HELPERS ==================
function mapBadgeToCategory($badge)
{
    $b = strtolower($badge);

    if (strpos($b, 'adventure') !== false) return 'adventure';
    if (strpos($b, 'beach') !== false || strpos($b, 'sea') !== false) return 'beach';
    if (strpos($b, 'city') !== false) return 'city';
    if (strpos($b, 'hike') !== false) return 'hiking';
    if (strpos($b, 'museum') !== false) return 'museum';
    if (strpos($b, 'culture') !== false) return 'cultural';
    if (strpos($b, 'luxury') !== false || strpos($b, 'relax') !== false) return 'relax';

    // fallback عام
    return 'adventure';
}

function fakeRating($id)
{
    // رقم لطيف بين 4.3 و 4.9
    $base = 4.3 + ($id % 6) * 0.1;
    if ($base > 4.9) $base = 4.9;
    return $base;
}

function fakeReviews($id)
{
    // رقم مراجعات شكل بس ثابت لكل id
    return 80 + (($id * 17) % 250);
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
  <link rel="stylesheet" href="./assets/css/home.css" />
  <link rel="stylesheet" href="./assets/css/packages.css" />

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

  <div class="page">
    <!-- ============ HERO ============ -->
    <section class="hero">
      <div class="hero-banner" data-animate>
        <div class="hero-overlay"></div>
        <div class="hero-text">
          <h1>Tour Package</h1>
          <p class="hero-breadcrumb">
            Home <span>/ Tour Package</span>
          </p>
        </div>
      </div>

      <div class="container">
        <form class="hero-search-box" id="heroSearch" data-animate>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Destination</label>
              <div class="input-with-icon">
                <input
                  type="text"
                  class="form-input hero-input"
                  name="destination"
                  placeholder="Where to go?"
                />
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
                <input
                  type="date"
                  class="form-input hero-input"
                  name="date"
                  placeholder="Date"
                />
                <i class="fa-solid fa-calendar"></i>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Guests</label>
              <div class="select-with-icon">
                <select class="form-select hero-select" name="guests">
                  <option>0</option>
                  <option>1</option>
                  <option>2</option>
                  <option>3</option>
                  <option>4+</option>
                </select>
                <i class="fa-solid fa-user"></i>
              </div>
            </div>

            <button class="form-button hero-button" type="submit">
              Search
              <span class="btn-ripple"></span>
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
              <li>
                <label>
                  <span>
                    <input type="checkbox" data-categories="all" checked />
                    All Tours
                  </span>
                  <span class="count">0</span>
                </label>
              </li>
              <li>
                <label>
                  <span>
                    <input type="checkbox" data-categories="adventure" />
                    Adventure
                  </span>
                  <span class="count">0</span>
                </label>
              </li>
              <li>
                <label>
                  <span>
                    <input type="checkbox" data-categories="beach" />
                    Beaches
                  </span>
                  <span class="count">0</span>
                </label>
              </li>
              <li>
                <label>
                  <span>
                    <input type="checkbox" data-categories="city" />
                    City Tours
                  </span>
                  <span class="count">0</span>
                </label>
              </li>
              <li>
                <label>
                  <span>
                    <input type="checkbox" data-categories="hiking" />
                    Hiking
                  </span>
                  <span class="count">0</span>
                </label>
              </li>
              <li>
                <label>
                  <span>
                    <input type="checkbox" data-categories="museum" />
                    Museum Tours
                  </span>
                  <span class="count">0</span>
                </label>
              </li>
            </ul>
          </div>

          <div class="sidebar-section" id="durationList">
            <div class="sidebar-title"><i class="fa-regular fa-clock"></i> Duration</div>
            <ul class="sidebar-list">
              <li>
                <label>
                  <span><input type="checkbox" data-duration="day" /> 1 Day</span>
                  <span class="count">0</span>
                </label>
              </li>
              <li>
                <label>
                  <span><input type="checkbox" data-duration="weekend" /> 2–3 Days</span>
                  <span class="count">0</span>
                </label>
              </li>
              <li>
                <label>
                  <span><input type="checkbox" data-duration="week" /> 4–7 Days</span>
                  <span class="count">0</span>
                </label>
              </li>
              <li>
                <label>
                  <span><input type="checkbox" data-duration="extended" /> 8+ Days</span>
                  <span class="count">0</span>
                </label>
              </li>
            </ul>
          </div>

          <div class="sidebar-section" id="ratingList">
            <div class="sidebar-title"><i class="fa-solid fa-star"></i> Reviews</div>
            <ul class="sidebar-list">
              <li>
                <label>
                  <span>
                    <input type="checkbox" data-rating="5" />
                    <i class="fa-solid fa-star"></i> 5 Stars &amp; Up
                  </span>
                  <span class="count">0</span>
                </label>
              </li>
              <li>
                <label>
                  <span>
                    <input type="checkbox" data-rating="4" />
                    <i class="fa-solid fa-star"></i> 4 &amp; Up
                  </span>
                  <span class="count">0</span>
                </label>
              </li>
              <li>
                <label>
                  <span>
                    <input type="checkbox" data-rating="3" />
                    <i class="fa-solid fa-star"></i> 3 &amp; Up
                  </span>
                  <span class="count">0</span>
                </label>
              </li>
            </ul>
          </div>
        </aside>

        <!-- ============ TOURS LIST ============ -->
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
                  $title     = $pkg['title']        ?? '';
                  $location  = $pkg['location']     ?? '';
                  $fromCity  = $pkg['from_city']    ?? '';
                  $duration  = (int)($pkg['duration_days'] ?? 0);
                  $price     = (float)($pkg['price_usd'] ?? 0);
                  $badge     = $pkg['badge_type']   ?? '';
                  $imageUrl  = $pkg['image_url']    ?? '';

                  $category  = mapBadgeToCategory($badge);
                  $rating    = fakeRating($id);
                  $reviews   = fakeReviews($id);

                  // بيانات زيادة للبوكنج
                  $cityOnly   = $location;                 // تقدرِ لاحقاً تقطعيها (مدينة / دولة)
                  $nights     = max(1, $duration);         // اعتبرنا عدد الأيام = عدد الليالي مؤقتاً
                  $comboText  = $badge ?: 'Flight + Hotel';
                  $currency   = 'USD';
                ?>
                <article class="tour-card" data-animate
                  data-price="<?php echo htmlspecialchars($price, ENT_QUOTES); ?>"
                  data-rating="<?php echo htmlspecialchars($rating, ENT_QUOTES); ?>"
                  data-duration="<?php echo htmlspecialchars($duration, ENT_QUOTES); ?>"
                  data-category="<?php echo htmlspecialchars($category, ENT_QUOTES); ?>"

                  data-package-id="<?php echo $id; ?>"
                  data-title="<?php echo htmlspecialchars($title, ENT_QUOTES); ?>"
                  data-city="<?php echo htmlspecialchars($cityOnly, ENT_QUOTES); ?>"
                  data-nights="<?php echo $nights; ?>"
                  data-combo="<?php echo htmlspecialchars($comboText, ENT_QUOTES); ?>"
                  data-currency="<?php echo htmlspecialchars($currency, ENT_QUOTES); ?>"
                >
                  <div class="tour-card-image">
                    <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($title, ENT_QUOTES); ?>">
                    <div class="tour-badge">
                      <?php echo htmlspecialchars($badge); ?>
                    </div>
                    <div class="tour-heart">
                      <i class="fa-regular fa-heart"></i>
                    </div>
                  </div>
                  <div class="tour-card-body">
                    <div class="tour-location"><?php echo htmlspecialchars($location); ?></div>
                    <div class="tour-title">
                      <?php echo htmlspecialchars($title); ?>
                    </div>
                    <div class="tour-meta">
                      <div class="tour-meta-left">
                        <span>
                          <i class="fa-solid fa-star"></i>
                          <?php echo number_format($rating, 1); ?> (<?php echo $reviews; ?>)
                        </span>
                        <span>
                          <i class="fa-regular fa-clock"></i>
                          <?php echo $duration > 0 ? $duration . ' Days' : 'Flexible'; ?>
                        </span>
                      </div>
                      <div class="tour-price">
                        $<?php echo number_format($price, 2); ?>
                      </div>
                    </div>
                  </div>
                  <div class="tour-footer">
                    <span>From <?php echo htmlspecialchars($fromCity); ?></span>
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
            <!-- رح تنعادِل بالديناميك من الجافا سكربت -->
            <div class="page-link active" data-type="page" data-page="1">1</div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- ============ FOOTER ============ -->
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

  <div id="toast" class="toast"></div>

  <script src="./assets/js/home.js"></script>
  <script src="./assets/js/packages.js"></script>
  
</body>
</html>
