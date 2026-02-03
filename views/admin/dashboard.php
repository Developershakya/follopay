<?php
// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ?page=admin');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <?php include 'header.php'; ?>
    <title>Document</title>
</head>
<body>
    
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
        <p class="text-gray-600">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-90">Total Users</p>
                    <p id="totalUsers" class="text-3xl font-bold">0</p>
                </div>
                <i class="fas fa-users text-3xl opacity-80"></i>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <i class="fas fa-arrow-up mr-1"></i>
                <span id="userGrowth">0%</span> growth this month
            </div>
        </div>

        <!-- Pending Withdrawals -->
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-90">Pending Withdrawals</p>
                    <p id="pendingWithdrawals" class="text-3xl font-bold">0</p>
                </div>
                <i class="fas fa-clock text-3xl opacity-80"></i>
            </div>
            <div class="mt-4">
                <span id="withdrawalAmount" class="text-sm">₹0 pending</span>
            </div>
        </div>

        <!-- Active Posts -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-90">Active Posts</p>
                    <p id="activePosts" class="text-3xl font-bold">0</p>
                </div>
                <i class="fas fa-newspaper text-3xl opacity-80"></i>
            </div>
            <div class="mt-4">
                <span id="availableComments" class="text-sm">0 comments available</span>
            </div>
        </div>

        <!-- Total Earnings -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-90">Platform Earnings</p>
                    <p id="platformEarnings" class="text-3xl font-bold">₹0</p>
                </div>
                <i class="fas fa-chart-line text-3xl opacity-80"></i>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <i class="fas fa-rupee-sign mr-1"></i>
                <span id="todayEarnings">₹0 today</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <a href="?page=admin-posts" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition-shadow border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                    <i class="fas fa-plus text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Create New Post</h3>
                    <p class="text-sm text-gray-600">Add new earning task</p>
                </div>
            </div>
        </a>

        <a href="?page=admin-withdrawals" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition-shadow border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center mr-4">
                    <i class="fas fa-rupee-sign text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Process Withdrawals</h3>
                    <p class="text-sm text-gray-600" id="pendingCount">0 pending</p>
                </div>
            </div>
        </a>

        <a href="?page=admin-users" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition-shadow border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                    <i class="fas fa-user-cog text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Manage Users</h3>
                    <p class="text-sm text-gray-600">View, ban, or edit users</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Withdrawals -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">Recent Withdrawals</h2>
                    <a href="?page=admin-withdrawals" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
                </div>
            </div>
            <div class="p-6">
                <div id="recentWithdrawals" class="space-y-4">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                        <p class="mt-2 text-gray-600">Loading withdrawals...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">Recent Users</h2>
                    <a href="?page=admin-users" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
                </div>
            </div>
            <div class="p-6">
                <div id="recentUsers" class="space-y-4">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                        <p class="mt-2 text-gray-600">Loading users...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <!-- <div class="bg-white rounded-xl shadow mt-8">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">System Status</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 mb-3">
                        <i class="fas fa-server text-green-600"></i>
                    </div>
                    <p class="font-bold">Server Status</p>
                    <p class="text-sm text-green-600">Online</p>
                </div>

                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-3">
                        <i class="fas fa-database text-blue-600"></i>
                    </div>
                    <p class="font-bold">Database</p>
                    <p class="text-sm text-green-600">Healthy</p>
                </div>

                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 mb-3">
                        <i class="fas fa-upload text-purple-600"></i>
                    </div>
                    <p class="font-bold">Uploads</p>
                    <p class="text-sm text-green-600">Normal</p>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Pending Screenshots -->
    <div class="bg-white rounded-xl shadow mt-8">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">Pending Screenshots</h2>
                <button onclick="loadPendingScreenshots()" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-sync-alt"></i>
                </button>

                 <a href="?page=admin-screenshot-verification" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>
        </div>
        <div class="p-6">
<div id="pendingScreenshots"
     class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                    <p class="mt-2 text-gray-600">Loading pending screenshots...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ===== Screenshot Fullscreen Modal ===== -->
<div id="ssModal"
     class="fixed inset-0 bg-black bg-opacity-90 hidden z-50">

    <button onclick="closeSSModal()"
            class="absolute top-4 right-6 text-white text-4xl">
        &times;
    </button>

    <div class="flex flex-col h-full">
        <div class="flex-1 flex items-center justify-center">
            <img id="ssImage"
                 class="max-h-[85vh] max-w-full object-contain rounded">
        </div>

        <div class="bg-gray-900 p-4 flex justify-center gap-4">
            <button onclick="approveSS()"
                    class="bg-green-600 px-6 py-2 rounded text-white">
                Approve
            </button>

            <button onclick="openRejectBox()"
                    class="bg-red-600 px-6 py-2 rounded text-white">
                Reject
            </button>
        </div>
    </div>
</div>

