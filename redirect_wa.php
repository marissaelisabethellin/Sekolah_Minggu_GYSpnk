
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
    setTimeout(() => { window.location.href = 'index.php'; }, 2500);
  </script>
</body>
</html>
