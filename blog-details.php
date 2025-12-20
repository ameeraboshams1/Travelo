<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Travelo · Blog Details</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
      line-height:1.65;
      min-height:100vh;
      overflow-x:hidden;
    }
    a{ text-decoration:none; color:inherit; }
    img{ max-width:100%; display:block; }

    .container{
      width:100%;
      max-width: 980px;
      margin:0 auto;
      padding: 0 16px;
    }

    .top-tools{
      margin: 18px auto 0;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      flex-wrap:wrap;
    }
    .pill{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding: 10px 14px;
      border-radius: 999px;
      background: rgba(255,255,255,.78);
      border: 1px solid rgba(232,234,243,.9);
      box-shadow: var(--shadow);
      color: var(--muted);
      font-weight: 800;
      font-size: .95rem;
    }
    .pill i{
      width: 30px; height: 30px;
      border-radius: 12px;
      display:flex; align-items:center; justify-content:center;
      background: var(--accentSoft);
      border: 1px solid rgba(124,58,237,.18);
      color: var(--accent);
    }

    .btn{
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
    }
    .btn-outline{
      background: rgba(255,255,255,.72);
      color: var(--accent);
      border: 2px solid rgba(124,58,237,.35);
      box-shadow: 0 10px 24px rgba(15,23,42,.08);
    }
    .btn-outline:hover{
      transform: translateY(-2px);
      background: rgba(124,58,237,.08);
      box-shadow: var(--shadowHover);
    }
    .btn-primary{
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      color: #fff;
      box-shadow: 0 12px 30px rgba(124,58,237,.22);
    }
    .btn-primary:hover{ transform: translateY(-2px); box-shadow: var(--shadowHover); }

    .hero{
      margin: 14px auto 18px;
      border-radius: 22px;
      overflow:hidden;
      border: 1px solid rgba(232,234,243,.9);
      box-shadow: var(--shadow);
      background: rgba(255,255,255,.75);
      position:relative;
      transform: translateY(8px);
      opacity: 0;
      animation: enter .55s ease-out forwards;
    }
    .hero::before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(900px 380px at 12% 0%, rgba(124,58,237,.20), transparent 55%),
        radial-gradient(820px 420px at 94% 20%, rgba(108,99,255,.18), transparent 58%);
      pointer-events:none;
      opacity:.9;
    }

    .cover{
      position:relative;
      height: 360px;
      overflow:hidden;
      isolation:isolate;
    }
    .cover img{
      width:100%;
      height:100%;
      object-fit:cover;
      transform: scale(1.04);
      transition: transform .9s ease;
    }
    .hero:hover .cover img{ transform: scale(1.08); }

    .cover .overlay{
      position:absolute;
      inset:0;
      background:
        linear-gradient(180deg, rgba(15,23,42,.10) 0%, rgba(15,23,42,.35) 55%, rgba(15,23,42,.70) 100%);
      z-index:1;
    }

    .cover .floating{
      position:absolute; z-index:2;
      right: 16px; top: 16px;
      display:flex; gap: 10px; flex-wrap:wrap;
    }
    .chip{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 9px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,.86);
      border: 1px solid rgba(232,234,243,.9);
      box-shadow: 0 12px 26px rgba(15,23,42,.16);
      font-weight: 900;
      font-size: .85rem;
      color: var(--ink);
      backdrop-filter: blur(8px);
    }
    .chip i{ color: var(--accent); }

    .hero-content{
      position:relative;
      z-index:3;
      padding: 18px 18px 22px;
    }

    .meta-row{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      flex-wrap:wrap;
      margin-bottom: 10px;
    }

    .category{
      display:inline-flex;
      align-items:center;
      padding: 7px 12px;
      border-radius: 999px;
      background: linear-gradient(135deg, rgba(124,58,237,.95), rgba(108,99,255,.95));
      color:#fff;
      font-weight: 900;
      font-size: .8rem;
      letter-spacing:.2px;
      box-shadow: 0 12px 26px rgba(124,58,237,.18);
      transform: translateY(0);
      animation: floaty 3.5s ease-in-out infinite;
    }

    .meta-inline{
      display:flex;
      align-items:center;
      gap: 10px;
      color: var(--muted);
      font-weight: 800;
      font-size: .95rem;
    }
    .meta-inline .dot{
      width:6px; height:6px;
      border-radius:999px;
      background: rgba(100,116,139,.55);
    }

    .title{
      font-size: clamp(1.45rem, 3.2vw, 2.1rem);
      font-weight: 950;
      line-height: 1.25;
      letter-spacing:.1px;
      margin-bottom: 10px;
    }
    .excerpt{
      color: var(--muted);
      font-weight: 700;
      font-size: 1.02rem;
      max-width: 80ch;
    }

    .content-card{
      margin: 0 auto 16px;
      border-radius: 22px;
      background: rgba(255,255,255,.86);
      border: 1px solid rgba(232,234,243,.9);
      box-shadow: var(--shadow);
      overflow:hidden;
      opacity:0;
      transform: translateY(10px);
      animation: enter2 .6s ease-out .12s forwards;
    }

    .content-inner{
      padding: 18px;
      display:grid;
      grid-template-columns: 1.2fr .8fr;
      gap: 16px;
    }

    .article{ padding: 4px 2px; }
    .article h2{ margin: 18px 0 10px; font-size: 1.25rem; font-weight: 950; }
    .article p{ color: var(--ink); margin: 10px 0; font-weight: 650; }
    .article ul{ margin: 10px 0 10px 18px; color: var(--ink); font-weight: 650; }
    .article li{ margin: 8px 0; }

    .side{
      position:sticky;
      top: 16px;
      height: fit-content;
      border-radius: 18px;
      padding: 14px;
      background:
        radial-gradient(520px 240px at 20% 0%, rgba(124,58,237,.14), transparent 62%),
        rgba(255,255,255,.92);
      border: 1px solid rgba(232,234,243,.9);
    }

    .author{
      display:flex;
      gap: 12px;
      align-items:center;
      padding: 12px;
      border-radius: 16px;
      border: 1px solid rgba(232,234,243,.9);
      background: rgba(255,255,255,.85);
    }
    .author img{
      width: 56px; height: 56px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid rgba(124,58,237,.35);
      box-shadow: 0 10px 22px rgba(15,23,42,.10);
    }
    .author h4{
      font-weight: 950;
      margin-bottom: 2px;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
      max-width: 220px;
    }
    .author p{
      color: var(--muted2);
      font-weight: 800;
      font-size: .9rem;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
      max-width: 220px;
    }

    .mini{ margin-top: 12px; display:grid; gap: 10px; }
    .mini .row{
      display:flex;
      justify-content:space-between;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 14px;
      border: 1px solid rgba(232,234,243,.9);
      background: rgba(255,255,255,.86);
      font-weight: 900;
      color: var(--muted);
    }
    .mini .row span:last-child{
      color: var(--ink);
      display:flex;
      align-items:center;
      gap: 8px;
    }
    .mini .row i{ color: var(--accent); }

    .tags{
      margin-top: 12px;
      padding: 12px;
      border-radius: 16px;
      border: 1px solid rgba(232,234,243,.9);
      background: rgba(255,255,255,.86);
    }
    .tags h5{ font-weight: 950; margin-bottom: 10px; }
    .tag-list{ display:flex; gap: 8px; flex-wrap:wrap; }
    .tag{
      padding: 7px 10px;
      border-radius: 999px;
      font-weight: 900;
      font-size: .82rem;
      color: var(--accent);
      background: var(--accentSoft);
      border: 1px solid rgba(124,58,237,.18);
    }

    .actions{ margin-top: 12px; display:grid; gap: 10px; }
    .actions .btn{ width:100%; justify-content:center; }

    .progress{
      position: fixed; top: 0; left: 0;
      height: 3px; width: 0%;
      background: linear-gradient(90deg, var(--accent), var(--accent2));
      z-index: 999;
      box-shadow: 0 10px 30px rgba(124,58,237,.25);
    }

    .skeleton{ position:relative; overflow:hidden; background: rgba(148,163,184,.20); border-radius: 14px; }
    .skeleton:after{
      content:""; position:absolute; inset:0;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
      transform: translateX(-100%);
      animation: shimmer 1.1s infinite;
    }
    @keyframes shimmer{ to { transform: translateX(100%); } }
    .s-cover{ height: 360px; border-radius: 0; }
    .s-title{ height: 28px; margin: 10px 0; width: 80%; }
    .s-line{ height: 14px; margin: 10px 0; width: 100%; }
    .s-line.w{ width: 78%; }

    .toast{
      position: fixed;
      bottom: 22px;
      left: 22px;
      max-width: 360px;
      padding: 12px 14px;
      border-radius: 16px;
      background: rgba(15,23,42,.88);
      color: #fff;
      border: 1px solid rgba(255,255,255,.08);
      box-shadow: var(--shadowHover);
      opacity:0;
      transform: translateY(12px);
      pointer-events:none;
      transition: var(--t);
      z-index: 999;
      display:flex;
      gap: 10px;
      align-items:flex-start;
    }
    .toast.show{ opacity:1; transform: translateY(0); }
    .toast i{ color: #c7b6ff; margin-top: 2px; }

    .error-card{
      margin: 14px auto 0;
      border-radius: 22px;
      background: rgba(255,255,255,.86);
      border: 1px solid rgba(232,234,243,.9);
      box-shadow: var(--shadow);
      padding: 28px 18px;
      text-align:center;
    }
    .error-card .icon{
      width: 64px; height: 64px;
      border-radius: 20px;
      margin: 0 auto 12px;
      display:flex; align-items:center; justify-content:center;
      background: rgba(220,53,69,.10);
      border: 1px solid rgba(220,53,69,.22);
      color: #dc3545;
      font-size: 28px;
    }
    .error-card h3{ font-weight: 950; margin-bottom: 6px; color:#dc3545; }
    .error-card p{ color: var(--muted); font-weight: 800; }

    @keyframes enter{ to{ opacity:1; transform: translateY(0); } }
    @keyframes enter2{ to{ opacity:1; transform: translateY(0); } }
    @keyframes floaty{ 0%,100%{ transform: translateY(0); } 50%{ transform: translateY(-4px); } }

    @media (max-width: 960px){
      .content-inner{ grid-template-columns: 1fr; }
      .side{ position:relative; top:0; }
      .cover{ height: 320px; }
    }
    @media (max-width: 640px){
      .cover{ height: 260px; }
      .top-tools{ justify-content:flex-start; }
      .pill{ width:100%; justify-content:flex-start; }
      .btn{ width:100%; }
    }
    /* ===================== THEME (Dark default) ===================== */
:root{
  /* Dark variables */
  --ink:#eaf0ff;
  --muted:#aab4d6;
  --muted2:#8f9ac1;

  --bg:#0b1020;
  --card: rgba(18,24,44,.86);
  --border: rgba(255,255,255,.10);

  --shadow: 0 18px 50px rgba(0,0,0,.45);
  --shadowHover: 0 26px 70px rgba(0,0,0,.55);
}

/* Light mode override */
body.light{
  --ink:#0f172a;
  --muted:#64748b;
  --muted2:#94a3b8;

  --bg:#f7f8fb;
  --card:#ffffff;
  --border:#e8eaf3;

  --shadow: 0 14px 40px rgba(15,23,42,.10);
  --shadowHover: 0 22px 60px rgba(15,23,42,.16);
}
body{
  background:
    radial-gradient(1200px 700px at 18% -10%,
      rgba(124,58,237, .22), transparent 60%),
    radial-gradient(900px 520px at 92% 8%,
      rgba(108,99,255, .18), transparent 55%),
    var(--bg);
  color:var(--ink);
  line-height:1.65;
  min-height:100vh;
  overflow-x:hidden;
}
.theme-toggle{
  position: fixed;
  bottom: 22px;
  right: 22px;
  width: 52px;
  height: 52px;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: rgba(255,255,255,.06);
  color: var(--ink);
  display:flex;
  align-items:center;
  justify-content:center;
  cursor:pointer;
  box-shadow: var(--shadow);
  transition: var(--t);
  z-index: 1000;
  backdrop-filter: blur(10px);
}
.theme-toggle:hover{
  transform: translateY(-4px);
  border-color: rgba(124,58,237,.35);
  box-shadow: var(--shadowHover);
}

  </style>
</head>

<body>
  <div class="progress" id="progress"></div>

  <div class="container">
    <div class="top-tools">
      <button class="btn btn-outline" id="backBtn"><i class="fa-solid fa-arrow-left"></i> Back to Blogs</button>
      <div class="pill" id="crumb">
        <i class="fa-regular fa-bookmark"></i>
        <span>Blog Details</span>
      </div>
    </div>

    <!-- Skeleton -->
    <section class="hero" id="heroSkeleton">
      <div class="cover"><div class="skeleton s-cover"></div></div>
      <div class="hero-content">
        <div class="skeleton s-title"></div>
        <div class="skeleton s-line"></div>
        <div class="skeleton s-line w"></div>
      </div>
    </section>

    <!-- Error -->
    <section class="error-card" id="errorCard" style="display:none;">
      <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3>Failed to load blog</h3>
      <p id="errorText">Unexpected API response</p>
      <button class="btn btn-primary" style="margin-top:12px;" id="reloadBtn">
        <i class="fa-solid fa-rotate-right"></i> Reload
      </button>
    </section>

    <!-- Real content -->
    <section class="hero" id="hero" style="display:none;">
      <div class="cover" id="cover">
        <img id="coverImg" alt="">
        <div class="overlay"></div>
        <div class="floating" id="floatingChips"></div>
      </div>

      <div class="hero-content">
        <div class="meta-row">
          <span class="category" id="category"></span>
          <div class="meta-inline">
            <span id="date"></span><span class="dot"></span>
            <span id="readTime"></span><span class="dot"></span>
            <span id="views"></span>
          </div>
        </div>

        <h1 class="title" id="title"></h1>
        <p class="excerpt" id="excerpt"></p>
      </div>
    </section>

    <section class="content-card" id="contentCard" style="display:none;">
      <div class="content-inner">
        <article class="article" id="article"></article>

        <aside class="side">
          <div class="author">
            <img id="authorAvatar" alt="">
            <div>
              <h4 id="authorName"></h4>
              <p id="authorRole"></p>
            </div>
          </div>

          <div class="mini">
            <div class="row">
              <span>Location</span>
              <span><i class="fa-solid fa-location-dot"></i> <span id="location"></span></span>
            </div>
            <div class="row">
              <span>Best Season</span>
              <span><i class="fa-regular fa-sun"></i> <span id="season"></span></span>
            </div>
            <div class="row">
              <span>Budget</span>
              <span><i class="fa-solid fa-wallet"></i> <span id="budget"></span></span>
            </div>
          </div>

          <div class="tags">
            <h5>Tags</h5>
            <div class="tag-list" id="tags"></div>
          </div>

          <div class="actions">
            <button class="btn btn-primary" id="shareBtn"><i class="fa-solid fa-share-nodes"></i> Share</button>
            <button class="btn btn-outline" id="copyLinkBtn"><i class="fa-regular fa-copy"></i> Copy link</button>
          </div>
        </aside>
      </div>
    </section>
  </div>

  <div class="toast" id="toast">
    <i class="fa-solid fa-circle-check"></i>
    <div id="toastText" style="font-weight:800;"></div>
  </div>

  <script>
    const API = "./API/blogs.php";

    const $ = (id) => document.getElementById(id);

    const heroSkeleton = $("heroSkeleton");
    const hero = $("hero");
    const contentCard = $("contentCard");

    const errorCard = $("errorCard");
    const errorText = $("errorText");

    const coverImg = $("coverImg");
    const floatingChips = $("floatingChips");

    const category = $("category");
    const dateEl = $("date");
    const readTime = $("readTime");
    const viewsEl = $("views");
    const titleEl = $("title");
    const excerptEl = $("excerpt");

    const authorAvatar = $("authorAvatar");
    const authorName = $("authorName");
    const authorRole = $("authorRole");

    const locationEl = $("location");
    const seasonEl = $("season");
    const budgetEl = $("budget");
    const tagsEl = $("tags");
    const article = $("article");

    const progress = $("progress");
    const toast = $("toast");
    const toastText = $("toastText");

    function showToast(text){
      toastText.textContent = text;
      toast.classList.add("show");
      setTimeout(() => toast.classList.remove("show"), 1600);
    }

    function formatDateAny(s){
      if (!s) return "";
      const d = new Date(s);
      if (isNaN(d.getTime())) return String(s);
      return d.toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' });
    }

    function safeStr(v){ return (v === null || v === undefined) ? "" : String(v); }

    function renderChips(list){
      if (!Array.isArray(list) || !list.length){ floatingChips.innerHTML = ""; return; }
      floatingChips.innerHTML = list.map(c => `
        <span class="chip"><i class="${c.icon}"></i> ${c.text}</span>
      `).join("");
    }

    function renderTags(tagsRaw){
      const raw = safeStr(tagsRaw).trim();
      if (!raw) { tagsEl.innerHTML = `<span class="tag">#travel</span>`; return; }

      // tags عندك غالبًا string (مثال: "beach, summer, greece")
      const parts = raw.split(",").map(t => t.trim()).filter(Boolean).slice(0, 18);
      if (!parts.length) { tagsEl.innerHTML = `<span class="tag">#travel</span>`; return; }

      tagsEl.innerHTML = parts.map(t => `<span class="tag">#${t}</span>`).join("");
    }

    function updateProgress(){
      const doc = document.documentElement;
      const st = doc.scrollTop || document.body.scrollTop;
      const sh = (doc.scrollHeight || document.body.scrollHeight) - doc.clientHeight;
      const pct = sh <= 0 ? 0 : (st / sh) * 100;
      progress.style.width = pct + "%";
    }

    function parallaxCover(){
      const y = window.scrollY || 0;
      coverImg.style.transform = `scale(1.06) translateY(${Math.min(18, y * 0.06)}px)`;
    }

    function getQueryParam(name){
      const url = new URL(window.location.href);
      return url.searchParams.get(name);
    }

    function showError(msg, extra){
      heroSkeleton.style.display = "none";
      hero.style.display = "none";
      contentCard.style.display = "none";
      errorCard.style.display = "block";
      errorText.textContent = extra ? `${msg} — ${extra}` : msg;
    }

    async function fetchJson(url){
      const res = await fetch(url, { headers: { "Accept": "application/json" } });
      const ct = (res.headers.get("content-type") || "").toLowerCase();
      const text = await res.text();

      // لو رجع HTML (مسار غلط) أو نص مش JSON
      if (!ct.includes("application/json")) {
        throw new Error("Non-JSON response (check API path). First 120 chars: " + text.slice(0,120));
      }

      let data;
      try { data = JSON.parse(text); }
      catch { throw new Error("Invalid JSON. First 120 chars: " + text.slice(0,120)); }

      if (!res.ok) {
        throw new Error(data?.message || ("HTTP " + res.status));
      }
      return data;
    }

    async function loadBlog(id, slug){
      const qs = new URLSearchParams();
      qs.set("action", "get");
      if (id) qs.set("id", id);
      else if (slug) qs.set("slug", slug);

      const data = await fetchJson(`${API}?${qs.toString()}`);

      // شكل API عندك: {success:true, row:{...}}
      if (!data || data.success !== true || !data.row) {
        throw new Error("Unexpected API shape (expected: {success:true,row:{...}})");
      }
      return data.row;
    }

    async function incrementView(id){
      if (!id) return;
      const qs = new URLSearchParams({ action:"view", id:String(id) });
      // fire-and-forget
      fetch(`${API}?${qs.toString()}`, { headers: { "Accept":"application/json" } }).catch(()=>{});
    }

    function htmlFromContent(content){
      // لو content مخزن HTML (زي ما بتكتبي من مودال) خليه كما هو
      // لو plain text حوّليه لفقرات
      const s = safeStr(content).trim();
      if (!s) return `<p>No content.</p>`;

      const looksLikeHtml = /<\s*[a-z][\s\S]*>/i.test(s);
      if (looksLikeHtml) return s;

      // plain text -> paragraphs
      const esc = s
        .replaceAll("&","&amp;")
        .replaceAll("<","&lt;")
        .replaceAll(">","&gt;")
        .replaceAll('"',"&quot;")
        .replaceAll("'","&#039;");
      return esc.split(/\n{2,}/).map(p => `<p>${p.replaceAll("\n","<br>")}</p>`).join("");
    }

    async function init(){
      $("backBtn").addEventListener("click", () => window.location.href = "blogs.php");
      $("reloadBtn").addEventListener("click", () => window.location.reload());

      $("shareBtn").addEventListener("click", async () => {
        const url = window.location.href;
        if (navigator.share) {
          try{ await navigator.share({ title: document.title, url }); }catch(_){}
        } else {
          await navigator.clipboard.writeText(url);
          showToast("Link copied ✅");
        }
      });

      $("copyLinkBtn").addEventListener("click", async () => {
        await navigator.clipboard.writeText(window.location.href);
        showToast("Copied to clipboard ✅");
      });

      window.addEventListener("scroll", () => { updateProgress(); parallaxCover(); }, { passive:true });
      updateProgress();

      const id = getQueryParam("id");
      const slug = getQueryParam("slug");

      if (!id && !slug) {
        showError("Missing id or slug in URL", "Open like: blog-details.php?id=1");
        return;
      }

      try{
        // جرّبي زوّدي view (اختياري)
        if (id) incrementView(id);

        const row = await loadBlog(id, slug);

        const cover = safeStr(row.cover_image).trim() || "https://images.unsplash.com/photo-1520975916090-3105956dac38?auto=format&fit=crop&w=1600&q=80";
        const cat = safeStr(row.category).trim() || "General";
        const t = safeStr(row.title).trim() || "Untitled";
        const ex = safeStr(row.excerpt).trim() || "—";

        coverImg.src = cover;
        coverImg.alt = t;

        const chips = [];
        if (row.location) chips.push({ icon:"fa-solid fa-location-dot", text: safeStr(row.location) });
        if (row.season) chips.push({ icon:"fa-regular fa-sun", text: safeStr(row.season) });
        if (row.budget) chips.push({ icon:"fa-solid fa-wallet", text: safeStr(row.budget) });
        renderChips(chips.slice(0,3));

        category.textContent = cat;

        // عندك created_at بالجدول
        dateEl.textContent = formatDateAny(row.created_at);

        const rt = row.read_time_min ? `${row.read_time_min} min` : "—";
        readTime.textContent = rt;

        const v = (row.views ?? 0);
        viewsEl.textContent = `${Number(v).toLocaleString()} views`;

        titleEl.textContent = t;
        excerptEl.textContent = ex;

        // author_name + author_username جايين من API
        const an = safeStr(row.author_name).trim() || safeStr(row.author_username).trim() || "Unknown";
        authorName.textContent = an;
        authorRole.textContent = row.author_username ? `@${row.author_username}` : "Author";

        // avatar مش موجود بجدول users عندك -> نخليه افتراضي
        authorAvatar.src = "https://ui-avatars.com/api/?name=" + encodeURIComponent(an) + "&background=7c3aed&color=fff&bold=true";
        authorAvatar.alt = an;

        locationEl.textContent = safeStr(row.location).trim() || "—";
        seasonEl.textContent = safeStr(row.season).trim() || "—";
        budgetEl.textContent = safeStr(row.budget).trim() || "—";

        renderTags(row.tags);
        article.innerHTML = htmlFromContent(row.content);

        // show content
        heroSkeleton.style.display = "none";
        errorCard.style.display = "none";
        hero.style.display = "block";
        contentCard.style.display = "block";

        setTimeout(() => window.dispatchEvent(new Event("scroll")), 50);

      } catch (e) {
        console.error(e);
        showError("Unexpected API response", e?.message || "");
      }
    }

    document.addEventListener("DOMContentLoaded", init);
  </script>
  <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle theme">
  <i class="fa-solid fa-moon" id="themeIcon"></i>
</button>
<script>
  (function initTheme(){
    const saved = localStorage.getItem('travelo_theme') || 'dark';
    if (saved === 'light') document.body.classList.add('light');
    updateThemeIcon();
  })();

  function updateThemeIcon(){
    const icon = document.getElementById('themeIcon');
    if(!icon) return;
    const isLight = document.body.classList.contains('light');
    icon.classList.toggle('fa-sun', isLight);
    icon.classList.toggle('fa-moon', !isLight);
  }

  document.getElementById('themeToggle')?.addEventListener('click', () => {
    document.body.classList.toggle('light');
    localStorage.setItem('travelo_theme',
      document.body.classList.contains('light') ? 'light' : 'dark'
    );
    updateThemeIcon();
  });
</script>

</body>
</html>
