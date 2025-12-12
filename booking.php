<?php
session_start();

/* ================== DB CONNECTION ================== */
$host     = 'localhost';
$dbname   = 'travelo';
$username = 'root';
$password = '';

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
    // لو في مشكلة بالـ DB و في طلب POST (جاينا من fetch)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json', true, 500);
        echo json_encode([
            'success' => false,
            'message' => 'DB connection error'
        ]);
        exit;
    }
    // لو GET عادي، بنكمّل عرض الصفحة بس بدون DB (مش مهم للعرض)
}

/* ========== Helper: generate UNIQUE booking_code ========== */
function generate_booking_code(PDO $pdo): string
{
    do {
        $code = 'TRV-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_code = ?");
        $stmt->execute([$code]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);

    return $code;
}

/* ========== AJAX handler: save booking + payment ========== */
/*
   ملاحظة مهمّة:
   خَلّيت الشرط هو أي POST → معناها جاية من الجافاسكربت
   وما عاد نحتاج field اسمه action=pay
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // تأكيد إنه عندنا $pdo جاهز
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'DB connection not available',
        ]);
        exit;
    }

    try {
        // --------- BOOKING META ---------
        $bookingType   = $_POST['booking_type']   ?? 'flight';
        $bookingStatus = $_POST['booking_status'] ?? 'pending';

        $userId    = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $userName  = trim($_POST['user_name']  ?? '');
        $userEmail = trim($_POST['user_email'] ?? '');

        $flightId  = !empty($_POST['flight_id'])  ? (int)$_POST['flight_id']  : null;
        $hotelId   = !empty($_POST['hotel_id'])   ? (int)$_POST['hotel_id']   : null;
        $packageId = !empty($_POST['package_id']) ? (int)$_POST['package_id'] : null;

        $tripStart = $_POST['trip_start_date'] ?? null;
        $tripEnd   = $_POST['trip_end_date']   ?? $tripStart;

        $adults    = (int)($_POST['travellers_adults']   ?? 1);
        $children  = (int)($_POST['travellers_children'] ?? 0);
        $infants   = (int)($_POST['travellers_infants']  ?? 0);

        $amountFlight   = (float)($_POST['amount_flight']   ?? 0);
        $amountHotel    = (float)($_POST['amount_hotel']    ?? 0);
        $amountPackage  = (float)($_POST['amount_package']  ?? 0);
        $amountTaxes    = (float)($_POST['amount_taxes']    ?? 0);
        $discountAmount = (float)($_POST['discount_amount'] ?? 0);
        $currency       = $_POST['currency'] ?? 'USD';

        // hidden input من الفورم: amount_total (اسم الكولوم نفسه)
        $amountTotalPost = isset($_POST['amount_total']) ? (float)$_POST['amount_total'] : null;
        $subtotal        = $amountFlight + $amountHotel + $amountPackage;
        $amountTotal     = $amountTotalPost !== null
            ? $amountTotalPost
            : $subtotal + $amountTaxes - $discountAmount;

        // --------- booking_code (UNIQUE, ما يضل فاضي) ---------
        $bookingCode = trim($_POST['booking_code'] ?? '');
        if ($bookingCode === '') {
            $bookingCode = generate_booking_code($pdo);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_code = ?");
            $stmt->execute([$bookingCode]);
            if ($stmt->fetchColumn() > 0) {
                $bookingCode = generate_booking_code($pdo);
            }
        }

        // --------- PAYMENT FIELDS ----------
        $paymentMethod  = $_POST['payment_method'] ?? 'visa';
        $promoCode      = trim($_POST['promo_code'] ?? '');
        $cardSaved      = isset($_POST['card_saved']) ? (int)$_POST['card_saved'] : 0;

        $cardHolderName = trim($_POST['card_holder_name'] ?? '');
        $cardNumberRaw  = preg_replace('/\D+/', '', $_POST['card_number'] ?? '');
        $expMonth       = isset($_POST['exp_month']) ? (int)$_POST['exp_month'] : null;
        $expYear        = isset($_POST['exp_year'])  ? (int)$_POST['exp_year']  : null;

        $cardLast4 = $cardNumberRaw ? substr($cardNumberRaw, -4) : null;
        $cardBrand = $paymentMethod;

        $paymentStatus       = 'success'; // لأنو ما في بوابة حقيقية
        $gatewayReferenceStr = 'LOCAL-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        // ========== TRANSACTION ==========
        $pdo->beginTransaction();

        /* ----- 1) INSERT INTO bookings ----- */
               /* ----- 1) INSERT INTO bookings ----- */
        $stmt = $pdo->prepare("
            INSERT INTO bookings
            (
              user_id,
              booking_type,
              hotel_id,
              flight_id,
              package_id,
              booking_code,
              trip_start_date,
              trip_end_date,
              travellers_adults,
              travellers_children,
              travellers_infants,
              currency,
              amount_flight,
              amount_hotel,
              amount_package,
              amount_taxes,
              discount_amount,
              total_amount,
              booking_status,
              notes,
              created_at
            )
            VALUES
            (
              :user_id,
              :booking_type,
              :hotel_id,
              :flight_id,
              :package_id,
              :booking_code,
              :trip_start_date,
              :trip_end_date,
              :adults,
              :children,
              :infants,
              :currency,
              :amount_flight,
              :amount_hotel,
              :amount_package,
              :amount_taxes,
              :discount_amount,
              :total_amount,
              :booking_status,
              NULL,
              NOW()
            )
        ");

        $stmt->execute([
            ':user_id'         => $userId,
            ':booking_type'    => $bookingType,
            ':hotel_id'        => $hotelId,
            ':flight_id'       => $flightId,
            ':package_id'      => $packageId,
            ':booking_code'    => $bookingCode,
            ':trip_start_date' => $tripStart ?: null,
            ':trip_end_date'   => $tripEnd   ?: null,
            ':adults'          => $adults,
            ':children'        => $children,
            ':infants'         => $infants,
            ':currency'        => $currency,
            ':amount_flight'   => $amountFlight,
            ':amount_hotel'    => $amountHotel,
            ':amount_package'  => $amountPackage,
            ':amount_taxes'    => $amountTaxes,
            ':discount_amount' => $discountAmount,
            ':total_amount'    => $amountTotal,
            ':booking_status'  => $bookingStatus,
        ]);

        $bookingId = (int)$pdo->lastInsertId();


        /* ----- 2) INSERT INTO payments ----- */
        $stmt = $pdo->prepare("
            INSERT INTO payments
            (
              booking_id,
              user_id,
              payment_method,
              amount_subtotal,
              amount_tax,
              amount_discount,
              amount_total,
              currency,
              promo_code,
              card_brand,
              card_last4,
              card_holder_name,
              exp_month,
              exp_year,
              card_saved,
              status,
              gateway_reference,
              created_at
            )
            VALUES
            (
              :booking_id,
              :user_id,
              :payment_method,
              :amount_subtotal,
              :amount_tax,
              :amount_discount,
              :amount_total,
              :currency,
              :promo_code,
              :card_brand,
              :card_last4,
              :card_holder_name,
              :exp_month,
              :exp_year,
              :card_saved,
              :status,
              :gateway_reference,
              NOW()
            )
        ");

        $stmt->execute([
            ':booking_id'        => $bookingId,
            ':user_id'           => $userId,
            ':payment_method'    => $paymentMethod,
            ':amount_subtotal'   => $subtotal,
            ':amount_tax'        => $amountTaxes,
            ':amount_discount'   => $discountAmount,
            ':amount_total'      => $amountTotal,
            ':currency'          => $currency,
            ':promo_code'        => $promoCode ?: null,
            ':card_brand'        => $cardBrand,
            ':card_last4'        => $cardLast4,
            ':card_holder_name'  => $cardHolderName ?: null,
            ':exp_month'         => $expMonth,
            ':exp_year'          => $expYear,
            ':card_saved'        => $cardSaved,
            ':status'            => $paymentStatus,
            ':gateway_reference' => $gatewayReferenceStr,
        ]);

        $pdo->commit();

        echo json_encode([
            'success'      => true,
            'booking_id'   => $bookingId,
            'booking_code' => $bookingCode,
            'amount_total' => $amountTotal,
            'currency'     => $currency,
        ]);
        } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // ما نرجّع 500 عشان الجافاسكربت يقدر يقرأ الرسالة
        // http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'DB error: ' . $e->getMessage(),
        ]);
    }

    exit; // مهم: ما نكمّل نرندر HTML
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <title>Travelo · Booking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet"
  />

  <!-- CSS -->
  <link rel="stylesheet" href="./assets/css/booking.css" />
