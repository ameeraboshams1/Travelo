document.addEventListener("DOMContentLoaded", () => {
  const tabItems = document.querySelectorAll(".tab-item");
  const tripChips = document.querySelectorAll(".trip-chip");
  const flightList = document.getElementById("flightList");
  const allCards = Array.from(flightList.children);

  const maxPriceInput = document.getElementById("maxPrice");
  const maxPriceValue = document.getElementById("maxPriceValue");
  const nonStopOnly = document.getElementById("nonStopOnly");
  const timeChips = document.querySelectorAll(".chip");
  const resetBtn = document.getElementById("resetFilters");

  let currentSort = "cheapest";
  let currentTimeFilter = "all";
  let currentTripFilter = "all";

  function renderCards(cards) {
    flightList.innerHTML = "";
    cards.forEach((card) => flightList.appendChild(card));
  }

  function getFilteredCards() {
    const maxPrice = Number(maxPriceInput.value);

    return allCards.filter((card) => {
      const price = Number(card.dataset.price);
      const stops = Number(card.dataset.stops);
      const tripType = card.dataset.trip; 

      if (price > maxPrice) return false;
      if (nonStopOnly.checked && stops !== 0) return false;

      
      if (currentTripFilter !== "all" && tripType !== currentTripFilter) {
        return false;
      }

      
      if (currentTimeFilter !== "all") {
        const depTimeText = card.querySelector(".time strong").textContent;
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
  }

    tabItems.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabItems.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");
      currentSort = tab.dataset.sort;
      applySortAndFilter();
    });
  });

    tripChips.forEach((chip) => {
    chip.addEventListener("click", () => {
      tripChips.forEach((c) => c.classList.remove("active"));
      chip.classList.add("active");
      currentTripFilter = chip.dataset.trip;
      applySortAndFilter();
    });
  });

    maxPriceInput.addEventListener("input", () => {
    maxPriceValue.textContent = `Up to ${maxPriceInput.value}`;
    applySortAndFilter();
  });

    nonStopOnly.addEventListener("change", applySortAndFilter);

    timeChips.forEach((chip) => {
    chip.addEventListener("click", () => {
      timeChips.forEach((c) => c.classList.remove("active"));
      chip.classList.add("active");
      currentTimeFilter = chip.dataset.time;
      applySortAndFilter();
    });
  });

    resetBtn.addEventListener("click", () => {
    maxPriceInput.value = 600;
    maxPriceValue.textContent = "Up to 600";
    nonStopOnly.checked = false;
    currentTimeFilter = "all";
    currentTripFilter = "all";
    currentSort = "cheapest";

    timeChips.forEach((c) => c.classList.remove("active"));
    document.querySelector('.chip[data-time="all"]').classList.add("active");

    tripChips.forEach((c) => c.classList.remove("active"));
    document.querySelector('.trip-chip[data-trip="all"]').classList.add("active");

    tabItems.forEach((t) => t.classList.remove("active"));
    document.querySelector('.tab-item[data-sort="cheapest"]').classList.add("active");

    applySortAndFilter();
  });

    applySortAndFilter();
});
document.addEventListener("DOMContentLoaded", () => {
  
  const tabItems = document.querySelectorAll(".tab-item");
  const tripChips = document.querySelectorAll(".trip-chip");
  const flightList = document.getElementById("flightList");
  const allCards = Array.from(flightList.children);

  const maxPriceInput = document.getElementById("maxPrice");
  const maxPriceValue = document.getElementById("maxPriceValue");
  const nonStopOnly = document.getElementById("nonStopOnly");
  const timeChips = document.querySelectorAll(".chip");
  const resetBtn = document.getElementById("resetFilters");

  let currentSort = "cheapest";
  let currentTimeFilter = "all";
  let currentTripFilter = "all";

  
  function renderCards(cards) {
    flightList.innerHTML = "";
    cards.forEach((card) => flightList.appendChild(card));
  }

  
  function getFilteredCards() {
    const maxPrice = Number(maxPriceInput.value);

    return allCards.filter((card) => {
      const price = Number(card.dataset.price);
      const stops = Number(card.dataset.stops);
      const tripType = card.dataset.trip; 

      if (price > maxPrice) return false;
      if (nonStopOnly.checked && stops !== 0) return false;

      if (currentTripFilter !== "all" && tripType !== currentTripFilter) {
        return false;
      }

      
      if (currentTimeFilter !== "all") {
        const depTimeText =
          card.querySelector(".ticket-times .time strong").textContent;
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
  }

    tabItems.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabItems.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");
      currentSort = tab.dataset.sort;
      applySortAndFilter();
    });
  });

    tripChips.forEach((chip) => {
    chip.addEventListener("click", () => {
      tripChips.forEach((c) => c.classList.remove("active"));
      chip.classList.add("active");
      currentTripFilter = chip.dataset.trip;
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
      currentTimeFilter = chip.dataset.time;
      applySortAndFilter();
    });
  });

    if (resetBtn) {
    resetBtn.addEventListener("click", () => {
      maxPriceInput.value = maxPriceInput.max || 600;
      maxPriceValue.textContent = `Up to ${maxPriceInput.value}`;
      nonStopOnly.checked = false;
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

    const modalEl = document.getElementById("flightDetailsModal");
  let detailsModal = null;

  if (modalEl && window.bootstrap) {
    detailsModal = new bootstrap.Modal(modalEl);
  }

  const detailsButtons = document.querySelectorAll(".details-btn");

  detailsButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      if (!modalEl || !detailsModal) return;

      const card = btn.closest(".flight-card.ticket");
      if (!card) return;

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
      const price =
        card.querySelector(".price")?.textContent.trim() || "";

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

    applySortAndFilter();
});



function animateTextReveal() {
  const title = document.querySelector('.main-title');
  if (!title) return;
  
  const text = title.textContent;
  title.innerHTML = '';
  
  for (let i = 0; i < text.length; i++) {
    const span = document.createElement('span');
    span.textContent = text[i];
    span.style.animationDelay = `${i * 0.1}s`;
    title.appendChild(span);
  }
  
  title.classList.add('text-reveal');
}


function createLightSweep() {
  const flashSection = document.querySelector('.flash-section');
  const lightSweep = document.createElement('div');
  lightSweep.className = 'light-sweep';
  
  flashSection.appendChild(lightSweep);
}


function createStars() {
  const flashSection = document.querySelector('.flash-section');
  
  
  const starCount = 30;
  
  for (let i = 0; i < starCount; i++) {
    const star = document.createElement('div');
    star.className = 'star';
    
    
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
  const flashButtons = document.querySelectorAll('.flash-btn');
  
  flashButtons.forEach(button => {
    
    button.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-8px) scale(1.05)';
    });
    
    button.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0) scale(1)';
    });
    
    
    button.addEventListener('click', function(e) {
      e.preventDefault();
      
      
      this.style.transform = 'translateY(-4px) scale(1.02)';
      
      setTimeout(() => {
        this.style.transform = 'translateY(-8px) scale(1.05)';
      }, 150);
      
      
      if (this.classList.contains('btn-primary')) {
        simulateBooking();
      } else {
        simulateExplore();
      }
    });
  });
}


