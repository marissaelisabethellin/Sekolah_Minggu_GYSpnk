// ============================================================
//  SCRIPT.JS — Sekolah Minggu GYS Pontianak
//  Kompatibel dengan index.php (versi PHP)
//  Semua fitur JS tetap berjalan di sisi client (browser).
// ============================================================


// ── 1. AMBIL ELEMEN DOM ─────────────────────────────────────

const navbar         = document.getElementById('navbar');
const hamburger      = document.getElementById('hamburger');
const drawer         = document.getElementById('drawer');
const drawerLinks    = document.querySelectorAll('.drawer_link');

const carouselSlides = document.querySelectorAll('.hero_slide');
const carouselDots   = document.querySelectorAll('.hero_dot');
const heroPrev       = document.getElementById('heroPrev');
const heroNext       = document.getElementById('heroNext');

const contactForm    = document.getElementById('contactForm');

const allAnchors     = document.querySelectorAll('a[href^="#"]');
const reveals        = document.querySelectorAll('.reveal');

const sections       = document.querySelectorAll('section[id]');
const navLinks       = document.querySelectorAll('.navbar_link');

const backToTopBtn   = document.getElementById('backToTop');
const galleryImages  = document.querySelectorAll('.galeri-item img');
const lightbox       = document.getElementById('lightbox');
const lightboxImage  = document.getElementById('lightboxImage');
const lightboxClose  = document.getElementById('lightboxClose');
const toast          = document.getElementById('toast');


// ── 2. STATE ────────────────────────────────────────────────

let currentSlide  = 0;
let autoPlayTimer = null;
let drawerOpen    = false;


// ── 3. TOAST (NOTIFIKASI SINGKAT) ───────────────────────────

/**
 * Tampilkan pesan singkat di bawah layar selama 3 detik.
 * @param {string} message
 */
function showToast(message) {
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

// Cek apakah halaman di-reload setelah PHP redirect (misal
// setelah error validasi server) dan ada hash #kontak.
// Kalau ada, scroll ke sana agar alert terlihat.
(function checkHashOnLoad() {
  if (window.location.hash === '#kontak') {
    const target = document.querySelector('#kontak');
    if (!target) return;
    setTimeout(() => {
      window.scrollTo({
        top: target.offsetTop - 80,
        behavior: 'smooth',
      });
    }, 400);
  }
}());


// ── 4. CAROUSEL / HERO SLIDER ───────────────────────────────

/**
 * Pindah ke slide dengan index tertentu.
 * @param {number} index
 */
function goToSlide(index) {
  if (!carouselSlides.length) return;

  carouselSlides[currentSlide].classList.remove('hero_slide--active');
  carouselDots[currentSlide]?.classList.remove('hero_dot--active');

  currentSlide = (index + carouselSlides.length) % carouselSlides.length;

  carouselSlides[currentSlide].classList.add('hero_slide--active');
  carouselDots[currentSlide]?.classList.add('hero_dot--active');
}

function stopAutoPlay() { clearInterval(autoPlayTimer); }

function startAutoPlay() {
  stopAutoPlay();
  autoPlayTimer = setInterval(() => goToSlide(currentSlide + 1), 5000);
}

/** Ganti slide secara manual (reset timer autoplay). */
function changeSlide(index) {
  stopAutoPlay();
  goToSlide(index);
  startAutoPlay();
}

// Klik dots
carouselDots.forEach((dot, i) => {
  dot.addEventListener('click', () => changeSlide(i));
});

// Tombol prev / next
heroPrev?.addEventListener('click', () => changeSlide(currentSlide - 1));
heroNext?.addEventListener('click', () => changeSlide(currentSlide + 1));

// Swipe mobile (touch)
(function initSwipe() {
  const carousel = document.getElementById('carousel');
  if (!carousel) return;

  let startX = 0;

  carousel.addEventListener('touchstart', e => {
    startX = e.touches[0].clientX;
  }, { passive: true });

  carousel.addEventListener('touchend', e => {
    const dist = startX - e.changedTouches[0].clientX;
    if (Math.abs(dist) <= 40) return;
    changeSlide(dist > 0 ? currentSlide + 1 : currentSlide - 1);
  }, { passive: true });
}());

// Mulai autoplay saat halaman selesai dimuat
startAutoPlay();


// ── 5. NAVBAR SCROLL EFFECT ─────────────────────────────────

function handleNavbarScroll() {
  navbar?.classList.toggle('scrolled', window.scrollY > 40);
}

window.addEventListener('scroll', handleNavbarScroll, { passive: true });
handleNavbarScroll(); // jalankan sekali saat load


// ── 6. ACTIVE LINK NAVBAR ───────────────────────────────────

function updateActiveNavbar() {
  let current = '';

  sections.forEach(section => {
    if (window.scrollY >= section.offsetTop - 120) {
      current = section.getAttribute('id');
    }
  });

  navLinks.forEach(link => {
    const targetId = link.getAttribute('href').replace('#', '');
    link.classList.toggle('active', targetId === current);
  });
}

window.addEventListener('scroll', updateActiveNavbar, { passive: true });
updateActiveNavbar();


// ── 7. MOBILE DRAWER ────────────────────────────────────────

function openDrawer() {
  drawerOpen = true;
  drawer?.classList.add('open');
  drawer?.setAttribute('aria-hidden', 'false');
  hamburger?.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeDrawer() {
  drawerOpen = false;
  drawer?.classList.remove('open');
  drawer?.setAttribute('aria-hidden', 'true');
  hamburger?.classList.remove('open');
  document.body.style.overflow = '';
}

hamburger?.addEventListener('click', () => {
  drawerOpen ? closeDrawer() : openDrawer();
});

drawerLinks.forEach(link => link.addEventListener('click', closeDrawer));

// Tutup drawer saat menekan Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    if (drawerOpen) closeDrawer();
    if (lightbox?.classList.contains('open')) closeLightbox();
  }
});


