<?php
// api/admin/suspicious-users.php
// Yeh file suspicious users ki list return karega

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
    
    // Multiple devices/IPs wale users
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.username,
            u.email,
            u.phone,
            u.created_at,
            u.is_banned,
            COUNT(DISTINCT dt.device_fingerprint) as unique_devices,
            COUNT(DISTINCT dt.ip_address) as unique_ips,
            GROUP_CONCAT(DISTINCT dt.ip_address SEPARATOR ',') as ip_addresses,
            GROUP_CONCAT(DISTINCT DATE_FORMAT(dt.created_at, '%Y-%m-%d') SEPARATOR ',') as login_dates,
            MAX(dt.created_at) as last_device_login,
            (SELECT COUNT(*) FROM users u2 WHERE u2.phone = u.phone AND u2.id != u.id) as phone_duplicates
        FROM users u
        LEFT JOIN device_tracking dt ON u.id = dt.user_id
        GROUP BY u.id
        HAVING 
            unique_devices > 1 
            OR unique_ips > 1 
            OR phone_duplicates > 0
        ORDER BY unique_devices DESC, last_device_login DESC
    ");
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($users),
        'data' => $users
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>