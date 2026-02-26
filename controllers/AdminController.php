<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Validation.php';
require_once __DIR__ . '/../helpers/PushNotification.php';

class AdminController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Post Management
    public function createPost($data) {
        try {
            $validation = new Validation();
            $push = new PushNotification();
            $rules = [
                'app_link' => 'required|url',
                'app_name' => 'required|min:3|max:200',
                'price' => 'required|numeric|min:1|max:100',
                'comments' => 'required|array|min:1'
            ];
            
            if (!$validation->validate($data, $rules)) {
                error_log('Validation failed: ' . json_encode($validation->errors()));
                return ['success' => false, 'errors' => $validation->errors()];
            }
            
            $this->db->beginTransaction();
            
            // Insert post
            $stmt = $this->db->prepare("
                INSERT INTO posts (admin_id, app_link, app_name, price, status, created_at)
                VALUES (?, ?, ?, ?, 'active', NOW())
            ");
            
            $userId = $_SESSION['user_id'] ?? 0;
            $stmt->execute([
                $userId,
                $data['app_link'],
                $data['app_name'],
                floatval($data['price'])
            ]);
            
            $postId = $this->db->lastInsertId();
            
            if (!$postId) {
                throw new Exception("Failed to create post");
            }
            
            // Insert comments
            $commentStmt = $this->db->prepare("
                INSERT INTO comments (post_id, comment_text, is_used, created_at)
                VALUES (?, ?, FALSE, NOW())
            ");
            
            $totalComments = 0;
            foreach ($data['comments'] as $comment) {
                $comment = trim($comment);
                if (!empty($comment) && strlen($comment) >= 3) {
                    try {
                        $commentStmt->execute([$postId, $comment]);
                        $totalComments++;
                    } catch (Exception $e) {
                        error_log("Comment insert error: " . $e->getMessage());
                        continue; // Skip bad comment
                    }
                }
            }
            
            if ($totalComments === 0) {
                throw new Exception("No valid comments provided");
            }
            
            // Update post with comment count
            $updateStmt = $this->db->prepare("
                UPDATE posts SET total_comments = ? WHERE id = ?
            ");
            $updateStmt->execute([$totalComments, $postId]);
            
            $this->db->commit();
            $push->sendToAll(
            "🔥 New Task Available",
            $data['app_name'] . " - ₹" . $data['price'],
            [
            "type" => "new_post",
            "post_id" => $postId
            ]
            );
            return [
                'success' => true,
                'post_id' => $postId,
                'comments_added' => $totalComments,
                'message' => "Post created successfully with $totalComments comments!"
            ];
            
        } catch(Exception $e) {
            $this->db->rollBack();
            error_log('Post creation error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create post: ' . $e->getMessage()];
        }
    }
    public function getPost($postId) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
    public function updatePost($postId, $data) {
        try {
            $validation = new Validation();
            
            $rules = [
                'app_link' => 'required|url',
                'app_name' => 'required|min:3|max:200',
                'price' => 'required|numeric|min:1|max:100',
                'status' => 'required|in:active,inactive'
            ];
            
            if (!$validation->validate($data, $rules)) {
                return ['success' => false, 'errors' => $validation->errors()];
            }
            
            // Verify post exists
            $checkStmt = $this->db->prepare("SELECT id FROM posts WHERE id = ?");
            $checkStmt->execute([$postId]);
            if (!$checkStmt->fetch()) {
                return ['success' => false, 'message' => 'Post not found'];
            }
            
            $stmt = $this->db->prepare("
                UPDATE posts 
                SET app_link = ?, app_name = ?, price = ?, status = ?, created_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $data['app_link'],
                $data['app_name'],
                floatval($data['price']),
                $data['status'],
                $postId
            ]);
            
            if (!$result) {
                throw new Exception("Failed to update post");
            }
            
            return ['success' => true, 'message' => 'Post updated successfully!'];
            
        } catch(Exception $e) {
            error_log('Post update error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update post: ' . $e->getMessage()];
        }
    }
    
    public function addCommentToPost($postId, $commentText) {
        try {
            $commentText = trim($commentText);
            $push = new PushNotification();
            
            if (empty($commentText) || strlen($commentText) < 3) {
                return ['success' => false, 'message' => 'Comment must be at least 3 characters'];
            }
            
            // Verify post exists
            $checkStmt = $this->db->prepare("SELECT id FROM posts WHERE id = ?");
            $checkStmt->execute([$postId]);
            if (!$checkStmt->fetch()) {
                return ['success' => false, 'message' => 'Post not found'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO comments (post_id, comment_text, is_used, created_at)
                VALUES (?, ?, FALSE, NOW())
            ");
            
            $result = $stmt->execute([$postId, $commentText]);
            
            if (!$result) {
                throw new Exception("Failed to insert comment");
            }
            
            // Update post comment count
            $updateStmt = $this->db->prepare("
                UPDATE posts 
                SET total_comments = total_comments + 1 
                WHERE id = ?
            ");
            $updateStmt->execute([$postId]);
            // 🔔 GET POST DETAILS FOR NOTIFICATION
            $postStmt = $this->db->prepare("SELECT app_name, price FROM posts WHERE id = ?");
            $postStmt->execute([$postId]);
            $post = $postStmt->fetch(PDO::FETCH_ASSOC);

            $push->sendToAll(
                "💬 Comments Updated",
                $post['app_name'] . " - ₹" . $post['price'],
                [
                    "type" => "comment_update",
                    "post_id" => $postId
                ]
            );
            return [
                'success' => true,
                'comment_id' => $this->db->lastInsertId(),
                'message' => 'Comment added successfully'
            ];
            
        } catch(Exception $e) {
            error_log('Comment add error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add comment: ' . $e->getMessage()];
        }
    }
    
    // Withdrawal Management
    public function getPendingWithdrawals() {
        try {
            $stmt = $this->db->prepare("
                SELECT w.*, u.username, u.email, u.phone
                FROM withdrawals w
                JOIN users u ON w.user_id = u.id
                WHERE w.status = 'pending'
                ORDER BY w.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log('Get pending withdrawals error: ' . $e->getMessage());
            return [];
        }
    }
    
    public function processWithdrawal($withdrawalId, $action, $notes = '', $refund = true) {
        try {
            $this->db->beginTransaction();
            
            // Get withdrawal details
            $stmt = $this->db->prepare("
                SELECT w.*, u.wallet_balance, u.id as user_id
                FROM withdrawals w
                JOIN users u ON w.user_id = u.id
                WHERE w.id = ?
            ");
            $stmt->execute([$withdrawalId]);
            $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$withdrawal) {
                throw new Exception("Withdrawal not found");
            }
            
            $newStatus = '';
            $transactionStatus = '';
            
            if ($action === 'approve') {
                $newStatus = 'approved';
                $transactionStatus = 'completed';
            } elseif ($action === 'reject') {
                $newStatus = 'failed';
                $transactionStatus = 'failed';
                
                // Refund amount if requested
                if ($refund) {
                    $refundAmount = floatval($withdrawal['amount']);
                    $newBalance = floatval($withdrawal['wallet_balance']) + $refundAmount;
                    
                    // Update user balance
                    $updateStmt = $this->db->prepare("
                        UPDATE users SET wallet_balance = ? WHERE id = ?
                    ");
                    $updateStmt->execute([$newBalance, $withdrawal['user_id']]);
                    
                    // Create refund transaction
                    $transStmt = $this->db->prepare("
                        INSERT INTO transactions 
                        (user_id, type, amount, balance_after, description, reference_id, reference_type, status, created_at)
                        VALUES (?, 'credit', ?, ?, 'Withdrawal Refund', ?, 'withdrawal', 'completed', NOW())
                    ");
                    $transStmt->execute([
                        $withdrawal['user_id'],
                        $refundAmount,
                        $newBalance,
                        $withdrawalId
                    ]);
                }
            } else {
                throw new Exception("Invalid action: " . $action);
            }
            
            // Update withdrawal
            $updateWithdrawal = $this->db->prepare("
                UPDATE withdrawals 
                SET status = ?, admin_notes = ?, processed_by = ?, processed_at = NOW()
                WHERE id = ?
            ");
            $updateWithdrawal->execute([
                $newStatus,
                $notes,
                $_SESSION['user_id'] ?? 0,
                $withdrawalId
            ]);
            
            // Update transaction status
            $updateTrans = $this->db->prepare("
                UPDATE transactions 
                SET status = ?, updated_at = NOW()
                WHERE reference_id = ? AND reference_type = 'withdrawal'
            ");
            $updateTrans->execute([$transactionStatus, $withdrawalId]);
            
            $this->db->commit();
            
            return ['success' => true, 'message' => "Withdrawal {$action}ed successfully"];
            
        } catch(Exception $e) {
            $this->db->rollBack();
            error_log('Process withdrawal error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // User Management
    public function banUser($userId, $reason) {
        try {
            // Verify user exists
            $checkStmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
            $checkStmt->execute([$userId]);
            if (!$checkStmt->fetch()) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET is_banned = TRUE, ban_reason = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$reason, $userId]);
            
            // Release any locked comments
            $releaseStmt = $this->db->prepare("
                UPDATE user_post_assignments 
                SET status = 'released'
                WHERE user_id = ? AND status = 'locked'
            ");
            $releaseStmt->execute([$userId]);
            
            return ['success' => true, 'message' => 'User banned successfully'];
        } catch(Exception $e) {
            error_log('Ban user error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function unbanUser($userId) {
        try {
            // Verify user exists
            $checkStmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
            $checkStmt->execute([$userId]);
            if (!$checkStmt->fetch()) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET is_banned = FALSE, ban_reason = NULL, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            return ['success' => true, 'message' => 'User unbanned successfully'];
        } catch(Exception $e) {
            error_log('Unban user error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Dashboard Stats
    public function getDashboardStats() {
        try {
            $stats = [];
            
            // Total users
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
            $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
            
            // Total posts
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM posts WHERE status = 'active'");
            $stats['total_posts'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
            
            // Total withdrawals
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM withdrawals");
            $stats['total_withdrawals'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
            
            // Pending withdrawals
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM withdrawals WHERE status = 'pending'");
            $stats['pending_withdrawals'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
            
            // Total earnings (sum of approved withdrawals)
            $stmt = $this->db->query("SELECT COALESCE(SUM(final_amount), 0) as total FROM withdrawals WHERE status = 'approved'");
            $stats['total_earnings'] = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // Pending earnings (sum of pending withdrawals)
            $stmt = $this->db->query("SELECT COALESCE(SUM(amount), 0) as total FROM withdrawals WHERE status = 'pending'");
            $stats['pending_earnings'] = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
            
            // Pending submissions
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM user_post_assignments WHERE status = 'submitted'");
            $stats['pending_submissions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
            
            // Total comments available
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM comments WHERE is_used = FALSE");
            $stats['available_comments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
            
            // Recent activities
            $stmt = $this->db->prepare("
                SELECT 'withdrawal' as type, CONCAT('Withdrawal: ₹', amount) as description, created_at 
                FROM withdrawals 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $stmt->execute();
            $stats['recent_activities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch(Exception $e) {
            error_log('Dashboard stats error: ' . $e->getMessage());
            return [
                'total_users' => 0,
                'total_posts' => 0,
                'total_withdrawals' => 0,
                'pending_withdrawals' => 0,
                'total_earnings' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getStats($type) {
        try {
            switch ($type) {
                case 'users':
                    $stmt = $this->db->query("
                        SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN is_banned = TRUE THEN 1 ELSE 0 END) as banned_users,
                            SUM(CASE WHEN is_banned = FALSE THEN 1 ELSE 0 END) as active_users
                        FROM users WHERE role = 'user'
                    ");
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                    
                case 'posts':
                    $stmt = $this->db->query("
                        SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                            COALESCE(SUM(total_comments), 0) as total_comments
                        FROM posts
                    ");
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                    
                case 'withdrawals':
                    $stmt = $this->db->query("
                        SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                            COALESCE(SUM(CASE WHEN status = 'approved' THEN final_amount ELSE 0 END), 0) as total_paid
                        FROM withdrawals
                    ");
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                    
                default:
                    return null;
            }
        } catch(Exception $e) {
            error_log('Get stats error: ' . $e->getMessage());
            return null;
        }
    }
}
?>