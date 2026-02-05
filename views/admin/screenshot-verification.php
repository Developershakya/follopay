<?php
// Safety check (extra, index.php already handles admin)
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#3b82f6">
    <?php include 'header.php'; ?>
    <script src="asserts/js/toast.js" defer></script>
    <style>
* {
  -webkit-user-select: none;
  -webkit-touch-callout: none;
}

html, body {
  width: 100%;
  height: 100%;
  margin: 0;
  -webkit-font-smoothing: antialiased;
  -webkit-text-size-adjust: 100%;
}

input, textarea, button {
  -webkit-user-select: text;
}

button {
  -webkit-appearance: none;
  appearance: none;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  padding: 0.75rem 1rem;
  font-size: 1rem;
  transition: all 0.2s ease;
  font-weight: 600;
}

button:active {
  transform: scale(0.98);
}

@media (max-width: 768px) {
  button, a {
    min-height: 44px;
    min-width: 44px;
  }
}

@supports (padding: max(0px)) {
  body {
    padding-left: max(0px, env(safe-area-inset-left));
    padding-right: max(0px, env(safe-area-inset-right));
    padding-top: max(0px, env(safe-area-inset-top));
    padding-bottom: max(0px, env(safe-area-inset-bottom));
  }
}

.webview-hidden {
  opacity: 0;
  pointer-events: none;
  visibility: hidden;
}

.webview-show {
  opacity: 1;
  pointer-events: auto;
  visibility: visible;
}

.webview-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  transition: opacity 0.25s ease;
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
  max-width: 420px;
  width: 90%;
  padding: 24px;
}

textarea {
  -webkit-appearance: none;
  appearance: none;
  width: 100%;
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 14px;
  resize: none;
  box-sizing: border-box;
  color: #1f2937;
  line-height: 1.5;
}

textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

    </style>

    <title>Pending Screenshot Verification</title>
</head>
<body class="bg-gray-100 min-h-screen p-6">
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
class="webview-overlay webview-hidden bg-black bg-opacity-95 flex items-center justify-center p-4">

  <button onclick="closeFullscreenImage()"
    style="position:fixed;top:16px;right:20px;color:white;font-size:30px;background:none;border:none;padding:8px;min-height:auto;min-width:auto">
    ×
  </button>

  <img id="fullscreenImage" style="max-width:95%;max-height:95%;border-radius:8px">
</div>

<!-- ================= FULLSCREEN MODAL ================= -->
<div id="ssModal"
class="webview-overlay webview-hidden bg-black bg-opacity-90 flex flex-col p-0">

  <button onclick="closeSSModal()"
    style="position:fixed;top:16px;right:20px;color:white;font-size:30px;z-index:100000;background:none;border:none;padding:8px;min-height:auto;min-width:auto">
    ×
  </button>

    <div class="flex flex-col h-full w-full">

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
                    <div style="flex:1;display:flex;align-items:center;justify-content:center">
                        <img id="ssImage"
                          style="max-width:95%;max-height:95%;border-radius:8px;cursor:pointer"
                          onclick="openFullscreenImage()"
                          title="Click to fullscreen">
                    </div>
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
            <div id="mobileCommentDrawer" style="position:fixed;bottom:0;left:0;right:0;background:#1f2937;border-top:2px solid #374151;padding:16px;border-radius:12px 12px 0 0;box-shadow:0 -4px 6px -1px rgba(0, 0, 0, 0.1);max-height:384px;overflow-y:auto;z-index:40;transform:translateY(100%);transition:transform 0.3s ease;display:md:none">
                <button onclick="toggleMobileDrawer()" style="position:absolute;top:8px;right:16px;color:white;font-size:24px;background:none;border:none;padding:0;min-height:auto;min-width:auto">−</button>
                <h3 style="font-weight:bold;color:white;font-size:18px;margin:0 0 12px 0">Details & Comment</h3>
                
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div>
                        <p style="font-size:12px;color:#9ca3af;margin:0 0 8px 0">Assigned Comment</p>
                        <div style="background:#111;padding:12px;border-radius:8px;border:1px solid #374151;max-height:160px;overflow-y:auto">
                            <p id="modalCommentMobile" style="color:white;font-size:12px;line-height:1.5;font-family:monospace;word-break:break-word;margin:0">
                                Loading...
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <p style="font-size:12px;color:#9ca3af;margin:0 0 4px 0">Submission Time</p>
                        <p id="modalSubmissionTimeMobile" style="color:white;font-size:14px;margin:0">-</p>
                    </div>
                    
                    <div>
                        <p style="font-size:12px;color:#9ca3af;margin:0 0 4px 0">Reward Amount</p>
                        <p id="modalPriceMobileDrawer" style="color:#4ade80;font-weight:bold;font-size:14px;margin:0">₹0</p>
                    </div>
                    
                    <div>
                        <p style="font-size:12px;color:#9ca3af;margin:0 0 4px 0">Assignment ID</p>
                        <p id="modalAssignmentIdMobileDrawer" style="color:#60a5fa;font-weight:bold;font-size:14px;margin:0">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Actions -->
        <div class="bg-gray-900 border-t border-gray-700 p-3 md:p-4 flex justify-center gap-2 md:gap-3 flex-wrap md:pb-4">
            <button onclick="downloadImage()"
                    style="background:#2563eb;color:white;padding:10px 20px;border-radius:6px;font-weight:600;transition:all 0.2s;display:flex;align-items:center;gap:8px;font-size:14px;min-height:44px">
                <i class="fas fa-download"></i><span class="hidden md:inline">Download</span>
            </button>

            <button onclick="approveSS()"
                    style="background:#16a34a;color:white;padding:10px 20px;border-radius:6px;font-weight:600;transition:all 0.2s;display:flex;align-items:center;gap:8px;font-size:14px;min-height:44px">
                <i class="fas fa-check"></i><span class="hidden md:inline">Approve</span>
            </button>

            <button onclick="openRejectBox()"
                    style="background:#dc2626;color:white;padding:10px 20px;border-radius:6px;font-weight:600;transition:all 0.2s;display:flex;align-items:center;gap:8px;font-size:14px;min-height:44px">
                <i class="fas fa-times"></i><span class="hidden md:inline">Reject</span>
            </button>

            <button onclick="toggleMobileDrawer()"
                    style="background:#4b5563;color:white;padding:10px 20px;border-radius:6px;font-weight:600;transition:all 0.2s;display:flex;align-items:center;gap:8px;font-size:14px;md:hidden;min-height:44px">
                <i class="fas fa-info-circle"></i>Details
            </button>
        </div>
    </div>
