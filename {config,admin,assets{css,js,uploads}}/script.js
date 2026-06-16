// DOM ELEMENTS Mengambil elemen HTML agar bisa dimanipulasi JS
const navbar = document.getElementById('navbar');

const hamburger = document.getElementById('hamburger');
const drawer = document.getElementById('drawer');
const drawerLinks = document.querySelectorAll('.drawer_link');

const carouselSlides = document.querySelectorAll('.hero_slide');
const carouselDots = document.querySelectorAll('.hero_dot');
const heroPrev = document.getElementById('heroPrev');
const heroNext = document.getElementById('heroNext');

const contactForm = document.getElementById('contactForm');

const allAnchors = document.querySelectorAll('a[href^="#"]');
const reveals = document.querySelectorAll('.reveal');

const sections = document.querySelectorAll('section');
const navLinks = document.querySelectorAll('.navbar_link');

const backToTopButton =
  document.getElementById('backToTop');

const galleryImages =
  document.querySelectorAll('.galeri-item img');

const lightbox =
  document.getElementById('lightbox');

const lightboxImage =
  document.getElementById('lightboxImage');

const lightboxClose =
  document.getElementById('lightboxClose');

const toast =
  document.getElementById('toast');


// STATE STATE (DATA SEMENTARA) Menyimpan kondisi aplikasi saat berjalan
let currentSlide = 0;
let autoPlayTimer = null;
let drawerOpen = false;


// TOAST (NOTIFIKASI) Menampilkan pesan sementara ke user
function showToast(message) {
  if (!toast) {
    return;
  }

  toast.textContent = message;

  toast.classList.add('show');

  setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}


// CAROUSEL (SLIDER) Mengatur perpindahan slide otomatis & manual
function goToSlide(index) {
  if (!carouselSlides.length || !carouselDots.length) {
    return;
  }

  carouselSlides[currentSlide]
    .classList.remove('hero_slide--active');

  carouselDots[currentSlide]
    .classList.remove('hero_dot--active');

  currentSlide =
    (index + carouselSlides.length) %
    carouselSlides.length;

  carouselSlides[currentSlide]
    .classList.add('hero_slide--active');

  carouselDots[currentSlide]
    .classList.add('hero_dot--active');
}

function stopAutoPlay() {
  clearInterval(autoPlayTimer);
}

function startAutoPlay() {
  stopAutoPlay();

  autoPlayTimer = setInterval(() => {
    goToSlide(currentSlide + 1);
  }, 5000);
}

function changeSlide(index) {
  stopAutoPlay();

  goToSlide(index);

  startAutoPlay();
}

// Klik dots
carouselDots.forEach((dot, index) => {
  dot.addEventListener('click', () => {
    changeSlide(index);
  });
});

// Tombol prev
if (heroPrev) {
  heroPrev.addEventListener('click', () => {
    changeSlide(currentSlide - 1);
  });
}

// Tombol next
if (heroNext) {
  heroNext.addEventListener('click', () => {
    changeSlide(currentSlide + 1);
  });
}

// Swipe mobile
(function () {
  const carousel =
    document.getElementById('carousel');

  if (!carousel) {
    return;
  }

  let touchStartX = 0;

  carousel.addEventListener(
    'touchstart',
    event => {
      touchStartX =
        event.touches[0].clientX;
    },
    { passive: true }
  );

  carousel.addEventListener(
    'touchend',
    event => {
      const touchEndX =
        event.changedTouches[0].clientX;

      const distance =
        touchStartX - touchEndX;

      if (Math.abs(distance) <= 40) {
        return;
      }

      // pindah slide manual
      changeSlide(
        distance > 0
          ? currentSlide + 1
          : currentSlide - 1
      );
    },
    { passive: true }
  );
}());

startAutoPlay();


// NAVBAR SCROLL EFFECT Memberi efek bayangan saat discroll
function handleNavbarScroll() {
  if (window.scrollY > 40) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
}

window.addEventListener(
  'scroll',
  handleNavbarScroll,
  { passive: true }
);


// ACTIVE NAVBAR Menandai menu sesuai section yang aktif
function updateActiveNavbar() {
  let currentSection = '';

  sections.forEach(section => {
    const sectionTop =
      section.offsetTop - 120;

    if (window.scrollY >= sectionTop) {
      currentSection =
        section.getAttribute('id');
    }
  });

  navLinks.forEach(link => {
    link.classList.remove('active');

    const targetId =
      link
        .getAttribute('href')
        .replace('#', '');

    if (targetId === currentSection) {
      link.classList.add('active');
    }
  });
}

window.addEventListener(
  'scroll',
  updateActiveNavbar,
  { passive: true }
);

updateActiveNavbar();


// MOBILE DRAWER (MENU HP) Mengatur buka/tutup menu hamburger
function openDrawer() {
  drawerOpen = true;

  drawer.classList.add('open');
  drawer.setAttribute(
    'aria-hidden',
    'false'
  );

  hamburger.classList.add('open');

  document.body.style.overflow =
    'hidden';
}

function closeDrawer() {
  drawerOpen = false;

  drawer.classList.remove('open');
  drawer.setAttribute(
    'aria-hidden',
    'true'
  );

  hamburger.classList.remove('open');

  document.body.style.overflow = '';
}

if (hamburger) {
  hamburger.addEventListener(
    'click',
    () => {
      drawerOpen
        ? closeDrawer()
        : openDrawer();
    }
  );
}

