<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lavish_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Select the database
    $conn->select_db($dbname);
} else {
    die("Error creating database: " . $conn->error);
}

// Function to create tables from SQL file
function createTables($conn, $sqlFile) {
    if (!file_exists($sqlFile)) {
        return "SQL file not found.";
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove the CREATE DATABASE and USE lines as we handled them above
    $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*;/i', '', $sql);
    $sql = preg_replace('/USE .*;/i', '', $sql);
    
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        return "Tables created successfully.";
    } else {
        return "Error creating tables: " . $conn->error;
    }
}

// Run the script to create tables
$result = createTables($conn, 'setup_db2.sql');
echo $result;

$conn->close();
?>
