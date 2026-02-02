<?php
class Heartbeat {
    private $db;
    private $lockTimeout = 300; // 5 minutes in seconds
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Assign comment with locking
    public function assignComment($userId, $postId) {
        try {
            $this->db->beginTransaction();
            
            // 1. Check if user already has active assignment
            $stmt = $this->db->prepare("
                SELECT upa.*, 
                       TIMESTAMPDIFF(SECOND, upa.last_heartbeat, NOW()) as time_since_heartbeat
                FROM user_post_assignments upa
                WHERE upa.user_id = ? 
                AND upa.status = 'locked' 
                AND TIMESTAMPDIFF(SECOND, upa.last_heartbeat, NOW()) < ?
                LIMIT 1
            ");
            $stmt->execute([$userId, $this->lockTimeout]);
            $existingAssignment = $stmt->fetch();
            
            if ($existingAssignment) {
                $this->db->commit();
                // Return existing assignment if still active
                $commentStmt = $this->db->prepare("
                    SELECT c.* FROM comments c 
                    WHERE c.id = ?
                ");
                $commentStmt->execute([$existingAssignment['comment_id']]);
                $comment = $commentStmt->fetch();
                
                $existingAssignment['comment_text'] = $comment['comment_text'] ?? '';
                return [
                    'success' => true,
                    'already_assigned' => true,
                    'assignment' => $existingAssignment
                ];
            }
            
            // 2. Clean up expired locks for other users (optional but good practice)
            $this->cleanupExpiredLocks();
            
            // 3. Find unused comment for this post (with row locking)
            $commentStmt = $this->db->prepare("
                SELECT c.id, c.comment_text 
                FROM comments c
                WHERE c.post_id = ? 
                AND c.is_used = FALSE
                AND c.id NOT IN (
                    SELECT comment_id FROM user_post_assignments 
                    WHERE status = 'locked' 
                    AND TIMESTAMPDIFF(SECOND, last_heartbeat, NOW()) < ?
                )
                LIMIT 1
                FOR UPDATE
            ");
            $commentStmt->execute([$postId, $this->lockTimeout]);
            $comment = $commentStmt->fetch();
            
            if (!$comment) {
                $this->db->commit();
                return [
                    'success' => false,
                    'message' => 'No comments available for this post'
                ];
            }
            
            // 4. Create assignment with locking
            $assignStmt = $this->db->prepare("
                INSERT INTO user_post_assignments 
                (user_id, post_id, comment_id, assigned_time, last_heartbeat, status)
                VALUES (?, ?, ?, NOW(), NOW(), 'locked')
            ");
            $assignStmt->execute([$userId, $postId, $comment['id']]);
            $assignmentId = $this->db->lastInsertId();
            
            // 5. Update user last_active
            $userStmt = $this->db->prepare("
                UPDATE users 
                SET last_active = NOW() 
                WHERE id = ?
            ");
            $userStmt->execute([$userId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'assignment' => [
                    'id' => $assignmentId,
                    'user_id' => $userId,
                    'post_id' => $postId,
                    'comment_id' => $comment['id'],
                    'comment_text' => $comment['comment_text'],
                    'assigned_time' => date('Y-m-d H:i:s'),
                    'last_heartbeat' => date('Y-m-d H:i:s'),
                    'status' => 'locked'
                ]
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'Error assigning comment: ' . $e->getMessage()
            ];
        }
    }
    
    // Update heartbeat to keep lock alive
    public function updateHeartbeat($assignmentId, $userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE user_post_assignments 
                SET last_heartbeat = NOW()
                WHERE id = ? AND user_id = ? AND status = 'locked'
            ");
            
            $success = $stmt->execute([$assignmentId, $userId]);
            return ['success' => $success];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Cleanup expired locks (run via cron every minute)
    public function cleanupExpiredLocks() {
        try {
            // Mark expired assignments as expired
            $stmt = $this->db->prepare("
                UPDATE user_post_assignments 
                SET status = 'expired'
                WHERE status = 'locked' 
                AND TIMESTAMPDIFF(SECOND, last_heartbeat, NOW()) > ?
            ");
            $stmt->execute([$this->lockTimeout]);
            
            return true;
        } catch (Exception $e) {
            error_log('Cleanup error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Release/unlock comment when user leaves or closes page
    public function releaseComment($assignmentId, $userId) {
        try {
            // Don't delete, just mark as expired so comment becomes available
            $stmt = $this->db->prepare("
                UPDATE user_post_assignments 
                SET status = 'released'
                WHERE id = ? AND user_id = ? AND status = 'locked'
            ");
            
            return $stmt->execute([$assignmentId, $userId]);
            
        } catch (Exception $e) {
            error_log('Release error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Get lock timeout value
    public function getLockTimeout() {
        return $this->lockTimeout;
    }
}
?>