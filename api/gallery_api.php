<?php
// ============================================
// GALLERY MANAGEMENT API - COMPLETE FIXED VERSION
// ============================================

// Turn off all error reporting for production, but log errors
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'gallery_errors.log');

// Set CORS headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'naroda_group';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    sendResponse(false, 'Database connection failed. Please check database setup.', null, 500);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Get action from request
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? trim($_GET['action']) : '';
} else {
    $action = isset($_GET['action']) ? trim($_GET['action']) : '';
    if (empty($action) && isset($_POST['action'])) {
        $action = trim($_POST['action']);
    }
}

// If no action specified, return help message
if (empty($action)) {
    sendResponse(false, 'No action specified. Available actions: get_gallery_data, save_image, delete_image, save_video, delete_video, save_highlight, delete_highlight, save_category, delete_category, get_statistics', null, 400);
}

// Helper function to get JSON input
function getJsonInput() {
    static $input = null;
    
    if ($input === null) {
        $rawInput = file_get_contents('php://input');
        if (empty($rawInput)) {
            $input = $_POST;
        } else {
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                parse_str($rawInput, $input);
            }
        }
    }
    
    return $input ?: [];
}

// Helper function to send JSON response
function sendResponse($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    $response = [
        'success' => (bool)$success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (!$success) {
        error_log("API Error [$statusCode]: $message");
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// NEW: Function to scan and get actual images from uploads folder
function getActualGalleryImages() {
    $uploadsPath = '../uploads/';
    $allImages = [];
    
    if (!is_dir($uploadsPath)) {
        return $allImages;
    }
    
    // Scan all items in uploads folder
    $items = scandir($uploadsPath);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $itemPath = $uploadsPath . $item;
        
        // Check if it's a directory (like gallery_1766776354)
        if (is_dir($itemPath) && strpos($item, 'gallery_') === 0) {
            // It's a gallery folder, scan it
            $files = scandir($itemPath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $filePath = $itemPath . '/' . $file;
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
                    $relativePath = 'uploads/' . $item . '/' . $file;
                    
                    $allImages[] = [
                        'path' => $relativePath,
                        'folder' => $item,
                        'filename' => $file,
                        'full_url' => getImageUrl($relativePath),
                        'uploaded_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                        'size' => round(filesize($filePath) / (1024 * 1024), 2) . ' MB'
                    ];
                }
            }
        } 
        // Check if it's a direct image file (like your gallery_1766776354_c9fb779dad4f10... files)
        else if (is_file($itemPath)) {
            $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
                $relativePath = 'uploads/' . $item;
                
                $allImages[] = [
                    'path' => $relativePath,
                    'folder' => 'uploads',
                    'filename' => $item,
                    'full_url' => getImageUrl($relativePath),
                    'uploaded_at' => date('Y-m-d H:i:s', filemtime($itemPath)),
                    'size' => round(filesize($itemPath) / (1024 * 1024), 2) . ' MB'
                ];
            }
        }
    }
    
    return $allImages;
}

// Helper function to get full image URL - FIXED
function getImageUrl($relativePath) {
    if (empty($relativePath)) {
        return '';
    }
    
    // If it's already a full URL, return as-is
    if (strpos($relativePath, 'http://') === 0 || strpos($relativePath, 'https://') === 0) {
        return $relativePath;
    }
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the base directory from the script path (e.g., /ng/api -> /ng)
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);  // e.g., /ng/api
    $baseDir = dirname($scriptPath);  // e.g., /ng
    
    // Handle root directory case
    if ($baseDir === '/' || $baseDir === '\\') {
        $baseDir = '';
    }
    
    // Remove leading slash from relativePath if present
    $relativePath = ltrim($relativePath, '/');
    
    return $protocol . $host . $baseDir . '/' . $relativePath;
}

