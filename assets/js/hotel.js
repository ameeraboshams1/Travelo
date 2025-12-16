document.addEventListener('DOMContentLoaded', () => {
  const hotelCards = Array.from(document.querySelectorAll('.hotel-card'));
  const hotelsList = document.querySelector('.hotels-list');
  const sortTabs   = document.querySelectorAll('.toolbar-tab');

  // ========== PRICE SLIDER ==========
  const sliderTrack   = document.querySelector('.slider-track');
  const sliderFill    = document.querySelector('.slider-fill');
  const leftThumb     = document.querySelector('.slider-thumb.left');
  const rightThumb    = document.querySelector('.slider-thumb.right');
  const minValueLabel = document.querySelector('.slider-values span:first-child');
  const maxValueLabel = document.querySelector('.slider-values span:last-child');
  const priceSummary  = document.getElementById('priceSummary');

  // Popular filters (checkboxes existing in your UI)
  const moreFiltersBtn   = document.getElementById('moreFiltersBtn');
  const filtersExtra     = document.getElementById('filtersExtra');
  const filterCheckboxes = document.querySelectorAll('.filter-checkbox input[type="checkbox"]');

  // ========== LOOKING FOR (ALWAYS VISIBLE) ==========
  const lookingForList  = document.getElementById('lookingForList');   // container that has checkboxes
  const lookingForClear = document.getElementById('lookingForClear');  // clear button
  const lookingForCount = document.getElementById('lookingForCount');  // count badge (optional)
  const lookingForChecks = lookingForList
    ? Array.from(lookingForList.querySelectorAll('input[type="checkbox"]'))
    : [];

  // ========== CATEGORY (ALWAYS VISIBLE - RADIO) ==========
  const categoryList   = document.getElementById('categoryList'); // container radios
  const categoryRadios = categoryList
    ? Array.from(categoryList.querySelectorAll('input[type="radio"][name="categoryTier"]'))
    : [];

  let selectedLookingFor = new Set(); // deals, wifi, ...
  let selectedCategory   = 'any';     // any | budget | mid | luxury

  // ========= BOOKING / BUTTONS =========
  const viewDetailsBtns = document.querySelectorAll('.hotel-card .btn-light');
  const bookNowBtns     = document.querySelectorAll('.hotel-card .btn-primary');
  const mapViewBtns     = document.querySelectorAll('.map-view');

  const bookingBaseUrl = 'booking.php';
  let modalCurrentCard = null;

  const qs = new URLSearchParams(window.location.search);
  const destinationFilterId = qs.get('destination_id') ? String(qs.get('destination_id')) : null;

  // ================== HELPERS ==================
  function formatPrice(num) {
    const n = Number(num) || 0;
    return `$${n.toFixed(2)}`;
  }

  function parseAmenities(card) {
    const raw = (card.dataset.amenities || '')
      .split('|')
      .map(s => s.trim().toLowerCase())
      .filter(Boolean);
    return new Set(raw);
  }

  function matchLookingForKey(key, card, amenSet) {
    const discount = parseInt(card.dataset.discount || '0', 10) || 0;

    if (key === 'deals') return discount > 0;
    if (key === 'pay_at_hotel') return amenSet.has('pay hotel available');
    if (key === 'breakfast') return amenSet.has('free breakfast');
    if (key === 'wifi') return amenSet.has('wifi');
    if (key === 'parking') return amenSet.has('parking');
    if (key === 'airport_shuttle') return amenSet.has('airport shuttle');
    if (key === 'couple_friendly') return amenSet.has('couple friendly');
    if (key === 'pet_friendly') return amenSet.has('pet friendly');
    if (key === 'view') return (amenSet.has('sea view') || amenSet.has('city view'));
    if (key === 'cctv') return (amenSet.has('cctv cameras') || amenSet.has('cctv'));

    return true;
  }

  function computeTierBounds(minP, maxP) {
    const span = Math.max(0, maxP - minP);
    const t1 = minP + span * 0.33;
    const t2 = minP + span * 0.66;
    return { t1, t2 };
  }

  function updateLookingForUI() {
    const n = selectedLookingFor.size;
    if (lookingForCount) {
      lookingForCount.textContent = String(n);
      lookingForCount.style.display = n ? '' : 'none';
    }
  }

  // ================== FILTER + SORT STATE ==================
  let currentSort = 'popular';

  const prices = hotelCards.map(card => parseFloat(card.dataset.price) || 0);
  const globalMinPrice = prices.length ? Math.min(...prices) : 0;
  const globalMaxPrice = prices.length ? Math.max(...prices) : 0;

  let currentMinPrice = globalMinPrice;
  let currentMaxPrice = globalMaxPrice;

  let leftPercent     = 0;
  let rightPercent    = 100;
  let isDraggingLeft  = false;
  let isDraggingRight = false;

  function computePriceFromPercent(percent) {
    return globalMinPrice + (globalMaxPrice - globalMinPrice) * (percent / 100);
  }

  function updateSliderLabels() {
    if (minValueLabel) minValueLabel.textContent = formatPrice(currentMinPrice);
    if (maxValueLabel) maxValueLabel.textContent = formatPrice(currentMaxPrice);
    if (priceSummary)  priceSummary.textContent  = `Up to ${formatPrice(currentMaxPrice)}`;
  }

  function updateSliderUI() {
    if (!sliderTrack || !leftThumb || !rightThumb || !sliderFill) return;

    leftThumb.style.left  = `${leftPercent}%`;
    rightThumb.style.left = `${rightPercent}%`;

    sliderFill.style.left  = `${leftPercent}%`;
    sliderFill.style.width = `${rightPercent - leftPercent}%`;

    currentMinPrice = computePriceFromPercent(leftPercent);
    currentMaxPrice = computePriceFromPercent(rightPercent);
    updateSliderLabels();
  }

  function compareCards(a, b) {
    if (currentSort === 'rating') {
      const ra = parseFloat(a.dataset.rating) || 0;
      const rb = parseFloat(b.dataset.rating) || 0;
      return rb - ra;
    }

    if (currentSort === 'price') {
      const pa = parseFloat(a.dataset.price) || 0;
      const pb = parseFloat(b.dataset.price) || 0;
      return pa - pb;
    }

    const ia = parseInt(a.dataset.index || '0', 10);
    const ib = parseInt(b.dataset.index || '0', 10);
    return ia - ib;
  }

  function getSelectedPopularFilters() {
    return Array.from(filterCheckboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.value.trim().toLowerCase());
  }

  function applyAllFilters() {
    const selectedPopular = getSelectedPopularFilters();
    const hasPopular      = selectedPopular.length > 0;

    const bounds = computeTierBounds(globalMinPrice, globalMaxPrice);

    // tier labels (optional)
    const b1 = formatPrice(bounds.t1);
    const b2 = formatPrice(bounds.t2);
    const elB = document.getElementById('tierBudget');
    const elM = document.getElementById('tierMid');
    const elL = document.getElementById('tierLuxury');
    if (elB) elB.textContent = `Budget (≤ ${b1})`;
    if (elM) elM.textContent = `Mid-range (${b1} – ${b2})`;
    if (elL) elL.textContent = `Luxury (≥ ${b2})`;

    const visibleCards = [];

    hotelCards.forEach(card => {
      const price = parseFloat(card.dataset.price) || 0;

      // destination filter
      if (destinationFilterId) {
        const cardDestId = String(card.dataset.destinationId || '');
        if (cardDestId !== destinationFilterId) {
          card.style.display = 'none';
          return;
        }
      }

      // price range
      let match = price >= currentMinPrice && price <= currentMaxPrice;
      if (!match) { card.style.display = 'none'; return; }

      // category tier (based on price bounds)
      if (selectedCategory !== 'any') {
        if (selectedCategory === 'budget') match = price <= bounds.t1;
        else if (selectedCategory === 'mid') match = price > bounds.t1 && price <= bounds.t2;
        else if (selectedCategory === 'luxury') match = price >= bounds.t2;
        if (!match) { card.style.display = 'none'; return; }
      }

      const amenSet = parseAmenities(card);

      // popular filters (AND)
      if (hasPopular) {
        const ok = selectedPopular.every(v => amenSet.has(v));
        if (!ok) { card.style.display = 'none'; return; }
      }

      // looking for (AND)
      if (selectedLookingFor.size) {
        const ok = Array.from(selectedLookingFor).every(key =>
          matchLookingForKey(key, card, amenSet)
        );
        if (!ok) { card.style.display = 'none'; return; }
      }

      card.style.display = '';
      visibleCards.push(card);
    });

    visibleCards.sort(compareCards);

    if (!hotelsList) return;
    hotelsList.innerHTML = '';
    visibleCards.forEach(card => hotelsList.appendChild(card));

    // keep hidden at end (optional)
    hotelCards.forEach(card => {
      if (card.style.display === 'none') hotelsList.appendChild(card);
    });
  }

  // ================== EVENTS ==================

  // Sort tabs
  sortTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      sortTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      currentSort = tab.dataset.sort || 'popular';
      applyAllFilters();
    });
  });

  // Looking For checkboxes
  if (lookingForChecks.length) {
    lookingForChecks.forEach(cb => {
      cb.addEventListener('change', () => {
        cb.checked ? selectedLookingFor.add(cb.value) : selectedLookingFor.delete(cb.value);
        updateLookingForUI();
        applyAllFilters();
      });
    });
  }

  // Looking For clear
  if (lookingForClear) {
    lookingForClear.addEventListener('click', (e) => {
      e.preventDefault();
      selectedLookingFor.clear();
      lookingForChecks.forEach(cb => cb.checked = false);
      updateLookingForUI();
      applyAllFilters();
    });
  }

  // Category radios
  if (categoryRadios.length) {
    // set initial from checked radio
    const init = categoryRadios.find(r => r.checked);
    if (init) selectedCategory = init.value || 'any';

    categoryRadios.forEach(r => {
      r.addEventListener('change', () => {
        if (!r.checked) return;
        selectedCategory = r.value || 'any';
        applyAllFilters();
      });
    });
  }

  // Popular filter checkboxes
  filterCheckboxes.forEach(cb => cb.addEventListener('change', applyAllFilters));

  // Price slider init
  if (sliderTrack && sliderFill && leftThumb && rightThumb && prices.length) {
    leftPercent  = 0;
    rightPercent = 100;
    updateSliderUI();

    function handleMove(clientX) {
      const rect = sliderTrack.getBoundingClientRect();
      const x    = Math.max(0, Math.min(clientX - rect.left, rect.width));
      const pct  = (x / rect.width) * 100;

      if (isDraggingLeft) {
        leftPercent = Math.max(0, Math.min(pct, rightPercent - 5));
      } else if (isDraggingRight) {
        rightPercent = Math.min(100, Math.max(pct, leftPercent + 5));
      }
      updateSliderUI();
      applyAllFilters();
    }

    leftThumb.addEventListener('mousedown', e => { isDraggingLeft = true; e.preventDefault(); });
    rightThumb.addEventListener('mousedown', e => { isDraggingRight = true; e.preventDefault(); });

    document.addEventListener('mousemove', e => {
      if (!isDraggingLeft && !isDraggingRight) return;
      handleMove(e.clientX);
    });
    document.addEventListener('mouseup', () => { isDraggingLeft = false; isDraggingRight = false; });

    leftThumb.addEventListener('touchstart', e => { isDraggingLeft = true; e.preventDefault(); }, { passive:false });
    rightThumb.addEventListener('touchstart', e => { isDraggingRight = true; e.preventDefault(); }, { passive:false });

    document.addEventListener('touchmove', e => {
      if (!isDraggingLeft && !isDraggingRight) return;
      handleMove(e.touches[0].clientX);
    }, { passive:false });
    document.addEventListener('touchend', () => { isDraggingLeft = false; isDraggingRight = false; });
  }

  // More filters toggle
  if (moreFiltersBtn && filtersExtra) {
    let isExpanded = false;
    filtersExtra.style.display = 'none';

    moreFiltersBtn.addEventListener('click', () => {
      isExpanded = !isExpanded;
      filtersExtra.style.display = isExpanded ? 'block' : 'none';
      moreFiltersBtn.innerHTML = isExpanded
        ? 'Less <span class="icon-placeholder tiny"></span>'
        : 'More <span class="icon-placeholder tiny"></span>';
    });
  }

  // ========= BOOK NOW =========
  function buildBookingParams(card, nights) {
    if (!card) return '';
    const hotelId   = card.dataset.hotelId || '';
    const hotelName = card.querySelector('.hotel-name')?.textContent.trim() || '';
    const hotelLocation = card.dataset.location || card.querySelector('.hotel-location')?.textContent.trim() || '';
    const cityCountry = card.dataset.cityCountry || '';

    const basePerNight = parseFloat(card.dataset.price || '0') || 0;
    const stayNights   = nights && nights > 0 ? nights : 1;
    const totalAmount  = basePerNight * stayNights;
    const taxAmount    = +(totalAmount * 0.15).toFixed(2);
    const currency     = card.dataset.currency || 'USD';

    const userId =
      window.TRAVELO && window.TRAVELO.userId ? window.TRAVELO.userId : '';
    const userName =
      window.TRAVELO && window.TRAVELO.userName ? window.TRAVELO.userName : '';
    const userEmail =
      window.TRAVELO && window.TRAVELO.userEmail ? window.TRAVELO.userEmail : '';

    const params = new URLSearchParams();
    params.set('booking_type', 'hotel');
    params.set('booking_status', 'pending');

    if (hotelName)     params.set('hotel_name', hotelName);
    if (hotelLocation) params.set('hotel_location', hotelLocation);
    if (cityCountry)   params.set('to_city', cityCountry);
    if (hotelId)       params.set('hotel_id', hotelId);

    params.set('stay_nights', stayNights.toString());
    params.set('amount_flight', '0');
    params.set('amount_hotel', totalAmount.toFixed(2));
    params.set('amount_package', '0');
    params.set('amount_taxes', taxAmount.toFixed(2));
    params.set('discount_amount', '0');
    params.set('currency', currency);

    if (userId)    params.set('user_id', userId);
    if (userName)  params.set('user_name', userName);
    if (userEmail) params.set('user_email', userEmail);

    return params.toString();
  }

  function goToBooking(card, nights) {
    if (!card) return;

    if (!window.TRAVELO || !window.TRAVELO.isLoggedIn) {
      const loginBtn = document.getElementById('btnLogin');
      if (loginBtn) { loginBtn.click(); return; }
    }

    const qs2 = buildBookingParams(card, nights);
    if (!qs2) return;
    window.location.href = `${bookingBaseUrl}?${qs2}`;
  }

  bookNowBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.hotel-card');
      if (!card) return;
      goToBooking(card, 1);
    });
  });

  mapViewBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.hotel-card');
      if (!card) return;
      const name = card.querySelector('.hotel-name')?.textContent.trim() || '';
      const loc  = card.dataset.location || card.querySelector('.hotel-location')?.textContent.trim() || '';
      alert(`Map view for:\n${name}\n${loc}\n(Map modal can be added later)`);
    });
  });

  // ========= MODAL =========
  const modalOverlay        = document.getElementById('hotelModal');
  const modalClose          = document.getElementById('hotelModalClose');
  const modalCloseSecondary = document.getElementById('modalCloseSecondary');

  const mainImageEl     = document.getElementById('modalMainImage');
  const thumbsContainer = document.getElementById('modalThumbs');

  const nameEl      = document.getElementById('modalHotelName');
  const locationEl  = document.getElementById('modalHotelLocation');
  const ratingEl    = document.getElementById('modalHotelRating');
  const reviewsEl   = document.getElementById('modalHotelReviews');
  const priceEl     = document.getElementById('modalHotelPrice');
  const aboutEl     = document.getElementById('modalHotelAbout');
  const servicesEl  = document.getElementById('modalHotelServices');
  const offerTagEl  = document.getElementById('modalOfferTag');
  const modalPerEl  = document.querySelector('.modal-per');

  const scoreLocationEl = document.getElementById('scoreLocation');
  const scoreServiceEl  = document.getElementById('scoreService');
  const scoreValueEl    = document.getElementById('scoreValue');

  const safetyTitleEl = document.getElementById('safetyTitle');
  const safetyDescEl  = document.getElementById('safetyDesc');

  const bookBtn     = document.getElementById('modalBookBtn');
  const payUnderBtn = document.getElementById('modalPayUnderBtn');
  const prevImgBtn  = document.getElementById('modalPrevImg');
  const nextImgBtn  = document.getElementById('modalNextImg');

  const nightsMinusBtn = document.getElementById('modalNightsMinus');
  const nightsPlusBtn  = document.getElementById('modalNightsPlus');
  const nightsValueEl  = document.getElementById('modalNightsValue');

  let currentImages  = [];
  let currentIndex   = 0;
  let modalBasePrice = 0;
  let modalNights    = 1;
  let modalCurrency  = 'USD';

  function isValidImageUrl(u) {
    const s = (u || '').trim();
    if (!s) return false;
    if (/^\d+$/.test(s)) return false;
    const low = s.toLowerCase();
    if (low === 'null' || low === 'none') return false;
    return (
      low.startsWith('http://') || low.startsWith('https://') ||
      low.startsWith('/') || low.startsWith('./') || low.startsWith('../')
    );
  }

  function refreshModalPriceUI() {
    const total = modalBasePrice * modalNights;
    if (priceEl) priceEl.textContent = formatPrice(total);

    if (modalPerEl) {
      const suffix = modalNights === 1 ? 'night' : 'nights';
      modalPerEl.textContent = `${modalCurrency} · ${modalNights} ${suffix}`;
    }
    if (nightsValueEl) nightsValueEl.textContent = String(modalNights);
  }

  function renderImages() {
    if (!mainImageEl || !thumbsContainer || !currentImages.length) return;

    mainImageEl.src = currentImages[currentIndex];
    thumbsContainer.innerHTML = '';

    currentImages.forEach((src, idx) => {
      const btn = document.createElement('button');
      if (idx === currentIndex) btn.classList.add('active');
      const img = document.createElement('img');
      img.src = src;
      img.alt = 'Hotel thumbnail';
      btn.appendChild(img);
      btn.addEventListener('click', () => {
        currentIndex = idx;
        renderImages();
      });
      thumbsContainer.appendChild(btn);
    });
  }

  function openModal(card) {
    if (!modalOverlay) return;
    modalCurrentCard = card;

    const name = card.querySelector('.hotel-name')?.textContent.trim() || '';
    const locationText = card.dataset.location || card.querySelector('.hotel-location')?.textContent.trim() || '';
    const cityCountry  = card.dataset.cityCountry || '';

    const rating   = parseFloat(card.dataset.rating) || 0;
    const reviews  = parseInt(card.dataset.reviews || '0', 10);
    const price    = parseFloat(card.dataset.price) || 0;
    const discount = parseInt(card.dataset.discount || '0', 10);
    const currency = card.dataset.currency || 'USD';

    modalBasePrice = price;
    modalNights    = 1;
    modalCurrency  = currency;

    const description = card.dataset.description ||
      'This property offers a comfortable stay with great location and helpful staff.';

    const amenitiesStr = card.dataset.amenities || '';
    const amenities = amenitiesStr
      ? amenitiesStr.split('|').map(a => a.trim()).filter(Boolean)
      : [];

    const imagesStr = card.dataset.images || '';
    currentImages = imagesStr
      ? imagesStr.split('|').map(i => i.trim()).filter(isValidImageUrl)
      : [];
    currentImages = Array.from(new Set(currentImages));

    if (!currentImages.length) {
      const fallbackImg = card.querySelector('.hotel-image-wrapper img');
      if (fallbackImg && isValidImageUrl(fallbackImg.src)) currentImages = [fallbackImg.src];
    }
    if (!currentImages.length) {
      currentImages = ['https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=80'];
    }

    currentIndex = 0;

    if (nameEl) nameEl.textContent = name;
    if (locationEl) locationEl.textContent = locationText || cityCountry;

    if (ratingEl)  ratingEl.textContent  = rating.toFixed(1);
    if (reviewsEl) reviewsEl.textContent = reviews ? `${reviews} reviews` : '';

    if (offerTagEl) offerTagEl.textContent = discount > 0 ? `${discount}% OFF` : 'BEST DEAL';
    if (aboutEl) aboutEl.textContent = description;

    if (servicesEl) {
      servicesEl.innerHTML = '';
      amenities.slice(0, 6).forEach(service => {
        const li = document.createElement('li');
        li.textContent = service;
        servicesEl.appendChild(li);
      });
    }

    const ratingPercent = Math.min(100, Math.max(60, (rating / 5) * 100));
    if (scoreLocationEl) scoreLocationEl.style.width = `${ratingPercent}%`;
    if (scoreServiceEl)  scoreServiceEl.style.width  = `${Math.max(55, ratingPercent - 5)}%`;
    if (scoreValueEl)    scoreValueEl.style.width    = `${Math.max(50, ratingPercent - 10)}%`;

    if (safetyTitleEl) safetyTitleEl.textContent = 'Travel safe during your stay';
    if (safetyDescEl)  safetyDescEl.textContent =
      'This property follows enhanced health & safety measures including cleaning and distancing practices.';

    refreshModalPriceUI();
    renderImages();

    modalOverlay.classList.add('show');
    document.body.classList.add('no-scroll');
  }

  function closeModal() {
    if (!modalOverlay) return;
    modalOverlay.classList.remove('show');
    document.body.classList.remove('no-scroll');
  }

  viewDetailsBtns.forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const card = btn.closest('.hotel-card');
      if (!card) return;
      openModal(card);
    });
  });

  if (prevImgBtn) prevImgBtn.addEventListener('click', () => {
    if (!currentImages.length) return;
    currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
    renderImages();
  });

  if (nextImgBtn) nextImgBtn.addEventListener('click', () => {
    if (!currentImages.length) return;
    currentIndex = (currentIndex + 1) % currentImages.length;
    renderImages();
  });

  if (nightsMinusBtn && nightsPlusBtn) {
    nightsMinusBtn.addEventListener('click', () => {
      if (modalNights > 1) { modalNights -= 1; refreshModalPriceUI(); }
    });
    nightsPlusBtn.addEventListener('click', () => {
      if (modalNights < 30) { modalNights += 1; refreshModalPriceUI(); }
    });
  }

  if (bookBtn) bookBtn.addEventListener('click', () => {
    if (modalCurrentCard) goToBooking(modalCurrentCard, modalNights);
  });

  if (payUnderBtn) payUnderBtn.addEventListener('click', () => {
    if (modalCurrentCard) goToBooking(modalCurrentCard, modalNights);
  });

  if (modalClose) modalClose.addEventListener('click', closeModal);
  if (modalCloseSecondary) modalCloseSecondary.addEventListener('click', closeModal);

  if (modalOverlay) modalOverlay.addEventListener('click', e => {
    if (e.target === modalOverlay) closeModal();
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
  });

  // ========= HERO ANIMATION =========
  const letters = document.querySelectorAll('.letter');
  letters.forEach((letter, index) => {
    letter.style.animationDelay = `${index * 0.1}s`;
  });

  const scrollIndicator = document.querySelector('.scroll-indicator');
  if (scrollIndicator) {
    window.addEventListener('scroll', () => {
      scrollIndicator.style.opacity = window.scrollY > 100 ? '0' : '1';
    });
  }

  const ctaButton = document.querySelector('.cta-button');
  if (ctaButton) {
    ctaButton.addEventListener('mouseenter', function () { this.classList.add('hover'); });
    ctaButton.addEventListener('mouseleave', function () { this.classList.remove('hover'); });
  }

  // ========= INIT =========
  // ensure category initial value if radios exist
  if (categoryRadios.length) {
    const init = categoryRadios.find(r => r.checked);
    if (init) selectedCategory = init.value || 'any';
  }
  updateLookingForUI();

  // slider init labels
  currentMinPrice = globalMinPrice;
  currentMaxPrice = globalMaxPrice;
  updateSliderLabels();

  applyAllFilters();
});
