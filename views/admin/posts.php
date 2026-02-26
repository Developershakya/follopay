<?php
// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ?page=dashboard');
    exit;
}
?>
 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <?php include 'header.php'; ?>
      <script src="asserts/js/toast.js" defer></script>
    <title>FolloPay</title>
 </head>
 <body>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Manage Posts</h1>
        <p class="text-gray-600">Create and manage earning tasks</p>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Create Post Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold">Create New Post</h2>
                </div>
                
                <div class="p-6">
                    <form id="createPostForm">
                        <!-- App Link -->
                        <div class="mb-6">
                            <label class="block text-gray-700 font-bold mb-2">
                                Play Store App Link
                            </label>
                            <input type="url" name="app_link" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                                   placeholder="https://play.google.com/store/apps/details?id=com.example.app" 
                                   required>
                            <p class="text-sm text-gray-600 mt-2">
                                Enter the exact Play Store link of the app
                            </p>
                        </div>

                        <!-- App Name -->
                        <div class="mb-6">
                            <label class="block text-gray-700 font-bold mb-2">
                                App Name
                            </label>
                            <input type="text" name="app_name" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                                   placeholder="Enter app name" 
                                   required>
                        </div>

                        <!-- Price Selection -->
                        <div class="mb-6">
                            <label class="block text-gray-700 font-bold mb-2">
                                Price per Post
                            </label>
                            <div class="flex flex-wrap gap-3">
                                <label class="flex items-center">
                                    <input type="radio" name="price" value="0.20" class="hidden peer">
                                    <span class="px-6 py-3 border-2 border-gray-300 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 font-bold cursor-pointer hover:border-green-400">
                                        ₹0.20
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="price" value="0.40" class="hidden peer" checked>
                                    <span class="px-6 py-3 border-2 border-gray-300 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 font-bold cursor-pointer hover:border-green-400">
                                        ₹0.40
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="price" value="0.50" class="hidden peer">
                                    <span class="px-6 py-3 border-2 border-gray-300 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 font-bold cursor-pointer hover:border-green-400">
                                        ₹0.50
                                    </span>
                                </label>
                                <label class="flex items-center cursor-pointer gap-2">
                                    <input type="radio" 
                                        name="price" 
                                        value="custom" 
                                        class="peer hidden">

                                    <span class="px-6 py-3 border-2 border-gray-300 rounded-lg
                                                peer-checked:border-green-500
                                                peer-checked:bg-green-50
                                                peer-checked:text-green-700
                                                font-bold hover:border-green-400 flex items-center gap-2">

                                        ₹
                                        <input type="number"
                                            name="custom_price"
                                            class="w-20 px-2 border-0 focus:ring-0 bg-transparent outline-none"
                                            placeholder="0.10+"
                                            min="0.10"
                                            step="0.01"
                                            max="10"
                                            inputmode="decimal">
                                    </span>
                                </label>

                            </div>
                        </div>

                        <!-- Comments -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-gray-700 font-bold">
                                    Comments List
                                </label>
                                <button type="button" onclick="addCommentField()" 
                                        class="text-sm text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-plus mr-1"></i> Add More
                                </button>
                            </div>
                            
                            <div id="commentsContainer" class="space-y-3">
                                <!-- Comment fields will be added here -->
                                <div class="flex items-center">
                                    <input type="text" name="comments[]" 
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                                           placeholder="Enter a comment" 
                                           required>
                                    <button type="button" onclick="removeCommentField(this)" 
                                            class="ml-2 text-red-500 hover:text-red-700">
                                        <i /. class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600 mt-2">
                                Each comment will be assigned to one user only. Add multiple comments for multiple users.
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-4 rounded-xl font-bold text-lg hover:from-green-600 hover:to-green-700">
                            <i class="fas fa-plus-circle mr-2"></i> Create Post
                        </button>
                    </form>
                    
                    <!-- Message -->
                    <div id="createPostMessage" class="mt-4 text-center hidden"></div>
                </div>
            </div>

            <!-- Recent Posts -->
            <div class="bg-white rounded-xl shadow mt-8">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold">Recent Posts</h2>
                        <button onclick="loadRecentPosts()" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div id="recentPosts" class="space-y-4">
                        <div class="text-center py-8">
                            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                            <p class="mt-2 text-gray-600">Loading posts...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Statistics & Actions -->
        <div class="space-y-6">
            <!-- Statistics -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">Post Statistics</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600">Total Posts</p>
                        <p id="totalPosts" class="text-2xl font-bold">0</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Active Posts</p>
                        <p id="activePostsCount" class="text-2xl font-bold text-green-600">0</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Comments</p>
                        <p id="totalComments" class="text-2xl font-bold">0</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Inactive Posts</p>
                        <p id="usedComments" class="text-2xl font-bold text-blue-600">0</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <!-- <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <button onclick="exportPosts()" 
                            class="w-full text-left p-3 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-file-export text-blue-500 mr-3"></i>
                        <span class="font-bold">Export Posts</span>
                    </button>
                    
                    <button onclick="bulkAddComments()" 
                            class="w-full text-left p-3 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-plus-circle text-green-500 mr-3"></i>
                        <span class="font-bold">Bulk Add Comments</span>
                    </button>
                    
                    <button onclick="manageCategories()" 
                            class="w-full text-left p-3 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-tags text-purple-500 mr-3"></i>
                        <span class="font-bold">Manage Categories</span>
                    </button>
                </div>
            </div> -->

            <!-- Recent Comments -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">Recent Comments</h2>
                <div id="recentComments" class="space-y-3">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Comments Modal -->
