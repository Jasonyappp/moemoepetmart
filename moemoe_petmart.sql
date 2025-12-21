-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 21, 2025 at 05:26 PM
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

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`cart_id`, `user_id`, `product_id`, `quantity`) VALUES
(249, 7, 96, 1),
(253, 8, 89, 3);

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
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_conversations`
--

INSERT INTO `chat_conversations` (`conversation_id`, `user_id`, `admin_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 'closed', '2025-12-21 06:24:07', '2025-12-21 07:03:20'),
(2, 6, 1, 'closed', '2025-12-21 07:14:53', '2025-12-21 07:45:49'),
(3, 6, 1, 'closed', '2025-12-21 07:46:37', '2025-12-21 08:26:55'),
(4, 6, 1, 'open', '2025-12-21 08:31:13', '2025-12-21 08:32:25'),
(5, 9, 1, 'open', '2025-12-21 08:38:57', '2025-12-21 17:22:43');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('member','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`message_id`, `conversation_id`, `sender_id`, `sender_type`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 6, 'member', 'hi, how much of the pet accessories ya?', 1, '2025-12-21 06:33:21'),
(2, 1, 1, 'admin', 'erm it is rm100', 1, '2025-12-21 06:34:34'),
(3, 1, 6, 'member', 'ok tqvm~', 1, '2025-12-21 06:48:53'),
(4, 1, 6, 'member', 'hihi', 1, '2025-12-21 07:03:20'),
(5, 2, 6, 'member', 'hi, seller', 1, '2025-12-21 07:15:06'),
(6, 2, 1, 'admin', 'yes, any problem?', 1, '2025-12-21 07:16:09'),
(7, 2, 6, 'member', 'nothing', 1, '2025-12-21 07:24:56'),
(8, 2, 1, 'admin', 'just testing', 1, '2025-12-21 07:25:33'),
(9, 2, 6, 'member', 'testing', 1, '2025-12-21 07:28:23'),
(10, 2, 1, 'admin', 'yy', 1, '2025-12-21 07:34:34'),
(11, 2, 6, 'member', 'yes', 1, '2025-12-21 07:39:02'),
(12, 2, 1, 'admin', 'anything?', 1, '2025-12-21 07:39:56'),
(13, 2, 1, 'admin', 'yes', 1, '2025-12-21 07:43:28'),
(14, 2, 6, 'member', 'teasting', 1, '2025-12-21 07:45:17'),
(15, 3, 6, 'member', 'yes yes', 1, '2025-12-21 07:53:59'),
(16, 3, 6, 'member', 'what r u doing', 1, '2025-12-21 07:54:05'),
(17, 3, 6, 'member', '88', 1, '2025-12-21 07:54:24'),
(18, 3, 6, 'member', '88', 1, '2025-12-21 07:54:25'),
(19, 3, 1, 'admin', 'anything u want to say?', 1, '2025-12-21 07:55:25'),
(20, 3, 1, 'admin', 'if yes pls wait until 8.00am', 1, '2025-12-21 07:55:38'),
(21, 3, 6, 'member', 'hihihihi', 1, '2025-12-21 08:14:28'),
(22, 3, 6, 'member', 'just for testing', 1, '2025-12-21 08:14:36'),
(23, 3, 6, 'member', 'nothing', 1, '2025-12-21 08:14:41'),
(24, 3, 1, 'admin', 'yes i am here', 1, '2025-12-21 08:15:17'),
(25, 3, 1, 'admin', '8989', 1, '2025-12-21 08:15:21'),
(26, 3, 1, 'admin', '9999', 1, '2025-12-21 08:15:23'),
(27, 3, 1, 'admin', 'yes', 1, '2025-12-21 08:24:54'),
(28, 3, 1, 'admin', 'yes', 1, '2025-12-21 08:24:55'),
(29, 3, 1, 'admin', '7', 1, '2025-12-21 08:24:56'),
(30, 3, 1, 'admin', '7', 1, '2025-12-21 08:24:56'),
(31, 4, 6, 'member', 'hihi, brother', 1, '2025-12-21 08:31:30'),
(32, 4, 6, 'member', 'any promotion ?', 1, '2025-12-21 08:31:41'),
(33, 4, 1, 'admin', 'no wor', 1, '2025-12-21 08:32:08'),
(34, 4, 1, 'admin', '9', 1, '2025-12-21 08:32:14'),
(35, 4, 1, 'admin', '9', 1, '2025-12-21 08:32:16'),
(36, 4, 1, 'admin', 'w', 1, '2025-12-21 08:32:22'),
(37, 4, 1, 'admin', 'u', 1, '2025-12-21 08:32:25'),
(38, 5, 9, 'member', 'yes anything?', 1, '2025-12-21 08:42:08'),
(39, 5, 9, 'member', '88', 1, '2025-12-21 08:42:54'),
(40, 5, 1, 'admin', 'wht u want ?', 1, '2025-12-21 08:45:44'),
(41, 5, 1, 'admin', 'any question?', 1, '2025-12-21 08:45:54'),
(42, 5, 9, 'member', 'nono', 1, '2025-12-21 08:50:11'),
(43, 5, 9, 'member', 'what is ur top sales ?', 1, '2025-12-21 15:37:52'),
(44, 5, 9, 'member', 'and what is the most expensive product?', 1, '2025-12-21 15:39:22'),
(45, 5, 1, 'admin', 'maybe is the hamster villa cage', 1, '2025-12-21 16:47:54'),
(46, 5, 9, 'member', 'ok', 0, '2025-12-21 17:22:43');

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

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`favorite_id`, `user_id`, `product_id`, `added_at`) VALUES
(14, 8, 89, '2025-12-19 21:40:19');

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
(174, 7, '789, Hello, KL', 'Kelly', '012-3456789', 114.00, 5.00, 'west', 'Touch \'n Go', NULL, NULL, NULL, '2025-12-19 18:53:02', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2001, 5, NULL, '', '', 95.06, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-01-12 15:30:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2002, 6, NULL, '', '', 228.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-02-18 10:45:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2004, 5, NULL, '', '', 163.60, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-04-22 13:10:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2005, 6, NULL, '', '', 177.85, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-05-08 11:55:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2006, 7, NULL, '', '', 285.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-06-14 16:40:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2007, 5, NULL, '', '', 179.82, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-07-20 09:15:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2008, 6, NULL, '', '', 194.50, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-08-03 14:25:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2009, 7, NULL, '', '', 358.20, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-09-17 18:50:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2010, 5, NULL, '', '', 282.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-10-09 12:30:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2011, 6, NULL, '', '', 107.60, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-11-25 20:05:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(2012, 7, NULL, '', '', 387.84, 0.00, NULL, NULL, NULL, NULL, NULL, '2023-12-18 17:45:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5001, 5, NULL, '', '', 215.68, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-01-05 12:30:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5002, 6, NULL, '', '', 260.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-01-10 15:45:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5003, 7, NULL, '', '', 211.70, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-01-18 09:20:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5004, 5, NULL, '', '', 139.72, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-01-25 17:55:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5005, 6, NULL, '', '', 280.40, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-02-03 11:10:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5006, 7, NULL, '', '', 313.85, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-02-12 14:25:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5007, 5, NULL, '', '', 162.50, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-02-20 19:40:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5008, 7, NULL, '', '', 195.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-03-02 10:50:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5009, 5, NULL, '', '', 208.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-03-08 16:15:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5010, 6, NULL, '', '', 302.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-03-15 13:35:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(5011, 7, NULL, '', '', 165.60, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-03-22 18:05:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6001, 5, NULL, '', '', 230.65, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-01-08 14:15:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6002, 6, NULL, '', '', 260.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-01-15 10:30:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6003, 7, NULL, '', '', 211.70, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-01-22 17:45:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6004, 6, NULL, '', '', 261.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-02-05 11:20:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6005, 7, NULL, '', '', 105.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-02-14 16:55:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6006, 5, NULL, '', '', 301.40, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-03-03 13:40:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6007, 7, NULL, '', '', 118.60, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-03-18 09:10:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6008, 6, NULL, '', '', 239.70, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-04-07 12:25:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6009, 5, NULL, '', '', 152.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-04-20 18:50:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6010, 7, NULL, '', '', 143.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-05-12 15:35:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6011, 5, NULL, '', '', 271.70, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-05-25 11:05:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6012, 6, NULL, '', '', 131.68, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-06-06 10:45:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6013, 7, NULL, '', '', 261.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-06-19 19:20:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6014, 5, NULL, '', '', 186.70, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-07-04 14:10:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6015, 6, NULL, '', '', 260.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-07-21 16:30:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6016, 7, NULL, '', '', 145.72, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-08-09 12:55:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6017, 5, NULL, '', '', 252.60, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-08-24 17:15:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6018, 6, NULL, '', '', 75.10, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-09-05 11:40:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6019, 7, NULL, '', '', 249.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-09-17 20:00:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6020, 5, NULL, '', '', 264.65, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-10-10 13:25:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6021, 6, NULL, '', '', 289.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-10-28 15:50:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6022, 7, NULL, '', '', 102.70, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-11-08 10:20:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6023, 5, NULL, '', '', 282.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-11-22 18:35:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6024, 6, NULL, '', '', 193.30, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-12-12 14:05:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(6025, 7, NULL, '', '', 466.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2024-12-20 19:55:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7001, 5, NULL, '', '', 152.22, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-04-06 13:20:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7002, 6, NULL, '', '', 348.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-04-15 10:45:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7003, 7, NULL, '', '', 129.50, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-04-25 18:10:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7004, 6, NULL, '', '', 199.78, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-05-09 15:30:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7005, 7, NULL, '', '', 99.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-05-18 11:55:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7006, 5, NULL, '', '', 285.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-06-04 12:40:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7007, 7, NULL, '', '', 228.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-06-20 17:15:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7008, 6, NULL, '', '', 243.40, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-07-07 14:25:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7009, 5, NULL, '', '', 183.80, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-07-22 19:50:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7010, 7, NULL, '', '', 143.00, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-08-05 11:10:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7011, 6, NULL, '', '', 271.70, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-08-19 16:35:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7012, 5, NULL, '', '', 233.75, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-09-03 13:55:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7013, 7, NULL, '', '', 115.70, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-09-16 20:20:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7014, 6, NULL, '', '', 186.70, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-10-08 12:05:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7015, 5, NULL, '', '', 294.90, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-10-24 18:30:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7016, 7, NULL, '', '', 292.85, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-11-06 15:45:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7017, 6, NULL, '', '', 129.50, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-11-21 10:15:00', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7018, 6, '2 Jalan Ss 2/78\r\nSs 2\r\n47300 Petaling Jaya\r\nMYS\r\nAddre', 'Jason', '011-11107767', 382.40, 0.00, 'west', 'Cash on Delivery', NULL, NULL, NULL, '2025-12-19 20:22:17', 'Cancelled', 'Pending Payment', NULL, 'MOE20PCT', 95.60),
(7019, 6, '2 Jalan Ss 2/78\r\nSs 2\r\n47300 Petaling Jaya\r\nMYS\r\nAddre', 'seah', '012-2222222', 8.00, 5.00, 'west', 'Credit/Debit Card', '7777', NULL, NULL, '2025-12-20 22:53:25', 'To Ship', 'Pending Payment', NULL, NULL, 0.00),
(7020, 6, '2 Jalan Ss 2/78\r\nSs 2\r\n47300 Petaling Jaya\r\nMYS\r\nAddre', 'seah', '012-2222222', 19.88, 5.00, 'west', 'Credit/Debit Card', '1237', NULL, NULL, '2025-12-21 02:01:12', 'Completed', 'Pending Payment', NULL, NULL, 0.00),
(7021, 9, '555', 'seah', '012-2222222', 223.84, 0.00, 'west', 'Cash on Delivery', NULL, NULL, NULL, '2025-12-21 08:41:37', 'Completed', 'Pending Payment', NULL, 'MOE20PCT', 55.96),
(7022, 9, '555', 'seah', '012-2222222', 25.59, 5.00, 'west', 'Credit/Debit Card', '3333', NULL, NULL, '2025-12-21 08:44:42', 'Completed', 'Pending Payment', NULL, 'MOEFREE5', 5.00),
(7023, 9, '555, jln tarumt, blk k, 47000, kuala lumpur, malaysia', 'seah', '012-2222222', 54.80, 5.00, 'west', 'Touch \'n Go', NULL, NULL, NULL, '2025-12-21 17:21:32', 'Shipped', 'Pending Payment', NULL, 'MOE10OFF', 10.00);

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
(189, 174, 3, 1, 109.00),
(190, 2001, 2, 1, 43.90),
(191, 2001, 96, 4, 4.99),
(192, 2001, 95, 2, 15.60),
(193, 2002, 3, 2, 109.00),
(194, 2002, 94, 1, 10.90),
(197, 2004, 91, 3, 35.90),
(198, 2004, 93, 1, 55.90),
(199, 2005, 3, 1, 109.00),
(200, 2005, 2, 1, 43.90),
(201, 2005, 96, 5, 4.99),
(202, 2006, 92, 1, 239.00),
(203, 2006, 95, 3, 15.60),
(204, 2007, 89, 1, 139.90),
(205, 2007, 96, 8, 4.99),
(206, 2008, 91, 2, 35.90),
(207, 2008, 93, 2, 55.90),
(208, 2008, 94, 1, 10.90),
(209, 2009, 3, 3, 109.00),
(210, 2009, 95, 2, 15.60),
(211, 2010, 92, 1, 239.00),
(212, 2010, 2, 1, 43.90),
(213, 2011, 96, 10, 4.99),
(214, 2011, 94, 2, 10.90),
(215, 2011, 91, 1, 35.90),
(216, 2012, 3, 2, 109.00),
(217, 2012, 89, 1, 139.90),
(218, 2012, 96, 6, 4.99),
(219, 5001, 96, 12, 4.99),
(220, 5001, 95, 3, 15.60),
(221, 5001, 3, 1, 109.00),
(222, 5002, 92, 1, 239.00),
(223, 5002, 94, 2, 10.90),
(224, 5003, 89, 1, 139.90),
(225, 5003, 91, 2, 35.90),
(226, 5004, 2, 1, 43.90),
(227, 5004, 93, 1, 55.90),
(228, 5004, 96, 8, 4.99),
(229, 5005, 3, 2, 109.00),
(230, 5005, 95, 4, 15.60),
(231, 5006, 92, 1, 239.00),
(232, 5006, 96, 15, 4.99),
(233, 5007, 91, 3, 35.90),
(234, 5007, 2, 1, 43.90),
(235, 5007, 94, 1, 10.90),
(236, 5008, 89, 1, 139.90),
(237, 5008, 93, 1, 55.90),
(238, 5009, 3, 1, 109.00),
(239, 5009, 96, 20, 4.99),
(240, 5010, 92, 1, 239.00),
(241, 5010, 95, 2, 15.60),
(242, 5010, 94, 3, 10.90),
(243, 5011, 2, 1, 43.90),
(244, 5011, 91, 2, 35.90),
(245, 5011, 96, 10, 4.99),
(246, 6001, 96, 15, 4.99),
(247, 6001, 95, 3, 15.60),
(248, 6001, 3, 1, 109.00),
(249, 6002, 92, 1, 239.00),
(250, 6002, 94, 2, 10.90),
(251, 6003, 89, 1, 139.90),
(252, 6003, 91, 2, 35.90),
(253, 6004, 3, 2, 109.00),
(254, 6004, 2, 1, 43.90),
(255, 6005, 93, 1, 55.90),
(256, 6005, 96, 10, 4.99),
(257, 6006, 92, 1, 239.00),
(258, 6006, 95, 4, 15.60),
(259, 6007, 91, 3, 35.90),
(260, 6007, 94, 1, 10.90),
(261, 6008, 89, 1, 139.90),
(262, 6008, 96, 20, 4.99),
(263, 6009, 3, 1, 109.00),
(264, 6009, 2, 1, 43.90),
(265, 6010, 93, 2, 55.90),
(266, 6010, 95, 2, 15.60),
(267, 6011, 92, 1, 239.00),
(268, 6011, 94, 3, 10.90),
(269, 6012, 91, 2, 35.90),
(270, 6012, 96, 12, 4.99),
(271, 6013, 3, 2, 109.00),
(272, 6013, 2, 1, 43.90),
(273, 6014, 89, 1, 139.90),
(274, 6014, 95, 3, 15.60),
(275, 6015, 92, 1, 239.00),
(276, 6015, 94, 2, 10.90),
(277, 6016, 93, 1, 55.90),
(278, 6016, 96, 18, 4.99),
(279, 6017, 91, 4, 35.90),
(280, 6017, 3, 1, 109.00),
(281, 6018, 2, 1, 43.90),
(282, 6018, 95, 2, 15.60),
(283, 6019, 92, 1, 239.00),
(284, 6019, 94, 1, 10.90),
(285, 6020, 89, 1, 139.90),
(286, 6020, 96, 25, 4.99),
(287, 6021, 3, 2, 109.00),
(288, 6021, 91, 2, 35.90),
(289, 6022, 93, 1, 55.90),
(290, 6022, 95, 3, 15.60),
(291, 6023, 92, 1, 239.00),
(292, 6023, 2, 1, 43.90),
(293, 6024, 96, 30, 4.99),
(294, 6024, 94, 4, 10.90),
(295, 6025, 3, 3, 109.00),
(296, 6025, 89, 1, 139.90),
(297, 7001, 96, 18, 4.99),
(298, 7001, 95, 4, 15.60),
(299, 7002, 3, 1, 109.00),
(300, 7002, 92, 1, 239.00),
(301, 7003, 91, 3, 35.90),
(302, 7003, 94, 2, 10.90),
(303, 7004, 89, 1, 139.90),
(304, 7004, 96, 12, 4.99),
(305, 7005, 2, 1, 43.90),
(306, 7005, 93, 1, 55.90),
(307, 7006, 92, 1, 239.00),
(308, 7006, 95, 3, 15.60),
(309, 7007, 3, 2, 109.00),
(310, 7007, 94, 1, 10.90),
(311, 7008, 91, 4, 35.90),
(312, 7008, 96, 20, 4.99),
(313, 7009, 89, 1, 139.90),
(314, 7009, 2, 1, 43.90),
(315, 7010, 93, 2, 55.90),
(316, 7010, 95, 2, 15.60),
(317, 7011, 92, 1, 239.00),
(318, 7011, 94, 3, 10.90),
(319, 7012, 3, 1, 109.00),
(320, 7012, 96, 25, 4.99),
(321, 7013, 91, 2, 35.90),
(322, 7013, 2, 1, 43.90),
(323, 7014, 89, 1, 139.90),
(324, 7014, 95, 3, 15.60),
(325, 7015, 92, 1, 239.00),
(326, 7015, 93, 1, 55.90),
(327, 7016, 3, 2, 109.00),
(328, 7016, 96, 15, 4.99),
(329, 7017, 91, 3, 35.90),
(330, 7017, 94, 2, 10.90),
(331, 7018, 92, 2, 239.00),
(332, 7019, 103, 3, 1.00),
(333, 7020, 99, 1, 14.88),
(334, 7021, 89, 2, 139.90),
(335, 7022, 104, 1, 25.59),
(336, 7023, 98, 2, 29.90);

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
  `photo_name` varchar(255) NOT NULL,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_code`, `product_name`, `description`, `price`, `stock_quantity`, `category_id`, `is_active`, `created_at`, `updated_at`, `photo_name`, `average_rating`, `review_count`) VALUES
(2, 'CAG0001', 'Luxury Cat Villa Carrier', '3-level cage with wheel', 43.90, 7, 2, 1, '2025-11-23 23:22:24', '2025-12-19 15:42:48', 'prod_693f05ea19699.jpg', 0.00, 0),
(3, 'FOD0001', 'Royal Canin Puppy 10kg', '~ Each pack is designed for adult dogs and available in different sizes to meet your feeding needs.', 109.00, 78, 3, 1, '2025-11-23 23:22:24', '2025-12-19 18:53:02', 'prod_693f165ba2028.jpg', 0.00, 0),
(89, 'CAG0002', 'Big Home Shape Cage', 'Available for cat & dog', 139.90, 1, 2, 1, '2025-12-15 03:00:35', '2025-12-21 16:43:46', '693f095361e88.jpg', 4.00, 1),
(91, 'CAG0004', 'Standard Pet Cage', 'Normal cage for our cute pets', 35.90, 39, 2, 1, '2025-12-15 03:04:33', '2025-12-20 23:14:10', 'prod_693f0bd3c4960.jpg', 5.00, 1),
(92, 'CAG0005', 'Luxury Hamster Villa Cage', 'Benefit:\r\n-Can put many hamsters', 239.00, 13, 2, 1, '2025-12-15 03:06:43', '2025-12-19 20:22:17', '693f0ac353967.jpg', 0.00, 0),
(93, 'CAG0003', 'Transparent Carries', 'Benefits:\r\n- Easily see your cutest pets', 55.90, 17, 2, 1, '2025-12-15 03:15:09', '2025-12-19 17:37:48', '693f0cbd71b9b.jpg', 0.00, 0),
(94, 'ACC0001', 'Pet Automatic Retractable Leash', '• Length Options: 3m / 5m\r\n• Material: Polyester + PP', 10.90, 25, 5, 1, '2025-12-15 03:24:38', '2025-12-19 16:31:18', 'prod_693f106d7be39.jpg', 0.00, 0),
(95, 'CLR0001', 'Pet Hair Remover Comb', '✔️Simply push the button, wipe, making it super simple to remove all the hair from the brush, so it\'s ready for the next time use.\r\n✔️Suit for dog, cat, rabbits and other pets, making them neat and clean.', 15.60, 28, 4, 1, '2025-12-15 03:38:35', '2025-12-19 17:43:45', '693f123b2857e.jpg', 0.00, 0),
(96, 'TOY0001', 'Cat Teaser Stick Toys with Bell', '🐾 Give your pets endless fun with this Cat Teaser Stick Toy with Bell. Interactive design, featuring a dangling bell and feather that will keep your cat entertained and engaged for hours!', 4.99, 11, 1, 1, '2025-12-15 03:43:09', '2025-12-19 12:12:21', '693f134dee5c4.jpg', 0.00, 0),
(97, 'CLR0002', 'Pet Grooming Set', '~ Pet gloves grooming\r\n~ Pet grooming comb * 2', 35.80, 120, 4, 1, '2025-12-20 20:03:45', '2025-12-20 20:03:45', '694690a1c1dfa.jpg', 0.00, 0),
(98, 'ACC0002', 'Automatic Water Food Dispenser 2 IN 1 Pet Feeder', '🐾 Enjoy peace of mind with our Automatic Pet Feeder and Water Dispenser, designed for cats, dogs, and rabbits. \r\n🐾 Large capacity, ensuring your pets stay fed and hydrated for days without frequent refills!', 29.90, 53, 5, 1, '2025-12-20 20:12:34', '2025-12-21 17:21:32', '694692b29c172.jpg', 0.00, 0),
(99, 'ACC0003', 'Washable Dog Bed Cat Bed Oval Sleeping Mat', '👉👉Specifications:\r\n\r\nFeatures for bed\r\n\r\n~Soft and Comfortable, Extra Large Space, Cervical Spine Care, Removable and Washable\r\n~Anti-Skid Epoxy: Moisture-Proof and Moisture-Proof, Effective Anti-Skid without Shifting.\r\n~One-Piece Nest, Sleeping around the Pillow Is Very Comfortable, Running around Deep Sleep.\r\n~Pillow Has High Rebound, No Collapse, Soft and Elastic, and It Is Very Comfortable to Sleep on It.\r\n~Full Filling, Long Sleep without Collapse, 360 ° Circular Package.', 14.88, 74, 5, 1, '2025-12-20 20:23:07', '2025-12-21 02:51:42', '6946952b30c91.jpg', 0.00, 0),
(100, 'ACC0004', 'L-Shaped Corner Wall Scratcher for Cats', '【Materials】: Cat Scratching Board is crafted from corrugated paper, density board. This cat scratching board is designed to withstand vigorous scratching, ensuring long term use\r\n\r\n【Stylish Design】: Featuring a minimalist design, this cat scratcher seamlessly blends into any home decor, making it a functional yet stylish addition to your living space\r\n\r\n【Furniture Protection】: By attracting your cat attention for play and claw sharpening, this pet cat scratch board protects your sofa and bed', 59.70, 36, 5, 1, '2025-12-20 20:28:43', '2025-12-20 20:28:43', '6946967b4025c.jpg', 0.00, 0),
(101, 'ACC0005', 'Large Dog Leash Vest Style Dog Chest Strap', '🔦 *** High Visibility Safety Reflective **:\r\n\r\n - Reflective Strips At Night Cover The Harness And Leash, Visible Within 200 Meters, Walking The Dog In Rainy/Night Is More Secure.\r\n\r\n 🐕 *** Comfortable Fit **:\r\n\r\n - Breathable Inner Lining, Adjustable Bust/Neck Circumference Is Not Stuffy To Wear For A Long Time.', 25.25, 40, 5, 1, '2025-12-20 20:37:37', '2025-12-22 00:22:18', '69469891853be.jpg', 0.00, 0),
(102, 'TOY0002', 'Pet Squeaky Duck Chew Toy Cat Dog', '[Product Features]\r\n\r\n~Realistic squeaking sound attracts pet attention interactive play\r\n~Soft plush material safe chewing teething relief puppies kittens\r\n~Durable reinforced stitching withstand biting multiple size options\r\n~Self amusement function reduces boredom home alone time\r\n~Bright yellow color cute duck design orange beak feet', 4.93, 60, 1, 1, '2025-12-20 20:46:11', '2025-12-20 20:46:11', '69469a93cb353.jpg', 0.00, 0),
(103, 'FOD0002', 'Pet Sausage Healthy Pet Hotdog Food Snack For Cats And Dogs', '🐾  High Protein & Low Fat Goodness: - Our 15g Pet Sausage Snack is the ultimate treat for your beloved cats and dogs! \r\n\r\n🐾  Packed with high protein and low fat, it\'s a nutritious reward that your pets will love.', 1.00, 97, 3, 1, '2025-12-20 20:49:43', '2025-12-20 22:53:25', '69469b6789665.jpg', 0.00, 0),
(104, 'FOD0003', 'Dry Kibble Dog Food for Puppy/Young Dog', '- Chicken/Egg/Milk (1.5KG)', 25.59, 45, 3, 1, '2025-12-20 21:00:22', '2025-12-21 08:49:16', '69469de6dcc39.jpg', 5.00, 1),
(105, 'FOD0004', 'Freeze Dried Chicken Pet Food', '😻High-protein nutrition promotes muscle development\r\n\r\nRich in high-quality protein, supports pet muscle growth and energy supplementation, and avoids obesity problems\r\n\r\n\r\n\r\n😻Low fat and easy to digest, healthy and worry-free\r\n\r\nLow fat content, easy for pets to digest and absorb, reducing gastrointestinal burden, suitable for sensitive pets\r\n\r\n\r\n\r\n😻Natural ingredients, no additives\r\n\r\nMade from 100% pure chicken breast, no preservatives, pigments or artificial additives, ensuring safety and naturalness\r\n\r\n\r\n\r\n😻Freeze-drying process retains nutrition and flavor\r\n\r\nUsing freeze-drying technology to lock in the original nutrition and delicious taste of chicken, with less nutrient loss', 19.90, 45, 3, 1, '2025-12-20 21:13:02', '2025-12-20 21:13:02', '6946a0de3d2e0.jpg', 0.00, 0),
(106, 'TOY0003', 'Pet Toy Dog UFO Ball | Portable UFO Dog Toy', 'Environmentally Friendly Materials: The Dog Dish Ball Is Made Of High-Quality PE Plastic Material, Which Is Tough, Durable, Safe And Non-Toxic.It Will Not Cause Any Harm To Teeth, But Please Do Not Let Your Dog Chew Too Much.', 13.90, 23, 1, 1, '2025-12-20 21:23:05', '2025-12-20 21:23:05', '6946a3399c923.jpg', 0.00, 0);

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
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `review_date` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `is_verified_purchase` tinyint(1) DEFAULT 1,
  `admin_reply` text DEFAULT NULL,
  `admin_reply_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`review_id`, `product_id`, `user_id`, `order_id`, `rating`, `review_text`, `review_date`, `updated_at`, `is_verified_purchase`, `admin_reply`, `admin_reply_date`) VALUES
