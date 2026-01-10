<?php
/**
 * Naroda Landmark API
 * Handles all CRUD operations for:
 * - Floor Plans
 * - Gallery
 * - Pricing (Office & Retail)
 * - Featured Properties
 * - Inquiries
 */

// Start output buffering
ob_start();

// Disable error display
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

try {
    require_once '../models/NarodaLandmark.php';
    
    $landmark = new NarodaLandmark();
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    if (empty($action)) {
        throw new Exception('No action specified');
    }
    
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $landmark);
            break;
            
        case 'POST':
            handlePostRequest($action, $landmark);
            break;
            
        case 'DELETE':
            handleDeleteRequest($action, $landmark);
            break;
            
        default:
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            http_response_code(405);
            exit();
    }
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit();
}

// ========================================
// GET REQUEST HANDLER
// ========================================
function handleGetRequest($action, $landmark) {
    ob_end_clean();
    
    switch ($action) {
        // Floor Plans
        case 'get_floor_plans':
            $floorPlans = $landmark->getAllFloorPlans();
            echo json_encode(['success' => true, 'floor_plans' => $floorPlans]);
            break;
            
        case 'get_floor_plan':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                $floorPlan = $landmark->getFloorPlanById($id);
                if ($floorPlan) {
                    echo json_encode(['success' => true, 'floor_plan' => $floorPlan]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Floor plan not found']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'ID is required']);
            }
            break;
            
        // Gallery
        case 'get_gallery':
            $gallery = $landmark->getAllGallery();
            echo json_encode(['success' => true, 'gallery' => $gallery]);
            break;
            
        case 'get_gallery_image':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                $image = $landmark->getGalleryById($id);
                if ($image) {
                    echo json_encode(['success' => true, 'image' => $image]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gallery image not found']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'ID is required']);
            }
            break;
            
        // Pricing
        case 'get_pricing':
            $category = $_GET['category'] ?? null;
            $pricing = $landmark->getPricing($category);
            echo json_encode(['success' => true, 'pricing' => $pricing]);
            break;
            
        case 'get_pricing_by_id':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                $pricing = $landmark->getPricingById($id);
                if ($pricing) {
                    echo json_encode(['success' => true, 'pricing' => $pricing]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Pricing not found']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'ID is required']);
            }
            break;
            
        // Properties
        case 'get_properties':
            $category = $_GET['category'] ?? null;
            $properties = $landmark->getProperties($category);
            echo json_encode(['success' => true, 'properties' => $properties]);
            break;
            
        case 'get_property':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                $property = $landmark->getPropertyById($id);
                if ($property) {
                    echo json_encode(['success' => true, 'property' => $property]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Property not found']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'ID is required']);
            }
            break;
            
        // Inquiries
        case 'get_inquiries':
            $type = $_GET['type'] ?? null;
            $inquiries = $landmark->getInquiries($type);
            echo json_encode(['success' => true, 'inquiries' => $inquiries]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
    }
    exit();
}

// ========================================
// FILE UPLOAD HANDLER
// ========================================
function handleFileUpload($file, $subfolder = 'misc') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $uploadDir = '../uploads/' . $subfolder . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    // Convert to URL path
    $urlPath = 'uploads/' . $subfolder . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $urlPath;
    }
    return null;
}

// ========================================
// POST REQUEST HANDLER
// ========================================
function handlePostRequest($action, $landmark) {
    ob_end_clean();
    
    $data = [];
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

    if (strpos($contentType, "application/json") !== false) {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
    } else {
        $data = $_POST;
    }
    
    switch ($action) {
        case 'save_floor_plan':
            if (isset($_FILES['image'])) {
                $url = handleFileUpload($_FILES['image'], 'floorplans');
                if ($url) $data['image_url'] = $url;
            }
            $result = $landmark->saveFloorPlan($data);
            echo json_encode($result);
            break;
            
        case 'save_gallery':
            error_log("=== SAVE_GALLERY START ===");
            error_log("POST data: " . print_r($_POST, true));
            error_log("FILES data: " . print_r($_FILES, true));
            
            $count = 0;
            $errors = [];
            if (isset($_FILES['images'])) {
                error_log("Gallery upload: Processing " . count($_FILES['images']['tmp_name']) . " images");
                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    $file = [
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $tmpName,
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key]
                    ];
                    
                    // Check for upload errors
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $errors[] = "Upload error for {$file['name']}: " . $file['error'];
                        error_log("Gallery upload error: {$file['name']} - Error code: {$file['error']}");
                        continue;
                    }
                    
                    $url = handleFileUpload($file, 'gallery');
                    if ($url) {
                        error_log("Gallery: File uploaded to: $url");
                        $result = $landmark->saveGallery([
                            'image_url' => $url, 
                            'title_en' => $file['name'],
                            'title_hi' => '',
                            'title_gu' => ''
                        ]);
                        
                        if ($result['success']) {
                            $count++;
                            error_log("Gallery: Successfully saved to DB with ID: " . $result['id']);
                        } else {
                            $errors[] = "DB save failed for {$file['name']}: " . ($result['error'] ?? 'Unknown error');
                            error_log("Gallery DB save failed: " . ($result['error'] ?? 'Unknown'));
                        }
                    } else {
                        $errors[] = "File upload failed for {$file['name']}";
                        error_log("Gallery: handleFileUpload returned null for {$file['name']}");
                    }
                }
            } else {
                error_log("Gallery: No images in _FILES array");
                $errors[] = "No images received in request";
            }
            
            error_log("=== SAVE_GALLERY END: $count images saved ===");
            
            if ($count > 0) {
                echo json_encode(['success' => true, 'message' => "$count images saved", 'errors' => $errors]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No images were saved', 'details' => $errors]);
            }
            break;
            
        case 'save_pricing':
            // Features might be sent as JSON string if from FormData, but pricing usually sent as JSON
            if (is_string($data['features'] ?? null)) {
                $data['features'] = json_decode($data['features'], true);
            }
            $result = $landmark->savePricing($data);
            echo json_encode($result);
            break;
            
        case 'save_property':
            // 1. Handle Images
            $imageUrls = [];
            
            // Handle existing images
            if (!empty($data['existing_images'])) {
                // If it's a string (JSON), decode it; otherwise use as array
                 $existing = $data['existing_images'];
                 if (is_string($existing)) {
                     $decoded = json_decode($existing, true);
                     if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $imageUrls = $decoded;
                     } else {
                         // It might be a single URL string directly or comma separated? 
                         // Assuming array from previous logic, but safeguard:
                         $imageUrls = (array)$existing;
                     }
                 } else if (is_array($existing)) {
                     $imageUrls = $existing;
                 }
            }

            // Handle new image uploads
            if (isset($_FILES['images'])) {
                $files = $_FILES['images'];
                // Normalize file array structure if needed (PHP $_FILES with [] name is weird)
                // If multiple files: name is array.
                if (is_array($files['name'])) {
                    $count = count($files['name']);
                    for ($i = 0; $i < $count; $i++) {
                        if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            $fileData = [
                                'name' => $files['name'][$i],
                                'type' => $files['type'][$i],
                                'tmp_name' => $files['tmp_name'][$i],
                                'error' => $files['error'][$i],
                                'size' => $files['size'][$i]
                            ];
                            $url = handleFileUpload($fileData, 'properties'); // saving to uploads/properties
                            if ($url) {
                                $imageUrls[] = $url;
                            }
                        }
                    }
                } else {
                     // Single file just in case
                     if ($files['error'] === UPLOAD_ERR_OK) {
                         $url = handleFileUpload($files, 'properties');
                         if ($url) $imageUrls[] = $url;
                     }
                }
            }
            $data['images'] = $imageUrls;

            // 2. Handle Brochure
            $brochure = null;
            // Existing brochure
            if (!empty($data['existing_brochure'])) {
                 $existingBrochure = $data['existing_brochure'];
                 // Decode if JSON string
                 if (is_string($existingBrochure)) {
                     $decoded = json_decode($existingBrochure, true);
                     if (json_last_error() === JSON_ERROR_NONE) {
                         $brochure = $decoded;
                     } 
                 } else {
                     $brochure = $existingBrochure;
                 }
            }
            
            // New brochure upload
            if (isset($_FILES['brochure']) && $_FILES['brochure']['error'] === UPLOAD_ERR_OK) {
                $url = handleFileUpload($_FILES['brochure'], 'brochures');
                if ($url) {
                    $brochure = [
                        'name' => $_FILES['brochure']['name'], 
                        'url' => $url, 
                        'size' => $_FILES['brochure']['size']
                    ];
                }
            }
            $data['brochure'] = $brochure;


            // 3. Handle Property Documents
            $propertyDocs = [];
            if (!empty($data['existing_property_documents'])) {
                 $existingDocs = $data['existing_property_documents'];
                 // It might be an array of JSON strings or a JSON string of array
                 // Front end sends existing_property_documents[] which are JSON strings of objects
                 if (is_array($existingDocs)) {
                     foreach($existingDocs as $doc) {
                         if (is_string($doc)) {
                             $decoded = json_decode($doc, true);
                             if (json_last_error() === JSON_ERROR_NONE) $propertyDocs[] = $decoded;
                         } elseif (is_array($doc)) {
                             $propertyDocs[] = $doc;
                         }
                     }
                 }
            }

            if (isset($_FILES['property_documents'])) {
                $files = $_FILES['property_documents'];
                if (is_array($files['name'])) {
                    $count = count($files['name']);
                    for ($i = 0; $i < $count; $i++) {
                         if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            $fileData = [
                                'name' => $files['name'][$i],
                                'type' => $files['type'][$i],
                                'tmp_name' => $files['tmp_name'][$i],
                                'error' => $files['error'][$i],
                                'size' => $files['size'][$i]
                            ];
                            $url = handleFileUpload($fileData, 'documents');
                            if ($url) {
                                $propertyDocs[] = [
                                    'name' => $files['name'][$i],
                                    'url' => $url,
                                    'size' => $files['size'][$i]
                                ];
                            }
                        }
                    }
                }
            }
            $data['property_documents'] = $propertyDocs;


            // 4. Handle Approvals Documents
             $approvalsDocs = [];
            if (!empty($data['existing_approvals_documents'])) {
                 $existingDocs = $data['existing_approvals_documents'];
                 if (is_array($existingDocs)) {
                     foreach($existingDocs as $doc) {
                         if (is_string($doc)) {
                             $decoded = json_decode($doc, true);
                             if (json_last_error() === JSON_ERROR_NONE) $approvalsDocs[] = $decoded;
                         } elseif (is_array($doc)) {
                             $approvalsDocs[] = $doc;
                         }
                     }
                 }
            }

            if (isset($_FILES['approvals_documents'])) {
                $files = $_FILES['approvals_documents'];
                if (is_array($files['name'])) {
                    $count = count($files['name']);
                    for ($i = 0; $i < $count; $i++) {
                         if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            $fileData = [
                                'name' => $files['name'][$i],
                                'type' => $files['type'][$i],
                                'tmp_name' => $files['tmp_name'][$i],
                                'error' => $files['error'][$i],
                                'size' => $files['size'][$i]
                            ];
                            $url = handleFileUpload($fileData, 'approvals');
                            if ($url) {
                                $approvalsDocs[] = [
                                    'name' => $files['name'][$i],
                                    'url' => $url,
                                    'size' => $files['size'][$i]
                                ];
                            }
                        }
                    }
                }
            }
            $data['approvals_documents'] = $approvalsDocs;

            
            $result = $landmark->saveProperty($data);
            echo json_encode($result);
            break;
            
        case 'save_inquiry':
            $result = $landmark->saveInquiry($data);
            echo json_encode($result);
            break;
            
        case 'update_inquiry_status':
            $id = $data['id'] ?? 0;
            $status = $data['status'] ?? '';
            if ($id && $status) {
                $result = $landmark->updateInquiryStatus($id, $status);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID and status are required']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
    }
    exit();
}

// ========================================
// DELETE REQUEST HANDLER
// ========================================
function handleDeleteRequest($action, $landmark) {
    ob_end_clean();
    
    $id = $_GET['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID is required']);
        exit();
    }
    
    switch ($action) {
        case 'delete_floor_plan':
            $result = $landmark->deleteFloorPlan($id);
            echo json_encode($result);
            break;
            
        case 'delete_gallery':
            $result = $landmark->deleteGallery($id);
            echo json_encode($result);
            break;
            
        case 'delete_pricing':
            $result = $landmark->deletePricing($id);
            echo json_encode($result);
            break;
            
        case 'delete_property':
            $result = $landmark->deleteProperty($id);
            echo json_encode($result);
            break;
            
        case 'delete_inquiry':
            $result = $landmark->deleteInquiry($id);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
    }
    exit();
}
?>
