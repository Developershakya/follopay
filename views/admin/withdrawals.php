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
     <script src="../../assets/js/withdrawals.js"></script>
    <title>Document</title>
</head>
<body>
    
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Withdrawal Management</h1>
                <p class="text-gray-600 mt-1">Approve or reject withdrawal requests</p>
            </div>
            <div class="flex space-x-4">
                <div class="relative">
                    <button id="filterDropdown" class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div id="filterMenu" class="hidden absolute right-0 mt-2 bg-white shadow-lg rounded-lg w-48 z-10">
                        <button onclick="filterWithdrawals('all')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">All</button>
                        <button onclick="filterWithdrawals('pending')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Pending</button>
                        <button onclick="filterWithdrawals('approved')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Approved</button>
                        <button onclick="filterWithdrawals('failed')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Failed</button>
                    </div>
                </div>
                <button onclick="exportToExcel()" class="bg-green-50 text-green-600 px-4 py-2 rounded-lg">
                    <i class="fas fa-file-excel mr-2"></i> Export
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Pending</p>
                        <p id="pendingCount" class="text-3xl font-bold">0</p>
                    </div>
                    <i class="fas fa-clock text-3xl opacity-80"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-xl p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Approved</p>
                        <p id="approvedCount" class="text-3xl font-bold">0</p>
                    </div>
                    <i class="fas fa-check-circle text-3xl opacity-80"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-red-400 to-red-500 rounded-xl p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Failed</p>
                        <p id="failedCount" class="text-3xl font-bold">0</p>
                    </div>
                    <i class="fas fa-times-circle text-3xl opacity-80"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Total Amount</p>
                        <p id="totalAmount" class="text-3xl font-bold">₹0</p>
                    </div>
                    <i class="fas fa-rupee-sign text-3xl opacity-80"></i>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="text" id="searchInput" placeholder="Search by username, email, UPI..." 
                       class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <select id="typeFilter" class="w-full px-4 py-2 border rounded-lg">
                    <option value="">All Types</option>
                    <option value="upi">UPI</option>
                    <option value="free_fire">Free Fire</option>
                </select>
            </div>
            <div>
                <input type="date" id="dateFilter" class="w-full px-4 py-2 border rounded-lg">
            </div>
        </div>

        <!-- Withdrawals Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="withdrawalsTable" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be loaded here -->
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center">
                            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                            <p class="mt-2 text-gray-600">Loading withdrawals...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-700">
                Showing <span id="startCount">0</span> to <span id="endCount">0</span> of <span id="totalCount">0</span> entries
            </div>
            <div class="flex space-x-2">
                <button onclick="prevPage()" id="prevBtn" class="px-3 py-1 border rounded disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="paginationNumbers" class="flex space-x-1"></div>
                <button onclick="nextPage()" id="nextBtn" class="px-3 py-1 border rounded disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Processing Withdrawal -->
<div id="processModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-4" id="modalTitle">Process Withdrawal</h3>
                
                <div class="mb-4">
                    <p class="text-gray-600">User: <span id="modalUser" class="font-bold"></span></p>
                    <p class="text-gray-600">Amount: <span id="modalAmount" class="font-bold"></span></p>
                    <p class="text-gray-600">Type: <span id="modalType" class="font-bold"></span></p>
                    <p id="modalDetails" class="text-gray-600"></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Admin Notes</label>
                    <textarea id="adminNotes" class="w-full px-3 py-2 border rounded-lg" rows="3"></textarea>
                </div>
                
                <div id="upiSection" class="mb-4 hidden">
                    <label class="block text-gray-700 mb-2">Payment Proof (Screenshot)</label>
                    <input type="file" id="paymentProof" accept="image/*" class="w-full px-3 py-2 border rounded">
                </div>
                
                <div id="freeFireSection" class="mb-4 hidden">
                    <label class="block text-gray-700 mb-2">Diamond Transfer Proof</label>
                    <input type="file" id="diamondProof" accept="image/*" class="w-full px-3 py-2 border rounded">
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="approveWithdrawal()" class="flex-1 bg-green-500 text-white py-2 rounded-lg font-bold">
                        <i class="fas fa-check mr-2"></i> Approve
                    </button>
                    <button onclick="rejectWithdrawal()" class="flex-1 bg-red-500 text-white py-2 rounded-lg font-bold">
                        <i class="fas fa-times mr-2"></i> Reject
                    </button>
                    <button onclick="closeModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg font-bold">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for View Details -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-lg">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-4">Withdrawal Details</h3>
                
                <div class="space-y-3" id="detailsContent">
                    <!-- Details will be loaded here -->
                </div>
                
                <div class="mt-6">
                    <button onclick="closeDetailsModal()" class="w-full bg-gray-500 text-white py-2 rounded-lg font-bold">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentWithdrawalId = null;
