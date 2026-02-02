<?php
// Safety check
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$postId = $_GET['id'] ?? 0;
if (!$postId) {
    echo '<div class="max-w-4xl mx-auto p-6"><p class="text-red-600 font-bold">Invalid Post ID</p></div>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>
    <title>Document</title>
</head>
<body>
    <div class="max-w-4xl mx-auto py-6 px-4">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Post</h1>
        <p class="text-gray-600">Update app details and pricing</p>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="bg-white rounded-lg shadow-lg p-8">
        <div class="flex items-center justify-center">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mr-4"></i>
            <p class="text-gray-600 text-lg">Loading post details...</p>
        </div>
    </div>

    <!-- Form Container -->
    <div id="formContainer" class="hidden bg-white rounded-lg shadow-lg p-8">
        
        <!-- Tabs -->
        <div class="flex border-b border-gray-200 mb-6">
            <button onclick="switchTab('basic')" id="basicTab" 
                    class="px-6 py-3 font-semibold text-blue-600 border-b-2 border-blue-600 transition">
                <i class="fas fa-info-circle mr-2"></i>Basic Info
            </button>
            <button onclick="switchTab('preview')" id="previewTab" 
                    class="px-6 py-3 font-semibold text-gray-600 border-b-2 border-transparent hover:text-blue-600 transition">
                <i class="fas fa-eye mr-2"></i>Preview
            </button>
        </div>

        <!-- Basic Info Tab -->
        <div id="basicTab-content" class="block">
            <form id="editPostForm" class="space-y-6">
                <input type="hidden" id="postId" name="post_id">

                <!-- App Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-mobile-alt text-blue-600 mr-2"></i>App Name
                    </label>
                    <input type="text" id="appName" name="app_name" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                           placeholder="Enter app name">
                    <p class="text-xs text-gray-500 mt-1">The name of the app users will post comments on</p>
                </div>

                <!-- App Link -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-link text-green-600 mr-2"></i>App Link
                    </label>
                    <input type="url" id="appLink" name="app_link" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                           placeholder="https://play.google.com/store/apps/details?id=...">
                    <p class="text-xs text-gray-500 mt-1">Google Play Store link for the app</p>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-rupee-sign text-green-600 mr-2"></i>Reward Amount (₹)
                    </label>
                    <div class="flex items-center">
                        <span class="text-xl font-bold text-gray-700 mr-2">₹</span>
                        <input type="number" id="price" name="price" required step="0.01" min="0"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                               placeholder="0.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Amount users will earn for completing the task</p>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-toggle-on text-purple-600 mr-2"></i>Status
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="status" value="active" id="statusActive" required
                                   class="w-4 h-4 text-blue-600">
                            <span class="ml-3 text-gray-700">
                                <i class="fas fa-check-circle text-green-600 mr-1"></i>Active
                            </span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="status" value="inactive" id="statusInactive"
                                   class="w-4 h-4 text-red-600">
                            <span class="ml-3 text-gray-700">
                                <i class="fas fa-ban text-red-600 mr-1"></i>Inactive
                            </span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Active posts are visible to users</p>
                </div>

                <!-- Form Actions - Mobile Responsive -->
                <div class="grid grid-cols-2 md:flex gap-2 md:gap-3 pt-6 border-t border-gray-200">
                    <button type="button" onclick="goBack()"
                            class="col-span-1 px-3 md:px-6 py-2 md:py-3 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition text-sm md:text-base md:flex-1">
                        <i class="fas fa-arrow-left mr-0 md:mr-2"></i><span class="hidden md:inline">Cancel</span>
                    </button>
                    <button type="button" onclick="switchTab('preview')"
                            class="col-span-1 px-3 md:px-6 py-2 md:py-3 bg-blue-100 text-blue-700 rounded-lg font-semibold hover:bg-blue-200 transition text-sm md:text-base md:flex-1">
                        <i class="fas fa-eye mr-0 md:mr-2"></i><span class="hidden md:inline">Preview</span>
                    </button>
                    <button type="button" onclick="deletePost()"
                            class="col-span-1 px-3 md:px-6 py-2 md:py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition text-sm md:text-base">
                        <i class="fas fa-trash mr-0 md:mr-2"></i><span class="hidden md:inline">Delete</span>
                    </button>
                    <button type="submit"
                            class="col-span-1 md:flex-1 px-3 md:px-6 py-2 md:py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition shadow-md hover:shadow-lg text-sm md:text-base">
                        <i class="fas fa-save mr-0 md:mr-2"></i><span class="hidden md:inline">Save</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Tab -->
        <div id="previewTab-content" class="hidden">
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-6 space-y-4">
                
                <!-- Preview Card -->
                <div class="bg-white border border-gray-300 rounded-lg shadow-md p-6">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 font-semibold">App Name</p>
                        <p class="text-2xl font-bold text-gray-800" id="previewAppName">-</p>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600 font-semibold">App Link</p>
                        <a id="previewAppLink" href="#" target="_blank" class="text-blue-600 hover:text-blue-800 underline break-all text-sm">
                            -
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600 font-semibold">Reward Amount</p>
                            <p class="text-2xl font-bold text-green-600" id="previewPrice">₹0</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-semibold">Status</p>
                            <p id="previewStatus" class="text-sm font-semibold">
                                <span id="previewStatusBadge" class="inline-block px-3 py-1 rounded-full text-white bg-green-600">
                                    Active
                                </span>
                            </p>
                        </div>
                    </div>
                </div>


                <!-- Preview Actions -->
                <div class="grid grid-cols-2 md:flex gap-2 md:gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="switchTab('basic')"
                            class="col-span-1 px-3 md:px-6 py-2 md:py-3 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition text-sm md:text-base md:flex-1">
                        <i class="fas fa-arrow-left mr-0 md:mr-2"></i><span class="hidden md:inline">Back to Edit</span>
                    </button>
                    <button type="button" onclick="document.getElementById('editPostForm').dispatchEvent(new Event('submit'))"
                            class="col-span-1 md:flex-1 px-3 md:px-6 py-2 md:py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-semibold hover:from-green-700 hover:to-green-800 transition shadow-md hover:shadow-lg text-sm md:text-base">
                        <i class="fas fa-check mr-0 md:mr-2"></i><span class="hidden md:inline">Confirm & Save</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Container -->
    <div id="errorContainer" class="hidden bg-red-50 border-2 border-red-300 rounded-lg p-6">
        <div class="flex items-start">
            <i class="fas fa-exclamation-circle text-red-600 text-2xl mr-4 mt-1"></i>
            <div>
                <h3 class="font-bold text-red-800 text-lg mb-2">Error Loading Post</h3>
                <p id="errorMessage" class="text-red-700 mb-4">-</p>
                <button onclick="goBack()" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Go Back
                </button>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <div id="successMessage" class="fixed top-6 right-6 bg-green-50 border-l-4 border-green-600 rounded-lg p-4 shadow-lg hidden z-50">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
            <div>
                <p class="font-bold text-green-800">Success!</p>
                <p class="text-green-700 text-sm">Post updated successfully</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<script>
let currentTab = 'basic';
const postId = new URLSearchParams(window.location.search).get('id');

// ================= LOAD POST DATA =================
function loadPostData() {
    if (!postId) {
        showError('Invalid Post ID');
        return;
    }

    // Agar get_post available nahi hai to form khali dikha do
    // Admin manually data fill kar sakta hai
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('formContainer').classList.remove('hidden');
    
    // Post ID set karo
    document.getElementById('postId').value = postId;
    
    // Try to load from database via AJAX
    loadPostViaAjax();
}

function loadPostViaAjax() {
    // Direct query karo database se (agar endpoint available ho)
    fetch(`ajax/admin.php?action=get_post&id=${postId}`, {
        method: 'GET'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success && d.post) {
            populateForm(d.post);
        }
        // Agar error ho to koi issue nahi, form khali rehega
        setupLivePreview();
    })
    .catch(err => {
        console.warn('Could not load post data, form is ready for manual input');
        setupLivePreview();
    });
}

