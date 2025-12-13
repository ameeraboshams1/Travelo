// ================= SLIDER =================
const slidesNode = document.getElementById('slides');
const indicators = document.getElementById('indicators');
const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// ================= FORM ELEMENTS =================
const email = document.getElementById('email');
const pass = document.getElementById('password');
const showPass = document.getElementById('remember'); 
const form = document.getElementById('loginForm');
const toast = document.getElementById('toast');
const emailErr = document.getElementById('emailErr');
const passErr = document.getElementById('passErr');
const submitBtn = document.getElementById('submitBtn');
const card = document.getElementById('card');
//const capsNote = document.getElementById('capsNote');

// ===== Forgot password elements =====
const forgotLink = document.getElementById('forgotLink');
const fpOverlay = document.getElementById('fpOverlay');
const fpCloseBtn = document.getElementById('fpCloseBtn');

const fpTitle = document.getElementById('fpTitle');
const fpDesc = document.getElementById('fpDesc');

// step email
const fpStepEmail = document.getElementById('fpStepEmail');
const fpEmailInput = document.getElementById('fpEmailInput');
const fpEmailErr = document.getElementById('fpEmailErr');
const fpSendCodeBtn = document.getElementById('fpSendCodeBtn');
const fpCancelBtn = document.getElementById('fpCancelBtn');

// step code
const fpStepCode = document.getElementById('fpStepCode');
const fpCodeInput = document.getElementById('fpCodeInput');
const fpCodeErr = document.getElementById('fpCodeErr');
const fpNextBtn = document.getElementById('fpNextBtn');
const fpResendBtn = document.getElementById('fpResendBtn');
const fpBackToEmailBtn = document.getElementById('fpBackToEmailBtn');

// step new password
const fpStepNew = document.getElementById('fpStepNew');
const fpNewPass = document.getElementById('fpNewPass');
const fpConfirmPass = document.getElementById('fpConfirmPass');
const fpNewPassErr = document.getElementById('fpNewPassErr');
const fpConfirmPassErr = document.getElementById('fpConfirmPassErr');
const fpBackBtn = document.getElementById('fpBackBtn');
const fpSubmitBtn = document.getElementById('fpSubmitBtn');
const showNewPass = document.getElementById('showp');
let current = 0,
timer = null,
hover = false,
visible = true,
swipeStartX = null;
let imgs=[];
let dots=[];

