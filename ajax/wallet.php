<?php
require_once '../config/constants.php';
require_once '../middleware/AuthMiddleware.php';
require_once '../middleware/BanCheckMiddleware.php';
require_once '../controllers/WalletController.php';
require_once '../controllers/WithdrawController.php';

header('Content-Type: application/json');

AuthMiddleware::handle();
BanCheckMiddleware::handle();

$walletController = new WalletController();
$withdrawController = new WithdrawController();
$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'get_balance':
        $wallet = $walletController->getUserWallet($userId);
        echo json_encode(['success' => true, 'wallet' => $wallet]);
        break;
        
    case 'get_transactions':
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $offset = ($page - 1) * $limit;
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
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
        ");
        
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $transactions = $stmt->fetchAll();
        echo json_encode(['success' => true, 'transactions' => $transactions]);
        break;
        
    case 'get_withdrawal_history':
        $withdrawals = $walletController->getWithdrawalHistory($userId);
        echo json_encode(['success' => true, 'withdrawals' => $withdrawals]);
        break;
        
    case 'get_free_fire_cards':
        $cards = $walletController->getFreeFireCards();
        echo json_encode(['success' => true, 'cards' => $cards]);
        break;
        
    case 'calculate_withdraw':
        $type = $_POST['type'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $mode = $_POST['mode'] ?? 'instant';
        
        $calculation = $withdrawController->calculateWithdraw($type, $amount, $mode);
        echo json_encode($calculation);
        break;
        
case 'request_withdraw':
    $db = Database::getInstance()->getConnection();
    
    $type = $_POST['type'] ?? '';
    $mode = $_POST['withdraw_mode'] ?? 'instant';
    $amount = floatval($_POST['amount'] ?? 0);
    
    // Auto-fetch saved UPI / Free Fire UID
    $upiId = $_POST['upi_id'] ?? null;
    $ffUid = $_POST['free_fire_uid'] ?? null;
    
    if ($type === 'upi' && !$upiId) {
        // Fetch from DB
        $stmt = $db->prepare("SELECT upi_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $upiId = $row['upi_id'] ?? null;
        if (!$upiId) {
            echo json_encode(['success' => false, 'message' => 'Please set your UPI ID in Payment Methods first.']);
            break;
        }
    }
    
    if ($type === 'free_fire' && !$ffUid) {
        $stmt = $db->prepare("SELECT free_fire_uid FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $ffUid = $row['free_fire_uid'] ?? null;
        if (!$ffUid) {
            echo json_encode(['success' => false, 'message' => 'Please set your Free Fire UID in Payment Methods first.']);
            break;
        }
    }
    
    $result = $withdrawController->createWithdrawRequest($userId, [
        'type' => $type,
        'amount' => $amount,
        'withdraw_mode' => $mode,
        'upi_id' => $upiId,
        'free_fire_uid' => $ffUid
    ]);
    
    echo json_encode($result);
    break;
        
    case 'get_withdraw_limits':
        $limits = $withdrawController->getWithdrawLimits();
        echo json_encode(['success' => true, 'limits' => $limits]);
        break;
        
    case 'get_withdraw_stats':
        $stats = $withdrawController->getWithdrawStats($userId);
        echo json_encode(['success' => true, 'stats' => $stats]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);



    case 'get_saved_payment_methods':
    // Returns saved UPI, Free Fire UID, and Bank for the logged-in user
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT upi_id, free_fire_uid FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch bank account
    $bankStmt = $db->prepare("SELECT * FROM user_bank_accounts WHERE user_id = ? LIMIT 1");
    $bankStmt->execute([$userId]);
    $bank = $bankStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'upi_id' => $userInfo['upi_id'] ?? null,
        'free_fire_uid' => $userInfo['free_fire_uid'] ?? null,
        'bank' => $bank ?: null
    ]);
    break;

case 'save_upi_id':
    $db = Database::getInstance()->getConnection();
    $upiId = trim($_POST['upi_id'] ?? '');
    
    // Validate UPI format
    if (!$upiId || !preg_match('/^[\w.\-]+@[\w]+$/', $upiId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid UPI ID format']);
        break;
    }
    
    // Check if already set (non-editable policy)
    $checkStmt = $db->prepare("SELECT upi_id FROM users WHERE id = ?");
    $checkStmt->execute([$userId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!empty($existing['upi_id'])) {
        echo json_encode(['success' => false, 'message' => 'UPI ID is already set and cannot be changed. Contact support.']);
        break;
    }
    
    // Check if this UPI is used by another account (ban protection)
    $dupStmt = $db->prepare("SELECT id FROM users WHERE upi_id = ? AND id != ?");
    $dupStmt->execute([$upiId, $userId]);
    if ($dupStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This UPI ID is already linked to another account.']);
        break;
    }
    
    $stmt = $db->prepare("UPDATE users SET upi_id = ? WHERE id = ?");
    $stmt->execute([$upiId, $userId]);
    
    echo json_encode(['success' => true, 'message' => 'UPI ID saved successfully!']);
    break;

case 'save_free_fire_uid':
    $db = Database::getInstance()->getConnection();
    $ffUid = trim($_POST['free_fire_uid'] ?? '');
    
    // Validate
    if (!$ffUid || !preg_match('/^\d{8,10}$/', $ffUid)) {
        echo json_encode(['success' => false, 'message' => 'Invalid Free Fire UID. Must be 8-10 digits.']);
        break;
    }
    
    // Check if already set
    $checkStmt = $db->prepare("SELECT free_fire_uid FROM users WHERE id = ?");
    $checkStmt->execute([$userId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!empty($existing['free_fire_uid'])) {
        echo json_encode(['success' => false, 'message' => 'Free Fire UID is already set and cannot be changed. Contact support.']);
        break;
    }
    
    $stmt = $db->prepare("UPDATE users SET free_fire_uid = ? WHERE id = ?");
    $stmt->execute([$ffUid, $userId]);
    
    echo json_encode(['success' => true, 'message' => 'Free Fire UID saved successfully!']);
    break;

case 'save_bank_account':
    $db = Database::getInstance()->getConnection();
    $bankName   = trim($_POST['bank_name'] ?? '');
    $accountNum = trim($_POST['account_number'] ?? '');
    $ifscCode   = strtoupper(trim($_POST['ifsc_code'] ?? ''));
    $holderName = trim($_POST['account_holder'] ?? '');
    
    // Validate
    if (!$bankName || !$accountNum || !$ifscCode || !$holderName) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        break;
    }
    if (!preg_match('/^\d{9,18}$/', $accountNum)) {
        echo json_encode(['success' => false, 'message' => 'Invalid account number']);
        break;
    }
    if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifscCode)) {
        echo json_encode(['success' => false, 'message' => 'Invalid IFSC code format']);
        break;
    }
    
    // Check if already set
    $checkStmt = $db->prepare("SELECT id FROM user_bank_accounts WHERE user_id = ? LIMIT 1");
    $checkStmt->execute([$userId]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Bank account is already set and cannot be changed. Contact support.']);
        break;
    }
    
    $stmt = $db->prepare("
        INSERT INTO user_bank_accounts (user_id, bank_name, account_number, ifsc_code, account_holder, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $bankName, $accountNum, $ifscCode, $holderName]);
    
    echo json_encode(['success' => true, 'message' => 'Bank account saved successfully!']);
    break;



        }
?>