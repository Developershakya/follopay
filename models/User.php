<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, phone, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['phone'] ?? null
        ]);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $allowedFields = ['username', 'email', 'phone', 'wallet_balance', 'is_banned', 'ban_reason'];
        $set = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $set[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($set)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $set) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function updateLastActive($id) {
        $stmt = $this->db->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getAllUsers($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("
            SELECT id, username, email, phone, wallet_balance, 
                   is_banned, ban_reason, created_at, last_active
            FROM users 
            WHERE role = 'user'
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function countAllUsers() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
        return $stmt->fetch()['count'];
    }
    
    public function searchUsers($query) {
        $stmt = $this->db->prepare("
            SELECT id, username, email, phone, wallet_balance, 
                   is_banned, created_at
            FROM users 
            WHERE role = 'user'
            AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        
        return $stmt->fetchAll();
    }
    
    public function updateWallet($userId, $amount, $type = 'credit') {
        $operator = $type === 'credit' ? '+' : '-';
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET wallet_balance = wallet_balance $operator ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$amount, $userId]);
    }
    
    public function getTopEarners($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT u.username, u.wallet_balance,
                   (SELECT COUNT(*) FROM user_post_assignments 
                    WHERE user_id = u.id AND status = 'approved') as completed_tasks,
                   (SELECT COALESCE(SUM(p.price), 0) FROM user_post_assignments upa
                    JOIN posts p ON upa.post_id = p.id
                    WHERE upa.user_id = u.id AND upa.status = 'approved') as total_earned
            FROM users u
            WHERE u.role = 'user'
            ORDER BY total_earned DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>