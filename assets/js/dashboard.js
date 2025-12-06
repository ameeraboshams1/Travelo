
(function initTheme() {
  const saved = localStorage.getItem('travelo-theme');
  if (saved === 'dark') document.documentElement.classList.add('dark');
  updateThemeIcon();
})();
function toggleTheme() {
  document.documentElement.classList.toggle('dark');
  localStorage.setItem('travelo-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
  applyDtDarkSkin();           
  updateThemeIcon();
  refreshMapTiles();
}
function updateThemeIcon() {
  const icon = document.getElementById('themeIcon');
  if (!icon) return;
  if (document.documentElement.classList.contains('dark')) {
    icon.classList.remove('bi-moon-stars');
    icon.classList.add('bi-sun');
  } else {
    icon.classList.add('bi-moon-stars');
    icon.classList.remove('bi-sun');
  }
}
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);
});

function toggleSidebar(e) { e && e.preventDefault(); document.getElementById('sidebar').classList.toggle('show'); }

const titleMap = {
  dashboard: 'Dashboard', users: 'Users', admins: 'Admins', destinations: 'Destinations',
  flights: 'Flights', hotels: 'Hotels', rooms: 'Rooms', packages: 'Packages',
  bookings: 'Bookings', payments: 'Payments'
};
function showSection(page) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  (document.getElementById(page) || document.getElementById('dashboard')).classList.add('active');
  const pt = document.getElementById('pageTitle'); if (pt) pt.textContent = titleMap[page] || 'Dashboard';
  document.querySelectorAll('#mainNav .nav-link').forEach(a => a.classList.toggle('active', a.dataset.page === page));
  initers[page] && initers[page]();
}
window.addEventListener('hashchange', () => showSection(location.hash.replace('#', '') || 'dashboard'));
document.addEventListener('DOMContentLoaded', () => showSection(location.hash.replace('#', '') || 'dashboard'));

const gSearch = document.getElementById('globalSearch');
document.addEventListener('keydown', (e) => { if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); gSearch?.focus(); } });
function applySearch(val) {
  const visibleTable = Array.from(document.querySelectorAll('.section.active table.datatable')).shift();
  if (visibleTable && $(visibleTable).DataTable) { $(visibleTable).DataTable().search(val).draw(); }
}
gSearch?.addEventListener('input', e => applySearch(e.target.value));
gSearch?.addEventListener('keydown', e => { if (e.key === 'Enter') applySearch(e.target.value); });

const USE_API = false;
function endpoint(entity, action, id) {
  const base = `api/${entity}.php`;
  const qs = new URLSearchParams({ action: action || 'list' });
  if (id) qs.set('id', id);
  return `${base}?${qs.toString()}`;
}
const API = {
  async list(entity) { if (!USE_API) return mock[entity]; const r = await fetch(endpoint(entity, 'list')); return r.json(); },
  async create(entity, payload) { if (!USE_API) { payload.id ||= entity[0] + Math.random().toString(36).slice(2, 7); mock[entity].push(payload); return payload; } const r = await fetch(endpoint(entity, 'create'), { method: 'POST', body: toFormData(payload) }); return r.json(); },
  async update(entity, id, payload) { if (!USE_API) { const i = mock[entity].findIndex(x => x.id == id); if (i > -1) mock[entity][i] = { ...mock[entity][i], ...payload }; return mock[entity][i]; } const r = await fetch(endpoint(entity, 'update', id), { method: 'POST', body: toFormData(payload) }); return r.json(); },
  async remove(entity, id) { if (!USE_API) { const i = mock[entity].findIndex(x => x.id == id); if (i > -1) mock[entity].splice(i, 1); return { ok: true }; } const r = await fetch(endpoint(entity, 'delete', id), { method: 'POST' }); return r.json(); }
};
function toFormData(obj) { const fd = new FormData(); Object.entries(obj || {}).forEach(([k, v]) => fd.append(k, v)); return fd; }

