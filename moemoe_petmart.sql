-- ====================================================
-- Moe Moe Pet Mart - Complete Database Setup
-- Database name: moemoe_petmart
-- ====================================================

-- Create the database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS moemoe_petmart 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

-- Use the new database
USE moemoe_petmart;

-- Drop table if exists (safe to run multiple times)
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('member', 'admin') NOT NULL DEFAULT 'member',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert your beloved admin account
-- Username: admin123
-- Password: yap123  (hashed safely with PHP password_hash())
INSERT INTO users (username, password, role) VALUES 
('admin123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE password = VALUES(password);
