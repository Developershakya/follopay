<?php
// add-money-simple.php - Simple API endpoint
// Sirf UTR aur Amount - No file upload, No image URL

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../middleware/AuthMiddleware.php';

// Check authentication
AuthMiddleware::handle();

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$db = Database::getInstance()->getConnection();

switch($action) {
    
    case 'verify_payment':
        /**
         * Verify payment aur add money to wallet
         * POST params: utr, amount
         * 
         * Assume payment already in database from PayU webhook
         */
        $utr = $_POST['utr'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        
        // Validation
        if (empty($utr) || $amount <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'कृपया UTR और Amount भरें',
                'type' => 'error'
            ]);
            break;
        }
        
        // Check if payment exists in payu_payments (from webhook)
        $stmt = $db->prepare("
            SELECT * FROM payu_payments 
            WHERE utr = ? AND amount = ? AND status = 'completed'
            LIMIT 1
        ");
        $stmt->execute([$utr, $amount]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            echo json_encode([
                'success' => false,
                'message' => 'UTR या Amount गलत है। कृपया दोबारा जांचें।',
                'type' => 'error'
            ]);
            break;
        }
        
        // Check if already added (prevent duplicate)
        $stmt = $db->prepare("
            SELECT id FROM transactions 
            WHERE user_id = ? AND payment_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$userId, $payment['id']]);
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'यह पेमेंट पहले से जोड़ा जा चुका है',
                'type' => 'warning'
            ]);
            break;
        }
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Update user wallet balance
            $stmt = $db->prepare("
                UPDATE users 
                SET wallet_balance = wallet_balance + ?, 
                    total_earned = total_earned + ?
                WHERE id = ?
            ");
            $stmt->execute([$amount, $amount, $userId]);
            
            // Get new balance
            $stmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $newBalance = $user['wallet_balance'];
            
            // Create transaction record
            $stmt = $db->prepare("
                INSERT INTO transactions 
                (user_id, type, amount, description, payment_id, status, created_at)
                VALUES (?, 'credit', ?, ?, ?, 'completed', NOW())
            ");
            $stmt->execute([
                $userId,
                $amount,
                'Payment Added - UTR: ' . $utr,
                $payment['id']
            ]);
            
            // Update payment record with user id
            $stmt = $db->prepare("
                UPDATE payu_payments 
                SET user_id = ? 
                WHERE id = ?
            ");
            $stmt->execute([$userId, $payment['id']]);
            
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => '₹' . number_format($amount, 2) . ' जोड़ा गया सफलतापूर्वक ✓',
                'type' => 'success',
                'amount' => $amount,
                'new_balance' => $newBalance
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error adding payment: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'पेमेंट जोड़ने में गलती हुई',
                'type' => 'error'
            ]);
        }
        break;
    
    case 'get_wallet':
        /**
         * Get current wallet balance
         */
        $stmt = $db->prepare("
            SELECT wallet_balance, total_earned FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($wallet) {
            echo json_encode([
                'success' => true,
                'wallet_balance' => $wallet['wallet_balance'],
                'total_earned' => $wallet['total_earned']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Wallet not found'
            ]);
        }
        break;
    
    case 'get_transactions':
        /**
         * Get transaction history
         * GET params: limit, offset
         */
        $limit = intval($_GET['limit'] ?? 50);
        $offset = intval($_GET['offset'] ?? 0);
        
        $stmt = $db->prepare("
            SELECT 
                id, 
                type, 
                amount, 
                description, 
                status,
                created_at
            FROM transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $limit, $offset]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'transactions' => $transactions
        ]);
        break;
    
    case 'get_stats':
        /**
         * Get earning statistics
         */
        $stmt = $db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as total_credit,
                COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) as total_debit
            FROM transactions 
            WHERE user_id = ? AND status = 'completed'
        ");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'total_earned' => $stats['total_credit'],
            'total_withdrawn' => $stats['total_debit']
        ]);
        break;
    
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
}
?>