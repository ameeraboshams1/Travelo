// ================== FILTER + SORT + MODAL + BOOKING ==================
document.addEventListener("DOMContentLoaded", () => {
  const tabItems = document.querySelectorAll(".tab-item");
  const tripChips = document.querySelectorAll(".trip-chip");
  const flightList = document.getElementById("flightList");
  const allCards = flightList ? Array.from(flightList.children) : [];

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

  // ====== BOOKING LOGIC ======
  // 🚨 الملف الجديد تبع الويزرد
  const bookingBaseUrl = "booking.php";
  let lastSelectedCard = null;

  function buildBookingParams(card) {
    if (!card) return "";

    const fromCityEl = card.querySelector(".ticket-route .city:first-child");
    const toCityEl = card.querySelector(".ticket-route .city:last-child");
    const depTimeEl = card.querySelector(
      ".ticket-times .time:first-child strong"
    );
    const arrTimeEl = card.querySelector(
      ".ticket-times .time:last-child strong"
    );
    const airlineEl = card.querySelector(".airline-name");
    const flightCodeEl = card.querySelector(".ticket-code");

    const fromCode =
      (fromCityEl && fromCityEl.dataset.code) ||
      (fromCityEl && fromCityEl.textContent.trim()) ||
      "";
    const toCode =
      (toCityEl && toCityEl.dataset.code) ||
      (toCityEl && toCityEl.textContent.trim()) ||
      "";

    const depTime = depTimeEl ? depTimeEl.textContent.trim() : "";
    const arrTime = arrTimeEl ? arrTimeEl.textContent.trim() : "";
    const airline = airlineEl ? airlineEl.textContent.trim() : "";
    const flightCode = flightCodeEl ? flightCodeEl.textContent.trim() : "";

    const tripType = card.dataset.trip || "oneway";
    const depDateLabel = card.dataset.depDateLabel || "";
    const retDateLabel = card.dataset.retDateLabel || "";

    const base = Number(card.dataset.price || "0");
    const tax = +(base * 0.15).toFixed(2); // ضريبة 15% بشكل مرتب
    const add = 0;

    // بيانات اليوزر من السكربت في PHP
    const userId =
      window.TRAVELO && window.TRAVELO.userId ? window.TRAVELO.userId : "";
    const userName =
      window.TRAVELO && window.TRAVELO.userName ? window.TRAVELO.userName : "";
    const userEmail =
      window.TRAVELO && window.TRAVELO.userEmail
        ? window.TRAVELO.userEmail
        : "";

    const flightId = card.dataset.flightId || "";

    const params = new URLSearchParams();

    // ========= Booking / trip info =========
    params.set("booking_type", "flight");
    params.set("booking_status", "pending");

    if (fromCityEl) params.set("from_city", fromCityEl.textContent.trim());
    if (toCityEl) params.set("to_city", toCityEl.textContent.trim());

    // التواريخ (الويزرد بس بعرضهم ستـرينغ، فآمن نبعث الـ label)
    if (depDateLabel) params.set("trip_start_date", depDateLabel);
    if (tripType === "roundtrip" && retDateLabel) {
      params.set("trip_end_date", retDateLabel);
    } else if (depDateLabel) {
      params.set("trip_end_date", depDateLabel);
    }

    // نرسل كمان شوية معلومات شكلية لو حبيتي تستخدميها لاحقاً
    if (airline) params.set("airline", airline);
    if (flightCode) params.set("flight_number", flightCode);
    if (fromCode) params.set("from_airport_code", fromCode);
    if (toCode) params.set("to_airport_code", toCode);
    if (depTime) params.set("departure_time", depTime);
    if (arrTime) params.set("arrival_time", arrTime);

    if (flightId) params.set("flight_id", flightId);

    // ========= Amount columns (جدول الـ bookings + payments) =========
    params.set("amount_flight", base.toString());
    params.set("amount_hotel", "0");
    params.set("amount_package", "0");
    params.set("amount_taxes", tax.toString());
    params.set("discount_amount", "0");
    params.set("currency", "USD");

    // ========= Travellers (لو مش موجودين، الويزرد بيفترض 1 adult) =========
    // هون ممكن لاحقاً نجيبهم من كويري الـ search لو عندك
    // params.set("travellers_adults", "2") ... الخ

    // ========= User info =========
    if (userId) params.set("user_id", userId);
    if (userName) params.set("user_name", userName);
    if (userEmail) params.set("user_email", userEmail);

    return params.toString();
  }

  function goToBooking(card) {
    if (!card) return;

    // لو مش عامل لوجين → نفتح مودال اللوجين بدال ما نروح على البوكنج
    if (!window.TRAVELO || !window.TRAVELO.isLoggedIn) {
      const loginBtn = document.getElementById("btnLogin");
      if (loginBtn) {
        loginBtn.click();
        return;
      }
    }

    const qs = buildBookingParams(card);
    if (!qs) return;
    window.location.href = bookingBaseUrl + "?" + qs;
  }

  // ربط Book Now
  const bookButtons = document.querySelectorAll(".book-btn");
  bookButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".flight-card.ticket");
      lastSelectedCard = card;
      goToBooking(card);
    });
  });

  // باقي الفلترة والفرز
  function renderCards(cards) {
    flightList.innerHTML = "";
    cards.forEach((card) => flightList.appendChild(card));
  }

  function getFilteredCards() {
  const maxPrice = maxPriceInput ? Number(maxPriceInput.value) : Infinity;

  return allCards.filter((card) => {

    // ✅ فلترة حسب الوجهة فقط إذا جاي destination_id من الرابط
    if (destinationFilterId) {
      const cardDestId = String(card.dataset.destinationId || "");
      if (cardDestId !== destinationFilterId) return false;
    }

    const price = Number(card.dataset.price || 0);
    const stops = Number(card.dataset.stops || 0);
    const tripType = card.dataset.trip || "all";

    if (maxPriceInput && price > maxPrice) return false;
    if (nonStopOnly && nonStopOnly.checked && stops !== 0) return false;

    if (currentTripFilter !== "all" && tripType !== currentTripFilter) {
      return false;
    }

    if (currentTimeFilter !== "all") {
      const depTimeEl = card.querySelector(".ticket-times .time:first-child strong");
      if (!depTimeEl) return false;

      const depTimeText = depTimeEl.textContent.trim();
      const hour = Number(depTimeText.split(":")[0]);

      if (currentTimeFilter === "morning" && hour >= 12) return false;
      if (currentTimeFilter === "evening" && hour < 12) return false;
    }

    return true;
  });
}


  function applySortAndFilter() {
    const filtered = getFilteredCards();

    filtered.sort((a, b) => {
      if (currentSort === "cheapest") {
        return Number(a.dataset.price) - Number(b.dataset.price);
      } else {
        return Number(a.dataset.duration) - Number(b.dataset.duration);
      }
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
      currentTripFilter = chip.dataset.trip || "all";
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
      const cheapestTab = document.querySelector(
        '.tab-item[data-sort="cheapest"]'
      );
      if (cheapestTab) cheapestTab.classList.add("active");

      applySortAndFilter();
    });
  }

  // Modal التفاصيل
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

      const airlineName =
        card.querySelector(".airline-name")?.textContent.trim() || "";
      const flightCode =
        card.querySelector(".ticket-code")?.textContent.trim() || "";

      const fromCityEl =
        card.querySelector(".ticket-route .city:first-child") || null;
      const toCityEl =
        card.querySelector(".ticket-route .city:last-child") || null;

      const fromCity = fromCityEl ? fromCityEl.textContent.trim() : "";
      const toCity = toCityEl ? toCityEl.textContent.trim() : "";

      const depTime =
        card
          .querySelector(".ticket-times .time:first-child strong")
          ?.textContent.trim() || "";
      const arrTime =
        card
          .querySelector(".ticket-times .time:last-child strong")
          ?.textContent.trim() || "";

      const duration = card.dataset.duration || "";
      const price = card.querySelector(".price")?.textContent.trim() || "";

      const tripTypeRaw = card.dataset.trip || "all";
      let tripTypeText = "Flight";
      if (tripTypeRaw === "oneway") tripTypeText = "One way";
      else if (tripTypeRaw === "roundtrip") tripTypeText = "Round trip";

      const stopsCount = Number(card.dataset.stops || "0");
      const stopsText =
        stopsCount === 0 ? "Non stop" : `${stopsCount} stop(s)`;

      modalEl.querySelector(".modal-airline-name").textContent = airlineName;
      modalEl.querySelector(".modal-flight-code").textContent = flightCode;
      modalEl.querySelector(".modal-trip-type").textContent = tripTypeText;

      modalEl.querySelector(".modal-route").textContent =
        fromCity && toCity ? `${fromCity} → ${toCity}` : "";

      modalEl.querySelector(".modal-departure").textContent = depTime;
      modalEl.querySelector(".modal-arrival").textContent = arrTime;

      modalEl.querySelector(".modal-duration").textContent =
        duration ? `${duration} h` : "";
      modalEl.querySelector(".modal-stops").textContent = stopsText;

      modalEl.querySelectorAll(".modal-price").forEach((el) => {
        el.textContent = price;
      });

      detailsModal.show();
    });
  });

  if (modalBookBtn) {
    modalBookBtn.addEventListener("click", () => {
      if (lastSelectedCard) {
        goToBooking(lastSelectedCard);
      }
    });
  }

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

      if (this.classList.contains("btn-primary")) {
        // simulateBooking(); // مش لازمتنا حالياً
      } else {
        // simulateExplore();
      }
    });
  });
}

function enhanceAirplanes() {
  const airplanes = document.querySelectorAll(".airplane");

  airplanes.forEach((plane) => {
    plane.addEventListener("animationiteration", () => {
      const colors = [
        "#3498db",
        "#9b59b6",
        "#2ecc71",
        "#e74c3c",
        "#f1c40f",
      ];
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
      const colors = [
        "#3498db",
        "#9b59b6",
        "#2ecc71",
        "#e74c3c",
        "#f1c40f",
      ];
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
        if (airplane.parentNode) {
          airplane.remove();
        }
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
