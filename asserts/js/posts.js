/**
 * Posts Management Page
 */

let allPosts = [];
let filteredPosts = [];
let currentFilter = 'all';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadPosts();
    setupEventListeners();
});

function setupEventListeners() {
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterPosts, 300));
    }
    
    // Create post form
    const createPostForm = document.getElementById('createPostForm');
    if (createPostForm) {
        createPostForm.addEventListener('submit', handleCreatePost);
    }
    
    // Filter dropdown
    const filterDropdown = document.getElementById('filterDropdown');
    const filterMenu = document.getElementById('filterMenu');
    if (filterDropdown && filterMenu) {
        filterDropdown.addEventListener('click', () => {
            filterMenu.classList.toggle('hidden');
        });
        
        document.addEventListener('click', (e) => {
            if (!filterDropdown.contains(e.target) && !filterMenu.contains(e.target)) {
                filterMenu.classList.add('hidden');
            }
        });
    }
}

async function loadPosts(status = 'all') {
    try {
        showLoading(true);
        currentFilter = status;
        
        const result = await AdminAPI.getPosts(status);
        
        if (result.success) {
            allPosts = result.posts || [];
            filteredPosts = [...allPosts];
            
            // Update stats
            updateStats();
            
            // Display posts
            displayPosts(filteredPosts);
        } else {
            showToast(result.message || 'Failed to load posts', 'error');
        }
    } catch (error) {
        console.error('Error loading posts:', error);
        showToast('Failed to load posts: ' + error.message, 'error');
        displayPosts([]);
    } finally {
        showLoading(false);
    }
}

function updateStats() {
    const stats = {
        total: allPosts.length,
        active: allPosts.filter(p => p.status === 'active').length,
        inactive: allPosts.filter(p => p.status === 'inactive').length,
        totalComments: allPosts.reduce((sum, p) => sum + parseInt(p.total_comments || 0), 0),
        availableComments: allPosts.reduce((sum, p) => sum + parseInt(p.available_comments || 0), 0)
    };
    
    // Update stat cards
    document.getElementById('totalPosts').textContent = stats.total;
    document.getElementById('activePosts').textContent = stats.active;
    document.getElementById('inactivePosts').textContent = stats.inactive;
    document.getElementById('totalComments').textContent = stats.totalComments;
    document.getElementById('availableComments').textContent = stats.availableComments;
    
    // Update table count
    document.getElementById('totalCount').textContent = filteredPosts.length;
    document.getElementById('startCount').textContent = filteredPosts.length > 0 ? '1' : '0';
    document.getElementById('endCount').textContent = filteredPosts.length;
}

