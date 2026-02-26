<?php
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../middleware/AuthMiddleware.php';
require_once '../middleware/AdminMiddleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Check authentication
    AuthMiddleware::handle();
    
    // Check admin rights
    AdminMiddleware::checkAdminOrJson();
    
    $db = Database::getInstance()->getConnection();
    
    // Get action
    $action = $_REQUEST['action'] ?? '';
    
    if (empty($action)) {
        throw new Exception('Action parameter is required');
    }
    
    switch($action) {
        
        // ============ GET EXPORT DATA ============
        case 'get_submission_data':
            $filterType = $_GET['filter_type'] ?? 'all';
            $filterValue = $_GET['filter_value'] ?? '';
            $postId = intval($_GET['post_id'] ?? 0);
            $userId = intval($_GET['user_id'] ?? 0);
            $status = $_GET['status'] ?? 'all';
            
            $where = "WHERE 1=1";
            $params = [];
            
            // Filter by status
            if ($status !== 'all') {
                $where .= " AND upa.status = ?";
                $params[] = $status;
            }
            
            // Filter by post
            if ($postId > 0) {
                $where .= " AND upa.post_id = ?";
                $params[] = $postId;
            }
            
            // Filter by user
            if ($userId > 0) {
                $where .= " AND upa.user_id = ?";
                $params[] = $userId;
            }
            
            // Filter by date range
            if (!empty($filterValue)) {
                switch($filterType) {
                    case 'date':
                        $where .= " AND DATE(upa.submitted_time) = ?";
                        $params[] = $filterValue;
                        break;
                        
                    case 'week':
                        $where .= " AND YEARWEEK(upa.submitted_time, 1) = YEARWEEK(?, 1)";
                        $params[] = $filterValue . '-1';
                        break;
                        
                    case 'month':
                        $where .= " AND DATE_FORMAT(upa.submitted_time, '%Y-%m') = ?";
                        $params[] = $filterValue;
                        break;
                }
            }
            
            // Get data - Fixed to match actual database columns
            $stmt = $db->prepare("
                SELECT 
                    u.id as user_id,
                    u.username as user_name,
                    u.email,
                    u.phone,
                    p.app_name,
                    p.app_link,
                    p.price,
                    upa.assigned_time,
                    upa.submitted_time,
                    upa.screenshot_path,
                    upa.status,
                    c.comment_text as assigned_comment,
                    CASE 
                        WHEN upa.submitted_time IS NOT NULL THEN DATEDIFF(upa.submitted_time, upa.assigned_time)
                        ELSE NULL
                    END as days_taken,
                    upa.reject_reason
                FROM user_post_assignments upa
                JOIN users u ON upa.user_id = u.id
                JOIN posts p ON upa.post_id = p.id
                LEFT JOIN comments c ON upa.comment_id = c.id
                $where
                ORDER BY upa.submitted_time DESC
            ");
            
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $data,
                'total' => count($data),
                'filter' => [
                    'type' => $filterType,
                    'value' => $filterValue,
                    'status' => $status,
                    'post_id' => $postId,
                    'user_id' => $userId
                ]
            ]);
            break;
            
        // ============ EXPORT TO CSV (NO LIBRARY NEEDED) ============
        case 'export_excel':
            $filterType = $_GET['filter_type'] ?? 'all';
            $filterValue = $_GET['filter_value'] ?? '';
            $postId = intval($_GET['post_id'] ?? 0);
            $userId = intval($_GET['user_id'] ?? 0);
            $status = $_GET['status'] ?? 'all';
            
            $where = "WHERE 1=1";
            $params = [];
            
            if ($status !== 'all') {
                $where .= " AND upa.status = ?";
                $params[] = $status;
            }
            
            if ($postId > 0) {
                $where .= " AND upa.post_id = ?";
                $params[] = $postId;
            }
            
            if ($userId > 0) {
                $where .= " AND upa.user_id = ?";
                $params[] = $userId;
            }
            
            if (!empty($filterValue)) {
                switch($filterType) {
                    case 'date':
                        $where .= " AND DATE(upa.submitted_time) = ?";
                        $params[] = $filterValue;
                        break;
                    case 'week':
                        $where .= " AND YEARWEEK(upa.submitted_time, 1) = YEARWEEK(?, 1)";
                        $params[] = $filterValue . '-1';
                        break;
                    case 'month':
                        $where .= " AND DATE_FORMAT(upa.submitted_time, '%Y-%m') = ?";
                        $params[] = $filterValue;
                        break;
                }
            }
            
            $stmt = $db->prepare("
                SELECT 
                    u.id as user_id,
                    u.username as user_name,
                    u.email,
                    u.phone,
                    p.app_name,
                    p.app_link,
                    p.price,
                    upa.assigned_time,
                    upa.submitted_time,
                    upa.screenshot_path,
                    upa.status,
                    c.comment_text as assigned_comment,
                    CASE 
                        WHEN upa.submitted_time IS NOT NULL THEN DATEDIFF(upa.submitted_time, upa.assigned_time)
                        ELSE NULL
                    END as days_taken,
                    upa.reject_reason
                FROM user_post_assignments upa
                JOIN users u ON upa.user_id = u.id
                JOIN posts p ON upa.post_id = p.id
                LEFT JOIN comments c ON upa.comment_id = c.id
                $where
                ORDER BY upa.submitted_time DESC
            ");
            
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($data)) {
                throw new Exception('No data to export');
            }
            
            // Create CSV file
            $filename = 'submission_export_' . date('Y-m-d_H-i-s') . '.csv';
            
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Open output stream
            $output = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 (Excel reads correctly)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write headers
            $headers = [
                'User ID',
                'User Name',
                'Email',
                'Phone',
                'App Name',
                'App Link',
                'Price (₹)',
                'Assigned Date',
                'Submitted Time',
                'Status',
                'Days Taken',
                'Assigned Comment',
                'Screenshot URL',
                'Reject Reason'
            ];
            
            fputcsv($output, $headers);
            
            // Write data rows
            foreach ($data as $row) {
                $csvRow = [
                    $row['user_id'],
                    $row['user_name'],
                    $row['email'],
                    $row['phone'],
                    $row['app_name'],
                    $row['app_link'],
                    $row['price'],
                    $row['assigned_time'],
                    $row['submitted_time'],
                    $row['status'],
                    $row['days_taken'] ?? 'N/A',
                    $row['assigned_comment'] ?? '',
                    $row['screenshot_path'] ?? '',
                    $row['reject_reason'] ?? ''
                ];
                fputcsv($output, $csvRow);
            }
            
            fclose($output);
            exit;
            break;
            
        // ============ GET FILTERS DATA (Posts, Users) ============
        case 'get_filter_options':
            
            // Get all posts
            $postsStmt = $db->prepare("
                SELECT id, app_name 
                FROM posts 
                WHERE status = 'active'
                ORDER BY app_name ASC
            ");
            $postsStmt->execute();
            $posts = $postsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get all users with submissions
            $usersStmt = $db->prepare("
                SELECT DISTINCT u.id, u.username 
                FROM users u
                JOIN user_post_assignments upa ON u.id = upa.user_id
                ORDER BY u.username ASC
            ");
            $usersStmt->execute();
            $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get date range of submissions
            $dateStmt = $db->prepare("
                SELECT 
                    MIN(DATE(submitted_time)) as min_date,
                    MAX(DATE(submitted_time)) as max_date,
                    COUNT(DISTINCT DATE(submitted_time)) as total_dates
                FROM user_post_assignments
                WHERE submitted_time IS NOT NULL
            ");
            $dateStmt->execute();
            $dateRange = $dateStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'posts' => $posts,
                'users' => $users,
                'date_range' => $dateRange
            ]);
            break;
            
        // ============ GET SUMMARY STATS ============
        case 'get_summary':
            $filterType = $_GET['filter_type'] ?? 'all';
            $filterValue = $_GET['filter_value'] ?? '';
            $postId = intval($_GET['post_id'] ?? 0);
            $userId = intval($_GET['user_id'] ?? 0);
            
            $where = "WHERE 1=1";
            $params = [];
            
            if ($postId > 0) {
                $where .= " AND upa.post_id = ?";
                $params[] = $postId;
            }
            
            if ($userId > 0) {
                $where .= " AND upa.user_id = ?";
                $params[] = $userId;
            }
            
            if (!empty($filterValue)) {
                switch($filterType) {
                    case 'date':
                        $where .= " AND DATE(upa.submitted_time) = ?";
                        $params[] = $filterValue;
                        break;
                    case 'week':
                        $where .= " AND YEARWEEK(upa.submitted_time, 1) = YEARWEEK(?, 1)";
                        $params[] = $filterValue . '-1';
                        break;
                    case 'month':
                        $where .= " AND DATE_FORMAT(upa.submitted_time, '%Y-%m') = ?";
                        $params[] = $filterValue;
                        break;
                }
            }
            
            // Fixed to match actual database columns
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_submissions,
                    SUM(CASE WHEN upa.status = 'submitted' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN upa.status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN upa.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN upa.status = 'approved' THEN p.price ELSE 0 END) as total_paid,
                    AVG(CASE 
                        WHEN upa.submitted_time IS NOT NULL THEN DATEDIFF(upa.submitted_time, upa.assigned_time)
                        ELSE NULL
                    END) as avg_days_taken
                FROM user_post_assignments upa
                JOIN posts p ON upa.post_id = p.id
                $where
            ");
            
            $stmt->execute($params);
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'summary' => $summary
            ]);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch(Exception $e) {
    error_log('Export API error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>