// ================== FILTER + SORT + MODAL + BOOKING ==================
document.addEventListener("DOMContentLoaded", () => {
  const tabItems = document.querySelectorAll(".tab-item");
  const tripChips = document.querySelectorAll(".trip-chip");
  const flightList = document.getElementById("flightList");
  const allCards = flightList ? Array.from(flightList.querySelectorAll(".flight-card.ticket")) : [];

  const maxPriceInput = document.getElementById("maxPrice");
  const maxPriceValue = document.getElementById("maxPriceValue");
  const nonStopOnly = document.getElementById("nonStopOnly");
  const timeChips = document.querySelectorAll(".chip");
  const resetBtn = document.getElementById("resetFilters");
  const resultsCountEl = document.querySelector(".results-count");

  let currentSort = "cheapest";
  let currentTimeFilter = "all";
  let currentTripFilter = "all";

  const qs = new URLSearchParams(window.location.search);
  const destinationFilterId = qs.get("destination_id") ? String(qs.get("destination_id")) : null;

  if (!flightList || allCards.length === 0) return;

  // ========================= Helpers =========================
  function safeText(el) {
    return el ? String(el.textContent || "").trim() : "";
  }

  function getCardData(card) {
    // Prefer dataset first (best), fallback to DOM.
    const fromCityEl = card.querySelector(".ticket-route .city:first-child");
    const toCityEl = card.querySelector(".ticket-route .city:last-child");
    const depTimeEl = card.querySelector(".ticket-times .time:first-child strong");
    const arrTimeEl = card.querySelector(".ticket-times .time:last-child strong");
    const airlineEl = card.querySelector(".airline-name");
    const flightCodeEl = card.querySelector(".ticket-code");

    const fromCity = card.dataset.originCity || safeText(fromCityEl);
    const toCity = card.dataset.destCity || safeText(toCityEl);

    const fromCode =
      card.dataset.fromAirportCode ||
      (fromCityEl && fromCityEl.dataset.code) ||
      fromCity;

    const toCode =
      card.dataset.toAirportCode ||
      (toCityEl && toCityEl.dataset.code) ||
      toCity;

    const airline = card.dataset.airlineName || safeText(airlineEl);
    const flightNumber = card.dataset.flightNumber || safeText(flightCodeEl);

    const depTime = card.dataset.departureTime || safeText(depTimeEl);
    const arrTime = card.dataset.arrivalTime || safeText(arrTimeEl);

    // Dates: prefer ISO from dataset, fallback to labels if you didn't add ISO yet
    const departDateISO =
      card.dataset.departDate ||
      card.dataset.departureDate ||
      ""; // expected YYYY-MM-DD

    const returnDateISO =
      card.dataset.returnDate ||
      ""; // expected YYYY-MM-DD

    const depDateLabel = card.dataset.depDateLabel || "";
    const retDateLabel = card.dataset.retDateLabel || "";

    const tripType = (card.dataset.trip || "oneway").toLowerCase();

    const base = Number(card.dataset.price || "0");
    const tax = +(base * 0.15).toFixed(2);

    const flightId = card.dataset.flightId || "";
    const destinationId = card.dataset.destinationId || "";

    return {
      fromCity,
      toCity,
      fromCode,
      toCode,
      airline,
      flightNumber,
      depTime,
      arrTime,
      departDateISO,
      returnDateISO,
      depDateLabel,
      retDateLabel,
      tripType,
      base,
      tax,
      flightId,
      destinationId
    };
  }

  function parseISODate(s) {
    // expects YYYY-MM-DD
    if (!s) return null;
    const d = new Date(s + "T00:00:00");
    return Number.isNaN(d.getTime()) ? null : d;
  }

  function calcDurationDays(departISO, returnISO, tripType) {
    // only meaningful for roundtrip
    if (tripType !== "roundtrip") return 1;

    const d1 = parseISODate(departISO);
    const d2 = parseISODate(returnISO);
    if (!d1 || !d2) return 1;

    const diff = Math.round((d2.getTime() - d1.getTime()) / 86400000);
    // clamp: at least 1 day
    return diff >= 1 ? diff : 1;
  }

  function formatHoursToHM(hours) {
    const n = Number(hours);
    if (!Number.isFinite(n)) return "";
    const h = Math.floor(n);
    const m = Math.round((n - h) * 60);
    if (m <= 0) return `${h}h`;
    if (m >= 60) return `${h + 1}h`;
    return `${h}h ${m}m`;
  }

  // ========================= BOOKING LOGIC =========================
  const bookingBaseUrl = "booking.php";
  let lastSelectedCard = null;

  function buildBookingParams(card) {
    if (!card) return "";

    const d = getCardData(card);

    // user info from PHP injected script
    const userId = (window.TRAVELO && window.TRAVELO.userId) ? window.TRAVELO.userId : "";
    const userName = (window.TRAVELO && window.TRAVELO.userName) ? window.TRAVELO.userName : "";
    const userEmail = (window.TRAVELO && window.TRAVELO.userEmail) ? window.TRAVELO.userEmail : "";

    const params = new URLSearchParams();

    // ========= Booking / trip info =========
    params.set("booking_type", "flight");
    params.set("booking_status", "pending");

    if (d.fromCity) params.set("from_city", d.fromCity);
    if (d.toCity) params.set("to_city", d.toCity);

    // Prefer ISO dates, fallback to labels
    const startDate = d.departDateISO || d.depDateLabel || "";
    const endDate =
      (d.tripType === "roundtrip" ? (d.returnDateISO || d.retDateLabel) : "") ||
      startDate;

    if (startDate) params.set("trip_start_date", startDate);
    if (endDate) params.set("trip_end_date", endDate);

    // duration_days (useful for booking page display if you want)
    const durDays = calcDurationDays(d.departDateISO, d.returnDateISO, d.tripType);
    params.set("duration_days", String(durDays));

    // Extra flight info
    if (d.airline) params.set("airline", d.airline);
    if (d.flightNumber) params.set("flight_number", d.flightNumber);
    if (d.fromCode) params.set("from_airport_code", d.fromCode);
    if (d.toCode) params.set("to_airport_code", d.toCode);
    if (d.depTime) params.set("departure_time", d.depTime);
    if (d.arrTime) params.set("arrival_time", d.arrTime);

    if (d.flightId) params.set("flight_id", d.flightId);
    if (d.destinationId) params.set("destination_id", d.destinationId);

    // ========= Amount columns =========
    params.set("amount_flight", d.base.toString());
    params.set("amount_hotel", "0");
    params.set("amount_package", "0");
    params.set("amount_taxes", d.tax.toString());
    params.set("discount_amount", "0");
    params.set("currency", "USD");

    // ========= User info =========
    if (userId) params.set("user_id", userId);
    if (userName) params.set("user_name", userName);
    if (userEmail) params.set("user_email", userEmail);

    return params.toString();
  }

  function goToBooking(card) {
    if (!card) return;

    // if not logged in -> open login modal/button
    if (!window.TRAVELO || !window.TRAVELO.isLoggedIn) {
      const loginBtn = document.getElementById("btnLogin");
      if (loginBtn) loginBtn.click();
      return;
    }

    const q = buildBookingParams(card);
    if (!q) return;
    window.location.href = bookingBaseUrl + "?" + q;
  }

  // Bind Book Now buttons
  document.querySelectorAll(".book-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".flight-card.ticket");
      lastSelectedCard = card;
      goToBooking(card);
    });
  });

  // ========================= FILTER + SORT =========================
  function renderCards(cards) {
    flightList.innerHTML = "";
    cards.forEach((card) => flightList.appendChild(card));
  }

  function getFilteredCards() {
    const maxPrice = maxPriceInput ? Number(maxPriceInput.value) : Infinity;

    return allCards.filter((card) => {
      // destination filter (if provided)
      if (destinationFilterId) {
        const cardDestId = String(card.dataset.destinationId || "");
        if (cardDestId !== destinationFilterId) return false;
      }

      const price = Number(card.dataset.price || 0);
      const stops = Number(card.dataset.stops || 0);
      const tripType = (card.dataset.trip || "all").toLowerCase();

      if (maxPriceInput && price > maxPrice) return false;
      if (nonStopOnly && nonStopOnly.checked && stops !== 0) return false;

      if (currentTripFilter !== "all" && tripType !== currentTripFilter) {
        return false;
      }

      if (currentTimeFilter !== "all") {
        const depTimeEl = card.querySelector(".ticket-times .time:first-child strong");
        const depTimeText = depTimeEl ? depTimeEl.textContent.trim() : "";
        const hour = Number(depTimeText.split(":")[0]);
        if (!Number.isFinite(hour)) return false;

        if (currentTimeFilter === "morning" && hour >= 12) return false;
        if (currentTimeFilter === "evening" && hour < 12) return false;
      }

      return true;
    });
  }

  function applySortAndFilter() {
  const filtered = getFilteredCards();

  filtered.sort((a, b) => {
    const ap = Number(a.dataset.price || "0");
    const bp = Number(b.dataset.price || "0");
    const ad = Number(a.dataset.duration || "999999");
    const bd = Number(b.dataset.duration || "999999");

    if (currentSort === "fastest") {
      if (ad !== bd) return ad - bd; 
      return ap - bp;               
    }

    // cheapest
    if (ap !== bp) return ap - bp;  
    return ad - bd;                
  });

  renderCards(filtered);

  if (resultsCountEl) {
    resultsCountEl.textContent = `${filtered.length} results found`;
  }
}


  tabItems.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabItems.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");
      currentSort = tab.dataset.sort || "cheapest";
      applySortAndFilter();
    });
  });

  tripChips.forEach((chip) => {
    chip.addEventListener("click", () => {
      tripChips.forEach((c) => c.classList.remove("active"));
      chip.classList.add("active");
      currentTripFilter = (chip.dataset.trip || "all").toLowerCase();
      applySortAndFilter();
    });
  });

  if (maxPriceInput && maxPriceValue) {
    maxPriceInput.addEventListener("input", () => {
      maxPriceValue.textContent = `Up to ${maxPriceInput.value}`;
      applySortAndFilter();
    });
  }

  if (nonStopOnly) {
    nonStopOnly.addEventListener("change", applySortAndFilter);
  }

  timeChips.forEach((chip) => {
    chip.addEventListener("click", () => {
      timeChips.forEach((c) => c.classList.remove("active"));
      chip.classList.add("active");
      currentTimeFilter = chip.dataset.time || "all";
      applySortAndFilter();
    });
  });

  if (resetBtn && maxPriceInput && maxPriceValue) {
    resetBtn.addEventListener("click", () => {
      // special: if we came with destination_id, reset removes it first
      if (destinationFilterId) {
        const url = new URL(window.location.href);
        url.searchParams.delete("destination_id");
        window.location.href = url.toString();
        return;
      }

      maxPriceInput.value = maxPriceInput.max || 600;
      maxPriceValue.textContent = `Up to ${maxPriceInput.value}`;
      if (nonStopOnly) nonStopOnly.checked = false;

      currentTimeFilter = "all";
      currentTripFilter = "all";
      currentSort = "cheapest";

      timeChips.forEach((c) => c.classList.remove("active"));
      const anyChip = document.querySelector('.chip[data-time="all"]');
      if (anyChip) anyChip.classList.add("active");

      tripChips.forEach((c) => c.classList.remove("active"));
      const tripAll = document.querySelector('.trip-chip[data-trip="all"]');
      if (tripAll) tripAll.classList.add("active");

      tabItems.forEach((t) => t.classList.remove("active"));
      const cheapestTab = document.querySelector('.tab-item[data-sort="cheapest"]');
      if (cheapestTab) cheapestTab.classList.add("active");

      applySortAndFilter();
    });
  }

  // ========================= Modal التفاصيل =========================
  const modalEl = document.getElementById("flightDetailsModal");
  let detailsModal = null;

  if (modalEl && window.bootstrap) {
    detailsModal = new bootstrap.Modal(modalEl);
  }

  const detailsButtons = document.querySelectorAll(".details-btn");
  const modalBookBtn = document.getElementById("modalBookBtn");

  detailsButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      if (!modalEl || !detailsModal) return;

      const card = btn.closest(".flight-card.ticket");
      if (!card) return;
      lastSelectedCard = card;

      const d = getCardData(card);

      const durationHours = card.dataset.duration || "";
      const durationNice = durationHours ? formatHoursToHM(durationHours) : "";

      const priceText = card.querySelector(".price") ? card.querySelector(".price").textContent.trim() : "";

      let tripTypeText = "Flight";
      if (d.tripType === "oneway") tripTypeText = "One way";
      else if (d.tripType === "roundtrip") tripTypeText = "Round trip";

      const stopsCount = Number(card.dataset.stops || "0");
      const stopsText = stopsCount === 0 ? "Non stop" : `${stopsCount} stop(s)`;

      modalEl.querySelector(".modal-airline-name").textContent = d.airline || "";
      modalEl.querySelector(".modal-flight-code").textContent = d.flightNumber || "";
      modalEl.querySelector(".modal-trip-type").textContent = tripTypeText;

      modalEl.querySelector(".modal-route").textContent =
        d.fromCity && d.toCity ? `${d.fromCity} → ${d.toCity}` : "";

      modalEl.querySelector(".modal-departure").textContent = d.depTime || "";
      modalEl.querySelector(".modal-arrival").textContent = d.arrTime || "";

      modalEl.querySelector(".modal-duration").textContent = durationNice || "";
      modalEl.querySelector(".modal-stops").textContent = stopsText;

      modalEl.querySelectorAll(".modal-price").forEach((el) => {
        el.textContent = priceText || "";
      });

      detailsModal.show();
    });
  });

  if (modalBookBtn) {
    modalBookBtn.addEventListener("click", () => {
      if (lastSelectedCard) goToBooking(lastSelectedCard);
    });
  }

  // ✅ default عند أول تحميل: Cheapest
