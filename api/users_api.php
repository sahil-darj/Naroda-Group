<?php
// Suppress HTML error output - log errors instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once '../models/User.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to load User model: ' . $e->getMessage()]);
    exit();
}

$user = new User();
$method = $_SERVER['REQUEST_METHOD'];

// Handle CORS preflight
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get action from request
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        handleGetRequest($action, $user);
        break;
        
    case 'POST':
        handlePostRequest($action, $user);
        break;
    
    // Supporting DELETE via POST with action or actual DELETE method if server supports it
    case 'DELETE':
        handleDeleteRequest($action, $user);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        http_response_code(405);
}

function handleGetRequest($action, $user) {
    switch ($action) {
        case 'get_users':
            $search = $_GET['search'] ?? null;
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['perPage'] ?? 10;
            
            $result = $user->getAllUsers($search, $page, $perPage);
            echo json_encode(array_merge(['success' => true], $result));
            break;
            
        case 'get_user':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                $userData = $user->getUserById($id);
                if ($userData) {
                    echo json_encode(['success' => true, 'user' => $userData]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'User not found']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'ID is required']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}

function handlePostRequest($action, $user) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    switch ($action) {
        case 'save_user':
            $result = $user->saveUser($data);
            echo json_encode($result);
            break;
        
        case 'login':
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Email and password are required']);
                break;
            }
            
            $result = $user->login($email, $password);
            echo json_encode($result);
            break;
        
        case 'register':
            // Validate required fields
            if (empty($data['firstname']) || empty($data['lastname']) || empty($data['email']) || empty($data['password'])) {
                echo json_encode(['success' => false, 'message' => 'First name, last name, email, and password are required']);
                break;
            }
            
            $result = $user->register($data);
            echo json_encode($result);
            break;
        
        case 'forgot_password':
            $email = $data['email'] ?? '';
            
            if (empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Email is required']);
                break;
            }
            
            $result = $user->generateForgotPasswordOTP($email);
            echo json_encode($result);
            break;
        
        case 'reset_password':
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Email and password are required']);
                break;
            }
            
            $result = $user->resetPassword($email, $password);
            echo json_encode($result);
            break;
        
        case 'logout':
            // Simple logout - just return success (session management is client-side)
            echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
            break;
        
        case 'change_password':
            $userId = $data['user_id'] ?? 0;
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';
            
            if (empty($userId) || empty($currentPassword) || empty($newPassword)) {
                echo json_encode(['success' => false, 'message' => 'User ID, current password, and new password are required']);
                break;
            }
            
            $result = $user->changePassword($userId, $currentPassword, $newPassword);
            echo json_encode($result);
            break;
        
        case 'update_profile':
            $userId = $data['user_id'] ?? 0;
            
            if (empty($userId)) {
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                break;
            }
            
            $result = $user->updateProfile($userId, $data);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}

function handleDeleteRequest($action, $user) {
    switch ($action) {
        case 'delete_user':
            $id = $_GET['id'] ?? 0;
            if ($id > 0) {
                $result = $user->deleteUser($id);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID is required']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}
?>
