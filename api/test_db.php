<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'naroda_group';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Database connected successfully!<br>";

// Test if table exists
$result = $conn->query("SHOW TABLES LIKE 'faqs'");
if ($result->num_rows > 0) {
    echo "✅ FAQs table exists!<br>";
    
    // Count FAQs
    $result = $conn->query("SELECT COUNT(*) as count FROM faqs");
    $row = $result->fetch_assoc();
    echo "📊 FAQs in table: " . $row['count'] . "<br>";
    
    // Show sample data
    $result = $conn->query("SELECT id, question, category, status FROM faqs LIMIT 5");
    echo "<br>📋 Sample FAQs:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- ID: " . $row['id'] . ", Question: " . substr($row['question'], 0, 50) . "...<br>";
    }
} else {
    echo "❌ FAQs table does not exist!<br>";
    
    // Try to create table
    echo "Attempting to create table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS faqs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        question_hi TEXT,
        question_gu TEXT,
        answer LONGTEXT NOT NULL,
        answer_hi LONGTEXT,
        answer_gu LONGTEXT,
        category VARCHAR(50) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        display_order INT DEFAULT 1,
        tags TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (category),
        INDEX idx_status (status),
        INDEX idx_order (display_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Table created successfully!<br>";
    } else {
        echo "❌ Error creating table: " . $conn->error . "<br>";
    }
}

$conn->close();
?>