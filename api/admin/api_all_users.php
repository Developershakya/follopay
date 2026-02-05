<?php
// api/admin/all-users.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Session.php';
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
            u.is_banned,
            u.last_active,
            u.created_at,
            COUNT(DISTINCT dt.device_fingerprint) as devices,
            CASE 
                WHEN u.last_active IS NULL THEN 999999
                WHEN u.last_active > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1
                WHEN u.last_active > DATE_SUB(NOW(), INTERVAL 30 MINUTE) THEN 2
                ELSE 3
            END as status_code,
            TIMESTAMPDIFF(MINUTE, u.last_active, NOW()) as last_active_minutes
        FROM users u
        LEFT JOIN device_tracking dt ON u.id = dt.user_id
        GROUP BY u.id
        ORDER BY u.last_active DESC, u.created_at DESC
    ");
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add is_online status
    $users = array_map(function($user) {
        $user['is_online'] = $user['last_active_minutes'] < 5;
        return $user;
    }, $users);
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>