const mock = {
  users: [
    { id: 1, full_name: 'Zeina Anabtawi', email: 'zeina@example.com', phone: '+970...', created_at: '2025-09-20' },
    { id: 2, full_name: 'Ameer AboShams', email: 'ameer@example.com', phone: '+962...', created_at: '2025-09-22' }
  ],
  admins: [
    { id: 1, full_name: 'Jana D.', email: 'jana@travelo.io', is_super: 1, created_at: '2025-09-01', img: 'https://i.pravatar.cc/120?img=3', r: 'Super Admin', s: 'Active', perms: ['Users', 'Flights', 'Hotels', 'Bookings', 'Payments'], last: 'Today 14:22' },
    { id: 2, full_name: 'Omar T.', email: 'omar@travelo.io', is_super: 0, created_at: '2025-09-05', img: 'https://i.pravatar.cc/120?img=5', r: 'Content Manager', s: 'Active', perms: ['Destinations', 'Hotels', 'Packages'], last: 'Today 10:11' }
  ],
  destinations: [
    { id: 10, slug: 'istanbul', name: 'Istanbul, Turkey', kind: 'city', country_code: 'TR', city: 'Istanbul', is_published: 1, sort_order: 100, airport_codes: '["IST","SAW"]', city_aliases: '["Istanbul","İstanbul"]' },
    { id: 11, slug: 'amman', name: 'Amman, Jordan', kind: 'city', country_code: 'JO', city: 'Amman', is_published: 1, sort_order: 120, airport_codes: '["AMM"]', city_aliases: '["Amman"]' }
  ],
  flights: [
    { id: 101, image_url: 'https://picsum.photos/seed/flight101/80/80', airline: 'Royal Jordanian', from_code: 'AMM', to_code: 'IST', depart_at: '2025-10-08 07:35', arrive_at: '2025-10-08 10:30', cabin_class: 'economy', price: 149.00, seats_left: 12 },
    { id: 102, image_url: 'https://picsum.photos/seed/flight102/80/80', airline: 'Emirates', from_code: 'AMM', to_code: 'DXB', depart_at: '2025-10-08 11:20', arrive_at: '2025-10-08 14:45', cabin_class: 'economy', price: 199.00, seats_left: 22 }
  ],
  hotels: [
    { id: 201, destination_id: 10, cover_image_url: 'https://picsum.photos/seed/hotel201/80/80', name: 'Istanbul View', city: 'Istanbul', country: 'Turkey', stars: 5, is_published: 1 },
    { id: 202, destination_id: 11, cover_image_url: 'https://picsum.photos/seed/hotel202/80/80', name: 'Amman Palace', city: 'Amman', country: 'Jordan', stars: 4, is_published: 1 }
  ],
  rooms: [
    { id: 301, hotel_id: 201, name: 'Deluxe King', capacity_adults: 2, capacity_children: 1, price_per_night: 140.00, refundable: 1, is_active: 1 },
    { id: 302, hotel_id: 202, name: 'Standard Twin', capacity_adults: 2, capacity_children: 0, price_per_night: 95.00, refundable: 1, is_active: 1 }
  ],
  packages: [
    { id: 401, image_url: 'https://picsum.photos/seed/pkg401/80/80', title: 'Istanbul Getaway', destination_id: 10, nights: 4, base_price: 499.00, currency: 'USD', is_active: 1, flight_id: 101, hotel_id: 201 }
  ],
  bookings: [
    { id: 5001, user_id: 1, ref_type: 'flight', ref_id: 101, status: 'paid', total_price: 149.00, currency: 'USD', created_at: '2025-09-28', checkin: null, checkout: null },
    { id: 5002, user_id: 2, ref_type: 'hotel', ref_id: 301, status: 'pending', total_price: 220.00, currency: 'USD', created_at: '2025-09-28', checkin: '2025-10-12', checkout: '2025-10-15' }
  ],
  payments: [
    { id: 6001, booking_id: 5001, provider: 'Visa', status: 'captured', amount: 149.00, currency: 'USD', txn_id: 'TX-22001', created_at: '2025-09-26' },
    { id: 6002, booking_id: 5002, provider: 'Mastercard', status: 'pending', amount: 220.00, currency: 'USD', txn_id: 'TX-22002', created_at: '2025-09-26' }
  ]
};

