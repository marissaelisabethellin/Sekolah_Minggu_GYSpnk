<?php
// Data navigasi footer — sinkron secara manual jika ada perubahan menu
$footerNav = [
    '#home'    => 'Beranda',
    '#tentang' => 'Tentang Kami',
    '#jadwal'  => 'Jadwal Rutin',
    '#galeri'  => 'Galeri',
    '#kontak'  => 'Kontak',
];

// Data jenjang untuk kolom footer
$footerJenjang = [
    'Kelas Indria',
    'Kelas Pratama',
    'Kelas Madya',
    'Kelas Tunas Muda',
    'Kelas Remaja',
];
?>

<!-- === FOOTER === -->
<footer class="footer">
  <div class="container">
    <div class="footer_top">

      <!-- Kolom 1: Brand & deskripsi -->
      <div class="footer_brand-wrap">
        <div class="footer_brand">
          <div class="footer_brand-logo">
            <img src="gambar/burung_merpati_logo-removebg-preview.png"
                 alt="Logo Sekolah Minggu"/>
          </div>
          <div>
            <span class="footer_brand-name">Sekolah Minggu</span><br/>
            <span class="footer_brand-name"
                  style="font-weight:600;font-size:.88rem;color:var(--color-muted)">
              Gereja Yesus Sejati Pontianak
            </span>
          </div>
        </div>
        <p class="footer_tagline">
          Membina generasi muda yang takut akan Tuhan, memiliki akar iman yang kuat, dan berkarakter mulia dalam kehidupan sehari-hari.
        </p>
        <div class="footer_socials">
          <a href="<?= SITE_INSTAGRAM ?>" class="footer_social" title="Instagram" aria-label="Instagram" target="_blank" rel="noopener">
            <i class="fa-brands fa-instagram"></i>
          </a>
          <a href="<?= SITE_YOUTUBE ?>" class="footer_social" title="YouTube" aria-label="YouTube" target="_blank" rel="noopener">
            <i class="fa-brands fa-youtube"></i>
          </a>
          <a href="<?= SITE_TIKTOK ?>" class="footer_social" title="TikTok" aria-label="TikTok" target="_blank" rel="noopener">
            <i class="fa-brands fa-tiktok"></i>
          </a>
        </div>
      </div>

      <!-- Kolom 2: Navigasi -->
      <div>
        <span class="footer_col-heading">Navigasi</span>
        <ul>
          <?php foreach ($footerNav as $href => $label): ?>
            <li>
              <a href="<?= $href ?>" class="footer_nav-link">
                <?= htmlspecialchars($label) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Kolom 3: Jenjang Kelas -->
      <div>
        <span class="footer_col-heading">Jenjang Kelas</span>
        <ul>
          <?php foreach ($footerJenjang as $kelas): ?>
            <li>
              <a href="#jenjang" class="footer_nav-link">
                <?= htmlspecialchars($kelas) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Kolom 4: Kontak -->
      <div>
        <span class="footer_col-heading">Kontak</span>

        <div class="footer_contact-item">
          <span class="material-symbols-outlined">location_on</span>
          <span><?= htmlspecialchars(SITE_ADDRESS) ?></span>
        </div>
        <div class="footer_contact-item">
          <span class="material-symbols-outlined">schedule</span>
          <span><?= htmlspecialchars(SITE_SCHEDULE) ?></span>
        </div>
        <div class="footer_contact-item">
          <span class="material-symbols-outlined">mail</span>
          <span><?= htmlspecialchars(SITE_EMAIL) ?></span>
        </div>
        <div class="footer_contact-item">
          <span class="material-symbols-outlined">phone_iphone</span>
          <span><?= htmlspecialchars(SITE_WA_DISPLAY) ?></span>
        </div>
      </div>

    </div>

    <div class="footer_bottom">
      &copy; <?= CURRENT_YEAR ?> Gereja Yesus Sejati Pontianak. All rights reserved.
    </div>
  </div>
</footer>
