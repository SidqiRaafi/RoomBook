-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2026 at 04:02 PM
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
-- Database: `roombook`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', 'admin123', '2026-01-18 15:12:02');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `nim` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `purpose` text DEFAULT NULL,
  `permit_number` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `status` enum('active','expired','deleted') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `room_id`, `nim`, `nama`, `booking_date`, `start_time`, `end_time`, `purpose`, `permit_number`, `created_by`, `created_at`, `deleted_at`, `deleted_by`, `status`) VALUES
(20, 11, '23552011395', 'Sidqi Raafi Al Fauzan', '2026-01-20', '08:00:00', '12:00:00', 'buat gabut aja hehe, sekalian ngecas', 'BK-20260120-001', 1, '2026-01-19 22:53:30', '2026-01-20 05:54:27', 1, 'deleted'),
(21, 11, '2323232', 'test booking hari berikut', '2026-01-20', '08:00:00', '12:00:00', 'testing terus gesya', 'BK-20260120-002', 1, '2026-01-19 22:57:29', '2026-01-20 06:01:19', 1, 'deleted'),
(22, 11, '12312321', 'test lagi', '2026-01-20', '08:00:00', '12:00:00', 'gabisa booking besoknya kalo ada booking di jam dan room sama hari sebelummya', 'BK-20260120-003', 1, '2026-01-19 23:01:52', NULL, NULL, 'active'),
(23, 11, '1231312', 'wei bisa dong', '2026-01-21', '08:00:00', '12:00:00', 'akhirnya bisa dong, udah jam 6 pagi ini', 'BK-20260121-001', 1, '2026-01-19 23:02:49', NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `created_at`) VALUES
(11, 'R. A-101', '2026-01-19 22:49:56'),
(12, 'R. A-102', '2026-01-19 22:49:56'),
(13, 'R. A-103', '2026-01-19 22:49:56'),
(14, 'R. A-201', '2026-01-19 22:49:56'),
(15, 'R. A-202', '2026-01-19 22:49:56'),
(16, 'R. A-203', '2026-01-19 22:49:56'),
(17, 'R. A-204', '2026-01-19 22:49:56'),
(20, 'R. A-301', '2026-01-19 22:49:56'),
(21, 'R. A-302', '2026-01-19 22:49:56'),
(22, 'R. A-303', '2026-01-19 22:49:56'),
(23, 'R. A-304', '2026-01-19 22:49:56'),
(25, 'R. A-401', '2026-01-19 22:49:56'),
(26, 'R. A-402', '2026-01-19 22:49:56'),
(27, 'R. A-403', '2026-01-19 22:49:56'),
(28, 'R. A-404', '2026-01-19 22:49:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permit_number` (`permit_number`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_name` (`room_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
