<?php
/**
 * FolloPay - Earn Money Page
 * Android WebView Optimized Version
 * Modified: Dynamic content based on link type (PlayStore, Google Maps, YouTube)
 * Fixed: Comment copying now preserves exact text including quotes
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#3b82f6">
    
    <title>Earn Money From Reviews - FolloPay | Get Paid for App Reviews</title>
    <meta name="description" content="Earn genuine money by writing reviews on Google Play Store and app reviews. Get paid for authentic feedback on apps and services with FolloPay.">
    <meta name="keywords" content="earn money from reviews, app reviews payment, review rewards, get paid for app reviews, review earning jobs">
    <meta name="robots" content="index, follow">
    <meta name="author" content="FolloPay">
    
    <meta property="og:type" content="website">
    <meta property="og:title" content="Earn Money From Reviews - FolloPay">
    <meta property="og:description" content="Get paid for writing authentic app reviews. Flexible earning opportunities for everyone.">
    <meta property="og:url" content="<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/?page=earn">
    
    <link rel="canonical" href="<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/?page=earn">
    
    <?php include 'header.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="asserts/js/toast.js"></script>
    <style>
        * {
            -webkit-user-select: none;
            -webkit-touch-callout: none;
            user-select: none;
        }
        
        input, textarea, button {
            -webkit-user-select: text;
            user-select: text;
        }
        
        html, body {
            height: 100%;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: 100%;
        }
        
        body {
            background-color: #f9fafb;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .drag-over {
            background-color: #dbeafe !important;
            border-color: #3b82f6 !important;
        }
        
        .phone-mockup {
            border: 8px solid #1f2937;
            border-radius: 30px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 280px;
        }
        
        .phone-mockup::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 35%;
            height: 20px;
            background: #1f2937;
            border-radius: 0 0 15px 15px;
            z-index: 10;
        }

        /* SEO Content */
        .seo-content {
            font-size: 0;
            height: 0;
            overflow: hidden;
        }

        .seo-content * {
            font-size: inherit;
        }

        /* WebView Optimizations */
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

        input[type="file"] {
            display: none;
        }

        .drop-zone {
            border: 2px dashed #86efac;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
            background-color: white;
        }

        .drop-zone.drag-over {
            background-color: #dcfce7;
            border-color: #16a34a;
        }

        /* Smooth animations for mobile */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Touch-friendly spacing */
        @media (max-width: 768px) {
            button, a {
                min-height: 44px;
                min-width: 44px;
            }

            .text-sm {
                font-size: 0.875rem;
            }

            .p-4 {
                padding: 1rem;
            }

            .p-5 {
                padding: 1.25rem;
            }
        }

        /* Loading animation */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Safe area insets for notch */
        @supports (padding: max(0px)) {
            body {
                padding-left: max(0px, env(safe-area-inset-left));
                padding-right: max(0px, env(safe-area-inset-right));
                padding-top: max(0px, env(safe-area-inset-top));
                padding-bottom: max(0px, env(safe-area-inset-bottom));
            }
        }

        /* Dialog overlay */
        .dialog-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            padding: 1rem;
        }

        .dialog-content {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        /* Text selection colors */
        ::selection {
            background-color: #3b82f6;
            color: white;
        }

        ::-webkit-selection {
            background-color: #3b82f6;
            color: white;
        }

        /* Link type indicator badge */
        .link-type-badge {
            display: inline-block;
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .badge-playstore {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .badge-youtube {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-maps {
            background-color: #dcfce7;
            color: #166534;
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- SEO Content -->
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

<!-- Main Container -->
<div class="min-h-screen bg-gray-50">
    <!-- Desktop View -->
    <div class="hidden md:block">
        <div class="max-w-6xl mx-auto py-8 px-4">
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h1 class="text-4xl font-bold mb-3 text-gray-800">Earn Money</h1>
                <p class="text-gray-600 mb-8 text-lg">Complete tasks and earn money by posting comments on apps</p>
                
                <div id="earnContainer" class="min-h-96">
                    <div class="flex flex-col items-center justify-center py-16">
                        <div class="spinner mb-4" style="width: 32px; height: 32px; border-width: 4px;"></div>
                        <p class="text-gray-600">Loading earn page...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile View -->
    <div class="md:hidden pb-20">
        <div class="bg-gray-50">
            <div class="sticky top-0 bg-white border-b border-gray-200 p-4 z-10">
                <h1 class="text-2xl font-bold text-gray-800">Earn Money</h1>
                <p class="text-sm text-gray-600">Complete tasks and earn</p>
            </div>
            
            <div id="mobileEarnContainer" class="p-4">
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="spinner mb-4" style="width: 28px; height: 28px; border-width: 3px;"></div>
                    <p class="text-gray-600 text-sm">Loading...</p>
                </div>
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
    
    // Prevent default context menu
    document.addEventListener('contextmenu', (e) => {
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });
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
            showError('Error loading page');
        });
}

function getTaskType(appLink) {
    if (!appLink) return 'playstore';
    const link = appLink.toLowerCase();
    
    // YouTube detection - supports multiple formats
    if (link.includes('youtube.com') || 
        link.includes('youtu.be') ||  // Short links like youtu.be/...
        link.includes('youtube.com/shorts') ||  // YouTube Shorts
        link.includes('youtube.com/watch') ||   // Regular videos
        link.includes('youtube.com/@')) {      // Channels
        return 'youtube';
    }
    
    // Google Maps detection
     if ( link.includes('google.com/maps') || link.includes('maps.google.')  || link.includes('maps.app') || link.includes('share.google/')) return 'maps';
    
    return 'playstore';
}

function renderAssignment(assignment, earnContainer, mobileEarnContainer) {
    currentAssignmentId = assignment.id;
    const timeLeft = Math.max(0, 300 - assignment.seconds_elapsed);
    const taskType = getTaskType(assignment.app_link);
    
    const desktopHTML = createDesktopAssignmentHTML(assignment, taskType);
    if (earnContainer) earnContainer.innerHTML = desktopHTML;
    
    const mobileHTML = createMobileAssignmentHTML(assignment, taskType);
    if (mobileEarnContainer) mobileEarnContainer.innerHTML = mobileHTML;
    
    // Attach drop zone handlers
    attachDropZoneHandlers('desktopDropZone', assignment.id);
    attachDropZoneHandlers('mobileDropZone', assignment.id);
    
    // Attach file input handlers
    const desktopFileInput = document.getElementById('desktopFileInput');
    const mobileFileInput = document.getElementById('mobileFileInput');
    if (desktopFileInput) desktopFileInput.onchange = (e) => handleFileSelect(e, assignment.id);
    if (mobileFileInput) mobileFileInput.onchange = (e) => handleFileSelect(e, assignment.id);
    
    startTimer(timeLeft, assignment.id);
    
    window.addEventListener('beforeunload', () => {
        releaseComment(assignment.id);
    });
}

function createDesktopAssignmentHTML(assignment, taskType) {
    const taskDetails = getTaskDetails(assignment, taskType);
    
    return `
        <div class="space-y-8">
            <!-- Task Details -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-8">
                <h2 class="text-2xl font-bold text-blue-800 mb-8">Current Task 
                    <span class="link-type-badge ${taskDetails.badgeClass}">${taskDetails.badgeText}</span>
                </h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <div>
                            <p class="text-sm text-gray-600 font-semibold mb-2">${taskDetails.nameLabel}</p>
                            <p class="font-bold text-2xl text-gray-800">${escapeHtml(taskDetails.displayName)}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600 font-semibold mb-2">${taskDetails.linkLabel}</p>
                            <a href="${taskDetails.displayLink}" target="_blank" rel="noopener noreferrer" class="inline-block">
                                <img src="${taskDetails.badgeImg}" 
                                     alt="${taskDetails.badgeAlt}"
                                     class="h-10 hover:scale-110 transition-transform duration-200" loading="lazy">
                            </a>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600 font-semibold mb-2">Reward Amount</p>
                            <p class="font-bold text-3xl text-green-600">₹${parseFloat(assignment.price).toFixed(2)}</p>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="space-y-4">
                        ${
                            taskType !== 'youtube' 
                            ? `
                            <div>
                                <p class="text-sm text-gray-600 font-semibold mb-3">Assigned Comment</p>
                                <div class="bg-gray-900 text-white p-5 rounded-lg font-mono text-sm break-words shadow-md max-h-40 overflow-y-auto">
                                    ${escapeHtml(assignment.comment_text || 'No comment')}
                                </div>
                                <button
                                  onclick="copyComment(this)"
                                  data-comment='${assignment.comment_text || ''}'
                                  class="w-full bg-blue-600 text-white py-3 rounded-lg mt-3 font-bold hover:bg-blue-700 active:bg-blue-800">
                                  <i class="fas fa-copy mr-2"></i> Copy Comment
                                </button>
                            </div>
                            `
                            : ''
                        }
                        
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <p class="text-sm text-gray-600 font-semibold mb-2">Time Remaining</p>
                            <p id="timer" class="font-bold text-3xl text-red-600">05:00</p>
                        </div>
                        
                        <button onclick="refreshComment(${assignment.id}, ${assignment.post_id})" id="refreshBtn" class="w-full bg-yellow-500 text-white py-3 rounded-lg font-bold hover:bg-yellow-600 active:bg-yellow-700 transition-colors hidden">
                            <i class="fas fa-sync-alt mr-2"></i> Refresh Comment
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Warning Box -->
            ${getWarningBoxHTML(taskType)}
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Sample Screenshot -->
                <div class="bg-gradient-to-b from-blue-50 to-white border border-blue-200 rounded-xl p-8 flex flex-col items-center">
                    <h3 class="font-bold text-blue-800 text-lg mb-6">✓ Sample Screenshot</h3>
                    <div class="phone-mockup">
                        <div class="bg-black">
                            <img src="${taskDetails.sampleImage}"
                                 alt="Sample screenshot" class="w-full aspect-[9/16] object-contain" loading="lazy">
                        </div>
                        <div class="bg-gray-50 p-3">
                            <p class="text-xs text-green-600 font-semibold text-center">
                                <i class="fas fa-check-circle mr-1"></i>Perfect! Both visible
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Upload Section -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-300 rounded-xl p-8">
                    <h3 class="font-bold text-green-800 text-lg mb-6">Upload Your Screenshot</h3>
                    
                    <div id="desktopDropZone" class="drop-zone border-green-400 bg-white hover:bg-green-50 transition-colors">
                        <div class="mb-4">
                            <i class="fas fa-cloud-upload-alt text-6xl text-green-500 mb-4 block animate-bounce"></i>
                        </div>
                        <p class="text-lg font-bold text-gray-800 mb-2">Drag and Drop Your Screenshot Here</p>
                        <p class="text-sm text-gray-600 mb-4">or click below to select from your device</p>
                        <p class="text-xs text-gray-500 mt-3">JPG, PNG, GIF, WebP (Max 5MB)</p>
                        <input type="file" id="desktopFileInput" accept="image/*">
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createMobileAssignmentHTML(assignment, taskType) {
    const taskDetails = getTaskDetails(assignment, taskType);
    
    return `
        <div class="space-y-4">
            <!-- Task Details -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                <h2 class="text-lg font-bold text-blue-800 mb-4">Current Task 
                    <span class="link-type-badge ${taskDetails.badgeClass}">${taskDetails.badgeText}</span>
                </h2>
                
                <div class="space-y-3 mb-4">
                    <div>
                        <p class="text-xs text-gray-600 font-semibold mb-1">${taskDetails.nameLabel}</p>
                        <p class="font-bold text-lg text-gray-800">${escapeHtml(taskDetails.displayName)}</p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-600 font-semibold mb-1">${taskDetails.linkLabel}</p>
                        <a href="${taskDetails.displayLink}" target="_blank" rel="noopener noreferrer" class="inline-block">
                            <img src="${taskDetails.badgeImg}" 
                                 alt="${taskDetails.badgeAlt}"
                                 class="h-8 hover:scale-110 transition-transform" loading="lazy">
                        </a>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-600 font-semibold mb-1">Reward</p>
                        <p class="font-bold text-xl text-green-600">₹${parseFloat(assignment.price).toFixed(2)}</p>
                    </div>
                </div>
                
                        ${
                            taskType !== 'youtube' 
                            ? `
                            <div>
                                <p class="text-sm text-gray-600 font-semibold mb-3">Assigned Comment</p>
                                <div class="bg-gray-900 text-white p-5 rounded-lg font-mono text-sm break-words shadow-md max-h-40 overflow-y-auto">
                                    ${escapeHtml(assignment.comment_text || 'No comment')}
                                </div>
                                <button
                                  onclick="copyComment(this)"
                                  data-comment='${assignment.comment_text || ''}'
                                  class="w-full bg-blue-600 text-white py-3 rounded-lg mt-3 font-bold hover:bg-blue-700 active:bg-blue-800">
                                  <i class="fas fa-copy mr-2"></i> Copy Comment
                                </button>
                            </div>
                            `
                            : ''
                        }
                
                <div class="bg-white rounded-lg p-3 border border-gray-200 mb-4">
                    <p class="text-xs text-gray-600 font-semibold mb-1">Time Remaining</p>
                    <p id="mobileTimer" class="font-bold text-2xl text-red-600">05:00</p>
                </div>
                
                <button onclick="refreshComment(${assignment.id}, ${assignment.post_id})" id="mobileRefreshBtn" class="w-full bg-yellow-500 text-white py-2 rounded-lg font-bold text-sm hover:bg-yellow-600 active:bg-yellow-700 hidden">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
            </div>
            
            <!-- Warning Box -->
            ${getWarningBoxHTMLMobile(taskType)}
            
            <!-- Sample Screenshot -->
            <div class="bg-gradient-to-b from-blue-50 to-white border border-blue-200 rounded-lg p-4">
                <h3 class="font-bold text-blue-800 text-sm mb-4 text-center">✓ Sample Screenshot</h3>
                <div class="flex justify-center">
                    <div class="phone-mockup">
                        <div class="bg-black">
                            <img src="${taskDetails.sampleImage}"
                                 alt="Sample screenshot" class="w-full aspect-[9/16] object-contain" loading="lazy">
                        </div>
                        <div class="bg-gray-50 p-2">
                            <p class="text-xs text-green-600 font-semibold text-center">
                                <i class="fas fa-check-circle mr-1"></i>Perfect
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Upload Section -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-300 rounded-lg p-4">
                <h3 class="font-bold text-green-800 text-sm mb-4">Upload Your Screenshot</h3>
                
                <div id="mobileDropZone" class="drop-zone border-green-400 bg-white hover:bg-green-50 transition-colors">
                    <div class="mb-3">
                        <i class="fas fa-cloud-upload-alt text-5xl text-green-500 mb-3 block animate-bounce"></i>
                    </div>
                    <p class="text-base font-bold text-gray-800 mb-2">Tap to Upload</p>
                    <p class="text-xs text-gray-600 mb-3">or drag your screenshot here</p>
                    <p class="text-xs text-gray-500 mt-3">JPG, PNG, GIF, WebP (Max 5MB)</p>
                    <input type="file" id="mobileFileInput" accept="image/*">
                </div>
            </div>
        </div>
    `;
}

function getTaskDetails(assignment, taskType) {
    let details = {
        nameLabel: 'App Name',
        linkLabel: 'App Link 👇👇👇',
        displayName: assignment.app_name || 'Unknown',
        displayLink: assignment.app_link || '#',
        badgeText: 'PlayStore',
        badgeClass: 'badge-playstore',
        badgeImg: 'https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg',
        badgeAlt: 'Get it on Google Play',
        sampleImage: 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770825741/playstore_ust6hw.png' // Default PlayStore
    };

    if (taskType === 'youtube') {
        details.nameLabel = 'Channel Name';
        details.linkLabel = 'Channel Link 👇👇👇';
        details.displayName = extractYoutubeChannelName(assignment.app_name) || 'YouTube Channel';
        details.badgeText = 'YouTube';
        details.badgeClass = 'badge-youtube';
        details.badgeImg = 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770031900/Screenshot_2026-02-02_165110_zj0g1h.png';
        details.badgeAlt = 'Subscribe on YouTube';
        // YouTube Sample Image
        details.sampleImage = 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770825742/youtube_gqbsn0.png';
    } else if (taskType === 'maps') {
        details.nameLabel = 'Location Name';
        details.linkLabel = 'Location Link 👇👇👇';
        details.displayName = extractLocationName(assignment.app_name) || 'Location';
        details.badgeText = 'Google Maps';
        details.badgeClass = 'badge-maps';
        details.badgeImg = 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770031901/Screenshot_2026-02-02_165134_s9eeag.png';
        details.badgeAlt = 'Rate us on Google Maps';
        // Google Maps Sample Image
        details.sampleImage = 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770825744/googlereview_g1ad0m.png';
    }

    return details;
}

function getWarningBoxHTML(taskType) {
    if (taskType === 'youtube') {
        return `
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-xl p-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="font-bold text-yellow-800 mb-2">YouTube Requirements</h3>
                        <p class="text-sm text-yellow-700 mb-2">
                            Screenshot must show <span class="font-bold bg-yellow-200 px-2 py-1 rounded">Channel Name, Comment & Subscribe Button</span>
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-3 rounded">
                            <i class="fas fa-info-circle mr-2"></i>Channel must be subscribed
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-3 rounded mt-2">
                            <i class="fas fa-info-circle mr-2"></i>Comment list not required
                        </p>
                    </div>
                </div>
            </div>
        `;
    } else if (taskType === 'maps') {
        return `
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-xl p-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="font-bold text-yellow-800 mb-2">Google Maps Review Requirements</h3>
                        <p class="text-sm text-yellow-700 mb-2">
                            Screenshot must show <span class="font-bold bg-yellow-200 px-2 py-1 rounded">Location Name, Review & Published Status</span>
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-3 rounded">
                            <i class="fas fa-info-circle mr-2"></i>Review must be published and live
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-3 rounded mt-2">
                            <i class="fas fa-info-circle mr-2"></i>Your name/profile should be visible
                        </p>
                    </div>
                </div>
            </div>
        `;
    } else {
        return `
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-xl p-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="font-bold text-yellow-800 mb-2">Screenshot Requirements</h3>
                        <p class="text-sm text-yellow-700 mb-2">
                            Screenshot must show <span class="font-bold bg-yellow-200 px-2 py-1 rounded">App Name AND Comment together</span>
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-3 rounded">
                            <i class="fas fa-info-circle mr-2"></i>Payment rejected if both not visible
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-3 rounded mt-2">
                            <i class="fas fa-info-circle mr-2"></i>App Must be Downloaded then open then go to playstore then review and delete after 7 days
                        </p>
                    </div>
                </div>
            </div>
        `;
    }
}

function getWarningBoxHTMLMobile(taskType) {
    if (taskType === 'youtube') {
        return `
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4">
                <div class="flex items-start gap-2">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-base flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="font-bold text-yellow-800 text-sm mb-1">YouTube Requirements</h3>
                        <p class="text-xs text-yellow-700 mb-1">
                            Show <span class="font-bold bg-yellow-200 px-1 py-0.5 rounded">Channel Name, Comment & Subscribe</span>
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-2 rounded">
                            <i class="fas fa-info-circle mr-1"></i>Must be subscribed
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-2 rounded mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Comment list not needed
                        </p>
                    </div>
                </div>
            </div>
        `;
    } else if (taskType === 'maps') {
        return `
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4">
                <div class="flex items-start gap-2">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-base flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="font-bold text-yellow-800 text-sm mb-1">Google Maps Review</h3>
                        <p class="text-xs text-yellow-700 mb-1">
                            Show <span class="font-bold bg-yellow-200 px-1 py-0.5 rounded">Location, Review & Published</span>
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-2 rounded">
                            <i class="fas fa-info-circle mr-1"></i>Review must be live
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-2 rounded mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Your profile visible
                        </p>
                    </div>
                </div>
            </div>
        `;
    } else {
        return `
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4">
                <div class="flex items-start gap-2">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-base flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="font-bold text-yellow-800 text-sm mb-1">Screenshot Requirements</h3>
                        <p class="text-xs text-yellow-700 mb-1">
                            Show <span class="font-bold bg-yellow-200 px-1 py-0.5 rounded">App Name & Comment</span> together
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-2 rounded">
                            <i class="fas fa-info-circle mr-1"></i>Payment rejected if not both visible
                        </p>
                        <p class="text-xs text-yellow-600 bg-yellow-100 p-2 rounded mt-1">
                            <i class="fas fa-info-circle mr-1"></i>App Must be Downloaded then reviewed
                        </p>
                    </div>
                </div>
            </div>
        `;
    }
}

function extractYoutubeChannelName(text) {
    // Extract channel name from app_name field if it contains one
    // This can be customized based on your data format
    return text || 'YouTube Channel';
}

function extractLocationName(text) {
    // Extract location name from app_name field
    // This can be customized based on your data format
    return text || 'Location';
}

function attachDropZoneHandlers(elementId, assignmentId) {
    const zone = document.getElementById(elementId);
    if (!zone) return;
    
    zone.addEventListener('dragover', handleDragOver);
    zone.addEventListener('dragleave', handleDragLeave);
    zone.addEventListener('drop', handleDrop);
    zone.addEventListener('click', () => {
        const fileInput = document.getElementById(elementId === 'desktopDropZone' ? 'desktopFileInput' : 'mobileFileInput');
        if (fileInput) fileInput.click();
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
    e.currentTarget.classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    if (files.length > 0 && files[0].type.startsWith('image/')) {
        uploadScreenshot(files[0]);
    }
}

function handleFileSelect(e, assignmentId) {
    if (e.target.files.length > 0) {
        uploadScreenshot(e.target.files[0]);
    }
}

function uploadScreenshot(file) {
    if (isExpired) {
        showToast('Time is up! Please refresh to get a new comment.', 3000, 'warning');
        return;
    }
    
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
            updateUploadDialog(true, data.message || 'Upload successful!');
            clearInterval(timerInterval);
            clearInterval(heartbeatInterval);
            setTimeout(() => {
                closeUploadDialog();
                window.location.href = '?page=dashboard';
            }, 2000);
        } else {
            updateUploadDialog(false, data.message || 'Upload failed');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        updateUploadDialog(false, 'An error occurred');
    });
}

function showUploadDialog() {
    const container = document.createElement('div');
    container.id = 'uploadDialog';
    container.className = 'dialog-overlay';
    container.innerHTML = `
        <div class="dialog-content">
            <div class="text-center">
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
    document.body.appendChild(container);
}

function updateUploadDialog(success, message) {
    const dialog = document.getElementById('uploadDialog');
    if (!dialog) return;
    
    const icon = success ? 'fas fa-check-circle text-green-600' : 'fas fa-exclamation-circle text-red-600';
    const title = success ? 'Success!' : 'Failed!';
    
    dialog.innerHTML = `
        <div class="dialog-content">
            <div class="text-center">
                <i class="${icon} text-4xl mb-4 block"></i>
                <h2 class="text-xl font-bold text-gray-800 mb-2">${title}</h2>
                <p class="text-sm text-gray-600 mb-4">${message}</p>
                ${success ? '' : `<button onclick="closeUploadDialog()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700">Close</button>`}
            </div>
        </div>
    `;
    
    if (success) {
        setTimeout(closeUploadDialog, 2000);
    }
}

function closeUploadDialog() {
    const dialog = document.getElementById('uploadDialog');
    if (dialog) dialog.remove();
}

function startTimer(seconds, assignmentId) {
    let timeLeft = seconds;
    isExpired = false;
    
    const updateTimer = () => {
        const mins = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const secs = (timeLeft % 60).toString().padStart(2, '0');
        const timeStr = `${mins}:${secs}`;
        
        const timer = document.getElementById('timer');
        const mobileTimer = document.getElementById('mobileTimer');
        if (timer) timer.textContent = timeStr;
        if (mobileTimer) mobileTimer.textContent = timeStr;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            clearInterval(heartbeatInterval);
            isExpired = true;
            
            const refreshBtn = document.getElementById('refreshBtn');
            const mobileRefreshBtn = document.getElementById('mobileRefreshBtn');
            if (refreshBtn) refreshBtn.classList.remove('hidden');
            if (mobileRefreshBtn) mobileRefreshBtn.classList.remove('hidden');
        } else {
            timeLeft--;
        }
    };
    
    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
    
    heartbeatInterval = setInterval(() => {
        if (!isExpired) {
            fetch('ajax/posts.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=heartbeat&assignment_id=${assignmentId}`
            }).catch(() => {});
        }
    }, 30000);
}

