<?php
require_once '../config/constants.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ===============================
   AUTH CHECK
================================*/
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

/* ===============================
   DB CONNECTION (PDO)
================================*/
$db  = Database::getInstance();
$pdo = $db->getConnection();

/* ===============================
   INPUT HANDLING
================================*/
$input  = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? ($input['action'] ?? '');

/* ===============================
   GET COMMENTS + COUNTS
================================*/
if ($action === 'get') {

    $post_id = (int)($_GET['post_id'] ?? 0);

    if (!$post_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid Post ID']);
        exit;
    }

    // COMMENTS
    $stmt = $pdo->prepare("
        SELECT id, comment_text, is_used
        FROM comments
        WHERE post_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll();

    // COUNTS
    $stmt2 = $pdo->prepare("
        SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) AS used_count,
            SUM(CASE WHEN is_used = 0 THEN 1 ELSE 0 END) AS unused_count
        FROM comments
        WHERE post_id = ?
    ");
    $stmt2->execute([$post_id]);
    $counts = $stmt2->fetch();

    echo json_encode([
        'success'  => true,
        'comments' => $comments,
        'counts'   => $counts
    ]);
    exit;
}

/* ===============================
   ADD SINGLE COMMENT
================================*/
if ($action === 'add') {

    $post_id = (int)($input['post_id'] ?? 0);
    $comment = trim($input['comment'] ?? '');

    if (!$post_id || $comment === '') {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO comments (post_id, comment_text, is_used)
        VALUES (?, ?, 0)
    ");
    $stmt->execute([$post_id, $comment]);

    echo json_encode(['success' => true]);
    exit;
}

/* ===============================
   ADD MULTIPLE COMMENTS (BULK)
================================*/
if ($action === 'add_bulk') {

    $post_id  = (int)($input['post_id'] ?? 0);
    $comments = trim($input['comments'] ?? '');

    if (!$post_id || !$comments) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    // One comment per line
    $lines = array_filter(array_map('trim', explode("\n", $comments)));

    if (count($lines) === 0) {
        echo json_encode(['success' => false, 'message' => 'No valid comments']);
        exit;
    }

    // 🔹 Start transaction (important)
    $pdo->beginTransaction();

    try {

        // Insert comments
        $stmt = $pdo->prepare("
            INSERT INTO comments (post_id, comment_text, is_used)
            VALUES (?, ?, 0)
        ");

        foreach ($lines as $c) {
            $stmt->execute([$post_id, $c]);
        }

        $addedCount = count($lines);

        // 🔹 Update posts.total_comments
        $updateStmt = $pdo->prepare("
            UPDATE posts
            SET total_comments = total_comments + ?
            WHERE id = ?
        ");
        $updateStmt->execute([$addedCount, $post_id]);

        // 🔹 Commit
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'added'   => $addedCount
        ]);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'Something went wrong'
        ]);
        exit;
    }
}


/* ===============================
   DELETE COMMENT
================================*/
if ($action === 'delete') {

    $comment_id = (int)($input['comment_id'] ?? 0);

    if (!$comment_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);

    echo json_encode(['success' => true]);
    exit;
}

/* ===============================
   DEFAULT
================================*/
echo json_encode(['success' => false, 'message' => 'Invalid action']);
