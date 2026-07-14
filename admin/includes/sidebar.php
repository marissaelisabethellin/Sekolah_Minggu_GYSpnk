<?php
// Hitung pesan belum dibaca untuk badge sidebar
$unread = 0;
try {
    $unread = (int) db()->query('SELECT COUNT(*) FROM pesan WHERE dibaca = 0')->fetchColumn();
} catch (Throwable) {}

$user       = currentUser();
$currentUri = $_SERVER['REQUEST_URI'] ?? '';

// Helper: apakah link ini aktif?
$isActive = fn(string $path) =>
    str_contains($currentUri, $path) ? 'active' : '';
?>

<aside class="sidebar" id="sidebar">

  <!-- Brand -->
  <div class="sidebar_brand">
    <div class="sidebar_brand-logo">
      <img src="<?= BASE_URL ?>/gambar/burung_merpati_logo-removebg-preview.png"
           alt="Logo"/>
    </div>
    <div class="sidebar_brand-text">
      <div class="sidebar_brand-name">GYS Pontianak</div>
      <div class="sidebar_brand-sub">Panel Admin</div>
    </div>
  </div>

  <!-- Navigasi -->
  <nav class="sidebar_nav">
    <div class="sidebar_section-label">Menu Utama</div>

    <a href="<?= BASE_URL ?>/admin/index.php"
       class="sidebar_link <?= $isActive('admin/index') ?>">
      <span class="material-symbols-outlined">dashboard</span>
      Dashboard
    </a>

    <a href="<?= BASE_URL ?>/admin/pesan.php"
       class="sidebar_link <?= $isActive('admin/pesan') ?>">
      <span class="material-symbols-outlined">mail</span>
      Pesan Masuk
      <?php if ($unread > 0): ?>
        <span class="sidebar_badge"><?= $unread ?></span>
      <?php endif; ?>
    </a>

    <div class="sidebar_section-label" style="margin-top:12px">Konten Website</div>

    

    <a href="<?= BASE_URL ?>/admin/jenjang.php"
       class="sidebar_link <?= $isActive('admin/jenjang') ?>">
      <span class="material-symbols-outlined">school</span>
      Jenjang Kelas
    </a>

    <a href="<?= BASE_URL ?>/admin/galeri.php"
       class="sidebar_link <?= $isActive('admin/galeri') ?>">
      <span class="material-symbols-outlined">photo_library</span>
      Galeri Foto
    </a>

    <?php if ($user['peran'] === 'super_admin'): ?>
      <div class="sidebar_section-label" style="margin-top:12px">Pengaturan</div>
      <a href="<?= BASE_URL ?>/admin/users.php"
         class="sidebar_link <?= $isActive('admin/users') ?>">
        <span class="material-symbols-outlined">manage_accounts</span>
        Kelola Pengguna
      </a>
    <?php endif; ?>

    <div class="sidebar_section-label" style="margin-top:12px">Lainnya</div>
    <a href="<?= BASE_URL ?>/index.php" target="_blank" class="sidebar_link">
      <span class="material-symbols-outlined">open_in_new</span>
      Lihat Website
    </a>
  </nav>

  <!-- User bottom -->
  <div class="sidebar_user">
    <div class="sidebar_user-avatar">
      <span class="material-symbols-outlined">person</span>
    </div>
    <div>
      <div class="sidebar_user-name"><?= htmlspecialchars($user['nama']) ?></div>
      <div class="sidebar_user-role">
        <?= $user['peran'] === 'super_admin' ? 'Super Admin' : 'Admin' ?>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/logout.php" class="sidebar_logout" title="Keluar">
      <span class="material-symbols-outlined">logout</span>
    </a>
  </div>

</aside>
