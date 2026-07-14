-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2026 at 02:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gys_sekolah_minggu`
--

-- --------------------------------------------------------

--
-- Table structure for table `bagian_utama`
--

CREATE TABLE `bagian_utama` (
  `id` int(10) UNSIGNED NOT NULL,
  `judul` varchar(150) NOT NULL COMMENT 'Judul utama di hero (H1)',
  `subjudul` varchar(255) NOT NULL DEFAULT '' COMMENT 'Teks subjudul / tagline',
  `deskripsi` text NOT NULL COMMENT 'Paragraf deskripsi singkat',
  `gambar` varchar(255) NOT NULL COMMENT 'Path file gambar background/utama hero',
  `teks_alternatif_gambar` varchar(255) DEFAULT NULL,
  `teks_tombol` varchar(100) NOT NULL DEFAULT 'Daftar Sekarang',
  `tautan_tombol` varchar(255) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Hanya 1 baris yang aktif sekaligus',
  `diperbarui_oleh` int(10) UNSIGNED DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Konten hero section halaman utama (judul, gambar, tombol CTA)';

--
-- Dumping data for table `bagian_utama`
--

INSERT INTO `bagian_utama` (`id`, `judul`, `subjudul`, `deskripsi`, `gambar`, `teks_alternatif_gambar`, `teks_tombol`, `tautan_tombol`, `aktif`, `diperbarui_oleh`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 'Sekolah Minggu\r\nGereja Yesus Sejati\r\nPontianak', 'Membimbing anak untuk mengenal Tuhan, bertumbuh dalam iman, dan hidup dalam pimpinan Roh Kudus.', '\"Biarkanlah anak-anak itu, janganlah menghalang-halangi mereka datang kepada-Ku; sebab orang-orang yang seperti itulah yang empunya Kerajaan Sorga.\"\r\n— Matius 19:14', 'gambar/hero-background.jpg', 'Hero Sekolah Minggu GYS', 'Daftar Sekarang', '#kontak', 1, 1, '2026-07-13 09:07:50', '2026-07-13 09:07:50');

-- --------------------------------------------------------

--
-- Table structure for table `bagian_utama_gambar`
--

CREATE TABLE `bagian_utama_gambar` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_bagian_utama` int(10) UNSIGNED NOT NULL COMMENT 'FK -> bagian_utama.id',
  `gambar` varchar(255) NOT NULL COMMENT 'Path file gambar slide carousel',
  `teks_alternatif` varchar(255) DEFAULT NULL,
  `urutan` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Urutan tampil slide, angka kecil tampil lebih awal',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gambar-gambar slide carousel untuk hero section (relasi 1-ke-banyak ke bagian_utama)';

--
-- Dumping data for table `bagian_utama_gambar`
--

INSERT INTO `bagian_utama_gambar` (`id`, `id_bagian_utama`, `gambar`, `teks_alternatif`, `urutan`, `dibuat_pada`) VALUES
(1, 1, 'uploads/hero/8a2ab207c6a3f3cbff85e4e0f224316c.jpg', 'Hero Sekolah Minggu GYS', 3, '2026-07-13 04:09:39'),
(2, 1, 'uploads/hero/48f0d3feb7ab21abd1c6cea7b53652de.jpg', '', 1, '2026-07-13 05:51:11'),
(3, 1, 'uploads/hero/3c26ffcadd74f6174277de267e95da62.jpg', '', 2, '2026-07-13 05:51:45');

