<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Validation.php';

class WithdrawController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Calculate withdraw charges and rules
public function calculateWithdraw($type, $amount, $mode, $date = null) {

    $date = $date ?: date('Y-m-d');
    $dayOfMonth = date('j', strtotime($date));

    $result = [
        'amount' => $amount,
        'charge_percent' => 0,
        'charge_amount' => 0,
        'final_amount' => $amount,
        'allowed' => true,
        'message' => '',
        'processing_days' => '1-7 days'
    ];

    /* ================= UPI ================= */
    if ($type === 'upi') {

        // 🔥 INSTANT → CHARGE LAGEGA
        if ($mode === 'instant') {

            if ($amount < WITHDRAW_INSTANT_MIN) {
                return [
                    'allowed' => false,
                    'message' => 'Minimum ₹' . WITHDRAW_INSTANT_MIN . ' required for instant withdraw'
                ];
            }

            $result['charge_percent'] = WITHDRAW_INSTANT_CHARGE;
            $result['charge_amount']  = ($amount * WITHDRAW_INSTANT_CHARGE) / 100;
            $result['final_amount']   = $amount - $result['charge_amount'];
            $result['processing_days'] = '1-7 working days';
        }

        // 🟢 DURATION → NO CHARGE
        elseif ($mode === 'duration') {

            if ($dayOfMonth < 10 || $dayOfMonth > 17) {
                return [
                    'allowed' => false,
                    'message' => 'Duration withdraw only available from 10th to 17th'
                ];
            }

            if ($amount < WITHDRAW_DURATION_MIN) {
                return [
                    'allowed' => false,
                    'message' => 'Minimum ₹' . WITHDRAW_DURATION_MIN . ' required for duration withdraw'
                ];
            }

            // 🔥 NO CHARGE
            $result['charge_percent'] = 0;
            $result['charge_amount']  = 0;
            $result['final_amount']   = $amount;
            $result['processing_days'] = '5-7 working days';
        }
    }

    /* ================= FREE FIRE ================= */
    elseif ($type === 'free_fire') {

        $card = $this->getFreeFireCardByAmount($amount);

        if (!$card) {
            return [
                'allowed' => false,
                'message' => 'Invalid diamond card amount'
            ];
        }

        $result['diamonds'] = $card['diamonds'];
        $result['processing_days'] = '5-7 working days';
    }

    return $result;
}

    private function getFreeFireCardByAmount($amount) {
        $stmt = $this->db->prepare("SELECT * FROM free_fire_cards WHERE rupees = ? AND is_active = TRUE LIMIT 1");
        $stmt->execute([$amount]);
        return $stmt->fetch();
    }
    
    // Create a withdrawal request
    public function createWithdrawRequest($userId, $data) {
        $validation = new Validation();
        
        $rules = [
            'type' => 'required|in:upi,free_fire',
            'amount' => 'required|numeric|min:1',
            'withdraw_mode' => 'required|in:instant,duration'
        ];
        
        // Fixed regex delimiter issue
        if ($data['type'] === 'upi') {
            $rules['upi_id'] = 'required|regex:^[\w.\-@]+$';
        } elseif ($data['type'] === 'free_fire') {
            $rules['free_fire_uid'] = 'required|regex:/^\d{8,10}$/';
        }
        
        if (!$validation->validate($data, $rules)) {
            return ['success' => false, 'errors' => $validation->errors()];
        }
        
        $calculation = $this->calculateWithdraw(
            $data['type'],
            $data['amount'],
            $data['withdraw_mode']
        );
        
        if (!$calculation['allowed']) {
            return ['success' => false, 'message' => $calculation['message']];
        }
        
        $user = $this->getUserBalance($userId);
        if (!$user || $user['wallet_balance'] < $data['amount']) {
            return ['success' => false, 'message' => 'Insufficient balance'];
        }
        
        $this->db->beginTransaction();
        
        try {
            // Insert withdrawal
            $stmt = $this->db->prepare("
                INSERT INTO withdrawals 
                (user_id, type, withdraw_mode, amount, charge_percent, charge_amount, final_amount, upi_id, free_fire_uid, status, request_date, created_at)
                VALUES
                (:user_id, :type, :withdraw_mode, :amount, :charge_percent, :charge_amount, :final_amount, :upi_id, :free_fire_uid, :status, :request_date, NOW())
            ");
            
            $stmt->execute([
                ':user_id' => $userId,
                ':type' => $data['type'],
                ':withdraw_mode' => $data['withdraw_mode'] ?? 'instant',
                ':amount' => $data['amount'],
                ':charge_percent' => $calculation['charge_percent'],
                ':charge_amount' => $calculation['charge_amount'],
                ':final_amount' => $calculation['final_amount'],
                ':upi_id' => $data['upi_id'] ?? null,
                ':free_fire_uid' => $data['free_fire_uid'] ?? null,
                ':status' => 'pending',
                ':request_date' => date('Y-m-d')
            ]);
            
            $withdrawId = $this->db->lastInsertId();
            
            // Deduct wallet
            $newBalance = $user['wallet_balance'] - $data['amount'];
            $this->updateUserBalance($userId, $newBalance);
            
            // Transaction record
            $this->createTransaction($userId, $data, $withdrawId, $newBalance);
            
            // Admin notification
            $this->createAdminNotification($withdrawId, $userId);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'withdraw_id' => $withdrawId,
                'message' => 'Withdrawal request submitted successfully! Processing time: ' . $calculation['processing_days']
            ];
        } catch(Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to create withdrawal: ' . $e->getMessage()];
        }
    }
    
    private function getUserBalance($userId) {
        $stmt = $this->db->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    private function updateUserBalance($userId, $newBalance) {
        $stmt = $this->db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
        $stmt->execute([$newBalance, $userId]);
    }
    
    private function createTransaction($userId, $data, $withdrawId, $newBalance) {
        $description = $data['type'] === 'upi' 
            ? 'UPI Withdrawal (' . ($data['withdraw_mode'] ?? 'instant') . ')'
            : 'Free Fire Diamonds Purchase';
        
        $stmt = $this->db->prepare("
            INSERT INTO transactions 
            (user_id, type, amount, balance_after, description, reference_id, reference_type, status, created_at)
            VALUES (?, 'debit', ?, ?, ?, ?, 'withdrawal', 'pending', NOW())
        ");
        
        $stmt->execute([
            $userId,
            $data['amount'],
            $newBalance,
            $description,
            $withdrawId
        ]);
    }
    
private function createAdminNotification($withdrawId, $userId) {
    $stmt = $this->db->prepare("
        INSERT INTO admin_notifications 
        (withdraw_id, user_id, message, created_at)
        VALUES (?, ?, 'New withdrawal request', NOW())
    ");
    $stmt->execute([$withdrawId, $userId]);
}

    
    public function getWithdrawLimits() {
        return [
            'instant' => [
                'min' => WITHDRAW_INSTANT_MIN,
                'charge' => WITHDRAW_INSTANT_CHARGE
            ],
            'duration' => [
                'min' => WITHDRAW_DURATION_MIN,
                'charge' => 0,
                'available' => $this->isDurationWithdrawAvailable()
            ]
        ];
    }
    
    private function isDurationWithdrawAvailable() {
        $dayOfMonth = date('j');
        return ($dayOfMonth >= 10 && $dayOfMonth <= 17);
    }
    
    public function getWithdrawStats($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'approved' THEN final_amount ELSE 0 END) as total_approved,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending,
                SUM(charge_amount) as total_charges
            FROM withdrawals 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
?>
