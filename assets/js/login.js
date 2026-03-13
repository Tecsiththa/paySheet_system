/* ============================================================
    Login Page JavaScript
   ============================================================ */

(function () {
  'use strict';

  /* ── Elements ── */
  const form        = document.getElementById('loginForm');
  const emailInput  = document.getElementById('email');
  const passInput   = document.getElementById('password');
  const emailErr    = document.getElementById('emailErr');
  const passErr     = document.getElementById('passErr');
  const btnLogin    = document.getElementById('btnLogin');
  const btnText     = document.getElementById('btnText');
  const btnSpinner  = document.getElementById('btnSpinner');
  const btnArrow    = document.getElementById('btnArrow');
  const loginCard   = document.getElementById('loginCard');

  /* ── Password visibility toggle ── */
  const passToggle = document.getElementById('passToggle');
  const eyeOpen = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
  const eyeOff  = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;

  if (passToggle) {
    passToggle.addEventListener('click', () => {
      const isPass = passInput.type === 'password';
      passInput.type = isPass ? 'text' : 'password';
      passToggle.innerHTML = isPass ? eyeOff : eyeOpen;
      passInput.focus();
    });
  }

  /* ── Real-time email validation ── */
  function validateEmail(val) {
    if (!val) return 'Email address is required.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) return 'Enter a valid email address.';
    return '';
  }

  if (emailInput) {
    emailInput.addEventListener('blur', () => {
      const err = validateEmail(emailInput.value.trim());
      showFieldErr(emailInput, emailErr, err);
    });

    emailInput.addEventListener('input', () => {
      if (emailInput.classList.contains('field-error')) {
        const err = validateEmail(emailInput.value.trim());
        showFieldErr(emailInput, emailErr, err);
      }
    });
  }

  /* ── Real-time password validation ── */
  if (passInput) {
    passInput.addEventListener('blur', () => {
      if (!passInput.value) {
        showFieldErr(passInput, passErr, 'Password is required.');
      } else {
        showFieldErr(passInput, passErr, '');
      }
    });

    passInput.addEventListener('input', () => {
      if (passInput.classList.contains('field-error') && passInput.value) {
        showFieldErr(passInput, passErr, '');
      }
    });
  }

  /* ── Show / hide field error ── */
  function showFieldErr(input, errEl, msg) {
    if (!input || !errEl) return;
    if (msg) {
      input.classList.add('field-error');
      errEl.innerHTML = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> ${msg}`;
      errEl.classList.add('show');
    } else {
      input.classList.remove('field-error');
      errEl.classList.remove('show');
    }
  }

  /* ── Form submission ── */
  if (form) {
    form.addEventListener('submit', function (e) {
      let valid = true;

      // Validate email
      const emailVal = emailInput?.value.trim() || '';
      const emailErrMsg = validateEmail(emailVal);
      if (emailErrMsg) {
        showFieldErr(emailInput, emailErr, emailErrMsg);
        valid = false;
      }

      // Validate password
      if (!passInput?.value) {
        showFieldErr(passInput, passErr, 'Password is required.');
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
        // Shake the card
        loginCard?.classList.remove('shake');
        void loginCard?.offsetWidth; // reflow
        loginCard?.classList.add('shake');
        loginCard?.addEventListener('animationend', () => loginCard.classList.remove('shake'), { once: true });
        // Focus first error
        if (emailErrMsg) emailInput?.focus();
        else passInput?.focus();
        return;
      }

      // Loading state
      if (btnLogin) {
        btnLogin.disabled = true;
        if (btnText)    btnText.textContent = 'Signing in...';
        if (btnSpinner) btnSpinner.style.display = 'block';
        if (btnArrow)   btnArrow.style.display = 'none';
      }
    });
  }

  /* ── Remember me — persist email ── */
  const rememberCheckbox = document.getElementById('remember');
  const savedEmail = localStorage.getItem('ps_email');

  if (savedEmail && emailInput) {
    emailInput.value = savedEmail;
    if (rememberCheckbox) rememberCheckbox.checked = true;
  }

  if (rememberCheckbox) {
    rememberCheckbox.addEventListener('change', () => {
      if (!rememberCheckbox.checked) {
        localStorage.removeItem('ps_email');
      }
    });
  }

  if (form) {
    form.addEventListener('submit', () => {
      if (rememberCheckbox?.checked && emailInput?.value) {
        localStorage.setItem('ps_email', emailInput.value.trim());
      } else {
        localStorage.removeItem('ps_email');
      }
    });
  }

  /* ── Particle dots ── */
  const particleContainer = document.querySelector('.particles');
  if (particleContainer) {
    const count = 18;
    for (let i = 0; i < count; i++) {
      const p = document.createElement('div');
      p.className = 'particle';
      p.style.cssText = `
        left: ${Math.random() * 100}%;
        top:  ${Math.random() * 100}%;
        animation-duration: ${3 + Math.random() * 5}s;
        animation-delay:    ${Math.random() * 6}s;
        width:  ${Math.random() > 0.5 ? 2 : 3}px;
        height: ${Math.random() > 0.5 ? 2 : 3}px;
        background: ${Math.random() > 0.7 ? '#4f8eff' : '#00e5b0'};
      `;
      particleContainer.appendChild(p);
    }
  }

  /* ── Subtle mouse parallax on orbs ── */
  let raf;
  window.addEventListener('mousemove', (e) => {
    cancelAnimationFrame(raf);
    raf = requestAnimationFrame(() => {
      const x = (e.clientX / window.innerWidth  - 0.5);
      const y = (e.clientY / window.innerHeight - 0.5);
      const o1 = document.querySelector('.orb-1');
      const o2 = document.querySelector('.orb-2');
      if (o1) o1.style.transform = `translate(${x * 18}px, ${y * 18}px) scale(1)`;
      if (o2) o2.style.transform = `translate(${-x * 14}px, ${-y * 14}px) scale(1)`;
    });
  }, { passive: true });

  /* ── Input float-label glow effect ── */
  document.querySelectorAll('.input-box input').forEach(input => {
    input.addEventListener('focus', () => {
      input.closest('.field')?.querySelector('label')?.style &&
        (input.closest('.field').querySelector('label').style.color = '#00e5b0');
    });
    input.addEventListener('blur', () => {
      input.closest('.field')?.querySelector('label')?.style &&
        (input.closest('.field').querySelector('label').style.color = '');
    });
  });

  /* ── Auto-dismiss PHP server-side alert after 6s ── */
  const serverAlert = document.querySelector('.alert');
  if (serverAlert) {
    setTimeout(() => {
      serverAlert.style.transition = 'opacity 0.5s, transform 0.5s';
      serverAlert.style.opacity    = '0';
      serverAlert.style.transform  = 'translateY(-6px)';
      setTimeout(() => serverAlert.remove(), 500);
    }, 6000);
  }

})();