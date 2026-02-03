<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Validation.php';

class AuthController {
    private $db;
    private $session;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->session = new Session();
    }
    
public function register($data) {
    $validation = new Validation();
    
    // ✅ Validation rules
    $rules = [
        'username' => 'required|min:3|max:50|unique:users',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
        // 👇 Phone: required + only numbers + exactly 10 digits + unique
        'phone' => 'required|regex:/^[0-9]{10}$/|unique:users',
        'confirm_password' => 'required|matches:password'
    ];

    // ❌ Validation failed
    if (!$validation->validate($data, $rules)) {
        return [
            'success' => false,
            'errors'  => $validation->errors()
        ];
    }

    // 🔐 Password hash
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    // 🧾 Insert query
    $stmt = $this->db->prepare("
        INSERT INTO users (username, email, password, phone, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");

    try {
        $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['phone']
        ]);

        $userId = $this->db->lastInsertId();

        // 🔁 Auto login after registration
        $this->login([
            'email' => $data['email'],
            'password' => $data['password'],
            'remember' => false
        ]);

        return [
            'success' => true,
            'message' => 'Registration successful!'
        ];

    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Registration failed. Please try again.'
            // production me $e->getMessage() mat dikhana
        ];
    }
}

    
    public function login($data) {
        $validation = new Validation();
        
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];
        
        if (!$validation->validate($data, $rules)) {
            return ['success' => false, 'errors' => $validation->errors()];
        }
        
        // Check user exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($data['password'], $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Check if banned
        if ($user['is_banned']) {
            $this->session->set('banned_user', [
                'username' => $user['username'],
                'ban_reason' => $user['ban_reason']
            ]);
            return ['success' => false, 'banned' => true, 'reason' => $user['ban_reason']];
        }
        
        // Set session
        $this->session->set('user_id', $user['id']);
        $this->session->set('username', $user['username']);
        $this->session->set('role', $user['role']);
        $this->session->set('logged_in', true);
        
        // Update last login
        $updateStmt = $this->db->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        
        return ['success' => true, 'role' => $user['role']];
    }
    
    public function logout() {
        $this->session->destroy();
        return ['success' => true];
    }
    
    public function checkAuth() {
        return $this->session->get('logged_in', false);
    }
    
    public function getCurrentUser() {
        if (!$this->checkAuth()) {
            return null;
        }
        
        $userId = $this->session->get('user_id');
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function isAdmin() {
        return $this->session->get('role') === 'admin';
    }
}
?>