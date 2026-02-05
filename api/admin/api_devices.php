<?php
// api/admin/devices.php

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
    
    // Total devices
    $devicesStmt = $db->prepare("SELECT COUNT(*) as count FROM device_tracking WHERE is_active = 1");
    $devicesStmt->execute();
    $totalDevices = $devicesStmt->fetch()['count'] ?? 0;
    
    // Multi-device users
    $multiStmt = $db->prepare("
        SELECT COUNT(*) as count FROM (
            SELECT user_id FROM device_tracking GROUP BY user_id HAVING COUNT(DISTINCT device_fingerprint) > 1
        ) as t
    ");
    $multiStmt->execute();
    $multiDeviceUsers = $multiStmt->fetch()['count'] ?? 0;
    
    // Blocked devices
    $blockedStmt = $db->prepare("SELECT COUNT(*) as count FROM device_blocklist WHERE is_active = 1");
    $blockedStmt->execute();
    $blockedDevices = $blockedStmt->fetch()['count'] ?? 0;
    
    // Suspicious devices
    $suspStmt = $db->prepare("
        SELECT 
            dt.device_fingerprint as fingerprint,
            COUNT(DISTINCT dt.user_id) as linked_accounts,
            COUNT(DISTINCT lh.ip_address) as unique_ips,
            (SELECT COUNT(*) FROM login_history WHERE device_fingerprint = dt.device_fingerprint AND status = 'failed') as failed_logins
        FROM device_tracking dt
        LEFT JOIN login_history lh ON dt.device_fingerprint = lh.device_fingerprint
        GROUP BY dt.device_fingerprint
        HAVING linked_accounts > 1 OR failed_logins > 5
        ORDER BY linked_accounts DESC
        LIMIT 10
    ");
    
    $suspStmt->execute();
    $suspiciousDevices = $suspStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'total_devices' => $totalDevices,
        'multi_device_users' => $multiDeviceUsers,
        'blocked_devices' => $blockedDevices,
        'suspicious_devices' => $suspiciousDevices
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>