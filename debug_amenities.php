<?php
require_once 'db_config.php';

$res = $conn->query("SELECT id, property_id, amenities FROM featured_properties LIMIT 5");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    echo "Property ID: " . $row['property_id'] . "\n";
    echo "Amenities Raw: " . $row['amenities'] . "\n";
    echo "Amenities Decoded: ";
    print_r(json_decode($row['amenities'], true));
    echo "\n-----------------------------------\n";
}
?>