currentSort = "cheapest";
tabItems.forEach((t) => t.classList.remove("active"));
document.querySelector('.tab-item[data-sort="cheapest"]')?.classList.add("active");

  applySortAndFilter();
});


// ================== FLASH / ANIMATION SECTION (نفسه) ==================
function animateTextReveal() {
  const title = document.querySelector(".main-title");
  if (!title) return;

  const text = title.textContent;
  title.innerHTML = "";

  for (let i = 0; i < text.length; i++) {
    const span = document.createElement("span");
    span.textContent = text[i];
    span.style.animationDelay = `${i * 0.1}s`;
    title.appendChild(span);
  }

  title.classList.add("text-reveal");
}

function createLightSweep() {
  const flashSection = document.querySelector(".flash-section");
  if (!flashSection) return;
  const lightSweep = document.createElement("div");
  lightSweep.className = "light-sweep";

  flashSection.appendChild(lightSweep);
}

function createStars() {
  const flashSection = document.querySelector(".flash-section");
  if (!flashSection) return;

  const starCount = 30;

  for (let i = 0; i < starCount; i++) {
    const star = document.createElement("div");
    star.className = "star";

    const size = Math.random() * 3 + 1;
    const left = Math.random() * 100;
    const top = Math.random() * 100;
    const delay = Math.random() * 5;
    const duration = Math.random() * 3 + 2;

    star.style.cssText = `
      position: absolute;
      width: ${size}px;
      height: ${size}px;
      background: white;
      border-radius: 50%;
      left: ${left}%;
      top: ${top}%;
      opacity: ${Math.random() * 0.5 + 0.1};
      animation: twinkle ${duration}s infinite ${delay}s;
      z-index: 1;
    `;

    flashSection.appendChild(star);
  }
}

