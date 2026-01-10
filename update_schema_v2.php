<?php
require_once 'db_config.php';

function addColumnIfNotExists($conn, $table, $column, $definition) {
    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE `$table` ADD `$column` $definition";
        if ($conn->query($sql)) {
            echo "Added column '$column' to '$table'.\n";
        } else {
            echo "Error adding column '$column': " . $conn->error . "\n";
        }
    } else {
        echo "Column '$column' already exists in '$table'.\n";
    }
}

echo "Updating schema...\n";

// Add missing columns to featured_properties
addColumnIfNotExists($conn, 'featured_properties', 'bedrooms', "VARCHAR(10)");
addColumnIfNotExists($conn, 'featured_properties', 'bathrooms', "VARCHAR(10)");
addColumnIfNotExists($conn, 'featured_properties', 'floor_plans_dimensions', "JSON");
addColumnIfNotExists($conn, 'featured_properties', 'location', "JSON"); // Ensure it exists

echo "Schema update completed.\n";
$conn->close();
?>
