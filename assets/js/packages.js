// ================== PACKAGES PAGE LOGIC ==================
class TourPage {
  constructor() {
    this.tours = [];
    this.filteredTours = [];
    this.perPage = 7;
    this.currentPage = 1;
    this.totalPages = 1;

    this.filters = {
      search: "",
      maxPrice: null,
      categories: new Set(),
      durations: new Set(),
      minRating: 0,
      sortBy: "featured",
    };

    this.ratingThresholds = { 5: 4.5, 4: 4.0, 3: 3.0 };
    this.bookingBaseUrl = "booking.php";

    const qs = new URLSearchParams(window.location.search);
    this.destinationFilterId = qs.get("destination_id")
      ? String(qs.get("destination_id"))
      : null;

    this.cacheElements();
    this.buildToursData();
    this.initPriceSlider();
    this.updateSidebarCounts();
    this.applyFilters(true);
    this.bindEvents();
    this.setupScrollAnimations();
    this.initDetailsModal();

    if (this.spinner) this.spinner.style.display = "none";
  }

  // ================= CACHE DOM =================
  cacheElements() {
    this.spinner = document.getElementById("spinner");

    this.heroForm = document.getElementById("heroSearch");
    this.heroInputs = this.heroForm
      ? this.heroForm.querySelectorAll(".form-input, .form-select")
      : [];
    this.heroButton = this.heroForm
      ? this.heroForm.querySelector('button[type="submit"]')
      : null;
    this.heroDateInput = this.heroForm
      ? this.heroForm.querySelector('input[type="date"]')
      : null;

    this.sidebarSearchInput = document.getElementById("sidebarSearch");

    this.priceSlider = document.getElementById("priceSlider");
    this.priceRangeText = document.getElementById("priceRangeText");
    this.priceApplyBtn = document.getElementById("applyPrice");

    this.categoriesSection = document.getElementById("categoriesList");
    this.durationSection = document.getElementById("durationList");
    this.ratingSection = document.getElementById("ratingList");

    this.categoryCheckboxes = this.categoriesSection
      ? Array.from(this.categoriesSection.querySelectorAll('input[type="checkbox"]'))
      : [];

    this.durationCheckboxes = this.durationSection
      ? Array.from(this.durationSection.querySelectorAll('input[type="checkbox"]'))
      : [];

    this.ratingCheckboxes = this.ratingSection
      ? Array.from(this.ratingSection.querySelectorAll('input[type="checkbox"]'))
      : [];

    this.tourGrid = document.getElementById("tourGrid");
    this.tourCards = this.tourGrid
      ? Array.from(this.tourGrid.querySelectorAll(".tour-card"))
      : [];

    this.bookButtons = this.tourGrid
      ? Array.from(this.tourGrid.querySelectorAll(".book-package-btn"))
      : [];

    this.tourCountSpan = document.getElementById("tourCount");
    this.sortSelect = document.getElementById("sortSelect");

    this.paginationContainer = document.querySelector(".pagination");

    this.toastEl = document.getElementById("toast");
    if (!this.toastEl) {
      this.toastEl = document.createElement("div");
      this.toastEl.id = "toast";
      this.toastEl.className = "toast";
      document.body.appendChild(this.toastEl);
    }

    if (this.heroButton && !this.heroButton.querySelector(".btn-ripple")) {
      const ripple = document.createElement("span");
      ripple.className = "btn-ripple";
      this.heroButton.style.position = "relative";
      this.heroButton.appendChild(ripple);
      this.rippleEl = ripple;
    } else {
      this.rippleEl = this.heroButton ? this.heroButton.querySelector(".btn-ripple") : null;
    }
  }

