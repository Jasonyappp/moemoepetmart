-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2025 at 03:33 PM
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('member','admin') NOT NULL DEFAULT 'member',
  `created_at` datetime DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password`, `role`, `created_at`, `profile_pic`) VALUES
(1, 'admin123', '', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-11-22 17:27:12', NULL),
(5, 'abc', 'abc123@yahoo.com', '012-3456789', '$2y$10$EKljSiD3aP0XAT.wLJdBKe7puFh/gvRdAGlaGoHU7aJ3tHfWqdqGi', 'member', '2025-11-22 22:32:05', 'uploads/profile_pics/5_1763821950_otter.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================
USE moemoe_petmart;

-- -----------------------------------------------------
-- Table: category
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_code VARCHAR(10) NOT NULL UNIQUE COMMENT 'Prefix for product code: TOY, CAG, FOD, etc.',
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (category_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table: product
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS product (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Format: TOY0001, CAG0005, etc.',
    product_name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    category_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES category(category_id) ON DELETE RESTRICT,
    INDEX idx_code (product_code),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table: product_image
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS product_image (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- Sample Data (Safe to run multiple times)
-- =====================================================

INSERT IGNORE INTO category (category_code, category_name, description) VALUES
('TOY', 'Toys', 'Interactive toys, balls, feather wands, etc.'),
('CAG', 'Cages & Carriers', 'Hamster cages, bird cages, travel carriers'),
('FOD', 'Food', 'Dry food, wet food, treats, milk replacement'),
('CLR', 'Cleaning & Grooming', 'Shampoo, brushes, nail clippers, deodorizers'),
('ACC', 'Accessories', 'Collars, leashes, clothes, beds, bowls');

INSERT IGNORE INTO product (product_code, product_name, description, price, stock_quantity, category_id) VALUES
('TOY0001', 'Feather Teaser Wand Set', '3-piece interactive cat toy with real feathers', 29.90, 50, 1),
('TOY0002', 'Squeaky Rubber Ball (M)', 'Durable dog toy, floats in water', 19.90, 35, 1),
('CAG0001', 'Luxury Hamster Villa Cage', '3-level cage with wheel, tunnel and water bottle', 299.00, 12, 2),
('CAG0002', 'Foldable Pet Carrier Bag', 'Airline approved, size M', 159.90, 8, 2),
('FOD0001', 'Royal Canin Puppy 10kg', 'Complete food for puppies 2-12 months', 399.00, 15, 3),
('FOD0002', 'Whiskas Tuna Wet Food 85g x12', 'Adult cat food in gravy', 89.90, 40, 3),
('CLR0001', 'Petkin Deodorizing Spray 500ml', 'Lavender scent, eliminates odor instantly', 69.90, 25, 4),
('ACC0001', 'LED Light-Up Dog Collar (L)', 'Rechargeable, visible up to 500m', 89.00, 20, 5);