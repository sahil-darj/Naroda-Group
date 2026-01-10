<?php
/**
 * Database Setup Script for Naroda Landmark Tables
 * Run this once to create all required tables
 */

header('Content-Type: text/plain');

$host = 'localhost';
$dbname = 'naroda_group';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database: $dbname\n\n";
    
    // Drop existing tables for clean setup
    $conn->exec("DROP TABLE IF EXISTS `landmark_floor_plans`, `landmark_gallery`, `landmark_pricing`, `landmark_properties`, `landmark_inquiries` ");
    echo "✓ Dropped existing tables (if any)\n";
    
    // Create Floor Plans Table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `landmark_floor_plans` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `floor_type` VARCHAR(50) NOT NULL,
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
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Created table: landmark_floor_plans\n";
    
    // Create Gallery Table
    $conn->exec("
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
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Created table: landmark_gallery\n";
    
    // Create Pricing Table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `landmark_pricing` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `pricing_type` VARCHAR(20) NOT NULL,
            `rental_price` VARCHAR(100) DEFAULT NULL,
            `features_en` TEXT DEFAULT NULL,
            `features_hi` TEXT DEFAULT NULL,
            `features_gu` TEXT DEFAULT NULL,
            `status` VARCHAR(20) DEFAULT 'active',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Created table: landmark_pricing\n";
    
    // Create Properties Table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `landmark_properties` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `category` VARCHAR(50) NOT NULL,
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
            `images` LONGTEXT DEFAULT NULL,
            `brochure` LONGTEXT DEFAULT NULL,
            `property_documents` LONGTEXT DEFAULT NULL,
            `approvals_documents` LONGTEXT DEFAULT NULL,
            `room_dimensions` LONGTEXT DEFAULT NULL,
            `map_iframe` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Created table: landmark_properties\n";
    
    // Create Inquiries Table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `landmark_inquiries` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `inquiry_type` VARCHAR(20) NOT NULL,
            `property_id` INT(11) DEFAULT NULL,
            `name` VARCHAR(255) DEFAULT NULL,
            `email` VARCHAR(255) DEFAULT NULL,
            `phone` VARCHAR(50) DEFAULT NULL,
            `message` TEXT DEFAULT NULL,
            `status` VARCHAR(50) DEFAULT 'new',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Created table: landmark_inquiries\n";
    
    echo "\n========================================\n";
    echo "✓ All 5 tables created successfully!\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
