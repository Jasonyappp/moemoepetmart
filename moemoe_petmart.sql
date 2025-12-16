-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2025 at 01:29 PM
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
-- Database: `moemoe_petmart`
--
CREATE DATABASE IF NOT EXISTS `moemoe_petmart` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `moemoe_petmart`;
-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_code` varchar(10) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_code`, `category_name`, `description`, `created_at`) VALUES
(1, 'TOY', 'Toys', 'Interactive toys, balls, feather wands, etc.', '2025-11-23 23:22:24'),
(2, 'CAG', 'Cages & Carriers', 'Hamster cages, bird cages, travel carriers', '2025-11-23 23:22:24'),
(3, 'FOD', 'Food', 'Dry food, wet food, treats, milk replacement', '2025-11-23 23:22:24'),
(4, 'CLR', 'Cleaning & Grooming', 'Shampoo, brushes, nail clippers, deodorizers', '2025-11-23 23:22:24'),
(5, 'ACC', 'Accessories', 'Collars, leashes, clothes, beds, bowls', '2025-11-23 23:22:24');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `card_last4` varchar(4) DEFAULT NULL,
  `qr_code` text DEFAULT NULL,
  `qr_generated_at` datetime DEFAULT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `order_status` varchar(50) DEFAULT 'Pending Payment',
  `status` enum('Pending Payment','To Ship','Shipped','Completed','Cancelled','Return/Refund') NOT NULL DEFAULT 'Pending Payment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `payment_method`, `card_last4`, `qr_code`, `qr_generated_at`, `order_date`, `order_status`, `status`) VALUES
