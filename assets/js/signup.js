// ============= DOM REFERENCES =============
const slidesNode        = document.getElementById('slides');
const imgs              = Array.from(slidesNode.querySelectorAll('img'));
const indicators        = document.getElementById('indicators');

const firstName          = document.getElementById('firstName');
const lastName          = document.getElementById('lastName');
const email             = document.getElementById('email');
const username          = document.getElementById('username');
const password          = document.getElementById('password');
const confirmPassword   = document.getElementById('confirmPassword');

const togglePass        = document.getElementById('togglePass');
const toggleConfirmPass = document.getElementById('toggleConfirmPass');

const form              = document.getElementById('signupForm');
const toast             = document.getElementById('toast');

const firstnameErr      = document.getElementById('firstNameErr');
const lastnameErr       = document.getElementById('lastNameErr');
const emailErr          = document.getElementById('emailErr');
const usernameErr       = document.getElementById('usernameErr');
const passErr           = document.getElementById('passErr');
const confirmPassErr    = document.getElementById('confirmPassErr');

const terms             = document.getElementById('terms');
const submitBtn         = document.getElementById('submitBtn');
const card              = document.getElementById('card');

let current = 0, timer = null, hover = false, visible = true, swipeStartX = null;
const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// ============= SLIDER =============
imgs.forEach((_, i) => {
  const b = document.createElement('button');
  if (i === 0) b.classList.add('active');
  b.addEventListener('click', () => show(i, true));
  indicators.appendChild(b);
});
const dots = Array.from(indicators.querySelectorAll('button'));

function show(n, manual) {
  current = (n + imgs.length) % imgs.length;
  imgs.forEach((im, i) => im.classList.toggle('active', i === current));
  dots.forEach((d, i) => d.classList.toggle('active', i === current));
  if (manual) restart();
}
function next()         { show(current + 1, false); }
function start()        { if (REDUCED || hover || !visible) return; stop(); timer = setInterval(next, 5200); }
function stop()         { if (timer) { clearInterval(timer); timer = null; } }
function restart()      { stop(); start(); }

slidesNode.addEventListener('mouseenter', () => { hover = true;  stop(); });
slidesNode.addEventListener('mouseleave', () => { hover = false; start(); });
slidesNode.addEventListener('touchstart', e => { swipeStartX = e.touches[0].clientX; stop(); }, { passive: true });
slidesNode.addEventListener('touchmove',  e => {
  if (swipeStartX === null) return;
  const dx = e.touches[0].clientX - swipeStartX;
  if (Math.abs(dx) > 60) {
    show(current + (dx < 0 ? 1 : -1), true);
    swipeStartX = null;
  }
}, { passive: true });
slidesNode.addEventListener('touchend', () => { swipeStartX = null; start(); }, { passive: true });
document.addEventListener('visibilitychange', () => { visible = !document.hidden; visible ? start() : stop(); });
document.addEventListener('keydown', e => {
  if (e.key === 'ArrowLeft')  show(current - 1, true);
  if (e.key === 'ArrowRight') show(current + 1, true);
});

imgs[0].classList.add('active');
start();

// ============= HELPERS =============
function isEmail(v) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
}
function isUsername(v) {
  return /^[a-zA-Z0-9_.-]{3,}$/.test(v);
}
function setError(node, msg, input) {
  node.textContent = msg || '';
  if (input) input.classList.toggle('invalid', !!msg);
}

function enableSubmit() {
  const agreed = terms ? terms.checked : false;

  const ok =
    !firstnameErr.textContent &&
    !lastnameErr.textContent &&
    !emailErr.textContent &&
    !usernameErr.textContent &&
    !passErr.textContent &&
    !confirmPassErr.textContent &&
    firstName.value.trim() &&
    lastName.value.trim() &&
    email.value.trim() &&
    username.value.trim() &&
    password.value.length >= 6 &&
    confirmPassword.value &&
    agreed;

  submitBtn.disabled = !ok;
}

// ============= VALIDATION =============
function validatefirstName() {
  const v = firstName.value.trim();
  if (!v) {
    setError(firstnameErr, 'Please enter your first name', firstName);
  } else if (v.length < 2) {
    setError(firstnameErr, 'Name must be at least 2 characters', firstName);
  } else {
    setError(firstnameErr, '', firstName);
  }
  enableSubmit();
}

function validatelastName() {
  const v = lastName.value.trim();
  if (!v) {
    setError(lastnameErr, 'Please enter your last name', lastName);
  } else if (v.length < 2) {
    setError(lastnameErr, 'Name must be at least 2 characters', lastName);
  } else {
    setError(lastnameErr, '', lastName);
  }
  enableSubmit();
}

function validateEmail() {
  const v = email.value.trim();
  if (!v) {
    setError(emailErr, 'Please enter your email', email);
  } else if (!isEmail(v)) {
    setError(emailErr, 'Please enter a valid email address', email);
  } else {
    setError(emailErr, '', email);
  }
  enableSubmit();
}

function validateUsername() {
  const v = username.value.trim();
  if (!v) {
    setError(usernameErr, 'Please choose a username', username);
  } else if (!isUsername(v)) {
    setError(
      usernameErr,
      'Username must be at least 3 characters and contain only letters, numbers, dots, dashes, or underscores',
      username
    );
  } else {
    setError(usernameErr, '', username);
  }
  enableSubmit();
}