</head>
<body>
  <!-- ===== Header ===== -->
  <header class="booking-header">
    <div class="container d-flex justify-content-between align-items-center py-3">
      <div class="d-flex align-items-center gap-2">
        <img src="./assets/images/logo.svg" alt="Travelo" style="height:32px;">
        <span class="fw-bold">Travelo</span>
      </div>
      <div class="text-end">
        <div class="small text-muted">Booking for</div>
        <div class="fw-semibold" id="headerUserName">
          <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Traveler'); ?>
        </div>
        <div class="small text-muted" id="headerUserEmail">
          <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>
        </div>
      </div>
    </div>
  </header>

  <main class="booking-page py-4">
    <div class="container">

      <!-- ===== Stepper ===== -->
      <ol class="booking-steps mb-4">
        <li class="step-item is-active" data-step="1">
          <span class="step-index">1</span>
          <div class="step-text">
            <span class="step-title">Review & Ticket</span>
            <span class="step-sub">Trip details</span>
          </div>
        </li>
        <li class="step-item" data-step="2">
          <span class="step-index">2</span>
          <div class="step-text">
            <span class="step-title">Payment</span>
            <span class="step-sub">Secure checkout</span>
          </div>
        </li>
        <li class="step-item" data-step="3">
          <span class="step-index">3</span>
          <div class="step-text">
            <span class="step-title">Done</span>
            <span class="step-sub">Confirmation</span>
          </div>
        </li>
      </ol>

      <!-- ====== STEP 1: Ticket & Summary ====== -->
      <section class="step-panel is-active" data-step-panel="1">
        <div class="row g-4 align-items-start">
          <!-- Ticket -->
          <div class="col-12 col-lg-8">
            <article class="ticket-card" id="ticketCard">
              <header class="ticket-header d-flex justify-content-between align-items-center">
                <div>
                  <div class="ticket-badge" id="ticketTypeBadge">Flight ticket</div>
                  <h2 class="ticket-title mb-0" id="ticketTitle">Your trip</h2>
                  <p class="ticket-subtext mb-0" id="ticketSubtitle">
                    Review your booking before continuing to payment.
                  </p>
                </div>
                <div class="text-end">
                  <div class="ticket-code-label">Booking reference</div>
                  <div class="ticket-code-value" id="ticketBookingCode">TRV-XXXX</div>
                </div>
              </header>

              <div class="ticket-body">
                <div class="ticket-main-row d-flex justify-content-between align-items-center">
                  <div class="ticket-main-left">
                    <div class="ticket-route" id="ticketRouteMain"></div>
                    <div class="ticket-meta" id="ticketMetaLine"></div>
                    <div class="ticket-dates" id="ticketDates"></div>
                  </div>
                  <div class="ticket-main-right text-end">
                    <div class="ticket-user-label">Traveler</div>
                    <div class="ticket-user-name" id="ticketUserName">
                      <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest'); ?>
                    </div>
                  </div>
                </div>

                <div class="ticket-extra-row" id="ticketExtraRow"></div>
              </div>

              <footer class="ticket-footer d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                  <span id="ticketNotes">Please make sure all details are correct before payment.</span>
                </div>
                <div class="text-end">
                  <div class="small text-muted">Total amount</div>
                  <div class="ticket-total" id="ticketTotalAmount">$0.00</div>
                </div>
              </footer>
            </article>
          </div>

          <!-- Price breakdown -->
          <div class="col-12 col-lg-4">
            <aside class="summary-card">
              <h3 class="summary-title">Price breakdown</h3>

              <div class="summary-row" id="rowAmountFlight">
                <span>Flights</span>
                <span id="amountFlightValue">$0.00</span>
              </div>
              <div class="summary-row" id="rowAmountHotel">
                <span>Hotels</span>
                <span id="amountHotelValue">$0.00</span>
              </div>
              <div class="summary-row" id="rowAmountPackage">
                <span>Packages</span>
                <span id="amountPackageValue">$0.00</span>
              </div>

              <hr>

              <div class="summary-row">
                <span>Taxes & fees</span>
                <span id="amountTaxesValue">$0.00</span>
              </div>
              <div class="summary-row" id="rowDiscount">
                <span>Discount</span>
                <span id="amountDiscountValue">-$0.00</span>
              </div>

              <hr>

              <div class="summary-row summary-total">
                <span>Total</span>
                <span id="amountTotalValue">$0.00</span>
              </div>

              <p class="small text-muted mt-2">
                Final total will be charged in <span id="currencyLabel">USD</span> using the selected payment method.
              </p>
            </aside>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-4">
          <button class="btn btn-outline-secondary" id="btnStep1BackHome">
            <i class="bi bi-arrow-left"></i> Back to search
          </button>
          <button class="btn btn-primary" id="btnToPayment">
            Next: Payment <i class="bi bi-arrow-right ms-1"></i>
          </button>
        </div>
      </section>

      <!-- ====== STEP 2: Payment ====== -->
      <section class="step-panel" data-step-panel="2">
        <div class="row g-4 align-items-start">
          <div class="col-12 col-lg-7">
            <div class="payment-card">
              <h3 class="payment-title mb-3">Payment method</h3>

              <div class="payment-methods mb-3">
                <label class="payment-option">
                  <input type="radio" name="payment_method" value="visa" checked />
                  <div class="payment-option-body">
                    <div class="payment-option-main">
                      <span><i class="bi bi-credit-card-2-front"></i> Visa</span>
                      <span class="payment-badge">Recommended</span>
                    </div>
                    <div class="payment-option-sub">Pay securely with your Visa credit or debit card.</div>
                  </div>
                </label>

                <label class="payment-option">
                  <input type="radio" name="payment_method" value="mastercard" />
                  <div class="payment-option-body">
                    <div class="payment-option-main">
                      <span><i class="bi bi-credit-card"></i> Mastercard</span>
                    </div>
                    <div class="payment-option-sub">Use any supported Mastercard card.</div>
                  </div>
                </label>

                <label class="payment-option">
                  <input type="radio" name="payment_method" value="cashcard" />
                  <div class="payment-option-body">
                    <div class="payment-option-main">
                      <span><i class="bi bi-wallet2"></i> Cash card / Offline</span>
                    </div>
                    <div class="payment-option-sub">
                      Reserve now and pay later at the airport or travel office.
                    </div>
                  </div>
                </label>
              </div>

              <!-- Card details -->
              <form id="paymentForm" novalidate>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label">Card holder name</label>
                    <input type="text" class="form-control" id="cardHolder" placeholder="As written on card" />
                  </div>
                  <div class="col-12">
                    <label class="form-label">Card number</label>
                    <input type="text" class="form-control" id="cardNumber" placeholder="XXXX XXXX XXXX XXXX" />
                  </div>
                  <div class="col-6 col-md-4">
                    <label class="form-label">Expiry month</label>
                    <input type="text" class="form-control" id="expMonth" placeholder="MM" />
                  </div>
                  <div class="col-6 col-md-4">
                    <label class="form-label">Expiry year</label>
                    <input type="text" class="form-control" id="expYear" placeholder="YYYY" />
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label">CVV</label>
                    <input type="password" class="form-control" id="cvv" placeholder="123" />
                  </div>

                  <div class="col-12">
                    <label class="form-label">Promo code</label>
                    <div class="input-group">
                      <input type="text" class="form-control" id="promoCode" placeholder="Optional" />
                      <button class="btn btn-outline-secondary" type="button" id="btnApplyPromo">
                        Apply
                      </button>
                    </div>
                    <div class="form-text" id="promoHelp">
                      If you have a Travelo promo code, enter it here.
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="saveCard" />
                      <label class="form-check-label" for="saveCard">
                        Save card details for future bookings
                      </label>
                    </div>
                  </div>
                </div>

                <div class="text-danger small mt-2" id="paymentError" style="display:none;"></div>

                <div class="d-flex justify-content-between mt-4">
                  <button type="button" class="btn btn-outline-secondary" id="btnBackToStep1">
                    <i class="bi bi-arrow-left"></i> Back
                  </button>
                  <button type="submit" class="btn btn-primary" id="btnPayNow">
                    Pay now <span id="btnPayAmountLabel"></span>
                  </button>
                </div>
              </form>
            </div>
          </div>

          <div class="col-12 col-lg-5">
            <aside class="summary-card">
              <h3 class="summary-title">Order summary</h3>
              <p class="small mb-2" id="summaryTripLine"></p>

              <div class="summary-row">
                <span>Subtotal</span>
                <span id="summarySubtotal">$0.00</span>
              </div>
              <div class="summary-row">
                <span>Taxes & fees</span>
                <span id="summaryTaxes">$0.00</span>
              </div>
              <div class="summary-row" id="summaryDiscountRow">
                <span>Discount</span>
                <span id="summaryDiscount">-$0.00</span>
              </div>
              <hr />
              <div class="summary-row summary-total">
                <span>Total to pay</span>
                <span id="summaryTotal">$0.00</span>
              </div>

              <p class="small text-muted mt-2">
                Your payment is processed securely. We never store full card numbers.
              </p>
            </aside>
          </div>
        </div>
      </section>

      <!-- ====== STEP 3: Done ====== -->
      <section class="step-panel" data-step-panel="3">
        <div class="row justify-content-center">
          <div class="col-12 col-lg-8">
            <div class="done-card text-center">
              <div class="done-icon mb-3">
                <i class="bi bi-check-circle-fill"></i>
              </div>
              <h2 class="mb-2">Booking confirmed</h2>
              <p class="mb-3">
                Thank you, <span id="doneUserName">Traveler</span>.  
                Your booking is now <strong>confirmed</strong>.
              </p>

              <div class="done-info mb-3">
                <div class="done-row">
                  <span>Booking reference</span>
                  <span id="doneBookingCode">TRV-XXXX</span>
                </div>
                <div class="done-row">
                  <span>Trip</span>
                  <span id="doneTripLine"></span>
                </div>
                <div class="done-row">
                  <span>Total paid</span>
                  <span id="doneTotalPaid">$0.00</span>
                </div>
              </div>

              <p class="small text-muted mb-4">
                We’ve sent a copy of your ticket to <span id="doneUserEmail"></span>.  
                You can print or download your ticket anytime.
              </p>

              <div class="d-flex justify-content-center gap-3">
                <button class="btn btn-outline-secondary" id="btnBackHome">
                  Back to homepage
                </button>
                <button class="btn btn-primary" id="btnPrintTicket">
                  <i class="bi bi-printer me-1"></i> Print ticket
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== Hidden meta form ===== -->
      <form id="bookingMeta" class="d-none">
        <input type="hidden" id="hfBookingType"   name="booking_type" />

        <input type="hidden" id="hfUserId"   name="user_id"
               value="<?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : ''; ?>">
        <input type="hidden" id="hfUserName" name="user_name"
               value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES); ?>">
        <input type="hidden" id="hfUserEmail" name="user_email"
               value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES); ?>">

        <input type="hidden" id="hfFlightId"      name="flight_id" />
        <input type="hidden" id="hfHotelId"       name="hotel_id" />
        <input type="hidden" id="hfPackageId"     name="package_id" />

        <input type="hidden" id="hfTripStart"     name="trip_start_date" />
        <input type="hidden" id="hfTripEnd"       name="trip_end_date" />
        <input type="hidden" id="hfAdults"        name="travellers_adults" />
        <input type="hidden" id="hfChildren"      name="travellers_children" />
        <input type="hidden" id="hfInfants"       name="travellers_infants" />

        <input type="hidden" id="hfBookingCode"   name="booking_code" />
        <input type="hidden" id="hfStayNights"    name="stay_nights" />

        <input type="hidden" id="hfAmountFlight"  name="amount_flight" />
        <input type="hidden" id="hfAmountHotel"   name="amount_hotel" />
        <input type="hidden" id="hfAmountPackage" name="amount_package" />
        <input type="hidden" id="hfAmountTaxes"   name="amount_taxes" />
        <input type="hidden" id="hfDiscount"      name="discount_amount" />
        <input type="hidden" id="hfCurrency"      name="currency" />
        <!-- اسم الحقل زي الكولوم بالجدول -->
        <input type="hidden" id="hfTotalAmount"   name="amount_total" />
      </form>
    </div>
  </main>

  <!-- Session info للـ JS لو احتاج -->
  <script>
    window.TRAVELO = {
      isLoggedIn: <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>,
      userId: <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?>,
      userName: <?php echo json_encode($_SESSION['user_name'] ?? ''); ?>,
      userEmail: <?php echo json_encode($_SESSION['user_email'] ?? ''); ?>
    };
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="./assets/js/booking.js"></script>
</body>
</html>
