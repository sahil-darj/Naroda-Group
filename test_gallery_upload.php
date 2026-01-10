<?php
/**
 * Gallery Upload Test Script
 * Tests database connection and saveGallery functionality
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== Gallery Upload Diagnostic Test ===\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    require_once 'models/NarodaLandmark.php';
    $landmark = new NarodaLandmark();
    echo "   ✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Check if gallery table exists
echo "2. Checking if landmark_gallery table exists...\n";
try {
    $gallery = $landmark->getAllGallery();
    echo "   ✓ Table exists. Current count: " . count($gallery) . " images\n\n";
} catch (Exception $e) {
    echo "   ✗ Table check failed: " . $e->getMessage() . "\n\n";
}

// Test 3: Test saveGallery function
echo "3. Testing saveGallery function with dummy data...\n";
try {
    $testData = [
        'image_url' => 'uploads/gallery/test_' . time() . '.jpg',
        'title_en' => 'Test Image ' . date('Y-m-d H:i:s'),
        'title_hi' => '',
        'title_gu' => ''
    ];
    
    $result = $landmark->saveGallery($testData);
    
    if ($result['success']) {
        echo "   ✓ saveGallery succeeded!\n";
        echo "   - Inserted ID: " . $result['id'] . "\n";
        echo "   - Message: " . $result['message'] . "\n\n";
        
        // Clean up test data
        echo "4. Cleaning up test data...\n";
        $deleteResult = $landmark->deleteGallery($result['id']);
        if ($deleteResult['success']) {
            echo "   ✓ Test data cleaned up\n\n";
        }
    } else {
        echo "   ✗ saveGallery failed!\n";
        echo "   - Error: " . ($result['error'] ?? 'Unknown error') . "\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Test failed with exception: " . $e->getMessage() . "\n\n";
}

// Test 4: Check uploads directory permissions
echo "5. Checking uploads directory...\n";
$uploadDir = 'uploads/gallery';
if (is_dir($uploadDir)) {
    echo "   ✓ Directory exists: $uploadDir\n";
    if (is_writable($uploadDir)) {
        echo "   ✓ Directory is writable\n\n";
    } else {
        echo "   ✗ Directory is NOT writable\n";
        echo "   - Please set permissions to 777 or 755\n\n";
    }
} else {
    echo "   ✗ Directory does not exist: $uploadDir\n";
    echo "   - Creating directory...\n";
    if (mkdir($uploadDir, 0777, true)) {
        echo "   ✓ Directory created\n\n";
    } else {
        echo "   ✗ Failed to create directory\n\n";
    }
}

echo "=== Test Complete ===\n";
?>
