(() => {
  const API = './API/blogs_admin.php';

  const statusSel = document.getElementById('blogsStatusFilter');
  const searchInp = document.getElementById('blogsSearch');
  const refreshBtn = document.getElementById('refreshBlogs');
  const listEl = document.getElementById('blogsList');
  const addBtn = document.getElementById('addBlogBtn'); // بدنا نخفيه (عشان View/Delete بس)

  const detailsModalEl = document.getElementById('blogDetailsModal');
  const detailsBody = document.getElementById('blogDetailsBody');

  const confirmModalEl = document.getElementById('confirmModal');
  const confirmBody = document.getElementById('confirmModalBody');
  const confirmYes = document.getElementById('confirmYes');

  if (!listEl || !statusSel || !searchInp || !refreshBtn) return;

  if (addBtn) addBtn.style.display = 'none'; // حسب طلبك: View + Delete فقط

  const esc = (s) => String(s ?? '')
    .replaceAll('&','&amp;').replaceAll('<','&lt;')
    .replaceAll('>','&gt;').replaceAll('"','&quot;')
    .replaceAll("'","&#039;");

  const fmtDate = (s) => {
    if (!s) return '—';
    return String(s).replace('T',' ').slice(0, 10);
  };

  function modalInstance(el){
    if (!el || !window.bootstrap) return null;
    return bootstrap.Modal.getOrCreateInstance(el);
  }

  async function getJson(url){
    const res = await fetch(url + (url.includes('?') ? '&' : '?') + `_=${Date.now()}`, {
      credentials: 'same-origin',
      cache: 'no-store'
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) throw new Error(data.message || 'Request failed');
    return data;
  }

  async function postJson(url, payload){
    const res = await fetch(url + (url.includes('?') ? '&' : '?') + `_=${Date.now()}`, {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify(payload || {}),
      credentials: 'same-origin',
      cache: 'no-store'
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) throw new Error(data.message || 'Request failed');
    return data;
  }

  function statusLabel(st){
    st = String(st || 'published');
    if (st === 'draft') return 'Draft';
    return 'Published';
  }

  let rows = [];
  let pendingDeleteId = null;

  function render(list){
    if (!Array.isArray(list) || !list.length){
      listEl.innerHTML = `<div class="text-muted small">No blogs found.</div>`;
      return;
    }

    listEl.innerHTML = list.map(r => {
      const id = esc(r.id);
      const title = esc(r.title || '—');
      const excerpt = esc(r.excerpt || '');
      const cat = esc(r.category || 'General');
      const st = String(r.status || 'published');
      const created = fmtDate(r.created_at);
      const views = Number(r.views || 0).toLocaleString();

      const img = r.cover_image && String(r.cover_image).trim()
        ? String(r.cover_image).trim()
        : 'https://images.unsplash.com/photo-1520975916090-3105956dac38?auto=format&fit=crop&w=1200&q=80';

      return `
        <div class="blog-card" data-id="${id}">
          <div class="blog-cover-wrap">
            <img class="blog-cover" src="${esc(img)}" alt="${title}">
            <div class="blog-badges">
              <span class="badge-pill">${cat}</span>
              <span class="badge-pill badge-status">${esc(statusLabel(st))}</span>
            </div>
          </div>

          <div class="blog-body">
            <h6 class="blog-title">${title}</h6>
            <p class="blog-excerpt">${excerpt || '&nbsp;'}</p>

            <div class="blog-meta">
              <div class="m"><i class="bi bi-calendar3"></i> ${esc(created)}</div>
              <div class="m"><i class="bi bi-eye"></i> ${esc(views)}</div>
            </div>
          </div>

          <div class="blog-actions">
            <button class="btn btn-sm btn-viewdetails btn-view" type="button">
              <i class="bi bi-eye me-1"></i>View
            </button>
            <button class="btn btn-sm btn-danger btn-del" type="button">
              <i class="bi bi-trash me-1"></i>Delete
            </button>
          </div>
        </div>
      `;
    }).join('');
  }

  async function load(){
    const status = (statusSel.value || '').trim(); // '' | published | draft
    const q = (searchInp.value || '').trim();

    refreshBtn.disabled = true;
    try{
      const url = `${API}?action=list&status=${encodeURIComponent(status)}&q=${encodeURIComponent(q)}&limit=400`;
      const data = await getJson(url);
      rows = data.rows || [];
      render(rows);
    }catch(e){
      listEl.innerHTML = `<div class="text-danger small">${esc(e.message || 'Failed')}</div>`;
    }finally{
      refreshBtn.disabled = false;
    }
  }

  async function openDetails(id){
    if (!detailsBody) return;

    detailsBody.innerHTML = `<div class="text-muted small">Loading...</div>`;
    modalInstance(detailsModalEl)?.show();

    try{
      const data = await getJson(`${API}?action=get&id=${encodeURIComponent(id)}`);
      const r = data.row || {};

      const title = esc(r.title || '—');
      const excerpt = esc(r.excerpt || '');
      const cat = esc(r.category || 'General');
      const st = esc(statusLabel(r.status || 'published'));
      const created = fmtDate(r.created_at);
      const updated = fmtDate(r.updated_at);
      const views = Number(r.views || 0).toLocaleString();

      const img = r.cover_image && String(r.cover_image).trim()
        ? String(r.cover_image).trim()
        : 'https://images.unsplash.com/photo-1520975916090-3105956dac38?auto=format&fit=crop&w=1200&q=80';

      const author = esc(r.author_name || r.author_username || '—');
      const slug = esc(r.slug || '—');

      // content قد يكون HTML (مقبول للأدمن)
      const contentHtml = (r.content ?? '').toString();

      detailsBody.innerHTML = `
        <div class="mb-3" style="border-radius:14px;overflow:hidden;border:1px solid var(--tbl-border);">
          <img src="${esc(img)}" alt="${title}" style="width:100%;height:260px;object-fit:cover;">
        </div>

        <div class="d-flex flex-wrap gap-2 mb-2">
          <span class="badge rounded-pill text-bg-light" style="border:1px solid var(--tbl-border);">${cat}</span>
          <span class="badge rounded-pill text-bg-light" style="border:1px solid var(--tbl-border);">${st}</span>
          <span class="badge rounded-pill text-bg-light" style="border:1px solid var(--tbl-border);"><i class="bi bi-eye me-1"></i>${esc(views)}</span>
        </div>

        <h5 class="fw-bold mb-1">${title}</h5>
        <div class="text-muted small mb-2">
          <i class="bi bi-person me-1"></i>${author}
          <span class="mx-2">•</span>
          <i class="bi bi-calendar3 me-1"></i>${esc(created)}
          <span class="mx-2">•</span>
          <i class="bi bi-arrow-repeat me-1"></i>${esc(updated)}
        </div>

        ${excerpt ? `<div class="p-3 rounded-4 mb-3" style="background:var(--tbl-head-bg);border:1px solid var(--tbl-border);">${excerpt}</div>` : ''}

        <div class="mb-2 small text-muted"><i class="bi bi-link-45deg me-1"></i>Slug: ${slug}</div>

        <div class="p-3 rounded-4" style="background:var(--tbl-bg);border:1px solid var(--tbl-border);">
          ${contentHtml || '<div class="text-muted small">No content.</div>'}
        </div>
      `;
    }catch(e){
      detailsBody.innerHTML = `<div class="text-danger small">${esc(e.message || 'Failed')}</div>`;
    }
  }

  function askDelete(id, title){
    pendingDeleteId = id;
    if (confirmBody) confirmBody.innerHTML = `Delete this blog?<br><b>${esc(title || '')}</b>`;
    modalInstance(confirmModalEl)?.show();
  }

  async function doDelete(){
    if (!pendingDeleteId) return;

    confirmYes.disabled = true;
    try{
      await postJson(`${API}?action=delete`, { id: parseInt(pendingDeleteId, 10) });
      modalInstance(confirmModalEl)?.hide();
      pendingDeleteId = null;
      await load();
    }catch(e){
      if (confirmBody) confirmBody.innerHTML = `<span class="text-danger">${esc(e.message || 'Delete failed')}</span>`;
    }finally{
      confirmYes.disabled = false;
    }
  }

  // events
  refreshBtn.addEventListener('click', load);
  statusSel.addEventListener('change', load);

  let t = null;
  searchInp.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(load, 300);
  });

  listEl.addEventListener('click', (e) => {
    const card = e.target.closest('.blog-card');
    if (!card) return;

    const id = card.getAttribute('data-id');
    const row = rows.find(x => String(x.id) === String(id));
    const title = row?.title || 'Blog';

    if (e.target.closest('.btn-view')) {
      openDetails(id);
      return;
    }

    if (e.target.closest('.btn-del')) {
      askDelete(id, title);
      return;
    }

    // click anywhere opens details
    openDetails(id);
  });

  confirmYes?.addEventListener('click', doDelete);

  // load only when blogs section opened (and once at start)
  function isBlogsSectionActive(){
    const sec = document.getElementById('blogs');
    if (!sec) return true;
    return sec.classList.contains('active');
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (isBlogsSectionActive()) load();
  });

  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[data-page]');
    if (!a) return;
    if ((a.getAttribute('data-page') || '') === 'blogs') {
      setTimeout(load, 0);
    }
  });
})();
