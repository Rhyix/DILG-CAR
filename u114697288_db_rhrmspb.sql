-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 02, 2026 at 06:24 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u114697288_db_rhrmspb`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(1, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-22 16:38:57', '2025-07-22 16:38:57'),
(2, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-23 08:21:15', '2025-07-23 08:21:15'),
(3, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-23 15:54:52', '2025-07-23 15:54:52'),
(4, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-23 15:54:52', '2025-07-23 15:54:52'),
(5, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-23 15:56:20', '2025-07-23 15:56:20'),
(6, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-23 15:56:20', '2025-07-23 15:56:20'),
(7, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-24 10:50:12', '2025-07-24 10:50:12'),
(8, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-24 10:50:12', '2025-07-24 10:50:12'),
(9, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-24 11:07:42', '2025-07-24 11:07:42'),
(10, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-24 11:07:59', '2025-07-24 11:07:59'),
(11, 'default', 'Created a new admin account.', 'App\\Models\\Admin', 'create', 3, 'App\\Models\\Admin', 1, '{\"username\":\"Viewer\",\"section\":\"Activity Log\"}', NULL, '2025-07-24 11:08:26', '2025-07-24 11:08:26'),
(12, 'default', 'Created a new admin account.', 'App\\Models\\Admin', 'create', 4, 'App\\Models\\Admin', 1, '{\"username\":\"error\",\"section\":\"Activity Log\"}', NULL, '2025-07-24 11:09:49', '2025-07-24 11:09:49'),
(13, 'default', 'Deactivated an admin account.', 'App\\Models\\Admin', 'deactivate', 4, 'App\\Models\\Admin', 1, '{\"deactivated_admin_id\":4,\"section\":\"Activity Log\"}', NULL, '2025-07-24 11:10:28', '2025-07-24 11:10:28'),
(14, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.66.224\",\"section\":\"Google Login\"}', NULL, '2025-07-24 11:55:03', '2025-07-24 11:55:03'),
(15, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.66.224\",\"section\":\"Google Login\"}', NULL, '2025-07-24 14:23:41', '2025-07-24 14:23:41'),
(16, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-24 15:14:42', '2025-07-24 15:14:42'),
(17, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-24 15:14:42', '2025-07-24 15:14:42'),
(18, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"2001:4452:3ce:7d00:98f5:40c1:748d:ba4\",\"email\":\"juaszie@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-24 18:13:35', '2025-07-24 18:13:35'),
(19, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"2001:4452:3ce:7d00:98f5:40c1:748d:ba4\",\"email\":\"juaszie@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-24 18:14:41', '2025-07-24 18:14:41'),
(20, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-25 09:36:03', '2025-07-25 09:36:03'),
(21, 'default', 'Started registration and sent OTP.', NULL, 'register', NULL, NULL, NULL, '{\"ip\":\"49.150.65.74\",\"email\":\"xnzjilm@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-25 09:39:20', '2025-07-25 09:39:20'),
(22, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-25 09:39:20', '2025-07-25 09:39:20'),
(23, 'default', 'Viewed OTP input form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"49.150.65.74\",\"email\":\"xnzjilm@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-25 09:39:21', '2025-07-25 09:39:21'),
(24, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-25 09:39:38', '2025-07-25 09:39:38'),
(25, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-25 09:39:38', '2025-07-25 09:39:38'),
(26, 'default', 'Completed registration and verified email.', NULL, 'verify', NULL, 'App\\Models\\User', 60, '{\"ip\":\"49.150.65.74\",\"email\":\"xnzjilm@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-25 09:39:42', '2025-07-25 09:39:42'),
(27, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-25 15:03:37', '2025-07-25 15:03:37'),
(28, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-25 15:03:37', '2025-07-25 15:03:37'),
(29, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-25 15:13:16', '2025-07-25 15:13:16'),
(30, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-25 15:13:46', '2025-07-25 15:13:46'),
(31, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-25 15:16:39', '2025-07-25 15:16:39'),
(32, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-25 15:16:39', '2025-07-25 15:16:39'),
(33, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 2, '{\"section\":\"Login\"}', NULL, '2025-07-25 16:13:18', '2025-07-25 16:13:18'),
(34, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 6, 'App\\Models\\Admin', 2, '{\"vacancy_id\":\"VAC-006\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-25 16:17:03', '2025-07-25 16:17:03'),
(35, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-25 16:17:18', '2025-07-25 16:17:18'),
(36, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-25 16:17:45', '2025-07-25 16:17:45'),
(37, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-25 16:17:45', '2025-07-25 16:17:45'),
(38, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"2001:4452:54f:cc00:95ab:d2d8:10e1:332\",\"email\":\"pagso@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-25 17:55:54', '2025-07-25 17:55:54'),
(39, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 2, '{\"section\":\"Login\"}', NULL, '2025-07-25 19:43:23', '2025-07-25 19:43:23'),
(40, 'default', 'Updated an admin account.', 'App\\Models\\Admin', 'update', 4, 'App\\Models\\Admin', 2, '{\"updated_admin_id\":4,\"section\":\"Activity Log\"}', NULL, '2025-07-25 19:53:27', '2025-07-25 19:53:27'),
(41, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 1, 'App\\Models\\Admin', 2, '{\"vacancy_id\":\"DEV-001\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-25 19:55:18', '2025-07-25 19:55:18'),
(42, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 14, 'App\\Models\\Admin', 2, '{\"vacancy_id\":\"L-014\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-25 20:43:56', '2025-07-25 20:43:56'),
(43, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 14, 'App\\Models\\Admin', 2, '{\"vacancy_id\":\"L-014\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-25 20:44:09', '2025-07-25 20:44:09'),
(44, 'default', 'Updated job vacancy fields.', 'App\\Models\\JobVacancy', 'edit', 14, 'App\\Models\\Admin', 2, '{\"changes\":{\"place_of_assignment\":{\"old\":\"Apayao Regional Office\",\"new\":\"Abra Provincial Office\"},\"closing_date\":{\"old\":\"2025-08-21T16:09:03.000000Z\",\"new\":\"2025-08-22\"},\"qualification_education\":{\"old\":\"Bachelor\'s \",\"new\":\"Bachelor\'s\"}},\"section\":\"Job Vacancy\"}', NULL, '2025-07-25 20:44:16', '2025-07-25 20:44:16'),
(45, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 14, 'App\\Models\\Admin', 2, '{\"vacancy_id\":\"L-014\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-25 20:44:25', '2025-07-25 20:44:25'),
(46, 'default', 'Updated job vacancy fields.', 'App\\Models\\JobVacancy', 'edit', 14, 'App\\Models\\Admin', 2, '{\"changes\":{\"closing_date\":{\"old\":\"2025-08-21T16:00:00.000000Z\",\"new\":\"2025-08-22\"}},\"section\":\"Job Vacancy\"}', NULL, '2025-07-25 20:44:35', '2025-07-25 20:44:35'),
(47, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 14, 'App\\Models\\Admin', 2, '{\"vacancy_id\":\"L-014\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-25 20:44:54', '2025-07-25 20:44:54'),
(48, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 14, 'App\\Models\\Admin', 2, '{\"vacancy_id\":\"L-014\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-25 21:03:21', '2025-07-25 21:03:21'),
(49, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"124.217.95.145\",\"section\":\"Google Login\"}', NULL, '2025-07-25 23:58:21', '2025-07-25 23:58:21'),
(50, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-25 23:58:28', '2025-07-25 23:58:28'),
(51, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-25 23:58:49', '2025-07-25 23:58:49'),
(52, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-25 23:59:00', '2025-07-25 23:59:00'),
(53, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-25 23:59:05', '2025-07-25 23:59:05'),
(54, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.65.74\",\"section\":\"Google Login\"}', NULL, '2025-07-26 11:09:45', '2025-07-26 11:09:45'),
(55, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"124.217.82.129\",\"section\":\"Google Login\"}', NULL, '2025-07-26 16:03:04', '2025-07-26 16:03:04'),
(56, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.65.74\",\"section\":\"Google Login\"}', NULL, '2025-07-26 19:27:55', '2025-07-26 19:27:55'),
(57, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-26 19:54:17', '2025-07-26 19:54:17'),
(58, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-26 19:54:17', '2025-07-26 19:54:17'),
(59, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-26 21:54:22', '2025-07-26 21:54:22'),
(60, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.65.74\",\"section\":\"Google Login\"}', NULL, '2025-07-27 08:26:37', '2025-07-27 08:26:37'),
(61, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.65.74\",\"section\":\"Google Login\"}', NULL, '2025-07-27 08:30:50', '2025-07-27 08:30:50'),
(62, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.65.74\",\"section\":\"Google Login\"}', NULL, '2025-07-27 11:38:53', '2025-07-27 11:38:53'),
(63, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.65.74\",\"section\":\"Google Login\"}', NULL, '2025-07-27 11:45:41', '2025-07-27 11:45:41'),
(64, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 59, '[]', NULL, '2025-07-27 11:50:23', '2025-07-27 11:50:23'),
(65, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 59, '[]', NULL, '2025-07-27 11:51:45', '2025-07-27 11:51:45'),
(66, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 59, '[]', NULL, '2025-07-27 11:56:57', '2025-07-27 11:56:57'),
(67, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 59, '[]', NULL, '2025-07-27 11:56:57', '2025-07-27 11:56:57'),
(68, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.65.74\",\"section\":\"Google Login\"}', NULL, '2025-07-27 11:57:29', '2025-07-27 11:57:29'),
(69, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.78.102\",\"section\":\"Google Login\"}', NULL, '2025-07-27 20:21:22', '2025-07-27 20:21:22'),
(70, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.78.102\",\"section\":\"Google Login\"}', NULL, '2025-07-27 22:24:58', '2025-07-27 22:24:58'),
(71, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.78.102\",\"section\":\"Google Login\"}', NULL, '2025-07-27 22:31:26', '2025-07-27 22:31:26'),
(72, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 2, '{\"section\":\"Login\"}', NULL, '2025-07-27 23:02:25', '2025-07-27 23:02:25'),
(73, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 14, 'App\\Models\\Admin', 2, '{\"vacancy_id\":\"L-014\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-27 23:02:40', '2025-07-27 23:02:40'),
(74, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 3, 'App\\Models\\Admin', 2, '{\"vacancy_id\":\"VAC-003\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-27 23:03:13', '2025-07-27 23:03:13'),
(75, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 3, 'App\\Models\\Admin', 2, '{\"position_title\":\"Commercial and Industrial Designer\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-27 23:03:21', '2025-07-27 23:03:21'),
(76, 'default', 'Activated an admin account.', 'App\\Models\\Admin', 'activate', 4, 'App\\Models\\Admin', 2, '{\"activated_admin_id\":4,\"section\":\"System Users Management\"}', NULL, '2025-07-27 23:03:59', '2025-07-27 23:03:59'),
(77, 'default', 'Deactivated an admin account.', 'App\\Models\\Admin', 'deactivate', 4, 'App\\Models\\Admin', 2, '{\"deactivated_admin_id\":4,\"section\":\"System Users Management\"}', NULL, '2025-07-27 23:12:52', '2025-07-27 23:12:52'),
(78, 'default', 'Started registration and sent OTP.', NULL, 'register', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"cristhangray@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-28 08:30:03', '2025-07-28 08:30:03'),
(79, 'default', 'Viewed OTP input form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"cristhangray@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-28 08:30:04', '2025-07-28 08:30:04'),
(80, 'default', 'Completed registration and verified email.', NULL, 'verify', NULL, 'App\\Models\\User', 61, '{\"ip\":\"144.48.30.203\",\"email\":\"cristhangray@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-28 08:30:50', '2025-07-28 08:30:50'),
(81, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:35:04', '2025-07-28 08:35:04'),
(82, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:35:04', '2025-07-28 08:35:04'),
(83, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:41:27', '2025-07-28 08:41:27'),
(84, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:42:01', '2025-07-28 08:42:01'),
(85, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:43:09', '2025-07-28 08:43:09'),
(86, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:43:29', '2025-07-28 08:43:29'),
(87, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:43:36', '2025-07-28 08:43:36'),
(88, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:46:28', '2025-07-28 08:46:28'),
(89, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:46:28', '2025-07-28 08:46:28'),
(90, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-28 08:48:52', '2025-07-28 08:48:52'),
(91, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 7, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"VAC-007\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-28 08:49:24', '2025-07-28 08:49:24'),
(92, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 13, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"L6-013\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-28 08:50:07', '2025-07-28 08:50:07'),
(93, 'default', 'Updated job vacancy fields.', 'App\\Models\\JobVacancy', 'edit', 13, 'App\\Models\\Admin', 1, '{\"changes\":{\"closing_date\":{\"old\":\"2025-08-21T16:09:03.000000Z\",\"new\":\"2025-08-30\"}},\"section\":\"Job Vacancy\"}', NULL, '2025-07-28 08:50:27', '2025-07-28 08:50:27'),
(94, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-28 08:50:42', '2025-07-28 08:50:42'),
(95, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:51:05', '2025-07-28 08:51:05'),
(96, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 08:51:05', '2025-07-28 08:51:05'),
(97, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 13, 'App\\Models\\User', 61, '{\"vacancy_id\":\"L6-013\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-28 08:52:15', '2025-07-28 08:52:15'),
(98, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-07-28 09:00:40', '2025-07-28 09:00:40'),
(99, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-28 09:13:35', '2025-07-28 09:13:35'),
(100, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-28 09:13:35', '2025-07-28 09:13:35'),
(101, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 09:14:11', '2025-07-28 09:14:11'),
(102, 'default', 'Uploaded application documents (Admin).', NULL, 'save', NULL, 'App\\Models\\User', 61, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Personal Data Sheet\"}', NULL, '2025-07-28 09:14:11', '2025-07-28 09:14:11'),
(103, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 13, 'App\\Models\\User', 5, '{\"vacancy_id\":\"L6-013\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-28 09:14:17', '2025-07-28 09:14:17'),
(104, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 3, '{\"section\":\"Login\"}', NULL, '2025-07-28 09:22:06', '2025-07-28 09:22:06'),
(105, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-28 09:23:16', '2025-07-28 09:23:16'),
(106, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 10:40:53', '2025-07-28 10:40:53'),
(107, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 10:40:53', '2025-07-28 10:40:53'),
(108, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-28 10:41:33', '2025-07-28 10:41:33'),
(109, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 10:41:59', '2025-07-28 10:41:59'),
(110, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 61, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"changes\":{\"deadline_time\":{\"old\":null,\"new\":\"10:41\"},\"application_letter_status\":{\"old\":\"Submitted\",\"new\":\"Okay\\/Confirmed\"},\"application_letter_remarks\":{\"old\":null,\"new\":\"No remarks provided.\"},\"document_signed_pds\":{\"remarks\":{\"old\":\"\",\"new\":\"List-of-approved-WordPress-plugins-by-GWHS.xlsx - WordPress.pdf\"}},\"document_cert_eligibility\":{\"remarks\":{\"old\":\"\",\"new\":\"RACADIO-PRACTICUM-WEEKLY-REPORT_2024-1 (1).pdf\"}},\"document_transcript_records\":{\"remarks\":{\"old\":\"\",\"new\":\"Form02_Marcos_Honey-May.docx.pdf\"}},\"document_photocopy_diploma\":{\"remarks\":{\"old\":\"\",\"new\":\"RACADIO-PRACTICUM-WEEKLY-REPORT_2024-1.pdf\"}}},\"section\":\"Application List\"}', NULL, '2025-07-28 10:42:09', '2025-07-28 10:42:09'),
(111, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 10:42:10', '2025-07-28 10:42:10'),
(112, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 10:52:34', '2025-07-28 10:52:34'),
(113, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 10:53:36', '2025-07-28 10:53:36'),
(114, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 11:30:07', '2025-07-28 11:30:07'),
(115, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 11:41:11', '2025-07-28 11:41:11'),
(116, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 11:45:15', '2025-07-28 11:45:15'),
(117, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 11:45:41', '2025-07-28 11:45:41'),
(118, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 11:48:23', '2025-07-28 11:48:23'),
(119, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 11:50:17', '2025-07-28 11:50:17'),
(120, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 11:50:37', '2025-07-28 11:50:37'),
(121, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 14:13:08', '2025-07-28 14:13:08'),
(122, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 14:13:08', '2025-07-28 14:13:08'),
(123, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 14:53:54', '2025-07-28 14:53:54'),
(124, 'default', 'Uploaded application documents (Admin).', NULL, 'save', NULL, 'App\\Models\\User', 61, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Personal Data Sheet\"}', NULL, '2025-07-28 14:53:54', '2025-07-28 14:53:54'),
(125, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-28 15:26:33', '2025-07-28 15:26:33'),
(126, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 15:26:47', '2025-07-28 15:26:47'),
(127, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-28 15:34:02', '2025-07-28 15:34:02'),
(128, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-28 15:34:02', '2025-07-28 15:34:02'),
(129, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-28 15:35:00', '2025-07-28 15:35:00'),
(130, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 12, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 15:36:19', '2025-07-28 15:36:19'),
(131, 'default', 'Created Work Experience Sheet', NULL, 'Create', NULL, 'App\\Models\\User', 5, '{\"entries_count\":1,\"action_type\":\"Create\",\"section\":\"Work Experience Sheet\"}', NULL, '2025-07-28 15:39:30', '2025-07-28 15:39:30'),
(132, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 12, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 15:40:10', '2025-07-28 15:40:10'),
(133, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 12, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 15:40:17', '2025-07-28 15:40:17'),
(134, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 15:40:23', '2025-07-28 15:40:23'),
(135, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-28 15:42:00', '2025-07-28 15:42:00'),
(136, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 15:42:50', '2025-07-28 15:42:50'),
(137, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-28 15:42:50', '2025-07-28 15:42:50'),
(138, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-28 15:45:45', '2025-07-28 15:45:45'),
(139, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 11, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 15:46:00', '2025-07-28 15:46:00'),
(140, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 12, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"L6-013\",\"section\":\"Application List\"}', NULL, '2025-07-28 15:46:15', '2025-07-28 15:46:15'),
(141, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"112.198.95.115\",\"section\":\"Google Login\"}', NULL, '2025-07-28 15:52:35', '2025-07-28 15:52:35'),
(142, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-28 15:57:28', '2025-07-28 15:57:28'),
(143, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 62, '{\"ip\":\"49.150.70.189\",\"section\":\"Google Login\"}', NULL, '2025-07-28 18:42:35', '2025-07-28 18:42:35'),
(144, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"49.150.70.189\",\"email\":\"test@example.com\",\"section\":\"Login\"}', NULL, '2025-07-28 19:43:15', '2025-07-28 19:43:15'),
(145, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"49.150.70.189\",\"email\":\"test@example.com\",\"section\":\"Login\"}', NULL, '2025-07-28 19:43:22', '2025-07-28 19:43:22'),
(146, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"49.150.70.189\",\"email\":\"test@example.com\",\"section\":\"Login\"}', NULL, '2025-07-28 19:43:28', '2025-07-28 19:43:28'),
(147, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"175.176.4.143\",\"email\":\"hsksmsna@yahoo.com\",\"section\":\"Login\"}', NULL, '2025-07-28 20:07:11', '2025-07-28 20:07:11'),
(148, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"175.176.4.143\",\"email\":\"hsksmsna@yahoo.com\",\"section\":\"Login\"}', NULL, '2025-07-28 20:07:25', '2025-07-28 20:07:25'),
(149, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-28 20:08:24', '2025-07-28 20:08:24'),
(150, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-28 20:08:24', '2025-07-28 20:08:24'),
(151, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.65.192\",\"section\":\"Google Login\"}', NULL, '2025-07-28 21:03:15', '2025-07-28 21:03:15'),
(152, 'default', 'Sent OTP for password reset.', NULL, 'send', NULL, NULL, NULL, '{\"ip\":\"49.150.65.192\",\"email\":\"edrienecabanela@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-28 21:05:23', '2025-07-28 21:05:23'),
(153, 'default', 'Viewed OTP input form for password reset.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"49.150.65.192\",\"email\":\"edrienecabanela@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-28 21:05:25', '2025-07-28 21:05:25'),
(154, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.70.101\",\"section\":\"Google Login\"}', NULL, '2025-07-28 22:00:46', '2025-07-28 22:00:46'),
(155, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 07:55:13', '2025-07-29 07:55:13'),
(156, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 07:55:13', '2025-07-29 07:55:13'),
(157, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 08:10:13', '2025-07-29 08:10:13'),
(158, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 08:10:17', '2025-07-29 08:10:17'),
(159, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 08:16:27', '2025-07-29 08:16:27'),
(160, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 5, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 08:41:02', '2025-07-29 08:41:02'),
(161, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 08:46:27', '2025-07-29 08:46:27'),
(162, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 08:46:53', '2025-07-29 08:46:53'),
(163, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 08:46:57', '2025-07-29 08:46:57'),
(164, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 08:58:32', '2025-07-29 08:58:32'),
(165, 'default', 'Deleted row in C2 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '{\"target_row\":\"work-exp-table\",\"id\":\"1\"}', NULL, '2025-07-29 08:58:36', '2025-07-29 08:58:36'),
(166, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 08:58:39', '2025-07-29 08:58:39'),
(167, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 09:01:46', '2025-07-29 09:01:46'),
(168, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 09:01:55', '2025-07-29 09:01:55'),
(169, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 09:01:57', '2025-07-29 09:01:57'),
(170, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 09:02:15', '2025-07-29 09:02:15'),
(171, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 09:02:15', '2025-07-29 09:02:15'),
(172, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.70.101\",\"section\":\"Google Login\"}', NULL, '2025-07-29 09:08:01', '2025-07-29 09:08:01'),
(173, 'default', 'Created Work Experience Sheet', NULL, 'Create', NULL, 'App\\Models\\User', 59, '{\"entries_count\":1,\"action_type\":\"Create\",\"section\":\"Work Experience Sheet\"}', NULL, '2025-07-29 09:08:47', '2025-07-29 09:08:47'),
(174, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:24:10', '2025-07-29 10:24:10'),
(175, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:24:10', '2025-07-29 10:24:10'),
(176, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:24:16', '2025-07-29 10:24:16'),
(177, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:24:22', '2025-07-29 10:24:22'),
(178, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:24:27', '2025-07-29 10:24:27'),
(179, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:24:39', '2025-07-29 10:24:39'),
(180, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:25:07', '2025-07-29 10:25:07'),
(181, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:25:07', '2025-07-29 10:25:07'),
(182, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:25:58', '2025-07-29 10:25:58'),
(183, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:25:58', '2025-07-29 10:25:58'),
(184, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:26:13', '2025-07-29 10:26:13'),
(185, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:26:14', '2025-07-29 10:26:14'),
(186, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:26:14', '2025-07-29 10:26:14'),
(187, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:26:16', '2025-07-29 10:26:16'),
(188, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:26:27', '2025-07-29 10:26:27'),
(189, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:26:31', '2025-07-29 10:26:31'),
(190, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:26:56', '2025-07-29 10:26:56'),
(191, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:26:56', '2025-07-29 10:26:56'),
(192, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:28:01', '2025-07-29 10:28:01'),
(193, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:28:01', '2025-07-29 10:28:01'),
(194, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:28:57', '2025-07-29 10:28:57'),
(195, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:28:57', '2025-07-29 10:28:57'),
(196, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:29:07', '2025-07-29 10:29:07'),
(197, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:29:11', '2025-07-29 10:29:11'),
(198, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:29:19', '2025-07-29 10:29:19'),
(199, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:29:19', '2025-07-29 10:29:19'),
(200, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:29:51', '2025-07-29 10:29:51'),
(201, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:29:51', '2025-07-29 10:29:51'),
(202, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:39:46', '2025-07-29 10:39:46'),
(203, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:39:46', '2025-07-29 10:39:46'),
(204, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:40:00', '2025-07-29 10:40:00'),
(205, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:40:05', '2025-07-29 10:40:05'),
(206, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:40:25', '2025-07-29 10:40:25'),
(207, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:40:25', '2025-07-29 10:40:25'),
(208, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:41:06', '2025-07-29 10:41:06'),
(209, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 10:41:06', '2025-07-29 10:41:06'),
(210, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:41:24', '2025-07-29 10:41:24'),
(211, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:41:24', '2025-07-29 10:41:24'),
(212, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"49.150.70.101\",\"email\":\"hsksmsna@yahoo.com\",\"section\":\"Login\"}', NULL, '2025-07-29 10:42:43', '2025-07-29 10:42:43'),
(213, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:42:58', '2025-07-29 10:42:58'),
(214, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:42:58', '2025-07-29 10:42:58'),
(215, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:46:31', '2025-07-29 10:46:31'),
(216, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 10:46:31', '2025-07-29 10:46:31'),
(217, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"49.150.70.101\",\"email\":\"edrienecabanela@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-29 10:52:38', '2025-07-29 10:52:38'),
(218, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 11:14:46', '2025-07-29 11:14:46'),
(219, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 11:14:46', '2025-07-29 11:14:46'),
(220, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"edrienecabanela@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-29 13:04:36', '2025-07-29 13:04:36'),
(221, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"edrienecabanela@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-29 13:04:43', '2025-07-29 13:04:43'),
(222, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-07-29 13:04:54', '2025-07-29 13:04:54'),
(223, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:05:30', '2025-07-29 13:05:30'),
(224, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:05:30', '2025-07-29 13:05:30'),
(225, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:06:10', '2025-07-29 13:06:10'),
(226, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:06:10', '2025-07-29 13:06:10'),
(227, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:06:14', '2025-07-29 13:06:14'),
(228, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:06:16', '2025-07-29 13:06:16'),
(229, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:06:16', '2025-07-29 13:06:16'),
(230, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:06:26', '2025-07-29 13:06:26'),
(231, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:07:11', '2025-07-29 13:07:11'),
(232, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:07:15', '2025-07-29 13:07:15'),
(233, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:07:35', '2025-07-29 13:07:35'),
(234, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:07:58', '2025-07-29 13:07:58'),
(235, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:07:58', '2025-07-29 13:07:58'),
(236, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:08:15', '2025-07-29 13:08:15'),
(237, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 57, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-07-29 13:08:17', '2025-07-29 13:08:17'),
(238, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:08:20', '2025-07-29 13:08:20'),
(239, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-29 13:08:54', '2025-07-29 13:08:54'),
(240, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:09:09', '2025-07-29 13:09:09'),
(241, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:09:09', '2025-07-29 13:09:09'),
(242, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-29 13:09:12', '2025-07-29 13:09:12'),
(243, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 5, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:09:17', '2025-07-29 13:09:17'),
(244, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:10:00', '2025-07-29 13:10:00'),
(245, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 13:10:06', '2025-07-29 13:10:06'),
(246, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 63, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-07-29 13:10:53', '2025-07-29 13:10:53'),
(247, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:11:22', '2025-07-29 13:11:22'),
(248, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-29 13:11:46', '2025-07-29 13:11:46'),
(249, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-29 13:11:55', '2025-07-29 13:11:55'),
(250, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-29 13:14:21', '2025-07-29 13:14:21'),
(251, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-29 13:14:21', '2025-07-29 13:14:21'),
(252, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:14:35', '2025-07-29 13:14:35'),
(253, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 63, '[]', NULL, '2025-07-29 13:18:49', '2025-07-29 13:18:49'),
(254, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 63, '[]', NULL, '2025-07-29 13:21:13', '2025-07-29 13:21:13'),
(255, 'default', 'Started registration and sent OTP.', NULL, 'register', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"caradioako@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-29 13:25:13', '2025-07-29 13:25:13'),
(256, 'default', 'Viewed OTP input form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"caradioako@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-29 13:25:14', '2025-07-29 13:25:14'),
(257, 'default', 'Completed registration and verified email.', NULL, 'verify', NULL, 'App\\Models\\User', 64, '{\"ip\":\"144.48.30.203\",\"email\":\"caradioako@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-29 13:25:24', '2025-07-29 13:25:24'),
(258, 'default', 'Sent OTP for password reset.', NULL, 'send', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"caradioako@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-29 13:25:40', '2025-07-29 13:25:40'),
(259, 'default', 'Viewed OTP input form for password reset.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"caradioako@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-29 13:25:41', '2025-07-29 13:25:41'),
(260, 'default', 'OTP verified for password reset.', NULL, 'verify', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"caradioako@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-29 13:26:11', '2025-07-29 13:26:11'),
(261, 'default', 'Viewed password reset form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"caradioako@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-29 13:26:12', '2025-07-29 13:26:12'),
(262, 'default', 'Password reset successfully.', NULL, 'reset', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"caradioako@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-29 13:26:26', '2025-07-29 13:26:26'),
(263, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"cristhangray@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-29 13:26:46', '2025-07-29 13:26:46'),
(264, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"cristhangray@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-29 13:27:10', '2025-07-29 13:27:10'),
(265, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"caradioako@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-29 13:27:34', '2025-07-29 13:27:34'),
(266, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"caradioako@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-29 13:27:45', '2025-07-29 13:27:45'),
(267, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 64, '[]', NULL, '2025-07-29 13:27:59', '2025-07-29 13:27:59'),
(268, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 64, '[]', NULL, '2025-07-29 13:27:59', '2025-07-29 13:27:59'),
(269, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 13:28:34', '2025-07-29 13:28:34'),
(270, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 13:28:34', '2025-07-29 13:28:34'),
(271, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 63, '[]', NULL, '2025-07-29 13:29:00', '2025-07-29 13:29:00'),
(272, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 63, '[]', NULL, '2025-07-29 13:29:00', '2025-07-29 13:29:00'),
(273, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 63, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:29:10', '2025-07-29 13:29:10'),
(274, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 13:33:32', '2025-07-29 13:33:32'),
(275, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 13:33:32', '2025-07-29 13:33:32'),
(276, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 61, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:33:37', '2025-07-29 13:33:37'),
(277, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 13:34:14', '2025-07-29 13:34:14'),
(278, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 13:34:14', '2025-07-29 13:34:14');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(279, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 61, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:34:20', '2025-07-29 13:34:20'),
(280, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 57, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:34:44', '2025-07-29 13:34:44'),
(281, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 61, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:34:54', '2025-07-29 13:34:54'),
(282, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"112.198.127.218\",\"section\":\"Google Login\"}', NULL, '2025-07-29 13:34:54', '2025-07-29 13:34:54'),
(283, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 13:34:56', '2025-07-29 13:34:56'),
(284, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 13:34:56', '2025-07-29 13:34:56'),
(285, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:35:01', '2025-07-29 13:35:01'),
(286, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 61, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:35:12', '2025-07-29 13:35:12'),
(287, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-07-29 13:35:29', '2025-07-29 13:35:29'),
(288, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 61, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:35:30', '2025-07-29 13:35:30'),
(289, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 59, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:35:52', '2025-07-29 13:35:52'),
(290, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"pagsolinganmark04@gmail.com\",\"section\":\"Login\"}', NULL, '2025-07-29 13:35:59', '2025-07-29 13:35:59'),
(291, 'default', 'Sent OTP for password reset.', NULL, 'send', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"pagsolinganmark04@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-29 13:36:21', '2025-07-29 13:36:21'),
(292, 'default', 'Viewed OTP input form for password reset.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"pagsolinganmark04@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-29 13:36:22', '2025-07-29 13:36:22'),
(293, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:36:47', '2025-07-29 13:36:47'),
(294, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 13:36:55', '2025-07-29 13:36:55'),
(295, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-29 13:36:55', '2025-07-29 13:36:55'),
(296, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 61, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:37:00', '2025-07-29 13:37:00'),
(297, 'default', 'OTP verified for password reset.', NULL, 'verify', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"pagsolinganmark04@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-29 13:37:13', '2025-07-29 13:37:13'),
(298, 'default', 'Viewed password reset form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"pagsolinganmark04@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-29 13:37:14', '2025-07-29 13:37:14'),
(299, 'default', 'Password reset successfully.', NULL, 'reset', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"pagsolinganmark04@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2025-07-29 13:37:29', '2025-07-29 13:37:29'),
(300, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 61, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:37:36', '2025-07-29 13:37:36'),
(301, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 58, '[]', NULL, '2025-07-29 13:38:49', '2025-07-29 13:38:49'),
(302, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 58, '[]', NULL, '2025-07-29 13:38:49', '2025-07-29 13:38:49'),
(303, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 61, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:40:17', '2025-07-29 13:40:17'),
(304, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:45:57', '2025-07-29 13:45:57'),
(305, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:46:12', '2025-07-29 13:46:12'),
(306, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:46:18', '2025-07-29 13:46:18'),
(307, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:46:28', '2025-07-29 13:46:28'),
(308, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:49:06', '2025-07-29 13:49:06'),
(309, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 13:49:18', '2025-07-29 13:49:18'),
(310, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 14:15:27', '2025-07-29 14:15:27'),
(311, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 14:16:16', '2025-07-29 14:16:16'),
(312, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 14:17:12', '2025-07-29 14:17:12'),
(313, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 14:23:22', '2025-07-29 14:23:22'),
(314, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 14:24:00', '2025-07-29 14:24:00'),
(315, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 14:25:26', '2025-07-29 14:25:26'),
(316, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-29 15:53:02', '2025-07-29 15:53:02'),
(317, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 15:56:02', '2025-07-29 15:56:02'),
(318, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 2, '{\"section\":\"Login\"}', NULL, '2025-07-29 15:58:36', '2025-07-29 15:58:36'),
(319, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 13, 'App\\Models\\Admin', 2, '{\"position_title\":\"LGOO 6\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 15:59:16', '2025-07-29 15:59:16'),
(320, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 14, 'App\\Models\\Admin', 2, '{\"position_title\":\"lgoo7\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 15:59:26', '2025-07-29 15:59:26'),
(321, 'default', 'Created new job vacancy.', 'App\\Models\\JobVacancy', 'create', 15, 'App\\Models\\Admin', 2, '{\"vacancy_id\":\"LGOOV-015\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:05:40', '2025-07-29 16:05:40'),
(322, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 12, 'App\\Models\\Admin', 2, '{\"position_title\":\"LGOO 6\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:05:54', '2025-07-29 16:05:54'),
(323, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-29 16:11:45', '2025-07-29 16:11:45'),
(324, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-29 16:12:06', '2025-07-29 16:12:06'),
(325, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 65, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-07-29 16:14:32', '2025-07-29 16:14:32'),
(326, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 66, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-07-29 16:15:14', '2025-07-29 16:15:14'),
(327, 'default', 'Started registration and sent OTP.', NULL, 'register', NULL, NULL, NULL, '{\"ip\":\"112.198.127.218\",\"email\":\"ejcabanela2nd@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-29 16:17:16', '2025-07-29 16:17:16'),
(328, 'default', 'Viewed OTP input form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"112.198.127.218\",\"email\":\"ejcabanela2nd@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-29 16:17:17', '2025-07-29 16:17:17'),
(329, 'default', 'Completed registration and verified email.', NULL, 'verify', NULL, 'App\\Models\\User', 67, '{\"ip\":\"112.198.127.218\",\"email\":\"ejcabanela2nd@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-29 16:17:55', '2025-07-29 16:17:55'),
(330, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 67, '[]', NULL, '2025-07-29 16:18:38', '2025-07-29 16:18:38'),
(331, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 67, '[]', NULL, '2025-07-29 16:18:38', '2025-07-29 16:18:38'),
(332, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-29 16:19:09', '2025-07-29 16:19:09'),
(333, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"152.32.126.21\",\"section\":\"Google Login\"}', NULL, '2025-07-29 16:23:50', '2025-07-29 16:23:50'),
(334, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 15, 'App\\Models\\User', 59, '{\"vacancy_id\":\"LGOOV-015\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:25:28', '2025-07-29 16:25:28'),
(335, 'default', 'Created new job vacancy.', 'App\\Models\\JobVacancy', 'create', 16, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LV-016\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:29:46', '2025-07-29 16:29:46'),
(336, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 16, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LV-016\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:30:24', '2025-07-29 16:30:24'),
(337, 'default', 'Updated job vacancy fields.', 'App\\Models\\JobVacancy', 'edit', 16, 'App\\Models\\Admin', 1, '{\"changes\":{\"closing_date\":{\"old\":\"0001-03-31T15:56:00.000000Z\",\"new\":\"2025-01-08\"}},\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:30:46', '2025-07-29 16:30:46'),
(338, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 16, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LV-016\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:31:06', '2025-07-29 16:31:06'),
(339, 'default', 'Updated job vacancy fields.', 'App\\Models\\JobVacancy', 'edit', 16, 'App\\Models\\Admin', 1, '{\"changes\":{\"closing_date\":{\"old\":\"2025-01-07T16:00:00.000000Z\",\"new\":\"2025-08-08\"}},\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:31:25', '2025-07-29 16:31:25'),
(340, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 16, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LV-016\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:32:32', '2025-07-29 16:32:32'),
(341, 'default', 'Created new job vacancy.', 'App\\Models\\JobVacancy', 'create', 17, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-017\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:38:43', '2025-07-29 16:38:43'),
(342, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 16:42:59', '2025-07-29 16:42:59'),
(343, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 16:42:59', '2025-07-29 16:42:59'),
(344, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 15, 'App\\Models\\User', 5, '{\"vacancy_id\":\"LGOOV-015\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-29 16:43:45', '2025-07-29 16:43:45'),
(345, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 16:45:09', '2025-07-29 16:45:09'),
(346, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 16:48:15', '2025-07-29 16:48:15'),
(347, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 5, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"changes\":{\"status\":{\"old\":\"Pending\",\"new\":\"Incomplete\"},\"deadline_time\":{\"old\":null,\"new\":\"16:48\"},\"application_letter_remarks\":{\"old\":null,\"new\":\"No remarks provided.\"},\"document_pqe_result\":{\"remarks\":{\"old\":\"\",\"new\":\"RACADIO-PRACTICUM-WEEKLY-REPORT_2024-(July7-11).pdf\"}},\"document_cert_eligibility\":{\"remarks\":{\"old\":\"\",\"new\":\"12 INTERNSHIP RELEASE FORM_2024_Racadio.pdf\"}},\"document_ipcr\":{\"remarks\":{\"old\":\"\",\"new\":\"15-TRAINING-AGREEMENT-AND_2024.pdf\"}},\"document_non_academic\":{\"remarks\":{\"old\":\"\",\"new\":\"OJT_ENROLLMENT_RACADIO.pdf\"}},\"document_cert_training\":{\"remarks\":{\"old\":\"\",\"new\":\"Record-File-REV1.pdf\"}},\"document_designation_order\":{\"remarks\":{\"old\":\"\",\"new\":\"02 CERTIFICATION OF UNITS EARNED FOR PRACTICUM_2024.docx.pdf\"}},\"document_transcript_records\":{\"remarks\":{\"old\":\"\",\"new\":\"02-CERTIFICATION-OF-UNITS-EARNED-FOR-PRACTICUM_2024.pdf\"}},\"document_photocopy_diploma\":{\"remarks\":{\"old\":\"\",\"new\":\"12 INTERNSHIP RELEASE FORM_2024_Racadio.pdf\"}},\"document_grade_masteraldoctorate\":{\"remarks\":{\"old\":\"\",\"new\":\"01 APPLICATION FOR INTERNSHIP_2024.docx.pdf\"}},\"document_tor_masteraldoctorate\":{\"remarks\":{\"old\":\"\",\"new\":\"01 APPLICATION FOR INTERNSHIP_2024.docx (1).pdf\"}},\"document_cert_employment\":{\"remarks\":{\"old\":\"\",\"new\":\"02 CERTIFICATION OF UNITS EARNED FOR PRACTICUM_2024.docx.pdf\"}},\"document_other_documents\":{\"remarks\":{\"old\":\"\",\"new\":\"02-CERTIFICATION-OF-UNITS-EARNED-FOR-PRACTICUM_2024.pdf\"}}},\"section\":\"Application List\"}', NULL, '2025-07-29 16:48:25', '2025-07-29 16:48:25'),
(348, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 16:48:26', '2025-07-29 16:48:26'),
(349, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 16:50:39', '2025-07-29 16:50:39'),
(350, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 5, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"changes\":{\"status\":{\"old\":\"Incomplete\",\"new\":\"Complete\"},\"deadline_time\":{\"old\":\"16:48:00\",\"new\":\"16:48\"}},\"section\":\"Application List\"}', NULL, '2025-07-29 16:50:44', '2025-07-29 16:50:44'),
(351, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 16:50:45', '2025-07-29 16:50:45'),
(352, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 16:50:57', '2025-07-29 16:50:57'),
(353, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 5, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"changes\":{\"deadline_time\":{\"old\":\"16:48:00\",\"new\":\"16:48\"},\"application_letter_remarks\":{\"old\":\"No remarks provided.\",\"new\":\"Wrong Document\"},\"document_transcript_records\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"}},\"document_photocopy_diploma\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"}}},\"section\":\"Application List\"}', NULL, '2025-07-29 16:55:00', '2025-07-29 16:55:00'),
(354, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 16:55:01', '2025-07-29 16:55:01'),
(355, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 5, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"changes\":{\"deadline_time\":{\"old\":\"16:48:00\",\"new\":\"16:48\"},\"qs_education\":{\"old\":null,\"new\":\"no\"},\"qs_eligibility\":{\"old\":null,\"new\":\"no\"},\"qs_experience\":{\"old\":null,\"new\":\"no\"},\"qs_training\":{\"old\":null,\"new\":\"no\"},\"qs_result\":{\"old\":null,\"new\":\"not qualified\"},\"application_remarks\":{\"old\":null,\"new\":\"INC\"}},\"section\":\"Application List\"}', NULL, '2025-07-29 16:56:09', '2025-07-29 16:56:09'),
(356, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 16:56:10', '2025-07-29 16:56:10'),
(357, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 13, 'App\\Models\\Admin', 1, '{\"user_id\":\"59\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 17:00:56', '2025-07-29 17:00:56'),
(358, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 17:04:14', '2025-07-29 17:04:14'),
(359, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 17:06:25', '2025-07-29 17:06:25'),
(360, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 17:22:11', '2025-07-29 17:22:11'),
(361, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 5, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"changes\":{\"status\":{\"old\":\"Complete\",\"new\":\"Closed\"},\"deadline_time\":{\"old\":\"16:48:00\",\"new\":\"16:48\"}},\"section\":\"Application List\"}', NULL, '2025-07-29 17:27:13', '2025-07-29 17:27:13'),
(362, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 17:27:14', '2025-07-29 17:27:14'),
(363, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 14, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 17:32:00', '2025-07-29 17:32:00'),
(364, 'default', 'Updated Work Experience Sheet', NULL, 'Update', NULL, 'App\\Models\\User', 5, '{\"entries_count\":2,\"action_type\":\"Update\",\"section\":\"Work Experience Sheet\"}', NULL, '2025-07-29 17:32:08', '2025-07-29 17:32:08'),
(365, 'default', 'Updated Work Experience Sheet', NULL, 'Update', NULL, 'App\\Models\\User', 5, '{\"entries_count\":2,\"action_type\":\"Update\",\"section\":\"Work Experience Sheet\"}', NULL, '2025-07-29 17:32:13', '2025-07-29 17:32:13'),
(366, 'default', 'Exported Work Experience Sheet.', NULL, 'export', NULL, 'App\\Models\\User', 5, '{\"exported_file\":\"WorkExperienceSheet.docx\",\"entries_count\":2,\"section\":\"Export\"}', NULL, '2025-07-29 17:32:15', '2025-07-29 17:32:15'),
(367, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"112.198.127.218\",\"section\":\"Google Login\"}', NULL, '2025-07-29 17:33:56', '2025-07-29 17:33:56'),
(368, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 68, '{\"ip\":\"112.198.127.218\",\"section\":\"Google Login\"}', NULL, '2025-07-29 17:34:18', '2025-07-29 17:34:18'),
(369, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 68, '[]', NULL, '2025-07-29 17:46:06', '2025-07-29 17:46:06'),
(370, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 68, '[]', NULL, '2025-07-29 17:46:57', '2025-07-29 17:46:57'),
(371, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 68, '[]', NULL, '2025-07-29 17:58:09', '2025-07-29 17:58:09'),
(372, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 68, '[]', NULL, '2025-07-29 17:58:09', '2025-07-29 17:58:09'),
(373, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 68, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 17:59:18', '2025-07-29 17:59:18'),
(374, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 18:11:04', '2025-07-29 18:11:04'),
(375, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 18:11:04', '2025-07-29 18:11:04'),
(376, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"2405:8d40:4c0e:b40c:d997:2ad8:4727:961a\",\"section\":\"Google Login\"}', NULL, '2025-07-29 22:58:52', '2025-07-29 22:58:52'),
(377, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-29 23:00:53', '2025-07-29 23:00:53'),
(378, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-29 23:07:08', '2025-07-29 23:07:08'),
(379, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 13, 'App\\Models\\Admin', 1, '{\"user_id\":\"59\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-07-29 23:10:50', '2025-07-29 23:10:50'),
(380, 'default', 'Accessed edit exam page.', NULL, 'view', NULL, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LGOOV-015\",\"section\":\"Exam Management\"}', NULL, '2025-07-29 23:12:51', '2025-07-29 23:12:51'),
(381, 'default', 'Managed exam participants and details.', NULL, NULL, NULL, NULL, NULL, '{\"vacancy_id\":\"LGOOV-015\",\"section\":\"Exam Management\"}', NULL, '2025-07-29 23:13:05', '2025-07-29 23:13:05'),
(382, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-29 23:14:03', '2025-07-29 23:14:03'),
(383, 'default', 'Started registration and sent OTP.', NULL, 'register', NULL, NULL, NULL, '{\"ip\":\"2405:8d40:4c0e:b40c:d997:2ad8:4727:961a\",\"email\":\"ej@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-29 23:17:10', '2025-07-29 23:17:10'),
(384, 'default', 'Viewed OTP input form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"2405:8d40:4c0e:b40c:d997:2ad8:4727:961a\",\"email\":\"ej@gmail.com\",\"section\":\"Register\"}', NULL, '2025-07-29 23:17:11', '2025-07-29 23:17:11'),
(385, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 23:18:06', '2025-07-29 23:18:06'),
(386, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-29 23:18:06', '2025-07-29 23:18:06'),
(387, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-29 23:35:45', '2025-07-29 23:35:45'),
(388, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-30 08:07:47', '2025-07-30 08:07:47'),
(389, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-30 08:07:47', '2025-07-30 08:07:47'),
(390, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-30 08:10:17', '2025-07-30 08:10:17'),
(391, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 16, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LV-016\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-30 08:10:40', '2025-07-30 08:10:40'),
(392, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.65.74\",\"section\":\"Google Login\"}', NULL, '2025-07-30 10:06:11', '2025-07-30 10:06:11'),
(393, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-30 14:14:04', '2025-07-30 14:14:04'),
(394, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-30 14:14:36', '2025-07-30 14:14:36'),
(395, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-30 14:14:36', '2025-07-30 14:14:36'),
(396, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.65.74\",\"section\":\"Google Login\"}', NULL, '2025-07-31 07:06:33', '2025-07-31 07:06:33'),
(397, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"124.217.82.129\",\"section\":\"Google Login\"}', NULL, '2025-07-31 11:27:44', '2025-07-31 11:27:44'),
(398, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-31 14:34:18', '2025-07-31 14:34:18'),
(399, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-31 14:34:18', '2025-07-31 14:34:18'),
(400, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-31 14:34:35', '2025-07-31 14:34:35'),
(401, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-31 14:34:40', '2025-07-31 14:34:40'),
(402, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 16, 'App\\Models\\User', 5, '{\"vacancy_id\":\"LV-016\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-31 14:35:20', '2025-07-31 14:35:20'),
(403, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"124.217.82.129\",\"section\":\"Google Login\"}', NULL, '2025-07-31 15:07:26', '2025-07-31 15:07:26'),
(404, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-31 15:07:31', '2025-07-31 15:07:31'),
(405, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-07-31 15:07:54', '2025-07-31 15:07:54'),
(406, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"112.198.95.108\",\"section\":\"Google Login\"}', NULL, '2025-07-31 16:03:08', '2025-07-31 16:03:08'),
(407, 'default', 'Deleted row in C2 form.', NULL, NULL, NULL, 'App\\Models\\User', 59, '{\"target_row\":\"work-exp-table\",\"id\":\"2\"}', NULL, '2025-07-31 16:10:32', '2025-07-31 16:10:32'),
(408, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-31 16:34:51', '2025-07-31 16:34:51'),
(409, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-31 16:35:10', '2025-07-31 16:35:10'),
(410, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-31 16:35:19', '2025-07-31 16:35:19'),
(411, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-31 16:35:29', '2025-07-31 16:35:29'),
(412, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 56, '[]', NULL, '2025-07-31 16:35:29', '2025-07-31 16:35:29'),
(413, 'default', 'Created Work Experience Sheet', NULL, 'Create', NULL, 'App\\Models\\User', 56, '{\"entries_count\":1,\"action_type\":\"Create\",\"section\":\"Work Experience Sheet\"}', NULL, '2025-07-31 16:39:45', '2025-07-31 16:39:45'),
(414, 'default', 'Exported Work Experience Sheet.', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"WorkExperienceSheet.docx\",\"entries_count\":1,\"section\":\"Export\"}', NULL, '2025-07-31 16:39:47', '2025-07-31 16:39:47'),
(415, 'default', 'Exported Work Experience Sheet.', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"WorkExperienceSheet.docx\",\"entries_count\":1,\"section\":\"Export\"}', NULL, '2025-07-31 17:55:37', '2025-07-31 17:55:37'),
(416, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-31 18:07:01', '2025-07-31 18:07:01'),
(417, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-07-31 18:07:01', '2025-07-31 18:07:01'),
(418, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 63, '{\"ip\":\"175.176.4.175\",\"section\":\"Google Login\"}', NULL, '2025-07-31 18:11:49', '2025-07-31 18:11:49'),
(419, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 16, 'App\\Models\\User', 63, '{\"vacancy_id\":\"LV-016\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-31 18:12:17', '2025-07-31 18:12:17'),
(420, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-31 18:13:01', '2025-07-31 18:13:01'),
(421, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 15, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-07-31 18:13:21', '2025-07-31 18:13:21'),
(422, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 16, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-07-31 18:13:28', '2025-07-31 18:13:28'),
(423, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-07-31 18:15:19', '2025-07-31 18:15:19'),
(424, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 63, '{\"ip\":\"175.176.4.175\",\"section\":\"Google Login\"}', NULL, '2025-07-31 18:15:46', '2025-07-31 18:15:46'),
(425, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-31 18:33:14', '2025-07-31 18:33:14'),
(426, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-31 18:33:14', '2025-07-31 18:33:14'),
(427, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-31 18:33:55', '2025-07-31 18:33:55'),
(428, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-31 18:34:19', '2025-07-31 18:34:19'),
(429, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-31 18:36:40', '2025-07-31 18:36:40'),
(430, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-31 18:36:40', '2025-07-31 18:36:40'),
(431, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 16, 'App\\Models\\User', 61, '{\"vacancy_id\":\"LV-016\",\"section\":\"Job Vacancy\"}', NULL, '2025-07-31 18:37:18', '2025-07-31 18:37:18'),
(432, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-07-31 18:37:32', '2025-07-31 18:37:32'),
(433, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 16, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-07-31 18:37:33', '2025-07-31 18:37:33'),
(434, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 63, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"changes\":{\"deadline_date\":{\"old\":null,\"new\":\"2025-08-08\"},\"deadline_time\":{\"old\":null,\"new\":\"18:37\"},\"application_remarks\":{\"old\":null,\"new\":\"No remarks yet\"},\"application_letter_remarks\":{\"old\":null,\"new\":\"No remarks provided.\"},\"document_transcript_records\":{\"remarks\":{\"old\":\"\",\"new\":\"Rizal-Retraction.pdf\"}},\"document_photocopy_diploma\":{\"remarks\":{\"old\":\"\",\"new\":\"Rizal-Retraction.pdf\"}}},\"section\":\"Application List\"}', NULL, '2025-07-31 18:38:01', '2025-07-31 18:38:01'),
(435, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 16, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-07-31 18:38:01', '2025-07-31 18:38:01'),
(436, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 15, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-07-31 18:38:29', '2025-07-31 18:38:29'),
(437, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 5, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LV-016\",\"changes\":{\"deadline_date\":{\"old\":null,\"new\":\"2025-08-07\"},\"deadline_time\":{\"old\":null,\"new\":\"18:38\"},\"application_remarks\":{\"old\":null,\"new\":\"No remarks yet\"},\"application_letter_remarks\":{\"old\":null,\"new\":\"No remarks provided.\"}},\"section\":\"Application List\"}', NULL, '2025-07-31 18:38:35', '2025-07-31 18:38:35'),
(438, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 15, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-07-31 18:38:35', '2025-07-31 18:38:35'),
(439, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 17, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-07-31 18:39:48', '2025-07-31 18:39:48'),
(440, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 61, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"LV-016\",\"changes\":{\"deadline_date\":{\"old\":null,\"new\":\"2025-08-06\"},\"deadline_time\":{\"old\":null,\"new\":\"18:39\"},\"application_remarks\":{\"old\":null,\"new\":\"No remarks yet\"},\"application_letter_remarks\":{\"old\":null,\"new\":\"No remarks provided.\"},\"document_signed_work_exp_sheet\":{\"remarks\":{\"old\":\"\",\"new\":\"ENDORSEMENT-LETTER_BENECO.pdf\"}},\"document_pqe_result\":{\"remarks\":{\"old\":\"\",\"new\":\"MARCOS, HONEY MAY.pdf\"}}},\"section\":\"Application List\"}', NULL, '2025-07-31 18:39:56', '2025-07-31 18:39:56'),
(441, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 17, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-07-31 18:39:57', '2025-07-31 18:39:57'),
(442, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-31 18:40:46', '2025-07-31 18:40:46'),
(443, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 61, '[]', NULL, '2025-07-31 18:40:46', '2025-07-31 18:40:46'),
(444, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-01 11:47:16', '2025-08-01 11:47:16'),
(445, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-01 11:47:16', '2025-08-01 11:47:16'),
(446, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-08-01 11:47:58', '2025-08-01 11:47:58'),
(447, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 15, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LGOOV-015\",\"section\":\"Job Vacancy\"}', NULL, '2025-08-01 11:48:10', '2025-08-01 11:48:10'),
(448, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 15, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LGOOV-015\",\"section\":\"Job Vacancy\"}', NULL, '2025-08-01 11:48:23', '2025-08-01 11:48:23'),
(449, 'default', 'Updated job vacancy fields.', 'App\\Models\\JobVacancy', 'edit', 15, 'App\\Models\\Admin', 1, '{\"changes\":{\"closing_date\":{\"old\":\"2025-07-30T16:00:00.000000Z\",\"new\":\"2025-08-09\"}},\"section\":\"Job Vacancy\"}', NULL, '2025-08-01 11:48:32', '2025-08-01 11:48:32'),
(450, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 15, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LGOOV-015\",\"section\":\"Job Vacancy\"}', NULL, '2025-08-01 11:50:33', '2025-08-01 11:50:33'),
(451, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 17, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-017\",\"section\":\"Job Vacancy\"}', NULL, '2025-08-01 11:51:19', '2025-08-01 11:51:19'),
(452, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 17, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-017\",\"section\":\"Job Vacancy\"}', NULL, '2025-08-01 11:51:29', '2025-08-01 11:51:29'),
(453, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 17, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-017\",\"section\":\"Job Vacancy\"}', NULL, '2025-08-01 11:51:38', '2025-08-01 11:51:38'),
(454, 'default', 'Updated job vacancy fields.', 'App\\Models\\JobVacancy', 'edit', 17, 'App\\Models\\Admin', 1, '{\"changes\":{\"closing_date\":{\"old\":\"2025-07-30T16:00:00.000000Z\",\"new\":\"2025-08-07\"}},\"section\":\"Job Vacancy\"}', NULL, '2025-08-01 11:51:43', '2025-08-01 11:51:43'),
(455, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 17, 'App\\Models\\User', 5, '{\"vacancy_id\":\"A-017\",\"section\":\"Job Vacancy\"}', NULL, '2025-08-01 11:52:01', '2025-08-01 11:52:01'),
(456, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 63, '{\"ip\":\"175.176.5.36\",\"section\":\"Google Login\"}', NULL, '2025-08-02 10:56:18', '2025-08-02 10:56:18'),
(457, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-08-02 10:58:09', '2025-08-02 10:58:09'),
(458, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 16, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-08-02 10:58:39', '2025-08-02 10:58:39'),
(459, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 17, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-08-02 10:59:00', '2025-08-02 10:59:00'),
(460, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-08-02 10:59:20', '2025-08-02 10:59:20'),
(461, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 63, '{\"ip\":\"175.176.5.36\",\"section\":\"Google Login\"}', NULL, '2025-08-02 11:00:43', '2025-08-02 11:00:43'),
(462, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-08-02 11:08:20', '2025-08-02 11:08:20'),
(463, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 18, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"A-017\",\"section\":\"Application List\"}', NULL, '2025-08-02 11:10:29', '2025-08-02 11:10:29'),
(464, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 63, '{\"ip\":\"175.176.4.52\",\"section\":\"Google Login\"}', NULL, '2025-08-02 17:38:29', '2025-08-02 17:38:29'),
(465, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-08-02 17:39:21', '2025-08-02 17:39:21'),
(466, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 16, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-08-02 17:40:16', '2025-08-02 17:40:16'),
(467, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-02 17:49:33', '2025-08-02 17:49:33'),
(468, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-02 17:49:33', '2025-08-02 17:49:33'),
(469, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"124.217.82.129\",\"section\":\"Google Login\"}', NULL, '2025-08-04 12:14:02', '2025-08-04 12:14:02'),
(470, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"2405:8d40:4810:f0c6:20e0:bf:c82a:e921\",\"email\":\"janelantolin20@gmail.com\",\"section\":\"Login\"}', NULL, '2025-08-04 21:23:54', '2025-08-04 21:23:54'),
(471, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 63, '{\"ip\":\"2405:8d40:4810:f0c6:20e0:bf:c82a:e921\",\"section\":\"Google Login\"}', NULL, '2025-08-04 21:24:10', '2025-08-04 21:24:10'),
(472, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-04 21:24:53', '2025-08-04 21:24:53'),
(473, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-04 21:24:53', '2025-08-04 21:24:53'),
(474, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-08-05 21:28:40', '2025-08-05 21:28:40'),
(475, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-08-05 21:29:16', '2025-08-05 21:29:16'),
(476, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.73.12\",\"section\":\"Google Login\"}', NULL, '2025-08-05 21:29:33', '2025-08-05 21:29:33'),
(477, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 59, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-08-05 21:29:40', '2025-08-05 21:29:40'),
(478, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.73.12\",\"section\":\"Google Login\"}', NULL, '2025-08-06 17:47:07', '2025-08-06 17:47:07'),
(479, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 20:49:44', '2025-08-06 20:49:44'),
(480, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 20:49:44', '2025-08-06 20:49:44'),
(481, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"124.217.82.129\",\"section\":\"Google Login\"}', NULL, '2025-08-06 21:18:07', '2025-08-06 21:18:07'),
(482, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-08-06 21:36:10', '2025-08-06 21:36:10'),
(483, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 17, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-017\",\"section\":\"Job Vacancy\"}', NULL, '2025-08-06 21:36:16', '2025-08-06 21:36:16'),
(484, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-08-06 21:37:04', '2025-08-06 21:37:04'),
(485, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 21:37:14', '2025-08-06 21:37:14'),
(486, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 21:37:14', '2025-08-06 21:37:14'),
(487, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 5, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-08-06 21:37:47', '2025-08-06 21:37:47'),
(488, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 5, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-08-06 21:40:11', '2025-08-06 21:40:11'),
(489, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 69, '{\"ip\":\"2001:4451:12b2:7300:79ad:3529:a88e:8497\",\"section\":\"Google Login\"}', NULL, '2025-08-06 21:43:08', '2025-08-06 21:43:08'),
(490, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 5, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-08-06 22:02:56', '2025-08-06 22:02:56'),
(491, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:04:02', '2025-08-06 22:04:02'),
(492, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:04:18', '2025-08-06 22:04:18'),
(493, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:06:54', '2025-08-06 22:06:54'),
(494, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:07:35', '2025-08-06 22:07:35'),
(495, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:09:09', '2025-08-06 22:09:09'),
(496, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:10:07', '2025-08-06 22:10:07'),
(497, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:10:25', '2025-08-06 22:10:25'),
(498, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:10:32', '2025-08-06 22:10:32'),
(499, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:10:32', '2025-08-06 22:10:32'),
(500, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 5, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-08-06 22:10:39', '2025-08-06 22:10:39'),
(501, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:12:21', '2025-08-06 22:12:21'),
(502, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:12:21', '2025-08-06 22:12:21'),
(503, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-06 22:13:07', '2025-08-06 22:13:07'),
(504, 'default', 'Uploaded application documents (Admin).', NULL, 'save', NULL, 'App\\Models\\User', 5, '{\"user_id\":\"5\",\"vacancy_id\":\"A-017\",\"section\":\"Personal Data Sheet\"}', NULL, '2025-08-06 22:13:07', '2025-08-06 22:13:07'),
(505, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 63, '{\"ip\":\"111.90.231.58\",\"section\":\"Google Login\"}', NULL, '2025-08-07 12:36:47', '2025-08-07 12:36:47'),
(506, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-08-07 12:37:32', '2025-08-07 12:37:32'),
(507, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 18, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"A-017\",\"section\":\"Application List\"}', NULL, '2025-08-07 12:37:46', '2025-08-07 12:37:46'),
(508, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 18, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"A-017\",\"section\":\"Application List\"}', NULL, '2025-08-07 12:37:53', '2025-08-07 12:37:53'),
(509, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 13, 'App\\Models\\Admin', 1, '{\"user_id\":\"59\",\"vacancy_id\":\"LGOOV-015\",\"section\":\"Application List\"}', NULL, '2025-08-07 12:38:08', '2025-08-07 12:38:08'),
(510, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 16, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-08-07 12:38:30', '2025-08-07 12:38:30'),
(511, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 16, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-08-07 12:38:42', '2025-08-07 12:38:42');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(512, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 63, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"changes\":{\"deadline_date\":{\"old\":\"2025-08-08\",\"new\":\"2025-08-06\"},\"deadline_time\":{\"old\":\"18:37\",\"new\":\"23:59\"}},\"section\":\"Application List\"}', NULL, '2025-08-07 12:39:11', '2025-08-07 12:39:11'),
(513, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 16, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-08-07 12:39:12', '2025-08-07 12:39:12'),
(514, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-08-07 12:40:38', '2025-08-07 12:40:38'),
(515, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 63, '{\"ip\":\"111.90.231.58\",\"section\":\"Google Login\"}', NULL, '2025-08-07 12:40:51', '2025-08-07 12:40:51'),
(516, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-08-07 12:41:59', '2025-08-07 12:41:59'),
(517, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 17, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-08-07 12:42:10', '2025-08-07 12:42:10'),
(518, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 16, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LV-016\",\"section\":\"Job Vacancy\"}', NULL, '2025-08-07 12:43:34', '2025-08-07 12:43:34'),
(519, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 17, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-08-07 12:44:15', '2025-08-07 12:44:15'),
(520, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-08-07 12:44:46', '2025-08-07 12:44:46'),
(521, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-07 14:47:39', '2025-08-07 14:47:39'),
(522, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-07 14:47:39', '2025-08-07 14:47:39'),
(523, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 5, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-08-07 14:47:48', '2025-08-07 14:47:48'),
(524, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-08-07 14:49:09', '2025-08-07 14:49:09'),
(525, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 16, 'App\\Models\\Admin', 1, '{\"user_id\":\"63\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-08-07 14:50:12', '2025-08-07 14:50:12'),
(526, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 16, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LV-016\",\"section\":\"Job Vacancy\"}', NULL, '2025-08-07 14:51:52', '2025-08-07 14:51:52'),
(527, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"124.217.82.129\",\"section\":\"Google Login\"}', NULL, '2025-08-08 00:18:17', '2025-08-08 00:18:17'),
(528, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-08-08 00:18:23', '2025-08-08 00:18:23'),
(529, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-08-08 00:20:38', '2025-08-08 00:20:38'),
(530, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-08-08 00:21:25', '2025-08-08 00:21:25'),
(531, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 57, '{\"ip\":\"110.54.156.253\",\"section\":\"Google Login\"}', NULL, '2025-08-12 09:00:28', '2025-08-12 09:00:28'),
(532, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"110.54.156.253\",\"section\":\"Google Login\"}', NULL, '2025-08-12 09:02:19', '2025-08-12 09:02:19'),
(533, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-12 15:29:53', '2025-08-12 15:29:53'),
(534, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 5, '[]', NULL, '2025-08-12 15:29:53', '2025-08-12 15:29:53'),
(535, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"jaysmatias15@gmail.com\",\"section\":\"Login\"}', NULL, '2025-08-13 14:50:08', '2025-08-13 14:50:08'),
(536, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 66, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-08-13 14:50:16', '2025-08-13 14:50:16'),
(537, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 66, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-08-13 14:50:56', '2025-08-13 14:50:56'),
(538, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 66, '{\"ip\":\"152.32.126.21\",\"section\":\"Google Login\"}', NULL, '2025-08-13 14:52:29', '2025-08-13 14:52:29'),
(539, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 70, '{\"ip\":\"152.32.126.21\",\"section\":\"Google Login\"}', NULL, '2025-08-13 16:53:06', '2025-08-13 16:53:06'),
(540, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"124.217.83.211\",\"section\":\"Google Login\"}', NULL, '2025-08-14 14:36:47', '2025-08-14 14:36:47'),
(541, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2025-08-14 14:36:55', '2025-08-14 14:36:55'),
(542, 'default', 'Updated Work Experience Sheet', NULL, 'Update', NULL, 'App\\Models\\User', 56, '{\"entries_count\":1,\"action_type\":\"Update\",\"section\":\"Work Experience Sheet\"}', NULL, '2025-08-14 14:39:13', '2025-08-14 14:39:13'),
(543, 'default', 'Exported Work Experience Sheet.', NULL, 'export', NULL, 'App\\Models\\User', 56, '{\"exported_file\":\"WorkExperienceSheet.docx\",\"entries_count\":1,\"section\":\"Export\"}', NULL, '2025-08-14 14:39:15', '2025-08-14 14:39:15'),
(544, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"124.217.82.45\",\"section\":\"Google Login\"}', NULL, '2025-08-30 17:56:20', '2025-08-30 17:56:20'),
(545, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"112.198.127.207\",\"section\":\"Google Login\"}', NULL, '2025-09-03 11:38:10', '2025-09-03 11:38:10'),
(546, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"119.93.37.6\",\"email\":\"juaszie@gmail.com\",\"section\":\"Login\"}', NULL, '2025-09-10 10:49:07', '2025-09-10 10:49:07'),
(547, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"119.93.37.6\",\"section\":\"Google Login\"}', NULL, '2025-09-10 10:50:50', '2025-09-10 10:50:50'),
(548, 'default', 'Admin logged in unsuccessfully.', NULL, 'login', NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-09-10 10:52:55', '2025-09-10 10:52:55'),
(549, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-09-11 11:59:15', '2025-09-11 11:59:15'),
(550, 'default', 'Admin logged in unsuccessfully.', NULL, 'login', NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-09-11 11:59:32', '2025-09-11 11:59:32'),
(551, 'default', 'Admin logged in unsuccessfully.', NULL, 'login', NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-09-11 12:00:13', '2025-09-11 12:00:13'),
(552, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-09-11 12:00:28', '2025-09-11 12:00:28'),
(553, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"152.32.126.19\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-09-11 15:06:15', '2025-09-11 15:06:15'),
(554, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"152.32.126.19\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-09-11 15:06:26', '2025-09-11 15:06:26'),
(555, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"152.32.126.19\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-09-11 15:06:52', '2025-09-11 15:06:52'),
(556, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"152.32.126.19\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-09-11 15:06:59', '2025-09-11 15:06:59'),
(557, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"152.32.126.19\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-09-11 15:07:29', '2025-09-11 15:07:29'),
(558, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 71, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-09-11 15:09:12', '2025-09-11 15:09:12'),
(559, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"admin@example.com\",\"section\":\"Login\"}', NULL, '2025-09-11 15:11:01', '2025-09-11 15:11:01'),
(560, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"admin@example.com\",\"section\":\"Login\"}', NULL, '2025-09-11 15:11:07', '2025-09-11 15:11:07'),
(561, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"admin@example.com\",\"section\":\"Login\"}', NULL, '2025-09-11 15:11:16', '2025-09-11 15:11:16'),
(562, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-09-11 15:11:30', '2025-09-11 15:11:30'),
(563, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-09-11 17:49:26', '2025-09-11 17:49:26'),
(564, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"regioncarpersonnel@gmail.com\",\"section\":\"Login\"}', NULL, '2025-09-18 08:19:49', '2025-09-18 08:19:49'),
(565, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-09-18 16:13:16', '2025-09-18 16:13:16'),
(566, 'default', 'Started registration and sent OTP.', NULL, 'register', NULL, NULL, NULL, '{\"ip\":\"152.32.126.19\",\"email\":\"billyferreol@dgmail.com\",\"section\":\"Register\"}', NULL, '2025-10-17 10:05:57', '2025-10-17 10:05:57'),
(567, 'default', 'Viewed OTP input form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"152.32.126.19\",\"email\":\"billyferreol@dgmail.com\",\"section\":\"Register\"}', NULL, '2025-10-17 10:05:57', '2025-10-17 10:05:57'),
(568, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"49.150.200.111\",\"section\":\"Google Login\"}', NULL, '2025-10-18 14:39:06', '2025-10-18 14:39:06'),
(569, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 57, '{\"ip\":\"49.150.75.6\",\"section\":\"Google Login\"}', NULL, '2025-10-18 14:43:17', '2025-10-18 14:43:17'),
(570, 'default', 'Started registration and sent OTP.', NULL, 'register', NULL, NULL, NULL, '{\"ip\":\"49.150.200.111\",\"email\":\"ejcabanela2nd@gmail.com\",\"section\":\"Register\"}', NULL, '2025-10-18 14:57:56', '2025-10-18 14:57:56'),
(571, 'default', 'Viewed OTP input form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"49.150.200.111\",\"email\":\"ejcabanela2nd@gmail.com\",\"section\":\"Register\"}', NULL, '2025-10-18 14:57:56', '2025-10-18 14:57:56'),
(572, 'default', 'Started registration and sent OTP.', NULL, 'register', NULL, NULL, NULL, '{\"ip\":\"49.150.200.111\",\"email\":\"ejcabanela2nd@gmail.com\",\"section\":\"Register\"}', NULL, '2025-10-18 15:07:51', '2025-10-18 15:07:51'),
(573, 'default', 'Viewed OTP input form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"49.150.200.111\",\"email\":\"ejcabanela2nd@gmail.com\",\"section\":\"Register\"}', NULL, '2025-10-18 15:07:51', '2025-10-18 15:07:51'),
(574, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.75.6\",\"section\":\"Google Login\"}', NULL, '2025-10-18 19:15:17', '2025-10-18 19:15:17'),
(575, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"49.150.200.111\",\"section\":\"Google Login\"}', NULL, '2025-10-18 19:41:08', '2025-10-18 19:41:08'),
(576, 'default', 'Started registration and sent OTP.', NULL, 'register', NULL, NULL, NULL, '{\"ip\":\"49.150.200.111\",\"email\":\"ejcabanela2nd@gmail.com\",\"section\":\"Register\"}', NULL, '2025-10-18 20:24:11', '2025-10-18 20:24:11'),
(577, 'default', 'Viewed OTP input form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"49.150.200.111\",\"email\":\"ejcabanela2nd@gmail.com\",\"section\":\"Register\"}', NULL, '2025-10-18 20:24:11', '2025-10-18 20:24:11'),
(578, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"120.29.90.58\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-10-18 20:40:30', '2025-10-18 20:40:30'),
(579, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"120.29.90.58\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-10-18 20:41:13', '2025-10-18 20:41:13'),
(580, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"120.29.90.58\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-10-18 20:41:46', '2025-10-18 20:41:46'),
(581, 'default', 'login', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2025-10-18 20:43:32', '2025-10-18 20:43:32'),
(582, 'default', 'User logged in successfully.', NULL, 'login', NULL, 'App\\Models\\User', 1, '[]', NULL, '2025-10-18 20:43:32', '2025-10-18 20:43:32'),
(583, 'default', 'Admin logged in unsuccessfully.', NULL, 'login', NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-10-18 20:50:01', '2025-10-18 20:50:01'),
(584, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-10-18 20:50:23', '2025-10-18 20:50:23'),
(585, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 71, '{\"ip\":\"112.199.57.98\",\"section\":\"Google Login\"}', NULL, '2025-10-21 15:13:57', '2025-10-21 15:13:57'),
(586, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-10-21 15:14:43', '2025-10-21 15:14:43'),
(587, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 18, 'App\\Models\\Admin', 1, '{\"user_id\":\"5\",\"vacancy_id\":\"A-017\",\"section\":\"Application List\"}', NULL, '2025-10-21 15:15:03', '2025-10-21 15:15:03'),
(588, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 17, 'App\\Models\\Admin', 1, '{\"user_id\":\"61\",\"vacancy_id\":\"LV-016\",\"section\":\"Application List\"}', NULL, '2025-10-21 15:15:31', '2025-10-21 15:15:31'),
(589, 'default', 'Managed exam participants and details.', NULL, NULL, NULL, NULL, NULL, '{\"vacancy_id\":\"A-017\",\"section\":\"Exam Management\"}', NULL, '2025-10-21 15:15:57', '2025-10-21 15:15:57'),
(590, 'default', 'Accessed edit exam page.', NULL, 'view', NULL, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-017\",\"section\":\"Exam Management\"}', NULL, '2025-10-21 15:16:03', '2025-10-21 15:16:03'),
(591, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 17, 'App\\Models\\Admin', 1, '{\"position_title\":\"ADAC-RTA\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:37', '2025-10-21 15:16:37'),
(592, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 16, 'App\\Models\\Admin', 1, '{\"position_title\":\"LGOO V\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:39', '2025-10-21 15:16:39'),
(593, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 15, 'App\\Models\\Admin', 1, '{\"position_title\":\"Local Government Operations Officer V\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:41', '2025-10-21 15:16:41'),
(594, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 7, 'App\\Models\\Admin', 1, '{\"position_title\":\"Precision Printing Worker\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:43', '2025-10-21 15:16:43'),
(595, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 8, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"VAC-008\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:44', '2025-10-21 15:16:44'),
(596, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 8, 'App\\Models\\Admin', 1, '{\"position_title\":\"Social Worker\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:47', '2025-10-21 15:16:47'),
(597, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 9, 'App\\Models\\Admin', 1, '{\"position_title\":\"Psychiatric Aide\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:50', '2025-10-21 15:16:50'),
(598, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 10, 'App\\Models\\Admin', 1, '{\"position_title\":\"Rotary Drill Operator\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:52', '2025-10-21 15:16:52'),
(599, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 11, 'App\\Models\\Admin', 1, '{\"position_title\":\"Music Composer\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:54', '2025-10-21 15:16:54'),
(600, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 2, 'App\\Models\\Admin', 1, '{\"position_title\":\"Electrical Parts Reconditioner\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:56', '2025-10-21 15:16:56'),
(601, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 4, 'App\\Models\\Admin', 1, '{\"position_title\":\"Medical Laboratory Technologist\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:16:58', '2025-10-21 15:16:58'),
(602, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 5, 'App\\Models\\Admin', 1, '{\"position_title\":\"Postmasters\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:17:00', '2025-10-21 15:17:00'),
(603, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 6, 'App\\Models\\Admin', 1, '{\"position_title\":\"Ship Engineer\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:17:03', '2025-10-21 15:17:03'),
(604, 'default', 'Created new job vacancy.', 'App\\Models\\JobVacancy', 'create', 18, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"II-018\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:19:03', '2025-10-21 15:19:03'),
(605, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 18, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"II-018\",\"section\":\"Job Vacancy\"}', NULL, '2025-10-21 15:19:13', '2025-10-21 15:19:13'),
(606, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-10-21 15:20:13', '2025-10-21 15:20:13'),
(607, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 71, '{\"ip\":\"112.199.57.98\",\"section\":\"Google Login\"}', NULL, '2025-10-21 15:20:25', '2025-10-21 15:20:25'),
(608, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 71, '[]', NULL, '2025-10-21 15:33:04', '2025-10-21 15:33:04'),
(609, 'default', 'Admin logged in unsuccessfully.', NULL, 'login', NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-10-22 11:20:28', '2025-10-22 11:20:28'),
(610, 'default', 'Admin logged in unsuccessfully.', NULL, 'login', NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-10-22 11:20:39', '2025-10-22 11:20:39'),
(611, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-10-22 11:28:00', '2025-10-22 11:28:00'),
(612, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-10-22 11:43:50', '2025-10-22 11:43:50'),
(613, 'default', 'Accessed edit exam page.', NULL, 'view', NULL, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"II-018\",\"section\":\"Exam Management\"}', NULL, '2025-10-22 11:44:29', '2025-10-22 11:44:29'),
(614, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-10-22 11:45:48', '2025-10-22 11:45:48'),
(615, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"216.247.95.203\",\"section\":\"Google Login\"}', NULL, '2025-10-25 17:14:17', '2025-10-25 17:14:17'),
(616, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 58, '{\"ip\":\"136.158.121.108\",\"section\":\"Google Login\"}', NULL, '2025-10-25 23:40:38', '2025-10-25 23:40:38'),
(617, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 58, '{\"ip\":\"136.158.121.108\",\"section\":\"Google Login\"}', NULL, '2025-10-25 23:41:27', '2025-10-25 23:41:27'),
(618, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 72, '{\"ip\":\"2001:4452:2b0:8100:a120:f8c5:f9eb:1890\",\"section\":\"Google Login\"}', NULL, '2025-10-27 21:32:40', '2025-10-27 21:32:40'),
(619, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"112.202.60.224\",\"section\":\"Google Login\"}', NULL, '2025-10-27 21:33:06', '2025-10-27 21:33:06'),
(620, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 73, '{\"ip\":\"136.158.121.108\",\"section\":\"Google Login\"}', NULL, '2025-11-02 14:55:54', '2025-11-02 14:55:54'),
(621, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 74, '{\"ip\":\"152.32.126.53\",\"section\":\"Google Login\"}', NULL, '2025-11-03 09:55:08', '2025-11-03 09:55:08'),
(622, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 74, '[]', NULL, '2025-11-03 09:57:39', '2025-11-03 09:57:39'),
(623, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 74, '[]', NULL, '2025-11-03 09:59:21', '2025-11-03 09:59:21'),
(624, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 74, '[]', NULL, '2025-11-03 10:01:37', '2025-11-03 10:01:37'),
(625, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 74, '[]', NULL, '2025-11-03 10:01:37', '2025-11-03 10:01:37'),
(626, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 74, '[]', NULL, '2025-11-03 10:02:15', '2025-11-03 10:02:15'),
(627, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 74, '[]', NULL, '2025-11-03 10:02:37', '2025-11-03 10:02:37'),
(628, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 74, '[]', NULL, '2025-11-03 10:02:37', '2025-11-03 10:02:37'),
(629, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-11-03 10:04:23', '2025-11-03 10:04:23'),
(630, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 18, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"II-018\",\"section\":\"Job Vacancy\"}', NULL, '2025-11-03 10:04:34', '2025-11-03 10:04:34'),
(631, 'default', 'Updated job vacancy fields.', 'App\\Models\\JobVacancy', 'edit', 18, 'App\\Models\\Admin', 1, '{\"changes\":{\"closing_date\":{\"old\":\"2025-10-30T16:00:00.000000Z\",\"new\":\"2025-11-07\"}},\"section\":\"Job Vacancy\"}', NULL, '2025-11-03 10:04:44', '2025-11-03 10:04:44'),
(632, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-11-03 10:05:26', '2025-11-03 10:05:26'),
(633, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 74, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-11-03 10:05:40', '2025-11-03 10:05:40'),
(634, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 18, 'App\\Models\\User', 74, '{\"vacancy_id\":\"II-018\",\"section\":\"Job Vacancy\"}', NULL, '2025-11-03 10:06:00', '2025-11-03 10:06:00'),
(635, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-11-03 10:06:50', '2025-11-03 10:06:50'),
(636, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 19, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"II-018\",\"section\":\"Application List\"}', NULL, '2025-11-03 10:07:25', '2025-11-03 10:07:25'),
(637, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 19, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"II-018\",\"section\":\"Application List\"}', NULL, '2025-11-03 11:06:20', '2025-11-03 11:06:20'),
(638, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 19, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"II-018\",\"section\":\"Application List\"}', NULL, '2025-11-03 11:06:26', '2025-11-03 11:06:26'),
(639, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-11-03 11:06:28', '2025-11-03 11:06:28'),
(640, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"111.90.197.64\",\"email\":\"samplepass@example.com\",\"section\":\"Login\"}', NULL, '2025-11-11 15:31:23', '2025-11-11 15:31:23'),
(641, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 74, '{\"ip\":\"103.80.142.155\",\"section\":\"Google Login\"}', NULL, '2025-11-13 07:45:29', '2025-11-13 07:45:29'),
(642, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-11-13 07:48:31', '2025-11-13 07:48:31'),
(643, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 18, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"II-018\",\"section\":\"Job Vacancy\"}', NULL, '2025-11-13 07:49:01', '2025-11-13 07:49:01'),
(644, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 18, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"II-018\",\"section\":\"Job Vacancy\"}', NULL, '2025-11-13 07:49:14', '2025-11-13 07:49:14'),
(645, 'default', 'Updated job vacancy fields.', 'App\\Models\\JobVacancy', 'edit', 18, 'App\\Models\\Admin', 1, '{\"changes\":{\"closing_date\":{\"old\":\"2025-11-06T16:00:00.000000Z\",\"new\":\"2025-11-15\"}},\"section\":\"Job Vacancy\"}', NULL, '2025-11-13 07:49:21', '2025-11-13 07:49:21'),
(646, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-11-13 07:49:27', '2025-11-13 07:49:27'),
(647, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 74, '{\"ip\":\"103.80.142.155\",\"section\":\"Google Login\"}', NULL, '2025-11-13 07:49:33', '2025-11-13 07:49:33'),
(648, 'default', 'Uploaded application documents (Admin).', NULL, 'save', NULL, 'App\\Models\\User', 74, '{\"user_id\":\"74\",\"vacancy_id\":\"II-018\",\"section\":\"Personal Data Sheet\"}', NULL, '2025-11-13 07:51:18', '2025-11-13 07:51:18'),
(649, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-11-13 07:52:04', '2025-11-13 07:52:04'),
(650, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 18, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"II-018\",\"section\":\"Job Vacancy\"}', NULL, '2025-11-13 07:52:08', '2025-11-13 07:52:08'),
(651, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 19, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"II-018\",\"section\":\"Application List\"}', NULL, '2025-11-13 07:52:16', '2025-11-13 07:52:16'),
(652, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-11-13 07:52:39', '2025-11-13 07:52:39'),
(653, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 74, '{\"ip\":\"103.80.142.155\",\"section\":\"Google Login\"}', NULL, '2025-11-13 07:52:48', '2025-11-13 07:52:48'),
(654, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-11-13 07:54:35', '2025-11-13 07:54:35'),
(655, 'default', 'Created new job vacancy.', 'App\\Models\\JobVacancy', 'create', 19, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-019\",\"section\":\"Job Vacancy\"}', NULL, '2025-11-13 07:55:11', '2025-11-13 07:55:11'),
(656, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 19, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-019\",\"section\":\"Job Vacancy\"}', NULL, '2025-11-13 07:55:19', '2025-11-13 07:55:19'),
(657, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 19, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-019\",\"section\":\"Job Vacancy\"}', NULL, '2025-11-13 07:55:25', '2025-11-13 07:55:25'),
(658, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-11-13 07:56:01', '2025-11-13 07:56:01'),
(659, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 74, '{\"ip\":\"103.80.142.155\",\"section\":\"Google Login\"}', NULL, '2025-11-13 07:56:09', '2025-11-13 07:56:09'),
(660, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 19, 'App\\Models\\User', 74, '{\"vacancy_id\":\"A-019\",\"section\":\"Job Vacancy\"}', NULL, '2025-11-13 07:56:23', '2025-11-13 07:56:23'),
(661, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-11-13 07:58:14', '2025-11-13 07:58:14'),
(662, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 20, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"A-019\",\"section\":\"Application List\"}', NULL, '2025-11-13 07:58:26', '2025-11-13 07:58:26'),
(663, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 74, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"A-019\",\"changes\":{\"deadline_time\":{\"old\":null,\"new\":\"07:58\"},\"qs_education\":{\"old\":null,\"new\":\"yes\"},\"qs_eligibility\":{\"old\":null,\"new\":\"yes\"},\"qs_experience\":{\"old\":null,\"new\":\"yes\"},\"qs_training\":{\"old\":null,\"new\":\"yes\"},\"qs_result\":{\"old\":null,\"new\":\"qualified\"},\"application_remarks\":{\"old\":null,\"new\":\"please comply hbwdwabawdkawja\"},\"application_letter_status\":{\"old\":\"Submitted\",\"new\":\"Disapproved With Deficiency\"},\"application_letter_remarks\":{\"old\":null,\"new\":\"mali ung docs\"},\"document_transcript_records\":{\"remarks\":{\"old\":\"\",\"new\":\"lla_a71ad0d6-5d10-4c30-883f-ba18b96e2de6.pdf\"}},\"document_photocopy_diploma\":{\"remarks\":{\"old\":\"\",\"new\":\"lla_a71ad0d6-5d10-4c30-883f-ba18b96e2de6.pdf\"}}},\"section\":\"Application List\"}', NULL, '2025-11-13 07:59:44', '2025-11-13 07:59:44'),
(664, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 20, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"A-019\",\"section\":\"Application List\"}', NULL, '2025-11-13 07:59:44', '2025-11-13 07:59:44'),
(665, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-11-13 07:59:48', '2025-11-13 07:59:48'),
(666, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 74, '{\"ip\":\"103.80.142.155\",\"section\":\"Google Login\"}', NULL, '2025-11-13 07:59:57', '2025-11-13 07:59:57'),
(667, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-11-13 08:01:37', '2025-11-13 08:01:37'),
(668, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 19, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-019\",\"section\":\"Job Vacancy\"}', NULL, '2025-11-13 08:01:45', '2025-11-13 08:01:45'),
(669, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 20, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"A-019\",\"section\":\"Application List\"}', NULL, '2025-11-13 08:01:54', '2025-11-13 08:01:54'),
(670, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 74, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"A-019\",\"changes\":{\"application_letter_status\":{\"old\":\"Disapproved With Deficiency\",\"new\":\"Okay\\/Confirmed\"}},\"section\":\"Application List\"}', NULL, '2025-11-13 08:02:02', '2025-11-13 08:02:02'),
(671, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 20, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"A-019\",\"section\":\"Application List\"}', NULL, '2025-11-13 08:02:03', '2025-11-13 08:02:03'),
(672, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 20, 'App\\Models\\Admin', 1, '{\"user_id\":\"74\",\"vacancy_id\":\"A-019\",\"section\":\"Application List\"}', NULL, '2025-11-13 08:02:30', '2025-11-13 08:02:30'),
(673, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.76.38\",\"section\":\"Google Login\"}', NULL, '2025-11-13 08:17:29', '2025-11-13 08:17:29'),
(674, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 56, '{\"ip\":\"136.158.121.86\",\"section\":\"Google Login\"}', NULL, '2025-11-14 12:12:57', '2025-11-14 12:12:57'),
(675, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 57, '{\"ip\":\"49.150.76.38\",\"section\":\"Google Login\"}', NULL, '2025-11-15 12:26:36', '2025-11-15 12:26:36'),
(676, 'default', 'Created Work Experience Sheet', NULL, 'Create', NULL, 'App\\Models\\User', 57, '{\"entries_count\":1,\"action_type\":\"Create\",\"section\":\"Work Experience Sheet\"}', NULL, '2025-11-15 12:27:51', '2025-11-15 12:27:51'),
(677, 'default', 'Exported Work Experience Sheet.', NULL, 'export', NULL, 'App\\Models\\User', 57, '{\"exported_file\":\"WorkExperienceSheet.docx\",\"entries_count\":1,\"section\":\"Export\"}', NULL, '2025-11-15 12:27:52', '2025-11-15 12:27:52'),
(678, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.76.38\",\"section\":\"Google Login\"}', NULL, '2025-11-29 21:17:03', '2025-11-29 21:17:03'),
(679, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 75, '{\"ip\":\"2001:4452:5f0:4600:dcd0:eb98:8333:caf7\",\"section\":\"Google Login\"}', NULL, '2025-12-22 01:40:36', '2025-12-22 01:40:36'),
(680, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-12-22 10:13:34', '2025-12-22 10:13:34'),
(681, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-12-22 10:14:04', '2025-12-22 10:14:04'),
(682, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"144.48.30.203\",\"email\":\"admin@debug.com\",\"section\":\"Login\"}', NULL, '2025-12-22 10:14:17', '2025-12-22 10:14:17'),
(683, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-12-22 10:14:35', '2025-12-22 10:14:35'),
(684, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-12-22 10:16:41', '2025-12-22 10:16:41'),
(685, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-12-22 10:25:33', '2025-12-22 10:25:33'),
(686, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-12-22 16:43:10', '2025-12-22 16:43:10'),
(687, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2025-12-22 16:44:31', '2025-12-22 16:44:31'),
(688, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 76, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2025-12-22 16:45:14', '2025-12-22 16:45:14'),
(689, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2025-12-26 10:23:39', '2025-12-26 10:23:39'),
(690, 'default', 'Accessed edit exam page.', NULL, 'view', NULL, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"A-019\",\"section\":\"Exam Management\"}', NULL, '2025-12-26 11:49:06', '2025-12-26 11:49:06'),
(691, 'default', 'Managed exam participants and details.', NULL, NULL, NULL, NULL, NULL, '{\"vacancy_id\":\"II-018\",\"section\":\"Exam Management\"}', NULL, '2025-12-26 11:49:27', '2025-12-26 11:49:27'),
(692, 'default', 'Accessed edit exam page.', NULL, 'view', NULL, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"II-018\",\"section\":\"Exam Management\"}', NULL, '2025-12-26 13:00:13', '2025-12-26 13:00:13'),
(693, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 71, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2026-01-06 13:19:54', '2026-01-06 13:19:54'),
(694, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2026-01-06 13:22:08', '2026-01-06 13:22:08'),
(695, 'default', 'Exported job vacancies of type COS', NULL, 'export', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Vacancies Management\"}', NULL, '2026-01-06 13:28:26', '2026-01-06 13:28:26'),
(696, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 19, 'App\\Models\\Admin', 1, '{\"position_title\":\"awdaw\",\"section\":\"Job Vacancy\"}', NULL, '2026-01-06 13:28:40', '2026-01-06 13:28:40'),
(697, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 18, 'App\\Models\\Admin', 1, '{\"position_title\":\"ISA I\",\"section\":\"Job Vacancy\"}', NULL, '2026-01-06 13:28:48', '2026-01-06 13:28:48'),
(698, 'default', 'Download template for job vacancies of type COS', NULL, 'download', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Vacancies Management\"}', NULL, '2026-01-06 13:29:08', '2026-01-06 13:29:08'),
(699, 'default', 'Imported job vacancies of type COS', NULL, 'import', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Vacancies Management\"}', NULL, '2026-01-06 13:29:48', '2026-01-06 13:29:48'),
(700, 'default', 'Created new job vacancy.', 'App\\Models\\JobVacancy', 'create', 20, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"EIV-020\",\"section\":\"Job Vacancy\"}', NULL, '2026-01-06 13:31:20', '2026-01-06 13:31:20'),
(701, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 71, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2026-01-06 13:32:27', '2026-01-06 13:32:27'),
(702, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 71, '[]', NULL, '2026-01-06 13:38:34', '2026-01-06 13:38:34'),
(703, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 71, '[]', NULL, '2026-01-06 13:40:09', '2026-01-06 13:40:09'),
(704, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 71, '[]', NULL, '2026-01-06 13:42:38', '2026-01-06 13:42:38'),
(705, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 71, '[]', NULL, '2026-01-06 13:42:41', '2026-01-06 13:42:41'),
(706, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 71, '[]', NULL, '2026-01-06 13:42:42', '2026-01-06 13:42:42'),
(707, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 71, '[]', NULL, '2026-01-06 13:42:57', '2026-01-06 13:42:57'),
(708, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 71, '[]', NULL, '2026-01-06 13:42:57', '2026-01-06 13:42:57'),
(709, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 71, '[]', NULL, '2026-01-06 13:43:20', '2026-01-06 13:43:20'),
(710, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 71, '[]', NULL, '2026-01-06 13:43:20', '2026-01-06 13:43:20'),
(711, 'default', 'Created Work Experience Sheet', NULL, 'Create', NULL, 'App\\Models\\User', 71, '{\"entries_count\":1,\"action_type\":\"Create\",\"section\":\"Work Experience Sheet\"}', NULL, '2026-01-06 13:43:53', '2026-01-06 13:43:53'),
(712, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 71, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2026-01-06 13:44:24', '2026-01-06 13:44:24'),
(713, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 20, 'App\\Models\\User', 71, '{\"vacancy_id\":\"EIV-020\",\"section\":\"Job Vacancy\"}', NULL, '2026-01-06 13:44:52', '2026-01-06 13:44:52'),
(714, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 21, 'App\\Models\\Admin', 1, '{\"user_id\":\"71\",\"vacancy_id\":\"EIV-020\",\"section\":\"Application List\"}', NULL, '2026-01-06 13:45:22', '2026-01-06 13:45:22'),
(715, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 71, 'App\\Models\\Admin', 1, '{\"user_id\":\"71\",\"vacancy_id\":\"EIV-020\",\"changes\":{\"status\":{\"old\":\"Pending\",\"new\":\"Incomplete\"},\"deadline_date\":{\"old\":null,\"new\":\"2026-01-07\"},\"deadline_time\":{\"old\":null,\"new\":\"17:01\"},\"qs_education\":{\"old\":null,\"new\":\"yes\"},\"qs_eligibility\":{\"old\":null,\"new\":\"yes\"},\"qs_experience\":{\"old\":null,\"new\":\"yes\"},\"qs_training\":{\"old\":null,\"new\":\"yes\"},\"qs_result\":{\"old\":null,\"new\":\"not qualified\"},\"application_remarks\":{\"old\":null,\"new\":\"dawdawdawdawdaw\"},\"application_letter_status\":{\"old\":\"Submitted\",\"new\":\"Okay\\/Confirmed\"},\"application_letter_remarks\":{\"old\":null,\"new\":\"No remarks provided.\"},\"document_pqe_result\":{\"remarks\":{\"old\":\"\",\"new\":\"REAP FORM.pdf\"}},\"document_transcript_records\":{\"remarks\":{\"old\":\"\",\"new\":\"REAP FORM.pdf\"}},\"document_photocopy_diploma\":{\"remarks\":{\"old\":\"\",\"new\":\"REAP FORM.pdf\"}}},\"section\":\"Application List\"}', NULL, '2026-01-06 13:46:38', '2026-01-06 13:46:38'),
(716, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 21, 'App\\Models\\Admin', 1, '{\"user_id\":\"71\",\"vacancy_id\":\"EIV-020\",\"section\":\"Application List\"}', NULL, '2026-01-06 13:46:38', '2026-01-06 13:46:38'),
(717, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 71, '[]', NULL, '2026-01-06 13:48:33', '2026-01-06 13:48:33'),
(718, 'default', 'Uploaded application documents (Admin).', NULL, 'save', NULL, 'App\\Models\\User', 71, '{\"user_id\":\"71\",\"vacancy_id\":\"EIV-020\",\"section\":\"Personal Data Sheet\"}', NULL, '2026-01-06 13:48:33', '2026-01-06 13:48:33'),
(719, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 21, 'App\\Models\\Admin', 1, '{\"user_id\":\"71\",\"vacancy_id\":\"EIV-020\",\"section\":\"Application List\"}', NULL, '2026-01-06 13:48:43', '2026-01-06 13:48:43'),
(720, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 71, 'App\\Models\\Admin', 1, '{\"user_id\":\"71\",\"vacancy_id\":\"EIV-020\",\"changes\":{\"status\":{\"old\":\"Incomplete\",\"new\":\"Complete\"},\"qs_result\":{\"old\":\"not qualified\",\"new\":\"qualified\"},\"application_remarks\":{\"old\":\"dawdawdawdawdaw\",\"new\":\"Pleaseghwheawkdaw\"},\"document_signed_pds\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}},\"document_signed_work_exp_sheet\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}},\"document_pqe_result\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"}},\"document_cert_eligibility\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}},\"document_ipcr\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}},\"document_non_academic\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}},\"document_cert_training\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}},\"document_designation_order\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}},\"document_transcript_records\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"}},\"document_photocopy_diploma\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"}},\"document_grade_masteraldoctorate\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}},\"document_tor_masteraldoctorate\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}},\"document_cert_employment\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}},\"document_other_documents\":{\"status\":{\"old\":\"PENDING\",\"new\":\"Okay\\/Confirmed\"},\"remarks\":{\"old\":\"\",\"new\":\"ExportPDS_2026-01-06_134424.pdf\"}}},\"section\":\"Application List\"}', NULL, '2026-01-06 13:49:44', '2026-01-06 13:49:44'),
(721, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 21, 'App\\Models\\Admin', 1, '{\"user_id\":\"71\",\"vacancy_id\":\"EIV-020\",\"section\":\"Application List\"}', NULL, '2026-01-06 13:49:44', '2026-01-06 13:49:44'),
(722, 'default', 'Accessed edit exam page.', NULL, 'view', NULL, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"EIV-020\",\"section\":\"Exam Management\"}', NULL, '2026-01-06 13:55:10', '2026-01-06 13:55:10'),
(723, 'default', 'Managed exam participants and details.', NULL, NULL, NULL, NULL, NULL, '{\"vacancy_id\":\"EIV-020\",\"section\":\"Exam Management\"}', NULL, '2026-01-06 13:56:04', '2026-01-06 13:56:04'),
(724, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 71, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2026-01-08 10:43:17', '2026-01-08 10:43:17'),
(725, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 59, '{\"ip\":\"49.150.73.102\",\"section\":\"Google Login\"}', NULL, '2026-01-08 17:42:33', '2026-01-08 17:42:33'),
(726, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2026-01-09 07:59:07', '2026-01-09 07:59:07'),
(727, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 21, 'App\\Models\\Admin', 1, '{\"user_id\":\"71\",\"vacancy_id\":\"EIV-020\",\"section\":\"Application List\"}', NULL, '2026-01-09 07:59:30', '2026-01-09 07:59:30'),
(728, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 21, 'App\\Models\\Admin', 1, '{\"user_id\":\"71\",\"vacancy_id\":\"EIV-020\",\"section\":\"Application List\"}', NULL, '2026-01-09 07:59:58', '2026-01-09 07:59:58'),
(729, 'default', 'Deleted job vacancy.', 'App\\Models\\JobVacancy', 'delete', 20, 'App\\Models\\Admin', 1, '{\"position_title\":\"Engineer IV\",\"section\":\"Job Vacancy\"}', NULL, '2026-01-09 08:00:14', '2026-01-09 08:00:14'),
(730, 'default', 'Created new job vacancy.', 'App\\Models\\JobVacancy', 'create', 21, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LGOOII-021\",\"section\":\"Job Vacancy\"}', NULL, '2026-01-09 08:20:17', '2026-01-09 08:20:17'),
(731, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 77, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2026-01-09 16:17:45', '2026-01-09 16:17:45'),
(732, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:21:19', '2026-01-09 16:21:19'),
(733, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:22:07', '2026-01-09 16:22:07'),
(734, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:23:57', '2026-01-09 16:23:57'),
(735, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:23:57', '2026-01-09 16:23:57'),
(736, 'default', 'Created Work Experience Sheet', NULL, 'Create', NULL, 'App\\Models\\User', 77, '{\"entries_count\":1,\"action_type\":\"Create\",\"section\":\"Work Experience Sheet\"}', NULL, '2026-01-09 16:24:52', '2026-01-09 16:24:52'),
(737, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:25:24', '2026-01-09 16:25:24'),
(738, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:25:25', '2026-01-09 16:25:25'),
(739, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:25:25', '2026-01-09 16:25:25'),
(740, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:25:25', '2026-01-09 16:25:25'),
(741, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:25:25', '2026-01-09 16:25:25');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(742, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:25:25', '2026-01-09 16:25:25'),
(743, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:25:59', '2026-01-09 16:25:59'),
(744, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:25:59', '2026-01-09 16:25:59'),
(745, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 21, 'App\\Models\\User', 77, '{\"vacancy_id\":\"LGOOII-021\",\"section\":\"Job Vacancy\"}', NULL, '2026-01-09 16:26:22', '2026-01-09 16:26:22'),
(746, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2026-01-09 16:27:02', '2026-01-09 16:27:02'),
(747, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 21, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LGOOII-021\",\"section\":\"Job Vacancy\"}', NULL, '2026-01-09 16:27:41', '2026-01-09 16:27:41'),
(748, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 22, 'App\\Models\\Admin', 1, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"section\":\"Application List\"}', NULL, '2026-01-09 16:28:05', '2026-01-09 16:28:05'),
(749, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 77, 'App\\Models\\Admin', 1, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"changes\":{\"status\":{\"old\":\"Pending\",\"new\":\"Incomplete\"},\"deadline_date\":{\"old\":null,\"new\":\"2026-01-13\"},\"deadline_time\":{\"old\":null,\"new\":\"17:00\"},\"qs_education\":{\"old\":null,\"new\":\"yes\"},\"qs_eligibility\":{\"old\":null,\"new\":\"yes\"},\"qs_experience\":{\"old\":null,\"new\":\"yes\"},\"qs_training\":{\"old\":null,\"new\":\"yes\"},\"qs_result\":{\"old\":null,\"new\":\"qualified\"},\"application_remarks\":{\"old\":null,\"new\":\"hawdhbawhdbawhbawdbawjdbawjhbdawhbawdaw\"},\"application_letter_status\":{\"old\":\"Submitted\",\"new\":\"Okay\\/Confirmed\"},\"application_letter_remarks\":{\"old\":null,\"new\":\"No remarks provided.\"},\"document_pqe_result\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_transcript_records\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_photocopy_diploma\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}}},\"section\":\"Application List\"}', NULL, '2026-01-09 16:29:40', '2026-01-09 16:29:40'),
(750, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 22, 'App\\Models\\Admin', 1, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"section\":\"Application List\"}', NULL, '2026-01-09 16:29:41', '2026-01-09 16:29:41'),
(751, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 77, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2026-01-09 16:31:52', '2026-01-09 16:31:52'),
(752, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:32:08', '2026-01-09 16:32:08'),
(753, 'default', 'Uploaded application documents (Admin).', NULL, 'save', NULL, 'App\\Models\\User', 77, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"section\":\"Personal Data Sheet\"}', NULL, '2026-01-09 16:32:08', '2026-01-09 16:32:08'),
(754, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:33:45', '2026-01-09 16:33:45'),
(755, 'default', 'Uploaded application documents (Admin).', NULL, 'save', NULL, 'App\\Models\\User', 77, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"section\":\"Personal Data Sheet\"}', NULL, '2026-01-09 16:33:45', '2026-01-09 16:33:45'),
(756, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 22, 'App\\Models\\Admin', 1, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"section\":\"Application List\"}', NULL, '2026-01-09 16:34:00', '2026-01-09 16:34:00'),
(757, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 77, 'App\\Models\\Admin', 1, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"changes\":{\"status\":{\"old\":\"Incomplete\",\"new\":\"Complete\"},\"document_signed_pds\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_signed_work_exp_sheet\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_cert_eligibility\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_ipcr\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_non_academic\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_cert_training\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_designation_order\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_grade_masteraldoctorate\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_tor_masteraldoctorate\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_cert_employment\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}},\"document_other_documents\":{\"remarks\":{\"old\":\"\",\"new\":\"Doc1.pdf\"}}},\"section\":\"Application List\"}', NULL, '2026-01-09 16:35:28', '2026-01-09 16:35:28'),
(758, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 22, 'App\\Models\\Admin', 1, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"section\":\"Application List\"}', NULL, '2026-01-09 16:35:29', '2026-01-09 16:35:29'),
(759, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 22, 'App\\Models\\Admin', 1, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"section\":\"Application List\"}', NULL, '2026-01-09 16:36:20', '2026-01-09 16:36:20'),
(760, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 22, 'App\\Models\\Admin', 1, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"section\":\"Application List\"}', NULL, '2026-01-09 16:37:19', '2026-01-09 16:37:19'),
(761, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:40:01', '2026-01-09 16:40:01'),
(762, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:40:01', '2026-01-09 16:40:01'),
(763, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 21, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LGOOII-021\",\"section\":\"Job Vacancy\"}', NULL, '2026-01-09 16:40:46', '2026-01-09 16:40:46'),
(764, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 21, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LGOOII-021\",\"section\":\"Job Vacancy\"}', NULL, '2026-01-09 16:40:54', '2026-01-09 16:40:54'),
(765, 'default', 'Updated job vacancy fields.', 'App\\Models\\JobVacancy', 'edit', 21, 'App\\Models\\Admin', 1, '{\"changes\":{\"closing_date\":{\"old\":\"2026-01-09T16:00:00.000000Z\",\"new\":\"2026-01-08\"}},\"section\":\"Job Vacancy\"}', NULL, '2026-01-09 16:41:04', '2026-01-09 16:41:04'),
(766, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 77, '[]', NULL, '2026-01-09 16:41:23', '2026-01-09 16:41:23'),
(767, 'default', 'Uploaded application documents (Admin).', NULL, 'save', NULL, 'App\\Models\\User', 77, '{\"user_id\":\"77\",\"vacancy_id\":\"LGOOII-021\",\"section\":\"Personal Data Sheet\"}', NULL, '2026-01-09 16:41:23', '2026-01-09 16:41:23'),
(768, 'default', 'Accessed edit exam page.', NULL, 'view', NULL, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LGOOII-021\",\"section\":\"Exam Management\"}', NULL, '2026-01-09 16:42:10', '2026-01-09 16:42:10'),
(769, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 77, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2026-01-09 16:50:22', '2026-01-09 16:50:22'),
(770, 'default', 'Exported Personal Data Sheet (PDS).', NULL, 'export', NULL, 'App\\Models\\User', 77, '{\"exported_file\":\"ExportPDS.pdf\",\"section\":\"Export\"}', NULL, '2026-01-09 16:51:05', '2026-01-09 16:51:05'),
(771, 'default', 'Sent OTP for password reset.', NULL, 'send', NULL, NULL, NULL, '{\"ip\":\"136.158.120.104\",\"email\":\"pagsolinganmark04@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2026-01-11 19:16:34', '2026-01-11 19:16:34'),
(772, 'default', 'Viewed OTP input form for password reset.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"136.158.120.104\",\"email\":\"pagsolinganmark04@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2026-01-11 19:16:35', '2026-01-11 19:16:35'),
(773, 'default', 'OTP verified for password reset.', NULL, 'verify', NULL, NULL, NULL, '{\"ip\":\"136.158.120.104\",\"email\":\"pagsolinganmark04@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2026-01-11 19:17:02', '2026-01-11 19:17:02'),
(774, 'default', 'Viewed password reset form.', NULL, 'view', NULL, NULL, NULL, '{\"ip\":\"136.158.120.104\",\"email\":\"pagsolinganmark04@gmail.com\",\"section\":\"Forgot Password\"}', NULL, '2026-01-11 19:17:02', '2026-01-11 19:17:02'),
(775, 'default', 'Failed login attempt.', NULL, 'login', NULL, NULL, NULL, '{\"ip\":\"136.158.120.104\",\"email\":\"fafaf@gmail.com\",\"section\":\"Login\"}', NULL, '2026-01-11 19:21:47', '2026-01-11 19:21:47'),
(776, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 71, '{\"ip\":\"152.32.126.14\",\"section\":\"Google Login\"}', NULL, '2026-01-19 11:18:19', '2026-01-19 11:18:19'),
(777, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2026-02-02 13:05:38', '2026-02-02 13:05:38'),
(778, 'default', 'Admin logged out.', NULL, NULL, NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2026-02-02 13:11:57', '2026-02-02 13:11:57'),
(779, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 78, '{\"ip\":\"144.48.30.203\",\"section\":\"Google Login\"}', NULL, '2026-02-02 13:12:18', '2026-02-02 13:12:18'),
(780, 'default', 'login through google', NULL, 'login', NULL, 'App\\Models\\User', 79, '{\"ip\":\"152.32.126.14\",\"section\":\"Google Login\"}', NULL, '2026-02-02 13:29:30', '2026-02-02 13:29:30'),
(781, 'default', 'Updated C1 form session.', NULL, NULL, NULL, 'App\\Models\\User', 79, '[]', NULL, '2026-02-02 13:34:37', '2026-02-02 13:34:37'),
(782, 'default', 'Updated C2 form session.', NULL, NULL, NULL, 'App\\Models\\User', 79, '[]', NULL, '2026-02-02 13:35:40', '2026-02-02 13:35:40'),
(783, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 79, '[]', NULL, '2026-02-02 13:38:28', '2026-02-02 13:38:28'),
(784, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 79, '[]', NULL, '2026-02-02 13:38:28', '2026-02-02 13:38:28'),
(785, 'default', 'Admin logged in unsuccessfully.', NULL, 'login', NULL, NULL, NULL, '{\"section\":\"Login\"}', NULL, '2026-02-02 13:39:17', '2026-02-02 13:39:17'),
(786, 'default', 'Admin logged in successfully.', NULL, 'login', NULL, 'App\\Models\\Admin', 1, '{\"section\":\"Login\"}', NULL, '2026-02-02 13:39:38', '2026-02-02 13:39:38'),
(787, 'default', 'Accessed edit exam page.', NULL, 'view', NULL, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"LGOOII-021\",\"section\":\"Exam Management\"}', NULL, '2026-02-02 13:40:14', '2026-02-02 13:40:14'),
(788, 'default', 'Created new job vacancy.', 'App\\Models\\JobVacancy', 'create', 22, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"ISAIII-022\",\"section\":\"Job Vacancy\"}', NULL, '2026-02-02 13:45:28', '2026-02-02 13:45:28'),
(789, 'default', 'Created Work Experience Sheet', NULL, 'Create', NULL, 'App\\Models\\User', 79, '{\"entries_count\":1,\"action_type\":\"Create\",\"section\":\"Work Experience Sheet\"}', NULL, '2026-02-02 13:47:18', '2026-02-02 13:47:18'),
(790, 'default', 'Updated Work Experience Sheet', NULL, 'Update', NULL, 'App\\Models\\User', 79, '{\"entries_count\":1,\"action_type\":\"Update\",\"section\":\"Work Experience Sheet\"}', NULL, '2026-02-02 13:47:21', '2026-02-02 13:47:21'),
(791, 'default', 'Exported Work Experience Sheet.', NULL, 'export', NULL, 'App\\Models\\User', 79, '{\"exported_file\":\"WorkExperienceSheet.docx\",\"entries_count\":1,\"section\":\"Export\"}', NULL, '2026-02-02 13:47:21', '2026-02-02 13:47:21'),
(792, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 79, '[]', NULL, '2026-02-02 13:49:41', '2026-02-02 13:49:41'),
(793, 'default', 'Finalized PDS submission.', NULL, 'save', NULL, 'App\\Models\\User', 79, '[]', NULL, '2026-02-02 13:49:41', '2026-02-02 13:49:41'),
(794, 'default', 'Applied to job vacancy.', 'App\\Models\\JobVacancy', 'apply job', 22, 'App\\Models\\User', 79, '{\"vacancy_id\":\"ISAIII-022\",\"section\":\"Job Vacancy\"}', NULL, '2026-02-02 13:50:01', '2026-02-02 13:50:01'),
(795, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 22, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"ISAIII-022\",\"section\":\"Job Vacancy\"}', NULL, '2026-02-02 13:53:00', '2026-02-02 13:53:00'),
(796, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 23, 'App\\Models\\Admin', 1, '{\"user_id\":\"79\",\"vacancy_id\":\"ISAIII-022\",\"section\":\"Application List\"}', NULL, '2026-02-02 13:53:19', '2026-02-02 13:53:19'),
(797, 'default', 'Editing job vacancy.', 'App\\Models\\JobVacancy', 'view', 22, 'App\\Models\\Admin', 1, '{\"vacancy_id\":\"ISAIII-022\",\"section\":\"Job Vacancy\"}', NULL, '2026-02-02 13:54:24', '2026-02-02 13:54:24'),
(798, 'default', 'Updated applicant status and documents.', 'App\\Models\\User', 'update', 79, 'App\\Models\\Admin', 1, '{\"user_id\":\"79\",\"vacancy_id\":\"ISAIII-022\",\"changes\":{\"deadline_time\":{\"old\":null,\"new\":\"13:53\"},\"qs_education\":{\"old\":null,\"new\":\"yes\"},\"qs_eligibility\":{\"old\":null,\"new\":\"yes\"},\"qs_experience\":{\"old\":null,\"new\":\"yes\"},\"qs_training\":{\"old\":null,\"new\":\"yes\"},\"qs_result\":{\"old\":null,\"new\":\"not qualified\"},\"application_remarks\":{\"old\":null,\"new\":\"Pleaseckjanwdadawhdaw hjdwbakdjnawldnawdaw kajwdawknddbadakk  jakjdna\"},\"application_letter_status\":{\"old\":\"Submitted\",\"new\":\"Okay\\/Confirmed\"},\"application_letter_remarks\":{\"old\":null,\"new\":\"No remarks provided.\"},\"document_transcript_records\":{\"remarks\":{\"old\":\"\",\"new\":\"img20260120_16045925_signed.pdf\"}},\"document_photocopy_diploma\":{\"remarks\":{\"old\":\"\",\"new\":\"img20260120_16045925_signed.pdf\"}}},\"section\":\"Application List\"}', NULL, '2026-02-02 13:57:38', '2026-02-02 13:57:38'),
(799, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 23, 'App\\Models\\Admin', 1, '{\"user_id\":\"79\",\"vacancy_id\":\"ISAIII-022\",\"section\":\"Application List\"}', NULL, '2026-02-02 13:57:39', '2026-02-02 13:57:39'),
(800, 'default', 'Store C5 form.', NULL, NULL, NULL, 'App\\Models\\User', 79, '[]', NULL, '2026-02-02 13:58:46', '2026-02-02 13:58:46'),
(801, 'default', 'Uploaded application documents (Admin).', NULL, 'save', NULL, 'App\\Models\\User', 79, '{\"user_id\":\"79\",\"vacancy_id\":\"ISAIII-022\",\"section\":\"Personal Data Sheet\"}', NULL, '2026-02-02 13:58:46', '2026-02-02 13:58:46'),
(802, 'default', 'Viewed applicant status.', 'App\\Models\\Applications', 'view', 23, 'App\\Models\\Admin', 1, '{\"user_id\":\"79\",\"vacancy_id\":\"ISAIII-022\",\"section\":\"Application List\"}', NULL, '2026-02-02 13:59:35', '2026-02-02 13:59:35');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `office` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','viewer') NOT NULL DEFAULT 'viewer',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `name`, `office`, `designation`, `email`, `password`, `role`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'debug_admin', 'Debug Admin', 'IT', 'Developer', 'admin@debug.com', '$2y$12$xZZIqnLfWzoQgbh4rsQMFeNY6IadMLNyWBuXwoYxByFktBKQ1loGy', 'admin', '2025-07-22 15:29:03', '2025-07-22 15:29:03', 1),
(2, 'admin', 'Admin', 'IT', 'Developer', 'admin@example.com', '$2y$12$n/MOyjsblvB/At/ueKnsL.fmOL.1pKYvWtqu08.H54FNXj1clrdi6', 'admin', '2025-07-22 15:29:03', '2025-07-25 16:13:18', 1),
(3, 'Viewer', 'Test Run', 'HR', 'Regional', 'test@test.com', '$2y$12$dNtB/A7ycrXhng6JSM4ucuDqnAsNvXO8QWUuCjsq4lhgIGgr7SFay', 'viewer', '2025-07-24 11:08:26', '2025-07-24 11:08:26', 1),
(4, 'ej', 'Edriene Jay Cabanela', 'HR Department', 'Administrative Assistant IV', 'edrienecabanela@gmail.com', '$2y$12$NbJ8nBQE52iEgnMT3nKi2.LM/zDBPB46HjEbC5LT2pfj2.H3tNieq', 'admin', '2025-07-24 11:09:49', '2025-07-27 23:12:52', 0);

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `vacancy_id` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `deadline_date` date DEFAULT NULL,
  `deadline_time` time DEFAULT NULL,
  `result` varchar(255) DEFAULT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `scores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scores`)),
  `is_valid` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by_admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_original_name` varchar(255) DEFAULT NULL,
  `file_stored_name` varchar(255) DEFAULT NULL,
  `file_storage_path` varchar(255) DEFAULT NULL,
  `file_remarks` text DEFAULT NULL,
  `file_status` varchar(255) DEFAULT NULL,
  `file_size_8b` bigint(20) UNSIGNED DEFAULT NULL,
  `qs_education` varchar(255) DEFAULT NULL,
  `qs_eligibility` varchar(255) DEFAULT NULL,
  `qs_experience` varchar(255) DEFAULT NULL,
  `qs_training` varchar(255) DEFAULT NULL,
  `qs_result` varchar(255) DEFAULT NULL,
  `application_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `vacancy_id`, `status`, `deadline_date`, `deadline_time`, `result`, `answers`, `scores`, `is_valid`, `created_at`, `updated_at`, `updated_by_admin_id`, `file_original_name`, `file_stored_name`, `file_storage_path`, `file_remarks`, `file_status`, `file_size_8b`, `qs_education`, `qs_eligibility`, `qs_experience`, `qs_training`, `qs_result`, `application_remarks`) VALUES
(22, 77, 'LGOOII-021', 'Complete', '2026-01-13', '17:00:00', NULL, NULL, NULL, 1, '2026-01-09 16:26:22', '2026-01-09 16:35:25', 1, 'Doc1.pdf', '5m7CzV0RhZS32umYmh9R4G8rmWzKRuuua4kCb3mA.pdf', 'uploads/application-files/5m7CzV0RhZS32umYmh9R4G8rmWzKRuuua4kCb3mA.pdf', 'No remarks provided.', 'Okay/Confirmed', 179531, 'yes', 'yes', 'yes', 'yes', 'qualified', 'hawdhbawhdbawhbawdbawjdbawjhbdawhbawdaw'),
(23, 79, 'ISAIII-022', 'Pending', NULL, '13:53:00', NULL, NULL, NULL, 1, '2026-02-02 13:50:01', '2026-02-02 13:57:34', 1, 'img20260120_16045925.pdf', 'LOMKxTDKcmgNF9YdBkxn6wqQZViRvabJdm1TgZhm.pdf', 'uploads/application-files/LOMKxTDKcmgNF9YdBkxn6wqQZViRvabJdm1TgZhm.pdf', 'No remarks provided.', 'Okay/Confirmed', 369333, 'yes', 'yes', 'yes', 'yes', 'not qualified', 'Pleaseckjanwdadawhdaw hjdwbakdjnawldnawdaw kajwdawknddbadakk  jakjdna');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('rhrmspb_portal_cache_349c7dc600ded7d80cad2ac0fb1f0b81d0e565fb', 'i:1;', 1768130567),
('rhrmspb_portal_cache_349c7dc600ded7d80cad2ac0fb1f0b81d0e565fb:timer', 'i:1768130567;', 1768130567),
('rhrmspb_portal_cache_658d774b73c86e748e0c195f6788c3ed73c995dc', 'i:1;', 1767947281),
('rhrmspb_portal_cache_658d774b73c86e748e0c195f6788c3ed73c995dc:timer', 'i:1767947281;', 1767947281),
('rhrmspb_portal_cache_661c643c50f386a37c4c485450d1252f9aa942ef', 'i:1;', 1761030943),
('rhrmspb_portal_cache_661c643c50f386a37c4c485450d1252f9aa942ef:timer', 'i:1761030943;', 1761030943),
('rhrmspb_portal_cache_72ffe471c476a6a5d4a53c61f7d3a9f1b930ae32', 'i:1;', 1762846343),
('rhrmspb_portal_cache_72ffe471c476a6a5d4a53c61f7d3a9f1b930ae32:timer', 'i:1762846343;', 1762846343),
('rhrmspb_portal_cache_7a699539acb9bcfe4d15c6386bae60bfe7dce33b', 'i:2;', 1760791860),
('rhrmspb_portal_cache_7a699539acb9bcfe4d15c6386bae60bfe7dce33b:timer', 'i:1760791860;', 1760791860),
('rhrmspb_portal_cache_894536fde4058758f4aeae3df422307138e2a93f', 'i:3;', 1770010804),
('rhrmspb_portal_cache_894536fde4058758f4aeae3df422307138e2a93f:timer', 'i:1770010804;', 1770010804),
('rhrmspb_portal_cache_9229e15568456ef72ef7539b8befaf9e2553e2de', 'i:1;', 1762992157),
('rhrmspb_portal_cache_9229e15568456ef72ef7539b8befaf9e2553e2de:timer', 'i:1762992157;', 1762992157),
('rhrmspb_portal_cache_ae94d3625ad5a7d7ae0b7c691d462151d888a352', 'i:1;', 1761104689),
('rhrmspb_portal_cache_ae94d3625ad5a7d7ae0b7c691d462151d888a352:timer', 'i:1761104689;', 1761104689),
('rhrmspb_portal_cache_daily_task_last_run', 'O:13:\"Carbon\\Carbon\":4:{s:4:\"date\";s:26:\"2026-02-02 13:12:18.767999\";s:13:\"timezone_type\";i:3;s:8:\"timezone\";s:11:\"Asia/Manila\";s:18:\"dumpDateProperties\";a:2:{s:4:\"date\";s:26:\"2026-02-02 13:12:18.767999\";s:8:\"timezone\";s:11:\"Asia/Manila\";}}', 2085369138);

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
-- Table structure for table `civil_service_eligibilities`
--

CREATE TABLE `civil_service_eligibilities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cs_eligibility_career` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `cs_eligibility_rating` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `cs_eligibility_date` date NOT NULL DEFAULT curdate(),
  `cs_eligibility_place` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `cs_eligibility_license` varchar(255) DEFAULT NULL,
  `cs_eligibility_validity` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `civil_service_eligibilities`
--

INSERT INTO `civil_service_eligibilities` (`id`, `created_at`, `updated_at`, `user_id`, `cs_eligibility_career`, `cs_eligibility_rating`, `cs_eligibility_date`, `cs_eligibility_place`, `cs_eligibility_license`, `cs_eligibility_validity`) VALUES
(13, '2025-07-29 13:21:13', '2025-07-29 13:21:13', 63, 'Civil Service Exam', '88', '2025-03-30', 'La Union National High School', '123456789', '2025-10-31'),
(14, '2025-07-31 18:36:40', '2025-07-31 18:34:19', 61, 'BOARD', '100', '2025-07-01', 'ROSALES', '234141', '2025-07-28'),
(16, '2025-08-06 22:12:21', '2025-08-06 22:10:25', 5, 'Board', '44%', '2025-07-18', 'DILG-CAR', '123asd', '2025-07-28'),
(18, '2025-11-03 10:02:37', '2025-11-03 10:02:15', 74, 'CSC Prof', '90', '2024-11-03', 'Baguio', '63738', '2025-09-03'),
(20, '2026-01-06 13:43:20', '2026-01-06 13:42:42', 71, 'CSC PROF', '99', '2025-12-30', 'adwawdwa', 'N/A', '2026-01-07'),
(22, '2026-01-09 16:25:59', '2026-01-09 16:25:25', 77, '1111', '111', '2026-01-05', '1111', '111', '2026-01-09');

-- --------------------------------------------------------

--
-- Table structure for table `educational_backgrounds`
--

CREATE TABLE `educational_backgrounds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `elem_from` varchar(7) DEFAULT NULL,
  `elem_to` varchar(7) DEFAULT NULL,
  `elem_school` varchar(255) DEFAULT NULL,
  `elem_academic_honors` varchar(255) DEFAULT NULL,
  `elem_basic` varchar(255) DEFAULT NULL,
  `elem_earned` varchar(255) DEFAULT NULL,
  `elem_year_graduated` varchar(4) DEFAULT NULL,
  `jhs_from` varchar(7) DEFAULT NULL,
  `jhs_to` varchar(7) DEFAULT NULL,
  `jhs_school` varchar(255) DEFAULT NULL,
  `jhs_academic_honors` varchar(255) DEFAULT NULL,
  `jhs_basic` varchar(255) DEFAULT NULL,
  `jhs_earned` varchar(255) DEFAULT NULL,
  `jhs_year_graduated` varchar(4) DEFAULT NULL,
  `shs_from` varchar(7) DEFAULT NULL,
  `shs_to` varchar(7) DEFAULT NULL,
  `shs_school` varchar(255) DEFAULT NULL,
  `shs_academic_honors` varchar(255) DEFAULT NULL,
  `shs_basic` varchar(255) DEFAULT NULL,
  `shs_earned` varchar(255) DEFAULT NULL,
  `shs_year_graduated` varchar(4) DEFAULT NULL,
  `vocational` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vocational`)),
  `college` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`college`)),
  `grad` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`grad`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `educational_backgrounds`
--

INSERT INTO `educational_backgrounds` (`id`, `created_at`, `updated_at`, `user_id`, `elem_from`, `elem_to`, `elem_school`, `elem_academic_honors`, `elem_basic`, `elem_earned`, `elem_year_graduated`, `jhs_from`, `jhs_to`, `jhs_school`, `jhs_academic_honors`, `jhs_basic`, `jhs_earned`, `jhs_year_graduated`, `shs_from`, `shs_to`, `shs_school`, `shs_academic_honors`, `shs_basic`, `shs_earned`, `shs_year_graduated`, `vocational`, `college`, `grad`) VALUES
(1, '2025-07-25 15:16:39', '2025-07-25 15:16:39', 5, '2008-06', '2014-03', 'UICS', NULL, 'PRIMARY', NULL, '2014', '2014-06', '2020-04', 'UCNHS', NULL, NULL, NULL, '2020', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]', '[{\"from\":\"2020-08\",\"to\":\"2026-04\",\"school\":\"PSU\",\"basic\":\"Computer Engineering\",\"earned\":\"200\",\"year_graduated\":\"2026\",\"academic_honors\":null}]', '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]'),
(2, '2025-07-27 11:56:57', '2025-07-27 11:56:57', 59, '2010', '2016', 'Minien Tebag Elementary School', NULL, 'PRIMARY', NULL, '2016', '2016', '2020', 'Daniel Maramba National High Schoo', NULL, NULL, NULL, '2022', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"from\":\"2022\",\"to\":\"2026\",\"school\":\"Pangasinan State University\",\"basic\":\"Bachelor of Computer Engineering\",\"earned\":\"180\",\"year_graduated\":\"2026\",\"academic_honors\":null}]', NULL),
(3, '2025-07-28 08:46:28', '2025-07-28 08:46:28', 61, '2025-10', '2025-08', 'Elementary 01', 'With Honor', 'PRIMARY', NULL, '2025', '2025-06', '2025-07', 'Rosales National High School', 'With Honor', NULL, NULL, '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]', '[{\"from\":\"2025-06\",\"to\":\"2025-08\",\"school\":\"Pangasinan State University\",\"basic\":\"Computer Engineering\",\"earned\":\"400\",\"year_graduated\":\"2025\",\"academic_honors\":null}]', '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]'),
(4, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, '2025-07', '2025-07', 'Cabanela', NULL, 'PRIMARY', NULL, '2006', '2025-07', '2025-07', 'School', NULL, NULL, NULL, '2068', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]', '[{\"from\":\"2025-07\",\"to\":\"2025-07\",\"school\":\"Xabanela\",\"basic\":\"Ges\",\"earned\":\"124\",\"year_graduated\":\"2056\",\"academic_honors\":null}]', '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]'),
(5, '2025-07-29 13:29:00', '2025-07-29 13:29:00', 63, '2010-06', '2016-04', 'Damortis Elementary School', 'With Honors', 'PRIMARY', NULL, '2016', '2016-06', '2022-04', 'Rosario Integrated School', 'With High Honors', 'Secondary', NULL, '2022', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]', '[{\"from\":\"2022-08\",\"to\":\"2026-04\",\"school\":\"Pangasinan State University - Urdaneta City Campus\",\"basic\":\"Bachelor of Science in Computer Engineering\",\"earned\":\"2400\",\"year_graduated\":\"2026\",\"academic_honors\":\"Cum Laude\"}]', '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]'),
(6, '2025-07-29 17:58:09', '2025-07-29 17:58:09', 68, '2025-06', '2025-07', 'BINMECKEG ELEMENTARY SCHOOL', NULL, 'PRIMARY', NULL, '2009', '2016-06', '2019-07', 'DON AMADEO PEREZ SR. NATIONAL HIGH SCHOOL', 'WITH HONORS', NULL, NULL, '2020', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]', '[{\"from\":\"2021-08\",\"to\":\"2025-05\",\"school\":\"PANGASINAN STATE UNIVERSITY - URDANETA CITY CAMPUS\",\"basic\":\"BACHELOR OF SCIENCE IN COMPUTER ENGINEERING\",\"earned\":\"180\",\"year_graduated\":\"2025\",\"academic_honors\":\"DOST JLSS SCHOLAR\"}]', '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]'),
(7, '2025-11-03 10:01:37', '2025-11-03 10:01:37', 74, '2024-11', '2025-11', 'Hhhh', NULL, 'PRIMARY', NULL, '2025', '2024-11', '2025-11', 'Yytt', NULL, NULL, NULL, '2025', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"from\":\"2024-11\",\"to\":\"2025-11\",\"school\":\"Tttt\",\"basic\":\"Ttt\",\"earned\":\"2025\",\"year_graduated\":\"2025\",\"academic_honors\":null}]', '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]'),
(8, '2026-01-06 13:42:57', '2026-01-06 13:42:57', 71, '2026-01', '2026-01', '123123123123123123123123123', '123123123123123123123123123', 'PRIMARY', NULL, '1999', '2026-01', '2026-01', '123123123123123123123123123', '123123123123123123123123123', '123123123123123123123123123', NULL, '1999', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"from\":\"2026-01\",\"to\":\"2026-01\",\"school\":\"123123123123123123123123123\",\"basic\":\"123123123123123123123123123\",\"earned\":\"20\",\"year_graduated\":\"1231\",\"academic_honors\":\"123123123123123123123123123\"}]', NULL),
(9, '2026-01-09 16:23:57', '2026-01-09 16:23:57', 77, '2026-02', '2026-02', 'dawdaw', NULL, 'PRIMARY', NULL, '2015', '2026-06', '2026-02', '111111111', NULL, 'adwaadw', NULL, '2020', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"from\":\"2026-02\",\"to\":\"2026-02\",\"school\":\"awdawdaw\",\"basic\":\"adwaw\",\"earned\":\"10\",\"year_graduated\":\"2020\",\"academic_honors\":null}]', NULL),
(10, '2026-02-02 13:38:28', '2026-02-02 13:38:28', 79, '2012-03', '2017-07', 'awdawdaw', NULL, 'PRIMARY', NULL, '2017', '2026-03', '2026-03', 'dwadaw', NULL, NULL, NULL, '2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]', '[{\"from\":\"2026-02\",\"to\":\"2026-03\",\"school\":\"awda\",\"basic\":\"ahjdba\",\"earned\":\"100\",\"year_graduated\":\"2026\",\"academic_honors\":null}]', '[{\"from\":null,\"to\":null,\"school\":null,\"basic\":null,\"earned\":null,\"year_graduated\":null,\"academic_honors\":null}]');

-- --------------------------------------------------------

--
-- Table structure for table `exam_details`
--

CREATE TABLE `exam_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vacancy_id` varchar(255) NOT NULL,
  `is_started` tinyint(1) NOT NULL DEFAULT 0,
  `time` time DEFAULT NULL,
  `date` date DEFAULT NULL,
  `place` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `notified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_details`
--

INSERT INTO `exam_details` (`id`, `vacancy_id`, `is_started`, `time`, `date`, `place`, `duration`, `notified_at`, `created_at`, `updated_at`) VALUES
(12, 'LGOOII-021', 0, NULL, NULL, NULL, NULL, NULL, '2026-01-09 08:20:17', '2026-01-09 08:20:17'),
(13, 'ISAIII-022', 0, NULL, NULL, NULL, NULL, NULL, '2026-02-02 13:45:28', '2026-02-02 13:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `exam_items`
--

CREATE TABLE `exam_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vacancy_id` varchar(255) NOT NULL,
  `question` text NOT NULL,
  `is_essay` tinyint(1) NOT NULL DEFAULT 0,
  `choices` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`choices`)),
  `ans` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `family_backgrounds`
--

CREATE TABLE `family_backgrounds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `spouse_surname` varchar(255) DEFAULT NULL,
  `spouse_first_name` varchar(255) DEFAULT NULL,
  `spouse_name_extension` varchar(255) DEFAULT NULL,
  `spouse_middle_name` varchar(255) DEFAULT NULL,
  `spouse_occupation` varchar(255) DEFAULT NULL,
  `spouse_employer` varchar(255) DEFAULT NULL,
  `spouse_business_address` varchar(255) DEFAULT NULL,
  `spouse_telephone` varchar(255) DEFAULT NULL,
  `father_surname` varchar(255) DEFAULT NULL,
  `father_first_name` varchar(255) DEFAULT NULL,
  `father_middle_name` varchar(255) DEFAULT NULL,
  `father_name_extension` varchar(255) DEFAULT NULL,
  `mother_maiden_surname` varchar(255) DEFAULT NULL,
  `mother_maiden_first_name` varchar(255) DEFAULT NULL,
  `mother_maiden_middle_name` varchar(255) DEFAULT NULL,
  `children_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`children_info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `family_backgrounds`
