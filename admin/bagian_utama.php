

    if (!isset($allowed[$mime])) {
        throw new RuntimeException(
            'Format gambar harus JPG, PNG, atau WEBP.'
        );
    }

    $namaFile =
        bin2hex(random_bytes(16))
        . '.'
        . $allowed[$mime];

    $folder =
        dirname(__DIR__) .
        '/uploads/hero/';

    if (!is_dir($folder)) {
        if (!mkdir($folder, 0755, true)) {
            throw new RuntimeException(
                'Folder upload gagal dibuat.'
            );
        }
    }

    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $folder . $namaFile
        )
    ) {
        throw new RuntimeException(
            'Gagal menyimpan file.'
        );
    }

    return 'uploads/hero/' . $namaFile;
}

/** Hapus file gambar dari disk jika ada dan aman dihapus. */
function hapusFileHero(?string $path): void
{
    if (!$path) {
        return;
    }
    $full = dirname(__DIR__) . '/' . $path;
    if (file_exists($full) && is_file($full) && is_writable($full)) {
        @unlink($full);
    }
}

const MAKS_GAMBAR_CAROUSEL = 6;

$action = $_POST['action'] ?? '';
$id     = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

// ── Proses POST ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    switch ($action) {

        // ── Konten teks hero (bagian_utama) ─────────────────
        case 'add':
        case 'edit':
            try {
                $judul         = trim($_POST['judul'] ?? '');
                $subjudul      = trim($_POST['subjudul'] ?? '');
                $deskripsi     = trim($_POST['deskripsi'] ?? '');
                $teks_tombol   = trim($_POST['teks_tombol'] ?? '') ?: 'Daftar Sekarang';
                $tautan_tombol = trim($_POST['tautan_tombol'] ?? '');
                $aktif         = isset($_POST['aktif']) ? 1 : 0;

                if (!$judul || !$deskripsi) {
                    throw new RuntimeException('Judul dan deskripsi wajib diisi.');
                }

                // Bisnis rule sesuai komentar kolom `aktif` pada tabel:
                // hanya 1 baris bagian_utama yang aktif di website sekaligus.
                $pdo->beginTransaction();

                if ($aktif) {
                    $pdo->exec('UPDATE bagian_utama SET aktif = 0');
                }

                if ($action === 'add') {
                    $pdo->prepare(
                        'INSERT INTO bagian_utama
                        (judul, subjudul, deskripsi, gambar, teks_alternatif_gambar,
                         teks_tombol, tautan_tombol, aktif, diperbarui_oleh)
                        VALUES (?,?,?,?,?,?,?,?,?)'
                    )->execute([
                        $judul, $subjudul, $deskripsi, '', null,
                        $teks_tombol, $tautan_tombol, $aktif, currentUser()['id']
                    ]);
                    $newId = (int) $pdo->lastInsertId();

                    $pdo->commit();

                    setFlash(
                        'success',
                        'Konten bagian utama berhasil ditambahkan. Sekarang tambahkan gambar carousel di bawah.'
                    );
                    header('Location: ' . BASE_URL . '/admin/bagian_utama.php?id=' . $newId);
                    exit;
                }

                // action === 'edit'
                $stmt = $pdo->prepare('SELECT id FROM bagian_utama WHERE id=?');
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    throw new RuntimeException('Data tidak ditemukan.');
                }

                $pdo->prepare(
                    'UPDATE bagian_utama SET
                    judul=?, subjudul=?, deskripsi=?,
                    teks_tombol=?, tautan_tombol=?, aktif=?, diperbarui_oleh=?
                    WHERE id=?'
                )->execute([
                    $judul, $subjudul, $deskripsi,
                    $teks_tombol, $tautan_tombol, $aktif, currentUser()['id'], $id
                ]);

                $pdo->commit();

                setFlash('success', 'Konten bagian utama berhasil diperbarui.');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                setFlash('error', $e->getMessage());
            }
            break;

        case 'delete':
            try {
                $stmt = $pdo->prepare('SELECT * FROM bagian_utama WHERE id=?');
                $stmt->execute([$id]);
                $row = $stmt->fetch();

                if (!$row) {
                    throw new RuntimeException('Data tidak ditemukan.');
                }

                $gStmt = $pdo->prepare('SELECT gambar FROM bagian_utama_gambar WHERE id_bagian_utama=?');
                $gStmt->execute([$id]);
                foreach ($gStmt->fetchAll() as $g) {
                    hapusFileHero($g['gambar']);
                }
                hapusFileHero($row['gambar']);

                // FK ON DELETE CASCADE otomatis menghapus baris bagian_utama_gambar terkait.
                $pdo->prepare('DELETE FROM bagian_utama WHERE id=?')->execute([$id]);

                setFlash('success', 'Konten bagian utama berhasil dihapus.');
            } catch (Throwable $e) {
                setFlash('error', $e->getMessage());
            }
            break;

        case 'aktifkan':
            $pdo->beginTransaction();
            $pdo->exec('UPDATE bagian_utama SET aktif = 0');
            $pdo->prepare(
                'UPDATE bagian_utama SET aktif = 1, diperbarui_oleh = ? WHERE id = ?'
            )->execute([currentUser()['id'], $id]);
            $pdo->commit();

            setFlash('success', 'Konten dijadikan tampilan aktif di website.');
            break;

        // ── Gambar carousel (bagian_utama_gambar) ───────────
        case 'add_gambar':
            try {
                $idHero = (int) ($_POST['id_bagian_utama'] ?? 0);
                $alt    = trim($_POST['teks_alternatif'] ?? '');
                $urutan = (int) ($_POST['urutan'] ?? 0);

                $stmt = $pdo->prepare('SELECT id FROM bagian_utama WHERE id=?');
                $stmt->execute([$idHero]);
                if (!$stmt->fetch()) {
                    throw new RuntimeException('Konten bagian utama tidak ditemukan.');
                }

                $countStmt = $pdo->prepare('SELECT COUNT(*) FROM bagian_utama_gambar WHERE id_bagian_utama=?');
                $countStmt->execute([$idHero]);
                if ((int) $countStmt->fetchColumn() >= MAKS_GAMBAR_CAROUSEL) {
                    throw new RuntimeException(
                        'Maksimal ' . MAKS_GAMBAR_CAROUSEL . ' gambar carousel per konten.'
                    );
                }

                if (empty($_FILES['gambar']['name'])) {
                    throw new RuntimeException('Silakan pilih gambar.');
                }

                $gambar = uploadHero($_FILES['gambar']);

                $pdo->prepare(
                    'INSERT INTO bagian_utama_gambar (id_bagian_utama, gambar, teks_alternatif, urutan)
                     VALUES (?,?,?,?)'
                )->execute([$idHero, $gambar, $alt, $urutan]);

                setFlash('success', 'Gambar carousel berhasil ditambahkan.');
                header('Location: ' . BASE_URL . '/admin/bagian_utama.php?id=' . $idHero);
                exit;
            } catch (Throwable $e) {
                setFlash('error', $e->getMessage());
                header('Location: ' . BASE_URL . '/admin/bagian_utama.php?id=' . ((int) ($_POST['id_bagian_utama'] ?? 0)));
                exit;
            }
            break;

        case 'edit_gambar':
            try {
                $idGambar = (int) ($_POST['id_gambar'] ?? 0);
                $idHero   = (int) ($_POST['id_bagian_utama'] ?? 0);
                $alt      = trim($_POST['teks_alternatif'] ?? '');
                $urutan   = (int) ($_POST['urutan'] ?? 0);

                $stmt = $pdo->prepare('SELECT * FROM bagian_utama_gambar WHERE id=?');
                $stmt->execute([$idGambar]);
                $old = $stmt->fetch();
                if (!$old) {
                    throw new RuntimeException('Gambar tidak ditemukan.');
                }

                $gambar = $old['gambar'];
                if (
                    isset($_FILES['gambar']) &&
                    $_FILES['gambar']['error'] === UPLOAD_ERR_OK
                ) {
                    $gambar = uploadHero($_FILES['gambar']);
                    hapusFileHero($old['gambar']);
                }

                $pdo->prepare(
                    'UPDATE bagian_utama_gambar SET gambar=?, teks_alternatif=?, urutan=? WHERE id=?'
                )->execute([$gambar, $alt, $urutan, $idGambar]);

                setFlash('success', 'Gambar carousel berhasil diperbarui.');
            } catch (Throwable $e) {
                setFlash('error', $e->getMessage());
            }
            header('Location: ' . BASE_URL . '/admin/bagian_utama.php?id=' . $idHero);
            exit;

        case 'delete_gambar':
            $idGambar = (int) ($_POST['id_gambar'] ?? 0);
            $idHero   = (int) ($_POST['id_bagian_utama'] ?? 0);

            $stmt = $pdo->prepare('SELECT * FROM bagian_utama_gambar WHERE id=?');
            $stmt->execute([$idGambar]);
            $g = $stmt->fetch();
            if ($g) {
                hapusFileHero($g['gambar']);
                $pdo->prepare('DELETE FROM bagian_utama_gambar WHERE id=?')->execute([$idGambar]);
                setFlash('success', 'Gambar carousel berhasil dihapus.');
            } else {
                setFlash('error', 'Gambar tidak ditemukan.');
            }
            header('Location: ' . BASE_URL . '/admin/bagian_utama.php?id=' . $idHero);
            exit;
    }

    header('Location: ' . BASE_URL . '/admin/bagian_utama.php');
    exit();
}

