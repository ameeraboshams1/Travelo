document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.testimonial-slide');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const paginationContainer = document.querySelector('.slider-pagination');

    let currentSlide = 0;

    function showSlide(index) {
        slides.forEach((slide, slideIndex) => {
            slide.classList.remove('active-slide');
            if (slideIndex === index) {
                void slide.offsetWidth;
                slide.classList.add('active-slide');
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
        slides.forEach((_, index) => {
            const dot = document.createElement('button');
            dot.classList.add('pagination-dot');
            dot.addEventListener('click', () => {
                showSlide(index);
            });
            paginationContainer.appendChild(dot);
        });
    }

    function updateDots(index) {
        const dots = document.querySelectorAll('.pagination-dot');
        dots.forEach((dot, dotIndex) => {
            if (dotIndex === index) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    createDots();
    showSlide(currentSlide);

    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
});


document.addEventListener("DOMContentLoaded", function () {

  const style = document.createElement("style");
  style.textContent = `
    .td-card {
      transition: all 0.4s ease;
      opacity: 0;
      transform: translateX(30px);
    }
    .td-card.show {
      opacity: 1 !important;
      transform: translateX(0) !important;
    }
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

  if (!container || !prevBtn || !nextBtn) {
    console.warn("تأكدي من وجود الحاوية (.row.g-4 أو .td-cards) وأزرار (.td-nav-btn.prev / .td-nav-btn.next).");
    return;
  }


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
    const candidates = Array.from(container.children).filter(el => {
      if (isHeadingLike(el)) return false;
      const cls = el.className || "";
      const looksLikeCol = /(col-|^col\s|^col$|col-auto)/.test(cls);
      const hasCardInside = el.querySelector(".card, [data-card], .td-card");
      return looksLikeCol && hasCardInside;
    });
    candidates.forEach(el => el.setAttribute("data-td-card", "1"));
    return candidates;
  }

  function collectCards() {
    let list = Array.from(container.querySelectorAll(".td-card, [data-td-card='1']"))
      .filter(el => !isHeadingLike(el) && !el.classList.contains("td-seeall") && !el.closest(".td-seeall"));
    list = autoMarkCardsIfNeeded(list);
    return Array.from(new Set(list));
  }

  let cards = collectCards();
  const pageSize = 3;
  let startIndex = 0;

  function render(direction = "next") {
    cards = collectCards();
    const total = cards.length;
    if (startIndex >= total) startIndex = Math.max(0, total - pageSize);

    cards.forEach(c => {
      c.classList.remove("show");
      c.style.display = "none";
      c.style.transform = direction === "next" ? "translateX(30px)" : "translateX(-30px)";
      c.style.opacity = "0";
    });

    const end = Math.min(startIndex + pageSize, total);
    for (let i = startIndex; i < end; i++) {
      const card = cards[i];
      card.style.display = ""; // يدخل بالـflow
      requestAnimationFrame(() => setTimeout(() => card.classList.add("show"), 30));
    }

    prevBtn.disabled = (startIndex === 0);
    nextBtn.disabled = (end >= total);
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
    if (e.key === "ArrowRight") { e.preventDefault(); goNext(); }
    if (e.key === "ArrowLeft")  { e.preventDefault(); goPrev(); }
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

document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('btnLogin');
  const spinner = document.getElementById('spinner');

  if (!btn || !spinner) return; // حماية لو العناصر مش موجودة

  btn.addEventListener('click', function (e) {
    e.preventDefault();                 // بس للتأكيد
    spinner.classList.add('show');      // فرجي السبينر



    setTimeout(function () {
      window.location.href = 'login.html';
    }, 1000);
  });
});