const badge = (s) => {
  const norm = String(s).toLowerCase();
  if (['active', 'on-time', 'paid', 'captured', 'confirmed', 'yes'].includes(norm)) return `<span class="status success">${s}</span>`;
  if (['pending', 'delayed'].includes(norm)) return `<span class="status pending">${s}</span>`;
  return `<span class="status canceled">${s}</span>`;
};
const actions = (opts, id, entity) => {
  const b = (icon, title, action, cls = 'btn-outline-secondary') => `<button class="btn ${cls} btn-action" data-id="${id}" data-entity="${entity}" data-action="${action}" title="${title}"><i class="bi ${icon}"></i></button>`;
  return `<div class="btn-group">
    ${opts.edit ? b('bi-pencil', 'Edit', 'edit') : ''}
    ${opts.del ? b('bi-trash', 'Delete', 'delete', 'btn-outline-danger') : ''}
    ${opts.cancel ? b('bi-x-circle', 'Cancel', 'cancel', 'btn-outline-warning') : ''}
    ${opts.refund ? b('bi-cash-coin', 'Refund', 'refund', 'btn-outline-primary') : ''}
  </div>`;
};
function getGridColor() { return document.documentElement.classList.contains('dark') ? '#232742' : '#eef0f4'; }
function resolveAlpha(hex, alpha) {
  if (/^#([A-Fa-f0-9]{6})$/.test(hex)) {
    const r = parseInt(hex.slice(1, 3), 16), g = parseInt(hex.slice(3, 5), 16), b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r},${g},${b},${alpha})`;
  }
  return hex;
}

function applyDtDarkSkin() {
  
  document.querySelectorAll('.dataTables_wrapper').forEach(w => {
    if (document.documentElement.classList.contains('dark')) w.classList.add('dt-dark');
    else w.classList.remove('dt-dark');
  });
  
  if (typeof $ !== 'undefined' && $.fn && $.fn.DataTable) {
    $('.datatable').each(function () {
      if ($.fn.DataTable.isDataTable(this)) {
        $(this).DataTable().columns.adjust().draw(false);
      }
    });
  }
}

const kpi = {
  users: mock.users.length + 320,
  bookings: mock.bookings.length + 420,
  revenue: mock.payments.reduce((s, p) => s + (p.amount || 0), 0) + 54000,
  otp: 92
};
document.addEventListener('DOMContentLoaded', () => {
  const el = (id) => document.getElementById(id);
  el('kpiUsers') && (el('kpiUsers').textContent = kpi.users.toLocaleString());
  el('kpiBookings') && (el('kpiBookings').textContent = kpi.bookings.toLocaleString());
  el('kpiRevenue') && (el('kpiRevenue').textContent = '$' + kpi.revenue.toLocaleString());
  el('kpiOTP') && (el('kpiOTP').textContent = kpi.otp + '%');
});

let MAP, BASE_LIGHT, BASE_DARK, PLANE_INT;
function buildFlightsMap() {
  const coords = {
    AMM: [31.722, 35.993], IST: [41.275, 28.751], DXB: [25.253, 55.365],
    KUL: [2.745, 101.71], CAI: [30.121, 31.405], JED: [21.679, 39.156],
    DOH: [25.273, 51.608], RUH: [24.957, 46.698]
  };
  if (MAP) { MAP.remove(); MAP = null; }
  MAP = L.map('flightsMap', { scrollWheelZoom: false, worldCopyJump: true }).setView(coords.AMM, 5);

  BASE_LIGHT = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 20, attribution: '&copy; OpenStreetMap & Carto' });
  BASE_DARK = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 20, attribution: '&copy; OpenStreetMap & Carto' });
  (document.documentElement.classList.contains('dark') ? BASE_DARK : BASE_LIGHT).addTo(MAP);

  const airportIcon = L.divIcon({ className: '', html: '<i class="bi bi-geo-alt-fill" style="font-size:18px;color:var(--accent)"></i>', iconSize: [18, 18], iconAnchor: [9, 18] });
  const markers = {};
  Object.entries(coords).forEach(([k, latlng]) => { markers[k] = L.marker(latlng, { icon: airportIcon }).addTo(MAP).bindPopup(`<b>${k}</b>`); });

  const css = getComputedStyle(document.documentElement);
  const color = (css.getPropertyValue('--route') || css.getPropertyValue('--p2') || '#8b5cf6').trim();
  const routes = [['AMM', 'IST'], ['JED', 'AMM'], ['AMM', 'DXB'], ['DXB', 'KUL'], ['AMM', 'CAI'], ['AMM', 'DOH'], ['AMM', 'RUH']];
  const arcs = [];
  routes.forEach(([a, b]) => {
    const ant = L.polyline.antPath([coords[a], coords[b]], { paused: false, reverse: false, delay: 600, dashArray: [10, 20], weight: 3, opacity: 0.9, color }).addTo(MAP).bindTooltip(`${a} → ${b}`);
    arcs.push(ant);
  });
  const group = L.featureGroup(arcs);
  MAP.fitBounds(group.getBounds().pad(0.2));

  const planeIcon = L.divIcon({ className: 'leaflet-plane', html: '✈️' });
  const plane = L.marker(coords.AMM, { icon: planeIcon, zIndexOffset: 1000 }).addTo(MAP);
  const start = L.latLng(coords.AMM), end = L.latLng(coords.IST);
  function bearing(a, b) {
    const toRad = d => d * Math.PI / 180, toDeg = r => r * 180 / Math.PI;
    const lat1 = toRad(a.lat), lat2 = toRad(b.lat), dLon = toRad(b.lng - a.lng);
    const y = Math.sin(dLon) * Math.cos(lat2);
    const x = Math.cos(lat1) * Math.cos(lat2) * Math.cos(dLon) - Math.sin(lat1) * Math.sin(lat2);
    return (toDeg(Math.atan2(y, x)) + 360) % 360;
  }
  function lerp(a, b, t) { return L.latLng(a.lat + (b.lat - a.lat) * t, a.lng + (b.lng - a.lng) * t); }
  let t = 0, dir = 1;
  const angle = bearing(start, end);
  if (PLANE_INT) clearInterval(PLANE_INT);
  PLANE_INT = setInterval(() => {
    t += dir * 0.01;
    if (t >= 1) { t = 1; dir = -1; }
    if (t <= 0) { t = 0; dir = 1; }
    const pos = lerp(start, end, t);
    plane.setLatLng(pos);
    const el = plane.getElement();
    if (el) el.style.transform = `translate(-12px,-12px) rotate(${angle}deg)`;
  }, 80);
}
function refreshMapTiles() {
  if (!MAP) return;
  if (document.documentElement.classList.contains('dark')) {
    if (MAP.hasLayer(BASE_LIGHT)) MAP.removeLayer(BASE_LIGHT);
    if (!MAP.hasLayer(BASE_DARK)) BASE_DARK.addTo(MAP);
  } else {
    if (MAP.hasLayer(BASE_DARK)) MAP.removeLayer(BASE_DARK);
    if (!MAP.hasLayer(BASE_LIGHT)) BASE_LIGHT.addTo(MAP);
  }
}

const initers = {
  dashboard: (function () {
    let done = false; return async function () {
      if (done) return; done = true;
      const css = getComputedStyle(document.documentElement);
      const p1 = css.getPropertyValue('--p1').trim();
      const p2 = css.getPropertyValue('--p2').trim();
      const p3 = css.getPropertyValue('--p3').trim();
      const p4 = css.getPropertyValue('--p4').trim();
      const pink1 = css.getPropertyValue('--pink1')?.trim() || p3;
      const pink2 = css.getPropertyValue('--pink2')?.trim() || p4;

      new Chart(document.getElementById('dashLine'), {
        type: 'line',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          datasets: [
            { label: 'Revenue', data: [12000, 17500, 16000, 22000, 28500, 19000, 21000, 23000, 34000, 26000, 24000, 28000], tension: .35, borderWidth: 2, pointRadius: 3, fill: false, borderColor: p1 },
            { label: 'Bookings', data: [820, 910, 870, 1120, 1240, 980, 1050, 1200, 1460, 1300, 1220, 1500], yAxisID: 'y1', tension: .4, borderDash: [6, 6], borderWidth: 2, borderColor: pink1 }
          ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: getGridColor() }, title: { display: true, text: '$' } }, y1: { beginAtZero: true, position: 'right', grid: { display: false }, title: { display: true, text: 'count' } } } }
      });

      new Chart(document.getElementById('dashDonut'), {
        type: 'doughnut',
        data: { labels: ['Confirmed', 'Pending', 'Canceled'], datasets: [{ data: [68, 20, 12], backgroundColor: [p2, pink1, p4] }] },
        options: { plugins: { legend: { position: 'bottom' } }, cutout: '58%', maintainAspectRatio: false }
      });

      new Chart(document.getElementById('dashArea'), {
        type: 'line',
        data: { labels: [...Array(30)].map((_, i) => `D-${30 - i}`), datasets: [{ label: 'Revenue ($)', data: [9, 8, 10, 12, 11, 9, 14, 12, 15, 13, 12, 16, 18, 17, 14, 15, 19, 18, 16, 14, 17, 18, 20, 22, 19, 21, 20, 22, 23, 24], tension: .4, borderWidth: 2, pointRadius: 0, fill: true, borderColor: p2, backgroundColor: resolveAlpha(p2, 0.15) }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: getGridColor() } } } }
      });

      new Chart(document.getElementById('dashBarMini'), {
        type: 'bar',
        data: { labels: [...Array(14)].map((_, i) => `D-${14 - i}`), datasets: [{ label: 'Bookings', data: [12, 9, 10, 8, 15, 7, 14, 11, 13, 12, 9, 10, 16, 15], backgroundColor: pink2 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: getGridColor() } } } }
      });

      const tbody = document.querySelector('#dashTable tbody');
      const merged = [
        ...mock.bookings.map(b => ({ type: 'Booking', ref: b.id, user: (mock.users.find(u => u.id === b.user_id)?.full_name) || '—', from: b.ref_type === 'flight' ? (mock.flights.find(f => f.id === b.ref_id)?.from_code || '—') : '—', to: b.ref_type === 'flight' ? (mock.flights.find(f => f.id === b.ref_id)?.to_code || '—') : '—', date: b.created_at, amt: `$${(b.total_price || 0).toFixed(2)}`, st: b.status })),
        ...mock.payments.map(p => ({ type: 'Payment', ref: p.txn_id, user: (mock.users.find(u => u.id === mock.bookings.find(b => b.id === p.booking_id)?.user_id)?.full_name) || '—', from: '—', to: '—', date: p.created_at, amt: `$${(p.amount || 0).toFixed(2)}`, st: p.status }))
      ].sort((a, b) => (a.date < b.date ? 1 : -1));
      merged.forEach(x => { const tr = document.createElement('tr'); tr.innerHTML = `<td>${x.type}</td><td>${x.ref}</td><td>${x.user}</td><td>${x.from}</td><td>${x.to}</td><td>${x.date}</td><td>${x.amt}</td><td>${badge(x.st)}</td>`; tbody.appendChild(tr); });
      $('#dashTable').DataTable({ pageLength: 7, order: [[5, 'desc']], columnDefs: [{ targets: [7], orderable: false }] });
      applyDtDarkSkin();

      buildFlightsMap();
    };
  })(),

  users: (function () {
    let done = false; return async function () {
      if (done) return; done = true;
      const tbody = document.querySelector('#usersTable tbody');
      const data = await API.list('users');
      data.forEach(u => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${u.full_name}</td><td>${u.email}</td><td>${u.phone || '—'}</td><td>${u.created_at || '—'}</td><td>${actions({ edit: true, del: true }, u.id, 'users')}</td>`;
        tbody.appendChild(tr);
      });
      $('#usersTable').DataTable({ pageLength: 7, order: [[3, 'desc']], columnDefs: [{ targets: [4], orderable: false }] });
      applyDtDarkSkin();
    };
  })(),

  admins: (function () {
    let done = false; return async function () {
      if (done) return; done = true;
      const tbody = document.querySelector('#adminsTable tbody');
      const data = await API.list('admins');
      data.forEach(a => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td class="d-flex align-items-center gap-2"><img class="avatar" src="${a.img || 'https://i.pravatar.cc/120?img=3'}" alt=""><span>${a.full_name}</span></td><td>${a.email}</td><td>${a.is_super ? 'Yes' : 'No'}</td><td>${a.created_at || '—'}</td><td>${actions({ edit: true, del: true }, a.id, 'admins')}</td>`;
        tbody.appendChild(tr);
      });
      $('#adminsTable').DataTable({ pageLength: 6, order: [[0, 'asc']], columnDefs: [{ targets: [4], orderable: false }] });
      applyDtDarkSkin();
    };
  })(),

  destinations: (function () {
    let done = false; return async function () {
      if (done) return; done = true;
      const tbody = document.querySelector('#destinationsTable tbody');
      const data = await API.list('destinations');
      data.forEach(d => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${d.slug}</td>
          <td>${d.name}</td>
          <td>${d.kind}</td>
          <td>${d.country_code || '—'}</td>
          <td>${d.city || '—'}</td>
          <td>${d.is_published ? 'Yes' : 'No'}</td>
          <td>${d.sort_order ?? 100}</td>
          <td><code>${d.airport_codes || '[]'}</code></td>
          <td><code>${d.city_aliases || '[]'}</code></td>
          <td>${actions({ edit: true, del: true }, d.id, 'destinations')}</td>`;
        tbody.appendChild(tr);
      });
      $('#destinationsTable').DataTable({ pageLength: 7, order: [[6, 'asc']], columnDefs: [{ targets: [9], orderable: false }] });
      applyDtDarkSkin();
    };
  })(),

  flights: (function () {
    let done = false; return async function () {
      if (done) return; done = true;
      const tbody = document.querySelector('#flightsTable tbody');
      const data = await API.list('flights');
      data.forEach(f => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><img class="thumb" src="${f.image_url || 'https://picsum.photos/seed/flight/80/80'}" alt=""></td>
          <td>${f.airline}</td>
          <td>${f.from_code} → ${f.to_code}</td>
          <td>${f.depart_at}</td>
          <td>${f.arrive_at}</td>
          <td>${f.cabin_class}</td>
          <td>$${Number(f.price).toFixed(2)}</td>
          <td>${f.seats_left}</td>
          <td>${actions({ edit: true, del: true }, f.id, 'flights')}</td>`;
        tbody.appendChild(tr);
      });
      $('#flightsTable').DataTable({ pageLength: 7, order: [[3, 'asc']], columnDefs: [{ targets: [8], orderable: false }] });
      applyDtDarkSkin();

      const css = getComputedStyle(document.documentElement); const p1 = css.getPropertyValue('--p1').trim(); const p3 = css.getPropertyValue('--p3').trim(); const p5 = css.getPropertyValue('--p5').trim();
      new Chart(document.getElementById('flightsDonut'), { type: 'doughnut', data: { labels: ['On-Time', 'Delayed', 'Canceled'], datasets: [{ data: [68, 22, 10], backgroundColor: [p1, p3, p5] }] }, options: { plugins: { legend: { position: 'bottom' } }, cutout: '58%', maintainAspectRatio: false } });
      new Chart(document.getElementById('flightsBar'), { type: 'bar', data: { labels: ['RJ', 'EK', 'QR', 'SV'], datasets: [{ label: 'OTP %', data: [92, 86, 88, 83], backgroundColor: p3 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100, grid: { color: getGridColor() } } } } });
    };
  })(),

  hotels: (function () {
    let done = false; return async function () {
      if (done) return; done = true;
      const tbody = document.querySelector('#hotelsTable tbody');
      const data = await API.list('hotels');
      data.forEach(h => {
        const destName = mock.destinations.find(d => d.id === h.destination_id)?.name || '—';
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><img class="thumb" src="${h.cover_image_url || 'https://picsum.photos/seed/hotel/80/80'}" alt=""></td>
          <td>${h.name}</td>
          <td>${destName}</td>
          <td>${h.city}</td>
          <td>${h.country}</td>
          <td>${h.stars || 0}★</td>
          <td>${h.is_published ? 'Yes' : 'No'}</td>
          <td>${actions({ edit: true, del: true }, h.id, 'hotels')}</td>`;
        tbody.appendChild(tr);
      });
      $('#hotelsTable').DataTable({ pageLength: 7, order: [[1, 'asc']], columnDefs: [{ targets: [7], orderable: false }] });
      applyDtDarkSkin();

      const css = getComputedStyle(document.documentElement); const p1 = css.getPropertyValue('--p1').trim(); const pink1 = (css.getPropertyValue('--pink1') || css.getPropertyValue('--p3')).trim();
      new Chart(document.getElementById('hotelsLine'), { type: 'line', data: { labels: ['Amman', 'Istanbul', 'Dubai', 'Cairo'], datasets: [{ label: 'Occupancy %', data: [78, 84, 88, 69], tension: .35, borderWidth: 2, pointRadius: 3, borderColor: p1 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100, grid: { color: getGridColor() } } } } });
      new Chart(document.getElementById('hotelsPie'), { type: 'pie', data: { labels: ['★5', '★4', '★3', '★2'], datasets: [{ data: [25, 45, 22, 8], backgroundColor: [p1, pink1, resolveAlpha(p1, 0.35), resolveAlpha(pink1, 0.35)] }] }, options: { plugins: { legend: { position: 'bottom' } }, maintainAspectRatio: false } });
    };
  })(),

  rooms: (function () {
    let done = false; return async function () {
      if (done) return; done = true;
      const tbody = document.querySelector('#roomsTable tbody');
      const data = await API.list('rooms');
      data.forEach(r => {
        const hotel = mock.hotels.find(h => h.id === r.hotel_id)?.name || `#${r.hotel_id}`;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${hotel}</td><td>${r.name}</td><td>${r.capacity_adults}/${r.capacity_children}</td><td>$${Number(r.price_per_night).toFixed(2)}</td><td>${r.refundable ? 'Yes' : 'No'}</td><td>${r.is_active ? 'Yes' : 'No'}</td><td>${actions({ edit: true, del: true }, r.id, 'rooms')}</td>`;
        tbody.appendChild(tr);
      });
      $('#roomsTable').DataTable({ pageLength: 7, order: [[0, 'asc']], columnDefs: [{ targets: [6], orderable: false }] });
      applyDtDarkSkin();
    };
  })(),

  packages: (function () {
    let done = false; return async function () {
      if (done) return; done = true;
      const tbody = document.querySelector('#packagesTable tbody');
      const data = await API.list('packages');
      data.forEach(p => {
        const dest = mock.destinations.find(d => d.id === p.destination_id)?.name || '—';
        const fl = mock.flights.find(f => f.id === p.flight_id);
        const ho = mock.hotels.find(h => h.id === p.hotel_id);
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><img class="thumb" src="${p.image_url || 'https://picsum.photos/seed/package/80/80'}" alt=""></td>
          <td>${p.title}</td>
          <td>${dest}</td>
          <td>${p.nights}</td>
          <td>$${Number(p.base_price).toFixed(2)}</td>
          <td>${p.currency}</td>
          <td>${p.is_active ? 'Yes' : 'No'}</td>
          <td>${fl ? `${fl.from_code}→${fl.to_code}` : '—'}</td>
          <td>${ho ? ho.name : '—'}</td>
          <td>${actions({ edit: true, del: true }, p.id, 'packages')}</td>`;
        tbody.appendChild(tr);
      });
      $('#packagesTable').DataTable({ pageLength: 7, order: [[1, 'asc']], columnDefs: [{ targets: [9], orderable: false }] });
      applyDtDarkSkin();
    };
  })(),

  bookings: (function () {
    let done = false; return async function () {
      if (done) return; done = true;
      const tbody = document.querySelector('#bookingsTable tbody');
      const data = await API.list('bookings');
      data.forEach(b => {
        const user = mock.users.find(u => u.id === b.user_id)?.full_name || `#${b.user_id}`;
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${b.id}</td>
          <td>${user}</td>
          <td>${b.ref_type}</td>
          <td>${b.ref_id}</td>
          <td>${b.checkin || '—'}</td>
          <td>${b.checkout || '—'}</td>
          <td>$${Number(b.total_price).toFixed(2)}</td>
          <td>${b.currency}</td>
          <td>${b.status}</td>
          <td>${b.created_at}</td>
          <td>${actions({ edit: true, cancel: true }, b.id, 'bookings')}</td>`;
        tbody.appendChild(tr);
      });
      $('#bookingsTable').DataTable({ pageLength: 7, order: [[9, 'desc']], columnDefs: [{ targets: [10], orderable: false }] });
      applyDtDarkSkin();

      const css = getComputedStyle(document.documentElement); const p1 = css.getPropertyValue('--p1').trim(); const pink1 = (css.getPropertyValue('--pink1') || css.getPropertyValue('--p3')).trim(); const pink2 = (css.getPropertyValue('--pink2') || css.getPropertyValue('--p4')).trim();
      new Chart(document.getElementById('bookingPolar'), { type: 'polarArea', data: { labels: ['flight', 'hotel', 'package'], datasets: [{ data: [54, 28, 18], backgroundColor: [p1, pink1, resolveAlpha(pink2, 0.5)] }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });
      new Chart(document.getElementById('bookingBar'), { type: 'bar', data: { labels: ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'], datasets: [{ label: 'Bookings', data: [22, 18, 25, 19, 27, 24, 29], backgroundColor: pink2 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: getGridColor() } } } } });
    };
  })(),

  payments: (function () {
    let done = false; return async function () {
      if (done) return; done = true;
      const tbody = document.querySelector('#paymentsTable tbody');
      const data = await API.list('payments');
      data.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${p.txn_id}</td><td>${p.booking_id}</td><td>${p.provider}</td><td>$${Number(p.amount).toFixed(2)}</td><td>${p.currency}</td><td>${p.created_at}</td><td>${badge(p.status)}</td><td>${actions({ edit: true, refund: true }, p.id, 'payments')}</td>`;
        tbody.appendChild(tr);
      });
      $('#paymentsTable').DataTable({ pageLength: 7, order: [[5, 'desc']], columnDefs: [{ targets: [7], orderable: false }] });
      applyDtDarkSkin();

      const css = getComputedStyle(document.documentElement); const p1 = css.getPropertyValue('--p1').trim(); const p3 = css.getPropertyValue('--p3').trim();
      new Chart(document.getElementById('payDoughnut'), { type: 'doughnut', data: { labels: ['Visa', 'Mastercard', 'PayPal', 'Cash'], datasets: [{ data: [48, 22, 20, 10], backgroundColor: [p1, p3, '#d6ccff', '#ede9fe'] }] }, options: { plugins: { legend: { position: 'bottom' } }, cutout: '58%', maintainAspectRatio: false } });
      new Chart(document.getElementById('payLine'), { type: 'line', data: { labels: [...Array(14)].map((_, i) => `D-${14 - i}`), datasets: [{ label: 'Payments', data: [12, 9, 10, 8, 15, 7, 14, 11, 13, 12, 9, 10, 16, 15], tension: .35, borderWidth: 2, pointRadius: 3, borderColor: p1 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { grid: { color: getGridColor() } } } } });
    };
  })()
};

