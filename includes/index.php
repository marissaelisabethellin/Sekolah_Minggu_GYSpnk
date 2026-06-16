<?php

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data/jenjang.php';   // → $jenjangList
require_once __DIR__ . '/data/galeri.php';     // → $galeriList

$formError   = $_SESSION['form_error']   ?? null;
$formSuccess = $_SESSION['form_success'] ?? null;
$formValues  = $_SESSION['form_values']  ?? [];

unset($_SESSION['form_error'], $_SESSION['form_success'], $_SESSION['form_values']);

$heroSlides = [
    ['src' => 'gambar/gambar depan gereja.jpeg',    'alt' => 'Gereja Yesus Sejati Pontianak'],
    ['src' => 'gambar/Tahun baru anak (2).jpg (1).jpeg', 'alt' => 'Tahun baru anak'],
    ['src' => 'gambar/Kelas remaja 2025.jpg.jpeg',  'alt' => 'Kelas remaja 2025'],
];
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>


<!-- ==== HERO ==== -->
<section id="home" class="hero">

    <!-- Carousel -->
    <div class="hero_carousel" id="carousel">
        <?php foreach ($heroSlides as $i => $slide): ?>
            <div class="hero_slide <?= $i === 0 ? 'hero_slide--active' : '' ?>">
                <img src="<?= htmlspecialchars($slide['src']) ?>"
                    alt="<?= htmlspecialchars($slide['alt']) ?>" />
            </div>
        <?php endforeach; ?>
        <div class="hero_overlay"></div>
    </div>

    <!-- Tombol navigasi carousel -->
    <button class="hero_arrow hero_arrow--prev" id="heroPrev" aria-label="Slide sebelumnya">&#8249;</button>
    <button class="hero_arrow hero_arrow--next" id="heroNext" aria-label="Slide berikutnya">&#8250;</button>

    <!-- Konten teks -->
    <div class="hero_content container">
        <div class="hero_text">
            <h1 class="hero_title anim-fade-up">
                Sekolah Minggu<br />Gereja Yesus Sejati<br />Pontianak
            </h1>
            <p class="hero_desc anim-fade-up" style="animation-delay:.15s">
                <?= htmlspecialchars(SITE_TAGLINE) ?>
            </p>

            <blockquote class="hero_quote anim-fade-up" style="animation-delay:.3s">
                <span class="material-symbols-outlined hero_quote-icon">format_quote</span>
                <p>
                    <?= htmlspecialchars(HERO_QUOTE) ?>
                    <cite><?= htmlspecialchars(HERO_QUOTE_SRC) ?></cite>
                </p>
            </blockquote>

            <div class="hero_actions anim-fade-up" style="animation-delay:.45s">
                <a href="#kontak" class="btn btn--sky">Daftar Sekarang</a>
                <a href="#jenjang" class="btn btn--ghost">Lihat Kelas</a>
            </div>
        </div>
    </div>

    <!-- Dots indikator -->
    <div class="hero_dots" id="carousel-dots">
        <?php foreach ($heroSlides as $i => $slide): ?>
            <button class="hero_dot <?= $i === 0 ? 'hero_dot--active' : '' ?>"
                aria-label="Slide <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>

</section>


<!-- ========================================================
     TENTANG
======================================================== -->
<section id="tentang" class="section">
    <div class="container">

        <div class="section_label reveal">Visi &amp; Misi</div>
        <h2 class="section_title reveal">Membangun Fondasi Iman</h2>
        <p class="section_description reveal">
            <?= htmlspecialchars(SITE_SHORT) ?> membantu anak-anak berkembang
            dalam iman melalui firman Tuhan, pujian, doa, dan tindakan kasih,
            sehingga mereka dapat membentuk karakter dan menjadi berkat bagi sesama.
        </p>

        <div class="visi-misi-grid">
            <div class="vm-card reveal">
                <div class="vm-card_icon"><span class="material-symbols-outlined">visibility</span></div>
                <h3 class="vm-card_title">Visi Kami</h3>
                <p class="vm-card_body">
                    Membentuk generasi muda yang berakar dalam kebenaran firman Tuhan, dipimpin oleh Roh Kudus,
                    bertumbuh dalam iman yang hidup, dan menjadi pelaku firman Tuhan dalam setiap aspek kehidupan mereka.
                </p>
            </div>

            <div class="vm-card reveal">
                <div class="vm-card_icon"><span class="material-symbols-outlined">flag</span></div>
                <h3 class="vm-card_title">Misi Kami</h3>
                <ul class="vm-card_list">
                    <li>Mengajarkan firman Tuhan sebagai dasar iman dan hidup.</li>
                    <li>Membimbing anak mengenal Tuhan dan hidup dalam Roh Kudus.</li>
                    <li>Membentuk karakter kasih, kebenaran, dan hidup Kristus.</li>
                </ul>
            </div>
        </div>

    </div>
