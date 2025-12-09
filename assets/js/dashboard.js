// ===================== Theme & Layout =====================

(function initTheme() {
  const saved = localStorage.getItem('travelo-theme');
  if (saved === 'dark') document.documentElement.classList.add('dark');
  updateThemeIcon();
})();

function toggleTheme() {
  document.documentElement.classList.toggle('dark');
  localStorage.setItem(
    'travelo-theme',
    document.documentElement.classList.contains('dark') ? 'dark' : 'light'
  );
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

function toggleSidebar(e) {
  e && e.preventDefault();
  document.getElementById('sidebar').classList.toggle('show');
}

const titleMap = {
  dashboard: 'Dashboard',
  users: 'Users',
  admins: 'Admins',
  destinations: 'Destinations',
  flights: 'Flights',
  hotels: 'Hotels',
  packages: 'Packages',
  bookings: 'Bookings',
  payments: 'Payments'
};

function showSection(page) {
  document.querySelectorAll('.section').forEach((s) => s.classList.remove('active'));
  (document.getElementById(page) || document.getElementById('dashboard')).classList.add(
    'active'
  );
  const pt = document.getElementById('pageTitle');
  if (pt) pt.textContent = titleMap[page] || 'Dashboard';
  document
    .querySelectorAll('#mainNav .nav-link')
    .forEach((a) => a.classList.toggle('active', a.dataset.page === page));

  initers[page] && initers[page]();
}

window.addEventListener('hashchange', () =>
  showSection(location.hash.replace('#', '') || 'dashboard')
);
document.addEventListener('DOMContentLoaded', () =>
  showSection(location.hash.replace('#', '') || 'dashboard')
);

// ===================== Global Search =====================

const gSearch = document.getElementById('globalSearch');

document.addEventListener('keydown', (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    gSearch?.focus();
  }
});

function applySearch(val) {
  const visibleTable = Array.from(
    document.querySelectorAll('.section.active table.datatable')
  ).shift();
  if (visibleTable && $(visibleTable).DataTable) {
    $(visibleTable).DataTable().search(val).draw();
  }
}

gSearch?.addEventListener('input', (e) => applySearch(e.target.value));
gSearch?.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') applySearch(e.target.value);
});

// ===================== API Helper =====================

const USE_API = true; // رح نشتغل PHP بعدين
const cache = {
  users: [],
  admins: [],
  destinations: [],
  flights: [],
  hotels: [],
  packages: [],
  bookings: [],
  payments: []
};

function endpoint(entity, action, id) {
  const base = `api/${entity}.php`;
  const qs = new URLSearchParams({ action: action || 'list' });
  if (id) qs.set('id', id);
  return `${base}?${qs.toString()}`;
}

async function apiList(entity) {
  if (!USE_API) return [];
  const url = endpoint(entity, 'list');
  const res = await fetch(url);
  if (!res.ok) throw new Error(`${entity} list failed`);
  const data = await res.json();
  cache[entity] = Array.isArray(data) ? data : [];
  return cache[entity];
}

async function apiCreate(entity, payload) {
  if (!USE_API) return {};
  const res = await fetch(endpoint(entity, 'create'), {
    method: 'POST',
    body: toFormData(payload)
  });
  if (!res.ok) throw new Error(`${entity} create failed`);
  return res.json();
}

async function apiUpdate(entity, id, payload) {
  if (!USE_API) return {};
  const res = await fetch(endpoint(entity, 'update', id), {
    method: 'POST',
    body: toFormData(payload)
  });
  if (!res.ok) throw new Error(`${entity} update failed`);
  return res.json();
}

async function apiDelete(entity, id) {
  if (!USE_API) return { ok: true };
  const res = await fetch(endpoint(entity, 'delete', id), {
    method: 'POST'
  });
  if (!res.ok) throw new Error(`${entity} delete failed`);
  return res.json();
}

function toFormData(obj) {
  const fd = new FormData();
  Object.entries(obj || {}).forEach(([k, v]) => {
    if (v === undefined || v === null) return;
    fd.append(k, v);
  });
  return fd;
}

// ===================== Helpers (UI + Charts) =====================

const badge = (s) => {
  const norm = String(s || '').toLowerCase();
  if (['active', 'on-time', 'paid', 'success', 'captured', 'confirmed'].includes(norm))
    return `<span class="status success">${s}</span>`;
  if (['pending', 'delayed'].includes(norm))
    return `<span class="status pending">${s}</span>`;
  return `<span class="status canceled">${s}</span>`;
};

const actions = (opts, id, entity) => {
  const b = (icon, title, action, cls = 'btn-outline-secondary') =>
    `<button class="btn ${cls} btn-action" data-id="${id}" data-entity="${entity}" data-action="${action}" title="${title}">
      <i class="bi ${icon}"></i>
    </button>`;
  return `<div class="btn-group">
    ${opts.edit ? b('bi-pencil', 'Edit', 'edit') : ''}
    ${opts.del ? b('bi-trash', 'Delete', 'delete', 'btn-outline-danger') : ''}
    ${opts.cancel ? b('bi-x-circle', 'Cancel', 'cancel', 'btn-outline-warning') : ''}
    ${opts.refund ? b('bi-cash-coin', 'Refund', 'refund', 'btn-outline-primary') : ''}
  </div>`;
};

function getGridColor() {
  return document.documentElement.classList.contains('dark') ? '#232742' : '#eef0f4';
}

function resolveAlpha(hex, alpha) {
  if (/^#([A-Fa-f0-9]{6})$/.test(hex)) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r},${g},${b},${alpha})`;
  }
  return hex;
}

