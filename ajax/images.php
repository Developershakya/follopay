<?php
require_once '../config/constants.php';
require_once '../middleware/AuthMiddleware.php';
require_once '../controllers/ImageController.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display in output
ini_set('log_errors', 1);

// Check authentication
AuthMiddleware::handle();

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Role: ' . ($_SESSION['role'] ?? 'not set')]);
    exit;
}

$imageController = new ImageController();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch($action) {
        case 'get_all':
            $filters = [
                'status' => $_GET['status'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'username' => $_GET['username'] ?? '',
                'app_name' => $_GET['app_name'] ?? '',
                'limit' => $_GET['limit'] ?? 100,
                'offset' => $_GET['offset'] ?? 0
            ];
            
            $images = $imageController->getAllScreenshots($filters);
            $stats = $imageController->getStatistics();
            
            // Debug info
            error_log('Images count: ' . count($images));
            error_log('Images data: ' . json_encode($images));
            
            echo json_encode([
                'success' => true,
                'images' => $images,
                'stats' => $stats,
                'debug' => [
                    'count' => count($images),
                    'filters' => $filters
                ]
            ]);
            break;
            
        case 'delete_single':
            $assignmentId = $_POST['assignment_id'] ?? 0;
            $result = $imageController->deleteImage($assignmentId);
            echo json_encode($result);
            break;
            
        case 'delete_bulk':
            $assignmentIds = json_decode($_POST['assignment_ids'] ?? '[]', true);
            
            if (empty($assignmentIds) || !is_array($assignmentIds)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No images selected'
                ]);
                break;
            }
            
            $result = $imageController->bulkDeleteImages($assignmentIds);
            echo json_encode($result);
            break;
            
        case 'get_stats':
            $stats = $imageController->getStatistics();
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }
} catch (Exception $e) {
    error_log('Error in images.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>