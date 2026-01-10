<?php
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$table_name = "team_members";

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id INT(11) NOT NULL AUTO_INCREMENT,
    firstname_en VARCHAR(100) NOT NULL,
    lastname_en VARCHAR(100) NOT NULL,
    firstname_hi VARCHAR(100),
    lastname_hi VARCHAR(100),
    firstname_gu VARCHAR(100),
    lastname_gu VARCHAR(100),
    
    role_en VARCHAR(100) NOT NULL,
    role_hi VARCHAR(100),
    role_gu VARCHAR(100),
    
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    department VARCHAR(100),
    experience INT(11) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'Active',
    
    bio_en TEXT,
    bio_hi TEXT,
    bio_gu TEXT,
    
    professional_background_en TEXT,
    professional_background_hi TEXT,
    professional_background_gu TEXT,
    
    achievements_en TEXT,
    achievements_hi TEXT,
    achievements_gu TEXT,
    
    education_en TEXT,
    education_hi TEXT,
    education_gu TEXT,
    
    avatar TEXT,
    linkedin_url VARCHAR(255),
    twitter_url VARCHAR(255),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    $conn->exec($sql);
    echo "Table '$table_name' created successfully (or already exists).\n";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