// ================= SLIDER LOGIC =================
if (slidesNode && indicators) {
  imgs = Array.from(slidesNode.querySelectorAll('img'));
imgs.forEach((_, i) => {
  const b = document.createElement('button');
  if (i === 0) b.classList.add('active');
  b.addEventListener('click', () => show(i, true));
  indicators.appendChild(b);
});
 dots = Array.from(indicators.querySelectorAll('button'));

function showSlide(n, manual) {
  current = (n + imgs.length) % imgs.length;
  imgs.forEach((im, i) => im.classList.toggle('active', i === current));
  dots.forEach((d, i) => d.classList.toggle('active', i === current));
  if (manual) restartSlider();
}

function nextSlide() { showSlide(current + 1, false); } 
function startSlider() { if (REDUCED || hover || !visible) return; stopSlider(); timer = setInterval(next, 5200); }
function stopSlider() { if (timer) { clearInterval(timer); timer = null; } }
function restartSlider() { stopSlider(); startSlider(); }
    
slidesNode.addEventListener('mouseenter', () => { hover = true; stopSlider(); });
slidesNode.addEventListener('mouseleave', () => { hover = false; startSlider(); });
slidesNode.addEventListener('touchstart', e => { swipeStartX = e.touches[0].clientX; stopSlider(); }, { passive: true });
slidesNode.addEventListener('touchmove', e => {
  if (swipeStartX === null) return;
  const dx = e.touches[0].clientX - swipeStartX;
  if (Math.abs(dx) > 60) {
    show(current + (dx < 0 ? 1 : -1), true);
    swipeStartX = null;
  }
}, { passive: true });
slidesNode.addEventListener('touchend', () => { swipeStartX = null; startSlider(); }, { passive: true });
document.addEventListener('visibilitychange', () => { visible = !document.hidden; visible ? startSlider() : stopSlider(); });
document.addEventListener('keydown', e => {
  if (e.key === 'ArrowLeft') show(current - 1, true);
  if (e.key === 'ArrowRight') show(current + 1, true);
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
}  else {
  console.warn('No #slides slider on this page.');
}

// ================= HELPERS =================
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
function toastShow(msg, ok = true) {////////////////toastShow //////////////
  toast.textContent = msg;
  toast.style.background = ok ? '#16a34a' : '#ef4444';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 1800);
}

// ================= VALIDATION =================
const validateEmail = debounce(() => {
  const v = email.value.trim();
  if (!v) {
    setError(emailErr, 'Enter email or username', email);
  } else if (v.includes('@')) {
    setError(emailErr, isEmail(v) ? '' : 'Enter a valid email', email);
  } else {
    setError(emailErr, isUsername(v) ? '' : 'Enter a valid username', email);
  }
  enableSubmit();
}, 140);

const validatePass = debounce(() => {
  const v = pass.value;
  if (!v) {
    setError(passErr, 'Enter your password', pass);
  } else if (v.length < 6) {
    setError(passErr, 'Password must be at least 6 characters', pass);
  } else {
    setError(passErr, '', pass);
  }
  enableSubmit();
}, 120);

email.addEventListener('input', validateEmail);
pass.addEventListener('input', validatePass);

//capslock
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
//showing password

if (showPass) {
  showPass.addEventListener('change', () => {
    const isChecked = showPass.checked;
    pass.type = isChecked ? 'text' : 'password';
  });
}

// Remember me///////////////////////
if (localStorage.getItem('travelo_remember') === '1') {
  email.value = localStorage.getItem('travelo_email') || '';
  remember.checked = true;
  validateEmail();
}

// ================= FORM SUBMIT =================
form.addEventListener('submit', async e => {
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

  const formData = new FormData(form);

  try {
    const response = await fetch('login.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.status === 'success') {
      toastShow('Logged in successfully ✅');

      card.style.transition =
        'transform .5s cubic-bezier(.2,.9,.3,1), box-shadow .5s';
      card.style.transform = 'translateY(-14px)';
      card.style.boxShadow = '0 36px 80px rgba(135,43,255,.16)';
        setTimeout(() => {
        if (result.role === 'admin') {
          window.location.href = 'dashboard.html';
        } else {
          window.location.href = 'index.php';
        }
      }, 2000); // wait for 2 secs
    } else {
      toastShow(result.message || 'Login failed', false);
      submitBtn.disabled = false;
    }
  } catch (err) {
    console.error(err);
    toastShow('Something went wrong', false);
    submitBtn.disabled = false;
  }
});



// Card tilt
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

document.getElementById("backHome").addEventListener("click", function() {
    window.location.href = "index.html";
  });
  document.getElementById("helpCenter").addEventListener("click", function() {
    window.location.href = "faqs.html";
  });
imgs.forEach(i => { const im = new Image(); im.src = i.src });

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
      window.location.href = 'faqs.html'; // من login.html إلى صفحة المساعدة
    });
  }

  if (backHomeBtn) {
    backHomeBtn.addEventListener('click', function () {
      window.location.href = 'index.php'; // من login.html إلى الصفحة الرئيسية
    });
  }
});

// ================= FORGOT PASSWORD FLOW =================
let fpCode = null;
let fpTargetEmail = '';

function generateCode() {
  fpCode = String(Math.floor(100000 + Math.random() * 900000));
  console.log('%cTravelo reset code:', 'color:#a855f7;font-weight:bold;', fpCode);
}

function resetForgotUI() {
  fpEmailInput.value = '';
  fpEmailErr.textContent = '';
  fpCodeInput.value = '';
  fpCodeErr.textContent = '';
  fpNewPass.value = '';
  fpConfirmPass.value = '';
  fpNewPassErr.textContent = '';
  fpConfirmPassErr.textContent = '';
}

