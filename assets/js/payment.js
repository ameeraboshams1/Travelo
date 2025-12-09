 const paymentOptions = document.querySelectorAll(".payment-option");

    paymentOptions.forEach((opt) => {
      opt.addEventListener("click", () => {
        paymentOptions.forEach((o) => o.classList.remove("active"));
        opt.classList.add("active");
      });
    });

  
    const currencySymbol = "$";
    let subtotal = 7000 + 2500 + 1500; 
    const tax = 800;
    let discountValue = 0;
    let couponApplied = false;

    const totalAmountEl = document.getElementById("totalAmount");
    const discountRow = document.getElementById("discountRow");
    const discountAmountEl = document.getElementById("discountAmount");
    const couponInput = document.getElementById("couponInput");
    const couponMessage = document.getElementById("couponMessage");
    const applyCouponBtn = document.getElementById("applyCouponBtn");
    const summaryItems = document.getElementById("summaryItems");

    function formatCurrency(amount) {
      return currencySymbol + amount.toLocaleString("en-US");
    }

    function updateTotals() {
      const total = subtotal + tax - discountValue;
      totalAmountEl.textContent = formatCurrency(total);
      discountAmountEl.textContent = "- " + formatCurrency(discountValue);

      if (discountValue > 0) {
        discountRow.style.display = "flex";
        gsap.from(discountRow, { y: -4, opacity: 0, duration: 0.4 });
      } else {
        discountRow.style.display = "none";
      }

      gsap.from(summaryItems, {
        scale: 0.98,
        duration: 0.25,
        ease: "power1.inOut"
      });
    }

    updateTotals();

    applyCouponBtn.addEventListener("click", () => {
      const code = couponInput.value.trim().toUpperCase();

      if (!code) {
        couponMessage.textContent = "Please enter a promo code.";
        couponMessage.className = "coupon-message error";
        gsap.from(couponMessage, { x: -6, duration: 0.15, repeat: 1, yoyo: true });
        return;
      }

      if (code === "TRAVELO10") {
        if (couponApplied) {
          couponMessage.textContent = "TRAVELO10 is already applied to your booking.";
          couponMessage.className = "coupon-message";
          return;
        }
        couponApplied = true;
        discountValue = Math.round(subtotal * 0.1);
        couponMessage.textContent = "Great! 10% discount applied on your subtotal.";
        couponMessage.className = "coupon-message success";
        updateTotals();
      } else {
        discountValue = 0;
        couponApplied = false;
        updateTotals();
        couponMessage.textContent = "This code is not valid for this booking.";
        couponMessage.className = "coupon-message error";
        gsap.from(couponMessage, { x: -6, duration: 0.15, repeat: 1, yoyo: true });
      }
    });

    window.addEventListener("load", () => {
      gsap.from(".card-animate", {
        y: 26,
        opacity: 0,
        duration: 0.7,
        ease: "power3.out",
        stagger: 0.12
      });

      gsap.from(".payment-footer", {
        y: 20,
        opacity: 0,
        duration: 0.7,
        delay: 0.2,
        ease: "power3.out"
      });
    });