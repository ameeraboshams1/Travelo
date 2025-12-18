document.addEventListener("DOMContentLoaded", () => {
  const API = "./API/faqs.php";

  const sideNav = document.getElementById("sideNav");
  const anchorsWrap = document.getElementById("faqAnchors");
  const faqList = document.getElementById("faqList");

  const tabs = Array.from(document.querySelectorAll(".tab[data-category]"));
  const searchInput = document.getElementById("searchInput");
  const clearSearch = document.getElementById("clearSearch");

  const expandAllBtn = document.getElementById("expandAll");
  const collapseAllBtn = document.getElementById("collapseAll");
  const printBtn = document.getElementById("printPage");

  const resultCount = document.getElementById("resultCount");
  const popularTag = document.getElementById("popularTag");
  const recentTag = document.getElementById("recentTag");

  const yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  // Feedback UI
  const openFeedback = document.getElementById("openFeedback");
  const modal = document.getElementById("feedbackModal");
  const closeFeedback = document.getElementById("closeFeedback");
  const cancelFeedback = document.getElementById("cancelFeedback");
  const sendFeedback = document.getElementById("sendFeedback");
  const downloadFeedback = document.getElementById("downloadFeedback");
  const fbTopic = document.getElementById("fbTopic");
  const fbText = document.getElementById("fbText");

  const toastEl = document.getElementById("toast");
  const scrollTopBtn = document.getElementById("scrollTop");

  if (!faqList) return;

  // ===== helpers =====
  const esc = (s) =>
    String(s ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  function toast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.classList.add("show");
    clearTimeout(toastEl._t);
    toastEl._t = setTimeout(() => toastEl.classList.remove("show"), 1800);
  }

  async function getJson(url) {
    const res = await fetch(url + (url.includes("?") ? "&" : "?") + `_=${Date.now()}`, {
      credentials: "same-origin",
      cache: "no-store",
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) throw new Error(data.message || "Request failed");
    return data;
  }

  // ===== state =====
  let activeTab = "All";
  let q = "";
  let onlyPopular = false;
  let onlyRecent = false;

  // map tab to DB enum values
  const CAT_MAP = {
    All: "All",
    Booking: "Booking",
    Payments: "Payments",
    Flights: "Flights",
    Hotels: "Hotels",
    Changes: "Changes & Refunds",
    Policies: "Travel Policies",
    Support: "Support & Account",
  };

  const DB_CATS_ORDER = [
    "Booking",
    "Payments",
    "Flights",
    "Hotels",
    "Changes & Refunds",
    "Travel Policies",
    "Support & Account",
  ];

  // ===== recent =====
  function getRecentIds() {
    try {
      const raw = localStorage.getItem("travelo_recent_faqs") || "[]";
      const arr = JSON.parse(raw);
      return Array.isArray(arr) ? arr.map(String) : [];
    } catch {
      return [];
    }
  }

  function pushRecent(id) {
    const ids = getRecentIds();
    const sid = String(id);
    const next = [sid, ...ids.filter((x) => x !== sid)].slice(0, 15);
    try { localStorage.setItem("travelo_recent_faqs", JSON.stringify(next)); } catch {}
  }

  function setActiveTabUI(key) {
    tabs.forEach((b) => {
      const k = b.getAttribute("data-category");
      const on = k === key;
      b.classList.toggle("is-active", on);
      b.setAttribute("aria-selected", on ? "true" : "false");
    });
  }

  function updatePills(total) {
    if (resultCount) resultCount.textContent = `${total} result${total === 1 ? "" : "s"}`;
    if (popularTag) popularTag.classList.toggle("active", !!onlyPopular);

    if (recentTag) {
      const hasRecent = getRecentIds().length > 0;
      recentTag.classList.toggle("hidden", !hasRecent);
      recentTag.classList.toggle("active", !!onlyRecent);
    }
  }

  // ===== render sidebar + anchors =====
  function slug(cat) {
    // stable id for anchors
    return String(cat).toLowerCase().replaceAll("&", "and").replaceAll(" ", "-").replaceAll(/[^a-z0-9\-]/g, "");
  }

  function renderSideNav(categories) {
    if (!sideNav) return;

    const cats = (categories && categories.length) ? categories : DB_CATS_ORDER;

    sideNav.innerHTML = cats.map(c => {
      const id = slug(c);
      return `<a href="#sec-${esc(id)}" class="side-link" data-sec="${esc(id)}">${esc(c)}</a>`;
    }).join("");
  }

  function renderSectionAnchors(categories) {
    if (!anchorsWrap) return;

    const cats = (categories && categories.length) ? categories : DB_CATS_ORDER;

    anchorsWrap.innerHTML = cats.map(c => {
      const id = slug(c);
      return `<div id="sec-${esc(id)}"></div>`;
    }).join("");
  }

  // ===== render FAQ list =====
  function buildItemHTML(r) {
    const id = esc(r.id);
    const cat = esc(r.category);
    const question = esc(r.question);
    const answer = esc(r.answer);
    const tags = esc(r.tags || "");
    const popular = +r.is_popular ? " is-popular" : "";

    return `
      <article class="faq-item${popular}" id="faq-${id}" role="listitem" data-id="${id}" data-cat="${cat}">
        <header class="faq-q">
          <button class="faq-toggle" type="button" aria-expanded="false">
            <span class="faq-cat">${cat}</span>
            <span class="faq-question">${question}</span>
          </button>
          <button class="faq-link" type="button" title="Copy link" aria-label="Copy link">🔗</button>
        </header>

        <div class="faq-a" hidden>
          <div class="faq-answer" style="white-space:pre-wrap; line-height:1.8;">${answer}</div>
          ${tags ? `<div class="faq-tags">Tags: <span>${tags}</span></div>` : ""}
        </div>
      </article>
    `;
  }

  function renderGrouped(rows) {
    const group = new Map(DB_CATS_ORDER.map(c => [c, []]));
    rows.forEach(r => {
      const c = String(r.category || "");
      if (!group.has(c)) group.set(c, []);
      group.get(c).push(r);
    });

    const catsWithItems = DB_CATS_ORDER.filter(c => (group.get(c) || []).length);

    // update sidebar & anchors based on what exists in DB حاليا
    renderSideNav(catsWithItems.length ? catsWithItems : DB_CATS_ORDER);
    renderSectionAnchors(catsWithItems.length ? catsWithItems : DB_CATS_ORDER);

    let html = "";
    (catsWithItems.length ? catsWithItems : DB_CATS_ORDER).forEach((c) => {
      const list = group.get(c) || [];
      if (!list.length) return;

      const secId = slug(c);
      html += `
        <div class="faq-group" data-group="${esc(c)}">
          <h2 class="faq-group-title" style="margin:18px 0 10px; font-weight:900;" data-sec-title="${esc(secId)}">
            ${esc(c)}
          </h2>
          ${list.map(buildItemHTML).join("")}
        </div>
      `;
    });

    faqList.innerHTML = html || `<div class="muted">No FAQs found.</div>`;
  }

  function expandAll() {
    faqList.querySelectorAll(".faq-item").forEach((item) => {
      const btn = item.querySelector(".faq-toggle");
      const body = item.querySelector(".faq-a");
      if (!btn || !body) return;
      btn.setAttribute("aria-expanded", "true");
      body.hidden = false;
    });
  }

  function collapseAll() {
    faqList.querySelectorAll(".faq-item").forEach((item) => {
      const btn = item.querySelector(".faq-toggle");
      const body = item.querySelector(".faq-a");
      if (!btn || !body) return;
      btn.setAttribute("aria-expanded", "false");
      body.hidden = true;
    });
  }

  // ===== load =====
  async function load() {
    const dbCat = CAT_MAP[activeTab] || "All";

    const params = new URLSearchParams();
    params.set("action", "list");
    params.set("limit", "500");
    if (dbCat && dbCat !== "All") params.set("category", dbCat);
    if (q) params.set("q", q);
    if (onlyPopular) params.set("onlyPopular", "1");

    const data = await getJson(`${API}?${params.toString()}`);
    let rows = Array.isArray(data.rows) ? data.rows : [];

    if (onlyRecent) {
      const recentIds = new Set(getRecentIds());
      rows = rows.filter(r => recentIds.has(String(r.id)));
    }

    renderGrouped(rows);
    updatePills(rows.length);

    // open hash item if exists
    const hash = location.hash || "";
    if (hash.startsWith("#faq-")) {
      const el = document.querySelector(hash);
      if (el) {
        const btn = el.querySelector(".faq-toggle");
        btn?.click();
        el.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    }
  }

  // ===== events =====

  // Tabs
  tabs.forEach((btn) => {
    btn.addEventListener("click", async () => {
      activeTab = btn.getAttribute("data-category") || "All";
      setActiveTabUI(activeTab);
      await load();
    });
  });

  // Search
  let t = null;
  searchInput?.addEventListener("input", () => {
    clearTimeout(t);
    t = setTimeout(async () => {
      q = (searchInput.value || "").trim();
      await load();
    }, 250);
  });

  clearSearch?.addEventListener("click", async () => {
    if (!searchInput) return;
    searchInput.value = "";
    q = "";
    await load();
    searchInput.focus();
  });

  // Expand/Collapse/Print
  expandAllBtn?.addEventListener("click", expandAll);
  collapseAllBtn?.addEventListener("click", collapseAll);
  printBtn?.addEventListener("click", () => window.print());

  // Popular / Recent
  popularTag?.addEventListener("click", async () => {
    onlyPopular = !onlyPopular;
    onlyRecent = false;
    await load();
  });

  recentTag?.addEventListener("click", async () => {
    onlyRecent = !onlyRecent;
    if (onlyRecent) onlyPopular = false;
    await load();
  });

  // Toggle answer + copy link
  faqList.addEventListener("click", async (e) => {
    const item = e.target.closest(".faq-item");
    if (!item) return;

    const id = item.getAttribute("data-id");

    if (e.target.closest(".faq-link")) {
      const url = `${location.origin}${location.pathname}#faq-${id}`;
      try {
        await navigator.clipboard.writeText(url);
        toast("Link copied ✅");
      } catch {
        toast("Copy failed");
      }
      return;
    }

    if (e.target.closest(".faq-toggle")) {
      const body = item.querySelector(".faq-a");
      const expanded = item.querySelector(".faq-toggle")?.getAttribute("aria-expanded") === "true";
      item.querySelector(".faq-toggle")?.setAttribute("aria-expanded", expanded ? "false" : "true");
      if (body) body.hidden = expanded;

      if (!expanded && id) pushRecent(id);
      updatePills(faqList.querySelectorAll(".faq-item").length);
    }
  });

  // Sidebar smooth scroll
  document.addEventListener("click", (e) => {
    const a = e.target.closest("#sideNav a.side-link");
    if (!a) return;
    const href = a.getAttribute("href") || "";
    if (!href.startsWith("#sec-")) return;

    e.preventDefault();
    const target = document.querySelector(href);
    target?.scrollIntoView({ behavior: "smooth", block: "start" });
  });

  // Scroll top
  scrollTopBtn?.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  // ===== Feedback modal (local only) =====
  function openModal() {
    if (!modal) return;
    modal.classList.add("open");
    modal.setAttribute("aria-hidden", "false");
  }
  function closeModal() {
    if (!modal) return;
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden", "true");
  }

  openFeedback?.addEventListener("click", openModal);
  closeFeedback?.addEventListener("click", closeModal);
  cancelFeedback?.addEventListener("click", closeModal);

  modal?.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  function getFeedbackRows() {
    try {
      const raw = localStorage.getItem("travelo_feedback") || "[]";
      const arr = JSON.parse(raw);
      return Array.isArray(arr) ? arr : [];
    } catch {
      return [];
    }
  }

  function saveFeedbackRow(row) {
    const rows = getFeedbackRows();
    rows.unshift(row);
    try { localStorage.setItem("travelo_feedback", JSON.stringify(rows.slice(0, 300))); } catch {}
  }

  sendFeedback?.addEventListener("click", () => {
    const topic = (fbTopic?.value || "").trim();
    const text = (fbText?.value || "").trim();
    if (!text) { toast("Write feedback first ✍️"); return; }

    saveFeedbackRow({
      topic,
      text,
      created_at: new Date().toISOString()
    });

    if (fbTopic) fbTopic.value = "";
    if (fbText) fbText.value = "";

    toast("Saved locally ✅");
    closeModal();
  });

  downloadFeedback?.addEventListener("click", () => {
    const rows = getFeedbackRows();
    if (!rows.length) { toast("No saved feedback"); return; }

    const header = ["created_at","topic","text"];
    const csv = [
      header.join(","),
      ...rows.map(r => [
        `"${String(r.created_at || "").replaceAll('"','""')}"`,
        `"${String(r.topic || "").replaceAll('"','""')}"`,
        `"${String(r.text || "").replaceAll('"','""')}"`
      ].join(","))
    ].join("\n");

    const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "travelo_feedback.csv";
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  });

  // ===== init =====
  (async () => {
    setActiveTabUI("All");
    updatePills(0);
    await load();
  })();
});
