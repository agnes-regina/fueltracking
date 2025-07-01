-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 04:28 PM
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
  `status_progress` enum('waiting_pengawas','waiting_driver','driver_loading_done','waiting_depo','waiting_fuelman','done') DEFAULT 'waiting_pengawas',
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
(5, 'B 3245 CA', 'Driver Utama', 'done', '2025-07-01 07:55:17', '2025-07-01 09:14:41', 'Driver Utama', 4, 'B 3245 CA', 2, '2025-07-01 07:55:17', '2025-07-01 14:55:00', '2025-07-01 14:56:00', '-7.8901133,110.3003608', 'uploads/1751356631_686394d7eb2ec_Gambar1.png', 'uploads/1751356631_686394d7eb802_Gambar1.png', 'uploads/1751356631_686394d7ebfdc_Gambar1.png', 'uploads/1751356631_686394d7ec5ca_Gambar1.png', '1242', '131241', '241512', '31421231542', 'uploads/1751356631_686394d7eca5e_Gambar1.png', 'uploads/1751356631_686394d7ecf3f_Gambar1.png', 'uploads/1751356631_686394d7ed3bd_Gambar1.png', NULL, 3, '2025-07-01 07:57:11', '2025-07-01 15:19:00', '2025-07-01 15:19:00', '-7.8901133,110.3003608', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-01 15:19:00', '2025-07-01 15:19:00', '2025-07-01 15:19:00', '-7.8901133,110.3003608', 4, '2025-07-01 08:19:23', '2025-07-01 15:20:00', 'uploads/68639a81a5630_1751358081.png', 'uploads/68639a81a5db6_1751358081.png', 'uploads/68639a81a627a_1751358081.png', 'uploads/68639a81a66cc_1751358081.png', 'uploads/68639a81a6d74_1751358081.png', 'uploads/68639a81a738d_1751358081.png', 'uploads/68639a81a7a2f_1751358081.png', '2025-07-01 15:20:00', 5, '2025-07-01 08:21:21', '2025-07-01 16:10:00', '2025-07-01 16:11:00', '-7.8901133,110.3003608', 'uploads/6863a7018dcab_1751361281.jpg', 'uploads/6863a7018e2f6_1751361281.jpg', 'uploads/6863a7018ea28_1751361281.jpg', 'uploads/6863a7018efe5_1751361281.jpg', NULL, NULL, NULL, NULL, 'uploads/6863a7018f6a1_1751361281.jpg', 'A', '124213', 211541, 324213, 124123, 123, 2, 6, '2025-07-01 09:14:41'),
(6, 'B 124 DD', 'Driver Utama', 'done', '2025-07-01 12:26:02', '2025-07-01 14:01:38', 'Driver Utama', 4, 'B 124 DD', 2, '2025-07-01 12:26:02', '2025-07-01 19:26:00', '2025-07-01 19:26:00', '-7.5754887,110.8243272', 'uploads/1751372862_6863d43e7473b_camera-photo-1751372824609.jpg', 'uploads/1751372862_6863d43e74b26_camera-photo-1751372827407.jpg', 'uploads/1751372862_6863d43e74ed9_camera-photo-1751372842624.jpg', 'uploads/1751372862_6863d43e7554e_camera-photo-1751372846332.jpg', '1222', '123123', '2343', '3434', 'uploads/1751372862_6863d43e75b00_camera-photo-1751372851425.jpg', 'uploads/1751372862_6863d43e7607e_camera-photo-1751372854541.jpg', 'uploads/1751372862_6863d43e76957_camera-photo-1751372857841.jpg', NULL, 3, '2025-07-01 12:27:42', '2025-07-01 19:37:00', '2025-07-01 19:37:00', '-7.5754887,110.8243272', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-01 19:37:00', '2025-07-01 20:31:00', '2025-07-01 20:31:00', '-7.5754887,110.8243272', 4, '2025-07-01 12:37:20', '2025-07-01 20:40:00', 'uploads/6863e57566d13_1751377269.jpg', 'uploads/6863e575672d8_1751377269.jpg', 'uploads/6863e57567c6a_1751377269.jpg', 'uploads/6863e575684a0_1751377269.jpg', 'uploads/6863e57568a5c_1751377269.jpg', 'uploads/6863e57569178_1751377269.jpg', 'uploads/6863e57569cb7_1751377269.jpg', '2025-07-01 20:40:00', 5, '2025-07-01 13:41:09', '2025-07-01 21:00:00', '2025-07-01 21:00:00', '-7.5754887,110.8243272', 'uploads/6863ea4237757_1751378498.jpg', 'uploads/6863ea4237d10_1751378498.jpg', 'uploads/6863ea4238627_1751378498.jpg', 'uploads/6863ea4238c03_1751378498.jpg', 'uploads/6863ea42396a8_1751378498.jpg', 'uploads/6863ea4239d98_1751378498.jpg', 'uploads/6863ea4240835_1751378498.jpg', 'uploads/6863ea4240c8c_1751378498.jpg', 'uploads/6863ea424118f_1751378498.jpg', 'Flow Meter Line 1', 'SN2-xxxxxx', 12, 32432, 34, 12, 123, 6, '2025-07-01 14:01:38'),
(7, 'B43231', 'Driver Utama', 'done', '2025-07-01 12:32:00', '2025-07-01 14:16:27', 'Driver Utama', 4, 'B43231', 2, '2025-07-01 12:32:00', '2025-07-01 19:32:00', '2025-07-01 19:32:00', '-7.5754887,110.8243272', 'uploads/1751373177_6863d5792ced4_camera-photo-1751373145220.jpg', 'uploads/1751373177_6863d5792e6a6_camera-photo-1751373147352.jpg', 'uploads/1751373177_6863d5792ec15_camera-photo-1751373156649.jpg', 'uploads/1751373177_6863d5792f146_camera-photo-1751373161441.jpg', '123', '12412', '314123', '1342124', 'uploads/1751373177_6863d5792f67e_camera-photo-1751373164937.jpg', 'uploads/1751373177_6863d5792fb6c_camera-photo-1751373167219.jpg', 'uploads/1751373177_6863d5793024e_camera-photo-1751373170463.jpg', NULL, 3, '2025-07-01 12:32:57', '2025-07-01 21:06:00', '2025-07-01 21:06:00', '-7.5754887,110.8243272', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-01 21:06:00', '2025-07-01 21:07:00', '2025-07-01 21:07:00', '-7.5754887,110.8243272', 4, '2025-07-01 14:07:02', '2025-07-01 21:07:00', 'uploads/6863ebdb39e62_1751378907.jpg', 'uploads/6863ebdb3a34e_1751378907.jpg', 'uploads/6863ebdb3aacd_1751378907.jpg', 'uploads/6863ebdb3b3ca_1751378907.jpg', 'uploads/6863ebdb3bc6a_1751378907.jpg', 'uploads/6863ebdb3c6c8_1751378907.jpg', 'uploads/6863ebdb3cf50_1751378907.jpg', '2025-07-01 21:08:00', 5, '2025-07-01 14:08:27', '2025-07-01 21:08:00', '2025-07-01 21:15:00', '-7.5754887,110.8243272', 'uploads/6863edbb62073_1751379387.jpg', 'uploads/6863edbb62522_1751379387.jpg', 'uploads/6863edbb62ab1_1751379387.jpg', 'uploads/6863edbb6325b_1751379387.jpg', 'uploads/6863edbb6378c_1751379387.jpg', 'uploads/6863edbb63e8a_1751379387.jpg', 'uploads/6863edbb6449f_1751379387.jpg', 'uploads/6863edbb648b1_1751379387.jpg', 'uploads/6863edbb64d64_1751379387.jpg', 'Flow Meter Line 1', 'SN2-xxxxxx', 12, 144, 213, 32, 213, 6, '2025-07-01 14:16:27'),
(8, 'B sadf 12', 'Driver Utama', 'waiting_driver', '2025-07-01 12:34:38', '2025-07-01 12:35:30', 'Driver Utama', 4, 'B sadf 12', 2, '2025-07-01 12:34:38', '2025-07-01 19:34:00', '2025-07-01 19:34:00', '-7.5754887,110.8243272', 'uploads/1751373330_6863d6122c126_camera-photo-1751373308507.jpg', 'uploads/1751373330_6863d6122c60f_camera-photo-1751373310685.jpg', 'uploads/1751373330_6863d6122d63e_camera-photo-1751373313781.jpg', 'uploads/1751373330_6863d6122dc6d_camera-photo-1751373319126.jpg', '123', '12312', '21312', '4123', 'uploads/1751373330_6863d6122e46a_camera-photo-1751373323085.jpg', 'uploads/1751373330_6863d6122e9dd_camera-photo-1751373325475.jpg', 'uploads/1751373330_6863d6122edf6_camera-photo-1751373327983.jpg', NULL, 3, '2025-07-01 12:35:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
(1, 'admin', '$2b$12$VVQu0jV5MTxi/JodXcM/reglojqsiegUfRdtZZhFNlLQs2w7P9Zpm', 'admin', 'System Administrator', 'admin@fueltrack.com', '089', 1, '2025-06-29 12:16:23', '2025-06-29 23:17:13'),
(2, 'pengawas', '$2b$12$VVQu0jV5MTxi/JodXcM/reglojqsiegUfRdtZZhFNlLQs2w7P9Zpm', 'pengawas_transportir', 'Pengawas Transportir 1', 'pengawas1@fueltrack.com', NULL, 1, '2025-06-29 12:16:23', '2025-06-29 17:08:11'),
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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
