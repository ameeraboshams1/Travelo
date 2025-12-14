<?php
session_start();
require __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Travelo · Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.10/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="./assets/css/dashboard.css" />
   <script src='https://cdn.jotfor.ms/agent/embedjs/019b189a507c7f0e98a0580ad136880f79ad/embed.js'>
</script>
  <style>
    :root {
      --tbl-bg: #ffffff;
      --tbl-head-bg: #f6f7fb;
      --tbl-head-text: #12131a;
      --tbl-row: #ffffff;
      --tbl-row-alt: #fafbff;
      --tbl-row-hover: #eef0f7;
      --tbl-border: #e5e7f2;
    }
    html.dark {
      --tbl-bg: #0f1222;
      --tbl-head-bg: #141832;
      --tbl-head-text: #e9ecff;
      --tbl-row: #0f1222;
      --tbl-row-alt: #0b0e1a;
      --tbl-row-hover: #1a1e3a;
      --tbl-border: #242949;
    }
    .table { background: var(--tbl-bg); color: inherit; }
    .table thead th {
      background: var(--tbl-head-bg) !important;
      color: var(--tbl-head-text) !important;
      border-bottom: 1px solid var(--tbl-border) !important;
    }
    .table tbody tr { background: var(--tbl-row); }
    .table tbody tr:nth-child(even) { background: var(--tbl-row-alt); }
    .table tbody tr:hover { background: var(--tbl-row-hover) !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      border: 1px solid var(--tbl-border) !important;
      background: transparent !important;
      color: inherit !important;
      border-radius: 8px;
      margin: 0 .125rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: var(--tbl-head-bg) !important;
      color: var(--tbl-head-text) !important;
    }
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
      background: var(--tbl-row);
      color: inherit;
      border: 1px solid var(--tbl-border);
      border-radius: 8px;
    }
    .thumb { width: 56px; height: 56px; object-fit: cover; border-radius: 10px; }
    .avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
    .status { padding: .2rem .5rem; border-radius: 999px; font-size: .75rem; }
    .status.success { background: #16a34a24; color: #16a34a; }
    .status.pending { background: #f59e0b24; color: #f59e0b; }
    .status.canceled { background: #ef444424; color: #ef4444; }
    .topbar { background: var(--tbl-head-bg); }
    #flightsMap { height: 360px; border-radius: 12px; overflow: hidden; }
    .page-title { font-weight: 700; font-size: 1.1rem; }
    .btn-action { padding: .35rem .5rem; }

    /* ==============================
       Prettier Admin Letter Badge
       ============================== */
    .admin-badge{
      width: 46px;
      height: 46px;
      border: 0;
      padding: 0;
      border-radius: 999px;
      position: relative;
      display: grid;
      place-items: center;
      cursor: pointer;

      background:
        radial-gradient(circle at 30% 25%, rgba(255,255,255,.55), transparent 48%),
        linear-gradient(135deg, #7c3aed 0%, #6c63ff 45%, #22c55e 130%);

      box-shadow:
        0 16px 34px rgba(124,58,237,.20),
        0 0 0 1px rgba(124,58,237,.18) inset;

      transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    }

    .admin-badge:hover{
      transform: translateY(-1px);
      filter: brightness(1.03);
      box-shadow:
        0 22px 48px rgba(124,58,237,.24),
        0 0 0 1px rgba(124,58,237,.22) inset;
    }

    .admin-badge:focus{
      outline: none;
      box-shadow:
        0 0 0 4px rgba(124,58,237,.18),
        0 22px 48px rgba(124,58,237,.24);
    }

    .admin-badge.dropdown-toggle::after{ display:none; }

    .admin-badge__ring{
      position: absolute;
      inset: -2px;
      border-radius: 999px;
      background:
        conic-gradient(from 180deg, rgba(124,58,237,.55), rgba(108,99,255,.55), rgba(124,58,237,.55));
      filter: blur(.2px);
      opacity: .65;
      z-index: 0;
    }

    .admin-badge__ring::after{
      content:"";
      position:absolute;
      inset: 2px;
      border-radius: 999px;
      background: rgba(255,255,255,.12);
      box-shadow: 0 0 0 1px rgba(255,255,255,.12) inset;
    }

    .admin-badge__letter{
      position: relative;
      z-index: 1;
      width: 40px;
      height: 40px;
      border-radius: 999px;
      display: grid;
      place-items: center;

      color: #fff;
      font-weight: 900;
      font-size: 15px;
      letter-spacing: -0.03em;

      background:
        radial-gradient(circle at 35% 30%, rgba(255,255,255,.24), transparent 55%),
        rgba(15,23,42,.18);

      box-shadow:
        0 10px 20px rgba(15,23,42,.18),
        0 0 0 1px rgba(255,255,255,.16) inset;
    }

    /* dropdown menu look */
    .admin-menu{
      border-radius: 16px;
      border: 1px solid var(--tbl-border);
      box-shadow: 0 18px 45px rgba(15,23,42,.18);
      padding: 8px;
      min-width: 210px;
    }
    .admin-menu .dropdown-item{
      border-radius: 12px;
      padding: 10px 12px;
    }
    .admin-menu .dropdown-item:hover{
      background: rgba(124,58,237,.10);
      color: #7c3aed;
    }

    /* ===== Soft purple hover for topbar buttons ===== */
    .topbar .btn-outline-secondary{
      border-radius: 14px;
      transition: background .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }
    .topbar .btn-outline-secondary:hover{
      background: rgba(124,58,237,.10) !important;
      border-color: rgba(124,58,237,.35) !important;
      color: #7c3aed !important;
      transform: translateY(-1px);
    }
    .topbar .btn-outline-secondary:focus{
      box-shadow: 0 0 0 4px rgba(124,58,237,.16) !important;
    }

    .topbar .form-control:focus{
      border-color: rgba(124,58,237,.35) !important;
      box-shadow: 0 0 0 4px rgba(124,58,237,.14) !important;
    }
    .topbar .input-group-text{
      border-radius: 14px 0 0 14px;
    }
    .topbar .form-control{
      border-radius: 0 14px 14px 0;
    }
  </style>
</head>
<body>
<div class="app">
  <!-- SIDEBAR -->
  <aside id="sidebar" class="sidebar">
    <div class="brand">
      <img class="logo" src="./assets/images/logo.svg" alt="Travelo logo" />
      Travelo Admin
    </div>
    <div class="mb-3">
      <a class="btn btn-outline-secondary d-lg-none w-100" href="#" onclick="toggleSidebar(event)">
        <i class="bi bi-layout-sidebar-inset"></i> Menu
      </a>
    </div>
    <nav class="nav flex-column gap-1" id="mainNav">
      <a class="nav-link active" href="#dashboard" data-page="dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      <a class="nav-link" href="#users" data-page="users"><i class="bi bi-people me-2"></i>Users</a>
      <a class="nav-link" href="#admins" data-page="admins"><i class="bi bi-person-gear me-2"></i>Admins</a>
      <a class="nav-link" href="#destinations" data-page="destinations"><i class="bi bi-geo-alt me-2"></i>Destinations</a>
      <a class="nav-link" href="#flights" data-page="flights"><i class="bi bi-airplane me-2"></i>Flights</a>
      <a class="nav-link" href="#hotels" data-page="hotels"><i class="bi bi-building me-2"></i>Hotels</a>
      <a class="nav-link" href="#packages" data-page="packages"><i class="bi bi-box-seam me-2"></i>Packages</a>
      <a class="nav-link" href="#bookings" data-page="bookings"><i class="bi bi-receipt me-2"></i>Bookings</a>
      <a class="nav-link" href="#payments" data-page="payments"><i class="bi bi-credit-card me-2"></i>Payments</a>
    </nav>
    <hr />
    <div class="sub">© 2025 Travelo</div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <!-- TOPBAR -->
    <div class="topbar container-fluid rounded-4 p-3 mb-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary d-lg-none" onclick="toggleSidebar(event)">
          <i class="bi bi-list"></i>
        </button>
        <div class="page-title" id="pageTitle">Dashboard</div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="input-group" style="max-width:360px;">
          <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
          <input id="globalSearch" class="form-control border-start-0" placeholder="Search (Ctrl + K)" />
        </div>

        <button id="themeToggle" class="btn btn-outline-secondary" title="Toggle theme">
          <i class="bi bi-moon-stars" id="themeIcon"></i>
        </button>

        <?php
          $adminName = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? $_SESSION['username'] ?? 'Admin';
          $adminName = trim((string)$adminName);
          $adminLetter = strtoupper(mb_substr($adminName !== '' ? $adminName : 'A', 0, 1));
        ?>

        <!-- ✅ THIS is the only replacement: avatar image -> admin letter badge -->
        <div class="dropdown">
          <button
            class="admin-badge dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            title="<?= htmlspecialchars($adminName ?: 'Admin') ?>"
          >
            <span class="admin-badge__ring"></span>
            <span class="admin-badge__letter"><?= htmlspecialchars($adminLetter) ?></span>
          </button>

<ul class="dropdown-menu dropdown-menu-end admin-menu">
  <li>
    <a class="dropdown-item" href="./adminprofile.php">
      <i class="bi bi-person me-2"></i> My profile
    </a>
  </li>

  <li><hr class="dropdown-divider"></li>

  <li>
    <form action="./logout.php" method="post" class="m-0 p-0">
      <button type="submit" class="dropdown-item">
        <i class="bi bi-box-arrow-right me-2"></i> Log out
      </button>
    </form>
  </li>
</ul>

        </div>
      </div>
    </div>

    <!-- DASHBOARD -->
    <section id="dashboard" class="section active">
      <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card p-3 h-100">
            <div class="card-title">Total Users</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="metric" id="kpiUsers">--</div>
              <i class="bi bi-people fs-2" style="color:var(--p1)"></i>
            </div>
            <div class="sub">Active users</div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card p-3 h-100">
            <div class="card-title">Total Bookings</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="metric" id="kpiBookings">--</div>
              <i class="bi bi-ticket-perforated fs-2" style="color:var(--pink1)"></i>
            </div>
            <div class="sub">All types</div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card p-3 h-100">
            <div class="card-title">Total Revenue</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="metric" id="kpiRevenue">--</div>
              <i class="bi bi-currency-dollar fs-2" style="color:var(--p3)"></i>
            </div>
            <div class="sub">Payments captured</div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card p-3 h-100">
            <div class="card-title">On-Time Flights</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="metric" id="kpiOTP">--</div>
              <i class="bi bi-clock-history fs-2" style="color:var(--p2)"></i>
            </div>
            <div class="sub">From flights data</div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-12 col-lg-8">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Revenue vs Bookings (Sample)</div>
            <div class="chart-wrap"><canvas id="dashLine"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Booking Status (Sample)</div>
            <div class="chart-wrap"><canvas id="dashDonut"></canvas></div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Revenue (Last 30 Days – Sample)</div>
            <div class="chart-wrap"><canvas id="dashArea"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Daily Bookings (Sample)</div>
            <div class="chart-wrap"><canvas id="dashBarMini"></canvas></div>
          </div>
        </div>
      </div>

      <div class="card p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Unified Activity (Bookings & Payments)</div>
        </div>
        <div class="table-responsive">
          <table id="dashTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Type</th>
              <th>Ref</th>
              <th>User</th>
              <th>From</th>
              <th>To</th>
              <th>Date</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="card p-3 mt-3">
        <div class="fw-bold mb-2">Live Routes Map</div>
        <div id="flightsMap"></div>
      </div>
    </section>

    <!-- USERS -->
    <section id="users" class="section">
      <div class="card p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Users</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addUser" data-entity="users" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="usersTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Name</th>
              <th>Username</th>
              <th>Email</th>
              <th>BIRTH DATE</th>
              <th>Active</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ADMINS -->
    <section id="admins" class="section">
      <div class="card p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Admins</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addAdmin" data-entity="admins" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="adminsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Admin</th>
              <th>Email</th>
              <th>Is Super</th>
              <th>Active</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- DESTINATIONS -->
    <section id="destinations" class="section">
      <div class="card p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Destinations</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addDestination" data-entity="destinations" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="destinationsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Image</th>
              <th>Name</th>
              <th>City</th>
              <th>Country</th>
              <th>Category</th>
              <th>Base Price</th>
              <th>Currency</th>
              <th>Top</th>
              <th>Active</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- FLIGHTS -->
    <section id="flights" class="section">
      <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Flights</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addFlight" data-entity="flights" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="flightsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Airline</th>
              <th>Flight #</th>
              <th>Destination</th>
              <th>Route</th>
              <th>Trip Type</th>
              <th>Depart Date</th>
              <th>Return Date</th>
              <th>Depart Time</th>
              <th>Arrive Time</th>
              <th>Duration (h)</th>
              <th>Stops</th>
              <th>Price</th>
              <th>Currency</th>
              <th>Active</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">On-Time / Delay / Cancel (Sample)</div>
            <div class="chart-wrap"><canvas id="flightsDonut"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Airline OTP (%) (Sample)</div>
            <div class="chart-wrap"><canvas id="flightsBar"></canvas></div>
          </div>
        </div>
      </div>
    </section>

    <!-- HOTELS -->
    <section id="hotels" class="section">
      <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Hotels</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addHotel" data-entity="hotels" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="hotelsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Image</th>
              <th>Name</th>
              <th>Destination</th>
              <th>Location</th>
              <th>Rating</th>
              <th>Reviews</th>
              <th>Price/Night</th>
              <th>Currency</th>
              <th>Discount %</th>
              <th>Active</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Occupancy Rate (Sample)</div>
            <div class="chart-wrap"><canvas id="hotelsLine"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Star Rating Mix (Sample)</div>
            <div class="chart-wrap"><canvas id="hotelsPie"></canvas></div>
          </div>
        </div>
      </div>
    </section>

    <!-- PACKAGES -->
    <section id="packages" class="section">
      <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Packages</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addPackage" data-entity="packages" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="packagesTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Image</th>
              <th>Title</th>
              <th>Destination</th>
              <th>Hotel</th>
              <th>Flight</th>
              <th>From City</th>
              <th>Location</th>
              <th>Duration (Days)</th>
              <th>Price (USD)</th>
              <th>Rating</th>
              <th>Category</th>
              <th>Featured</th>
              <th>Active</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- BOOKINGS -->
    <section id="bookings" class="section">
      <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Bookings</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addBooking" data-entity="bookings" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="bookingsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Booking Code</th>
              <th>User</th>
              <th>Type</th>
              <th>Package</th>
              <th>Start</th>
              <th>End</th>
              <th>Total</th>
              <th>Currency</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Bookings by Type (Sample)</div>
            <div class="chart-wrap"><canvas id="bookingsTypeChart"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Daily Bookings (Sample)</div>
            <div class="chart-wrap"><canvas id="bookingsDailyChart"></canvas></div>
          </div>
        </div>
      </div>
    </section>

    <!-- PAYMENTS -->
    <section id="payments" class="section">
      <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Payments</div>
        </div>
        <div class="table-responsive">
          <table id="paymentsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>ID</th>
              <th>Booking</th>
              <th>User</th>
              <th>Method</th>
              <th>Amount</th>
              <th>Currency</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Methods Split </div>
            <div class="chart-wrap"><canvas id="payDoughnut"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Daily Payments </div>
            <div class="chart-wrap"><canvas id="payLine"></canvas></div>
          </div>
        </div>
      </div>
    </section>
  </main>
</div>

<!-- FORM MODAL -->
<div class="modal fade" id="formModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formModalTitle">Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="entityForm">
        <div class="modal-body" id="formModalBody"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="formSubmitBtn">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CONFIRM MODAL -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="confirmModalBody">Are you sure?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmYes">Yes</button>
      </div>
    </div>
  </div>
</div>

<!-- DETAILS MODAL -->
<div class="modal fade" id="detailsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsTitle">Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailsBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.10/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.10/js/dataTables.bootstrap5.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-ant-path@1.3.0/dist/leaflet-ant-path.min.js"></script>

<script src="./assets/js/dashboard.js"></script>
</body>
</html>
