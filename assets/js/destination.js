document.addEventListener("DOMContentLoaded", () => {
  
  const tabs = document.querySelectorAll(".category-btn");
  const cards = document.querySelectorAll(".destination-col");

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabs.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");

      const cat = tab.dataset.category;

      cards.forEach((col) => {
        const c = col.dataset.category;

        if (cat === "all" || c === cat) {
          col.classList.remove("hidden", "filtered-out");
        } else {
          col.classList.add("hidden", "filtered-out");
        }
      });
    });
  });

  
  const destinationData = {
    Tokyo: {
      title: "Tokyo",
      location: "Tokyo, Japan",
      img: "Rectangle 1434.svg",
      desc:
        "Tokyo blends neon lights, traditional shrines, and some of the best food in the world. From Shibuya Crossing to Asakusa, every district tells a different story.",
      visitors: "14M / year",
      season: "Mar – Apr",
      price: "$360",
    },
    Rome: {
      title: "Rome",
      location: "Rome, Italy",
      img: "https://images.pexels.com/photos/532263/pexels-photo-532263.jpeg",
      desc:
        "Walk through thousands of years of history: the Colosseum, the Roman Forum, and the Vatican. Enjoy espresso in sunny piazzas and authentic Italian gelato.",
      visitors: "9.8M / year",
      season: "Apr – Jun",
      price: "$370",
    },
    Barcelona: {
      title: "Barcelona",
      location: "Barcelona, Spain",
      img: "https://images.pexels.com/photos/586052/pexels-photo-586052.jpeg",
      desc:
        "Enjoy sunny beaches, Gaudí’s masterpieces, and a lively food scene. Perfect for travelers who want both city experiences and seaside relaxation.",
      visitors: "11M / year",
      season: "May – Sep",
      price: "$400",
    },
    Bangkok: {
      title: "Bangkok",
      location: "Bangkok, Thailand",
      img: "https://images.pexels.com/photos/373290/pexels-photo-373290.jpeg",
      desc:
        "From floating markets to golden temples and vibrant night life, Bangkok is a city that never sleeps and always surprises visitors.",
      visitors: "22M / year",
      season: "Nov – Feb",
      price: "$300",
    },
    Sydney: {
      title: "Sydney",
      location: "Sydney, Australia",
      img: "https://images.pexels.com/photos/2193300/pexels-photo-2193300.jpeg",
      desc:
        "Explore the Sydney Opera House, coastal walks, and some of the most beautiful beaches in the world in a relaxed, outdoor-loving city.",
      visitors: "10M / year",
      season: "Dec – Feb",
      price: "$300",
    },
    Toronto: {
      title: "Toronto",
      location: "Toronto, Canada",
      img: "https://images.pexels.com/photos/2946729/pexels-photo-2946729.jpeg",
      desc:
        "A multicultural city with a modern skyline, cozy neighborhoods, and quick access to nature like Niagara Falls and hiking trails.",
      visitors: "8M / year",
      season: "May – Sep",
      price: "$370",
    },
  };

  
  const modalOverlay = document.getElementById("destinationModal");
  const modalClose = document.getElementById("destinationModalClose");

  const modalImg = document.getElementById("modalDestinationImage");
  const modalTitle = document.getElementById("modalDestinationTitle");
  const modalLocation = document.getElementById("modalDestinationLocation");
  const modalDesc = document.getElementById("modalDestinationDesc");
  const modalVisitors = document.getElementById("modalVisitors");
  const modalSeason = document.getElementById("modalSeason");
  const modalPrice = document.getElementById("modalPrice");

  function openModal(city) {
    const data = destinationData[city];
    if (!data) return;

    modalTitle.textContent = data.title;
    modalLocation.textContent = data.location;
    modalImg.src = data.img;
    modalDesc.textContent = data.desc;
    modalVisitors.textContent = data.visitors;
    modalSeason.textContent = data.season;
    modalPrice.textContent = data.price;

    modalOverlay.classList.add("show");
    document.body.classList.add("no-scroll");
  }

  function closeModal() {
    modalOverlay.classList.remove("show");
    document.body.classList.remove("no-scroll");
  }

  
  const viewButtons = document.querySelectorAll(".view-btn");
  viewButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const city = btn.dataset.city;
      if (city) {
        openModal(city);
      }
    });
  });

  
  if (modalClose) {
    modalClose.addEventListener("click", () => {
      closeModal();
    });
  }

  if (modalOverlay) {
    modalOverlay.addEventListener("click", (e) => {
      if (e.target === modalOverlay) {
        closeModal();
      }
    });
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modalOverlay.classList.contains("show")) {
      closeModal();
    }
  });
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