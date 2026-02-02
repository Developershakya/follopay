<?php
require_once "../config/db.php";

$user_id = $_SESSION['user_id'];

$q = "
SELECT upa.*, p.app_link, p.price, c.comment_text,
TIMESTAMPDIFF(SECOND, upa.assigned_time, NOW()) AS elapsed
FROM user_post_assignments upa
JOIN posts p ON p.id = upa.post_id
JOIN comments c ON c.id = upa.comment_id
WHERE upa.user_id = ?
AND upa.status = 'locked'
AND TIMESTAMPDIFF(SECOND, upa.last_heartbeat, NOW()) < ?
LIMIT 1
";

$stmt = mysqli_prepare($conn, $q);
mysqli_stmt_bind_param($stmt, "ii", $user_id, COMMENT_LOCK_TIME);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

echo json_encode(mysqli_fetch_assoc($res));