let currentWithdrawalData = null;
let currentPage = 1;
let totalPages = 1;
let pageSize = 10;
let currentFilter = 'pending';

// Load withdrawals on page load
document.addEventListener('DOMContentLoaded', function() {
    loadWithdrawals();
    setupEventListeners();
});

function setupEventListeners() {
    // Search input
    document.getElementById('searchInput').addEventListener('input', function() {
        setTimeout(() => loadWithdrawals(), 500);
    });
    
    // Type filter
    document.getElementById('typeFilter').addEventListener('change', loadWithdrawals);
    
    // Date filter
    document.getElementById('dateFilter').addEventListener('change', loadWithdrawals);
    
    // Filter dropdown
    document.getElementById('filterDropdown').addEventListener('click', function() {
        document.getElementById('filterMenu').classList.toggle('hidden');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#filterDropdown')) {
            document.getElementById('filterMenu').classList.add('hidden');
        }
    });
}

function filterWithdrawals(status) {
    currentFilter = status;
    currentPage = 1;
    loadWithdrawals();
    document.getElementById('filterMenu').classList.add('hidden');
}

async function loadWithdrawals() {
    const search = document.getElementById('searchInput').value;
    const type = document.getElementById('typeFilter').value;
    const date = document.getElementById('dateFilter').value;
    
    try {
        const response = await fetch(`ajax/admin.php?action=get_withdrawals&page=${currentPage}&status=${currentFilter}&search=${encodeURIComponent(search)}&type=${type}&date=${date}`);
        const data = await response.json();
        
        if (data.success) {
            updateStats(data.stats);
            renderWithdrawals(data.withdrawals);
            updatePagination(data.stats.total, currentPage, Math.ceil(data.stats.total / pageSize));
        } else {
            console.error('Error loading withdrawals:', data.message);
        }
    } catch (error) {
        console.error('Error loading withdrawals:', error);
        alert('Failed to load withdrawals. Check console for details.');
    }
}

function updateStats(stats) {
    document.getElementById('pendingCount').textContent = parseInt(stats.pending) || 0;
    document.getElementById('approvedCount').textContent = parseInt(stats.approved) || 0;
    document.getElementById('failedCount').textContent = parseInt(stats.failed) || 0;
    
    const totalAmount = parseFloat(stats.total_amount) || 0;
    document.getElementById('totalAmount').textContent = '₹' + totalAmount.toFixed(2);
}

