<?php
ob_start();
$_GET['action'] = 'get_project_data';
$_SERVER['REQUEST_METHOD'] = 'GET';
include 'api.php';
$output = ob_get_clean();
file_put_contents('test_api_output.txt', $output);
echo "Results written to test_api_output.txt\n";
?>
