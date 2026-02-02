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
    $result = $withdrawController->createWithdrawRequest($userId, [
        'type' => $_POST['type'] ?? '',
        'amount' => $_POST['amount'] ?? 0,
        'withdraw_mode' => $_POST['withdraw_mode'] ?? 'instant',
        'upi_id' => $_POST['upi_id'] ?? '',
        'free_fire_uid' => $_POST['free_fire_uid'] ?? ''
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
}
?>