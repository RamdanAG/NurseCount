-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 05:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nursecount`
--

-- --------------------------------------------------------

--
-- Table structure for table `dosage_data`
--

CREATE TABLE `dosage_data` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `umur` int(11) NOT NULL,
  `alamat` varchar(500) NOT NULL,
  `berat_kg` decimal(5,2) NOT NULL,
  `tinggi_cm` decimal(5,2) NOT NULL,
  `aktivitas_factor` decimal(3,2) NOT NULL,
  `stress_factor` decimal(3,2) NOT NULL,
  `bmr` decimal(8,2) NOT NULL,
  `total_energi` decimal(8,2) NOT NULL,
  `protein_g` decimal(8,2) NOT NULL,
  `karbohidrat_g` decimal(8,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dosage_data`
--

INSERT INTO `dosage_data` (`id`, `user_id`, `nama`, `umur`, `alamat`, `berat_kg`, `tinggi_cm`, `aktivitas_factor`, `stress_factor`, `bmr`, `total_energi`, `protein_g`, `karbohidrat_g`, `created_at`) VALUES
(1, 6, 'udin', 19, 'cibingbin', 12.00, 90.00, 1.73, 1.30, 592.50, 1328.68, 49.83, 182.69, '2025-05-10 14:19:07'),
(2, 6, 'udin', 19, 'cibingbin', 12.00, 90.00, 1.73, 1.30, 592.50, 1328.68, 49.83, 182.69, '2025-05-10 14:19:19'),
(3, 6, 'udin', 19, 'cibingbin', 12.00, 90.00, 1.73, 1.30, 592.50, 1328.68, 49.83, 182.69, '2025-05-10 14:21:10');

-- --------------------------------------------------------

--
-- Table structure for table `fluid_data`
--

CREATE TABLE `fluid_data` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `umur` int(11) NOT NULL,
  `alamat` varchar(500) NOT NULL,
  `mode_calc` enum('per24','perjam') NOT NULL,
  `berat_kg` decimal(5,2) NOT NULL,
  `kondisi` varchar(50) NOT NULL,
  `total_ml_per24` decimal(8,2) DEFAULT NULL,
  `ml_per_jam` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fluid_data`
--

INSERT INTO `fluid_data` (`id`, `user_id`, `nama`, `umur`, `alamat`, `mode_calc`, `berat_kg`, `kondisi`, `total_ml_per24`, `ml_per_jam`, `created_at`) VALUES
(1, 6, 'cahwiguna', 19, 'cibingbin', 'per24', 12.00, 'Demam', 1100.00, NULL, '2025-05-10 15:08:44');

-- --------------------------------------------------------

--
-- Table structure for table `gcs_data`
--

CREATE TABLE `gcs_data` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `umur` int(11) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `jenis_kelamin` varchar(20) NOT NULL,
  `skor_eye` int(11) NOT NULL,
  `skor_verbal` int(11) NOT NULL,
  `skor_motor` int(11) NOT NULL,
  `skor_total` int(11) NOT NULL,
  `status_kesadaran` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gcs_data`
--

INSERT INTO `gcs_data` (`id`, `user_id`, `nama_lengkap`, `umur`, `alamat`, `jenis_kelamin`, `skor_eye`, `skor_verbal`, `skor_motor`, `skor_total`, `status_kesadaran`, `created_at`) VALUES
(1, 6, 'ramdan', 12, 'jl braga', 'Laki-laki', 3, 3, 2, 8, 'Somnolen (Meracau/Gelisah)', '2025-05-10 12:15:26'),
(2, 7, 'rfdasd', 213, '3dasdasd', 'Laki-laki', 3, 3, 6, 12, 'Apatis (Kurang Perhatian)', '2025-05-10 12:51:54');

-- --------------------------------------------------------

--
-- Table structure for table `hasil_imt`
--

CREATE TABLE `hasil_imt` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_kelamin` varchar(20) DEFAULT NULL,
  `usia` int(11) DEFAULT NULL,
  `berat_kg` float DEFAULT NULL,
  `tinggi_cm` float DEFAULT NULL,
  `imt` float DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `nama_lengkap` varchar(50) NOT NULL,
  `umur` varchar(100) NOT NULL,
  `alamat` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasil_kalori`
--

