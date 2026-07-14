
    }
    header('Location: ' . BASE_URL . '/admin/pesan.php');
    exit;
}

// ── Filter ──────────────────────────────────────────────────
$filter  = $_GET['filter'] ?? 'all';  // all | unread | read
$search  = trim($_GET['q'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$where  = '1=1';
$params = [];

if ($filter === 'unread') { $where .= ' AND p.dibaca = 0'; }
if ($filter === 'read')   { $where .= ' AND p.dibaca = 1'; }

if ($search !== '') {
    $where   .= ' AND (p.nama LIKE ? OR p.whatsapp LIKE ? OR p.pesan LIKE ? OR j.nama LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$countStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM pesan p
     LEFT JOIN jenjang j ON j.id = p.id_jenjang
     WHERE $where"
);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT
        p.*,
        j.nama AS nama_jenjang,
        u.nama AS nama_penanggung_jawab
     FROM pesan p
     LEFT JOIN jenjang j ON j.id = p.id_jenjang
     LEFT JOIN pengguna u ON u.id = p.ditangani_oleh
     WHERE $where
     ORDER BY p.dibuat_pada DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$pesanList = $stmt->fetchAll();

$totalPages = (int) ceil($total / $perPage);
$unreadCount = (int) $pdo->query('SELECT COUNT(*) FROM pesan WHERE dibaca = 0')->fetchColumn();

$pageTitle = 'Pesan Masuk';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-main">
<header class="admin-topbar">

    <div class="admin-topbar_left">

        <button class="sidebar-toggle" id="sidebarToggle">
            <span class="material-symbols-outlined">menu</span>
        </button>

        <a href="<?= BASE_URL ?>/admin/index.php"
           class="back-dashboard"
           title="Kembali ke Dashboard">
            <span class="material-symbols-outlined">arrow_back_ios_new</span>
        </a>

        <span class="admin-topbar_title">
            Pesan Masuk
        </span>

    </div>

    <div class="admin-topbar_right">

        <?php if ($unreadCount > 0): ?>
        <form method="POST" style="margin:0">
            <input type="hidden" name="action" value="read_all">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <button type="submit" class="btn btn--outline btn--sm">
                <span class="material-symbols-outlined">done_all</span>
                Tandai Semua Dibaca
            </button>
        </form>
        <?php endif; ?>

    </div>

</header>

  <div class="admin-content">
    <?= renderFlash() ?>

    <!-- Filter & Search bar -->
    <div class="panel" style="margin-bottom:20px">
      <div class="panel_body" style="padding:16px 20px">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
          <div style="display:flex;gap:8px">
            <?php foreach (['all'=>'Semua','unread'=>'Belum Dibaca','read'=>'Sudah Dibaca'] as $k => $v): ?>
              <a href="?filter=<?= $k ?><?= $search ? '&q='.urlencode($search) : '' ?>"
                 class="btn btn--sm <?= $filter === $k ? 'btn--primary' : 'btn--outline' ?>">
                <?= $v ?>
                <?php if ($k === 'unread' && $unreadCount > 0): ?>
                  <span style="background:rgba(255,255,255,.35);border-radius:999px;padding:1px 7px;font-size:.75rem"><?= $unreadCount ?></span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
          <div style="display:flex;gap:8px;margin-left:auto">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>"/>
            <input class="form-control" style="height:36px;min-width:220px" type="search"
                   name="q" placeholder="Cari nama / WA / pesan…"
                   value="<?= htmlspecialchars($search) ?>"/>
            <button type="submit" class="btn btn--primary btn--sm">
              <span class="material-symbols-outlined">search</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabel -->
    <div class="panel">
      <div class="panel_header">
        <h2 class="panel_title">
          <span class="material-symbols-outlined">mail</span>
          <?= $total ?> Pesan Ditemukan
        </h2>
      </div>
      <div class="admin-table-wrap">
        <?php if (empty($pesanList)): ?>
          <div class="empty-state">
            <span class="material-symbols-outlined">inbox</span>
            <p>Tidak ada pesan<?= $search ? " untuk pencarian \"$search\"" : '' ?>.</p>
          </div>
        <?php else: ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Anak</th>
                <th>Usia</th>
                <th>Orang Tua</th>
                <th>WhatsApp</th>
                <th>Kelas Dituju</th>
                <th>Waktu</th>
                <th>Status</th>
                <th>Ditangani Oleh</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pesanList as $p): ?>
                <tr>
                  <td style="color:var(--color-muted)"><?= $p['id'] ?></td>
                  <td>
                    <?php if (!$p['dibaca']): ?><span class="unread-dot"></span><?php endif; ?>
                    <a href="#" class="open-detail"
                       data-id="<?= $p['id'] ?>"
                       data-nama="<?= htmlspecialchars($p['nama']) ?>"
                       data-usia="<?= $p['usia'] ?>"
                       data-gender="<?= htmlspecialchars($p['jenis_kelamin']) ?>"
                       data-wali="<?= htmlspecialchars($p['nama_wali']) ?>"
                       data-wa="<?= htmlspecialchars($p['whatsapp']) ?>"
                       data-alamat="<?= htmlspecialchars($p['alamat']) ?>"
                       data-pesan="<?= htmlspecialchars($p['pesan']) ?>"
                       data-jenjang="<?= htmlspecialchars($p['nama_jenjang'] ?? '—') ?>"
                       data-waktu="<?= date('d M Y, H:i', strtotime($p['dibuat_pada'])) ?>"
                       style="font-weight:600;color:var(--color-navy)">
                      <?= htmlspecialchars($p['nama']) ?>
                    </a>
                  </td>
                  <td><?= $p['usia'] ?> th</td>
                  <td><?= htmlspecialchars($p['nama_wali']) ?></td>
                  <td>
                    <a href="https://wa.me/62<?= ltrim($p['whatsapp'], '0') ?>"
                       target="_blank" style="color:var(--color-primary)">
                      <?= htmlspecialchars($p['whatsapp']) ?>
                    </a>
                  </td>
                  <td>
                    <?php if ($p['nama_jenjang']): ?>
                      <span class="badge badge--gray"><?= htmlspecialchars($p['nama_jenjang']) ?></span>
                    <?php else: ?>
                      <span style="color:var(--color-muted);font-size:.82rem">—</span>
                    <?php endif; ?>
                  </td>
                  <td style="color:var(--color-muted);font-size:.82rem;white-space:nowrap">
                    <?= date('d M Y', strtotime($p['dibuat_pada'])) ?><br/>
                    <?= date('H:i', strtotime($p['dibuat_pada'])) ?>
                  </td>
                  <td>
                    <?php if ($p['dibaca']): ?>
                      <span class="badge badge--green">Dibaca</span>
                    <?php else: ?>
                      <span class="badge badge--blue">Baru</span>
                    <?php endif; ?>
                  </td>
                  <td style="font-size:.82rem;color:var(--color-muted)">
                    <?= $p['nama_penanggung_jawab'] ? htmlspecialchars($p['nama_penanggung_jawab']) : '—' ?>
                  </td>
                  <td>
                    <div style="display:flex;gap:6px">
                      <!-- Toggle baca -->
                      <form method="POST" style="margin:0">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
                        <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
                        <input type="hidden" name="action" value="<?= $p['dibaca'] ? 'unread' : 'read' ?>"/>
                        <button type="submit" class="btn btn--icon btn--outline" title="<?= $p['dibaca'] ? 'Tandai belum dibaca' : 'Tandai sudah dibaca' ?>">
                          <span class="material-symbols-outlined"><?= $p['dibaca'] ? 'mark_email_unread' : 'drafts' ?></span>
                        </button>
                      </form>
                      <!-- Hapus -->
                      <button class="btn btn--icon btn--danger confirm-delete"
                              data-id="<?= $p['id'] ?>"
                              data-nama="<?= htmlspecialchars($p['nama']) ?>"
                              title="Hapus pesan">
                        <span class="material-symbols-outlined">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div style="padding:16px 24px;display:flex;gap:8px;align-items:center;border-top:1px solid var(--color-border)">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?filter=<?= $filter ?>&q=<?= urlencode($search) ?>&page=<?= $i ?>"
               class="btn btn--sm <?= $i === $page ? 'btn--primary' : 'btn--outline' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>
          <span style="font-size:.82rem;color:var(--color-muted);margin-left:8px">
            Hal <?= $page ?> dari <?= $totalPages ?>
          </span>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal Detail Pesan -->
<div class="modal-overlay" id="detailModal">
  <div class="modal" style="max-width:560px">
    <div class="modal_icon modal_icon--info">
      <span class="material-symbols-outlined">mail</span>
    </div>
    <h3 class="modal_title" id="detailNama">—</h3>
    <div id="detailBody"></div>
    <div class="modal_actions" style="margin-top:20px">
      <button class="btn btn--outline" id="closeDetail">Tutup</button>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal_icon modal_icon--danger">
      <span class="material-symbols-outlined">delete_forever</span>
    </div>
    <h3 class="modal_title">Hapus Pesan?</h3>
    <p class="modal_body" id="deleteDesc">Pesan dari <strong id="deleteNama"></strong> akan dihapus permanen.</p>
    <div class="modal_actions">
      <button class="btn btn--outline" id="cancelDelete">Batal</button>
      <form method="POST" style="margin:0" id="deleteForm">
        <input type="hidden" name="action" value="delete"/>
        <input type="hidden" name="id" id="deleteId"/>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
        <button type="submit" class="btn btn--danger">Hapus</button>
      </form>
    </div>
  </div>
</div>

<script>
  // Sidebar toggle
  document.getElementById('sidebarToggle')?.addEventListener('click', () =>
    document.getElementById('sidebar').classList.toggle('open'));

  // Modal Detail
  const detailModal = document.getElementById('detailModal');
  document.querySelectorAll('.open-detail').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const d = btn.dataset;
      document.getElementById('detailNama').textContent = d.nama;
      document.getElementById('detailBody').innerHTML = `
        <div class="detail-row"><span class="detail-row_label">Usia</span><span class="detail-row_val">${d.usia} tahun</span></div>
        <div class="detail-row"><span class="detail-row_label">Jenis Kelamin</span><span class="detail-row_val">${d.gender}</span></div>
        <div class="detail-row"><span class="detail-row_label">Orang Tua / Wali</span><span class="detail-row_val">${d.wali}</span></div>
        <div class="detail-row"><span class="detail-row_label">WhatsApp</span><span class="detail-row_val"><a href="https://wa.me/62${d.wa.replace(/^0/,'')}" target="_blank" style="color:var(--color-primary)">${d.wa}</a></span></div>
        <div class="detail-row"><span class="detail-row_label">Kelas Dituju</span><span class="detail-row_val">${d.jenjang}</span></div>
        <div class="detail-row"><span class="detail-row_label">Alamat</span><span class="detail-row_val">${d.alamat}</span></div>
        <div class="detail-row"><span class="detail-row_label">Pesan</span><span class="detail-row_val">${d.pesan}</span></div>
        <div class="detail-row"><span class="detail-row_label">Waktu</span><span class="detail-row_val">${d.waktu}</span></div>
      `;
      detailModal.classList.add('open');
    });
  });
  document.getElementById('closeDetail')?.addEventListener('click', () =>
    detailModal.classList.remove('open'));
  detailModal.addEventListener('click', e => {
    if (e.target === detailModal) detailModal.classList.remove('open');
  });

  // Modal Hapus
  const deleteModal = document.getElementById('deleteModal');
  document.querySelectorAll('.confirm-delete').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('deleteNama').textContent = btn.dataset.nama;
      document.getElementById('deleteId').value = btn.dataset.id;
      deleteModal.classList.add('open');
    });
  });
  document.getElementById('cancelDelete')?.addEventListener('click', () =>
    deleteModal.classList.remove('open'));
  deleteModal.addEventListener('click', e => {
    if (e.target === deleteModal) deleteModal.classList.remove('open');
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      detailModal.classList.remove('open');
      deleteModal.classList.remove('open');
    }
  });
</script>
</body>
</html>
