-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 103.84.195.58
-- Generation Time: Aug 14, 2024 at 02:38 PM
-- Server version: 5.7.40-0ubuntu0.18.04.1
-- PHP Version: 8.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `app_amoora_travel`
--

-- --------------------------------------------------------

--
-- Table structure for table `audience`
--

CREATE TABLE `audience` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `presenter_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session` json DEFAULT NULL,
  `heartbeat` timestamp NULL DEFAULT NULL,
  `offer` json DEFAULT NULL,
  `answer` json DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `live_location`
--

CREATE TABLE `live_location` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `label` varchar(20) DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `lng` float DEFAULT NULL,
  `heartbeat` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `live_location`
--

INSERT INTO `live_location` (`id`, `user_id`, `label`, `lat`, `lng`, `heartbeat`) VALUES
(24, 39, '', 3.12708, 101.678, '2024-08-13 10:25:09'),
(26, 43, '', -6.40536, 106.849, '2024-08-14 03:45:20'),
(27, 44, '', -6.40555, 106.849, '2024-08-14 10:59:01');

-- --------------------------------------------------------

--
-- Table structure for table `log_location`
--

CREATE TABLE `log_location` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `lng` float DEFAULT NULL,
  `time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `log_location`
--

INSERT INTO `log_location` (`id`, `user_id`, `lat`, `lng`, `time`) VALUES
(1905, 39, 3.12708, 101.678, '2024-08-13 10:25:09'),
(1906, 43, -6.40536, 106.849, '2024-08-14 03:45:20'),
(1907, 44, -6.40555, 106.849, '2024-08-14 10:59:01');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `is_phone_verified` tinyint(1) NOT NULL DEFAULT '0',
  `address` text,
  `passport_no` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `photo` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `full_name`, `phone`, `is_phone_verified`, `address`, `passport_no`, `user_id`, `created_at`, `photo`) VALUES
(1, 'Admin', '+628121985013', 0, 'Jl. H. Zaini, Bakti Jaya, Kec. Sukmajaya, Kota Depok, Jawa Barat 16418', '123456789', 1, NULL, '/members/avatar-1.jpeg'),
(9, 'Jamaah1', '628121985012', 1, 'Jl. H. Zaini, Bakti Jaya, Kec. Sukmajaya, Kota Depok, Jawa Barat 16418', '123456789', 39, '2024-06-21 00:26:20', NULL),
(10, 'Jamaah2', '08568737430', 0, NULL, NULL, 40, '2024-07-04 05:41:38', NULL),
(11, 'Jamaah3', '08123456789', 0, NULL, NULL, 41, '2024-07-04 11:46:49', NULL),
(12, 'NRP', '0816774319', 0, NULL, NULL, 42, '2024-07-17 05:17:48', NULL),
(13, 'Muthowwif1', '+628121985013', 0, NULL, NULL, 22, NULL, '/members/avatar-1.jpeg'),
(14, 'rudi ali nur akbar', '0895346226111', 0, NULL, NULL, 43, '2024-08-14 03:45:20', NULL),
(15, 'Ruri Salam', '085322066660', 0, NULL, NULL, 44, '2024-08-14 10:43:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `body` text,
  `image` varchar(200) DEFAULT NULL,
  `topic` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `pinned` tinyint(1) NOT NULL DEFAULT '0',
  `pinned_duration` int(11) NOT NULL DEFAULT '86400' COMMENT '24 hours'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `presenter`
--

CREATE TABLE `presenter` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `heartbeat` timestamp NULL DEFAULT NULL,
  `session` json DEFAULT NULL,
  `channel` varchar(100) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `ip_broadcast` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `name`) VALUES
(1, 'Jamaah'),
(2, 'Muthowwif'),
(99, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `identifier` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `verify_code` varchar(6) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `is_email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `role_id` int(11) NOT NULL DEFAULT '1',
  `is_closed` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `identifier`, `password`, `created_at`, `verify_code`, `name`, `email`, `is_email_verified`, `role_id`, `is_closed`) VALUES
(1, 'admin', '827ccb0eea8a706c4c34a16891f84e7b', '2024-04-20 03:53:42', 'VIYL6B', 'Antho', 'antho.firuze@gmail.com', 1, 99, 0),
(22, 'muthowwif1', '827ccb0eea8a706c4c34a16891f84e7b', '2024-05-23 05:21:26', 'KDW1V7', 'Rynest', 'developerrynest@gmail.com', 1, 2, 0),
(39, 'jamaah1', '827ccb0eea8a706c4c34a16891f84e7b', '2024-06-21 00:26:20', 'WDUZ9M', 'Antho', 'demo.project19@gmail.com', 1, 1, 1),
(40, 'jamaah2', '827ccb0eea8a706c4c34a16891f84e7b', '2024-07-04 05:41:38', 'TDRPZ2', 'baci', 'ahmad.ayyasy.08@gmail.com', 0, 1, 0),
(41, 'jamaah3', '827ccb0eea8a706c4c34a16891f84e7b', '2024-07-04 11:46:49', 'L5BZU8', 'Aisyah', 'aisyah.kamila@gmail.com', 0, 1, 0),
(42, 'na2rp@yahoo.com', 'b93939873fd4923043b9dec975811f66', '2024-07-17 05:17:48', 'AN1SDJ', 'Nana', 'na2rp@yahoo.com', 0, 1, 0),
(43, 'rudiali260@gmail.com', 'bbf13d23965ab134188051c385bfdedb', '2024-08-14 03:45:20', 'NJXAU2', 'rudi', 'rudiali260@gmail.com', 0, 1, 0),
(44, 'ruri.salam@gmail.com', 'ee2af2028ce8debe38f34ddd2adb6c4d', '2024-08-14 10:43:18', 'B8Q9HW', 'Ruri', 'ruri.salam@gmail.com', 0, 1, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audience`
--
ALTER TABLE `audience`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `live_location`
--
ALTER TABLE `live_location`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_location`
--
ALTER TABLE `log_location`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `presenter`
--
ALTER TABLE `presenter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `speaker_unique` (`user_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_identifier` (`identifier`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audience`
--
ALTER TABLE `audience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `live_location`
--
ALTER TABLE `live_location`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `log_location`
--
ALTER TABLE `log_location`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1908;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `presenter`
--
ALTER TABLE `presenter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
