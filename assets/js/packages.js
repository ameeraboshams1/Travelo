

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
      destinations: new Set(),
      minRatings: new Set(),
      sortBy: "featured",
    };

    this.cacheElements();
    this.buildToursData();
    this.initPriceSlider();
    this.initCheckboxMetadata();
    this.applyFilters(true);
    this.bindEvents();
    this.setupScrollAnimations();
  }

    cacheElements() {
    
    this.heroForm = document.getElementById("heroSearch");
    this.heroInputs = this.heroForm
      ? this.heroForm.querySelectorAll(".form-input, .form-select")
      : [];
    this.heroButton = this.heroForm
      ? this.heroForm.querySelector(".form-button")
      : null;
    this.heroDateInput = this.heroForm
      ? this.heroForm.querySelector('input[type="date"]')
      : null;

    
    this.sidebar = document.querySelector(".sidebar");
    this.sidebarSections = this.sidebar
      ? Array.from(this.sidebar.querySelectorAll(".sidebar-section"))
      : [];

    this.sidebarSearchInput = this.sidebar
      ? this.sidebar.querySelector(".search-box input")
      : null;

    this.priceSection = this.sidebarSections.find((sec) =>
      sec.querySelector(".sidebar-title")?.textContent
        .toLowerCase()
        .includes("filter by")
    );
    this.priceSlider = this.priceSection
      ? this.priceSection.querySelector('input[type="range"]')
      : null;
    this.priceRangeMinLabel = this.priceSection
      ? this.priceSection.querySelector(".sidebar-range span:first-child")
      : null;
    this.priceRangeMaxLabel = this.priceSection
      ? this.priceSection.querySelector(".sidebar-range span:last-child")
      : null;
    this.priceApplyBtn = this.priceSection
      ? this.priceSection.querySelector(".btn-apply")
      : null;

    this.categoriesSection = this.sidebarSections.find((sec) =>
      sec.querySelector(".sidebar-title")?.textContent
        .toLowerCase()
        .includes("categories")
    );
    this.destinationsSection = this.sidebarSections.find((sec) =>
      sec.querySelector(".sidebar-title")?.textContent
        .toLowerCase()
        .includes("destinations")
    );
    this.reviewsSection = this.sidebarSections.find((sec) =>
      sec.querySelector(".sidebar-title")?.textContent
        .toLowerCase()
        .includes("reviews")
    );

    this.categoryCheckboxes = this.categoriesSection
      ? Array.from(
          this.categoriesSection.querySelectorAll('input[type="checkbox"]')
        )
      : [];
    this.destinationCheckboxes = this.destinationsSection
      ? Array.from(
          this.destinationsSection.querySelectorAll('input[type="checkbox"]')
        )
      : [];
    this.reviewCheckboxes = this.reviewsSection
      ? Array.from(
          this.reviewsSection.querySelectorAll('input[type="checkbox"]')
        )
      : [];

    
    this.tourGrid = document.querySelector(".tour-grid");
    this.tourCards = this.tourGrid
      ? Array.from(this.tourGrid.querySelectorAll(".tour-card"))
      : [];

    
    this.tourCountTitle = document.querySelector(".list-top-bar h2");
    this.sortSelect = document.querySelector(".list-top-meta select");

    
    this.paginationContainer = document.querySelector(".pagination");

    
    this.toastEl = document.createElement("div");
    this.toastEl.style.cssText =
      "position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#b049f1;color:#fff;padding:10px 18px;border-radius:999px;font-size:13px;box-shadow:0 8px 20px rgba(0,0,0,0.2);opacity:0;pointer-events:none;transition:opacity 0.25s,transform 0.25s;z-index:9999;";
    document.body.appendChild(this.toastEl);

    
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

    buildToursData() {
    this.tours = this.tourCards.map((card, index) => {
      const title = card.querySelector(".tour-title")?.textContent.trim() || "";
      const location =
        card.querySelector(".tour-location")?.textContent.trim() || "";
      const priceText =
        card.querySelector(".tour-price")?.textContent.trim() || "";
      const price = parseFloat(priceText.replace(/[^0-9.]/g, "")) || 0;

      const ratingSpan = card.querySelector(".tour-meta-left span:first-child");
      let rating = 0;
      let reviews = 0;
      if (ratingSpan) {
        const ratingMatch = ratingSpan.textContent.match(/([\d.]+)/);
        const reviewsMatch = ratingSpan.textContent.match(/\((\d+)\)/);
        rating = ratingMatch ? parseFloat(ratingMatch[1]) : 0;
        reviews = reviewsMatch ? parseInt(reviewsMatch[1], 10) : 0;
      }

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

      return {
        element: card,
        originalIndex: index,
        data: {
          title,
          location,
          price,
          rating,
          reviews,
          duration,
          badge,
          image,
          categories: tags,
          continent,
        },
      };
    });

    
    if (this.tours.length === 0) return;

    
    this.maxTourPrice = Math.max(...this.tours.map((t) => t.data.price));
  }

  mapLocationToContinent(location) {
    const loc = location.toLowerCase();
    if (["australia"].includes(loc)) return "oceania";
    if (["united kingdom", "greece", "italy"].includes(loc)) return "europe";
    if (["japan", "china"].includes(loc)) return "asia";
    if (["brazil"].includes(loc)) return "america";
    if (["egypt"].includes(loc)) return "africa";
    return "other";
  }

    initPriceSlider() {
    if (!this.priceSlider || !this.maxTourPrice) return;

    this.priceSlider.setAttribute("min", "0");
    this.priceSlider.setAttribute(
      "max",
      String(Math.ceil(this.maxTourPrice / 50) * 50)
    );
    this.priceSlider.value = this.priceSlider.max;

    this.filters.maxPrice = parseFloat(this.priceSlider.value);

    if (this.priceRangeMinLabel) {
      this.priceRangeMinLabel.textContent = `$0`;
    }
    if (this.priceRangeMaxLabel) {
      this.priceRangeMaxLabel.textContent = `$${this.priceSlider.value}`;
    }

    this.updatePriceSliderBackground();
  }

  updatePriceSliderBackground() {
    if (!this.priceSlider) return;
    const min = parseFloat(this.priceSlider.min);
    const max = parseFloat(this.priceSlider.max);
    const val = parseFloat(this.priceSlider.value);
    const percent = ((val - min) / (max - min)) * 100;
    this.priceSlider.style.background = `linear-gradient(to right, #b049f1 0%, #b049f1 ${percent}%, #e8dcff ${percent}%, #e8dcff 100%)`;
  }

    initCheckboxMetadata() {
    
    this.categoryCheckboxes.forEach((cb) => {
      const labelSpan = cb.closest("label")?.querySelector("span");
      const text = labelSpan
        ? labelSpan.textContent.replace(/\s+/g, " ").trim()
        : "";
      cb.dataset.filterValue = text.toLowerCase();
    });

    
    this.destinationCheckboxes.forEach((cb) => {
      const labelSpan = cb.closest("label")?.querySelector("span");
      let text = labelSpan
        ? labelSpan.textContent.replace(/\s+/g, " ").trim()
        : "";
      text = text.toLowerCase();
      cb.dataset.filterValue = text; 
    });

    
    this.reviewCheckboxes.forEach((cb) => {
      const labelSpan = cb.closest("label")?.querySelector("span");
      const text = labelSpan
        ? labelSpan.textContent.replace(/\s+/g, " ").trim()
        : "";
      const match = text.match(/(\d)/); 
      cb.dataset.filterValue = match ? match[1] : "0";
    });
  }

    bindEvents() {
    
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

      
      this.heroForm.addEventListener("submit", (e) =>
        this.handleHeroSearchSubmit(e)
      );

      
      if (this.heroDateInput) {
        const today = new Date().toISOString().split("T")[0];
        this.heroDateInput.min = today;
      }

      
      if (this.heroButton && this.rippleEl) {
        this.heroButton.addEventListener("click", (e) =>
          this.createButtonRipple(e)
        );
      }
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
        if (this.priceRangeMaxLabel) {
          this.priceRangeMaxLabel.textContent = `$${val}`;
        }
        this.updatePriceSliderBackground();
      });
    }

    
    if (this.priceApplyBtn) {
      this.priceApplyBtn.addEventListener("click", () => {
        this.applyFilters(true);
      });
    }

    
    if (this.categoryCheckboxes.length > 0) {
      this.categoryCheckboxes.forEach((cb) => {
        cb.addEventListener("change", () => this.handleCategoryChange());
      });
    }

    
    if (this.destinationCheckboxes.length > 0) {
      this.destinationCheckboxes.forEach((cb) => {
        cb.addEventListener("change", () => this.handleDestinationChange());
      });
    }

    
    if (this.reviewCheckboxes.length > 0) {
      this.reviewCheckboxes.forEach((cb) => {
        cb.addEventListener("change", () => this.handleReviewChange());
      });
    }

    
    if (this.sortSelect) {
      this.sortSelect.addEventListener("change", (e) => {
        const txt = e.target.value.toLowerCase();
        if (txt.includes("low to high")) this.filters.sortBy = "price-asc";
        else if (txt.includes("high to low")) this.filters.sortBy = "price-desc";
        else if (txt.includes("rating")) this.filters.sortBy = "rating";
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
  }

    handleCategoryChange() {
    this.filters.categories.clear();

    const allCb = this.categoryCheckboxes.find(
      (cb) => cb.dataset.filterValue === "all tours"
    );

    let anySpecificChecked = false;

    this.categoryCheckboxes.forEach((cb) => {
      const val = cb.dataset.filterValue;
      if (val === "all tours") return;
      if (cb.checked) {
        anySpecificChecked = true;
        this.filters.categories.add(val);
      }
    });

    if (!anySpecificChecked) {
      this.filters.categories.clear();
      if (allCb) allCb.checked = true;
    } else {
      if (allCb) allCb.checked = false;
    }

    this.applyFilters(true);
  }

  handleDestinationChange() {
    this.filters.destinations.clear();
    this.destinationCheckboxes.forEach((cb) => {
      const val = cb.dataset.filterValue; 
      if (cb.checked) {
        this.filters.destinations.add(val);
      }
    });
    this.applyFilters(true);
  }

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

    applyFilters(resetPage = false) {
    if (!this.tours.length) return;

    let result = [...this.tours];

    
    if (this.filters.maxPrice != null) {
      result = result.filter((t) => t.data.price <= this.filters.maxPrice);
    }

    
    if (this.filters.search) {
      const q = this.filters.search;
      result = result.filter((t) => {
        const title = t.data.title.toLowerCase();
        const loc = t.data.location.toLowerCase();
        return title.includes(q) || loc.includes(q);
      });
    }

    
    if (this.filters.categories.size > 0) {
      result = result.filter((t) => {
        const tags = t.data.categories;
        if (!tags || !tags.length) return false;
        for (const c of this.filters.categories) {
          if (tags.includes(c)) return true;
        }
        return false;
      });
    }

    
    if (this.filters.destinations.size > 0) {
      result = result.filter((t) => {
        const cont = t.data.continent;
        for (const d of this.filters.destinations) {
          if (cont === d) return true;
        }
        return false;
      });
    }

    
    if (this.filters.minRatings.size > 0) {
      const minNeeded = Math.min(...this.filters.minRatings);
      result = result.filter((t) => t.data.rating >= minNeeded);
    }

    
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
    if (!this.tourCountTitle) return;
    this.tourCountTitle.textContent = `${this.filteredTours.length} Tours`;
  }

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
    });
  }

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
    if (this.currentPage === 1) {
      prev.classList.add("disabled");
    }
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
    if (this.currentPage === this.totalPages) {
      next.classList.add("disabled");
    }
    this.paginationContainer.appendChild(next);
  }

    handleHeroSearchSubmit(e) {
    e.preventDefault();
    if (!this.heroForm) return;

    const destination =
      this.heroForm.querySelector('.form-input[type="text"]')?.value || "";
    const selects = this.heroForm.querySelectorAll(".form-select");
    const type = selects[0]?.value || "";
    const guests = selects[1]?.value || "";
    const dateVal = this.heroForm.querySelector('input[type="date"]')?.value;

    console.log("Hero search:", {
      destination,
      type,
      date: dateVal,
      guests,
    });

    this.showToast("Search submitted successfully ✈️");
  }

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
}

