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
      card.style.display = "";      requestAnimationFrame(() => setTimeout(() => card.classList.add("show"), 30));
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

  if (!btn || !spinner) return;
  btn.addEventListener('click', function (e) {
    e.preventDefault();                    spinner.classList.add('show');     


    setTimeout(function () {
      window.location.href = 'login.html';
    }, 1000);
  });
});
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('btnLogin1');
  const spinner = document.getElementById('spinner');

  if (!btn || !spinner) return; 

  btn.addEventListener('click', function (e) {
    e.preventDefault();                 
    spinner.classList.add('show');      


    setTimeout(function () {
      window.location.href = 'signup.html';
    }, 1000);
  });
});
// Tabs filtering
const tabs = document.querySelectorAll(".category-btn");
const cards = document.querySelectorAll(".destination-col");

tabs.forEach((tab) => {
  tab.addEventListener("click", () => {
    tabs.forEach((t) => t.classList.remove("active"));
    tab.classList.add("active");

    const cat = tab.dataset.category;
    
    cards.forEach((col) => {
      if (cat === "all") {
        col.classList.remove("hidden");
        col.classList.remove("filtered-out");
      } else {
        const c = col.dataset.category;
        if (c === cat) {
          col.classList.remove("hidden");
          col.classList.remove("filtered-out");
        } else {
          col.classList.add("hidden");
          col.classList.add("filtered-out");
        }
      }
    });
  });
});

// --------- "See More" → Open Modal ---------
const modalOverlay = document.getElementById("destinationModal");
const modalCloseBtn = document.getElementById("destinationModalClose");

const modalImg = document.getElementById("modalDestinationImage");
const modalTitle = document.getElementById("modalDestinationTitle");
const modalLocation = document.getElementById("modalDestinationLocation");
const modalDesc = document.getElementById("modalDestinationDesc");
const modalVisitors = document.getElementById("modalVisitors");
const modalSeason = document.getElementById("modalSeason");
const modalPrice = document.getElementById("modalPrice");

// معلومات إضافية لكل مدينة (بتقدري تعدلي النصوص براحتك)
const cityDetails = {
  Tokyo: {
    desc: "Tokyo blends ultra-modern city life with traditional temples, colorful streets, and unforgettable food experiences. Perfect for both solo travelers and groups.",
    visitors: "14M / year",
    season: "Mar – Apr (Sakura)"
  },
  Rome: {
    desc: "Walk through ancient history in Rome – from the Colosseum to the Vatican – with charming streets, cafés, and vibrant Italian culture.",
    visitors: "9.8M / year",
    season: "Apr – Jun"
  },
  Barcelona: {
    desc: "Barcelona offers a unique mix of beaches, Gaudí’s architecture, and lively nightlife – making it one of Europe’s most loved destinations.",
    visitors: "11M / year",
    season: "May – Sep"
  },
  Bangkok: {
    desc: "Bangkok is full of energy, night markets, temples, and incredible street food – a must-visit hub in Southeast Asia.",
    visitors: "22M / year",
    season: "Nov – Feb"
  },
  Sydney: {
    desc: "Famous for its harbour, Opera House, and beaches, Sydney is ideal for outdoor lovers and city explorers alike.",
    visitors: "10M / year",
    season: "Dec – Feb"
  },
  Toronto: {
    desc: "Toronto is a modern, multicultural city with iconic skylines, nearby nature, and a rich food scene.",
    visitors: "8M / year",
    season: "May – Sep"
  }
};

// دالة فتح المودال وتعبئته من الكارد
function openDestinationModal(card) {
  const city = card.querySelector(".destination-city")?.textContent.trim() || "";
  const location = card.querySelector(".location-city")?.textContent.trim() || "";
  const priceText = card.querySelector(".destination-price")?.childNodes[0].textContent.trim() || "";
  const imgEl = card.querySelector(".destination-image");
  const imgSrc = imgEl ? imgEl.src : "";

  // تعبئة البيانات
  modalTitle.textContent = city || "Destination";
  modalLocation.textContent = location || "";
  modalImg.src = imgSrc;
  modalImg.alt = city;

  const info = cityDetails[city] || {
    desc: "Discover this wonderful destination with flexible packages, guided trips, and hand-picked hotels tailored to your travel style.",
    visitors: "Millions / year",
    season: "All year"
  };

  modalDesc.textContent = info.desc;
  modalVisitors.textContent = info.visitors;
  modalSeason.textContent = info.season;
  modalPrice.textContent = priceText || "$ ---";

  // إظهار المودال
  modalOverlay.classList.add("show");
  document.body.classList.add("no-scroll");
}

// ربط أزرار See More بالمودال
document.querySelectorAll(".view-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const card = btn.closest(".destination-card");
    if (!card) return;
    openDestinationModal(card);
  });
});

// إغلاق المودال
function closeDestinationModal() {
  modalOverlay.classList.remove("show");
  document.body.classList.remove("no-scroll");
}

modalCloseBtn.addEventListener("click", closeDestinationModal);

// إغلاق عند الضغط خارج الكارد
modalOverlay.addEventListener("click", (e) => {
  if (e.target === modalOverlay) {
    closeDestinationModal();
  }
});

// إغلاق بـ ESC
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && modalOverlay.classList.contains("show")) {
    closeDestinationModal();
  }
});
// SEE ALL + SPINNER REDIRECT
const seeAllBtn = document.querySelector(".see-all-link");
const globalSpinner = document.getElementById("spinner");

if (seeAllBtn && globalSpinner) {
  seeAllBtn.addEventListener("click", function (e) {
    e.preventDefault();

    // Show existing signup spinner
    globalSpinner.classList.add("show");

    // Redirect after delay
    setTimeout(function () {
      window.location.href = "destination.html";
    }, 1000); // تقدر تعدلي الوقت
  });
}
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('userMenuToggle');
    const menu   = document.getElementById('userMenu');

    if (toggle && menu) {
      toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('show');
      });

      document.addEventListener('click', () => {
        menu.classList.remove('show');
      });
    }
  });