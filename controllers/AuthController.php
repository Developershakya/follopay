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
    
    // 📱 Device fingerprint generate karein
    private function generateDeviceFingerprint() {
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        
        $fingerprint = hash('sha256', $ip . $userAgent . $acceptLanguage);
        return $fingerprint;
    }
    
    // 🌐 Client ka real IP address nikaalein
    private function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        return trim($ip);
    }
    
    // 🔍 Check karein same device se kitne accounts hain
    private function checkDuplicateDevice($userId = null, $phone = null, $email = null) {
        $fingerprint = $this->generateDeviceFingerprint();
        $ip = $this->getClientIP();
        
        try {
            // Check 1: Same device/IP se multiple accounts
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT dt.user_id) as account_count 
                FROM device_tracking dt
                WHERE (dt.device_fingerprint = ? OR dt.ip_address = ?) 
                AND dt.is_active = 1
                " . ($userId ? "AND dt.user_id != ?" : ""));
            
            if ($userId) {
                $stmt->execute([$fingerprint, $ip, $userId]);
            } else {
                $stmt->execute([$fingerprint, $ip]);
            }
            
            $result = $stmt->fetch();
            $deviceAccountCount = $result['account_count'] ?? 0;
            
            // Check 2: Same phone se multiple accounts
            $phoneAccountCount = 0;
            if ($phone) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count FROM users 
                    WHERE phone = ? " . ($userId ? "AND id != ?" : ""));
                
                if ($userId) {
                    $stmt->execute([$phone, $userId]);
                } else {
                    $stmt->execute([$phone]);
                }
                
                $phoneResult = $stmt->fetch();
                $phoneAccountCount = $phoneResult['count'] ?? 0;
            }
            
            // Check 3: Same email se multiple accounts
            $emailAccountCount = 0;
            if ($email) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count FROM users 
                    WHERE email = ? " . ($userId ? "AND id != ?" : ""));
                
                if ($userId) {
                    $stmt->execute([$email, $userId]);
                } else {
                    $stmt->execute([$email]);
                }
                
                $emailResult = $stmt->fetch();
                $emailAccountCount = $emailResult['count'] ?? 0;
            }
            
            return [
                'device_duplicates' => $deviceAccountCount,
                'phone_duplicates' => $phoneAccountCount,
                'email_duplicates' => $emailAccountCount,
                'is_suspicious' => ($deviceAccountCount > 0 || $phoneAccountCount > 0)
            ];
        } catch (PDOException $e) {
            return ['device_duplicates' => 0, 'phone_duplicates' => 0, 'email_duplicates' => 0, 'is_suspicious' => false];
        }
    }
    
    // 💾 Device info track karein (registration ke baad)
    private function trackDevice($userId) {
        $fingerprint = $this->generateDeviceFingerprint();
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO device_tracking (user_id, device_fingerprint, ip_address, user_agent, is_active, created_at) 
                VALUES (?, ?, ?, ?, 1, NOW())
            ");
            
            $stmt->execute([$userId, $fingerprint, $ip, $userAgent]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // 📝 Login history store karein
    private function logLogin($userId, $status = 'success') {
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_history (user_id, ip_address, user_agent, status, login_time) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$userId, $ip, $userAgent, $status]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
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

        // 🔍 Check karein kya same device/phone se pehle se account hai
        $duplicateCheck = $this->checkDuplicateDevice(null, $data['phone'], $data['email']);
        
        if ($duplicateCheck['is_suspicious']) {
            return [
                'success' => false,
                'message' => 'Multiple accounts detected from your device. Please contact support.',
                'device_duplicates' => $duplicateCheck['device_duplicates']
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
            
            // 📱 Device ko track karein
            $this->trackDevice($userId);
            
            // 📝 Login history
            $this->logLogin($userId, 'registration');

            // 🔓 Auto login after registration
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
            $this->logLogin(null, 'failed');
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Check if banned
        if ($user['is_banned']) {
            $this->logLogin($user['id'], 'banned');
            $this->session->set('banned_user', [
                'username' => $user['username'],
                'ban_reason' => $user['ban_reason']
            ]);
            return ['success' => false, 'banned' => true, 'reason' => $user['ban_reason']];
        }
        
        // 🔍 Check kya koi suspicious activity hai
        $duplicateCheck = $this->checkDuplicateDevice($user['id']);
        
        // Set session
        $this->session->set('user_id', $user['id']);
        $this->session->set('username', $user['username']);
        $this->session->set('role', $user['role']);
        $this->session->set('logged_in', true);
        $this->session->set('device_duplicates', $duplicateCheck['device_duplicates']);
        
        // Update last login
        $updateStmt = $this->db->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        
        // 📝 Log successful login
        $this->logLogin($user['id'], 'success');
        
        // 📱 Track device
        $this->trackDevice($user['id']);
        
        return [
            'success' => true, 
            'role' => $user['role'],
            'device_duplicates' => $duplicateCheck['device_duplicates'],
            'has_duplicates' => $duplicateCheck['device_duplicates'] > 0
        ];
    }
    
    public function logout() {
        $userId = $this->session->get('user_id');
        if ($userId) {
            $this->logLogin($userId, 'logout');
        }
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
    
    // 👨‍💼 Admin ko info dene ke liye - suspicious users track karein
    public function getSuspiciousUsers() {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.id,
                    u.username,
                    u.email,
                    u.phone,
                    COUNT(DISTINCT dt.device_fingerprint) as unique_devices,
                    GROUP_CONCAT(DISTINCT dt.ip_address) as ip_addresses,
                    MAX(dt.created_at) as last_device_login,
                    u.created_at
                FROM users u
                LEFT JOIN device_tracking dt ON u.id = dt.user_id
                GROUP BY u.id
                HAVING unique_devices > 1 OR 
                       (SELECT COUNT(*) FROM users u2 WHERE u2.phone = u.phone) > 1
                ORDER BY unique_devices DESC
            ");
            
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error fetching data'];
        }
    }
    
    // 🔗 Linked accounts dekhen
    public function getLinkedAccounts($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.id,
                    u.username,
                    u.email,
                    u.phone,
                    dt.device_fingerprint,
                    dt.ip_address,
                    dt.created_at,
                    lh.login_time,
                    lh.status
                FROM users u
                INNER JOIN device_tracking dt ON u.id = dt.user_id
                LEFT JOIN login_history lh ON u.id = lh.user_id
                WHERE (dt.device_fingerprint IN (
                    SELECT device_fingerprint FROM device_tracking WHERE user_id = ?
                ) OR dt.ip_address IN (
                    SELECT ip_address FROM device_tracking WHERE user_id = ?
                ))
                AND u.id != ?
                ORDER BY dt.created_at DESC
            ");
            
            $stmt->execute([$userId, $userId, $userId]);
            return ['success' => true, 'data' => $stmt->fetchAll()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error fetching linked accounts'];
        }
    }
}
?>