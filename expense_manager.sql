-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2025 at 01:06 PM
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
-- Database: `expense_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `approvals`
--

CREATE TABLE `approvals` (
  `id` int(11) NOT NULL,
  `expense_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `step_order` int(11) DEFAULT 1,
  `status` enum('Pending','Approved','Rejected','Skipped') DEFAULT 'Pending',
  `comments` text DEFAULT NULL,
  `action_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approvals`
--

INSERT INTO `approvals` (`id`, `expense_id`, `approver_id`, `step_order`, `status`, `comments`, `action_time`, `created_at`) VALUES
(1, 1, 3, 1, 'Approved', '', '2025-10-04 15:30:24', '2025-10-04 15:28:20'),
(2, 1, 2, 3, 'Pending', NULL, NULL, '2025-10-04 15:28:20'),
(3, 2, 3, 1, 'Rejected', 'molo', '2025-10-04 15:30:21', '2025-10-04 15:29:03'),
(4, 2, 2, 3, 'Skipped', NULL, NULL, '2025-10-04 15:29:03'),
(5, 3, 3, 1, 'Approved', '', '2025-10-04 15:30:11', '2025-10-04 15:29:37'),
(6, 3, 2, 3, 'Pending', NULL, NULL, '2025-10-04 15:29:37'),
(7, 4, 3, 1, 'Approved', 'done', '2025-10-04 15:47:52', '2025-10-04 15:47:00'),
(8, 4, 2, 3, 'Rejected', 'done', '2025-10-04 15:48:27', '2025-10-04 15:47:00'),
(9, 5, 3, 1, 'Approved', 'done', '2025-10-04 15:52:34', '2025-10-04 15:52:28'),
(10, 5, 2, 3, 'Approved', 'done', '2025-10-04 15:57:33', '2025-10-04 15:52:28'),
(11, 6, 3, 1, 'Approved', 'done', '2025-10-04 16:11:06', '2025-10-04 16:09:53'),
(12, 6, 2, 3, 'Approved', 'done', '2025-10-04 16:12:41', '2025-10-04 16:09:53'),
(13, 7, 3, 1, 'Approved', 'done', '2025-10-04 16:25:34', '2025-10-04 16:24:36'),
(14, 7, 6, 2, 'Skipped', NULL, NULL, '2025-10-04 16:24:36'),
(15, 7, 7, 4, 'Approved', 'matlab katam', '2025-10-04 16:24:53', '2025-10-04 16:24:36'),
(16, 7, 2, 5, 'Approved', 'done', '2025-10-04 16:26:05', '2025-10-04 16:24:36');

-- --------------------------------------------------------

--
-- Table structure for table `approval_rules`
--

CREATE TABLE `approval_rules` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `mode` enum('SEQUENTIAL','PARALLEL') DEFAULT 'SEQUENTIAL',
  `rule_type` enum('None','Percentage','Specific','Hybrid') DEFAULT 'None',
  `threshold` int(11) DEFAULT NULL,
  `specific_approver_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approval_rules`
--

