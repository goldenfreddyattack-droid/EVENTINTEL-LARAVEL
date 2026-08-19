-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2026 at 12:41 PM
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
-- Database: `copyeventintel`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `theme` varchar(120) DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `event_end_time` time DEFAULT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `venue_name` varchar(150) DEFAULT NULL,
  `venue_status` varchar(50) DEFAULT 'pending',
  `venue_address` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'planning',
  `clothes` varchar(255) DEFAULT NULL,
  `clothes_status` varchar(50) DEFAULT 'pending',
  `catering` varchar(255) DEFAULT NULL,
  `catering_status` varchar(50) DEFAULT 'pending',
  `host` varchar(255) DEFAULT NULL,
  `host_status` varchar(50) DEFAULT 'pending',
  `soundsnlights` varchar(255) DEFAULT NULL,
  `soundsnlights_status` varchar(50) DEFAULT 'pending',
  `photographer` varchar(255) DEFAULT NULL,
  `photographer_status` varchar(50) DEFAULT 'pending',
  `coordinator` varchar(255) DEFAULT NULL,
  `coordinator_package` varchar(255) NOT NULL,
  `coordinator_status` varchar(255) DEFAULT 'pending',
  `coordinator_proposal` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `clothes_note` text DEFAULT NULL,
  `venue_note` text DEFAULT NULL,
  `catering_note` text DEFAULT NULL,
  `host_note` text DEFAULT NULL,
  `s&l_note` text DEFAULT NULL,
  `photographer_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `user_id`, `title`, `event_type`, `theme`, `budget`, `event_date`, `event_time`, `event_end_time`, `guest_count`, `venue_name`, `venue_status`, `venue_address`, `latitude`, `longitude`, `status`, `clothes`, `clothes_status`, `catering`, `catering_status`, `host`, `host_status`, `soundsnlights`, `soundsnlights_status`, `photographer`, `photographer_status`, `coordinator`, `coordinator_package`, `coordinator_status`, `coordinator_proposal`, `payment_method`, `payment_status`, `clothes_note`, `venue_note`, `catering_note`, `host_note`, `s&l_note`, `photographer_note`, `created_at`) VALUES
(1, 2, 'Birthday Event', 'Birthday', NULL, NULL, '2026-06-16', '20:35:00', NULL, 123, NULL, 'pending', NULL, NULL, NULL, 'planning', '', 'pending', '', 'pending', '', 'pending', '', 'pending', '', 'pending', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-03 09:31:50'),
(2, 2, 'Anniversary Event', 'Anniversary', NULL, NULL, '2026-06-25', '12:09:00', NULL, 130, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-03 13:09:31'),
(3, 2, 'Reunion Event', 'Reunion', NULL, NULL, '2026-06-12', '21:18:00', NULL, 130, 'Casa De Alvin', 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-03 13:15:16'),
(4, 2, 'Wedding Event', 'Wedding', NULL, NULL, '2026-06-10', '19:10:00', NULL, 132, 'Casa De Alvin', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 07:10:41'),
(5, 2, 'Birthday Event', 'Birthday', NULL, NULL, '2026-06-18', '19:24:00', NULL, 132, 'Casa De Alvin', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', 'Catering', 'pending', 'Vincent Tolentino', 'Payment Pending', 'RM Lights & Sounds', 'pending', '', 'pending', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 07:22:39'),
(6, 2, 'Birthday Event', 'Birthday', NULL, NULL, '2026-06-18', '18:50:00', NULL, 123, 'Casa De Alvin', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', 'Catering', 'pending', 'Mama Dhel San Antonio', 'pending', 'RM Lights & Sounds', 'accepted', 'Photographer', 'pending', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 07:51:16'),
(7, 2, 'Christening Event', 'Christening', NULL, NULL, '2026-06-10', '19:58:00', NULL, 145, '', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', 'Antonio\'s Catering', 'Paid', '', 'pending', '', 'pending', '', 'pending', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 07:58:23'),
(8, 2, 'Reunion Event', 'Reunion', NULL, NULL, '2026-07-11', '16:03:00', NULL, 122, '', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', '', 'pending', '', 'pending', '', 'pending', 'John Doe', 'Paid', NULL, '', NULL, NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 08:00:33'),
(10, 2, 'Birthday Event', 'Birthday', NULL, NULL, '2026-06-09', '20:20:00', NULL, 134, 'Casa De Alvin', 'declined', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'declined', 'Antonio\'s Catering', 'Payment Pending', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'Payment Pending', 'John Doe', 'accepted', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 08:20:46'),
(11, 2, 'Wedding Event', 'Wedding', NULL, NULL, '2026-06-12', '11:58:00', NULL, 122, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'Paid', 'Antonio\'s Catering', 'declined', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'pending', 'John Doe', 'Payment Pending', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 14:58:28'),
(13, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', '', 'declined', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 15:40:35'),
(16, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', '', 'Paid', 'djakad', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 16:08:32'),
(17, 2, 'Gender Reveal Event', 'Gender Reveal', NULL, NULL, '2026-06-11', '16:28:00', NULL, 122, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'pending', 'Antonio\'s Catering', 'accepted', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'pending', 'John Doe', 'pending', NULL, '', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-05 05:30:52'),
(18, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', '', 'Paid', 'This is the place and etc', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-05 05:32:18'),
(36, 2, 'Birthday Event', 'Birthday', NULL, NULL, '2026-08-31', '22:00:00', NULL, 150, 'La Tehillah Private Resort and Events Place', 'pending', NULL, NULL, NULL, 'planning', 'La Tehillah Private Resort and Events Place', 'pending', '', 'pending', 'La Tehillah Private Resort and Events Place', 'pending', '', 'pending', 'La Tehillah Private Resort and Events Place', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 12:00:54'),
(37, 2, 'Anniversary Event', 'Anniversary', NULL, NULL, '2026-07-30', '05:07:00', NULL, 120, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'pending', 'ADM (Asuncion de Grande) Catering', 'pending', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'pending', 'A&A Self-Portrait Studio', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 15:08:32'),
(38, 2, 'Christening Event', 'Christening', NULL, NULL, '2026-08-08', '17:22:00', NULL, 130, 'LIOS Resort and Events Place', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'pending', 'LIOS Resort and Events Place', 'pending', 'Vincent Tolentino', 'declined', 'LIOS Resort and Events Place', 'pending', 'LIOS Resort and Events Place', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, 'another event schedule on the day', NULL, NULL, '2026-07-28 06:22:46'),
(39, 2, 'Gender Reveal Event', 'Gender Reveal', NULL, NULL, '2026-08-08', '21:32:00', NULL, 130, 'LIOS Resort and Events Place', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'declined', 'Taste Event Planner Designs and Catering Services', 'pending', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'pending', 'John Doe', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', 'clothing not available', NULL, NULL, NULL, NULL, NULL, '2026-07-28 07:33:50'),
(40, 2, 'Wedding Event', 'Wedding', NULL, NULL, '2026-08-08', '21:38:00', NULL, 130, 'La Tehillah Private Resort and Events Place', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'pending', 'Antonio\'s Catering', 'declined', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'pending', 'John Doe', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', 'The reason is we don\'t do that type of food in here', NULL, NULL, NULL, NULL, NULL, '2026-07-28 07:40:54'),
(41, 2, 'Anniversary Event', 'Anniversary', NULL, NULL, '2026-07-28', '20:55:00', NULL, 130, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', 'Casa de Alvin', 'pending', 'Antonio\'s Catering', 'declined', 'Vincent Tolentino', 'pending', '', 'pending', '', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, 'over naman sa aga teh', NULL, NULL, NULL, '2026-07-28 07:56:42'),
(42, 2, 'Wedding Event', 'Wedding', NULL, NULL, '2026-11-28', '10:10:00', NULL, 130, 'LIOS Resort and Events Place', 'Payment Pending', NULL, NULL, NULL, 'planning', 'LIOS Resort and Events Place', 'pending', '', 'pending', 'Vincent Tolentino', 'pending', 'LIOS Resort and Events Place', 'pending', 'John Doe', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, 'not available on this date', NULL, NULL, NULL, '2026-08-01 14:11:32'),
(43, 2, 'Birthday Event', 'Birthday', NULL, NULL, '2026-08-15', '22:11:00', NULL, 15, 'La Tehillah Private Resort and Events Place', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'pending', 'Taste Event Planner Designs and Catering Services', 'pending', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'pending', 'A&A Self-Portrait Studio', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-01 14:12:33'),
(44, 2, 'Birthday Event', 'Birthday', 'Cartoon Theme', 50000.00, '2026-08-17', '18:12:00', NULL, 100, 'LIOS Resort and Events Place', 'Paid', NULL, NULL, NULL, 'planning', '', 'pending', '', 'pending', 'LIOS Resort and Events Place', 'pending', '', 'pending', '', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-02 07:10:40'),
(45, 2, 'Debut Event', 'Debut', 'Vintage Debut', 80000.00, '2026-09-03', '16:24:00', NULL, 100, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', 'ADM (Asuncion de Grande) Catering', 'pending', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'pending', 'John Doe', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-02 08:19:49'),
(46, 2, 'Birthday Event', 'Birthday', 'Cartoon Theme', 45000.00, '2026-08-13', '18:17:00', NULL, 100, 'LIOS Resort and Events Place', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', '', 'pending', '', 'pending', '', 'pending', '', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-02 10:14:44'),
(47, 2, 'Birthday Event', 'Birthday', 'Cartoon Theme', 20000.00, '2026-08-19', '23:15:00', NULL, 100, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', 'ADM (Asuncion de Grande) Catering', 'pending', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'pending', 'John Doe', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-02 10:15:31'),
(48, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', '', 'Paid', 'safasfsfsafasf', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 14:07:26'),
(49, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', 'Premium Package', 'Payment Pending', 'SIGE BOI', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 15:36:27');

-- --------------------------------------------------------

--
-- Table structure for table `event_services`
--

CREATE TABLE `event_services` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `service_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_services`
--

INSERT INTO `event_services` (`id`, `event_id`, `service_name`, `created_at`) VALUES
(1, 1, 'venue', '2026-06-03 09:31:50'),
(2, 1, 'clothes', '2026-06-03 09:31:50'),
(3, 1, 'catering', '2026-06-03 09:31:50'),
(4, 1, 'host', '2026-06-03 09:31:50'),
(5, 1, 'sounds_lights', '2026-06-03 09:31:50'),
(6, 1, 'photographer', '2026-06-03 09:31:50'),
(7, 2, 'venue', '2026-06-03 13:09:31'),
(8, 2, 'clothes', '2026-06-03 13:09:31'),
(9, 2, 'catering', '2026-06-03 13:09:31'),
(10, 2, 'host', '2026-06-03 13:09:31'),
(11, 2, 'sounds_lights', '2026-06-03 13:09:31'),
(12, 2, 'photographer', '2026-06-03 13:09:31'),
(13, 3, 'venue', '2026-06-03 13:15:16'),
(14, 3, 'clothes', '2026-06-03 13:15:16'),
(15, 3, 'catering', '2026-06-03 13:15:16'),
(16, 3, 'host', '2026-06-03 13:15:16'),
(17, 3, 'sounds_lights', '2026-06-03 13:15:16'),
(18, 3, 'photographer', '2026-06-03 13:15:16'),
(19, 4, 'venue', '2026-06-04 07:10:41'),
(20, 4, 'clothes', '2026-06-04 07:10:41'),
(21, 4, 'catering', '2026-06-04 07:10:41'),
(22, 4, 'host', '2026-06-04 07:10:41'),
(23, 4, 'sounds_lights', '2026-06-04 07:10:41'),
(24, 4, 'photographer', '2026-06-04 07:10:41'),
(25, 5, 'venue', '2026-06-04 07:22:39'),
(26, 5, 'clothes', '2026-06-04 07:22:39'),
(27, 5, 'catering', '2026-06-04 07:22:39'),
(28, 5, 'host', '2026-06-04 07:22:39'),
(29, 5, 'sounds_lights', '2026-06-04 07:22:39'),
(30, 5, 'photographer', '2026-06-04 07:22:39'),
(31, 6, 'venue', '2026-06-04 07:51:16'),
(32, 6, 'clothes', '2026-06-04 07:51:16'),
(33, 6, 'catering', '2026-06-04 07:51:16'),
(34, 6, 'host', '2026-06-04 07:51:16'),
(35, 6, 'sounds_lights', '2026-06-04 07:51:16'),
(36, 6, 'photographer', '2026-06-04 07:51:16'),
(37, 7, 'clothes', '2026-06-04 07:58:23'),
(38, 7, 'catering', '2026-06-04 07:58:23'),
(39, 8, 'photographer', '2026-06-04 08:00:33'),
(40, 9, 'venue', '2026-06-04 08:15:25'),
(41, 9, 'clothes', '2026-06-04 08:15:25'),
(42, 9, 'catering', '2026-06-04 08:15:25'),
(43, 9, 'host', '2026-06-04 08:15:25'),
(44, 9, 'sounds_lights', '2026-06-04 08:15:25'),
(45, 9, 'photographer', '2026-06-04 08:15:25'),
(46, 10, 'venue', '2026-06-04 08:20:46'),
(47, 10, 'clothes', '2026-06-04 08:20:46'),
(48, 10, 'catering', '2026-06-04 08:20:46'),
(49, 10, 'host', '2026-06-04 08:20:46'),
(50, 10, 'sounds_lights', '2026-06-04 08:20:46'),
(51, 10, 'photographer', '2026-06-04 08:20:46'),
(52, 11, 'venue', '2026-06-04 14:58:28'),
(53, 11, 'clothes', '2026-06-04 14:58:28'),
(54, 11, 'catering', '2026-06-04 14:58:28'),
(55, 11, 'host', '2026-06-04 14:58:28'),
(56, 11, 'sounds_lights', '2026-06-04 14:58:28'),
(57, 11, 'photographer', '2026-06-04 14:58:28'),
(58, 14, 'venue', '2026-06-04 15:42:12'),
(59, 14, 'clothes', '2026-06-04 15:42:12'),
(60, 14, 'catering', '2026-06-04 15:42:12'),
(61, 14, 'host', '2026-06-04 15:42:12'),
(62, 14, 'sounds_lights', '2026-06-04 15:42:12'),
(63, 14, 'photographer', '2026-06-04 15:42:12'),
(64, 15, 'venue', '2026-06-04 16:08:25'),
(65, 15, 'clothes', '2026-06-04 16:08:25'),
(66, 15, 'catering', '2026-06-04 16:08:25'),
(67, 15, 'host', '2026-06-04 16:08:25'),
(68, 15, 'sounds_lights', '2026-06-04 16:08:25'),
(69, 15, 'photographer', '2026-06-04 16:08:25'),
(70, 17, 'venue', '2026-06-05 05:30:52'),
(71, 17, 'clothes', '2026-06-05 05:30:52'),
(72, 17, 'catering', '2026-06-05 05:30:52'),
(73, 17, 'host', '2026-06-05 05:30:52'),
(74, 17, 'sounds_lights', '2026-06-05 05:30:52'),
(75, 17, 'photographer', '2026-06-05 05:30:52'),
(76, 19, 'venue', '2026-06-20 03:31:41'),
(77, 19, 'clothes', '2026-06-20 03:31:41'),
(78, 19, 'catering', '2026-06-20 03:31:41'),
(79, 19, 'host', '2026-06-20 03:31:41'),
(80, 19, 'sounds_lights', '2026-06-20 03:31:41'),
(81, 19, 'photographer', '2026-06-20 03:31:41'),
(82, 20, 'venue', '2026-07-12 13:52:48'),
(83, 20, 'clothes', '2026-07-12 13:52:48'),
(84, 20, 'catering', '2026-07-12 13:52:48'),
(85, 20, 'host', '2026-07-12 13:52:48'),
(86, 20, 'sounds_lights', '2026-07-12 13:52:48'),
(87, 20, 'photographer', '2026-07-12 13:52:48'),
(88, 21, 'venue', '2026-07-19 09:55:37'),
(89, 21, 'catering', '2026-07-19 09:55:37'),
(90, 21, 'host', '2026-07-19 09:55:37'),
(91, 21, 'sounds_lights', '2026-07-19 09:55:37'),
(92, 22, 'venue', '2026-07-19 09:56:53'),
(93, 22, 'catering', '2026-07-19 09:56:53'),
(94, 22, 'photographer', '2026-07-19 09:56:53'),
(95, 23, 'venue', '2026-07-19 10:01:18'),
(96, 23, 'clothes', '2026-07-19 10:01:18'),
(97, 23, 'host', '2026-07-19 10:01:18'),
(98, 23, 'photographer', '2026-07-19 10:01:18'),
(99, 24, 'venue', '2026-07-19 10:02:20'),
(100, 24, 'clothes', '2026-07-19 10:02:20'),
(101, 24, 'sounds_lights', '2026-07-19 10:02:20'),
(102, 24, 'photographer', '2026-07-19 10:02:20'),
(103, 25, 'venue', '2026-07-19 10:06:11'),
(104, 25, 'clothes', '2026-07-19 10:06:11'),
(105, 25, 'catering', '2026-07-19 10:06:11'),
(106, 25, 'host', '2026-07-19 10:06:11'),
(107, 25, 'sounds_lights', '2026-07-19 10:06:11'),
(108, 25, 'photographer', '2026-07-19 10:06:11'),
(109, 26, 'venue', '2026-07-19 10:53:16'),
(110, 26, 'clothes', '2026-07-19 10:53:16'),
(111, 26, 'catering', '2026-07-19 10:53:16'),
(112, 26, 'host', '2026-07-19 10:53:16'),
(113, 26, 'sounds_lights', '2026-07-19 10:53:16'),
(114, 26, 'photographer', '2026-07-19 10:53:16'),
(115, 27, 'venue', '2026-07-19 10:56:21'),
(116, 27, 'clothes', '2026-07-19 10:56:21'),
(117, 27, 'catering', '2026-07-19 10:56:21'),
(118, 28, 'venue', '2026-07-19 11:01:35'),
(119, 28, 'clothes', '2026-07-19 11:01:35'),
(120, 28, 'host', '2026-07-19 11:01:35'),
(121, 28, 'photographer', '2026-07-19 11:01:35'),
(122, 29, 'venue', '2026-07-19 11:02:21'),
(123, 30, 'venue', '2026-07-19 11:04:49'),
(124, 30, 'sounds_lights', '2026-07-19 11:04:49'),
(125, 30, 'photographer', '2026-07-19 11:04:49'),
(126, 31, 'venue', '2026-07-19 11:07:42'),
(127, 31, 'clothes', '2026-07-19 11:07:42'),
(128, 31, 'host', '2026-07-19 11:07:42'),
(129, 32, 'venue', '2026-07-19 11:08:43'),
(130, 33, 'venue', '2026-07-19 11:11:46'),
(131, 34, 'venue', '2026-07-19 11:12:41'),
(132, 34, 'clothes', '2026-07-19 11:12:41'),
(133, 34, 'catering', '2026-07-19 11:12:41'),
(134, 34, 'host', '2026-07-19 11:12:41'),
(135, 34, 'sounds_lights', '2026-07-19 11:12:41'),
(136, 34, 'photographer', '2026-07-19 11:12:41'),
(137, 35, 'venue', '2026-07-19 11:14:26'),
(138, 36, 'venue', '2026-07-19 12:00:54'),
(139, 36, 'clothes', '2026-07-19 12:00:54'),
(140, 36, 'host', '2026-07-19 12:00:54'),
(141, 36, 'photographer', '2026-07-19 12:00:54'),
(142, 37, 'venue', '2026-07-19 15:08:32'),
(143, 37, 'clothes', '2026-07-19 15:08:32'),
(144, 37, 'catering', '2026-07-19 15:08:32'),
(145, 37, 'host', '2026-07-19 15:08:32'),
(146, 37, 'sounds_lights', '2026-07-19 15:08:32'),
(147, 37, 'photographer', '2026-07-19 15:08:32'),
(148, 38, 'venue', '2026-07-28 06:22:47'),
(149, 38, 'clothes', '2026-07-28 06:22:47'),
(150, 38, 'catering', '2026-07-28 06:22:47'),
(151, 38, 'host', '2026-07-28 06:22:47'),
(152, 38, 'sounds_lights', '2026-07-28 06:22:47'),
(153, 38, 'photographer', '2026-07-28 06:22:47'),
(154, 39, 'venue', '2026-07-28 07:33:50'),
(155, 39, 'clothes', '2026-07-28 07:33:50'),
(156, 39, 'catering', '2026-07-28 07:33:50'),
(157, 39, 'host', '2026-07-28 07:33:50'),
(158, 39, 'sounds_lights', '2026-07-28 07:33:50'),
(159, 39, 'photographer', '2026-07-28 07:33:50'),
(160, 40, 'venue', '2026-07-28 07:40:54'),
(161, 40, 'clothes', '2026-07-28 07:40:54'),
(162, 40, 'catering', '2026-07-28 07:40:54'),
(163, 40, 'host', '2026-07-28 07:40:54'),
(164, 40, 'sounds_lights', '2026-07-28 07:40:54'),
(165, 40, 'photographer', '2026-07-28 07:40:54'),
(166, 41, 'venue', '2026-07-28 07:56:42'),
(167, 41, 'clothes', '2026-07-28 07:56:42'),
(168, 41, 'catering', '2026-07-28 07:56:42'),
(169, 41, 'host', '2026-07-28 07:56:42'),
(170, 42, 'venue', '2026-08-01 14:11:32'),
(171, 42, 'clothes', '2026-08-01 14:11:32'),
(172, 42, 'catering', '2026-08-01 14:11:32'),
(173, 42, 'host', '2026-08-01 14:11:32'),
(174, 42, 'sounds_lights', '2026-08-01 14:11:32'),
(175, 42, 'photographer', '2026-08-01 14:11:32'),
(176, 43, 'venue', '2026-08-01 14:12:33'),
(177, 43, 'clothes', '2026-08-01 14:12:33'),
(178, 43, 'catering', '2026-08-01 14:12:33'),
(179, 43, 'host', '2026-08-01 14:12:33'),
(180, 43, 'sounds_lights', '2026-08-01 14:12:33'),
(181, 43, 'photographer', '2026-08-01 14:12:33'),
(182, 44, 'venue', '2026-08-02 07:10:40'),
(183, 44, 'host', '2026-08-02 07:10:40'),
(184, 45, 'venue', '2026-08-02 08:19:49'),
(185, 45, 'clothes', '2026-08-02 08:19:49'),
(186, 45, 'catering', '2026-08-02 08:19:49'),
(187, 45, 'host', '2026-08-02 08:19:49'),
(188, 45, 'sounds_lights', '2026-08-02 08:19:49'),
(189, 45, 'photographer', '2026-08-02 08:19:49'),
(190, 46, 'venue', '2026-08-02 10:14:44'),
(191, 47, 'venue', '2026-08-02 10:15:31'),
(192, 47, 'catering', '2026-08-02 10:15:31'),
(193, 47, 'host', '2026-08-02 10:15:31'),
(194, 47, 'sounds_lights', '2026-08-02 10:15:31'),
(195, 47, 'photographer', '2026-08-02 10:15:31');

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `guest_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `qr_code` varchar(100) DEFAULT NULL,
  `rsvp_status` varchar(50) DEFAULT 'pending',
  `attended` tinyint(1) DEFAULT 0,
  `scanned_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invitations`
