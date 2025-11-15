-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 10:52 AM
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
-- Database: `big_bro_squad`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'completed',
  `payment_method` varchar(50) DEFAULT 'cash',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_method`, `created_at`) VALUES
(36, 7, 1900.00, 'completed', 'cash', '2025-10-26 15:20:23'),
(37, 7, 1050.00, 'completed', 'cash', '2025-10-26 15:37:49'),
(38, 9, 1800.00, 'completed', 'cash', '2025-11-09 08:14:25'),
(39, 6, 1650.00, 'completed', 'cash', '2025-11-15 02:48:37'),
(40, 6, 1740.00, 'completed', 'cash', '2025-11-15 09:23:39'),
(41, 6, 3493.00, 'completed', 'cash', '2025-11-15 09:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`, `created_at`) VALUES
(56, 36, 1, 'BBQ T-shirt', 500.00, 1, 500.00, '2025-10-26 15:20:23'),
(57, 36, 2, 'BBQ Hoodie', 800.00, 1, 800.00, '2025-10-26 15:20:23'),
(58, 36, 3, 'BBQ Sweatpants', 600.00, 1, 600.00, '2025-10-26 15:20:23'),
(59, 37, 3, 'BBQ Sweatpants', 600.00, 1, 600.00, '2025-10-26 15:37:49'),
(60, 37, 4, 'BBQ Compression', 450.00, 1, 450.00, '2025-10-26 15:37:49'),
(61, 38, 3, 'BBQ Sweatpants', 600.00, 3, 1800.00, '2025-11-09 08:14:25'),
(62, 39, 5, 'BBQ Whey', 1200.00, 1, 1200.00, '2025-11-15 02:48:37'),
(63, 39, 4, 'BBQ Compression', 450.00, 1, 450.00, '2025-11-15 02:48:37'),
(64, 40, 1, 'Adidas Training T-Shirt', 950.00, 1, 950.00, '2025-11-15 09:23:39'),
(65, 40, 5, 'Compression Socks (Black)', 790.00, 1, 790.00, '2025-11-15 09:23:39'),
(66, 41, 1, 'Jordan Essentials Ringer Tee', 728.00, 1, 728.00, '2025-11-15 09:45:03'),
(67, 41, 5, 'Nike Sportswear', 1467.00, 1, 1467.00, '2025-11-15 09:45:03'),
(68, 41, 6, 'Nike Dri-FIT City Connect Logo (MLB Colorado Rockies)', 1298.00, 1, 1298.00, '2025-11-15 09:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(1024) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `active` int(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `image_path`, `category`, `stock`, `active`) VALUES
(1, 'Jordan Essentials Ringer Tee', 728.00, 'Big Kids\' T-Shirt', 'https://static.nike.com/a/images/33bb4f9e-4324-4be4-b757-beaa74db276b/jordan-essentials-ringer-tee-big-kids-t-shirt-JnjG5t.png', 'APPAREL', 100, 1),
(2, 'Nike Baseball (MLB Seattle Mariners)', 2109.00, 'Men\'s 3/4-Sleeve Pullover Hoodie', 'https://static.nike.com/a/images/49b1fb4d-48e8-4976-ae93-e83052e3f9ba/baseball-seattle-mariners-mens-3-4-sleeve-pullover-hoodie-M1mGVL.png', 'APPAREL', 100, 1),
(3, 'Air Jordan 11 CMFT Low', 2526.00, 'Women\'s Shoes', 'https://static.nike.com/a/images/b4e5c283-241b-438e-85b9-9d886bc900e2/air-jordan-11-cmft-low-womens-shoes-kk9grs.png', 'FOOTWEAR', 100, 1),
(4, 'Nike Marquee Edge', 5678.00, 'Mirrored Sunglasses', 'https://static.nike.com/a/images/574fe120-647b-42c1-b056-1d77e3115e0c/marquee-edge-mirrored-sunglasses-SFNrxk.png', 'EQUIPMENT', 100, 1),
(5, 'Nike Sportswear', 1467.00, 'Big Kids\' T-Shirt', 'https://static.nike.com/a/images/e3a0f91d-cede-4529-a068-1eec1e6df668/sportswear-big-kids-t-shirt-xZgR4W.png', 'APPAREL', 100, 1),
(6, 'Nike Dri-FIT City Connect Logo (MLB Colorado Rockies)', 1298.00, 'Men\'s T-Shirt', 'https://static.nike.com/a/images/cdd23adf-a673-4810-8929-295c272dd06b/dri-fit-city-connect-logo-colorado-rockies-mens-t-shirt-5Sm3Qf.png', 'APPAREL', 100, 1),
(7, 'Indiana Pacers', 2271.00, 'Men\'s Nike NBA Fleece Pullover Hoodie', 'https://static.nike.com/a/images/b0f1a4e1-75e1-4c63-9f1c-3b0f5b0a3b2b/indiana-pacers-mens-nike-nba-fleece-pullover-hoodie-R9jW3x.png', 'APPAREL', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `password`, `birthday`, `phone`, `address`, `created_at`) VALUES
(6, 'ADMIN .com', 'Admin@gmail.com', '$2y$10$frcnfbl3qzuY4f98A74NJ.AeFy7UW//6wkAQa8SRwy7WunqTDgmX2', '1111-11-11', '0911111111', '11111111111111111111111111', '2025-10-26 15:19:01'),
(7, 'Khiaongo Yanangi', 'ngoga2642@gmail.com', '$2y$10$5Gc5d42yN60Hjxa0h9mjouccq2ypXUxylqEMC2NCr7GjalbmeagxG', '1111-11-11', '0613517944', '111111111', '2025-10-26 15:20:02'),
(8, 'Khiaongo Yanangi', '6731501012@lamduan.mfu.ac.th', '$2y$10$dhzpyqRgTcMROiN3GNutbubPI7oq6zgANf8u9aKgXCpoGnIUuQGZ6', '1111-11-11', '0874561234', '1111111111', '2025-11-09 07:29:54'),
(9, 'kkk yyy', 'kkk@gmail.com', '$2y$10$XpLz9SmzbT5.cL/8uzz9Tu.y.wfLCx2myz6iNAg6iupAZ5A2zdevK', '2025-11-11', '0911111111', '12345', '2025-11-09 08:13:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
