<?php
require_once '../config/constants.php';
require_once '../controllers/AccountController.php';
require_once '../helpers/Session.php';

header('Content-Type: application/json');

$accountController = new AccountController();
$session = new Session();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    // ✅ NEW: Get current user info (for form pre-filling)
    case 'check-user':
        if (!$session->get('logged_in')) {
            echo json_encode([
                'success' => false,
                'message' => 'Not logged in'
            ]);
            break;
        }
        
        $userId = $session->get('user_id');
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, username, email, phone FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
        break;

    case 'request-deletion':
        $result = $accountController->requestDeletion([
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'reason' => $_POST['reason'] ?? ''
        ]);
        echo json_encode($result);
        break;

    case 'get-requests':
        $status = $_GET['status'] ?? null;
        $limit = intval($_GET['limit'] ?? 20);
        $offset = intval($_GET['offset'] ?? 0);
        
        $result = $accountController->getAllDeletionRequests($status, $limit, $offset);
        echo json_encode($result);
        break;

    case 'get-request':
        $requestId = intval($_GET['id'] ?? 0);
        $result = $accountController->getDeletionRequest($requestId);
        echo json_encode($result);
        break;

    case 'approve-deletion':
        $requestId = intval($_POST['request_id'] ?? 0);
        $adminNotes = $_POST['admin_notes'] ?? '';
        
        $result = $accountController->approveDeletion($requestId, $adminNotes);
        echo json_encode($result);
        break;

    case 'reject-deletion':
        $requestId = intval($_POST['request_id'] ?? 0);
        $reason = $_POST['reason'] ?? '';
        
        $result = $accountController->rejectDeletion($requestId, $reason);
        echo json_encode($result);
        break;

    case 'cancel-deletion':
        $result = $accountController->cancelDeletionRequest();
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>