function displayPosts(posts) {
    const tbody = document.getElementById('postsTable');
    
    if (!posts || posts.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center">
                    <i class="fas fa-newspaper text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">No posts found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = posts.map(p => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">
                <div>
                    <p class="font-medium text-gray-900">${escapeHtml(p.app_name)}</p>
                    <a href="${escapeHtml(p.app_link)}" target="_blank" 
                       class="text-xs text-blue-500 hover:underline">
                        <i class="fas fa-external-link-alt mr-1"></i>View App
                    </a>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="text-center">
                    <p class="text-lg font-bold text-green-600">${formatCurrency(p.price)}</p>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="text-center">
                    <p class="text-lg font-medium text-gray-900">${p.total_comments || 0}</p>
                    <div class="flex items-center justify-center space-x-2 mt-1">
                        <span class="text-xs text-green-600">
                            <i class="fas fa-check-circle mr-1"></i>${p.available_comments || 0} available
                        </span>
                        <span class="text-xs text-gray-500">
                            <i class="fas fa-times-circle mr-1"></i>${p.used_comments || 0} used
                        </span>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                ${formatDate(p.created_at)}
            </td>
            <td class="px-6 py-4">
                ${getStatusBadge(p.status)}
            </td>
            <td class="px-6 py-4">
                <div class="flex space-x-2">
                    <button onclick="viewPostComments(${p.id})" 
                            class="text-blue-600 hover:text-blue-900" 
                            title="View Comments">
                        <i class="fas fa-comments text-lg"></i>
                    </button>
                    <button onclick="openEditModal(${p.id})" 
                            class="text-green-600 hover:text-green-900" 
                            title="Edit Post">
                        <i class="fas fa-edit text-lg"></i>
                    </button>
                    <button onclick="togglePostStatus(${p.id}, '${p.status}')" 
                            class="text-yellow-600 hover:text-yellow-900" 
                            title="${p.status === 'active' ? 'Deactivate' : 'Activate'}">
                        <i class="fas fa-${p.status === 'active' ? 'pause' : 'play'}-circle text-lg"></i>
                    </button>
                    <button onclick="deletePost(${p.id})" 
                            class="text-red-600 hover:text-red-900" 
                            title="Delete Post">
                        <i class="fas fa-trash text-lg"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    updateStats();
}

function filterPosts() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    
    filteredPosts = allPosts.filter(p => {
        return !searchTerm || 
            p.app_name.toLowerCase().includes(searchTerm) ||
            p.app_link.toLowerCase().includes(searchTerm);
    });
    
    displayPosts(filteredPosts);
}

function filterPostsByStatus(status) {
    loadPosts(status);
    document.getElementById('filterMenu')?.classList.add('hidden');
}

async function handleCreatePost(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {
        app_name: formData.get('app_name'),
        app_link: formData.get('app_link'),
        price: formData.get('price'),
        comments: formData.get('comments')
    };
    
    try {
        showLoading(true);
        const result = await AdminAPI.createPost(data);
        
        if (result.success) {
            showToast('Post created successfully!', 'success');
            e.target.reset();
            closeModal('createPostModal');
            loadPosts(currentFilter);
        } else {
            if (result.errors) {
                const errorMessages = Object.values(result.errors).join(', ');
                showToast(errorMessages, 'error');
            } else {
                showToast(result.message || 'Failed to create post', 'error');
            }
        }
    } catch (error) {
        console.error('Error creating post:', error);
        showToast('Failed to create post: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

async function viewPostComments(postId) {
    try {
        showLoading(true);
        const result = await AdminAPI.getPostComments(postId);
        
        if (result.success) {
            showCommentsModal(postId, result.comments);
        } else {
            showToast(result.message || 'Failed to load comments', 'error');
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        showToast('Failed to load comments: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

function showCommentsModal(postId, comments) {
    const modal = document.getElementById('commentsModal');
    if (!modal) return;
    
    document.getElementById('commentsPostId').value = postId;
    
    const tbody = document.getElementById('commentsTable');
    tbody.innerHTML = comments.map((c, index) => `
        <tr class="${c.is_used ? 'bg-gray-50' : ''}">
            <td class="px-4 py-2 text-center">${index + 1}</td>
            <td class="px-4 py-2">${escapeHtml(c.comment_text)}</td>
            <td class="px-4 py-2 text-center">${getStatusBadge(c.status)}</td>
            <td class="px-4 py-2 text-center text-sm text-gray-500">
                ${formatDate(c.created_at)}
            </td>
        </tr>
    `).join('');
    
    modal.classList.remove('hidden');
}

async function addComment() {
    const postId = document.getElementById('commentsPostId').value;
    const commentText = document.getElementById('newComment').value;
    
    if (!commentText.trim()) {
        showToast('Please enter a comment', 'error');
        return;
    }
    
    try {
        showLoading(true);
        const result = await AdminAPI.addComment(postId, commentText);
        
        if (result.success) {
            showToast('Comment added successfully!', 'success');
            document.getElementById('newComment').value = '';
            viewPostComments(postId); // Reload comments
        } else {
            showToast(result.message || 'Failed to add comment', 'error');
        }
    } catch (error) {
        console.error('Error adding comment:', error);
        showToast('Failed to add comment: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

async function togglePostStatus(postId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    
    try {
        showLoading(true);
        
        const post = allPosts.find(p => p.id === postId);
        const result = await AdminAPI.updatePost(postId, {
            app_name: post.app_name,
            app_link: post.app_link,
            price: post.price,
            status: newStatus
        });
        
        if (result.success) {
            showToast(`Post ${newStatus === 'active' ? 'activated' : 'deactivated'} successfully!`, 'success');
            loadPosts(currentFilter);
        } else {
            showToast(result.message || 'Failed to update post', 'error');
        }
    } catch (error) {
        console.error('Error updating post:', error);
        showToast('Failed to update post: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

async function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this post? This will also delete all associated comments and submissions.')) {
        return;
    }
    
    try {
        showLoading(true);
        const result = await AdminAPI.deletePost(postId);
        
        if (result.success) {
            showToast('Post deleted successfully!', 'success');
            loadPosts(currentFilter);
        } else {
            showToast(result.message || 'Failed to delete post', 'error');
        }
    } catch (error) {
        console.error('Error deleting post:', error);
        showToast('Failed to delete post: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

function openCreateModal() {
    document.getElementById('createPostModal')?.classList.remove('hidden');
}

function openEditModal(postId) {
    const post = allPosts.find(p => p.id === postId);
    if (!post) return;
    
    document.getElementById('editPostId').value = postId;
    document.getElementById('editAppName').value = post.app_name;
    document.getElementById('editAppLink').value = post.app_link;
    document.getElementById('editPrice').value = post.price;
    document.getElementById('editStatus').value = post.status;
    
    document.getElementById('editPostModal').classList.remove('hidden');
}

async function updatePost() {
    const postId = document.getElementById('editPostId').value;
    const data = {
        app_name: document.getElementById('editAppName').value,
        app_link: document.getElementById('editAppLink').value,
        price: document.getElementById('editPrice').value,
        status: document.getElementById('editStatus').value
    };
    
    try {
        showLoading(true);
        const result = await AdminAPI.updatePost(postId, data);
        
        if (result.success) {
            showToast('Post updated successfully!', 'success');
            closeModal('editPostModal');
            loadPosts(currentFilter);
        } else {
            showToast(result.message || 'Failed to update post', 'error');
        }
    } catch (error) {
        console.error('Error updating post:', error);
        showToast('Failed to update post: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

function closeModal(modalId) {
    document.getElementById(modalId)?.classList.add('hidden');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatCurrency(amount) {
    return '₹' + parseFloat(amount || 0).toFixed(2);
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusBadge(status) {
    const badges = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
        'active': 'bg-green-100 text-green-800',
        'inactive': 'bg-gray-100 text-gray-800',
        'Available': 'bg-green-100 text-green-800',
        'Used': 'bg-gray-100 text-gray-800'
    };
    
    return `<span class="px-2 py-1 rounded-full text-xs font-medium ${badges[status] || 'bg-gray-100 text-gray-800'}">
        ${status}
    </span>`;
}