  // ================= BUILD DATA =================
  buildToursData() {
    this.tours = this.tourCards.map((card, index) => {
      const title = card.querySelector(".tour-title")?.textContent.trim() || "";
      const location = card.querySelector(".tour-location")?.textContent.trim() || "";

      const price = parseFloat(card.dataset.price || "0") || 0;
      const rating = parseFloat(card.dataset.rating || "0") || 0;
      const duration = parseInt(card.dataset.duration || "0", 10) || 0;
      const category = (card.dataset.category || "adventure").toLowerCase();

      // reviews من النص داخل الكارد
      const ratingSpan = card.querySelector(".tour-meta-left span:first-child");
      let reviews = 0;
      if (ratingSpan) {
        const txt = ratingSpan.textContent.trim();
        const match = txt.match(/\((\d+)\)/);
        if (match) reviews = parseInt(match[1], 10) || 0;
      }

      // duration bucket
      let durationBucket = "week";
      if (duration <= 1) durationBucket = "day";
      else if (duration <= 3) durationBucket = "weekend";
      else if (duration <= 7) durationBucket = "week";
      else durationBucket = "extended";

      // hotel + dashboard flags (from hotels table)
      const hotel = {
        name: (card.dataset.hotelName || "").trim(),
        rating: (card.dataset.hotelRating || "").trim(),
        reviews: (card.dataset.hotelReviews || "").trim(),
        priceNight: (card.dataset.hotelPriceNight || "").trim(),
        currency: (card.dataset.hotelCurrency || "USD").trim(),
        flags: {
          has_wifi: +card.dataset.hasWifi === 1,
          has_free_breakfast: +card.dataset.hasFreeBreakfast === 1,
          has_parking: +card.dataset.hasParking === 1,
          has_city_view: +card.dataset.hasCityView === 1,
          has_sea_view: +card.dataset.hasSeaView === 1,
          airport_shuttle: +card.dataset.airportShuttle === 1,
          has_attached_bathroom: +card.dataset.hasAttachedBathroom === 1,
          has_cctv: +card.dataset.hasCctv === 1,
          pay_at_hotel: +card.dataset.payAtHotel === 1,
          couple_friendly: +card.dataset.coupleFriendly === 1,
          pet_friendly: +card.dataset.petFriendly === 1,
        },
      };

      // flight details (if exists)
      const flight = {
        airline: (card.dataset.flightAirline || "").trim(),
        no: (card.dataset.flightNo || "").trim(),
        from: (card.dataset.flightFrom || "").trim(),
        to: (card.dataset.flightTo || "").trim(),
        departAt: (card.dataset.flightDepart || "").trim(),
        arriveAt: (card.dataset.flightArrive || "").trim(),
      };

      return {
        element: card,
        originalIndex: index,
        data: {
          title,
          location,
          price,
          rating,
          duration,
          durationBucket,
          category,
          reviews,
          hotel,
          flight,
        },
      };
    });

    if (!this.tours.length) return;
    this.maxTourPrice = Math.max(...this.tours.map((t) => t.data.price));
  }

  // ================= PRICE SLIDER =================
  initPriceSlider() {
    if (!this.priceSlider || !this.maxTourPrice) return;

    const maxVal = Math.ceil(this.maxTourPrice / 50) * 50;
    this.priceSlider.min = "0";
    this.priceSlider.max = String(maxVal);
    this.priceSlider.value = String(maxVal);

    this.filters.maxPrice = maxVal;

    if (this.priceRangeText) {
      this.priceRangeText.textContent = `Selected range: $0 - $${maxVal}`;
    }
    this.updatePriceSliderBackground();
  }

  updatePriceSliderBackground() {
    if (!this.priceSlider) return;
    const min = parseFloat(this.priceSlider.min);
    const max = parseFloat(this.priceSlider.max);
    const val = parseFloat(this.priceSlider.value);
    if (max === min) return;
    const percent = ((val - min) / (max - min)) * 100;
    this.priceSlider.style.background = `linear-gradient(to right, #b049f1 0%, #b049f1 ${percent}%, #e8dcff ${percent}%, #e8dcff 100%)`;
  }

  // ================= COUNTS =================
  updateSidebarCounts() {
    if (!this.tours.length) return;

    const catCounts = {};
    this.categoryCheckboxes.forEach((cb) => {
      const key = cb.dataset.categories;
      if (key) catCounts[key] = 0;
    });

    this.tours.forEach((t) => {
      const cat = t.data.category;
      if (catCounts[cat] !== undefined) catCounts[cat]++;
      if (catCounts["all"] !== undefined) catCounts["all"]++;
    });

    this.categoryCheckboxes.forEach((cb) => {
      const key = cb.dataset.categories;
      const span = cb.closest("label")?.querySelector(".count");
      if (!span || !key) return;
      span.textContent = catCounts[key] !== undefined ? catCounts[key] : 0;
    });

    const durCounts = { day: 0, weekend: 0, week: 0, extended: 0 };
    this.tours.forEach((t) => {
      const bucket = t.data.durationBucket;
      if (durCounts[bucket] !== undefined) durCounts[bucket]++;
    });

    this.durationCheckboxes.forEach((cb) => {
      const key = cb.dataset.duration;
      const span = cb.closest("label")?.querySelector(".count");
      if (span && durCounts[key] !== undefined) span.textContent = durCounts[key];
    });

    const ratingCounts = { 5: 0, 4: 0, 3: 0 };
    this.tours.forEach((t) => {
      const r = t.data.rating || 0;
      Object.entries(this.ratingThresholds).forEach(([k, threshold]) => {
        if (r >= threshold) ratingCounts[k]++;
      });
    });

    this.ratingCheckboxes.forEach((cb) => {
      const key = parseInt(cb.dataset.rating || "0", 10);
      const span = cb.closest("label")?.querySelector(".count");
      if (span && ratingCounts[key] !== undefined) span.textContent = ratingCounts[key];
    });
  }

