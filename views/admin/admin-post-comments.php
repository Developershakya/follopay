<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ?page=dashboard');
    exit;
}

$post_id = (int)($_GET['id'] ?? 0);
if (!$post_id) {
    echo "Invalid Post ID";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Manage Comments</title>
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto p-4">

    <h1 class="text-2xl font-bold mb-6 text-center md:text-left">Manage Comments</h1>

    <!-- GRID: Left = Add, Right = Comments -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- LEFT SIDE: ADD COMMENTS -->
        <div class="md:col-span-1 space-y-6">

            <!-- COUNTS -->
            <div class="bg-white p-4 rounded shadow space-y-2">
                <h2 class="font-semibold text-lg mb-2">Comments Stats</h2>
                <div class="flex justify-between">
                    <div class="bg-blue-100 px-4 py-2 rounded w-1/3 text-center">
                        Total: <b id="totalCount">0</b>
                    </div>
                    <div class="bg-green-100 px-4 py-2 rounded w-1/3 text-center">
                        Used: <b id="usedCount">0</b>
                    </div>
                    <div class="bg-yellow-100 px-4 py-2 rounded w-1/3 text-center">
                        Unused: <b id="unusedCount">0</b>
                    </div>
                </div>
            </div>

            <!-- ADD BULK COMMENTS -->
            <div class="bg-white p-4 rounded shadow">
                <h2 class="font-semibold text-lg mb-2">Add Multiple Comments</h2>
                <textarea id="bulkComments"
                          class="w-full border rounded p-2 h-48"
                          placeholder="One comment per line"></textarea>
                <button onclick="addBulkComments()"
                        class="mt-3 w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Add All Comments
                </button>
            </div>

        </div>

        <!-- RIGHT SIDE: COMMENTS LIST -->
        <div class="md:col-span-2">
            <div class="bg-white p-4 rounded shadow">
                <h2 class="font-semibold text-lg mb-3">Comments</h2>
                <div id="commentsList" class="grid grid-cols-1 gap-3">
                    Loading...
                </div>
            </div>
        </div>

    </div>

</div>

<script>
const POST_ID = <?= $post_id ?>;

/* ===============================
   LOAD COMMENTS
================================*/
function loadComments() {
    fetch(`ajax/admin_comments.php?action=get&post_id=${POST_ID}`)
        .then(res => res.json())
        .then(data => {

            if (!data.success) {
                document.getElementById('commentsList').innerHTML = 'No comments';
                return;
            }

            // COUNTS
            document.getElementById('totalCount').innerText  = data.counts.total ?? 0;
            document.getElementById('usedCount').innerText   = data.counts.used_count ?? 0;
            document.getElementById('unusedCount').innerText = data.counts.unused_count ?? 0;

            let html = '';

            if (data.comments.length === 0) {
                html = '<p class="text-gray-500">No comments added yet</p>';
            }

            data.comments.forEach(c => {
                html += `
                    <div class="flex justify-between items-center border p-3 rounded hover:bg-gray-50">
                        <div class="flex-1 ${c.is_used == 1 ? 'line-through text-gray-400' : ''}">
                            ${escapeHtml(c.comment_text)}
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <span class="text-xs font-semibold ${
                                c.is_used == 1 ? 'text-red-500' : 'text-green-600'
                            }">
                                ${c.is_used == 1 ? 'USED' : 'UNUSED'}
                            </span>
                            <button onclick="deleteComment(${c.id})"
                                    class="text-red-500 hover:underline text-xs">
                                Delete
                            </button>
                        </div>
                    </div>
                `;
            });

            document.getElementById('commentsList').innerHTML = html;
        });
}

/* ===============================
   ADD BULK COMMENTS
================================*/
function addBulkComments() {
    const comments = document.getElementById('bulkComments').value.trim();

    if (!comments) {
        alert('Please enter comments');
        return;
    }

    fetch('ajax/admin_comments.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'add_bulk',
            post_id: POST_ID,
            comments: comments
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.added + ' comments added');
            document.getElementById('bulkComments').value = '';
            loadComments();
        } else {
            alert(data.message);
        }
    });
}

/* ===============================
   DELETE COMMENT
================================*/
function deleteComment(id) {
    if (!confirm('Delete this comment?')) return;

    fetch('ajax/admin_comments.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'delete',
            comment_id: id
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadComments();
        } else {
            alert(data.message);
        }
    });
}

/* ===============================
   SECURITY: ESCAPE HTML
================================*/
function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

// INIT
loadComments();
</script>

</body>
</html>
