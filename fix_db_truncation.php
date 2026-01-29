<?php
require 'db_config2.php';

$queries = [
    "ALTER TABLE apartment_plans MODIFY image LONGTEXT",
    "ALTER TABLE gallery MODIFY url LONGTEXT"
];

foreach ($queries as $query) {
    if ($conn->query($query)) {
        echo "Success: $query\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}
