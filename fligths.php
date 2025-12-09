<?php
require __DIR__ . '/db.php'; // فيه $pdo

// نجيب كل الرحلات من جدول flights مع معلومات إضافية من destinations
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
    $flights = $stmt->fetchAll(); // FETCH_ASSOC من db.php
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Flight Tickets</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <link rel="stylesheet" href="./assets/css/home.css" />
  <link rel="stylesheet" href="./assets/css/fligth.css" />
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
          <button id="btnLogin" type="button" class="sign_in">Login</button>
          <button id="btnLogin1" class="sign_up">Sign up</button>
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
            <?php echo count($flights); ?> results found
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
                $stopsLabel  = $stops === 0 ? 'Non stop' : $stops . ' stop' . ($stops > 1 ? 's' : '');
                $durationLbl = formatDuration($duration);
                $airlineCode = $flight['airline_code'] ?: substr($flight['airline_name'], 0, 3);
                $destCity    = $flight['dest_city'] ?: $flight['destination_city'];
                $destCountry = $flight['dest_country'] ?: '';
              ?>
              <article
                class="flight-card ticket"
                data-price="<?php echo htmlspecialchars($price); ?>"
                data-duration="<?php echo htmlspecialchars($duration); ?>"
                data-stops="<?php echo htmlspecialchars($stops); ?>"
                data-trip="<?php echo htmlspecialchars($tripType); ?>"
              >
                <div class="ticket-inner">
                  <div class="ticket-left">
                    <div class="ticket-row-top">
                      <div class="airline">
                        <div class="logo-circle">
                          <?php echo htmlspecialchars($airlineCode); ?>
                        </div>
                        <div>
                          <div class="airline-name">
                            <?php echo htmlspecialchars($flight['airline_name']); ?>
                          </div>
                          <div class="airline-sub">
                            <?php echo htmlspecialchars($flight['fare_subtitle'] ?: 'Economy'); ?>
                          </div>
                        </div>
                      </div>
                      <div class="ticket-code">
                        <?php echo htmlspecialchars($flight['flight_number']); ?>
                      </div>
                    </div>

                    <div class="ticket-route">
                      <span class="city" data-code="<?php echo htmlspecialchars($flight['origin_airport_code']); ?>">
                        <?php echo htmlspecialchars($flight['origin_city']); ?>
                      </span>
                      <span class="arrow"><?php echo $tripArrow; ?></span>
                      <span class="city" data-code="<?php echo htmlspecialchars($flight['destination_airport_code']); ?>">
                        <?php echo htmlspecialchars($destCity); ?>
                      </span>
                    </div>

                    <div class="ticket-times">
                      <div class="time">
                        <strong><?php echo htmlspecialchars(substr($flight['departure_time'], 0, 5)); ?></strong>
                        <span class="code"><?php echo htmlspecialchars($flight['origin_airport_code']); ?></span>
                      </div>

                      <div class="time-line">
                        <span class="duration-label">
                          <?php echo htmlspecialchars($durationLbl); ?>
                        </span>
                        <div class="dashed-line"></div>
                        <span class="nonstop-label"><?php echo htmlspecialchars($stopsLabel); ?></span>
                      </div>

                      <div class="time text-end">
                        <strong><?php echo htmlspecialchars(substr($flight['arrival_time'], 0, 5)); ?></strong>
                        <span class="code"><?php echo htmlspecialchars($flight['destination_airport_code']); ?></span>
                      </div>
                    </div>

                    <div class="ticket-extra">
                      <?php if (!empty($flight['extras'])): ?>
                        <?php
                          $extras = array_map('trim', explode(',', $flight['extras']));
                          foreach ($extras as $extra):
                            if ($extra === '') continue;
                        ?>
                          <span><?php echo htmlspecialchars($extra); ?></span>
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
                      $<?php echo number_format($price, 2); ?>
                    </strong>
                    <button class="btn-primary">Book Now</button>
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

  <!-- مودال التفاصيل (زي ما عندك) -->
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
          <button type="button" class="btn btn-primary btn-sm">
            Book this flight
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- الفوتر نفسه تبعك -->
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="./assets/js/home.js"></script>
  <script src="./assets/js/fligth.js"></script>
</body>
</html>
