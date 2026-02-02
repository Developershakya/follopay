<?php
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../middleware/AuthMiddleware.php';

header('Content-Type: application/json');

try {
    // Check authentication
    AuthMiddleware::handle();
    
    $db = Database::getInstance()->getConnection();
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        throw new Exception('User not authenticated');
    }
    
    $action = $_REQUEST['action'] ?? '';
    
    switch($action) {
        
case 'get_stats':
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT upa.id) AS tasks_completed,
            COUNT(DISTINCT CASE WHEN upa.status = 'approved' THEN upa.id END) AS tasks_approved,

            (
                SELECT COALESCE(SUM(t.amount), 0)
                FROM transactions t
                WHERE 
                    t.user_id = u.id
                    AND t.type = 'credit'
                    AND t.status = 'completed'
            ) AS total_earned,

            CASE 
                WHEN COUNT(DISTINCT upa.id) = 0 THEN 0
                ELSE ROUND(
                    (COUNT(DISTINCT CASE WHEN upa.status = 'approved' THEN upa.id END) 
                    / COUNT(DISTINCT upa.id)) * 100
                )
            END AS success_rate

        FROM users u
        LEFT JOIN user_post_assignments upa 
            ON u.id = upa.user_id
        WHERE u.id = ?
    ");

    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'tasks_completed' => (int)$stats['tasks_completed'],
            'tasks_approved' => (int)$stats['tasks_approved'],
            'total_earned' => (float)$stats['total_earned'], // ✅ only amount sum
            'success_rate' => (int)$stats['success_rate']
        ]
    ]);
    break;

            
        case 'get_activity':
            $limit = intval($_GET['limit'] ?? 10);
            
            // Get transactions as activity
            $stmt = $db->prepare("
                SELECT 
                    id,
                    type,
                    amount,
                    description,
                    created_at,
                    CASE 
                        WHEN type = 'credit' THEN amount
                        WHEN type = 'debit' THEN -amount
                        ELSE 0
                    END as signed_amount,
                    CASE
                        WHEN description LIKE '%withdrawal%' THEN 'withdrawal'
                        WHEN type = 'credit' THEN 'credit'
                        WHEN type = 'debit' THEN 'debit'
                        ELSE 'transaction'
                    END as activity_type
                FROM transactions
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format for display
            $formatted = [];
            foreach ($transactions as $activity) {
                $formatted[] = [
                    'type' => $activity['activity_type'] ?? $activity['type'] ?? 'transaction',
                    'description' => $activity['description'] ?? 'Activity',
                    'amount' => $activity['signed_amount'] ?? $activity['amount'] ?? 0,
                    'created_at' => $activity['created_at']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'activity' => $formatted
            ]);
            break;
            
        case 'update_profile':
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            
            if (empty($username) || empty($email)) {
                throw new Exception('Username and email are required');
            }
            
            // Check if email is unique (excluding current user)
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Email already in use');
            }
            
            // Check if username is unique (excluding current user)
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Username already taken');
            }
            
            // Update profile
            $stmt = $db->prepare("
                UPDATE users
                SET username = ?, email = ?, phone = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$username, $email, $phone, $userId]);
            
            // Update session
            $_SESSION['username'] = $username;
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
            break;
            
        case 'change_password':
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword)) {
                throw new Exception('All password fields are required');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match');
            }
            
            if (strlen($newPassword) < 6) {
                throw new Exception('New password must be at least 6 characters');
            }
            
            // Get current password hash from database
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Verify current password - THIS IS THE KEY VALIDATION
            if (!password_verify($currentPassword, $user['password'])) {
                throw new Exception('Current password is incorrect');
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // Update password in database
            $stmt = $db->prepare("
                UPDATE users
                SET password = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$hashedPassword, $userId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>