(() => {
  const API = "./API/faqs.php";

  // ===== DOM =====
  const faqList = document.getElementById("faqList");
  const searchInput = document.getElementById("searchInput");
  const clearSearch = document.getElementById("clearSearch");
  const resultCount = document.getElementById("resultCount");
  const tabs = Array.from(document.querySelectorAll(".tab"));
  const expandAllBtn = document.getElementById("expandAll");
  const collapseAllBtn = document.getElementById("collapseAll");
  const printBtn = document.getElementById("printPage");
  const popularTag = document.getElementById("popularTag");
  const recentTag = document.getElementById("recentTag");
  const toast = document.getElementById("toast");

  const feedbackModal = document.getElementById("feedbackModal");
  const openFeedback = document.getElementById("openFeedback");
  const closeFeedback = document.getElementById("closeFeedback");
  const cancelFeedback = document.getElementById("cancelFeedback");
  const sendFeedback = document.getElementById("sendFeedback");
  const downloadFeedback = document.getElementById("downloadFeedback");
  const fbTopic = document.getElementById("fbTopic");
  const fbText = document.getElementById("fbText");

  const yearSpan = document.getElementById("year");
  const scrollTopBtn = document.getElementById("scrollTop");
  if (yearSpan) yearSpan.textContent = new Date().getFullYear();

  if (!faqList) return;

  // ===== helpers =====
  function debounce(fn, ms = 180) {
    let t;
    return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
  }

  function esc(s) {
    return String(s ?? "").replace(/[&<>"']/g, c => ({
      "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
    }[c]));
  }

  function toastMsg(msg) {
    if (!toast) return;
    toast.textContent = msg;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 1400);
  }

  function highlight(text, term) {
    const raw = String(text ?? "");
    if (!term) return esc(raw);
    const safe = term.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const rx = new RegExp(`(${safe})`, "ig");
    return esc(raw).replace(rx, `<mark>$1</mark>`);
  }

  async function getJson(url) {
    const res = await fetch(url + (url.includes("?") ? "&" : "?") + `_=${Date.now()}`, {
      credentials: "same-origin",
      cache: "no-store"
    });
    const data = await res.json().catch(() => null);
    if (!data) throw new Error("API did not return JSON");
    if (!res.ok || !data.success) throw new Error(data.message || "Request failed");
    return data;
  }

  // ===== state =====
  const state = {
    query: "",
    category: "All",            // tab key: All/Booking/Payments/Flights/Hotels/Changes/Policies/Support
    onlyPopular: false,
    onlyRecent: false,
    recentIds: new Set(JSON.parse(localStorage.getItem("travelo_recent") || "[]")),
  };

  // Tabs mapping (your tab keys => DB category text)
  const TAB_TO_DB = {
    All: "All",
    Booking: "Booking",
    Payments: "Payments",
    Flights: "Flights",
    Hotels: "Hotels",
    Changes: "Changes & Refunds",
    Policies: "Travel Policies",
    Support: "Support & Account",
  };

  // ===== API load (cache in memory) =====
  let DATA = []; // rows from DB: {id, category, question, answer, tags, is_popular, ...}

  async function loadFromApi() {
    // backend: action=list + limit
    const data = await getJson(`${API}?action=list&limit=500`);
    const rows = Array.isArray(data.rows) ? data.rows : [];

    // normalize to the exact structure your UI expects
    DATA = rows
      .filter(r => +r.is_active !== 0) // show active فقط
      .map(r => ({
        id: String(r.id),
        category: normalizeCategory(String(r.category || "All")),
        popular: !!(+r.is_popular),
        tags: parseTags(r.tags),
        question: String(r.question || ""),
        answer: String(r.answer || ""),
        sort_order: Number(r.sort_order || 0),
      }))
      // sort per category then sort_order
      .sort((a, b) => {
        const ac = a.category.localeCompare(b.category);
        if (ac !== 0) return ac;
        return (a.sort_order - b.sort_order) || a.question.localeCompare(b.question);
      });
  }

  function normalizeCategory(dbCat) {
    // DB might return full text (Changes & Refunds), etc. Keep as is but align with side nav keys:
    // side nav uses Booking/Payments/Flights/Hotels/Changes/Policies/Support as ids,
    // but UI badge shows the real category text (dbCat).
    return dbCat;
  }

  function parseTags(tags) {
    if (!tags) return [];
    // tags in DB might be: "refund, baggage, check-in" or "refund|baggage"
    const s = String(tags);
    const parts = s.split(/[,|]/g).map(x => x.trim()).filter(Boolean);
    return parts;
  }

  // ===== filtering =====
  function filteredData() {
    const q = state.query.trim().toLowerCase();
    const tabKey = state.category || "All";
    const dbCat = TAB_TO_DB[tabKey] || "All";

    let arr = DATA.slice();

    // tab filter
    if (dbCat !== "All") {
      arr = arr.filter(d => d.category === dbCat);
    }

    // popular toggle
    if (state.onlyPopular) {
      arr = arr.filter(d => d.popular);
    }

    // recent toggle
    if (state.onlyRecent) {
      arr = arr.filter(d => state.recentIds.has(d.id));
    }

    // search filter
    if (q) {
      arr = arr.filter(d => {
        const hay = (d.question + " " + d.answer + " " + (d.tags || []).join(" ")).toLowerCase();
        return hay.includes(q);
      });
    }

    return arr;
  }

  // ===== accordion UI =====
  function setPanelOpen(panel, header, open) {
    const inner = panel.querySelector(".faq-panel-content");
    const chev = header.querySelector(".chev");
    if (!inner) return;

    if (open) {
      panel.style.maxHeight = inner.scrollHeight + "px";
      header.setAttribute("aria-expanded", "true");
      if (chev) chev.style.transform = "rotate(180deg)";
    } else {
      panel.style.maxHeight = "0px";
      header.setAttribute("aria-expanded", "false");
      if (chev) chev.style.transform = "rotate(0deg)";
    }
  }

  function expandAll() {
    document.querySelectorAll(".faq-item").forEach(item => {
      const header = item.querySelector(".faq-header");
      const panel = item.querySelector(".faq-panel");
      if (header && panel) setPanelOpen(panel, header, true);
    });
  }

  function collapseAll() {
    document.querySelectorAll(".faq-item").forEach(item => {
      const header = item.querySelector(".faq-header");
      const panel = item.querySelector(".faq-panel");
      if (header && panel) setPanelOpen(panel, header, false);
    });
  }

  // keep maxHeight correct when panel content changes/resizes
  const ro = new ResizeObserver(entries => {
    entries.forEach(entry => {
      const panel = entry.target.closest(".faq-panel");
      if (!panel) return;
      const headerId = panel.getAttribute("aria-labelledby");
      const header = headerId ? document.getElementById(headerId) : null;
      if (header && header.getAttribute("aria-expanded") === "true") {
        panel.style.maxHeight = entry.target.scrollHeight + "px";
      }
    });
  });

  function attachObservers() {
    document.querySelectorAll(".faq-panel .faq-panel-content").forEach(inner => ro.observe(inner));
  }

  // ===== helpful (localStorage) =====
  function getHelpful() { return JSON.parse(localStorage.getItem("travelo_helpful") || "{}"); }
  function setHelpful(o) { localStorage.setItem("travelo_helpful", JSON.stringify(o)); }

  function applyHelpful(id) {
    const s = getHelpful()[id] || { up: 0, down: 0, voted: null };
    const box = document.querySelector(`.helpful[data-id="${id}"]`);
    if (!box) return;

    box.querySelector('[data-vote="up"]')?.setAttribute("aria-pressed", s.voted === "up" ? "true" : "false");
    box.querySelector('[data-vote="down"]')?.setAttribute("aria-pressed", s.voted === "down" ? "true" : "false");

    const score = box.querySelector(`#${CSS.escape(id)}-score`);
    if (score) score.textContent = `Helpful: ${s.up} • Not helpful: ${s.down}`;
  }

  function voteHelpful(id, kind) {
    const store = getHelpful();
    const cur = store[id] || { up: 0, down: 0, voted: null };

    if (cur.voted === kind) {
      cur[kind] = Math.max(0, cur[kind] - 1);
      cur.voted = null;
    } else {
      if (cur.voted) cur[cur.voted] = Math.max(0, cur[cur.voted] - 1);
      cur[kind] += 1;
      cur.voted = kind;
    }

    store[id] = cur;
    setHelpful(store);
    applyHelpful(id);

    toastMsg(cur.voted ? (cur.voted === "up" ? "Thanks for the feedback!" : "Got it — we’ll improve this.") : "Vote removed");
  }

  // ===== render =====
  function render() {
    const qTerm = state.query.trim();
    const rows = filteredData();

    // pills visibility
    if (popularTag) popularTag.classList.toggle("hidden", !DATA.some(r => r.popular));
    if (recentTag) recentTag.classList.toggle("hidden", state.recentIds.size === 0);

    // result label (keep your behavior)
    if (resultCount) {
      const txt =
        qTerm || state.category !== "All" || state.onlyPopular || state.onlyRecent
          ? `Showing ${rows.length} result${rows.length !== 1 ? "s" : ""}`
            + (state.category !== "All" ? ` in “${state.category}”` : "")
            + (qTerm ? ` for “${qTerm}”` : "")
            + (state.onlyPopular ? " • Popular" : "")
            + (state.onlyRecent ? " • Recent" : "")
            + "."
          : "";
      resultCount.textContent = txt;
    }

    faqList.innerHTML = "";

    rows.forEach(item => {
      const article = document.createElement("article");
      article.className = "faq-item";
      article.id = item.id;

      const header = document.createElement("button");
      header.className = "faq-header";
      header.id = `${item.id}-header`;
      header.type = "button";
      header.setAttribute("aria-controls", `${item.id}-panel`);
      header.setAttribute("aria-expanded", "false");

      const left = document.createElement("div");
      left.className = "faq-left";

      const badge = document.createElement("span");
      badge.className = "badge";
      badge.textContent = item.category;

      const qEl = document.createElement("div");
      qEl.className = "faq-q";
      qEl.innerHTML = highlight(item.question, qTerm);

      left.append(badge, qEl);

      const tools = document.createElement("div");

      const copyBtn = document.createElement("button");
      copyBtn.className = "copy-link";
      copyBtn.type = "button";
      copyBtn.title = "Copy direct link";
      copyBtn.textContent = "🔗";
      copyBtn.addEventListener("click", async (e) => {
        e.stopPropagation();
        const url = `${location.origin}${location.pathname}#${item.id}`;
        try {
          await navigator.clipboard.writeText(url);
          toastMsg("Link copied");
        } catch {
          toastMsg("Copy failed");
        }
      });

      const chev = document.createElement("span");
      chev.className = "chev";
      chev.textContent = "⌄";

      tools.append(copyBtn, chev);
      header.append(left, tools);

      const panel = document.createElement("div");
      panel.className = "faq-panel";
      panel.id = `${item.id}-panel`;
      panel.setAttribute("role", "region");
      panel.setAttribute("aria-labelledby", `${item.id}-header`);
      panel.style.maxHeight = "0px";

      const inner = document.createElement("div");
      inner.className = "faq-panel-content";
      inner.innerHTML = `
        <div>${highlight(item.answer, qTerm)}</div>
        ${item.tags?.length ? `<div class="tags">${item.tags.map(t => `<span class="tag">#${esc(t)}</span>`).join("")}</div>` : ""}
        <div class="helpful" data-id="${esc(item.id)}">
          <button class="thumb" type="button" aria-pressed="false" data-vote="up">👍 Helpful</button>
          <button class="thumb" type="button" aria-pressed="false" data-vote="down">👎 Not really</button>
          <span class="help-score" id="${esc(item.id)}-score"></span>
        </div>
      `;
      panel.appendChild(inner);

      header.addEventListener("click", (e) => {
        if (e.target.closest(".copy-link")) return;

        const willOpen = header.getAttribute("aria-expanded") !== "true";
        setPanelOpen(panel, header, willOpen);

        if (willOpen) {
          history.replaceState(null, "", `#${item.id}`);
          state.recentIds.add(item.id);
          localStorage.setItem("travelo_recent", JSON.stringify([...state.recentIds]));
          if (recentTag) recentTag.classList.remove("hidden");
        }
      });

      article.append(header, panel);
      faqList.appendChild(article);

      // helpful events
      inner.querySelectorAll(".thumb").forEach(btn => {
        btn.addEventListener("click", (ev) => {
          ev.stopPropagation();
          voteHelpful(item.id, btn.dataset.vote);
        });
      });

      applyHelpful(item.id);
    });

    attachObservers();
    openFromHash();
  }

  function openFromHash() {
    const id = (location.hash || "").replace("#", "");
    if (!id) return;

    const header = document.getElementById(`${id}-header`);
    const panel = document.getElementById(`${id}-panel`);
    if (header && panel) {
      setPanelOpen(panel, header, true);
      const inner = panel.querySelector(".faq-panel-content");
      if (inner) ro.observe(inner);
      header.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  }

  // ===== events =====
  const onSearch = debounce(() => {
    state.query = (searchInput?.value || "");
    clearSearch?.classList.toggle("show", !!state.query);
    render();
    expandAll(); // keep your behavior
  }, 150);

  searchInput?.addEventListener("input", onSearch);

  clearSearch?.addEventListener("click", () => {
    if (!searchInput) return;
    searchInput.value = "";
    state.query = "";
    clearSearch.classList.remove("show");
    render();
  });

  tabs.forEach(btn => {
    btn.addEventListener("click", () => {
      tabs.forEach(t => t.classList.remove("is-active"));
      btn.classList.add("is-active");

      state.category = btn.dataset.category || "All";
      render();

      const layout = document.querySelector(".layout");
      if (layout) window.scrollTo({ top: layout.offsetTop - 12, behavior: "smooth" });
    });
  });

  popularTag?.addEventListener("click", () => {
    state.onlyPopular = !state.onlyPopular;
    state.onlyRecent = false;

    popularTag.classList.toggle("active", state.onlyPopular);
    recentTag?.classList.remove("active");

    render();
  });

  recentTag?.addEventListener("click", () => {
    state.onlyRecent = !state.onlyRecent;
    if (state.onlyRecent) state.onlyPopular = false;

    recentTag.classList.toggle("active", state.onlyRecent);
    popularTag?.classList.remove("active");

    render();
  });

  expandAllBtn?.addEventListener("click", () => { expandAll(); attachObservers(); });
  collapseAllBtn?.addEventListener("click", () => collapseAll());
  printBtn?.addEventListener("click", () => window.print());

  scrollTopBtn?.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));

  // ===== Feedback modal (as-is) =====
  (function initFeedback() {
    const modal = feedbackModal;
    if (!modal) return;

    function openModal() {
      modal.classList.add("show");
      modal.setAttribute("aria-hidden", "false");
      fbText?.focus();
    }
    function closeModal() {
      modal.classList.remove("show");
      modal.setAttribute("aria-hidden", "true");
    }

    openFeedback?.addEventListener("click", openModal);
    closeFeedback?.addEventListener("click", closeModal);
    cancelFeedback?.addEventListener("click", closeModal);

    function getFB() { return JSON.parse(localStorage.getItem("travelo_feedback") || "[]"); }
    function setFB(v) { localStorage.setItem("travelo_feedback", JSON.stringify(v)); }

    sendFeedback?.addEventListener("click", () => {
      const topic = (fbTopic?.value || "").trim();
      const text = (fbText?.value || "").trim();
      if (!text) { toastMsg("Please write some feedback."); return; }

      const all = getFB();
      all.push({ topic, text, ts: new Date().toISOString() });
      setFB(all);

      if (fbTopic) fbTopic.value = "";
      if (fbText) fbText.value = "";

      closeModal();
      toastMsg("Thanks for your feedback! 💜");
    });

    downloadFeedback?.addEventListener("click", () => {
      const rows = getFB();
      if (!rows.length) { toastMsg("No feedback saved yet."); return; }

      const csv = ["topic,text,timestamp", ...rows.map(r =>
        `"${(r.topic || "").replace(/"/g, '""')}","${String(r.text || "").replace(/"/g, '""')}","${r.ts}"`
      )].join("\n");

      const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
      const a = document.createElement("a");
      a.href = URL.createObjectURL(blob);
      a.download = "travelo_feedback.csv";
      document.body.appendChild(a);
      a.click();
      a.remove();
    });
  })();

  // ===== Side nav jump (keeps your behavior) =====
  (function initSideNavJump() {
    const sideLinks = document.querySelectorAll(".side-link");

    function scrollToWithOffset(el, offset = 80) {
      if (!el) return;
      const y = el.getBoundingClientRect().top + window.pageYOffset - offset;
      window.scrollTo({ top: y, behavior: "smooth" });
    }

    sideLinks.forEach(link => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        const catKey = (link.getAttribute("href") || "").replace("#", "");
        if (!catKey) return;

        // activate tab if exists
        const tab = tabs.find(t => (t.dataset.category || "") === catKey);
        if (tab) {
          tabs.forEach(t => t.classList.remove("is-active"));
          tab.classList.add("is-active");
        }

        state.category = catKey;
        render();

        const dbCat = TAB_TO_DB[catKey] || catKey;

        const firstItem = Array.from(document.querySelectorAll(".faq-item"))
          .find(it => it.querySelector(".badge")?.textContent === dbCat);

        if (firstItem) {
          const header = firstItem.querySelector(".faq-header");
          const panel  = firstItem.querySelector(".faq-panel");
          if (header && panel) setPanelOpen(panel, header, true);
          scrollToWithOffset(firstItem, 80);
        } else {
          scrollToWithOffset(document.querySelector(".faq-list"), 80);
        }

        document.querySelectorAll(".side-link").forEach(a => a.classList.toggle("active", a === link));
        history.replaceState(null, "", "#" + catKey);
      });
    });
  })();

  // ===== init =====
  (async () => {
    try {
      await loadFromApi();
      render();
    } catch (e) {
      console.error(e);
      faqList.innerHTML = `<div class="muted">Failed to load FAQs. Check API.</div>`;
      toastMsg("Failed to load FAQs");
    }
  })();
})();
