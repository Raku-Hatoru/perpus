-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 24, 2026 at 10:15 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan_digital`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `year` year DEFAULT NULL,
  `stock` int DEFAULT '0',
  `cover_image` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `isbn`, `title`, `author`, `publisher`, `year`, `stock`, `cover_image`, `category_id`) VALUES
(1, '978-602-8519-93-9', 'how to be good lawyer', 'haq', 'haq', '2023', 6, NULL, 5),
(2, '978-602-1234-56-7', 'How to be a good it support', 'elang', 'elang', '2005', 100, '', 4),
(3, '978-979-1234-56-8', 'hilang timbul', 'tera lie', 'tera lie', '2026', 5, NULL, 3),
(4, '978-602-0332-15-4', 'Struktur Data dan Algoritma dengan PHP', 'Haq', 'Haq', '2026', 0, 'https://penerbit.stekom.ac.id/public/journals/12/article_288_cover_en_US.jpg', 4),
(5, '978-602-0523-11-8', 'Dasar-Dasar Keamanan Siber untuk Pemula', 'elang', 'elang', '2026', 5, 'https://down-id.img.susercontent.com/file/id-11134207-7rbk9-m71yhod7u8os81_tn', 4),
(6, '978-979-1234-56-7', 'Rekayasa Perangkat Lunak Modern', 'inas', 'inas', '2026', 3, 'https://www.davefarley.net/wp-content/uploads/2022/01/Modern-Software-Engineering.png', 4),
(7, '978-602-8888-21-0', 'https://cdn.gramedia.com/uploads/items/tuntunan_praktis.jpg', 'Raku', 'Gramedia', '2026', 4, 'https://cdn.gramedia.com/uploads/items/tuntunan_praktis.jpg', 4),
(8, '978-623-0103-92-1', 'Mastering Laravel 11 untuk Web Developer', 'Cofe', 'Gramedia', '2026', 2, 'https://cdn.gramedia.com/uploads/products/ebnrzta-cn.jpg', 4),
(9, '978-602-4444-12-9', 'Arsitektur Microservices di Lingkungan Cloud', 'elang', 'Gramedia', '2026', 2, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQO0C39iexcyfGNDARHjPlSiNTXjaJ0Aqesdg&s', 4),
(10, '978-979-9999-01-2', 'Logika Matematika dan Pemrograman Linear', 'elang', 'Gramedia', '2026', 0, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTDvg28S_ZNRJTi5V86JhsypVTF-4WPhfnKQg&s', 4);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'sains', 'buku ipa'),
(2, 'bahasa', 'buku belajar dunia'),
(3, 'cerita', 'Kumpulan novel dan cerita pendek'),
(4, 'teknologi', 'belajar jadi it propesional'),
(5, 'hukum', 'hukum itu penting');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `book_id` int DEFAULT NULL,
  `loan_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('borrowed','returned') DEFAULT 'borrowed',
  `fine_amount` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `user_id`, `book_id`, `loan_date`, `due_date`, `return_date`, `status`, `fine_amount`) VALUES
(1, 3, 6, '2026-03-24', '2026-03-31', '2026-03-24', 'returned', 0.00),
(2, 3, 2, '2026-03-01', '2026-03-10', '2026-03-24', 'returned', 14000.00),
(3, 3, 10, '2026-03-24', '2026-03-31', NULL, 'borrowed', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(3, 'haq', '$2y$10$aHxx1HUAK9PFV5NIAp11QOpjYERqvKTlMfgxY0jX/w2o66wIgx66O', 'msholahatulhaq@gmail.com', 'member', '2026-03-15 15:04:45'),
(4, 'Romy', '$2y$10$I.P104/Rz./945B1ytw/Nee4UwVdNHXDbMmV1hjEyxfUtm9zXbD7i', '123@gmail.com', 'admin', '2026-03-15 15:10:31'),
(5, 'admin', '$2y$10$QiyUimOpPpCIRbeSzxY9aecM8PENIu92STZAPKcaIZjrl/SvuIr/a', 'admin123@gmail.com', 'admin', '2026-03-23 13:41:29'),
(7, 'haq1', '$2y$10$C.NmyubS9by2KJtVAzCWL.m6/xQCgtQy/aDwfKf9f0F5DAzokDPXG', 'msholahatulha@gmail.com', 'member', '2026-03-24 09:58:46'),
(8, 'haq2', '$2y$10$/4Bi9gmE4a7HZryowyRoS.SMME7Sa.0vVzUUEJHRzZbItlvwhLNFy', '123456@gmail.com', 'member', '2026-03-24 09:59:10'),
(9, 'haq4', '$2y$10$E1waUuhJFFoRB1/Jf.r4U./qlhMpeKHRas6pSs/Ds3vBWTN1LWi.W', '234567@gmail.com', 'member', '2026-03-24 09:59:29');

-- --------------------------------------------------------

--
-- Table structure for table `waitlists`
--

CREATE TABLE `waitlists` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `book_id` int DEFAULT NULL,
  `status` enum('waiting','notified','resolved') DEFAULT 'waiting',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `waitlists`
--

INSERT INTO `waitlists` (`id`, `user_id`, `book_id`, `status`, `created_at`) VALUES
(1, 3, 4, 'waiting', '2026-03-24 10:07:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `waitlists`
--
ALTER TABLE `waitlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `waitlists`
--
ALTER TABLE `waitlists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `waitlists`
--
ALTER TABLE `waitlists`
  ADD CONSTRAINT `waitlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `waitlists_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
