-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 11:15 AM
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
-- Database: `meditrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `allocation_disbursals`
--

CREATE TABLE `allocation_disbursals` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `bhw_id` int(11) NOT NULL,
  `disbursed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `allocation_disbursal_batches`
--

CREATE TABLE `allocation_disbursal_batches` (
  `id` int(11) NOT NULL,
  `disbursal_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `allocation_distributions`
--

CREATE TABLE `allocation_distributions` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `quantity_allocated` int(11) NOT NULL,
  `quantity_claimed` int(11) DEFAULT 0,
  `distribution_month` varchar(7) NOT NULL,
  `status` enum('pending','claimed','expired') DEFAULT 'pending',
  `claim_deadline` date NOT NULL,
  `claimed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `allocation_programs`
--

CREATE TABLE `allocation_programs` (
  `id` int(11) NOT NULL,
  `program_name` varchar(191) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity_per_senior` int(11) NOT NULL,
  `frequency` enum('monthly','quarterly') NOT NULL,
  `scope_type` enum('barangay','purok') NOT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `purok_id` int(11) DEFAULT NULL,
  `claim_window_days` int(11) NOT NULL DEFAULT 14,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allocation_programs`
--

INSERT INTO `allocation_programs` (`id`, `program_name`, `medicine_id`, `quantity_per_senior`, `frequency`, `scope_type`, `barangay_id`, `purok_id`, `claim_window_days`, `is_active`, `created_at`) VALUES
(1, 'Maintenace for Seniors', 13, 1, 'monthly', 'barangay', 1, NULL, 14, 1, '2025-10-21 06:10:32');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `description`, `start_date`, `end_date`, `image_path`, `created_by`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'Medical Mission - Free Check-up', 'Free medical check-up for all residents. Bring valid ID and medical records if available. Services include blood pressure monitoring, blood sugar testing, and general consultation.', '2025-10-20', '2025-10-20', NULL, 1, '2025-10-14 02:04:56', '2025-10-14 02:41:51', 1),
(2, 'Vaccination Drive - COVID-19 Booster', 'COVID-19 booster vaccination drive for eligible residents. Please bring vaccination card and valid ID. Walk-in basis, first come first served.', '2025-10-25', '2025-10-27', NULL, 1, '2025-10-14 02:04:56', '2025-10-14 02:41:52', 1),
(3, 'Community Clean-up Day', 'Join us for a community clean-up activity. All residents are encouraged to participate. Cleaning materials will be provided. Refreshments will be served.', '2025-11-01', '2025-11-01', NULL, 1, '2025-10-14 02:04:56', '2025-10-14 02:41:52', 1),
(4, 'Health Education Seminar', 'Learn about preventive healthcare, nutrition, and healthy lifestyle practices. Open to all residents. Certificate of attendance will be provided.', '2025-11-05', '2025-11-05', NULL, 1, '2025-10-14 02:04:56', '2025-10-14 02:41:52', 1),
(5, 'Senior Citizen Health Program', 'Special health program for senior citizens including free medicines, health monitoring, and social activities. Registration required.', '2025-11-10', '2025-11-12', NULL, 1, '2025-10-14 02:04:56', '2025-10-14 02:41:52', 1),
(6, 'asd', 'asd', '2025-09-30', '2025-10-07', NULL, 1, '2025-10-14 02:27:35', '2025-10-14 02:27:35', 1),
(7, 'immunize', 'asdas', '2025-10-15', '2025-10-16', NULL, 1, '2025-10-14 02:36:51', '2025-10-14 02:36:51', 1),
(8, 'Weekly Health Check-up', 'Regular weekly health monitoring for all residents. Blood pressure, weight, and basic health assessments available.', '2025-10-15', '2025-10-15', NULL, 1, '2025-10-14 02:41:52', '2025-10-14 02:41:52', 1),
(9, 'Medicine Distribution Day', 'Monthly medicine distribution for residents with approved requests. Please bring your ID and request confirmation.', '2025-10-18', '2025-10-18', NULL, 1, '2025-10-14 02:41:52', '2025-10-14 02:41:52', 1),
(10, 'Nutrition Workshop', 'Learn about healthy eating habits and meal planning for families. Free samples and recipe cards will be provided.', '2025-10-22', '2025-10-22', NULL, 1, '2025-10-14 02:41:52', '2025-10-14 02:41:52', 1);

-- --------------------------------------------------------

--
-- Table structure for table `barangays`
--

CREATE TABLE `barangays` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangays`
--

INSERT INTO `barangays` (`id`, `name`) VALUES
(1, 'Basdacu');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Pain Relief', 'Medicines for pain management and relief', '2025-10-08 04:02:37', '2025-10-08 04:02:37'),
(2, 'Antibiotics', 'Antibacterial medications', '2025-10-08 04:02:37', '2025-10-08 04:02:37'),
(3, 'Vitamins', 'Vitamin supplements and nutritional aids', '2025-10-08 04:02:37', '2025-10-08 04:02:37'),
(4, 'First Aid', 'Basic first aid supplies and medications', '2025-10-08 04:02:37', '2025-10-08 04:02:37'),
(5, 'Chronic Care', 'Medicines for chronic conditions', '2025-10-08 04:02:37', '2025-10-08 04:02:37'),
(6, 'Emergency', 'Emergency medications and supplies', '2025-10-08 04:02:37', '2025-10-08 04:02:37'),
(19, 'HighBlood Pressure Maintance', 'Medications for High Blood Patients', '2025-10-08 04:55:03', '2025-10-08 04:55:03');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `recipient` varchar(191) NOT NULL,
  `subject` varchar(191) NOT NULL,
  `body` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed') NOT NULL,
  `error` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `recipient`, `subject`, `body`, `sent_at`, `status`, `error`) VALUES
(1, 's2peed3@gmail.com', 'New medicine added', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New medicine available</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New medicine available</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new medicine has been added to the inventory.</p>\r\n        <div><p>Medicine: <b>asd</b></p><p>Please review batches and availability.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/dashboard.php\">Open BHW Panel</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-09-23 03:34:38', 'failed', 'SMTP Error: Could not connect to SMTP host. Failed to connect to server'),
(2, 's2peed3@gmail.com', 'New medicine added', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New medicine available</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New medicine available</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new medicine has been added to the inventory.</p>\r\n        <div><p>Medicine: <b>hahahha</b></p><p>Please review batches and availability.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/dashboard.php\">Open BHW Panel</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-09-23 03:54:09', 'failed', 'SMTP Error: Could not connect to SMTP host.'),
(3, 's2peed3@gmail.com', 'New medicine added', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New medicine available</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New medicine available</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new medicine has been added to the inventory.</p>\r\n        <div><p>Medicine: <b>bago</b></p><p>Please review batches and availability.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/dashboard.php\">Open BHW Panel</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-09-23 04:12:19', 'sent', NULL),
(4, 's2peed3@gmail.com', 'New medicine added', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New medicine available</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New medicine available</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new medicine has been added to the inventory.</p>\r\n        <div><p>Medicine: <b>bai na  bai</b></p><p>Please review batches and availability.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/dashboard.php\">Open BHW Panel</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-09-23 06:14:17', 'sent', NULL),
(5, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> Gardson Binasbas</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-02 02:43:29', 'sent', NULL),
(6, 'gardson.binasbas@bisu.edu.ph', 'Registration Approved - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Registration Approved</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Registration Approved</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Your resident registration has been approved!</p>\r\n        <div><p>Hello Gardson Binasbas,</p>\r\n        <p>Your registration as a resident has been approved by your assigned BHW. You can now log in to your account and start requesting medicines.</p>\r\n        <p>Please keep your login credentials safe and do not share them with others.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/index.php\">Login to MediTrack</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-03 01:19:11', 'sent', NULL),
(7, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> Shevic Tacatane</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-03 11:37:39', 'sent', NULL),
(8, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> Kimberly Mante</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-07 03:39:21', 'sent', NULL),
(9, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> Kimberly Mante</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-07 03:41:27', 'sent', NULL),
(10, 'vicvictacatane@gmail.com', 'Registration Approved - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Registration Approved</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Registration Approved</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Your resident registration has been approved!</p>\r\n        <div><p>Hello Kimberly Mante,</p>\r\n        <p>Your registration as a resident has been approved by your assigned BHW. You can now log in to your account and start requesting medicines.</p>\r\n        <p>Please keep your login credentials safe and do not share them with others.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/index.php\">Login to MediTrack</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-07 07:48:28', 'sent', NULL),
(11, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> LUCIANO C CANAMOCAN</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 02:39:52', 'sent', NULL),
(13, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> LUCIANO C CANAMOCAN</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 03:13:13', 'sent', NULL),
(14, 'lucianocanamocanjr@gmail.com', 'Registration Approved - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Registration Approved</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Registration Approved</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Your resident registration has been approved!</p>\r\n        <div><p>Hello LUCIANO C CANAMOCAN,</p>\r\n        <p>Your registration as a resident has been approved by your assigned BHW. You can now log in to your account and start requesting medicines.</p>\r\n        <p>Please keep your login credentials safe and do not share them with others.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/index.php\">Login to MediTrack</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 03:37:44', 'sent', NULL),
(15, 'admin@example.com', 'Registration Rejected - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Registration Rejected</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Registration Rejected</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Your resident registration has been rejected.</p>\r\n        <div><p>Hello LUCIANO C CANAMOCAN,</p>\r\n        <p>Unfortunately, your registration as a resident has been rejected by your assigned BHW.</p>\r\n        <p><strong>Reason:</strong> balik balik imong email</p>\r\n        <p>You may contact your BHW for more information or submit a new registration with corrected information.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/index.php\">Contact Support</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 03:47:02', 'sent', NULL),
(16, 'canamocan18@gmail.com', 'Registration Rejected - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Registration Rejected</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Registration Rejected</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Your resident registration has been rejected.</p>\r\n        <div><p>Hello John Mark Sagetarios,</p>\r\n        <p>Unfortunately, your registration as a resident has been rejected by your assigned BHW.</p>\r\n        <p><strong>Reason:</strong> gwapo ra kayka</p>\r\n        <p>You may contact your BHW for more information or submit a new registration with corrected information.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/index.php\">Contact Support</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 03:47:49', 'sent', NULL),
(17, 's2peed3@gmail.com', 'New medicine added', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New medicine available</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New medicine available</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new medicine has been added to the inventory.</p>\r\n        <div><p>Medicine: <b>Losartan</b></p><p>Please review batches and availability.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/dashboard.php\">Open BHW Panel</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 05:06:56', 'sent', NULL),
(18, 's2peed3@gmail.com', 'New Medicine Request - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Medicine Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Medicine Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A resident has submitted a new medicine request.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new medicine request has been submitted by Axl Tag-at.</p>\r\n        <p><strong>Medicine:</strong> Unknown Medicine</p>\r\n        <p>Please review the request and approve or reject it.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/requests.php\">Review Request</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 05:29:27', 'sent', NULL),
(19, 's2peed3@gmail.com', 'New Medicine Request - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Medicine Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Medicine Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A resident has submitted a new medicine request.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new medicine request has been submitted by Axl Tag-at.</p>\r\n        <p><strong>Medicine:</strong> Unknown Medicine</p>\r\n        <p>Please review the request and approve or reject it.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/requests.php\">Review Request</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 05:30:48', 'sent', NULL),
(20, 's2peed3@gmail.com', 'New Medicine Request - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Medicine Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Medicine Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A resident has submitted a new medicine request.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new medicine request has been submitted by Axl Tag-at.</p>\r\n        <p><strong>Medicine:</strong> Unknown Medicine</p>\r\n        <p>Please review the request and approve or reject it.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/requests.php\">Review Request</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 05:39:01', 'sent', NULL),
(21, 's2peed5@gmail.com', 'Request approved', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Request approved</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Request approved</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Your medicine request was approved.</p>\r\n        <div><p>Please proceed to your assigned barangay health center to claim.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/resident/requests.php\">View My Requests</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 09:21:14', 'sent', NULL);
INSERT INTO `email_logs` (`id`, `recipient`, `subject`, `body`, `sent_at`, `status`, `error`) VALUES
(22, 's2peed5@gmail.com', 'Request approved', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Request approved</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Request approved</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Your medicine request was approved.</p>\r\n        <div><p>Please proceed to your assigned barangay health center to claim.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/resident/requests.php\">View My Requests</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-08 09:21:19', 'sent', NULL),
(23, 's2peed3@gmail.com', 'New Medicine Request - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Medicine Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Medicine Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A resident has submitted a new medicine request.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new medicine request has been submitted by Axl Tag-at.</p>\r\n        <p><strong>Medicine:</strong> Unknown Medicine</p>\r\n        <p>Please review the request and approve or reject it.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/bhw/requests.php\">Review Request</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-12 12:18:46', 'sent', NULL),
(24, 's2peed5@gmail.com', 'Medicine Request Rejected - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Request Rejected</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Request Rejected</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Your medicine request has been rejected.</p>\r\n        <div><p>Hello Axl Tag-at,</p>\r\n        <p>Unfortunately, your request for Losartan has been rejected.</p>\r\n        <p><strong>Reason:</strong> haha</p>\r\n        <p>You may contact your BHW for more information or submit a new request with additional documentation.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/resident/requests.php\">View Request</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-18 02:16:26', 'failed', 'SMTP Error: Could not authenticate.'),
(25, 's2peed5@gmail.com', 'Medicine Request Rejected - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Request Rejected</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Request Rejected</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Your medicine request has been rejected.</p>\r\n        <div><p>Hello Axl Tag-at,</p>\r\n        <p>Unfortunately, your request for Losartan has been rejected.</p>\r\n        <p><strong>Reason:</strong> haha</p>\r\n        <p>You may contact your BHW for more information or submit a new request with additional documentation.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/resident/requests.php\">View Request</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-18 02:16:28', '', 'PHP mail() function failed'),
(26, 's2peed5@gmail.com', '[Resend] Medicine Request Rejected - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Resent Email</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Resent Email</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">This is an automatic resend of a previous notification.</p>\r\n        <div><p>If you received the earlier message, you can ignore this one.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/public/login.php\">Open MediTrack</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-20 02:43:51', 'sent', NULL),
(27, 'calnamocan18@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello John Mark C Sagetarios,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">983545</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/verify_email.php?email=calnamocan18%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 09:38:58', 'sent', NULL),
(28, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> John Mark C Sagetarios</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 09:39:10', 'sent', NULL),
(29, 'calnamocan18@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello John Mark C Sagetarios,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">026075</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/verify_email.php?email=calnamocan18%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 09:51:36', 'sent', NULL),
(30, 'calnamocan18@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello John Mark C Sagetarios,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">514167</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/verify_email.php?email=calnamocan18%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 10:55:37', 'sent', NULL),
(31, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> John Mark C Sagetarios</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 10:55:45', 'sent', NULL),
(32, 'calnamocan18@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello John Mark C Sagetarios,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">542530</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/verify_email.php?email=calnamocan18%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 10:56:21', 'sent', NULL),
(33, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> John Mark C Sagetarios</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 10:56:46', 'failed', 'SMTP Error: Could not connect to SMTP host. Connection failed. stream_socket_enable_crypto(): SSL: An existing connection was forcibly closed by the remote host'),
(34, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> John Mark C Sagetarios</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 10:56:48', '', 'PHP mail() function failed'),
(35, 'calnamocan18@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello John Mark Sagetarios,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">118920</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calnamocan18%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 11:12:58', 'sent', NULL),
(36, 'calnamocan18@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello John Mark Sagetarios,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">318892</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calnamocan18%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 11:13:33', 'sent', NULL),
(37, 'calnamocan18@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello John Mark Sagetarios,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">510031</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calnamocan18%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 11:13:45', 'sent', NULL),
(38, 'calnamocan18@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello John Mark Sagetarios,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">439587</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calnamocan18%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 11:14:42', 'sent', NULL),
(39, 'calnamocan18@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello John Mark Sagetarios,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">790168</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calnamocan18%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 11:15:28', 'sent', NULL),
(40, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> John Mark C Sagetarios</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-10-23 11:16:03', 'sent', NULL);
INSERT INTO `email_logs` (`id`, `recipient`, `subject`, `body`, `sent_at`, `status`, `error`) VALUES
(41, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">286367</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 08:19:20', 'sent', NULL),
(42, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">480411</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 08:28:10', 'sent', NULL),
(43, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">284187</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 08:28:14', 'sent', NULL),
(44, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">946066</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 08:31:25', 'sent', NULL),
(45, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">461810</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 08:32:04', 'sent', NULL),
(46, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">598570</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 08:51:57', 'sent', NULL),
(47, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">285115</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 08:58:22', 'sent', NULL),
(48, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escole,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">160128</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 09:09:46', 'sent', NULL),
(49, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">934791</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 09:15:47', 'sent', NULL),
(50, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">887057</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 09:20:57', 'sent', NULL),
(51, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">414956</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 09:27:05', 'sent', NULL),
(52, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">283724</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 09:32:55', 'sent', NULL),
(53, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">971219</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 10:10:07', 'sent', NULL),
(54, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">436212</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 11:05:31', 'sent', NULL),
(55, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">896933</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 11:05:39', 'sent', NULL),
(56, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">484930</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 11:06:14', 'sent', NULL),
(57, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">916927</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 11:44:52', 'sent', NULL),
(58, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">900585</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 11:44:57', 'sent', NULL);
INSERT INTO `email_logs` (`id`, `recipient`, `subject`, `body`, `sent_at`, `status`, `error`) VALUES
(59, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">185552</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 11:45:02', 'sent', NULL),
(60, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">060022</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 11:45:14', 'sent', NULL),
(61, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">356042</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 11:45:18', 'sent', NULL),
(62, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">961249</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 11:45:22', 'sent', NULL),
(63, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">481936</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-16 11:46:04', 'sent', NULL),
(64, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">454787</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 10:58:30', 'sent', NULL),
(65, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">712978</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 11:36:14', 'sent', NULL),
(66, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">897071</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 11:36:18', 'sent', NULL),
(67, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">283612</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 12:05:15', 'sent', NULL),
(68, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">336199</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 12:13:38', 'sent', NULL),
(69, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">919380</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 12:19:15', 'sent', NULL),
(70, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">719191</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 12:23:46', 'sent', NULL),
(71, 'calderon202423@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Junrel Escol,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">254125</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=calderon202423%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 12:26:28', 'sent', NULL),
(72, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> Junrel E Escol</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 12:26:52', 'sent', NULL),
(73, 's2peed2@gmail.com', 'Verify your email - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Verify your email address</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Verify your email address</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Use the code below to verify your email and complete your registration.</p>\r\n        <div><div style=\"font-size:14px;color:#111827\"><p>Hello Clifbelle Cabrera,</p><p>Your verification code is:</p><div style=\"font-size:28px;font-weight:700;letter-spacing:4px;margin:12px 0;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-align:center\">302666</div><p>This code will expire in 15 minutes. If you did not request this, you can ignore this email.</p></div></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/verify_email.php?email=s2peed2%40gmail.com\">Verify Email</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 12:33:36', 'sent', NULL),
(74, 's2peed3@gmail.com', 'New Resident Registration - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>New Registration Request</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">New Registration Request</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">A new resident has registered in your purok.</p>\r\n        <div><p>Hello Ann Canamucan,</p>\r\n        <p>A new resident registration is pending your approval in Purok 1.</p>\r\n        <p><strong>Resident:</strong> Clifbelle C Cabrera</p>\r\n        <p>Please review the registration details and approve or reject the request.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/bhw/pending_residents.php\">Review Registration</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 12:33:56', 'sent', NULL),
(75, 'calnamocan18@gmail.com', 'Registration Rejected - MediTrack', '<!doctype html>\r\n<html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n<title>Registration Rejected</title>\r\n<style>body{background-color:#f7f7fb;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;color:#111827} .container{max-width:640px;margin:0 auto;padding:24px} .card{background:#ffffff;border-radius:12px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.1);overflow:hidden} .header{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%);padding:20px 24px;color:#fff} .brand{font-weight:700;font-size:18px} .title{font-size:20px;margin:0} .content{padding:24px} p{line-height:1.6;margin:0 0 12px} .lead{font-size:16px;color:#374151;margin-bottom:16px} .divider{height:1px;background:#e5e7eb;margin:16px 0} .btn a{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600} .muted{color:#6b7280;font-size:12px;margin-top:12px} @media (prefers-color-scheme: dark){ body{background:#0b1220;color:#e5e7eb} .card{background:#111827;box-shadow:none} .header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%)} .lead{color:#9ca3af} .divider{background:#1f2937} .muted{color:#9ca3af} }</style></head>\r\n<body>\r\n  <div class=\"container\">\r\n    <div class=\"card\">\r\n      <div class=\"header\"><div class=\"brand\">MediTrack</div><h1 class=\"title\">Registration Rejected</h1></div>\r\n      <div class=\"content\">\r\n        <p class=\"lead\">Your resident registration has been rejected.</p>\r\n        <div><p>Hello John Mark C Sagetarios,</p>\r\n        <p>Unfortunately, your registration as a resident has been rejected by your assigned BHW.</p>\r\n        <p><strong>Reason:</strong> afsd</p>\r\n        <p>You may contact your BHW for more information or submit a new registration with corrected information.</p></div>\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"btn\"><a href=\"/thesis/public/index.php\">Contact Support</a></td></tr></table>\r\n        <div class=\"divider\"></div>\r\n        <p class=\"muted\">This is an automated message from MediTrack. Please do not reply.</p>\r\n      </div>\r\n    </div>\r\n    <p class=\"muted\" style=\"text-align:center\">© 2025 MediTrack</p>\r\n  </div>\r\n</body></html>', '2025-11-17 12:58:57', 'sent', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_notifications`
--

CREATE TABLE `email_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed') NOT NULL DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_notifications`
--

INSERT INTO `email_notifications` (`id`, `user_id`, `notification_type`, `subject`, `body`, `sent_at`, `status`) VALUES
(2, 6, 'registration_approval', 'Registration Approved', 'Resident registration approved', '2025-10-03 01:19:11', 'sent'),
(6, 7, 'registration_approval', 'Registration Approved', 'Resident registration approved', '2025-10-07 07:48:28', 'sent'),
(9, 8, 'registration_approval', 'Registration Approved', 'Resident registration approved', '2025-10-08 03:37:44', 'sent'),
(12, 2, 'medicine_request', 'New Medicine Request', 'New medicine request notification sent to BHW', '2025-10-08 05:29:27', 'sent'),
(13, 2, 'medicine_request', 'New Medicine Request', 'New medicine request notification sent to BHW', '2025-10-08 05:30:48', 'sent'),
(14, 2, 'medicine_request', 'New Medicine Request', 'New medicine request notification sent to BHW', '2025-10-08 05:39:01', 'sent'),
(15, 2, 'medicine_request', 'New Medicine Request', 'New medicine request notification sent to BHW', '2025-10-12 12:18:46', 'sent'),
(16, 2, 'medicine_rejection', 'Medicine Request Rejected', 'Medicine request rejection notification sent to resident', '2025-10-18 02:16:28', 'failed');

-- --------------------------------------------------------

--
-- Table structure for table `family_addition_notifications`
--

CREATE TABLE `family_addition_notifications` (
  `id` int(11) NOT NULL,
  `family_addition_id` int(11) NOT NULL,
  `recipient_type` enum('resident','bhw') NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `notification_type` enum('pending','approved','rejected') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Notifications for family member approvals';

-- --------------------------------------------------------

--
-- Table structure for table `family_members`
--

CREATE TABLE `family_members` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL COMMENT 'Date of birth instead of age for better data accuracy',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `middle_initial` varchar(5) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_members`
--

INSERT INTO `family_members` (`id`, `resident_id`, `full_name`, `relationship`, `date_of_birth`, `created_at`, `first_name`, `middle_initial`, `last_name`) VALUES
(1, 3, 'Marilyn Mante', 'Mother', '1976-12-17', '2025-10-07 07:48:24', 'Marilyn', '', 'Mante'),
(2, 1, 'Jaycho Carido', 'Brother', '2001-12-01', '2025-10-08 03:37:33', 'Jaycho', 'C', 'Carido'),
(3, 4, '', 'Brother', '2025-10-06', '2025-10-08 03:37:41', 'jaycho', 'c', 'carido');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_adjustments`
--

CREATE TABLE `inventory_adjustments` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `adjustment_type` enum('CORRECTION','PHYSICAL_COUNT','SYSTEM_ERROR','THEFT','DAMAGE') NOT NULL,
  `old_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `difference` int(11) NOT NULL,
  `reason` text NOT NULL,
  `adjusted_by` int(11) NOT NULL,
  `adjusted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_alerts`
--

CREATE TABLE `inventory_alerts` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `alert_type` enum('low_stock','out_of_stock','expiring_soon','expired','reorder_point') NOT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL,
  `message` text NOT NULL,
  `current_value` int(11) DEFAULT NULL,
  `threshold_value` int(11) DEFAULT NULL,
  `is_acknowledged` tinyint(1) DEFAULT 0,
  `acknowledged_by` int(11) DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_alerts`
--

INSERT INTO `inventory_alerts` (`id`, `medicine_id`, `batch_id`, `alert_type`, `severity`, `message`, `current_value`, `threshold_value`, `is_acknowledged`, `acknowledged_by`, `acknowledged_at`, `created_at`) VALUES
(1, 3, NULL, 'out_of_stock', 'critical', 'biogesic is OUT OF STOCK! Immediate reorder required.', 0, 0, 1, 1, '2025-11-17 13:13:06', '2025-10-21 05:54:33'),
(2, 3, NULL, 'out_of_stock', 'critical', 'biogesic is OUT OF STOCK! Immediate reorder required.', 0, 0, 0, NULL, NULL, '2025-11-17 13:13:08');

-- --------------------------------------------------------

--
-- Stand-in structure for view `inventory_summary`
-- (See below for the actual view)
--
CREATE TABLE `inventory_summary` (
`medicine_id` int(11)
,`medicine_name` varchar(191)
,`description` text
,`image_path` varchar(255)
,`current_stock` decimal(32,0)
,`total_received` decimal(32,0)
,`total_dispensed` decimal(32,0)
,`expired_stock` decimal(32,0)
,`expiring_soon` decimal(32,0)
,`is_low_stock` int(1)
,`last_transaction_date` timestamp
,`total_batches` bigint(21)
,`active_batches` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `transaction_type` enum('IN','OUT','ADJUSTMENT','TRANSFER','EXPIRED','DAMAGED') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_type` enum('BATCH_RECEIVED','REQUEST_DISPENSED','WALKIN_DISPENSED','ADJUSTMENT','TRANSFER','EXPIRY','DAMAGE') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `minimum_stock_level` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `name`, `description`, `category`, `image_path`, `is_active`, `created_at`, `category_id`, `minimum_stock_level`) VALUES
(3, 'biogesic', 'asdas', 'Pain Relief', 'uploads/medicines/med_1758594421_2344edfb.png', 1, '2025-09-23 02:27:01', NULL, 10),
(13, 'Losartan', 'For high blood pressure patients', NULL, 'uploads/medicines/med_1759900012_ea0a2dfe.jpg', 1, '2025-10-08 05:06:52', 19, 10);

-- --------------------------------------------------------

--
-- Table structure for table `medicine_batches`
--

CREATE TABLE `medicine_batches` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `batch_code` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_available` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `received_at` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_batches`
--

INSERT INTO `medicine_batches` (`id`, `medicine_id`, `batch_code`, `quantity`, `quantity_available`, `expiry_date`, `received_at`, `created_at`) VALUES
(2, 13, 'Los0825', 50, 47, '2025-12-19', '2025-10-08', '2025-10-08 05:07:41');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_categories`
--

CREATE TABLE `medicine_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_categories`
--

INSERT INTO `medicine_categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Pain Relief', 'Medicines for pain management and relief', '2025-10-08 03:59:32', '2025-10-08 03:59:32'),
(2, 'Antibiotics', 'Antibacterial medications', '2025-10-08 03:59:32', '2025-10-08 03:59:32'),
(3, 'Vitamins', 'Vitamin supplements and nutritional aids', '2025-10-08 03:59:32', '2025-10-08 03:59:32'),
(4, 'First Aid', 'Basic first aid supplies and medications', '2025-10-08 03:59:32', '2025-10-08 03:59:32'),
(5, 'Chronic Care', 'Medicines for chronic conditions', '2025-10-08 03:59:32', '2025-10-08 03:59:32'),
(6, 'Emergency', 'Emergency medications and supplies', '2025-10-08 03:59:32', '2025-10-08 03:59:32');

-- --------------------------------------------------------

--
-- Table structure for table `pending_family_members`
--

CREATE TABLE `pending_family_members` (
  `id` int(11) NOT NULL,
  `pending_resident_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL COMMENT 'Date of birth instead of age for better data accuracy',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `middle_initial` varchar(5) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_family_members`
--

INSERT INTO `pending_family_members` (`id`, `pending_resident_id`, `full_name`, `relationship`, `date_of_birth`, `created_at`, `first_name`, `middle_initial`, `last_name`) VALUES
(1, 1, 'Christe Hanna Mae Cuas', 'Mother', '0000-00-00', '2025-09-24 00:20:17', 'Christe', 'Hanna', 'Cuas'),
(2, 1, 'Clifbelle Cabrera', 'Son', '0000-00-00', '2025-09-24 00:20:17', 'Clifbelle', '', 'Cabrera'),
(3, 4, 'Marilyn Mante', 'Mother', '1976-12-17', '2025-10-07 03:39:17', 'Marilyn', '', 'Mante'),
(4, 6, 'Marilyn Mante', 'Mother', '1976-12-17', '2025-10-07 03:41:25', 'Marilyn', '', 'Mante'),
(5, 7, '', 'Daughter', '2025-10-06', '2025-10-08 02:39:49', 'jaycho', 'c', 'carido'),
(6, 12, '', 'Brother', '2025-10-06', '2025-10-08 03:13:10', 'jaycho', 'c', 'carido');

-- --------------------------------------------------------

--
-- Table structure for table `pending_residents`
--

CREATE TABLE `pending_residents` (
  `id` int(11) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_initial` varchar(10) DEFAULT NULL COMMENT 'Middle initial or middle name',
  `date_of_birth` date NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `barangay_id` int(11) NOT NULL,
  `purok_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `bhw_id` int(11) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_code` varchar(12) DEFAULT NULL,
  `email_verification_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_residents`
--

INSERT INTO `pending_residents` (`id`, `email`, `password_hash`, `first_name`, `last_name`, `middle_initial`, `date_of_birth`, `phone`, `address`, `barangay_id`, `purok_id`, `status`, `bhw_id`, `rejection_reason`, `email_verified`, `email_verification_code`, `email_verification_expires_at`, `created_at`, `updated_at`) VALUES
(1, 'canamocan18@gmail.com', '$2y$10$YVa27Z.RbrKby1AprgPQz.R9O7GkvCoMl.IM2/1IhE9WxVQpuVjha', 'John Mark', 'Sagetarios', NULL, '2000-02-03', '09123123121', '', 1, 1, 'rejected', 2, 'gwapo ra kayka', 0, NULL, NULL, '2025-09-24 00:20:17', '2025-10-08 03:47:46'),
(2, 'gardson.binasbas@bisu.edu.ph', '$2y$10$BMPM4ivGkSplxNscVjkKhumiuZ0EcmMPzdUio407iKxTgTPnWVZme', 'Gardson', 'Binasbas', NULL, '2013-02-07', '0981278311', '', 1, 1, 'approved', 2, '', 0, NULL, NULL, '2025-10-02 02:43:23', '2025-10-03 01:19:07'),
(3, 'shevic@gmail.com', '$2y$10$CTubdzZ6vLp.4iBnamyj9.Ear9ActzTLoFEcyFFc0iKVBRpZseI5O', 'Shevic', 'Tacatane', NULL, '2003-11-03', '09937057361', '', 1, 1, 'pending', NULL, NULL, 0, NULL, NULL, '2025-10-03 11:37:33', NULL),
(4, 'mayettacatane@gmail.com', '$2y$10$NVTFTAZHppS9G11/XrtSeutZoWCWHtFCaEnNZ85U9uuEiwgpogFSS', 'Kimberly', 'Mante', NULL, '2003-07-19', '09937057361', '', 1, 1, 'pending', NULL, NULL, 0, NULL, NULL, '2025-10-07 03:39:17', NULL),
(6, 'vicvictacatane@gmail.com', '$2y$10$ubc6V951T9m/x5DhG7jY3.e1BgXls/R69D2S37MbXKwlNc/utH3Na', 'Kimberly', 'Mante', NULL, '2004-07-18', '09937057361', '', 1, 1, 'approved', 2, NULL, 0, NULL, NULL, '2025-10-07 03:41:25', '2025-10-07 07:48:24'),
(7, 'admin@example.com', '$2y$10$MpgkVlpT9WrNdsiE3A.Lo.YV7dALBpo/FUiEEBhCh5MpIHmLPC/su', 'LUCIANO', 'CANAMOCAN', 'C', '2025-10-01', '0936 684 9713', '', 1, 1, 'rejected', 2, 'balik balik imong email', 0, NULL, NULL, '2025-10-08 02:39:49', '2025-10-08 03:46:58'),
(12, 'lucianocanamocanjr@gmail.com', '$2y$10$Cb6HtKKDT7pSonkLXsT6ouFwVphBRY9tvqUogJvSim4wsUngR64qC', 'LUCIANO', 'CANAMOCAN', 'C', '2025-09-30', '0936 684 9713', '', 1, 1, 'approved', 2, NULL, 0, NULL, NULL, '2025-10-08 03:13:10', '2025-10-08 03:37:41'),
(23, 'calnamocan18@gmail.com', '$2y$10$yDE4kvOXVexPy/rUAk0.W..MUUah8CXkn.KehE1tSPdDMgcm1ubB6', 'John Mark', 'Sagetarios', 'C', '2025-10-01', '0912 312 3123', '', 1, 1, 'rejected', 2, 'afsd', 0, NULL, NULL, '2025-10-23 11:15:49', '2025-11-17 12:58:53'),
(26, 'calderon202423@gmail.com', '$2y$10$BDqqiADkvQH2oKwLnoYCpujACyXXZ.v3hoy57Y1up7tYPs0/NOZ7O', 'Junrel', 'Escol', 'E', '2007-11-01', '0987 123 1232', 'Purok 1, Basdacu', 1, 1, 'pending', NULL, NULL, 1, NULL, NULL, '2025-11-17 12:26:48', NULL),
(27, 's2peed2@gmail.com', '$2y$10$Huxphfd7DZWroyvhhp6eF.Sir7eMHoM3SIvZnrUH0KhteGId3zKBe', 'Clifbelle', 'Cabrera', 'C', '2007-11-16', '0912 323 2557', 'Purok 1, Basdacu', 1, 1, 'pending', NULL, NULL, 1, NULL, NULL, '2025-11-17 12:33:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `puroks`
--

CREATE TABLE `puroks` (
  `id` int(11) NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `puroks`
--

INSERT INTO `puroks` (`id`, `barangay_id`, `name`) VALUES
(1, 1, 'Purok 1'),
(2, 1, 'Purok 2'),
(3, 1, 'Purok 3'),
(4, 1, 'Purok 4'),
(5, 1, 'Purok 5'),
(6, 1, 'Purok 6'),
(8, 1, 'Purok 7');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `requested_for` enum('self','family') NOT NULL DEFAULT 'self',
  `family_member_id` int(11) DEFAULT NULL,
  `patient_name` varchar(150) DEFAULT NULL,
  `patient_date_of_birth` date DEFAULT NULL COMMENT 'Date of birth instead of age for better data accuracy',
  `relationship` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `proof_image_path` varchar(255) DEFAULT NULL,
  `status` enum('submitted','approved','rejected','ready_to_claim','claimed') NOT NULL DEFAULT 'submitted',
  `bhw_id` int(11) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `resident_id`, `medicine_id`, `requested_for`, `family_member_id`, `patient_name`, `patient_date_of_birth`, `relationship`, `reason`, `proof_image_path`, `status`, `bhw_id`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 13, 'family', 2, 'Jaycho C. Cardio', '2001-12-01', 'Brother', 'Test request', NULL, 'approved', 2, NULL, '2025-10-08 05:38:10', '2025-10-08 09:21:10'),
(2, 1, 13, 'self', NULL, '', '0000-00-00', '', 'asd', 'uploads/proofs/proof_1759901937_4f5b1ece.jpg', 'approved', 2, NULL, '2025-10-08 05:38:57', '2025-10-08 09:21:15'),
(3, 1, 13, 'self', NULL, '', '0000-00-00', '', 'asd', 'uploads/proofs/proof_1760271521_4edd81d1.jpg', 'rejected', 2, 'haha', '2025-10-12 12:18:41', '2025-10-18 02:15:55');

-- --------------------------------------------------------

--
-- Table structure for table `request_fulfillments`
--

CREATE TABLE `request_fulfillments` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_fulfillments`
--

INSERT INTO `request_fulfillments` (`id`, `request_id`, `batch_id`, `quantity`, `created_at`) VALUES
(1, 1, 2, 1, '2025-10-08 09:21:10'),
(2, 2, 2, 1, '2025-10-08 09:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `barangay_id` int(11) NOT NULL,
  `purok_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_initial` varchar(10) DEFAULT NULL COMMENT 'Middle initial or middle name',
  `date_of_birth` date NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `user_id`, `barangay_id`, `purok_id`, `first_name`, `last_name`, `middle_initial`, `date_of_birth`, `email`, `phone`, `address`, `created_at`) VALUES
(1, 4, 1, 1, 'axl', 'tagat', 's', '2020-06-10', 's2peed5@gmail.com', '09940204774', '', '2025-09-23 08:47:33'),
(2, 6, 1, 1, 'Gardson', 'Binasbas', NULL, '2013-02-07', 'gardson.binasbas@bisu.edu.ph', '0981278311', '', '2025-10-03 01:19:07'),
(3, 7, 1, 1, 'Kimberly', 'Mante', NULL, '2004-07-18', 'vicvictacatane@gmail.com', '09937057361', '', '2025-10-07 07:48:24'),
(4, 8, 1, 1, 'LUCIANO', 'CANAMOCAN', 'C', '2025-09-30', 'lucianocanamocanjr@gmail.com', '0936 684 9713', '', '2025-10-08 03:37:41');

-- --------------------------------------------------------

--
-- Table structure for table `resident_family_additions`
--

CREATE TABLE `resident_family_additions` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `bhw_id` int(11) DEFAULT NULL COMMENT 'BHW who approved/rejected',
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `middle_initial` varchar(5) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Resident-initiated family member additions pending BHW approval';

--
-- Dumping data for table `resident_family_additions`
--

INSERT INTO `resident_family_additions` (`id`, `resident_id`, `full_name`, `relationship`, `date_of_birth`, `status`, `bhw_id`, `rejection_reason`, `created_at`, `updated_at`, `approved_at`, `rejected_at`, `first_name`, `middle_initial`, `last_name`) VALUES
(1, 1, 'Jaycho Carido', 'Brother', '2001-12-01', 'approved', 2, NULL, '2025-10-07 08:34:57', '2025-10-08 03:37:33', '2025-10-08 03:37:33', NULL, 'Jaycho', '', 'Carido');

-- --------------------------------------------------------

--
-- Table structure for table `senior_allocations`
--

CREATE TABLE `senior_allocations` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `bhw_id` int(11) NOT NULL,
  `status` enum('pending','released','expired','returned') NOT NULL DEFAULT 'pending',
  `must_claim_before` date NOT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `value_text` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `value_text`, `updated_at`) VALUES
(1, 'brand_name', 'MediTrack', '2025-09-24 00:14:36'),
(2, 'brand_logo_path', NULL, '2025-09-24 00:14:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','bhw','resident') NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `middle_initial` varchar(10) DEFAULT NULL COMMENT 'Middle initial or middle name',
  `profile_image` varchar(255) DEFAULT NULL,
  `purok_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `middle_initial`, `profile_image`, `purok_id`, `created_at`) VALUES
(1, 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'Super', 'Admin', NULL, 'uploads/profiles/profile_1_1763382627.jpg', NULL, '2025-09-23 02:05:22'),
(2, 's2peed3@gmail.com', '$2y$10$VLLynkZwldSlf1w3R7OOhe.rBMNA5fIITjIQ6/JpJA7APyseiKX6K', 'bhw', 'Ann', 'Canamucan', NULL, NULL, 1, '2025-09-23 02:29:01'),
(4, 's2peed5@gmail.com', '$2y$10$hQCDNu7NxJSAKRDuDbX0O.a4kz2SN4PycumnbLkArr0IASbS4ahxK', 'resident', 'Axl', 'Tag-at', NULL, 'uploads/profiles/profile_4_1760273418.jfif', 1, '2025-09-23 08:47:33'),
(6, 'gardson.binasbas@bisu.edu.ph', '$2y$10$BMPM4ivGkSplxNscVjkKhumiuZ0EcmMPzdUio407iKxTgTPnWVZme', 'resident', 'Gardson', 'Binasbas', NULL, NULL, 1, '2025-10-03 01:19:07'),
(7, 'vicvictacatane@gmail.com', '$2y$10$ubc6V951T9m/x5DhG7jY3.e1BgXls/R69D2S37MbXKwlNc/utH3Na', 'resident', 'Kimberly', 'Mante', NULL, NULL, 1, '2025-10-07 07:48:24'),
(8, 'lucianocanamocanjr@gmail.com', '$2y$10$Cb6HtKKDT7pSonkLXsT6ouFwVphBRY9tvqUogJvSim4wsUngR64qC', 'super_admin', 'LUCIANO', 'CANAMOCAN', 'C', NULL, 1, '2025-10-08 03:37:41');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_residents_with_senior`
-- (See below for the actual view)
--
CREATE TABLE `v_residents_with_senior` (
`id` int(11)
,`user_id` int(11)
,`barangay_id` int(11)
,`purok_id` int(11)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`date_of_birth` date
,`email` varchar(191)
,`phone` varchar(50)
,`address` varchar(255)
,`created_at` timestamp
,`is_senior` int(1)
);

-- --------------------------------------------------------

--
-- Structure for view `inventory_summary`
--
DROP TABLE IF EXISTS `inventory_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `inventory_summary`  AS SELECT `m`.`id` AS `medicine_id`, `m`.`name` AS `medicine_name`, `m`.`description` AS `description`, `m`.`image_path` AS `image_path`, coalesce(sum(case when `mb`.`quantity_available` > 0 and `mb`.`expiry_date` > curdate() then `mb`.`quantity_available` else 0 end),0) AS `current_stock`, coalesce(sum(case when `it`.`transaction_type` = 'IN' then `it`.`quantity` else 0 end),0) AS `total_received`, coalesce(sum(case when `it`.`transaction_type` = 'OUT' then abs(`it`.`quantity`) else 0 end),0) AS `total_dispensed`, coalesce(sum(case when `mb`.`expiry_date` <= curdate() and `mb`.`quantity_available` > 0 then `mb`.`quantity_available` else 0 end),0) AS `expired_stock`, coalesce(sum(case when `mb`.`expiry_date` between curdate() and curdate() + interval 30 day and `mb`.`quantity_available` > 0 then `mb`.`quantity_available` else 0 end),0) AS `expiring_soon`, CASE WHEN coalesce(sum(case when `mb`.`quantity_available` > 0 AND `mb`.`expiry_date` > curdate() then `mb`.`quantity_available` else 0 end),0) < 10 THEN 1 ELSE 0 END AS `is_low_stock`, max(`it`.`created_at`) AS `last_transaction_date`, count(distinct `mb`.`id`) AS `total_batches`, count(distinct case when `mb`.`quantity_available` > 0 and `mb`.`expiry_date` > curdate() then `mb`.`id` end) AS `active_batches` FROM ((`medicines` `m` left join `medicine_batches` `mb` on(`m`.`id` = `mb`.`medicine_id`)) left join `inventory_transactions` `it` on(`m`.`id` = `it`.`medicine_id`)) WHERE `m`.`is_active` = 1 GROUP BY `m`.`id`, `m`.`name`, `m`.`description`, `m`.`image_path` ;

-- --------------------------------------------------------

--
-- Structure for view `v_residents_with_senior`
--
DROP TABLE IF EXISTS `v_residents_with_senior`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_residents_with_senior`  AS SELECT `r`.`id` AS `id`, `r`.`user_id` AS `user_id`, `r`.`barangay_id` AS `barangay_id`, `r`.`purok_id` AS `purok_id`, `r`.`first_name` AS `first_name`, `r`.`last_name` AS `last_name`, `r`.`date_of_birth` AS `date_of_birth`, `r`.`email` AS `email`, `r`.`phone` AS `phone`, `r`.`address` AS `address`, `r`.`created_at` AS `created_at`, timestampdiff(YEAR,`r`.`date_of_birth`,curdate()) >= 60 AS `is_senior` FROM `residents` AS `r` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allocation_disbursals`
--
ALTER TABLE `allocation_disbursals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_disbursal_program` (`program_id`),
  ADD KEY `fk_disbursal_bhw` (`bhw_id`);

--
-- Indexes for table `allocation_disbursal_batches`
--
ALTER TABLE `allocation_disbursal_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_disbursal_batches_d` (`disbursal_id`),
  ADD KEY `fk_disbursal_batches_b` (`batch_id`);

--
-- Indexes for table `allocation_distributions`
--
ALTER TABLE `allocation_distributions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_allocation` (`program_id`,`resident_id`,`distribution_month`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_resident` (`resident_id`),
  ADD KEY `idx_month` (`distribution_month`);

--
-- Indexes for table `allocation_programs`
--
ALTER TABLE `allocation_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prog_med` (`medicine_id`),
  ADD KEY `fk_prog_barangay` (`barangay_id`),
  ADD KEY `fk_prog_purok` (`purok_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_date_range` (`start_date`,`end_date`);

--
-- Indexes for table `barangays`
--
ALTER TABLE `barangays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_notifications_user` (`user_id`);

--
-- Indexes for table `family_addition_notifications`
--
ALTER TABLE `family_addition_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notif_family_add` (`family_addition_id`),
  ADD KEY `idx_recipient` (`recipient_type`,`recipient_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `family_members`
--
ALTER TABLE `family_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_family_resident` (`resident_id`);

--
-- Indexes for table `inventory_adjustments`
--
ALTER TABLE `inventory_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_adj_medicine` (`medicine_id`),
  ADD KEY `fk_adj_batch` (`batch_id`),
  ADD KEY `fk_adj_user` (`adjusted_by`);

--
-- Indexes for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `acknowledged_by` (`acknowledged_by`),
  ADD KEY `idx_alert_type` (`alert_type`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_acknowledged` (`is_acknowledged`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_inv_trans_batch` (`batch_id`),
  ADD KEY `fk_inv_trans_user` (`created_by`),
  ADD KEY `idx_medicine_date` (`medicine_id`,`created_at`),
  ADD KEY `idx_type_date` (`transaction_type`,`created_at`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_medicine_name` (`name`),
  ADD KEY `medicines_category_fk` (`category_id`);

--
-- Indexes for table `medicine_batches`
--
ALTER TABLE `medicine_batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_batch_per_medicine` (`medicine_id`,`batch_code`),
  ADD KEY `idx_expiry` (`expiry_date`);

--
-- Indexes for table `medicine_categories`
--
ALTER TABLE `medicine_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `pending_family_members`
--
ALTER TABLE `pending_family_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pending_family_resident` (`pending_resident_id`);

--
-- Indexes for table `pending_residents`
--
ALTER TABLE `pending_residents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_pending_resident_barangay` (`barangay_id`),
  ADD KEY `fk_pending_resident_bhw` (`bhw_id`),
  ADD KEY `idx_pending_residents_purok` (`purok_id`),
  ADD KEY `idx_pending_residents_status` (`status`),
  ADD KEY `idx_pending_residents_email` (`email`),
  ADD KEY `idx_pending_residents_verification` (`email_verification_code`);

--
-- Indexes for table `puroks`
--
ALTER TABLE `puroks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_barangay_purok` (`barangay_id`,`name`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_request_resident` (`resident_id`),
  ADD KEY `fk_request_medicine` (`medicine_id`),
  ADD KEY `fk_request_bhw` (`bhw_id`),
  ADD KEY `fk_request_family_member` (`family_member_id`);

--
-- Indexes for table `request_fulfillments`
--
ALTER TABLE `request_fulfillments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fulfill_req` (`request_id`),
  ADD KEY `fk_fulfill_batch` (`batch_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_resident_user` (`user_id`),
  ADD KEY `fk_resident_barangay` (`barangay_id`),
  ADD KEY `fk_resident_purok` (`purok_id`);

--
-- Indexes for table `resident_family_additions`
--
ALTER TABLE `resident_family_additions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_res_family_add_bhw` (`bhw_id`),
  ADD KEY `idx_resident_status` (`resident_id`,`status`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `senior_allocations`
--
ALTER TABLE `senior_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_salloc_program` (`program_id`),
  ADD KEY `fk_salloc_resident` (`resident_id`),
  ADD KEY `fk_salloc_bhw` (`bhw_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_purok` (`purok_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allocation_disbursals`
--
ALTER TABLE `allocation_disbursals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `allocation_disbursal_batches`
--
ALTER TABLE `allocation_disbursal_batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `allocation_distributions`
--
ALTER TABLE `allocation_distributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `allocation_programs`
--
ALTER TABLE `allocation_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `barangays`
--
ALTER TABLE `barangays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `email_notifications`
--
ALTER TABLE `email_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `family_addition_notifications`
--
ALTER TABLE `family_addition_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `family_members`
--
ALTER TABLE `family_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_adjustments`
--
ALTER TABLE `inventory_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `medicine_batches`
--
ALTER TABLE `medicine_batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `medicine_categories`
--
ALTER TABLE `medicine_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pending_family_members`
--
ALTER TABLE `pending_family_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pending_residents`
--
ALTER TABLE `pending_residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `puroks`
--
ALTER TABLE `puroks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `request_fulfillments`
--
ALTER TABLE `request_fulfillments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `resident_family_additions`
--
ALTER TABLE `resident_family_additions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `senior_allocations`
--
ALTER TABLE `senior_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allocation_disbursals`
--
ALTER TABLE `allocation_disbursals`
  ADD CONSTRAINT `fk_disbursal_bhw` FOREIGN KEY (`bhw_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_disbursal_program` FOREIGN KEY (`program_id`) REFERENCES `allocation_programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `allocation_disbursal_batches`
--
ALTER TABLE `allocation_disbursal_batches`
  ADD CONSTRAINT `fk_disbursal_batches_b` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_disbursal_batches_d` FOREIGN KEY (`disbursal_id`) REFERENCES `allocation_disbursals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `allocation_distributions`
--
ALTER TABLE `allocation_distributions`
  ADD CONSTRAINT `allocation_distributions_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `allocation_programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `allocation_distributions_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `allocation_distributions_ibfk_3` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `allocation_distributions_ibfk_4` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `allocation_programs`
--
ALTER TABLE `allocation_programs`
  ADD CONSTRAINT `fk_prog_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_prog_med` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prog_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD CONSTRAINT `fk_email_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `family_addition_notifications`
--
ALTER TABLE `family_addition_notifications`
  ADD CONSTRAINT `fk_notif_family_add` FOREIGN KEY (`family_addition_id`) REFERENCES `resident_family_additions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `family_members`
--
ALTER TABLE `family_members`
  ADD CONSTRAINT `fk_family_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_adjustments`
--
ALTER TABLE `inventory_adjustments`
  ADD CONSTRAINT `fk_adj_batch` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_adj_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_adj_user` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  ADD CONSTRAINT `inventory_alerts_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_alerts_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_alerts_ibfk_3` FOREIGN KEY (`acknowledged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `fk_inv_trans_batch` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_inv_trans_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inv_trans_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medicines`
--
ALTER TABLE `medicines`
  ADD CONSTRAINT `medicines_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `medicine_batches`
--
ALTER TABLE `medicine_batches`
  ADD CONSTRAINT `fk_batch_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pending_family_members`
--
ALTER TABLE `pending_family_members`
  ADD CONSTRAINT `fk_pending_family_resident` FOREIGN KEY (`pending_resident_id`) REFERENCES `pending_residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pending_residents`
--
ALTER TABLE `pending_residents`
  ADD CONSTRAINT `fk_pending_resident_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pending_resident_bhw` FOREIGN KEY (`bhw_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pending_resident_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `puroks`
--
ALTER TABLE `puroks`
  ADD CONSTRAINT `fk_purok_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `fk_request_bhw` FOREIGN KEY (`bhw_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_request_family_member` FOREIGN KEY (`family_member_id`) REFERENCES `family_members` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_request_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_request_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `request_fulfillments`
--
ALTER TABLE `request_fulfillments`
  ADD CONSTRAINT `fk_fulfill_batch` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fulfill_req` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `residents`
--
ALTER TABLE `residents`
  ADD CONSTRAINT `fk_resident_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_resident_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_resident_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `resident_family_additions`
--
ALTER TABLE `resident_family_additions`
  ADD CONSTRAINT `fk_res_family_add_bhw` FOREIGN KEY (`bhw_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_res_family_add_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `senior_allocations`
--
ALTER TABLE `senior_allocations`
  ADD CONSTRAINT `fk_salloc_bhw` FOREIGN KEY (`bhw_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_salloc_program` FOREIGN KEY (`program_id`) REFERENCES `allocation_programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_salloc_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