drawerLinks.forEach(link => {
  link.addEventListener(
    'click',
    closeDrawer
  );
});


// SMOOTH SCROLL  Scroll halus saat klik
allAnchors.forEach(anchor => {
  anchor.addEventListener(
    'click',
    function (event) {
      const href =
        this.getAttribute('href');

      const target =
        document.querySelector(href);

      if (!target) {
        return;
      }

      event.preventDefault();

      const offset = 80;

      const targetTop =
        target.getBoundingClientRect()
          .top +
        window.pageYOffset -
        offset;

      window.scrollTo({
        top: targetTop,
        behavior: 'smooth'
      });
    }
  );
});


// SCROLL REVEAL Animasi muncul saat discroll
function handleReveal() {
  reveals.forEach(element => {
    const rect =
      element.getBoundingClientRect();

    const triggerLine =
      window.innerHeight - 100;

    if (rect.top < triggerLine) {
      const siblings = Array.from(
        element.parentElement
          .querySelectorAll('.reveal')
      );

      const indexInGroup =
        siblings.indexOf(element);

      element.style.transitionDelay =
        `${indexInGroup * 0.08}s`;

      element.classList.add(
        'visible'
      );
    }
  });
}

window.addEventListener(
  'scroll',
  handleReveal,
  { passive: true }
);

handleReveal();


// BACK TO TOP Tombol kembali ke atas
function handleBackToTop() {
  if (!backToTopButton) {
    return;
  }

  if (window.scrollY > 400) {
    backToTopButton
      .classList.add('visible');
  } else {
    backToTopButton
      .classList.remove('visible');
  }
}

window.addEventListener(
  'scroll',
  handleBackToTop,
  { passive: true }
);

if (backToTopButton) {
  backToTopButton.addEventListener(
    'click',
    () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
  );
}


// LIGHTBOX GALLERY Menampilkan gambar dalam popup
galleryImages.forEach(image => {
  image.addEventListener(
    'click',
    () => {
      lightboxImage.src =
        image.src;

      lightbox.classList.add(
        'open'
      );

      document.body.style.overflow =
        'hidden';
    }
  );
});

function closeLightbox() {
  lightbox.classList.remove(
    'open'
  );

  document.body.style.overflow = '';
}

if (lightboxClose) {
  lightboxClose.addEventListener(
    'click',
    closeLightbox
  );
}

if (lightbox) {
  lightbox.addEventListener(
    'click',
    event => {
      if (event.target === lightbox) {
        closeLightbox();
      }
    }
  );
}


// CONTACT FORM Validasi input & kirim ke WhatsApp
if (contactForm) {
  contactForm.addEventListener(
    'submit',
    function (event) {
      event.preventDefault();

      const nameInput =
        document.getElementById('name');

      const ageInput =
        document.getElementById('age');

      const genderInput =
        document.getElementById('gender');

      const guardianInput =
        document.getElementById('guardian');

      const phoneInput =
        document.getElementById('whatsapp');

      const addressInput =
        document.getElementById('address');

      const messageInput =
        document.getElementById('message');

      const name =
        nameInput.value.trim();

      const age =
        ageInput.value.trim();

      const gender =
        genderInput.value.trim();

      const guardian =
        guardianInput.value.trim();

      const phone =
        phoneInput.value.trim();

      const address =
        addressInput.value.trim();

      const message =
        messageInput.value.trim();

      const phonePattern =
        /^08[0-9]{8,11}$/;

      // Validasi field kosong
      if (
        !name ||
        !age ||
        !gender ||
        !guardian ||
        !phone ||
        !address ||
        !message
      ) {
        showToast(
          'Semua field wajib diisi.'
        );
        return;
      }

      // Validasi nama
      if (name.length < 3) {
        showToast(
          'Nama minimal 3 karakter.'
        );

        nameInput.focus();

        return;
      }

      // Validasi usia
      if (
        Number(age) < 3 ||
        Number(age) > 17
      ) {
        showToast(
          'Usia harus 3–17 tahun.'
        );

        ageInput.focus();

        return;
      }

      // Validasi nama wali
      if (guardian.length < 3) {
        showToast(
          'Nama wali tidak valid.'
        );

        guardianInput.focus();

        return;
      }

      // Validasi WhatsApp
      if (
        !phonePattern.test(phone)
      ) {
        showToast(
          'Nomor WhatsApp tidak valid.'
        );

        phoneInput.focus();

        return;
      }

      // Validasi alamat
      if (address.length < 10) {
        showToast(
          'Alamat terlalu pendek.'
        );

        addressInput.focus();

        return;
      }

      // Validasi pesan
      if (message.length < 10) {
        showToast(
          'Pesan minimal 10 karakter.'
        );

        messageInput.focus();

        return;
      }

      // Format pesan WhatsApp
      const whatsappMessage =
      `Halo Sekolah Minggu GYS Pontianak,

      Nama Lengkap: ${name}
      Usia: ${age} tahun
      Jenis Kelamin: ${gender}
      Nama Orang Tua/Wali: ${guardian}
      Nomor WhatsApp: ${phone}
      Alamat: ${address}

      Pesan:
      ${message}`;

      const encodedMessage =
        encodeURIComponent(
          whatsappMessage
        );

      // Kirim ke WhatsApp
      window.open(
        `https://wa.me/6281345656929?text=${encodedMessage}`,
        '_blank'
      );

      showToast(
        'Pesan berhasil dikirim.'
      );

      contactForm.reset();
    }
  );
}