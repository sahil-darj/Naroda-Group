-- =====================================================
-- Naroda Landmark Database Tables
-- Database: naroda_group
-- Created: 2025-12-26
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =====================================================
-- 1. Floor Plans Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `landmark_floor_plans` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `floor_type` VARCHAR(50) NOT NULL COMMENT 'ground, first-fifth, sixth-seventh, eighth-ninth',
  `office_size` INT(11) DEFAULT 0,
  `shops_per_floor` INT(11) DEFAULT 0,
  `elevators` INT(11) DEFAULT 0,
  `total_floors` INT(11) DEFAULT 0,
  `description_en` TEXT DEFAULT NULL,
  `description_hi` TEXT DEFAULT NULL,
  `description_gu` TEXT DEFAULT NULL,
  `image_url` LONGTEXT DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_floor_type` (`floor_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. Gallery Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `landmark_gallery` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title_en` VARCHAR(255) DEFAULT NULL,
  `title_hi` VARCHAR(255) DEFAULT NULL,
  `title_gu` VARCHAR(255) DEFAULT NULL,
  `description_en` TEXT DEFAULT NULL,
  `description_hi` TEXT DEFAULT NULL,
  `description_gu` TEXT DEFAULT NULL,
  `image_url` LONGTEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT 'general',
  `status` VARCHAR(20) DEFAULT 'active',
  `display_order` INT(11) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category`),
  KEY `idx_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. Pricing Table (Office & Retail)
-- =====================================================
CREATE TABLE IF NOT EXISTS `landmark_pricing` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pricing_type` ENUM('office', 'retail') NOT NULL,
  `rental_price` VARCHAR(100) DEFAULT NULL,
  `features_en` JSON DEFAULT NULL,
  `features_hi` JSON DEFAULT NULL,
  `features_gu` JSON DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pricing_type` (`pricing_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. Featured Properties Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `landmark_properties` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category` VARCHAR(50) NOT NULL COMMENT 'for-sale, for-rent',
  `title_en` VARCHAR(255) DEFAULT NULL,
  `title_hi` VARCHAR(255) DEFAULT NULL,
  `title_gu` VARCHAR(255) DEFAULT NULL,
  `location_en` VARCHAR(255) DEFAULT NULL,
  `location_hi` VARCHAR(255) DEFAULT NULL,
  `location_gu` VARCHAR(255) DEFAULT NULL,
  `type_en` VARCHAR(100) DEFAULT NULL,
  `type_hi` VARCHAR(100) DEFAULT NULL,
  `type_gu` VARCHAR(100) DEFAULT NULL,
  `description_en` TEXT DEFAULT NULL,
  `description_hi` TEXT DEFAULT NULL,
  `description_gu` TEXT DEFAULT NULL,
  `features_en` TEXT DEFAULT NULL,
  `features_hi` TEXT DEFAULT NULL,
  `features_gu` TEXT DEFAULT NULL,
  `area` INT(11) DEFAULT 0,
  `floor` VARCHAR(50) DEFAULT NULL,
  `parking` INT(11) DEFAULT 0,
  `status` VARCHAR(50) DEFAULT 'Available',
  `price` VARCHAR(100) DEFAULT NULL,
  `price_unit` VARCHAR(50) DEFAULT NULL,
  `property_id` VARCHAR(50) DEFAULT NULL,
  `facing` VARCHAR(50) DEFAULT NULL,
  `images` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_property_id` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. Inquiries Table (Schedule Visit)
-- =====================================================
CREATE TABLE IF NOT EXISTS `landmark_inquiries` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `inquiry_type` ENUM('sale', 'rent') NOT NULL,
  `property_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inquiry_type` (`inquiry_type`),
  KEY `idx_status` (`status`),
  KEY `idx_property_id` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- End of Naroda Landmark Tables
-- =====================================================