// Helper function to format database rows - ENHANCED
function formatImageRow($row) {
    $imageUrl = $row['image_url'] ?? '';
    
    // If image_url is empty, try to find a matching image from uploads
    if (empty($imageUrl)) {
        $allImages = getActualGalleryImages();
        // Try to find image by ID or title
        foreach ($allImages as $actualImage) {
            if (strpos($actualImage['filename'], $row['title_en'] ?? '') !== false) {
                $imageUrl = $actualImage['path'];
                break;
            }
        }
    }
    
    return [
        'id' => (int)$row['id'],
        'title' => [
            'en' => $row['title_en'] ?? 'Untitled',
            'hi' => $row['title_hi'] ?? ($row['title_en'] ?? 'Untitled'),
            'gu' => $row['title_gu'] ?? ($row['title_en'] ?? 'Untitled')
        ],
        'description' => [
            'en' => $row['description_en'] ?? '',
            'hi' => $row['description_hi'] ?? '',
            'gu' => $row['description_gu'] ?? ''
        ],
        'category' => $row['category'] ?? 'uncategorized',
        'project' => $row['project'] ?? 'both',
        'tags' => !empty($row['tags']) ? json_decode($row['tags'], true) : [],
        'featured' => (bool)($row['featured'] ?? false),
        'image_url' => getImageUrl($imageUrl),
        'relative_url' => $imageUrl, // Keep relative URL for reference
        'size' => $row['size'] ?? '2.5 MB',
        'dimensions' => $row['dimensions'] ?? '1920x1080',
        'uploaded_date' => $row['uploaded_date'] ?? date('Y-m-d'),
        'created_at' => $row['created_at'] ?? ''
    ];
}

function formatVideoRow($row) {
    $videoUrl = $row['video_url'] ?? '';
    $thumbnailUrl = $row['thumbnail_url'] ?? '';
    
    return [
        'id' => (int)$row['id'],
        'title' => [
            'en' => $row['title_en'] ?? '',
            'hi' => $row['title_hi'] ?? '',
            'gu' => $row['title_gu'] ?? ''
        ],
        'description' => [
            'en' => $row['description_en'] ?? '',
            'hi' => $row['description_hi'] ?? '',
            'gu' => $row['description_gu'] ?? ''
        ],
        'category' => $row['category'] ?? '',
        'duration' => $row['duration'] ?? '0:00',
        'size' => $row['size'] ?? '0 MB',
        'views' => (int)($row['views'] ?? 0),
        'thumbnail_url' => getImageUrl($thumbnailUrl),
        'video_url' => getImageUrl($videoUrl),
        'source' => $row['source'] ?? 'upload',
        'featured' => (bool)($row['featured'] ?? false),
        'uploaded_date' => $row['uploaded_date'] ?? date('Y-m-d'),
        'created_at' => $row['created_at'] ?? ''
    ];
}

function formatHighlightRow($row) {
    $images = !empty($row['images']) ? json_decode($row['images'], true) : [];
    
    $formattedImages = [];
    foreach ($images as $imageUrl) {
        $formattedImages[] = getImageUrl($imageUrl);
    }
    
    return [
        'id' => (int)$row['id'],
        'title' => [
            'en' => $row['title_en'] ?? '',
            'hi' => $row['title_hi'] ?? '',
            'gu' => $row['title_gu'] ?? ''
        ],
        'description' => [
            'en' => $row['description_en'] ?? '',
            'hi' => $row['description_hi'] ?? '',
            'gu' => $row['description_gu'] ?? ''
        ],
        'location' => [
            'en' => $row['location_en'] ?? '',
            'hi' => $row['location_hi'] ?? '',
            'gu' => $row['location_gu'] ?? ''
        ],
        'project' => $row['project'] ?? '',
        'images' => $formattedImages,
        'display_order' => (int)($row['sort_order'] ?? 0),
        'featured' => (bool)($row['featured'] ?? false),
        'status' => $row['status'] ?? 'active',
        'updated_at' => $row['updated_at'] ?? '',
        'created_at' => $row['created_at'] ?? ''
    ];
}

function formatCategoryRow($row) {
    return [
        'id' => (int)$row['id'],
        'name' => $row['name'] ?? '',
        'display_name' => [
            'en' => $row['display_name_en'] ?? '',
            'hi' => $row['display_name_hi'] ?? '',
            'gu' => $row['display_name_gu'] ?? ''
        ],
        'color' => $row['color'] ?? '#4361ee',
        'count' => (int)($row['image_count'] ?? 0) + (int)($row['video_count'] ?? 0),
        'created_at' => $row['created_at'] ?? ''
    ];
}

