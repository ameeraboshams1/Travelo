// ================== Helpers ==================
const $ = (q, root = document) => root.querySelector(q);

function formatMoney(amount, currency = "USD") {
  const n = Number(amount) || 0;
  let symbol = "$";

  switch (currency) {
    case "EUR":
      symbol = "€";
      break;
    case "GBP":
      symbol = "£";
      break;
    case "JOD":
      symbol = "JOD ";
      break;
    default:
      symbol = "$";
  }
  return symbol + n.toFixed(2);
}

// ================== Main ==================
document.addEventListener("DOMContentLoaded", () => {
  // ---------- 1) قراءة باراميترات الـ URL ----------
  const q = new URLSearchParams(window.location.search);

  const currency      = (q.get("currency") || "USD").toUpperCase();
  const amountFlight  = parseFloat(q.get("amount_flight")  || "0");
  const amountHotel   = parseFloat(q.get("amount_hotel")   || "0");
  const amountPackage = parseFloat(q.get("amount_package") || "0");
  const amountTaxes   = parseFloat(q.get("amount_taxes")   || "0");

  const tripStart = q.get("trip_start") || "";
  const tripEnd   = q.get("trip_end")   || "";

  const adults   = parseInt(q.get("adults")   || "1", 10);
  const children = parseInt(q.get("children") || "0", 10);
  const infants  = parseInt(q.get("infants")  || "0", 10);

  // القيم الأساسية
  const subtotalInitial = amountFlight + amountHotel + amountPackage;
  let subtotal = subtotalInitial;
  let taxes    = amountTaxes;
  let discount = 0; // قيمة الخصم (لو كوبون)
  let total    = subtotal + taxes;

  // ---------- 2) عناصر الـ DOM الخاصة بالـ Summary ----------
  const subtotalTextEl = $("#subtotalText");
  const taxTextEl      = $("#taxText");
  const discountRow    = $("#discountRow");
  const discountTextEl = $("#discountText");
  const totalTextEl    = $("#totalText");

  // hidden inputs عشان يروحوا لـ payment_process.php
  const subtotalInput  = $("#subtotalInput");
  const taxInput       = $("#taxInput");
  const discountInput  = $("#discountInput");
  const totalInput     = $("#totalInput");
  const currencyInput  = $("#currencyInput");
  const promoInput     = $("#promoInput");

  // Trip info (الجزء الجانبي)
  const tripItems = document.querySelectorAll(".trip-info-item");

  // ---------- دالة تحديث الـ Summary ----------
  function refreshSummary() {
    total = subtotal + taxes;

    if (subtotalTextEl) subtotalTextEl.textContent = formatMoney(subtotal, currency);
    if (taxTextEl)      taxTextEl.textContent      = formatMoney(taxes, currency);
    if (totalTextEl)    totalTextEl.textContent    = formatMoney(total, currency);

    if (discountRow && discountTextEl) {
      if (discount > 0) {
        discountRow.style.display = "flex";
        discountTextEl.textContent = "-" + formatMoney(discount, currency);
      } else {
        discountRow.style.display = "none";
        discountTextEl.textContent = "-" + formatMoney(0, currency);
      }
    }

    // hidden inputs
    if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
    if (taxInput)      taxInput.value      = taxes.toFixed(2);
    if (discountInput) discountInput.value = discount.toFixed(2);
    if (totalInput)    totalInput.value    = total.toFixed(2);
    if (currencyInput) currencyInput.value = currency;
  }

  // تعبئة البداية من الـ URL
  refreshSummary();

  // ---------- 3) تعبئة معلومات الرحلة في الكارد الجانبي (اختياري) ----------
  if (tripItems.length >= 3) {
    const depVal  = tripItems[0].querySelector(".trip-info-value");
    const retVal  = tripItems[1].querySelector(".trip-info-value");
    const travVal = tripItems[2].querySelector(".trip-info-value");

    if (depVal && tripStart) depVal.textContent = tripStart;
    if (retVal && tripEnd)   retVal.textContent = tripEnd;

    if (travVal) {
      let txt = `${adults} Adult${adults > 1 ? "s" : ""}`;
      if (children) txt += ` · ${children} Child${children > 1 ? "ren" : ""}`;
      if (infants)  txt += ` · ${infants} Infant${infants > 1 ? "s" : ""}`;
      travVal.textContent = txt;
    }
  }

  // ---------- 4) الكوبون TRAVELO10 ----------
  const couponInputEl = $("#couponInput");
  const couponBtn     = $("#applyCouponBtn");
  const couponMsg     = $("#couponMessage");

  function resetCouponUI() {
    discount = 0;
    subtotal = subtotalInitial;
    if (promoInput) promoInput.value = "";
    if (couponMsg) {
      couponMsg.textContent =
        "Tip: Use TRAVELO10 to get 10% off on your subtotal.";
      couponMsg.className = "coupon-message";
    }
    refreshSummary();
  }

  if (couponBtn && couponInputEl) {
    couponBtn.addEventListener("click", () => {
      const code = (couponInputEl.value || "").trim().toUpperCase();

      if (!code) {
        resetCouponUI();
        return;
      }

      if (code === "TRAVELO10") {
        discount = subtotalInitial * 0.1;
        subtotal = subtotalInitial - discount;

        if (promoInput) promoInput.value = code;
        if (couponMsg) {
          couponMsg.textContent = "Coupon applied! 10% discount on subtotal.";
          couponMsg.className = "coupon-message success";
        }
      } else {
        if (couponMsg) {
          couponMsg.textContent = "Invalid or unsupported promo code.";
          couponMsg.className = "coupon-message error";
        }
        discount = 0;
        subtotal = subtotalInitial;
        if (promoInput) promoInput.value = "";
      }

      refreshSummary();
    });
  }

  // ---------- 5) اختيار وسيلة الدفع ----------
  const paymentOptions = document.querySelectorAll(".payment-option");
  const paymentMethodInput = $("#paymentMethodInput");

  let selectedMethod = "visa";

  paymentOptions.forEach((opt) => {
    opt.addEventListener("click", () => {
      paymentOptions.forEach((o) => o.classList.remove("active"));
      opt.classList.add("active");

      selectedMethod = opt.dataset.method || "visa";
      if (paymentMethodInput) paymentMethodInput.value = selectedMethod;
    });
  });

  // ---------- 6) تجهيز بيانات الكرت المخفية قبل الإرسال ----------
  const paymentForm        = $("#paymentForm");
  const cardNumberInput    = $("#cardNumber");
  const cardHolderInput    = $("#cardHolder");
  const cardExpInput       = $("#cardExp");
  const cardCvvInput       = $("#cardCvv");
  const saveCardCheckbox   = $("#saveCardCheckbox");

  const cardBrandHidden    = $("#cardBrandInput");
  const cardLast4Hidden    = $("#cardLast4Input");
  const cardHolderHidden   = $("#cardHolderInputHidden");
  const expMonthHidden     = $("#expMonthInput");
  const expYearHidden      = $("#expYearInput");
  const cardSavedHidden    = $("#cardSavedInput");

  function detectBrand(number) {
    if (/^4/.test(number)) return "visa";
    if (/^5[1-5]/.test(number)) return "mastercard";
    if (/^3[47]/.test(number)) return "amex";
    return "card";
  }

  if (paymentForm) {
    paymentForm.addEventListener("submit", (e) => {
      // هنا ممكن تعملي validation بسيط
      const numRaw = (cardNumberInput?.value || "").replace(/\s+/g, "");
      const holder = (cardHolderInput?.value || "").trim();
      const expRaw = (cardExpInput?.value || "").trim();
      const cvvRaw = (cardCvvInput?.value || "").trim();

      if (!numRaw || numRaw.length < 12 || !holder || !expRaw || cvvRaw.length < 3) {
        alert("Please fill in all card details correctly.");
        e.preventDefault();
        return;
      }

      const brand = detectBrand(numRaw);

      const [mm, yy] = expRaw.split("/").map((x) => x.trim());

      if (cardBrandHidden)  cardBrandHidden.value  = brand;
      if (cardLast4Hidden)  cardLast4Hidden.value  = numRaw.slice(-4);
      if (cardHolderHidden) cardHolderHidden.value = holder;
      if (expMonthHidden)   expMonthHidden.value   = mm || "";
      if (expYearHidden)    expYearHidden.value    = yy || "";
      if (cardSavedHidden)  cardSavedHidden.value  = saveCardCheckbox?.checked ? "1" : "0";

      // لو حابة تشيلي الـ alert هذا بعد ما تجهزي الـ payment_process.php
      // alert("Form ready to be sent to payment_process.php");
    });
  }
});
