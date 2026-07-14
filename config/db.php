<?php
// ============================================================
//  CONFIG/DB.PHP — Koneksi PDO MySQL
// ============================================================

require_once dirname(__DIR__) . '/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_NAME
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Tampilkan pesan ramah
        http_response_code(503);
        die('<p style="font-family:sans-serif;padding:2rem;color:#922b2b">
             ⚠️ Koneksi database gagal. Periksa konfigurasi di <code>config.php</code>.<br>
             Detail: ' . htmlspecialchars($e->getMessage()) . '</p>');
    }

    return $pdo;
}
