// ===============================
// Testimonials Slider
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  const slides = document.querySelectorAll(".testimonial-slide");
  const prevBtn = document.querySelector(".prev-btn");
  const nextBtn = document.querySelector(".next-btn");
  const paginationContainer = document.querySelector(".slider-pagination");

  if (!slides.length || !prevBtn || !nextBtn || !paginationContainer) return;

  let currentSlide = 0;

  function updateDots(index) {
    const dots = document.querySelectorAll(".pagination-dot");
    dots.forEach((dot, dotIndex) => {
      dot.classList.toggle("active", dotIndex === index);
    });
  }

  function showSlide(index) {
    slides.forEach((slide, slideIndex) => {
      slide.classList.remove("active-slide");
      if (slideIndex === index) {
        void slide.offsetWidth;
        slide.classList.add("active-slide");
      }
    });
    updateDots(index);
  }

  function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
  }

  function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(currentSlide);
  }

  function createDots() {
    paginationContainer.innerHTML = "";
    slides.forEach((_, index) => {
      const dot = document.createElement("button");
      dot.classList.add("pagination-dot");
      dot.addEventListener("click", () => showSlide(index));
      paginationContainer.appendChild(dot);
    });
  }

  createDots();
  showSlide(currentSlide);

  nextBtn.addEventListener("click", nextSlide);
  prevBtn.addEventListener("click", prevSlide);
});

// ===============================
// Top Destinations Carousel (if you have td-nav-btn prev/next)
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  const style = document.createElement("style");
  style.textContent = `
    .td-card { transition: all 0.4s ease; opacity: 0; transform: translateX(30px); }
    .td-card.show { opacity: 1 !important; transform: translateX(0) !important; }
    .td-fix-justify { justify-content: flex-start !important; }
  `;
  document.head.appendChild(style);

  let container =
    document.querySelector(".td-cards .row.g-4") ||
    document.querySelector(".td-cards") ||
    document.querySelector(".row.g-4") ||
    document.querySelector(".cards-row");

  const prevBtn = document.querySelector(".td-nav-btn.prev");
  const nextBtn = document.querySelector(".td-nav-btn.next");

  // لو ما عندك هالأزرار، ما رح يشتغل هالجزء (مو مشكلة)
  if (!container || !prevBtn || !nextBtn) return;

  container.classList.add("td-fix-justify");
  container.classList.remove(
    "justify-content-between",
    "justify-content-around",
    "justify-content-evenly",
    "justify-content-center"
  );
  container.style.justifyContent = "flex-start";

  const isHeadingLike = (el) =>
    el.matches("h1,h2,h3,h4,h5,h6,.section-title,[role='heading'],.td-static");

  function autoMarkCardsIfNeeded(list) {
    if (list.length > 0) return list;

    const candidates = Array.from(container.children).filter((el) => {
      if (isHeadingLike(el)) return false;
      const cls = el.className || "";
      const looksLikeCol = /(col-|^col\s|^col$|col-auto)/.test(cls);
      const hasCardInside = el.querySelector(".card, [data-card], .td-card");
      return looksLikeCol && hasCardInside;
    });

    candidates.forEach((el) => el.setAttribute("data-td-card", "1"));
    return candidates;
  }

  function collectCards() {
    let list = Array.from(
      container.querySelectorAll(".td-card, [data-td-card='1']")
    ).filter(
      (el) =>
        !isHeadingLike(el) &&
        !el.classList.contains("td-seeall") &&
        !el.closest(".td-seeall")
    );

    list = autoMarkCardsIfNeeded(list);
    return Array.from(new Set(list));
  }

  let cards = collectCards();
  const pageSize = 3;
  let startIndex = 0;

  function render(direction = "next") {
    cards = collectCards();
    const total = cards.length;
    if (!total) return;

    if (startIndex >= total) startIndex = Math.max(0, total - pageSize);

    cards.forEach((c) => {
      c.classList.remove("show");
      c.style.display = "none";
      c.style.transform =
        direction === "next" ? "translateX(30px)" : "translateX(-30px)";
      c.style.opacity = "0";
    });

    const end = Math.min(startIndex + pageSize, total);
    for (let i = startIndex; i < end; i++) {
      const card = cards[i];
      card.style.display = "";
      requestAnimationFrame(() =>
        setTimeout(() => card.classList.add("show"), 30)
      );
    }

    prevBtn.disabled = startIndex === 0;
    nextBtn.disabled = end >= total;
    prevBtn.style.opacity = prevBtn.disabled ? "0.5" : "1";
    nextBtn.style.opacity = nextBtn.disabled ? "0.5" : "1";
  }

  function goNext() {
    if (startIndex + pageSize < cards.length) {
      startIndex += pageSize;
      render("next");
    }
  }

  function goPrev() {
    if (startIndex > 0) {
      startIndex -= pageSize;
      render("prev");
    }
  }

  nextBtn.addEventListener("click", goNext);
  prevBtn.addEventListener("click", goPrev);

  document.addEventListener("keydown", (e) => {
    if (e.key === "ArrowRight") {
      e.preventDefault();
      goNext();
    }
    if (e.key === "ArrowLeft") {
      e.preventDefault();
      goPrev();
    }
  });

  const observer = new MutationObserver(() => {
    const oldLen = cards.length;
    cards = collectCards();
    if (cards.length !== oldLen && startIndex >= cards.length) {
      startIndex = Math.max(0, cards.length - pageSize);
    }
    render();
  });
  observer.observe(container, { childList: true, subtree: true });

  render();
});

