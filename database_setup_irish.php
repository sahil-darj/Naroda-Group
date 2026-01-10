<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDB();
    
    // 1. Apartment Plans Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ni_apartment_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(20) NOT NULL,
        area VARCHAR(50),
        bedrooms INT,
        bathrooms INT,
        balconies INT,
        description_en TEXT,
        description_hi TEXT,
        description_gu TEXT,
        image_url TEXT,
        status VARCHAR(20) DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // 2. Gallery Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ni_gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        description TEXT,
        image_url TEXT NOT NULL,
        is_featured BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Pricing Plans Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ni_pricing_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(20) NOT NULL,
        starting_price VARCHAR(50),
        sqft VARCHAR(50),
        bedrooms INT,
        bathrooms INT,
        parking VARCHAR(50),
        available_units INT,
        availability_status VARCHAR(50),
        features_en JSON,
        features_hi JSON,
        features_gu JSON,
        status VARCHAR(20) DEFAULT 'active',
        last_updated DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // 4. Featured Properties Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ni_featured_properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id VARCHAR(50) UNIQUE,
        category VARCHAR(50), -- for-sale, for-rent
        status VARCHAR(50), -- Available, Sold Out, etc.
        title_en VARCHAR(255),
        title_hi VARCHAR(255),
        title_gu VARCHAR(255),
        location_en VARCHAR(255),
        location_hi VARCHAR(255),
        location_gu VARCHAR(255),
        price VARCHAR(50),
        price_unit VARCHAR(50),
        area VARCHAR(50),
        floor VARCHAR(50),
        parking VARCHAR(50),
        facing VARCHAR(50),
        description_en TEXT,
        description_hi TEXT,
        description_gu TEXT,
        overview_description_en TEXT,
        overview_description_hi TEXT,
        overview_description_gu TEXT,
        overview_features_en JSON,
        overview_features_hi JSON,
        overview_features_gu JSON,
        amenities_sections_en JSON,
        amenities_sections_hi JSON,
        amenities_sections_gu JSON,
        floor_plans_dimensions JSON,
        location_details JSON,
        documents JSON,
        images JSON,
        brochure JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // 5. Schedule Visits (Inquiries) Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ni_schedule_visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(20), -- sale, rent
        name VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(50),
        inquiry_type VARCHAR(100),
        property_id VARCHAR(50),
        property_title VARCHAR(255),
        message TEXT,
        preferred_date DATE,
        status VARCHAR(50) DEFAULT 'new',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    echo json_encode(['success' => true, 'message' => 'Naroda Irish tables created successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
