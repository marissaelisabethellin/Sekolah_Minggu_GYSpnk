<?php
// ============================================================
//  CONFIG/AUTH.PHP 
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Cek apakah user sudah login ─────────────────────────────
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

// ── Paksa redirect ke login jika belum login ────────────────
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

// ── Paksa redirect jika bukan super_admin ───────────────────
function requireSuperAdmin(): void
{
    requireLogin();
    if (($_SESSION['user_peran'] ?? '') !== 'super_admin') {
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
}

// ── Ambil data user yang sedang login ───────────────────────
function currentUser(): array
{
    return [
        'id'   => $_SESSION['user_id']   ?? null,
        'nama' => $_SESSION['user_nama']  ?? '',
        'peran' => $_SESSION['user_peran']  ?? '',
        'nama_pengguna' => $_SESSION['user_nama_pengguna'] ?? '',
    ];
}

// ── Set sesi setelah login berhasil ─────────────────────────
function loginUser(array $user): void
{
    session_regenerate_id(true);   
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['user_nama']     = $user['nama'];
    $_SESSION['user_peran']     = $user['peran'];
    $_SESSION['user_nama_pengguna'] = $user['nama_pengguna'];
}

// ── Hapus sesi (logout) ─────────────────────────────────────
function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ── Flash message (satu kali tampil) ────────────────────────
function setFlash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ── Render flash alert HTML ──────────────────────────────────
function renderFlash(): string
{
    $flash = getFlash();
    if (!$flash) return '';

    $cls = $flash['type'] === 'success' ? 'alert--success' : 'alert--error';
    $msg = htmlspecialchars($flash['msg']);
    return "<div class=\"admin-alert {$cls}\">{$msg}</div>";
}

// ── CSRF token (sederhana) ───────────────────────────────────
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        die('CSRF token tidak valid. Kembali dan coba lagi.');
    }
}
