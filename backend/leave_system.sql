-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2026 at 11:45 AM
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
-- Database: `leave_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `leave_period` enum('full','morning','afternoon') NOT NULL DEFAULT 'full',
  `total_days` decimal(4,1) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_remark` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `user_id`, `leave_type_id`, `start_date`, `end_date`, `leave_period`, `total_days`, `reason`, `status`, `admin_remark`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2026-11-14', '2026-11-14', 'full', 1.0, 'มีไข้สูงและปวดศีรษะ ไปพบแพทย์ที่คลินิก', 'pending', NULL, '2026-11-14 10:03:32', '2026-11-14 10:03:32'),
(2, 3, 3, '2026-11-19', '2026-11-21', 'full', 3.0, 'เดินทางไปพักผ่อนต่างจังหวัดกับครอบครัว', 'pending', NULL, '2026-11-14 10:03:32', '2026-11-14 10:03:32'),
(3, 2, 2, '2026-11-04', '2026-11-04', 'morning', 0.5, 'ติดต่อทำใบขับขี่ที่กรมการขนส่ง', 'approved', 'อนุมัติตามสิทธิ์เรียบร้อย', '2026-11-14 10:03:32', '2026-11-14 10:03:32'),
(4, 3, 2, '2026-11-10', '2026-11-10', 'full', 1.0, 'ติดธุระด่วนที่บ้าน', 'rejected', 'ช่วงวันดังกล่าวมีการประชุมสรุปแคมเปญประจำเดือน', '2026-11-14 10:03:32', '2026-11-14 10:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `max_days` int(11) NOT NULL DEFAULT 10,
  `description` varchar(255) DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `max_days`, `description`, `is_active`, `created_at`) VALUES
(1, 'ลาป่วย', 30, 'ลาเนื่องจากอาการเจ็บป่วยหรือพบแพทย์', 1, '2026-11-14 10:03:32'),
(2, 'ลากิจ', 6, 'ลาเพื่อทำธุระส่วนตัวที่จำเป็น', 1, '2026-11-14 10:03:32'),
(3, 'ลาพักร้อน', 10, 'ลาพักผ่อนประจำปี (ยื่นล่วงหน้าอย่างน้อย 3 วัน)', 1, '2026-11-14 10:03:32'),
(4, 'ลาคลอด / ลาบวช', 90, 'ลาตามสิทธิ์กฎหมายแรงงาน', 1, '2026-11-14 10:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL DEFAULT 'ทั่วไป',
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `department`, `role`, `created_at`) VALUES
(1, 'SNOW', '1234', 'SNOW', 'ฝ่ายบุคคล (HR)', 'admin', '2026-11-14 10:03:32'),
(2, 'SALMON', '1234', 'SALMON', 'พัฒนาระบบ (IT)', 'user', '2026-11-14 10:03:32'),
(3, 'suda', '1234', 'สุดา รักงาน', 'การตลาด (Marketing)', 'user', '2026-11-14 10:03:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_leave_user` (`user_id`),
  ADD KEY `fk_leave_type` (`leave_type_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
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
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `fk_leave_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`),
  ADD CONSTRAINT `fk_leave_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