function enhanceAirplanes() {
  const airplanes = document.querySelectorAll('.airplane');
  
  airplanes.forEach((plane, index) => {
    
    plane.addEventListener('animationiteration', () => {
      
      const colors = ['#3498db', '#9b59b6', '#2ecc71', '#e74c3c', '#f1c40f'];
      const randomColor = colors[Math.floor(Math.random() * colors.length)];
      plane.style.color = randomColor;
      
      
      const randomSize = Math.random() * 15 + 25;
      plane.style.fontSize = `${randomSize}px`;
      
      
      const randomDuration = Math.random() * 10 + 20;
      plane.style.animationDuration = `${randomDuration}s`;
    });
  });
}


function simulateBooking() {
  
  const bookingModal = document.createElement('div');
  bookingModal.className = 'booking-modal';
  
  bookingModal.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    animation: fadeIn 0.3s forwards;
  `;
  
  const modalContent = document.createElement('div');
  modalContent.style.cssText = `
    background: white;
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    max-width: 400px;
    width: 90%;
    transform: translateY(-50px);
    animation: slideUp 0.5s forwards 0.3s;
  `;
  
  modalContent.innerHTML = `
    <div style="font-size: 60px; color: #27ae60; margin-bottom: 20px;">✈️</div>
    <h3 style="color: #2c3e50; margin-bottom: 10px;">Ready to Book!</h3>
    <p style="color: #7f8c8d; margin-bottom: 20px;">
      You're about to start your journey. Let's find the perfect flight for you!
    </p>
    <p style="color: #95a5a6; font-size: 14px; margin-bottom: 30px;">
      Explore our amazing deals and destinations.
    </p>
    <button id="closeModal" style="
      background: #3498db;
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    ">Continue</button>
  `;
  
  bookingModal.appendChild(modalContent);
  document.body.appendChild(bookingModal);
  
  
  const style = document.createElement('style');
  style.textContent = `
    @keyframes fadeIn {
      to { opacity: 1; }
    }
    @keyframes slideUp {
      to { transform: translateY(0); }
    }
    @keyframes fadeOut {
      to { opacity: 0; }
    }
  `;
  document.head.appendChild(style);
  
  
  bookingModal.addEventListener('click', function(e) {
    if (e.target === bookingModal || e.target.id === 'closeModal') {
      bookingModal.style.animation = 'fadeOut 0.3s forwards';
      setTimeout(() => {
        document.body.removeChild(bookingModal);
      }, 300);
    }
  });
}


function simulateExplore() {
  
  const exploreModal = document.createElement('div');
  exploreModal.className = 'explore-modal';
  
  exploreModal.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    animation: fadeIn 0.3s forwards;
  `;
  
  const modalContent = document.createElement('div');
  modalContent.style.cssText = `
    background: white;
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    max-width: 400px;
    width: 90%;
    transform: translateY(-50px);
    animation: slideUp 0.5s forwards 0.3s;
  `;
  
  modalContent.innerHTML = `
    <div style="font-size: 60px; color: #3498db; margin-bottom: 20px;">🗺️</div>
    <h3 style="color: #2c3e50; margin-bottom: 10px;">Explore Destinations!</h3>
    <p style="color: #7f8c8d; margin-bottom: 20px;">
      Discover amazing places around the world. From beaches to mountains, we have it all!
    </p>
    <p style="color: #95a5a6; font-size: 14px; margin-bottom: 30px;">
      Top destinations: Paris, Tokyo, Dubai, New York, Bali
    </p>
    <button id="closeExploreModal" style="
      background: #9b59b6;
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    ">Let's Explore</button>
  `;
  
  exploreModal.appendChild(modalContent);
  document.body.appendChild(exploreModal);
  
  
  exploreModal.addEventListener('click', function(e) {
    if (e.target === exploreModal || e.target.id === 'closeExploreModal') {
      exploreModal.style.animation = 'fadeOut 0.3s forwards';
      setTimeout(() => {
        document.body.removeChild(exploreModal);
      }, 300);
    }
  });
}