// ── 8. SMOOTH SCROLL ────────────────────────────────────────

allAnchors.forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const href   = this.getAttribute('href');
    const target = document.querySelector(href);
    if (!target) return;

    e.preventDefault();
    const top = target.getBoundingClientRect().top + window.pageYOffset - 80;
    window.scrollTo({ top, behavior: 'smooth' });
  });
});


// ── 9. SCROLL REVEAL ────────────────────────────────────────

function handleReveal() {
  reveals.forEach(el => {
    if (el.getBoundingClientRect().top >= window.innerHeight - 100) return;

    // Tunda berdasarkan urutan elemen dalam parent-nya
    const siblings   = Array.from(el.parentElement.querySelectorAll('.reveal'));
    const idx        = siblings.indexOf(el);
    el.style.transitionDelay = `${idx * 0.08}s`;
    el.classList.add('visible');
  });
}

window.addEventListener('scroll', handleReveal, { passive: true });
handleReveal(); // langsung jalankan sekali


// ── 10. BACK TO TOP ─────────────────────────────────────────

function handleBackToTop() {
  backToTopBtn?.classList.toggle('visible', window.scrollY > 400);
}

window.addEventListener('scroll', handleBackToTop, { passive: true });

backToTopBtn?.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});


// ── 11. LIGHTBOX GALERI ─────────────────────────────────────

galleryImages.forEach(img => {
  img.addEventListener('click', () => {
    if (!lightbox || !lightboxImage) return;
    lightboxImage.src = img.src;
    lightboxImage.alt = img.alt;
    lightbox.classList.add('open');
    document.body.style.overflow = 'hidden';
  });
});

function closeLightbox() {
  lightbox?.classList.remove('open');
  document.body.style.overflow = '';
}

lightboxClose?.addEventListener('click', closeLightbox);

lightbox?.addEventListener('click', e => {
  if (e.target === lightbox) closeLightbox();
});


// ── 12. VALIDASI FORM SISI CLIENT (opsional / tambahan) ─────
//
// Catatan: Validasi utama sudah dilakukan server-side di
// process_contact.php menggunakan PHP.
// Validasi JS di sini hanya sebagai UX tambahan agar
// pengguna mendapat feedback instan SEBELUM submit ke server.
// Jika JS dinonaktifkan, form tetap aman karena PHP memvalidasi.
//
// Form menggunakan action="process_contact.php" method="POST",
// jadi submit diteruskan ke PHP — tidak ada manipulasi WA URL
// di sisi JS lagi (berbeda dari versi HTML sebelumnya).
// ─────────────────────────────────────────────────────────────

if (contactForm) {

  contactForm.addEventListener('submit', function (e) {

    const get = id => document.getElementById(id);

    const nameEl     = get('name');
    const ageEl      = get('age');
    const genderEl   = get('gender');
    const guardianEl = get('guardian');
    const waEl       = get('whatsapp');
    const addressEl  = get('address');
    const messageEl  = get('message');

    const name     = nameEl?.value.trim()     ?? '';
    const age      = ageEl?.value.trim()      ?? '';
    const gender   = genderEl?.value.trim()   ?? '';
    const guardian = guardianEl?.value.trim() ?? '';
    const phone    = waEl?.value.trim()       ?? '';
    const address  = addressEl?.value.trim()  ?? '';
    const message  = messageEl?.value.trim()  ?? '';

    // Validasi wajib isi
    if (!name || !age || !gender || !guardian || !phone || !address || !message) {
      e.preventDefault();
      showToast('Semua field wajib diisi.');
      return;
    }

    // Nama minimal 3 karakter
    if (name.length < 3) {
      e.preventDefault();
      showToast('Nama minimal 3 karakter.');
      nameEl?.focus();
      return;
    }

    // Usia 3–17
    const ageNum = Number(age);
    if (ageNum < 3 || ageNum > 17) {
      e.preventDefault();
      showToast('Usia harus 3–17 tahun.');
      ageEl?.focus();
      return;
    }

    // Nama wali minimal 3 karakter
    if (guardian.length < 3) {
      e.preventDefault();
      showToast('Nama wali minimal 3 karakter.');
      guardianEl?.focus();
      return;
    }

    // Format nomor WhatsApp: 08xxxxxxxx
    if (!/^08[0-9]{8,11}$/.test(phone)) {
      e.preventDefault();
      showToast('Nomor WhatsApp tidak valid. Contoh: 081234567890');
      waEl?.focus();
      return;
    }

    // Alamat minimal 10 karakter
    if (address.length < 10) {
      e.preventDefault();
      showToast('Alamat terlalu pendek.');
      addressEl?.focus();
      return;
    }

    // Pesan minimal 10 karakter
    if (message.length < 10) {
      e.preventDefault();
      showToast('Pesan minimal 10 karakter.');
      messageEl?.focus();
      return;
    }

    // Semua validasi JS lolos → biarkan form submit ke PHP
    // PHP akan memvalidasi ulang dan membuka WhatsApp.
    showToast('Mengirim pesan…');
  });

}