<?php
require_once 'config/database.php';
require_once 'middleware/AuthMiddleware.php';

AuthMiddleware::handle();

// Get current user data
$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'] ?? null;

$currentUser = null;
if ($userId) {
    $stmt = $db->prepare("SELECT id, username, email, phone, wallet_balance, created_at, last_active, role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$currentUser) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
$isAdmin = ($currentUser['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
   <?php include 'header.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="asserts/js/toast.js"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-6xl mx-auto p-4">
    <!-- Profile Header -->
<div class="bg-white rounded-xl shadow p-4 md:p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <!-- Left Section -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 
                    items-center sm:items-start text-center sm:text-left">

            <!-- Avatar -->
            <div class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 
                        rounded-full bg-gradient-to-r from-blue-400 to-purple-500 
                        flex items-center justify-center text-white 
                        text-2xl sm:text-3xl md:text-4xl font-bold shrink-0">
                <?php echo strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)); ?>
            </div>

            <!-- User Info -->
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl md:text-2xl font-bold break-words">
                    <?php echo htmlspecialchars($currentUser['username']); ?>
                </h1>

                <p class="text-gray-600 text-sm">
                    Member since <?php echo date('F Y', strtotime($currentUser['created_at'])); ?>
                </p>

                <p class="text-gray-600 text-sm mt-1 break-all">
                    <i class="fas fa-envelope mr-2"></i>
                    <?php echo htmlspecialchars($currentUser['email']); ?>
                </p>
            </div>
        </div>

        <!-- Admin Badge -->
        <?php if ($isAdmin): ?>
        <div class="self-center md:self-center bg-purple-100 text-purple-700 
                    px-3 py-1.5 rounded-lg font-medium text-sm whitespace-nowrap">
            <i class="fas fa-crown mr-1"></i> Admin
        </div>
        <?php endif; ?>

    </div>
</div>


    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-r from-green-400 to-green-500 text-white p-4 rounded-xl">
            <p class="text-sm">Wallet Balance</p>
            <p id="profileBalance" class="text-2xl font-bold">₹<?php echo number_format($currentUser['wallet_balance'], 2); ?></p>
        </div>
        <div class="bg-gradient-to-r from-blue-400 to-blue-500 text-white p-4 rounded-xl">
            <p class="text-sm">Tasks Completed</p>
            <p id="completedTasks" class="text-2xl font-bold">0</p>
        </div>
        <div class="bg-gradient-to-r from-purple-400 to-purple-500 text-white p-4 rounded-xl">
            <p class="text-sm">Total Earned</p>
            <p id="totalEarned" class="text-2xl font-bold">₹0</p>
        </div>
        <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-white p-4 rounded-xl">
            <p class="text-sm">Success Rate</p>
            <p id="successRate" class="text-2xl font-bold">0%</p>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Personal Info -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-bold mb-4">Personal Information</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">Username</p>
                    <p id="displayUsername" class="font-bold"><?php echo htmlspecialchars($currentUser['username']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email Address</p>
                    <p id="displayEmail" class="font-bold"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phone</p>
                    <p id="displayPhone" class="font-bold"><?php echo htmlspecialchars($currentUser['phone'] ?? 'Not set'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Account Status</p>
                    <p class="font-bold text-green-600">
                        <i class="fas fa-check-circle mr-2"></i> Active
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Last Active</p>
                    <p id="lastActive" class="font-bold">
                        <?php 
                        if ($currentUser['last_active']) {
                            echo date('M d, Y h:i A', strtotime($currentUser['last_active']));
                        } else {
                            echo 'Just now';
                        }
                        ?>
                    </p>
                </div>
            </div>
            
            <?php if ($isAdmin): ?>
            <button onclick="openModal('editProfileModal')" class="w-full mt-6 bg-blue-500 text-white py-3 rounded-lg font-bold hover:bg-blue-600 transition">
                <i class="fas fa-edit mr-2"></i> Edit Profile
            </button>
            <?php endif; ?>
        </div>

        <!-- Security -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-bold mb-4">Security</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">Password</p>
                    <p class="font-bold">••••••••</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Two-Factor Authentication</p>
                    <p class="font-bold text-yellow-600">Not Enabled</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Password Security</p>
                    <p class="text-sm text-gray-700">Change your password regularly to keep your account secure.</p>
                </div>
            </div>
            
            <button onclick="openModal('changePasswordModal')" class="w-full mt-6 bg-gray-500 text-white py-3 rounded-lg font-bold hover:bg-gray-600 transition">
                <i class="fas fa-key mr-2"></i> Change Password
            </button>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Recent Transactions</h2>
            <button onclick="refreshActivity()" class="text-blue-600 hover:text-blue-800 transition" id="refreshBtn">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        
        <div id="recentActivity" class="space-y-3">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                <p class="mt-2 text-gray-600">Loading transactions...</p>
            </div>
        </div>
    </div>

    <!-- Account Actions -->
<div class="grid grid-cols-3  gap-3">

    <!-- Refer -->
    <button onclick="showReferral()"
        class="bg-gradient-to-r from-green-500 to-green-600 text-white p-3 rounded-lg text-center">
        <i class="fas fa-user-plus text-xl block mb-1"></i>
        <p class="text-sm font-semibold">Refer</p>
        <p class="text-xs opacity-90">₹10 Earn</p>
    </button>

    <!-- Support -->
    <a href="mailto:follopayhelp@gmail.com?subject=Support%20Request"
        class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-3 rounded-lg text-center">
        <i class="fas fa-headset text-xl block mb-1"></i>
        <p class="text-sm font-semibold">Support</p>
        <p class="text-xs opacity-90">Help</p>
    </a>

    <!-- Telegram -->
    <a href="https://t.me/YOUR_TELEGRAM_USERNAME" target="_blank"
        class="bg-gradient-to-r from-sky-500 to-sky-600 text-white p-3 rounded-lg text-center">
        <i class="fab fa-telegram-plane text-xl block mb-1"></i>
        <p class="text-sm font-semibold">Telegram</p>
        <p class="text-xs opacity-90">Join</p>
    </a>

</div>

</div>

<!-- Edit Profile Modal (Admin Only) -->
<div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Edit Profile</h2>
                <button onclick="closeModal('editProfileModal')" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="editProfileForm" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">Username</label>
                    <input type="text" name="username" id="editUsername" value="<?php echo htmlspecialchars($currentUser['username']); ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">Email</label>
                    <input type="email" name="email" id="editEmail" value="<?php echo htmlspecialchars($currentUser['email']); ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">Phone Number</label>
                    <input type="tel" name="phone" id="editPhone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Optional">
                </div>
                
                <div id="editProfileMessage" class="hidden p-3 rounded-lg text-sm"></div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal('editProfileModal')" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition font-medium">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Change Password</h2>
                <button onclick="closeModal('changePasswordModal')" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="changePasswordForm" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">Current Password</label>
                    <div class="relative">
                        <input type="password" name="current_password" id="currentPassword" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <button type="button" onclick="toggleCurrentPassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-800 transition">
                            <i id="currentPasswordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Enter your current password to verify</p>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">New Password</label>
                    <div class="relative">
                        <input type="password" name="new_password" id="newPassword" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <button type="button" onclick="toggleNewPassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-800 transition">
                            <i id="newPasswordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirmNewPassword" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <button type="button" onclick="toggleConfirmNewPassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-800 transition">
                            <i id="confirmNewPasswordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div id="passwordMessage" class="hidden p-3 rounded-lg text-sm"></div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal('changePasswordModal')" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition font-medium">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============================================
// 🎯 ULTRA OPTIMIZED - NO DUPLICATE CALLS!
// ============================================

let walletData = null;
let isLoadingWallet = false;  // ✅ Call guard
let pageInitialized = false;  // ✅ Init guard

// Ek hi baar call hoga - loadWalletData()
window.addEventListener('DOMContentLoaded', initPage);

function initPage() {
    if (pageInitialized) {
        console.log('❌ Page already initialized, skipping...');
        return;
    }
    
    pageInitialized = true;
    console.log('✅ Page initializing...');
    
    loadWalletData();
    setupFormHandlers();
}

function setupFormHandlers() {
    const editForm = document.getElementById('editProfileForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditProfile);
    }
    
    const passwordForm = document.getElementById('changePasswordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', handleChangePassword);
    }
}

/**
 * 🚀 SINGLE API CALL - NO DUPLICATES!
 */
function loadWalletData() {
    // Guard 1: Already loading?
    if (isLoadingWallet) {
        console.log('⏳ Wallet already loading, skipping...');
        return;
    }
    
    // Guard 2: Already loaded?
    if (walletData !== null) {
        console.log('✅ Wallet already cached, using cache...');
        updateUI(walletData);
        return;
    }
    
    isLoadingWallet = true;
    console.log('🔄 Making API call: ajax/wallet.php?action=get_balance');
    
    fetch('ajax/wallet.php?action=get_balance', {
        method: 'GET',
        headers: {
            'Cache-Control': 'no-cache'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch wallet');
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ API Response received:', data);
            isLoadingWallet = false;
            
            if (data.success && data.wallet) {
                walletData = data.wallet;
                updateUI(walletData);
            } else {
                showError('recentActivity', 'Failed to load wallet data');
            }
        })
        .catch(error => {
            console.error('❌ Error loading wallet:', error);
            isLoadingWallet = false;
            showError('recentActivity', error.message);
        });
}

/**
 * Single function to update all UI
 */
function updateUI(wallet) {
    if (!wallet) return;
    
    // Update stats
    updateProfileStats(wallet);
    
    // Update transactions
    updateRecentActivity(wallet);
}

/**
 * Stats calculate karo
 */
function updateProfileStats(wallet) {
    if (!wallet || !wallet.transactions) return;
    
    const transactions = wallet.transactions;
    
    let totalEarned = 0;
    let completedCount = 0;
    let totalCount = transactions.length;
    
    transactions.forEach(tx => {
        if (tx.status === 'completed' && tx.reference_type.includes('post')) {
            totalEarned += parseFloat(tx.amount || 0);
            completedCount++;
        }
    });
    
    const successRate = totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0;
    
    document.getElementById('completedTasks').textContent = completedCount;
    document.getElementById('successRate').textContent = successRate + '%';
    document.getElementById('totalEarned').textContent = '₹' + totalEarned.toFixed(2);
}

/**
 * Recent Activity display
 */
function updateRecentActivity(wallet) {
    if (!wallet || !wallet.transactions) {
        showNoTransactions();
        return;
    }
    
    const transactions = wallet.transactions;
    
    if (Array.isArray(transactions) && transactions.length > 0) {
        renderActivity(transactions);
    } else {
        showNoTransactions();
    }
}

function showNoTransactions() {
    document.getElementById('recentActivity').innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-history text-3xl text-gray-300"></i>
            <p class="mt-2 text-gray-600">No transactions yet</p>
        </div>
    `;
}

function showError(elementId, message) {
    document.getElementById(elementId).innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-exclamation-triangle text-3xl text-red-300"></i>
            <p class="mt-2 text-red-600">Error: ${message}</p>
        </div>
    `;
}

function renderActivity(activities) {
    const container = document.getElementById('recentActivity');
    let html = '';
    
    activities.forEach(activity => {
        const isCredit = activity.type === 'credit';
        const icon = activity.description.includes('withdrawal') ? 'fa-credit-card' :
                    activity.description.includes('approved') ? 'fa-check-circle' :
                    activity.description.includes('rejected') ? 'fa-times-circle' :
                    isCredit ? 'fa-money-bill-wave' : 'fa-arrow-down';
        
        const color = activity.description.includes('rejected') ? 'text-red-500' :
                     isCredit ? 'text-green-500' : 'text-blue-500';
        
        const amount = Math.abs(parseFloat(activity.amount || 0)).toFixed(2);
        
        const statusBadge = activity.status === 'completed' ? 
            '<span class="ml-2 px-2 py-1 bg-green-100 text-green-700 text-xs rounded font-semibold">Completed</span>' :
            activity.status === 'failed' ?
            '<span class="ml-2 px-2 py-1 bg-red-100 text-red-700 text-xs rounded font-semibold">Failed</span>' :
            '<span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded font-semibold">Pending</span>';
        
        html += `
            <div class="flex items-center p-4 border-b last:border-b-0 hover:bg-gray-50 transition">
                <div class="w-10 h-10 rounded-full ${color} bg-opacity-20 flex items-center justify-center mr-4 flex-shrink-0">
                    <i class="fas ${icon} ${color}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-800">${escapeHtml(activity.description || 'Transaction')}</p>
                    <p class="text-sm text-gray-600">${new Date(activity.created_at).toLocaleString()}</p>
                </div>
                <div class="text-right ml-4 flex-shrink-0">
                    <p class="font-bold ${isCredit ? 'text-green-600' : 'text-red-600'}">
                        ${isCredit ? '+' : '-'}₹${amount}
                    </p>
                    ${statusBadge}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html || '<p class="text-center text-gray-600">No transactions found</p>';
}

/**
 * Refresh - walletData use kar, naya call nahi!
 */
function refreshActivity() {
    const btn = document.getElementById('refreshBtn');
    btn.style.transform = 'rotate(360deg)';
    btn.style.transition = 'transform 1s';
    
    setTimeout(() => {
        btn.style.transform = 'rotate(0deg)';
        
        if (walletData) {
            console.log('♻️ Refreshing from cache...');
            updateRecentActivity(walletData);
        } else {
            console.log('📡 Cache empty, fetching fresh...');
            loadWalletData();
        }
    }, 500);
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function showReferral() {
    showToast('Referral feature coming soon!', 3000, 'info');
}

async function handleEditProfile(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update_profile');
    
    const messageDiv = document.getElementById('editProfileMessage');
    messageDiv.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700');
    messageDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating profile...';
    messageDiv.className = 'p-3 rounded-lg text-sm bg-blue-100 text-blue-700';
    
    try {
        const response = await fetch('ajax/profile.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Profile updated successfully!';
            messageDiv.className = 'p-3 rounded-lg text-sm bg-green-100 text-green-700';
            
            setTimeout(() => {
                closeModal('editProfileModal');
                location.reload();
            }, 1500);
        } else {
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + data.message;
            messageDiv.className = 'p-3 rounded-lg text-sm bg-red-100 text-red-700';
        }
    } catch (error) {
        console.error('Error:', error);
        messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Failed to update profile';
        messageDiv.className = 'p-3 rounded-lg text-sm bg-red-100 text-red-700';
    }
}

async function handleChangePassword(e) {
    e.preventDefault();
    
    const newPassword = this.elements.new_password.value;
    const confirmPassword = this.elements.confirm_password.value;
    const messageDiv = document.getElementById('passwordMessage');
    
    if (newPassword !== confirmPassword) {
        messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> Passwords do not match!';
        messageDiv.className = 'p-3 rounded-lg text-sm bg-red-100 text-red-700';
        messageDiv.classList.remove('hidden');
        return;
    }
    
    if (newPassword.length < 6) {
        messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> Password must be at least 6 characters';
        messageDiv.className = 'p-3 rounded-lg text-sm bg-red-100 text-red-700';
        messageDiv.classList.remove('hidden');
        return;
    }
    
    const formData = new FormData(this);
    formData.append('action', 'change_password');
    
    messageDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Changing password...';
    messageDiv.className = 'p-3 rounded-lg text-sm bg-blue-100 text-blue-700';
    messageDiv.classList.remove('hidden');
    
    try {
        const response = await fetch('ajax/profile.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i><strong>Success!</strong> Password changed successfully!';
            messageDiv.className = 'p-3 rounded-lg text-sm bg-green-100 text-green-700';
            
            this.reset();
            setTimeout(() => {
                closeModal('changePasswordModal');
            }, 2000);
        } else {
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> ' + data.message;
            messageDiv.className = 'p-3 rounded-lg text-sm bg-red-100 text-red-700';
        }
    } catch (error) {
        console.error('Error:', error);
        messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> Failed to change password';
        messageDiv.className = 'p-3 rounded-lg text-sm bg-red-100 text-red-700';
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function toggleCurrentPassword() {
    const input = document.getElementById('currentPassword');
    const icon = document.getElementById('currentPasswordIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function toggleNewPassword() {
    const input = document.getElementById('newPassword');
    const icon = document.getElementById('newPasswordIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function toggleConfirmNewPassword() {
    const input = document.getElementById('confirmNewPassword');
    const icon = document.getElementById('confirmNewPasswordIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>