function copyComment(btn) {
    // Get comment text directly from the data-comment attribute
    const text = btn.getAttribute('data-comment');

    if (!text) {
        showToast('Nothing to copy', 2000, 'error');
        return;
    }

    // Use clipboard API if available
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text)
            .then(() => {
                showToast('Comment copied!', 2000, 'success');
                // Visual feedback
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check mr-2"></i> Copied!';
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                }, 2000);
            })
            .catch(() => fallbackCopy(text, btn));
    } else {
        fallbackCopy(text, btn);
    }
}

function fallbackCopy(text, btn) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.top = '-1000px';
    textarea.style.opacity = '0';

    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    try {
        document.execCommand('copy');
        showToast('Comment copied!', 2000, 'success');
        
        // Visual feedback
        if (btn) {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check mr-2"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 2000);
        }
    } catch (e) {
        showToast('Copy not supported', 3000, 'error');
    }

    document.body.removeChild(textarea);
}

function showConfirmDialog(title, message, onConfirm, onCancel) {
    let dialog = document.getElementById('confirmDialog');
    if (dialog) dialog.remove();

    dialog = document.createElement('div');
    dialog.id = 'confirmDialog';
    document.body.appendChild(dialog);

    dialog.style.cssText = `
        position:fixed;
        top:0;
        left:0;
        right:0;
        bottom:0;
        background:rgba(0,0,0,0.5);
        display:flex;
        align-items:center;
        justify-content:center;
        z-index:999998;
        padding:16px;
    `;

    dialog.innerHTML = `
        <div style="background:white;border-radius:12px;padding:24px;max-width:320px;width:100%;box-shadow:0 20px 25px -5px rgba(0, 0, 0, 0.3)">
            <h3 style="margin:0 0 8px 0;font-size:18px;font-weight:bold;color:#1f2937">${title}</h3>
            <p style="margin:0 0 24px 0;font-size:14px;color:#4b5563;line-height:1.5">${message}</p>
            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button id="confirmCancel"
                    style="padding:10px 20px;border:2px solid #d1d5db;background:white;color:#1f2937;border-radius:8px;font-weight:600;cursor:pointer;font-size:14px">
                    Cancel
                </button>
                <button id="confirmOk"
                    style="padding:10px 20px;background:#3b82f6;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:14px">
                    Confirm
                </button>
            </div>
        </div>
    `;

    dialog.querySelector('#confirmOk').addEventListener('click', () => {
        dialog.remove();
        if (typeof onConfirm === 'function') onConfirm();
    });

    dialog.querySelector('#confirmCancel').addEventListener('click', () => {
        dialog.remove();
        if (typeof onCancel === 'function') onCancel();
    });
}