// ================= POPULATE FORM =================
function populateForm(post) {
    document.getElementById('postId').value = post.id;
    document.getElementById('appName').value = post.app_name || '';
    document.getElementById('appLink').value = post.app_link || '';
    document.getElementById('price').value = parseFloat(post.price || 0).toFixed(2);
    
    if (post.status === 'inactive') {
        document.getElementById('statusInactive').checked = true;
    } else {
        document.getElementById('statusActive').checked = true;
    }

    // Setup live preview
    setupLivePreview();
}

// ================= SETUP LIVE PREVIEW =================
function setupLivePreview() {
    const appNameInput = document.getElementById('appName');
    const appLinkInput = document.getElementById('appLink');
    const priceInput = document.getElementById('price');
    const statusInputs = document.querySelectorAll('input[name="status"]');

    const updatePreview = () => {
        const appName = appNameInput.value || 'App Name';
        const appLink = appLinkInput.value || 'app-link';
        const price = parseFloat(priceInput.value || 0).toFixed(2);
        const status = document.querySelector('input[name="status"]:checked').value;

        // Preview tab
        document.getElementById('previewAppName').textContent = appName;
        document.getElementById('previewAppLink').textContent = appLink;
        document.getElementById('previewAppLink').href = appLink;
        document.getElementById('previewPrice').textContent = '₹' + price;
        
        const statusBadge = document.getElementById('previewStatusBadge');
        if (status === 'inactive') {
            statusBadge.textContent = 'Inactive';
            statusBadge.className = 'inline-block px-3 py-1 rounded-full text-white bg-red-600';
        } else {
            statusBadge.textContent = 'Active';
            statusBadge.className = 'inline-block px-3 py-1 rounded-full text-white bg-green-600';
        }

        // User preview
        document.getElementById('userPreviewAppName').textContent = appName;
        document.getElementById('userPreviewAppLink').textContent = appLink;
        document.getElementById('userPreviewPrice').textContent = '₹' + price;
    };

    appNameInput.addEventListener('input', updatePreview);
    appLinkInput.addEventListener('input', updatePreview);
    priceInput.addEventListener('input', updatePreview);
    statusInputs.forEach(input => input.addEventListener('change', updatePreview));

    // Initial update
    updatePreview();
}