(10, 6, 399.00, NULL, NULL, NULL, NULL, '2025-12-13 13:04:56', 'Pending Payment', 'Pending Payment'),
(11, 6, 39.99, NULL, NULL, NULL, NULL, '2025-12-13 13:06:26', 'Pending Payment', 'Pending Payment'),
(48, 7, 39.99, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-14 15:04:42', 'To Ship', 'Pending Payment'),
(49, 7, 399.00, 'Touch n Go', NULL, '/images_tng/tng_qr.jpg', '2025-12-14 15:05:33', '2025-12-14 15:05:19', 'To Ship', 'Pending Payment'),
(50, 7, 438.99, 'Credit/Debit Card', '1234', NULL, NULL, '2025-12-14 15:07:10', 'To Ship', 'Pending Payment'),
(51, 7, 39.99, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-14 15:14:06', 'To Ship', 'Pending Payment'),
(52, 7, 399.00, 'Credit/Debit Card', '1234', NULL, NULL, '2025-12-14 15:14:49', 'To Ship', 'Pending Payment'),
(53, 7, 438.99, 'Touch n Go', NULL, '/images_tng/tng_qr.jpg', '2025-12-14 15:15:44', '2025-12-14 15:15:27', 'To Ship', 'Pending Payment'),
(54, 7, 399.00, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-14 15:24:59', 'To Ship', 'Pending Payment'),
(55, 7, 39.99, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-14 15:29:55', 'To Ship', 'Pending Payment'),
(56, 7, 399.00, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-14 15:32:45', 'To Ship', 'Pending Payment'),
(57, 7, 39.99, 'Touch n Go', NULL, '/images_tng/tng_qr.jpeg', '2025-12-14 16:05:28', '2025-12-14 16:03:18', 'Cancelled', 'Pending Payment'),
(58, 6, 399.00, 'Touch n Go', NULL, '/images_tng/tng_qr.jpeg', '2025-12-14 22:18:02', '2025-12-14 22:17:42', 'Cancelled', 'Pending Payment'),
(59, 6, 7385.00, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-15 00:07:30', 'Pending Payment', 'Pending Payment');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(12, 10, 3, 1, 399.00),
(13, 11, 2, 1, 39.99),
(49, 48, 2, 1, 39.99),
(50, 49, 3, 1, 399.00),
(51, 50, 2, 1, 39.99),
(52, 50, 3, 1, 399.00),
(53, 51, 2, 1, 39.99),
(54, 52, 3, 1, 399.00),
(55, 53, 2, 1, 39.99),
(56, 53, 3, 1, 399.00),
(57, 54, 3, 1, 399.00),
(58, 55, 2, 1, 39.99),
(59, 56, 3, 1, 399.00),
(60, 57, 2, 1, 39.99),
(61, 58, 3, 1, 399.00),
(62, 59, 3, 15, 399.00),
(63, 59, 89, 14, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_code` varchar(20) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `category_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `photo_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_code`, `product_name`, `description`, `price`, `stock_quantity`, `category_id`, `is_active`, `created_at`, `updated_at`, `photo_name`) VALUES
(2, 'CAG0001', 'Luxury Cat Villa Carrier', '3-level cage with wheel', 43.90, 25, 2, 1, '2025-11-23 23:22:24', '2025-12-15 02:46:02', 'prod_693f05ea19699.jpg'),
(3, 'FOD0001', 'Royal Canin Puppy 10kg', '~ Each pack is designed for adult dogs and available in different sizes to meet your feeding needs.', 109.00, 90, 3, 1, '2025-11-23 23:22:24', '2025-12-15 03:56:11', 'prod_693f165ba2028.jpg'),
(89, 'CAG0002', 'Big Home Shape Cage', 'Available for cat & dog', 139.90, 18, 2, 1, '2025-12-15 03:00:35', '2025-12-15 03:00:35', '693f095361e88.jpg'),
(91, 'CAG0004', 'Standard Pet Cage', 'Normal cage for our cute pets', 35.90, 50, 2, 1, '2025-12-15 03:04:33', '2025-12-15 03:11:15', 'prod_693f0bd3c4960.jpg'),
(92, 'CAG0005', 'Luxury Hamster Villa Cage', 'Benefit:\r\n-Can put many hamsters', 239.00, 12, 2, 1, '2025-12-15 03:06:43', '2025-12-15 03:06:43', '693f0ac353967.jpg'),
(93, 'CAG0003', 'Transparent Carries', 'Benefits:\r\n- Easily see your cutest pets', 55.90, 24, 2, 1, '2025-12-15 03:15:09', '2025-12-15 03:15:09', '693f0cbd71b9b.jpg'),
(94, 'ACC0001', 'Pet Automatic Retractable Leash', '• Length Options: 3m / 5m\r\n• Material: Polyester + PP', 10.90, 50, 5, 1, '2025-12-15 03:24:38', '2025-12-15 03:30:53', 'prod_693f106d7be39.jpg'),
(95, 'CLR0001', 'Pet Hair Remover Comb', '✔️Simply push the button, wipe, making it super simple to remove all the hair from the brush, so it\'s ready for the next time use.\r\n✔️Suit for dog, cat, rabbits and other pets, making them neat and clean.', 15.60, 45, 4, 1, '2025-12-15 03:38:35', '2025-12-15 03:38:35', '693f123b2857e.jpg'),
(96, 'TOY0001', 'Cat Teaser Stick Toys with Bell', '🐾 Give your pets endless fun with this Cat Teaser Stick Toy with Bell. Interactive design, featuring a dangling bell and feather that will keep your cat entertained and engaged for hours!', 4.99, 28, 1, 1, '2025-12-15 03:43:09', '2025-12-15 03:43:09', '693f134dee5c4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` varchar(255) NOT NULL,
  `type` enum('pet','supply') NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_image`
--

CREATE TABLE `product_image` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `home_address` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('member','admin') NOT NULL DEFAULT 'member',
  `created_at` datetime DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL,
  `locked` tinyint(1) DEFAULT 0,
  `lock_reason` varchar(255) DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `home_address`, `password`, `role`, `created_at`, `profile_pic`, `locked`, `lock_reason`, `locked_at`) VALUES
(1, 'Seahnijun', '', '', '0', '$2y$10$jSANxKW6shQ/CsKMsKzSXeqiOue5QFD2DnhPO/SwQJDAwdgtR1lRO', 'admin', '2025-11-22 17:27:12', 'uploads/profile_pics/1_1765859839_admin.jpg', 0, NULL, NULL),
(5, 'abc', 'abc123@yahoo.com', '012-3456789', '0', '$2y$10$EKljSiD3aP0XAT.wLJdBKe7puFh/gvRdAGlaGoHU7aJ3tHfWqdqGi', 'member', '2025-11-22 22:32:05', 'uploads/profile_pics/5_1765885108_otter.jpg', 0, NULL, NULL),
(6, 'haha', 'haha@gmail.com', '012-2222222', '0', '$2y$10$VybeVzjUtq7U2kpxMCJuV.zUuOi1vHO9l.u/./ThjRVMB8WuekqJS', 'member', '2025-12-06 23:12:07', 'uploads/profile_pics/6_1765302986_iu-3.jpg', 0, 'Payment issues', NULL),
(7, 'aaa', 'tanyijia-wp23@student.tarc.edu.my', '0123456789', '0', '$2y$10$k5r/g6EeYTTKXn1w06SEUutPnBB9ASLIHWZbut1A5pbh/8Z0bigS.', 'member', '2025-12-13 16:42:33', NULL, 0, 'Suspicious activity', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_name` varchar(100) DEFAULT 'Home',
  `full_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `address_name`, `full_address`, `created_at`) VALUES
(2, 5, 'Home', '21, Jalan CapyBara 520, 57000 Kuala Lumpur', '2025-12-16 11:35:45'),
(3, 5, 'Mama house', '45, Jalan Bintang 3, 58100 Kuala Lumpur', '2025-12-16 11:36:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_code` (`category_code`),
  ADD KEY `idx_code` (`category_code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_code` (`product_code`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_image`
--
ALTER TABLE `product_image`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `product_image`
--
ALTER TABLE `product_image`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `product_image`
--
ALTER TABLE `product_image`
  ADD CONSTRAINT `product_image_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
