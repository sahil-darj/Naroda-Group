<?php
require_once 'db_config.php';

// Add columns if they don't exist
$columns = [
    "ADD COLUMN IF NOT EXISTS bedrooms VARCHAR(50)",
    "ADD COLUMN IF NOT EXISTS bathrooms VARCHAR(50)",
    "ADD COLUMN IF NOT EXISTS floor_plans_dimensions JSON"
];

foreach ($columns as $col) {
    $sql = "ALTER TABLE featured_properties $col";
    if ($conn->query($sql) === TRUE) {
        echo "Column added successfully or already exists: $col<br>";
    } else {
        echo "Error adding column $col: " . $conn->error . "<br>";
    }
}

$conn->close();
?>
