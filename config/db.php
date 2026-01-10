<?php
// db.php - Adapter to central database connector
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Use central config/database.php which provides getDB()
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
if (!$pdo) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
?>