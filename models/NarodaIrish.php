<?php
require_once __DIR__ . '/../config/database.php';

class NarodaIrish {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    // --- Apartment Plans ---
    public function getApartmentPlans($type = null) {
        $sql = "SELECT * FROM apartment_plans";
        if ($type) {
            // Adjust filtering to match bhk_type column
            $stmt = $this->db->prepare($sql . " WHERE bhk_type = ?");
            $stmt->execute([$type]);
        } else {
            $stmt = $this->db->query($sql);
        }
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Transform data to match frontend expectations
        return array_map(function($plan) {
            // Parse JSON description
            $desc = json_decode($plan['description'] ?? '{}', true);
            
            return [
                'id' => $plan['id'],
                'type' => $plan['bhk_type'], // Map bhk_type to type
                'area' => $plan['area'],
                'area_sqft' => $plan['area'], // Redundant but safe mapping
                'bedrooms' => $plan['bedrooms'],
                'bathrooms' => $plan['bathrooms'],
                'balconies' => $plan['balconies'],
                'image_url' => $plan['image'] ?? '', // Map image to image_url
                'description_en' => $desc['en'] ?? '',
                'description_hi' => $desc['hi'] ?? '',
                'description_gu' => $desc['gu'] ?? '',
                'title_en' => strtoupper($plan['bhk_type']) . ' Apartment', // safe default title
                'status' => $plan['status']
            ];
        }, $plans);
    }