const formModal = new bootstrap.Modal('#formModal');
const confirmModal = new bootstrap.Modal('#confirmModal');
const detailsModal = new bootstrap.Modal('#detailsModal');
let CURRENT_ENTITY = null;

function showDetails(title, html) {
  document.getElementById('detailsTitle').textContent = title;
  document.getElementById('detailsBody').innerHTML = html;
  detailsModal.show();
}

function buildForm(entity, mode, data) {
  CURRENT_ENTITY = entity;
  const T = (l, i, t = 'text', v = '') => `
    <div class="col-md-6">
      <label class="form-label" for="${i}">${l}</label>
      <input class="form-control" id="${i}" name="${i}" type="${t}" value="${v ?? ''}">
    </div>`;
  const S = (l, i, opts, val) => `
    <div class="col-md-6">
      <label class="form-label" for="${i}">${l}</label>
      <select class="form-select" id="${i}" name="${i}">
        ${opts.map(o => `<option value="${o}" ${String(o) === String(val) ? 'selected' : ''}>${o}</option>`).join('')}
      </select>
    </div>`;
  const C = (l, i, val) => `
    <div class="col-md-6 form-check mt-4">
      <input class="form-check-input" type="checkbox" id="${i}" name="${i}" ${val ? 'checked' : ''}>
      <label class="form-check-label" for="${i}">${l}</label>
    </div>`;
  const J = (l, i, v = '') => `
    <div class="col-12">
      <label class="form-label" for="${i}">${l}</label>
      <textarea class="form-control" id="${i}" name="${i}" rows="3">${v ?? ''}</textarea>
      <div class="form-text">JSON array</div>
    </div>`;

  let body = '<div class="row g-3">';
  if (entity === 'users') {
    body += T('Full Name', 'full_name', 'text', data?.full_name) + T('Email', 'email', 'email', data?.email) + T('Phone', 'phone', 'text', data?.phone);
  } else if (entity === 'admins') {
    body += T('Full Name', 'full_name', 'text', data?.full_name) + T('Email', 'email', 'email', data?.email) + C('Is Super', 'is_super', data?.is_super);
  } else if (entity === 'destinations') {
    body += T('Slug', 'slug', 'text', data?.slug) + T('Name', 'name', 'text', data?.name) + S('Kind', 'kind', ['city', 'country', 'island'], data?.kind)
      + T('Country Code', 'country_code', 'text', data?.country_code) + T('City', 'city', 'text', data?.city)
      + C('Published', 'is_published', data?.is_published) + T('Sort Order', 'sort_order', 'number', data?.sort_order ?? 100)
      + J('airport_codes', 'airport_codes', data?.airport_codes || '["IST","SAW"]')
      + J('city_aliases', 'city_aliases', data?.city_aliases || '["Istanbul","İstanbul"]');
  } else if (entity === 'flights') {
    body += T('Image URL', 'image_url', 'url', data?.image_url || 'https://picsum.photos/seed/flight/800/600')
      + T('Airline', 'airline', 'text', data?.airline) + T('From (IATA)', 'from_code', 'text', data?.from_code) + T('To (IATA)', 'to_code', 'text', data?.to_code)
      + T('Depart At', 'depart_at', 'datetime-local', data?.depart_at?.replace(' ', 'T')) + T('Arrive At', 'arrive_at', 'datetime-local', data?.arrive_at?.replace(' ', 'T'))
      + S('Cabin', 'cabin_class', ['economy', 'premium', 'business', 'first'], data?.cabin_class)
      + T('Price', 'price', 'number', data?.price) + T('Seats Left', 'seats_left', 'number', data?.seats_left);
  } else if (entity === 'hotels') {
    body += T('Cover Image URL', 'cover_image_url', 'url', data?.cover_image_url || 'https://picsum.photos/seed/hotel/800/600')
      + S('Destination', 'destination_id', mock.destinations.map(d => d.id), data?.destination_id) + T('Name', 'name', 'text', data?.name)
      + T('City', 'city', 'text', data?.city) + T('Country', 'country', 'text', data?.country) + T('Stars', 'stars', 'number', data?.stars)
      + C('Published', 'is_published', data?.is_published);
  } else if (entity === 'rooms') {
    body += S('Hotel', 'hotel_id', mock.hotels.map(h => h.id), data?.hotel_id) + T('Name', 'name', 'text', data?.name)
      + T('Adults', 'capacity_adults', 'number', data?.capacity_adults) + T('Children', 'capacity_children', 'number', data?.capacity_children)
      + T('Price/Night', 'price_per_night', 'number', data?.price_per_night) + C('Refundable', 'refundable', data?.refundable) + C('Active', 'is_active', data?.is_active);
  } else if (entity === 'packages') {
    body += T('Image URL', 'image_url', 'url', data?.image_url || 'https://picsum.photos/seed/package/800/600')
      + S('Destination', 'destination_id', mock.destinations.map(d => d.id), data?.destination_id) + T('Title', 'title', 'text', data?.title)
      + T('Nights', 'nights', 'number', data?.nights) + T('Base Price', 'base_price', 'number', data?.base_price)
      + T('Currency', 'currency', 'text', data?.currency || 'USD') + C('Active', 'is_active', data?.is_active)
      + S('Flight (optional)', 'flight_id', ['', ...mock.flights.map(f => f.id)], data?.flight_id ?? '')
      + S('Hotel (optional)', 'hotel_id', ['', ...mock.hotels.map(h => h.id)], data?.hotel_id ?? '');
  } else if (entity === 'bookings') {
    body += S('User', 'user_id', mock.users.map(u => u.id), data?.user_id) + S('Type', 'ref_type', ['flight', 'hotel', 'package'], data?.ref_type) + T('Ref ID', 'ref_id', 'number', data?.ref_id)
      + S('Status', 'status', ['pending', 'paid', 'cancelled', 'refunded'], data?.status) + T('Total Price', 'total_price', 'number', data?.total_price)
      + T('Currency', 'currency', 'text', data?.currency || 'USD') + T('Check-in', 'checkin', 'date', data?.checkin) + T('Check-out', 'checkout', 'date', data?.checkout);
  } else if (entity === 'payments') {
    body += T('Booking ID', 'booking_id', 'number', data?.booking_id) + S('Provider', 'provider', ['Visa', 'Mastercard', 'PayPal', 'Cash'], data?.provider)
      + S('Status', 'status', ['pending', 'captured', 'failed', 'refunded'], data?.status)
      + T('Amount', 'amount', 'number', data?.amount) + T('Currency', 'currency', 'text', data?.currency || 'USD') + T('Txn ID', 'txn_id', 'text', data?.txn_id);
  }
  body += '</div>';
  document.getElementById('formModalTitle').textContent = (mode === 'add' ? 'Add ' : 'Edit ') + entity.charAt(0).toUpperCase() + entity.slice(1);
  document.getElementById('formModalBody').innerHTML = body;
  document.getElementById('formSubmitBtn').textContent = mode === 'add' ? 'Create' : 'Save';
  formModal.show();
}

