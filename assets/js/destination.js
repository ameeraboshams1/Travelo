/* ===========================
   destination.js (Server-side filter)
   - NO JS filtering
   - NO JS pagination
   - Keeps: modal, booking buttons, spinner, user-menu
   =========================== */

const $  = (s) => document.querySelector(s);
const $$ = (s) => document.querySelectorAll(s);

document.addEventListener("DOMContentLoaded", () => {
  /* (Optional) keep your square card look */
  $$(".destination-col").forEach(col => {
    col.style.aspectRatio = "1/1";
    col.style.minHeight = "320px";
  });

  /* NOTE:
     Category filter is server-side now.
     Buttons/links in PHP should be <a href="?category=...&page=1">.
     So we do NOTHING here for filters.
  */
});

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

  if (modalTitle)    modalTitle.textContent = name;
  if (modalLocation) modalLocation.textContent = location;

  if (modalImg) {
    modalImg.src = image;
    modalImg.alt = name;
  }

  if (modalDesc) modalDesc.textContent = desc;
  if (modalPrice) modalPrice.textContent = price;

  if (modalRatingEl) modalRatingEl.textContent = `★ ${rating}`;
  if (modalVisitors) modalVisitors.textContent = visitors;
  if (modalSeason) modalSeason.textContent = season;

  if (modalOverlay) {
    modalOverlay.classList.add('show');
    document.body.classList.add('no-scroll');
  }
}

function closeModal(){
  if (!modalOverlay) return;
  modalOverlay.classList.remove('show');
  document.body.classList.remove('no-scroll');
}

$$('.view-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    openModalFromButton(btn);
  });
});

if (modalClose) modalClose.addEventListener('click', closeModal);

if (modalOverlay) {
  modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) closeModal();
  });
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && modalOverlay && modalOverlay.classList.contains('show')) {
    closeModal();
  }
});

/* ----------  booking buttons  ---------- */
if (bookFlightBtn) {
  bookFlightBtn.addEventListener('click', () => {
    if(!currentDestId) return;
    location.href = `fligths.php?destination_id=${encodeURIComponent(currentDestId)}`;
  });
}

if (bookHotelBtn) {
  bookHotelBtn.addEventListener('click', () => {
    if(!currentDestId) return;
    location.href = `hotel.php?destination_id=${encodeURIComponent(currentDestId)}`;
  });
}

if (bookPackageBtn) {
  bookPackageBtn.addEventListener('click', () => {
    if(!currentDestId) return;
    location.href = `packages.php?destination_id=${encodeURIComponent(currentDestId)}`;
  });
}

/* ----------  spinner for login / signup  ---------- */
const spinner = $('#spinner');
['btnLogin','btnLogin1'].forEach(id => {
  const b = $(`#${id}`);
  if(!b || !spinner) return;

  b.addEventListener('click', (e) => {
    e.preventDefault();
    spinner.classList.add('show');
    setTimeout(() => {
      location.href = id === 'btnLogin' ? 'login.html' : 'signup.html';
    }, 600);
  });
});

/* ----------  user-menu toggle  ---------- */
const userToggle = $('#userMenuToggle');
const userMenu   = $('#userMenu');

if(userToggle && userMenu){
  userToggle.addEventListener('click', () => userMenu.classList.toggle('show'));

  document.addEventListener('click', (e) => {
    if(!e.target.closest('.nav-user')) userMenu.classList.remove('show');
  });
}
