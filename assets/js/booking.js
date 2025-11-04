
const $ = (q,root=document)=>root.querySelector(q);
const $$ = (q,root=document)=>Array.from(root.querySelectorAll(q));
const money = v => `$${Number(v).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}`;

function drawBarcode(canvas, text){
  const ctx = canvas.getContext('2d'); const w = canvas.width, h=canvas.height;
  ctx.clearRect(0,0,w,h); ctx.fillStyle='#111827';
  let seed = 0; for (let i=0;i<text.length;i++){ seed = (seed * 31 + text.charCodeAt(i)) >>> 0; }
  const bars = 80; const bw = Math.floor(w/(bars+10));
  const start = 5*bw;
  for(let i=0;i<bars;i++){ const on = ((seed >> (i%32)) & 1) === 1; const x = start + i*bw; const bh = on ? h-6 : (h/2); ctx.fillRect(x, h-bh, Math.max(1,bw-1), bh); }
}

const state = {
  type: 'flight',
  pricing: { base: 320, tax: 74, add: 0 }
};

function setType(type){
  state.type = type;

  $('#ticket-flight').classList.toggle('d-none', type!=='flight');
  $('#ticket-package').classList.toggle('d-none', type!=='package');
  $('#ticket-hotel').classList.toggle('d-none', type!=='hotel');

  $$('.selector .btn').forEach(b=> b.classList.toggle('active', b.dataset.type===type));

  if(type==='flight'){ state.pricing = {base:320, tax:74, add:0}; }
  if(type==='package'){ state.pricing = {base:1080, tax:139, add:30}; }
  if(type==='hotel'){ state.pricing = {base:380, tax:62, add:20}; }
  renderSummary();
}

function renderSummary(){
  const {base,tax,add} = state.pricing;
  const total = base+tax+add;
  $('#s-base').textContent = money(base);
  $('#s-tax').textContent  = money(tax);
  $('#s-add').textContent  = money(add);
  $('#s-total').textContent= money(total);

  if(state.type==='flight'){ $('#f-total').textContent = money(total); drawBarcode($('#f-barcode'), 'TRV-9H2K4'); }
  if(state.type==='package'){ $('#p-total').textContent = money(total); drawBarcode($('#p-barcode'), 'PKG-X7M32'); }
  if(state.type==='hotel'){ $('#h-total').textContent = money(total); drawBarcode($('#h-barcode'), 'HTL-4FQ92'); }
}

function fromQuery(){
  const q = new URLSearchParams(location.search);
  const type = q.get('type'); if(type) setType(type);

  if(q.get('from')) $('#f-from').textContent = q.get('from').toUpperCase();
  if(q.get('to')) $('#f-to').textContent = q.get('to').toUpperCase();
  if(q.get('airline')) $('#f-airline').textContent = q.get('airline');
  if(q.get('fn')) $('#f-flightno').textContent = `${q.get('fn')} · ${q.get('cabin')||'Economy'}`;
  if(q.get('pnr')) $('#f-pnr').textContent = q.get('pnr');
  if(q.get('name')) $('#f-passenger').textContent = q.get('name');
}

function updatePrintDate(){
  const el = document.getElementById('printDate');
  if(!el) return;
  const now = new Date();
  el.textContent = `Printed on ${now.toLocaleDateString()} ${now.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}`;
}
window.addEventListener('beforeprint', updatePrintDate);

function activeTicketData(){
  if(state.type==='flight'){
    return {
      title: 'Flight Receipt',
      line1: `${$('#f-from').textContent} → ${$('#f-to').textContent}`,
      line2: `${$('#f-airline').textContent} · ${$('#f-flightno').textContent}`,
      ref: ($('#f-pnr').textContent||'TRV-XXXX'),
      name: $('#f-passenger').textContent,
      total: $('#f-total').textContent
    };
  }
  if(state.type==='package'){
    return {
      title: 'Package Receipt',
      line1: $('#p-dest').textContent,
      line2: $('#p-includes').textContent,
      ref: ($('#p-voucher').textContent||'PKG-XXXX'),
      name: $('#p-guest').textContent,
      total: $('#p-total').textContent
    };
  }
  return {
    title: 'Hotel Receipt',
    line1: $('#h-hotel').textContent,
    line2: `${$('#h-city').textContent} · ${$('#h-room').textContent}`,
    ref: ($('#h-conf').textContent||'HTL-XXXX'),
    name: $('#h-guest').textContent,
    total: $('#h-total').textContent
  };
}

