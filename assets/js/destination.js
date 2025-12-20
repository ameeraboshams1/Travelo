/* =========  SQUARE CARDS  +  PAGINATION  ========= */
const ITEMS_PER_PAGE = 8;
let   currentPage    = 1;

/* ----------  utilities  ---------- */
const $ = s => document.querySelector(s);
const $$ = s => document.querySelectorAll(s);

function getVisibleCards(){
  return Array.from($$('.destination-col'))
              .filter(c => !c.classList.contains('hidden'));
}

function renderPagination() {
    if (!this.paginationContainer) return;

    this.paginationContainer.innerHTML = "";

    if (this.totalPages <= 1) {
      const single = document.createElement("div");
      single.className = "page-link active";
      single.textContent = "1";
      single.dataset.type = "page";
      single.dataset.page = "1";
      this.paginationContainer.appendChild(single);
      return;
    }

    const prev = document.createElement("div");
    prev.className = "page-link";
    prev.innerHTML = '<i class="fa-solid fa-angle-left"></i>';
    prev.dataset.type = "prev";
    if (this.currentPage === 1) prev.classList.add("disabled");
    this.paginationContainer.appendChild(prev);

    for (let p = 1; p <= this.totalPages; p++) {
      const pageEl = document.createElement("div");
      pageEl.className = "page-link";
      if (p === this.currentPage) pageEl.classList.add("active");
      pageEl.textContent = String(p);
      pageEl.dataset.type = "page";
      pageEl.dataset.page = String(p);
      this.paginationContainer.appendChild(pageEl);
    }

    const next = document.createElement("div");
    next.className = "page-link";
    next.innerHTML = '<i class="fa-solid fa-angle-right"></i>';
    next.dataset.type = "next";
    if (this.currentPage === this.totalPages) next.classList.add("disabled");
    this.paginationContainer.appendChild(next);
  }

 

/* ----------  category filter  ---------- */
function applyFilter(){
  const active = $('.category-btn.active');
  const cat    = active ? active.dataset.category : 'all';
  $$('.destination-col').forEach(c=>{
    const t = c.dataset.category;
    c.classList.toggle('hidden', cat!=='all' && t!==cat);
  });
  currentPage = 1;
  renderPage();
}
$$('.category-btn').forEach(t=>{
  t.addEventListener('click',()=>{
    $$('.category-btn').forEach(x=>x.classList.remove('active'));
    t.classList.add('active');
    applyFilter();
  });
});

/* ----------  run once on load  ---------- */
document.addEventListener('DOMContentLoaded',()=>{
  /* force big square */
  $$('.destination-col').forEach(col=>{
    col.style.aspectRatio = '1/1';          // make it square
    col.style.minHeight   = '320px';        // enforce minimum size
  });
  applyFilter();   // draws pagination for the first time
});

/* =========================================================
   everything below is 100 % identical to your old file
   (modal, booking buttons, spinner, user-menu …)
   ========================================================= */

/* ----------  modal  ---------- */
const modalOverlay   = $('#destinationModal');
const modalClose     = $('#destinationModalClose');
const modalImg       = $('#modalDestinationImage');
const modalTitle     = $('#modalDestinationTitle');
const modalLocation  = $('#modalDestinationLocation');
const modalDesc      = $('#modalDestinationDesc');
const modalPrice     = $('#modalPrice');
const modalVisitors  = $('#modalVisitors');
const modalSeason    = $('#modalSeason');
const modalRatingEl  = $('#modalRating');

const bookFlightBtn  = $('#modalBookFlightBtn');
const bookHotelBtn   = $('#modalBookHotelBtn');
const bookPackageBtn = $('#modalBookPackageBtn');

let currentDestId = null;

function openModalFromButton(btn){
  const parent = btn.closest('.destination-col');
  currentDestId = btn.dataset.id || (parent ? parent.dataset.id : null);

  const name     = btn.dataset.name     || 'Destination';
  const location = btn.dataset.location || '';
  const image    = btn.dataset.image    || '';
  const desc     = btn.dataset.desc     || '';
  const price    = btn.dataset.price    || '';
  const rating   = btn.dataset.rating   || '5.0';
  const visitors = btn.dataset.visitors || '—';
  const season   = btn.dataset.season   || '—';

  modalTitle.textContent    = name;
  modalLocation.textContent = location;
  modalImg.src              = image;
  modalImg.alt              = name;
  modalDesc.textContent     = desc;
  modalPrice.textContent    = price;
  if(modalRatingEl) modalRatingEl.textContent = `★ ${rating}`;
  modalVisitors.textContent = visitors;
  modalSeason.textContent   = season;

  modalOverlay.classList.add('show');
  document.body.classList.add('no-scroll');
}
function closeModal(){
  modalOverlay.classList.remove('show');
  document.body.classList.remove('no-scroll');
}
$$('.view-btn').forEach(btn=>
  btn.addEventListener('click',e=>{
    e.preventDefault();
    openModalFromButton(btn);
  })
);
modalClose.addEventListener('click',closeModal);
modalOverlay.addEventListener('click',e=>{ if(e.target===modalOverlay) closeModal(); });
document.addEventListener('keydown',e=>{
  if(e.key==='Escape' && modalOverlay.classList.contains('show')) closeModal();
});

/* ----------  booking buttons  ---------- */
bookFlightBtn.addEventListener('click',()=>{
  if(!currentDestId) return;
  location.href = `fligths.php?destination_id=${encodeURIComponent(currentDestId)}`;
});
bookHotelBtn.addEventListener('click',()=>{
  if(!currentDestId) return;
  location.href = `hotel.php?destination_id=${encodeURIComponent(currentDestId)}`;
});
bookPackageBtn.addEventListener('click',()=>{
  if(!currentDestId) return;
  location.href = `packages.php?destination_id=${encodeURIComponent(currentDestId)}`;
});

/* ----------  spinner for login / signup  ---------- */
const spinner = $('#spinner');
['btnLogin','btnLogin1'].forEach(id=>{
  const b = $(`#${id}`);
  if(!b) return;
  b.addEventListener('click',e=>{
    e.preventDefault();
    spinner.classList.add('show');
    setTimeout(()=> location.href = id==='btnLogin'?'login.html':'signup.html', 600);
  });
});

/* ----------  user-menu toggle  ---------- */
const userToggle = $('#userMenuToggle');
const userMenu   = $('#userMenu');
if(userToggle){
  userToggle.addEventListener('click',()=> userMenu.classList.toggle('show'));
  document.addEventListener('click',e=>{
    if(!e.target.closest('.nav-user')) userMenu.classList.remove('show');
  });
}