// ================== SLIDER ==================
const slidesNode = document.getElementById('slides');
const indicators = document.getElementById('indicators');
const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

let imgs = [];
let dots = [];
let current = 0;
let timer = null;
let hover = false;
let visible = true;
let swipeStartX = null;

if (slidesNode && indicators) {
  imgs = Array.from(slidesNode.querySelectorAll('img'));

  imgs.forEach((_, i) => {
    const b = document.createElement('button');
    if (i === 0) b.classList.add('active');
    b.addEventListener('click', () => showSlide(i, true));
    indicators.appendChild(b);
  });

  dots = Array.from(indicators.querySelectorAll('button'));

  function showSlide(n, manual) {
    current = (n + imgs.length) % imgs.length;

    imgs.forEach((im, i) =>
      im.classList.toggle('active', i === current)
    );
    dots.forEach((d, i) =>
      d.classList.toggle('active', i === current)
    );

    if (manual) restartSlider();
  }

  function nextSlide() {
    showSlide(current + 1, false);
  }

  function startSlider() {
    if (REDUCED || hover || !visible) return;
    stopSlider();
    timer = setInterval(nextSlide, 5200);
  }

  function stopSlider() {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  function restartSlider() {
    stopSlider();
    startSlider();
  }

  slidesNode.addEventListener('mouseenter', () => {
    hover = true;
    stopSlider();
  });

  slidesNode.addEventListener('mouseleave', () => {
    hover = false;
    startSlider();
  });

  slidesNode.addEventListener(
    'touchstart',
    e => {
      swipeStartX = e.touches[0].clientX;
      stopSlider();
    },
    { passive: true }
  );

  slidesNode.addEventListener(
    'touchmove',
    e => {
      if (swipeStartX === null) return;
      const dx = e.touches[0].clientX - swipeStartX;
      if (Math.abs(dx) > 60) {
        showSlide(current + (dx < 0 ? 1 : -1), true);
        swipeStartX = null;
      }
    },
    { passive: true }
  );

  slidesNode.addEventListener('touchend', () => {
    swipeStartX = null;
    startSlider();
  });

  document.addEventListener('visibilitychange', () => {
    visible = !document.hidden;
    visible ? startSlider() : stopSlider();
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft') showSlide(current - 1, true);
    if (e.key === 'ArrowRight') showSlide(current + 1, true);
  });

  if (imgs.length > 0) {
    imgs[0].classList.add('active');
    startSlider();
  }

  // Preload للصور
  imgs.forEach(i => {
    const im = new Image();
    im.src = i.src;
  });
} else {
  console.warn('No #slides slider on this page.');
}

// ================== FORM & VALIDATION ==================
const email = document.getElementById('email');
const pass = document.getElementById('password');
const showPass = document.getElementById('remember'); // نفس الـ id في HTML
const form = document.getElementById('loginForm');
const toast = document.getElementById('toast');
const emailErr = document.getElementById('emailErr');
const passErr = document.getElementById('passErr');
const submitBtn = document.getElementById('submitBtn');
const card = document.getElementById('card');

function debounce(fn, ms) {
  let t;
  return (...a) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...a), ms);
  };
}

function isEmail(v) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
}

function isUsername(v) {
  return /^[a-zA-Z0-9_.-]{3,}$/.test(v);
}

function setError(node, msg, input) {
  node.textContent = msg || '';
  if (input) {
    input.classList.toggle('invalid', !!msg);
  }
}

function enableSubmit() {
  const ok =
    !emailErr.textContent &&
    !passErr.textContent &&
    email.value.trim() &&
    pass.value.trim().length >= 6;
  submitBtn.disabled = !ok;
}

const validateEmail = debounce(() => {
  const v = email.value.trim();
  if (!v) {
    setError(emailErr, 'Enter email or username', email);
  } else if (v.includes('@')) {
    setError(emailErr, isEmail(v) ? '' : 'Enter a valid email', email);
  } else {
    setError(
      emailErr,
      isUsername(v) ? '' : 'Enter a valid username',
      email
    );
  }
  enableSubmit();
}, 140);

const validatePass = debounce(() => {
  const v = pass.value;
  if (!v) {
    setError(passErr, 'Enter your password', pass);
  } else if (v.length < 6) {
    setError(
      passErr,
      'Password must be at least 6 characters',
      pass
    );
  } else {
    setError(passErr, '', pass);
  }
  enableSubmit();
}, 120);

email.addEventListener('input', validateEmail);
pass.addEventListener('input', validatePass);

// ملاحظة الكابس لوك
pass.addEventListener('keydown', e => {
  const note = document.getElementById('capsNote');
  if (!note) return;
  note.textContent =
    e.getModifierState && e.getModifierState('CapsLock')
      ? 'Caps Lock is ON'
      : '';
});

pass.addEventListener('keyup', e => {
  const note = document.getElementById('capsNote');
  if (!note) return;
  note.textContent =
    e.getModifierState && e.getModifierState('CapsLock')
      ? 'Caps Lock is ON'
      : '';
});


if (showPass) {
  showPass.addEventListener('change', () => {
    const isChecked = showPass.checked;
    pass.type = isChecked ? 'text' : 'password';
  });
}

function toastShow(msg, ok = true) {
  toast.textContent = msg;
  toast.style.background = ok ? '#16a34a' : '#ef4444';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 1800);
}

form.addEventListener('submit', e => {
  e.preventDefault();
  validateEmail();
  validatePass();

  if (submitBtn.disabled) {
    form.classList.add('shake');
    setTimeout(() => form.classList.remove('shake'), 380);
    if (navigator.vibrate) navigator.vibrate(60);
    toastShow('Please fix the errors', false);
    return;
  }

  submitBtn.disabled = true;
  toastShow('Logging in...');

  setTimeout(() => {
    toastShow('Logged in successfully ✅');
    card.style.transition =
      'transform .5s cubic-bezier(.2,.9,.3,1), box-shadow .5s';
    card.style.transform = 'translateY(-14px)';
    card.style.boxShadow = '0 36px 80px rgba(135,43,255,.16)';
  }, 900);
});


let raf = 0;
const tilt = e => {
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

// Enter ينقل من الإيميل للباسورد
document.addEventListener('keydown', e => {
  if (e.key === 'Enter') {
    const a = [email, pass];
    const i = a.indexOf(document.activeElement);
    if (i > -1 && i < a.length - 1) {
      a[i + 1].focus();
      e.preventDefault();
    }
  }
});

// =============== Help Center + Back Home ===============
document.addEventListener('DOMContentLoaded', function () {
  const helpCenterBtn = document.getElementById('helpCenter');
  const backHomeBtn = document.getElementById('backHome');

  if (helpCenterBtn) {
    helpCenterBtn.addEventListener('click', () => {
      toastShow('Opening Help Center...');
    });
  }

  if (backHomeBtn) {
    backHomeBtn.addEventListener('click', function () {
      window.location.href = 'index.html'; // من login.html إلى الصفحة الرئيسية
    });
  }
});
