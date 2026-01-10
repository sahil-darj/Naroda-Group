<?php
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    $sql = "CREATE TABLE IF NOT EXISTS naroda_irish_inquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        message TEXT,
        property_id VARCHAR(50),
        property_title VARCHAR(255),
        type VARCHAR(50) DEFAULT 'general', -- 'sale', 'rent', 'general'
        status VARCHAR(50) DEFAULT 'new',   -- 'new', 'contacted', 'closed'
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $conn->exec($sql);
    echo "Table 'naroda_irish_inquiries' created successfully.\n";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
