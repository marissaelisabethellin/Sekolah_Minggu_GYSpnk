<?php
// Dipanggil di awal setiap halaman admin
// $pageTitle harus sudah di-set sebelum include ini
if (!defined('SITE_NAME')) {
    require_once dirname(__DIR__) . '/config.php';
}
$title = ($pageTitle ?? 'Panel Admin') . ' — ' . SITE_SHORT;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Work+Sans:wght@400;500;600&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= BASE_URL ?>/admin/CSS/style.css"/>
</head>
<body>
