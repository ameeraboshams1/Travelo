// ================== BOOKING WIZARD ==================
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);

  // --------- Core booking params from URL ---------
  const bookingType   = params.get("booking_type")   || "flight";
  const bookingStatus = params.get("booking_status") || "pending";

  const userName  = params.get("user_name")  || "Traveler";
  const userEmail = params.get("user_email") || "";
  const userId    = params.get("user_id")    || "";

  const flightId  = params.get("flight_id")  || "";
  const hotelId   = params.get("hotel_id")   || "";
  const packageId = params.get("package_id") || "";

  const tripStart = params.get("trip_start_date") || "";
  const tripEnd   = params.get("trip_end_date")   || tripStart;

  const travellersAdults   = params.get("travellers_adults")   || "1";
  const travellersChildren = params.get("travellers_children") || "0";
  const travellersInfants  = params.get("travellers_infants")  || "0";

  const amountFlight   = Number(params.get("amount_flight")   || "0");
  const amountHotel    = Number(params.get("amount_hotel")    || "0");
  const amountPackage  = Number(params.get("amount_package")  || "0");
  const amountTaxes    = Number(params.get("amount_taxes")    || "0");
  const discountAmount = Number(params.get("discount_amount") || "0");
  const currency       = params.get("currency") || "USD";

  const subtotal    = amountFlight + amountHotel + amountPackage;
  const totalAmount = subtotal + amountTaxes - discountAmount;

  // --------- Flight display-only info ---------
  const fromCity      = params.get("from_city")          || "";
  const toCity        = params.get("to_city")            || "";
  const fromAirport   = params.get("from_airport_code")  || "";
  const toAirport     = params.get("to_airport_code")    || "";
  const departureTime = params.get("departure_time")     || "";
  const arrivalTime   = params.get("arrival_time")       || "";
  const airline       = params.get("airline")            || "";
  const flightNumber  = params.get("flight_number")      || "";

  // --------- Hotel display-only info ---------
  const hotelName      = params.get("hotel_name")      || "";
  const hotelLocation  = params.get("hotel_location")  || "";
  const hotelCityAlt   = params.get("hotel_city")      || "";
  const hotelCity      = hotelCityAlt || hotelLocation || params.get("to_city") || "";
  const hotelNightsStr = params.get("stay_nights")     || params.get("nights") || "";
  const roomType       = params.get("room_type")       || "";
  const boardType      = params.get("board_type")      || "";

  // --------- Package display-only info ---------
  const packageTitle   = params.get("package_title") || "";
  const packageCity    = params.get("package_city")  || "";
  const packageNightsStr  = params.get("pkg_nights")    || "";
  const packageCombo   = params.get("pkg_combo")     || "";

  // --------- Generate booking code (front-end) ---------
  function generateBookingCode() {
    const now = new Date();
    const y   = now.getFullYear().toString().slice(-2);
    const m   = String(now.getMonth() + 1).padStart(2, "0");
    const d   = String(now.getDate()).padStart(2, "0");
    const rand = Math.random().toString(36).substring(2, 6).toUpperCase();
    return `TRV-${y}${m}${d}-${rand}`;
  }
  const bookingCode = params.get("booking_code") || generateBookingCode();

  // ================== DOM refs ==================
  const headerUserName  = document.getElementById("headerUserName");
  const headerUserEmail = document.getElementById("headerUserEmail");

  const ticketTypeBadge   = document.getElementById("ticketTypeBadge");
  const ticketTitle       = document.getElementById("ticketTitle");
  const ticketSubtitle    = document.getElementById("ticketSubtitle");
  const ticketBookingCode = document.getElementById("ticketBookingCode");
  const ticketRouteMain   = document.getElementById("ticketRouteMain");
  const ticketMetaLine    = document.getElementById("ticketMetaLine");
  const ticketDates       = document.getElementById("ticketDates");
  const ticketUserName    = document.getElementById("ticketUserName");
  const ticketExtraRow    = document.getElementById("ticketExtraRow");
  const ticketTotalAmount = document.getElementById("ticketTotalAmount");
  const ticketNotes       = document.getElementById("ticketNotes");

  const currencyLabel = document.getElementById("currencyLabel");

  // Summary (right card – step 1)
  const rowAmountFlight   = document.getElementById("rowAmountFlight");
  const rowAmountHotel    = document.getElementById("rowAmountHotel");
  const rowAmountPackage  = document.getElementById("rowAmountPackage");
  const rowDiscount       = document.getElementById("rowDiscount");

  const amountFlightValue   = document.getElementById("amountFlightValue");
  const amountHotelValue    = document.getElementById("amountHotelValue");
  const amountPackageValue  = document.getElementById("amountPackageValue");
  const amountTaxesValue    = document.getElementById("amountTaxesValue");
  const amountDiscountValue = document.getElementById("amountDiscountValue");
  const amountTotalValue    = document.getElementById("amountTotalValue");

  // Payment summary (step 2)
  const summaryTripLine    = document.getElementById("summaryTripLine");
  const summarySubtotal    = document.getElementById("summarySubtotal");
  const summaryTaxes       = document.getElementById("summaryTaxes");
  const summaryDiscount    = document.getElementById("summaryDiscount");
  const summaryDiscountRow = document.getElementById("summaryDiscountRow");
  const summaryTotal       = document.getElementById("summaryTotal");
  const btnPayAmountLabel  = document.getElementById("btnPayAmountLabel");

  // Done step
  const doneUserName    = document.getElementById("doneUserName");
  const doneBookingCode = document.getElementById("doneBookingCode");
  const doneTripLine    = document.getElementById("doneTripLine");
  const doneTotalPaid   = document.getElementById("doneTotalPaid");
  const doneUserEmail   = document.getElementById("doneUserEmail");

  // Hidden meta form (for back-end)
  const metaForm = document.getElementById("bookingMeta");

  // ================== Helpers ==================
  function formatMoney(num) {
    const n = Number(num) || 0;
    return (currency === "USD" ? "$" : currency + " ") + n.toFixed(2);
  }

  function cleanDate(str) {
    if (!str) return "";
    return str;
  }

  function ensureHidden(name, value) {
    if (!metaForm) return;
    let input = metaForm.querySelector(`input[name="${name}"]`);
    if (!input) {
      input = document.createElement("input");
      input.type = "hidden";
      input.name = name;
      metaForm.appendChild(input);
    }
    input.value = value;
  }

  // احسب عدد الليالي للباك إند
  function computeStayNights() {
    let n = 0;
    if (bookingType === "hotel") {
      n = Number(hotelNightsStr || "0");
    } else if (bookingType === "package") {
      n = Number(packageNightsStr || "0");
    } else if (tripStart && tripEnd) {
      const d1 = new Date(tripStart);
      const d2 = new Date(tripEnd);
      const diff = (d2 - d1) / (1000 * 60 * 60 * 24);
      if (!Number.isNaN(diff) && diff > 0) n = diff;
    }
    if (!Number.isFinite(n) || n < 0) n = 0;
    return Math.round(n);
  }
  const stayNights = computeStayNights();

  // ================== Fill header & meta ==================
  if (headerUserName)  headerUserName.textContent  = userName;
  if (headerUserEmail) headerUserEmail.textContent = userEmail;

  if (ticketBookingCode) ticketBookingCode.textContent = bookingCode;
  if (ticketUserName)    ticketUserName.textContent    = userName;
  if (currencyLabel)     currencyLabel.textContent     = currency;
  if (ticketTotalAmount) ticketTotalAmount.textContent = formatMoney(totalAmount);
  if (amountTotalValue)  amountTotalValue.textContent  = formatMoney(totalAmount);

  const dateRange = tripStart
    ? `${cleanDate(tripStart)} – ${cleanDate(tripEnd)}`
    : "";
  if (ticketDates && dateRange) ticketDates.textContent = dateRange;

  const travellerPieces = [];
  if (Number(travellersAdults) > 0) {
    travellerPieces.push(
      `${travellersAdults} adult${travellersAdults === "1" ? "" : "s"}`
    );
  }
  if (Number(travellersChildren) > 0) {
    travellerPieces.push(`${travellersChildren} child`);
  }
  if (Number(travellersInfants) > 0) {
    travellerPieces.push(`${travellersInfants} infant`);
  }
  const travellersLabel = travellerPieces.join(" · ") || "1 adult";

  if (ticketMetaLine) {
    let typeLabel = "";
    if (bookingType === "flight")  typeLabel = "Flight";
    else if (bookingType === "hotel")  typeLabel = "Hotel stay";
    else if (bookingType === "package") typeLabel = "Travel package";

    ticketMetaLine.textContent = `${typeLabel} · ${travellersLabel}`;
  }

  // ---- booking type specific ticket ----
  if (bookingType === "flight") {
    if (ticketTypeBadge) ticketTypeBadge.textContent = "Flight ticket";
    if (ticketTitle)
      ticketTitle.textContent = `${fromCity || "Your city"} → ${
        toCity || "Destination"
      }`;
    if (ticketSubtitle) {
      ticketSubtitle.textContent =
        airline && flightNumber
          ? `${airline} · Flight ${flightNumber}`
          : "Review your flight details before paying.";
    }

    if (ticketRouteMain) {
      const fromLabel = fromAirport
        ? `${fromCity || "Origin"} (${fromAirport})`
        : fromCity || "Origin";
      const toLabel = toAirport
        ? `${toCity || "Destination"} (${toAirport})`
        : toCity || "Destination";
      ticketRouteMain.textContent = `${fromLabel} → ${toLabel}`;
    }

    if (ticketExtraRow) {
      ticketExtraRow.innerHTML = `
        <div class="ticket-extra-col">
          <div class="ticket-extra-label">Departure</div>
          <div class="ticket-extra-value">${departureTime || "--:--"}</div>
        </div>
        <div class="ticket-extra-col">
          <div class="ticket-extra-label">Arrival</div>
          <div class="ticket-extra-value">${arrivalTime || "--:--"}</div>
        </div>
        <div class="ticket-extra-col">
          <div class="ticket-extra-label">Airline</div>
          <div class="ticket-extra-value">${airline || "TBA"}</div>
        </div>
      `;
    }
  } else if (bookingType === "hotel") {
    if (ticketTypeBadge) ticketTypeBadge.textContent = "Hotel booking";
    if (ticketTitle)  ticketTitle.textContent = hotelName || "Your hotel";
    if (ticketSubtitle) {
      ticketSubtitle.textContent =
        (hotelCity || hotelLocation)
          ? `${hotelCity || hotelLocation} · ${roomType || "Room"}`
          : "Review your stay details.";
    }

    if (ticketRouteMain) {
      ticketRouteMain.textContent = hotelCity || hotelLocation || "Destination";
    }

    if (ticketExtraRow) {
      ticketExtraRow.innerHTML = `
        <div class="ticket-extra-col">
          <div class="ticket-extra-label">Nights</div>
          <div class="ticket-extra-value">${hotelNightsStr || "-"}</div>
        </div>
        <div class="ticket-extra-col">
          <div class="ticket-extra-label">Room type</div>
          <div class="ticket-extra-value">${roomType || "Standard room"}</div>
        </div>
        <div class="ticket-extra-col">
          <div class="ticket-extra-label">Board</div>
          <div class="ticket-extra-value">${boardType || "Room only"}</div>
        </div>
      `;
    }
  } else if (bookingType === "package") {
    if (ticketTypeBadge) ticketTypeBadge.textContent = "Package booking";
    if (ticketTitle)  ticketTitle.textContent = packageTitle || "Travel package";
    if (ticketSubtitle) {
      ticketSubtitle.textContent =
        packageCity || packageCombo
          ? `${packageCity || ""} ${
              packageCombo ? "· " + packageCombo : ""
            }`
          : "Review your package details.";
    }

    if (ticketRouteMain) {
      ticketRouteMain.textContent = packageCity || "Destination";
    }

    if (ticketExtraRow) {
      ticketExtraRow.innerHTML = `
        <div class="ticket-extra-col">
          <div class="ticket-extra-label">Nights</div>
          <div class="ticket-extra-value">${packageNightsStr || "-"}</div>
        </div>
        <div class="ticket-extra-col">
          <div class="ticket-extra-label">Combo</div>
          <div class="ticket-extra-value">${packageCombo || "Flight + Hotel"}</div>
        </div>
        <div class="ticket-extra-col">
          <div class="ticket-extra-label">Travelers</div>
          <div class="ticket-extra-value">${travellersLabel}</div>
        </div>
      `;
    }
  }

  if (ticketNotes) {
    ticketNotes.textContent =
      "Please review trip dates, travellers and total amount carefully. After payment, changes may require contacting Travelo support.";
  }

  // ---- fill hidden meta form ----
  if (metaForm) {
    metaForm.querySelector("#hfBookingType").value = bookingType;
    metaForm.querySelector("#hfUserId").value    = userId;
    metaForm.querySelector("#hfUserName").value  = userName;
    metaForm.querySelector("#hfUserEmail").value = userEmail;

    metaForm.querySelector("#hfFlightId").value  = flightId;
    metaForm.querySelector("#hfHotelId").value   = hotelId;
    metaForm.querySelector("#hfPackageId").value = packageId;

    metaForm.querySelector("#hfTripStart").value = tripStart;
    metaForm.querySelector("#hfTripEnd").value   = tripEnd;
    metaForm.querySelector("#hfAdults").value    = travellersAdults;
    metaForm.querySelector("#hfChildren").value  = travellersChildren;
    metaForm.querySelector("#hfInfants").value   = travellersInfants;

    metaForm.querySelector("#hfAmountFlight").value   = amountFlight.toString();
    metaForm.querySelector("#hfAmountHotel").value    = amountHotel.toString();
    metaForm.querySelector("#hfAmountPackage").value  = amountPackage.toString();
    metaForm.querySelector("#hfAmountTaxes").value    = amountTaxes.toString();
    metaForm.querySelector("#hfDiscount").value       = discountAmount.toString();
    metaForm.querySelector("#hfCurrency").value       = currency;
    metaForm.querySelector("#hfTotalAmount").value    = totalAmount.toString();

    // ✅ booking code + stay nights
    const stayInput = metaForm.querySelector("#hfStayNights");
    if (stayInput) stayInput.value = String(stayNights);

    const codeInput = metaForm.querySelector("#hfBookingCode");
    if (codeInput) codeInput.value = bookingCode;

    // نضيف booking_status بس كـ hidden جديد
    ensureHidden("booking_status", bookingStatus);
  }

  // ================== Fill amounts (step 1 + 2) ==================
  if (rowAmountFlight)  rowAmountFlight.style.display  = amountFlight  > 0 ? "flex" : "none";
  if (rowAmountHotel)   rowAmountHotel.style.display   = amountHotel   > 0 ? "flex" : "none";
  if (rowAmountPackage) rowAmountPackage.style.display = amountPackage > 0 ? "flex" : "none";
  if (rowDiscount)      rowDiscount.style.display      = discountAmount> 0 ? "flex" : "none";

  if (amountFlightValue)   amountFlightValue.textContent   = formatMoney(amountFlight);
  if (amountHotelValue)    amountHotelValue.textContent    = formatMoney(amountHotel);
  if (amountPackageValue)  amountPackageValue.textContent  = formatMoney(amountPackage);
  if (amountTaxesValue)    amountTaxesValue.textContent    = formatMoney(amountTaxes);
  if (amountDiscountValue)
    amountDiscountValue.textContent =
      "-" + formatMoney(discountAmount).replace(
        currency === "USD" ? "$" : currency + " ",
        ""
      );

  if (summarySubtotal) summarySubtotal.textContent = formatMoney(subtotal);
  if (summaryTaxes)    summaryTaxes.textContent    = formatMoney(amountTaxes);
  if (summaryDiscount)
    summaryDiscount.textContent =
      "-" + formatMoney(discountAmount).replace(
        currency === "USD" ? "$" : currency + " ",
        ""
      );
  if (summaryDiscountRow)
    summaryDiscountRow.style.display =
      discountAmount > 0 ? "flex" : "none";
  if (summaryTotal) summaryTotal.textContent = formatMoney(totalAmount);

  if (btnPayAmountLabel) btnPayAmountLabel.textContent = `· ${formatMoney(totalAmount)}`;

  if (summaryTripLine) {
    if (bookingType === "flight") {
      summaryTripLine.textContent = `${fromCity || "Origin"} → ${
        toCity || "Destination"
      } · ${travellersLabel}`;
    } else if (bookingType === "hotel") {
      summaryTripLine.textContent = `${hotelName || "Hotel"} · ${
        hotelCity || hotelLocation || ""
      } · ${travellersLabel}`;
    } else {
      summaryTripLine.textContent = `${packageTitle || "Package"} · ${travellersLabel}`;
    }
  }

  // Done step static info
  if (doneUserName)    doneUserName.textContent    = userName;
  if (doneBookingCode) doneBookingCode.textContent = bookingCode;
  if (doneUserEmail)   doneUserEmail.textContent   = userEmail;
  if (doneTotalPaid)   doneTotalPaid.textContent   = formatMoney(totalAmount);
  if (doneTripLine) {
    if (bookingType === "flight") {
      doneTripLine.textContent = `${fromCity || "Origin"} → ${
        toCity || "Destination"
      }`;
    } else if (bookingType === "hotel") {
      doneTripLine.textContent =
        hotelName || hotelCity || hotelLocation || "Hotel";
    } else {
      doneTripLine.textContent =
        packageTitle || packageCity || "Package";
    }
  }

  // ================== Stepper logic ==================
  const stepItems  = Array.from(document.querySelectorAll(".step-item"));
  const stepPanels = Array.from(document.querySelectorAll(".step-panel"));

  function goToStep(step) {
    stepItems.forEach((item) => {
      const s = Number(item.dataset.step);
      item.classList.toggle("is-active", s === step);
      item.classList.toggle("is-completed", s < step);
    });

    stepPanels.forEach((panel) => {
      const s = Number(panel.dataset.stepPanel);
      panel.classList.toggle("is-active", s === step);
    });

    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  const btnToPayment      = document.getElementById("btnToPayment");
  const btnStep1BackHome  = document.getElementById("btnStep1BackHome");
  const btnBackToStep1    = document.getElementById("btnBackToStep1");
  const btnBackHome       = document.getElementById("btnBackHome");
  const btnPrintTicket    = document.getElementById("btnPrintTicket");

  if (btnToPayment) {
    btnToPayment.addEventListener("click", () => goToStep(2));
  }
  if (btnStep1BackHome) {
    btnStep1BackHome.addEventListener("click", () => {
      window.location.href = "index.php";
    });
  }
  if (btnBackToStep1) {
    btnBackToStep1.addEventListener("click", () => goToStep(1));
  }
  if (btnBackHome) {
    btnBackHome.addEventListener("click", () => {
      window.location.href = "index.php";
    });
  }
  if (btnPrintTicket) {
    btnPrintTicket.addEventListener("click", () => window.print());
  }

  // ================== Payment form ==================
  const paymentForm  = document.getElementById("paymentForm");
  const paymentError = document.getElementById("paymentError");

  const cardHolderInput = document.getElementById("cardHolder");
  const cardNumberInput = document.getElementById("cardNumber");
  const expMonthInput   = document.getElementById("expMonth");
  const expYearInput    = document.getElementById("expYear");
  const cvvInput        = document.getElementById("cvv");
  const promoCodeInput  = document.getElementById("promoCode");
  const saveCardInput   = document.getElementById("saveCard");

  function getPaymentMethod() {
    const radios = document.querySelectorAll('input[name="payment_method"]');
    for (const r of radios) {
      if (r.checked) return r.value;
    }
    return "visa";
  }

  function showPaymentError(msg) {
    if (!paymentError) return;
    paymentError.textContent = msg;
    paymentError.style.display = "block";
  }

  if (paymentForm) {
    paymentForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (paymentError) paymentError.style.display = "none";

      const method = getPaymentMethod();

      // ---- Basic card validation (للفيزا/ماستر فقط) ----
      if (method !== "cashcard") {
        if (!cardHolderInput.value.trim()) {
          return showPaymentError("Please enter the card holder name.");
        }
        if (
          !/^\d{12,19}$/.test(
            cardNumberInput.value.replace(/\s+/g, "")
          )
        ) {
          return showPaymentError("Please enter a valid card number.");
        }
        if (!/^\d{2}$/.test(expMonthInput.value.trim())) {
          return showPaymentError("Please enter a valid expiry month (MM).");
        }
        if (!/^\d{2,4}$/.test(expYearInput.value.trim())) {
          return showPaymentError("Please enter a valid expiry year.");
        }
        if (!/^\d{3,4}$/.test(cvvInput.value.trim())) {
          return showPaymentError("Please enter a valid CVV code.");
        }
      }

      // ---- Build FormData: meta + payment ----
      const formData = new FormData();

      // meta (hidden)
      if (metaForm) {
        const metaFd = new FormData(metaForm);
        for (const [key, value] of metaFd.entries()) {
          formData.append(key, value);
        }
      }

      // payment method
      formData.append("payment_method", method);

      // card data
      if (method !== "cashcard") {
        const rawNumber = cardNumberInput.value.replace(/\s+/g, "");
        formData.append("card_holder_name", cardHolderInput.value.trim());
        formData.append("card_number", rawNumber);
        formData.append("exp_month", expMonthInput.value.trim());
        formData.append("exp_year", expYearInput.value.trim());
        formData.append("cvv", cvvInput.value.trim());
      }

      // promo code
      const promo = promoCodeInput.value.trim();
      if (promo) formData.append("promo_code", promo);

      // card_saved
      formData.append("card_saved", saveCardInput.checked ? "1" : "0");

      try {
        const response = await fetch("./booking.php", {
          method: "POST",
          body: formData,
        });

        let data = null;
        try {
          data = await response.json();
        } catch (err) {
          console.error("Cannot parse JSON:", err);
        }

        if (!response.ok || !data) {
          console.error("Raw response:", response, data);
          return showPaymentError(
            `Server error (${response.status}). Please try again.`
          );
        }

        if (!data.success) {
          console.error("Payment error from server:", data.message);
          return showPaymentError(
            data.message || "Payment failed. Please try again."
          );
        }

        // ✅ success from back-end → go to confirmation step
        goToStep(3);
      } catch (err) {
        console.error("Network error:", err);
        showPaymentError("Network error. Please try again.");
      }
    });
  }

  // ================== Promo button (decorative) ==================
  const btnApplyPromo = document.getElementById("btnApplyPromo");
  const promoHelp     = document.getElementById("promoHelp");
  if (btnApplyPromo && promoHelp && promoCodeInput) {
    btnApplyPromo.addEventListener("click", () => {
      const code = promoCodeInput.value.trim();
      if (!code) {
        promoHelp.textContent = "Please enter a promo code first.";
        promoHelp.classList.add("text-danger");
        return;
      }
      promoHelp.textContent = "Promo code will be validated during payment.";
      promoHelp.classList.remove("text-danger");
    });
  }
});