function applyDtDarkSkin() {
  document.querySelectorAll('.dataTables_wrapper').forEach((w) => {
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

// ===================== KPI (Dashboard Small Numbers) =====================

async function fillKpis() {
  const el = (id) => document.getElementById(id);
  try {
    const [users, bookings, payments] = await Promise.all([
      apiList('users').catch(() => []),
      apiList('bookings').catch(() => []),
      apiList('payments').catch(() => [])
    ]);

    const totalUsers = users.length;
    const totalBookings = bookings.length;
    const totalRevenue = payments.reduce(
      (s, p) => s + Number(p.amount_total || p.amount || 0),
      0
    );

    // نحسب On-Time كنسبة الحجوزات الـ confirmed من كل الحجوزات
    const confirmedCount = bookings.filter((b) =>
      String(b.booking_status || '').toLowerCase() === 'confirmed'
    ).length;

    const onTimePercent =
      totalBookings > 0 ? Math.round((confirmedCount / totalBookings) * 100) : 0;

    if (el('kpiUsers')) el('kpiUsers').textContent = totalUsers.toLocaleString();
    if (el('kpiBookings')) el('kpiBookings').textContent = totalBookings.toLocaleString();
    if (el('kpiRevenue')) {
      el('kpiRevenue').textContent = '$' + totalRevenue.toLocaleString();
    }
    if (el('kpiOTP')) el('kpiOTP').textContent = onTimePercent + '%';
  } catch (e) {
    console.error('KPI error', e);
  }
}

document.addEventListener('DOMContentLoaded', fillKpis);

// ===================== Leaflet Map =====================

let MAP,
  BASE_LIGHT,
  BASE_DARK,
  PLANE_INT;

function buildFlightsMap() {
  const coords = {
    AMM: [31.722, 35.993],
    IST: [41.275, 28.751],
    DXB: [25.253, 55.365],
    KUL: [2.745, 101.71],
    CAI: [30.121, 31.405],
    JED: [21.679, 39.156],
    DOH: [25.273, 51.608],
    RUH: [24.957, 46.698]
  };
  if (MAP) {
    MAP.remove();
    MAP = null;
  }
  const mapEl = document.getElementById('flightsMap');
  if (!mapEl) return;

  MAP = L.map('flightsMap', { scrollWheelZoom: false, worldCopyJump: true }).setView(
    coords.AMM,
    5
  );

  BASE_LIGHT = L.tileLayer(
    'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    { maxZoom: 20, attribution: '&copy; OpenStreetMap & Carto' }
  );
  BASE_DARK = L.tileLayer(
    'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
    { maxZoom: 20, attribution: '&copy; OpenStreetMap & Carto' }
  );

  (document.documentElement.classList.contains('dark') ? BASE_DARK : BASE_LIGHT).addTo(
    MAP
  );

  const airportIcon = L.divIcon({
    className: '',
    html: '<i class="bi bi-geo-alt-fill" style="font-size:18px;color:var(--accent)"></i>',
    iconSize: [18, 18],
    iconAnchor: [9, 18]
  });

  const markers = {};
  Object.entries(coords).forEach(([k, latlng]) => {
    markers[k] = L.marker(latlng, { icon: airportIcon })
      .addTo(MAP)
      .bindPopup(`<b>${k}</b>`);
  });

  const css = getComputedStyle(document.documentElement);
  const color =
    (css.getPropertyValue('--route') ||
      css.getPropertyValue('--p2') ||
      '#8b5cf6').trim();

  // نحاول نرسم من جدول flights لو متوفر
  let routes = [];
  const flights = cache.flights || [];
  flights.forEach((f) => {
    const from = (f.origin_airport_code || '').toUpperCase();
    const to = (f.destination_airport_code || '').toUpperCase();
    if (coords[from] && coords[to]) {
      routes.push([from, to]);
    }
  });

  // لو ما في داتا/إحداثيات من الداتا بيز، نرجع للروتات الثابتة
  if (!routes.length) {
    routes = [
      ['AMM', 'IST'],
      ['JED', 'AMM'],
      ['AMM', 'DXB'],
      ['DXB', 'KUL'],
      ['AMM', 'CAI'],
      ['AMM', 'DOH'],
      ['AMM', 'RUH']
    ];
  }

  const arcs = [];
  routes.forEach(([a, b]) => {
    const ant = L.polyline.antPath([coords[a], coords[b]], {
      paused: false,
      reverse: false,
      delay: 600,
      dashArray: [10, 20],
      weight: 3,
      opacity: 0.9,
      color
    })
      .addTo(MAP)
      .bindTooltip(`${a} → ${b}`);
    arcs.push(ant);
  });

  const group = L.featureGroup(arcs);
  MAP.fitBounds(group.getBounds().pad(0.2));

  const planeIcon = L.divIcon({ className: 'leaflet-plane', html: '✈️' });
  const plane = L.marker(coords.AMM, { icon: planeIcon, zIndexOffset: 1000 }).addTo(MAP);
  const firstRoute = routes[0] || ['AMM', 'IST'];
  const start = L.latLng(coords[firstRoute[0]]);
  const end = L.latLng(coords[firstRoute[1]]);


  function bearing(a, b) {
    const toRad = (d) => (d * Math.PI) / 180;
    const toDeg = (r) => (r * 180) / Math.PI;
    const lat1 = toRad(a.lat);
    const lat2 = toRad(b.lat);
    const dLon = toRad(b.lng - a.lng);
    const y = Math.sin(dLon) * Math.cos(lat2);
    const x =
      Math.cos(lat1) * Math.cos(lat2) * Math.cos(dLon) -
      Math.sin(lat1) * Math.sin(lat2);
    return (toDeg(Math.atan2(y, x)) + 360) % 360;
  }

  function lerp(a, b, t) {
    return L.latLng(a.lat + (b.lat - a.lat) * t, a.lng + (b.lng - a.lng) * t);
  }

  let t = 0,
    dir = 1;
  const angle = bearing(start, end);
  if (PLANE_INT) clearInterval(PLANE_INT);
  PLANE_INT = setInterval(() => {
    t += dir * 0.01;
    if (t >= 1) {
      t = 1;
      dir = -1;
    }
    if (t <= 0) {
      t = 0;
      dir = 1;
    }
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

// ===================== Initers (Per Section) =====================
// ======== Small date helper for dashboard aggregations ========
function parseDateSafe(str) {
  if (!str) return null;
  const t = Date.parse(str);
  if (Number.isNaN(t)) return null;
  return new Date(t);
}
const initers = {
  // ---------- Dashboard ----------
  // ---------- Dashboard ----------
dashboard: (function () {
  let done = false;
  return async function () {
    if (done) return;
    done = true;

    const css = getComputedStyle(document.documentElement);
    const p1 = css.getPropertyValue('--p1').trim();
    const p2 = css.getPropertyValue('--p2').trim();
    const p3 = css.getPropertyValue('--p3').trim();
    const p4 = css.getPropertyValue('--p4').trim();
    const pink1 = (css.getPropertyValue('--pink1') || p3).trim();
    const pink2 = (css.getPropertyValue('--pink2') || p4).trim();

    // ----- تحميل الداتا من الـ API / الكاش -----
    let bookings = cache.bookings || [];
    let payments = cache.payments || [];
    let users = cache.users || [];
    let flights = cache.flights || [];

    try {
      if (!bookings.length) bookings = await apiList('bookings');
      if (!payments.length) payments = await apiList('payments');
      if (!users.length) users = await apiList('users');
      if (!flights.length) flights = await apiList('flights');
    } catch (e) {
      console.error('dashboard data error:', e);
    }

    // ===== 1) Charts Aggregations =====

    // شهريًا (12 شهر)
    const revenuePerMonth = Array(12).fill(0);
    payments.forEach((p) => {
      const d = parseDateSafe(p.created_at);
      if (!d) return;
      const m = d.getMonth(); // 0..11
      revenuePerMonth[m] += Number(p.amount_total || 0);
    });

    const bookingsPerMonth = Array(12).fill(0);
    bookings.forEach((b) => {
      const d =
        parseDateSafe(b.created_at) ||
        parseDateSafe(b.trip_start_date);
      if (!d) return;
      const m = d.getMonth();
      bookingsPerMonth[m] += 1;
    });

    // آخر 30 يوم – ريفينيو
    const today = new Date();
    const labels30 = [];
    const revenueLast30 = [];
    for (let i = 29; i >= 0; i--) {
      const d = new Date(today);
      d.setDate(d.getDate() - i);
      const key = d.toISOString().slice(0, 10);
      const label = `${d.getMonth() + 1}/${d.getDate()}`;
      labels30.push(label);
      let sum = 0;
      payments.forEach((p) => {
        const k = (p.created_at || '').slice(0, 10);
        if (k === key) sum += Number(p.amount_total || 0);
      });
      revenueLast30.push(sum);
    }

    // آخر 14 يوم – عدد الحجوزات
    const labels14 = [];
    const bookingsLast14 = [];
    for (let i = 13; i >= 0; i--) {
      const d = new Date(today);
      d.setDate(d.getDate() - i);
      const key = d.toISOString().slice(0, 10);
      const label = `${d.getMonth() + 1}/${d.getDate()}`;
      labels14.push(label);
      let cnt = 0;
      bookings.forEach((b) => {
        const k =
          (b.created_at || b.trip_start_date || '').slice(0, 10);
        if (k === key) cnt++;
      });
      bookingsLast14.push(cnt);
    }

    // توزيع حالات الحجز
    const statusCounts = { confirmed: 0, pending: 0, cancelled: 0 };
    bookings.forEach((b) => {
      const st = (b.booking_status || 'pending').toLowerCase();
      if (statusCounts[st] == null) statusCounts.pending++;
      else statusCounts[st]++;
    });

    // ===== 2) بناء التشارتات من الداتا الحقيقية =====

    if (document.getElementById('dashLine')) {
      new Chart(document.getElementById('dashLine'), {
        type: 'line',
        data: {
          labels: [
            'Jan','Feb','Mar','Apr','May','Jun',
            'Jul','Aug','Sep','Oct','Nov','Dec'
          ],
          datasets: [
            {
              label: 'Revenue',
              data: revenuePerMonth,
              tension: 0.35,
              borderWidth: 2,
              pointRadius: 3,
              fill: false,
              borderColor: p1
            },
            {
              label: 'Bookings',
              data: bookingsPerMonth,
              yAxisID: 'y1',
              tension: 0.4,
              borderDash: [6, 6],
              borderWidth: 2,
              borderColor: pink1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: getGridColor() },
              title: { display: true, text: '$' }
            },
            y1: {
              beginAtZero: true,
              position: 'right',
              grid: { display: false },
              title: { display: true, text: 'count' }
            }
          }
        }
      });
    }

    if (document.getElementById('dashDonut')) {
      new Chart(document.getElementById('dashDonut'), {
        type: 'doughnut',
        data: {
          labels: ['Confirmed', 'Pending', 'Cancelled'],
          datasets: [
            {
              data: [
                statusCounts.confirmed,
                statusCounts.pending,
                statusCounts.cancelled
              ],
              backgroundColor: [p2, pink1, p4]
            }
          ]
        },
        options: {
          plugins: { legend: { position: 'bottom' } },
          cutout: '58%',
          maintainAspectRatio: false
        }
      });
    }

    if (document.getElementById('dashArea')) {
      new Chart(document.getElementById('dashArea'), {
        type: 'line',
        data: {
          labels: labels30,
          datasets: [
            {
              label: 'Revenue ($)',
              data: revenueLast30,
              tension: 0.4,
              borderWidth: 2,
              pointRadius: 0,
              fill: true,
              borderColor: p2,
              backgroundColor: resolveAlpha(p2, 0.15)
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true, grid: { color: getGridColor() } } }
        }
      });
    }

    if (document.getElementById('dashBarMini')) {
      new Chart(document.getElementById('dashBarMini'), {
        type: 'bar',
        data: {
          labels: labels14,
          datasets: [
            {
              label: 'Bookings',
              data: bookingsLast14,
              backgroundColor: pink2
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true, grid: { color: getGridColor() } } }
        }
      });
    }

    // ===== 3) جدول الـ Activity في أول الداشبورد =====
    const tbody = document.querySelector('#dashTable tbody');
    if (tbody) {
      tbody.innerHTML = '';

      const rows = [];

      // Bookings rows
      bookings.forEach((b) => {
        const u = users.find((u) => u.id === b.user_id);
        const uname =
          (u &&
            ([u.first_name, u.last_name].filter(Boolean).join(' ') ||
              u.username ||
              u.email)) ||
          `#${b.user_id}`;

        rows.push({
          sortDate: b.created_at || b.trip_start_date || '',
          type: 'Booking',
          ref: b.booking_code || `BK-${b.id}`,
          user: uname,
          from: b.from_city || '—',
          to: b.to_city || '—',
          date: (b.trip_start_date || b.created_at || '—').slice(0, 10),
          amt: '$' + Number(b.total_amount || 0).toFixed(2),
          st: b.booking_status || 'pending'
        });
      });

      // Payments rows
      payments.forEach((p) => {
        const u = users.find((u) => u.id === p.user_id);
        const uname =
          (u &&
            ([u.first_name, u.last_name].filter(Boolean).join(' ') ||
              u.username ||
              u.email)) ||
          (p.user_id ? `#${p.user_id}` : '—');

        rows.push({
          sortDate: p.created_at || '',
          type: 'Payment',
          ref: p.gateway_reference || `TX-${p.id}`,
          user: uname,
          from: '—',
          to: '—',
          date: (p.created_at || '—').slice(0, 10),
          amt: '$' + Number(p.amount_total || 0).toFixed(2),
          st: p.status || 'pending'
        });
      });

      rows
        .sort((a, b) => (b.sortDate || '').localeCompare(a.sortDate || ''))
        .slice(0, 30)
        .forEach((x) => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${x.type}</td>
            <td>${x.ref}</td>
            <td>${x.user}</td>
            <td>${x.from}</td>
            <td>${x.to}</td>
            <td>${x.date}</td>
            <td>${x.amt}</td>
            <td>${badge(x.st)}</td>`;
          tbody.appendChild(tr);
        });

      $('#dashTable').DataTable({
        pageLength: 7,
        order: [[5, 'desc']],
        columnDefs: [{ targets: [7], orderable: false }]
      });
      applyDtDarkSkin();
    }

    // ===== 4) الماب – رح تستخدم الفلايتس من الكاش (ولو مش موجودة ترجع للـ sample) =====
    buildFlightsMap();
  };
})(),


  // ---------- Users ----------
  users: (function () {
    let done = false;
    return async function () {
      if (done) return;
      done = true;

      const tbody = document.querySelector('#usersTable tbody');
      if (!tbody) return;

      let data = [];
      try {
        data = await apiList('users');
      } catch (e) {
        console.error('users API error:', e);
        data = [];
      }

      tbody.innerHTML = '';
      (data || []).forEach((u) => {
        const fullName = [u.first_name, u.last_name].filter(Boolean).join(' ') || '—';
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${fullName}</td>
          <td>${u.username || '—'}</td>
          <td>${u.email || '—'}</td>
          <td>${u.birth_date || '—'}</td>
          <td>${u.is_active ? 'Yes' : 'No'}</td>
          <td>${u.created_at || '—'}</td>
          <td>${actions({ edit: true, del: true }, u.id, 'users')}</td>`;
        tbody.appendChild(tr);
      });

      $('#usersTable').DataTable({
        pageLength: 7,
        order: [[5, 'desc']],
        columnDefs: [{ targets: [6], orderable: false }]
      });
      applyDtDarkSkin();
    };
  })(),

  // ---------- Admins ----------
  admins: (function () {
    let done = false;
    return async function () {
      if (done) return;
      done = true;

      const tbody = document.querySelector('#adminsTable tbody');
      if (!tbody) return;

      let data = [];
      try {
        data = await apiList('admins');
      } catch (e) {
        console.error('admins API error:', e);
        data = [];
      }

      tbody.innerHTML = '';
      (data || []).forEach((a) => {
        const fullName =
          a.display_name ||
          [a.first_name, a.last_name].filter(Boolean).join(' ') ||
          '—';
        const avatar = a.avatar_url || 'https://i.pravatar.cc/120?img=3';
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="d-flex align-items-center gap-2">
            <img class="avatar" src="${avatar}" alt="">
            <span>${fullName}</span>
          </td>
          <td>${a.email || '—'}</td>
          <td>${a.is_super ? 'Yes' : 'No'}</td>
          <td>${a.is_active ? 'Yes' : 'No'}</td>
          <td>${a.created_at || '—'}</td>
          <td>${actions({ edit: true, del: true }, a.id, 'admins')}</td>`;
        tbody.appendChild(tr);
      });

      $('#adminsTable').DataTable({
        pageLength: 6,
        order: [[0, 'asc']],
        columnDefs: [{ targets: [5], orderable: false }]
      });
      applyDtDarkSkin();
    };
  })(),

  // ---------- Destinations ----------
  destinations: (function () {
    let done = false;
    return async function () {
      if (done) return;
      done = true;

      const tbody = document.querySelector('#destinationsTable tbody');
      if (!tbody) return;

      let data = [];
      try {
        data = await apiList('destinations');
      } catch (e) {
        console.error('destinations API error:', e);
        data = [];
      }

      tbody.innerHTML = '';
      (data || []).forEach((d) => {
        const imgHtml = d.image_url
          ? `<img src="${d.image_url}" alt="${d.name || ''}" class="thumb">`
          : `<div class="thumb d-flex align-items-center justify-content-center border bg-light text-muted">
             N/A
           </div>`;

        const tr = document.createElement('tr');
        tr.innerHTML = `
        <td>${imgHtml}</td>
        <td>${d.name || '—'}</td>
        <td>${d.city || '—'}</td>
        <td>${d.country || '—'}</td>
        <td>${d.category || '—'}</td>
        <td>$${Number(d.base_price || 0).toFixed(2)}</td>
        <td>${d.currency || 'USD'}</td>
        <td>${d.is_top ? 'Yes' : 'No'}</td>
        <td>${d.is_active ? 'Yes' : 'No'}</td>
        <td>${d.created_at || '—'}</td>
        <td>${actions({ edit: true, del: true }, d.id, 'destinations')}</td>`;
        tbody.appendChild(tr);
      });

      $('#destinationsTable').DataTable({
        pageLength: 7,
        order: [[1, 'asc']], // نرتّب بالاسم
        columnDefs: [{ targets: [10], orderable: false }]
      });
      applyDtDarkSkin();
    };
  })(),

  // ---------- Flights ----------
  // ---------- Flights ----------
flights: (function () {
  let done = false;
  return async function () {
    if (done) return;
    done = true;

    const tbody = document.querySelector('#flightsTable tbody');
    if (!tbody) return;

    let flights = [];
    let dests = cache.destinations;
    let bookings = cache.bookings;

    // نجيب الفلايتس
    try {
      flights = await apiList('flights');
    } catch (e) {
      console.error('flights API error:', e);
      flights = [];
    }

    // نجيب الديستنيشنز إذا مش بالكاش
    if (!dests || !dests.length) {
      try {
        dests = await apiList('destinations');
      } catch (e) {
        console.error('destinations API error (for flights):', e);
        dests = [];
      }
    }

    // نجيب البوكنجز عشان التشارتس
    if (!bookings || !bookings.length) {
      try {
        bookings = await apiList('bookings');
      } catch (e) {
        console.error('bookings API error (for flights charts):', e);
        bookings = [];
      }
    }

    // ================== جدول الفلايتس ==================
    tbody.innerHTML = '';

    (flights || []).forEach((f) => {
      const dest = dests.find((d) => String(d.id) === String(f.destination_id));
      const destName = dest ? dest.name : '—';
      const destCity = dest ? dest.city : (f.destination_city || '—');

      const originCity = f.origin_city || 'Amman';
      const route = `${originCity} → ${destCity}`;

      const departDate = f.departure_date || f.depart_date || '—';
      const returnDate = f.return_date || '—';
      const departTime = f.departure_time || '—';
      const arriveTime = f.arrival_time || '—';
      const duration =
        f.duration_hours != null ? Number(f.duration_hours).toFixed(1) : '—';
      const stops =
        f.stops_count != null ? Number(f.stops_count) : 0;

      const price = `$${Number(f.base_price || 0).toFixed(2)}`;
      const currency = f.currency || 'USD';
      const active = f.is_active ? 'Yes' : 'No';

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${f.airline_name || '—'}</td>
        <td>${f.flight_number || '—'}</td>
        <td>${destName}</td>
        <td>${route}</td>
        <td>${f.trip_type || '—'}</td>
        <td>${departDate}</td>
        <td>${returnDate}</td>
        <td>${departTime}</td>
        <td>${arriveTime}</td>
        <td>${duration}</td>
        <td>${stops}</td>
        <td>${price}</td>
        <td>${currency}</td>
        <td>${active}</td>
        <td>${actions({ edit: true, del: true }, f.id, 'flights')}</td>
      `;
      tbody.appendChild(tr);
    });

    $('#flightsTable').DataTable({
      pageLength: 7,
      order: [[5, 'asc']],                // حسب تاريخ الإقلاع
      columnDefs: [{ targets: [14], orderable: false }]
    });

    applyDtDarkSkin();

    // ================== التشارتس الديناميكية ==================
    const css = getComputedStyle(document.documentElement);
    const p1 = css.getPropertyValue('--p1').trim();
    const p3 = css.getPropertyValue('--p3').trim();
    const p5 = css.getPropertyValue('--p5').trim() || resolveAlpha(p3, 0.4);

    // نحسب إحصائيات البوكنجز تبعات الفلايتس
const flightBookings = (bookings || []).filter(
  (b) => b.flight_id != null && b.flight_id !== ''
);
    let onTimeCount = 0;
    let delayedCount = 0;
    let cancelledCount = 0;

    flightBookings.forEach((b) => {
      const st = (b.booking_status || '').toLowerCase();
      if (st === 'confirmed') onTimeCount++;
      else if (st === 'cancelled') cancelledCount++;
      else delayedCount++; // أي شيء غير هيك بنعتبره delayed
    });

    const totalForDonut = onTimeCount + delayedCount + cancelledCount;
    const donutData =
      totalForDonut > 0
        ? [onTimeCount, delayedCount, cancelledCount]
        : [68, 22, 10]; // fallback لو ما في داتا

    if (document.getElementById('flightsDonut')) {
      new Chart(document.getElementById('flightsDonut'), {
        type: 'doughnut',
        data: {
          labels: ['On-Time', 'Delayed', 'Cancelled'],
          datasets: [
            {
              data: donutData,
              backgroundColor: [p1, p3, p5]
            }
          ]
        },
        options: {
          plugins: { legend: { position: 'bottom' } },
          cutout: '58%',
          maintainAspectRatio: false
        }
      });
    }

    // ---------- Airline OTP % ----------
    const airlineStats = {}; // { 'Royal Jordanian': {total:.., onTime:..}, ... }

    flightBookings.forEach((b) => {
      const fl = flights.find((f) => String(f.id) === String(b.flight_id));
      if (!fl || !fl.airline_name) return;
      const name = fl.airline_name;

      if (!airlineStats[name]) airlineStats[name] = { total: 0, onTime: 0 };
      airlineStats[name].total++;
      if ((b.booking_status || '').toLowerCase() === 'confirmed') {
        airlineStats[name].onTime++;
      }
    });

    let labels = Object.keys(airlineStats);
    let otpValues = labels.map((name) => {
      const s = airlineStats[name];
      return s.total > 0 ? Math.round((s.onTime / s.total) * 100) : 0;
    });

    // لو ما في داتا، نرجع للـ sample
    if (!labels.length) {
      labels = ['RJ', 'EK', 'QR', 'SV'];
      otpValues = [92, 86, 88, 83];
    }

    if (document.getElementById('flightsBar')) {
      new Chart(document.getElementById('flightsBar'), {
        type: 'bar',
        data: {
          labels,
          datasets: [
            { label: 'OTP %', data: otpValues, backgroundColor: p3 }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              grid: { color: getGridColor() }
            }
          }
        }
      });
    }
  };
})(),

  // ---------- Hotels ----------
  // ---------- Hotels ----------
hotels: (function () {
  let done = false;
  return async function () {
    if (done) return;
    done = true;

    const tbody = document.querySelector('#hotelsTable tbody');
    if (!tbody) return;

    let hotels = [];
    let dests = cache.destinations;

    try {
      hotels = await apiList('hotels');
    } catch (e) {
      console.error('hotels API error:', e);
      hotels = [];
    }

    if (!dests || !dests.length) {
      try {
        dests = await apiList('destinations');
      } catch (e) {
        console.error('destinations API error (for hotels):', e);
        dests = [];
      }
    }

    // ================== جدول الفنادق ==================
    tbody.innerHTML = '';
    (hotels || []).forEach((h) => {
      const dest = dests.find((d) => d.id === h.destination_id);
      const destName = dest ? dest.name : '—';
      const city = dest ? dest.city : '—';
      const country = dest ? dest.country : '—';

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${h.id}</td>
        <td>${h.name || '—'}</td>
        <td>${destName}</td>
        <td>${city}</td>
        <td>${country}</td>
        <td>${h.rating ? `${h.rating}★` : '—'}</td>
        <td>${h.reviews_count || 0}</td>
        <td>$${Number(h.price_per_night || 0).toFixed(2)}</td>
        <td>${h.currency || 'USD'}</td>
        <td>${h.is_active ? 'Yes' : 'No'}</td>
        <td>${h.created_at || '—'}</td>
        <td>${actions({ edit: true, del: true }, h.id, 'hotels')}</td>`;
      tbody.appendChild(tr);
    });

    $('#hotelsTable').DataTable({
      pageLength: 7,
      order: [[1, 'asc']],
      columnDefs: [{ targets: [11], orderable: false }]
    });
    applyDtDarkSkin();

    // ================== نحضّر الداتا للتشارتس ==================

    // 1) Occupancy: بنقيس "نسبة إشغال" تقريبية من معدّل الـ rating لكل مدينة
    const cityStats = {};
    (hotels || []).forEach((h) => {
      const dest = dests.find((d) => d.id === h.destination_id);
      const city = dest ? dest.city : 'Other';
      const r = parseFloat(h.rating) || 0;
      if (!cityStats[city]) cityStats[city] = { sumRating: 0, count: 0 };
      cityStats[city].sumRating += r;
      cityStats[city].count += 1;
    });

    let occLabels = [];
    let occValues = [];

    const cityNames = Object.keys(cityStats);
    if (cityNames.length > 0) {
      cityNames.forEach((c) => {
        const { sumRating, count } = cityStats[c];
        const avgRating = count ? sumRating / count : 0;
        const occ = Math.round((avgRating / 5) * 100); // من 0–5 → 0–100%
        occLabels.push(c);
        occValues.push(occ);
      });
    } else {
      // لو ما في ولا فندق/داتا → نرجع للـ Sample
      occLabels = ['Amman', 'Istanbul', 'Dubai', 'Cairo'];
      occValues = [78, 84, 88, 69];
    }

    // 2) Star rating mix: توزيع عدد الفنادق حسب ★5 / ★4 / ★3 / ★2 وأقل
    const starBuckets = { 5: 0, 4: 0, 3: 0, 2: 0 };
    (hotels || []).forEach((h) => {
      const r = parseFloat(h.rating);
      if (!r) return;
      if (r >= 4.5) starBuckets[5] += 1;
      else if (r >= 3.5) starBuckets[4] += 1;
      else if (r >= 2.5) starBuckets[3] += 1;
      else starBuckets[2] += 1;
    });

    let starData = [
      starBuckets[5],
      starBuckets[4],
      starBuckets[3],
      starBuckets[2]
    ];

    const totalStars = starData.reduce((s, x) => s + x, 0);
    if (!totalStars) {
      // برضو لو ما في داتا، نرجع للأرقام القديمة
      starData = [25, 45, 22, 8];
    }

    // ================== رسم التشارتس ==================
    const css = getComputedStyle(document.documentElement);
    const p1 = css.getPropertyValue('--p1').trim();
    const pink1 =
      (css.getPropertyValue('--pink1') || css.getPropertyValue('--p3')).trim();

    // Occupancy line chart
    if (document.getElementById('hotelsLine')) {
      new Chart(document.getElementById('hotelsLine'), {
        type: 'line',
        data: {
          labels: occLabels,
          datasets: [
            {
              label: 'Occupancy %',
              data: occValues,
              tension: 0.35,
              borderWidth: 2,
              pointRadius: 3,
              borderColor: p1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              grid: { color: getGridColor() }
            }
          }
        }
      });
    }

    // Star rating pie chart
    if (document.getElementById('hotelsPie')) {
      new Chart(document.getElementById('hotelsPie'), {
        type: 'pie',
        data: {
          labels: ['★5', '★4', '★3', '★2'],
          datasets: [
            {
              data: starData,
              backgroundColor: [
                p1,
                pink1,
                resolveAlpha(p1, 0.35),
                resolveAlpha(pink1, 0.35)
              ]
            }
          ]
        },
        options: {
          plugins: { legend: { position: 'bottom' } },
          maintainAspectRatio: false
        }
      });
    }
  };
})(),


  // ---------- Packages ----------
  packages: (function () {
    let done = false;
    return async function () {
      if (done) return;
      done = true;

      const tbody = document.querySelector('#packagesTable tbody');
      if (!tbody) return;

      let packages = [];
      let dests = cache.destinations;

      try {
        packages = await apiList('packages');
      } catch (e) {
        console.error('packages API error:', e);
        packages = [];
      }

      if (!dests || !dests.length) {
        try {
          dests = await apiList('destinations');
        } catch (e) {
          console.error('destinations API error (for packages):', e);
          dests = [];
        }
      }

      tbody.innerHTML = '';
      (packages || []).forEach((p) => {
        const dest = dests.find((d) => d.id === p.destination_id);
        const destName = dest ? dest.name : p.location || '—';

        const imgSrc = p.image_url || 'https://picsum.photos/seed/package/80/60';

        const tr = document.createElement('tr');
        tr.innerHTML = `
        <td>
          <img src="${imgSrc}" alt="${p.title || ''}" style="width:60px;height:45px;object-fit:cover;border-radius:6px;">
        </td>
        <td>${p.title || '—'}</td>
        <td>${destName}</td>
        <td>${p.from_city || 'Amman'}</td>
        <td>${p.location || '—'}</td>
        <td>${p.duration_days || 0}</td>
        <td>$${Number(p.price_usd || 0).toFixed(2)}</td>
        <td>${p.rating || '—'}</td>
        <td>${p.category || '—'}</td>
        <td>${p.is_featured ? 'Yes' : 'No'}</td>
        <td>${p.is_active ? 'Yes' : 'No'}</td>
        <td>${p.created_at || '—'}</td>
        <td>${actions({ edit: true, del: true }, p.id, 'packages')}</td>`;
        tbody.appendChild(tr);
      });

      $('#packagesTable').DataTable({
        pageLength: 7,
        order: [[1, 'asc']], // نرتّب على العنوان
        columnDefs: [{ targets: [12], orderable: false }] // actions = آخر عمود (index 12)
      });
      applyDtDarkSkin();
    };
  })(),

  // ---------- Bookings ----------
 // ---------- Bookings ----------
bookings: (function () {
  let done = false;
  return async function () {
    if (done) return;
    done = true;

    const tbody = document.querySelector('#bookingsTable tbody');
    if (!tbody) return;

    let bookings = [];
    let users = cache.users;

    try {
      bookings = await apiList('bookings');
    } catch (e) {
      console.error('bookings API error:', e);
      bookings = [];
    }

    if (!users || !users.length) {
      try {
        users = await apiList('users');
      } catch (e) {
        console.error('users API error (for bookings):', e);
        users = [];
      }
    }

    tbody.innerHTML = '';
    (bookings || []).forEach((b) => {
      const u = users.find((u) => u.id === b.user_id);
      const uname =
        (u &&
          ([u.first_name, u.last_name].filter(Boolean).join(' ') ||
            u.username ||
            u.email)) ||
        `#${b.user_id}`;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${b.booking_code || b.id}</td>
        <td>${uname}</td>
        <td>${b.booking_type || 'bundle'}</td>
        <td>${b.package_id || '—'}</td>
        <td>${b.trip_start_date || '—'}</td>
        <td>${b.trip_end_date || '—'}</td>
        <td>$${Number(b.total_amount || 0).toFixed(2)}</td>
        <td>${b.currency || 'USD'}</td>
        <td>${b.payment_status || 'pending'}</td>
        <td>${b.booking_status || 'pending'}</td>
        <td>${b.created_at || '—'}</td>
        <td>${actions({ edit: true, cancel: true }, b.id, 'bookings')}</td>
      `;
      tbody.appendChild(tr);
    });

    $('#bookingsTable').DataTable({
      pageLength: 7,
      order: [[10, 'desc']],          // حسب عمود "CREATED"
      columnDefs: [{ targets: [11], orderable: false }] // actions
    });
    applyDtDarkSkin();

    // ================= Charts for Bookings =================

    const css = getComputedStyle(document.documentElement);
    const p1 = css.getPropertyValue('--p1').trim();  // لون رئيسي
    const p3 = css.getPropertyValue('--p3').trim();  // لون ثاني

    // ------- 1) Bookings by Type (doughnut) -------
    if (document.getElementById('bookingsTypeChart')) {
      const typeCounts = {
        flight: 0,
        hotel: 0,
        package: 0,
        bundle: 0,
        other: 0
      };

      (bookings || []).forEach((b) => {
        const t = (b.booking_type || 'other').toLowerCase();
        if (typeCounts.hasOwnProperty(t)) typeCounts[t]++;
        else typeCounts.other++;
      });

      const labels = ['Flight', 'Hotel', 'Package', 'Bundle', 'Other'];
      const dataVals = [
        typeCounts.flight,
        typeCounts.hotel,
        typeCounts.package,
        typeCounts.bundle,
        typeCounts.other
      ];

      new Chart(document.getElementById('bookingsTypeChart'), {
        type: 'doughnut',
        data: {
          labels,
          datasets: [
            {
              data: dataVals,
              backgroundColor: [
                p1,
                p3,
                resolveAlpha(p1, 0.6),
                resolveAlpha(p3, 0.6),
                '#d6ccff'
              ]
            }
          ]
        },
        options: {
          plugins: { legend: { position: 'bottom' } },
          cutout: '58%',
          maintainAspectRatio: false
        }
      });
    }

    // ------- 2) Daily Bookings (last 7 days) -------
    if (document.getElementById('bookingsDailyChart')) {
      const today = new Date();
      const labels = [];
      const counts = [];

      // نجهز آخر 7 أيام
      for (let i = 6; i >= 0; i--) {
        const d = new Date(today);
        d.setDate(d.getDate() - i);
        const key = d.toISOString().slice(0, 10); // YYYY-MM-DD
        labels.push(key);
        counts.push(0);
      }

      // نعدّ لكل يوم كم booking (من created_at)
      (bookings || []).forEach((b) => {
        const created = (b.created_at || '').slice(0, 10);
        const idx = labels.indexOf(created);
        if (idx !== -1) counts[idx]++;
      });

      new Chart(document.getElementById('bookingsDailyChart'), {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Bookings',
              data: counts,
              tension: 0.35,
              borderWidth: 2,
              pointRadius: 3,
              fill: true,
              borderColor: p1,
              backgroundColor: resolveAlpha(p1, 0.18)
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: getGridColor() }
            },
            x: {
              grid: { display: false }
            }
          }
        }
      });
    }
  };
})(),


  // ---------- Payments ----------
// ---------- Payments ----------
payments: (function () {
  let done = false;
  return async function () {
    if (done) return;
    done = true;

    const tbody = document.querySelector('#paymentsTable tbody');
    if (!tbody) return;

    let payments = [];
    let users = cache.users;

    try {
      payments = await apiList('payments');
    } catch (e) {
      console.error('payments API error:', e);
      payments = [];
    }

    if (!users || !users.length) {
      try {
        users = await apiList('users');
      } catch (e) {
        console.error('users API error (for payments):', e);
        users = [];
      }
    }

    tbody.innerHTML = '';
    (payments || []).forEach((p) => {
      const u = users.find((u) => u.id === p.user_id);
      const uname =
        (u &&
          ([u.first_name, u.last_name].filter(Boolean).join(' ') ||
            u.username ||
            u.email)) ||
        (p.user_id ? `#${p.user_id}` : '—');

      const tr = document.createElement('tr');

      // ID, Booking, User, Method, Amount, Currency, Status, Created, Actions
      tr.innerHTML = `
        <td>${p.gateway_reference || p.id}</td>
        <td>${p.booking_id}</td>
        <td>${uname}</td>
        <td>${p.payment_method || p.card_brand || '—'}</td>
        <td>$${Number(p.amount_total || 0).toFixed(2)}</td>
        <td>${p.currency || 'USD'}</td>
        <td>${badge(p.status || 'pending')}</td>
        <td>${p.created_at || '—'}</td>
        <td>${actions({ edit: true, refund: true }, p.id, 'payments')}</td>
      `;
      tbody.appendChild(tr);
    });

    $('#paymentsTable').DataTable({
      pageLength: 7,
      order: [[7, 'desc']], // حسب تاريخ الدفع
      columnDefs: [{ targets: [8], orderable: false }] // Actions col
    });
    applyDtDarkSkin();

    // ================= Charts for Payments =================
    const css = getComputedStyle(document.documentElement);
    const p1 = css.getPropertyValue('--p1').trim();
    const p3 = css.getPropertyValue('--p3').trim();

    // ------- 1) Methods Split (doughnut) -------
    if (document.getElementById('payDoughnut')) {
      const methodCounts = {
        visa: 0,
        mastercard: 0,
        cashcard: 0,
        other: 0
      };

      (payments || []).forEach((p) => {
        const m = (p.payment_method || p.card_brand || '').toLowerCase();
        if (m.includes('visa')) methodCounts.visa++;
        else if (m.includes('master')) methodCounts.mastercard++;
        else if (m.includes('cash')) methodCounts.cashcard++;
        else methodCounts.other++;
      });

      const labels = ['Visa', 'Mastercard', 'Cashcard', 'Other'];
      const dataVals = [
        methodCounts.visa,
        methodCounts.mastercard,
        methodCounts.cashcard,
        methodCounts.other
      ];

      new Chart(document.getElementById('payDoughnut'), {
        type: 'doughnut',
        data: {
          labels,
          datasets: [
            {
              data: dataVals,
              backgroundColor: [
                p1,
                p3,
                resolveAlpha(p1, 0.45),
                '#d6ccff'
              ]
            }
          ]
        },
        options: {
          plugins: { legend: { position: 'bottom' } },
          cutout: '58%',
          maintainAspectRatio: false
        }
      });
    }

// ------- 2) Daily Payments (last 14 days including today) -------
if (document.getElementById('payLine')) {
  const today = new Date();
  const dayKeys = [];
  const labels = [];
  const sums = [];

  // آخر 14 يوم **من اليوم نفسه (D-0) لغاية D-13**
  for (let i = 13; i >= 0; i--) {
    const d = new Date(today);
    d.setDate(d.getDate() - i);
    const iso = d.toISOString().slice(0, 10); // YYYY-MM-DD
    dayKeys.push(iso);
    labels.push(i === 0 ? 'Today' : `D-${i}`);
    sums.push(0);
  }

  (payments || []).forEach((p) => {
    const dateSrc = p.created_at || p.payment_date || p.paid_at;
    if (!dateSrc) return;
    const day = String(dateSrc).slice(0, 10);
    const idx = dayKeys.indexOf(day);
    if (idx !== -1) {
      sums[idx] += Number(p.amount_total || p.amount || 0);
    }
  });

  new Chart(document.getElementById('payLine'), {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Payments',
          data: sums,
          tension: 0.35,
          borderWidth: 2,
          pointRadius: 3,
          borderColor: p1,
          fill: true,
          backgroundColor: resolveAlpha(p1, 0.18)
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: getGridColor() }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });
}

  };
})()

};

// ===================== Forms (CRUD) =====================

const formModal = new bootstrap.Modal('#formModal');
const confirmModal = new bootstrap.Modal('#confirmModal');
const detailsModal = new bootstrap.Modal('#detailsModal');

let CURRENT_ENTITY = null;
let CURRENT_MODE = 'add';
let CURRENT_ID = null;

function showDetails(title, html) {
  document.getElementById('detailsTitle').textContent = title;
  document.getElementById('detailsBody').innerHTML = html;
  detailsModal.show();
}

// --- Build select options for destinations once ---
async function getDestinationOptions() {
  const res = await fetch('api/destinations.php');
  const rows = await res.json();
  return rows.map((row) => ({
    value: row.id,
    label: `${row.name} (${row.city}, ${row.country})`,
    city: row.city // 👈 مهم
  }));
}

// Helpers for HTML fields
function fieldText(label, name, type = 'text', value = '', extraAttrs = '') {
  return `
  <div class="col-md-6">
    <label class="form-label" for="${name}">${label}</label>
    <input class="form-control"
           id="${name}"
           name="${name}"
           type="${type}"
           value="${value ?? ''}"
           ${extraAttrs}>
  </div>`;
}

function fieldTextarea(label, name, value = '') {
  return `
  <div class="col-12">
    <label class="form-label" for="${name}">${label}</label>
    <textarea class="form-control" id="${name}" name="${name}" rows="3">${value ?? ''}</textarea>
  </div>`;
}

function fieldSelect(label, name, options, selectedValue) {
  let html = `<div class="col-md-6 mb-3">
    <label for="${name}" class="form-label">${label}</label>
    <select id="${name}" name="${name}" class="form-select">
  `;
  for (const opt of options) {
    const selected =
      String(opt.value) === String(selectedValue ?? '') ? ' selected' : '';
    const dataCity = opt.city ? ` data-city="${opt.city}"` : '';
    html += `<option value="${opt.value}"${selected}${dataCity}>${opt.label}</option>`;
  }
  html += '</select></div>';
  return html;
}

function fieldCheckbox(label, name, checked) {
  return `
  <div class="col-md-4 form-check mt-4">
    <input class="form-check-input" type="checkbox" id="${name}" name="${name}" ${
      checked ? 'checked' : ''
    }>
    <label class="form-check-label" for="${name}">${label}</label>
  </div>`;
}

async function buildForm(entity, mode, data) {
  CURRENT_ENTITY = entity;
  CURRENT_MODE = mode;
  CURRENT_ID = data?.id || null;

  let body = '<div class="row g-3">';
  if (CURRENT_ID) {
    body += `<input type="hidden" name="id" value="${CURRENT_ID}">`;
  }

  if (entity === 'admins') {
    body += fieldText('First Name', 'first_name', 'text', data?.first_name);
    body += fieldText('Last Name', 'last_name', 'text', data?.last_name);
    body += fieldText('Display Name', 'display_name', 'text', data?.display_name);
    body += fieldText('Email', 'email', 'email', data?.email);
    body += fieldText('Password (hash or plain to hash later)', 'password_hash', 'text', '');
    body += fieldText('Avatar URL', 'avatar_url', 'url', data?.avatar_url);
    body += fieldCheckbox('Is Super Admin', 'is_super', data?.is_super);
    body += fieldCheckbox('Is Active', 'is_active', data?.is_active ?? 1);
  } else if (entity === 'users') {
    body += fieldText('First Name', 'first_name', 'text', data?.first_name);
    body += fieldText('Last Name', 'last_name', 'text', data?.last_name);
    body += fieldText('Username', 'username', 'text', data?.username);
    body += fieldText('Email', 'email', 'email', data?.email);
    body += fieldText('Birth Date', 'birth_date', 'date', data?.birth_date);
    body += fieldText('Password (hash or plain to hash later)', 'password_hash', 'text', '');
    body += fieldCheckbox('Is Active', 'is_active', data?.is_active ?? 1);
  } else if (entity === 'destinations') {
    body += fieldText('Name', 'name', 'text', data?.name);
    body += fieldText('City', 'city', 'text', data?.city);
    body += fieldText('Country', 'country', 'text', data?.country);
    body += fieldSelect(
      'Category',
      'category',
      [
        { value: 'city', label: 'City' },
        { value: 'mountain', label: 'Mountain' },
        { value: 'forest', label: 'Forest' },
        { value: 'island', label: 'Island' }
      ],
      data?.category
    );
    body += fieldText('Image URL', 'image_url', 'url', data?.image_url);
    body += fieldTextarea('Short Description', 'short_desc', data?.short_desc);
    body += fieldTextarea('Long Description', 'long_desc', data?.long_desc);
    body += fieldText('Base Price (USD)', 'base_price', 'number', data?.base_price);
    body += fieldText('Currency', 'currency', 'text', data?.currency || 'USD');
    body += fieldCheckbox('Top Destination', 'is_top', data?.is_top);
    body += fieldCheckbox('Is Active', 'is_active', data?.is_active ?? 1);
  } else if (entity === 'hotels') {
    const destOptions = await getDestinationOptions();
    body += fieldSelect('Destination', 'destination_id', destOptions, data?.destination_id);
    body += fieldText('Name', 'name', 'text', data?.name);
    body += fieldText('Location Text (e.g., near center)', 'location_text', 'text', data?.location_text);
    body += fieldText('Rating (e.g., 4.5)', 'rating', 'number', data?.rating);
    body += fieldText('Reviews Count', 'reviews_count', 'number', data?.reviews_count);
    body += fieldText('Price per Night (USD)', 'price_per_night', 'number', data?.price_per_night);
    body += fieldText('Currency', 'currency', 'text', data?.currency || 'USD');
    body += fieldText('Discount %', 'discount_percent', 'number', data?.discount_percent);
    body += fieldTextarea('Description', 'description', data?.description);
    body += fieldCheckbox('Parking', 'has_parking', data?.has_parking);
    body += fieldCheckbox('Attached Bathroom', 'has_attached_bathroom', data?.has_attached_bathroom);
    body += fieldCheckbox('CCTV', 'has_cctv', data?.has_cctv);
    body += fieldCheckbox('WiFi', 'has_wifi', data?.has_wifi);
    body += fieldCheckbox('Sea View', 'has_sea_view', data?.has_sea_view);
    body += fieldCheckbox('City View', 'has_city_view', data?.has_city_view);
    body += fieldCheckbox('Free Breakfast', 'has_free_breakfast', data?.has_free_breakfast);
    body += fieldCheckbox('Pay at Hotel', 'pay_at_hotel', data?.pay_at_hotel);
    body += fieldCheckbox('Couple Friendly', 'couple_friendly', data?.couple_friendly);
    body += fieldCheckbox('Pet Friendly', 'pet_friendly', data?.pet_friendly);
    body += fieldCheckbox('Airport Shuttle', 'airport_shuttle', data?.airport_shuttle);
    body += fieldCheckbox('Is Active', 'is_active', data?.is_active ?? 1);
  } else if (entity === 'flights') {
    const destOptions = await getDestinationOptions();
    body += fieldSelect('Destination', 'destination_id', destOptions, data?.destination_id);
    body += fieldText('Airline Name', 'airline_name', 'text', data?.airline_name);
    body += fieldText('Airline Code', 'airline_code', 'text', data?.airline_code);
    body += fieldText('Flight Number', 'flight_number', 'text', data?.flight_number);
    body += fieldSelect(
      'Trip Type',
      'trip_type',
      [
        { value: 'oneway', label: 'One-way' },
        { value: 'roundtrip', label: 'Round-trip' }
      ],
      data?.trip_type
    );
    body += fieldText('Origin City', 'origin_city', 'text', data?.origin_city);
    body += fieldText('Origin Airport Code', 'origin_airport_code', 'text', data?.origin_airport_code);

    // Destination City – auto-filled from destination select
    body += fieldText(
      'Destination City',
      'destination_city',
      'text',
      data?.destination_city,
      'readonly'
    );

    body += fieldText('Destination Airport Code', 'destination_airport_code', 'text', data?.destination_airport_code);
    body += fieldText('Depart Date', 'depart_date', 'date', data?.depart_date);
    body += fieldText('Return Date (optional)', 'return_date', 'date', data?.return_date);
    body += fieldText('Departure Time', 'departure_time', 'time', data?.departure_time);
    body += fieldText('Arrival Time', 'arrival_time', 'time', data?.arrival_time);
    body += fieldText('Return Departure Time', 'return_departure_time', 'time', data?.return_departure_time);
    body += fieldText('Return Arrival Time', 'return_arrival_time', 'time', data?.return_arrival_time);
    body += fieldText('Duration (hours)', 'duration_hours', 'number', data?.duration_hours);
    body += fieldText('Stops Count', 'stops_count', 'number', data?.stops_count);
    body += fieldText('Fare Subtitle', 'fare_subtitle', 'text', data?.fare_subtitle);
    body += fieldTextarea('Extras (JSON/Text)', 'extras', data?.extras);
    body += fieldText('Base Price (USD)', 'base_price', 'number', data?.base_price);
    body += fieldText('Currency', 'currency', 'text', data?.currency || 'USD');
    body += fieldCheckbox('Is Active', 'is_active', data?.is_active ?? 1);
  } else if (entity === 'packages') {
    const destOptions = await getDestinationOptions();
    body += fieldSelect('Destination', 'destination_id', destOptions, data?.destination_id);
    body += fieldText('Title', 'title', 'text', data?.title);
    body += fieldText('Location Text', 'location', 'text', data?.location);
    body += fieldText('From City', 'from_city', 'text', data?.from_city);
    body += fieldText('Image URL', 'image_url', 'url', data?.image_url);
    body += fieldText('Badge Type', 'badge_type', 'text', data?.badge_type);
    body += fieldText('Price (USD)', 'price_usd', 'number', data?.price_usd);
    body += fieldText('Duration (days)', 'duration_days', 'number', data?.duration_days);
    body += fieldText('Rating', 'rating', 'number', data?.rating);
    body += fieldText('Reviews Count', 'reviews_count', 'number', data?.reviews_count);
    body += fieldSelect(
      'Category',
      'category',
      [
        { value: 'adventure', label: 'Adventure' },
        { value: 'beach', label: 'Beach' },
        { value: 'city', label: 'City' },
        { value: 'hiking', label: 'Hiking' },
        { value: 'museum', label: 'Museum' },
        { value: 'nature', label: 'Nature' },
        { value: 'relax', label: 'Relax' },
        { value: 'cultural', label: 'Cultural' }
      ],
      data?.category
    );
    body += fieldCheckbox('Featured', 'is_featured', data?.is_featured);
    body += fieldCheckbox('Is Active', 'is_active', data?.is_active ?? 1);
  } else if (entity === 'bookings') {
    const userOptions =
      (cache.users || []).map((u) => ({
        value: u.id,
        label:
          [u.first_name, u.last_name].filter(Boolean).join(' ') ||
          u.username ||
          u.email ||
          `User #${u.id}`
      })) || [];
    body += fieldSelect('User', 'user_id', userOptions, data?.user_id);
    body += fieldSelect(
      'Booking Type',
      'booking_type',
      [
        { value: 'flight', label: 'Flight' },
        { value: 'hotel', label: 'Hotel' },
        { value: 'package', label: 'Package' },
        { value: 'bundle', label: 'Bundle' }
      ],
      data?.booking_type
    );
    body += fieldText('Package ID (optional)', 'package_id', 'number', data?.package_id);
    body += fieldText('Booking Code', 'booking_code', 'text', data?.booking_code);
    body += fieldText('Trip Start Date', 'trip_start_date', 'date', data?.trip_start_date);
    body += fieldText('Trip End Date', 'trip_end_date', 'date', data?.trip_end_date);
    body += fieldText('Adults', 'travellers_adults', 'number', data?.travellers_adults || 1);
    body += fieldText('Children', 'travellers_children', 'number', data?.travellers_children || 0);
    body += fieldText('Infants', 'travellers_infants', 'number', data?.travellers_infants || 0);
    body += fieldText('Currency', 'currency', 'text', data?.currency || 'USD');
    body += fieldText('Amount Flight', 'amount_flight', 'number', data?.amount_flight);
    body += fieldText('Amount Hotel', 'amount_hotel', 'number', data?.amount_hotel);
    body += fieldText('Amount Package', 'amount_package', 'number', data?.amount_package);
    body += fieldText('Taxes', 'amount_taxes', 'number', data?.amount_taxes);
    body += fieldText('Discount Amount', 'discount_amount', 'number', data?.discount_amount);
    body += fieldText('Coupon Code', 'coupon_code', 'text', data?.coupon_code);
    body += fieldText('Total Amount', 'total_amount', 'number', data?.total_amount);
    body += fieldSelect(
      'Payment Method',
      'payment_method',
      [
        { value: '', label: '—' },
        { value: 'visa', label: 'Visa' },
        { value: 'mastercard', label: 'Mastercard' },
        { value: 'cashcard', label: 'Cashcard' }
      ],
      data?.payment_method
    );
    body += fieldSelect(
      'Payment Status',
      'payment_status',
      [
        { value: 'pending', label: 'Pending' },
        { value: 'paid', label: 'Paid' },
        { value: 'failed', label: 'Failed' },
        { value: 'refunded', label: 'Refunded' }
      ],
      data?.payment_status
    );
    body += fieldSelect(
      'Booking Status',
      'booking_status',
      [
        { value: 'pending', label: 'Pending' },
        { value: 'confirmed', label: 'Confirmed' },
        { value: 'cancelled', label: 'Cancelled' }
      ],
      data?.booking_status
    );
    body += fieldTextarea('Notes', 'notes', data?.notes);
  } else if (entity === 'payments') {
    body += fieldText('Booking ID', 'booking_id', 'number', data?.booking_id);
    body += fieldText('User ID', 'user_id', 'number', data?.user_id);
    body += fieldSelect(
      'Payment Method',
      'payment_method',
      [
        { value: 'visa', label: 'Visa' },
        { value: 'mastercard', label: 'Mastercard' },
        { value: 'cashcard', label: 'Cashcard' }
      ],
      data?.payment_method
    );
    body += fieldText('Amount Subtotal', 'amount_subtotal', 'number', data?.amount_subtotal);
    body += fieldText('Amount Tax', 'amount_tax', 'number', data?.amount_tax);
    body += fieldText('Amount Discount', 'amount_discount', 'number', data?.amount_discount);
    body += fieldText('Amount Total', 'amount_total', 'number', data?.amount_total);
    body += fieldText('Currency', 'currency', 'text', data?.currency || 'USD');
    body += fieldText('Promo Code ID', 'promo_code_id', 'number', data?.promo_code_id);
    body += fieldSelect(
      'Card Brand',
      'card_brand',
      [
        { value: '', label: '—' },
        { value: 'visa', label: 'Visa' },
        { value: 'mastercard', label: 'Mastercard' },
        { value: 'cashcard', label: 'Cashcard' }
      ],
      data?.card_brand
    );
    body += fieldText('Card Last4', 'card_last4', 'text', data?.card_last4);
    body += fieldText('Card Holder Name', 'card_holder_name', 'text', data?.card_holder_name);
    body += fieldText('Exp Month', 'exp_month', 'number', data?.exp_month);
    body += fieldText('Exp Year', 'exp_year', 'number', data?.exp_year);
    body += fieldCheckbox('Save Card', 'card_saved', data?.card_saved);
    body += fieldSelect(
      'Status',
      'status',
      [
        { value: 'pending', label: 'Pending' },
        { value: 'success', label: 'Success' },
        { value: 'failed', label: 'Failed' },
        { value: 'refunded', label: 'Refunded' }
      ],
      data?.status
    );
    body += fieldText('Gateway Reference', 'gateway_reference', 'text', data?.gateway_reference);
  }

  body += '</div>';

  document.getElementById('formModalTitle').textContent =
    (mode === 'add' ? 'Add ' : 'Edit ') +
    entity.charAt(0).toUpperCase() +
    entity.slice(1);

  const modalBody = document.getElementById('formModalBody');
  modalBody.innerHTML = body;

  // 🔗 تزامن Destination → Destination City في فورم الفلايتس
  if (entity === 'flights') {
    const destSelect = document.getElementById('destination_id');
    const destCityInput = document.getElementById('destination_city');

    if (destSelect && destCityInput) {
      const syncCity = () => {
        const opt = destSelect.options[destSelect.selectedIndex];
        const city = opt && opt.dataset.city ? opt.dataset.city : '';
        destCityInput.value = city;
      };

      // أول ما يفتح المودال
      syncCity();

      // كل ما نغيّر الـ Destination
      destSelect.addEventListener('change', syncCity);
    }
  }

  document.getElementById('formSubmitBtn').textContent =
    mode === 'add' ? 'Create' : 'Save';

  formModal.show();
}

// ===================== Click Handlers (Add/Edit/Delete) =====================

document.addEventListener('click', async (e) => {
  const btn = e.target.closest('button, a');
  if (!btn) return;
  const action = btn.dataset.action;
  const entity = btn.dataset.entity;
  const id = btn.dataset.id;

  if (!action || !entity) return;

  if (action === 'add') {
    e.preventDefault();
    await buildForm(entity, 'add', null);
    return;
  }

  if (action === 'edit') {
    e.preventDefault();
    let data = [];
    try {
      if (!cache[entity] || !cache[entity].length) {
        data = await apiList(entity);
      } else {
        data = cache[entity];
      }
    } catch (err) {
      console.error('fetch single entity for edit error', err);
      data = [];
    }
    const item = (data || []).find((x) => String(x.id) === String(id));
    await buildForm(entity, 'edit', item || { id });
    return;
  }

  if (['delete', 'cancel', 'refund'].includes(action)) {
    e.preventDefault();
    const label =
      action === 'delete'
        ? 'Delete this item?'
        : action === 'cancel'
        ? 'Cancel this booking?'
        : 'Refund this payment?';
    document.getElementById('confirmModalBody').textContent = label;
    document.getElementById('confirmYes').onclick = async () => {
      confirmModal.hide();
      try {
        if (action === 'delete') {
          await apiDelete(entity, id);
          alert('Deleted successfully.');
          location.reload();
        } else {
          // cancel/refund منسلمهم للباك اند لاحقًا
          alert(label + ' (handled by backend later)');
        }
      } catch (err) {
        console.error('action error', err);
        alert('Action failed. Check console / backend.');
      }
    };
    confirmModal.show();
    return;
  }
});

// ===================== Form Submit =====================

const BOOL_FIELDS = {
  admins: ['is_super', 'is_active'],
  users: ['is_active'],
  destinations: ['is_top', 'is_active'],
  hotels: [
    'has_parking',
    'has_attached_bathroom',
    'has_cctv',
    'has_wifi',
    'has_sea_view',
    'has_city_view',
    'has_free_breakfast',
    'pay_at_hotel',
    'couple_friendly',
    'pet_friendly',
    'airport_shuttle',
    'is_active'
  ],
  flights: ['is_active'],
  packages: ['is_featured', 'is_active'],
  bookings: [],
  payments: ['card_saved']
};

const INT_FIELDS = {
  admins: [],
  users: [],
  destinations: [],
  hotels: ['reviews_count', 'discount_percent', 'destination_id'],
  flights: ['destination_id', 'stops_count'],
  packages: ['destination_id', 'duration_days', 'reviews_count'],
  bookings: [
    'user_id',
    'package_id',
    'travellers_adults',
    'travellers_children',
    'travellers_infants'
  ],
  payments: ['booking_id', 'user_id', 'promo_code_id', 'exp_month', 'exp_year']
};

const FLOAT_FIELDS = {
  admins: [],
  users: [],
  destinations: ['base_price'],
  hotels: ['rating', 'price_per_night'],
  flights: ['duration_hours', 'base_price'],
  packages: ['price_usd', 'rating'],
  bookings: [
    'amount_flight',
    'amount_hotel',
    'amount_package',
    'amount_taxes',
    'discount_amount',
    'total_amount'
  ],
  payments: ['amount_subtotal', 'amount_tax', 'amount_discount', 'amount_total']
};

document.getElementById('entityForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  if (!CURRENT_ENTITY) return;

  const fd = new FormData(e.target);
  let payload = Object.fromEntries(fd.entries());

  // checkboxes
  (BOOL_FIELDS[CURRENT_ENTITY] || []).forEach((k) => {
    payload[k] = fd.get(k) ? 1 : 0;
  });

  // int
  (INT_FIELDS[CURRENT_ENTITY] || []).forEach((k) => {
    if (payload[k] !== undefined && payload[k] !== '') {
      payload[k] = parseInt(payload[k], 10);
      if (Number.isNaN(payload[k])) delete payload[k];
    }
  });

  // float
  (FLOAT_FIELDS[CURRENT_ENTITY] || []).forEach((k) => {
    if (payload[k] !== undefined && payload[k] !== '') {
      payload[k] = parseFloat(payload[k]);
      if (Number.isNaN(payload[k])) delete payload[k];
    }
  });

  try {
    if (CURRENT_MODE === 'add') {
      delete payload.id;
      await apiCreate(CURRENT_ENTITY, payload);
      alert('Created successfully.');
    } else {
      const id = payload.id || CURRENT_ID;
      await apiUpdate(CURRENT_ENTITY, id, payload);
      alert('Saved successfully.');
    }
    formModal.hide();
    location.reload();
  } catch (err) {
    console.error('save error', err);
    alert('Save failed. Check console / backend.');
  }
});
