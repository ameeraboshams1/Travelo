
(function initNavbarMenu() {
    const nav = document.querySelector('.nav');
    const btn = document.querySelector('.menu-toggle');
    if (!nav || !btn) return;

    const links = document.querySelectorAll('.nav-links-ul a, .nav-button button');

    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-label', 'Open menu');

    function toggleMenu(force) {
        const willOpen = force !== undefined ? force : !nav.classList.contains('open');
        nav.classList.toggle('open', willOpen);
        btn.classList.toggle('active', willOpen);
        btn.setAttribute('aria-expanded', String(willOpen));
        btn.setAttribute('aria-label', willOpen ? 'Close menu' : 'Open menu');
        document.documentElement.style.overflow = willOpen ? 'hidden' : '';
    }

    btn.addEventListener('click', () => toggleMenu());
    links.forEach(el => el.addEventListener('click', () => toggleMenu(false)));
    document.addEventListener('click', (e) => {
        if (!nav.contains(e.target) && nav.classList.contains('open')) toggleMenu(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && nav.classList.contains('open')) toggleMenu(false);
    });
    const BREAKPOINT = 900;
    window.addEventListener('resize', () => {
        if (window.innerWidth > BREAKPOINT && nav.classList.contains('open')) toggleMenu(false);
    });
})();

const DATA = [
    {
        id: "q-how-book", category: "Booking", popular: true, tags: ["search", "itinerary", "PNR"],
        question: "How do I book a trip on Travelo?",
        answer: "Go to Flights or Hotels, enter your details, pick an option, and review the itinerary before payment. Your booking reference (PNR) will arrive by email within minutes."
    },
    {
        id: "q-multi-city", category: "Booking", tags: ["multi-city", "open-jaw"],
        question: "Can I book multi-city or open-jaw itineraries?",
        answer: "Yes. Use the Multi-City option on the Flights page. You can add up to 6 legs and mix airlines if available."
    },
    {
        id: "q-confirmation-delay", category: "Booking", tags: ["email", "confirmation"],
        question: "I didn’t get my confirmation email—what now?",
        answer: "Check spam and make sure your email is correct. You can always find your bookings under ‘My Bookings’. If it’s missing after 15 minutes, contact support."
    },

    {
        id: "q-pay-methods", category: "Payments", popular: true, tags: ["cards", "wallets", "security"],
        question: "Which payment methods are accepted?",
        answer: "We accept major credit/debit cards, selected local wallets, and bank transfers in some regions. All transactions use strong encryption and anti-fraud checks."
    },
    {
        id: "q-failed-payment", category: "Payments", tags: ["declined", "3DS", "bank"],
        question: "My payment failed—what should I do?",
        answer: "Verify card details/balance and try again. If it fails, call your bank to enable online/international transactions or use another method."
    },
    {
        id: "q-invoice", category: "Payments", tags: ["invoice", "receipt", "VAT"],
        question: "Can I get an invoice with VAT details?",
        answer: "Yes. After payment, download your invoice from ‘My Bookings’. For company invoices, add billing info before checkout."
    },

    {
        id: "q-baggage", category: "Flights", popular: true, tags: ["baggage", "allowance", "cabin"],
        question: "How much baggage can I bring?",
        answer: "Allowance varies by airline and fare. The exact details are shown during checkout and in your e-ticket. Extra/oversized bags may incur fees at the airport."
    },
    {
        id: "q-seat", category: "Flights", tags: ["seats", "selection"],
        question: "Can I choose my seat?",
        answer: "Many fares allow seat selection for free or a small fee. Choose in checkout or later via ‘My Bookings’. Some airlines assign seats at check-in."
    },
    {
        id: "q-checkin", category: "Flights", tags: ["online check-in", "boarding pass"],
        question: "Is online check-in available?",
        answer: "Most airlines open online check-in 24–48 hours before departure. We email you a reminder with your airline’s link when it opens."
    },

    {
        id: "q-hotel-cancel", category: "Hotels", tags: ["cancellation", "non-refundable"],
        question: "What is the hotel cancellation policy?",
        answer: "Each property sets its own policy. Check the ‘Cancellation Policy’ on the room card. Non-refundable deals are cheaper but can’t be canceled."
    },
    {
        id: "q-hotel-amenities", category: "Hotels", tags: ["breakfast", "wifi", "parking"],
        question: "Are breakfast and Wi-Fi included?",
        answer: "Depends on the room type and property. Inclusions are listed on the room card and in your confirmation email."
    },
    {
        id: "q-hotel-late", category: "Hotels", tags: ["late check-in", "no-show"],
        question: "Arriving late to the hotel—what should I do?",
        answer: "Add a late check-in note during booking or contact the property. If arriving after midnight, call ahead to avoid a no-show."
    },

    {
        id: "q-change-flight", category: "Changes", tags: ["change", "date", "time", "fee"],
        question: "Can I change my flight after booking?",
        answer: "If fare rules allow, you can change date/time for an airline fee + any fare difference. Go to ‘My Bookings’ to request a change."
    },
    {
        id: "q-refund-time", category: "Changes", popular: true, tags: ["refund", "timeline", "processing"],
        question: "How long do refunds take?",
        answer: "Approved refunds typically appear in 5–14 business days depending on your bank/method. We’ll email updates throughout the process."
    },
    {
        id: "q-cancel-window", category: "Changes", tags: ["24h", "cool-off"],
        question: "Is there a 24-hour free cancellation window?",
        answer: "Some fares and countries provide a grace period. If applicable, you’ll see it during checkout and in confirmation details."
    },

    {
        id: "q-visa", category: "Policies", tags: ["visa", "passport", "documents"],
        question: "Do I need a visa for my trip?",
        answer: "Visa rules depend on nationality and destination. Always check official government sources well in advance and ensure your passport validity meets requirements."
    },
    {
        id: "q-baby", category: "Policies", tags: ["infant", "child", "stroller"],
        question: "Traveling with infants or children—what should I know?",
        answer: "Check each airline’s infant/child policy for seating and baggage. Bring birth documents if required; strollers are often checked free at the gate."
    },
    {
        id: "q-special-assist", category: "Policies", tags: ["wheelchair", "medical"],
        question: "How do I request special assistance?",
        answer: "Add a note in checkout or contact support at least 48 hours before departure. Many airlines require advance notice to arrange assistance."
    },

    {
        id: "q-contact", category: "Support", tags: ["email", "24/7"],
        question: "How do I contact Travelo support?",
        answer: "Use the Support page for contact options (email). For urgent travel issues, we’re available 24/7."
    },
    {
        id: "q-secure", category: "Support", tags: ["2FA", "security"],
        question: "How do I keep my account secure?",
        answer: "Enable two-factor authentication, use a strong unique password, and never share one-time codes. We’ll never ask for your password."
    },
    {
        id: "q-delete", category: "Support", tags: ["delete account", "privacy"],
        question: "Can I delete my account?",
        answer: "Yes. Request deletion from Account Settings → Privacy. We’ll confirm by email and process according to our retention policies."
    },
];

