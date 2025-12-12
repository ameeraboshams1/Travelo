// فلترة حسب الكاتيجوري + المودال + Book Trip/Hotel + أزرار اللوجين
document.addEventListener('DOMContentLoaded', () => {

  // ========== Tabs Filter ==========
  const tabs  = document.querySelectorAll('.category-btn');
  const cards = document.querySelectorAll('.destination-col');

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      tabs.forEach((t) => t.classList.remove('active'));
      tab.classList.add('active');

      const cat = tab.dataset.category;

      cards.forEach((col) => {
        const c = col.dataset.category;
        if (cat === 'all' || c === cat) {
          col.classList.remove('hidden', 'filtered-out');
        } else {
          col.classList.add('hidden', 'filtered-out');
        }
      });
    });
  });

  // ========== Modal ==========
  const modalOverlay = document.getElementById('destinationModal');
  const modalClose   = document.getElementById('destinationModalClose');

  const modalImg      = document.getElementById('modalDestinationImage');
  const modalTitle    = document.getElementById('modalDestinationTitle');
  const modalLocation = document.getElementById('modalDestinationLocation');
  const modalDesc     = document.getElementById('modalDestinationDesc');
  const modalVisitors = document.getElementById('modalVisitors');
  const modalSeason   = document.getElementById('modalSeason');
  const modalPrice    = document.getElementById('modalPrice');
  const modalRatingEl = document.getElementById('modalRating');

  const bookFlightBtn = document.getElementById('modalBookFlightBtn');
  const bookHotelBtn  = document.getElementById('modalBookHotelBtn');
  const bookPackageBtn = document.getElementById('modalBookPackageBtn');


  let currentDestId = null;

  function openModalFromButton(btn) {
    // ✅ نخزّن ID الوجهة (من الزر أو من الـ parent)
    const parentCol = btn.closest('.destination-col');
    currentDestId = btn.dataset.id || (parentCol ? parentCol.dataset.id : null) || null;

    const name     = btn.dataset.name || btn.dataset.city || 'Destination';
    const location = btn.dataset.location || '';
    const image    = btn.dataset.image || '';
    const desc     = btn.dataset.desc || '';
    const price    = btn.dataset.price || '';
    const rating   = btn.dataset.rating || '';

    const visitors = btn.dataset.visitors || '';
    const season   = btn.dataset.season || '';

    if (modalTitle)    modalTitle.textContent = name;
    if (modalLocation) modalLocation.textContent = location;
    if (modalImg)      { modalImg.src = image; modalImg.alt = name; }
    if (modalDesc)     modalDesc.textContent = desc;

    if (modalPrice && price) modalPrice.textContent = price;

    if (modalRatingEl && rating) modalRatingEl.textContent = `★ ${rating}`;

    if (modalVisitors) modalVisitors.textContent = visitors || '—';
    if (modalSeason)   modalSeason.textContent   = season   || '—';

    if (modalOverlay) {
      modalOverlay.classList.add('show');
      document.body.classList.add('no-scroll');
    }
  }

  function closeModal() {
    if (!modalOverlay) return;
    modalOverlay.classList.remove('show');
    document.body.classList.remove('no-scroll');
  }

  // فتح المودال
  document.querySelectorAll('.view-btn').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openModalFromButton(btn);
    });
  });

  // إغلاق
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

  // ✅ Book Trip -> fligths.php?destination_id=ID
  if (bookFlightBtn) {
    bookFlightBtn.addEventListener('click', () => {
      if (!currentDestId) return;
      window.location.href = `fligths.php?destination_id=${encodeURIComponent(currentDestId)}`;
    });
  }

  // ✅ Book Hotel -> hotel.php?destination_id=ID
  if (bookHotelBtn) {
    bookHotelBtn.addEventListener('click', () => {
      if (!currentDestId) return;
      window.location.href = `hotel.php?destination_id=${encodeURIComponent(currentDestId)}`;
    });
  }

  // ✅ Book Package -> packages.php?destination_id=ID
if (bookPackageBtn) {
  bookPackageBtn.addEventListener('click', () => {
    if (!currentDestId) return;
    window.location.href = `packages.php?destination_id=${encodeURIComponent(currentDestId)}`;
  });
}


  // ========== Login / Signup Spinner ==========
  const spinner = document.getElementById('spinner');
  const btnLogin  = document.getElementById('btnLogin');
  const btnSignup = document.getElementById('btnLogin1');

  if (btnLogin && spinner) {
    btnLogin.addEventListener('click', function (e) {
      e.preventDefault();
      spinner.classList.add('show');
      setTimeout(function () {
        window.location.href = 'login.html';
      }, 1000);
    });
  }

  if (btnSignup && spinner) {
    btnSignup.addEventListener('click', function (e) {
      e.preventDefault();
      spinner.classList.add('show');
      setTimeout(function () {
        window.location.href = 'signup.html';
      }, 1000);
    });
  }
});
