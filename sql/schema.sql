-- Inventory Management System Schema
-- MySQL 5.7+ / MariaDB 10.3+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `inventory_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `inventory_db`;

-- ----------------------------
-- Users
-- ----------------------------
CREATE TABLE `users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `full_name`  VARCHAR(100) NOT NULL,
  `email`      VARCHAR(100) NOT NULL UNIQUE,
  `role`       ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Categories
-- ----------------------------
CREATE TABLE `categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Products
-- ----------------------------
CREATE TABLE `products` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sku`           VARCHAR(50)    NOT NULL UNIQUE,
  `name`          VARCHAR(150)   NOT NULL,
  `description`   TEXT,
  `category_id`   INT UNSIGNED   NOT NULL,
  `price`         DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `stock_qty`     INT            NOT NULL DEFAULT 0,
  `low_stock_threshold` INT      NOT NULL DEFAULT 10,
  `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_sku`      (`sku`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Stock Movements
-- ----------------------------
CREATE TABLE `stock_movements` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`  INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED NOT NULL,
  `type`        ENUM('in','out','adjustment') NOT NULL,
  `quantity`    INT          NOT NULL,
  `note`        VARCHAR(255),
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product`  (`product_id`),
  KEY `idx_user`     (`user_id`),
  KEY `idx_created`  (`created_at`),
  CONSTRAINT `fk_movement_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_movement_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`    (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
