<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_config.php';

echo "Checking columns for 'featured_properties'...\n";
$result = $conn->query("SHOW COLUMNS FROM featured_properties");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
$conn->close();
?>
