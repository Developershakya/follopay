<?php
class Comment {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($postId, $commentText) {
        $stmt = $this->db->prepare("
            INSERT INTO comments (post_id, comment_text, created_at)
            VALUES (?, ?, NOW())
        ]);
        
        return $stmt->execute([$postId, trim($commentText)]);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, p.app_name, p.app_link
            FROM comments c
            JOIN posts p ON c.post_id = p.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByPost($postId, $onlyUnused = false) {
        $where = $onlyUnused ? "AND c.is_used = FALSE" : "";
        
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   CASE WHEN c.is_used THEN 'Used' ELSE 'Available' END as status_text,
                   (SELECT username FROM users u 
                    JOIN user_post_assignments upa ON u.id = upa.user_id
                    WHERE upa.comment_id = c.id AND upa.status = 'approved'
                    LIMIT 1) as used_by
            FROM comments c
            WHERE c.post_id = ? $where
            ORDER BY c.is_used, c.created_at DESC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }
    
    public function update($id, $commentText) {
        $stmt = $this->db->prepare("
            UPDATE comments 
            SET comment_text = ?, updated_at = NOW()
            WHERE id = ? AND is_used = FALSE
        ");
        
        return $stmt->execute([trim($commentText), $id]);
    }
    
    public function markAsUsed($id) {
        $stmt = $this->db->prepare("
            UPDATE comments 
            SET is_used = TRUE, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$id]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ? AND is_used = FALSE");
        return $stmt->execute([$id]);
    }
    
    public function getRandomUnusedComment($postId) {
        $stmt = $this->db->prepare("
            SELECT c.* 
            FROM comments c
            WHERE c.post_id = ? AND c.is_used = FALSE
            AND NOT EXISTS (
                SELECT 1 FROM user_post_assignments upa
                WHERE upa.comment_id = c.id AND upa.status = 'locked'
                AND TIMESTAMPDIFF(SECOND, upa.last_heartbeat, NOW()) < ?
            )
            ORDER BY RAND()
            LIMIT 1
        ");
        
        $stmt->execute([$postId, COMMENT_LOCK_TIME]);
        return $stmt->fetch();
    }
    
    public function getCommentUsageStats() {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(c.created_at) as date,
                COUNT(*) as total_comments,
                SUM(CASE WHEN c.is_used THEN 1 ELSE 0 END) as used_comments,
                ROUND(100.0 * SUM(CASE WHEN c.is_used THEN 1 ELSE 0 END) / COUNT(*), 2) as usage_percentage
            FROM comments c
            WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(c.created_at)
            ORDER BY date DESC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getCommentsByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT c.*, p.app_name, upa.status, upa.submitted_time
            FROM comments c
            JOIN user_post_assignments upa ON c.id = upa.comment_id
            JOIN posts p ON c.post_id = p.id
            WHERE upa.user_id = ?
            ORDER BY upa.submitted_time DESC
        ]);
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function countUnusedComments($postId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM comments 
            WHERE post_id = ? AND is_used = FALSE
        ");
        $stmt->execute([$postId]);
        return $stmt->fetch()['count'];
    }
    
    public function cleanupOrphanedComments() {
        // Remove comments from deleted posts
        $stmt = $this->db->prepare("
            DELETE c FROM comments c
            LEFT JOIN posts p ON c.post_id = p.id
            WHERE p.id IS NULL
        ");
        return $stmt->execute();
    }
}
?>