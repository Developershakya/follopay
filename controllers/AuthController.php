<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Validation.php';
require_once __DIR__ . '/../helpers/EmailService.php';

class AuthController {
    private $db;
    private $session;
    private $emailService;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->session = new Session();
        $this->emailService = new EmailService();
    }
    
    public function register($data) {
        $validation = new Validation();
        
        // Validation rules
        $rules = [
            'username' => 'required|min:3|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'required|regex:/^[0-9]{10}$/|unique:users',
            'confirm_password' => 'required|matches:password'
        ];

        if (!$validation->validate($data, $rules)) {
            return [
                'success' => false,
                'errors'  => $validation->errors()
            ];
        }

        // Generate OTP
        $otp = $this->generateOTP();
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Store temporary data with OTP
        $stmt = $this->db->prepare("
            INSERT INTO user_otp_verification (email, username, password, phone, otp, otp_expires_at, created_at) 
            VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())
        ");

        try {
            $stmt->execute([
                $data['email'],
                $data['username'],
                $hashedPassword,
                $data['phone'],
                $otp
            ]);

            // Send OTP via email
            $emailSent = $this->emailService->sendOTPEmail($data['email'], $data['username'], $otp);
            
            if (!$emailSent) {
                return [
                    'success' => false,
                    'message' => 'Failed to send OTP. Please try again.'
                ];
            }

            return [
                'success' => true,
                'message' => 'OTP sent to your email. Please verify to complete registration.',
                'redirect_to' => 'verify-otp',
                'email' => $data['email']
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }

    public function verifyOTP($data) {
        $validation = new Validation();
        
        $rules = [
            'email' => 'required|email',
            'otp' => 'required|regex:/^[0-9]{6}$/'
        ];
        
        if (!$validation->validate($data, $rules)) {
            return ['success' => false, 'errors' => $validation->errors()];
        }
        
        // Find OTP record
        $stmt = $this->db->prepare("
            SELECT * FROM user_otp_verification 
            WHERE email = ? AND otp = ? AND otp_expires_at > NOW()
        ");
        $stmt->execute([$data['email'], $data['otp']]);
        $record = $stmt->fetch();
        
        if (!$record) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP. Please try again or request a new one.'
            ];
        }
        
        // Insert user into users table
        $userStmt = $this->db->prepare("
            INSERT INTO users (username, email, password, phone, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        try {
            $userStmt->execute([
                $record['username'],
                $record['email'],
                $record['password'],
                $record['phone']
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Delete OTP record
            $deleteStmt = $this->db->prepare("DELETE FROM user_otp_verification WHERE id = ?");
            $deleteStmt->execute([$record['id']]);
            
            // Auto login
            $this->session->set('user_id', $userId);
            $this->session->set('username', $record['username']);
            $this->session->set('role', 'user');
            $this->session->set('logged_in', true);
            
            return [
                'success' => true,
                'message' => 'Account created successfully! Welcome to EarnApp'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ];
        }
    }

    public function refreshOTP($email) {
        // Check if email exists in pending verification
        $stmt = $this->db->prepare("
            SELECT * FROM user_otp_verification WHERE email = ?
        ");
        $stmt->execute([$email]);
        $record = $stmt->fetch();
        
        if (!$record) {
            return [
                'success' => false,
                'message' => 'Email not found in registration process.'
            ];
        }
        
        // Generate new OTP
        $newOtp = $this->generateOTP();
        
        // Update OTP
        $updateStmt = $this->db->prepare("
            UPDATE user_otp_verification 
            SET otp = ?, otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
            WHERE id = ?
        ");
        
        try {
            $updateStmt->execute([$newOtp, $record['id']]);
            
            // Send new OTP
            $emailSent = $this->emailService->sendOTPEmail($email, $record['username'], $newOtp);
            
            if (!$emailSent) {
                return [
                    'success' => false,
                    'message' => 'Failed to send OTP.'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'New OTP sent to your email.'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to refresh OTP.'
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
            return [
                'success' => false,
                'banned' => true,
                'reason' => $user['ban_reason']
            ];
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

    public function forgotPassword($email) {
        $validation = new Validation();
        
        if (!$validation->validate(['email' => $email], ['email' => 'required|email'])) {
            return [
                'success' => false,
                'message' => 'Please provide a valid email.'
            ];
        }
        
        // Check if user exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Don't reveal if email exists (security)
            return [
                'success' => true,
                'message' => 'If this email exists, you will receive password reset instructions.'
            ];
        }
        
        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $resetToken);
        
        // Store reset token
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_reset_token = ?, password_reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
            WHERE id = ?
        ");
        
        try {
            $stmt->execute([$hashedToken, $user['id']]);
            
            // Send reset email
            $emailSent = $this->emailService->sendPasswordResetEmail($email, $user['username'], $resetToken);
            
            if (!$emailSent) {
                return [
                    'success' => true,
                    'message' => 'If this email exists, you will receive password reset instructions.'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'If this email exists, you will receive password reset instructions.'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => true,
                'message' => 'If this email exists, you will receive password reset instructions.'
            ];
        }
    }

    public function resetPassword($data) {
        $validation = new Validation();
        
        $rules = [
            'token' => 'required',
            'password' => 'required|min:6',
            'confirm_password' => 'required|matches:password'
        ];
        
        if (!$validation->validate($data, $rules)) {
            return [
                'success' => false,
                'errors' => $validation->errors()
            ];
        }
        
        // Verify token
        $hashedToken = hash('sha256', $data['token']);
        
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE password_reset_token = ? AND password_reset_expires > NOW()
        ");
        $stmt->execute([$hashedToken]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid or expired reset link.'
            ];
        }
        
        // Hash new password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $updateStmt = $this->db->prepare("
            UPDATE users 
            SET password = ?, password_reset_token = NULL, password_reset_expires = NULL
            WHERE id = ?
        ");
        
        try {
            $updateStmt->execute([$hashedPassword, $user['id']]);
            
            // Send confirmation email
            $this->emailService->sendPasswordResetConfirmation($user['email'], $user['username']);
            
            return [
                'success' => true,
                'message' => 'Password reset successfully! You can now login with your new password.'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to reset password. Please try again.'
            ];
        }
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
        // ✓ FIXED: Added is_banned column to SELECT query
        $stmt = $this->db->prepare("SELECT id, username, email, phone, role, created_at, is_banned, ban_reason FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function isAdmin() {
        return $this->session->get('role') === 'admin';
    }

    private function generateOTP() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
?>