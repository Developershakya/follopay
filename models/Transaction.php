<?php
class Transaction {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO transactions 
            (user_id, type, amount, balance_after, description, reference_id, reference_type, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $data['user_id'],
            $data['type'],
            $data['amount'],
            $data['balance_after'],
            $data['description'],
            $data['reference_id'] ?? null,
            $data['reference_type'] ?? null,
            $data['status'] ?? 'completed'
        ]);
    }
    
    public function findByUserId($userId, $limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT t.*,
                   p.app_name,
                   w.type as withdraw_type,
                   w.status as withdraw_status
            FROM transactions t
            LEFT JOIN posts p ON t.reference_id = p.id AND t.reference_type = 'post'
            LEFT JOIN withdrawals w ON t.reference_id = w.id AND t.reference_type = 'withdrawal'
            WHERE t.user_id = ?
            ORDER BY t.created_at DESC
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
            FROM transactions 
            WHERE user_id = ?
        ]);
        $stmt->execute([$userId]);
        return $stmt->fetch()['count'];
    }
    
    public function getUserBalance($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as total_credit,
                COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) as total_debit,
                COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END), 0) as net_balance
            FROM transactions 
            WHERE user_id = ? AND status = 'completed'
        ]);
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function getDailyStats($days = 7) {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credits,
                SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debits,
                COUNT(DISTINCT user_id) as unique_users
            FROM transactions 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ]);
        
        $stmt->bindValue(1, $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getTopTransactions($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.username
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            ORDER BY t.amount DESC
            LIMIT ?
        ]);
        
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getPendingTransactions() {
        $stmt = $this->db->prepare("
            SELECT t.*, u.username, u.email
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            WHERE t.status = 'pending'
            ORDER BY t.created_at DESC
        ]);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function updateStatus($transactionId, $status) {
        $stmt = $this->db->prepare("
            UPDATE transactions 
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ]);
        return $stmt->execute([$status, $transactionId]);
    }
}
?>