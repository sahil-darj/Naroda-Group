<?php
require_once 'db_config.php';

// SQL to upgrade image column in apartment_plans
$sql1 = "ALTER TABLE apartment_plans MODIFY COLUMN image LONGTEXT";
if ($conn->query($sql1) === TRUE) {
    echo "Apartment plans image column upgraded successfully\n";
} else {
    echo "Error upgrading apartment_plans: " . $conn->error . "\n";
}

// SQL to upgrade url column in gallery
$sql2 = "ALTER TABLE gallery MODIFY COLUMN url LONGTEXT";
if ($conn->query($sql2) === TRUE) {
    echo "Gallery url column upgraded successfully\n";
} else {
    echo "Error upgrading gallery: " . $conn->error . "\n";
}

$conn->close();
?>
