<?php
require_once '../config/constants.php';
require_once '../middleware/AuthMiddleware.php';
require_once '../controllers/CommentController.php';

header('Content-Type: application/json');

// Check authentication and admin access
AuthMiddleware::handle();
AdminMiddleware::checkAdminOrJson();

$commentController = new CommentController();
$action = $_POST['action'] ?? '';

switch($action) {
    case 'add':
        $postId = $_POST['post_id'] ?? 0;
        $commentText = $_POST['comment_text'] ?? '';
        
        $result = $commentController->addComment($postId, $commentText);
        echo json_encode($result);
        break;
        
    case 'update':
        $commentId = $_POST['comment_id'] ?? 0;
        $newText = $_POST['comment_text'] ?? '';
        
        $result = $commentController->updateComment($commentId, $newText);
        echo json_encode($result);
        break;
        
    case 'delete':
        $commentId = $_POST['comment_id'] ?? 0;
        
        $result = $commentController->deleteComment($commentId);
        echo json_encode($result);
        break;
        
    case 'get_stats':
        $postId = $_GET['post_id'] ?? 0;
        
        $stats = $commentController->getCommentStats($postId);
        echo json_encode(['success' => true, 'stats' => $stats]);
        break;
        
    case 'get_recent':
        $limit = $_GET['limit'] ?? 10;
        $comments = $commentController->getRecentlyUsedComments($limit);
        echo json_encode(['success' => true, 'comments' => $comments]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>