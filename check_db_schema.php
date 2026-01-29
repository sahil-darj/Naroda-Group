<?php
require 'db_config2.php';
$output = "";
$tables = ['apartment_plans', 'gallery', 'featured_properties'];
foreach ($tables as $table) {
    $output .= "\nTable: $table\n";
    $res = $conn->query("DESCRIBE $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $output .= sprintf("%-15s | %-15s\n", $row['Field'], $row['Type']);
        }
    } else {
        $output .= "Error describing $table: " . $conn->error . "\n";
    }
}
file_put_contents('schema_output.txt', $output);
echo "Schema written to schema_output.txt\n";