// ===============================
// Login / Signup with spinner
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  const spinner = document.getElementById("spinner");

  const btnLogin = document.getElementById("btnLogin");
  if (btnLogin && spinner) {
    btnLogin.addEventListener("click", function (e) {
      e.preventDefault();
      spinner.classList.add("show");
      setTimeout(function () {
        window.location.href = "login.html";
      }, 600);
    });
  }

  const btnSignup = document.getElementById("btnLogin1");
  if (btnSignup && spinner) {
    btnSignup.addEventListener("click", function (e) {
      e.preventDefault();
      spinner.classList.add("show");
      setTimeout(function () {
        window.location.href = "signup.html";
      }, 600);
    });
  }
});

// ===============================
// Top Destinations Tabs + Modal (from data-* on button)
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  // ---- Tabs filtering ----
  const tabs = document.querySelectorAll(".category-btn");
  const cols = document.querySelectorAll(".destination-col");

  if (tabs.length && cols.length) {
    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        tabs.forEach((t) => t.classList.remove("active"));
        tab.classList.add("active");

        const cat = (tab.dataset.category || "all").toLowerCase();

        cols.forEach((col) => {
          const itemCat = (col.dataset.category || "").toLowerCase();

          if (cat === "all") {
            col.classList.remove("hidden", "filtered-out");
            col.style.display = "";
          } else {
            const show = itemCat === cat;
            col.classList.toggle("hidden", !show);
            col.classList.toggle("filtered-out", !show);
            col.style.display = show ? "" : "none";
          }
        });
      });
    });
  }

  // ---- Modal ----
  const modalOverlay = document.getElementById("destinationModal");
  const modalCloseBtn = document.getElementById("destinationModalClose");

  const modalImg = document.getElementById("modalDestinationImage");
  const modalTitle = document.getElementById("modalDestinationTitle");
  const modalLocation = document.getElementById("modalDestinationLocation");
  const modalDesc = document.getElementById("modalDestinationDesc");
  const modalVisitors = document.getElementById("modalVisitors");
  const modalSeason = document.getElementById("modalSeason");
  const modalPrice = document.getElementById("modalPrice");

  // اختياري: بيانات إضافية حسب الاسم (لو مش موجود، بحط defaults)
  const detailsByName = {
    Tokyo: { visitors: "14M / year", season: "Mar – Apr (Sakura)" },
    Rome: { visitors: "9.8M / year", season: "Apr – Jun" },
    Barcelona: { visitors: "11M / year", season: "May – Sep" },
    Bangkok: { visitors: "22M / year", season: "Nov – Feb" },
    Sydney: { visitors: "10M / year", season: "Dec – Feb" },
    Toronto: { visitors: "8M / year", season: "May – Sep" },
  };

  function openDestinationModalFromBtn(btn) {
    if (!modalOverlay) return;

    const name = btn.dataset.name || "Destination";
    const location = btn.dataset.location || "";
    const imgSrc = btn.dataset.image || "";
    const desc = btn.dataset.desc || "Discover this destination with Travelo.";
    const price = btn.dataset.price || "$ ---";

    if (modalTitle) modalTitle.textContent = name;
    if (modalLocation) modalLocation.textContent = location;
    if (modalDesc) modalDesc.textContent = desc;

    if (modalImg) {
      modalImg.src = imgSrc;
      modalImg.alt = name;
    }

    if (modalPrice) modalPrice.textContent = price;

    const extra = detailsByName[name] || {
      visitors: "Millions / year",
      season: "All year",
    };
    if (modalVisitors) modalVisitors.textContent = extra.visitors;
    if (modalSeason) modalSeason.textContent = extra.season;

    modalOverlay.classList.add("show");
    document.body.classList.add("no-scroll");
  }

  function closeDestinationModal() {
    if (!modalOverlay) return;
    modalOverlay.classList.remove("show");
    document.body.classList.remove("no-scroll");
  }

  // Delegation: يشتغل حتى لو الكروت PHP
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".view-btn");
    if (!btn) return;
    openDestinationModalFromBtn(btn);
  });

  if (modalCloseBtn) modalCloseBtn.addEventListener("click", closeDestinationModal);

  if (modalOverlay) {
    modalOverlay.addEventListener("click", (e) => {
      if (e.target === modalOverlay) closeDestinationModal();
    });
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modalOverlay?.classList.contains("show")) {
      closeDestinationModal();
    }
  });

  // ---- See all link (destination.php) + spinner ----
  const seeAllBtn = document.querySelector(".see-all-link");
  const globalSpinner = document.getElementById("spinner");

  if (seeAllBtn && globalSpinner) {
    seeAllBtn.addEventListener("click", function (e) {
      e.preventDefault();
      globalSpinner.classList.add("show");
      setTimeout(function () {
        window.location.href = "destination.php";
      }, 600);
    });
  }
});

// ===============================
// User menu toggle (avatar dropdown)
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.getElementById("userMenuToggle");
  const menu = document.getElementById("userMenu");

  if (toggle && menu) {
    toggle.addEventListener("click", (e) => {
      e.stopPropagation();
      menu.classList.toggle("show");
    });

    document.addEventListener("click", () => {
      menu.classList.remove("show");
    });
  }
});