// ================= TAB SWITCHING =================
function switchTab(tab) {
    currentTab = tab;

    // Hide all tabs
    document.getElementById('basicTab-content').classList.add('hidden');
    document.getElementById('previewTab-content').classList.add('hidden');

    // Remove active state from all tab buttons
    document.getElementById('basicTab').classList.remove('border-b-2', 'border-blue-600', 'text-blue-600');
    document.getElementById('basicTab').classList.add('border-b-2', 'border-transparent', 'text-gray-600');
    document.getElementById('previewTab').classList.remove('border-b-2', 'border-blue-600', 'text-blue-600');
    document.getElementById('previewTab').classList.add('border-b-2', 'border-transparent', 'text-gray-600');

    // Show selected tab
    if (tab === 'basic') {
        document.getElementById('basicTab-content').classList.remove('hidden');
        document.getElementById('basicTab').classList.add('border-b-2', 'border-blue-600', 'text-blue-600');
        document.getElementById('basicTab').classList.remove('border-transparent', 'text-gray-600');
    } else {
        document.getElementById('previewTab-content').classList.remove('hidden');
        document.getElementById('previewTab').classList.add('border-b-2', 'border-blue-600', 'text-blue-600');
        document.getElementById('previewTab').classList.remove('border-transparent', 'text-gray-600');
    }
}

// ================= FORM SUBMISSION =================
document.getElementById('editPostForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'update_post');

    // Debug - form data print karo
    console.log('=== FORM DATA ===');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    console.log('=================');

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

    fetch('ajax/admin.php', { method: 'POST', body: formData })
        .then(r => {
            console.log('Response status:', r.status);
            return r.text(); // First get as text to see raw response
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const d = JSON.parse(text);
                console.log('Parsed JSON:', d);
                
                if (d.success) {
                    showSuccess();
                    setTimeout(() => {
                        window.location.href = '?page=admin-posts';
                    }, 2000);
                } else {
                    alert('Error: ' + (d.message || 'Failed to update post'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch(e) {
                console.error('JSON Parse error:', e);
                console.error('Response was:', text);
                alert('Server error: ' + text);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            alert('An error occurred: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
});

// ================= HELPERS =================
function showError(message) {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('formContainer').classList.add('hidden');
    document.getElementById('errorContainer').classList.remove('hidden');
    document.getElementById('errorMessage').textContent = message;
}

function showSuccess() {
    const msg = document.getElementById('successMessage');
    msg.classList.remove('hidden');
    setTimeout(() => {
        msg.classList.add('hidden');
    }, 3000);
}

function goBack() {
    window.location.href = '?page=admin-posts';
}

// ================= DELETE POST =================
function deletePost() {
    if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        return;
    }

    if (!confirm('This will also delete all comments and user assignments for this post. Continue?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete_post');
    formData.append('post_id', postId);

    const deleteBtn = document.querySelector('button[onclick="deletePost()"]');
    const originalText = deleteBtn.innerHTML;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';

    fetch('ajax/admin.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                alert('Post deleted successfully!');
                window.location.href = '?page=admin-posts';
            } else {
                alert('Error: ' + (d.message || 'Failed to delete post'));
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalText;
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred');
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        });
}

// INIT
loadPostData();
</script>