<?php
// Data menu navigasi — tambah item cukup di sini
$navItems = [
    ['href' => '#home',    'label' => 'Beranda'],
    ['href' => '#tentang', 'label' => 'Tentang'],
    ['href' => '#jenjang', 'label' => 'Jenjang', 'dropdown' => [
        'Kelas Indria',
        'Kelas Pratama',
        'Kelas Madya',
        'Kelas Tunas Muda',
        'Kelas Remaja',
    ]],
    ['href' => '#jadwal',  'label' => 'Jadwal'],
    ['href' => '#galeri',  'label' => 'Galeri'],
    ['href' => '#kontak',  'label' => 'Kontak'],
];
?>

<!-- === NAVBAR === -->
<nav id="navbar" class="navbar">
    <div class="navbar_inner container">

        <a href="#home" class="navbar_brand">
            <div class="navbar_logo">
                <img src="gambar/transparan_tjc_logo_indonesia_color (1).png"
                    alt="Logo <?= htmlspecialchars(SITE_SHORT) ?>" />
            </div>
        </a>

        <ul class="navbar_links">
            <?php foreach ($navItems as $item): ?>
                <?php if (!empty($item['dropdown'])): ?>
                    <!-- Item dengan dropdown -->
                    <li class="navbar_item">
                        <a href="<?= $item['href'] ?>" class="navbar_link navbar_jenjang_dropdown-link">
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                        <ul class="navbar_dropdown">
                            <?php foreach ($item['dropdown'] as $sub): ?>
                                <li>
                                    <a href="<?= $item['href'] ?>" class="navbar_dropdown-link">
                                        <?= htmlspecialchars($sub) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Item biasa -->
                    <li class="navbar_item">
                        <a href="<?= $item['href'] ?>" class="navbar_link">
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>

        <a href="#kontak" class="btn btn--navy btn--pill btn--sm">Daftar</a>

        <button id="hamburger" class="navbar_hamburger" aria-label="Buka menu">
            <span></span><span></span><span></span>
        </button>

    </div>
</nav>

<!-- Mobile Drawer -->
<div id="drawer" class="drawer" aria-hidden="true">
    <ul class="drawer_links">
        <?php foreach ($navItems as $item): ?>
            <li>
                <a href="<?= $item['href'] ?>" class="drawer_link">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>