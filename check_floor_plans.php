<?php
require 'db_config.php';

$res = $conn->query('SELECT id, bhk_type, image FROM apartment_plans');
echo "Floor Plans in Database:\n";
echo "========================\n";
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    echo "BHK Type: " . $row['bhk_type'] . "\n";
    echo "Image: " . $row['image'] . "\n";
    echo "------------------------\n";
}
$conn->close();
?>
