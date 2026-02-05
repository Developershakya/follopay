<?php
// api/admin/suspicious.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

header('Content-Type: application/json');

$auth = new AuthController();
if (!$auth->isAdmin()) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.username,
            u.email,
            COUNT(DISTINCT dt.device_fingerprint) as devices,
            COUNT(DISTINCT lh.ip_address) as unique_ips,
            (SELECT COUNT(*) FROM login_history WHERE user_id = u.id AND status = 'failed' AND login_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as failed_logins
        FROM users u
        LEFT JOIN device_tracking dt ON u.id = dt.user_id
        LEFT JOIN login_history lh ON u.id = lh.user_id
        GROUP BY u.id
        HAVING devices > 1 OR unique_ips > 3 OR failed_logins > 5
        ORDER BY devices DESC, failed_logins DESC
    ");
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>