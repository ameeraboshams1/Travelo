<?php
require __DIR__ . '/db.php';
session_start();

$sql = "
    SELECT
        f.*,
        d.city    AS dest_city,
        d.country AS dest_country
    FROM flights f
    LEFT JOIN destinations d ON f.destination_id = d.id
    WHERE f.is_active = 1
    ORDER BY f.base_price ASC
";

try {
    $stmt = $pdo->query($sql);
    $flights = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Flights query failed: ' . $e->getMessage());
}

function formatDuration($hours)
{
    if ($hours === null) return '';
    $hours = (float)$hours;
    $h = floor($hours);
    $m = round(($hours - $h) * 60);
    if ($m === 60) {
        $h++;
        $m = 0;
    }
    if ($m > 0) {
        return $h . 'h ' . $m . 'm';
    }
    return $h . 'h';
}

function formatDateLabel($dateStr)
{
    if (!$dateStr) return '';
    try {
        $dt = new DateTime($dateStr);
        return $dt->format('D, d M Y');
    } catch (Exception $e) {
        return $dateStr;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Travelo · Flights</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <link rel="stylesheet" href="./assets/css/home.css" />
  <link rel="stylesheet" href="./assets/css/fligth.css" />
   
</script>  
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
      100% {
        transform: translateX(100%);
      }
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
      to {
        opacity: 1;
        transform: translateY(0);
      }
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

    .user-menu.show {
      display: block;
    }
  </style>

  <script>
    // نرسل معلومات اليوزر للـ JS عشان نبعثها للـ booking.html
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
            <li><a href="./fligths.php" class="active">Flights</a></li>
            <li><a href="./hotel.php">Hotels</a></li>
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

  <div class="flash-section">
    <div class="airplane">✈</div>
    <div class="airplane">✈</div>
    <div class="airplane">✈</div>

    <div class="flash-content">
      <h1 class="main-title text-reveal">FLIGHT</h1>
      <div class="tagline float-effect">
        <span>Your Journey Begins Here</span>
      </div>
    </div>
  </div>

  <div class="container my-5">
    <div class="row g-3 align-items-center mb-3">
      <div class="col-12 col-lg-6">
        <div class="d-flex gap-3">
          <div class="tab-item active" data-sort="cheapest">
            <h4>Cheapest</h4>
            <p>Show lowest prices first</p>
          </div>
          <div class="tab-item" data-sort="fastest">
            <h4>Fastest</h4>
            <p>Show shortest trips first</p>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="trip-type-wrapper">
          <span class="trip-type-label">Trip type</span>
          <div class="trip-type-chips">
            <button class="trip-chip active" data-trip="all">All</button>
            <button class="trip-chip" data-trip="oneway">One Way</button>
            <button class="trip-chip" data-trip="roundtrip">Round Trip</button>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 align-items-start">
      <div class="col-12 col-lg-8">
        <div class="results-header mb-2">
          <h3>Available flights</h3>
          <span class="results-count">
            <?= count($flights); ?> results found
          </span>
        </div>

        <div id="flightList" class="d-flex flex-column gap-3 mt-3">
          <?php if (count($flights) > 0): ?>
            <?php foreach ($flights as $flight): ?>
              <?php
                $price       = (float)$flight['base_price'];
                $duration    = (float)$flight['duration_hours'];
                $stops       = (int)$flight['stops_count'];
                $tripType    = $flight['trip_type']; // oneway / roundtrip
                $tripArrow   = $tripType === 'roundtrip' ? '⇄' : '→';
                $stopsLabel  = $stops === 0
                    ? 'Non stop'
                    : $stops . ' stop' . ($stops > 1 ? 's' : '');
                $durationLbl = formatDuration($duration);
                $airlineCode = $flight['airline_code'] ?: substr($flight['airline_name'], 0, 3);
                $destCity    = $flight['dest_city'] ?: $flight['destination_city'];
                $destCountry = $flight['dest_country'] ?: '';
                $depDateLbl  = formatDateLabel($flight['departure_date']);
                $retDateLbl  = formatDateLabel($flight['return_date']);
              ?>
<article
  class="flight-card ticket"
  data-flight-id="<?= (int)$flight['id'] ?>"
  data-destination-id="<?= (int)$flight['destination_id'] ?>"
  data-price="<?= htmlspecialchars($price) ?>"
  data-duration="<?= htmlspecialchars($duration) ?>"
  data-stops="<?= htmlspecialchars($stops) ?>"
  data-trip="<?= htmlspecialchars($tripType) ?>"
  data-dep-date-label="<?= htmlspecialchars($depDateLbl) ?>"
  data-ret-date-label="<?= htmlspecialchars($retDateLbl) ?>"

  data-depart-date="<?= htmlspecialchars($flight['departure_date'] ?? '') ?>"
  data-return-date="<?= htmlspecialchars($flight['return_date'] ?? '') ?>"

  data-airline-name="<?= htmlspecialchars($flight['airline_name'] ?? '') ?>"
  data-flight-number="<?= htmlspecialchars($flight['flight_number'] ?? '') ?>"

  data-from-airport-code="<?= htmlspecialchars($flight['origin_airport_code'] ?? '') ?>"
  data-to-airport-code="<?= htmlspecialchars($flight['destination_airport_code'] ?? '') ?>"

  data-departure-time="<?= htmlspecialchars(substr($flight['departure_time'] ?? '', 0, 5)) ?>"
  data-arrival-time="<?= htmlspecialchars(substr($flight['arrival_time'] ?? '', 0, 5)) ?>"

  data-origin-city="<?= htmlspecialchars($flight['origin_city'] ?? '') ?>"
  data-dest-city="<?= htmlspecialchars($destCity ?? '') ?>"
>

                <div class="ticket-inner">
                  <div class="ticket-left">
                    <div class="ticket-row-top">
                      <div class="airline">
                        <div class="logo-circle">
                          <?= htmlspecialchars($airlineCode) ?>
                        </div>
                        <div>
                          <div class="airline-name">
                            <?= htmlspecialchars($flight['airline_name']) ?>
                          </div>
                          <div class="airline-sub">
                            <?= htmlspecialchars($flight['fare_subtitle'] ?: 'Economy') ?>
                          </div>
                        </div>
                      </div>
                      <div class="ticket-code">
                        <?= htmlspecialchars($flight['flight_number']) ?>
                      </div>
                    </div>

                    <div class="ticket-route">
                      <span class="city" data-code="<?= htmlspecialchars($flight['origin_airport_code']) ?>">
                        <?= htmlspecialchars($flight['origin_city']) ?>
                      </span>
                      <span class="arrow"><?= $tripArrow ?></span>
                      <span class="city" data-code="<?= htmlspecialchars($flight['destination_airport_code']) ?>">
                        <?= htmlspecialchars($destCity) ?>
                      </span>
                    </div>

                    <div class="ticket-times">
                      <div class="time">
                        <strong><?= htmlspecialchars(substr($flight['departure_time'], 0, 5)) ?></strong>
                        <span class="code"><?= htmlspecialchars($flight['origin_airport_code']) ?></span>
                      </div>

                      <div class="time-line">
                        <span class="duration-label">
                          <?= htmlspecialchars($durationLbl) ?>
                        </span>
                        <div class="dashed-line"></div>
                        <span class="nonstop-label"><?= htmlspecialchars($stopsLabel) ?></span>
                      </div>

                      <div class="time text-end">
                        <strong><?= htmlspecialchars(substr($flight['arrival_time'], 0, 5)) ?></strong>
                        <span class="code"><?= htmlspecialchars($flight['destination_airport_code']) ?></span>
                      </div>
                    </div>

                    <div class="ticket-extra">
                      <?php if (!empty($flight['extras'])): ?>
                        <?php
                          $extras = array_map('trim', explode(',', $flight['extras']));
                          foreach ($extras as $extra):
                            if ($extra === '') continue;
                        ?>
                          <span><?= htmlspecialchars($extra) ?></span>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <span>Free meal</span>
                        <span>Changeable</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="ticket-divider"></div>

                  <div class="ticket-right">
                    <span class="fare-label">From</span>
                    <strong class="price">
                      $<?= number_format($price, 2) ?>
                    </strong>
                    <button class="btn-primary book-btn">Book Now</button>
                  </div>
                </div>

                <div class="flight-footer">
                  <button class="details-btn">
                    <span>View details</span>
                    <span class="chevron">▼</span>
                  </button>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No flights available at the moment.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-12 col-lg-4">
        <aside class="filter-card">
          <h3>Filter</h3>

          <div class="filter-group">
            <label for="maxPrice">Max price ($)</label>
            <div class="range-row">
              <input type="range" id="maxPrice" min="100" max="600" value="600" />
              <span id="maxPriceValue">Up to 600</span>
            </div>
          </div>

          <div class="filter-group">
            <span class="filter-label">Stops</span>
            <label class="checkbox-row">
              <input type="checkbox" id="nonStopOnly" />
              <span>Non stop only</span>
            </label>
          </div>

          <div class="filter-group">
            <span class="filter-label">Departure time</span>
            <div class="chips">
              <button class="chip active" data-time="all">Any</button>
              <button class="chip" data-time="morning">Morning</button>
              <button class="chip" data-time="evening">Evening</button>
            </div>
          </div>

          <button class="btn-outline mt-2" id="resetFilters">Reset filters</button>
        </aside>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div
    class="modal fade"
    id="flightDetailsModal"
    tabindex="-1"
    aria-labelledby="flightDetailsModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="flightDetailsModalLabel">
              <span class="modal-airline-name">Airline Name</span>
            </h5>
            <div class="small text-muted">
              Flight <span class="modal-flight-code">XX 000</span> ·
              <span class="modal-trip-type">One way</span>
            </div>
          </div>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>

        <div class="modal-body">
          <div class="row gy-3">
            <div class="col-12 col-md-7">
              <h6 class="mb-2">Route</h6>
              <p class="mb-1 fw-semibold modal-route">
                Amman → Istanbul
              </p>

              <div class="d-flex justify-content-between small text-muted mb-2">
                <span>Departure</span>
                <span>Arrival</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold modal-departure">08:30</div>
                <div class="text-center small text-muted">
                  Duration:
                  <span class="modal-duration">4h</span><br />
                  Stops:
                  <span class="modal-stops">Non stop</span>
                </div>
                <div class="fw-semibold modal-arrival">12:30</div>
              </div>
            </div>

            <div class="col-12 col-md-5">
              <h6 class="mb-2">Fare details</h6>
              <div class="border rounded-3 p-3 mb-2">
                <div class="d-flex justify-content-between mb-1">
                  <span class="small text-muted">Base price</span>
                  <span class="small modal-price">$220</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="small text-muted">Taxes & fees</span>
                  <span class="small">Included</span>
                </div>
                <hr class="my-2" />
                <div class="d-flex justify-content-between">
                  <strong>Total</strong>
                  <strong class="modal-price">$220</strong>
                </div>
              </div>
              <p class="small text-muted mb-0">
                * Final price may change slightly depending on payment method and
                currency conversion.
              </p>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-outline-secondary btn-sm"
            data-bs-dismiss="modal"
          >
            Close
          </button>
          <button type="button" class="btn btn-primary btn-sm" id="modalBookBtn">
            Book this flight
          </button>
        </div>
      </div>
    </div>
  </div>

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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="./assets/js/home.js"></script>
  <script src="./assets/js/fligth.js"></script>

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
