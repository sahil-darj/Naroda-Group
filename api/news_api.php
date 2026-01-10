<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'naroda_group';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle Image Upload Helper
function handleImageUpload() {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/news/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            return "uploads/news/" . $new_filename; // Return relative path for DB
        }
    }
    return null;
}

switch ($action) {
    case 'get_news':
        $sql = "SELECT * FROM news ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        $news = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Formatting for frontend compatibility
                $row['title'] = [
                    'en' => $row['title_en'],
                    'hi' => $row['title_hi'],
                    'gu' => $row['title_gu']
                ];
                $row['excerpt'] = [
                    'en' => $row['excerpt_en'],
                    'hi' => $row['excerpt_hi'],
                    'gu' => $row['excerpt_gu']
                ];
                $row['content'] = [
                    'en' => $row['content_en'],
                    'hi' => $row['content_hi'],
                    'gu' => $row['content_gu']
                ];
                $news[] = $row;
            }
        }
        echo json_encode(["success" => true, "data" => $news]);
        break;

    case 'save_news':
        // Support both JSON and Form Data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $id = isset($input['id']) && is_numeric($input['id']) ? $input['id'] : null;
        
        // Extract Multilingual Fields (Handling nested arrays if JSON, or flat keys if FormData)
        // Note: For simplicity, assuming JSON payload structure from frontend or constructed manually
        
        $title_en = $input['title']['en'] ?? $input['title_en'] ?? '';
        $title_hi = $input['title']['hi'] ?? $input['title_hi'] ?? '';
        $title_gu = $input['title']['gu'] ?? $input['title_gu'] ?? '';
        
        $excerpt_en = $input['excerpt']['en'] ?? $input['excerpt_en'] ?? '';
        $excerpt_hi = $input['excerpt']['hi'] ?? $input['excerpt_hi'] ?? '';
        $excerpt_gu = $input['excerpt']['gu'] ?? $input['excerpt_gu'] ?? '';
        
        $content_en = $input['content']['en'] ?? $input['content_en'] ?? '';
        $content_hi = $input['content']['hi'] ?? $input['content_hi'] ?? '';
        $content_gu = $input['content']['gu'] ?? $input['content_gu'] ?? '';
        
        $category = $input['category'] ?? 'general';
        $author = $input['author'] ?? 'Admin';
        $status = $input['status'] ?? 'draft';
        $date = $input['date'] ?? date('Y-m-d');
        $tags = is_array($input['tags']) ? implode(',', $input['tags']) : ($input['tags'] ?? '');
        $featured = isset($input['featured']) ? (int)$input['featured'] : 0;
        
        // Handle Image
        $image = $input['image'] ?? '';
        
        // Check if image is Base64
        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            $data = substr($image, strpos($image, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif
            
            if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png', 'webp' ])) {
                // Invalid type
            } else {
                $data = base64_decode($data);
                if ($data === false) {
                    // Decode failed
                } else {
                    $target_dir = "../uploads/news/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $new_filename = uniqid() . '.' . $type;
                    file_put_contents($target_dir . $new_filename, $data);
                    $image = "uploads/news/" . $new_filename;
                }
            }
        }

        if ($id) {
            // Check if ID exists (real ID vs timestamp ID from mock)
            // If ID is very large (timestamp), it might be a new item if not in DB.
            // But usually we use 0 or null for new.
            // Let's rely on checking if it exists or just Insert.
            
            // Try Update
            $check = $conn->query("SELECT id FROM news WHERE id = $id");
            if ($check->num_rows > 0) {
                 $stmt = $conn->prepare("UPDATE news SET title_en=?, title_hi=?, title_gu=?, excerpt_en=?, excerpt_hi=?, excerpt_gu=?, content_en=?, content_hi=?, content_gu=?, category=?, author=?, status=?, date=?, tags=?, featured=?, image=? WHERE id=?");
                 $stmt->bind_param("ssssssssssssssisi", $title_en, $title_hi, $title_gu, $excerpt_en, $excerpt_hi, $excerpt_gu, $content_en, $content_hi, $content_gu, $category, $author, $status, $date, $tags, $featured, $image, $id);
            } else {
                 // Insert with specific ID (rare) or treat as new? Treat as new but let DB assign ID
                 $stmt = $conn->prepare("INSERT INTO news (title_en, title_hi, title_gu, excerpt_en, excerpt_hi, excerpt_gu, content_en, content_hi, content_gu, category, author, status, date, tags, featured, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                 $stmt->bind_param("ssssssssssssssis", $title_en, $title_hi, $title_gu, $excerpt_en, $excerpt_hi, $excerpt_gu, $content_en, $content_hi, $content_gu, $category, $author, $status, $date, $tags, $featured, $image);
            }
        } else {
             $stmt = $conn->prepare("INSERT INTO news (title_en, title_hi, title_gu, excerpt_en, excerpt_hi, excerpt_gu, content_en, content_hi, content_gu, category, author, status, date, tags, featured, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
             $stmt->bind_param("ssssssssssssssis", $title_en, $title_hi, $title_gu, $excerpt_en, $excerpt_hi, $excerpt_gu, $content_en, $content_hi, $content_gu, $category, $author, $status, $date, $tags, $featured, $image);
        }

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "News saved successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'delete_news':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'];
        
        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "News deleted successfully"]);
        } else {
             echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        break;
}

$conn->close();
?>
