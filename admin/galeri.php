<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/auth.php';

requireLogin();

$pdo    = db();
function uploadGaleri(array $file): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gambar gagal.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Ukuran gambar maksimal 5 MB.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!getimagesize($file['tmp_name'])) {
    throw new RuntimeException(
        'File bukan gambar yang valid.'
    );
}
    $mime = mime_content_type($file['tmp_name']);

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
        dirname(__DIR__)
        . '/uploads/galeri/';

        if (!is_dir($folder)) {
    if (
        !mkdir($folder, 0755, true)
    ) {
        throw new RuntimeException(
            'Folder upload gagal dibuat.'
            );}}

    if (
    !move_uploaded_file(
        $file['tmp_name'],
        $folder . $namaFile
    )
) 
  {
    throw new RuntimeException(
        'Gagal menyimpan file.'
    );
  }

    return 'uploads/galeri/' . $namaFile;
}
$action = $_POST['action'] ?? '';
$id     = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

// ── Proses POST ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $teks_alternatif = trim($_POST['teks_alternatif'] ?? '');
    $urutan = (int) ($_POST['urutan'] ?? 0);
    $aktif  = isset($_POST['aktif']) ? 1 : 0;

    switch ($action) {
        case 'add':
            try { 
              if (empty($_FILES['gambar']['name'])) {
              throw new RuntimeException('Silakan pilih gambar.');}  
            $sumber = uploadGaleri($_FILES['gambar']);
            $pdo->prepare('INSERT INTO galeri(
                sumber,
                teks_alternatif,
                urutan,
                aktif,
                dibuat_oleh)
            VALUES
            (
                ?, ?, ?, ?, ?
            )'
        )->execute([
            $sumber,
            $teks_alternatif,
            $urutan,
            $aktif,
            currentUser()['id']
        ]);
        setFlash(
            'success',
            'Foto berhasil ditambahkan.'
        );
    } catch (Throwable $e) {

        setFlash(
            'error',
            $e->getMessage()
        );
    }
    break;

        case 'edit':
          try {
            $stmt = $pdo->prepare(
            'SELECT * FROM galeri WHERE id=?'
            );
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        if (!$old) {
            throw new RuntimeException(
                'Data tidak ditemukan.'
            );
        }
        $sumber = $old['sumber'];
        if (
            isset($_FILES['gambar']) &&
            $_FILES['gambar']['error'] === UPLOAD_ERR_OK
        ) {
            $sumber = uploadGaleri($_FILES['gambar']);
            $oldFile = dirname(__DIR__) . '/' . $old['sumber'];
            if (
              file_exists($oldFile) &&
              is_file($oldFile) &&
              is_writable($oldFile)
          ) {
              unlink($oldFile);
            }
          }

        $pdo->prepare(
            'UPDATE galeri
             SET
             sumber=?,
             teks_alternatif=?,
             urutan=?,
             aktif=?
             WHERE id=?'
        )->execute([
            $sumber,
            $teks_alternatif,
            $urutan,
            $aktif,
            $id
        ]);

        setFlash(
            'success',
            'Foto berhasil diperbarui.'
        );

    } catch (Throwable $e) {
        setFlash(
            'error',
            $e->getMessage()
        );
    }

    break;

        case 'delete':
            $stmt =
            $pdo->prepare('SELECT * FROM galeri WHERE id=?'
          );
            $stmt->execute([$id]);
            $foto = $stmt->fetch();

    if ($foto) {
      $file = dirname(__DIR__) . '/' . $foto['sumber'];
    if (
      file_exists($file) &&
      is_file($file) &&
      is_writable($file)
      ) {
        unlink($file);
        }

    $pdo->prepare(
        'DELETE FROM galeri WHERE id=?'
    )->execute([$id]);
}

    setFlash(
        'success',
        'Foto berhasil dihapus.'
    );

    break;
    }
    header('Location: ' . BASE_URL . '/admin/galeri.php');
    exit;
}

// ── Data untuk form edit ─────────────────────────────────────
$editRow = null;
if ($id > 0 && !isset($_GET['add'])) {
    $s = $pdo->prepare('SELECT * FROM galeri WHERE id=?');
    $s->execute([$id]);
    $editRow = $s->fetch() ?: null;
}

