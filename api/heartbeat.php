<?php
require_once "../config/db.php";

$assignment_id = $_POST['assignment_id'];
$user_id = $_SESSION['user_id'];

mysqli_query($conn, "
UPDATE user_post_assignments
SET last_heartbeat = NOW()
WHERE id = $assignment_id
AND user_id = $user_id
AND status = 'locked'
");
