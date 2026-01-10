<?php
require_once __DIR__ . '/../models/NarodaIrish.php';
header('Content-Type: application/json');

$model = new NarodaIrish();
$action = $_REQUEST['action'] ?? '';

// Helper to handle file uploads
function handleFileUpload($file, $subdir = 'projects') {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;
    
    $uploadDir = __DIR__ . '/../uploads/' . $subdir . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . $subdir . '/' . $filename;
    }
    return null;
}

// Get request data
$data = [];
$contentType = $_SERVER["CONTENT_TYPE"] ?? '';

if (strpos($contentType, "application/json") !== false) {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $data = $_POST;
}

switch ($action) {
    case 'get_apartment_plans':
        $type = $_GET['type'] ?? null;
        echo json_encode(['success' => true, 'data' => $model->getApartmentPlans($type)]);
        break;

    case 'save_apartment_plan':
        // Handle image upload from FormData
        if (isset($_FILES['image'])) {
            $url = handleFileUpload($_FILES['image'], 'apartments');
            if ($url) $data['image_url'] = $url;
        }
        
        if ($model->saveApartmentPlan($data)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save apartment plan']);
        }
        break;

    case 'delete_apartment_plan':
        $id = $_GET['id'] ?? null;
        if ($model->deleteApartmentPlan($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete plan']);
        }
        break;

    case 'get_gallery':
        echo json_encode(['success' => true, 'data' => $model->getGallery()]);
        break;

    case 'save_gallery_image':
        if (isset($_FILES['image'])) {
            $url = handleFileUpload($_FILES['image'], 'gallery');
            if ($url) {
                $data['image_url'] = $url;
                $data['title'] = $data['title'] ?? $_FILES['image']['name'];
                $data['description'] = $data['description'] ?? '';
                $data['is_featured'] = $data['is_featured'] ?? 0;
                
                if ($model->saveGalleryImage($data)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to save to database']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No image provided']);
        }
        break;

    case 'delete_gallery_image':
        $id = $_GET['id'] ?? null;
        if ($model->deleteGalleryImage($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete image']);
        }
        break;

    case 'get_pricing_plans':
        $type = $_GET['type'] ?? null;
        echo json_encode(['success' => true, 'data' => $model->getPricingPlans($type)]);
        break;

    case 'save_pricing_plan':
        if ($model->savePricingPlan($data)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save pricing plan']);
        }
        break;

    case 'get_featured_properties':
        $category = $_GET['category'] ?? null;
        $properties = $model->getFeaturedProperties($category);
        
        // Decode JSON fields for frontend
        foreach ($properties as &$p) {
            $jsonFields = ['overview_features_en', 'overview_features_hi', 'overview_features_gu', 'amenities_sections_en', 'amenities_sections_hi', 'amenities_sections_gu', 'floor_plans_dimensions', 'location_details', 'documents', 'images', 'brochure'];
            foreach ($jsonFields as $field) {
                if (isset($p[$field])) $p[$field] = json_decode($p[$field], true);
            }
        }
        echo json_encode(['success' => true, 'data' => $properties]);
        break;

    case 'save_featured_property':
        // Decode JSON blobs from FormData
        $jsonFields = ['overview', 'amenities', 'location_details', 'documents', 'floor_plans', 'existing_images'];
        foreach ($jsonFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = json_decode($data[$field], true);
            }
        }

        // Handle File Uploads
        $images = $data['existing_images'] ?? [];
        
        // Primary Image 1
        if (isset($_FILES['image1'])) {
            $url = handleFileUpload($_FILES['image1'], 'properties');
            if ($url) array_unshift($images, $url);
        }

        // Additional Property Images
        if (isset($_FILES['property_images'])) {
            foreach ($_FILES['property_images']['tmp_name'] as $key => $tmpName) {
                $file = [
                    'name' => $_FILES['property_images']['name'][$key],
                    'type' => $_FILES['property_images']['type'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $_FILES['property_images']['error'][$key],
                    'size' => $_FILES['property_images']['size'][$key]
                ];
                $url = handleFileUpload($file, 'properties');
                if ($url) $images[] = $url;
            }
        }
        $data['images'] = $images;

        // Documents
        $docs = $data['documents'] ?? ['propertyDocuments' => [], 'approvalsDocuments' => [], 'brochure' => null];
        
        if (isset($_FILES['property_documents'])) {
            foreach ($_FILES['property_documents']['tmp_name'] as $key => $tmpName) {
                $file = [
                    'name' => $_FILES['property_documents']['name'][$key],
                    'type' => $_FILES['property_documents']['type'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $_FILES['property_documents']['error'][$key],
                    'size' => $_FILES['property_documents']['size'][$key]
                ];
                $url = handleFileUpload($file, 'documents');
                if ($url) {
                    $docs['propertyDocuments'][] = ['name' => $file['name'], 'size' => $file['size'], 'url' => $url, 'type' => $file['type']];
                }
            }
        }

        if (isset($_FILES['approval_documents'])) {
            foreach ($_FILES['approval_documents']['tmp_name'] as $key => $tmpName) {
                $file = [
                    'name' => $_FILES['approval_documents']['name'][$key],
                    'type' => $_FILES['approval_documents']['type'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $_FILES['approval_documents']['error'][$key],
                    'size' => $_FILES['approval_documents']['size'][$key]
                ];
                $url = handleFileUpload($file, 'documents');
                if ($url) {
                    $docs['approvalsDocuments'][] = ['name' => $file['name'], 'size' => $file['size'], 'url' => $url, 'type' => $file['type']];
                }
            }
        }

        if (isset($_FILES['brochure_file'])) {
            $url = handleFileUpload($_FILES['brochure_file'], 'documents');
            if ($url) {
                $docs['brochure'] = ['name' => $_FILES['brochure_file']['name'], 'size' => $_FILES['brochure_file']['size'], 'url' => $url, 'type' => $_FILES['brochure_file']['type']];
            }
        }
        $data['documents'] = $docs;
        $data['brochure'] = $docs['brochure'];

        // Floor Plans
        $fp = $data['floor_plans'] ?? [];
        if (isset($_FILES['floor_plan_image'])) {
            $url = handleFileUpload($_FILES['floor_plan_image'], 'floorplans');
            if ($url) $fp['image'] = $url;
        }
        if (isset($_FILES['floor_plan_doc'])) {
            $url = handleFileUpload($_FILES['floor_plan_doc'], 'floorplans');
            if ($url) $fp['document'] = $url;
        }
        $data['floor_plans_dimensions'] = $fp;

        // Map overview fields
        $ov = $data['overview'] ?? [];
        $data['overview_description_en'] = $ov['description'] ?? '';
        $data['overview_description_hi'] = $ov['description_hi'] ?? '';
        $data['overview_description_gu'] = $ov['description_gu'] ?? '';
        $data['overview_features_en'] = $ov['features'] ?? [];
        $data['overview_features_hi'] = $ov['features_hi'] ?? [];
        $data['overview_features_gu'] = $ov['features_gu'] ?? [];
        $data['price_unit'] = $ov['priceUnit'] ?? '';

        // Map location fields
        $loc = $data['location_details'] ?? [];
        $data['location_details'] = $loc;
        
        // Map amenities
        $am = $data['amenities'] ?? [];
        $data['amenities_sections_en'] = $am['sections'] ?? [];
        $data['amenities_sections_hi'] = $am['sections_hi'] ?? [];
        $data['amenities_sections_gu'] = $am['sections_gu'] ?? [];

        if ($model->saveFeaturedProperty($data)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save property']);
        }
        break;

    case 'delete_featured_property':
        $id = $_GET['id'] ?? null;
        if ($model->deleteFeaturedProperty($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete property']);
        }
        break;

    case 'save_inquiry':
        if ($model->saveInquiry($data)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save inquiry']);
        }
        break;

    case 'get_inquiries':
        $category = $_GET['category'] ?? null;
        echo json_encode(['success' => true, 'data' => $model->getInquiries($category)]);
        break;

    case 'get_all':
        $apartments = $model->getApartmentPlans();
        $pricing = $model->getPricingPlans();
        $featured = $model->getFeaturedProperties();
        
        // Decode JSON fields for pricing and featured
        foreach ($pricing as &$p) {
            $p['features_en'] = json_decode($p['features_en'], true);
            $p['features_hi'] = json_decode($p['features_hi'], true);
            $p['features_gu'] = json_decode($p['features_gu'], true);
        }
        
        foreach ($featured as &$p) {
            $jsonFields = ['overview_features_en', 'overview_features_hi', 'overview_features_gu', 'amenities_sections_en', 'amenities_sections_hi', 'amenities_sections_gu', 'floor_plans_dimensions', 'location_details', 'documents', 'images', 'brochure'];
            foreach ($jsonFields as $field) {
                if (isset($p[$field])) $p[$field] = json_decode($p[$field], true);
            }
        }

        $data = [
            'success' => true,
            'data' => [
                'apartments' => $apartments,
                'gallery' => $model->getGallery(),
                'pricing' => $pricing,
                'featured' => $featured
            ]
        ];
        echo json_encode($data);
        break;

    case 'update_inquiry_status':
        if ($model->updateInquiryStatus($data['id'], $data['status'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update status']);
        }
        break;

    case 'delete_inquiry':
        $id = $_GET['id'] ?? null;
        if ($model->deleteInquiry($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete inquiry']);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
