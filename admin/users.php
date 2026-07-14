
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $pdo->prepare('INSERT INTO pengguna (nama_pengguna,kata_sandi_hash,nama,peran) VALUES (?,?,?,?)')
                ->execute([$nama_pengguna, $hash, $nama, $peran]);
            setFlash('success', "Pengguna \"$nama\" berhasil ditambahkan.");
            break;

        case 'edit':
            if (!$nama) { setFlash('error', 'Nama wajib diisi.'); break; }
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $pdo->prepare('UPDATE pengguna SET nama=?,peran=?,kata_sandi_hash=? WHERE id=?')
                    ->execute([$nama, $peran, $hash, $id]);
            } else {
                $pdo->prepare('UPDATE pengguna SET nama=?,peran=? WHERE id=?')
                    ->execute([$nama, $peran, $id]);
            }
            setFlash('success', 'Data pengguna berhasil diperbarui.');
            break;

        case 'delete':
            if ($id === (int) $me['id']) {
                setFlash('error', 'Tidak bisa menghapus akun sendiri.');
                break;
            }
            $pdo->prepare('DELETE FROM pengguna WHERE id=?')->execute([$id]);
            setFlash('success', 'Pengguna berhasil dihapus.');
            break;
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$editRow  = null;
if ($id > 0 && !isset($_GET['add'])) {
    $s = $pdo->prepare('SELECT id,nama_pengguna,nama,peran FROM pengguna WHERE id=?');
    $s->execute([$id]);
    $editRow = $s->fetch() ?: null;
}

$pengguna = $pdo->query('SELECT id,nama_pengguna,nama,peran,dibuat_pada FROM pengguna ORDER BY id ASC')->fetchAll();
$showForm = isset($_GET['add']) || $editRow !== null;

$pageTitle = 'Kelola Pengguna';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-main">
  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button class="sidebar-toggle" id="sidebarToggle">
        <span class="material-symbols-outlined">menu</span>
      </button>
      <span class="admin-topbar_title">Kelola Pengguna</span>
    </div>
    <div class="admin-topbar_right">
      <a href="?add=1" class="btn btn--primary btn--sm">
        <span class="material-symbols-outlined">person_add</span>
        Tambah Pengguna
      </a>
    </div>
  </header>

  <div class="admin-content">
    <?= renderFlash() ?>

    <?php if ($showForm): ?>
      <div class="panel" style="margin-bottom:24px">
        <div class="panel_header">
          <h2 class="panel_title">
            <span class="material-symbols-outlined"><?= $editRow ? 'edit' : 'person_add' ?></span>
            <?= $editRow ? 'Edit: ' . htmlspecialchars($editRow['nama']) : 'Tambah Pengguna Baru' ?>
          </h2>
          <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn--outline btn--sm">
            <span class="material-symbols-outlined">close</span> Batal
          </a>
        </div>
        <div class="panel_body">
          <form method="POST" action="users.php">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
            <input type="hidden" name="action" value="<?= $editRow ? 'edit' : 'add' ?>"/>
            <?php if ($editRow): ?><input type="hidden" name="id" value="<?= $editRow['id'] ?>"/><?php endif; ?>

            <div class="form-grid form-grid--2">
              <div class="form-group">
                <label class="form-label">Nama Lengkap *</label>
                <input class="form-control" type="text" name="nama" required
                       value="<?= htmlspecialchars($editRow['nama'] ?? '') ?>"/>
              </div>
              <div class="form-group">
                <label class="form-label">Username *</label>
                <input class="form-control" type="text" name="nama_pengguna"
                       <?= $editRow ? 'disabled value="'.htmlspecialchars($editRow['nama_pengguna'] ?? '').'"' : 'required' ?>/>
                <?php if ($editRow): ?><span class="form-hint">Username tidak dapat diubah.</span><?php endif; ?>
              </div>
            </div>

            <div class="form-grid form-grid--2" style="margin-top:16px">
              <div class="form-group">
                <label class="form-label"><?= $editRow ? 'Password Baru' : 'Password *' ?></label>
                <input class="form-control" type="password" name="password"
                       <?= $editRow ? 'placeholder="Kosongkan jika tidak diganti"' : 'required' ?>/>
              </div>
              <div class="form-group">
                <label class="form-label">Role</label>
                <select class="form-control" name="peran">
                  <option value="admin" <?= ($editRow['peran'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                  <option value="super_admin" <?= ($editRow['peran'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
              </div>
            </div>

            <div style="margin-top:24px;display:flex;gap:12px">
              <button type="submit" class="btn btn--primary">
                <span class="material-symbols-outlined"><?= $editRow ? 'save' : 'add' ?></span>
                <?= $editRow ? 'Simpan' : 'Tambah' ?>
              </button>
              <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn--outline">Batal</a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <div class="panel">
      <div class="panel_header">
        <h2 class="panel_title">
          <span class="material-symbols-outlined">manage_accounts</span>
          <?= count($pengguna) ?> Pengguna
        </h2>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>#</th><th>Nama</th><th>Username</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <?php foreach ($pengguna as $u): ?>
              <tr>
                <td style="color:var(--color-muted)"><?= $u['id'] ?></td>
                <td style="font-weight:600">
                  <?= htmlspecialchars($u['nama']) ?>
                  <?php if ((int)$u['id'] === (int)$me['id']): ?>
                    <span class="badge badge--blue" style="margin-left:6px">Anda</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($u['nama_pengguna']) ?></td>
                <td>
                  <span class="badge <?= $u['peran'] === 'super_admin' ? 'badge--amber' : 'badge--blue' ?>">
                    <?= $u['peran'] === 'super_admin' ? 'Super Admin' : 'Admin' ?>
                  </span>
                </td>
                <td style="font-size:.82rem;color:var(--color-muted)">
                  <?= date('d M Y', strtotime($u['dibuat_pada'])) ?>
                </td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="?id=<?= $u['id'] ?>" class="btn btn--icon btn--outline" title="Edit">
                      <span class="material-symbols-outlined">edit</span>
                    </a>
                    <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                      <button class="btn btn--icon btn--danger confirm-delete"
                              data-id="<?= $u['id'] ?>"
                              data-nama="<?= htmlspecialchars($u['nama']) ?>">
                        <span class="material-symbols-outlined">delete</span>
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal_icon modal_icon--danger">
      <span class="material-symbols-outlined">delete_forever</span>
    </div>
    <h3 class="modal_title">Hapus Pengguna?</h3>
    <p class="modal_body">Akun <strong id="deleteNama"></strong> akan dihapus permanen.</p>
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
</script>
</body>
</html>
