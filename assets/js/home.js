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
// ===============================
// User menu toggle (robust) + toggle on 2nd click
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  const navUser = document.querySelector(".nav-user");
  const toggle  = document.getElementById("userMenuToggle");
  const menu    = document.getElementById("userMenu");

  // إذا مش مسجل دخول ما رح تلاقيهم
  if (!toggle || !menu) return;

  // ✅ يمنع تكرار الربط لو الملف انحمّل مرتين
  if (window.__TRAVELO_USERMENU_BOUND__) return;
  window.__TRAVELO_USERMENU_BOUND__ = true;

  let ignoreOutside = false;

  const isOpen = () => menu.classList.contains("show");

  const open = () => {
    menu.classList.add("show");
    navUser?.classList.add("open");
    toggle.setAttribute("aria-expanded", "true");
  };

  const close = () => {
    menu.classList.remove("show");
    navUser?.classList.remove("open");
    toggle.setAttribute("aria-expanded", "false");
  };

  const toggleMenu = () => (isOpen() ? close() : open());

  // ✅ استخدمي pointerdown + capture عشان ما حدا يسرق الكليك
  toggle.addEventListener(
    "pointerdown",
    (e) => {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      ignoreOutside = true;        // امنعي close بنفس اللمسة
      toggleMenu();
      setTimeout(() => (ignoreOutside = false), 120);
    },
    true
  );

  // ✅ كليك داخل القائمة ما يسكرها
  menu.addEventListener(
    "pointerdown",
    (e) => {
      e.stopPropagation();
      e.stopImmediatePropagation();
    },
    true
  );

  // ✅ سكري بس إذا كبستي برا
  document.addEventListener(
    "pointerdown",
    (e) => {
      if (ignoreOutside) return;
      if (!isOpen()) return;
      if (navUser?.contains(e.target)) return;
      close();
    },
    true
  );

  // ✅ ESC
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") close();
  });
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


class DarkModeManager {
    constructor() {
        this.toggleBtn = document.getElementById('darkModeToggle');
        this.icon = document.getElementById('darkModeIcon');
        this.html = document.documentElement;
        
        this.init();
    }
    
