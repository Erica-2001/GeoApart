-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 20, 2025 at 08:36 AM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u451427308_geoapart`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'geoapart544@gmail.com', '$2y$10$wM42PAPisT.DKqoXfxkotOnRnMveCd352CwwWbWTLi1jg66oNRyfe', '2025-03-14 03:24:54');

-- --------------------------------------------------------

--
-- Table structure for table `apartments`
--

CREATE TABLE `apartments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `location_link` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `features` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'default_apartment.jpg',
  `landlord_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `apartment_type` enum('Studio','Loft','Duplex','Micro') NOT NULL DEFAULT 'Studio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apartments`
--

INSERT INTO `apartments` (`id`, `name`, `location`, `location_link`, `price`, `features`, `image`, `landlord_id`, `created_at`, `apartment_type`) VALUES
(49, 'LBB\'s Apartment', 'Kumintang Ibaba Batangas City', 'https://www.google.com/maps/dir//13.7633244,121.0623363/@13.7633433,121.0623363,138m/data=!3m1!1e3?entry=ttu&g_ep=EgoyMDI1MDQwMS4wIKXMDSoASAFQAw%3D%3D', 2500.00, 'Bed frame\r\nPantry\r\nBathroom\r\nRefrigerator\r\nAir conditioner\r\nOwn sub-meter\r\nWi-Fi\r\nParking slot\r\nVisitors allowed : YES\r\nPet-friendly : YES', 'default_apartment.jpg', 9, '2025-04-04 08:26:15', 'Loft'),
(72, 'LBB\'s Apartment ', 'Shekel Strett, Sitio Hilltop, Kumintang Ibba', 'https://www.google.com/maps/place/13%C2%B045\'46.0%22N+121%C2%B003\'43.3%22E/@13.76279,121.062036,15z/data=!4m4!3m3!8m2!3d13.76279!4d121.062036?g_ep=Eg1tbF8yMDI1MDUwNV8wIOC7DCoASAJQAQ%3D%3D', 1500.00, 'Bed Frame\r\nPantry\r\nBathroom\r\nRefrigerator\r\nAircon\r\nOwn Sub-Meter\r\nWiFi\r\nParking Slot\r\nVisitors Allowed\r\nPet Friendly', 'default_apartment.jpg', 32, '2025-05-08 07:14:25', 'Loft'),
(75, 'Aila\'s Apartment 1 ', 'Shekel Street,  Sitio Hilltop, Barangay Kumintang Ibaba, Batangas City ', 'https://www.google.com/maps/place/Zmiles+\'N+More+Dental+Clinic,+Lungsod+ng+Batangas,+Batangas/@13.7629521,121.0679065,13z/data=!4m2!3m1!1s0x33bd0543569c6a69:0x57537565df050cce?g_ep=Eg1tbF8yMDI1MDUwNV8xIJvbDyoASAJQAQ%3D%3D', 8000.00, '2 Bed Frame\r\nPantry\r\n1 Bathroom\r\nRefrigerator\r\nAircon \r\nOwn Sub-Meter\r\nParking Slot\r\nVisitors Allowed', 'default_apartment.jpg', 46, '2025-05-14 02:58:54', 'Micro'),
(76, 'Aila\'s Apartment 2', 'Shekel Street, Sitio Hilltop, Brgy. Kumintang Ibaba, Batangas City', 'https://www.google.com/maps/place/13%C2%B045\'48.2%22N+121%C2%B003\'46.9%22E/@13.763376,121.063013,15z/data=!4m4!3m3!8m2!3d13.763376!4d121.063013?g_ep=Eg1tbF8yMDI1MDUwNV8xIJvbDyoASAJQAQ%3D%3D', 8000.00, '2 Double Deck Bed Frames\r\n1 Comfort room\r\nPantry\r\nOwn Submeter\r\nAir-conditioned \r\nVisitors Allowed ', 'default_apartment.jpg', 46, '2025-05-14 03:05:24', 'Duplex'),
(77, 'Baranda\'s Apartment ', 'Sitio Hilltop, Barangay Kumintang Ibaba, Batangas City ', 'https://www.google.com/maps/place/13%C2%B045\'47.8%22N+121%C2%B003\'42.9%22E/@13.763281,121.061925,15z/data=!4m4!3m3!8m2!3d13.763281!4d121.061925?g_ep=Eg1tbF8yMDI1MDUwNV8xIJvbDyoASAJQAQ%3D%3D', 6000.00, 'Bed Frame, \r\nPantry, \r\nBathroom, \r\nRefrigerator, \r\nWiFi, \r\nVisitors Allowed', 'default_apartment.jpg', 55, '2025-05-14 04:35:07', 'Duplex'),
(79, 'Shaira\'s Apartment ', 'Hilltop ', '', 8000.00, 'Pantry\r\nBathroom\r\nRefrigerator\r\nAircon', 'default_apartment.jpg', 70, '2025-05-18 23:29:32', 'Duplex'),
(81, 'testing', 'batangas city', 'https://maps.app.goo.gl/8yJQQFCtmwCTshtTA', 5000.00, 'Pantry, Bathroom, Refrigerator', 'default_apartment.jpg', 55, '2025-05-19 15:16:57', 'Studio'),
(82, 'testing2', 'batangas city', 'https://maps.app.goo.gl/8yJQQFCtmwCTshtTA', 123.00, 'Pantry', 'default_apartment.jpg', 55, '2025-05-19 15:21:12', 'Studio');

