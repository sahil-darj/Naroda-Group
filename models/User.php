<?php
require_once '../config/database.php';

class User {
    private $conn;
    private $table_users = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all users
    public function getAllUsers($search = null, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->table_users} WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $query .= " AND (firstname LIKE :search OR lastname LIKE :search OR email LIKE :search OR phone LIKE :search)";
            $params['search'] = "%{$search}%";
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
            $users = $stmt->fetchAll();
            
            // Get total count
            $totalStmt = $this->conn->query("SELECT FOUND_ROWS() as total");
            $totalResult = $totalStmt->fetch();
            $total = $totalResult['total'];
            
            return [
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($total / $perPage)
            ];
            
        } catch(PDOException $e) {
            error_log("Error getting users: " . $e->getMessage());
            return [
                'users' => [],
                'total' => 0,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    // Get single user by ID
    public function getUserById($id) {
        $query = "SELECT * FROM {$this->table_users} WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                unset($user['password']); // Remove password for security
            }
            return $user;
        } catch(PDOException $e) {
            error_log("Error getting user: " . $e->getMessage());
            return null;
        }
    }

    // Get user by email
    public function getUserByEmail($email) {
        $query = "SELECT * FROM {$this->table_users} WHERE email = :email LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                unset($user['password']); // Remove password for security
            }
            return $user;
        } catch(PDOException $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return null;
        }
    }

    // Check if email exists
    public function checkEmailExists($email) {
        $query = "SELECT id FROM {$this->table_users} WHERE email = :email LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['email' => $email]);
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            error_log("Error checking email: " . $e->getMessage());
            return false;
        }
    }

    // Save user (Create or Update)
    public function saveUser($data) {
        try {
            if (isset($data['id']) && $data['id']) {
                // Update
                $query = "UPDATE {$this->table_users} SET 
                            firstname = :firstname,
                            lastname = :lastname,
                            firstname_hi = :firstname_hi,
                            lastname_hi = :lastname_hi,
                            firstname_gu = :firstname_gu,
                            lastname_gu = :lastname_gu,
                            email = :email,
                            phone = :phone,
                            gender = :gender,
                            role = :role,
                            status = :status,
                            address = :address"; // Password logic handled separately or only if provided
                
                $params = [
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'firstname_hi' => $data['firstname_hi'] ?? '',
                    'lastname_hi' => $data['lastname_hi'] ?? '',
                    'firstname_gu' => $data['firstname_gu'] ?? '',
                    'lastname_gu' => $data['lastname_gu'] ?? '',
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'gender' => $data['gender'],
                    'role' => $data['role'],
                    'status' => $data['status'],
                    'address' => $data['address'] ?? '',
                    'id' => $data['id']
                ];

                if (!empty($data['password'])) {
                    $query .= ", password = :password";
                    $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                }

                if (!empty($data['avatar'])) {
                    $query .= ", avatar = :avatar";
                    $params['avatar'] = $data['avatar'];
                }

                $query .= " WHERE id = :id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $data['id'], 'message' => 'User updated successfully'];

            } else {
                // Insert
                $query = "INSERT INTO {$this->table_users} 
                            (firstname, lastname, firstname_hi, lastname_hi, firstname_gu, lastname_gu, 
                             email, phone, gender, role, status, password, address, avatar, created_at)
                          VALUES 
                            (:firstname, :lastname, :firstname_hi, :lastname_hi, :firstname_gu, :lastname_gu,
                             :email, :phone, :gender, :role, :status, :password, :address, :avatar, NOW())";
                
                $params = [
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'firstname_hi' => $data['firstname_hi'] ?? '',
                    'lastname_hi' => $data['lastname_hi'] ?? '',
                    'firstname_gu' => $data['firstname_gu'] ?? '',
                    'lastname_gu' => $data['lastname_gu'] ?? '',
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'gender' => $data['gender'],
                    'role' => $data['role'],
                    'status' => $data['status'],
                    'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                    'address' => $data['address'] ?? '',
                    'avatar' => $data['avatar'] ?? ''
                ];
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                
                return ['success' => true, 'id' => $this->conn->lastInsertId(), 'message' => 'User created successfully'];
            }
        } catch(PDOException $e) {
            error_log("Error saving user: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Delete user
    public function deleteUser($id) {
        $query = "DELETE FROM {$this->table_users} WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch(PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Login
    public function login($email, $password) {
        $query = "SELECT * FROM {$this->table_users} WHERE email = :email LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($password, $row['password'])) {
                    // Remove password from returned data
                    unset($row['password']);
                    return ['success' => true, 'user' => $row];
                } else {
                    return ['success' => false, 'message' => 'Invalid password'];
                }
            } else {
                return ['success' => false, 'message' => 'User not found'];
            }
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Register new user
    public function register($data) {
        try {
            // Check if email already exists
            if ($this->checkEmailExists($data['email'])) {
                return ['success' => false, 'message' => 'Email already registered'];
            }
            
            // Generate avatar initials
            $avatar = $this->getInitials($data['firstname'], $data['lastname']);
            
            $query = "INSERT INTO {$this->table_users} 
                        (firstname, lastname, email, phone, password, avatar, role, status, created_at)
                      VALUES 
                        (:firstname, :lastname, :email, :phone, :password, :avatar, :role, :status, NOW())";
            
            $params = [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? '',
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'avatar' => $avatar,
                'role' => 'user', // Default role
                'status' => 'active' // Default status
            ];
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $userId = $this->conn->lastInsertId();
            
            // Get the created user data
            $userData = $this->getUserById($userId);
            
            return ['success' => true, 'user' => $userData, 'message' => 'Registration successful'];
            
        } catch(PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Generate OTP for forgot password (OTP is generated but NOT stored in database)
    public function generateForgotPasswordOTP($email) {
        try {
            // Check if user exists
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return ['success' => false, 'message' => 'Email not found'];
            }
            
            // Generate 6-digit OTP
            $otp = rand(100000, 999999);
            
            // OTP is NOT stored in database, just returned for sending via email
            return ['success' => true, 'otp' => $otp, 'user' => $user];
            
        } catch(PDOException $e) {
            error_log("OTP generation error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Reset password (direct reset without OTP verification in DB)
    public function resetPassword($email, $newPassword) {
        try {
            $query = "UPDATE {$this->table_users} 
                      SET password = :password
                      WHERE email = :email";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'email' => $email
            ]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Password reset successfully'];
            }
            
            return ['success' => false, 'message' => 'User not found'];
            
        } catch(PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Get initials for avatar
    private function getInitials($firstName, $lastName) {
        $initials = '';
        if (!empty($firstName)) {
            $initials .= strtoupper(substr($firstName, 0, 1));
        }
        if (!empty($lastName)) {
            $initials .= strtoupper(substr($lastName, 0, 1));
        }
        return !empty($initials) ? $initials : 'U';
    }

    // Update user profile
    public function updateProfile($userId, $data) {
        try {
            $query = "UPDATE {$this->table_users} SET ";
            $params = [];
            $fields = [];
            
            if (isset($data['firstname'])) {
                $fields[] = "firstname = :firstname";
                $params['firstname'] = $data['firstname'];
            }
            
            if (isset($data['lastname'])) {
                $fields[] = "lastname = :lastname";
                $params['lastname'] = $data['lastname'];
            }
            
            if (isset($data['phone'])) {
                $fields[] = "phone = :phone";
                $params['phone'] = $data['phone'];
            }
            
            if (isset($data['avatar'])) {
                $fields[] = "avatar = :avatar";
                $params['avatar'] = $data['avatar'];
            }
            
            if (isset($data['address'])) {
                $fields[] = "address = :address";
                $params['address'] = $data['address'];
            }
            
            if (empty($fields)) {
                return ['success' => false, 'message' => 'No data to update'];
            }
            
            $query .= implode(', ', $fields);
            $query .= " WHERE id = :id";
            $params['id'] = $userId;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return ['success' => true, 'message' => 'Profile updated successfully'];
            
        } catch(PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Change password
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // First verify current password
            $query = "SELECT password FROM {$this->table_users} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Update to new password
            $updateQuery = "UPDATE {$this->table_users} 
                           SET password = :password 
                           WHERE id = :id";
            
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'id' => $userId
            ]);
            
            return ['success' => true, 'message' => 'Password changed successfully'];
            
        } catch(PDOException $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>