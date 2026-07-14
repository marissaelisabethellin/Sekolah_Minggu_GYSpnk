<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/auth.php';

requireLogin();

$pageTitle = 'Dashboard';

// ── Statistik ────────────────────────────────────────────────
$pdo            = db();
$totalJenjang   = (int) $pdo->query('SELECT COUNT(*) FROM jenjang')->fetchColumn();
$totalHero      = (int) $pdo->query('SELECT COUNT(*) FROM bagian_utama')->fetchColumn();
$totalGaleri    = (int) $pdo->query('SELECT COUNT(*) FROM galeri')->fetchColumn();
$totalPesan     = (int) $pdo->query('SELECT COUNT(*) FROM pesan')->fetchColumn();
$unreadPesan    = (int) $pdo->query('SELECT COUNT(*) FROM pesan WHERE dibaca = 0')->fetchColumn();

// 5 pesan terbaru
$recentPesan = $pdo
    ->query('SELECT * FROM pesan ORDER BY dibuat_pada DESC LIMIT 5')
    ->fetchAll();

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-main">

  <!-- Topbar -->
  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button class="sidebar-toggle" id="sidebarToggle">
        <span class="material-symbols-outlined">menu</span>
      </button>
      <span class="admin-topbar_title">Dashboard</span>
    </div>
    <div class="admin-topbar_right">
      <a href="<?= BASE_URL ?>/index.php" target="_blank" class="topbar-btn">
        <span class="material-symbols-outlined">open_in_new</span>
        Lihat Website
      </a>
    </div>
  </header>

  <div class="admin-content">

    <?= renderFlash() ?>

    <!-- Stat Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-card_icon stat-card_icon--blue">
          <span class="material-symbols-outlined">school</span>
        </div>
        <div>
          <div class="stat-card_val"><?= $totalJenjang ?></div>
          <div class="stat-card_label">Jenjang Kelas</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card_icon stat-card_icon--amber">
          <span class="material-symbols-outlined">view_carousel</span>
        </div>
        <div>
          <div class="stat-card_val"><?= $totalHero ?></div>
          <div class="stat-card_label">Konten Bagian Utama</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card_icon stat-card_icon--green">
          <span class="material-symbols-outlined">photo_library</span>
        </div>
        <div>
          <div class="stat-card_val"><?= $totalGaleri ?></div>
          <div class="stat-card_label">Foto Galeri</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card_icon stat-card_icon--amber">
          <span class="material-symbols-outlined">mail</span>
        </div>
        <div>
          <div class="stat-card_val"><?= $totalPesan ?></div>
          <div class="stat-card_label">Total Pesan</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card_icon stat-card_icon--red">
          <span class="material-symbols-outlined">mark_email_unread</span>
        </div>
        <div>
          <div class="stat-card_val"><?= $unreadPesan ?></div>
          <div class="stat-card_label">Pesan Belum Dibaca</div>
        </div>
      </div>
    </div>

    <!-- Pesan Terbaru -->
    <div class="panel">
      <div class="panel_header">
        <h2 class="panel_title">
          <span class="material-symbols-outlined">inbox</span>
          Pesan Terbaru
        </h2>
        <a href="<?= BASE_URL ?>/admin/pesan.php" class="btn btn--outline btn--sm">
          Lihat Semua
        </a>
      </div>

      <div class="admin-table-wrap">
        <?php if (empty($recentPesan)): ?>
          <div class="empty-state">
            <span class="material-symbols-outlined">inbox</span>
            <p>Belum ada pesan masuk.</p>
          </div>
        <?php else: ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Usia</th>
                <th>WhatsApp</th>
                <th>Waktu</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentPesan as $p): ?>
                <tr>
                  <td>
                    <?php if (!$p['dibaca']): ?>
                      <span class="unread-dot"></span>
                    <?php endif; ?>
                    <?= htmlspecialchars($p['nama']) ?>
                  </td>
                  <td><?= $p['usia'] ?> th</td>
                  <td><?= htmlspecialchars($p['whatsapp']) ?></td>
                  <td style="color:var(--color-muted);font-size:.85rem">
                    <?= date('d M Y, H:i', strtotime($p['dibuat_pada'])) ?>
                  </td>
                  <td>
                    <?php if ($p['dibaca']): ?>
                      <span class="badge badge--green">Dibaca</span>
                    <?php else: ?>
                      <span class="badge badge--blue">Baru</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<script>
  const sidebar = document.getElementById('sidebar');
  document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    sidebar.classList.toggle('open');
  });
</script>
</body>
</html>
