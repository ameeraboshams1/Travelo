
const API = { search: 'flights_search.php' }; // GET: from,to,depart,return,pax,cabin,page,pageSize
const PAGE_SIZE = 10;

const DEMO_MODE = true;
const DEMO_DATA = [
  { id:1, airline:'Royal Jordanian', code:'RJ', flight_no:'RJ261', from:'AMM', to:'DXB', depart:'2025-11-10T09:25:00', arrive:'2025-11-10T12:30:00', price:189, stops:0, duration:185, from_lat:31.722, from_lng:35.994, to_lat:25.253, to_lng:55.364, r_depart:'2025-11-17T18:10:00', r_arrive:'2025-11-17T21:05:00', r_price:205 },
  { id:2, airline:'Emirates', code:'EK', flight_no:'EK904', from:'AMM', to:'DXB', depart:'2025-11-10T17:10:00', arrive:'2025-11-10T20:15:00', price:228, stops:0, duration:185, from_lat:31.722, from_lng:35.994, to_lat:25.253, to_lng:55.364, r_depart:'2025-11-17T13:30:00', r_arrive:'2025-11-17T16:25:00', r_price:240 },
  { id:3, airline:'Turkish Airlines', code:'TK', flight_no:'TK815', from:'AMM', to:'IST', depart:'2025-11-10T05:30:00', arrive:'2025-11-10T08:05:00', price:159, stops:0, duration:155, from_lat:31.722, from_lng:35.994, to_lat:41.275, to_lng:28.751, r_depart:'2025-11-18T11:10:00', r_arrive:'2025-11-18T13:45:00', r_price:165 },
  { id:4, airline:'Qatar Airways', code:'QR', flight_no:'QR401', from:'AMM', to:'DOH', depart:'2025-11-10T21:40:00', arrive:'2025-11-11T00:15:00', price:172, stops:0, duration:155, from_lat:31.722, from_lng:35.994, to_lat:25.274, to_lng:51.608, r_depart:'2025-11-18T09:25:00', r_arrive:'2025-11-18T11:55:00', r_price:178 },
  { id:5, airline:'Egyptair', code:'MS', flight_no:'MS740', from:'AMM', to:'CAI', depart:'2025-11-10T14:30:00', arrive:'2025-11-10T16:00:00', price:130, stops:0, duration:90,  from_lat:31.722, from_lng:35.994, to_lat:30.112, to_lng:31.400, r_depart:'2025-11-16T19:15:00', r_arrive:'2025-11-16T20:45:00', r_price:135 },
  { id:6, airline:'Flydubai', code:'FZ', flight_no:'FZ144', from:'AMM', to:'DXB', depart:'2025-11-10T22:15:00', arrive:'2025-11-11T01:05:00', price:119, stops:0, duration:170, from_lat:31.722, from_lng:35.994, to_lat:25.253, to_lng:55.364, r_depart:'2025-11-17T10:40:00', r_arrive:'2025-11-17T13:20:00', r_price:129 }
];

const DEST_IMAGES = {
  'DXB': 'https://images.unsplash.com/photo-1504270997636-07ddfbd48945?q=80&w=1200&auto=format&fit=crop',
  'IST': 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?q=80&w=1200&auto=format&fit=crop',
  'DOH': 'https://images.unsplash.com/photo-1566550074878-0ac0fecc69e7?q=80&w=1200&auto=format&fit=crop',
  'CAI': 'https://images.unsplash.com/photo-1544989164-31dc3c645987?q=80&w=1200&auto=format&fit=crop',
  'AMM': 'https://images.unsplash.com/photo-1604328698692-cf6afe3f17dc?q=80&w=1200&auto=format&fit=crop',
  default: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=1200&auto=format&fit=crop'
};
const CITY_NAMES = { DXB:'Dubai, UAE', IST:'Istanbul, Türkiye', DOH:'Doha, Qatar', CAI:'Cairo, Egypt', AMM:'Amman, Jordan' };

