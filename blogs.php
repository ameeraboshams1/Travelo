<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
$userId = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;

$userName = '';
if ($isLoggedIn) {
  $userName = $_SESSION['username']
    ?? $_SESSION['user_name']
    ?? (($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
  $userName = trim((string)$userName);
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Travelo · Blogs</title>

  <!-- Bootstrap (Modal) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root{
      --ink:#0f172a;
      --muted:#64748b;
      --muted2:#94a3b8;

      --bg:#f7f8fb;
      --card:#ffffff;
      --border:#e8eaf3;

      --accent:#7c3aed;
      --accent2:#6c63ff;
      --accentSoft: rgba(124,58,237,.12);

      --shadow: 0 14px 40px rgba(15,23,42,.10);
      --shadowHover: 0 22px 60px rgba(15,23,42,.16);
      --r:18px;
      --r2:12px;
      --t: all .28s ease;

      --font: "Plus Jakarta Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    }

    *{ margin:0; padding:0; box-sizing:border-box; font-family:var(--font); }
    body{
      background:
        radial-gradient(1200px 700px at 18% -10%, rgba(124,58,237,.18), transparent 60%),
        radial-gradient(900px 520px at 92% 8%, rgba(108,99,255,.16), transparent 55%),
        var(--bg);
      color:var(--ink);
      line-height:1.6;
      min-height:100vh;
      overflow-x: hidden;
    }
    a{ text-decoration:none; color:inherit; }

    .container{
      width:100%;
      max-width: 1200px;
      margin:0 auto;
      padding: 0 16px;
    }

    /* Hero */
    .hero{
      margin-top: 26px;
      padding: 22px;
      border-radius: var(--r);
      background:
        radial-gradient(900px 420px at 15% 10%, rgba(124,58,237,.22), transparent 58%),
        radial-gradient(760px 420px at 95% 30%, rgba(108,99,255,.20), transparent 55%),
        rgba(255,255,255,.72);
      border: 1px solid rgba(232,234,243,.9);
      box-shadow: var(--shadow);
      overflow:hidden;
      position:relative;
    }
    .hero:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(600px 250px at 10% 0%, rgba(124,58,237,.18), transparent 60%),
        radial-gradient(520px 260px at 90% 20%, rgba(108,99,255,.16), transparent 58%);
      pointer-events:none;
      opacity:.85;
    }
    .hero-inner{ position:relative; display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-end; }
    .hero-title{
      display:flex; align-items:center; gap:10px;
      margin-bottom: 8px;
      font-weight: 800;
      letter-spacing:.2px;
      font-size: 1.8rem;
    }
    .hero-title .dot{
      width:12px; height:12px; border-radius:999px;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      box-shadow: 0 10px 26px rgba(124,58,237,.32);
    }
    .hero p{ color: var(--muted); max-width: 62ch; }

    .hero-actions{
      display:flex;
      gap:12px;
      align-items:center;
      flex-wrap:wrap;
      justify-content:flex-end;
      min-width: 280px;
    }

    /* Filters */
    .filters-section{
      background: rgba(255,255,255,.78);
      border: 1px solid rgba(232,234,243,.9);
      box-shadow: var(--shadow);
      padding: 18px;
      margin: 18px 0 26px;
      border-radius: var(--r);
    }
    .filters-title{
      display:flex;
      align-items:center;
      gap:10px;
      margin-bottom: 14px;
      color: var(--ink);
      font-weight: 800;
    }
    .filters-title i{
      color: var(--accent);
      background: var(--accentSoft);
      border: 1px solid rgba(124,58,237,.18);
      width: 34px; height: 34px;
      border-radius: 12px;
      display:flex; align-items:center; justify-content:center;
    }
    .filters-container{
      display:flex;
      flex-wrap:wrap;
      gap: 12px;
      margin-bottom: 14px;
    }
    .filter-group{
      display:flex;
      flex-direction:column;
      min-width: 200px;
      flex: 1;
    }
    .filter-group label{
      margin-bottom: 8px;
      font-weight: 700;
      color: var(--muted);
      font-size: .92rem;
      display:flex;
      gap:8px;
      align-items:center;
    }
    .filter-group label i{ color: var(--accent); opacity:.9; }

    .filter-group select,
    .filter-group input{
      padding: 12px 14px;
      border: 1px solid var(--border);
      border-radius: var(--r2);
      background: rgba(255,255,255,.92);
      font-size: 1rem;
      transition: var(--t);
      outline:none;
    }
    .filter-group select:focus,
    .filter-group input:focus{
      border-color: rgba(124,58,237,.55);
      box-shadow: 0 0 0 4px rgba(124,58,237,.12);
    }

    .search-box{ position:relative; flex: 2; min-width: 280px; }
    .search-box input{ width:100%; padding-right: 44px; }
    .search-box i{
      position:absolute;
      right: 14px;
      top: 44px;
      transform: translateY(-50%);
      color: var(--muted2);
    }

    .filters-actions{
      display:flex;
      justify-content:flex-end;
      gap: 10px;
      flex-wrap:wrap;
    }

    .btnx{
      padding: 11px 16px;
      border-radius: 12px;
      font-weight: 800;
      cursor:pointer;
      border:none;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      transition: var(--t);
      user-select:none;
      white-space: nowrap;
    }
    .btn-primaryx{
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      color: #fff;
      box-shadow: 0 12px 30px rgba(124,58,237,.22);
    }
    .btn-primaryx:hover{ transform: translateY(-2px); box-shadow: var(--shadowHover); }
    .btn-outlinex{
      background: rgba(255,255,255,.75);
      color: var(--accent);
      border: 2px solid rgba(124,58,237,.35);
    }
    .btn-outlinex:hover{
      transform: translateY(-2px);
      background: rgba(124,58,237,.08);
      box-shadow: 0 10px 24px rgba(15,23,42,.10);
    }
    .btn-softx{
      background: rgba(124,58,237,.10);
      border: 1px solid rgba(124,58,237,.18);
      color: rgba(124,58,237,1);
    }
    .btn-softx:hover{
      transform: translateY(-2px);
      background: rgba(124,58,237,.14);
      box-shadow: 0 10px 24px rgba(15,23,42,.10);
    }

    /* Blogs */
    .blogs-section{ margin: 10px 0 40px; }
    .section-header{
      display:flex;
      justify-content:space-between;
      align-items:flex-end;
      gap: 12px;
      margin-bottom: 16px;
      flex-wrap:wrap;
    }
    .section-title{
      display:flex;
      align-items:center;
      gap:10px;
      font-size: 1.25rem;
      font-weight: 900;
    }
    .section-title i{
      color: var(--accent);
      background: var(--accentSoft);
      border: 1px solid rgba(124,58,237,.18);
      width: 34px; height: 34px;
      border-radius: 12px;
      display:flex; align-items:center; justify-content:center;
    }
    .meta-line{ color: var(--muted); font-weight: 700; font-size: .95rem; }

    .sort-options{ display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .sort-options select{
      padding: 10px 14px;
      border: 1px solid var(--border);
      border-radius: 12px;
      background: rgba(255,255,255,.85);
      outline:none;
      font-weight: 800;
      color: var(--ink);
    }

    .blogs-grid{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
      gap: 18px;
    }

    .blog-card{
      background: rgba(255,255,255,.92);
      border: 1px solid rgba(232,234,243,.9);
      border-radius: var(--r);
      overflow:hidden;
      box-shadow: var(--shadow);
      transition: var(--t);
      animation: fadeInUp .45s ease-out both;
      cursor:pointer;
      position: relative;
    }
    .blog-card:hover{
      transform: translateY(-8px);
      box-shadow: var(--shadowHover);
      border-color: rgba(124,58,237,.22);
    }

    .blog-image{
      height: 210px;
      width: 100%;
      object-fit: cover;
      transition: var(--t);
      display:block;
      background: linear-gradient(135deg, rgba(124,58,237,.18), rgba(108,99,255,.12));
    }
    .blog-card:hover .blog-image{ transform: scale(1.04); }

    .blog-content{ padding: 18px; }
    .blog-meta{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap: 10px;
      margin-bottom: 10px;
      color: var(--muted2);
      font-size: .9rem;
      font-weight: 800;
    }
    .blog-category{
      background: linear-gradient(135deg, rgba(124,58,237,.95), rgba(108,99,255,.95));
      color:#fff;
      padding: 6px 12px;
      border-radius: 999px;
      font-size: .78rem;
      font-weight: 900;
      letter-spacing:.2px;
      box-shadow: 0 12px 26px rgba(124,58,237,.18);
      white-space:nowrap;
    }
    .blog-title{
      font-size: 1.18rem;
      margin-bottom: 10px;
      line-height: 1.35;
      font-weight: 900;
      transition: var(--t);
    }
    .blog-card:hover .blog-title{ color: var(--accent); }

    .blog-excerpt{
      color: var(--muted);
      margin-bottom: 14px;
      display:-webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow:hidden;
      min-height: 3.9em;
    }

    .blog-footer{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap: 10px;
      padding-top: 14px;
      border-top: 1px solid rgba(232,234,243,.9);
    }
    .blog-author{
      display:flex;
      align-items:center;
      gap: 10px;
      min-width: 0;
    }

    .author-avatar{
      width: 42px;
      height: 42px;
      border-radius: 50%;
      display:grid;
      place-items:center;
      font-weight: 900;
      color:#fff;
      background: linear-gradient(135deg, rgba(124,58,237,.95), rgba(108,99,255,.95));
      box-shadow: 0 10px 22px rgba(15,23,42,.10);
      border: 2px solid rgba(124,58,237,.25);
      flex: 0 0 auto;
    }
    .author-info h4{
      font-size: .96rem;
      margin-bottom: 2px;
      font-weight: 900;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
      max-width: 180px;
    }
    .author-info p{
      font-size: .82rem;
      color: var(--muted2);
      font-weight: 800;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
      max-width: 180px;
    }

    .read-time{
      display:flex;
      align-items:center;
      gap: 7px;
      color: var(--muted2);
      font-size: .9rem;
      font-weight: 900;
      white-space:nowrap;
    }
    .read-time i{ color: var(--accent); opacity:.9; }

    /* Loading */
    .loading{
      display:flex;
      justify-content:center;
      align-items:center;
      min-height: 220px;
    }
    .spinner{
      width: 52px;
      height: 52px;
      border: 5px solid rgba(124,58,237,0.14);
      border-top-color: var(--accent);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes fadeInUp {
      from { opacity:0; transform: translateY(16px); }
      to   { opacity:1; transform: translateY(0); }
    }

    /* Scroll top */
    .scroll-top{
      position: fixed;
      bottom: 24px;
      right: 24px;
      width: 52px;
      height: 52px;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      color:#fff;
      border-radius: 999px;
      display:flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      box-shadow: var(--shadow);
      transition: var(--t);
      opacity:0;
      visibility:hidden;
      z-index: 99;
    }
    .scroll-top.active{ opacity:1; visibility:visible; }
    .scroll-top:hover{ transform: translateY(-5px); box-shadow: var(--shadowHover); }

    /* ===== Modal Scroll Fix (IMPORTANT) ===== */
    .modal-dialog { max-height: calc(100vh - 40px); }
    .modal-content { border-radius: 18px; overflow: hidden; }
    .modal-body{
      overflow: auto;
      max-height: calc(100vh - 220px);
    }
    .form-control, .form-select{
      border-radius: 14px !important;
      border: 1px solid var(--border) !important;
    }
    .form-control:focus, .form-select:focus{
      border-color: rgba(124,58,237,.55) !important;
      box-shadow: 0 0 0 4px rgba(124,58,237,.12) !important;
    }

    @media (max-width: 992px){
      .filters-container{ flex-direction:column; }
      .filter-group{ min-width:100%; }
      .search-box{ min-width:100%; }
      .blogs-grid{ grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
    }
    @media (max-width: 640px){
      .hero{ padding: 18px; }
      .blogs-grid{ grid-template-columns: 1fr; }
    }
  </style>
</head>

<body>
  <div class="container">
    <section class="hero">
      <div class="hero-inner">
        <div>
          <div class="hero-title"><span class="dot"></span> Travelo Blogs</div>
          <p>Travel stories, destination guides, and smart tips to help you plan better trips.</p>
        </div>

        <div class="hero-actions">
          <button class="btnx btn-outlinex" id="resetFiltersTop"><i class="fa-solid fa-rotate-left"></i> Reset</button>
          <button class="btnx btn-primaryx" id="applyFiltersTop"><i class="fa-solid fa-sliders"></i> Apply filters</button>

          <?php if ($isLoggedIn): ?>
            <button class="btnx btn-softx" id="openAddBlog">
              <i class="fa-solid fa-pen-to-square"></i> Add Blog
            </button>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="filters-section">
      <div class="filters-title">
        <i class="fa-solid fa-filter"></i>
        <h2 style="font-size:1.1rem;">Search & Filters</h2>
      </div>

      <div class="filters-container">
        <div class="filter-group">
          <label for="category"><i class="fa-solid fa-tag"></i> Category</label>
          <select id="category">
            <option value="">All categories</option>
          </select>
        </div>

        <div class="filter-group">
          <label for="author"><i class="fa-solid fa-user"></i> Author</label>
          <select id="author">
            <option value="">All authors</option>
          </select>
        </div>

        <div class="filter-group">
          <label for="date"><i class="fa-regular fa-calendar"></i> Date</label>
          <select id="date">
            <option value="">Any time</option>
            <option value="week">Last 7 days</option>
            <option value="month">Last 30 days</option>
            <option value="3months">Last 3 months</option>
            <option value="year">Last 12 months</option>
          </select>
        </div>

        <div class="filter-group search-box">
          <label for="search"><i class="fa-solid fa-magnifying-glass"></i> Search</label>
          <input type="text" id="search" placeholder="Search blogs..." autocomplete="off">
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>
      </div>

      <div class="filters-actions">
        <button class="btnx btn-outlinex" id="resetFilters">
          <i class="fa-solid fa-rotate-left"></i> Reset
        </button>
        <button class="btnx btn-primaryx" id="applyFilters">
          <i class="fa-solid fa-check"></i> Apply
        </button>
      </div>
    </section>

    <section class="blogs-section" id="blogs">
      <div class="section-header">
        <div>
          <div class="section-title">
            <i class="fa-regular fa-newspaper"></i>
            <span>Latest Posts</span>
          </div>
          <div class="meta-line" id="resultCount">0 posts</div>
        </div>

        <div class="sort-options">
          <select id="sortBy">
            <option value="newest">Newest first</option>
            <option value="oldest">Oldest first</option>
            <option value="popular">Most viewed</option>
            <option value="title">Title (A → Z)</option>
          </select>
        </div>
      </div>

      <div class="blogs-grid" id="blogsGrid"></div>

      <div class="loading" id="loadingIndicator">
        <div class="spinner"></div>
      </div>
    </section>
  </div>

  <div class="scroll-top" id="scrollTop" aria-label="Scroll to top">
    <i class="fa-solid fa-arrow-up"></i>
  </div>

  <!-- ===== Add Blog Modal (only for logged in) ===== -->
  <?php if ($isLoggedIn): ?>
  <div class="modal fade" id="addBlogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2"></i>Add New Blog</h5>
            <div class="small text-muted">Publishing as: <b><?= htmlspecialchars($userName ?: 'User') ?></b></div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form id="addBlogForm">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-bold">Title *</label>
              <input class="form-control" id="bTitle" required placeholder="Blog title">
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Category *</label>
                <input class="form-control" id="bCategory" required placeholder="e.g. destinations, tips...">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Cover image URL</label>
                <input class="form-control" id="bCover" placeholder="https://...">
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-md-4">
                <label class="form-label fw-bold">Read time (min)</label>
                <input type="number" class="form-control" id="bReadTime" min="1" value="5">
              </div>
              <div class="col-md-8">
                <label class="form-label fw-bold">Tags (comma separated)</label>
                <input class="form-control" id="bTags" placeholder="summer, cheap, europe">
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-md-4">
                <label class="form-label fw-bold">Location</label>
                <input class="form-control" id="bLocation" placeholder="Istanbul">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">Season</label>
                <input class="form-control" id="bSeason" placeholder="Summer">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">Budget</label>
                <input class="form-control" id="bBudget" placeholder="Low / Medium / High">
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label fw-bold">Excerpt *</label>
              <textarea class="form-control" id="bExcerpt" rows="3" required placeholder="Short summary..."></textarea>
            </div>

            <div class="mt-3">
              <label class="form-label fw-bold">Content *</label>
              <textarea class="form-control" id="bContent" rows="8" required placeholder="Write the full blog content..."></textarea>
            </div>

            <div class="alert alert-danger mt-3 d-none" id="addBlogErr"></div>
            <div class="alert alert-success mt-3 d-none" id="addBlogOk">Blog created successfully ✅</div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="addBlogSubmit">
              <i class="fa-solid fa-check me-1"></i> Publish
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    window.TRAVELO = {
      isLoggedIn: <?= $isLoggedIn ? 'true' : 'false' ?>,
      userId: <?= (int)$userId ?>,
      userName: <?= json_encode($userName) ?>
    };
  </script>

  <script>
    const API = './API/blogs.php';

    const blogsGrid = document.getElementById('blogsGrid');
    const loadingIndicator = document.getElementById('loadingIndicator');

    const categoryFilter = document.getElementById('category');
    const authorFilter   = document.getElementById('author');
    const dateFilter     = document.getElementById('date');
    const searchInput    = document.getElementById('search');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const sortBySelect    = document.getElementById('sortBy');
    const scrollTopBtn    = document.getElementById('scrollTop');
    const resultCount     = document.getElementById('resultCount');

    const applyFiltersTop = document.getElementById('applyFiltersTop');
    const resetFiltersTop = document.getElementById('resetFiltersTop');

    let currentBlogs = [];
    let filteredBlogs = [];

    function escapeHtml(str){
      return String(str ?? '')
        .replaceAll('&','&amp;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;')
        .replaceAll('"','&quot;')
        .replaceAll("'","&#039;");
    }

    function formatDate(dateString) {
      if (!dateString) return '';
      const d = new Date(String(dateString).replace(' ', 'T'));
      if (isNaN(+d)) return String(dateString);
      return d.toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' });
    }

    function initials(name){
      name = String(name || '').trim();
      if (!name) return 'U';
      return name[0].toUpperCase();
    }

    function normalizeRow(r){
      // expect from API: id, title, slug, excerpt, cover_image, category, read_time_min, views, created_at
      // plus author_name/username (joined from users)
      const title = r.title ?? '';
      const authorName = r.author_name ?? r.author_username ?? r.username ?? 'User';
      const authorRole = r.author_role ?? 'Travelo Writer';

      return {
        id: Number(r.id || 0),
        slug: r.slug || '',
        title,
        excerpt: r.excerpt || '',
        image: r.cover_image || '',
        category: (r.category || '').toLowerCase(),
        categoryName: r.category ? String(r.category) : 'General',
        author: {
          id: String(r.author_id || ''),
          name: String(authorName),
          role: String(authorRole)
        },
        date: r.created_at || r.updated_at || '',
        readTime: (r.read_time_min ? `${parseInt(r.read_time_min,10)} min` : '—'),
        views: Number(r.views || 0),
        status: String(r.status || 'published')
      };
    }

    async function getJson(url){
      const res = await fetch(url, { credentials: 'same-origin', cache: 'no-store' });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.success) {
        const msg = data.message || `HTTP ${res.status}`;
        throw new Error(msg);
      }
      return data;
    }

    async function postJson(url, payload){
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify(payload || {}),
        credentials: 'same-origin',
        cache: 'no-store'
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.success) {
        const msg = data.message || `HTTP ${res.status}`;
        throw new Error(msg);
      }
      return data;
    }

    function createBlogCard(blog) {
      const img = blog.image ? escapeHtml(blog.image) : '';
      const safeTitle = escapeHtml(blog.title);
      const safeExcerpt = escapeHtml(blog.excerpt);
      const safeCat = escapeHtml(blog.categoryName || 'General');
      const safeDate = escapeHtml(formatDate(blog.date));
      const safeAuthor = escapeHtml(blog.author.name || 'User');
      const safeRole = escapeHtml(blog.author.role || 'Writer');
      const safeRead = escapeHtml(blog.readTime || '—');

      const imgTag = img
        ? `<img src="${img}" alt="${safeTitle}" class="blog-image" loading="lazy">`
        : `<div class="blog-image" style="display:block;"></div>`;

      return `
        <article class="blog-card" data-id="${blog.id}" data-slug="${escapeHtml(blog.slug)}" data-category="${escapeHtml(blog.category)}" data-author="${escapeHtml(blog.author.id)}">
          ${imgTag}
          <div class="blog-content">
            <div class="blog-meta">
              <span class="blog-category">${safeCat}</span>
              <span>${safeDate}</span>
            </div>

            <h3 class="blog-title">${safeTitle}</h3>
            <p class="blog-excerpt">${safeExcerpt}</p>

            <div class="blog-footer">
              <div class="blog-author">
                <div class="author-avatar">${escapeHtml(initials(blog.author.name))}</div>
                <div class="author-info">
                  <h4>${safeAuthor}</h4>
                  <p>${safeRole}</p>
                </div>
              </div>

              <div class="read-time" title="Read time">
                <i class="fa-regular fa-clock"></i>
                <span>${safeRead}</span>
              </div>
            </div>
          </div>
        </article>
      `;
    }

    function displayBlogs(blogs) {
      resultCount.textContent = `${blogs.length} post${blogs.length === 1 ? '' : 's'}`;

      if (!blogs.length) {
        blogsGrid.innerHTML = `
          <div style="grid-column: 1 / -1; text-align:center; padding: 44px 12px;">
            <i class="fa-solid fa-magnifying-glass" style="font-size:2.6rem; color: var(--muted2); margin-bottom: 14px;"></i>
            <h3 style="color: var(--muted); font-weight:900; margin-bottom:6px;">No posts found</h3>
            <p style="color: var(--muted2); font-weight:800;">Try changing filters or search keywords.</p>
          </div>
        `;
        return;
      }

      blogsGrid.innerHTML = blogs.map(createBlogCard).join('');

      document.querySelectorAll('.blog-card').forEach(card => {
        card.addEventListener('click', () => {
          const slug = card.getAttribute('data-slug') || '';
          const id = card.getAttribute('data-id') || '';
          // اربطيها بصفحة التفاصيل عندك:
          // blog-details.php?slug=...
          const url = slug
            ? `blog-details.php?slug=${encodeURIComponent(slug)}`
            : `blog-details.php?id=${encodeURIComponent(id)}`;
          window.location.href = url;
        });
      });
    }

    function buildFilterOptions(blogs){
      const cats = new Map();
      const authors = new Map();

      blogs.forEach(b => {
        const cKey = b.category || '';
        const cName = b.categoryName || 'General';
        if (cKey) cats.set(cKey, cName);

        const aKey = b.author.id || '';
        const aName = b.author.name || 'User';
        if (aKey) authors.set(aKey, aName);
      });

      categoryFilter.innerHTML = `<option value="">All categories</option>` +
        Array.from(cats.entries())
          .sort((a,b)=> String(a[1]).localeCompare(String(b[1])))
          .map(([val,name]) => `<option value="${escapeHtml(val)}">${escapeHtml(name)}</option>`)
          .join('');

      authorFilter.innerHTML = `<option value="">All authors</option>` +
        Array.from(authors.entries())
          .sort((a,b)=> String(a[1]).localeCompare(String(b[1])))
          .map(([val,name]) => `<option value="${escapeHtml(val)}">${escapeHtml(name)}</option>`)
          .join('');
    }

    function filterBlogs() {
      const category = categoryFilter.value;
      const authorId = authorFilter.value;
      const date = dateFilter.value;
      const search = (searchInput.value || '').trim().toLowerCase();
      const sortBy = sortBySelect.value;

      let result = [...currentBlogs];

      if (category) result = result.filter(b => b.category === category);
      if (authorId) result = result.filter(b => String(b.author.id) === String(authorId));

      if (date) {
        const now = new Date();
        const cutoff = new Date(now);
        if (date === 'week') cutoff.setDate(now.getDate() - 7);
        if (date === 'month') cutoff.setDate(now.getDate() - 30);
        if (date === '3months') cutoff.setMonth(now.getMonth() - 3);
        if (date === 'year') cutoff.setFullYear(now.getFullYear() - 1);

        result = result.filter(b => {
          const d = new Date(String(b.date).replace(' ', 'T'));
          if (isNaN(+d)) return true;
          return d >= cutoff;
        });
      }

      if (search) {
        result = result.filter(b =>
          String(b.title).toLowerCase().includes(search) ||
          String(b.excerpt).toLowerCase().includes(search) ||
          String(b.categoryName).toLowerCase().includes(search) ||
          String(b.author.name).toLowerCase().includes(search)
        );
      }

      result.sort((a,b) => {
        const da = new Date(String(a.date).replace(' ', 'T'));
        const db = new Date(String(b.date).replace(' ', 'T'));

        if (sortBy === 'newest') return (db - da);
        if (sortBy === 'oldest') return (da - db);
        if (sortBy === 'popular') return (b.views||0) - (a.views||0);
        if (sortBy === 'title') return String(a.title).localeCompare(String(b.title), 'en');
        return 0;
      });

      filteredBlogs = result;
      displayBlogs(filteredBlogs);
    }

    function resetFilters() {
      categoryFilter.value = '';
      authorFilter.value = '';
      dateFilter.value = '';
      searchInput.value = '';
      sortBySelect.value = 'newest';
      filteredBlogs = [...currentBlogs];
      displayBlogs(filteredBlogs);
    }

    function scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function fetchBlogsFromAPI(){
      // list should return only published for non logged users on backend
      // but even if backend returns all, we filter on UI:
      const data = await getJson(`${API}?action=list&_=${Date.now()}`);
      const rows = Array.isArray(data.rows) ? data.rows : [];
      return rows.map(normalizeRow)
        .filter(b => (window.TRAVELO.isLoggedIn ? true : (b.status === 'published' || b.status === 'approved' || b.status === 'active')));
    }

    async function initApp() {
      loadingIndicator.style.display = 'flex';
      blogsGrid.innerHTML = '';

      try {
        const blogs = await fetchBlogsFromAPI();
        currentBlogs = blogs;
        filteredBlogs = [...blogs];

        buildFilterOptions(currentBlogs);

        loadingIndicator.style.display = 'none';
        displayBlogs(filteredBlogs);

        applyFiltersBtn.addEventListener('click', filterBlogs);
        resetFiltersBtn.addEventListener('click', resetFilters);
        sortBySelect.addEventListener('change', filterBlogs);

        applyFiltersTop.addEventListener('click', filterBlogs);
        resetFiltersTop.addEventListener('click', resetFilters);

        let searchTimeout;
        searchInput.addEventListener('input', () => {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(filterBlogs, 350);
        });

        window.addEventListener('scroll', () => {
          if (window.pageYOffset > 320) scrollTopBtn.classList.add('active');
          else scrollTopBtn.classList.remove('active');
        });

        scrollTopBtn.addEventListener('click', scrollToTop);

      } catch (err) {
        console.error(err);
        loadingIndicator.style.display = 'none';
        blogsGrid.innerHTML = `
          <div style="grid-column: 1 / -1; text-align:center; padding: 44px 12px;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:2.6rem; color: #dc3545; margin-bottom: 14px;"></i>
            <h3 style="color:#dc3545; font-weight:900; margin-bottom:6px;">Failed to load blogs</h3>
            <p style="color: var(--muted); font-weight:800;">${escapeHtml(err.message || 'Please try again later.')}</p>
            <button class="btnx btn-primaryx" style="margin-top:12px;" onclick="window.location.reload()">
              <i class="fa-solid fa-rotate-right"></i> Reload
            </button>
          </div>
        `;
      }
    }

    document.addEventListener('DOMContentLoaded', initApp);

    // ===== Add Blog (Logged-in Only) =====
    (function(){
      if (!window.TRAVELO.isLoggedIn) return;

      const openBtn = document.getElementById('openAddBlog');
      const modalEl = document.getElementById('addBlogModal');
      const form    = document.getElementById('addBlogForm');

      const errBox  = document.getElementById('addBlogErr');
      const okBox   = document.getElementById('addBlogOk');
      const submit  = document.getElementById('addBlogSubmit');

      const bTitle   = document.getElementById('bTitle');
      const bCategory= document.getElementById('bCategory');
      const bCover   = document.getElementById('bCover');
      const bRead    = document.getElementById('bReadTime');
      const bTags    = document.getElementById('bTags');
      const bLoc     = document.getElementById('bLocation');
      const bSeason  = document.getElementById('bSeason');
      const bBudget  = document.getElementById('bBudget');
      const bExcerpt = document.getElementById('bExcerpt');
      const bContent = document.getElementById('bContent');

      if (!openBtn || !modalEl || !form || !window.bootstrap) return;

      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

      function resetForm(){
        form.reset();
        if (bRead) bRead.value = 5;
        errBox.classList.add('d-none');
        okBox.classList.add('d-none');
      }

      openBtn.addEventListener('click', () => {
        resetForm();
        modal.show();
      });

      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        errBox.classList.add('d-none');
        okBox.classList.add('d-none');

        const payload = {
          title: (bTitle.value || '').trim(),
          category: (bCategory.value || '').trim(),
          cover_image: (bCover.value || '').trim(),
          read_time_min: parseInt(bRead.value || '5', 10) || 5,
          tags: (bTags.value || '').trim(),
          location: (bLoc.value || '').trim(),
          season: (bSeason.value || '').trim(),
          budget: (bBudget.value || '').trim(),
          excerpt: (bExcerpt.value || '').trim(),
          content: (bContent.value || '').trim()
        };

        if (!payload.title || !payload.category || !payload.excerpt || !payload.content) {
          errBox.textContent = 'Please fill required fields: Title, Category, Excerpt, Content.';
          errBox.classList.remove('d-none');
          return;
        }

        submit.disabled = true;
        try{
          await postJson(`${API}?action=create`, payload);
          okBox.classList.remove('d-none');

          // reload list
          const blogs = await fetchBlogsFromAPI();
          currentBlogs = blogs;
          buildFilterOptions(currentBlogs);
          resetFilters();

          setTimeout(() => {
            modal.hide();
          }, 600);

        }catch(err){
          errBox.textContent = err.message || 'Create failed';
          errBox.classList.remove('d-none');
        }finally{
          submit.disabled = false;
        }
      });

      // optional: clean backdrop issues if any
      modalEl.addEventListener('hidden.bs.modal', () => {
        document.body.style.paddingRight = '';
      });
    })();
  </script>
</body>
</html>
