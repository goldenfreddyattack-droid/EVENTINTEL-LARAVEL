-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2026 at 12:39 PM
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
-- Database: `eventintel`
--

-- --------------------------------------------------------

--
-- Table structure for table `3a_tbl`
--

CREATE TABLE `3a_tbl` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `mname` varchar(255) NOT NULL,
  `add` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coordinator_packages`
--

CREATE TABLE `coordinator_packages` (
  `package_id` int(11) NOT NULL,
  `coordinator_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `inclusions` text DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coordinator_reviews`
--

CREATE TABLE `coordinator_reviews` (
  `review_id` int(11) NOT NULL,
  `coordinator_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_event_requests`
--

CREATE TABLE `custom_event_requests` (
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `coordinator_id` int(10) UNSIGNED NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_date` date NOT NULL,
  `venue_preference` varchar(255) DEFAULT NULL,
  `guest_count` int(10) UNSIGNED DEFAULT NULL,
  `theme` varchar(120) DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT NULL,
  `required_services` text DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_event_requests`
--

INSERT INTO `custom_event_requests` (`request_id`, `event_id`, `client_id`, `coordinator_id`, `event_type`, `event_date`, `venue_preference`, `guest_count`, `theme`, `budget`, `required_services`, `special_requests`, `additional_notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 50, 2, 4, 'Wedding', '2026-12-23', 'Resort', 120, 'Beach', 100000.00, 'Catering', 'sadasdfas', 'saaaggsfa', 'pending', NULL, NULL),
(2, 51, 2, 4, 'Wedding', '2026-12-17', 'Resort', 120, 'Beach', 100000.00, 'Catering', 'sdasd', 'afsfasfas', 'pending', NULL, NULL);

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
(10, 2, 'Birthday Event', 'Birthday', NULL, NULL, '2026-06-09', '20:20:00', NULL, 134, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', 'Antonio\'s Catering', 'Payment Pending', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'Payment Pending', 'John Doe', 'accepted', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 08:20:46'),
(11, 2, 'Wedding Event', 'Wedding', NULL, NULL, '2026-06-12', '11:58:00', NULL, 122, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'Paid', NULL, 'pending', 'Vincent Tolentino', 'declined', 'RM Lights & Sounds', 'pending', 'John Doe', 'Payment Pending', NULL, '', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, '3', NULL, NULL, '2026-06-04 14:58:28'),
(13, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', '', 'declined', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 15:40:35'),
(16, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', '', 'Paid', 'djakad', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 16:08:32'),
(17, 2, 'Gender Reveal Event', 'Gender Reveal', NULL, NULL, '2026-06-11', '16:28:00', NULL, 122, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'pending', 'Antonio\'s Catering', 'accepted', 'Vincent Tolentino', 'pending', 'RM Lights & Sounds', 'pending', 'John Doe', 'declined', NULL, '', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, '1', '2026-06-05 05:30:52'),
(18, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', '', 'Paid', 'This is the place and etc', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-05 05:32:18'),
(36, 2, 'Birthday Event', 'Birthday', NULL, NULL, '2026-08-31', '22:00:00', NULL, 150, NULL, 'pending', NULL, NULL, NULL, 'planning', 'La Tehillah Private Resort and Events Place', 'pending', '', 'pending', 'La Tehillah Private Resort and Events Place', 'pending', '', 'pending', 'La Tehillah Private Resort and Events Place', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 12:00:54'),
(37, 2, 'Anniversary Event', 'Anniversary', NULL, NULL, '2026-07-30', '05:07:00', NULL, 120, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'pending', 'ADM (Asuncion de Grande) Catering', 'pending', 'Vincent Tolentino', 'declined', 'RM Lights & Sounds', 'pending', 'A&A Self-Portrait Studio', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, '12', NULL, NULL, '2026-07-19 15:08:32'),
(38, 2, 'Christening Event', 'Christening', NULL, NULL, '2026-08-08', '17:22:00', NULL, 130, 'LIOS Resort and Events Place', 'declined', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'pending', 'LIOS Resort and Events Place', 'pending', NULL, 'pending', 'LIOS Resort and Events Place', 'pending', 'LIOS Resort and Events Place', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, '23', NULL, NULL, NULL, NULL, '2026-07-28 06:22:46'),
(39, 2, 'Gender Reveal Event', 'Gender Reveal', NULL, NULL, '2026-08-08', '21:32:00', NULL, 130, 'Casa de Consuelo Private Resort and Events Place', 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', 'Taste Event Planner Designs and Catering Services', 'pending', 'Vincent Tolentino', 'declined', 'RM Lights & Sounds', 'pending', 'Vision Photography', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, '31', NULL, NULL, '2026-07-28 07:33:50'),
(40, 2, 'Wedding Event', 'Wedding', NULL, NULL, '2026-08-08', '21:38:00', NULL, 130, NULL, 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'pending', NULL, 'pending', 'Mama Dhel San Antonio', 'pending', 'RM Lights & Sounds', 'pending', 'John Doe', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', 'The reason is we don\'t do that type of food in here', NULL, NULL, NULL, NULL, NULL, '2026-07-28 07:40:54'),
(41, 2, 'Anniversary Event', 'Anniversary', NULL, NULL, '2026-07-28', '20:55:00', NULL, 130, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', 'Casa de Alvin', 'pending', NULL, 'pending', 'Vincent Tolentino', 'pending', '', 'pending', '', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-28 07:56:42'),
(42, 2, 'Wedding Event', 'Wedding', NULL, NULL, '2026-11-28', '10:10:00', NULL, 130, 'LIOS Resort and Events Place', 'Pending Confirmation', NULL, NULL, NULL, 'planning', 'LIOS Resort and Events Place', 'pending', '', 'pending', 'Vincent Tolentino', 'Paid', 'LIOS Resort and Events Place', 'pending', 'John Doe', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, 'not available on this date', NULL, NULL, NULL, '2026-08-01 14:11:32'),
(43, 2, 'Birthday Event', 'Birthday', NULL, NULL, '2026-08-15', '22:11:00', NULL, 15, 'Casa de Consuelo Private Resort and Events Place', 'pending', NULL, NULL, NULL, 'planning', 'Aquino\'s Clothing', 'pending', 'Taste Event Planner Designs and Catering Services', 'pending', 'Vincent Tolentino', 'Payment Pending', 'RM Lights & Sounds', 'pending', 'A&A Self-Portrait Studio', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-01 14:12:33'),
(44, 2, 'Birthday Event', 'Birthday', 'Cartoon Theme', 50000.00, '2026-08-17', '18:12:00', NULL, 100, 'LIOS Resort and Events Place', 'Paid', NULL, NULL, NULL, 'planning', '', 'pending', '', 'pending', 'LIOS Resort and Events Place', 'pending', '', 'pending', '', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-02 07:10:40'),
(45, 2, 'Debut Event', 'Debut', 'Vintage Debut', 80000.00, '2026-09-03', '16:24:00', NULL, 100, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', 'ADM (Asuncion de Grande) Catering', 'pending', 'Vincent Tolentino', 'Paid', 'RM Lights & Sounds', 'pending', 'John Doe', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-02 08:19:49'),
(46, 2, 'Birthday Event', 'Birthday', 'Cartoon Theme', 45000.00, '2026-08-13', '18:17:00', NULL, 100, 'Balai Manlapaz Resto', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', '', 'pending', '', 'pending', '', 'pending', '', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-02 10:14:44'),
(47, 2, 'Birthday Event', 'Birthday', 'Cartoon Theme', 20000.00, '2026-08-19', '23:15:00', NULL, 100, 'Casa de Alvin', 'pending', NULL, NULL, NULL, 'planning', '', 'pending', 'ADM (Asuncion de Grande) Catering', 'pending', 'Vincent Tolentino', 'Payment Pending', 'RM Lights & Sounds', 'pending', 'John Doe', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-02 10:15:31'),
(48, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', '', 'Paid', 'safasfsfsafasf', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 14:07:26'),
(49, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', 'Premium Package', 'Payment Pending', 'SIGE BOI', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 15:36:27'),
(50, 2, 'Wedding Event (Custom)', 'Wedding', 'Beach', 100000.00, '2026-12-23', NULL, NULL, 120, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', '', 'accepted', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-22 04:41:46'),
(51, 2, 'Wedding Event (Custom)', 'Wedding', 'Beach', 100000.00, '2026-12-17', NULL, NULL, 120, NULL, 'pending', NULL, NULL, NULL, 'planning', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', 'Vincent Tolentino', '', 'Pending Confirmation', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-22 04:42:21'),
(52, 2, 'Wedding Event', 'Wedding', 'Beach / Destination', 130.00, '2026-11-24', '05:08:00', '12:08:00', 130, NULL, 'pending', NULL, NULL, NULL, 'planning', 'FC Rental Gown Apalit', 'pending', 'Taste Event Planner Designs and Catering Services', 'pending', 'Mama Dhel San Antonio', 'pending', 'RM Lights & Sounds', 'pending', 'Vision Photography', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 07:09:22'),
(53, 2, 'Christening Event', 'Christening', 'Sky Blue / Pastel', 25000.00, '2026-08-31', '05:10:00', '23:10:00', 130, NULL, 'pending_reselect', NULL, NULL, NULL, 'planning', 'FC Rental Gown Apalit', 'pending', 'Taste Event Planner Designs and Catering Services', 'pending', 'Mama Dhel San Antonio', 'pending', 'RM Lights & Sounds', 'pending', 'Vision Photography', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 07:10:37'),
(56, 22, 'Grand Wedding', 'Wedding', 'Ballroom', NULL, '2026-12-18', '13:54:00', '20:54:00', 123, 'La Tehillah Private Resort and Events Place', 'pending', NULL, NULL, NULL, 'planning', 'M&M Gowns', 'pending', 'La Tehillah Private Resort and Events Place', 'pending', 'Vincent Tolentino', 'pending', 'La Tehillah Private Resort and Events Place', 'pending', 'Vision Photography', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-09 21:54:48'),
(60, 22, 'Alvin\'s Birthday', 'Birthday', '50th Birthday', NULL, '2026-09-19', '18:12:00', '23:13:00', 180, 'LIOS Resort and Events Place', 'pending', NULL, NULL, NULL, 'planning', 'FC Rental Gown Apalit', 'pending', 'LIOS Resort and Events Place', 'pending', 'Vincent Tolentino', 'pending', 'LIOS Resort and Events Place', 'pending', 'Vision Photography', 'pending', NULL, '', 'pending', NULL, 'cash', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-11 02:14:25');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(43, 47, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-08-02 10:15:31'),
(44, 52, 'You\'re Invited to Wedding Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-08-23 07:09:22'),
(45, 53, 'You\'re Invited to Christening Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-08-23 07:10:37'),
(46, 54, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-09-03 08:50:48'),
(47, 55, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-09-09 05:55:22'),
(48, 56, 'You\'re Invited to Wedding Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-09-09 21:54:48'),
(49, 57, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-09-10 00:15:12'),
(50, 58, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-09-10 00:26:45'),
(51, 59, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-09-10 00:32:23'),
(52, 60, 'You\'re Invited to Birthday Event', 'Please confirm your attendance.', '#f3c547', 'Segoe UI', 'Confirm RSVP', NULL, '2026-09-11 02:14:25');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_29_054606_studentmngt_table', 1),
(5, '2026_08_18_000000_create_events_and_supplier_services_tables', 1),
(6, '2026_08_18_000001_fix_events_table', 1),
(7, '2026_08_19_000000_add_service_pic_to_supplier_services_table', 1),
(8, '2026_08_19_000001_restore_supplier_pic_to_supplier_services_table', 1),
(9, '2026_08_21_000000_add_template_to_invitations_table', 2),
(10, '2026_08_22_000000_create_custom_event_requests_table', 3),
(11, '2026_09_03_000000_add_email_verified_at_to_users_table', 4),
(12, '2026_09_10_000000_convert_venue_add_ons_to_json', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('EyYBtDZsX3v3EJ7cRvs7OOzzQXRzxfHtnRzn9Hhq', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYkx3a1UzNWR1dkE1MmtZdWdsem9DckFtWlpGQUxvVmZ4ZmpldENiVCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdXBwbGllci9ib29raW5ncyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjExO30=', 1789123163);

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
  `venue_add_ons` varchar(255) DEFAULT NULL,
  `venueaddons_price1` decimal(10,0) NOT NULL,
  `venueaddons_price2` decimal(10,0) NOT NULL,
  `venueaddons_price3` decimal(10,0) NOT NULL,
  `venueaddons_price4` decimal(10,0) NOT NULL,
  `venueaddons_price5` decimal(10,0) NOT NULL,
  `venueaddons_details1` mediumtext DEFAULT NULL,
  `venueaddons_details2` mediumtext DEFAULT NULL,
  `venueaddons_details3` mediumtext DEFAULT NULL,
  `venueaddons_details4` mediumtext DEFAULT NULL,
  `venueaddons_details5` mediumtext NOT NULL,
  `service_pic` longblob DEFAULT NULL,
  `service_pic1` longblob DEFAULT NULL,
  `service_pic2` longblob DEFAULT NULL,
  `service_pic3` longblob DEFAULT NULL,
  `service_pic4` longblob DEFAULT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 5.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_services`
--

INSERT INTO `supplier_services` (`service_id`, `user_id`, `category`, `style`, `name`, `description`, `price`, `capacity`, `venue_add_ons`, `venueaddons_price1`, `venueaddons_price2`, `venueaddons_price3`, `venueaddons_price4`, `venueaddons_price5`, `venueaddons_details1`, `venueaddons_details2`, `venueaddons_details3`, `venueaddons_details4`, `venueaddons_details5`, `service_pic`, `service_pic1`, `service_pic2`, `service_pic3`, `service_pic4`, `address`, `latitude`, `longitude`, `rating`, `created_at`) VALUES
(3, 3, 'Host', NULL, 'Vincent Tolentino', 'Experienced MC for formal and casual events', 7000.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', 'Apalit, Pampanga', 14.9500000, 120.7650000, 4.70, '2026-06-03 09:28:20'),
(4, 3, 'Photographer', NULL, 'John Doe', 'Photo coverage and edited photos', 12000.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', 'Apalit, Pampanga', 14.9510000, 120.7680000, 4.80, '2026-06-03 09:28:20'),
(5, 15, 'Sounds & Lights', NULL, 'RM Lights & Sounds', 'Audio system, microphones, lighting rig', 15000.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', 'Apalit, Pampanga', 14.9540000, 120.7660000, 4.60, '2026-06-03 09:28:20'),
(10, 6, 'Venue', NULL, 'Casa de Consuelo Private Resort and Events Place', 'Private resort and events place with in-house catering services, ideal for weddings, birthdays and family celebrations.', 18000.00, 220, '[\"Catering\",\"Sounds & Lights\"]', 20000, 0, 0, 0, 10000, 'What We Offer (Core Services)\r\nWeddings: Custom menus, elegant plating, and dedicated staff for your big day.\r\n\r\nCorporate Events: Professional buffet setups, box lunches, and service for business meetings or galas.\r\n\r\nPrivate Parties: Tailored food options for birthdays, anniversaries, and family celebrations. \r\n\r\n', NULL, NULL, NULL, 'Core Services Offered\r\n\r\nSound Systems: High-powered speakers, digital audio mixers, wireless microphones, and DJ gear.\r\n\r\nLighting Packages: Vibrant LED PAR cans, moving head fixtures, uplighting, and computerized DMX controllers.\r\n\r\nVisual Displays: High-resolution LED video walls, projectors, and projection screens.\r\n\r\nStaging and Trusses: Aluminum roof structures, heavy-duty rigging, and secure stage platforms.\r\n\r\nSpecial Effects: Fog machines, low-lying smoke, pyro, and confetti blowers.\r\n\r\nPower & Logistics: Silent generators, industrial fans, air coolers, and professional hauling. ', '', '', '', '', '', 'Purok 1, Sto. Rosario Tabuyuc, Apalit, Pampanga', NULL, NULL, 4.70, '2026-06-03 09:28:20'),
(11, 7, 'Venue', NULL, 'La Tehillah Private Resort and Events Place', 'Resort and events venue offering all-in packages with accommodations, ideal for weddings and large celebrations.', 19000.00, 200, '[\"Catering\",\"Sounds & Lights\"]', 24000, 0, 0, 0, 12000, 'What We Offer (Core Services)\r\nWeddings: Custom menus, elegant plating, and dedicated staff for your big day.\r\n\r\nCorporate Events: Professional buffet setups, box lunches, and service for business meetings or galas.\r\n\r\nPrivate Parties: Tailored food options for birthdays, anniversaries, and family celebrations. \r\n', NULL, NULL, NULL, 'Core Services Offered\r\n\r\nSound Systems: High-powered speakers, digital audio mixers, wireless microphones, and DJ gear.\r\n\r\nLighting Packages: Vibrant LED PAR cans, moving head fixtures, uplighting, and computerized DMX controllers.\r\n\r\nVisual Displays: High-resolution LED video walls, projectors, and projection screens.\r\n\r\nStaging and Trusses: Aluminum roof structures, heavy-duty rigging, and secure stage platforms.\r\n\r\nSpecial Effects: Fog machines, low-lying smoke, pyro, and confetti blowers.\r\n\r\nPower & Logistics: Silent generators, industrial fans, air coolers, and professional hauling.', '', '', '', '', '', '92 Centro St., Brgy. Balucuc, Apalit, Pampanga', NULL, NULL, 4.60, '2026-06-03 09:28:20'),
(12, 8, 'Catering', NULL, 'ADM (Asuncion de Grande) Catering', 'Family-owned catering business serving Apalit since 1994, offering catering and styling services for weddings, debuts, birthdays and corporate events.', 16000.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', 'Apalit, Pampanga', NULL, NULL, 4.90, '2026-06-03 09:28:20'),
(13, 9, 'Catering', NULL, 'Taste Event Planner Designs and Catering Services', 'Full event coordination, styling and catering services based in Sulipan, Apalit, known for elegant table setups and reception design.', 17500.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', 'Sulipan, Apalit, Pampanga', NULL, NULL, 4.80, '2026-06-03 09:28:20'),
(14, 10, 'Photographer', NULL, 'A&A Self-Portrait Studio', 'DTI and BIR registered photography studio in Apalit offering event photo coverage and self-photo studio sessions.', 6000.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '2nd Floor, DMD Blue Arcade Bldg., San Vicente, Apalit, Pampanga', NULL, NULL, 4.50, '2026-06-03 09:28:20'),
(15, 11, 'Venue', 'Resort', 'LIOS Resort and Events Place', 'Beautiful private resort and events place perfect for weddings, birthdays, and special occasions.', 20000.00, 250, '[\"Catering\",\"Sounds & Lights\"]', 17000, 0, 0, 0, 8000, 'What We Offer (Core Services)\r\nWeddings: Custom menus, elegant plating, and dedicated staff for your big day.\r\n\r\nCorporate Events: Professional buffet setups, box lunches, and service for business meetings or galas.\r\n\r\nPrivate Parties: Tailored food options for birthdays, anniversaries, and family celebrations. \r\n', NULL, NULL, NULL, 'Core Services Offered\r\n\r\nSound Systems: High-powered speakers, digital audio mixers, wireless microphones, and DJ gear.\r\n\r\nLighting Packages: Vibrant LED PAR cans, moving head fixtures, uplighting, and computerized DMX controllers.\r\n\r\nVisual Displays: High-resolution LED video walls, projectors, and projection screens.\r\n\r\nStaging and Trusses: Aluminum roof structures, heavy-duty rigging, and secure stage platforms.\r\n\r\nSpecial Effects: Fog machines, low-lying smoke, pyro, and confetti blowers.\r\n\r\nPower & Logistics: Silent generators, industrial fans, air coolers, and professional hauling. ', '', '', '', '', '', '#300 Danga, Colgante, Apalit, Pampanga', NULL, NULL, 4.80, '2026-08-02 06:59:48'),
(16, 12, 'Venue', 'Resto', 'Balai Manlapaz Resto', 'A cozy resto and events place in Manlapaz, ideal for intimate gatherings and celebrations.', 15000.00, 150, '[\"Catering\",\"Sounds & Lights\"]', 22000, 0, 0, 0, 11000, 'What We Offer (Core Services)\r\nWeddings: Custom menus, elegant plating, and dedicated staff for your big day.\r\n\r\nCorporate Events: Professional buffet setups, box lunches, and service for business meetings or galas.\r\n\r\nPrivate Parties: Tailored food options for birthdays, anniversaries, and family celebrations. \r\n', NULL, NULL, NULL, 'Core Services Offered\r\n\r\nSound Systems: High-powered speakers, digital audio mixers, wireless microphones, and DJ gear.\r\n\r\nLighting Packages: Vibrant LED PAR cans, moving head fixtures, uplighting, and computerized DMX controllers.\r\n\r\nVisual Displays: High-resolution LED video walls, projectors, and projection screens.\r\n\r\nStaging and Trusses: Aluminum roof structures, heavy-duty rigging, and secure stage platforms.\r\n\r\nSpecial Effects: Fog machines, low-lying smoke, pyro, and confetti blowers.\r\n\r\nPower & Logistics: Silent generators, industrial fans, air coolers, and professional hauling. ', '', '', '', '', '', 'Manlapaz, Apalit, Pampanga', NULL, NULL, 4.60, '2026-08-02 06:59:48'),
(17, 16, 'Sounds & Lights', NULL, 'J\'s Audio Lights And Sounds', 'Professional sounds and lights services deliver complete audio-visual production for events. They provide crystal-clear sound systems, dynamic stage lighting, and expert technical operators to turn ordinary venues into engaging, high-energy experiences for weddings, concerts, and corporate functions.', 14000.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', 'XQ24+266, Apalit, Pampanga', NULL, NULL, 5.00, '2026-08-06 13:21:31'),
(18, 17, 'Host', NULL, 'Mama Dhel San Antonio', 'provides professional on-stage engagement, program management, and guest coordination to ensure live gatherings, corporate seminars, and social celebrations run smoothly, keep audiences entertained, and maintain a polished, welcoming atmosphere from start to finish.', 18000.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', NULL, NULL, NULL, 5.00, '2026-08-06 13:23:05'),
(19, 18, 'Clothing', NULL, 'FC Rental Gown Apalit', '\"Welcome to FC Rental Gown, your ultimate shared wardrobe for every occasion. We offer a curated collection of premium clothing rentals, from striking event wear to chic daily styles. Experience the joy of wearing high-end fashion without the heavy price tag or storage hassle. Simply choose your look, enjoy your moment, and return it—we handle all the dry cleaning.\"', 6000.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', NULL, NULL, NULL, 5.00, '2026-08-06 13:25:04'),
(20, 19, 'Clothing', NULL, 'M&M Gowns', 'M&M Gown offers a premier, eco-conscious formalwear rental experience, providing access to luxury gowns and haute couture dresses at a fraction of the retail cost. Designed for galas, weddings, red carpet events, and special occasions, our curated collection features contemporary designer gowns, vintage classics, and bespoke bridal wear.', 5000.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '246 Danga, Apalit, Pampanga', NULL, NULL, 5.00, '2026-08-12 04:31:31'),
(21, 20, 'Photographer', NULL, 'Vision Photography', 'Luminary Lens Photography is a boutique photography studio dedicated to capturing life’s most meaningful moments with timeless artistry and emotional depth. Specializing in high-end portraits, editorial fashion, weddings, and special events, we blend candid storytelling with refined aesthetic direction to turn fleeting interactions into cherished visual art.', 7000.00, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '', '', '', '', '', '', 'Andal Reaidence, 316 Purok uno, Sampaloc, Apalit, 2016 Pampanga', NULL, NULL, 5.00, '2026-08-12 04:34:38');

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
  `email_verified_at` date DEFAULT NULL,
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

INSERT INTO `users` (`user_id`, `username`, `full_name`, `email`, `password`, `role`, `email_verified_at`, `status`, `first_name`, `last_name`, `middle_initial`, `age`, `gender`, `phone`, `province`, `municipality`, `barangay`, `postal_code`, `business_name`, `business_address`, `valid_id`, `business_permit`, `face_capture`, `created_at`) VALUES
(1, 'admin', 'Admin User', 'admin@test.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'admin', '2026-09-04', 'approved', 'Admin', 'User', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-03 09:28:20'),
(2, 'client', 'Client User', 'client@test.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'client', '2026-09-04', 'approved', 'Client', 'User', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-03 09:28:20'),
(3, 'supplier', 'Supplier User', 'supplier@test.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', '2026-09-04', 'approved', 'Supplier', 'User', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Events Supplier Inc', 'Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(4, 'coordinator', 'Vincent Tolentino', 'coord@test.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'coordinator', '2026-09-04', 'approved', 'Vincent', 'Tolentino', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Apalit Event Coordination', 'Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(6, 'casadeconsuelo', 'Casa de Consuelo Private Resort', 'casadeconsuelo.events@gmail.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Casa de Consuelo Private Resort and Events Place', 'Purok 1, Sto. Rosario Tabuyuc, Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(7, 'latehillah_resort', 'La Tehillah Private Resort', 'latehillahresort@gmail.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'La Tehillah Private Resort and Events Place', '92 Centro St., Brgy. Balucuc, Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(8, 'admcatering', 'ADM Catering Services', 'info@admcatering.ph', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ADM (Asuncion de Grande) Catering', 'Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(9, 'tasteevents', 'Taste Event Planner Designs and Catering', 'taste.sweetbitebyyhang@gmail.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Taste Event Planner Designs and Catering Services', 'Sulipan, Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(10, 'aaphotography', 'A&A Photography Services', 'aamirrorphotobooth@gmail.com', '$2y$10$5OEhKWqJU/XxtA0w/smNG.bkkgHwonn7lt3HQc498.S0AxBqWTEve', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'A&A Self-Portrait Studio', '2nd Floor, DMD Blue Arcade Bldg., San Vicente, Apalit, Pampanga', NULL, NULL, NULL, '2026-06-03 09:28:20'),
(11, 'lios_resort', 'LIOS Resort and Events Place', 'liosresort@gmail.com', '$2y$10$ndc5jO9LDYUi4sNXe6R58eKFSXl.7iSYN84KuXa9P35F5HujA2jyG', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, '09171234567', 'Pampanga', 'Apalit', NULL, NULL, 'LIOS Resort and Events Place', '#300 Danga, Colgante, Apalit, Pampanga', NULL, NULL, NULL, '2026-08-02 06:59:48'),
(12, 'balai_manlapaz', 'Balai Manlapaz Resto', 'Balaimanlapaz@gmail.com', '$2y$10$ndc5jO9LDYUi4sNXe6R58eKFSXl.7iSYN84KuXa9P35F5HujA2jyG', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, '09179876543', 'Pampanga', 'Apalit', NULL, NULL, 'Balai Manlapaz Resto', 'Manlapaz, Apalit, Pampanga', NULL, NULL, NULL, '2026-08-02 06:59:48'),
(16, 'jsaudiolightsandsound', 'J\'s Audio Lights And Sounds', 'jsaudiolightsandsound@gmail.com', 'password', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, 'Pampanga', 'Apalit', 'XQ24+266, Apalit, Pampanga', '2016', 'J\'s Audio Lights And Sounds', 'XQ24+266, Apalit, Pampanga', NULL, NULL, NULL, '2026-08-23 15:18:49'),
(17, 'mamadhel', 'Mama Dhel San Antonio', 'mamadhel@gmail.com', 'password', 'supplier', '2026-09-04', 'approved', 'Dhel', 'San Antonio', NULL, NULL, 'Male', NULL, 'Pampanga', 'Apalit', 'Sampaloc', '2016', 'Mama Dhel San Antonio', NULL, NULL, NULL, NULL, '2026-08-23 15:21:59'),
(18, 'fcrental', 'FC Rental Gown', 'fcrental@gmail.com', 'password', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, 'Pampanga', 'Apalit', NULL, '2016', 'FC Rental Gown Apalit', NULL, NULL, NULL, NULL, '2026-08-23 15:23:38'),
(19, 'mnmgowns', 'M&M Gowns', 'mnmgown@gmail.com', 'password', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, 'Pampanga', 'Apalit', 'Danga', '2016', 'M&M Gowns', '246 Danga, Apalit, Pampanga', NULL, NULL, NULL, '2026-08-23 15:25:21'),
(20, 'visionphotography', 'Vision Photography', 'visionphotography@gmail.com', 'password', 'supplier', '2026-09-04', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, 'Pampanga', 'Apalit', 'Sampaloc', '2016', 'Vision Photography', 'Andal Reaidence, 316 Purok uno, Sampaloc, Apalit, Pampanga', NULL, NULL, NULL, '2026-08-23 15:26:59'),
(22, 'JC18', 'Jan Clyde L Gutierrez', 'goldenfreddyattack@gmail.com', '$2y$12$Vo/EZYdYeGK3IlUSSmIN2ez5j7i67vgA8hThe7sfCpmjzv82TeXiO', 'client', '2026-09-16', 'pending', 'Jan Clyde', 'Gutierrez', 'L', 21, 'male', '0905 630 3625', 'Pampanga', 'Apalit', 'San Juan', '2016', NULL, NULL, NULL, NULL, NULL, '2026-09-09 08:01:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `3a_tbl`
--
ALTER TABLE `3a_tbl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `coordinator_packages`
--
ALTER TABLE `coordinator_packages`
  ADD PRIMARY KEY (`package_id`),
  ADD KEY `idx_coordinator` (`coordinator_id`);

--
-- Indexes for table `coordinator_reviews`
--
ALTER TABLE `coordinator_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_coordinator` (`coordinator_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `custom_event_requests`
--
ALTER TABLE `custom_event_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `custom_event_requests_event_id_index` (`event_id`),
  ADD KEY `custom_event_requests_client_id_index` (`client_id`),
  ADD KEY `custom_event_requests_coordinator_id_index` (`coordinator_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `3a_tbl`
--
ALTER TABLE `3a_tbl`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coordinator_packages`
--
ALTER TABLE `coordinator_packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coordinator_reviews`
--
ALTER TABLE `coordinator_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_event_requests`
--
ALTER TABLE `custom_event_requests`
  MODIFY `request_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `guest_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invitations`
--
ALTER TABLE `invitations`
  MODIFY `invitation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `supplier_services`
--
ALTER TABLE `supplier_services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
