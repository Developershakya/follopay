<?php
require_once 'config/database.php';
require_once 'middleware/AdminMiddleware.php';
AdminMiddleware::check();

$db = Database::getInstance()->getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'header.php'; ?>
    <title>User Management</title>
</head>
<body>

<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
                <p class="text-gray-600 mt-1">Manage users, ban/unban, view activity</p>
            </div>
            <div class="flex space-x-4">
                <a href="?page=export" class="bg-green-50 text-green-600 px-4 py-2 rounded-lg hover:bg-green-100 transition">
                    <i class="fas fa-file-excel mr-2"></i> Export
                </a>
                <button onclick="showAddUserModal()" class="bg-blue-500 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-600 transition">
                    <i class="fas fa-user-plus mr-2"></i> Add User
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Total Users</p>
                        <p id="totalUsers" class="text-3xl font-bold">0</p>
                    </div>
                    <i class="fas fa-users text-3xl opacity-80"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-xl p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Active Today</p>
                        <p id="activeToday" class="text-3xl font-bold">0</p>
                    </div>
                    <i class="fas fa-user-check text-3xl opacity-80"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-red-400 to-red-500 rounded-xl p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Banned Users</p>
                        <p id="bannedUsers" class="text-3xl font-bold">0</p>
                    </div>
                    <i class="fas fa-user-slash text-3xl opacity-80"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-purple-400 to-purple-500 rounded-xl p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Total Earnings</p>
                        <p id="totalEarnings" class="text-3xl font-bold">₹0</p>
                    </div>
                    <i class="fas fa-rupee-sign text-3xl opacity-80"></i>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <input type="text" id="searchUsers" placeholder="Search by username, email, phone..." 
                       class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <select id="statusFilter" class="w-full px-4 py-2 border rounded-lg">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="banned">Banned</option>
                    <option value="inactive">Inactive (7+ days)</option>
                </select>
            </div>
            <div>
                <select id="sortBy" class="w-full px-4 py-2 border rounded-lg">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="balance_high">Balance (High to Low)</option>
                    <option value="balance_low">Balance (Low to High)</option>
                </select>
            </div>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTable" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                            <p class="mt-2 text-gray-600">Loading users...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-700">
                Showing <span id="userStart">0</span> to <span id="userEnd">0</span> of <span id="userTotal">0</span> users
            </div>
            <div class="flex space-x-2">
                <button onclick="prevUserPage()" id="prevUserBtn" class="px-3 py-1 border rounded disabled:opacity-50 hover:bg-gray-100 transition" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="userPagination" class="flex space-x-1"></div>
                <button onclick="nextUserPage()" id="nextUserBtn" class="px-3 py-1 border rounded disabled:opacity-50 hover:bg-gray-100 transition" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Ban User -->
<div id="banModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-4">Ban User</h3>
                
                <div class="mb-4">
                    <p id="banUserName" class="font-bold text-lg"></p>
                    <p id="banUserEmail" class="text-gray-600"></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Ban Reason</label>
                    <select id="banReasonSelect" class="w-full px-3 py-2 border rounded-lg mb-2">
                        <option value="">Select Reason</option>
                        <option value="Multiple Account">Multiple Account</option>
                        <option value="Fake Screenshots">Fake Screenshots</option>
                        <option value="Violation of Terms">Violation of Terms</option>
                        <option value="Spamming">Spamming</option>
                        <option value="Fraudulent Activity">Fraudulent Activity</option>
                        <option value="Other">Other</option>
                    </select>
                    <textarea id="customBanReason" class="w-full px-3 py-2 border rounded-lg hidden" 
                              placeholder="Enter custom reason..." rows="3"></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="confirmBan()" class="flex-1 bg-red-500 text-white py-2 rounded-lg font-bold hover:bg-red-600 transition">
                        Ban User
                    </button>
                    <button onclick="closeBanModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg font-bold hover:bg-gray-600 transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add User -->
<div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-4">Add New User</h3>
                
                <form id="addUserForm" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Username</label>
                        <input type="text" id="addUsername" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Email</label>
                        <input type="email" id="addEmail" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Password <span class="text-red-600">*</span></label>
                        <div class="flex gap-2">
                            <input type="password" id="addPasswordInput" required class="flex-1 px-3 py-2 border rounded-lg">
                            <button type="button" onclick="toggleAddPassword()" id="addTogglePasswordBtn" class="px-3 py-2 bg-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 transition">Show</button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Phone (Optional)</label>
                        <input type="tel" id="addPhone" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Initial Wallet Balance</label>
                        <input type="number" id="addWalletBalance" value="0" min="0" step="0.01" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="addIsAdmin" class="mr-2">
                            <span>Make Admin User</span>
                        </label>
                    </div>
                </form>
                
                <div class="mt-6 flex space-x-3">
                    <button onclick="createUser()" class="flex-1 bg-blue-500 text-white py-2 rounded-lg font-bold hover:bg-blue-600 transition">
                        Create User
                    </button>
                    <button onclick="closeAddUserModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg font-bold hover:bg-gray-600 transition">
                        Cancel
                    </button>
                </div>
                
                <div id="addUserMessage" class="mt-4 text-center hidden"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Edit User -->