  // ================= EVENTS =================
  bindEvents() {
    if (this.heroForm && this.heroInputs.length > 0) {
      this.heroInputs.forEach((input) => {
        input.addEventListener("focus", () => this.heroForm.classList.add("is-active"));
        input.addEventListener("blur", () => {
          const stillFocused = Array.from(this.heroInputs).some((el) => el === document.activeElement);
          if (!stillFocused) this.heroForm.classList.remove("is-active");
        });
      });

      if (this.heroDateInput) {
        const today = new Date().toISOString().split("T")[0];
        this.heroDateInput.min = today;
      }

      if (this.heroButton && this.rippleEl) {
        this.heroButton.addEventListener("click", (e) => this.createButtonRipple(e));
      }

      this.heroForm.addEventListener("submit", (e) => this.handleHeroSearchSubmit(e));
    }

    if (this.sidebarSearchInput) {
      this.sidebarSearchInput.addEventListener("input", (e) => {
        this.filters.search = e.target.value.trim().toLowerCase();
        this.applyFilters(true);
      });
    }

    if (this.priceSlider) {
      this.priceSlider.addEventListener("input", (e) => {
        const val = parseFloat(e.target.value);
        this.filters.maxPrice = val;
        if (this.priceRangeText) this.priceRangeText.textContent = `Selected range: $0 - $${val}`;
        this.updatePriceSliderBackground();
      });
    }

    if (this.priceApplyBtn) {
      this.priceApplyBtn.addEventListener("click", () => {
        this.applyFilters(true);
        this.showToast("Price filter applied");
      });
    }

    if (this.categoryCheckboxes.length > 0) {
      this.categoryCheckboxes.forEach((cb) =>
        cb.addEventListener("change", (e) => this.handleCategoryChange(e))
      );
    }

    if (this.durationCheckboxes.length > 0) {
      this.durationCheckboxes.forEach((cb) =>
        cb.addEventListener("change", () => this.handleDurationChange())
      );
    }

    if (this.ratingCheckboxes.length > 0) {
      this.ratingCheckboxes.forEach((cb) =>
        cb.addEventListener("change", () => this.handleRatingChange())
      );
    }

    if (this.sortSelect) {
      this.sortSelect.addEventListener("change", (e) => {
        const val = e.target.value;
        if (val === "price-low") this.filters.sortBy = "price-asc";
        else if (val === "price-high") this.filters.sortBy = "price-desc";
        else if (val === "rating") this.filters.sortBy = "rating";
        else if (val === "popular") this.filters.sortBy = "popular";
        else this.filters.sortBy = "featured";
        this.applyFilters(true);
      });
    }

    if (this.paginationContainer) {
      this.paginationContainer.addEventListener("click", (e) => {
        const btn = e.target.closest(".page-link");
        if (!btn || btn.classList.contains("disabled")) return;

        const type = btn.dataset.type;
        if (type === "prev") {
          if (this.currentPage > 1) {
            this.currentPage--;
            this.renderTours();
            this.renderPagination();
          }
        } else if (type === "next") {
          if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.renderTours();
            this.renderPagination();
          }
        } else if (type === "page") {
          const page = parseInt(btn.dataset.page, 10);
          if (page !== this.currentPage && page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
            this.renderTours();
            this.renderPagination();
          }
        }
      });
    }

    // wishlist heart
    if (this.tourGrid) {
      this.tourGrid.addEventListener("click", (e) => {
        const heartIcon = e.target.closest(".tour-heart i");
        if (!heartIcon) return;
        heartIcon.classList.toggle("fa-regular");
        heartIcon.classList.toggle("fa-solid");
      });
    }

