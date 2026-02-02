<?php
// Safety check (extra, index.php already handles admin)
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}
?>

<div class="max-w-7xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">
        Pending Screenshot Verification
    </h1>

    <div id="pendingScreenshots"
         class="grid grid-cols-1 md:grid-cols-3 gap-4">
    </div>
</div>

<!-- ================= FULLSCREEN IMAGE VIEWER ================= -->
<div id="fullscreenImageModal"
     class="fixed inset-0 bg-black bg-opacity-95 hidden z-50 flex items-center justify-center p-4">
    <button onclick="closeFullscreenImage()"
            class="absolute top-4 right-6 text-white text-4xl hover:text-gray-300 transition">
        &times;
    </button>
    <img id="fullscreenImage" class="max-h-[95vh] max-w-[95vw] object-contain rounded-lg">
    <p class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white text-sm">Press ESC to close</p>
</div>

<!-- ================= FULLSCREEN MODAL ================= -->
<div id="ssModal"
     class="fixed inset-0 bg-black bg-opacity-90 hidden z-50 flex flex-col">

    <button onclick="closeSSModal()"
            class="absolute top-4 right-6 text-white text-3xl hover:text-gray-300 transition z-50">
        &times;
    </button>

    <div class="flex flex-col h-full">

        <!-- Header with info -->
        <div class="bg-gray-900 border-b border-gray-700 p-3 md:p-4">
            <div class="max-w-6xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4 text-white text-sm md:text-base">
                <div>
                    <p class="text-xs text-gray-400">Username</p>
                    <p class="font-bold truncate" id="modalUsername">-</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">App Name</p>
                    <p class="font-bold truncate" id="modalAppName">-</p>
                </div>
                <div class="hidden md:block">
                    <p class="text-xs text-gray-400">Reward Amount</p>
                    <p class="font-bold text-green-400" id="modalPrice">₹0</p>
                </div>
                <div class="hidden md:block">
                    <p class="text-xs text-gray-400">Assignment ID</p>
                    <p class="font-bold text-blue-400" id="modalAssignmentId">-</p>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col md:flex-row gap-0 md:gap-4 p-2 md:p-4 overflow-hidden">
            
            <!-- Image Section -->
            <div class="flex-1 flex flex-col items-center justify-center bg-black bg-opacity-50 rounded-lg p-2 md:p-0 overflow-auto">
                <div class="relative w-full h-full flex items-center justify-center">
                    <img id="ssImage"
                         class="max-h-full max-w-full object-contain rounded shadow-lg border border-gray-700 cursor-pointer hover:opacity-90 transition"
                         onclick="openFullscreenImage()"
                         title="Click to view in fullscreen">
                    <span class="absolute bottom-2 right-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded hidden md:block">
                        Click to expand
                    </span>
                </div>
            </div>
            
            <!-- Comment Section - Hidden on mobile, visible on desktop -->
            <div class="hidden md:block w-full md:w-80 bg-gray-800 rounded-lg p-4 border border-gray-700 overflow-y-auto max-h-[calc(100vh-200px)]">
                <h3 class="font-bold text-white text-lg mb-3">Assigned Comment</h3>
                <div class="bg-gray-900 p-4 rounded-lg border border-gray-600">
                    <p id="modalComment" class="text-white text-sm leading-relaxed font-mono break-words">
                        Loading...
                    </p>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-700">
                    <p class="text-xs text-gray-400 mb-2">Submission Time</p>
                    <p id="modalSubmissionTime" class="text-white text-sm">-</p>
                </div>
                <div class="mt-3 pt-4 border-t border-gray-700">
                    <p class="text-xs text-gray-400 mb-2">Reward Amount</p>
                    <p class="text-white font-bold text-green-400" id="modalPriceMobile">₹0</p>
                </div>
                <div class="mt-3">
                    <p class="text-xs text-gray-400 mb-2">Assignment ID</p>
                    <p class="text-white font-bold text-blue-400" id="modalAssignmentIdMobile">-</p>
                </div>
            </div>

            <!-- Mobile Drawer - Slide from bottom -->
            <div id="mobileCommentDrawer" class="fixed bottom-0 left-0 right-0 bg-gray-800 border-t-2 border-gray-700 p-4 rounded-t-lg shadow-lg md:hidden transform transition-transform duration-300 max-h-96 overflow-y-auto z-40">
                <button onclick="toggleMobileDrawer()" class="absolute top-2 right-4 text-white text-2xl">−</button>
                <h3 class="font-bold text-white text-lg mb-3">Details & Comment</h3>
                
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Assigned Comment</p>
                        <div class="bg-gray-900 p-3 rounded-lg border border-gray-600 max-h-40 overflow-y-auto">
                            <p id="modalCommentMobile" class="text-white text-xs leading-relaxed font-mono break-words">
                                Loading...
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Submission Time</p>
                        <p id="modalSubmissionTimeMobile" class="text-white text-sm">-</p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Reward Amount</p>
                        <p class="text-white font-bold text-green-400" id="modalPriceMobileDrawer">₹0</p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Assignment ID</p>
                        <p class="text-white font-bold text-blue-400" id="modalAssignmentIdMobileDrawer">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Actions -->
        <div class="bg-gray-900 border-t border-gray-700 p-3 md:p-4 flex justify-center gap-2 md:gap-3 flex-wrap md:pb-4">
            <button onclick="downloadImage()"
                    class="bg-blue-600 hover:bg-blue-700 px-4 md:px-6 py-2 rounded text-white font-semibold transition flex items-center gap-2 text-sm md:text-base">
                <i class="fas fa-download"></i><span class="hidden md:inline">Download</span>
            </button>

            <button onclick="approveSS()"
                    class="bg-green-600 hover:bg-green-700 px-4 md:px-6 py-2 rounded text-white font-semibold transition flex items-center gap-2 text-sm md:text-base">
                <i class="fas fa-check"></i><span class="hidden md:inline">Approve</span>
            </button>

            <button onclick="openRejectBox()"
                    class="bg-red-600 hover:bg-red-700 px-4 md:px-6 py-2 rounded text-white font-semibold transition flex items-center gap-2 text-sm md:text-base">
                <i class="fas fa-times"></i><span class="hidden md:inline">Reject</span>
            </button>

            <button onclick="toggleMobileDrawer()"
                    class="bg-gray-700 hover:bg-gray-600 px-4 md:px-6 py-2 rounded text-white font-semibold transition flex items-center gap-2 text-sm md:hidden">
                <i class="fas fa-info-circle"></i>Details
            </button>
        </div>
    </div>
