<?php
// api/admin/activity-history.php
// Login history aur sab activities

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

header('Content-Type: application/json');

$session = new Session();
$auth = new AuthController();

// Check if admin
if (!$auth->isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Limit kya hai
    $limit = $_GET['limit'] ?? 500;
    $offset = $_GET['offset'] ?? 0;
    
    // Login history with user details
    $stmt = $db->prepare("
        SELECT 
            lh.id,
            lh.user_id,
            COALESCE(u.username, 'Unknown') as username,
            COALESCE(u.email, 'Unknown') as email,
            lh.ip_address,
            lh.user_agent,
            lh.status,
            lh.login_time,
            CASE 
                WHEN u.is_banned = 1 THEN 'BANNED'
                WHEN lh.status = 'failed' THEN 'FAILED_LOGIN'
                WHEN lh.status = 'success' THEN 'SUCCESS'
                ELSE lh.status
            END as activity_type
        FROM login_history lh
        LEFT JOIN users u ON lh.user_id = u.id
        ORDER BY lh.login_time DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$limit, $offset]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Total count
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM login_history");
    $countStmt->execute();
    $total = $countStmt->fetch()['total'];
    
    echo json_encode([
        'success' => true,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset,
        'data' => $activities
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>