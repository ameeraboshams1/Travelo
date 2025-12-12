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
      minRating: 0, // 3 / 4 / 5 stars
      sortBy: "featured",
    };

    // thresholds لتفسير 5 / 4 / 3 ستارز
    this.ratingThresholds = {
      5: 4.5,
      4: 4.0,
      3: 3.0,
    };

    this.bookingBaseUrl = "booking.php";

    this.cacheElements();
    this.buildToursData();
    this.initPriceSlider();
    this.updateSidebarCounts();
    this.applyFilters(true);
    this.bindEvents();
    this.setupScrollAnimations();
    this.initDetailsModal();

    // نخفي السبنر بعد تحميل الصفحة
    if (this.spinner) {
      this.spinner.style.display = "none";
    }
  }

  // ================= CACHE DOM =================
  cacheElements() {
    // Spinner
    this.spinner = document.getElementById("spinner");

    // HERO
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

    // Sidebar search
    this.sidebarSearchInput = document.getElementById("sidebarSearch");

    // Price filter
    this.priceSlider = document.getElementById("priceSlider");
    this.priceRangeText = document.getElementById("priceRangeText");
    this.priceApplyBtn = document.getElementById("applyPrice");

    // Sections
    this.categoriesSection = document.getElementById("categoriesList");
    this.durationSection = document.getElementById("durationList");
    this.ratingSection = document.getElementById("ratingList");

    this.categoryCheckboxes = this.categoriesSection
      ? Array.from(
          this.categoriesSection.querySelectorAll('input[type="checkbox"]')
        )
      : [];

    this.durationCheckboxes = this.durationSection
      ? Array.from(
          this.durationSection.querySelectorAll('input[type="checkbox"]')
        )
      : [];

    this.ratingCheckboxes = this.ratingSection
      ? Array.from(
          this.ratingSection.querySelectorAll('input[type="checkbox"]')
        )
      : [];

    // Tours grid
    this.tourGrid = document.getElementById("tourGrid");
    this.tourCards = this.tourGrid
      ? Array.from(this.tourGrid.querySelectorAll(".tour-card"))
      : [];

    // BOOK NOW buttons على الكروت
    this.bookButtons = this.tourGrid
      ? Array.from(this.tourGrid.querySelectorAll(".book-package-btn"))
      : [];

    // Top bar
    this.tourCountSpan = document.getElementById("tourCount");
    this.sortSelect = document.getElementById("sortSelect");

    // Pagination
    this.paginationContainer = document.querySelector(".pagination");

    // Toast
    this.toastEl = document.createElement("div");
    this.toastEl.style.cssText =
      "position:fixed;bottom:20px;left:50%;transform:translateX(-50%) translateY(8px);background:#b049f1;color:#fff;padding:10px 18px;border-radius:999px;font-size:13px;box-shadow:0 8px 20px rgba(0,0,0,0.2);opacity:0;pointer-events:none;transition:opacity 0.25s,transform 0.25s;z-index:9999;";
    document.body.appendChild(this.toastEl);

    // Ripple داخل زر السيرتش (لو مش موجود)
    if (this.heroButton && !this.heroButton.querySelector(".btn-ripple")) {
      const ripple = document.createElement("span");
      ripple.className = "btn-ripple";
      Object.assign(ripple.style, {
        position: "absolute",
        borderRadius: "50%",
        transform: "scale(0)",
        width: "160px",
        height: "160px",
        background:
          "radial-gradient(circle, rgba(255,255,255,0.5), transparent)",
        opacity: "0",
        pointerEvents: "none",
        left: "0",
        top: "0",
      });
      this.heroButton.style.position = "relative";
      this.heroButton.appendChild(ripple);
      this.rippleEl = ripple;
    }
  }

  // ================= BUILD DATA =================
  buildToursData() {
    this.tours = this.tourCards.map((card, index) => {
      const title =
        card.querySelector(".tour-title")?.textContent.trim() || "";
      const location =
        card.querySelector(".tour-location")?.textContent.trim() || "";

      const price = parseFloat(card.dataset.price || "0") || 0;
      const rating = parseFloat(card.dataset.rating || "0") || 0;
      const duration = parseInt(card.dataset.duration || "0", 10) || 0;
      const category = (card.dataset.category || "adventure").toLowerCase();

      // bucket للمدة
      let durationBucket = "week";
      if (duration <= 1) durationBucket = "day";
      else if (duration <= 3) durationBucket = "weekend";
      else if (duration <= 7) durationBucket = "week";
      else durationBucket = "extended";

      // reviews من النص داخل الكارد
      const ratingSpan = card.querySelector(".tour-meta-left span:first-child");
      let reviews = 0;
      if (ratingSpan) {
        const txt = ratingSpan.textContent.trim();
        const match = txt.match(/\((\d+)\)/);
        if (match) reviews = parseInt(match[1], 10) || 0;
      }

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

  // ================= COUNTS (أرقام الفلاتر) =================
  updateSidebarCounts() {
    if (!this.tours.length) return;

    // ---- Categories ----
    const catCounts = {};
    this.categoryCheckboxes.forEach((cb) => {
      const key = cb.dataset.categories;
      if (key) catCounts[key] = 0;
    });

    this.tours.forEach((t) => {
      const cat = t.data.category;
      if (catCounts[cat] !== undefined) {
        catCounts[cat]++;
      }
      if (catCounts["all"] !== undefined) {
        catCounts["all"]++;
      }
    });

    this.categoryCheckboxes.forEach((cb) => {
      const key = cb.dataset.categories;
      const label = cb.closest("label");
      const span = label?.querySelector(".count");
      if (!span || !key) return;
      span.textContent = catCounts[key] !== undefined ? catCounts[key] : 0;
    });

    // ---- Duration ----
    const durCounts = {
      day: 0,
      weekend: 0,
      week: 0,
      extended: 0,
    };

    this.tours.forEach((t) => {
      const bucket = t.data.durationBucket;
      if (durCounts[bucket] !== undefined) durCounts[bucket]++;
    });

    this.durationCheckboxes.forEach((cb) => {
      const key = cb.dataset.duration;
      const label = cb.closest("label");
      const span = label?.querySelector(".count");
      if (span && durCounts[key] !== undefined) {
        span.textContent = durCounts[key];
      }
    });

    // ---- Ratings ----
    const ratingCounts = { 5: 0, 4: 0, 3: 0 };

    this.tours.forEach((t) => {
      const r = t.data.rating || 0;
      Object.entries(this.ratingThresholds).forEach(([k, threshold]) => {
        if (r >= threshold) ratingCounts[k]++;
      });
    });

    this.ratingCheckboxes.forEach((cb) => {
      const key = parseInt(cb.dataset.rating || "0", 10);
      const label = cb.closest("label");
      const span = label?.querySelector(".count");
      if (span && ratingCounts[key] !== undefined) {
        span.textContent = ratingCounts[key];
      }
    });
  }

  // ================= EVENTS =================
  bindEvents() {
    // hero focus / blur + min date + ripple + submit
    if (this.heroForm && this.heroInputs.length > 0) {
      this.heroInputs.forEach((input) => {
        input.addEventListener("focus", () => {
          this.heroForm.classList.add("is-active");
        });
        input.addEventListener("blur", () => {
          const stillFocused = Array.from(this.heroInputs).some(
            (el) => el === document.activeElement
          );
          if (!stillFocused) {
            this.heroForm.classList.remove("is-active");
          }
        });
      });

      // min date اليوم
      if (this.heroDateInput) {
        const today = new Date().toISOString().split("T")[0];
        this.heroDateInput.min = today;
      }

      // ripple
      if (this.heroButton && this.rippleEl) {
        this.heroButton.addEventListener("click", (e) =>
          this.createButtonRipple(e)
        );
      }

      // submit hero
      this.heroForm.addEventListener("submit", (e) =>
        this.handleHeroSearchSubmit(e)
      );
    }

    // search في السايد بار
    if (this.sidebarSearchInput) {
      this.sidebarSearchInput.addEventListener("input", (e) => {
        this.filters.search = e.target.value.trim().toLowerCase();
        this.applyFilters(true);
      });
    }

    // price slider
    if (this.priceSlider) {
      this.priceSlider.addEventListener("input", (e) => {
        const val = parseFloat(e.target.value);
        this.filters.maxPrice = val;
        if (this.priceRangeText) {
          this.priceRangeText.textContent = `Selected range: $0 - $${val}`;
        }
        this.updatePriceSliderBackground();
      });
    }

    if (this.priceApplyBtn) {
      this.priceApplyBtn.addEventListener("click", () => {
        this.applyFilters(true);
        this.showToast("Price filter applied");
      });
    }

    // categories
    if (this.categoryCheckboxes.length > 0) {
      this.categoryCheckboxes.forEach((cb) => {
        cb.addEventListener("change", (e) => this.handleCategoryChange(e));
      });
    }

    // duration
    if (this.durationCheckboxes.length > 0) {
      this.durationCheckboxes.forEach((cb) => {
        cb.addEventListener("change", () => this.handleDurationChange());
      });
    }

    // ratings
    if (this.ratingCheckboxes.length > 0) {
      this.ratingCheckboxes.forEach((cb) => {
        cb.addEventListener("change", () => this.handleRatingChange());
      });
    }

    // sort select
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

    // pagination
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

    // Toggle wishlist heart
    if (this.tourGrid) {
      this.tourGrid.addEventListener("click", (e) => {
        const heartIcon = e.target.closest(".tour-heart i");
        if (!heartIcon) return;
        heartIcon.classList.toggle("fa-regular");
        heartIcon.classList.toggle("fa-solid");
      });
    }

    // BOOK NOW من الكارد
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

  // ======== HANDLERS للفلاتر ========
  handleCategoryChange(event) {
    this.filters.categories.clear();

    const allCb = this.categoryCheckboxes.find(
      (cb) => cb.dataset.categories === "all"
    );

    // لو المستخدم لمس "All Tours"
    if (event && event.target && event.target.dataset.categories === "all") {
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
        return title.includes(q) || loc.includes(q);
      });
    }

    // categories
    if (this.filters.categories.size > 0) {
      result = result.filter((t) =>
        this.filters.categories.has(t.data.category)
      );
    }

    // durations
    if (this.filters.durations.size > 0) {
      result = result.filter((t) =>
        this.filters.durations.has(t.data.durationBucket)
      );
    }

    // ratings
    if (this.filters.minRating > 0) {
      const threshold =
        this.ratingThresholds[this.filters.minRating] || this.filters.minRating;
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
    this.totalPages = Math.max(
      1,
      Math.ceil(this.filteredTours.length / this.perPage)
    );

    if (resetPage || this.currentPage > this.totalPages) {
      this.currentPage = 1;
    }

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

      if (this.paginationContainer) {
        this.paginationContainer.style.display = "none";
      }
      return;
    }

    if (this.paginationContainer) {
      this.paginationContainer.style.display = "flex";
    }

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

    // prev
    const prev = document.createElement("div");
    prev.className = "page-link";
    prev.innerHTML = '<i class="fa-solid fa-angle-left"></i>';
    prev.dataset.type = "prev";
    if (this.currentPage === 1) prev.classList.add("disabled");
    this.paginationContainer.appendChild(prev);

    // pages
    for (let p = 1; p <= this.totalPages; p++) {
      const pageEl = document.createElement("div");
      pageEl.className = "page-link";
      if (p === this.currentPage) pageEl.classList.add("active");
      pageEl.textContent = String(p);
      pageEl.dataset.type = "page";
      pageEl.dataset.page = String(p);
      this.paginationContainer.appendChild(pageEl);
    }

    // next
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

    const destination =
      this.heroForm.querySelector('input[name="destination"]')?.value || "";
    const selects = this.heroForm.querySelectorAll(".form-select");
    const typeValue = selects[0]?.value || "";
    const guests = selects[1]?.value || "";
    const dateVal = this.heroForm.querySelector('input[type="date"]')?.value;

    // اربط السيرتش اللي فوق مع السيرتش الجانبي
    this.filters.search = destination.trim().toLowerCase();
    if (this.sidebarSearchInput) {
      this.sidebarSearchInput.value = destination;
    }

    // طبّق الفلتر حسب النوع
    this.applyHeroTypeToCategories(typeValue);

    this.applyFilters(true);

    const info = [];
    if (destination) info.push(`Destination: ${destination}`);
    if (typeValue && typeValue !== "Activity") info.push(`Type: ${typeValue}`);
    if (dateVal) info.push(`Date: ${dateVal}`);
    if (guests) info.push(`Guests: ${guests}`);

    this.showToast(
      info.length ? `Search applied — ${info.join(" • ")}` : "Showing all tours"
    );
  }

  applyHeroTypeToCategories(typeValue) {
    if (!this.categoryCheckboxes.length) return;
    const normalized = (typeValue || "").toLowerCase();
    const allCb = this.categoryCheckboxes.find(
      (cb) => cb.dataset.categories === "all"
    );

    // reset
    this.filters.categories.clear();
    this.categoryCheckboxes.forEach((cb) => (cb.checked = false));

    if (!normalized || normalized === "activity") {
      if (allCb) allCb.checked = true;
      return;
    }

    this.categoryCheckboxes.forEach((cb) => {
      const val = cb.dataset.categories || "";
      const simple = val.toLowerCase();
      let shouldCheck = false;

      if (normalized === "adventure" && simple.includes("adventure"))
        shouldCheck = true;
      else if (normalized === "relax" && simple.includes("beach"))
        shouldCheck = true; // تقريباً رحلات استرخاء
      else if (normalized === "city" && simple.includes("city"))
        shouldCheck = true;
      else if (normalized === "cultural" && simple.includes("museum"))
        shouldCheck = true;

      if (shouldCheck) {
        cb.checked = true;
        this.filters.categories.add(val);
      }
    });

    if (!this.filters.categories.size && allCb) {
      allCb.checked = true;
    } else if (allCb) {
      allCb.checked = false;
    }
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
      this.rippleEl.style.transition =
        "transform 0.6s ease-out, opacity 0.6s ease-out";
      this.rippleEl.style.transform = "scale(3)";
      this.rippleEl.style.opacity = "0";
    });
  }

  // ================= SCROLL ANIMATIONS =================
  setupScrollAnimations() {
    if (!("IntersectionObserver" in window)) {
      document
        .querySelectorAll("[data-animate]")
        .forEach((el) => el.classList.add("is-visible"));
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
      {
        root: null,
        threshold: 0.1,
        rootMargin: "0px 0px -50px 0px",
      }
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
    this.toastEl.style.opacity = "1";
    this.toastEl.style.transform = "translateX(-50%) translateY(0)";
    clearTimeout(this.toastTimeout);
    this.toastTimeout = setTimeout(() => {
      this.toastEl.style.opacity = "0";
      this.toastEl.style.transform = "translateX(-50%) translateY(8px)";
    }, 2500);
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

    const baseTotal = parseFloat(card.dataset.price || "0") || 0;
    const taxes = +(baseTotal * 0.15).toFixed(2);

    const isLoggedIn = window.TRAVELO && window.TRAVELO.isLoggedIn;
    const userId =
      window.TRAVELO && window.TRAVELO.userId ? window.TRAVELO.userId : "";
    const userName =
      window.TRAVELO && window.TRAVELO.userName ? window.TRAVELO.userName : "";
    const userEmail =
      window.TRAVELO && window.TRAVELO.userEmail
        ? window.TRAVELO.userEmail
        : "";

    const params = new URLSearchParams();

    params.set("booking_type", "package");
    params.set("booking_status", "pending");

    if (pkgId) params.set("package_id", pkgId);
    if (title) params.set("package_title", title);
    if (city) params.set("package_city", city);
    if (combo) params.set("pkg_combo", combo);
    params.set("pkg_nights", nights.toString());

    params.set("amount_flight", "0");
    params.set("amount_hotel", "0");
    params.set("amount_package", baseTotal.toFixed(2));
    params.set("amount_taxes", taxes.toFixed(2));
    params.set("discount_amount", "0");
    params.set("currency", currency);

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
      if (loginBtn) {
        loginBtn.click();
        return;
      }
    }

    const qs = this.buildPackageBookingParams(card);
    if (!qs) return;
    window.location.href = `${this.bookingBaseUrl}?${qs}`;
  }

  // ================= MODAL (VIEW DETAILS) =================
  initDetailsModal() {
    document.addEventListener("click", (e) => {
      const btn = e.target.closest(".view-details");
      if (!btn) return;

      const card = btn.closest(".tour-card");
      if (!card) return;

      this.openTourModal(card);
    });
  }

  openTourModal(card) {
    const titleEl = card.querySelector(".tour-title");
    const locationEl = card.querySelector(".tour-location");
    const ratingEl = card.querySelector(".tour-meta-left span:first-child");
    const daysEl = card.querySelector(".tour-meta-left span:last-child");
    const priceEl = card.querySelector(".tour-price");
    const imgEl = card.querySelector(".tour-card-image img");
    const badgeEl = card.querySelector(".tour-badge");

    const title = titleEl ? titleEl.textContent.trim() : "Tour Package";
    const location = locationEl
      ? locationEl.textContent.trim()
      : "Destination";
    const ratingText = ratingEl ? ratingEl.textContent.trim() : "4.8 (100)";
    const daysText = daysEl ? daysEl.textContent.trim() : "5 Days";
    const priceText = priceEl
      ? priceEl.textContent.replace("$", "").trim()
      : "500";
    const imageSrc = imgEl ? imgEl.src : "";
    const badgeText = badgeEl ? badgeEl.textContent.trim() : "Featured";

    const ratingMatch = ratingText.match(/([\d.]+)/);
    const reviewsMatch = ratingText.match(/\((\d+)\)/);
    const daysMatch = daysText.match(/(\d+)/);

    const rating = ratingMatch ? ratingMatch[1] : "4.8";
    const reviews = reviewsMatch ? reviewsMatch[1] : "120";
    const days = daysMatch ? daysMatch[1] : "5";

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
            <button class="tour-modal-close">&times;</button>
          </div>
          <div class="tour-modal-main">
            <div class="tour-modal-location">${location}</div>
            <h2 class="tour-modal-title">${title}</h2>
            <div class="tour-modal-meta">
              <span><i class="fa-solid fa-star"></i>${rating} (${reviews} reviews)</span>
              <span><i class="fa-regular fa-clock"></i>${days} Days</span>
              <span><i class="fa-solid fa-earth-europe"></i>Multi-activity tour</span>
            </div>

            <div>
              <div class="tour-modal-section-title">Overview</div>
              <p class="tour-modal-text">
                Enjoy a complete travel experience that combines comfortable flights, handpicked hotels,
                and immersive guided tours. This package is designed to balance sightseeing, culture,
                and free time so you can explore ${location} at your own pace.
              </p>
            </div>

            <div>
              <div class="tour-modal-section-title">What's included</div>
              <div class="tour-included-list">
                <div class="tour-included-item">
                  <i class="fa-solid fa-plane-departure"></i>
                  <span>Round-trip flights</span>
                </div>
                <div class="tour-included-item">
                  <i class="fa-solid fa-hotel"></i>
                  <span>Hotel accommodation</span>
                </div>
                <div class="tour-included-item">
                  <i class="fa-solid fa-bus"></i>
                  <span>Airport transfers</span>
                </div>
                <div class="tour-included-item">
                  <i class="fa-solid fa-person-hiking"></i>
                  <span>City tours & activities</span>
                </div>
                <div class="tour-included-item">
                  <i class="fa-solid fa-utensils"></i>
                  <span>Daily breakfast</span>
                </div>
                <div class="tour-included-item">
                  <i class="fa-solid fa-user-tie"></i>
                  <span>Professional tour guide</span>
                </div>
              </div>
            </div>

            <div>
              <div class="tour-modal-section-title">Itinerary snapshot</div>
              <div class="tour-itinerary">
                <div class="tour-itinerary-item">
                  <div class="tour-itinerary-day">Day 1</div>
                  <div class="tour-itinerary-text">
                    Arrival in ${location}, hotel check-in and free time to explore the local area.
                  </div>
                </div>
                <div class="tour-itinerary-item">
                  <div class="tour-itinerary-day">Day 2</div>
                  <div class="tour-itinerary-text">
                    Guided city tour covering top landmarks, hidden gems, and cultural highlights.
                  </div>
                </div>
                <div class="tour-itinerary-item">
                  <div class="tour-itinerary-day">Day 3</div>
                  <div class="tour-itinerary-text">
                    Optional excursions, free shopping time, or relaxing at your hotel.
                  </div>
                </div>
                <div class="tour-itinerary-item">
                  <div class="tour-itinerary-day">Day ${days}</div>
                  <div class="tour-itinerary-text">
                    Final moments in ${location}, transfer to the airport and flight back home.
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
              <div class="tour-side-meta-label">Rating</div>
              <div class="tour-side-meta-value">${rating} / 5</div>
            </div>
            <div class="tour-side-meta-item">
              <div class="tour-side-meta-label">Reviews</div>
              <div class="tour-side-meta-value">${reviews}+ travellers</div>
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

    modal.querySelector(".tour-modal-close").addEventListener("click", close);
    modal.querySelector(".tour-modal-overlay").addEventListener("click", close);
    modal.addEventListener("click", (ev) => {
      if (ev.target === modal) close();
    });

    const bookBtn = modal.querySelector(".tour-btn-primary");
    const inquireBtn = modal.querySelector(".tour-btn-secondary");

    bookBtn.addEventListener("click", () => {
      this.goToPackageBooking(card);
      close();
    });

    inquireBtn.addEventListener("click", () => {
      alert("Inquiry form can be opened here 💬");
      close();
    });
  }
}

// ===== INIT PAGE =====
document.addEventListener("DOMContentLoaded", () => {
  new TourPage();
});
