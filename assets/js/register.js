/* ============================================================
   PaySheet Pro — Register Page JavaScript
   ============================================================ */

(function () {
  'use strict';

  /* ── Password visibility toggle ── */
  document.querySelectorAll('.pass-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.previousElementSibling;
      const isText = input.type === 'text';
      input.type = isText ? 'password' : 'text';
      btn.innerHTML = isText
        ? `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
        : `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
    });
  });

  /* ── Password strength meter ── */
  const passInput    = document.getElementById('password');
  const strengthFill = document.getElementById('strengthFill');
  const strengthText = document.getElementById('strengthText');

  if (passInput && strengthFill) {
    passInput.addEventListener('input', () => {
      const val = passInput.value;
      let score = 0;
      let label = '';
      let color = '';

      if (val.length >= 6)  score++;
      if (val.length >= 10) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      const levels = [
        { pct: '0%',   color: 'transparent',                   text: '' },
        { pct: '20%',  color: '#f87171',                       text: 'Very Weak' },
        { pct: '40%',  color: '#fb923c',                       text: 'Weak' },
        { pct: '60%',  color: '#facc15',                       text: 'Fair' },
        { pct: '80%',  color: '#4ade80',                       text: 'Strong' },
        { pct: '100%', color: '#00e5b0',                       text: 'Very Strong ✓' },
      ];

      const level = val.length === 0 ? levels[0] : levels[Math.min(score, 5)];
      strengthFill.style.width      = level.pct;
      strengthFill.style.background = level.color;
      strengthText.textContent      = level.text;
      strengthText.style.color      = level.color;
    });
  }

  /* ── Password match validation ── */
  const confirmInput = document.getElementById('confirm_password');
  const matchMsg     = document.getElementById('matchMsg');

  if (confirmInput && matchMsg) {
    function checkMatch() {
      if (!confirmInput.value) { matchMsg.className = 'field-msg'; return; }
      const ok = confirmInput.value === passInput.value;
      confirmInput.classList.toggle('valid', ok);
      confirmInput.classList.toggle('invalid', !ok);
      matchMsg.className = 'field-msg show ' + (ok ? 'ok' : 'err');
      matchMsg.innerHTML = ok
        ? `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Passwords match`
        : `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Passwords do not match`;
    }
    confirmInput.addEventListener('input', checkMatch);
    passInput.addEventListener('input', () => { if (confirmInput.value) checkMatch(); });
  }

  /* ── Email format validation ── */
  const emailInput = document.getElementById('company_email');
  const emailMsg   = document.getElementById('emailMsg');

  if (emailInput && emailMsg) {
    emailInput.addEventListener('blur', () => {
      if (!emailInput.value) { emailMsg.className = 'field-msg'; return; }
      const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value);
      emailInput.classList.toggle('valid', valid);
      emailInput.classList.toggle('invalid', !valid);
      emailMsg.className = 'field-msg show ' + (valid ? 'ok' : 'err');
      emailMsg.innerHTML = valid
        ? `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Valid email`
        : `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Enter a valid email address`;
    });
  }

  /* ── Company name length indicator ── */
  const nameInput   = document.getElementById('company_name');
  const nameCounter = document.getElementById('nameCounter');

  if (nameInput && nameCounter) {
    nameInput.addEventListener('input', () => {
      nameCounter.textContent = nameInput.value.length + '/100';
    });
  }

  /* ── Phone number formatting ── */
  const phoneInput = document.getElementById('company_phone');
  if (phoneInput) {
    phoneInput.addEventListener('input', () => {
      let val = phoneInput.value.replace(/\D/g, '');
      if (val.startsWith('94')) val = '+' + val;
      else if (val.startsWith('0')) val = val;
      phoneInput.value = val;
    });
  }

  /* ── Submit button loader ── */
  const form       = document.getElementById('registerForm');
  const btnSubmit  = document.getElementById('btnSubmit');
  const btnText    = document.getElementById('btnText');
  const btnSpinner = document.getElementById('btnSpinner');

  if (form && btnSubmit) {
    form.addEventListener('submit', (e) => {
      // Check terms
      const terms = document.getElementById('terms');
      if (terms && !terms.checked) {
        e.preventDefault();
        showInlineError('Please agree to the Terms of Service to continue.');
        return;
      }

      // Activate loading state
      btnSubmit.disabled = true;
      btnText.textContent = 'Creating account...';
      btnSpinner.style.display = 'block';
    });
  }

  function showInlineError(msg) {
    let existing = document.querySelector('.alert.alert-error.inline');
    if (existing) existing.remove();
    const div = document.createElement('div');
    div.className = 'alert alert-error inline';
    div.innerHTML = `<span class="alert-icon">⚠</span><span>${msg}</span>`;
    form.prepend(div);
    div.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  /* ── Step indicator animation ── */
  const fields = form ? form.querySelectorAll('input, select, textarea') : [];
  const dots   = document.querySelectorAll('.step-dot');
  let filledCount = 0;

  function updateSteps() {
    let filled = 0;
    fields.forEach(f => { if (f.value.trim()) filled++; });
    const totalRequired = form ? form.querySelectorAll('[required]').length : 1;
    const pct = filled / Math.max(totalRequired * 1.5, 1);

    if (dots.length >= 3) {
      dots[0].classList.toggle('done',   pct > 0.1);
      dots[1].classList.toggle('active', pct > 0.1 && pct <= 0.6);
      dots[1].classList.toggle('done',   pct > 0.6);
      dots[2].classList.toggle('active', pct > 0.6);
    }
  }

  fields.forEach(f => f.addEventListener('input', updateSteps));

  /* ── Subtle input focus ring glow ── */
  document.querySelectorAll('.fg input, .fg select, .fg textarea').forEach(el => {
    el.addEventListener('focus', () => {
      el.closest('.fg')?.querySelector('label')?.style && (el.closest('.fg').querySelector('label').style.color = '#00e5b0');
    });
    el.addEventListener('blur', () => {
      el.closest('.fg')?.querySelector('label')?.style && (el.closest('.fg').querySelector('label').style.color = '');
    });
  });

  /* ── Animated stat counters ── */
  function animCount(el) {
    const to  = parseFloat(el.dataset.to);
    const sfx = el.dataset.suffix || '';
    const dur = 1600;
    const t0  = performance.now();
    (function tick(now) {
      const p = Math.min((now - t0) / dur, 1);
      const e = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(to * e) + sfx;
      if (p < 1) requestAnimationFrame(tick);
    })(t0);
  }
  document.querySelectorAll('[data-to]').forEach(el => animCount(el));

})();