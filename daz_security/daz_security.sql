-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2026 at 12:43 PM
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
-- Database: `daz_security`
--

-- --------------------------------------------------------

--
-- Table structure for table `registered_users`
--

CREATE TABLE `registered_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('customer','cashier','it_manager','admin') DEFAULT 'customer',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registered_users`
--

INSERT INTO `registered_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `phone`, `address`, `failed_attempts`, `is_locked`, `locked_until`, `is_verified`, `verification_token`, `created_at`, `updated_at`) VALUES
(1, 'kema', 'akmashahirasuhaimi@gmail.com', '$2y$10$dM1RcATG.SrUdnB3FFPVouOLlUQNWvwc3UvUaOLR1bTF8rYMqkheS', 'kema shahira', 'customer', '01153560780', 'No.30, Lorong 15,Taman Universiti,86400, Parit Raja, Batu Pahat', 0, 0, NULL, 0, '63c787d599bc8bed44b71c106bf18fc98930257f9c34de80efaf3fa28510d31e', '2026-01-07 11:30:49', '2026-01-07 11:30:49');

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `event` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security_logs`
--

INSERT INTO `security_logs` (`id`, `username`, `event`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'admin_daz', 'LOGIN_FAILED', 'Password mismatch (Attempt 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2026-01-07 09:35:28'),
(2, 'admin_daz', 'LOGIN_FAILED', 'Password mismatch (Attempt 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2026-01-07 09:35:28'),
(3, 'cashier_01', 'LOGIN_FAILED', 'Password mismatch (Attempt 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2026-01-07 09:35:28'),
(4, 'admin_daz', 'LOGIN_SUCCESS', 'Phase 1 verification passed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 09:35:39'),
(5, 'admin_daz', 'MFA_SENT', 'MFA token dispatched', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 09:35:39'),
(6, 'it_pro', 'LOGIN_SUCCESS', 'Phase 1 verification passed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 09:36:33'),
(7, 'it_pro', 'MFA_SENT', 'MFA token dispatched', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 09:36:33'),
(8, 'it_pro', 'LOGIN_FAILED', 'Incorrect MFA code provided', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 09:36:48'),
(9, 'kema', 'LOGIN_FAILED', 'Username not found', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 10:29:28'),
(10, 'admin_daz', 'LOGIN_SUCCESS', 'Phase 1 verification passed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 10:45:22'),
(11, 'admin_daz', 'MFA_SENT', 'MFA token dispatched', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 10:45:22'),
(12, 'admin_daz', 'LOGOUT', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:28:50'),
(13, 'kema', 'USER_REGISTERED', 'New user registered via registration form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:30:49'),
(14, 'kema', 'LOGIN_FAILED', 'Username not found', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:31:23'),
(15, 'admin_daz', 'LOGIN_SUCCESS', 'Phase 1 verification passed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:31:33'),
(16, 'admin_daz', 'MFA_SENT', 'MFA token dispatched', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:31:33'),
(17, 'kema', 'LOGIN_FAILED', 'Username not found', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:32:26'),
(18, 'kema', 'LOGIN_FAILED', 'Username not found', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:33:37'),
(19, 'admin_daz', 'LOGIN_SUCCESS', 'Phase 1 verification passed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:33:40'),
(20, 'admin_daz', 'MFA_SENT', 'MFA token dispatched', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:33:40'),
(21, 'customer_lee', 'LOGIN_SUCCESS', 'Phase 1 verification passed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:34:03'),
(22, 'customer_lee', 'MFA_SENT', 'MFA token dispatched', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:34:03'),
(23, 'customer_lee', 'MFA_VERIFIED', 'Full session authorized', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:34:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','cashier','it_manager','customer') NOT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `failed_attempts`, `is_locked`, `locked_until`, `created_at`) VALUES
(1, 'admin_daz', '$2y$10$uwgGrOEW64rwuEjx2uNk1eBRPdZ8uEWIAiwGPOo/ya4ZcJa.X1SWS', 'admin', 0, 0, NULL, '2026-01-07 09:35:28'),
(2, 'it_pro', '$2y$10$uwgGrOEW64rwuEjx2uNk1eBRPdZ8uEWIAiwGPOo/ya4ZcJa.X1SWS', 'it_manager', 0, 0, NULL, '2026-01-07 09:35:28'),
(3, 'cashier_01', '$2y$10$uwgGrOEW64rwuEjx2uNk1eBRPdZ8uEWIAiwGPOo/ya4ZcJa.X1SWS', 'cashier', 0, 0, NULL, '2026-01-07 09:35:28'),
(4, 'customer_lee', '$2y$10$uwgGrOEW64rwuEjx2uNk1eBRPdZ8uEWIAiwGPOo/ya4ZcJa.X1SWS', 'customer', 0, 0, NULL, '2026-01-07 09:35:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `registered_users`
--
ALTER TABLE `registered_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_registered_users_email` (`email`),
  ADD KEY `idx_registered_users_role` (`role`),
  ADD KEY `idx_registered_users_created_at` (`created_at`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_event` (`event`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `registered_users`
--
ALTER TABLE `registered_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
