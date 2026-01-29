<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP post_max_size: " . ini_get('post_max_size') . "\n";
echo "PHP upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "PHP memory_limit: " . ini_get('memory_limit') . "\n\n";

function check_db($dbname) {
    echo "--- Database: $dbname ---\n";
    $conn = new mysqli('localhost', 'root', '', $dbname);
    if ($conn->connect_error) { echo "Failed: " . $conn->connect_error . "\n"; return; }

    $tables = ['featured_properties', 'apartment_plans', 'gallery'];
    foreach ($tables as $t) {
        $res = $conn->query("SHOW COLUMNS FROM $t");
        if ($res) {
            echo "Table: $t\n";
            while ($row = $res->fetch_assoc()) {
                if (in_array($row['Field'], ['images', 'url', 'image', 'brochure', 'floor_plans_dimensions'])) {
                    echo "  " . $row['Field'] . " -> " . $row['Type'] . "\n";
                }
            }
        }
    }
    $conn->close();
    echo "\n";
}

check_db('lavish_db');
check_db('iries1_db');
?>
