(() => {
  const API = "./API/faqs.php";

  // ===== required elements (section/table/button) =====
  const sec = document.getElementById("faqs");
  const tableEl = document.getElementById("faqsTable");
  const addBtn = document.getElementById("addFaq");
  if (!sec || !tableEl) return;

  // ===== shared modals in your dashboard =====
  const formModalEl = document.getElementById("formModal");
  const formTitleEl = document.getElementById("formModalTitle");
  const formBodyEl  = document.getElementById("formModalBody");
  const formEl      = document.getElementById("entityForm");
  const formSubmitBtn = document.getElementById("formSubmitBtn");

  const confirmModalEl = document.getElementById("confirmModal");
  const confirmBodyEl  = document.getElementById("confirmModalBody");
  const confirmYesBtn  = document.getElementById("confirmYes");

  const detailsModalEl = document.getElementById("detailsModal");
  const detailsTitleEl = document.getElementById("detailsTitle");
  const detailsBodyEl  = document.getElementById("detailsBody");

  // ===== bootstrap modal helpers =====
  const modalInstance = (el) => (el && window.bootstrap) ? bootstrap.Modal.getOrCreateInstance(el) : null;
  const formModal    = modalInstance(formModalEl);
  const confirmModal = modalInstance(confirmModalEl);
  const detailsModal = modalInstance(detailsModalEl);

  // ===== categories exactly like your ENUM (texts) =====
  const CATEGORIES = [
    "All",
    "Booking",
    "Payments",
    "Flights",
    "Hotels",
    "Changes & Refunds",
    "Travel Policies",
    "Support & Account"
  ];

  // ===== utils =====
  const esc = (s) => String(s ?? "")
    .replaceAll("&","&amp;").replaceAll("<","&lt;")
    .replaceAll(">","&gt;").replaceAll('"',"&quot;")
    .replaceAll("'","&#039;");

  const fmtDate = (s) => {
    if (!s) return "—";
    const t = String(s).replace("T"," ");
    return t.length >= 19 ? t.slice(0,19) : t;
  };

  const clip = (s, n=90) => {
    s = String(s ?? "").trim();
    if (s.length <= n) return s;
    return s.slice(0, n) + "…";
  };

  async function getJson(url){
    const res = await fetch(url + (url.includes("?") ? "&" : "?") + `_=${Date.now()}`, {
      credentials: "same-origin",
      cache: "no-store"
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) throw new Error(data.message || "Request failed");
    return data;
  }

  async function postJson(url, payload){
    const res = await fetch(url + (url.includes("?") ? "&" : "?") + `_=${Date.now()}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload || {}),
      credentials: "same-origin",
      cache: "no-store"
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) throw new Error(data.message || "Request failed");
    return data;
  }

  // ===== state =====
  let dt = null;
  let rows = [];
  let byId = new Map();
  let pendingDeleteId = null;

  // ===== build form HTML =====
  function formHtml(row){
    const r = row || {};
    const cat = String(r.category ?? "All");
    const q   = String(r.question ?? "");
    const a   = String(r.answer ?? "");
    const tags= String(r.tags ?? "");
    const pop = !!(+r.is_popular);
    const act = (r.is_active === undefined) ? true : !!(+r.is_active);
    const sort= (r.sort_order ?? 0);

    const opts = CATEGORIES.map(c =>
      `<option value="${esc(c)}" ${c===cat?'selected':''}>${esc(c)}</option>`
    ).join("");

    return `
      <input type="hidden" id="faqId" value="${esc(r.id ?? "")}">

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-bold">Category</label>
          <select class="form-select" id="faqCategory">${opts}</select>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-bold">Sort order</label>
          <input type="number" class="form-control" id="faqSort" value="${esc(sort)}">
        </div>

        <div class="col-md-3 d-flex align-items-end gap-2">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="faqPopular" ${pop ? "checked":""}>
            <label class="form-check-label fw-bold" for="faqPopular">Popular</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="faqActive" ${act ? "checked":""}>
            <label class="form-check-label fw-bold" for="faqActive">Active</label>
          </div>
        </div>

        <div class="col-12">
          <label class="form-label fw-bold">Question *</label>
          <input type="text" class="form-control" id="faqQuestion" value="${esc(q)}" placeholder="Type the question...">
        </div>

        <div class="col-12">
          <label class="form-label fw-bold">Answer *</label>
          <textarea class="form-control" id="faqAnswer" rows="6" placeholder="Type the answer...">${esc(a)}</textarea>
        </div>

        <div class="col-12">
          <label class="form-label fw-bold">Tags (optional)</label>
          <input type="text" class="form-control" id="faqTags" value="${esc(tags)}" placeholder="refund, baggage, check-in">
        </div>
      </div>
    `;
  }

  function openForm(mode, row){
    if (!formModal) return;

    formEl.dataset.entity = "faqs";
    formEl.dataset.mode = mode;

    formTitleEl.textContent = (mode === "edit") ? "Edit FAQ" : "Add FAQ";
    formSubmitBtn.textContent = "Save";
    formBodyEl.innerHTML = formHtml(row);

    formModal.show();
  }

  function openDetails(row){
    if (!detailsModal || !row) return;

    detailsTitleEl.textContent = "FAQ Details";
    detailsBodyEl.innerHTML = `
      <div class="p-3 rounded-4" style="background:var(--tbl-head-bg);border:1px solid var(--tbl-border);">
        <div class="mb-2"><span class="fw-bold">Category:</span> ${esc(row.category)}</div>
        <div class="mb-2"><span class="fw-bold">Question:</span><div class="mt-1">${esc(row.question)}</div></div>
        <div class="mb-2"><span class="fw-bold">Answer:</span><div class="mt-1" style="white-space:pre-wrap;line-height:1.75;">${esc(row.answer)}</div></div>
        <div class="mb-2"><span class="fw-bold">Tags:</span> ${esc(row.tags || "—")}</div>
        <div class="d-flex gap-2 flex-wrap mt-2">
          <span class="badge ${(+row.is_popular ? "bg-primary" : "bg-secondary")}">${+row.is_popular ? "Popular" : "Not popular"}</span>
          <span class="badge ${(+row.is_active ? "bg-success" : "bg-secondary")}">${+row.is_active ? "Active" : "Inactive"}</span>
          <span class="badge bg-light text-dark">Sort: ${esc(row.sort_order)}</span>
          <span class="badge bg-light text-dark">Updated: ${esc(fmtDate(row.updated_at))}</span>
        </div>
      </div>
    `;

    detailsModal.show();
  }

  // ===== DataTable =====
  function initTableOnce(){
    if (dt) return;

    dt = $(tableEl).DataTable({
      pageLength: 10,
      order: [[4, "asc"]], // sort_order
      autoWidth: false,
      columns: [
        { title: "Category" },
        { title: "Question" },
        { title: "Answer" },
        { title: "Popular" },
        { title: "Active" },
        { title: "Sort" },
        { title: "Updated" },
        { title: "Actions", orderable: false, searchable: false }
      ]
    });
  }

  function renderTable(){
    initTableOnce();

    const data = rows.map(r => {
      const ansShort = clip(r.answer, 90);

      const popBadge = +r.is_popular
        ? `<span class="badge bg-primary">Yes</span>`
        : `<span class="badge bg-secondary">No</span>`;

      const actBadge = +r.is_active
        ? `<span class="badge bg-success">Yes</span>`
        : `<span class="badge bg-secondary">No</span>`;

      const actions = `
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-sm btn-outline-secondary faq-view" data-id="${esc(r.id)}">
            <i class="bi bi-eye"></i>
          </button>
          <button class="btn btn-sm btn-primary faq-edit" data-id="${esc(r.id)}">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-sm btn-danger faq-del" data-id="${esc(r.id)}">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      `;

      return [
        esc(r.category),
        esc(clip(r.question, 80)),
        `<span title="${esc(r.answer)}">${esc(ansShort)}</span>`,
        popBadge,
        actBadge,
        esc(r.sort_order),
        esc(fmtDate(r.updated_at)),
        actions
      ];
    });

    dt.clear();
    dt.rows.add(data);
    dt.draw(false);
  }

  // ===== load data =====
  async function loadFaqs(){
    // Admin sees all by default
    const data = await getJson(`${API}?action=list&limit=500`);
    rows = Array.isArray(data.rows) ? data.rows : [];
    byId = new Map(rows.map(r => [String(r.id), r]));
    renderTable();
  }

  // expose for other scripts / navigation hook
  window.loadFaqs = async () => {
    try { await loadFaqs(); } catch(e){ console.error(e); }
  };

  // ===== events =====
  addBtn?.addEventListener("click", () => openForm("add", null));

  // table actions (delegation)
  tableEl.addEventListener("click", (e) => {
    const btn = e.target.closest("button");
    if (!btn) return;

    const id = btn.getAttribute("data-id");
    if (!id) return;

    const row = byId.get(String(id));
    if (!row) return;

    if (btn.classList.contains("faq-view")) {
      openDetails(row);
      return;
    }

    if (btn.classList.contains("faq-edit")) {
      openForm("edit", row);
      return;
    }

    if (btn.classList.contains("faq-del")) {
      pendingDeleteId = String(id);
      confirmBodyEl.textContent = "Delete this FAQ?";
      confirmModal?.show();
      return;
    }
  });

  // confirm delete
  confirmYesBtn?.addEventListener("click", async () => {
    if (!pendingDeleteId) return;
    const id = parseInt(pendingDeleteId, 10);
    pendingDeleteId = null;

    try {
      confirmYesBtn.disabled = true;
      await postJson(`${API}?action=delete`, { id });
      confirmModal?.hide();
      await loadFaqs();
    } catch (e) {
      console.error(e);
      alert(e.message || "Delete failed");
    } finally {
      confirmYesBtn.disabled = false;
    }
  });

  // submit form (only when entity = faqs)
  formEl?.addEventListener("submit", async (e) => {
    if (formEl.dataset.entity !== "faqs") return;
    e.preventDefault();

    const id = (document.getElementById("faqId")?.value || "").trim();
    const category = (document.getElementById("faqCategory")?.value || "All").trim();
    const question = (document.getElementById("faqQuestion")?.value || "").trim();
    const answer   = (document.getElementById("faqAnswer")?.value || "").trim();
    const tags     = (document.getElementById("faqTags")?.value || "").trim();
    const is_popular = document.getElementById("faqPopular")?.checked ? 1 : 0;
    const is_active  = document.getElementById("faqActive")?.checked ? 1 : 0;
    const sort_order = parseInt(document.getElementById("faqSort")?.value || "0", 10) || 0;

    if (!question || !answer) {
      alert("Question and Answer are required.");
      return;
    }

    const payload = { category, question, answer, tags, is_popular, is_active, sort_order };

    try {
      formSubmitBtn.disabled = true;

      if (id) {
        await postJson(`${API}?action=update`, { id: parseInt(id, 10), ...payload });
      } else {
        await postJson(`${API}?action=create`, payload);
      }

      formModal?.hide();
      await loadFaqs();
    } catch (err) {
      console.error(err);
      alert(err.message || "Save failed");
    } finally {
      formSubmitBtn.disabled = false;
    }
  });

  // ===== auto init when section opened =====
  function isFaqsActive(){
    return sec.classList.contains("active");
  }

  async function onFaqsShown(){
    try {
      initTableOnce();
      await loadFaqs();
    } catch (e) {
      console.error(e);
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    if (isFaqsActive()) onFaqsShown();
  });

  // when clicking sidebar links
  document.addEventListener("click", (e) => {
    const a = e.target.closest("a[data-page]");
    if (!a) return;
    if ((a.getAttribute("data-page") || "") === "faqs") {
      setTimeout(onFaqsShown, 0);
    }
  });

})();