const faqList = document.getElementById("faqList");
const searchInput = document.getElementById("searchInput");
const clearSearch = document.getElementById("clearSearch");
const resultCount = document.getElementById("resultCount");
const tabs = document.querySelectorAll(".tab");
const expandAllBtn = document.getElementById("expandAll");
const collapseAllBtn = document.getElementById("collapseAll");
const printBtn = document.getElementById("printPage");
const popularTag = document.getElementById("popularTag");
const recentTag = document.getElementById("recentTag");
const feedbackModal = document.getElementById("feedbackModal");
const openFeedback = document.getElementById("openFeedback");
const closeFeedback = document.getElementById("closeFeedback");
const cancelFeedback = document.getElementById("cancelFeedback");
const sendFeedback = document.getElementById("sendFeedback");
const downloadFeedback = document.getElementById("downloadFeedback");
const fbTopic = document.getElementById("fbTopic");
const fbText = document.getElementById("fbText");
const toast = document.getElementById("toast");
const yearSpan = document.getElementById("year");
const scrollTopBtn = document.getElementById("scrollTop");
if (yearSpan) yearSpan.textContent = new Date().getFullYear();

function debounce(fn, ms = 180) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }
function esc(s) { return s.replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c])) }
function highlight(text, term) {
    if (!term) return esc(text);
    const rx = new RegExp(`(${term.replace(/[.*+?^${}()|[\\]\\\\]/g, "\\$&")})`, "ig");
    return esc(text).replace(rx, `<mark>$1</mark>`);
}
function toastMsg(msg) { if (!toast) return; toast.textContent = msg; toast.classList.add("show"); setTimeout(() => toast.classList.remove("show"), 1400); }

let state = {
    query: "",
    category: "All",
    recentIds: new Set(JSON.parse(localStorage.getItem("travelo_recent") || "[]"))
};

