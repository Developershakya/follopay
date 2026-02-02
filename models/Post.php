<?php
class Post {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($adminId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO posts (admin_id, app_link, app_name, price, status, created_at)
            VALUES (?, ?, ?, ?, 'active', NOW())
        ");
        
        return $stmt->execute([
            $adminId,
            $data['app_link'],
            $data['app_name'],
            $data['price']
        ]);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username as admin_name
            FROM posts p
            LEFT JOIN users u ON p.admin_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $allowedFields = ['app_link', 'app_name', 'price', 'status'];
        $set = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $set[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($set)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE posts SET " . implode(', ', $set) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        // Note: Comments will be deleted via CASCADE
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getAllPosts($page = 1, $limit = 20, $status = null) {
        $offset = ($page - 1) * $limit;
        $where = '';
        $params = [];
        
        if ($status) {
            $where = "WHERE p.status = ?";
            $params[] = $status;
        }
        
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   u.username as admin_name,
                   COUNT(c.id) as total_comments,
                   SUM(CASE WHEN c.is_used THEN 1 ELSE 0 END) as used_comments
            FROM posts p
            LEFT JOIN users u ON p.admin_id = u.id
            LEFT JOIN comments c ON p.id = c.post_id
            $where
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function countAllPosts($status = null) {
        $where = '';
        $params = [];
        
        if ($status) {
            $where = "WHERE status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT COUNT(*) as count FROM posts $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch()['count'];
    }
    
    public function getActivePostsForUser($userId, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND is_used = FALSE) as available_comments,
                   IF(EXISTS(
                       SELECT 1 FROM user_post_assignments 
                       WHERE user_id = ? AND post_id = p.id 
                       AND status IN ('submitted', 'approved')
                   ), 1, 0) AS already_done
            FROM posts p
            WHERE p.status = 'active'
            HAVING available_comments > 0 AND already_done = 0
            ORDER BY p.created_at DESC
            LIMIT ?
        ");
        
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getPostStats($postId) {
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                COUNT(DISTINCT c.id) as total_comments,
                SUM(CASE WHEN c.is_used THEN 1 ELSE 0 END) as used_comments,
                COUNT(DISTINCT upa.id) as total_assignments,
                SUM(CASE WHEN upa.status = 'approved' THEN 1 ELSE 0 END) as approved_assignments,
                SUM(CASE WHEN upa.status = 'approved' THEN p.price ELSE 0 END) as total_payout
            FROM posts p
            LEFT JOIN comments c ON p.id = c.post_id
            LEFT JOIN user_post_assignments upa ON p.id = upa.post_id
            WHERE p.id = ?
            GROUP BY p.id
        ");
        
        $stmt->execute([$postId]);
        return $stmt->fetch();
    }
    
    public function getRecentPosts($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   u.username as admin_name,
                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND is_used = FALSE) as available_comments
            FROM posts p
            LEFT JOIN users u ON p.admin_id = u.id
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC
            LIMIT ?
        ");
        
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function incrementUsedComments($postId) {
        $stmt = $this->db->prepare("
            UPDATE posts 
            SET used_comments = used_comments + 1 
            WHERE id = ?
        ");
        return $stmt->execute([$postId]);
    }
}
?>