<div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-4">Edit User</h3>
                
                <form id="editUserForm" class="space-y-4">
                    <input type="hidden" id="editUserId">
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Username</label>
                        <input type="text" id="editUsername" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Email</label>
                        <input type="email" id="editEmail" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Phone</label>
                        <input type="tel" id="editPhone" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Wallet Balance</label>
                        <input type="number" id="editWalletBalance" min="0" step="0.01" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Change Password (Leave empty to keep current)</label>
                        <div class="flex gap-2">
                            <input type="password" id="editPasswordInput" class="flex-1 px-3 py-2 border rounded-lg">
                            <button type="button" onclick="toggleEditPassword()" id="editTogglePasswordBtn" class="px-3 py-2 bg-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 transition">Show</button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimum 6 characters if changing</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="editIsBanned" class="mr-2">
                            <span class="font-medium">Ban This User</span>
                        </label>
                        <textarea id="editBanReason" class="w-full px-3 py-2 border rounded-lg mt-2 hidden" 
                                  placeholder="Ban reason..." rows="2"></textarea>
                    </div>
                </form>
                
                <div class="mt-6 flex space-x-3">
                    <button onclick="updateUser()" class="flex-1 bg-green-500 text-white py-2 rounded-lg font-bold hover:bg-green-600 transition">
                        Update User
                    </button>
                    <button onclick="closeEditUserModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg font-bold hover:bg-gray-600 transition">
                        Cancel
                    </button>
                </div>
                
                <div id="editUserMessage" class="mt-4 text-center hidden"></div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentUserId = null;
let userPage = 1;
let userTotalPages = 1;
let userPageSize = 10;

document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    setupUserEventListeners();
});

function setupUserEventListeners() {
    document.getElementById('searchUsers').addEventListener('input', function() {
        setTimeout(() => loadUsers(), 500);
    });
    document.getElementById('statusFilter').addEventListener('change', loadUsers);
    document.getElementById('sortBy').addEventListener('change', loadUsers);
    
    document.getElementById('banReasonSelect').addEventListener('change', function() {
        document.getElementById('customBanReason').classList.toggle('hidden', this.value !== 'Other');
    });

    document.getElementById('editIsBanned').addEventListener('change', function() {
        document.getElementById('editBanReason').classList.toggle('hidden', !this.checked);
    });
}

