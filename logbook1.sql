-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Jan 2025 pada 06.26
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `logbook1`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `preview_data` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `preview_data`, `is_read`, `created_at`) VALUES
(17, 3, 'Laporan Anda telah dihapus oleh admin', '{\"id\":28,\"tanggal\":\"2024-12-23\",\"petugas\":\"Faishal\",\"melaksanakan_tugas\":\"Apa yaaa\",\"output\":\"punya faishal\",\"tugas_lainnya\":\"\",\"catatan\":\"\",\"data_dukung_1\":\"\",\"data_dukung_2\":\"\",\"approved_at\":null,\"approved_by\":null,\"created_at\":\"2024-12-23 16:56:36\",\"created_by\":3,\"status\":\"belum_selesai\",\"status_approval\":\"belum_disetujui\",\"updated_at\":null,\"user_id\":3,\"laporan_user_id\":3}', 1, '2024-12-23 07:58:30'),
(18, 3, 'Laporan Anda telah dihapus oleh admin', '{\"id\":29,\"tanggal\":\"2024-12-23\",\"petugas\":\"Iyee\",\"melaksanakan_tugas\":\"Apa yaaa\",\"output\":\"punya faishal\",\"tugas_lainnya\":\"\",\"catatan\":\"\",\"data_dukung_1\":\"\",\"data_dukung_2\":\"\",\"approved_at\":null,\"approved_by\":null,\"created_at\":\"2024-12-23 16:56:44\",\"created_by\":3,\"status\":\"belum_selesai\",\"status_approval\":\"belum_disetujui\",\"updated_at\":null,\"user_id\":3,\"laporan_user_id\":3}', 1, '2024-12-23 07:59:49'),
(19, 2, 'Laporan Anda telah dihapus oleh admin', '{\"id\":26,\"tanggal\":\"2024-12-23\",\"petugas\":\"Iyee\",\"melaksanakan_tugas\":\"menginput laporan\",\"output\":\"sa\",\"tugas_lainnya\":\"\",\"catatan\":\"\",\"data_dukung_1\":\"\",\"data_dukung_2\":\"\",\"approved_at\":null,\"approved_by\":null,\"created_at\":\"2024-12-23 16:52:36\",\"created_by\":2,\"status\":\"belum_selesai\",\"status_approval\":\"belum_disetujui\",\"updated_at\":null,\"user_id\":2,\"laporan_user_id\":2}', 1, '2024-12-23 08:14:29'),
(20, 2, 'Laporan Anda telah dihapus oleh admin', '{\"id\":31,\"tanggal\":\"2024-12-23\",\"petugas\":\"Iyee\",\"melaksanakan_tugas\":\"Apa yaaa\",\"output\":\"-\",\"tugas_lainnya\":\"\",\"catatan\":\"\",\"data_dukung_1\":\"\",\"data_dukung_2\":\"\",\"approved_at\":null,\"approved_by\":null,\"created_at\":\"2024-12-23 17:17:23\",\"created_by\":2,\"status\":\"belum_selesai\",\"status_approval\":\"belum_disetujui\",\"updated_at\":null,\"user_id\":2,\"laporan_user_id\":2}', 1, '2024-12-23 08:17:36'),
(48, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"menunggu_revisi\"}', 1, '2024-12-30 21:38:52'),
(49, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"menunggu_revisi\"}', 1, '2024-12-30 21:41:29'),
(50, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"menunggu_revisi\"}', 1, '2024-12-30 21:44:27'),
(52, 3, 'Laporan Anda telah dihapus oleh admin', '{\"id\":34,\"tanggal\":\"2024-12-30\",\"petugas\":\"Faishal\",\"melaksanakan_tugas\":\"Apa yaaa\",\"output\":\"re\",\"tugas_lainnya\":\"\",\"catatan\":\"\",\"data_dukung_1\":\"\",\"data_dukung_2\":\"\",\"approved_at\":null,\"approved_by\":null,\"created_at\":\"2024-12-31 06:48:07\",\"created_by\":3,\"status\":\"belum_selesai\",\"status_user\":null,\"status_approval\":\"belum_selesai\",\"updated_at\":null,\"user_id\":3,\"catatan_revisi\":null,\"laporan_user_id\":3}', 1, '2024-12-30 21:48:56'),
(53, 2, 'Laporan Anda telah dihapus oleh admin', '{\"id\":35,\"tanggal\":\"2024-12-30\",\"petugas\":\"Iyee\",\"melaksanakan_tugas\":\"Apa yaaa\",\"output\":\"aaaaa\",\"tugas_lainnya\":\"\",\"catatan\":\"\",\"data_dukung_1\":\"\",\"data_dukung_2\":\"\",\"approved_at\":null,\"approved_by\":null,\"created_at\":\"2024-12-31 06:49:19\",\"created_by\":2,\"status\":\"belum_selesai\",\"status_user\":null,\"status_approval\":\"belum_selesai\",\"updated_at\":null,\"user_id\":2,\"catatan_revisi\":null,\"laporan_user_id\":2}', 1, '2024-12-30 21:49:22'),
(54, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"sudah_direvisi\"}', 1, '2024-12-30 21:53:46'),
(55, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"sudah_direvisi\"}', 1, '2024-12-30 21:54:47'),
(56, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"sudah direvisi\"}', 1, '2024-12-30 21:55:21'),
(57, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"menunggu_revisi\"}', 1, '2024-12-30 21:57:22'),
(58, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"Menunggu Revisi\"}', 1, '2024-12-30 21:59:58'),
(59, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"Sudah Direvisi\"}', 1, '2024-12-30 22:00:01'),
(60, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"Menunggu Revisi\"}', 1, '2024-12-30 22:45:20'),
(61, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"oke saya terima\",\"status\":\"Sudah Direvisi\"}', 1, '2024-12-30 22:45:24'),
(62, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporannya\",\"status\":\"Menunggu Revisi\"}', 1, '2025-01-04 14:59:37'),
(63, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"Menunggu Revisi\"}', 1, '2025-01-04 15:01:46'),
(64, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"Sudah Direvisi\"}', 1, '2025-01-04 15:28:00'),
(65, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporannya\",\"status\":\"Sudah Direvisi\"}', 1, '2025-01-04 15:28:23'),
(66, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"Menunggu Revisi\"}', 1, '2025-01-04 15:28:29'),
(67, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporannya\",\"status\":\"Menunggu Revisi\"}', 1, '2025-01-04 15:29:09'),
(68, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"Sudah Direvisi\"}', 1, '2025-01-04 15:33:30'),
(69, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"Menunggu Revisi\"}', 1, '2025-01-04 15:34:30'),
(70, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"Sudah Direvisi\"}', 1, '2025-01-04 15:39:57'),
(71, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"Menunggu Revisi\"}', 1, '2025-01-04 15:41:02'),
(72, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"menunggu_revisi\"}', 1, '2025-01-04 15:42:46'),
(73, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"Sudah Direvisi\"}', 1, '2025-01-04 15:43:37'),
(74, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"menunggu_revisi\"}', 1, '2025-01-04 15:43:43'),
(75, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"menunggu_revisi\"}', 1, '2025-01-04 15:44:57'),
(76, 2, 'Revisi tugas: Apa yaaa', '{\"catatan\":\"buatkan ulang laporan\",\"status\":\"Menunggu Revisi\"}', 1, '2025-01-04 15:45:41'),
(78, 2, 'Laporan Anda telah dihapus oleh admin', '{\"tanggal\":\"2025-01-04\",\"melaksanakan_tugas\":\"Apa yaaa\",\"hasil\":\"aaaa\",\"tugas_lainnya\":\"aa\",\"catatan\":\"aaa\",\"data_dukung_1\":\"\",\"data_dukung_2\":\"\"}', 1, '2025-01-04 16:37:31'),
(79, 2, 'Laporan Anda telah dihapus oleh admin', '{\"tanggal\":\"2025-01-04\",\"melaksanakan_tugas\":\"menginput laporan\",\"hasil\":\"kajhdkas\",\"tugas_lainnya\":\"\",\"catatan\":\"\",\"data_dukung_1\":\"\",\"data_dukung_2\":\"\"}', 1, '2025-01-04 16:40:03'),
(80, NULL, 'Laporan Anda memerlukan revisi dari admin', '{\"tanggal\":\"2025-01-04\",\"melaksanakan_tugas\":\"menginput laporan\",\"hasil\":\"kasdhjak\",\"tugas_lainnya\":\"\",\"catatan_revisi\":\"perbaiki dengan benar\",\"status_revisi\":\"Menunggu Revisi\"}', 0, '2025-01-04 16:51:07'),
(82, NULL, 'Laporan tanggal 04 January 2025 memerlukan revisi.\nStatus: Menunggu Revisi\nCatatan: perbaiki dengan benar', '{\"id_laporan\":\"41\",\"tanggal\":\"04 January 2025\",\"melaksanakan_tugas\":\"menginput laporan\",\"hasil\":\"kasdhjak\",\"tugas_lainnya\":\"\",\"status_revisi\":\"Menunggu Revisi\",\"catatan_revisi\":\"perbaiki dengan benar\",\"direvisi_oleh\":null,\"waktu_revisi\":\"04 January 2025 17:55:31\"}', 0, '2025-01-04 16:55:31'),
(83, NULL, 'Laporan tanggal 04 January 2025 memerlukan revisi.\nStatus: Sudah Direvisi\nCatatan: perbaiki dengan benar', '{\"id_laporan\":\"41\",\"tanggal\":\"04 January 2025\",\"melaksanakan_tugas\":\"menginput laporan\",\"hasil\":\"kasdhjak\",\"tugas_lainnya\":\"\",\"status_revisi\":\"Sudah Direvisi\",\"catatan_revisi\":\"perbaiki dengan benar\",\"direvisi_oleh\":null,\"waktu_revisi\":\"04 January 2025 17:55:43\"}', 0, '2025-01-04 16:55:43'),
(84, 3, 'Laporan Anda telah dihapus oleh admin', '{\"tanggal\":\"2025-01-04\",\"melaksanakan_tugas\":\"apa coba\",\"hasil\":\"kjahskd\",\"tugas_lainnya\":\"\",\"catatan\":\"\",\"data_dukung_1\":\"\",\"data_dukung_2\":\"\"}', 1, '2025-01-04 16:57:43'),
(89, 2, 'Revisi tugas: menginput laporan\n', '{\"title\":\"Revisi tugas: menginput laporan\",\"date\":null,\"status\":\"Menunggu Revisi\",\"catatan\":\"perbaiki dengan benar\"}', 1, '2025-01-04 17:22:20'),
(90, 2, 'Revisi tugas: menginput laporan\n', '{\"title\":\"Revisi tugas: menginput laporan\",\"date\":null,\"status\":\"Menunggu Revisi\",\"catatan\":\"ulangi lagi\"}', 1, '2025-01-04 17:25:32'),
(91, 2, 'Revisi tugas: menginput laporan\n', '{\"title\":\"Revisi tugas: menginput laporan\",\"date\":null,\"status\":\"Sudah Direvisi\",\"catatan\":\"perbaiki dengan benar\"}', 1, '2025-01-04 17:26:44'),
(92, 2, 'Revisi tugas: menginput laporan\n', '{\"title\":\"Revisi tugas: menginput laporan\",\"date\":null,\"status\":\"Menunggu Revisi\",\"catatan\":\"perbaiki dengan benar ya\"}', 1, '2025-01-04 17:26:59'),
(99, 2, 'Revisi tugas: Apa yaaa\n', '{\"title\":\"Revisi tugas: Apa yaaa\",\"date\":null,\"status\":{\"label\":\"Status:\",\"value\":\"Sudah Direvisi\"},\"catatan\":{\"label\":\"Catatan:\",\"value\":\"ss\"}}', 1, '2025-01-04 17:33:24'),
(103, 2, 'Revisi tugas: Apa yaaa\n', '{\"title\":\"Revisi tugas: Apa yaaa\",\"date\":null,\"status\":\"Menunggu Revisi\",\"catatan\":\"sadasdpoaj\"}', 1, '2025-01-04 17:37:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `petugas`
--

CREATE TABLE `petugas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `petugas`
--

INSERT INTO `petugas` (`id`, `nama`) VALUES
(1, 'Faishal'),
(2, 'Iyee');

-- --------------------------------------------------------

--
-- Struktur dari tabel `profile_details`
--

CREATE TABLE `profile_details` (
  `id` int(11) NOT NULL,
  `foto_url` varchar(255) DEFAULT NULL,
  `jabatan` varchar(100) NOT NULL,
  `level` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nip` varchar(50) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `profile_details`
--

INSERT INTO `profile_details` (`id`, `foto_url`, `jabatan`, `level`, `nama`, `nip`, `no_hp`, `created_at`) VALUES
(2, 'uploads/67480ed115d38_6743e6d51cd44_0 (5).jpg', 'KEPALA STASIUN', '', 'coba', '8979364981', '90219739123', '2024-11-28 06:33:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `revisions`
--

CREATE TABLE `revisions` (
  `id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('Menunggu Revisi','Sudah Direvisi') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `revisions`
--

INSERT INTO `revisions` (`id`, `task_id`, `catatan`, `status`, `created_at`) VALUES
(1, 30, 'buatkan ulang laporannya', 'Sudah Direvisi', '2024-12-30 11:55:25'),
(4, 41, 'perbaiki dengan benar', 'Sudah Direvisi', '2025-01-04 16:40:41'),
(5, 44, 'sadasdpoaj', 'Sudah Direvisi', '2025-01-04 17:31:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tata_usaha`
--

CREATE TABLE `tata_usaha` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `petugas` varchar(100) NOT NULL,
  `melaksanakan_tugas` text NOT NULL,
  `output` text NOT NULL,
  `tugas_lainnya` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `data_dukung_1` varchar(255) DEFAULT NULL,
  `data_dukung_2` varchar(255) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `status` set('belum selesai','sudahselesai','konsep','Menunggu Revisi','Sudah Direvisi','Menunggu Persetujuan') DEFAULT NULL,
  `status_user` varchar(50) DEFAULT NULL,
  `status_approval` enum('belum selesai','sudahselesai','konsep','Menunggu Revisi','Sudah Direvisi','Menunggu Persetujuan') DEFAULT 'belum selesai',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL,
  `catatan_revisi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tata_usaha`
--

INSERT INTO `tata_usaha` (`id`, `tanggal`, `petugas`, `melaksanakan_tugas`, `output`, `tugas_lainnya`, `catatan`, `data_dukung_1`, `data_dukung_2`, `approved_at`, `approved_by`, `created_at`, `created_by`, `status`, `status_user`, `status_approval`, `updated_at`, `user_id`, `catatan_revisi`) VALUES
(2, '2024-11-28', 'admin', 'bismillah', 'coba', '', '', 'uploads/database.pdf', NULL, NULL, NULL, '2024-11-27 22:20:11', 1, NULL, NULL, '', '2024-12-03 00:43:14', 1, NULL),
(3, '2024-11-28', 'Faishal', 'Apa yaaa', 'Ted', 'semua', 'cobaa', 'uploads/868-Article Text-5416-1-10-20230531.pdf', 'uploads/SISTEM+INFORMASI+LOGBOOK+BERBASIS+WEB+PADA+STASIUN+GEOFISIKA+KELAS+I+DELI+SERDANG.pdf', '2024-11-28 06:24:16', 1, '2024-11-27 22:21:46', 2, '', NULL, '', '2024-11-28 06:24:16', 2, NULL),
(6, '2024-11-29', 'Faishal', 'Apa yaaa', 'asd', 'asda', 'haha', NULL, NULL, NULL, NULL, '2024-11-28 18:13:41', 2, '', NULL, '', NULL, 2, NULL),
(11, '2024-12-21', 'Faishal', 'menginput laporan', 'selesai', '-', '-', 'uploads/AlfiyyahFaridah_202155202116_TUGAS_INDIVIDU.pdf', '', NULL, NULL, '2024-12-21 05:49:13', 3, '', NULL, '', NULL, 3, NULL),
(13, '2024-12-21', 'admin', 'bismillah', '-', '', '', '', '', NULL, NULL, '2024-12-21 05:53:49', 1, 'sudahselesai', NULL, '', '2025-01-04 15:35:19', 1, NULL),
(30, '2024-12-23', 'Iyee', 'Apa yaaa', 'ini sudah benar', 'aaaaa', '', '', '', NULL, NULL, '2024-12-23 07:57:06', 2, 'Sudah Direvisi', 'Sudah Direvisi', '', '2025-01-04 16:38:59', 2, NULL),
(41, '2025-01-04', 'Iyee', 'menginput laporan', 'yqwu', 'sdhdhhd', 'bxva', 'Maya Nurlati Kelian_202155202077.pdf', '', NULL, NULL, '2025-01-04 16:40:19', 2, 'Menunggu Revisi', 'Menunggu Revisi', 'belum selesai', '2025-01-04 17:26:59', 2, NULL),
(43, '2025-01-04', 'admin', 'bismillah', 'a', '', '', '', '', NULL, NULL, '2025-01-04 17:25:08', 1, 'konsep', NULL, 'belum selesai', '2025-01-04 17:25:18', 1, NULL),
(44, '2025-01-04', 'Iyee', 'Apa yaaa', 'asa', 'asdkjfh', 'asdkja', 'contoh produk digital.png', '202155202049_Tugas2.pdf', '2025-01-04 17:38:30', 1, '2025-01-04 17:28:38', 2, '', 'Sudah Direvisi', 'belum selesai', '2025-01-04 17:38:30', 2, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `nama_tugas` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `jabatan`, `foto_profil`, `created_at`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'ADMINISTRASI UMUM', '6736a4ee3d920_kpl stsiun.png', '2024-11-28 06:14:23'),
(2, 'user', 'ee11cbb19052e40b07aac0ca060c23ee', 'user', 'user', '6743d3671439d_6729b18bd0093_pie.jpg', '2024-11-28 06:14:23'),
(3, 'tes', '28b662d883b6d76fd96e4ddc5e9ba780', 'user', 'tes', '67415ef083ea9_6736a55f06453_WhatsApp Image 2024-11-12 at 10.03.10_61acabfb.jpg', '2024-11-28 06:22:53');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `profile_details`
--
ALTER TABLE `profile_details`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `revisions`
--
ALTER TABLE `revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indeks untuk tabel `tata_usaha`
--
ALTER TABLE `tata_usaha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_status_approval` (`status_approval`);

--
-- Indeks untuk tabel `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT untuk tabel `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `profile_details`
--
ALTER TABLE `profile_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `revisions`
--
ALTER TABLE `revisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `tata_usaha`
--
ALTER TABLE `tata_usaha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT untuk tabel `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `revisions`
--
ALTER TABLE `revisions`
  ADD CONSTRAINT `revisions_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tata_usaha` (`id`);

--
-- Ketidakleluasaan untuk tabel `tata_usaha`
--
ALTER TABLE `tata_usaha`
  ADD CONSTRAINT `tata_usaha_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tata_usaha_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tata_usaha_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
