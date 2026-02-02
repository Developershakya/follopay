<?php
require_once "../config/db.php";

$user_id = $_SESSION['user_id'];
$assignment_id = $_POST['assignment_id'];

mysqli_query($conn, "
UPDATE user_post_assignments
SET status = 'submitted',
submitted_time = NOW()
WHERE id = $assignment_id
AND user_id = $user_id
");

$cid = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT comment_id FROM user_post_assignments WHERE id = $assignment_id
"))['comment_id'];

mysqli_query($conn,"UPDATE comments SET is_used = 1 WHERE id = $cid");

echo json_encode(["success"=>true]);