const $ = (q,root=document)=>root.querySelector(q);
const $$ = (q,root=document)=>Array.from(root.querySelectorAll(q));
const fmtMoney = v => `$${Number(v).toFixed(2)}`;
const minutesToHm = m => `${Math.floor(m/60)}h ${m%60}m`;
const avatar = (name) => (name||'?').trim()[0]?.toUpperCase()||'T';
const showError = (msg)=>{ const b=$('#banner'); b.textContent=msg||'Something went wrong'; b.classList.remove('d-none'); setTimeout(()=>b.classList.add('d-none'), 4000); };

function totalPrice(f){ const isRound = document.documentElement.getAttribute('data-trip')==='round'; return isRound ? Number(f.price||0) + Number(f.r_price||0) : Number(f.price||0); }

let map, routeLayer;
function initMap(){
  map = L.map('map');
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ maxZoom:19, attribution:'&copy; OpenStreetMap' }).addTo(map);
  map.setView([30,20],3);
}
function drawRoute(f){
  if(!map) return; if(routeLayer){ routeLayer.remove(); }
  if([f.from_lat,f.from_lng,f.to_lat,f.to_lng].some(x=>x===undefined||x===null)) return;
  const A=[+f.from_lat,+f.from_lng], B=[+f.to_lat,+f.to_lng]; routeLayer=L.layerGroup();
  const m1=L.marker(A,{title:f.from}).addTo(routeLayer).bindPopup(`<b>${f.from}</b>`);
  const m2=L.marker(B,{title:f.to}).addTo(routeLayer).bindPopup(`<b>${f.to}</b>`);
  const line=L.polyline([A,B],{weight:4,opacity:.85}).addTo(routeLayer); routeLayer.addTo(map); map.fitBounds(line.getBounds(),{padding:[30,30]}); setTimeout(()=>m1.openPopup(),300);
}

function cardItem(f, idx, meta){
  const dep=new Date(f.depart), arr=new Date(f.arrive), stops=f.stops===0?'Nonstop':(f.stops===1?'1 stop':`${f.stops} stops`);
  const isRound = document.documentElement.getAttribute('data-trip')==='round';
  const total = totalPrice(f);
  const photo = (DEST_IMAGES[f.to]||DEST_IMAGES.default);
  const el=document.createElement('div'); el.className='flight-card';
  el.innerHTML = `
    <div class="flight-photo">
      <img src="${photo}" alt="${f.to}">
      ${isRound?'<span class="flight-badge">Round trip</span>':''}
      <span class="price-tag"><i class="bi bi-cash-coin"></i> ${fmtMoney(total)}</span>
    </div>
    <div class="flight-body">
      <div class="airline">
        <div class="logo">${avatar(f.airline)}</div>
        <div>
          <div class="fw-semibold">${f.airline} <span class="text-muted">${f.code||''}</span></div>
          <div class="text-muted small">${f.flight_no||''} · ${dep.toLocaleDateString()}</div>
        </div>
      </div>
      <div>
        <div class="fw-semibold">${f.from} <i class="bi bi-arrow-right"></i> ${f.to}</div>
        <div class="timeline mt-1"><span class="dot"></span><span class="line"></span><span class="dot"></span></div>
        <div class="text-muted small mt-1">${dep.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})} → ${arr.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})} · ${stops}${isRound?` · Return ${new Date(f.r_depart).toLocaleDateString()} ${new Date(f.r_depart).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}`:''}</div>
      </div>
      <div class="d-flex align-items-center justify-content-between gap-2">
        <div class="d-flex gap-1">
          ${idx===meta.bestIdx?'<span class="chip best">Best</span>':''}
          ${idx===meta.fastestIdx?'<span class="chip fast">Fastest</span>':''}
          ${idx===meta.cheapestIdx?'<span class="chip cheap">Cheapest</span>':''}
        </div>
        <div class="d-grid">
          <button class="btn btn-sm btn-outline-primary btn-details">Details</button>
          <button class="btn btn-primary btn-book mt-1">Book</button>
        </div>
      </div>
    </div>`;
  $('.btn-details', el).onclick = ()=> openDetails(f);
  $('.btn-book', el).onclick = ()=> proceedBooking(f);
  el.addEventListener('mouseenter', ()=> drawRoute(f));
  return el;
}

