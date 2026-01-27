<?php
require_once 'db_config.php';

$output = "";

// Check table structure
$res = $conn->query("DESC pricing_plans");
$output .= "Table structure for pricing_plans:\n";
while ($row = $res->fetch_assoc()) {
    $output .= print_r($row, true);
}

// Check indexes
$res = $conn->query("SHOW INDEX FROM pricing_plans");
$output .= "\nIndexes for pricing_plans:\n";
while ($row = $res->fetch_assoc()) {
    $output .= print_r($row, true);
}

// Check counts
$res = $conn->query("SELECT bhk_type, count(*) as count FROM pricing_plans GROUP BY bhk_type");
$output .= "\nBHK coverage details:\n";
while ($row = $res->fetch_assoc()) {
    $output .= print_r($row, true);
}

// Check sample data
$res = $conn->query("SELECT * FROM pricing_plans");
$output .= "\nAll pricing plans:\n";
while ($row = $res->fetch_assoc()) {
    $output .= print_r($row, true);
}

file_put_contents('db_check_output.txt', $output);
echo "Results written to db_check_output.txt\n";

$conn->close();
?>
