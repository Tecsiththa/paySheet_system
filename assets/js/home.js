/* ============================================================
   PaySheet Pro — Homepage JavaScript
   ============================================================ */

(function () {
  'use strict';

  /* ── Navbar scroll effect ── */
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
  }, { passive: true });

  /* ── Mobile menu ── */
  const hamburger = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobileMenu');

  hamburger?.addEventListener('click', () => {
    const open = mobileMenu.classList.toggle('open');
    hamburger.setAttribute('aria-expanded', open);
    hamburger.querySelectorAll('span')[0].style.transform = open ? 'rotate(45deg) translate(5px, 5px)' : '';
    hamburger.querySelectorAll('span')[1].style.opacity  = open ? '0' : '1';
    hamburger.querySelectorAll('span')[2].style.transform = open ? 'rotate(-45deg) translate(5px, -5px)' : '';
  });

  // Close mobile menu on link click
  document.querySelectorAll('#mobileMenu a').forEach(a => {
    a.addEventListener('click', () => {
      mobileMenu.classList.remove('open');
      hamburger.querySelectorAll('span').forEach(s => { s.style.transform = ''; s.style.opacity = '1'; });
    });
  });

  /* ── Scroll reveal ── */
  const revealEls = document.querySelectorAll('.reveal');
  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        revealObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  revealEls.forEach(el => revealObs.observe(el));

 

  /* ── Animated counter ── */
  function animateCount(el) {
    const target = parseFloat(el.dataset.target);
    const suffix = el.dataset.suffix || '';
    const prefix = el.dataset.prefix || '';
    const decimals = el.dataset.decimals ? parseInt(el.dataset.decimals) : 0;
    const duration = 1800;
    const start = performance.now();

    function step(now) {
      const progress = Math.min((now - start) / duration, 1);
      const ease = 1 - Math.pow(1 - progress, 3);
      const val = target * ease;
      el.textContent = prefix + val.toFixed(decimals) + suffix;
      if (progress < 1) requestAnimationFrame(step);
    }

    requestAnimationFrame(step);
  }

  const counterObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCount(entry.target);
        counterObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('[data-target]').forEach(el => counterObs.observe(el));

  /* ── Smooth scroll for anchor links ── */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  /* ── Bar animations (triggered on scroll) ── */
  const barObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.querySelectorAll('.vis-bar-fill').forEach(bar => {
          const w = bar.style.width;
          bar.style.width = '0';
          requestAnimationFrame(() => {
            bar.style.transition = 'width 1.2s cubic-bezier(0.4,0,0.2,1)';
            bar.style.width = w;
          });
        });
        barObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  document.querySelectorAll('.how-visual').forEach(el => barObs.observe(el));

  /* ── Typing effect on hero badge ── */
  const heroTitle = document.getElementById('heroTitle');
  if (heroTitle) {
    heroTitle.style.visibility = 'visible';
  }

  /* ── Tilt on feature cards ── */
  document.querySelectorAll('.feature-card').forEach(card => {
    card.addEventListener('mousemove', e => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const cx = rect.width  / 2;
      const cy = rect.height / 2;
      const tiltX = ((y - cy) / cy) * 4;
      const tiltY = ((cx - x) / cx) * 4;
      card.style.transform = `perspective(600px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translateY(-4px)`;
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
      card.style.transition = 'transform 0.4s ease';
    });
  });

  /* ── Parallax on hero orbs ── */
  window.addEventListener('mousemove', e => {
    const x = (e.clientX / window.innerWidth  - 0.5) * 20;
    const y = (e.clientY / window.innerHeight - 0.5) * 20;
    document.querySelector('.bg-orb-1')?.style && (document.querySelector('.bg-orb-1').style.transform = `translate(${x * 0.6}px, ${y * 0.6}px)`);
    document.querySelector('.bg-orb-2')?.style && (document.querySelector('.bg-orb-2').style.transform = `translate(${-x * 0.4}px, ${-y * 0.4}px)`);
  }, { passive: true });

})();