<!-- ===== Reject Reason Box ===== -->
<div id="rejectBox"
     class="fixed inset-0 bg-black bg-opacity-60 hidden z-50
            flex items-center justify-center">

    <div class="bg-white rounded p-6 w-96">
        <h3 class="font-bold mb-2">Reject Reason</h3>

<label class="block text-sm font-semibold mb-1">Select Reason</label>

<select id="rejectReasonSelect"
        class="w-full border p-2 rounded mb-2"
        onchange="toggleCustomReason(this.value)">
    <option value="">-- Select reason --</option>
    <option value="Fake / edited screenshot">Fake / edited screenshot</option>
    <option value="Wrong task completed">Wrong task completed</option>
    <option value="Screenshot not clear">Screenshot not clear</option>
    <option value="App not installed properly">App not installed properly</option>
    <option value="Duplicate submission">Duplicate submission</option>
    <option value="other">Other (write manually)</option>
</select>

<textarea id="rejectReasonCustom"
          class="w-full border p-2 rounded hidden"
          placeholder="Write custom reason..."></textarea>

        <div class="flex justify-end gap-2 mt-4">
            <button onclick="closeRejectBox()">Cancel</button>
            <button onclick="rejectSS()"
                    class="bg-red-600 text-white px-4 py-1 rounded">
                Reject
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadRecentWithdrawals();
    loadRecentUsers();
    loadPendingScreenshots();
});