</div>

<!-- ================= REJECT BOX ================= -->
<div id="rejectBox"
     class="fixed inset-0 bg-black bg-opacity-60 hidden z-50
            flex items-center justify-center p-4">

    <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
        <h3 class="font-bold text-lg mb-4">Reject Reason</h3>

        <textarea id="rejectReason"
                  class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                  placeholder="Enter reason for rejection..."
                  rows="4"></textarea>

        <div class="flex justify-end gap-3 mt-4">
            <button onclick="closeRejectBox()"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                Cancel
            </button>
            <button onclick="rejectSS()"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                Reject
            </button>
        </div>
    </div>
</div>

<script>
let currentAssignmentId = null;
let currentImagePath = null;
let currentAssignmentData = null;
let mobileDrawerOpen = false;

// ================= LOAD DATA =================
function loadPendingScreenshots() {
    fetch('ajax/admin.php?action=get_pending_screenshots')
        .then(r => r.json())
        .then(d => {
            const box = document.getElementById('pendingScreenshots');
            box.innerHTML = '';

            if (!d.screenshots || d.screenshots.length === 0) {
                box.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-check-circle text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500 text-lg">No pending screenshots</p>
                    </div>
                `;
                return;
            }

            d.screenshots.forEach(s => {
                box.innerHTML += `
                <div class="border border-gray-300 rounded-lg p-4 bg-white shadow hover:shadow-lg transition">
                    <div class="mb-3">
                        <p class="font-bold text-gray-800">${escapeHtml(s.username)}</p>
                        <p class="text-sm text-gray-600">
                            ${escapeHtml(s.app_name)} – <span class="font-semibold text-green-600">₹${parseFloat(s.price).toFixed(2)}</span>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">ID: ${s.id}</p>
                    </div>

                    <img src="${s.screenshot_path}"
                         class="mt-3 h-48 w-full object-cover rounded-lg cursor-pointer hover:opacity-80 transition border border-gray-200"
                         onclick="openSSModal(${s.id}, '${s.screenshot_path}', ${JSON.stringify(s).replace(/"/g, '&quot;')})"
                         title="Click to view full screen">
                    
                    <div class="flex gap-2 mt-3">
                        <button onclick="openSSModal(${s.id}, '${s.screenshot_path}', ${JSON.stringify(s).replace(/"/g, '&quot;')})"
                                class="flex-1 bg-blue-100 text-blue-700 py-2 rounded-lg text-sm font-semibold hover:bg-blue-200 transition">
                            <i class="fas fa-expand mr-1"></i>View Full
                        </button>
                    </div>
                </div>`;
            });
        })
        .catch(err => {
            console.error('Error loading screenshots:', err);
            const box = document.getElementById('pendingScreenshots');
            box.innerHTML = '<p class="text-red-500">Error loading screenshots</p>';
        });
}

// ================= MODAL =================
function openSSModal(id, img, data) {
    currentAssignmentId = id;
    currentImagePath = img;
    currentAssignmentData = data;
    mobileDrawerOpen = false;
    
    // Update header info
    document.getElementById('ssImage').src = img;
    document.getElementById('modalUsername').textContent = escapeHtml(data.username || '-');
    document.getElementById('modalAppName').textContent = escapeHtml(data.app_name || '-');
    document.getElementById('modalPrice').textContent = '₹' + parseFloat(data.price || 0).toFixed(2);
    document.getElementById('modalAssignmentId').textContent = id;
    
    // Update comment - Desktop
    document.getElementById('modalComment').textContent = escapeHtml(data.comment_text || 'No comment assigned');
    
    // Update comment - Mobile
    document.getElementById('modalCommentMobile').textContent = escapeHtml(data.comment_text || 'No comment assigned');
    
    // Update submission time
    if (data.submitted_at) {
        const date = new Date(data.submitted_at);
        const timeStr = date.toLocaleString();
        document.getElementById('modalSubmissionTime').textContent = timeStr;
        document.getElementById('modalSubmissionTimeMobile').textContent = timeStr;
    } else {
        document.getElementById('modalSubmissionTime').textContent = '-';
        document.getElementById('modalSubmissionTimeMobile').textContent = '-';
    }
    
    // Update mobile drawer
    document.getElementById('modalPriceMobile').textContent = '₹' + parseFloat(data.price || 0).toFixed(2);
    document.getElementById('modalPriceMobileDrawer').textContent = '₹' + parseFloat(data.price || 0).toFixed(2);
    document.getElementById('modalAssignmentIdMobile').textContent = id;
    document.getElementById('modalAssignmentIdMobileDrawer').textContent = id;
    
    document.getElementById('ssModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    closeMobileDrawer();
}

function closeSSModal() {
    document.getElementById('ssModal').classList.add('hidden');
    document.getElementById('rejectBox').classList.add('hidden');
    document.getElementById('rejectReason').value = '';
    document.body.style.overflow = 'auto';
}

function toggleMobileDrawer() {
    const drawer = document.getElementById('mobileCommentDrawer');
    if (mobileDrawerOpen) {
        closeMobileDrawer();
    } else {
        openMobileDrawer();
    }
}

function openMobileDrawer() {
    const drawer = document.getElementById('mobileCommentDrawer');
    drawer.style.transform = 'translateY(0)';
    mobileDrawerOpen = true;
}

function closeMobileDrawer() {
    const drawer = document.getElementById('mobileCommentDrawer');
    drawer.style.transform = 'translateY(100%)';
    mobileDrawerOpen = false;
}

// ================= FULLSCREEN IMAGE =================
function openFullscreenImage() {
    if (!currentImagePath) return;
    const fsModal = document.getElementById('fullscreenImageModal');
    const fsImage = document.getElementById('fullscreenImage');
    fsImage.src = currentImagePath;
    fsModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFullscreenImage() {
    document.getElementById('fullscreenImageModal').classList.add('hidden');
    document.body.style.overflow = 'hidden'; // Keep main modal scroll hidden
}

// ================= DOWNLOAD =================
function downloadImage() {
    if (!currentImagePath) {
        alert('No image to download');
        return;
    }

    const link = document.createElement('a');
    link.href = currentImagePath;
    link.download = `screenshot-${currentAssignmentId}-${new Date().getTime()}.jpg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ================= APPROVE =================