--

CREATE TABLE `invitations` (
  `invitation_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `theme_color` varchar(20) DEFAULT NULL,
  `font_style` varchar(80) DEFAULT 'Segoe UI',
  `button_text` varchar(100) DEFAULT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invitations`
--

INSERT INTO `invitations` (`invitation_id`, `event_id`, `title`, `message`, `theme_color`, `font_style`, `button_text`, `background_image`, `created_at`) VALUES
(1, 1, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-03 09:31:50'),
(2, 2, 'You\'re Invited to Anniversary Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-03 13:09:31'),
(3, 3, 'You\'re Invited to Reunion Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-03 13:15:16'),
(4, 4, 'You\'re Invited to Wedding Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-04 07:10:41'),
(5, 5, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-04 07:22:39'),
(6, 6, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-04 07:51:16'),
(7, 7, 'You\'re Invited to Christening Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-04 07:58:23'),
(8, 8, 'You\'re Invited to Reunion Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-04 08:00:33'),
(9, 9, 'You\'re Invited to Christening Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-04 08:15:25'),
(10, 10, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-04 08:20:46'),
(11, 11, 'You\'re Invited to Wedding Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-04 14:58:28'),
(12, 14, 'You\'re Invited to Racing Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-04 15:42:12'),
(13, 15, 'You\'re Invited to Class Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-04 16:08:25'),
(14, 17, 'You\'re Invited to Gender Reveal Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-05 05:30:52'),
(15, 19, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-06-20 03:31:41'),
(16, 20, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-12 13:52:48'),
(17, 21, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 09:55:37'),
(18, 22, 'You\'re Invited to Gender Reveal Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 09:56:53'),
(19, 23, 'You\'re Invited to Anniversary Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 10:01:18'),
(20, 24, 'You\'re Invited to Reunion Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 10:02:20'),
(21, 25, 'You\'re Invited to Christening Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 10:06:11'),
(22, 26, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 10:53:16'),
(23, 27, 'You\'re Invited to Anniversary Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 10:56:21'),
(24, 28, 'You\'re Invited to Anniversary Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 11:01:35'),
(25, 29, 'You\'re Invited to Gender Reveal Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 11:02:21'),
(26, 30, 'You\'re Invited to Anniversary Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 11:04:49'),
(27, 31, 'You\'re Invited to Party Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 11:07:42'),
(28, 32, 'You\'re Invited to Gender Reveal Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 11:08:43'),
(29, 33, 'You\'re Invited to Class Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 11:11:46'),
(30, 34, 'You\'re Invited to Christening Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 11:12:41'),
(31, 35, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 11:14:26'),
(32, 36, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 12:00:54'),
(33, 37, 'You\'re Invited to Anniversary Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-19 15:08:32'),
(34, 38, 'You\'re Invited to Christening Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-28 06:22:47'),
(35, 39, 'You\'re Invited to Gender Reveal Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-28 07:33:50'),
(36, 40, 'You\'re Invited to Wedding Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-28 07:40:54'),
(37, 41, 'You\'re Invited to Anniversary Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-07-28 07:56:42'),
(38, 42, 'You\'re Invited to Wedding Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-08-01 14:11:32'),
(39, 43, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-08-01 14:12:33'),
(40, 44, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-08-02 07:10:40'),
(41, 45, 'You\'re Invited to Debut Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-08-02 08:19:49'),
(42, 46, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-08-02 10:14:44'),
(43, 47, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-08-02 10:15:31');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `event_id`, `sender_id`, `recipient_id`, `body`, `created_at`) VALUES
(1, 42, 2, 11, 'HELLO PO, AVAILABLE PO BA THAT DAY', '2026-08-02 10:42:36'),
(2, 42, 11, 2, 'yes po', '2026-08-02 10:43:07'),
(3, 48, 4, 2, 'Is you available', '2026-08-03 14:10:40'),
(4, 48, 2, 4, 'OFC', '2026-08-03 14:12:30');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `service_name` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT 'cash',
  `paid_by` int(11) NOT NULL,
  `paid_to` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Payment Pending',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `event_id`, `service_type`, `service_name`, `amount`, `payment_method`, `paid_by`, `paid_to`, `status`, `note`, `created_at`) VALUES
(1, 18, 'coordinator', 'Vincent Tolentino', 0.00, 'online', 2, 0, 'Payment Pending', 'Client initiated payment via Online', '2026-08-03 14:42:15'),
(2, 18, 'coordinator', 'Vincent Tolentino', 0.00, 'online', 2, 0, 'Payment Pending', 'Client initiated payment via Online', '2026-08-03 14:42:23'),
(3, 48, 'coordinator', 'Vincent Tolentino', 0.00, 'online', 2, 0, 'Payment Pending', 'Client initiated payment via Online', '2026-08-03 14:42:32'),
(4, 16, 'coordinator', 'Vincent Tolentino', 0.00, 'cash', 2, 0, 'Payment Pending', 'Client initiated payment via Cash', '2026-08-03 14:57:35'),
(5, 16, 'coordinator', 'Vincent Tolentino', 0.00, 'online', 2, 0, 'Payment Pending', 'Client initiated payment via Online', '2026-08-03 14:58:28'),
(6, 16, 'coordinator', 'Vincent Tolentino', 0.00, 'cash', 2, 0, 'Payment Pending', 'Client initiated payment via Cash', '2026-08-03 14:58:33'),
(7, 16, 'coordinator', 'Vincent Tolentino', 0.00, 'cash', 2, 0, 'Payment Pending', 'Client initiated payment via Cash', '2026-08-03 14:58:48'),
(8, 49, 'coordinator', 'Vincent Tolentino', 0.00, 'cash', 2, 0, 'Payment Pending', 'Client initiated payment via Cash', '2026-08-03 15:45:31');

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE `post_comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `like_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_services`
--

CREATE TABLE `supplier_services` (
  `service_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `style` varchar(150) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 5.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_services`
--

INSERT INTO `supplier_services` (`service_id`, `user_id`, `category`, `style`, `name`, `description`, `price`, `capacity`, `address`, `latitude`, `longitude`, `rating`, `created_at`) VALUES
(3, 3, 'Host', NULL, 'Vincent Tolentino', 'Experienced MC for formal and casual events', 7000.00, NULL, 'Apalit, Pampanga', 14.9500000, 120.7650000, 4.70, '2026-06-03 09:28:20'),
(4, 3, 'Photographer', NULL, 'John Doe', 'Photo coverage and edited photos', 12000.00, NULL, 'Apalit, Pampanga', 14.9510000, 120.7680000, 4.80, '2026-06-03 09:28:20'),
(5, 15, 'Sounds & Lights', NULL, 'RM Lights & Sounds', 'Audio system, microphones, lighting rig', 15000.00, NULL, 'Apalit, Pampanga', 14.9540000, 120.7660000, 4.60, '2026-06-03 09:28:20'),
(10, 6, 'Venue', NULL, 'Casa de Consuelo Private Resort and Events Place', 'Private resort and events place with in-house catering services, ideal for weddings, birthdays and family celebrations.', 18000.00, 220, 'Purok 1, Sto. Rosario Tabuyuc, Apalit, Pampanga', NULL, NULL, 4.70, '2026-06-03 09:28:20'),
(11, 7, 'Venue', NULL, 'La Tehillah Private Resort and Events Place', 'Resort and events venue offering all-in packages with accommodations, ideal for weddings and large celebrations.', 19000.00, 200, '92 Centro St., Brgy. Balucuc, Apalit, Pampanga', NULL, NULL, 4.60, '2026-06-03 09:28:20'),
(12, 8, 'Catering', NULL, 'ADM (Asuncion de Grande) Catering', 'Family-owned catering business serving Apalit since 1994, offering catering and styling services for weddings, debuts, birthdays and corporate events.', 16000.00, NULL, 'Apalit, Pampanga', NULL, NULL, 4.90, '2026-06-03 09:28:20'),
(13, 9, 'Catering', NULL, 'Taste Event Planner Designs and Catering Services', 'Full event coordination, styling and catering services based in Sulipan, Apalit, known for elegant table setups and reception design.', 17500.00, NULL, 'Sulipan, Apalit, Pampanga', NULL, NULL, 4.80, '2026-06-03 09:28:20'),
(14, 10, 'Photographer', NULL, 'A&A Self-Portrait Studio', 'DTI and BIR registered photography studio in Apalit offering event photo coverage and self-photo studio sessions.', 6000.00, NULL, '2nd Floor, DMD Blue Arcade Bldg., San Vicente, Apalit, Pampanga', NULL, NULL, 4.50, '2026-06-03 09:28:20'),
(15, 11, 'Venue', 'Resort', 'LIOS Resort and Events Place', 'Beautiful private resort and events place perfect for weddings, birthdays, and special occasions.', 20000.00, 250, '#300 Danga, Colgante, Apalit, Pampanga', NULL, NULL, 4.80, '2026-08-02 06:59:48'),
(16, 12, 'Venue', 'Resto', 'Balai Manlapaz Resto', 'A cozy resto and events place in Manlapaz, ideal for intimate gatherings and celebrations.', 15000.00, 150, 'Manlapaz, Apalit, Pampanga', NULL, NULL, 4.60, '2026-08-02 06:59:48'),
(17, 16, 'Sounds & Lights', NULL, 'J\'s Audio Lights And Sounds', 'Professional sounds and lights services deliver complete audio-visual production for events. They provide crystal-clear sound systems, dynamic stage lighting, and expert technical operators to turn ordinary venues into engaging, high-energy experiences for weddings, concerts, and corporate functions.', 14000.00, NULL, 'XQ24+266, Apalit, Pampanga', NULL, NULL, 5.00, '2026-08-06 13:21:31'),
(18, 17, 'Host', NULL, 'Mama Dhel San Antonio', 'provides professional on-stage engagement, program management, and guest coordination to ensure live gatherings, corporate seminars, and social celebrations run smoothly, keep audiences entertained, and maintain a polished, welcoming atmosphere from start to finish.', 18000.00, NULL, NULL, NULL, NULL, 5.00, '2026-08-06 13:23:05'),
(19, 18, 'Clothing', NULL, 'FC Rental Gown Apalit', '\"Welcome to FC Rental Gown, your ultimate shared wardrobe for every occasion. We offer a curated collection of premium clothing rentals, from striking event wear to chic daily styles. Experience the joy of wearing high-end fashion without the heavy price tag or storage hassle. Simply choose your look, enjoy your moment, and return it—we handle all the dry cleaning.\"', 6000.00, NULL, NULL, NULL, NULL, 5.00, '2026-08-06 13:25:04'),
(20, 19, 'Clothing', NULL, 'M&M Gowns', 'M&M Gown offers a premier, eco-conscious formalwear rental experience, providing access to luxury gowns and haute couture dresses at a fraction of the retail cost. Designed for galas, weddings, red carpet events, and special occasions, our curated collection features contemporary designer gowns, vintage classics, and bespoke bridal wear.', 5000.00, NULL, '246 Danga, Apalit, Pampanga', NULL, NULL, 5.00, '2026-08-12 04:31:31'),
(21, 20, 'Photographer', NULL, 'Vision Photography', 'Luminary Lens Photography is a boutique photography studio dedicated to capturing life’s most meaningful moments with timeless artistry and emotional depth. Specializing in high-end portraits, editorial fashion, weddings, and special events, we blend candid storytelling with refined aesthetic direction to turn fleeting interactions into cherished visual art.', 7000.00, NULL, 'Andal Reaidence, 316 Purok uno, Sampaloc, Apalit, 2016 Pampanga', NULL, NULL, 5.00, '2026-08-12 04:34:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','supplier','coordinator','admin') DEFAULT 'client',
  `status` enum('approved','pending','rejected') DEFAULT 'approved',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `middle_initial` varchar(10) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `business_name` varchar(150) DEFAULT NULL,
  `business_address` text DEFAULT NULL,
  `valid_id` varchar(255) DEFAULT NULL,
  `business_permit` varchar(255) DEFAULT NULL,
  `face_capture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `full_name`, `email`, `password`, `role`, `status`, `first_name`, `last_name`, `middle_initial`, `age`, `gender`, `phone`, `province`, `municipality`, `barangay`, `postal_code`, `business_name`, `business_address`, `valid_id`, `business_permit`, `face_capture`, `created_at`) VALUES
(1, 'admin', 'Admin User', 'admin@test.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'admin', 'approved', 'Admin', 'User', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-03 09:28:20'),
(2, 'client', 'Client User', 'client@test.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'client', 'approved', 'Client', 'User', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-03 09:28:20'),
(3, 'supplier', 'Supplier User', 'supplier@test.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', 'approved', 'Supplier', 'User', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Events Supplier Inc', 'Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(4, 'coordinator', 'Vincent Tolentino', 'coord@test.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'coordinator', 'approved', 'Vincent', 'Tolentino', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Apalit Event Coordination', 'Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(6, 'casadeconsuelo', 'Casa de Consuelo Private Resort', 'casadeconsuelo.events@gmail.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Casa de Consuelo Private Resort and Events Place', 'Purok 1, Sto. Rosario Tabuyuc, Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(7, 'latehillah_resort', 'La Tehillah Private Resort', 'latehillahresort@gmail.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'La Tehillah Private Resort and Events Place', '92 Centro St., Brgy. Balucuc, Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(8, 'admcatering', 'ADM Catering Services', 'info@admcatering.ph', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ADM (Asuncion de Grande) Catering', 'Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(9, 'tasteevents', 'Taste Event Planner Designs and Catering', 'taste.sweetbitebyyhang@gmail.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Taste Event Planner Designs and Catering Services', 'Sulipan, Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(10, 'aaphotography', 'A&A Photography Services', 'aamirrorphotobooth@gmail.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'A&A Self-Portrait Studio', '2nd Floor, DMD Blue Arcade Bldg., San Vicente, Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(11, 'lios_resort', 'LIOS Resort and Events Place', 'liosresort@gmail.com', '$2y$10$ndc5jO9LDYUi4sNXe6R58eKFSXl.7iSYN84KuXa9P35F5HujA2jyG', 'supplier', 'approved', NULL, NULL, NULL, NULL, NULL, '09171234567', 'Pampanga', 'Apalit', NULL, NULL, 'LIOS Resort and Events Place', '#300 Danga, Colgante, Apalit, Pampanga', NULL, NULL, NULL, '2026-08-02 06:59:48'),
(12, 'balai_manlapaz', 'Balai Manlapaz Resto', 'Balaimanlapaz@gmail.com', '$2y$10$ndc5jO9LDYUi4sNXe6R58eKFSXl.7iSYN84KuXa9P35F5HujA2jyG', 'supplier', 'approved', NULL, NULL, NULL, NULL, NULL, '09179876543', 'Pampanga', 'Apalit', NULL, NULL, 'Balai Manlapaz Resto', 'Manlapaz, Apalit, Pampanga', NULL, NULL, NULL, '2026-08-02 06:59:48');

-- --------------------------------------------------------

--
-- Table structure for table `user_posts`
--

CREATE TABLE `user_posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `likes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `event_services`
--
ALTER TABLE `event_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`guest_id`);

--
-- Indexes for table `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`invitation_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_sender_id` (`sender_id`),
  ADD KEY `idx_recipient_id` (`recipient_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_paid_by` (`paid_by`),
  ADD KEY `idx_paid_to` (`paid_to`);

--
-- Indexes for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_like` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- Indexes for table `supplier_services`
--
ALTER TABLE `supplier_services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_posts`
--
ALTER TABLE `user_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `event_services`
--
ALTER TABLE `event_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `guest_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invitations`
--
ALTER TABLE `invitations`
  MODIFY `invitation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_services`
--
ALTER TABLE `supplier_services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_posts`
--
ALTER TABLE `user_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD CONSTRAINT `post_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `user_posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `user_posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_posts`
--
ALTER TABLE `user_posts`
  ADD CONSTRAINT `user_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