function badgeMeta(items){
  if(!items.length) return {cheapestIdx:-1, fastestIdx:-1, bestIdx:-1};
  let cheapestIdx=0, fastestIdx=0, bestIdx=0; // best = normalized price + duration
  const norm = (v, min, max)=> (max-min? (v-min)/(max-min) : 0);
  const minP=Math.min(...items.map(x=>x.price)), maxP=Math.max(...items.map(x=>x.price));
  const minD=Math.min(...items.map(x=>x.duration)), maxD=Math.max(...items.map(x=>x.duration));
  let bestScore=Infinity;
  items.forEach((x,i)=>{ if(x.price<items[cheapestIdx].price) cheapestIdx=i; if(x.duration<items[fastestIdx].duration) fastestIdx=i; const s = norm(x.price,minP,maxP)+norm(x.duration,minD,maxD); if(s<bestScore){ bestScore=s; bestIdx=i; } });
  return {cheapestIdx, fastestIdx, bestIdx};
}

function render(items){
  const cardsC=$('#cards');
  cardsC.innerHTML='';
  if(!items.length){ cardsC.innerHTML = `<div class='p-4 text-center text-muted'>No flights match your filters.</div>`; $('#resultCount').textContent='Showing 0 flights'; return; }
  const meta = badgeMeta(items);
  items.forEach((f,i)=>{ cardsC.appendChild(cardItem(f,i,meta)); });
  $('#resultCount').textContent = `Showing ${items.length} ${items.length===1?'flight':'flights'}`;
  if(items[0]) drawRoute(items[0]);
}

function openDetails(f){
  const isRound = document.documentElement.getAttribute('data-trip')==='round';
  const dep=new Date(f.depart), arr=new Date(f.arrive);
  let body = `
    <div class="d-flex justify-content-between">
      <div>
        <div class="fw-semibold">${f.from} → ${f.to}</div>
        <div class="text-muted">${dep.toLocaleDateString()} · ${dep.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})} → ${arr.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}</div>
        <div class="text-muted">Duration: ${minutesToHm(f.duration)} · Stops: ${f.stops||0}</div>
      </div>
      <div class="text-end">
        <div class="fw-bold fs-4">${fmtMoney(totalPrice(f))}</div>
        <div class="text-muted">${isRound?'Round trip':'One way'} · ${$('#cabin').value}</div>
      </div>
    </div>`;
  if(isRound){
    const rdep=new Date(f.r_depart), rarr=new Date(f.r_arrive);
    body += `
      <hr>
      <div class="d-flex justify-content-between">
        <div>
          <div class="fw-semibold">${f.to} → ${f.from}</div>
          <div class="text-muted">${rdep.toLocaleDateString()} · ${rdep.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})} → ${rarr.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}</div>
          <div class="text-muted">Return fare: ${fmtMoney(f.r_price||0)}</div>
        </div>
        <div class="text-end">
          <div class="fw-bold">Total: ${fmtMoney(totalPrice(f))}</div>
        </div>
      </div>`;
  }
  body += `
    <hr>
    <div class="row g-2">
      <div class="col-md-6"><div class="p-2 border rounded-3">Baggage: 7kg cabin · 23kg check-in</div></div>
      <div class="col-md-6"><div class="p-2 border rounded-3">Fare: Non-refundable · Date change fee</div></div>
    </div>`;
  $('#flightBody').innerHTML = body;
  new bootstrap.Modal('#flightModal').show();
  drawRoute(f);
  $('#continueBtn').onclick = ()=> proceedBooking(f);
}

function proceedBooking(f){
  const q = new URLSearchParams({ id:f.id, cabin:$('#cabin').value, pax:$('#pax').value });
  window.location.href = `checkout.html?${q.toString()}`;
}

