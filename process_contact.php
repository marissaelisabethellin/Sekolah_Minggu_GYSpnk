<?php
// ============================================================
//  PROCESS_CONTACT.PHP — Validasi + Simpan ke DB + WA
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ── Helper ───────────────────────────────────────────────────
function clean(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function fail(string $msg): never {
    $_SESSION['form_error']  = $msg;
    $_SESSION['form_values'] = $_POST;
    header('Location: index.php#kontak');
    exit;
}

// ── Ambil input ──────────────────────────────────────────────
$name     = clean($_POST['name']     ?? '');
$age      = trim($_POST['age']       ?? '');
$gender   = clean($_POST['gender']   ?? '');
$guardian = clean($_POST['guardian'] ?? '');
$phone    = trim($_POST['whatsapp']  ?? '');
$address  = clean($_POST['address']  ?? '');
$message  = clean($_POST['message']  ?? '');

// ── Validasi ─────────────────────────────────────────────────
if (!$name || !$age || !$gender || !$guardian || !$phone || !$address || !$message)
    fail('Semua field wajib diisi.');

if (mb_strlen($name) < 3)
    fail('Nama minimal 3 karakter.');

$ageInt = (int) $age;
if ($ageInt < 3 || $ageInt > 17)
    fail('Usia harus berada di rentang 3–17 tahun.');

if (!in_array($gender, ['Laki-laki', 'Perempuan'], true))
    fail('Jenis kelamin tidak valid.');

if (mb_strlen($guardian) < 3)
    fail('Nama orang tua / wali minimal 3 karakter.');

if (!preg_match('/^08[0-9]{8,11}$/', $phone))
    fail('Nomor WhatsApp tidak valid. Contoh: 081234567890');

if (mb_strlen($address) < 10)
    fail('Alamat terlalu pendek, mohon isi lebih lengkap.');

if (mb_strlen($message) < 10)
    fail('Pesan minimal 10 karakter.');

// ── Simpan ke database ───────────────────────────────────────
try {
    db()->prepare(
        'INSERT INTO pesan (nama, usia, jenis_kelamin, nama_wali, whatsapp, alamat, pesan)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    )->execute([$name, $ageInt, $gender, $guardian, $phone, $address, $message]);
} catch (Throwable $e) {
    // Jika DB gagal, tetap lanjutkan ke WA (jangan blokir user)
    error_log('DB pesan error: ' . $e->getMessage());
}

// ── Susun & kirim ke WhatsApp ────────────────────────────────
$waText = <<<TEXT
Halo Sekolah Minggu GYS Pontianak,

Nama Lengkap       : {$name}
Usia               : {$ageInt} tahun
Jenis Kelamin      : {$gender}
Nama Orang Tua/Wali: {$guardian}
Nomor WhatsApp     : {$phone}
Alamat             : {$address}

Pesan:
{$message}
TEXT;

$_SESSION['form_success'] = 'Pesan berhasil dikirim dan tersimpan!';
unset($_SESSION['form_values']);

header('Location: redirect_wa.php?url=' . urlencode('https://wa.me/' . SITE_WA . '?text=' . rawurlencode($waText)));
exit;
