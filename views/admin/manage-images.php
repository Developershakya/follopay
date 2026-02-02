<?php
require_once 'config/constants.php';
require_once 'controllers/AuthController.php';

$auth = new AuthController();
if (!$auth->checkAuth()) {
    header('Location: ?page=login');
    exit;
}

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
    <title>Image Management - FolloPay Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
            padding: 20px;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content-wrapper {
            max-width: 1200px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            background: white;
            border-radius: 12px;
            position: relative;
        }
        
        .modal-image {
            max-width: 100%;
            max-height: 60vh;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }
        
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .image-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
            }
            
            .modal-content-wrapper {
                max-height: 95vh;
                margin: 10px;
            }
        }
        
        .image-card {
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .image-card.selected {
            border-color: #3b82f6;
            background-color: #dbeafe;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .btn-action {
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .comment-box {
            background: #f3f4f6;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            border-radius: 0.5rem;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body class="bg-gray-50">

<div class="min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <h1 class="text-xl md:text-2xl font-bold text-gray-800">
                    <i class="fas fa-images mr-2 text-blue-600"></i>
                    Screenshot Management
                </h1>
                <a href="?page=admin" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Admin
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6 md:py-8">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-6 md:mb-8" id="statsContainer">
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs md:text-sm text-gray-600">Total Screenshots</p>
                        <p class="text-2xl md:text-3xl font-bold text-gray-800" id="totalCount">0</p>
                    </div>
                    <i class="fas fa-images text-2xl md:text-4xl text-blue-500"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs md:text-sm text-gray-600">Pending</p>
                        <p class="text-2xl md:text-3xl font-bold text-yellow-600" id="pendingCount">0</p>
                    </div>
                    <i class="fas fa-clock text-2xl md:text-4xl text-yellow-500"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs md:text-sm text-gray-600">Approved</p>
                        <p class="text-2xl md:text-3xl font-bold text-green-600" id="approvedCount">0</p>
                    </div>
                    <i class="fas fa-check-circle text-2xl md:text-4xl text-green-500"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4 md:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs md:text-sm text-gray-600">Rejected</p>
                        <p class="text-2xl md:text-3xl font-bold text-red-600" id="rejectedCount">0</p>
                    </div>
                    <i class="fas fa-times-circle text-2xl md:text-4xl text-red-500"></i>
                </div>
            </div>
        </div>

        <!-- Filters & Actions -->
        <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6 md:mb-8">
            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4 mb-4">
                <select id="filterStatus" class="border rounded-lg px-3 md:px-4 py-2 text-sm md:text-base">
                    <option value="">All Status</option>
                    <option value="submitted">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                
                <input type="date" id="filterDateFrom" class="border rounded-lg px-3 md:px-4 py-2 text-sm md:text-base" placeholder="From Date">
                <input type="date" id="filterDateTo" class="border rounded-lg px-3 md:px-4 py-2 text-sm md:text-base" placeholder="To Date">
                <input type="text" id="filterUsername" class="border rounded-lg px-3 md:px-4 py-2 text-sm md:text-base" placeholder="Search username">
                <input type="text" id="filterAppName" class="border rounded-lg px-3 md:px-4 py-2 text-sm md:text-base" placeholder="Search app">
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-2 md:gap-3">
                <button onclick="applyFilters()" class="btn-action bg-blue-600 text-white px-4 md:px-6 py-2 rounded-lg hover:bg-blue-700 text-sm md:text-base">
                    <i class="fas fa-filter mr-1 md:mr-2"></i>Apply
                </button>
                <button onclick="resetFilters()" class="btn-action bg-gray-300 text-gray-700 px-4 md:px-6 py-2 rounded-lg hover:bg-gray-400 text-sm md:text-base">
                    <i class="fas fa-redo mr-1 md:mr-2"></i>Reset
                </button>
                <div class="flex-1 hidden md:block"></div>
                <button onclick="toggleSelectAll()" class="btn-action bg-purple-600 text-white px-4 md:px-6 py-2 rounded-lg hover:bg-purple-700 text-sm md:text-base">
                    <i class="fas fa-check-square mr-1 md:mr-2"></i><span class="hidden md:inline">Select All</span><span class="md:hidden">All</span>
                </button>
                <button onclick="downloadSelected()" class="btn-action bg-green-600 text-white px-4 md:px-6 py-2 rounded-lg hover:bg-green-700 text-sm md:text-base">
                    <i class="fas fa-download mr-1 md:mr-2"></i><span class="hidden md:inline">Download</span>
                </button>
                <button onclick="deleteSelected()" class="btn-action bg-red-600 text-white px-4 md:px-6 py-2 rounded-lg hover:bg-red-700 text-sm md:text-base">
                    <i class="fas fa-trash mr-1 md:mr-2"></i><span class="hidden md:inline">Delete</span>
                </button>
            </div>
            
            <!-- Selected Count -->
            <div id="selectedCount" class="mt-3 text-sm text-gray-600 hidden">
                <i class="fas fa-check-circle text-blue-600 mr-2"></i>
                <span id="selectedCountText">0 images selected</span>
            </div>
        </div>

        <!-- Image Grid -->
        <div id="imageGrid" class="image-grid">
            <!-- Images will be loaded here -->
        </div>
        
        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-3xl md:text-4xl text-gray-400"></i>
            <p class="mt-4 text-gray-600 text-sm md:text-base">Loading images...</p>
        </div>
        
        <!-- No Results -->
        <div id="noResults" class="text-center py-12 hidden">
            <i class="fas fa-inbox text-4xl md:text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-600 text-lg md:text-xl">No images found</p>
        </div>
    </div>
</div>

<!-- Enhanced Image Preview Modal -->
<div id="imageModal" class="modal">
    <div class="modal-content-wrapper">
        <!-- Close Button -->
        <button class="absolute top-4 right-4 bg-red-600 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-red-700 z-10" 
                onclick="closeModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- Modal Content -->
        <div class="p-6">
            <!-- Image -->
            <div class="mb-6">
                <img id="modalImage" class="modal-image rounded-lg shadow-lg" src="" alt="Screenshot Preview">
            </div>
            
            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column - Task Details -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 border-b pb-2">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Task Details
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">App Name</p>
                            <p class="text-sm font-bold text-gray-800" id="modalAppName">-</p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">Username</p>
                            <p class="text-sm text-gray-800" id="modalUsername">-</p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">Email</p>
                            <p class="text-sm text-gray-800" id="modalEmail">-</p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">Price</p>
                            <p class="text-lg font-bold text-green-600" id="modalPrice">-</p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">Status</p>
                            <span id="modalStatus" class="status-badge">-</span>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">Submitted Time</p>
                            <p class="text-sm text-gray-800" id="modalSubmittedTime">-</p>
                        </div>
                        
                        <div id="modalAppLinkDiv">
                            <p class="text-xs text-gray-600 font-semibold mb-1">App Link</p>
                            <a id="modalAppLink" href="#" target="_blank" class="text-sm text-blue-600 hover:underline break-all">-</a>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Comment -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 border-b pb-2">
                        <i class="fas fa-comment-alt text-blue-600 mr-2"></i>Assigned Comment
                    </h3>
                    
                    <div class="comment-box" id="modalComment">
                        Loading comment...
                    </div>
                    
                    <button onclick="copyModalComment()" class="w-full mt-4 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-copy mr-2"></i>Copy Comment
                    </button>
                    
                    <!-- Rejection Reason (if rejected) -->
                    <div id="modalRejectionDiv" class="mt-4 hidden">
                        <p class="text-xs text-gray-600 font-semibold mb-2">Rejection Reason</p>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <p class="text-sm text-red-800" id="modalRejectionReason">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let allImages = [];
let selectedImages = new Set();
let currentModalData = null;

// Load images on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, fetching images...');
    loadImages();
});

function loadImages() {
    const params = new URLSearchParams({
        action: 'get_all',
        status: document.getElementById('filterStatus').value,
        date_from: document.getElementById('filterDateFrom').value,
        date_to: document.getElementById('filterDateTo').value,
        username: document.getElementById('filterUsername').value,
        app_name: document.getElementById('filterAppName').value,
        limit: 100
    });
    
    console.log('Fetching with params:', params.toString());
    
    document.getElementById('loadingIndicator').classList.remove('hidden');
    document.getElementById('imageGrid').innerHTML = '';
    document.getElementById('noResults').classList.add('hidden');
    
    fetch(`ajax/images.php?${params}`)
        .then(response => response.json())
        .then(data => {
            console.log('Response data:', data);
            
            document.getElementById('loadingIndicator').classList.add('hidden');
            
            if (data.success) {
                allImages = data.images;
                console.log('Total images loaded:', allImages.length);
                
                // Update statistics
                if (data.stats) {
                    document.getElementById('totalCount').textContent = data.stats.total_screenshots || 0;
                    document.getElementById('pendingCount').textContent = data.stats.pending || 0;
                    document.getElementById('approvedCount').textContent = data.stats.approved || 0;
                    document.getElementById('rejectedCount').textContent = data.stats.rejected || 0;
                }
                
                // Render images
                if (allImages.length > 0) {
                    renderImages();
                } else {
                    document.getElementById('noResults').classList.remove('hidden');
                }
            } else {
                console.error('API error:', data.message);
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            document.getElementById('loadingIndicator').classList.add('hidden');
            alert('Failed to load images');
        });
}

function downloadImage(url) {
    fetch(url)
        .then(res => res.blob())
        .then(blob => {
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'screenshot.jpg'; // naam change kar sakte ho
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(a.href);
        })
        .catch(() => alert('Download failed'));
}

function renderImages() {
    const grid = document.getElementById('imageGrid');
    grid.innerHTML = '';
    
    allImages.forEach((image, index) => {
        const statusClass = 
            image.status === 'submitted' ? 'status-pending' :
            image.status === 'approved' ? 'status-approved' :
            'status-rejected';
        
        const statusText = 
            image.status === 'submitted' ? 'Pending' :
            image.status === 'approved' ? 'Approved' :
            'Rejected';
        
        const card = document.createElement('div');
        card.className = `image-card bg-white rounded-lg shadow-md overflow-hidden ${selectedImages.has(image.id) ? 'selected' : ''}`;
        card.innerHTML = `
            <div class="relative">
                <img src="${image.screenshot_path}" 
                     alt="Screenshot" 
                     class="w-full h-32 md:h-48 object-cover cursor-pointer hover:opacity-80"
                     onclick="openModal(${index})"
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EImage Error%3C/text%3E%3C/svg%3E';">
                <div class="absolute top-2 right-2">
                    <input type="checkbox" 
                           class="w-4 h-4 md:w-5 md:h-5 cursor-pointer"
                           ${selectedImages.has(image.id) ? 'checked' : ''}
                           onchange="toggleSelect(${image.id})">
                </div>
                <div class="absolute top-2 left-2">
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
            </div>
            <div class="p-3 md:p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="font-bold text-sm md:text-base text-gray-800 truncate">${escapeHtml(image.app_name)}</p>
                    <span class="text-green-600 font-bold text-sm md:text-base">₹${parseFloat(image.price).toFixed(2)}</span>
                </div>
                <p class="text-xs md:text-sm text-gray-600 mb-1">
                    <i class="fas fa-user mr-1"></i>${escapeHtml(image.username)}
                </p>
                <p class="text-xs text-gray-500 mb-2 md:mb-3">
                    <i class="fas fa-calendar mr-1"></i>${formatDate(image.submitted_time)}
                </p>
                ${image.status === 'rejected' && image.reject_reason ? `
                    <p class="text-xs text-red-600 bg-red-50 p-2 rounded mb-2">
                        <strong>Reason:</strong> ${escapeHtml(image.reject_reason)}
                    </p>
                ` : ''}
                <div class="flex gap-2">
                    <button 
                    onclick="downloadImage('${image.screenshot_path}')" 
                    class="flex-1 bg-blue-600 text-white text-xs md:text-sm py-2 px-2 md:px-3 rounded hover:bg-blue-700">
                    <i class="fas fa-download mr-1"></i>
                    <span class="hidden md:inline">Download</span>
                    </button>
                    <button onclick="deleteSingle(${image.id})" 
                            class="flex-1 bg-red-600 text-white text-xs md:text-sm py-2 px-2 md:px-3 rounded hover:bg-red-700">
                        <i class="fas fa-trash mr-1"></i><span class="hidden md:inline">Delete</span>
                    </button>
                </div>
            </div>
        `;
        
        grid.appendChild(card);
    });
    
    updateSelectedCount();
}

function toggleSelect(imageId) {
    if (selectedImages.has(imageId)) {
        selectedImages.delete(imageId);
    } else {
        selectedImages.add(imageId);
    }
    renderImages();
}

function toggleSelectAll() {
    if (selectedImages.size === allImages.length) {
        selectedImages.clear();
    } else {
        selectedImages = new Set(allImages.map(img => img.id));
    }
    renderImages();
}

function updateSelectedCount() {
    const countDiv = document.getElementById('selectedCount');
    const countText = document.getElementById('selectedCountText');
    
    if (selectedImages.size > 0) {
        countDiv.classList.remove('hidden');
        countText.textContent = `${selectedImages.size} image${selectedImages.size > 1 ? 's' : ''} selected`;
    } else {
        countDiv.classList.add('hidden');
    }
}

function applyFilters() {
    selectedImages.clear();
    loadImages();
}

function resetFilters() {
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterUsername').value = '';
    document.getElementById('filterAppName').value = '';
    selectedImages.clear();
    loadImages();
}

async function downloadSelected() {
    if (selectedImages.size === 0) {
        alert('Please select images to download');
        return;
    }

    const selectedData = allImages.filter(img => selectedImages.has(img.id));

    alert(`Downloading ${selectedData.length} images...`);

    for (const image of selectedData) {
        try {
            const res = await fetch(image.screenshot_path);
            const blob = await res.blob();

            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `screenshot_${image.username}_${image.id}.jpg`;

            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);

            // browser ko time dene ke liye
            await new Promise(r => setTimeout(r, 400));

        } catch (e) {
            console.error('Download failed:', image.screenshot_path);
        }
    }
}


function deleteSelected() {
    if (selectedImages.size === 0) {
        alert('Please select images to delete');
        return;
    }
    
    if (!confirm(`Are you sure you want to delete ${selectedImages.size} image(s)? This will also delete them from Cloudinary.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_bulk');
    formData.append('assignment_ids', JSON.stringify(Array.from(selectedImages)));
    
    fetch('ajax/images.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            selectedImages.clear();
            loadImages();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to delete images');
    });
}

function deleteSingle(imageId) {
    if (!confirm('Are you sure you want to delete this image? This will also delete it from Cloudinary.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_single');
    formData.append('assignment_id', imageId);
    
    fetch('ajax/images.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadImages();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to delete image');
    });
}

function openModal(imageIndex) {
    const image = allImages[imageIndex];
    currentModalData = image;
    
    // Set image
    document.getElementById('modalImage').src = image.screenshot_path;
    
    // Set details
    document.getElementById('modalAppName').textContent = image.app_name || '-';
    document.getElementById('modalUsername').textContent = image.username || '-';
    document.getElementById('modalEmail').textContent = image.email || '-';
    document.getElementById('modalPrice').textContent = '₹' + parseFloat(image.price).toFixed(2);
    document.getElementById('modalSubmittedTime').textContent = formatDate(image.submitted_time);
    document.getElementById('modalAppLink').href = image.app_link || '#';
    document.getElementById('modalAppLink').textContent = image.app_link || '-';
    
    // Set status badge
    const statusBadge = document.getElementById('modalStatus');
    const statusClass = 
        image.status === 'submitted' ? 'status-pending' :
        image.status === 'approved' ? 'status-approved' :
        'status-rejected';
    const statusText = 
        image.status === 'submitted' ? 'Pending' :
        image.status === 'approved' ? 'Approved' :
        'Rejected';
    
    statusBadge.className = 'status-badge ' + statusClass;
    statusBadge.textContent = statusText;
    
    // Set comment
    document.getElementById('modalComment').textContent = image.comment_text || 'No comment available';
    
    // Show/hide rejection reason
    if (image.status === 'rejected' && image.reject_reason) {
        document.getElementById('modalRejectionDiv').classList.remove('hidden');
        document.getElementById('modalRejectionReason').textContent = image.reject_reason;
    } else {
        document.getElementById('modalRejectionDiv').classList.add('hidden');
    }
    
    // Show modal
    document.getElementById('imageModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('imageModal').classList.remove('active');
    document.body.style.overflow = 'auto';
    currentModalData = null;
}

function copyModalComment() {
    const commentText = document.getElementById('modalComment').textContent;
    
    navigator.clipboard.writeText(commentText).then(() => {
        alert('Comment copied to clipboard!');
    }).catch(err => {
        // Fallback method
        const textarea = document.createElement('textarea');
        textarea.value = commentText;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Comment copied to clipboard!');
    });
}

// Close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('imageModal');
    if (event.target == modal) {
        closeModal();
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', {day: '2-digit', month: 'short', year: 'numeric'}) + ' ' + 
           date.toLocaleTimeString('en-IN', {hour: '2-digit', minute: '2-digit'});
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>