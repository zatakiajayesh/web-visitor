<?php
/**
 * User Model Class
 * Handles user-related database operations
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Register a new user
     */
    public function register($name, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $this->db->query("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'user')");
            $this->db->bind(':name', $name);
            $this->db->bind(':email', $email);
            $this->db->bind(':password', $hashedPassword);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'User registered successfully'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Authenticate user
     */
    public function login($email, $password) {
        $this->db->query("SELECT * FROM users WHERE email = :email AND is_active = 1");
        $this->db->bind(':email', $email);
        $user = $this->db->single();
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->db->query("UPDATE users SET last_login = NOW() WHERE id = :id");
            $this->db->bind(':id', $user['id']);
            $this->db->execute();
            
            return ['success' => true, 'user' => $user];
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $this->db->query("SELECT id, name, email, role, is_active, last_login, created_at FROM users WHERE id = :id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->single();
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($id, $name, $email) {
        $this->db->query("UPDATE users SET name = :name, email = :email WHERE id = :id");
        $this->db->bind(':name', $name);
        $this->db->bind(':email', $email);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        
        return $this->db->execute();
    }
    
    /**
     * Change password
     */
    public function changePassword($id, $oldPassword, $newPassword) {
        $user = $this->getUserById($id);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Verify old password
        if (!password_verify($oldPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid current password'];
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->db->query("UPDATE users SET password = :password WHERE id = :id");
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        
        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to change password'];
    }
    
    /**
     * Get all users (admin only)
     */
    public function getAllUsers($limit = 50, $offset = 0) {
        $this->db->query("
            SELECT id, name, email, role, is_active, last_login, created_at 
            FROM users 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $this->db->query("SELECT id FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->single() !== false;
    }
}
