-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 22, 2025 at 07:28 PM
-- Server version: 8.0.31
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `new_nppk`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','inactive','suspended','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_token_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `first_name`, `last_name`, `surname`, `phone_number`, `email`, `email_verified_at`, `password`, `remember_token`, `is_super_admin`, `status`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `last_login_at`, `last_login_ip`, `password_reset_token`, `password_reset_token_expires_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'super', 'admin', NULL, NULL, 'superadmin@realsyscms.co.ke', '2025-06-18 15:45:45', '$2y$12$mZ0IN3aEv0siUQQaE6StBucfc3GqK9eyFyOhAta4dAAsUFGU2zaye', NULL, 1, 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-18 15:45:45', '2025-06-18 15:45:45', NULL),
(2, 'Kate', 'thuku', 'Mwangi', '0722111222', 'kmwangi@test.com', NULL, '$2y$12$kCE7ia83PrNg5tXs9WsifefRHfBm21J0QmghiD7PGnzvUrxtBP9oW', NULL, 0, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-20 15:19:26', '2025-06-20 15:19:26', NULL),
(3, 'James', NULL, 'Mwangi', '0722333444', 'jmwangi@test.com', NULL, '$2y$12$DuBvtdKbjYWRwGlbZ6om8e0FKHa.f.cP7IITy26hF9C6WScPcUNOe', NULL, 0, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-20 15:31:12', '2025-06-20 15:31:12', NULL),
(4, 'Brian', NULL, 'Mochama', '0723710025', 'brianmochama@gmail.com', NULL, '$2y$12$APWXdvWIkV45dx93fARHt.hIRu0oNoDeD7LhS6wUjTbds2Rshyrbi', NULL, 0, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-20 17:09:12', '2025-06-20 17:09:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_user_sessions`
--

DROP TABLE IF EXISTS `admin_user_sessions`;
CREATE TABLE IF NOT EXISTS `admin_user_sessions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_user_sessions_admin_id_foreign` (`admin_id`),
  KEY `admin_user_sessions_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_user_sessions`
--

