<?php
// api/admin/live-activity.php
// Real-time user activity

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

header('Content-Type: application/json');

$auth = new AuthController();
if (!$auth->isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Last 10 activities (live)
    $stmt = $db->prepare("
        SELECT 
            lh.id,
            u.username,
            u.email,
            lh.action_type as action,
            lh.status,
            lh.ip_address,
            lh.login_time as timestamp,
            (SELECT COUNT(DISTINCT user_id) FROM device_tracking WHERE device_fingerprint = lh.device_fingerprint AND is_active = 1) as linked_accounts
        FROM login_history lh
        LEFT JOIN users u ON lh.user_id = u.id
        ORDER BY lh.login_time DESC
        LIMIT 10
    ");
    
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count active users (logged in within last 5 minutes)
    $activeStmt = $db->prepare("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM login_history 
        WHERE status = 'success' 
        AND login_time > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $activeStmt->execute();
    $activeCount = $activeStmt->fetch()['count'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'data' => $activities,
        'active_count' => $activeCount
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>