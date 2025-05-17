-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 17, 2025 at 06:45 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cat_adoption`
--

-- --------------------------------------------------------

--
-- Table structure for table `adoptions`
--

CREATE TABLE `adoptions` (
  `id` int NOT NULL,
  `cat_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `gender` enum('Laki-laki','Perempuan') COLLATE utf8mb4_general_ci NOT NULL,
  `adoption_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoptions`
--

INSERT INTO `adoptions` (`id`, `cat_id`, `name`, `email`, `phone`, `gender`, `adoption_date`) VALUES
(26, 15, 'mon', 'marcies@gmail.com', '0897654321', 'Perempuan', '2025-05-16 08:34:08');

-- --------------------------------------------------------

--
-- Table structure for table `cats`
--

CREATE TABLE `cats` (
  `id` int NOT NULL,
  `img` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `price` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cats`
--

INSERT INTO `cats` (`id`, `img`, `name`, `description`, `price`, `created_at`, `updated_at`) VALUES
(15, 'uploads/67e0e251a63ff.jpg', 'Kiru - BSH', 'Tubuh besar, bulu pendek tebal', 5500000, '2025-03-24 04:40:50', '2025-03-24 04:40:50'),
(18, 'uploads/67e0e351d5e15.jpg', 'Mici - Persia', 'Berbulu lebat, wajah pesek', 2800000, '2025-03-24 04:45:06', '2025-03-24 04:45:06'),
(19, 'uploads/67e0e450877b3.jpg', 'Kuki - Munchkin', 'Berkaki pendek, lucu dan lincah', 5250000, '2025-03-24 04:49:20', '2025-03-24 04:49:20'),
(21, 'uploads/67e0e58a2a7aa.jpg', 'May - Scottish Fold', 'Telinga terlipat unik', 7000000, '2025-03-24 04:54:34', '2025-03-24 04:54:34'),
(22, 'uploads/67e0e669b5e06.jpeg', 'Bara - Bengal', 'Corak seperti macan tutul', 13000000, '2025-03-24 04:58:17', '2025-03-24 04:58:17'),
(23, 'uploads/67e0e6bc4fc50.jpeg', 'Miki - Sphynx', 'Tidak berbulu, perawatan khusus', 11100000, '2025-03-24 04:59:40', '2025-03-24 04:59:40'),
(24, 'uploads/67e0ea33cc7e3.jpeg', 'Milo - Siberian', 'Bulu tebal, tahan dingin, ramah', 8750000, '2025-03-24 05:14:27', '2025-03-24 05:14:27'),
(25, 'uploads/67e0eb5f1e667.jpeg', 'Rinn - Ragdoll', 'Jinak dan suka dipeluk', 7000000, '2025-03-24 05:19:27', '2025-03-24 05:19:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('admin','pengadopsi') NOT NULL DEFAULT 'pengadopsi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `nama`, `password`, `created_at`, `role`) VALUES
(1, 'monriie', 'mon', '$2y$10$/fBuDeHD/VC8kYf6oD4FX.t3fWJBKCzi43LNKaOYccu3Btye75o36', '2025-04-30 17:27:26', 'pengadopsi'),
(3, 'admin', 'admin1', '$2y$10$v7NssXMQmGr6wMGVEY3MYu8oPITJ7zY0RhL9CjaG0HBAsxXqQo1Pq', '2025-05-10 15:36:55', 'admin'),
(4, 'yupi', 'marcie', '$2y$10$4UscHmZdqAOr1MB.uufqkOcpnJahW0HRNiFn90/33zDapdIkIx63e', '2025-05-17 06:27:04', 'pengadopsi'),
(5, 'ciwi', 'cimii', '$2y$10$2BXqWvIJ7qRHqSdHXUCW.eQNgX6RI0FOXF7ZcdOkqR.amiggWGYAO', '2025-05-17 06:30:35', 'pengadopsi');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adoptions`
--
ALTER TABLE `adoptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `cats`
--
ALTER TABLE `cats`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `adoptions`
--
ALTER TABLE `adoptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `cats`
--
ALTER TABLE `cats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adoptions`
--
ALTER TABLE `adoptions`
  ADD CONSTRAINT `adoptions_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `cats` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
