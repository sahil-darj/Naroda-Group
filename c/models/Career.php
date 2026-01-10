<?php
require_once '../config/database.php';

class Career {
    private $conn;
    private $table_jobs = 'career_jobs';
    private $table_applications = 'career_applications';
    private $table_departments = 'career_departments';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // --- JOBS ---

    public function getAllJobs($search = null, $department = null, $status = null, $type = null) {
        $query = "SELECT * FROM {$this->table_jobs} WHERE 1=1";
        $params = [];

        if ($search) {
            $query .= " AND (title_en LIKE :search OR title_hi LIKE :search OR title_gu LIKE :search OR description_en LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        if ($department && $department !== 'All Departments' && $department !== '') {
            $query .= " AND department = :department";
            $params['department'] = $department;
        }
        if ($status && $status !== 'All Status' && $status !== '') {
            $query .= " AND status = :status";
            $params['status'] = $status;
        }
        if ($type && $type !== 'All Types' && $type !== '') {
            $query .= " AND type = :type";
            $params['type'] = $type;
        }

        $query .= " ORDER BY created_at DESC";

        try {
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add applications count to each job
            foreach ($jobs as &$job) {
                $countQuery = "SELECT COUNT(*) as count FROM {$this->table_applications} WHERE job_id = :job_id";
                $countStmt = $this->conn->prepare($countQuery);
                $countStmt->execute(['job_id' => $job['id']]);
                $result = $countStmt->fetch(PDO::FETCH_ASSOC);
                $job['applications'] = $result['count'];
            }
            return $jobs;
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getJobById($id) {
        $query = "SELECT * FROM {$this->table_jobs} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return null;
        }
    }

    public function saveJob($data) {
        try {
            $fields = [
                'title_en', 'title_hi', 'title_gu',
                'description_en', 'description_hi', 'description_gu',
                'requirements_en', 'requirements_hi', 'requirements_gu',
                'benefits_en', 'benefits_hi', 'benefits_gu',
                'department', 'type',
                'location_en', 'location_hi', 'location_gu',
                'experience_en', 'experience_hi', 'experience_gu',
                'salary_en', 'salary_hi', 'salary_gu',
                'vacancies', 'status', 'deadline', 'posted_date'
            ];

            // Filter data to only include valid fields
            $validData = array_intersect_key($data, array_flip($fields));

            if (isset($data['id']) && !empty($data['id'])) {
                // UPDATE
                $setClause = [];
                $params = ['id' => $data['id']];
                foreach ($validData as $key => $value) {
                    $setClause[] = "$key = :$key";
                    $params[$key] = $value;
                }
                
                if (empty($setClause)) return ['success' => false, 'message' => 'No data to update'];

                $query = "UPDATE {$this->table_jobs} SET " . implode(', ', $setClause) . " WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                return ['success' => true, 'id' => $data['id'], 'message' => 'Job updated successfully'];
            } else {
                // INSERT
                $cols = implode(', ', array_keys($validData));
                $placeholders = ':' . implode(', :', array_keys($validData));
                
                $query = "INSERT INTO {$this->table_jobs} ($cols) VALUES ($placeholders)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($validData);
                return ['success' => true, 'id' => $this->conn->lastInsertId(), 'message' => 'Job added successfully'];
            }
        } catch(PDOException $e) {
             return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteJob($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table_jobs} WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return ['success' => true];
        } catch(PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // --- DEPARTMENTS ---

    public function getAllDepartments($search = null) {
        $query = "SELECT * FROM {$this->table_departments} WHERE 1=1";
        $params = [];
        if ($search) {
             $query .= " AND (name_en LIKE :search OR name_hi LIKE :search OR name_gu LIKE :search)";
             $params['search'] = "%{$search}%";
        }
        $query .= " ORDER BY name_en ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $val) {
                 $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate open positions and employees (mock logic or actual if stored, using mock for now based on jobs)
            foreach ($departments as &$dept) {
                // Count open positions (active jobs with this department slug)
                 if (isset($dept['slug'])) {
                    $jobCountParams = ['dept' => $dept['slug']];
                    $jobCountQuery = "SELECT COUNT(*) as count FROM {$this->table_jobs} WHERE department = :dept AND status = 'active'";
                    $jobStmt = $this->conn->prepare($jobCountQuery);
                    $jobStmt->execute($jobCountParams);
                    $dept['openPositions'] = $jobStmt->fetch(PDO::FETCH_ASSOC)['count'];
                 } else {
                    $dept['openPositions'] = 0;
                 }
                 $dept['totalEmployees'] = 0; // Placeholder
            }
            return $departments;
        } catch(PDOException $e) {
            return [];
        }
    }

    public function saveDepartment($data) {
        try {
             $fields = [
                 'slug', 'name_en', 'name_hi', 'name_gu',
                 'head', 'description_en', 'description_hi', 'description_gu', 'status'
             ];
             $validData = array_intersect_key($data, array_flip($fields));

             if (isset($data['id']) && !empty($data['id'])) {
                 $setClause = [];
                 $params = ['id' => $data['id']];
                 foreach ($validData as $key => $value) {
                     $setClause[] = "$key = :$key";
                     $params[$key] = $value;
                 }
                 $query = "UPDATE {$this->table_departments} SET " . implode(', ', $setClause) . " WHERE id = :id";
                 $stmt = $this->conn->prepare($query);
                 $stmt->execute($params);
                 return ['success' => true, 'id' => $data['id']];
             } else {
                 $cols = implode(', ', array_keys($validData));
                 $placeholders = ':' . implode(', :', array_keys($validData));
                 $query = "INSERT INTO {$this->table_departments} ($cols) VALUES ($placeholders)";
                 $stmt = $this->conn->prepare($query);
                 $stmt->execute($validData);
                 return ['success' => true, 'id' => $this->conn->lastInsertId()];
             }
        } catch(PDOException $e) {
             return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteDepartment($id) {
         try {
             $stmt = $this->conn->prepare("DELETE FROM {$this->table_departments} WHERE id = :id");
             $stmt->execute(['id' => $id]);
             return ['success' => true];
         } catch(PDOException $e) {
             return ['success' => false, 'error' => $e->getMessage()];
         }
    }

    // --- APPLICATIONS ---

    public function getAllApplications($search = null, $status = null, $job = null) {
        $query = "SELECT a.*, j.title_en as job_title FROM {$this->table_applications} a 
                  LEFT JOIN {$this->table_jobs} j ON a.job_id = j.id 
                  WHERE 1=1";
        $params = [];

        if ($search) {
            $query .= " AND (a.name LIKE :search OR a.email LIKE :search)";
             $params['search'] = "%{$search}%";
        }
        if ($status && $status !== 'All Status' && $status !== '') {
            $query .= " AND a.status = :status";
            $params['status'] = $status;
        }
        
        $query .= " ORDER BY a.applied_date DESC";

        try {
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $val) {
                 $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function saveApplicationAndStatus($id, $status) {
         try {
             $stmt = $this->conn->prepare("UPDATE {$this->table_applications} SET status = :status WHERE id = :id");
             $stmt->execute(['status' => $status, 'id' => $id]);
             return ['success' => true];
         } catch(PDOException $e) {
              return ['success' => false, 'error' => $e->getMessage()];
         }
    }

    public function deleteApplication($id) {
         try {
             $stmt = $this->conn->prepare("DELETE FROM {$this->table_applications} WHERE id = :id");
             $stmt->execute(['id' => $id]);
             return ['success' => true];
         } catch(PDOException $e) {
             return ['success' => false, 'error' => $e->getMessage()];
         }
    }
}
?>
