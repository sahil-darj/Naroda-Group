<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'naroda_group';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "Connected successfully\n";

$table = 'naroda_irish_featured';
$result = $conn->query("SHOW COLUMNS FROM $table");
$output = "Columns for table: $table\n";
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $output .= $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    $output .= "Error: " . $conn->error . "\n";
}

file_put_contents('db_columns.txt', $output);
echo "Output written to db_columns.txt\n";
$conn->close();
?>