// Data untuk form edit
$editRow    = null;
$gambarList = [];
if ($id > 0 && !isset($_GET['add'])) {
    $s = $pdo->prepare('SELECT * FROM bagian_utama WHERE id=?');
    $s->execute([$id]);
    $editRow = $s->fetch() ?: null;

    if ($editRow) {
        $gs = $pdo->prepare(
            'SELECT * FROM bagian_utama_gambar WHERE id_bagian_utama=? ORDER BY urutan ASC, id ASC'
        );
        $gs->execute([$id]);
        $gambarList = $gs->fetchAll();
    }
}

// JOIN ke tabel pengguna untuk menampilkan nama admin yang terakhir memperbarui.
// Subquery COUNT ke bagian_utama_gambar untuk menampilkan jumlah slide per konten.
$heroList = $pdo->query(
    'SELECT b.*, p.nama AS nama_pengubah,
        (SELECT COUNT(*) FROM bagian_utama_gambar g WHERE g.id_bagian_utama = b.id) AS jumlah_gambar
     FROM bagian_utama b
     LEFT JOIN pengguna p ON p.id = b.diperbarui_oleh
     ORDER BY b.aktif DESC, b.id DESC'
)->fetchAll();

$showForm  = isset($_GET['add']) || $editRow !== null;
$pageTitle = 'Bagian Utama';
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
            Bagian Utama (Hero)
        </span>

    </div>

    <div class="admin-topbar_right">
        <a href="?add=1" class="btn btn--primary btn--sm">
            <span class="material-symbols-outlined">add</span>
            Tambah Konten
        </a>
    </div>