function refreshComment(assignmentId, postId) {
    showConfirmDialog(
        'Get New Comment?',
        'Your current comment will be released and you\'ll get a new one.',
        function() {
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
                    showToast('Comment refreshed', 2000, 'success');
                    loadEarnPage();
                } else {
                    showToast(data.message || 'Failed to refresh comment', 3000, 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showToast('An error occurred', 3000, 'error');
            });
        }
    );
}

function releaseComment(assignmentId) {
    if (assignmentId) {
        navigator.sendBeacon('ajax/posts.php', `action=release_comment&assignment_id=${assignmentId}`);
    }
}

function getBadgeByLink(appLink) {
    if (!appLink) return { img: 'https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg', alt: 'Get it on Google Play' };
    
    appLink = appLink.toLowerCase();
    
    if (appLink.includes('youtube') || appLink.includes('youtu.be') || appLink.includes('youtube.com/channel') || appLink.includes('youtube.com/user')) {
        return { img: 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770031900/Screenshot_2026-02-02_165110_zj0g1h.png', alt: 'Subscribe on YouTube' };
    }
    if (appLink.includes('maps') || appLink.includes('google.com/maps')) {
        return { img: 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770031901/Screenshot_2026-02-02_165134_s9eeag.png', alt: 'Rate us on Google Maps' };
    }
    
    return { img: 'https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg', alt: 'Get it on Google Play' };
}

function loadAvailablePosts() {
    const earnContainer = document.getElementById('earnContainer');
    const mobileEarnContainer = document.getElementById('mobileEarnContainer');
    
    fetch('ajax/posts.php?action=get_available')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.posts?.length > 0) {
                let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
                
                data.posts.forEach(post => {
                    const appName = post.app_name || 'Unknown';
                    const appLink = post.app_link || '#';
                    const price = parseFloat(post.price) || 0;
                    const taskType = getTaskType(appLink);
                    
                    let displayName = appName;
                    let badgeClass = 'badge-playstore';
                    
                    if (taskType === 'youtube') {
                        displayName = extractYoutubeChannelName(appName);
                        badgeClass = 'badge-youtube';
                    } else if (taskType === 'maps') {
                        displayName = extractLocationName(appName);
                        badgeClass = 'badge-maps';
                    }
                    
                    html += `
                        <div class="bg-white border border-gray-200 rounded-lg shadow hover:shadow-lg transition-shadow p-5">
                            <h3 class="font-bold text-lg text-gray-800 mb-2 truncate">${escapeHtml(displayName)} 
                                <span class="link-type-badge ${badgeClass}" style="font-size: 0.65rem; padding: 0.2rem 0.5rem;">${taskType === 'youtube' ? 'YouTube' : taskType === 'maps' ? 'Maps' : 'PlayStore'}</span>
                            </h3>
                            <a href="${appLink}" target="_blank" rel="noopener noreferrer" class="inline-block mb-3">
                                <img src="${getBadgeByLink(appLink).img}" alt="Store link" class="h-8 hover:scale-110 transition-transform" loading="lazy">
                            </a>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-green-600">₹${price.toFixed(2)}</span>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Active</span>
                            </div>
                            <button onclick="startEarning(${post.id})" class="w-full bg-green-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-600 active:bg-green-700 transition-colors text-sm">
                                <i class="fas fa-play mr-1"></i> Start
                            </button>
                        </div>
                    `;
                });
                
                html += '</div>';
                if (earnContainer) earnContainer.innerHTML = html;
                if (mobileEarnContainer) mobileEarnContainer.innerHTML = html;
            } else {
                const noTasksHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                        <p class="text-gray-600 font-medium">No tasks available</p>
                        <p class="text-gray-500 text-sm mt-1">Check back later</p>
                    </div>
                `;
                if (earnContainer) earnContainer.innerHTML = noTasksHTML;
                if (mobileEarnContainer) mobileEarnContainer.innerHTML = noTasksHTML;
            }
        })
        .catch(err => {
            console.error('Error:', err);
            showError('Failed to load tasks');
        });
}

function startEarning(postId) {
    if (!postId) return showToast('Invalid task', 2000, 'error');
    
    const buttons = document.querySelectorAll(`button[onclick*="startEarning(${postId})"]`);
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Starting...';
    });

    fetch('ajax/posts.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=assign_comment&post_id=${postId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '?page=earn';
        } else {
            showToast(data.message || 'Failed to start', 3000, 'error');
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-play mr-1"></i> Start';
            });
        }
    })
    .catch(() => {
        showToast('An error occurred', 3000, 'error');
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play mr-1"></i> Start';
        });
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeForJS(text) {
    return text.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n');
}

function showError(message) {
    const container = document.getElementById('earnContainer') || document.getElementById('mobileEarnContainer');
    if (container) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-exclamation-triangle text-5xl text-red-400 mb-3"></i>
                <p class="text-red-600 font-medium">${message}</p>
                <button onclick="location.reload()" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Retry</button>
            </div>
        `;
    }
}
</script>

</body>
</html>