CREATE TABLE `hasil_kalori` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `umur` int(11) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `berat_kg` float DEFAULT NULL,
  `tinggi_cm` float DEFAULT NULL,
  `aktivitas` float DEFAULT NULL,
  `stres` float DEFAULT NULL,
  `bmr` float DEFAULT NULL,
  `total_energi` float DEFAULT NULL,
  `protein` float DEFAULT NULL,
  `karbohidrat` float DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasil_lukabakar`
--

CREATE TABLE `hasil_lukabakar` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID dari tabel users',
  `tbsa` decimal(5,2) NOT NULL COMMENT 'Total Body Surface Area (%)',
  `total_cairan` int(11) NOT NULL COMMENT 'Total cairan (mL)',
  `cairan_8jam` int(11) NOT NULL COMMENT 'Cairan 8 jam pertama (mL)',
  `cairan_16jam` int(11) NOT NULL COMMENT 'Cairan 16 jam berikutnya (mL)',
  `berat_kg` decimal(5,2) NOT NULL COMMENT 'Berat badan (kg)',
  `usia` int(11) NOT NULL COMMENT 'Usia (tahun)',
  `foto` varchar(255) DEFAULT NULL COMMENT 'Nama file foto luka',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hasil_lukabakar`
--

INSERT INTO `hasil_lukabakar` (`id`, `user_id`, `tbsa`, `total_cairan`, `cairan_8jam`, `cairan_16jam`, `berat_kg`, `usia`, `foto`, `created_at`) VALUES
(1, 5, 9.00, 432, 216, 216, 12.00, 32, '1746855709_WhatsApp Image 2025-05-07 at 21.24.26.jpeg', '2025-05-10 05:41:49'),
(2, 6, 4.50, 180, 90, 90, 10.00, 12, '1746875228_zeta.png', '2025-05-10 11:07:08');

-- --------------------------------------------------------

--
-- Table structure for table `laju_infus`
--

CREATE TABLE `laju_infus` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_infus` varchar(20) NOT NULL,
  `volume_ml` decimal(10,2) NOT NULL,
  `waktu_jam` int(11) NOT NULL,
  `menit_opt` int(11) DEFAULT 0,
  `dosis_mg` decimal(10,2) DEFAULT NULL,
  `konsentrasi` decimal(10,2) DEFAULT NULL,
  `volume_obat` decimal(10,2) DEFAULT NULL,
  `laju_ml_jam` decimal(10,2) NOT NULL,
  `laju_tetes_menit` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Nama_Lengkap` varchar(50) NOT NULL,
  `umur` varchar(100) NOT NULL,
  `alamat` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','resepsionis','customer') NOT NULL DEFAULT 'customer',
  `lang` enum('id','en','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `lang`) VALUES
(6, 'ramdan', '$2y$10$HVoKIEs3zmBjyGKxDZpjM.XCvOkV3gfLSaKLRnPrebBGiQQ7fIS3i', 'ramdan', '', 'id'),
(7, 'udin', '$2y$10$X/qvjkKCtm6TJv/vpQWiK.JjerY2ObBxDIytwlyw2X8/QJnXjOrOq', 'udin', '', 'id');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dosage_data`
--
ALTER TABLE `dosage_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `fluid_data`
--
ALTER TABLE `fluid_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `gcs_data`
--
ALTER TABLE `gcs_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hasil_imt`
--
ALTER TABLE `hasil_imt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hasil_kalori`
--
ALTER TABLE `hasil_kalori`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hasil_lukabakar`
--
ALTER TABLE `hasil_lukabakar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `laju_infus`
--
ALTER TABLE `laju_infus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dosage_data`
--
ALTER TABLE `dosage_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fluid_data`
--
ALTER TABLE `fluid_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gcs_data`
--
ALTER TABLE `gcs_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hasil_imt`
--
ALTER TABLE `hasil_imt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hasil_kalori`
--
ALTER TABLE `hasil_kalori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hasil_lukabakar`
--
ALTER TABLE `hasil_lukabakar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `laju_infus`
--
ALTER TABLE `laju_infus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dosage_data`
--
ALTER TABLE `dosage_data`
  ADD CONSTRAINT `dosage_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `fluid_data`
--
ALTER TABLE `fluid_data`
  ADD CONSTRAINT `fluid_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `gcs_data`
--
ALTER TABLE `gcs_data`
  ADD CONSTRAINT `gcs_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hasil_kalori`
--
ALTER TABLE `hasil_kalori`
  ADD CONSTRAINT `hasil_kalori_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