    // BOOK NOW
    if (this.bookButtons && this.bookButtons.length) {
      this.bookButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
          const card = btn.closest(".tour-card");
          if (!card) return;
          this.goToPackageBooking(card);
        });
      });
    }
  }

  // ======== FILTER HANDLERS ========
  handleCategoryChange(event) {
    this.filters.categories.clear();

    const allCb = this.categoryCheckboxes.find((cb) => cb.dataset.categories === "all");

    if (event?.target?.dataset?.categories === "all") {
      if (allCb) allCb.checked = true;
      this.categoryCheckboxes.forEach((cb) => {
        if (cb !== allCb) cb.checked = false;
      });
      this.filters.categories.clear();
      this.applyFilters(true);
      return;
    }

    let anySpecificChecked = false;
    this.categoryCheckboxes.forEach((cb) => {
      const val = cb.dataset.categories;
      if (val === "all") return;
      if (cb.checked) {
        anySpecificChecked = true;
        this.filters.categories.add(val);
      }
    });

    if (!anySpecificChecked) {
      this.filters.categories.clear();
      if (allCb) allCb.checked = true;
    } else if (allCb) {
      allCb.checked = false;
    }

    this.applyFilters(true);
  }

  handleDurationChange() {
    this.filters.durations.clear();
    this.durationCheckboxes.forEach((cb) => {
      const val = cb.dataset.duration;
      if (cb.checked && val) this.filters.durations.add(val);
    });
    this.applyFilters(true);
  }

  handleRatingChange() {
    let min = 0;
    this.ratingCheckboxes.forEach((cb) => {
      if (cb.checked) {
        const v = parseInt(cb.dataset.rating || "0", 10);
        if (!min || v < min) min = v;
      }
    });
    this.filters.minRating = min;
    this.applyFilters(true);
  }

  // ================= FILTER + SORT =================
  applyFilters(resetPage = false) {
    if (!this.tours.length) return;

    let result = [...this.tours];

    // destination_id filter
    if (this.destinationFilterId) {
      result = result.filter((t) => {
        const cardDestId = String(t.element.dataset.destinationId || "");
        return cardDestId === this.destinationFilterId;
      });
    }

    // price
    if (this.filters.maxPrice != null) {
      result = result.filter((t) => t.data.price <= this.filters.maxPrice);
    }

    // search
    if (this.filters.search) {
      const q = this.filters.search;
      result = result.filter((t) => {
        const title = t.data.title.toLowerCase();
        const loc = t.data.location.toLowerCase();
        const hotelName = (t.data.hotel?.name || "").toLowerCase();
        return title.includes(q) || loc.includes(q) || hotelName.includes(q);
      });
    }

    // categories
    if (this.filters.categories.size > 0) {
      result = result.filter((t) => this.filters.categories.has(t.data.category));
    }

    // durations
    if (this.filters.durations.size > 0) {
      result = result.filter((t) => this.filters.durations.has(t.data.durationBucket));
    }

    // ratings
    if (this.filters.minRating > 0) {
      const threshold = this.ratingThresholds[this.filters.minRating] || this.filters.minRating;
      result = result.filter((t) => t.data.rating >= threshold);
    }

    // sort
    switch (this.filters.sortBy) {
      case "price-asc":
        result.sort((a, b) => a.data.price - b.data.price);
        break;
      case "price-desc":
        result.sort((a, b) => b.data.price - a.data.price);
        break;
      case "rating":
        result.sort((a, b) => b.data.rating - a.data.rating);
        break;
      case "popular":
        result.sort(
          (a, b) =>
            (b.data.reviews || 0) - (a.data.reviews || 0) ||
            b.data.rating - a.data.rating ||
            a.originalIndex - b.originalIndex
        );
        break;
      case "featured":
      default:
        result.sort((a, b) => a.originalIndex - b.originalIndex);
        break;
    }

    this.filteredTours = result;
    this.totalPages = Math.max(1, Math.ceil(this.filteredTours.length / this.perPage));

    if (resetPage || this.currentPage > this.totalPages) this.currentPage = 1;

    this.updateToursCount();
    this.renderTours();
    this.renderPagination();
  }

  updateToursCount() {
    if (!this.tourCountSpan) return;
    this.tourCountSpan.textContent = this.filteredTours.length;
  }

  // ================= RENDER =================
  renderTours() {
    if (!this.tourGrid) return;

    this.tourGrid.innerHTML = "";

    if (this.filteredTours.length === 0) {
      const div = document.createElement("div");
      div.className = "no-results";
      div.style.cssText =
        "grid-column:1/-1;text-align:center;padding:60px 20px;color:#8a6fa3;";
      div.innerHTML = `
        <i class="fa-solid fa-compass" style="font-size:48px;margin-bottom:16px;opacity:0.6;"></i>
        <h3 style="margin:0 0 8px;font-size:18px;">No tours found</h3>
        <p style="margin:0 0 16px;font-size:14px;opacity:0.8;">Try adjusting your filters or search query.</p>
      `;
      this.tourGrid.appendChild(div);
      if (this.paginationContainer) this.paginationContainer.style.display = "none";
      return;
    }

    if (this.paginationContainer) this.paginationContainer.style.display = "flex";

    const start = (this.currentPage - 1) * this.perPage;
    const end = start + this.perPage;
    const pageItems = this.filteredTours.slice(start, end);

    pageItems.forEach((t) => {
      t.element.style.display = "flex";
      t.element.style.animation = "fade-in 0.4s ease";
      this.tourGrid.appendChild(t.element);
    });
  }

  // ================= PAGINATION RENDER =================
  renderPagination() {
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

  // ================= HERO SEARCH =================
  handleHeroSearchSubmit(e) {
    e.preventDefault();
    if (!this.heroForm) return;

    const destination = this.heroForm.querySelector('input[name="destination"]')?.value || "";
    const selects = this.heroForm.querySelectorAll(".form-select");
    const typeValue = selects[0]?.value || "";
    const guests = selects[1]?.value || "";
    const dateVal = this.heroForm.querySelector('input[type="date"]')?.value;

    this.filters.search = destination.trim().toLowerCase();
    if (this.sidebarSearchInput) this.sidebarSearchInput.value = destination;

    this.applyHeroTypeToCategories(typeValue);
    this.applyFilters(true);

    const info = [];
    if (destination) info.push(`Destination: ${destination}`);
    if (typeValue && typeValue !== "Activity") info.push(`Type: ${typeValue}`);
    if (dateVal) info.push(`Date: ${dateVal}`);
    if (guests) info.push(`Guests: ${guests}`);

    this.showToast(info.length ? `Search applied — ${info.join(" • ")}` : "Showing all tours");
  }

  applyHeroTypeToCategories(typeValue) {
    if (!this.categoryCheckboxes.length) return;

    const normalized = (typeValue || "").toLowerCase();
    const allCb = this.categoryCheckboxes.find((cb) => cb.dataset.categories === "all");

    this.filters.categories.clear();
    this.categoryCheckboxes.forEach((cb) => (cb.checked = false));

    if (!normalized || normalized === "activity") {
      if (allCb) allCb.checked = true;
      return;
    }

    this.categoryCheckboxes.forEach((cb) => {
      const val = (cb.dataset.categories || "").toLowerCase();
      let shouldCheck = false;

      if (normalized === "adventure" && val.includes("adventure")) shouldCheck = true;
      else if (normalized === "relax" && val.includes("beach")) shouldCheck = true;
      else if (normalized === "city" && val.includes("city")) shouldCheck = true;
      else if (normalized === "cultural" && val.includes("museum")) shouldCheck = true;

      if (shouldCheck) {
        cb.checked = true;
        this.filters.categories.add(cb.dataset.categories);
      }
    });

    if (!this.filters.categories.size && allCb) allCb.checked = true;
    else if (allCb) allCb.checked = false;
  }

  // ================= BUTTON RIPPLE =================
  createButtonRipple(e) {
    if (!this.heroButton || !this.rippleEl) return;

    const rect = this.heroButton.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;

    this.rippleEl.style.width = `${size}px`;
    this.rippleEl.style.height = `${size}px`;
    this.rippleEl.style.left = `${x}px`;
    this.rippleEl.style.top = `${y}px`;
    this.rippleEl.style.opacity = "0.8";
    this.rippleEl.style.transform = "scale(0)";
    this.rippleEl.style.transition = "none";

    requestAnimationFrame(() => {
      this.rippleEl.style.transition = "transform 0.6s ease-out, opacity 0.6s ease-out";
      this.rippleEl.style.transform = "scale(3)";
      this.rippleEl.style.opacity = "0";
    });
  }

  // ================= SCROLL ANIMATIONS =================
  setupScrollAnimations() {
    if (!("IntersectionObserver" in window)) {
      document.querySelectorAll("[data-animate]").forEach((el) => el.classList.add("is-visible"));
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { root: null, threshold: 0.1, rootMargin: "0px 0px -50px 0px" }
    );

    document.querySelectorAll("[data-animate]").forEach((el, idx) => {
      el.style.setProperty("--delay", `${idx * 100}ms`);
      observer.observe(el);
    });
  }

  // ================= TOAST =================
  showToast(message) {
    if (!this.toastEl) return;
    this.toastEl.textContent = message;
    this.toastEl.classList.add("show");
    clearTimeout(this.toastTimeout);
    this.toastTimeout = setTimeout(() => {
      this.toastEl.classList.remove("show");
    }, 2400);
  }

  // ================= BOOKING HELPERS =================
  buildPackageBookingParams(card) {
    if (!card) return "";

    const pkgId = card.dataset.packageId || "";
    const title = card.dataset.title || "";
    const city = card.dataset.city || "";
    const nights = parseInt(card.dataset.nights || "1", 10) || 1;
    const combo = card.dataset.combo || "";
    const currency = card.dataset.currency || "USD";

    // hotel
    const hotelName = (card.dataset.hotelName || "").trim();

    // flight
    const flightAirline = (card.dataset.flightAirline || "").trim();
    const flightNo = (card.dataset.flightNo || "").trim();
    const flightFrom = (card.dataset.flightFrom || "").trim();
    const flightTo = (card.dataset.flightTo || "").trim();
    const flightDepartAt = (card.dataset.flightDepart || "").trim();
    const flightArriveAt = (card.dataset.flightArrive || "").trim();

    const baseTotal = parseFloat(card.dataset.price || "0") || 0;
    const taxes = +(baseTotal * 0.15).toFixed(2);

    const isLoggedIn = window.TRAVELO && window.TRAVELO.isLoggedIn;
    const userId = window.TRAVELO?.userId || "";
    const userName = window.TRAVELO?.userName || "";
    const userEmail = window.TRAVELO?.userEmail || "";

    const params = new URLSearchParams();
    params.set("booking_type", "package");
    params.set("booking_status", "pending");

    if (pkgId) params.set("package_id", pkgId);
    if (title) params.set("package_title", title);
    if (city) params.set("package_city", city);
    if (combo) params.set("pkg_combo", combo);
    params.set("pkg_nights", nights.toString());

    // pricing
    params.set("amount_flight", "0");
    params.set("amount_hotel", "0");
    params.set("amount_package", baseTotal.toFixed(2));
    params.set("amount_taxes", taxes.toFixed(2));
    params.set("discount_amount", "0");
    params.set("currency", currency);

    // ✅ pass hotel name
    if (hotelName) params.set("hotel_name", hotelName);

    // ✅ pass flight details
    if (flightAirline) params.set("flight_airline", flightAirline);
    if (flightNo) params.set("flight_no", flightNo);
    if (flightFrom) params.set("flight_from", flightFrom);
    if (flightTo) params.set("flight_to", flightTo);
    if (flightDepartAt) params.set("flight_depart_at", flightDepartAt);
    if (flightArriveAt) params.set("flight_arrive_at", flightArriveAt);

    // ✅ also set trip dates from flight if available
    if (flightDepartAt) params.set("trip_start_date", flightDepartAt);
    if (flightArriveAt) params.set("trip_end_date", flightArriveAt);

    if (isLoggedIn && userId) params.set("user_id", userId);
    if (isLoggedIn && userName) params.set("user_name", userName);
    if (isLoggedIn && userEmail) params.set("user_email", userEmail);

    return params.toString();
  }

  goToPackageBooking(card) {
    if (!card) return;

    const isLoggedIn = window.TRAVELO && window.TRAVELO.isLoggedIn;
    if (!isLoggedIn) {
      const loginBtn = document.getElementById("btnLogin");
      if (loginBtn) loginBtn.click();
      return;
    }

    const qs = this.buildPackageBookingParams(card);
    if (!qs) return;
    window.location.href = `${this.bookingBaseUrl}?${qs}`;
  }

  // ================= MODAL =================
  initDetailsModal() {
    document.addEventListener("click", (e) => {
      const btn = e.target.closest(".view-details");
      if (!btn) return;
      const card = btn.closest(".tour-card");
      if (!card) return;
      this.openTourModal(card);
    });
  }

  formatDT(value) {
    if (!value) return "";
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleString(undefined, {
      year: "numeric",
      month: "short",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  buildAmenitiesHTML(flags) {
    const items = [
      { key: "has_wifi", icon: "fa-wifi", label: "Wi-Fi" },
      { key: "has_free_breakfast", icon: "fa-mug-saucer", label: "Free Breakfast" },
      { key: "has_parking", icon: "fa-square-parking", label: "Parking" },
      { key: "airport_shuttle", icon: "fa-van-shuttle", label: "Airport Shuttle" },
      { key: "has_sea_view", icon: "fa-water", label: "Sea View" },
      { key: "has_city_view", icon: "fa-city", label: "City View" },
      { key: "has_attached_bathroom", icon: "fa-bath", label: "Private Bathroom" },
      { key: "has_cctv", icon: "fa-video", label: "CCTV" },
      { key: "pay_at_hotel", icon: "fa-money-bill-wave", label: "Pay at Hotel" },
      { key: "couple_friendly", icon: "fa-heart", label: "Couple Friendly" },
      { key: "pet_friendly", icon: "fa-paw", label: "Pet Friendly" },
    ];

    const enabled = items.filter((it) => flags?.[it.key]);
    if (!enabled.length) {
      return `<div class="pkg-empty-note">No amenity data.</div>`;
    }

    return `
      <div class="pkg-amenities">
        ${enabled
          .map(
            (it) => `
          <div class="pkg-amenity">
            <i class="fa-solid ${it.icon}"></i>
            <span>${it.label}</span>
          </div>
        `
          )
          .join("")}
      </div>
    `;
  }

  openTourModal(card) {
    const title = card.querySelector(".tour-title")?.textContent.trim() || "Tour Package";
    const location = card.querySelector(".tour-location")?.textContent.trim() || "Destination";
    const ratingText = card.querySelector(".tour-meta-left span:first-child")?.textContent.trim() || "4.8 (120)";
    const daysText = card.querySelector(".tour-meta-left span:last-child")?.textContent.trim() || "5 Days";
    const priceText = card.querySelector(".tour-price")?.textContent.replace("$", "").trim() || "500";
    const imageSrc = card.querySelector(".tour-card-image img")?.src || "";
    const badgeText = card.querySelector(".tour-badge")?.textContent.trim() || "Featured";

    const ratingMatch = ratingText.match(/([\d.]+)/);
    const reviewsMatch = ratingText.match(/\((\d+)\)/);
    const daysMatch = daysText.match(/(\d+)/);

    const rating = ratingMatch ? ratingMatch[1] : "4.8";
    const reviews = reviewsMatch ? reviewsMatch[1] : "120";
    const days = daysMatch ? daysMatch[1] : "5";

    // hotel
    const hotelName = (card.dataset.hotelName || "").trim();
    const hotelRating = (card.dataset.hotelRating || "").trim();
    const hotelReviews = (card.dataset.hotelReviews || "").trim();
    const hotelPriceNight = (card.dataset.hotelPriceNight || "").trim();
    const hotelCurrency = (card.dataset.hotelCurrency || "USD").trim();

    const flags = {
      has_wifi: +card.dataset.hasWifi === 1,
      has_free_breakfast: +card.dataset.hasFreeBreakfast === 1,
      has_parking: +card.dataset.hasParking === 1,
      has_city_view: +card.dataset.hasCityView === 1,
      has_sea_view: +card.dataset.hasSeaView === 1,
      airport_shuttle: +card.dataset.airportShuttle === 1,
      has_attached_bathroom: +card.dataset.hasAttachedBathroom === 1,
      has_cctv: +card.dataset.hasCctv === 1,
      pay_at_hotel: +card.dataset.payAtHotel === 1,
      couple_friendly: +card.dataset.coupleFriendly === 1,
      pet_friendly: +card.dataset.petFriendly === 1,
    };

    // flight
    const flightAirline = (card.dataset.flightAirline || "").trim();
    const flightNo = (card.dataset.flightNo || "").trim();
    const flightFrom = (card.dataset.flightFrom || "").trim();
    const flightTo = (card.dataset.flightTo || "").trim();
    const flightDepartAt = (card.dataset.flightDepart || "").trim();
    const flightArriveAt = (card.dataset.flightArrive || "").trim();

    const hasFlight = !!(flightAirline || flightNo || flightFrom || flightTo || flightDepartAt || flightArriveAt);
    const hasHotel = !!hotelName;

    const flightBlock = hasFlight
      ? `
        <div class="pkg-block">
          <div class="pkg-block-title"><i class="fa-solid fa-plane"></i> Flight details</div>
          <div class="pkg-kv">
            <div><span>Airline</span><b>${flightAirline || "-"}</b></div>
            <div><span>Flight No</span><b>${flightNo || "-"}</b></div>
            <div><span>From</span><b>${flightFrom || "-"}</b></div>
            <div><span>To</span><b>${flightTo || "-"}</b></div>
            <div><span>Departure</span><b>${this.formatDT(flightDepartAt) || "-"}</b></div>
            <div><span>Arrival</span><b>${this.formatDT(flightArriveAt) || "-"}</b></div>
          </div>
        </div>
      `
      : `
        <div class="pkg-block">
          <div class="pkg-block-title"><i class="fa-solid fa-plane"></i> Flight details</div>
          <div class="pkg-empty-note">No flight data linked to this package.</div>
        </div>
      `;

    const hotelBlock = hasHotel
      ? `
        <div class="pkg-block">
          <div class="pkg-block-title"><i class="fa-solid fa-hotel"></i> Hotel</div>
          <div class="pkg-hotel-head">
            <div class="pkg-hotel-name">${hotelName}</div>
            <div class="pkg-hotel-sub">
              ${hotelRating ? `<span><i class="fa-solid fa-star"></i> ${hotelRating}</span>` : ""}
              ${hotelReviews ? `<span>(${hotelReviews} reviews)</span>` : ""}
              ${hotelPriceNight ? `<span class="pkg-hotel-price">${hotelCurrency} ${hotelPriceNight} / night</span>` : ""}
            </div>
          </div>
          ${this.buildAmenitiesHTML(flags)}
        </div>
      `
      : `
        <div class="pkg-block">
          <div class="pkg-block-title"><i class="fa-solid fa-hotel"></i> Hotel</div>
          <div class="pkg-empty-note">No hotel linked to this package.</div>
        </div>
      `;

    const modal = document.createElement("div");
    modal.className = "tour-modal";
    modal.innerHTML = `
      <div class="tour-modal-overlay"></div>
      <div class="tour-modal-dialog">
        <div class="tour-modal-main-column">
          <div class="tour-modal-hero">
            <img src="${imageSrc}" alt="${title}">
            <div class="tour-modal-hero-badge">
              <i class="fa-solid fa-suitcase-rolling"></i>
              <span>${badgeText}</span>
            </div>
            <button class="tour-modal-close" aria-label="Close">&times;</button>
          </div>

          <div class="tour-modal-main">
            <div class="tour-modal-location">${location}</div>
            <h2 class="tour-modal-title">${title}</h2>

            <div class="tour-modal-meta">
              <span><i class="fa-solid fa-star"></i>${rating} (${reviews} reviews)</span>
              <span><i class="fa-regular fa-clock"></i>${days} Days</span>
              <span><i class="fa-solid fa-tag"></i>Package Deal</span>
            </div>

            ${flightBlock}
            ${hotelBlock}

            <div class="pkg-block">
              <div class="pkg-block-title"><i class="fa-solid fa-circle-info"></i> Overview</div>
              <p class="tour-modal-text">
                This package combines travel essentials in one smooth plan. You’ll enjoy flexible sightseeing,
                curated stays, and a balanced itinerary so you can explore <b>${location}</b> at your own pace.
              </p>
            </div>

            <div class="pkg-block">
              <div class="pkg-block-title"><i class="fa-solid fa-check"></i> What's included</div>
              <div class="tour-included-list">
                ${hasFlight ? `
                  <div class="tour-included-item"><i class="fa-solid fa-plane-departure"></i><span>Round-trip flights</span></div>
                ` : `
                  <div class="tour-included-item"><i class="fa-solid fa-route"></i><span>Transport guidance</span></div>
                `}
                ${hasHotel ? `
                  <div class="tour-included-item"><i class="fa-solid fa-hotel"></i><span>Hotel accommodation</span></div>
                ` : `
                  <div class="tour-included-item"><i class="fa-solid fa-bed"></i><span>Stay recommendations</span></div>
                `}
                <div class="tour-included-item"><i class="fa-solid fa-bus"></i><span>Airport transfers</span></div>
                <div class="tour-included-item"><i class="fa-solid fa-person-hiking"></i><span>City tours & activities</span></div>
                <div class="tour-included-item"><i class="fa-solid fa-utensils"></i><span>Daily breakfast (if available)</span></div>
                <div class="tour-included-item"><i class="fa-solid fa-user-tie"></i><span>Professional tour guide</span></div>
              </div>
            </div>

            <div class="pkg-block">
              <div class="pkg-block-title"><i class="fa-solid fa-calendar-days"></i> Itinerary snapshot</div>
              <div class="tour-itinerary">
                <div class="tour-itinerary-item">
                  <div class="tour-itinerary-day">Day 1</div>
                  <div class="tour-itinerary-text">
                    ${hasFlight && flightDepartAt
                      ? `Flight departure: <b>${this.formatDT(flightDepartAt)}</b>.`
                      : `Arrival in ${location}.`}
                    Hotel check-in and free time to explore.
                  </div>
                </div>
                <div class="tour-itinerary-item">
                  <div class="tour-itinerary-day">Day 2</div>
                  <div class="tour-itinerary-text">
                    Guided city tour covering landmarks, hidden gems, and cultural highlights.
                  </div>
                </div>
                <div class="tour-itinerary-item">
                  <div class="tour-itinerary-day">Day 3</div>
                  <div class="tour-itinerary-text">
                    Optional excursions, shopping time, or relax at your hotel.
                  </div>
                </div>
                <div class="tour-itinerary-item">
                  <div class="tour-itinerary-day">Day ${days}</div>
                  <div class="tour-itinerary-text">
                    Final moments in ${location}.
                    ${hasFlight && flightArriveAt
                      ? `Return flight around <b>${this.formatDT(flightArriveAt)}</b>.`
                      : `Transfer to the airport and head back home.`}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <aside class="tour-modal-side">
          <div class="tour-price-box">
            <div class="tour-price-label">Starting from</div>
            <div class="tour-price-main">
              <span class="amount">$${priceText}</span>
              <span class="per">per person</span>
            </div>
          </div>

          <div class="tour-side-meta">
            <div class="tour-side-meta-item">
              <div class="tour-side-meta-label">Destination</div>
              <div class="tour-side-meta-value">${location}</div>
            </div>
            <div class="tour-side-meta-item">
              <div class="tour-side-meta-label">Duration</div>
              <div class="tour-side-meta-value">${days} days</div>
            </div>
            <div class="tour-side-meta-item">
              <div class="tour-side-meta-label">Hotel</div>
              <div class="tour-side-meta-value">${hotelName || "-"}</div>
            </div>
            <div class="tour-side-meta-item">
              <div class="tour-side-meta-label">Flight</div>
              <div class="tour-side-meta-value">${flightAirline ? `${flightAirline}${flightNo ? ` · ${flightNo}` : ""}` : "-"}</div>
            </div>
          </div>

          <div class="tour-modal-actions">
            <button class="tour-btn-primary">
              <i class="fa-solid fa-calendar-check"></i>
              Book this package
            </button>
            <button class="tour-btn-secondary">
              <i class="fa-regular fa-message"></i>
              Send an inquiry
            </button>
          </div>
        </aside>
      </div>
    `;

    document.body.appendChild(modal);
    document.body.style.overflow = "hidden";

    const close = () => {
      document.body.style.overflow = "";
      modal.remove();
    };

    modal.querySelector(".tour-modal-close")?.addEventListener("click", close);
    modal.querySelector(".tour-modal-overlay")?.addEventListener("click", close);
    modal.addEventListener("click", (ev) => {
      if (ev.target === modal) close();
    });

    modal.querySelector(".tour-btn-primary")?.addEventListener("click", () => {
      this.goToPackageBooking(card);
      close();
    });

    modal.querySelector(".tour-btn-secondary")?.addEventListener("click", () => {
      alert("Inquiry form can be opened here 💬");
    });
  }
}

// ===== INIT PAGE =====
document.addEventListener("DOMContentLoaded", () => {
  new TourPage();
});
