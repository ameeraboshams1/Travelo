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
// Top Destinations Tabs 
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  const root = document.querySelector(".top-wrapper");
  if (!root) return;

  const stage = root.querySelector(".td-stage");
  const row = root.querySelector(".row.g-4");
  const prev = root.querySelector(".td-nav-btn.prev");
  const next = root.querySelector(".td-nav-btn.next");
  const dotsHost = document.getElementById("tdDots");

  if (!stage || !row || !prev || !next || !dotsHost) return;

  let page = 0;

  const allCards = () => Array.from(row.querySelectorAll(".destination-col"));
  const isFilteredOut = (el) =>
    el.classList.contains("hidden") || el.classList.contains("filtered-out");
  const visibleCards = () => allCards().filter((c) => !isFilteredOut(c));

  function getPageSize() {
    const w = window.innerWidth;
    if (w < 576) return 2;      // mobile
    if (w < 992) return 4;      // tablet
    return 6;                   // desktop
  }

  function buildDots(pages) {
    dotsHost.innerHTML = "";
    if (pages <= 1) return;

    for (let i = 0; i < pages; i++) {
      const dot = document.createElement("button");
      dot.type = "button";
      dot.className = "td-dot" + (i === page ? " active" : "");
      dot.setAttribute("aria-label", `Page ${i + 1}`);
      dot.addEventListener("click", () => render(i));
      dotsHost.appendChild(dot);
    }
  }

  function render(targetPage = page) {
    const pageSize = getPageSize();
    const vis = visibleCards();
    const pages = Math.max(1, Math.ceil(vis.length / pageSize));

    page = Math.max(0, Math.min(targetPage, pages - 1));

    // hide all
    allCards().forEach((c) => c.classList.add("td-page-hidden"));

    // show page slice
    const start = page * pageSize;
    const end = start + pageSize;
    vis.slice(start, end).forEach((c) => c.classList.remove("td-page-hidden"));

    // show/hide arrows + dots only when needed
    const needNav = pages > 1;
    prev.style.display = needNav ? "" : "none";
    next.style.display = needNav ? "" : "none";
    dotsHost.style.display = needNav ? "flex" : "none";

    prev.disabled = page === 0;
    next.disabled = page >= pages - 1;

    buildDots(pages);
  }

  prev.addEventListener("click", () => render(page - 1));
  next.addEventListener("click", () => render(page + 1));

  // لما تغيّري category (الفلتر بملف ثاني): رجّعي لأول صفحة
  root.querySelectorAll(".category-btn").forEach((btn) => {
    btn.addEventListener("click", () => setTimeout(() => render(0), 0));
  });

  // لو تغيّر حجم الشاشة
  window.addEventListener("resize", () => render(0));

  render(0);
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



document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('newsletterForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    try {
      const res = await fetch('subscribe.php', { method: 'POST', body: formData });
      const data = await res.json();

      if (data.status === 'ok') {
        showToast('Subscribed successfully! ✨', 'success');
        form.reset();
      } else if (data.status === 'exists') {
        showToast('This email is already subscribed.', 'error');
      } else if (data.status === 'invalid') {
        showToast('Please enter a valid email address ❌', 'error');
      } else if (data.status === 'mail_error') {
        showToast('Subscribed, but email failed to send ⚠️', 'error');
        console.log('Mailer error:', data.error);
      } else {
        showToast('Something went wrong ⚠️', 'error');
      }
    } catch (err) {
      showToast('Network error ⚠️', 'error');
      console.error(err);
    }
  });
});


document.addEventListener("DOMContentLoaded", () => {
  const btn = document.querySelector(".get-started");
  if (!btn) return;

  btn.addEventListener("click", (e) => {
    e.preventDefault();
    window.location.href = "login.html"; 
  });
});

