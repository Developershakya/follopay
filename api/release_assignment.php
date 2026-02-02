<?php
require_once "../config/db.php";

$user_id = $_SESSION['user_id'];

mysqli_query($conn, "
DELETE FROM user_post_assignments
WHERE user_id = $user_id
AND status = 'locked'
");