$galeriList = $pdo->query(
    'SELECT
        g.*,
        u.nama AS nama_pengunggah
     FROM galeri g
     LEFT JOIN pengguna u ON u.id = g.dibuat_oleh
     ORDER BY g.urutan ASC, g.id ASC'
)->fetchAll();
$showForm   = isset($_GET['add']) || $editRow !== null;

$pageTitle = 'Galeri Foto';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-main">
  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px">
    <button class="sidebar-toggle" id="sidebarToggle">
        <span class="material-symbols-outlined">menu</span>
    </button>

    <a href="<?= BASE_URL ?>/admin/index.php"
       class="back-dashboard">
        <span class="material-symbols-outlined">
            arrow_back_ios_new
        </span>
    </a>

    <span class="admin-topbar_title">
        Galeri Foto
    </span>
</div>
    <div class="admin-topbar_right">
      <a href="?add=1" class="btn btn--primary btn--sm">
        <span class="material-symbols-outlined">add_photo_alternate</span>
        Tambah Foto
      </a>
    </div>
  </header>

  <div class="admin-content">
    <?= renderFlash() ?>

    <!-- Form Tambah / Edit -->
    <?php if ($showForm): ?>
      <div class="panel" style="margin-bottom:24px">
        <div class="panel_header">
          <h2 class="panel_title">
            <span class="material-symbols-outlined"><?= $editRow ? 'edit' : 'add_photo_alternate' ?></span>
            <?= $editRow ? 'Edit Foto #' . $editRow['id'] : 'Tambah Foto Baru' ?>
          </h2>
          <a href="<?= BASE_URL ?>/admin/galeri.php" class="btn btn--outline btn--sm">
            <span class="material-symbols-outlined">close</span> Batal
          </a>
        </div>
        <div class="panel_body">
          <form method="POST" enctype="multipart/form-data" action="galeri.php">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
            <input type="hidden" name="action" value="<?= $editRow ? 'edit' : 'add' ?>"/>
            <?php if ($editRow): ?>
              <input type="hidden" name="id" value="<?= $editRow['id'] ?>"/>
            <?php endif; ?>

            <div class="form-group">
              <label class="form-label">
                Upload Gambar *
              </label>
            <input class="form-control" type="file" name="gambar" id="gambarInput" accept=".jpg,.jpeg,.png,.webp"
            <?= $editRow ? '' : 'required' ?>
            >
              <span class="form-hint">
                    JPG, PNG, WEBP (maksimal 5 MB)
              </span>
            </div>

            <!-- Preview gambar -->
            <div class="preview-wrapper">
              <img id="imgPreview"
                   src="<?= $editRow ? BASE_URL . '/' . htmlspecialchars($editRow['sumber']) : '' ?>"
                   alt="Preview"
                   onerror="this.style.display='none'"
                   style="max-height:160px;border-radius:12px;border:1px solid var(--color-border);
                          <?= $editRow ? '' : 'display:none' ?>;object-fit:cover;"/>
            </div>

            <div class="form-grid form-grid--2" style="margin-top:16px">
              <div class="form-group">
                <label class="form-label">Alt Text</label>
                <input class="form-control" type="text" name="teks_alternatif"
                       placeholder="Deskripsi foto…"
                       value="<?= htmlspecialchars($editRow['teks_alternatif'] ?? '') ?>"/>
              </div>
              <div class="form-group">
                <label class="form-label">Urutan Tampil</label>
                <input class="form-control" type="number" name="urutan" min="0"
                       value="<?= $editRow['urutan'] ?? 0 ?>"/>
              </div>
            </div>

            <div class="form-group" style="margin-top:16px">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600">
                <input type="checkbox" name="aktif" value="1"
                       <?= ($editRow['aktif'] ?? 1) ? 'checked' : '' ?>
                       style="accent-color:var(--color-primary);width:16px;height:16px"/>
                Aktif (tampil di galeri website)
              </label>
            </div>

            <div style="margin-top:24px;display:flex;gap:12px">
              <button type="submit" class="btn btn--primary">
                <span class="material-symbols-outlined"><?= $editRow ? 'save' : 'add' ?></span>
                <?= $editRow ? 'Simpan Perubahan' : 'Tambah Foto' ?>
              </button>
              <a href="<?= BASE_URL ?>/admin/galeri.php" class="btn btn--outline">Batal</a>
            </div>
          </form>
        </div>
      </div>
      <?php if ($editRow): ?>
    <small class="file-info">
        File saat ini:
        <?= htmlspecialchars(basename($editRow['sumber'])) ?>
    </small>
    <?php endif; ?>

    <?php endif; ?>
    
    <!-- Grid Galeri -->
    <div class="panel">
      <div class="panel_header">
        <h2 class="panel_title">
          <span class="material-symbols-outlined">photo_library</span>
          <?= count($galeriList) ?> Foto
        </h2>
      </div>

      <?php if (empty($galeriList)): ?>
        <div class="empty-state">
          <span class="material-symbols-outlined">photo_library</span>
          <p>Belum ada foto. <a href="?add=1" style="color:var(--color-primary)">Tambah sekarang.</a></p>
        </div>
      <?php else: ?>
        <!-- Grid tampilan kartu -->
        <div style="padding:24px;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
          <?php foreach ($galeriList as $g): ?>
            <div style="border:1px solid var(--color-border);border-radius:16px;overflow:hidden;background:var(--color-bg)">
              <div style="height:140px;overflow:hidden;position:relative">
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($g['sumber']) ?>"
                     alt="<?= htmlspecialchars($g['teks_alternatif']) ?>"
                     style="width:100%;height:100%;object-fit:cover"
                     onerror="this.style.opacity='.2'"/>
                <?php if (!$g['aktif']): ?>
                  <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center">
                    <span class="badge badge--gray">Nonaktif</span>
                  </div>
                <?php endif; ?>
              </div>
              <div style="padding:12px">
                <p style="font-size:.8rem;color:var(--color-muted);margin-bottom:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                   title="<?= htmlspecialchars($g['teks_alternatif']) ?>">
                  <?= $g['teks_alternatif'] ?: '<em>—</em>' ?>
                </p>
                <p style="font-size:.72rem;color:var(--color-muted);margin-bottom:8px;display:flex;align-items:center;gap:4px">
                  <span class="material-symbols-outlined" style="font-size:.9rem">person</span>
                  <?= htmlspecialchars($g['nama_pengunggah'] ?? 'Tidak diketahui') ?>
                </p>
                <div style="display:flex;gap:6px">
                  <a href="?id=<?= $g['id'] ?>" class="btn btn--icon btn--outline btn--sm" title="Edit">
                    <span class="material-symbols-outlined">edit</span>
                  </a>
                  <button class="btn btn--icon btn--danger btn--sm confirm-delete"
                          data-id="<?= $g['id'] ?>"
                          title="Hapus">
                    <span class="material-symbols-outlined">delete</span>
                  </button>
                  <span style="margin-left:auto;font-size:.75rem;color:var(--color-muted);align-self:center">#<?= $g['urutan'] ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal Hapus -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal_icon modal_icon--danger">
      <span class="material-symbols-outlined">delete_forever</span>
    </div>
    <h3 class="modal_title">Hapus Foto?</h3>
    <p class="modal_body">Foto ini akan dihapus dari galeri dan file gambar akan dihapus dari server secara permanen.</p>
    <div class="modal_actions">
      <button class="btn btn--outline" id="cancelDelete">Batal</button>
      <form method="POST" style="margin:0">
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

  // Preview gambar
const gambarInput = document.getElementById('gambarInput');
const imgPreview = document.getElementById('imgPreview');
gambarInput?.addEventListener(
    'change',
    function ()
    {
        const file = this.files[0];
        if (!file)
        {
            imgPreview.style.display = 'none';
            return;
        }
        const url = URL.createObjectURL(file);

        imgPreview.src = url;
        imgPreview.style.display = 'block';

        imgPreview.onload = () =>
            URL.revokeObjectURL(url);
    }
);

  // Modal hapus
  const modal = document.getElementById('deleteModal');
  document.querySelectorAll('.confirm-delete').forEach(btn => {
    btn.addEventListener('click', () => {
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