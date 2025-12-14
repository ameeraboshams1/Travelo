<?php
// ================== CONFIG ==================
session_start();

$host     = 'localhost';
$dbname   = 'travelo';
$username = 'root';
$password = '';

$loginUrl = 'login.html'; // عدليه لو عندك login.php

if (!isset($_SESSION['user_id'])) {
  header("Location: $loginUrl");
  exit;
}

// ================== DB CONNECTION (PDO) ==================
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
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function money($n, $cur='USD') {
  $n = (float)$n;
  return (h($cur) . ' ' . number_format($n, 2));
}

function statusClass($s) {
  $s = strtolower((string)$s);
  if ($s === 'confirmed') return 'st-confirmed';
  if ($s === 'cancelled') return 'st-cancelled';
  return 'st-pending';
}
function statusLabel($s) {
  $s = strtolower((string)$s);
  if ($s === 'confirmed') return 'Confirmed';
  if ($s === 'cancelled') return 'Cancelled';
  return 'Pending';
}

function typeLabel($t) {
  $t = strtolower((string)$t);
  if ($t === 'flight') return 'Flight Ticket';
  if ($t === 'hotel') return 'Hotel Booking';
  return 'Package Booking';
}
function typeBadgeClass($t) {
  $t = strtolower((string)$t);
  if ($t === 'flight') return 'bdg-flight';
  if ($t === 'hotel') return 'bdg-hotel';
  return 'bdg-package';
}
function travellersText($a,$c,$i) {
  $parts = [];
  $a = (int)$a; $c = (int)$c; $i = (int)$i;
  if ($a > 0) $parts[] = $a . ' adult' . ($a > 1 ? 's' : '');
  if ($c > 0) $parts[] = $c . ' child' . ($c > 1 ? 'ren' : '');
  if ($i > 0) $parts[] = $i . ' infant' . ($i > 1 ? 's' : '');
  return $parts ? implode(' • ', $parts) : '1 adult';
}
function fmtDate($d, $format='D, d M Y') {
  if (!$d) return '';
  $ts = strtotime($d);
  if (!$ts) return '';
  return date($format, $ts);
}
function nightsBetween($start, $end) {
  if (!$start || !$end) return 0;
  try {
    $d1 = new DateTime($start);
    $d2 = new DateTime($end);
    $days = (int)$d1->diff($d2)->days;
    return max(1, $days);
  } catch (Throwable $e) {
    return 0;
  }
}


// ================== ACTIONS (Cancel / Restore) ==================
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action    = $_POST['action'] ?? '';
  $bookingId = (int)($_POST['booking_id'] ?? 0);
  $userId    = (int)$_SESSION['user_id'];

  if ($bookingId > 0 && in_array($action, ['cancel','restore'], true)) {
    try {
      $pdo->beginTransaction();

      // booking
      $stmt = $pdo->prepare("SELECT id, booking_status FROM bookings WHERE id=? AND user_id=? LIMIT 1");
      $stmt->execute([$bookingId, $userId]);
      $b = $stmt->fetch();

      if (!$b) {
        $pdo->rollBack();
        $flash = ['type'=>'error','msg'=>'Booking not found.'];
      } else {
        $currentBooking = strtolower($b['booking_status'] ?? 'pending');

        // last payment (if exists)
        $pstmt = $pdo->prepare("
          SELECT id, status
          FROM payments
          WHERE booking_id=? AND user_id=?
          ORDER BY id DESC
          LIMIT 1
        ");
        $pstmt->execute([$bookingId, $userId]);
        $pay = $pstmt->fetch();
        $payStatus = $pay ? strtolower($pay['status']) : null;

        // ===== CANCEL =====
        if ($action === 'cancel') {

          // pending booking: cancel allowed
          if ($currentBooking === 'pending') {

            $pdo->prepare("UPDATE bookings SET booking_status='cancelled' WHERE id=? AND user_id=?")
                ->execute([$bookingId, $userId]);

            // if there is a pending payment, mark it failed
            if ($pay && $payStatus === 'pending') {
              $pdo->prepare("UPDATE payments SET status='failed' WHERE id=? AND user_id=?")
                  ->execute([(int)$pay['id'], $userId]);
            }

            $pdo->commit();
            $flash = ['type'=>'ok','msg'=>'Booking cancelled successfully.'];

          }
          // confirmed booking: only cancel if paid => refund
          elseif ($currentBooking === 'confirmed') {

            if ($pay && $payStatus === 'success') {
              $pdo->prepare("UPDATE bookings SET booking_status='cancelled' WHERE id=? AND user_id=?")
                  ->execute([$bookingId, $userId]);

              $pdo->prepare("UPDATE payments SET status='refunded' WHERE id=? AND user_id=?")
                  ->execute([(int)$pay['id'], $userId]);

              $pdo->commit();
              $flash = ['type'=>'ok','msg'=>'Booking cancelled & refunded.'];
            } else {
              $pdo->rollBack();
              $flash = ['type'=>'warn','msg'=>'Cannot cancel a confirmed booking unless payment is successful.'];
            }

          } else {
            $pdo->rollBack();
            $flash = ['type'=>'warn','msg'=>'This booking is already cancelled.'];
          }
        }

        // ===== RESTORE =====
        if ($action === 'restore') {

          if ($currentBooking !== 'cancelled') {
            $pdo->rollBack();
            $flash = ['type'=>'warn','msg'=>'Only cancelled bookings can be restored.'];
          } else {
            // if last payment is success => don't allow restore
            if ($pay && $payStatus === 'success') {
              $pdo->rollBack();
              $flash = ['type'=>'warn','msg'=>'Cannot restore because there is a successful payment for this booking.'];
            } else {
              $pdo->prepare("UPDATE bookings SET booking_status='pending' WHERE id=? AND user_id=?")
                  ->execute([$bookingId, $userId]);

              $pdo->commit();
              $flash = ['type'=>'ok','msg'=>'Booking restored to pending.'];
            }
          }
        }
      }
    } catch (PDOException $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $flash = ['type'=>'error','msg'=>'Action failed.'];
    }
  }
}


