<?php
// ============================================================
//  LOGIN.PHP
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

// Sudah login → langsung ke dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pengguna = trim($_POST['nama_pengguna'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$nama_pengguna || !$password) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = db()->prepare('SELECT * FROM pengguna WHERE nama_pengguna = ? LIMIT 1');
        $stmt->execute([$nama_pengguna]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['kata_sandi_hash'])) {
            loginUser($user);
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Admin — <?= htmlspecialchars(SITE_SHORT) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Work+Sans:wght@400;500;600&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="admin/CSS/style.css"/>
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #E8F5FE 0%, #EEE8F4 100%);
      padding: 24px;
    }

    .login-wrap{
    width: 100%;
    max-width: 1200px;
    min-height: 650px;
    display: grid;
    grid-template-columns: 420px 1fr;
    background: #fff;
    border-radius: 32px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.08);
}

    /* Panel kiri: branding */
    .login-brand{
      padding: 50px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: linear-gradient(135deg, #2C6E9E, #5FB9F6);
      color: white;
      position: relative;
    }
    .login-brand::before {
      content: '';
      position: absolute;
      top: -80px; right: -80px;
      width: 280px; height: 280px;
      border-radius: 50%;
      background: rgba(255,255,255,.08);
    }
    .login-brand::after {
      content: '';
      position: absolute;
      bottom: -60px; left: -60px;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: rgba(255,255,255,.06);
    }
    .login-brand_logo{
  width: 100px;
  height: 100px;
  border-radius: 24px;
  background:rgba(255,255,255,.15);
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 30px;
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,.25);
  position: relative; z-index: 1;
    }
    .login-brand_logo img{
      width: 75px;
      height: 75px;
      object-fit: contain;
    }
    .login-brand_title{
      font-size: 2.5rem;
      font-weight: 800;
      line-height: 1.15;
      margin-bottom: 18px;
    }
    .login-brand_sub {
      font-size: .95rem;
      color: rgba(255,255,255,.80);
      line-height: 1.7;
      position: relative; z-index: 1;
    }
    .login-brand_badges {
      display: flex; 
      margin-top: 10px;
      flex-direction: column; 
      gap: 12px;
      position: relative; z-index: 1;
    }
    .login-brand_badge {
      display: flex; 
      align-items: center; 
      gap: 10px;
      font-size: 1rem; 
      line-height: 1.4;
      color: rgba(255,255,255,.85);
    }
    .login-brand_badge .material-symbols-outlined {
      font-size: 1.1rem;
      background: rgba(255,255,255,.18);
      border-radius: 8px;
      padding: 4px;
    }

    /* Panel kanan: form */
    .login-form-wrap {
      background: #fff;
      padding: 60px 48px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .login-form-wrap h2 {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-weight: 800;
      font-size: 1.75rem;
      color: #1E3A5F;
      margin-bottom: 8px;
    }
    .login-form-wrap p {
      color: #5A7A9B;
      margin-bottom: 36px;
      font-size: .95rem;
    }

    .login-error {
      background: #fff0f0;
      border: 1.5px solid #f2a0a0;
      color: #922b2b;
      border-radius: 14px;
      padding: 12px 16px;
      font-size: .9rem;
      font-weight: 600;
      margin-bottom: 20px;
      display: flex; align-items: center; gap: 8px;
    }

    .form-group { margin-bottom: 20px; }
    .form-label {
      display: block;
      font-size: .85rem; font-weight: 700;
      color: #1E3A5F;
      margin-bottom: 8px;
    }
    .form-input-wrap {
      position: relative;
    }
    .form-input-wrap > .material-symbols-outlined{
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: #5A7A9B;
      pointer-events: none;
    }
    .toggle-pw{
      position: absolute;
      right: 18px;
      top: 50%;
      transform: translateY(-50%);
      border: none;
      background: none;
      cursor: pointer;
      color: #5A7A9B;
      display: flex;
      align-items: center;
      justify-content: center; z-index: 5;
    }
    .toggle-pw .material-symbols-outlined{
      font-size:22px;
    }
    .form-input{
      width: 100%;
      height: 58px;
      padding-left: 55px;
      padding-right: 55px;
      border-radius: 16px;
      font-size: 1rem;
    }
    .form-input:focus{
      border-color: #5FB9F6;
      box-shadow: 0 0 0 4px rgba(95,185,246,.15);
    }

    .toggle-pw {
      position: absolute;
      right: 14px; top: 50%;
      transform: translateY(-50%);
      color: #5A7A9B;
      cursor: pointer;
      font-size: 1.2rem;
      background: none; border: none;
      padding: 4px;
      transition: color .2s;
    }
    .toggle-pw:hover { color: #5FB9F6; }

    .btn-login {
      width: 100%;
      height: 54px;
      background: #5FB9F6;
      color: #fff;
      border: none;
      border-radius: 14px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 8px;
      transition: background .2s, transform .2s, box-shadow .2s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-login:hover {
      background: #3aa8f0;
      box-shadow: 0 8px 28px rgba(95,185,246,.40);
      transform: translateY(-1px);
    }

    .login-back {
      display: flex; align-items: center; justify-content: center; gap: 6px;
      margin-top: 24px;
      font-size: .88rem; color: #5A7A9B;
      text-decoration: none;
      transition: color .2s;
    }
    .login-back:hover { color: #5FB9F6; }

    @media (max-width: 992px){
    .login-wrap{
        grid-template-columns:1fr;
        max-width:650px;
      }
    .login-brand{
        text-align:center;
        align-items:center;
      }
    .login-brand_badge{
        justify-content:center;
      }
  }

    @media (max-width:576px){
    body{
        padding:15px;
    }
    .login-form-wrap{
        padding:35px 25px;
    }
    .login-brand{
        padding:35px 25px;
    }
    .login-brand_title{
        font-size:2rem;
    }
    .login-form-wrap h2{
        font-size:1.8rem;
    }
    .form-input{
        height:52px;
    }
    .btn-login{
        height:52px;
    }
  }
  </style>
</head>
<body>
  <div class="login-wrap">

    <!-- Panel kiri: branding -->
    <div class="login-brand">
      <div class="login-brand_logo">
        <img src="gambar/burung_merpati_logo-removebg-preview.png">
      </div>
      <h1 class="login-brand_title">
        Panel Admin<br/>Sekolah Minggu<br/>GYS Pontianak
      </h1>
      <p class="login-brand_sub">
        Kelola konten website Sekolah Minggu secara mudah dan aman dari satu tempat.
      </p>
      <div class="login-brand_badges">
        <div class="login-brand_badge">
          <span class="material-symbols-outlined">dashboard</span>
          Dashboard & Statistik
        </div>
        <div class="login-brand_badge">
          <span class="material-symbols-outlined">mail</span>
          Kelola Pesan Masuk
        </div>
        <div class="login-brand_badge">
          <span class="material-symbols-outlined">school</span>
          Kelola Jenjang Kelas
        </div>
        <div class="login-brand_badge">
          <span class="material-symbols-outlined">photo_library</span>
          Kelola Galeri Foto
        </div>
      </div>
    </div>

    <!-- Panel kanan: form -->
    <div class="login-form-wrap">
      <h2>Selamat Datang</h2>
      <p>Masuk ke panel admin untuk mengelola konten.</p>

      <?php if ($error): ?>
        <div class="login-error">
          <span class="material-symbols-outlined">error</span>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label class="form-label" for="nama_pengguna">Username</label>
          <div class="form-input-wrap">
            <span class="material-symbols-outlined">person</span>
            <input class="form-input" type="text" id="nama_pengguna" name="nama_pengguna"
                   placeholder="Masukkan username"
                   value="<?= htmlspecialchars($_POST['nama_pengguna'] ?? '') ?>"
                   required autocomplete="nama_pengguna"/>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="form-input-wrap">
            <span class="material-symbols-outlined">lock</span>
            <input class="form-input" type="password" id="password" name="password"
                   placeholder="Masukkan password"
                   required autocomplete="current-password"/>
            <button type="button" class="toggle-pw" id="togglePw" aria-label="Lihat password">
              <span class="material-symbols-outlined">visibility</span>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-login">
          <span class="material-symbols-outlined">login</span>
          Masuk
        </button>
      </form>

      <a href="index.php" class="login-back">
        <span class="material-symbols-outlined" style="font-size:1rem">arrow_back</span>
        Kembali ke Halaman Utama
      </a>
    </div>

  </div>

  <script>
    // Toggle show/hide password
    document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('togglePw');
    const passwordInput = document.getElementById('password');
    if(toggleBtn && passwordInput){
        toggleBtn.addEventListener('click', () => {
            const isPassword =
                passwordInput.type === 'password';
            passwordInput.type =
                isPassword ? 'text' : 'password';
            toggleBtn.innerHTML = `
                <span class="material-symbols-outlined">
                    ${isPassword ? 'visibility_off' : 'visibility'}
                </span>
            `;
        });
    }
});
  </script>
</body>
</html>
