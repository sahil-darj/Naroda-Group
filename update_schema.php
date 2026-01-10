<?php
header('Content-Type: text/plain');

require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Connected to database.\n";
    
    // 1. Update landmark_pricing
    echo "Updating landmark_pricing...\n";
    try {
        $conn->exec("ALTER TABLE landmark_pricing ADD COLUMN rental_price_hi VARCHAR(100) DEFAULT NULL AFTER rental_price");
        echo "Added rental_price_hi.\n";
    } catch (PDOException $e) {
        echo "Skipped rental_price_hi (maybe exists).\n";
    }
    try {
        $conn->exec("ALTER TABLE landmark_pricing ADD COLUMN rental_price_gu VARCHAR(100) DEFAULT NULL AFTER rental_price_hi");
        echo "Added rental_price_gu.\n";
    } catch (PDOException $e) {
         echo "Skipped rental_price_gu (maybe exists).\n";
    }
    
    // 2. Update landmark_properties
    echo "Updating landmark_properties...\n";
    try {
        $conn->exec("ALTER TABLE landmark_properties ADD COLUMN overview_features_en LONGTEXT DEFAULT NULL AFTER features_gu");
        echo "Added overview_features_en.\n";
    } catch (PDOException $e) {
         echo "Skipped overview_features_en (maybe exists).\n";
    }
    try {
        $conn->exec("ALTER TABLE landmark_properties ADD COLUMN overview_features_hi LONGTEXT DEFAULT NULL AFTER overview_features_en");
        echo "Added overview_features_hi.\n";
    } catch (PDOException $e) {
         echo "Skipped overview_features_hi (maybe exists).\n";
    }
    try {
        $conn->exec("ALTER TABLE landmark_properties ADD COLUMN overview_features_gu LONGTEXT DEFAULT NULL AFTER overview_features_hi");
        echo "Added overview_features_gu.\n";
    } catch (PDOException $e) {
         echo "Skipped overview_features_gu (maybe exists).\n";
    }

    echo "Schema update completed successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
