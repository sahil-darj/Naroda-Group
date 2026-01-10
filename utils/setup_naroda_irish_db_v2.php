<?php
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Apartments Table
    $conn->exec("CREATE TABLE IF NOT EXISTS naroda_irish_apartments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(10) NOT NULL, -- 1bhk, 2bhk, 3bhk
        area DECIMAL(10, 2),
        bedrooms INT,
        bathrooms INT,
        balconies INT,
        description_en TEXT,
        description_hi TEXT,
        description_gu TEXT,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_type (type)
    )");

    // Gallery Table
    $conn->exec("CREATE TABLE IF NOT EXISTS naroda_irish_gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_url VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Pricing Table
    $conn->exec("CREATE TABLE IF NOT EXISTS naroda_irish_pricing (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(10) NOT NULL, -- 1bhk, 2bhk, 3bhk
        starting_price VARCHAR(50),
        sqft VARCHAR(50),
        bedrooms INT,
        bathrooms INT,
        parking VARCHAR(100),
        available_units INT,
        status VARCHAR(50),
        features_en JSON,
        features_hi JSON,
        features_gu JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_pricing_type (type)
    )");

    // Featured Properties Table
    $conn->exec("CREATE TABLE IF NOT EXISTS naroda_irish_featured (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title_en VARCHAR(255),
        title_hi VARCHAR(255),
        title_gu VARCHAR(255),
        location_en VARCHAR(255),
        price VARCHAR(50),
        type VARCHAR(50),
        status VARCHAR(50),
        image_url VARCHAR(255),
        features_en JSON, 
        features_hi JSON,
        features_gu JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    echo "Tables for Naroda Irish created successfully.";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
