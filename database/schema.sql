-- ==========================================================
-- S PARFUM - DATABASE SCHEMA & SEED DATA (database/schema.sql)
-- Purpose: Defines relational MySQL tables (users, products,
-- orders, order_items) with foreign keys and seed records.
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `sparfum_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sparfum_db`;

-- Drop tables in reverse order of foreign keys
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;

-- 1. USERS TABLE
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `address` TEXT NULL,
    `role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. PRODUCTS TABLE
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `subtitle` VARCHAR(150) NOT NULL,
    `category` ENUM('Floral', 'Warm', 'Fresh', 'Signature') NOT NULL DEFAULT 'Signature',
    `description` TEXT NOT NULL,
    `scent_notes` VARCHAR(255) NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `image` VARCHAR(255) NOT NULL,
    `featured` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ORDERS TABLE
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(150) NOT NULL,
    `customer_phone` VARCHAR(30) NOT NULL,
    `shipping_address` TEXT NOT NULL,
    `delivery_date` DATE NULL,
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
    `notes` TEXT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL,
    `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Processing',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ORDER ITEMS TABLE
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NULL,
    `product_name` VARCHAR(100) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `quantity` INT NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED DATA
-- ==========================================================

-- Admin & Sample Customer accounts
-- Passwords are:
-- admin@sparfum.com -> admin123
-- customer@sparfum.com -> customer123
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `address`, `role`) VALUES
('Administrator', 'admin@sparfum.com', '$2y$10$JIv8pqjyE1iKOR42zLr0gutz3tBf5JUhHMSmbufIFyfbJNirzz2jm', '+63 912 345 6789', 'S Parfum, Dumaguete City, Philippines', 'admin'),
('Maria Santos', 'customer@sparfum.com', '$2y$10$4vwVWjfnSiLwNqgnHqudQOLdMsmc48jxgsHEITiMlLHHgXRpdVPXe', '+63 917 888 1234', '123 Rizal Boulevard, Dumaguete City, Negros Oriental, 6200', 'customer');

-- Products matching the PDF Mockup
INSERT INTO `products` (`name`, `subtitle`, `category`, `description`, `scent_notes`, `price`, `stock`, `image`, `featured`) VALUES
(
    'S PARFUM I',
    'Floral | Soft | Elegant',
    'Floral',
    'An enchanting feminine elixir woven with delicate blooming gardenia, soft white jasmine, and velvet rose petals with a warm cashmere musk whisper. Evokes effortless grace and timeless sophistication.',
    'Top: White Peony, Italian Bergamot | Heart: Grasse Rose, Blooming Jasmine | Base: Cashmere Woods, White Amber',
    2450.00,
    15,
    'assets/images/PerfumeShine.jpg',
    1
),
(
    'S PARFUM II',
    'Warm | Sensual | Refined',
    'Warm',
    'A luxurious nocturnal aura enveloped in rare golden amber, rich Madagascar vanilla bourbon, roasted tonka bean, and spiced saffron cedarwood. Bold, intoxicating, and undeniably magnetic.',
    'Top: Golden Saffron, Bergamot Rind | Heart: Madagascar Vanilla, Cinnamon Bark | Base: Rich Amber, Tonka Bean, Sandalwood',
    2850.00,
    8,
    'assets/images/PerfumeGold.png',
    1
),
(
    'S PARFUM III',
    'Fresh | Delicate | Timeless',
    'Fresh',
    'A breath of crystalline morning dew, sparkling citrus zest, delicate aquatic blossoms, and clean vetiver. An uplifting sanctuary of purity and breezy coastal breezes.',
    'Top: Crisp Sea Breeze, Sicilian Lemon | Heart: Water Lily, White Tea | Base: Clean Vetiver, Cedarwood, Sheer Musk',
    2650.00,
    12,
    'assets/images/PerfumeBlack.png',
    1
);

