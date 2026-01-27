<?php
require_once 'db_config.php';

echo "--- CURRENT DATA ---\n";
$res = $conn->query("SELECT * FROM pricing_plans WHERE bhk_type = '2bhk'");
print_r($res->fetch_assoc());

echo "\n--- SAVING NEW DATA ---\n";
$new_price = "UPDATETEST_" . time();
$sql = "UPDATE pricing_plans SET starting_price = '$new_price' WHERE bhk_type = '2bhk'";
if ($conn->query($sql)) {
    echo "Update successful. New price: $new_price\n";
} else {
    echo "Update failed: " . $conn->error . "\n";
}

echo "\n--- FETCHING DATA VIA DB QUERY ---\n";
$res = $conn->query("SELECT * FROM pricing_plans WHERE bhk_type = '2bhk'");
$data = $res->fetch_assoc();
print_r($data);

echo "\n--- FETCHING DATA VIA api.php LOGIC ---\n";
$_GET['action'] = 'get_project_data';
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
include 'api.php';
$json = ob_get_clean();
$api_data = json_decode($json, true);
echo "API starting_price for 2bhk: " . $api_data['pricing_plans']['2bhk']['starting_price'] . "\n";

if ($data['starting_price'] === $api_data['pricing_plans']['2bhk']['starting_price']) {
    echo "\nRESULT: MATCH! Both DB and API see the same data.\n";
} else {
    echo "\nRESULT: MISMATCH! DB sees " . $data['starting_price'] . " but API sees " . $api_data['pricing_plans']['2bhk']['starting_price'] . "\n";
}

$conn->close();
?>
