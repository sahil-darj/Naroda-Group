<?php
ob_start();
$_GET['action'] = 'save_pricing_plan';
$_SERVER['REQUEST_METHOD'] = 'POST';

$input = [
    'type' => '2bhk',
    'startingPrice' => 'TEST_PRICE_99',
    'sqft' => 'TEST_SQFT_99',
    'bedrooms' => '9',
    'bathrooms' => '9',
    'parking' => '9',
    'available' => '9',
    'availabilityStatus' => 'sold-out',
    'features' => [
        'en' => ['Test Feature 1', 'Test Feature 2'],
        'hi' => ['टेस्ट'],
        'gu' => ['ટેસ્ટ']
    ]
];

// Set php://input mockup
$tempFile = tempnam(sys_get_temp_dir(), 'php_input');
file_put_contents($tempFile, json_encode($input));

function mock_file_get_contents($filename) {
    global $tempFile;
    if ($filename === 'php://input') {
        return file_get_contents($tempFile);
    }
    return file_get_contents($filename);
}

// We need to override file_get_contents in api.php or just use a helper
// Since we can't easily override it, let's just modify api.php slightly to support a debug flag or use a different way.
// Actually, I'll just create a modified api_test.php for this test.

$api_code = file_get_contents('api.php');
$api_code = str_replace("file_get_contents('php://input')", "file_get_contents('$tempFile')", $api_code);
file_put_contents('api_test_runner.php', $api_code);

include 'api_test_runner.php';
$output = ob_get_clean();
echo "API RESPONSE:\n$output\n";

// Check database after
require_once 'db_config.php';
$res = $conn->query("SELECT * FROM pricing_plans WHERE bhk_type = '2bhk'");
echo "\nDATABASE DATA AFTER SAVE:\n";
print_r($res->fetch_assoc());

unlink($tempFile);
unlink('api_test_runner.php');
?>
