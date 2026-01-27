<?php
/**
 * Naroda Landmark Model
 * Handles CRUD operations for all 5 sections:
 * - Floor Plans
 * - Gallery
 * - Pricing (Office & Retail)
 * - Featured Properties
 * - Inquiries
 */

require_once __DIR__ . '/../config/database.php';

class NarodaLandmark {
    private $conn;
    private $table_floor_plans = 'landmark_floor_plans';
    private $table_gallery = 'landmark_gallery';
    private $table_pricing = 'landmark_pricing';
    private $table_properties = 'landmark_properties';
    private $table_inquiries = 'landmark_inquiries';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Try to increase max_allowed_packet for this session if possible
        try {
            $this->conn->exec("SET SESSION max_allowed_packet=16777216"); // 16MB
        } catch (PDOException $e) {
            // Might fail if user doesn't have privileges, ignore
        }
    }

    // ========================================
    // FLOOR PLANS METHODS
    // ========================================

    public function getAllFloorPlans() {
        $query = "SELECT * FROM {$this->table_floor_plans} ORDER BY id DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting floor plans: " . $e->getMessage());
            return [];
        }
    }

    public function getFloorPlanById($id) {
        $query = "SELECT * FROM {$this->table_floor_plans} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting floor plan: " . $e->getMessage());
            return null;
        }
    }

    public function saveFloorPlan($data) {
        try {
            $fields = [
                'floor_type', 'office_size', 'shops_per_floor', 'elevators', 'total_floors',
                'description_en', 'description_hi', 'description_gu', 'image_url', 'status'
            ];

            if (!empty($data['id'])) {
                // Update existing
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

                $query = "UPDATE {$this->table_floor_plans} SET " . implode(', ', $setClause) . " WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $data['id'], 'message' => 'Floor plan updated successfully'];
            } else {
                // Insert new
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

                $query = "INSERT INTO {$this->table_floor_plans} (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $this->conn->lastInsertId(), 'message' => 'Floor plan added successfully'];
            }
        } catch (PDOException $e) {
            error_log("Error saving floor plan: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteFloorPlan($id) {
        $query = "DELETE FROM {$this->table_floor_plans} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return ['success' => true, 'message' => 'Floor plan deleted successfully'];
        } catch (PDOException $e) {
            error_log("Error deleting floor plan: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========================================
    // GALLERY METHODS
    // ========================================

    public function getAllGallery() {
        $query = "SELECT * FROM {$this->table_gallery} ORDER BY display_order ASC, id DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting gallery: " . $e->getMessage());
            return [];
        }
    }

    public function getGalleryById($id) {
        $query = "SELECT * FROM {$this->table_gallery} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting gallery image: " . $e->getMessage());
            return null;
        }
    }

    public function saveGallery($data) {
        try {
            $fields = [
                'title_en', 'title_hi', 'title_gu',
                'description_en', 'description_hi', 'description_gu',
                'image_url', 'category', 'status', 'display_order'
            ];

            if (!empty($data['id'])) {
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

                $query = "UPDATE {$this->table_gallery} SET " . implode(', ', $setClause) . " WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $data['id'], 'message' => 'Gallery image updated successfully'];
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

                $query = "INSERT INTO {$this->table_gallery} (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $this->conn->lastInsertId(), 'message' => 'Gallery image added successfully'];
            }
        } catch (PDOException $e) {
            error_log("Error saving gallery: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteGallery($id) {
        $query = "DELETE FROM {$this->table_gallery} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return ['success' => true, 'message' => 'Gallery image deleted successfully'];
        } catch (PDOException $e) {
            error_log("Error deleting gallery: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========================================
    // PRICING METHODS (Office & Retail)
    // ========================================

    public function getPricing($type = null) {
        $query = "SELECT * FROM {$this->table_pricing}";
        $params = [];
        
        if ($type) {
            $query .= " WHERE pricing_type = :type";
            $params['type'] = $type;
        }
        
        $query .= " ORDER BY id DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting pricing: " . $e->getMessage());
            return [];
        }
    }

    public function getPricingById($id) {
        $query = "SELECT * FROM {$this->table_pricing} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting pricing: " . $e->getMessage());
            return null;
        }
    }

    public function savePricing($data) {
        try {
            $fields = [
                'pricing_type', 'rental_price', 'rental_price_hi', 'rental_price_gu',
                'features_en', 'features_hi', 'features_gu', 'status'
            ];

            if (!empty($data['id'])) {
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

                $query = "UPDATE {$this->table_pricing} SET " . implode(', ', $setClause) . " WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $data['id'], 'message' => 'Pricing updated successfully'];
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

                $query = "INSERT INTO {$this->table_pricing} (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $this->conn->lastInsertId(), 'message' => 'Pricing added successfully'];
            }
        } catch (PDOException $e) {
            error_log("Error saving pricing: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deletePricing($id) {
        $query = "DELETE FROM {$this->table_pricing} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return ['success' => true, 'message' => 'Pricing deleted successfully'];
        } catch (PDOException $e) {
            error_log("Error deleting pricing: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========================================
    // FEATURED PROPERTIES METHODS
    // ========================================

    public function getProperties($category = null) {
        $query = "SELECT * FROM {$this->table_properties}";
        $params = [];
        
        if ($category) {
            $query .= " WHERE category = :category";
            $params['category'] = $category;
        }
        
        $query .= " ORDER BY id DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $properties = $stmt->fetchAll();
            
            // Decode JSON fields
            $jsonFields = ['images', 'brochure', 'property_documents', 'approvals_documents', 'room_dimensions', 'amenities', 'overview_features_en', 'overview_features_hi', 'overview_features_gu'];
            foreach ($properties as &$property) {
                // Decode features if they are JSON
                foreach (['features_en', 'features_hi', 'features_gu'] as $f) {
                    if (!empty($property[$f]) && (strpos($property[$f], '[') === 0 || strpos($property[$f], '{') === 0)) {
                        $property[$f] = json_decode($property[$f], true);
                    }
                }
                
                foreach ($jsonFields as $field) {
                    if (!empty($property[$field])) {
                        $decoded = json_decode($property[$field], true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $property[$field] = $decoded;
                        } else {
                            // Fallback: treat as comma-separated string if simple string
                            $property[$field] = is_string($property[$field]) ? array_values(array_filter(array_map('trim', explode(',', $property[$field])))) : [];
                        }
                    } else {
                        $property[$field] = in_array($field, ['images', 'property_documents', 'approvals_documents', 'room_dimensions', 'amenities', 'overview_features_en', 'overview_features_hi', 'overview_features_gu']) ? [] : null;
                    }
                }
            }
            
            return $properties;
        } catch (PDOException $e) {
            error_log("Error getting properties: " . $e->getMessage());
            return [];
        }
    }

    public function getPropertyById($id) {
        $query = "SELECT * FROM {$this->table_properties} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            $property = $stmt->fetch();
            
            // Decode JSON fields
            if ($property) {
                // Decode features if they are JSON
                foreach (['features_en', 'features_hi', 'features_gu'] as $f) {
                    if (!empty($property[$f]) && (strpos($property[$f], '[') === 0 || strpos($property[$f], '{') === 0)) {
                        $property[$f] = json_decode($property[$f], true);
                    }
                }

                $jsonFields = ['images', 'brochure', 'property_documents', 'approvals_documents', 'room_dimensions', 'amenities', 'overview_features_en', 'overview_features_hi', 'overview_features_gu'];
                foreach ($jsonFields as $field) {
                    if (!empty($property[$field])) {
                        $decoded = json_decode($property[$field], true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $property[$field] = $decoded;
                        } else {
                            // Fallback: treat as comma-separated string if simple string
                            $property[$field] = is_string($property[$field]) ? array_values(array_filter(array_map('trim', explode(',', $property[$field])))) : [];
                        }
                    } else {
                        $property[$field] = in_array($field, ['images', 'property_documents', 'approvals_documents', 'room_dimensions', 'amenities', 'overview_features_en', 'overview_features_hi', 'overview_features_gu']) ? [] : null;
                    }
                }
            }
            
            return $property;
        } catch (PDOException $e) {
            error_log("Error getting property: " . $e->getMessage());
            return null;
        }
    }

    public function saveProperty($data) {
        try {
            $fields = [
                'category',
                'title_en', 'title_hi', 'title_gu',
                'location_en', 'location_hi', 'location_gu',
                'type_en', 'type_hi', 'type_gu',
                'description_en', 'description_hi', 'description_gu',
                'features_en', 'features_hi', 'features_gu',
                'overview_features_en', 'overview_features_hi', 'overview_features_gu',
                'area', 'floor', 'parking', 'status',
                'price', 'price_unit', 'property_id', 'facing', 'images',
                'brochure', 'property_documents', 'approvals_documents', 'room_dimensions', 
                'map_iframe', 'amenities', 'floor_plan_image'
            ];

            // Handle JSON fields
            $jsonFields = ['images', 'brochure', 'property_documents', 'approvals_documents', 'room_dimensions', 'amenities', 'overview_features_en', 'overview_features_hi', 'overview_features_gu'];
            foreach ($jsonFields as $field) {
                if (isset($data[$field]) && is_array($data[$field])) {
                    $token = $data[$field]; // Just a reference
                    $data[$field] = json_encode($data[$field]);
                }
            }

            if (!empty($data['id'])) {
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

                $query = "UPDATE {$this->table_properties} SET " . implode(', ', $setClause) . " WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $data['id'], 'message' => 'Property updated successfully'];
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

                $query = "INSERT INTO {$this->table_properties} (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $this->conn->lastInsertId(), 'message' => 'Property added successfully'];
            }
        } catch (PDOException $e) {
            error_log("Error saving property: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteProperty($id) {
        $query = "DELETE FROM {$this->table_properties} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return ['success' => true, 'message' => 'Property deleted successfully'];
        } catch (PDOException $e) {
            error_log("Error deleting property: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========================================
    // INQUIRIES METHODS
    // ========================================

    public function getInquiries($type = null) {
        $query = "SELECT i.*, 
                         COALESCE(p.title_en, 'General Project Visit') as property_title, 
                         COALESCE(p.category, 'Sale') as category 
                  FROM {$this->table_inquiries} i 
                  LEFT JOIN {$this->table_properties} p ON i.property_id = p.id";
        $params = [];
        
        if ($type) {
            $query .= " WHERE i.inquiry_type = :type";
            $params['type'] = $type;
        }
        
        $query .= " ORDER BY i.created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting inquiries: " . $e->getMessage());
            return [];
        }
    }

    public function getInquiryById($id) {
        $query = "SELECT i.*, p.title_en as property_title 
                  FROM {$this->table_inquiries} i 
                  LEFT JOIN {$this->table_properties} p ON i.property_id = p.id 
                  WHERE i.id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting inquiry: " . $e->getMessage());
            return null;
        }
    }

    public function saveInquiry($data) {
        try {
            $fields = ['inquiry_type', 'property_id', 'name', 'email', 'phone', 'message', 'status'];

            if (!empty($data['id'])) {
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

                $query = "UPDATE {$this->table_inquiries} SET " . implode(', ', $setClause) . " WHERE id = :id";
                $stmt = $this->conn->prepare($query);
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

                $query = "INSERT INTO {$this->table_inquiries} (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $this->conn->lastInsertId(), 'message' => 'Inquiry added successfully'];
            }
        } catch (PDOException $e) {
            error_log("Error saving inquiry: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateInquiryStatus($id, $status) {
        $query = "UPDATE {$this->table_inquiries} SET status = :status WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id, 'status' => $status]);
            return ['success' => true, 'message' => 'Inquiry status updated successfully'];
        } catch (PDOException $e) {
            error_log("Error updating inquiry status: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteInquiry($id) {
        $query = "DELETE FROM {$this->table_inquiries} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return ['success' => true, 'message' => 'Inquiry deleted successfully'];
        } catch (PDOException $e) {
            error_log("Error deleting inquiry: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
