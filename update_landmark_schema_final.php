<?php
header('Content-Type: text/plain');

require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Connected to database.\n";
    
    $table = 'landmark_properties';
    echo "Updating $table...\n";
    
    $columns = [
        'brochure' => 'JSON DEFAULT NULL',
        'property_documents' => 'JSON DEFAULT NULL',
        'approvals_documents' => 'JSON DEFAULT NULL',
        'room_dimensions' => 'JSON DEFAULT NULL',
        'map_iframe' => 'LONGTEXT DEFAULT NULL',
        'amenities' => 'JSON DEFAULT NULL',
        'floor_plan_image' => 'LONGTEXT DEFAULT NULL'
    ];
    
    foreach ($columns as $column => $definition) {
        try {
            $conn->exec("ALTER TABLE $table ADD COLUMN $column $definition");
            echo "✓ Added column: $column\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "- Column $column already exists, skipping.\n";
            } else {
                echo "✗ Error adding $column: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "Schema update completed successfully.\n.\n";

} catch (PDOException $e) {
    echo "Fatal Error: " . $e->getMessage();
}
?>