async function loadUsers() {
    const tableBody = document.getElementById('usersTable');
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="px-6 py-8 text-center">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                <p class="mt-2 text-gray-600">Loading users...</p>
            </td>
        </tr>
    `;

    const search = document.getElementById('searchUsers').value;
    const status = document.getElementById('statusFilter').value;
    const sort = document.getElementById('sortBy').value;

    try {
        const response = await fetch(`ajax/admin.php?action=get_users&page=${userPage}&search=${encodeURIComponent(search)}&status=${status}&sort=${sort}`);
        const data = await response.json();

        if (data.success) {
            updateUserStats(data.total || 0, data.users.length || 0);
            renderUsers(data.users || []);
            updateUserPagination(data.total || 0, userPage, Math.ceil((data.total || 0)/userPageSize));
        } else {
            renderUsers([]);
        }
    } catch (error) {
        console.error('Error loading users:', error);
        renderUsers([]);
    }
}

// Update stats
function updateUserStats(totalUsers = 0, currentCount = 0) {
    document.getElementById('totalUsers').textContent = totalUsers;
    document.getElementById('activeToday').textContent = currentCount;
    document.getElementById('bannedUsers').textContent = 0;
    document.getElementById('totalEarnings').textContent = '₹0';
}

// Render table
function renderUsers(users) {
    const tableBody = document.getElementById('usersTable');

    if (!users || users.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <i class="fas fa-users text-3xl text-gray-300"></i>
                    <p class="mt-2 text-gray-600">No users found</p>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    users.forEach(user => {
        const isBanned = user.is_banned == 1;
        const statusColor = isBanned ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
        const statusText = isBanned ? 'Banned' : 'Active';

        html += `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${escapeHtml(user.username)}</div>
                    <div class="text-sm text-gray-500">Joined: ${new Date(user.created_at).toLocaleDateString()}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${escapeHtml(user.email)}</div>
                    <div class="text-sm text-gray-500">${escapeHtml(user.phone || 'No phone')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-bold text-green-600">₹${parseFloat(user.wallet_balance || 0).toFixed(2)}</div>
                    <div class="text-xs text-gray-500">Earned: ₹${parseFloat(user.total_earned || 0).toFixed(2)}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm">${user.total_submissions || 0} submissions</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full ${statusColor}">${statusText}</span>
                    ${isBanned && user.ban_reason ? `<div class="text-xs text-red-600 mt-1">${escapeHtml(user.ban_reason)}</div>` : ''}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                        <button onclick="editUser(${user.id}, '${escapeJs(user.username)}', '${escapeJs(user.email)}', '${escapeJs(user.phone || '')}', ${user.wallet_balance}, ${user.is_banned}, '${escapeJs(user.ban_reason || '')}')"
                                class="text-green-600 hover:text-green-900 transition" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${!isBanned ? `
                        <button onclick="openBanModal(${user.id}, '${escapeJs(user.username)}', '${escapeJs(user.email)}')"
                                class="text-red-600 hover:text-red-900 transition" title="Ban User">
                            <i class="fas fa-ban"></i>
                        </button>` : `
                        <button onclick="unbanUser(${user.id})" 
                                class="text-green-600 hover:text-green-900 transition" title="Unban User">
                            <i class="fas fa-check"></i>
                        </button>`}
                        <button onclick="deleteUser(${user.id}, '${escapeJs(user.username)}')" class="text-gray-600 hover:text-gray-900 transition" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;
}

// Pagination
function updateUserPagination(total, page, pages) {
    userTotalPages = pages;
    document.getElementById('userStart').textContent = total === 0 ? 0 : ((page - 1) * userPageSize) + 1;
    document.getElementById('userEnd').textContent = Math.min(page * userPageSize, total);
    document.getElementById('userTotal').textContent = total;

    document.getElementById('prevUserBtn').disabled = page === 1;
    document.getElementById('nextUserBtn').disabled = page === pages || pages === 0;

    const paginationDiv = document.getElementById('userPagination');
    let paginationHtml = '';

    const startPage = Math.max(1, page - 2);
    const endPage = Math.min(pages, page + 2);

    for (let i = startPage; i <= endPage; i++) {
        paginationHtml += `
            <button onclick="goToUserPage(${i})" 
                    class="px-3 py-1 border rounded transition ${i === page ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 hover:bg-gray-100'}">
                ${i}
            </button>
        `;
    }

    paginationDiv.innerHTML = paginationHtml;
}

function goToUserPage(page) {
    userPage = page;
    loadUsers();
    window.scrollTo(0, 0);
}

function prevUserPage() {
    if (userPage > 1) {
        userPage--;
        loadUsers();
        window.scrollTo(0, 0);
    }
}

function nextUserPage() {
    if (userPage < userTotalPages) {
        userPage++;
        loadUsers();
        window.scrollTo(0, 0);
    }
}

function openBanModal(userId, username, email) {
    currentUserId = userId;
    
    document.getElementById('banUserName').textContent = escapeHtml(username);
    document.getElementById('banUserEmail').textContent = escapeHtml(email);
    document.getElementById('banModal').classList.remove('hidden');
}

function closeBanModal() {
    document.getElementById('banModal').classList.add('hidden');
    document.getElementById('banReasonSelect').value = '';
    document.getElementById('customBanReason').value = '';
    document.getElementById('customBanReason').classList.add('hidden');
    currentUserId = null;
}

async function confirmBan() {
    if (!currentUserId) return;
    
    let reason = document.getElementById('banReasonSelect').value;
    if (reason === 'Other') {
        reason = document.getElementById('customBanReason').value;
    }
    
    if (!reason.trim()) {
        alert('Please enter a ban reason');
        return;
    }
    
    try {
        const response = await fetch('ajax/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=ban_user&user_id=${currentUserId}&reason=${encodeURIComponent(reason)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('User banned successfully!');
            closeBanModal();
            loadUsers();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error banning user');
    }
}

async function unbanUser(userId) {
    if (confirm('Unban this user?')) {
        try {
            const response = await fetch('ajax/admin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=unban_user&user_id=${userId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('User unbanned successfully!');
                loadUsers();
            }
        } catch (error) {
            alert('Error unbanning user');
        }
    }
}

function showAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
    document.getElementById('addUserForm').reset();
    document.getElementById('addUserMessage').classList.add('hidden');
    
    const passwordInput = document.getElementById('addPasswordInput');
    const toggleBtn = document.getElementById('addTogglePasswordBtn');
    
    if (passwordInput) {
        passwordInput.type = 'password';
    }
    if (toggleBtn) {
        toggleBtn.textContent = 'Show';
    }
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
}

function toggleAddPassword() {
    const input = document.getElementById('addPasswordInput');
    const btn = document.getElementById('addTogglePasswordBtn');
    
    if (!input || !btn) {
        console.error('Password input or button not found');
        return;
    }
    
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
    } else {
        input.type = 'password';
        btn.textContent = 'Show';
    }
}

