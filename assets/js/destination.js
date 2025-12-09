// فلترة حسب الكاتيجوري + المودال + أزرار اللوجين

document.addEventListener('DOMContentLoaded', () => {
  // ========== Tabs Filter ==========
  const tabs = document.querySelectorAll('.category-btn');
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
  const modalClose = document.getElementById('destinationModalClose');

  const modalImg = document.getElementById('modalDestinationImage');
  const modalTitle = document.getElementById('modalDestinationTitle');
  const modalLocation = document.getElementById('modalDestinationLocation');
  const modalDesc = document.getElementById('modalDestinationDesc');
  const modalVisitors = document.getElementById('modalVisitors');
  const modalSeason = document.getElementById('modalSeason');
  const modalPrice = document.getElementById('modalPrice');
  const modalRating = document.getElementById('modalRating');

  function openModalFromButton(btn) {
    const name = btn.dataset.name || btn.dataset.city || 'Destination';
    const location = btn.dataset.location || '';
    const image = btn.dataset.image || '';
    const desc = btn.dataset.desc || '';
    const price = btn.dataset.price || '';
    const rating = btn.dataset.rating || '';

    // لو حابة تعملي visitors / season حسب المدينة ممكن تضيفيها بالداتا
    const visitors = btn.dataset.visitors || '';
    const season = btn.dataset.season || '';

    modalTitle.textContent = name;
    modalLocation.textContent = location;
    modalImg.src = image;
    modalDesc.textContent = desc;

    if (price) modalPrice.textContent = price;
    if (rating) modalRating.textContent = `★ ${rating}`;
    if (visitors) modalVisitors.textContent = visitors;
    if (season) modalSeason.textContent = season;

    modalOverlay.classList.add('show');
    document.body.classList.add('no-scroll');
  }

  function closeModal() {
    modalOverlay.classList.remove('show');
    document.body.classList.remove('no-scroll');
  }

  const viewButtons = document.querySelectorAll('.view-btn');
  viewButtons.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openModalFromButton(btn);
    });
  });

  if (modalClose) {
    modalClose.addEventListener('click', () => {
      closeModal();
    });
  }

  if (modalOverlay) {
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) {
        closeModal();
      }
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modalOverlay.classList.contains('show')) {
      closeModal();
    }
  });
});

// ========== Login / Signup Spinner ==========
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('btnLogin');
  const spinner = document.getElementById('spinner');

  if (!btn || !spinner) return;

  btn.addEventListener('click', function (e) {
    e.preventDefault();
    spinner.classList.add('show');
    setTimeout(function () {
      window.location.href = 'login.html';
    }, 1000);
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('btnLogin1');
  const spinner = document.getElementById('spinner');

  if (!btn || !spinner) return;

  btn.addEventListener('click', function (e) {
    e.preventDefault();
    spinner.classList.add('show');
    setTimeout(function () {
      window.location.href = 'signup.html';
    }, 1000);
  });
});
