const slidesNode = document.getElementById('slides');
const imgs = Array.from(slidesNode.querySelectorAll('img'));
const indicators = document.getElementById('indicators');
const email = document.getElementById('email');
const pass = document.getElementById('password');
const toggle = document.getElementById('togglePass');
const form = document.getElementById('loginForm');
const toast = document.getElementById('toast');
const emailErr = document.getElementById('emailErr');
const passErr = document.getElementById('passErr');
const remember = document.getElementById('remember');
const submitBtn = document.getElementById('submitBtn');
const card = document.getElementById('card');

let current = 0, timer = null, hover = false, visible = true, swipeStartX = null;
const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

imgs.forEach((_, i) => { const b = document.createElement('button'); if (i === 0) b.classList.add('active'); b.addEventListener('click', () => show(i, true)); indicators.appendChild(b) });
const dots = Array.from(indicators.querySelectorAll('button'));

function show(n, manual) { current = (n + imgs.length) % imgs.length; imgs.forEach((im, i) => im.classList.toggle('active', i === current)); dots.forEach((d, i) => d.classList.toggle('active', i === current)); if (manual) restart() }
function next() { show(current + 1, false) }
function start() { if (REDUCED || hover || !visible) return; stop(); timer = setInterval(next, 5200) }
function stop() { if (timer) { clearInterval(timer); timer = null } }
slidesNode.addEventListener('mouseenter', () => { hover = true; stop() });
slidesNode.addEventListener('mouseleave', () => { hover = false; start() });
slidesNode.addEventListener('touchstart', e => { swipeStartX = e.touches[0].clientX; stop() }, { passive: true });
slidesNode.addEventListener('touchmove', e => { if (swipeStartX === null) return; const dx = e.touches[0].clientX - swipeStartX; if (Math.abs(dx) > 60) { show(current + (dx < 0 ? 1 : -1), true); swipeStartX = null } }, { passive: true });
slidesNode.addEventListener('touchend', () => { swipeStartX = null; start() }, { passive: true });
document.addEventListener('visibilitychange', () => { visible = !document.hidden; visible ? start() : stop() });
document.addEventListener('keydown', e => { if (e.key === 'ArrowLeft') show(current - 1, true); if (e.key === 'ArrowRight') show(current + 1, true) });
imgs[0].classList.add('active'); start();

function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms) } }
function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) }
function isUsername(v) { return /^[a-zA-Z0-9_.-]{3,}$/.test(v) }
function setError(node, msg, input) { node.textContent = msg || ''; if (input) { input.classList.toggle('invalid', !!msg) } }
function enableSubmit() { const ok = !emailErr.textContent && !passErr.textContent && email.value.trim() && pass.value.trim().length >= 6; submitBtn.disabled = !ok }

const validateEmail = debounce(() => { const v = email.value.trim(); if (!v) { setError(emailErr, 'Enter email or username', email) } else if (v.includes('@')) { setError(emailErr, isEmail(v) ? '' : 'Enter a valid email', email) } else { setError(emailErr, isUsername(v) ? '' : 'Enter a valid username', email) } enableSubmit() }, 140);
const validatePass = debounce(() => { const v = pass.value; if (!v) { setError(passErr, 'Enter your password', pass) } else if (v.length < 6) { setError(passErr, 'Password must be at least 6 characters', pass) } else { setError(passErr, '', pass) } enableSubmit() }, 120);

email.addEventListener('input', validateEmail);
pass.addEventListener('input', validatePass);
pass.addEventListener('keydown', e => { document.getElementById('capsNote').textContent = e.getModifierState && e.getModifierState('CapsLock') ? 'Caps Lock is ON' : '' });
pass.addEventListener('keyup', e => { document.getElementById('capsNote').textContent = e.getModifierState && e.getModifierState('CapsLock') ? 'Caps Lock is ON' : '' });

toggle.addEventListener('mousedown', () => { pass.type = 'text'; toggle.classList.add('show'); toggle.setAttribute('aria-pressed', 'true') });
toggle.addEventListener('mouseup', () => { pass.type = 'password'; toggle.classList.remove('show'); toggle.setAttribute('aria-pressed', 'false') });
toggle.addEventListener('mouseleave', () => { if (pass.type === 'text') { pass.type = 'password'; toggle.classList.remove('show'); toggle.setAttribute('aria-pressed', 'false') } });

if (localStorage.getItem('travelo_remember') === '1') { email.value = localStorage.getItem('travelo_email') || ''; document.getElementById('remember').checked = true; validateEmail() }

function toastShow(msg, ok = true) { toast.textContent = msg; toast.style.background = ok ? '#16a34a' : '#ef4444'; toast.classList.add('show'); setTimeout(() => toast.classList.remove('show'), 1800) }

form.addEventListener('submit', e => {
    e.preventDefault();
    validateEmail(); validatePass();
    if (submitBtn.disabled) {
        form.classList.add('shake'); setTimeout(() => form.classList.remove('shake'), 380);
        if (navigator.vibrate) navigator.vibrate(60);
        toastShow('Please fix the errors', false); return
    }
    if (remember.checked) { localStorage.setItem('travelo_remember', '1'); localStorage.setItem('travelo_email', email.value.trim()) }
    else { localStorage.removeItem('travelo_remember'); localStorage.removeItem('travelo_email') }
    submitBtn.disabled = true; toastShow('Logging in...')
    setTimeout(() => { toastShow('Logged in successfully ✅'); card.style.transition = 'transform .5s cubic-bezier(.2,.9,.3,1), box-shadow .5s'; card.style.transform = 'translateY(-14px)'; card.style.boxShadow = '0 36px 80px rgba(135,43,255,.16)' }, 900)
});

document.getElementById('helpCenter').addEventListener('click', () => toastShow('Opening Help Center...'));
document.getElementById('backHome').addEventListener('click', () => toastShow('Going back to homepage...'));

let raf = 0;
const tilt = (e) => {
    const rect = card.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const y = (e.clientY - rect.top) / rect.height;
    const rx = (0.5 - y) * 8;
    const ry = (x - 0.5) * 10;
    card.style.transform = `rotateX(${rx}deg) rotateY(${ry}deg) translateZ(0)`;
};
card.addEventListener('mousemove', e => {
    if (REDUCED) return;
    cancelAnimationFrame(raf);
    raf = requestAnimationFrame(() => tilt(e));
});
card.addEventListener('mouseleave', () => {
    card.style.transform = 'translateZ(0)';
});
document.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        const a = [email, pass];
        const i = a.indexOf(document.activeElement);
        if (i > -1 && i < a.length - 1) { a[i + 1].focus(); e.preventDefault(); }
    }
});

imgs.forEach(i => { const im = new Image(); im.src = i.src });
