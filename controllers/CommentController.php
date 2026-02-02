<?php
require_once __DIR__ . '/../config/database.php';

class CommentController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function addComment($postId, $commentText) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO comments (post_id, comment_text, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$postId, trim($commentText)]);
            
            // Update post comment count
            $updateStmt = $this->db->prepare("
                UPDATE posts 
                SET total_comments = total_comments + 1 
                WHERE id = ?
            ");
            $updateStmt->execute([$postId]);
            
            return [
                'success' => true, 
                'comment_id' => $this->db->lastInsertId(),
                'message' => 'Comment added successfully'
            ];
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateComment($commentId, $newText) {
        try {
            $stmt = $this->db->prepare("
                UPDATE comments 
                SET comment_text = ?, updated_at = NOW()
                WHERE id = ? AND is_used = FALSE
            ");
            $stmt->execute([trim($newText), $commentId]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Comment updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Cannot edit used comment'];
            }
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function deleteComment($commentId) {
        try {
            // Check if comment is used
            $checkStmt = $this->db->prepare("
                SELECT is_used, post_id FROM comments WHERE id = ?
            ");
            $checkStmt->execute([$commentId]);
            $comment = $checkStmt->fetch();
            
            if ($comment['is_used']) {
                return ['success' => false, 'message' => 'Cannot delete used comment'];
            }
            
            // Delete comment
            $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$commentId]);
            
            // Update post comment count
            $updateStmt = $this->db->prepare("
                UPDATE posts 
                SET total_comments = GREATEST(0, total_comments - 1) 
                WHERE id = ?
            ");
            $updateStmt->execute([$comment['post_id']]);
            
            return ['success' => true, 'message' => 'Comment deleted successfully'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getCommentStats($postId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_used THEN 1 ELSE 0 END) as used,
                SUM(CASE WHEN is_used THEN 0 ELSE 1 END) as available,
                (SELECT COUNT(*) FROM user_post_assignments 
                 WHERE post_id = ? AND status = 'locked') as locked
            FROM comments 
            WHERE post_id = ?
        ");
        $stmt->execute([$postId, $postId]);
        return $stmt->fetch();
    }
    
    public function getRecentlyUsedComments($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT c.comment_text, c.is_used, p.app_name, u.username, upa.submitted_time
            FROM comments c
            JOIN posts p ON c.post_id = p.id
            LEFT JOIN user_post_assignments upa ON c.id = upa.comment_id
            LEFT JOIN users u ON upa.user_id = u.id
            WHERE c.is_used = TRUE
            ORDER BY upa.submitted_time DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>