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
<<<<<<< HEAD
      minRating: 0,
=======
      minRatings: new Set(),
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
      sortBy: "featured",
    };

    // thresholds لتفسير 5 / 4 / 3 ستارز
    this.ratingThresholds = {
      5: 4.5,
      4: 4.0,
      3: 3.0,
    };

    this.cacheElements();
    this.buildToursData();
    this.initPriceSlider();
    this.updateSidebarCounts();
    this.applyFilters(true);
    this.bindEvents();
    this.setupScrollAnimations();
<<<<<<< HEAD
    this.initDetailsModal();
  }

  // ================= CACHE DOM =================
  cacheElements() {
    // hero form
=======

    // Hide spinner overlay when page is ready
    if (this.spinner) {
      this.spinner.style.display = "none";
    }
  }

  // ================= CACHE DOM ELEMENTS =================
  cacheElements() {
    // Spinner
    this.spinner = document.getElementById("spinner");

    // HERO
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    this.heroForm = document.getElementById("heroSearch");
    this.heroInputs = this.heroForm
      ? this.heroForm.querySelectorAll(".form-input, .form-select")
      : [];

    // نخلي الريبل على زر Search فقط (type="submit")
    this.heroButton = this.heroForm
      ? this.heroForm.querySelector('button[type="submit"]')
      : null;

    this.heroDateInput = this.heroForm
      ? this.heroForm.querySelector('input[type="date"]')
      : null;

<<<<<<< HEAD
    // sidebar
    this.sidebarSearchInput = document.getElementById("sidebarSearch");
=======
    // زر Reset في الهيرو
    this.heroResetBtn = this.heroForm
      ? this.heroForm.querySelector("#heroReset")
      : null;

    // SIDEBAR
    this.sidebar = document.querySelector(".sidebar");
    this.sidebarSections = this.sidebar
      ? Array.from(this.sidebar.querySelectorAll(".sidebar-section"))
      : [];
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f

    this.priceSlider = document.getElementById("priceSlider");
    this.priceRangeText = document.getElementById("priceRangeText");
    this.priceApplyBtn = document.getElementById("applyPrice");

<<<<<<< HEAD
    this.categoriesSection = document.getElementById("categoriesList");
    this.durationSection = document.getElementById("durationList");
    this.ratingSection = document.getElementById("ratingList");
=======
    // PRICE SECTION
    this.priceSection = this.sidebarSections.find((sec) =>
      sec
        .querySelector(".sidebar-title")
        ?.textContent.toLowerCase()
        .includes("filter by")
    );
    this.priceSlider = this.priceSection
      ? this.priceSection.querySelector('input[type="range"]')
      : null;

    // النص: Selected range: $0 - $150
    this.priceRangeTextEl = this.priceSection
      ? this.priceSection.querySelector("#priceRangeText")
      : null;

    this.priceApplyBtn = this.priceSection
      ? this.priceSection.querySelector(".btn-apply")
      : null;

    // CATEGORIES
    this.categoriesSection = this.sidebarSections.find((sec) =>
      sec
        .querySelector(".sidebar-title")
        ?.textContent.toLowerCase()
        .includes("categories")
    );

    // DURATION
    this.durationSection = this.sidebarSections.find((sec) =>
      sec
        .querySelector(".sidebar-title")
        ?.textContent.toLowerCase()
        .includes("duration")
    );

    // REVIEWS
    this.reviewsSection = this.sidebarSections.find((sec) =>
      sec
        .querySelector(".sidebar-title")
        ?.textContent.toLowerCase()
        .includes("reviews")
    );
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f

    this.categoryCheckboxes = this.categoriesSection
      ? Array.from(
          this.categoriesSection.querySelectorAll('input[type="checkbox"]')
        )
      : [];

    this.durationCheckboxes = this.durationSection
      ? Array.from(
          this.durationSection.querySelectorAll('input[type="checkbox"]')
<<<<<<< HEAD
        )
      : [];

    this.ratingCheckboxes = this.ratingSection
      ? Array.from(
          this.ratingSection.querySelectorAll('input[type="checkbox"]')
        )
      : [];

    // tours grid
    this.tourGrid = document.getElementById("tourGrid");
=======
        )
      : [];

    this.reviewCheckboxes = this.reviewsSection
      ? Array.from(
          this.reviewsSection.querySelectorAll('input[type="checkbox"]')
        )
      : [];

    // TOURS GRID
    this.tourGrid = document.querySelector(".tour-grid");
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    this.tourCards = this.tourGrid
      ? Array.from(this.tourGrid.querySelectorAll(".tour-card"))
      : [];

