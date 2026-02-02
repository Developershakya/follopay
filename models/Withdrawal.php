<?php
class Withdrawal {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO withdrawals 
            (user_id, type, withdraw_mode, amount, charge_percent, charge_amount, 
             final_amount, upi_id, free_fire_uid, status, request_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', CURDATE(), NOW())
        ]);
        
        return $stmt->execute([
            $data['user_id'],
            $data['type'],
            $data['withdraw_mode'] ?? 'instant',
            $data['amount'],
            $data['charge_percent'] ?? 0,
            $data['charge_amount'] ?? 0,
            $data['final_amount'],
            $data['upi_id'] ?? null,
            $data['free_fire_uid'] ?? null
        ]);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT w.*, u.username, u.email, u.phone
            FROM withdrawals w
            JOIN users u ON w.user_id = u.id
            WHERE w.id = ?
        ]);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByUserId($userId, $limit = 20, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT w.*
            FROM withdrawals w
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
            LIMIT ? OFFSET ?
        ]);
        
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function countByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM withdrawals 
            WHERE user_id = ?
        ]);
        $stmt->execute([$userId]);
        return $stmt->fetch()['count'];
    }
    
    public function getPendingWithdrawals() {
        $stmt = $this->db->prepare("
            SELECT w.*, u.username, u.email, u.phone
            FROM withdrawals w
            JOIN users u ON w.user_id = u.id
            WHERE w.status = 'pending'
            ORDER BY w.created_at DESC
        ]);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function updateStatus($withdrawalId, $status, $adminId = null, $notes = '') {
        $stmt = $this->db->prepare("
            UPDATE withdrawals 
            SET status = ?, 
                processed_by = ?, 
                admin_notes = ?,
                processed_at = NOW()
            WHERE id = ?
        ]);
        
        return $stmt->execute([$status, $adminId, $notes, $withdrawalId]);
    }
    
    public function getStats() {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_withdrawals,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                COALESCE(SUM(final_amount), 0) as total_amount,
                COALESCE(SUM(charge_amount), 0) as total_charges
            FROM withdrawals
        ]);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getMonthlyStats($months = 6) {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(request_date, '%Y-%m') as month,
                COUNT(*) as withdrawal_count,
                SUM(amount) as total_amount,
                SUM(final_amount) as total_paid,
                SUM(charge_amount) as total_charges,
                COUNT(DISTINCT user_id) as unique_users
            FROM withdrawals
            WHERE request_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(request_date, '%Y-%m')
            ORDER BY month DESC
        ]);
        
        $stmt->bindValue(1, $months, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getWithdrawalsByType($type, $status = null, $limit = 50) {
        $where = "WHERE type = ?";
        $params = [$type];
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql = "
            SELECT w.*, u.username
            FROM withdrawals w
            JOIN users u ON w.user_id = u.id
            $where
            ORDER BY w.created_at DESC
            LIMIT ?
        ";
        
        $params[] = $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>