--

INSERT INTO `family_backgrounds` (`id`, `created_at`, `updated_at`, `user_id`, `spouse_surname`, `spouse_first_name`, `spouse_name_extension`, `spouse_middle_name`, `spouse_occupation`, `spouse_employer`, `spouse_business_address`, `spouse_telephone`, `father_surname`, `father_first_name`, `father_middle_name`, `father_name_extension`, `mother_maiden_surname`, `mother_maiden_first_name`, `mother_maiden_middle_name`, `children_info`) VALUES
(1, '2025-07-25 15:16:39', '2025-07-29 09:02:15', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Benosa', 'Frennie', NULL, '[{\"name\":null,\"dob\":null}]'),
(2, '2025-07-27 11:56:57', '2025-07-27 11:56:57', 59, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Santos', 'Jimmy', 'Cruz', NULL, 'Montemayor', 'Imelda', 'Ventigan', NULL),
(3, '2025-07-28 08:46:28', '2025-07-29 10:26:14', 61, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Magdalena', 'Clara', 'Rosa', '[{\"name\":null,\"dob\":null}]'),
(4, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Edutha', 'Editha', 'Oribello', '[{\"name\":null,\"dob\":null}]'),
(5, '2025-07-29 13:29:00', '2025-07-29 13:29:00', 63, 'Mahiru', 'Shiina', NULL, NULL, 'Housewife', NULL, NULL, '09123456789', 'Antolin', 'Gerardo', 'Villagonzalo', NULL, 'Moldez', 'Eliza', 'Mitra', '[{\"name\":\"Children 1\",\"dob\":\"2025-07-29\"}]'),
(6, '2025-07-29 17:58:09', '2025-07-29 17:58:09', 68, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'MEDIANA', 'STEFANIE', 'JOY', NULL, 'GALLARDE', 'KRISHA', NULL, '[{\"name\":null,\"dob\":null}]'),
(7, '2025-11-03 10:01:37', '2025-11-03 10:01:37', 74, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Hhh', 'Hhh', NULL, '[{\"name\":null,\"dob\":null}]'),
(8, '2026-01-06 13:42:57', '2026-01-06 13:42:57', 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123123123123123123123123123', '123123123123123123123123123', '123123123123123123123123123', '123123123123123123123123123', '123123123123123123123123123', '123123123123123123123123123', '123123123123123123123123123', NULL),
(9, '2026-01-09 16:23:57', '2026-01-09 16:23:57', 77, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'awdawd', 'awdawd', NULL, NULL),
(10, '2026-02-02 13:38:28', '2026-02-02 13:38:28', 79, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'hdiauh', 'iauwhwdiuahw', NULL, '[{\"name\":null,\"dob\":null}]');

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
-- Table structure for table `job_vacancies`
--

CREATE TABLE `job_vacancies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vacancy_id` varchar(255) NOT NULL,
  `position_title` varchar(255) NOT NULL,
  `vacancy_type` varchar(255) NOT NULL,
  `pcn_no` varchar(255) DEFAULT NULL,
  `plantilla_item_no` varchar(255) DEFAULT NULL,
  `monthly_salary` decimal(10,2) NOT NULL,
  `salary_grade` varchar(255) DEFAULT NULL,
  `place_of_assignment` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `closing_date` datetime NOT NULL,
  `qualification_education` varchar(255) NOT NULL,
  `qualification_training` varchar(255) NOT NULL,
  `qualification_experience` varchar(255) NOT NULL,
  `qualification_eligibility` varchar(255) NOT NULL,
  `competencies` text DEFAULT NULL,
  `expected_output` text DEFAULT NULL,
  `scope_of_work` text DEFAULT NULL,
  `duration_of_work` text DEFAULT NULL,
  `to_person` varchar(255) NOT NULL,
  `to_position` varchar(255) NOT NULL,
  `to_office` varchar(255) NOT NULL,
  `to_office_address` varchar(255) NOT NULL,
  `last_modified_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_vacancies`
--

INSERT INTO `job_vacancies` (`id`, `vacancy_id`, `position_title`, `vacancy_type`, `pcn_no`, `plantilla_item_no`, `monthly_salary`, `salary_grade`, `place_of_assignment`, `status`, `closing_date`, `qualification_education`, `qualification_training`, `qualification_experience`, `qualification_eligibility`, `competencies`, `expected_output`, `scope_of_work`, `duration_of_work`, `to_person`, `to_position`, `to_office`, `to_office_address`, `last_modified_by`, `created_at`, `updated_at`) VALUES
(21, 'LGOOII-021', 'Local Government Operations Officer II', 'COS', NULL, NULL, 34421.00, '13', 'Ifugao Provincial Office', 'CLOSED', '2026-01-08 00:00:00', 'Bachelor\'s Degree', 'None Required', 'None Required', 'CSC Professional / 2nd Level Eligibility', NULL, 'Test', 'Test', '8 hrs', 'Atty. Anthony C. Nuyda, CESO III', 'Regional Director', 'DILG-CAR', 'Upper Session Rd. corner North Drive, Baguio City, Benguet', 'System', '2026-01-09 08:20:17', '2026-01-09 16:41:04'),
(22, 'ISAIII-022', 'Information Systems Analyst III', 'COS', NULL, NULL, 51000.00, 'SG 19', 'DILG-CAR Regional Office', 'OPEN', '2026-02-04 00:00:00', 'IT Graduateadaw', 'None Required', '1 year', 'N/A', NULL, 'awdaw', 'awdaw', 'awdawd', 'Atty. Anthony C. Nuyda, CESO III', 'Regional Director', 'DILG-CAR', 'Upper Session Rd. corner North Drive, Baguio City, Benguet', NULL, '2026-02-02 13:45:28', '2026-02-02 13:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `learning_and_developments`
--

CREATE TABLE `learning_and_developments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `learning_title` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `learning_type` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `learning_from` date NOT NULL DEFAULT '2025-06-01',
  `learning_to` date NOT NULL DEFAULT '2025-06-02',
  `learning_hours` smallint(6) NOT NULL DEFAULT 24,
  `learning_conducted` varchar(255) NOT NULL DEFAULT 'NOINPUT'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `learning_and_developments`
--

INSERT INTO `learning_and_developments` (`id`, `created_at`, `updated_at`, `user_id`, `learning_title`, `learning_type`, `learning_from`, `learning_to`, `learning_hours`, `learning_conducted`) VALUES
(1, '2025-07-27 11:56:57', '2025-07-27 11:56:57', 59, 'Vibe Coding', 'Technical', '2025-03-15', '2025-03-15', 4, 'icpep'),
(7, '2025-07-29 13:29:00', '2025-07-29 13:29:00', 63, 'OSH Training', 'Supervisory', '2025-07-29', '2025-07-29', 120, '1BESO'),
(8, '2025-07-31 18:36:40', '2025-07-31 18:36:40', 61, 'PMA', 'Managerial', '2025-07-15', '2025-07-16', 212, 'BOSS MAN');

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
(1, '2025_06_24_041939_job_vacancy', 1),
(2, '2025_06_24_045359_create_users_table', 1),
(3, '2025_06_24_051553_create_personal_information_table', 1),
(4, '2025_06_24_070848_create_family_backgrounds_table', 1),
(5, '2025_06_24_072533_create_educational_backgrounds_table', 1),
(6, '2025_06_24_075042_create_work_experiences_table', 1),
(7, '2025_06_24_080851_create_civil_service_eligibilities_table', 1),
(8, '2025_06_24_084342_create_learning_and_developments_table', 1),
(9, '2025_06_24_085857_create_voluntary_works_table', 1),
(10, '2025_06_24_091727_create_other_information_table', 1),
(11, '2025_06_24_092449_create_related_questions_table', 1),
(12, '2025_06_27_032353_create_table_admins', 1),
(13, '2025_06_27_113405_create_exam_details_table', 1),
(14, '2025_06_27_133055_create_misc_infos_table', 1),
(15, '2025_06_30_095445_create_applications_table', 1),
(16, '2025_06_30_104017_create_uploaded_documents_table', 1),
(17, '2025_06_30_110220_add_otp_fields_to_users_table', 1),
(18, '2025_06_30_162403_add_is_active_to_admins_table', 1),
(19, '2025_07_01_113938_create_exam_items', 1),
(20, '2025_07_01_130932_create_cache_table', 1),
(21, '2025_07_04_171007_create_jobs_table', 1),
(22, '2025_07_08_154021_create_failed_jobs_table', 1),
(23, '2025_07_17_110434_create_work_exp_sheet', 1),
(24, '2025_07_18_133216_create_activity_log_table', 1),
(25, '2025_07_18_133217_add_event_column_to_activity_log_table', 1),
(26, '2025_07_18_133218_add_batch_uuid_column_to_activity_log_table', 1),
(27, '2025_06_19_034620_user-migration', 2);

-- --------------------------------------------------------

--
-- Table structure for table `misc_infos`
--

CREATE TABLE `misc_infos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `related_34_a` varchar(3) DEFAULT NULL,
  `related_34_b` varchar(255) DEFAULT NULL,
  `guilty_35_a` varchar(255) DEFAULT NULL,
  `criminal_35_b` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`criminal_35_b`)),
  `convicted_36` varchar(255) DEFAULT NULL,
  `separated_37` varchar(255) DEFAULT NULL,
  `candidate_38` varchar(255) DEFAULT NULL,
  `resigned_38_b` varchar(255) DEFAULT NULL,
  `immigrant_39` varchar(255) DEFAULT NULL,
  `indigenous_40_a` varchar(255) DEFAULT NULL,
  `pwd_40_b` varchar(255) DEFAULT NULL,
  `solo_parent_40_c` varchar(255) DEFAULT NULL,
  `ref1_name` varchar(255) DEFAULT NULL,
  `ref1_tel` varchar(255) DEFAULT NULL,
  `ref1_address` varchar(255) DEFAULT NULL,
  `ref2_name` varchar(255) DEFAULT NULL,
  `ref2_tel` varchar(255) DEFAULT NULL,
  `ref2_address` varchar(255) DEFAULT NULL,
  `ref3_name` varchar(255) DEFAULT NULL,
  `ref3_tel` varchar(255) DEFAULT NULL,
  `ref3_address` varchar(255) DEFAULT NULL,
  `govt_id_type` varchar(255) DEFAULT NULL,
  `govt_id_number` varchar(255) DEFAULT NULL,
  `govt_id_date_issued` varchar(255) DEFAULT NULL,
  `govt_id_place_issued` varchar(255) DEFAULT NULL,
  `photo_upload` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `misc_infos`
--

INSERT INTO `misc_infos` (`id`, `created_at`, `updated_at`, `user_id`, `related_34_a`, `related_34_b`, `guilty_35_a`, `criminal_35_b`, `convicted_36`, `separated_37`, `candidate_38`, `resigned_38_b`, `immigrant_39`, `indigenous_40_a`, `pwd_40_b`, `solo_parent_40_c`, `ref1_name`, `ref1_tel`, `ref1_address`, `ref2_name`, `ref2_tel`, `ref2_address`, `ref3_name`, `ref3_tel`, `ref3_address`, `govt_id_type`, `govt_id_number`, `govt_id_date_issued`, `govt_id_place_issued`, `photo_upload`) VALUES
(1, '2025-07-25 15:16:39', '2025-08-06 22:10:32', 5, 'no', 'no', 'no', '\"no\"', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'Other People Forsure', '09341324563', 'Sa Bahay Nila Syempre', 'Ibang Tao Eto', '09452331234', 'Ibang Bahay Siya Nakatira', 'Sure Ibang Tao', '09219845832', 'Ibang Tao Binabahayan', 'Voter\'s ID', '324asd', '2025-06-30', 'Sa Pag Kuhanan', NULL),
(2, '2025-07-27 11:56:57', '2025-07-27 11:56:57', 59, 'no', 'no', 'no', '\"no\"', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'Blezielle Santos', '09946960014', 'Sta Barbara', 'Imelda Santos', '09629285362', 'Sta.Barbara', 'Jimmy Santos', '09937580133', 'Sta.Barbara', 'PhilSys', '3583076140389631', '2024-10-26', 'Urdaneta Pangasinan', 'uploads/pds-photo/sIoLlSRCGhxmK1gpSzT1jTcMLQ31PWVjs5aufOss.jpg'),
(3, '2025-07-28 08:46:28', '2025-07-31 18:36:40', 61, 'no', 'no', 'no', '\"no\"', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'R1', '2112', 'R1ADD', 'R2', '2112', 'R2ADD', 'R3', '2112', 'R3ADD', 'PhilHealth', '1111111111111111', '2025-07-16', 'Manila', NULL),
(4, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'no', 'no', 'no', '\"no\"', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'Edriene Jay Cabanela', '09163431868', 'Emerald Road', 'Edriene Jay Cabanela', '09163431868', 'Emerald Road', 'Edriene Jay Cabanela', '09163431868', 'Emerald Road', 'voters', '61727', '2025-07-29', 'Vsjsud', 'uploads/pds-photo/oZbuYVU5wN1K5TDskiLrCs52PpQXkW4mF7jzYdf4.png'),
(5, '2025-07-29 13:29:00', '2025-07-29 13:29:00', 63, 'yes', 'YOU KNOW', 'YOU KNOW', '\"2025-07-29,WHERE YOU ARE WITH\"', 'YOU KNOW WHERE', 'ARE WITH', 'FLOOR COLLAPSING', 'FLOATINGG', 'BOUNCING BACK', 'AND ONE DAY', 'I AM GONNA', 'GROW WINGS', 'A CHEMICAL REACTION', '123456789', 'HYSTERICAL AND USELESS', 'HYSTERICAL AND', '123456789', 'LET DOWN', 'AND HANGING AROUND', '123456789', 'CRUSHED LIKE A', 'BUG', 'TO THE GROUND', '2025-07-29', 'LET DOWN', 'uploads/pds-photo/tu4NpomM2ROuE9OtilBmyiLYUoM94Svi68jIa2vj.jpg'),
(6, '2025-07-29 17:58:09', '2025-07-29 17:58:09', 68, 'no', 'no', 'no', '\"no\"', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'EDRIENE JAY ORIBELLO CABANELA', '09774583800', 'ST.  JOSEPH, CONCEPCION, ROSARIO, LA UNION', 'EDRIENE JAY ORIBELLO CABANELA', '09762606888', 'BLOCK 15, LOT 2 EMERALD RD.', 'EDRIENE JAY ORIBELLO CABANELA', '09762606888', 'BLOCK 15, LOT 2 EMERALD RD.', 'drivers', '424355', '2025-07-18', 'SISON, PANGASINAN', 'uploads/pds-photo/l50IvZNWbIbsxJYOnVYoBiX6WowsOm84NO7W4rIk.png'),
(7, '2025-11-03 10:01:37', '2025-11-03 10:02:37', 74, 'no', 'no', 'no', '\"no\"', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'Ytttt', '09212119303', 'Hhggg', 'Hdhdj', '09212119302', 'Vhjm', 'Fgjjnn', '09212119304', 'Bjdjdjfj', 'PhilHealth', '5267282828', '2025-11-03', 'Hsjsns', NULL),
(8, '2026-01-06 13:42:57', '2026-01-06 13:42:57', 71, 'no', 'no', 'no', '\"no\"', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'wadawdaw', '09212119301', 'awdawdaw', 'wadawdaw', '09212119301', 'awdawdaw', 'wadawdaw', '09212119301', 'awdawdaw', 'Passport', '12312312312312312312', '2026-01-08', 'awdawdaw', NULL),
(9, '2026-01-09 16:23:57', '2026-01-09 16:25:59', 77, 'no', 'no', 'no', '\"no\"', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', '11111111111111', '09123456789', '111111111111111111', '11111111111111', '09123456789', '111111111111111', '11111111', '09123456789', '111111111', 'GSIS', '111111111111111111', '2026-01-07', '09123456789', NULL),
(10, '2026-02-02 13:38:28', '2026-02-02 13:38:28', 79, 'no', 'no', 'no', '\"no\"', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'adawdad', '09123456789', '09123456789', '09123456789', '09123456789', '09123456789', '09123456789', '09123456789', '09123456789', 'PhilSys/National ID', '09123456789', '2026-02-11', '09123456789', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `other_information`
--

CREATE TABLE `other_information` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `skill` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skill`)),
  `distinction` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`distinction`)),
  `organization` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`organization`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `other_information`
--

INSERT INTO `other_information` (`id`, `created_at`, `updated_at`, `user_id`, `skill`, `distinction`, `organization`) VALUES
(1, '2025-07-25 15:16:39', '2025-08-06 22:12:21', 5, '[null,\"Gaming\",\"GRAPHIC DESIGN\",\"PROGRAMMING\"]', '[null,\"Athlete\"]', '[null,\"SNR\",\"ICpEP\"]'),
(2, '2025-07-27 11:56:57', '2025-07-27 11:56:57', 59, '[\"programming\"]', '[null]', '[null]'),
(3, '2025-07-28 08:46:28', '2025-07-31 18:36:40', 61, '[\"Honey\",\"skill1\",\"Hw\",\"Hw\"]', '[\"Hw\",\"non1\",\"Aa\"]', '[null,\"mem1\",\"Memq\"]'),
(4, '2025-07-29 13:14:21', '2025-07-31 16:35:29', 56, '[null]', '[null]', '[null]'),
(5, '2025-07-29 13:29:00', '2025-07-29 13:29:00', 63, '[\"Programming\",\"Gaming\",\"Networking\"]', '[null]', '[\"DOST - SICAP\"]'),
(6, '2025-07-29 17:58:09', '2025-07-29 17:58:09', 68, '[null]', '[null]', '[null]'),
(7, '2025-11-03 10:01:37', '2025-11-03 10:02:37', 74, '[null]', '[null]', '[null]'),
(8, '2026-01-06 13:42:57', '2026-01-06 13:43:20', 71, '[null,\"wdawdwa\"]', '[null,\"awdaw\"]', '[null,\"awda\"]'),
(9, '2026-01-09 16:23:57', '2026-01-09 16:25:59', 77, '[null]', '[null]', '[null]'),
(10, '2026-02-02 13:38:28', '2026-02-02 13:49:41', 79, '[null]', '[null]', '[null]');

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
-- Table structure for table `personal_information`
--

CREATE TABLE `personal_information` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cs_id_no` varchar(255) DEFAULT NULL,
  `surname` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `name_extension` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `middle_name` varchar(255) DEFAULT NULL,
  `sex` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `civil_status` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `date_of_birth` date NOT NULL DEFAULT curdate(),
  `place_of_birth` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `height` varchar(255) DEFAULT NULL,
  `weight` varchar(255) DEFAULT NULL,
  `blood_type` char(255) DEFAULT NULL,
  `philhealth_no` varchar(255) DEFAULT NULL,
  `tin_no` varchar(255) DEFAULT NULL,
  `agency_employee_no` varchar(255) DEFAULT NULL,
  `gsis_id_no` varchar(255) DEFAULT NULL,
  `pagibig_id_no` varchar(255) DEFAULT NULL,
  `sss_id_no` varchar(255) DEFAULT NULL,
  `citizenship` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `dual_country` varchar(255) DEFAULT NULL,
  `dual_type` varchar(255) DEFAULT NULL,
  `residential_address` varchar(255) DEFAULT NULL,
  `permanent_address` varchar(255) DEFAULT NULL,
  `telephone_no` varchar(255) DEFAULT NULL,
  `mobile_no` varchar(255) DEFAULT NULL,
  `email_address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_information`
--

INSERT INTO `personal_information` (`id`, `created_at`, `updated_at`, `user_id`, `cs_id_no`, `surname`, `name_extension`, `first_name`, `middle_name`, `sex`, `civil_status`, `date_of_birth`, `place_of_birth`, `height`, `weight`, `blood_type`, `philhealth_no`, `tin_no`, `agency_employee_no`, `gsis_id_no`, `pagibig_id_no`, `sss_id_no`, `citizenship`, `dual_country`, `dual_type`, `residential_address`, `permanent_address`, `telephone_no`, `mobile_no`, `email_address`) VALUES
(1, '2025-07-25 15:16:39', '2025-07-29 09:02:15', 5, NULL, 'Racadio', NULL, 'Christian', NULL, 'male', 'single', '2001-03-01', 'Poblacion Urdaneta City Pangasinan', '180', '65', 'A+', NULL, NULL, NULL, NULL, NULL, NULL, 'Filipino', NULL, NULL, '15/|/Labsan Street/|/{*}/|/Kayang Extension/|/Baguio City/|/Benguet/|/2600', '#51/|/Mc Arthur Highway/|/{*}/|/Poblacion/|/Urdaneta City/|/Pangasinan/|/2428', NULL, '09452087319', 'debug@debug.com'),
(2, '2025-07-27 11:56:57', '2025-07-27 11:56:57', 59, NULL, 'Santos', NULL, 'Joash Irvin', 'Montemayor', 'male', 'single', '2004-07-05', 'Lingayen Pangasinan', '168', '67', 'O', NULL, NULL, NULL, NULL, NULL, NULL, 'Filipino', NULL, NULL, '#14/|/Sta.Victoria/|/Villa/|/Minien West/|/Santa Barbara/|/Pangasinan/|/2419', '#14/|/Sta.Victoria/|/Villa/|/Minien West/|/Santa Barbara/|/Pangasinan/|/2419', '0755299501', '09629285362', 'juaszie@gmail.com'),
(3, '2025-07-28 08:46:28', '2025-07-28 08:46:28', 61, NULL, 'OBILLO', NULL, 'CRISTHAN', 'ACOSTA', 'male', 'single', '2001-03-11', 'Villasis, Pangasinan', '167', '60', 'A+', '25423524', '2452345245', '24542352435', '2524352', '32453425', '52345235', 'Filipino', NULL, NULL, '111/|/Bonifacio/|/laban village/|/Carmen/|/Villasis/|/Pangasinan/|/2442', '111/|/Bonifacio/|/laban village/|/Carmen/|/Villasis/|/Pangasinan/|/2442', '09277419250', '09277419250', 'cristhangray@gmail.com'),
(4, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, NULL, 'Cabanela', NULL, 'Edriene', 'Cabanela', 'male', 'single', '2025-07-29', 'AGOO, LA UNION', '169', '69', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Filipino', NULL, NULL, '{*}/|/{*}/|/{*}/|/Conception/|/Rosario/|/La union/|/2506', '{*}/|/{*}/|/{*}/|/Conception/|/Rosario/|/La union/|/2506', NULL, '09762606888', 'edrienecabanela@gmail.com'),
(5, '2025-07-29 13:29:00', '2025-07-29 13:29:00', 63, NULL, 'Antolin', NULL, 'Janel', 'Moldez', 'male', 'married', '2004-01-20', 'Cavite City', '178', '65', 'O', '123456789', '123456789', '123456789', '123456789', '123456789', '123456789', 'Dual Citizenship', 'Japan', NULL, '140/|/{*}/|/{*}/|/Damortis/|/Santo Tomas/|/La Union/|/2505', '140/|/{*}/|/{*}/|/Damortis/|/Santo Tomas/|/La Union/|/2505', '09774258867', '09774258867', 'janelantolin20@gmail.com'),
(6, '2025-07-29 17:58:09', '2025-07-29 17:58:09', 68, NULL, 'CABANELA', 'EDRIENE JAY ORIBELLO CABANELA', 'EDRIENE JAY', 'ORIBELLO', 'female', 'single', '1996-02-06', 'AGOO, LA UNION', '76', '60', NULL, '57742609', '13134141', '5655656', '42445535', '65354', '31098967', 'Filipino', NULL, NULL, 'BLOCK 15, LOT 2/|/EMERALD RD./|/ST. JOSEPH SUBDIVISION/|/CONCEPCION/|/AGOO, LA UNION/|/Region I/|/1230', 'BLOCK 15, LOT 2D./|/EMERALD RD./|/{*}/|/CONCEPCION/|/ROSARIO/|/LA UNION/|/2506', '09774583800', '09774583800', 'edrienecabanela@gmail.com'),
(7, '2025-11-03 10:01:37', '2025-11-03 10:01:37', 74, NULL, 'Tttt', 'Tttt', 'Tttt', 'Tttt', 'male', 'single', '2025-08-12', 'Hhhhh', '215', '125', 'A+', '5585558', '555555', '5555555', '215151515', '8855555', '888555', 'Filipino', NULL, NULL, '166/|/Tt/|/{*}/|/Hhh/|/Hhh/|/Hhh/|/2600', '{*}/|/{*}/|/{*}/|/Vgg/|/Vvg/|/Bb/|/2600', NULL, '09212119302', 'billyferreol@gmail.com'),
(8, '2026-01-06 13:42:57', '2026-01-06 13:42:57', 71, NULL, 'awdawda', 'awdada', 'wdawdaw', 'dawda', 'male', 'single', '2025-12-29', 'adwawdaw', '123', '123', 'O+', '123123123123123123123123123', '123123123123123123123123123', '123123123123123123123123123', '123123123123123123123123123', '123123123123123123123123123', '123123123123123123123123123', 'Filipino', NULL, NULL, '123123123123123123123123123/|/123123123123123123123123123/|/123123123123123123123123123/|/123123123123123123123123123/|/123123123123123123123123123/|/123123123123123123123123123/|/1231', '123123123123123123123123123/|/123123123123123123123123123/|/123123123123123123123123123/|/123123123123123123123123123123123123123123123123123123/|/123123123123123123123123123/|/123123123123123123123123123/|/1231', NULL, '09212119301', 'subaybayancordillera@gmail.com'),
(9, '2026-01-09 16:23:57', '2026-01-09 16:23:57', 77, NULL, 'aaaa', 'aaaaaaaaaaaaaaaa', 'aaaaaaaaaaaaa', 'aaaaaaaaaaaaaa', 'male', 'single', '2026-01-06', 'aaaaaaaa', '165', '85', 'O+', '11111111111111', '1111111', '111111111', '111111111111', '11111111111', '111111111', 'Filipino', NULL, NULL, '1111111/|/1111111/|/1111111111111/|/Maniboc/|/Lingayen/|/Pangasinan/|/2401', '1111/|/11111111/|/1111111111111111111/|/Maniboc/|/Lingayen/|/Pangasinan/|/2401', '09123456789', '09123456789', 'zoomcarroom2@gmail.com'),
(10, '2026-02-02 13:38:28', '2026-02-02 13:38:28', 79, NULL, '11', '111', '11', '11', 'male', 'single', '2026-02-04', 'da', '110', '110', 'A+', NULL, NULL, NULL, NULL, NULL, NULL, 'Filipino', NULL, NULL, '{*}/|/{*}/|/{*}/|/Maniboc/|/Lingayen/|/Pangasinan/|/2401', '{*}/|/{*}/|/{*}/|/Maniboc/|/Lingayen/|/Pangasinan/|/2401', NULL, '09123456789', 'zoomcarroom1@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `related_questions`
--

CREATE TABLE `related_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rel_third_deg_details` varchar(255) DEFAULT NULL,
  `admin_offense_details` varchar(255) DEFAULT NULL,
  `criminal_charged_details` varchar(255) DEFAULT NULL,
  `convicted_details` varchar(255) DEFAULT NULL,
  `separated_details` varchar(255) DEFAULT NULL,
  `candidate_details` varchar(255) DEFAULT NULL,
  `pwd_details` varchar(255) DEFAULT NULL,
  `solo_parent_details` varchar(255) DEFAULT NULL,
  `indigenous_details` varchar(255) DEFAULT NULL
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
('7jL58szUp4FMcgDJqu4omLNbob8QVJYNvH5DYCEu', 79, '152.32.126.14', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6Im5RSHM1RWhuaTNKQlloaldqWkdBSjZKVEs1ZkVDaU94UmNmTDVLRkUiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQ2OiJodHRwczovL3Jocm1zcGIucHVibGljZGF0YXBvcnRhbC5jb20vZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1MToiaHR0cHM6Ly9yaHJtc3BiLnB1YmxpY2RhdGFwb3J0YWwuY29tL2Rhc2hib2FyZF91c2VyIjt9czo1OiJzdGF0ZSI7czo0MDoiWmU3NXNpZjZEU1JQNDNBdmpic0c1U05kbjBnbTZmTm1lTVFGN2pLNCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Nzk7czo0OiJmb3JtIjthOjM6e3M6MjoiYzEiO2E6Njc6e3M6Nzoic3VybmFtZSI7czoyOiIxMSI7czoxMDoiZmlyc3RfbmFtZSI7czoyOiIxMSI7czoxMToibWlkZGxlX25hbWUiO3M6MjoiMTEiO3M6MTQ6Im5hbWVfZXh0ZW5zaW9uIjtzOjM6IjExMSI7czoxMjoiY2l2aWxfc3RhdHVzIjtzOjY6InNpbmdsZSI7czoxMzoiZGF0ZV9vZl9iaXJ0aCI7czoxMDoiMjAyNi0wMi0wNCI7czoxNDoicGxhY2Vfb2ZfYmlydGgiO3M6MjoiZGEiO3M6MTE6ImNpdGl6ZW5zaGlwIjtzOjg6IkZpbGlwaW5vIjtzOjM6InNleCI7czo0OiJtYWxlIjtzOjEwOiJibG9vZF90eXBlIjtzOjI6IkErIjtzOjEyOiJ0ZWxlcGhvbmVfbm8iO047czo5OiJtb2JpbGVfbm8iO3M6MTE6IjA5MTIzNDU2Nzg5IjtzOjEzOiJlbWFpbF9hZGRyZXNzIjtzOjIyOiJ6b29tY2Fycm9vbTFAZ21haWwuY29tIjtzOjY6ImhlaWdodCI7czozOiIxMTAiO3M6Njoid2VpZ2h0IjtzOjM6IjExMCI7czoxMDoiZ3Npc19pZF9ubyI7TjtzOjEzOiJwYWdpYmlnX2lkX25vIjtOO3M6MTM6InBoaWxoZWFsdGhfbm8iO047czo5OiJzc3NfaWRfbm8iO047czo2OiJ0aW5fbm8iO047czoxODoiYWdlbmN5X2VtcGxveWVlX25vIjtOO3M6MTI6ImR1YWxfY291bnRyeSI7TjtzOjEyOiJyZXNfaG91c2Vfbm8iO047czoxMDoicmVzX3N0cmVldCI7TjtzOjExOiJyZXNfc3ViX3ZpbCI7TjtzOjg6InJlc19icmd5IjtzOjc6Ik1hbmlib2MiO3M6ODoicmVzX2NpdHkiO3M6ODoiTGluZ2F5ZW4iO3M6MTI6InJlc19wcm92aW5jZSI7czoxMDoiUGFuZ2FzaW5hbiI7czoxMToicmVzX3ppcGNvZGUiO3M6NDoiMjQwMSI7czoxMjoicGVyX2hvdXNlX25vIjtOO3M6MTA6InBlcl9zdHJlZXQiO047czoxMToicGVyX3N1Yl92aWwiO047czo4OiJwZXJfYnJneSI7czo3OiJNYW5pYm9jIjtzOjg6InBlcl9jaXR5IjtzOjg6IkxpbmdheWVuIjtzOjEyOiJwZXJfcHJvdmluY2UiO3M6MTA6IlBhbmdhc2luYW4iO3M6MTE6InBlcl96aXBjb2RlIjtzOjQ6IjI0MDEiO3M6MTQ6InNwb3VzZV9zdXJuYW1lIjtOO3M6MTc6InNwb3VzZV9maXJzdF9uYW1lIjtOO3M6MTg6InNwb3VzZV9taWRkbGVfbmFtZSI7TjtzOjIxOiJzcG91c2VfbmFtZV9leHRlbnNpb24iO047czoxNzoic3BvdXNlX29jY3VwYXRpb24iO047czoxNToic3BvdXNlX2VtcGxveWVyIjtOO3M6MjM6InNwb3VzZV9idXNpbmVzc19hZGRyZXNzIjtOO3M6MTY6InNwb3VzZV90ZWxlcGhvbmUiO047czo4OiJjaGlsZHJlbiI7YToxOntpOjA7YToyOntzOjQ6Im5hbWUiO047czozOiJkb2IiO047fX1zOjE0OiJmYXRoZXJfc3VybmFtZSI7TjtzOjE3OiJmYXRoZXJfZmlyc3RfbmFtZSI7TjtzOjE4OiJmYXRoZXJfbWlkZGxlX25hbWUiO047czoyMToiZmF0aGVyX25hbWVfZXh0ZW5zaW9uIjtOO3M6MjE6Im1vdGhlcl9tYWlkZW5fc3VybmFtZSI7czo2OiJoZGlhdWgiO3M6MjQ6Im1vdGhlcl9tYWlkZW5fZmlyc3RfbmFtZSI7czoxMjoiaWF1d2h3ZGl1YWh3IjtzOjI1OiJtb3RoZXJfbWFpZGVuX21pZGRsZV9uYW1lIjtOO3M6OToiZWxlbV9mcm9tIjtzOjc6IjIwMTItMDMiO3M6NzoiZWxlbV90byI7czo3OiIyMDE3LTA3IjtzOjEwOiJlbGVtX2Jhc2ljIjtzOjc6IlBSSU1BUlkiO3M6MTE6ImVsZW1fc2Nob29sIjtzOjg6ImF3ZGF3ZGF3IjtzOjE5OiJlbGVtX3llYXJfZ3JhZHVhdGVkIjtzOjQ6IjIwMTciO3M6MjA6ImVsZW1fYWNhZGVtaWNfaG9ub3JzIjtOO3M6ODoiamhzX2Zyb20iO3M6NzoiMjAyNi0wMyI7czo2OiJqaHNfdG8iO3M6NzoiMjAyNi0wMyI7czo5OiJqaHNfYmFzaWMiO047czoxMDoiamhzX3NjaG9vbCI7czo2OiJkd2FkYXciO3M6MTg6Impoc195ZWFyX2dyYWR1YXRlZCI7czo0OiIyMDI2IjtzOjE5OiJqaHNfYWNhZGVtaWNfaG9ub3JzIjtOO3M6MTA6InZvY2F0aW9uYWwiO2E6MTp7aTowO2E6Nzp7czo0OiJmcm9tIjtOO3M6MjoidG8iO047czo2OiJzY2hvb2wiO047czo1OiJiYXNpYyI7TjtzOjY6ImVhcm5lZCI7TjtzOjE0OiJ5ZWFyX2dyYWR1YXRlZCI7TjtzOjE1OiJhY2FkZW1pY19ob25vcnMiO047fX1zOjc6ImNvbGxlZ2UiO2E6MTp7aTowO2E6Nzp7czo0OiJmcm9tIjtzOjc6IjIwMjYtMDIiO3M6MjoidG8iO3M6NzoiMjAyNi0wMyI7czo2OiJzY2hvb2wiO3M6NDoiYXdkYSI7czo1OiJiYXNpYyI7czo2OiJhaGpkYmEiO3M6NjoiZWFybmVkIjtzOjM6IjEwMCI7czoxNDoieWVhcl9ncmFkdWF0ZWQiO3M6NDoiMjAyNiI7czoxNToiYWNhZGVtaWNfaG9ub3JzIjtOO319czo0OiJncmFkIjthOjE6e2k6MDthOjc6e3M6NDoiZnJvbSI7TjtzOjI6InRvIjtOO3M6Njoic2Nob29sIjtOO3M6NToiYmFzaWMiO047czo2OiJlYXJuZWQiO047czoxNDoieWVhcl9ncmFkdWF0ZWQiO047czoxNToiYWNhZGVtaWNfaG9ub3JzIjtOO319fXM6MjoiYzIiO2E6Mjp7czoxODoiYWxsX3VzZXJfd29ya19leHBzIjthOjA6e31zOjM0OiJhbGxfdXNlcl9jaXZpbF9zZXJ2aWNlX2VsaWdpYmlsaXR5IjthOjA6e319czoyOiJjNCI7YToyOTp7czoxMjoicmVsYXRlZF8zNF9hIjtzOjI6Im5vIjtzOjEyOiJyZWxhdGVkXzM0X2IiO3M6Mjoibm8iO3M6MTE6Imd1aWx0eV8zNV9hIjtzOjI6Im5vIjtzOjEzOiJjcmltaW5hbF8zNV9iIjtzOjI6Im5vIjtzOjEyOiJjb252aWN0ZWRfMzYiO3M6Mjoibm8iO3M6MTI6InNlcGFyYXRlZF8zNyI7czoyOiJubyI7czoxMjoiY2FuZGlkYXRlXzM4IjtzOjI6Im5vIjtzOjEzOiJyZXNpZ25lZF8zOF9iIjtzOjI6Im5vIjtzOjEyOiJpbW1pZ3JhbnRfMzkiO3M6Mjoibm8iO3M6MTU6ImluZGlnZW5vdXNfNDBfYSI7czoyOiJubyI7czo4OiJwd2RfNDBfYiI7czoyOiJubyI7czoxNjoic29sb19wYXJlbnRfNDBfYyI7czoyOiJubyI7czo5OiJyZWYxX25hbWUiO3M6NzoiYWRhd2RhZCI7czo4OiJyZWYxX3RlbCI7czoxMToiMDkxMjM0NTY3ODkiO3M6MTI6InJlZjFfYWRkcmVzcyI7czoxMToiMDkxMjM0NTY3ODkiO3M6OToicmVmMl9uYW1lIjtzOjExOiIwOTEyMzQ1Njc4OSI7czo4OiJyZWYyX3RlbCI7czoxMToiMDkxMjM0NTY3ODkiO3M6MTI6InJlZjJfYWRkcmVzcyI7czoxMToiMDkxMjM0NTY3ODkiO3M6OToicmVmM19uYW1lIjtzOjExOiIwOTEyMzQ1Njc4OSI7czo4OiJyZWYzX3RlbCI7czoxMToiMDkxMjM0NTY3ODkiO3M6MTI6InJlZjNfYWRkcmVzcyI7czoxMToiMDkxMjM0NTY3ODkiO3M6MTI6ImdvdnRfaWRfdHlwZSI7czoxOToiUGhpbFN5cy9OYXRpb25hbCBJRCI7czoxNDoiZ292dF9pZF9udW1iZXIiO3M6MTE6IjA5MTIzNDU2Nzg5IjtzOjE5OiJnb3Z0X2lkX2RhdGVfaXNzdWVkIjtzOjEwOiIyMDI2LTAyLTExIjtzOjIwOiJnb3Z0X2lkX3BsYWNlX2lzc3VlZCI7czoxMToiMDkxMjM0NTY3ODkiO3M6MTI6InBob3RvX3VwbG9hZCI7TjtzOjE5OiJjcmltaW5hbF8zNV9iX2FycmF5IjthOjI6e3M6NDoiZGF0ZSI7TjtzOjY6InN0YXR1cyI7Tjt9czoxMzoiZ292dF9pZF9vdGhlciI7TjtzOjc6InVzZXJfaWQiO2k6Nzk7fX1zOjEzOiJkYXRhX2xlYXJuaW5nIjthOjA6e31zOjE0OiJkYXRhX3ZvbHVudGFyeSI7YTowOnt9czoxNDoiZGF0YV9vdGhlckluZm8iO2E6NDp7czo1OiJza2lsbCI7YToxOntpOjA7Tjt9czoxMToiZGlzdGluY3Rpb24iO2E6MTp7aTowO047fXM6MTI6Im9yZ2FuaXphdGlvbiI7YToxOntpOjA7Tjt9czo3OiJ1c2VyX2lkIjtpOjc5O319', 1770012079),
('B4KdyXt2WhzuyR7zPE2DhHO7JSSNgmMESkAr7FAv', NULL, '1.37.67.133', 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/6.10.1 Chrome/134.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibldzQVliSWpnZ0FoV3E2NHJFZHNsYVZRZ2p6Wk15U2JGRnlWZ2NPNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vcmhybXNwYi5wdWJsaWNkYXRhcG9ydGFsLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6NTE6Imh0dHBzOi8vcmhybXNwYi5wdWJsaWNkYXRhcG9ydGFsLmNvbS9kYXNoYm9hcmRfdXNlciI7fX0=', 1770008702),
('duPPQ75jMdGlZnozDjXNGByz2qKwvfeXEx9ij2x2', NULL, '152.32.126.14', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiUmJ3UXlxdjdBQXBxUWY5Y1Rsa2o3WnJDZFlzNzJlWnc1WEJWMVpmQiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTc6Imh0dHBzOi8vcmhybXNwYi5wdWJsaWNkYXRhcG9ydGFsLmNvbS9hZG1pbi92YWNhbmNpZXNfbWFuYWdlbWVudC9maWx0ZXI/am9iPSZzZWFyY2g9JnNvcnQ9JnN0YXR1cz0iO31zOjUyOiJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxOToidmFjYW5jeUZpbHRlclNlYXJjaCI7TjtzOjE2OiJ2YWNhbmN5RmlsdGVySm9iIjtOO3M6MTk6InZhY2FuY3lGaWx0ZXJTdGF0dXMiO047fQ==', 1770013023),
('EAcOuqZHhiUq2gwePpMcGq4HeEsMGLHY3qgiBBZM', NULL, '1.37.67.199', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibzJCUDFuQTYyVndxakZGTkFIYUpWY3RublplSzBYd1lqdGI5RDVMSiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vcmhybXNwYi5wdWJsaWNkYXRhcG9ydGFsLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1770008703),
('eki1qPVTeeMBqULG7vN6AhEXmhRzcu9O2XkhfuJA', NULL, '1.37.67.199', 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/6.10.1 Chrome/134.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiS0o2Tm5zOXRrTVBhSVFuUHRMVkYxVW1laG1IYU1Ebjl1eTB0SUlvUyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MzoiaHR0cHM6Ly9yaHJtc3BiLnB1YmxpY2RhdGFwb3J0YWwuY29tL3Bkcy9jNSI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQyOiJodHRwczovL3Jocm1zcGIucHVibGljZGF0YXBvcnRhbC5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1770010617),
('lnVTjTfMqUz9XFk2p8lbWbRMWWgraJDjJ0vhYoka', NULL, '1.37.67.132', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWGNBS2JaSG8yMjBKNnBjNm1ITkNac3NPZzlFRmpPMm1OWVpoU3VPbSI7czo1OiJzdGF0ZSI7czo0MDoiend0SnRraWQ3a3pvQ00wNGJESmttTTliclFzYmJpSTNNajN2S010UCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDg6Imh0dHBzOi8vcmhybXNwYi5wdWJsaWNkYXRhcG9ydGFsLmNvbS9hdXRoL2dvb2dsZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1770009133),
('NRn3joWbIdu4eAoOUyxmUuy5FBSkGNAF4iagSelj', NULL, '1.37.67.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiekd5MGhvZ0p6SEZIa0twVVdMVWljRXRQWTRCd243bm1mMTNEa3dKRCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MzoiaHR0cHM6Ly9yaHJtc3BiLnB1YmxpY2RhdGFwb3J0YWwuY29tL3Bkcy9jMiI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQyOiJodHRwczovL3Jocm1zcGIucHVibGljZGF0YXBvcnRhbC5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1770010482),
('qqcBSLXOKLlzf4spL0w7u8s6XKJShyBGTEXpVQAW', NULL, '203.177.59.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZEloYUh5RlZYY0NwUVh5YXNVSXdQb1B6Z3pMRjNNbUlKU0xoU1B5cSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NjoiaHR0cHM6Ly9yaHJtc3BiLnB1YmxpY2RhdGFwb3J0YWwuY29tL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQyOiJodHRwczovL3Jocm1zcGIucHVibGljZGF0YXBvcnRhbC5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1770010645),
('r90lVbDVvuFp14t1gEASYbv09uuqyMgSiyMw5QTH', NULL, '14.102.171.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNmhVOU9vZGxxemxuU1hhMndtd01xRVN3bllWZXNTWkNnTFZVVXdVTyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MzoiaHR0cHM6Ly9yaHJtc3BiLnB1YmxpY2RhdGFwb3J0YWwuY29tL3Bkcy9jMSI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQyOiJodHRwczovL3Jocm1zcGIucHVibGljZGF0YXBvcnRhbC5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1770009169),
('wtF4hyqUY355d8KBS8ASGk8u0omr0Su5Y6qZxhb6', NULL, '1.37.67.200', 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/6.10.1 Chrome/134.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSWE0V0FQU0d0cVZKbDBuWDRtQm9Od1l1VVZDRkVFQzJLaHZpZ0RUNCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1MToiaHR0cHM6Ly9yaHJtc3BiLnB1YmxpY2RhdGFwb3J0YWwuY29tL2Rhc2hib2FyZF91c2VyIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vcmhybXNwYi5wdWJsaWNkYXRhcG9ydGFsLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1770008701);

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_documents`
--

CREATE TABLE `uploaded_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `original_name` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `stored_name` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `storage_path` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `mime_type` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `remarks` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(255) NOT NULL DEFAULT 'PENDING',
  `file_size_8b` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `isApproved` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `uploaded_documents`
--

INSERT INTO `uploaded_documents` (`id`, `created_at`, `updated_at`, `user_id`, `document_type`, `original_name`, `stored_name`, `storage_path`, `mime_type`, `remarks`, `status`, `file_size_8b`, `isApproved`) VALUES
(1, '2025-07-25 15:16:39', '2025-08-06 22:13:07', 5, 'pqe_result', '19b-Evaluation-Instrument-of-PSU-Partner-Agencies-Self-Ratee_2024_signed.pdf', 'JiXrFW1u1wXYpGDlGzMuo3ZX7gHE8qJY2eaAgYi3.pdf', 'uploads/pds-files/JiXrFW1u1wXYpGDlGzMuo3ZX7gHE8qJY2eaAgYi3.pdf', 'application/pdf', 'RACADIO-PRACTICUM-WEEKLY-REPORT_2024-(July7-11).pdf', 'PENDING', 367340, 0),
(2, '2025-07-25 15:16:39', '2025-07-29 16:48:25', 5, 'cert_eligibility', '12 INTERNSHIP RELEASE FORM_2024_Racadio.pdf', 'OjML0idgb9UAt7T7KLRtwd2AQOCVXgigWh51M3S4.pdf', 'uploads/pds-files/OjML0idgb9UAt7T7KLRtwd2AQOCVXgigWh51M3S4.pdf', 'application/pdf', '12 INTERNSHIP RELEASE FORM_2024_Racadio.pdf', 'PENDING', 211108, 0),
(3, '2025-07-25 15:16:39', '2025-07-29 16:48:25', 5, 'ipcr', '15-TRAINING-AGREEMENT-AND_2024.pdf', 'yEgBqnetj6BdDY5JqWMYv3a7miPRuVpyP4et2IDu.pdf', 'uploads/pds-files/yEgBqnetj6BdDY5JqWMYv3a7miPRuVpyP4et2IDu.pdf', 'application/pdf', '15-TRAINING-AGREEMENT-AND_2024.pdf', 'PENDING', 98406, 0),
(4, '2025-07-25 15:16:39', '2025-07-29 16:48:25', 5, 'non_academic', 'OJT_ENROLLMENT_RACADIO.pdf', 'XKR3XZDLfBNnw6b9Huh7myq9Jhj26xXWLXpwkCBR.pdf', 'uploads/pds-files/XKR3XZDLfBNnw6b9Huh7myq9Jhj26xXWLXpwkCBR.pdf', 'application/pdf', 'OJT_ENROLLMENT_RACADIO.pdf', 'PENDING', 52272, 0),
(5, '2025-07-25 15:16:39', '2025-07-29 16:48:25', 5, 'cert_training', 'Record-File-REV1.pdf', 'ILPkMOqPcjW05CFs47NSLyZb8uJFEmVJOC2QSL42.pdf', 'uploads/pds-files/ILPkMOqPcjW05CFs47NSLyZb8uJFEmVJOC2QSL42.pdf', 'application/pdf', 'Record-File-REV1.pdf', 'PENDING', 266865, 0),
(6, '2025-07-25 15:16:39', '2025-07-29 16:48:25', 5, 'designation_order', '02 CERTIFICATION OF UNITS EARNED FOR PRACTICUM_2024.docx.pdf', 'M7THiIsMyfPKEVOmjlrvE3ELe8byaQPDJcbwJUQh.pdf', 'uploads/pds-files/M7THiIsMyfPKEVOmjlrvE3ELe8byaQPDJcbwJUQh.pdf', 'application/pdf', '02 CERTIFICATION OF UNITS EARNED FOR PRACTICUM_2024.docx.pdf', 'PENDING', 129184, 0),
(7, '2025-07-25 15:16:39', '2025-07-29 16:55:00', 5, 'transcript_records', '02-CERTIFICATION-OF-UNITS-EARNED-FOR-PRACTICUM_2024.pdf', 'dfFdW5TFm7U2ODQXxwfsGiONO6tHczFagYQIosSO.pdf', 'uploads/pds-files/dfFdW5TFm7U2ODQXxwfsGiONO6tHczFagYQIosSO.pdf', 'application/pdf', '02-CERTIFICATION-OF-UNITS-EARNED-FOR-PRACTICUM_2024.pdf', 'Okay/Confirmed', 157267, 0),
(8, '2025-07-25 15:16:39', '2025-07-29 16:55:00', 5, 'photocopy_diploma', '12 INTERNSHIP RELEASE FORM_2024_Racadio.pdf', '3yaJI50gZBzxsTtv2LoIZ2MpX5e7IxOMcEUzmDu6.pdf', 'uploads/pds-files/3yaJI50gZBzxsTtv2LoIZ2MpX5e7IxOMcEUzmDu6.pdf', 'application/pdf', '12 INTERNSHIP RELEASE FORM_2024_Racadio.pdf', 'Okay/Confirmed', 211108, 0),
(9, '2025-07-25 15:16:39', '2025-07-29 16:48:25', 5, 'grade_masteraldoctorate', '01 APPLICATION FOR INTERNSHIP_2024.docx.pdf', 'VXyIpR31dKd1UY7KuApKbkpnk6LIo9iKXTe4Jzxx.pdf', 'uploads/pds-files/VXyIpR31dKd1UY7KuApKbkpnk6LIo9iKXTe4Jzxx.pdf', 'application/pdf', '01 APPLICATION FOR INTERNSHIP_2024.docx.pdf', 'PENDING', 111350, 0),
(10, '2025-07-25 15:16:39', '2025-07-29 16:48:25', 5, 'tor_masteraldoctorate', '01 APPLICATION FOR INTERNSHIP_2024.docx (1).pdf', 'fZUJOxqd7w4uAIbH5nm0ToRKPeyxYForC7r8Qxnx.pdf', 'uploads/pds-files/fZUJOxqd7w4uAIbH5nm0ToRKPeyxYForC7r8Qxnx.pdf', 'application/pdf', '01 APPLICATION FOR INTERNSHIP_2024.docx (1).pdf', 'PENDING', 148126, 0),
(11, '2025-07-25 15:16:39', '2025-07-29 16:48:25', 5, 'cert_employment', '02 CERTIFICATION OF UNITS EARNED FOR PRACTICUM_2024.docx.pdf', '6y1pr8FNW4bjnmx5gEPJeZ9042iis5WwqRips6Ar.pdf', 'uploads/pds-files/6y1pr8FNW4bjnmx5gEPJeZ9042iis5WwqRips6Ar.pdf', 'application/pdf', '02 CERTIFICATION OF UNITS EARNED FOR PRACTICUM_2024.docx.pdf', 'PENDING', 129184, 0),
(12, '2025-07-25 15:16:39', '2025-07-29 16:48:25', 5, 'other_documents', '02-CERTIFICATION-OF-UNITS-EARNED-FOR-PRACTICUM_2024.pdf', 'sklle0UMTc0LHNvkP4IrNaHTRtcl82zxJ0VwdFMa.pdf', 'uploads/pds-files/sklle0UMTc0LHNvkP4IrNaHTRtcl82zxJ0VwdFMa.pdf', 'application/pdf', '02-CERTIFICATION-OF-UNITS-EARNED-FOR-PRACTICUM_2024.pdf', 'PENDING', 157267, 0),
(13, '2025-07-27 11:56:57', '2025-07-27 11:56:57', 59, 'pqe_result', 'GPTZero AI Scan - undefined.pdf', 'Uv85wfVcWursQzzAf5pmcpjPq6P5UURtA1KjWbfh.pdf', 'uploads/pds-files/Uv85wfVcWursQzzAf5pmcpjPq6P5UURtA1KjWbfh.pdf', 'application/pdf', '', 'PENDING', 35906, 0),
(14, '2025-07-27 11:56:57', '2025-07-27 11:56:57', 59, 'cert_eligibility', 'GPTZero AI Scan - .pdf', 'dj1nsVvHTHG2w0BvfH2e2lv0YlJqbTAmf6CwfLj9.pdf', 'uploads/pds-files/dj1nsVvHTHG2w0BvfH2e2lv0YlJqbTAmf6CwfLj9.pdf', 'application/pdf', '', 'PENDING', 49508, 0),
(15, '2025-07-27 11:56:57', '2025-07-27 11:56:57', 59, 'transcript_records', 'GPTZero AI Scan - .pdf', 'rhi14RFszlPYyJPqvDWAT3E8qLRF8SVOS605GA4B.pdf', 'uploads/pds-files/rhi14RFszlPYyJPqvDWAT3E8qLRF8SVOS605GA4B.pdf', 'application/pdf', '', 'PENDING', 49508, 0),
(16, '2025-07-27 11:56:57', '2025-07-27 11:56:57', 59, 'photocopy_diploma', 'Final Paper Gender and Society - Quinto.docx.pdf', 'rS2fAPgjvG3B5hA1DU195sWZfXtRDA24JbEeGpvQ.pdf', 'uploads/pds-files/rS2fAPgjvG3B5hA1DU195sWZfXtRDA24JbEeGpvQ.pdf', 'application/pdf', '', 'PENDING', 104402, 0),
(17, '2025-07-28 08:46:28', '2025-07-28 10:42:09', 61, 'cert_eligibility', 'RACADIO-PRACTICUM-WEEKLY-REPORT_2024-1 (1).pdf', 'An7RR6fo5Lsd49ipzWHof13AT2nEITC8lcel2ARz.pdf', 'uploads/pds-files/An7RR6fo5Lsd49ipzWHof13AT2nEITC8lcel2ARz.pdf', 'application/pdf', 'RACADIO-PRACTICUM-WEEKLY-REPORT_2024-1 (1).pdf', 'PENDING', 109530, 0),
(18, '2025-07-28 08:46:28', '2025-07-28 10:42:09', 61, 'transcript_records', 'Form02_Marcos_Honey-May.docx.pdf', '2u46979K7fYnBaMTc2jIh5rgHQqpMryLqD20Y8r0.pdf', 'uploads/pds-files/2u46979K7fYnBaMTc2jIh5rgHQqpMryLqD20Y8r0.pdf', 'application/pdf', 'Form02_Marcos_Honey-May.docx.pdf', 'PENDING', 219428, 0),
(19, '2025-07-28 08:46:28', '2025-07-28 10:42:09', 61, 'photocopy_diploma', 'RACADIO-PRACTICUM-WEEKLY-REPORT_2024-1.pdf', 'fWHRe1VHCBFdYDYxizfLzNCXFrDYvhYtAWwZTkFT.pdf', 'uploads/pds-files/fWHRe1VHCBFdYDYxizfLzNCXFrDYvhYtAWwZTkFT.pdf', 'application/pdf', 'RACADIO-PRACTICUM-WEEKLY-REPORT_2024-1.pdf', 'PENDING', 109530, 0),
(20, '2025-07-28 09:14:11', '2025-07-28 10:42:09', 61, 'signed_pds', 'List-of-approved-WordPress-plugins-by-GWHS.xlsx - WordPress.pdf', 'JcU70Glip4KCQBeuzF9aqeHjHjZQxUF9V83wjMXY.pdf', 'uploads/pds-files/JcU70Glip4KCQBeuzF9aqeHjHjZQxUF9V83wjMXY.pdf', 'application/pdf', 'List-of-approved-WordPress-plugins-by-GWHS.xlsx - WordPress.pdf', 'PENDING', 111175, 0),
(21, '2025-07-28 14:53:54', '2025-07-31 18:39:56', 61, 'signed_work_exp_sheet', 'ENDORSEMENT-LETTER_BENECO.pdf', 'P5ZMBDdvGPbDs1mZZOEjbcXOZglDH2DSuBDqLzvS.pdf', 'uploads/pds-files/P5ZMBDdvGPbDs1mZZOEjbcXOZglDH2DSuBDqLzvS.pdf', 'application/pdf', 'ENDORSEMENT-LETTER_BENECO.pdf', 'PENDING', 434287, 0),
(22, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'pqe_result', '2203.02155v1.pdf', '3CYZqqzk4ftdC5zcrDLRDDRRG2FRCDZjKd68hpyN.pdf', 'uploads/pds-files/3CYZqqzk4ftdC5zcrDLRDDRRG2FRCDZjKd68hpyN.pdf', 'application/pdf', '', 'PENDING', 1797405, 0),
(23, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'cert_eligibility', '2203.02155v1.pdf', 'WYqejvfvAmSf3cT88Qolo9IJlAtWjIvlG8TPQ308.pdf', 'uploads/pds-files/WYqejvfvAmSf3cT88Qolo9IJlAtWjIvlG8TPQ308.pdf', 'application/pdf', '', 'PENDING', 1797405, 0),
(24, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'ipcr', 'QP-DILG-AS-RO-08-Recruitment-Selection-and-Placement-RSP-for-1st-and-2nd-Level-Position.pdf', '4s32VDeNTcZcJLuHWELLBORKJQhdoPtHCGwcLiGB.pdf', 'uploads/pds-files/4s32VDeNTcZcJLuHWELLBORKJQhdoPtHCGwcLiGB.pdf', 'application/pdf', '', 'PENDING', 9020068, 0),
(25, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'non_academic', 'AUTHORIZATION.pdf', 'XAus99i470KSpAHQz7cAXRCQOfp0JEpB1zFEDh9L.pdf', 'uploads/pds-files/XAus99i470KSpAHQz7cAXRCQOfp0JEpB1zFEDh9L.pdf', 'application/pdf', '', 'PENDING', 318017, 0),
(26, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'cert_training', 'AUTHORIZATION (1).pdf', 'jafd0TTMKxrb096ypk2gs41Y5yanitLC3ZtrVfnq.pdf', 'uploads/pds-files/jafd0TTMKxrb096ypk2gs41Y5yanitLC3ZtrVfnq.pdf', 'application/pdf', '', 'PENDING', 318017, 0),
(27, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'designation_order', 'AUTHORIZATION.pdf', 'QEcNcYAsVQFDI85ebixmUS2tuJm3Vl615wOCdgnp.pdf', 'uploads/pds-files/QEcNcYAsVQFDI85ebixmUS2tuJm3Vl615wOCdgnp.pdf', 'application/pdf', '', 'PENDING', 318017, 0),
(28, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'transcript_records', 'AUTHORIZATION.pdf', 'nd4oOg62fSOIdOOt4an5oZFVGHMCIClDPc0kWnGD.pdf', 'uploads/pds-files/nd4oOg62fSOIdOOt4an5oZFVGHMCIClDPc0kWnGD.pdf', 'application/pdf', '', 'PENDING', 318017, 0),
(29, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'photocopy_diploma', 'AUTHORIZATION.pdf', '3jaSSkRddSgPapmgUCe8dh9iv2HDcxkjEYjtuHWY.pdf', 'uploads/pds-files/3jaSSkRddSgPapmgUCe8dh9iv2HDcxkjEYjtuHWY.pdf', 'application/pdf', '', 'PENDING', 318017, 0),
(30, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'grade_masteraldoctorate', '2025_DOST_SEI_PRIMER_Practical_Training_Program-June-16-2025-1.pdf', 'RixQFKberpIhoiaCeG29fkXKwiCWVnk8fmmLrxCI.pdf', 'uploads/pds-files/RixQFKberpIhoiaCeG29fkXKwiCWVnk8fmmLrxCI.pdf', 'application/pdf', '', 'PENDING', 2174844, 0),
(31, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'tor_masteraldoctorate', '2203.02155v1.pdf', 'gXgCqVmGBczHzUjA4pkteCiSZD7gFqAc75u5Rp7x.pdf', 'uploads/pds-files/gXgCqVmGBczHzUjA4pkteCiSZD7gFqAc75u5Rp7x.pdf', 'application/pdf', '', 'PENDING', 1797405, 0),
(32, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'cert_employment', 'WirWo_Readings_07-07-25_17-16-24.pdf', 'as31vd4lrlz8nML8f0g4rC0Uaud39SimY2TsKhDX.pdf', 'uploads/pds-files/as31vd4lrlz8nML8f0g4rC0Uaud39SimY2TsKhDX.pdf', 'application/pdf', '', 'PENDING', 57715, 0),
(33, '2025-07-29 13:14:21', '2025-07-29 13:14:21', 56, 'other_documents', '2203.02155v1.pdf', 'DZqFVJlt0QvAHgzsgiRNmYRGmwuFjOJnohZ6HIX3.pdf', 'uploads/pds-files/DZqFVJlt0QvAHgzsgiRNmYRGmwuFjOJnohZ6HIX3.pdf', 'application/pdf', '', 'PENDING', 1797405, 0),
(34, '2025-07-29 13:29:00', '2025-07-31 18:38:01', 63, 'transcript_records', 'Rizal-Retraction.pdf', 'dkCkWlYX7O4itMs0v7BTqiXGhGxJo1jydbyrOLTd.pdf', 'uploads/pds-files/dkCkWlYX7O4itMs0v7BTqiXGhGxJo1jydbyrOLTd.pdf', 'application/pdf', 'Rizal-Retraction.pdf', 'PENDING', 912076, 0),
(35, '2025-07-29 13:29:00', '2025-07-31 18:38:01', 63, 'photocopy_diploma', 'Rizal-Retraction.pdf', 'wKjbA0dFFUv3AN1AtWFReU2zE6B8MgUzGwlMlHtV.pdf', 'uploads/pds-files/wKjbA0dFFUv3AN1AtWFReU2zE6B8MgUzGwlMlHtV.pdf', 'application/pdf', 'Rizal-Retraction.pdf', 'PENDING', 912076, 0),
(36, '2025-07-29 17:58:09', '2025-07-29 17:58:09', 68, 'transcript_records', 'PDS_fixed_v8.pdf', 'iry8L8W4hhmRUcLH0lpNuWV2iGP8oVOaaiOnClJj.pdf', 'uploads/pds-files/iry8L8W4hhmRUcLH0lpNuWV2iGP8oVOaaiOnClJj.pdf', 'application/pdf', '', 'PENDING', 3409648, 0),
(37, '2025-07-29 17:58:09', '2025-07-29 17:58:09', 68, 'photocopy_diploma', 'page 2 ayos.pdf', 'ehdfi3NmYi4jmCJv448NLUlbDLgJRV5jLdwn5v2a.pdf', 'uploads/pds-files/ehdfi3NmYi4jmCJv448NLUlbDLgJRV5jLdwn5v2a.pdf', 'application/pdf', '', 'PENDING', 93619, 0),
(38, '2025-07-31 18:36:40', '2025-07-31 18:39:56', 61, 'pqe_result', 'MARCOS, HONEY MAY.pdf', 'O0YLZVTF6RXEVRFrxKfa6f7vWMvdko2asxa5UBOu.pdf', 'uploads/pds-files/O0YLZVTF6RXEVRFrxKfa6f7vWMvdko2asxa5UBOu.pdf', 'application/pdf', 'MARCOS, HONEY MAY.pdf', 'PENDING', 532242, 0),
(39, '2025-10-21 15:33:04', '2026-01-06 13:49:41', 71, 'transcript_records', 'ExportPDS_2026-01-06_134424.pdf', 'bXXbNjvBI7reuEhZR8dtyJhlZP52B8Z5MdlKLwRm.pdf', 'uploads/pds-files/bXXbNjvBI7reuEhZR8dtyJhlZP52B8Z5MdlKLwRm.pdf', 'application/pdf', 'REAP FORM.pdf', 'Okay/Confirmed', 714320, 0),
(40, '2025-10-21 15:33:04', '2026-01-06 13:49:41', 71, 'photocopy_diploma', 'ExportPDS_2026-01-06_134424.pdf', 'VpH6yPRSzFAjb3xZyc4NwZ1bPVqkm4uQzBa03Sll.pdf', 'uploads/pds-files/VpH6yPRSzFAjb3xZyc4NwZ1bPVqkm4uQzBa03Sll.pdf', 'application/pdf', 'REAP FORM.pdf', 'Okay/Confirmed', 714320, 0),
(41, '2025-11-03 10:01:37', '2025-11-13 07:59:40', 74, 'transcript_records', 'lla_a71ad0d6-5d10-4c30-883f-ba18b96e2de6.pdf', 'TtSs1XfAF6TLWbzkanbLXmspLBzmPXxgU2wcI3G6.pdf', 'uploads/pds-files/TtSs1XfAF6TLWbzkanbLXmspLBzmPXxgU2wcI3G6.pdf', 'application/pdf', 'lla_a71ad0d6-5d10-4c30-883f-ba18b96e2de6.pdf', 'PENDING', 2556017, 0),
(42, '2025-11-03 10:01:37', '2025-11-13 07:59:40', 74, 'photocopy_diploma', 'lla_a71ad0d6-5d10-4c30-883f-ba18b96e2de6.pdf', 'oeX66aNfksI8JVc6vPvJsfyX8odkfMxladgC44Ky.pdf', 'uploads/pds-files/oeX66aNfksI8JVc6vPvJsfyX8odkfMxladgC44Ky.pdf', 'application/pdf', 'lla_a71ad0d6-5d10-4c30-883f-ba18b96e2de6.pdf', 'PENDING', 2556017, 0),
(43, '2026-01-06 13:40:09', '2026-01-06 13:49:41', 71, 'pqe_result', 'ExportPDS_2026-01-06_134424.pdf', 'mk4sV9jJTgHoShox610IS0fu8vL6orifD5a7Qqrf.pdf', 'uploads/pds-files/mk4sV9jJTgHoShox610IS0fu8vL6orifD5a7Qqrf.pdf', 'application/pdf', 'REAP FORM.pdf', 'Okay/Confirmed', 714320, 0),
(44, '2026-01-06 13:48:32', '2026-01-06 13:49:41', 71, 'signed_pds', 'ExportPDS_2026-01-06_134424.pdf', '3MbSK7Ox0l9zbFcWTGdT7E1vquSoQ11iEaoBHnFe.pdf', 'uploads/pds-files/3MbSK7Ox0l9zbFcWTGdT7E1vquSoQ11iEaoBHnFe.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(45, '2026-01-06 13:48:32', '2026-01-06 13:49:41', 71, 'signed_work_exp_sheet', 'ExportPDS_2026-01-06_134424.pdf', 'x7krsTbu7Y4VXLEwLd8qKJLVOXPkNcb5GBGwGNSe.pdf', 'uploads/pds-files/x7krsTbu7Y4VXLEwLd8qKJLVOXPkNcb5GBGwGNSe.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(46, '2026-01-06 13:48:32', '2026-01-06 13:49:41', 71, 'cert_eligibility', 'ExportPDS_2026-01-06_134424.pdf', 'P1VnVpl1SALqrTC0BShw06N3wGzyIytQUDRhwC7W.pdf', 'uploads/pds-files/P1VnVpl1SALqrTC0BShw06N3wGzyIytQUDRhwC7W.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(47, '2026-01-06 13:48:32', '2026-01-06 13:49:41', 71, 'ipcr', 'ExportPDS_2026-01-06_134424.pdf', 'Gh5VjjJnV8erai7F3RnofFSibgnzCNwuNeLVB3hj.pdf', 'uploads/pds-files/Gh5VjjJnV8erai7F3RnofFSibgnzCNwuNeLVB3hj.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(48, '2026-01-06 13:48:32', '2026-01-06 13:49:41', 71, 'non_academic', 'ExportPDS_2026-01-06_134424.pdf', 'Gt486nHYSOSKA14dZ3kCizbVqDXlS8yzKZ1ZtobK.pdf', 'uploads/pds-files/Gt486nHYSOSKA14dZ3kCizbVqDXlS8yzKZ1ZtobK.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(49, '2026-01-06 13:48:32', '2026-01-06 13:49:41', 71, 'cert_training', 'ExportPDS_2026-01-06_134424.pdf', 'hm0KNyolB3EebLAr2XjuTQKn0nOXwPmhy2Piu9fj.pdf', 'uploads/pds-files/hm0KNyolB3EebLAr2XjuTQKn0nOXwPmhy2Piu9fj.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(50, '2026-01-06 13:48:32', '2026-01-06 13:49:41', 71, 'designation_order', 'ExportPDS_2026-01-06_134424.pdf', 'Jci1UEMRHJiGb666LnOzSpVwKO59OJUWrDkYyIJy.pdf', 'uploads/pds-files/Jci1UEMRHJiGb666LnOzSpVwKO59OJUWrDkYyIJy.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(51, '2026-01-06 13:48:32', '2026-01-06 13:49:41', 71, 'grade_masteraldoctorate', 'ExportPDS_2026-01-06_134424.pdf', 'zEVodddAQieg4leCjxaY2ciUPPgs8VZ42vLqIPDy.pdf', 'uploads/pds-files/zEVodddAQieg4leCjxaY2ciUPPgs8VZ42vLqIPDy.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(52, '2026-01-06 13:48:32', '2026-01-06 13:49:41', 71, 'tor_masteraldoctorate', 'ExportPDS_2026-01-06_134424.pdf', '1LDh3bBeLM0lbGWe8phr3XNmGTClTw6Kzo0hZMcE.pdf', 'uploads/pds-files/1LDh3bBeLM0lbGWe8phr3XNmGTClTw6Kzo0hZMcE.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(53, '2026-01-06 13:48:33', '2026-01-06 13:49:41', 71, 'cert_employment', 'ExportPDS_2026-01-06_134424.pdf', 'aPBqld28sEtu3IkiZAePB2PRh9e60EFUraK8LBkT.pdf', 'uploads/pds-files/aPBqld28sEtu3IkiZAePB2PRh9e60EFUraK8LBkT.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(54, '2026-01-06 13:48:33', '2026-01-06 13:49:41', 71, 'other_documents', 'ExportPDS_2026-01-06_134424.pdf', 'u78OcCs42zaRkKYeVUUV4jBHxjYykVAxwxmesqGm.pdf', 'uploads/pds-files/u78OcCs42zaRkKYeVUUV4jBHxjYykVAxwxmesqGm.pdf', 'application/pdf', 'ExportPDS_2026-01-06_134424.pdf', 'Okay/Confirmed', 714320, 0),
(55, '2026-01-09 16:23:57', '2026-01-09 16:33:45', 77, 'pqe_result', 'Doc1.pdf', '6DmEDAUczQmJIKE4OL8rjgu2LDIb3jgQ32lWwk8k.pdf', 'uploads/pds-files/6DmEDAUczQmJIKE4OL8rjgu2LDIb3jgQ32lWwk8k.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(56, '2026-01-09 16:23:57', '2026-01-09 16:33:45', 77, 'transcript_records', 'Doc1.pdf', 'vIN3nt6me06TlcrVPJZk8YvMJf3YZcacCAtdfGmF.pdf', 'uploads/pds-files/vIN3nt6me06TlcrVPJZk8YvMJf3YZcacCAtdfGmF.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(57, '2026-01-09 16:23:57', '2026-01-09 16:33:45', 77, 'photocopy_diploma', 'Doc1.pdf', 'nftwYF1Jkjx0JYhRo13TBvGhVhS2hQf9u4aygzGM.pdf', 'uploads/pds-files/nftwYF1Jkjx0JYhRo13TBvGhVhS2hQf9u4aygzGM.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(58, '2026-01-09 16:32:08', '2026-01-09 16:41:23', 77, 'signed_pds', 'Doc1.pdf', 'gstJvYDNFs6H2j11q8VsBb1EVDbQEEnsvVYkFoPj.pdf', 'uploads/pds-files/gstJvYDNFs6H2j11q8VsBb1EVDbQEEnsvVYkFoPj.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(59, '2026-01-09 16:33:45', '2026-01-09 16:35:25', 77, 'signed_work_exp_sheet', 'Doc1.pdf', 'Eual9J2rnLqpF048TZ17TwS0ax0fI1WBfGA6WtVs.pdf', 'uploads/pds-files/Eual9J2rnLqpF048TZ17TwS0ax0fI1WBfGA6WtVs.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(60, '2026-01-09 16:33:45', '2026-01-09 16:35:25', 77, 'cert_eligibility', 'Doc1.pdf', 'P1RbdAUFsdrtHfwnOAZuZfslP7AK5BzqTVnpMK2W.pdf', 'uploads/pds-files/P1RbdAUFsdrtHfwnOAZuZfslP7AK5BzqTVnpMK2W.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(61, '2026-01-09 16:33:45', '2026-01-09 16:35:25', 77, 'ipcr', 'Doc1.pdf', 'GJNoXMaLva9KYn1fxIwM1qp5PQKGMLfAlzKOTry3.pdf', 'uploads/pds-files/GJNoXMaLva9KYn1fxIwM1qp5PQKGMLfAlzKOTry3.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(62, '2026-01-09 16:33:45', '2026-01-09 16:35:25', 77, 'non_academic', 'Doc1.pdf', 'tWAAKhm0URvNPjXkadCgTOQUzeXma3bewpXBEpXt.pdf', 'uploads/pds-files/tWAAKhm0URvNPjXkadCgTOQUzeXma3bewpXBEpXt.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(63, '2026-01-09 16:33:45', '2026-01-09 16:35:25', 77, 'cert_training', 'Doc1.pdf', 'epxXWJTxWSlsvcFxUlx0VyaRnYs1slxPBoLbAagV.pdf', 'uploads/pds-files/epxXWJTxWSlsvcFxUlx0VyaRnYs1slxPBoLbAagV.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(64, '2026-01-09 16:33:45', '2026-01-09 16:35:25', 77, 'designation_order', 'Doc1.pdf', '546fdQ79Ux9LOK2xaWY4KIfLvb5nwfgbhPovIo0f.pdf', 'uploads/pds-files/546fdQ79Ux9LOK2xaWY4KIfLvb5nwfgbhPovIo0f.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(65, '2026-01-09 16:33:45', '2026-01-09 16:35:25', 77, 'grade_masteraldoctorate', 'Doc1.pdf', 'oCbnAc42L6qg1a9IVkaKSWljss8y1jY9fMNWRKS8.pdf', 'uploads/pds-files/oCbnAc42L6qg1a9IVkaKSWljss8y1jY9fMNWRKS8.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(66, '2026-01-09 16:33:45', '2026-01-09 16:35:25', 77, 'tor_masteraldoctorate', 'Doc1.pdf', 'yRA0kPkYb5pSnwIZuJNjh8RaZ9d2ddTOFQ8zQnzE.pdf', 'uploads/pds-files/yRA0kPkYb5pSnwIZuJNjh8RaZ9d2ddTOFQ8zQnzE.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(67, '2026-01-09 16:33:45', '2026-01-09 16:35:25', 77, 'cert_employment', 'Doc1.pdf', '2lN82acjhwSZ6kag5wwAu2uF2tObBsqDhivwOtCE.pdf', 'uploads/pds-files/2lN82acjhwSZ6kag5wwAu2uF2tObBsqDhivwOtCE.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(68, '2026-01-09 16:33:45', '2026-01-09 16:35:25', 77, 'other_documents', 'Doc1.pdf', '5Fk8AqyWEpuHe2Y980YiQyqr3dvwPr64MjD2oXQE.pdf', 'uploads/pds-files/5Fk8AqyWEpuHe2Y980YiQyqr3dvwPr64MjD2oXQE.pdf', 'application/pdf', 'Doc1.pdf', 'PENDING', 179531, 0),
(69, '2026-02-02 13:38:28', '2026-02-02 13:57:34', 79, 'transcript_records', 'img20260120_16045925_signed.pdf', 'GxQ6px7rd9pqxjUR19FdRyr0lrfJ70BeL6umevEp.pdf', 'uploads/pds-files/GxQ6px7rd9pqxjUR19FdRyr0lrfJ70BeL6umevEp.pdf', 'application/pdf', 'img20260120_16045925_signed.pdf', 'PENDING', 440567, 0),
(70, '2026-02-02 13:38:28', '2026-02-02 13:57:34', 79, 'photocopy_diploma', 'img20260120_16045925_signed.pdf', '7H2KxqiN9Qm7gJGjrX48CaXNweBIsPMeH8KoQHXQ.pdf', 'uploads/pds-files/7H2KxqiN9Qm7gJGjrX48CaXNweBIsPMeH8KoQHXQ.pdf', 'application/pdf', 'img20260120_16045925_signed.pdf', 'PENDING', 440567, 0),
(71, '2026-02-02 13:58:46', '2026-02-02 13:58:46', 79, 'signed_pds', 'img20260120_16045925_signed_signed.pdf', '2vDvygMDW9JFrbnsukIzW5JuaRURKQNq1jWeaeUr.pdf', 'uploads/pds-files/2vDvygMDW9JFrbnsukIzW5JuaRURKQNq1jWeaeUr.pdf', 'application/pdf', '', 'PENDING', 514157, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT 0,
  `has_pds` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `is_admin`, `is_super_admin`, `has_pds`, `remember_token`, `otp`, `otp_expires_at`, `created_at`, `updated_at`) VALUES
(1, 'sample', 'sample1@example.com', '2025-07-22 15:29:01', '$2y$12$V1EuiJ00CdDNQuB1Pidga.Eh6aPBE6/mmsr6FOLJkBnqFjCaEcPWS', 0, 0, 0, 'SAWVDvaERUwSC1D4zXn6mPeZ2563SKSHM5i4iD79vsM7tQWxkmkKrGGMjGyB', NULL, NULL, '2025-07-22 15:29:01', '2025-07-22 15:29:01'),
(2, 'sample_uv', 'sample2@example.com', NULL, '$2y$12$lZmN8Da4T3xMsTeJ00jPhOLHrHrFXuPV17SOChcGtaaqCWLurGmhe', 0, 0, 0, 'xLlf2vT7ny', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(3, 'admin', 'sample3@example.com', '2025-07-22 15:29:02', '$2y$12$8C28F3s2mUXjmNspAabT3.L.MLzXXTYhf/4VidCjllxkak5M9NWWy', 1, 0, 0, 'MQUZojMIdJ', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(4, 'superadmin', 'sample4@example.com', '2025-07-22 15:29:02', '$2y$12$5MkkegGFJc9NkdljeZaKLe707eEvIp8R8Su4AbPHR7oVXnkxyATTe', 1, 1, 0, '7X7yLu3Jl7', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(5, 'pagso', 'pagso@gmail.com', '2025-07-22 15:29:02', '$2y$12$uD1gRyumLwQ6lwSLcwV4X.5V5paU7PcJINSRdikQuxHWCXyDZ55r6', 0, 0, 0, 'IZmpxbKPUpYtUIlrMz2knL7jrSWglK10RtFvbTHUDjK8A54HcJ8xKjEGMXtJ', NULL, NULL, '2025-07-22 15:29:02', '2025-08-06 22:13:07'),
(6, 'Miss Rhea Huel Sr.', 'amiya81@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'HzY3AE32vG', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(7, 'Zachariah Gislason', 'sonny76@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'mXX7GbB7v9', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(8, 'Emma Homenick', 'hailee.dooley@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'DZ31SY1DXH', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(9, 'Kristofer Durgan', 'mills.rahul@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'cPhsEzr5A8', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(10, 'Alec Toy', 'kelly.larson@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'dFoOHkZFB0', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(11, 'Miss Lorna Halvorson', 'breynolds@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'TzSsyZGP48', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(12, 'Werner Huels', 'myrtle18@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'TR53I7W2Ve', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(13, 'Dawn Hamill', 'zpouros@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, '2Z0I2qAdyp', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(14, 'Mrs. Maribel Reinger DDS', 'flatley.stacy@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'SidJENSO26', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(15, 'Marielle Hessel', 'abe38@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'vecPrwRuTl', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(16, 'Casimer O\'Hara', 'jfeeney@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'SEtdLjGEkk', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(17, 'Gerardo Rolfson', 'sipes.madison@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'KH8676zSt5', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(18, 'Kallie Rutherford', 'neal58@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'Lu3bztvAZK', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(19, 'Madelynn Hill', 'edison28@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'iC2wCi8yQs', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(20, 'Florencio Moore', 'westley62@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'ndO2TMw7kY', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(21, 'Prof. Hanna Lemke', 'weldon.ryan@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, '8CsskDBsyc', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(22, 'Toni Dibbert', 'enrique.williamson@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'suYSbyNaJD', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(23, 'Malika Gusikowski', 'olson.elvera@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'eos9VarRge', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(24, 'Mr. Bernie Bartoletti MD', 'hudson32@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'CC60ElMMWv', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(25, 'Mr. Jonathan Bahringer', 'lizzie.price@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'Sgd36Y7ouk', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(26, 'Ezequiel Hand', 'nikolaus.austin@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'C2u3hWsfoO', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(27, 'Kaycee VonRueden', 'rsanford@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'avQXK6MmIM', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(28, 'Adelle McClure', 'israel16@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'eccZD4sAho', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(29, 'Miss Vivian Reynolds', 'torrey.morissette@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'mOGXemCtMk', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(30, 'Prof. Spencer Renner MD', 'abdullah.kunze@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'Z5ZUcxpJDV', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(31, 'Karolann Mills DDS', 'huel.marc@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'DM58rkcriC', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(32, 'Marcellus Okuneva', 'amoore@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'pk2yn9km97', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(33, 'Narciso Effertz', 'bconroy@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'qYdWmtuRtW', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(34, 'Hassan Sauer', 'aron.murphy@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'JBtgSre7OS', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(35, 'Dr. Elouise Kilback DDS', 'collier.earline@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'TBhcemsmN3', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(36, 'Miss Antonietta Dietrich', 'fbeatty@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'FbS9HvkUAN', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(37, 'Janet Hoeger', 'qrunolfsdottir@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'XNKp0fd8Vc', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(38, 'Lyric Howe', 'efay@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'SQ4DzUaXFe', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(39, 'Mrs. Verna Stracke', 'qgrady@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'iJhSqbIkyp', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(40, 'Duane Schiller', 'aanderson@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'DYS4YqCkJs', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(41, 'Deron Beier', 'tito.schultz@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, '0i6XW6znQc', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(42, 'Margarete Larkin', 'zosinski@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'kqRCBWW1dV', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(43, 'Miss Whitney Klein', 'jreichert@example.org', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'POjqYNWDaY', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(44, 'Elizabeth Gibson', 'eugenia67@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'WL7j64ipBG', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(45, 'Rashad Mohr', 'kiarra.renner@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'o6GPu0uwDF', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(46, 'Johanna Dickens', 'corkery.pamela@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'QPEoAX7n9E', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(47, 'Reilly Kuvalis IV', 'fhahn@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'OfsdMB1lG2', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(48, 'Miss Hailie Haag MD', 'pcarter@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'lgPughlRgs', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(49, 'Jalen Kshlerin', 'sallie72@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'tNeEAjQ9Px', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(50, 'Ernestina Emard', 'kory.johnston@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'Z2zYrmCj8d', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(51, 'Lowell Muller', 'tara79@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'ParjPS7EEN', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(52, 'Prof. Marquis Hudson', 'jayme.weimann@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'PYNG2Ip7V9', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(53, 'Harley Gusikowski', 'carmela.auer@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'fyEaMhGDD9', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(54, 'Filiberto Daugherty', 'alvera.johns@example.com', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'SDO95CAmCn', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(55, 'Ms. Tania Walsh', 'solson@example.net', '2025-07-22 15:29:02', '$2y$12$evlZy.KcmUMu/b.q/2lrZOVXmrh8YNFAfsHy73z7O/3ofaxkcCisi', 0, 0, 0, 'oklkrMOpqu', NULL, NULL, '2025-07-22 15:29:02', '2025-07-22 15:29:02'),
(56, 'EJ Cabanela', 'edrienecabanela@gmail.com', NULL, '$2y$12$7ymD39F7.r0GEDF6yOKaS.QkBadlKo9q.8PWEzhvYWh1F/FXfLN/a', 0, 0, 0, NULL, '832233', '2025-07-28 21:10:21', '2025-07-22 15:29:47', '2025-07-31 16:35:19'),
(57, 'Joash Irvin Santos', 'asjo0509@gmail.com', NULL, '$2y$12$rTOzDcINM9DIZwpaPSwAI.YuZMP..aDYgtbugxraS6gFq8ddLdH8C', 0, 0, 0, NULL, NULL, NULL, '2025-07-22 19:22:16', '2025-07-22 19:22:16'),
(58, 'PAGSOLINGAN MARK ANGELU', 'pagsolinganmark04@gmail.com', NULL, '$2y$12$VOIlRS5pXqHMBE67.0J5/OOYUCuLGcC726Y/UbkDh/2PaygObCfFG', 0, 0, 0, NULL, NULL, NULL, '2025-07-23 19:45:25', '2026-01-11 19:17:02'),
(59, 'Joash Santos', 'juaszie@gmail.com', NULL, '$2y$12$k5Ki2IN.G0EfHXPpKVy6FOdcA7zXEjV8Az2bLC7lzB.oJw38qEg1C', 0, 0, 0, NULL, NULL, NULL, '2025-07-23 19:46:29', '2025-07-23 19:46:29'),
(60, 'Josah', 'xnzjilm@gmail.com', '2025-07-25 09:39:42', '$2y$12$I87MyQGx6UQgCpj5Of3W9.LlNmXRg7rk.mKTUE1QqaHcZhoQLqvh2', 0, 0, 0, NULL, NULL, NULL, '2025-07-25 09:39:42', '2025-07-25 09:39:42'),
(61, 'Cristhan Obillo', 'cristhangray@gmail.com', '2025-07-28 08:30:50', '$2y$12$DQYV2ZmokJg7EKBovlRFWO.rTIpYwszQkdalE9Y3KXG/7rxkTuJx2', 0, 0, 0, NULL, NULL, NULL, '2025-07-28 08:30:50', '2025-07-31 18:35:40'),
(62, 'Ash Jo', 'ashcie1105@gmail.com', NULL, '$2y$12$njtaEcTemnhX0DLYSm/Mhe7hRPegT97z7KjclEJzpZQHUR3gLXNO.', 0, 0, 0, NULL, NULL, NULL, '2025-07-28 18:42:35', '2025-07-28 18:42:35'),
(63, 'Janel Antolin', 'janelantolin20@gmail.com', NULL, '$2y$12$c4bWHANbzNn8TrYcPEPN8.MvtWqLcsZOJqxX9fEJJISYM2ne5hsN.', 0, 0, 0, NULL, NULL, NULL, '2025-07-29 13:10:53', '2025-07-29 13:27:45'),
(64, 'Christian', 'caradioako@gmail.com', '2025-07-29 13:25:24', '$2y$12$1Z2.soZR.vgtqIlVolvtSeDjKZSHjK8sZXv99qosAu75.iJOqoAGK', 0, 0, 0, NULL, NULL, NULL, '2025-07-29 13:25:24', '2025-07-29 13:26:26'),
(65, 'Kimberly Dumling-Muñoz', 'munozkimberly060@gmail.com', NULL, '$2y$12$0Ej/iK2AHt9e/x/WgqCLcOHrZsQVr.MYmrp8VErMVE/ms1JMEhl0u', 0, 0, 0, NULL, NULL, NULL, '2025-07-29 16:14:32', '2025-07-29 16:14:32'),
(66, 'Jayssa Matias', 'jaysmatias15@gmail.com', NULL, '$2y$12$DeX.DYVj/5G7FjFbivvZj.QlwvWBkKRpE0SZwDoBCMeXK4Fk358gu', 0, 0, 0, NULL, NULL, NULL, '2025-07-29 16:15:14', '2025-07-29 16:15:14'),
(68, 'Edriene Jay Cabanela', '22ur0338@psu.edu.ph', NULL, '$2y$12$Dr0zCbVY9Q/hBGdKYva/Ke.gyC/3kalq.4S0QpLUljr8pKHXxPdF6', 0, 0, 0, NULL, NULL, NULL, '2025-07-29 17:34:18', '2025-07-29 17:49:14'),
(69, 'JOHN ANDREW SANTOS', '202411166@gordoncollege.edu.ph', NULL, '$2y$12$ZoHa6N8g5kzK3dY29Ev/4eeOEtCKRo3pOzG8tEm1cDbCzqmnsv4P2', 0, 0, 0, NULL, NULL, NULL, '2025-08-06 21:43:08', '2025-08-06 21:43:08'),
(70, 'CAR HRRS', 'regioncarpersonnel@gmail.com', NULL, '$2y$12$fGVhwwCZBcwFnkUYeORfm.tKFVYxATaka7usTggTW20y3xDQs5xsq', 0, 0, 0, NULL, NULL, NULL, '2025-08-13 16:53:06', '2025-08-13 16:53:06'),
(71, 'SubayBAYAN Cordillera', 'subaybayancordillera@gmail.com', NULL, '$2y$12$yrohyWolfZUyIylDOgypq.8sTaKkeeVfkZOUQB5jKhf9.JP6TFopO', 0, 0, 0, NULL, NULL, NULL, '2025-09-11 15:09:12', '2026-01-06 13:48:33'),
(72, 'Lawrence Joces Ortiz', '22ur0574@psu.edu.ph', NULL, '$2y$12$cTrS97ysEmmuByImDA.Yp.ePcs1qLryjKwRVCX17JcDtBzTq3HdIu', 0, 0, 0, NULL, NULL, NULL, '2025-10-27 21:32:40', '2025-10-27 21:32:40'),
(73, 'Cherry Orpano', 'cherryorpano7@gmail.com', NULL, '$2y$12$iGpcxGcEo/fDzc6wyVlbEOq1rGQO4MDqpfSjWdHSmXCU8i8heXXXu', 0, 0, 0, NULL, NULL, NULL, '2025-11-02 14:55:54', '2025-11-02 14:55:54'),
(74, 'Billy John Ferreol', 'billyferreol@gmail.com', NULL, '$2y$12$yl5bjKSHj7hs7CxJAz0IjOnEqrLoE/mYbXyZDHOqjvmbBcOVFAtZG', 0, 0, 0, NULL, NULL, NULL, '2025-11-03 09:55:08', '2025-11-13 07:51:18'),
(75, 'Mark Angelu Pagsolingan', '22ur0608@psu.edu.ph', NULL, '$2y$12$1DfB6xwZEJKoMCm0aAH15.257Rohnqsufg/wr7SPettrNitL2Cm1m', 0, 0, 0, NULL, NULL, NULL, '2025-12-22 01:40:36', '2025-12-22 01:40:36'),
(76, 'Clayre Chaokas II', 'ccchaokas@dilg.gov.ph', NULL, '$2y$12$dVlbLQ8vveD0g67qcC7qeusDSOGUHnKrCGSEbENMsZC4IzhOKHpym', 0, 0, 0, NULL, NULL, NULL, '2025-12-22 16:45:14', '2025-12-22 16:45:14'),
(77, 'ZoomCAR Room2', 'zoomcarroom2@gmail.com', NULL, '$2y$12$4it8hPL0AEg7iUTH/JONue5RnNN2Je2sM3PeKUJOfqj/OVrL1ELHu', 0, 0, 0, NULL, NULL, NULL, '2026-01-09 16:17:45', '2026-01-09 16:41:23'),
(78, 'Billy John Ferreol', 'bdferreol@dilg.gov.ph', NULL, '$2y$12$nRpYLk2DeALtIQgaDd8Wj.iDHvirMCftzyzcejjqgugClkiYvL16q', 0, 0, 0, NULL, NULL, NULL, '2026-02-02 13:12:18', '2026-02-02 13:12:18'),
(79, 'Room1 ZoomCAR', 'zoomcarroom1@gmail.com', NULL, '$2y$12$fj.TyQ2DgvbXChtgl95WO.EvD9RaVctzzfv5Beo06wCfyuCWt69ee', 0, 0, 0, NULL, NULL, NULL, '2026-02-02 13:29:30', '2026-02-02 13:58:46');

-- --------------------------------------------------------

--
-- Table structure for table `voluntary_works`
--

CREATE TABLE `voluntary_works` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `voluntary_org` varchar(255) NOT NULL,
  `voluntary_from` date NOT NULL,
  `voluntary_to` date NOT NULL,
  `voluntary_hours` smallint(6) NOT NULL,
  `voluntary_position` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `voluntary_works`
--

INSERT INTO `voluntary_works` (`id`, `created_at`, `updated_at`, `user_id`, `voluntary_org`, `voluntary_from`, `voluntary_to`, `voluntary_hours`, `voluntary_position`) VALUES
(6, '2025-07-29 13:29:00', '2025-07-29 13:29:00', 63, 'DOST Organization', '2024-07-29', '2025-07-29', 2400, 'Volunteer'),
(7, '2025-07-31 18:36:40', '2025-07-31 18:36:40', 61, 'vol1', '2025-07-16', '2025-07-17', 13, 'BOSS');

-- --------------------------------------------------------

--
-- Table structure for table `work_experiences`
--

CREATE TABLE `work_experiences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `work_exp_from` date NOT NULL DEFAULT curdate(),
  `work_exp_to` date NOT NULL DEFAULT curdate(),
  `work_exp_position` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `work_exp_department` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `work_exp_salary` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `work_exp_grade` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `work_exp_status` varchar(255) NOT NULL DEFAULT 'NOINPUT',
  `work_exp_govt_service` char(255) NOT NULL DEFAULT '~'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_experiences`
--

INSERT INTO `work_experiences` (`id`, `created_at`, `updated_at`, `user_id`, `work_exp_from`, `work_exp_to`, `work_exp_position`, `work_exp_department`, `work_exp_salary`, `work_exp_grade`, `work_exp_status`, `work_exp_govt_service`) VALUES
(13, '2025-07-29 13:21:13', '2025-07-29 13:21:13', 63, '2025-06-19', '2025-07-29', 'Web Developer', 'Department of the Interior and Local Government - Cordillera Administrative Region', '0', '0', 'Temporary', 'Y'),
(14, '2025-07-31 18:36:40', '2025-07-31 18:34:19', 61, '2025-07-23', '2025-07-29', 'boss', 'boas', '23232', '22', 'Permanent', 'Y'),
(16, '2025-08-06 22:12:21', '2025-08-06 22:10:25', 5, '2025-07-21', '2025-07-30', 'IT', 'Regional', '50000', '23', 'Permanent', 'Y'),
(18, '2025-11-03 10:02:37', '2025-11-03 10:02:15', 74, '2025-11-03', '2025-11-03', 'Hhhhh', 'Hhh', '666', '12', 'Permanent', 'Y'),
(20, '2026-01-06 13:43:20', '2026-01-06 13:42:42', 71, '2026-01-07', '2026-01-07', '1231', '2312', '1231231', '12312', 'Contractual', 'Y'),
(22, '2026-01-09 16:25:59', '2026-01-09 16:25:25', 77, '2026-01-14', '2026-01-14', '111', '111', '11', '1111', 'Temporary', 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `work_exp_sheet`
--

CREATE TABLE `work_exp_sheet` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `position` varchar(255) NOT NULL,
  `office` varchar(255) NOT NULL,
  `supervisor` varchar(255) NOT NULL,
  `agency` varchar(255) NOT NULL,
  `accomplishments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`accomplishments`)),
  `duties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`duties`)),
  `isDisplayed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_exp_sheet`
--

INSERT INTO `work_exp_sheet` (`id`, `user_id`, `start_date`, `end_date`, `position`, `office`, `supervisor`, `agency`, `accomplishments`, `duties`, `isDisplayed`, `created_at`, `updated_at`) VALUES
(2, 59, '2025-07-08', NULL, 'cczxcz', 'zxczxc', 'zxcxz', 'xzcxzc', '[\"zczxc\"]', '[\"cxzcxz\"]', 1, '2025-07-29 09:08:47', '2025-07-29 09:08:47'),
(5, 5, '2025-07-28', NULL, 'IT OFFICER', 'HR DEPARTMENT', 'HR DEPARTMENT', 'QUEZON CITY', '[\"30%\"]', '[\"Handled Network\"]', 1, '2025-07-29 17:32:13', '2025-07-29 17:32:13'),
(6, 5, '2025-07-06', '2025-07-28', 'ehnh', '4ttr', 'thtt', '4gthn', '[\"rgrgggt\"]', '[\"4tgtgtgt\"]', 1, '2025-07-29 17:32:13', '2025-07-29 17:32:13'),
(8, 56, '2025-07-31', NULL, 'YES', 'N9', 'Yes', 'No', '[\"None\"]', '[\"Noje\"]', 1, '2025-08-14 14:39:13', '2025-08-14 14:39:13'),
(9, 57, '2025-11-27', NULL, 'fesdf', 'cvcv', 'vfxcvxc', 'vcxvxc', '[\"vxcvcxvvc\"]', '[\"cxvxcv\"]', 1, '2025-11-15 12:27:51', '2025-11-15 12:27:51'),
(10, 71, '2026-01-07', NULL, '123123123123123123123123123', 'wadawdaw', 'dawdwa', 'awdawdaw', '[\"dawdwa\"]', '[\"awdawdawd\"]', 1, '2026-01-06 13:43:53', '2026-01-06 13:43:53'),
(11, 77, '2026-01-07', '2026-01-07', '09123456789', '09123456789', '09123456789', '09123456789', '[\"09123456789\"]', '[\"09123456789\"]', 1, '2026-01-09 16:24:52', '2026-01-09 16:24:52'),
(13, 79, '2026-02-02', NULL, 'ISA III', 'awdaw', 'awda', 'awda', '[\"awda\"]', '[\"awda\"]', 1, '2026-02-02 13:47:21', '2026-02-02 13:47:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_username_unique` (`username`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `civil_service_eligibilities`
--
ALTER TABLE `civil_service_eligibilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `civil_service_eligibilities_user_id_foreign` (`user_id`);

--
-- Indexes for table `educational_backgrounds`
--
ALTER TABLE `educational_backgrounds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `educational_backgrounds_user_id_foreign` (`user_id`);

--
-- Indexes for table `exam_details`
--
ALTER TABLE `exam_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_items`
--
ALTER TABLE `exam_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `family_backgrounds`
--
ALTER TABLE `family_backgrounds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `family_backgrounds_user_id_foreign` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_vacancies`
--
ALTER TABLE `job_vacancies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `learning_and_developments`
--
ALTER TABLE `learning_and_developments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_learning_combination` (`id`,`learning_from`,`user_id`),
  ADD KEY `learning_and_developments_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `misc_infos`
--
ALTER TABLE `misc_infos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `misc_infos_user_id_foreign` (`user_id`);

--
-- Indexes for table `other_information`
--
ALTER TABLE `other_information`
  ADD PRIMARY KEY (`id`),
  ADD KEY `other_information_user_id_foreign` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_information`
--
ALTER TABLE `personal_information`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_information_user_id_foreign` (`user_id`);

--
-- Indexes for table `related_questions`
--
ALTER TABLE `related_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `related_questions_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `uploaded_documents`
--
ALTER TABLE `uploaded_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_documents_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `voluntary_works`
--
ALTER TABLE `voluntary_works`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_voluntary_combination` (`voluntary_org`,`voluntary_from`,`user_id`),
  ADD KEY `voluntary_works_user_id_foreign` (`user_id`);

--
-- Indexes for table `work_experiences`
--
ALTER TABLE `work_experiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work_experiences_user_id_foreign` (`user_id`);

--
-- Indexes for table `work_exp_sheet`
--
ALTER TABLE `work_exp_sheet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work_exp_sheet_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=803;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `civil_service_eligibilities`
--
ALTER TABLE `civil_service_eligibilities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `educational_backgrounds`
--
ALTER TABLE `educational_backgrounds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `exam_details`
--
ALTER TABLE `exam_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `exam_items`
--
ALTER TABLE `exam_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `family_backgrounds`
--
ALTER TABLE `family_backgrounds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_vacancies`
--
ALTER TABLE `job_vacancies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `learning_and_developments`
--
ALTER TABLE `learning_and_developments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `misc_infos`
--
ALTER TABLE `misc_infos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `other_information`
--
ALTER TABLE `other_information`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `personal_information`
--
ALTER TABLE `personal_information`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `related_questions`
--
ALTER TABLE `related_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uploaded_documents`
--
ALTER TABLE `uploaded_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `voluntary_works`
--
ALTER TABLE `voluntary_works`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `work_experiences`
--
ALTER TABLE `work_experiences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `work_exp_sheet`
--
ALTER TABLE `work_exp_sheet`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `civil_service_eligibilities`
--
ALTER TABLE `civil_service_eligibilities`
  ADD CONSTRAINT `civil_service_eligibilities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `educational_backgrounds`
--
ALTER TABLE `educational_backgrounds`
  ADD CONSTRAINT `educational_backgrounds_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `family_backgrounds`
--
ALTER TABLE `family_backgrounds`
  ADD CONSTRAINT `family_backgrounds_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `learning_and_developments`
--
ALTER TABLE `learning_and_developments`
  ADD CONSTRAINT `learning_and_developments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `misc_infos`
--
ALTER TABLE `misc_infos`
  ADD CONSTRAINT `misc_infos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `other_information`
--
ALTER TABLE `other_information`
  ADD CONSTRAINT `other_information_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `personal_information`
--
ALTER TABLE `personal_information`
  ADD CONSTRAINT `personal_information_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `related_questions`
--
ALTER TABLE `related_questions`
  ADD CONSTRAINT `related_questions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `uploaded_documents`
--
ALTER TABLE `uploaded_documents`
  ADD CONSTRAINT `uploaded_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `voluntary_works`
--
ALTER TABLE `voluntary_works`
  ADD CONSTRAINT `voluntary_works_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `work_experiences`
--
ALTER TABLE `work_experiences`
  ADD CONSTRAINT `work_experiences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `work_exp_sheet`
--
ALTER TABLE `work_exp_sheet`
  ADD CONSTRAINT `work_exp_sheet_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
