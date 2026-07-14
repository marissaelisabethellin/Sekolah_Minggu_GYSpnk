<?php
// ============================================================
//  INDEX.PHP — Halaman Utama (data dari MySQL)
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config/db.php';

// ── Ambil data dari database ─────────────────────────────────
$heroPrimary = null;
$heroImages  = [];
try {
    $pdo = db();

    $jenjangList = $pdo
        ->query('SELECT * FROM jenjang WHERE aktif = 1 ORDER BY urutan ASC, id ASC')
        ->fetchAll(PDO::FETCH_ASSOC);

    $galeriList = $pdo
        ->query('SELECT * FROM galeri WHERE aktif = 1 ORDER BY urutan ASC, id ASC')
        ->fetchAll(PDO::FETCH_ASSOC);

    // Hanya 1 baris bagian_utama yang aktif sekaligus (sesuai desain ERD).
    $heroPrimary = $pdo
        ->query('SELECT * FROM bagian_utama WHERE aktif = 1 ORDER BY id DESC LIMIT 1')
        ->fetch(PDO::FETCH_ASSOC) ?: null;

    // Relasi 1-ke-banyak: semua gambar carousel milik konten hero aktif
    // tersebut (dikelola dari admin/bagian_utama.php -> "Gambar Carousel").
    if ($heroPrimary) {
        $gStmt = $pdo->prepare(
            'SELECT * FROM bagian_utama_gambar WHERE id_bagian_utama = ? ORDER BY urutan ASC, id ASC'
        );
        $gStmt->execute([$heroPrimary['id']]);
        $heroImages = $gStmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Throwable $e) {
    // Fallback ke array statis jika DB belum siap
    require_once __DIR__ . '/data/jenjang.php';
    require_once __DIR__ . '/data/galeri.php';
    $heroPrimary = null;
    $heroImages  = [];
}

// ── Flash message dari process_contact.php ───────────────────
$formError   = $_SESSION['form_error']   ?? null;
$formSuccess = $_SESSION['form_success'] ?? null;
$formValues  = $_SESSION['form_values']  ?? [];
unset($_SESSION['form_error'], $_SESSION['form_success'], $_SESSION['form_values']);

// ── Konten & slide hero (dari tabel bagian_utama + bagian_utama_gambar) ──
$heroSlides = $heroImages ? array_map(
    fn($g) => [
        'sumber'          => $g['gambar'],
        'teks_alternatif' => $g['teks_alternatif'] ?: 'Sekolah Minggu Gereja Yesus Sejati Pontianak',
    ],
    $heroImages
) : [
    // Fallback statis apabila admin belum mengisi gambar carousel
    ['sumber' => 'gambar/gambar depan gereja.jpeg',         'teks_alternatif' => 'Gereja Yesus Sejati Pontianak'],
    ['sumber' => 'gambar/Tahun baru anak (2).jpg (1).jpeg', 'teks_alternatif' => 'Tahun Baru Anak'],
    ['sumber' => 'gambar/Kelas remaja 2025.jpg.jpeg',       'teks_alternatif' => 'Kelas Remaja 2025'],
];

$heroJudul        = $heroPrimary['judul']         ?? "Sekolah Minggu\r\nGereja Yesus Sejati\r\nPontianak";
$heroSubjudul     = $heroPrimary['subjudul']      ?? SITE_TAGLINE;
$heroDeskripsi    = $heroPrimary['deskripsi']     ?? (HERO_QUOTE . "\r\n" . HERO_QUOTE_SRC);
$heroTeksTombol   = $heroPrimary['teks_tombol']   ?: 'Daftar Sekarang';
$heroTautanTombol = $heroPrimary['tautan_tombol'] ?: '#kontak';

/**
 * Deskripsi hero disimpan sebagai satu blok teks; baris terakhir yang
 * diawali tanda kutip sumber (mis. "— Matius 19:14") dipisah otomatis
 * agar bisa ditampilkan sebagai <cite>, konsisten dengan data contoh
 * pada tabel bagian_utama.
 */
function pisahKutipanHero(string $teks): array
{
    $baris = preg_split('/\r\n|\r|\n/', trim($teks));
    $sitasi = '';
    if (count($baris) > 1) {
        $barisTerakhir = trim(end($baris));
        if ($barisTerakhir !== '' && (str_starts_with($barisTerakhir, '—') || str_starts_with($barisTerakhir, '-'))) {
            $sitasi = array_pop($baris);
        }
    }
    return ['isi' => implode(' ', $baris), 'sitasi' => $sitasi];
}

$heroQuote = pisahKutipanHero($heroDeskripsi);
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>


<!-- ======================================================
     HERO
====================================================== -->
<section id="home" class="hero">

  <div class="hero_carousel" id="carousel">
    <?php foreach ($heroSlides as $i => $slide): ?>
      <div class="hero_slide <?= $i === 0 ? 'hero_slide--active' : '' ?>">
        <img src="<?= htmlspecialchars($slide['sumber']) ?>"
             alt="<?= htmlspecialchars($slide['teks_alternatif']) ?>"/>
      </div>
    <?php endforeach; ?>
    <div class="hero_overlay"></div>
  </div>

  <button class="hero_arrow hero_arrow--prev" id="heroPrev" aria-label="Slide sebelumnya">&#8249;</button>
  <button class="hero_arrow hero_arrow--next" id="heroNext" aria-label="Slide berikutnya">&#8250;</button>

  <div class="hero_content container">
    <div class="hero_text">
      <h1 class="hero_title anim-fade-up">
        <?= nl2br(htmlspecialchars($heroJudul)) ?>
      </h1>
      <p class="hero_desc anim-fade-up" style="animation-delay:.15s">
        <?= htmlspecialchars($heroSubjudul) ?>
      </p>
      <blockquote class="hero_quote anim-fade-up" style="animation-delay:.3s">
        <span class="material-symbols-outlined hero_quote-icon">format_quote</span>
        <p>
          <?= htmlspecialchars($heroQuote['isi']) ?>
          <?php if ($heroQuote['sitasi']): ?>
            <cite><?= htmlspecialchars($heroQuote['sitasi']) ?></cite>
          <?php endif; ?>
        </p>
      </blockquote>
      <div class="hero_actions anim-fade-up" style="animation-delay:.45s">
        <a href="<?= htmlspecialchars($heroTautanTombol) ?>" class="btn btn--sky"><?= htmlspecialchars($heroTeksTombol) ?></a>
        <a href="#jenjang" class="btn btn--ghost">Lihat Kelas</a>
      </div>
    </div>
  </div>

  <div class="hero_dots" id="carousel-dots">
    <?php foreach ($heroSlides as $i => $slide): ?>
      <button class="hero_dot <?= $i === 0 ? 'hero_dot--active' : '' ?>"
              aria-label="Slide <?= $i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>

</section>


<!-- ======================================================
     TENTANG
====================================================== -->
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
          Membentuk generasi muda yang berakar dalam kebenaran firman Tuhan, dipimpin oleh
          Roh Kudus, bertumbuh dalam iman yang hidup, dan menjadi pelaku firman Tuhan dalam
          setiap aspek kehidupan mereka.
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


<!-- ======================================================
     JENJANG KELAS — data dari MySQL via $jenjangList
====================================================== -->
<section id="jenjang" class="section section--lilac">
  <div class="container">
    <div class="section_label reveal">Pembelajaran</div>
    <h2 class="section_title reveal">Jenjang Kelas Sekolah Minggu</h2>

    <div class="kelas-grid">
      <?php foreach ($jenjangList as $kelas): ?>
        <article class="kelas-card reveal">
          <div class="kelas-card_img-wrap">
            <img src="<?= htmlspecialchars($kelas['gambar']) ?>"
                 alt="<?= htmlspecialchars($kelas['teks_alternatif']) ?>"/>
            <span class="kelas-card_badge"><?= htmlspecialchars($kelas['usia']) ?></span>
          </div>
          <div class="kelas-card_body">
            <h3 class="kelas-card_name"><?= htmlspecialchars($kelas['nama']) ?></h3>
            <p class="kelas-card_desc"><?= htmlspecialchars($kelas['deskripsi']) ?></p>
            <div class="kelas-card_tag">
              <span class="material-symbols-outlined"><?= htmlspecialchars($kelas['ikon_label']) ?></span>
              <?= htmlspecialchars($kelas['label']) ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ======================================================
     JADWAL
====================================================== -->
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
        <h3 class="jadwal-card_loc-name">Gereja Yesus Sejati Pontianak</h3>
        <a href="<?= SITE_MAPS_URL ?>" target="_blank" rel="noopener" class="jadwal-card_map-link">
          Petunjuk Arah <span class="material-symbols-outlined">arrow_forward</span>
        </a>
      </div>
    </div>
  </div>
</section>


<!-- ======================================================
     GALERI — data dari MySQL via $galeriList
====================================================== -->
<section id="galeri" class="section section--plum">
  <div class="container">
    <div class="section_label reveal">Momen Berharga</div>
    <h2 class="section_title reveal">Galeri Aktivitas</h2>

    <div class="galeri-grid">
      <?php foreach ($galeriList as $foto): ?>
        <div class="galeri-item reveal">
          <img src="<?= htmlspecialchars($foto['sumber']) ?>"
               alt="<?= htmlspecialchars($foto['teks_alternatif']) ?>"/>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ======================================================
     KONTAK
====================================================== -->
<section id="kontak" class="section">
  <div class="container">
    <div class="kontak-grid">

      <!-- Info kiri -->
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
          <div class="alert alert--success">&#10003; <?= htmlspecialchars($formSuccess) ?></div>
        <?php endif; ?>
        <?php if ($formError): ?>
          <div class="alert alert--error">&#9888; <?= htmlspecialchars($formError) ?></div>
        <?php endif; ?>

        <form id="contactForm" class="kontak-form"
              action="process_contact.php" method="POST" novalidate>

          <div class="form-group">
            <label class="form-label" for="name">Nama Lengkap</label>
            <input class="form-input" type="text" id="name" name="name"
                   placeholder="Masukkan nama Anda" required
                   value="<?= htmlspecialchars($formValues['name'] ?? '') ?>"/>
          </div>

          <div class="form-group">
            <label class="form-label" for="age">Usia</label>
            <input class="form-input" type="number" id="age" name="age"
                   placeholder="Contoh: 8" min="3" max="17" required
                   value="<?= htmlspecialchars($formValues['age'] ?? '') ?>"/>
          </div>

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

          <div class="form-group">
            <label class="form-label" for="guardian">Nama Orang Tua / Wali</label>
            <input class="form-input" type="text" id="guardian" name="guardian"
                   placeholder="Masukkan nama orang tua / wali" required
                   value="<?= htmlspecialchars($formValues['guardian'] ?? '') ?>"/>
          </div>

          <div class="form-group">
            <label class="form-label" for="whatsapp">Nomor WhatsApp</label>
            <input class="form-input" type="tel" id="whatsapp" name="whatsapp"
                   placeholder="Contoh: 081234567890" required
                   value="<?= htmlspecialchars($formValues['whatsapp'] ?? '') ?>"/>
          </div>

          <div class="form-group">
            <label class="form-label" for="address">Alamat Tempat Tinggal</label>
            <textarea class="form-textarea" id="address" name="address"
                      rows="3" placeholder="Masukkan alamat tempat tinggal" required
                      ><?= htmlspecialchars($formValues['address'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label" for="message">Pesan Anda</label>
            <textarea class="form-textarea" id="message" name="message"
                      rows="4" placeholder="Tuliskan pertanyaan Anda..." required
                      ><?= htmlspecialchars($formValues['message'] ?? '') ?></textarea>
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

<button id="backToTop" class="back-to-top" aria-label="Kembali ke atas">&#8593;</button>

<div id="lightbox" class="lightbox">
  <button id="lightboxClose" class="lightbox_close" aria-label="Tutup gambar">&times;</button>
  <img id="lightboxImage" class="lightbox_image" alt="Preview gambar"/>
</div>

<div id="toast" class="toast"></div>

<script src="js/script.js"></script>
</body>
</html>
