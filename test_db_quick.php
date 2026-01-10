<?php
/**
 * Quick Database Test - Check if landmark_gallery table exists and can insert
 */
header('Content-Type: text/plain');

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'naroda_group';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connected\n\n";
    
    // Check if table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'landmark_gallery'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table 'landmark_gallery' exists\n\n";
        
        // Show table structure
        echo "Table structure:\n";
        $stmt = $conn->query("DESCRIBE landmark_gallery");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['Field']} ({$row['Type']})\n";
        }
        echo "\n";
        
        // Try to insert a test record
        echo "Testing INSERT...\n";
        $testData = [
            'image_url' => 'uploads/gallery/test_' . time() . '.jpg',
            'title_en' => 'Test Image ' . date('Y-m-d H:i:s')
        ];
        
        $sql = "INSERT INTO landmark_gallery (image_url, title_en) VALUES (:image_url, :title_en)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($testData);
        
        $insertId = $conn->lastInsertId();
        echo "✓ INSERT successful! ID: $insertId\n\n";
        
        // Clean up
        $conn->exec("DELETE FROM landmark_gallery WHERE id = $insertId");
        echo "✓ Test record cleaned up\n";
        
    } else {
        echo "✗ Table 'landmark_gallery' does NOT exist!\n";
        echo "Run setup_landmark_tables.php to create it.\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
