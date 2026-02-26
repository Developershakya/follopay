<?php
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../middleware/AuthMiddleware.php';
require_once '../middleware/AdminMiddleware.php';
require_once '../controllers/AdminController.php';
require_once '../controllers/PostController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Check authentication
    AuthMiddleware::handle();
    
    // Check admin rights
    AdminMiddleware::checkAdminOrJson();
    
    $adminController = new AdminController();
    $postController = new PostController();
    $db = Database::getInstance()->getConnection();
    
    // Get action - support both POST and GET
    $action = $_REQUEST['action'] ?? '';
    
    error_log("Admin API - Method: " . $_SERVER['REQUEST_METHOD'] . ", Action: " . $action);
    
    if (empty($action)) {
        throw new Exception('Action parameter is required');
    }
    
    switch($action) {
        // ============ POST MANAGEMENT ============
        case 'create_post':

            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
                break;
            }

            // Handle comments
            $comments = [];

            if (isset($input['comments'])) {
                if (is_array($input['comments'])) {
                    $comments = $input['comments'];
                } else {
                    $comments = explode("\n", $input['comments']);
                }
            }

            $comments = array_values(array_filter(array_map('trim', $comments)));

            $result = $adminController->createPost([
                'app_link' => $input['app_link'] ?? '',
                'app_name' => $input['app_name'] ?? '',
                'price'    => (float)($input['price'] ?? 0),
                'comments' => $comments
            ]);

            echo json_encode($result);
            break;

            
