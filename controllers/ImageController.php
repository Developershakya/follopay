<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cloudinary.php';

class ImageController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all screenshots with filters
     */
    public function getAllScreenshots($filters = []) {
        try {
            $sql = "
                SELECT 
                    upa.id,
                    upa.screenshot_path,
                    upa.cloudinary_public_id,
                    upa.status,
                    upa.submitted_time,
                    upa.assigned_time,
                    upa.reject_reason,
                    u.username,
                    u.email,
                    p.app_name,
                    p.app_link,
                    p.price,
                    c.comment_text,
                    c.id as comment_id
                FROM user_post_assignments upa
                JOIN users u ON upa.user_id = u.id
                JOIN posts p ON upa.post_id = p.id
                LEFT JOIN comments c ON upa.comment_id = c.id
                WHERE upa.screenshot_path IS NOT NULL
                AND upa.screenshot_path != ''
            ";
            
            $params = [];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $sql .= " AND upa.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(upa.submitted_time) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(upa.submitted_time) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['username'])) {
                $sql .= " AND u.username LIKE ?";
                $params[] = '%' . $filters['username'] . '%';
            }
            
            if (!empty($filters['app_name'])) {
                $sql .= " AND p.app_name LIKE ?";
                $params[] = '%' . $filters['app_name'] . '%';
            }
            
            $sql .= " ORDER BY upa.submitted_time DESC";
            
            // Add pagination if provided
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT " . intval($filters['limit']);
                
                if (!empty($filters['offset'])) {
                    $sql .= " OFFSET " . intval($filters['offset']);
                }
            }
            
            error_log('SQL Query: ' . $sql);
            error_log('SQL Params: ' . json_encode($params));
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log('Query returned ' . count($results) . ' rows');
            
            return $results;
            
        } catch (Exception $e) {
            error_log('Error in getAllScreenshots: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_screenshots,
                    SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM user_post_assignments
                WHERE screenshot_path IS NOT NULL
                AND screenshot_path != ''
            ");
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Statistics: ' . json_encode($result));
            
            return $result;
            
        } catch (Exception $e) {
            error_log('Error in getStatistics: ' . $e->getMessage());
            return [
                'total_screenshots' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0
            ];
        }
    }
    
    /**
     * Delete single image from Cloudinary and database
     */
    public function deleteImage($assignmentId) {
        try {
            $this->db->beginTransaction();
            
            // Get image details
            $stmt = $this->db->prepare("
                SELECT cloudinary_public_id, screenshot_path 
                FROM user_post_assignments 
                WHERE id = ?
            ");
            $stmt->execute([$assignmentId]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$image) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Image not found'
                ];
            }
            
            // Delete from Cloudinary
            if (!empty($image['cloudinary_public_id'])) {
                $cloudinary = CloudinaryConfig::getInstance();
                $cloudinary->delete($image['cloudinary_public_id']);
            }
            
            // Remove from database (set to NULL instead of deleting record)
            $updateStmt = $this->db->prepare("
                UPDATE user_post_assignments 
                SET screenshot_path = NULL,
                    cloudinary_public_id = NULL
                WHERE id = ?
            ");
            $updateStmt->execute([$assignmentId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Image deleted successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Delete image error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error deleting image: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Bulk delete images
     */
    public function bulkDeleteImages($assignmentIds) {
        try {
            $deleted = 0;
            $failed = 0;
            
            foreach ($assignmentIds as $id) {
                $result = $this->deleteImage($id);
                if ($result['success']) {
                    $deleted++;
                } else {
                    $failed++;
                }
            }
            
            return [
                'success' => true,
                'deleted' => $deleted,
                'failed' => $failed,
                'message' => "Deleted: $deleted, Failed: $failed"
            ];
            
        } catch (Exception $e) {
            error_log('Bulk delete error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error in bulk delete: ' . $e->getMessage()
            ];
        }
    }
}
?>