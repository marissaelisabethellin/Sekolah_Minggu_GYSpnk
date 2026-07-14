<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/auth.php';

requireLogin();

$pdo    = db();
function uploadJenjang(array $file): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gambar gagal.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Ukuran gambar maksimal 5 MB.');
    }

    if (!getimagesize($file['tmp_name'])) {
        throw new RuntimeException('File bukan gambar yang valid.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

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
        dirname(__DIR__) .
        '/uploads/jenjang/';

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

    return 'uploads/jenjang/' . $namaFile;
}
$action = $_POST['action'] ?? '';
$id     = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

// ── Proses POST ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $nama      = trim($_POST['nama']      ?? '');
    $usia      = trim($_POST['usia']      ?? '');
    $teks_alternatif = trim($_POST['teks_alternatif'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $ikon_label = trim($_POST['ikon_label'] ?? 'school');
    $label = trim($_POST['label'] ?? '');
    $urutan    = (int) ($_POST['urutan']  ?? 0);
    $aktif     = isset($_POST['aktif']) ? 1 : 0;

    switch ($action) {
        case 'add':
          try {
            if (
              !$nama ||
              !$usia ||
              !$deskripsi
              ) {
              throw new RuntimeException('Nama, usia, dan deskripsi wajib diisi.');
              }
            if (empty($_FILES['gambar']['name'])) {
              throw new RuntimeException('Silakan pilih gambar.');}

        $gambar = uploadJenjang($_FILES['gambar']);

        $pdo->prepare(
            'INSERT INTO jenjang
            (
                nama,
                usia,
                gambar,
                teks_alternatif,
                deskripsi,
                ikon_label,
                label,
                urutan,
                aktif,
                dibuat_oleh
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?,?,?
            )'
        )->execute([
            $nama,
            $usia,
            $gambar,
            $teks_alternatif,
            $deskripsi,
            $ikon_label,
            $label,
            $urutan,
            $aktif,
            currentUser()['id']
        ]);

        setFlash(
            'success',
            "Kelas \"$nama\" berhasil ditambahkan."
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
            if (
              !$nama ||
              !$usia ||
              !$deskripsi
        ) {
    throw new RuntimeException(
        'Nama, usia, dan deskripsi wajib diisi.'
    );
}
            $stmt = $pdo->prepare('SELECT * FROM jenjang WHERE id=?');
            $stmt->execute([$id]);
            $old = $stmt->fetch();

          if (!$old) {
            throw new RuntimeException('Data tidak ditemukan.');}
            $gambar = $old['gambar'];

          if (
            isset($_FILES['gambar']) &&
            $_FILES['gambar']['error'] === UPLOAD_ERR_OK
            ) {
        $gambar = uploadJenjang(
            $_FILES['gambar']
        );
        $oldFile =
            dirname(__DIR__) .
            '/' .
            $old['gambar'];
        if (
            file_exists($oldFile) &&
            is_file($oldFile) &&
            is_writable($oldFile)
        ) {
            @unlink($oldFile);
        }
    }

    $pdo->prepare(
        'UPDATE jenjang SET
        nama=?,
        usia=?,
        gambar=?,
        teks_alternatif=?,
        deskripsi=?,
        ikon_label=?,
        label=?,
        urutan=?,
        aktif=?
        WHERE id=?'
    )->execute([
        $nama,
        $usia,
        $gambar,
        $teks_alternatif,
        $deskripsi,
        $ikon_label,
        $label,
        $urutan,
        $aktif,
        $id
    ]);

    setFlash(
        'success',
        "Kelas \"$nama\" berhasil diperbarui."
    );

} catch (Throwable $e) {

    setFlash(
        'error',
        $e->getMessage()
    );
}
break;

        case 'delete':
          $stmt = $pdo->prepare(
            'SELECT * FROM jenjang WHERE id=?'
            );
          $stmt->execute([$id]);
          $row = $stmt->fetch();
    if (!$row) {
        setFlash(
            'error',
            'Data tidak ditemukan.'
        );
        break;
    }
    $file = dirname(__DIR__) . '/' . $row['gambar'];
    if (
        file_exists($file) &&
        is_file($file) &&
        is_writable($file)
    ) {
        unlink($file);
    }

    $pdo->prepare(
        'DELETE FROM jenjang WHERE id=?'
    )->execute([$id]);

    setFlash(
        'success',
        'Kelas berhasil dihapus.'
    );

    break;
    }
    header('Location: ' . BASE_URL . '/admin/jenjang.php');
    exit;
}

// ── Ambil data edit (jika ada ?id=) ─────────────────────────
$editRow = null;
if ($id > 0 && !isset($_GET['add'])) {
    $s = $pdo->prepare('SELECT * FROM jenjang WHERE id=?');
    $s->execute([$id]);
    $editRow = $s->fetch() ?: null;
}

$jenjangList = $pdo->query(
    'SELECT
        j.*,
        u.nama AS nama_pembuat
     FROM jenjang j
     LEFT JOIN pengguna u ON u.id = j.dibuat_oleh
     ORDER BY j.urutan ASC, j.id ASC'
)->fetchAll();
$showForm    = isset($_GET['add']) || $editRow !== null;

$pageTitle = 'Jenjang Kelas';
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
       class="back-dashboard"
       title="Kembali ke Dashboard">
        <span class="material-symbols-outlined">arrow_back_ios_new</span>
    </a>

    <span class="admin-topbar_title">
        Jenjang Kelas
    </span>
</div>

    <div class="admin-topbar_right">
      <a href="?add=1" class="btn btn--primary btn--sm">
        <span class="material-symbols-outlined">add</span>
        Tambah Kelas
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
            <span class="material-symbols-outlined"><?= $editRow ? 'edit' : 'add_circle' ?></span>
            <?= $editRow ? 'Edit Kelas: ' . htmlspecialchars($editRow['nama']) : 'Tambah Kelas Baru' ?>
          </h2>
          <a href="<?= BASE_URL ?>/admin/jenjang.php" class="btn btn--outline btn--sm">
            <span class="material-symbols-outlined">close</span>
            Batal
          </a>
        </div>
        <div class="panel_body">
          <form method="POST" enctype="multipart/form-data" action="jenjang.php">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
            <input type="hidden" name="action" value="<?= $editRow ? 'edit' : 'add' ?>"/>
            <?php if ($editRow): ?>
              <input type="hidden" name="id" value="<?= $editRow['id'] ?>"/>
            <?php endif; ?>

            <div class="form-grid form-grid--2">
              <div class="form-group">
                <label class="form-label">Nama Kelas *</label>
                <input class="form-control" type="text" name="nama"
                       placeholder="Contoh: Kelas Madya" required
                       value="<?= htmlspecialchars($editRow['nama'] ?? '') ?>"/>
              </div>
              <div class="form-group">
                <label class="form-label">Rentang Usia *</label>
                <input class="form-control" type="text" name="usia"
                       placeholder="Contoh: 9–11 th" required
                       value="<?= htmlspecialchars($editRow['usia'] ?? '') ?>"/>
              </div>
            </div>

            <div class="form-group" style="margin-top:16px">
              <label class="form-label">Upload Gambar *</label>
              <input class="form-control" type="file" name="gambar" id="gambarInput" accept=".jpg,.jpeg,.png,.webp"
              <?= $editRow ? '' : 'required' ?>>
              <span class="form-hint">JPG, PNG, WEBP (maksimal 5 MB)</span>
            </div>

            <div class="form-group" style="margin-top:16px">
              <label class="form-label">Teks Alternatif Gambar</label>
              <input class="form-control" type="text" name="teks_alternatif"
                     placeholder="Deskripsi gambar untuk aksesibilitas"
                     value="<?= htmlspecialchars($editRow['teks_alternatif'] ?? '') ?>"/>
            </div>

            <div class="form-group" style="margin-top:16px">
              <label class="form-label">Deskripsi Kelas *</label>
              <textarea class="form-control" name="deskripsi" rows="4"
                        placeholder="Tulis deskripsi kelas…" required
                        ><?= htmlspecialchars($editRow['deskripsi'] ?? '') ?></textarea>
            </div>

            <div class="form-grid form-grid--2" style="margin-top:16px">
              <div class="form-group">
                <label class="form-label">Icon Tag (Material Symbols)</label>
                <input class="form-control" type="text" name="ikon_label"
                       placeholder="Contoh: school"
                       value="<?= htmlspecialchars($editRow['ikon_label'] ?? 'school') ?>"/>
                <span class="form-hint">
                  Cek nama icon di
                  <a href="https://fonts.google.com/icons" target="_blank" style="color:var(--color-primary)">
                    fonts.google.com/icons
                  </a>
                </span>
              </div>
              <div class="form-group">
                <label class="form-label">Label Tag</label>
                <input class="form-control" type="text" name="label"
                       placeholder="Contoh: Pendalaman"
                       value="<?= htmlspecialchars($editRow['label'] ?? '') ?>"/>
              </div>
            </div>

            <div class="preview-wrapper">
              <img id="imgPreview" src="<?= $editRow ? BASE_URL . '/' . htmlspecialchars($editRow['gambar']) : '' ?>" alt="Preview"
              onerror="this.style.display='none'"
              style="max-height:180px; border-radius:12px; border:1px solid var(--color-border); object-fit:cover;
              <?= $editRow ? '' : 'display:none' ?>
            ">
            <?php if ($editRow): ?>
    <small class="file-info">
        File saat ini:
        <?= htmlspecialchars(
            basename($editRow['gambar'])
        ) ?>
    </small>
<?php endif; ?>
            </div>

            <div class="form-grid form-grid--2" style="margin-top:16px">
              <div class="form-group">
                <label class="form-label">Urutan Tampil</label>
                <input class="form-control" type="number" name="urutan" min="0"
                       value="<?= $editRow['urutan'] ?? 0 ?>"/>
                <span class="form-hint">Angka kecil tampil lebih awal.</span>
              </div>
              <div class="form-group" style="justify-content:flex-end;padding-bottom:4px">
                <label class="form-label">Status</label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;color:var(--color-text)">
                  <input type="checkbox" name="aktif" value="1"
                         <?= ($editRow['aktif'] ?? 1) ? 'checked' : '' ?>
                         style="accent-color:var(--color-primary);width:16px;height:16px"/>
                  Aktif (tampil di website)
                </label>
              </div>
            </div>

            <div style="margin-top:24px;display:flex;gap:12px">
              <button type="submit" class="btn btn--primary">
                <span class="material-symbols-outlined"><?= $editRow ? 'save' : 'add' ?></span>
                <?= $editRow ? 'Simpan Perubahan' : 'Tambah Kelas' ?>
              </button>
              <a href="<?= BASE_URL ?>/admin/jenjang.php" class="btn btn--outline">Batal</a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <!-- Tabel Daftar -->
    <div class="panel">
      <div class="panel_header">
        <h2 class="panel_title">
          <span class="material-symbols-outlined">school</span>
          <?= count($jenjangList) ?> Jenjang Kelas
        </h2>
      </div>
      <div class="admin-table-wrap">
        <?php if (empty($jenjangList)): ?>
          <div class="empty-state">
            <span class="material-symbols-outlined">school</span>
            <p>Belum ada data kelas. <a href="?add=1" style="color:var(--color-primary)">Tambah sekarang.</a></p>
          </div>
        <?php else: ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>Gambar</th>
                <th>Nama Kelas</th>
                <th>Usia</th>
                <th>Tag</th>
                <th>Dibuat Oleh</th>
                <th>Urutan</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($jenjangList as $j): ?>
                <tr>
                  <td>
                    <img class="table-thumb"
                         src="<?= BASE_URL ?>/<?= htmlspecialchars($j['gambar']) ?>"
                         alt="<?= htmlspecialchars($j['teks_alternatif']) ?>"
                         onerror="this.style.opacity='.3'"/>
                  </td>
                  <td style="font-weight:600"><?= htmlspecialchars($j['nama']) ?></td>
                  <td><?= htmlspecialchars($j['usia']) ?></td>
                  <td>
                    <span style="display:flex;align-items:center;gap:4px;font-size:.82rem;color:var(--color-primary)">
                      <span class="material-symbols-outlined" style="font-size:1rem"><?= htmlspecialchars($j['ikon_label']) ?></span>
                      <?= htmlspecialchars($j['label']) ?>
                    </span>
                  </td>
                  <td style="font-size:.82rem;color:var(--color-muted)">
                    <?= htmlspecialchars($j['nama_pembuat'] ?? 'Tidak diketahui') ?>
                  </td>
                  <td><?= $j['urutan'] ?></td>
                  <td>
                    <span class="badge <?= $j['aktif'] ? 'badge--green' : 'badge--gray' ?>">
                      <?= $j['aktif'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                  </td>
                  <td>
                    <div style="display:flex;gap:6px">
                      <a href="?id=<?= $j['id'] ?>" class="btn btn--icon btn--outline" title="Edit">
                        <span class="material-symbols-outlined">edit</span>
                      </a>
                      <button class="btn btn--icon btn--danger confirm-delete"
                              data-id="<?= $j['id'] ?>"
                              data-nama="<?= htmlspecialchars($j['nama']) ?>"
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
    <h3 class="modal_title">Hapus Kelas?</h3>
    <p class="modal_body">Kelas <strong id="deleteNama"></strong> akan dihapus permanen dari database.</p>
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

  const gambarInput = document.getElementById('gambarInput');
  const imgPreview = document.getElementById('imgPreview');

gambarInput?.addEventListener(
    'change',
    function () {
        const file = this.files[0];
        if (!file) {
            imgPreview.style.display = 'none';
            return;
        }
        const url =
            URL.createObjectURL(file);
        imgPreview.src = url;
        imgPreview.style.display = 'block';
        imgPreview.onload = () =>
            URL.revokeObjectURL(url);
    }
);
</script>
</body>
</html>