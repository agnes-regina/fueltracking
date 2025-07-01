-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2025 at 05:31 PM
-- Server version: 8.0.42
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fuel_tracking`
--

-- --------------------------------------------------------

--
-- Table structure for table `fuel_logs`
--

CREATE TABLE `fuel_logs` (
  `id` int NOT NULL,
  `nomor_unit` varchar(50) NOT NULL,
  `driver_name` varchar(100) NOT NULL,
  `status_progress` enum('waiting_pengawas','waiting_driver','waiting_depo','waiting_fuelman','done') DEFAULT 'waiting_pengawas',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pt_driver_name` varchar(100) DEFAULT NULL,
  `pt_driver_id` int DEFAULT NULL,
  `pt_unit_number` varchar(50) DEFAULT NULL,
  `pt_created_by` int DEFAULT NULL,
  `pt_created_at` timestamp NULL DEFAULT NULL,
  `pl_loading_start` datetime DEFAULT NULL,
  `pl_loading_end` datetime DEFAULT NULL,
  `pl_loading_location` text,
  `pl_segel_photo_1` text,
  `pl_segel_photo_2` text,
  `pl_segel_photo_3` text,
  `pl_segel_photo_4` text,
  `pl_segel_1` varchar(50) DEFAULT NULL,
  `pl_segel_2` varchar(50) DEFAULT NULL,
  `pl_segel_3` varchar(50) DEFAULT NULL,
  `pl_segel_4` varchar(50) DEFAULT NULL,
  `pl_doc_sampel` text,
  `pl_doc_do` text,
  `pl_doc_suratjalan` text,
  `pl_waktu_keluar_pertamina` datetime DEFAULT NULL,
  `pl_created_by` int DEFAULT NULL,
  `pl_created_at` timestamp NULL DEFAULT NULL,
  `dr_loading_start` datetime DEFAULT NULL,
  `dr_loading_end` datetime DEFAULT NULL,
  `dr_loading_location` text,
  `dr_segel_photo_1` text,
  `dr_segel_photo_2` text,
  `dr_segel_photo_3` text,
  `dr_segel_photo_4` text,
  `dr_doc_do` text,
  `dr_doc_surat_pertamina` text,
  `dr_doc_sampel_bbm` text,
  `dr_waktu_keluar_pertamina` datetime DEFAULT NULL,
  `dr_unload_start` datetime DEFAULT NULL,
  `dr_unload_end` datetime DEFAULT NULL,
  `dr_unload_location` text,
  `dr_created_by` int DEFAULT NULL,
  `dr_created_at` timestamp NULL DEFAULT NULL,
  `pd_arrived_at` datetime DEFAULT NULL,
  `pd_foto_kondisi_1` text,
  `pd_foto_kondisi_2` text,
  `pd_foto_kondisi_3` text,
  `pd_foto_kondisi_4` text,
  `pd_foto_sib` text,
  `pd_foto_ftw` text,
  `pd_foto_p2h` text,
  `pd_goto_msf` datetime DEFAULT NULL,
  `pd_created_by` int DEFAULT NULL,
  `pd_created_at` timestamp NULL DEFAULT NULL,
  `fm_unload_start` datetime DEFAULT NULL,
  `fm_unload_end` datetime DEFAULT NULL,
  `fm_location` text,
  `fm_segel_photo_awal_1` text,
  `fm_segel_photo_awal_2` text,
  `fm_segel_photo_awal_3` text,
  `fm_segel_photo_awal_4` text,
  `fm_photo_akhir_1` text,
  `fm_photo_akhir_2` text,
  `fm_photo_akhir_3` text,
  `fm_photo_akhir_4` text,
  `fm_photo_kejernihan` text,
  `fm_flowmeter` varchar(100) DEFAULT NULL,
  `fm_serial` varchar(100) DEFAULT NULL,
  `fm_awal` float DEFAULT NULL,
  `fm_akhir` float DEFAULT NULL,
  `fm_fuel_density` float DEFAULT NULL,
  `fm_fuel_temp` float DEFAULT NULL,
  `fm_fuel_fame` float DEFAULT NULL,
  `fm_created_by` int DEFAULT NULL,
  `fm_created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `fuel_logs`
--

INSERT INTO `fuel_logs` (`id`, `nomor_unit`, `driver_name`, `status_progress`, `created_at`, `updated_at`, `pt_driver_name`, `pt_driver_id`, `pt_unit_number`, `pt_created_by`, `pt_created_at`, `pl_loading_start`, `pl_loading_end`, `pl_loading_location`, `pl_segel_photo_1`, `pl_segel_photo_2`, `pl_segel_photo_3`, `pl_segel_photo_4`, `pl_segel_1`, `pl_segel_2`, `pl_segel_3`, `pl_segel_4`, `pl_doc_sampel`, `pl_doc_do`, `pl_doc_suratjalan`, `pl_waktu_keluar_pertamina`, `pl_created_by`, `pl_created_at`, `dr_loading_start`, `dr_loading_end`, `dr_loading_location`, `dr_segel_photo_1`, `dr_segel_photo_2`, `dr_segel_photo_3`, `dr_segel_photo_4`, `dr_doc_do`, `dr_doc_surat_pertamina`, `dr_doc_sampel_bbm`, `dr_waktu_keluar_pertamina`, `dr_unload_start`, `dr_unload_end`, `dr_unload_location`, `dr_created_by`, `dr_created_at`, `pd_arrived_at`, `pd_foto_kondisi_1`, `pd_foto_kondisi_2`, `pd_foto_kondisi_3`, `pd_foto_kondisi_4`, `pd_foto_sib`, `pd_foto_ftw`, `pd_foto_p2h`, `pd_goto_msf`, `pd_created_by`, `pd_created_at`, `fm_unload_start`, `fm_unload_end`, `fm_location`, `fm_segel_photo_awal_1`, `fm_segel_photo_awal_2`, `fm_segel_photo_awal_3`, `fm_segel_photo_awal_4`, `fm_photo_akhir_1`, `fm_photo_akhir_2`, `fm_photo_akhir_3`, `fm_photo_akhir_4`, `fm_photo_kejernihan`, `fm_flowmeter`, `fm_serial`, `fm_awal`, `fm_akhir`, `fm_fuel_density`, `fm_fuel_temp`, `fm_fuel_fame`, `fm_created_by`, `fm_created_at`) VALUES
(1, 'B8916981', 'dio', 'done', '2025-06-29 12:55:29', '2025-06-29 13:08:52', 'dio', NULL, 'B8916981', 2, '2025-06-29 12:55:29', '2025-06-29 19:59:00', '2025-06-29 19:59:00', '-7.8888101,110.3018501', 'uploads/6861390bc6062_1751202059.jpeg', 'uploads/6861390bc669e_1751202059.jpeg', 'uploads/6861390bc6900_1751202059.jpeg', 'uploads/6861390bc6c5c_1751202059.jpeg', '123', '123', '123', '123', 'uploads/6861390bc6f06_1751202059.jpeg', 'uploads/6861390bc734e_1751202059.jpeg', 'uploads/6861390bc778f_1751202059.jpeg', '2025-06-29 20:00:00', 3, '2025-06-29 13:00:59', '2025-06-29 20:02:00', '2025-06-29 20:02:00', '-7.8888101,110.3018501', 'uploads/686139bfe62fd_1751202239.jpeg', 'uploads/686139bfe6898_1751202239.jpeg', 'uploads/686139bfe6dcb_1751202239.jpeg', 'uploads/686139bfe7688_1751202239.jpeg', 'uploads/686139bfe7b7b_1751202239.jpeg', 'uploads/686139bfe83f4_1751202239.jpeg', 'uploads/686139bfe8d96_1751202239.jpeg', '2025-06-29 20:02:00', '2025-06-29 20:03:00', '2025-06-29 20:03:00', '-7.8888101,110.3018501', 4, '2025-06-29 13:03:59', '2025-06-29 20:05:00', 'uploads/68613a7033e2c_1751202416.jpeg', 'uploads/68613a703427a_1751202416.jpeg', 'uploads/68613a7034974_1751202416.jpeg', 'uploads/68613a7034c36_1751202416.jpeg', 'uploads/68613a7034ed6_1751202416.jpeg', 'uploads/68613a70351a7_1751202416.jpeg', 'uploads/68613a7035a70_1751202416.jpeg', '2025-06-29 20:06:00', 5, '2025-06-29 13:06:56', '2025-06-29 20:07:00', '2025-06-29 20:07:00', '-7.8888101,110.3018501', 'uploads/68613ae4b85db_1751202532.jpeg', 'uploads/68613ae4b9246_1751202532.jpeg', 'uploads/68613ae4b9880_1751202532.jpeg', 'uploads/68613ae4b9b6b_1751202532.jpeg', 'uploads/68613ae4ba1fa_1751202532.jpeg', 'uploads/68613ae4ba582_1751202532.jpeg', 'uploads/68613ae4ba963_1751202532.jpeg', 'uploads/68613ae4baf12_1751202532.jpeg', 'uploads/68613ae4bb415_1751202532.jpeg', 'Flowmeter A', '2314', 10, 10, 23, 23, 41, 6, '2025-06-29 13:08:52'),
(2, 'B234fas', 'dio1', 'waiting_driver', '2025-06-29 14:06:31', '2025-06-29 14:15:32', 'dio1', NULL, 'B234fas', 2, '2025-06-29 14:06:31', '2025-06-29 21:13:00', '2025-06-29 21:13:00', '-7.8888101,110.3018501', 'uploads/68614a39eedd7_1751206457.jpeg', 'uploads/68614a39ef39f_1751206457.jpeg', 'uploads/68614a39efa64_1751206457.jpeg', 'uploads/68614a39f017e_1751206457.jpeg', '11', '11', '11', '11', 'uploads/68614a39f08a1_1751206457.jpeg', 'uploads/68614a39f12be_1751206457.jpeg', 'uploads/68614a39f16d9_1751206457.jpeg', '2025-06-29 21:13:00', 3, '2025-06-29 14:14:17', '2025-06-29 21:14:00', '2025-06-29 21:14:00', '-7.8888101,110.3018501', 'uploads/68614a83f1311_1751206531.jpeg', 'uploads/68614a83f1a33_1751206531.jpeg', 'uploads/68614a83f228e_1751206531.jpeg', 'uploads/68614a83f2ac9_1751206531.jpeg', 'uploads/68614a83f33fc_1751206531.jpeg', 'uploads/68614a83f3b8d_1751206531.jpeg', 'uploads/68614a83f40b4_1751206531.jpeg', '2025-06-29 21:14:00', NULL, NULL, NULL, 4, '2025-06-29 14:15:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'B2145123', 'Driver Utama', 'waiting_pengawas', '2025-06-29 15:02:53', '2025-06-29 15:02:53', 'Driver Utama', NULL, 'B2145123', 2, '2025-06-29 15:02:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, '27ghgaiusf', 'Driver Utama', 'waiting_pengawas', '2025-06-29 15:21:10', '2025-06-29 15:21:10', 'Driver Utama', 4, '27ghgaiusf', 2, '2025-06-29 15:21:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pengawas_transportir','pengawas_lapangan','driver','pengawas_depo','fuelman','gl_pama') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `full_name`, `email`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2b$12$VVQu0jV5MTxi/JodXcM/reglojqsiegUfRdtZZhFNlLQs2w7P9Zpm', 'admin', 'System Administrator', 'admin@fueltrack.com', NULL, 1, '2025-06-29 12:16:23', '2025-06-29 12:32:09'),
(2, 'pengawas', '$2b$12$VVQu0jV5MTxi/JodXcM/reglojqsiegUfRdtZZhFNlLQs2w7P9Zpm', 'pengawas_transportir', 'Pengawas Transportir 1', 'pengawas1@fueltrack.com', NULL, 1, '2025-06-29 12:16:23', '2025-06-29 12:53:02'),
(3, 'lapangan', '$2b$12$VVQu0jV5MTxi/JodXcM/reglojqsiegUfRdtZZhFNlLQs2w7P9Zpm', 'pengawas_lapangan', 'Pengawas Lapangan 1', 'lapangan1@fueltrack.com', NULL, 1, '2025-06-29 12:16:23', '2025-06-29 12:53:05'),
(4, 'driver', '$2b$12$VVQu0jV5MTxi/JodXcM/reglojqsiegUfRdtZZhFNlLQs2w7P9Zpm', 'driver', 'Driver Utama', 'driver1@fueltrack.com', NULL, 1, '2025-06-29 12:16:23', '2025-06-29 12:53:09'),
(5, 'depo', '$2b$12$VVQu0jV5MTxi/JodXcM/reglojqsiegUfRdtZZhFNlLQs2w7P9Zpm', 'pengawas_depo', 'Pengawas Depo 1', 'depo1@fueltrack.com', NULL, 1, '2025-06-29 12:16:23', '2025-06-29 12:53:15'),
(6, 'fuelman', '$2b$12$VVQu0jV5MTxi/JodXcM/reglojqsiegUfRdtZZhFNlLQs2w7P9Zpm', 'fuelman', 'Fuelman 1', 'fuelman1@fueltrack.com', NULL, 1, '2025-06-29 12:16:23', '2025-06-29 12:53:19'),
(7, 'glpama', '$2b$12$VVQu0jV5MTxi/JodXcM/reglojqsiegUfRdtZZhFNlLQs2w7P9Zpm', 'gl_pama', 'GL PAMA 1', 'glpama1@fueltrack.com', NULL, 1, '2025-06-29 12:16:23', '2025-06-29 12:53:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fuel_logs`
--
ALTER TABLE `fuel_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pt_created_by` (`pt_created_by`),
  ADD KEY `pl_created_by` (`pl_created_by`),
  ADD KEY `dr_created_by` (`dr_created_by`),
  ADD KEY `pd_created_by` (`pd_created_by`),
  ADD KEY `fm_created_by` (`fm_created_by`),
  ADD KEY `idx_status_progress` (`status_progress`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_nomor_unit` (`nomor_unit`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_user_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fuel_logs`
--
ALTER TABLE `fuel_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fuel_logs`
--
ALTER TABLE `fuel_logs`
  ADD CONSTRAINT `fuel_logs_ibfk_1` FOREIGN KEY (`pt_created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fuel_logs_ibfk_2` FOREIGN KEY (`pl_created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fuel_logs_ibfk_3` FOREIGN KEY (`dr_created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fuel_logs_ibfk_4` FOREIGN KEY (`pd_created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fuel_logs_ibfk_5` FOREIGN KEY (`fm_created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