</header>

<div class="admin-content">
    <?= renderFlash() ?>

    <!-- Form Tambah / Edit Konten Teks -->
    <?php if ($showForm): ?>
      <div class="panel">
        <div class="panel_header">
          <h2 class="panel_title">
            <span class="material-symbols-outlined"><?= $editRow ? 'edit' : 'add_circle' ?></span>
            <?= $editRow ? 'Edit Konten #' . $editRow['id'] : 'Tambah Konten Bagian Utama' ?>
          </h2>
          <a href="<?= BASE_URL ?>/admin/bagian_utama.php" class="btn btn--outline btn--sm">
            <span class="material-symbols-outlined">close</span>
            Batal
          </a>
        </div>
        <div class="panel_body">
          <form method="POST" action="bagian_utama.php">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
            <input type="hidden" name="action" value="<?= $editRow ? 'edit' : 'add' ?>"/>
            <?php if ($editRow): ?>
              <input type="hidden" name="id" value="<?= $editRow['id'] ?>"/>
            <?php endif; ?>

            <div class="form-stack">
              <div class="form-group">
                <label class="form-label">Judul Hero (H1) *</label>
                <textarea class="form-control" name="judul" rows="3" required
                          placeholder="Contoh: Sekolah Minggu&#10;Gereja Yesus Sejati&#10;Pontianak"
                          ><?= htmlspecialchars($editRow['judul'] ?? '') ?></textarea>
                <span class="form-hint">Gunakan baris baru (Enter) jika judul terdiri dari beberapa baris.</span>
              </div>

              <div class="form-group">
                <label class="form-label">Subjudul / Tagline</label>
                <textarea class="form-control" name="subjudul" rows="2"
                          placeholder="Kalimat singkat pendukung judul…"
                          ><?= htmlspecialchars($editRow['subjudul'] ?? '') ?></textarea>
              </div>

              <div class="form-group">
                <label class="form-label">Deskripsi *</label>
                <textarea class="form-control" name="deskripsi" rows="4" required
                          placeholder="Paragraf deskripsi singkat / ayat pendukung…"
                          ><?= htmlspecialchars($editRow['deskripsi'] ?? '') ?></textarea>
              </div>

              <div class="form-grid form-grid--2">
                <div class="form-group">
                  <label class="form-label">Teks Tombol CTA</label>
                  <input class="form-control" type="text" name="teks_tombol"
                         placeholder="Contoh: Daftar Sekarang"
                         value="<?= htmlspecialchars($editRow['teks_tombol'] ?? 'Daftar Sekarang') ?>"/>
                </div>
                <div class="form-group">
                  <label class="form-label">Tautan Tombol</label>
                  <input class="form-control" type="text" name="tautan_tombol"
                         placeholder="Contoh: #kontak atau https://…"
                         value="<?= htmlspecialchars($editRow['tautan_tombol'] ?? '') ?>"/>
                </div>
              </div>

              <div class="form-group">
                <label class="checkbox-label">
                  <input type="checkbox" name="aktif" value="1"
                         <?= ($editRow['aktif'] ?? 0) ? 'checked' : '' ?>
                         class="checkbox-input"/>
                  Jadikan aktif (tampil di halaman utama website)
                </label>
                <span class="form-hint">Hanya satu konten yang aktif dalam satu waktu; mengaktifkan ini menonaktifkan yang lain.</span>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn btn--primary">
                  <span class="material-symbols-outlined"><?= $editRow ? 'save' : 'arrow_forward' ?></span>
                  <?= $editRow ? 'Simpan Perubahan' : 'Simpan & Lanjut Tambah Gambar' ?>
                </button>
                <a href="<?= BASE_URL ?>/admin/bagian_utama.php" class="btn btn--outline">Batal</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <!-- Manajer Gambar Carousel (hanya saat edit konten yang sudah tersimpan) -->
    <?php if ($editRow): ?>
      <div class="panel">
        <div class="panel_header panel_header--column">
          <h2 class="panel_title">
            <span class="material-symbols-outlined">view_carousel</span>
            Gambar Carousel (<?= count($gambarList) ?>/<?= MAKS_GAMBAR_CAROUSEL ?>)
          </h2>
          <p class="panel_note">
            Tambahkan beberapa gambar di sini (disarankan 3) — semuanya akan tampil bergantian
            sebagai slide carousel hero di halaman utama untuk konten <strong>#<?= $editRow['id'] ?></strong>.
          </p>
        </div>

        <div class="panel_body">
          <!-- Form tambah gambar -->
          <form method="POST" enctype="multipart/form-data" action="bagian_utama.php" class="hero-upload-box">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
            <input type="hidden" name="action" value="add_gambar"/>
            <input type="hidden" name="id_bagian_utama" value="<?= $editRow['id'] ?>"/>

            <div class="form-group form-group--grow">
              <label class="form-label">Gambar Baru</label>
              <input class="form-control" type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp" required
                     <?= count($gambarList) >= MAKS_GAMBAR_CAROUSEL ? 'disabled' : '' ?>>
            </div>
            <div class="form-group form-group--grow-sm">
              <label class="form-label">Alt Text</label>
              <input class="form-control" type="text" name="teks_alternatif" placeholder="Deskripsi gambar…">
            </div>
            <div class="form-group form-group--fixed-sm">
              <label class="form-label">Urutan</label>
              <input class="form-control" type="number" name="urutan" min="0" value="<?= count($gambarList) ?>">
            </div>
            <button type="submit" class="btn btn--primary btn--sm"
                    <?= count($gambarList) >= MAKS_GAMBAR_CAROUSEL ? 'disabled' : '' ?>>
              <span class="material-symbols-outlined">add_photo_alternate</span>
              Tambah
            </button>
          </form>

          <?php if (empty($gambarList)): ?>
            <div class="empty-state">
              <span class="material-symbols-outlined">image</span>
              <p>Belum ada gambar carousel. Tambahkan minimal 1 gambar di atas.</p>
            </div>
          <?php else: ?>
            <div class="hero-gallery">
              <?php foreach ($gambarList as $g): ?>
                <div class="hero-image-card">
                  <div class="hero-image-card_thumb">
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($g['gambar']) ?>"
                         alt="<?= htmlspecialchars($g['teks_alternatif'] ?? '') ?>"
                         onerror="this.style.opacity='.2'"/>
                  </div>
                  <form method="POST" enctype="multipart/form-data" action="bagian_utama.php" class="hero-image-card_form">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
                    <input type="hidden" name="action" value="edit_gambar"/>
                    <input type="hidden" name="id_gambar" value="<?= $g['id'] ?>"/>
                    <input type="hidden" name="id_bagian_utama" value="<?= $editRow['id'] ?>"/>

                    <input class="form-control hero-image-card_input" type="text" name="teks_alternatif"
                           placeholder="Alt text" value="<?= htmlspecialchars($g['teks_alternatif'] ?? '') ?>"/>

                    <div class="hero-image-card_row">
                      <input class="form-control hero-image-card_input--sm" type="number" name="urutan" min="0"
                             value="<?= $g['urutan'] ?>" title="Urutan"/>
                      <input class="hero-image-card_file" type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp"/>
                    </div>

                    <div class="hero-image-card_actions">
                      <button type="submit" class="btn btn--outline btn--sm">
                        <span class="material-symbols-outlined">save</span>
                        Simpan
                      </button>
                    </div>
                  </form>
                  <form method="POST" action="bagian_utama.php" class="hero-image-card_form hero-image-card_form--delete"
                        onsubmit="return confirm('Hapus gambar ini dari carousel?');">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
                    <input type="hidden" name="action" value="delete_gambar"/>
                    <input type="hidden" name="id_gambar" value="<?= $g['id'] ?>"/>
                    <input type="hidden" name="id_bagian_utama" value="<?= $editRow['id'] ?>"/>
                    <button type="submit" class="btn btn--danger btn--sm">
                      <span class="material-symbols-outlined">delete</span>
                      Hapus
                    </button>
                  </form>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Tabel Daftar Konten -->
    <div class="panel">
      <div class="panel_header">
        <h2 class="panel_title">
          <span class="material-symbols-outlined">view_carousel</span>
          <?= count($heroList) ?> Konten Bagian Utama
        </h2>
      </div>
      <div class="admin-table-wrap">
        <?php if (empty($heroList)): ?>
          <div class="empty-state">
            <span class="material-symbols-outlined">view_carousel</span>
            <p>Belum ada konten. <a href="?add=1" class="text-primary">Tambah sekarang.</a></p>
          </div>
        <?php else: ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>Judul</th>
                <th>Tombol CTA</th>
                <th>Gambar Carousel</th>
                <th>Diperbarui Oleh</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($heroList as $h): ?>
                <tr>
                  <td class="cell-title" title="<?= htmlspecialchars($h['judul']) ?>">
                    <?= htmlspecialchars(str_replace("\r\n", ' ', $h['judul'])) ?>
                  </td>
                  <td>
                    <span class="cell-muted">
                      <?= htmlspecialchars($h['teks_tombol']) ?>
                      <?php if ($h['tautan_tombol']): ?>
                        → <code class="inline-code"><?= htmlspecialchars($h['tautan_tombol']) ?></code>
                      <?php endif; ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge <?= $h['jumlah_gambar'] > 0 ? 'badge--blue' : 'badge--gray' ?>">
                      <?= (int) $h['jumlah_gambar'] ?> gambar
                    </span>
                  </td>
                  <td class="cell-muted">
                    <?= htmlspecialchars($h['nama_pengubah'] ?? '—') ?>
                  </td>
                  <td>
                    <span class="badge <?= $h['aktif'] ? 'badge--green' : 'badge--gray' ?>">
                      <?= $h['aktif'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                  </td>
                  <td>
                    <div class="row-actions">
                      <?php if (!$h['aktif']): ?>
                        <form method="POST" class="form-inline">
                          <input type="hidden" name="action" value="aktifkan"/>
                          <input type="hidden" name="id" value="<?= $h['id'] ?>"/>
                          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
                          <button type="submit" class="btn btn--icon btn--outline" title="Jadikan Aktif">
                            <span class="material-symbols-outlined">check_circle</span>
                          </button>
                        </form>
                      <?php endif; ?>
                      <a href="?id=<?= $h['id'] ?>" class="btn btn--icon btn--outline" title="Edit & Kelola Gambar">
                        <span class="material-symbols-outlined">edit</span>
                      </a>
                      <button class="btn btn--icon btn--danger confirm-delete"
                              data-id="<?= $h['id'] ?>"
                              data-nama="konten ini beserta semua gambar carousel-nya"
                              title="Hapus">
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
    </div>
  </div>
</div>

<!-- Modal Hapus -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal_icon modal_icon--danger">
      <span class="material-symbols-outlined">delete_forever</span>
    </div>
    <h3 class="modal_title">Hapus Konten?</h3>
    <p class="modal_body">
      <strong id="deleteNama"></strong> akan dihapus permanen beserta seluruh file gambarnya dari server.
    </p>
    <div class="modal_actions">
      <button class="btn btn--outline" id="cancelDelete">Batal</button>
      <form method="POST" class="form-inline">
        <input type="hidden" name="action" value="delete"/>
        <input type="hidden" name="id" id="deleteId"/>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
        <button type="submit" class="btn btn--danger">Hapus</button>
      </form>
    </div>
  </div>
</div>

<script>
  document.getElementById('sidebarToggle')?.addEventListener('click', () =>
    document.getElementById('sidebar').classList.toggle('open'));

  const modal = document.getElementById('deleteModal');
  document.querySelectorAll('.confirm-delete').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('deleteNama').textContent = btn.dataset.nama;
      document.getElementById('deleteId').value = btn.dataset.id;
      modal.classList.add('open');
    });
  });
  document.getElementById('cancelDelete')?.addEventListener('click', () => modal.classList.remove('open'));
  modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') modal.classList.remove('open'); });
</script>
</body>
</html>