function renderWithdrawals(withdrawals) {
    const tableBody = document.getElementById('withdrawalsTable');
    
    if (withdrawals.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                    <i class="fas fa-inbox text-3xl text-gray-300"></i>
                    <p class="mt-2 text-gray-600">No withdrawals found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    withdrawals.forEach(withdrawal => {
        const statusColor = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'approved': 'bg-green-100 text-green-800',
            'failed': 'bg-red-100 text-red-800',
            'refunded': 'bg-blue-100 text-blue-800'
        }[withdrawal.status] || 'bg-gray-100 text-gray-800';
        
        const typeIcon = withdrawal.type === 'upi' ? 'fa-university' : 'fa-gamepad';
        const typeColor = withdrawal.type === 'upi' ? 'text-blue-600' : 'text-purple-600';
        
        html += `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${escapeHtml(withdrawal.username)}</div>
                            <div class="text-sm text-gray-500">${escapeHtml(withdrawal.email)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <i class="fas ${typeIcon} ${typeColor} mr-2"></i>
                        <span class="text-sm">${withdrawal.type === 'upi' ? 'UPI' : 'Free Fire'}</span>
                        ${withdrawal.withdraw_mode === 'duration' ? '<span class="ml-2 text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Duration</span>' : ''}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-bold">₹${parseFloat(withdrawal.amount).toFixed(2)}</div>
                    ${withdrawal.charge_amount > 0 ? 
                        `<div class="text-xs text-red-600">Charge: ₹${parseFloat(withdrawal.charge_amount).toFixed(2)}</div>
                         <div class="text-xs text-green-600">Final: ₹${parseFloat(withdrawal.final_amount).toFixed(2)}</div>` : ''}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm">
                        ${withdrawal.upi_id ? `UPI: ${escapeHtml(withdrawal.upi_id)}` : ''}
                        ${withdrawal.free_fire_uid ? `UID: ${escapeHtml(withdrawal.free_fire_uid)}` : ''}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${new Date(withdrawal.created_at).toLocaleDateString()}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full ${statusColor}">
                        ${withdrawal.status}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                        <button onclick="viewDetails(${withdrawal.id})" class="text-blue-600 hover:text-blue-900 transition">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${withdrawal.status === 'pending' ? `
                        <button onclick="openProcessModal(${withdrawal.id})" 
                                class="text-green-600 hover:text-green-900 transition">
                            <i class="fas fa-edit"></i>
                        </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

function updatePagination(total, page, pages) {
    totalPages = pages;
    
    document.getElementById('startCount').textContent = ((page - 1) * pageSize) + 1;
    document.getElementById('endCount').textContent = Math.min(page * pageSize, total);
    document.getElementById('totalCount').textContent = total;
    
    // Update pagination buttons
    document.getElementById('prevBtn').disabled = page === 1;
    document.getElementById('nextBtn').disabled = page === pages || pages === 0;
    
    // Generate page numbers
    const paginationDiv = document.getElementById('paginationNumbers');
    let paginationHtml = '';
    
    const startPage = Math.max(1, page - 2);
    const endPage = Math.min(pages, page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHtml += `
            <button onclick="goToPage(${i})" 
                    class="px-3 py-1 border rounded transition ${i === page ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 hover:bg-gray-100'}">
                ${i}
            </button>
        `;
    }
    
    paginationDiv.innerHTML = paginationHtml;
}

function goToPage(page) {
    currentPage = page;
    loadWithdrawals();
    window.scrollTo(0, 0);
}

function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        loadWithdrawals();
        window.scrollTo(0, 0);
    }
}

function nextPage() {
    if (currentPage < totalPages) {
        currentPage++;
        loadWithdrawals();
        window.scrollTo(0, 0);
    }
}

async function openProcessModal(id) {
    try {
        // Fetch withdrawal details first
        const response = await fetch(`ajax/admin.php?action=get_withdrawal_details&id=${id}`);
        const data = await response.json();
        
        if (!data.success) {
            alert('Error loading withdrawal details');
            return;
        }
        
        const withdrawal = data.withdrawal;
        currentWithdrawalId = id;
        currentWithdrawalData = withdrawal;
        
        document.getElementById('modalTitle').textContent = `Process Withdrawal #${id}`;
        document.getElementById('modalUser').textContent = escapeHtml(withdrawal.username);
        document.getElementById('modalAmount').textContent = `₹${parseFloat(withdrawal.amount).toFixed(2)}`;
        document.getElementById('modalType').textContent = withdrawal.type === 'upi' ? 'UPI Transfer' : 'Free Fire Diamonds';
        
        if (withdrawal.type === 'upi') {
            document.getElementById('modalDetails').textContent = `UPI ID: ${escapeHtml(withdrawal.upi_id)}`;
            document.getElementById('upiSection').classList.add('hidden');
            document.getElementById('freeFireSection').classList.add('hidden');
        } else {
            document.getElementById('modalDetails').textContent = `Free Fire UID: ${escapeHtml(withdrawal.free_fire_uid)}`;
            document.getElementById('upiSection').classList.add('hidden');
            document.getElementById('freeFireSection').classList.add('hidden');
        }
        
        document.getElementById('adminNotes').value = '';
        document.getElementById('processModal').classList.remove('hidden');
    } catch (error) {
        console.error('Error opening modal:', error);
        alert('Failed to load withdrawal details');
    }
}

function closeModal() {
    document.getElementById('processModal').classList.add('hidden');
    document.getElementById('adminNotes').value = '';
    currentWithdrawalId = null;
    currentWithdrawalData = null;
}

async function approveWithdrawal() {
    if (!currentWithdrawalId) {
        alert('No withdrawal selected');
        return;
    }
    
    const notes = document.getElementById('adminNotes').value.trim();
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    
    try {
        const response = await fetch('ajax/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=approve_withdrawal&withdrawal_id=${currentWithdrawalId}&notes=${encodeURIComponent(notes)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✓ Withdrawal approved successfully! Transaction updated.');
            closeModal();
            loadWithdrawals();
        } else {
            alert('❌ Error: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Network error. Check console for details.');
    } finally {
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

async function rejectWithdrawal() {
    if (!currentWithdrawalId) {
        alert('No withdrawal selected');
        return;
    }
    
    const notes = document.getElementById('adminNotes').value.trim();
    
    if (!notes) {
        alert('Please provide a reason for rejection');
        return;
    }
    
    const refund = confirm('Refund amount to user wallet?');
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    
    try {
        const response = await fetch('ajax/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=reject_withdrawal&withdrawal_id=${currentWithdrawalId}&notes=${encodeURIComponent(notes)}&refund=${refund ? '1' : '0'}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✓ Withdrawal rejected!' + (refund ? ' Amount refunded to wallet.' : ''));
            closeModal();
            loadWithdrawals();
        } else {
            alert('❌ Error: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Network error. Check console for details.');
    } finally {
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

async function viewDetails(id) {
    try {
        const response = await fetch(`ajax/admin.php?action=get_withdrawal_details&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const withdrawal = data.withdrawal;
            let html = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded">
                            <p class="text-sm text-gray-600">User</p>
                            <p class="font-bold">${escapeHtml(withdrawal.username)}</p>
                            <p class="text-xs text-gray-500">${escapeHtml(withdrawal.email)}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <p class="text-sm text-gray-600">Amount</p>
                            <p class="font-bold text-green-600">₹${parseFloat(withdrawal.amount).toFixed(2)}</p>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Type</p>
                        <p class="font-bold">${withdrawal.type === 'upi' ? 'UPI Transfer' : 'Free Fire Diamonds'}</p>
                        ${withdrawal.withdraw_mode === 'duration' ? '<span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded ml-2">Duration Mode</span>' : ''}
                    </div>
                    
                    ${withdrawal.upi_id ? `
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">UPI ID</p>
                        <p class="font-bold font-mono">${escapeHtml(withdrawal.upi_id)}</p>
                    </div>
                    ` : ''}
                    
                    ${withdrawal.free_fire_uid ? `
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Free Fire UID</p>
                        <p class="font-bold font-mono">${escapeHtml(withdrawal.free_fire_uid)}</p>
                    </div>
                    ` : ''}
                    
                    ${withdrawal.phone ? `
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Phone</p>
                        <p class="font-bold font-mono">${escapeHtml(withdrawal.phone)}</p>
                    </div>
                    ` : ''}
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded">
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="px-2 py-1 text-xs rounded-full ${
                                withdrawal.status === 'approved' ? 'bg-green-100 text-green-800' : 
                                withdrawal.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                'bg-red-100 text-red-800'
                            }">
                                ${withdrawal.status.toUpperCase()}
                            </span>
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <p class="text-sm text-gray-600">Request Date</p>
                            <p class="text-sm">${new Date(withdrawal.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                    
                    ${withdrawal.charge_amount > 0 ? `
                    <div class="bg-red-50 p-3 rounded border border-red-200">
                        <p class="text-sm text-gray-600">Platform Charge</p>
                        <p class="font-bold text-red-600">₹${parseFloat(withdrawal.charge_amount).toFixed(2)} (${withdrawal.charge_percent}%)</p>
                    </div>
                    ` : ''}
                    
                    <div class="bg-green-50 p-3 rounded border border-green-200">
                        <p class="text-sm text-gray-600">Final Amount (After Charge)</p>
                        <p class="font-bold text-green-700">₹${parseFloat(withdrawal.final_amount).toFixed(2)}</p>
                    </div>
                    
                    ${withdrawal.admin_notes ? `
                    <div class="bg-blue-50 p-3 rounded border border-blue-200">
                        <p class="text-sm text-gray-600">Admin Notes</p>
                        <p class="text-sm">${escapeHtml(withdrawal.admin_notes)}</p>
                    </div>
                    ` : ''}
                    
                    ${withdrawal.processed_at ? `
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Processed By</p>
                        <p class="text-sm">${escapeHtml(withdrawal.processed_by_username || 'Unknown Admin')} on ${new Date(withdrawal.processed_at).toLocaleString()}</p>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('detailsContent').innerHTML = html;
            document.getElementById('detailsModal').classList.remove('hidden');
        } else {
            alert('Error loading details: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading details:', error);
        alert('Failed to load withdrawal details');
    }
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}

function exportToExcel() {
    const params = new URLSearchParams({
        action: 'export_withdrawals',
        status: currentFilter,
        search: document.getElementById('searchInput').value,
        type: document.getElementById('typeFilter').value,
        date: document.getElementById('dateFilter').value
    });
    
    window.location.href = `ajax/admin.php?${params.toString()}`;
}

// Utility function to escape HTML
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
</script>
</body>
</html>