-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id` int(10) UNSIGNED NOT NULL,
  `sumber` varchar(255) DEFAULT NULL,
  `teks_alternatif` varchar(255) DEFAULT NULL,
  `urutan` tinyint(4) NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `dibuat_oleh` int(10) UNSIGNED DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Foto galeri aktivitas Sekolah Minggu';

--
-- Dumping data for table `galeri`
--

INSERT INTO `galeri` (`id`, `sumber`, `teks_alternatif`, `urutan`, `aktif`, `dibuat_oleh`, `dibuat_pada`) VALUES
(1, 'gambar/pentas seni anugerah teridah.jpeg', 'Pentas Seni Anugerah Terindah', 1, 1, 1, '2026-06-17 19:37:08'),
(2, 'gambar/tahun baru anak 2025.jpg', 'Tahun Baru Anak 2025', 2, 1, 1, '2026-06-17 19:37:08'),
(3, 'gambar/penampilan anak-anak.jpeg', 'Penampilan Anak-anak', 3, 1, 1, '2026-06-17 19:37:08'),
(4, 'gambar/Kebaktian padang madya 2025.jpg.jpeg', 'Kebaktian Padang Madya 2025', 4, 1, 1, '2026-06-17 19:37:08'),
(5, 'gambar/anak anak free time.jpeg', 'Anak-anak Free Time', 5, 1, 1, '2026-06-17 19:37:08'),
(6, 'gambar/sesi games.jpg.jpeg', 'Sesi Games', 6, 1, 1, '2026-06-17 19:37:08');

-- --------------------------------------------------------

--
-- Table structure for table `jenjang`
--

CREATE TABLE `jenjang` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `usia` varchar(20) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `teks_alternatif` varchar(255) DEFAULT NULL,
  `deskripsi` text NOT NULL,
  `ikon_label` varchar(50) DEFAULT NULL,
  `label` varchar(50) DEFAULT NULL,
  `urutan` tinyint(4) NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `dibuat_oleh` int(10) UNSIGNED DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Data jenjang / kelas Sekolah Minggu';

--
-- Dumping data for table `jenjang`
--

INSERT INTO `jenjang` (`id`, `nama`, `usia`, `gambar`, `teks_alternatif`, `deskripsi`, `ikon_label`, `label`, `urutan`, `aktif`, `dibuat_oleh`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 'Kelas Indria', '3–5 th', 'gambar/Indria.jpg.jpeg', 'Kelas Indria', 'Kelas Indria adalah jenjang awal bagi anak usia dini untuk mengenal kasih Tuhan melalui kegiatan sederhana dan menyenangkan seperti bernyanyi, bermain, dan membaca cerita dari Alkitab. Ini juga membentuk karakter dasar sebagai fondasi iman sejak dini.', 'child_care', 'Materi Dasar', 1, 1, 1, '2026-06-17 19:37:08', '2026-06-17 19:37:08'),
(2, 'Kelas Pratama', '6–8 th', 'gambar/Kelas Pratama.jpg.jpeg', 'Kelas Pratama', 'Melalui kegiatan interaktif yang mendorong partisipasi, kelas pratama membantu anak memahami firman Tuhan secara lebih nyata. Mereka juga membangun karakter yang jujur, disiplin, dan bertanggung jawab sebagai bagian dari pertumbuhan iman mereka.', 'menu_book', 'Nilai Karakter', 2, 1, 1, '2026-06-17 19:37:08', '2026-06-17 19:37:08'),
(3, 'Kelas Madya', '9–11 th', 'gambar/Kelas Madya (3).jpg.jpeg', 'Kelas Madya', 'Kelas Madya membimbing anak memahami firman Tuhan secara lebih mendalam dan menerapkannya dalam kehidupan sehari-hari melalui diskusi dan pembelajaran yang aktif. Serta anak didorong untuk berpikir, bertanya, dan memahami iman dengan lebih matang.', 'history_edu', 'Pendalaman', 3, 1, 1, '2026-06-17 19:37:08', '2026-06-17 19:37:08'),
(4, 'Kelas Tunas Muda', '12–14 th', 'gambar/kelas tunas muda.jpeg', 'Kelas Tunas Muda', 'Melalui pembelajaran yang relevan, Kelas Tunas Muda membantu anak praremaja memperdalam iman mereka. Melalui aktivitas dan diskusi interaktif, mereka didorong untuk memahami firman Tuhan, membangun karakter yang baik, dan belajar membuat keputusan yang bijaksana untuk mempersiapkan mereka untuk masa remaja.', 'school', 'Identitas Iman', 4, 1, 1, '2026-06-17 19:37:08', '2026-06-17 19:37:08'),
(5, 'Kelas Remaja', '15–17 th', 'gambar/Kelas Remaja (1).jpg.jpeg', 'Kelas Remaja', 'Kelas Remaja membantu remaja memperdalam iman mereka secara kritis dan relevan dalam menghadapi kehidupan melalui diskusi dan pembelajaran interaktif. Kelas ini juga mendorong mereka untuk membangun pemahaman iman mereka sendiri, membentuk karakter dewasa, dan memiliki jalan hidup yang jelas yang didasarkan pada kebenaran.', 'emoji_people', 'Kepemimpinan', 5, 1, 1, '2026-06-17 19:37:08', '2026-06-17 19:37:08');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_pengguna` varchar(50) DEFAULT NULL,
  `kata_sandi_hash` varchar(255) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `peran` enum('admin','super_admin') DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Akun admin panel';

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama_pengguna`, `kata_sandi_hash`, `nama`, `peran`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 'admin', '$2y$12$8k5rvflvIU9ntURV/xfaVuTia0TZFVca3y/RqvFwno.3BGKPYB0Ry', 'Administrator', 'super_admin', '2026-06-05 04:11:33', '2026-06-08 16:45:08'),
(2, 'admin1', '$2y$12$Nm.Lxlc2e1EUrilUTnvBE.mtQJVsldjPtg1gLWauGyfMnpkymlN22', 'Administrator 02', 'admin', '2026-07-13 15:49:37', '2026-07-13 15:49:37');

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `usia` tinyint(4) NOT NULL,
  `jenis_kelamin` varchar(20) NOT NULL,
  `nama_wali` varchar(100) NOT NULL,
  `whatsapp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `pesan` text NOT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `id_jenjang` int(10) UNSIGNED DEFAULT NULL,
  `ditangani_oleh` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK → users.id: admin yang menangani/membaca pesan ini',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pesan / pendaftaran masuk dari form kontak publik';

