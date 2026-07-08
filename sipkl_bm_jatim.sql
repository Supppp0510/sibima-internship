-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Jul 2026 pada 05.49
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sipkl_bm_jatim`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` bigint(20) NOT NULL,
  `mahasiswa_id` bigint(20) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `status` enum('hadir','izin','sakit','alpha') NOT NULL DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `aktivitas` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `aktivitas`, `ip_address`, `created_at`) VALUES
(1, 1, 'Menghapus bidang : Keuangan', '::1', NULL),
(2, 1, 'Menambahkan Universitas : Universitas Islam Negeri Maulana Malik Ibrahim Malang', '::1', NULL),
(3, 1, 'Mengubah data universitas : Universitas Islam Negeri Maulana Malik Ibrahim Malang', '::1', NULL),
(4, 1, 'Menambahkan Universitas : Universitas Negeri Surabaya', '::1', NULL),
(5, 1, 'Menambahkan Program Studi : Teknik Informatika', '::1', NULL),
(6, 1, 'Mengubah Program Studi : Teknik Informatika', '::1', NULL),
(7, 1, 'Mengubah Program Studi : Teknik Informatika', '::1', NULL),
(8, 1, 'Mengubah Program Studi : Teknik Informatika', '::1', NULL),
(9, 1, 'Menambahkan Program Studi : Adminstrasi Publik', '::1', NULL),
(10, 1, 'Menambahkan Program Studi : juec', '::1', NULL),
(11, 1, 'Menghapus Program Studi : juec', '::1', NULL),
(12, 1, 'Login ke sistem', '::1', NULL),
(13, 1, 'Menambahkan Universitas : um', '::1', NULL),
(14, 1, 'Mengubah data universitas : unm', '::1', NULL),
(15, 1, 'Menghapus Universitas : unm', '::1', NULL),
(16, 1, 'Menambahkan Program Studi : qwe', '::1', NULL),
(17, 1, 'Mengubah Program Studi : qwe', '::1', NULL),
(18, 1, 'Menghapus Program Studi : qwe', '::1', NULL),
(19, 1, 'Menambahkan Periode PKL : genap', '::1', NULL),
(20, 1, 'Menambahkan Periode PKL : ganjil', '::1', NULL),
(21, 1, 'Menambahkan Periode PKL : 243', '::1', NULL),
(22, 1, 'Mengubah Periode PKL : 2437hh', '::1', NULL),
(23, 1, 'Menghapus Periode PKL : 2437hh', '::1', NULL),
(24, 1, 'Menambahkan Mahasiswa : mila', '::1', NULL),
(25, 1, 'Menambahkan Mahasiswa : dina', '::1', NULL),
(26, 1, 'Mengubah data mahasiswa : mila', '::1', NULL),
(27, 1, 'Menghapus data mahasiswa : dina', '::1', NULL),
(28, 1, 'Menambahkan Pembimbing : ahmad', '::1', NULL),
(29, 1, 'Mengubah data pembimbing : ahmad', '::1', NULL),
(30, 1, 'Menambahkan Pembimbing : Budi', '::1', NULL),
(31, 1, 'Menghapus data pembimbing : ahmad', '::1', NULL),
(32, 1, 'Menambahkan Pendaftaran PKL : mila', '::1', NULL),
(33, 1, 'Verifikasi Pendaftaran PKL : mila menjadi menunggu', '::1', NULL),
(34, 1, 'Verifikasi Pendaftaran PKL : mila menjadi menunggu', '::1', NULL),
(35, 1, 'Menghapus Pendaftaran PKL : mila', '::1', NULL),
(36, 1, 'Menambahkan Pendaftaran PKL : mila', '::1', NULL),
(37, 1, 'Verifikasi Pendaftaran PKL : mila menjadi menunggu', '::1', NULL),
(38, 1, 'Verifikasi Pendaftaran PKL : mila menjadi menunggu', '::1', NULL),
(39, 1, 'Verifikasi Pendaftaran PKL : mila menjadi diterima', '::1', NULL),
(40, 1, 'Verifikasi Pendaftaran PKL : mila menjadi diterima', '::1', NULL),
(41, 1, 'Memverifikasi pendaftaran PKL mahasiswa : mila (diterima)', '::1', NULL),
(42, 1, 'Login ke sistem', '::1', NULL),
(43, 1, 'Menambahkan Mahasiswa : dani', '::1', NULL),
(44, 1, 'Menambahkan Pendaftaran PKL : dani', '::1', NULL),
(45, 1, 'Memverifikasi pendaftaran PKL mahasiswa : dani (ditolak)', '::1', NULL),
(46, 1, 'Menambahkan Pembimbing : Diah', '::1', NULL),
(47, 1, 'Menambahkan data bidang : ', '::1', NULL),
(48, 1, 'Menambahkan Universitas : Universitas Negeri Malang', '::1', NULL),
(49, 1, 'Menambahkan Program Studi : Sistem Informasi', '::1', NULL),
(50, 1, 'Menambahkan Mahasiswa : indah', '::1', NULL),
(51, 1, 'Menambahkan Pembimbing : ali', '::1', NULL),
(52, 1, 'Menambahkan Pendaftaran PKL : indah', '::1', NULL),
(53, 1, 'Menambahkan Penilaian PKL : mila', '::1', NULL),
(54, 1, 'Mengubah Penilaian PKL : mila', '::1', NULL),
(55, 1, 'Menghapus Penilaian PKL : mila', '::1', NULL),
(56, 1, 'Menambahkan Penilaian PKL : mila', '::1', NULL),
(57, 1, 'Generate Sertifikat PKL : mila', '::1', NULL),
(58, 1, 'Mengubah data mahasiswa : indah', '::1', NULL),
(59, 1, 'Mengubah data mahasiswa : dani', '::1', NULL),
(60, 1, 'Mengubah Pendaftaran PKL : mila menjadi diterima', '::1', NULL),
(61, 1, 'Login ke sistem', '::1', NULL),
(62, 1, 'Login ke sistem', '::1', NULL),
(63, 1, 'Login ke sistem', '::1', NULL),
(64, 1, 'Logout dari sistem', '::1', NULL),
(65, 1, 'Login ke sistem', '::1', NULL),
(66, 1, 'Logout dari sistem', '::1', NULL),
(67, 1, 'Login ke sistem', '::1', NULL),
(68, 1, 'Logout dari sistem', '::1', NULL),
(69, 4, 'Logout dari sistem', '::1', NULL),
(70, 1, 'Login ke sistem', '::1', NULL),
(71, 1, 'Logout dari sistem', '::1', NULL),
(72, 4, 'Logout dari sistem', '::1', NULL),
(73, 2, 'Logout dari sistem', '::1', NULL),
(74, 4, 'Logout dari sistem', '::1', NULL),
(75, 1, 'Login ke sistem', '::1', NULL),
(76, 1, 'Logout dari sistem', '::1', NULL),
(77, 1, 'Login ke sistem', '::1', NULL),
(78, 1, 'Logout dari sistem', '::1', NULL),
(79, 1, 'Login ke sistem', '::1', NULL),
(80, 1, 'Login ke sistem', '::1', NULL),
(81, 1, 'Logout dari sistem', '::1', NULL),
(82, 1, 'Login ke sistem', '::1', NULL),
(83, 1, 'Logout dari sistem', '::1', NULL),
(84, 4, 'Logout dari sistem', '::1', NULL),
(85, 2, 'Logout dari sistem', '::1', NULL),
(86, 5, 'Logout dari sistem', '::1', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `bidang`
--

CREATE TABLE `bidang` (
  `id` bigint(20) NOT NULL,
  `nama_bidang` varchar(100) NOT NULL,
  `kuota` int(11) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bidang`
--

INSERT INTO `bidang` (`id`, `nama_bidang`, `kuota`, `deskripsi`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Teknologi Informasi', 10, 'Bidang Pengembangan Sistem', 'aktif', NULL, NULL),
(4, 'Teknologi Informatika', 10, 'Bidang Teknologi Informasi', 'aktif', NULL, NULL),
(5, 'Kesekretarian', 5, 'mengurus segala hal yang berhubungan dengan kesekretariatan, baik persuratan, data, dsb', 'aktif', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id` bigint(20) NOT NULL,
  `mahasiswa_id` bigint(20) DEFAULT NULL,
  `file_laporan` varchar(255) DEFAULT NULL,
  `tanggal_upload` date DEFAULT NULL,
  `status` enum('belum','direvisi','disetujui') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `logbook`
--

CREATE TABLE `logbook` (
  `id` bigint(20) NOT NULL,
  `mahasiswa_id` bigint(20) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `judul` varchar(200) DEFAULT NULL,
  `kegiatan` text DEFAULT NULL,
  `dokumentasi` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','disetujui','revisi') DEFAULT 'menunggu',
  `komentar` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `logbook`
--

INSERT INTO `logbook` (`id`, `mahasiswa_id`, `tanggal`, `judul`, `kegiatan`, `dokumentasi`, `status`, `komentar`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-07-03', 'penugasan 1', 'brainstorming dan cari software requirement', 'Screenshot 2026-07-03 072339.png', 'menunggu', NULL, '2026-07-03 00:57:13', '2026-07-03 00:57:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `nim` varchar(30) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `universitas_id` bigint(150) DEFAULT NULL,
  `program_studi_id` bigint(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `user_id`, `nim`, `nama`, `universitas_id`, `program_studi_id`, `alamat`, `no_hp`, `email`, `foto`, `created_at`, `updated_at`) VALUES
(1, 1, '230321', 'mila', 2, 2, 'malang dinoyo', '123456789', 'mila@mail.co', '1783003510_Screenshot (311).png', NULL, NULL),
(3, 1, '7865', 'dani', 1, 1, 'lamongan', '12421246663', 'dani@mail.co', '1783049563_Screenshot 2026-07-03 070759.png', NULL, NULL),
(4, 1, '00023', 'indah', 4, 5, 'kediri', '32428967', 'indah@mail.co', '1783049551_Screenshot (347).png', NULL, NULL),
(5, 4, NULL, 'mahendra rivaldo', NULL, NULL, NULL, NULL, 'mahendra22@gmail.com', NULL, '2026-07-06 22:38:44', NULL),
(6, 5, NULL, 'rivaldo aja', NULL, NULL, NULL, NULL, 'rivaldo123@gmail.com', NULL, '2026-07-07 22:46:41', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `jenis` enum('info','success','warning','error') DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `dibaca` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `user_id`, `judul`, `isi`, `jenis`, `link`, `dibaca`, `created_at`, `updated_at`) VALUES
(15, 3, 'Pendaftaran Diterima', 'Selamat, pendaftaran PKL Anda telah diterima.', 'success', '/pendaftaran/1', 0, '2026-07-02 03:45:23', '2026-07-02 03:45:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembimbing`
--

CREATE TABLE `pembimbing` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `bidang_id` bigint(20) DEFAULT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembimbing`
--

INSERT INTO `pembimbing` (`id`, `user_id`, `bidang_id`, `nip`, `nama`, `jabatan`, `no_hp`, `created_at`, `updated_at`) VALUES
(2, 1, 1, '0987600', 'Budi', 'sekben', '0987654321', NULL, NULL),
(3, 1, 1, '7300', 'Diah', 'kepala upt surabaya', '09876544444', NULL, NULL),
(4, 1, 5, '0888', 'ali', 'kabid kesekretariatan', '8978634799', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id` bigint(20) NOT NULL,
  `mahasiswa_id` bigint(20) DEFAULT NULL,
  `periode_id` bigint(20) DEFAULT NULL,
  `bidang_id` bigint(20) DEFAULT NULL,
  `pembimbing_id` bigint(20) DEFAULT NULL,
  `tanggal_daftar` date DEFAULT NULL,
  `surat_pengantar` varchar(255) DEFAULT NULL,
  `cv` varchar(255) DEFAULT NULL,
  `transkrip` varchar(255) DEFAULT NULL,
  `proposal` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','diterima','ditolak') DEFAULT 'menunggu',
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `mahasiswa_id`, `periode_id`, `bidang_id`, `pembimbing_id`, `tanggal_daftar`, `surat_pengantar`, `cv`, `transkrip`, `proposal`, `status`, `catatan_admin`, `created_at`, `updated_at`) VALUES
(2, 1, 1, 5, 4, '2026-07-02', 'surat_1783009121_9666.pdf', 'cv_1783009121_5566.pdf', 'transkrip_1783009121_1805.pdf', 'proposal_1783009121_4121.pdf', 'diterima', '', NULL, NULL),
(3, 3, 1, 1, 2, '2026-07-03', 'surat_1783035530_4692.pdf', 'cv_1783035530_8313.pdf', 'transkrip_1783035530_8449.pdf', 'proposal_1783035530_3076.pdf', 'ditolak', 'kurangnya minimal transkip nilai', NULL, NULL),
(4, 4, 1, 5, NULL, '2026-07-04', 'surat_1783038140_5586.pdf', 'cv_1783038140_9167.pdf', 'transkrip_1783038140_9558.pdf', 'proposal_1783038140_6298.pdf', 'menunggu', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `penilaian`
--

CREATE TABLE `penilaian` (
  `id` bigint(20) NOT NULL,
  `mahasiswa_id` bigint(20) DEFAULT NULL,
  `pembimbing_id` bigint(20) DEFAULT NULL,
  `disiplin` int(11) DEFAULT NULL,
  `komunikasi` int(11) DEFAULT NULL,
  `kerjasama` int(11) DEFAULT NULL,
  `tanggung_jawab` int(11) DEFAULT NULL,
  `inisiatif` int(11) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penilaian`
--

INSERT INTO `penilaian` (`id`, `mahasiswa_id`, `pembimbing_id`, `disiplin`, `komunikasi`, `kerjasama`, `tanggung_jawab`, `inisiatif`, `nilai_akhir`, `catatan`, `created_at`, `updated_at`) VALUES
(2, 1, 2, 75, 80, 80, 85, 70, 78.00, '', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `periode_pkl`
--

CREATE TABLE `periode_pkl` (
  `id` bigint(20) NOT NULL,
  `nama_periode` varchar(100) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('aktif','selesai') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `periode_pkl`
--

INSERT INTO `periode_pkl` (`id`, `nama_periode`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`, `updated_at`) VALUES
(1, 'genap', '2026-07-01', '2026-08-31', 'aktif', NULL, NULL),
(2, 'ganjil', '2026-01-01', '2026-02-28', 'selesai', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `program_studi`
--

CREATE TABLE `program_studi` (
  `id` bigint(20) NOT NULL,
  `nama_prodi` varchar(100) DEFAULT NULL,
  `jenjang` enum('D3','D4','S1','S2') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `program_studi`
--

INSERT INTO `program_studi` (`id`, `nama_prodi`, `jenjang`, `created_at`, `updated_at`) VALUES
(1, 'Teknik Informatika', 'S1', NULL, NULL),
(2, 'Adminstrasi Publik', 'S1', NULL, NULL),
(5, 'Sistem Informasi', 'S1', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_status`
--

CREATE TABLE `riwayat_status` (
  `id` bigint(20) NOT NULL,
  `pendaftaran_id` bigint(20) DEFAULT NULL,
  `status_lama` varchar(50) DEFAULT NULL,
  `status_baru` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `diubah_oleh` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) NOT NULL,
  `nama_role` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `nama_role`, `created_at`, `updated_at`) VALUES
(1, 'Admin', NULL, NULL),
(2, 'Pembimbing', NULL, NULL),
(3, 'Mahasiswa', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sertifikat`
--

CREATE TABLE `sertifikat` (
  `id` bigint(20) NOT NULL,
  `mahasiswa_id` bigint(20) DEFAULT NULL,
  `nomor_sertifikat` varchar(100) DEFAULT NULL,
  `tanggal_terbit` date DEFAULT NULL,
  `file_pdf` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sertifikat`
--

INSERT INTO `sertifikat` (`id`, `mahasiswa_id`, `nomor_sertifikat`, `tanggal_terbit`, `file_pdf`, `created_at`, `updated_at`) VALUES
(1, 1, '001/SIPKL/BM-JATIM/2026', '2026-07-03', 'sertifikat_230321.pdf', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `universitas`
--

CREATE TABLE `universitas` (
  `id` bigint(20) NOT NULL,
  `nama_universitas` varchar(200) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `universitas`
--

INSERT INTO `universitas` (`id`, `nama_universitas`, `alamat`, `kota`, `created_at`, `updated_at`) VALUES
(1, 'Universitas Islam Negeri Maulana Malik Ibrahim Malang', 'Jl. Gajayana No.50, Dinoyo, Lowokwaru', 'Malang', NULL, NULL),
(2, 'Universitas Negeri Surabaya', 'Jl. Lidah Wetan, Kec. Lakarsantri', 'Surabaya', NULL, NULL),
(4, 'Universitas Negeri Malang', 'Lowokwaru Kota Malang', 'Malang', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `role_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `password`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'Admin', 'admin@sipkl.com', 'admin123', 'aktif', NULL, NULL, NULL),
(2, 2, 'Aryo', 'aryo@pubm.go.id', '123456', 'aktif', NULL, NULL, NULL),
(3, 3, 'Bayu', 'bayu2@gmail.com', '123456', 'aktif', '', NULL, NULL),
(4, 3, 'mahendra rivaldo', 'mahendra22@gmail.com', '$2y$10$jbSIQIotVckNjxx91DZ1GeWNgPwC9zh8vOnYc8iE5ZfBLVuoMTHoe', 'aktif', NULL, '2026-07-06 22:38:44', NULL),
(5, 3, 'rivaldo aja', 'rivaldo123@gmail.com', '$2y$10$DeOqTI0zq5Yc9HsqIfvjVu0m.TpfRJOpJMhM6vd6dSDEeJzD2UOwO', 'aktif', NULL, '2026-07-07 22:46:41', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indeks untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `bidang`
--
ALTER TABLE `bidang`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indeks untuk tabel `logbook`
--
ALTER TABLE `logbook`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifikasi_user` (`user_id`);

--
-- Indeks untuk tabel `pembimbing`
--
ALTER TABLE `pembimbing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `bidang_id` (`bidang_id`);

--
-- Indeks untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `periode_id` (`periode_id`),
  ADD KEY `bidang_id` (`bidang_id`),
  ADD KEY `pembimbing_id` (`pembimbing_id`);

--
-- Indeks untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `pembimbing_id` (`pembimbing_id`);

--
-- Indeks untuk tabel `periode_pkl`
--
ALTER TABLE `periode_pkl`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `program_studi`
--
ALTER TABLE `program_studi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `riwayat_status`
--
ALTER TABLE `riwayat_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftaran_id` (`pendaftaran_id`),
  ADD KEY `diubah_oleh` (`diubah_oleh`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `sertifikat`
--
ALTER TABLE `sertifikat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indeks untuk tabel `universitas`
--
ALTER TABLE `universitas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_roles` (`role_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT untuk tabel `bidang`
--
ALTER TABLE `bidang`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `logbook`
--
ALTER TABLE `logbook`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `pembimbing`
--
ALTER TABLE `pembimbing`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `periode_pkl`
--
ALTER TABLE `periode_pkl`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `program_studi`
--
ALTER TABLE `program_studi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `riwayat_status`
--
ALTER TABLE `riwayat_status`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `sertifikat`
--
ALTER TABLE `sertifikat`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `universitas`
--
ALTER TABLE `universitas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`);

--
-- Ketidakleluasaan untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`);

--
-- Ketidakleluasaan untuk tabel `logbook`
--
ALTER TABLE `logbook`
  ADD CONSTRAINT `logbook_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`);

--
-- Ketidakleluasaan untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notifikasi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembimbing`
--
ALTER TABLE `pembimbing`
  ADD CONSTRAINT `pembimbing_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pembimbing_ibfk_2` FOREIGN KEY (`bidang_id`) REFERENCES `bidang` (`id`);

--
-- Ketidakleluasaan untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`),
  ADD CONSTRAINT `pendaftaran_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periode_pkl` (`id`),
  ADD CONSTRAINT `pendaftaran_ibfk_3` FOREIGN KEY (`bidang_id`) REFERENCES `bidang` (`id`),
  ADD CONSTRAINT `pendaftaran_ibfk_4` FOREIGN KEY (`pembimbing_id`) REFERENCES `pembimbing` (`id`);

--
-- Ketidakleluasaan untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  ADD CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`),
  ADD CONSTRAINT `penilaian_ibfk_2` FOREIGN KEY (`pembimbing_id`) REFERENCES `pembimbing` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_status`
--
ALTER TABLE `riwayat_status`
  ADD CONSTRAINT `riwayat_status_ibfk_1` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`),
  ADD CONSTRAINT `riwayat_status_ibfk_2` FOREIGN KEY (`diubah_oleh`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `sertifikat`
--
ALTER TABLE `sertifikat`
  ADD CONSTRAINT `sertifikat_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