let allItems=[];
function applyFilters(list){
  const stops=$('#fStops').value, airline=$('#fAirline').value.trim().toLowerCase(), price=Number($('#fPrice').value||0), dep=$('#fDep').value;
  return list.filter(f=>{ let ok=true;
    if(stops==='0') ok=ok&&f.stops===0; else if(stops==='1') ok=ok&&f.stops<=1; else if(stops==='>1') ok=ok&&f.stops>1;
    if(airline) ok=ok&&(f.airline.toLowerCase().includes(airline)||(f.code||'').toLowerCase().includes(airline));
    if(price>0) ok=ok&&f.price<=price;
    if(dep){ const h=new Date(f.depart).getHours(); if(dep==='morning') ok=ok&&(h>=5&&h<12); if(dep==='afternoon') ok=ok&&(h>=12&&h<17); if(dep==='evening') ok=ok&&(h>=17&&h<22); if(dep==='night') ok=ok&&(h>=22||h<5); }
    return ok; });
}
function sortBy(list){ const k=$('#sortBy').value; const a=[...list]; if(k==='price') a.sort((x,y)=>x.price-y.price); if(k==='duration') a.sort((x,y)=>x.duration-y.duration); if(k==='depart') a.sort((x,y)=>new Date(x.depart)-new Date(y.depart)); return a; }
function refresh(){ render(sortBy(applyFilters(allItems))); }
function handleChip(action){
  if(action==='cheapest'){ $('#sortBy').value='price'; }
  if(action==='fastest'){ $('#sortBy').value='duration'; }
  if(action==='morning'){ $('#fDep').value='morning'; }
  if(action==='evening'){ $('#fDep').value='evening'; }
  if(action==='nonstop'){ $('#fStops').value='0'; }
  refresh();
}

let page=1;
function params(){ return { from:$('#from').value.trim(), to:$('#to').value.trim(), depart:$('#depart').value, return:$('#return').value, pax:$('#pax').value, cabin:$('#cabin').value, page, pageSize:PAGE_SIZE }; }

async function search({append=false}={}){
  $('#banner').classList.add('d-none');
  if(!append){ $('#cards').innerHTML = `<div class='skeleton'></div><div class='skeleton'></div>`; page=1; }
  const p = params();
  try{
    let data;
    if(DEMO_MODE){
      await new Promise(r=>setTimeout(r, 350));
      const items = DEMO_DATA.filter(x=> (!p.from || x.from.includes(p.from.toUpperCase())) && (!p.to || x.to.includes(p.to.toUpperCase())));
      data = { items, hasMore:false };
    } else {
      const url = `${API.search}?${new URLSearchParams(p)}`;
      const res = await fetch(url, { headers:{'Accept':'application/json'} });
      if(!res.ok) throw new Error('HTTP '+res.status);
      data = await res.json();
    }
    const items = Array.isArray(data)? data : (data.items||[]);
    allItems = append? allItems.concat(items) : items;
    refresh();
    $('#loadMore').classList.toggle('d-none', !(data.hasMore));
  }catch(err){ showError('Failed to load flights'); $('#cards').innerHTML = `<div class='p-4 text-center text-muted'>Could not load flights.</div>`; }
}

async function loadMore(){ page += 1; await search({append:true}); }

function initTripTabs(){
  $$('.trip-tabs .btn').forEach(btn=> btn.onclick=()=>{
    $$('.trip-tabs .btn').forEach(x=>x.classList.remove('active'));
    btn.classList.add('active');
    const isRound = btn.dataset.trip==='round';
    $('#returnWrap').classList.toggle('d-none', !isRound);
    document.documentElement.setAttribute('data-trip', isRound? 'round':'oneway');
    refresh();
  });
}
function swap(){ const a=$('#from'), b=$('#to'); const t=a.value; a.value=b.value; b.value=t; }

document.addEventListener('DOMContentLoaded', ()=>{
  initMap(); initTripTabs();
  $('#from').value='AMM'; $('#to').value='DXB'; $('#depart').valueAsDate = new Date(Date.now()+86400000*7);

  $('#searchForm').addEventListener('submit', (e)=>{ e.preventDefault(); search(); });
  $('#swap').addEventListener('click', swap);

  ['fStops','fAirline','fPrice','fDep','sortBy'].forEach(id=> $('#'+id).addEventListener('input', refresh));
  $('#clearFilters').addEventListener('click', ()=>{ ['fStops','fAirline','fPrice','fDep'].forEach(id=> $('#'+id).value=''); refresh(); });

  $$('#quickChips [data-chip]').forEach(btn=> btn.addEventListener('click', ()=> handleChip(btn.dataset.chip)));

  $('#refreshBtn').addEventListener('click', ()=> search());
  $('#loadMore').addEventListener('click', loadMore);

  document.getElementById('refineBtn').addEventListener('click', ()=>{
    document.querySelector('.filters').scrollIntoView({behavior:'smooth', block:'start'});
  });

  search();
});