INSERT INTO `admin_user_sessions` (`id`, `admin_id`, `user_id`, `started_at`, `ended_at`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 3, 3, '2025-06-20 15:37:58', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-20 15:37:58', '2025-06-20 15:37:58'),
(2, 3, 3, '2025-06-20 16:00:01', '2025-06-20 16:10:08', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-20 16:00:01', '2025-06-20 16:10:08');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('available_themes', 'a:2:{s:7:\"default\";a:12:{s:4:\"name\";s:15:\"RealSys Default\";s:10:\"identifier\";s:7:\"default\";s:11:\"description\";s:74:\"A clean, modern blog theme with beautiful typography and responsive design\";s:7:\"version\";s:5:\"1.0.0\";s:6:\"author\";s:4:\"NPPK\";s:7:\"website\";s:16:\"https://nppk.com\";s:6:\"active\";b:1;s:10:\"screenshot\";s:25:\"assets/img/screenshot.jpg\";s:13:\"content_types\";a:3:{i:0;a:4:{s:4:\"name\";s:7:\"Article\";s:10:\"identifier\";s:7:\"article\";s:11:\"description\";s:33:\"Article content for post listings\";s:6:\"fields\";a:7:{i:0;a:5:{s:4:\"name\";s:5:\"Title\";s:10:\"identifier\";s:5:\"title\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:1;s:10:\"validation\";s:16:\"required|max:255\";}i:1;a:5:{s:4:\"name\";s:8:\"Subtitle\";s:10:\"identifier\";s:8:\"subtitle\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:0;s:10:\"validation\";s:7:\"max:255\";}i:2;a:5:{s:4:\"name\";s:7:\"Content\";s:10:\"identifier\";s:7:\"content\";s:4:\"type\";s:7:\"wysiwyg\";s:8:\"required\";b:1;s:10:\"validation\";s:8:\"required\";}i:3;a:5:{s:4:\"name\";s:14:\"Featured Image\";s:10:\"identifier\";s:14:\"featured_image\";s:4:\"type\";s:5:\"image\";s:8:\"required\";b:1;s:10:\"validation\";s:14:\"required|image\";}i:4;a:5:{s:4:\"name\";s:11:\"Author Name\";s:10:\"identifier\";s:11:\"author_name\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:1;s:10:\"validation\";s:8:\"required\";}i:5;a:5:{s:4:\"name\";s:14:\"Published Date\";s:10:\"identifier\";s:14:\"published_date\";s:4:\"type\";s:4:\"date\";s:8:\"required\";b:1;s:10:\"validation\";s:13:\"required|date\";}i:6;a:5:{s:4:\"name\";s:3:\"URL\";s:10:\"identifier\";s:3:\"url\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:1;s:10:\"validation\";s:8:\"required\";}}}i:1;a:4:{s:4:\"name\";s:11:\"Page Header\";s:10:\"identifier\";s:11:\"page_header\";s:11:\"description\";s:29:\"Hero header content for pages\";s:6:\"fields\";a:3:{i:0;a:5:{s:4:\"name\";s:5:\"Title\";s:10:\"identifier\";s:5:\"title\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:1;s:10:\"validation\";s:16:\"required|max:255\";}i:1;a:5:{s:4:\"name\";s:8:\"Subtitle\";s:10:\"identifier\";s:8:\"subtitle\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:0;s:10:\"validation\";s:7:\"max:255\";}i:2;a:5:{s:4:\"name\";s:16:\"Background Image\";s:10:\"identifier\";s:10:\"background\";s:4:\"type\";s:5:\"image\";s:8:\"required\";b:1;s:10:\"validation\";s:14:\"required|image\";}}}i:2;a:4:{s:4:\"name\";s:16:\"Contact Settings\";s:10:\"identifier\";s:16:\"contact_settings\";s:11:\"description\";s:26:\"Settings for contact forms\";s:6:\"fields\";a:4:{i:0;a:5:{s:4:\"name\";s:10:\"Form Title\";s:10:\"identifier\";s:5:\"title\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:1;s:10:\"validation\";s:16:\"required|max:255\";}i:1;a:5:{s:4:\"name\";s:15:\"Email Recipient\";s:10:\"identifier\";s:9:\"recipient\";s:4:\"type\";s:5:\"email\";s:8:\"required\";b:1;s:10:\"validation\";s:14:\"required|email\";}i:2;a:5:{s:4:\"name\";s:15:\"Success Message\";s:10:\"identifier\";s:15:\"success_message\";s:4:\"type\";s:8:\"textarea\";s:8:\"required\";b:0;s:10:\"validation\";s:7:\"max:500\";}i:3;a:6:{s:4:\"name\";s:11:\"Button Text\";s:10:\"identifier\";s:11:\"button_text\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:0;s:10:\"validation\";s:6:\"max:50\";s:7:\"default\";s:4:\"Send\";}}}}s:9:\"templates\";a:4:{i:0;a:4:{s:4:\"name\";s:4:\"Home\";s:10:\"identifier\";s:4:\"home\";s:4:\"file\";s:24:\"templates/home.blade.php\";s:8:\"sections\";a:3:{i:0;a:4:{s:4:\"name\";s:11:\"Hero Header\";s:10:\"identifier\";s:4:\"hero\";s:11:\"description\";s:33:\"Page header with background image\";s:6:\"layout\";a:4:{s:5:\"width\";s:4:\"full\";s:6:\"height\";s:4:\"auto\";s:7:\"padding\";a:4:{s:3:\"top\";s:4:\"5rem\";s:6:\"bottom\";s:4:\"5rem\";s:4:\"left\";s:1:\"0\";s:5:\"right\";s:1:\"0\";}s:10:\"responsive\";a:1:{s:6:\"mobile\";a:2:{s:6:\"height\";s:4:\"auto\";s:7:\"padding\";a:2:{s:3:\"top\";s:4:\"3rem\";s:6:\"bottom\";s:4:\"3rem\";}}}}}i:1;a:4:{s:4:\"name\";s:13:\"Post Listings\";s:10:\"identifier\";s:5:\"posts\";s:11:\"description\";s:26:\"Blog post listings section\";s:6:\"layout\";a:4:{s:5:\"width\";s:9:\"container\";s:6:\"height\";s:4:\"auto\";s:7:\"padding\";a:4:{s:3:\"top\";s:4:\"3rem\";s:6:\"bottom\";s:4:\"3rem\";s:4:\"left\";s:4:\"1rem\";s:5:\"right\";s:4:\"1rem\";}s:10:\"responsive\";a:1:{s:6:\"mobile\";a:1:{s:7:\"padding\";a:4:{s:3:\"top\";s:4:\"2rem\";s:6:\"bottom\";s:4:\"2rem\";s:4:\"left\";s:6:\"0.5rem\";s:5:\"right\";s:6:\"0.5rem\";}}}}}i:2;a:3:{s:4:\"name\";s:6:\"Footer\";s:10:\"identifier\";s:6:\"footer\";s:11:\"description\";s:29:\"Page footer with social links\";}}}i:1;a:4:{s:4:\"name\";s:4:\"Post\";s:10:\"identifier\";s:4:\"post\";s:4:\"file\";s:24:\"templates/post.blade.php\";s:8:\"sections\";a:5:{i:0;a:3:{s:4:\"name\";s:11:\"Hero Header\";s:10:\"identifier\";s:4:\"hero\";s:11:\"description\";s:33:\"Post header with background image\";}i:1;a:3:{s:4:\"name\";s:12:\"Post Content\";s:10:\"identifier\";s:7:\"content\";s:11:\"description\";s:22:\"Main post content area\";}i:2;a:3:{s:4:\"name\";s:11:\"Author Info\";s:10:\"identifier\";s:6:\"author\";s:11:\"description\";s:26:\"Author information section\";}i:3;a:3:{s:4:\"name\";s:8:\"Comments\";s:10:\"identifier\";s:8:\"comments\";s:11:\"description\";s:16:\"Comments section\";}i:4;a:3:{s:4:\"name\";s:6:\"Footer\";s:10:\"identifier\";s:6:\"footer\";s:11:\"description\";s:29:\"Page footer with social links\";}}}i:2;a:4:{s:4:\"name\";s:5:\"About\";s:10:\"identifier\";s:5:\"about\";s:4:\"file\";s:25:\"templates/about.blade.php\";s:8:\"sections\";a:4:{i:0;a:4:{s:4:\"name\";s:11:\"Hero Header\";s:10:\"identifier\";s:4:\"hero\";s:11:\"description\";s:33:\"Page header with background image\";s:6:\"layout\";a:4:{s:5:\"width\";s:4:\"full\";s:6:\"height\";s:4:\"auto\";s:7:\"padding\";a:4:{s:3:\"top\";s:4:\"5rem\";s:6:\"bottom\";s:4:\"5rem\";s:4:\"left\";s:1:\"0\";s:5:\"right\";s:1:\"0\";}s:10:\"responsive\";a:1:{s:6:\"mobile\";a:2:{s:6:\"height\";s:4:\"auto\";s:7:\"padding\";a:2:{s:3:\"top\";s:4:\"3rem\";s:6:\"bottom\";s:4:\"3rem\";}}}}}i:1;a:3:{s:4:\"name\";s:12:\"Main Content\";s:10:\"identifier\";s:7:\"content\";s:11:\"description\";s:17:\"Main content area\";}i:2;a:3:{s:4:\"name\";s:4:\"Team\";s:10:\"identifier\";s:4:\"team\";s:11:\"description\";s:20:\"Team members section\";}i:3;a:3:{s:4:\"name\";s:6:\"Footer\";s:10:\"identifier\";s:6:\"footer\";s:11:\"description\";s:29:\"Page footer with social links\";}}}i:3;a:4:{s:4:\"name\";s:7:\"Contact\";s:10:\"identifier\";s:7:\"contact\";s:4:\"file\";s:27:\"templates/contact.blade.php\";s:8:\"sections\";a:4:{i:0;a:4:{s:4:\"name\";s:11:\"Hero Header\";s:10:\"identifier\";s:4:\"hero\";s:11:\"description\";s:33:\"Page header with background image\";s:6:\"layout\";a:4:{s:5:\"width\";s:4:\"full\";s:6:\"height\";s:4:\"auto\";s:7:\"padding\";a:4:{s:3:\"top\";s:4:\"5rem\";s:6:\"bottom\";s:4:\"5rem\";s:4:\"left\";s:1:\"0\";s:5:\"right\";s:1:\"0\";}s:10:\"responsive\";a:1:{s:6:\"mobile\";a:2:{s:6:\"height\";s:4:\"auto\";s:7:\"padding\";a:2:{s:3:\"top\";s:4:\"3rem\";s:6:\"bottom\";s:4:\"3rem\";}}}}}i:1;a:3:{s:4:\"name\";s:12:\"Contact Form\";s:10:\"identifier\";s:4:\"form\";s:11:\"description\";s:20:\"Contact form section\";}i:2;a:3:{s:4:\"name\";s:3:\"Map\";s:10:\"identifier\";s:3:\"map\";s:11:\"description\";s:19:\"Google Maps section\";}i:3;a:3:{s:4:\"name\";s:6:\"Footer\";s:10:\"identifier\";s:6:\"footer\";s:11:\"description\";s:29:\"Page footer with social links\";}}}}s:7:\"widgets\";a:3:{i:0;a:6:{s:4:\"name\";s:11:\"Hero Header\";s:10:\"identifier\";s:11:\"hero-header\";s:11:\"description\";s:45:\"Header with background image and text overlay\";s:13:\"content_types\";a:1:{i:0;s:11:\"page_header\";}s:16:\"query_parameters\";a:4:{s:12:\"content_type\";s:11:\"page_header\";s:5:\"limit\";i:1;s:8:\"order_by\";s:10:\"created_at\";s:9:\"direction\";s:4:\"desc\";}s:6:\"fields\";a:3:{i:0;a:4:{s:4:\"name\";s:5:\"Title\";s:10:\"identifier\";s:5:\"title\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:1;}i:1;a:4:{s:4:\"name\";s:8:\"Subtitle\";s:10:\"identifier\";s:8:\"subtitle\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:0;}i:2;a:4:{s:4:\"name\";s:16:\"Background Image\";s:10:\"identifier\";s:10:\"background\";s:4:\"type\";s:5:\"image\";s:8:\"required\";b:1;}}}i:1;a:6:{s:4:\"name\";s:9:\"Post List\";s:10:\"identifier\";s:9:\"post-list\";s:11:\"description\";s:31:\"List of blog posts with preview\";s:13:\"content_types\";a:1:{i:0;s:7:\"article\";}s:16:\"query_parameters\";a:5:{s:12:\"content_type\";s:7:\"article\";s:5:\"limit\";i:10;s:8:\"order_by\";s:14:\"published_date\";s:9:\"direction\";s:4:\"desc\";s:7:\"filters\";a:1:{s:6:\"status\";s:9:\"published\";}}s:6:\"fields\";a:1:{i:0;a:5:{s:4:\"name\";s:14:\"Posts Per Page\";s:10:\"identifier\";s:8:\"per_page\";s:4:\"type\";s:6:\"number\";s:8:\"required\";b:1;s:7:\"default\";i:5;}}}i:2;a:6:{s:4:\"name\";s:12:\"Contact Form\";s:10:\"identifier\";s:12:\"contact-form\";s:11:\"description\";s:37:\"Contact form with email functionality\";s:13:\"content_types\";a:1:{i:0;s:16:\"contact_settings\";}s:16:\"query_parameters\";a:4:{s:12:\"content_type\";s:16:\"contact_settings\";s:5:\"limit\";i:1;s:8:\"order_by\";s:10:\"created_at\";s:9:\"direction\";s:4:\"desc\";}s:6:\"fields\";a:2:{i:0;a:4:{s:4:\"name\";s:10:\"Form Title\";s:10:\"identifier\";s:5:\"title\";s:4:\"type\";s:4:\"text\";s:8:\"required\";b:1;}i:1;a:4:{s:4:\"name\";s:15:\"Email Recipient\";s:10:\"identifier\";s:9:\"recipient\";s:4:\"type\";s:5:\"email\";s:8:\"required\";b:1;}}}}s:6:\"assets\";a:3:{s:3:\"css\";a:3:{i:0;s:72:\"https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic\";i:1;s:119:\"https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800\";i:2;s:21:\"assets/css/styles.css\";}s:2:\"js\";a:2:{i:0;s:53:\"https://use.fontawesome.com/releases/v6.3.0/js/all.js\";i:1;s:20:\"assets/js/scripts.js\";}s:6:\"images\";a:4:{i:0;s:22:\"assets/img/home-bg.jpg\";i:1;s:23:\"assets/img/about-bg.jpg\";i:2;s:22:\"assets/img/post-bg.jpg\";i:3;s:25:\"assets/img/contact-bg.jpg\";}}}s:5:\"miata\";a:9:{s:4:\"name\";s:5:\"Miata\";s:10:\"identifier\";s:5:\"miata\";s:11:\"description\";s:38:\"A political Party theme for RealsysCMS\";s:7:\"version\";s:5:\"1.0.0\";s:6:\"author\";s:37:\"HTMLDemo.net (Adapted for RealsysCMS)\";s:7:\"website\";s:16:\"https://nppk.com\";s:6:\"active\";b:0;s:10:\"screenshot\";s:24:\"assets/img/thumbnail.jpg\";s:8:\"settings\";a:3:{s:13:\"primary_color\";s:7:\"#ff5c5c\";s:15:\"secondary_color\";s:7:\"#1e1e85\";s:11:\"font_family\";s:19:\"Poppins, sans-serif\";}}}', 1753286736),
('menus.all.page1.template1', 'O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:2:{s:6:\"header\";O:15:\"App\\Models\\Menu\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"menus\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:9:{s:2:\"id\";i:1;s:4:\"name\";s:17:\"Header Navigation\";s:4:\"slug\";s:10:\"header-nav\";s:8:\"location\";s:6:\"header\";s:11:\"description\";s:39:\"Main navigation displayed in the header\";s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";s:9:\"rootItems\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:5:{i:0;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:16:{s:2:\"id\";i:1;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:4:\"Home\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:1:\"/\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:1;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:4:\"Home\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:1:\"/\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:16:{s:2:\"id\";i:2;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:11:\"About Party\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:2;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:11:\"About Party\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:2;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:16:{s:2:\"id\";i:3;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:10:\"Leadership\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:2;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:52:23\";s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:15:{s:2:\"id\";i:4;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:3;s:5:\"label\";s:17:\"Executive Members\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:58:11\";s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:4;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:3;s:5:\"label\";s:17:\"Executive Members\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:58:11\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:15:{s:2:\"id\";i:5;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:3;s:5:\"label\";s:11:\"Secretariat\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:58:57\";s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:5;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:3;s:5:\"label\";s:11:\"Secretariat\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:58:57\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:3;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:10:\"Leadership\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:2;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:52:23\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;r:219;i:1;r:294;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:3;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:16:{s:2:\"id\";i:10;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:4:\"News\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:5:\"/news\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";s:16:\"features-section\";s:21:\"visibility_conditions\";N;s:8:\"position\";i:3;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:59:47\";s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:10;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:4:\"News\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:5:\"/news\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";s:16:\"features-section\";s:21:\"visibility_conditions\";N;s:8:\"position\";i:3;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:59:47\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:4;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:16:{s:2:\"id\";i:6;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:7:\"Contact\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/contact\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:4;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:6;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:7:\"Contact\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/contact\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:4;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:11:\"\0*\0original\";a:8:{s:2:\"id\";i:1;s:4:\"name\";s:17:\"Header Navigation\";s:4:\"slug\";s:10:\"header-nav\";s:8:\"location\";s:6:\"header\";s:11:\"description\";s:39:\"Main navigation displayed in the header\";s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:9:\"rootItems\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:5:{i:0;r:27;i:1;r:108;i:2;r:189;i:3;r:422;i:4;r:503;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:4:\"name\";i:1;s:4:\"slug\";i:2;s:8:\"location\";i:3;s:11:\"description\";i:4;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}s:6:\"footer\";O:15:\"App\\Models\\Menu\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"menus\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:9:{s:2:\"id\";i:2;s:4:\"name\";s:17:\"Footer Navigation\";s:4:\"slug\";s:10:\"footer-nav\";s:8:\"location\";s:6:\"footer\";s:11:\"description\";s:44:\"Secondary navigation displayed in the footer\";s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";s:9:\"rootItems\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:5:{i:0;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:16:{s:2:\"id\";i:7;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:14:\"Privacy Policy\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/privacy\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:7;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:14:\"Privacy Policy\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/privacy\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:16:{s:2:\"id\";i:8;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:16:\"Terms of Service\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/terms\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:2;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:8;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:16:\"Terms of Service\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/terms\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:2;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:2;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:16:{s:2:\"id\";i:9;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:7:\"Contact\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/contact\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:3;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:44:50\";s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:9;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:7:\"Contact\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/contact\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:3;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:44:50\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:3;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:16:{s:2:\"id\";i:11;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:12:\"Members Area\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/login\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:4;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-12 19:40:05\";s:10:\"updated_at\";s:19:\"2025-06-12 19:40:05\";s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:11;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:12:\"Members Area\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/login\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:4;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-12 19:40:05\";s:10:\"updated_at\";s:19:\"2025-06-12 19:40:05\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:4;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:16:{s:2:\"id\";i:12;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:10:\"Admin Area\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:11:\"admin/login\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:5;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-12 20:00:50\";s:10:\"updated_at\";s:19:\"2025-06-13 11:03:25\";s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"is_current\";b:0;}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:12;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:10:\"Admin Area\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:11:\"admin/login\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:5;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-12 20:00:50\";s:10:\"updated_at\";s:19:\"2025-06-13 11:03:25\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:11:\"\0*\0original\";a:8:{s:2:\"id\";i:2;s:4:\"name\";s:17:\"Footer Navigation\";s:4:\"slug\";s:10:\"footer-nav\";s:8:\"location\";s:6:\"footer\";s:11:\"description\";s:44:\"Secondary navigation displayed in the footer\";s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:9:\"rootItems\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:5:{i:0;r:649;i:1;r:730;i:2;r:811;i:3;r:892;i:4;r:973;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:4:\"name\";i:1;s:4:\"slug\";i:2;s:8:\"location\";i:3;s:11:\"description\";i:4;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1753214324);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('menu.location.header', 'O:15:\"App\\Models\\Menu\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"menus\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:2:\"id\";i:1;s:4:\"name\";s:17:\"Header Navigation\";s:4:\"slug\";s:10:\"header-nav\";s:8:\"location\";s:6:\"header\";s:11:\"description\";s:39:\"Main navigation displayed in the header\";s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:11:\"\0*\0original\";a:8:{s:2:\"id\";i:1;s:4:\"name\";s:17:\"Header Navigation\";s:4:\"slug\";s:10:\"header-nav\";s:8:\"location\";s:6:\"header\";s:11:\"description\";s:39:\"Main navigation displayed in the header\";s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:9:\"rootItems\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:5:{i:0;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:1;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:4:\"Home\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:1:\"/\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:1;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:4:\"Home\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:1:\"/\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:2;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:11:\"About Party\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:2;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:11:\"About Party\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:2;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:3;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:10:\"Leadership\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:2;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:52:23\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:3;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:10:\"Leadership\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:2;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:52:23\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:4;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:3;s:5:\"label\";s:17:\"Executive Members\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:58:11\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:4;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:3;s:5:\"label\";s:17:\"Executive Members\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:58:11\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:5;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:3;s:5:\"label\";s:11:\"Secretariat\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:58:57\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:5;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:3;s:5:\"label\";s:11:\"Secretariat\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/about\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:58:57\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:3;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:10;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:4:\"News\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:5:\"/news\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";s:16:\"features-section\";s:21:\"visibility_conditions\";N;s:8:\"position\";i:3;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:59:47\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:10;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:4:\"News\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:5:\"/news\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";s:16:\"features-section\";s:21:\"visibility_conditions\";N;s:8:\"position\";i:3;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:59:47\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:4;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:6;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:7:\"Contact\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/contact\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:4;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:6;s:7:\"menu_id\";i:1;s:9:\"parent_id\";N;s:5:\"label\";s:7:\"Contact\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/contact\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:4;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-06 23:23:35\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:4:\"name\";i:1;s:4:\"slug\";i:2;s:8:\"location\";i:3;s:11:\"description\";i:4;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}', 1753214520),
('menu.location.footer', 'O:15:\"App\\Models\\Menu\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"menus\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:2:\"id\";i:2;s:4:\"name\";s:17:\"Footer Navigation\";s:4:\"slug\";s:10:\"footer-nav\";s:8:\"location\";s:6:\"footer\";s:11:\"description\";s:44:\"Secondary navigation displayed in the footer\";s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:11:\"\0*\0original\";a:8:{s:2:\"id\";i:2;s:4:\"name\";s:17:\"Footer Navigation\";s:4:\"slug\";s:10:\"footer-nav\";s:8:\"location\";s:6:\"footer\";s:11:\"description\";s:44:\"Secondary navigation displayed in the footer\";s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:9:\"rootItems\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:5:{i:0;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:7;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:14:\"Privacy Policy\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/privacy\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:7;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:14:\"Privacy Policy\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/privacy\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:8;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:16:\"Terms of Service\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/terms\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:2;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:8;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:16:\"Terms of Service\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/terms\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:2;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-05 00:04:41\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:2;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:9;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:7:\"Contact\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/contact\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:3;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:44:50\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:9;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:7:\"Contact\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:8:\"/contact\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:3;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-05 00:04:41\";s:10:\"updated_at\";s:19:\"2025-06-12 19:44:50\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:3;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:11;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:12:\"Members Area\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/login\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:4;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-12 19:40:05\";s:10:\"updated_at\";s:19:\"2025-06-12 19:40:05\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:11;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:12:\"Members Area\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:6:\"/login\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:4;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-12 19:40:05\";s:10:\"updated_at\";s:19:\"2025-06-12 19:40:05\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:4;O:19:\"App\\Models\\MenuItem\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"menu_items\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:12;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:10:\"Admin Area\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:11:\"admin/login\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:5;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-12 20:00:50\";s:10:\"updated_at\";s:19:\"2025-06-13 11:03:25\";}s:11:\"\0*\0original\";a:14:{s:2:\"id\";i:12;s:7:\"menu_id\";i:2;s:9:\"parent_id\";N;s:5:\"label\";s:10:\"Admin Area\";s:9:\"link_type\";s:3:\"url\";s:3:\"url\";s:11:\"admin/login\";s:6:\"target\";s:5:\"_self\";s:7:\"page_id\";N;s:10:\"section_id\";N;s:21:\"visibility_conditions\";N;s:8:\"position\";i:5;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2025-06-12 20:00:50\";s:10:\"updated_at\";s:19:\"2025-06-13 11:03:25\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:21:\"visibility_conditions\";s:5:\"array\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:1:{i:0;s:8:\"full_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:8:\"children\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:7:\"menu_id\";i:1;s:9:\"parent_id\";i:2;s:5:\"label\";i:3;s:9:\"link_type\";i:4;s:3:\"url\";i:5;s:6:\"target\";i:6;s:7:\"page_id\";i:7;s:10:\"section_id\";i:8;s:21:\"visibility_conditions\";i:9;s:8:\"position\";i:10;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:4:\"name\";i:1;s:4:\"slug\";i:2;s:8:\"location\";i:3;s:11:\"description\";i:4;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}', 1753214521);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `constituencies`
--

DROP TABLE IF EXISTS `constituencies`;
CREATE TABLE IF NOT EXISTS `constituencies` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `county_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `constituencies_name_county_id_unique` (`name`,`county_id`),
  UNIQUE KEY `constituencies_code_unique` (`code`),
  KEY `constituencies_county_id_foreign` (`county_id`)
) ENGINE=MyISAM AUTO_INCREMENT=290 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `constituencies`
--

INSERT INTO `constituencies` (`id`, `name`, `code`, `county_id`, `created_at`, `updated_at`) VALUES
(1, 'Changamwe', '0101', 1, '2025-06-11 19:50:52', NULL),
(2, 'Jomvu', '0102', 1, '2025-06-11 19:50:52', NULL),
(3, 'Kisauni', '0103', 1, '2025-06-11 19:50:52', NULL),
(4, 'Likoni', '0104', 1, '2025-06-11 19:50:52', NULL),
(5, 'Mvita', '0105', 1, '2025-06-11 19:50:52', NULL),
(6, 'Nyali', '0106', 1, '2025-06-11 19:50:52', NULL),
(7, 'Kinango', '0201', 2, '2025-06-11 19:50:52', NULL),
(8, 'Lunga Lunga', '0202', 2, '2025-06-11 19:50:52', NULL),
(9, 'Msambweni', '0203', 2, '2025-06-11 19:50:52', NULL),
(10, 'Matuga', '0204', 2, '2025-06-11 19:50:52', NULL),
(11, 'Kilifi North', '0301', 3, '2025-06-11 19:50:52', NULL),
(12, 'Kilifi South', '0302', 3, '2025-06-11 19:50:52', NULL),
(13, 'Kaloleni', '0303', 3, '2025-06-11 19:50:52', NULL),
(14, 'Ganze', '0304', 3, '2025-06-11 19:50:52', NULL),
(15, 'Magarini', '0305', 3, '2025-06-11 19:50:52', NULL),
(16, 'Rabai', '0306', 3, '2025-06-11 19:50:52', NULL),
(17, 'Malindi', '0307', 3, '2025-06-11 19:50:52', NULL),
(18, 'Garsen', '0401', 4, '2025-06-11 19:50:52', NULL),
(19, 'Galole', '0402', 4, '2025-06-11 19:50:52', NULL),
(20, 'Bura', '0403', 4, '2025-06-11 19:50:52', NULL),
(21, 'Lamu East', '0501', 5, '2025-06-11 19:50:52', NULL),
(22, 'Lamu West', '0502', 5, '2025-06-11 19:50:52', NULL),
(23, 'Taveta', '0601', 6, '2025-06-11 19:50:52', NULL),
(24, 'Wundanyi', '0602', 6, '2025-06-11 19:50:52', NULL),
(25, 'Mwatate', '0603', 6, '2025-06-11 19:50:52', NULL),
(26, 'Voi', '0604', 6, '2025-06-11 19:50:52', NULL),
(27, 'Dujis', '0701', 7, '2025-06-11 19:50:52', NULL),
(28, 'Balambala', '0702', 7, '2025-06-11 19:50:52', NULL),
(29, 'Dadaab', '0703', 7, '2025-06-11 19:50:52', NULL),
(30, 'Fafi', '0704', 7, '2025-06-11 19:50:52', NULL),
(31, 'Ijara', '0705', 7, '2025-06-11 19:50:52', NULL),
(32, 'Lagdera', '0706', 7, '2025-06-11 19:50:52', NULL),
(33, 'Wajir East', '0801', 8, '2025-06-11 19:50:52', NULL),
(34, 'Wajir North', '0802', 8, '2025-06-11 19:50:52', NULL),
(35, 'Wajir South', '0803', 8, '2025-06-11 19:50:52', NULL),
(36, 'Wajir West', '0804', 8, '2025-06-11 19:50:52', NULL),
(37, 'Tarbaj', '0805', 8, '2025-06-11 19:50:52', NULL),
(38, 'Eldas', '0806', 8, '2025-06-11 19:50:52', NULL),
(39, 'Mandera West', '0901', 9, '2025-06-11 19:50:52', NULL),
(40, 'Banissa', '0902', 9, '2025-06-11 19:50:52', NULL),
(41, 'Mandera North', '0903', 9, '2025-06-11 19:50:52', NULL),
(42, 'Mandera South', '0904', 9, '2025-06-11 19:50:52', NULL),
(43, 'Mandera East', '0905', 9, '2025-06-11 19:50:52', NULL),
(44, 'Lafey', '0906', 9, '2025-06-11 19:50:52', NULL),
(45, 'Laisamis', '1001', 10, '2025-06-11 19:50:52', NULL),
(46, 'North Horr', '1002', 10, '2025-06-11 19:50:52', NULL),
(47, 'Saku', '1003', 10, '2025-06-11 19:50:52', NULL),
(48, 'Moyale', '1004', 10, '2025-06-11 19:50:52', NULL),
(49, 'Isiolo North', '1101', 11, '2025-06-11 19:50:52', NULL),
(50, 'Isiolo South', '1102', 11, '2025-06-11 19:50:52', NULL),
(51, 'Buuri', '1201', 12, '2025-06-11 19:50:52', NULL),
(52, 'Central Imenti', '1202', 12, '2025-06-11 19:50:52', NULL),
(53, 'Igembe Central', '1203', 12, '2025-06-11 19:50:52', NULL),
(54, 'Igembe South', '1204', 12, '2025-06-11 19:50:52', NULL),
(55, 'Igembe North', '1205', 12, '2025-06-11 19:50:52', NULL),
(56, 'Tigania West', '1206', 12, '2025-06-11 19:50:52', NULL),
(57, 'Tigania East', '1207', 12, '2025-06-11 19:50:52', NULL),
(58, 'Imenti North', '1208', 12, '2025-06-11 19:50:52', NULL),
(59, 'Imenti South', '1209', 12, '2025-06-11 19:50:52', NULL),
(60, 'Tharaka ', '1301', 13, '2025-06-11 19:50:52', NULL),
(61, 'Chuka/Igambang’ombe', '1302', 13, '2025-06-11 19:50:52', NULL),
(62, 'Maara', '1303', 13, '2025-06-11 19:50:52', NULL),
(63, 'Manyatta', '1401', 14, '2025-06-11 19:50:52', NULL),
(64, 'Runyenjes', '1402', 14, '2025-06-11 19:50:52', NULL),
(65, 'Mbeere North', '1403', 14, '2025-06-11 19:50:52', NULL),
(66, 'Mbeere South', '1404', 14, '2025-06-11 19:50:52', NULL),
(67, 'Kitui West', '1501', 15, '2025-06-11 19:50:52', NULL),
(68, 'Kitui Central', '1502', 15, '2025-06-11 19:50:52', NULL),
(69, 'Kitui Rural', '1503', 15, '2025-06-11 19:50:52', NULL),
(70, 'Kitui South', '1504', 15, '2025-06-11 19:50:52', NULL),
(71, 'Kitui East', '1505', 15, '2025-06-11 19:50:52', NULL),
(72, 'Mwingi North', '1506', 15, '2025-06-11 19:50:52', NULL),
(73, 'Mwingi West', '1507', 15, '2025-06-11 19:50:52', NULL),
(74, 'Mwingi Central', '1508', 15, '2025-06-11 19:50:52', NULL),
(75, 'Masinga', '1601', 16, '2025-06-11 19:50:52', NULL),
(76, 'Yatta', '1602', 16, '2025-06-11 19:50:52', NULL),
(77, 'Matungulu', '1603', 16, '2025-06-11 19:50:52', NULL),
(78, 'Kangundo', '1604', 16, '2025-06-11 19:50:52', NULL),
(79, 'Mwala', '1605', 16, '2025-06-11 19:50:52', NULL),
(80, 'Kathiani', '1606', 16, '2025-06-11 19:50:52', NULL),
(81, 'Machakos Town', '1607', 16, '2025-06-11 19:50:52', NULL),
(82, 'Mavoko', '1608', 16, '2025-06-11 19:50:52', NULL),
(83, 'Mbooni', '1701', 17, '2025-06-11 19:50:52', NULL),
(84, 'Kaiti', '1702', 17, '2025-06-11 19:50:52', NULL),
(85, 'Makueni', '1703', 17, '2025-06-11 19:50:52', NULL),
(86, 'Kilome', '1704', 17, '2025-06-11 19:50:52', NULL),
(87, 'Kibwezi East', '1705', 17, '2025-06-11 19:50:52', NULL),
(88, 'Kibwezi West', '1706', 17, '2025-06-11 19:50:52', NULL),
(89, 'Kinangop', '1801', 18, '2025-06-11 19:50:52', NULL),
(90, 'Kipipiri', '1802', 18, '2025-06-11 19:50:52', NULL),
(91, 'Ol Joro Orok', '1803', 18, '2025-06-11 19:50:52', NULL),
(92, 'Ndaragwa', '1804', 18, '2025-06-11 19:50:52', NULL),
(93, 'Ol Kalou', '1805', 18, '2025-06-11 19:50:52', NULL),
(94, 'Mathira', '1901', 19, '2025-06-11 19:50:52', NULL),
(95, 'Othaya', '1902', 19, '2025-06-11 19:50:52', NULL),
(96, 'Tetu', '1905', 19, '2025-06-11 19:50:52', NULL),
(97, 'Mukurweini', '1904', 19, '2025-06-11 19:50:52', NULL),
(98, 'Nyeri Town', '1906', 19, '2025-06-11 19:50:52', NULL),
(99, 'Kieni', '1907', 19, '2025-06-11 19:50:52', NULL),
(100, 'Kirinyaga Central', '2001', 20, '2025-06-11 19:50:52', NULL),
(101, 'Mwea', '2002', 20, '2025-06-11 19:50:52', NULL),
(102, 'Gichugu', '2003', 20, '2025-06-11 19:50:52', NULL),
(103, 'Ndia', '2004', 20, '2025-06-11 19:50:52', NULL),
(104, 'Gatanga', '2101', 21, '2025-06-11 19:50:52', NULL),
(105, 'Kandara', '2102', 21, '2025-06-11 19:50:52', NULL),
(106, 'Kigumo', '2103', 21, '2025-06-11 19:50:52', NULL),
(107, 'Mathioya', '2104', 21, '2025-06-11 19:50:52', NULL),
(108, 'Kiharu', '2105', 21, '2025-06-11 19:50:52', NULL),
(109, 'Kangema', '2106', 21, '2025-06-11 19:50:52', NULL),
(110, 'Maragwa', '2107', 21, '2025-06-11 19:50:52', NULL),
(111, 'Gatundu North', '2201', 22, '2025-06-11 19:50:52', NULL),
(112, 'Gatundu South', '2202', 22, '2025-06-11 19:50:52', NULL),
(113, 'Githunguri', '2203', 22, '2025-06-11 19:50:52', NULL),
(114, 'Juja', '2204', 22, '2025-06-11 19:50:52', NULL),
(115, 'Kabete', '2205', 22, '2025-06-11 19:50:52', NULL),
(116, 'Kiambaa', '2206', 22, '2025-06-11 19:50:52', NULL),
(117, 'Kiambu', '2207', 22, '2025-06-11 19:50:52', NULL),
(118, 'Limuru', '2208', 22, '2025-06-11 19:50:52', NULL),
(119, 'Kikuyu', '2209', 22, '2025-06-11 19:50:52', NULL),
(120, 'Lari', '2210', 22, '2025-06-11 19:50:52', NULL),
(121, 'Ruiru', '2211', 22, '2025-06-11 19:50:52', NULL),
(122, 'Thika Town', '2212', 22, '2025-06-11 19:50:52', NULL),
(123, 'Turkana Central', '2301', 23, '2025-06-11 19:50:52', NULL),
(124, 'Turkana East', '2302', 23, '2025-06-11 19:50:52', NULL),
(125, 'Turkana North', '2303', 23, '2025-06-11 19:50:52', NULL),
(126, 'Turkana South', '2304', 23, '2025-06-11 19:50:52', NULL),
(127, 'Turkana West', '2305', 23, '2025-06-11 19:50:52', NULL),
(128, 'Loima', '2306', 23, '2025-06-11 19:50:52', NULL),
(129, 'Kapenguria', '2401', 24, '2025-06-11 19:50:52', NULL),
(130, 'Sigor', '2402', 24, '2025-06-11 19:50:52', NULL),
(131, 'Kacheliba', '2403', 24, '2025-06-11 19:50:52', NULL),
(132, 'Pokot South', '2404', 24, '2025-06-11 19:50:52', NULL),
(133, 'Samburu East', '2501', 25, '2025-06-11 19:50:52', NULL),
(134, 'Samburu North', '2502', 25, '2025-06-11 19:50:52', NULL),
(135, 'Samburu West', '2503', 25, '2025-06-11 19:50:52', NULL),
(136, 'Cherang\'any', '2601', 26, '2025-06-11 19:50:52', NULL),
(137, 'Kwanza', '2602', 26, '2025-06-11 19:50:52', NULL),
(138, 'Endebess', '2603', 26, '2025-06-11 19:50:52', NULL),
(139, 'Saboti', '2604', 26, '2025-06-11 19:50:52', NULL),
(140, 'Kiminini', '2605', 26, '2025-06-11 19:50:52', NULL),
(141, 'Ainabkoi', '2701', 27, '2025-06-11 19:50:52', NULL),
(142, 'Kapseret', '2702', 27, '2025-06-11 19:50:52', NULL),
(143, 'Kesses', '2703', 27, '2025-06-11 19:50:52', NULL),
(144, 'Moiben', '2704', 27, '2025-06-11 19:50:52', NULL),
(145, 'Soy', '2705', 27, '2025-06-11 19:50:52', NULL),
(146, 'Turbo', '2706', 27, '2025-06-11 19:50:52', NULL),
(147, 'Keiyo North', '2801', 28, '2025-06-11 19:50:52', NULL),
(148, 'Keiyo South', '2802', 28, '2025-06-11 19:50:52', NULL),
(149, 'Marakwet East', '2803', 28, '2025-06-11 19:50:52', NULL),
(150, 'Marakwet West', '2804', 28, '2025-06-11 19:50:52', NULL),
(151, 'Aldai', '2901', 29, '2025-06-11 19:50:52', NULL),
(152, 'Chesumei', '2902', 29, '2025-06-11 19:50:52', NULL),
(153, 'Emgwen', '2903', 29, '2025-06-11 19:50:52', NULL),
(154, 'Mosop', '2904', 29, '2025-06-11 19:50:52', NULL),
(155, 'Nandi Hills', '2905', 29, '2025-06-11 19:50:52', NULL),
(156, 'Tinderet', '2906', 29, '2025-06-11 19:50:52', NULL),
(157, 'Baringo Central', '3001', 30, '2025-06-11 19:50:52', NULL),
(158, 'Baringo North', '3002', 30, '2025-06-11 19:50:52', NULL),
(159, 'Baringo South', '3003', 30, '2025-06-11 19:50:52', NULL),
(160, 'Eldama Ravine', '3004', 30, '2025-06-11 19:50:52', NULL),
(161, 'Mogotio', '3005', 30, '2025-06-11 19:50:52', NULL),
(162, 'Tiaty', '3006', 30, '2025-06-11 19:50:52', NULL),
(163, 'Laikipia North', '3101', 31, '2025-06-11 19:50:52', NULL),
(164, 'Laikipia East', '3102', 31, '2025-06-11 19:50:52', NULL),
(165, 'Laikipia West', '3103', 31, '2025-06-11 19:50:52', NULL),
(166, 'Nakuru Town East', '3201', 32, '2025-06-11 19:50:52', NULL),
(167, 'Nakuru Town West', '3202', 32, '2025-06-11 19:50:52', NULL),
(168, 'Njoro', '3203', 32, '2025-06-11 19:50:52', NULL),
(169, 'Molo', '3204', 32, '2025-06-11 19:50:52', NULL),
(170, 'Gilgil', '3205', 32, '2025-06-11 19:50:52', NULL),
(171, 'Naivasha', '3206', 32, '2025-06-11 19:50:52', NULL),
(172, 'Kuresoi North', '3207', 32, '2025-06-11 19:50:52', NULL),
(173, 'Kuresoi South', '3208', 32, '2025-06-11 19:50:52', NULL),
(174, 'Bahati', '3209', 32, '2025-06-11 19:50:52', NULL),
(175, 'Rongai', '3210', 32, '2025-06-11 19:50:52', NULL),
(176, 'Subukia', '3211', 32, '2025-06-11 19:50:52', NULL),
(177, 'Narok North', '3301', 33, '2025-06-11 19:50:52', NULL),
(178, 'Narok South', '3302', 33, '2025-06-11 19:50:52', NULL),
(179, 'Narok East', '3303', 33, '2025-06-11 19:50:52', NULL),
(180, 'Narok West', '3304', 33, '2025-06-11 19:50:52', NULL),
(181, 'Kilgoris', '3305', 33, '2025-06-11 19:50:52', NULL),
(182, 'Emurua Dikirr', '3306', 33, '2025-06-11 19:50:52', NULL),
(183, 'Kajiado Central', '3401', 34, '2025-06-11 19:50:52', NULL),
(184, 'Kajiado East', '3402', 34, '2025-06-11 19:50:52', NULL),
(185, 'Kajiado North', '3403', 34, '2025-06-11 19:50:52', NULL),
(186, 'Kajiado West', '3404', 34, '2025-06-11 19:50:52', NULL),
(187, 'Kajiado South', '3405', 34, '2025-06-11 19:50:52', NULL),
(188, 'Ainamoi', '3501', 35, '2025-06-11 19:50:52', NULL),
(189, 'Belgut', '3502', 35, '2025-06-11 19:50:52', NULL),
(190, 'Bureti', '3503', 35, '2025-06-11 19:50:52', NULL),
(191, 'Kipkelion East', '3504', 35, '2025-06-11 19:50:52', NULL),
(192, 'Kipkelion West', '3505', 35, '2025-06-11 19:50:52', NULL),
(193, 'Soin Sigowet', '3506', 35, '2025-06-11 19:50:52', NULL),
(194, 'Sotik', '3601', 36, '2025-06-11 19:50:52', NULL),
(195, 'Bomet Central', '3602', 36, '2025-06-11 19:50:52', NULL),
(196, 'Bomet East', '3603', 36, '2025-06-11 19:50:52', NULL),
(197, 'Chepalungu', '3604', 36, '2025-06-11 19:50:52', NULL),
(198, 'Konoin', '3605', 36, '2025-06-11 19:50:52', NULL),
(199, 'Butere', '3701', 37, '2025-06-11 19:50:52', NULL),
(200, 'Ikolomani', '3702', 37, '2025-06-11 19:50:52', NULL),
(201, 'Khwisero', '3703', 37, '2025-06-11 19:50:52', NULL),
(202, 'Lurambi', '3704', 37, '2025-06-11 19:50:52', NULL),
(203, 'Likuyani', '3705', 37, '2025-06-11 19:50:52', NULL),
(204, 'Malava', '3706', 37, '2025-06-11 19:50:52', NULL),
(205, 'Matungu', '3707', 37, '2025-06-11 19:50:52', NULL),
(206, 'Mumias East', '3708', 37, '2025-06-11 19:50:52', NULL),
(207, 'Mumias West', '3709', 37, '2025-06-11 19:50:52', NULL),
(208, 'Navakholo', '3710', 37, '2025-06-11 19:50:52', NULL),
(209, 'Lugari', '3711', 37, '2025-06-11 19:50:52', NULL),
(210, 'Shinyalu', '3712', 37, '2025-06-11 19:50:52', NULL),
(211, 'Emuhaya', '3801', 38, '2025-06-11 19:50:52', NULL),
(212, 'Hamisi', '3802', 38, '2025-06-11 19:50:52', NULL),
(213, 'Sabatia', '3803', 38, '2025-06-11 19:50:52', NULL),
(214, 'Vihiga', '3804', 38, '2025-06-11 19:50:52', NULL),
(215, 'Luanda', '3805', 38, '2025-06-11 19:50:52', NULL),
(216, 'Bumula', '3901', 39, '2025-06-11 19:50:52', NULL),
(217, 'Kanduyi', '3902', 39, '2025-06-11 19:50:52', NULL),
(218, 'Webuye East', '3903', 39, '2025-06-11 19:50:52', NULL),
(219, 'Webuye West', '3904', 39, '2025-06-11 19:50:52', NULL),
(220, 'Mt. Elgon', '3905', 39, '2025-06-11 19:50:52', NULL),
(221, 'Sirisia', '3906', 39, '2025-06-11 19:50:52', NULL),
(222, 'Tongaren', '3907', 39, '2025-06-11 19:50:52', NULL),
(223, 'Kabuchai', '3908', 39, '2025-06-11 19:50:52', NULL),
(224, 'Kimilili', '3909', 39, '2025-06-11 19:50:52', NULL),
(225, 'Teso North', '4001', 40, '2025-06-11 19:50:52', NULL),
(226, 'Teso South', '4002', 40, '2025-06-11 19:50:52', NULL),
(227, 'Nambale', '4003', 40, '2025-06-11 19:50:52', NULL),
(228, 'Matayos', '4004', 40, '2025-06-11 19:50:52', NULL),
(229, 'Butula', '4005', 40, '2025-06-11 19:50:52', NULL),
(230, 'Funyula', '4006', 40, '2025-06-11 19:50:52', NULL),
(231, 'Alego Usonga', '4101', 41, '2025-06-11 19:50:52', NULL),
(232, 'Gem', '4102', 41, '2025-06-11 19:50:52', NULL),
(233, 'Bondo', '4103', 41, '2025-06-11 19:50:52', NULL),
(234, 'Rarieda', '4104', 41, '2025-06-11 19:50:52', NULL),
(235, 'Ugenya', '4105', 41, '2025-06-11 19:50:52', NULL),
(236, 'Ugunja', '4106', 41, '2025-06-11 19:50:52', NULL),
(237, 'Kisumu Central', '4201', 42, '2025-06-11 19:50:52', NULL),
(238, 'Kisumu East', '4202', 42, '2025-06-11 19:50:52', NULL),
(239, 'Kisumu West', '4203', 42, '2025-06-11 19:50:52', NULL),
(240, 'Seme', '4204', 42, '2025-06-11 19:50:52', NULL),
(241, 'Nyando', '4205', 42, '2025-06-11 19:50:52', NULL),
(242, 'Muhoroni', '4206', 42, '2025-06-11 19:50:52', NULL),
(243, 'Nyakach', '4207', 42, '2025-06-11 19:50:52', NULL),
(244, 'Homa Bay Town', '4301', 43, '2025-06-11 19:50:52', NULL),
(245, 'Kabondo Kasipul', '4302', 43, '2025-06-11 19:50:52', NULL),
(246, 'Karachuonyo', '4303', 43, '2025-06-11 19:50:52', NULL),
(247, 'Kasipul', '4304', 43, '2025-06-11 19:50:52', NULL),
(248, 'Ndhiwa', '4305', 43, '2025-06-11 19:50:52', NULL),
(249, 'Rangwe', '4306', 43, '2025-06-11 19:50:52', NULL),
(250, 'Suba North', '4307', 43, '2025-06-11 19:50:52', NULL),
(251, 'Suba South', '4308', 43, '2025-06-11 19:50:52', NULL),
(252, 'Rongo', '4401', 44, '2025-06-11 19:50:52', NULL),
(253, 'Awendo', '4402', 44, '2025-06-11 19:50:52', NULL),
(254, 'Suna East', '4403', 44, '2025-06-11 19:50:52', NULL),
(255, 'Suna West', '4404', 44, '2025-06-11 19:50:52', NULL),
(256, 'Uriri', '4405', 44, '2025-06-11 19:50:52', NULL),
(257, 'Nyatike', '4406', 44, '2025-06-11 19:50:52', NULL),
(258, 'Kuria East', '4407', 44, '2025-06-11 19:50:52', NULL),
(259, 'Kuria West', '4408', 44, '2025-06-11 19:50:52', NULL),
(260, 'Kitutu Chache North', '4501', 45, '2025-06-11 19:50:52', NULL),
(261, 'Kitutu Chache South', '4502', 45, '2025-06-11 19:50:52', NULL),
(262, 'Nyaribari Masaba', '4503', 45, '2025-06-11 19:50:52', NULL),
(263, 'Nyaribari Chache', '4504', 45, '2025-06-11 19:50:52', NULL),
(264, 'Bomachoge Borabu', '4505', 45, '2025-06-11 19:50:52', NULL),
(265, 'Bomachoge Chache', '4506', 45, '2025-06-11 19:50:52', NULL),
(266, 'Bobasi', '4507', 45, '2025-06-11 19:50:52', NULL),
(267, 'South Mugirango', '4508', 45, '2025-06-11 19:50:52', NULL),
(268, 'Bonchari', '4509', 45, '2025-06-11 19:50:52', NULL),
(269, 'Borabu', '4601', 46, '2025-06-11 19:50:52', NULL),
(270, 'Kitutu Masaba', '4602', 46, '2025-06-11 19:50:52', NULL),
(271, 'West Mugirango', '4603', 46, '2025-06-11 19:50:52', NULL),
(272, 'North Mugirango', '4604', 46, '2025-06-11 19:50:52', NULL),
(273, 'Westlands', '4701', 47, '2025-06-11 19:50:52', NULL),
(274, 'Dagoretti North', '4702', 47, '2025-06-11 19:50:52', NULL),
(275, 'Dagoretti South', '4703', 47, '2025-06-11 19:50:52', NULL),
(276, 'Lang\'ata', '4704', 47, '2025-06-11 19:50:52', NULL),
(277, 'Kibra', '4705', 47, '2025-06-11 19:50:52', NULL),
(278, 'Roysambu', '4706', 47, '2025-06-11 19:50:52', NULL),
(279, 'Kasarani', '4707', 47, '2025-06-11 19:50:52', NULL),
(280, 'Ruaraka', '4708', 47, '2025-06-11 19:50:52', NULL),
(281, 'Embakasi South', '4709', 47, '2025-06-11 19:50:52', NULL),
(282, 'Embakasi North', '4710', 47, '2025-06-11 19:50:52', NULL),
(283, 'Embakasi Central', '4711', 47, '2025-06-11 19:50:52', NULL),
(284, 'Embakasi East', '4712', 47, '2025-06-11 19:50:52', NULL),
(285, 'Embakasi West', '4713', 47, '2025-06-11 19:50:52', NULL),
(286, 'Makadara', '4714', 47, '2025-06-11 19:50:52', NULL),
(287, 'Kamukunji', '4715', 47, '2025-06-11 19:50:52', NULL),
(288, 'Starehe', '4716', 47, '2025-06-11 19:50:52', NULL),
(289, 'Mathare', '4717', 47, '2025-06-11 19:50:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `content_field_values`
--

DROP TABLE IF EXISTS `content_field_values`;
CREATE TABLE IF NOT EXISTS `content_field_values` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `content_item_id` bigint UNSIGNED NOT NULL,
  `content_type_field_id` bigint UNSIGNED NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_field_value_unique` (`content_item_id`,`content_type_field_id`),
  KEY `content_field_values_content_type_field_id_foreign` (`content_type_field_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `content_field_values`
--

INSERT INTO `content_field_values` (`id`, `content_item_id`, `content_type_field_id`, `value`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Home/About Us', '2025-06-27 18:11:00', '2025-06-27 18:11:00', NULL),
(2, 1, 2, NULL, '2025-06-27 18:11:00', '2025-06-27 18:11:00', NULL),
(3, 1, 3, NULL, '2025-06-27 18:11:04', '2025-06-27 18:11:04', NULL),
(4, 1, 4, 'About Us', '2025-06-27 18:11:04', '2025-06-27 18:11:04', NULL),
(5, 2, 5, '[{\"icon\":\"12\",\"top_text\":\"Members\",\"counter_numnber\":\"45\"}]', '2025-06-30 02:24:09', '2025-07-01 12:45:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `content_items`
--

DROP TABLE IF EXISTS `content_items`;
CREATE TABLE IF NOT EXISTS `content_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `content_type_id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_items_content_type_id_slug_unique` (`content_type_id`,`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `content_items`
--

INSERT INTO `content_items` (`id`, `content_type_id`, `title`, `slug`, `status`, `published_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'About Page Featured Image', 'about-page-featured-image', 'draft', NULL, NULL, NULL, '2025-06-27 18:11:00', '2025-06-27 18:11:00', NULL),
(2, 2, 'Counter Home', 'counter-home', 'draft', NULL, NULL, NULL, '2025-06-30 02:24:09', '2025-06-30 02:24:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `content_types`
--

DROP TABLE IF EXISTS `content_types`;
CREATE TABLE IF NOT EXISTS `content_types` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_types_slug_unique` (`slug`),
  KEY `content_types_created_by_foreign` (`created_by`),
  KEY `content_types_updated_by_foreign` (`updated_by`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `content_types`
--

INSERT INTO `content_types` (`id`, `name`, `slug`, `description`, `icon`, `is_active`, `is_system`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Featured Image Content', 'featured-image-content', 'Generated from Featured Image Widget widget.', 'bx bx-layer', 1, 0, NULL, NULL, '2025-06-27 17:58:22', '2025-06-27 17:58:22', NULL),
(2, 'Counters', 'counters', 'Type that holds counters', NULL, 1, 0, NULL, NULL, '2025-06-29 18:28:40', '2025-06-29 18:28:40', NULL),
(3, 'Icon Card Content', 'icon-card-content', 'Generated from Icon Card Widget widget.', 'bx bx-layer', 1, 0, NULL, NULL, '2025-07-01 15:59:06', '2025-07-01 15:59:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `content_type_fields`
--

DROP TABLE IF EXISTS `content_type_fields`;
CREATE TABLE IF NOT EXISTS `content_type_fields` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `content_type_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validation_rules` text COLLATE utf8mb4_unicode_ci,
  `settings` json DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_unique` tinyint(1) NOT NULL DEFAULT '0',
  `position` int NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_type_fields_content_type_id_slug_unique` (`content_type_id`,`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `content_type_fields`
--

INSERT INTO `content_type_fields` (`id`, `content_type_id`, `name`, `slug`, `field_type`, `validation_rules`, `settings`, `is_required`, `is_unique`, `position`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'caption', 'caption', 'text', '', '\"{\\\"min_length\\\":0,\\\"max_length\\\":255}\"', 0, 0, 0, '', '2025-06-27 17:58:22', '2025-06-27 17:58:22', NULL),
(2, 1, 'image', 'image', 'image', '', '\"{\\\"allowed_extensions\\\":[\\\"jpg\\\",\\\"jpeg\\\",\\\"png\\\",\\\"gif\\\"],\\\"max_size\\\":2048}\"', 1, 0, 1, '', '2025-06-27 17:58:22', '2025-06-27 17:58:22', NULL),
(3, 1, 'link_u_r_l', 'link-u-r-l', 'url', '', '\"[]\"', 0, 0, 2, '', '2025-06-27 17:58:22', '2025-06-27 17:58:22', NULL),
(4, 1, 'title', 'title', 'text', '', '\"{\\\"min_length\\\":0,\\\"max_length\\\":255}\"', 0, 0, 3, '', '2025-06-27 17:58:22', '2025-06-27 17:58:22', NULL),
(5, 2, 'repeater', 'repeater', 'repeater', NULL, '\"{\\\"subfields\\\":[{\\\"name\\\":\\\"icon\\\",\\\"label\\\":\\\"Icon\\\",\\\"type\\\":\\\"image\\\",\\\"required\\\":true,\\\"settings\\\":{}},{\\\"name\\\":\\\"top_text\\\",\\\"label\\\":\\\"Top Text\\\",\\\"type\\\":\\\"text\\\",\\\"required\\\":true,\\\"settings\\\":{}},{\\\"name\\\":\\\"counter_numnber\\\",\\\"label\\\":\\\"Counter Numnber\\\",\\\"type\\\":\\\"number\\\",\\\"required\\\":false,\\\"settings\\\":{}}],\\\"min_items\\\":1,\\\"max_items\\\":10}\"', 0, 0, 1, NULL, '2025-06-29 18:36:55', '2025-06-29 18:36:55', NULL),
(6, 3, 'icon_cards', 'icon-cards', 'repeater', '', '\"{\\\"subfields\\\":[{\\\"name\\\":\\\"icon\\\",\\\"slug\\\":\\\"icon\\\",\\\"field_type\\\":\\\"image\\\",\\\"is_required\\\":true,\\\"is_unique\\\":false,\\\"position\\\":0,\\\"description\\\":\\\"Icon image for the card\\\",\\\"validation_rules\\\":\\\"\\\",\\\"settings\\\":{\\\"allowed_extensions\\\":[\\\"jpg\\\",\\\"jpeg\\\",\\\"png\\\",\\\"gif\\\"],\\\"max_size\\\":2048}},{\\\"name\\\":\\\"heading\\\",\\\"slug\\\":\\\"heading\\\",\\\"field_type\\\":\\\"text\\\",\\\"is_required\\\":true,\\\"is_unique\\\":false,\\\"position\\\":0,\\\"description\\\":\\\"Card heading\\\",\\\"validation_rules\\\":\\\"\\\",\\\"settings\\\":{\\\"min_length\\\":0,\\\"max_length\\\":255}},{\\\"name\\\":\\\"content\\\",\\\"slug\\\":\\\"content\\\",\\\"field_type\\\":\\\"textarea\\\",\\\"is_required\\\":true,\\\"is_unique\\\":false,\\\"position\\\":0,\\\"description\\\":\\\"Card content text\\\",\\\"validation_rules\\\":\\\"\\\",\\\"settings\\\":{\\\"min_length\\\":0,\\\"max_length\\\":255}},{\\\"name\\\":\\\"link_u_r_l\\\",\\\"slug\\\":\\\"link-u-r-l\\\",\\\"field_type\\\":\\\"url\\\",\\\"is_required\\\":false,\\\"is_unique\\\":false,\\\"position\\\":0,\\\"description\\\":\\\"Optional URL for the icon to link to\\\",\\\"validation_rules\\\":\\\"\\\",\\\"settings\\\":[]}],\\\"min_items\\\":1,\\\"max_items\\\":12}\"', 1, 0, 0, '', '2025-07-01 15:59:06', '2025-07-01 15:59:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `content_type_field_options`
--

DROP TABLE IF EXISTS `content_type_field_options`;
CREATE TABLE IF NOT EXISTS `content_type_field_options` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `field_id` bigint UNSIGNED NOT NULL,
  `label` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_index` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `content_type_field_options_field_id_foreign` (`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `counties`
--

DROP TABLE IF EXISTS `counties`;
CREATE TABLE IF NOT EXISTS `counties` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `counties_name_unique` (`name`),
  UNIQUE KEY `counties_code_unique` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counties`
--

INSERT INTO `counties` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'Mombasa', '001', NULL, NULL),
(2, 'Kwale', '002', NULL, NULL),
(3, 'Kilifi', '003', NULL, NULL),
(4, 'Tana River', '004', NULL, NULL),
(5, 'Lamu', '005', NULL, NULL),
(6, 'Taita-Taveta', '006', NULL, NULL),
(7, 'Garissa', '007', NULL, NULL),
(8, 'Wajir', '008', NULL, NULL),
(9, 'Mandera', '009', NULL, NULL),
(10, 'Marsabit', '010', NULL, NULL),
(11, 'Isiolo', '011', NULL, NULL),
(12, 'Meru', '012', NULL, NULL),
(13, 'Tharaka-Nithi', '013', NULL, NULL),
(14, 'Embu', '014', NULL, NULL),
(15, 'Kitui', '015', NULL, NULL),
(16, 'Machakos', '016', NULL, NULL),
(17, 'Makueni', '017', NULL, NULL),
(18, 'Nyandarua', '018', NULL, NULL),
(19, 'Nyeri', '019', NULL, NULL),
(20, 'Kirinyaga', '020', NULL, NULL),
(21, 'Murang\'a', '021', NULL, NULL),
(22, 'Kiambu', '022', NULL, NULL),
(23, 'Turkana', '023', NULL, NULL),
(24, 'West Pokot', '024', NULL, NULL),
(25, 'Samburu', '025', NULL, NULL),
(26, 'Trans Nzoia', '026', NULL, NULL),
(27, 'Uasin Gishu', '027', NULL, NULL),
(28, 'Elgeyo-Marakwet', '028', NULL, NULL),
(29, 'Nandi', '029', NULL, NULL),
(30, 'Baringo', '030', NULL, NULL),
(31, 'Laikipia', '031', NULL, NULL),
(32, 'Nakuru', '032', NULL, NULL),
(33, 'Narok', '033', NULL, NULL),
(34, 'Kajiado', '034', NULL, NULL),
(35, 'Kericho', '035', NULL, NULL),
(36, 'Bomet', '036', NULL, NULL),
(37, 'Kakamega', '037', NULL, NULL),
(38, 'Vihiga', '038', NULL, NULL),
(39, 'Bungoma', '039', NULL, NULL),
(40, 'Busia', '040', NULL, NULL),
(41, 'Siaya', '041', NULL, NULL),
(42, 'Kisumu', '042', NULL, NULL),
(43, 'Homa Bay', '043', NULL, NULL),
(44, 'Migori', '044', NULL, NULL),
(45, 'Kisii', '045', NULL, NULL),
(46, 'Nyamira', '046', NULL, NULL),
(47, 'Nairobi', '047', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ethnicities`
--

DROP TABLE IF EXISTS `ethnicities`;
CREATE TABLE IF NOT EXISTS `ethnicities` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ethnicities_name_unique` (`name`),
  UNIQUE KEY `ethnicities_code_unique` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ethnicities`
--

INSERT INTO `ethnicities` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'Kikuyu', 'Kikuyu', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(2, 'Luhya', 'Luhya', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(3, 'Kalenjin', 'Kalenjin', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(4, 'Luo', 'Luo', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(5, 'Kamba', 'Kamba', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(6, 'Kisii', 'Kisii', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(7, 'Mijikenda', 'Mijikenda', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(8, 'Meru', 'Meru', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(9, 'Turkana', 'Turkana', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(10, 'Maasai', 'Maasai', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(11, 'Teso', 'Teso', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(12, 'Embu', 'Embu', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(13, 'Taita', 'Taita', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(14, 'Kuria', 'Kuria', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(15, 'Samburu', 'Samburu', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(16, 'Tharaka', 'Tharaka', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(17, 'Mbeere', 'Mbeere', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(18, 'Borana', 'Borana', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(19, 'Basuba', 'Basuba', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(20, 'Swahili', 'Swahili', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(21, 'Gabra', 'Gabra', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(22, 'Orma', 'Orma', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(23, 'Rendille', 'Rendille', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(24, 'Somali', 'Somali', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(25, 'Gosha', 'Gosha', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(26, 'Burji', 'Burji', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(27, 'Daasanach', 'Daasanach', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(28, 'El Molo', 'El Molo', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(29, 'Konso', 'Konso', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(30, 'Sakuye', 'Sakuye', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(31, 'Galjeel', 'Galjeel', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(32, 'Ajuran', 'Ajuran', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(33, 'Degodia', 'Degodia', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(34, 'Ogaden', 'Ogaden', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(35, 'Murulle', 'Murulle', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(36, 'Pokot', 'Pokot', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(37, 'Endorois', 'Endorois', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(38, 'Nubi', 'Nubi', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(39, 'Yaaku', 'Yaaku', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(40, 'Bajuni', 'Bajuni', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(41, 'Dahalo', 'Dahalo', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(42, 'Taveta', 'Taveta', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(43, 'Pokomo', 'Pokomo', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(44, 'Boni', 'Boni', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(45, 'Sabaot', 'Sabaot', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(46, 'Ilchamus', 'Ilchamus', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(47, 'Sengwer', 'Sengwer', '2025-06-11 13:58:46', '2025-06-11 13:58:46');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"42722e1b-b3e4-42d2-8e43-bb7d39cd7e9f\",\"displayName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"command\":\"O:69:\\\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\\\":2:{s:8:\\\"\\u0000*\\u0000media\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:49:\\\"Spatie\\\\MediaLibrary\\\\MediaCollections\\\\Models\\\\Media\\\";s:2:\\\"id\\\";i:4;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"connection\\\";s:8:\\\"database\\\";}\"}}', 0, NULL, 1750265304, 1750265304),
(2, 'default', '{\"uuid\":\"c08e0ac0-a03b-40f6-ac2e-f79c60150336\",\"displayName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"command\":\"O:69:\\\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\\\":2:{s:8:\\\"\\u0000*\\u0000media\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:49:\\\"Spatie\\\\MediaLibrary\\\\MediaCollections\\\\Models\\\\Media\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"connection\\\";s:8:\\\"database\\\";}\"}}', 0, NULL, 1750267065, 1750267065),
(3, 'default', '{\"uuid\":\"79aecfd0-65b1-4619-861c-89cb18ec3123\",\"displayName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"command\":\"O:69:\\\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\\\":2:{s:8:\\\"\\u0000*\\u0000media\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:49:\\\"Spatie\\\\MediaLibrary\\\\MediaCollections\\\\Models\\\\Media\\\";s:2:\\\"id\\\";i:6;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"connection\\\";s:8:\\\"database\\\";}\"}}', 0, NULL, 1750268922, 1750268922),
(4, 'default', '{\"uuid\":\"27b21c72-b9e5-4ee2-bad7-015dbb3c8e76\",\"displayName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"command\":\"O:69:\\\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\\\":2:{s:8:\\\"\\u0000*\\u0000media\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:49:\\\"Spatie\\\\MediaLibrary\\\\MediaCollections\\\\Models\\\\Media\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"connection\\\";s:8:\\\"database\\\";}\"}}', 0, NULL, 1750354939, 1750354939),
(5, 'default', '{\"uuid\":\"a35f11a5-3520-46f2-aa61-b1e8733775dd\",\"displayName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"command\":\"O:69:\\\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\\\":2:{s:8:\\\"\\u0000*\\u0000media\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:49:\\\"Spatie\\\\MediaLibrary\\\\MediaCollections\\\\Models\\\\Media\\\";s:2:\\\"id\\\";i:8;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"connection\\\";s:8:\\\"database\\\";}\"}}', 0, NULL, 1750443567, 1750443567),
(6, 'default', '{\"uuid\":\"09099b80-613e-45f9-891b-706de291c905\",\"displayName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\",\"command\":\"O:69:\\\"Spatie\\\\MediaLibrary\\\\ResponsiveImages\\\\Jobs\\\\GenerateResponsiveImagesJob\\\":2:{s:8:\\\"\\u0000*\\u0000media\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:49:\\\"Spatie\\\\MediaLibrary\\\\MediaCollections\\\\Models\\\\Media\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"connection\\\";s:8:\\\"database\\\";}\"}}', 0, NULL, 1750444272, 1750444272);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
CREATE TABLE IF NOT EXISTS `media` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  `folder_id` bigint UNSIGNED DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint UNSIGNED NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_order_column_index` (`order_column`),
  KEY `media_folder_id_foreign` (`folder_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `model_type`, `model_id`, `folder_id`, `uuid`, `collection_name`, `name`, `file_name`, `mime_type`, `disk`, `conversions_disk`, `size`, `manipulations`, `custom_properties`, `generated_conversions`, `responsive_images`, `order_column`, `created_at`, `updated_at`) VALUES
(8, 'App\\Models\\User', 2, NULL, '07284a95-8357-447c-addc-905fb2dfe9f2', 'profile_photos', 'avatar3.jpg', 'VNLbOX0d6GjRN6YUXQlnYVoclVql5h0iRohIJoVc.jpg', 'image/jpeg', 'media', 'media', 928372, '[]', '[]', '[]', '[]', 2, '2025-06-20 15:19:26', '2025-06-20 15:19:26'),
(9, 'App\\Models\\User', 3, NULL, 'e959fa25-ef75-4242-ba6c-0a126e19ff8d', 'profile_photos', 'avatar1.jpg', 'yms0HEnrGZmdigDG7KeaNLXjT4GaG5EbXVRgg7Vz.jpg', 'image/jpeg', 'media', 'media', 17938, '[]', '[]', '[]', '[]', 1, '2025-06-20 15:31:12', '2025-06-20 15:31:12'),
(3, 'App\\Models\\User', 4, NULL, '7eb94da6-6ec3-47bc-afd7-a774d09a9dfc', 'profile_photos', 'avatar1', 'avatar1.jpg', 'image/jpeg', 'public', 'public', 17938, '[]', '[]', '[]', '[]', 1, '2025-06-13 17:30:16', '2025-06-13 17:30:16'),
(4, 'App\\Models\\Admin', 6, NULL, '6808a5ac-7c24-4697-9762-5d2eef9945cc', 'profile_photos', 'avatar3.jpg', '61EDXXlFf8Ji3mCF2Y8rwmJsaW7s5BVP7Gca30LW.jpg', 'image/jpeg', 'media', 'media', 928372, '[]', '[]', '[]', '[]', 1, '2025-06-18 13:48:23', '2025-06-18 13:48:23'),
(5, 'App\\Models\\Admin', 7, NULL, '3211f879-ebe1-4ed3-9d90-8581040f74c4', 'profile_photos', 'avatar3.jpg', 'tppdDoV4T5otIN1Dcqe1InoNgm9zTM5aPGWftawu.jpg', 'image/jpeg', 'media', 'media', 928372, '[]', '[]', '[]', '[]', 1, '2025-06-18 14:17:44', '2025-06-18 14:17:44'),
(6, 'App\\Models\\User', 6, NULL, '2406872b-738e-460f-a434-2119874cd78e', 'profile_photos', 'avatar3.jpg', 'nL3jQ6u5KiItpTEr79Nxgiax7pBbEhuye7uzjRny.jpg', 'image/jpeg', 'media', 'media', 928372, '[]', '[]', '[]', '[]', 1, '2025-06-18 14:48:42', '2025-06-18 14:48:42'),
(7, 'App\\Models\\User', 1, NULL, '69e71062-67da-4066-96ec-a65c34e63850', 'profile_photos', 'womanavatar', 'womanavatar.jpg', 'image/jpeg', 'media', 'media', 23188, '[]', '[]', '[]', '[]', 2, '2025-06-19 14:42:17', '2025-06-19 14:42:17'),
(10, 'App\\Models\\ContentItem', 1, NULL, '57fafbfe-47b2-4a6b-9177-e26656c6462b', 'field_2', 'about us', 'about-us.jpg', 'image/jpeg', 'media', 'media', 58527, '[]', '{\"field_id\": 2}', '[]', '[]', 1, '2025-06-27 18:11:03', '2025-06-27 18:11:03'),
(11, 'App\\Models\\MediaLibrary', 1, NULL, '4dfdd465-257c-45f8-8305-f6067734794c', 'images', '1', '1.jpg', 'image/jpeg', 'media', 'media', 191337, '[]', '{\"alt\": \"\", \"title\": \"\", \"caption\": \"\"}', '[]', '[]', 1, '2025-06-30 16:10:01', '2025-06-30 16:10:01'),
(12, 'App\\Models\\MediaLibrary', 2, NULL, '0592c195-bff0-45ed-923b-1cd6ebbcc40f', 'images', '1', '1.png', 'image/png', 'media', 'media', 2078, '[]', '{\"alt\": \"\", \"title\": \"\", \"caption\": \"\"}', '[]', '[]', 1, '2025-07-01 05:11:58', '2025-07-01 05:11:58'),
(13, 'App\\Models\\MediaLibrary', 4, NULL, '1325c4b7-6557-4ee1-863c-3f13287dc46c', 'images', 'icon3', 'icon3.png', 'image/png', 'media', 'media', 1834, '[]', '{\"alt\": \"\", \"title\": \"\", \"caption\": \"\"}', '[]', '[]', 1, '2025-07-01 05:16:12', '2025-07-01 05:16:12'),
(14, 'App\\Models\\MediaLibrary', 3, NULL, 'c91482f9-7300-4200-85cf-421ac6618747', 'images', 'icon2', 'icon2.png', 'image/png', 'media', 'media', 1687, '[]', '{\"alt\": \"\", \"title\": \"\", \"caption\": \"\"}', '[]', '[]', 1, '2025-07-01 05:16:12', '2025-07-01 05:16:12');

-- --------------------------------------------------------

--
-- Table structure for table `media_folders`
--

DROP TABLE IF EXISTS `media_folders`;
CREATE TABLE IF NOT EXISTS `media_folders` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_folders_slug_unique` (`slug`),
  KEY `media_folders_parent_id_foreign` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_libraries`
--

DROP TABLE IF EXISTS `media_libraries`;
CREATE TABLE IF NOT EXISTS `media_libraries` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_libraries`
--

INSERT INTO `media_libraries` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, NULL, '2025-06-30 16:10:00', '2025-06-30 16:10:00'),
(2, NULL, '2025-07-01 05:11:58', '2025-07-01 05:11:58'),
(3, NULL, '2025-07-01 05:16:12', '2025-07-01 05:16:12'),
(4, NULL, '2025-07-01 05:16:12', '2025-07-01 05:16:12');

-- --------------------------------------------------------

--
-- Table structure for table `media_tags`
--

DROP TABLE IF EXISTS `media_tags`;
CREATE TABLE IF NOT EXISTS `media_tags` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_tags_slug_unique` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_tag_media`
--

DROP TABLE IF EXISTS `media_tag_media`;
CREATE TABLE IF NOT EXISTS `media_tag_media` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `media_id` bigint UNSIGNED NOT NULL,
  `media_tag_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_tag_media_media_id_media_tag_id_unique` (`media_id`,`media_tag_id`),
  KEY `media_tag_media_media_tag_id_foreign` (`media_tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

DROP TABLE IF EXISTS `memberships`;
CREATE TABLE IF NOT EXISTS `memberships` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `membership_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `payment_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regular',
  `fee_amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_card` tinyint(1) NOT NULL DEFAULT '0',
  `card_issue_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `memberships_membership_number_unique` (`membership_number`),
  KEY `memberships_user_id_index` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`id`, `membership_number`, `user_id`, `start_date`, `end_date`, `status`, `payment_status`, `membership_type`, `fee_amount`, `payment_date`, `payment_method`, `payment_reference`, `issued_card`, `card_issue_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'NPK-PM20250001', 1, '2025-06-19', '2026-06-19', 'pending', 'unpaid', 'regular', NULL, NULL, NULL, NULL, 0, NULL, '2025-06-19 14:42:14', '2025-06-19 14:42:14', NULL),
(2, 'NPK-PM20250002', 2, '2025-06-20', '2026-06-20', 'pending', 'unpaid', 'regular', NULL, NULL, NULL, NULL, 0, NULL, '2025-06-20 15:19:26', '2025-06-20 15:19:26', NULL),
(3, 'NPK-PM20250003', 3, '2025-06-20', '2026-06-20', 'pending', 'unpaid', 'regular', NULL, NULL, NULL, NULL, 0, NULL, '2025-06-20 15:31:12', '2025-06-20 15:31:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
CREATE TABLE IF NOT EXISTS `menus` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'header, footer, sidebar, etc.',
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menus_slug_unique` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `slug`, `location`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Header Navigation', 'header-nav', 'header', 'Main navigation displayed in the header', 1, '2025-06-04 21:04:41', '2025-06-04 21:04:41'),
(2, 'Footer Navigation', 'footer-nav', 'footer', 'Secondary navigation displayed in the footer', 1, '2025-06-04 21:04:41', '2025-06-04 21:04:41');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `label` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'url' COMMENT 'url, page, section, external',
  `url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '_self, _blank, etc.',
  `page_id` bigint UNSIGNED DEFAULT NULL,
  `section_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID of the section for one-page navigation',
  `visibility_conditions` json DEFAULT NULL COMMENT 'JSON with page/template/auth conditions',
  `position` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_parent_id_foreign` (`parent_id`),
  KEY `menu_items_page_id_foreign` (`page_id`),
  KEY `menu_items_menu_id_parent_id_is_active_position_index` (`menu_id`,`parent_id`,`is_active`,`position`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `label`, `link_type`, `url`, `target`, `page_id`, `section_id`, `visibility_conditions`, `position`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Home', 'url', '/', '_self', NULL, NULL, NULL, 0, 1, '2025-06-04 21:04:41', '2025-06-06 20:23:35'),
(2, 1, NULL, 'About Party', 'url', '/about', '_self', NULL, NULL, NULL, 1, 1, '2025-06-04 21:04:41', '2025-06-06 20:23:35'),
(3, 1, NULL, 'Leadership', 'url', '/about', '_self', NULL, NULL, NULL, 2, 1, '2025-06-04 21:04:41', '2025-06-12 16:52:23'),
(4, 1, 3, 'Executive Members', 'url', '/about', '_self', NULL, NULL, NULL, 0, 1, '2025-06-04 21:04:41', '2025-06-12 16:58:11'),
(5, 1, 3, 'Secretariat', 'url', '/about', '_self', NULL, NULL, NULL, 1, 1, '2025-06-04 21:04:41', '2025-06-12 16:58:57'),
(6, 1, NULL, 'Contact', 'url', '/contact', '_self', NULL, NULL, NULL, 4, 1, '2025-06-04 21:04:41', '2025-06-06 20:23:35'),
(7, 2, NULL, 'Privacy Policy', 'url', '/privacy', '_self', NULL, NULL, NULL, 1, 1, '2025-06-04 21:04:41', '2025-06-04 21:04:41'),
(8, 2, NULL, 'Terms of Service', 'url', '/terms', '_self', NULL, NULL, NULL, 2, 1, '2025-06-04 21:04:41', '2025-06-04 21:04:41'),
(9, 2, NULL, 'Contact', 'url', '/contact', '_self', NULL, NULL, NULL, 3, 1, '2025-06-04 21:04:41', '2025-06-12 16:44:50'),
(10, 1, NULL, 'News', 'url', '/news', '_self', NULL, 'features-section', NULL, 3, 1, '2025-06-04 21:04:41', '2025-06-12 16:59:47'),
(11, 2, NULL, 'Members Area', 'url', '/login', '_self', NULL, NULL, NULL, 4, 1, '2025-06-12 16:40:05', '2025-06-12 16:40:05'),
(12, 2, NULL, 'Admin Area', 'url', 'admin/login', '_self', NULL, NULL, NULL, 5, 1, '2025-06-12 17:00:50', '2025-06-13 08:03:25');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(41, '2025_06_10_181123_create_memberships_table', 6),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_05_22_182540_create_permission_tables', 1),
(5, '2025_05_22_185621_create_media_table', 1),
(6, '2025_05_22_192930_add_two_factor_columns_to_users_table', 1),
(7, '2025_05_22_193122_create_admins_table', 1),
(8, '2025_06_01_000001_create_themes_table', 1),
(49, '2025_06_01_000002_create_templates_table', 11),
(11, '2025_06_01_000004_create_widgets_table', 1),
(12, '2025_06_01_000005_create_widget_field_definitions_table', 1),
(13, '2025_06_01_000006_create_content_types_table', 1),
(14, '2025_06_01_000007_create_content_type_fields_table', 1),
(44, '2025_06_01_000008_create_widget_content_type_associations_table', 8),
(16, '2025_06_01_000009_create_content_items_table', 1),
(17, '2025_06_01_000010_create_content_field_values_table', 1),
(18, '2025_06_01_000011_create_pages_table', 1),
(19, '2025_06_01_000012_create_page_sections_table', 1),
(20, '2025_06_01_000013_create_page_section_widgets_table', 1),
(21, '2025_06_04_000001_create_menus_table', 1),
(22, '2025_06_04_000002_create_menu_items_table', 1),
(23, '2025_06_10_181121_create_ethnicities_table', 2),
(40, '2025_06_10_181121_create_profiles_table', 6),
(25, '2025_06_10_181121_create_special_statuses_table', 2),
(26, '2025_06_10_181122_create_mobile_providers_table', 2),
(27, '2025_06_10_181122_create_profile_types_table', 2),
(28, '2025_06_10_181122_create_religions_table', 2),
(33, '2025_06_10_181123_create_counties_table', 3),
(39, '0001_01_01_000000_create_users_table', 6),
(34, '2025_06_10_181124_create_constituencies_table', 3),
(35, '2025_06_10_181124_create_wards_table', 3),
(37, '2025_06_10_184101_create_polling_stations_table', 4),
(38, '2024_03_21_create_admin_user_sessions_table', 5),
(42, '2025_06_24_174233_create_content_type_field_options_table', 7),
(43, '2025_06_24_174433_add_missing_fields_to_content_types_table', 7),
(45, '2025_06_30_164514_create_media_tags_table', 9),
(46, '2025_06_30_create_media_folders_table', 9),
(47, '2025_06_30_create_media_tag_media_table', 9),
(48, '2025_06_30_190054_create_media_libraries_table', 10),
(51, '2025_06_01_000003_create_template_sections_table', 12);

-- --------------------------------------------------------

--
-- Table structure for table `mobile_providers`
--

DROP TABLE IF EXISTS `mobile_providers`;
CREATE TABLE IF NOT EXISTS `mobile_providers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefix` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile_providers_name_unique` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mobile_providers`
--

INSERT INTO `mobile_providers` (`id`, `name`, `prefix`, `logo_path`, `created_at`, `updated_at`) VALUES
(1, 'Safaricom', '07', NULL, '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(2, 'Airtel', '07', NULL, '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(3, 'Telkom', '07', NULL, '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(4, 'Equitel', '07', NULL, '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(5, 'Faiba 4G', '07', NULL, '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(6, 'Other', '07', NULL, '2025-06-11 13:59:19', '2025-06-11 13:59:19');

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\Admin', 1),
(2, 'App\\Models\\Admin', 2),
(2, 'App\\Models\\Admin', 4),
(3, 'App\\Models\\Admin', 3),
(3, 'App\\Models\\Admin', 4),
(3, 'App\\Models\\Admin', 5),
(3, 'App\\Models\\Admin', 6),
(3, 'App\\Models\\Admin', 7),
(4, 'App\\Models\\Admin', 2),
(4, 'App\\Models\\Admin', 8),
(4, 'App\\Models\\Admin', 9),
(5, 'App\\Models\\User', 1),
(5, 'App\\Models\\User', 2),
(5, 'App\\Models\\User', 3),
(5, 'App\\Models\\User', 5),
(5, 'App\\Models\\User', 6);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_homepage` tinyint(1) NOT NULL DEFAULT '0',
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`),
  KEY `pages_template_id_foreign` (`template_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `template_id`, `title`, `slug`, `is_homepage`, `meta_description`, `meta_keywords`, `status`, `published_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Home Page', 'home', 1, NULL, NULL, 'published', NULL, NULL, NULL, '2025-07-18 18:06:25', '2025-07-20 11:08:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `page_sections`
--

DROP TABLE IF EXISTS `page_sections`;
CREATE TABLE IF NOT EXISTS `page_sections` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` bigint UNSIGNED NOT NULL,
  `template_section_id` bigint UNSIGNED NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `column_span_override` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `column_offset_override` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `css_classes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `padding` json DEFAULT NULL,
  `margin` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_sections_page_id_foreign` (`page_id`),
  KEY `page_sections_template_section_id_foreign` (`template_section_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_sections`
--

INSERT INTO `page_sections` (`id`, `page_id`, `template_section_id`, `position`, `column_span_override`, `column_offset_override`, `css_classes`, `background_color`, `padding`, `margin`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-18 18:06:25', '2025-07-18 18:06:25', NULL),
(2, 1, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-18 18:06:25', '2025-07-18 18:06:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `page_section_widgets`
--

DROP TABLE IF EXISTS `page_section_widgets`;
CREATE TABLE IF NOT EXISTS `page_section_widgets` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_section_id` bigint UNSIGNED NOT NULL,
  `widget_id` bigint UNSIGNED NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `column_position` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `content_query` json DEFAULT NULL,
  `css_classes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `padding` json DEFAULT NULL,
  `margin` json DEFAULT NULL,
  `min_height` int DEFAULT NULL,
  `max_height` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_section_widgets_page_section_id_foreign` (`page_section_id`),
  KEY `page_section_widgets_widget_id_foreign` (`widget_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_section_widgets`
--

INSERT INTO `page_section_widgets` (`id`, `page_section_id`, `widget_id`, `position`, `column_position`, `settings`, `content_query`, `css_classes`, `padding`, `margin`, `min_height`, `max_height`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 3, 1, NULL, '[]', '{\"limit\": 1, \"order_by\": \"created_at\", \"content_type_id\": 1, \"order_direction\": \"desc\", \"content_item_ids\": [1]}', NULL, NULL, NULL, NULL, NULL, '2025-07-22 13:29:31', '2025-07-22 13:29:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view themes', 'admin', '2025-06-04 21:04:39', '2025-06-04 21:04:39'),
(2, 'create themes', 'admin', '2025-06-04 21:04:39', '2025-06-04 21:04:39'),
(3, 'edit themes', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(4, 'delete themes', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(5, 'activate themes', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(6, 'view templates', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(7, 'create templates', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(8, 'edit templates', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(9, 'delete templates', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(10, 'view content types', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(11, 'create content types', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(12, 'edit content types', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(13, 'delete content types', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(14, 'view content type fields', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(15, 'create content type fields', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(16, 'edit content type fields', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(17, 'delete content type fields', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(18, 'view content items', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(19, 'create content items', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(20, 'edit content items', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(21, 'delete content items', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(22, 'publish content items', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(23, 'view others content', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(24, 'edit others content', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(25, 'view pages', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(26, 'create pages', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(27, 'edit pages', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(28, 'delete pages', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(29, 'publish pages', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(30, 'view page sections', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(31, 'edit page sections', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(32, 'set homepage', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(33, 'view widgets', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(34, 'create widgets', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(35, 'edit widgets', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(36, 'delete widgets', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(37, 'view widget fields', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(38, 'edit widget fields', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(39, 'configure widgets', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(40, 'place widgets', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(41, 'view admins', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(42, 'create admins', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(43, 'edit admins', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(44, 'delete admins', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(45, 'view roles', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(46, 'create roles', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(47, 'edit roles', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(48, 'delete roles', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(49, 'assign roles', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(50, 'access settings', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(51, 'edit settings', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(52, 'view system logs', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(53, 'run maintenance', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(54, 'view profile', 'web', '2025-06-11 19:50:56', '2025-06-11 19:50:56'),
(55, 'edit profile', 'web', '2025-06-11 19:50:57', '2025-06-11 19:50:57'),
(56, 'view events', 'web', '2025-06-11 19:50:57', '2025-06-11 19:50:57'),
(57, 'register for events', 'web', '2025-06-11 19:50:57', '2025-06-11 19:50:57'),
(58, 'access member resources', 'web', '2025-06-11 19:50:57', '2025-06-11 19:50:57'),
(59, 'volunteer for tasks', 'web', '2025-06-11 19:50:57', '2025-06-11 19:50:57'),
(60, 'track volunteer hours', 'web', '2025-06-11 19:50:57', '2025-06-11 19:50:57'),
(61, 'access public resources', 'web', '2025-06-11 19:50:57', '2025-06-11 19:50:57');

-- --------------------------------------------------------

--
-- Table structure for table `polling_stations`
--

DROP TABLE IF EXISTS `polling_stations`;
CREATE TABLE IF NOT EXISTS `polling_stations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ward_id` bigint UNSIGNED NOT NULL,
  `location_description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `polling_stations_name_ward_id_unique` (`name`,`ward_id`),
  UNIQUE KEY `polling_stations_code_unique` (`code`),
  KEY `polling_stations_ward_id_foreign` (`ward_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

DROP TABLE IF EXISTS `profiles`;
CREATE TABLE IF NOT EXISTS `profiles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `id_passport_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `postal_address` text COLLATE utf8mb4_unicode_ci,
  `mobile_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ethnicity_id` bigint UNSIGNED DEFAULT NULL,
  `special_status_id` bigint UNSIGNED DEFAULT NULL,
  `ncpwd_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion_id` bigint UNSIGNED DEFAULT NULL,
  `mobile_provider_id` bigint UNSIGNED DEFAULT NULL,
  `county_id` bigint UNSIGNED DEFAULT NULL,
  `constituency_id` bigint UNSIGNED DEFAULT NULL,
  `ward_id` bigint UNSIGNED DEFAULT NULL,
  `enlisting_date` date DEFAULT NULL,
  `recruiting_person` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_type_id` bigint UNSIGNED NOT NULL,
  `additional_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profiles_ethnicity_id_foreign` (`ethnicity_id`),
  KEY `profiles_special_status_id_foreign` (`special_status_id`),
  KEY `profiles_religion_id_foreign` (`religion_id`),
  KEY `profiles_mobile_provider_id_foreign` (`mobile_provider_id`),
  KEY `profiles_county_id_foreign` (`county_id`),
  KEY `profiles_constituency_id_foreign` (`constituency_id`),
  KEY `profiles_ward_id_foreign` (`ward_id`),
  KEY `profiles_profile_type_id_foreign` (`profile_type_id`),
  KEY `profiles_user_id_index` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `id_passport_number`, `membership_number`, `date_of_birth`, `postal_address`, `mobile_number`, `gender`, `ethnicity_id`, `special_status_id`, `ncpwd_number`, `religion_id`, `mobile_provider_id`, `county_id`, `constituency_id`, `ward_id`, `enlisting_date`, `recruiting_person`, `profile_type_id`, `additional_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '12345678', NULL, '1995-06-19', 'Machakos', '0711223344', 'male', 5, 3, NULL, 1, 1, 16, 78, 387, '2025-06-19', NULL, 1, '{\"agree_marketing\": true}', '2025-06-19 14:42:14', '2025-06-19 14:42:14', NULL),
(2, 2, NULL, NULL, NULL, NULL, '0722111222', 'male', 1, 3, NULL, 1, 1, 22, 111, 552, '2025-06-20', NULL, 1, NULL, '2025-06-20 15:19:26', '2025-06-20 15:19:26', NULL),
(3, 3, NULL, NULL, NULL, NULL, '0722333444', 'male', 1, 4, NULL, 1, 1, 20, 102, 508, '2025-06-20', NULL, 1, NULL, '2025-06-20 15:31:12', '2025-06-20 15:31:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `profile_types`
--

DROP TABLE IF EXISTS `profile_types`;
CREATE TABLE IF NOT EXISTS `profile_types` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `dashboard_route` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `profile_types_name_unique` (`name`),
  UNIQUE KEY `profile_types_code_unique` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profile_types`
--

INSERT INTO `profile_types` (`id`, `name`, `code`, `description`, `dashboard_route`, `created_at`, `updated_at`) VALUES
(1, 'Party Member', 'PM', 'Standard party member with basic privileges', NULL, '2025-06-11 19:50:55', NULL),
(2, 'Party Official', 'PO', 'Elected or appointed party official', NULL, '2025-06-11 19:50:55', NULL),
(3, 'Party Staff', 'PS', 'Party secretariat staff member', NULL, '2025-06-11 19:50:55', NULL),
(4, 'Volunteer', 'VOLUNTEER', 'Volunteer for the party', NULL, '2025-06-11 19:50:55', NULL),
(5, 'Party Aspirant', 'PA', 'Member seeking party nomination for elective position', NULL, '2025-06-11 19:50:55', NULL),
(6, 'Voter', 'VOTER', 'Voter who wants to see party information', NULL, '2025-06-11 19:50:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `religions`
--

DROP TABLE IF EXISTS `religions`;
CREATE TABLE IF NOT EXISTS `religions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `religions_name_unique` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `religions`
--

INSERT INTO `religions` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Christianity', '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(2, 'Islam', '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(3, 'Hinduism', '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(4, 'Traditional African Religion', '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(5, 'Sikhism', '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(6, 'Buddhism', '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(7, 'Baha\'i Faith', '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(8, 'Jainism', '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(9, 'None', '2025-06-11 13:59:19', '2025-06-11 13:59:19'),
(10, 'Other', '2025-06-11 13:59:19', '2025-06-11 13:59:19');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super-admin', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(2, 'administrator', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(3, 'editor', 'admin', '2025-06-04 21:04:40', '2025-06-04 21:04:40'),
(4, 'content-creator', 'admin', '2025-06-04 21:04:41', '2025-06-04 21:04:41'),
(5, 'party_member', 'web', '2025-06-11 19:50:57', '2025-06-11 19:50:57'),
(6, 'volunteer', 'web', '2025-06-11 19:50:57', '2025-06-11 19:50:57'),
(7, 'voter', 'web', '2025-06-11 19:50:58', '2025-06-11 19:50:58');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(3, 1),
(3, 2),
(4, 1),
(5, 1),
(5, 2),
(6, 1),
(6, 2),
(7, 1),
(8, 1),
(8, 2),
(9, 1),
(10, 1),
(10, 2),
(11, 1),
(11, 2),
(12, 1),
(12, 2),
(13, 1),
(14, 1),
(14, 2),
(15, 1),
(15, 2),
(16, 1),
(16, 2),
(17, 1),
(18, 1),
(18, 2),
(18, 3),
(18, 4),
(19, 1),
(19, 2),
(19, 3),
(19, 4),
(20, 1),
(20, 2),
(20, 3),
(20, 4),
(21, 1),
(21, 2),
(21, 3),
(22, 1),
(22, 2),
(22, 3),
(23, 1),
(23, 2),
(23, 3),
(24, 1),
(24, 2),
(24, 3),
(25, 1),
(25, 2),
(25, 3),
(25, 4),
(26, 1),
(26, 2),
(26, 3),
(26, 4),
(27, 1),
(27, 2),
(27, 3),
(27, 4),
(28, 1),
(28, 2),
(29, 1),
(29, 2),
(29, 3),
(30, 1),
(30, 2),
(30, 3),
(30, 4),
(31, 1),
(31, 2),
(31, 3),
(32, 1),
(32, 2),
(33, 1),
(33, 2),
(33, 3),
(33, 4),
(34, 1),
(35, 1),
(35, 2),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(39, 2),
(39, 3),
(40, 1),
(40, 2),
(40, 3),
(40, 4),
(41, 1),
(41, 2),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 5),
(54, 6),
(54, 7),
(55, 5),
(55, 6),
(55, 7),
(56, 5),
(56, 6),
(56, 7),
(57, 5),
(57, 6),
(58, 5),
(58, 6),
(59, 6),
(60, 6),
(61, 7);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('C2OjhUyr1dcGGu5fidfNowWttdPGU0sBuJvxLt1p', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVko4YklkenBETTNuWEhpbm9lZWpvVThvemtnejJvVkxXTEJUb1dTUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjA6Imh0dHA6Ly9yZWFsc3lzY21zLmxvY2FsOjgwODAvdGhlbWVzL21pYXRhL2Fzc2V0cy9mYXZpY29uLmljbyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjM6InVybCI7YTowOnt9czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YTowOnt9fQ==', 1753210933),
('LKlW9SbpApkOU5Ot6Ax6w13sNrwXDYLYGcFjZ6vp', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiN2Yxa2dnUXZ2RHRyMjBIeG5idm5LSGFyWjM1aGFQZE5oeFV3YVB6SyI7czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YTowOnt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjE6Imh0dHA6Ly9yZWFsc3lzY21zLmxvY2FsOjgwODAvYXNzZXRzL2FkbWluL2Nzcy9hcHAubWluLmNzcy5tYXAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1753207937);

-- --------------------------------------------------------

--
-- Table structure for table `special_statuses`
--

DROP TABLE IF EXISTS `special_statuses`;
CREATE TABLE IF NOT EXISTS `special_statuses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `special_statuses_code_unique` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `special_statuses`
--

INSERT INTO `special_statuses` (`id`, `name`, `code`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Youth', 'YOUTH', 'Kenyan youth aged 18-35 years', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(2, 'Person with Disabilities', 'PWD', 'Persons with various forms of disabilities', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(3, 'Women', 'WOMEN', 'Female gender category for special representation', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(4, 'Elder', 'ELDER', 'Senior citizens aged 60 years and above', '2025-06-11 13:58:46', '2025-06-11 13:58:46'),
(5, 'Marginalized Community', 'MARGINALIZED', 'Members of historically marginalized communities', '2025-06-11 13:58:46', '2025-06-11 13:58:46');

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

DROP TABLE IF EXISTS `templates`;
CREATE TABLE IF NOT EXISTS `templates` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `theme_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `settings` json DEFAULT NULL,
  `layout_data` json DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `templates_theme_id_slug_unique` (`theme_id`,`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `theme_id`, `name`, `slug`, `file_path`, `description`, `settings`, `layout_data`, `is_default`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'Default Template', 'default-template', 'default.blade.php', 'The default template to design all pages', NULL, NULL, 1, 1, '2025-07-16 17:09:47', '2025-07-20 11:04:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `template_sections`
--

DROP TABLE IF EXISTS `template_sections`;
CREATE TABLE IF NOT EXISTS `template_sections` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `x` int NOT NULL DEFAULT '0',
  `y` int NOT NULL DEFAULT '0',
  `w` int NOT NULL DEFAULT '12',
  `h` int NOT NULL DEFAULT '3',
  `section_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'full-width',
  `column_layout` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_repeatable` tinyint(1) NOT NULL DEFAULT '0',
  `max_widgets` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `settings` json DEFAULT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `widget_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_sections_template_id_slug_unique` (`template_id`,`slug`),
  KEY `template_sections_parent_id_foreign` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `template_sections`
--

INSERT INTO `template_sections` (`id`, `template_id`, `name`, `slug`, `position`, `x`, `y`, `w`, `h`, `section_type`, `column_layout`, `is_repeatable`, `max_widgets`, `description`, `settings`, `parent_id`, `widget_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Hero Header', 'hero-header', 0, 0, 0, 12, 3, 'full-width', '12', 0, 0, 'Hero Header', NULL, NULL, NULL, '2025-07-17 14:20:41', '2025-07-17 14:30:06', NULL),
(2, 1, 'Content', 'content', 3, 0, 3, 12, 3, 'full-width', '12', 0, 0, 'Content Section', NULL, NULL, NULL, '2025-07-17 14:43:54', '2025-07-17 14:44:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

DROP TABLE IF EXISTS `themes`;
CREATE TABLE IF NOT EXISTS `themes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `directory` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `themes_slug_unique` (`slug`),
  UNIQUE KEY `themes_directory_unique` (`directory`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `name`, `slug`, `directory`, `version`, `author`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'RealSys Default', 'default', 'default', '1.0.0', 'NPPK', 'A clean, modern blog theme with beautiful typography and responsive design', 0, '2025-06-04 21:04:41', '2025-06-04 21:06:53', NULL),
(2, 'Miata', 'miata', 'miata', '1.0.0', 'HTMLDemo.net (Adapted for RealsysCMS)', 'A political Party theme for RealsysCMS', 1, '2025-06-04 21:05:42', '2025-06-04 21:11:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','suspended','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., google, facebook, github',
  `provider_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_token` text COLLATE utf8mb4_unicode_ci,
  `provider_refresh_token` text COLLATE utf8mb4_unicode_ci,
  `password_reset_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_token_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_provider_provider_id_index` (`provider`,`provider_id`),
  KEY `users_status_index` (`status`),
  KEY `users_email_verified_at_index` (`email_verified_at`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `surname`, `last_name`, `email`, `email_verified_at`, `password`, `remember_token`, `phone_number`, `id_number`, `status`, `must_change_password`, `provider`, `provider_id`, `provider_token`, `provider_refresh_token`, `password_reset_token`, `password_reset_token_expires_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Rose', 'Mwendwa', 'Kilonzo', 'rmwendwa@test.com', NULL, '$2y$12$ace5KQMHuahZ25bzQG8.4emwI5MMoneGOGstJnqlKFyUQRspiZ/eG', NULL, '0711223344', '12345678', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-19 14:42:14', '2025-06-19 16:09:37', NULL),
(2, 'Kate', 'Mwangi', 'thuku', 'kmwangi@test.com', NULL, '$2y$12$kCE7ia83PrNg5tXs9WsifefRHfBm21J0QmghiD7PGnzvUrxtBP9oW', NULL, '0722111222', NULL, 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-20 15:19:26', '2025-06-20 15:19:26', NULL),
(3, 'James', 'Mwangi', NULL, 'jmwangi@test.com', NULL, '$2y$12$DuBvtdKbjYWRwGlbZ6om8e0FKHa.f.cP7IITy26hF9C6WScPcUNOe', NULL, '0722333444', NULL, 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-20 15:31:12', '2025-06-20 15:31:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wards`
--

DROP TABLE IF EXISTS `wards`;
CREATE TABLE IF NOT EXISTS `wards` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `constituency_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wards_name_constituency_id_unique` (`name`,`constituency_id`),
  UNIQUE KEY `wards_code_unique` (`code`),
  KEY `wards_constituency_id_foreign` (`constituency_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1449 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wards`
--

INSERT INTO `wards` (`id`, `name`, `code`, `constituency_id`, `created_at`, `updated_at`) VALUES
(1, 'Port Reitz', '010101', 1, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(2, 'Kipevu', '010102', 1, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(3, 'Airport', '010103', 1, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(4, 'Miritini', '010104', 1, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(5, 'Chaani', '010105', 1, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(6, 'Jomvu Kuu', '010201', 2, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(7, 'Magongo', '010202', 2, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(8, 'Mikindani', '010203', 2, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(9, 'Mjambere', '010301', 3, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(10, 'Junda', '010302', 3, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(11, 'Bamburi', '010303', 3, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(12, 'Mwakirunge', '010304', 3, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(13, 'Mtopanga', '010305', 3, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(14, 'Magogoni', '010306', 3, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(15, 'Shanzu', '010307', 3, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(16, 'Mtongwe', '010401', 4, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(17, 'Shika adabu', '010402', 4, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(18, 'Bofu', '010403', 4, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(19, 'Likoni', '010404', 4, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(20, 'Timbwani', '010405', 4, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(21, 'Mji wa Kale/Makadara', '010501', 5, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(22, 'Tudor', '010502', 5, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(23, 'Tononoka', '010503', 5, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(24, 'Ganjoni/Shimanzi', '010504', 5, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(25, 'Majengo', '010505', 5, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(26, 'Frere Town', '010601', 6, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(27, 'Ziwa la Ng\'ombe', '010602', 6, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(28, 'Mkomani', '010603', 6, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(29, 'Kongowea', '010604', 6, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(30, 'Ziwani/Kadzandani', '010605', 6, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(31, 'Ndavaya', '020101', 7, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(32, 'Puma', '020102', 7, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(33, 'Kinango', '020103', 7, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(34, 'Chengoni/Samburu', '020104', 7, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(35, 'Mackinon Road', '020105', 7, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(36, 'Mwavumbo', '020106', 7, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(37, 'Kasemeni', '020107', 7, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(38, 'Pongwe/Kikoneni', '020201', 8, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(39, 'Dzombo', '020202', 8, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(40, 'Vanga', '020203', 8, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(41, 'Mwereni', '020204', 8, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(42, 'Gombato Bongwe', '020301', 9, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(43, 'Ukunda', '020302', 9, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(44, 'Kinondo', '020303', 9, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(45, 'Ramisi', '020304', 9, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(46, 'Tsimba Golini', '020401', 10, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(47, 'Waa', '020402', 10, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(48, 'Tiwi', '020403', 10, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(49, 'Kubo South', '020404', 10, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(50, 'Mkongani', '020405', 10, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(51, 'Tezo', '030101', 11, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(52, 'Sokoni', '030102', 11, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(53, 'Kibarani', '030103', 11, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(54, 'Dabaso', '030104', 11, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(55, 'Matsangoni', '030105', 11, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(56, 'Watamu', '030106', 11, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(57, 'Mnarani', '030107', 11, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(58, 'Junju', '030201', 12, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(59, 'Mwarakaya', '030202', 12, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(60, 'Shimo la Tewa', '030203', 12, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(61, 'Chasimba', '030204', 12, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(62, 'Mtepeni', '030205', 12, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(63, 'Mariakani', '030301', 13, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(64, 'Kayafungo', '030302', 13, '2025-06-11 19:50:52', '2025-06-11 19:50:52'),
(65, 'Kaloleni', '030303', 13, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(66, 'Mwanamwinga', '030304', 13, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(67, 'Dungicha', '030401', 14, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(68, 'Bamba', '030402', 14, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(69, 'Jaribuni', '030403', 14, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(70, 'Sokoke', '030404', 14, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(71, 'Maarafa', '030501', 15, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(72, 'Magarini', '030502', 15, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(73, 'Gongoni', '030503', 15, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(74, 'Adu', '030504', 15, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(75, 'Garashi', '030505', 15, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(76, 'Sabaki', '030506', 15, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(77, 'Mwawesa', '030601', 16, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(78, 'Ruruma', '030602', 16, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(79, 'Jibana', '030603', 16, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(80, 'Rabai/Kisurutuni', '030604', 16, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(81, 'Jilore', '030701', 17, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(82, 'Kakuyuni', '030702', 17, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(83, 'Ganda', '030703', 17, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(84, 'Malindi Town', '030704', 17, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(85, 'Shella', '030705', 17, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(86, 'Garsen Central', '040101', 18, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(87, 'Garsen East', '040102', 18, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(88, 'Garsen North', '040103', 18, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(89, 'Garsen South', '040104', 18, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(90, 'Kipini East', '040105', 18, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(91, 'Kipini West', '040106', 18, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(92, 'Kinakomba', '040201', 19, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(93, 'Mikinduni', '040202', 19, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(94, 'Chewani', '040203', 19, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(95, 'Wayu', '040204', 19, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(96, 'Chewele', '040301', 20, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(97, 'Hirimani', '040302', 20, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(98, 'Bangale', '040303', 20, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(99, 'Madogo', '040304', 20, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(100, 'Sala', '040305', 20, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(101, 'Faza', '050101', 21, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(102, 'Kiunga', '050102', 21, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(103, 'Basuba', '050103', 21, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(104, 'Shella', '050201', 22, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(105, 'Mkomani', '050202', 22, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(106, 'Hindi', '050203', 22, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(107, 'Mkunumbi', '050204', 22, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(108, 'Hongwe', '050205', 22, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(109, 'Witu', '050206', 22, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(110, 'Bahari', '050207', 22, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(111, 'Chala', '060101', 23, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(112, 'Mahoo', '060102', 23, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(113, 'Bomani', '060103', 23, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(114, 'Mboghoni', '060104', 23, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(115, 'Mata', '060105', 23, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(116, 'Wundanyi/Mbale', '060201', 24, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(117, 'Werugha', '060202', 24, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(118, 'Wumingu/Kishushe', '060203', 24, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(119, 'Mwanda/Mgange', '060204', 24, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(120, 'Ronge', '060301', 25, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(121, 'Mwatate', '060302', 25, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(122, 'Bura', '060303', 25, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(123, 'Chawia', '060304', 25, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(124, 'Wusi/Kishamba', '060305', 25, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(125, 'Mbololo', '060401', 26, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(126, 'Kaloleni', '060402', 26, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(127, 'Sagala', '060403', 26, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(128, 'Marungu', '060404', 26, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(129, 'Kaigau', '060405', 26, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(130, 'Ngolia', '060406', 26, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(131, 'Waberi', '070101', 27, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(132, 'Galbet', '070102', 27, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(133, 'Township', '070103', 27, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(134, 'Iftin', '070104', 27, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(135, 'Balambala', '070201', 28, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(136, 'Danyere', '070202', 28, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(137, 'Jarajara', '070203', 28, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(138, 'Saka', '070204', 28, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(139, 'Sankuri', '070205', 28, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(140, 'Dertu', '070301', 29, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(141, 'Dadaab', '070302', 29, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(142, 'Labasigale', '070303', 29, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(143, 'Damajale', '070304', 29, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(144, 'Liboi', '070305', 29, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(145, 'Abakaile', '070306', 29, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(146, 'Bura', '070401', 30, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(147, 'Dekaharia', '070402', 30, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(148, 'Jarajila', '070403', 30, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(149, 'Fafi', '070404', 30, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(150, 'Nanighi', '070405', 30, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(151, 'Hulugho', '070501', 31, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(152, 'Sangailu', '070502', 31, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(153, 'Ijara', '070503', 31, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(154, 'Masalani', '070504', 31, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(155, 'Modogashe', '070601', 32, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(156, 'Bename', '070602', 32, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(157, 'Goreale', '070603', 32, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(158, 'Maalamin', '070604', 32, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(159, 'Sabena', '070605', 32, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(160, 'Baraki', '070606', 32, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(161, 'Wagbri', '080101', 33, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(162, 'Township', '080102', 33, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(163, 'Barwago', '080103', 33, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(164, 'Khorof/Harar', '080104', 33, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(165, 'Gurar', '080201', 34, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(166, 'Bute', '080202', 34, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(167, 'Korondile', '080203', 34, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(168, 'Malkagufu', '080204', 34, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(169, 'Batalu', '080205', 34, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(170, 'Danaba', '080206', 34, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(171, 'Godoma', '080207', 34, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(172, 'Benane', '080301', 35, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(173, 'Burder', '080302', 35, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(174, 'Dadaja Bulla', '080303', 35, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(175, 'Habaswein', '080304', 35, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(176, 'Lagboghol South', '080305', 35, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(177, 'Ibrahim Ure', '080306', 35, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(178, 'Arbajahan', '080401', 36, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(179, 'Hadado/Athibohol', '080402', 36, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(180, 'Ademasajide', '080403', 36, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(181, 'Ganyure', '080404', 36, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(182, 'Wagalla', '080405', 36, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(183, 'Elben', '080501', 37, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(184, 'Sarman', '080502', 37, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(185, 'Tarbaj', '080503', 37, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(186, 'Wargadud', '080504', 37, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(187, 'Eldas', '080601', 38, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(188, 'Della', '080602', 38, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(189, 'Lakoley South/Basir', '080603', 38, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(190, 'Elnur/Tula Tula', '080604', 38, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(191, 'Takaba South', '090101', 39, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(192, 'Takaba', '090102', 39, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(193, 'Lagsure', '090103', 39, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(194, 'Dandu', '090104', 39, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(195, 'Gither', '090105', 39, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(196, 'Banissa', '090201', 40, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(197, 'Derkhale', '090202', 40, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(198, 'Guba', '090203', 40, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(199, 'Malkamari', '090204', 40, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(200, 'Kiliwehiri', '090205', 40, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(201, 'Ashabito', '090301', 41, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(202, 'Guticha', '090302', 41, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(203, 'Marothile', '090303', 41, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(204, 'Rhamu', '090304', 41, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(205, 'Rhamu Dimtu', '090305', 41, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(206, 'Wargadud', '090401', 42, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(207, 'Kutulo', '090402', 42, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(208, 'Elwak South', '090403', 42, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(209, 'Elwak North', '090404', 42, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(210, 'Shimbir Fatuma', '090405', 42, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(211, 'Arabia', '090501', 43, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(212, 'Libehia', '090502', 43, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(213, 'Khalalio', '090503', 43, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(214, 'Neboi', '090504', 43, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(215, 'Township', '090505', 43, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(216, 'Sala', '090601', 44, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(217, 'Fino', '090602', 44, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(218, 'Lafey', '090603', 44, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(219, 'Warangara', '090604', 44, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(220, 'Alungo', '090605', 44, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(221, 'Loiyangalani', '100101', 45, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(222, 'Kargi/South Horr', '100102', 45, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(223, 'Korr/Ngurunit', '100103', 45, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(224, 'LogoLogo', '100104', 45, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(225, 'Laisamis', '100105', 45, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(226, 'Dukana', '100201', 46, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(227, 'Maikona', '100202', 46, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(228, 'Turbi', '100203', 46, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(229, 'North Horr', '100204', 46, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(230, 'Illeret', '100205', 46, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(231, 'Sagate/Jaldesa', '100301', 47, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(232, 'Karare', '100302', 47, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(233, 'Marsabit Central', '100303', 47, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(234, 'Butiye', '100401', 48, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(235, 'Sololo', '100402', 48, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(236, 'Heillu/Manyatta', '100403', 48, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(237, 'Golbo', '100404', 48, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(238, 'Moyale Township', '100405', 48, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(239, 'Uran', '100406', 48, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(240, 'Obbu', '100407', 48, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(241, 'Wabera', '110101', 49, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(242, 'Bulla Pesa', '110102', 49, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(243, 'Chari', '110103', 49, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(244, 'Cherab', '110104', 49, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(245, 'Ngare Mara', '110105', 49, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(246, 'Burat', '110106', 49, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(247, 'Oldo/Nyiro', '110107', 49, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(248, 'Garba Tulla', '110201', 50, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(249, 'Kina', '110202', 50, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(250, 'Sericho', '110203', 50, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(251, 'Timau', '120101', 51, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(252, 'Kisima', '120102', 51, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(253, 'Kiirua/Naari', '120103', 51, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(254, 'Ruiri/Rwarera', '120104', 51, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(255, 'Mwanganthia', '120201', 52, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(256, 'Abothuguchi Central', '120202', 52, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(257, 'Abothuguchi West', '120203', 52, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(258, 'Kiagu', '120204', 52, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(259, 'Kibirichia', '120205', 52, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(260, 'Akirang\'ondu', '120301', 53, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(261, 'Athiru', '120302', 53, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(262, 'Ruujine', '120303', 53, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(263, 'Igembe East Njia', '120304', 53, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(264, 'Kangeta', '120305', 53, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(265, 'Maua', '120401', 54, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(266, 'Kegoi/Antubochiu', '120402', 54, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(267, 'Athiru', '120403', 54, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(268, 'Gaiti', '120404', 54, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(269, 'Akachiu', '120405', 54, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(270, 'Kanuni', '120406', 54, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(271, 'Antuambui', '120501', 55, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(272, 'Ntunene', '120502', 55, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(273, 'Antubetwe Kiongo', '120503', 55, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(274, 'Naathui', '120504', 55, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(275, 'Amwathi', '120505', 55, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(276, 'Athwana', '120601', 56, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(277, 'Akithi', '120602', 56, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(278, 'Kianjai', '120603', 56, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(279, 'Nkomo', '120604', 56, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(280, 'Mbeu', '120605', 56, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(281, 'Thangatha', '120701', 57, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(282, 'Mikinduri', '120702', 57, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(283, 'Kiguchwa', '120703', 57, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(284, 'Mithara', '120704', 57, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(285, 'Karama', '120705', 57, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(286, 'Municipality', '120801', 58, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(287, 'Ntima East', '120802', 58, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(288, 'Ntima West', '120803', 58, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(289, 'Nyaki West', '120804', 58, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(290, 'Nyaki East', '120805', 58, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(291, 'Mitunguu', '120901', 59, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(292, 'Igoji East', '120902', 59, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(293, 'Igoji West', '120903', 59, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(294, 'Abogeta East', '120904', 59, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(295, 'Abogeta West', '120905', 59, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(296, 'Nkuene', '120906', 59, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(297, 'Gatunga', '130101', 60, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(298, 'Mukothima', '130102', 60, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(299, 'Nkondi', '130103', 60, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(300, 'Chiakariga', '130104', 60, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(301, 'Marimanti', '130105', 60, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(302, 'Mariani', '130201', 61, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(303, 'Karingani', '130202', 61, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(304, 'Magumoni', '130203', 61, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(305, 'Mugwe', '130204', 61, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(306, 'Igambang\'ombe', '130205', 61, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(307, 'Mitheru', '130301', 62, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(308, 'Muthambi', '130302', 62, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(309, 'Mwimbi', '130303', 62, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(310, 'Ganga', '130304', 62, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(311, 'Chogoria', '130305', 62, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(312, 'Ruguru/Ngandori', '140101', 63, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(313, 'Kithimu', '140102', 63, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(314, 'Nginda', '140103', 63, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(315, 'Mbeti North', '140104', 63, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(316, 'Kirimari', '140105', 63, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(317, 'Gaturi South', '140106', 63, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(318, 'Gaturi North', '140201', 64, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(319, 'Kagaari South', '140202', 64, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(320, 'Kagaari North', '140203', 64, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(321, 'Central Ward', '140204', 64, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(322, 'Kyeni North', '140205', 64, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(323, 'Kyeni South', '140206', 64, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(324, 'Nthawa', '140301', 65, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(325, 'Muminji', '140302', 65, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(326, 'Evurore', '140303', 65, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(327, 'Mwea', '140401', 66, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(328, 'Amakim', '140402', 66, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(329, 'Mbeti South', '140403', 66, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(330, 'Mavuria', '140404', 66, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(331, 'Kiambere', '140405', 66, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(332, 'Mutonguni', '150101', 67, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(333, 'Kauwi', '150102', 67, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(334, 'Matinyani', '150103', 67, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(335, 'Kwa Mutonga/Kithum Ula', '150104', 67, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(336, 'Miambani', '150201', 68, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(337, 'Township Kyangwithya West', '150202', 68, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(338, 'Mulango', '150203', 68, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(339, 'Kyangwithya East', '150204', 68, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(340, 'Kisasi', '150301', 69, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(341, 'Mbitini', '150302', 69, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(342, 'Kwavonza/Yatta', '150303', 69, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(343, 'Kanyangi', '150304', 69, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(344, 'Ikana/Kyantune', '150401', 70, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(345, 'Mutomo', '150402', 70, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(346, 'Mutha', '150403', 70, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(347, 'Ikutha', '150404', 70, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(348, 'Kanziko', '150405', 70, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(349, 'Athi', '150406', 70, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(350, 'Zombe/Mwitika', '150501', 71, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(351, 'Nzambani', '150502', 71, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(352, 'Chuluni', '150503', 71, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(353, 'Voo/Kyamatu', '150504', 71, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(354, 'Endau/Malalani', '150505', 71, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(355, 'Mutito/Kaliku', '150506', 71, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(356, 'Ngomeni', '150601', 72, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(357, 'Kyuso', '150602', 72, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(358, 'Mumoni', '150603', 72, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(359, 'Tseikuru', '150604', 72, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(360, 'Tharaka', '150605', 72, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(361, 'Kyome/Thaana', '150701', 73, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(362, 'Nguutani', '150702', 73, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(363, 'Migwani', '150703', 73, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(364, 'Kiomo/Kyethani', '150704', 73, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(365, 'Central', '150801', 74, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(366, 'Kivou', '150802', 74, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(367, 'Nguni', '150803', 74, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(368, 'Mui', '150804', 74, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(369, 'Waita', '150805', 74, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(370, 'Kivaa', '160101', 75, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(371, 'Masinga', '160102', 75, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(372, 'Central', '160103', 75, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(373, 'Ekalakala', '160104', 75, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(374, 'Muthesya', '160105', 75, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(375, 'Ndithini', '160106', 75, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(376, 'Ndalani', '160201', 76, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(377, 'Matuu', '160202', 76, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(378, 'Kithimani', '160203', 76, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(379, 'Ikomba', '160204', 76, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(380, 'Katangi', '160205', 76, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(381, 'Tala', '160301', 77, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(382, 'Matungulu North', '160302', 77, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(383, 'Matungulu East', '160303', 77, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(384, 'Matungulu West', '160304', 77, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(385, 'Kyeleni', '160305', 77, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(386, 'Kangundo North', '160401', 78, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(387, 'Kangundo Central', '160402', 78, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(388, 'Kangundo East', '160403', 78, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(389, 'Kangundo West', '160404', 78, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(390, 'Mbiuni', '160501', 79, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(391, 'Makutano/Mwala', '160502', 79, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(392, 'Masii', '160503', 79, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(393, 'Muthetheni', '160504', 79, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(394, 'Wamunyu', '160505', 79, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(395, 'Kibauni', '160506', 79, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(396, 'Mitaboni', '160601', 80, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(397, 'Kathiani Central', '160602', 80, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(398, 'Upper Kaewa/Iveti', '160603', 80, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(399, 'Lower Kaewa/Kaani', '160604', 80, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(400, 'Kalama', '160701', 81, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(401, 'Mua', '160702', 81, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(402, 'Mutitini', '160703', 81, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(403, 'Machakos Central', '160704', 81, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(404, 'Mumbuni North', '160705', 81, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(405, 'Muvuti/Kiima-Kimwe', '160706', 81, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(406, 'Kola', '160707', 81, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(407, 'Athi River', '160801', 82, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(408, 'Kinanie', '160802', 82, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(409, 'Muthwani', '160803', 82, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(410, 'Syokimau/Mulolongo', '160804', 82, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(411, 'Tulimani', '170101', 83, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(412, 'Mbooni', '170102', 83, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(413, 'Kithungo/Kitundu', '170103', 83, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(414, 'Kiteta/Kisau', '170104', 83, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(415, 'Waia-Kako', '170105', 83, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(416, 'Kalawa', '170106', 83, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(417, 'Ukia', '170201', 84, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(418, 'Kee', '170202', 84, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(419, 'Kilungu', '170203', 84, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(420, 'Ilima', '170204', 84, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(421, 'Wote', '170301', 85, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(422, 'Muvau/Kikuumini', '170302', 85, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(423, 'Mavindini', '170303', 85, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(424, 'Kitise/Kithuki', '170304', 85, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(425, 'Kathonzweni', '170305', 85, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(426, 'Nzau/Kilili/Kalamba', '170306', 85, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(427, 'Mbitini', '170307', 85, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(428, 'Kasikeu', '170401', 86, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(429, 'Mukaa', '170402', 86, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(430, 'Kiima Kiu/Kalanzoni', '170403', 86, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(431, 'Masongaleni', '170501', 87, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(432, 'Mtito Andei', '170502', 87, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(433, 'Thange', '170503', 87, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(434, 'Ivingoni/Nzambani', '170504', 87, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(435, 'Makindu', '170601', 88, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(436, 'Nguumo', '170602', 88, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(437, 'Kikumbulyu North', '170603', 88, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(438, 'Kimumbulyu South', '170604', 88, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(439, 'Nguu/Masumba', '170605', 88, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(440, 'Emali/Mulala', '170606', 88, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(441, 'Engineer', '180101', 89, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(442, 'Gathara', '180102', 89, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(443, 'North Kinangop', '180103', 89, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(444, 'Murungaru', '180104', 89, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(445, 'Njabini/Kiburu', '180105', 89, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(446, 'Nyakio', '180106', 89, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(447, 'Githabai', '180107', 89, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(448, 'Magumu', '180108', 89, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(449, 'Wanjohi', '180201', 90, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(450, 'Kipipiri', '180202', 90, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(451, 'Geta', '180203', 90, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(452, 'Githioro', '180204', 90, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(453, 'Gathanji', '180301', 91, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(454, 'Gatima', '180302', 91, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(455, 'Weru', '180303', 91, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(456, 'Charagita', '180304', 91, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(457, 'Leshau/Pondo', '180401', 92, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(458, 'Kiriita', '180402', 92, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(459, 'Central', '180403', 92, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(460, 'Shamata', '180404', 92, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(461, 'Karau', '180501', 93, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(462, 'Kanjuiri Range', '180502', 93, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(463, 'Mirangine', '180503', 93, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(464, 'Kaimbaga', '180504', 93, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(465, 'Rurii', '180505', 93, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(466, 'Ruguru', '190101', 94, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(467, 'Magutu', '190102', 94, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(468, 'Iriani', '190103', 94, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(469, 'Konyu', '190104', 94, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(470, 'Kirimukuyu', '190105', 94, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(471, 'Karatina Town', '190106', 94, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(472, 'Mahiga', '190201', 95, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(473, 'Iria-Ini', '190202', 95, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(474, 'Chinga', '190203', 95, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(475, 'Karima', '190204', 95, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(476, 'Dedan Kimathi', '190501', 96, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(477, 'Wamagana', '190502', 96, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(478, 'Aguthi-Gaaki', '190503', 96, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(479, 'Gikondi', '190401', 97, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(480, 'Rugi', '190402', 97, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(481, 'Mukurwe-Ini West', '190403', 97, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(482, 'Mukurwe-Ini Central', '190404', 97, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(483, 'Kiganjo/Mathari', '190601', 98, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(484, 'Rware', '190602', 98, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(485, 'Gatitu/Muruguru', '190603', 98, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(486, 'Ruring’u', '190604', 98, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(487, 'Kamakwa/Mukaro', '190605', 98, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(488, 'Mweiga', '190701', 99, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(489, 'Naromoro Kiamthaga', '190702', 99, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(490, 'Mwiyogo/Endara Sha', '190703', 99, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(491, 'Mugunda', '190704', 99, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(492, 'Gatarakwa', '190705', 99, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(493, 'Thegu River', '190706', 99, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(494, 'Kabaru', '190707', 99, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(495, 'Gakawa', '190708', 99, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(496, 'Mutira', '200101', 100, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(497, 'Kanyekini', '200102', 100, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(498, 'Kerugoya', '200103', 100, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(499, 'Inoi', '200104', 100, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(500, 'Mutithi', '200201', 101, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(501, 'Kangai', '200202', 101, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(502, 'Wamumu', '200203', 101, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(503, 'Nyangati', '200204', 101, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(504, 'Murindiko', '200205', 101, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(505, 'Gathigiriri', '200206', 101, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(506, 'Teberer', '200207', 101, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(507, 'Thiba', '200208', 101, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(508, 'Kabare Baragwi', '200301', 102, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(509, 'Njukiini', '200302', 102, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(510, 'Ngariama', '200303', 102, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(511, 'Karumandi', '200304', 102, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(512, 'Mukure', '200401', 103, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(513, 'Kiine', '200402', 103, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(514, 'Kariti', '200403', 103, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(515, 'Ithanga', '210101', 104, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(516, 'Kakuzi/Mitubiri', '210102', 104, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(517, 'Mugumo-Ini', '210103', 104, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(518, 'Kihumbu-Ini', '210104', 104, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(519, 'Gatanga', '210105', 104, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(520, 'Kariara', '210106', 104, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(521, 'Ng’ararii', '210201', 105, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(522, 'Muruka', '210202', 105, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(523, 'Kangundu-Ini', '210203', 105, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(524, 'Gaichanjiru', '210204', 105, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(525, 'Ithiru', '210205', 105, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(526, 'Ruchu', '210206', 105, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(527, 'Kahumbu', '210301', 106, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(528, 'Muthithi', '210302', 106, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(529, 'Kigumo', '210303', 106, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(530, 'Kangari', '210304', 106, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(531, 'Kinyona', '210305', 106, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(532, 'Gituhi', '210401', 107, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(533, 'Kiru', '210402', 107, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(534, 'Kamacharia', '210403', 107, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(535, 'Wangu', '210501', 108, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(536, 'Mugoiri', '210502', 108, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(537, 'Mbiri', '210503', 108, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(538, 'Township', '210504', 108, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(539, 'Murarandia', '210505', 108, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(540, 'Gaturi', '210506', 108, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(541, 'Kanyenya-Ini', '210601', 109, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(542, 'Muguru', '210602', 109, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(543, 'Rwathia', '210603', 109, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(544, 'Kimorori/Wempa', '210701', 110, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(545, 'Makuyu', '210702', 110, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(546, 'Kambiti', '210703', 110, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(547, 'Kamahuha', '210704', 110, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(548, 'Ichagaki', '210705', 110, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(549, 'Nginda', '210706', 110, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(550, 'Gituamba', '220101', 111, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(551, 'Githobokoni', '220102', 111, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(552, 'Chania', '220103', 111, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(553, 'Mang’u', '220104', 111, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(554, 'Kiamwangi', '220201', 112, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(555, 'Kiganjo', '220202', 112, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(556, 'Ndarugu', '220203', 112, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(557, 'Ngenda', '220204', 112, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(558, 'Githunguri', '220301', 113, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(559, 'Githiga', '220302', 113, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(560, 'Ikinu', '220303', 113, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(561, 'Ngewa', '220304', 113, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(562, 'Komothai', '220305', 113, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(563, 'Murera', '220401', 114, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(564, 'Theta', '220402', 114, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(565, 'Juja', '220403', 114, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(566, 'Witeithie', '220404', 114, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(567, 'Kalimoni', '220405', 114, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(568, 'Gitaru', '220501', 115, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(569, 'Muguga', '220502', 115, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(570, 'Nyathuna', '220503', 115, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(571, 'Kabete', '220504', 115, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(572, 'Uthiru', '220505', 115, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(573, 'Cianda', '220601', 116, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(574, 'Karuiri', '220602', 116, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(575, 'Ndenderu', '220603', 116, '2025-06-11 19:50:53', '2025-06-11 19:50:53'),
(576, 'Muchatha', '220604', 116, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(577, 'Kihara', '220605', 116, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(578, 'Ting’gang’a', '220701', 117, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(579, 'Ndumberi', '220702', 117, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(580, 'Riabai', '220703', 117, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(581, 'Township', '220704', 117, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(582, 'Bibirioni', '220801', 118, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(583, 'Limuru Central', '220802', 118, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(584, 'Ndeiya', '220803', 118, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(585, 'Limuru East', '220804', 118, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(586, 'Ngecha Tigoni', '220805', 118, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(587, 'Karai', '220901', 119, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(588, 'Nachu', '220902', 119, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(589, 'Sigona', '220903', 119, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(590, 'Kikuyu', '220904', 119, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(591, 'Kinoo', '220905', 119, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(592, 'Kijabe', '221001', 120, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(593, 'Nyanduma', '221002', 120, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(594, 'Kamburu', '221003', 120, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(595, 'Lari/Kirenga', '221004', 120, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(596, 'Gitothua', '221101', 121, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(597, 'Biashara', '221102', 121, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(598, 'Gatongora', '221103', 121, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(599, 'Kahawa Sukari', '221104', 121, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(600, 'Kahawa Wendani', '221105', 121, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(601, 'Kiuu', '221106', 121, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(602, 'Mwiki', '221107', 121, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(603, 'Mwihoko', '221108', 121, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(604, 'Township', '221201', 122, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(605, 'Kamenu', '221202', 122, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(606, 'Hospital', '221203', 122, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(607, 'Gatuanyaga', '221204', 122, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(608, 'Ngoliba', '221205', 122, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(609, 'Kerio Delta', '230101', 123, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(610, 'Kang’atotha', '230102', 123, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(611, 'Kalokol', '230103', 123, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(612, 'Lodwar Township', '230104', 123, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(613, 'Kanamkemer', '230105', 123, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(614, 'Kapedo/Napeito', '230201', 124, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(615, 'Katilia', '230202', 124, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(616, 'Lokori/Kochodin', '230203', 124, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(617, 'Kaeris', '230301', 125, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(618, 'Lake zone', '230302', 125, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(619, 'Lapur', '230303', 125, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(620, 'Kaaleng/kaikor', '230304', 125, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(621, 'Kibish', '230305', 125, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(622, 'Nakalale', '230306', 125, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(623, 'Kaputir', '230401', 126, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(624, 'Katilu', '230402', 126, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(625, 'Lobokat', '230403', 126, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(626, 'Kalapata', '230404', 126, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(627, 'Lokichar', '230405', 126, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(628, 'Kakuma', '230501', 127, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(629, 'Lopur', '230502', 127, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(630, 'Letea', '230503', 127, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(631, 'Songot', '230504', 127, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(632, 'Kalobeyei', '230505', 127, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(633, 'Lokichoggio', '230506', 127, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(634, 'Nanaam', '230507', 127, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(635, 'Kotaruk/Lobei', '230601', 128, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(636, 'Turkwel', '230602', 128, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(637, 'Loima', '230603', 128, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(638, 'Lokiriama/Loren Gippi', '230604', 128, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(639, 'Riwo', '240101', 129, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(640, 'Kapenguria', '240102', 129, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(641, 'Mnagei', '240103', 129, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(642, 'Siyoi', '240104', 129, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(643, 'Endugh', '240105', 129, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(644, 'Sook', '240106', 129, '2025-06-11 19:50:54', '2025-06-11 19:50:54');
INSERT INTO `wards` (`id`, `name`, `code`, `constituency_id`, `created_at`, `updated_at`) VALUES
(645, 'Sekerr', '240201', 130, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(646, 'Masool', '240202', 130, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(647, 'Lomut', '240203', 130, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(648, 'Weiwei', '240204', 130, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(649, 'Suam', '240301', 131, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(650, 'Kodich', '240302', 131, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(651, 'Kasei', '240303', 131, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(652, 'Kapchok', '240304', 131, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(653, 'Kiwawa', '240305', 131, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(654, 'Alale', '240306', 131, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(655, 'Chepareria', '240401', 132, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(656, 'Batei', '240402', 132, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(657, 'Lelan', '240403', 132, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(658, 'Tapach', '240404', 132, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(659, 'Waso', '250101', 133, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(660, 'Wamba West', '250102', 133, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(661, 'Wamba East', '250103', 133, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(662, 'Wamba North', '250104', 133, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(663, 'El-Barta', '250201', 134, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(664, 'Nachola', '250202', 134, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(665, 'Ndoto', '250203', 134, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(666, 'Nyiro', '250204', 134, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(667, 'Angata Nanyokie', '250205', 134, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(668, 'Baawa', '250206', 134, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(669, 'Lodokejek', '250301', 135, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(670, 'Suguta Marmar', '250302', 135, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(671, 'Maralal', '250303', 135, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(672, 'Loosuk', '250304', 135, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(673, 'Poro', '250305', 135, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(674, 'Sinyerere', '260101', 136, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(675, 'Makutano', '260102', 136, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(676, 'Kaplamai', '260103', 136, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(677, 'Motosiet', '260104', 136, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(678, 'Cherangany/Suwerwa', '260105', 136, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(679, 'Chepsiro/Kiptoror', '260106', 136, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(680, 'Sitatunga', '260107', 136, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(681, 'Kapomboi', '260201', 137, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(682, 'Kwanza', '260202', 137, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(683, 'Keiyo', '260203', 137, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(684, 'Bidii', '260204', 137, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(685, 'Chepchoina', '260301', 138, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(686, 'Endebess', '260302', 138, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(687, 'Matumbei', '260303', 138, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(688, 'Kinyoro', '260401', 139, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(689, 'Matisi', '260402', 139, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(690, 'Tuwani', '260403', 139, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(691, 'Saboti', '260404', 139, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(692, 'Machewa', '260405', 139, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(693, 'Kiminini', '260501', 140, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(694, 'Waitaluk', '260502', 140, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(695, 'Sirende', '260503', 140, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(696, 'Hospital', '260504', 140, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(697, 'Sikhendu', '260505', 140, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(698, 'Nabiswa', '260506', 140, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(699, 'Kapsoya', '270101', 141, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(700, 'Kaptagat', '270102', 141, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(701, 'Ainabkoi/Olare', '270103', 141, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(702, 'Simat/Kapseret', '270201', 142, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(703, 'Kipkenyo', '270202', 142, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(704, 'Ngeria', '270203', 142, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(705, 'Megun', '270204', 142, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(706, 'Langas', '270205', 142, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(707, 'Racecourse', '270301', 143, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(708, 'Cheptiret/Kipchamo', '270302', 143, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(709, 'Tulwet/Chuiyat', '270303', 143, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(710, 'Tarakwa', '270304', 143, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(711, 'Tembelio', '270401', 144, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(712, 'Sergoit', '270402', 144, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(713, 'Karuna/Meibeki', '270403', 144, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(714, 'Moiben', '270404', 144, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(715, 'Kimumu', '270405', 144, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(716, 'Moi’s Bridge', '270501', 145, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(717, 'Kapkures', '270502', 145, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(718, 'Ziwa', '270503', 145, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(719, 'Segero/Barsombe', '270504', 145, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(720, 'Kipsom Ba', '270505', 145, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(721, 'Soy', '270506', 145, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(722, 'Kuinet/Kapsuswa', '270507', 145, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(723, 'Ngenyilel', '270601', 146, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(724, 'Tapsagoi', '270602', 146, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(725, 'Kamagut', '270603', 146, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(726, 'Kiplombe', '270604', 146, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(727, 'Kapsaos', '270605', 146, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(728, 'Huruma', '270606', 146, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(729, 'Emsoo', '280101', 147, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(730, 'Kamariny', '280102', 147, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(731, 'Kapchemutwa', '280103', 147, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(732, 'Tambach', '280104', 147, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(733, 'Kaptarakwa', '280201', 148, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(734, 'Chepkorio', '280202', 148, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(735, 'Soy North', '280203', 148, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(736, 'Soy South', '280204', 148, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(737, 'Kabiemit', '280205', 148, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(738, 'Metkei', '280206', 148, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(739, 'Kapyego', '280301', 149, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(740, 'Sambirir', '280302', 149, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(741, 'Endo', '280303', 149, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(742, 'Embobut / Embulot', '280304', 149, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(743, 'Kapsowar', '280401', 150, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(744, 'Lelan', '280402', 150, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(745, 'Sengwer', '280403', 150, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(746, 'Cherang’any/Chebororwa', '280404', 150, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(747, 'Moiben/Kuserwo', '280405', 150, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(748, 'Arror', '280406', 150, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(749, 'Kabwareng', '290101', 151, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(750, 'Terik', '290102', 151, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(751, 'Kemeloi-Maraba', '290103', 151, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(752, 'Kobujoi', '290104', 151, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(753, 'Kaptumo-Kaboi', '290105', 151, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(754, 'Koyo-Ndurio', '290106', 151, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(755, 'Chemundu/Kapng’etuny', '290201', 152, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(756, 'Kosirai', '290202', 152, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(757, 'Lelmokwo/Ngechek', '290203', 152, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(758, 'Kaptel/Kamoiywo', '290204', 152, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(759, 'Kiptuya', '290205', 152, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(760, 'Chepkumia', '290301', 153, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(761, 'Kapkangani', '290302', 153, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(762, 'Kapsabet', '290303', 153, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(763, 'Kilibwoni', '290304', 153, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(764, 'Chepterwai', '290401', 154, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(765, 'Kipkaren', '290402', 154, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(766, 'Kurgung/ Surungai', '290403', 154, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(767, 'Kabiyet', '290404', 154, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(768, 'Ndalat', '290405', 154, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(769, 'Kabisaga', '290406', 154, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(770, 'Sangalo/Kebulonik', '290407', 154, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(771, 'Nandi Hills', '290501', 155, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(772, 'Chepkunyuk', '290502', 155, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(773, 'Ol’lessos', '290503', 155, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(774, 'Kapchorua', '290504', 155, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(775, 'Songhor/Soba', '290601', 156, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(776, 'Tindiret', '290602', 156, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(777, 'Chemelil/Chemase', '290603', 156, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(778, 'Kapsimotwo', '290604', 156, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(779, 'Kabarnet', '300101', 157, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(780, 'Sacho', '300102', 157, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(781, 'Tenges', '300103', 157, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(782, 'Ewalel/Chapcha', '300104', 157, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(783, 'Kapropita', '300105', 157, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(784, 'Barwessa', '300201', 158, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(785, 'Kabartonjo', '300202', 158, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(786, 'Saimo/Kipsaraman', '300203', 158, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(787, 'Saimo/Soi', '300204', 158, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(788, 'Bartabwa', '300205', 158, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(789, 'Marigat', '300301', 159, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(790, 'Ilchamus', '300302', 159, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(791, 'Mochongoi', '300303', 159, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(792, 'Mukutani', '300304', 159, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(793, 'Lembus', '300401', 160, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(794, 'Lembus Kwen', '300402', 160, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(795, 'Ravine', '300403', 160, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(796, 'Mumberes/Maji Mazuri', '300404', 160, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(797, 'Lembus /Pekerra', '300405', 160, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(798, 'Mogotio', '300501', 161, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(799, 'Emining', '300502', 161, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(800, 'Kisanana', '300503', 161, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(801, 'Tirioko', '300601', 162, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(802, 'Kolowa', '300602', 162, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(803, 'Ribkwo', '300603', 162, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(804, 'Silale', '300604', 162, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(805, 'Loiyamorock', '300605', 162, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(806, 'Tangulbei/Korossi', '300606', 162, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(807, 'Churo/Amaya', '300607', 162, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(808, 'Sosian', '310101', 163, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(809, 'Segera', '310102', 163, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(810, 'Mugogodo West', '310103', 163, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(811, 'Mugogodo East', '310104', 163, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(812, 'Ngobit', '310201', 164, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(813, 'Tigithi', '310202', 164, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(814, 'Thingithu', '310203', 164, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(815, 'Nanyuki', '310204', 164, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(816, 'Umande', '310205', 164, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(817, 'Ol-Moran', '310301', 165, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(818, 'Rumuruti', '310302', 165, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(819, 'Township', '310303', 165, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(820, 'Githiga', '310304', 165, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(821, 'Marmanet', '310305', 165, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(822, 'Igwamiti Salama', '310306', 165, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(823, 'Biashara', '320101', 166, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(824, 'Kivumbini', '320102', 166, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(825, 'Flamingo', '320103', 166, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(826, 'Menengai', '320104', 166, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(827, 'Nakuru East', '320105', 166, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(828, 'Barut', '320201', 167, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(829, 'London', '320202', 167, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(830, 'Kaptembwo', '320203', 167, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(831, 'Kapkures', '320204', 167, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(832, 'Rhoda', '320205', 167, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(833, 'Shaabab', '320206', 167, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(834, 'Mau Narok', '320301', 168, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(835, 'Mauche', '320302', 168, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(836, 'Kihingo', '320303', 168, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(837, 'Nessuit', '320304', 168, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(838, 'Lare', '320305', 168, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(839, 'Njoro', '320306', 168, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(840, 'Mariashoni', '320401', 169, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(841, 'Elburgon', '320402', 169, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(842, 'Turi', '320403', 169, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(843, 'Molo', '320404', 169, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(844, 'Gilgil', '320501', 170, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(845, 'Elementaita', '320502', 170, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(846, 'Mbaruk/Eburu', '320503', 170, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(847, 'Malewa West', '320504', 170, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(848, 'Murindati', '320505', 170, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(849, 'Biashara', '320601', 171, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(850, 'Hells Gate', '320602', 171, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(851, 'Lake View', '320603', 171, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(852, 'Maiella', '320604', 171, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(853, 'Mai Mahiu', '320605', 171, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(854, 'Olkaria', '320606', 171, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(855, 'Naivasha East', '320607', 171, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(856, 'Viwandani', '320608', 171, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(857, 'Kiptororo', '320701', 172, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(858, 'Nyota', '320702', 172, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(859, 'Sirikwa', '320703', 172, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(860, 'Kamara', '320704', 172, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(861, 'Amalo', '320801', 173, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(862, 'Keringet', '320802', 173, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(863, 'Kiptagich', '320803', 173, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(864, 'Tinet', '320804', 173, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(865, 'Dundori', '320901', 174, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(866, 'Kabatini', '320902', 174, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(867, 'Kiamaina', '320903', 174, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(868, 'Lanet/Umoja', '320904', 174, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(869, 'Bahati', '320905', 174, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(870, 'Menengai West', '321001', 175, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(871, 'Soin', '321002', 175, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(872, 'Visoi', '321003', 175, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(873, 'Mosop', '321004', 175, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(874, 'Solai', '321005', 175, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(875, 'Subukia', '321101', 176, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(876, 'Waseges', '321102', 176, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(877, 'Kabazi', '321103', 176, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(878, 'Olpusimoru', '330101', 177, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(879, 'Olokurto', '330102', 177, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(880, 'Narok Town', '330103', 177, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(881, 'Nkareta\'Olorropil', '330104', 177, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(882, 'Melili', '330105', 177, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(883, 'Majimoto/Naroos', '330201', 178, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(884, 'Uraololulung’a', '330202', 178, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(885, 'Melelo', '330203', 178, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(886, 'Loita', '330204', 178, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(887, 'Sogoo', '330205', 178, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(888, 'Sagamian', '330206', 178, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(889, 'Mosiro', '330301', 179, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(890, 'Ildamat', '330302', 179, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(891, 'Keekonyokie', '330303', 179, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(892, 'Suswa', '330304', 179, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(893, 'Ilmotiok', '330401', 180, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(894, 'Mara', '330402', 180, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(895, 'Siana', '330403', 180, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(896, 'Naikarra', '330404', 180, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(897, 'Kilgoris Central', '330501', 181, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(898, 'Keyian', '330502', 181, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(899, 'Angata Barikoi', '330503', 181, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(900, 'Shankoe', '330504', 181, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(901, 'Kimintet', '330505', 181, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(902, 'Lolgorian', '330506', 181, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(903, 'Ilkerin', '330601', 182, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(904, 'Ololmasani', '330602', 182, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(905, 'Mogondo', '330603', 182, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(906, 'Kapsasian', '330604', 182, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(907, 'Purko', '340101', 183, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(908, 'Ildamat', '340102', 183, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(909, 'Dalalekutuk', '340103', 183, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(910, 'Matapato North', '340104', 183, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(911, 'Matapato South', '340105', 183, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(912, 'Kaputiei North', '340201', 184, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(913, 'Kitengela', '340202', 184, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(914, 'Oloosirkon/Sholinke', '340203', 184, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(915, 'Kenyawa-Poka', '340204', 184, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(916, 'Imaroro', '340205', 184, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(917, 'Olkeri', '340301', 185, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(918, 'Ongata Rongai', '340302', 185, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(919, 'Nkaimurunya', '340303', 185, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(920, 'Oloolua', '340304', 185, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(921, 'Ngong', '340305', 185, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(922, 'Keekonyokie', '340401', 186, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(923, 'Iloodokilani', '340402', 186, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(924, 'Magadi', '340403', 186, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(925, 'Ewuaso Oonkidong’i', '340404', 186, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(926, 'Mosiro', '340405', 186, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(927, 'Entonet/Lenkisi', '340501', 187, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(928, 'Mbirikani/Eselen', '340502', 187, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(929, 'Keikuku', '340503', 187, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(930, 'Rombo', '340504', 187, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(931, 'Kimana', '340505', 187, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(932, 'Kapsoit', '350101', 188, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(933, 'Ainamoi', '350102', 188, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(934, 'Kipchebor', '350103', 188, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(935, 'Kapkugerwet', '350104', 188, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(936, 'Kipchimchim', '350105', 188, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(937, 'Kapsaos', '350106', 188, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(938, 'Waldai', '350201', 189, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(939, 'Kabianga', '350202', 189, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(940, 'Cheptororiet/Seretut', '350203', 189, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(941, 'Chaik', '350204', 189, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(942, 'Kapsuser', '350205', 189, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(943, 'Kisiara', '350301', 190, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(944, 'Tebesonik', '350302', 190, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(945, 'Cheboin', '350303', 190, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(946, 'Chemosot', '350304', 190, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(947, 'Litein', '350305', 190, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(948, 'Cheplanget', '350306', 190, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(949, 'Kapkatet', '350307', 190, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(950, 'Londiani', '350401', 191, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(951, 'Kedowa/Kimugul', '350402', 191, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(952, 'Chepseon', '350403', 191, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(953, 'Tendeno/Sorget', '350404', 191, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(954, 'Kunyak', '350501', 192, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(955, 'Kamasian', '350502', 192, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(956, 'Kipkelion', '350503', 192, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(957, 'Chilchila', '350504', 192, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(958, 'Sigowet', '350601', 193, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(959, 'Kaplelartet', '350602', 193, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(960, 'Soliat', '350603', 193, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(961, 'Soin', '350604', 193, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(962, 'Ndanai/Abosi', '360101', 194, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(963, 'Chemagel', '360102', 194, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(964, 'Kipsonoi', '360103', 194, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(965, 'Apletundo', '360104', 194, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(966, 'Rongena/Manare T', '360105', 194, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(967, 'Silibwet Township', '360201', 195, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(968, 'Ndaraweta', '360202', 195, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(969, 'Singorwet', '360203', 195, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(970, 'Chesoen', '360204', 195, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(971, 'Mutarakwa', '360205', 195, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(972, 'Merigi', '360301', 196, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(973, 'Kembu', '360302', 196, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(974, 'Longisa', '360303', 196, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(975, 'Kipreres', '360304', 196, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(976, 'Chemaner', '360305', 196, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(977, 'Kong’asis', '360401', 197, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(978, 'Nyangores', '360402', 197, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(979, 'Sigor', '360403', 197, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(980, 'Chebunyo', '360404', 197, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(981, 'Siongiroi', '360405', 197, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(982, 'Chepchabas', '360501', 198, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(983, 'Kimulot', '360502', 198, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(984, 'Mogogosiek', '360503', 198, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(985, 'Boito', '360504', 198, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(986, 'Embomos', '360505', 198, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(987, 'Marama West', '370101', 199, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(988, 'Marama Central', '370102', 199, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(989, 'Marenyo-Shianda', '370103', 199, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(990, 'Maram North', '370104', 199, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(991, 'Marama South', '370105', 199, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(992, 'Idakho South', '370201', 200, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(993, 'Idakho East', '370202', 200, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(994, 'Idakho North', '370203', 200, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(995, 'Idakho Central', '370204', 200, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(996, 'Kisa North', '370301', 201, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(997, 'Kisa East', '370302', 201, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(998, 'Kisa West', '370303', 201, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(999, 'Kisa Central', '370304', 201, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1000, 'Butsotso East', '370401', 202, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1001, 'Butsotso South', '370402', 202, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1002, 'Butsotso Central', '370403', 202, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1003, 'Sheywe', '370404', 202, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1004, 'Mahiakalo', '370405', 202, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1005, 'Shirere', '370406', 202, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1006, 'Likuyani', '370501', 203, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1007, 'Sango', '370502', 203, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1008, 'Kongoni', '370503', 203, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1009, 'Nzoia', '370504', 203, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1010, 'Sinoko', '370505', 203, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1011, 'West Kabras', '370601', 204, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1012, 'Chemuche East', '370602', 204, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1013, 'Kabras', '370603', 204, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1014, 'Butali/Chegulo', '370604', 204, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1015, 'Manda-Shivanga', '370605', 204, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1016, 'Shirugu-Mugai', '370606', 204, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1017, 'South Kabras', '370607', 204, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1018, 'Koyonzo', '370701', 205, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1019, 'Kholera', '370702', 205, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1020, 'Khalaba', '370703', 205, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1021, 'Mayoni', '370704', 205, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1022, 'Namamali', '370705', 205, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1023, 'Lusheya/Lubinu', '370801', 206, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1024, 'Malaha/Isongo/Makunga', '370802', 206, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1025, 'East Wanga', '370803', 206, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1026, 'Mumias Central', '370901', 207, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1027, 'Mumias North', '370902', 207, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1028, 'Etenje', '370903', 207, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1029, 'Musanda', '370904', 207, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1030, 'Ingostse-Mathia', '371001', 208, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1031, 'Shinoyi-Shikomari', '371002', 208, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1032, 'Esumeyia', '371003', 208, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1033, 'Bunyala West', '371004', 208, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1034, 'Bunyal East', '371005', 208, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1035, 'Bunyala Central', '371006', 208, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1036, 'Mautuma', '371101', 209, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1037, 'Lugari', '371102', 209, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1038, 'Lumakanda', '371103', 209, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1039, 'Chekalini', '371104', 209, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1040, 'Chevaywa', '371105', 209, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1041, 'Lawandeti', '371106', 209, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1042, 'Mautuma', '371201', 210, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1043, 'Lugari', '371202', 210, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1044, 'Lumakanda', '371203', 210, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1045, 'Chekalini', '371204', 210, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1046, 'Chevaywa', '371205', 210, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1047, 'Lawandeti', '371206', 210, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1048, 'North East Bunyore', '380101', 211, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1049, 'Central Bunyore', '380102', 211, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1050, 'West Bunyore', '380103', 211, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1051, 'Shiru', '380201', 212, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1052, 'Gisambai', '380202', 212, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1053, 'Shamakhokho', '380203', 212, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1054, 'Banja', '380204', 212, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1055, 'Muhudi', '380205', 212, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1056, 'Tambaa', '380206', 212, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1057, 'Jepkoyai', '380207', 212, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1058, 'Lyaduywa/Izava', '380301', 213, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1059, 'West Sabatia', '380302', 213, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1060, 'Chavakali', '380303', 213, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1061, 'North Maragoli', '380304', 213, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1062, 'Wodanga', '380305', 213, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1063, 'Busali', '380306', 213, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1064, 'Lugaga-Wamuluma', '380401', 214, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1065, 'South Maragoli', '380402', 214, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1066, 'Central Maragoli', '380403', 214, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1067, 'Mungoma', '380404', 214, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1068, 'Luanda Township', '380501', 215, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1069, 'Wemilabi', '380502', 215, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1070, 'Mwibona', '380503', 215, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1071, 'Luanda South', '380504', 215, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1072, 'Emabungo', '380505', 215, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1073, 'Bumula', '390101', 216, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1074, 'Khasoko', '390102', 216, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1075, 'Kabula', '390103', 216, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1076, 'Kimaeti', '390104', 216, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1077, 'South Bukusu', '390105', 216, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1078, 'Siboti', '390106', 216, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1079, 'Bukembe West', '390201', 217, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1080, 'Bukembe East', '390202', 217, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1081, 'Township', '390203', 217, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1082, 'Khalaba', '390204', 217, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1083, 'Musikoma', '390205', 217, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1084, 'East Snag’alo', '390206', 217, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1085, 'Marakatu', '390207', 217, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1086, 'Tuuti', '390208', 217, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1087, 'West Sang’alo', '390209', 217, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1088, 'Mihuu', '390301', 218, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1089, 'Ndivisi', '390302', 218, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1090, 'Maraka', '390303', 218, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1091, 'Sitikho', '390401', 219, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1092, 'Matulo', '390402', 219, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1093, 'Bokoli', '390403', 219, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1094, 'Cheptais', '390501', 220, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1095, 'Chesikaki', '390502', 220, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1096, 'Chepyuk', '390503', 220, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1097, 'Kapkateny', '390504', 220, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1098, 'Kaptama', '390505', 220, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1099, 'Elgon', '390506', 220, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1100, 'Namwela', '390601', 221, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1101, 'Malakisi/South Kulisiru', '390602', 221, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1102, 'Lwandanyi', '390603', 221, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1103, 'Mbakalo', '390701', 222, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1104, 'Naitiri/Kabuyefwe', '390702', 222, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1105, 'Milima', '390703', 222, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1106, 'Ndalu/Tabani', '390704', 222, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1107, 'Tongaren', '390705', 222, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1108, 'Soysambu/Mitua', '390706', 222, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1109, 'Kabuchai/Chwele', '390801', 223, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1110, 'West Nalondo', '390802', 223, '2025-06-11 19:50:54', '2025-06-11 19:50:54'),
(1111, 'Bwake/Luuya', '390803', 223, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1112, 'Mukuyuni', '390804', 223, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1113, 'South Bukusu', '390805', 223, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1114, 'Kibingei', '390901', 224, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1115, 'Kimilili', '390902', 224, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1116, 'Maeni', '390903', 224, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1117, 'Kamukuywa', '390904', 224, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1118, 'MALABA CENTRAL', '400101', 225, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1119, 'MALABA NORTH', '400102', 225, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1120, 'ANG’URAI SOUTH', '400103', 225, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1121, 'MALABA SOUTH', '400104', 225, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1122, 'ANG’URAI NORTH', '400105', 225, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1123, 'ANG’URAI EAST', '400106', 225, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1124, 'ANG\'OROM', '400201', 226, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1125, 'CHAKOI SOUTH', '400202', 226, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1126, 'AMUKURA CENTRAL', '400203', 226, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1127, 'CHAKOI NORTH', '400204', 226, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1128, 'AMUKURA EAST', '400205', 226, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1129, 'AMUKURA WEST', '400206', 226, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1130, 'NAMBALE TOWNSHIP', '400301', 227, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1131, 'BUKHAYO NORTH/WALTSI', '400302', 227, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1132, 'BUKHAYO EAST', '400303', 227, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1133, 'BUKHAYO CENTRAL', '400304', 227, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1134, 'BUKHAYO WEST', '400401', 228, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1135, 'MAYENJE', '400402', 228, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1136, 'MATAYOS SOUTHBUSIBWABO', '400403', 228, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1137, 'BURUMBA', '400404', 228, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1138, 'MARACHI WESTKINGANDOLE', '400501', 229, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1139, 'MARACHI CENTRAL', '400502', 229, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1140, 'MARACHI EAST', '400503', 229, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1141, 'MARACHI NORTH', '400504', 229, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1142, 'ELUGULU', '400505', 229, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1143, 'NAMBOBOTO NAMBUKU', '400601', 230, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1144, 'NANGINA', '400602', 230, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1145, 'AGENG\'A NANGUBA', '400603', 230, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1146, 'BWIRI', '400604', 230, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1147, 'Usonga', '410101', 231, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1148, 'West Alego', '410102', 231, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1149, 'Central Alego', '410103', 231, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1150, 'Siaya Township', '410104', 231, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1151, 'North Alego', '410105', 231, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1152, 'South East Alego', '410106', 231, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1153, 'North Gem', '410201', 232, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1154, 'West Gem', '410202', 232, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1155, 'Central Gem', '410203', 232, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1156, 'Yala Township', '410204', 232, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1157, 'East Gem', '410205', 232, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1158, 'South Gem', '410206', 232, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1159, 'West Yimbo', '410301', 233, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1160, 'Central Sakwa', '410302', 233, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1161, 'South Sakwa', '410303', 233, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1162, 'Yimbo East', '410304', 233, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1163, 'West Sakwa', '410305', 233, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1164, 'North Sakwa', '410306', 233, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1165, 'Gem Rae', '410401', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1166, 'East Asembo', '410402', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1167, 'West Asembo', '410403', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1168, 'Central Asembo', '410404', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1169, 'South West Asembo', '410405', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1170, 'North West Asembo', '410406', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1171, 'North East Asembo', '410407', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1172, 'South East Asembo', '410408', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1173, 'Nyang\'oma Kogelo', '410409', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1174, 'West Uyoma', '410410', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1175, 'Central Uyoma', '410411', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1176, 'North Uyoma', '410412', 234, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1177, 'East Asembo', '410501', 235, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1178, 'West Asembo', '410502', 235, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1179, 'North Uyoma', '410503', 235, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1180, 'South Uyoma', '410504', 235, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1181, 'West Uyoma', '410505', 235, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1182, 'Sidindi', '410601', 236, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1183, 'Sigomere', '410602', 236, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1184, 'Ugunja', '410603', 236, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1185, 'Railways', '420101', 237, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1186, 'Migosi', '420102', 237, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1187, 'Shaurimoyo Kaloleni', '420103', 237, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1188, 'Market Milimani', '420104', 237, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1189, 'Kondele', '420105', 237, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1190, 'Nyalenda B', '420106', 237, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1191, 'Kajulu', '420201', 238, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1192, 'Kolwa East', '420202', 238, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1193, 'Manyatta \'B\'', '420203', 238, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1194, 'Nyalenda \'A\'', '420204', 238, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1195, 'Kolwa Central', '420205', 238, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1196, 'South West Kisumu', '420301', 239, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1197, 'Cetral Kisumu', '420302', 239, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1198, 'Kisumu North', '420303', 239, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1199, 'West Kisumu', '420304', 239, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1200, 'North West Kisumu', '420305', 239, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1201, 'West Seme', '420401', 240, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1202, 'Central Seme', '420402', 240, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1203, 'East Seme', '420403', 240, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1204, 'North Seme', '420404', 240, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1205, 'East Kano/Waidhi', '420501', 241, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1206, 'Awasi/Onjiko', '420502', 241, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1207, 'Ahero', '420503', 241, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1208, 'Kabonyo/Kanyag Wal', '420504', 241, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1209, 'Kobura', '420505', 241, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1210, 'Miwani', '420601', 242, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1211, 'Ombeyi', '420602', 242, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1212, 'Masogo/Nyag’oma', '420603', 242, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1213, 'Chemeli/Muhoroni/Koru', '420604', 242, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1214, 'South East Nyakach', '420701', 243, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1215, 'West Nyakach', '420702', 243, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1216, 'North Nyakach', '420703', 243, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1217, 'Central Nyakach', '420704', 243, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1218, 'South West Nyakach', '420705', 243, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1219, 'Homa Bay Central', '430101', 244, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1220, 'Homa Bay Arujo', '430102', 244, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1221, 'Homa Bay West', '430103', 244, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1222, 'Homa Bay East', '430104', 244, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1223, 'Kabondo East', '430201', 245, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1224, 'Kabondo West', '430202', 245, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1225, 'Kokwanyo', '430203', 245, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1226, 'Kakelo-Kojwach', '430204', 245, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1227, 'West Karachuonyo', '430301', 246, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1228, 'North Karachuonyo', '430302', 246, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1229, 'Central Kanyaluo', '430303', 246, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1230, 'Kibiri', '430304', 246, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1231, 'Wangchieng', '430305', 246, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1232, 'Kendu Bay Town', '430306', 246, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1233, 'West Kasipul', '430401', 247, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1234, 'South Kasipul', '430402', 247, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1235, 'Central Kasipul', '430403', 247, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1236, 'East Kamagak', '430404', 247, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1237, 'West Kamagak', '430405', 247, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1238, 'Kwabwai', '430501', 248, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1239, 'Kanyadoto', '430502', 248, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1240, 'Kanyikela', '430503', 248, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1241, 'Kabuoch North', '430504', 248, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1242, 'Kabuoch South/Pala', '430505', 248, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1243, 'Kanyamwa Kologi', '430506', 248, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1244, 'Kanyamwa Kosewe', '430507', 248, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1245, 'West Gem', '430601', 249, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1246, 'East Gem', '430602', 249, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1247, 'Kagan', '430603', 249, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1248, 'Kochia', '430604', 249, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1249, 'Mfangano Island', '430701', 250, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1250, 'Rusinga Island', '430702', 250, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1251, 'Kasgunga', '430703', 250, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1252, 'Gember', '430704', 250, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1253, 'Lambwe', '430705', 250, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1254, 'Gwassi South', '430801', 251, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1255, 'Gwassi North', '430802', 251, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1256, 'Kaksingri West', '430803', 251, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1257, 'Ruma-Kakshingri', '430804', 251, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1258, 'North Kamagambo', '440101', 252, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1259, 'Central Kamagambo', '440102', 252, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1260, 'East Kamagambo', '440103', 252, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1261, 'South Kamagambo', '440104', 252, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1262, 'North East Sakwa', '440201', 253, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1263, 'South Sakwa', '440202', 253, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1264, 'West Sakwa', '440203', 253, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1265, 'Central Sakwa', '440204', 253, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1266, 'God Jope', '440301', 254, '2025-06-11 19:50:55', '2025-06-11 19:50:55');
INSERT INTO `wards` (`id`, `name`, `code`, `constituency_id`, `created_at`, `updated_at`) VALUES
(1267, 'Suna Central', '440302', 254, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1268, 'Kakrao', '440303', 254, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1269, 'Kwa', '440304', 254, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1270, 'Wiga', '440401', 255, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1271, 'Wasweta II', '440402', 255, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1272, 'Ragan-Oruba', '440403', 255, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1273, 'Wasimbete', '440404', 255, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1274, 'West Kanyamkago', '440501', 256, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1275, 'North Kanyamkago', '440502', 256, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1276, 'Central Kanyam Kago', '440503', 256, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1277, 'South Kanyamkago', '440504', 256, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1278, 'East Kanyamkago', '440505', 256, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1279, 'Kachien’g', '440601', 257, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1280, 'Kanyasa', '440602', 257, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1281, 'North Kadem', '440603', 257, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1282, 'Macalder/ Kanyarwanda', '440604', 257, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1283, 'Kaler', '440605', 257, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1284, 'Got Kachola', '440606', 257, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1285, 'Muhuru', '440607', 257, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1286, 'Gokeharaka/Getamwega', '440701', 258, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1287, 'Ntimaru West', '440702', 258, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1288, 'Ntimaru East', '440703', 258, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1289, 'Nyabasi East', '440704', 258, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1290, 'Nyabasi West', '440705', 258, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1291, 'Bukira East', '440801', 259, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1292, 'Bukira Central/ Ikerege', '440802', 259, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1293, 'Isibania', '440803', 259, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1294, 'Makerero', '440804', 259, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1295, 'Masaba', '440805', 259, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1296, 'Tagare', '440806', 259, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1297, 'Nyamosense/Ko Mosoko', '440807', 259, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1298, 'MONYERERO', '450101', 260, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1299, 'SENSI', '450102', 260, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1300, 'MARANI', '450103', 260, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1301, 'MWAMONARI', '450104', 260, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1302, 'BOGUSERO', '450201', 261, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1303, 'BOGEKA', '450202', 261, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1304, 'NYAKOE', '450203', 261, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1305, 'KITUTU CENTRAL', '450204', 261, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1306, 'NYATIEKO', '450205', 261, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1307, 'ICHUNI', '450301', 262, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1308, 'NYAMASIBI', '450302', 262, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1309, 'MASIMBA', '450303', 262, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1310, 'GESUSU', '450304', 262, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1311, 'KIAMOKAMA', '450305', 262, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1312, 'BOBARACHO', '450401', 263, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1313, 'KISII CENTRAL', '450402', 263, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1314, 'KEUMBU', '450403', 263, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1315, 'KIOGORO', '450404', 263, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1316, 'BIRONGO', '450405', 263, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1317, 'IBENO', '450406', 263, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1318, 'BORABU MASABA', '450501', 264, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1319, 'BOOCHI BORABU', '450502', 264, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1320, 'BOKIMONGE', '450503', 264, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1321, 'MAGENCHE', '450504', 264, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1322, 'MAJOGE BASI', '450601', 265, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1323, 'BOOCHI/TENDERE', '450602', 265, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1324, 'BOSOTI/SENGERA', '450603', 265, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1325, 'MASIGE WEST', '450701', 266, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1326, 'MASIG EAST', '450702', 266, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1327, 'BASI CENTRAL', '450703', 266, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1328, 'NYACHEKI', '450704', 266, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1329, 'BASSI BOGETAORIO', '450705', 266, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1330, 'BOBASI CHACHE', '450706', 266, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1331, 'SAMETA/ MOKWERERO', '450707', 266, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1332, 'BOBASI/ BOITANGARE', '450708', 266, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1333, 'BOGETENGA', '450801', 267, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1334, 'BORABU/CHITAGO', '450802', 267, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1335, 'MOTICHO', '450803', 267, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1336, 'GETENGA', '450804', 267, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1337, 'TABAKA', '450805', 267, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1338, 'BOIKANGA', '450806', 267, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1339, 'BOMARIBA', '450901', 268, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1340, 'BOGIAKUMU', '450902', 268, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1341, 'BOKEIRA', '450903', 268, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1342, 'RIANA', '450904', 268, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1343, 'Mekenene', '460101', 269, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1344, 'Kiabonyoru', '460102', 269, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1345, 'Esise', '460103', 269, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1346, 'Nyansiongo', '460104', 269, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1347, 'Rigoma', '460201', 270, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1348, 'Gachuba', '460202', 270, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1349, 'Kemera', '460203', 270, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1350, 'Magombo', '460204', 270, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1351, 'Manga', '460205', 270, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1352, 'Gesima', '460206', 270, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1353, 'Nyamaiya', '460301', 271, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1354, 'Bogichora', '460302', 271, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1355, 'Bosamaro', '460303', 271, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1356, 'Bonyamatuta', '460304', 271, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1357, 'Township', '460305', 271, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1358, 'Itibo', '460401', 272, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1359, 'Bomwagamo', '460402', 272, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1360, 'Bokeira', '460403', 272, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1361, 'Magwagwa', '460404', 272, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1362, 'Ekerenyo', '460405', 272, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1363, 'Kitisuru', '470101', 273, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1364, 'Parklands/Highridge', '470102', 273, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1365, 'Karura', '470103', 273, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1366, 'Kangemi', '470104', 273, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1367, 'Mountain View', '470105', 273, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1368, 'Kilimani', '470201', 274, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1369, 'Kawangware', '470202', 274, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1370, 'Gatina', '470203', 274, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1371, 'Kileleshwa', '470204', 274, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1372, 'Kabiro', '470205', 274, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1373, 'Mutu-Ini', '470301', 275, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1374, 'Ngando', '470302', 275, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1375, 'Riruta', '470303', 275, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1376, 'Uthiru/Ruthimitu', '470304', 275, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1377, 'Waithaka', '470305', 275, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1378, 'Karen', '470401', 276, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1379, 'Nairobi West', '470402', 276, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1380, 'Mugumu-Ini', '470403', 276, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1381, 'South C', '470404', 276, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1382, 'Nyayo Highrise', '470405', 276, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1383, 'Woodley/Kenyatta Golf Course', '470501', 277, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1384, 'Sarang\'ombe', '470502', 277, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1385, 'Makina', '470503', 277, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1386, 'Lindi', '470504', 277, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1387, 'Laini Saba', '470505', 277, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1388, 'Kahawa West', '470601', 278, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1389, 'Roysambu', '470602', 278, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1390, 'Githurai', '470603', 278, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1391, 'Kahawa', '470604', 278, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1392, 'Zimmerman', '470605', 278, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1393, 'Kasarani', '470701', 279, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1394, 'Njiru', '470702', 279, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1395, 'Clay City', '470703', 279, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1396, 'Mwiki', '470704', 279, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1397, 'Ruai', '470705', 279, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1398, 'Utalii', '470801', 280, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1399, 'Korogocho', '470802', 280, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1400, 'Lucky Summer', '470803', 280, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1401, 'Mathare North', '470804', 280, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1402, 'Baba Dogo', '470805', 280, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1403, 'Kwa Njenga', '470901', 281, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1404, 'Imara Daima', '470902', 281, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1405, 'Kware', '470903', 281, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1406, 'Kwa Reuben', '470904', 281, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1407, 'Pipeline', '470905', 281, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1408, 'Dandora Area I', '471001', 282, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1409, 'Dandora Area II', '471002', 282, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1410, 'Dandora Area III', '471003', 282, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1411, 'Dandora Area IV', '471004', 282, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1412, 'Kariobangi North', '471005', 282, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1413, 'Kayole North', '471101', 283, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1414, 'Kayole Central', '471102', 283, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1415, 'Kariobangi South', '471103', 283, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1416, 'Komarock', '471104', 283, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1417, 'Matopeni / Spring Valley', '471105', 283, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1418, 'Utawala', '471201', 284, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1419, 'Upper Savanna', '471202', 284, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1420, 'Lower Savanna', '471203', 284, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1421, 'Embakasi', '471204', 284, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1422, 'Mihango', '471205', 284, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1423, 'Umoja 1', '471301', 285, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1424, 'Umoja 2', '471302', 285, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1425, 'Mowlem', '471303', 285, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1426, 'Kariobangi south', '471304', 285, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1427, 'Maringo/ Hamza', '471305', 285, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1428, 'Viwandani', '471401', 286, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1429, 'Harambee', '471402', 286, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1430, 'Makongeni', '471403', 286, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1431, 'Pumwani', '471404', 286, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1432, 'Eastleigh North', '471405', 286, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1433, 'Eastleigh South', '471501', 287, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1434, 'Nairobi Central', '471502', 287, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1435, 'Airbase', '471503', 287, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1436, 'California', '471504', 287, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1437, 'Mgara', '471505', 287, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1438, 'Nairobi South', '471601', 288, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1439, 'Hospital', '471602', 288, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1440, 'Ngara', '471603', 288, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1441, 'Pangani', '471604', 288, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1442, 'Landimawe', '471605', 288, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1443, 'Ziwani / Kariokor', '471606', 288, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1444, 'Mlango Kubwa', '471701', 289, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1445, 'Kiamaiko', '471702', 289, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1446, 'Ngei', '471703', 289, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1447, 'Huruma', '471704', 289, '2025-06-11 19:50:55', '2025-06-11 19:50:55'),
(1448, 'Mabatini', '471705', 289, '2025-06-11 19:50:55', '2025-06-11 19:50:55');

-- --------------------------------------------------------

--
-- Table structure for table `widgets`
--

DROP TABLE IF EXISTS `widgets`;
CREATE TABLE IF NOT EXISTS `widgets` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `theme_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `view_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `widgets_theme_id_slug_unique` (`theme_id`,`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `widgets`
--

INSERT INTO `widgets` (`id`, `theme_id`, `name`, `slug`, `description`, `icon`, `view_path`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'Counter Statistics Widget', 'counter', 'A counter widget to display animated statistics with icons', 'ri-number-1', 'widgets.counter.view', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(2, 2, 'Default Content Widget', 'default', 'A simple widget for displaying default content with title, text and optional button', 'ri-layout-line', 'widgets.default.view', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(3, 2, 'Featured Image Widget', 'featuredimage', 'Displays a featured image with optional title, caption and link', 'ri-image-line', 'widgets.featuredimage.view', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(4, 2, 'Header with Description', 'headerdescription', 'A header section with title, icon and description text', 'ri-h-1', 'widgets.headerdescription.view', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(5, 2, 'Icon Card Widget', 'iconcard', 'Display content cards with icons and text', 'fa fa-id-card', 'widgets.iconcard.view', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(6, 2, 'Image Slider Widget', 'slider', 'A responsive image carousel/slider with navigation controls and optional captions', 'fa fa-image', 'widgets.slider.view', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(7, 2, 'Team Members Widget', 'team', 'Display team members with images, titles and social links', 'fa fa-users', 'widgets.team.view', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(8, 2, 'Team Member Details Widget', 'teamdetails', 'Display detailed information about a single team member with expanded biography', 'fa fa-user-circle', 'widgets.teamdetails.view', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `widget_content_type_associations`
--

DROP TABLE IF EXISTS `widget_content_type_associations`;
CREATE TABLE IF NOT EXISTS `widget_content_type_associations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `widget_id` bigint UNSIGNED NOT NULL,
  `content_type_id` bigint UNSIGNED NOT NULL,
  `field_mappings` json DEFAULT NULL,
  `options` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `widget_content_type_associations_widget_id_foreign` (`widget_id`),
  KEY `widget_content_type_associations_content_type_id_foreign` (`content_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `widget_content_type_associations`
--

INSERT INTO `widget_content_type_associations` (`id`, `widget_id`, `content_type_id`, `field_mappings`, `options`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 1, '{\"Image\": \"image\", \"Title\": \"title\", \"Caption\": \"caption\", \"Link URL\": \"link_u_r_l\"}', '[]', 1, '2025-06-29 16:33:50', '2025-06-29 16:33:50', NULL),
(2, 1, 2, '{\"Counters\": \"repeater\"}', '[]', 1, '2025-07-01 15:56:50', '2025-07-01 15:56:50', NULL),
(3, 5, 3, '{\"Icon Cards\": \"icon_cards\"}', '[]', 1, '2025-07-01 15:59:06', '2025-07-01 15:59:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `widget_field_definitions`
--

DROP TABLE IF EXISTS `widget_field_definitions`;
CREATE TABLE IF NOT EXISTS `widget_field_definitions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `widget_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validation_rules` text COLLATE utf8mb4_unicode_ci,
  `settings` json DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `position` int NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `widget_field_definitions_widget_id_slug_unique` (`widget_id`,`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `widget_field_definitions`
--

INSERT INTO `widget_field_definitions` (`id`, `widget_id`, `name`, `slug`, `field_type`, `validation_rules`, `settings`, `is_required`, `position`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 2, 'Title', 'title', 'text', NULL, '[]', 1, 0, 'Widget main title', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(5, 2, 'Content', 'content', 'rich_text', NULL, '[]', 1, 1, 'Main content text', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(6, 2, 'Button Text', 'button_text', 'text', NULL, '[]', 0, 2, 'Optional button text', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(7, 2, 'Button URL', 'button_url', 'url', NULL, '[]', 0, 3, 'URL for the button', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(8, 2, 'Layout Style', 'layout_style', 'select', NULL, '{\"options\": [{\"label\": \"Default\", \"value\": \"default\"}, {\"label\": \"Boxed\", \"value\": \"boxed\"}, {\"label\": \"Full Width\", \"value\": \"full-width\"}]}', 0, 4, 'Widget layout style', '2025-06-27 17:49:06', '2025-07-01 14:34:12', NULL),
(9, 3, 'Image', 'image', 'image', NULL, '[]', 1, 0, 'The main featured image', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(10, 3, 'Title', 'title', 'text', NULL, '[]', 0, 1, 'Optional title above the image', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(11, 3, 'Caption', 'caption', 'text', NULL, '[]', 0, 2, 'Optional caption text below the image', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(12, 3, 'Link URL', 'link_url', 'url', NULL, '[]', 0, 3, 'Optional URL for the image to link to', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(13, 4, 'Title', 'title', 'text', NULL, '[]', 1, 0, 'The main heading text', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(14, 4, 'Icon', 'icon', 'image', NULL, '[]', 0, 1, 'Icon image for the header', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(15, 4, 'Description', 'description', 'textarea', NULL, '[]', 0, 2, 'Description text below the header', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(33, 1, 'Counters', 'counters', 'repeater', NULL, '{\"max_items\": 12, \"min_items\": 1, \"subfields\": [{\"name\": \"Icon\", \"slug\": \"icon\", \"field_type\": \"image\", \"description\": \"Icon image for the counter\", \"is_required\": true}, {\"name\": \"Top Text\", \"slug\": \"top_text\", \"field_type\": \"text\", \"description\": \"Top text for the counter\", \"is_required\": true}, {\"name\": \"Counter Number\", \"slug\": \"counter_number\", \"field_type\": \"number\", \"description\": \"Counter number\", \"is_required\": true}]}', 1, 0, 'Counters to display', '2025-07-01 14:29:15', '2025-07-01 14:34:12', NULL),
(17, 6, 'Main Image', 'main_image', 'image', NULL, '[]', 1, 0, 'The main image displayed in the slider', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(35, 6, 'Slides', 'slides', 'repeater', NULL, '{\"max_items\": 10, \"min_items\": 1, \"subfields\": [{\"name\": \"Image\", \"slug\": \"image\", \"field_type\": \"image\", \"description\": \"Slide image\", \"is_required\": true}, {\"name\": \"Title Line 1\", \"slug\": \"title_line1\", \"field_type\": \"text\", \"description\": \"First line of title (Layer 1)\", \"is_required\": false}, {\"name\": \"Title Line 2\", \"slug\": \"title_line2\", \"field_type\": \"text\", \"description\": \"Second line of title (Layer 2)\", \"is_required\": false}, {\"name\": \"Title Line 3\", \"slug\": \"title_line3\", \"field_type\": \"text\", \"description\": \"Third line of title (Layer 2)\", \"is_required\": false}, {\"name\": \"Button Text\", \"slug\": \"button_text\", \"field_type\": \"text\", \"description\": \"Optional button text (Layer 3)\", \"is_required\": false}, {\"name\": \"Button URL\", \"slug\": \"button_url\", \"field_type\": \"url\", \"description\": \"URL for the button\", \"is_required\": false}]}', 1, 1, 'Slide items for the slider', '2025-07-01 14:34:13', '2025-07-01 14:34:13', NULL),
(19, 7, 'Section Title', 'section_title', 'text', NULL, '[]', 0, 0, 'Optional title for the team section', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(20, 7, 'Section Description', 'section_description', 'textarea', NULL, '[]', 0, 1, 'Optional description for the team section', '2025-06-27 17:49:06', '2025-06-27 17:49:06', NULL),
(21, 7, 'Team Members', 'team_members', 'repeater', NULL, '{\"max_items\": 12, \"min_items\": 1, \"subfields\": [{\"name\": \"Photo\", \"slug\": \"photo\", \"field_type\": \"image\", \"description\": \"Team member photo\", \"is_required\": true}, {\"name\": \"Name\", \"slug\": \"name\", \"field_type\": \"text\", \"description\": \"Team member name\", \"is_required\": true}, {\"name\": \"Designation\", \"slug\": \"designation\", \"field_type\": \"text\", \"description\": \"Team member job title or position\", \"is_required\": true}, {\"name\": \"Bio\", \"slug\": \"bio\", \"field_type\": \"textarea\", \"description\": \"Team member biography or description\", \"is_required\": false}, {\"name\": \"Facebook URL\", \"slug\": \"facebook\", \"field_type\": \"url\", \"description\": \"Team member Facebook profile URL\", \"is_required\": false}, {\"name\": \"Twitter URL\", \"slug\": \"twitter\", \"field_type\": \"url\", \"description\": \"Team member Twitter profile URL\", \"is_required\": false}, {\"name\": \"LinkedIn URL\", \"slug\": \"linkedin\", \"field_type\": \"url\", \"description\": \"Team member LinkedIn profile URL\", \"is_required\": false}, {\"name\": \"Instagram URL\", \"slug\": \"instagram\", \"field_type\": \"url\", \"description\": \"Team member Instagram profile URL\", \"is_required\": false}]}', 1, 2, 'Team members to display', '2025-06-27 17:49:06', '2025-07-01 14:34:13', NULL),
(22, 8, 'Photo', 'photo', 'image', NULL, '[]', 1, 0, 'Team member photo', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL),
(23, 8, 'Name', 'name', 'text', NULL, '[]', 1, 1, 'Team member name', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL),
(24, 8, 'Position', 'position', 'text', NULL, '[]', 1, 2, 'Team member job title or position', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL),
(25, 8, 'Biography', 'biography', 'rich_text', NULL, '[]', 1, 3, 'Team member detailed biography', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL),
(26, 8, 'Email', 'email', 'email', NULL, '[]', 0, 4, 'Team member email address', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL),
(27, 8, 'Phone', 'phone', 'phone', NULL, '[]', 0, 5, 'Team member phone number', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL),
(28, 8, 'Facebook URL', 'facebook', 'url', NULL, '[]', 0, 6, 'Team member Facebook profile URL', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL),
(29, 8, 'Twitter URL', 'twitter', 'url', NULL, '[]', 0, 7, 'Team member Twitter profile URL', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL),
(30, 8, 'LinkedIn URL', 'linkedin', 'url', NULL, '[]', 0, 8, 'Team member LinkedIn profile URL', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL),
(31, 8, 'Instagram URL', 'instagram', 'url', NULL, '[]', 0, 9, 'Team member Instagram profile URL', '2025-06-27 17:49:07', '2025-06-27 17:49:07', NULL),
(34, 5, 'Icon Cards', 'icon_cards', 'repeater', NULL, '{\"max_items\": 12, \"min_items\": 1, \"subfields\": [{\"name\": \"Icon\", \"slug\": \"icon\", \"field_type\": \"image\", \"description\": \"Icon image for the card\", \"is_required\": true}, {\"name\": \"Heading\", \"slug\": \"heading\", \"field_type\": \"text\", \"description\": \"Card heading\", \"is_required\": true}, {\"name\": \"Content\", \"slug\": \"content\", \"field_type\": \"textarea\", \"description\": \"Card content text\", \"is_required\": true}, {\"name\": \"Link URL\", \"slug\": \"link_url\", \"field_type\": \"url\", \"description\": \"Optional URL for the icon to link to\", \"is_required\": false}]}', 1, 0, 'Cards with icons to display', '2025-07-01 14:29:15', '2025-07-01 14:34:13', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
