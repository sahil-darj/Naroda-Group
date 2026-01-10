<?php
// Start output buffering to catch any unexpected output
ob_start();

// Disable error display to prevent HTML in JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
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
    require_once '../models/Team.php';
    require_once '../models/FAQ.php'; // Import FAQ Model
    
    $team = new Team();
    $faqModel = new FAQ(); // Instantiate FAQ Model
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Get action from request
    $action = $_GET['action'] ?? '';
    
    if (empty($action)) {
        throw new Exception('No action specified');
    }
    
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $team, $faqModel);
            break;
            
        case 'POST':
            handlePostRequest($action, $team);
            break;
        
        // Supporting DELETE via POST with action or actual DELETE method
        case 'DELETE':
            handleDeleteRequest($action, $team);
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

function handleGetRequest($action, $team, $faqModel = null) {
    ob_end_clean();
    
    switch ($action) {
        case 'get_members':
            $search = $_GET['search'] ?? null;
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['perPage'] ?? 10;
            $department = $_GET['department'] ?? null;
            
            $result = $team->getAllMembers($search, $page, $perPage, $department);
            echo json_encode(array_merge(['success' => true], $result));
            break;
            
        case 'get_member':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                $memberData = $team->getMemberById($id);
                if ($memberData) {
                    echo json_encode(['success' => true, 'member' => $memberData]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Member not found']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'ID is required']);
            }
            break;

        case 'get_faqs':
            if (!$faqModel) {
                 echo json_encode(['success' => false, 'error' => 'FAQ Model not loaded']);
                 return;
            }
            $lang = $_GET['lang'] ?? 'en';
            // Default logic: Fetch active FAQs, all categories
            $result = $faqModel->getFAQsByLanguage($lang, 'all', null, 1, 50); 
            echo json_encode(['success' => true, 'data' => $result['faqs']]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit();
}

function handlePostRequest($action, $team) {
    ob_end_clean();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    switch ($action) {
        case 'save_member':
            $result = $team->saveMember($data);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit();
}

function handleDeleteRequest($action, $team) {
    ob_end_clean();
    
    switch ($action) {
        case 'delete_member':
            $id = $_GET['id'] ?? 0;
            if ($id > 0) {
                $result = $team->deleteMember($id);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID is required']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit();
}


?>