document.addEventListener('click', async (e) => {
  const btn = e.target.closest('button, a'); if (!btn) return;
  const action = btn.dataset.action; const entity = btn.dataset.entity; const id = btn.dataset.id;

  if (action === 'add') { e.preventDefault(); buildForm(entity, 'add', null); return; }

  if (action === 'edit') {
    e.preventDefault();
    const data = findEntityData(entity, id);
    buildForm(entity, 'edit', data); return;
  }

  if (['delete', 'cancel', 'refund'].includes(action)) {
    e.preventDefault();
    const label = action === 'delete' ? 'Delete this item?' : action === 'cancel' ? 'Cancel this booking?' : 'Refund this payment?';
    document.getElementById('confirmModalBody').textContent = label;
    document.getElementById('confirmYes').onclick = async () => {
      confirmModal.hide();
      if (action === 'delete') { await API.remove(entity, id); alert('Deleted (mock/API).'); location.reload(); }
      else alert(label + ' (mock)');
    };
    confirmModal.show(); return;
  }
});

function findEntityData(entity, id) {
  const X = mock[entity] || [];
  return X.find(x => String(x.id) === String(id));
}

document.getElementById('entityForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const title = document.getElementById('formModalTitle').textContent.toLowerCase();
  const entity =
    title.includes('destinations') ? 'destinations' :
      title.includes('flights') ? 'flights' :
        title.includes('hotels') ? 'hotels' :
          title.includes('rooms') ? 'rooms' :
            title.includes('packages') ? 'packages' :
              title.includes('bookings') ? 'bookings' :
                title.includes('payments') ? 'payments' :
                  title.includes('admins') ? 'admins' : 'users';

  const fd = new FormData(e.target);
  let payload = Object.fromEntries(fd.entries());

  ['is_super', 'is_published', 'refundable', 'is_active'].forEach(k => { if (payload[k] !== undefined) payload[k] = payload[k] === 'on' ? 1 : 0; });

  ['stars', 'sort_order', 'seats_left', 'nights', 'hotel_id', 'destination_id', 'flight_id', 'user_id', 'ref_id', 'booking_id', 'amount', 'price', 'price_per_night', 'base_price', 'total_price'].forEach(k => {
    if (payload[k] !== undefined && payload[k] !== '') payload[k] = Number(payload[k]);
  });

  ['depart_at', 'arrive_at'].forEach(k => { if (payload[k]) payload[k] = payload[k].replace('T', ' '); });

  payload.id = payload.id || (entity.slice(0, 1) + Math.random().toString(36).slice(2, 7));

  await API.create(entity, payload);
  formModal.hide();
  alert('Saved (mock/API).');
  location.reload();
});