(2, 91, 6, 7017, 5, 'good', '2025-12-20 23:14:10', '2025-12-21 03:04:01', 1, 'tqvm~ and hv a nice day, pls give 5 stars', '2025-12-21 03:04:01'),
(4, 104, 9, 7022, 5, 'my dog very like this dog food, will purchase again, yeye', '2025-12-21 08:49:16', '2025-12-21 17:31:32', 1, 'tq~', '2025-12-21 17:31:32'),
(5, 89, 9, 7021, 4, 'the quality good condition', '2025-12-21 16:43:46', '2025-12-21 16:47:27', 1, 'have a nice day~ 😘', '2025-12-21 16:47:27');

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
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
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

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `home_address`, `password`, `reset_token`, `reset_token_expiry`, `role`, `created_at`, `profile_pic`, `locked`, `lock_reason`, `locked_at`) VALUES
(1, 'Seahnijun', '', '', '0', '$2y$10$jSANxKW6shQ/CsKMsKzSXeqiOue5QFD2DnhPO/SwQJDAwdgtR1lRO', NULL, NULL, 'admin', '2025-11-22 17:27:12', 'uploads/profile_pics/1_1765859839_admin.jpg', 0, NULL, NULL),
(5, 'abc', 'abc123@yahoo.com', '012-3456789', '0', '$2y$10$EKljSiD3aP0XAT.wLJdBKe7puFh/gvRdAGlaGoHU7aJ3tHfWqdqGi', NULL, NULL, 'member', '2025-11-22 22:32:05', 'uploads/profile_pics/5_1765885108_otter.jpg', 0, NULL, NULL),
(6, 'haha', 'rokuya124@gmail.com', '012-2222222', '0', '$2y$10$Fv.4ZLPVE/p1gpn7IQeKKuqqUpRMvY0YMJfWv6teJnMiHs4b06/J2', NULL, NULL, 'member', '2025-12-06 23:12:07', 'uploads/profile_pics/6_1765302986_iu-3.jpg', 0, 'Suspicious activity', NULL),
(7, 'aaa', 'tanyijia-wp23@student.tarc.edu.my', '0123456789', '0', '$2y$10$k5r/g6EeYTTKXn1w06SEUutPnBB9ASLIHWZbut1A5pbh/8Z0bigS.', NULL, NULL, 'member', '2025-12-13 16:42:33', 'uploads/profile_pics/7_1766119675_images.jpg', 0, 'Suspicious activity', NULL),
(8, 'Jasonyap_1022', 'jasonyap102204@gmail.com', '01111111111', '', '$2y$10$0o9jO9W3DCblF67T.rlwKuoVaM1wNIscdOk4w0AAJubulef885uA.', NULL, NULL, 'member', '2025-12-19 21:33:00', 'uploads/profile_pics/8_1766151590_Hu.Tao.full.3511224.jpg', 0, 'Payment issues', NULL),
(9, 'tester', 'haha@gmail.com', '012-2222222', '', '$2y$10$zR5fCraEwopzo7Z.GuSncuvM1ThREopDifEAq48B/GGckqckAkLU.', NULL, NULL, 'member', '2025-12-21 08:38:07', 'uploads/profile_pics/9_1766277586_2.jpg', 0, NULL, NULL);

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
(65, 7, 'Home', 'Abby', '015-69874236', '789, Hello, KL', '2025-12-19 08:45:32'),
(66, 6, 'Home', 'Jason', '011-11107767', '2 Jalan Ss 2/78\r\nSs 2\r\n47300 Petaling Jaya\r\nMYS\r\nAddre', '2025-12-19 12:22:17'),
(67, 9, 'School', 'seah', '012-2222222', '555', '2025-12-21 00:41:37'),
(68, 9, 'School', 'seah', '012-2222222', '555, jln tarumt, blk k, 47000, kuala lumpur, malaysia', '2025-12-21 09:21:32');

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
(1, 'MOE10OFF', 'fixed', 10.00, 50.00, '2026-04-30', 100, 6, '2025-12-19 05:20:19'),
(2, 'MOE20PCT', 'percentage', 20.00, 100.00, '2026-12-31', NULL, 5, '2025-12-19 05:20:19'),
(3, 'MOEFREE5', 'fixed', 5.00, 20.00, '2026-08-31', NULL, 7, '2025-12-19 06:26:15');

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
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `created_at` (`created_at`);

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
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_user_product_order` (`user_id`,`product_id`,`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `rating` (`rating`);

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=293;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7024;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=337;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

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
-- Constraints for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD CONSTRAINT `fk_chat_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_chat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_message_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