-- --------------------------------------------------------

--
-- Table structure for table `apartment_images`
--

CREATE TABLE `apartment_images` (
  `id` int(11) NOT NULL,
  `apartment_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apartment_images`
--

INSERT INTO `apartment_images` (`id`, `apartment_id`, `image_path`, `uploaded_at`) VALUES
(28, 49, 'uploads/1743755175_1743334594_images.jpg', '2025-04-04 08:26:15'),
(43, 72, 'uploads/1746688465_FB_IMG_1746686543565.jpg', '2025-05-08 07:14:25'),
(46, 75, 'uploads/1747191534_Messenger_creation_700805015786170.jpeg', '2025-05-14 02:58:54'),
(47, 76, 'uploads/1747191924_received_1863024071197883.jpeg', '2025-05-14 03:05:24'),
(48, 77, 'uploads/1747197307_Messenger_creation_1241740191287567.jpeg', '2025-05-14 04:35:07'),
(50, 79, 'uploads/1747610972_images (1).jpeg', '2025-05-18 23:29:32'),
(52, 81, 'uploads/1747667817_Screenshot 2025-05-19 231643.png', '2025-05-19 15:16:57'),
(53, 82, 'uploads/1747668072_Screenshot 2025-05-19 231643.png', '2025-05-19 15:21:12');

-- --------------------------------------------------------

--
-- Table structure for table `apartment_units`
--

CREATE TABLE `apartment_units` (
  `id` int(11) NOT NULL,
  `apartment_id` int(11) NOT NULL,
  `unit_number` varchar(50) NOT NULL,
  `unit_status` enum('Available','Pending','Occupied') NOT NULL DEFAULT 'Available',
  `unit_price` decimal(10,2) NOT NULL,
  `unit_features` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apartment_units`
--

INSERT INTO `apartment_units` (`id`, `apartment_id`, `unit_number`, `unit_status`, `unit_price`, `unit_features`, `created_at`) VALUES
(19, 49, '101', 'Available', 2500.00, 'Bed frame\r\nPantry\r\nBathroom\r\nRefrigerator\r\nAir conditioner\r\nOwn sub-meter\r\nWi-Fi\r\nParking slot\r\nVisitors allowed : YES\r\nPet-friendly : YES', '2025-04-04 08:26:41'),
(35, 72, 'Unit 1', 'Available', 1500.00, '1 Bed Frame\r\n1 Comfort Room\r\nOwn Submeter\r\nWifi', '2025-05-08 07:18:02'),
(39, 75, 'Unit 1', 'Occupied', 8000.00, '2 Double Deck Bed frames\r\n1 Comfort Room\r\nKitchen\r\nVisitors allowed \r\nOwn Submeter', '2025-05-14 03:06:09'),
(40, 75, 'Unit number 2', 'Occupied', 8000.00, '2 Double Deck Bed frames\r\n1 Comfort Room\r\nOwn Submeter \r\nKitchen\r\nVisitors Allowed \r\nParking Slot', '2025-05-14 03:09:11'),
(41, 76, '1', 'Available', 8000.00, '2 Double Deck Bed Frames\r\n1 Comfort Room\r\nPantry\r\nVisitors Allowed\r\nOwn Submeter', '2025-05-14 03:17:16'),
(42, 76, '2', 'Available', 8000.00, '2 Double Deck Bed Frames\r\n1 Confront \r\nOwn Submeter\r\nVisitors Allowed', '2025-05-14 03:20:35'),
(43, 75, '3', 'Occupied', 8000.00, '2 Double Deck Bed frame\r\n1 Comfort Room\r\nKitchen \r\nOwn Submeter \r\nVisitors liwed', '2025-05-14 03:23:43'),
(44, 76, '3', 'Available', 8000.00, '', '2025-05-14 03:27:20'),
(45, 77, '1', 'Pending', 6000.00, '', '2025-05-14 04:36:08'),
(46, 77, '2', 'Available', 6000.00, '', '2025-05-14 04:37:29'),
(47, 77, '3', 'Available', 6000.00, '', '2025-05-14 04:38:53'),
(51, 79, '1', 'Available', 8000.00, '2 bed rooms\r\n1 Comfort room\r\nKitchen \r\nPet\'s allowed \r\nVisitors Allowed', '2025-05-18 23:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(73, 30, '📩 A tenant has requested to rent Unit #143. Please review and approve the request.', 0, '2025-05-06 14:53:24'),
(86, 30, '📩 A tenant has requested to rent Unit #144. Please review and approve the request.', 0, '2025-05-07 08:33:47'),
(96, 49, '❌ Your rental request was rejected by Landlord. Reason: No reason provided.', 0, '2025-05-14 03:37:23'),
(97, 46, '📩 A tenant has requested to rent Unit #Unit 1. Please review and approve the request.', 0, '2025-05-14 03:38:50'),
(98, 49, '🎉 Your rental request was approved by Landlord. Welcome to your new unit!', 0, '2025-05-14 03:41:56'),
(99, 46, '📩 A tenant has requested to rent Unit #Unit number 2. Please review and approve the request.', 0, '2025-05-14 05:02:43'),
(100, 57, '🎉 Your rental request was approved by Landlord. Welcome to your new unit!', 0, '2025-05-14 05:03:25'),
(101, 55, '📩 A tenant has requested to rent Unit #1. Please review and approve the request.', 0, '2025-05-18 16:00:56');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('Admin','Landlord') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` enum('Tenant') NOT NULL DEFAULT 'Tenant',
  `apartment_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Reviewing','Paid','Overdue','Pastdue') DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `sender_id`, `sender_type`, `receiver_id`, `receiver_type`, `apartment_id`, `unit_id`, `total_amount`, `payment_status`, `payment_date`, `payment_proof`) VALUES
(102, 49, '', 46, 'Tenant', 75, 39, 8000.00, 'Pending', '2025-05-14 03:38:50', NULL),
(103, 57, '', 46, 'Tenant', 75, 40, 8000.00, 'Pending', '2025-05-14 05:02:43', NULL),
(104, 65, '', 55, 'Tenant', 77, 45, 6000.00, 'Pending', '2025-05-18 16:00:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tenant_rentals`
--

CREATE TABLE `tenant_rentals` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `landlord_id` int(11) NOT NULL,
  `apartment_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `rental_start_date` date NOT NULL DEFAULT curdate(),
  `rental_end_date` date DEFAULT NULL,
  `status` enum('Active','Ended') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_rentals`
--

INSERT INTO `tenant_rentals` (`id`, `tenant_id`, `landlord_id`, `apartment_id`, `unit_id`, `rental_start_date`, `rental_end_date`, `status`, `created_at`) VALUES
(59, 49, 46, 75, 39, '2025-05-14', NULL, 'Active', '2025-05-14 03:38:50'),
(60, 57, 46, 75, 40, '2025-05-14', NULL, 'Active', '2025-05-14 05:02:43'),
(61, 65, 55, 77, 45, '2025-05-18', NULL, 'Active', '2025-05-18 16:00:56');

-- --------------------------------------------------------

--
-- Table structure for table `unit_images`
--

CREATE TABLE `unit_images` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit_images`
--

INSERT INTO `unit_images` (`id`, `unit_id`, `image_path`, `uploaded_at`) VALUES
(44, 19, 'uploads/units/1743755201_Sierra_HDR_Panorama_DFX8048_2280x819_Q40_wm_mini - Copy (2).jpg', '2025-04-04 08:26:41'),
(45, 19, 'uploads/units/1743755201_Sierra_HDR_Panorama_DFX8048_2280x819_Q40_wm_mini - Copy.jpg', '2025-04-04 08:26:41'),
(46, 19, 'uploads/units/1743755201_Sierra_HDR_Panorama_DFX8048_2280x819_Q40_wm_mini.jpg', '2025-04-04 08:26:41'),
(64, 35, 'uploads/units/1746688744_IMG_20250508_144303.jpg', '2025-05-08 07:19:04'),
(68, 41, 'uploads/units/1747192760_received_1205090694441867.jpeg', '2025-05-14 03:19:20'),
(69, 43, 'uploads/units/1747193143_Messenger_creation_700805015786170.jpeg', '2025-05-14 03:25:43'),
(76, 51, 'uploads/units/1747611164_images (2).jpeg', '2025-05-18 23:32:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('Tenant','Landlord') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `selected_apartment` int(11) DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `mobile`, `name`, `email`, `password`, `user_type`, `created_at`, `selected_apartment`, `proof_image`, `status`) VALUES
(8, '09123456789', 'Joy Baranda', 'joy@gmail.com', '$2y$10$up2tGVs1a0yC.j7nUViv4eOEsWqOZXpHOnE7NbjihFpG8Oe2K.Kei', 'Landlord', '2025-03-30 11:13:28', NULL, NULL, 'Approved'),
(9, '09760649022', 'Luis Bagsit Bagui', 'luis@gmail.com', '$2y$10$pIqaaYQIGPIVk7xMf0WjzuMOHoKqipMVApqrMLcHJp1Tdqj6OewgO', 'Landlord', '2025-03-30 11:25:28', NULL, NULL, 'Approved'),
(30, '09910135267', 'Laida Robledo', 'landlordga97@gmail.com', '$2y$10$53SqL11H1gzSHJK8q5igOu7MvfHgzv4UdMVLWwLZlGQle7Aqa5KQy', 'Landlord', '2025-05-06 14:01:11', NULL, 'uploads/proofs/1746540071_Screenshot_2025-05-06-21-59-38-30.jpg', 'Approved'),
(32, '09760649022', 'Luis Bagui', 'luisbagui346@gmail.com', '$2y$10$VS1SpGmKxCMpt1/ULPgCs.taBHZ90D.4FgWEkzya.fyfglxGuTXSm', 'Landlord', '2025-05-08 05:46:22', NULL, 'uploads/proofs/1746683182_e8845fb3-8565-4fbe-9d14-b979361146f5.jpeg', 'Approved'),
(45, '09630061075', 'ERICA ANNE DE CASTRO', 'eannedecastro13@gmail.com', '$2y$10$uDr.lM/A5Psf8heKljsCBO9dzlPffzrJ/oeEjLPQT4JsHRpe7BMXK', 'Landlord', '2025-05-13 20:48:13', NULL, 'uploads/proofs/1747169293_received_1006714138250697.jpeg', 'Approved'),
(46, '09988584015', 'Rosemarie Donayre', 'rosemariedonayre90@gmail.com', '$2y$10$RIkyVX9SJxp7HFA0IIVYWebs7HWGsSL49xJqFDrxmxhot0SjleDlW', 'Landlord', '2025-05-14 01:50:10', NULL, 'uploads/proofs/1747187410_IMG20250514094933.jpg', 'Approved'),
(47, '0997628419', 'Nicolette Yvonne Javier', 'nicolettejavier14@gmail.com', '$2y$10$0Sxy6M04QFd4wSFI5q.tteyuVE3ogoTv22BTDsUCWfv3XaPmFdklq', 'Tenant', '2025-05-14 02:24:59', NULL, 'uploads/proofs/1747189499_Messenger_creation_FF5F3B95-5C23-47B4-A93C-E109310D6440.jpeg', 'Approved'),
(48, '09477396115', 'Keisha Dannielle Manalo', 'keishamnl@gmail.com', '$2y$10$puaaArkH.l9O94hD7AYRHeWjb/fCdubska6Rv0IFwzcFcb64M4jCa', 'Tenant', '2025-05-14 02:32:05', NULL, 'uploads/proofs/1747189925_Messenger_creation_8318A83A-84F2-4D76-A5DC-FD17ABDEB446.jpeg', 'Approved'),
(49, '09107984782', 'Jennifer B. Porto ', 'jenniferporto42@gmail.com', '$2y$10$v4inyWq594GwjNly4uzNoekpUg8EBdEKXIY4lNkYD.7s3Sci8LEvO', 'Tenant', '2025-05-14 02:33:51', NULL, 'uploads/proofs/1747190031_temp_capture_image.jpg', 'Approved'),
(51, '09923710218', 'JHUNE MENDELL ATIENZA', 'jmatienza021120@gmail.com', '$2y$10$S0OuqIfEzpGR0ghiy5Uc9OA8XwXEZN2HZUUyvDZ5NV.MPbYVbILVq', 'Tenant', '2025-05-14 03:05:18', NULL, 'uploads/proofs/1747191918_id.png', 'Approved'),
(52, 'jamesclintonsar', 'James Clinton P. Sarmiento', 'jamesclintonsarmiento@gmail.com', '$2y$10$yu1WXFxEltniwI8YY7MsRuK0itlNJv5hKA.mofKj199m77OVVYIO.', 'Tenant', '2025-05-14 03:08:01', NULL, 'uploads/proofs/1747192081_received_995824925294580.jpeg', 'Pending'),
(55, '09664332056', 'Eduardo Baranda', 'barandaeddie570@gmail.com', '$2y$10$yUwkyKSKpZR5MKFXjKBll.v.ZIR3dN74XNVO.rf8BPFIH7tX1ONqS', 'Landlord', '2025-05-14 04:15:11', NULL, 'uploads/proofs/1747196111_IMG_20250514_115835.jpg', 'Approved'),
(56, '09918146085', 'Vince', '20-05095@g.batstate-u.edu.ph', '$2y$10$2jPCj0m7.BXUsw/JMYIXJesAFIvRZykL/zdoVuwpdeZAEsqpqhVI.', 'Tenant', '2025-05-14 04:28:26', NULL, 'uploads/proofs/1747196906_Screenshot 2025-05-14 122810.png', 'Approved'),
(57, '09392875703', 'Allyza Camille Comia', 'allyzacomia04@gmail.com', '$2y$10$B2fUVBgkB5heqrH4Usnlx.6NMYX9Q81d7E2SWZUi4uGylhb13Y/sy', 'Tenant', '2025-05-14 04:59:24', NULL, 'uploads/proofs/1747198764_temp_capture_image.jpg', 'Approved'),
(58, '09511003500', 'John Carl Ortiz', 'johncarlortiz28@gmail.com', '$2y$10$6xKVMR27t6gVgNQEgMqMvuqo8OEBvUb0mldpvBPhwfNfR3omNd88K', 'Tenant', '2025-05-14 05:39:18', NULL, 'uploads/proofs/1747201158_image.jpg', 'Pending'),
(59, '09300472891', 'Eloiza de Castro', 'eloizadecastro94@gmail.com', '$2y$10$akdTjxKjTgr5SFsTFQbOVeJFdbGBdj6k0WePyTIbplf1NPV7GhUYu', 'Tenant', '2025-05-14 05:46:17', NULL, 'uploads/proofs/1747201577_1747201400519.jpg', 'Pending'),
(60, '09452783315', 'Rancel Rioflorido ', 'rancelrioflorido@gmail.com', '$2y$10$Y6OUvOVmahnXAxhbDbAp4ei0tNBB8c6AHCREVcXvm4NsdMNT0opyS', 'Tenant', '2025-05-14 05:47:29', NULL, 'uploads/proofs/1747201649_IMG_20240229_110458.jpg', 'Pending'),
(61, '09485897660', 'CARL LESTER IDANAN', 'carllestercarandang@gmail.com', '$2y$10$/hzd6y9f1tEz9NkFLbL45.nbWbAGfHGJ.wRIu0TNvXpnUXk5Vfatm', 'Tenant', '2025-05-14 05:55:25', NULL, 'uploads/proofs/1747202125_IMG_20250514_135435.jpg', 'Pending'),
(62, '09672560399', 'Jhon Juven D. Rodriguez ', 'jhonjuven@gmail.com', '$2y$10$3yOkQzT5lykkwfnNT4/yQuZxLItvoksZjUeTLIWedENsJbFE5.ERG', 'Tenant', '2025-05-14 06:06:18', NULL, 'uploads/proofs/1747202778_IMG_20250514_140537.jpg', 'Pending'),
(63, '09157986531', 'Bryan Jay Harvey Genil', 'harveygenil7@gmail.com', '$2y$10$zYSIE1y3yvZ8wL/AQj/9mOR7gxtljiRQedUbIvGhFno8HYfzisxZ2', 'Tenant', '2025-05-14 06:55:26', NULL, 'uploads/proofs/1747205726_IMG_4532.jpeg', 'Pending'),
(64, '1154564', 'sample', 'tenant@gmail.com', '$2y$10$S6hG60Mq5A/fvsi5vCoyTOsF1i5rX92AugXIrn3r2lOa7BD0G9q7K', 'Tenant', '2025-05-18 11:08:07', NULL, 'uploads/proofs/1747566487_Picture4.jpg', 'Approved'),
(65, '09171050617', 'Rizu Rams', 'rizu.ramos@gmail.com', '$2y$10$tehZR1poXXLE9VY92XlGzu0QREsGSJ1czIg7gWMiU8TMiBy2VIDFm', 'Tenant', '2025-05-18 15:48:07', NULL, 'uploads/proofs/1747583287_IMG_20250518_234725.jpg', 'Approved'),
(69, '+639335063897', 'Janine Ingles', 'janinemae07ingles@gmail.com', '$2y$10$7VB7/OpfV/NZ14DEzuIKzu2OzSh55qEjZO4alx.pyPC.HBj3rybPe', 'Tenant', '2025-05-18 18:48:24', NULL, 'uploads/proofs/1747594104_IMG_20250519_024226.jpg', 'Pending'),
(70, '099755151289', 'SHAIRA KAE DESCALZO ', 'descalzoshaira@gmail.com', '$2y$10$lzqQhaIMpToll.AUBbaIAOMVfOCO25MZy8wdH85dr7KU2vurJk/7K', 'Landlord', '2025-05-18 23:19:22', NULL, 'uploads/proofs/1747610362_IMG20250514115848.jpg', 'Approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `apartments`
--
ALTER TABLE `apartments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `landlord_id` (`landlord_id`);

--
-- Indexes for table `apartment_images`
--
ALTER TABLE `apartment_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `apartment_id` (`apartment_id`);

--
-- Indexes for table `apartment_units`
--
ALTER TABLE `apartment_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `apartment_id` (`apartment_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `apartment_id` (`apartment_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `tenant_rentals`
--
ALTER TABLE `tenant_rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `landlord_id` (`landlord_id`),
  ADD KEY `apartment_id` (`apartment_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `unit_images`
--
ALTER TABLE `unit_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `selected_apartment` (`selected_apartment`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `apartments`
--
ALTER TABLE `apartments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `apartment_images`
--
ALTER TABLE `apartment_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `apartment_units`
--
ALTER TABLE `apartment_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `tenant_rentals`
--
ALTER TABLE `tenant_rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `unit_images`
--
ALTER TABLE `unit_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apartments`
--
ALTER TABLE `apartments`
  ADD CONSTRAINT `apartments_ibfk_1` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `apartment_images`
--
ALTER TABLE `apartment_images`
  ADD CONSTRAINT `apartment_images_ibfk_1` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `apartment_units`
--
ALTER TABLE `apartment_units`
  ADD CONSTRAINT `apartment_units_ibfk_1` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_4` FOREIGN KEY (`unit_id`) REFERENCES `apartment_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenant_rentals`
--
ALTER TABLE `tenant_rentals`
  ADD CONSTRAINT `tenant_rentals_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_rentals_ibfk_2` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_rentals_ibfk_3` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_rentals_ibfk_4` FOREIGN KEY (`unit_id`) REFERENCES `apartment_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `unit_images`
--
ALTER TABLE `unit_images`
  ADD CONSTRAINT `unit_images_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `apartment_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`selected_apartment`) REFERENCES `apartments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