function approveSS() {
    const fd = new FormData();
    fd.append('action', 'approve_submission');
    fd.append('assignment_id', currentAssignmentId);

    fetch('ajax/admin.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                alert(d.message || 'Screenshot approved successfully');
                closeSSModal();
                loadPendingScreenshots();
            } else {
                alert('Error: ' + (d.message || 'Failed to approve'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred');
        });
}

// ================= REJECT =================
function openRejectBox() {
    document.getElementById('rejectBox').classList.remove('hidden');
    document.getElementById('rejectReason').focus();
}

function closeRejectBox() {
    document.getElementById('rejectBox').classList.add('hidden');
    document.getElementById('rejectReason').value = '';
}

function rejectSS() {
    const reason = document.getElementById('rejectReason').value.trim();

    if (!reason) {
        alert('Please enter a rejection reason');
        document.getElementById('rejectReason').focus();
        return;
    }

    if (!confirm('Are you sure you want to reject this screenshot?')) {
        return;
    }

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rejecting...';

    const fd = new FormData();
    fd.append('action', 'reject_submission');
    fd.append('assignment_id', currentAssignmentId);
    fd.append('reason', reason);

    fetch('ajax/admin.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                alert(d.message || 'Screenshot rejected');
                closeRejectBox();
                closeSSModal();
                loadPendingScreenshots();
            } else {
                alert('Error: ' + (d.message || 'Failed to reject'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-times mr-2"></i>Reject';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-times mr-2"></i>Reject';
        });
}

// ================= HELPER =================
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// INIT
loadPendingScreenshots();

// Keyboard shortcuts
document.addEventListener('keydown', function(event) {
    const ssModal = document.getElementById('ssModal');
    const fsModal = document.getElementById('fullscreenImageModal');
    
    if (event.key === 'Escape') {
        if (!fsModal.classList.contains('hidden')) {
            closeFullscreenImage();
        } else if (!ssModal.classList.contains('hidden')) {
            closeSSModal();
        }
    }
});
</script>