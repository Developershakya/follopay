/**
 * Dashboard Page
 */

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    // Refresh stats every 30 seconds
    setInterval(loadDashboardStats, 30000);
});

async function loadDashboardStats() {
    try {
        const result = await AdminAPI.getDashboardStats();
        
        if (result.success && result.stats) {
            updateDashboard(result.stats);
        } else {
            console.error('Failed to load dashboard stats');
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

function updateDashboard(stats) {
    // Update stat cards
    document.getElementById('totalUsers').textContent = stats.total_users || 0;
    document.getElementById('pendingWithdrawals').textContent = stats.pending_withdrawals || 0;
    document.getElementById('activePosts').textContent = stats.total_posts || 0;
    document.getElementById('platformEarnings').textContent = formatCurrency(stats.total_earnings || 0);
    
    // Update additional info
    document.getElementById('withdrawalAmount').textContent = 
        formatCurrency(stats.pending_earnings || 0) + ' pending';
    document.getElementById('availableComments').textContent = 
        (stats.available_comments || 0) + ' comments available';
    document.getElementById('todayEarnings').textContent = 
        formatCurrency(stats.today_earnings || 0) + ' today';
    
    // Update pending submissions count if element exists
    const pendingSubmissionsEl = document.getElementById('pendingSubmissions');
    if (pendingSubmissionsEl) {
        pendingSubmissionsEl.textContent = stats.pending_submissions || 0;
    }
    
    // Update growth indicator
    const userGrowth = document.getElementById('userGrowth');
    if (userGrowth) {
        userGrowth.textContent = (stats.user_growth || 0) + '%';
    }
    
    // Update recent activities if available
    if (stats.recent_activities && stats.recent_activities.length > 0) {
        updateRecentActivities(stats.recent_activities);
    }
}

function updateRecentActivities(activities) {
    const container = document.getElementById('recentActivities');
    if (!container) return;
    
    container.innerHTML = activities.map(activity => `
        <div class="flex items-center justify-between py-3 border-b last:border-0">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full ${
                    activity.type === 'withdrawal' ? 'bg-yellow-100' : 
                    activity.type === 'submission' ? 'bg-blue-100' : 
                    'bg-green-100'
                } flex items-center justify-center mr-3">
                    <i class="fas ${
                        activity.type === 'withdrawal' ? 'fa-money-bill-wave text-yellow-600' : 
                        activity.type === 'submission' ? 'fa-image text-blue-600' : 
                        'fa-check text-green-600'
                    }"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">${escapeHtml(activity.description)}</p>
                    <p class="text-xs text-gray-500">${formatDate(activity.created_at)}</p>
                </div>
            </div>
        </div>
    `).join('');
}

// Quick stats functions
async function loadQuickStats() {
    try {
        const [usersResult, postsResult, withdrawalsResult] = await Promise.all([
            AdminAPI.getStats('users'),
            AdminAPI.getStats('posts'),
            AdminAPI.getStats('withdrawals')
        ]);
        
        // Update users stats
        if (usersResult.success) {
            updateUserStats(usersResult.stats);
        }
        
        // Update posts stats
        if (postsResult.success) {
            updatePostStats(postsResult.stats);
        }
        
        // Update withdrawal stats
        if (withdrawalsResult.success) {
            updateWithdrawalStats(withdrawalsResult.stats);
        }
    } catch (error) {
        console.error('Error loading quick stats:', error);
    }
}

function updateUserStats(stats) {
    const container = document.getElementById('userStats');
    if (!container) return;
    
    container.innerHTML = `
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-gray-900">${stats.total || 0}</p>
                <p class="text-xs text-gray-500">Total</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-600">${stats.active_users || 0}</p>
                <p class="text-xs text-gray-500">Active</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-red-600">${stats.banned_users || 0}</p>
                <p class="text-xs text-gray-500">Banned</p>
            </div>
        </div>
    `;
}

function updatePostStats(stats) {
    const container = document.getElementById('postStats');
    if (!container) return;
    
    container.innerHTML = `
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-gray-900">${stats.total || 0}</p>
                <p class="text-xs text-gray-500">Total</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-600">${stats.active || 0}</p>
                <p class="text-xs text-gray-500">Active</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-blue-600">${stats.total_comments || 0}</p>
                <p class="text-xs text-gray-500">Comments</p>
            </div>
        </div>
    `;
}

function updateWithdrawalStats(stats) {
    const container = document.getElementById('withdrawalStats');
    if (!container) return;
    
    container.innerHTML = `
        <div class="grid grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-gray-900">${stats.total || 0}</p>
                <p class="text-xs text-gray-500">Total</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-yellow-600">${stats.pending || 0}</p>
                <p class="text-xs text-gray-500">Pending</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-600">${stats.approved || 0}</p>
                <p class="text-xs text-gray-500">Approved</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-purple-600">${formatCurrency(stats.total_paid || 0)}</p>
                <p class="text-xs text-gray-500">Paid Out</p>
            </div>
        </div>
    `;
}

function escapeHtml(text) {
    if (!text) return '';
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