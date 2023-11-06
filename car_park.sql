-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 06, 2023 at 11:11 PM
-- Server version: 8.0.31
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `car_park`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `car_plate` varchar(10) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `booking_status` enum('booked','cancelled') DEFAULT 'booked',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_date_from` (`date_from`),
  KEY `idx_date_to` (`date_to`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `date_from`, `date_to`, `car_plate`, `price`, `booking_status`, `created_at`, `updated_at`) VALUES
(1, '2023-11-02', '2023-11-09', 'ABC123', '90.00', 'cancelled', '2023-11-03 21:05:29', '2023-11-05 23:04:18'),
(2, '2023-11-02', '2023-11-09', 'DEF456', '90.00', 'cancelled', '2023-11-03 21:05:29', '2023-11-06 21:38:51'),
(3, '2023-06-02', '2023-06-09', 'GHI789', '90.00', 'cancelled', '2023-11-03 21:05:29', '2023-11-06 22:34:38'),
(4, '2023-11-01', '2023-11-10', 'ABC123', '10.00', 'cancelled', '2023-11-03 23:12:42', '2023-11-06 21:39:50'),
(5, '2023-11-01', '2023-11-10', 'ABC123', '10.00', 'cancelled', '2023-11-03 23:12:42', '2023-11-03 23:12:42'),
(6, '2023-11-02', '2023-11-09', 'ABC123', '10.00', 'cancelled', '2023-11-03 23:12:42', '2023-11-06 21:39:50'),
(7, '2023-11-01', '2023-11-10', 'ABC123', '10.00', 'booked', '2023-11-03 23:14:54', '2023-11-03 23:14:54'),
(8, '2023-11-01', '2023-11-10', 'ABC123', '10.00', 'cancelled', '2023-11-03 23:14:54', '2023-11-03 23:14:54'),
(9, '2023-11-02', '2023-11-09', 'ABC123', '10.00', 'booked', '2023-11-03 23:14:54', '2023-11-03 23:14:54'),
(10, '2023-11-02', '2023-11-09', 'ABC123', NULL, 'booked', '2023-11-03 23:15:17', '2023-11-06 22:33:45'),
(34, '2024-12-01', '2024-12-02', 'ABC123', '45.00', 'booked', '2023-11-06 22:38:50', '2023-11-06 22:38:50'),
(32, '2023-12-01', '2023-12-02', 'ABC123', '45.00', 'booked', '2023-11-06 21:54:25', '2023-11-06 21:54:25'),
(31, '2023-11-01', '2023-11-10', 'ABC123', '210.00', 'booked', '2023-11-06 21:45:05', '2023-11-06 21:45:05'),
(30, '2023-11-01', '2023-11-10', 'ABC123', '210.00', 'booked', '2023-11-06 21:44:40', '2023-11-06 21:44:40'),
(33, '2024-12-01', '2024-12-02', 'ABC123', '45.00', 'booked', '2023-11-06 22:38:45', '2023-11-06 22:38:45'),
(28, '2023-11-01', '2023-11-10', 'ABC123', '110.00', 'booked', '2023-11-06 00:18:23', '2023-11-06 00:18:23'),
(27, '2023-11-01', '2023-11-10', 'ABC123', '110.00', 'booked', '2023-11-05 23:53:02', '2023-11-05 23:53:02'),
(26, '2023-11-01', '2023-11-10', 'ABC123', '110.00', 'booked', '2023-11-05 23:53:00', '2023-11-05 23:53:00'),
(25, '2023-11-01', '2023-11-10', 'ABC123', '110.00', 'booked', '2023-11-05 23:52:58', '2023-11-05 23:52:58'),
(24, '2023-11-01', '2023-11-10', 'ABC123', '110.00', 'cancelled', '2023-11-05 23:52:31', '2023-11-05 23:53:44');

-- --------------------------------------------------------

--
-- Table structure for table `pricing`
--

DROP TABLE IF EXISTS `pricing`;
CREATE TABLE IF NOT EXISTS `pricing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season` enum('summer','winter') NOT NULL,
  `day_type` enum('weekday','weekend') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `season_day_type_unique` (`season`,`day_type`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pricing`
--

INSERT INTO `pricing` (`id`, `season`, `day_type`, `price`) VALUES
(1, 'summer', 'weekday', '10.00'),
(2, 'summer', 'weekend', '15.00'),
(3, 'winter', 'weekday', '20.00'),
(4, 'winter', 'weekend', '25.00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
