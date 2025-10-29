-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 22, 2025 at 03:52 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myormawa_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id` int NOT NULL,
  `ormawa_id` int NOT NULL,
  `nama_event` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `lokasi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form`
--

CREATE TABLE `form` (
  `id` int NOT NULL,
  `form_info_id` int NOT NULL DEFAULT '1',
  `nama` varchar(30) NOT NULL,
  `tipe` enum('text','number','file','textarea','radio','select','email') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `label` varchar(20) NOT NULL,
  `opsi` text NOT NULL,
  `created_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `form`
--

INSERT INTO `form` (`id`, `form_info_id`, `nama`, `tipe`, `label`, `opsi`, `created_at`) VALUES
(5, 12, 'hallo', 'text', 'hallo', '', '2025-10-22 15:28:09'),
(6, 12, 'aku_siapa', 'number', 'aku siapa', '', '2025-10-22 15:29:01'),
(7, 12, 'hahah', 'textarea', 'hahah', '', '2025-10-22 15:29:11'),
(8, 12, 'ahhaah', 'file', 'ahhaah', '', '2025-10-22 15:29:20'),
(9, 12, '1', 'radio', '1', '[\"1\",\"2\"]', '2025-10-22 15:29:31'),
(10, 12, 'test', 'select', 'test', '[\"pp\",\"ppp\"]', '2025-10-22 15:29:44'),
(11, 13, 'ppp', 'email', 'ppp', '', '2025-10-22 15:34:59');

-- --------------------------------------------------------

--
-- Table structure for table `form_info`
--

CREATE TABLE `form_info` (
  `id` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `form_info`
--

INSERT INTO `form_info` (`id`, `judul`, `deskripsi`, `gambar`, `created_at`, `updated_at`) VALUES
(12, 'AWOKAWOKAWOAK', 'pppppppppppp', '', '2025-10-22 15:17:20', '2025-10-22 15:17:20'),
(13, 'pppppppppp', 'pppp', '', '2025-10-22 15:33:15', '2025-10-22 15:33:15');

-- --------------------------------------------------------

--
-- Table structure for table `kehadiran`
--

CREATE TABLE `kehadiran` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('hadir','tidak hadir','terlambat') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `absen_mulai` datetime NOT NULL,
  `absen_selesai` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oprek`
--

CREATE TABLE `oprek` (
  `id` int NOT NULL,
  `ormawa_id` int NOT NULL,
  `nama_oprek` varchar(100) NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `syarat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ormawa`
--

CREATE TABLE `ormawa` (
  `id` int NOT NULL,
  `nama_ormawa` varchar(20) NOT NULL,
  `logo` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submit`
--

CREATE TABLE `submit` (
  `id` int NOT NULL,
  `form_id` int NOT NULL,
  `user_id` int NOT NULL,
  `field_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `field_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  `submitted_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `submit`
--

INSERT INTO `submit` (`id`, `form_id`, `user_id`, `field_name`, `field_value`, `created_at`, `submitted_at`) VALUES
(7, 5, 999, 'hallo', 'hallo', '2025-10-22 15:32:21', '2025-10-22 15:32:21'),
(8, 6, 999, 'aku_siapa', '3', '2025-10-22 15:32:21', '2025-10-22 15:32:21'),
(9, 7, 999, 'hahah', 'haha', '2025-10-22 15:32:21', '2025-10-22 15:32:21'),
(10, 8, 999, 'ahhaah', '68f8f9050c08c_Acara_13&14_E41241046_Alif Micca Muhammad_Upload .docx', '2025-10-22 15:32:21', '2025-10-22 15:32:21'),
(11, 9, 999, '1', '2', '2025-10-22 15:32:21', '2025-10-22 15:32:21'),
(12, 10, 999, 'test', 'ppp', '2025-10-22 15:32:21', '2025-10-22 15:32:21'),
(13, 11, 999, 'ppp', 'ppp@gmailco', '2025-10-22 15:35:16', '2025-10-22 15:35:16'),
(14, 11, 999, 'ppp', 'ppp@gmailco', '2025-10-22 15:43:52', '2025-10-22 15:43:52');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `nama` varchar(20) NOT NULL,
  `nim` varchar(10) NOT NULL,
  `username` varchar(25) NOT NULL,
  `email` varchar(20) NOT NULL,
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `level` enum('1','2','3') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `nama`, `nim`, `username`, `email`, `password`, `level`) VALUES
(1, 'Super', '41240975', 'zaky', 'zaky@gmail.com', 'ae919f3dc4f8ae578ff9a1f35d97ecb1', '1'),
(2, 'admin', '', '', 'hmjti@polije.ac.id', 'faiq1', '2'),
(3, 'ukm o', '', 'olahraga', 'ukmo@polije.ac.id', '123456', '2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ormawa_id` (`ormawa_id`);

--
-- Indexes for table `form`
--
ALTER TABLE `form`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_info_id` (`form_info_id`);

--
-- Indexes for table `form_info`
--
ALTER TABLE `form_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kehadiran`
--
ALTER TABLE `kehadiran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `oprek`
--
ALTER TABLE `oprek`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ormawa_id` (`ormawa_id`);

--
-- Indexes for table `ormawa`
--
ALTER TABLE `ormawa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `submit`
--
ALTER TABLE `submit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `form`
--
ALTER TABLE `form`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `form_info`
--
ALTER TABLE `form_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `kehadiran`
--
ALTER TABLE `kehadiran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oprek`
--
ALTER TABLE `oprek`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ormawa`
--
ALTER TABLE `ormawa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submit`
--
ALTER TABLE `submit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`ormawa_id`) REFERENCES `ormawa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `form`
--
ALTER TABLE `form`
  ADD CONSTRAINT `form_ibfk_1` FOREIGN KEY (`form_info_id`) REFERENCES `form_info` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kehadiran`
--
ALTER TABLE `kehadiran`
  ADD CONSTRAINT `kehadiran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kehadiran_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `oprek`
--
ALTER TABLE `oprek`
  ADD CONSTRAINT `oprek_ibfk_1` FOREIGN KEY (`ormawa_id`) REFERENCES `ormawa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `submit`
--
ALTER TABLE `submit`
  ADD CONSTRAINT `submit_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `form` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
