<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "iries1_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Set timeouts to avoid "Server has gone away"
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 300);
$conn->query("SET SESSION wait_timeout=300");
$conn->query("SET SESSION interactive_timeout=300");
// Dynamic Max Allowed Packet Fix
$res = $conn->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
$row = $res->fetch_assoc();
$current_packet = intval($row['Value']);
$target_packet = 67108864; // 64MB

if ($current_packet < $target_packet) {
    // Try to set GLOBAL variable (requires privileges)
    $conn->query("SET GLOBAL max_allowed_packet=$target_packet");
    // Close and Reconnect to apply GLOBAL changes to this session
    $conn->close();
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die(json_encode(["status" => "error", "message" => "Re-connection failed: " . $conn->connect_error]));
    }
}
$conn->set_charset("utf8mb4");

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