document.addEventListener("DOMContentLoaded", () => {
  new TourPage();
});
document.addEventListener('DOMContentLoaded', function () {
    
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.view-details');
      if (!btn) return;

      const card = btn.closest('.tour-card');
      if (!card) return;

      openTourModal(card);
    });

    function openTourModal(card) {
      const titleEl = card.querySelector('.tour-title');
      const locationEl = card.querySelector('.tour-location');
      const ratingEl = card.querySelector('.tour-meta-left span:first-child');
      const daysEl = card.querySelector('.tour-meta-left span:last-child');
      const priceEl = card.querySelector('.tour-price');
      const imgEl = card.querySelector('.tour-card-image img');
      const badgeEl = card.querySelector('.tour-badge');

      const title = titleEl ? titleEl.textContent.trim() : 'Tour Package';
      const location = locationEl ? locationEl.textContent.trim() : 'Destination';
      const ratingText = ratingEl ? ratingEl.textContent.trim() : '4.8 (100)';
      const daysText = daysEl ? daysEl.textContent.trim() : '5 Days';
      const priceText = priceEl ? priceEl.textContent.replace('$', '').trim() : '500';
      const imageSrc = imgEl ? imgEl.src : '';
      const badgeText = badgeEl ? badgeEl.textContent.trim() : 'Featured';

      
      const ratingMatch = ratingText.match(/([\d.]+)/);
      const reviewsMatch = ratingText.match(/\((\d+)\)/);
      const daysMatch = daysText.match(/(\d+)/);

      const rating = ratingMatch ? ratingMatch[1] : '4.8';
      const reviews = reviewsMatch ? reviewsMatch[1] : '120';
      const days = daysMatch ? daysMatch[1] : '5';

      const modal = document.createElement('div');
      modal.className = 'tour-modal';
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
      document.body.style.overflow = 'hidden';

      const close = () => {
        document.body.style.overflow = '';
        modal.remove();
      };

      modal.querySelector('.tour-modal-close').addEventListener('click', close);
      modal.querySelector('.tour-modal-overlay').addEventListener('click', close);

      modal.addEventListener('click', (ev) => {
        if (ev.target === modal) close();
      });

      const bookBtn = modal.querySelector('.tour-btn-primary');
      const inquireBtn = modal.querySelector('.tour-btn-secondary');

      bookBtn.addEventListener('click', () => {
        alert('Booking flow can be implemented here 🚀');
        close();
      });

      inquireBtn.addEventListener('click', () => {
        alert('Inquiry form can be opened here 💬');
        close();
      });
    }
  });