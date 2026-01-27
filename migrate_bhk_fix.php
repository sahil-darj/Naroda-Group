<?php
require_once 'db_config.php';

// Migrate apartment_plans
$sql1 = "UPDATE apartment_plans SET bhk_type = '2bhk' WHERE bhk_type = '1bhk'";
if ($conn->query($sql1)) {
    echo "Apartment plans migrated: " . $conn->affected_rows . " rows updated.\n";
} else {
    echo "Error migrating apartment plans: " . $conn->error . "\n";
}

// Migrate pricing_plans
// Note: pricing_plans has a UNIQUE constraint on bhk_type.
// If both 1bhk and 2bhk exist, we might need to decide which one to keep.
// For now, let's just try to update. If it fails due to duplicate, we'll know.
$sql2 = "UPDATE pricing_plans SET bhk_type = '2bhk' WHERE bhk_type = '1bhk'";
if ($conn->query($sql2)) {
    echo "Pricing plans migrated: " . $conn->affected_rows . " rows updated.\n";
} else {
    if ($conn->errno == 1062) { // Duplicate entry
        echo "Pricing plans migration skipped: 2bhk record already exists.\n";
        // Optionally delete the 1bhk one
        $conn->query("DELETE FROM pricing_plans WHERE bhk_type = '1bhk'");
        echo "Deleted redundant 1bhk pricing plan.\n";
    } else {
        echo "Error migrating pricing plans: " . $conn->error . "\n";
    }
}

$conn->close();
?>