</section>


<!-- ========================================================
     JENJANG KELAS  ← dirender dari $jenjangList (data/jenjang.php)
======================================================== -->
<section id="jenjang" class="section section--lilac">
    <div class="container">

        <div class="section_label reveal">Pembelajaran</div>
        <h2 class="section_title reveal">Jenjang Kelas Sekolah Minggu</h2>

        <div class="kelas-grid">
            <?php foreach ($jenjangList as $kelas): ?>
                <article class="kelas-card reveal">
                    <div class="kelas-card_img-wrap">
                        <img src="<?= htmlspecialchars($kelas['gambar']) ?>"
                            alt="<?= htmlspecialchars($kelas['alt']) ?>" />
                        <span class="kelas-card_badge"><?= htmlspecialchars($kelas['usia']) ?></span>
                    </div>
                    <div class="kelas-card_body">
                        <h3 class="kelas-card_name"><?= htmlspecialchars($kelas['nama']) ?></h3>
                        <p class="kelas-card_desc"><?= htmlspecialchars($kelas['deskripsi']) ?></p>
                        <div class="kelas-card_tag">
                            <span class="material-symbols-outlined"><?= htmlspecialchars($kelas['tag_icon']) ?></span>
                            <?= htmlspecialchars($kelas['tag_label']) ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    </div>
</section>


<!-- ========================================================
     JADWAL
======================================================== -->
<section id="jadwal" class="section">
    <div class="container">
        <div class="jadwal-card reveal">
            <div class="jadwal-card_deco"></div>

            <div class="jadwal-card_left">
                <span class="jadwal-card_label">Waktu Ibadah</span>
                <h2 class="jadwal-card_title">Jadwal Sekolah Minggu Rutin</h2>

                <div class="jadwal-card_info-list">
                    <div class="jadwal-info">
                        <div class="jadwal-info_icon">
                            <span class="material-symbols-outlined">calendar_today</span>
                        </div>
                        <div>
                            <p class="jadwal-info_sub">Hari</p>
                            <p class="jadwal-info_val">Setiap Minggu</p>
                        </div>
                    </div>
                    <div class="jadwal-info">
                        <div class="jadwal-info_icon">
                            <span class="material-symbols-outlined">schedule</span>
                        </div>
                        <div>
                            <p class="jadwal-info_sub">Jam</p>
                            <p class="jadwal-info_val">08:15 – 10:00 WIB</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="jadwal-card_right">
                <p class="jadwal-card_loc-label">Lokasi Beribadah</p>
                <h3 class="jadwal-card_loc-name">Gedung Yesus Sejati Pontianak</h3>
                <a href="<?= SITE_MAPS_URL ?>" target="_blank" rel="noopener" class="jadwal-card_map-link">
                    Petunjuk Arah <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>
</section>


<!-- ========================================================
     GALERI  ← dirender dari $galeriList (data/galeri.php)
======================================================== -->
<section id="galeri" class="section section--plum">
    <div class="container">

        <div class="section_label reveal">Momen Berharga</div>
        <h2 class="section_title reveal">Galeri Aktivitas</h2>

        <div class="galeri-grid">
            <?php foreach ($galeriList as $foto): ?>
                <div class="galeri-item reveal">
                    <img src="<?= htmlspecialchars($foto['src']) ?>"
                        alt="<?= htmlspecialchars($foto['alt']) ?>" />
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>


<!-- ========================================================
     KONTAK