</div>

<!-- ================= REJECT MODAL ================= -->
<div id="rejectBox"
class="webview-overlay webview-hidden bg-black bg-opacity-60 flex items-center justify-center p-4">

  <div class="modal-content">
    <h3 style="font-size:18px;font-weight:bold;color:#1f2937;margin:0 0 16px 0">Rejection Reason</h3>
    
    <textarea id="rejectReason" 
              placeholder="Enter reason for rejection..." 
              style="height:120px;margin-bottom:20px"></textarea>

    <div style="display:flex;gap:12px;justify-content:flex-end">
      <button onclick="closeRejectBox()" style="padding:10px 20px;border:2px solid #d1d5db;background:white;color:#1f2937;border-radius:8px;font-weight:600;cursor:pointer;font-size:14px;transition:all 0.2s;min-height:auto;min-width:auto">
        Cancel
      </button>
      <button onclick="rejectSS()" style="padding:10px 20px;background:#dc2626;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:14px;transition:all 0.2s;min-height:auto;min-width:auto">
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
                    showToast('Error loading screenshots', 3000, 'error');
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
            const m = document.getElementById('ssModal');
            m.classList.remove('webview-hidden');
            m.classList.add('webview-show');

            document.documentElement.style.overflow = 'hidden';
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
            
            document.body.style.overflow = 'hidden';
            closeMobileDrawer();
        }

        function closeSSModal() {
            const m = document.getElementById('ssModal');
            m.classList.remove('webview-show');
            m.classList.add('webview-hidden');
            document.documentElement.style.overflow = 'auto';
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
            const f = document.getElementById('fullscreenImageModal');
            document.getElementById('fullscreenImage').src = currentImagePath;
            f.classList.remove('webview-hidden');
            f.classList.add('webview-show');
            document.body.style.overflow = 'hidden';
        }

        function closeFullscreenImage() {
            const f = document.getElementById('fullscreenImageModal');
            f.classList.remove('webview-show');
            f.classList.add('webview-hidden');
            document.body.style.overflow = 'auto';
        }

        // ================= DOWNLOAD =================
        function downloadImage() {
            if (!currentImagePath) {
                showToast('No image to download', 2500, 'error');
                return;
            }

            showToast('Downloading...', 5000, 'info');

            // Fetch image as blob to avoid redirect
            fetch(currentImagePath)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch image');
                    return response.blob();
                })
                .then(blob => {
                    // Create blob URL
                    const blobUrl = window.URL.createObjectURL(blob);
                    
                    // Create link and download
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = `screenshot-${currentAssignmentId}-${new Date().getTime()}.jpg`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Clean up blob URL
                    window.URL.revokeObjectURL(blobUrl);
                    
                    showToast('Image downloaded successfully', 2500, 'success');
                })
                .catch(err => {
                    console.error('Error downloading image:', err);
                    showToast('Failed to download image', 3000, 'error');
                });
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
                        showToast(d.message || 'Screenshot approved successfully', 2500, 'success');
                        closeSSModal();
                        loadPendingScreenshots();
                    } else {
                        showToast('Error: ' + (d.message || 'Failed to approve'), 3000, 'error');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showToast('An error occurred', 3000, 'error');
                });
        }

        // ================= REJECT =================
        function openRejectBox() {
            const r = document.getElementById('rejectBox');
            r.classList.remove('webview-hidden');
            r.classList.add('webview-show');
            document.getElementById('rejectReason').value = '';
            document.getElementById('rejectReason').focus();
        }

        function closeRejectBox() {
            const r = document.getElementById('rejectBox');
            r.classList.remove('webview-show');
            r.classList.add('webview-hidden');
            document.getElementById('rejectReason').value = '';
        }

        function rejectSS() {
            const reason = document.getElementById('rejectReason').value.trim();

            if (!reason) {
                showToast('Please enter rejection reason', 2500, 'warning');
                return;
            }

            const fd = new FormData();
            fd.append('action', 'reject_submission');
            fd.append('assignment_id', currentAssignmentId);
            fd.append('reason', reason);

            fetch('ajax/admin.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        showToast('Screenshot rejected', 2500, 'success');
                        closeRejectBox();
                        closeSSModal();
                        loadPendingScreenshots();
                    } else {
                        showToast('Error: ' + (d.message || 'Failed to reject'), 3000, 'error');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showToast('An error occurred', 3000, 'error');
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
            const rejectBox = document.getElementById('rejectBox');
            
            if (event.key === 'Escape') {
                if (!fsModal.classList.contains('webview-hidden')) {
                    closeFullscreenImage();
                } else if (!rejectBox.classList.contains('webview-hidden')) {
                    closeRejectBox();
                } else if (!ssModal.classList.contains('webview-hidden')) {
                    closeSSModal();
                }
            }
        });
</script>
</body>
</html>