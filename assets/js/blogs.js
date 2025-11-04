const POSTS = [
  {
    id:"post-01",
    title:"The Ultimate Carry‑On Guide for Stress‑Free Flights",
    date:"2025-02-09",
    minutes:7,
    cover:"https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=60",
    tags:["baggage","packing","flights"],
    excerpt:"What to pack, what to skip, and how to breeze through security with only a carry‑on.",
    href:"post1.html"
  },
  {
    id:"post-02",
    title:"How to Find Fair Flight Prices (Without Spending Hours)",
    date:"2025-03-14",
    minutes:6,
    cover:"https://images.unsplash.com/photo-1502920917128-1aa500764ce7?auto=format&fit=crop&w=1600&q=60",
    tags:["flights","deals","search"],
    excerpt:"Smart timing, alerts, and flexible routes—how Travelo helps you lock the best fares.",
    href:"post2.html"
  },
  {
    id:"post-03",
    title:"Visa Basics: Documents You Actually Need",
    date:"2025-01-22",
    minutes:8,
    cover:"https://images.unsplash.com/photo-1460485319191-c2154f3b0a42?auto=format&fit=crop&w=1600&q=60",
    tags:["visa","documents","policies"],
    excerpt:"Cut through the noise—here’s the essential checklist we recommend before you fly.",
    href:"post3.html"
  },
  {
    id:"post-04",
    title:"Hotel Hacks: Upgrades, Late Check‑outs, and Perks",
    date:"2025-05-05",
    minutes:9,
    cover:"https://images.unsplash.com/photo-1501117716987-c8e8d3c6a6a3?auto=format&fit=crop&w=1600&q=60",
    tags:["hotels","perks","tips"],
    excerpt:"Get more from every stay: the small asks that add up to big comfort.",
    href:"post4.html"
  },
  {
    id:"post-05",
    title:"Family Travel: Flying with Infants Without the Meltdown",
    date:"2024-12-11",
    minutes:10,
    cover:"https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?auto=format&fit=crop&w=1600&q=60",
    tags:["family","infant","policies"],
    excerpt:"Seat choices, stroller tips, and how to prep bottles for security.",
    href:"post5.html"
  },
  {
    id:"post-06",
    title:"Refunds & Changes: How Travelo Keeps It Simple",
    date:"2025-04-02",
    minutes:6,
    cover:"https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=1600&q=60",
    tags:["refund","changes","support"],
    excerpt:"Understand fare rules in plain English so you can change plans confidently.",
    href:"post6.html"
  },
  {
    id:"post-07",
    title:"Seat Selection 101: What’s Worth Paying For",
    date:"2025-06-18",
    minutes:5,
    cover:"https://images.unsplash.com/photo-1502164980785-f8aa41d53611?auto=format&fit=crop&w=1600&q=60",
    tags:["seats","flights"],
    excerpt:"Legroom, windows, and quiet zones: where to sit for your type of trip.",
    href:"post7.html"
  },
  {
    id:"post-08",
    title:"Travel Safety: Smarter Passwords & 2FA on the Road",
    date:"2025-07-09",
    minutes:5,
    cover:"https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1600&q=60",
    tags:["security","account","tips"],
    excerpt:"Simple practices that protect your bookings and inbox while traveling.",
    href:"post8.html"
  },
  {
    id:"post-09",
    title:"Sports Gear & Instruments: Fly Without Headaches",
    date:"2025-03-03",
    minutes:7,
    cover:"https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1600&q=60",
    tags:["sports","baggage","policies"],
    excerpt:"Fees, dimensions, and the one call you should make before airport day.",
    href:"post9.html"
  },
  {
    id:"post-10",
    title:"City Weekend: 48 Hours That Feel Like a Week",
    date:"2025-08-21",
    minutes:6,
    cover:"https://images.unsplash.com/photo-1491555103944-7c647fd857e6?auto=format&fit=crop&w=1600&q=60",
    tags:["weekend","planning","deals"],
    excerpt:"Itinerary templates and booking moves that squeeze the most from two days.",
    href:"post10.html"
  },
  {
    id:"post-11",
    title:"Hotel Breakfasts: When to Skip and When to Splurge",
    date:"2025-05-29",
    minutes:4,
    cover:"https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1600&q=60",
    tags:["hotels","food","tips"],
    excerpt:"Buffets vs. local cafés: value, time, and satisfaction compared.",
    href:"post11.html"
  },
  {
    id:"post-12",
    title:"Multi‑City Magic: See More on the Same Ticket",
    date:"2025-09-10",
    minutes:8,
    cover:"https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=60",
    tags:["multi-city","routing","strategy"],
    excerpt:"Build routes like a pro: surface hidden gems and save money.",
    href:"post12.html"
  }
];

