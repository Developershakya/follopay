<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Heartbeat.php';

class PostController {
    private $db;
    private $heartbeat;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->heartbeat = new Heartbeat($this->db);
    }
    
    public function getAvailablePosts($userId) {
        try {
            // Get posts where user hasn't submitted successfully AND has unused comments available
            $stmt = $this->db->prepare("
                SELECT p.*, 
                       (SELECT COUNT(*) FROM comments 
                        WHERE post_id = p.id 
                        AND is_used = FALSE
                        AND id NOT IN (
                            SELECT comment_id FROM user_post_assignments 
                            WHERE status = 'locked' 
                            AND TIMESTAMPDIFF(SECOND, last_heartbeat, NOW()) < 300
                        )
                       ) as available_comments
                FROM posts p
                WHERE p.status = 'active'
                AND p.id NOT IN (
                    SELECT DISTINCT post_id FROM user_post_assignments 
                    WHERE user_id = ? 
                    AND status IN ('submitted', 'approved')
                )
                HAVING available_comments > 0
                ORDER BY p.created_at DESC, available_comments DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log('Error in getAvailablePosts: ' . $e->getMessage());
            return [];
        }
    }
    
    public function getPostDetails($postId) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, 
                       (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as total_comments,
                       (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND is_used = TRUE) as used_comments,
                       (SELECT COUNT(*) FROM comments 
                        WHERE post_id = p.id 
                        AND is_used = FALSE
                        AND id NOT IN (
                            SELECT comment_id FROM user_post_assignments 
                            WHERE status = 'locked'
                            AND TIMESTAMPDIFF(SECOND, last_heartbeat, NOW()) < 300
                        )
                       ) as available_comments
                FROM posts p
                WHERE p.id = ?
            ");
            $stmt->execute([$postId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log('Error in getPostDetails: ' . $e->getMessage());
            return null;
        }
    }

    public function getTodaysUserTasks($userId)
{
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT upa.*, p.app_name, p.price, p.app_link
        FROM user_post_assignments upa
        JOIN posts p ON p.id = upa.post_id
        WHERE upa.user_id = ?
          AND DATE(upa.submitted_time) = CURDATE()
    ");
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    
    public function assignCommentToUser($userId, $postId) {
        try {
            $result = $this->heartbeat->assignComment($userId, $postId);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function getCurrentAssignment($userId) {
        try {
            // Get current locked assignment for user
            $stmt = $this->db->prepare("
                SELECT upa.*, 
                       p.app_link, 
                       p.app_name, 
                       p.price, 
                       c.comment_text,
                       TIMESTAMPDIFF(SECOND, upa.assigned_time, NOW()) AS seconds_elapsed,
                       TIMESTAMPDIFF(SECOND, upa.last_heartbeat, NOW()) AS seconds_since_heartbeat
                FROM user_post_assignments upa
                JOIN posts p ON upa.post_id = p.id
                JOIN comments c ON upa.comment_id = c.id
                WHERE upa.user_id = ? 
                AND upa.status = 'locked'
                AND TIMESTAMPDIFF(SECOND, upa.last_heartbeat, NOW()) < 300
                ORDER BY upa.assigned_time DESC
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$assignment) {
                return null;
            }
            
            return $assignment;
            
        } catch (Exception $e) {
            error_log('Error in getCurrentAssignment: ' . $e->getMessage());
            return null;
        }
    }
    
    // public function submitScreenshot($assignmentId, $userId, $screenshotData) {
    //     try {
    //         $this->db->beginTransaction();
            
    //         // 1. Validate assignment still exists and is locked
    //         $checkStmt = $this->db->prepare("
    //             SELECT upa.*, 
    //                    TIMESTAMPDIFF(SECOND, upa.assigned_time, NOW()) as seconds_elapsed,
    //                    TIMESTAMPDIFF(SECOND, upa.last_heartbeat, NOW()) as seconds_since_heartbeat
    //             FROM user_post_assignments upa
    //             WHERE upa.id = ? 
    //             AND upa.user_id = ? 
    //             AND upa.status = 'locked'
    //         ");
    //         $checkStmt->execute([$assignmentId, $userId]);
    //         $assignment = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
    //         if (!$assignment) {
    //             $this->db->rollBack();
    //             return [
    //                 'success' => false,
    //                 'message' => 'Assignment not found or already submitted'
    //             ];
    //         }
            
    //         // 2. Check if time expired (300 seconds = 5 minutes)
    //         if ($assignment['seconds_elapsed'] > 300) {
    //             $this->db->rollBack();
    //             return [
    //                 'success' => false,
    //                 'message' => 'Time expired! Please refresh to get a new comment.',
    //                 'expired' => true
    //             ];
    //         }
            
    //         // 3. Validate screenshot file
    //         if (!isset($screenshotData) || !isset($screenshotData['name'])) {
    //             $this->db->rollBack();
    //             return [
    //                 'success' => false,
    //                 'message' => 'No screenshot provided'
    //             ];
    //         }
            
    //         // 4. Save screenshot
    //         $uploadDir = __DIR__ . '/../uploads/screenshots/';
    //         if (!is_dir($uploadDir)) {
    //             mkdir($uploadDir, 0755, true);
    //         }
            
    //         $filename = 'screenshot_' . $userId . '_' . $assignmentId . '_' . time() . '.jpg';
    //         $filepath = $uploadDir . $filename;
    //         $relativePath = '/follo/uploads/screenshots/' . $filename;
            
    //         if (!move_uploaded_file($screenshotData['tmp_name'], $filepath)) {
    //             $this->db->rollBack();
    //             return [
    //                 'success' => false,
    //                 'message' => 'Failed to upload screenshot'
    //             ];
    //         }
            
    //         // 5. Update assignment status to submitted
    //         $updateStmt = $this->db->prepare("
    //             UPDATE user_post_assignments 
    //             SET status = 'submitted', 
    //                 screenshot_path = ?,
    //                 submitted_time = NOW()
    //             WHERE id = ? AND user_id = ?
    //         ");
    //         $updateStmt->execute([$relativePath, $assignmentId, $userId]);
            
    //         // 6. Mark comment as used
    //         $commentStmt = $this->db->prepare("
    //             UPDATE comments 
    //             SET is_used = TRUE 
    //             WHERE id = ?
    //         ");
    //         $commentStmt->execute([$assignment['comment_id']]);
            
    //         // 7. Update post used count
    //         $postStmt = $this->db->prepare("
    //             UPDATE posts 
    //             SET used_comments = used_comments + 1 
    //             WHERE id = ?
    //         ");
    //         $postStmt->execute([$assignment['post_id']]);
            
    //         $this->db->commit();
            
    //         return [
    //             'success' => true,
    //             'message' => 'Screenshot submitted successfully! It will be reviewed within 24-48 hours.',
    //             'assignment_id' => $assignmentId
    //         ];
            
    //     } catch (Exception $e) {
    //         $this->db->rollBack();
    //         error_log('Screenshot upload error: ' . $e->getMessage());
    //         return [
    //             'success' => false,
    //             'message' => 'Error submitting screenshot: ' . $e->getMessage()
    //         ];
    //     }
    // }
    public function submitScreenshot($assignmentId, $userId, $screenshotData) {
    try {
        $this->db->beginTransaction();
        
        // 1. Validate assignment still exists and is locked
        $checkStmt = $this->db->prepare("
            SELECT upa.*, 
                   TIMESTAMPDIFF(SECOND, upa.assigned_time, NOW()) as seconds_elapsed,
                   TIMESTAMPDIFF(SECOND, upa.last_heartbeat, NOW()) as seconds_since_heartbeat
            FROM user_post_assignments upa
            WHERE upa.id = ? 
            AND upa.user_id = ? 
            AND upa.status = 'locked'
        ");
        $checkStmt->execute([$assignmentId, $userId]);
        $assignment = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$assignment) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Assignment not found or already submitted'
            ];
        }
        
        // 2. Check if time expired (300 seconds = 5 minutes)
        if ($assignment['seconds_elapsed'] > 300) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Time expired! Please refresh to get a new comment.',
                'expired' => true
            ];
        }
        
        // 3. Validate screenshot file
        if (!isset($screenshotData) || !isset($screenshotData['name'])) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'No screenshot provided'
            ];
        }
        
        // 4. Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $fileType = $screenshotData['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Invalid file type. Only JPG, PNG, and WEBP images are allowed.'
            ];
        }
        
        // 5. Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($screenshotData['size'] > $maxSize) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'File size too large. Maximum size is 5MB.'
            ];
        }
        
        // 6. Upload to Cloudinary
        require_once __DIR__ . '/../config/cloudinary.php';
        $cloudinary = CloudinaryConfig::getInstance();
        
        $uploadResult = $cloudinary->upload($screenshotData['tmp_name'], [
            'folder' => 'follopay/screenshots',
            'public_id' => 'screenshot_' . $userId . '_' . $assignmentId . '_' . time(),
            'overwrite' => false
        ]);
        
        if (!$uploadResult['success']) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to upload screenshot to cloud storage'
            ];
        }
        
        $cloudinaryUrl = $uploadResult['url'];
        $publicId = $uploadResult['public_id'];
        
        // 7. Update assignment status to submitted
        $updateStmt = $this->db->prepare("
            UPDATE user_post_assignments 
            SET status = 'submitted', 
                screenshot_path = ?,
                cloudinary_public_id = ?,
                submitted_time = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $updateStmt->execute([$cloudinaryUrl, $publicId, $assignmentId, $userId]);
        
        // 8. Mark comment as used
        $commentStmt = $this->db->prepare("
            UPDATE comments 
            SET is_used = TRUE 
            WHERE id = ?
        ");
        $commentStmt->execute([$assignment['comment_id']]);
        
        // 9. Update post used count
        $postStmt = $this->db->prepare("
            UPDATE posts 
            SET used_comments = used_comments + 1 
            WHERE id = ?
        ");
        $postStmt->execute([$assignment['post_id']]);
        
        $this->db->commit();
        
        return [
            'success' => true,
            'message' => 'Screenshot submitted successfully! It will be reviewed within 7 business days.',
            'assignment_id' => $assignmentId,
            'screenshot_url' => $cloudinaryUrl
        ];
        
    } catch (Exception $e) {
        $this->db->rollBack();
        error_log('Screenshot upload error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error submitting screenshot: ' . $e->getMessage()
        ];
    }
}
    
    public function approveSubmission($assignmentId, $adminId) {
        try {
            $this->db->beginTransaction();
            
            // Get assignment details
            $stmt = $this->db->prepare("
                SELECT upa.*, p.price, u.wallet_balance, u.id as user_id
                FROM user_post_assignments upa
                JOIN posts p ON upa.post_id = p.id
                JOIN users u ON upa.user_id = u.id
                WHERE upa.id = ? AND upa.status = 'submitted'
            ");
            $stmt->execute([$assignmentId]);
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$assignment) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Submission not found or already processed'
                ];
            }
            
            // Update assignment status
            $updateStmt = $this->db->prepare("
                UPDATE user_post_assignments 
                SET status = 'approved',
                    approved_time = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$assignmentId]);
            
            // Credit user wallet
            $newBalance = floatval($assignment['wallet_balance']) + floatval($assignment['price']);
            $walletStmt = $this->db->prepare("
                UPDATE users 
                SET wallet_balance = ?
                WHERE id = ?
            ");
            $walletStmt->execute([$newBalance, $assignment['user_id']]);
            
            // Create transaction record
            $transStmt = $this->db->prepare("
                INSERT INTO transactions 
                (user_id, type, amount, balance_after, description, reference_id, reference_type, status, created_at)
                VALUES (?, 'credit', ?, ?, 'Post screenshot approved' || ?, ?, 'post', 'completed', NOW())
            ");
            $transStmt->execute([
                $assignment['user_id'],
                $assignment['price'],
                $newBalance,
                $assignment['post_id'],
                $assignment['post_id']
            ]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Submission approved and wallet credited'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Approval error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error approving submission: ' . $e->getMessage()
            ];
        }
    }
    
    // public function rejectSubmission($assignmentId, $reason) {
    //     try {
    //         $stmt = $this->db->prepare("
    //             UPDATE user_post_assignments 
    //             SET status = 'rejected',
    //                 rejection_reason = ?,
    //                 rejected_time = NOW()
    //             WHERE id = ?
    //         ");
    //         $stmt->execute([$reason, $assignmentId]);
            
    //         return [
    //             'success' => true,
    //             'message' => 'Submission rejected'
    //         ];
            
    //     } catch (Exception $e) {
    //         error_log('Rejection error: ' . $e->getMessage());
    //         return [
    //             'success' => false,
    //             'message' => 'Error rejecting submission: ' . $e->getMessage()
    //         ];
    //     }
    // }

        //     public function rejectSubmission($assignmentId, $reason) {
        //     try {
        //         $this->db->beginTransaction();
                
        //         // Get assignment details including cloudinary_public_id
        //         $stmt = $this->db->prepare("
        //             SELECT cloudinary_public_id 
        //             FROM user_post_assignments 
        //             WHERE id = ?
        //         ");
        //         $stmt->execute([$assignmentId]);
        //         $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
                
        //         // Update assignment status
        //         $updateStmt = $this->db->prepare("
        //             UPDATE user_post_assignments 
        //             SET status = 'rejected',
        //                 rejection_reason = ?,
        //                 rejected_time = NOW()
        //             WHERE id = ?
        //         ");
        //         $updateStmt->execute([$reason, $assignmentId]);
                
        //         // Optional: Delete from Cloudinary
        //         if ($assignment && !empty($assignment['cloudinary_public_id'])) {
        //             require_once __DIR__ . '/../config/cloudinary.php';
        //             $cloudinary = CloudinaryConfig::getInstance();
        //             $cloudinary->delete($assignment['cloudinary_public_id']);
        //         }
                
        //         $this->db->commit();
                
        //         return [
        //             'success' => true,
        //             'message' => 'Submission rejected'
        //         ];
                
        //     } catch (Exception $e) {
        //         $this->db->rollBack();
        //         error_log('Rejection error: ' . $e->getMessage());
        //         return [
        //             'success' => false,
        //             'message' => 'Error rejecting submission: ' . $e->getMessage()
        //         ];
        //     }
        // }



        public function rejectSubmission($assignmentId, $reason) {
    try {
        $this->db->beginTransaction();
        
        // Update assignment status (use reject_reason instead of rejection_reason)
        $updateStmt = $this->db->prepare("
            UPDATE user_post_assignments 
            SET status = 'rejected',
                reject_reason = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$reason, $assignmentId]);
        
        $this->db->commit();
        
        return [
            'success' => true,
            'message' => 'Submission rejected'
        ];
        
    } catch (Exception $e) {
        $this->db->rollBack();
        error_log('Rejection error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error rejecting submission: ' . $e->getMessage()
        ];
    }
}
}
?>