<<<<<<< HEAD
    // top bar
    this.tourCountSpan = document.getElementById("tourCount");
    this.sortSelect = document.getElementById("sortSelect");

    // pagination
    this.paginationContainer = document.querySelector(".pagination");

    // toast
=======
    // TOP BAR
    this.tourCountTitle = document.querySelector(".list-top-bar h2");

    this.sortSelect = document.querySelector(".list-top-meta select");

    // PAGINATION
    this.paginationContainer = document.querySelector(".pagination");

    // زر Reset في السايدبار (لو حطيتيه هناك)
    this.resetBtn = document.getElementById("resetFilters");

    // Toast
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    this.toastEl = document.createElement("div");
    this.toastEl.style.cssText =
      "position:fixed;bottom:20px;left:50%;transform:translateX(-50%) translateY(8px);background:#b049f1;color:#fff;padding:10px 18px;border-radius:999px;font-size:13px;box-shadow:0 8px 20px rgba(0,0,0,0.2);opacity:0;pointer-events:none;transition:opacity 0.25s,transform 0.25s;z-index:9999;";
    document.body.appendChild(this.toastEl);

<<<<<<< HEAD
    // ripple داخل زر السيرتش
=======
    // Ripple على زر Search
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
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

<<<<<<< HEAD
  // ================= BUILD DATA =================
=======
  // ================= BUILD TOURS DATA =================
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
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

<<<<<<< HEAD
      // bucket للمدة
      let durationBucket = "week";
      if (duration <= 1) durationBucket = "day";
      else if (duration <= 3) durationBucket = "weekend";
      else if (duration <= 7) durationBucket = "week";
      else durationBucket = "extended";
=======
      const durationSpan = card.querySelector(
        ".tour-meta-left span:nth-child(2)"
      );
      let duration = 0;
      if (durationSpan) {
        const durMatch = durationSpan.textContent.match(/(\d+)/);
        duration = durMatch ? parseInt(durMatch[1], 10) : 0;
      }

      const badge = card.querySelector(".tour-badge")
        ? card.querySelector(".tour-badge").textContent.trim()
        : "";

      const image = card.querySelector("img")?.src || "";

      const badgeLower = badge.toLowerCase();
      const titleLower = title.toLowerCase();
      const tags = [];
      if (badgeLower.includes("adventure")) tags.push("adventure");
      if (badgeLower.includes("city")) tags.push("city tours");
      if (badgeLower.includes("nature")) tags.push("nature");
      if (badgeLower.includes("relax")) tags.push("relax");
      if (badgeLower.includes("culture")) tags.push("culture");
      if (badgeLower.includes("popular")) tags.push("popular");
      if (badgeLower.includes("top rated")) tags.push("top rated");
      if (badgeLower.includes("bestseller")) tags.push("bestseller");
      if (titleLower.includes("beach")) tags.push("beaches");
      if (titleLower.includes("museum")) tags.push("museum tours");

      const continent = this.mapLocationToContinent(location);
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f

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
        },
      };
    });

    if (!this.tours.length) return;
    this.maxTourPrice = Math.max(...this.tours.map((t) => t.data.price));
  }

<<<<<<< HEAD
=======
  mapLocationToContinent(location) {
    const loc = location.toLowerCase();
    if (["australia"].includes(loc)) return "oceania";
    if (["united kingdom", "greece", "italy"].includes(loc)) return "europe";
    if (["japan", "china"].includes(loc)) return "asia";
    if (["brazil"].includes(loc)) return "america";
    if (["egypt"].includes(loc)) return "africa";
    return "other";
  }