async function createUser() {
    const form = document.getElementById('addUserForm');
    const username = document.getElementById('addUsername').value.trim();
    const email = document.getElementById('addEmail').value.trim().toLowerCase();
    const password = document.getElementById('addPasswordInput').value;
    const phone = document.getElementById('addPhone').value.trim();
    const walletBalance = document.getElementById('addWalletBalance').value;
    const isAdmin = document.getElementById('addIsAdmin').checked;
    
    if (!username || !email || !password) {
        alert('Username, email, and password are required');
        return;
    }
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters');
        return;
    }
    
    try {
        const response = await fetch('ajax/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=create_user&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&phone=${encodeURIComponent(phone)}&wallet_balance=${walletBalance}&is_admin=${isAdmin ? 1 : null}`
        });
        
        const data = await response.json();
        const messageDiv = document.getElementById('addUserMessage');
        messageDiv.classList.remove('hidden');
        
        if (data.success) {
            messageDiv.className = 'text-green-600';
            messageDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${data.message}`;
            form.reset();
            document.getElementById('addPasswordInput').type = 'password';
            setTimeout(() => {
                closeAddUserModal();
                loadUsers();
            }, 1500);
        } else {
            messageDiv.className = 'text-red-600';
            messageDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message}`;
        }
    } catch (error) {
        alert('Error creating user: ' + error.message);
    }
}

function editUser(id, username, email, phone, balance, isBanned, banReason) {
    currentUserId = id;
    
    document.getElementById('editUserId').value = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editEmail').value = email;
    document.getElementById('editPhone').value = phone;
    document.getElementById('editWalletBalance').value = balance;
    document.getElementById('editIsBanned').checked = isBanned == 1;
    document.getElementById('editBanReason').value = banReason;
    
    // Reset password field
    const passwordInput = document.getElementById('editPasswordInput');
    if (passwordInput) {
        passwordInput.value = '';
        passwordInput.type = 'password';
    }
    
    const toggleBtn = document.getElementById('editTogglePasswordBtn');
    if (toggleBtn) {
        toggleBtn.textContent = 'Show';
    }
    
    if (isBanned == 1) {
        document.getElementById('editBanReason').classList.remove('hidden');
    } else {
        document.getElementById('editBanReason').classList.add('hidden');
    }
    
    document.getElementById('editUserModal').classList.remove('hidden');
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.add('hidden');
    currentUserId = null;
}

function toggleEditPassword() {
    const input = document.getElementById('editPasswordInput');
    const btn = document.getElementById('editTogglePasswordBtn');
    
    if (!input || !btn) {
        console.error('Password input or button not found');
        return;
    }
    
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
    } else {
        input.type = 'password';
        btn.textContent = 'Show';
    }
}

async function updateUser() {
    const username = document.getElementById('editUsername').value.trim();
    const email = document.getElementById('editEmail').value.trim();
    const phone = document.getElementById('editPhone').value.trim();
    const balance = document.getElementById('editWalletBalance').value;
    const newPassword = document.getElementById('editPasswordInput').value;
    const isBanned = document.getElementById('editIsBanned').checked ? 1 : 0;
    const banReason = document.getElementById('editBanReason').value;
    
    if (!username || !email) {
        alert('Username and email are required');
        return;
    }
    
    if (newPassword && newPassword.length < 6) {
        alert('Password must be at least 6 characters');
        return;
    }
    
    let body = `action=update_user&id=${currentUserId}&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}&wallet_balance=${balance}&is_banned=${isBanned}&ban_reason=${encodeURIComponent(banReason)}`;
    
    if (newPassword) {
        body += `&new_password=${encodeURIComponent(newPassword)}`;
    }
    
    try {
        const response = await fetch('ajax/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: body
        });
        
        const result = await response.json();
        const messageDiv = document.getElementById('editUserMessage');
        messageDiv.classList.remove('hidden');
        
        if (result.success) {
            messageDiv.className = 'text-green-600';
            messageDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${result.message}`;
            setTimeout(() => {
                closeEditUserModal();
                loadUsers();
            }, 1500);
        } else {
            messageDiv.className = 'text-red-600';
            messageDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${result.message}`;
        }
    } catch (error) {
        alert('Error updating user: ' + error.message);
    }
}

async function deleteUser(userId, username) {
    if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone and will delete all their data.`)) {
        try {
            const response = await fetch('ajax/admin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_user&user_id=${userId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('User deleted successfully!');
                loadUsers();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('Error deleting user: ' + error.message);
        }
    }
}



// Utility functions
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

function escapeJs(text) {
    return text.replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n');
}
</script>
</body>
</html>