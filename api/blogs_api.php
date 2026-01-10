<?php
// Add output buffering at the VERY TOP
ob_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Error reporting - enable for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'naroda_group';

$response = ["success" => false, "message" => "Unknown error"];
$conn = null;

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if (empty($action)) {
        throw new Exception("No action specified");
    }

    switch ($action) {
        case 'get_blogs':
            $sql = "SELECT * FROM blogs ORDER BY created_at DESC";
            $result = $conn->query($sql);
            
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            
            $blogs = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Formatting for frontend compatibility
                    $row['title'] = [
                        'en' => $row['title_en'] ?? '',
                        'hi' => $row['title_hi'] ?? '',
                        'gu' => $row['title_gu'] ?? ''
                    ];
                    $row['excerpt'] = [
                        'en' => $row['excerpt_en'] ?? '',
                        'hi' => $row['excerpt_hi'] ?? '',
                        'gu' => $row['excerpt_gu'] ?? ''
                    ];
                    $row['content'] = [
                        'en' => $row['content_en'] ?? '',
                        'hi' => $row['content_hi'] ?? '',
                        'gu' => $row['content_gu'] ?? ''
                    ];
                    $row['author'] = [
                        'en' => $row['author_en'] ?? 'Admin',
                        'hi' => $row['author_hi'] ?? 'Admin',
                        'gu' => $row['author_gu'] ?? 'Admin'
                    ];
                    $blogs[] = $row;
                }
            }
            $response = ["success" => true, "data" => $blogs];
            break;

        case 'save_blog':
            // Support both JSON and Form Data
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }

            $id = isset($input['id']) && is_numeric($input['id']) ? $input['id'] : null;
            
            $title_en = $input['title']['en'] ?? $input['title_en'] ?? '';
            $title_hi = $input['title']['hi'] ?? $input['title_hi'] ?? '';
            $title_gu = $input['title']['gu'] ?? $input['title_gu'] ?? '';
            
            $excerpt_en = $input['excerpt']['en'] ?? $input['excerpt_en'] ?? '';
            $excerpt_hi = $input['excerpt']['hi'] ?? $input['excerpt_hi'] ?? '';
            $excerpt_gu = $input['excerpt']['gu'] ?? $input['excerpt_gu'] ?? '';
            
            $content_en = $input['content']['en'] ?? $input['content_en'] ?? '';
            $content_hi = $input['content']['hi'] ?? $input['content_hi'] ?? '';
            $content_gu = $input['content']['gu'] ?? $input['content_gu'] ?? '';

            // Author handling
            $author_en = is_array($input['author']) ? ($input['author']['en'] ?? '') : ($input['author'] ?? 'Admin');
            $author_hi = is_array($input['author']) ? ($input['author']['hi'] ?? '') : ($input['author'] ?? 'Admin');
            $author_gu = is_array($input['author']) ? ($input['author']['gu'] ?? '') : ($input['author'] ?? 'Admin');

            $category = $input['category'] ?? 'trends';
            $status = $input['status'] ?? 'draft';
            $date = $input['date'] ?? date('Y-m-d');
            $tags = is_array($input['tags']) ? implode(',', $input['tags']) : ($input['tags'] ?? '');
            $read_time = $input['readTime'] ?? '5 min read';
            
            // Handle Image
            $image = $input['image'] ?? '';
            
            // Check if image is Base64
            if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                $data = substr($image, strpos($image, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif
                
                if (in_array($type, [ 'jpg', 'jpeg', 'gif', 'png', 'webp' ])) {
                    $data = base64_decode($data);
                    if ($data !== false) {
                        $target_dir = "../uploads/blogs/";
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }
                        $new_filename = uniqid() . '.' . $type;
                        file_put_contents($target_dir . $new_filename, $data);
                        $image = "uploads/blogs/" . $new_filename;
                    }
                }
            }

            if ($id) {
                // Update
                $check = $conn->query("SELECT id FROM blogs WHERE id = $id");
                if ($check && $check->num_rows > 0) {
                     $stmt = $conn->prepare("UPDATE blogs SET title_en=?, title_hi=?, title_gu=?, excerpt_en=?, excerpt_hi=?, excerpt_gu=?, content_en=?, content_hi=?, content_gu=?, category=?, author_en=?, author_hi=?, author_gu=?, status=?, date=?, tags=?, image=?, read_time=? WHERE id=?");
                     $stmt->bind_param("ssssssssssssssssssi", $title_en, $title_hi, $title_gu, $excerpt_en, $excerpt_hi, $excerpt_gu, $content_en, $content_hi, $content_gu, $category, $author_en, $author_hi, $author_gu, $status, $date, $tags, $image, $read_time, $id);
                } else {
                     // Insert
                     $stmt = $conn->prepare("INSERT INTO blogs (title_en, title_hi, title_gu, excerpt_en, excerpt_hi, excerpt_gu, content_en, content_hi, content_gu, category, author_en, author_hi, author_gu, status, date, tags, image, read_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                     $stmt->bind_param("ssssssssssssssssss", $title_en, $title_hi, $title_gu, $excerpt_en, $excerpt_hi, $excerpt_gu, $content_en, $content_hi, $content_gu, $category, $author_en, $author_hi, $author_gu, $status, $date, $tags, $image, $read_time);
                }
            } else {
                 // Insert
                 $stmt = $conn->prepare("INSERT INTO blogs (title_en, title_hi, title_gu, excerpt_en, excerpt_hi, excerpt_gu, content_en, content_hi, content_gu, category, author_en, author_hi, author_gu, status, date, tags, image, read_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                 $stmt->bind_param("ssssssssssssssssss", $title_en, $title_hi, $title_gu, $excerpt_en, $excerpt_hi, $excerpt_gu, $content_en, $content_hi, $content_gu, $category, $author_en, $author_hi, $author_gu, $status, $date, $tags, $image, $read_time);
            }

            if ($stmt->execute()) {
                $response = ["success" => true, "message" => "Blog saved successfully"];
            } else {
                $response = ["success" => false, "message" => "Error: " . $stmt->error];
            }
            $stmt->close();
            break;

        case 'delete_blog':
            // Check both POST and JSON input
            $id = null;
            
            // First check POST data (FormData)
            if (isset($_POST['id']) && is_numeric($_POST['id'])) {
                $id = intval($_POST['id']);
            } 
            // If not in POST, check JSON input
            else {
                $input = json_decode(file_get_contents('php://input'), true);
                if ($input && isset($input['id']) && is_numeric($input['id'])) {
                    $id = intval($input['id']);
                }
            }
            
            if (!$id) {
                $response = ["success" => false, "message" => "Invalid blog ID"];
                break;
            }
            
            // Optional: Check if blog exists before deleting
            $check = $conn->prepare("SELECT id FROM blogs WHERE id = ?");
            $check->bind_param("i", $id);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows === 0) {
                $response = ["success" => false, "message" => "Blog not found"];
                $check->close();
                break;
            }
            $check->close();
            
            // Delete the blog
            $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $response = ["success" => true, "message" => "Blog deleted successfully"];
            } else {
                $response = ["success" => false, "message" => "Error deleting blog: " . $stmt->error];
            }
            $stmt->close();
            break;

        default:
            $response = ["success" => false, "message" => "Invalid action"];
            break;
    }
    
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
} finally {
    // Clean any output buffer
    ob_end_clean();
    
    // Ensure JSON is output
    echo json_encode($response);
    
    // Close connection if open
    if ($conn) {
        $conn->close();
    }
    
    // Exit to prevent any additional output
    exit();
}
?>