function initFlashButtons() {
  const flashButtons = document.querySelectorAll(".flash-btn");

  flashButtons.forEach((button) => {
    button.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-8px) scale(1.05)";
    });

    button.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)";
    });

    button.addEventListener("click", function (e) {
      e.preventDefault();

      this.style.transform = "translateY(-4px) scale(1.02)";

      setTimeout(() => {
        this.style.transform = "translateY(-8px) scale(1.05)";
      }, 150);
    });
  });
}

function enhanceAirplanes() {
  const airplanes = document.querySelectorAll(".airplane");

  airplanes.forEach((plane) => {
    plane.addEventListener("animationiteration", () => {
      const colors = ["#3498db", "#9b59b6", "#2ecc71", "#e74c3c", "#f1c40f"];
      const randomColor = colors[Math.floor(Math.random() * colors.length)];
      plane.style.color = randomColor;

      const randomSize = Math.random() * 15 + 25;
      plane.style.fontSize = `${randomSize}px`;

      const randomDuration = Math.random() * 10 + 20;
      plane.style.animationDuration = `${randomDuration}s`;
    });
  });
}

function initFloatEffects() {
  const tagline = document.querySelector(".tagline span");
  if (tagline) {
    setInterval(() => {
      tagline.style.animation = "none";
      setTimeout(() => {
        tagline.style.animation = "float 6s ease-in-out infinite";
      }, 10);
    }, 6000);
  }
}

