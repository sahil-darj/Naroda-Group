<?php
// migrate_database.php
header('Content-Type: text/plain');

$host = 'localhost';
$username = 'root';
$password = '';

// Connect to MySQL server (not specific DB yet)
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to MySQL server.\n";

// Helper function to check if DB exists
function dbExists($conn, $dbname) {
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    return $result->num_rows > 0;
}

// 1. Check if naroda_group exists
if (!dbExists($conn, 'naroda_group')) {
    echo "Error: Target database 'naroda_group' does not exist.\n";
    // Optional: Create it? User said it exists.
    // $conn->query("CREATE DATABASE naroda_group");
    exit;
}

// 2. Check if naroda_db exists
if (!dbExists($conn, 'naroda_db')) {
    echo "Source database 'naroda_db' does not exist. Migration might have already run.\n";
} else {
    // 3. Move 'news' table
    // Check if news exists in naroda_db
    $checkTable = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'naroda_db' AND TABLE_NAME = 'news'");
    
    if ($checkTable->num_rows > 0) {
        // Check if news already exists in naroda_group (collision)
        $checkTarget = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'naroda_group' AND TABLE_NAME = 'news'");
        
        if ($checkTarget->num_rows > 0) {
            echo "Warning: Table 'news' already exists in 'naroda_group'. Skipping move to avoid overwrite.\n";
        } else {
            echo "Moving 'news' table from 'naroda_db' to 'naroda_group'...\n";
            $sql = "RENAME TABLE naroda_db.news TO naroda_group.news";
            if ($conn->query($sql) === TRUE) {
                echo "Success: Table moved.\n";
            } else {
                echo "Error moving table: " . $conn->error . "\n";
                exit; // Stop here if move fails
            }
        }
    } else {
        echo "Table 'news' not found in 'naroda_db'.\n";
    }

    // 4. Drop naroda_db
    echo "Dropping database 'naroda_db'...\n";
    if ($conn->query("DROP DATABASE naroda_db") === TRUE) {
        echo "Success: 'naroda_db' dropped.\n";
    } else {
        echo "Error dropping 'naroda_db': " . $conn->error . "\n";
    }
}

// 5. Drop naroda (if requested)
if (dbExists($conn, 'naroda')) {
    echo "Dropping database 'naroda'...\n";
    if ($conn->query("DROP DATABASE naroda") === TRUE) {
        echo "Success: 'naroda' dropped.\n";
    } else {
        echo "Error dropping 'naroda': " . $conn->error . "\n";
    }
} else {
    echo "Database 'naroda' does not exist or already dropped.\n";
}

$conn->close();

echo "\nMigration script completed.\n";
?>
