<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function check_db($host, $user, $pass, $dbname, $tables) {
    echo "--- Checking Database: $dbname ---\n";
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        echo "Connection failed: " . $conn->connect_error . "\n\n";
        return;
    }

    foreach ($tables as $table) {
        $result = $conn->query("SHOW COLUMNS FROM $table");
        if ($result) {
            echo "Table: $table\n";
            while ($row = $result->fetch_assoc()) {
                echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
        } else {
            echo "Error checking table $table: " . $conn->error . "\n";
        }
        echo "\n";
    }
    $conn->close();
}

$host = 'localhost';
$user = 'root';
$pass = '';

$lavish_tables = ['featured_properties', 'apartment_plans', 'gallery'];
$iries_tables = ['featured_properties', 'apartment_plans', 'gallery'];

check_db($host, $user, $pass, 'lavish_db', $lavish_tables);
check_db($host, $user, $pass, 'iries1_db', $iries_tables);
?>
