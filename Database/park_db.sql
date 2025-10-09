-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2025 at 09:09 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `park_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `duration` int(11) NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `estimated_arrival_time` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `slot_id`, `location_id`, `start_time`, `duration`, `end_time`, `status`, `estimated_arrival_time`, `created_at`) VALUES
(1, 3, 7, 2, '2025-10-10 11:58:00', 3, '2025-10-09 04:58:00', 'completed', '2025-10-09 11:58:00', '2025-10-09 11:58:32'),
(2, 3, 12, 3, '2025-10-09 12:03:00', 3, '2025-10-09 15:06:00', 'active', '2025-10-09 13:04:00', '2025-10-09 12:04:07');

-- --------------------------------------------------------

--
-- Table structure for table `parking_locations`
--

CREATE TABLE `parking_locations` (
  `location_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `latitude` decimal(9,6) NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `total_slots` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_locations`
--

INSERT INTO `parking_locations` (`location_id`, `name`, `address`, `city`, `latitude`, `longitude`, `total_slots`, `created_at`, `updated_at`) VALUES
(1, 'Location one', 'XYZ', 'Lahore', 24.838700, 67.120900, 36, '2025-10-09 11:46:00', '2025-10-09 11:46:36'),
(2, 'City Center Plaza', 'Main Road, Block A', 'Karachi', 24.860700, 67.001100, 5, '2025-10-09 11:50:13', '2025-10-09 11:50:13'),
(3, 'Mall of Lahore', 'Cavalry Ground, Lahore Cantt', 'Lahore', 31.520400, 74.358700, 5, '2025-10-09 11:50:13', '2025-10-09 11:50:13'),
(4, 'Islamabad Giga Mall', 'DHA Phase II, Islamabad', 'Islamabad', 33.565100, 72.889600, 5, '2025-10-09 11:50:13', '2025-10-09 11:50:13'),
(5, 'Ocean Mall', 'Clifton Block 9', 'Karachi', 24.812300, 67.028200, 5, '2025-10-09 11:50:13', '2025-10-09 11:50:13'),
(6, 'Centaurus Mall', 'Blue Area, F-8', 'Islamabad', 33.707100, 73.055500, 5, '2025-10-09 11:50:13', '2025-10-09 11:50:13');

-- --------------------------------------------------------

--
-- Table structure for table `parking_slots`
--

CREATE TABLE `parking_slots` (
  `slot_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `slot_number` varchar(10) NOT NULL,
  `status` enum('available','occupied','reserved') NOT NULL DEFAULT 'available',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_slots`
--

INSERT INTO `parking_slots` (`slot_id`, `location_id`, `slot_number`, `status`, `updated_at`) VALUES
(1, 1, 'Slot 1', 'available', '2025-10-09 11:49:30'),
(2, 1, 'A1', 'available', '2025-10-09 11:50:13'),
(3, 1, 'A2', 'available', '2025-10-09 11:50:13'),
(4, 1, 'A3', 'occupied', '2025-10-09 11:50:13'),
(5, 1, 'A4', 'reserved', '2025-10-09 11:50:13'),
(6, 1, 'A5', 'available', '2025-10-09 11:50:13'),
(7, 2, 'B1', 'occupied', '2025-10-09 11:58:32'),
(8, 2, 'B2', 'occupied', '2025-10-09 11:50:13'),
(9, 2, 'B3', 'available', '2025-10-09 11:50:13'),
(10, 2, 'B4', 'reserved', '2025-10-09 11:50:13'),
(11, 2, 'B5', 'available', '2025-10-09 11:50:13'),
(12, 3, 'C1', 'occupied', '2025-10-09 12:04:07'),
(13, 3, 'C2', 'occupied', '2025-10-09 11:50:13'),
(14, 3, 'C3', 'available', '2025-10-09 11:50:13'),
(15, 3, 'C4', 'available', '2025-10-09 11:50:13'),
(16, 3, 'C5', 'reserved', '2025-10-09 11:50:13'),
(17, 4, 'D1', 'available', '2025-10-09 11:50:13'),
(18, 4, 'D2', 'available', '2025-10-09 11:50:13'),
(19, 4, 'D3', 'occupied', '2025-10-09 11:50:13'),
(20, 4, 'D4', 'reserved', '2025-10-09 11:50:13'),
(21, 4, 'D5', 'available', '2025-10-09 11:50:13'),
(22, 5, 'E1', 'available', '2025-10-09 11:50:13'),
(23, 5, 'E2', 'occupied', '2025-10-09 11:50:13'),
(24, 5, 'E3', 'available', '2025-10-09 11:50:13'),
(25, 5, 'E4', 'reserved', '2025-10-09 11:50:13'),
(26, 5, 'E5', 'available', '2025-10-09 11:50:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `age`, `full_name`, `bio`) VALUES
(3, 'nasir', '$2y$10$a/SgsGBswoo1YfNrJd./p.Jqey.MDYC.QoLsyJGas3H5cdesUPvjq', 'nasiryt.827@gmail.com', '9853', 23, 'd', 'fdsa'),
(4, 'haider12', '$2y$10$j1mvI4F4.u1iM3JzRCBSL.ODKDxnU/OTW532InO137kXIW.8lrfgi', 'haider@gmail.com', '03120558064', 23, 'Haider Ali', 'XYZ');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `slot_id` (`slot_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `parking_locations`
--
ALTER TABLE `parking_locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `parking_slots`
--
ALTER TABLE `parking_slots`
  ADD PRIMARY KEY (`slot_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `parking_locations`
--
ALTER TABLE `parking_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `parking_slots`
--
ALTER TABLE `parking_slots`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`slot_id`) REFERENCES `parking_slots` (`slot_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `parking_locations` (`location_id`) ON DELETE CASCADE;

--
-- Constraints for table `parking_slots`
--
ALTER TABLE `parking_slots`
  ADD CONSTRAINT `parking_slots_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `parking_locations` (`location_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
