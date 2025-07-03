@-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2025 at 11:13 PM
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
-- Database: `rems`
--

-- --------------------------------------------------------

--
-- Table structure for table `about`
--

CREATE TABLE `about` (
  `id` int(10) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about`
--

INSERT INTO `about` (`id`, `title`, `content`, `image`) VALUES
(11, 'Legacy Builders Realestate', '<p class=\"has-text-align-left has-base-2-color has-text-color has-link-color wp-elements-121d0131178b237cb90aafcb0a140493\">Legacy Builders is an established property development company focused on creating homes packed with functionality, convenience and comfort for today&rsquo;s modern lifestyle seeker and savvy investor.</p>\r\n<p class=\"has-base-2-color has-text-color has-link-color wp-elements-8aae7fe0ec3951851f9beac044f8c10a\">The innovation-led, future-focused Legacy Builders Real Estate established in 2022, the company is responsible for more than 5 large-scale projects across Addis Ababa.</p>\r\n<p class=\"has-base-2-color has-text-color has-link-color wp-elements-67b0642f9d09f5fd3ad7c36370d17189\">Legacy Builders Real Estate is introducing world-class luxury high-end apartments to Addis Ababa&rsquo;s key areas.&nbsp; In doing so, we strive to fulfill the needs of luxury living and provide long term profitable investment opportunities in real estate.</p>', 'legac.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `aid` int(10) NOT NULL,
  `auser` varchar(50) NOT NULL,
  `aemail` varchar(50) NOT NULL,
  `apass` varchar(255) DEFAULT NULL,
  `aphone` varchar(15) NOT NULL,
  `utype` varchar(50) DEFAULT NULL,
  `uimage` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`aid`, `auser`, `aemail`, `apass`, `aphone`, `utype`, `uimage`) VALUES
(15, 'admin', 'admin@gmail.com', '$2y$10$mA3mh17hfV17N5QxXfaam.ulaerhEwSIsV8wP.aE2bYJfEuqI.wY6', '', NULL, NULL); --hash for "password"

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_date` datetime DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `status` enum('pending','confirmed','canceled') DEFAULT 'pending',
  `cancelled_date` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `message` text DEFAULT NULL,
  `id_document` varchar(255) DEFAULT NULL,
  `agreement_accepted` tinyint(1) DEFAULT 0,
  `agreement_signed` tinyint(1) DEFAULT 0,
  `agreement_date` datetime DEFAULT NULL,
  `move_in_date` date DEFAULT NULL,
  `lease_term` int(11) DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT 'pending',
  `payment_transaction_id` varchar(100) DEFAULT NULL,
  `property_status` varchar(20) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `deposit_amount` decimal(10,2) DEFAULT NULL,
  `full_price` decimal(10,2) DEFAULT NULL,
  `deposit_paid` tinyint(1) DEFAULT 0,
  `remaining_balance` decimal(10,2) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `payment_phone` varchar(20) DEFAULT NULL,
  `payment_verified_date` datetime DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT 50.00,
  `booking_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--


-- --------------------------------------------------------

--
-- Table structure for table `city`
--

CREATE TABLE `city` (
  `cid` int(50) NOT NULL,
  `cname` varchar(100) NOT NULL,
  `sid` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `city`
--

INSERT INTO `city` (`cid`, `cname`, `sid`) VALUES
(9, 'Finfine', 3),
(10, 'Finfine', 2),
(11, 'Adama', 2),
(12, 'Fitche', 7),
(13, 'Finfine', 15);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `cid` int(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `message` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`cid`, `name`, `email`, `phone`, `subject`, `message`) VALUES
(7, 'contact', 'contact@gmail.com', '0909090909', 'contact.com', 'my is...'),
(8, 'me', 'em', '909', 'sub', 'message');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `fid` int(50) NOT NULL,
  `uid` int(50) NOT NULL,
  `fdescription` varchar(300) NOT NULL,
  `status` int(1) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`fid`, `uid`, `fdescription`, `status`, `date`) VALUES
(7, 28, 'This is a demo feedback in order to use set it as Testimonial for the site. Just a simply dummy text rather than using lorem ipsum text lines.', 1, '2025-02-23 16:07:08'),
(9, 29, 'yaadike naaf hin gallee', 1, '2025-03-07 15:33:30'),
(10, 37, 'hi manni gurguramu jirahii\r\n', 1, '2025-03-17 14:55:16'),
(11, 38, 'ko', 1, '2025-04-29 20:32:37'),
(12, 53, 'hojii keessan jaalladheera', 1, '2025-05-10 17:08:25');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `payment_date` datetime NOT NULL,
  `status` varchar(20) NOT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `pid` int(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `pcontent` longtext NOT NULL,
  `type` varchar(100) NOT NULL,
  `stype` varchar(100) NOT NULL,
  `bedroom` int(50) NOT NULL,
  `bathroom` int(50) NOT NULL,
  `kitchen` int(50) NOT NULL,
  `floor` varchar(50) NOT NULL,
  `size` int(50) NOT NULL,
  `price` int(50) NOT NULL,
  `location` varchar(200) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `feature` text DEFAULT NULL,
  `pimage` varchar(300) NOT NULL,
  `pimage1` varchar(300) NOT NULL,
  `pimage2` varchar(300) NOT NULL,
  `pimage3` varchar(255) DEFAULT NULL,
  `pimage4` varchar(255) DEFAULT NULL,
  `panorama_image` varchar(255) DEFAULT NULL,
  `uid` int(50) NOT NULL,
  `status` enum('available','booked') DEFAULT 'available',
  `mapimage` varchar(300) NOT NULL,
  `topmapimage` varchar(255) DEFAULT NULL,
  `groundmapimage` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `isFeatured` int(11) DEFAULT NULL,
  `tour_config` text DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `map_zoom` int(11) DEFAULT 14
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property`
--

INSERT INTO `property` (`pid`, `title`, `pcontent`, `type`, `stype`, `bedroom`, `bathroom`, `kitchen`, `floor`, `size`, `price`, `location`, `city`, `state`, `feature`, `pimage`, `pimage1`, `pimage2`, `pimage3`, `pimage4`, `panorama_image`, `uid`, `status`, `mapimage`, `topmapimage`, `groundmapimage`, `date`, `isFeatured`, `tour_config`, `latitude`, `longitude`, `map_zoom`) VALUES
(32, 'Mana Jireenyaa', '<p>.</p>', 'apartment', 'rent', 2, 2, 3, '1st Floor', 4325, 10000, 'birbirsa gooro', 'Oromia', 'Oromia', '<p>.</p>', '6817c3a1a6754.jpg', '6817c3a1a6ef4.jpg', '6817c3a1a75c3.jpg', '6817c3a1a8ff7.jpg', '6817c3a1a97e9.jpg', '6817c3a1ab3b2.jpg', 52, 'booked', '6817c3a1a9efc.jpg', '6817c3a1aa637.jpg', '6817c3a1aad07.jpg', '2025-03-26 20:43:50', 0, NULL, NULL, NULL, 14),
(35, 'villa', '<p><strong>Moora mana barumsa</strong></p>', 'apartment', 'rent', 6, 6, 1, '1st Floor', 65432, 22000, 'Bole', 'Addis Ababa', 'Oromia', '', '6822ea4a69a37.jpg', '6822ea4a6a1bb.jpg', '6822ea4a6a8f2.jpg', '6822ea4a6aff4.jpeg', '681b8d6deb8a1.jpg', '6822ea4a6cdb0.jpg', 52, 'available', '6822ea4a6b6ef.jpg', '6822ea4a6bcf1.jpg', '6822ea4a6c311.jpg', '2025-04-30 16:36:58', 1, NULL, NULL, NULL, 14),
(39, 'Ayat 49', '<p>property for rent</p>', 'apartment', 'rent', 6, 6, 1, '1st Floor', 65432, 200000, 'Ayat', 'Addis Ababa', 'Ethiopia', '<p>.</p>', '6817ba9bde3e9.jpg', '6817ba9bdebfd.jpg', '682f1610433a1.jpg', '6817ba9be0e8a.jpg', '6817ba9be1781.jpg', '682f161043c9d.jpg', 0, 'available', '6817ba9be1f34.jpg', '682f1610436d8.jpg', '682f1610439f8.jpg', '2025-05-03 19:00:53', 0, '682f1610439f8.jpg', '9.034573', '38.846055', 0),
(48, 'Hope', '', 'flat', 'sale', 4, 5, 5, '3rd Floor', 400, 100000000, 'Bole', 'Addis Ababa', 'Ethiopia', '', '6830761d890475.40559350.jpg', '6830761da5e6a9.34172372.jpg', '68307e0363962.jpg', NULL, NULL, '6830761da68020.13346924.jpg', 0, 'booked', '68307d14cc53e.jpg', '68307e03641e5.jpg', '6830761da71a86.06919103.jpg', '2025-05-23 16:20:30', 0, '6830761da71a86.06919103.jpg', '9.022200', '38.746800', 14);

-- --------------------------------------------------------

--
-- Table structure for table `reminder_logs`
--

CREATE TABLE `reminder_logs` (
  `id` int(11) NOT NULL,
  `visit_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sent_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `state`
--

CREATE TABLE `state` (
  `sid` int(50) NOT NULL,
  `sname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `state`
--

INSERT INTO `state` (`sid`, `sname`) VALUES
(7, 'Ethiopia\n\n');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `uid` int(50) NOT NULL,
  `uname` varchar(100) NOT NULL,
  `uemail` varchar(100) NOT NULL,
  `uphone` varchar(20) NOT NULL,
  `upass` varchar(255) NOT NULL,
  `utype` varchar(50) NOT NULL,
  `uimage` varchar(300) NOT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `status_end_date` datetime DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--


-- --------------------------------------------------------

--
-- Table structure for table `visits`
--

CREATE TABLE `visits` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `request_date` datetime NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visits`
--


-- --------------------------------------------------------

--
-- Table structure for table `visit_history`
--

CREATE TABLE `visit_history` (
  `id` int(11) NOT NULL,
  `visit_id` int(11) NOT NULL,
  `previous_date` date NOT NULL,
  `previous_time` time NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visit_history`
--


--
-- Indexes for dumped tables
--

--
-- Indexes for table `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`aid`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_property` (`property_id`);

--
-- Indexes for table `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`cid`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`cid`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`fid`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `reminder_logs`
--
ALTER TABLE `reminder_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visit_id` (`visit_id`);

--
-- Indexes for table `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`sid`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `visit_history`
--
ALTER TABLE `visit_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visit_id` (`visit_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about`
--
ALTER TABLE `about`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `aid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `city`
--
ALTER TABLE `city`
  MODIFY `cid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `cid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `fid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `pid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `reminder_logs`
--
ALTER TABLE `reminder_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `state`
--
ALTER TABLE `state`
  MODIFY `sid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `uid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `visits`
--
ALTER TABLE `visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `visit_history`
--
ALTER TABLE `visit_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `property` (`pid`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`uid`);

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `payment_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`uid`),
  ADD CONSTRAINT `payment_history_ibfk_3` FOREIGN KEY (`property_id`) REFERENCES `property` (`pid`);

--
-- Constraints for table `reminder_logs`
--
ALTER TABLE `reminder_logs`
  ADD CONSTRAINT `reminder_logs_ibfk_1` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visit_history`
--
ALTER TABLE `visit_history`
  ADD CONSTRAINT `visit_history_ibfk_1` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