>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
  // ================= PRICE SLIDER =================
  initPriceSlider() {
    if (!this.priceSlider || !this.maxTourPrice) return;

<<<<<<< HEAD
    const maxVal = Math.ceil(this.maxTourPrice / 50) * 50;

    this.priceSlider.min = "0";
    this.priceSlider.max = String(maxVal);
    this.priceSlider.value = String(maxVal);

    this.filters.maxPrice = maxVal;

    if (this.priceRangeText) {
      this.priceRangeText.textContent = `Selected range: $0 - $${maxVal}`;
=======
    this.priceSlider.setAttribute("min", "0");
    this.priceSlider.setAttribute(
      "max",
      String(Math.ceil(this.maxTourPrice / 50) * 50)
    );
    this.priceSlider.value = this.priceSlider.max;
    this.filters.maxPrice = parseFloat(this.priceSlider.value);

    if (this.priceRangeTextEl) {
      this.priceRangeTextEl.textContent = `Selected range: $0 - $${this.priceSlider.value}`;
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
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

<<<<<<< HEAD
  // ================= COUNTS (أرقام الفلاتر) =================
  updateSidebarCounts() {
    if (!this.tours.length) return;

    // ---- Categories ----
    const catCounts = {};
=======
  // ================= CHECKBOXES META =================
  initCheckboxMetadata() {
    // Categories
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    this.categoryCheckboxes.forEach((cb) => {
      const key = cb.dataset.categories;
      if (key) catCounts[key] = 0;
    });

<<<<<<< HEAD
    this.tours.forEach((t) => {
      const cat = t.data.category; // نفس data-category من الكارد
      if (catCounts[cat] !== undefined) {
        catCounts[cat]++;
      }
      if (catCounts["all"] !== undefined) {
        // all tours
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
=======
    // Duration
    this.durationCheckboxes.forEach((cb) => {
      const token = cb.dataset.duration || "";
      cb.dataset.filterValue = token;
    });

    // Reviews
    this.reviewCheckboxes.forEach((cb) => {
      const labelSpan = cb.closest("label")?.querySelector("span");
      const text = labelSpan
        ? labelSpan.textContent.replace(/\s+/g, " ").trim()
        : "";
      const match = text.match(/(\d)/);
      cb.dataset.filterValue = match ? match[1] : "0";
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    });
  }

  // ================= EVENTS =================
  bindEvents() {
<<<<<<< HEAD
    // hero focus / blur
=======
    // HERO FOCUS / BLUR
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
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

<<<<<<< HEAD
      // min date اليوم
=======
      // submit = Search
      this.heroForm.addEventListener("submit", (e) =>
        this.handleHeroSearchSubmit(e)
      );

      // Min date = اليوم
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
      if (this.heroDateInput) {
        const today = new Date().toISOString().split("T")[0];
        this.heroDateInput.min = today;
      }

<<<<<<< HEAD
      // ripple
=======
      // Ripple
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
      if (this.heroButton && this.rippleEl) {
        this.heroButton.addEventListener("click", (e) =>
          this.createButtonRipple(e)
        );
      }

<<<<<<< HEAD
      // submit hero (شكل فقط)
      this.heroForm.addEventListener("submit", (e) =>
        this.handleHeroSearchSubmit(e)
      );
    }

    // search في السايد بار
=======
      // زر Reset في الهيرو
      if (this.heroResetBtn) {
        this.heroResetBtn.addEventListener("click", (e) => {
          e.preventDefault();
          this.resetAllFilters();
        });
      }
    }

    // Sidebar search
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    if (this.sidebarSearchInput) {
      this.sidebarSearchInput.addEventListener("input", (e) => {
        this.filters.search = e.target.value.trim().toLowerCase();
        this.applyFilters(true);
      });
    }

<<<<<<< HEAD
    // price slider
=======
    // Price slider
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    if (this.priceSlider) {
      this.priceSlider.addEventListener("input", (e) => {
        const val = parseFloat(e.target.value);
        this.filters.maxPrice = val;
<<<<<<< HEAD
        if (this.priceRangeText) {
          this.priceRangeText.textContent = `Selected range: $0 - $${val}`;
=======
        if (this.priceRangeTextEl) {
          this.priceRangeTextEl.textContent = `Selected range: $0 - $${val}`;
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
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

<<<<<<< HEAD
    // categories
=======
    // Categories
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    if (this.categoryCheckboxes.length > 0) {
      this.categoryCheckboxes.forEach((cb) => {
        cb.addEventListener("change", () => this.handleCategoryChange());
      });
    }

<<<<<<< HEAD
    // duration
=======
    // Duration
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    if (this.durationCheckboxes.length > 0) {
      this.durationCheckboxes.forEach((cb) => {
        cb.addEventListener("change", () => this.handleDurationChange());
      });
    }

<<<<<<< HEAD
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
        else this.filters.sortBy = "featured";
        this.applyFilters(true);
      });
    }

    // pagination
=======
    // Reviews
    if (this.reviewCheckboxes.length > 0) {
      this.reviewCheckboxes.forEach((cb) => {
        cb.addEventListener("change", () => this.handleReviewChange());
      });
    }

    // Sort
if (this.sortSelect) {
  this.sortSelect.addEventListener("change", (e) => {
    const val = e.target.value;        

    if (val === "price-low") {
      this.filters.sortBy = "price-asc";
    } else if (val === "price-high") {
      this.filters.sortBy = "price-desc";
    } else if (val === "rating") {
      this.filters.sortBy = "rating";
    } else if (val === "popular") {
      this.filters.sortBy = "popular";
    } else {
      this.filters.sortBy = "featured";
    }
    this.applyFilters(true);
  });
}


    // Pagination
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
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
            this.currentPage--;
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

    // زر Reset في السايدبار (اختياري)
    if (this.resetBtn) {
      this.resetBtn.addEventListener("click", () => this.resetAllFilters());
    }
  }

<<<<<<< HEAD
  // ======== HANDLERS للفلاتر (تضمن إن الـ checkbox يرجع يتشيك/ينشال عادي) ========
=======
  // ================= FILTER HANDLERS =================
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
  handleCategoryChange() {
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
      // no specific filter => show كل شيء
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
      // ولا كاتيجوري محدد => رجع All Tours
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
<<<<<<< HEAD
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
=======
      const val = cb.dataset.filterValue;
      if (cb.checked && val) this.filters.durations.add(val);
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    });
    this.filters.minRating = min;
    this.applyFilters(true);
  }

<<<<<<< HEAD
  // ================= FILTER + SORT =================
=======
  handleReviewChange() {
    this.filters.minRatings.clear();
    this.reviewCheckboxes.forEach((cb) => {
      const val = parseInt(cb.dataset.filterValue || "0", 10);
      if (cb.checked && val > 0) {
        this.filters.minRatings.add(val);
      }
    });
    this.applyFilters(true);
  }

  // ================= RESET ALL =================
  resetAllFilters() {
    // reset filters object
    this.filters.search = "";
    this.filters.categories.clear();
    this.filters.durations.clear();
    this.filters.minRatings.clear();
    this.filters.sortBy = "featured";

    // hero form
    if (this.heroForm) {
      const destInput = this.heroForm.querySelector('input[name="destination"]');
      if (destInput) destInput.value = "";

      const typeSelect = this.heroForm.querySelector('select[name="type"]');
      if (typeSelect) typeSelect.selectedIndex = 0;

      const guestsSelect = this.heroForm.querySelector('select[name="guests"]');
      if (guestsSelect) guestsSelect.value = "0";

      if (this.heroDateInput) this.heroDateInput.value = "";
    }

    // sidebar search
    if (this.sidebarSearchInput) this.sidebarSearchInput.value = "";

    // price slider
    if (this.priceSlider && this.maxTourPrice) {
      this.priceSlider.value = this.priceSlider.max;
      this.filters.maxPrice = parseFloat(this.priceSlider.value);
      if (this.priceRangeTextEl) {
        this.priceRangeTextEl.textContent = `Selected range: $0 - $${this.priceSlider.value}`;
      }
      this.updatePriceSliderBackground();
    }

    // categories checkboxes: All Tours on, others off
    const allCb = this.categoryCheckboxes.find(
      (cb) => cb.dataset.filterValue === "all tours"
    );
    this.categoryCheckboxes.forEach((cb) => {
      if (cb === allCb) cb.checked = true;
      else cb.checked = false;
    });

    // durations
    this.durationCheckboxes.forEach((cb) => (cb.checked = false));

    // reviews
    this.reviewCheckboxes.forEach((cb) => (cb.checked = false));

    // sort select
    if (this.sortSelect) this.sortSelect.value = "featured";

    // page
    this.currentPage = 1;

    this.applyFilters(true);
    this.showToast("Filters reset");
  }

  // ================= APPLY FILTERS =================
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
  applyFilters(resetPage = false) {
    if (!this.tours.length) return;

    let result = [...this.tours];

<<<<<<< HEAD
    // price
=======
    // Max price
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    if (this.filters.maxPrice != null) {
      result = result.filter((t) => t.data.price <= this.filters.maxPrice);
    }

<<<<<<< HEAD
    // search
=======
    // Search
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    if (this.filters.search) {
      const q = this.filters.search;
      result = result.filter((t) => {
        const title = t.data.title.toLowerCase();
        const loc = t.data.location.toLowerCase();
        return title.includes(q) || loc.includes(q);
      });
    }

<<<<<<< HEAD
    // categories
=======
    // Categories
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    if (this.filters.categories.size > 0) {
      result = result.filter((t) =>
        this.filters.categories.has(t.data.category)
      );
    }

<<<<<<< HEAD
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
=======
    // Durations
    if (this.filters.durations.size > 0) {
      result = result.filter((t) => {
        const d = t.data.duration;
        let ok = false;
        this.filters.durations.forEach((token) => {
          if (token === "day" && d <= 1) ok = true;
          else if (token === "weekend" && d >= 2 && d <= 3) ok = true;
          else if (token === "week" && d >= 4 && d <= 7) ok = true;
          else if (token === "extended" && d >= 8) ok = true;
        });
        return ok;
      });
    }

    // Ratings
    if (this.filters.minRatings.size > 0) {
      const minNeeded = Math.min(...this.filters.minRatings);
      result = result.filter((t) => t.data.rating >= minNeeded);
    }

>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
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
        b.data.reviews - a.data.reviews ||
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
<<<<<<< HEAD
    if (!this.tourCountSpan) return;
    this.tourCountSpan.textContent = this.filteredTours.length;
  }

  // ================= RENDER (بدون ما نمسح الكروت من الدوم) =================
  renderTours() {
    if (!this.tourGrid) return;

    // نخفي/نظهر بس، ما نغيّر الـ innerHTML عشان الإيفنتس ما تضيع
    const start = (this.currentPage - 1) * this.perPage;
    const end = start + this.perPage;

    const idsInPage = new Set(
      this.filteredTours.slice(start, end).map((t) => t.originalIndex)
    );

    if (this.filteredTours.length === 0) {
      this.tourCards.forEach((card) => {
        card.style.display = "none";
      });
      if (!this.noResultsEl) {
        this.noResultsEl = document.createElement("div");
        this.noResultsEl.className = "no-results";
        this.noResultsEl.style.cssText =
          "grid-column:1/-1;text-align:center;padding:60px 20px;color:#8a6fa3;";
        this.noResultsEl.innerHTML = `
          <i class="fa-solid fa-compass" style="font-size:48px;margin-bottom:16px;opacity:0.6;"></i>
          <h3 style="margin:0 0 8px;font-size:18px;">No tours found</h3>
          <p style="margin:0 0 16px;font-size:14px;opacity:0.8;">Try adjusting your filters or search query.</p>
        `;
        this.tourGrid.appendChild(this.noResultsEl);
      }
      if (this.paginationContainer) {
        this.paginationContainer.style.display = "none";
      }
      return;
    }

    if (this.noResultsEl) {
      this.noResultsEl.remove();
      this.noResultsEl = null;
    }

=======
    if (!this.tourCountTitle) return;
    this.tourCountTitle.innerHTML = `<i class="fa-solid fa-suitcase-rolling"></i> ${this.filteredTours.length} Tours`;
  }

  // ================= RENDER TOURS =================
 renderTours() {
  if (!this.tourGrid) return;


  const existing = this.tourGrid.querySelector(".no-results");
  if (existing) existing.remove();

  this.tours.forEach((t) => {
    t.element.style.display = "none";
  });

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
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    if (this.paginationContainer) {
      this.paginationContainer.style.display = "none";
    }
<<<<<<< HEAD

    this.tours.forEach((t, idx) => {
      if (idsInPage.has(idx)) {
        t.element.style.display = "flex";
        t.element.style.animation = "fade-in 0.4s ease";
      } else {
        t.element.style.display = "none";
      }
    });
  }

=======
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
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
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

<<<<<<< HEAD
    // prev
=======
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    const prev = document.createElement("div");
    prev.className = "page-link";
    prev.innerHTML = '<i class="fa-solid fa-angle-left"></i>';
    prev.dataset.type = "prev";
    if (this.currentPage === 1) prev.classList.add("disabled");
    this.paginationContainer.appendChild(prev);

<<<<<<< HEAD
    // pages
=======
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    for (let p = 1; p <= this.totalPages; p++) {
      const pageEl = document.createElement("div");
      pageEl.className = "page-link";
      if (p === this.currentPage) pageEl.classList.add("active");
      pageEl.textContent = String(p);
      pageEl.dataset.type = "page";
      pageEl.dataset.page = String(p);
      this.paginationContainer.appendChild(pageEl);
    }

<<<<<<< HEAD
    // next
=======
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
    const next = document.createElement("div");
    next.className = "page-link";
    next.innerHTML = '<i class="fa-solid fa-angle-right"></i>';
    next.dataset.type = "next";
    if (this.currentPage === this.totalPages) next.classList.add("disabled");
    this.paginationContainer.appendChild(next);
  }

<<<<<<< HEAD
  // ================= HERO SEARCH =================
=======
  // ================= HERO SUBMIT (SEARCH) =================
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
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
      (cb) => cb.dataset.filterValue === "all tours"
    );

    // reset
    this.filters.categories.clear();
    this.categoryCheckboxes.forEach((cb) => (cb.checked = false));

    if (!normalized || normalized === "activity") {
      if (allCb) allCb.checked = true;
      return;
    }

    this.categoryCheckboxes.forEach((cb) => {
      const val = cb.dataset.filterValue || "";
      const simple = val.toLowerCase();
      let shouldCheck = false;

      if (normalized === "adventure" && simple.includes("adventure"))
        shouldCheck = true;
      else if (normalized === "relax" && simple.includes("relax"))
        shouldCheck = true;
      else if (normalized === "city" && simple.includes("city"))
        shouldCheck = true;
      else if (
        normalized === "cultural" &&
        (simple.includes("cultural") || simple.includes("culture"))
      )
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

<<<<<<< HEAD
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

=======
// ===== INIT PAGE =====
document.addEventListener("DOMContentLoaded", () => {
  new TourPage();
});

// ===== MODAL VIEW DETAILS =====
document.addEventListener("DOMContentLoaded", function () {
  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".view-details");
    if (!btn) return;

    const card = btn.closest(".tour-card");
    if (!card) return;

    openTourModal(card);
  });

  function openTourModal(card) {
    const titleEl = card.querySelector(".tour-title");
    const locationEl = card.querySelector(".tour-location");
    const ratingEl = card.querySelector(".tour-meta-left span:first-child");
    const daysEl = card.querySelector(".tour-meta-left span:last-child");
    const priceEl = card.querySelector(".tour-price");
    const imgEl = card.querySelector(".tour-card-image img");
    const badgeEl = card.querySelector(".tour-badge");

    const title = titleEl ? titleEl.textContent.trim() : "Tour Package";
    const location = locationEl ? locationEl.textContent.trim() : "Destination";
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

>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
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
      alert("Booking flow can be implemented here 🚀");
      close();
    });

    inquireBtn.addEventListener("click", () => {
      alert("Inquiry form can be opened here 💬");
      close();
    });
  }
<<<<<<< HEAD
}

document.addEventListener("DOMContentLoaded", () => {
  new TourPage();
=======
>>>>>>> 9101ed4f90685224fc3a22a643d12455dac81b8f
});
