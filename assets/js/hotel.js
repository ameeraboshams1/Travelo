document.addEventListener('DOMContentLoaded', function() {
  
  const hotelCards = document.querySelectorAll('.hotel-card');
  
  hotelCards.forEach(card => {
    
    const imageData = {
      0: [
        'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg',
        'https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg',
        'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&cs=tinysrgb&w=600'
      ],
      1: [
        'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg',
        'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg',
        'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=600'
      ],
      2: [
        'https://images.pexels.com/photos/2581540/pexels-photo-2581540.jpeg',
        'https://images.pexels.com/photos/189296/pexels-photo-189296.jpeg',
        'https://images.pexels.com/photos/2581540/pexels-photo-2581540.jpeg?auto=compress&cs=tinysrgb&w=600'
      ]
    };
    
    const index = Array.from(hotelCards).indexOf(card);
    const images = imageData[index] || imageData[0];
    
    const imgElement = card.querySelector('img');
    const prevBtn = card.querySelector('.image-nav.prev');
    const nextBtn = card.querySelector('.image-nav.next');
    
    let currentImageIndex = 0;
    
    function updateImage() {
      imgElement.src = images[currentImageIndex];
      imgElement.alt = `Hotel image ${currentImageIndex + 1}`;
    }
    
    if (prevBtn && nextBtn) {
      prevBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
        updateImage();
      });
      
      nextBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        currentImageIndex = (currentImageIndex + 1) % images.length;
        updateImage();
      });
    }
    
    
    const imageWrapper = card.querySelector('.hotel-image-wrapper');
    const dotsContainer = document.createElement('div');
    dotsContainer.className = 'image-dots';
    
    images.forEach((_, i) => {
      const dot = document.createElement('span');
      dot.className = `image-dot ${i === 0 ? 'active' : ''}`;
      dot.addEventListener('click', function() {
        currentImageIndex = i;
        updateImage();
        updateDots();
      });
      dotsContainer.appendChild(dot);
    });
    
    imageWrapper.appendChild(dotsContainer);
    
    function updateDots() {
      const dots = dotsContainer.querySelectorAll('.image-dot');
      dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === currentImageIndex);
      });
    }
  });

  
  const sortTabs = document.querySelectorAll('.toolbar-tab');
  const hotelCardsArray = Array.from(hotelCards);
  
  sortTabs.forEach(tab => {
    tab.addEventListener('click', function() {
      
      sortTabs.forEach(t => t.classList.remove('active'));
      
      this.classList.add('active');
      
      const sortType = this.dataset.sort;
      sortHotels(sortType);
    });
  });
  
  function sortHotels(sortType) {
    let sortedCards;
    
    switch(sortType) {
      case 'rating':
        
        sortedCards = hotelCardsArray.sort((a, b) => {
          return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
        });
        break;
        
      case 'price':
        
        sortedCards = hotelCardsArray.sort((a, b) => {
          return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
        });
        break;
        
      case 'popular':
      default:
        
        sortedCards = hotelCardsArray.sort((a, b) => {
          return Array.from(hotelCards).indexOf(a) - Array.from(hotelCards).indexOf(b);
        });
        break;
    }
    
    
    const hotelsList = document.querySelector('.hotels-list');
    hotelsList.innerHTML = '';
    sortedCards.forEach(card => {
      hotelsList.appendChild(card);
    });
  }

  
  const sliderTrack = document.querySelector('.slider-track');
  const sliderFill = document.querySelector('.slider-fill');
  const leftThumb = document.querySelector('.slider-thumb.left');
  const rightThumb = document.querySelector('.slider-thumb.right');
  const minValue = document.querySelector('.slider-values span:first-child');
  const maxValue = document.querySelector('.slider-values span:last-child');
  
  if (sliderTrack && leftThumb && rightThumb) {
    let isDraggingLeft = false;
    let isDraggingRight = false;
    
    const minPrice = 3.54;
    const maxPrice = 19544;
    const trackWidth = sliderTrack.offsetWidth;
    const thumbWidth = 12;
    
    let leftPosition = 0; 
    let rightPosition = 90; 
    
    updateSlider();
    
    
    function updateSlider() {
      leftThumb.style.left = `${leftPosition}%`;
      rightThumb.style.left = `${rightPosition}%`;
      sliderFill.style.left = `${leftPosition}%`;
      sliderFill.style.width = `${rightPosition - leftPosition}%`;
      
      const leftPrice = (minPrice + (maxPrice - minPrice) * (leftPosition / 100)).toFixed(2);
      const rightPrice = (minPrice + (maxPrice - minPrice) * (rightPosition / 100)).toFixed(2);
      
      minValue.textContent = `$${leftPrice}`;
      maxValue.textContent = `$${rightPrice}`;
      
      
      filterByPrice(parseFloat(leftPrice), parseFloat(rightPrice));
    }
    
    
    function filterByPrice(min, max) {
      hotelCards.forEach(card => {
        const price = parseFloat(card.dataset.price);
        if (price >= min && price <= max) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    }
    
    
    leftThumb.addEventListener('mousedown', function(e) {
      isDraggingLeft = true;
      e.preventDefault();
    });
    
    rightThumb.addEventListener('mousedown', function(e) {
      isDraggingRight = true;
      e.preventDefault();
    });
    
    
    document.addEventListener('mousemove', function(e) {
      if (!isDraggingLeft && !isDraggingRight) return;
      
      const rect = sliderTrack.getBoundingClientRect();
      const x = Math.max(0, Math.min(e.clientX - rect.left, trackWidth));
      const percentage = (x / trackWidth) * 100;
      
      if (isDraggingLeft) {
        leftPosition = Math.max(0, Math.min(percentage, rightPosition - 10));
        updateSlider();
      } else if (isDraggingRight) {
        rightPosition = Math.min(100, Math.max(percentage, leftPosition + 10));
        updateSlider();
      }
    });
    
    document.addEventListener('mouseup', function() {
      isDraggingLeft = false;
      isDraggingRight = false;
    });
    
    
    leftThumb.addEventListener('touchstart', function(e) {
      isDraggingLeft = true;
      e.preventDefault();
    });
    
    rightThumb.addEventListener('touchstart', function(e) {
      isDraggingRight = true;
      e.preventDefault();
    });
    
    document.addEventListener('touchmove', function(e) {
      if (!isDraggingLeft && !isDraggingRight) return;
      
      const rect = sliderTrack.getBoundingClientRect();
      const x = Math.max(0, Math.min(e.touches[0].clientX - rect.left, trackWidth));
      const percentage = (x / trackWidth) * 100;
      
      if (isDraggingLeft) {
        leftPosition = Math.max(0, Math.min(percentage, rightPosition - 10));
        updateSlider();
      } else if (isDraggingRight) {
        rightPosition = Math.min(100, Math.max(percentage, leftPosition + 10));
        updateSlider();
      }
    });
    
    document.addEventListener('touchend', function() {
      isDraggingLeft = false;
      isDraggingRight = false;
    });
  }

  
  const moreFiltersBtn = document.getElementById('moreFiltersBtn');
  const filtersExtra = document.getElementById('filtersExtra');
  
  if (moreFiltersBtn && filtersExtra) {
    let isExpanded = false;
    
    moreFiltersBtn.addEventListener('click', function() {
      isExpanded = !isExpanded;
      
      if (isExpanded) {
        filtersExtra.style.display = 'block';
        moreFiltersBtn.innerHTML = 'Less <span class="icon-placeholder tiny"></span>';
      } else {
        filtersExtra.style.display = 'none';
        moreFiltersBtn.innerHTML = 'More <span class="icon-placeholder tiny"></span>';
      }
    });
    
    
    filtersExtra.style.display = 'none';
  }

  
  const filterCheckboxes = document.querySelectorAll('.filter-checkbox input[type="checkbox"]');
  
  filterCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      applyFilters();
    });
  });
  
  function applyFilters() {
    const selectedFilters = Array.from(filterCheckboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.parentElement.querySelector('span').textContent.trim());
    
    
    if (selectedFilters.length === 0) {
      hotelCards.forEach(card => {
        card.style.display = '';
      });
      return;
    }
    
    
    
    hotelCards.forEach(card => {
      const cardText = card.textContent.toLowerCase();
      let shouldShow = false;
      
      selectedFilters.forEach(filter => {
        const filterLower = filter.toLowerCase();
        if (
          (filterLower.includes('parking') && cardText.includes('parking')) ||
          (filterLower.includes('breakfast') && cardText.includes('breakfast')) ||
          (filterLower.includes('cctv') && cardText.includes('cctv')) ||
          (filterLower.includes('wifi') && cardText.includes('wifi')) ||
          (filterLower.includes('sea') && cardText.includes('sea view')) ||
          (filterLower.includes('city') && cardText.includes('city view'))
        ) {
          shouldShow = true;
        }
      });
      
      card.style.display = shouldShow ? '' : 'none';
    });
  }

  
  const viewDetailsBtns = document.querySelectorAll('.btn-light');
  const bookNowBtns = document.querySelectorAll('.btn-primary');
  
  viewDetailsBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const hotelCard = this.closest('.hotel-card');
      const hotelName = hotelCard.querySelector('.hotel-name').textContent;
      alert(`Viewing details for: ${hotelName}\n\nThis would typically open a detailed modal or navigate to a hotel details page.`);
    });
  });
  
  bookNowBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const hotelCard = this.closest('.hotel-card');
      const hotelName = hotelCard.querySelector('.hotel-name').textContent;
      const hotelPrice = hotelCard.querySelector('.price-value').textContent;
      alert(`Booking ${hotelName}\n\nPrice: ${hotelPrice}\n\nThis would typically open a booking form or redirect to a booking page.`);
    });
  });

  
  const mapViewBtns = document.querySelectorAll('.map-view');
  
  mapViewBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const hotelCard = this.closest('.hotel-card');
      const hotelName = hotelCard.querySelector('.hotel-name').textContent;
      const hotelLocation = hotelCard.querySelector('.hotel-location').textContent;
      alert(`Map view for: ${hotelName}\nLocation: ${hotelLocation}\n\nThis would typically open a map modal showing the hotel location.`);
    });
  });
});
    document.addEventListener('DOMContentLoaded', function() {
      
      const letters = document.querySelectorAll('.letter');
      letters.forEach((letter, index) => {
        letter.style.animationDelay = `${index * 0.1}s`;
      });
      
      
      const ctaButton = document.querySelector('.cta-button');
      ctaButton.addEventListener('mouseenter', function() {
        this.classList.add('hover');
      });
      
      ctaButton.addEventListener('mouseleave', function() {
        this.classList.remove('hover');
      });
      
      
      const scrollIndicator = document.querySelector('.scroll-indicator');
      window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
          scrollIndicator.style.opacity = '0';
        } else {
          scrollIndicator.style.opacity = '1';
        }
      });
    });
    document.addEventListener("DOMContentLoaded", function () {
  const viewButtons = document.querySelectorAll(".hotel-card .btn-light");

  const modalOverlay = document.getElementById("hotelModal");
  const modalClose = document.getElementById("hotelModalClose");
  const modalCloseSecondary = document.getElementById("modalCloseSecondary");

  const mainImageEl = document.getElementById("modalMainImage");
  const thumbsContainer = document.getElementById("modalThumbs");

  const nameEl = document.getElementById("modalHotelName");
  const locationEl = document.getElementById("modalHotelLocation");
  const ratingEl = document.getElementById("modalHotelRating");
  const reviewsEl = document.getElementById("modalHotelReviews");
  const priceEl = document.getElementById("modalHotelPrice");
  const aboutEl = document.getElementById("modalHotelAbout");
  const servicesEl = document.getElementById("modalHotelServices");
  const offerTagEl = document.getElementById("modalOfferTag");

  const scoreLocationEl = document.getElementById("scoreLocation");
  const scoreServiceEl = document.getElementById("scoreService");
  const scoreValueEl = document.getElementById("scoreValue");

  const safetyTitleEl = document.getElementById("safetyTitle");
  const safetyDescEl = document.getElementById("safetyDesc");

  const prevImgBtn = document.getElementById("modalPrevImg");
  const nextImgBtn = document.getElementById("modalNextImg");
  const bookBtn = document.getElementById("modalBookBtn");

  
  const HOTEL_DETAILS = {
    "Taj Fort Aguada Resort & Spa Candolim, Goa": {
      reviews: 275,
      offer: "25% OFF",
      about:
        "A luxury beachfront resort in Goa with panoramic sea views, large pools, and an award-winning spa. Perfect for both families and couples.",
      services: ["Parking", "Attached Bathroom", "CCTV Cameras", "Wifi"],
      safetyTitle: "Travel safe during your stay",
      safetyDesc:
        "This property follows extra safety and cleaning measures to help keep you protected.",
      scores: { location: 92, service: 88, value: 84 },
      images: [
        "https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg",
        "https://images.pexels.com/photos/237371/pexels-photo-237371.jpeg",
        "https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg",
      ],
    },
    "Blue Sea Cliff Hotel": {
      reviews: 198,
      offer: "20% OFF",
      about:
        "Overlooking the cliffs of Santorini, this hotel offers bright rooms, infinity pools, and world-class breakfast with a view.",
      services: ["Parking", "Sea View", "Breakfast", "Wifi"],
      safetyTitle: "Extra hygiene measures",
      safetyDesc:
        "Staff follow local health guidelines and the property is regularly sanitized.",
      scores: { location: 95, service: 90, value: 80 },
      images: [
        "https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg",
        "https://images.pexels.com/photos/325404/pexels-photo-325404.jpeg",
        "https://images.pexels.com/photos/2581540/pexels-photo-2581540.jpeg",
      ],
    },
    "Skyline City Hotel": {
      reviews: 312,
      offer: "15% OFF",
      about:
        "Located in central Tokyo, Skyline City Hotel combines modern design with easy access to shopping, dining, and public transport.",
      services: ["Parking", "City View", "CCTV Cameras", "Wifi"],
      safetyTitle: "City stay with安心",
      safetyDesc:
        "24/7 reception, monitored entrances, and contactless check-in are available.",
      scores: { location: 96, service: 86, value: 82 },
      images: [
        "https://images.pexels.com/photos/2581540/pexels-photo-2581540.jpeg",
        "https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg",
        "https://images.pexels.com/photos/237371/pexels-photo-237371.jpeg",
      ],
    },
  };

  let currentImages = [];
  let currentIndex = 0;

  function openModal(card) {
    const name = card.querySelector(".hotel-name").textContent.trim();
    const location = card.querySelector(".hotel-location").textContent.trim();
    const price = card.querySelector(".price-value")
      ? card.querySelector(".price-value").textContent.trim()
      : card.dataset.price || "";
    const rating = card.dataset.rating || "4.9";

    const details = HOTEL_DETAILS[name] || {};

    
    nameEl.textContent = name;
    locationEl.textContent = location;
    ratingEl.textContent = rating;
    priceEl.textContent = price;

    
    const reviewsCount = details.reviews || 0;
    reviewsEl.textContent = reviewsCount ? `${reviewsCount} reviews` : "";

    
    offerTagEl.textContent = details.offer || "";

    
    aboutEl.textContent =
      details.about ||
      "This property offers a comfortable stay with a great location and friendly staff.";

    
    servicesEl.innerHTML = "";
    (details.services || []).forEach((service) => {
      const li = document.createElement("li");
      li.textContent = service;
      servicesEl.appendChild(li);
    });

    
    safetyTitleEl.textContent =
      details.safetyTitle || "Travel safe during your stay";
    safetyDescEl.textContent =
      details.safetyDesc ||
      "This property follows enhanced health & safety measures.";

    
    const scores = details.scores || {
      location: 85,
      service: 80,
      value: 78,
    };
    scoreLocationEl.style.width = scores.location + "%";
    scoreServiceEl.style.width = scores.service + "%";
    scoreValueEl.style.width = scores.value + "%";

    
    currentImages = details.images ? details.images.slice() : [];
    if (!currentImages.length) {
      const fallbackImg = card.querySelector(".hotel-image-wrapper img, .hotel-img-wrap img");
      if (fallbackImg) currentImages = [fallbackImg.src];
    }
    currentIndex = 0;
    renderImages();

    modalOverlay.classList.add("show");
  }

  function closeModal() {
    modalOverlay.classList.remove("show");
  }

  function renderImages() {
    if (!currentImages.length) return;
    mainImageEl.src = currentImages[currentIndex];

    thumbsContainer.innerHTML = "";
    currentImages.forEach((src, idx) => {
      const btn = document.createElement("button");
      if (idx === currentIndex) btn.classList.add("active");
      const img = document.createElement("img");
      img.src = src;
      img.alt = "Hotel thumbnail";
      btn.appendChild(img);
      btn.addEventListener("click", () => {
        currentIndex = idx;
        renderImages();
      });
      thumbsContainer.appendChild(btn);
    });
  }

  prevImgBtn.addEventListener("click", () => {
    if (!currentImages.length) return;
    currentIndex =
      (currentIndex - 1 + currentImages.length) % currentImages.length;
    renderImages();
  });

  nextImgBtn.addEventListener("click", () => {
    if (!currentImages.length) return;
    currentIndex = (currentIndex + 1) % currentImages.length;
    renderImages();
  });

  
  bookBtn.addEventListener("click", () => {
    alert("Booking flow will be implemented here 🤍");
  });

  
  viewButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const card = btn.closest(".hotel-card");
      if (card) openModal(card);
    });
  });

  
  modalClose.addEventListener("click", closeModal);
  modalCloseSecondary.addEventListener("click", closeModal);
  modalOverlay.addEventListener("click", (e) => {
    if (e.target === modalOverlay) closeModal();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeModal();
  });
});