--
-- Dumping data for table `pesan`
--

INSERT INTO `pesan` (`id`, `nama`, `usia`, `jenis_kelamin`, `nama_wali`, `whatsapp`, `alamat`, `pesan`, `dibaca`, `id_jenjang`, `ditangani_oleh`, `dibuat_pada`) VALUES
(1, 'Marr', 17, 'Perempuan', 'Lisa', '081349772107', 'Jalan Tanjung Pura', 'Saya mau ikut sekolah minggu', 1, NULL, NULL, '2026-06-08 18:50:34'),
(2, 'Marissa', 14, 'Perempuan', 'Lisa', '081349772107', 'Jalan Bumi', 'saya mau ikut sekulmingg', 1, NULL, NULL, '2026-06-08 18:53:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bagian_utama`
--
ALTER TABLE `bagian_utama`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hero_aktif` (`aktif`),
  ADD KEY `idx_hero_updated_by` (`diperbarui_oleh`);

--
-- Indexes for table `bagian_utama_gambar`
--
ALTER TABLE `bagian_utama_gambar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bug_id_bagian_utama` (`id_bagian_utama`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_galeri_created_by` (`dibuat_oleh`);

--
-- Indexes for table `jenjang`
--
ALTER TABLE `jenjang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jenjang_created_by` (`dibuat_oleh`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_username` (`nama_pengguna`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pesan_jenjang_id` (`id_jenjang`),
  ADD KEY `idx_pesan_ditangani_oleh` (`ditangani_oleh`),
  ADD KEY `idx_pesan_dibaca` (`dibaca`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bagian_utama`
--
ALTER TABLE `bagian_utama`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bagian_utama_gambar`
--
ALTER TABLE `bagian_utama_gambar`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jenjang`
--
ALTER TABLE `jenjang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pesan`
--
ALTER TABLE `pesan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bagian_utama`
--
ALTER TABLE `bagian_utama`
  ADD CONSTRAINT `fk_hero_updated_by` FOREIGN KEY (`diperbarui_oleh`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `bagian_utama_gambar`
--
ALTER TABLE `bagian_utama_gambar`
  ADD CONSTRAINT `fk_bug_id_bagian_utama` FOREIGN KEY (`id_bagian_utama`) REFERENCES `bagian_utama` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `galeri`
--
ALTER TABLE `galeri`
  ADD CONSTRAINT `fk_galeri_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `jenjang`
--
ALTER TABLE `jenjang`
  ADD CONSTRAINT `fk_jenjang_created_by` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pesan`
--
ALTER TABLE `pesan`
  ADD CONSTRAINT `fk_pesan_ditangani_oleh` FOREIGN KEY (`ditangani_oleh`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pesan_jenjang_id` FOREIGN KEY (`id_jenjang`) REFERENCES `jenjang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