// ================== FETCH BOOKINGS (WITH JOINS) ==================
$userId = (int)$_SESSION['user_id'];
$bookings = [];

try {
  $stmt = $pdo->prepare("
    SELECT
      b.*,

      -- Flight (direct booking)
      f.airline_name              AS f_airline_name,
      f.flight_number             AS f_flight_number,
      f.trip_type                 AS f_trip_type,
      f.departure_date            AS f_departure_date,
      f.return_date               AS f_return_date,
      f.origin_city               AS f_origin_city,
      f.origin_airport_code       AS f_origin_code,
      f.destination_city          AS f_dest_city,
      f.destination_airport_code  AS f_dest_code,
      f.departure_time            AS f_departure_time,
      f.arrival_time              AS f_arrival_time,
      f.duration_hours            AS f_duration_hours,
      f.stops_count               AS f_stops_count,

      -- Hotel (direct booking)
      h.name                      AS h_name,
      h.location_text             AS h_location,
      h.rating                    AS h_rating,
      h.reviews_count             AS h_reviews,

      -- Package
      p.title                     AS p_title,
      p.location                  AS p_location,
      p.from_city                 AS p_from_city,
      p.duration_days             AS p_days,
      p.badge_type                AS p_badge,
      p.rating                    AS p_rating,
      p.reviews_count             AS p_reviews,

      -- Package included flight/hotel (via packages table)
      pf.airline_name             AS pf_airline_name,
      pf.flight_number            AS pf_flight_number,
      pf.trip_type                AS pf_trip_type,
      pf.departure_date           AS pf_departure_date,
      pf.return_date              AS pf_return_date,
      pf.origin_city              AS pf_origin_city,
      pf.origin_airport_code      AS pf_origin_code,
      pf.destination_city         AS pf_dest_city,
      pf.destination_airport_code AS pf_dest_code,
      pf.departure_time           AS pf_departure_time,
      pf.arrival_time             AS pf_arrival_time,
      pf.duration_hours           AS pf_duration_hours,
      pf.stops_count              AS pf_stops_count,

      ph.name                     AS ph_name,
      ph.location_text            AS ph_location,
      ph.rating                   AS ph_rating,
      ph.reviews_count            AS ph_reviews

    FROM bookings b
    LEFT JOIN flights   f  ON f.id  = b.flight_id
    LEFT JOIN hotels    h  ON h.id  = b.hotel_id
    LEFT JOIN packages  p  ON p.id  = b.package_id
    LEFT JOIN flights   pf ON pf.id = p.flight_id
    LEFT JOIN hotels    ph ON ph.id = p.hotel_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
  ");
  $stmt->execute([$userId]);
  $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
  $bookings = [];
}

$userName  = $_SESSION['user_name']  ?? 'Traveler';
$userEmail = $_SESSION['user_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Travelo · My bookings</title>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
  <link href="./assets/css/home.css" rel="stylesheet">
   <script src='https://cdn.jotfor.ms/agent/embedjs/019b189a507c7f0e98a0580ad136880f79ad/embed.js'>
</script>
  <style>
    :root{
      --ink:#0f172a;
      --muted:#64748b;
      --bg:#f6f7fb;
      --surface:#ffffff;
      --border:#e9eaf3;
      --shadow:0 18px 50px rgba(15,23,42,.10);
      --accent:#7c3aed;
      --accent2:#6c63ff;
      --halo:rgba(124,58,237,.14);
      --radius:18px;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family:"Plus Jakarta Sans",system-ui,-apple-system,Segoe UI,Roboto,Arial;
      background:
        radial-gradient(900px 380px at 30% 20%, rgba(124,58,237,.12), transparent 60%),
        radial-gradient(900px 380px at 80% 40%, rgba(108,99,255,.10), transparent 60%),
        var(--bg);
      color:var(--ink);
    }
    a{color:inherit;text-decoration:none}
    .container{width:min(1180px, 92%); margin:0 auto}

    /* ====== (تركنا nav-user CSS تبعك زي ما هو) ====== */
    .nav-user { position: relative; display: flex; align-items: center; gap: 8px; z-index: 100; }
    .nav-button .user-toggle {
      display: inline-flex; align-items: center; gap: 10px;
      padding: 5px 16px 5px 8px; border-radius: 999px; border: none; outline: none;
      background: rgba(255, 255, 255, 0.92); cursor: pointer;
      font-family: "Plus Jakarta Sans", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
      font-size: 14px; font-weight: 600; color: #0f172a;
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(255, 255, 255, 0.3) inset;
      backdrop-filter: blur(12px) saturate(180%);
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative; overflow: hidden;
    }
    .nav-button .user-toggle::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
    }
    .nav-button .user-toggle:hover {
      transform: translateY(-1.5px);
      box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14), 0 0 0 1px rgba(255, 255, 255, 0.4) inset;
      background: rgba(255, 255, 255, 0.98);
    }
    .nav-button .user-toggle:active { transform: translateY(0); transition-duration: 0.1s; }
    .user-avatar {
      width: 32px; height: 32px; border-radius: 999px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #ffffff; display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 14px;
      box-shadow: 0 3px 8px rgba(102, 126, 234, 0.3);
      position: relative; overflow: hidden; transition: transform 0.3s ease;
    }
    .user-avatar::after {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transform: translateX(-100%);
    }
    .nav-button .user-toggle:hover .user-avatar { transform: scale(1.05) rotate(5deg); }
    .nav-button .user-toggle:hover .user-avatar::after { animation: shimmer 1.5s infinite; }
    @keyframes shimmer { 100% { transform: translateX(100%); } }
    .user-text { white-space: nowrap; color: #0f172a; font-weight: 600; font-size: 14px; letter-spacing: -0.01em; position: relative; }
    .user-menu {
      position: absolute; right: 0; top: calc(100% + 8px);
      min-width: 200px; background: rgba(255, 255, 255, 0.98);
      border-radius: 16px; padding: 8px 0; display: none;
      z-index: 1000;
      box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(255, 255, 255, 0.1) inset;
      backdrop-filter: blur(20px);
      opacity: 0; transform: translateY(-10px);
      animation: menuFadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
      overflow: hidden;
    }
    @keyframes menuFadeIn { to { opacity: 1; transform: translateY(0); } }
    .user-menu::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
      background: linear-gradient(90deg, transparent, rgba(124, 58, 237, 0.2), transparent);
    }
    .user-menu a, .user-menu form button {
      display: flex; align-items: center; gap: 10px;
      width: 100%; text-align: left; padding: 10px 18px;
      font-size: 14px; font-weight: 500;
      font-family: "Plus Jakarta Sans", system-ui, sans-serif;
      background: transparent; border: none; cursor: pointer;
      color: #475569; transition: all 0.2s ease; position: relative;
    }
    .user-menu hr {
      border: none; height: 1px;
      background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
      margin: 6px 16px;
    }
    .user-menu.show { display: block; }

    /* ===== Page ===== */
    .page-head{padding:26px 0 14px;}
    .head-row{display:flex; align-items:flex-end; justify-content:space-between; gap:18px; flex-wrap:wrap;}
    .title h1{margin:0; font-size:28px; letter-spacing:-.02em}
    .title p{margin:6px 0 0; color:var(--muted); font-weight:600}
    .toolbar{display:flex; gap:10px; flex-wrap:wrap; align-items:center;}
    .input{
      display:flex; align-items:center; gap:10px;
      background:rgba(255,255,255,.92);
      border:1px solid rgba(233,234,243,.9);
      border-radius:999px;
      padding:10px 14px;
      box-shadow: 0 10px 26px rgba(15,23,42,.06);
      min-width:260px;
    }
    .input i{color:#94a3b8}
    .input input{border:0; outline:0; width:100%; background:transparent; font-family:inherit; font-weight:600; color:var(--ink);}
    .tabs{
      display:flex; gap:8px; flex-wrap:wrap;
      background:rgba(255,255,255,.92);
      border:1px solid rgba(233,234,243,.9);
      border-radius:999px;
      padding:6px;
      box-shadow: 0 10px 26px rgba(15,23,42,.06);
    }
    .tab{
      border:0; cursor:pointer;
      padding:9px 12px;
      border-radius:999px;
      font-weight:800;
      color:#475569;
      background:transparent;
      font-family:inherit;
      display:flex; align-items:center; gap:8px;
    }
    .tab.active{
      background:linear-gradient(135deg, var(--accent), var(--accent2));
      color:#fff;
      box-shadow: 0 14px 34px rgba(124,58,237,.25);
    }

    .list{padding:10px 0 40px; display:grid; gap:16px;}

    .ticket{
      background:rgba(255,255,255,.92);
      border:1px solid rgba(233,234,243,.9);
      border-radius:22px;
      box-shadow: var(--shadow);
      overflow:hidden;
      position:relative;
    }
    .ticket:before{
      content:"";
      position:absolute; inset:-2px;
      background: radial-gradient(700px 260px at 20% 0%, rgba(124,58,237,.18), transparent 60%),
                  radial-gradient(700px 260px at 90% 30%, rgba(108,99,255,.14), transparent 60%);
      pointer-events:none;
    }
    .ticket-inner{
      position:relative;
      padding:18px;
      display:grid;
      grid-template-columns: 1.4fr .8fr;
      gap:16px;
    }
    @media (max-width: 980px){ .ticket-inner{grid-template-columns:1fr} }

    .ticket-left{
      background:rgba(255,255,255,.80);
      border:1px solid rgba(233,234,243,.9);
      border-radius:20px;
      padding:16px 16px 14px;
      position:relative;
      overflow:hidden;
    }
    .ticket-left:after,.ticket-left:before{
      content:"";
      position:absolute; top:50%;
      width:28px; height:28px; border-radius:999px;
      background:var(--bg);
      border:1px solid rgba(233,234,243,.9);
      transform:translateY(-50%);
    }
    .ticket-left:after{ left:-14px; }
    .ticket-left:before{ right:-14px; }

    .t-top{display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-start;}
    .badge{
      display:inline-flex; align-items:center; gap:8px;
      padding:6px 10px;
      border-radius:999px;
      font-weight:900;
      font-size:12px;
      letter-spacing:.06em;
      text-transform:uppercase;
      border:1px solid rgba(233,234,243,.9);
      background:rgba(124,58,237,.08);
      color:var(--accent);
    }
    .bdg-flight{background:rgba(59,130,246,.10); color:#2563eb}
    .bdg-hotel{background:rgba(16,185,129,.10); color:#059669}
    .bdg-package{background:rgba(124,58,237,.10); color:var(--accent)}
    .ref{text-align:right;}
    .ref small{display:block; color:#94a3b8; font-weight:800; letter-spacing:.12em}
    .ref .code{
      margin-top:6px; display:inline-flex;
      padding:8px 12px; border-radius:999px;
      background:rgba(255,255,255,.9);
      border:1px solid rgba(233,234,243,.9);
      font-weight:900; letter-spacing:.02em;
    }

    .t-title{margin:10px 0 2px; font-size:22px; letter-spacing:-.02em}
    .t-sub{margin:0 0 10px; color:var(--muted); font-weight:700}

    .divider{border-top:1px dashed rgba(148,163,184,.55); margin:12px 0;}

    /* ✅ أهم تعديل: meta صار 4 أعمدة */
    .meta{display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap:12px;}
    @media (max-width: 980px){ .meta{grid-template-columns: repeat(2, minmax(0,1fr));} }
    @media (max-width: 720px){ .meta{grid-template-columns: 1fr;} }

    .kv small{display:block; color:#94a3b8; font-weight:900; letter-spacing:.12em; text-transform:uppercase; margin-bottom:6px;}
    .kv div{font-weight:900; color:#0b1220;}

    .note{margin-top:12px; color:#64748b; font-weight:700; font-size:12.5px;}
    .total-mini{margin-top:10px; display:flex; justify-content:space-between; align-items:flex-end; gap:10px;}
    .total-mini small{color:#94a3b8; font-weight:900; letter-spacing:.12em; text-transform:uppercase}
    .total-mini b{color:var(--accent); font-size:18px;}

    .pricebox{
      background:rgba(255,255,255,.88);
      border:1px solid rgba(233,234,243,.9);
      border-radius:20px;
      padding:16px;
      box-shadow: 0 18px 50px rgba(15,23,42,.08);
    }
    .pricebox h3{margin:0 0 10px; font-size:16px; letter-spacing:-.01em;}
    .rowp{display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid rgba(233,234,243,.9); color:#334155; font-weight:800;}
    .rowp:last-child{border-bottom:0}
    .rowp.total{padding-top:12px;}
    .rowp.total span:last-child{color:var(--accent); font-size:18px;}
    .pb-note{margin:10px 0 0; color:#64748b; font-weight:700; font-size:13px; line-height:1.5;}

    .actions{position:relative; display:flex; justify-content:space-between; align-items:center; gap:10px; padding:12px 18px 16px; flex-wrap:wrap;}
    .chips{display:flex; gap:8px; flex-wrap:wrap; align-items:center;}
    .chip{
      display:inline-flex; align-items:center; gap:8px;
      padding:8px 12px;
      border-radius:999px;
      background:rgba(255,255,255,.9);
      border:1px solid rgba(233,234,243,.9);
      color:#334155;
      font-weight:900;
      font-size:12.5px;
    }
    .st-pending{background:rgba(245,158,11,.10); color:#b45309; border-color:rgba(245,158,11,.25)}
    .st-confirmed{background:rgba(16,185,129,.10); color:#047857; border-color:rgba(16,185,129,.25)}
    .st-cancelled{background:rgba(239,68,68,.10); color:#b91c1c; border-color:rgba(239,68,68,.25)}

    .btns{display:flex; gap:10px; flex-wrap:wrap;}
    .btn{
      border:0; cursor:pointer;
      border-radius:999px;
      padding:10px 14px;
      font-weight:900;
      font-family:inherit;
      display:inline-flex; align-items:center; gap:10px;
    }
    .btn-ghost{background:transparent; border:1px solid rgba(148,163,184,.55); color:#334155;}
    .btn-primary{background:linear-gradient(135deg, var(--accent), var(--accent2)); color:#fff; box-shadow: 0 16px 36px rgba(124,58,237,.28);}
    .btn-danger{background:rgba(239,68,68,.10); color:#b91c1c; border:1px solid rgba(239,68,68,.25);}
    .btn:disabled{opacity:.55; cursor:not-allowed}

    .details{display:none; padding:0 18px 18px;}
    .details.show{display:block}
    .details-card{
      background:rgba(255,255,255,.86);
      border:1px solid rgba(233,234,243,.9);
      border-radius:18px;
      padding:14px;
      color:#334155;
      font-weight:700;
    }
    .grid2{display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:10px;}
    @media (max-width: 720px){ .grid2{grid-template-columns:1fr} }
    .muted{color:var(--muted)}
    .hr{height:1px; background:rgba(233,234,243,.9); margin:10px 0}

    .toast{
      position:fixed;
      left:50%; bottom:18px;
      transform:translateX(-50%) translateY(10px);
      background:rgba(15,23,42,.92);
      color:#fff;
      padding:10px 14px;
      border-radius:999px;
      font-weight:800;
      box-shadow: 0 18px 50px rgba(15,23,42,.20);
      opacity:0;
      pointer-events:none;
      transition:.25s;
      z-index:9999;
    }
    .toast.show{opacity:1; transform:translateX(-50%) translateY(0)}
  </style>
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
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="./fligths.php">Flights</a></li>
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
              </button>

              <div class="user-menu" id="userMenu">
                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <a href="admin-dashboard.php">Admin dashboard</a>
                <?php else: ?>
                  <a href="./myBooking.php">My bookings</a>
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

  <!-- PAGE -->
  <div class="container">
    <div class="page-head">
      <div class="head-row">
        <div class="title">
          <h1>My bookings</h1>
          <p>All your flight / hotel / package reservations in one place.</p>
        </div>

        <div class="toolbar">
          <div class="input">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="searchInput" type="text" placeholder="Search by code, status, type..." />
          </div>

          <div class="tabs" role="tablist">
            <button class="tab active" data-filter="all" type="button"><i class="fa-solid fa-layer-group"></i> All</button>
            <button class="tab" data-filter="flight" type="button"><i class="fa-solid fa-plane"></i> Flights</button>
            <button class="tab" data-filter="hotel" type="button"><i class="fa-solid fa-hotel"></i> Hotels</button>
            <button class="tab" data-filter="package" type="button"><i class="fa-solid fa-suitcase-rolling"></i> Packages</button>
          </div>
        </div>
      </div>
    </div>

    <div class="list" id="bookingList">
      <?php if (empty($bookings)): ?>
        <div class="ticket">
          <div class="ticket-inner">
            <div class="ticket-left">
              <div class="badge bdg-package"><i class="fa-solid fa-compass"></i> No bookings</div>
              <h2 class="t-title" style="margin-top:12px;">No reservations yet</h2>
              <p class="t-sub">Start booking flights, hotels, or packages — your tickets will appear here.</p>
              <div class="divider"></div>
              <div class="meta">
                <div class="kv"><small>Tip</small><div>Go to Packages / Flights / Hotels</div></div>
                <div class="kv"><small>Then</small><div>Click “Book now”</div></div>
                <div class="kv"><small>After</small><div>Come back here ✨</div></div>
                <div class="kv"><small>Enjoy</small><div>Travelo 💜</div></div>
              </div>
            </div>
            <div class="pricebox">
              <h3>Quick actions</h3>
              <div class="rowp"><span>Packages</span><span><a href="packages.php" style="color:var(--accent);font-weight:900">Browse</a></span></div>
              <div class="rowp"><span>Flights</span><span><a href="fligths.php" style="color:var(--accent);font-weight:900">Browse</a></span></div>
              <div class="rowp"><span>Hotels</span><span><a href="hotel.php" style="color:var(--accent);font-weight:900">Browse</a></span></div>
              <p class="pb-note">Once you book, you’ll see your ticket-style booking card here.</p>
            </div>
          </div>
        </div>
      <?php else: ?>

        <?php foreach ($bookings as $b): ?>
          <?php
            $id     = (int)($b['id'] ?? 0);
            $type   = strtolower($b['booking_type'] ?? 'package');
            $cur    = $b['currency'] ?? 'USD';
            $status = strtolower($b['booking_status'] ?? 'pending');

            $travTxt = travellersText(
              (int)($b['travellers_adults'] ?? 1),
              (int)($b['travellers_children'] ?? 0),
              (int)($b['travellers_infants'] ?? 0)
            );

            // amounts (✅ dynamic breakdown)
            $af = (float)($b['amount_flight'] ?? 0);
            $ah = (float)($b['amount_hotel'] ?? 0);
            $ap = (float)($b['amount_package'] ?? 0);
            $tax = (float)($b['amount_taxes'] ?? 0);
            $disc = (float)($b['discount_amount'] ?? 0);
            $total = (float)($b['total_amount'] ?? 0);

            // dates from bookings first, else fallback by joins
            $start = $b['trip_start_date'] ?? null;
            $end   = $b['trip_end_date'] ?? null;

            // build title/sub/meta per type (ticket style) - ✅ now 4 metas
            $title = 'Booking';
            $sub   = '';

            $m1L = '—'; $m1V = '—';
            $m2L = '—'; $m2V = '—';
            $m3L = '—'; $m3V = '—';
            $m4L = 'Dates'; $m4V = '—';

            if ($type === 'flight') {
              if (!$start && !empty($b['f_departure_date'])) $start = $b['f_departure_date'];
              if (!$end   && !empty($b['f_return_date']))   $end   = $b['f_return_date'];

              $title = trim(($b['f_origin_city'] ?? 'Origin') . " → " . ($b['f_dest_city'] ?? 'Destination'));
              $sub   = trim(($b['f_airline_name'] ?? 'Airline') . (empty($b['f_flight_number']) ? '' : ' · ' . $b['f_flight_number']));

              $m1L = 'Departure'; $m1V = trim(($b['f_departure_time'] ?? '—'));
              $m2L = 'Arrival';   $m2V = trim(($b['f_arrival_time'] ?? '—'));
              $m3L = 'Route';
              $m3V = trim(
                ($b['f_origin_city'] ?? '') . " (" . ($b['f_origin_code'] ?? '') . ") → " .
                ($b['f_dest_city'] ?? '')   . " (" . ($b['f_dest_code'] ?? '') . ")"
              );

            } elseif ($type === 'hotel') {
              $title = $b['h_name'] ? $b['h_name'] : ("Hotel #" . (int)($b['hotel_id'] ?? 0));
              $sub   = trim(($b['h_location'] ?? 'Location') . ' · ' . $travTxt);

              $nights = nightsBetween($start, $end);

              $m1L = 'Check-in';  $m1V = $start ? fmtDate($start) : '—';
              $m2L = 'Check-out'; $m2V = $end ? fmtDate($end) : '—';
              $m3L = 'Nights';    $m3V = $nights ? ($nights . ' night(s)') : '—';
              $m4L = 'Guests';    $m4V = $travTxt;

            } else {
              // package: show included flight + included hotel (✅)
              if (!$start && !empty($b['pf_departure_date'])) $start = $b['pf_departure_date'];
              if (!$end   && !empty($b['pf_return_date']))   $end   = $b['pf_return_date'];

              $title = $b['p_title'] ? $b['p_title'] : ("Package #" . (int)($b['package_id'] ?? 0));

              $incFlight = trim(
                ($b['pf_airline_name'] ?? '') .
                (!empty($b['pf_flight_number']) ? (' · ' . $b['pf_flight_number']) : '')
              );
              $incHotel = trim(($b['ph_name'] ?? ''));

              $subParts = [];
              if (!empty($b['p_location'])) $subParts[] = $b['p_location'];
              if (!empty($b['p_badge']))    $subParts[] = $b['p_badge'];
              $subParts[] = $travTxt;
              $sub = implode(' · ', array_filter($subParts));

              $m1L = 'From';     $m1V = $b['p_from_city'] ?? '—';
              $m2L = 'Duration'; $m2V = !empty($b['p_days']) ? ((int)$b['p_days'] . ' days') : '—';

              $m3L = 'Flight';
              $m3V = $incFlight ?: (trim(($b['pf_departure_time'] ?? '') . ' → ' . ($b['pf_arrival_time'] ?? '')) ?: '—');

              $m4L = 'Hotel';
              $m4V = $incHotel ?: '—';
            }

            $dateLine = ($start || $end)
              ? trim((fmtDate($start) ?: '') . (($start || $end) ? ' — ' : '') . (fmtDate($end) ?: ''))
              : 'Flexible dates';

            // always keep dates in m4 if not used (flight/hotel/package)
            if ($type === 'flight') { $m4L = 'Dates'; $m4V = $dateLine; }
            if ($type === 'package') { $m4L = 'Hotel'; $m4V = $m4V ?: '—'; }
            if ($type === 'hotel') { /* m4 already guests */ }

          ?>

          <div class="ticket booking-item"
              data-type="<?= h($type) ?>"
              data-code="<?= h($b['booking_code'] ?? '') ?>"
              data-status="<?= h($status) ?>">

            <div class="ticket-inner">
              <!-- LEFT -->
              <div class="ticket-left">
                <div class="t-top">
                  <div class="badge <?= h(typeBadgeClass($type)) ?>">
                    <i class="fa-solid <?= $type === 'flight' ? 'fa-plane' : ($type === 'hotel' ? 'fa-hotel' : 'fa-suitcase-rolling') ?>"></i>
                    <?= h(typeLabel($type)) ?>
                  </div>

                  <div class="ref">
                    <small>BOOKING REFERENCE</small>
                    <span class="code"><?= h($b['booking_code'] ?? 'TRV-XXXX') ?></span>
                  </div>
                </div>

                <h2 class="t-title"><?= h($title) ?></h2>
                <p class="t-sub"><?= h($sub) ?></p>

                <div class="divider"></div>

                <div class="meta">
                  <div class="kv">
                    <small><?= h($m1L) ?></small>
                    <div><?= h($m1V) ?></div>
                  </div>
                  <div class="kv">
                    <small><?= h($m2L) ?></small>
                    <div><?= h($m2V) ?></div>
                  </div>
                  <div class="kv">
                    <small><?= h($m3L) ?></small>
                    <div><?= h($m3V) ?></div>
                  </div>
                  <div class="kv">
                    <small><?= h($type === 'hotel' ? 'Trip dates' : $m4L) ?></small>
                    <div><?= h($type === 'hotel' ? $dateLine : $m4V) ?></div>
                  </div>
                </div>

                <div class="note">
                  Please review trip details carefully. After confirmation, changes may require contacting Travelo support.
                </div>

                <div class="total-mini">
                  <div><small>Total amount</small></div>
                  <b><?= money($total, $cur) ?></b>
                </div>
              </div>

              <!-- RIGHT -->
              <div class="pricebox">
                <h3>Price breakdown</h3>

                <?php if ($af > 0): ?>
                  <div class="rowp"><span>Flights</span><span><?= money($af, $cur) ?></span></div>
                <?php endif; ?>

                <?php if ($ah > 0): ?>
                  <div class="rowp"><span>Hotels</span><span><?= money($ah, $cur) ?></span></div>
                <?php endif; ?>

                <?php if ($ap > 0): ?>
                  <div class="rowp"><span>Packages</span><span><?= money($ap, $cur) ?></span></div>
                <?php endif; ?>

                <div class="rowp">
                  <span>Taxes & fees</span>
                  <span><?= money($tax, $cur) ?></span>
                </div>

                <?php if ($disc > 0): ?>
                  <div class="rowp">
                    <span>Discount</span>
                    <span>-<?= money($disc, $cur) ?></span>
                  </div>
                <?php endif; ?>

                <div class="rowp total">
                  <span>Total</span>
                  <span><?= money($total, $cur) ?></span>
                </div>

                <p class="pb-note">
                  Final total will be charged in <?= h($cur) ?> using the selected payment method.
                </p>
              </div>
            </div>

            <!-- actions -->
            <div class="actions">
              <div class="chips">
                <span class="chip <?= h(statusClass($status)) ?>">
                  <i class="fa-solid <?= $status==='confirmed'?'fa-circle-check':($status==='cancelled'?'fa-circle-xmark':'fa-hourglass-half') ?>"></i>
                  <?= h(statusLabel($status)) ?>
                </span>

                <span class="chip">
                  <i class="fa-regular fa-calendar"></i>
                  <?= h(date('d M Y', strtotime($b['created_at'] ?? 'now'))) ?>
                </span>

                <?php if (!empty($b['coupon_code'])): ?>
                  <span class="chip">
                    <i class="fa-solid fa-tag"></i>
                    <?= h($b['coupon_code']) ?>
                  </span>
                <?php endif; ?>
              </div>

              <div class="btns">
                <button class="btn btn-ghost js-toggle-details" type="button" data-target="details-<?= $id ?>">
                  <i class="fa-regular fa-eye"></i> View details
                </button>

                <?php if ($status === 'pending'): ?>
                  <a class="btn btn-primary" href="booking.php?booking_id=<?= (int)$id ?>">
                    <i class="fa-solid fa-credit-card"></i> Complete booking
                  </a>

                  <form method="post" style="margin:0" onsubmit="return confirm('Cancel this booking?');">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="booking_id" value="<?= (int)$id ?>">
                    <button class="btn btn-danger" type="submit">
                      <i class="fa-solid fa-ban"></i> Cancel
                    </button>
                  </form>

                <?php elseif ($status === 'cancelled'): ?>

                  <form method="post" style="margin:0" onsubmit="return confirm('Restore this booking to pending?');">
                    <input type="hidden" name="action" value="restore">
                    <input type="hidden" name="booking_id" value="<?= (int)$id ?>">
                    <button class="btn btn-primary" type="submit">
                      <i class="fa-solid fa-rotate-left"></i> Restore
                    </button>
                  </form>

                <?php else: ?>

                  <form method="post" style="margin:0"
                        onsubmit="return confirm('Cancel this confirmed booking? (Refund will apply if payment is successful)');">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="booking_id" value="<?= (int)$id ?>">
                    <button class="btn btn-danger" type="submit">
                      <i class="fa-solid fa-ban"></i> Cancel
                    </button>
                  </form>

                <?php endif; ?>

                <button class="btn btn-primary js-copy" type="button" data-code="<?= h($b['booking_code'] ?? '') ?>">
                  <i class="fa-regular fa-copy"></i> Copy code
                </button>
              </div>
            </div>

            <!-- details -->
            <div class="details" id="details-<?= $id ?>">
              <div class="details-card">
                <div class="grid2">
                  <div><span class="muted">Booking ID:</span> <b>#<?= (int)$id ?></b></div>
                  <div><span class="muted">Type:</span> <b><?= h(strtoupper($type)) ?></b></div>

                  <div><span class="muted">Traveller(s):</span> <b><?= h($travTxt) ?></b></div>
                  <div><span class="muted">Currency:</span> <b><?= h($cur) ?></b></div>

                  <div><span class="muted">Trip start:</span> <b><?= $start ? h($start) : '—' ?></b></div>
                  <div><span class="muted">Trip end:</span> <b><?= $end ? h($end) : '—' ?></b></div>

                  <div><span class="muted">Flight ID:</span> <b><?= !empty($b['flight_id']) ? (int)$b['flight_id'] : '—' ?></b></div>
                  <div><span class="muted">Hotel ID:</span> <b><?= !empty($b['hotel_id']) ? (int)$b['hotel_id'] : '—' ?></b></div>

                  <div><span class="muted">Package ID:</span> <b><?= !empty($b['package_id']) ? (int)$b['package_id'] : '—' ?></b></div>
                  <div><span class="muted">Status:</span> <b><?= h($status) ?></b></div>
                </div>

                <?php if (!empty($b['notes'])): ?>
                  <div class="hr"></div>
                  <div><span class="muted">Notes:</span><br><?= nl2br(h($b['notes'])) ?></div>
                <?php endif; ?>

                <?php if ($type === 'flight'): ?>
                  <div class="hr"></div>
                  <div class="grid2">
                    <div><span class="muted">Airline:</span> <b><?= h($b['f_airline_name'] ?? '—') ?></b></div>
                    <div><span class="muted">Flight No:</span> <b><?= h($b['f_flight_number'] ?? '—') ?></b></div>
                    <div><span class="muted">Departure:</span> <b><?= h($b['f_departure_time'] ?? '—') ?></b></div>
                    <div><span class="muted">Arrival:</span> <b><?= h($b['f_arrival_time'] ?? '—') ?></b></div>
                  </div>

                <?php elseif ($type === 'hotel'): ?>
                  <div class="hr"></div>
                  <div class="grid2">
                    <div><span class="muted">Hotel:</span> <b><?= h($b['h_name'] ?? '—') ?></b></div>
                    <div><span class="muted">Location:</span> <b><?= h($b['h_location'] ?? '—') ?></b></div>
                    <div><span class="muted">Rating:</span> <b><?= isset($b['h_rating']) ? h($b['h_rating']) : '—' ?></b></div>
                    <div><span class="muted">Reviews:</span> <b><?= h((int)($b['h_reviews'] ?? 0)) ?></b></div>
                  </div>

                <?php else: ?>
                  <div class="hr"></div>
                  <div class="grid2">
                    <div><span class="muted">Package:</span> <b><?= h($b['p_title'] ?? '—') ?></b></div>
                    <div><span class="muted">Location:</span> <b><?= h($b['p_location'] ?? '—') ?></b></div>
                    <div><span class="muted">From:</span> <b><?= h($b['p_from_city'] ?? '—') ?></b></div>
                    <div><span class="muted">Days:</span> <b><?= !empty($b['p_days']) ? h((int)$b['p_days']) : '—' ?></b></div>

                    <div><span class="muted">Included flight:</span> <b><?= h(trim(($b['pf_airline_name'] ?? '—') . (!empty($b['pf_flight_number']) ? (' · '.$b['pf_flight_number']) : ''))) ?></b></div>
                    <div><span class="muted">Flight time:</span> <b><?= h(trim(($b['pf_departure_time'] ?? '—') . ' → ' . ($b['pf_arrival_time'] ?? '—'))) ?></b></div>

                    <div><span class="muted">Included hotel:</span> <b><?= h($b['ph_name'] ?? '—') ?></b></div>
                    <div><span class="muted">Hotel location:</span> <b><?= h($b['ph_location'] ?? '—') ?></b></div>
                  </div>
                <?php endif; ?>
              </div>
            </div>

          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script>
    // ===== Toast =====
    const toast = (msg) => {
      const el = document.getElementById("toast");
      if (!el) return;
      el.textContent = msg;
      el.classList.add("show");
      clearTimeout(window.__t);
      window.__t = setTimeout(() => el.classList.remove("show"), 2200);
    };

    // ===== Tabs filter =====
    const tabs = document.querySelectorAll(".tab");
    const items = document.querySelectorAll(".booking-item");

    tabs.forEach(t => {
      t.addEventListener("click", () => {
        tabs.forEach(x => x.classList.remove("active"));
        t.classList.add("active");
        applyFilters();
      });
    });

    // ===== Search =====
    const searchInput = document.getElementById("searchInput");
    if (searchInput) searchInput.addEventListener("input", applyFilters);

    function applyFilters() {
      const activeTab = document.querySelector(".tab.active");
      const typeFilter = activeTab ? activeTab.dataset.filter : "all";
      const q = (searchInput?.value || "").trim().toLowerCase();

      items.forEach(it => {
        const t = (it.dataset.type || "").toLowerCase();
        const code = (it.dataset.code || "").toLowerCase();
        const st = (it.dataset.status || "").toLowerCase();

        const okType = (typeFilter === "all") ? true : (t === typeFilter);
        const okQ = !q ? true : (code.includes(q) || st.includes(q) || t.includes(q));

        it.style.display = (okType && okQ) ? "" : "none";
      });
    }

    // ===== View details collapse =====
    document.addEventListener("click", (e) => {
      const btn = e.target.closest(".js-toggle-details");
      if (!btn) return;
      const id = btn.dataset.target;
      const panel = document.getElementById(id);
      if (!panel) return;
      panel.classList.toggle("show");
      btn.innerHTML = panel.classList.contains("show")
        ? '<i class="fa-regular fa-eye-slash"></i> Hide details'
        : '<i class="fa-regular fa-eye"></i> View details';
    });

    // ===== Copy code =====
    document.addEventListener("click", async (e) => {
      const btn = e.target.closest(".js-copy");
      if (!btn) return;
      const code = btn.dataset.code || "";
      try {
        await navigator.clipboard.writeText(code);
        toast("Copied: " + code);
      } catch {
        toast("Copy failed");
      }
    });

    applyFilters();

    <?php if ($flash): ?>
      toast(<?= json_encode($flash['msg']) ?>);
    <?php endif; ?>
  </script>

  <script src="./assets/js/home.js"></script>
</body>
</html>
