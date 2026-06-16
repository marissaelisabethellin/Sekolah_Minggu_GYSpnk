<?php
// ============================================================
//  PROSES FORMULIR KONTAK
//  Menerima POST dari index.php, validasi server-side,
//  lalu redirect ke WhatsApp dengan pesan terformat.
// ============================================================

require_once __DIR__ . '/config.php';

// ── Hanya izinkan metode POST ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ── Fungsi helper ────────────────────────────────────────────
function sanitize(string $val): string
{
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function redirectWithError(string $msg): never
{
    $_SESSION['form_error']  = $msg;
    $_SESSION['form_values'] = $_POST;   // kembalikan nilai input
    header('Location: index.php#kontak');
    exit;
}

session_start();

// ── Ambil & bersihkan input ──────────────────────────────────
$name     = sanitize($_POST['name']     ?? '');
$age      = trim($_POST['age']          ?? '');
$gender   = sanitize($_POST['gender']   ?? '');
$guardian = sanitize($_POST['guardian'] ?? '');
$phone    = trim($_POST['whatsapp']     ?? '');
$address  = sanitize($_POST['address']  ?? '');
$message  = sanitize($_POST['message']  ?? '');

// ── Validasi wajib isi ───────────────────────────────────────
if (!$name || !$age || !$gender || !$guardian || !$phone || !$address || !$message) {
    redirectWithError('Semua field wajib diisi.');
}

// ── Validasi nama (min 3 karakter) ───────────────────────────
if (mb_strlen($name) < 3) {
    redirectWithError('Nama minimal 3 karakter.');
}

// ── Validasi usia (3–17 tahun) ───────────────────────────────
$ageInt = (int) $age;
if ($ageInt < 3 || $ageInt > 17) {
    redirectWithError('Usia harus berada di rentang 3–17 tahun.');
}

// ── Validasi jenis kelamin ───────────────────────────────────
$allowedGenders = ['Laki-laki', 'Perempuan'];
if (!in_array($gender, $allowedGenders, true)) {
    redirectWithError('Jenis kelamin tidak valid.');
}

// ── Validasi nama wali (min 3 karakter) ──────────────────────
if (mb_strlen($guardian) < 3) {
    redirectWithError('Nama orang tua / wali minimal 3 karakter.');
}

// ── Validasi nomor WhatsApp (format 08xxxxxxxx) ──────────────
if (!preg_match('/^08[0-9]{8,11}$/', $phone)) {
    redirectWithError('Nomor WhatsApp tidak valid. Contoh: 081234567890');
}

// ── Validasi alamat (min 10 karakter) ────────────────────────
if (mb_strlen($address) < 10) {
    redirectWithError('Alamat terlalu pendek, mohon isi lebih lengkap.');
}

// ── Validasi pesan (min 10 karakter) ─────────────────────────
if (mb_strlen($message) < 10) {
    redirectWithError('Pesan minimal 10 karakter.');
}

// ── Semua validasi lolos — susun pesan WhatsApp ──────────────
$waText = <<<TEXT
Halo Sekolah Minggu GYS Pontianak,

Nama Lengkap      : {$name}
Usia              : {$ageInt} tahun
Jenis Kelamin     : {$gender}
Nama Orang Tua/Wali: {$guardian}
Nomor WhatsApp    : {$phone}
Alamat            : {$address}

Pesan:
{$message}
TEXT;

$encoded = rawurlencode($waText);
$waUrl   = 'https://wa.me/' . SITE_WA . '?text=' . $encoded;

// ── Simpan pesan sukses ke sesi, lalu redirect ───────────────
$_SESSION['form_success'] = 'Pesan berhasil dikirim! Anda akan diarahkan ke WhatsApp.';
unset($_SESSION['form_values']);

// Redirect ke WhatsApp (buka tab baru tidak bisa dari PHP,
//  jadi kita redirect melalui halaman perantara)
header('Location: redirect_wa.php?url=' . urlencode($waUrl));
exit;
