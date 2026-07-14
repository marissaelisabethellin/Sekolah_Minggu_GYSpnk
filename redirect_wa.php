<?php
// ============================================================
//  HALAMAN PERANTARA 
// ============================================================

session_start();
require_once __DIR__ . '/config.php';

$waUrl = filter_input(INPUT_GET, 'url', FILTER_VALIDATE_URL);

if (!$waUrl || !str_starts_with($waUrl, 'https://wa.me/')) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <title>Mengirim Pesan…</title>
  <meta name="robots" content="noindex">
  <style>
    :root { font-family: 'Segoe UI', sans-serif; }
    body  { display:flex; flex-direction:column; align-items:center;
            justify-content:center; min-height:100vh; margin:0;
            background:#F9F4FB; color:#1E3A5F; text-align:center; gap:20px; }
    h1    { font-size:1.5rem; margin:0; }
    p     { color:#5A7A9B; margin:0; }
    .btn  { display:inline-block; margin-top:8px; padding:14px 32px;
            background:#5FB9F6; color:#fff; border-radius:999px;
            text-decoration:none; font-weight:700; font-size:1rem; }
    .btn:hover { background:#3aa8f0; }
  </style>
</head>
<body>
  <h1>✅ Pesan Berhasil Diproses!</h1>
  <p>WhatsApp akan terbuka di tab baru dalam sekejap…</p>
  <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" class="btn">
    Buka WhatsApp Sekarang
  </a>
  <p style="font-size:.85rem">
    <a href="index.php" style="color:#5FB9F6">← Kembali ke Halaman Utama</a>
  </p>

  <script>
    // Buka WhatsApp otomatis, lalu kembali ke halaman utama
    window.open(<?= json_encode($waUrl) ?>, '_blank');
    setTimeout(() => { window.location.href = 'index.php'; }, 5000);
  </script>
</body>
</html>
