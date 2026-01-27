<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_project_data':
        $data = [
            'apartment_plans' => [
                '2bhk' => [],
                '3bhk' => []
            ],
            'gallery' => [],
            'pricing_plans' => [
                '2bhk' => null,
                '3bhk' => null
            ],
            'featured_properties' => [
                'for-sale' => [],
                'for-rent' => []
            ],
            'schedule_visits' => [
                'sale' => [],
                'rent' => []
            ]
        ];

        // Fetch Apartment Plans
        $res = $conn->query("SELECT * FROM apartment_plans WHERE bhk_type != '1bhk'");
        while ($row = $res->fetch_assoc()) {
            $row['description'] = json_decode($row['description'] ?? '{}', true);
            $data['apartment_plans'][$row['bhk_type']][] = $row;
        }

        // Fetch Gallery
        $res = $conn->query("SELECT * FROM gallery");
        while ($row = $res->fetch_assoc()) {
            $data['gallery'][] = $row;
        }

        // Fetch Pricing Plans
        $res = $conn->query("SELECT * FROM pricing_plans WHERE bhk_type != '1bhk'");
        while ($row = $res->fetch_assoc()) {
            $row['features'] = json_decode($row['features'], true);
            $data['pricing_plans'][$row['bhk_type']] = $row;
        }

        // Fetch Featured Properties
        $res = $conn->query("SELECT * FROM featured_properties");
        while ($row = $res->fetch_assoc()) {
            $row['title'] = json_decode($row['title'] ?? '{}', true);
            $row['location'] = json_decode($row['location'] ?? '{}', true);
            $row['description'] = json_decode($row['description'] ?? '{}', true);
            $row['images'] = json_decode($row['images'] ?? '[]', true);
            $row['brochure'] = json_decode($row['brochure'] ?? 'null', true);
            $row['overview'] = json_decode($row['overview'] ?? 'null', true);
            $row['amenities'] = json_decode($row['amenities'] ?? 'null', true);
            $row['location_details'] = json_decode($row['location_details'] ?? 'null', true);
            $row['documents'] = json_decode($row['documents'] ?? 'null', true);
            $row['floor_plans_dimensions'] = json_decode($row['floor_plans_dimensions'] ?? 'null', true);
            $data['featured_properties'][$row['property_type']][] = $row;
        }

        // Fetch Gallery Images
        $res = $conn->query("SELECT * FROM gallery ORDER BY uploaded_date DESC");
        $data['gallery'] = [];
        while ($row = $res->fetch_assoc()) {
            $data['gallery'][] = $row;
        }

        // Fetch Inquiries
        $res = $conn->query("SELECT * FROM inquiries");
        while ($row = $res->fetch_assoc()) {
            $data['schedule_visits'][$row['inquiry_category']][] = $row;
        }

        echo json_encode($data);
        break;

    case 'save_property':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $id = $input['id'] ?? null;
            $property_type = $conn->real_escape_string($input['property_type'] ?? 'for-sale');
            $title = $conn->real_escape_string(json_encode($input['title'] ?? []));
            $location = $conn->real_escape_string(json_encode($input['location'] ?? []));
            $type = $conn->real_escape_string($input['type'] ?? '');
            $category = $conn->real_escape_string($input['category'] ?? '');
            $area = $conn->real_escape_string($input['area'] ?? '');
            $floor = $conn->real_escape_string($input['floor'] ?? '');
            $parking = $conn->real_escape_string($input['parking'] ?? '');
            $status = $conn->real_escape_string($input['status'] ?? '');
            $price = $conn->real_escape_string($input['price'] ?? '');
            $price_unit = $conn->real_escape_string($input['price_unit'] ?? '');
            $property_id_field = $conn->real_escape_string($input['property_id'] ?? '');
            $facing = $conn->real_escape_string($input['facing'] ?? '');
            $description = $conn->real_escape_string(json_encode($input['description'] ?? []));
            
            $images = $conn->real_escape_string(json_encode($input['images'] ?? []));
            $brochure = $conn->real_escape_string(json_encode($input['brochure'] ?? null));
            $overview = $conn->real_escape_string(json_encode($input['overview'] ?? null));
            $amenities = $conn->real_escape_string(json_encode($input['amenities'] ?? null));
            $location_details = $conn->real_escape_string(json_encode($input['location_details'] ?? null));
            $location_details = $conn->real_escape_string(json_encode($input['location_details'] ?? null));
            $documents = $conn->real_escape_string(json_encode($input['documents'] ?? null));
            
            // New Fields
            $bedrooms = $conn->real_escape_string($input['bedrooms'] ?? '');
            $bathrooms = $conn->real_escape_string($input['bathrooms'] ?? '');
            $floor_plans_dimensions = $conn->real_escape_string(json_encode($input['floor_plans_dimensions'] ?? null));

            if ($id && $id !== 'null') {
                // Update
                $sql = "UPDATE featured_properties SET 
                        property_type='$property_type', title='$title', location='$location', type='$type', 
                        category='$category', area='$area', floor='$floor', parking='$parking', 
                        status='$status', price='$price', price_unit='$price_unit', property_id='$property_id_field', 
                        facing='$facing', description='$description', images='$images', brochure='$brochure', 
                        overview='$overview', amenities='$amenities', location_details='$location_details', documents='$documents',
                        bedrooms='$bedrooms', bathrooms='$bathrooms', floor_plans_dimensions='$floor_plans_dimensions'
                        WHERE id=$id";
            } else {
                // Insert
                $sql = "INSERT INTO featured_properties 
                        (property_type, title, location, type, category, area, floor, parking, status, price, price_unit, property_id, facing, description, images, brochure, overview, amenities, location_details, documents, bedrooms, bathrooms, floor_plans_dimensions) 
                        VALUES 
                        ('$property_type', '$title', '$location', '$type', '$category', '$area', '$floor', '$parking', '$status', '$price', '$price_unit', '$property_id_field', '$facing', '$description', '$images', '$brochure', '$overview', '$amenities', '$location_details', '$documents', '$bedrooms', '$bathrooms', '$floor_plans_dimensions')";
            }

            if ($conn->query($sql)) {
                file_put_contents('debug_log.txt', date('[Y-m-d H:i:s] ') . "Success: Property saved. ID: " . ($id ?: $conn->insert_id) . "\n", FILE_APPEND);
                echo json_encode(["status" => "success", "id" => $id ?: $conn->insert_id]);
            } else {
                file_put_contents('debug_log.txt', date('[Y-m-d H:i:s] ') . "Error: " . $conn->error . "\nSQL: " . substr($sql, 0, 500) . "...\n", FILE_APPEND);
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'delete_property':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $id = $input['id'];
            if ($conn->query("DELETE FROM featured_properties WHERE id=$id")) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'save_inquiry':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $category = $conn->real_escape_string($input['inquiry_category'] ?? 'sale');
            $name = $conn->real_escape_string($input['name'] ?? '');
            $email = $conn->real_escape_string($input['email'] ?? '');
            $phone = $conn->real_escape_string($input['phone'] ?? '');
            $type = $conn->real_escape_string($input['inquiry_type'] ?? '');
            $p_id = $conn->real_escape_string($input['property_id'] ?? '');
            $p_title = $conn->real_escape_string($input['property_title'] ?? '');
            $message = $conn->real_escape_string($input['message'] ?? '');
            $pref_date = $conn->real_escape_string($input['preferred_date'] ?? '');
            
            $sql = "INSERT INTO inquiries (inquiry_category, name, email, phone, inquiry_type, property_id, property_title, message, preferred_date) 
                    VALUES ('$category', '$name', '$email', '$phone', '$type', '$p_id', '$p_title', '$message', '$pref_date')";
                    
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'update_inquiry_status':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $id = $input['id'];
            $status = $conn->real_escape_string($input['status']);
            if ($conn->query("UPDATE inquiries SET status='$status' WHERE id=$id")) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'delete_inquiry':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $id = $input['id'];
            if ($conn->query("DELETE FROM inquiries WHERE id=$id")) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'save_apartment_plan':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $id = $input['id'] ?? null;
            $bhk_type = $conn->real_escape_string($input['bhk_type']);

            // Safety check: Don't allow saving 1bhk
            if ($bhk_type === '1bhk') {
                echo json_encode(["status" => "error", "message" => "1 BHK plans are no longer supported"]);
                break;
            }
            $area = $conn->real_escape_string($input['area']);
            $bedrooms = $conn->real_escape_string($input['bedrooms']);
            $bathrooms = $conn->real_escape_string($input['bathrooms']);
            $balconies = $conn->real_escape_string($input['balconies']);
            $description = $conn->real_escape_string(json_encode($input['description'] ?? []));
            $image = $conn->real_escape_string($input['image']);
            $last_updated = date('Y-m-d');
            
            if ($id) {
                $sql = "UPDATE apartment_plans SET 
                        bhk_type='$bhk_type', area='$area', bedrooms='$bedrooms', bathrooms='$bathrooms', 
                        balconies='$balconies', description='$description', image='$image', last_updated='$last_updated' 
                        WHERE id=$id";
            } else {
                $sql = "INSERT INTO apartment_plans (bhk_type, area, bedrooms, bathrooms, balconies, description, image, last_updated) 
                        VALUES ('$bhk_type', '$area', '$bedrooms', '$bathrooms', '$balconies', '$description', '$image', '$last_updated')";
            }
            
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "id" => $id ?: $conn->insert_id]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'save_gallery_image':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $url = $conn->real_escape_string($input['url']);
            $title = $conn->real_escape_string($input['title']);
            $uploaded_date = date('Y-m-d');
            
            $sql = "INSERT INTO gallery (url, title, uploaded_date) VALUES ('$url', '$title', '$uploaded_date')";
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "id" => $conn->insert_id]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'save_pricing_plan':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $bhk_type_val = $conn->real_escape_string($input['type']);

            // Safety check: Don't allow saving 1bhk
            if ($bhk_type_val === '1bhk') {
                echo json_encode(["status" => "error", "message" => "1 BHK pricing is no longer supported"]);
                break;
            }
            $starting_price = $conn->real_escape_string($input['startingPrice']);
            $sqft = $conn->real_escape_string($input['sqft']);
            $bedrooms = $conn->real_escape_string($input['bedrooms']);
            $bathrooms = $conn->real_escape_string($input['bathrooms']);
            $parking = $conn->real_escape_string($input['parking']);
            $available = $conn->real_escape_string($input['available']);
            $availability_status = $conn->real_escape_string($input['availabilityStatus']);
            $features = $conn->real_escape_string(json_encode($input['features'] ?? []));
            
            $sql = "INSERT INTO pricing_plans (bhk_type, starting_price, sqft, bedrooms, bathrooms, parking, available, availability_status, features) 
                    VALUES ('$bhk_type_val', '$starting_price', '$sqft', '$bedrooms', '$bathrooms', '$parking', '$available', '$availability_status', '$features')
                    ON DUPLICATE KEY UPDATE 
                    starting_price='$starting_price', sqft='$sqft', bedrooms='$bedrooms', bathrooms='$bathrooms', 
                    parking='$parking', available='$available', availability_status='$availability_status', features='$features'";
            
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'delete_apartment_plan':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $id = $input['id'];
            if ($conn->query("DELETE FROM apartment_plans WHERE id=$id")) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'save_gallery_image':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $title = $conn->real_escape_string($input['title'] ?? '');
            $url = $conn->real_escape_string($input['url'] ?? '');
            $description = $conn->real_escape_string($input['description'] ?? '');
            $uploaded_date = date('Y-m-d');
            $is_featured = isset($input['is_featured']) ? ($input['is_featured'] ? 1 : 0) : 0;
            
            $sql = "INSERT INTO gallery (title, url, description, uploaded_date, is_featured) 
                    VALUES ('$title', '$url', '$description', '$uploaded_date', $is_featured)";
            
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "id" => $conn->insert_id]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'fetch_gallery':
        if ($method === 'GET') {
            $result = $conn->query("SELECT * FROM gallery ORDER BY uploaded_date DESC");
            $gallery = [];
            while ($row = $result->fetch_assoc()) {
                $gallery[] = $row;
            }
            echo json_encode(["status" => "success", "gallery" => $gallery]);
        }
        break;

    case 'delete_gallery_image':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('C:\Users\SAHIL\AppData\Local\Temp\php654C.tmp'), true);
            $id = $input['id'];
            if ($conn->query("DELETE FROM gallery WHERE id=$id")) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        break;
}

$conn->close();
?>
