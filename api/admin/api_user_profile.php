<?php
// api/admin/user-profile.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

header('Content-Type: application/json');

$auth = new AuthController();
if (!$auth->isAdmin()) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_GET['id'] ?? 0;

try {
    $db = Database::getInstance()->getConnection();
    
    // User info
    $userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Devices
    $devicesStmt = $db->prepare("
        SELECT * FROM device_tracking 
        WHERE user_id = ? 
        ORDER BY updated_at DESC
    ");
    $devicesStmt->execute([$userId]);
    $devices = $devicesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent logins
    $loginsStmt = $db->prepare("
        SELECT * FROM login_history 
        WHERE user_id = ? 
        ORDER BY login_time DESC 
        LIMIT 5
    ");
    $loginsStmt->execute([$userId]);
    $logins = $loginsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'devices' => $devices,
        'logins' => $logins
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>