function showStepEmail() {
  fpStepEmail.classList.remove('is-hidden');
  fpStepCode.classList.add('is-hidden');
  fpStepNew.classList.add('is-hidden');

  fpTitle.textContent = 'Reset your password';
  fpDesc.textContent =
    'Enter the email address linked with your Travelo account and we’ll send you a 6-digit verification code to continue.';
  fpEmailInput.focus();
}

function showStepCode() {
  fpStepEmail.classList.add('is-hidden');
  fpStepCode.classList.remove('is-hidden');
  fpStepNew.classList.add('is-hidden');

  fpTitle.textContent = 'Check your inbox';
  fpDesc.innerHTML =
    `We’ve sent a 6-digit verification code to ` +
    `<strong class="fp-email">${fpTargetEmail}</strong>. ` +
    `Enter the code below to continue resetting your Travelo password.`;

  fpCodeInput.focus();
}


function showStepNewPass() {
  fpStepEmail.classList.add('is-hidden');
  fpStepCode.classList.add('is-hidden');
  fpStepNew.classList.remove('is-hidden');

  fpTitle.textContent = 'Create a new password';
  fpDesc.textContent =
    'Choose a strong password that you haven’t used before for Travelo. You will use it the next time you sign in.';
  fpNewPass.focus();
}

function openForgotModal() {
  if (!fpOverlay) return;
  resetForgotUI();
  fpTargetEmail = '';
  fpOverlay.classList.add('show');
  document.body.classList.add('no-scroll');
  showStepEmail();
}

function closeForgotModal() {
  if (!fpOverlay) return;
  fpOverlay.classList.remove('show');
  document.body.classList.remove('no-scroll');
}

// ====== bindings ======
if (forgotLink && fpOverlay) {
  forgotLink.addEventListener('click', e => {
    e.preventDefault();
    openForgotModal();
  });

  fpCloseBtn.addEventListener('click', closeForgotModal);
  fpCancelBtn.addEventListener('click', closeForgotModal);
  fpOverlay.addEventListener('click', e => {
    if (e.target === fpOverlay) closeForgotModal();
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && fpOverlay.classList.contains('show')) {
      closeForgotModal();
    }
  });


// STEP EMAIL: send code
fpSendCodeBtn.addEventListener('click', async () => {
  const emailValue = fpEmailInput.value.trim();
  fpEmailErr.textContent = '';

  if (!emailValue) {
    fpEmailErr.textContent = 'Enter your email address';
    fpEmailInput.focus();
    return;
  }
  if (!isEmail(emailValue)) {
    fpEmailErr.textContent = 'Enter a valid email address';
    fpEmailInput.focus();
    return;
  }

  try {
    const res = await fetch('forgetPass.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ emailfp: emailValue })
    });

    // log raw response for debugging
    const text = await res.text();
    console.log('Raw response:', text);

    let data;
    try {
      data = JSON.parse(text);
    } catch (err) {
      fpEmailErr.textContent = 'Server error. Check console.';
      console.error('JSON parse error:', err);
      return;
    }

    if (data.status === 'success') {
      fpTargetEmail = emailValue; // save email for later steps
      toastShow('Verification code sent to your email ✉️');
      showStepCode();
    } else {
      fpEmailErr.textContent = data.message;
    }
  } catch (err) {
    fpEmailErr.textContent = 'Something went wrong. Try again later.';
    console.error(err);
  }
});



  // STEP CODE: back to email
  fpBackToEmailBtn.addEventListener('click', () => {
    showStepEmail();
  });

  // STEP CODE: next