// Main API handler
try {
    switch ($action) {
        // ------------------------------------------------------------------
        // FETCH ALL DATA - ENHANCED WITH ACTUAL IMAGES
        // ------------------------------------------------------------------
        case 'get_gallery_data':
            $data = [
                'images' => [],
                'videos' => [],
                'highlights' => [],
                'categories' => [],
                'actual_images' => [] // Add actual images from folder
            ];

            // Check if tables exist
            $checkTables = $conn->query("SHOW TABLES LIKE 'gallery_images'");
            if ($checkTables && $checkTables->num_rows > 0) {
                // Fetch Images from database
                $result = $conn->query("SELECT * FROM gallery_images ORDER BY uploaded_date DESC, id DESC");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $data['images'][] = formatImageRow($row);
                    }
                    $result->free();
                }
            }
            
            // If no images in database, get from uploads folder
            if (empty($data['images'])) {
                $actualImages = getActualGalleryImages();
                foreach ($actualImages as $index => $actualImage) {
                    $data['images'][] = [
                        'id' => $index + 1000, // Temporary ID for images not in DB
                        'title' => [
                            'en' => pathinfo($actualImage['filename'], PATHINFO_FILENAME),
                            'hi' => pathinfo($actualImage['filename'], PATHINFO_FILENAME),
                            'gu' => pathinfo($actualImage['filename'], PATHINFO_FILENAME)
                        ],
                        'description' => [
                            'en' => 'Uploaded image from gallery',
                            'hi' => 'गैलरी से अपलोड की गई छवि',
                            'gu' => 'ગેલેરીમાંથી અપલોડ કરેલી છબી'
                        ],
                        'category' => 'uncategorized',
                        'project' => 'both',
                        'tags' => ['uploaded', 'gallery'],
                        'featured' => $index < 3,
                        'image_url' => $actualImage['full_url'],
                        'relative_url' => $actualImage['path'],
                        'size' => $actualImage['size'],
                        'dimensions' => 'Unknown',
                        'uploaded_date' => $actualImage['uploaded_at'],
                        'created_at' => $actualImage['uploaded_at'],
                        'from_folder' => true // Flag to indicate this came from folder
                    ];
                }
            }

            // Check for videos table
            $checkVideos = $conn->query("SHOW TABLES LIKE 'gallery_videos'");
            if ($checkVideos && $checkVideos->num_rows > 0) {
                // Fetch Videos
                $result = $conn->query("SELECT * FROM gallery_videos ORDER BY uploaded_date DESC, id DESC");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $data['videos'][] = formatVideoRow($row);
                    }
                    $result->free();
                }
            }

            // Check for highlights table
            $checkHighlights = $conn->query("SHOW TABLES LIKE 'gallery_highlights'");
            if ($checkHighlights && $checkHighlights->num_rows > 0) {
                // Fetch Highlights
                $result = $conn->query("SELECT * FROM gallery_highlights ORDER BY sort_order ASC, updated_at DESC");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $data['highlights'][] = formatHighlightRow($row);
                    }
                    $result->free();
                }
            }

            // Check for categories table
            $checkCategories = $conn->query("SHOW TABLES LIKE 'gallery_categories'");
            if ($checkCategories && $checkCategories->num_rows > 0) {
                // Fetch Categories with counts
                $result = $conn->query("SELECT 
                    c.*,
                    (SELECT COUNT(*) FROM gallery_images i WHERE i.category = c.name) as image_count,
                    (SELECT COUNT(*) FROM gallery_videos v WHERE v.category = c.name) as video_count
                    FROM gallery_categories c
                    ORDER BY c.name");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $data['categories'][] = formatCategoryRow($row);
                    }
                    $result->free();
                }
            } else {
                // Create default categories if table doesn't exist
                $defaultCategories = [
                    ['name' => 'landmark', 'display_name_en' => 'Naroda Landmark', 'display_name_hi' => 'नरोदा लैंडमार्क', 'display_name_gu' => 'નારોદા લેન્ડમાર્ક', 'color' => '#4361ee'],
                    ['name' => 'irish', 'display_name_en' => 'Naroda Irish', 'display_name_hi' => 'नरोदा आयरिश', 'display_name_gu' => 'નારોદા આયરિશ', 'color' => '#3a0ca3'],
                    ['name' => 'construction', 'display_name_en' => 'Construction', 'display_name_hi' => 'निर्माण', 'display_name_gu' => 'કન્સ્ટ્રક્શન', 'color' => '#7209b7'],
                    ['name' => 'interior', 'display_name_en' => 'Interiors', 'display_name_hi' => 'इंटीरियर', 'display_name_gu' => 'ઇન્ટિરિયર્સ', 'color' => '#f72585'],
                    ['name' => 'amenities', 'display_name_en' => 'Amenities', 'display_name_hi' => 'सुविधाएं', 'display_name_gu' => 'સુવિધાઓ', 'color' => '#4cc9f0']
                ];
                
                foreach ($defaultCategories as $cat) {
                    $data['categories'][] = [
                        'id' => count($data['categories']) + 1,
                        'name' => $cat['name'],
                        'display_name' => [
                            'en' => $cat['display_name_en'],
                            'hi' => $cat['display_name_hi'],
                            'gu' => $cat['display_name_gu']
                        ],
                        'color' => $cat['color'],
                        'count' => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }
            }
            
            // Get actual images from uploads folder for reference
            $data['actual_images'] = getActualGalleryImages();

            sendResponse(true, 'Data retrieved successfully', $data);
            break;

        // ------------------------------------------------------------------
        // UPLOAD IMAGE - FIXED FOR YOUR STRUCTURE
        // ------------------------------------------------------------------
        case 'upload_image':
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $errorCode = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                $errorMsg = $errorMessages[$errorCode] ?? 'Unknown upload error';
                sendResponse(false, 'Upload failed: ' . $errorMsg, null, 400);
            }
            
            $file = $_FILES['image'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileType = $file['type'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
            if (!in_array($fileType, $allowedTypes)) {
                sendResponse(false, 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP', null, 400);
            }
            
            // Validate file size (max 10MB)
            $maxSize = 10 * 1024 * 1024;
            if ($fileSize > $maxSize) {
                sendResponse(false, 'File too large. Maximum size: 10MB', null, 400);
            }
            
            // Create uploads directory if it doesn't exist
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename based on timestamp
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $safeName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', basename($fileName, '.' . $extension));
            $timestamp = time();
            
            // Create filename like: gallery_1766776354_filename.jpg
            $uniqueName = 'gallery_' . $timestamp . '_' . $safeName . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $uploadPath = $uploadDir . $uniqueName;
            
            // Move uploaded file
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                // Get file dimensions
                $imageInfo = @getimagesize($uploadPath);
                $dimensions = $imageInfo ? $imageInfo[0] . 'x' . $imageInfo[1] : 'Unknown';
                $formattedSize = round($fileSize / (1024 * 1024), 2) . ' MB';
                
                // Return the relative URL
                $relativePath = 'uploads/' . $uniqueName;
                
                sendResponse(true, 'Image uploaded successfully', [
                    'url' => $relativePath,
                    'full_url' => getImageUrl($relativePath),
                    'filename' => $uniqueName,
                    'size' => $formattedSize,
                    'dimensions' => $dimensions
                ]);
            } else {
                sendResponse(false, 'Failed to save uploaded file', null, 500);
            }
            break;

        // ------------------------------------------------------------------
        // SAVE IMAGE - FIXED
        // ------------------------------------------------------------------
        case 'save_image':
            $input = getJsonInput();
            
            if (empty($input) || empty($input['title']) || empty($input['category'])) {
                sendResponse(false, 'Title and category are required', null, 400);
            }

            $title_en = $input['title']['en'] ?? '';
            $title_hi = $input['title']['hi'] ?? $title_en;
            $title_gu = $input['title']['gu'] ?? $title_en;
            $category = trim($input['category']);
            $project = $input['project'] ?? 'both';
            $description_en = $input['description']['en'] ?? '';
            $description_hi = $input['description']['hi'] ?? $description_en;
            $description_gu = $input['description']['gu'] ?? $description_en;
            $tags = json_encode($input['tags'] ?? []);
            $featured = ($input['featured'] ?? false) ? 1 : 0;
            $image_url = $input['image_url'] ?? '';
            $size = $input['size'] ?? '2.5 MB';
            $dimensions = $input['dimensions'] ?? '1920x1080';
            $uploaded_date = $input['uploaded_date'] ?? date('Y-m-d');

            // Validate that we have an image URL for new images
            if (!isset($input['id']) && empty($image_url)) {
                sendResponse(false, 'Image file is required', null, 400);
            }

            if (isset($input['id']) && is_numeric($input['id']) && $input['id'] > 0) {
                // Update existing image
                $id = (int)$input['id'];
                
                if (empty($image_url)) {
                    $stmt = $conn->prepare("SELECT image_url FROM gallery_images WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $existing = $result->fetch_assoc();
                    $stmt->close();
                    
                    if ($existing) {
                        $image_url = $existing['image_url'];
                    }
                }
                
                $sql = "UPDATE gallery_images SET 
                        title_en = ?, title_hi = ?, title_gu = ?,
                        category = ?, project = ?,
                        description_en = ?, description_hi = ?, description_gu = ?,
                        tags = ?, featured = ?, image_url = ?,
                        size = ?, dimensions = ?, uploaded_date = ?
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $message = 'Image updated successfully';
            } else {
                // Insert new image
                $sql = "INSERT INTO gallery_images 
                        (title_en, title_hi, title_gu, category, project, 
                         description_en, description_hi, description_gu, tags, 
                         featured, image_url, size, dimensions, uploaded_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $message = 'Image saved successfully';
            }

            if (isset($input['id']) && $input['id'] > 0) {
                // Update
                $stmt->bind_param(
                    "ssssssssssisssi",
                    $title_en, $title_hi, $title_gu,
                    $category, $project,
                    $description_en, $description_hi, $description_gu,
                    $tags, $featured, $image_url,
                    $size, $dimensions, $uploaded_date,
                    $id
                );
            } else {
                // Insert
                $stmt->bind_param(
                    "sssssssssissss",
                    $title_en, $title_hi, $title_gu,
                    $category, $project,
                    $description_en, $description_hi, $description_gu,
                    $tags, $featured, $image_url,
                    $size, $dimensions, $uploaded_date
                );
            }

            if ($stmt->execute()) {
                $imageId = isset($input['id']) && $input['id'] > 0 ? $input['id'] : $conn->insert_id;
                
                // Fetch the saved image
                $fetchStmt = $conn->prepare("SELECT * FROM gallery_images WHERE id = ?");
                if ($fetchStmt) {
                    $fetchStmt->bind_param("i", $imageId);
                    $fetchStmt->execute();
                    $result = $fetchStmt->get_result();
                    $savedImage = $result->fetch_assoc();
                    $fetchStmt->close();
                    
                    sendResponse(true, $message, formatImageRow($savedImage));
                } else {
                    sendResponse(true, $message, ['id' => $imageId]);
                }
            } else {
                sendResponse(false, 'Failed to save image: ' . $stmt->error, null, 500);
            }
            
            if (isset($stmt)) $stmt->close();
            break;

        // ------------------------------------------------------------------
        // GET ACTUAL IMAGES FROM FOLDER (NEW ENDPOINT)
        // ------------------------------------------------------------------
        case 'get_actual_images':
            $actualImages = getActualGalleryImages();
            sendResponse(true, 'Images retrieved from uploads folder', [
                'count' => count($actualImages),
                'images' => $actualImages
            ]);
            break;

        // ------------------------------------------------------------------
        // CHECK UPLOADS DIRECTORY
        // ------------------------------------------------------------------
        case 'check_uploads':
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
                sendResponse(true, 'Uploads directory created');
            } else {
                sendResponse(true, 'Uploads directory exists');
            }
            break;

        // ------------------------------------------------------------------
        // DELETE IMAGE
        // ------------------------------------------------------------------
        case 'delete_image':
            $input = getJsonInput();
            $id = $input['id'] ?? null;
            
            if (!$id) {
                sendResponse(false, 'Image ID is required', null, 400);
            }
            
            $stmt = $conn->prepare("DELETE FROM gallery_images WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                sendResponse(true, 'Image deleted successfully');
            } else {
                sendResponse(false, 'Failed to delete image: ' . $stmt->error);
            }
            $stmt->close();
            break;

        // ------------------------------------------------------------------
        // UPLOAD VIDEO FILE
        // ------------------------------------------------------------------
        case 'upload_video':
            if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
                $errorCode = $_FILES['video']['error'] ?? UPLOAD_ERR_NO_FILE;
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                $errorMsg = $errorMessages[$errorCode] ?? 'Unknown upload error';
                sendResponse(false, 'Upload failed: ' . $errorMsg, null, 400);
            }
            
            $file = $_FILES['video'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileType = $file['type'];
            
            // Validate file type
            $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo'];
            if (!in_array($fileType, $allowedTypes)) {
                sendResponse(false, 'Invalid file type. Allowed: MP4, WEBM, OGG, MOV, AVI', null, 400);
            }
            
            // Validate file size (max 100MB)
            $maxSize = 100 * 1024 * 1024;
            if ($fileSize > $maxSize) {
                sendResponse(false, 'File too large. Maximum size: 100MB', null, 400);
            }
            
            // Create uploads directory if it doesn't exist
            $uploadDir = '../uploads/videos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $safeName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', basename($fileName, '.' . $extension));
            $timestamp = time();
            $uniqueName = 'video_' . $timestamp . '_' . $safeName . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $uploadPath = $uploadDir . $uniqueName;
            
            // Move uploaded file
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                $formattedSize = round($fileSize / (1024 * 1024), 2) . ' MB';
                $relativePath = 'uploads/videos/' . $uniqueName;
                
                sendResponse(true, 'Video uploaded successfully', [
                    'url' => $relativePath,
                    'full_url' => getImageUrl($relativePath),
                    'filename' => $uniqueName,
                    'size' => $formattedSize
                ]);
            } else {
                sendResponse(false, 'Failed to save uploaded file', null, 500);
            }
            break;

        // ------------------------------------------------------------------
        // SAVE VIDEO
        // ------------------------------------------------------------------
        case 'save_video':
            $input = getJsonInput();
            
            if (empty($input) || empty($input['title'])) {
                sendResponse(false, 'Title is required', null, 400);
            }

            $title_en = $input['title']['en'] ?? '';
            $title_hi = $input['title']['hi'] ?? $title_en;
            $title_gu = $input['title']['gu'] ?? $title_en;
            $category = $input['category'] ?? '';
            $description_en = $input['description']['en'] ?? '';
            $description_hi = $input['description']['hi'] ?? $description_en;
            $description_gu = $input['description']['gu'] ?? $description_en;
            $tags = json_encode($input['tags'] ?? []);
            $featured = ($input['featured'] ?? false) ? 1 : 0;
            $video_url = $input['video_url'] ?? '';
            $thumbnail_url = $input['thumbnail_url'] ?? '';
            $source = $input['source'] ?? 'upload';
            $duration = $input['duration'] ?? '0:00';
            $size = $input['size'] ?? '0 MB';
            $uploaded_date = $input['uploaded_date'] ?? date('Y-m-d');

            if (isset($input['id']) && is_numeric($input['id']) && $input['id'] > 0) {
                // Update existing video
                $id = (int)$input['id'];
                $sql = "UPDATE gallery_videos SET 
                        title_en = ?, title_hi = ?, title_gu = ?,
                        category = ?,
                        description_en = ?, description_hi = ?, description_gu = ?,
                        featured = ?, video_url = ?,
                        thumbnail_url = ?, source = ?, duration = ?,
                        size = ?, uploaded_date = ?
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $message = 'Video updated successfully';
            } else {
                // Insert new video
                $sql = "INSERT INTO gallery_videos 
                        (title_en, title_hi, title_gu, category, 
                         description_en, description_hi, description_gu, 
                         featured, video_url, thumbnail_url, source, duration, 
                         size, uploaded_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $message = 'Video saved successfully';
            }

            if (isset($input['id']) && $input['id'] > 0) {
                // Update
                $stmt->bind_param(
                    "sssssssissssssi",
                    $title_en, $title_hi, $title_gu,
                    $category,
                    $description_en, $description_hi, $description_gu,
                    $featured, $video_url,
                    $thumbnail_url, $source, $duration,
                    $size, $uploaded_date,
                    $id
                );
            } else {
                // Insert
                $stmt->bind_param(
                    "sssssssissssss",
                    $title_en, $title_hi, $title_gu,
                    $category,
                    $description_en, $description_hi, $description_gu,
                    $featured, $video_url,
                    $thumbnail_url, $source, $duration,
                    $size, $uploaded_date
                );
            }

            if ($stmt->execute()) {
                $videoId = isset($input['id']) && $input['id'] > 0 ? $input['id'] : $conn->insert_id;
                sendResponse(true, $message, ['id' => $videoId]);
            } else {
                sendResponse(false, 'Failed to save video: ' . $stmt->error, null, 500);
            }
            
            if (isset($stmt)) $stmt->close();
            break;

        // ------------------------------------------------------------------
        // DELETE VIDEO
        // ------------------------------------------------------------------
        case 'delete_video':
            $input = getJsonInput();
            $id = $input['id'] ?? null;
            
            if (!$id) {
                sendResponse(false, 'Video ID is required', null, 400);
            }
            
            $stmt = $conn->prepare("DELETE FROM gallery_videos WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                sendResponse(true, 'Video deleted successfully');
            } else {
                sendResponse(false, 'Failed to delete video: ' . $stmt->error);
            }
            $stmt->close();
            break;

        // ------------------------------------------------------------------
        // SAVE HIGHLIGHT
        // ------------------------------------------------------------------
        case 'save_highlight':
            $input = getJsonInput();
            
            if (empty($input) || empty($input['title']) || empty($input['project'])) {
                sendResponse(false, 'Title and project are required', null, 400);
            }

            $title_en = $input['title']['en'] ?? '';
            $title_hi = $input['title']['hi'] ?? $title_en;
            $title_gu = $input['title']['gu'] ?? $title_en;
            $project = $input['project'] ?? '';
            $description_en = $input['description']['en'] ?? '';
            $description_hi = $input['description']['hi'] ?? $description_en;
            $description_gu = $input['description']['gu'] ?? $description_en;
            $location_en = $input['location']['en'] ?? '';
            $location_hi = $input['location']['hi'] ?? $location_en;
            $location_gu = $input['location']['gu'] ?? $location_en;
            $images = json_encode($input['images'] ?? []);
            $sort_order = $input['display_order'] ?? 1;
            $featured = ($input['featured'] ?? false) ? 1 : 0;
            $status = $input['status'] ?? 'active';

            if (isset($input['id']) && is_numeric($input['id']) && $input['id'] > 0) {
                // Update existing highlight
                $id = (int)$input['id'];
                $sql = "UPDATE gallery_highlights SET 
                        title_en = ?, title_hi = ?, title_gu = ?,
                        project = ?,
                        description_en = ?, description_hi = ?, description_gu = ?,
                        location_en = ?, location_hi = ?, location_gu = ?,
                        images = ?, sort_order = ?, featured = ?,
                        status = ?, updated_at = NOW()
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $message = 'Highlight updated successfully';
            } else {
                // Insert new highlight
                $sql = "INSERT INTO gallery_highlights 
                        (title_en, title_hi, title_gu, project, 
                         description_en, description_hi, description_gu, 
                         location_en, location_hi, location_gu, images, 
                         sort_order, featured, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $message = 'Highlight saved successfully';
            }

            if (isset($input['id']) && $input['id'] > 0) {
                // Update
                $stmt->bind_param(
                    "ssssssssssisisi",
                    $title_en, $title_hi, $title_gu,
                    $project,
                    $description_en, $description_hi, $description_gu,
                    $location_en, $location_hi, $location_gu,
                    $images, $sort_order, $featured,
                    $status, $id
                );
            } else {
                // Insert
                $stmt->bind_param(
                    "ssssssssssisis",
                    $title_en, $title_hi, $title_gu,
                    $project,
                    $description_en, $description_hi, $description_gu,
                    $location_en, $location_hi, $location_gu,
                    $images, $sort_order, $featured,
                    $status
                );
            }

            if ($stmt->execute()) {
                $highlightId = isset($input['id']) && $input['id'] > 0 ? $input['id'] : $conn->insert_id;
                sendResponse(true, $message, ['id' => $highlightId]);
            } else {
                sendResponse(false, 'Failed to save highlight: ' . $stmt->error, null, 500);
            }
            
            if (isset($stmt)) $stmt->close();
            break;

        // ------------------------------------------------------------------
        // DELETE HIGHLIGHT
        // ------------------------------------------------------------------
        case 'delete_highlight':
            $input = getJsonInput();
            $id = $input['id'] ?? null;
            
            if (!$id) {
                sendResponse(false, 'Highlight ID is required', null, 400);
            }
            
            $stmt = $conn->prepare("DELETE FROM gallery_highlights WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                sendResponse(true, 'Highlight deleted successfully');
            } else {
                sendResponse(false, 'Failed to delete highlight: ' . $stmt->error);
            }
            $stmt->close();
            break;

        // ------------------------------------------------------------------
        // SAVE CATEGORY
        // ------------------------------------------------------------------
        case 'save_category':
            $input = getJsonInput();
            
            if (empty($input) || empty($input['name'])) {
                sendResponse(false, 'Category name is required', null, 400);
            }

            $name = $input['name'];
            $display_name_en = $input['display_name']['en'] ?? $name;
            $display_name_hi = $input['display_name']['hi'] ?? $display_name_en;
            $display_name_gu = $input['display_name']['gu'] ?? $display_name_en;
            $color = $input['color'] ?? '#4361ee';

            if (isset($input['id']) && is_numeric($input['id']) && $input['id'] > 0) {
                // Update existing category
                $id = (int)$input['id'];
                $sql = "UPDATE gallery_categories SET 
                        name = ?, display_name_en = ?, display_name_hi = ?, 
                        display_name_gu = ?, color = ?
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $message = 'Category updated successfully';
            } else {
                // Check if category already exists
                $checkStmt = $conn->prepare("SELECT id FROM gallery_categories WHERE name = ?");
                $checkStmt->bind_param("s", $name);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                
                if ($result->num_rows > 0) {
                    $checkStmt->close();
                    sendResponse(false, 'Category already exists', null, 400);
                }
                $checkStmt->close();
                
                // Insert new category
                $sql = "INSERT INTO gallery_categories 
                        (name, display_name_en, display_name_hi, display_name_gu, color) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $message = 'Category saved successfully';
            }

            if (isset($input['id']) && $input['id'] > 0) {
                // Update
                $stmt->bind_param(
                    "sssssi",
                    $name, $display_name_en, $display_name_hi, $display_name_gu,
                    $color, $id
                );
            } else {
                // Insert
                $stmt->bind_param(
                    "sssss",
                    $name, $display_name_en, $display_name_hi, $display_name_gu,
                    $color
                );
            }

            if ($stmt->execute()) {
                $categoryId = isset($input['id']) && $input['id'] > 0 ? $input['id'] : $conn->insert_id;
                sendResponse(true, $message, ['id' => $categoryId]);
            } else {
                sendResponse(false, 'Failed to save category: ' . $stmt->error, null, 500);
            }
            
            if (isset($stmt)) $stmt->close();
            break;

        // ------------------------------------------------------------------
        // DELETE CATEGORY
        // ------------------------------------------------------------------
        case 'delete_category':
            $input = getJsonInput();
            $name = $input['name'] ?? null;
            
            if (!$name) {
                sendResponse(false, 'Category name is required', null, 400);
            }
            
            $stmt = $conn->prepare("DELETE FROM gallery_categories WHERE name = ?");
            $stmt->bind_param("s", $name);
            
            if ($stmt->execute()) {
                sendResponse(true, 'Category deleted successfully');
            } else {
                sendResponse(false, 'Failed to delete category: ' . $stmt->error);
            }
            $stmt->close();
            break;

        // ------------------------------------------------------------------
        // GET STATISTICS
        // ------------------------------------------------------------------
        case 'get_statistics':
            $stats = [
                'total_images' => 0,
                'total_videos' => 0,
                'total_categories' => 0,
                'storage_used' => 0,
                'featured_count' => 0
            ];
            
            // Count images
            $result = $conn->query("SELECT COUNT(*) as count FROM gallery_images");
            if ($result) {
                $row = $result->fetch_assoc();
                $stats['total_images'] = (int)$row['count'];
                $result->free();
            }
            
            // Count featured images
            $result = $conn->query("SELECT COUNT(*) as count FROM gallery_images WHERE featured = 1");
            if ($result) {
                $row = $result->fetch_assoc();
                $stats['featured_count'] = (int)$row['count'];
                $result->free();
            }
            
            // Count videos
            $result = $conn->query("SELECT COUNT(*) as count FROM gallery_videos");
            if ($result) {
                $row = $result->fetch_assoc();
                $stats['total_videos'] = (int)$row['count'];
                $result->free();
            }
            
            // Count categories
            $result = $conn->query("SELECT COUNT(*) as count FROM gallery_categories");
            if ($result) {
                $row = $result->fetch_assoc();
                $stats['total_categories'] = (int)$row['count'];
                $result->free();
            }
            
            // Calculate storage (simplified)
            $stats['storage_used'] = round(($stats['total_images'] * 2.5) + ($stats['total_videos'] * 30), 2);
            
            sendResponse(true, 'Statistics retrieved', $stats);
            break;

        // ------------------------------------------------------------------
        // TEST DATABASE CONNECTION
        // ------------------------------------------------------------------
        case 'test_connection':
            $tables = [];
            $result = $conn->query("SHOW TABLES");
            if ($result) {
                while ($row = $result->fetch_array()) {
                    $tables[] = $row[0];
                }
                $result->free();
            }
            
            sendResponse(true, 'Database connection successful', [
                'tables' => $tables,
                'database' => $dbname,
                'server' => $host,
                'status' => 'connected'
            ]);
            break;

        // ------------------------------------------------------------------
        // DEFAULT ACTION
        // ------------------------------------------------------------------
        default:
            sendResponse(false, 'Invalid action specified: ' . $action, null, 400);
            break;
    }
} catch (Exception $e) {
    sendResponse(false, 'Server error: ' . $e->getMessage(), null, 500);
}

// Close connection
$conn->close();
?>