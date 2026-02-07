<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Validation.php';
require_once __DIR__ . '/../helpers/EmailService.php';

class AccountController {
    private $db;
    private $session;
    private $emailService;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->session = new Session();
        $this->emailService = new EmailService();
    }
    
    /**
     * Request account deletion
     * User provides email and phone for verification
     */
    public function requestDeletion($data) {
        $validation = new Validation();
        
        $rules = [
            'email' => 'required|email',
            'phone' => 'required|regex:/^[0-9]{10}$/'
        ];
        
        if (!$validation->validate($data, $rules)) {
            return [
                'success' => false,
                'errors'  => $validation->errors()
            ];
        }
        
        // Check if user exists with this email and phone
        $stmt = $this->db->prepare("
            SELECT id, username, email, phone FROM users 
            WHERE email = ? AND phone = ? AND is_banned = 0
        ");
        $stmt->execute([$data['email'], $data['phone']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Account not found. Email or phone number is incorrect.'
            ];
        }
        
        // Check if already has a pending deletion request
        $checkStmt = $this->db->prepare("
            SELECT id FROM account_deletion_requests 
            WHERE user_id = ? AND request_status IN ('pending', 'approved')
        ");
        $checkStmt->execute([$user['id']]);
        $existingRequest = $checkStmt->fetch();
        
        if ($existingRequest) {
            return [
                'success' => false,
                'message' => 'You already have a pending deletion request. Please contact support for more information.'
            ];
        }
        
        // Create deletion request
        $insertStmt = $this->db->prepare("
            INSERT INTO account_deletion_requests 
            (user_id, username, email, phone, request_status, request_reason, requested_at) 
            VALUES (?, ?, ?, ?, 'pending', ?, NOW())
        ");
        
        try {
            $insertStmt->execute([
                $user['id'],
                $user['username'],
                $user['email'],
                $user['phone'],
                $data['reason'] ?? 'No reason provided'
            ]);
            
            $requestId = $this->db->lastInsertId();
            
            // Send confirmation email to user
            $emailSent = $this->emailService->sendDeletionRequestConfirmation(
                $user['email'],
                $user['username'],
                $requestId
            );
            
            // Send notification to admin
            $this->emailService->sendAdminDeletionNotification(
                $user['username'],
                $user['email'],
                $user['phone']
            );
            
            return [
                'success' => true,
                'message' => 'Your account deletion request has been submitted successfully. Our admin team will review it and contact you within 24-48 hours.',
                'request_id' => $requestId
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to submit deletion request. Please try again later.'
            ];
        }
    }
    
    /**
     * Get all deletion requests (for admin)
     */
    public function getAllDeletionRequests($status = null, $limit = 20, $offset = 0) {
        if (!$this->session->get('logged_in') || $this->session->get('role') !== 'admin') {
            return [
                'success' => false,
                'message' => 'Unauthorized access'
            ];
        }
        
        $query = "
            SELECT 
                adr.id,
                adr.user_id,
                adr.username,
                adr.email,
                adr.phone,
                adr.request_status,
                adr.request_reason,
                adr.requested_at,
                adr.reviewed_by,
                adr.reviewed_at,
                adr.admin_notes,
                u.created_at as user_created_at
            FROM account_deletion_requests adr
            LEFT JOIN users u ON adr.user_id = u.id
        ";
        
        $params = [];
        
        if ($status) {
            $query .= " WHERE adr.request_status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY adr.requested_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $requests = $stmt->fetchAll();
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM account_deletion_requests";
            if ($status) {
                $countQuery .= " WHERE request_status = ?";
            }
            $countStmt = $this->db->prepare($countQuery);
            $countParams = $status ? [$status] : [];
            $countStmt->execute($countParams);
            $count = $countStmt->fetch();
            
            return [
                'success' => true,
                'data' => $requests,
                'total' => $count['total'],
                'limit' => $limit,
                'offset' => $offset
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch deletion requests'
            ];
        }
    }
    
    /**
     * Get single deletion request
     */
    public function getDeletionRequest($requestId) {
        if (!$this->session->get('logged_in') || $this->session->get('role') !== 'admin') {
            return [
                'success' => false,
                'message' => 'Unauthorized access'
            ];
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM account_deletion_requests 
            WHERE id = ?
        ");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        
        if (!$request) {
            return [
                'success' => false,
                'message' => 'Request not found'
            ];
        }
        
        return [
            'success' => true,
            'data' => $request
        ];
    }
    
    /**
     * Approve deletion request and delete user account
     */
    public function approveDeletion($requestId, $adminNotes = '') {
        if (!$this->session->get('logged_in') || $this->session->get('role') !== 'admin') {
            return [
                'success' => false,
                'message' => 'Unauthorized access'
            ];
        }
        
        // Get request details
        $stmt = $this->db->prepare("SELECT * FROM account_deletion_requests WHERE id = ?");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        
        if (!$request) {
            return [
                'success' => false,
                'message' => 'Request not found'
            ];
        }
        
        if ($request['request_status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'This request has already been processed'
            ];
        }
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Update request status
            $updateStmt = $this->db->prepare("
                UPDATE account_deletion_requests 
                SET request_status = 'completed', 
                    reviewed_by = ?, 
                    reviewed_at = NOW(), 
                    admin_notes = ?,
                    deleted_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([
                $this->session->get('user_id'),
                $adminNotes,
                $requestId
            ]);
            
            // Delete user account
            $deleteStmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $deleteStmt->execute([$request['user_id']]);
            
            // Commit transaction
            $this->db->commit();
            
            // Send confirmation email to user
            $this->emailService->sendDeletionCompletionEmail(
                $request['email'],
                $request['username']
            );
            
            return [
                'success' => true,
                'message' => 'Account has been deleted successfully'
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to process deletion request'
            ];
        }
    }
    
    /**
     * Reject deletion request
     */
    public function rejectDeletion($requestId, $reason = '') {
        if (!$this->session->get('logged_in') || $this->session->get('role') !== 'admin') {
            return [
                'success' => false,
                'message' => 'Unauthorized access'
            ];
        }
        
        $stmt = $this->db->prepare("SELECT * FROM account_deletion_requests WHERE id = ?");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        
        if (!$request) {
            return [
                'success' => false,
                'message' => 'Request not found'
            ];
        }
        
        if ($request['request_status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'This request has already been processed'
            ];
        }
        
        try {
            $updateStmt = $this->db->prepare("
                UPDATE account_deletion_requests 
                SET request_status = 'rejected', 
                    reviewed_by = ?, 
                    reviewed_at = NOW(), 
                    admin_notes = ?
                WHERE id = ?
            ");
            $updateStmt->execute([
                $this->session->get('user_id'),
                $reason,
                $requestId
            ]);
            
            // Send rejection email to user
            $this->emailService->sendDeletionRejectionEmail(
                $request['email'],
                $request['username'],
                $reason
            );
            
            return [
                'success' => true,
                'message' => 'Deletion request has been rejected'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to reject deletion request'
            ];
        }
    }
    
    /**
     * Cancel own deletion request (user)
     */
    public function cancelDeletionRequest() {
        if (!$this->session->get('logged_in')) {
            return [
                'success' => false,
                'message' => 'You must be logged in'
            ];
        }
        
        $userId = $this->session->get('user_id');
        
        try {
            $stmt = $this->db->prepare("
                UPDATE account_deletion_requests 
                SET request_status = 'rejected'
                WHERE user_id = ? AND request_status = 'pending'
            ");
            $stmt->execute([$userId]);
            
            return [
                'success' => true,
                'message' => 'Your deletion request has been cancelled'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to cancel deletion request'
            ];
        }
    }
}
?>