// STEP CODE: next
fpNextBtn.addEventListener('click', async () => {
  const codeValue = fpCodeInput.value.trim();
  fpCodeErr.textContent = '';

  if (!codeValue) {
    fpCodeErr.textContent = 'Enter the 6-digit code';
    fpCodeInput.focus();
    return;
  }

  try {
    const res = await fetch('verifycode.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ emailfp: fpTargetEmail, code: codeValue })
    });

    const text = await res.text();
    console.log('Verify code response:', text);

    let data;
    try { data = JSON.parse(text); } 
    catch (err) {
      fpCodeErr.textContent = 'Server error. Check console.';
      console.error('JSON parse error:', err);
      return;
    }

    if (data.status === 'success') {
      showStepNewPass();
    } else {
      fpCodeErr.textContent = data.message;
      fpCodeInput.focus();
      if (navigator.vibrate) navigator.vibrate(60);
    }
  } catch (err) {
    fpCodeErr.textContent = 'Something went wrong. Try again later.';
    console.error(err);
  }
});



// STEP CODE: resend
fpResendBtn.addEventListener('click', async () => {
  if (!fpTargetEmail) {
    showStepEmail();
    fpEmailErr.textContent = 'Enter your email first';
    fpEmailInput.focus();
    return;
  }

  try {
    const res = await fetch('forgetPass.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ emailfp: fpTargetEmail })
    });

    const text = await res.text();
    console.log('Raw response:', text);

    let data;
    try {
      data = JSON.parse(text);
    } catch (err) {
      fpCodeErr.textContent = 'Server error. Check console.';
      console.error('JSON parse error:', err);
      return;
    }

    if (data.status === 'success') {
      toastShow('A new code has been sent ✉️');
      showStepCode();
    } else {
      fpCodeErr.textContent = data.message;
    }
  } catch (err) {
    fpCodeErr.textContent = 'Something went wrong. Try again later.';
    console.error(err);
  }
});


 

  // STEP NEW: back to code
  fpBackBtn.addEventListener('click', () => {
    showStepCode();
  });

  function validateResetPasswords() {
    let ok = true;
    const p1 = fpNewPass.value.trim();
    const p2 = fpConfirmPass.value.trim();

    fpNewPassErr.textContent = '';
    fpConfirmPassErr.textContent = '';

    if (!p1) {
      fpNewPassErr.textContent = 'Enter a new password';
      ok = false;
    } else if (p1.length < 6) {
      fpNewPassErr.textContent = 'Password must be at least 6 characters';
      ok = false;
    }

    if (!p2) {
      fpConfirmPassErr.textContent = 'Confirm your new password';
      ok = false;
    } else if (p1 && p1 !== p2) {
      fpConfirmPassErr.textContent = 'Passwords do not match';
      ok = false;
    }

    return ok;
  }

//showpass buttonfor new password
if (showNewPass) {
  showNewPass.addEventListener('change', () => {
    const isChecked = showNewPass.checked;
    //pass.type = isChecked ? 'text' : 'password';
     fpNewPass.type = isChecked ? 'text' : 'password';
     fpConfirmPass.type = isChecked ? 'text' : 'password'; 
  });
}
// STEP NEW: submit
fpSubmitBtn.addEventListener('click', async () => {
  if (!validateResetPasswords()) return;

  const newPassValue = fpNewPass.value.trim();
  const codeValue = fpCodeInput.value.trim(); // code from Step 1

  try {
    const res = await fetch('resetpass.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ 
        emailfp: fpTargetEmail, 
        code: codeValue, 
        newpass: newPassValue 
      })
    });

    const text = await res.text();
    console.log('Reset password response:', text);

    let data;
    try { data = JSON.parse(text); } 
    catch (err) {
      fpNewPassErr.textContent = 'Server error. Check console.';
      console.error('JSON parse error:', err);
      return;
    }

    if (data.status === 'success') {
      closeForgotModal();
     // pass.value = newPassValue; // fill login form
      validatePass();
      toastShow('Password updated successfully. You can now sign in ✅');
      pass.focus();
    } else {
      fpNewPassErr.textContent = data.message;
    }
  } catch (err) {
    fpNewPassErr.textContent = 'Something went wrong. Try again later.';
    console.error(err);
  }
});

}
