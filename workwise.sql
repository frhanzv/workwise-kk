-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 12, 2026 at 06:17 AM
-- Server version: 8.0.42
-- PHP Version: 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `workwise`
--

-- --------------------------------------------------------

--
-- Table structure for table `antenna_modes`
--

CREATE TABLE `antenna_modes` (
  `id` int UNSIGNED NOT NULL,
  `mode_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `antenna_modes`
--

INSERT INTO `antenna_modes` (`id`, `mode_name`, `color`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'RFID-UHF', 'purple', 'Ultra High Frequency RFID', 1, NULL, '2025-12-15 07:47:48'),
(2, 'NFC', 'blue', 'Near Field Communication', 1, NULL, '2025-12-15 07:48:16'),
(3, 'Bluetooth', 'amber', 'Bluetooth Low Energy', 1, NULL, '2025-12-15 07:48:40'),
(4, 'Biometric', 'teal', 'Fingerprint or Facial Recognition', 1, NULL, '2025-12-15 07:49:01'),
(5, 'GPS', 'green', 'Global Positioning System', 1, NULL, '2025-12-15 07:48:55');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int UNSIGNED NOT NULL,
  `worker_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `zone_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `worker_id`, `zone_id`, `check_in_time`, `check_out_time`, `date`, `created_at`, `updated_at`) VALUES
(1, 'ID-91234', 'Z-1002', '2025-12-16 12:55:00', '2025-12-16 18:03:00', '2025-12-16', '2025-12-16 12:55:59', '2025-12-16 18:04:02'),
(3, 'ID-56789', 'Z-1001', '2025-12-17 11:39:00', '2025-12-17 13:29:00', '2025-12-17', '2025-12-17 11:39:55', '2025-12-17 13:30:01'),
(5, 'ID-56789', 'Z-1002', '2025-12-17 13:30:00', '2025-12-17 14:34:00', '2025-12-17', '2025-12-17 13:30:19', '2025-12-17 14:35:03'),
(7, 'ID-56789', 'Z-1001', '2025-12-23 10:14:00', '2025-12-23 17:56:00', '2025-12-23', '2025-12-23 10:14:56', '2025-12-23 17:56:45'),
(14, 'ID-91234', 'Z-1001', '2025-12-23 16:39:04', '2025-12-23 16:41:30', '2025-12-23', '2025-12-23 16:39:04', '2025-12-23 16:41:30'),
(15, 'ID-91234', 'Z-1001', '2025-12-23 17:06:26', '2025-12-23 17:22:49', '2025-12-23', '2025-12-23 17:06:26', '2025-12-23 17:22:49'),
(16, 'ID-91234', 'Z-1007', '2025-12-23 17:24:34', '2025-12-23 17:47:31', '2025-12-23', '2025-12-23 17:24:34', '2025-12-23 17:47:31'),
(17, 'ID-91234', 'Z-1003', '2025-12-23 17:50:07', '2025-12-23 17:56:14', '2025-12-23', '2025-12-23 17:50:07', '2025-12-23 17:56:14'),
(18, 'ID-91234', 'Z-1003', '2025-12-29 09:25:09', '2025-12-29 18:26:17', '2025-12-29', '2025-12-29 09:25:09', '2025-12-29 18:26:17'),
(19, 'ID-14297', 'Z-1003', '2025-12-30 14:38:02', '2025-12-30 16:25:07', '2025-12-30', '2025-12-30 14:38:02', '2025-12-30 16:25:07'),
(21, 'ID-14297', 'Z-1003', '2025-12-30 16:25:43', '2025-12-30 16:25:46', '2025-12-30', '2025-12-30 16:25:43', '2025-12-30 16:25:46'),
(22, 'ID-14297', 'Z-1003', '2025-12-30 16:25:47', '2025-12-30 16:36:28', '2025-12-30', '2025-12-30 16:25:47', '2025-12-30 16:36:28'),
(23, 'ID-14305', 'Z-1003', '2025-12-30 16:25:48', '2025-12-30 16:35:37', '2025-12-30', '2025-12-30 16:25:48', '2025-12-30 16:35:37'),
(24, 'ID-14297', 'Z-1001', '2025-12-30 16:39:14', '2025-12-30 18:10:47', '2025-12-30', '2025-12-30 16:39:14', '2025-12-30 18:10:47'),
(25, 'ID-14306', 'Z-1001', '2025-12-30 16:39:14', '2025-12-30 18:10:51', '2025-12-30', '2025-12-30 16:39:14', '2025-12-30 18:10:51'),
(26, 'ID-14304', 'Z-1001', '2025-12-30 16:39:15', '2025-12-30 18:10:49', '2025-12-30', '2025-12-30 16:39:15', '2025-12-30 18:10:49'),
(27, 'ID-14299', 'Z-1001', '2025-12-30 16:39:15', '2025-12-30 18:10:50', '2025-12-30', '2025-12-30 16:39:15', '2025-12-30 18:10:50'),
(28, 'ID-14301', 'Z-1001', '2025-12-30 16:39:15', '2025-12-30 18:10:47', '2025-12-30', '2025-12-30 16:39:15', '2025-12-30 18:10:47'),
(29, 'ID-14305', 'Z-1001', '2025-12-30 16:39:16', '2025-12-30 18:10:48', '2025-12-30', '2025-12-30 16:39:16', '2025-12-30 18:10:48'),
(30, 'ID-14302', 'Z-1001', '2025-12-30 16:39:17', '2025-12-30 18:10:49', '2025-12-30', '2025-12-30 16:39:17', '2025-12-30 18:10:49'),
(31, 'ID-14303', 'Z-1001', '2025-12-30 16:39:19', '2025-12-30 18:10:48', '2025-12-30', '2025-12-30 16:39:19', '2025-12-30 18:10:48'),
(41, 'ID-14301', 'Z-1001', '2025-12-31 12:11:20', '2025-12-31 15:46:58', '2025-12-31', '2025-12-31 12:11:20', '2025-12-31 15:46:58'),
(42, 'ID-14303', 'Z-1001', '2025-12-31 12:12:14', '2025-12-31 18:03:20', '2025-12-31', '2025-12-31 12:12:14', '2025-12-31 18:03:20'),
(43, 'ID-14305', 'Z-1001', '2025-12-31 12:13:31', '2025-12-31 12:15:43', '2025-12-31', '2025-12-31 12:13:31', '2025-12-31 12:15:43'),
(44, 'ID-14304', 'Z-1001', '2025-12-31 12:14:22', '2025-12-31 14:27:17', '2025-12-31', '2025-12-31 12:14:22', '2025-12-31 14:27:17'),
(45, 'ID-14304', 'Z-1001', '2025-12-31 15:46:58', '2025-12-31 18:09:31', '2025-12-31', '2025-12-31 15:46:58', '2025-12-31 18:09:31'),
(46, 'ID-14301', 'Z-1001', '2025-12-31 16:03:53', '2025-12-31 18:07:42', '2025-12-31', '2025-12-31 16:03:53', '2025-12-31 18:07:42'),
(48, 'ID-14302', 'Z-1001', '2025-12-31 16:43:28', '2025-12-31 18:03:21', '2025-12-31', '2025-12-31 16:43:28', '2025-12-31 18:03:21'),
(49, 'ID-14297', 'Z-1001', '2025-12-31 16:49:01', '2025-12-31 18:03:20', '2025-12-31', '2025-12-31 16:49:01', '2025-12-31 18:03:20'),
(50, 'ID-14299', 'Z-1001', '2025-12-31 16:55:49', '2025-12-31 18:03:21', '2025-12-31', '2025-12-31 16:55:49', '2025-12-31 18:03:21'),
(51, 'ID-14306', 'Z-1001', '2025-12-31 16:55:49', '2025-12-31 18:02:15', '2025-12-31', '2025-12-31 16:55:49', '2025-12-31 18:02:15'),
(52, 'ID-14305', 'Z-1001', '2025-12-31 17:28:53', '2025-12-31 18:10:01', '2025-12-31', '2025-12-31 17:28:53', '2025-12-31 18:10:01'),
(53, 'ID-14303', 'Z-1001', '2026-01-02 08:02:52', '2026-01-02 12:08:32', '2026-01-02', '2026-01-02 08:02:52', '2026-01-02 12:08:32'),
(54, 'ID-14301', 'Z-1001', '2026-01-02 07:50:35', '2026-01-02 18:04:18', '2026-01-02', '2026-01-02 07:50:35', '2026-01-02 18:04:18'),
(55, 'ID-14304', 'Z-1001', '2026-01-02 15:59:29', '2026-01-02 18:04:19', '2026-01-02', '2026-01-02 15:59:29', '2026-01-02 18:04:19'),
(56, 'ID-14299', 'Z-1001', '2026-01-02 15:59:29', '2026-01-02 18:04:18', '2026-01-02', '2026-01-02 15:59:29', '2026-01-02 18:04:18'),
(57, 'ID-14302', 'Z-1001', '2026-01-02 15:59:29', '2026-01-02 18:04:19', '2026-01-02', '2026-01-02 15:59:29', '2026-01-02 18:04:19'),
(58, 'ID-14297', 'Z-1001', '2026-01-02 15:59:30', '2026-01-02 18:05:07', '2026-01-02', '2026-01-02 15:59:30', '2026-01-02 18:05:07'),
(59, 'ID-14306', 'Z-1001', '2026-01-02 15:59:30', '2026-01-02 18:04:18', '2026-01-02', '2026-01-02 15:59:30', '2026-01-02 18:04:18'),
(60, 'ID-14305', 'Z-1001', '2026-01-02 15:59:31', '2026-01-02 18:05:06', '2026-01-02', '2026-01-02 15:59:31', '2026-01-02 18:05:06'),
(61, 'ID-14302', 'Z-1001', '2026-01-05 09:37:36', '2026-01-05 18:08:36', '2026-01-05', '2026-01-05 09:37:36', '2026-01-05 18:08:36'),
(62, 'ID-14301', 'Z-1001', '2026-01-05 09:37:36', '2026-01-05 18:08:37', '2026-01-05', '2026-01-05 09:37:36', '2026-01-05 18:08:37'),
(63, 'ID-14303', 'Z-1001', '2026-01-05 09:38:26', '2026-01-05 18:08:36', '2026-01-05', '2026-01-05 09:38:26', '2026-01-05 18:08:36'),
(64, 'ID-14304', 'Z-1001', '2026-01-05 09:38:28', '2026-01-05 18:09:03', '2026-01-05', '2026-01-05 09:38:28', '2026-01-05 18:09:03'),
(67, 'ID-14306', 'Z-1001', '2026-01-05 10:43:11', '2026-01-05 18:08:37', '2026-01-05', '2026-01-05 10:43:11', '2026-01-05 18:08:37'),
(68, 'ID-14305', 'Z-1001', '2026-01-05 10:43:53', '2026-01-05 18:08:36', '2026-01-05', '2026-01-05 10:43:53', '2026-01-05 18:08:36'),
(69, 'ID-14298', 'Z-1001', '2026-01-05 18:10:16', NULL, '2026-01-05', '2026-01-05 18:10:16', '2026-01-05 18:10:16'),
(85, 'ID-14299', 'Z-1001', '2026-01-09 18:32:17', NULL, '2026-01-09', '2026-01-09 18:32:17', '2026-01-09 18:32:17'),
(86, 'ID-14298', 'Z-1001', '2026-01-09 18:33:08', NULL, '2026-01-09', '2026-01-09 18:33:08', '2026-01-09 18:33:08'),
(87, 'ID-14297', 'Z-1001', '2026-01-09 18:33:08', NULL, '2026-01-09', '2026-01-09 18:33:09', '2026-01-09 18:33:09');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `state_id` int UNSIGNED NOT NULL,
  `country_id` int UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `state_id`, `country_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Johor Bahru', 1, 99, 1, '2025-12-29 10:02:01', NULL),
(2, 'Batu Pahat', 1, 99, 1, '2025-12-29 10:02:01', NULL),
(3, 'Muar', 1, 99, 1, '2025-12-29 10:02:01', NULL),
(4, 'Kluang', 1, 99, 1, '2025-12-29 10:02:01', NULL),
(5, 'Pontian', 1, 99, 1, '2025-12-29 10:02:01', NULL),
(6, 'Segamat', 1, 99, 1, '2025-12-29 10:02:01', NULL),
(7, 'Kota Tinggi', 1, 99, 1, '2025-12-29 10:02:01', NULL),
(8, 'Mersing', 1, 99, 1, '2025-12-29 10:02:01', NULL),
(9, 'Alor Setar', 2, 99, 1, '2025-12-29 10:02:01', NULL),
(10, 'Sungai Petani', 2, 99, 1, '2025-12-29 10:02:01', NULL),
(11, 'Kulim', 2, 99, 1, '2025-12-29 10:02:01', NULL),
(12, 'Langkawi', 2, 99, 1, '2025-12-29 10:02:01', NULL),
(13, 'Baling', 2, 99, 1, '2025-12-29 10:02:01', NULL),
(14, 'Kuala Kedah', 2, 99, 1, '2025-12-29 10:02:01', NULL),
(15, 'Kota Bharu', 3, 99, 1, '2025-12-29 10:02:01', NULL),
(16, 'Pasir Mas', 3, 99, 1, '2025-12-29 10:02:01', NULL),
(17, 'Tanah Merah', 3, 99, 1, '2025-12-29 10:02:01', NULL),
(18, 'Tumpat', 3, 99, 1, '2025-12-29 10:02:01', NULL),
(19, 'Gua Musang', 3, 99, 1, '2025-12-29 10:02:01', NULL),
(20, 'Kuala Lumpur', 4, 99, 1, '2025-12-29 10:02:01', NULL),
(21, 'Victoria', 5, 99, 1, '2025-12-29 10:02:01', NULL),
(22, 'Melaka City', 6, 99, 1, '2025-12-29 10:02:01', NULL),
(23, 'Alor Gajah', 6, 99, 1, '2025-12-29 10:02:01', NULL),
(24, 'Jasin', 6, 99, 1, '2025-12-29 10:02:01', NULL),
(25, 'Seremban', 7, 99, 1, '2025-12-29 10:02:01', NULL),
(26, 'Port Dickson', 7, 99, 1, '2025-12-29 10:02:01', NULL),
(27, 'Nilai', 7, 99, 1, '2025-12-29 10:02:01', NULL),
(28, 'Kuala Pilah', 7, 99, 1, '2025-12-29 10:02:01', NULL),
(29, 'Tampin', 7, 99, 1, '2025-12-29 10:02:01', NULL),
(30, 'Kuantan', 8, 99, 1, '2025-12-29 10:02:01', NULL),
(31, 'Temerloh', 8, 99, 1, '2025-12-29 10:02:01', NULL),
(32, 'Bentong', 8, 99, 1, '2025-12-29 10:02:01', NULL),
(33, 'Raub', 8, 99, 1, '2025-12-29 10:02:01', NULL),
(34, 'Jerantut', 8, 99, 1, '2025-12-29 10:02:01', NULL),
(35, 'Pekan', 8, 99, 1, '2025-12-29 10:02:01', NULL),
(36, 'Kuala Lipis', 8, 99, 1, '2025-12-29 10:02:01', NULL),
(37, 'George Town', 9, 99, 1, '2025-12-29 10:02:01', NULL),
(38, 'Butterworth', 9, 99, 1, '2025-12-29 10:02:01', NULL),
(39, 'Bukit Mertajam', 9, 99, 1, '2025-12-29 10:02:01', NULL),
(40, 'Balik Pulau', 9, 99, 1, '2025-12-29 10:02:01', NULL),
(41, 'Ipoh', 10, 99, 1, '2025-12-29 10:02:01', NULL),
(42, 'Taiping', 10, 99, 1, '2025-12-29 10:02:01', NULL),
(43, 'Teluk Intan', 10, 99, 1, '2025-12-29 10:02:01', NULL),
(44, 'Kuala Kangsar', 10, 99, 1, '2025-12-29 10:02:01', NULL),
(45, 'Sitiawan', 10, 99, 1, '2025-12-29 10:02:01', NULL),
(46, 'Lumut', 10, 99, 1, '2025-12-29 10:02:01', NULL),
(47, 'Parit Buntar', 10, 99, 1, '2025-12-29 10:02:01', NULL),
(48, 'Batu Gajah', 10, 99, 1, '2025-12-29 10:02:01', NULL),
(49, 'Kangar', 11, 99, 1, '2025-12-29 10:02:01', NULL),
(50, 'Arau', 11, 99, 1, '2025-12-29 10:02:01', NULL),
(51, 'Putrajaya', 12, 99, 1, '2025-12-29 10:02:01', NULL),
(52, 'Kota Kinabalu', 13, 99, 1, '2025-12-29 10:02:01', NULL),
(53, 'Sandakan', 13, 99, 1, '2025-12-29 10:02:01', NULL),
(54, 'Tawau', 13, 99, 1, '2025-12-29 10:02:01', NULL),
(55, 'Lahad Datu', 13, 99, 1, '2025-12-29 10:02:01', NULL),
(56, 'Keningau', 13, 99, 1, '2025-12-29 10:02:01', NULL),
(57, 'Semporna', 13, 99, 1, '2025-12-29 10:02:01', NULL),
(58, 'Kuching', 14, 99, 1, '2025-12-29 10:02:01', NULL),
(59, 'Miri', 14, 99, 1, '2025-12-29 10:02:01', NULL),
(60, 'Sibu', 14, 99, 1, '2025-12-29 10:02:01', NULL),
(61, 'Bintulu', 14, 99, 1, '2025-12-29 10:02:01', NULL),
(62, 'Limbang', 14, 99, 1, '2025-12-29 10:02:01', NULL),
(63, 'Sarikei', 14, 99, 1, '2025-12-29 10:02:01', NULL),
(64, 'Shah Alam', 15, 99, 1, '2025-12-29 10:02:01', NULL),
(65, 'Petaling Jaya', 15, 99, 1, '2025-12-29 10:02:01', NULL),
(66, 'Subang Jaya', 15, 99, 1, '2025-12-29 10:02:01', NULL),
(67, 'Klang', 15, 99, 1, '2025-12-29 10:02:01', NULL),
(68, 'Ampang', 15, 99, 1, '2025-12-29 10:02:01', NULL),
(69, 'Sepang', 15, 99, 1, '2025-12-29 10:02:01', NULL),
(70, 'Kajang', 15, 99, 1, '2025-12-29 10:02:01', NULL),
(71, 'Selayang', 15, 99, 1, '2025-12-29 10:02:01', NULL),
(72, 'Rawang', 15, 99, 1, '2025-12-29 10:02:01', NULL),
(73, 'Kuala Selangor', 15, 99, 1, '2025-12-29 10:02:01', NULL),
(74, 'Kuala Terengganu', 16, 99, 1, '2025-12-29 10:02:01', NULL),
(75, 'Kemaman', 16, 99, 1, '2025-12-29 10:02:01', NULL),
(76, 'Dungun', 16, 99, 1, '2025-12-29 10:02:01', NULL),
(77, 'Marang', 16, 99, 1, '2025-12-29 10:02:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(3) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Afghanistan', 'AF', 1, '2025-12-29 10:02:01', NULL),
(2, 'Albania', 'AL', 1, '2025-12-29 10:02:01', NULL),
(3, 'Algeria', 'DZ', 1, '2025-12-29 10:02:01', NULL),
(4, 'Andorra', 'AD', 1, '2025-12-29 10:02:01', NULL),
(5, 'Angola', 'AO', 1, '2025-12-29 10:02:01', NULL),
(6, 'Argentina', 'AR', 1, '2025-12-29 10:02:01', NULL),
(7, 'Armenia', 'AM', 1, '2025-12-29 10:02:01', NULL),
(8, 'Australia', 'AU', 1, '2025-12-29 10:02:01', NULL),
(9, 'Austria', 'AT', 1, '2025-12-29 10:02:01', NULL),
(10, 'Azerbaijan', 'AZ', 1, '2025-12-29 10:02:01', NULL),
(11, 'Bahamas', 'BS', 1, '2025-12-29 10:02:01', NULL),
(12, 'Bahrain', 'BH', 1, '2025-12-29 10:02:01', NULL),
(13, 'Bangladesh', 'BD', 1, '2025-12-29 10:02:01', NULL),
(14, 'Barbados', 'BB', 1, '2025-12-29 10:02:01', NULL),
(15, 'Belarus', 'BY', 1, '2025-12-29 10:02:01', NULL),
(16, 'Belgium', 'BE', 1, '2025-12-29 10:02:01', NULL),
(17, 'Belize', 'BZ', 1, '2025-12-29 10:02:01', NULL),
(18, 'Benin', 'BJ', 1, '2025-12-29 10:02:01', NULL),
(19, 'Bhutan', 'BT', 1, '2025-12-29 10:02:01', NULL),
(20, 'Bolivia', 'BO', 1, '2025-12-29 10:02:01', NULL),
(21, 'Bosnia and Herzegovina', 'BA', 1, '2025-12-29 10:02:01', NULL),
(22, 'Botswana', 'BW', 1, '2025-12-29 10:02:01', NULL),
(23, 'Brazil', 'BR', 1, '2025-12-29 10:02:01', NULL),
(24, 'Brunei', 'BN', 1, '2025-12-29 10:02:01', NULL),
(25, 'Bulgaria', 'BG', 1, '2025-12-29 10:02:01', NULL),
(26, 'Burkina Faso', 'BF', 1, '2025-12-29 10:02:01', NULL),
(27, 'Burundi', 'BI', 1, '2025-12-29 10:02:01', NULL),
(28, 'Cambodia', 'KH', 1, '2025-12-29 10:02:01', NULL),
(29, 'Cameroon', 'CM', 1, '2025-12-29 10:02:01', NULL),
(30, 'Canada', 'CA', 1, '2025-12-29 10:02:01', NULL),
(31, 'Cape Verde', 'CV', 1, '2025-12-29 10:02:01', NULL),
(32, 'Central African Republic', 'CF', 1, '2025-12-29 10:02:01', NULL),
(33, 'Chad', 'TD', 1, '2025-12-29 10:02:01', NULL),
(34, 'Chile', 'CL', 1, '2025-12-29 10:02:01', NULL),
(35, 'China', 'CN', 1, '2025-12-29 10:02:01', NULL),
(36, 'Colombia', 'CO', 1, '2025-12-29 10:02:01', NULL),
(37, 'Comoros', 'KM', 1, '2025-12-29 10:02:01', NULL),
(38, 'Congo', 'CG', 1, '2025-12-29 10:02:01', NULL),
(39, 'Costa Rica', 'CR', 1, '2025-12-29 10:02:01', NULL),
(40, 'Croatia', 'HR', 1, '2025-12-29 10:02:01', NULL),
(41, 'Cuba', 'CU', 1, '2025-12-29 10:02:01', NULL),
(42, 'Cyprus', 'CY', 1, '2025-12-29 10:02:01', NULL),
(43, 'Czech Republic', 'CZ', 1, '2025-12-29 10:02:01', NULL),
(44, 'Denmark', 'DK', 1, '2025-12-29 10:02:01', NULL),
(45, 'Djibouti', 'DJ', 1, '2025-12-29 10:02:01', NULL),
(46, 'Dominica', 'DM', 1, '2025-12-29 10:02:01', NULL),
(47, 'Dominican Republic', 'DO', 1, '2025-12-29 10:02:01', NULL),
(48, 'Ecuador', 'EC', 1, '2025-12-29 10:02:01', NULL),
(49, 'Egypt', 'EG', 1, '2025-12-29 10:02:01', NULL),
(50, 'El Salvador', 'SV', 1, '2025-12-29 10:02:01', NULL),
(51, 'Equatorial Guinea', 'GQ', 1, '2025-12-29 10:02:01', NULL),
(52, 'Eritrea', 'ER', 1, '2025-12-29 10:02:01', NULL),
(53, 'Estonia', 'EE', 1, '2025-12-29 10:02:01', NULL),
(54, 'Ethiopia', 'ET', 1, '2025-12-29 10:02:01', NULL),
(55, 'Fiji', 'FJ', 1, '2025-12-29 10:02:01', NULL),
(56, 'Finland', 'FI', 1, '2025-12-29 10:02:01', NULL),
(57, 'France', 'FR', 1, '2025-12-29 10:02:01', NULL),
(58, 'Gabon', 'GA', 1, '2025-12-29 10:02:01', NULL),
(59, 'Gambia', 'GM', 1, '2025-12-29 10:02:01', NULL),
(60, 'Georgia', 'GE', 1, '2025-12-29 10:02:01', NULL),
(61, 'Germany', 'DE', 1, '2025-12-29 10:02:01', NULL),
(62, 'Ghana', 'GH', 1, '2025-12-29 10:02:01', NULL),
(63, 'Greece', 'GR', 1, '2025-12-29 10:02:01', NULL),
(64, 'Grenada', 'GD', 1, '2025-12-29 10:02:01', NULL),
(65, 'Guatemala', 'GT', 1, '2025-12-29 10:02:01', NULL),
(66, 'Guinea', 'GN', 1, '2025-12-29 10:02:01', NULL),
(67, 'Guinea-Bissau', 'GW', 1, '2025-12-29 10:02:01', NULL),
(68, 'Guyana', 'GY', 1, '2025-12-29 10:02:01', NULL),
(69, 'Haiti', 'HT', 1, '2025-12-29 10:02:01', NULL),
(70, 'Honduras', 'HN', 1, '2025-12-29 10:02:01', NULL),
(71, 'Hungary', 'HU', 1, '2025-12-29 10:02:01', NULL),
(72, 'Iceland', 'IS', 1, '2025-12-29 10:02:01', NULL),
(73, 'India', 'IN', 1, '2025-12-29 10:02:01', NULL),
(74, 'Indonesia', 'ID', 1, '2025-12-29 10:02:01', NULL),
(75, 'Iran', 'IR', 1, '2025-12-29 10:02:01', NULL),
(76, 'Iraq', 'IQ', 1, '2025-12-29 10:02:01', NULL),
(77, 'Ireland', 'IE', 1, '2025-12-29 10:02:01', NULL),
(78, 'Israel', 'IL', 1, '2025-12-29 10:02:01', NULL),
(79, 'Italy', 'IT', 1, '2025-12-29 10:02:01', NULL),
(80, 'Jamaica', 'JM', 1, '2025-12-29 10:02:01', NULL),
(81, 'Japan', 'JP', 1, '2025-12-29 10:02:01', NULL),
(82, 'Jordan', 'JO', 1, '2025-12-29 10:02:01', NULL),
(83, 'Kazakhstan', 'KZ', 1, '2025-12-29 10:02:01', NULL),
(84, 'Kenya', 'KE', 1, '2025-12-29 10:02:01', NULL),
(85, 'Kiribati', 'KI', 1, '2025-12-29 10:02:01', NULL),
(86, 'Kuwait', 'KW', 1, '2025-12-29 10:02:01', NULL),
(87, 'Kyrgyzstan', 'KG', 1, '2025-12-29 10:02:01', NULL),
(88, 'Laos', 'LA', 1, '2025-12-29 10:02:01', NULL),
(89, 'Latvia', 'LV', 1, '2025-12-29 10:02:01', NULL),
(90, 'Lebanon', 'LB', 1, '2025-12-29 10:02:01', NULL),
(91, 'Lesotho', 'LS', 1, '2025-12-29 10:02:01', NULL),
(92, 'Liberia', 'LR', 1, '2025-12-29 10:02:01', NULL),
(93, 'Libya', 'LY', 1, '2025-12-29 10:02:01', NULL),
(94, 'Liechtenstein', 'LI', 1, '2025-12-29 10:02:01', NULL),
(95, 'Lithuania', 'LT', 1, '2025-12-29 10:02:01', NULL),
(96, 'Luxembourg', 'LU', 1, '2025-12-29 10:02:01', NULL),
(97, 'Madagascar', 'MG', 1, '2025-12-29 10:02:01', NULL),
(98, 'Malawi', 'MW', 1, '2025-12-29 10:02:01', NULL),
(99, 'Malaysia', 'MY', 1, '2025-12-29 10:02:01', NULL),
(100, 'Maldives', 'MV', 1, '2025-12-29 10:02:01', NULL),
(101, 'Mali', 'ML', 1, '2025-12-29 10:02:01', NULL),
(102, 'Malta', 'MT', 1, '2025-12-29 10:02:01', NULL),
(103, 'Marshall Islands', 'MH', 1, '2025-12-29 10:02:01', NULL),
(104, 'Mauritania', 'MR', 1, '2025-12-29 10:02:01', NULL),
(105, 'Mauritius', 'MU', 1, '2025-12-29 10:02:01', NULL),
(106, 'Mexico', 'MX', 1, '2025-12-29 10:02:01', NULL),
(107, 'Micronesia', 'FM', 1, '2025-12-29 10:02:01', NULL),
(108, 'Moldova', 'MD', 1, '2025-12-29 10:02:01', NULL),
(109, 'Monaco', 'MC', 1, '2025-12-29 10:02:01', NULL),
(110, 'Mongolia', 'MN', 1, '2025-12-29 10:02:01', NULL),
(111, 'Montenegro', 'ME', 1, '2025-12-29 10:02:01', NULL),
(112, 'Morocco', 'MA', 1, '2025-12-29 10:02:01', NULL),
(113, 'Mozambique', 'MZ', 1, '2025-12-29 10:02:01', NULL),
(114, 'Myanmar', 'MM', 1, '2025-12-29 10:02:01', NULL),
(115, 'Namibia', 'NA', 1, '2025-12-29 10:02:01', NULL),
(116, 'Nauru', 'NR', 1, '2025-12-29 10:02:01', NULL),
(117, 'Nepal', 'NP', 1, '2025-12-29 10:02:01', NULL),
(118, 'Netherlands', 'NL', 1, '2025-12-29 10:02:01', NULL),
(119, 'New Zealand', 'NZ', 1, '2025-12-29 10:02:01', NULL),
(120, 'Nicaragua', 'NI', 1, '2025-12-29 10:02:01', NULL),
(121, 'Niger', 'NE', 1, '2025-12-29 10:02:01', NULL),
(122, 'Nigeria', 'NG', 1, '2025-12-29 10:02:01', NULL),
(123, 'North Korea', 'KP', 1, '2025-12-29 10:02:01', NULL),
(124, 'North Macedonia', 'MK', 1, '2025-12-29 10:02:01', NULL),
(125, 'Norway', 'NO', 1, '2025-12-29 10:02:01', NULL),
(126, 'Oman', 'OM', 1, '2025-12-29 10:02:01', NULL),
(127, 'Pakistan', 'PK', 1, '2025-12-29 10:02:01', NULL),
(128, 'Palau', 'PW', 1, '2025-12-29 10:02:01', NULL),
(129, 'Palestine', 'PS', 1, '2025-12-29 10:02:01', NULL),
(130, 'Panama', 'PA', 1, '2025-12-29 10:02:01', NULL),
(131, 'Papua New Guinea', 'PG', 1, '2025-12-29 10:02:01', NULL),
(132, 'Paraguay', 'PY', 1, '2025-12-29 10:02:01', NULL),
(133, 'Peru', 'PE', 1, '2025-12-29 10:02:01', NULL),
(134, 'Philippines', 'PH', 1, '2025-12-29 10:02:01', NULL),
(135, 'Poland', 'PL', 1, '2025-12-29 10:02:01', NULL),
(136, 'Portugal', 'PT', 1, '2025-12-29 10:02:01', NULL),
(137, 'Qatar', 'QA', 1, '2025-12-29 10:02:01', NULL),
(138, 'Romania', 'RO', 1, '2025-12-29 10:02:01', NULL),
(139, 'Russia', 'RU', 1, '2025-12-29 10:02:01', NULL),
(140, 'Rwanda', 'RW', 1, '2025-12-29 10:02:01', NULL),
(141, 'Saint Kitts and Nevis', 'KN', 1, '2025-12-29 10:02:01', NULL),
(142, 'Saint Lucia', 'LC', 1, '2025-12-29 10:02:01', NULL),
(143, 'Saint Vincent and the Grenadines', 'VC', 1, '2025-12-29 10:02:01', NULL),
(144, 'Samoa', 'WS', 1, '2025-12-29 10:02:01', NULL),
(145, 'San Marino', 'SM', 1, '2025-12-29 10:02:01', NULL),
(146, 'Sao Tome and Principe', 'ST', 1, '2025-12-29 10:02:01', NULL),
(147, 'Saudi Arabia', 'SA', 1, '2025-12-29 10:02:01', NULL),
(148, 'Senegal', 'SN', 1, '2025-12-29 10:02:01', NULL),
(149, 'Serbia', 'RS', 1, '2025-12-29 10:02:01', NULL),
(150, 'Seychelles', 'SC', 1, '2025-12-29 10:02:01', NULL),
(151, 'Sierra Leone', 'SL', 1, '2025-12-29 10:02:01', NULL),
(152, 'Singapore', 'SG', 1, '2025-12-29 10:02:01', NULL),
(153, 'Slovakia', 'SK', 1, '2025-12-29 10:02:01', NULL),
(154, 'Slovenia', 'SI', 1, '2025-12-29 10:02:01', NULL),
(155, 'Solomon Islands', 'SB', 1, '2025-12-29 10:02:01', NULL),
(156, 'Somalia', 'SO', 1, '2025-12-29 10:02:01', NULL),
(157, 'South Africa', 'ZA', 1, '2025-12-29 10:02:01', NULL),
(158, 'South Korea', 'KR', 1, '2025-12-29 10:02:01', NULL),
(159, 'South Sudan', 'SS', 1, '2025-12-29 10:02:01', NULL),
(160, 'Spain', 'ES', 1, '2025-12-29 10:02:01', NULL),
(161, 'Sri Lanka', 'LK', 1, '2025-12-29 10:02:01', NULL),
(162, 'Sudan', 'SD', 1, '2025-12-29 10:02:01', NULL),
(163, 'Suriname', 'SR', 1, '2025-12-29 10:02:01', NULL),
(164, 'Sweden', 'SE', 1, '2025-12-29 10:02:01', NULL),
(165, 'Switzerland', 'CH', 1, '2025-12-29 10:02:01', NULL),
(166, 'Syria', 'SY', 1, '2025-12-29 10:02:01', NULL),
(167, 'Taiwan', 'TW', 1, '2025-12-29 10:02:01', NULL),
(168, 'Tajikistan', 'TJ', 1, '2025-12-29 10:02:01', NULL),
(169, 'Tanzania', 'TZ', 1, '2025-12-29 10:02:01', NULL),
(170, 'Thailand', 'TH', 1, '2025-12-29 10:02:01', NULL),
(171, 'Timor-Leste', 'TL', 1, '2025-12-29 10:02:01', NULL),
(172, 'Togo', 'TG', 1, '2025-12-29 10:02:01', NULL),
(173, 'Tonga', 'TO', 1, '2025-12-29 10:02:01', NULL),
(174, 'Trinidad and Tobago', 'TT', 1, '2025-12-29 10:02:01', NULL),
(175, 'Tunisia', 'TN', 1, '2025-12-29 10:02:01', NULL),
(176, 'Turkey', 'TR', 1, '2025-12-29 10:02:01', NULL),
(177, 'Turkmenistan', 'TM', 1, '2025-12-29 10:02:01', NULL),
(178, 'Tuvalu', 'TV', 1, '2025-12-29 10:02:01', NULL),
(179, 'Uganda', 'UG', 1, '2025-12-29 10:02:01', NULL),
(180, 'Ukraine', 'UA', 1, '2025-12-29 10:02:01', NULL),
(181, 'United Arab Emirates', 'AE', 1, '2025-12-29 10:02:01', NULL),
(182, 'United Kingdom', 'GB', 1, '2025-12-29 10:02:01', NULL),
(183, 'United States', 'US', 1, '2025-12-29 10:02:01', NULL),
(184, 'Uruguay', 'UY', 1, '2025-12-29 10:02:01', NULL),
(185, 'Uzbekistan', 'UZ', 1, '2025-12-29 10:02:01', NULL),
(186, 'Vanuatu', 'VU', 1, '2025-12-29 10:02:01', NULL),
(187, 'Vatican City', 'VA', 1, '2025-12-29 10:02:01', NULL),
(188, 'Venezuela', 'VE', 1, '2025-12-29 10:02:01', NULL),
(189, 'Vietnam', 'VN', 1, '2025-12-29 10:02:01', NULL),
(190, 'Yemen', 'YE', 1, '2025-12-29 10:02:01', NULL),
(191, 'Zambia', 'ZM', 1, '2025-12-29 10:02:01', NULL),
(192, 'Zimbabwe', 'ZW', 1, '2025-12-29 10:02:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Assembly', 'Assembly line operations', 1, '2025-12-15 10:40:55', '2025-12-15 11:03:59'),
(2, 'Logistics', 'Logistics and shipping', 1, '2025-12-15 10:40:55', NULL),
(3, 'Quality Control', 'Quality assurance and control', 1, '2025-12-15 10:40:55', NULL),
(4, 'Management', 'Management and administration', 1, '2025-12-15 10:40:55', NULL),
(5, 'Maintenance', 'Equipment maintenance', 1, '2025-12-15 10:40:55', NULL),
(7, 'Human Resources', 'HR Operations', 1, '2026-01-02 15:18:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `groups_shift`
--

CREATE TABLE `groups_shift` (
  `id` int UNSIGNED NOT NULL,
  `group` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ACTIVE',
  `is_default` enum('YES','NO') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'NO',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups_shift`
--

INSERT INTO `groups_shift` (`id`, `group`, `code`, `name`, `start_time`, `end_time`, `color`, `status`, `is_default`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'RG0001', 'RG01', 'RG01', '00:00:00', '00:00:00', 'red', 'ACTIVE', 'NO', 1, '2025-12-29 16:33:24', '2025-12-29 16:39:12');

-- --------------------------------------------------------

--
-- Table structure for table `job_positions`
--

CREATE TABLE `job_positions` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_positions`
--

INSERT INTO `job_positions` (`id`, `title`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Line Operator', 'Factory line operator', 1, '2025-12-29 09:48:38', NULL),
(2, 'Supervisor', 'Line supervisor', 1, '2025-12-29 09:48:38', NULL),
(3, 'Manager', 'Department manager', 1, '2025-12-29 09:48:38', NULL),
(4, 'Quality Controller', 'Quality control inspector', 1, '2025-12-29 09:48:38', NULL),
(5, 'Maintenance Technician', 'Equipment maintenance', 1, '2025-12-29 09:48:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leave_reasons`
--

CREATE TABLE `leave_reasons` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `type` enum('Paid Leave','Medical Leave','Unpaid Leave','Other') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Paid Leave',
  `description` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_reasons`
--

INSERT INTO `leave_reasons` (`id`, `name`, `type`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Annual Leave', 'Paid Leave', 'Paid annual vacation leave', 1, NULL, NULL),
(2, 'Sick Leave', 'Medical Leave', 'Medical leave for illness', 1, NULL, NULL),
(3, 'Medical Appointment', 'Medical Leave', 'Leave for medical appointments', 1, NULL, NULL),
(4, 'Family Emergency', 'Paid Leave', 'Emergency family matters', 1, NULL, NULL),
(5, 'Personal Leave', 'Unpaid Leave', 'Personal matters requiring time off', 1, NULL, NULL),
(6, 'Maternity Leave', 'Paid Leave', 'Maternity leave for mothers', 1, NULL, NULL),
(7, 'Paternity Leave', 'Paid Leave', 'Paternity leave for fathers', 1, NULL, NULL),
(8, 'Bereavement Leave', 'Paid Leave', 'Leave for funeral or bereavement', 1, NULL, NULL),
(9, 'Training/Conference', 'Paid Leave', 'Attending training or conference', 1, NULL, NULL),
(10, 'Unpaid Personal Leave', 'Unpaid Leave', 'Unpaid leave for personal reasons', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint UNSIGNED NOT NULL,
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2024-01-01-000001', 'App\\Database\\Migrations\\CreateUsersTable', 'default', 'App', 1765531131, 1),
(2, '2024-01-01-000002', 'App\\Database\\Migrations\\AddProfilePhotoToUsers', 'default', 'App', 1765533237, 2),
(3, '2024-01-01-000003', 'App\\Database\\Migrations\\CreateZonesTable', 'default', 'App', 1765534623, 3),
(4, '2024-01-01-000004', 'App\\Database\\Migrations\\CreateAntennaModesTable', 'default', 'App', 1765780605, 4),
(5, '2024-01-01-000005', 'App\\Database\\Migrations\\AddFunctionToZonesTable', 'default', 'App', 1765782546, 5),
(6, '2024-01-01-000006', 'App\\Database\\Migrations\\AddColorToAntennaModesTable', 'default', 'App', 1765784798, 6),
(7, '2024-01-01-000007', 'App\\Database\\Migrations\\CreateWorkersTable', 'default', 'App', 1765787976, 7),
(8, '2024-01-01-000008', 'App\\Database\\Migrations\\CreateDepartmentsTable', 'default', 'App', 1765795255, 8),
(9, '2025-12-16-000001', 'App\\Database\\Migrations\\CreateAttendanceRecordsTable', 'default', 'App', 1765860860, 9),
(10, '2025-12-23-000001', 'App\\Database\\Migrations\\AddRfidTagToWorkersTable', 'default', 'App', 1766458394, 10),
(11, '2025-12-29-000001', 'App\\Database\\Migrations\\CreateJobPositionsTable', 'default', 'App', 1766972918, 11),
(15, '2025-12-29-000002', 'App\\Database\\Migrations\\CreateCountriesTable', 'default', 'App', 1766973721, 12),
(16, '2025-12-29-000003', 'App\\Database\\Migrations\\CreateStatesTable', 'default', 'App', 1766973721, 12),
(17, '2025-12-29-000004', 'App\\Database\\Migrations\\CreateCitiesTable', 'default', 'App', 1766973721, 12),
(18, '2025-12-29-000005', 'App\\Database\\Migrations\\AddLocationToWorkersTable', 'default', 'App', 1766973721, 12),
(19, '2025-12-29-000006', 'App\\Database\\Migrations\\CreateOperatingHoursTable', 'default', 'App', 1766977978, 13),
(20, '2025-12-29-000007', 'App\\Database\\Migrations\\CreateShiftsTable', 'default', 'App', 1766979260, 14),
(21, '2025-12-29-000008', 'App\\Database\\Migrations\\CreateStaffGroupsTable', 'default', 'App', 1766980492, 15),
(22, '2025-12-29-000009', 'App\\Database\\Migrations\\CreatePublicHolidaysTable', 'default', 'App', 1766981289, 16),
(24, '2025-12-29-000010', 'App\\Database\\Migrations\\CreateGroupsShiftTable', 'default', 'App', 1766996126, 17),
(25, '2025-12-29-000011', 'App\\Database\\Migrations\\CreateStaffAvailabilityTable', 'default', 'App', 1766996457, 18),
(26, '2025-12-29-000012', 'App\\Database\\Migrations\\CreateStaffShiftAllocationTable', 'default', 'App', 1766998175, 19),
(27, '2025-12-29-000013', 'App\\Database\\Migrations\\AddIcNumberToWorkersTable', 'default', 'App', 1767002677, 20),
(28, '2025-12-31-000001', 'App\\Database\\Migrations\\AddIndexesToAttendanceRecords', 'default', 'App', 1767154966, 21),
(29, '2025-12-31-000002', 'App\\Database\\Migrations\\CreateZoneAntennasTable', 'default', 'App', 1767174404, 22),
(30, '2026-01-02-033605', 'App\\Database\\Migrations\\AlterShiftColumnInWorkersTable', 'default', 'App', 1767324994, 23),
(31, '2026-01-02-100000', 'App\\Database\\Migrations\\CreateLeaveReasonsTables', 'default', 'App', 1767338313, 24);

-- --------------------------------------------------------

--
-- Table structure for table `operating_hours`
--

CREATE TABLE `operating_hours` (
  `id` int UNSIGNED NOT NULL,
  `day` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operating_hours`
--

INSERT INTO `operating_hours` (`id`, `day`, `start_time`, `end_time`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Monday', '08:00:00', '17:00:00', 1, '2025-12-29 11:19:44', '2025-12-29 11:27:27'),
(2, 'Tuesday', '08:00:00', '17:00:00', 1, '2025-12-29 11:27:43', '2025-12-29 11:27:57'),
(3, 'Wednesday', '08:00:00', '17:00:00', 1, '2025-12-29 11:28:14', '2025-12-29 11:28:14'),
(4, 'Thursday', '08:00:00', '17:00:00', 1, '2025-12-29 11:28:28', '2025-12-29 11:28:28'),
(5, 'Friday', '08:00:00', '17:00:00', 1, '2025-12-29 11:28:41', '2025-12-29 11:28:41'),
(6, 'Saturday', '08:00:00', '14:00:00', 1, '2025-12-29 11:29:02', '2025-12-29 11:29:02'),
(7, 'Sunday', '08:00:00', '14:00:00', 1, '2025-12-29 11:29:15', '2025-12-29 11:29:15');

-- --------------------------------------------------------

--
-- Table structure for table `public_holidays`
--

CREATE TABLE `public_holidays` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `holiday_date` date NOT NULL,
  `type` enum('Federal','State') COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `public_holidays`
--

INSERT INTO `public_holidays` (`id`, `name`, `holiday_date`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'New Year', '2026-01-01', 'Federal', 1, '2025-12-29 12:28:43', '2025-12-29 12:28:43'),
(4, 'State Selangor Holiday', '2026-01-06', 'State', 1, '2025-12-29 12:37:41', '2025-12-29 12:37:41');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `name`, `start_time`, `end_time`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Morning', '08:00:00', '17:00:00', 1, '2025-12-29 11:46:50', '2026-01-02 17:33:25'),
(2, 'Afternoon', '14:00:00', '01:00:00', 1, '2025-12-29 11:47:49', '2026-01-02 17:33:09'),
(3, 'Evening', '18:00:00', '05:00:00', 1, '2025-12-29 11:49:07', '2026-01-02 17:08:05'),
(4, 'Evening Late', '15:00:00', '17:00:00', 0, '2026-01-02 14:48:42', '2026-01-02 17:33:16'),
(5, 'Late Night', '01:00:00', '06:00:00', 1, '2026-01-05 16:24:31', '2026-01-05 16:24:31');

-- --------------------------------------------------------

--
-- Table structure for table `staff_availability`
--

CREATE TABLE `staff_availability` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ACTIVE',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_availability`
--

INSERT INTO `staff_availability` (`id`, `name`, `description`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Present', 'Present', 'ACTIVE', 1, '2025-12-29 16:22:00', '2025-12-29 16:22:00'),
(2, 'Paid Leave', 'Paid to be on leave', 'ACTIVE', 1, '2025-12-29 16:22:26', '2025-12-29 16:22:26'),
(3, 'Medical Leave', 'Due to medical reasons', 'ACTIVE', 1, '2025-12-29 16:22:47', '2025-12-29 16:22:47');

-- --------------------------------------------------------

--
-- Table structure for table `staff_groups`
--

CREATE TABLE `staff_groups` (
  `id` int UNSIGNED NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `note` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_groups`
--

INSERT INTO `staff_groups` (`id`, `code`, `name`, `note`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'RG0001', 'RG0001', 'Manual added', 1, '2025-12-29 12:00:22', '2025-12-29 12:00:43'),
(2, 'RG0002', 'RG0002', 'Manual added', 1, '2025-12-29 12:01:14', '2025-12-29 12:01:14');

-- --------------------------------------------------------

--
-- Table structure for table `staff_shift_allocation`
--

CREATE TABLE `staff_shift_allocation` (
  `id` int UNSIGNED NOT NULL,
  `group_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `allocation_date` date NOT NULL,
  `shift_code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_shift_allocation`
--

INSERT INTO `staff_shift_allocation` (`id`, `group_id`, `allocation_date`, `shift_code`, `is_active`, `created_at`, `updated_at`) VALUES
(12, 'RG0001', '2025-12-01', 'RG01', 1, '2025-12-29 17:02:31', '2025-12-29 17:02:31'),
(13, 'RG0001', '2025-12-02', 'RG01', 1, '2025-12-29 17:02:31', '2025-12-29 17:02:31'),
(14, 'RG0001', '2025-12-03', 'RG01', 1, '2025-12-29 17:02:31', '2025-12-29 17:02:31'),
(15, 'RG0001', '2025-12-04', 'RG01', 1, '2025-12-29 17:02:31', '2025-12-29 17:02:31'),
(16, 'RG0001', '2025-12-05', 'RG01', 1, '2025-12-29 17:02:31', '2025-12-29 17:02:31');

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `country_id` int UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`, `country_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Johor', 99, 1, '2025-12-29 10:02:01', NULL),
(2, 'Kedah', 99, 1, '2025-12-29 10:02:01', NULL),
(3, 'Kelantan', 99, 1, '2025-12-29 10:02:01', NULL),
(4, 'Kuala Lumpur', 99, 1, '2025-12-29 10:02:01', NULL),
(5, 'Labuan', 99, 1, '2025-12-29 10:02:01', NULL),
(6, 'Melaka', 99, 1, '2025-12-29 10:02:01', NULL),
(7, 'Negeri Sembilan', 99, 1, '2025-12-29 10:02:01', NULL),
(8, 'Pahang', 99, 1, '2025-12-29 10:02:01', NULL),
(9, 'Penang', 99, 1, '2025-12-29 10:02:01', NULL),
(10, 'Perak', 99, 1, '2025-12-29 10:02:01', NULL),
(11, 'Perlis', 99, 1, '2025-12-29 10:02:01', NULL),
(12, 'Putrajaya', 99, 1, '2025-12-29 10:02:01', NULL),
(13, 'Sabah', 99, 1, '2025-12-29 10:02:01', NULL),
(14, 'Sarawak', 99, 1, '2025-12-29 10:02:01', NULL),
(15, 'Selangor', 99, 1, '2025-12-29 10:02:01', NULL),
(16, 'Terengganu', 99, 1, '2025-12-29 10:02:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `profile_photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `profile_photo`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'admin', 'admin@bytespace.asia', '$2y$10$/3.6nNtSpg9.VgdUjLZcUu4qHUOIOLVNGR.OnqiHDGRRQAiFxtqEG', 'Workwise', 'profile_2_1767080709.png', 1, '2025-12-12 10:11:28', '2025-12-30 15:45:09');

-- --------------------------------------------------------

--
-- Table structure for table `workers`
--

CREATE TABLE `workers` (
  `worker_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ic_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rfid_tag_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'UHF RFID tag ID from Yanzeo SA810 reader',
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `country_id` int UNSIGNED DEFAULT NULL,
  `state_id` int UNSIGNED DEFAULT NULL,
  `city_id` int UNSIGNED DEFAULT NULL,
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `position` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `start_date` date NOT NULL,
  `shift` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive','on_break','offline') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `profile_photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `documents` text COLLATE utf8mb4_general_ci,
  `assigned_zones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'JSON array of zone IDs',
  `last_active` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workers`
--

INSERT INTO `workers` (`worker_id`, `ic_number`, `rfid_tag_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `country_id`, `state_id`, `city_id`, `department`, `position`, `start_date`, `shift`, `status`, `profile_photo`, `documents`, `assigned_zones`, `last_active`, `created_at`, `updated_at`) VALUES
('ID-14297', '', 'DD20251128000501B0000041', 'Muhammad', 'Aiman', 'aiman@bytespace.asia', '+60199924358', 'Bytespace SDN BHD E – 2 – 26 Sunsuria Forum, Jalan Setia Dagang AL U13/AL Setia Alam', 99, 15, 64, 'logistics', 'Line Operator', '2025-12-14', 'Evening', 'active', NULL, NULL, '[\"Z-1002\",\"Z-1007\",\"Z-1003\",\"Z-1001\",\"Z-1005\"]', '2026-01-09 18:33:08', '2025-12-15 09:06:20', '2026-01-09 18:33:09'),
('ID-14298', '', 'DD20251128000501B0000042', 'Aqil', 'Nazran', 'nazran@bytespace.asia', '+60199924359', 'Bytespace SDN BHD E – 2 – 26 Sunsuria Forum, Jalan Setia Dagang AL U13/AL Setia Alam', 99, 15, 64, 'assembly', 'Line Operator', '2025-12-10', 'Evening', 'active', NULL, NULL, '[\"Z-1001\",\"Z-1005\",\"Z-1009\",\"Z-1010\"]', '2026-01-09 18:33:08', '2025-12-15 08:46:03', '2026-01-09 18:33:08'),
('ID-14299', '020314035432', 'DD20251128000501B0000044', 'Fatin', 'Aqila', 'fatin@bytespace.asia', '+60199924357', '18A, Jalan SS 15/2A', 99, 15, 64, 'assembly', 'Supervisor', '2025-12-16', 'Evening', 'active', NULL, NULL, '[\"Z-1001\",\"Z-1002\",\"Z-1003\",\"Z-1005\",\"Z-1008\"]', '2026-01-09 18:32:17', '2025-12-16 17:54:10', '2026-01-09 18:32:17'),
('ID-14301', '020314030527', 'DD20251128000501B0000043', 'Wan', 'Ahmad Farhan', 'farhan@bytespace.asia', '+60199924359', '13-2, Jalan Lengkuas, E16/E, Seksyen 16', 99, 15, 64, 'management', 'Manager', '2025-12-30', 'Morning', 'active', '1767064201_e54efed6456d79679dbd.png', NULL, '[\"Z-1001\",\"Z-1004\",\"Z-1005\",\"Z-1006\",\"Z-1008\"]', '2026-01-12 11:21:02', '2025-12-30 11:03:03', '2026-01-12 11:21:02'),
('ID-14302', '', 'DD20251128000501B0000048', 'Conrad', 'Jacob', 'conrad@bytspace.asia', '', 'Bytespace SDN BHD E – 2 – 26 Sunsuria Forum, Jalan Setia Dagang AL U13/AL Setia Alam', 99, 15, 64, 'management', 'Manager', '2025-12-30', 'Morning', 'active', NULL, NULL, '[\"Z-1001\",\"Z-1004\",\"Z-1005\",\"Z-1006\",\"Z-1008\"]', '2026-01-12 11:21:14', '2025-12-30 11:17:38', '2026-01-12 11:21:14'),
('ID-14303', '', 'E28069150000401232C7529E', 'Muhammad', 'Shafiq', 'shafiq@bytespace.asia', '', 'Bytespace SDN BHD E – 2 – 26 Sunsuria Forum, Jalan Setia Dagang AL U13/AL Setia Alam', 99, 15, 64, 'maintenance', 'Maintenance Technician', '2025-12-30', 'Morning', 'active', NULL, NULL, '[\"Z-1001\",\"Z-1004\",\"Z-1005\",\"Z-1006\",\"Z-1008\"]', '2026-01-12 12:50:53', '2025-12-30 11:19:48', '2026-01-12 12:50:53'),
('ID-14304', '', 'E28069150000501232C74E9E', 'Muhammad', 'Irham', 'irham@bytespace.asia', '', 'Bytespace SDN BHD E – 2 – 26 Sunsuria Forum, Jalan Setia Dagang AL U13/AL Setia Alam', 99, 15, 64, 'maintenance', 'Maintenance Technician', '2025-12-30', 'Morning', 'active', NULL, NULL, '[\"Z-1001\",\"Z-1004\",\"Z-1005\",\"Z-1006\",\"Z-1008\"]', '2026-01-12 12:50:57', '2025-12-30 11:23:26', '2026-01-12 12:50:57'),
('ID-14305', '', 'E28069150000501232C7569E', 'Suresh', 'Candambareswaran', 'suresh@bytespace.asia', '', 'Bytespace SDN BHD E – 2 – 26 Sunsuria Forum, Jalan Setia Dagang AL U13/AL Setia Alam', 99, 15, 64, 'management', 'Manager', '2025-12-30', 'Evening,Morning', 'active', NULL, NULL, '[\"Z-1001\",\"Z-1002\",\"Z-1003\",\"Z-1004\",\"Z-1005\",\"Z-1006\",\"Z-1008\"]', '2026-01-12 12:53:08', '2025-12-30 11:30:53', '2026-01-12 12:53:08'),
('ID-14306', '', 'E280F30200000000C142E929', 'Sharifah', '‎ ', 'sharifah@bytespace.asia', '', 'Bytespace SDN BHD E – 2 – 26 Sunsuria Forum, Jalan Setia Dagang AL U13/AL Setia Alam', 99, 15, 64, 'human resources', 'Manager', '2025-12-30', 'Morning', 'active', NULL, NULL, '[\"Z-1001\",\"Z-1005\",\"Z-1006\",\"Z-1008\",\"Z-1011\"]', '2026-01-12 12:52:28', '2025-12-30 11:33:54', '2026-01-12 12:52:28');

-- --------------------------------------------------------

--
-- Table structure for table `worker_leave_records`
--

CREATE TABLE `worker_leave_records` (
  `id` int UNSIGNED NOT NULL,
  `worker_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `leave_reason_id` int UNSIGNED NOT NULL,
  `leave_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_by` int UNSIGNED DEFAULT NULL COMMENT 'User ID who created this record',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `worker_leave_records`
--

INSERT INTO `worker_leave_records` (`id`, `worker_id`, `leave_reason_id`, `leave_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'ID-14306', 1, '2026-01-07', 'Test', 2, '2026-01-02 16:06:40', '2026-01-02 16:06:40');

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `id` int UNSIGNED NOT NULL,
  `zone_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `zone_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'location_on',
  `icon_color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'blue',
  `antenna_mode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `reader_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'YanzeoSA810',
  `antenna_color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'purple',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `port` int NOT NULL DEFAULT '8080',
  `power_level` int NOT NULL DEFAULT '30',
  `function` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`id`, `zone_id`, `zone_name`, `location`, `icon`, `icon_color`, `antenna_mode`, `reader_type`, `antenna_color`, `ip_address`, `port`, `power_level`, `function`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Z-1001', 'Main Entrance Gate', '', 'home_pin', 'blue', 'RFID-UHF', 'ZebraFX7500', 'purple', '192.168.100.115', 49152, 30, 'IN', 'active', '2025-12-15 06:56:24', '2026-01-09 17:45:34'),
(2, 'Z-1002', 'Warehouse A - Loading', '', 'location_on', 'blue', 'NFC', 'YanzeoSA810', 'purple', '192.168.1.11', 8080, 30, 'IN / OUT', 'active', '2025-12-15 07:25:02', '2025-12-15 07:25:02'),
(3, 'Z-1003', 'Warehouse B - Storage', '', 'warehouse', 'amber', 'RFID-UHF', 'YanzeoSA810', 'purple', '192.168.100.118', 49152, 30, 'IN / OUT', 'active', '2025-12-15 07:31:37', '2025-12-30 16:37:27'),
(4, 'Z-1004', 'Server Room', '', 'location_on', 'blue', 'Biometric', 'YanzeoSA810', 'purple', '192.168.1.20', 8080, 30, 'IN / OUT', 'active', '2025-12-15 07:37:18', '2025-12-15 07:37:18'),
(5, 'Z-1005', 'Cafeteria', '', 'location_on', 'blue', 'NFC', 'YanzeoSA810', 'purple', '192.168.1.30', 8080, 30, 'IN ONLY', 'active', '2025-12-15 07:40:28', '2025-12-15 07:40:28'),
(6, 'Z-1020', 'Test', '', 'location_on', 'blue', 'Test', 'YanzeoSA810', 'purple', '192.168.1.112', 8080, 30, 'IN ONLY', 'inactive', '2025-12-15 07:51:07', '2025-12-15 07:59:45'),
(7, 'Z-1006', 'Office Level 2', '', 'business_center', 'blue', 'Bluetooth', 'YanzeoSA810', 'purple', '192.168.1.45', 8080, 30, 'IN / OUT', 'active', '2025-12-15 08:10:07', '2025-12-15 08:10:07'),
(8, 'Z-1007', 'Loading Dock', '', 'warehouse', 'amber', 'RFID-UHF', 'YanzeoSA810', 'purple', '192.168.100.117', 49152, 30, 'IN / OUT', 'active', '2025-12-16 04:15:24', '2025-12-23 17:48:15'),
(9, 'Z-1008', 'Office Level 1', '', 'business_center', 'blue', 'Biometric', 'YanzeoSA810', 'purple', '192.168.1.44', 8080, 30, 'IN / OUT', 'active', '2025-12-16 04:16:36', '2025-12-16 04:16:36'),
(10, 'Z-1009', 'Assembly Line 1', '', 'location_on', 'blue', 'NFC', 'YanzeoSA810', 'purple', '192.168.1.50', 8080, 30, 'IN ONLY', 'active', '2025-12-16 04:19:09', '2025-12-16 04:19:09'),
(11, 'Z-1010', 'Assembly Line 2', '', 'location_on', 'blue', 'NFC', 'YanzeoSA810', 'purple', '192.168.1.51', 8080, 30, 'OUT ONLY', 'active', '2025-12-16 04:19:52', '2025-12-16 04:19:52'),
(12, 'Z-1011', 'Quality Control', '', 'location_on', 'blue', 'GPS', 'YanzeoSA810', 'purple', '192.168.1.60', 8080, 30, 'IN / OUT', 'active', '2025-12-16 04:20:54', '2025-12-16 04:20:54');

-- --------------------------------------------------------

--
-- Table structure for table `zone_antennas`
--

CREATE TABLE `zone_antennas` (
  `id` int UNSIGNED NOT NULL,
  `zone_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `antenna_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `port` int NOT NULL DEFAULT '49152',
  `antenna_mode` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `function` enum('IN','OUT','IN / OUT') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'IN / OUT',
  `power_level` int NOT NULL DEFAULT '30',
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zone_antennas`
--

INSERT INTO `zone_antennas` (`id`, `zone_id`, `antenna_name`, `ip_address`, `port`, `antenna_mode`, `function`, `power_level`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(79, 'Z-1001', 'Antenna 1', '192.168.100.115', 49152, 'RFID-UHF', 'IN', 30, 'active', 1, '2026-01-09 17:45:34', '2026-01-09 17:45:34'),
(80, 'Z-1001', 'Antenna 2', '192.168.100.114', 49152, 'RFID-UHF', 'OUT', 30, 'active', 2, '2026-01-09 17:45:34', '2026-01-09 17:45:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `antenna_modes`
--
ALTER TABLE `antenna_modes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `worker_id` (`worker_id`),
  ADD KEY `date` (`date`),
  ADD KEY `idx_attendance_active_checkin` (`worker_id`,`zone_id`,`date`,`check_out_time`),
  ADD KEY `idx_attendance_date` (`date`,`worker_id`),
  ADD KEY `idx_attendance_worker` (`worker_id`,`date`),
  ADD KEY `idx_attendance_checkout` (`worker_id`,`zone_id`,`check_out_time`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cities_state_id_foreign` (`state_id`),
  ADD KEY `cities_country_id_foreign` (`country_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `groups_shift`
--
ALTER TABLE `groups_shift`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_positions`
--
ALTER TABLE `job_positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Indexes for table `leave_reasons`
--
ALTER TABLE `leave_reasons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `operating_hours`
--
ALTER TABLE `operating_hours`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `public_holidays`
--
ALTER TABLE `public_holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_availability`
--
ALTER TABLE `staff_availability`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_groups`
--
ALTER TABLE `staff_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `staff_shift_allocation`
--
ALTER TABLE `staff_shift_allocation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id_allocation_date` (`group_id`,`allocation_date`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD KEY `states_country_id_foreign` (`country_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`worker_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `rfid_tag_id` (`rfid_tag_id`),
  ADD KEY `fk_workers_country` (`country_id`),
  ADD KEY `fk_workers_state` (`state_id`),
  ADD KEY `fk_workers_city` (`city_id`);

--
-- Indexes for table `worker_leave_records`
--
ALTER TABLE `worker_leave_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `worker_id` (`worker_id`),
  ADD KEY `leave_reason_id` (`leave_reason_id`),
  ADD KEY `leave_date` (`leave_date`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zone_id` (`zone_id`);

--
-- Indexes for table `zone_antennas`
--
ALTER TABLE `zone_antennas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zone_id` (`zone_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `antenna_modes`
--
ALTER TABLE `antenna_modes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `groups_shift`
--
ALTER TABLE `groups_shift`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `job_positions`
--
ALTER TABLE `job_positions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `leave_reasons`
--
ALTER TABLE `leave_reasons`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `operating_hours`
--
ALTER TABLE `operating_hours`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `public_holidays`
--
ALTER TABLE `public_holidays`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `staff_availability`
--
ALTER TABLE `staff_availability`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff_groups`
--
ALTER TABLE `staff_groups`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff_shift_allocation`
--
ALTER TABLE `staff_shift_allocation`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `worker_leave_records`
--
ALTER TABLE `worker_leave_records`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `zone_antennas`
--
ALTER TABLE `zone_antennas`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cities_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workers`
--
ALTER TABLE `workers`
  ADD CONSTRAINT `fk_workers_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_workers_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_workers_state` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `workers` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `worker_leave_records`
--
ALTER TABLE `worker_leave_records`
  ADD CONSTRAINT `worker_leave_records_leave_reason_id_foreign` FOREIGN KEY (`leave_reason_id`) REFERENCES `leave_reasons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `worker_leave_records_worker_id_foreign` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `zone_antennas`
--
ALTER TABLE `zone_antennas`
  ADD CONSTRAINT `zone_antennas_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
