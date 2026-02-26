<?php
require_once '../config/constants.php';
require_once '../middleware/AuthMiddleware.php';
require_once '../controllers/PostController.php';
require_once '../middleware/BanCheckMiddleware.php';
require_once '../helpers/Heartbeat.php';

header('Content-Type: application/json');
AuthMiddleware::handle();
BanCheckMiddleware::handle();

$postController = new PostController();
$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'get_available':
        $posts = $postController->getAvailablePosts($userId);
        echo json_encode(['success' => true, 'posts' => $posts]);
        break;

    // ── NEW: period (today/month) + status (submitted/approved/rejected) ──
    case 'get_user_tasks':
        $period = $_GET['period'] ?? $_POST['period'] ?? 'today';
        $status = $_GET['status'] ?? $_POST['status'] ?? null;
        $tasks  = $postController->getUserTasksByPeriod($userId, $period, $status);
        echo json_encode(['success' => true, 'tasks' => $tasks]);
        break;

    // legacy — keep for other pages
    case 'get_today_tasks':
        $tasks = $postController->getTodaysUserTasks($userId);
        echo json_encode(['success' => true, 'tasks' => $tasks]);
        break;

    case 'get_details':
        $postId = $_GET['post_id'] ?? 0;
        $post = $postController->getPostDetails($postId);
        echo json_encode(['success' => true, 'post' => $post]);
        break;

    case 'assign_comment':
        $postId = $_POST['post_id'] ?? 0;
        $current = $postController->getCurrentAssignment($userId);
        if ($current) {
            echo json_encode(['success' => true, 'already_assigned' => true, 'assignment' => $current]);
            break;
        }
        $result = $postController->assignCommentToUser($userId, $postId);
        echo json_encode($result);
        break;

    case 'get_current_assignment':
        $assignment = $postController->getCurrentAssignment($userId);
        if ($assignment) echo json_encode(['success' => true, 'assignment' => $assignment]);
        else echo json_encode(['success' => false, 'message' => 'No active assignment']);
        break;

    case 'submit_screenshot':
        if (!isset($_FILES['screenshot'])) {
            echo json_encode(['success' => false, 'message' => 'No screenshot uploaded']);
            break;
        }
        $assignmentId = $_POST['assignment_id'] ?? 0;
        $result = $postController->submitScreenshot($assignmentId, $userId, $_FILES['screenshot']);
        echo json_encode($result);
        break;

    case 'refresh_comment':
        $assignmentId = $_POST['assignment_id'] ?? 0;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM user_post_assignments WHERE id = ? AND user_id = ?");
        $stmt->execute([$assignmentId, $userId]);
        $postId = $_POST['post_id'] ?? 0;
        $result = $postController->assignCommentToUser($userId, $postId);
        echo json_encode($result);
        break;

    case 'heartbeat':
        $assignmentId = $_POST['assignment_id'] ?? 0;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE user_post_assignments SET last_heartbeat = NOW() WHERE id = ? AND user_id = ? AND status = 'locked'");
        $success = $stmt->execute([$assignmentId, $userId]);
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>