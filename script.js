/* ============================================================
   DeepBlue Diving Academy — Main Script
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  // ── 1. NAVBAR scroll effect ──────────────────────────────
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 60);
    document.getElementById('backToTop').classList.toggle('visible', window.scrollY > 400);
  });

  // ── 2. HAMBURGER MENU ────────────────────────────────────
  const hamburger = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobileMenu');
  hamburger.addEventListener('click', () => {
    mobileMenu.classList.toggle('open');
  });
  mobileMenu.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => mobileMenu.classList.remove('open'));
  });

  // ── 3. SMOOTH SCROLL for nav links ──────────────────────
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      const target = document.querySelector(link.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const offset = 70;
        window.scrollTo({ top: target.offsetTop - offset, behavior: 'smooth' });
      }
    });
  });

  // ── 4. ANIMATED COUNTERS ─────────────────────────────────
  function animateCounter(el) {
    const target = parseInt(el.dataset.target);
    const duration = 1800;
    const step = target / (duration / 16);
    let current = 0;
    const timer = setInterval(() => {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      el.textContent = Math.floor(current).toLocaleString('id-ID');
    }, 16);
  }

  const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        statsObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.stat-num').forEach(el => statsObserver.observe(el));

  // ── 5. SCROLL REVEAL (data-aos) ──────────────────────────
  const aosObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.classList.add('visible');
        }, i * 120);
        aosObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('[data-aos]').forEach(el => aosObserver.observe(el));

  // ── 6. TESTIMONIAL SLIDER ────────────────────────────────
  const track   = document.getElementById('testiTrack');
  const dotsWrap = document.getElementById('testiDots');
  const cards   = track ? Array.from(track.children) : [];
  let current   = 0;
  let autoPlay;

  function getVisible() {
    const w = window.innerWidth;
    if (w >= 1100) return 3;
    if (w >= 900)  return 2;
    return 1;
  }

  function buildDots() {
    dotsWrap.innerHTML = '';
    const pages = Math.ceil(cards.length / getVisible());
    for (let i = 0; i < pages; i++) {
      const btn = document.createElement('button');
      btn.className = 'testi-dot' + (i === 0 ? ' active' : '');
      btn.addEventListener('click', () => goTo(i));
      dotsWrap.appendChild(btn);
    }
  }

  function goTo(idx) {
    const visible = getVisible();
    const pages = Math.ceil(cards.length / visible);
    current = Math.max(0, Math.min(idx, pages - 1));
    const cardW = cards[0] ? cards[0].offsetWidth + 28 : 0;
    track.style.transform = `translateX(-${current * cardW * visible}px)`;
    dotsWrap.querySelectorAll('.testi-dot').forEach((d, i) => {
      d.classList.toggle('active', i === current);
    });
  }

  function startAutoPlay() {
    stopAutoPlay();
    autoPlay = setInterval(() => {
      const pages = Math.ceil(cards.length / getVisible());
      goTo(current + 1 < pages ? current + 1 : 0);
    }, 4500);
  }
  function stopAutoPlay() { clearInterval(autoPlay); }

  if (track && cards.length) {
    buildDots();
    startAutoPlay();
    track.addEventListener('mouseenter', stopAutoPlay);
    track.addEventListener('mouseleave', startAutoPlay);
    window.addEventListener('resize', () => { buildDots(); goTo(0); });

    // Touch swipe
    let startX = 0;
    track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; stopAutoPlay(); });
    track.addEventListener('touchend', e => {
      const diff = startX - e.changedTouches[0].clientX;
      const pages = Math.ceil(cards.length / getVisible());
      if (Math.abs(diff) > 50) goTo(diff > 0 ? Math.min(current + 1, pages - 1) : Math.max(current - 1, 0));
      startAutoPlay();
    });
  }

  // ── 7. FORM VALIDATION & SUBMISSION ─────────────────────
  const regForm  = document.getElementById('regForm');
  const btnSubmit = document.getElementById('btnSubmit');
  const btnText  = document.getElementById('btnText');
  const btnLoader = document.getElementById('btnLoader');
  const formSuccess = document.getElementById('formSuccess');

  function setError(id, msg) {
    const field = document.getElementById(id);
    const errEl = document.getElementById('err-' + id);
    if (field)  field.classList.toggle('error', !!msg);
    if (errEl)  errEl.textContent = msg || '';
  }

  function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function validatePhone(phone) {
    return /^(\+62|62|0)[0-9]{8,13}$/.test(phone.replace(/\s/g, ''));
  }

  function validateForm() {
    let valid = true;
    const nama    = document.getElementById('nama')?.value.trim();
    const email   = document.getElementById('email')?.value.trim();
    const telepon = document.getElementById('telepon')?.value.trim();
    const program = document.getElementById('program')?.value;

    if (!nama || nama.length < 3) {
      setError('nama', 'Nama minimal 3 karakter'); valid = false;
    } else { setError('nama', ''); }

    if (!email || !validateEmail(email)) {
      setError('email', 'Format email tidak valid'); valid = false;
    } else { setError('email', ''); }

    if (!telepon || !validatePhone(telepon)) {
      setError('telepon', 'Nomor telepon tidak valid'); valid = false;
    } else { setError('telepon', ''); }

    if (!program) {
      setError('program', 'Pilih program yang diinginkan'); valid = false;
    } else { setError('program', ''); }

    return valid;
  }

  // Live validation
  ['nama','email','telepon','program'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', validateForm);
  });

  if (regForm) {
    regForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!validateForm()) return;

      // Simulate PHP form submission
      btnSubmit.disabled = true;
      btnText.style.display  = 'none';
      btnLoader.style.display = 'inline';

      try {
        // In production, this would be: await fetch('submit.php', { method: 'POST', body: new FormData(regForm) })
       await fetch('submit.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams({
    nama: document.getElementById('nama').value,
    email: document.getElementById('email').value,
    telepon: document.getElementById('telepon').value,
    program: document.getElementById('program').value,
    pengalaman: document.getElementById('pengalaman').value,
    jadwal: document.getElementById('jadwal').value,
    pesan: document.getElementById('pesan').value,
  })
});

        formSuccess.style.display = 'block';
        regForm.reset();
        regForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Confetti effect
        launchConfetti();

      } catch (err) {
        alert('Terjadi kesalahan. Silahkan coba lagi atau hubungi kami via WhatsApp.');
      } finally {
        btnSubmit.disabled = false;
        btnText.style.display  = 'inline';
        btnLoader.style.display = 'none';
      }
    });
  }

  // Simulate PHP backend call (replace with real fetch in production)
  function simulateServerPost(data) {
    console.log('[DeepBlue] Form data to submit:', data);
    return new Promise((resolve) => setTimeout(resolve, 1800));
  }

  // ── 8. SIMPLE CONFETTI ───────────────────────────────────
  function launchConfetti() {
    const colors = ['#f7c948','#00b4d8','#0077b6','#ffffff','#90e0ef'];
    for (let i = 0; i < 60; i++) {
      const el = document.createElement('div');
      Object.assign(el.style, {
        position: 'fixed',
        top: '60%',
        left: Math.random() * 100 + 'vw',
        width:  Math.random() * 10 + 6 + 'px',
        height: Math.random() * 10 + 6 + 'px',
        background: colors[Math.floor(Math.random() * colors.length)],
        borderRadius: Math.random() > 0.5 ? '50%' : '2px',
        pointerEvents: 'none',
        zIndex: 9999,
        opacity: 1,
        transform: 'translateY(0)',
        transition: `transform ${1 + Math.random() * 1.5}s ease, opacity ${1.5 + Math.random()}s ease`,
      });
      document.body.appendChild(el);
      requestAnimationFrame(() => {
        el.style.transform = `translateY(-${200 + Math.random() * 300}px) rotate(${Math.random() * 360}deg)`;
        el.style.opacity = '0';
      });
      setTimeout(() => el.remove(), 3000);
    }
  }

  // ── 9. BACK TO TOP ───────────────────────────────────────
  document.getElementById('backToTop')?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // ── 10. SET MIN DATE for jadwal ──────────────────────────
  const jadwalInput = document.getElementById('jadwal');
  if (jadwalInput) {
    const today = new Date();
    today.setDate(today.getDate() + 3); // min 3 days from now
    jadwalInput.min = today.toISOString().split('T')[0];
  }

  // ── 11. ACTIVE NAV LINK on scroll ───────────────────────
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-links a, .mobile-menu a');

  const navObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        navLinks.forEach(a => {
          a.style.color = a.getAttribute('href') === '#' + entry.target.id
            ? 'var(--accent)' : '';
        });
      }
    });
  }, { threshold: 0.4, rootMargin: '-70px 0px -40% 0px' });

  sections.forEach(s => navObserver.observe(s));



  // ── 12. TRANSISI PINDAH HALAMAN ─────────────────────────
  document.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', function(e) {

      const href = this.getAttribute('href');

      // skip jika href kosong/null atau anchor atau javascript:
      if (
        !href ||
        href === '' ||
        href.startsWith('#') ||
        href.startsWith('javascript')
      ) return;

      e.preventDefault();

      document.body.classList.add('fade-out');

      setTimeout(() => {
        window.location.href = href;
      }, 400);
    });
  });

});