async function loadDashboardStats() {
    try {
        const response = await fetch('ajax/admin.php?action=get_dashboard_stats');
        const data = await response.json();
        
        if (data.success) {
            // Update stats
            document.getElementById('totalUsers').textContent = data.stats.total_users || 0;
            document.getElementById('pendingWithdrawals').textContent = data.stats.pending_withdrawals || 0;
            document.getElementById('activePosts').textContent = data.stats.active_posts || 0;
            document.getElementById('platformEarnings').textContent = '₹' + (data.stats.total_earnings || 0);
            
            // Update quick action counts
            document.getElementById('pendingCount').textContent = 
                (data.stats.pending_withdrawals || 0) + ' pending';
            
            // Calculate and update growth
            if (data.stats.user_growth) {
                document.getElementById('userGrowth').textContent = data.stats.user_growth + '%';
            }
            
            // Update withdrawal amount
            if (data.stats.pending_withdrawal_amount) {
                document.getElementById('withdrawalAmount').textContent = 
                    '₹' + data.stats.pending_withdrawal_amount + ' pending';
            }
            
            // Update available comments
            if (data.stats.available_comments) {
                document.getElementById('availableComments').textContent = 
                    data.stats.available_comments + ' comments available';
            }
            
            // Update today's earnings
            if (data.stats.today_earnings) {
                document.getElementById('todayEarnings').textContent = 
                    '₹' + data.stats.today_earnings + ' today';
            }
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

async function loadRecentWithdrawals() {
    try {
        const response = await fetch('ajax/admin.php?action=get_recent_withdrawals');
        const data = await response.json();
        
        if (data.success && data.withdrawals.length > 0) {
            renderRecentWithdrawals(data.withdrawals);
        } else {
            document.getElementById('recentWithdrawals').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-2xl text-gray-300"></i>
                    <p class="mt-2 text-gray-600">No recent withdrawals</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading recent withdrawals:', error);
    }
}

function renderRecentWithdrawals(withdrawals) {
    const container = document.getElementById('recentWithdrawals');
    let html = '';
    
    withdrawals.slice(0, 5).forEach(withdrawal => {
        const statusColor = withdrawal.status === 'pending' ? 'text-yellow-600' :
                          withdrawal.status === 'approved' ? 'text-green-600' : 'text-red-600';
        const icon = withdrawal.type === 'upi' ? 'fa-university' : 'fa-gamepad';
        
        html += `
            <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                        <i class="fas ${icon} text-gray-600"></i>
                    </div>
                    <div>
                        <p class="font-bold">${withdrawal.username}</p>
                        <p class="text-sm text-gray-600">
                            ${new Date(withdrawal.created_at).toLocaleDateString()}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold">₹${withdrawal.amount}</p>
                    <span class="text-sm ${statusColor}">
                        ${withdrawal.status}
                    </span>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

async function loadRecentUsers() {
    try {
        const response = await fetch('ajax/admin.php?action=get_recent_users');
        const data = await response.json();
        
        if (data.success && data.users.length > 0) {
            renderRecentUsers(data.users);
        } else {
            document.getElementById('recentUsers').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-users text-2xl text-gray-300"></i>
                    <p class="mt-2 text-gray-600">No recent users</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading recent users:', error);
    }
}

function renderRecentUsers(users) {
    const container = document.getElementById('recentUsers');
    let html = '';
    
    users.slice(0, 5).forEach(user => {
        const statusIcon = user.is_banned ? 
            '<i class="fas fa-ban text-red-500 mr-1"></i>' : 
            '<i class="fas fa-check-circle text-green-500 mr-1"></i>';
        
        const statusText = user.is_banned ? 'Banned' : 'Active';
        
        html += `
            <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold mr-3">
                        ${user.username.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <p class="font-bold">${user.username}</p>
                        <p class="text-sm text-gray-600">${user.email}</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm">
                        ${statusIcon} ${statusText}
                    </div>
                    <p class="text-xs text-gray-600">
                        Joined ${new Date(user.created_at).toLocaleDateString()}
                    </p>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

async function loadPendingScreenshots() {
    try {
        const response = await fetch('ajax/admin.php?action=get_pending_screenshots');
        const data = await response.json();
        
        if (data.success && data.screenshots.length > 0) {
            renderPendingScreenshots(data.screenshots);
        } else {
            document.getElementById('pendingScreenshots').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-2xl text-green-300"></i>
                    <p class="mt-2 text-gray-600">No pending screenshots</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading pending screenshots:', error);
    }
}

let currentAssignmentId = null;

function renderPendingScreenshots(screenshots) {
    const box = document.getElementById('pendingScreenshots');
    box.innerHTML = '';

    screenshots.slice(0, 6).forEach(s => {
        box.innerHTML += `
        <div class="bg-gray-50 border rounded-lg p-3">
            <p class="font-bold">${s.username}</p>
            <p class="text-sm text-gray-600">
                ${s.app_name} – ₹${s.price}
            </p>

            <img src="${s.screenshot_path}"
                 class="mt-2 h-40 w-full object-cover rounded cursor-pointer"
                 onclick="openSSModal(${s.id}, '${s.screenshot_path}')">
        </div>`;
    });
}


function viewScreenshot(screenshotId) {
    window.open(`ajax/admin.php?action=view_screenshot&id=${screenshotId}`, '_blank');
}

function approveScreenshot(screenshotId) {
    if (confirm('Approve this submission?')) {
        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('action', 'approve_screenshot');
        formData.append('screenshot_id', screenshotId);
        
        fetch('ajax/admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Screenshot approved!');
                loadPendingScreenshots();
                loadDashboardStats();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

// Auto-refresh every 60 seconds
setInterval(() => {
    loadDashboardStats();
    loadPendingScreenshots();
}, 60000);

function openSSModal(id, img) {
    currentAssignmentId = id;
    document.getElementById('ssImage').src = img;
    document.getElementById('ssModal').classList.remove('hidden');
}

function closeSSModal() {
    document.getElementById('ssModal').classList.add('hidden');
}

function approveSS() {
    const fd = new FormData();
    fd.append('action', 'approve_submission');
    fd.append('assignment_id', currentAssignmentId);

    fetch('ajax/admin.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            alert(d.message || 'Approved');
            closeSSModal();
            loadPendingScreenshots();
        });
}

function openRejectBox() {
    document.getElementById('rejectBox').classList.remove('hidden');
}

function closeRejectBox() {
    document.getElementById('rejectBox').classList.add('hidden');
    document.getElementById('rejectReasonSelect').value = '';
    document.getElementById('rejectReasonCustom').value = '';
    document.getElementById('rejectReasonCustom').classList.add('hidden');
}


function rejectSS() {
const selected = document.getElementById('rejectReasonSelect').value;
const custom   = document.getElementById('rejectReasonCustom').value.trim();

let reason = '';

if (selected === 'other') {
    reason = custom;
} else {
    reason = selected;
}

if (!reason) {
    alert('Please select or enter a reject reason');
    return;
}


    const fd = new FormData();
    fd.append('action', 'reject_submission');
    fd.append('assignment_id', currentAssignmentId);
    fd.append('reason', reason);

    fetch('ajax/admin.php', { method:'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            alert(d.message || 'Rejected');
            closeRejectBox();
            closeSSModal();
            loadPendingScreenshots();
        });
}

function toggleCustomReason(value) {
    const box = document.getElementById('rejectReasonCustom');
    if (value === 'other') {
        box.classList.remove('hidden');
    } else {
        box.classList.add('hidden');
        box.value = '';
    }
}


</script>

<style>
/* Custom scrollbar for dashboard */
#recentWithdrawals, #recentUsers, #pendingScreenshots {
    max-height: 300px;
    overflow-y: auto;
}

#recentWithdrawals::-webkit-scrollbar,
#recentUsers::-webkit-scrollbar,
#pendingScreenshots::-webkit-scrollbar {
    width: 4px;
}

#recentWithdrawals::-webkit-scrollbar-track,
#recentUsers::-webkit-scrollbar-track,
#pendingScreenshots::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#recentWithdrawals::-webkit-scrollbar-thumb,
#recentUsers::-webkit-scrollbar-thumb,
#pendingScreenshots::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

#recentWithdrawals::-webkit-scrollbar-thumb:hover,
#recentUsers::-webkit-scrollbar-thumb:hover,
#pendingScreenshots::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

</body>
</html>