<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Start session for OTP storage
session_start();

require_once '../models/User.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$user = new User();
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $data = $_POST;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'check_email':
        if (empty($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            break;
        }
        $userData = $user->getUserByEmail($data['email']);
        if ($userData) {
            // Remove password from response
            unset($userData['password']);
            echo json_encode(['success' => true, 'exists' => true, 'user' => $userData]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email not found']);
        }
        break;

    case 'send_otp':
        if (empty($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            break;
        }
        
        // Generate random 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // Store in session
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_email'] = $data['email'];
        $_SESSION['otp_expiry'] = time() + 300; // 5 minutes
        
        // In a real app, send email here. For now, return it (for testing) or log it.
        // We will return it in the response for easy testing since we don't have SMTP.
        
        echo json_encode([
            'success' => true, 
            'message' => 'OTP sent successfully',
            'email' => $data['email'],
            'otp' => $otp // REMOVE THIS IN PRODUCTION
        ]);
        break;

    case 'verify_otp':
        if (empty($data['otp']) || empty($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'OTP and Email are required']);
            break;
        }
        
        if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
            // Fallback for when session is lost or testing cross-origin without credentials
            // For this specific 'fix', let's be lenient if the user provided correct OTP logic
            // But since we can't trust client, we really need the session.
            // If session is empty, maybe we can rely on client passing the OTP they received (INSECURE but works for "mock").
            // Better: Use a simple file-based storage if sessions are flaky with react/fetch without credentials.
            // But let's try session first.
            
            // Debugging
            // error_log("Session OTP: " . ($_SESSION['otp'] ?? 'none'));
            
             echo json_encode(['success' => false, 'message' => 'OTP expired or invalid session. Please request a new OTP.']);
             break;
        }
        
        if ($_SESSION['otp_email'] !== $data['email']) {
             echo json_encode(['success' => false, 'message' => 'Email mismatch']);
             break;
        }
        
        if (time() > $_SESSION['otp_expiry']) {
             echo json_encode(['success' => false, 'message' => 'OTP expired']);
             break;
        }
        
        if ($_SESSION['otp'] === $data['otp']) {
            $userData = $user->getUserByEmail($data['email']);
             echo json_encode(['success' => true, 'message' => 'OTP verified', 'userId' => $userData['id']]);
        } else {
             echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
        }
        break;

    case 'reset_password':
        if (empty($data['userId']) || empty($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'User ID and Password are required']);
            break;
        }
        
        $result = $user->updatePassword($data['userId'], $data['password']);
        
        if ($result['success']) {
            // Clear OTP session
            unset($_SESSION['otp']);
            unset($_SESSION['otp_email']);
            unset($_SESSION['otp_expiry']);
        }
        
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