INSERT INTO `approval_rules` (`id`, `company_id`, `mode`, `rule_type`, `threshold`, `specific_approver_id`, `created_at`) VALUES
(3, 1, 'PARALLEL', 'Percentage', 60, NULL, '2025-10-04 16:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `approval_sequences`
--

CREATE TABLE `approval_sequences` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `step_order` int(11) NOT NULL,
  `approver_type` enum('USER','ROLE','MANAGER') DEFAULT 'USER',
  `approver_user_id` int(11) DEFAULT NULL,
  `approver_role` varchar(40) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approval_sequences`
--

INSERT INTO `approval_sequences` (`id`, `company_id`, `step_order`, `approver_type`, `approver_user_id`, `approver_role`, `created_at`) VALUES
(9, 1, 1, 'ROLE', NULL, 'Manager', '2025-10-04 16:23:51'),
(10, 1, 2, 'ROLE', NULL, 'HR', '2025-10-04 16:23:51'),
(11, 1, 3, 'ROLE', NULL, 'Finance', '2025-10-04 16:23:51'),
(12, 1, 4, 'ROLE', NULL, 'CFO', '2025-10-04 16:23:51'),
(13, 1, 5, 'ROLE', NULL, 'Director', '2025-10-04 16:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `action` varchar(20) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `country` varchar(100) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `country`, `currency`, `created_at`) VALUES
(1, 'percipeint', 'India', 'INR', '2025-10-04 15:23:58');

-- --------------------------------------------------------

--
-- Table structure for table `exchange_rates`
--

CREATE TABLE `exchange_rates` (
  `base_currency` varchar(10) NOT NULL,
  `rates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rates`)),
  `fetched_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exchange_rates`
--

INSERT INTO `exchange_rates` (`base_currency`, `rates`, `fetched_at`) VALUES
('EUR', '{\"provider\":\"https:\\/\\/www.exchangerate-api.com\",\"WARNING_UPGRADE_TO_V6\":\"https:\\/\\/www.exchangerate-api.com\\/docs\\/free\",\"terms\":\"https:\\/\\/www.exchangerate-api.com\\/terms\",\"base\":\"EUR\",\"date\":\"2025-10-04\",\"time_last_updated\":1759536002,\"rates\":{\"EUR\":1,\"AED\":4.31,\"AFN\":79.39,\"ALL\":96.76,\"AMD\":449.63,\"ANG\":2.1,\"AOA\":1103.56,\"ARS\":1673.01,\"AUD\":1.78,\"AWG\":2.1,\"AZN\":2,\"BAM\":1.96,\"BBD\":2.35,\"BDT\":142.85,\"BGN\":1.96,\"BHD\":0.442,\"BIF\":3467.62,\"BMD\":1.17,\"BND\":1.51,\"BOB\":8.13,\"BRL\":6.27,\"BSD\":1.17,\"BTN\":104.19,\"BWP\":16.44,\"BYN\":3.6,\"BZD\":2.35,\"CAD\":1.64,\"CDF\":3005.97,\"CHF\":0.934,\"CLP\":1129.28,\"CNY\":8.37,\"COP\":4576.08,\"CRC\":591.64,\"CUP\":28.18,\"CVE\":110.27,\"CZK\":24.26,\"DJF\":208.69,\"DKK\":7.47,\"DOP\":73.29,\"DZD\":152.21,\"EGP\":56.06,\"ERN\":17.61,\"ETB\":168.71,\"FJD\":2.64,\"FKP\":0.872,\"FOK\":7.47,\"GBP\":0.872,\"GEL\":3.2,\"GGP\":0.872,\"GHS\":14.87,\"GIP\":0.872,\"GMD\":86.15,\"GNF\":10191.42,\"GTQ\":9,\"GYD\":245.51,\"HKD\":9.14,\"HNL\":30.8,\"HRK\":7.53,\"HTG\":153.52,\"HUF\":388.48,\"IDR\":19466.89,\"ILS\":3.88,\"IMP\":0.872,\"INR\":104.19,\"IQD\":1535.24,\"IRR\":49543.15,\"ISK\":142.12,\"JEP\":0.872,\"JMD\":189.11,\"JOD\":0.833,\"JPY\":173.06,\"KES\":151.63,\"KGS\":102.41,\"KHR\":4706.72,\"KID\":1.78,\"KMF\":491.97,\"KRW\":1650.93,\"KWD\":0.358,\"KYD\":0.979,\"KZT\":642.71,\"LAK\":25534.68,\"LBP\":105094.94,\"LKR\":354.79,\"LRD\":213.11,\"LSL\":20.23,\"LYD\":6.34,\"MAD\":10.69,\"MDL\":19.59,\"MGA\":5178.73,\"MKD\":61.69,\"MMK\":2467.09,\"MNT\":4234.99,\"MOP\":9.41,\"MRU\":46.96,\"MUR\":53.4,\"MVR\":18.12,\"MWK\":2049.56,\"MXN\":21.6,\"MYR\":4.94,\"MZN\":75.01,\"NAD\":20.23,\"NGN\":1712.04,\"NIO\":43.17,\"NOK\":11.68,\"NPR\":166.7,\"NZD\":2.01,\"OMR\":0.451,\"PAB\":1.17,\"PEN\":4.07,\"PGK\":4.97,\"PHP\":68.08,\"PKR\":332.45,\"PLN\":4.26,\"PYG\":8318.69,\"QAR\":4.27,\"RON\":5.09,\"RSD\":117.17,\"RUB\":96.41,\"RWF\":1709.91,\"SAR\":4.4,\"SBD\":9.6,\"SCR\":17.46,\"SDG\":524.5,\"SEK\":11,\"SGD\":1.51,\"SHP\":0.872,\"SLE\":27.4,\"SLL\":27403.41,\"SOS\":671.13,\"SRD\":45.06,\"SSP\":5548.39,\"STN\":24.5,\"SYP\":15433.98,\"SZL\":20.23,\"THB\":38.04,\"TJS\":11.04,\"TMT\":4.11,\"TND\":3.41,\"TOP\":2.77,\"TRY\":48.94,\"TTD\":9.44,\"TVD\":1.78,\"TWD\":35.68,\"TZS\":2865.8,\"UAH\":48.42,\"UGX\":4039.99,\"USD\":1.17,\"UYU\":46.81,\"UZS\":14195.35,\"VES\":217.65,\"VND\":30829.58,\"VUV\":139.86,\"WST\":3.23,\"XAF\":655.96,\"XCD\":3.17,\"XCG\":2.1,\"XDR\":0.856,\"XOF\":655.96,\"XPF\":119.33,\"YER\":280.77,\"ZAR\":20.23,\"ZMW\":28.01,\"ZWL\":31.24}}', '2025-10-04 15:29:37'),
('GBP', '{\"provider\":\"https:\\/\\/www.exchangerate-api.com\",\"WARNING_UPGRADE_TO_V6\":\"https:\\/\\/www.exchangerate-api.com\\/docs\\/free\",\"terms\":\"https:\\/\\/www.exchangerate-api.com\\/terms\",\"base\":\"GBP\",\"date\":\"2025-10-04\",\"time_last_updated\":1759536002,\"rates\":{\"GBP\":1,\"AED\":4.95,\"AFN\":91.11,\"ALL\":111.06,\"AMD\":516.03,\"ANG\":2.41,\"AOA\":1265.94,\"ARS\":1919.93,\"AUD\":2.04,\"AWG\":2.41,\"AZN\":2.29,\"BAM\":2.24,\"BBD\":2.7,\"BDT\":164,\"BGN\":2.24,\"BHD\":0.507,\"BIF\":3988.8,\"BMD\":1.35,\"BND\":1.74,\"BOB\":9.33,\"BRL\":7.19,\"BSD\":1.35,\"BTN\":119.52,\"BWP\":18.86,\"BYN\":4.13,\"BZD\":2.7,\"CAD\":1.88,\"CDF\":3452.49,\"CHF\":1.07,\"CLP\":1294.9,\"CNY\":9.6,\"COP\":5252.15,\"CRC\":679.04,\"CUP\":32.34,\"CVE\":126.46,\"CZK\":27.83,\"DJF\":239.49,\"DKK\":8.57,\"DOP\":84.11,\"DZD\":174.69,\"EGP\":64.29,\"ERN\":20.21,\"ETB\":193.52,\"EUR\":1.15,\"FJD\":3.03,\"FKP\":1,\"FOK\":8.57,\"GEL\":3.67,\"GGP\":1,\"GHS\":17.06,\"GIP\":1,\"GMD\":98.87,\"GNF\":11696,\"GTQ\":10.33,\"GYD\":281.98,\"HKD\":10.48,\"HNL\":35.35,\"HRK\":8.64,\"HTG\":176.33,\"HUF\":445.79,\"IDR\":22333.8,\"ILS\":4.45,\"IMP\":1,\"INR\":119.52,\"IQD\":1763.29,\"IRR\":57037.41,\"ISK\":162.97,\"JEP\":1,\"JMD\":216.26,\"JOD\":0.955,\"JPY\":198.6,\"KES\":173.96,\"KGS\":117.48,\"KHR\":5405.87,\"KID\":2.04,\"KMF\":564.22,\"KRW\":1893.45,\"KWD\":0.411,\"KYD\":1.12,\"KZT\":737.15,\"LAK\":29307.13,\"LBP\":120606.23,\"LKR\":407.01,\"LRD\":244.6,\"LSL\":23.22,\"LYD\":7.28,\"MAD\":12.27,\"MDL\":22.47,\"MGA\":6000.69,\"MKD\":70.74,\"MMK\":2831.58,\"MNT\":4863.41,\"MOP\":10.8,\"MRU\":53.94,\"MUR\":61.28,\"MVR\":20.8,\"MWK\":2352.14,\"MXN\":24.79,\"MYR\":5.66,\"MZN\":86.09,\"NAD\":23.22,\"NGN\":1963.39,\"NIO\":49.55,\"NOK\":13.41,\"NPR\":191.24,\"NZD\":2.31,\"OMR\":0.518,\"PAB\":1.35,\"PEN\":4.67,\"PGK\":5.7,\"PHP\":78.11,\"PKR\":381.75,\"PLN\":4.88,\"PYG\":9547.68,\"QAR\":4.91,\"RON\":5.84,\"RSD\":134.43,\"RUB\":110.59,\"RWF\":1960.95,\"SAR\":5.05,\"SBD\":11.01,\"SCR\":20.04,\"SDG\":602.41,\"SEK\":12.63,\"SGD\":1.74,\"SHP\":1,\"SLE\":31.45,\"SLL\":31447.96,\"SOS\":770.82,\"SRD\":51.75,\"SSP\":6365.91,\"STN\":28.1,\"SYP\":17714.28,\"SZL\":23.22,\"THB\":43.57,\"TJS\":12.66,\"TMT\":4.71,\"TND\":3.91,\"TOP\":3.17,\"TRY\":56.15,\"TTD\":9.35,\"TVD\":2.04,\"TWD\":40.94,\"TZS\":3286.56,\"UAH\":55.52,\"UGX\":4633.15,\"USD\":1.35,\"UYU\":53.72,\"UZS\":16295.97,\"VES\":249.83,\"VND\":35371.51,\"VUV\":160.41,\"WST\":3.7,\"XAF\":752.29,\"XCD\":3.64,\"XCG\":2.41,\"XDR\":0.982,\"XOF\":752.29,\"XPF\":136.86,\"YER\":322.26,\"ZAR\":23.22,\"ZMW\":32.15,\"ZWL\":35.81}}', '2025-10-04 15:29:03'),
('USD', '{\"provider\":\"https:\\/\\/www.exchangerate-api.com\",\"WARNING_UPGRADE_TO_V6\":\"https:\\/\\/www.exchangerate-api.com\\/docs\\/free\",\"terms\":\"https:\\/\\/www.exchangerate-api.com\\/terms\",\"base\":\"USD\",\"date\":\"2025-10-04\",\"time_last_updated\":1759536002,\"rates\":{\"USD\":1,\"AED\":3.67,\"AFN\":67.62,\"ALL\":82.43,\"AMD\":383.1,\"ANG\":1.79,\"AOA\":919.38,\"ARS\":1424.75,\"AUD\":1.51,\"AWG\":1.79,\"AZN\":1.7,\"BAM\":1.67,\"BBD\":2,\"BDT\":121.68,\"BGN\":1.67,\"BHD\":0.376,\"BIF\":2956.39,\"BMD\":1,\"BND\":1.29,\"BOB\":6.93,\"BRL\":5.34,\"BSD\":1,\"BTN\":88.8,\"BWP\":14.1,\"BYN\":3.12,\"BZD\":2,\"CAD\":1.39,\"CDF\":2641.13,\"CHF\":0.796,\"CLP\":961.89,\"CNY\":7.13,\"COP\":3905.74,\"CRC\":504.03,\"CUP\":24,\"CVE\":93.9,\"CZK\":20.66,\"DJF\":177.72,\"DKK\":6.36,\"DOP\":62.39,\"DZD\":129.65,\"EGP\":47.8,\"ERN\":15,\"ETB\":143.59,\"EUR\":0.852,\"FJD\":2.26,\"FKP\":0.742,\"FOK\":6.36,\"GBP\":0.742,\"GEL\":2.72,\"GGP\":0.742,\"GHS\":12.65,\"GIP\":0.742,\"GMD\":73.42,\"GNF\":8683.63,\"GTQ\":7.67,\"GYD\":209.34,\"HKD\":7.78,\"HNL\":26.25,\"HRK\":6.42,\"HTG\":130.97,\"HUF\":330.92,\"IDR\":16580.72,\"ILS\":3.3,\"IMP\":0.742,\"INR\":88.8,\"IQD\":1309.67,\"IRR\":42212.19,\"ISK\":121.06,\"JEP\":0.742,\"JMD\":160.45,\"JOD\":0.709,\"JPY\":147.4,\"KES\":129.17,\"KGS\":87.27,\"KHR\":4014.31,\"KID\":1.51,\"KMF\":418.96,\"KRW\":1405.76,\"KWD\":0.306,\"KYD\":0.833,\"KZT\":547.37,\"LAK\":21740.93,\"LBP\":89500,\"LKR\":302.39,\"LRD\":181.47,\"LSL\":17.23,\"LYD\":5.41,\"MAD\":9.11,\"MDL\":16.69,\"MGA\":4455.98,\"MKD\":52.49,\"MMK\":2101.42,\"MNT\":3606.78,\"MOP\":8.02,\"MRU\":40.04,\"MUR\":45.51,\"MVR\":15.44,\"MWK\":1744.94,\"MXN\":18.4,\"MYR\":4.21,\"MZN\":63.61,\"NAD\":17.23,\"NGN\":1465.45,\"NIO\":36.78,\"NOK\":9.95,\"NPR\":142.08,\"NZD\":1.71,\"OMR\":0.384,\"PAB\":1,\"PEN\":3.47,\"PGK\":4.24,\"PHP\":57.99,\"PKR\":283.17,\"PLN\":3.63,\"PYG\":7089.2,\"QAR\":3.64,\"RON\":4.34,\"RSD\":99.79,\"RUB\":82.09,\"RWF\":1452.64,\"SAR\":3.75,\"SBD\":8.18,\"SCR\":14.87,\"SDG\":510.9,\"SEK\":9.37,\"SGD\":1.29,\"SHP\":0.742,\"SLE\":23.34,\"SLL\":23337.04,\"SOS\":572.06,\"SRD\":38.38,\"SSP\":4699.74,\"STN\":20.86,\"SYP\":13104.3,\"SZL\":17.23,\"THB\":32.39,\"TJS\":9.4,\"TMT\":3.5,\"TND\":2.9,\"TOP\":2.37,\"TRY\":41.69,\"TTD\":6.76,\"TVD\":1.51,\"TWD\":30.41,\"TZS\":2444.32,\"UAH\":41.25,\"UGX\":3436.04,\"UYU\":39.88,\"UZS\":12095.37,\"VES\":185.4,\"VND\":26245.76,\"VUV\":119.64,\"WST\":2.75,\"XAF\":558.62,\"XCD\":2.7,\"XCG\":1.79,\"XDR\":0.729,\"XOF\":558.62,\"XPF\":101.62,\"YER\":239.23,\"ZAR\":17.23,\"ZMW\":23.88,\"ZWL\":26.66}}', '2025-10-04 15:47:00');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `original_amount` decimal(12,2) NOT NULL,
  `original_currency` varchar(10) NOT NULL,
  `company_amount` decimal(12,2) DEFAULT NULL,
  `company_currency` varchar(10) DEFAULT NULL,
  `exchange_rate` decimal(18,8) DEFAULT NULL,
  `category` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `company_id`, `user_id`, `original_amount`, `original_currency`, `company_amount`, `company_currency`, `exchange_rate`, `category`, `description`, `expense_date`, `receipt_path`, `status`, `created_at`) VALUES
(1, 1, 4, 20.00, 'INR', 20.00, 'INR', 1.00000000, 'Entertainment', 'Max Hours/Week: 20', '2025-10-01', NULL, 'Pending', '2025-10-04 15:28:20'),
(2, 1, 4, 3.00, 'GBP', 358.56, 'INR', 119.52000000, 'Accommodation', '&amp;3 Faculty Dashboard T ——', '2025-10-04', NULL, 'Rejected', '2025-10-04 15:29:03'),
(3, 1, 4, 100.00, 'EUR', 10419.00, 'INR', 104.19000000, 'Office Supplies', '', '2025-10-01', NULL, 'Pending', '2025-10-04 15:29:37'),
(4, 1, 4, 20.00, 'USD', 1776.00, 'INR', 88.80000000, 'Transportation', 'Max Hours/Week: 20', '2025-10-04', NULL, 'Rejected', '2025-10-04 15:47:00'),
(5, 1, 4, 15000.00, 'USD', 1332000.00, 'INR', 88.80000000, 'Office Supplies', 'testing', '2025-10-04', NULL, 'Approved', '2025-10-04 15:52:28'),
(6, 1, 4, 2000.00, 'USD', 177600.00, 'INR', 88.80000000, 'Accommodation', 'testing', '2025-10-04', NULL, 'Approved', '2025-10-04 16:09:53'),
(7, 1, 4, 10000.00, 'USD', 888000.00, 'INR', 88.80000000, 'Accommodation', 'testing', '2025-10-04', NULL, 'Approved', '2025-10-04 16:24:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Manager','Employee','Finance','Director','HR','CFO') DEFAULT 'Employee',
  `manager_id` int(11) DEFAULT NULL,
  `is_manager_approver` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `company_id`, `name`, `email`, `password`, `role`, `manager_id`, `is_manager_approver`, `created_at`) VALUES
(1, 1, 'ayush', 'ayush@gmail.com', '$2y$10$uIqWLyhN9g818C8h1CKYO.t9tvNdbHMPZxXG.q0/xtZN5ILAap6re', 'Admin', NULL, 1, '2025-10-04 15:23:58'),
(2, 1, 'hiren', 'hiren@gmail.com', '$2y$10$1c8Vg71b7SzRsBxl3110Zu3PVXv5yGVwTMbTApPvDqZwsGWVqrCMe', 'Director', NULL, 1, '2025-10-04 15:24:24'),
(3, 1, 'meet', 'meet@gmail.com', '$2y$10$T9CU8K.38WAR8HVDflYHdO4J.5u.B5hHteSkLr.TJNj92rGIrRBIO', 'Manager', NULL, 1, '2025-10-04 15:25:29'),
(4, 1, 'Shukla Pranjal', 'emp@gmail.com', '$2y$10$UyuM5MUaaBskEa6YxJGBtuzALZYseG7Y.QkY4NfP2qRaGW1QNSOkS', 'Employee', 3, 1, '2025-10-04 15:25:49'),
(5, 1, 'dir', 'cfo@gmail.com', '$2y$10$Xx.AOT4DE73G3ce0ryiI2u6RVml9QI79dF20NF.LliRXgkMB5vN1q', 'Director', NULL, 1, '2025-10-04 16:03:18'),
(6, 1, 'hr', 'hr@gmail.com', '$2y$10$rKNXF7Xzz5MMBwMa9GXi2.d1iugSGGT3C8Jt9Q.SZFz92jh.c5vT.', 'HR', NULL, 1, '2025-10-04 16:08:13'),
(7, 1, 'cfo', 'cforeal@gmail.com', '$2y$10$jA88meDSj2fH.Ib75Ym0z.9G9mXOZsUsSRAwSwKUwWRnysMGgWOT2', 'CFO', NULL, 1, '2025-10-04 16:08:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_approvals_expense` (`expense_id`),
  ADD KEY `idx_approvals_approver` (`approver_id`),
  ADD KEY `idx_approvals_status` (`status`);

--
-- Indexes for table `approval_rules`
--
ALTER TABLE `approval_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `specific_approver_id` (`specific_approver_id`);

--
-- Indexes for table `approval_sequences`
--
ALTER TABLE `approval_sequences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approver_user_id` (`approver_user_id`),
  ADD KEY `idx_approval_sequences_company` (`company_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_audit_logs_record` (`table_name`,`record_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD PRIMARY KEY (`base_currency`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_expenses_company` (`company_id`),
  ADD KEY `idx_expenses_user` (`user_id`),
  ADD KEY `idx_expenses_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `idx_users_company` (`company_id`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approvals`
--
ALTER TABLE `approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `approval_rules`
--
ALTER TABLE `approval_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `approval_sequences`
--
ALTER TABLE `approval_sequences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_ibfk_1` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `approval_rules`
--
ALTER TABLE `approval_rules`
  ADD CONSTRAINT `approval_rules_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `approval_rules_ibfk_2` FOREIGN KEY (`specific_approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `approval_sequences`
--
ALTER TABLE `approval_sequences`
  ADD CONSTRAINT `approval_sequences_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `approval_sequences_ibfk_2` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