function filteredData() {
    const q = state.query.trim().toLowerCase();
    return DATA.filter(d => {
        const okCat = state.category === "All" || d.category === state.category;
        if (!okCat) return false;
        if (!q) return true;
        return (d.question + " " + d.answer + " " + (d.tags || []).join(" ")).toLowerCase().includes(q);
    });
}

function render() {
    if (!faqList) return;
    const q = state.query.trim();
    const rows = filteredData();

    if (popularTag) popularTag.classList.toggle("hidden", !rows.some(r => r.popular));
    if (recentTag) recentTag.classList.toggle("hidden", state.recentIds.size === 0);

    if (resultCount) {
        resultCount.textContent = q || state.category !== "All"
            ? `Showing ${rows.length} result${rows.length !== 1 ? "s" : ""}${state.category !== "All" ? ` in “${state.category}”` : ""}${q ? ` for “${q}”` : ""}.`
            : "";
    }

    faqList.innerHTML = "";
    rows.forEach(item => {
        const article = document.createElement("article");
        article.className = "faq-item";

        const header = document.createElement("button");
        header.className = "faq-header";
        header.id = `${item.id}-header`;
        header.setAttribute("aria-controls", `${item.id}-panel`);
        header.setAttribute("aria-expanded", "false");

        const left = document.createElement("div");
        left.className = "faq-left";

        const badge = document.createElement("span");
        badge.className = "badge";
        badge.textContent = item.category;

        const qEl = document.createElement("div");
        qEl.className = "faq-q";
        qEl.innerHTML = highlight(item.question, q);

        left.append(badge, qEl);

        const tools = document.createElement("div");
        const copyBtn = document.createElement("button");
        copyBtn.className = "copy-link";
        copyBtn.type = "button"; copyBtn.title = "Copy direct link"; copyBtn.textContent = "🔗";
        copyBtn.addEventListener("click", e => {
            e.stopPropagation();
            const url = `${location.origin}${location.pathname}#${item.id}`;
            navigator.clipboard.writeText(url).then(() => toastMsg("Link copied"));
        });
        const chev = document.createElement("span");
        chev.className = "chev"; chev.textContent = "⌄";

        tools.append(copyBtn, chev);
        header.append(left, tools);

        const panel = document.createElement("div");
        panel.className = "faq-panel";
        panel.id = `${item.id}-panel`;
        panel.setAttribute("role", "region");
        panel.setAttribute("aria-labelledby", `${item.id}-header`);

        const inner = document.createElement("div");
        inner.className = "faq-panel-content";
        inner.innerHTML = `
      <div>${highlight(item.answer, q)}</div>
      ${item.tags ? `<div class="tags">${item.tags.map(t => `<span class="tag">#${esc(t)}</span>`).join("")}</div>` : ""}
      <div class="helpful" data-id="${item.id}">
        <button class="thumb" aria-pressed="false" data-vote="up">👍 Helpful</button>
        <button class="thumb" aria-pressed="false" data-vote="down">👎 Not really</button>
        <span class="help-score" id="${item.id}-score"></span>
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
            }
        });

        article.append(header, panel);
        faqList.appendChild(article);

        applyHelpful(item.id);
    });

    faqList.querySelectorAll(".helpful").forEach(box => {
        const id = box.dataset.id;
        box.querySelectorAll(".thumb").forEach(btn => {
            btn.addEventListener("click", () => voteHelpful(id, btn.dataset.vote));
        });
    });

    openFromHash();
}

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

function getHelpful() { return JSON.parse(localStorage.getItem("travelo_helpful") || "{}"); }
function setHelpful(o) { localStorage.setItem("travelo_helpful", JSON.stringify(o)); }
function applyHelpful(id) {
    const s = getHelpful()[id] || { up: 0, down: 0, voted: null };
    const box = document.querySelector(`.helpful[data-id="${id}"]`);
    if (!box) return;
    box.querySelector('[data-vote="up"]').setAttribute("aria-pressed", s.voted === 'up' ? "true" : "false");
    box.querySelector('[data-vote="down"]').setAttribute("aria-pressed", s.voted === 'down' ? "true" : "false");
    const score = box.querySelector(`#${id}-score`);
    if (score) score.textContent = `Helpful: ${s.up} • Not helpful: ${s.down}`;
}
function voteHelpful(id, kind) {
    const store = getHelpful();
    const cur = store[id] || { up: 0, down: 0, voted: null };
    if (cur.voted === kind) {
        cur[kind] = Math.max(0, cur[kind] - 1);
        cur.voted = null;
    } else {
        if (cur.voted) { cur[cur.voted] = Math.max(0, cur[cur.voted] - 1); }
        cur[kind] += 1; cur.voted = kind;
    }
    store[id] = cur; setHelpful(store); applyHelpful(id);
    toastMsg(cur.voted ? (cur.voted === 'up' ? 'Thanks for the feedback!' : 'Got it — we’ll improve this.') : 'Vote removed');
}

const onSearch = debounce(() => {
    state.query = (searchInput?.value || "");
    clearSearch?.classList.toggle("show", !!state.query);
    render();

    expandAll();
}, 150);
searchInput?.addEventListener("input", onSearch);
clearSearch?.addEventListener("click", () => {
    if (!searchInput) return;
    searchInput.value = ""; state.query = ""; clearSearch.classList.remove("show"); render();
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

(function initFeedback() {
    const modal = feedbackModal;
    if (!modal) return;
    function openModal() { modal.classList.add("show"); modal.setAttribute("aria-hidden", "false"); fbText?.focus(); }
    function closeModal() { modal.classList.remove("show"); modal.setAttribute("aria-hidden", "true"); }
    openFeedback?.addEventListener("click", openModal);
    closeFeedback?.addEventListener("click", closeModal);
    cancelFeedback?.addEventListener("click", closeModal);

    function getFB() { return JSON.parse(localStorage.getItem("travelo_feedback") || "[]"); }
    function setFB(v) { localStorage.setItem("travelo_feedback", JSON.stringify(v)); }

    sendFeedback?.addEventListener("click", () => {
        const topic = (fbTopic?.value || "").trim();
        const text = (fbText?.value || "").trim();
        if (!text) { toastMsg("Please write some feedback."); return; }
        const all = getFB(); all.push({ topic, text, ts: new Date().toISOString() });
        setFB(all);
        if (fbTopic) fbTopic.value = ""; if (fbText) fbText.value = "";
        closeModal(); toastMsg("Thanks for your feedback! 💜");
    });

    downloadFeedback?.addEventListener("click", () => {
        const rows = getFB();
        if (!rows.length) { toastMsg("No feedback saved yet."); return; }
        const csv = ["topic,text,timestamp", ...rows.map(r => `"${(r.topic || "").replace(/"/g, '""')}","${r.text.replace(/"/g, '""')}","${r.ts}"`)].join("\n");
        const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob); a.download = "travelo_feedback.csv";
        document.body.appendChild(a); a.click(); a.remove();
    });
})();

printBtn?.addEventListener("click", () => window.print());
scrollTopBtn?.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));

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

function attachObservers() {
    document.querySelectorAll(".faq-panel .faq-panel-content").forEach(inner => {
        ro.observe(inner);
    });
}

render();
attachObservers();
(function initSideNavJump(){
  const sideLinks = document.querySelectorAll('.side-link');

  function scrollToWithOffset(el, offset = 80){
    if(!el) return;
    const y = el.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({ top: y, behavior: 'smooth' });
  }

  sideLinks.forEach(link=>{
    link.addEventListener('click', (e)=>{
      e.preventDefault();
      const cat = (link.getAttribute('href') || '').replace('#','');
      if(!cat) return;

      const tab = Array.from(tabs).find(t => (t.dataset.category||'') === cat);
      if(tab){
        tabs.forEach(t=>t.classList.remove('is-active'));
        tab.classList.add('is-active');
      }
      state.category = cat;

      render();

      const firstItem = Array.from(document.querySelectorAll('.faq-item'))
        .find(it => it.querySelector('.badge')?.textContent === cat);

      if(firstItem){
        const header = firstItem.querySelector('.faq-header');
        const panel  = firstItem.querySelector('.faq-panel');
        setPanelOpen(panel, header, true);
        scrollToWithOffset(firstItem, 80); // عشان الهيدر الستكي ما يغطيه
      }else{

        scrollToWithOffset(document.querySelector('.faq-list'), 80);
      }

      document.querySelectorAll('.side-link').forEach(a=> a.classList.toggle('active', a === link));

      history.replaceState(null,'','#'+cat);
    });
  });
})();

expandAllBtn?.addEventListener("click", () => { expandAll(); attachObservers(); });
collapseAllBtn?.addEventListener("click", () => { collapseAll(); });