const TAGS = Array.from(new Set(POSTS.flatMap(p=>p.tags))).sort();
const PER_PAGE = 12; // all visible; change to 6 for pagination demo

const grid = document.getElementById('grid');
const pager = document.getElementById('pager');
const tagStrip = document.getElementById('tagStrip');
const yearSpan = document.getElementById('year');
if(yearSpan) yearSpan.textContent = new Date().getFullYear();

let uiState = { q:"", tag:"All", page:1 };

function renderTags(){
  if(!tagStrip) return;
  tagStrip.innerHTML = '';
  const allBtn = document.createElement('button');
  allBtn.className = 'filter is-active';
  allBtn.textContent = 'All';
  allBtn.addEventListener('click', ()=>selectTag('All', allBtn));
  tagStrip.appendChild(allBtn);
  TAGS.forEach(t=>{
    const b = document.createElement('button');
    b.className = 'filter'; b.textContent = `#${t}`;
    b.addEventListener('click', ()=>selectTag(t, b));
    tagStrip.appendChild(b);
  });
}
function selectTag(tag, btn){
  uiState.tag = tag; uiState.page = 1;
  tagStrip.querySelectorAll('.filter').forEach(el=>el.classList.remove('is-active'));
  btn.classList.add('is-active');
  renderGrid();
}

function filterPosts(){
  const q = uiState.q.trim().toLowerCase();
  return POSTS.filter(p=>{
    const inTag = uiState.tag==='All' || p.tags.includes(uiState.tag);
    const inText = !q || (p.title + ' ' + p.excerpt + ' ' + p.tags.join(' ')).toLowerCase().includes(q);
    return inTag && inText;
  });
}

function renderGrid(){
  if(!grid) return;
  const items = filterPosts();
  grid.innerHTML = '';

  items.forEach(p=>{
    const card = document.createElement('article');
    card.className = 'card';
    card.innerHTML = `
      <img class="card-thumb" src="${p.cover}" alt="${p.title}">
      <div class="card-body">
        <div class="card-meta"><span>${new Date(p.date).toLocaleDateString()}</span> • <span>${p.minutes} min read</span></div>
        <h3 class="card-title">${p.title}</h3>
        <p class="card-excerpt">${p.excerpt}</p>
        <div class="tags-line">${p.tags.map(t=>`<span class="tag">#${t}</span>`).join('')}</div>
      </div>
      <div class="card-foot">
        <button class="readmore" data-href="${p.href}">Read more</button>
        <a href="${p.href}">Open</a>
      </div>`;
    card.querySelector('.readmore').addEventListener('click', ()=>{
      location.href = p.href;
    });
    grid.appendChild(card);
  });

  pager && (pager.style.display = items.length > PER_PAGE ? 'flex' : 'none');
}

const searchInput = document.getElementById('searchInput');
const clearSearch = document.getElementById('clearSearch');
const onSearch = () => { uiState.q = searchInput.value; clearSearch.classList.toggle('show', !!uiState.q); renderGrid(); };
searchInput?.addEventListener('input', onSearch);
clearSearch?.addEventListener('click', ()=>{ searchInput.value=''; uiState.q=''; clearSearch.classList.remove('show'); renderGrid(); });

renderTags();
renderGrid();