======================================================== -->
<section id="kontak" class="section">
    <div class="container">
        <div class="kontak-grid">

            <!-- Info kontak kiri -->
            <div class="kontak-info">
                <h2 class="kontak-info_title reveal">Mari Bergabung Bersama Kami</h2>
                <p class="kontak-info_desc reveal">
                    Punya pertanyaan seputar kurikulum, pendaftaran, atau ingin berkunjung
                    untuk melihat suasana kelas? Silakan hubungi kami.
                </p>

                <ul class="kontak-list">
                    <li class="kontak-item reveal">
                        <div class="kontak-item_icon"><span class="material-symbols-outlined">location_on</span></div>
                        <div>
                            <h4 class="kontak-item_label">Alamat</h4>
                            <p class="kontak-item_val"><?= htmlspecialchars(SITE_ADDRESS) ?></p>
                        </div>
                    </li>
                    <li class="kontak-item reveal">
                        <div class="kontak-item_icon"><span class="material-symbols-outlined">mail</span></div>
                        <div>
                            <h4 class="kontak-item_label">Email</h4>
                            <p class="kontak-item_val"><?= htmlspecialchars(SITE_EMAIL) ?></p>
                        </div>
                    </li>
                    <li class="kontak-item reveal">
                        <div class="kontak-item_icon"><span class="material-symbols-outlined">phone_iphone</span></div>
                        <div>
                            <h4 class="kontak-item_label">WhatsApp</h4>
                            <p class="kontak-item_val"><?= htmlspecialchars(SITE_WA_DISPLAY) ?></p>
                        </div>
                    </li>
                </ul>

                <div class="kontak-map reveal">
                    <iframe src="<?= SITE_MAPS_EMBED ?>"
                        width="600" height="450" style="border:0;"
                        allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

            <!-- Form kanan -->
            <div class="kontak-form-wrap reveal">
                <h3 class="kontak-form-wrap_title">Kirim Pesan</h3>

                <?php if ($formSuccess): ?>
                    <div class="alert alert--success">✅ <?= htmlspecialchars($formSuccess) ?></div>
                <?php endif; ?>
                <?php if ($formError): ?>
                    <div class="alert alert--error">⚠️ <?= htmlspecialchars($formError) ?></div>
                <?php endif; ?>

                <!--
          action → process_contact.php (validasi server-side PHP)
          method → POST (data tidak tampil di URL)
        -->
                <form id="contactForm" class="kontak-form"
                    action="process_contact.php" method="POST" novalidate>

                    <!-- Nama -->
                    <div class="form-group">
                        <label class="form-label" for="name">Nama Lengkap</label>
                        <input class="form-input" type="text" id="name" name="name"
                            placeholder="Masukkan nama Anda" required
                            value="<?= htmlspecialchars($formValues['name'] ?? '') ?>" />
                    </div>

                    <!-- Usia -->
                    <div class="form-group">
                        <label class="form-label" for="age">Usia</label>
                        <input class="form-input" type="number" id="age" name="age"
                            placeholder="Contoh: 8" min="3" max="17" required
                            value="<?= htmlspecialchars($formValues['age'] ?? '') ?>" />
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="form-group">
                        <label class="form-label" for="gender">Jenis Kelamin</label>
                        <select class="form-input" id="gender" name="gender" required>
                            <option value="">Pilih jenis kelamin</option>
                            <?php foreach (['Laki-laki', 'Perempuan'] as $opt): ?>
                                <option value="<?= $opt ?>"
                                    <?= (($formValues['gender'] ?? '') === $opt) ? 'selected' : '' ?>>
                                    <?= $opt ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Orang Tua / Wali -->
                    <div class="form-group">
                        <label class="form-label" for="guardian">Nama Orang Tua / Wali</label>
                        <input class="form-input" type="text" id="guardian" name="guardian"
                            placeholder="Masukkan nama orang tua / wali" required
                            value="<?= htmlspecialchars($formValues['guardian'] ?? '') ?>" />
                    </div>

                    <!-- WhatsApp -->
                    <div class="form-group">
                        <label class="form-label" for="whatsapp">Nomor WhatsApp</label>
                        <input class="form-input" type="tel" id="whatsapp" name="whatsapp"
                            placeholder="Contoh: 081234567890" required
                            value="<?= htmlspecialchars($formValues['whatsapp'] ?? '') ?>" />
                    </div>

                    <!-- Alamat -->
                    <div class="form-group">
                        <label class="form-label" for="address">Alamat Tempat Tinggal</label>
                        <textarea class="form-textarea" id="address" name="address"
                            rows="3" placeholder="Masukkan alamat tempat tinggal" required><?= htmlspecialchars($formValues['address'] ?? '') ?></textarea>
                    </div>

                    <!-- Pesan -->
                    <div class="form-group">
                        <label class="form-label" for="message">Pesan Anda</label>
                        <textarea class="form-textarea" id="message" name="message"
                            rows="4" placeholder="Tuliskan pertanyaan Anda..." required><?= htmlspecialchars($formValues['message'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn--sky btn--full btn--lg">
                        Kirim ke WhatsApp
                        <span class="material-symbols-outlined">send</span>
                    </button>

                </form>
            </div>

        </div>
    </div>
</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>


<!-- Elemen pendukung JS -->
<button id="backToTop" class="back-to-top" aria-label="Kembali ke atas">↑</button>

<div id="lightbox" class="lightbox">
    <button id="lightboxClose" class="lightbox_close" aria-label="Tutup gambar">×</button>
    <img id="lightboxImage" class="lightbox_image" alt="Preview gambar" />
</div>

<div id="toast" class="toast"></div>

<script src="script.js"></script>
</body>

</html>