<!-- <div id="bulkCommentsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">Bulk Add Comments</h2>
        </div>
        <div class="p-6 overflow-auto max-h-[60vh]">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Select Post</label>
                <select id="bulkPostSelect" class="w-full px-4 py-2 border rounded-lg">
             
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">
                    Comments (One per line)
                </label>
                <textarea id="bulkComments" rows="10" 
                          class="w-full px-4 py-2 border rounded-lg" 
                          placeholder="Enter comments, one per line"></textarea>
                <p class="text-sm text-gray-600 mt-2">
                    Each line will be added as a separate comment
                </p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideModal('bulkCommentsModal')" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="button" onclick="submitBulkComments()" 
                        class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                    Add Comments
                </button>
            </div>
        </div>
    </div>
</div> -->

<script>
let commentCount = 1;

document.addEventListener('DOMContentLoaded', function() {
    loadPostStatistics();
    loadRecentPosts();
    loadRecentComments();
    
    // Add initial comment fields
    for (let i = 1; i < 1; i++) {
        addCommentField();
    }
});

function addCommentField() {
    const container = document.getElementById('commentsContainer');
    const div = document.createElement('div');
    div.className = 'flex items-center';
    div.innerHTML = `
        <input type="text" name="comments[]" 
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
               placeholder="Enter a comment" 
               required>
        <button type="button" onclick="removeCommentField(this)" 
                class="ml-2 text-red-500 hover:text-red-700">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
    commentCount++;
}

function removeCommentField(button) {
    if (commentCount > 1) {
        button.parentElement.remove();
        commentCount--;
    } else {
        window.showToast('At least one comment is required', 2500, 'warning');
    }
}

// Handle form submission
document.getElementById('createPostForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const comments = Array.from(formData.getAll('comments[]')).filter(comment => comment.trim() !== '');
    
    if (comments.length === 0) {
        showMessage('Please add at least one comment', 'error');
        return;
    }
    
    // Get price value
    const priceValue = formData.get('price');
    const price = priceValue === 'custom' ? formData.get('custom_price') : priceValue;
    
    if (!price || price < .10 || price > 10) {
        showMessage('Please select a valid price (₹0.1-₹10)', 'error');
        return;
    }
    
    const data = {
        ajax: 'true',
        action: 'create_post',
        app_link: formData.get('app_link'),
        app_name: formData.get('app_name'),
        price: price,
        comments: comments
    };
    
    // Show loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('ajax/admin.php?action=create_post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            this.reset();
            commentCount = 1;
            document.getElementById('commentsContainer').innerHTML = '';
            addCommentField();
            
            // Refresh data
            loadPostStatistics();
            loadRecentPosts();
            loadRecentComments();
        } else {
            showMessage(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Failed to create post', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

function showMessage(message, type) {
    const toastType = type === 'success' ? 'success' : 'error';
    window.showToast(message, 2500, toastType);
}

async function loadPostStatistics() {
    try {
        const response = await fetch('ajax/admin.php?action=get_post_stats');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalPosts').textContent = data.stats.total || 0;
            document.getElementById('activePostsCount').textContent = data.stats.active || 0;
            document.getElementById('totalComments').textContent = data.stats.total_comments || 0;
            document.getElementById('usedComments').textContent = data.stats.inactive || 0;
        }
    } catch (error) {
        console.error('Error loading post statistics:', error);
    }
}

async function loadRecentPosts() {
    try {
        const response = await fetch('ajax/admin.php?action=get_recent_posts');
        const data = await response.json();
        
        if (data.success && data.posts.length > 0) {
            renderRecentPosts(data.posts);
        } else {
            document.getElementById('recentPosts').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-newspaper text-2xl text-gray-300"></i>
                    <p class="mt-2 text-gray-600">No posts yet</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading recent posts:', error);
    }
}

function renderRecentPosts(posts) {
    const container = document.getElementById('recentPosts');
    let html = '';
    
    posts.forEach(post => {
        const usedPercentage = post.comment_count > 0 ? 
            Math.round((post.used_comments / post.comment_count) * 100) : 0;
        
        html += `
            <div class="border rounded-lg p-4 hover:bg-gray-50">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h3 class="font-bold">${post.app_name}</h3>
                    </div>
                    <div class="text-right">
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full font-bold">
                            ₹${post.price}
                        </span>
                        <span class="block text-xs text-gray-600 mt-1">
                            ${post.status === 'active' ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                </div>
                
                <div class="flex items-center justify-between text-sm">
                    <div>
                        <span class="text-gray-600">Comments: </span>
                        <span class="font-bold">${post.used_comments || 0}/${post.comment_count || 0}</span>
                      
                    </div>
                    <div class="text-right">
                        <span class="text-gray-600">${usedPercentage}% used</span>
                    </div>
                </div>
                
                <div class="mt-3 flex space-x-2">
                    <button onclick="editPost(${post.id})" 
                            class="flex-1 bg-blue-500 text-white py-2 rounded text-sm hover:bg-blue-600">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                    <button onclick="managePostComments(${post.id})" 
                            class="flex-1 bg-purple-500 text-white py-2 rounded text-sm hover:bg-purple-600">
                        <i class="fas fa-comments mr-1"></i> Comments
                    </button>
                    <button onclick="togglePostStatus(${post.id}, '${post.status}')" 
                            class="flex-1 ${post.status === 'active' ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'} text-white py-2 rounded text-sm">
                        <i class="fas ${post.status === 'active' ? 'fa-pause' : 'fa-play'} mr-1"></i>
                        ${post.status === 'active' ? 'Pause' : 'Activate'}
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

async function loadRecentComments() {
    try {
        const response = await fetch('ajax/admin.php?action=get_recent_comments');
        const data = await response.json();
        
        if (data.success && data.comments.length > 0) {
            renderRecentComments(data.comments);
        } else {
            document.getElementById('recentComments').innerHTML = `
                <div class="text-center py-4">
                    <p class="text-gray-600">No comments yet</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading recent comments:', error);
    }
}

function renderRecentComments(comments) {
    const container = document.getElementById('recentComments');
    let html = '';
    
    comments.slice(0, 5).forEach(comment => {
        const statusClass = comment.is_used ? 'line-through text-gray-500' : 'text-gray-800';
        const statusIcon = comment.is_used ? 
            '<i class="fas fa-check-circle text-green-500 mr-1"></i>' : 
            '<i class="fas fa-clock text-yellow-500 mr-1"></i>';
        
        html += `
            <div class="text-sm p-2 border rounded hover:bg-gray-50">
                <div class="flex justify-between mb-1">
                    <span class="${statusClass}">
                        ${statusIcon}
                        ${comment.comment_text.substring(0, 50)}...
                    </span>
                    <span class="text-xs text-gray-500">
                        ${comment.is_used ? 'Used' : 'Available'}
                    </span>
                </div>
                <div class="text-xs text-gray-500">
                    ${comment.app_name} • ₹${comment.price}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// function bulkAddComments() {
//     // Load posts for selection
//     fetch('ajax/admin.php?action=get_posts_list')
//         .then(response => response.json())
//         .then(data => {
//             if (data.success) {
//                 const select = document.getElementById('bulkPostSelect');
//                 select.innerHTML = '<option value="">Select a post</option>';
                
//                 data.posts.forEach(post => {
//                     select.innerHTML += `<option value="${post.id}">${post.app_name} (₹${post.price})</option>`;
//                 });
                
//                 showModal('bulkCommentsModal');
//             }
//         });
// }

function submitBulkComments() {
    const postId = document.getElementById('bulkPostSelect').value;
    const commentsText = document.getElementById('bulkComments').value;
    
    if (!postId || !commentsText.trim()) {
        window.showToast('Please select a post and enter comments', 2500, 'warning');
        return;
    }
    
    const comments = commentsText.split('\n')
        .map(comment => comment.trim())
        .filter(comment => comment !== '');
    
    if (comments.length === 0) {
        window.showToast('Please enter valid comments', 2500, 'warning');
        return;
    }
    
    const data = {
        ajax: 'true',
        action: 'bulk_add_comments',
        post_id: postId,
        comments: comments
    };
    
    fetch('ajax/admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            window.showToast(`Added ${result.added_count} comments successfully!`, 2500, 'success');
            hideModal('bulkCommentsModal');
            document.getElementById('bulkComments').value = '';
            loadPostStatistics();
            loadRecentComments();
        } else {
            window.showToast('Error: ' + result.message, 2500, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.showToast('Failed to add comments', 2500, 'error');
    });
}

function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.getElementById(modalId).classList.add('flex');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.remove('flex');
    document.getElementById(modalId).classList.add('hidden');
}

function editPost(postId) {
    window.location.href = `?page=admin-post-edit&id=${postId}`;
}

function managePostComments(postId) {
    window.location.href = `?page=admin-post-comments&id=${postId}`;
}

function showConfirmDialog(message, onConfirm) {
    const modal = document.getElementById('confirmModal');
    const msg = document.getElementById('confirmMessage');
    const okBtn = document.getElementById('confirmOk');
    const cancelBtn = document.getElementById('confirmCancel');

    msg.textContent = message;
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        okBtn.onclick = null;
        cancelBtn.onclick = null;
    };

    okBtn.onclick = () => {
        close();
        onConfirm();
    };

    cancelBtn.onclick = close;
}

function togglePostStatus(postId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const message = newStatus === 'active'
        ? 'Do you want to activate this post?'
        : 'Do you want to pause this post?';

    showConfirmDialog(message, () => {

        const formData = new FormData();
        formData.append('action', 'toggle_post_status');
        formData.append('post_id', postId);
        formData.append('status', newStatus);

        fetch('ajax/admin.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.showToast(data.message || 'Post status updated successfully', 2500, 'success');
                loadRecentPosts();
                loadPostStatistics();
            } else {
                window.showToast(data.message || 'Something went wrong', 2500, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.showToast('Failed to update post status', 2500, 'error');
        });

    });
}


// function exportPosts() {
//     alert('Export feature coming soon!');
// }

// function manageCategories() {
//     alert('Category management coming soon!');
// }
</script>

<style>
.line-through {
    text-decoration: line-through;
}

/* Custom radio button styling */
input[type="radio"]:checked + span {
    border-color: #10b981;
    background-color: #f0fdf4;
    color: #065f46;
}

/* Scrollbar for bulk comments modal */
#bulkComments {
    resize: vertical;
}
</style>
<!-- Confirmation Dialog -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm text-center">
        <h3 class="text-lg font-semibold mb-3" id="confirmTitle">Confirm Action</h3>
        <p class="text-gray-600 mb-5" id="confirmMessage"></p>

        <div class="flex justify-center gap-3">
            <button id="confirmCancel"
                class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">
                Cancel
            </button>
            <button id="confirmOk"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                Yes, Continue
            </button>
        </div>
    </div>
</div>

 </body>
 </html>