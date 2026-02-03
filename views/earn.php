<?php
/**
 * FolloPay - Earn Money Page
 * SEO Optimized version with all existing functionality
 */
require_once 'config/constants.php';
require_once 'controllers/AuthController.php';

$auth = new AuthController();
if (!$auth->checkAuth()) {
    header('Location: ?page=login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earn Money From Reviews - FolloPay | Get Paid for App Reviews</title>
    <meta name="description" content="Earn genuine money by writing reviews on Google Play Store and app reviews. Get paid for authentic feedback on apps and services with FolloPay.">
    <meta name="keywords" content="earn money from reviews, app reviews payment, review rewards, get paid for app reviews, review earning jobs">
    <meta name="robots" content="index, follow">
    <meta name="author" content="FolloPay">
    
    <!-- Open Graph Tags for Social Sharing -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Earn Money From Reviews - FolloPay">
    <meta property="og:description" content="Get paid for writing authentic app reviews. Flexible earning opportunities for everyone.">
    <meta property="og:url" content="<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/?page=earn">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/?page=earn">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .drag-over {
            background-color: #dbeafe !important;
            border-color: #3b82f6 !important;
        }
        
        .phone-mockup {
            border: 12px solid #1f2937;
            border-radius: 40px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .phone-mockup::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40%;
            height: 25px;
            background: #1f2937;
            border-radius: 0 0 20px 20px;
            z-index: 10;
        }

        /* SEO Content - Hidden but indexed */
        .seo-content {
            font-size: 0;
            height: 0;
            overflow: hidden;
        }

        .seo-content * {
            font-size: inherit;
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- SEO Content Section (Hidden but indexed by Google) -->
<div class="seo-content">
    <h1>Earn Real Money Writing Reviews on FolloPay</h1>
    <p>Join thousands of users who are making genuine income by sharing their honest opinions about apps and services. Whether you're looking to earn some extra cash or build a consistent side income, FolloPay makes the process straightforward and rewarding.</p>
    
    <h2>How the Review Earning Process Works</h2>
    <p>The process is simple: Sign up and verify your account, get review tasks from your dashboard, write genuine reviews about apps and services, and get paid when your review is approved. No experience needed, no special skills required.</p>
    
    <h2>Types of Reviews You Can Write on FolloPay</h2>
    <p>You can write reviews for Google Play Store apps, Apple App Store apps, service and website reviews, and product and tool reviews. Each type offers different earning potential based on detail and quality.</p>
    
    <h2>What Makes a Great Review</h2>
    <p>FolloPay looks for complete honesty, reasonable detail and length (100-200+ words), proper grammar and spelling, specific examples and practical experience. Your authentic feedback and real experience is what makes reviews valuable.</p>
    
    <h2>Real Earnings on FolloPay</h2>
    <p>Casual users can earn 800-1500 per month with 2-3 hours per week. Regular users dedicating 1-2 hours daily typically earn 3500-5500 per month. Dedicated users working 2-3 hours daily can earn 7000-12000+ per month.</p>
    
    <h2>Is FolloPay Legitimate?</h2>
    <p>Yes, FolloPay is a trusted platform where you can earn real money by writing genuine reviews. We have transparent payment records and thousands of verified users making real money on our platform.</p>
</div>

<!-- Desktop Content -->
<div class="hidden md:block">
    <div class="max-w-5xl mx-auto py-8 px-4">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Earn Money</h1>
            <p class="text-gray-600 mb-8">Complete tasks and earn money by posting comments on apps</p>
            
            <div id="earnContainer">
                <div class="text-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
                    <p class="mt-4 text-gray-600">Loading earn page...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Content -->
<div class="md:hidden">
    <div class="p-4 pb-8">
        <h1 class="text-2xl font-bold mb-2 text-gray-800">Earn Money</h1>
        <p class="text-gray-600 mb-6">Complete tasks and earn money</p>
        
        <div id="mobileEarnContainer">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                <p class="mt-3 text-gray-600">Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
let timerInterval = null;
let heartbeatInterval = null;
let currentAssignmentId = null;
let isExpired = false;

document.addEventListener('DOMContentLoaded', function() {
    loadEarnPage();
});

function loadEarnPage() {
    fetch('ajax/posts.php?action=get_current_assignment')
        .then(response => response.json())
        .then(data => {
            const earnContainer = document.getElementById('earnContainer');
            const mobileEarnContainer = document.getElementById('mobileEarnContainer');
            
            if (data.success && data.assignment) {
                renderAssignment(data.assignment, earnContainer, mobileEarnContainer);
            } else {
                loadAvailablePosts();
            }
        })
        .catch(err => {
            console.error('Error:', err);
            const container = document.getElementById('earnContainer');
            if (container) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-exclamation-triangle text-5xl text-red-400 mb-3"></i>
                        <p class="text-red-600 font-medium text-lg">Error loading page</p>
                        <p class="text-gray-600 text-sm mt-2">Please refresh the page</p>
                    </div>
                `;
            }
        });
}

function renderAssignment(assignment, earnContainer, mobileEarnContainer) {
    currentAssignmentId = assignment.id;
    const timeLeft = Math.max(0, 300 - assignment.seconds_elapsed);
    
    // Desktop view
    if (earnContainer) {
        earnContainer.innerHTML = `
            <div class="space-y-8">
                <!-- Task Details -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-8">
                    <h2 class="text-2xl font-bold text-blue-800 mb-6">Current Task</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <div>
                                <p class="text-sm text-gray-600 font-semibold mb-2">App Name</p>
                                <p class="font-bold text-2xl text-gray-800">${escapeHtml(assignment.app_name || 'Unknown')}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600 font-semibold mb-2">App Link</p>
                                <a href="${assignment.app_link || '#'}" target="_blank" title="Open app on Google Play Store" class="inline-block">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" 
                                         alt="Google Play Store Badge" 
                                         class="h-10 hover:scale-110 transition-transform duration-200">
                                </a>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600 font-semibold mb-2">Reward Amount</p>
                                <p class="font-bold text-3xl text-green-600">₹${parseFloat(assignment.price).toFixed(2)}</p>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600 font-semibold mb-3">Assigned Comment</p>
                                <div class="bg-gray-900 text-white p-5 rounded-lg font-mono text-sm break-words shadow-md" id="desktopCommentBox">
                                    ${escapeHtml(assignment.comment_text || 'No comment')}
                                </div>
                                <button onclick="copyComment('${assignment.comment_text.replace(/'/g, "\\'")}')" 
                                        title="Copy comment to clipboard"
                                        class="w-full bg-blue-600 text-white py-3 rounded-lg mt-3 font-bold hover:bg-blue-700 transition-colors duration-200">
                                    <i class="fas fa-copy"></i> Copy Comment
                                </button>
                            </div>
                            
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <p class="text-sm text-gray-600 font-semibold mb-2">Time Remaining</p>
                                <p id="timer" class="font-bold text-3xl text-red-600" aria-label="Time remaining">05:00</p>
                            </div>
                            
                            <button onclick="refreshComment(${assignment.id}, ${assignment.post_id})" 
                                    id="refreshBtn" title="Refresh to get a new comment"
                                    class="w-full bg-yellow-500 text-white py-3 rounded-lg font-bold hover:bg-yellow-600 transition-colors duration-200 hidden">
                                <i class="fas fa-sync-alt"></i> Refresh Comment
                            </button>
                        </div>
                    </div>
                    
                    <div id="desktopMessage" class="mt-6 text-center hidden p-4 rounded-lg" role="status" aria-live="polite"></div>
                </div>
                
                <!-- Warning Box -->
                <div class="bg-yellow-50 border-2 border-yellow-400 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mt-1" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-yellow-800 text-lg mb-2">Important - Screenshot Requirements</h3>
                            <p class="text-sm text-yellow-700 mb-3">
                                Your screenshot must clearly show <span class="font-bold bg-yellow-200 px-2 py-1 rounded">both the App Name AND the Assigned Comment in the same frame</span>. 
                            </p>
                            <p class="text-xs text-yellow-600 bg-yellow-100 p-3 rounded">
                                <i class="fas fa-info-circle mr-2" aria-hidden="true"></i>
                                <strong>How to:</strong> Open the app → paste the comment → take screenshot showing app name in header and your comment posted below. Payment will be rejected if both are not visible.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- Sample Screenshot -->
                    <div class="bg-gradient-to-b from-blue-50 to-white border border-blue-200 rounded-xl p-6 md:p-8">
                        <h3 class="font-bold text-blue-800 text-lg mb-6 text-center md:text-left">
                            ✓ Sample Screenshot (What We're Looking For)
                        </h3>

                        <div class="flex justify-center">
                            <div class="phone-mockup w-64 sm:w-72 bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-300">

                                <!-- Portrait Image -->
                                <div class="bg-black">
                                    <img 
                                        src="${assignment.sample_image || 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1769955414/sample-portrait_xplmhh.png'}"
                                        alt="Sample screenshot showing app name and comment visible in the same frame"
                                        class="w-full aspect-[9/16] object-cover"
                                        loading="lazy"
                                    >
                                </div>

                                <!-- Footer -->
                                <div class="bg-gray-50 p-4">
                                    <p class="text-xs text-green-600 font-semibold text-center">
                                        <i class="fas fa-check-circle mr-1" aria-hidden="true"></i>
                                        Perfect! Both visible
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Upload Section -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-300 rounded-xl p-6 md:p-8">
                        <h3 class="font-bold text-green-800 text-lg mb-6 text-center md:text-left">
                            Upload Your Screenshot
                        </h3>

                        <div 
                            id="desktopDropZone"
                            class="border-2 border-dashed border-green-400 rounded-xl p-8 md:p-10 text-center cursor-pointer transition-all duration-300 hover:bg-white"
                            ondrop="handleDrop(event)" 
                            ondragover="handleDragOver(event)"
                            ondragleave="handleDragLeave(event)"
                            onclick="document.getElementById('desktopFileInput').click()"
                            role="button"
                            tabindex="0"
                            aria-label="Drop zone to upload screenshot"
                        >
                            <i class="fas fa-cloud-upload-alt text-5xl text-green-500 mb-4 block" aria-hidden="true"></i>
                            <p class="text-lg font-bold text-gray-800">
                                Drag and drop your screenshot here
                            </p>
                            <p class="text-sm text-gray-600 mt-2">
                                or click to select file
                            </p>

                            <input 
                                type="file" 
                                accept="image/*" 
                                class="hidden" 
                                id="desktopFileInput" 
                                onchange="handleFileSelect(event, '${assignment.id}')"
                                aria-label="Select screenshot file"
                            >
                        </div>

                        <div id="desktopMessage" class="mt-6 text-center hidden p-4 rounded-lg" role="status" aria-live="polite"></div>
                    </div>

                </div>

            </div>
        `;
        
        // Add click handler to drop zone
        document.getElementById('desktopDropZone').onclick = () => {
            document.getElementById('desktopFileInput').click();
        };
    }
    
    // Mobile view
    if (mobileEarnContainer) {
        mobileEarnContainer.innerHTML = `
            <div class="space-y-6">
                <!-- Task Details -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-5">
                    <h2 class="text-xl font-bold text-blue-800 mb-4">Current Task</h2>
                    
                    <div class="space-y-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">App Name</p>
                            <p class="font-bold text-lg text-gray-800">${escapeHtml(assignment.app_name || 'Unknown')}</p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">App Link</p>
                            <a href="${assignment.app_link || '#'}" target="_blank" title="Open app on Google Play Store" class="inline-block">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" 
                                     alt="Google Play Store Badge"
                                     class="h-8 hover:scale-105 transition-transform">
                            </a>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">Reward</p>
                            <p class="font-bold text-2xl text-green-600">₹${parseFloat(assignment.price).toFixed(2)}</p>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-600 font-semibold mb-2">Assigned Comment</p>
                        <div class="bg-gray-900 text-white p-3 rounded-lg font-mono text-xs break-words" id="mobileCommentBox">
                            ${escapeHtml(assignment.comment_text || 'No comment')}
                        </div>
                        <button onclick="copyComment('${assignment.comment_text.replace(/'/g, "\\'")}')" 
                                title="Copy comment to clipboard"
                                class="w-full bg-blue-600 text-white py-2 rounded-lg mt-3 font-bold text-sm hover:bg-blue-700 transition-colors">
                            <i class="fas fa-copy" aria-hidden="true"></i> Copy
                        </button>
                    </div>
                    
                    <div class="bg-white rounded-lg p-3 border border-gray-200 mt-4">
                        <p class="text-xs text-gray-600 font-semibold mb-1">Time Remaining</p>
                        <p id="mobileTimer" class="font-bold text-2xl text-red-600" aria-label="Time remaining">05:00</p>
                    </div>
                    
                    <button onclick="refreshComment(${assignment.id}, ${assignment.post_id})" 
                            id="mobileRefreshBtn" title="Refresh to get a new comment"
                            class="w-full bg-yellow-500 text-white py-2 rounded-lg mt-3 font-bold text-sm hover:bg-yellow-600 transition-colors hidden">
                        <i class="fas fa-sync-alt" aria-hidden="true"></i> Refresh
                    </button>
                    
                    <div id="mobileMessage" class="mt-4 text-center hidden p-3 rounded-lg text-sm" role="status" aria-live="polite"></div>
                </div>
                
                <!-- Warning Box -->
                <div class="bg-yellow-50 border-2 border-yellow-400 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-lg flex-shrink-0 mt-0.5" aria-hidden="true"></i>
                        <div>
                            <h3 class="font-bold text-yellow-800 text-sm mb-2">Screenshot Requirements</h3>
                            <p class="text-xs text-yellow-700 mb-2">
                                Screenshot must show <span class="font-bold bg-yellow-200 px-1 py-0.5 rounded">App Name AND Comment together</span>
                            </p>
                            <p class="text-xs text-yellow-600 bg-yellow-100 p-2 rounded">
                                <i class="fas fa-info-circle mr-1" aria-hidden="true"></i>Payment rejected if both not visible
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Sample Screenshot -->
                <div class="bg-gradient-to-b from-blue-50 to-white border border-blue-200 rounded-xl p-6 md:p-8">
                    <h3 class="font-bold text-blue-800 text-lg mb-6 text-center md:text-left">
                        ✓ Sample Screenshot (What We're Looking For)
                    </h3>

                    <div class="flex justify-center">
                        <div class="phone-mockup w-64 sm:w-72 bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-300">

                            <!-- Portrait Image -->
                            <div class="bg-black">
                                <img 
                                    src="${assignment.sample_image || 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1769955414/sample-portrait_xplmhh.png'}"
                                    alt="Sample screenshot showing app name and comment visible in the same frame"
                                    class="w-full aspect-[9/16] object-cover"
                                    loading="lazy"
                                >
                            </div>

                            <!-- Footer -->
                            <div class="bg-gray-50 p-4">
                                <p class="text-xs text-green-600 font-semibold text-center">
                                    <i class="fas fa-check-circle mr-1" aria-hidden="true"></i>
                                    Perfect! Both visible
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
                
                <!-- Upload Section -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-300 rounded-xl p-5">
                    <h3 class="font-bold text-green-800 text-sm mb-4">Upload Screenshot</h3>
                    
                    <div id="mobileDropZone" class="border-3 border-dashed border-green-400 rounded-xl p-6 text-center cursor-pointer transition-all"
                         ondrop="handleDrop(event)" 
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)"
                         role="button"
                         tabindex="0"
                         aria-label="Drop zone to upload screenshot">
                        <i class="fas fa-cloud-upload-alt text-4xl text-green-500 mb-3 block" aria-hidden="true"></i>
                        <p class="text-sm font-bold text-gray-800">Tap to select</p>
                        <p class="text-xs text-gray-600">or drag file here</p>
                        <input type="file" accept="image/*" class="hidden" id="mobileFileInput" onchange="handleFileSelect(event, '${assignment.id}')" aria-label="Select screenshot file">
                    </div>
                    
                    <div id="mobileMessage" class="mt-4 text-center hidden p-3 rounded-lg text-sm" role="status" aria-live="polite"></div>
                </div>
            </div>
        `;
        
        // Add click handler to drop zone
        document.getElementById('mobileDropZone').onclick = () => {
            document.getElementById('mobileFileInput').click();
        };
    }
    
    startTimer(timeLeft, '${assignment.id}');
    
    window.addEventListener('beforeunload', () => {
        releaseComment(assignment.id);
    });
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const dropZone = e.currentTarget;
    dropZone.classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        
        if (!file.type.startsWith('image/')) {
            alert('Please drop an image file');
            return;
        }
        
        uploadScreenshot(file);
    }
}

function handleFileSelect(e, assignmentId) {
    if (e.target.files.length > 0) {
        uploadScreenshot(e.target.files[0], assignmentId);
    }
}

function uploadScreenshot(file, assignmentId) {
    if (isExpired) {
        alert('Time is up! Please refresh to get a new comment.');
        return;
    }
    
    // Show upload dialog
    showUploadDialog();
    
    const formData = new FormData();
    formData.append('action', 'submit_screenshot');
    formData.append('assignment_id', currentAssignmentId);
    formData.append('screenshot', file);
    
    fetch('ajax/posts.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message in dialog
            updateUploadDialog(true, data.message || 'Upload successful! Please wait...');
            
            clearInterval(timerInterval);
            clearInterval(heartbeatInterval);
            
            // Redirect after 3 seconds
            setTimeout(() => {
                closeUploadDialog();
                window.location.href = '?page=dashboard';
            }, 3000);
        } else {
            // Show error message in dialog
            updateUploadDialog(false, data.message || 'Failed to submit');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        updateUploadDialog(false, 'An error occurred. Please try again.');
    });
}

function showUploadDialog() {
    // Create dialog container if it doesn't exist
    let dialogContainer = document.getElementById('uploadDialogContainer');
    if (!dialogContainer) {
        dialogContainer = document.createElement('div');
        dialogContainer.id = 'uploadDialogContainer';
        document.body.appendChild(dialogContainer);
    }
    
    dialogContainer.innerHTML = `
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl p-8 max-w-sm w-full text-center">
                <div class="mb-6">
                    <i class="fas fa-cloud-upload-alt text-5xl text-blue-500 mb-4 block animate-bounce" aria-hidden="true"></i>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Uploading Image</h2>
                    <p class="text-gray-600 text-sm">Please wait, your screenshot is being sent...</p>
                </div>
                <div class="flex justify-center mb-6">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                </div>
                <div id="dialogMessage" class="text-gray-600 text-sm font-semibold">
                    Uploading in progress...
                </div>
            </div>
        </div>
    `;
}

function updateUploadDialog(isSuccess, message) {
    const dialogContainer = document.getElementById('uploadDialogContainer');
    if (!dialogContainer) return;
    
    let icon, bgColor, textColor, title;
    
    if (isSuccess) {
        icon = 'fas fa-check-circle';
        bgColor = 'bg-green-100';
        textColor = 'text-green-700';
        title = 'Success!';
    } else {
        icon = 'fas fa-exclamation-circle';
        bgColor = 'bg-red-100';
        textColor = 'text-red-700';
        title = 'Failed!';
    }
    
    dialogContainer.innerHTML = `
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl p-8 max-w-sm w-full text-center">
                <div class="mb-6">
                    <div class="${bgColor} rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                        <i class="${icon} text-4xl ${textColor}" aria-hidden="true"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">${title}</h2>
                </div>
                <div id="dialogMessage" class="text-gray-700 text-sm mb-6 ${textColor} font-semibold">
                    ${message}
                </div>
                ${isSuccess ? `
                    <p class="text-gray-500 text-xs">Redirecting to dashboard in 3 seconds...</p>
                ` : `
                    <button onclick="closeUploadDialog()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                        Close
                    </button>
                `}
            </div>
        </div>
    `;
}

function closeUploadDialog() {
    const dialogContainer = document.getElementById('uploadDialogContainer');
    if (dialogContainer) {
        dialogContainer.remove();
    }
}

function startTimer(seconds, assignmentId) {
    let timeLeft = seconds;
    isExpired = false;
    
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const secs = timeLeft % 60;
        const timeString = `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        
        const timerElement = document.getElementById('timer');
        const mobileTimerElement = document.getElementById('mobileTimer');
        
        if (timerElement) timerElement.textContent = timeString;
        if (mobileTimerElement) mobileTimerElement.textContent = timeString;
        
        if (timeLeft <= 30) {
            if (timerElement) timerElement.classList.add('animate-pulse');
            if (mobileTimerElement) mobileTimerElement.classList.add('animate-pulse');
        }
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            clearInterval(heartbeatInterval);
            isExpired = true;
            
            const refreshBtn = document.getElementById('refreshBtn');
            const mobileRefreshBtn = document.getElementById('mobileRefreshBtn');
            
            if (refreshBtn) refreshBtn.classList.remove('hidden');
            if (mobileRefreshBtn) mobileRefreshBtn.classList.remove('hidden');
            
            const messageDiv = document.getElementById('desktopMessage') || document.getElementById('mobileMessage');
            if (messageDiv) {
                messageDiv.className = 'mt-6 text-center p-4 rounded-lg bg-red-100 text-red-700 font-semibold';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2" aria-hidden="true"></i> Time is up! Click "Refresh Comment" to get a new comment.';
                messageDiv.classList.remove('hidden');
            }
        } else {
            timeLeft--;
        }
    }
    
    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
    
    heartbeatInterval = setInterval(() => {
        if (!isExpired) {
            fetch('ajax/posts.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=heartbeat&assignment_id=${assignmentId}`
            }).catch(err => console.error('Heartbeat error:', err));
        }
    }, 30000);
}

