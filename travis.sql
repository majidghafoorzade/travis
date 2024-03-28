-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2024 at 02:30 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `travis`
--

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` int(10) UNSIGNED NOT NULL,
  `option_key` varchar(255) NOT NULL,
  `option_value` text NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `option_key`, `option_value`, `updated_at`) VALUES
(1, 'nobitex_api_token', 'f6e2d72a5eb563c1b4f159f962378ea43fe2f15b', '2024-03-28 04:29:08');

-- --------------------------------------------------------

--
-- Table structure for table `usdtirt_price`
--

CREATE TABLE `usdtirt_price` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `price` float NOT NULL,
  `price_change` float DEFAULT NULL,
  `reg_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usdtirt_rsi`
--

CREATE TABLE `usdtirt_rsi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rsi` float DEFAULT NULL,
  `reg_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usdtirt_trades`
--

CREATE TABLE `usdtirt_trades` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('buy','sell') NOT NULL,
  `price` float NOT NULL,
  `reg_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_key_unique` (`option_key`);

--
-- Indexes for table `usdtirt_price`
--
ALTER TABLE `usdtirt_price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usdtirt_rsi`
--
ALTER TABLE `usdtirt_rsi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usdtirt_trades`
--
ALTER TABLE `usdtirt_trades`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `usdtirt_price`
--
ALTER TABLE `usdtirt_price`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usdtirt_rsi`
--
ALTER TABLE `usdtirt_rsi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usdtirt_trades`
--
ALTER TABLE `usdtirt_trades`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
