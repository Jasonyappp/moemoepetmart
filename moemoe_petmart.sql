-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 12:20 PM
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

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`cart_id`, `user_id`, `product_id`, `quantity`) VALUES
(249, 7, 96, 1);

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
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `recipient_phone` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `shipping_region` varchar(20) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `card_last4` varchar(4) DEFAULT NULL,
  `qr_code` text DEFAULT NULL,
  `qr_generated_at` datetime DEFAULT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `order_status` varchar(50) DEFAULT 'Pending Payment',
  `status` enum('Pending Payment','To Ship','Shipped','Completed','Cancelled','Return/Refund') NOT NULL DEFAULT 'Pending Payment',
  `return_reason` varchar(255) DEFAULT NULL,
  `voucher_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `shipping_address`, `recipient_name`, `recipient_phone`, `total_amount`, `shipping_fee`, `shipping_region`, `payment_method`, `card_last4`, `qr_code`, `qr_generated_at`, `order_date`, `order_status`, `status`, `return_reason`, `voucher_code`, `discount_amount`) VALUES
(10, 6, NULL, '', '0', 399.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-12-13 13:04:56', 'Pending Payment', 'Pending Payment', NULL, NULL, 0.00),
(11, 6, NULL, '', '0', 39.99, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-12-13 13:06:26', 'Pending Payment', 'Pending Payment', NULL, NULL, 0.00),
(48, 7, NULL, '', '0', 39.99, 0.00, NULL, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-14 15:04:42', 'To Ship', 'Pending Payment', NULL, NULL, 0.00),
(49, 7, NULL, '', '0', 399.00, 0.00, NULL, 'Touch n Go', NULL, '/images_tng/tng_qr.jpg', '2025-12-14 15:05:33', '2025-12-14 15:05:19', 'To Ship', 'Pending Payment', NULL, NULL, 0.00),
(50, 7, NULL, '', '0', 438.99, 0.00, NULL, 'Credit/Debit Card', '1234', NULL, NULL, '2025-12-14 15:07:10', 'To Ship', 'Pending Payment', NULL, NULL, 0.00),
(52, 7, NULL, '', '0', 399.00, 0.00, NULL, 'Credit/Debit Card', '1234', NULL, NULL, '2025-12-14 15:14:49', 'To Ship', 'Pending Payment', NULL, NULL, 0.00),
(54, 7, NULL, '', '0', 399.00, 0.00, NULL, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-14 15:24:59', 'To Ship', 'Pending Payment', NULL, NULL, 0.00),
(55, 7, NULL, '', '0', 39.99, 0.00, NULL, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-14 15:29:55', 'To Ship', 'Pending Payment', NULL, NULL, 0.00),
(56, 7, NULL, '', '0', 399.00, 0.00, NULL, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-14 15:32:45', 'Return/Refund', 'Pending Payment', 'Does not match description', NULL, 0.00),
(57, 7, NULL, '', '0', 39.99, 0.00, NULL, 'Touch n Go', NULL, '/images_tng/tng_qr.jpeg', '2025-12-14 16:05:28', '2025-12-14 16:03:18', 'Cancelled', 'Pending Payment', NULL, NULL, 0.00),
(114, 7, NULL, '', '0', 239.00, 0.00, NULL, 'Touch \'n Go', NULL, NULL, NULL, '2025-12-18 17:29:06', 'Return/Refund', 'Pending Payment', 'Wrong item received', NULL, 0.00),
(163, 7, '89, Jalan ABC, Kuala Lumpur', 'YJ', '0123456789', 44.50, 0.00, NULL, 'Touch \'n Go', NULL, NULL, NULL, '2025-12-19 14:49:03', 'To Ship', 'Pending Payment', NULL, 'MOE10OFF', 10.00),
(164, 7, '89, Jalan ABC, Kuala Lumpur', 'YJ', '0123456789', 280.64, 0.00, NULL, 'Touch \'n Go', NULL, NULL, NULL, '2025-12-19 14:55:09', 'To Ship', 'Pending Payment', NULL, 'MOE20PCT', 70.16),
(165, 7, '123, Lorong ABC, Malaysia', 'Kelly', '015-69874236', 109.00, 0.00, NULL, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-19 15:24:12', 'Pending Payment', 'Pending Payment', NULL, NULL, 0.00),
(167, 7, '89, Jalan ABC, Kuala Lumpur', 'YJ', '0123456789', 82.80, 0.00, NULL, 'Cash on Delivery', NULL, NULL, NULL, '2025-12-19 15:42:48', 'Pending Payment', 'Pending Payment', NULL, 'MOEFREE5', 5.00),
(168, 7, '89, Jalan ABC, Kuala Lumpur', 'YJ', '0123456789', 66.80, 10.00, 'east', 'Cash on Delivery', NULL, NULL, NULL, '2025-12-19 16:31:18', 'Pending Payment', 'Pending Payment', NULL, 'MOE10OFF', 10.00),
(170, 7, '789, Hello, KL', 'Abby', '015-69874236', 134.16, 0.00, 'west', 'Credit/Debit Card', '2741', NULL, NULL, '2025-12-19 16:45:32', 'To Ship', 'Pending Payment', NULL, 'MOE20PCT', 33.54),
(171, 7, '789, Hello, KL', 'YJ', '015-69874236', 31.20, 5.00, 'west', 'Cash on Delivery', NULL, NULL, NULL, '2025-12-19 17:17:23', 'Pending Payment', 'Pending Payment', NULL, 'MOEFREE5', 5.00),
(173, 7, '89, Jalan ABC, Kuala Lumpur', 'YJ', '0123456789', 88.60, 5.00, 'west', 'Cash on Delivery', NULL, NULL, NULL, '2025-12-19 17:43:45', 'Pending Payment', 'Pending Payment', NULL, 'MOE10OFF', 10.00),
(174, 7, '789, Hello, KL', 'Kelly', '012-3456789', 114.00, 5.00, 'west', 'Touch \'n Go', NULL, NULL, NULL, '2025-12-19 18:53:02', 'To Ship', 'Pending Payment', NULL, NULL, 0.00);

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
(54, 52, 3, 1, 399.00),
(57, 54, 3, 1, 399.00),
(58, 55, 2, 1, 39.99),
(59, 56, 3, 1, 399.00),
(60, 57, 2, 1, 39.99),
(120, 114, 92, 1, 239.00),
(176, 163, 94, 5, 10.90),
(177, 164, 93, 2, 55.90),
(178, 164, 92, 1, 239.00),
(179, 165, 3, 1, 109.00),
(181, 167, 2, 2, 43.90),
(182, 168, 94, 1, 10.90),
(183, 168, 93, 1, 55.90),
(185, 170, 93, 3, 55.90),
(186, 171, 95, 2, 15.60),
(188, 173, 95, 6, 15.60),
(189, 174, 3, 1, 109.00);

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
(2, 'CAG0001', 'Luxury Cat Villa Carrier', '3-level cage with wheel', 43.90, 7, 2, 1, '2025-11-23 23:22:24', '2025-12-19 15:42:48', 'prod_693f05ea19699.jpg'),
(3, 'FOD0001', 'Royal Canin Puppy 10kg', '~ Each pack is designed for adult dogs and available in different sizes to meet your feeding needs.', 109.00, 78, 3, 1, '2025-11-23 23:22:24', '2025-12-19 18:53:02', 'prod_693f165ba2028.jpg'),
(89, 'CAG0002', 'Big Home Shape Cage', 'Available for cat & dog', 139.90, 3, 2, 1, '2025-12-15 03:00:35', '2025-12-19 12:11:13', '693f095361e88.jpg'),
(91, 'CAG0004', 'Standard Pet Cage', 'Normal cage for our cute pets', 35.90, 39, 2, 1, '2025-12-15 03:04:33', '2025-12-19 16:38:17', 'prod_693f0bd3c4960.jpg'),
(92, 'CAG0005', 'Luxury Hamster Villa Cage', 'Benefit:\r\n-Can put many hamsters', 239.00, 15, 2, 1, '2025-12-15 03:06:43', '2025-12-19 14:55:09', '693f0ac353967.jpg'),
(93, 'CAG0003', 'Transparent Carries', 'Benefits:\r\n- Easily see your cutest pets', 55.90, 17, 2, 1, '2025-12-15 03:15:09', '2025-12-19 17:37:48', '693f0cbd71b9b.jpg'),
(94, 'ACC0001', 'Pet Automatic Retractable Leash', '• Length Options: 3m / 5m\r\n• Material: Polyester + PP', 10.90, 25, 5, 1, '2025-12-15 03:24:38', '2025-12-19 16:31:18', 'prod_693f106d7be39.jpg'),
(95, 'CLR0001', 'Pet Hair Remover Comb', '✔️Simply push the button, wipe, making it super simple to remove all the hair from the brush, so it\'s ready for the next time use.\r\n✔️Suit for dog, cat, rabbits and other pets, making them neat and clean.', 15.60, 28, 4, 1, '2025-12-15 03:38:35', '2025-12-19 17:43:45', '693f123b2857e.jpg'),
(96, 'TOY0001', 'Cat Teaser Stick Toys with Bell', '🐾 Give your pets endless fun with this Cat Teaser Stick Toy with Bell. Interactive design, featuring a dangling bell and feather that will keep your cat entertained and engaged for hours!', 4.99, 11, 1, 1, '2025-12-15 03:43:09', '2025-12-19 12:12:21', '693f134dee5c4.jpg');

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
(6, 'haha', 'haha@gmail.com', '012-2222222', '0', '$2y$10$VybeVzjUtq7U2kpxMCJuV.zUuOi1vHO9l.u/./ThjRVMB8WuekqJS', 'member', '2025-12-06 23:12:07', 'uploads/profile_pics/6_1765302986_iu-3.jpg', 0, 'Suspicious activity', NULL),
(7, 'aaa', 'tanyijia-wp23@student.tarc.edu.my', '0123456789', '0', '$2y$10$k5r/g6EeYTTKXn1w06SEUutPnBB9ASLIHWZbut1A5pbh/8Z0bigS.', 'member', '2025-12-13 16:42:33', 'uploads/profile_pics/7_1766119675_images.jpg', 0, 'Suspicious activity', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_name` varchar(100) DEFAULT 'Home',
  `recipient_name` varchar(100) NOT NULL,
  `recipient_phone` varchar(20) NOT NULL,
  `full_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `address_name`, `recipient_name`, `recipient_phone`, `full_address`, `created_at`) VALUES
(58, 5, 'Mama House', 'Anna', '019-87654321', '89, Jalan Bukit Oden 2, 54000 Kuala Lumpur', '2025-12-19 04:09:32'),
(59, 5, 'Grandpa House', 'Alex', '012-3456789', '45, Jalan Bukit Harimau 3, 58100 Kuala Lumpur', '2025-12-19 04:10:09'),
(61, 7, 'My Home', 'YJ', '0123456789', '89, Jalan ABC, Kuala Lumpur', '2025-12-19 04:47:41'),
(63, 7, 'Moe Moe Pet Mart', 'Kelly', '015-69874236', '123, Lorong ABC, Malaysia', '2025-12-19 06:44:48'),
(64, 7, 'work', 'Abby', '015-69874236', '789, Hello, KL', '2025-12-19 08:45:10'),
(65, 7, 'Home', 'Abby', '015-69874236', '789, Hello, KL', '2025-12-19 08:45:32');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('fixed','percentage') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `min_spend` decimal(10,2) DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `type`, `value`, `min_spend`, `expiry_date`, `usage_limit`, `used_count`, `created_at`) VALUES
(1, 'MOE10OFF', 'fixed', 10.00, 50.00, '2026-04-30', 100, 5, '2025-12-19 05:20:19'),
(2, 'MOE20PCT', 'percentage', 20.00, 100.00, '2026-12-31', NULL, 3, '2025-12-19 05:20:19'),
(3, 'MOEFREE5', 'fixed', 5.00, 20.00, '2026-08-31', NULL, 6, '2025-12-19 06:26:15');

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
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `user_product_unique` (`user_id`,`product_id`),
  ADD KEY `fk_favorites_product` (`product_id`);

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
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=250;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_favorites_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