function copyComment(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Comment copied to clipboard!');
    }).catch(err => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Comment copied to clipboard!');
    });
}

function refreshComment(assignmentId, postId) {
    if (confirm('Get a new comment? Your current comment will be released.')) {
        fetch('ajax/posts.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=refresh_comment&assignment_id=${assignmentId}&post_id=${postId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.assignment) {
                clearInterval(timerInterval);
                clearInterval(heartbeatInterval);
                loadEarnPage();
            } else {
                alert(data.message || 'Failed to refresh comment');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred. Please try again.');
        });
    }
}

function releaseComment(assignmentId) {
    if (assignmentId) {
        navigator.sendBeacon('ajax/posts.php', 
            `action=release_comment&assignment_id=${assignmentId}`
        );
    }
}

function getBadgeByLink(appLink) {
    if (!appLink) return {
        img: 'https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg',
        alt: 'Get it on Google Play'
    };

    appLink = appLink.toLowerCase();

    if (appLink.includes('youtube.com') || appLink.includes('youtu.be')) {
        return {
            img: 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770031900/Screenshot_2026-02-02_165110_zj0g1h.png',
            alt: 'Subscribe on YouTube'
        };
    }

    if (appLink.includes('maps') || appLink.includes('google.com/maps')) {
        return {
            img: 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770031901/Screenshot_2026-02-02_165134_s9eeag.png',
            alt: 'Rate us on Google Reviews'
        };
    }

    if (appLink.includes('playstore')) {
        return {
            img: 'https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg',
            alt: 'Get it on Google Play'
        };
    }

    // fallback
    return {
        img: 'https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg',
        alt: 'Open link'
    };
}