    public function saveApartmentPlan($data) {
        // Construct JSON description
        $description = json_encode([
            'en' => $data['description_en'] ?? '',
            'hi' => $data['description_hi'] ?? '',
            'gu' => $data['description_gu'] ?? ''
        ]);

        if (isset($data['id']) && $data['id']) {
            $stmt = $this->db->prepare("UPDATE apartment_plans SET 
                bhk_type = ?, area = ?, bedrooms = ?, bathrooms = ?, balconies = ?, 
                description = ?, image = ?, status = ? WHERE id = ?");
            return $stmt->execute([
                $data['type'], $data['area'], $data['bedrooms'], $data['bathrooms'], $data['balconies'],
                $description,
                $data['image_url'], $data['status'], $data['id']
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO apartment_plans 
                (bhk_type, area, bedrooms, bathrooms, balconies, description, image, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $data['type'], $data['area'], $data['bedrooms'], $data['bathrooms'], $data['balconies'],
                $description,
                $data['image_url'], $data['status']
            ]);
        }
    }

    public function deleteApartmentPlan($id) {
        $stmt = $this->db->prepare("DELETE FROM apartment_plans WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Gallery ---
    public function getGallery() {
        return $this->db->query("SELECT * FROM ni_gallery ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveGalleryImage($data) {
        $stmt = $this->db->prepare("INSERT INTO ni_gallery (title, description, image_url, is_featured) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['title'], $data['description'], $data['image_url'], $data['is_featured']]);
    }

    public function deleteGalleryImage($id) {
        $stmt = $this->db->prepare("DELETE FROM ni_gallery WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Pricing Plans ---
    public function getPricingPlans($type = null) {
        $sql = "SELECT * FROM ni_pricing_plans";
        if ($type) {
            $stmt = $this->db->prepare($sql . " WHERE type = ?");
            $stmt->execute([$type]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function savePricingPlan($data) {
        $stmt = $this->db->prepare("SELECT id FROM ni_pricing_plans WHERE type = ?");
        $stmt->execute([$data['type']]);
        $existing = $stmt->fetch();

        $params = [
            $data['type'], $data['starting_price'], $data['sqft'], $data['bedrooms'], $data['bathrooms'], $data['parking'],
            $data['available_units'], $data['availability_status'], 
            json_encode($data['features_en']), json_encode($data['features_hi']), json_encode($data['features_gu']),
            $data['status'], $data['last_updated']
        ];

        if ($existing) {
            $params[] = $existing['id'];
            $stmt = $this->db->prepare("UPDATE ni_pricing_plans SET 
                type=?, starting_price=?, sqft=?, bedrooms=?, bathrooms=?, parking=?, 
                available_units=?, availability_status=?, features_en=?, features_hi=?, 
                features_gu=?, status=?, last_updated=? WHERE id=?");
            return $stmt->execute($params);
        } else {
            $stmt = $this->db->prepare("INSERT INTO ni_pricing_plans 
                (type, starting_price, sqft, bedrooms, bathrooms, parking, available_units, availability_status, 
                features_en, features_hi, features_gu, status, last_updated) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute($params);
        }
    }

    // --- Featured Properties ---
    public function getFeaturedProperties($category = null) {
        $sql = "SELECT * FROM ni_featured_properties";
        if ($category) {
            $stmt = $this->db->prepare($sql . " WHERE category = ?");
            $stmt->execute([$category]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveFeaturedProperty($data) {
        $jsonData = [
            'overview_features_en', 'overview_features_hi', 'overview_features_gu',
            'amenities_sections_en', 'amenities_sections_hi', 'amenities_sections_gu',
            'floor_plans_dimensions', 'location_details', 'documents', 'images', 'brochure'
        ];

        foreach ($jsonData as $key) {
            if (isset($data[$key])) {
                $data[$key] = json_encode($data[$key]);
            } else {
                $data[$key] = json_encode([]);
            }
        }

        if (isset($data['id']) && $data['id']) {
            $sql = "UPDATE ni_featured_properties SET 
                property_id = ?, category = ?, status = ?, title_en = ?, title_hi = ?, title_gu = ?,
                location_en = ?, location_hi = ?, location_gu = ?, price = ?, price_unit = ?,
                area = ?, floor = ?, parking = ?, facing = ?, description_en = ?, description_hi = ?, description_gu = ?,
                overview_description_en = ?, overview_description_hi = ?, overview_description_gu = ?,
                overview_features_en = ?, overview_features_hi = ?, overview_features_gu = ?,
                amenities_sections_en = ?, amenities_sections_hi = ?, amenities_sections_gu = ?,
                floor_plans_dimensions = ?, location_details = ?, documents = ?, images = ?, brochure = ?
                WHERE id = ?";
            $params = [
                $data['property_id'], $data['category'], $data['status'], $data['title_en'], $data['title_hi'], $data['title_gu'],
                $data['location_en'], $data['location_hi'], $data['location_gu'], $data['price'], $data['price_unit'],
                $data['area'], $data['floor'], $data['parking'], $data['facing'], $data['description_en'], $data['description_hi'], $data['description_gu'],
                $data['overview_description_en'], $data['overview_description_hi'], $data['overview_description_gu'],
                $data['overview_features_en'], $data['overview_features_hi'], $data['overview_features_gu'],
                $data['amenities_sections_en'], $data['amenities_sections_hi'], $data['amenities_sections_gu'],
                $data['floor_plans_dimensions'], $data['location_details'], $data['documents'], $data['images'], $data['brochure'],
                $data['id']
            ];
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } else {
            $sql = "INSERT INTO ni_featured_properties (
                property_id, category, status, title_en, title_hi, title_gu,
                location_en, location_hi, location_gu, price, price_unit,
                area, floor, parking, facing, description_en, description_hi, description_gu,
                overview_description_en, overview_description_hi, overview_description_gu,
                overview_features_en, overview_features_hi, overview_features_gu,
                amenities_sections_en, amenities_sections_hi, amenities_sections_gu,
                floor_plans_dimensions, location_details, documents, images, brochure
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['property_id'], $data['category'], $data['status'], $data['title_en'], $data['title_hi'], $data['title_gu'],
                $data['location_en'], $data['location_hi'], $data['location_gu'], $data['price'], $data['price_unit'],
                $data['area'], $data['floor'], $data['parking'], $data['facing'], $data['description_en'], $data['description_hi'], $data['description_gu'],
                $data['overview_description_en'], $data['overview_description_hi'], $data['overview_description_gu'],
                $data['overview_features_en'], $data['overview_features_hi'], $data['overview_features_gu'],
                $data['amenities_sections_en'], $data['amenities_sections_hi'], $data['amenities_sections_gu'],
                $data['floor_plans_dimensions'], $data['location_details'], $data['documents'], $data['images'], $data['brochure']
            ];
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }
    }

    public function deleteFeaturedProperty($id) {
        $stmt = $this->db->prepare("DELETE FROM ni_featured_properties WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Inquiries ---
    public function saveInquiry($data) {
        $fields = ['property_id', 'inquiry_type', 'name', 'email', 'phone', 'message', 'status'];
        
        try {
            if (isset($data['id']) && $data['id']) {
                // Update
                $setClause = [];
                $params = ['id' => $data['id']];
                
                foreach ($fields as $field) {
                    if (isset($data[$field])) {
                        $setClause[] = "$field = :$field";
                        $params[$field] = $data[$field];
                    }
                }
                
                if (empty($setClause)) {
                    return ['success' => false, 'error' => 'No data to update'];
                }

                $query = "UPDATE ni_schedule_visits SET " . implode(', ', $setClause) . " WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $data['id'], 'message' => 'Inquiry updated successfully'];
            } else {
                // Insert
                $insertFields = [];
                $placeholders = [];
                $params = [];
                
                foreach ($fields as $field) {
                    if (isset($data[$field])) {
                        $insertFields[] = $field;
                        $placeholders[] = ":$field";
                        $params[$field] = $data[$field];
                    }
                }

                // Default status if not provided
                if (!isset($params['status'])) {
                    $insertFields[] = 'status';
                    $placeholders[] = ':status';
                    $params['status'] = 'new';
                }

                // Default inquiry_type if not provided
                if (!isset($params['inquiry_type'])) {
                    $insertFields[] = 'inquiry_type';
                    $placeholders[] = ':inquiry_type';
                    $params['inquiry_type'] = 'General';
                }

                $query = "INSERT INTO ni_schedule_visits (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $this->db->lastInsertId(), 'message' => 'Inquiry added successfully'];
            }
        } catch (PDOException $e) {
            error_log("Error saving inquiry: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    public function getInquiries($category = null) {
        $sql = "SELECT * FROM ni_schedule_visits";
        if ($category) {
            $stmt = $this->db->prepare($sql . " WHERE category = ? ORDER BY submitted_at DESC");
            $stmt->execute([$category]);
        } else {
            $stmt = $this->db->query($sql . " ORDER BY submitted_at DESC");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateInquiryStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE ni_schedule_visits SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function deleteInquiry($id) {
        $stmt = $this->db->prepare("DELETE FROM ni_schedule_visits WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
