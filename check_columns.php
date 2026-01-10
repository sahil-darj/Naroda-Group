<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'naroda_group';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully\n";

$tables = ['naroda_irish_featured'];

foreach ($tables as $table) {
    echo "\nColumns for table: $table\n";
    $result = $conn->query("SHOW COLUMNS FROM $table");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

$conn->close();
?>