function initBackgroundEffects() {
  const flashSection = document.querySelector(".flash-section");
  if (!flashSection) return;

  const backgrounds = [
    "https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=2074&q=80",
    "https://images.unsplash.com/photo-1540453764285-7c5d5d5b5b1a?auto=format&fit=crop&w=2070&q=80",
    "https://images.unsplash.com/photo-1534274988757-a28bf1a57c17?auto=format&fit=crop&w=2070&q=80",
  ];

  let currentBg = 0;

  setInterval(() => {
    currentBg = (currentBg + 1) % backgrounds.length;

    flashSection.style.opacity = "0.7";
    flashSection.style.transition = "opacity 1s ease";

    setTimeout(() => {
      flashSection.style.backgroundImage = `
        linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
        url('${backgrounds[currentBg]}')
      `;
      flashSection.style.opacity = "1";
    }, 500);

    setTimeout(() => {
      flashSection.style.transition = "";
    }, 1500);
  }, 10000);
}

function addDynamicAirplanes() {
  const flashSection = document.querySelector(".flash-section");
  if (!flashSection) return;

  setInterval(() => {
    if (Math.random() > 0.7) {
      const airplane = document.createElement("div");
      airplane.className = "airplane";
      airplane.textContent = "✈";

      const size = Math.random() * 20 + 20;
      const top = Math.random() * 80 + 10;
      const duration = Math.random() * 15 + 20;
      const delay = Math.random() * 5;
      const colors = ["#3498db", "#9b59b6", "#2ecc71", "#e74c3c", "#f1c40f"];
      const color = colors[Math.floor(Math.random() * colors.length)];

      airplane.style.cssText = `
        position: absolute;
        top: ${top}%;
        left: -50px;
        font-size: ${size}px;
        color: ${color};
        opacity: 0.3;
        animation: flyAcross ${duration}s linear infinite ${delay}s;
        z-index: 2;
      `;

      flashSection.appendChild(airplane);

      setTimeout(() => {
        if (airplane.parentNode) airplane.remove();
      }, (duration + delay) * 1000);
    }
  }, 3000);
}

function initFlashEffects() {
  animateTextReveal();
  createLightSweep();
  createStars();
  initFlashButtons();
  enhanceAirplanes();
  initFloatEffects();
  initBackgroundEffects();
  addDynamicAirplanes();

  setInterval(() => {
    const airplanes = document.querySelectorAll(".airplane");
    airplanes.forEach((plane) => {
      plane.style.animation = "none";
      setTimeout(() => {
        plane.style.animation = "";
      }, 10);
    });
  }, 30000);
}

document.addEventListener("DOMContentLoaded", initFlashEffects);

window.addEventListener("scroll", function () {
  const flashSection = document.querySelector(".flash-section");
  const scrollPosition = window.scrollY;

  if (flashSection) {
    flashSection.style.backgroundPositionY = `${scrollPosition * 0.5}px`;
  }
});

window.addEventListener("load", function () {
  setTimeout(() => {
    const buttons = document.querySelectorAll(".flash-btn");
    buttons.forEach((button, index) => {
      setTimeout(() => {
        button.style.transform = "translateY(-10px)";
        setTimeout(() => {
          button.style.transform = "translateY(0)";
        }, 300);
      }, index * 200);
    });
  }, 1500);
});
