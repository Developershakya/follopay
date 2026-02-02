<?php
require_once __DIR__ . '/../config/database.php';

class WalletController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getUserWallet($userId) {
        // Get balance
        $stmt = $this->db->prepare("
            SELECT wallet_balance, created_at 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $wallet = $stmt->fetch();
        
        if (!$wallet) {
            return null;
        }
        
        // Get transaction history
        $transStmt = $this->db->prepare("
            SELECT t.*, 
                   p.app_name,
                   w.type as withdraw_type,
                   w.status as withdraw_status
            FROM transactions t
            LEFT JOIN posts p ON t.reference_id = p.id AND t.reference_type = 'post'
            LEFT JOIN withdrawals w ON t.reference_id = w.id AND t.reference_type = 'withdrawal'
            WHERE t.user_id = ?
            ORDER BY t.created_at DESC
            LIMIT 50
        ");
        $transStmt->execute([$userId]);
        
        $wallet['transactions'] = $transStmt->fetchAll();
        
        // Get pending submissions
        $pendingStmt = $this->db->prepare("
            SELECT COUNT(*) as pending_count, 
                   COALESCE(SUM(p.price), 0) as pending_amount
            FROM user_post_assignments upa
            JOIN posts p ON upa.post_id = p.id
            WHERE upa.user_id = ? AND upa.status = 'submitted'
        ");
        $pendingStmt->execute([$userId]);
        $pending = $pendingStmt->fetch();
        
        $wallet['pending'] = $pending;
        
        return $wallet;
    }
    
public function getWithdrawalHistory($userId) {
    try {
        $db = Database::getInstance()->getConnection();

        // Fetch withdrawals, latest first
        $stmt = $db->prepare("
            SELECT w.*,
                   CASE 
                       WHEN w.type = 'upi' AND w.withdraw_mode = 'instant' THEN CONCAT('Instant UPI (', w.charge_percent, '% charge)')
                       WHEN w.type = 'upi' AND w.withdraw_mode = 'duration' THEN 'Duration UPI (No charge)'
                       WHEN w.type = 'free_fire' THEN CONCAT('Free Fire Diamonds (', w.amount, ' = ', 
                           (SELECT diamonds FROM free_fire_cards WHERE rupees = w.amount LIMIT 1), ' diamonds)')
                   END AS description_formatted
            FROM withdrawals w
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$userId]);
        $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'withdrawals' => $withdrawals
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Failed to fetch withdrawal history: ' . $e->getMessage()
        ];
    }
}

    
public function getFreeFireCards() {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM free_fire_cards WHERE is_active = 1 ORDER BY rupees ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    
    public function addFreeFireCard($rupees, $diamonds) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO free_fire_cards (rupees, diamonds, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$rupees, $diamonds]);
            return ['success' => true, 'message' => 'Card added successfully'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateFreeFireCard($cardId, $data) {
        try {
            $set = [];
            $params = [];
            foreach ($data as $key => $value) {
                if (in_array($key, ['rupees', 'diamonds', 'is_active', 'display_order'])) {
                    $set[] = "$key = ?";
                    $params[] = $value;
                }
            }
            $params[] = $cardId;
            
            $sql = "UPDATE free_fire_cards SET " . implode(', ', $set) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return ['success' => true, 'message' => 'Card updated successfully'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getTotalEarned($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as total_earned,
                COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) as total_withdrawn
            FROM transactions 
            WHERE user_id = ? AND status = 'completed'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
?>