function downloadReceipt(){
  const d = activeTicketData();
  const c = document.createElement('canvas');
  const W = 900, H = 1200;
  c.width = W; c.height = H;
  const ctx = c.getContext('2d');

  ctx.fillStyle = '#ffffff'; ctx.fillRect(0,0,W,H);

  const grad = ctx.createLinearGradient(0,0,W,0);
  grad.addColorStop(0,'#6c63ff'); grad.addColorStop(1,'#8b5cf6');
  ctx.fillStyle = grad; ctx.fillRect(0,0,W,80);

  ctx.fillStyle = '#fff'; ctx.font = 'bold 28px "Plus Jakarta Sans", Arial'; ctx.fillText('Travelo', 24, 50);

  ctx.fillStyle = '#0f172a'; ctx.font = 'bold 32px "Plus Jakarta Sans", Arial'; ctx.fillText(d.title, 24, 140);

  ctx.fillStyle = '#f3f4f6'; ctx.fillRect(24,170,W-48, 210);
  ctx.strokeStyle = '#e5e7eb'; ctx.strokeRect(24,170,W-48,210);

  ctx.fillStyle = '#6b7280'; ctx.font = '600 14px "Plus Jakarta Sans", Arial';
  ctx.fillText('Reference', 40, 200);
  ctx.fillText('Name',      40, 240);
  ctx.fillText('Details',   40, 280);
  ctx.fillText('More',      40, 320);

  ctx.fillStyle = '#111827'; ctx.font = '700 20px "Plus Jakarta Sans", Arial';
  ctx.fillText(d.ref, 140, 200);
  ctx.fillText(d.name, 140, 240);
  ctx.fillText(d.line1, 140, 280);
  ctx.fillText(d.line2, 140, 320);

  ctx.fillStyle = '#6b7280'; ctx.font = '600 14px "Plus Jakarta Sans", Arial'; ctx.fillText('Total', 40, 390);
  ctx.fillStyle = '#111827'; ctx.font = '800 28px "Plus Jakarta Sans", Arial'; ctx.fillText(d.total, 100, 390);

  ctx.fillStyle = '#ffffff'; ctx.fillRect(24, 430, W-48, 140);
  ctx.strokeStyle = '#e5e7eb'; ctx.strokeRect(24, 430, W-48, 140);

  const bars = 120; const bw = Math.floor((W-48-60)/bars);
  let seed = 0; for(let i=0;i<d.ref.length;i++){ seed = (seed * 31 + d.ref.charCodeAt(i)) >>> 0; }
  const y = 450;
  ctx.fillStyle = '#111827';
  for(let i=0;i<bars;i++){ const on = ((seed >> (i%32)) & 1) === 1; const x = 54 + i*bw; const bh = on ? 110 : 60; ctx.fillRect(x, y + (110-bh), Math.max(1,bw-1), bh); }

  ctx.fillStyle = '#6b7280'; ctx.font = '600 14px "Plus Jakarta Sans", Arial';
  const now = new Date();
  const when = `${now.toLocaleDateString()} ${now.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}`;
  ctx.fillText(`Generated: ${when}`, 24, H-30);
  ctx.textAlign = 'right';
  ctx.fillText('Thank you for booking with Travelo', W-24, H-30);
  ctx.textAlign = 'left';

  const a = document.createElement('a');
  a.href = c.toDataURL('image/png');
  const prefix = state.type==='flight'?'TRV':(state.type==='package'?'PKG':'HTL');
  a.download = `${prefix}_receipt_${d.ref.replace(/\W+/g,'')}.png`;
  a.click();
}

document.addEventListener('DOMContentLoaded', ()=>{

  $$('.selector .btn').forEach(btn=> btn.addEventListener('click', ()=> setType(btn.dataset.type)));

  $('#printBtn').addEventListener('click', ()=> window.print());

  setType('flight');
  renderSummary();
  fromQuery();

  const receiptBtn = Array.from(document.querySelectorAll('.pay-actions .btn'))
    .find(b=> b.textContent.toLowerCase().includes('download receipt'));
  if(receiptBtn){ receiptBtn.addEventListener('click', downloadReceipt); }
});