function validatePassword() {
  const v = password.value;
  if (!v) {
    setError(passErr, 'Please create a password', password);
  } else if (v.length < 6) {
    setError(passErr, 'Password must be at least 6 characters', password);
  } else {
    setError(passErr, '', password);
  }
  validateConfirmPassword();
  enableSubmit();
}

function validateConfirmPassword() {
  const v = confirmPassword.value;
  if (!v) {
    setError(confirmPassErr, 'Please confirm your password', confirmPassword);
  } else if (v !== password.value) {
    setError(confirmPassErr, 'Passwords do not match', confirmPassword);
  } else {
    setError(confirmPassErr, '', confirmPassword);
  }
  enableSubmit();
}

// ============= FIELD EVENTS =============
firstName.addEventListener('input', validatefirstName);
lastName.addEventListener('input', validatelastName);
email.addEventListener('input', validateEmail);
username.addEventListener('input', validateUsername);
password.addEventListener('input', validatePassword);
confirmPassword.addEventListener('input', validateConfirmPassword);

// Caps Lock message
password.addEventListener('keydown', e => {
  const note = document.getElementById('capsNote');
  if (!note) return;
  note.textContent =
    e.getModifierState && e.getModifierState('CapsLock')
      ? 'Caps Lock is ON'
      : '';
});
password.addEventListener('keyup', e => {
  const note = document.getElementById('capsNote');
  if (!note) return;
  note.textContent =
    e.getModifierState && e.getModifierState('CapsLock')
      ? 'Caps Lock is ON'
      : '';
});

// show / hide password
if (togglePass) {
  togglePass.addEventListener('mousedown', () => {
    password.type = 'text';
    togglePass.classList.add('show');
    togglePass.setAttribute('aria-pressed', 'true');
  });
  togglePass.addEventListener('mouseup', () => {
    password.type = 'password';
    togglePass.classList.remove('show');
    togglePass.setAttribute('aria-pressed', 'false');
  });
  togglePass.addEventListener('mouseleave', () => {
    if (password.type === 'text') {
      password.type = 'password';
      togglePass.classList.remove('show');
      togglePass.setAttribute('aria-pressed', 'false');
    }
  });
}

if (toggleConfirmPass) {
  toggleConfirmPass.addEventListener('mousedown', () => {
    confirmPassword.type = 'text';
    toggleConfirmPass.classList.add('show');
    toggleConfirmPass.setAttribute('aria-pressed', 'true');
  });
  toggleConfirmPass.addEventListener('mouseup', () => {
    confirmPassword.type = 'password';
    toggleConfirmPass.classList.remove('show');
    toggleConfirmPass.setAttribute('aria-pressed', 'false');
  });
  toggleConfirmPass.addEventListener('mouseleave', () => {
    if (confirmPassword.type === 'text') {
      confirmPassword.type = 'password';
      toggleConfirmPass.classList.remove('show');
      toggleConfirmPass.setAttribute('aria-pressed', 'false');
    }
  });
}

// ✅ هنا الحل: ما نستدعي addEventListener إلا إذا العنصر موجود
if (terms) {
  terms.addEventListener('change', enableSubmit);
} else {
  console.warn('⚠️ terms checkbox not found in this page');
}

// ============= TOAST =============
function toastShow(msg, ok = true) {
  toast.textContent = msg;
  toast.style.background = ok ? '#16a34a' : '#ef4444';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 1800);
}

// ============= SUBMIT =============
form.addEventListener('submit', async (e) => {
  e.preventDefault();

  // Run validations
  validatefirstName();
  validatelastName();
  validateEmail();
  validateUsername();
  validatePassword();
  validateConfirmPassword();

  // Stop if there are validation errors
  if (submitBtn.disabled) {
    form.classList.add('shake');
    setTimeout(() => form.classList.remove('shake'), 380);
    if (navigator.vibrate) navigator.vibrate(60);
    toastShow('Please fix the errors', false);
    return;
  }

  submitBtn.disabled = true;
  toastShow('Creating your account...');

  try {
    const formData = new FormData(form);

    const response = await fetch("registeration.php", {
      method: "POST",
      body: formData
    });

    const result = await response.json();
    console.log(result); // debug

    if (result.status === "success") {
      toastShow(result.message);
        // Show modal
  const modal = document.getElementById('successModal');
  const yesBtn = document.getElementById('yesBtn');
  const noBtn = document.getElementById('noBtn');

  modal.style.display = 'flex'; // show modal

  // Reset form and card styles
  form.reset();
  card.style.transform = '';
  card.style.boxShadow = '';

  yesBtn.onclick = () => {
    window.location.href = 'login.html'; // go to login page
  };

  noBtn.onclick = () => {
    modal.style.display = 'none'; // just close modal
  };
    
    } else {
      // Show error from PHP
      toastShow('Error: ' + (result.message || 'Unknown error'), false);
      submitBtn.disabled = false;
    }
  } catch (err) {
    toastShow('Network error. Please try again.', false);
    submitBtn.disabled = false;
    console.error(err);
  }
});

// ============= HELP / BACK BUTTONS (لو موجودة) =============
const helpBtn = document.getElementById('helpCenter');
if (helpBtn) helpBtn.addEventListener('click', () => toastShow('Opening Help Center...'));

const backHomeBtn = document.getElementById('backHome');
if (backHomeBtn) backHomeBtn.addEventListener('click', () => {
  window.location.href = 'index.html';
});

// ============= TILT EFFECT =============
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
  document.getElementById("helpCenter").addEventListener("click", function() {
    window.location.href = "faqs.html";
  });

// preload slider images
imgs.forEach(i => {
  const im = new Image();
  im.src = i.src;
});
