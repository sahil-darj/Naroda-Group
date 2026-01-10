<?php
require_once '../config/database.php';

class Team {
    private $conn;
    private $table_team = 'team_members';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all team members
    public function getAllMembers($search = null, $page = 1, $perPage = 10, $department = null) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->table_team} WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $query .= " AND (firstname_en LIKE :search OR lastname_en LIKE :search OR email LIKE :search OR role_en LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($department && $department !== 'All Departments') {
             $query .= " AND department = :department";
             $params['department'] = $department;
        }
        
        $query .= " ORDER BY id DESC LIMIT :offset, :limit";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val, PDO::PARAM_STR);
            }
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            
            $stmt->execute();
            $members = $stmt->fetchAll();
            
            // Get total count
            $totalStmt = $this->conn->query("SELECT FOUND_ROWS() as total");
            $totalResult = $totalStmt->fetch();
            $total = $totalResult['total'];
            
            return [
                'members' => $members,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($total / $perPage)
            ];
            
        } catch(PDOException $e) {
            error_log("Error getting team members: " . $e->getMessage());
            return [
                'members' => [],
                'total' => 0,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    // Get single member by ID
    public function getMemberById($id) {
        $query = "SELECT * FROM {$this->table_team} WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Error getting team member: " . $e->getMessage());
            return null;
        }
    }

    // Save member (Create or Update)
    public function saveMember($data) {
        try {
            // Fields to handle
            $fields = [
                'firstname_en', 'lastname_en', 'role_en', 'bio_en', 'professional_background_en', 'achievements_en', 'education_en',
                'firstname_hi', 'lastname_hi', 'role_hi', 'bio_hi', 'professional_background_hi', 'achievements_hi', 'education_hi',
                'firstname_gu', 'lastname_gu', 'role_gu', 'bio_gu', 'professional_background_gu', 'achievements_gu', 'education_gu',
                'email', 'phone', 'department', 'experience', 'status', 'linkedin_url', 'twitter_url', 'avatar'
            ];

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

                $query = "UPDATE {$this->table_team} SET " . implode(', ', $setClause) . " WHERE id = :id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $data['id'], 'message' => 'Team member updated successfully'];

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
                
                // Add created_at
                $insertFields[] = 'created_at';
                $placeholders[] = 'NOW()';

                $query = "INSERT INTO {$this->table_team} (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $this->conn->lastInsertId(), 'message' => 'Team member added successfully'];
            }
        } catch(PDOException $e) {
            error_log("Error saving team member: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Delete member
    public function deleteMember($id) {
        $query = "DELETE FROM {$this->table_team} WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return ['success' => true, 'message' => 'Team member deleted successfully'];
        } catch(PDOException $e) {
            error_log("Error deleting team member: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