case 'update_post':
    $postId = intval($_POST['post_id'] ?? 0);
    $result = $adminController->updatePost($postId, [
        'app_link' => $_POST['app_link'] ?? '',
        'app_name' => $_POST['app_name'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'status' => $_POST['status'] ?? 'active'
    ]);
    echo json_encode($result);  // ← ADD THIS
    break; 

        case 'add_comment':
            $postId = intval($_POST['post_id'] ?? 0);
            $commentText = $_POST['comment_text'] ?? '';
            
            if (!$postId || !$commentText) {
                throw new Exception("Post ID and comment text are required");
            }
            
            $result = $adminController->addCommentToPost($postId, $commentText);
            echo json_encode($result);
            break;
            
        case 'delete_post':
            $postId = intval($_POST['post_id'] ?? $_GET['post_id'] ?? 0);
            if (!$postId) {
                throw new Exception("Post ID is required");
            }
            
            $db->beginTransaction();
            try {
                $stmt = $db->prepare("DELETE FROM user_post_assignments WHERE post_id = ?");
                $stmt->execute([$postId]);
                
                $stmt = $db->prepare("DELETE FROM comments WHERE post_id = ?");
                $stmt->execute([$postId]);
                
                $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
                $stmt->execute([$postId]);
                
                $db->commit();
                echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
            } catch(Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
        case 'get_post':
            $postId = intval($_GET['id'] ?? 0);
            if (!$postId) {
                echo json_encode(['success' => false, 'message' => 'Post ID required']);
                break;
            }
            
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id, app_name, app_link, price, status FROM posts WHERE id = ? LIMIT 1");
                $stmt->execute([$postId]);
                $post = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($post) {
                    echo json_encode(['success' => true, 'post' => $post]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Post not found']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break; 
        case 'get_posts':
            $status = $_GET['status'] ?? 'all';
            $search = $_GET['search'] ?? '';
            
            $where = "WHERE 1=1";
            $params = [];
            
            if ($status !== 'all') {
                $where .= " AND p.status = ?";
                $params[] = $status;
            }
            
            if (!empty($search)) {
                $where .= " AND p.app_name LIKE ?";
                $params[] = "%$search%";
            }
            
            $stmt = $db->prepare("
                SELECT p.*, 
                       COUNT(DISTINCT c.id) as total_comments,
                       SUM(CASE WHEN c.is_used = 1 THEN 1 ELSE 0 END) as used_comments,
                       SUM(CASE WHEN c.is_used = 0 THEN 1 ELSE 0 END) as available_comments
                FROM posts p
                LEFT JOIN comments c ON p.id = c.post_id
                $where
                GROUP BY p.id
                ORDER BY p.created_at DESC
            ");
            $stmt->execute($params);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'posts' => $posts,
                'total' => count($posts)
            ]);
            break;
            
        case 'get_post_comments':
            $postId = intval($_GET['post_id'] ?? 0);
            if (!$postId) {
                throw new Exception("Post ID is required");
            }
            
            $stmt = $db->prepare("
                SELECT c.*, 
                       CASE WHEN c.is_used = 1 THEN 'Used' ELSE 'Available' END as status
                FROM comments c
                WHERE c.post_id = ?
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$postId]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'comments' => $comments
            ]);
            break;
            
        case 'get_post_stats':
            $stmt = $db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    COALESCE(SUM(total_comments), 0) as total_comments
                FROM posts
            ");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        case 'get_recent_posts':

            $stmt = $db->prepare("
                SELECT 
                    p.*,
                    COUNT(c.id) AS comment_count
                FROM posts p
                LEFT JOIN comments c ON p.id = c.post_id
                GROUP BY p.id
                ORDER BY p.created_at DESC
            ");

            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'posts' => $posts
            ]);
            break;

        case 'toggle_post_status':

        $postId = intval($_POST['post_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';

        if (!$postId || !in_array($newStatus, ['active', 'inactive'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid post or status'
            ]);
            exit;
        }

        try {
            $stmt = $db->prepare("
                UPDATE posts 
                SET status = :status 
                WHERE id = :id
            ");

            $stmt->execute([
                ':status' => $newStatus,
                ':id' => $postId
            ]);

            if ($stmt->rowCount()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Post status updated'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No changes made'
                ]);
            }

        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error'
            ]);
        }

        break;
            
        case 'get_recent_comments':
            $limit = intval($_GET['limit'] ?? 10);
            
            $stmt = $db->prepare("
                SELECT c.*, p.app_name
                FROM comments c
                JOIN posts p ON c.post_id = p.id
                ORDER BY c.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'comments' => $comments]);
            break;
            
        // ============ WITHDRAWAL MANAGEMENT ============
        case 'get_pending_withdrawals':
            $withdrawals = $adminController->getPendingWithdrawals();
            echo json_encode(['success' => true, 'withdrawals' => $withdrawals]);
            break;
            
       case 'approve_withdrawal':
    $withdrawalId = intval($_POST['withdrawal_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$withdrawalId) {
        throw new Exception("Withdrawal ID is required");
    }
    
    $db->beginTransaction();
    try {
        // 1. Fetch withdrawal details
        $stmt = $db->prepare("
            SELECT w.*, u.id as user_id, u.username, u.wallet_balance
            FROM withdrawals w
            JOIN users u ON w.user_id = u.id
            WHERE w.id = ? AND w.status = 'pending'
            FOR UPDATE
        ");
        $stmt->execute([$withdrawalId]);
        $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$withdrawal) {
            throw new Exception("Withdrawal not found or already processed");
        }
        
        $userId = (int)$withdrawal['user_id'];
        $amount = (float)$withdrawal['amount'];
        $finalAmount = (float)$withdrawal['final_amount'];
        
        // 2. Update withdrawal status to approved
        $stmt = $db->prepare("
            UPDATE withdrawals
            SET status = 'approved', 
                admin_notes = ?,
                processed_at = NOW(),
                processed_by = ?
            WHERE id = ?
        ");
        $adminId = $_SESSION['user_id'] ?? null;
        $stmt->execute([$notes, $adminId, $withdrawalId]);
        
        // 3. UPDATE existing transaction record
        $description = 'Withdrawal approved - ' . ($withdrawal['type'] === 'upi' ? 'UPI Transfer' : 'Free Fire Diamonds');
        $stmt = $db->prepare("
            UPDATE transactions
            SET status = 'completed',
                balance_after = (SELECT wallet_balance FROM users WHERE id = ?),
                description = ?
            WHERE reference_id = ? AND reference_type = 'withdrawal'
        ");
        $stmt->execute([
            $userId,
            $description,
            $withdrawalId
        ]);
        
        // Check if transaction record exists
        if ($stmt->rowCount() === 0) {
            throw new Exception("Transaction record not found for this withdrawal");
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Withdrawal approved successfully! Transaction updated.',
            'withdrawal_id' => $withdrawalId,
            'amount' => $finalAmount
        ]);
        
    } catch(Exception $e) {
        $db->rollBack();
        throw $e;
    }
    break;
            
        case 'reject_withdrawal':
            $withdrawalId = intval($_POST['withdrawal_id'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            $refund = isset($_POST['refund']) ? (int)($_POST['refund']) : 0;
            
            if (!$withdrawalId) {
                throw new Exception("Withdrawal ID is required");
            }
            
            if (empty($notes)) {
                throw new Exception("Rejection reason is required");
            }
            
            $db->beginTransaction();
            try {
                // 1. Fetch withdrawal details
                $stmt = $db->prepare("
                    SELECT w.*, u.id as user_id
                    FROM withdrawals w
                    JOIN users u ON w.user_id = u.id
                    WHERE w.id = ? AND w.status = 'pending'
                    FOR UPDATE
                ");
                $stmt->execute([$withdrawalId]);
                $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$withdrawal) {
                    throw new Exception("Withdrawal not found or already processed");
                }
                
                $userId = (int)$withdrawal['user_id'];
                $amount = (float)$withdrawal['amount'];
                
                // 2. Update withdrawal status to failed/rejected
                $stmt = $db->prepare("
                    UPDATE withdrawals
                    SET status = 'failed', 
                        admin_notes = ?,
                        processed_at = NOW(),
                        processed_by = ?
                    WHERE id = ?
                ");
                $adminId = $_SESSION['user_id'] ?? null;
                $stmt->execute([$notes, $adminId, $withdrawalId]);
                
                // 3. Handle refund if enabled
                if ($refund) {
                    // Credit amount back to wallet
                    $stmt = $db->prepare("
                        UPDATE users
                        SET wallet_balance = wallet_balance + ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$amount, $userId]);
                }
                
                // 4. Create SINGLE transaction record
                $transactionType = $refund ? 'credit' : 'debit';
                $description = $refund ? 
                    'Withdrawal rejected & refunded - ' . ($withdrawal['type'] === 'upi' ? 'UPI Transfer' : 'Free Fire Diamonds') :
                    'Withdrawal rejected - ' . ($withdrawal['type'] === 'upi' ? 'UPI Transfer' : 'Free Fire Diamonds');
                
                $stmt = $db->prepare("
                    INSERT INTO transactions
                    (user_id, type, amount, balance_after, description, reference_id, reference_type, status, created_at)
                    VALUES (?, ?, ?, 
                        (SELECT wallet_balance FROM users WHERE id = ?),
                        ?,
                        ?, 'withdrawal', 'completed', NOW()
                    )
                ");
                $stmt->execute([
                    $userId,
                    $transactionType,
                    $refund ? $amount : 0,
                    $userId,
                    $description,
                    $withdrawalId
                ]);
                
                $db->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Withdrawal rejected successfully!' . ($refund ? ' Amount refunded to user wallet.' : ''),
                    'withdrawal_id' => $withdrawalId,
                    'refunded' => (bool)$refund,
                    'amount' => $amount
                ]);
                
            } catch(Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
            
        case 'get_withdrawals':
            $status = $_GET['status'] ?? 'all';
            $search = $_GET['search'] ?? '';
            $type = $_GET['type'] ?? '';
            $date = $_GET['date'] ?? '';
            $page = intval($_GET['page'] ?? 1);
            $pageSize = 10;
            
            $where = "WHERE 1=1";
            $params = [];
            
            if ($status !== 'all') {
                $where .= " AND w.status = ?";
                $params[] = $status;
            }
            
            if (!empty($type)) {
                $where .= " AND w.type = ?";
                $params[] = $type;
            }
            
            if (!empty($date)) {
                $where .= " AND DATE(w.created_at) = ?";
                $params[] = $date;
            }
            
            if (!empty($search)) {
                $where .= " AND (u.username LIKE ? OR u.email LIKE ? OR w.upi_id LIKE ? OR w.free_fire_uid LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Get total count
            $countStmt = $db->prepare("
                SELECT COUNT(*) as total
                FROM withdrawals w
                JOIN users u ON w.user_id = u.id
                $where
            ");
            $countStmt->execute($params);
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get paginated results
            $offset = ($page - 1) * $pageSize;
            $stmt = $db->prepare("
                SELECT w.*, 
                       u.username, u.email, u.phone
                FROM withdrawals w
                JOIN users u ON w.user_id = u.id
                $where
                ORDER BY 
                    CASE 
                        WHEN w.status = 'pending' THEN 1
                        WHEN w.status = 'approved' THEN 2
                        WHEN w.status = 'failed' THEN 3
                        ELSE 4
                    END,
                    w.created_at DESC
                LIMIT $pageSize OFFSET $offset
            ");
            $stmt->execute($params);
            $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate stats - with same filters as main query
            $statsStmt = $db->prepare("
                SELECT 
                    COUNT(*) as total,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending,
                    COALESCE(SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END), 0) as approved,
                    COALESCE(SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END), 0) as failed,
                    COALESCE(SUM(amount), 0) as total_amount,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount,
                    COALESCE(SUM(CASE WHEN status = 'approved' THEN final_amount ELSE 0 END), 0) as approved_amount
                FROM withdrawals w
                JOIN users u ON w.user_id = u.id
                $where
            ");
            $statsStmt->execute($params);
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
            
            // Convert numeric strings to actual numbers
            foreach ($stats as $key => $value) {
                if (is_numeric($value)) {
                    $stats[$key] = (float)$value;
                }
            }
            
            echo json_encode([
                'success' => true,
                'withdrawals' => $withdrawals,
                'stats' => $stats,
                'page' => $page,
                'pages' => ceil($totalCount / $pageSize),
                'total' => $totalCount
            ]);
            break;
            
        case 'get_withdrawal_details':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) {
                throw new Exception("Withdrawal ID is required");
            }
            
            $stmt = $db->prepare("
                SELECT w.*, 
                       u.username, u.email, u.phone,
                       COALESCE(admin.username, 'Not processed') as processed_by_username
                FROM withdrawals w
                JOIN users u ON w.user_id = u.id
                LEFT JOIN users admin ON w.processed_by = admin.id
                WHERE w.id = ?
            ");
            $stmt->execute([$id]);
            $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$withdrawal) {
                throw new Exception("Withdrawal not found");
            }
            
            echo json_encode([
                'success' => true,
                'withdrawal' => $withdrawal
            ]);
            break;
            
        // ============ SUBMISSION MANAGEMENT ============
        case 'get_pending_submissions':
            $stmt = $db->prepare("
                SELECT upa.*, 
                       p.app_name, p.price, p.app_link,
                       u.username, u.email, u.phone,
                       c.comment_text
                FROM user_post_assignments upa
                JOIN posts p ON upa.post_id = p.id
                JOIN users u ON upa.user_id = u.id
                LEFT JOIN comments c ON upa.comment_id = c.id
                WHERE upa.status = 'submitted'
                ORDER BY upa.submitted_time DESC
            ");
            $stmt->execute();
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'submissions' => $submissions,
                'total' => count($submissions)
            ]);
            break;
            
        case 'get_pending_screenshots':
            // Alias for get_pending_submissions
            $stmt = $db->prepare("
                SELECT upa.*, 
                       p.app_name, p.price, p.app_link,
                       u.username, u.email, u.phone,
                       c.comment_text
                FROM user_post_assignments upa
                JOIN posts p ON upa.post_id = p.id
                JOIN users u ON upa.user_id = u.id
                LEFT JOIN comments c ON upa.comment_id = c.id
                WHERE upa.status = 'submitted'
                ORDER BY upa.submitted_time DESC
            ");
            $stmt->execute();
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                // 'submissions' => $submissions,
                'screenshots' => $submissions, // Alias
                'total' => count($submissions)
            ]);
            break;
            
        case 'get_all_submissions':
            $status = $_GET['status'] ?? 'all';
            
            $where = "WHERE 1=1";
            $params = [];
            
            if ($status !== 'all') {
                $where .= " AND upa.status = ?";
                $params[] = $status;
            }
            
            $stmt = $db->prepare("
                SELECT upa.*, 
                       p.app_name, p.price, p.app_link,
                       u.username, u.email, u.phone,
                       c.comment_text
                FROM user_post_assignments upa
                JOIN posts p ON upa.post_id = p.id
                JOIN users u ON upa.user_id = u.id
                LEFT JOIN comments c ON upa.comment_id = c.id
                $where
                ORDER BY upa.created_at DESC
            ");
            $stmt->execute($params);
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'submissions' => $submissions,
                'total' => count($submissions)
            ]);
            break;
            
        case 'approve_submission':

            $assignmentId = (int)($_POST['assignment_id'] ?? 0);
            if (!$assignmentId) {
                throw new Exception('Assignment ID required');
            }

            $db->beginTransaction();

            // Fetch assignment + price
            $stmt = $db->prepare("
                SELECT upa.user_id, p.price
                FROM user_post_assignments upa
                JOIN posts p ON p.id = upa.post_id
                WHERE upa.id = ? AND upa.status = 'submitted'
                FOR UPDATE
            ");
            $stmt->execute([$assignmentId]);
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$assignment) {
                $db->rollBack();
                throw new Exception('Invalid or already processed submission');
            }

            $userId = (int)$assignment['user_id'];
            $amount = (float)$assignment['price'];

            // 1️⃣ Update assignment status
            $stmt = $db->prepare("
                UPDATE user_post_assignments
                SET status = 'approved'
                WHERE id = ?
            ");
            $stmt->execute([$assignmentId]);

            // 2️⃣ Update user wallet
            $stmt = $db->prepare("
                UPDATE users
                SET wallet_balance = wallet_balance + ?
                WHERE id = ?
            ");
            $stmt->execute([$amount, $userId]);

            // 3️⃣ Insert transaction (credit)
            $stmt = $db->prepare("
                INSERT INTO transactions
                (user_id, type, amount, balance_after, description, reference_id, reference_type, status, created_at)
                VALUES (?, 'credit', ?, 
                    (SELECT wallet_balance FROM users WHERE id = ?),
                    'Post screenshot approved',
                    ?, 'post', 'completed', NOW()
                )
            ");
            $stmt->execute([
                $userId,
                $amount,
                $userId,
                $assignmentId
            ]);

            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Submission approved, wallet credited'
            ]);
            break;


        /* ============ REJECT SUBMISSION ============ */

        case 'reject_submission':

            $assignmentId = (int)($_POST['assignment_id'] ?? 0);
            $reason = trim($_POST['reason'] ?? '');

            if (!$assignmentId) {
                throw new Exception('Assignment ID required');
            }

            if ($reason === '') {
                throw new Exception('Reject reason is required');
            }

            $db->beginTransaction();

            // Fetch assignment (for user_id)
            $stmt = $db->prepare("
                SELECT user_id
                FROM user_post_assignments
                WHERE id = ? AND status = 'submitted'
            ");
            $stmt->execute([$assignmentId]);
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$assignment) {
                $db->rollBack();
                throw new Exception('Invalid or already processed submission');
            }

            // Update assignment status
            $stmt = $db->prepare("
                UPDATE user_post_assignments
                SET status = 'rejected',
                    reject_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$reason, $assignmentId]);

            // INSERT failed transaction with 0 amount
            $stmt = $db->prepare("
                INSERT INTO transactions
                (user_id, type, amount, description, reference_id, reference_type, status, created_at)
                VALUES (?, 'credit', 0, 'Submission rejected', ?, 'post', 'failed', NOW())
            ");
            $stmt->execute([
                $assignment['user_id'],
                $assignmentId
            ]);

            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Submission rejected (transaction logged)'
            ]);
            break;

            
        // ============ USER MANAGEMENT ============
        case 'get_users':
            $status = $_GET['status'] ?? 'all';
            $search = $_GET['search'] ?? '';
            $page = intval($_GET['page'] ?? 1);
            $sort = $_GET['sort'] ?? 'newest';
            
            $where = "WHERE role = 'user'";
            $params = [];
            
            if ($status === 'banned') {
                $where .= " AND is_banned = 1";
            } elseif ($status === 'active') {
                $where .= " AND is_banned = 0";
            }
            
            if (!empty($search)) {
                $where .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Sort order
            $orderBy = "ORDER BY u.created_at DESC";
            if ($sort === 'oldest') {
                $orderBy = "ORDER BY u.created_at ASC";
            } elseif ($sort === 'balance_high') {
                $orderBy = "ORDER BY u.wallet_balance DESC";
            } elseif ($sort === 'balance_low') {
                $orderBy = "ORDER BY u.wallet_balance ASC";
            }
            
            $stmt = $db->prepare("
                SELECT u.id, u.username, u.email, u.phone, u.wallet_balance, 
                       u.is_banned, u.ban_reason, u.created_at,
                       COUNT(DISTINCT upa.id) as total_submissions,
                       COUNT(DISTINCT CASE WHEN upa.status = 'approved' THEN upa.id END) as approved_submissions,
                       COALESCE(SUM(CASE WHEN t.type = 'credit' AND t.status = 'completed' THEN t.amount ELSE 0 END), 0) as total_earned
                FROM users u
                LEFT JOIN user_post_assignments upa ON u.id = upa.user_id
                LEFT JOIN transactions t ON u.id = t.user_id
                $where
                GROUP BY u.id
                $orderBy
            ");
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'users' => $users,
                'total' => count($users)
            ]);
            break;
            
        case 'get_recent_users':
            $limit = intval($_GET['limit'] ?? 10);
            
            $stmt = $db->prepare("
                SELECT u.id, u.username, u.email, u.created_at, u.wallet_balance
                FROM users u
                WHERE u.role = 'user'
                ORDER BY u.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'users' => $users]);
            break;
            
        case 'get_recent_withdrawals':

            $stmt = $db->prepare("
                SELECT 
                    w.id,
                    w.amount,
                    w.status,
                    w.created_at,
                    u.username
                FROM withdrawals w
                JOIN users u ON u.id = w.user_id
                ORDER BY w.created_at DESC
                LIMIT 5
            ");
            $stmt->execute();

            echo json_encode([
                'success' => true,
                'withdrawals' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);
            break;
            
        case 'get_user_details':
            $userId = intval($_GET['user_id'] ?? 0);
            if (!$userId) {
                throw new Exception("User ID is required");
            }
            
            $stmt = $db->prepare("
                SELECT u.*,
                       COUNT(DISTINCT upa.id) as total_submissions,
                       COUNT(DISTINCT CASE WHEN upa.status = 'approved' THEN upa.id END) as approved_submissions,
                       COUNT(DISTINCT w.id) as total_withdrawals,
                       COALESCE(SUM(CASE WHEN t.type = 'credit' AND t.status = 'completed' THEN t.amount ELSE 0 END), 0) as total_earned
                FROM users u
                LEFT JOIN user_post_assignments upa ON u.id = upa.user_id
                LEFT JOIN withdrawals w ON u.id = w.user_id
                LEFT JOIN transactions t ON u.id = t.user_id
                WHERE u.id = ?
                GROUP BY u.id
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception("User not found");
            }
            
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
            break;
            
        case 'ban_user':
            $userId = intval($_POST['user_id'] ?? 0);
            $reason = $_POST['reason'] ?? '';
            
            if (!$userId) {
                throw new Exception("User ID is required");
            }
            
            $result = $adminController->banUser($userId, $reason);
            echo json_encode($result);
            break;
            
        case 'unban_user':
            $userId = intval($_POST['user_id'] ?? 0);
            if (!$userId) {
                throw new Exception("User ID is required");
            }
            
            $result = $adminController->unbanUser($userId);
            echo json_encode($result);
            break;
            
        // ============ DASHBOARD STATS ============
        case 'get_dashboard_stats':
            $stats = $adminController->getDashboardStats();
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        case 'get_stats':
            $type = $_GET['type'] ?? '';
            if (!in_array($type, ['users', 'posts', 'withdrawals', 'submissions'])) {
                throw new Exception("Invalid stats type");
            }
            
            $stats = $adminController->getStats($type);
            if (!$stats) {
                throw new Exception("Failed to get stats");
            }
            
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        // ============ USER CREATION & MANAGEMENT ============
        case 'create_user':
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone = trim($_POST['phone'] ?? '');
            $walletBalance = floatval($_POST['wallet_balance'] ?? 0);
            $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
            
            if (empty($username) || empty($email) || empty($password)) {
                throw new Exception("Username, email, and password are required");
            }
            
            if (strlen($password) < 6) {
                throw new Exception("Password must be at least 6 characters");
            }
            
            // Check if user already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                throw new Exception("Email or username already exists");
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            // Create user
            $stmt = $db->prepare("
                INSERT INTO users (username, email, password, phone, wallet_balance, role, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $username,
                $email,
                $hashedPassword,
                $phone,
                $walletBalance,
                $isAdmin ? 'admin' : 'user'
            ]);
            
            $userId = $db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully!',
                'user_id' => $userId
            ]);
            break;
            
        case 'update_user':
            $userId = intval($_POST['id'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $walletBalance = floatval($_POST['wallet_balance'] ?? 0);
            $isBanned = isset($_POST['is_banned']) && $_POST['is_banned'] == 1 ? 1 : 0;
            $banReason = trim($_POST['ban_reason'] ?? '');
            $role = isset($_POST['role']) ? $_POST['role'] : 'user';
            $newPassword = $_POST['new_password'] ?? '';
            
            if (!$userId) {
                throw new Exception("User ID is required");
            }
            
            if (empty($username) || empty($email)) {
                throw new Exception("Username and email are required");
            }
            
            // Check if email/username already exists (for other users)
            $stmt = $db->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
            $stmt->execute([$email, $username, $userId]);
            if ($stmt->fetch()) {
                throw new Exception("Email or username already exists");
            }
            
            // Build update query
            $updateFields = "username = ?, email = ?, phone = ?, wallet_balance = ?, is_banned = ?, ban_reason = ?, role = ?, updated_at = NOW()";
            $params = [$username, $email, $phone, $walletBalance, $isBanned, $banReason, $role];
            
            // Add password if provided
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    throw new Exception("Password must be at least 6 characters");
                }
                $updateFields = "username = ?, email = ?, phone = ?, wallet_balance = ?, is_banned = ?, ban_reason = ?, role = ?, password = ?, updated_at = NOW()";
                $params = [$username, $email, $phone, $walletBalance, $isBanned, $banReason, $role, password_hash($newPassword, PASSWORD_BCRYPT)];
            }
            
            $params[] = $userId;
            
            $stmt = $db->prepare("UPDATE users SET $updateFields WHERE id = ?");
            $stmt->execute($params);
            
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully!'
            ]);
            break;
            
        case 'delete_user':
            $userId = intval($_POST['user_id'] ?? $_GET['user_id'] ?? 0);
            
            if (!$userId) {
                throw new Exception("User ID is required");
            }
            
            // Prevent deleting current admin
            if ($_SESSION['user_id'] == $userId) {
                throw new Exception("Cannot delete your own account");
            }
            
            $db->beginTransaction();
            try {
                // Delete user's withdrawals
                $stmt = $db->prepare("DELETE FROM withdrawals WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                // Delete user's post assignments
                $stmt = $db->prepare("DELETE FROM user_post_assignments WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                // Delete user's transactions
                $stmt = $db->prepare("DELETE FROM transactions WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                // Delete user
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                
                $db->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'User deleted successfully!'
                ]);
            } catch(Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch(Exception $e) {
    error_log('Admin API error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>