function loadAvailablePosts() {
    const earnContainer = document.getElementById('earnContainer');
    const mobileEarnContainer = document.getElementById('mobileEarnContainer');
    
    fetch('ajax/posts.php?action=get_available')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.posts && data.posts.length > 0) {
                const posts = data.posts;
                let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
                
                posts.forEach(post => {
                    const appName = post.app_name || 'Unknown App';
                    const appLink = post.app_link || '#';
                    const badge = getBadgeByLink(appLink);
                    const price = parseFloat(post.price) || 0;
                    const commentsAvailable = post.available_comments || 0;
                    const postId = post.id;
                    

                    html += `
                        <div class="bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden flex flex-col">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-5 border-b border-gray-200">
                                <h3 class="font-bold text-lg text-gray-800 mb-2">${escapeHtml(appName)}</h3>
                                    <a href="${appLink}" target="_blank" class="inline-block mb-3">
                                        <img src="${badge.img}"
                                            alt="${badge.alt}"
                                            class="h-10 hover:scale-110 transition-transform duration-200">
                                    </a>
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-green-600">₹${price.toFixed(2)}</span>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded text-xs font-semibold">Active</span>
                                </div>
                            </div>
                            <div class="p-5 flex-1">
                                <div class="space-y-3">
                                    <div class="flex items-center text-gray-700">
                                        <i class="fas fa-clock text-orange-500 mr-3" aria-hidden="true"></i>
                                        <span>Quick task • ~5 min</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-5 bg-gray-50 border-t border-gray-200">
                                <button onclick="startEarning(${postId})" 
                                        title="Start earning by completing this task"
                                        class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg">
                                    <i class="fas fa-play" aria-hidden="true"></i> Start Earning
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                if (earnContainer) earnContainer.innerHTML = html;
                if (mobileEarnContainer) mobileEarnContainer.innerHTML = html;
            } else {
                const noTasksHTML = `
                    <div class="text-center py-16">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4" aria-hidden="true"></i>
                        <p class="text-gray-600 text-xl font-medium">No available tasks at the moment</p>
                        <p class="text-gray-500 text-sm mt-2">Check back later for new earning opportunities</p>
                    </div>
                `;
                if (earnContainer) earnContainer.innerHTML = noTasksHTML;
                if (mobileEarnContainer) mobileEarnContainer.innerHTML = noTasksHTML;
            }
        })
        .catch(err => console.error('Error loading posts:', err));
}

function startEarning(postId) {
    if (!postId) {
        alert('Invalid task');
        return;
    }

    const buttons = document.querySelectorAll(`button[onclick="startEarning(${postId})"]`);
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2" aria-hidden="true"></i> Starting...';
    });

    fetch('ajax/posts.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=assign_comment&post_id=${postId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '?page=earn';
        } else {
            alert(data.message || 'Failed to start task. Please try again.');
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-play mr-2" aria-hidden="true"></i> Start Earning';
            });
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('An error occurred. Please try again.');
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play mr-2" aria-hidden="true"></i> Start Earning';
        });
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>