<?php
// api/faq_api.php

// Enable CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$host = 'localhost';
$username = 'root'; // Change as needed
$password = ''; // Change as needed
$database = 'naroda_group_faq'; // Your database name

// Create database connection
try {
    $conn = new mysqli($host, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

// Helper function to send JSON response
function sendResponse($success, $data = null, $error = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'error' => $error,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Helper function to sanitize input
function sanitizeInput($conn, $input) {
    if (is_array($input)) {
        return array_map(function($item) use ($conn) {
            return $conn->real_escape_string(trim($item));
        }, $input);
    }
    return $conn->real_escape_string(trim($input));
}

// Helper function to validate required fields
function validateRequiredFields($fields, $data) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    return $missing;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request parameters
$params = [];
$input = file_get_contents('php://input');
if (!empty($input)) {
    $params = json_decode($input, true);
}

// Merge with GET parameters
$params = array_merge($params, $_GET);

// Main routing logic
switch ($method) {
    case 'GET':
        handleGetRequest($conn, $params);
        break;
    
    case 'POST':
        handlePostRequest($conn, $params);
        break;
    
    case 'PUT':
        handlePutRequest($conn, $params);
        break;
    
    case 'DELETE':
        handleDeleteRequest($conn, $params);
        break;
    
    default:
        sendResponse(false, null, 'Method not allowed', 405);
}

// Handle GET requests
function handleGetRequest($conn, $params) {
    $id = isset($params['id']) ? intval($params['id']) : null;
    $category = isset($params['category']) ? sanitizeInput($conn, $params['category']) : null;
    $search = isset($params['search']) ? sanitizeInput($conn, $params['search']) : null;
    
    if ($id) {
        // Get single FAQ by ID
        $stmt = $conn->prepare("SELECT * FROM faqs WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            sendResponse(false, null, 'FAQ not found', 404);
        }
        
        $faq = $result->fetch_assoc();
        $stmt->close();
        sendResponse(true, $faq);
    } else {
        // Get all FAQs with optional filters
        $query = "SELECT * FROM faqs WHERE 1=1";
        $types = "";
        $values = [];
        
        if ($category && $category !== 'all') {
            $query .= " AND category = ?";
            $types .= "s";
            $values[] = $category;
        }
        
        if ($search) {
            $query .= " AND (question LIKE ? OR question_hi LIKE ? OR question_gu LIKE ? OR tags LIKE ?)";
            $types .= "ssss";
            $searchTerm = "%$search%";
            $values[] = $searchTerm;
            $values[] = $searchTerm;
            $values[] = $searchTerm;
            $values[] = $searchTerm;
        }
        
        $query .= " ORDER BY display_order ASC, created_at DESC";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($values)) {
            $stmt->bind_param($types, ...$values);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $faqs = [];
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
        
        $stmt->close();
        sendResponse(true, $faqs);
    }
}

// Handle POST requests (Create new FAQ)
function handlePostRequest($conn, $params) {
    // Validate required fields
    $requiredFields = ['question', 'answer', 'category'];
    $missing = validateRequiredFields($requiredFields, $params);
    
    if (!empty($missing)) {
        sendResponse(false, null, 'Missing required fields: ' . implode(', ', $missing), 400);
    }
    
    // Extract and sanitize data
    $question = sanitizeInput($conn, $params['question']);
    $question_hi = isset($params['question_hi']) ? sanitizeInput($conn, $params['question_hi']) : '';
    $question_gu = isset($params['question_gu']) ? sanitizeInput($conn, $params['question_gu']) : '';
    $answer = sanitizeInput($conn, $params['answer']);
    $answer_hi = isset($params['answer_hi']) ? sanitizeInput($conn, $params['answer_hi']) : '';
    $answer_gu = isset($params['answer_gu']) ? sanitizeInput($conn, $params['answer_gu']) : '';
    $category = sanitizeInput($conn, $params['category']);
    $status = isset($params['status']) ? sanitizeInput($conn, $params['status']) : 'active';
    $order = isset($params['order']) ? intval($params['order']) : 1;
    $tags = isset($params['tags']) ? sanitizeInput($conn, $params['tags']) : '';
    
    // Insert new FAQ
    $stmt = $conn->prepare("
        INSERT INTO faqs (
            question, question_hi, question_gu, 
            answer, answer_hi, answer_gu, 
            category, status, display_order, tags
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "ssssssssis", 
        $question, $question_hi, $question_gu,
        $answer, $answer_hi, $answer_gu,
        $category, $status, $order, $tags
    );
    
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        
        // Fetch the newly created FAQ
        $stmt2 = $conn->prepare("SELECT * FROM faqs WHERE id = ?");
        $stmt2->bind_param("i", $newId);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $newFAQ = $result->fetch_assoc();
        
        $stmt2->close();
        $stmt->close();
        
        sendResponse(true, $newFAQ, null, 201);
    } else {
        $stmt->close();
        sendResponse(false, null, 'Failed to create FAQ: ' . $conn->error, 500);
    }
}

// Handle PUT requests (Update FAQ)
function handlePutRequest($conn, $params) {
    // Validate required fields
    if (!isset($params['id'])) {
        sendResponse(false, null, 'FAQ ID is required', 400);
    }
    
    $id = intval($params['id']);
    
    // Check if FAQ exists
    $checkStmt = $conn->prepare("SELECT id FROM faqs WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        sendResponse(false, null, 'FAQ not found', 404);
    }
    
    $checkStmt->close();
    
    // Build update query dynamically based on provided fields
    $updateFields = [];
    $types = "";
    $values = [];
    
    $fieldMappings = [
        'question' => 'question',
        'question_hi' => 'question_hi',
        'question_gu' => 'question_gu',
        'answer' => 'answer',
        'answer_hi' => 'answer_hi',
        'answer_gu' => 'answer_gu',
        'category' => 'category',
        'status' => 'status',
        'order' => 'display_order',
        'tags' => 'tags'
    ];
    
    foreach ($fieldMappings as $paramKey => $dbField) {
        if (isset($params[$paramKey])) {
            $updateFields[] = "$dbField = ?";
            
            if ($paramKey === 'order') {
                $types .= "i"; // integer
                $values[] = intval($params[$paramKey]);
            } else {
                $types .= "s"; // string
                $values[] = sanitizeInput($conn, $params[$paramKey]);
            }
        }
    }
    
    if (empty($updateFields)) {
        sendResponse(false, null, 'No fields to update', 400);
    }
    
    // Add updated_at timestamp
    $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
    
    $query = "UPDATE faqs SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $types .= "i";
    $values[] = $id;
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        // Fetch updated FAQ
        $stmt2 = $conn->prepare("SELECT * FROM faqs WHERE id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $updatedFAQ = $result->fetch_assoc();
        
        $stmt2->close();
        $stmt->close();
        
        sendResponse(true, $updatedFAQ);
    } else {
        $stmt->close();
        sendResponse(false, null, 'Failed to update FAQ: ' . $conn->error, 500);
    }
}

// Handle DELETE requests
function handleDeleteRequest($conn, $params) {
    // Validate required fields
    if (!isset($params['id'])) {
        sendResponse(false, null, 'FAQ ID is required', 400);
    }
    
    $id = intval($params['id']);
    
    // Check if FAQ exists
    $checkStmt = $conn->prepare("SELECT id FROM faqs WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        sendResponse(false, null, 'FAQ not found', 404);
    }
    
    $checkStmt->close();
    
    // Delete FAQ
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        sendResponse(true, ['id' => $id, 'deleted' => true]);
    } else {
        $stmt->close();
        sendResponse(false, null, 'Failed to delete FAQ: ' . $conn->error, 500);
    }
}

// Close database connection
$conn->close();