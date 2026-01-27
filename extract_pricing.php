<?php
$json = file_get_contents('test_api_output.txt');
$data = json_decode($json, true);
$output = "";
if (isset($data['pricing_plans'])) {
    $output .= "PRICING PLANS FROM API:\n";
    $output .= print_r($data['pricing_plans'], true);
} else {
    $output .= "Pricing plans not found in JSON output.\n";
}
file_put_contents('pricing_api_debug.txt', $output);
echo "Results written to pricing_api_debug.txt\n";
?>
