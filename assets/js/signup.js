const slidesNode = document.getElementById('slides');
const imgs = Array.from(slidesNode.querySelectorAll('img'));
const indicators = document.getElementById('indicators');
const fullName = document.getElementById('fullName');
const email = document.getElementById('email');
const username = document.getElementById('username');
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirmPassword');
const togglePass = document.getElementById('togglePass');
const toggleConfirmPass = document.getElementById('toggleConfirmPass');
const form = document.getElementById('signupForm');
const toast = document.getElementById('toast');
const nameErr = document.getElementById('nameErr');
const emailErr = document.getElementById('emailErr');
const usernameErr = document.getElementById('usernameErr');
const passErr = document.getElementById('passErr');
const confirmPassErr = document.getElementById('confirmPassErr');
const terms = document.getElementById('terms');
const submitBtn = document.getElementById('submitBtn');
const card = document.getElementById('card');

let current = 0, timer = null, hover = false, visible = true, swipeStartX = null;
const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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

function next() { show(current + 1, false); }
function start() { if (REDUCED || hover || !visible) return; stop(); timer = setInterval(next, 5200); }
function stop() { if (timer) { clearInterval(timer); timer = null; } }
function restart() { stop(); start(); }

slidesNode.addEventListener('mouseenter', () => { hover = true; stop(); });
slidesNode.addEventListener('mouseleave', () => { hover = false; start(); });
slidesNode.addEventListener('touchstart', e => { swipeStartX = e.touches[0].clientX; stop(); }, { passive: true });
slidesNode.addEventListener('touchmove', e => { 
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
    if (e.key === 'ArrowLeft') show(current - 1, true); 
    if (e.key === 'ArrowRight') show(current + 1, true); 
});

imgs[0].classList.add('active'); 
start();

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
    const ok = !nameErr.textContent && !emailErr.textContent && !usernameErr.textContent && 
               !passErr.textContent && !confirmPassErr.textContent && 
               fullName.value.trim() && email.value.trim() && username.value.trim() && 
               password.value.length >= 8 && confirmPassword.value && terms.checked;
    submitBtn.disabled = !ok;
}

const validateName = debounce(() => { 
    const v = fullName.value.trim(); 
    if (!v) { 
        setError(nameErr, 'Please enter your full name', fullName); 
    } else if (v.length < 2) { 
        setError(nameErr, 'Name must be at least 2 characters', fullName); 
    } else { 
        setError(nameErr, '', fullName); 
    } 
    enableSubmit(); 
}, 140);

const validateEmail = debounce(() => { 
    const v = email.value.trim(); 
    if (!v) { 
        setError(emailErr, 'Please enter your email', email); 
    } else if (!isEmail(v)) { 
        setError(emailErr, 'Please enter a valid email address', email); 
    } else { 
        setError(emailErr, '', email); 
    } 
    enableSubmit(); 
}, 140);

const validateUsername = debounce(() => { 
    const v = username.value.trim(); 
    if (!v) { 
        setError(usernameErr, 'Please choose a username', username); 
    } else if (!isUsername(v)) { 
        setError(usernameErr, 'Username must be at least 3 characters and contain only letters, numbers, dots, dashes, or underscores', username); 
    } else { 
        setError(usernameErr, '', username); 
    } 
    enableSubmit(); 
}, 140);

const validatePassword = debounce(() => { 
    const v = password.value; 
    if (!v) { 
        setError(passErr, 'Please create a password', password); 
    } else if (v.length < 8) { 
        setError(passErr, 'Password must be at least 8 characters', password); 
    } else { 
        setError(passErr, '', password); 
    } 
    validateConfirmPassword(); 
    enableSubmit(); 
}, 120);

const validateConfirmPassword = debounce(() => { 
    const v = confirmPassword.value; 
    if (!v) { 
        setError(confirmPassErr, 'Please confirm your password', confirmPassword); 
    } else if (v !== password.value) { 
        setError(confirmPassErr, 'Passwords do not match', confirmPassword); 
    } else { 
        setError(confirmPassErr, '', confirmPassword); 
    } 
    enableSubmit(); 
}, 120);

fullName.addEventListener('input', validateName);
email.addEventListener('input', validateEmail);
username.addEventListener('input', validateUsername);
password.addEventListener('input', validatePassword);
confirmPassword.addEventListener('input', validateConfirmPassword);

password.addEventListener('keydown', e => { 
    document.getElementById('capsNote').textContent = e.getModifierState && e.getModifierState('CapsLock') ? 'Caps Lock is ON' : ''; 
});
password.addEventListener('keyup', e => { 
    document.getElementById('capsNote').textContent = e.getModifierState && e.getModifierState('CapsLock') ? 'Caps Lock is ON' : ''; 
});

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

terms.addEventListener('change', enableSubmit);

function toastShow(msg, ok = true) { 
    toast.textContent = msg; 
    toast.style.background = ok ? '#16a34a' : '#ef4444'; 
    toast.classList.add('show'); 
    setTimeout(() => toast.classList.remove('show'), 1800); 
}

form.addEventListener('submit', e => {
    e.preventDefault();
    validateName();
    validateEmail();
    validateUsername();
    validatePassword();
    validateConfirmPassword();
    
    if (submitBtn.disabled) {
        form.classList.add('shake'); 
        setTimeout(() => form.classList.remove('shake'), 380);
        if (navigator.vibrate) navigator.vibrate(60);
        toastShow('Please fix the errors', false); 
        return;
    }
    
    submitBtn.disabled = true; 
    toastShow('Creating your account...');
    
    setTimeout(() => { 
        toastShow('Account created successfully! ✅'); 
        card.style.transition = 'transform .5s cubic-bezier(.2,.9,.3,1), box-shadow .5s'; 
        card.style.transform = 'translateY(-14px)'; 
        card.style.boxShadow = '0 36px 80px rgba(135,43,255,.16)';

        setTimeout(() => {
            form.reset();
            submitBtn.disabled = true;
            card.style.transform = '';
            card.style.boxShadow = '';
        }, 2000);
    }, 1500);
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

imgs.forEach(i => { 
    const im = new Image(); 
    im.src = i.src; 
});