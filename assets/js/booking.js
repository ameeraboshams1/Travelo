// ================== BOOKING WIZARD (FULL) ==================
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);

  // ================== Resume booking (from MyBooking) ==================
  const resumeBooking = window.TRAVELO?.resumeBooking || null;
  const isResume = !!(resumeBooking && resumeBooking.id);

  // ---------- helpers ----------
  const str = (v, fb = "") => (v === null || v === undefined ? fb : String(v));
  const num = (v, fb = 0) => {
    const n = Number(v);
    return Number.isFinite(n) ? n : fb;
  };

  const isDateOnly = (v) => /^\d{4}-\d{2}-\d{2}$/.test(String(v || "").trim());
  const isTimeOnly = (v) => /^\d{2}:\d{2}(:\d{2})?$/.test(String(v || "").trim());
  const looksLikeDateTime = (v) => /^\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}/.test(String(v || "").trim());

  const pad2 = (x) => String(x).padStart(2, "0");

  function combineDateTime(dateStr, timeStr) {
    const d = String(dateStr || "").trim();
    const t = String(timeStr || "").trim();
    if (!d) return "";
    if (!t) return d; // date only
    const hhmm = t.slice(0, 5);
    return `${d}T${hhmm}`; // datetime-local
  }

  function toDateOnly(v) {
    const s = String(v || "").trim();
    if (!s) return "";
    if (isDateOnly(s)) return s;
    if (looksLikeDateTime(s)) return s.slice(0, 10);
    return "";
  }

  function addDays(dateStr, days) {
    const d = String(dateStr || "").trim();
    if (!d) return "";
    const dt = new Date(d + "T00:00:00");
    if (Number.isNaN(dt.getTime())) return "";
    dt.setDate(dt.getDate() + Number(days || 0));
    return `${dt.getFullYear()}-${pad2(dt.getMonth() + 1)}-${pad2(dt.getDate())}`;
  }

  function diffDays(dateA, dateB) {
    const a = toDateOnly(dateA);
    const b = toDateOnly(dateB);
    if (!a || !b) return 0;
    const d1 = new Date(a + "T00:00:00");
    const d2 = new Date(b + "T00:00:00");
    const diff = (d2 - d1) / (1000 * 60 * 60 * 24);
    if (!Number.isFinite(diff)) return 0;
    return Math.max(0, Math.round(diff));
  }

  function displayDT(v) {
    const s = String(v || "").trim();
    if (!s) return "—";
    if (isTimeOnly(s)) return "—"; // ما نعرض وقت لحاله

    const date = toDateOnly(s) || s.slice(0, 10);
    let time = "";

    if (s.includes("T")) time = s.split("T")[1] || "";
    else if (s.includes(" ")) time = s.split(" ")[1] || "";

    const hhmm = time ? time.slice(0, 5) : "";
    return hhmm ? `${date} • ${hhmm}` : date;
  }

  function pickValidDateParam(keys) {
    for (const k of keys) {
      const v = (params.get(k) || "").trim();
      if (!v) continue;
      if (isTimeOnly(v)) continue;
      if (isDateOnly(v) || looksLikeDateTime(v)) return v;
    }
    return "";
  }

  function todayDate() {
    const now = new Date();
    return `${now.getFullYear()}-${pad2(now.getMonth() + 1)}-${pad2(now.getDate())}`;
  }

  // ================== Core booking params (URL OR resumeBooking) ==================
  const bookingType = isResume
    ? (resumeBooking.booking_type || "flight")
    : (params.get("booking_type") || "flight");

  const bookingStatus = isResume
    ? (resumeBooking.booking_status || "pending")
    : (params.get("booking_status") || "pending");

  // user info (prefer session/global)
  const userName = window.TRAVELO?.userName
    ? String(window.TRAVELO.userName || "Traveler")
    : (params.get("user_name") || "Traveler");

  const userEmail = window.TRAVELO?.userEmail
    ? String(window.TRAVELO.userEmail || "")
    : (params.get("user_email") || "");

  const userId = window.TRAVELO?.userId
    ? String(window.TRAVELO.userId || "")
    : (params.get("user_id") || "");

  // IDs (IMPORTANT: let, because package may fill them from API)
  let flightId  = isResume ? str(resumeBooking.flight_id, "")  : (params.get("flight_id") || "");
  let hotelId   = isResume ? str(resumeBooking.hotel_id, "")   : (params.get("hotel_id") || "");
  const packageId = isResume ? str(resumeBooking.package_id, "") : (params.get("package_id") || "");

  // --------- Flight display-only info (URL only) ---------
  const fromCity      = params.get("from_city")          || "";
  const toCity        = params.get("to_city")            || "";
  const fromAirport   = params.get("from_airport_code")  || "";
  const toAirport     = params.get("to_airport_code")    || "";

  // These might be overwritten for package from flights API
  let departureTime = params.get("departure_time") || params.get("depart_time") || "";
  let arrivalTime   = params.get("arrival_time")   || "";
  let returnTime    = params.get("return_time")    || params.get("return_departure_time") || arrivalTime || "";

  let airline       = params.get("airline")       || "";
  let flightNumber  = params.get("flight_number") || "";

  // ✅ IMPORTANT: real dates (ignore time-only)
  const depDateParam = pickValidDateParam([
    "departure_date",
    "depart_date",
    "start_date",
    "pkg_start_date",
    "trip_start_date",
  ]);

  const retDateParam = pickValidDateParam([
    "return_date",
    "end_date",
    "pkg_end_date",
    "trip_end_date",
  ]);

  // dates (raw)
  let tripStart = isResume ? str(resumeBooking.trip_start_date, "") : (params.get("trip_start_date") || depDateParam || "");
  let tripEnd   = isResume ? str(resumeBooking.trip_end_date, tripStart) : (params.get("trip_end_date") || retDateParam || tripStart);

  // --------- Hotel display-only info (URL only) ---------
  const hotelName      = params.get("hotel_name")      || "";
  const hotelLocation  = params.get("hotel_location")  || "";
  const hotelCityAlt   = params.get("hotel_city")      || "";
  const hotelCity      = hotelCityAlt || hotelLocation || params.get("to_city") || "";
  const hotelNightsStr = params.get("stay_nights")     || params.get("nights") || "";
  const roomType       = params.get("room_type")       || "";
  const boardType      = params.get("board_type")      || "";

  // --------- Package display-only info (URL only) ---------
  const packageTitle      = params.get("package_title") || "";
  const packageCity       = params.get("package_city")  || "";
  const packageNightsStr  = params.get("duration_days") || params.get("pkg_nights") || params.get("stay_nights") || "";
  const packageCombo      = params.get("pkg_combo")     || "";

  // travellers
  const travellersAdults   = isResume ? str(resumeBooking.travellers_adults, "1")   : (params.get("travellers_adults") || "1");
  const travellersChildren = isResume ? str(resumeBooking.travellers_children, "0") : (params.get("travellers_children") || "0");
  const travellersInfants  = isResume ? str(resumeBooking.travellers_infants, "0")  : (params.get("travellers_infants") || "0");

  // money
  const currency = isResume ? str(resumeBooking.currency, "USD") : (params.get("currency") || "USD");

  const amountFlight   = isResume ? num(resumeBooking.amount_flight, 0)   : num(params.get("amount_flight"), 0);
  const amountHotel    = isResume ? num(resumeBooking.amount_hotel, 0)    : num(params.get("amount_hotel"), 0);
  const amountPackage  = isResume ? num(resumeBooking.amount_package, 0)  : num(params.get("amount_package"), 0);
  const amountTaxes    = isResume ? num(resumeBooking.amount_taxes, 0)    : num(params.get("amount_taxes"), 0);
  const discountAmount = isResume ? num(resumeBooking.discount_amount, 0) : num(params.get("discount_amount"), 0);

  const subtotal = amountFlight + amountHotel + amountPackage;

  const totalAmount = isResume
    ? num(resumeBooking.total_amount, subtotal + amountTaxes - discountAmount)
    : (subtotal + amountTaxes - discountAmount);

  // --------- Generate booking code (front-end) ---------
  function generateBookingCode() {
    const now = new Date();
    const y   = now.getFullYear().toString().slice(-2);
    const m   = String(now.getMonth() + 1).padStart(2, "0");
    const d   = String(now.getDate()).padStart(2, "0");
    const rand = Math.random().toString(36).substring(2, 6).toUpperCase();
    return `TRV-${y}${m}${d}-${rand}`;
  }

  const bookingCode = isResume
    ? (str(resumeBooking.booking_code, "") || generateBookingCode())
    : (params.get("booking_code") || generateBookingCode());

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

  const tripDatesGrid = document.getElementById("tripDatesGrid");
  const tripStartView = document.getElementById("tripStartView");
  const tripEndView   = document.getElementById("tripEndView");

  const hotelDatesForm  = document.getElementById("hotelDatesForm");
  const hotelStartInput = document.getElementById("hotelStartInput");
  const hotelEndInput   = document.getElementById("hotelEndInput");
  const hotelDatesHint  = document.getElementById("hotelDatesHint");

  const currencyLabel = document.getElementById("currencyLabel");

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

  const summaryTripLine    = document.getElementById("summaryTripLine");
  const summarySubtotal    = document.getElementById("summarySubtotal");
  const summaryTaxes       = document.getElementById("summaryTaxes");
  const summaryDiscount    = document.getElementById("summaryDiscount");
  const summaryDiscountRow = document.getElementById("summaryDiscountRow");
  const summaryTotal       = document.getElementById("summaryTotal");
  const btnPayAmountLabel  = document.getElementById("btnPayAmountLabel");

  const doneUserName    = document.getElementById("doneUserName");
  const doneBookingCode = document.getElementById("doneBookingCode");
  const doneTripLine    = document.getElementById("doneTripLine");
  const doneTotalPaid   = document.getElementById("doneTotalPaid");
  const doneUserEmail   = document.getElementById("doneUserEmail");

  const doneTitleEl = document.querySelector(".done-card h2");
  const doneDescEl  = document.querySelector(".done-card p");

  const metaForm = document.getElementById("bookingMeta");

  // ================== Helpers ==================
  function formatMoney(numVal) {
    const n = Number(numVal) || 0;
    return (currency === "USD" ? "$" : currency + " ") + n.toFixed(2);
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

  function computeStayNights() {
    let n = 0;
    if (bookingType === "hotel") n = Number(hotelNightsStr || "0");
    else if (bookingType === "package") n = Number(packageNightsStr || "0");
    else if (tripStart && tripEnd) n = diffDays(tripStart, tripEnd);
    if (!Number.isFinite(n) || n < 0) n = 0;
    return Math.round(n);
  }

  let stayNights = computeStayNights();

  function updateDatesUI() {
    if (ticketDates) {
      if (tripStart) ticketDates.textContent = `${displayDT(tripStart)} – ${displayDT(tripEnd || tripStart)}`;
      else ticketDates.textContent = "";
    }

    if (tripDatesGrid) {
      const has = !!tripStart;
      tripDatesGrid.hidden = !has;
      if (has) {
        if (tripStartView) tripStartView.textContent = displayDT(tripStart);
        if (tripEndView)   tripEndView.textContent   = displayDT(tripEnd || tripStart);
      }
    }
  }

  function syncHiddenCore() {
    if (!metaForm) return;
    metaForm.querySelector("#hfTripStart") && (metaForm.querySelector("#hfTripStart").value = tripStart);
    metaForm.querySelector("#hfTripEnd")   && (metaForm.querySelector("#hfTripEnd").value = tripEnd);
    metaForm.querySelector("#hfStayNights") && (metaForm.querySelector("#hfStayNights").value = String(stayNights));

    metaForm.querySelector("#hfFlightId") && (metaForm.querySelector("#hfFlightId").value = flightId);
    metaForm.querySelector("#hfHotelId")  && (metaForm.querySelector("#hfHotelId").value  = hotelId);
    metaForm.querySelector("#hfPackageId")&& (metaForm.querySelector("#hfPackageId").value= packageId);
  }

  // ================== Package helpers: load package + flight ==================
  async function fetchJson(url) {
    const r = await fetch(url, { headers: { "Accept": "application/json" } });
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    return await r.json();
  }

  async function loadPackageRowById(id) {
    if (!id) return null;
    const list = await fetchJson(`./API/packages.php?action=list`);
    const pid = String(id);
    return Array.isArray(list) ? (list.find(x => String(x.id) === pid) || null) : null;
  }

  async function loadFlightRowById(id) {
    if (!id) return null;
    const list = await fetchJson(`./API/flights.php?action=list`);
    const fid = String(id);
    return Array.isArray(list) ? (list.find(x => String(x.id) === fid) || null) : null;
  }

  function normalizeTime(t) {
    const s = String(t || "").trim();
    if (!s) return "";
    return s.slice(0, 5); // HH:MM
  }

  function asDate(v) {
    return toDateOnly(v);
  }

  // ================== AUTO FIX FOR DATES ==================
  // Remove time-only junk
  if (!isResume) {
    if (isTimeOnly(tripStart)) tripStart = "";
    if (isTimeOnly(tripEnd)) tripEnd = "";
  }

  // ================== Fill header & base UI ==================
  if (headerUserName)  headerUserName.textContent  = userName;
  if (headerUserEmail) headerUserEmail.textContent = userEmail;

  if (ticketBookingCode) ticketBookingCode.textContent = bookingCode;
  if (ticketUserName)    ticketUserName.textContent    = userName;
  if (currencyLabel)     currencyLabel.textContent     = currency;
  if (ticketTotalAmount) ticketTotalAmount.textContent = formatMoney(totalAmount);
  if (amountTotalValue)  amountTotalValue.textContent  = formatMoney(totalAmount);

  // ================== traveller label ==================
  const travellerPieces = [];
  if (Number(travellersAdults) > 0) travellerPieces.push(`${travellersAdults} adult${travellersAdults === "1" ? "" : "s"}`);
  if (Number(travellersChildren) > 0) travellerPieces.push(`${travellersChildren} child`);
  if (Number(travellersInfants) > 0)  travellerPieces.push(`${travellersInfants} infant`);
  const travellersLabel = travellerPieces.join(" · ") || "1 adult";

  // ================== Booking type specific rendering (initial) ==================
  if (ticketMetaLine) {
    let typeLabel = "";
    if (bookingType === "flight") typeLabel = "Flight";
    else if (bookingType === "hotel") typeLabel = "Hotel stay";
    else if (bookingType === "package") typeLabel = "Travel package";
    ticketMetaLine.textContent = `${typeLabel} · ${travellersLabel}`;
  }

  // ================== Fill hidden meta (base) ==================
  if (metaForm) {
    metaForm.querySelector("#hfBookingType") && (metaForm.querySelector("#hfBookingType").value = bookingType);
    metaForm.querySelector("#hfUserId")      && (metaForm.querySelector("#hfUserId").value = userId);
    metaForm.querySelector("#hfUserName")    && (metaForm.querySelector("#hfUserName").value = userName);
    metaForm.querySelector("#hfUserEmail")   && (metaForm.querySelector("#hfUserEmail").value = userEmail);

    metaForm.querySelector("#hfAdults")      && (metaForm.querySelector("#hfAdults").value = travellersAdults);
    metaForm.querySelector("#hfChildren")    && (metaForm.querySelector("#hfChildren").value = travellersChildren);
    metaForm.querySelector("#hfInfants")     && (metaForm.querySelector("#hfInfants").value = travellersInfants);

    metaForm.querySelector("#hfAmountFlight")  && (metaForm.querySelector("#hfAmountFlight").value = amountFlight.toString());
    metaForm.querySelector("#hfAmountHotel")   && (metaForm.querySelector("#hfAmountHotel").value = amountHotel.toString());
    metaForm.querySelector("#hfAmountPackage") && (metaForm.querySelector("#hfAmountPackage").value = amountPackage.toString());
    metaForm.querySelector("#hfAmountTaxes")   && (metaForm.querySelector("#hfAmountTaxes").value = amountTaxes.toString());
    metaForm.querySelector("#hfDiscount")      && (metaForm.querySelector("#hfDiscount").value = discountAmount.toString());
    metaForm.querySelector("#hfCurrency")      && (metaForm.querySelector("#hfCurrency").value = currency);
    metaForm.querySelector("#hfTotalAmount")   && (metaForm.querySelector("#hfTotalAmount").value = totalAmount.toString());

    const hfBookingId = metaForm.querySelector("#hfBookingId");
    if (hfBookingId) hfBookingId.value = isResume ? String(resumeBooking.id) : "";

    const codeInput = metaForm.querySelector("#hfBookingCode");
    if (codeInput) codeInput.value = bookingCode;

    ensureHidden("booking_status", bookingStatus);
  }

  // ================== Hotel date picker logic ==================
  function showHotelPicker(show) {
    if (!hotelDatesForm) return;
    hotelDatesForm.hidden = !show;
  }

  function updateHotelEndFromStart() {
    if (!hotelStartInput || !hotelEndInput) return;

    const start = hotelStartInput.value;
    const nights = Number(stayNights || hotelNightsStr || 0);

    if (!start) {
      hotelEndInput.value = "";
      if (hotelDatesHint) hotelDatesHint.textContent = "";
      return;
    }

    const end = nights > 0 ? addDays(start, nights) : start;
    hotelEndInput.value = end;

    tripStart = start;
    tripEnd = end;

    if (hotelDatesHint) {
      hotelDatesHint.textContent = nights > 0
        ? `${nights} night(s) · Check-out is automatically calculated`
        : `Same-day stay`;
    }

    updateDatesUI();
    syncHiddenCore();
  }

  if (bookingType === "hotel" && !isResume) {
    showHotelPicker(true);

    if (hotelStartInput) {
      // start default
      const startDefault = toDateOnly(tripStart) || todayDate();
      hotelStartInput.value = startDefault;
      hotelStartInput.min = todayDate();

      // nights default
      const n = Number(hotelNightsStr || 1);
      stayNights = Number.isFinite(n) && n > 0 ? Math.round(n) : 1;

      hotelStartInput.addEventListener("change", updateHotelEndFromStart);
      updateHotelEndFromStart();
    }
  } else {
    showHotelPicker(false);
  }

  // ================== FIX dates for flight (non-resume) ==================
  if (!isResume && bookingType === "flight") {
    const startDateOnly = toDateOnly(tripStart) || toDateOnly(depDateParam);
    const endDateOnly   = toDateOnly(tripEnd)   || toDateOnly(retDateParam) || startDateOnly;

    if (startDateOnly) tripStart = combineDateTime(startDateOnly, departureTime);
    if (endDateOnly)   tripEnd   = combineDateTime(endDateOnly, returnTime || departureTime);
    if (!tripEnd && tripStart) tripEnd = tripStart;
  }

  // ================== PACKAGE: fill flight/hotel IDs + dates from APIs ==================
  async function hydratePackageFromApis() {
    if (bookingType !== "package" || isResume) return;

    // 1) Load package row (has hotel_id, flight_id, duration_days)
    const pkgRow = await loadPackageRowById(packageId);
    if (pkgRow) {
      // fill IDs if empty
      if (!flightId && pkgRow.flight_id != null) flightId = String(pkgRow.flight_id);
      if (!hotelId  && pkgRow.hotel_id  != null) hotelId  = String(pkgRow.hotel_id);

      // nights/days (if exists)
      const pkgDays = Number(pkgRow.duration_days || packageNightsStr || 0);
      if (Number.isFinite(pkgDays) && pkgDays > 0) stayNights = Math.round(pkgDays);
    }

    // 2) Load flight row (has departure_date/return_date + times + duration)
    const flRow = await loadFlightRowById(flightId);
    if (flRow) {
      const depDate = asDate(flRow.departure_date);
      const retDate = asDate(flRow.return_date) || depDate;

      // overwrite display info (optional)
      departureTime = normalizeTime(flRow.departure_time) || departureTime;
      arrivalTime   = normalizeTime(flRow.arrival_time)   || arrivalTime;
      returnTime    = normalizeTime(flRow.departure_time) || returnTime; // for return we only have departure_time in table

      airline = flRow.airline_name || airline;
      flightNumber = flRow.flight_number || flightNumber;

      // ✅ Set tripStart/tripEnd from flight dates + departure_time
      if (depDate) tripStart = combineDateTime(depDate, departureTime);
      if (retDate) tripEnd   = combineDateTime(retDate, departureTime);

      // ✅ duration_days from dates
      const dDays = diffDays(depDate, retDate);
      if (dDays > 0) stayNights = dDays;
      else if (!stayNights || stayNights <= 0) stayNights = 1;
    } else {
      // fallback if no flight row: at least guarantee date range
      const startDateOnly = toDateOnly(tripStart) || todayDate();
      const endDateOnly   = toDateOnly(tripEnd) || addDays(startDateOnly, Math.max(1, Number(stayNights || packageNightsStr || 1)));
      tripStart = combineDateTime(startDateOnly, departureTime);
      tripEnd   = combineDateTime(endDateOnly, departureTime);
      if (!stayNights || stayNights <= 0) stayNights = diffDays(startDateOnly, endDateOnly) || 1;
    }

    // update hidden after hydrate
    if (metaForm) {
      metaForm.querySelector("#hfFlightId")  && (metaForm.querySelector("#hfFlightId").value = flightId);
      metaForm.querySelector("#hfHotelId")   && (metaForm.querySelector("#hfHotelId").value  = hotelId);
      metaForm.querySelector("#hfPackageId") && (metaForm.querySelector("#hfPackageId").value= packageId);

      metaForm.querySelector("#hfTripStart") && (metaForm.querySelector("#hfTripStart").value = tripStart);
      metaForm.querySelector("#hfTripEnd")   && (metaForm.querySelector("#hfTripEnd").value   = tripEnd);
      metaForm.querySelector("#hfStayNights")&& (metaForm.querySelector("#hfStayNights").value= String(stayNights));
    }

    updateDatesUI();
  }

  // ================== Render ticket per type ==================
  function renderTicket() {
    if (bookingType === "flight") {
      if (ticketTypeBadge) ticketTypeBadge.textContent = "Flight ticket";
      if (ticketTitle) ticketTitle.textContent = (fromCity || toCity) ? `${fromCity || "Your city"} → ${toCity || "Destination"}` : "Flight booking";
      if (ticketSubtitle) ticketSubtitle.textContent = (airline && flightNumber) ? `${airline} · Flight ${flightNumber}` : "Review your flight details before paying.";

      if (ticketRouteMain) {
        if (fromCity || toCity || fromAirport || toAirport) {
          const fromLabel = fromAirport ? `${fromCity || "Origin"} (${fromAirport})` : (fromCity || "Origin");
          const toLabel   = toAirport   ? `${toCity || "Destination"} (${toAirport})` : (toCity || "Destination");
          ticketRouteMain.textContent = `${fromLabel} → ${toLabel}`;
        } else ticketRouteMain.textContent = "Flight route";
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
    }

    if (bookingType === "hotel") {
      if (ticketTypeBadge) ticketTypeBadge.textContent = "Hotel booking";
      if (ticketTitle) ticketTitle.textContent = hotelName || "Your hotel";
      if (ticketSubtitle) ticketSubtitle.textContent = (hotelCity || hotelLocation) ? `${hotelCity || hotelLocation} · ${roomType || "Room"}` : "Review your stay details.";
      if (ticketRouteMain) ticketRouteMain.textContent = hotelCity || hotelLocation || "Destination";

      if (ticketExtraRow) {
        ticketExtraRow.innerHTML = `
          <div class="ticket-extra-col">
            <div class="ticket-extra-label">Nights</div>
            <div class="ticket-extra-value">${String(stayNights || hotelNightsStr || "-")}</div>
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
    }

    if (bookingType === "package") {
      if (ticketTypeBadge) ticketTypeBadge.textContent = "Package booking";
      if (ticketTitle) ticketTitle.textContent = packageTitle || "Travel package";
      if (ticketSubtitle) ticketSubtitle.textContent = (packageCity || packageCombo) ? `${packageCity || ""}${packageCombo ? " · " + packageCombo : ""}` : "Review your package details.";
      if (ticketRouteMain) ticketRouteMain.textContent = packageCity || "Destination";

      if (ticketExtraRow) {
        ticketExtraRow.innerHTML = `
          <div class="ticket-extra-col">
            <div class="ticket-extra-label">Days</div>
            <div class="ticket-extra-value">${String(stayNights || packageNightsStr || "-")}</div>
          </div>
          <div class="ticket-extra-col">
            <div class="ticket-extra-label">Flight</div>
            <div class="ticket-extra-value">${airline ? airline : "Included"} ${flightNumber ? "· " + flightNumber : ""}</div>
          </div>
          <div class="ticket-extra-col">
            <div class="ticket-extra-label">Hotel</div>
            <div class="ticket-extra-value">${hotelId ? ("Hotel ID #" + hotelId) : "Included"}</div>
          </div>
        `;
      }
    }
  }

  // ================== Notes + initial UI ==================
  if (ticketNotes) {
    ticketNotes.textContent =
      "Please review trip dates, travellers and total amount carefully. After payment, changes may require contacting Travelo support.";
  }

  // ================== Amounts ==================
  if (rowAmountFlight)  rowAmountFlight.style.display  = amountFlight  > 0 ? "flex" : "none";
  if (rowAmountHotel)   rowAmountHotel.style.display   = amountHotel   > 0 ? "flex" : "none";
  if (rowAmountPackage) rowAmountPackage.style.display = amountPackage > 0 ? "flex" : "none";
  if (rowDiscount)      rowDiscount.style.display      = discountAmount> 0 ? "flex" : "none";

  if (amountFlightValue)   amountFlightValue.textContent   = formatMoney(amountFlight);
  if (amountHotelValue)    amountHotelValue.textContent    = formatMoney(amountHotel);
  if (amountPackageValue)  amountPackageValue.textContent  = formatMoney(amountPackage);
  if (amountTaxesValue)    amountTaxesValue.textContent    = formatMoney(amountTaxes);
  if (amountDiscountValue) {
    amountDiscountValue.textContent =
      "-" + formatMoney(discountAmount).replace(currency === "USD" ? "$" : currency + " ", "");
  }

  if (summarySubtotal) summarySubtotal.textContent = formatMoney(subtotal);
  if (summaryTaxes)    summaryTaxes.textContent    = formatMoney(amountTaxes);

  if (summaryDiscount) {
    summaryDiscount.textContent =
      "-" + formatMoney(discountAmount).replace(currency === "USD" ? "$" : currency + " ", "");
  }
  if (summaryDiscountRow) summaryDiscountRow.style.display = discountAmount > 0 ? "flex" : "none";

  if (summaryTotal) summaryTotal.textContent = formatMoney(totalAmount);
  if (btnPayAmountLabel) btnPayAmountLabel.textContent = `· ${formatMoney(totalAmount)}`;

  if (summaryTripLine) {
    if (bookingType === "flight") {
      summaryTripLine.textContent = (fromCity || toCity)
        ? `${fromCity || "Origin"} → ${toCity || "Destination"} · ${travellersLabel}`
        : `Flight · ${travellersLabel}`;
    } else if (bookingType === "hotel") {
      summaryTripLine.textContent = `${hotelName || "Hotel"} · ${hotelCity || hotelLocation || ""} · ${travellersLabel}`;
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
    if (bookingType === "flight") doneTripLine.textContent = (fromCity || toCity) ? `${fromCity || "Origin"} → ${toCity || "Destination"}` : "Flight";
    else if (bookingType === "hotel") doneTripLine.textContent = hotelName || hotelCity || hotelLocation || "Hotel";
    else doneTripLine.textContent = packageTitle || packageCity || "Package";
  }

  // ================== Render initial dates + ticket ==================
  updateDatesUI();
  renderTicket();

  // ================== Hydrate package then re-render ==================
  (async () => {
    try {
      if (bookingType === "package" && !isResume) {
        // for package, hide hotel picker
        if (hotelDatesForm) hotelDatesForm.hidden = true;

        await hydratePackageFromApis();
        // re-render after API fills flight/hotel/dates
        stayNights = computeStayNights();
        syncHiddenCore();
        updateDatesUI();
        renderTicket();
      } else {
        // for flight/hotel normal
        stayNights = computeStayNights();
        syncHiddenCore();
        updateDatesUI();
        renderTicket();
      }
    } catch (e) {
      console.error("Package hydration failed:", e);
      // still render whatever we have
      stayNights = computeStayNights();
      syncHiddenCore();
      updateDatesUI();
      renderTicket();
    }
  })();

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

  if (btnToPayment) btnToPayment.addEventListener("click", () => goToStep(2));
  if (btnStep1BackHome) btnStep1BackHome.addEventListener("click", () => { window.location.href = "index.php"; });
  if (btnBackToStep1) btnBackToStep1.addEventListener("click", () => goToStep(1));
  if (btnBackHome) btnBackHome.addEventListener("click", () => { window.location.href = "index.php"; });
  if (btnPrintTicket) btnPrintTicket.addEventListener("click", () => window.print());

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
    for (const r of radios) if (r.checked) return r.value;
    return "visa";
  }

  const cardFieldsWrap = document.getElementById("cardFields");
  const offlineWrap    = document.getElementById("offlineFields");
  const saveCardRow    = document.getElementById("saveCardRow");
  const payBtnText     = document.getElementById("payBtnText");

  const radios = Array.from(document.querySelectorAll('input[name="payment_method"]'));
  const cardInputs = [cardHolderInput, cardNumberInput, expMonthInput, expYearInput, cvvInput].filter(Boolean);

  function setCardRequired(isReq) {
    cardInputs.forEach((el) => {
      el.required = isReq;
      el.disabled = !isReq;
      if (!isReq) el.value = "";
    });

    if (saveCardInput) {
      saveCardInput.checked = false;
      saveCardInput.disabled = !isReq;
    }

    if (saveCardRow) saveCardRow.hidden = !isReq;
  }

  function applyPaymentUI() {
    const method = document.querySelector('input[name="payment_method"]:checked')?.value || "visa";
    const isOffline = method === "cashcard";

    if (cardFieldsWrap) cardFieldsWrap.hidden = isOffline;
    if (offlineWrap)    offlineWrap.hidden    = !isOffline;

    setCardRequired(!isOffline);

    if (payBtnText) payBtnText.textContent = isOffline ? "Reserve now" : "Pay now";
    if (btnPayAmountLabel) btnPayAmountLabel.hidden = isOffline;

    if (paymentError) paymentError.style.display = "none";
  }

  radios.forEach(r => r.addEventListener("change", applyPaymentUI));
  applyPaymentUI();

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

      // ✅ لازم يكون في تاريخ للبكج/فلايت
      if ((bookingType === "flight" || bookingType === "package") && (!toDateOnly(tripStart) || !toDateOnly(tripEnd))) {
        return showPaymentError("Trip dates are missing. Please try again from search.");
      }

      // ---- Basic card validation ----
      if (method !== "cashcard") {
        if (!cardHolderInput.value.trim()) return showPaymentError("Please enter the card holder name.");
        if (!/^\d{12,19}$/.test(cardNumberInput.value.replace(/\s+/g, ""))) return showPaymentError("Please enter a valid card number.");
        if (!/^\d{2}$/.test(expMonthInput.value.trim())) return showPaymentError("Please enter a valid expiry month (MM).");
        if (!/^\d{2,4}$/.test(expYearInput.value.trim())) return showPaymentError("Please enter a valid expiry year.");
        if (!/^\d{3,4}$/.test(cvvInput.value.trim())) return showPaymentError("Please enter a valid CVV code.");
      }

      const formData = new FormData();

      if (metaForm) {
        const metaFd = new FormData(metaForm);
        for (const [key, value] of metaFd.entries()) formData.append(key, value);
      }

      if (isResume) formData.set("booking_id", String(resumeBooking.id));

      formData.set("booking_status", method === "cashcard" ? "pending" : "confirmed");
      formData.set("payment_method", method);

      // ✅ force updated dates to server
      formData.set("trip_start_date", tripStart || "");
      formData.set("trip_end_date", tripEnd || tripStart || "");
      formData.set("stay_nights", String(stayNights || 0));

      // ✅ ensure IDs are synced (important for package hydration)
      formData.set("flight_id", flightId || "");
      formData.set("hotel_id", hotelId || "");
      formData.set("package_id", packageId || "");

      if (method !== "cashcard") {
        const rawNumber = cardNumberInput.value.replace(/\s+/g, "");
        formData.set("card_holder_name", cardHolderInput.value.trim());
        formData.set("card_number", rawNumber);
        formData.set("exp_month", expMonthInput.value.trim());
        formData.set("exp_year", expYearInput.value.trim());
        formData.set("cvv", cvvInput.value.trim());
      }

      const promo = promoCodeInput.value.trim();
      if (promo) formData.set("promo_code", promo);

      formData.set("card_saved", saveCardInput?.checked ? "1" : "0");

      try {
        const response = await fetch("./booking.php", { method: "POST", body: formData });

        let data = null;
        try { data = await response.json(); } catch (err) {}

        if (!response.ok || !data) return showPaymentError(`Server error (${response.status}). Please try again.`);
        if (!data.success) return showPaymentError(data.message || "Payment failed. Please try again.");

        const bStatus = (data.booking_status || "").toLowerCase();
        if (doneBookingCode && data.booking_code) doneBookingCode.textContent = data.booking_code;
        if (doneTotalPaid && data.amount_total != null) doneTotalPaid.textContent = formatMoney(data.amount_total);

        if (doneTitleEl) doneTitleEl.textContent = bStatus === "confirmed" ? "Booking confirmed" : "Reservation created";

        if (doneDescEl) {
          doneDescEl.innerHTML =
            bStatus === "confirmed"
              ? `Thank you, <span id="doneUserName">${userName}</span>. Your booking is now <strong>confirmed</strong>.`
              : `Thank you, <span id="doneUserName">${userName}</span>. Your booking is now <strong>pending</strong>.`;
        }

        goToStep(3);
      } catch (err) {
        showPaymentError("Network error. Please try again.");
      }
    });
  }

  // ================== Promo button ==================
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
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnPrintTicket');
  if (!btn) return;

  const esc = (s) => String(s ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  function pickText(id, fallback = '—') {
    const el = document.getElementById(id);
    const t = el ? el.textContent.trim() : '';
    return t || fallback;
  }

  function formatNow() {
    const d = new Date();
    return d.toLocaleString(undefined, {
      year: 'numeric', month: 'short', day: '2-digit',
      hour: '2-digit', minute: '2-digit'
    });
  }

  function detectType(typeBadgeText) {
    const t = (typeBadgeText || '').toLowerCase();
    if (t.includes('hotel')) return { key: 'hotel', icon: '🏨', label: 'Hotel booking' };
    if (t.includes('package')) return { key: 'package', icon: '🎁', label: 'Package booking' };
    return { key: 'flight', icon: '✈', label: 'Flight ticket' };
  }

  btn.addEventListener('click', (e) => {
    e.preventDefault();

    // ✅ اجمع بيانات التذكرة من الصفحة
    const typeBadgeRaw = pickText('ticketTypeBadge', 'Ticket');
    const typeMeta     = detectType(typeBadgeRaw);

    const title       = pickText('ticketTitle', 'Your trip');
    const subtitle    = pickText('ticketSubtitle', '');
    const bookingCode = pickText('ticketBookingCode', 'TRV-XXXX');

    const routeMain   = pickText('ticketRouteMain', '');
    const metaLine    = pickText('ticketMetaLine', '');

    const startView   = pickText('tripStartView', pickText('ticketDates', '—'));
    const endView     = pickText('tripEndView', '—');

    const traveler    = pickText('ticketUserName', (window.TRAVELO?.userName || 'Traveler'));
    const email       = (window.TRAVELO?.userEmail || '').trim();

    const totalAmount = pickText('ticketTotalAmount', pickText('amountTotalValue', '$0.00'));
    const currency    = pickText('currencyLabel', 'USD');

    const extraRaw = (document.getElementById('ticketExtraRow')?.innerText || '').trim();
    const extraLines = extraRaw
      .split('\n')
      .map(x => x.trim())
      .filter(Boolean)
      .slice(0, 8);

    const issuedAt = formatNow();

    // ✅ افتح نافذة طباعة
    const w = window.open('', 'TRAVELO_PRINT', 'width=980,height=720');
    if (!w) {
      alert('Popup blocked. Allow popups for this site then try again.');
      return;
    }

    const html = `<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Travelo Ticket</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">

  <style>
    /* ✅ أهم تعديل: no min-height + margin مضبوط */
    @page { size: A4; margin: 12mm; }

    :root{
      --ink:#0f172a;
      --muted:#6b7280;
      --border:#e5e7eb;
      --bg:#ffffff;
      --accent:#872bff;
      --accent2:#6c63ff;
    }

    *{ box-sizing:border-box; }
    html,body{
      margin:0;
      background: var(--bg);
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
      font-family:"Plus Jakarta Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color: var(--ink);
    }

    /* صفحة واحدة */
    .page{
      width: 100%;
      margin: 0;
    }

    /* التذكرة */
    .ticket{
      width: 100%;
      border: 1px solid var(--border);
      border-radius: 22px;
      overflow: hidden;
      position: relative;
      background:#fff;
      page-break-inside: avoid;
      break-inside: avoid;
    }

    /* Gradient header */
    .topbar{
      height: 12mm;
      background: linear-gradient(90deg, var(--accent), var(--accent2));
    }

    /* ثقوب داخلية (بدون ما تسبب أي overflow غريب) */
    .ticket:before, .ticket:after{
      content:"";
      position:absolute;
      top: 54%;
      width: 13mm;
      height: 13mm;
      border-radius: 999px;
      background: #fff;
      border: 1px solid var(--border);
      transform: translateY(-50%);
      z-index: 3;
      opacity: .95;
    }
    .ticket:before{ left: -6.5mm; }
    .ticket:after{ right: -6.5mm; }

    /* Header */
    .head{
      padding: 9mm 10mm 6mm;
      display:flex;
      justify-content: space-between;
      gap: 8mm;
      align-items: flex-start;
    }

    .brand{
      display:flex;
      align-items:flex-start;
      gap: 10px;
    }
    .logo{
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      display:grid;
      place-items:center;
      color:#fff;
      font-weight:900;
      letter-spacing:.5px;
      flex: 0 0 auto;
    }
    .brand h1{
      font-size: 16.5pt;
      margin:0;
      line-height:1.05;
    }
    .brand p{
      margin:4px 0 0;
      color: var(--muted);
      font-size: 10pt;
      max-width: 105mm;
      line-height: 1.35;
    }

    .badge{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 7px 11px;
      border-radius: 999px;
      font-weight: 800;
      font-size: 10pt;
      background: rgba(135,43,255,.10);
      color: var(--accent);
      border: 1px solid rgba(135,43,255,.18);
      white-space: nowrap;
      margin-top: 8px;
    }

    .codeBox{
      text-align:right;
      min-width: 60mm;
    }
    .codeLabel{
      color: var(--muted);
      font-size: 9.5pt;
      margin-bottom: 5px;
    }
    .code{
      font-size: 13pt;
      font-weight: 900;
      letter-spacing: 1.2px;
    }
    .metaTiny{
      margin-top: 6px;
      color: var(--muted);
      font-size: 9pt;
    }

    .cut{
      border-top: 2px dashed #e5e7eb;
      margin: 0 10mm;
    }

    /* Body */
    .body{
      padding: 7mm 10mm 9mm;
      display:grid;
      grid-template-columns: 1.25fr .75fr;
      gap: 8mm;
      align-items:start;
    }

    .block{
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 8mm;
      background: #fff;
    }

    .block h2{
      margin:0 0 8px;
      font-size: 12pt;
      letter-spacing:.2px;
    }

    .route{
      font-size: 13.5pt;
      font-weight: 900;
      margin: 0 0 6px;
    }

    .meta{
      color: var(--muted);
      font-size: 10pt;
      margin: 0 0 10px;
      line-height: 1.35;
    }

    .grid{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px 14px;
      margin-top: 8px;
    }
    .kv .k{
      font-size: 9pt;
      color: var(--muted);
      margin-bottom: 3px;
    }
    .kv .v{
      font-size: 11pt;
      font-weight: 800;
      line-height: 1.25;
      word-break: break-word;
    }

    .list{
      margin: 10px 0 0;
      padding: 0;
      list-style: none;
    }
    .list li{
      padding: 7px 0;
      border-bottom: 1px solid #f1f5f9;
      font-size: 10pt;
      color: #111827;
      line-height: 1.35;
    }
    .list li:last-child{ border-bottom:none; }

    .total{
      display:flex;
      justify-content: space-between;
      align-items:flex-end;
      gap: 12px;
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px solid #eef2f7;
    }
    .total .tlabel{
      color: var(--muted);
      font-size: 9.5pt;
    }
    .total .tval{
      font-size: 20pt;
      font-weight: 900;
      letter-spacing: .2px;
      white-space: nowrap;
    }

    .note{
      margin-top: 7mm;
      color: var(--muted);
      font-size: 9pt;
      line-height: 1.45;
    }

    /* Footer داخل التذكرة (حتى ما يزيد الطول ويعمل صفحة ثانية) */
    .ticketFoot{
      display:flex;
      justify-content: space-between;
      padding: 6mm 10mm;
      border-top: 1px solid #eef2f7;
      color: var(--muted);
      font-size: 9pt;
    }

    /* تأكيد عدم الانقسام */
    .head, .body, .block { break-inside: avoid; page-break-inside: avoid; }
  </style>
</head>
<body>
  <div class="page">
    <div class="ticket">
      <div class="topbar"></div>

      <div class="head">
        <div>
          <div class="brand">
            <div class="logo">T</div>
            <div>
              <h1>Travelo Ticket</h1>
              <p>${esc(title)}${subtitle ? ` — ${esc(subtitle)}` : ''}</p>
            </div>
          </div>

          <div class="badge">${esc(typeMeta.icon)} ${esc(typeMeta.label)}</div>
        </div>

        <div class="codeBox">
          <div class="codeLabel">Booking reference</div>
          <div class="code">${esc(bookingCode)}</div>
          <div class="metaTiny">Issued: ${esc(issuedAt)}</div>
        </div>
      </div>

      <div class="cut"></div>

      <div class="body">
        <div class="block">
          <h2>Trip details</h2>

          ${routeMain ? `<div class="route">${esc(routeMain)}</div>` : ''}
          ${metaLine ? `<div class="meta">${esc(metaLine)}</div>` : ''}

          <div class="grid">
            <div class="kv">
              <div class="k">Start</div>
              <div class="v">${esc(startView)}</div>
            </div>
            <div class="kv">
              <div class="k">End</div>
              <div class="v">${esc(endView)}</div>
            </div>

            <div class="kv">
              <div class="k">Traveler</div>
              <div class="v">${esc(traveler)}</div>
            </div>
            <div class="kv">
              <div class="k">Email</div>
              <div class="v">${esc(email || '—')}</div>
            </div>
          </div>

          ${extraLines.length ? `
            <ul class="list">
              ${extraLines.map(x => `<li>${esc(x)}</li>`).join('')}
            </ul>
          ` : ''}

          <div class="note">
            Please review trip dates, travellers and total amount carefully.
            After payment, changes may require contacting Travelo support.
          </div>
        </div>

        <div class="block">
          <h2>Payment</h2>

          <div class="kv" style="margin-top:8px;">
            <div class="k">Currency</div>
            <div class="v">${esc(currency)}</div>
          </div>

          <div class="total">
            <div>
              <div class="tlabel">Total amount</div>
              <div class="metaTiny">Paid/Reserved based on method</div>
            </div>
            <div class="tval">${esc(totalAmount)}</div>
          </div>

          <div class="note">
            Tip: enable “Background graphics” in print settings
            to keep the Travelo gradient header.
          </div>
        </div>
      </div>

      <div class="ticketFoot">
        <div>Travelo • e-Ticket</div>
        <div>${esc(bookingCode)}</div>
      </div>
    </div>
  </div>

  <script>
    (async function(){
      try{
        if (document.fonts && document.fonts.ready) {
          await document.fonts.ready;
        }
      }catch(e){}
      setTimeout(function(){
        window.print();
        setTimeout(() => window.close(), 250);
      }, 220);
    })();
  <\/script>
</body>
</html>`;

    w.document.open();
    w.document.write(html);
    w.document.close();
    w.focus();
  });
});