function initFloatEffects() {
  const tagline = document.querySelector('.tagline span');
  if (tagline) {
    
    setInterval(() => {
      tagline.style.animation = 'none';
      setTimeout(() => {
        tagline.style.animation = 'float 6s ease-in-out infinite';
      }, 10);
    }, 6000);
  }
}


function initBackgroundEffects() {
  const flashSection = document.querySelector('.flash-section');
  const backgrounds = [
    'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2074&q=80',
    'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80',
    'https://images.unsplash.com/photo-1540453764285-7c5d5d5b5b1a?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
    'https://images.unsplash.com/photo-1534274988757-a28bf1a57c17?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'
  ];
  
  let currentBg = 0;
  
  
  setInterval(() => {
    currentBg = (currentBg + 1) % backgrounds.length;
    
    
    flashSection.style.opacity = '0.7';
    flashSection.style.transition = 'opacity 1s ease';
    
    setTimeout(() => {
      flashSection.style.backgroundImage = `linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('${backgrounds[currentBg]}')`;
      flashSection.style.opacity = '1';
    }, 500);
    
    setTimeout(() => {
      flashSection.style.transition = '';
    }, 1500);
  }, 10000);
}


function addDynamicAirplanes() {
  const flashSection = document.querySelector('.flash-section');
  
  
  setInterval(() => {
    if (Math.random() > 0.7) { 
      const airplane = document.createElement('div');
      airplane.className = 'airplane';
      airplane.textContent = '✈';
      
      
      const size = Math.random() * 20 + 20;
      const top = Math.random() * 80 + 10;
      const duration = Math.random() * 15 + 20;
      const delay = Math.random() * 5;
      const color = ['#3498db', '#9b59b6', '#2ecc71', '#e74c3c', '#f1c40f'][Math.floor(Math.random() * 5)];
      
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
  
  
  
  
  
  addDynamicAirplanes();
  
  
  setInterval(() => {
    const airplanes = document.querySelectorAll('.airplane');
    airplanes.forEach(plane => {
      plane.style.animation = 'none';
      setTimeout(() => {
        plane.style.animation = '';
      }, 10);
    });
  }, 30000);
}


document.addEventListener('DOMContentLoaded', initFlashEffects);


window.addEventListener('scroll', function() {
  const flashSection = document.querySelector('.flash-section');
  const scrollPosition = window.scrollY;
  
  
  if (flashSection) {
    flashSection.style.backgroundPositionY = `${scrollPosition * 0.5}px`;
  }
});


window.addEventListener('load', function() {
  
  setTimeout(() => {
    const buttons = document.querySelectorAll('.flash-btn');
    buttons.forEach((button, index) => {
      setTimeout(() => {
        button.style.transform = 'translateY(-10px)';
        setTimeout(() => {
          button.style.transform = 'translateY(0)';
        }, 300);
      }, index * 200);
    });
  }, 1500);
});