    init() {
        // Check saved preference
        const savedMode = localStorage.getItem('traveloDarkMode');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        // Set initial mode
        if (savedMode === 'dark' || (!savedMode && prefersDark)) {
            this.enableDarkMode(false);
        }
        
        // Add event listener
        this.toggleBtn.addEventListener('click', () => this.toggle());
        
        // System preference listener
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('traveloDarkMode')) {
                e.matches ? this.enableDarkMode() : this.disableDarkMode();
            }
        });
        
        // Add keyboard shortcut
        this.addKeyboardShortcut();
    }
    
    toggle() {
        // Prevent rapid clicking
        if (this.toggleBtn.classList.contains('animating')) return;
        
        this.toggleBtn.classList.add('animating');
        
        // Add click animation
        this.toggleBtn.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            if (this.html.classList.contains('dark')) {
                this.disableDarkMode();
            } else {
                this.enableDarkMode();
            }
            
            this.toggleBtn.style.transform = '';
            setTimeout(() => {
                this.toggleBtn.classList.remove('animating');
            }, 300);
        }, 150);
    }
    
    enableDarkMode(animate = true) {
        if (!animate) {
            this.html.style.transition = 'none';
        }
        
        this.html.classList.add('dark');
        this.updateIcon('sun');
        localStorage.setItem('traveloDarkMode', 'dark');
        
        // Update meta theme color
        this.updateMetaColor('#0f172a');
        
        // Show notification
        this.showNotification('Dark mode activated');
        
        if (!animate) {
            setTimeout(() => {
                this.html.style.transition = '';
            }, 10);
        }
    }
    
    disableDarkMode() {
        this.html.classList.remove('dark');
        this.updateIcon('moon');
        localStorage.setItem('traveloDarkMode', 'light');
        this.updateMetaColor('#f7f8fb');
        this.showNotification('Light mode activated');
    }
    
    updateIcon(type) {
        this.icon.style.transform = 'rotate(360deg) scale(0.8)';
        
        setTimeout(() => {
            if (type === 'sun') {
                this.icon.classList.remove('bi-moon-fill');
                this.icon.classList.add('bi-sun-fill');
            } else {
                this.icon.classList.remove('bi-sun-fill');
                this.icon.classList.add('bi-moon-fill');
            }
            
            this.icon.style.transform = 'rotate(0deg) scale(1)';
        }, 150);
    }
    
    updateMetaColor(color) {
        let meta = document.querySelector('meta[name="theme-color"]');
        if (!meta) {
            meta = document.createElement('meta');
            meta.name = 'theme-color';
            document.head.appendChild(meta);
        }
        meta.content = color;
    }
    
    addKeyboardShortcut() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+Shift+D or Cmd+Shift+D
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'd') {
                e.preventDefault();
                this.toggle();
            }
        });
    }
    
    showNotification(message) {
        // Use existing toast function if available
        if (typeof showToast === 'function') {
            showToast(message, 'success');
            return;
        }
        
        // Fallback notification
        const notification = document.createElement('div');
        notification.className = 'dark-mode-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--dark-card);
            color: var(--dark-text);
            padding: 12px 20px;
            border-radius: 12px;
            border: 1px solid var(--dark-border);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            animation: slideIn 0.3s ease;
            font-weight: 500;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    }
    
    isDarkMode() {
        return this.html.classList.contains('dark');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.darkMode = new DarkModeManager();
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .dark-mode-toggle i {
            transition: transform 0.3s ease;
        }
    `;
    document.head.appendChild(style);
});

// Global functions for external access
function toggleDarkMode() {
    if (window.darkMode) {
        window.darkMode.toggle();
    }
}

function getDarkModeStatus() {
    return window.darkMode ? window.darkMode.isDarkMode() : false;
}
// ===============================
// User menu dropdown (FINAL FIX) - cleans old listeners + toggle
// Put this at VERY END of home.js
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  let navUser = document.querySelector(".nav-user");
  let toggle = document.getElementById("userMenuToggle");
  const menu = document.getElementById("userMenu");

  if (!toggle || !menu) return;

  // ✅ prevent running twice
  if (window.__TRAVELO_USERMENU_FIXED__) return;
  window.__TRAVELO_USERMENU_FIXED__ = true;

  // ✅ remove ALL old event listeners on the toggle by cloning it
  // (this is the nuclear option that fixes "it opens/closes instantly" + "does nothing")
  const cloned = toggle.cloneNode(true);
  toggle.parentNode.replaceChild(cloned, toggle);
  toggle = cloned;

  const isOpen = () => menu.classList.contains("show");

  const open = () => {
    menu.classList.add("show");
    navUser && navUser.classList.add("open");
    toggle.setAttribute("aria-expanded", "true");
  };

  const close = () => {
    menu.classList.remove("show");
    navUser && navUser.classList.remove("open");
    toggle.setAttribute("aria-expanded", "false");
  };

  const toggleMenu = () => (isOpen() ? close() : open());

  // ✅ use pointerdown + capture to beat any other listeners
  toggle.addEventListener(
    "pointerdown",
    (e) => {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      toggleMenu();
    },
    true
  );

  // ✅ clicking inside menu shouldn't close it
  menu.addEventListener(
    "pointerdown",
    (e) => {
      e.stopPropagation();
      e.stopImmediatePropagation();
    },
    true
  );

  // ✅ close on outside click only
  document.addEventListener(
    "pointerdown",
    (e) => {
      if (!isOpen()) return;
      if (navUser && navUser.contains(e.target)) return;
      close();
    },
    true
  );

  // ✅ esc close
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") close();
  });
});
